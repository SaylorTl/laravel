<?php
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/4/2
 * Time: 10:31
 */


 function pp_log($str,$bid=null,$creditcode=null){
    $now = date("Y-m-d H:i:s");
    echo "($now):".$creditcode."标号".$bid.$str."\n";
    $day = date("Y-m-d");
    file_put_contents(dirname(dirname(__DIR__))."/storage/logs/".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
}

function pp_bid_log($str,$bid=null,$creditcode=null){
    $now = date("Y-m-d H:i:s");
    echo "($now):".$creditcode."标号".$bid.$str."\n";
    $day = date("Y-m-d");
    file_put_contents(dirname(dirname(__DIR__))."/storage/logs/bid_".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
}