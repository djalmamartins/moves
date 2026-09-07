<?php
namespace Source\Controllers\Erp\Connect;

use IntlDateFormatter;
use Source\Models\Corporation\AppCondominium;
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
        $this->canonicalHome();
        return;

    }

    private function canonicalHome(): void
    {
        $condominiumId = (int)($this->condo->id ?? 0);
        $service = new FinancialService(Connect::getInstance());
        $items = $condominiumId ? $service->entries($condominiumId) : [];
        echo $this->view->render('components/finance/home', [
            'app' => 'finance/home',
            'head' => $this->seo->render(CONF_SITE_NAME . ' | Financeiro', CONF_SITE_DESC, url('/erp/finance/home'), url('/erp/assets/images/image.jpg'), false),
            'totals' => $condominiumId ? $service->totals($condominiumId) : ['receivable'=>'0.00','payable'=>'0.00','received'=>'0.00','paid'=>'0.00'],
            'monthly' => $condominiumId ? $service->monthlyTotals($condominiumId) : [],
            'income' => array_values(array_filter($items, fn($item) => $item->type === 'receivable' && !in_array($item->status, ['paid','cancelled'], true))),
            'expense' => array_values(array_filter($items, fn($item) => $item->type === 'payable' && !in_array($item->status, ['paid','cancelled'], true))),
            'condo' => (object)['select' => $this->condo, 'list' => (new AppCondominium())->find()->fetch(true)],
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
