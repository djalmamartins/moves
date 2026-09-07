<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrototypeDesignTokensTest extends TestCase
{
    private string $prototype;

    protected function setUp(): void
    {
        $this->prototype = dirname(__DIR__, 2) . '/container/apps/prototype';
    }

    public function testOfficialBrandColorAndSemanticAliasesAreDefined(): void
    {
        $tokens = file_get_contents($this->prototype . '/default/assets/tokens.css');

        self::assertIsString($tokens);
        self::assertStringContainsString('--ms-color-brand-500: #c5a131;', strtolower($tokens));
        self::assertStringContainsString('--ms-color-primary: var(--ms-color-brand-500);', $tokens);
        self::assertStringContainsString('--ms-color-on-primary: var(--ms-color-neutral-950);', $tokens);
    }

    public function testFoundationsCoverThemeAccessibilityAndCoreScales(): void
    {
        $tokens = file_get_contents($this->prototype . '/default/assets/tokens.css');

        self::assertIsString($tokens);
        self::assertStringContainsString('html[data-theme="dark"]', $tokens);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $tokens);
        self::assertStringContainsString('--ms-space-1: 0.25rem;', $tokens);
        self::assertStringContainsString('--ms-font-sans:', $tokens);
        self::assertStringContainsString('--ms-radius-md:', $tokens);
    }

    public function testPrototypeCatalogueLoadsTokensBeforeComponentStyles(): void
    {
        $page = file_get_contents($this->prototype . '/default/foundations.html');

        self::assertIsString($page);
        $tokensPosition = strpos($page, 'assets/tokens.css');
        $componentsPosition = strpos($page, 'assets/foundations.css');
        self::assertNotFalse($tokensPosition);
        self::assertNotFalse($componentsPosition);
        self::assertLessThan($componentsPosition, $tokensPosition);
        self::assertStringContainsString('data-theme-toggle', $page);
    }
}
