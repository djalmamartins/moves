<?php

namespace Source\Services\ServiceDesk;

use PDO;
use Source\Support\Audit;

final class AgendaService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function delete(int $eventId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM studio_calendar_events WHERE id=:id');
        $statement->execute(['id' => $eventId]);
        if (!$statement->rowCount()) return false;
        Audit::record('delete', 'studio_calendar_events', $eventId);
        return true;
    }

    public function save(array $data, int $userId): int
    {
        $eventId = (int)($data['event_id'] ?? 0);
        $title = mb_substr(trim(strip_tags((string)($data['title'] ?? ''))), 0, 180);
        $starts = strtotime((string)($data['starts_at'] ?? ''));
        $ends = !empty($data['ends_at']) ? strtotime((string)$data['ends_at']) : null;
        if (mb_strlen($title) < 3 || !$starts || ($ends && $ends < $starts)) {
            throw new \InvalidArgumentException('Informe título e período válidos.');
        }
        $recurrenceRule = (string)($data['recurrence_rule'] ?? '');
        $recurrence = in_array($recurrenceRule, ['', 'FREQ=DAILY', 'FREQ=WEEKLY', 'FREQ=BIWEEKLY', 'FREQ=MONTHLY', 'FREQ=QUARTERLY'], true) ? ($recurrenceRule ?: null) : null;
        $values = [
            'condo' => (int)($data['condominium_id'] ?? 0) ?: null,
            'title' => $title,
            'description' => trim(strip_tags((string)($data['description'] ?? ''))),
            'starts' => date('Y-m-d H:i:s', $starts),
            'ends' => $ends ? date('Y-m-d H:i:s', $ends) : null,
            'recurrence' => $recurrence,
            'reminder' => (int)($data['reminder_minutes'] ?? 0) ?: null,
            'location' => mb_substr(trim(strip_tags((string)($data['location'] ?? ''))), 0, 255) ?: null,
            'type' => in_array($data['type'] ?? '', ['meeting', 'task', 'deadline', 'support'], true) ? $data['type'] : 'meeting',
            'status' => in_array($data['status'] ?? '', ['scheduled', 'completed', 'cancelled'], true) ? $data['status'] : 'scheduled',
            'assigned' => (int)($data['assigned_to'] ?? 0) ?: null,
            'entity_type' => mb_substr(trim((string)($data['operation_entity_type'] ?? '')), 0, 40) ?: null,
            'entity_id' => (int)($data['operation_entity_id'] ?? 0) ?: null,
        ];
        if ($eventId) {
            $values['id'] = $eventId;
            $statement = $this->pdo->prepare('UPDATE studio_calendar_events SET condominium_id=:condo,title=:title,description=:description,starts_at=:starts,ends_at=:ends,recurrence_rule=:recurrence,reminder_minutes=:reminder,location=:location,type=:type,status=:status,assigned_to=:assigned,operation_entity_type=:entity_type,operation_entity_id=:entity_id WHERE id=:id');
            $statement->execute($values);
            $action = 'update';
        } else {
            $values['creator'] = $userId;
            $statement = $this->pdo->prepare('INSERT INTO studio_calendar_events(condominium_id,title,description,starts_at,ends_at,recurrence_rule,reminder_minutes,location,type,status,assigned_to,operation_entity_type,operation_entity_id,created_by) VALUES(:condo,:title,:description,:starts,:ends,:recurrence,:reminder,:location,:type,:status,:assigned,:entity_type,:entity_id,:creator)');
            $statement->execute($values);
            $eventId = (int)$this->pdo->lastInsertId();
            $action = 'create';
        }
        $this->pdo->prepare('DELETE FROM operation_calendar_participants WHERE event_id=:event')->execute(['event' => $eventId]);
        $participant = $this->pdo->prepare('INSERT IGNORE INTO operation_calendar_participants(event_id,user_id) VALUES(:event,:user)');
        foreach (array_unique(array_map('intval', (array)($data['participants'] ?? []))) as $participantId) {
            if ($participantId > 0) $participant->execute(['event' => $eventId, 'user' => $participantId]);
        }
        Audit::record($action, 'studio_calendar_events', $eventId, [], ['title' => $title, 'condominium_id' => $values['condo']]);
        return $eventId;
    }

    public function events(array $filters): array
    {
        $month = (string)($filters['month'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) $month = date('Y-m');
        $params = ['from' => $month . '-01 00:00:00', 'to' => date('Y-m-d H:i:s', strtotime($month . '-01 +1 month'))];
        $terms = ["(e.starts_at>=:from OR (e.recurrence_rule IS NOT NULL AND e.recurrence_rule<>''))", 'e.starts_at<:to'];
        $type = in_array($filters['type'] ?? '', ['meeting', 'task', 'deadline', 'support'], true) ? $filters['type'] : '';
        $status = in_array($filters['status'] ?? '', ['scheduled', 'completed', 'cancelled'], true) ? $filters['status'] : '';
        $assigned = (int)($filters['assigned_to'] ?? 0) ?: null;
        $condo = (int)($filters['condominium_id'] ?? 0) ?: null;
        if ($type) { $terms[] = 'e.type=:type'; $params['type'] = $type; }
        if ($status) { $terms[] = 'e.status=:status'; $params['status'] = $status; }
        if ($assigned) { $terms[] = 'e.assigned_to=:assigned'; $params['assigned'] = $assigned; }
        if ($condo) { $terms[] = 'e.condominium_id=:condo'; $params['condo'] = $condo; }
        $statement = $this->pdo->prepare("SELECT e.*,CONCAT(u.first_name,' ',u.last_name) assigned_name,c.name condominium_name,c.latitude,c.longitude,'event' source_type,1 editable FROM studio_calendar_events e LEFT JOIN users u ON u.id=e.assigned_to LEFT JOIN operation_condominiums c ON c.id=e.condominium_id WHERE " . implode(' AND ', $terms) . ' ORDER BY e.starts_at');
        $statement->execute($params);
        return ['month' => $month, 'from' => $params['from'], 'to' => $params['to'], 'events' => $statement->fetchAll() ?: [], 'type' => $type, 'status' => $status, 'assigned' => $assigned, 'condominium' => $condo];
    }
}
