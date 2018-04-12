<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis;
use App\libraries\OpenapiClient as OpenapiClient;
use App\Jobs\GetLoanInfo;
use App\Jobs\GetLoanList;
use App\Jobs\DoBid;


class Ppdai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
            $this->PageIndex ++;
            $this->getLoanList();
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
        if($nowRecodeTime - $lastRecodeTime >3600){
            $this->cache->set("lastRecodeTime",$nowRecodeTime);
        }
        $url = "https://openapi.ppdai.com/invest/LLoanInfoService/LoanList";
        $date = date("Y-m-d H:i:s",time()-3600);
        $request = '{"PageIndex":"'.$this->PageIndex.'","StartDateTime": "'.$date.'"}';
        $result = json_decode($this->client->send($url, $request,config('app.accessToken'),30),true);
//        if($this->PageIndex >= 6){
//            $this->finish = false;
//            return;
//        }
        if(!$result){
            pp_log("查询失败：".$result['ResultMessage']);
            $this->finish = false;
            return;
        }
        if($result['Result'] !== 1){
            pp_log("查询失败：".$result['ResultMessage']);
            $this->finish = false;
            return;
        }
        $aviLoan = array();
        if(empty($result['LoanInfos'])){
            pp_log('查询结果为空','123');
            $this->finish = false;
            return;
        }
        foreach($result['LoanInfos'] as $key=>$value){
            if($value['Rate']<12|| $value['Months']>12){
                continue;
            }
            if($this->cache->get("ppid".$value['ListingId'])){
//                pp_log("标号已标记，不再重复查询",$value['ListingId']);
                continue;
            }
            if($value['CreditCode'] == 'AA'){
                pp_log(" ".$value['CreditCode']."快捷投标开始投标",$value['ListingId']);
                $this->dispatch((new DoBid($value))->onQueue('dobid'));
                continue;
            }

            $aviLoan[]=$value['ListingId'];
        }
        if(!$aviLoan){
            pp_log("筛选出符合条件标的为空",00);
            return;
        }
        $temp = array();
        foreach($aviLoan as $k=>$v){
            $temp[]=$v;
            if(($k % 9==0 && $k>=0) || (count($aviLoan)< 9 && $k==count($aviLoan)-1) ){
                $this->dispatch((new GetLoanInfo($temp))->onQueue('loaninfo'));
            }
        }
    }


}
