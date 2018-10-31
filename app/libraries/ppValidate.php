<?php

namespace App\libraries;
use Illuminate\Support\Facades\Config as LaravelConfig;

class ppValidate
{

    public static function validate(array $rules,$loan){
        $config  = LaravelConfig::get('ppdai');
        foreach ($rules as $rule){
            $method = "rule_{$rule}";
            //todo 这里的rule 是可以使用参数的，比如月份 12
            if(!self::$method($loan,$config))  return $method;
        }
        return true;
    }

    public static function rule_phone_not_avi($loan){
        if(empty($loan->PhoneValidate)){
            pp_log("投标成功".json_encode($loan,JSON_UNESCAPED_UNICODE ));
        }
        if($loan->PhoneValidate == 0) return false;
        return true;
    }

    public static function rule_month_too_long($loan){
        if($loan->Months >6) return false;
        return true;
    }

    public static function rule_highest_too_much($loan,$config){
        $owing = $loan->Amount + $loan->OwingAmount;
        $owingRatio = self::getOwingRatio($loan);
        if($loan->HighestDebt>=12000 && ($owingRatio>1)){
            return false;
        }
        return true;
    }

    public static function rule_amount_too_big($loan,$config){
        //单笔金额不能太大
        if($loan->Amount > $config['AmountLimit']){
            return false;
        }
        return true;
    }

    public static function rule_owingamount_too_big($loan,$config){
        //待还金额不能太大
        if($loan->OwingAmount>$config['OwingAmountLimit']){
            return false;
        }
        return true;
    }

    public static function rule_owing_too_big($loan){
        $owing = $loan->Amount + $loan->OwingAmount;
        //待还金额不能太大
        if($owing> 13000){
            return false;
        }
        return true;
    }

    public static function rule_lastsuccessborrowtime_too_short($loan){
        $time_off = time()-strtotime($loan->LastSuccessBorrowTime);
        if( $time_off<2767680){
            return false;
        }
        return true;
    }

    public static function rule_overduemorecount_too_much($loan){
        if($loan->OverdueMoreCount>0){
            return false;
        }
        return true;
    }

    public static function  rule_overduelesscount_too_much($loan){
        //超过3次的直接过掉，后面有更严格的要求
        if($loan->OverdueLessCount>5){
            return false;
        }
        return true;
    }

    public static function  rule_certificatevalidate_not($loan){
        //学历的情况
        if($loan->CertificateValidate ==0){
            return false;
        };	//学历认证的占比1/3
        return true;
    }

    public static function  rule_totalprincipal_too_small($loan){
        if($loan->TotalPrincipal >0 && $loan->TotalPrincipal<5000){
            return false;	//累计借款数额太小不成
        }
        return true;
    }

    public static function  rule_fistloan_not_certificate($loan){
        //对于第一次借贷的必须是本科学历
        if($loan->SuccessCount == 0){
            if (!($loan->StudyStyle=="普通" ||$loan->StudyStyle=="普通全日制")) {
                return false;
            }
        }
        return true;
    }

    public static function  rule_age_not_reg($loan){
        if($loan->Age<22 || $loan->Age>=38){
            return false;
        }
        return true;
    }


    public static function  rule_old_too_small($loan){
        $owing = $loan->Amount + $loan->OwingAmount;
        if($loan->Age>=32 && $owing<=5000){
            return false;
        }
        return true;
    }

    public static function  rule_credit_too_bad($loan){
        if($loan->SuccessCount >1){
            $r = ($loan->OverdueLessCount + $loan->OverdueMoreCount)/$loan->NormalCount;
            if($r>0.3){
                return false;
            }
        }
        return true;
    }

    public static function  rule_OverdueLessCount_check($loan,$config)
    {
        //不允许逾期(在openAPI自动投资中好像特别关注)
        if($loan->OverdueLessCount>=3){
            $overdueflag = true;
            //不严格的情况下,35倍，基数45，
            //严格的情况下，45倍，基数60
            if($loan->NormalCount>$config['OverduelessNormalCountBase']
                && ($loan->NormalCount> ($loan->OverdueLessCount*$config['OverduelessNormalCountPerOne']))){
                $overdueflag = false;
            }
            if($overdueflag){
                //对于逾期一次的
                return false;
            }
        }
        return true;
    }

    public static function rule_NoWasteCountFlag($loan,$config)
    {
        $strictflag=false;	//对与很好的标
        $owing = $loan->Amount + $loan->OwingAmount;
        if($loan->Months==6 && ($loan->CreditCode == 'D'||$loan->CreditCode == 'C')){
            if($loan->NormalCount>45 && $owing<6500) $strictflag=true;
            else if($loan->NormalCount>=70 && $owing<7500) $strictflag=true;
            else if($loan->NormalCount>=100 && $owing<8500) $strictflag=true;
        }
        //不允许逾期(在openAPI自动投资中好像特别关注)
        if ($config['NoWasteCountFlag']) {
            //不允许有流标和撤标的情况
            if ($loan->FailedCount > 0 || (!$strictflag && $loan->FailedCount == 1)) {
                return false;
            }
            if ($loan->WasteCount > 0 || (!$strictflag && $loan->WasteCount == 1)) {
                return false;
            }
        }
        return true;
    }


    public static function  rule_pastduenumber_too_big($loan){
        if($loan->PastDueNumber > 0){
            return false;
        }
        return true;
    }

    public static function  rule_currentcreditcode_too_low($loan){
        if(!in_array($loan->CurrentCreditCode,array("AA","A","B","C"))){
            return false;
        }
        return true;
    }

    public static function  rule_pastdueday_too_much($loan){
        if($loan->PastDueDay>0){
            return false;
        }
        return true;
    }

    public static function  rule_allowanceradio_too_low($loan){
        if($loan->AllowanceRadio<0){
            return false;
        }
        return true;
    }


    public static function getOwingRatio($loan){
        //超过3次的直接过掉，后面有更严格的要求
        if($loan->OverdueLessCount>5){
            return false;
        }
        return true;
    }



}