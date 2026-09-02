<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class ErpOfficialGenerationTest extends TestCase
{
    public function testErpRoutesUseOnlyConnectGeneration(): void
    {
        $routes=file_get_contents(dirname(__DIR__,2).'/container/apps/erp/default/default.php');
        self::assertStringContainsString('Source\\Controllers\\Erp\\Connect',$routes);
        self::assertStringNotContainsString('Source\\Controllers\\Erp\\V1',$routes);
        self::assertSame(2,substr_count($routes,'$route->namespace("Source\\Controllers\\Erp\\Connect")'));
    }

    public function testLegacyGenerationIsExplicitlyFrozen(): void
    {
        $notice=dirname(__DIR__,2).'/source/Controllers/Erp/V1/README.md';
        self::assertFileExists($notice);
        self::assertStringContainsString('não recebe novas rotas ou funcionalidades',file_get_contents($notice));
    }
}
