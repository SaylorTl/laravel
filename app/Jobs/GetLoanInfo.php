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
        switch ($this->type) {
            case 'PPD_LOAN':
                $this->loanDetail($this->aviList,$this->type);
                break;
            case 'PPD_DEBET':
                $this->debetDetail($this->aviList,$this->type);
                break;
        }
    }


    //债转详情建造者
    public function debetDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        $this->builder = new ConcreteBuilder();
        $this->director = new Director($this->builder);
        $this->director->ProductDetail($aviLoan, $type);
        $product = $this->builder->getResult();

        if(!$product){
            return;
        }
        foreach($product as $key=>$value){
            $this->cache->setex("ppid".$value->ListingId,3600,1);
            $invoker = new Invoker($value);
            $res = $invoker->check();
            if($res){
                $this->debetLoanDetail($value,'PPD_LOAN');
            }
        }
    }

    //债转标的详情建造者
    public function debetLoanDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        $this->builder = new ConcreteBuilder();
        $this->director = new Director($this->builder);
        $this->director->ProductDetail(array($aviLoan->ListingId), $type);
        $product = $this->builder->getResult();
        if(!$product){
            return;
        }
        foreach($product as $key=>$value){
            $value->DebtdealId = $aviLoan->DebtId;
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
        $this->builder = new ConcreteBuilder();
        $this->director = new Director($this->builder);
        $this->director->ProductDetail($aviLoan, $type);
        $product = $this->builder->getResult();
        if(!$product){
            return;
        }
        foreach($product as $key=>$value){
            $this->cache->setex("ppid".$value->ListingId,3600,1);
            $invoker = new Invoker($value);
            $invoker->action();
        }
    }
}
