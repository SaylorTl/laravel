<?php
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/7/20
 * Time: 11:31
 */
namespace App\service\reponsemeta;
use App\libraries\OpenapiClient as client;

class Instance
{

    private $endpoint = [
        'base' => 'https://openapi.ppdai.com/',
        'loanList' => 'https://openapi.ppdai.com/listing/openapiNoAuth/loanList',
        'loanDetail' => 'https://openapi.ppdai.com/listing/openapiNoAuth/batchListingInfo',
        'doBid' => 'https://openapi.ppdai.com/listing/openapi/bid',
        'debetList'=>'https://openapi.ppdai.com/debt/openapiNoAuth/buyList',
        'debetDetail'=>'https://openapi.ppdai.com/debt/openapiNoAuth/batchDebtInfo',
        'doDebet' => 'https://openapi.ppdai.com/debt/openapi/buy',

    ];

    private $client;
    public $repose;

    public function __construct() {
        $this->client = new client();
    }

    /**
     * @param $param LoanList
     * @return array| boolean
     */
    public function getLoanList($PageIndex=1){
        $date = date("Y-m-d H:i:s",time()-3600);
        $this->repose = '{"PageIndex":"'.$PageIndex.'","StartDateTime": "'.$date.'"}';
        return $this->client->send($this->endpoint['loanList'],$this->repose);
    }

    public function getLoanDetail($param){
        $this->repose = '{"ListingIds": ['.implode(",",$param).']}';;
        return $this->client->send($this->endpoint['loanDetail'],$this->repose);
    }

    public function getDebet($PageIndex=1){
        $date = date("Y-m-d H:i:s",time()-3600);
        $this->repose = '{"PageIndex":"'.$PageIndex.'","StartDateTime": "'.$date.'"}';
        debet_bid_log("12321".$this->endpoint['debetList']);
        return $this->client->send($this->endpoint['debetList'],$this->repose);
    }

    public function getDebetDetail($param){
        $this->repose = '{"DebtIds": ['.implode(",",$param).']}';;
        return $this->client->send($this->endpoint['debetDetail'],$this->repose);
    }

    public function doBid($ListingId){
        $this->repose = '{"ListingId": '.$ListingId.',"Amount": 50,"UseCoupon":"true"}';
        return $this->client->send($this->endpoint['doBid'],$this->repose);
    }

    public function doDebet($DealId){
        $this->repose = '{"debtDealId": '.$DealId.'}';
        return $this->client->send($this->endpoint['doDebet'],$this->repose);
    }

}