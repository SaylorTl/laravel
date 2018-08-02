<?php

namespace App\service\requestmeta;
use App\service\middleware\ConcreteBuilder;
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/7/20
 * Time: 11:15
 */

/**
 *
 *导演者
 */
class Director {
    private $builder;
    public function __construct(ConcreteBuilder $builder) {
        $this->builder = $builder;
    }


    public function ProductList($type){
        switch ($type) {
            case 'PPD_LOAN':
                $this->builder->getLoanList();
                break;
            case 'PPD_DEBET':
                $this->builder->getDebetList();
                break;
        }
    }

    public function ProductDetail($product,$type){
        switch ($type) {
            case 'PPD_LOAN':
                $this->builder->getLoanDetail($product);
                break;
            case 'PPD_DEBET':
                $this->builder->getDebetDetail($product);
                break;
        }

    }
}