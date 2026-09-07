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

    public function testEntryCrudIsFilteredAndScopedByCondominium(): void
    {
        $this->seedScope();$service=new FinancialService($this->pdo);$entry=$service->createEntry(['condominium_id'=>2,'type'=>'payable','description'=>'Água setembro','document_number'=>'AG-09','competency'=>'2026-09-01','due_at'=>'2026-09-12','amount'=>'45.00'],2);self::assertCount(1,$service->entries(2,['type'=>'payable','q'=>'AG-09']));self::assertCount(0,$service->entries(3));self::assertTrue($service->updateEntry(2,$entry,['type'=>'payable','description'=>'Água setembro ajustada','document_number'=>'AG-09','competency'=>'2026-09-01','due_at'=>'2026-09-13','amount'=>'47.50']));self::assertSame('47.50',$this->entry($entry)->amount);self::assertTrue($service->cancel(2,$entry));self::assertSame('cancelled',$this->entry($entry)->status);
    }

    public function testMonthlyTotalsUseCanonicalEntriesAndPayments(): void
    {
        $this->seedScope();$service=new FinancialService($this->pdo);$entry=$service->createEntry(['condominium_id'=>2,'type'=>'receivable','description'=>'Receita mensal','competency'=>date('Y-m-01'),'due_at'=>date('Y-m-10'),'amount'=>'100.00'],2);$service->pay(2,$entry,'40.00',[],2);$month=array_values(array_filter($service->monthlyTotals(2),fn($row)=>$row->month===date('m/y')));self::assertNotEmpty($month);self::assertSame('40.00',$month[0]->income);self::assertSame('60.00',$month[0]->receivable);
    }

    public function testBankTransactionsAreScopedAndRequireCompatibleEntryType(): void
    {
        $this->seedScope();$service=new FinancialService($this->pdo);$payable=$service->createEntry(['condominium_id'=>2,'type'=>'payable','description'=>'Despesa bancária','competency'=>'2026-09-01','due_at'=>'2026-09-10','amount'=>'30.00'],2);$receivable=$service->createEntry(['condominium_id'=>2,'type'=>'receivable','description'=>'Receita bancária','competency'=>'2026-09-01','due_at'=>'2026-09-10','amount'=>'30.00'],2);$this->pdo->exec("INSERT INTO erp_bank_transactions(id,condominium_id,wallet_id,external_id,occurred_at,description,amount) VALUES(3,2,2,'TX-3','2026-09-05 12:00:00','Débito',-30.00),(4,2,2,'TX-4','2026-09-05 13:00:00','Crédito',30.00)");self::assertCount(2,$service->bankTransactions(2));self::assertCount(0,$service->bankTransactions(3));try{$service->reconcile(2,3,$receivable,2);self::fail('Natureza incompatível deveria falhar.');}catch(\InvalidArgumentException){}self::assertSame('pending',$this->pdo->query('SELECT reconciliation_status FROM erp_bank_transactions WHERE id=3')->fetchColumn());self::assertTrue($service->ignoreTransaction(2,4));self::assertFalse($service->ignoreTransaction(3,3));$service->reconcile(2,3,$payable,2);self::assertSame('matched',$this->pdo->query('SELECT reconciliation_status FROM erp_bank_transactions WHERE id=3')->fetchColumn());
    }

    private function seedScope():void { $this->pdo->exec("INSERT INTO app_condominium(id,condominium_name,status) VALUES(2,'Condomínio Teste','active') ON DUPLICATE KEY UPDATE condominium_name=VALUES(condominium_name)");$this->pdo->exec("INSERT INTO app_wallets(id,condominium_id,wallet,balance,status) VALUES(2,2,'Conta teste',0,'active') ON DUPLICATE KEY UPDATE wallet=VALUES(wallet)"); }
    private function entry(int $id):object { return$this->pdo->query("SELECT * FROM erp_financial_entries WHERE id={$id}")->fetch(); }
}
