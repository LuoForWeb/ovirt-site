<?php
/*******************************************
 ** EXCHANGE备份
 ** @author       wuxian@vinchin.com
 ** @date         2022-04-12 
 ********************************************/
require_once XPHP_PATH.'utils/BLLHandler.class.php';
class ExchangeHandler extends BLLHandler{
    private $opcodeHandler;
    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('NodeOpcode');
    }
    
}
