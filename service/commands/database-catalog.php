<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$schemaPath = $root . '/storage/database/baseline/20260831_schema.sql';
$manifestPath = $root . '/storage/database/baseline/20260831_manifest.json';
$outputPath = $root . '/docs/database-catalog.md';
$checkOnly = in_array('--check', $argv, true);

if (!is_file($schemaPath) || !is_file($manifestPath)) {
    fwrite(STDERR, "Baseline estrutural não encontrado.\n");
    exit(2);
}

$schema = (string)file_get_contents($schemaPath);
$manifest = json_decode((string)file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\) ENGINE=/s', $schema, $matches, PREG_SET_ORDER);

$models = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/source/Models')) as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $source = (string)file_get_contents($file->getPathname());
    if (preg_match('/parent::__construct\(["\']([^"\']+)["\']/', $source, $table)) {
        $models[$table[1]][] = str_replace($root . '/', '', $file->getPathname());
    }
}

/** @return array{string,string,string} */
function policy(string $table): array
{
    return match (true) {
        str_starts_with($table, 'operation_') => ['Operation', '/operation', '5 anos após encerramento'],
        str_starts_with($table, 'studio_support_'), str_starts_with($table, 'support_') => ['Help Desk', '/helpdesk e /suporte', '5 anos após encerramento'],
        str_starts_with($table, 'studio_') => ['Studio', '/studio', 'Enquanto publicado + histórico'],
        str_starts_with($table, 'access_'), str_starts_with($table, 'system_audit_'), $table === 'system_protected_users' => ['Core/Security', 'Administração e auditoria', '5 anos; proteção legal pode ampliar'],
        str_contains($table, 'session'), $table === 'report_online' => ['Core/Session', 'Autenticação e presença', '30 dias após expiração'],
        str_starts_with($table, 'app_invoice'), str_starts_with($table, 'app_order'), str_starts_with($table, 'app_wallet'), str_starts_with($table, 'app_transaction'), str_starts_with($table, 'erp_') => ['ERP/Financeiro', '/erp', '10 anos após exercício'],
        str_starts_with($table, 'app_') => ['ERP', '/erp e /app', '5 anos após término do vínculo'],
        in_array($table, ['pages', 'posts', 'categories', 'faq_channels', 'faq_questions', 'slides', 'slide_categories'], true) => ['Studio/Conteúdo', '/studio e site público', 'Enquanto publicado + histórico'],
        in_array($table, ['notifications', 'notification_messages', 'notifications_categories', 'mail_queue'], true) => ['Core/Comunicação', 'Notificações e e-mail', '2 anos após envio'],
        str_starts_with($table, 'report_'), $table === 'app_log' => ['Observabilidade', 'Relatórios e logs', '13 meses'],
        $table === 'users', $table === 'password_reset_tokens' => ['Core/Identidade', 'Todos os ambientes', 'Vínculo ativo + 5 anos'],
        default => ['Core/Legado', 'Uso interno/legado', 'Revisão anual; não excluir sem auditoria'],
    };
}

$rows = [];
$relationCount = 0;
foreach ($matches as $match) {
    $table = $match[1];
    preg_match_all('/FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)` \(`([^`]+)`\)/', $match[2], $foreignKeys, PREG_SET_ORDER);
    $relations = [];
    foreach ($foreignKeys as $foreignKey) {
        $relations[] = "`{$foreignKey[1]}` → `{$foreignKey[2]}.{$foreignKey[3]}`";
        $relationCount++;
    }
    [$owner, $screens, $retention] = policy($table);
    $model = isset($models[$table]) ? implode('<br>', array_map(static fn(string $path): string => "`{$path}`", $models[$table])) : 'SQL/serviço direto';
    $rows[] = "| `{$table}` | {$owner} | {$model} | {$screens} | " . ($relations ? implode('<br>', $relations) : '—') . " | {$retention} |";
}

usort($rows, static fn(string $a, string $b): int => strcmp($a, $b));
$fingerprint = (string)($manifest['fingerprint'] ?? 'indisponível');
$document = "# Catálogo do banco de dados MOVES\n\n";
$document .= "> Gerado de `storage/database/baseline/20260831_schema.sql`. Não contém dados de produção.\n\n";
$document .= "- Baseline: `20260831`\n- Fingerprint: `{$fingerprint}`\n- Tabelas catalogadas: " . count($rows) . "\n- Relações com foreign key: {$relationCount}\n\n";
$document .= "## Política\n\nOwner indica a área responsável. Retenção é a regra operacional mínima proposta e deve respeitar obrigações legais, bloqueios de auditoria e solicitações válidas de titulares. Exclusões nunca devem ignorar relações ou trilhas de auditoria.\n\n";
$document .= "## Tabelas\n\n| Tabela | Owner | Model/serviço | Telas/uso | Relações | Retenção |\n|---|---|---|---|---|---|\n" . implode("\n", $rows) . "\n";

if ($checkOnly) {
    $expected = count($manifest['tables'] ?? []);
    if ($expected !== count($rows) || !is_file($outputPath) || (string)file_get_contents($outputPath) !== $document) {
        fwrite(STDERR, "Catálogo desatualizado: baseline={$expected}, catálogo=" . count($rows) . ".\n");
        exit(1);
    }
    echo "Catálogo válido: " . count($rows) . " tabelas e {$relationCount} relações.\n";
    exit(0);
}

if (!is_dir(dirname($outputPath))) {
    mkdir(dirname($outputPath), 0775, true);
}
file_put_contents($outputPath, $document, LOCK_EX);
echo "Catálogo gerado: " . count($rows) . " tabelas e {$relationCount} relações.\n";
