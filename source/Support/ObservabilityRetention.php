<?php

declare(strict_types=1);

namespace Source\Support;

use DateTimeImmutable;
use PDO;

final class ObservabilityRetention
{
    public const RESOLVED_INCIDENT_DAYS = 90;
    public const AUDIT_DAYS = 1825;
    public const ACCESS_REPORT_DAYS = 395;
    public const ONLINE_SESSION_DAYS = 1;

    /** @return array<string,array{table:string,date_column:string,where:string,days:int}> */
    public static function plan(): array
    {
        return [
            'resolved_incidents' => ['table' => 'app_log', 'date_column' => 'last_seen_at', 'where' => "status IN ('resolved','ignored')", 'days' => self::RESOLVED_INCIDENT_DAYS],
            'audit_trail' => ['table' => 'system_audit_logs', 'date_column' => 'created_at', 'where' => '1=1', 'days' => self::AUDIT_DAYS],
            'access_reports' => ['table' => 'report_access', 'date_column' => 'created_at', 'where' => '1=1', 'days' => self::ACCESS_REPORT_DAYS],
            'online_sessions' => ['table' => 'report_online', 'date_column' => 'updated_at', 'where' => '1=1', 'days' => self::ONLINE_SESSION_DAYS],
        ];
    }

    /** @return array<string,int> */
    public static function run(PDO $pdo, bool $apply = false, ?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now');
        $result = [];
        foreach (self::plan() as $name => $policy) {
            $exists = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($policy['table']))->fetchColumn();
            if (!$exists) {
                $result[$name] = 0;
                continue;
            }
            $cutoff = $now->modify('-' . $policy['days'] . ' days')->format('Y-m-d H:i:s');
            $sql = " FROM {$policy['table']} WHERE {$policy['where']} AND {$policy['date_column']} < :cutoff";
            $statement = $pdo->prepare(($apply ? 'DELETE' : 'SELECT COUNT(*)') . $sql);
            $statement->execute(['cutoff' => $cutoff]);
            $result[$name] = $apply ? $statement->rowCount() : (int)$statement->fetchColumn();
        }
        return $result;
    }
}
