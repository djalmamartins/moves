<?php
namespace Source\Controllers\Erp\Connect;

use IntlDateFormatter;
use Source\Models\Corporation\AppCondominium;
use Source\Models\Erp\AppInvoice;
use Source\Controllers\App\V1\App;
use Source\Core\Connect;
use Source\Services\Erp\FinancialService;

/**
 * ERP | Class Finance
 *
 * @author Djalma Martins
 * @package Source\App\Erp\Connect
 */
class Finance extends Erp
{
    /**
     * Finance constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function dash(?array $data): void
    {
        redirect("/erp/finance/home");
    }

    public function home(?array $data): void
    {

        $head = $this->seo->render(
            CONF_SITE_NAME . " | Financeiro",
            CONF_SITE_DESC,
            url("/erp"),
            url("/erp/assets/images/image.jpg"),
            false
        );

        //CHART
        $dateChart = [];
        for ($month = -11; $month <= 0; $month++){
            $dateChart[] = date("m/y", strtotime("{$month}month"));
        }

        $chartData = new \stdClass();
        $chartData->categories = "'" . implode("','", $dateChart) . "'";
        $chartData->expense = "0,0,0,0,0,0,0,0,0,0,0,0";
        $chartData->income = "0,0,0,0,0,0,0,0,0,0,0,0";
        $chartData->owing = "0,0,0,0,0,0,0,0,0,0,0,0";

        $chart = (new AppInvoice())
            ->find("condominium_id = :condo AND due_at >= DATE(now() - INTERVAL 11 MONTH) GROUP BY year(due_at) ASC, month(due_at) ASC", "condo={$this->condo->id}",
            "

            year(due_at) AS due_year,
            month(due_at) AS due_month,
            DATE_FORMAT(due_at, '%m/%Y') AS due_date,
            (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'paid' AND type = 'income' AND  year(due_at) = due_year AND month(due_at) = due_month) AS income,
            (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'paid' AND type = 'expense' AND  year(due_at) = due_year AND month(due_at) = due_month) AS expense,
            (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'unpaid' AND type = 'income' AND year(due_at) = due_year AND month(due_at) = due_month) AS owing,
            (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'unpaid' AND type = 'income' AND  year(due_at) = due_year AND month(due_at) = due_month) AS receive

            "
            )
            ->limit(12)
            ->fetch( true);

        if($chart){
            $chartCategories = [];
            $chartExpense = [];
            $chartIncome = [];
            $chartOwing = [];
            $chartReceive = [];

            foreach ($chart as $chartItem) {
                $chartCategories[] = $chartItem->due_date;
                $chartExpense[] = $chartItem->expense;
                $chartIncome[] = $chartItem->income;
                $chartOwing[] = $chartItem->owing;
                $chartReceive[] = $chartItem->receive;
            }

            $chartData->categories = "'" . implode("','", $chartCategories) . "'";
            $chartData->expense = implode(",", array_map("abs", $chartExpense));
            $chartData->income = implode(",", array_map("abs", $chartIncome));
            $chartData->owing = implode(",", array_map("abs", $chartOwing));
            $chartData->receive = implode(",", array_map("abs", $chartReceive));
        }

//        var_dump($chartCategories, $chartExpense,$chartIncome, $chartOwing);
        //END CHART

        //INCOME && EXPENSE

        $income = (new AppInvoice())
            ->find("condominium_id = :condo AND status = 'unpaid' AND type = 'income' AND date(due_at) <= date(now() + INTERVAL 1 MONTH)", "condo={$this->condo->id}")
            ->order("due_at")
            ->fetch(true);

        $expense = (new AppInvoice())
            ->find("condominium_id = :condo AND status = 'unpaid' AND type = 'expense' AND date(due_at) <= date(now() + INTERVAL 1 MONTH)", "condo={$this->condo->id}")
            ->order("due_at")
            ->fetch(true);

        //END INCOME && EXPENSE

        //COUNT

        $count = (new AppInvoice())
            ->find("condominium_id = :condo AND YEAR(due_at) = YEAR(NOW()) AND MONTH(due_at) = MONTH(NOW())", "condo={$this->condo->id}",
                "
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'paid' AND type = 'income' AND YEAR(due_at) = YEAR(NOW()) AND MONTH(due_at) = MONTH(NOW())) AS cIncome,
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'paid' AND type = 'expense' AND YEAR(due_at) = YEAR(NOW()) AND MONTH(due_at) = MONTH(NOW())) AS cExpense,
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND type = 'expense' AND YEAR(due_at) = YEAR(NOW()) AND MONTH(due_at) = MONTH(NOW())) AS ctExpense,
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'unpaid' AND type = 'income' AND YEAR(due_at) = YEAR(NOW()) AND MONTH(due_at) = MONTH(NOW())) AS cReceive
                "
            )->fetch();

        //END COUNT

        $wallet = (new AppInvoice())->find("condominium_id = :condo", "condo={$this->condo->id}",
        "
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'paid' AND type = 'income') AS income,
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'paid' AND type = 'expense') AS expense,
                (SELECT SUM(value) FROM app_invoices WHERE condominium_id = :condo AND status = 'unpaid' AND type = 'income' AND due_at <= CURDATE()) AS owing
        ")->fetch();

        if($wallet){
            $wallet->wallet = $wallet->income - $wallet->expense;
        }

        echo $this->view->render("components/finance/home", [
            "app" => "finance/home",
            "head" => $head,
            "chart" => $chartData,
            "income" => $income,
            "expense" => $expense,
            "wallet" => $wallet,

            "count" => (object)[
                "income" => $count->cIncome,
                "ctExpense" => $count->ctExpense,
                "cfExpense" => $count->ctExpense - $count->cExpense,
                "cReceive" => $count->cReceive,
                "cash" => $count->cIncome - $count->cExpense,
            ],

            "condo" => (object)[
                "select" => $this->condo,
                "list" => (new AppCondominium())->find()->fetch(true),
            ],
        ]);
    }

    public function income(?array $data): void
    {
        $_GET['type']='receivable';$this->entries($data);
    }

    public function expenses(?array $data): void { $_GET['type']='payable';$this->entries($data); }

    public function entries(?array $data): void
    {
        $service=new FinancialService(Connect::getInstance());$condominiumId=(int)($this->condo->id??0);
        if($_SERVER['REQUEST_METHOD']==='POST'){
            header('Content-Type: application/json; charset=UTF-8');if(!csrf_verify($data??[])){http_response_code(419);echo json_encode(['message'=>$this->message->error('Sessão expirada. Atualize a página.')->render()]);return;}if(!$condominiumId){http_response_code(422);echo json_encode(['message'=>$this->message->warning('Selecione um condomínio.')->render()]);return;}
            try{$action=(string)($data['action']??'create');$entryId=(int)($data['entry_id']??0);if($action==='cancel'){if(!$service->cancel($condominiumId,$entryId))throw new \InvalidArgumentException('Lançamento não pode ser cancelado.');}elseif($action==='pay'){$service->pay($condominiumId,$entryId,(string)($data['payment_amount']??''),$data,(int)$this->user->id);}elseif($action==='update'){if(!$service->updateEntry($condominiumId,$entryId,$data))throw new \InvalidArgumentException('Lançamento não pode ser editado.');}else{$data['condominium_id']=$condominiumId;$service->createEntry($data,(int)$this->user->id);}echo json_encode(['redirect'=>url('/erp/finance/entries')]);}catch(\InvalidArgumentException $exception){http_response_code(422);echo json_encode(['message'=>$this->message->warning($exception->getMessage())->render()]);}return;
        }
        $filters=$_GET;$items=$condominiumId?$service->entries($condominiumId,$filters):[];$totals=$condominiumId?$service->totals($condominiumId):['receivable'=>'0.00','payable'=>'0.00','received'=>'0.00','paid'=>'0.00'];$edit=null;if($condominiumId&&!empty($_GET['edit']))foreach($items as $item)if((int)$item->id===(int)$_GET['edit']){$edit=$item;break;}
        $head=$this->seo->render(CONF_SITE_NAME.' | Lançamentos financeiros',CONF_SITE_DESC,url('/erp/finance/entries'),url('/erp/assets/images/image.jpg'),false);
        echo $this->view->render('components/finance/entries',['app'=>'finance/entries','head'=>$head,'items'=>$items,'totals'=>$totals,'filters'=>$filters,'edit'=>$edit,'condo'=>(object)['select'=>$this->condo,'list'=>(new AppCondominium())->find()->fetch(true)]]);
    }



//    public function home(?array $data): void
//    {
//        $head = $this->seo->render(
//            CONF_SITE_NAME . " | Usuários",
//            CONF_SITE_DESC,
//            url("/erp"),
//            url("/erp/assets/images/image.jpg"),
//            false
//        );
//
//        echo $this->view->render("components/users/home", [
//            "app" => "users/home",
//            "head" => $head,
//            "search" => $search,
//            "users" => $users->order("id DESC")->limit($pager->limit())->offset($pager->offset())->fetch(true),
//            "paginator" => $pager->render(),
//
//            "user" => $this->user,
//
//        ]);
//    }
}
