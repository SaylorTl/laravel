<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Predis;

class IndexController extends Controller
{
    //
    public function index(){
        $single_server = array(
            'host' => '118.24.80.47',
            'port' => 6379,
            'database' => 15,
        );

        $client  = new Predis\Client($single_server);
        $client->set('foo', 'bar');
        $client->get('foo');
        print_r($client);exit;
    }
}
