<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use ReflectionClass;
use Source\Controllers\Operation\ServiceDesk;
use Source\Controllers\Studio\Studio;
use Source\Services\ServiceDesk\AgendaService;
use Source\Services\ServiceDesk\TicketService;

final class ServiceDeskBoundaryTest extends TestCase
{
    public function testAgendaServiceIsViewFreeAndSharedByEnvironmentControllers(): void
    {
        $ownerId = $this->createUser(['email' => 'owner-agenda@test.local']);
        $service = new AgendaService($this->pdo);
        $eventId = $service->save([
            'title' => 'Agenda compartilhada', 'description' => 'Criada sem controller.',
            'starts_at' => '2026-09-12T09:00', 'ends_at' => '2026-09-12T10:00',
            'type' => 'meeting', 'status' => 'scheduled', 'assigned_to' => (string)$ownerId,
            'participants' => [(string)$ownerId],
        ], $ownerId);

        self::assertGreaterThan(0, $eventId);
        self::assertSame(1, (int)$this->pdo->query("SELECT COUNT(*) FROM operation_calendar_participants WHERE event_id={$eventId}")->fetchColumn());
        $service->save([
            'event_id' => (string)$eventId, 'title' => 'Agenda compartilhada atualizada',
            'starts_at' => '2026-09-12T09:00', 'ends_at' => '2026-09-12T10:30',
            'type' => 'meeting', 'status' => 'completed', 'assigned_to' => (string)$ownerId,
        ], $ownerId);
        $calendar = $service->events(['month' => '2026-09', 'status' => 'completed']);
        self::assertCount(1, $calendar['events']);
        self::assertSame('Agenda compartilhada atualizada', $calendar['events'][0]->title);
        self::assertTrue($service->delete($eventId));
        self::assertFalse($service->delete($eventId));

        $reflection = new ReflectionClass(AgendaService::class);
        self::assertFalse($reflection->hasProperty('view'));
        self::assertStringNotContainsString('Source\\Core\\View', file_get_contents($reflection->getFileName()));
    }

    public function testAgendaServiceRejectsInvalidPeriod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new AgendaService($this->pdo))->save([
            'title' => 'Período inválido', 'starts_at' => '2026-09-12T10:00',
            'ends_at' => '2026-09-12T09:00',
        ], 1);
    }

    public function testTicketServiceCoversWorkflowWithoutViews(): void
    {
        $userId=$this->createUser(['email'=>'ticket-service@test.local']);$service=new TicketService($this->pdo);
        $ticket=$service->create(['subject'=>'Falha no portão social','message'=>'O motor parou durante o fechamento.','area'=>'technical','priority'=>'urgent','requester_id'=>$userId,'assigned_to'=>$userId],$userId);
        self::assertSame(12,strlen($ticket->protocol));self::assertSame('urgent',$ticket->priority);
        self::assertTrue($service->update($ticket->id,['status'=>'in_progress','priority'=>'high','assigned_to'=>$userId,'team'=>'Manutenção','category'=>'Portaria','tags'=>'portão'],$userId));
        $messageId=$service->reply($ticket->id,'Equipe técnica acionada.',true,$userId,900);self::assertGreaterThan(0,$messageId);
        $templateId=$service->template('Acionamento técnico','Equipe técnica foi acionada.',$userId);self::assertGreaterThan(0,$templateId);
        self::assertCount(1,$service->queue(['q'=>'portão social','priority'=>'high']));
        self::assertSame(1,$service->bulk([$ticket->id],'closed',$userId));
        self::assertSame('closed',$this->pdo->query("SELECT status FROM studio_support_tickets WHERE id={$ticket->id}")->fetchColumn());
        self::assertTrue($service->deleteTemplate($templateId));
        $reflection=new ReflectionClass(TicketService::class);self::assertFalse($reflection->hasProperty('view'));self::assertStringNotContainsString('Source\\Core\\View',file_get_contents($reflection->getFileName()));
        self::assertSame('Source\\Core\\Controller',(new ReflectionClass(ServiceDesk::class))->getParentClass()->getName());
        $operationSource=file_get_contents((new ReflectionClass(ServiceDesk::class))->getFileName());$studioSource=file_get_contents((new ReflectionClass(Studio::class))->getFileName());
        self::assertStringContainsString('new TicketService(', $operationSource);
        self::assertStringContainsString('new TicketService(', $studioSource);
        self::assertStringNotContainsString('extends \\Source\\Controllers\\Studio\\Studio', $operationSource);
    }
}
