<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class DatabaseCatalogTest extends TestCase
{
    public function testCatalogCoversEveryBaselineTable(): void
    {
        $root = dirname(__DIR__, 2);
        $manifest = json_decode((string)file_get_contents($root . '/storage/database/baseline/20260831_manifest.json'), true, flags: JSON_THROW_ON_ERROR);
        $catalog = (string)file_get_contents($root . '/docs/database-catalog.md');

        self::assertNotEmpty($manifest['fingerprint']);
        self::assertStringContainsString($manifest['fingerprint'], $catalog);
        foreach (array_keys($manifest['tables']) as $table) {
            self::assertSame(1, substr_count($catalog, "| `{$table}` |"), "Tabela {$table} ausente ou duplicada no catálogo.");
        }
    }

    public function testCatalogDocumentsOwnershipUsageRelationsAndRetention(): void
    {
        $catalog = (string)file_get_contents(dirname(__DIR__, 2) . '/docs/database-catalog.md');

        self::assertStringContainsString('| Tabela | Owner | Model/serviço | Telas/uso | Relações | Retenção |', $catalog);
        self::assertStringContainsString('| `operation_visits` | Operation |', $catalog);
        self::assertStringContainsString('`condominium_id` → `operation_condominiums.id`', $catalog);
        self::assertStringContainsString('| `users` | Core/Identidade |', $catalog);
    }
}
