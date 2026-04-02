<?php
/*******************************************
 ** 操作控制器，统一调用，如果方法不全，请通知管理员添加~！！
 ** 包含数据库操作 [db]打头
 ** 消息发送处理 [mb]打头到后台 [mu]打头到前台
 ** 日志处理 [log]打头
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-4-16 10:18:40
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
require_once RUN_PATH.'backend_monitor/VPDO.class.php';
class OPHandler{
    /************************************************************************************
     * 数据库 =>start
     */ 
	
	protected $VPDO;
	
	function __construct(){
	    $this->writeLog("reconnect one!!!!!!!!!!!!!!!!!!!!");
// 	    $this->VPDO = new VPDO();
		$this->reconnectVDPD();
	}
	
	public function __destruct(){
	    $this->VPDO = null;
	}
	
	
	/**
	 * 自动重连数据库对象
	 */
	public function reconnectVDPD(){
	    $this->writeLog("reconnect start!!!!!!!!!!!!!!!!!!!!!!!!!" .date('Y-m-d H:i:s'));
	    while (true){
	        if(!empty($this->VPDO)){
	            $this->writeLog("sleep !!!");
	            sleep(5);
	            break;
	        }
	        $this->writeLog("reconnect mysql!!!!!!!!!!!!!!!!!!!!!!!!!");
	        $this->VPDO = new VPDO();
	        break;
	    }
// 		if(empty($this->VPDO)){
// 			sleep(60);
// 			$this->writeLog(" end end!!!!!!!!!!!!!!!!!!!!!!!!!".date('Y-m-d H:i:s'));
// 			$this->reconnectVDPD();
// 		}
	}
    
    /**
     * 数据库带参数查询，适用于select
     * @param string $sql  SQL语句,如"select a from b where c = ?"
     * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
     * @return array()
     */
    public function dbSelect($sql, $param = array()) {
    	if(empty($this->VPDO)){
    		$this->reconnectVDPD();
    	}  
        return $this->VPDO->sqlQuery($sql, $param, true);
    }
    
    /**
     * 数据库带参数，适用于insert,delete
     * @param string $sql  SQL语句,如"select a from b where c = ?"
     * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
     * @return int 受影响的行数
     */
    public function dbQuery($sql, $param = array()){
    	if(empty($this->VPDO)){
    		$this->reconnectVDPD();
    	} 
        return $this->VPDO->exec($sql, $param, false);
    }
    
    /**
     * 查询sql执行结果,成功或失败,适用于insert,update或delete,有的地方会有updates受影响为0行,但是结果是成功的,方便处理
     * @param string $sql
     * @return boolean 返回成功和失败
     */
    public function dbExec($sql, $param = array()){
        if(empty($this->VPDO)){
            $this->reconnectVDPD();
        } 
        $result = $this->dbQuery($sql, $param);
        if(false === $result){
            //这是执行出错
            return false;
        }
        //这是执行成功  返回受影响的行数为0或更多都任务成功
        return true;
    }
    
    /**
     * 写日志,只用于WEB写文件,调试用
     * @param string $msg   日志消息,可以是自定义消息,|error日志可以是错误定义名字或错误号
     * @param int $type  消息类型           info/notice/warning/error       1/2/3/4
     */
    public function writeLog($msg, $type = 1){
    	$str= file_get_contents('/etc/backup_system/common_server.conf.xml');
    	//获取log_dir
    	$list = explode("<log_dir>", $str);
    	$listOne = explode("</log_dir>", $list[1]);
    	$logDir = $listOne[0];
    	if(!file_exists($logDir."/web")){
    	    $cmd = "mkdir ".$logDir."/web";
    		exec($cmd);
    	}
    	
    	$file = $logDir."/web/log_monitor-" .date("Y-m-d") . '.log';
    	if(!file_exists($file)){
    		$addFile = "touch ".$file;
    		exec($addFile);
    		$this->checkRecycleLog($logDir);
    	}
    	$msg = $this->groupMsg($msg, $type);
    	$count = file_put_contents($file, $msg, FILE_APPEND | LOCK_EX);
    	if($count > 0){
    		return true;
    	}
    	return false;
    }
    
    private function checkRecycleLog($dir){
    	$cmd = "ls ".$dir."/web/log_monitor-*";
    	exec($cmd, $output);
    	$count = count($output);
    	if($count > 15){
    		$row = $count - 15;
    		for ($i=0;$i<$row;$i++){
    			if(file_exists($output[$i])){
    				$deletCmd = "rm -rf ".$output[$i];
    				exec($deletCmd);
    			}
    		}
    	}
    	sleep(60);
    	return true;
    	
    }
    
    /**
     * 组合消息
     * @param string $msg
     * @param int $type
     */
    private function groupMsg($msg, $type){
    	$logInfo = array(
            //是否开启日志,开启日志后不写入INFO,NOTICE,WARNING日志,ERROR日志会照常写入
            'DEBUG' => true,
            //日志路径
            'PATH' => '/var/log/vinchin/web/log',
            //日志等级  
            'INFO' => 1,
            'NOTICE' => 2,
            'WARNING' => 3,
            'ERROR' => 4,
        );
        
    	$logMsg = '';
    	$debugInfo = debug_backtrace();
    	$logLevel = array_search($type, $logInfo, true);
    	$i = 0;
    	foreach ($debugInfo as $value){
    		if("writeLog" == $value['function']){
    			break;
    		}
    		$i++;
    	}
//     	if($type == $logInfo['ERROR']){
//     		$utils = Xphp::instance('Utils');
//     		$msg = $utils->getErrorDes($msg);
//     	}
    	$logMsg .= date("Y-m-d H:i:s")." [" . $logLevel . "] ";
    	$logMsg .= $msg." : file: " . $debugInfo[$i]['file'] . " on line ". $debugInfo[$i]['line'] . ", ";
    	$logMsg .=  "class: " . $debugInfo[$i+1]['class'] . ", function: ". $debugInfo[$i+1]['function'] . "." . PHP_EOL;
    	return $logMsg;
    }
    
    
    /**
     * 辅助函数 =>end
     *************************************************************************************/
}

?>