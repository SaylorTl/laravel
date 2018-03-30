<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Predis;
use App\libraries\OpenapiClient as OpenapiClient;

class Ppdai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    var $accessToken ="3e0ac8a3-70b9-4488-bfc8-5ab3149cf8ce";//我的
    var $cache;
    var $client;
    public function __construct()
    {
        parent::__construct();
        $this->client = new OpenapiClient();
        $this->cache  = new Predis\Client();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $this->getLoanList();
    }

    public function pp_log($str,$bid=null,$creditcode=null){
        $now = date("Y-m-d H:i:s");
        echo "($now):".$creditcode."标号".$bid.$str."\n";
        $day = date("Y-m-d");
        file_put_contents(dirname(dirname(dirname(__DIR__)))."/storage/logs/".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
    }

    /*新版投标列表接口（默认每页2000条）*/
    public function getLoanList(){
        //定时清理缓存
        $nowRecodeTime = time();
        $lastRecodeTime = $this->cache->get("lastRecodeTime") ;
        if($nowRecodeTime - $lastRecodeTime >3600){
            $this->cache->set("lastRecodeTime",$nowRecodeTime);
        }
        $url = "https://openapi.ppdai.com/invest/LLoanInfoService/LoanList";
        $date = date("Y-m-d H:i:s",time()-3600);
        $request = '{"PageIndex":1,"StartDateTime": "'.$date.'"}';
        $result = json_decode($this->client->send($url, $request,$this->accessToken),true);
        if($result['Result'] !== 1){
            $this->pp_log($result['ResultMessage']);
            return;
        }
        $aviLoan = array();
        if(empty($result['LoanInfos'])){
            $this->pp_log('查询结果为空','123');
            return;
        }
        foreach($result['LoanInfos'] as $key=>$value){
            if($value['Rate']<10 || $value['Months']>12){
                continue;
            }
            if($value['CreditCode'] == 'AA'){
                $bidurl = "https://openapi.ppdai.com/invest/BidService/Bidding";
                $this->pp_log(" ".$value['CreditCode']."快捷投标开始投标",$value['ListingId']);
                $req = '{"ListingId": '.$value['ListingId'].',"Amount":100,"UseCoupon":"true"}';
                $res = json_decode($this->client->send($bidurl, $req,$this->accessToken),true);
                if($res['Result']!= 0){
                    $this->pp_log($res['Result'].$res['ResultMessage'],$res['ListingId']);
                    continue;
                }
                if(!$res){
                    $this->pp_log("连线中断",$res['ListingId']);
                    continue;
                }
                $this->pp_log(" ".$value['CreditCode']."级标的投资成功",$value['ListingId']);
                continue;
            }
            if($this->cache->get("ppid".$value['ListingId'])){
                $this->pp_log("标号已标记，不再重复查询",$value['ListingId']);
                continue;
            }
            $aviLoan[]=$value['ListingId'];
        }

        $temp = array();
        foreach($aviLoan as $k=>$v){
            $temp[]=$v;
            $this->cache->setex("ppid".$v,900,1);
            if(($k % 9)==0 && $k>0){
                $bidList = $this->getLoanInfo($temp);
                if(1 == $bidList['Result'] ){
                    $this->doBid($bidList['LoanInfos']);
                }
                $temp = array();
            }
        }
    }
    /*获取投标详情*/
    public function getLoanInfo($aviLoan){
        /*新版散标详情批量接口（请求列表不大于10）*/
        $url = "https://openapi.ppdai.com/invest/LLoanInfoService/BatchListingInfos";
        $aviLoanStr = implode(",",$aviLoan);
        $request = '{"ListingIds": ['.$aviLoanStr.']}';
        $result = json_decode($this->client->send($url, $request,$this->accessToken),true);
        if($result['Result']!==1){
            $this->pp_log($result['ResultMessage']);
            return array('Result'=>0);
        }
        return $result;
    }
    public function doBid($bidList){
        if($bidList){
            /*投标接口*/
            $url = "https://openapi.ppdai.com/invest/BidService/Bidding";
            foreach($bidList as $bk=>$bv){
                $amount = $this->getBidAmount($bv);
                if($amount >0){
                    $this->pp_log(" ".$bv['CreditCode']."开始投标",$bv['ListingId']);
                    $request = '{"ListingId": '.$bv['ListingId'].',"Amount": '.$amount.',"UseCoupon":"true"}';
                    $result = json_decode($this->client->send($url, $request,$this->accessToken),true);
                    if($result['Result']!= 0){
                        $this->pp_log($result['Result'].$result['ResultMessage'],$result['ListingId']);
                        continue;
                    }
                    $this->pp_log(" ".$bv['CreditCode']."级标的投资成功",$bv['ListingId']);
                }
            }
        }
    }
    /**根据投资策略，计算该标的投资额度, otherpersonflag为真时考虑其他用户的投标*/
    public  function getBidAmount($LoanInfo){
        $flag="";
        $owing = $LoanInfo['Amount'] + $LoanInfo['OwingAmount'];
        $repayCountRatio = $this->getRepayCountRatio($LoanInfo);
        $owingRatio =$this-> getOwingRatio($LoanInfo);
// //以前分别是5.5 和 0.85
        if($LoanInfo['CreditCode']=='AA'){
            return 100;
        }
// if($LoanInfo['HighestDebt']>=13000 && ($owingRatio>1)){
// $this->pp_log('比历史最高负债高，有点怕怕~'.($LoanInfo['Amount']+ $LoanInfo['OwingAmount']).'/'.$LoanInfo['HighestDebt'],$LoanInfo['ListingId'],$LoanInfo['CreditCode']);
// return 0;
// }
        $owinglimit = $this->getCreditLimit($LoanInfo);
        if($owinglimit<=0){
            return 0;
        }
        $creditPerNorm = $owing;	//设置一个比较大的数(缺省没有信用)
        if($LoanInfo['NormalCount']>0) $creditPerNorm = $owing/$LoanInfo['NormalCount'];	//每次正常还款对应的id
//投资期限设置,下面这行总保留,防止错误
        if($LoanInfo['Months']>12) return 0;	 //12个月也投
//投资期限设置,如果只投6个月，允许这行
//	 if(loan.month>=12) return 0;	 //12个月不投，只投6个月
//单笔金额不能太大
        if($LoanInfo['Amount']>config('app.AmountLimit') || $LoanInfo['Amount']>=10000) return 0;
//待还金额不能太大
        if($LoanInfo['OwingAmount']>config('app.OwingAmountLimit') || config('app.OwingAmount')>=15000) return 0;
        $bidAmount=0;	//预期投资的金额
        if($owinglimit>0){
//成功还款次数/借款次数， 如果大于一定值，则表示前面的还款比较正常
//该参数非常重要，用于判断进行刷信用的情况
            $r = $LoanInfo['NormalCount']/$LoanInfo['SuccessCount'];
            $bidAmount=1;
            if($owing<=$owinglimit){
                $ro = $owing/$owinglimit;	//欠款和额度的比例
                $rhd = 10;	//欠款和历史最高负载的比例
                if($LoanInfo['HighestDebt']>0) $rhd = $owing/$LoanInfo['HighestDebt'];
                $trueloanflag = false;
                if($LoanInfo['TotalPrincipal']>0 && $LoanInfo['TotalPrincipal']<5000){
                    $trueloanflag = false;	//累计借款数额太小不成
                }
                else if($rhd>=1.1){
                    $trueloanflag = false;	//要求所有欠款低于最高负债一定比例
                }else if($LoanInfo['Amount']>=$LoanInfo['HighestPrincipal'] && $rhd>=0.8){
//不超过最高借款,同时满足最高负债的比例
                    $trueloanflag = false;
                }
                else{	//最后是允许的正面条件，前面都是筛选条件
//if(r>=4 || (rhd>0 && rhd<0.7))	//不严格的情况
                    if($r>=4 && ($rhd>0 && $rhd<0.7))	//严格的情况
                    {	 //原来严格的时候设置rhd<0.6
                        $trueloanflag = true;	//正常还款的次数比或者
                    }
                }
//待还金额不能大于10000元
                if($trueloanflag){
                    $bidAmount = $this->getBidCreit($LoanInfo);
                }
            }
        }
        else if($creditPerNorm<=85 && $owing<=6000 && $LoanInfo['NormalCount']>=40){
//所有欠款小于1万，且信用额度较高的情况
//	 bidAmount=BidPolicy.creditBidCount;
        }
        else if($owing<=5000 && $LoanInfo['Months']<=6 && $LoanInfo['SuccessCount'] ==0 && $LoanInfo['CancelCount']==0 && $LoanInfo['FailedCount']==0){
//第一次借款的情况
            if($LoanInfo['Gender']==2 ||
                ($LoanInfo['EducationDegree']!=null && $LoanInfo['EducationDegree']=="本科"
                    && $LoanInfo['StudyStyle']!=null && $LoanInfo['StudyStyle']=="普通"	)){
                $bidAmount=1;	//如果金额太小，用于显示数据，并不实际投资，测试用
            }
        }
        else if($creditPerNorm<100 && $owing<20000 && $LoanInfo['NormalCount']>=30){
//所有欠款小于1万，且信用额度较高的情况
            $bidAmount=1;	//如果金额太小，用于显示数据，并不实际投资，测试用
        }
        return $bidAmount;
    }
    /**flag为true表示是否计算其他人投标的影响*/
    public function getCreditLimit($loaninfo){
//至少要认证身份证和电话
        if($loaninfo['PhoneValidate']==0){	//99%以上的都有手机验证
            $this->pp_log('无手机号',$loaninfo['ListingId']);
            return 0;
        }
        $time_off = time()-strtotime($loaninfo['LastSuccessBorrowTime']);
        if( $time_off<604800){
            $this->pp_log("刚借完又借的资金状况忒差了~淘汰",$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
//有超期还款记录的
        if($loaninfo['OverdueMoreCount']>0){
            $this->pp_log('有超期还款记录',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
//超过3次的直接过掉，后面有更严格的要求
        if($loaninfo['OverdueLessCount']>=3){
            $this->pp_log('逾期(1-15)还清次数大于3',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
//学历的情况
        if($loaninfo['CertificateValidate']==0){
            $this->pp_log('未完成学历认证',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        };	//学历认证的占比1/3
        $owing = $loaninfo['Amount'] + $loaninfo['OwingAmount'];	//如果借款成功后的待还
        $strictflag=false;	//对与很好的标
        if($loaninfo['Months']==6 && ($loaninfo['CreditCode'] == 'D'||$loaninfo['CreditCode'] == 'C')){
            if($loaninfo['NormalCount']>45 && $owing<5500) $strictflag=true;
            else if($loaninfo['NormalCount']>=70 && $owing<6500) $strictflag=true;
            else if($loaninfo['NormalCount']>=100 && $owing<7500) $strictflag=true;
        }
//不允许逾期(在openAPI自动投资中好像特别关注)
        if($loaninfo['OverdueLessCount']>=1){
            $overdueflag = true;
//不严格的情况下,35倍，基数45，
//严格的情况下，45倍，基数60
            if($loaninfo['NormalCount']>config('app.OverduelessNormalCountBase')
                && ($loaninfo['NormalCount']> ($loaninfo['OverdueLessCount']*config('app.OverduelessNormalCountPerOne')))){
                $overdueflag = false;
            }
            if($overdueflag){
//对于逾期一次的
                $this->pp_log('逾期淘汰',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;
            }
        }
        if(config('app.NoWasteCountFlag')){
//不允许有流标和撤标的情况
            if($loaninfo['FailedCount']>0 || (!$strictflag && $loaninfo['FailedCount']==1)){
                $this->pp_log('不容许有流标和撤标的情况',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;//失败
            }
            if($loaninfo['CancelCount']>0 || (!$strictflag && $loaninfo['CancelCount']==1)){
                $this->pp_log('不容许有流标和撤标的情况',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;	//撤销
            }
            if($loaninfo['WasteCount']>0 || (!$strictflag && $loaninfo['WasteCount']==1)){
                $this->pp_log('不容许有流标和撤标的情况',$loaninfo['ListingId'],$loaninfo['CreditCode']);
                return 0;	 //流标
            }
        }
//系统禁止18岁以下的人借款，同时34岁及以下占比80%以上，40岁及以下92%，38岁及以下占比90%
//48岁及以下占比98%
//系统中年龄分布和性别的分布好像关系不大
//***根据自己的黑名单统计30岁以上借款小额的问题比较大(原来是不能大于32岁）
        if($loaninfo['Age']<20 || $loaninfo['Age']>=38){
            $this->pp_log('年龄不符合要求',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
        if($loaninfo['Age']>=30 && $owing<=5000){
            $this->pp_log('30岁以上小额贷款问题比较大',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;
        }
//以前设置成25，待还6000
        if($loaninfo['NormalCount']<35 && $owing<=6000 && $loaninfo['Months']==6 && ($loaninfo['CreditCode'] == 'C')){
//还款记录较少的小额贷款
            $loaninfo['Flag']="Little";
            $this->pp_log('还款记录较少',$loaninfo['ListingId'],$loaninfo['CreditCode']);
            return 0;	//20的情况下出现的标很多
        }
//成功还款次数/借款次数， 如果大于一定值，则表示前面的还款比较正常
//该参数非常重要，用于判断通过全额的提前还款进行刷信用的情况
//如果进行全额本息的提前还款并不会导致异常
// if($loaninfo['SuccessCount'] >0){
// $r = ($loaninfo['NormalCount'])/$loaninfo['SuccessCount'];
// if($r<3){
// $this->pp_log('小贼，涉嫌刷信誉',$loaninfo['ListingId']);
// return 0;
// }
// }
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
            if ($loaninfo['VideoValidate'] == 1 || $loaninfo['NciicIdentityCheck'] == 1) {
// 视频认证或者户籍认证
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
    /**只投学历标，根据学历的不同金额也不同**/
    public function getBidCreit($LoanInfo){
        $bidAmount=config('app.CreditBidAmount');
        if ($LoanInfo['CertificateValidate'] == 1 && $LoanInfo['StudyStyle'] != null) {
            if ($LoanInfo['Gender'] == 2) $bidAmount += 10; // 女
// CertificateValidate学位认证, （EducateValidate学籍认证）
            if ($LoanInfo['StudyStyle']=="普通" ||$LoanInfo['StudyStyle']=="普通全日制") {
                if (strpos($LoanInfo['EducationDegree'],"本科")!=false) {
                    $bidAmount += 30;
                } else {
                    $bidAmount += 20;
                }
            } else if ("研究生"==$LoanInfo['StudyStyle']) {
                $bidAmount += 50;
            } else{
                $bidAmount += 10;
            }
            if ($LoanInfo['VideoValidate'] == 1 || $LoanInfo['NciicIdentityCheck'] == 1) {
// 视频认证或者户籍认证
                $bidAmount += 10;
            }
            if ($LoanInfo['CreditValidate'] == 1) {
                $bidAmount += 20; // 人行信用认证
            }
        }
        return $bidAmount;
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
}
