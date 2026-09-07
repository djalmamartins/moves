<?php

declare(strict_types=1);

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Source\Core\Connect;
use Source\Support\ObservabilityRetention;

$apply = in_array('--apply', $argv, true);
try {
    $pdo = Connect::getInstance();
    if ($apply) {
        $pdo->beginTransaction();
    }
    $result = ObservabilityRetention::run($pdo, $apply);
    if ($apply) {
        $pdo->commit();
    }
    echo ($apply ? 'Removidos' : 'Elegíveis') . ': ' . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Falha na retenção: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
