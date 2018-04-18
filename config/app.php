<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],


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
    'AmountLimit' => 18000,	//曾经设置为11000, 8000，5000

    //待还金额不能太大
    'OwingAmountLimit' => 13000, //曾经设置为10000， 9000

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

    'accessToken' =>"225c3b51-6a1a-460f-aebd-4363d0f294be"//我的
];
