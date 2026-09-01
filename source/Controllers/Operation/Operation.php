<?php

namespace Source\Controllers\Operation;

use Source\Core\Connect;
use Source\Models\Auth;
use Source\Models\User;
use Source\Support\Access;
use Source\Support\Audit;
use Source\Support\Upload;

/**
 * Ambiente operacional da Connect.
 *
 * A camada HTTP possui rotas, views e assets próprios. As regras de negócio
 * administrativas permanecem herdadas enquanto os módulos são compartilhados,
 * evitando duplicar ACL, auditoria e persistência.
 */
class Operation extends \Source\Controllers\Studio\Studio
{
    private const RESOURCES = [
        'demands' => ['table' => 'operation_demands', 'title' => 'Demandas', 'singular' => 'Demanda', 'icon' => 'warning-outline', 'permission' => 'operation.demands.view', 'auto_protocol' => 'DEM', 'fields' => [
            'condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'required' => true], 'title' => ['label' => 'Demanda', 'required' => true], 'description' => ['label' => 'Descrição', 'type' => 'textarea'], 'category' => ['label' => 'Categoria'],
            'assigned_to' => ['label' => 'Responsável', 'type' => 'user'], 'priority' => ['label' => 'Prioridade', 'type' => 'select', 'options' => ['low' => 'Baixa', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente']],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['new' => 'Nova', 'analysis' => 'Em análise', 'in_progress' => 'Em andamento', 'waiting_third_party' => 'Aguardando terceiro', 'waiting_condominium' => 'Aguardando condomínio', 'completed' => 'Concluída', 'cancelled' => 'Cancelada']], 'due_at' => ['label' => 'Prazo', 'type' => 'datetime-local']
        ]],
        'condominiums' => ['table' => 'operation_condominiums', 'title' => 'Condomínios', 'singular' => 'Condomínio', 'icon' => 'business-outline', 'fields' => [
            'name' => ['label' => 'Nome', 'required' => true], 'document' => ['label' => 'CNPJ'], 'address' => ['label' => 'Endereço'], 'city' => ['label' => 'Cidade'], 'state' => ['label' => 'UF'],
            'geofence_radius' => ['label' => 'Raio permitido (m)', 'type' => 'number', 'default' => 100], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['implementation' => 'Implantação', 'active' => 'Ativo', 'inactive' => 'Inativo']]
        ]],
        'visits' => ['table' => 'operation_visits', 'title' => 'Visitas', 'singular' => 'Visita', 'icon' => 'calendar-outline', 'permission' => 'operation.visits.manage', 'fields' => [
            'condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'required' => true], 'title' => ['label' => 'Título', 'required' => true],
            'visit_type' => ['label' => 'Tipo', 'type' => 'select', 'options' => ['periodic'=>'Periódica','management' => 'Gestão', 'inspection' => 'Vistoria','meeting'=>'Reunião','follow_up'=>'Acompanhamento','technical'=>'Técnica','emergency'=>'Emergencial','implementation' => 'Implantação', 'maintenance' => 'Manutenção']],
            'scheduled_at' => ['label' => 'Início', 'type' => 'datetime-local', 'required' => true], 'ends_at'=>['label'=>'Término previsto','type'=>'datetime-local'], 'assigned_to'=>['label'=>'Responsável','type'=>'user'], 'objective'=>['label'=>'Objetivo','type'=>'textarea'], 'recurrence_rule'=>['label'=>'Recorrência','type'=>'select','options'=>[''=>'Não repetir','FREQ=DAILY'=>'Diariamente','FREQ=WEEKLY'=>'Semanalmente','FREQ=BIWEEKLY'=>'A cada 2 semanas','FREQ=MONTHLY'=>'Mensalmente','FREQ=QUARTERLY'=>'Trimestralmente']], 'signature_required'=>['label'=>'Exigir assinatura','type'=>'select','options'=>['0'=>'Não','1'=>'Sim']], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['scheduled' => 'Agendada', 'in_progress' => 'Em andamento', 'completed' => 'Concluída', 'cancelled' => 'Cancelada']], 'notes' => ['label' => 'Observações', 'type' => 'textarea']
        ]],
        'quotes' => ['table' => 'operation_quotes', 'title' => 'Orçamentos', 'singular' => 'Orçamento', 'icon' => 'calculator-outline', 'permission' => 'operation.quotes.manage', 'auto_protocol' => 'ORC', 'fields' => [
            'condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'required' => true], 'demand_id' => ['label' => 'Demanda relacionada', 'type' => 'demand'], 'title' => ['label' => 'Descrição', 'required' => true], 'description' => ['label' => 'Detalhes', 'type' => 'textarea'],
            'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Rascunho', 'requested' => 'Solicitado', 'received' => 'Recebido', 'analysis' => 'Em análise', 'waiting_approval' => 'Aguardando aprovação', 'approved' => 'Aprovado', 'rejected' => 'Reprovado', 'expired' => 'Expirado']], 'valid_until' => ['label' => 'Validade', 'type' => 'date'], 'assigned_to' => ['label' => 'Responsável', 'type' => 'user']
        ]],
        'suppliers' => ['table' => 'operation_suppliers', 'title' => 'Fornecedores', 'singular' => 'Fornecedor', 'icon' => 'people-outline', 'permission' => 'operation.suppliers.manage', 'fields' => [
            'legal_name' => ['label' => 'Razão social', 'required' => true], 'trade_name' => ['label' => 'Nome fantasia'], 'document' => ['label' => 'CNPJ/CPF'], 'category' => ['label' => 'Categoria/serviço'], 'contact_name' => ['label' => 'Contato'], 'phone' => ['label' => 'Telefone'], 'email' => ['label' => 'E-mail', 'type' => 'email'], 'address' => ['label' => 'Endereço'], 'city' => ['label' => 'Cidade'], 'state' => ['label' => 'UF'], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Ativo', 'inactive' => 'Inativo', 'blocked' => 'Bloqueado']]
        ]],
        'people' => ['table' => 'operation_people', 'title' => 'Moradores e Síndicos', 'singular' => 'Pessoa', 'icon' => 'people-circle-outline', 'permission' => 'operation.people.manage', 'fields' => [
            'name' => ['label' => 'Nome', 'required' => true], 'document' => ['label' => 'Documento'], 'phone' => ['label' => 'Telefone'], 'email' => ['label' => 'E-mail', 'type' => 'email'], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Ativo', 'inactive' => 'Inativo']],
            'link_condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'virtual' => true], 'relation_type' => ['label' => 'Vínculo', 'type' => 'select', 'virtual' => true, 'options' => ['resident' => 'Morador', 'owner' => 'Proprietário', 'tenant' => 'Inquilino', 'syndic' => 'Síndico', 'subsyndic' => 'Subsíndico', 'councillor' => 'Conselheiro']], 'block_label' => ['label' => 'Bloco', 'virtual' => true], 'unit_label' => ['label' => 'Unidade', 'virtual' => true]
        ]],
        'checklists' => ['table' => 'operation_checklists', 'title' => 'Checklists', 'singular' => 'Checklist', 'icon' => 'checkbox-outline', 'fields' => [
            'name' => ['label' => 'Nome', 'required' => true], 'category' => ['label' => 'Categoria'], 'description' => ['label' => 'Descrição', 'type' => 'textarea'], 'active' => ['label' => 'Situação', 'type' => 'select', 'options' => ['1' => 'Ativo', '0' => 'Inativo']]
        ]],
        'issues' => ['table' => 'operation_issues', 'title' => 'Pendências', 'singular' => 'Pendência', 'icon' => 'warning-outline', 'fields' => [
            'condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'required' => true], 'title' => ['label' => 'Título', 'required' => true], 'description' => ['label' => 'Descrição', 'type' => 'textarea'], 'category' => ['label' => 'Categoria'],
            'priority' => ['label' => 'Prioridade', 'type' => 'select', 'options' => ['low' => 'Baixa', 'medium' => 'Média', 'high' => 'Alta', 'critical' => 'Crítica']], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['open' => 'Aberta', 'in_progress' => 'Em execução', 'waiting' => 'Aguardando', 'resolved' => 'Resolvida', 'cancelled' => 'Cancelada']], 'due_at' => ['label' => 'Prazo', 'type' => 'datetime-local']
        ]],
        'action-plans' => ['table' => 'operation_action_plans', 'title' => 'Planos de ação', 'singular' => 'Plano de ação', 'icon' => 'clipboard-outline', 'fields' => [
            'issue_id' => ['label' => 'Pendência', 'type' => 'issue', 'required' => true], 'title' => ['label' => 'Título', 'required' => true], 'description' => ['label' => 'Descrição', 'type' => 'textarea'], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['planned' => 'Planejado', 'in_progress' => 'Em execução', 'completed' => 'Concluído', 'cancelled' => 'Cancelado']], 'due_at' => ['label' => 'Prazo', 'type' => 'datetime-local']
        ]],
        'assets' => ['table' => 'operation_assets', 'title' => 'Equipamentos', 'singular' => 'Equipamento', 'icon' => 'construct-outline', 'fields' => [
            'condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'required' => true], 'name' => ['label' => 'Nome', 'required' => true], 'category' => ['label' => 'Categoria'], 'serial_number' => ['label' => 'Número de série'], 'location' => ['label' => 'Localização'], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Ativo', 'maintenance' => 'Em manutenção', 'inactive' => 'Inativo']], 'next_maintenance_at' => ['label' => 'Próxima manutenção', 'type' => 'datetime-local']
        ]],
        'requests' => ['table' => 'operation_resident_requests', 'title' => 'Desejos dos moradores', 'singular' => 'Solicitação', 'icon' => 'heart-outline', 'permission' => 'operation.people.manage', 'fields' => [
            'condominium_id' => ['label' => 'Condomínio', 'type' => 'condominium', 'required' => true], 'title' => ['label' => 'Título', 'required' => true], 'description' => ['label' => 'Descrição', 'type' => 'textarea'], 'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['submitted' => 'Enviado', 'reviewing' => 'Em análise', 'voting' => 'Em votação', 'approved' => 'Aprovado', 'planned' => 'Planejado', 'rejected' => 'Recusado']], 'votes' => ['label' => 'Votos', 'type' => 'number', 'default' => 0]
        ]]
    ];

    public function root(): void
    {
        $user=Auth::user();
        redirect($user && Access::can('studio.access',$user) && Access::can('operation.access',$user) ? '/operation/dash' : '/operation/login');
    }

    public function dash(): void
    {
        $this->operationUser('operation.access');
        parent::dash();
    }

    public function realtime(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        $user = Auth::user();
        if (!$user || !Access::can('studio.access', $user) || !Access::can('operation.access', $user)) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }
        try {
            $pdo = Connect::getInstance();
            $counts = $pdo->query("SELECT
              (SELECT COUNT(*) FROM operation_visits WHERE DATE(scheduled_at)=CURDATE() AND status<>'cancelled') visits_today,
              (SELECT COUNT(*) FROM operation_visit_items WHERE result='pending') checklist_pending,
              (SELECT COUNT(*) FROM operation_issues WHERE status IN ('open','in_progress','waiting')) issues_open,
              (SELECT COUNT(*) FROM operation_issues WHERE status IN ('open','in_progress','waiting') AND due_at<NOW()) issues_overdue,
              (SELECT COUNT(*) FROM operation_action_plans WHERE status IN ('planned','in_progress')) plans_open,
              (SELECT COUNT(*) FROM operation_assets WHERE status='maintenance' OR next_maintenance_at<=DATE_ADD(NOW(),INTERVAL 7 DAY)) assets_attention,
              (SELECT COUNT(*) FROM operation_demands WHERE status NOT IN ('completed','cancelled')) demands_open,
              (SELECT COUNT(*) FROM studio_support_tickets WHERE status NOT IN ('resolved','closed')) tickets_open,
              (SELECT COUNT(*) FROM operation_quotes WHERE status IN ('requested','received','analysis','waiting_approval')) quotes_pending,
              (SELECT COUNT(*) FROM operation_tasks WHERE status IN ('pending','in_progress') AND due_at<NOW()) tasks_overdue,
              (SELECT COUNT(*) FROM operation_documents WHERE valid_until BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY)) documents_expiring")->fetch();
            $cursor = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM operation_activity")->fetchColumn();
            echo json_encode(['counts' => $counts, 'cursor' => $cursor, 'server_time' => date(DATE_ATOM)], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $exception) {
            http_response_code(503);
            echo json_encode(['error' => 'operation_schema_unavailable']);
        }
    }

    public function meuDia(): void
    {
        $user = Auth::user();
        if (!$user || !Access::can('studio.access', $user) || !Access::can('operation.access', $user) || !Access::can('dashboard.view', $user)) {
            redirect($user ? '/operation/ops/403' : '/operation/login');
        }
        $data = ['dayAgenda' => [], 'pendingTasks' => [], 'currentVisit' => null, 'appointmentsCount' => 0, 'scheduledTasksCount' => 0, 'toScheduleCount' => 0, 'waitingThirdPartiesCount' => 0, 'weeklyVisitsCount' => 0, 'overdueCount'=>0, 'criticalTicketsCount'=>0, 'unassignedDemandsCount'=>0];
        try {
            $pdo = Connect::getInstance();
            $data['dayAgenda'] = $pdo->query("SELECT * FROM (SELECT v.id,v.title,v.objective description,v.status,v.scheduled_at starts_at,v.ends_at,DATE_FORMAT(v.scheduled_at,'%H:%i') time,c.name condominium_name,CONCAT(u.first_name,' ',u.last_name) responsible_name,'visit' source_type,CONCAT('/operation/visitas/',v.id) source_url FROM operation_visits v JOIN operation_condominiums c ON c.id=v.condominium_id LEFT JOIN users u ON u.id=v.assigned_to WHERE DATE(v.scheduled_at)=CURDATE() AND v.status<>'cancelled' UNION ALL SELECT e.id,e.title,e.description,e.status,e.starts_at,e.ends_at,DATE_FORMAT(e.starts_at,'%H:%i'),c.name,CONCAT(u.first_name,' ',u.last_name),'event','/operation/agenda' FROM studio_calendar_events e LEFT JOIN operation_condominiums c ON c.id=e.condominium_id LEFT JOIN users u ON u.id=e.assigned_to WHERE DATE(e.starts_at)=CURDATE() AND e.status<>'cancelled' UNION ALL SELECT t.id,t.title,t.description,t.status,COALESCE(t.starts_at,t.due_at),t.due_at,DATE_FORMAT(COALESCE(t.starts_at,t.due_at),'%H:%i'),c.name,CONCAT(u.first_name,' ',u.last_name),'task',CONCAT('/operation/demandas/',t.demand_id) FROM operation_tasks t JOIN operation_condominiums c ON c.id=t.condominium_id LEFT JOIN users u ON u.id=t.assigned_to WHERE DATE(COALESCE(t.starts_at,t.due_at))=CURDATE() AND t.status<>'cancelled') agenda ORDER BY starts_at")->fetchAll() ?: [];
            $data['appointmentsCount'] = count($data['dayAgenda']);
            $data['scheduledTasksCount'] = (int)$pdo->query("SELECT COUNT(*) FROM operation_visit_items WHERE result='pending'")->fetchColumn();
            $data['toScheduleCount'] = (int)$pdo->query("SELECT COUNT(*) FROM operation_visits WHERE status='scheduled' AND scheduled_at>NOW()")->fetchColumn();
            $data['waitingThirdPartiesCount'] = (int)$pdo->query("SELECT COUNT(*) FROM operation_issues WHERE status='waiting'")->fetchColumn();
            $data['weeklyVisitsCount'] = (int)$pdo->query("SELECT COUNT(*) FROM operation_visits WHERE status<>'cancelled' AND YEARWEEK(scheduled_at,1)=YEARWEEK(CURDATE(),1)")->fetchColumn();
            $data['overdueCount']=(int)$pdo->query("SELECT (SELECT COUNT(*) FROM operation_issues WHERE status IN ('open','in_progress','waiting') AND due_at<NOW())+(SELECT COUNT(*) FROM operation_demands WHERE status NOT IN ('completed','cancelled') AND due_at<NOW())+(SELECT COUNT(*) FROM operation_tasks WHERE status IN ('pending','in_progress') AND due_at<NOW())")->fetchColumn();
            $data['criticalTicketsCount']=(int)$pdo->query("SELECT COUNT(*) FROM studio_support_tickets WHERE priority='urgent' AND status NOT IN ('resolved','closed')")->fetchColumn();
            $data['unassignedDemandsCount']=(int)$pdo->query("SELECT COUNT(*) FROM operation_demands WHERE assigned_to IS NULL AND status NOT IN ('completed','cancelled')")->fetchColumn();
            $data['pendingTasks'] = $pdo->query("SELECT * FROM (SELECT id,title,COALESCE(description,category,'Pendência operacional') subtitle,due_at,DATE_FORMAT(due_at,'%d/%m %H:%i') due,CASE priority WHEN 'critical' THEN 'alert-circle-outline' WHEN 'high' THEN 'warning-outline' ELSE 'checkbox-outline' END icon,'issue' source_type,CONCAT('/operation/issues/',id) source_url FROM operation_issues WHERE status IN ('open','in_progress','waiting') UNION ALL SELECT id,title,COALESCE(description,category,'Demanda') subtitle,due_at,DATE_FORMAT(due_at,'%d/%m %H:%i'),CASE priority WHEN 'urgent' THEN 'alert-circle-outline' WHEN 'high' THEN 'warning-outline' ELSE 'clipboard-outline' END,'demand',CONCAT('/operation/demandas/',id) FROM operation_demands WHERE status NOT IN ('completed','cancelled')) work ORDER BY due_at IS NULL,due_at LIMIT 20")->fetchAll() ?: [];
            $visit = $pdo->query("SELECT v.id,v.title,v.status,c.name condominium_name FROM operation_visits v JOIN operation_condominiums c ON c.id=v.condominium_id WHERE DATE(v.scheduled_at)=CURDATE() AND v.status<>'cancelled' ORDER BY FIELD(v.status,'in_progress','scheduled','completed'),v.scheduled_at LIMIT 1")->fetch();
            if ($visit) {
                $stmt = $pdo->prepare("SELECT title,result FROM operation_visit_items WHERE visit_id=:visit ORDER BY id LIMIT 8");
                $stmt->execute(['visit' => $visit->id]);
                $data['currentVisit'] = (object)['id' => $visit->id, 'title' => $visit->title, 'condominium' => $visit->condominium_name, 'status' => $visit->status, 'items' => $stmt->fetchAll() ?: []];
            }
        } catch (\Throwable $exception) {
            \Source\Support\AppLogger::exception($exception, 'operation', ['event_type' => 'operation_my_day_failed']);
        }
        echo $this->view->render('components/dash/my-day', array_merge([
            'head' => $this->seo->render('Meu Dia - Connect Operações', CONF_SITE_DESC, url('/operation/meu-dia'), themeStudio('/assets/images/favicon.png', 'default')),
            'title' => 'Meu Dia', 'app' => 'meu-dia', 'user' => $user, 'currentVersion' => VERSION_STUDIO, 'adminBase' => '/operation'
        ], $data));
    }

    public function condominiums(?array $data): void { $this->resource('condominiums', $data); }
    public function demands(?array $data): void { $this->resource('demands', $data); }
    public function visits(?array $data): void
    {
        $id = (int)($data['id'] ?? 0);
        $action = (string)($data['action'] ?? '');
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['start','item','agenda_item','occurrence','evidence','finish'], true)) {
            $this->visitAction($id, $action, $data ?? []);
            return;
        }
        if ($id && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->visitDetail($id);
            return;
        }
        $this->resource('visits', $data);
    }
    public function checklists(?array $data): void { $this->resource('checklists', $data); }
    public function issues(?array $data): void { $this->resource('issues', $data); }
    public function actionPlans(?array $data): void { $this->resource('action-plans', $data); }
    public function assets(?array $data): void { $this->resource('assets', $data); }
    public function residentRequests(?array $data): void { $this->resource('requests', $data); }
    public function quotes(?array $data): void { $this->resource('quotes', $data); }
    public function suppliers(?array $data): void { $this->resource('suppliers', $data); }
    public function people(?array $data): void { $this->resource('people', $data); }

    public function documents(?array $data): void
    {
        $user = $this->operationUser('operation.documents.manage');
        $pdo = Connect::getInstance();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=UTF-8');
            if (!csrf_verify($data ?? [])) { http_response_code(419); echo json_encode(['message' => $this->message->error('Sessão expirada.')->render()]); return; }
            $id = (int)($data['id'] ?? 0);
            if (($data['action'] ?? '') === 'delete' && $id) {
                $stmt = $pdo->prepare('SELECT file_path FROM operation_documents WHERE id=:id'); $stmt->execute(['id' => $id]); $path = $stmt->fetchColumn();
                if ($path) (new Upload())->remove(dirname(__DIR__, 3) . '/' . CONF_UPLOAD_DIR . '/' . $path);
                $pdo->prepare('DELETE FROM operation_documents WHERE id=:id')->execute(['id' => $id]);
                Audit::record('delete', 'operation_documents', $id); echo json_encode(['redirect' => url('/operation/documentos')]); return;
            }
            $condominiumId = (int)($data['condominium_id'] ?? 0); $title = mb_substr(trim(strip_tags((string)($data['title'] ?? ''))), 0, 180); $category = mb_substr(trim(strip_tags((string)($data['category'] ?? ''))), 0, 100);
            if (!$condominiumId || mb_strlen($title) < 3 || !$category || empty($_FILES['document_file'])) { http_response_code(422); echo json_encode(['message' => $this->message->warning('Informe condomínio, título, categoria e arquivo.')->render()]); return; }
            $upload = new Upload(); $path = $upload->file($_FILES['document_file'], 'operation-' . slug($title) . '-' . time());
            if (!$path) { http_response_code(422); echo json_encode(['message' => $upload->message()->render()]); return; }
            $file = $_FILES['document_file'];
            $stmt = $pdo->prepare("INSERT INTO operation_documents(condominium_id,demand_id,supplier_id,title,category,file_path,original_name,mime_type,file_size,document_date,valid_until,responsible_id,created_by) VALUES(:condo,:demand,:supplier,:title,:category,:path,:original,:mime,:size,:document_date,:valid_until,:responsible,:user)");
            $stmt->execute(['condo'=>$condominiumId,'demand'=>(int)($data['demand_id']??0)?:null,'supplier'=>(int)($data['supplier_id']??0)?:null,'title'=>$title,'category'=>$category,'path'=>$path,'original'=>mb_substr((string)$file['name'],0,255),'mime'=>mb_substr((string)$file['type'],0,120),'size'=>(int)$file['size'],'document_date'=>$data['document_date']?:null,'valid_until'=>$data['valid_until']?:null,'responsible'=>(int)($data['responsible_id']??0)?:null,'user'=>(int)$user->id]);
            $id = (int)$pdo->lastInsertId(); $this->logActivity($pdo,'documents',$id,'created','Documento enviado',(int)$user->id); Audit::record('create','operation_documents',$id,[],['title'=>$title,'category'=>$category]);
            echo json_encode(['redirect'=>url('/operation/documentos')]); return;
        }
        $items = $pdo->query("SELECT d.*,c.name condominium_name,dm.protocol demand_protocol,s.trade_name supplier_name FROM operation_documents d JOIN operation_condominiums c ON c.id=d.condominium_id LEFT JOIN operation_demands dm ON dm.id=d.demand_id LEFT JOIN operation_suppliers s ON s.id=d.supplier_id ORDER BY d.id DESC LIMIT 100")->fetchAll() ?: [];
        echo $this->view->render('components/operation/documents', $this->operationView('Documentos','documents',$user,['items'=>$items,'condominiums'=>$pdo->query("SELECT id,name FROM operation_condominiums WHERE status<>'inactive' ORDER BY name")->fetchAll()?:[],'demands'=>$pdo->query("SELECT id,protocol,title FROM operation_demands ORDER BY id DESC")->fetchAll()?:[],'suppliers'=>$pdo->query("SELECT id,COALESCE(trade_name,legal_name) name FROM operation_suppliers WHERE status='active' ORDER BY name")->fetchAll()?:[],'users'=>$pdo->query("SELECT id,CONCAT(first_name,' ',last_name) name FROM users ORDER BY first_name")->fetchAll()?:[]]));
    }

    public function operationReports(): void
    {
        $user = $this->operationUser('operation.reports.view'); $pdo = Connect::getInstance();
        $summary = $pdo->query("SELECT (SELECT COUNT(*) FROM operation_demands WHERE status NOT IN ('completed','cancelled')) demands_open,(SELECT COUNT(*) FROM operation_demands WHERE status='completed') demands_completed,(SELECT COUNT(*) FROM studio_support_tickets WHERE status NOT IN ('resolved','closed')) tickets_open,(SELECT COUNT(*) FROM operation_visits WHERE status='completed') visits_completed,(SELECT COUNT(*) FROM operation_visits WHERE status='scheduled') visits_pending,(SELECT COUNT(*) FROM operation_quotes WHERE status NOT IN ('approved','rejected','expired')) quotes_pending,(SELECT COUNT(*) FROM operation_issues WHERE status NOT IN ('resolved','cancelled')) issues_open")->fetch();
        $byCondo = $pdo->query("SELECT c.id,c.name,COUNT(DISTINCT d.id) demands,COUNT(DISTINCT v.id) visits,COUNT(DISTINCT i.id) issues FROM operation_condominiums c LEFT JOIN operation_demands d ON d.condominium_id=c.id LEFT JOIN operation_visits v ON v.condominium_id=c.id LEFT JOIN operation_issues i ON i.condominium_id=c.id GROUP BY c.id,c.name ORDER BY demands DESC,issues DESC,c.name")->fetchAll() ?: [];
        echo $this->view->render('components/operation/reports', $this->operationView('Relatórios','reports',$user,['summary'=>$summary,'byCondo'=>$byCondo]));
    }

    public function agenda(?array $data): void
    {
        $user=$this->operationUser('operation.agenda.manage'); $pdo=Connect::getInstance();
        if($_SERVER['REQUEST_METHOD']==='POST'){
            header('Content-Type: application/json; charset=UTF-8'); if(!csrf_verify($data??[])){http_response_code(419);echo json_encode(['message'=>$this->message->error('Sessão expirada.')->render()]);return;}
            $id=(int)($data['event_id']??0); if(($data['action']??'')==='delete'&&$id){$pdo->prepare('DELETE FROM studio_calendar_events WHERE id=:id')->execute(['id'=>$id]);Audit::record('delete','studio_calendar_events',$id);echo json_encode(['redirect'=>url('/operation/agenda')]);return;}
            $title=mb_substr(trim(strip_tags((string)($data['title']??''))),0,180);$starts=strtotime((string)($data['starts_at']??''));$ends=!empty($data['ends_at'])?strtotime((string)$data['ends_at']):null;
            if(mb_strlen($title)<3||!$starts||($ends&&$ends<$starts)){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Informe título e período válidos.')->render()]);return;}
            $recurrence=in_array($data['recurrence_rule']??'',['','FREQ=DAILY','FREQ=WEEKLY','FREQ=BIWEEKLY','FREQ=MONTHLY','FREQ=QUARTERLY'],true)?($data['recurrence_rule']?:null):null;
            $values=['condo'=>(int)($data['condominium_id']??0)?:null,'title'=>$title,'description'=>trim(strip_tags((string)($data['description']??''))),'starts'=>date('Y-m-d H:i:s',$starts),'ends'=>$ends?date('Y-m-d H:i:s',$ends):null,'recurrence'=>$recurrence,'reminder'=>(int)($data['reminder_minutes']??0)?:null,'location'=>mb_substr(trim(strip_tags((string)($data['location']??''))),0,255)?:null,'type'=>in_array($data['type']??'',['meeting','task','deadline','support'],true)?$data['type']:'meeting','status'=>in_array($data['status']??'',['scheduled','completed','cancelled'],true)?$data['status']:'scheduled','assigned'=>(int)($data['assigned_to']??0)?:null,'entity_type'=>mb_substr(trim((string)($data['operation_entity_type']??'')),0,40)?:null,'entity_id'=>(int)($data['operation_entity_id']??0)?:null];
            if($id){$values['id']=$id;$pdo->prepare('UPDATE studio_calendar_events SET condominium_id=:condo,title=:title,description=:description,starts_at=:starts,ends_at=:ends,recurrence_rule=:recurrence,reminder_minutes=:reminder,location=:location,type=:type,status=:status,assigned_to=:assigned,operation_entity_type=:entity_type,operation_entity_id=:entity_id WHERE id=:id')->execute($values);$action='update';}
            else{$values['creator']=(int)$user->id;$pdo->prepare('INSERT INTO studio_calendar_events(condominium_id,title,description,starts_at,ends_at,recurrence_rule,reminder_minutes,location,type,status,assigned_to,operation_entity_type,operation_entity_id,created_by) VALUES(:condo,:title,:description,:starts,:ends,:recurrence,:reminder,:location,:type,:status,:assigned,:entity_type,:entity_id,:creator)')->execute($values);$id=(int)$pdo->lastInsertId();$action='create';}
            $pdo->prepare('DELETE FROM operation_calendar_participants WHERE event_id=:event')->execute(['event'=>$id]);
            $participant=$pdo->prepare("INSERT IGNORE INTO operation_calendar_participants(event_id,user_id) VALUES(:event,:user)");foreach(array_unique(array_map('intval',(array)($data['participants']??[]))) as $participantId)if($participantId>0)$participant->execute(['event'=>$id,'user'=>$participantId]);
            Audit::record($action,'studio_calendar_events',$id,[],['title'=>$title,'condominium_id'=>$values['condo']]);$this->logActivity($pdo,'agenda',$id,$action,$title,(int)$user->id);echo json_encode(['redirect'=>url('/operation/agenda')]);return;
        }
        $month=(string)($_GET['month']??date('Y-m'));if(!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$month))$month=date('Y-m');$view=in_array($_GET['view']??'month',['day','week','month','list','map'],true)?$_GET['view']:'month';$terms=["(e.starts_at>=:from OR (e.recurrence_rule IS NOT NULL AND e.recurrence_rule<>''))",'e.starts_at<:to'];$params=['from'=>$month.'-01 00:00:00','to'=>date('Y-m-d H:i:s',strtotime($month.'-01 +1 month'))];
        $type=in_array($_GET['type']??'',['meeting','task','deadline','support','visit'],true)?$_GET['type']:'';$status=in_array($_GET['status']??'',['scheduled','completed','cancelled'],true)?$_GET['status']:'';$assigned=(int)($_GET['assigned_to']??0)?:null;$condo=(int)($_GET['condominium_id']??0)?:null;
        if($type){$terms[]='e.type=:type';$params['type']=$type;}if($status){$terms[]='e.status=:status';$params['status']=$status;}if($assigned){$terms[]='e.assigned_to=:assigned';$params['assigned']=$assigned;}if($condo){$terms[]='e.condominium_id=:condo';$params['condo']=$condo;}
        $stmt=$pdo->prepare("SELECT e.*,CONCAT(u.first_name,' ',u.last_name) assigned_name,c.name condominium_name,c.latitude,c.longitude,'event' source_type,1 editable FROM studio_calendar_events e LEFT JOIN users u ON u.id=e.assigned_to LEFT JOIN operation_condominiums c ON c.id=e.condominium_id WHERE ".implode(' AND ',$terms).' ORDER BY e.starts_at');$stmt->execute($params);$events=$type==='visit'?[]:($stmt->fetchAll()?:[]);
        if($type===''||$type==='visit'){$visitTerms=["(v.scheduled_at>=:from OR (v.recurrence_rule IS NOT NULL AND v.recurrence_rule<>''))","v.scheduled_at<:to"]; $visitParams=['from'=>$params['from'],'to'=>$params['to']];if($status){$visitTerms[]='v.status=:status';$visitParams['status']=$status;}if($assigned){$visitTerms[]='v.assigned_to=:assigned';$visitParams['assigned']=$assigned;}if($condo){$visitTerms[]='v.condominium_id=:condo';$visitParams['condo']=$condo;}$visits=$pdo->prepare("SELECT v.id,v.condominium_id,v.title,v.objective description,v.scheduled_at starts_at,v.ends_at,v.recurrence_rule,NULL reminder_minutes,c.address location,'visit' type,v.status,v.assigned_to,CONCAT(u.first_name,' ',u.last_name) assigned_name,c.name condominium_name,c.latitude,c.longitude,'visit' source_type,0 editable FROM operation_visits v LEFT JOIN users u ON u.id=v.assigned_to JOIN operation_condominiums c ON c.id=v.condominium_id WHERE ".implode(' AND ',$visitTerms));$visits->execute($visitParams);$events=array_merge($events,$visits->fetchAll()?:[]);}
        $events=$this->expandCalendarRecurrences($events,$params['from'],$params['to']);usort($events,static fn($a,$b)=>strcmp($a->starts_at,$b->starts_at));
        echo $this->view->render('components/agenda/home',$this->operationView('Agenda','agenda',$user,['month'=>$month,'viewMode'=>$view,'events'=>$events,'users'=>(new User())->find("status != :status","status=trash")->order('first_name,last_name')->fetch(true)?:[],'condominiums'=>$pdo->query("SELECT id,name FROM operation_condominiums WHERE status<>'inactive' ORDER BY name")->fetchAll()?:[],'typeFilter'=>$type,'statusFilter'=>$status,'assignedFilter'=>$assigned,'condominiumFilter'=>$condo]));
    }

    private function operationUser(string $permission)
    {
        $user=Auth::user(); if(!$user||!Access::can('studio.access',$user)) redirect('/operation/login'); if(!Access::can($permission,$user)) redirect('/operation/ops/403'); return $user;
    }

    private function operationView(string $title,string $app,$user,array $data=[]): array
    {
        return array_merge(['head'=>$this->seo->render($title.' - Connect Operações',CONF_SITE_DESC,url('/operation'),themeStudio('/assets/images/favicon.png','default')),'title'=>$title,'app'=>$app,'user'=>$user,'currentVersion'=>VERSION_STUDIO,'adminBase'=>'/operation'],$data);
    }

    public function visitReport(array $data): void
    {
        $user=$this->operationUser('operation.visits.manage');$id=(int)($data['id']??0);$pdo=Connect::getInstance();$stmt=$pdo->prepare("SELECT v.*,c.name condominium_name,c.address,c.city,c.state,CONCAT(u.first_name,' ',u.last_name) assigned_name FROM operation_visits v JOIN operation_condominiums c ON c.id=v.condominium_id LEFT JOIN users u ON u.id=v.assigned_to WHERE v.id=:id");$stmt->execute(['id'=>$id]);$visit=$stmt->fetch();if(!$visit){redirect('/operation/ops/404');return;}$stmt=$pdo->prepare('SELECT * FROM operation_visit_items WHERE visit_id=:id ORDER BY area,category,id');$stmt->execute(['id'=>$id]);$items=$stmt->fetchAll()?:[];$stmt=$pdo->prepare('SELECT * FROM operation_issues WHERE visit_id=:id ORDER BY id');$stmt->execute(['id'=>$id]);$issues=$stmt->fetchAll()?:[];$resultLabels=['pending'=>'Pendente','conforming'=>'Conforme','attention'=>'Atenção','nonconforming'=>'Não conforme','not_applicable'=>'N/A'];$rows='';foreach($items as $item)$rows.='<tr><td>'.htmlspecialchars($item->area?:'Geral').'</td><td>'.htmlspecialchars($item->title).'</td><td>'.htmlspecialchars($resultLabels[$item->result]??$item->result).'</td><td>'.nl2br(htmlspecialchars((string)$item->notes)).'</td></tr>';$issueRows='';foreach($issues as $issue)$issueRows.='<li><strong>'.htmlspecialchars($issue->title).'</strong> — '.htmlspecialchars($issue->priority).' / '.htmlspecialchars($issue->status).'</li>';$duration=$visit->started_at&&$visit->completed_at?gmdate('H:i:s',max(0,strtotime($visit->completed_at)-strtotime($visit->started_at))):'—';$html='<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#172033;font-size:11px}header{border-bottom:3px solid #C5A131;padding-bottom:14px;margin-bottom:18px}h1{margin:4px 0;font-size:22px}small{color:#667085}section{margin:18px 0}.facts{display:table;width:100%}.facts div{display:table-cell;padding:10px;background:#f3f4f6}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #d7dce4;text-align:left}th{background:#f3f4f6}</style></head><body><header><small>CONNECT CONDOMÍNIOS · RELATÓRIO OPERACIONAL</small><h1>'.htmlspecialchars($visit->title).'</h1><div>'.htmlspecialchars($visit->condominium_name).' · Visita #V-'.str_pad((string)$visit->id,4,'0',STR_PAD_LEFT).'</div></header><section class="facts"><div><small>Responsável</small><br><strong>'.htmlspecialchars($visit->assigned_name?:'Não definido').'</strong></div><div><small>Início</small><br><strong>'.htmlspecialchars($visit->started_at?date_fmt($visit->started_at,'d/m/Y H:i'):'—').'</strong></div><div><small>Término</small><br><strong>'.htmlspecialchars($visit->completed_at?date_fmt($visit->completed_at,'d/m/Y H:i'):'—').'</strong></div><div><small>Duração</small><br><strong>'.$duration.'</strong></div></section><section><h2>Objetivo e resumo</h2><p>'.nl2br(htmlspecialchars((string)($visit->objective?:$visit->notes))).'</p><p>'.nl2br(htmlspecialchars((string)$visit->summary)).'</p></section><section><h2>Checklist</h2><table><thead><tr><th>Área</th><th>Item</th><th>Resultado</th><th>Observação</th></tr></thead><tbody>'.$rows.'</tbody></table></section><section><h2>Pendências geradas</h2><ul>'.$issueRows.'</ul></section><footer>Gerado em '.date('d/m/Y H:i').' por '.htmlspecialchars($user->fullName()).'</footer></body></html>';$options=new \Dompdf\Options();$options->set('isRemoteEnabled',false);$dompdf=new \Dompdf\Dompdf($options);$dompdf->loadHtml($html,'UTF-8');$dompdf->setPaper('A4','portrait');$dompdf->render();$pdo->prepare("INSERT INTO operation_visit_events(visit_id,event_type,summary,user_id) VALUES(:visit,'report_generated','Relatório gerado',:user)")->execute(['visit'=>$id,'user'=>(int)$user->id]);$dompdf->stream('visita-'.$id.'.pdf',['Attachment'=>false]);
    }

    private function visitDetail(int $id): void
    {
        $user = $this->operationUser('operation.visits.manage');
        $pdo = Connect::getInstance();
        $stmt = $pdo->prepare("SELECT v.*,c.name condominium_name,c.address,c.city,c.state,c.latitude condominium_latitude,c.longitude condominium_longitude,c.geofence_radius,CONCAT(u.first_name,' ',u.last_name) assigned_name FROM operation_visits v JOIN operation_condominiums c ON c.id=v.condominium_id LEFT JOIN users u ON u.id=v.assigned_to WHERE v.id=:id");
        $stmt->execute(['id' => $id]);
        $visit = $stmt->fetch();
        if (!$visit) { redirect('/operation/ops/404'); return; }
        $query = $pdo->prepare("SELECT i.*,(SELECT COUNT(*) FROM operation_visit_evidence e WHERE e.visit_item_id=i.id) evidence_count FROM operation_visit_items i WHERE i.visit_id=:id ORDER BY COALESCE(i.area,''),COALESCE(i.category,''),i.id");
        $query->execute(['id' => $id]);
        $events = $pdo->prepare("SELECT e.*,CONCAT(u.first_name,' ',u.last_name) user_name FROM operation_visit_events e LEFT JOIN users u ON u.id=e.user_id WHERE e.visit_id=:id ORDER BY e.occurred_at DESC,e.id DESC"); $events->execute(['id'=>$id]);
        $evidence = $pdo->prepare("SELECT e.* FROM operation_visit_evidence e WHERE e.visit_id=:id ORDER BY e.created_at DESC"); $evidence->execute(['id'=>$id]);
        $this->prepareVisitAgenda($pdo, $visit);
        $issues = $pdo->prepare("SELECT * FROM operation_issues WHERE visit_id=:id ORDER BY id DESC"); $issues->execute(['id'=>$id]);
        $agenda = $pdo->prepare("SELECT * FROM operation_visit_agenda_items WHERE visit_id=:id ORDER BY FIELD(priority,'critical','high','normal','low'),position,id"); $agenda->execute(['id'=>$id]);
        $outcomes = $pdo->prepare("SELECT * FROM operation_visit_outcomes WHERE visit_id=:id ORDER BY id DESC"); $outcomes->execute(['id'=>$id]);
        echo $this->view->render('components/visits/detail', $this->operationView($visit->title,'visits',$user,['visit'=>$visit,'items'=>$query->fetchAll()?:[],'events'=>$events->fetchAll()?:[],'evidence'=>$evidence->fetchAll()?:[],'issues'=>$issues->fetchAll()?:[],'agendaItems'=>$agenda->fetchAll()?:[],'outcomes'=>$outcomes->fetchAll()?:[]]));
    }

    private function visitAction(int $id, string $action, array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $user = $this->operationUser('operation.visits.manage');
        if (!csrf_verify($data)) { http_response_code(419); echo json_encode(['message'=>$this->message->error('Sessão expirada.')->render()]); return; }
        $pdo = Connect::getInstance();
        $stmt=$pdo->prepare("SELECT v.*,c.latitude condominium_latitude,c.longitude condominium_longitude,c.geofence_radius FROM operation_visits v JOIN operation_condominiums c ON c.id=v.condominium_id WHERE v.id=:id FOR UPDATE");
        try {
            $pdo->beginTransaction(); $stmt->execute(['id'=>$id]); $visit=$stmt->fetch();
            if(!$visit) throw new \RuntimeException('Visita não encontrada.');
            $syncId=preg_match('/^[a-f0-9-]{36}$/i',(string)($data['sync_id']??''))?(string)$data['sync_id']:null;
            if($syncId){$known=$pdo->prepare('SELECT status FROM operation_visit_sync_queue WHERE id=:id');$known->execute(['id'=>$syncId]);if($known->fetchColumn()==='synced'){$pdo->rollBack();echo json_encode(['redirect'=>url('/operation/visitas/'.$id),'synced'=>true]);return;}$pdo->prepare("INSERT INTO operation_visit_sync_queue(id,visit_id,user_id,device_id,operation_type,payload_json,status) VALUES(:id,:visit,:user,:device,:type,:payload,'processing') ON DUPLICATE KEY UPDATE status='processing',attempts=attempts+1")->execute(['id'=>$syncId,'visit'=>$id,'user'=>(int)$user->id,'device'=>mb_substr((string)($data['device']??''),0,180)?:null,'type'=>$action,'payload'=>json_encode(array_diff_key($data,['csrf'=>true]),JSON_UNESCAPED_UNICODE)]);}
            $summary=''; $payload=[];
            if($action==='start'){
                if($visit->status!=='scheduled') throw new \RuntimeException('Esta visita não está disponível para início.');
                $lat=$this->coordinate($data['latitude']??null);$lng=$this->coordinate($data['longitude']??null);$accuracy=$this->accuracy($data['accuracy']??null);
                if($visit->condominium_latitude!==null&&$visit->condominium_longitude!==null){if($lat===null||$lng===null)throw new \RuntimeException('Autorize a localização para iniciar esta visita.');$distance=$this->distanceMeters((float)$visit->condominium_latitude,(float)$visit->condominium_longitude,$lat,$lng);if($distance>(float)$visit->geofence_radius+$accuracy)throw new \RuntimeException('Você está fora do perímetro permitido para o check-in.');$payload['distance_m']=round($distance,1);}
                $pdo->prepare("UPDATE operation_visits SET status='in_progress',started_at=NOW(),checkin_latitude=:lat,checkin_longitude=:lng,checkin_accuracy=:accuracy,checkin_device=:device WHERE id=:id")->execute(['lat'=>$lat,'lng'=>$lng,'accuracy'=>$accuracy?:null,'device'=>mb_substr((string)($data['device']??($_SERVER['HTTP_USER_AGENT']??'')),0,255),'id'=>$id]);$summary='Visita iniciada';
            } elseif($action==='item'){
                if($visit->status!=='in_progress')throw new \RuntimeException('Inicie a visita antes de responder o checklist.');$itemId=(int)($data['item_id']??0);$result=in_array($data['result']??'',['conforming','attention','nonconforming','not_applicable'],true)?$data['result']:'';$notes=trim(strip_tags((string)($data['notes']??'')));if(!$itemId||!$result)throw new \RuntimeException('Selecione uma resposta válida.');$item=$pdo->prepare('SELECT * FROM operation_visit_items WHERE id=:item AND visit_id=:visit');$item->execute(['item'=>$itemId,'visit'=>$id]);$item=$item->fetch();if(!$item)throw new \RuntimeException('Item não pertence a esta visita.');if($result==='nonconforming'&&$item->comment_required_on_failure&&!$notes)throw new \RuntimeException('Informe a providência ou observação da não conformidade.');$pdo->prepare('UPDATE operation_visit_items SET result=:result,notes=:notes,checked_by=:user,checked_at=NOW() WHERE id=:item')->execute(['result'=>$result,'notes'=>$notes?:null,'user'=>(int)$user->id,'item'=>$itemId]);$summary='Item verificado: '.$item->title;$payload=['item_id'=>$itemId,'result'=>$result];
            } elseif($action==='agenda_item'){
                $agendaId=(int)($data['agenda_item_id']??0);$agendaStatus=in_array($data['agenda_status']??'',['pending','discussed','resolved','dismissed'],true)?$data['agenda_status']:'';
                if(!$agendaId||!$agendaStatus)throw new \RuntimeException('Item de pauta inválido.');
                $update=$pdo->prepare('UPDATE operation_visit_agenda_items SET status=:status WHERE id=:item AND visit_id=:visit');$update->execute(['status'=>$agendaStatus,'item'=>$agendaId,'visit'=>$id]);
                if(!$update->rowCount())throw new \RuntimeException('Item de pauta não encontrado.');
                $summary='Pauta atualizada';$payload=['agenda_item_id'=>$agendaId,'status'=>$agendaStatus];
            } elseif($action==='occurrence'){
                $title=mb_substr(trim(strip_tags((string)($data['title']??''))),0,180);if(mb_strlen($title)<3)throw new \RuntimeException('Informe o título da ocorrência.');
                $description=trim(strip_tags((string)($data['description']??'')))?:null;$category=mb_substr(trim(strip_tags((string)($data['category']??'Ocorrência'))),0,100);$priority=in_array($data['priority']??'',['low','medium','high','critical'],true)?$data['priority']:'medium';$assigned=(int)($data['assigned_to']??0)?:null;$due=!empty($data['due_at'])?str_replace('T',' ',$data['due_at']).':00':null;$outcome=in_array($data['outcome_type']??'',['issue','demand','ticket','task','record'],true)?$data['outcome_type']:'issue';
                $issueId=null;$outcomeId=null;
                if($outcome==='issue'){$issue=$pdo->prepare("INSERT INTO operation_issues(condominium_id,visit_id,title,description,category,priority,status,assigned_to,due_at,created_by) VALUES(:condo,:visit,:title,:description,:category,:priority,'open',:assigned,:due,:user)");$issue->execute(['condo'=>$visit->condominium_id,'visit'=>$id,'title'=>$title,'description'=>$description,'category'=>$category,'priority'=>$priority,'assigned'=>$assigned,'due'=>$due,'user'=>(int)$user->id]);$issueId=$outcomeId=(int)$pdo->lastInsertId();}
                elseif($outcome==='demand'){$protocol='DEM-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(2)));$stmt=$pdo->prepare("INSERT INTO operation_demands(protocol,condominium_id,title,description,category,priority,status,assigned_to,due_at,source_type,source_id,created_by) VALUES(:protocol,:condo,:title,:description,:category,:priority,'new',:assigned,:due,'visit',:visit,:user)");$stmt->execute(['protocol'=>$protocol,'condo'=>$visit->condominium_id,'title'=>$title,'description'=>$description,'category'=>$category,'priority'=>$priority==='critical'?'urgent':($priority==='medium'?'normal':$priority),'assigned'=>$assigned,'due'=>$due,'visit'=>$id,'user'=>(int)$user->id]);$outcomeId=(int)$pdo->lastInsertId();}
                elseif($outcome==='ticket'){$protocol=strtoupper(substr(bin2hex(random_bytes(6)),0,12));$stmt=$pdo->prepare("INSERT INTO studio_support_tickets(protocol,condominium_id,subject,message,area,priority,status,assigned_to,created_by,due_at) VALUES(:protocol,:condo,:title,:description,'technical',:priority,'open',:assigned,:user,:due)");$stmt->execute(['protocol'=>$protocol,'condo'=>$visit->condominium_id,'title'=>$title,'description'=>$description?:$title,'priority'=>$priority==='critical'?'urgent':$priority,'assigned'=>$assigned,'user'=>(int)$user->id,'due'=>$due?:date('Y-m-d H:i:s',strtotime('+2 days'))]);$outcomeId=(int)$pdo->lastInsertId();}
                elseif($outcome==='task'){$stmt=$pdo->prepare("INSERT INTO operation_tasks(condominium_id,title,description,task_type,priority,status,due_at,assigned_to,created_by) VALUES(:condo,:title,:description,'task',:priority,'pending',:due,:assigned,:user)");$stmt->execute(['condo'=>$visit->condominium_id,'title'=>$title,'description'=>$description,'priority'=>$priority==='critical'?'urgent':($priority==='medium'?'normal':$priority),'due'=>$due,'assigned'=>$assigned,'user'=>(int)$user->id]);$outcomeId=(int)$pdo->lastInsertId();}
                $pdo->prepare('INSERT INTO operation_visit_outcomes(visit_id,issue_id,outcome_type,outcome_id,title,created_by) VALUES(:visit,:issue,:type,:outcome,:title,:user)')->execute(['visit'=>$id,'issue'=>$issueId,'type'=>$outcome,'outcome'=>$outcomeId,'title'=>$title,'user'=>(int)$user->id]);
                if($outcomeId)$pdo->prepare("INSERT IGNORE INTO operation_relations(source_type,source_id,target_type,target_id,relation_type,created_by) VALUES('visits',:visit,:type,:target,'generated',:user)")->execute(['visit'=>$id,'type'=>$outcome.'s','target'=>$outcomeId,'user'=>(int)$user->id]);
                $summary='Ocorrência registrada e encaminhada';$payload=['outcome_type'=>$outcome,'outcome_id'=>$outcomeId];
            } elseif($action==='evidence'){
                if(empty($_FILES['evidence_file']))throw new \RuntimeException('Selecione uma foto ou documento.');$itemId=(int)($data['item_id']??0)?:null;if($itemId){$check=$pdo->prepare('SELECT COUNT(*) FROM operation_visit_items WHERE id=:item AND visit_id=:visit');$check->execute(['item'=>$itemId,'visit'=>$id]);if(!(int)$check->fetchColumn())throw new \RuntimeException('Item inválido.');}$upload=new Upload();$path=$upload->file($_FILES['evidence_file'],'visit-'.$id.'-'.time());if(!$path)throw new \RuntimeException('Não foi possível armazenar o arquivo enviado.');$file=$_FILES['evidence_file'];$evidence=$pdo->prepare('INSERT INTO operation_visit_evidence(visit_id,visit_item_id,file_path,original_name,mime_type,file_size,caption,latitude,longitude,created_by) VALUES(:visit,:item,:path,:name,:mime,:size,:caption,:lat,:lng,:user)');$evidence->execute(['visit'=>$id,'item'=>$itemId,'path'=>$path,'name'=>mb_substr((string)$file['name'],0,255),'mime'=>mb_substr((string)$file['type'],0,120),'size'=>(int)$file['size'],'caption'=>mb_substr(trim(strip_tags((string)($data['caption']??''))),0,255)?:null,'lat'=>$this->coordinate($data['latitude']??null),'lng'=>$this->coordinate($data['longitude']??null),'user'=>(int)$user->id]);$summary='Evidência adicionada';$payload=['evidence_id'=>(int)$pdo->lastInsertId(),'item_id'=>$itemId];
            } elseif($action==='finish'){
                if($visit->status!=='in_progress')throw new \RuntimeException('A visita não está em andamento.');$pending=(int)$pdo->query("SELECT COUNT(*) FROM operation_visit_items WHERE visit_id={$id} AND result='pending'")->fetchColumn();if($pending>0&&!Access::can('operation.visits.override_required',$user))throw new \RuntimeException("Ainda existem {$pending} item(ns) sem resposta.");
                $signatureName=mb_substr(trim(strip_tags((string)($data['signature_name']??''))),0,180)?:null;$signaturePath=$this->storeVisitSignature($id,(string)($data['signature_data']??''));if($visit->signature_required&&(!$signatureName||!$signaturePath))throw new \RuntimeException('Informe o nome e faça a assinatura para concluir.');
                $lat=$this->coordinate($data['latitude']??null);$lng=$this->coordinate($data['longitude']??null);$accuracy=$this->accuracy($data['accuracy']??null);$pdo->prepare("UPDATE operation_visits SET status='completed',completed_at=NOW(),summary=:summary,checkout_latitude=:lat,checkout_longitude=:lng,checkout_accuracy=:accuracy,checkout_device=:device,signature_name=:signature,signature_path=:signature_path WHERE id=:id")->execute(['summary'=>trim(strip_tags((string)($data['summary']??'')))?:null,'lat'=>$lat,'lng'=>$lng,'accuracy'=>$accuracy?:null,'device'=>mb_substr((string)($data['device']??($_SERVER['HTTP_USER_AGENT']??'')),0,255),'signature'=>$signatureName,'signature_path'=>$signaturePath,'id'=>$id]);$summary='Visita finalizada';$payload=['pending_overridden'=>$pending,'signed'=>(bool)$signaturePath];
            } else { throw new \RuntimeException('Ação inválida.'); }
            $event=$pdo->prepare('INSERT INTO operation_visit_events(visit_id,event_type,summary,details_json,user_id) VALUES(:visit,:type,:summary,:details,:user)');$event->execute(['visit'=>$id,'type'=>$action,'summary'=>$summary,'details'=>json_encode($payload,JSON_UNESCAPED_UNICODE),'user'=>(int)$user->id]);$this->logActivity($pdo,'visits',$id,$action,$summary,(int)$user->id);Audit::record($action,'operation_visits',$id,[],array_merge(['summary'=>$summary],$payload));if($syncId)$pdo->prepare("UPDATE operation_visit_sync_queue SET status='synced',synced_at=NOW(),last_error=NULL WHERE id=:id")->execute(['id'=>$syncId]);$pdo->commit();echo json_encode(['redirect'=>url('/operation/visitas/'.$id),'message'=>$this->message->success($summary.'.')->render()]);
        } catch(\Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();if(isset($syncId)&&$syncId){$pdo->prepare("UPDATE operation_visit_sync_queue SET status='failed',last_error=:error WHERE id=:id")->execute(['error'=>mb_substr($exception->getMessage(),0,500),'id'=>$syncId]);}http_response_code(422);echo json_encode(['message'=>$this->message->warning($exception->getMessage())->render()]);}
    }

    private function expandCalendarRecurrences(array $events,string $from,string $to): array
    {
        $expanded=[];$rangeStart=new \DateTimeImmutable($from);$rangeEnd=new \DateTimeImmutable($to);$steps=['FREQ=DAILY'=>'+1 day','FREQ=WEEKLY'=>'+1 week','FREQ=BIWEEKLY'=>'+2 weeks','FREQ=MONTHLY'=>'+1 month','FREQ=QUARTERLY'=>'+3 months'];
        foreach($events as $event){$start=new \DateTimeImmutable($event->starts_at);$duration=$event->ends_at?max(0,strtotime($event->ends_at)-strtotime($event->starts_at)):0;$step=($event->source_type??'')==='visit'?null:($steps[$event->recurrence_rule??'']??null);if(!$step){if($start>=$rangeStart&&$start<$rangeEnd)$expanded[]=$event;continue;}while($start<$rangeStart)$start=$start->modify($step);$guard=0;while($start<$rangeEnd&&$guard++<370){$occurrence=clone $event;$occurrence->starts_at=$start->format('Y-m-d H:i:s');$occurrence->ends_at=$duration?$start->modify("+{$duration} seconds")->format('Y-m-d H:i:s'):null;$occurrence->recurring_occurrence=true;$expanded[]=$occurrence;$start=$start->modify($step);}}
        return $expanded;
    }

    private function coordinate($value): ?float { if($value===null||$value==='')return null;return is_numeric($value)?(float)$value:null; }
    private function accuracy($value): float { return is_numeric($value)?max(0,min(1000,(float)$value)):0; }
    private function distanceMeters(float $lat1,float $lon1,float $lat2,float $lon2): float { $earth=6371000;$latDelta=deg2rad($lat2-$lat1);$lonDelta=deg2rad($lon2-$lon1);$a=sin($latDelta/2)**2+cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($lonDelta/2)**2;return $earth*2*atan2(sqrt($a),sqrt(1-$a)); }

    private function prepareVisitAgenda(\PDO $pdo, object $visit): void
    {
        $insert=$pdo->prepare("INSERT IGNORE INTO operation_visit_agenda_items(visit_id,source_type,source_id,title,description,priority,position) VALUES(:visit,:type,:source,:title,:description,:priority,:position)");
        $sources=[
            ["SELECT id,title,description,CASE priority WHEN 'urgent' THEN 'critical' WHEN 'high' THEN 'high' ELSE 'normal' END priority FROM operation_demands WHERE condominium_id=:condo AND status NOT IN ('completed','cancelled') ORDER BY due_at IS NULL,due_at LIMIT 12",'demand',10],
            ["SELECT id,title,description,priority FROM operation_issues WHERE condominium_id=:condo AND status NOT IN ('resolved','cancelled') ORDER BY FIELD(priority,'critical','high','medium','low'),due_at IS NULL,due_at LIMIT 12",'issue',30],
            ["SELECT id,subject title,message description,CASE priority WHEN 'urgent' THEN 'critical' WHEN 'medium' THEN 'normal' ELSE priority END priority FROM studio_support_tickets WHERE condominium_id=:condo AND status NOT IN ('resolved','closed') ORDER BY due_at LIMIT 10",'ticket',50],
            ["SELECT id,title,CONCAT('Documento vence em ',DATE_FORMAT(valid_until,'%d/%m/%Y')) description,CASE WHEN valid_until<CURDATE() THEN 'critical' WHEN valid_until<=DATE_ADD(CURDATE(),INTERVAL 15 DAY) THEN 'high' ELSE 'normal' END priority FROM operation_documents WHERE condominium_id=:condo AND valid_until<=DATE_ADD(CURDATE(),INTERVAL 60 DAY) AND status<>'archived' ORDER BY valid_until LIMIT 10",'document',70],
            ["SELECT id,name title,CONCAT('Manutenção prevista para ',DATE_FORMAT(next_maintenance_at,'%d/%m/%Y')) description,CASE WHEN next_maintenance_at<NOW() THEN 'critical' ELSE 'high' END priority FROM operation_assets WHERE condominium_id=:condo AND (status='maintenance' OR next_maintenance_at<=DATE_ADD(NOW(),INTERVAL 30 DAY)) ORDER BY next_maintenance_at LIMIT 10",'asset',90]
        ];
        foreach($sources as [$sql,$type,$position]){$stmt=$pdo->prepare($sql);$stmt->execute(['condo'=>$visit->condominium_id]);foreach($stmt->fetchAll()?:[] as $row){$priority=in_array($row->priority,['low','normal','high','critical'],true)?$row->priority:'normal';$insert->execute(['visit'=>$visit->id,'type'=>$type,'source'=>$row->id,'title'=>mb_substr($row->title,0,180),'description'=>$row->description,'priority'=>$priority,'position'=>$position++]);}}
    }

    private function storeVisitSignature(int $visitId,string $dataUrl): ?string
    {
        if($dataUrl==='')return null;
        if(!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#',$dataUrl,$match))throw new \RuntimeException('Assinatura inválida.');
        $binary=base64_decode($match[1],true);if($binary===false||strlen($binary)<100||strlen($binary)>2_000_000)throw new \RuntimeException('Assinatura inválida ou muito grande.');
        $relative=trim(CONF_UPLOAD_DIR,'/').'/operation/signatures/visit-'.$visitId.'-'.date('YmdHis').'.png';$absolute=dirname(__DIR__,3).'/'.$relative;$directory=dirname($absolute);
        if(!is_dir($directory)&&!mkdir($directory,0775,true)&&!is_dir($directory))throw new \RuntimeException('Não foi possível preparar a pasta de assinaturas.');
        if(file_put_contents($absolute,$binary,LOCK_EX)===false)throw new \RuntimeException('Não foi possível salvar a assinatura.');
        return $relative;
    }

    private function materializeVisitRecurrences(\PDO $pdo,int $visitId,array $values,int $userId): void
    {
        $steps=['FREQ=DAILY'=>'+1 day','FREQ=WEEKLY'=>'+1 week','FREQ=BIWEEKLY'=>'+2 weeks','FREQ=MONTHLY'=>'+1 month','FREQ=QUARTERLY'=>'+3 months'];$rule=(string)($values['recurrence_rule']??'');if(!isset($steps[$rule]))return;
        $start=new \DateTimeImmutable((string)$values['scheduled_at']);$end=!empty($values['ends_at'])?new \DateTimeImmutable((string)$values['ends_at']):null;$duration=$end?max(0,$end->getTimestamp()-$start->getTimestamp()):0;
        $columns=array_keys(array_intersect_key($values,array_flip(['condominium_id','demand_id','title','objective','visit_type','assigned_to','notes','signature_required'])));$columns=array_merge($columns,['scheduled_at','ends_at','status','recurrence_parent_id','recurrence_key','created_by']);$placeholders=array_map(static fn($column)=>':'.$column,$columns);$insert=$pdo->prepare('INSERT IGNORE INTO operation_visits('.implode(',',$columns).') VALUES('.implode(',',$placeholders).')');
        $copyItems=$pdo->prepare("INSERT INTO operation_visit_items(visit_id,checklist_item_id,title,area,category,priority,photo_required_on_failure,comment_required_on_failure,result) SELECT :child,checklist_item_id,title,area,category,priority,photo_required_on_failure,comment_required_on_failure,'pending' FROM operation_visit_items WHERE visit_id=:parent");
        $occurrence=$start;for($index=1;$index<=12;$index++){$occurrence=$occurrence->modify($steps[$rule]);$row=[];foreach($columns as $column)$row[$column]=$values[$column]??null;$row['scheduled_at']=$occurrence->format('Y-m-d H:i:s');$row['ends_at']=$duration?$occurrence->modify('+'.$duration.' seconds')->format('Y-m-d H:i:s'):null;$row['status']='scheduled';$row['recurrence_parent_id']=$visitId;$row['recurrence_key']=$occurrence->format('YmdHi');$row['created_by']=$userId;$insert->execute($row);if($insert->rowCount())$copyItems->execute(['child'=>(int)$pdo->lastInsertId(),'parent'=>$visitId]);}
    }

    private function resource(string $slug, ?array $request): void
    {
        $user = Auth::user();
        if (!$user || !Access::can('studio.access', $user)) {
            redirect('/operation/login');
        }
        $config = self::RESOURCES[$slug];
        if (!Access::can($config['permission'] ?? 'operation.access', $user)) redirect('/operation/ops/403');
        $pdo = Connect::getInstance();
        $id = (int)($request['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=UTF-8');
            if (!csrf_verify($request ?? [])) {
                http_response_code(419);
                echo json_encode(['message' => $this->message->error('Sessão expirada. Atualize a página.')->render()]);
                return;
            }
            $action = (string)($request['action'] ?? 'save');
            if ($id > 0 && $action === 'comment') {
                $comment=trim(strip_tags((string)($request['comment']??'')));if(mb_strlen($comment)<2){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Escreva um comentário.')->render()]);return;}$pdo->prepare('INSERT INTO operation_comments(entity_type,entity_id,comment,is_internal,created_by) VALUES(:type,:id,:comment,:internal,:user)')->execute(['type'=>$slug,'id'=>$id,'comment'=>$comment,'internal'=>!empty($request['is_internal'])?1:0,'user'=>(int)$user->id]);$this->logActivity($pdo,$slug,$id,'commented','Comentário adicionado',(int)$user->id);Audit::record('comment',$config['table'],$id,[],['comment'=>$comment]);echo json_encode(['redirect'=>url('/operation/'.$slug.'/'.$id),'message'=>$this->message->success('Comentário adicionado.')->render()]);return;
            }
            if ($id > 0 && $action === 'attachment') {
                if(empty($_FILES['attachment_file'])){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Selecione um arquivo.')->render()]);return;}$upload=new Upload();$path=$upload->file($_FILES['attachment_file'],$slug.'-'.$id.'-'.time());if(!$path){http_response_code(422);echo json_encode(['message'=>$upload->message()->render()]);return;}$file=$_FILES['attachment_file'];$pdo->prepare('INSERT INTO operation_attachments(entity_type,entity_id,file_path,original_name,mime_type,file_size,created_by) VALUES(:type,:id,:path,:name,:mime,:size,:user)')->execute(['type'=>$slug,'id'=>$id,'path'=>$path,'name'=>mb_substr((string)$file['name'],0,255),'mime'=>mb_substr((string)$file['type'],0,120),'size'=>(int)$file['size'],'user'=>(int)$user->id]);$this->logActivity($pdo,$slug,$id,'attachment_added','Anexo adicionado',(int)$user->id);echo json_encode(['redirect'=>url('/operation/'.$slug.'/'.$id),'message'=>$this->message->success('Arquivo anexado.')->render()]);return;
            }
            if ($slug==='demands' && $id > 0 && $action === 'create_task') {
                $title=mb_substr(trim(strip_tags((string)($request['task_title']??''))),0,180);$demand=$pdo->prepare('SELECT condominium_id FROM operation_demands WHERE id=:id');$demand->execute(['id'=>$id]);$condominiumId=(int)$demand->fetchColumn();if(!$condominiumId||mb_strlen($title)<3){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Informe o título da tarefa.')->render()]);return;}$pdo->prepare("INSERT INTO operation_tasks(condominium_id,demand_id,title,description,task_type,priority,status,starts_at,due_at,assigned_to,created_by) VALUES(:condo,:demand,:title,:description,'task',:priority,'pending',:starts,:due,:assigned,:user)")->execute(['condo'=>$condominiumId,'demand'=>$id,'title'=>$title,'description'=>trim(strip_tags((string)($request['task_description']??'')))?:null,'priority'=>in_array($request['task_priority']??'',['low','normal','high','urgent'],true)?$request['task_priority']:'normal','starts'=>!empty($request['task_starts_at'])?str_replace('T',' ',$request['task_starts_at']).':00':null,'due'=>!empty($request['task_due_at'])?str_replace('T',' ',$request['task_due_at']).':00':null,'assigned'=>(int)($request['task_assigned_to']??0)?:null,'user'=>(int)$user->id]);$taskId=(int)$pdo->lastInsertId();$pdo->prepare("INSERT IGNORE INTO operation_relations(source_type,source_id,target_type,target_id,relation_type,created_by) VALUES('demands',:demand,'tasks',:task,'generated',:user)")->execute(['demand'=>$id,'task'=>$taskId,'user'=>(int)$user->id]);$this->logActivity($pdo,'demands',$id,'task_created','Tarefa criada a partir da demanda',(int)$user->id);echo json_encode(['redirect'=>url('/operation/demandas/'.$id),'message'=>$this->message->success('Tarefa criada e vinculada.')->render()]);return;
            }
            if ($slug==='demands' && $id > 0 && $action === 'transition') {
                $status=in_array($request['demand_status']??'',['new','analysis','in_progress','waiting_third_party','waiting_condominium','completed','cancelled'],true)?$request['demand_status']:'';
                if(!$status){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Selecione um status válido.')->render()]);return;}
                $assigned=(int)($request['demand_assigned_to']??0)?:null;$priority=in_array($request['demand_priority']??'',['low','normal','high','urgent'],true)?$request['demand_priority']:'normal';
                $pdo->prepare("UPDATE operation_demands SET status=:status,assigned_to=:assigned,priority=:priority,completed_at=:completed WHERE id=:id")->execute(['status'=>$status,'assigned'=>$assigned,'priority'=>$priority,'completed'=>$status==='completed'?date('Y-m-d H:i:s'):null,'id'=>$id]);
                $this->logActivity($pdo,'demands',$id,'transition','Demanda movida para '.str_replace('_',' ',$status),(int)$user->id);Audit::record('transition','operation_demands',$id,[],['status'=>$status,'assigned_to'=>$assigned,'priority'=>$priority]);echo json_encode(['redirect'=>url('/operation/demandas/'.$id),'message'=>$this->message->success('Fluxo da demanda atualizado.')->render()]);return;
            }
            if ($slug==='quotes' && $action==='add_offer' && $id>0) {
                $supplier=(int)($request['supplier_id']??0);$amount=str_replace(',','.',preg_replace('/[^0-9,.]/','',(string)($request['amount']??'')));
                if(!$supplier){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Selecione um fornecedor.')->render()]);return;}
                $stmt=$pdo->prepare("INSERT INTO operation_quote_offers(quote_id,supplier_id,amount,received_at,valid_until,notes,status) VALUES(:quote,:supplier,:amount,:received,:valid,:notes,:status) ON DUPLICATE KEY UPDATE amount=VALUES(amount),received_at=VALUES(received_at),valid_until=VALUES(valid_until),notes=VALUES(notes),status=VALUES(status)");
                $stmt->execute(['quote'=>$id,'supplier'=>$supplier,'amount'=>$amount!==''?$amount:null,'received'=>!empty($request['received_at'])?str_replace('T',' ',$request['received_at']).':00':null,'valid'=>$request['offer_valid_until']?:null,'notes'=>trim((string)($request['offer_notes']??'')),'status'=>$amount!==''?'received':'requested']);
                $this->logActivity($pdo,'quotes',$id,'offer_updated','Cotação de fornecedor atualizada',(int)$user->id);Audit::record('update','operation_quotes',$id,[],['supplier_id'=>$supplier,'offer'=>'updated']);echo json_encode(['redirect'=>url('/operation/quotes/'.$id)]);return;
            }
            if ($action === 'delete' && $id > 0) {
                try {
                    $pdo->beginTransaction();
                    $this->logActivity($pdo, $slug, $id, 'deleted', $config['singular'] . ' excluído', (int)$user->id);
                    $stmt = $pdo->prepare("DELETE FROM {$config['table']} WHERE id=:id");
                    $stmt->execute(['id' => $id]);
                    $pdo->commit();
                    echo json_encode(['redirect' => url('/operation/' . $slug)]);
                } catch (\Throwable $exception) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    http_response_code(409);
                    echo json_encode(['message' => $this->message->warning('Este registro possui vínculos e não pode ser excluído. Inative-o ou remova os vínculos primeiro.')->render()]);
                }
                return;
            }
            $values = [];
            foreach ($config['fields'] as $name => $field) {
                $value = trim((string)($request[$name] ?? ($field['default'] ?? '')));
                if (!empty($field['required']) && $value === '') {
                    http_response_code(422);
                    echo json_encode(['message' => $this->message->warning('Preencha o campo ' . $field['label'] . '.')->render()]);
                    return;
                }
                if (($field['type'] ?? '') === 'datetime-local' && $value !== '') $value = str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
                if (empty($field['virtual'])) $values[$name] = $value === '' ? null : $value;
            }
            $pdo->beginTransaction();
            try {
                if ($id > 0) {
                    $set = implode(',', array_map(fn($name) => "{$name}=:{$name}", array_keys($values)));
                    $values['id'] = $id;
                    $pdo->prepare("UPDATE {$config['table']} SET {$set} WHERE id=:id")->execute($values);
                    $actionName = 'updated';
                } else {
                    if (!empty($config['auto_protocol'])) $values['protocol'] = $config['auto_protocol'] . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
                    if (array_key_exists('created_by', $this->tableColumns($pdo, $config['table']))) $values['created_by'] = (int)$user->id;
                    $columns = implode(',', array_keys($values));
                    $params = implode(',', array_map(fn($name) => ":{$name}", array_keys($values)));
                    $pdo->prepare("INSERT INTO {$config['table']} ({$columns}) VALUES ({$params})")->execute($values);
                    $id = (int)$pdo->lastInsertId();
                    $actionName = 'created';
                }
                if ($slug === 'visits' && $actionName === 'created') {
                    $template = $pdo->prepare("INSERT INTO operation_visit_items(visit_id,checklist_item_id,title,area,category,priority,photo_required_on_failure,comment_required_on_failure,result) SELECT :visit,i.id,i.title,i.area,i.category,i.priority,i.photo_required_on_failure,i.comment_required_on_failure,'pending' FROM operation_checklist_items i JOIN operation_checklists c ON c.id=i.checklist_id WHERE c.active=1 AND (c.condominium_id IS NULL OR c.condominium_id=:condo) AND (c.visit_type IS NULL OR c.visit_type='' OR c.visit_type=:visit_type) ORDER BY i.position,i.id");
                    $template->execute(['visit'=>$id,'condo'=>(int)$values['condominium_id'],'visit_type'=>(string)$values['visit_type']]);
                    $pdo->prepare("INSERT INTO operation_visit_events(visit_id,event_type,summary,details_json,user_id) VALUES(:visit,'scheduled','Visita agendada',:details,:user)")->execute(['visit'=>$id,'details'=>json_encode(['scheduled_at'=>$values['scheduled_at']],JSON_UNESCAPED_UNICODE),'user'=>(int)$user->id]);
                    $this->materializeVisitRecurrences($pdo,$id,$values,(int)$user->id);
                }
                $this->logActivity($pdo, $slug, $id, $actionName, $config['singular'] . ' salvo', (int)$user->id);
                if ($slug === 'people' && !empty($request['link_condominium_id'])) {
                    $pdo->prepare("DELETE FROM operation_person_links WHERE person_id=:person AND condominium_id=:condo")->execute(['person'=>$id,'condo'=>(int)$request['link_condominium_id']]);
                    $link=$pdo->prepare("INSERT INTO operation_person_links(person_id,condominium_id,unit_label,block_label,relation_type,status) VALUES(:person,:condo,:unit,:block,:relation,'active')");
                    $link->execute(['person'=>$id,'condo'=>(int)$request['link_condominium_id'],'unit'=>mb_substr(trim((string)($request['unit_label']??'')),0,80)?:null,'block'=>mb_substr(trim((string)($request['block_label']??'')),0,80)?:null,'relation'=>in_array($request['relation_type']??'',['resident','owner','tenant','syndic','subsyndic','councillor'],true)?$request['relation_type']:'resident']);
                }
                $pdo->commit();
                Audit::record($actionName === 'created' ? 'create' : 'update', $config['table'], $id, [], $values);
                echo json_encode(['redirect' => url('/operation/' . $slug), 'message' => $this->message->success('Registro salvo com sucesso.')->render()]);
            } catch (\Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                http_response_code(422);
                echo json_encode(['message' => $this->message->error('Não foi possível salvar. Verifique os vínculos informados.')->render()]);
            }
            return;
        }
        $record = null;
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM {$config['table']} WHERE id=:id");
            $stmt->execute(['id' => $id]);
            $record = $stmt->fetch() ?: null;
            if($record&&$slug==='people'){$link=$pdo->prepare("SELECT condominium_id link_condominium_id,relation_type,unit_label,block_label FROM operation_person_links WHERE person_id=:id AND status='active' ORDER BY id DESC LIMIT 1");$link->execute(['id'=>$id]);$personLink=$link->fetch();if($personLink)foreach((array)$personLink as $key=>$value)$record->{$key}=$value;}
        }
        $page=max(1,(int)($_GET['page']??1));$perPage=min(100,max(10,(int)($_GET['per_page']??25)));$offset=($page-1)*$perPage;$filters=['q'=>mb_substr(trim((string)($_GET['q']??'')),0,100),'status'=>mb_substr(trim((string)($_GET['status']??'')),0,40),'priority'=>mb_substr(trim((string)($_GET['priority']??'')),0,40),'condominium_id'=>(int)($_GET['condominium_id']??0),'assigned_to'=>(int)($_GET['assigned_to']??0)];$where=[];$params=[];$columns=$this->tableColumns($pdo,$config['table']);
        if($filters['q']!==''){$searchColumns=array_values(array_intersect(['name','title','trade_name','legal_name','protocol','description','category'],array_keys($columns)));if($searchColumns){$where[]='('.implode(' OR ',array_map(static fn($column)=>"r.{$column} LIKE :q",$searchColumns)).')';$params['q']='%'.$filters['q'].'%';}}
        foreach(['status','priority','condominium_id','assigned_to'] as $filter){if($filters[$filter]!==''&&$filters[$filter]!==0&&isset($columns[$filter])){$where[]="r.{$filter}=:{$filter}";$params[$filter]=$filters[$filter];}}
        $whereSql=$where?' WHERE '.implode(' AND ',$where):'';$countStmt=$pdo->prepare("SELECT COUNT(*) FROM {$config['table']} r{$whereSql}");$countStmt->execute($params);$totalItems=(int)$countStmt->fetchColumn();$totalPages=max(1,(int)ceil($totalItems/$perPage));if($page>$totalPages){$page=$totalPages;$offset=($page-1)*$perPage;}
        if($slug==='people'){$stmt=$pdo->prepare("SELECT r.*,c.name condominium_name,l.relation_type,l.unit_label,l.block_label FROM operation_people r LEFT JOIN operation_person_links l ON l.person_id=r.id AND l.status='active' LEFT JOIN operation_condominiums c ON c.id=l.condominium_id{$whereSql} ORDER BY r.id DESC LIMIT {$perPage} OFFSET {$offset}");}
        else{$join=$slug==='condominiums'?'r.id':(isset($config['fields']['condominium_id'])?'r.condominium_id':'NULL');$stmt=$pdo->prepare("SELECT r.*,c.name condominium_name FROM {$config['table']} r LEFT JOIN operation_condominiums c ON c.id={$join}{$whereSql} ORDER BY r.id DESC LIMIT {$perPage} OFFSET {$offset}");}$stmt->execute($params);$items=$stmt->fetchAll()?:[];
        $condominiums = $pdo->query("SELECT id,name FROM operation_condominiums WHERE status<>'inactive' ORDER BY name")->fetchAll() ?: [];
        $issues = $pdo->query("SELECT id,title FROM operation_issues WHERE status NOT IN ('resolved','cancelled') ORDER BY id DESC")->fetchAll() ?: [];
        $demands = $pdo->query("SELECT id,protocol,title FROM operation_demands WHERE status NOT IN ('completed','cancelled') ORDER BY id DESC")->fetchAll() ?: [];
        $users = $pdo->query("SELECT id,CONCAT(first_name,' ',last_name) name FROM users WHERE status<>'trash' ORDER BY first_name,last_name")->fetchAll() ?: [];
        $suppliers = $pdo->query("SELECT id,COALESCE(NULLIF(trade_name,''),legal_name) name FROM operation_suppliers WHERE status='active' ORDER BY name")->fetchAll() ?: [];
        $offers=[];if($slug==='quotes'&&$id){$offerStmt=$pdo->prepare("SELECT o.*,COALESCE(NULLIF(s.trade_name,''),s.legal_name) supplier_name FROM operation_quote_offers o JOIN operation_suppliers s ON s.id=o.supplier_id WHERE o.quote_id=:id ORDER BY o.amount IS NULL,o.amount");$offerStmt->execute(['id'=>$id]);$offers=$offerStmt->fetchAll()?:[];}
        $comments=[];$attachments=[];$activity=[];$tasks=[];if($record){$query=$pdo->prepare("SELECT c.*,CONCAT(u.first_name,' ',u.last_name) user_name FROM operation_comments c LEFT JOIN users u ON u.id=c.created_by WHERE c.entity_type=:type AND c.entity_id=:id ORDER BY c.created_at DESC");$query->execute(['type'=>$slug,'id'=>$record->id]);$comments=$query->fetchAll()?:[];$query=$pdo->prepare('SELECT * FROM operation_attachments WHERE entity_type=:type AND entity_id=:id ORDER BY created_at DESC');$query->execute(['type'=>$slug,'id'=>$record->id]);$attachments=$query->fetchAll()?:[];$query=$pdo->prepare("SELECT a.*,CONCAT(u.first_name,' ',u.last_name) user_name FROM operation_activity a LEFT JOIN users u ON u.id=a.user_id WHERE a.entity_type=:type AND a.entity_id=:id ORDER BY a.created_at DESC LIMIT 50");$query->execute(['type'=>$slug,'id'=>$record->id]);$activity=$query->fetchAll()?:[];if($slug==='demands'){$query=$pdo->prepare('SELECT * FROM operation_tasks WHERE demand_id=:id ORDER BY due_at IS NULL,due_at');$query->execute(['id'=>$record->id]);$tasks=$query->fetchAll()?:[];}}
        echo $this->view->render('components/operation/resource', [
            'head' => $this->seo->render($config['title'] . ' - Connect Operações', CONF_SITE_DESC, url('/operation/' . $slug), themeStudio('/assets/images/favicon.png', 'default')),
            'title' => $config['title'], 'app' => $slug, 'user' => $user, 'currentVersion' => VERSION_STUDIO, 'adminBase' => '/operation',
            'resource' => $slug, 'config' => $config, 'items' => $items, 'record' => $record, 'condominiums' => $condominiums, 'issues' => $issues, 'demands' => $demands, 'users' => $users, 'suppliers'=>$suppliers, 'offers'=>$offers, 'comments'=>$comments, 'attachments'=>$attachments, 'activity'=>$activity, 'tasks'=>$tasks,'filters'=>$filters,'page'=>$page,'perPage'=>$perPage,'totalItems'=>$totalItems,'totalPages'=>$totalPages
        ]);
    }

    private function tableColumns(\PDO $pdo, string $table): array
    {
        $columns = [];
        foreach ($pdo->query("SHOW COLUMNS FROM {$table}")->fetchAll() as $column) $columns[$column->Field] = true;
        return $columns;
    }

    private function logActivity(\PDO $pdo, string $type, int $id, string $action, string $summary, int $userId): void
    {
        $stmt = $pdo->prepare('INSERT INTO operation_activity (entity_type,entity_id,action,summary,user_id) VALUES (:type,:id,:action,:summary,:user)');
        $stmt->execute(['type' => $type, 'id' => $id, 'action' => $action, 'summary' => $summary, 'user' => $userId]);
    }
}
