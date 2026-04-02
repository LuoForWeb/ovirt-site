<?php
/******************************************* 
** 告警管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2016-06-25 下午14:43:12 
** @version      1.0.0 
** @copyright    Copyright 2015-2016 vinchin.com 
********************************************/
class AlarmHandler extends OPHandler{
    
    /**
     * 获取系统告警表格信息
     * @param unknown $params
     */
    public function getSystemAlarms($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $search = $params['search'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $level = $search['level'];
        $sortArr = array('', '', 'alarm_level', 'alarm_time', '', 'error_code', 'solved_flag', '');
        
        $sql = "select system_alarm_id, alarm_level, description_key, description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code 
                from bd_system_alarm ";
        $sqlCount = "select count(system_alarm_id) as total from bd_system_alarm ";
		
        $accurateFlag = $params['accurateFlag'];
        if(!empty($level) && !$accurateFlag ){
            $sql .= " where alarm_level = $level ";
            $sqlCount .= " where alarm_level = $level ";
        }
        if($accurateFlag){
        	$search = $params['search'];
        	$alarmLevel = intval($search['alarmLevel']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	
        	$sql .= " where (alarm_level = ". $alarmLevel ." or ". $alarmLevel ." = '')";
        	$sqlCount .= " where (alarm_level = ". $alarmLevel ." or ". $alarmLevel ." = '')";
        	
        	//如果填了日志时间范围查询
        	if($startTime && $endTime){
        		$sql .= " and alarm_time between '". $startTime ."' and '". $endTime ."' ";
        		$sqlCount .= " and alarm_time between '". $startTime ."' and '". $endTime ."' ";
        	}
        }
        
        $sqlParams = array($start, $length);
        $sqlCountParams = array();
        
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
       	$data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        
        $records = array("data" => array());
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $logHandler = Xphp::instance('LogHandler');
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['system_alarm_id'] . '">',
                ++$start,
                intval($d['alarm_level']),
                $this->parseDate($d['alarm_time']),
                $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'], 
                    $d['error_code'], $d['description_key'], $d['description_param']),
                $utils->parseFlagToBool($d['solved_flag']),
                array(
                    'id' => intval($d['system_alarm_id']), 
                    'leveldes' => $ptDes['ALARM_LEVEL_DES'][intval($d['alarm_level'])],
                    'solveddes' => $ptDes['ALARM_RESPOND_DES'][intval($d['solved_flag'])],
                ),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到任务告警信息
     * @param unknown $params
     */
    public function getTaskAlarms($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $search = $params['search'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'task_name', 'task_type', 'alarm_level', 'alarm_time', '', 'solved_flag', '');
        $level = $search['level'];
        $name = $search['name'];
        $accurateFlag = $params['accurateFlag'];
        if($accurateFlag){
        	$name = $params['search']['taskName'];
        }
        $sql = "select task_alarm_id, task_name, task_type, alarm_level, description_key, 
                description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code
                from bd_task_alarm where task_name like '%" . $name . "%' ";
        $sqlCount = "select count(task_alarm_id) as total from bd_task_alarm where task_name like '%" . $name . "%' ";
        
        if(!empty($level) && !$accurateFlag){
            $sql .= " and alarm_level = $level ";
            $sqlCount .= " and alarm_level = $level ";
        }
        
        if($accurateFlag){
        	$search = $params['search'];
        	$hypervisor = intval($search['hypervisor']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
        	$taskName = $search['taskName'];
        	$alarmLevel = intval($search['alarmLevel']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	$nodeuuid = $search['nodeValue'];

        	if($moduleType && $moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
        		$sql .= " and (submodule_type = ". $hypervisor ." or ". $hypervisor . " = '') ";
        		$sqlCount .= " and (submodule_type = ". $hypervisor ." or ". $hypervisor . " = '') ";
         	}
        	$sql .= " and (task_type = ". $taskType ." or ". $taskType ." = '') and
        			(module_type = ". $moduleType ." or ". $moduleType ." = '') and
        				 (alarm_level = ". $alarmLevel ." or ". $alarmLevel ." = '') ";
        	$sqlCount .= " and (task_type = ". $taskType ." or ". $taskType ." = '') and
        			(module_type = ". $moduleType ." or ". $moduleType ." = '') and
        				 (alarm_level = ". $alarmLevel ." or ". $alarmLevel ." = '') ";

        	if($nodeuuid){
        		$sql .= " and (node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '') ";
        		$sqlCount .= " and (node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '') ";
        	}
        	
        	//如果填了日志时间范围查询
        	if($startTime && $endTime){
        		$sql .= " and alarm_time between '". $startTime ."' and '". $endTime ."' ";
        		$sqlCount .= " and alarm_time between '". $startTime ."' and '". $endTime ."' ";
        	}
        }
        
        $userType = Xphp::$_user['usertype'];
        $confUserType = Xphp::$_config['USERTYPE'];
        if($userType == $confUserType['operator']){
            $sql .= " and user_uuid = ? ";
            $sqlCount .= " and user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array(Xphp::$_user['useruuid']);
        }else{
            $sqlParams = array($start, $length);
            $sqlCountParams = array();
        }
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        
        $records = array("data" => array());
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $logHandler = Xphp::instance('LogHandler');
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['task_alarm_id'] . '">',
                ++$start,
                $d['task_name'],
                $ptDes['TASKTYPEDES'][intval($d['task_type'])],
                intval($d['alarm_level']),
                $this->parseDate($d['alarm_time']),
                $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'],
                    $d['error_code'], $d['description_key'], $d['description_param']),
                $utils->parseFlagToBool($d['solved_flag']),
                array(
                    'id' => intval($d['task_alarm_id']),
                    'leveldes' => $ptDes['ALARM_LEVEL_DES'][intval($d['alarm_level'])],
                    'solveddes' => $ptDes['ALARM_RESPOND_DES'][intval($d['solved_flag'])],
                ),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到系统告警详情
     * @param unknown $params
     * @return string
     */
    public function getSystemAlarmDetails($params){
        $id = $params['id'];
        //打开直接标记为已经响应
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where system_alarm_id = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username'], $id);
        $result = $this->dbExec($sql, $sqlParams);
        //获取数据
        $sql = "select system_alarm_id, alarm_level, description_key, description_param, alarm_time, 
                solved_flag, solved_time, solved_username, email_send_flag, sms_send_flag, error_code 
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $info = array();
        if(data){
            $utils = Xphp::instance('Utils');
            $logHandler = Xphp::instance('LogHandler');
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $info = array(
                'alarmid' => $id,
                'alarmlevel' => $ptDes['ALARM_LEVEL_DES'][intval($data[0]['alarm_level'])],
                'alarmlevelflag' => intval($data[0]['alarm_level']),
                'alarmtime' => $data[0]['alarm_time'],
                'alarmcontent' => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'], 
                    $data[0]['error_code'], $data[0]['description_key'], $data[0]['description_param']),
                'alarmsolvedflag' => $utils->parseFlagToBool($data[0]['solved_flag']),
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][intval($data[0]['solved_flag'])],
                'alarmsolveduser' => $data[0]['solved_username'],
                'alarmsolvedtime' => $data[0]['solved_time'],
                'alarmemailflag' => $utils->parseFlagToBool($data[0]['email_send_flag']),
                'alarmsmsflag' => $utils->parseFlagToBool($data[0]['sms_send_flag']),
                'alarmemail' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['email_send_flag'])],
                'alarmsms' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['sms_send_flag'])],
            );
        }
        return json_encode($info);
    }
    
    /**
     * 得到任务告警详情
     * @param unknown $params
     */
    public function getTaskAlarmDetails($params){
        $id = $params['id'];
        //打开详情直接标记为已响应
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where task_alarm_id = ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username'], $id);
        $result = $this->dbExec($sql, $sqlParams);
        //获取数据
        $sql = "select task_alarm_id, alarm_level, description_key, description_param, alarm_time, 
                task_name, task_type, module_type, submodule_type, node_uuid, node_name, storage_name, 
                solved_flag, solved_time, solved_username, email_send_flag, sms_send_flag, error_code, task_log_path 
                from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $info = array();
        if(data){
            $utils = Xphp::instance('Utils');
            $logHandler = Xphp::instance('LogHandler');
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $info = array(
                'alarmid' => $id,
                'alarmlevel' => $ptDes['ALARM_LEVEL_DES'][intval($data[0]['alarm_level'])],
                'alarmlevelflag' => intval($data[0]['alarm_level']),
                'alarmtime' => $data[0]['alarm_time'],
                'alarmcontent' => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'],
                    $data[0]['error_code'], $data[0]['description_key'], $data[0]['description_param']),
                'alarmsolvedflag' => $utils->parseFlagToBool($data[0]['solved_flag']),
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][intval($data[0]['solved_flag'])],
                'alarmsolveduser' => $data[0]['solved_username'],
                'alarmsolvedtime' => $data[0]['solved_time'],
                'alarmemailflag' => $utils->parseFlagToBool($data[0]['email_send_flag']),
                'alarmsmsflag' => $utils->parseFlagToBool($data[0]['sms_send_flag']),
                'alarmemail' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['email_send_flag'])],
                'alarmsms' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['sms_send_flag'])],
                
                'taskname' => $data[0]['task_name'],
                'taskmodule' => $logHandler->getModuleTypeDes($data[0]['module_type'], $data[0]['submodule_type']),
                'tasktype' => $ptDes['TASKTYPEDES'][intval($data[0]['task_type'])],
                'tasknode' => $data[0]['node_name'],
                'taskstorage' => empty($data[0]['storage_name']) ? Xphp::$_config['NULLSPACE'] : $data[0]['storage_name'],
            );
        }
        return json_encode($info);
    }
    
    /**
     * 得到任务日志信息
     * @param unknown $params
     */
    public function getTaskAlarmDetailsLogs($params){
        $id = $params['id'];
        $sql = "select node_uuid, task_log_path, alarm_level from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $info = array();
        if(data){
            $info = array(
                'tasklog' => $this->getTaskLogInfo($data[0]['node_uuid'], $data[0]['task_log_path'], $data[0]['alarm_level']),
            );
        }
        return $this->muOpResult(true, '', '', '', '', $info);
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
            $fileContent = str_replace("\n", "<br><br>", $msg['msg']['file_content']);
        }else{
            $errorMsg = $this->muOpResult(false, Xphp::$_lang['WEB_ALARM_READ_LOG_INFO'], '', 'error', $msg['errorCode']);
            exit($errorMsg);
        }
        return $fileContent;
    }
    
    /**
     * 下载任务告警日志
     * @param unknown $params
     */
    public function downLoadTaskLog($params){
        $id = $params['id'];
        if(empty($id)) return false;
        $sql = "select node_uuid, task_log_path, task_name, alarm_time from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        if(empty($data)) return false;
        $path = $data[0]['task_log_path'];
        $nodeuuid = $data[0]['node_uuid'];
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $fileContent = '';
        if($msg['result']){
            $fileContent = str_replace("\n", "\r\n", $msg['msg']['file_content']);
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($fileContent));
            Header("Content-Disposition: attachment; filename=". $data[0]['task_name'] . "_" . $data[0]['alarm_time'] . ".txt");
            return $fileContent;
        }else{
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($fileContent));
            Header("Content-Disposition: attachment; filename=". $data[0]['task_name'] . "_" . $data[0]['alarm_time'] . ".txt");
            return $fileContent;
        }
        
    }
    
    /**
     * 删除系统告警
     * @param unknown $params
     */
    public function deleteSystemAlarm($params){
        $id = $params['id'];
        $this->paramsCheck($id);
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }
        $opName = 'BD_ALARM_OP_SYSTEM_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }
    
    /**
     * 删除任务告警
     */
    public function deleteTaskAlarm($params){
        $id = $params['id'];
        $this->paramsCheck($id);
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }
        $opName = 'BD_ALARM_OP_TASK_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }
    
    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName
     * @param json $msg
     * @return string
     */
    private function unifyMsg($opName, $jsonMsg){
        $mbResult = $this->mbPFMsg($opName, $jsonMsg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 处理系统告警,标记为响应
     * @param unknown $params
     */
    public function solvedSystemAlarm($params){
        $ids = implode(',', $params['id']);
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
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }
    
    /**
     * 处理系统告警,标记为未响应
     * @param unknown $params
     */
    public function notSolvedSystemAlarm($params){
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where system_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_NOT_RESPONSE'];
        if($result){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => false,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['UNSET']],
            );
        }
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }
    
    /**
     * 处理任务告警,标记为已响应
     * @param unknown $parmas
     */
    public function solvedTaskAlarm($params){
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
                where task_alarm_id in ($ids)";
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
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }
    
    /**
     * 处理任务告警,标记为未响应
     * @param unknown $parmas
     */
    public function notSolvedTaskAlarm($params){
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where task_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_NOT_RESPONSE'];
        if($result){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => false,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['UNSET']],
            );
        }
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }
    
    /**
     * 得到灾备中心告警信息4.0 暂时保留
     * TODO
     * @param unknown $params
     */
    public function getSurveyAlarmInfo($params){
        $info = array(
            "task" => array(
                "warn" => 0,
                "error" => 0
            ),
            "system" => array(
                "warn" => 0,
                "error" => 0
            )
        );
        //系统告警
        $sql = "select count(description_key) as total from bd_system_alarm where solved_flag = ?
            and alarm_level = ? ";
        $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
        $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $info['system']['warn'] = intval($dataWarn[0]['total']);
        $info['system']['error'] = intval($dataError[0]['total']);
        
        //任务告警
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            //如果是操作员,获取自己的任务告警
            $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
            and alarm_level = ? and user_uuid = ?";
            $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN'], Xphp::$_user['useruuid']);
            $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR'], Xphp::$_user['useruuid']);
            //如果是操作员 不获取系统告警
            $info['system']['warn'] = 0;
            $info['system']['error'] = 0;
        }else{
            //其他类型用户,获取系统所有的任务告警
            $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
            and alarm_level = ? ";
            $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
            $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        }
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $info['task']['warn'] = intval($dataWarn[0]['total']);
        $info['task']['error'] = intval($dataError[0]['total']);
        
        $info['vcenter'] = 0;
        $info['storage'] = 0;
        $info['lisence'] = 0;
        //检测系统是否授权
        $systemHandler = Xphp::instance('SystemHandler');
        $status = $systemHandler->getSystemAuthorizationStatus();
        if($status != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            $info['lisence'] = 1;
        }
        //检测是否添加了存储设备
        $sql = "select storage_id from bd_storage_resource";
        $count = $this->dbQuery($sql);
        $info['storage'] = $count > 0 ? 0 : 1;
        //检测是否添加了vcenter
        $sql = "select vcenter_id from vm_vcenter";
        $count = $this->dbQuery($sql);
        $info['vcenter'] =$count > 0 ? 0 : 1;
        
        return json_encode($info);
    }
    
    
    /**
     * 得到首页通知信息
     * 左侧菜单提示
     * 顶部消息通知
     * @param unknown $params
     */
    public function getSurveyNoticeInfo($params){
        //系统告警
        $sql = "select count(description_key) as total from bd_system_alarm where solved_flag = ?
            and alarm_level = ? ";
        $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
        $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $systemWarn = intval($dataWarn[0]['total']);
        $systemError = intval($dataError[0]['total']);
        
        //任务告警
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            //如果是操作员,获取自己的任务告警
            $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
            and alarm_level = ? and user_uuid = ?";
            $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN'], Xphp::$_user['useruuid']);
            $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR'], Xphp::$_user['useruuid']);
            //如果是操作员 不获取系统告警
            $info['system']['warn'] = 0;
            $info['system']['error'] = 0;
        }else{
            //其他类型用户,获取系统所有的任务告警
            $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
            and alarm_level = ? ";
            $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
            $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        }
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $taskWarn = intval($dataWarn[0]['total']);
        $taskError = intval($dataError[0]['total']);
        
        //当前任务
        $sql = "select count(task_uuid) as total from bd_task where user_uuid = ? and delete_flag = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'], Xphp::$_config['FLAG']['UNSET']));
        $currentTask = intval($data[0]['total']);
        
        //历史任务
        $sql = "select count(task_uuid) as total from bd_history_task where user_name = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['username']));
        $historyTask = intval($data[0]['total']);
        
        $info = array(
            "alarm" => array(
                "system" => array(
                    "error" => $systemError,
                    "warn" => $systemWarn,
                ),
                "task" => array(
                    "error" => $taskError,
                    "warn" => $taskWarn,
                )
            ),
            "task" => array(
                "current" => $currentTask,
                "history" => $historyTask
            ),
            "vcenter" => 0,
            "storage" => 0,
            "lisence" => 0,
        );
        
        //检测系统是否授权
        $systemHandler = Xphp::instance('SystemHandler');
        $status = $systemHandler->getSystemAuthorizationStatus();
        if($status != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            $info['lisence'] = 1;
        }
        //检测是否添加了存储设备
        $sql = "select storage_id from bd_storage_resource";
        $count = $this->dbQuery($sql);
        $info['storage'] = $count > 0 ? 0 : 1;
        //检测是否添加了vcenter
        $sql = "select vcenter_id from vm_vcenter";
        $count = $this->dbQuery($sql);
        $info['vcenter'] =$count > 0 ? 0 : 1;
        
        $info['system'] = $info['lisence'];
        $info['resource'] = $info['vcenter'] + $info['storage'];
        
        $info = $this->checkUserPemission($info);
        return json_encode($info);
    }
    
    
    /**
     * 检查用户权限
     * 主要是检查用户是否有权限查看告警(任务告警/系统告警),任务(当前任务/历史任务)
     * @param array $info
     */
    private function checkUserPemission($info){
        $permission = $_SESSION['permission'];
        //检查任务,当前任务和历史任务
        if(!in_array('current_job', $permission)){
            $info['task']['current'] = 0;
        }
        if(!in_array('history_job', $permission)){
            $info['task']['history'] = 0;
        }
        
        //检查告警,任务告警和系统告警
        if(!in_array('task_alarm', $permission)){
            $info['alarm']['task']['error'] = 0;
            $info['alarm']['task']['warn'] = 0;
        }
        if(!in_array('system_alarm', $permission)){
            $info['alarm']['system']['error'] = 0;
            $info['alarm']['system']['warn'] = 0;
        }
        
        return $info;
    }
    
    
    
    
    /****************************************************************************
     * 下面代码是短信和邮件通知接口
     ****************************************************************************/
    /**
     * 发送短信和邮件接口(提供给后台调用)
     * @param array $params
     *  mode:1.邮件,2.短信
     *  type:1.任务,2.系统
     *  id:告警id
     */
    public function sendNotice($params){
        $emailFlag = $params['email'];
        $smsFlag = $params['sms'];
        $type = intval($params['type']);
        $id = intval($params['id']);
        $this->checkNoticeParams($emailFlag, $smsFlag, $type, $id);
        if($type == Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            //发送系统通知
            return $this->sendSystemNotice($emailFlag, $smsFlag, $id);
        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['TASK']){
            //发送任务通知
            return $this->sendTaskNotice($emailFlag, $smsFlag, $id);
        }
    }
    
    /**
     * 发送系统通知
     * @param int $mode
     * @param int $systemAlarmID
     */
    private function sendSystemNotice($emailFlag, $smsFlag, $systemAlarmID){
        $usersHandler = Xphp::instance('UsersHandler');
        $checkResult = $this->checkNoticeSetting($emailFlag, $smsFlag, Xphp::$_config['NOTICE_TYPE']['SYSTEM'], $systemAlarmID);
        $emailFlag = $checkResult['email'];
        $smsFlag = $checkResult['sms'];
        $type = Xphp::$_config['NOTICE_TYPE']['SYSTEM'];
        if($emailFlag == Xphp::$_config['FLAG']['SET'] && $smsFlag == Xphp::$_config['FLAG']['SET']){
            //邮件短信一起发
            $EmailInfo = $this->getSystemEmailParams($systemAlarmID);
            $SmsInfo = $this->getSystemSmsParams($systemAlarmID);
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $smsResult = $usersHandler->sendSms($SmsInfo);
            
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);
            
            $emailResult = json_decode($emailResult, true);
            $smsResult = json_decode($smsResult, true);
            $result = false;
            if ($emailResult['re'] && $smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_SUCCESS'];
                $result = true;
            }elseif ($emailResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_EMAIL_SUCCESS'];
            }elseif ($smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_SMS_SUCCESS'];
            }else{
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_FAILURE'];
            }
            return $this->muOpResult($result, Xphp::$_lang['WEB_ALARM_SEND_NOTICE'], $msg, 'error');
        }
        if($emailFlag == Xphp::$_config['FLAG']['SET']){
            //邮件通知
            $EmailInfo = $this->getSystemEmailParams($systemAlarmID);
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            return $emailResult;
        }
        if($smsFlag == Xphp::$_config['FLAG']['SET']){
            //短信通知
            $SmsInfo = $this->getSystemSmsParams($systemAlarmID);
            $smsResult = $usersHandler->sendSms($SmsInfo);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);
            return $smsResult;
        }
    }
    
    /**
     * 得到所有管理员的邮件和电话
     * @return array(email=>array, telephone=>array)
     */
    public function getManagerEmailAndTelephone(){
        $sql = "select email, telephone from bd_user where user_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['USERTYPE']['manager']));
        $email = array();
        $telephone = array();
        foreach ($data as $d){
            $email[] = $d['email'];
            $telephone[] = $d['telephone'];
        }
        $info = array(
            'email' => $email,
            'telephone' => $telephone
        );
        return $info;
    }
    
    /**
     * 得到所有的通知邮件,
     * @param array $email 要通知的邮件
     * 在要通知的邮件基础上添加系统配置处的邮件
     */
    public function getAllEmail($email){
        $sql = "select receive_email from bd_email_notice";
        $data = $this->dbSelect($sql, array());
        $setEmail = json_decode($data[0]['receive_email'], TRUE);
        $resultEmail = array_unique(array_merge($email, $setEmail));
        return $resultEmail;
    }
    
    /**
     * 得到系统邮件通知参数
     * @param int $systemAlarmID
     */
    private function getSystemEmailParams($systemAlarmID){
        $sql = "select alarm_level, description_key, description_param, alarm_time, error_code, system_log_path
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($systemAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['SYSTEM'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param']);
        $managerInfo = $this->getManagerEmailAndTelephone();
        $params = array(
            'email' => $this->getAllEmail($managerInfo['email']),
            'title' => $desription,
            'info' => $desription . "<br>" . Xphp::$_lang['UI_ALARM_TIME'] . ': ' .  
                      $data[0]['alarm_time'] . "<br>" . Xphp::$_lang['UI_ALARM_LEVLE'] . ":" .
            $data[0]['alarm_level'],
            'attachment' => array($data[0]['system_log_path']),
        );
        return $params;
    }
    
    /**
     * 得到系统短信通知参数
     * @param int $systemAlarmID
     */
    private function getSystemSmsParams($systemAlarmID){
        $sql = "select alarm_level, description_key, description_param, alarm_time, error_code, system_log_path
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($systemAlarmID));
        $this->checkNoticeData($data);
        
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['SYSTEM'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param']);
        $managerInfo = $this->getManagerEmailAndTelephone();
        $telephone = implode(',', $managerInfo['telephone']);
        $params = array(
            'tels' => $telephone,
            'msg' => $desription . ". " . date("m-d H:i:s", strtotime($data[0]['alarm_time'])),
        );
        return $params;
    }
    
    /**
     * 发送任务通知
     * @param int $mode
     * @param int $TaskAlarmID
     */
    private function sendTaskNotice($emailFlag, $smsFlag, $taskAlarmID){
        $usersHandler = Xphp::instance('UsersHandler');
        $checkResult = $this->checkNoticeSetting($emailFlag, $smsFlag, Xphp::$_config['NOTICE_TYPE']['TASK'], $taskAlarmID);
        $emailFlag = $checkResult['email'];
        $smsFlag = $checkResult['sms'];
        $type = Xphp::$_config['NOTICE_TYPE']['TASK'];
        
        if($emailFlag == Xphp::$_config['FLAG']['SET'] && $smsFlag == Xphp::$_config['FLAG']['SET']){
            //邮件短信一起发
            $EmailInfo = $this->getTaskEmailParams($taskAlarmID);
            $SmsInfo = $this->getTaskSmsParams($taskAlarmID);
            
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $smsResult = $usersHandler->sendSms($SmsInfo);
            
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);
            
            $emailResult = json_decode($emailResult, true);
            $smsResult = json_decode($smsResult, true);
    
            $result = false;
            if ($emailResult['re'] && $smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_SUCCESS'];
                $result = true;
            }elseif ($emailResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_EMAIL_SUCCESS'];
            }elseif ($smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_SMS_SUCCESS'];
            }else{
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_FAILURE'];
            }
            return $this->muOpResult($result, Xphp::$_lang['WEB_ALARM_SEND_NOTICE'], $msg, 'error');
        }
        if($emailFlag == Xphp::$_config['FLAG']['SET']){
            //邮件通知
            $EmailInfo = $this->getTaskEmailParams($taskAlarmID);
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            return $emailResult;
        }
        if($smsFlag == Xphp::$_config['FLAG']['SET']){
            //短信通知
            $SmsInfo = $this->getTaskSmsParams($taskAlarmID);
            $smsResult = $usersHandler->sendSms($SmsInfo);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);
            return $smsResult;
        }
    }
    
    /**
     * 得到任务邮件通知参数
     * @param int $taskAlarmID
     */
    private function getTaskEmailParams($taskAlarmID){
        $sql = "select bta.alarm_level, bta.description_key, bta.description_param, bta.alarm_time,
                bta.task_uuid, bta.task_name, bta.task_type, bta.module_type, bta.submodule_type, bta.node_name,
                bta.storage_name, bta.user_name, bta.error_code, bta.task_log_path,
                bu.email, bu.telephone
                from bd_task_alarm bta, bd_user bu
                where bta.user_uuid = bu.user_uuid
                and bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($taskAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], $data[0]['task_name']);
        $params = array(
            'email' => $this->getAllEmail(array($data[0]['email'])),
            'title' => $desription,
            'info' => $this->getTaskEmailBody($data[0]),
            'attachment' => array($data[0]['task_log_path']),
        );
        return $params;
    }
    
    /**
     * 得到任务邮件通知email内容
     * @param array $data
     */
    private function getTaskEmailBody($data){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $noticeArr = array(
            Xphp::$_lang['UI_JOB_RNAME'] => $data['task_name'],
            Xphp::$_lang['UI_PUBLIC_TASK_TYPE'] => $ptDes['TASKTYPEDES'][$data['task_type']],
            Xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] => $ptDes['MODULE_TYPE_DES'][$data['module_type']] . 
                                    "[" . Xphp::$_config['VMHYPERVISORDES'][$data['submodule_type']] . "]",
            Xphp::$_lang['UI_NODE_NAME'] => $data['node_name'],
            Xphp::$_lang['UI_STORAGE_NAME'] => empty($data['storage_name']) ? Xphp::$_config['NULLSPACE'] :  $data['storage_name'],
            Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] => $data['error_code'],
            Xphp::$_lang['UI_ALARM_LEVLE'] => $ptDes['ALARM_LEVEL_DES'][intval($data['alarm_level'])],
            Xphp::$_lang['UI_ALARM_TIME'] => $data['alarm_time'],
        );
        $info = '';
        foreach ($noticeArr as $key => $value){
            $info .= $key . ": " . $value . "<br>";
        }
        
        $list = $this->getTaskEmailBodyList($data['module_type'], $data['task_uuid']);
        $info .= "<br>" . $list;
        return $info;
    }
    
    /**
     * 得到邮件中任务详情列表,文件列表或虚拟机列表
     * @param int $moduleType
     * @param string $taskuuid
     */
    private function getTaskEmailBodyList($moduleType, $taskuuid){
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
            //虚拟机
            $info = Xphp::$_lang['UI_VCENTER_MACHINE_LIST'] . ": <br>";
            $list = $this->getTaskVMList($taskuuid);
        }elseif($moduleType == Xphp::$_config['MODULE_TYPE']['FS']){
            //文件
            $info = Xphp::$_lang['UI_BACKUP_FILE_LIST'] . ": <br>";
            $list = $this->getTaskFileList($taskuuid);
        }
        foreach ($list as $l){
            $info .= $l . "<br>";
        }
        
        if(empty($list)){
            //如果没有获取到虚拟机列表,恢复成功的时候就获取不到,调用发送通知的时候,已经没有记录了.
            $info = '';
        }
        
        return $info;
    }
    
    /**
     * 根据任务UUID得到虚拟机列表
     * @param string $taskuuid
     */
    private function getTaskVMList($taskuuid){
        //TODO 这里可以显示的更详细,比如大小...
        $sql = "select dir_path from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $list = array();
        foreach ($data as $d){
            $list[] = $d['dir_path'];
        }
        return $list;
    }
    
    /**
     * 根据任务UUID得到文件列表
     * @param string $taskuuid
     */
    private function getTaskFileList($taskuuid){
        $sql = "select path_name from fs_path_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $list = array();
        foreach($data as $d){
            $list[] = $d['path_name'];
        }
        return $list;
    }
    
    /**
     * 得到任务短信通知参数
     * @param int $taskAlarmID
     */
    private function getTaskSmsParams($taskAlarmID){
        $sql = "select bta.alarm_level, bta.description_key, bta.description_param, bta.alarm_time,
                bta.task_name, bta.task_type, bta.module_type, bta.submodule_type, bta.node_name,
                bta.storage_name, bta.user_name, bta.error_code, bta.task_log_path,
                bu.email, bu.telephone
                from bd_task_alarm bta, bd_user bu
                where bta.user_uuid = bu.user_uuid
                and bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($taskAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], $data[0]['task_name']);
        $params = array(
            'tels' =>  $data[0]['telephone'],
            'msg' => $desription . ". " . date("m-d H:i:s", strtotime($data[0]['alarm_time'])),
        );
        return $params;
    }
    
    /**
     * 检测是否获取到通知
     * @param array $data
     */
    private function checkNoticeData($data){
        if(empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_GET_NOTICE_INFO']));
        }
    }
    
    /**
     * 检测通知参数
     * @param int $type     通知类型
     * @param int $id       告警ID
     */
    private function checkNoticeParams($emailFlag, $smsFlag, $type, $id){
        if($emailFlag == Xphp::$_config['FLAG']['UNSET'] && $smsFlag == Xphp::$_config['FLAG']['UNSET']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_GET_NOTICE_SETTING']));
        }
        if($type != Xphp::$_config['NOTICE_TYPE']['TASK'] && $type != Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_CHECK_NOTICE_TYPE']));
        }
        if($id <= 0){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_CHECK_NOTICE_NUM']));
        }
    }
    
    /**
     * 根据配置得到本次发送邮件和短信的标志
     * @param boolean $emailFlag    调用者发送邮件的标志
     * @param boolean $smsFlag      调用者发送短信的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return array
     */
    private function checkNoticeSetting($emailFlag, $smsFlag, $type, $id){
        $emailFlag = $this->checkEmailNoticeSetting($emailFlag, $type, $id);
        $smsFlag = $this->checkSmsmNoticeSetting($smsFlag, $type, $id);
        $flag = array(
            'email' => $emailFlag,
            'sms' => $smsFlag
        );
        return $flag;
    }
    
    /**
     * 根据配置得到本次发送邮件的标志
     * @param boolean $emailFlag    调用者发送邮件的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return boolean
     */
    private function checkEmailNoticeSetting($emailFlag, $type, $id){
        if(!$emailFlag) return false;
        //得到邮件配置
        $sql = "select email_notice_flag, system_notice_flag, system_notice_level, 
                task_notice_flag, task_notice_level from bd_email_notice";
        $data = $this->dbSelect($sql);
        //如果未设置总开关,直接返回false
        if($data[0]['email_notice_flag'] == Xphp::$_config['FLAG']['UNSET']) return false;
        
        return $this->unifyCheckSetting($data[0], $type, $id);
    }
    
    /**
     * 根据配置得到本次发送短信的标志
     * @param boolean $emailFlag    调用者发送短信的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return boolean
     */
    private function checkSmsmNoticeSetting($smsFlag, $type, $id){
        if(!$smsFlag) return false;
        //得到邮件配置
        $sql = "select sms_notice_flag, system_notice_flag, system_notice_level,
                task_notice_flag, task_notice_level from bd_sms_notice";
        $data = $this->dbSelect($sql);
        //如果未设置总开关,直接返回false
        if($data[0]['sms_notice_flag'] == Xphp::$_config['FLAG']['UNSET']) return false;
        
        return $this->unifyCheckSetting($data[0], $type, $id);
    }
    
    /**
     * 
     * @param array $setting    配置信息
     * @param int $type         系统通知还是任务通知
     * @param int $id           通知ID号
     * @return boolean
     */
    private function unifyCheckSetting($setting, $type, $id){
        if($type == Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            //系统通知
            if($setting['system_notice_flag'] == Xphp::$_config['FLAG']['UNSET'])   return false;
        
            $alarmID = "system_alarm_id";
            $tableName = "bd_system_alarm";
            $tableLevel = "system_notice_level";
        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['TASK']){
            //任务通知
            if($setting['task_notice_flag'] == Xphp::$_config['FLAG']['UNSET'])   return false;
        
            $alarmID = "task_alarm_id";
            $tableName = "bd_task_alarm";
            $tableLevel = "task_notice_level";
        }
        //得到本次告警等级
        $sql = "select alarm_level from $tableName where $alarmID = ?";
        $alarmInfo = $this->dbSelect($sql, array($id));
        $level = $alarmInfo[0]['alarm_level'];
        
        $settingLeve = explode(',', $setting[$tableLevel]);
        
        if(in_array($level, $settingLeve)){
            return true;
        }
        return false;
    }
    
    /**
     * 更新通知结果
     * @param int $alarmID  告警ID号
     * @param int $type     系统告警/任务告警
     * @param int $mode     邮件/短信
     * @param json $result  发送结果
     */
    private function updateNoticeResult($alarmID, $type, $mode, $result){
        $result = json_decode($result, true);
        if(!$result['re']) return true;     //发送失败,不更新
        
        if($type == Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            $tableName = "bd_system_alarm";     //系统告警
            $tableID = "system_alarm_id";
        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['TASK']){
            $tableName = "bd_task_alarm";       //任务告警
            $tableID = "task_alarm_id";
        }
        
        if($mode == Xphp::$_config['NOTICE_MODE']['EMIAL']){
            $sendFlagName = "email_send_flag";      //邮件通知
        }elseif ($mode == Xphp::$_config['NOTICE_MODE']['SMS']){
            $sendFlagName = "sms_send_flag";        //告警通知
        }
        
        if(empty($tableName) || empty($sendFlagName)) return true;
        $sql = "update $tableName set $sendFlagName = ? where $tableID = ?";
        return $this->dbExec($sql, array(Xphp::$_config['FLAG']['SET'], $alarmID));
    }
    
}
?>