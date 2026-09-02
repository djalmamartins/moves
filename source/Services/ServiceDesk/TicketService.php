<?php

namespace Source\Services\ServiceDesk;

use PDO;
use Source\Support\Audit;

final class TicketService
{
    public function __construct(private readonly PDO $pdo) {}

    public function create(array $data, int $userId): object
    {
        $subject=mb_substr(trim(strip_tags((string)($data['subject']??''))),0,180);$message=trim(strip_tags((string)($data['message']??'')));
        if(mb_strlen($subject)<4||mb_strlen($message)<10)throw new \InvalidArgumentException('Informe assunto e uma descrição com pelo menos 10 caracteres.');
        $priority=in_array($data['priority']??'',['low','medium','high','urgent'],true)?$data['priority']:'medium';$protocol='CH'.date('ymd').strtoupper(substr(bin2hex(random_bytes(2)),0,4));$hours=['urgent'=>24,'high'=>48,'medium'=>72,'low'=>120][$priority];$dueAt=date('Y-m-d H:i:s',strtotime("+{$hours} hours"));
        $values=['condo'=>(int)($data['condominium_id']??0)?:null,'demand'=>(int)($data['demand_id']??0)?:null,'protocol'=>$protocol,'subject'=>$subject,'message'=>$message,'area'=>in_array($data['area']??'',['general','technical','financial'],true)?$data['area']:'general','priority'=>$priority,'requester'=>(int)($data['requester_id']??0)?:$userId,'assigned'=>(int)($data['assigned_to']??0)?:null,'creator'=>$userId,'due'=>$dueAt];
        $stmt=$this->pdo->prepare('INSERT INTO studio_support_tickets(condominium_id,demand_id,protocol,subject,message,area,priority,requester_id,assigned_to,created_by,due_at) VALUES(:condo,:demand,:protocol,:subject,:message,:area,:priority,:requester,:assigned,:creator,:due)');$stmt->execute($values);$id=(int)$this->pdo->lastInsertId();Audit::record('create','studio_support_tickets',$id,[],['protocol'=>$protocol,'subject'=>$subject,'priority'=>$priority]);return(object)['id'=>$id,'protocol'=>$protocol,'subject'=>$subject,'priority'=>$priority,'due_at'=>$dueAt,'assigned_to'=>$values['assigned'],'requester_id'=>$values['requester']];
    }

    public function update(int $id,array $data,int $userId): bool
    {
        $stmt=$this->pdo->prepare('SELECT status,priority,assigned_to,team,category,tags FROM studio_support_tickets WHERE id=:id');$stmt->execute(['id'=>$id]);$before=$stmt->fetch();if(!$before)return false;
        $status=in_array($data['status']??'',['open','in_progress','waiting_customer','resolved','closed'],true)?$data['status']:$before->status;$priority=in_array($data['priority']??'',['low','medium','high','urgent'],true)?$data['priority']:$before->priority;$assigned=(int)($data['assigned_to']??$before->assigned_to)?:null;$team=mb_substr(trim(strip_tags((string)($data['team']??$before->team))),0,100);$category=mb_substr(trim(strip_tags((string)($data['category']??$before->category))),0,100);$tags=mb_substr(trim(strip_tags((string)($data['tags']??$before->tags))),0,500);
        $this->pdo->prepare('UPDATE studio_support_tickets SET condominium_id=:condo,demand_id=:demand,status=:status,priority=:priority,assigned_to=:assigned,team=:team,category=:category,tags=:tags,resolved_at=:resolved WHERE id=:id')->execute(['condo'=>(int)($data['condominium_id']??0)?:null,'demand'=>(int)($data['demand_id']??0)?:null,'status'=>$status,'priority'=>$priority,'assigned'=>$assigned,'team'=>$team,'category'=>$category,'tags'=>$tags,'resolved'=>in_array($status,['resolved','closed'],true)?date('Y-m-d H:i:s'):null,'id'=>$id]);
        foreach(['status'=>$status,'priority'=>$priority,'assigned_to'=>$assigned,'team'=>$team,'category'=>$category,'tags'=>$tags] as $field=>$new)if((string)$before->$field!==(string)$new)$this->event($id,$userId,$field,(string)$before->$field,(string)$new);Audit::record('update','studio_support_tickets',$id,[],['status'=>$status,'priority'=>$priority]);return true;
    }

