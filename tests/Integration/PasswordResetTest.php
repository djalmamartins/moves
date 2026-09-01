<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use DateTimeImmutable;
use MovesOSTests\TestCase;
use RuntimeException;
use Source\Models\User;
use Source\Support\PasswordReset;

final class PasswordResetTest extends TestCase
{
    public function testTokenIsStoredOnlyAsHashAndExpires(): void
    {
        $user = $this->user();
        $now = new DateTimeImmutable('2026-09-01 12:00:00');
        $token = PasswordReset::issue($user, '127.0.0.10', $now);
        $record = $this->pdo->query('SELECT * FROM password_reset_tokens')->fetch();

        self::assertSame(64, strlen($token));
        self::assertNotSame($token, $record->token_hash);
        self::assertSame(hash('sha256', $token), $record->token_hash);
        self::assertFalse(PasswordReset::consume($user->email, $token, 'Nova@123', $now->modify('+31 minutes')));
    }

    public function testNewTokenRevokesPreviousAndTokenIsSingleUse(): void
    {
        $user = $this->user();
        $now = new DateTimeImmutable('2026-09-01 12:00:00');
        $first = PasswordReset::issue($user, '127.0.0.11', $now);
        $second = PasswordReset::issue($user, '127.0.0.11', $now->modify('+1 minute'));

        self::assertFalse(PasswordReset::consume($user->email, $first, 'Nova@123', $now->modify('+2 minutes')));
        self::assertTrue(PasswordReset::consume($user->email, $second, 'Nova@123', $now->modify('+2 minutes')));
        self::assertFalse(PasswordReset::consume($user->email, $second, 'Outra@123', $now->modify('+3 minutes')));
    }

    public function testRateLimitAllowsThreeRequestsPerHour(): void
    {
        $user = $this->user();
        $now = new DateTimeImmutable('2026-09-01 12:00:00');
        PasswordReset::issue($user, '127.0.0.12', $now);
        PasswordReset::issue($user, '127.0.0.12', $now->modify('+1 minute'));
        PasswordReset::issue($user, '127.0.0.12', $now->modify('+2 minutes'));

        $this->expectException(RuntimeException::class);
        PasswordReset::issue($user, '127.0.0.12', $now->modify('+3 minutes'));
    }

    private function user(): User
    {
        return (new User())->findById($this->createUser(['email' => 'reset-' . bin2hex(random_bytes(3)) . '@test.local']));
    }
}
