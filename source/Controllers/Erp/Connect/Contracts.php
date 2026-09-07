<?php

namespace Source\Controllers\Erp\Connect;

use Source\Core\Connect;
use Source\Models\Corporation\AppCondominium;
use Source\Services\Erp\ContractApprovalService;

final class Contracts extends Erp
{
    public function home(?array $data): void
    {
        $service=new ContractApprovalService(Connect::getInstance());$condominiumId=(int)($this->condo->id??0);
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->json();if(!csrf_verify($data??[])){$this->fail('Sessão expirada.',419);return;}try{$service->createContract($condominiumId,$data??[],(int)$this->user->id);echo json_encode(['redirect'=>url('/erp/contracts')]);}catch(\InvalidArgumentException $e){$this->fail($e->getMessage());}return;}
        echo $this->view->render('components/contracts/home',['app'=>'contracts','head'=>$this->head('Contratos'),'contracts'=>$condominiumId?$service->contracts($condominiumId):[],'condo'=>$this->condoData()]);
    }

    public function detail(?array $data): void
    {
        $service=new ContractApprovalService(Connect::getInstance());$condominiumId=(int)($this->condo->id??0);$id=(int)($data['id']??0);
        if($_SERVER['REQUEST_METHOD']==='POST'){$this->json();if(!csrf_verify($data??[])){$this->fail('Sessão expirada.',419);return;}try{$action=(string)($data['action']??'');if($action==='document')$service->attachDocument($condominiumId,$id,$data??[],(int)$this->user->id);elseif($action==='submit')$service->submit($condominiumId,$id,array_filter([(int)($data['approver_1']??0),(int)($data['approver_2']??0)]));elseif($action==='approve'||$action==='reject')$service->decide($condominiumId,$id,(int)$this->user->id,$action==='approve'?'approved':'rejected',(string)($data['note']??''));else throw new \InvalidArgumentException('Ação inválida.');echo json_encode(['redirect'=>url('/erp/contracts/'.$id)]);}catch(\InvalidArgumentException $e){$this->fail($e->getMessage());}return;}
        try{$contract=$service->contract($condominiumId,$id);}catch(\InvalidArgumentException){redirect('/erp/contracts');return;}
        echo $this->view->render('components/contracts/detail',['app'=>'contracts','head'=>$this->head($contract->title),'contract'=>$contract,'documents'=>$service->documents($condominiumId,$id),'steps'=>$service->approvalSteps($condominiumId,$id),'approvers'=>$service->approvers(),'condo'=>$this->condoData()]);
    }

    private function json(): void { header('Content-Type: application/json; charset=UTF-8'); }
    private function fail(string $message,int $status=422): void { http_response_code($status);echo json_encode(['message'=>$this->message->warning($message)->render()]); }
    private function head(string $title): string { return$this->seo->render(CONF_SITE_NAME.' | '.$title,CONF_SITE_DESC,url('/erp/contracts'),url('/erp/assets/images/image.jpg'),false); }
    private function condoData(): object { return(object)['select'=>$this->condo,'list'=>(new AppCondominium())->find()->fetch(true)]; }
}
