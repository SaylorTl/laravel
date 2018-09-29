<?php
namespace App\service\middleware;

use App\libraries\ppValidate;
use App\service\reponsemeta\Instance;
use Predis;
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
        $this->cache  = new Predis\Client();
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
        if($this->cache->get("ppid".$this->_repos->ListingId)){
            return;
        }
        $ok = ppValidate::validate($rules,$this->_repos);
        if(true !== $ok){
            pp_bid_log($ok."过滤失败",$this->_repos->ListingId);
            return;
        }
        $result = $this->_receiver->doBid($this->_repos->ListingId);
        if(empty($result)){
            pp_log("投标服务器无反应");
            return;
        }
        if(empty($result->Result)){
            pp_log("投标成功".json_encode($result,JSON_UNESCAPED_UNICODE ));
            return;
        }

        if($result->Result == 0){
            pp_log("投标成功".json_encode($result,JSON_UNESCAPED_UNICODE ));
            return;
        }
        pp_log("投标失败".json_encode($result,JSON_UNESCAPED_UNICODE ),$this->_repos->ListingId);
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
        if($this->cache->get("ppid".$this->_repos->ListingId)){
            return;
        }

        if("AA" !==$this->_repos->CreditCode){
            $ok = ppValidate::validate($rules,$this->_repos);
            if(true !== $ok){
                debet_bid_log($ok."过滤失败",$this->_repos->DebtdealId);
                return;
            }
        }

        $result = $this->_receiver->doDebet($this->_repos->DebtdealId);
        if(empty($result)){
            dbpp_log("投标服务器无反应");
            return;
        }
        if(empty($result->Result)){
            dbpp_log("投标成功".json_encode($result,JSON_UNESCAPED_UNICODE ));
            return;
        }
        if($result->Result == 0){
            dbpp_log("投标成功".json_encode($result,JSON_UNESCAPED_UNICODE ));
            return;
        }
        dbpp_log("投标失败".json_encode($result,JSON_UNESCAPED_UNICODE ),$this->_repos->DebtdealId);
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
