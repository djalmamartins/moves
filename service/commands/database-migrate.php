<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$mode = $argv[1] ?? 'status';
if (!in_array($mode, ['status', 'apply', 'baseline', 'install', 'verify'], true)) {
    fwrite(STDERR, "Uso: php service/commands/database-migrate.php [status|apply|baseline|install|verify]\n");
    exit(2);
}

$envFile = $root . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile, false, INI_SCANNER_RAW) ?: [] as $key => $value) {
        if (getenv((string)$key) === false) putenv($key . '=' . (string)$value);
    }
}

$environment = getenv('MOVESOS_ENV') ?: getenv('APP_ENV') ?: 'production';
$host = getenv('MOVESOS_DB_HOST') ?: getenv('DB_HOST') ?: '';
$port = (int)(getenv('MOVESOS_DB_PORT') ?: getenv('DB_PORT') ?: 3306);
$database = getenv('MOVESOS_DB_NAME') ?: getenv('DB_NAME') ?: '';
$user = getenv('MOVESOS_DB_USER') ?: getenv('DB_USER') ?: '';
$password = getenv('MOVESOS_DB_PASS');
if ($password === false) $password = getenv('DB_PASS');

if ($host === '' || $database === '' || $user === '' || $password === false) {
    fwrite(STDERR, "Banco não configurado. Defina DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS.\n");
    exit(2);
}
if ($mode === 'baseline' && !in_array('--confirm-baseline', $argv, true)) {
    fwrite(STDERR, "Baseline exige --confirm-baseline após backup e verificação do clone.\n");
    exit(2);
}
if ($mode === 'install' && !in_array('--confirm-install', $argv, true)) {
    fwrite(STDERR, "Instalação estrutural exige --confirm-install e um banco vazio.\n");
    exit(2);
}
if (in_array($mode, ['baseline', 'install'], true) && $environment === 'production' && !in_array('--confirm-production', $argv, true)) {
    fwrite(STDERR, "Baseline em produção exige --confirm-production. Use somente se o schema já foi aplicado anteriormente.\n");
    exit(2);
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $user,
        (string)$password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $baselineSql = $root . '/storage/database/baseline/20260831_schema.sql';
    $manifestFile = $root . '/storage/database/baseline/20260831_manifest.json';
    if (!is_file($baselineSql) || !is_file($manifestFile)) {
        throw new RuntimeException('Baseline estrutural ou manifesto não encontrado.');
    }
    $manifest = json_decode((string)file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);

    $schemaSnapshot = static function () use ($pdo): array {
        $tables = [];
        foreach ($pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM) as $row) {
            $table = (string)$row[0];
            if ($table === 'movesos_schema_migrations') continue;
            $quoted = '`' . str_replace('`', '``', $table) . '`';
            $create = (string)$pdo->query("SHOW CREATE TABLE {$quoted}")->fetch(PDO::FETCH_NUM)[1];
            $create = preg_replace('/ AUTO_INCREMENT=\d+/i', '', $create) ?? $create;
            $tables[$table] = hash('sha256', rtrim($create, "; \t\r\n"));
        }
        ksort($tables, SORT_STRING);
        return ['tables' => $tables, 'fingerprint' => hash('sha256', json_encode($tables, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))];
    };
    $verifySchema = static function () use ($schemaSnapshot, $manifest): void {
        $actual = $schemaSnapshot();
        if (!hash_equals((string)$manifest['fingerprint'], $actual['fingerprint'])) {
            $expectedTables = (array)$manifest['tables'];
            $missing = array_keys(array_diff_key($expectedTables, $actual['tables']));
            $unexpected = array_keys(array_diff_key($actual['tables'], $expectedTables));
            $changed = [];
            foreach (array_intersect_key($expectedTables, $actual['tables']) as $table => $hash) {
                if (!hash_equals((string)$hash, (string)$actual['tables'][$table])) $changed[] = $table;
            }
            throw new RuntimeException('Schema divergente do baseline. Ausentes: ' . (implode(', ', $missing) ?: '-')
                . '; extras: ' . (implode(', ', $unexpected) ?: '-') . '; alteradas: ' . (implode(', ', $changed) ?: '-') . '.');
        }
        echo "Schema compatível com o baseline ({$actual['fingerprint']}).\n";
    };

    if ($mode === 'verify') {
        $verifySchema();
        exit(0);
    }

    $existingTables = $schemaSnapshot()['tables'];
    if ($mode === 'install') {
        if ($existingTables !== []) {
            throw new RuntimeException('Install recusado: o banco não está vazio.');
        }
        $pdo->exec((string)file_get_contents($baselineSql));
        $verifySchema();
    } elseif ($mode === 'baseline') {
        $verifySchema();
    } elseif ($mode === 'apply' && $existingTables === []) {
        throw new RuntimeException('Banco vazio: use install --confirm-install para carregar o baseline estrutural.');
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS movesos_schema_migrations (
        migration VARCHAR(190) NOT NULL PRIMARY KEY,
        checksum CHAR(64) NOT NULL,
        batch INT UNSIGNED NOT NULL,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $applied = [];
    foreach ($pdo->query('SELECT migration, checksum FROM movesos_schema_migrations') as $row) {
        $applied[$row['migration']] = $row['checksum'];
    }
    $files = glob($root . '/storage/database/migrations/*.sql') ?: [];
    sort($files, SORT_STRING);
    $pending = [];
    foreach ($files as $file) {
        $name = basename($file);
        $checksum = hash_file('sha256', $file);
        if (isset($applied[$name])) {
            if (!hash_equals($applied[$name], $checksum)) {
                throw new RuntimeException("Migration alterada depois de aplicada: {$name}");
            }
            echo "[aplicada] {$name}\n";
        } else {
            echo "[pendente] {$name}\n";
            $pending[] = [$file, $name, $checksum];
        }
    }

    if ($mode === 'status') {
        echo count($pending) . " migration(s) pendente(s).\n";
        exit($pending ? 1 : 0);
    }

    $batch = (int)$pdo->query('SELECT COALESCE(MAX(batch), 0) + 1 FROM movesos_schema_migrations')->fetchColumn();
    $record = $pdo->prepare('INSERT INTO movesos_schema_migrations (migration, checksum, batch) VALUES (:migration, :checksum, :batch)');
    if (in_array($mode, ['apply', 'baseline', 'install'], true) && !$pdo->query("SELECT GET_LOCK('movesos_schema_migrations', 10)")->fetchColumn()) {
        throw new RuntimeException('Não foi possível obter o lock de migrations.');
    }
    foreach ($pending as [$file, $name, $checksum]) {
        if ($mode === 'apply') {
            $sql = trim((string)file_get_contents($file));
            if ($sql !== '') $pdo->exec($sql);
        }
        $record->execute(['migration' => $name, 'checksum' => $checksum, 'batch' => $batch]);
        echo '[' . ($mode === 'apply' ? 'executada' : 'registrada') . "] {$name}\n";
    }
    if (in_array($mode, ['apply', 'baseline', 'install'], true)) $pdo->query("SELECT RELEASE_LOCK('movesos_schema_migrations')");
    echo $pending ? "Banco atualizado com sucesso.\n" : "Banco já está atualizado.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Falha na migration: {$exception->getMessage()}\n");
    exit(1);
}
