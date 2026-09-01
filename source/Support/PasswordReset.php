<?php

declare(strict_types=1);

namespace Source\Support;

use DateTimeImmutable;
use RuntimeException;
use Source\Core\Connect;
use Source\Models\User;

final class PasswordReset
{
    private const TTL_MINUTES = 30;
    private const MAX_REQUESTS_PER_HOUR = 3;

    public static function issue(User $user, ?string $ip = null, ?DateTimeImmutable $now = null): string
    {
        $pdo = Connect::getInstance();
        $now ??= new DateTimeImmutable();
        $since = $now->modify('-1 hour')->format('Y-m-d H:i:s');
        $rate = $pdo->prepare('SELECT COUNT(*) FROM password_reset_tokens WHERE created_at >= :since AND (user_id = :user OR request_ip = :ip)');
        $rate->execute(['since' => $since, 'user' => $user->id, 'ip' => $ip ?: 'unknown']);
        if ((int)$rate->fetchColumn() >= self::MAX_REQUESTS_PER_HOUR) {
            throw new RuntimeException('Limite de recuperação atingido. Tente novamente mais tarde.');
        }

        $token = bin2hex(random_bytes(32));
        $pdo->beginTransaction();
        try {
            $revoke = $pdo->prepare('UPDATE password_reset_tokens SET revoked_at=:now WHERE user_id=:user AND used_at IS NULL AND revoked_at IS NULL');
            $revoke->execute(['now' => $now->format('Y-m-d H:i:s'), 'user' => $user->id]);
            $insert = $pdo->prepare('INSERT INTO password_reset_tokens (user_id,token_hash,expires_at,request_ip,created_at) VALUES (:user,:hash,:expires,:ip,:created)');
            $insert->execute([
                'user' => $user->id, 'hash' => hash('sha256', $token),
                'expires' => $now->modify('+' . self::TTL_MINUTES . ' minutes')->format('Y-m-d H:i:s'),
                'ip' => $ip ?: null, 'created' => $now->format('Y-m-d H:i:s'),
            ]);
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
        return $token;
    }

    public static function consume(string $email, string $token, string $password, ?DateTimeImmutable $now = null): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return false;
        $pdo = Connect::getInstance();
        $now ??= new DateTimeImmutable();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('SELECT pr.id,pr.expires_at,pr.used_at,pr.revoked_at,u.id AS user_id FROM password_reset_tokens pr JOIN users u ON u.id=pr.user_id WHERE u.email=:email AND pr.token_hash=:hash LIMIT 1 FOR UPDATE');
            $statement->execute(['email' => $email, 'hash' => hash('sha256', $token)]);
            $record = $statement->fetch();
            if (!$record || $record->used_at || $record->revoked_at || strtotime($record->expires_at) < $now->getTimestamp()) {
                $pdo->rollBack();
                return false;
            }
            $updateUser = $pdo->prepare('UPDATE users SET password=:password,forget=NULL WHERE id=:user');
            $updateUser->execute(['password' => passwd($password), 'user' => $record->user_id]);
            $use = $pdo->prepare('UPDATE password_reset_tokens SET used_at=:now WHERE id=:id AND used_at IS NULL');
            $use->execute(['now' => $now->format('Y-m-d H:i:s'), 'id' => $record->id]);
            if ($use->rowCount() !== 1) { $pdo->rollBack(); return false; }
            $pdo->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
    }
}
