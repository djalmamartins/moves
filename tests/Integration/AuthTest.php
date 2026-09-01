<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use Source\Models\Auth;
use Source\Models\User;
use Source\Support\PasswordReset;

final class AuthTest extends TestCase
{
    public function testLoginCreatesAuthenticatedSessionAndLog(): void
    {
        $userId = $this->createUser(['email' => 'login@test.local', 'level' => 5]);

        self::assertTrue((new Auth())->login('login@test.local', 'Senha@123', false, 5));
        self::assertSame($userId, (int)($_SESSION['authUser'] ?? 0));
        self::assertSame($userId, (int)Auth::user()?->id);
        self::assertSame(1, (int)$this->pdo->query("SELECT COUNT(*) FROM app_log WHERE event_type='user_activity'")->fetchColumn());
    }

    public function testInvalidPasswordDoesNotAuthenticate(): void
    {
        $this->createUser(['email' => 'invalid@test.local']);

        self::assertFalse((new Auth())->login('invalid@test.local', 'Senha@999'));
        self::assertArrayNotHasKey('authUser', $_SESSION);
    }

    public function testRequiredLevelIsEnforced(): void
    {
        $this->createUser(['email' => 'basic@test.local', 'level' => 1]);

        self::assertFalse((new Auth())->login('basic@test.local', 'Senha@123', false, 5));
        self::assertArrayNotHasKey('authUser', $_SESSION);
    }

    public function testLogoutRemovesAuthentication(): void
    {
        $_SESSION['authUser'] = 123;
        $_SESSION['authCondo'] = 456;

        Auth::logout();

        self::assertArrayNotHasKey('authUser', $_SESSION);
        self::assertArrayNotHasKey('authCondo', $_SESSION);
    }

    public function testPasswordResetRequiresValidCodeAndMatchingPasswords(): void
    {
        $userId = $this->createUser([
            'email' => 'recovery@test.local',
            'document' => '52998224725'
        ]);
        $token = PasswordReset::issue((new User())->findById($userId), '127.0.0.1');
        $auth = new Auth();

        self::assertFalse($auth->reset('recovery@test.local', 'codigo-invalido', 'Nova@123', 'Nova@123'));
        self::assertFalse($auth->reset('recovery@test.local', $token, 'Nova@123', 'Diferente@123'));
        self::assertTrue($auth->reset('recovery@test.local', $token, 'Nova@123', 'Nova@123'));
        self::assertFalse($auth->reset('recovery@test.local', $token, 'Outra@123', 'Outra@123'));

        $user = $this->pdo->query("SELECT password,forget FROM users WHERE id={$userId}")->fetch();
        self::assertTrue(passwd_verify('Nova@123', $user->password));
        self::assertEmpty($user->forget);
    }

    public function testAccountConfirmationKeepsLegacyActivationCodeIsolated(): void
    {
        $userId = $this->createUser(['email' => 'confirmation@test.local', 'document' => '52998224725']);
        $this->pdo->prepare('UPDATE users SET forget=? WHERE id=?')->execute(['activation-code', $userId]);

        self::assertTrue((new Auth())->confirm('confirmation@test.local', 'activation-code', 'Nova@123', 'Nova@123'));
        self::assertEmpty($this->pdo->query("SELECT forget FROM users WHERE id={$userId}")->fetchColumn());
    }
}
