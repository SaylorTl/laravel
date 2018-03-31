<?php
namespace App\libraries;
use App\libraries\RsaClient as RsaClient;
class http{

    public static function SendAuthRequest($url, $request) {
        $curl = curl_init ( $url );
        $header = array ();
        $header [] = 'Content-Type:application/json;charset=UTF-8';

        curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt ( $curl, CURLOPT_POST, 1 );
        curl_setopt ( $curl, CURLOPT_POSTFIELDS, $request );
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $result = curl_exec ( $curl );
        curl_close ( $curl );

// 	$auth = json_decode ( $result, true );
// 	if ($auth == NULL || $auth == false) {
// 		return $result;
// 	}
// 	return $auth;
        return $result;
    }
// 包装好的发送请求函数
    public static function SendRequest ( $url, $request, $appId, $accessToken,$time=5 ){
        $curl = curl_init ( $url );

        $timestamp = gmdate ( "Y-m-d H:i:s", time ()); // UTC format
        $timestap_sign = RsaClient::sign($appId. $timestamp);
        $requestSignStr = RsaClient::sortToSign($request);
        $request_sign = RsaClient::sign($requestSignStr);

        $header = array ();
        $header [] = 'Content-Type:application/json;charset=UTF-8';
        $header [] = 'X-PPD-TIMESTAMP:' . $timestamp;
        $header [] = 'X-PPD-TIMESTAMP-SIGN:' . $timestap_sign;
        $header [] = 'X-PPD-APPID:' . $appId;
        $header [] = 'X-PPD-SIGN:' . $request_sign;
        if ($accessToken!= null)
            $header [] = 'X-PPD-ACCESSTOKEN:' . $accessToken;
        curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ( $curl, CURLOPT_POST, 1 );
        curl_setopt ( $curl, CURLOPT_POSTFIELDS, $request );
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($curl, CURLOPT_TIMEOUT, $time);
        $result = curl_exec ( $curl );
        curl_close ( $curl );
        return $result;
    }

}







