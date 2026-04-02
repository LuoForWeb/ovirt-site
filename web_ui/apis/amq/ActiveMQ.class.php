<?php
/******************************************* 
** 消息推送activemq处理类
** 
** @author       
** @date         2018-04-08
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class ActiveMQ extends OPHandler{
    //stomp对象
    private $stomp;
    
    private $settings;
    
    public function __construct(){
        
    }
    
    public function __destruct(){
        /* close connection */
        unset($this->stomp);
    }
    
    /**
     * 初始化连接
     * @param array $settings 新的配置
     */
    private function initStomp($settings){
        $flag = !$this->listenerSettings($settings);
        if(!$this->stomp || $flag){
            $this->settings = $settings;
            try {
                $this->writeLog("New Connection ActiveMQ!");
                $this->stomp = new Stomp($this->getConnectUrl(), $this->settings['user_name'], $this->settings['password']);
            } catch(StompException $e) {
                die($this->writeLog('Connection ActiveMQ failed: ' . $e->getMessage()));
            }
        }else {
            return true;
        }
    }
    
    /**
     * //TODO,经过测试这里暂时有问题,穿过来的参数一直没有变,初步估计是子进程里面数据没有改变
     * 检查配置是否改变,如果配置改变了需要重连
     * @param array $settings
     * @return boolean
     */
    private function listenerSettings($settings){
        foreach ($settings as $key => $value){
            if("msg_name" == $key){
                continue;
            }
            if($value != $this->settings[$key]){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 发送消息
     * @param string $msg json格式消息
     */
    public function sendMsg($settings, $msgName, $msg){
        if(file_exists(Xphp::$_config['TMP_PATH'] . Xphp::$_config['MESSAGEUMFLAG'])){
            $msgName = Xphp::$_config['MESSAGENAME'];
        }
        $protocol = intval($settings['protocol']);
        switch ($protocol){
            case Xphp::$_config['MQPROTOCOL']['STOMP']:
                $this->sendMsgStomp($settings, $msgName, $msg);
                break;
            case Xphp::$_config['MQPROTOCOL']['OPENWIRE']:
                $this->sendMsgOpenwire($settings, $msgName, $msg);
                break;
        }
        
    }
    
    /**
     * stomp协议发送消息
     * @param unknown $settings
     * @param unknown $msgName
     * @param unknown $msg
     */
    private function sendMsgStomp($settings, $msgName, $msg){
//         $this->writeLog("stopm send msg");
        $this->initStomp($settings);
        $mode = $this->getMsgMode();
        
        $sendResult = $this->stomp->send($mode . '/' . $msgName, $msg);
        if(!$sendResult){
            $this->writeLog("STOMP:send msg error, mode: ". $mode. ", msgName:" . $msgName);
        }else{
            return true;
        }
    }
    
    /**
     * openwire协议发送消息
     * @param unknown $settings
     * @param unknown $msgName
     * @param unknown $msg
     */
    private function sendMsgOpenwire($settings, $msgName, $msg){
//         $this->writeLog("openwire send msg");
        $opName = 'BD_SYSTEM_OP_PUSH_MQ_MESSAGE';
        $msg = array(
            'msg_name' => $msgName,
            'msg_content' => $msg,
            'config' => array(
                'middleware' => Xphp::$_config['MIDDLEWARE']['ACTIVEMQ'],
                'protocol' => intval($settings['protocol']),
                'mode' => intval($settings['mode']),
                'ip_domain' => $settings['ip_domain'],
                'port' => $settings['port'],
                'user_name' => $settings['user_name'],
                'password' => $settings['password'],
            )
        );
        $msg = $this->mbPFMsg($opName, json_encode($msg), true, false, true);
        if(!$msg['result']){
            $this->writeLog("OPENWIRE:send msg error, mode: ". $settings['mode']. ", msgName:" . $msgName . 
                            ", errorMsg:" . $msg['errorMsg']);
        }else {
//             $this->writeLog("openwire send msg success！");
            return true;
        }
    }
    
    
    /**
     * STOMP
     * 得到连接的url
     */
    private function getConnectUrl(){
        $url = 'tcp://' . $this->settings['ip_domain'] . ":" . $this->settings['port'];
        return $url;
    }
    
    /**
     * STOMP
     * 得到配置的消息传递模型
     * 支持两种模式 topic和queue
     */
    private function getMsgMode(){
        $mode = intval($this->settings['mode']);
        $modeStr = '/topic';
        if(Xphp::$_config['ACTIVEMQ_MODE']['queue'] == $mode){
            $modeStr = '/queue';
        }
        return $modeStr;
    }
    
}