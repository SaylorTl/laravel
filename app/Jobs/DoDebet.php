<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\libraries\OpenapiClient as OpenapiClient;
use Predis;


class DoDebet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bv;
    public $client;
    public $cache;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($bv)
    {
        $this->client = new OpenapiClient();
        $this->bv = $bv;
        $this->cache  = new Predis\Client();
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $this->doBid($this->bv);
    }

    public function doDebet($bv){
        if($bv){
            /*投标接口*/
            if(!$this->cache->get("ppid".$bv)){
                $this->cache->setex("ppid".$bv,86400,1);
            }
            $url = "https://openapi.ppdai.com/invest/BidService/BuyDebt";
            pp_log(" 债券".$bv."开始投标");
            debet_bid_log('债券开始投标');
            $request = '{"debtDealId": '.$bv.'}';
            $json = $this->client->send($url, $request,30);
            debet_bid_log($json,$bv);
            $result = json_decode($json,true);
            if($result['Result']!= 0){
                debet_bid_log("债转".$result['ResultMessage'],$bv);
                pp_log("债转".$result['ResultMessage'],$bv);
                return;
            }
            debet_bid_log(" ".$bv."债转投标成功",$bv);
        }
    }
}
