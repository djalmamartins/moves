<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class ResidentPortalScopeTest extends TestCase
{
    public function testScopeMapsEveryRequiredCapability(): void
    {
        $scope=file_get_contents(dirname(__DIR__,2).'/docs/architecture/resident-portal-scope.md');foreach(['Documentos','Boletos','Comunicados','Reservas','Ocorrências'] as $capability)self::assertStringContainsString($capability,$scope);foreach(['/app/documents','/app/billing','/app/communications','/app/reservations','/app/occurrences'] as $route)self::assertStringContainsString($route,$scope);
    }

    public function testScopeDefinesApiPermissionsAndTenantRules(): void
    {
        $scope=file_get_contents(dirname(__DIR__,2).'/docs/architecture/resident-portal-scope.md');self::assertStringContainsString('/app/api/v1/',$scope);self::assertStringContainsString('app.documents.view',$scope);self::assertStringContainsString('user_id',$scope);self::assertStringContainsString('condominium_id',$scope);self::assertStringContainsString('unit_id',$scope);self::assertStringContainsString('404',$scope);self::assertStringContainsString('CSRF',$scope);
    }

    public function testCurrentPortalRoutesRemainExplicitlyInventoried(): void
    {
        $routes=file_get_contents(dirname(__DIR__,2).'/container/apps/residents/default/default.php');self::assertStringContainsString('/dash/home',$routes);self::assertStringNotContainsString('/documents',$routes);self::assertStringNotContainsString('/reservations',$routes);
    }
}
