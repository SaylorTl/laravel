<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\libraries\OpenapiClient as OpenapiClient;
use Predis;


class GetLoanInfo1 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $aviList;
    public $client;
    public $cache;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($aviLoan)
    {
        $this->client = new OpenapiClient();
        $this->aviList = $aviLoan;
        $this->cache  = new Predis\Client();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $bidList =  $this->getLoanInfo($this->aviList);
        if(1 == $bidList['Result'] ){
            foreach($bidList['LoanInfos'] as $bk=>$bv){
                $this->cache->setex("ppid".$bv['ListingId'],3600,1);
                $amount = $this->getBidAmount($bv);
                if($amount >0){
                    $this->client->doBid($bv);
//                    (new DoBid($bv))->dispatch($bv)->onQueue("dobid");
                }
            }
        }
    }
<<<<<<< HEAD:app/Jobs/GetLoanInfo1.php
=======
    public function getDebetInfo($debid){
        /*新版散标详情批量接口（请求列表不大于10）*/
        $url = "https://openapi.ppdai.com/debt/openapiNoAuth/batchDebtInfo";
        $request = '{"DebtIds": ['.$debid.']}';
        $result = json_decode($this->client->send($url, $request,3),true);
        if($result['Result']!==1){
            debet_bid_log("获取债转信息详情失败".$result['ResultMessage']);
            return false;
        }
        $Debt =  $result['DebtInfos'][0];
        if($Debt['PastDueNumber']>0){
            debet_bid_log("债转有逾期记录",$debid,$Debt['CurrentCreditCode']);
            return false;
        }
        if(!in_array($Debt['CurrentCreditCode'],array("AA","A","B","C"))){
            debet_bid_log("债转掉级太快",$debid,$Debt['CurrentCreditCode']);
            return false;
        }
        if($Debt['PastDueDay']>0){
            debet_bid_log("债转有逾期记录",$debid,$Debt['CurrentCreditCode']);
            return false;
        }
        if($Debt['AllowanceRadio']<0){
            debet_bid_log("债转折让比例太低",$debid,$Debt['CurrentCreditCode']);
            return false;
        }
        return true;


    }
