<?php

namespace Source\Services\Erp;

use PDO;

final class ContractApprovalService
{
    public function __construct(private readonly PDO $pdo) {}

    public function createContract(int $condominiumId, array $data, int $userId): int
    {
        $title=mb_substr(trim(strip_tags((string)($data['title']??''))),0,180);$start=$this->date((string)($data['starts_at']??''));$end=!empty($data['ends_at'])?$this->date((string)$data['ends_at']):null;if($condominiumId<1||mb_strlen($title)<3||($end&&$end<$start))throw new \InvalidArgumentException('Informe título e vigência válidos.');$stmt=$this->pdo->prepare("INSERT INTO erp_contracts(condominium_id,supplier_id,title,number,starts_at,ends_at,monthly_amount,notice_days,status,owner_id,created_by) VALUES(:condo,:supplier,:title,:number,:start,:end,:amount,:notice,'draft',:owner,:user)");$stmt->execute(['condo'=>$condominiumId,'supplier'=>(int)($data['supplier_id']??0)?:null,'title'=>$title,'number'=>mb_substr(trim((string)($data['number']??'')),0,80)?:null,'start'=>$start,'end'=>$end,'amount'=>$this->amount($data['monthly_amount']??null),'notice'=>max(0,min(365,(int)($data['notice_days']??30))),'owner'=>(int)($data['owner_id']??0)?:null,'user'=>$userId]);return(int)$this->pdo->lastInsertId();
    }

    public function attachDocument(int $condominiumId, int $contractId, array $data, int $userId): int
    {
        $this->contract($condominiumId,$contractId);$title=mb_substr(trim(strip_tags((string)($data['title']??''))),0,180);if(mb_strlen($title)<3)throw new \InvalidArgumentException('Informe um título de documento válido.');$stmt=$this->pdo->prepare("INSERT INTO erp_documents(condominium_id,entity_type,entity_id,title,category,file_path,expires_at,visibility,status,created_by) VALUES(:condo,'contract',:entity,:title,:category,:path,:expires,:visibility,'active',:user)");$visibility=in_array($data['visibility']??'', ['internal','managers','residents','public'],true)?$data['visibility']:'internal';$stmt->execute(['condo'=>$condominiumId,'entity'=>$contractId,'title'=>$title,'category'=>mb_substr(trim((string)($data['category']??'')),0,80)?:null,'path'=>mb_substr(trim((string)($data['file_path']??'')),0,255)?:null,'expires'=>!empty($data['expires_at'])?$this->date((string)$data['expires_at']):null,'visibility'=>$visibility,'user'=>$userId]);return(int)$this->pdo->lastInsertId();
    }

    public function submit(int $condominiumId, int $contractId, array $approverIds): void
    {
        $approverIds=array_values(array_filter(array_map('intval',$approverIds),fn($id)=>$id>0));if(!$approverIds)throw new \InvalidArgumentException('Informe ao menos um aprovador.');$this->pdo->beginTransaction();try{$contract=$this->contract($condominiumId,$contractId,true);if($contract->status!=='draft')throw new \InvalidArgumentException('Somente contratos em rascunho podem ser enviados.');$stmt=$this->pdo->prepare("INSERT INTO erp_approval_steps(entity_type,entity_id,condominium_id,sequence_no,approver_id,status) VALUES('contract',:entity,:condo,:sequence,:approver,'pending')");foreach($approverIds as $index=>$approver)$stmt->execute(['entity'=>$contractId,'condo'=>$condominiumId,'sequence'=>$index+1,'approver'=>$approver]);$this->pdo->prepare("UPDATE erp_contracts SET status='pending_approval' WHERE id=:id AND condominium_id=:condo")->execute(['id'=>$contractId,'condo'=>$condominiumId]);$this->pdo->commit();}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw$e;}
    }

    public function decide(int $condominiumId, int $contractId, int $approverId, string $decision, string $note=''): void
    {
        if(!in_array($decision,['approved','rejected'],true))throw new \InvalidArgumentException('Decisão inválida.');$this->pdo->beginTransaction();try{$contract=$this->contract($condominiumId,$contractId,true);if($contract->status!=='pending_approval')throw new \InvalidArgumentException('Contrato não está aguardando aprovação.');$stmt=$this->pdo->prepare("SELECT * FROM erp_approval_steps WHERE entity_type='contract' AND entity_id=:entity AND condominium_id=:condo AND status='pending' ORDER BY sequence_no LIMIT 1 FOR UPDATE");$stmt->execute(['entity'=>$contractId,'condo'=>$condominiumId]);$step=$stmt->fetch();if(!$step||(int)$step->approver_id!==$approverId)throw new \InvalidArgumentException('A etapa atual pertence a outro aprovador.');$this->pdo->prepare('UPDATE erp_approval_steps SET status=:status,note=:note,decided_at=NOW() WHERE id=:id')->execute(['status'=>$decision,'note'=>mb_substr(trim(strip_tags($note)),0,2000)?:null,'id'=>$step->id]);if($decision==='rejected'){$status='rejected';}else{$pending=$this->pdo->prepare("SELECT COUNT(*) FROM erp_approval_steps WHERE entity_type='contract' AND entity_id=:entity AND condominium_id=:condo AND status='pending'");$pending->execute(['entity'=>$contractId,'condo'=>$condominiumId]);$status=((int)$pending->fetchColumn()===0)?'active':'pending_approval';}$this->pdo->prepare('UPDATE erp_contracts SET status=:status WHERE id=:id AND condominium_id=:condo')->execute(['status'=>$status,'id'=>$contractId,'condo'=>$condominiumId]);$this->pdo->commit();}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw$e;}
    }

    public function contract(int $condominiumId, int $contractId, bool $lock=false): object
    {
        $stmt=$this->pdo->prepare('SELECT * FROM erp_contracts WHERE id=:id AND condominium_id=:condo'.($lock?' FOR UPDATE':''));$stmt->execute(['id'=>$contractId,'condo'=>$condominiumId]);$contract=$stmt->fetch();if(!$contract)throw new \InvalidArgumentException('Contrato não encontrado.');return$contract;
    }

    private function date(string $date): string { $parsed=\DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!$parsed||$parsed->format('Y-m-d')!==$date)throw new \InvalidArgumentException('Data inválida.');return$date; }
    private function amount(mixed $value): ?string { if($value===null||$value==='')return null;$value=str_replace(',','.',trim((string)$value));if(!preg_match('/^\d+(?:\.\d{1,2})?$/',$value))throw new \InvalidArgumentException('Valor inválido.');return number_format((float)$value,2,'.',''); }
}
