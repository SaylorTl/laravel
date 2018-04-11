<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\libraries\OpenapiClient as OpenapiClient;
use Predis;
use Illuminate\Console\Command as command;

class GetLoanList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $client;
    public $cache;
    public $command;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->command = new command();
        $this->client = new OpenapiClient();
        $this->cache  = new Predis\Client();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getLoanPagel();
    }
    /*新版投标列表接口（默认每页200条）*/
    public function getLoanPagel(){
        //定时清理缓存
        $url = "https://openapi.ppdai.com/invest/LLoanInfoService/LoanList";
        $date = date("Y-m-d H:i:s",time()-3600);
        $request = '{"PageIndex":"1","StartDateTime": "'.$date.'"}';
        $result = json_decode($this->client->send($url, $request,config('app.accessToken'),30),true);
        if(!$result){
            pp_log("查询失败：".$result['ResultMessage']);
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
            if($this->cache->get("ppid".$value['ListingId'])){
//                pp_log("查询失败，不再重复查询",$value['ListingId']);
                continue;
            }
            if($value['Rate']<12 || $value['Months']>12){
                continue;
            }

            if($value['CreditCode'] == 'AA'){
                pp_log(" ".$value['CreditCode']."快捷投标开始投标",$value['ListingId']);
                $this->command->dispatch((new DoBid($value)))->onQueue('dobid');
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
                $this->dispatch((new GetLoanInfo($temp)))->onQueue("loaninfo");
            }
        }
    }

}
