<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use ReflectionClass;
use Source\Controllers\Operation\Operation;
use Source\Models\User;

final class OperationVisitWorkflowTest extends TestCase
{
    public function testVisitLifecyclePersistsChecklistOccurrenceAndHistoryWithUserTwo(): void
    {
        $ownerId = $this->createUser([
            'first_name' => 'Proprietário',
            'last_name' => 'Principal',
            'email' => 'owner-operation@test.local',
            'document' => '52998224725',
            'level' => 10,
        ]);
        self::assertSame(1, $ownerId);
        $operatorId = $this->createUser(['first_name' => 'Mariana', 'last_name' => 'Oliveira', 'level' => 5]);
        self::assertSame(2, $operatorId);

        $this->pdo->prepare("INSERT INTO operation_condominiums(name,address,latitude,longitude,geofence_radius,status,created_by) VALUES(?,?,?,?,?,'active',?)")
            ->execute(['Condomínio Teste', 'Rua Teste, 100', -19.9245000, -43.9352000, 100, $operatorId]);
        $condominiumId = (int)$this->pdo->lastInsertId();
        $this->pdo->prepare("INSERT INTO operation_visits(condominium_id,title,objective,visit_type,scheduled_at,status,assigned_to,created_by) VALUES(?,?,?,'management',NOW(),'scheduled',?,?)")
            ->execute([$condominiumId, 'Visita de gestão', 'Verificar áreas comuns', $operatorId, $operatorId]);
        $visitId = (int)$this->pdo->lastInsertId();
        $this->pdo->prepare("INSERT INTO operation_visit_items(visit_id,title,area,category,priority,result,comment_required_on_failure) VALUES(?,?,'Portaria','Segurança','critical','pending',1)")
            ->execute([$visitId, 'Portão fechando corretamente?']);
        $itemId = (int)$this->pdo->lastInsertId();

        $this->pdo->prepare("UPDATE operation_visits SET status='in_progress',started_at=NOW(),checkin_latitude=?,checkin_longitude=?,checkin_accuracy=8,checkin_device='Automated test' WHERE id=?")
            ->execute([-19.9245000, -43.9352000, $visitId]);
        $this->pdo->prepare("UPDATE operation_visit_items SET result='nonconforming',notes='Motor exige manutenção',checked_by=?,checked_at=NOW() WHERE id=?")
            ->execute([$operatorId, $itemId]);
        $this->pdo->prepare("INSERT INTO operation_issues(condominium_id,visit_id,title,description,category,priority,status,created_by) VALUES(?,?,?,'Motor com ruído','Segurança','critical','open',?)")
            ->execute([$condominiumId, $visitId, 'Revisar portão', $operatorId]);
        $this->pdo->prepare("INSERT INTO operation_visit_events(visit_id,event_type,summary,user_id) VALUES(?,'start','Visita iniciada',?),(?,'item','Não conformidade registrada',?),(?,'finish','Visita finalizada',?)")
            ->execute([$visitId,$operatorId,$visitId,$operatorId,$visitId,$operatorId]);
        $this->pdo->prepare("UPDATE operation_visits SET status='completed',completed_at=NOW(),summary='Visita concluída com uma pendência' WHERE id=?")
            ->execute([$visitId]);

        self::assertSame('completed', $this->pdo->query("SELECT status FROM operation_visits WHERE id={$visitId}")->fetchColumn());
        self::assertSame('nonconforming', $this->pdo->query("SELECT result FROM operation_visit_items WHERE id={$itemId}")->fetchColumn());
        self::assertSame(1, (int)$this->pdo->query("SELECT COUNT(*) FROM operation_issues WHERE visit_id={$visitId}")->fetchColumn());
        self::assertSame(3, (int)$this->pdo->query("SELECT COUNT(*) FROM operation_visit_events WHERE visit_id={$visitId}")->fetchColumn());
        self::assertNotNull((new User())->findById(1));
    }

    public function testServerGeofenceDistanceCalculation(): void
    {
        $method = (new ReflectionClass(Operation::class))->getMethod('distanceMeters');
        $operation = (new ReflectionClass(Operation::class))->newInstanceWithoutConstructor();

        self::assertLessThan(2.0, $method->invoke($operation, -19.9245, -43.9352, -19.9245, -43.9352));
        self::assertGreaterThan(100.0, $method->invoke($operation, -19.9245, -43.9352, -19.9225, -43.9352));
    }

    public function testVisitAgendaAndDifferentOutcomeTypesRemainLinked(): void
    {
        $operatorId = $this->createUser(['first_name' => 'Mariana', 'last_name' => 'Alves', 'level' => 5]);
        $this->pdo->prepare("INSERT INTO operation_condominiums(name,status,created_by) VALUES(?,'active',?)")->execute(['Condomínio Agenda', $operatorId]);
        $condominiumId=(int)$this->pdo->lastInsertId();
        $this->pdo->prepare("INSERT INTO operation_visits(condominium_id,title,visit_type,scheduled_at,status,created_by) VALUES(?,?,'management',NOW(),'in_progress',?)")->execute([$condominiumId,'Visita integrada',$operatorId]);
        $visitId=(int)$this->pdo->lastInsertId();
        $this->pdo->prepare("INSERT INTO operation_visit_agenda_items(visit_id,source_type,source_id,title,priority) VALUES(?,'demand',?,?, 'high')")->execute([$visitId,91,'Assunto crítico da gestão']);
        $this->pdo->prepare("INSERT INTO operation_visit_outcomes(visit_id,outcome_type,outcome_id,title,created_by) VALUES(?,'task',?, ?,?)")->execute([$visitId,77,'Retorno ao fornecedor',$operatorId]);

        self::assertSame('high',$this->pdo->query("SELECT priority FROM operation_visit_agenda_items WHERE visit_id={$visitId}")->fetchColumn());
        self::assertSame('task',$this->pdo->query("SELECT outcome_type FROM operation_visit_outcomes WHERE visit_id={$visitId}")->fetchColumn());
        self::assertNotNull((new User())->findById(1));
    }

    public function testOfflineSyncIdentifierIsIdempotent(): void
    {
        $operatorId=$this->createUser(['first_name'=>'Mariana','last_name'=>'Alves','level'=>5]);
        $this->pdo->prepare("INSERT INTO operation_condominiums(name,status,created_by) VALUES(?,'active',?)")->execute(['Condomínio Offline',$operatorId]);$condominiumId=(int)$this->pdo->lastInsertId();
        $this->pdo->prepare("INSERT INTO operation_visits(condominium_id,title,visit_type,scheduled_at,status,created_by) VALUES(?,?,'inspection',NOW(),'in_progress',?)")->execute([$condominiumId,'Visita offline',$operatorId]);$visitId=(int)$this->pdo->lastInsertId();
        $syncId='123e4567-e89b-12d3-a456-426614174000';$statement=$this->pdo->prepare("INSERT INTO operation_visit_sync_queue(id,visit_id,user_id,operation_type,payload_json,status) VALUES(?,?,?,'item','{}','synced') ON DUPLICATE KEY UPDATE attempts=attempts+1");$statement->execute([$syncId,$visitId,$operatorId]);$statement->execute([$syncId,$visitId,$operatorId]);
        self::assertSame(1,(int)$this->pdo->query("SELECT COUNT(*) FROM operation_visit_sync_queue WHERE id='{$syncId}'")->fetchColumn());
        self::assertSame(1,(int)$this->pdo->query("SELECT attempts FROM operation_visit_sync_queue WHERE id='{$syncId}'")->fetchColumn());
    }
}
