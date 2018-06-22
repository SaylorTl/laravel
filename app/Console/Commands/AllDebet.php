<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis;
use App\libraries\OpenapiClient as OpenapiClient;
use App\Jobs\GetDebetInfo;
use App\Jobs\DoBid;


class AllDebet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:alldebet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    var $cache;
    var $client;
    var $finish = true;
    var $PageIndex =1;
    public function __construct()
    {
        parent::__construct();
        $this->client = new OpenapiClient();
        $this->cache  = new Predis\Client();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        do{
            pp_log("查询第". $this->PageIndex."页\n",0);
            $this->getLoanList();
            $this->PageIndex ++;
            sleep(10);
            //     $this->dispatchNow((new GetLoanList())->onQueue('loanlist'));
            //     sleep(10);//等待时间，进行下一次操作。
        }while($this->finish);
    }

    /*新版投标列表接口（默认每页200条）*/
    public function getLoanList(){
        //定时清理缓存
        $nowRecodeTime = time();
        $lastRecodeTime = $this->cache->get("lastRecodeTime") ;
        if($nowRecodeTime - $lastRecodeTime >900){
            $this->cache->set("lastRecodeTime",$nowRecodeTime);
        }
        $url = "https://openapi.ppdai.com/invest/LLoanInfoService/DebtListNew";
        $date = date("Y-m-d H:i:s",time()-3600);
        $request = '{"PageIndex":"'.$this->PageIndex.'","Levels":"AA,A,B,C"}';
        $result = json_decode($this->client->send($url, $request,30),true);

        if(!$result){
            pp_log("查询失败：".$result['ResultMessage']);
            $this->finish = false;
            return;
        }
        if(empty($result['Result'])){
            pp_log("查询失败：".$result['ResultMessage']);
            $this->finish = false;
            return;
        }
        if($result['Result'] !== 1){
            pp_log("查询失败：".$result['ResultMessage']);
            $this->finish = false;
            return;
        }

        if(empty($result['DebtInfos'])){
            pp_log('查询结果为空','123');
            $this->finish = false;
            return;
        }
        $aviLoan = array();
        foreach($result['DebtInfos'] as $key=>$value){
            if($value['PriceforSale']>300 || $value['PriceforSaleRate']<12|| $value['OwingNumber']>8 || !in_array($value['CreditCode'],array("AA","A","B","C","D"))){
                continue;
            }
            if($value['CreditCode'] == 'AA' && $value['PriceforSaleRate']>=11){
                pp_log(" ".$value['CreditCode']."快捷投标开始投标",$value['ListingId']);
                $this->dispatch((new DoBid($value))->onQueue('dobid'));
                continue;
            }

            //非陪标再筛选一次
            if($value['PriceforSale']>50 || $value['PriceforSaleRate']<20){
                continue;
            }
            if($this->cache->get("DebId".$value['DebtdealId'])){
                continue;
            }
            $aviLoan[$value['DebtdealId']]=$value['ListingId'];
        }
        if(!$aviLoan){
            pp_log("筛选出符合条件标的为空",00);
            return;
        }
        $temp = array();
        foreach($aviLoan as $k=>$v){
            $temp[$k]=$v;
            if( count($temp)== 9 || (count($aviLoan)<=9 && count($temp)==count($aviLoan))){
                $this->dispatch((new GetDebetInfo($temp))->onQueue('debetInfo'));
                $temp = array();
            }
        }
    }


}
