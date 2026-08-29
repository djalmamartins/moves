<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use ReflectionClass;
use Source\Controllers\Studio\Studio;
use Source\Models\User;

final class ServiceDeskTest extends TestCase
{
    public function testTicketStoresUrgentSlaAndInteractionHistory(): void
    {
        $userId = $this->createUser(['level' => 10]);
        $dueAt = date('Y-m-d H:i:s', time() + 86400);
        $this->pdo->prepare('INSERT INTO studio_support_tickets(protocol,subject,message,area,priority,status,requester_id,assigned_to,created_by,due_at) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute(['CH240101ABC1','Falha crítica','O serviço precisa de atendimento imediato.','technical','urgent','open',$userId,$userId,$userId,$dueAt]);
        $ticketId = (int)$this->pdo->lastInsertId();
        $this->pdo->prepare('INSERT INTO studio_support_ticket_messages(ticket_id,user_id,message,is_internal) VALUES (?,?,?,?)')
            ->execute([$ticketId,$userId,'Equipe acionada.',1]);

        $ticket = $this->pdo->query("SELECT * FROM studio_support_tickets WHERE id={$ticketId}")->fetch();
        self::assertSame('urgent', $ticket->priority);
        self::assertSame($dueAt, $ticket->due_at);
        self::assertSame(1, (int)$this->pdo->query("SELECT COUNT(*) FROM studio_support_ticket_messages WHERE ticket_id={$ticketId}")->fetchColumn());
    }

    public function testTicketProtocolFitsTheDatabaseColumn(): void
    {
        $protocol = 'CH' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        self::assertSame(12, strlen($protocol));
        $userId = $this->createUser();
        $this->pdo->prepare('INSERT INTO studio_support_tickets(protocol,subject,message,area,priority,status,requester_id,created_by,due_at) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$protocol, 'Validação de protocolo', 'O protocolo deve caber na coluna sem truncamento.', 'general', 'medium', 'open', $userId, $userId, date('Y-m-d H:i:s', time() + 72 * 3600)]);
        self::assertSame($protocol, $this->pdo->query('SELECT protocol FROM studio_support_tickets LIMIT 1')->fetchColumn());
    }

    public function testCalendarPersistsAssignedSupportEvent(): void
    {
        $userId = $this->createUser();
        $this->pdo->prepare('INSERT INTO studio_calendar_events(title,starts_at,type,assigned_to,created_by) VALUES (?,?,?,?,?)')
            ->execute(['Retorno de atendimento',date('Y-m-d 10:00:00'),'support',$userId,$userId]);
        $event = $this->pdo->query('SELECT * FROM studio_calendar_events ORDER BY id DESC LIMIT 1')->fetch();
        self::assertSame('support', $event->type);
        self::assertSame($userId, (int)$event->assigned_to);
    }

    public function testOpeningTicketQueuesEmailAndCentralNotificationForAdminAndDeveloper(): void
    {
        $requesterId = $this->createUser(['email' => 'requester@example.com']);
        $adminId = $this->createUser(['email' => 'admin@example.com', 'level' => 5]);
        $developerId = $this->createUser(['email' => 'developer@example.com', 'level' => 10]);
        $dueAt = date('Y-m-d H:i:s', time() + 86400);

        $this->pdo->prepare('INSERT INTO studio_support_tickets(protocol,subject,message,area,priority,status,requester_id,created_by,due_at) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute(['CH240101ABC2', 'Teste de entrega', 'Teste real de notificação e fila de e-mail.', 'technical', 'urgent', 'open', $requesterId, $requesterId, $dueAt]);
        $ticketId = (int)$this->pdo->lastInsertId();

        $reflection = new ReflectionClass(Studio::class);
        $studio = $reflection->newInstanceWithoutConstructor();
        $notify = $reflection->getMethod('notifyTicketOpened');
        $notify->invoke($studio, $ticketId, 'CH240101ABC2', 'Teste de entrega', 'urgent', $dueAt, null, $requesterId);

        $recipients = $this->pdo->query('SELECT users_id FROM mail_queue ORDER BY users_id')->fetchAll(\PDO::FETCH_COLUMN);
        self::assertSame([$requesterId, $adminId, $developerId], array_map('intval', $recipients));
        self::assertSame(3, (int)$this->pdo->query('SELECT COUNT(*) FROM notifications')->fetchColumn());
        self::assertSame(3, (int)$this->pdo->query("SELECT COUNT(*) FROM notifications WHERE title LIKE 'Novo chamado%'")->fetchColumn());
    }

    public function testAgendaEventQueuesEmailAndCentralNotificationForAdminAndDeveloper(): void
    {
        $creatorId = $this->createUser(['email' => 'creator@example.com']);
        $adminId = $this->createUser(['email' => 'agenda-admin@example.com', 'level' => 5]);
        $developerId = $this->createUser(['email' => 'agenda-developer@example.com', 'level' => 10]);
        $startsAt = date('Y-m-d 10:00:00', strtotime('+1 day'));
        $this->pdo->prepare('INSERT INTO studio_calendar_events(title,starts_at,type,created_by) VALUES (?,?,?,?)')
            ->execute(['Teste de agenda', $startsAt, 'support', $creatorId]);
        $eventId = (int)$this->pdo->lastInsertId();

        $reflection = new ReflectionClass(Studio::class);
        $studio = $reflection->newInstanceWithoutConstructor();
        $notify = $reflection->getMethod('notifyAgendaEvent');
        $notify->invoke($studio, $eventId, 'Teste de agenda', $startsAt, 'support', null, (new User())->findById($creatorId));

        $recipients = $this->pdo->query('SELECT users_id FROM mail_queue ORDER BY users_id')->fetchAll(\PDO::FETCH_COLUMN);
        self::assertSame([$creatorId, $adminId, $developerId], array_map('intval', $recipients));
        self::assertSame(3, (int)$this->pdo->query("SELECT COUNT(*) FROM notifications WHERE title='Novo compromisso na agenda'")->fetchColumn());
    }
}
