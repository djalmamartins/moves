<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use DateTimeImmutable;
use MovesOSTests\TestCase;
use Source\Support\ObservabilityRetention;

final class ObservabilityRetentionTest extends TestCase
{
    public function testDryRunDoesNotDeleteAndApplyPreservesOpenIncidents(): void
    {
        $this->pdo->exec("INSERT INTO app_log(level,channel,msg,status,last_seen_at) VALUES
            ('error','test','resolvido antigo','resolved','2025-01-01 00:00:00'),
            ('error','test','ignorado antigo','ignored','2025-01-01 00:00:00'),
            ('error','test','aberto antigo','open','2025-01-01 00:00:00'),
            ('info','test','resolvido recente','resolved','2026-08-01 00:00:00')");
        $now = new DateTimeImmutable('2026-09-07 12:00:00');

        $preview = ObservabilityRetention::run($this->pdo, false, $now);
        self::assertSame(2, $preview['resolved_incidents']);
        self::assertSame(4, (int)$this->pdo->query('SELECT COUNT(*) FROM app_log')->fetchColumn());

        $applied = ObservabilityRetention::run($this->pdo, true, $now);
        self::assertSame(2, $applied['resolved_incidents']);
        self::assertSame(2, (int)$this->pdo->query('SELECT COUNT(*) FROM app_log')->fetchColumn());
        self::assertSame(1, (int)$this->pdo->query("SELECT COUNT(*) FROM app_log WHERE status='open'")->fetchColumn());
        self::assertSame(1, (int)$this->pdo->query("SELECT COUNT(*) FROM app_log WHERE msg='resolvido recente'")->fetchColumn());
    }

    public function testRetentionPlanKeepsResponsibilitiesExplicit(): void
    {
        $plan = ObservabilityRetention::plan();
        self::assertSame(90, $plan['resolved_incidents']['days']);
        self::assertSame("status IN ('resolved','ignored')", $plan['resolved_incidents']['where']);
        self::assertSame(1825, $plan['audit_trail']['days']);
        self::assertSame(395, $plan['access_reports']['days']);
        self::assertSame(1, $plan['online_sessions']['days']);
    }
}
