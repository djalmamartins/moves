<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use Source\Controllers\Operation\Operation;
use Source\Controllers\Operation\ServiceDesk;
use Source\Models\User;
use Source\Support\Access;

final class CriticalOperationCrudTest extends TestCase
{
    private Operation $operation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createUser([
            'first_name' => 'Proprietário', 'last_name' => 'Principal',
            'email' => 'owner-critical@test.local', 'document' => '52998224725', 'level' => 10,
        ]);
        $operatorId = $this->createUser([
            'first_name' => 'Mariana', 'last_name' => 'Operadora',
            'email' => 'operator-critical@test.local', 'document' => '11144477735', 'level' => 2,
        ]);
        self::assertSame(2, $operatorId);

        $this->pdo->exec("INSERT INTO access_roles(name,slug,level,description) VALUES('Operador de teste','operator',30,'Perfil operacional não developer')");
        $roleId = (int)$this->pdo->lastInsertId();
        $permissions = [
            'operation.access' => 'Acessar Operacional',
            'operation.demands.view' => 'Visualizar demandas',
            'operation.visits.manage' => 'Gerenciar visitas',
            'operation.agenda.manage' => 'Gerenciar agenda',
            'studio.access' => 'Acessar base administrativa',
            'support.manage' => 'Gerenciar chamados',
        ];
        $permission = $this->pdo->prepare("INSERT INTO access_permissions(name,slug,group_name,description) VALUES(:name,:slug,'Operacional','Teste E2E')");
        $grant = $this->pdo->prepare('INSERT INTO access_role_permissions(role_id,permission_id) VALUES(:role,:permission)');
        foreach ($permissions as $slug => $name) {
            $permission->execute(['name' => $name, 'slug' => $slug]);
            $grant->execute(['role' => $roleId, 'permission' => (int)$this->pdo->lastInsertId()]);
        }
        $this->pdo->prepare('INSERT INTO access_user_roles(user_id,role_id,assigned_by) VALUES(2,:role,1)')->execute(['role' => $roleId]);
        Access::clear();

