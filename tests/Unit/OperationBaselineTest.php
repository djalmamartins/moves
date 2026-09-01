<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;
use Source\Controllers\Operation\Operation;
use Source\Core\Controller;

final class OperationBaselineTest extends TestCase
{
    public function testBootstrapLoadsOperationFromItsOwnContainer(): void
    {
        $root = dirname(__DIR__, 2);
        $bootstrap = file_get_contents($root . '/index.php');

        self::assertIsString($bootstrap);
        self::assertStringContainsString("moves_container_path('operation', 'default')", $bootstrap);
        self::assertStringNotContainsString("moves_container_path('studio', 'operation')", $bootstrap);
        self::assertFileExists($root . '/container/apps/operation/default/default.php');
        self::assertFileExists($root . '/source/Controllers/Operation/Operation.php');
    }

    public function testContainerHelpersRecognizeOperationAsAnApplicationArea(): void
    {
        $helpers = file_get_contents(dirname(__DIR__, 2) . '/source/Boot/Helpers.php');

        self::assertIsString($helpers);
        self::assertStringContainsString("['studio', 'operation', 'helpdesk', 'erp', 'residents']", $helpers);
        self::assertStringContainsString("moves_container_url('operation', 'default', \$path)", $helpers);
    }

    public function testOperationDoesNotRepublishStudioCmsRoutes(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2) . '/container/apps/operation/default/default.php');

        self::assertIsString($routes);
        foreach ([
            '/blog', '/pages', '/page', '/media', '/users', '/user', '/faqs',
            '/testimonials', '/slides', '/slide', '/versions', '/system-logs',
            '/settings', '/formularios', '/notifications',
        ] as $forbiddenPrefix) {
            self::assertStringNotContainsString('"' . $forbiddenPrefix, $routes);
        }
    }

    public function testOperationPackageContainsOnlyOperationalViewGroups(): void
    {
        $components = dirname(__DIR__, 2) . '/container/apps/operation/default/components';
        $allowed = ['agenda', 'dash', 'error', 'login', 'logs', 'operation', 'tickets', 'visits'];
        $actual = [];
        foreach (new \DirectoryIterator($components) as $entry) {
            if ($entry->isDir() && !$entry->isDot()) {
                $actual[] = $entry->getFilename();
            }
        }
        sort($actual);
        sort($allowed);

        self::assertSame($allowed, $actual);
    }

    public function testOperationControllerDoesNotInheritStudio(): void
    {
        self::assertSame(Controller::class, get_parent_class(Operation::class));
        self::assertFalse(is_subclass_of(Operation::class, \Source\Controllers\Studio\Studio::class));

        $operation = file_get_contents(dirname(__DIR__, 2) . '/source/Controllers/Operation/Operation.php');
        $studio = file_get_contents(dirname(__DIR__, 2) . '/source/Controllers/Studio/Studio.php');
        self::assertIsString($operation);
        self::assertIsString($studio);
        self::assertStringNotContainsString("Access::can('studio.access'", $operation);
        self::assertStringNotContainsString('operation_dashboard_failed', $studio);
    }
}
