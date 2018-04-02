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

     public $bid;
    public $client;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bid)
    {
        $this->client = new OpenapiClient();
        $this->bid = $bid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->doBid($this->bid);
        //
    }

    public function doBid($bid){
        if($bid){
            /*投标接口*/
            $url = "https://openapi.ppdai.com/invest/BidService/Bidding";
            pp_log(" ".$bid['CreditCode']."开始投标",$bid['ListingId']);
            $request = '{"ListingId": '.$bid['ListingId'].',"Amount": 50,"UseCoupon":"true"}';
            $result = json_decode($this->client->send($url, $request,config('app.accessToken'),5),true);
            if($result['Result']!= 0){
                pp_log($result['Result'].$result['ResultMessage'],$result['ListingId']);
                return;
            }
            pp_log(" ".$bid['CreditCode']."级标的投资成功",$bid['ListingId']);
        }
    }
}
