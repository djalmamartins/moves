<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PrototypeMeuDiaTest extends TestCase
{
    public function testPrototypeIsIsolatedAndUsesOfficialTokens(): void
    {
        $root = dirname(__DIR__, 2) . '/container/apps/prototype/default';
        $html = file_get_contents($root . '/meu-dia.html');
        $css = file_get_contents($root . '/assets/meu-dia.css');

        self::assertStringContainsString('assets/tokens.css', $html);
        self::assertStringContainsString('assets/meu-dia.css', $html);
        self::assertStringContainsString('assets/meu-dia.js', $html);
        self::assertStringContainsString('var(--ms-color-primary)', $css);
        self::assertStringNotContainsString('#C5A131', $css);
    }

    public function testPrototypeHasAccessibleDemonstrableInteractions(): void
    {
        $root = dirname(__DIR__, 2) . '/container/apps/prototype/default';
        $html = file_get_contents($root . '/meu-dia.html');
        $js = file_get_contents($root . '/assets/meu-dia.js');

        foreach (['aria-current="page"', 'aria-live="polite"', 'role="tablist"', '<dialog data-dialog>', 'class="skip"'] as $contract) {
            self::assertStringContainsString($contract, $html);
        }
        foreach (['data-task-tab', 'showModal()', 'data-start', 'data-theme'] as $interaction) {
            self::assertStringContainsString($interaction, $js);
        }
    }
}
