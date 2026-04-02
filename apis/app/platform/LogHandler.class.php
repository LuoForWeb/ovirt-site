<?php
/******************************************* 
** 日志管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-05-03 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class LogHandler extends OPHandler{
    
    /**
     * 得到任务日志
     * @param unknown $params
     */
    public function getJobLogs($params){
        //TODO
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $draw = $params['draw'];
        $sortArr = array('', '', 'task_name', 'submodule_type', 'task_type', 'user_name', 'op_time', 'log_level', 'description_key');
        $sql = "select id, task_name, task_type, user_name, agent_name, module_type, submodule_type, 
                    error_code, unix_timestamp(op_time) op_time, description_key, description_param, log_level 
                from bd_task_log where running_flag = ? ";
        $sqlCount = "select count(id) as total from bd_task_log where running_flag = ? ";
        
        $running_flag = Xphp::$_config['FLAG']['UNSET'];
        $userType = Xphp::$_user['usertype'];
        $confUserType = Xphp::$_config['USERTYPE'];
        
        //获取当前用户所拥有的用户
        $jobHandler = Xphp::instance('JobHandler');
        $userGroup = $jobHandler->getUserGroups(Xphp::$_user['username'], $userType);
        $users = implode("','", $userGroup);
        $name = $search['name'];
        $accurateFlag = $params['accurateFlag'];
        if(!$accurateFlag){
        	if($userType == $confUserType['operator']){
        		$sql .= "and user_name = ? ";
        		$sqlCount .= "and user_name = ? ";
        		$sqlParams = array($running_flag, Xphp::$_user['username']);
        		$sqlCountParams = array($running_flag, Xphp::$_user['username']);
        	}else{
        		$sql .= "and user_name in ('". $users ."') ";
        		$sqlCount .= "and user_name in ('". $users ."') ";
        		$sqlParams = array($running_flag);
        		$sqlCountParams = array($running_flag);
        	}
        	//按任务名搜索
        	if(!empty($name)){
        		//按模块搜索
        		$sql .= " and task_name like '%" . $name . "%' ";
        		$sqlCount .= " and task_name like '%" . $name . "%' ";
        		$sqlParams = array_merge($sqlParams, array($start, $length));
        		$sqlCountParams = array_merge($sqlCountParams, array());
        	}else{
        		$sqlParams = array_merge($sqlParams, array($start, $length));
        	}
        }else{
        	$search = $params['search'];
        	$createUser = $search['createUser'];
        	$hypervisor = intval($search['hypervisor']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
        	$taskName = $search['taskName'];
        	$logStatus = intval($search['logStatus']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	
        	//判断输入的用户名是否在当前用户的管理范围
        	if(in_array($createUser, $userGroup)){
        		$sql .= "and user_name = ? ";
        		$sqlCount .= "and user_name = ? ";
        		$sqlParams = array($running_flag, $createUser, $start, $length);
        		$sqlCountParams = array($running_flag, $createUser);
        	}else if(!$createUser){
        		$sql .= "and user_name in ('". $users ."') ";
        		$sqlCount .= "and user_name in ('". $users ."') ";
        		$sqlParams = array($running_flag, $start, $length);
        		$sqlCountParams = array($running_flag);
        	}else{
        		$sql .= "and user_name = ? ";
        		$sqlCount .= "and user_name = ? ";
        		$createUser = '';
        		$sqlParams = array($running_flag, $createUser, $start, $length);
        		$sqlCountParams = array($running_flag, $createUser);
        	}
        	
        	if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
        		$sql .= " and (submodule_type = ". $hypervisor ." or ". $hypervisor . " = '') ";
        		$sqlCount .= " and (submodule_type = ". $hypervisor ." or ". $hypervisor . " = '') ";
         	}
        	$sql .= " and
        			(task_type = ". $taskType ." or ". $taskType ." = '') and
        			(module_type = ". $moduleType ." or ". $moduleType ." = '') and
        			(log_level = ". $logStatus ." or ". $logStatus ." = '') and
        				 task_name like '%". $taskName ."%' ";
        	$sqlCount .= " and
        			(task_type = ". $taskType ." or ". $taskType ." = '') and
        			(module_type = ". $moduleType ." or ". $moduleType ." = '') and
        			(log_level = ". $logStatus ." or ". $logStatus ." = '') and
        				 task_name like '%". $taskName ."%' ";
        	
        	//如果填了日志时间范围查询
        	if($startTime && $endTime){
        		$sql .= " and op_time between '". $startTime ."' and '". $endTime ."' ";
        		$sqlCount .= " and op_time between '". $startTime ."' and '". $endTime ."' ";
        	}
        }
		
        
        
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $desConf = include APP_PATH. 'platform/PFDescription.php';
        $records = array("data" => array());
        foreach($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['task_name'],
                $this->getModuleTypeDes($d['module_type'], $d['submodule_type']),
                $desConf['TASKTYPEDES'][$d['task_type']],
                $d['user_name'],
                $this->parseDate($d['op_time']),
                $this->getLogLevelDes($d['log_level']),
                $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'], 
                    $d['description_key'], $d['description_param']),
                array('level'=>intval($d['log_level'])),
            );
        }
        
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到虚拟机任务监控运行日志
     * @param unknown $params
     * @return string
     */
    public function getVMRunningJobLog($params){
        $taskuuid = $params['uuid'];
        $sql = "select id, task_name, task_type, user_name, agent_name, module_type, submodule_type,
        error_code, unix_timestamp(op_time) op_time, description_key, description_param, log_level
        from bd_task_log where task_uuid = ? and running_flag = ?  order by id desc";
        $sqlParams = array($taskuuid, Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        foreach ($data as $d){
            $info[] = array(
                $this->parseDate($d['op_time']),
                $this->getLogLevelDes($d['log_level']),
                $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],
                    $d['description_key'], $d['description_param'], $d['log_level']),
                array('level'=>intval($d['log_level'])),
            );
        }
        return  json_encode($info);
    }
    
    /**
     * 得到正在运行的任务的日志
     * @param unknown $params
     */
    public function getRunningJobLog($params){
        $taskuuid = $params['uuid'];
        //注意,这里是通过ID来判断日志的生成先后顺序
        $sortArr = array('', 'id', '', '');
        $sql = "select id, task_name, task_type, user_name, agent_name, module_type, submodule_type,
                    error_code, op_time, description_key, description_param, log_level
                from bd_task_log where task_uuid = ? and running_flag = ?  order by op_time desc, id desc";
        $sqlParams = array($taskuuid, Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $records = array();
        foreach ($data as $d){
            $records[] = array(
                $d['op_time'],
                $this->getLogLevelDes($d['log_level']),
                $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],
                    $d['description_key'], $d['description_param']),
                array('level'=>intval($d['log_level'])),
            );
        }
        return  json_encode($records);
    }
    
    /**
     * 得到正在运行的文件任务日志
     * @param unknown $params
     */
    public function getFileRunningJobLog($params){
    	$taskuuid = $params['uuid'];
    	//注意,这里是通过ID来判断日志的生成先后顺序
    	$sortArr = array('', 'id', '', '');
    	$sql = "select id, task_name, task_type, user_name, agent_name, module_type, submodule_type,
                    error_code, unix_timestamp(op_time), description_key, description_param, log_level
                from bd_task_log where task_uuid = ? and running_flag = ?  order by id desc";
    	$sqlParams = array($taskuuid, Xphp::$_config['FLAG']['SET']);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$info = array();
    	foreach ($data as $d){
    	    $info[] = array(
    	        $this->parseDate($d['op_time']),
    	        $this->getLogLevelDes($d['log_level']),
    	        $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],
    	            $d['description_key'], $d['description_param'], $d['log_level']),
    	        array('level'=>intval($d['log_level'])),
    	    );
    	}
    	return  json_encode($info);
    }
    
    /**
     * 得到系统日志
     * @param unknown $params
     */
    public function getSystemLogs($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'user_name', 'op_time', 'log_level', 'description_key');
        
        $sql = "select id, user_uuid, user_name, agent_name, error_code, unix_timestamp(op_time) op_time, description_key, 
                description_param, log_level from bd_system_log ";
        $sqlCount = "select count(id) as total from bd_system_log ";
        
        $userType = Xphp::$_user['usertype'];
        $confUserType = Xphp::$_config['USERTYPE'];
    	//获取当前用户所拥有的用户
        $jobHandler = Xphp::instance('JobHandler');
        $userGroup = $jobHandler->getUserGroups(Xphp::$_user['username'], $userType);
        $users = implode("','", $userGroup);
        $name = $search['name'];
        $accurateFlag = $params['accurateFlag'];
        if(!$accurateFlag){
        	if($userType == $confUserType['operator']){
        		$sql .= "where user_name = ? ";
        		$sqlCount .= "where user_name = ? ";
        		$sqlParams = array(Xphp::$_user['username']);
        		$sqlCountParams = array(Xphp::$_user['username']);
        	}else{
        		$sql .= "where user_name in ('". $users ."') ";
        		$sqlCount .= "where user_name in ('". $users ."') ";
        		$sqlParams = array();
        		$sqlCountParams = array();
        	}
        	//按任务名搜索
        	if(!empty($name)){
        		//按模块搜索
        		$sql .= " and task_name like '%" . $name . "%' ";
        		$sqlCount .= " and task_name like '%" . $name . "%' ";
        		$sqlParams = array_merge($sqlParams, array($start, $length));
        		$sqlCountParams = array_merge($sqlCountParams, array());
        	}else{
        		$sqlParams = array_merge($sqlParams, array($start, $length));
        	}
        }else{
        	$search = $params['search'];
        	$createUser = $search['createUser'];
        	$logStatus = intval($search['logStatus']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	
        	//判断输入的用户名是否在当前用户的管理范围
        	if(in_array($createUser, $userGroup)){
        		$sql .= "where user_name = ? ";
        		$sqlCount .= "where user_name = ? ";
        		$sqlParams = array($createUser, $start, $length);
        		$sqlCountParams = array($createUser);
        	}else if(!$createUser){
        		$sql .= "where user_name in ('". $users ."') ";
        		$sqlCount .= "where user_name in ('". $users ."') ";
        		$sqlParams = array($start, $length);
        		$sqlCountParams = array();
        	}else{
        		$sql .= "where user_name = ? ";
        		$sqlCount .= "where user_name = ? ";
        		$createUser = '';
        		$sqlParams = array($createUser, $start, $length);
        		$sqlCountParams = array($createUser);
        	}
        	
        	$sql .= " and (log_level = ". $logStatus ." or ". $logStatus ." = '') ";
        	$sqlCount .= " and (log_level = ". $logStatus ." or ". $logStatus ." = '') ";
        	
        	//如果填了日志时间范围查询
        	if($startTime && $endTime){
        		$sql .= " and op_time between '". $startTime ."' and '". $endTime ."' ";
        		$sqlCount .= " and op_time between '". $startTime ."' and '". $endTime ."' ";
        	}
        }
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        
        $records = array("data" => array());
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['user_name'],
                $this->parseDate($d['op_time']),
                $this->getLogLevelDes($d['log_level']),
                $this->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'], $d['error_code'], 
                    $d['description_key'], $d['description_param']),
                array('level'=>intval($d['log_level'])),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到下载系统日志列表
     * @param unknown $params
     */
    public function getSystemDownloadLogs($params){
        $nodeuuid = $params['node'];
        $start = intval($params['start']);
        $length = $params['length'];
        $sortType = $params['sortType'];
        $this->paramsCheck($nodeuuid);
        $opName = 'BD_SYSTEM_OP_LIST_DIR';
        $dateList = array();
        $fileList = array();
        $logPath = Xphp::$_config['LOG_PATH'];
        foreach ($logPath as $key=>$value){
            $msg = array('file_path' => $value);
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
            if($mbResult['result']){
                foreach ($mbResult['msg']['file_list'] as $file){
                    $fileDate = date("Y-m-d", strtotime($file['create_time']));
                    if(!in_array($fileDate, $dateList)){
                        //如果没有这天
                        $dateList[] = $fileDate;
                        $fileList[$fileDate] = array(
                            $fileDate,
                            $file['file_size'],
                            $file['modify_time']
                        );
                    }else{
                        //如果已经有了这天,更新文件大小和最后修改时间
                        $fileList[$fileDate][1] += $file['file_size'];
                        if(strtotime($file['modify_time']) > strtotime($fileList[$fileDate][2])){
                            $fileList[$fileDate][2] = $file['modify_time'];
                        }
                    }
                }
            }
        }
        
        if("desc" == $sortType){
            $sort = SORT_DESC;
        }else{
            $sort = SORT_ASC;
        }
        
        array_multisort($dateList, $sort);
        $logNum = count($fileList);
        
        $utils = Xphp::instance('Utils');
        $records = array("data" => array());
        $lengthNum = $start + $length;
        
        
        for($i=$start; $i<$lengthNum; $i++){
            if(empty($dateList[$i]))    continue;
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $fileList[$dateList[$i]][0] . '">',
                "system_log_" . $fileList[$dateList[$i]][0],
                $utils->calSize($fileList[$dateList[$i]][1]),
                $fileList[$dateList[$i]][2],
            );
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $logNum;
        $records["recordsFiltered"] = $logNum;
        
        return  json_encode($records);
    }
    
    /**
     * 得到日志下载包路径
     * 涉及到节点,所以只能通过读取文件内容来处理
     * @param unknown $params
     */
    public function getDownLoadPackageName($params){
        $nodeuuid = $params['nodeuuid'];
        $ids = $params['id'];
        $this->paramsCheck($nodeuuid, $ids);
        $logPath = Xphp::$_config['LOG_PATH'];
        $tmpPath = Xphp::$_config['TMP_PATH'];
        $filenameArr = array();
        foreach ($ids as $id){
            foreach ($logPath as $key => $value){
                $filename = $value . "/" . $key . "-" . $id . ".log";
                $tmpFilename = $tmpPath . $key . "-" . $id . ".log";
                $opName = 'BD_SYSTEM_OP_READ_FILE';
                $msg = array('file_path' => $filename);
                $msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
                if($msg['result']){
                    file_put_contents($tmpFilename, $msg['msg']['file_content']);
                    $filenameArr[] = $key . "-" . $id . ".log";
                }
            }
        }
        if(!empty($filenameArr)){
            $filenameStr = implode(" ", $filenameArr);
            $cmd = "cd " . $tmpPath;
            $cmd .= ";rm -rf system_log.zip";
            $cmd .= ";zip -q -r -m " . $tmpPath . "system_log.zip " . $filenameStr;
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = array('command'=>$cmd);
            $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
            if($mbResult['result']){
                return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_LOG_PACKAGE_FILE'], "", "", "", Xphp::$_config['TMP_PATH_RE'] . "system_log.zip");
            }else{
                return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_LOG_PACKAGE_FILE']);
            }
        }else{
            //读取远程文件失败
            return $this->muOpResult(false, Xphp::$_lang['WEB_LOG_READ_REMOTE_FILE']);
        }
        
    }
    
    /**
     * 得到任务日志模块类型信息
     * @param int $module       模块号
     * @param int $subModule    子模块号
     * 告警/存储要调用
     */
    public function getModuleTypeDes($module, $subModule){
        $desConf = include APP_PATH. 'platform/PFDescription.php';
        $des = $desConf['MODULE_TYPE_DES'][$module];
        if($subModule){
            //添加子模块,暂时添加虚拟机子模块,后期TODO涉及到数据库子模块
            if($module == Xphp::$_config['MODULE_TYPE']['VM']){
//                 $des .= "[" . Xphp::$_config['VMHYPERVISORDES'][$subModule] . "]";
                $des = Xphp::$_config['VMHYPERVISORDES'][$subModule];
            }
        }
        return $des;
    }
    
    /**
     * 得到日志等级
     * @param int $logLevel
     */
    private function getLogLevelDes($logLevel){
        $logLevel = intval($logLevel);
        $confDes = include APP_PATH . 'platform/PFDescription.php';
        $confDes = $confDes['LOG_LEVEL_DES'];
        return $confDes[$logLevel];
    }
    
    /**
     * 得到日志描述信息
     * @param int $logType      日志类型   系统日志2/任务日志1
     * @param int $errorCode    错误码
     * @param string $desription    描述
     * @param string $descriptionParam  描述参数
     * @return string
     */
    public function getLogDesription($logType, $errorCode, $desription, $descriptionParam, $logLevel){
        $confDes = $this->getLogConf($logType);
        $desStr = $confDes[$desription];
        if($errorCode){
            $errorConf = include CONF_PATH . 'error.php';
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $errorClass = $this->getLogLevelClass($logLevel);
            $desStr .= ",[" . '<span  class="' . $errorClass . '">#' . $errorCode . '</span>' . "]" . $errorStr;
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
     * 得到日志错误对应的类型
     * @param unknown $logLevel
     */
    private function getLogLevelClass($logLevel){
        $logClass = "font-blue";   //一般日志
        if(Xphp::$_config['LOGLEVEL']['WARN'] == $logLevel){
            $logClass = "font-yellow-gold";
        }elseif(Xphp::$_config['LOGLEVEL']['ERROR'] == $logLevel){
            $logClass = "font-red-thunderbird";
        }
        return $logClass;
    }
    
    /**
     * 得到短信和邮件通知日志描述信息
     * @param int $logType      日志类型   系统日志2/任务日志1
     * @param int $errorCode    错误码
     * @param string $desription    描述
     * @param string $descriptionParam  描述参数
     * @param string $taskName  任务名,只有任务告警有
     * @return string
     */
    public function getLogDesriptionNotice($logType, $errorCode, $desription, $descriptionParam, $taskName){
        $confDes = $this->getLogConf($logType);
        $desStr = $confDes[$desription];
        if($errorCode){
            $errorConf = include CONF_PATH . 'error.php';
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $desStr .= ",[#" . $errorCode . "]" . $errorStr;
        }
        if($descriptionParam){
            $param = json_decode($descriptionParam, true);
            $desArr = explode("%s", $desStr);
            $desStr = "";
            foreach ($desArr as $k => $v){
                $desStr .= $v . $this->getEachParamsDes($param[$k], false);
            }
        }
        //如果是有任务名
        if(!empty($taskName)){
            $desStr = "[" . $taskName . "]" . $desStr;
        }
        return $desStr;
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
                if($classShowFlag){
                    $des = '<span  class="font-blue">' . $arr[1] . '</span>';
                }else{
                    $des = $arr[1];
                }
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
     * 根据类型得到日志配置文件
     * @param int $logType  日志类型   系统日志2/任务日志1
     * @return array 
     */
    private function getLogConf($logType){
        if($logType == Xphp::$_config['LOGTYPE']['SYSTEM']){
            return include CONF_PATH . 'log_system.php';
        }elseif ($logType == Xphp::$_config['LOGTYPE']['TASK']){
            return include CONF_PATH . 'log_task.php';
        }
    }
    
    /**
     * 删除系统日志
     * @param unknown $params
     * @return string
     */
    public function deleteSystemLog($params){
        $id = $params['id'];
        $this->paramsCheck($id);
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }
        $opName = 'BD_LOG_OP_SYSTEM_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }
    
    /**
     * 删除任务日志
     * @param unknown $params
     * @return string
     */
    public function deleteTaskLog($params){
        $id = $params['id'];
        $this->paramsCheck($id);
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }
        $opName = 'BD_LOG_OP_TASK_DELETE';
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
}
?>