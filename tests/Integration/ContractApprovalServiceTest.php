<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use Source\Services\Erp\ContractApprovalService;

final class ContractApprovalServiceTest extends TestCase
{
    public function testSequentialApprovalActivatesContractAndKeepsAuditTrail(): void
    {
        $service=new ContractApprovalService($this->pdo);$contract=$service->createContract(2,['title'=>'Manutenção de elevadores','starts_at'=>'2026-09-01','ends_at'=>'2027-08-31','monthly_amount'=>'1250,00'],2);$document=$service->attachDocument(2,$contract,['title'=>'Contrato assinado','category'=>'contract','file_path'=>'private/contracts/2.pdf'],2);$service->submit(2,$contract,[2,3]);self::assertSame('pending_approval',$service->contract(2,$contract)->status);try{$service->decide(2,$contract,3,'approved');self::fail('Não deve ignorar a ordem de aprovação.');}catch(\InvalidArgumentException){}$service->decide(2,$contract,2,'approved','Conferido');self::assertSame('pending_approval',$service->contract(2,$contract)->status);$service->decide(2,$contract,3,'approved');self::assertSame('active',$service->contract(2,$contract)->status);self::assertSame(2,(int)$this->pdo->query("SELECT COUNT(*) FROM erp_approval_steps WHERE entity_id={$contract} AND status='approved'")->fetchColumn());self::assertSame($contract,(int)$this->pdo->query("SELECT entity_id FROM erp_documents WHERE id={$document} AND condominium_id=2")->fetchColumn());
    }

    public function testRejectionAndTenantIsolationAreEnforced(): void
    {
        $service=new ContractApprovalService($this->pdo);$contract=$service->createContract(2,['title'=>'Serviço de portaria','starts_at'=>'2026-09-01'],2);$service->submit(2,$contract,[2]);try{$service->contract(3,$contract);self::fail('Contrato não pode atravessar condomínio.');}catch(\InvalidArgumentException){}try{$service->attachDocument(3,$contract,['title'=>'Documento externo'],2);self::fail('Documento não pode atravessar condomínio.');}catch(\InvalidArgumentException){}$service->decide(2,$contract,2,'rejected','Valor divergente');self::assertSame('rejected',$service->contract(2,$contract)->status);$step=$this->pdo->query("SELECT * FROM erp_approval_steps WHERE entity_id={$contract}")->fetch();self::assertSame('rejected',$step->status);self::assertSame('Valor divergente',$step->note);self::assertNotEmpty($step->decided_at);
    }
}
