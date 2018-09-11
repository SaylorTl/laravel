<?php

namespace App\service\middleware;

use App\service\reponsemeta\Instance;
use App\service\reponsemeta\Product;
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/7/19
 * Time: 17:36
 */


class ConcreteBuilder
{
     private  $instance;
     const PPDAI_BID = 'PPDAI_BID';
     const PPDAI_LOAN = 'PPDAI_LOAN';
     const PPDAI_DEBET = 'PPDAI_DEBET';
     const PPDAI_LOAN_LIST = 'PPDAI_LOAN_LIST';
     const PPDAI_DEBET_LIST = 'PPDAI_DEBET_LIST';
     private $_product;

     function __construct()
     {
          $this->instance = new Instance();
          $this->_product = new Product();
     }

     public function getLoanList(){
          $this->_item = $this->instance->getLoanList();
          $this->_product->addLoan($this->_item);
     }
     public function getDebetList(){
          $this->_item = $this->instance->getDebet();
          $this->_product->addDebet($this->_item);
     }

     public function getLoanDetail($product){
          $this->_item = $this->instance->getLoanDetail($product);
          $this->_product->addLoanDetail($this->_item);
     }
     public function getDebetDetail($product){
          $this->_item = $this->instance->getDebetDetail($product);
          $this->_product->addDebetDetail($this->_item);
     }

     public function getResult() {
          return $this->_product->_AAitem;
     }


}