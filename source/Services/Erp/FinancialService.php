<?php

namespace Source\Services\Erp;

use PDO;

final class FinancialService
{
    public function __construct(private readonly PDO $pdo) {}

    public function createEntry(array $data,int $userId): int
    {
        $condominium=(int)($data['condominium_id']??0);$description=mb_substr(trim(strip_tags((string)($data['description']??''))),0,220);$type=(string)($data['type']??'');$amount=$this->money((string)($data['amount']??''));$competency=$this->date((string)($data['competency']??''));$due=$this->date((string)($data['due_at']??''));
        if($condominium<1||mb_strlen($description)<3||!in_array($type,['receivable','payable'],true)||$amount<=0)throw new \InvalidArgumentException('Informe condomínio, tipo, descrição, competência, vencimento e valor válidos.');
        $stmt=$this->pdo->prepare("INSERT INTO erp_financial_entries(condominium_id,unit_id,supplier_id,wallet_id,category_id,type,description,document_number,competency,due_at,amount,status,created_by) VALUES(:condo,:unit,:supplier,:wallet,:category,:type,:description,:document,:competency,:due,:amount,'open',:user)");
        $stmt->execute(['condo'=>$condominium,'unit'=>(int)($data['unit_id']??0)?:null,'supplier'=>(int)($data['supplier_id']??0)?:null,'wallet'=>(int)($data['wallet_id']??0)?:null,'category'=>(int)($data['category_id']??0)?:null,'type'=>$type,'description'=>$description,'document'=>mb_substr(trim((string)($data['document_number']??'')),0,80)?:null,'competency'=>$competency,'due'=>$due,'amount'=>$this->decimal($amount),'user'=>$userId]);
        return(int)$this->pdo->lastInsertId();
    }

