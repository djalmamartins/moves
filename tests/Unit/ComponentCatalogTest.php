<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class ComponentCatalogTest extends TestCase
{
    public function testSharedCatalogDefinesRequiredComponentFamilies(): void
    {
        $root = dirname(__DIR__, 2);
        $css = (string)file_get_contents($root . '/container/shared/assets/css/moves-components.css');
        foreach (['#C5A131', '.moves-button', '.moves-table', '.moves-card', '.moves-modal', '.moves-drawer', '.moves-form', '.moves-alert', ':focus-visible', 'prefers-reduced-motion'] as $required) {
            self::assertStringContainsString($required, $css);
        }
    }

    public function testModernAdministrativeThemesLoadSharedCatalog(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['studio', 'operation'] as $app) {
            $layout = (string)file_get_contents($root . "/container/apps/{$app}/default/layouts/studio.php");
            self::assertStringContainsString('/container/shared/assets/css/moves-components.css', $layout);
        }
        self::assertFileExists($root . '/docs/design-system/component-catalog.md');
    }
}
