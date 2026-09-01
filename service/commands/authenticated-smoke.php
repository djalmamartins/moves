<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$database = getenv('MOVESOS_SMOKE_DB') ?: 'movesos_auth_smoke_test';
if (!preg_match('/^movesos_auth_[a-z0-9_]*_test$/', $database)) {
    fwrite(STDERR, "O banco de smoke deve usar o padrão movesos_auth_*_test.\n");
    exit(2);
}

$host = getenv('MOVESOS_TEST_DB_HOST') ?: '127.0.0.1';
$port = (int)(getenv('MOVESOS_TEST_DB_PORT') ?: 3306);
$user = getenv('MOVESOS_TEST_DB_USER') ?: 'root';
$password = getenv('MOVESOS_TEST_DB_PASS') ?: '';
$socket = getenv('MOVESOS_TEST_DB_SOCKET') ?: '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
$serverDsn = is_file($socket) ? "mysql:unix_socket={$socket};charset=utf8mb4" : "mysql:host={$host};port={$port};charset=utf8mb4";
$databaseDsn = is_file($socket) ? "mysql:unix_socket={$socket};dbname={$database};charset=utf8mb4" : "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
$server = new PDO($serverDsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$quotedDatabase = '`' . str_replace('`', '``', $database) . '`';
$process = null;
$pipes = [];
$sessionDirectory = sys_get_temp_dir() . '/moves-auth-smoke-sessions';
$sessionId = 'moves-auth-smoke-id2';
$portNumber = random_int(19000, 19999);

try {
    $server->exec("DROP DATABASE IF EXISTS {$quotedDatabase}");
    $server->exec("CREATE DATABASE {$quotedDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = new PDO($databaseDsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec((string)file_get_contents($root . '/storage/database/baseline/20260831_schema.sql'));
    $passwordHash = password_hash('Senha@123', PASSWORD_DEFAULT);
    $statement = $pdo->prepare("INSERT INTO users (id,first_name,last_name,email,document,password,level,status) VALUES (2,'Usuário','Smoke','smoke-id2@test.local','00000000002',:password,5,'confirmed')");
    $statement->execute(['password' => $passwordHash]);
    $pdo->exec((string)file_get_contents($root . '/storage/database/migrations/20260815_movesos_access_control.sql'));
    $pdo->exec((string)file_get_contents($root . '/storage/database/migrations/20260901_settings_acl_bootstrap.sql'));
    $pdo->exec("INSERT INTO access_permissions (name,slug,group_name) VALUES
        ('Gerenciar suporte','support.manage','Suporte'),
        ('Gerenciar depoimentos','testimonials.manage','Conteúdo')
        ON DUPLICATE KEY UPDATE name=VALUES(name)");
    $pdo->exec("INSERT IGNORE INTO access_role_permissions (role_id,permission_id)
        SELECT r.id,p.id FROM access_roles r CROSS JOIN access_permissions p WHERE r.slug='client_admin'");
    $pdo->exec("UPDATE settings SET access_studio=1,access_erp=1,access_app=1,access_site=1,access_support=1 WHERE id=1");
    $pdo->exec("INSERT INTO app_corporations (id,corporation_name,fantasy_name,status) VALUES (1,'MOVES Test','MOVES Test','confirmed')");
    $pdo->exec("INSERT INTO app_condominium (id,sub_of,condominium_name,fantasy_name,status) VALUES (1,1,'Condomínio Teste','Condomínio Teste','confirmed')");
    $pdo->exec("INSERT INTO app_session (corporations_id,condominium_id,users_id) VALUES (1,1,2)");

    if (!is_dir($sessionDirectory)) mkdir($sessionDirectory, 0700, true);
    session_save_path($sessionDirectory);
    session_id($sessionId);
    session_start();
    $_SESSION = ['authUser' => 2, 'authCondo' => 1];
    session_write_close();

    $command = sprintf('%s -d session.save_path=%s -S 127.0.0.1:%d -t %s', escapeshellarg(PHP_BINARY), escapeshellarg($sessionDirectory), $portNumber, escapeshellarg($root));
    $environment = array_merge($_ENV, [
        'MOVESOS_ENV' => 'testing', 'MOVESOS_TEST_DB' => $database,
        'MOVESOS_TEST_DB_HOST' => $host, 'MOVESOS_TEST_DB_PORT' => (string)$port,
        'MOVESOS_TEST_DB_USER' => $user, 'MOVESOS_TEST_DB_PASS' => $password,
        'MOVESOS_TEST_DB_SOCKET' => $socket,
    ]);
    $process = proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $root, $environment);
    if (!is_resource($process)) throw new RuntimeException('Não foi possível iniciar o servidor de smoke.');

    $baseUrl = "http://localhost:{$portNumber}";
    $ready = false;
    for ($attempt = 0; $attempt < 30; $attempt++) {
        usleep(100000);
        if (@get_headers($baseUrl . '/studio/login') !== false) { $ready = true; break; }
    }
    if (!$ready) throw new RuntimeException('Servidor de smoke não ficou disponível.');

    $matrix = [
        'studio' => ['/studio/dash','/studio/pages','/studio/blog/home','/studio/media','/studio/users','/studio/faqs','/studio/support','/studio/agenda','/studio/tickets','/studio/reports','/studio/versions','/studio/settings'],
        'erp' => ['/erp/dash/home','/erp/register/home','/erp/register/condo','/erp/register/users','/erp/users/home','/erp/finance/home','/erp/finance/income','/erp/condo/home','/erp/condo/profile','/erp/condo/address','/erp/condo/units'],
        'app' => ['/app/dash/home'],
    ];
    $failures = [];
    foreach ($matrix as $environmentName => $routes) {
        foreach ($routes as $route) {
            $context = stream_context_create(['http' => ['method' => 'GET', 'ignore_errors' => true, 'follow_location' => 0, 'header' => "Cookie: PHPSESSID={$sessionId}\r\n"]]);
            @file_get_contents($baseUrl . $route, false, $context);
            $statusLine = $http_response_header[0] ?? '';
            preg_match('/\s(\d{3})\s/', $statusLine, $match);
            $status = (int)($match[1] ?? 0);
            $location = '';
            foreach ($http_response_header ?? [] as $header) if (stripos($header, 'Location:') === 0) $location = trim(substr($header, 9));
            $locationPath = rtrim((string)parse_url($location, PHP_URL_PATH), '/');
            $selfRedirect = $status >= 300 && $status < 400 && $locationPath === rtrim($route, '/');
            $errorRedirect = (bool)preg_match('~/ops/(?:403|404|500)$~', $locationPath);
            if (!in_array($status, [200, 301, 302, 303, 307, 308], true) || $selfRedirect || $errorRedirect) {
                $failures[] = "{$environmentName} {$route}: HTTP {$status}" . ($location ? " -> {$location}" : '');
            } else {
                echo "[ok] {$environmentName} {$route}: HTTP {$status}" . ($location ? " -> {$location}" : '') . PHP_EOL;
            }
        }
    }
    if ($failures) throw new RuntimeException("Falhas no smoke autenticado:\n- " . implode("\n- ", $failures));
    echo "Smoke autenticado concluído com o usuário ID 2.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    if (is_resource($process)) {
        proc_terminate($process);
        foreach ($pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
        proc_close($process);
    }
    $server->exec("DROP DATABASE IF EXISTS {$quotedDatabase}");
}
