<?php
namespace App\service\requestmeta;
/**
 * Created by PhpStorm.
 * User: hodor-out
 * Date: 2018/7/20
 * Time: 16:15
 */
use App\service\reponsemeta\Instance;
use App\service\middleware\ConcreteCommand;

class Invoker { // 请求者角色
    private $_command;
    public function __construct($value) {
        $receiver = new Instance();
        $command = new ConcreteCommand($receiver,$value);
        $this->_command = $command;
    }
    public function action() {
        $this->_command->execute();
    }

    public function debetAction() {
        $this->_command->debetExecute();
    }
    public function check(){
        return $this->_command->check();
    }
}