    public function bulk(array $ids,string $status,int $userId): int
    {
        $ids=array_values(array_unique(array_filter(array_map('intval',$ids))));if(!$ids||!in_array($status,['open','in_progress','waiting_customer','resolved','closed'],true))throw new \InvalidArgumentException('Selecione chamados e um status válido.');$marks=implode(',',array_fill(0,count($ids),'?'));$stmt=$this->pdo->prepare("UPDATE studio_support_tickets SET status=?,resolved_at=".(in_array($status,['resolved','closed'],true)?'NOW()':'NULL')." WHERE id IN ({$marks})");$stmt->execute(array_merge([$status],$ids));foreach($ids as $id)$this->event($id,$userId,'status',null,$status);return $stmt->rowCount();
    }

    public function reply(int $id,string $message,bool $internal,int $userId,int $seconds=0,bool $resolve=false): int
    {
        $message=trim(strip_tags($message));if(mb_strlen($message)<2)throw new \InvalidArgumentException('Escreva uma resposta antes de enviar.');$exists=$this->pdo->prepare('SELECT id FROM studio_support_tickets WHERE id=:id');$exists->execute(['id'=>$id]);if(!$exists->fetchColumn())throw new \InvalidArgumentException('Chamado não encontrado.');$stmt=$this->pdo->prepare('INSERT INTO studio_support_ticket_messages(ticket_id,user_id,message,is_internal) VALUES(?,?,?,?)');$stmt->execute([$id,$userId,$message,$internal?1:0]);$messageId=(int)$this->pdo->lastInsertId();$this->pdo->prepare("UPDATE studio_support_tickets SET status=".($resolve?"'resolved'":"IF(status='open','in_progress',status)").",first_response_at=COALESCE(first_response_at,NOW()),work_seconds=work_seconds+:seconds,resolved_at=".($resolve?'NOW()':'resolved_at').' WHERE id=:id')->execute(['seconds'=>max(0,min(86400,$seconds)),'id'=>$id]);return$messageId;
    }

    public function template(string $title,string $body,int $userId): int
    { $title=mb_substr(trim(strip_tags($title)),0,120);$body=trim(strip_tags($body));if(mb_strlen($title)<2||mb_strlen($body)<2)throw new \InvalidArgumentException('Informe título e conteúdo da resposta rápida.');$stmt=$this->pdo->prepare('INSERT INTO studio_support_templates(title,body,created_by) VALUES(?,?,?)');$stmt->execute([$title,$body,$userId]);return(int)$this->pdo->lastInsertId(); }
    public function deleteTemplate(int $id): bool { $stmt=$this->pdo->prepare('DELETE FROM studio_support_templates WHERE id=?');$stmt->execute([$id]);return(bool)$stmt->rowCount(); }

    public function queue(array $filters): array
    {
        $terms=[];$params=[];$status=(string)($filters['status']??'');$priority=(string)($filters['priority']??'');$q=trim(strip_tags((string)($filters['q']??'')));if(in_array($status,['open','in_progress','waiting_customer','resolved','closed'],true)){$terms[]='t.status=:status';$params['status']=$status;}if(in_array($priority,['low','medium','high','urgent'],true)){$terms[]='t.priority=:priority';$params['priority']=$priority;}if($q!==''){$terms[]='(t.subject LIKE :q OR t.protocol LIKE :q OR t.requester_name LIKE :q OR t.requester_email LIKE :q)';$params['q']="%{$q}%";}$sql='SELECT t.* FROM studio_support_tickets t'.($terms?' WHERE '.implode(' AND ',$terms):'').' ORDER BY t.created_at DESC LIMIT 100';$stmt=$this->pdo->prepare($sql);$stmt->execute($params);return$stmt->fetchAll()?:[];
    }

    private function event(int $ticketId,int $userId,string $type,?string $old,?string $new): void { $this->pdo->prepare('INSERT INTO studio_support_ticket_events(ticket_id,user_id,event_type,old_value,new_value) VALUES(?,?,?,?,?)')->execute([$ticketId,$userId,$type,$old,$new]); }
}
