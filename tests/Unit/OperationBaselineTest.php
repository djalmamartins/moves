<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

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
}