    public function pay(int $condominiumId,int $entryId,string $amount,array $data,int $userId): int
    {
        $this->pdo->beginTransaction();try{$id=$this->applyPayment($condominiumId,$entryId,$this->money($amount),$data,$userId);$this->pdo->commit();return$id;}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw$e;}
    }

    public function updateEntry(int $condominiumId,int $entryId,array $data): bool
    {
        $description=mb_substr(trim(strip_tags((string)($data['description']??''))),0,220);$type=(string)($data['type']??'');$amount=$this->money((string)($data['amount']??''));$competency=$this->date((string)($data['competency']??''));$due=$this->date((string)($data['due_at']??''));if(mb_strlen($description)<3||!in_array($type,['receivable','payable'],true)||$amount<=0)throw new \InvalidArgumentException('Informe tipo, descrição, competência, vencimento e valor válidos.');
        $stmt=$this->pdo->prepare("UPDATE erp_financial_entries SET unit_id=:unit,supplier_id=:supplier,wallet_id=:wallet,category_id=:category,type=:type,description=:description,document_number=:document,competency=:competency,due_at=:due,amount=:amount WHERE id=:id AND condominium_id=:condo AND paid_amount=0 AND status NOT IN ('paid','cancelled')");$stmt->execute(['unit'=>(int)($data['unit_id']??0)?:null,'supplier'=>(int)($data['supplier_id']??0)?:null,'wallet'=>(int)($data['wallet_id']??0)?:null,'category'=>(int)($data['category_id']??0)?:null,'type'=>$type,'description'=>$description,'document'=>mb_substr(trim((string)($data['document_number']??'')),0,80)?:null,'competency'=>$competency,'due'=>$due,'amount'=>$this->decimal($amount),'id'=>$entryId,'condo'=>$condominiumId]);return(bool)$stmt->rowCount();
    }

    public function entries(int $condominiumId,array $filters=[]): array
    {
        $terms=['condominium_id=:condo'];$params=['condo'=>$condominiumId];$type=(string)($filters['type']??'');$status=(string)($filters['status']??'');$search=trim(strip_tags((string)($filters['q']??'')));if(in_array($type,['receivable','payable'],true)){$terms[]='type=:type';$params['type']=$type;}if(in_array($status,['draft','pending_approval','approved','open','partial','paid','overdue','cancelled'],true)){$terms[]='status=:status';$params['status']=$status;}if($search!==''){$terms[]='(description LIKE :q OR document_number LIKE :q)';$params['q']="%{$search}%";}$stmt=$this->pdo->prepare('SELECT * FROM erp_financial_entries WHERE '.implode(' AND ',$terms).' ORDER BY due_at,id DESC LIMIT 200');$stmt->execute($params);return$stmt->fetchAll()?:[];
    }

    public function cancel(int $condominiumId,int $entryId): bool
    {
        $stmt=$this->pdo->prepare("UPDATE erp_financial_entries SET status='cancelled' WHERE id=:id AND condominium_id=:condo AND paid_amount=0 AND status NOT IN ('paid','cancelled')");$stmt->execute(['id'=>$entryId,'condo'=>$condominiumId]);return(bool)$stmt->rowCount();
    }

    public function reconcile(int $condominiumId,int $transactionId,int $entryId,int $userId): int
    {
        $this->pdo->beginTransaction();try{$stmt=$this->pdo->prepare("SELECT * FROM erp_bank_transactions WHERE id=:id AND condominium_id=:condo AND reconciliation_status='pending' FOR UPDATE");$stmt->execute(['id'=>$transactionId,'condo'=>$condominiumId]);$transaction=$stmt->fetch();if(!$transaction)throw new \InvalidArgumentException('Transação bancária indisponível para conciliação.');$entry=$this->entryForUpdate($condominiumId,$entryId);$expectedType=((float)$transaction->amount)<0?'payable':'receivable';if($entry->type!==$expectedType)throw new \InvalidArgumentException('A natureza da transação não corresponde ao lançamento.');$payment=$this->applyPayment($condominiumId,$entryId,$this->money(ltrim((string)$transaction->amount,'-')),['wallet_id'=>$transaction->wallet_id,'paid_at'=>$transaction->occurred_at,'method'=>'bank_reconciliation','reference'=>$transaction->external_id],$userId);$this->pdo->prepare("UPDATE erp_bank_transactions SET reconciliation_status='matched',matched_entry_id=:entry WHERE id=:id AND condominium_id=:condo")->execute(['entry'=>$entryId,'id'=>$transactionId,'condo'=>$condominiumId]);$this->pdo->commit();return$payment;}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw$e;}
    }

    public function bankTransactions(int $condominiumId, string $status = 'pending'): array
    {
        $status = in_array($status, ['pending','matched','ignored'], true) ? $status : 'pending';
        $stmt = $this->pdo->prepare('SELECT * FROM erp_bank_transactions WHERE condominium_id=:condo AND reconciliation_status=:status ORDER BY occurred_at DESC,id DESC LIMIT 200');
        $stmt->execute(['condo' => $condominiumId, 'status' => $status]);
        return $stmt->fetchAll() ?: [];
    }

    public function ignoreTransaction(int $condominiumId, int $transactionId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE erp_bank_transactions SET reconciliation_status='ignored' WHERE id=:id AND condominium_id=:condo AND reconciliation_status='pending'");
        $stmt->execute(['id' => $transactionId, 'condo' => $condominiumId]);
        return (bool)$stmt->rowCount();
    }

    public function totals(int $condominiumId): array
    {
        $stmt=$this->pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type='receivable' AND status<>'cancelled' THEN amount ELSE 0 END),0) receivable,COALESCE(SUM(CASE WHEN type='payable' AND status<>'cancelled' THEN amount ELSE 0 END),0) payable,COALESCE(SUM(CASE WHEN type='receivable' AND status<>'cancelled' THEN paid_amount ELSE 0 END),0) received,COALESCE(SUM(CASE WHEN type='payable' AND status<>'cancelled' THEN paid_amount ELSE 0 END),0) paid FROM erp_financial_entries WHERE condominium_id=:condo");$stmt->execute(['condo'=>$condominiumId]);return(array)$stmt->fetch();
    }

    public function monthlyTotals(int $condominiumId, int $months = 12): array
    {
        $months = max(1, min(24, $months));
        $start = (new \DateTimeImmutable('first day of this month'))
            ->modify('-' . ($months - 1) . ' months')->format('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(due_at, '%m/%y') month,
            COALESCE(SUM(CASE WHEN type='receivable' AND status<>'cancelled' THEN paid_amount ELSE 0 END),0) income,
            COALESCE(SUM(CASE WHEN type='receivable' AND status NOT IN ('paid','cancelled') THEN amount-paid_amount ELSE 0 END),0) receivable,
            COALESCE(SUM(CASE WHEN type='payable' AND status<>'cancelled' THEN paid_amount ELSE 0 END),0) expense
            FROM erp_financial_entries WHERE condominium_id=:condo AND due_at>=:start
            GROUP BY YEAR(due_at),MONTH(due_at) ORDER BY YEAR(due_at),MONTH(due_at)");
        $stmt->execute(['condo' => $condominiumId, 'start' => $start]);
        return $stmt->fetchAll() ?: [];
    }

    private function applyPayment(int $condominiumId,int $entryId,int $amount,array $data,int $userId): int
    {
        if($amount<=0)throw new \InvalidArgumentException('O pagamento deve ser maior que zero.');$stmt=$this->pdo->prepare("SELECT * FROM erp_financial_entries WHERE id=:id AND condominium_id=:condo AND status NOT IN ('cancelled','paid') FOR UPDATE");$stmt->execute(['id'=>$entryId,'condo'=>$condominiumId]);$entry=$stmt->fetch();if(!$entry)throw new \InvalidArgumentException('Lançamento não encontrado ou já encerrado.');$remaining=$this->money((string)$entry->amount)-$this->money((string)$entry->paid_amount);if($amount>$remaining)throw new \InvalidArgumentException('Pagamento maior que o saldo do lançamento.');$stmt=$this->pdo->prepare('INSERT INTO erp_payments(entry_id,wallet_id,amount,paid_at,method,reference,created_by) VALUES(:entry,:wallet,:amount,:paid,:method,:reference,:user)');$stmt->execute(['entry'=>$entryId,'wallet'=>(int)($data['wallet_id']??0)?:null,'amount'=>$this->decimal($amount),'paid'=>$data['paid_at']??date('Y-m-d H:i:s'),'method'=>mb_substr((string)($data['method']??'transfer'),0,30),'reference'=>mb_substr((string)($data['reference']??''),0,120)?:null,'user'=>$userId]);$paymentId=(int)$this->pdo->lastInsertId();$newPaid=$this->money((string)$entry->paid_amount)+$amount;$status=$newPaid===$this->money((string)$entry->amount)?'paid':'partial';$this->pdo->prepare('UPDATE erp_financial_entries SET paid_amount=:paid,status=:status WHERE id=:id')->execute(['paid'=>$this->decimal($newPaid),'status'=>$status,'id'=>$entryId]);return$paymentId;
    }

    private function entryForUpdate(int $condominiumId, int $entryId): object
    {
        $stmt=$this->pdo->prepare("SELECT * FROM erp_financial_entries WHERE id=:id AND condominium_id=:condo AND status NOT IN ('cancelled','paid') FOR UPDATE");
        $stmt->execute(['id'=>$entryId,'condo'=>$condominiumId]);
        $entry=$stmt->fetch();
        if(!$entry)throw new \InvalidArgumentException('Lançamento não encontrado ou já encerrado.');
        return $entry;
    }

    private function date(string $date): string { $parsed=\DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!$parsed||$parsed->format('Y-m-d')!==$date)throw new \InvalidArgumentException('Data financeira inválida.');return$date; }
    private function money(string $value): int { $value=str_replace(',','.',trim($value));if(!preg_match('/^\d+(?:\.\d{1,2})?$/',$value))throw new \InvalidArgumentException('Valor financeiro inválido.');[$whole,$fraction]=array_pad(explode('.',$value,2),2,'');return((int)$whole*100)+(int)str_pad($fraction,2,'0'); }
    private function decimal(int $cents): string { return sprintf('%d.%02d',intdiv($cents,100),$cents%100); }
}
