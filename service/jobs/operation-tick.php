<?php

declare(strict_types=1);

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Source\Core\Connect;

try {
    $pdo = Connect::getInstance();
    $pdo->beginTransaction();
    $pdo->exec("UPDATE operation_documents SET status=CASE WHEN valid_until<CURDATE() THEN 'expired' WHEN valid_until<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) THEN 'expiring' ELSE 'valid' END WHERE status<>'archived' AND valid_until IS NOT NULL");
    $pdo->exec("UPDATE operation_quotes SET status='expired' WHERE valid_until<CURDATE() AND status NOT IN ('approved','rejected','expired')");
    $rows = $pdo->query("SELECT c.id,c.health_score,
      GREATEST(0,LEAST(100,100-
        18*(SELECT COUNT(*) FROM operation_issues i WHERE i.condominium_id=c.id AND i.status IN ('open','in_progress','waiting') AND i.priority='critical')-
        8*(SELECT COUNT(*) FROM operation_issues i WHERE i.condominium_id=c.id AND i.status IN ('open','in_progress','waiting') AND i.priority='high')-
        3*(SELECT COUNT(*) FROM operation_issues i WHERE i.condominium_id=c.id AND i.status IN ('open','in_progress','waiting') AND i.priority IN ('medium','low'))-
        5*(SELECT COUNT(*) FROM operation_demands d WHERE d.condominium_id=c.id AND d.status NOT IN ('completed','cancelled') AND d.due_at<NOW())
      )) calculated_score FROM operation_condominiums c WHERE c.status<>'inactive'")->fetchAll() ?: [];
    $update = $pdo->prepare('UPDATE operation_condominiums SET health_score=:score WHERE id=:id');
    $activity = $pdo->prepare("INSERT INTO operation_activity(entity_type,entity_id,action,summary,payload_json) VALUES('condominium',:id,'health_recalculated',:summary,:payload)");
    foreach ($rows as $row) {
        if ((int)$row->health_score === (int)$row->calculated_score) continue;
        $update->execute(['score' => (int)$row->calculated_score, 'id' => (int)$row->id]);
        $activity->execute(['id' => (int)$row->id, 'summary' => 'Índice operacional recalculado', 'payload' => json_encode(['before' => (int)$row->health_score, 'after' => (int)$row->calculated_score])]);
    }
    $pdo->commit();
    fwrite(STDOUT, 'Operation atualizado: ' . count($rows) . " condomínio(s).\n");
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Falha no ciclo Operation: ' . $exception->getMessage() . "\n");
    exit(1);
}
