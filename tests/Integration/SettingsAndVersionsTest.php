<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use Source\Models\Settings\Settings;

final class SettingsAndVersionsTest extends TestCase
{
    public function testEmptySettingsTableReceivesSafeDefaults(): void
    {
        $this->pdo->exec('TRUNCATE TABLE settings');

        $settings = Settings::dados();

        self::assertSame(1, (int)$settings->id);
        self::assertSame('MOVES', $settings->site_name);
        self::assertSame(0, (int)$settings->access_studio);
        self::assertSame(0, (int)$settings->access_erp);
        self::assertSame(0, (int)$settings->access_app);
        self::assertSame(1, (int)$settings->access_site);
        self::assertSame(1, (int)$settings->access_support);
    }

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
        self::assertFileExists($root . '/container/web/default/default.php');
        self::assertFileExists($root . '/container/web/support/default.php');
        self::assertFileExists($root . '/container/apps/residents/default/default.php');
        self::assertFileExists($root . '/container/apps/erp/default/default.php');
        self::assertFileExists($root . '/container/apps/studio/default/default.php');
        self::assertFileExists($root . '/container/apps/studio/default/layouts/error.php');
        self::assertDirectoryExists($root . '/container/apps/studio/default/components');
        self::assertDirectoryExists($root . '/container/web/default/pages');
    }

    public function testCanonicalArchitectureHasNoLegacyParallelTrees(): void
    {
        $root = dirname(__DIR__, 2);

        foreach (['container/themes', 'container/studio', 'container/send', 'database', 'source/Public', 'source/Minify'] as $legacy) {
            self::assertDirectoryDoesNotExist($root . '/' . $legacy);
        }

        self::assertSame($root . '/container/web/default', moves_container_path('web', 'connect_by_moves'));
        self::assertSame($root . '/container/apps/studio/default', moves_container_path('studio', 'moves_studio'));
        self::assertSame($root . '/container/apps/erp/default', moves_container_path('erp', 'connect'));
        self::assertSame($root . '/container/apps/residents/default', moves_container_path('residents', 'app_connect'));
        self::assertSame($root . '/container/mail/default', moves_container_path('mail', 'mail'));
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
