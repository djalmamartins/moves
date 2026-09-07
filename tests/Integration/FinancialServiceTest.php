<?php

declare(strict_types=1);

namespace MovesOSTests\Integration;

use MovesOSTests\TestCase;
use Source\Services\Erp\FinancialService;

final class FinancialServiceTest extends TestCase
{
    public function testPartialAndFullPaymentsReconcileTotals(): void
    {
        $this->seedScope();$service=new FinancialService($this->pdo);$entry=$service->createEntry(['condominium_id'=>2,'wallet_id'=>2,'type'=>'receivable','description'=>'Taxa condominial setembro','competency'=>'2026-09-01','due_at'=>'2026-09-10','amount'=>'150.00'],2);
        $service->pay(2,$entry,'50.00',['wallet_id'=>2,'paid_at'=>'2026-09-05 10:00:00'],2);self::assertSame('partial',$this->entry($entry)->status);self::assertSame('50.00',$this->entry($entry)->paid_amount);
        $service->pay(2,$entry,'100.00',['wallet_id'=>2,'paid_at'=>'2026-09-06 10:00:00'],2);self::assertSame('paid',$this->entry($entry)->status);self::assertSame('150.00',$this->entry($entry)->paid_amount);self::assertSame('150.00',$service->totals(2)['received']);
    }

    public function testReconciliationIsAtomicAndScoped(): void
    {
        $this->seedScope();$service=new FinancialService($this->pdo);$entry=$service->createEntry(['condominium_id'=>2,'wallet_id'=>2,'type'=>'payable','description'=>'Manutenção elevador','competency'=>'2026-09-01','due_at'=>'2026-09-15','amount'=>'80.00'],2);$this->pdo->exec("INSERT INTO erp_bank_transactions(id,condominium_id,wallet_id,external_id,occurred_at,description,amount) VALUES(2,2,2,'TX-2','2026-09-05 12:00:00','Pagamento elevador',-80.00)");$service->reconcile(2,2,$entry,2);self::assertSame('paid',$this->entry($entry)->status);self::assertSame('matched',$this->pdo->query('SELECT reconciliation_status FROM erp_bank_transactions WHERE id=2')->fetchColumn());$this->expectException(\InvalidArgumentException::class);$service->reconcile(2,2,$entry,2);
    }

    public function testOverpaymentAndCancellingPaidEntryAreRejected(): void
    {
        $this->seedScope();$service=new FinancialService($this->pdo);$entry=$service->createEntry(['condominium_id'=>2,'type'=>'payable','description'=>'Conta de energia','competency'=>'2026-09-01','due_at'=>'2026-09-20','amount'=>'20.00'],2);try{$service->pay(2,$entry,'20.01',[],2);self::fail('Pagamento excedente deveria falhar.');}catch(\InvalidArgumentException){}self::assertSame('0.00',$this->entry($entry)->paid_amount);$service->pay(2,$entry,'20.00',[],2);self::assertFalse($service->cancel(2,$entry));
    }

    private function seedScope():void { $this->pdo->exec("INSERT INTO app_condominium(id,condominium_name,status) VALUES(2,'Condomínio Teste','active') ON DUPLICATE KEY UPDATE condominium_name=VALUES(condominium_name)");$this->pdo->exec("INSERT INTO app_wallets(id,condominium_id,wallet,balance,status) VALUES(2,2,'Conta teste',0,'active') ON DUPLICATE KEY UPDATE wallet=VALUES(wallet)"); }
    private function entry(int $id):object { return$this->pdo->query("SELECT * FROM erp_financial_entries WHERE id={$id}")->fetch(); }
}
