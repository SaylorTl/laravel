<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis;
use App\service\middleware\ConcreteBuilder;
use App\service\requestmeta\Director;
use App\service\requestmeta\Invoker;


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
                if ($value->Rate < 12 || $value-> Months > 6) {
                    continue;
                }
                if ("AA" == $value->CreditCode) {
                    $invoker = new Invoker($value);
                    $invoker->action();
                    continue;
                }
                    $aviLoan[] = $value->ListingId;
            }
            if(!empty($aviLoan)){
                $this->dispatch((new GetLoanInfo($aviLoan,$type))->onQueue('loaninfo'));
            }
        }
    }

}