        $_SESSION['authUser'] = 2;
        $_SESSION['csrf_token'] = 'critical-operation-token';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/operation';
        $this->operation = new Operation();
    }

    public function testCondominiumDemandAndVisitCrudWithFiltersPaginationAndCsrf(): void
    {
        $invalid = $this->post('condominiums', [
            'csrf' => 'invalid-token', 'name' => 'Não deve persistir', 'status' => 'active',
        ]);
        self::assertStringContainsString('Sessão expirada', html_entity_decode($invalid['message'] ?? '', ENT_QUOTES, 'UTF-8'));
        self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM operation_condominiums')->fetchColumn());

        $this->post('condominiums', [
            'csrf' => 'critical-operation-token', 'name' => 'Condomínio Solar',
            'document' => '12.345.678/0001-90', 'address' => 'Rua das Flores, 123',
            'city' => 'Belo Horizonte', 'state' => 'MG', 'geofence_radius' => '100', 'status' => 'implementation',
        ]);
        $condominiumId = (int)$this->pdo->query("SELECT id FROM operation_condominiums WHERE name='Condomínio Solar'")->fetchColumn();
        self::assertGreaterThan(0, $condominiumId);

        $this->post('condominiums', [
            'id' => (string)$condominiumId, 'csrf' => 'critical-operation-token',
            'name' => 'Condomínio Solar Atualizado', 'document' => '12.345.678/0001-90',
            'address' => 'Rua das Flores, 123', 'city' => 'Belo Horizonte', 'state' => 'MG',
            'geofence_radius' => '120', 'status' => 'active',
        ]);
        self::assertSame('active', $this->pdo->query("SELECT status FROM operation_condominiums WHERE id={$condominiumId}")->fetchColumn());

        $this->post('demands', [
            'csrf' => 'critical-operation-token', 'condominium_id' => (string)$condominiumId,
            'title' => 'Revisar portão social', 'description' => 'Motor apresenta ruído.',
            'category' => 'Portaria', 'assigned_to' => '2', 'priority' => 'high',
            'status' => 'new', 'due_at' => '2026-09-10T12:00',
        ]);
        $demandId = (int)$this->pdo->query("SELECT id FROM operation_demands WHERE title='Revisar portão social'")->fetchColumn();
        self::assertGreaterThan(0, $demandId);
        $this->post('demands', [
            'id' => (string)$demandId, 'csrf' => 'critical-operation-token',
            'condominium_id' => (string)$condominiumId, 'title' => 'Revisar portão principal',
            'description' => 'Motor avaliado.', 'category' => 'Portaria', 'assigned_to' => '2',
            'priority' => 'urgent', 'status' => 'analysis', 'due_at' => '2026-09-11T12:00',
        ]);
        self::assertSame('analysis', $this->pdo->query("SELECT status FROM operation_demands WHERE id={$demandId}")->fetchColumn());

        $this->post('visits', [
            'csrf' => 'critical-operation-token', 'condominium_id' => (string)$condominiumId,
            'title' => 'Visita técnica do portão', 'visit_type' => 'technical',
            'scheduled_at' => '2026-09-05T09:00', 'ends_at' => '2026-09-05T10:00',
            'assigned_to' => '2', 'objective' => 'Inspecionar o motor.', 'status' => 'scheduled',
            'signature_required' => '0', 'recurrence_rule' => '', 'notes' => 'Levar checklist.',
        ]);
        $visitId = (int)$this->pdo->query("SELECT id FROM operation_visits WHERE title='Visita técnica do portão'")->fetchColumn();
        self::assertGreaterThan(0, $visitId);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['q' => 'portão principal', 'status' => 'analysis', 'page' => '1', 'per_page' => '10'];
        ob_start();
        $this->operation->demands([]);
        $html = (string)ob_get_clean();
        self::assertStringContainsString('Revisar portão principal', $html);
        self::assertStringNotContainsString('Visita técnica do portão', $html);

        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->post('visits', ['id' => (string)$visitId, 'csrf' => 'critical-operation-token', 'action' => 'delete']);
        $this->post('demands', ['id' => (string)$demandId, 'csrf' => 'critical-operation-token', 'action' => 'delete']);
        $this->post('condominiums', ['id' => (string)$condominiumId, 'csrf' => 'critical-operation-token', 'action' => 'delete']);

        self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM operation_visits')->fetchColumn());
        self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM operation_demands')->fetchColumn());
        self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM operation_condominiums')->fetchColumn());
    }

    public function testUsersAgendaAndTicketsCrudAsNonDeveloperOperator(): void
    {
        $user = (new User())->bootstrap('Carlos', 'Síndico', 'carlos-sindico@test.local', '12345678909', 'Senha@123');
        self::assertTrue($user->save());
        $userId = (int)$user->id;
        $user->first_name = 'Carlos Atualizado';
        self::assertTrue($user->save());
        self::assertSame('Carlos Atualizado', (new User())->findById($userId)->first_name);
        $filteredUsers = (new User())->find('first_name LIKE :name', 'name=%Atualizado%')->limit(10)->offset(0)->fetch(true);
        self::assertCount(1, $filteredUsers);

        $agendaCreate = $this->capture(fn() => $this->operation->agenda([
            'csrf' => 'critical-operation-token', 'title' => 'Reunião semanal do condomínio',
            'description' => 'Analisar demandas abertas.', 'starts_at' => '2026-09-08T10:00',
            'ends_at' => '2026-09-08T11:00', 'type' => 'meeting', 'status' => 'scheduled',
            'assigned_to' => '2', 'participants' => ['2'],
        ]));
        self::assertStringContainsString('/operation/agenda', $agendaCreate['redirect'] ?? '');
        $eventId = (int)$this->pdo->query("SELECT id FROM studio_calendar_events WHERE title='Reunião semanal do condomínio'")->fetchColumn();
        self::assertGreaterThan(0, $eventId);
        $this->capture(fn() => $this->operation->agenda([
            'csrf' => 'critical-operation-token', 'event_id' => (string)$eventId,
            'title' => 'Reunião semanal atualizada', 'description' => 'Pauta concluída.',
            'starts_at' => '2026-09-08T10:00', 'ends_at' => '2026-09-08T11:30',
            'type' => 'meeting', 'status' => 'completed', 'assigned_to' => '2',
        ]));
        self::assertSame('completed', $this->pdo->query("SELECT status FROM studio_calendar_events WHERE id={$eventId}")->fetchColumn());

        $_SERVER['REQUEST_URI'] = '/operation/tickets';
        $tickets = new ServiceDesk();
        $ticketCreate = $this->capture(fn() => $tickets->tickets([
            'csrf' => 'critical-operation-token', 'action' => 'create',
            'subject' => 'Portão com falha intermitente',
            'message' => 'O portão para durante o fechamento e precisa de análise.',
            'area' => 'technical', 'priority' => 'high', 'requester_id' => '2', 'assigned_to' => '2',
        ]));
        self::assertStringContainsString('/operation/tickets', $ticketCreate['redirect'] ?? '');
        $ticketId = (int)$this->pdo->query("SELECT id FROM studio_support_tickets WHERE subject='Portão com falha intermitente'")->fetchColumn();
        self::assertGreaterThan(0, $ticketId);
        $this->capture(fn() => $tickets->tickets([
            'csrf' => 'critical-operation-token', 'action' => 'update', 'ticket_id' => (string)$ticketId,
            'status' => 'closed', 'priority' => 'high', 'assigned_to' => '2',
            'team' => 'Manutenção', 'category' => 'Portaria', 'tags' => 'portão,urgente',
        ]));
        self::assertSame('closed', $this->pdo->query("SELECT status FROM studio_support_tickets WHERE id={$ticketId}")->fetchColumn());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['q' => 'falha intermitente', 'status' => 'closed', 'priority' => 'high'];
        ob_start();
        $tickets->tickets([]);
        $ticketHtml = (string)ob_get_clean();
        self::assertStringContainsString('Portão com falha intermitente', $ticketHtml);

        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->capture(fn() => $this->operation->agenda([
            'csrf' => 'critical-operation-token', 'event_id' => (string)$eventId, 'action' => 'delete',
        ]));
        self::assertSame(0, (int)$this->pdo->query("SELECT COUNT(*) FROM studio_calendar_events WHERE id={$eventId}")->fetchColumn());
        self::assertTrue($user->destroy());
        self::assertNull((new User())->findById($userId));
    }

    /** @return array<string,mixed> */
    private function post(string $resource, array $payload): array
    {
        http_response_code(200);
        ob_start();
        $this->operation->{$resource}($payload);
        $body = (string)ob_get_clean();
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded, $body);
        return $decoded;
    }

    /** @return array<string,mixed> */
    private function capture(callable $action): array
    {
        http_response_code(200);
        ob_start();
        $action();
        $body = (string)ob_get_clean();
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded, $body);
        return $decoded;
    }
}
