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

    public function testEverySurfaceHasAnEnforcedInitialLoadBudget(): void
    {
        $root=dirname(__DIR__,2);$budgets=require $root.'/config/asset-budgets.php';
        self::assertSame(['web','erp','residents','studio'],array_keys($budgets));
        foreach($budgets as $surface=>$types){self::assertSame(['css','js'],array_keys($types),$surface);foreach($types as $budget)self::assertGreaterThan(0,$budget['max_bytes']);}
        $builder=file_get_contents($root.'/source/Services/Assets/AssetBuilder.php');self::assertStringContainsString('assertBudgets($results)',$builder);self::assertStringContainsString("config/asset-budgets.php",$builder);
    }

    public function testHeavyVendorsAreLimitedToSurfacesThatUseThem(): void
    {
        $builder=file_get_contents(dirname(__DIR__,2).'/source/Services/Assets/AssetBuilder.php');
        self::assertStringContainsString("$"."jquery,$"."carousel,...$"."forms,$"."charts",$builder);
        self::assertStringContainsString("$"."jquery,$"."carousel,...$"."forms,...$"."utilities",$builder);
        self::assertStringNotContainsString('$commonJs',$builder);
    }
}
