<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use Source\Models\Settings\Settings;

final class SettingsAndVersionsTest extends TestCase
{
    public function testUpdatesSettingsWithoutCreatingAnotherConfigurationRow(): void
    {
        $settings = Settings::dados();
        $settings->site_title = 'Título alterado no teste';

        self::assertTrue($settings->save());
        self::assertSame('Título alterado no teste', Settings::dados()?->site_title);
        self::assertSame(1, (int)$this->pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn());
    }

    public function testRequiredThemeEntrypointsExist(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertFileExists($root . '/container/themes/connect_by_moves/default.php');
        self::assertFileExists($root . '/container/themes/support_by_moves/default.php');
        self::assertFileExists($root . '/container/studio/app_connect/default.php');
        self::assertFileExists($root . '/container/studio/connect/default.php');
        self::assertFileExists($root . '/container/studio/moves_studio/default.php');
        self::assertFileExists($root . '/container/studio/moves_studio/layouts/error.php');
        self::assertDirectoryExists($root . '/container/studio/moves_studio/components');
        self::assertDirectoryExists($root . '/container/themes/connect_by_moves/pages');
    }

    public function testVersionsAreIndependentByProduct(): void
    {
        $insert = $this->pdo->prepare("INSERT INTO movesos_versions (product,version,name,status,published_at) VALUES (?,?,?,'current',NOW())");
        $insert->execute(['web', '1.2.0', 'Web']);
        $insert->execute(['studio', '1.2.0', 'Studio']);
        $insert->execute(['support', '1.2.0', 'Suporte']);

        self::assertSame(3, (int)$this->pdo->query("SELECT COUNT(*) FROM movesos_versions WHERE version='1.2.0'")->fetchColumn());
        $this->pdo->exec("UPDATE movesos_versions SET status='archived' WHERE product='web'");
        self::assertSame('current', $this->pdo->query("SELECT status FROM movesos_versions WHERE product='studio'")->fetchColumn());
        self::assertSame('current', $this->pdo->query("SELECT status FROM movesos_versions WHERE product='support'")->fetchColumn());
    }
}
