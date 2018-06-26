<?php
namespace App\libraries;
/**
 * 获取授权
 *
 * @param $appid: 应用ID
 * @param $code
 */
use Predis;

class OpenapiClient{
	public function authorize($code) {
		$appid = config('app.appid');
		$request = '{"AppID": "'.$appid.'","Code": "'.$code.'"}';
		$url = "https://ac.ppdai.com/oauth2/authorize";
		return Http::SendAuthRequest ( $url, $request );
	}

	public function get_access_token() {
		$this->cache  = new Predis\Client();
		$accessToken = $this->cache->get("accessToken");
		if(!$accessToken){
			$openID = $this->cache->get("openID");
			if(!$openID){
				$this->cache->setex("accessToken",518400,config('app.accessToken'));
				$this->cache->setex("refreshToken",604800,config('app.refreshToken'));
				$this->cache->setex("openID",604800,config('app.openID'));
			}
			if(!$this->cache->get("accessToken")){
				$refreshToken = $this->cache->get("refreshToken");
				$data = json_decode($this->refresh_token($openID,$refreshToken),true);
				$this->cache->setex("accessToken",518400,$data['AccessToken']);
				$this->cache->setex("refreshToken",604800,$data['RefreshToken']);
				$this->cache->setex("openID",604800,config('app.openID'));
				$accessToken = $data['AccessToken'];
			}
		}
		return $accessToken;
	}


	public function doBid($bv){
		$this->cache  = new Predis\Client();
		if($bv){
			/*投标接口*/
			if(!$this->cache->get("ppid".$bv['ListingId'])){
				$this->cache->setex("ppid".$bv['ListingId'],86400,1);
			}
			$url = "https://openapi.ppdai.com/invest/BidService/Bidding";
			pp_log(" ".$bv['CreditCode']."开始投标",$bv['ListingId']);
			pp_bid_log('开始投标',$bv['ListingId'],$bv['CreditCode']);
			$request = '{"ListingId": '.$bv['ListingId'].',"Amount": 50,"UseCoupon":"true"}';
			$json = $this->send($url, $request,30);
			$result = json_decode($json,true);
			pp_bid_log($json,$result['ListingId']);
			if($result['Result']!= 0){
				pp_bid_log($result['Result'].$result['ResultMessage'],$result['ListingId']);
				pp_log($result['Result'].$result['ResultMessage'],$result['ListingId']);
				return;
			}
			pp_log(" ".$bv['CreditCode']."级标的投资成功",$bv['ListingId']);
			pp_bid_log(" ".$bv['CreditCode']."级标的投资成功",$bv['ListingId']);
		}
	}


	public function doDebet($bv){
		$this->cache  = new Predis\Client();
		if($bv){
			/*投标接口*/
			if(!$this->cache->get("ppid".$bv)){
				$this->cache->setex("ppid".$bv,86400,1);
			}
			$url = "https://openapi.ppdai.com/invest/BidService/BuyDebt";
			pp_log(" 债券".$bv."开始投标");
			debet_bid_log('债券开始投标');
			$request = '{"debtDealId": '.$bv.'}';
			$json = $this->send($url, $request,30);
			$result = json_decode($json,true);
			debet_bid_log($json,$bv);
			if($result['Result']!= 0){
				debet_bid_log("债转".$result['ResultMessage'],$bv);
				pp_log("债转".$result['ResultMessage'],$bv);
				return;
			}
			debet_bid_log(" ".$bv."债转投标成功",$bv);
		}
	}

	/**
	 * 刷新AccessToken
	 *
	 * @param $openid: 用户唯一标识
	 * @param $openid: 应用ID
	 * @param $refreshtoken: 刷新令牌Token
	 */
    public function refresh_token($openid, $refreshtoken) {
		$appid = config('app.appid');
		$request = '{"AppID":"' . $appid . '","OpenID":"' . $openid. '","RefreshToken":"' . $refreshtoken. '"}';
		$url = "https://ac.ppdai.com/oauth2/refreshtoken";
		return Http::SendAuthRequest ( $url, $request );
	}

	/**
	 * 向拍拍贷网关发送请求
	 * Url 请求地址
	 * Data 请求报文
	 * AppId 应用编号
	 * Sign 签名信息
	 * AccessToken 访问令牌
	 *
	 * @param unknown $url
	 * @param unknown $data
	 * @param string $accesstoken
	 */
    public function send($url, $request,$time = 5) {
		$appid = config('app.appid');
		$accesstoken = $this->get_access_token();
		$appPrivateKey = config('app.appPrivateKey');
		return Http::SendRequest ( $url, $request, $appid, $accesstoken,$time );
	}
}



