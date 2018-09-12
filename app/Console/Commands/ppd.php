<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis;
use App\service\middleware\ConcreteBuilder;
use App\service\requestmeta\Director;
use App\service\requestmeta\Invoker;
use App\Jobs\GetLoanInfo;


class Ppd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ppd {type}';

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
                if ($value->Rate < 11 || $value-> Months > 6) {
                    continue;
                }
                pp_bid_log("筛选".json_encode($value,JSON_UNESCAPED_UNICODE ));
                if ("AA" == $value->CreditCode) {
                    $invoker = new Invoker($value);
                    $invoker->action();
                    continue;
                }
                $aviLoan[] = $value->ListingId;
            }
            $temp = array();
            if(!empty($aviLoan)){
                $k=0;
                foreach ($aviLoan as  $v) {
                    $k++;
                    $temp [] = $v;
                    if (($k % 9 == 0 && $k >= 0) || (count($aviLoan) < 9 && $k == count($aviLoan))) {
                        pp_bid_log("正在投标".json_encode($aviLoan,JSON_UNESCAPED_UNICODE ));
                        $this->dispatch((new GetLoanInfo($temp,$type))->onQueue('loaninfo'));
                        $temp = array();
                    }
                }

            }
        }
    }

}
