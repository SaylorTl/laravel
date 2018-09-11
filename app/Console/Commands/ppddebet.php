<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis;
use App\service\middleware\ConcreteBuilder;
use App\service\requestmeta\Director;
use App\service\requestmeta\Invoker;
use App\Jobs\GetLoanInfo;



class ppddebet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ppddebet {type}';

    const PPD_LOAN = "PPD_LOAN";
    const PPD_DEBET = "PPD_DEBET";

    private $builder;
    private $director;
    public $cache;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function __construct()
    {
        parent::__construct();
        $this->cache  = new Predis\Client();
        $this->builder = new ConcreteBuilder();
        $this->director = new Director($this->builder);
    }

    /**
     * 标的列表建造者
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        $this->director->ProductList($type);
        $product = $this->builder->getResult();
        if($product){
            $aviLoan = array();
            foreach ($product as $key => $value) {
                if($this->cache->get("ppid".$value->ListingId)){
                    continue;
                }
                if($value->PriceforSale>300 || $value->PriceforSaleRate<12|| $value->OwingNumber>6 || !in_array($value->CreditCode,array("AA","A","B","C","D"))){
                    continue;
                }
                if ("AA" == $value->CreditCode) {
                    $invoker = new Invoker($value);
                    $invoker->debetAction();
                    continue;
                }
                if($value->PriceforSale > 50 || $value->PriceforSaleRate < 16){
                    continue;
                }
                $aviLoan[$value->ListingId] = $value->DebtdealId;
            }
            $temp = array();
            if(!empty($aviLoan)){
                $k=0;
                foreach ($aviLoan as  $v) {
                    $k++;
                    $temp [] = $v;
                    if (($k % 9 == 0 && $k >= 0) || (count($aviLoan) < 9 && $k == count($aviLoan))) {
                        $this->dispatch((new GetLoanInfo($temp,$type))->onQueue('loaninfo'));
                        $temp = array();
                    }
                }

            }
        }
    }
}
