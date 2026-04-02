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
        $sortArr = array('', '', 'btl.task_name', 'btl.submodule_type', 'btl.task_type', 'btl.user_name', 'btl.op_time', 'btl.log_level', 'btl.description_key');
        $sql = "select bu.user_level, btl.id, btl.task_name, btl.task_type, btl.user_name, btl.agent_name, btl.module_type, btl.submodule_type, 
                    btl.error_code, unix_timestamp(btl.op_time) op_time, btl.description_key, btl.description_param, btl.log_level 
                from bd_task_log btl left join bd_user bu on btl.user_uuid = bu.user_uuid
                where btl.running_flag = ? ";
        $sqlCount = "select count(btl.id) as total from bd_task_log btl left join bd_user bu on btl.user_uuid = bu.user_uuid
                        where btl.running_flag = ? ";
        
        $running_flag = Xphp::$_config['FLAG']['UNSET'];
        $sqlParams = array($running_flag);
        $sqlCountParams = array($running_flag);
        //获取当前用户所拥有的用户
        $utils = Xphp::instance('Utils');
        
        $name = $search['name'];
        $name = $utils->escapeWildcard($name);
        $accurateFlag = $params['accurateFlag'];

        if ($_SESSION['isThreePowers']) {
            // 如果是三权模式
            if($_SESSION['userLevel'] == 3){
                //安全管理员
                $sql .= " and (bu.user_level = 5 or bu.user_level = 4) ";
                $sqlCount .= " and (bu.user_level = 5 or bu.user_level = 4) ";
            }else if($_SESSION['userLevel'] == 4){
                //安全审计员
                $sql .= " and (bu.user_level = 2 or bu.user_level = 3) ";
                $sqlCount .= " and (bu.user_level = 2 or bu.user_level = 3) ";
            } else {
                // 关联管理用户判断 日志 - 查看   log_look
                $authUser = $_SESSION['authUser']['log_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and btl.user_uuid in " . $useruuidArr;
                    $sqlCount .= "and btl.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and btl.user_uuid = ? ";
                    $sqlCount .= " and btl.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
                    $sqlCountParams = array_merge($sqlCountParams,array(Xphp::$_user['useruuid']));
                }
            }
        } else {
            //不是全局观察者获取对应用户的任务
            if(!in_array("global_observer", $_SESSION['permission'])){
                // 关联管理用户判断 日志 - 查看   log_look
                $authUser = $_SESSION['authUser']['log_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and btl.user_uuid in " . $useruuidArr;
                    $sqlCount .= "and btl.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and btl.user_uuid = ? ";
                    $sqlCount .= " and btl.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
                    $sqlCountParams = array_merge($sqlCountParams,array(Xphp::$_user['useruuid']));
                }
            }
        }
        if(!$accurateFlag){
        	//按任务名搜索
            if($this->checkEmpty($name)){
        		$sql .= " and btl.task_name like ? ";
        		$sqlCount .= " and btl.task_name like ? ";
        		$sqlParams = array_merge($sqlParams, array('%'.$name.'%'));
        		$sqlCountParams = array_merge($sqlCountParams, array('%'.$name.'%'));
        	}
        }else{
        	$search = $params['search'];
        	$createUser = $search['createUser'];
        	$hypervisor = intval($search['hypervisor']);
        	$dbType = intval($search['dbtype']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
            $subModuleType = intval($search['sub_module_type']);
        	$taskName = $search['taskName'];
        	$taskName = $utils->escapeWildcard($taskName);
        	$logStatus = intval($search['logStatus']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	
        	//判断输入的用户名是否在当前用户的管理范围
        	if($this->checkEmpty($createUser)){
        	    //按模块搜索
        	    $sql .= " and btl.user_name like ? ";
        	    $sqlCount .= " and btl.user_name like ? ";
        	    $sqlParams = array_merge($sqlParams, array('%'.$createUser.'%'));
        	    $sqlCountParams = array_merge($sqlCountParams, array('%'.$createUser.'%'));
        	}
        	
        	if($moduleType == Xphp::$_config['MODULE_TYPE']['VM'] && !empty($hypervisor)){
        	    //虚拟化类型
        	    $sql .= " and btl.submodule_type = ? ";
        	    $sqlCount .= " and btl.submodule_type = ? ";
        	    $sqlParams = array_merge($sqlParams, array($hypervisor));
        	    $sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
        	}else if($moduleType == Xphp::$_config['MODULE_TYPE']['DB'] && !empty($dbType)){
        	    //数据库类型
        	    $sql .= " and btl.submodule_type = ? ";
        	    $sqlCount .= " and btl.submodule_type = ? ";
        	    $sqlParams = array_merge($sqlParams, array($dbType));
        	    $sqlCountParams = array_merge($sqlCountParams, array($dbType));
        	}
        	
        	//按任务名搜索
        	if($this->checkEmpty($taskName)){
        	    $sql .= " and btl.task_name like ? ";
        	    $sqlCount .= " and btl.task_name like ? ";
        	    $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
        	    $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        	}
        	
        	//模块类型
        	if(!empty($moduleType)){
                if ($subModuleType == 3) { //公有云
                    $publicCloud =  "(" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ")";
                    $sql.= " and btl.module_type = ? and btl.submodule_type in  " . $publicCloud . " ";
                    $sqlCount .= " and btl.module_type = ? and btl.submodule_type in ( " . $publicCloud . " ) ";
                    $sqlParams = array_merge($sqlParams, array($moduleType));
         	        $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
                } else if($subModuleType == 1){ // 虚拟机
                    $publicCloud =  "(" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ")";
                    $vmHypervisor = "(" . implode(',', Xphp::$_config['VMHYPERVISORTYPE']) . ")";
                    $sql .= " and btl.module_type = ? and btl.submodule_type in  " . $vmHypervisor . " and btl.submodule_type not in " . $publicCloud . " ";
                    $sqlCount .= " and btl.module_type = ? and btl.submodule_type in  " . $vmHypervisor . " and btl.submodule_type not in " . $publicCloud . " ";
                    $sqlParams = array_merge($sqlParams, array($moduleType));
                    $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
                } else {
                    $sql .= " and btl.module_type = ? ";
        	        $sqlCount .= " and btl.module_type = ? ";
        	        $sqlParams = array_merge($sqlParams, array($moduleType));
        	        $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
                }
        	}
            if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
                $tasktypeList = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'],
                    Xphp::$_config['TASKTYPE']['DB_BACKUP'],
                    Xphp::$_config['TASKTYPE']['OS_BACKUP'],
                    Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']
                );
            }else if($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']){
                $tasktypeList = array(
                    Xphp::$_config['TASKTYPE']['RECOVERY'],
                    Xphp::$_config['TASKTYPE']['DB_RECOVERY'],
                    Xphp::$_config['TASKTYPE']['OS_RECOVERY'],
                    Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']
                );
            }else if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY']){
                $tasktypeList = array(Xphp::$_config['TASKTYPE']['BACKUP_COPY']);
            }else if($taskType == Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']){
                $tasktypeList = array(Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']);
            }else {
                $tasktypeList = array($taskType);
            }
            $tasktypeListDes = implode(',', $tasktypeList);
        	//任务类型
        	if(!empty($taskType)){
                $sql .= " and btl.task_type in (".$tasktypeListDes.") ";
                $sqlCount .= " and btl.task_type in (".$tasktypeListDes.") ";
        	}
        	
        	//日志等级
        	if(!empty($logStatus)){
        	    $sql .= " and btl.log_level = ? ";
        	    $sqlCount .= " and btl.log_level = ? ";
        	    $sqlParams = array_merge($sqlParams, array($logStatus));
        	    $sqlCountParams = array_merge($sqlCountParams, array($logStatus));
        	}
        	
        	//如果填了开始时间范围查询
        	if(!empty($startTime) && !empty($endTime)){
        	    $sql .= " and btl.op_time between ? and ? ";
        	    $sqlCount .= " and btl.op_time between ? and ? ";
        	    $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        	    $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
        	}
        }
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams,array($start,$length));

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $desConf = include APP_PATH. 'platform/PFDescription.php';
        $records = array("data" => array());
        foreach($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['task_name'],
                $this->getModuleTypeDes($d['module_type'], $d['submodule_type'], $d['task_type']),
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
        $sql = "select btl.id, btl.task_name, btl.task_type, btl.user_name, btl.agent_name, btl.module_type, btl.submodule_type,
        btl.error_code, unix_timestamp(btl.op_time) op_time, btl.description_key, btl.description_param, btl.log_level, btl.id, btl.error_detail, btl.script_detail,
        vml.vm_config, bht.details 
        from bd_task_log btl left join vm_machine_list vml on btl.task_uuid = vml.task_uuid and btl.task_type = ? 
        left join bd_history_task bht on btl.task_uuid = bht.task_uuid 
        where btl.task_uuid = ? and btl.running_flag = ? group by btl.id order by btl.id desc";
        $sqlParams = array(Xphp::$_config['TASKTYPE']['RECOVERY'], $taskuuid, Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        foreach ($data as $d){
            $des = $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],
                $d['description_key'], $d['description_param'], $d['log_level'], $d['id'],
                $d['error_detail'], $d['module_type'], $d['submodule_type'],
                $d['script_detail']);
            // 公有云替换描述
            if (in_array($d['submodule_type'], Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
                $vmConfig = $d['vm_config'] ?? '';
                $vmConfig = json_decode($vmConfig, true);
                $historyDetail = $d['details'] ?? '';
                $historyDetail = json_decode($historyDetail, true);
                // 任务运行时从vm_machine_list.vm_config获取恢复类型，完成时从历史任务详情获取是否有卷列表判断恢复类型
                if (Xphp::$_config['TASKTYPE']['RECOVERY'] == $d['task_type']
                    && (!empty($vmConfig) && 2 == $vmConfig['recovery_level'] || !empty($historyDetail[0]['disk_list']))) {
                    // 卷恢复替换第一条描述的"实例"为"卷"
                    $des = str_replace(Xphp::$_lang['WEB_LOG_TASK_GET_RECOVERY_INSTANCE_LIST'], Xphp::$_lang['WEB_LOG_TASK_GET_RECOVERY_VOL_LIST'], $des);
                    // "开始恢复实例‘实例名（实例ID）’"改为"开始恢复实例‘实例名（实例ID）’的卷数据"
                    if (0 === strpos($des, Xphp::$_lang['UI_RECOVERY_AWS_START_RECOVERY_INSTANCE'])) {
                        $des .= Xphp::$_lang['UI_RECOVERY_AWS_VOL_DATA_OF_INSTANCE'];
                    }
                }
            }
            $info[] = array(
                $this->parseDate($d['op_time']),
                $this->getLogLevelDes($d['log_level']),
                $des,
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
     * 得到正在运行的卷实时模块任务日志
     * @param unknown $params
     */
    public function getVolCdpRunningJobLog($params){
        $taskuuid = $params['uuid'];
        //注意,这里是通过ID来判断日志的生成先后顺序
        $sortArr = array('', 'id', '', '');
        $sql = "select id, task_name, task_type, user_name, agent_name, module_type, submodule_type,
                    error_code, op_time, description_key, description_param, log_level,task_uuid 
                from bd_task_log where task_uuid = ? and running_flag = ?  order by op_time desc, id desc";
        $sqlParams = array($taskuuid, Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $records = array();
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        foreach ($data as $d){
            $taskuuid = $d['task_uuid'];
            $taskType = $d['task_type'];
            $takeoverAgentRole = 0;
            if($taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){  //接管任务
                $takeoverAgentRole = $this->takeoverAppType($taskuuid);
            }
            $getLogDesriptionDes =  $this->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],$d['description_key'], $d['description_param'],$d['log_level']);
            
            //验证场景,将接管替换成验证
            if($takeoverAgentRole == $VolCdpDes['EMD_VM_ROLE']['EMD_VM_ROLE_DRILL']){ 
                $getLogDesriptionDes = str_replace(Xphp::$_lang['UI_VOL_CDP_STANDBY_USE_TAKEOVER'], Xphp::$_lang['UI_VOL_CDP_STANDBY_USE_VERIFY'], $getLogDesriptionDes); 
            } 
            $records[] = array(
                $d['op_time'],
                $this->getLogLevelDes($d['log_level']),
                $getLogDesriptionDes,
                array('level'=>intval($d['log_level'])),
            );
        }
        return  json_encode($records);
    }
    /**
     * 获取实时接管任务应用场景
     * @param mixed $taskuuid
     * @return mixed
     */
    private function takeoverAppType($taskuuid){
        $sql = "select takeover_agent_role from cdp_vol_task_takeover_info where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0]['takeover_agent_role'];
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
        $search =$params['search'];
        $sortArr = array('', '', 'bsl.user_name','bsl.op_time', 'bsl.log_level', 'bsl.description_key');

        $sql = "select bu.user_level, bsl.id, bsl.user_uuid, bsl.user_name, bsl.agent_name, bsl.error_code, unix_timestamp(bsl.op_time) op_time, bsl.description_key, 
                bsl.description_param, bsl.log_level from bd_system_log bsl left join bd_user bu on bsl.user_uuid = bu.user_uuid ";
        $sqlCount = "select count(bsl.id) as total from bd_system_log bsl left join bd_user bu on bsl.user_uuid = bu.user_uuid ";
        $sqlParams = array();
        $sqlCountParams = array();
        //检查是否是租户管理员
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
        $userListDes = implode("','", $userList);
        //Master用户
        if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
            $sql .= " where 1=1 ";
            $sqlCount .= " where 1=1 ";
        }else if($tenantMangerFlag){
            $sql .= " where bsl.user_uuid in ('".$userListDes."')";
            $sqlCount .= " where bsl.user_uuid in ('".$userListDes."')";
        }else{
            if ($_SESSION['isThreePowers']) {
                // 三权模式
                if($_SESSION['userLevel'] == 3){
                    //安全管理员
                    $sql .= " where (bu.user_level = 5 or bu.user_level = 4) ";
                    $sqlCount .= " where (bu.user_level = 5 or bu.user_level = 4) ";
                }else if($_SESSION['userLevel'] == 4){
                    //安全审计员
                    $sql .= " where (bu.user_level = 2 or bu.user_level = 3) ";
                    $sqlCount .= " where (bu.user_level = 2 or bu.user_level = 3) ";
                } else {
                    $sql .= " where bsl.user_uuid = ? ";
                    $sqlCount .= " where bsl.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                    $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
                }
            } else {
                // 关联管理用户判断 存储资源 - 查看   log_look
                $authUser = $_SESSION['authUser']['log_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= " where bsl.user_uuid in " . $useruuidArr;
                    $sqlCount .= " where bsl.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " where bsl.user_uuid = ? ";
                    $sqlCount .= " where bsl.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                    $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
                }
            }
        }
        
    	//获取当前用户所拥有的用户
        
        $name = $search['name'];
        $accurateFlag = $params['accurateFlag'];
        
        if(!$accurateFlag){
            if($this->checkEmpty($name)){
                $sql .= " and bsl.user_name like ? ";
                $sqlCount .= " and bsl.user_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$name.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$name.'%'));
            }
        }else{
        	$search = $params['search'];
        	$createUser = $search['createUser'];
        	$logStatus = intval($search['logStatus']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
            $opType = $search['opType'];

            if($this->checkEmpty($opType)){
                $arr = ['SYSTEM_USER_LOGIN','UI_USER_LOGIN_OUT','SYSTEM_USER','BD_SYSTEMLOG_DESC_KEY_NODE',
                    'BD_SYSTEMLOG_DESC_KEY_AGENT_','BD_SYSTEMLOG_DESC_KEY_STORAGE'];
                if (in_array($opType, $arr)) {
                    $sql .= " and bsl.description_key like ?";
                    $sqlCount .= " and bsl.description_key like ? ";
                    $sqlParams = array_merge($sqlParams, array($opType . '%'));
                    $sqlCountParams = array_merge($sqlCountParams, array($opType . '%'));
                } else {
                    foreach ($arr as $item) {
                        $sql .= " and bsl.description_key not like ?";
                        $sqlCount .= " and bsl.description_key not like ? ";
                        $sqlParams = array_merge($sqlParams, array($item . '%'));
                        $sqlCountParams = array_merge($sqlCountParams, array($item . '%'));
                    }
                }
            }
        	
        	if($this->checkEmpty($createUser)){
        	    $sql .= " and bsl.user_name like ? ";
        	    $sqlCount .= " and bsl.user_name like ? ";
        	    $sqlParams = array_merge($sqlParams, array('%'.$createUser.'%'));
        	    $sqlCountParams = array_merge($sqlCountParams, array('%'.$createUser.'%'));
        	}
        	
        	//日志等级
        	if(!empty($logStatus)){
        	    $sql .= " and bsl.log_level = ? ";
        	    $sqlCount .= " and bsl.log_level = ? ";
        	    $sqlParams = array_merge($sqlParams, array($logStatus));
        	    $sqlCountParams = array_merge($sqlCountParams, array($logStatus));
        	}
        	
        	//如果填了开始时间范围查询
        	if(!empty($startTime) && !empty($endTime)){
        	    $sql .= " and bsl.op_time between ? and ? ";
        	    $sqlCount .= " and bsl.op_time between ? and ? ";
        	    $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        	    $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
        	}
        	
        }
        //排除副本任务执行保留策略和合并时间点的日志
        $sql .= ' and bsl.description_key != "BACKUP_COPY_DESC_KEY_DO_RETENTION" and bsl.description_key != "BACKUP_COPY_DESC_KEY_MERGE_TIMEPOINT" ';
        $sqlCount .= ' and bsl.description_key != "BACKUP_COPY_DESC_KEY_DO_RETENTION" and bsl.description_key != "BACKUP_COPY_DESC_KEY_MERGE_TIMEPOINT" ';
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $num = intval($count[0]['total']);
        $records = array("data" => array());
        foreach ($data as $d){
            $des = $this->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'], $d['error_code'],
                $d['description_key'], $d['description_param']);
            $opTypeDes = $this->getOpType($d['description_key']);

            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['user_name'],
                $opTypeDes,
                $this->parseDate($d['op_time']),
                $this->getLogLevelDes($d['log_level']),
                $des,
                array('level'=>intval($d['log_level'])),
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $num;
        $records["recordsFiltered"] =$num;
        
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
        $dateList = array();
        $fileList = array();
        // 获取系统的日志文件列表
        $logList = $this->getLogList();
        // 按天合并日志
        foreach ($logList as $fileInfo) {
            $fileDate = date('Y-m-d', $fileInfo['create_time']);
            if (!in_array($fileDate, $dateList)) {
                $dateList[] = $fileDate;
                $fileList[$fileDate] = [
                    $fileDate,
                    $fileInfo['filesize'],
                    $fileInfo['modify_time'],
                ];
            } else {
                // 同一天有多个日志文件，追加到列表里面
                $fileList[$fileDate][1] += $fileInfo['filesize'];
                if ($fileInfo['modify_time'] > $logList[$fileDate][2]) {
                    // 取最后的修改时间
                    $logList[$fileDate][2] = $fileInfo['modify_time'];
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
                // 这个值是秒级时间戳，需要格式化
                date('Y-m-d H:i:s', $fileList[$dateList[$i]][2])
            );
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $logNum;
        $records["recordsFiltered"] = $logNum;
        
        return  json_encode($records);
    }

    /**
     * 获取所有的日志模块
     * @return array
     */
    private function getLogModulePath()
    {
        $logModules = [];
        $dir = Xphp::$_config['LOG_PATH'];
        if (!is_dir($dir)) {
            return $logModules;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $filepath = $dir . '/' . $file;
            // 日志模块只能是目录，不能是文件
            if (is_file($filepath) || !is_dir($filepath)) {
                continue;
            }
            $logModules[] = $filepath;
        }
        return $logModules;
    }

    /**
     * 获取日志文件列表
     * @return array
     */
    private function getLogList()
    {
        // 获取系统日志模块
        $logModulePaths = $this->getLogModulePath();
        if (!$logModulePaths) {
            return [];
        }

        $logList = [];
        // 分别获取各个模块下的日志文件
        foreach ($logModulePaths as $logModulePath) {
            if (!is_dir($logModulePath)) {
                continue;
            }
            $files = scandir($logModulePath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $filepath = $logModulePath . '/' . $file;
                // 日志文件只能在模块下的一级文件，不会去遍历子目录
                if (is_dir($filepath) || !is_file($filepath)) {
                    continue;
                }
                $logList[] = [
                    'create_time' => filectime($filepath),
                    'filesize' => filesize($filepath),
                    'modify_time' => filemtime($filepath),
                    'filepath' => $filepath,
                    'filename' => $file,
                ];
            }
        }
        return $logList;
    }
    
    /**
     * 得到日志下载包路径
     * 涉及到节点,所以只能通过读取文件内容来处理
     * @param unknown $params
     */
    public function getDownLoadPackageName($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_system_log_download");
        $nodeuuid = $params['nodeuuid'];
        $ids = $params['id'];
        $this->paramsCheck($nodeuuid, $ids);
        $tmpPath = Xphp::$_config['TMP_PATH'];
        // 单次下载文件的大小跟随配置文件，默认为8MB
        $fileBuffer = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];

        $filenameArr = array();
        // 获取系统日志文件列表
        $logList = $this->getLogList();
        $opName = 'NODE_SYS_OP_PREAD_FILE';
        // 将指定<天>的日志文件复制到tmp目录下
        foreach ($logList as $fileInfo) {
            foreach ($ids as $id) {
                if (strpos($fileInfo['filename'], $id) !== false) {
                    $filesize = $fileInfo['filesize'];
                    $tmpFilename = $tmpPath . '/' . $fileInfo['filename'];
                    // 这里采用分块写，一次写太多内存会超出
                    $fp = fopen($tmpFilename, 'a+');
                    for ($i = 0; $i < $filesize; $i += $fileBuffer) {
                        if ($filesize - $i <= $fileBuffer) {
                            $readLen = $filesize - $i;
                        } else {
                            $readLen = $fileBuffer;
                        }
                        $msg = array(
                            'file_path' => $fileInfo['filepath'],
                            'offset' => $i,
                            'length' => $readLen,
                        );
                        $readBuffer = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
                        fwrite($fp, $readBuffer);
                    }
                    fclose($fp);
                    $filenameArr[] = $fileInfo['filename'];
                }
            }
        }

        if(!empty($filenameArr)){
            // 将上面的日志文件打包压缩，并返回下载路径
            $filenameStr = implode(" ", $filenameArr);
            $cmd = "cd " . $tmpPath;
            $cmd .= ";rm -rf system_log.zip";
            $cmd .= ";zip -q -r -m " . $tmpPath . "system_log.zip " . $filenameStr;
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = array('command'=>$cmd);
            $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
            if($mbResult['result']){
                $systemHandler = Xphp::instance('SystemHandler');
                $nodeHandler = Xphp::instance('NodeHandler');
                $nodeuuid = $nodeHandler->getLocalNodeUUID();
                $url = $systemHandler->groupUnifyDownloadUrl($nodeuuid, Xphp::$_config['TMP_PATH'] . "system_log.zip");
                
                return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_LOG_PACKAGE_FILE'], "", "", 0, $url);
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
     * @param int $tasktype    任务类型
     * 告警/存储要调用
     */
    public function getModuleTypeDes($module, $subModule = null, $tasktype = null){
        $desConf = Xphp::$_pfdes;
        $des = $desConf['MODULE_TYPE_DES'][$module];
        if($module == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
            if($tasktype){
                if($tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                    $des = Xphp::$_lang['UI_PLATFORM_ARCHIVE'];
                }
            }
        }
        if($subModule){
            //添加子模块,暂时添加虚拟机子模块,后期TODO涉及到数据库子模块
            if($module == Xphp::$_config['MODULE_TYPE']['VM']){
//                 $des .= "[" . Xphp::$_config['VMHYPERVISORDES'][$subModule] . "]";
                if (in_array($subModule, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
                    $des = Xphp::$_lang['WEB_PLATFORM_DES_PUBLIC_CLOUD'];
                } else if (in_array($subModule, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
                    // 副本没有私有云
                    if($tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'] || $tasktype == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $tasktype == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                        $des = Xphp::$_lang['WEB_PLATFORM_DES_VM'];
                    }else{
                        $des = Xphp::$_lang['WEB_PLATFORM_DES_PRIVATE_CLOUD'];
                    }
                } else {
                    $des = Xphp::$_lang['WEB_PLATFORM_DES_VM'];
                }
            }
            //还需添加文件子模块
            if($module == Xphp::$_config['MODULE_TYPE']['FS']){
                if($subModule == Xphp::$_config['SUBMODULE_TYPE']['HADOOP']){
                   
                    $des = "Hadoop";
                }elseif ($subModule == Xphp::$_config['SUBMODULE_TYPE']['OBS']){
                    $des =  Xphp::$_lang['UI_PLATFORM_OBS'];
                }
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
        $confDes = Xphp::$_pfdes;
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
    public function getLogDesription($logType, $errorCode, $desription, $descriptionParam, $logLevel = null, $logId = null, $errorDetail = null, $moduleType = 0, $subModuleType = 0, $scriptDetail = null){
        // var_dump($logType, $errorCode, $desription, $descriptionParam, $logLevel);
        $confDes = $this->getLogConf($logType);
        $desStr = $confDes[$desription];
        if($errorCode){
            $errorConf = Xphp::$_error;
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $errorClass = $this->getLogLevelClass($logLevel);
            $desStr .= ",[" . '<span  class="' . $errorClass . '">#' . $errorCode . '</span>' . "]" . $errorStr;
        }
        // 公有云替换描述中的"虚拟机"为"实例","磁盘"为"卷"
        if (Xphp::$_config['MODULE_TYPE']['VM'] == $moduleType && in_array($subModuleType, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
            $desStr = str_replace(Xphp::$_lang['WEB_PLATFORM_DES_VM'], Xphp::$_lang['WEB_PLATFORM_DES_INSTANCE'], $desStr);
            $desStr = str_replace(Xphp::$_lang['WEB_PLATFORM_DES_DISK'], Xphp::$_lang['WEB_PLATFORM_DES_VOL'], $desStr);
        }
        $batchDelete = array('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', 'SYSTEM_LOG_DELETE_ARCHIVE_BATCH_TIMEPOINT', 'SYSTEM_LOG_DELETE_COPY_BATCH_TIMEPOINT', 'BD_SYSTEMLOG_DESC_KEY_DEL_TASK_ORCHESTRATION_PLAN');
        // 多个参数合并到一个%s的情况
        $multiInOne = ['VM_TASK_DESC_KEY_BACKUP_TASK_ADD_VM', 'VM_TASK_DESC_KEY_BACKUP_TASK_REMOVE_VM'];
        if ($descriptionParam) {
            $param = json_decode($descriptionParam, true);
            if (in_array($desription, $multiInOne)) {
                $desArr = explode("'%s'", $desStr);
                $desStr = "";
                $desStr .= $desArr[0];
                $paramArr = [];
                foreach ($param as $p) {
                    $paramArr[] = "'" . $this->getEachParamsDes($p, $param) . "'";
                }
                $desStr .= implode(',', $paramArr);
            } else {
                $desArr = explode("%s",
                    $desStr
                );
                $desStr = "";
                $i = 0;
                foreach ($desArr as $k => $v) {
                    $i++;
                    if ($i == 1 && in_array($desription, $batchDelete)) {
                        $desStr .= $v . $this->getEachParamsDes($param[$k], $param, false);
                    } else {
                        $desStr .= $v . $this->getEachParamsDes($param[$k], $param);
                    }
                }
            }
            if( $param && count($param)>2 && in_array($desription, $batchDelete)) {
                for ($i = 2; $i <= count($param); $i++) {
                    $parts = explode(':', $param[$i]);
                    if (count($parts) > 1) {
                        $value = $parts[1];
                        $desStr .= ';<span  class="font-green">' .$value . '</span>';
                    }
                }
            }
        }
        if ($errorDetail) {
            try {
                $errorDetail = json_decode($errorDetail, true);
                if ($errorDetail) {
                    $desStr .= ', <u class="text-success error-detail-link" data-id="' . $logId . '" style="cursor: pointer">'
                        . Xphp::$_lang['WEB_LOG_TASK_ERROR_DETAIL_LINK'] . '</u>';
                }
            } catch (Exception $e) {}
        }
        if ($scriptDetail) {
            try {
                $scriptDetail = json_decode($scriptDetail, true);
                if ($scriptDetail) {
                    $desStr .= ', <u class="text-success script-detail-link" data-id="' . $logId . '" style="cursor: pointer">'
                        . Xphp::$_lang['WEB_LOG_TASK_SCRIPT_DETAIL_LINK'] . '</u>';
                }
            } catch (Exception $e) {}
        }
        return $desStr;
    }
    
    /**
     * 得到日志错误对应的类型
     * @param unknown $logLevel
     */
    private function getLogLevelClass($logLevel){
        $logClass = "font-green";   //一般日志
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
                $desStr .= $v . $this->getEachParamsDes($param[$k], $param,false);
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
    public function getEachParamsDes($eachParams,$descriptionParam,$classShowFlag = true){
        if(empty($eachParams)){
            return "";
        }
        $des = "";
        $arr = explode(":", $eachParams, 2);
        switch($arr[0]){
            case "S":
                if($classShowFlag){
                    $des = '<span  class="font-green">' . $arr[1] . '</span>';
                }else{
                    $des = $arr[1];
                }
                break;
            case "module_type":
                $des = $this->logModuleTypeDes($arr[1]);
                break;
            case "backup_mode":
                $des = $this->logBackupModeDes($arr[1],$descriptionParam);
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
        $desConf = Xphp::$_pfdes;
        return $desConf['MODULE_TYPE_DES'][intval($index)];
    }
    
    /**
     * 得到日志备份模式描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logBackupModeDes($index,$descriptionParam){
        $desConf = Xphp::$_pfdes;
        $moduleType = explode(":", $descriptionParam[1],2);
        if($moduleType[0]=='module_type' && $moduleType[1] == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            return "";
        }else{
            return $desConf['BACKUP_MODE_DES'][intval($index)];
        }
    }
    
    /**
     * 根据类型得到日志配置文件
     * @param int $logType  日志类型   系统日志2/任务日志1
     * @return array 
     */
    public function getLogConf($logType){
        if($logType == Xphp::$_config['LOGTYPE']['SYSTEM']){
            return include CONF_PATH . 'log_system.php';
        }elseif ($logType == Xphp::$_config['LOGTYPE']['TASK']){
            return include CONF_PATH . 'log_task.php';
        }
    }
    
    /**
     * 删除系统日志
     * @param {} $params
     * @return string
     */
    public function deleteSystemLog($params){
        $id = $params['id'];
        $tenantFlag = $params['tenantFlag']; //删除租户标志
        //权限检查
        if(!$tenantFlag){
            $roleHandler = Xphp::instance('RoleHandler');
            $roleHandler->pOperationPermissionCheckExit("p_system_log_delete");
            $this->paramsCheck($id);
        }
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }

        $alamId = "(" . implode(',', $idArray) . ")";
        $data = $this->dbSelect("select unix_timestamp(op_time) AS op_time from bd_system_log where id in {$alamId}");
        // 判断下是否符合保留策略
        $utils = Xphp::instance("Utils");
        $leastDays = $utils->xphp_get_system_data_safe(3);
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'op_time');
        switch ($reserveType) {
            case 1:
                // 按数量保留
                $countSql = "SELECT * from bd_system_log ORDER BY op_time desc limit 0,{$reserveNum}";
                $taskCount = $this->dbSelect($countSql); //删除前数量
                $idArr = array_column($taskCount, 'id');
                foreach($params['id'] as $v){
                    if (in_array($v, $idArr)) {
                        // 那么不允许删除
                        $msg = Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS'];
                        $msg = str_replace('%S%', $reserveNum, $msg);
                        return $this->muOpResult(
                            false,
                            Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                            $msg
                        );
                    }
                }
                break;
            case 2:
                // 按时间保留
                $msg = str_replace('%S%', $reserveNum, Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS']);
                if (time() - min($daysArr) < $reserveNum * 24 * 3600) {
                    // 那么不允许删除
                    return $this->muOpResult(
                        false,
                        Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                        $msg
                    );
                }
                break;
            case 3:
                // 永久保留，那么不允许删除
                $msg = Xphp::$_lang['WEB_LOG_DELETE_CAN_NOT_DELETE_TIPS'];
                return $this->muOpResult(
                    false,
                    Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                    $msg
                );
        }
        $opName = 'BD_LOG_OP_SYSTEM_DELETE';
        $msg = ['id_list' => $idArray];
        return $this->unifyMsg($opName, json_encode($msg));
    }
    
    /**
     * 删除任务日志
     * @param {} $params
     * @return string
     */
    public function deleteTaskLog($params){
        $id = $params['id'];
        $tenantFlag = $params['tenantFlag']; //删除租户标志
        if(!$tenantFlag){
            //权限检查
            $roleHandler = Xphp::instance('RoleHandler');
            $roleHandler->pOperationPermissionCheckExit("p_job_log_delete");
            $this->paramsCheck($id);
        }
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }

        // 关联管理用户判断 存储资源 - 操作 log_operate
        $ids = implode(',', $idArray);
        $data = $this->dbSelect("select user_uuid,unix_timestamp(op_time) op_time from bd_task_log where id in ($ids)");
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['log_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], array_column($data, 'user_uuid'), $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }

        // 判断下是否符合保留策略
        $leastDays = $utils->xphp_get_system_data_safe(2);
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'op_time');
        if ($reserveType == 3) {
            // 永久保留，那么不允许删除
            $msg = Xphp::$_lang['WEB_LOG_DELETE_CAN_NOT_DELETE_TIPS'];
            return $this->muOpResult(
                false,
                Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                $msg
            );
        } else if ($reserveType == 2) {
            // 按时间保留
            if (time() - min($daysArr) < $reserveNum * 24 * 3600) {
                // 那么不允许删除
                $msg = Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'];
                $msg = str_replace('%S%', $reserveNum, $msg);
                return $this->muOpResult(
                    false,
                    Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                    $msg
                );
            }
        } else if ($reserveType == 1) {
            // 按数量保留
            $countSql = "SELECT * from bd_task_log where running_flag = 2 ORDER BY op_time desc limit 0,{$reserveNum}";
            $taskCount = $this->dbSelect($countSql); //删除前有多少任务日志
            $idArr = array_column($taskCount, 'id');
            foreach($params['id'] as $v){
                    if (in_array($v, $idArr)) {
                    // 那么不允许删除
                    $msg = Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS'];
                    $msg = str_replace('%S%', $reserveNum, $msg);
                    return $this->muOpResult(
                        false,
                        Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                        $msg
                    );
                }
            }
        }

        // $this->checkTaskLogTime($idArray, $tenantFlag); //检查只能删除半年以前的日志
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
        $this->writelogLog($opName, json_decode($jsonMsg,true), $mbResult);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    private function writelogLog($opName, $msg, $mbResult){
        $key = "";
        $params = array(count($msg['id_list']));
        if($opName == "BD_LOG_OP_TASK_DELETE"){
            $key = "SYSTEM_LOG_DELETE_TASK_LOG";
        }else if($opName == "BD_LOG_OP_SYSTEM_DELETE"){
            $key = "SYSTEM_LOG_DELETE_SYSTEM_LOG";
        }
        
        if($mbResult['result']){
            $this->systemLog($key, $params);
        }else{
            $this->systemLog($key, $params,  Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
        }
    }
    
    /**
     * 删除日志检查是否为半年前的日志
     * @param array $ids
     */
    private function checkTaskLogTime($ids, $tenantFlag){
        //删除租户时不检查
        if($tenantFlag) return;
        $idStr = implode("','", $ids);
        $sql = "select unix_timestamp(op_time) op_time from bd_task_log where id in ('".$idStr."')";
        $data = $this->dbSelect($sql, array());
        foreach ($data as $d){
            $halfyear = 365/2 * 24 * 3600 ;
            $time = time() - intval($d['op_time']);
            if($time < $halfyear){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'], Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'],'warning'));
            }
        }
    }
    
    /**
     * 删除日志检查是否为半年前的日志
     * @param array $ids
     * @param bool $tenantFlag 
     * @param
     */
    private function checkSystemLogTime($ids, $tenantFlag){
        //删除租户时不检查
        if($tenantFlag) return;
        $idStr = implode("','", $ids);
        $sql = "select unix_timestamp(op_time) op_time from bd_system_log where id in ('".$idStr."')";
        $data = $this->dbSelect($sql, array());
        foreach ($data as $d){
            $halfyear = 365/2 * 24 * 3600 ;
            $time = time() - intval($d['op_time']);
            if($time < $halfyear){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'], Xphp::$_lang['WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'],'warning'));
            }
        }
    }

    //获取系统操作日志类型
    private function getOpType($des){
        $res = 'UI_PLATFORM_SYSTEM_SET';
        if(strpos($des, 'SYSTEM_USER_LOGIN') !== false){
            $res = 'UI_PLATFORM_SYSTEM_LOGIN'; // 登录
        } else if(strpos($des, 'SYSTEM_USER_LOGINOUT') !== false){
            $res = 'UI_USER_LOGIN_OUT'; // 退出登录
        } else if(strpos($des, 'SYSTEM_USER') !== false){
            $res = 'UI_ORGAN_USER_MANAGE'; // 用户管理
        } else if(strpos($des, 'BD_SYSTEMLOG_DESC_KEY_NODE') !== false){
            $res = 'UI_VCENTER_ALLOCATION_NODE'; // 备份节点
        } else if(strpos($des, 'BD_SYSTEMLOG_DESC_KEY_AGENT_') !== false){
            $res = 'UI_DB_AGENT_MANAGE'; // 客户端
        } else if(strpos($des, 'BD_SYSTEMLOG_DESC_KEY_STORAGE') !== false){
            $res = 'UI_DATACENTER_BACKUP_STORAGE'; // 存储
        }
        return Xphp::$_lang[$res];
    }
    
    //获取系统时间
    public function getSystemTime() {
        $systemHandler = Xphp::instance('SystemHandler');
        $nowTime = $systemHandler->getSystemTime();
        return $nowTime;
    }

    /**
     * @param $params
     * @return string
     */
    public function getRunningLogDetails($params): string
    {
        $logId = $params['log_id'];
        $sql = "SELECT error_detail FROM bd_task_log WHERE id = ? ";
        $data = $this->dbSelect($sql, [$logId]);
        return $data[0]['error_detail'];
    }

    public function getRunningLogScriptDetails($params): string
    {
        $logId = $params['log_id'];
        $sql = "SELECT script_detail FROM bd_task_log WHERE id =? ";
        $data = $this->dbSelect($sql, [$logId]);
        return $data[0]['script_detail'];
    }
}
?>