<?php
declare(strict_types=1);
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
final class PrototypeCondominiumsTest extends TestCase
{
    private string $root;
    protected function setUp(): void { $this->root=dirname(__DIR__,2).'/container/apps/prototype/default'; }
    public function testScreenUsesCatalogueAndOfficialTokens(): void
    {
        $html=file_get_contents($this->root.'/condominios.html');$css=file_get_contents($this->root.'/assets/condominios.css');
        self::assertLessThan(strpos($html,'assets/components.css'),strpos($html,'assets/tokens.css'));
        self::assertStringContainsString('var(--ms-color-primary)',$css);self::assertStringNotContainsString('#C5A131',$css);
    }
    public function testFiltersStatesTableAndAccessibilityAreDemonstrable(): void
    {
        $html=file_get_contents($this->root.'/condominios.html');$js=file_get_contents($this->root.'/assets/condominios.js');
        foreach(['data-search','data-status','data-sort="name"','scope="col"','aria-live="polite"','data-empty','data-detail'] as $value)self::assertStringContainsString($value,$html);
        foreach(["healthy:'Saudável'","attention:'Atenção'","critical:'Crítico'",'localeCompare','showModal()'] as $value)self::assertStringContainsString($value,$js);
    }
}