>>>>>>> 4f828adc173d4bce4837a435fbf317681389ee02:app/Jobs/GetDebetInfo.php

    /*获取投标详情*/
    public function getLoanInfo($aviLoan){
        /*新版散标详情批量接口（请求列表不大于10）*/
        $url = "https://openapi.ppdai.com/listing/openapiNoAuth/batchListingInfo";
        $aviLoanStr = implode(",",$aviLoan);
        $request = '{"ListingIds": ['.$aviLoanStr.']}';
        $result = json_decode($this->client->send($url, $request,3),true);
        if($result['Result']!==1){
            pp_log("获取信息详情失败".$result['ResultMessage']);
            return array('Result'=>0);
        }
        return $result;
    }

    /**根据投资策略，计算该标的投资额度, otherpersonflag为真时考虑其他用户的投标*/
    public  function getBidAmount($LoanInfo){
        $owinglimit = $this->getCreditLimit($LoanInfo);
        if($owinglimit<=0){
            return 0;
        }
        $owing = $LoanInfo['Amount'] + $LoanInfo['OwingAmount'];
        $creditPerNorm = $owing;

        $bidAmount=0;	//预期投资的金额
        if($owinglimit>0){
            $bidAmount = 50;
        }
        return $bidAmount;
    }


    /**flag为true表示是否计算其他人投标的影响*/
    public function getCreditLimit($loaninfo){
        //至少要认证身份证和电话
        if($loaninfo['PhoneValidate']==0){	//99%以上的都有手机验证
            pp_log('无手机号',$loaninfo['ListingId']);
            return 0;
        }
        $owing = $loaninfo['Amount'] + $loaninfo['OwingAmount'];
        $owingRatio =$this-> getOwingRatio($loaninfo);
        //以前分别是5.5 和 0.85
        if($loaninfo['HighestDebt']>=12000 && ($owingRatio>1)){
            pp_log('比历史最高负债高，有点怕怕~'.($loaninfo['Amount']+ $loaninfo['OwingAmount']).'/'.$loaninfo['HighestDebt'],$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
        //设置一个比较大的数(缺省没有信用)
        if($loaninfo['NormalCount']>0) $creditPerNorm = $owing/$loaninfo['NormalCount'];	//每次正常还款对应的id
        //投资期限设置,下面这行总保留,防止错误
        if($loaninfo['Months']>12){
            pp_log('期限太长',$loaninfo['ListingId']);
            return 0;	 //12个月也投
        }
        //投资期限设置,如果只投6个月，允许这行
        //if(loan.month>=12) return 0;	 //12个月不投，只投6个月
        //单笔金额不能太大
        if($loaninfo['Amount']>config('app.AmountLimit')){
            pp_log('单笔金额不能太大,'.$loaninfo['Amount'],$loaninfo['ListingId']);
            return 0;
        }
        //待还金额不能太大
        if($loaninfo['OwingAmount']>config('app.OwingAmountLimit')){
            pp_log('待还金额不能太大,'.$loaninfo['OwingAmount'],$loaninfo['ListingId']);
            return 0;
        }

        //待还金额不能太大
        if($owing>13000){
            pp_log('负债太大,'.$loaninfo['OwingAmount'],$loaninfo['ListingId']);
            return 0;
        }

        if($loaninfo['LastSuccessBorrowTime']){
            $time_off = time()-strtotime($loaninfo['LastSuccessBorrowTime']);
            if( $time_off<2767680){
                pp_log(" 30天之内不许重复贷款，刚借完又借的资金状况忒差了~淘汰",$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;
            }
        }
        //有超期还款记录的
        if($loaninfo['OverdueMoreCount']>0){
            pp_log('有超期还款记录,'.$loaninfo['OverdueMoreCount'],$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
        //超过3次的直接过掉，后面有更严格的要求
        if($loaninfo['OverdueLessCount']>5){
            pp_log('逾期(1-15)还清次数大于5,'.$loaninfo['OverdueLessCount'],$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
        //学历的情况
        if($loaninfo['CertificateValidate']==0){
            pp_log('未完成学历认证',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        };	//学历认证的占比1/3

        if($loaninfo['TotalPrincipal']>0 && $loaninfo['TotalPrincipal']<5000){
            pp_log('累计接待金额太小',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;	//累计借款数额太小不成
        }

        //对于第一次借贷的必须是本科学历
        if($loaninfo['SuccessCount'] == 0){
            // || strpos($loaninfo['EducationDegree'],"本科") ==false
            if (!($loaninfo['StudyStyle']=="普通" ||$loaninfo['StudyStyle']=="普通全日制")) {
                pp_log('第一次借贷非全日制学历，淘汰',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;
            }
        }
        //系统禁止18岁以下的人借款，同时34岁及以下占比80%以上，40岁及以下92%，38岁及以下占比90%
        //48岁及以下占比98%
        //系统中年龄分布和性别的分布好像关系不大
        //***根据自己的黑名单统计30岁以上借款小额的问题比较大(原来是不能大于32岁）
        if($loaninfo['Age']<22 || $loaninfo['Age']>=38){
            pp_log('年龄不符合要求,'.$loaninfo['Age'],$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
        if($loaninfo['Age']>=32 && $owing<=5000){
            pp_log('30岁以上小额贷款问题比较大',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }

        if($loaninfo['SuccessCount'] >1){
            $r = ($loaninfo['OverdueLessCount']+$loaninfo['OverdueMoreCount'])/$loaninfo['NormalCount'];
            if($r>0.3){
                pp_log('新手标，信用太差',$loaninfo['ListingId']);
                return 0;
            }
        }
        $owing = $loaninfo['Amount'] + $loaninfo['OwingAmount'];	//如果借款成功后的待还
        $strictflag=false;	//对与很好的标
        if($loaninfo['Months']==6 && ($loaninfo['CreditCode'] == 'D'||$loaninfo['CreditCode'] == 'C')){
            if($loaninfo['NormalCount']>45 && $owing<6500) $strictflag=true;
            else if($loaninfo['NormalCount']>=70 && $owing<7500) $strictflag=true;
            else if($loaninfo['NormalCount']>=100 && $owing<8500) $strictflag=true;
        }
        //不允许逾期(在openAPI自动投资中好像特别关注)
        if($loaninfo['OverdueLessCount']>=3){
            $overdueflag = true;
            //不严格的情况下,35倍，基数45，
            //严格的情况下，45倍，基数60
            if($loaninfo['NormalCount']>config('app.OverduelessNormalCountBase')
                && ($loaninfo['NormalCount']> ($loaninfo['OverdueLessCount']*config('app.OverduelessNormalCountPerOne')))){
                $overdueflag = false;
            }
            if($overdueflag){
                //对于逾期一次的
                pp_log('逾期淘汰',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;
            }
        }
        if(config('app.NoWasteCountFlag')){
            //不允许有流标和撤标的情况
            if($loaninfo['FailedCount']>0 || (!$strictflag && $loaninfo['FailedCount']==1)){
                pp_log('不容许有流标和撤标的情况',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;//失败
            }
//            if($loaninfo['CancelCount']>0 || (!$strictflag && $loaninfo['CancelCount']==1)){
//                pp_log('不容许有流标和撤标的情况',$loaninfo['ListingId'],$loaninfo['CreditCode']);
//                return 0;	//撤销
//            }
            if($loaninfo['WasteCount']>0 || (!$strictflag && $loaninfo['WasteCount']==1)){
                pp_log('不容许有流标和撤标的情况',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;	 //流标
            }
        }

        //成功还款次数/借款次数， 如果大于一定值，则表示前面的还款比较正常
        //该参数非常重要，用于判断通过全额的提前还款进行刷信用的情况
        //如果进行全额本息的提前还款并不会导致异常
//        if($loaninfo['SuccessCount'] >0){
//            $r = ($loaninfo['NormalCount'])/$loaninfo['SuccessCount'];
//            if($r<3){
//                pp_log('小贼，涉嫌刷信誉',$loaninfo['ListingId']);
//                return 0;
//            }
//        }
        //计算可能的
        $owinglimit = 0;
        if ($loaninfo['CertificateValidate'] == 1 && $loaninfo['StudyStyle'] != null) {
            if ($loaninfo['Gender'] == 2) $owinglimit += 1000; // 女
// CertificateValidate学位认证, （EducateValidate学籍认证）
            if ($loaninfo['StudyStyle']=="普通" ||$loaninfo['StudyStyle']=="普通全日制") {
                if (strpos($loaninfo['EducationDegree'],"本科")!=false) {
                    $owinglimit += 3000;
                } else {
                    $owinglimit += 2000;
                }
            } else if ("研究生"==$loaninfo['StudyStyle']) {
                $owinglimit += 5000;
            } else{
                $owinglimit += 1000;
            }
            if ($loaninfo['CreditValidate'] == 1) {
                $owinglimit += 2000; // 人行信用认证
            }
            //依据已经还款的次数进行信用评价
            if ($loaninfo['NormalCount'] >= 25) {
                $rebitlimit = 200 * ($loaninfo['NormalCount'] - 20);
                $owinglimit += $rebitlimit;
            }
            //依据累计还款的额度信用评价
            if ($loaninfo['TotalPrincipal'] >= 0) {
                $rebitlimit = ($loaninfo['TotalPrincipal'] - $loaninfo['OwingPrincipal'])/5;
                if($rebitlimit>0) $owinglimit += $rebitlimit;
            }
            // 不能太高, 目前6个月的额度比12个月的额度高
            if ($owinglimit > config('app.MaxOwingLimit6'))
                $owinglimit = config('app.MaxOwingLimit6');
            if ($loaninfo['Months'] >= 12) {
                if ($owinglimit > config('app.MaxOwingLimit12'))
                    $owinglimit = config('app.MaxOwingLimit12');
            }
        }
        return $owinglimit;
    }
    /**欠款与最高欠款比例*/
    public function getOwingRatio($loaninfo){
        $owingRatio = 0;
        if($loaninfo['HighestDebt']>0) $owingRatio= ($loaninfo['Amount']+ $loaninfo['OwingAmount'])/$loaninfo['HighestDebt'];
        return $owingRatio;
    }
    /**回款次数比例*/
    public function getRepayCountRatio($loaninfo){
        $repayRatio = 0;
        if($loaninfo['SuccessCount']>0) $repayRatio= $loaninfo['NormalCount']/$loaninfo['SuccessCount'];
        return $repayRatio;
    }


    //标的详情建造者
    public function debetDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        $temp = array();
        foreach ($aviLoan as $k => $v) {
            $temp[] = $v;
            if (($k % 9 == 0 && $k >= 0) || (count($aviLoan) < 9 && $k == count($aviLoan) - 1)) {
                $this->director->ProductDetail($temp, $type);
                $product = $this->builder->getResult();
                if(!$product){
                    continue;
                }
                foreach($product as $key=>$value){
                    $rules = array('pastduenumber_too_big','currentcreditcode_too_low','pastdueday_too_much','allowanceradio_too_low');
                    $ok = ppValidate::validate($rules,$value);
                    if(true !==$ok){
                        continue;
                    }
                    $this->debetLoanDetail($value,'PPD_LOAN');
                }
                $temp = array();
                return;
            }
        }
    }

    //标的详情建造者
    public function debetLoanDetail($aviLoan, $type)
    {
        if(empty($aviLoan)){
            exit();
        }
        $this->builder = new ConcreteBuilder();
        $this->director = new Director($this->builder);
        $this->director->ProductDetail(array($aviLoan->ListingId), $type);
        $product = $this->builder->getResult();
        foreach($product as $key=>$value){
            $value->DebtId = $aviLoan->DebtId;
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
        foreach ($aviLoan as $k => $v) {
            $temp[] = $v;
            if (($k % 9 == 0 && $k >= 0) || (count($aviLoan) < 9 && $k == count($aviLoan) - 1)) {
                continue;
            }
            $this->builder = new ConcreteBuilder();
            $this->director = new Director($this->builder);
            $this->director->ProductDetail($temp, $type);
            $product = $this->builder->getResult();
            if(!$product){
                continue;
            }
            foreach($product as $key=>$value){
                $invoker = new Invoker($value);
                $invoker->action();
            }
        }
    }
}
