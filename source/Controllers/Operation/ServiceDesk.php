<?php

namespace Source\Controllers\Operation;

use Source\Core\Connect;
use Source\Core\Controller;
use Source\Models\Auth;
use Source\Models\User;
use Source\Services\ServiceDesk\TicketService;
use Source\Support\Access;

/** Controlador HTTP exclusivo da fila de chamados do ambiente operacional. */
final class ServiceDesk extends Controller
{
    public function __construct()
    {
        parent::__construct(moves_container_path('operation', 'default') . '/');
    }

    public function tickets(?array $data): void
    {
        $user = $this->user(); $pdo = Connect::getInstance(); $service = new TicketService($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=UTF-8');
            if (!csrf_verify($data ?? [])) { http_response_code(419); $this->json('Sessão expirada. Atualize a página.', 'error'); return; }
            try {
                $action=(string)($data['action']??'create');$ticketId=(int)($data['ticket_id']??0);
                if($action==='template_save'){$service->template((string)($data['title']??''),(string)($data['body']??''),(int)$user->id);echo json_encode(['reload'=>true]);return;}
                if($action==='template_delete'){if(!$service->deleteTemplate((int)($data['template_id']??0)))throw new \InvalidArgumentException('Resposta rápida não encontrada.');echo json_encode(['reload'=>true]);return;}
                if($action==='bulk'){$affected=$service->bulk((array)($data['ticket_ids']??[]),(string)($data['status']??''),(int)$user->id);echo json_encode(['reload'=>true,'affected'=>$affected]);return;}
                if($action==='update'){if(!$ticketId||!$service->update($ticketId,$data,(int)$user->id))throw new \InvalidArgumentException('Chamado não encontrado.');echo json_encode(['redirect'=>url('/operation/tickets?ticket='.$ticketId)]);return;}
                if($action==='reply'){$service->reply($ticketId,(string)($data['message']??''),!empty($data['is_internal']),(int)$user->id,(int)($data['time_spent']??0),!empty($data['resolve_after']));echo json_encode(['redirect'=>url('/operation/tickets?ticket='.$ticketId)]);return;}
                $requesterId=(int)($data['requester_id']??0)?:(int)$user->id;if(!(new User())->findById($requesterId))throw new \InvalidArgumentException('Selecione um solicitante válido.');$data['requester_id']=$requesterId;$ticket=$service->create($data,(int)$user->id);$this->message->success("Chamado {$ticket->protocol} cadastrado.")->flash();echo json_encode(['redirect'=>url('/operation/tickets')]);return;
            } catch (\InvalidArgumentException $exception) { http_response_code(422); $this->json($exception->getMessage(),'warning'); return; }
        }
        $filters=$_GET;$tickets=$service->queue($filters);$counts=array_fill_keys(['open','in_progress','waiting_customer','resolved','closed'],0);foreach($pdo->query('SELECT status,COUNT(*) total FROM studio_support_tickets GROUP BY status')->fetchAll()?:[] as $row)$counts[$row->status]=(int)$row->total;
        $selectedTicket=null;$messages=[];$events=[];$attachments=[];
        if($ticketId=(int)($_GET['ticket']??0)){$stmt=$pdo->prepare("SELECT t.*,c.name condominium_name,d.protocol demand_protocol,COALESCE(NULLIF(TRIM(CONCAT(r.first_name,' ',r.last_name)),''),t.requester_name,'Solicitante') requester_name,CONCAT(a.first_name,' ',a.last_name) assigned_name FROM studio_support_tickets t LEFT JOIN users r ON r.id=t.requester_id LEFT JOIN users a ON a.id=t.assigned_to LEFT JOIN operation_condominiums c ON c.id=t.condominium_id LEFT JOIN operation_demands d ON d.id=t.demand_id WHERE t.id=:id");$stmt->execute(['id'=>$ticketId]);$selectedTicket=$stmt->fetch()?:null;if($selectedTicket){$stmt=$pdo->prepare("SELECT m.*,CONCAT(u.first_name,' ',u.last_name) user_name FROM studio_support_ticket_messages m JOIN users u ON u.id=m.user_id WHERE m.ticket_id=:id ORDER BY m.created_at");$stmt->execute(['id'=>$ticketId]);$messages=$stmt->fetchAll()?:[];$stmt=$pdo->prepare("SELECT e.*,CONCAT(u.first_name,' ',u.last_name) user_name FROM studio_support_ticket_events e LEFT JOIN users u ON u.id=e.user_id WHERE e.ticket_id=:id ORDER BY e.created_at");$stmt->execute(['id'=>$ticketId]);$events=$stmt->fetchAll()?:[];$stmt=$pdo->prepare('SELECT * FROM studio_support_ticket_attachments WHERE ticket_id=:id ORDER BY created_at');$stmt->execute(['id'=>$ticketId]);$attachments=$stmt->fetchAll()?:[];}}
        echo $this->view->render('components/tickets/home',['head'=>$this->seo->render('Chamados - Connect Operações',CONF_SITE_DESC,url('/operation/chamados'),moves_container_url('operation','default','/assets/images/favicon.png')),'title'=>'Chamados','app'=>'tickets','user'=>$user,'currentVersion'=>VERSION_STUDIO,'adminBase'=>'/operation','tickets'=>$tickets,'users'=>(new User())->find('status != :status','status=trash')->order('first_name,last_name')->fetch(true)?:[],'condominiums'=>$pdo->query("SELECT id,name FROM operation_condominiums WHERE status<>'inactive' ORDER BY name")->fetchAll()?:[],'demands'=>$pdo->query("SELECT id,protocol,title FROM operation_demands WHERE status NOT IN ('completed','cancelled') ORDER BY id DESC")->fetchAll()?:[],'status'=>(string)($filters['status']??''),'search'=>trim((string)($filters['q']??'')),'priorityFilter'=>(string)($filters['priority']??''),'assignedFilter'=>(string)($filters['assigned_to']??''),'dueFilter'=>(string)($filters['due']??''),'counts'=>$counts,'selectedTicket'=>$selectedTicket,'messages'=>$messages,'events'=>$events,'attachments'=>$attachments,'templates'=>$pdo->query('SELECT * FROM studio_support_templates WHERE active=1 ORDER BY title')->fetchAll()?:[]]);
    }

    private function user(){ $user=Auth::user();if(!$user||!Access::can('operation.access',$user))redirect('/operation/login');if(!Access::can('operation.tickets.manage',$user))redirect('/operation/ops/403');return $user; }
    private function json(string $message,string $type):void { echo json_encode(['message'=>$this->message->{$type}($message)->render()]); }
}
