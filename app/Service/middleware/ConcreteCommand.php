<?php
namespace App\service\middleware;

use App\libraries\ppValidate;
use App\service\reponsemeta\Instance;
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/7/20
 * Time: 15:07
 */
class ConcreteCommand { // 具体命令方法
    private $_receiver;
    private $_repos;
    public function __construct(Instance $receiver,$value) {
        $this->_receiver = $receiver;
        $this->_repos = $value;
    }
    public function execute() {
        $rules = [
            "phone_not_avi",
            "month_too_long",
            "highest_too_much",
            "amount_too_big",
            "owingamount_too_big",
            "owing_too_big",
            "lastsuccessborrowtime_too_short",
            "overduemorecount_too_much",
            "overduelesscount_too_much",
            "certificatevalidate_not",
            "totalprincipal_too_small",
            "fistloan_not_certificate",
            "age_not_reg",
            "old_too_small",
            "credit_too_bad",
            "OverdueLessCount_check",
            "NoWasteCountFlag"
        ];
        $ok = ppValidate::validate($rules,$this->_repos);
        if(true !== $ok){
            pp_bid_log($ok."过滤失败",$this->_repos->ListingId);
            return;
        }
        $result = $this->_receiver->doBid($this->_repos->ListingId);
        if($result->Result == 0){
            pp_log("投标成功".json_encode($result,JSON_UNESCAPED_UNICODE ));
        }
        pp_log($ok."投标失败".json_encode($result,JSON_UNESCAPED_UNICODE ),$this->_repos->ListingId);
    }

    public function debetExecute() {
        $rules = [
            "phone_not_avi",
            "month_too_long",
            "highest_too_much",
            "amount_too_big",
            "owingamount_too_big",
            "owing_too_big",
            "lastsuccessborrowtime_too_short",
            "overduemorecount_too_much",
            "overduelesscount_too_much",
            "certificatevalidate_not",
            "totalprincipal_too_small",
            "fistloan_not_certificate",
            "age_not_reg",
            "old_too_small",
            "credit_too_bad",
            "OverdueLessCount_check",
            "NoWasteCountFlag"
        ];
        if("AA" !==$this->_repos->CreditCode){
            $ok = ppValidate::validate($rules,$this->_repos);
            if(true !== $ok){
                debet_bid_log($ok."过滤失败",$this->_repos->DebtdealId);
                return;
            }
        }

        $result = $this->_receiver->doDebet($this->_repos->DebtdealId);
        if($result->Result == 0){
            dbpp_log("投标成功".json_encode($result,JSON_UNESCAPED_UNICODE ));
        }
        dbpp_log($ok."投标失败".json_encode($result,JSON_UNESCAPED_UNICODE ),$this->_repos->DebtdealId);
    }

    public function check(){
        $rules = array('pastduenumber_too_big','currentcreditcode_too_low','pastdueday_too_much','allowanceradio_too_low');
        $ok = ppValidate::validate($rules,$this->_repos);
        if(true !== $ok){
            debet_bid_log($ok."债券过滤失败",$this->_repos->DebtId);
            return false;
        }
        return $ok;
    }


}
