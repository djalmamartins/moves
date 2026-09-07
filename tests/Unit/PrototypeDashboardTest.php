<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrototypeDashboardTest extends TestCase
{
    private string $prototype;

    protected function setUp(): void
    {
        $this->prototype = dirname(__DIR__, 2) . '/container/apps/prototype/default';
    }

    public function testDashboardLoadsOfficialTokensBeforeItsStyles(): void
    {
        $page = $this->contents('dashboard.html');
        self::assertLessThan(strpos($page, 'assets/dashboard.css'), strpos($page, 'assets/tokens.css'));
        self::assertStringNotContainsString('#C5A131', $this->contents('assets/dashboard.css'));
        self::assertStringContainsString('var(--ms-color-primary)', $this->contents('assets/dashboard.css'));
    }

    public function testDashboardContainsRequiredOperationalViews(): void
    {
        $page = $this->contents('dashboard.html');
        foreach (['data-portfolio-filter', 'aria-label="Resumo da carteira"', 'id="visitas"', 'id="saude"', 'id="modulos"', 'id="pendencias"'] as $feature) {
            self::assertStringContainsString($feature, $page);
        }
    }

    public function testDashboardProvidesResponsiveAndAccessibleInteractions(): void
    {
        $page = $this->contents('dashboard.html');
        foreach (['aria-current="page"', 'aria-expanded="false"', 'aria-live="polite"', '<progress ', '<time datetime='] as $contract) {
            self::assertStringContainsString($contract, $page);
        }
        $styles = $this->contents('assets/dashboard.css');
        self::assertStringContainsString('@media (max-width: 54rem)', $styles);
        self::assertStringContainsString('@media (max-width: 40rem)', $styles);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
        $script = $this->contents('assets/dashboard.js');
        self::assertStringContainsString("event.key === 'Escape'", $script);
        self::assertStringContainsString("filter?.addEventListener('change'", $script);
    }

    private function contents(string $path): string
    {
        $contents = file_get_contents($this->prototype . '/' . $path);
        self::assertIsString($contents);
        return $contents;
    }
}
