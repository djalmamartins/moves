<?php

declare(strict_types=1);

namespace MovesOSTests\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Source\Models\Banking\Banking;
use Source\Models\Slide\AppSlide;
use Source\Models\Slide\SlideCategory;
use Source\Models\Talk\Talk;

final class LegacyModelMappingTest extends TestCase
{
    public function testDeprecatedSlideModelsPointToExistingLegacyTables(): void
    {
        self::assertSame('slides', $this->entityOf(new AppSlide()));
        self::assertSame('categories_slide', $this->entityOf(new SlideCategory()));
    }

    public function testBankingFailsWithAnExplicitMigrationPath(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('AppWallet');
        new Banking();
    }

    public function testTalkFailsExplicitlyInsteadOfQueryingAMissingTable(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('não existe tabela');
        new Talk();
    }

    private function entityOf(object $model): string
    {
        $property = (new ReflectionClass($model))->getParentClass()->getProperty('entity');
        return (string)$property->getValue($model);
    }
}
