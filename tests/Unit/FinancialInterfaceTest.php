<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Source\Controllers\Erp\Connect\Finance;
use Source\Services\Erp\FinancialService;

final class FinancialInterfaceTest extends TestCase
{
    public function testCanonicalFinancialRoutesHaveControllerActions():void
    {
        $routes=file_get_contents(dirname(__DIR__,2).'/container/apps/erp/default/default.php');foreach(['entries','income','expenses'] as $action){self::assertStringContainsString("Finance:{$action}",$routes);self::assertTrue(method_exists(Finance::class,$action));}
    }

    public function testFinancialServiceAndViewDoNotUseLegacyInvoices():void
    {
        $service=file_get_contents((new ReflectionClass(FinancialService::class))->getFileName());$controller=file_get_contents((new ReflectionClass(Finance::class))->getFileName());$view=file_get_contents(dirname(__DIR__,2).'/container/apps/erp/default/components/finance/entries.php');self::assertStringNotContainsString('app_invoices',$service);self::assertStringNotContainsString('app_invoices',$controller);self::assertStringContainsString('erp_financial_entries',$service);self::assertStringNotContainsString('Vinicius Moura',$view);
    }
}
