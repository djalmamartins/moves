<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrototypeComponentCatalogueTest extends TestCase
{
    private string $prototype;

    protected function setUp(): void
    {
        $this->prototype = dirname(__DIR__, 2) . '/container/apps/prototype/default';
    }

    public function testCatalogueUsesOfficialTokensBeforeComponentStyles(): void
    {
        $page = file_get_contents($this->prototype . '/components.html');
        self::assertIsString($page);
        self::assertLessThan(strpos($page, 'assets/components.css'), strpos($page, 'assets/tokens.css'));
        self::assertStringNotContainsString('#C5A131', $this->contents('assets/components.css'));
        self::assertStringContainsString('var(--ms-color-primary)', $this->contents('assets/components.css'));
    }

    public function testCatalogueExposesAllComponentFamiliesAndStates(): void
    {
        $page = $this->contents('components.html');
        foreach (['id="actions"', 'id="forms"', 'id="feedback"', 'id="data"', 'id="navigation"', 'id="overlays"'] as $section) {
            self::assertStringContainsString($section, $page);
        }
        foreach ([':hover', ':active', ':disabled', '--loading', ':focus-visible'] as $state) {
            self::assertStringContainsString($state, $this->contents('assets/components.css'));
        }
    }

    public function testInteractiveExamplesProvideAccessibleContracts(): void
    {
        $page = $this->contents('components.html');
        foreach (['aria-invalid="true"', 'aria-describedby=', 'role="alert"', 'aria-live="polite"', 'role="tablist"', 'aria-labelledby="modal-title"'] as $contract) {
            self::assertStringContainsString($contract, $page);
        }
        $script = $this->contents('assets/components.js');
        self::assertStringContainsString("'ArrowLeft', 'ArrowRight'", $script);
        self::assertStringContainsString("setAttribute('aria-selected'", $script);
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents($this->prototype . '/' . $path);
        self::assertIsString($contents);
        return $contents;
    }
}
