<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\libraries\OpenapiClient as OpenapiClient;
use Predis;
use App\libraries\ppValidate;
use App\service\middleware\ConcreteBuilder;
use App\service\requestmeta\Director;
use App\service\requestmeta\Invoker;


class GetLoanInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $aviList;
    public $client;
    public $cache;
    public $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($aviLoan,$type)
    {
        $this->client = new OpenapiClient();
        $this->aviList = $aviLoan;
        $this->cache  = new Predis\Client();
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
<<<<<<< HEAD
        switch ($this->type) {
            case 'PPD_LOAN':
                $this->loanDetail($this->aviList,$this->type);
                break;
            case 'PPD_DEBET':
                $this->debetDetail($this->aviList,$this->type);
                break;
=======

        $bidList =  $this->getLoanInfo($this->aviList);
        if(1 == $bidList['Result'] ){
            foreach($bidList['LoanInfos'] as $bk=>$bv){
                $this->cache->setex("ppid".$bv['ListingId'],3600,1);
                $amount = $this->getBidAmount($bv);
                if($amount >0){
                    $this->client->doBid($bv);
//                    (new DoBid($bv))->dispatch($bv)->onQueue("dobid");
                }
            }
        }
    }

    /*获取投标详情*/
    public function getLoanInfo($aviLoan){
        /*新版散标详情批量接口（请求列表不大于10）*/
        $url = "https://openapi.ppdai.com/listing/openapiNoAuth/batchListingInfo";
        $aviLoanStr = implode(",",$aviLoan);
        $request = '{"ListingIds": ['.$aviLoanStr.']}';
        $result = json_decode($this->client->send($url, $request,3),true);
        if($result['Result']!==1){
            pp_log("获取信息详情失败".$result['ResultMessage']);
            return array('Result'=>0);
>>>>>>> 4f828adc173d4bce4837a435fbf317681389ee02
        }
    }



    //标的详情建造者
    public function debetDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        $temp = array();
        foreach ($aviLoan as $k => $v) {
            $temp[] = $v;
            if (($k % 9 == 0 && $k >= 0) || (count($aviLoan) < 9 && $k == count($aviLoan) - 1)) {
                $this->builder = new ConcreteBuilder();
                $this->director = new Director($this->builder);
                $this->director->ProductDetail($temp, $type);
                $product = $this->builder->getResult();
                if(!$product){
                    continue;
                }
                foreach($product as $key=>$value){
                    $this->cache->setex("ppid".$value['ListingId'],3600,1);
                    $rules = array('pastduenumber_too_big','currentcreditcode_too_low','pastdueday_too_much','allowanceradio_too_low');
                    $ok = ppValidate::validate($rules,$value);
                    if(true !==$ok){
                        continue;
                    }
                    $this->debetLoanDetail($value,'PPD_LOAN');
                }
                return;
            }
        }
    }

    //标的详情建造者
    public function debetLoanDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        $this->builder = new ConcreteBuilder();
        $this->director = new Director($this->builder);
        $this->director->ProductDetail(array($aviLoan->ListingId), $type);
        $product = $this->builder->getResult();
        foreach($product as $key=>$value){
            $value->DebtId = $aviLoan->DebtId;
            $invoker = new Invoker($value);
            $invoker->debetAction();
        }
    }

    //标的详情建造者
    public function loanDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        foreach ($aviLoan as $k => $v) {
            $temp[] = $v;
            if (($k % 9 == 0 && $k >= 0) || (count($aviLoan) < 9 && $k == count($aviLoan) - 1)) {
                continue;
            }
            $this->builder = new ConcreteBuilder();
            $this->director = new Director($this->builder);
            $this->director->ProductDetail($temp, $type);
            $product = $this->builder->getResult();
            if(!$product){
                continue;
            }
            foreach($product as $key=>$value){
                $this->cache->setex("ppid".$value['ListingId'],3600,1);
                $invoker = new Invoker($value);
                $invoker->action();
            }
        }
    }
}
