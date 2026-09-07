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
        $workflow=file_get_contents(dirname(__DIR__,2).'/.github/workflows/ci.yml');self::assertStringContainsString('composer assets:check',$workflow);self::assertFileExists(dirname(__DIR__,2).'/service/commands/build-assets.php');
    }
}
