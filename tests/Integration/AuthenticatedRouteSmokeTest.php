<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use PHPUnit\Framework\TestCase;

final class AuthenticatedRouteSmokeTest extends TestCase
{
    public function testAuthenticatedMenuRoutesWithUserIdTwo(): void
    {
        $root = dirname(__DIR__, 2);
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/service/commands/authenticated-smoke.php');
        exec($command . ' 2>&1', $output, $status);

        self::assertSame(0, $status, implode(PHP_EOL, $output));
        self::assertStringContainsString('usuário ID 2', implode(PHP_EOL, $output));
    }
}
