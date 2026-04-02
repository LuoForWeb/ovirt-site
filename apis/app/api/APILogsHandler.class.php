<?php
/******************************************* 
** 日志处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APILogsHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/logs/systems/lists" => array(
            'GET' => 'getLogSystemsLists'
        ),
    
        "/logs/systems" => array(
            'DELETE' => 'deleteSystemsLogs'
        ),
        
        "/logs/jobs/lists" => array(
            'GET' => 'getLogJobsLists'
        ),
        
        "/logs/jobs" => array(
            'DELETE' => 'deleteJobsLogs'
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 获取系统操作日志路由控制
     */
    protected function getLogSystemsLists(){
        //定义方法版本
        $version = array(
            "v1" => "getLogSystemsListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除系统操作日志路由控制
     */
    protected function deleteSystemsLogs(){
        //定义方法版本
        $version = array(
            "v1" => "deleteSystemsLogsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取系统任务日志路由控制
     */
    protected function getLogJobsLists(){
        //定义方法版本
        $version = array(
            "v1" => "getLogJobsListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除系统任务日志路由控制
     */
    protected function deleteJobsLogs(){
        //定义方法版本
        $version = array(
            "v1" => "deleteJobsLogsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********getLogSystemsLists**********/
    private function getLogSystemsListsV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	
    	$sql = "select id, user_uuid, user_name, agent_name, error_code, unix_timestamp(op_time) op_time, description_key, 
                description_param, log_level from bd_system_log limit ? , ?";
    	$sqlCount = "select count(id) as total from bd_system_log";
    	
    	$sqlParams = array($begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, array());
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    		    "id" => intval($d['id']),
    			"user_name" => $d['user_name'],
    			"op_time" => $d['op_time'],
    			"log_level" => $d['log_level'],
    			"description" => $this->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'], $d['error_code'], 
                    $d['description_key'], $d['description_param']),
    		);
    	}
    	
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	
    	return $this->apiResponse(true, "API_CODE_LOGS_GET_SYSTEM_LOG_LIST", $data);
    }
    
    /**********deleteSystemsLogs**********/
    private function deleteSystemsLogsV1(){
        $id = $this->params['id'];
        $this->apiParamsCheck($id);
        
        $idArray = array();
        foreach ($id as $v){
        	//检查系统日志是否存在
        	$this->checkSystemlogExist($v);
            $idArray[] = intval($v);
        }
        $opName = 'BD_LOG_OP_SYSTEM_DELETE';
        $msg = array('id_list' => $idArray);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_LOGS_DELETE_SYSTEMLOG', array(), $mbResult);
    }
    
    /**********getLogJobsLists**********/
    private function getLogJobsListsV1(){
        $begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	
    	$sql = "select id, task_uuid, task_name, task_type, user_name, agent_name, module_type, submodule_type, 
                    error_code, unix_timestamp(op_time) op_time, description_key, description_param, log_level 
                from bd_task_log where running_flag = ? limit ? , ?";
    	$sqlCount = "select count(id) as total from bd_task_log where running_flag = ?";
    	
    	$sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['FLAG']['UNSET']));
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    		    "id" => intval($d['id']),
    			"job_uuid" => $d['task_uuid'],
    			"job_name" => $d['task_name'],
    			"hypervisor" => $d['submodule_type'],
    			"job_type" => $d['task_type'],
    			"user_name" => $d['user_name'], 
    			"op_time" => $d['op_time'],
    			"log_level" => $d['log_level'],
    			"description" => $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'], 
                    $d['description_key'], $d['description_param']),
    		);
    	}
    	
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	
    	return $this->apiResponse(true, "API_CODE_LOGS_GET_JOB_LOG_LIST", $data);
    }
    
    /**********deleteJobsLogs**********/
    private function deleteJobsLogsV1(){
        $id = $this->params['id'];
        $this->apiParamsCheck($id);
        $idArray = array();
        foreach ($id as $v){
        	//检查任务日志是否存在
        	$this->checkTasklogExist($v);
            $idArray[] = intval($v);
        }
        $opName = 'BD_LOG_OP_TASK_DELETE';
        $msg = array('id_list' => $idArray);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        return $this->apiResponse($mbResult['result'], 'API_CODE_LOGS_DELETE_JOBLOG', array(), $mbResult);
    }
    
    
    
    /*************************************非直接调用接口,如工具类等↓*******************************************/
      /**
     * 得到日志描述信息
     * @param int $logType      日志类型   系统日志2/任务日志1
     * @param int $errorCode    错误码
     * @param string $desription    描述
     * @param string $descriptionParam  描述参数
     * @return string
     */
    public function getLogDesription($logType, $errorCode, $desription, $descriptionParam, $logLevel = null){
    	$confDes = $this->getLogConf($logType);
    	$desStr = $confDes[$desription];
    	if($errorCode){
    		$errorConf = include ROOT_PATH . 'api/xphp/conf/error.php';
    		$errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
    		$desStr .= ",[". '"#' . $errorCode . "]" . $errorStr;
    	}
    	if($descriptionParam){
    		$param = json_decode($descriptionParam, true);
    		$desArr = explode("%s", $desStr);
    		$desStr = "";
    		foreach ($desArr as $k => $v){
    			$desStr .= $v . $this->getEachParamsDes($param[$k]);
    		}
    	}
    	return $desStr;
    }
    
     /**
     * 根据类型得到日志配置文件
     * @param int $logType  日志类型   系统日志2/任务日志1
     * @return array
     */
    private function getLogConf($logType){
    	if($logType == Xphp::$_config['LOGTYPE']['SYSTEM']){
    		return include ROOT_PATH . 'api/xphp/conf/log_system.php';
    	}elseif ($logType == Xphp::$_config['LOGTYPE']['TASK']){
    		return include ROOT_PATH . 'api/xphp/conf/log_task.php';
    	}
    }
    
    
     /**
     * 得到每一项参数的描述
     * @param string $eachParams
     * @param boolean $classShowFlag  是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    private function getEachParamsDes($eachParams, $classShowFlag = true){
    	if(empty($eachParams)){
    		return "";
    	}
    	$des = "";
    	$arr = explode(":", $eachParams, 2);
    	switch($arr[0]){
    		case "S":
    				$des = $arr[1];
    			break;
    		case "module_type":
    			$des = $this->logModuleTypeDes($arr[1]);
    			break;
    		case "backup_mode":
    			$des = $this->logBackupModeDes($arr[1]);
    			break;
    		default:
    			break;
    	}
    	return $des;
    }
    
    /**
     * 得到日志模块描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logModuleTypeDes($index){
    	$desConf = include APP_PATH. 'platform/PFDescription.php';
    	return $desConf['MODULE_TYPE_DES'][intval($index)];
    }
    
    /**
     * 得到日志备份模式描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logBackupModeDes($index){
    	$desConf = include APP_PATH. 'platform/PFDescription.php';
    	return $desConf['BACKUP_MODE_DES'][intval($index)];
    }
    
    /**
     * 检查系统日志id是否存在
     * @param int $id
     */
    private function checkSystemlogExist($id){
    	$sql="select description_key from bd_system_log where id = ?";
        	$data = $this->dbSelect($sql,array($id));
        	if(!$data){
        		 return $this->apiResponse(false, 'API_CODE_LOGS_DELETE_SYSTEMLOG_ERROR');
        	}
    }
    
    /**
     * 检查任务日志id是否存在
     * @param int $id
     */
    private function checkTasklogExist($id){
    	$sql="select description_key from bd_task_log where id = ?";
    	$data = $this->dbSelect($sql,array($id));
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_LOGS_DELETE_TASKLOG_ERROR');
    	}
    }
    
}