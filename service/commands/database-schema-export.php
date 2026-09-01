<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

if (!in_array('--confirm-export', $argv, true)) {
    fwrite(STDERR, "Uso: php service/commands/database-schema-export.php --confirm-export\n");
    exit(2);
}

$envFile = $root . '/.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile, false, INI_SCANNER_RAW) ?: [] as $key => $value) {
        if (getenv((string)$key) === false) {
            putenv($key . '=' . (string)$value);
        }
    }
}

$host = getenv('MOVESOS_DB_HOST') ?: getenv('DB_HOST') ?: '';
$port = (int)(getenv('MOVESOS_DB_PORT') ?: getenv('DB_PORT') ?: 3306);
$database = getenv('MOVESOS_DB_NAME') ?: getenv('DB_NAME') ?: '';
$user = getenv('MOVESOS_DB_USER') ?: getenv('DB_USER') ?: '';
$password = getenv('MOVESOS_DB_PASS');
if ($password === false) {
    $password = getenv('DB_PASS');
}

if ($host === '' || $database === '' || $user === '' || $password === false) {
    fwrite(STDERR, "Banco não configurado.\n");
    exit(2);
}

/** @return array{fingerprint:string,tables:array<string,string>,statements:array<string,string>} */
function snapshot(PDO $pdo): array
{
    $tables = [];
    $statements = [];
    $query = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    foreach ($query->fetchAll(PDO::FETCH_NUM) as $row) {
        $table = (string)$row[0];
        if ($table === 'movesos_schema_migrations') {
            continue;
        }
        $quoted = '`' . str_replace('`', '``', $table) . '`';
        $create = (string)$pdo->query("SHOW CREATE TABLE {$quoted}")->fetch(PDO::FETCH_NUM)[1];
        $create = preg_replace('/ AUTO_INCREMENT=\d+/i', '', $create) ?? $create;
        $create = rtrim($create, "; \t\r\n");
        $statements[$table] = $create;
        $tables[$table] = hash('sha256', $create);
    }
    ksort($tables, SORT_STRING);
    ksort($statements, SORT_STRING);

    return [
        'fingerprint' => hash('sha256', json_encode($tables, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
        'tables' => $tables,
        'statements' => $statements,
    ];
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $user,
        (string)$password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $snapshot = snapshot($pdo);
    $directory = $root . '/storage/database/baseline';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException("Não foi possível criar {$directory}");
    }

    $sql = "-- MOVES structural baseline (schema only; no application data).\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach ($snapshot['statements'] as $statement) {
        $sql .= $statement . ";\n\n";
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

    file_put_contents($directory . '/20260831_schema.sql', $sql, LOCK_EX);
    file_put_contents(
        $directory . '/20260831_manifest.json',
        json_encode([
            'version' => '20260831',
            'fingerprint' => $snapshot['fingerprint'],
            'tables' => $snapshot['tables'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
        LOCK_EX
    );
    echo count($snapshot['tables']) . " tabelas exportadas; fingerprint {$snapshot['fingerprint']}.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Falha ao exportar schema: {$exception->getMessage()}\n");
    exit(1);
}
