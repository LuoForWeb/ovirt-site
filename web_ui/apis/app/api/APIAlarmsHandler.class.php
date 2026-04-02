<?php
/******************************************* 
** 告警处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APIAlarmsHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/alarms/jobs/lists" => array(
            'GET' => 'getJobsAlarmsLists'
        ),
    
        "/alarms/jobs" => array(
        	'POST' => 'responseJobsAlarms',
            'DELETE' => 'deleteJobsAlarms'
        ),
    
        "/alarms/systems/lists" => array(
            'GET' => 'getSystemsAlarmsLists'
        ),
    
        "/alarms/systems" => array(
        	'POST' => 'responseSystemsAlarms',
            'DELETE' => 'deleteSystemsAlarms'
        ),
        "/alarms/survey_notice" => array(
            'GET' => 'getSurveyNoticeInfo'
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 获取任务告警列表路由控制
     */
    protected function getJobsAlarmsLists(){
        //定义方法版本
        $version = array(
            "v1" => "getJobsAlarmsListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 响应任务告警路由控制
     */
    protected function responseJobsAlarms(){
    	//定义方法版本
    	$version = array(
    		"v1" => "responseJobsAlarmsV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }

    
    /**
     * 删除任务告警路由控制
     */
    protected function deleteJobsAlarms(){
        //定义方法版本
        $version = array(
            "v1" => "deleteJobsAlarmsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到系统告警列表路由控制
     */
    protected function getSystemsAlarmsLists(){
        //定义方法版本
        $version = array(
            "v1" => "getSystemsAlarmsListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 响应系统告警路由控制
     */
    protected function responseSystemsAlarms(){
    	//定义方法版本
    	$version = array(
    		"v1" => "responseSystemsAlarmsV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除系统告警路由控制
     */
    protected function deleteSystemsAlarms(){
        //定义方法版本
        $version = array(
            "v1" => "deleteSystemsAlarmsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }

    /**
     * 得到任务告警和系统告警通知信息
     */
    protected function getSurveyNoticeInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getSurveyNoticeInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }



    /**********getJobsAlarmsLists**********/
    private function getJobsAlarmsListsV1(){
        $begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	
    	$sql = "select task_alarm_id, task_uuid, submodule_type, solved_username, unix_timestamp(solved_time) solved_time, sms_send_flag, email_send_flag,
    			node_uuid, node_name, storage_name, task_name, task_type, alarm_level, description_key, task_log_path,
                description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code
                from bd_task_alarm limit ? , ?";
    	$sqlCount = "select count(task_alarm_id) as total from bd_task_alarm";
    	
    	$sqlParams = array($begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, array());
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$logHandler = Xphp::instance('APILogsHandler');
    	$utils = Xphp::instance('Utils');
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    			"job_uuid" => $d['task_uuid'],
    			"job_name" => $d['task_name'],
    			"job_type" => $d['task_type'],
    			"alarm_level" => intval($d['alarm_level']), 
    			"alarm_time" => $d['alarm_time'],
    			"description" => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'],
                     $d['error_code'], $d['description_key'], $d['description_param']),
    			"id" => intval($d['task_alarm_id']),
    			"hypervisor" => intval($d['submodule_type']),
    			"solved_flag" => intval($d['solved_flag']),
    			"solved_user" => $d['solved_username'],
    			"solved_time" => (string)$d['solved_time'],
    			"sms_notice" => $d['sms_send_flag'],
    			"mail_notice" => $d['email_send_flag'],
    			"node_uuid" => $d['node_uuid'],
    			"node_name" => $d['node_name'],
    			"storage_name" => $d['storage_name'],
    			"error_log" => $this->getTaskLogInfo($d['node_uuid'], $d['task_log_path'], $d['alarm_level']),
    			"details" => $this->getTaskVmDetails($d['task_uuid'])
    		);
    	}
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	
    	return $this->apiResponse(true, "API_CODE_ALARMS_GET_JOB_ALARM_LIST", $data);
    }
    
    /**********deleteJobsAlarms**********/
    private function deleteJobsAlarmsV1(){
        $id = $this->params['id'];
        $this->apiParamsCheck($id);
        $idArray = array();
        foreach ($id as $v){
        	//检查任务告警是否存在
        	$this->checkTaskAlarmExist($v);
            $idArray[] = intval($v);
        }
        $opName = 'BD_ALARM_OP_TASK_DELETE';
        $msg = array('id_list' => $idArray);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        return $this->apiResponse($mbResult['result'], 'API_CODE_ALARMS_DELETE_JOB_ALARM', array(), $mbResult);
    }
    
    /**********responseJobsAlarms**********/
    private function responseJobsAlarmsV1(){
    	$ids = implode(',', $this->params['id']);
    	$idArray = $this->params['id'];
    	foreach ($idArray as $v){
    		//检查任务告警是否存在
    		$this->checkTaskAlarmExist($v);
    	}
    	$nowDate = date('Y-m-d H:i:s');
    	$sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
    	where task_alarm_id in ($ids)";
    	$sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username']);
    	$result = $this->dbExec($sql, $sqlParams);
    	if($result){
    		$ptDes = include APP_PATH . 'platform/PFDescription.php';
    		$ext = array(
    			'alarmsolvedflag' => true,
    			'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['SET']],
    			'alarmsolveduser' => Xphp::$_user['username'],
    			'alarmsolvedtime' => $nowDate
    		);
    	}
    	return $this->apiResponse($result, "API_CODE_ALARMS_RESPONSE_JOB_ALARM", array(), $ext);
    }
    
    /**********getSystemsAlarmsLists**********/
    private function getSystemsAlarmsListsV1(){
        $begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	
    	$sql = "select system_alarm_id, alarm_level, solved_username, unix_timestamp(solved_time) solved_time, email_send_flag, sms_send_flag, description_key, description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code 
                from bd_system_alarm limit ? , ?";
    	$sqlCount = "select count(system_alarm_id) as total from bd_system_alarm";
    	
    	$sqlParams = array($begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount);
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$logHandler = Xphp::instance('APILogsHandler');
    	$utils = Xphp::instance('Utils');
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    			"alarm_level" => intval($d['alarm_level']),
    			"alarm_time" => $d['alarm_time'],
    			"description" => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'], 
                    $d['error_code'], $d['description_key'], $d['description_param']),
    			'id' => intval($d['system_alarm_id']), 
    			"solved_flag" => intval($d['solved_flag']),
    			"solved_user" => $d['solved_username'],
    			"solved_time" => (string)$d['solved_time'],
    			"sms_notice" => $d['sms_send_flag'],
    			"mail_notice" => $d['email_send_flag'],
    		);
    	}
    	
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	
    	return $this->apiResponse(true, "API_CODE_ALARMS_GET_SYSTEM_ALARM_LIST", $data);
    }
    
    /**********deleteSystemsAlarms**********/
    private function deleteSystemsAlarmsV1(){
         $id = $this->params['id'];
        $this->apiParamsCheck($id);
        $idArray = array();
        foreach ($id as $v){
        	//检查系统告警是否存在
        	$this->checkSystemAlarmExist($v);
            $idArray[] = intval($v);
        }
        $opName = 'BD_ALARM_OP_SYSTEM_DELETE';
        $msg = array('id_list' => $idArray);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        return $this->apiResponse($mbResult['result'], 'API_CODE_ALARMS_DELETE_SYSTEM_ALARM', array(), $mbResult);
    }

    /**********getSurveyNoticeInfo**********/
    private function getSurveyNoticeInfoV1(){
        //系统告警
        $sql = "select count(description_key) as total from bd_system_alarm where solved_flag = ?
            and alarm_level != ? ";
        $systemParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['NORMAL']);
        $dataSystem = $this->dbSelect($sql, $systemParams);
        $systemAlarm = intval($dataSystem[0]['total']);

        //任务告警 都显示自己的
        $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
        and alarm_level != ?";
        $jobParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['NORMAL']);
        //超级管理员admin获取所有任务告警数量，其他用户获取自己的任务告警数量
        if(Xphp::$_user['username'] != "admin" && Xphp::$_user['usertype'] != 3){
            $sql .= " and user_uuid = ?";
            $jobParams = array_merge($jobParams, array(Xphp::$_user['useruuid']));
        }

        
        $datajob = $this->dbSelect($sql, $jobParams);
        $jobAlarm = intval($datajob[0]['total']);

        $info = array(
            "system_alarm" => $systemAlarm,
            "job_alarm" => $jobAlarm,
            "alarm_total" => $jobAlarm + $systemAlarm,
        );

        return $this->apiResponse(true, "API_CODE_ALARMS_GET_UNSOLVED_ALARM_NOTICE", $info);
    }


    /**********responseSystemsAlarms**********/
    private function responseSystemsAlarmsV1(){
    	$ids = implode(',', $this->params['id']);
    	$idArray = $this->params['id'];
    	foreach ($idArray as $v){
    		//检查任务告警是否存在
    		$this->checkSystemAlarmExist($v);
    	}
    	$nowDate = date('Y-m-d H:i:s');
   		$sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ? 
                where system_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_RESPONSE'];
        if($result){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => true,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['SET']],
                'alarmsolveduser' => Xphp::$_user['username'],
                'alarmsolvedtime' => $nowDate
            );
        }
    	return $this->apiResponse($result, "API_CODE_ALARMS_RESPONSE_SYSTEM_ALARM", array(), $ext);
    }
    
    
    /**********************************其他工具方法************************************/
    /**
     * 转化时间戳为2012-12-12 12:12:12格式
     * @param unknown $timestamp
     * @return string
     */
    protected function parseDate($timestamp){
    	if(empty($timestamp)){
    		return "";
    	}
    	return date("Y-m-d H:i:s", $timestamp);
    }
    
    /**
     * 得到告警日志的信息
     * @param string $nodeuuid
     * @param string $path
     * @param int    $logLeve
     * @return string
     */
    private function getTaskLogInfo($nodeuuid, $path, $logLeve){
    	if(empty($path) || empty($nodeuuid) || empty($logLeve)) return '';
    	if(intval($logLeve) == Xphp::$_config['LOGLEVEL']['NORMAL']){
    		//如果是一般告警,没有日志
    		return '';
    	}
    	$opName = 'BD_SYSTEM_OP_READ_FILE';
    	$msg = array('file_path' => $path);
    	$msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
    	$fileContent = '';
    	if($msg['result']){
    		$fileContent = $msg['msg']['file_content'];
    	}else{
    		return $fileContent;
    	}
    	return $fileContent;
    }
    
    /**
     * 检查系统告警	id是否存在
     * @param int $id
     */
    private function checkSystemAlarmExist($id){
    	$sql="select description_key from bd_system_alarm where system_alarm_id = ?";
    	$data = $this->dbSelect($sql,array($id));
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_ALARMS_DELETE_SYSTEM_ALARM_ERROR');
    	}
    }
    
    /**
     * 检查任务日志id是否存在
     * @param int $id
     */
    private function checkTaskAlarmExist($id){
    	$sql="select description_key from bd_task_alarm where task_alarm_id = ?";
    	$data = $this->dbSelect($sql,array($id));
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_ALARMS_DELETE_JOB_ALARM_ERROR');
    	}
    }
    
    /**
     * 消息推送得到单个任务告警
     * @param string $id
     */
    public function getJobAlarmDetail($id){
    	$sql = "select task_uuid, submodule_type, solved_username, unix_timestamp(solved_time) solved_time, sms_send_flag, email_send_flag,
    			node_uuid, node_name, storage_name, task_name, task_type, alarm_level, description_key, task_log_path,
                description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code
                from bd_task_alarm where task_alarm_id = ?";
    	 
    	$data = $this->dbSelect($sql, array($id));
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$logHandler = Xphp::instance('APILogsHandler');
    	$utils = Xphp::instance('Utils');
    	$info = array(
    		"job_name" => $data[0]['task_name'],
    		"job_uuid" => $data[0]['task_uuid'],
    		"job_type" => $data[0]['task_type'],
    		"alarm_level" => intval($data[0]['alarm_level']),
    		"alarm_time" => $data[0]['alarm_time'],
    		"description" => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'],
    				$data[0]['error_code'], $data[0]['description_key'], $data[0]['description_param']),
    		"id" => $id,
    		"hypervisor" => intval($data[0]['submodule_type']),
    		"solved_flag" => intval($data[0]['solved_flag']),
    		"solved_user" => $data[0]['solved_username'],
    		"solved_time" => (string)$data[0]['solved_time'],
    		"sms_notice" => $data[0]['sms_send_flag'],
    		"mail_notice" => $data[0]['email_send_flag'],
    		"node_uuid" => $data[0]['node_uuid'],
    		"node_name" => $data[0]['node_name'],
    		"storage_name" => $data[0]['storage_name'],
    	    "error_log" => $this->getTaskLogInfo($data[0]['node_uuid'], $data[0]['task_log_path'], $data[0]['alarm_level'])
    	);
    	return $info;
    }
    
    /**
     * 消息推送得到单个系统告警
     * @param string $id
     */
    public function getSystemAlarmDetail($id){
    	$sql = "select alarm_level, solved_username, unix_timestamp(solved_time) solved_time, email_send_flag, sms_send_flag, description_key, description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code
                from bd_system_alarm where system_alarm_id = ?";
    	$data = $this->dbSelect($sql, array($id));
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$logHandler = Xphp::instance('APILogsHandler');
    	$utils = Xphp::instance('Utils');
    	$info = array(
    		"alarm_level" => intval($data[0]['alarm_level']),
    		"alarm_time" => $data[0]['alarm_time'],
    		"description" => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'],
    				$data[0]['error_code'], $data[0]['description_key'], $data[0]['description_param']),
    		'id' => $id,
    		"solved_flag" => intval($data[0]['solved_flag']),
    		"solved_user" => $data[0]['solved_username'],
    		"solved_time" => (string)$data[0]['solved_time'],
    		"sms_notice" => $data[0]['sms_send_flag'],
    		"mail_notice" => $data[0]['email_send_flag'],
    	);
    	return $info;
    }
    
    
    /**
     * 得到任务告警的虚拟机信息
     * @param string $taskuuid
     */
    private function getTaskVmDetails($taskuuid){
    	$sql = "select vcenter_uuid, vm_uuid, vm_name, version, host_uuid, dir_path from vm_machine_list where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$info =array();
    	foreach ($data as $d){
    		$info[] = array(
    			'vcenter_uuid' => $d['vcenter_uuid'],
    			'vm_uuid' => $d['vm_uuid'],
    			'vm_name' => $d['vm_name'],
    			'version' => $d['version'],
    			'host_uuid' => $d['host_uuid'],
    			'dir_path' => $d['dir_path']	
    		);
    	}
    	
    	return $info;
    }
}