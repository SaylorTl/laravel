<?php

return [

    'PeiBidFlag'  => true,//是否投陪标
    'CreditBidFlag' => false,//是否投信用标
    'NoWasteCountFlag'=> true,	//true时表示不能允许有流标、废标、撤标等

    //正常小额投标时的策略
    //2017年9月30日，假期模式
    'Pei12Month' => 36,	//12%的陪标的月份，缺省9月标12%，偶尔会12个月的12%标
    'Pei11Month' => 18,	//是否投11%的3月标,小于等于该月数的标才投
    'Pei10Month' => 0,	//是否投10%的1月标,小于等于该月数的标才投
    'PeiRateLimit' => 10, //12.5f;	//陪标的最低投标利率

    //2017年9月29日之前，12.5%的18月标比较常见，正常工作日
    //	static int pei12Month = 9;	//12%的陪标的月份，缺省9月标12%，偶尔会12个月的12%标
    //	static boolean pei11Flag = false;	//是否投11%的3月标
    //	static boolean pei10Flag = false;	//是否投10%的1月标
    //	static double peiRateLimit = 12.5f; //12.5f;	//陪标的最低投标利率



    //大额时的策略
    //	static int pei12Month = 12;	//12%的陪标的月份，缺省9月标12%，偶尔会12个月的12%标
    //	static boolean pei11Flag = true;	//是否投11%的3月标
    //	static boolean pei10Flag = true;	//是否投10%的1月标
    //	static double peiRateLimit = 12.0f; //12.5f;	//陪标的最低投标利率

    'CreditLevel'=>false,	//投标是否限制严格

    'PeiBidAmount' => 500,//除13以上陪标的单笔投资金额
    'CreditBidAmount' => 50,	//信用标投标金额

    //设置为100时，实际能跑到40秒100次， 设置为50时3分40秒秒能跑600次
    //设置为30时2分50秒能跑600次
    'SleepTime' => 2,	//两次查询之间的等待时间，每秒最多10次访问,600次每分钟（原来1分钟1000次），（100-200）



    //不严格的情况下,35次允许逾期1次，基数45，
    //严格的情况下，45次允许逾期1次，基数60
    'OverduelessNormalCountBase' => 45,	//允许逾期必须还款次数大于一定数额
    'OverduelessNormalCountPerOne' => 30,	//每多少次允许逾期1次

    //单笔金额不能太大(1080个标中<5000:1016个；<4000:953个；<3500:902个；<3000:855个，<2000:681个；<1000:314个
    'AmountLimit' => 15000,	//曾经设置为11000, 8000，5000

    //待还金额不能太大
    'OwingAmountLimit' => 9000, //曾经设置为10000， 9000

    //最大金额
    'MaxOwingLimit6'  =>15000,	//6个月的最大允许的待还金额，最大测试过14000
    'MaxOwingLimit12' =>15000,	//12个月最大允许的待还金额

    //请替换自己的appid和appPrivateKey, 申请地址：http://open.ppdai.com/
    'appid'=>'20da2fc969914505977e5c8d58a171dd',

    /**
     * php在生成密钥时，需要选择PEM PKCS#1格式的密钥，否则会报错
     * @var string $appPrivateKey
     * @var string $appPublicKey
     */
    /**
     * php在生成密钥时，需要选择PEM PKCS#1格式的密钥，否则会报错
     * @var string $appPrivateKey
     * @var string $appPublicKey
     */
    'appPrivateKey' => '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCapAZZraRTkJCXiSGZ5ihz9ebNw5DGnng/gklboME+YMHKN37G
skbQGpi5h/tt0OP3anwiBHsOv18psDTruV9aY4Hxxj4IAZUuNKKMLu98dcX24rfB
p9X53234PyCneQzXJ2Q9Xr0ehPJIe61tkiYkIdbvzJl7AnhoFTwEHtXP2QIDAQAB
AoGADSjSJYWCS14s/8g6pMcSQDP3zSDMTCqmHvluVq7KYw2G0DeCwVPgdMsengM1
YTX6gE5+e8KX2jxs07Zb+odO1taSYMJh0q7E9anbUhIGJx7YVEXljFAD6p/xAdO6
uoXVd01/D4chfJyoRzV7cBcLXrSspC7l3cHqmhVLHTFpyAECQQD5D+L9hBQVuxyt
gQm7jhho+BbfvjDWoOgWopOq87BiWe/PQhZfu6cz+4rVubZ9+hzV+FydBEfY3X1m
aARb58MBAkEAnvLMyW8g1Tz4GysPikaTsn4i7XRkW4CBGu55J8YKHM3vJkQqStYl
SASQTqk8CYBdCnEmMBXhmmryxEF0d9WE2QJBAKSCMdRyITIZXV/dE6imusja1YEE
Bw49Sg9pY1BPlfngnd2wMUcak95qD9IL0NZ1Fgbe/Y1Y/nvoEKRLoFV0SAECQEPy
p3i0+OQvXCDBF7OU2C7FnUjFKOG03XwV1dUa49fMcR96pFm5kdZnnQkDb5bgOOXt
2NVVhUvtzDn5gUB5FoECQFLA1+P48MrmL4QEMmzpJgnvrK/M8Obm4LFlXPA7uIpi
HUIUlSYzZFvByTu8UYv/n93WCmiTQeLL2FEYIJO8xUA=
-----END RSA PRIVATE KEY-----',
    'appPublicKey'=>'-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCapAZZraRTkJCXiSGZ5ihz9ebN
w5DGnng/gklboME+YMHKN37GskbQGpi5h/tt0OP3anwiBHsOv18psDTruV9aY4Hx
xj4IAZUuNKKMLu98dcX24rfBp9X53234PyCneQzXJ2Q9Xr0ehPJIe61tkiYkIdbv
zJl7AnhoFTwEHtXP2QIDAQAB
-----END PUBLIC KEY-----',
    'accessToken' =>"4470efa9-b6e7-44b9-9265-b2aac9bcf080"//我的

];
