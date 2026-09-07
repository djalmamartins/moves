<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use PHPUnit\Framework\TestCase;

final class AssetPipelineTest extends TestCase
{
    public function testComposerUsesSingleExplicitPipeline(): void
    {
        $composer=json_decode(file_get_contents(dirname(__DIR__,2).'/composer.json'),true,512,JSON_THROW_ON_ERROR);self::assertSame('@php service/commands/build-assets.php all',$composer['scripts']['assets:build']);self::assertSame('@php service/commands/build-assets.php all --check',$composer['scripts']['assets:check']);foreach($composer['autoload']['files'] as $file)self::assertStringNotContainsString('Services/Minify/',$file);
    }

    public function testCiChecksCommittedBundles(): void
    {
        $root=dirname(__DIR__,2);$workflow=file_get_contents($root.'/.github/workflows/ci.yml');$command=file_get_contents($root.'/service/commands/build-assets.php');self::assertStringContainsString('composer assets:check',$workflow);self::assertFileExists($root.'/service/commands/build-assets.php');self::assertStringNotContainsString("vendor/autoload.php",$command);self::assertStringContainsString("vendor/movescode/compress",$command);
    }
}
