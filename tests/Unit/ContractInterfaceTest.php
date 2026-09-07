<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;
use Source\Controllers\Erp\Connect\Contracts;

final class ContractInterfaceTest extends TestCase
{
    public function testOfficialRoutesPointToConnectController(): void
    {
        $root=dirname(__DIR__,2).'/container/apps/erp/default/';$routes=file_get_contents($root.'default.php');$nav=file_get_contents($root.'pages/nav.php');self::assertStringContainsString('Contracts:home',$routes);self::assertStringContainsString('Contracts:detail',$routes);self::assertStringContainsString('"contracts"',$nav);self::assertTrue(method_exists(Contracts::class,'home'));self::assertTrue(method_exists(Contracts::class,'detail'));
    }

    public function testViewsExposeLifecycleActionsWithCsrf(): void
    {
        $root=dirname(__DIR__,2).'/container/apps/erp/default/components/contracts/';$home=file_get_contents($root.'home.php');$detail=file_get_contents($root.'detail.php');self::assertStringContainsString('csrf_input()',$home);self::assertStringContainsString('csrf_input()',$detail);self::assertStringContainsString('submit',$detail);self::assertStringContainsString('approve',$detail);self::assertStringContainsString('reject',$detail);
    }

    public function testControllerEnforcesDedicatedPermissions(): void
    {
        $controller=file_get_contents(dirname(__DIR__,2).'/source/Controllers/Erp/Connect/Contracts.php');self::assertStringContainsString("erp.contracts.manage",$controller);self::assertStringContainsString("erp.contracts.approve",$controller);
    }
}
