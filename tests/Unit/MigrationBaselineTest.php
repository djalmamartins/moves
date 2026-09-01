<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class MigrationBaselineTest extends TestCase
{
    public function testBaselineIsSchemaOnlyAndMatchesManifestTableNames(): void
    {
        $root = dirname(__DIR__, 2);
        $sql = (string)file_get_contents($root . '/storage/database/baseline/20260831_schema.sql');
        $manifest = json_decode(
            (string)file_get_contents($root . '/storage/database/baseline/20260831_manifest.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string)$manifest['fingerprint']);
        self::assertCount(92, $manifest['tables']);
        self::assertStringNotContainsString('INSERT INTO', strtoupper($sql));
        self::assertStringNotContainsString('DEFINER=', strtoupper($sql));
        self::assertStringNotContainsString('DROP TABLE', strtoupper($sql));

        preg_match_all('/CREATE TABLE `([^`]+)`/', $sql, $matches);
        $sqlTables = $matches[1];
        sort($sqlTables, SORT_STRING);
        $manifestTables = array_keys($manifest['tables']);
        sort($manifestTables, SORT_STRING);

        self::assertSame($manifestTables, $sqlTables);
    }

    public function testAllManifestHashesAreSha256(): void
    {
        $manifest = json_decode(
            (string)file_get_contents(dirname(__DIR__, 2) . '/storage/database/baseline/20260831_manifest.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        foreach ($manifest['tables'] as $table => $hash) {
            self::assertNotSame('movesos_schema_migrations', $table);
            self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string)$hash);
        }
    }
}
