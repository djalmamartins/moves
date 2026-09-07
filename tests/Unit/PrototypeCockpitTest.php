<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrototypeCockpitTest extends TestCase
{
    private string $prototype;

    protected function setUp(): void
    {
        $this->prototype = dirname(__DIR__, 2) . '/container/apps/prototype/default';
    }

    public function testCockpitRemainsIsolatedAndUsesOfficialAssets(): void
    {
        $page = $this->contents('cockpit.html');
        self::assertStringContainsString('assets/tokens.css', $page);
        self::assertStringContainsString('assets/components.css', $page);
        self::assertStringNotContainsString('/erp/', $page);
        self::assertStringNotContainsString('/operation/', $page);
        self::assertStringNotContainsString('<?php', $page);
    }

    public function testCockpitProvidesContextIndicatorsAndActions(): void
    {
        $page = $this->contents('cockpit.html');
        foreach (['data-context-toggle', 'data-context-name', 'Indicadores principais', 'O que precisa de atenção', 'Contas e fundos', 'Módulos do condomínio', 'Atividade recente'] as $contract) self::assertStringContainsString($contract, $page);
        self::assertGreaterThanOrEqual(6, substr_count($page, 'data-action='));
    }

    public function testInteractionsExposeAccessibleFeedbackAndDialog(): void
    {
        $page = $this->contents('cockpit.html');$script = $this->contents('assets/cockpit.js');
        foreach (['aria-live="polite"', 'aria-expanded="false"', 'aria-current="page"', 'aria-labelledby="action-title"'] as $contract) self::assertStringContainsString($contract, $page);
        self::assertStringContainsString('dialog.showModal()', $script);self::assertStringContainsString("event.key==='Escape'", $script);
    }

    public function testCockpitStylesUseTokensAndResponsiveBreakpoints(): void
    {
        $styles = $this->contents('assets/cockpit.css');self::assertStringContainsString('var(--ms-color-primary)', $styles);self::assertStringContainsString('@media(max-width:760px)', $styles);self::assertStringContainsString('@media(prefers-reduced-motion:reduce)', $styles);self::assertStringNotContainsString('#C5A131', $styles);
    }

    private function contents(string $path): string
    {
        $contents=file_get_contents($this->prototype.'/'.$path);self::assertIsString($contents);return $contents;
    }
}
