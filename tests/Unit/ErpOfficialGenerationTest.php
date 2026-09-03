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

    public function testDocumentedConnectRouteGapsMatchRuntimeMap(): void
    {
        $routes=file_get_contents(dirname(__DIR__,2).'/container/apps/erp/default/default.php');
        preg_match_all('~\$route->(?:get|post)\("([^"]+)",\s*"([A-Za-z]+):([A-Za-z]+)"\)~',$routes,$matches,PREG_SET_ORDER);
        $missing=[];
        foreach($matches as $match){$class='Source\\Controllers\\Erp\\Connect\\'.$match[2];if(!class_exists($class)||!method_exists($class,$match[3]))$missing[$match[1].' -> '.$match[2].':'.$match[3]]=true;}
        self::assertSame([
            '/permissions -> Dash:permissions',
            '/users/profile_register -> Users:profileRegister',
            '/users/profile_register/{user_id} -> Users:profileRegister',
            '/users/profile_edit/{user_id} -> Users:profileEdit',
        ],array_keys($missing));
    }
}
