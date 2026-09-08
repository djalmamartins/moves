<?php
declare(strict_types=1);
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
final class PrototypeUnitRecordTest extends TestCase
{
    private string $root;
    protected function setUp(): void { $this->root=dirname(__DIR__,2).'/container/apps/prototype/default'; }
    public function testUnitRecordUsesOfficialCatalogueAndRemainsPrototypeOnly(): void
    {
        $html=file_get_contents($this->root.'/prontuario-unidade.html');$css=file_get_contents($this->root.'/assets/prontuario-unidade.css');
        self::assertLessThan(strpos($html,'assets/components.css'),strpos($html,'assets/tokens.css'));
        self::assertStringContainsString('var(--ms-color-primary)',$css);self::assertStringNotContainsString('#C5A131',$css);
        self::assertFileDoesNotExist(dirname(__DIR__,2).'/source/Controllers/Prototype/UnitRecord.php');
    }
    public function testTimelineTabsFiltersAndAccessibilityAreDemonstrable(): void
    {
        $html=file_get_contents($this->root.'/prontuario-unidade.html');$js=file_get_contents($this->root.'/assets/prontuario-unidade.js');
        foreach(['role="tablist"','role="tabpanel"','data-event-filter','datetime="2026-09-05"','aria-live="polite"','<dialog'] as $value)self::assertStringContainsString($value,$html);
        foreach(['ArrowLeft','ArrowRight','showModal()','aria-selected','data-event-empty'] as $value)self::assertStringContainsString($value,$js);
    }
}
