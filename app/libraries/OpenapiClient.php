<?php
namespace App\libraries;
/**
 * 获取授权
 *
 * @param $appid: 应用ID
 * @param $code
 */
class OpenapiClient{
	public function authorize($code) {
		$appid = config('app.appid');
		$request = '{"AppID": "'.$appid.'","Code": "'.$code.'"}';
		$url = "https://ac.ppdai.com/oauth2/authorize";
		return Http::SendAuthRequest ( $url, $request );
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
    public function send($url, $request, $accesstoken = '',$time = 5) {
		$appid = config('app.appid');
		$appPrivateKey = config('app.appPrivateKey');
		return Http::SendRequest ( $url, $request, $appid, $accesstoken,$time );
	}
}



