<?php
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/4/2
 * Time: 10:31
 */


 function pp_log($str,$bid=null,$creditcode=null){
//    $now = date("Y-m-d H:i:s");
//    echo "($now):".$creditcode."标号".$bid.$str."\n";
//    $day = date("Y-m-d");
//    file_put_contents(dirname(dirname(__DIR__))."/storage/logs/".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
}

public function pp_bid_log($str,$bid=null,$creditcode=null){
    $now = date("Y-m-d H:i:s");
    echo "($now):".$creditcode."标号".$bid.$str."\n";
    $day = date("Y-m-d");
    file_put_contents(dirname(dirname(dirname(__DIR__)))."/storage/logs/bid_".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
}

function debet_bid_log($str,$bid=null,$creditcode=null){
    $now = date("Y-m-d H:i:s");
    echo "($now):".$creditcode."标号".$bid.$str."\n";
    $day = date("Y-m-d");
    file_put_contents(dirname(dirname(__DIR__))."/storage/logs/debet_bid".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
}

function dbpp_log($str,$bid=null,$creditcode=null){
//    $now = date("Y-m-d H:i:s");
//    echo "($now):".$creditcode."标号".$bid.$str."\n";
//    $day = date("Y-m-d");
//    file_put_contents(dirname(dirname(__DIR__))."/storage/logs/debet_".$day.".log","($now):".$creditcode."标号".$bid.$str."\n", FILE_APPEND);
}

function clean_dir(){
    $dir = dirname(dirname(__DIR__))."/storage/logs/";
    $date = date("Y-m-d",time());
    $file_arr = array($date.".log","debet_".$date.".log","debet.log","worker_bid.log","worker.log");
    foreach($file_arr as $key=>$value){
        $file_name = $dir.$value;
        if(is_file($file_name)){
            unlink($dir.$value);
        }
    }
}