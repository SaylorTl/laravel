<?php
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/7/19
 * Time: 17:36
 */
namespace App\service\reponsemeta;


class Product
{
    public  $_AAitem;

    public function addLoan($_item){
        $this->_AAitem = array();
        if(isset($_item->Result) && $_item->Result == 1){
            $part = $_item->LoanInfos;
            if(!empty($part)){
                $this->_AAitem = $part;
                return $this->_AAitem ;
            }
        }
        return false;
    }

    public function addDebet($_item){
        $this->_AAitem = array();
        debet_bid_log("33".$_item->Result);
        if(isset($_item->Result) && $_item->Result == 1){
            $part = $_item->DebtInfos;
            debet_bid_log("33".json_encode($_item->DebtInfos));
            if(!empty($part)){
                $this->_AAitem = $part;
                return $this->_AAitem ;
            }
        }
        return false;
    }

    public function addLoanDetail($_item){
        $this->_AAitem = array();
        if(isset($_item->Result) && $_item->Result == 1){
            $part = $_item->LoanInfos;
            if(!empty($part)){
                $this->_AAitem = $part;
                return $this->_AAitem ;
            }
        }
        return false;
    }

    public function addDebetDetail($_item){
        $this->_AAitem = array();
        if(isset($_item->Result) && $_item->Result == 1){
            $part = $_item->DebtInfos;
            if(!empty($part)){
                $this->_AAitem = $part;
                return $this->_AAitem ;
            }
        }
        return false;
    }

}