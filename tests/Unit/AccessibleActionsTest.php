<?php
declare(strict_types=1);
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
final class AccessibleActionsTest extends TestCase
{
    private string $root;
    protected function setUp(): void { $this->root=dirname(__DIR__,2); }
    public function testStudioAndOperationLoadSharedAccessibleDialog(): void
    {
        foreach(['studio','operation'] as $app){$layout=file_get_contents("{$this->root}/container/apps/{$app}/default/layouts/studio.php");self::assertStringContainsString('/container/shared/assets/js/moves-dialog.js',$layout);}
        $dialog=file_get_contents($this->root.'/container/shared/assets/js/moves-dialog.js');
        foreach(['aria-modal','aria-live','dataset.confirmSubmit','event.key!=="Tab"','restoreFocus'] as $value)self::assertStringContainsString($value,$dialog);
    }
    public function testApplicationSourcesDoNotUseNativeDialogsOrEmptyActionLinks(): void
    {
        $violations=[];
        foreach(['studio','operation'] as $app){$base="{$this->root}/container/apps/{$app}/default";$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));foreach($files as $file){if(!$file->isFile()||!in_array($file->getExtension(),['php','js'],true)||str_contains($file->getPathname(),'/vendor/')||str_ends_with($file->getFilename(),'.min.js'))continue;$source=file_get_contents($file->getPathname());$dynamicMediaLink=str_ends_with($file->getPathname(),'/components/media/home.php');if(preg_match('/(?:window\\.(?:alert|confirm|prompt)|(?<![\\w.])(?:alert|confirm|prompt))\\s*\\(/',$source)||(!$dynamicMediaLink&&preg_match('/href=["\']#["\']/', $source)))$violations[]=str_replace($this->root.'/','',$file->getPathname());}}
        self::assertSame([],array_values(array_unique($violations)),implode(', ',$violations));
    }
}
