<?php
declare(strict_types=1);
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
final class PrototypeErpShellTest extends TestCase
{
    public function testShellUsesOfficialFoundationsAndLandmarks(): void
    {
        $root=dirname(__DIR__,2).'/container/apps/prototype/default';$html=(string)file_get_contents($root.'/shell.html');
        foreach(['assets/tokens.css','id="sidebar"','id="content"','AMBIENTES MOVES','aria-label="Breadcrumb"','data-command'] as $required)self::assertStringContainsString($required,$html);
    }
    public function testShellSupportsResponsiveNavigationThemeAndKeyboard(): void
    {
        $root=dirname(__DIR__,2).'/container/apps/prototype/default/assets';$css=(string)file_get_contents($root.'/shell.css');$js=(string)file_get_contents($root.'/shell.js');
        foreach(['var(--ms-color-primary)','@media(max-width:860px)','@media(prefers-reduced-motion:reduce)'] as $required)self::assertStringContainsString($required,$css);
        self::assertStringContainsString("key.toLowerCase()==='k'",$js);self::assertStringContainsString("localStorage.setItem('moves-theme'",$js);self::assertStringNotContainsString('alert(',$js);
    }
}
