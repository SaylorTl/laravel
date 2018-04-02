<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\libraries\OpenapiClient as OpenapiClient;

class DoBid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     public $bv;
    public $client;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bv)
    {
        $this->client = new OpenapiClient();
        $this->bv = $bv;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->doBid($this->bv);
        //
    }

    public function doBid($bv){
        if($bv){
            /*投标接口*/
            $url = "https://openapi.ppdai.com/invest/BidService/Bidding";
            pp_log(" ".$bv['CreditCode']."开始投标",$bv['ListingId']);
            $request = '{"ListingId": '.$bv['ListingId'].',"Amount": 50,"UseCoupon":"true"}';
            $result = json_decode($this->client->send($url, $request,config('app.accessToken'),5),true);
            if($result['Result']!= 0){
                pp_log($result['Result'].$result['ResultMessage'],$result['ListingId']);
                return;
            }
            pp_log(" ".$bv['CreditCode']."级标的投资成功",$bv['ListingId']);
        }
    }
}
