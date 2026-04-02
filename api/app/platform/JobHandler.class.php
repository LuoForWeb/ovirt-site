<?php
/*******************************************
** 任务管理处理类
**
** @author       xiezhuowei@vinchin.com
** @date         2015-04-10 下午15:16:44
** @version      1.0.0
** @copyright    Copyright 2015 vinchin.com
********************************************/
class JobHandler extends OPHandler{
    /**
     * 得到当前所有任务信息
     * @param unknown $params
     */
    public function getCurrentJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', '', 'bt.task_type', 'bt.create_time',
            'bt.task_status', 'bri.speed','bri.total_object_completed_size', 'bu.user_name', ''
        );
        $utils = Xphp::instance('Utils');
        $taskName = $utils->escapeWildcard($search['name']);
        $accurateFlag = $params['accurateFlag'];
//         任务名	模块类型	任务类型	创建时间	状态	速度	创建者	操作
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time, 
                        bt.task_status, bt.strategy_id, bu.user_name,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time,
                        bri.total_object_valid_size,bri.total_object_completed_valid_size
                from bd_running_info bri, bd_user bu, bd_task bt 
                     left join sr_sure_backup ssb on bt.task_uuid = ssb.task_uuid 
                where bt.task_uuid = bri.task_uuid and 
                	 bt.user_uuid = bu.user_uuid and 
                     bt.delete_flag = ? ";
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu 
                    where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid ";

        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        if($this->checkEmpty($taskName) && !$accurateFlag){
            //按任务名搜索
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }

        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bu.user_uuid = ? ";
            $sqlCount .= "and bu.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }
        if(!empty($accurateFlag)){
        	$search = $params['search'];
        	$hypervisor = intval($search['hypervisor']);
        	$dbType = intval($search['dbtype']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
        	$taskStatus =intval($search['taskStatus']);
        	$taskName = $utils->escapeWildcard($search['taskName']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	$nodeuuid = $search['nodeuuid'];

        	//虚拟化 根据虚拟化类型搜索
        	if(($moduleType && $moduleType == Xphp::$_config['MODULE_TYPE']['VM']) || !empty($hypervisor)){
        		$sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, bu.user_name,
                        bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time
                    from bd_running_info bri, bd_user bu, vm_task vt, bd_task bt left join sr_sure_backup ssb on bt.task_uuid = ssb.task_uuid
                    where bt.task_uuid = vt.task_uuid  
                        and bt.task_uuid = bri.task_uuid 
                        and bt.user_uuid = bu.user_uuid 
                        and bt.delete_flag = 2 and bu.user_uuid = ? ";

        		$sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu, vm_task vt where bt.task_uuid = vt.task_uuid and bt.user_uuid = bu.user_uuid  and  bt.delete_flag = 2 and bu.user_uuid = ? ";
        		$sqlParams = array(Xphp::$_user['useruuid']);
        		$sqlCountParams = array( Xphp::$_user['useruuid']);
        		if($hypervisor != 0){
        			$sql .= " and vt.hypervisor_type =? ";
        			$sqlCount .= " and vt.hypervisor_type =? ";
        			$sqlParams = array_merge($sqlParams, array($hypervisor));
        			$sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
        		}
        	}else if(($moduleType && $moduleType == Xphp::$_config['MODULE_TYPE']['DB']) || !empty($dbType)){
        	    //数据库 根据数据库类型搜索
        	    $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time,bu.user_name
                    from bd_task bt, bd_running_info bri, bd_user bu, db_task dt
                    where bt.task_uuid = dt.task_uuid 
                        and bt.task_uuid = bri.task_uuid 
                        and bt.user_uuid = bu.user_uuid 
                        and bt.delete_flag = 2 and bu.user_uuid = ? ";
    	        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu, db_task dt where bt.task_uuid = dt.task_uuid and bt.user_uuid = bu.user_uuid  and  bt.delete_flag = 2 and bu.user_uuid = ? ";
    	        $sqlParams = array(Xphp::$_user['useruuid']);
    	        $sqlCountParams = array(Xphp::$_user['useruuid']);
    	        if($dbType != 0){
    	            $sql .= " and dt.db_type = ? ";
    	            $sqlCount .= " and dt.db_type = ? ";
    	            $sqlParams = array_merge($sqlParams, array($dbType));
    	            $sqlCountParams = array_merge($sqlCountParams, array($dbType));
    	        }
        	}

        	//默认备份恢复包含vm/fs/db/os/nas
        	$tasktypeList = array($taskType);
        	if(empty($moduleType)){
        	    if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
        	        $tasktypeList = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['TASKTYPE']['DB_BACKUP'], Xphp::$_config['TASKTYPE']['OS_BACKUP'],Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']);
        	    }else if($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']){
        	        $tasktypeList = array(Xphp::$_config['TASKTYPE']['RECOVERY'], Xphp::$_config['TASKTYPE']['DB_RECOVERY'], Xphp::$_config['TASKTYPE']['OS_RECOVERY'],Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']);
        	    }
        	}

        	//模块类型
        	if(!empty($moduleType)){
        	    $sql .= " and bt.module_type = ? ";
        	    $sqlCount .= " and bt.module_type = ? ";
        	    $sqlParams = array_merge($sqlParams, array($moduleType));
        	    $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
        	}

        	$tasktypeListDes = implode(',', $tasktypeList);
        	//任务类型
        	if(!empty($taskType)){
        	    $sql .= " and bt.task_type in (".$tasktypeListDes.") ";
        	    $sqlCount .= " and bt.task_type in (".$tasktypeListDes.") ";
        	}
        	//任务状态
        	if(!empty($taskStatus)){
        	    $sql .= " and bt.task_status = ? ";
        	    $sqlCount .= " and bt.task_status = ? ";
        	    $sqlParams = array_merge($sqlParams, array($taskStatus));
        	    $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
        	}

        	//任务名
        	if($this->checkEmpty($taskName)){
        	    $sql .= " and bt.task_name like ? ";
        	    $sqlCount .= " and bt.task_name like ? ";
        	    $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
        	    $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        	}

        	if(!empty($nodeuuid)){
        	    $sql .= " and bt.node_uuid = ? ";
        	    $sqlCount .= " and bt.node_uuid = ? ";
        		$sqlParams = array_merge($sqlParams, array($nodeuuid));
        		$sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
        	}

        	//如果填了开始时间范围查询
        	if(!empty($startTime) && !empty($endTime)){
        		$sql .= " and bt.create_time between ? and ? ";
        		$sqlCount .= " and bt.create_time between ? and ? ";
        		$sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        		$sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
        	}
        }
        $sql .= " order by  $sortArr[$sortColumn]  $sortType , bt.id desc limit ? , ? ";

        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';

        $records["data"] = array();
        foreach ($data as $d){
            $taskType = intval($d['task_type']);
            $progress = $this->getCurrentTaskProgress($d);
            $records["data"][] = array(
                $d['task_name'],
                $this->getCurrentJobsModuleType($d['module_type'], $d['task_uuid'], intval($d['task_type'])),
                $this->getTaskNameString($d['module_type'],$taskType),
                $this->parseDate($d['create_time']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $progress,
                $d['user_name'],
                $this->getTaskOpCodeByType($d['task_uuid'],$taskType, $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),  //10
            	$d['module_type'],
            	$d['task_status'],
            	$this->getBackupStrategy($d['strategy_id']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $this->getTaskRunStage($d['task_uuid'],$d['module_type']),  //获取任务执行阶段，目前仅卷CDP使用
                "archive_flag" => $this -> getArchiveFlag($d['task_uuid'],$d['module_type']),
            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return  json_encode($records);
    }

     /**
     * 是否开启归档
     * @param unknown $task_uuid
     * @param unknown $moduleType
     */
    private  function getArchiveFlag($task_uuid,$moduleType){
        if($moduleType == Xphp::$_config['MODULE_TYPE']['FS']){
            $sql = "SELECT file_archive_flag from fs_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($task_uuid));
            return $data[0]['file_archive_flag'] != 2 ? true : false;
        } else if($moduleType == Xphp::$_config['MODULE_TYPE']['NAS']) {
            $sql = "SELECT file_archive_flag from nas_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($task_uuid));
            return $data[0]['file_archive_flag'] != 2 ? true : false;
        }else {
            return false;
        }
    }

    /**
     * 获取任务运行阶段
     * @param unknown $task_uuid
     * @param unknown $moduleType
     */
    private  function getTaskRunStage($task_uuid,$moduleType){
        $taskCurrentStage = 0;
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            $sql = "SELECT current_task_running_stage from cdp_vol_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($task_uuid));
            $taskCurrentStage = 0; //任务阶段
            if(!empty($data)){
                $taskCurrentStage = $data[0]['current_task_running_stage'];
            }
        }
        return $taskCurrentStage;
    }

    /**
     * 得到当前任务中每个任务的进度,public方法，其他地方也调用，如大屏
     * @param array $d
     * @return string $progress
     */
    public function getCurrentTaskProgress($d){
        $taskType = intval($d['task_type']);
        $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']);
        //数据验证任务单独获取状态
        if($taskType== Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
            $progress = $this->getSureBackupProgress($d['task_status'], $d['task_progress'], false);
        }
        //卷CDP备份和恢复
        if($taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] || $taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'] 
            || $taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] || $taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
            $volCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';

            $totalObjectSize = $d['total_object_size'];
            $completedValidSize = $d['total_object_completed_valid_size'];

            $taskRunningStage = $volCdpHandler->getVolCdpTaskRunningStage($d['task_uuid']);

            $currentSizeValue = $volCdpHandler->getTaskTotalCapacityInfo($d['task_uuid']);
            $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $currentSizeValue, false, $d['task_type']);
            if($taskRunningStage==$volCdpDes['TASK_RUNNING_STAGE']['REALTIME_SYNC']
                || $taskRunningStage==$volCdpDes['TASK_RUNNING_STAGE']['WAIT_CONVERT_TO_REALTIME_SYNC']
                || $taskRunningStage==$volCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']){
                $progress = Xphp::$_config['NULLSPACE'];
            }
        }
        if($d['module_type'] == Xphp::$_config['MODULE_TYPE']['FS'] || $d['module_type'] == Xphp::$_config['MODULE_TYPE']['NAS']) {//文件当前任务进度
            $progress = $this->getFileBackupProgress($d['task_status'],$d['total_object_size'], $d['total_object_completed_size'],$d['task_uuid']);
        }
        return $progress;
    }


    /**
     * 获取nas和文件的当前任务进度   扫描中不显示
     * @return string
     */
    private function getFileBackupProgress($task_status,$totalSize, $currentSize,$task_uuid){
       if($task_status != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $task_status != Xphp::$_config['TASKSTATUS']['PAUSED'] &&
            $task_status != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //如果任务没有运行
                return Xphp::$_config['NULLSPACE'];
        }
        $sql = "select task_status, count(agent_uuid) as count from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        foreach($data as $d) {
            $agent_status = $ptDes['AGENT_TASK_STATUS'][$d['task_status']];
            //客户端数量等于1，并且是传输中 总进度显示
            if ($d['count'] == 1 && $agent_status == Xphp::$_lang['WEB_PLATFORM_DES_RUNNING']) {
                $utils = Xphp::instance('Utils');
                $speed = $utils->calPercent($totalSize, $currentSize);
                //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
                $speed = sprintf("%.2f",substr($speed, 0, -1)) . "%";
                return $speed;
            }else {
                return Xphp::$_config['NULLSPACE'];
            }
        }
    }

    /**
     * 当前任务中的任务类型修改部分描述,在任务中的当前任务虚拟机  文件  数据库中用
     * @param unknown $module_type  模块类型
     * @param unknown $task_type  任务类型
     * @author liushuai@vinchin.com
     * @return string
     */
    public function getTaskNameString($module_type,$task_type){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $task_name_string = "";
        if($module_type == Xphp::$_config['MODULE_TYPE']['DB']){//如果为数据库
            if($task_type == Xphp::$_config['TASKTYPE']['DB_BACKUP']){
                $task_name_string = Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['DB_RECOVERY']){
                $task_name_string = Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'];
            }else{
                $task_name_string = $ptDes['TASKTYPEDES'][$task_type];
            }
        }else if($module_type == Xphp::$_config['MODULE_TYPE']['OEM_DBCDP']){//如果为数据库实时
            if($task_type == Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP']){
                $task_name_string = "CDP";
            }else if($task_type == Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY']){
                $task_name_string = Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'];
            }else{
                $task_name_string = $ptDes['TASKTYPEDES'][$task_type];
            }
        }else if($module_type == Xphp::$_config['MODULE_TYPE']['OEM_FSCDP']){
            if($task_type == Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP']){
                $task_name_string = Xphp::$_lang['WEB_PLATFORM_DES_REAL_TIME_SYN'];
            }else{
                $task_name_string = $ptDes['TASKTYPEDES'][$task_type];
            }
        }else if($module_type == Xphp::$_config['MODULE_TYPE']['OS']){
            if($task_type == Xphp::$_config['TASKTYPE']['OS_BACKUP']){
                $task_name_string = Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY']){ //瞬时恢复
                $task_name_string =  $ptDes['TASKTYPEDES'][$task_type];
            }else if($task_type == Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY_MOTION']){//操作系统迁移
                $task_name_string =  $ptDes['TASKTYPEDES'][$task_type];
            }
            else{
                $task_name_string = Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'];
            }

        }else{
            $task_name_string = $ptDes['TASKTYPEDES'][$task_type];
        }
        return $task_name_string;
    }

    /**
     * 获取备份时间策略
     * @param string $strategyID
     */
    private function getBackupStrategy($strategyID){
    	$sql = "select unix_timestamp(start_time) start_time, mode,strategy_type from bd_time_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql,array($strategyID));
    	$info = array();
    	$type = 0;
    	$flag = false;
        foreach ($data as $d){
            //只有一条增量则是永久增量
            $info[] = 1 == count($data) && Xphp::$_config['BACKUP_MODE']['INCREMENTAL'] == $data[0]['mode'] ? Xphp::$_config['BACKUP_MODE']['PINCREMENTAL'] : intval($d['mode']);
            if($d['strategy_type'] == 4){
                $type = intval($d['strategy_type']);
                if(intval($d['start_time']) <= time()){
                    $flag = true;
                }
            }
        }
    	$strategy = array(
    		'modeList' => $info,
    		'type' => $type,
    		'timeout' => $flag
    	);
    	return $strategy;
    }

    /**
     * 得到当前所有的演练任务
     * @param unknown $params
     * @return string
     */
    public function getCurrentOrchJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.module_type', 'bt.task_type', 'bt.create_time',
            'bt.task_status', 'op.plan_name', 'bu.user_name', 'bt.task_status'
        );

        $module = $search['module'];
        //         任务名	模块类型	任务类型	创建时间	状态	速度	创建者	操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id,
                		bri.speed, bri.speed_time, bu.user_name, 
                        ot.recovery_mode, ot.hypervisor_type,  op.plan_name 
                from bd_task bt, bd_running_info bri, bd_user bu, orch_task ot, orch_plan op  
                where bt.task_uuid = bri.task_uuid and
                	 bt.user_uuid = bu.user_uuid and 
                     bt.task_uuid = ot.task_uuid and 
                     ot.plan_uuid = op.plan_uuid and 
                     bt.delete_flag = 2 and bt.task_type = ? ";
        $sqlCount = "select count(id) as total from bd_task where delete_flag = 2 and task_type = ? ";

        $sqlParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK']);
        $sqlCountParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK']);

        $sql .= "and bu.user_uuid = ? ";
        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount .= "and user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid'], $start, $length));
        $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        foreach ($data as $d){
            $records["data"][] = array(
                $d['task_name'],
                Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
                $vmDes['OrchRecoveryMode'][$d['recovery_mode']],
                $this->parseDate($d['create_time']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $d['plan_name'],
                $d['user_name'],
                $this->getTaskOpCode($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
            );
        }

        $utils = Xphp::instance('Utils');
        $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序

        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到模块的详细描述信息,主要是虚拟机模块要得到子模块号信息
     * @param int $moduleType
     * @param string $taskuuid
     * @param int $taskType
     */
    private function getCurrentJobsModuleType($moduleType, $taskuuid, $taskType){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $moduleTypeDes = $ptDes['MODULE_TYPE_DES'][$moduleType];
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            //数据验证任务
            if($taskType == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                $sql = "select hypervisor_type from sr_sure_backup where task_uuid = ?";
                $data = $this->dbSelect($sql, array($taskuuid));
            }
            $hypervisor = intval($data[0]['hypervisor_type']);
//             $moduleTypeDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$hypervisor] . "]";
            $moduleTypeDes = Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
        }else if($moduleType == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
            if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                //副本
                $moduleTypeDes = Xphp::$_lang['WEB_PLATFORM_DES_COPY'];
            }else if($taskType == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $taskType == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                //归档
                $moduleTypeDes = Xphp::$_lang['UI_PLATFORM_ARCHIVE'];
            }
        }
        return $moduleTypeDes;
    }

    /**
     * 得到任务速度
     * @param int $status
     * @param int $speed
     * @return string
     */
    public function getCurrentJobSpeed($status, $speed, $speedTime){
        if(time() - $speedTime > 12){
            $speed = 0;
        }
        if($status == Xphp::$_config['TASKSTATUS']['RUNNING'] || $status == Xphp::$_config['TASKSTATUS']['ABNORMAL'] ){
            $utils = Xphp::instance('Utils');
            return $utils->calSpeed($speed);
        }else{
            return Xphp::$_config['NULLSPACE'];
        }
    }

    /**
     * 得到每个任务的操作控制辅助信息,用于链接到任务详情和任务控制
     * @param array $d
     * @return array
     */
    private function getOpInfo($d){
        $opInfo = array(
            'uuid'=>$d['task_uuid'],
            'module'=>intval($d['module_type']),
            'taskType'=>intval($d['task_type']),
            'subModule'=>0,
            'status' => intval($d['task_status']),
            'tenantuuid' => $_SESSION['tenantuuid']
        );
        //TODO 根据不同模块类型 ,添加不一样的子模块信息或其他信息
        if(Xphp::$_config['MODULE_TYPE']['VM'] == intval($d['module_type']) ||
           Xphp::$_config['MODULE_TYPE']['VDDT_SERVER'] == intval($d['module_type'])){
            $opInfo['subModule'] = $this->getVMTaskHypervisor($d['task_uuid']);
        }

        //演练任务,增加恢复模式
        if(Xphp::$_config['TASKTYPE']['ORCH_TASK'] == intval($d['task_type'])){
            $opInfo['recoveryMode'] = intval($d['recovery_mode']);
            $opInfo['subModule'] = intval($d['hypervisor_type']);
        }

        //数据库任务
        if(Xphp::$_config['MODULE_TYPE']['DB'] == intval($d['module_type'])){
            $opInfo['subModule'] = $this->getDBTaskHypervisor($d['task_uuid']);
        }
        //副本
        if(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] == intval($d['module_type'])){
            if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                $opInfo['subModule'] = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_VM_BACKUP_COPY'];  //虚拟机副本子模块
            }else if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY_FETCH']){
                $opInfo['subModule'] = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  //文件副本子模块
            }else if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY_FETCH']){
                $opInfo['subModule'] = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_DB_BACKUP_COPY'];  //数据库副本子模块
            }else if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY_FETCH']){
                $opInfo['subModule'] = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_OS_BACKUP_COPY'];  //操作系统副本子模块
            }else if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY_FETCH']){
                $opInfo['subModule'] = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_OS_BACKUP_COPY'];  //操作系统副本子模块
            }else if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['ARCHIVE'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                //虚拟机归档
                $opInfo['subModule'] = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_VM_ARCHIVE'];
            }


        }

        return $opInfo;
    }

    /**
     * 根据任务UUID得到虚拟机模块的子模块号
     * @param sting $taskuuid
     * @return int
     */
    private function getVMTaskHypervisor($taskuuid){
        $this->paramsCheck($taskuuid);
        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if($data){
            return $data[0]['hypervisor_type'];
        }
        return 0;
    }

    /**
     * 根据任务UUID得到数据库的子模块号
     * @param sting $taskuuid
     * @return int
     */
    private function getDBTaskHypervisor($taskuuid){
        $this->paramsCheck($taskuuid);
        $sql = "select db_type from db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if($data){
            return $data[0]['db_type'];
        }
        return 0;
    }

    /**
     * 得到每个任务的额外信息展示            策略信息/保留策略/存储信息/其他信息
     * @param array $d 每一条数据
     * @return array
     */
    private function getJobOtherInfo($d){
        $info = array();
        $info['timeStrategy'] = $this->getJobTimeStrategy($d['strategy_id']);
        $reservedStrategy = false;
        $recoveryInfo = false;
        $instantInfo = false;
        $motionInfo = false;
        $grainInfo = false;
        $transportStrategy = false;
        $dbCDPInfo = false;
        $dbProtectInfo = false;
        $fileCDPInfo = false;
        if(Xphp::$_config['TASKTYPE']['BACKUP'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['BACKUP_COPY'] == intval($d['task_type']) ||
            Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'] == intval($d['task_type']) ||
            Xphp::$_config['TASKTYPE']['ARCHIVE'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['DB_BACKUP'] == intval($d['task_type']) ||
            Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'] == intval($d['task_type'])
            || Xphp::$_config['TASKTYPE']['OS_BACKUP'] == intval($d['task_type']) ||
            Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY'] == intval($d['task_type'])
            || Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC'] ==  intval($d['task_type'])){
            //如果是备份任务,获取保留策略
            //华为CBR同步也需要保留策略
            	$reservedStrategy = $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']);
        }
        if(Xphp::$_config['TASKTYPE']['RECOVERY'] == intval($d['task_type'])){
            if(Xphp::$_config['MODULE_TYPE']['VM'] == intval($d['module_type'])){
                //如果是恢复任务,增加任务时间点信息显示
                $recoveryInfo = $this->getDesRecoveryInfo($d['task_uuid']);
            }else if(Xphp::$_config['MODULE_TYPE']['FS'] == intval($d['module_type']) || Xphp::$_config['MODULE_TYPE']['NAS'] == intval($d['module_type'])){
                //文件恢复或者nas恢复
                $recoveryInfo = $this->getFsDesRecoveryInfo($d['task_uuid']);
            }

        }
        if(Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] == intval($d['task_type'])){
        	//如果是瞬时恢复任务,
        	$instantInfo = $this->getDesInstantInfo($d['task_uuid']);
        }
        if(Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'] == intval($d['task_type'])){
        	//如果是迁移任务
        	$motionInfo = $this->getDesMotionInfo($d['task_uuid']);
        }
        if(Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] == intval($d['task_type'])){
        	//如果是细粒度恢复任务
        	$grainInfo = $this->getGrainDetailInfo($d['task_uuid']);
        }

        if(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] == intval($d['module_type'])){
        	//如果是副本和归档任务
            $transportStrategy = $this->getTransportInfo($d['task_uuid'],intval($d['task_type']));
        }
        if(Xphp::$_config['TASKTYPE']['DB_RECOVERY'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['DB_BACKUP'] == intval($d['task_type'])){
            //如果是数据库的备份恢复任务
            $transportStrategy = false;
        }
        if(Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'] == intval($d['task_type']) ||
           Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY'] == intval($d['task_type'])){
            //如果是数据库CDP
            $dbCDPInfo = $this->getDbCDPDetail($d['task_uuid'],  $d['task_type']);
        }
        if(Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'] == intval($d['task_type']) ||
            Xphp::$_config['TASKTYPE']['FILE_CDP_RECOVERY'] == intval($d['task_type'])){
                //如果是文件CDP
                $fileCDPInfo = $this->getFileCDPDetail($d['task_uuid'],  $d['task_type']);
        }
        if(Xphp::$_config['TASKTYPE']['DB_BACKUP'] == intval($d['task_type']) ||
            Xphp::$_config['TASKTYPE']['DB_RECOVERY'] == intval($d['task_type'])){
            $dbProtectInfo = $this->getDBProtectDetail($d['task_uuid'], $d['task_type']);
        }
        if(Xphp::$_config['TASKTYPE']['OS_RECOVERY'] == intval($d['task_type'])){
            //主机恢复
            $recoveryInfo = $this->getDesOSRecoveryInfo($d['task_uuid']);
        }
        if(Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY'] == intval($d['task_type'])){
        	//操作系统瞬时恢复任务
        	$instantInfo = $this->getOSInstantInfo($d['task_uuid']);
        }
        if(Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY_MOTION'] == intval($d['task_type'])){
            //操作系统迁移任务详情
            $motionInfo = $this->getOSMotionInfo($d['task_uuid']);
        }

        $info['reservedStrategy'] = $reservedStrategy;
        $info['transportStrategy'] = $transportStrategy;
        $info['recoveryDetail'] = $recoveryInfo;
        $info['instantDetail'] = $instantInfo;
        $info['motionDetail'] = $motionInfo;
        $info['grainDetail'] = $grainInfo;
        $info['dbCDPDetail'] = $dbCDPInfo;
        $info['dbProtectDetail'] = $dbProtectInfo;
        $info['fileCDPInfo'] = $fileCDPInfo;
        //TODO  根据情况,添加额外信息,会发送到界面统一处理
        return $info;
    }

    /**
     * 获取数据库CDP任务的任务详情
     * @param unknown $taskuuid
     * @param unknown $tasktype
     */
    private function getDbCDPDetail($taskuuid, $tasktype){
        $sql = "select cdh1.host_name as productname, cdh1.ip as productip, cdh1.host_uuid as productuuid, 
                		cdh2.host_name as standbyname, cdh2.ip as standbyip, cdh2.host_uuid as standbyuuid, cdt.config 
                from cdp_db_task as cdt 
                inner join cdp_db_host as cdh1 
                on cdt.product_host_uuid = cdh1.host_uuid 
                inner join cdp_db_host as cdh2 
                on cdt.standby_host_uuid = cdh2.host_uuid 
                where cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $backupType = "";
        if(Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'] == $tasktype){
            //如果是备份,添加备份类型,
            $config = json_decode($data[0]['config'], true);
            $backupType = intval($config['standbyhostInfo']['backuptype']);
        }
        $info = array(
            'productdes' => $data[0]['productname'] . "(" . $data[0]['productip'] . ")",
            'standbydes' => $data[0]['standbyname'] . "(" . $data[0]['standbyip'] . ")",
            'productip' => $data[0]['productip'],
            'standbyip' => $data[0]['standbyip'],
            'productuuid' => $data[0]['productuuid'],
            'standbyuuid' => $data[0]['standbyuuid'],
            'tasktype' => intval($tasktype),
            'backuptype' => $backupType
        );
        return $info;
    }

    /**
     * 获取数据库信息
     * @param string $taskuuid
     * @param intval $tasktype
     */
    private function getDBProtectDetail($taskuuid, $tasktype){
        $sql = "select dt.db_type, bt.task_type from bd_task bt, db_task dt where bt.task_uuid = dt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                'dbtype' => $data[0]['db_type'],
                'tasktype' => $data[0]['task_type']
            );

        }

        return $info;
    }

    /**
     * 获取文件CDP任务的任务详情
     * @param unknown $taskuuid
     * @param unknown $tasktype
     */
    private function getFileCDPDetail($taskuuid, $tasktype){
        $sql = "select cdh1.host_name as productname, cdh1.ip as productip, cdh1.host_uuid as productuuid,
                		cdh2.host_name as standbyname, cdh2.ip as standbyip, cdh2.host_uuid as standbyuuid, cdt.config
                from cdp_fs_task as cdt
                inner join cdp_db_host as cdh1
                on cdt.product_host_uuid = cdh1.host_uuid
                inner join cdp_db_host as cdh2
                on cdt.standby_host_uuid = cdh2.host_uuid
                where cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $backupType = "";
        if(Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'] == $tasktype){
            //如果是备份,添加备份类型,
            $config = json_decode($data[0]['config'], true);
            $backupType = intval($config['standbyhostInfo']['backuptype']);
        }
        $info = array(
            'productdes' => $data[0]['productname'] . "(" . $data[0]['productip'] . ")",
            'standbydes' => $data[0]['standbyname'] . "(" . $data[0]['standbyip'] . ")",
            'productip' => $data[0]['productip'],
            'standbyip' => $data[0]['standbyip'],
            'productuuid' => $data[0]['productuuid'],
            'standbyuuid' => $data[0]['standbyuuid'],
            'tasktype' => intval($tasktype),
            'backuptype' => $backupType
        );
        return $info;
    }

    /**
     * 获取副本回传传输策略
     * @param string $taskuuid
     */
    private function getTransportInfo($taskuuid,$task_type){
    	$sql = 'select bts.encrypt_flag, bts.compress_flag from bd_task bt, bd_transport_strategy bts where bt.strategy_id = bts.strategy_id and bt.task_uuid = ?';
    	$data = $this->dbSelect($sql, array($taskuuid));
    	//如果是文件恢复与备份没有压缩传输
    	if($task_type == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY_FETCH']){
    	    $info = array(
    	        'encrypt_flag' => $this->getStrategySwitch(intval($data[0]['encrypt_flag'])),
    	    );
    	}else{
    	    //其他
    	    $info = array(
    	        'encrypt_flag' => $this->getStrategySwitch(intval($data[0]['encrypt_flag'])),
    	        'compress_flag' => $this->getStrategySwitch(intval($data[0]['compress_flag']))
    	    );
    	}
    	return $info;
    }


    /**
     * 策略开关描述
     * @param int $flag
     */
    private function getStrategySwitch($flag){
    	$switch = Xphp::$_lang['UI_PUBLIC_OFF'];
    	if($flag == Xphp::$_config['FLAG']['SET']){
    		$switch = Xphp::$_lang['UI_PUBLIC_ON'];
    	}else if($flag == Xphp::$_config['FLAG']['UNSET']){
    		$switch = Xphp::$_lang['UI_PUBLIC_OFF'];
    	}
    	return $switch;
    }

    /**
     * 得到时间策略信息
     * @param int $strategyID
     * @return array
     */
    private function getJobTimeStrategy($strategyID){
//         $this->paramsCheck($strategyID);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time ,full_backup_compensation_flag
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $utils = Xphp::instance('Utils');
        $strategy = array();
        $allBackupMode = array_column($data, 'mode');
        foreach ($data as $d){
        	$startTime = $d['start_time'];
        	if(intval($d['strategy_type']) == Xphp::$_config['STRATEGY_TYPE']['ONCE'] && strtotime($d['start_time']) < time()){
        		$startTime = $d['start_time'].'('.Xphp::$_lang['UI_PUBLIC_EXPIRED'].')';
        	}
            $strategy[] = array(
                // 没有完全备份就是永久增量
                'mode' => !in_array(Xphp::$_config['BACKUP_MODE']['FULL'], $allBackupMode) && Xphp::$_config['BACKUP_MODE']['INCREMENTAL'] == $d['mode']
                    ? Xphp::$_config['BACKUP_MODE']['PINCREMENTAL'] : $d['mode'],
                'type' => $d['strategy_type'],// 1是天,2是周,3是月,4是一次性
                'days' => $this->getDaysArr($d['days']),
                'frequency' => $this->getFrequency($d['strategy_type'], $d['days']),
                'startTime' => $startTime,
                'rollFlag' => Xphp::$_config['FLAG']['SET'] == intval($d['roll_flag']) ? true : false ,
                'rollInterval' => $utils->secToTime($d['roll_interval']),
                'endTime' => $d['roll_end_time'],
                'full_backup_compensation_flag'=> $utils->parseFlagToBool($d['full_backup_compensation_flag']),
            );
        }
        return $strategy;
    }

    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int $StrategyType
     * @param array $days
     * @return string  空字符串  s1 - s4
     */
    private function getFrequency($strategyType, $days){
        $frequency = "";
        if(Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'] != intval($strategyType)){
            return $frequency;
        }
        $strIndex = strpos($days, "s");
        if($strIndex){
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }

    /**
     * 得到天的数组
     * @param unknown $days
     */
    private function getDaysArr($days){
        $count = strlen($days);
        $daysArr = array();
        for ($i =0; $i<$count; $i++){
            if("s" == substr($days, $i, 1)) break;
            $daysArr[] = intval(substr($days, $i, 1));
        }
        return $daysArr;
    }

    /**
     * 得到保留策略信息
     * @param int $strategyID
     * @return array
     */
    public function getReservedStrategy($strategyID,$taskuuid){
        $this->paramsCheck($strategyID);
        $sql = "select strategy_type, number, auto_archive, strategy_mode from bd_reserved_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $reserved = false;

        $sqlGFS = "select level1_type, level2_type, retention_num from bd_task_gfs_retention_strategy where task_uuid = ?";
        $dataGFS = $this->dbSelect($sqlGFS,array($taskuuid));
        $thisGFS = array();
        if(!empty($dataGFS)){
            foreach ($dataGFS as $d){
                switch ($d['level1_type']){
                    case 1:
                        $thisGFS['week'] = array(
                        $d['level2_type'],
                        $d['retention_num'],
                        'checked',
                        );
                        break;
                    case 2:
                        $thisGFS['month'] = array(
                        $d['level2_type'],
                        $d['retention_num'],
                        'checked',
                        );
                        break;
                    case 3:
                        $thisGFS['year'] = array(
                        $d['level2_type'],
                        $d['retention_num'],
                        'checked',
                        );
                        break;
                }
            };

        };
        foreach ($data as $d){
            $reserved = array(
                'type' => $d['strategy_type'],
                'value' => $d['number'],
                'archive' => $d['auto_archive'],
                'GFSinfo' => $thisGFS,
                'strategyMode' => intval($d['strategy_mode']),
            );
        }
        return $reserved;
    }

    /**
     * 得到任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getTransportStrategy($taskuuid, $strategyid){
        $info = array();
        $sql = "select hypervisor_type, transport_priority from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        //传输模式,VMware使用
        $info['hypervisor'] = intval($data[0]['hypervisor_type']);
        $info['mode'] = $this->getTransportModeDes($data[0]['hypervisor_type'], $data[0]['transport_priority']);
        $info['mode_index'] = $data[0]['transport_priority'];
        //如果是proxy传输模式
        if (in_array(intval($data[0]['hypervisor_type']), array_merge(Xphp::$_config['VMHYPERVISORGROUP']['openstack'], [
                Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'],
                Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM'],
                Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'],
                Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
            ])) && intval($data[0]['transport_priority']) == 3) {
            $info['mode'] = Xphp::$_lang['UI_PLATFORM_APPLIANCE'];
        }
        //加密传输,XenServer使用
        $sql = "select encrypt_flag, reconnect_times, reconnect_interval, encrypt_method from bd_transport_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];
        return $info;
    }

    /**
     * 得到副本任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getCopyTransportStrategy($taskuuid, $strategyid){
    	$info = array();
    	$sql = "select encrypt_flag, compress_flag, compress_method, reconnect_times, reconnect_interval from bd_transport_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql, array($strategyid));
    	$info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
    	$info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['compress_flag']);
        $info['compress_method'] = $data[0]['compress_method'];
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
    	return $info;
    }

    /**
     * 得到数据库任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getDBTransportStrategy($taskuuid, $strategyid){
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times,
                    bts.reconnect_interval, bts.encrypt_method, bts.network_uuid, bts.network_pool_uuid,
                    bnnp.network_pool_nickname
                from bd_transport_strategy bts
                    left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid
                    LEFT JOIN bd_node_network_pool bnnp ON bts.network_pool_uuid = bnnp.network_pool_uuid
                where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
        $info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['compress_flag']);
        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['network_uuid'] = $data[0]['network_uuid'];
        $info['network_pool_uuid'] = $data[0]['network_pool_uuid'];
        $info['network_pool_nickname'] = $data[0]['network_pool_nickname'];
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];

        return $info;
    }
    /**
     * 得到文件任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getFsTransportStrategy($taskuuid, $strategyid){
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method,
                    bts.network_uuid, bts.network_pool_uuid, bnnp.network_pool_nickname
                from bd_transport_strategy bts
                    left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid
                    LEFT JOIN bd_node_network_pool bnnp ON bts.network_pool_uuid = bnnp.network_pool_uuid
                where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
        $info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['compress_flag']);
        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['network_uuid'] = $data[0]['network_uuid'];
        $info['network_pool_uuid'] = $data[0]['network_pool_uuid'];
        $info['network_pool_nickname'] = $data[0]['network_pool_nickname'];
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];

        return $info;
    }

    /**
     * 得到传输模式的描述
     * @param int $hypervisor
     * @param string  $transportPriority
     */
    private function getTransportModeDes($hypervisor, $transportPriority){
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['vmware']) || in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['huawei'])){
            $modeArr = explode(":", $transportPriority);
            $mode = $modeArr[1];
            return $vmDes['VmTransportMode'][$mode];
        }
        $transportPriority = intval($transportPriority);
        if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){
            return $vmDes['VmTransportModeOpenstack'][$transportPriority];
        }
        //如果是华为KVM
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XFUSION_KVM']){
            return $vmDes['VmTransportModeHuaWeiKVM'][$transportPriority];
        }

        //如果是RHV|OLVM|zVirt|HostVM
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HOSTVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RED_VIRT']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ROSA_VIRT']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OVIRT_KVM']
        ){
            return $vmDes['VmTransportModeRedHat'][$transportPriority];
        }

        return $vmDes['VmTransportModeXenServer'][$transportPriority];
    }

    /**
     * 根据备份任务时间策略得到当前任务对应的备份模式按钮
     * @param unknown $taskuuid 任务uuid
     * @param boolean $type 是否添加启用策略按钮flag 8
     * @return array:
     */
    private function getTaskStartMode($taskuuid, $type){
    	//根据任务uuid得到时间策略，然后根据时间策略类型得到返回的操作码
    	//1.只有完备，可以同时又增量和差异 7 6
    	//2.又增量或差异就只能启动增量或差异7/6
    	$sql = "select bts.mode from bd_task bt, bd_time_strategy bts where 
    			bt.strategy_id =  bts.strategy_id and bt.task_uuid = ? ";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$startMode = array();
    	$modeInfo = array();
    	foreach($data as $d){
    		$modeInfo[] = $d['mode'];

    	}
    	//如果type为true 加上启用策略按钮8
    	if($type){
    		$startMode = array(8);
    	}
    	if(in_array(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'],$modeInfo)){
    		array_push($startMode, 7);
    	}else if(in_array(Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'],$modeInfo)){
    		array_push($startMode, 6);
    	}else {
    		array_push($startMode,7,6);
    	}

    	return $startMode;
    }

    /**
     * 根据任务状态得到任务能够进行的操作码数组
     * @param int $status
     * @return array 启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备
     */
    private function getTaskOpCode($taskuuid,$tasktype, $status){
        $status = intval($status);
        $this->paramsCheck($status);
        $statusConf = Xphp::$_config['TASKSTATUS'];
        $opCode = array();
        switch ($status){
            case $statusConf['WAITTING']:
                if(Xphp::$_config['TASKTYPE']['BACKUP'] == $tasktype){
                	$startMode = $this->getTaskStartMode($taskuuid, false);
                    $opCode = array(10, 2);
                    $opCode = array_merge($startMode, $opCode);
                }else{
                    $opCode = array(1, 2);
                }
                break;
            case $statusConf['ERROR']:
                if(Xphp::$_config['TASKTYPE']['BACKUP'] == $tasktype){
                	$startMode = $this->getTaskStartMode($taskuuid, true);
                    $opCode = array(10, 2);
                    $opCode = array_merge($startMode, $opCode);
                }else{
                    $opCode = array(1, 2);
                }
                break;
            case $statusConf['RUNNING']:
                if(Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] == $tasktype){
                    $opCode = array(9, 2);
                }else{
                    $opCode = array(2);
                }
                break;
            case $statusConf['ABNORMAL']:
            case $statusConf['STOPPING']:
                $opCode = array(2);
                break;
            case $statusConf['STOPPED']:
                if(Xphp::$_config['TASKTYPE']['BACKUP'] == $tasktype){
                	$startMode = $this->getTaskStartMode($taskuuid, true);
                    $opCode = array(10, 3, 4);
                    $opCode = array_merge($startMode, $opCode);
                }else{
                    $opCode = array(1, 4);
                }
                break;
            case $statusConf['NETWORK_FAULT']:
                $opCode = array(2);
                break;
            default:
                break;
        }
        return $opCode;
    }

    /**
     * 根据任务状态得到任务能够进行的操作码数组
     * @param int $status
     * @return array 启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切(新增)/15 停止回切(新增)/16创建标签点
     * @modify 将任务控制数字定义移植到配置文件中,读取Array TASK_CONTROL中对应的元素
     */
    private function getTaskOpCodeByType($taskuuid,$tasktype, $status){
        $status = intval($status);
    	$this->paramsCheck($tasktype);
    	$taskTypeConf = Xphp::$_config['TASKTYPE'];
    	$taskControl = Xphp::$_config['TASK_CONTROL'];
    	$opCode = array();
    	switch ($tasktype){
    		case $taskTypeConf['BACKUP']:
    			$opCode = array(
    			    $taskControl['START_STRATEGY'],
    			    $taskControl['START_FULL'],
    			    $taskControl['START_INCR'],
    			    $taskControl['START_DIFF'],
    			    $taskControl['STOP'],
    			    $taskControl['MODIFY'],
    			    $taskControl['DELETE'],
    			);
    			break;
    		case $taskTypeConf['RECOVERY']:
    		    $opCode = array(
        		    $taskControl['START'],
        		    $taskControl['STOP'],
        		    $taskControl['DELETE'],
    		    );
    			break;
    		case $taskTypeConf['VM_INSTANT_RECOVERY']:
    			$opCode = array(
    			    $taskControl['START'],
    			    $taskControl['MIGRATION'],
    			    $taskControl['STOP'],
    			    $taskControl['DELETE'],
    			);
    			break;
    		case $taskTypeConf['VM_INSTANT_RECOVERY_MOTION']:
    		    $opCode = array(
    		      $taskControl['STOP'],
    		    );
    			break;
			case $taskTypeConf['VM_FILE_RECOVERY']:
			    $opCode = array(
    			    $taskControl['START'],
    			    $taskControl['STOP'],
    			    $taskControl['DELETE'],
			    );
			    break;
			case $taskTypeConf['VM_CDP_BACKUP']:
			    $opCode = array(
    			    $taskControl['START'],
    			    $taskControl['STOP'],
    			    $taskControl['MODIFY'],
    			    $taskControl['DELETE'],
			    );
			    break;
		    case $taskTypeConf['BACKUP_COPY']:
		        $opCode = array(
		            $taskControl['START_STRATEGY'],
		            $taskControl['START'],
		            $taskControl['STOP'],
		            $taskControl['MODIFY'],
		            $taskControl['DELETE'],
// 		            $taskControl['PAUSE'],
		        );
		        break;
	        case $taskTypeConf['BACKUP_COPY_FETCH']:
	            $opCode = array(
	                $taskControl['START'],
	                $taskControl['STOP'],
	                $taskControl['DELETE'],
// 	                $taskControl['PAUSE'],
	            );
                break;
            case $taskTypeConf['ARCHIVE']:
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['ARCHIVE_FETCH']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['DB_CDP_BACKUP']:
                $dbCDPHandler = Xphp::instance('DbCDPHandler');
                $opCode = $dbCDPHandler->getBackupTaskOpCode($taskuuid, $status);
                break;
            case $taskTypeConf['DB_CDP_RECOVERY']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['FILE_BACKUP_COPY']:
            case $taskTypeConf['NAS_BACKUP_COPY']:
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
//                     $taskControl['PAUSE'],
                );
                break;
            case $taskTypeConf['FILE_BACKUP_COPY_FETCH']:
            case $taskTypeConf['NAS_BACKUP_COPY_FETCH']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
//                     $taskControl['PAUSE'],
                );
                break;
            case $taskTypeConf['DB_BACKUP']:
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START_FULL'],
                    $taskControl['START_INCR'],
                    $taskControl['START_DIFF'],
                    $taskControl['LOG_BACKUP'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['DB_RECOVERY']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['FILE_CDP_BACKUP']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['FILE_CDP_RECOVERY']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['DB_BACKUP_COPY']:
            case $taskTypeConf['OS_BACKUP_COPY']:
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
//                     $taskControl['PAUSE'],
                );
                break;
            case $taskTypeConf['DB_BACKUP_COPY_FETCH']:
            case $taskTypeConf['OS_BACKUP_COPY_FETCH']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
//                     $taskControl['PAUSE'],
                );
                break;
            case $taskTypeConf['VOL_CDP_BACKUP']: //卷CDP暂时添加上述可控制项
            case $taskTypeConf['VOL_CDP_REPLICATION']:
                $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
                $taskRunningStage = $volCdpHandler->getVolCdpTaskRunningStage($taskuuid);
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
//                     $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                    $taskControl['START_TAKEOVER'],
                    $taskControl['STOP_TAKEOVER'],
                    $taskControl['START_FAILBACK'],
                );
                break;
            case $taskTypeConf['VOL_CDP_RECOVERY']: //卷CDP恢复添加上述可控制项
                $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
                $taskRunningStage = $volCdpHandler->getVolCdpTaskRunningStage($taskuuid);
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
//                     $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['VOL_CDP_TAKEOVER']: //手动接管添加上述可控制项
                $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
                $taskRunningStage = $volCdpHandler->getVolCdpTaskRunningStage($taskuuid);
                $opCode = array(
                    $taskControl['START_TAKEOVER'],
                    $taskControl['STOP_TAKEOVER'],
                    $taskControl['START_FAILBACK'],
//                     $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['OS_BACKUP']: //OS备份
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START_FULL'],
                    $taskControl['START_INCR'],
                    $taskControl['START_DIFF'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['OS_RECOVERY']: //OS恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['SURE_BACKUP']: //数据验证
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['VM_HUAWEI_CBR_SYNC']://华为CBR同步
                //在bd_task中通过task_id去查找strategy_id 再通过getTimeStrategyInfo找到是否按策略同步
                $sql = "select strategy_id from bd_task where task_uuid = ?";
                $data = $this->dbSelect($sql,array($taskuuid));
                $strategyid =  $data[0]['strategy_id'];
                $vmHandler = Xphp::instance('Vmhandler');
                $timestrategy  = $vmHandler->getTimeStrategyInfo($strategyid);
                if($timestrategy['type'] == 'oncetime'){
                    $opCode = array(
                        $taskControl['START'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                }else{
                    $opCode = array(
                        $taskControl['START_STRATEGY'],
                        $taskControl['START'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                }
                break;
            case $taskTypeConf['OS_INSTANT_RECOVERY']: //操作系统瞬时恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['MIGRATION'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['OS_INSTANT_RECOVERY_MOTION']: //操作系统迁移
                $opCode = array(
                    $taskControl['STOP'],
                );
                break;
    		default:
    			break;
    	}
    	return $opCode;
    }
    /**
     * 得到任务流量
     * @param unknown $params
     */
    public function getTaskSpeed($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.module_type, bri.speed, bri.speed_time, bt.task_status from bd_running_info bri, bd_task bt 
                where bri.task_uuid = bt.task_uuid and bri.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $speed = 0;
        if($data){
            if($data[0]['task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
                $data[0]['task_status'] == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                $speedCount = intval($data[0]['speed']);
                if($speedCount < 0){
                    $speedCount = 0;
                }
                $speed = round($speedCount / 1024, 2);
            }
        }
        if(time() - $data[0]['speed_time'] > 12){
            //如果长时间没有更新速度,处理速度为0
            $speed = 0;
        }

        $data = array(
            "speed" => $speed,
            "test" => $data[0]['speed_time'],
            "t" => time(),
        	"nowTime" => date("H:i:s")
        );

        return json_encode($data);
    }
    /**
     * 任务详情: 得到卷CDP模块任务基本信息
     * @param unknown $params
     */
    public function getVolCdpBasicInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
                	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
                	   bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time,
                       bri.total_object_valid_size,bri.total_object_completed_valid_size,vol_task.current_task_running_stage,
                       vol_task.master_agent_uuid,vol_task.standby_agent_uuid,vol_task.recovery_target_agent_uuid,vol_task.auto_takeover_flag,
                       vol_task.rebuild_partition_flag,vol_task.recovery_data_source,vol_task.monitor_data_io_replication_mode,vol_task.auto_fault_resume_flag,
                       vol_task.takeover_data_source,vol_task.mirror_backup_flag,vol_task.backup_mode,vol_task.auto_takeover_enable_flag
                from   bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs,cdp_vol_task vol_task
                where  bt.user_uuid = bu.user_uuid and 
                       bt.task_uuid = bri.task_uuid and 
                       bt.strategy_id = bs.strategy_id and 
                       bt.task_uuid = vol_task.task_uuid and 
                	   bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $utils = Xphp::instance('Utils');
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            $masterHostInfo = $this->getHostInfo($d['master_agent_uuid'],$d['task_type'],$taskUUID);  //主机
            $standbyHostInfo = $this->getHostInfo($d['standby_agent_uuid'],$d['task_type'],$taskUUID);    //备机
            $totalObjectSize = $d['total_object_size'];
            $completedValidSize = $d['total_object_completed_valid_size'];

            $totalValidSize = $d['total_object_valid_size'];
            $currentTaskRunningStage = $d['current_task_running_stage'];

            $valueFlag = false;
            $currentSizeValue = 0;
            if($totalObjectSize!=0 && $completedValidSize!=0){
                $valueFlag = true;
                $currentSizeValue = $volCdpHandler->getTaskTotalCapacityInfo($taskUUID);
            }
            if(!(is_numeric($currentSizeValue))){
                $valueFlag = false;
            }
            
            $currentSize = $utils->calSize($currentSizeValue,$valueFlag);
            $totailSize = $utils->calSize($d['total_object_size']);
            $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $currentSizeValue, false, $d['task_type']);

            $totalprogress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'],$currentSizeValue, true, $d['task_type']);
            //任务处于实时同步阶段或逆向实时同步阶段
            if($currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['REALTIME_SYNC']
                || $currentTaskRunningStage==$VolCdpDes['TASK_RUNNING_STAGE']['WAIT_CONVERT_TO_REALTIME_SYNC']
                || $currentTaskRunningStage ==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC'] ){
                $currentSize =  $volCdpHandler->getTaskCurrentTotalSize($d['task_uuid']);
            }
            $consistencyCheckVol = "--";
            //服务端数据一致性校验阶段 和 服务端实时数据校验
            if( $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['SERVER_CONS_CHECK'] || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['REALTIME_CONSISTENCY_CHECK']){
                $serverConsCheckInfo = $volCdpHandler->getServerConsCheckInfo($taskUUID);
                $totailSize = $utils->calSize($serverConsCheckInfo['total_size']);
                $currentSize = $utils->calSize($serverConsCheckInfo['completed_size']);
                $consistencyCheckVol = $serverConsCheckInfo['current_vol'];
                $totalprogress = $serverConsCheckInfo['total_progress'];
                $progress = $totalprogress;
            }

            $storageuuid = $d['storage_uuid'];
            $monitorDataIoReplicationMode = $d['monitor_data_io_replication_mode'];
            $threadNum = $d['thread_num'];
            $mirrorBackupFlag = $d['mirror_backup_flag'];
            if($currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
                || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
                || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){  //任务处于回切阶段
                    $failbackInfo = $volCdpHandler->getFailbackInfo($taskUUID);
                    $storageuuid = $failbackInfo['storage_uuid'];
                    $monitorDataIoReplicationMode = $failbackInfo['monitor_data_io_replication_mode'];
                    $threadNum = $failbackInfo['transport_thread_num'];
                    $mirrorBackupFlag = $failbackInfo['mirror_backup_flag'];
            }
            $taskStatusValue = $d['task_status'];
            $startTime = $volCdpHandler ->getTaskStartTime($d['start_time'],$d['task_status'],$taskUUID,$d['task_type']);
            $currentTaskRunningStageValue = $d['current_task_running_stage'];
            $speedValue = $d['speed'];
            if($speedValue==0){
                $speed = "--";
            }else{
                $speed = $this->getJobSpeed($d['task_status'], $d['speed']);
            }
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $ptDes['TASKTYPEDES'][$d['task_type']],
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$taskStatusValue],
                'statusValue' => intval($taskStatusValue),
                'totalSize' => $totailSize,
                'currentSize' => $currentSize,
                'speed' => $speed,
                'progress' => $progress,
                'totalprogress' => $totalprogress,
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($startTime, $d['task_status']),
                'intervalTime' => $this->getTimeInterval($startTime, $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transportStrategy' => $volCdpHandler->getVolCdpTransportStrategy($taskUUID, $d['strategy_id'],$currentTaskRunningStage),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $storageuuid),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),
                'modeStrategy' => '',
                'thread_num' => $threadNum,
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                // 'applianceDes' => $this->getJobAppliance($d['task_uuid'])['des'],
                'fulldiskRestore' => $this->getRecoveryFullDisk($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'cache_info' => $volCdpHandler->getVolCdpTaskCacheInfo($d['task_uuid'],$currentTaskRunningStage),
                'current_task_running_stage' => $ptDes['CDP_TASK_RUNNING_STAGE'][$currentTaskRunningStageValue], //当前任务运行阶段
                'current_task_running_stage_value' =>$currentTaskRunningStageValue, //当前任务运行阶段value
                'os_type' => $masterHostInfo['os_type'],
                'master_agent_info' => $masterHostInfo['host_info'],
                'agent_ip' =>$masterHostInfo['agent_ip'],
                'master_agent_uuid' => $d['master_agent_uuid'],
                'standby_agent_info' => $standbyHostInfo['host_info'],  //备机
                'standby_agent_uuid' => $d['standby_agent_uuid'],
                'recovery_target_agent_uuid' => $d['recovery_target_agent_uuid'],  //恢复代理
                'rebuild_partition_flag' => $d['rebuild_partition_flag'],  //重建分区标志位.恢复使用
                'monitor_data_io_replication_mode' => $this->getVolCdpTaskIoMode($monitorDataIoReplicationMode), //监控IO复制模式
                'auto_takeover_flag' => $d['auto_takeover_flag'],  //自动接管标志位
                'auto_takeover_info' => $volCdpHandler->getAutoTakeoverConfInfo($d['task_uuid'],$d['auto_takeover_flag'],$d['task_type']),
                'auto_fault_resume_flag' => $d['auto_fault_resume_flag'], //故障自动恢复
                'mirror_backup_flag' => $mirrorBackupFlag,
                'takeover_app_info' => Xphp::$_lang['WEB_JOB_GET_APP_INFO'], //TOGO 增加接管应用解析
                'handover_info' => $volCdpHandler->getHandoverConfInfo($d['task_uuid'],$d['task_type']), //获取手动接管配置
                'takeover_data_source' => $d['takeover_data_source'],
                'consistency_check_vol' => $consistencyCheckVol,
                'backup_mode' => $d['backup_mode'],
                'auto_takeover_enable_flag' => $d['auto_takeover_enable_flag'],  //自动接管是否启用
            );
        }
        return json_encode($basicInfo);
    }
    /**
     * 任务详情: 得到虚拟机模块任务基本信息
     * @param unknown $params
     */
    public function getBasicInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        // $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        //                bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
        //         	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
        //         		bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, bt.backup_server_ip
        //         from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs
        //         where bt.user_uuid = bu.user_uuid and
        //               bt.task_uuid = bri.task_uuid and
        //               bt.strategy_id = bs.strategy_id and
        //         	  bt.task_uuid = ? ";
        // $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        // bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
        // bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
        // bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, bt.backup_server_ip,
        // bcs.is_integrity_check,bcs.calibration_algorithm,bcs.error_handle,
        // bn.ip, bn.host_name, bn.node_nickname
        // from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs,bd_integrity_check_strategy bcs, bd_node bn
        // where bt.user_uuid = bu.user_uuid and
        // bt.task_uuid = bri.task_uuid and
        // bt.strategy_id = bs.strategy_id and
        // bcs.task_uuid  =   bt.task_uuid and
        // bt.node_uuid = bn.node_uuid and
        // bt.task_uuid = ? ";
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,bt.task_orchestration_plan_flag,
        bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
        bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, bt.backup_server_ip,
        bn.ip, bn.host_name, bn.node_nickname
        from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs, bd_node bn
        where bt.user_uuid = bu.user_uuid and 
        bt.task_uuid = bri.task_uuid and 
        bt.strategy_id = bs.strategy_id and 
        bt.node_uuid = bn.node_uuid and
        bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        $nodeHandler = Xphp::instance('NodeHandler');
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            $applianceInfo = $this->getJobAppliance($d['task_uuid']);
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
                                $d['module_type'], $d['task_uuid']),
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'statusValue' => intval($d['task_status']),
                'totalSize' => $utils->calSize($d['total_object_size']),
                'currentSize' => $utils->calSize($d['total_object_completed_size']),
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'timeStrategyBackupType' => Xphp::instance('Vmhandler')->getTimeStrategyBackupType($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transportStrategy' => $this->getTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid']),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                'applianceFlag' => $applianceInfo['flag'],
                'applianceDes' => $applianceInfo['des'],
                'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'fulldiskRestore' => $this->getRecoveryFullDisk($d['task_uuid']),
                'nosnapshot' => $this->getXenSnapshotFlag($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'backupNodeIp' => $d['backup_server_ip'],



                //华为CBR下云同步任务/备份上云任务新增数据完整性校验
                // 'verify' => $ptDes['INTEGRITY_CHECK_DES'][$d['is_integrity_check']], //是否开启数据完整性校验
                // 'verifyValue'=> intval($d['is_integrity_check']),
                // 'verifyType'=>$ptDes['INTEGRITY_CHECKTYPE_DES'][$d['calibration_algorithm']], //校验类型
                // 'verifyAlarm'=>$ptDes['INTEGRITY_CHECK_DES'][$d['error_handle']], //校验失败是否继续任务
                // 'verifyAlarmValue'=> intval($d['error_handle']),
                'verify' =>Xphp::$_lang['UI_PUBLIC_OFF_TWO'], //是否开启数据完整性校验
                'verifyValue'=> 2,
                'verifyType'=>"MD5", //校验类型
                'verifyAlarm'=>Xphp::$_lang['UI_SETTINGS_UPDATE_CONTINUE'], //校验失败是否继续任务
                'verifyAlarmValue'=> 2,
                "nodename" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                'task_orchestration_plan_flag' => $utils->parseFlagToBool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $this->getTapeGroupStrategy($d['storage_uuid']) ?? '',
            );
            //如果是虚拟机备份添加备份模式配置
            if(intval($d['module_type']) == Xphp::$_config['MODULE_TYPE']['VM']){
                $basicInfo['modeStrategy'] = $this->getVMBackupModeStrategy($d['task_uuid']);
                if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                    $basicInfo['modeStrategy'] = $this->getVerifyModeStrategy($d['task_uuid'], $utils);
                }

                //获取筛选条件
                $basicInfo['selectConditions'] = $this->getVMSelectConditions($d['task_uuid']);
                //获取是否自动加入备份
                $auto_join_flag = $this->getVMTaskDetail($d['task_uuid'])['auto_join_flag'];
                $basicInfo['autoJoinFlag'] = $utils->parseFlagToBool($auto_join_flag);
            }
        }
        return json_encode($basicInfo);
    }
    /**
     * 任务详情: 得到文件模块任务基本信息
     * @param unknown $params
     */
    public function getFsBasicInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT DISTINCT
                    bt.task_uuid,
                    bt.task_name,
                    bt.module_type,
                    bt.sub_module_type,
                    bt.task_type,
                    bt.task_status,
                    bt.thread_num,
                    bt.worm_flag,
                    bt.virus_scan_flag,
                    bt.integrity_check_flag,
                    bt.strategy_id,
                    UNIX_TIMESTAMP( bt.create_time ) AS create_time,
                    bt.transport_ip_segment,
                    bt.node_uuid,
                    bt.storage_uuid,
                    bt.current_stage,
                    bt.stage_percent,
                    bu.user_name,
                    UNIX_TIMESTAMP( bs.next_start_time ) AS next_start_time,
                    bt.ignore_resource_limiting_flag,
                    bt.user_uuid,
                    bri.total_object_size,
                    bri.total_object_completed_size,
                    bri.speed,
                    UNIX_TIMESTAMP( bri.start_time ) AS start_time,
                    ft.snap_shot_flag,
                    ft.detail,
                    bal.detail AS bal_detail,
                    ft.file_archive_flag,
                    ft.skip_file_alarm_flag,
                    ft.skip_file_alarm_min_num,
                    ft.skip_file_alarm_min_ratio,
                    ft.permission_operate_flag,
                    ft.same_file_strategy,
                    ft.link_file_pass_flag,
                    ft.dir_tree_recovery_flag,
                    fpl.new_root_path,
                    bt.task_orchestration_plan_flag,
                    btsc.worm_protection_time,
                    btsc.virus_scan_config_list,
                    btsc.integrity_check_strategy,
                    btsc.backup_integrity_check_full_error_policy,
                    btsc.backup_integrity_check_inc_error_policy,
                    btsc.recovery_integrity_check_error_policy,
                    brs.network_retry_times,
                    brs.network_retry_interval,
                    brs.op_retry_times,
                    brs.op_retry_interval,
                    brs.task_retry_object,
                    brs.task_retry_times,
                    brs.task_retry_interval,
                    bsr.worm_flag AS worm_storage_flag 
                FROM
                    bd_task bt
                    JOIN bd_user bu ON bt.user_uuid = bu.user_uuid
                    JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid
                    JOIN bd_strategy bs ON bt.strategy_id = bs.strategy_id
                    JOIN fs_task ft ON bt.task_uuid = ft.task_uuid
                    JOIN bd_task_agent_list bal ON bt.task_uuid = bal.task_uuid
                    JOIN fs_path_list fpl ON bt.task_uuid = fpl.task_uuid
                    JOIN bd_task_safe_config btsc ON bt.task_uuid = btsc.task_uuid
                    JOIN bd_retry_strategy brs ON bt.task_uuid = brs.task_uuid
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid 
                WHERE
                    bt.task_uuid = ? GROUP BY bt.task_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            // 处理下阶段的显示
            $stageArr = $ptDes['COMMON_STAGE'];
            $currentstage = !empty($stageArr[$d['current_stage']]) ?
                $stageArr[$d['current_stage']] : Xphp::$_config['NULLSPACE'];
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }
            $detail = json_decode($d['detail'], true);
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
                                $d['module_type'], $d['task_uuid']),
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'statusValue' => intval($d['task_status']),
                'totalSize' => $utils->calSize($d['total_object_size']),
                'currentSize' => $utils->calSize($d['total_object_completed_size']),
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'timeStrategyBackupType' => $vmHandler->getTimeStrategyBackupType($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transportStrategy' => $this->getFsTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid'],$taskUUID),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'fulldiskRestore' => $this->getRecoveryFullDisk($d['task_uuid']),
                'nosnapshot' => $this->getXenSnapshotFlag($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'snapShotFlag' => $d['snap_shot_flag'],
                'wildcardMode' => intval(json_decode($d['bal_detail'],true)["wildcard_mode"]),
                'networkFlag' => $this->getNetworkShowFlag($taskUUID, $d['module_type'], $d['task_type']),//检查显示传输网络标志
                'scan_thread_num' => intval($detail['scan_thread_num']),
                'scan_file_num' => intval($detail['scan_file_num']),
                'archiveflag' => $d['file_archive_flag'],
                'skip_file_alarm_flag' => $d['skip_file_alarm_flag'] == 1 ? true : false,
                'skip_file_alarm_min_num' => $d['skip_file_alarm_min_num'],
                'skip_file_alarm_min_ratio' => $d['skip_file_alarm_min_ratio'] . '%',
                'permission_operate_flag' => $d['permission_operate_flag'] == 1 ? true : false,
                'same_file_strategy' => $d['same_file_strategy'],
                'link_file_pass_flag' => $d['link_file_pass_flag'] == 1 ? true : false,
                'dir_tree_recovery_flag' => $d['dir_tree_recovery_flag'] == 1 ? true : false,
                'new_root_path' => $d['new_root_path'] == '' ? Xphp::$_lang['UI_FILE_RECOVERY_OLD_PATH']  : $d['new_root_path'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $this->getNasResorceInfo($taskUUID)['source_agent_type'],//agent_type
                'src_sub_module_type' => $this->getNasResorceInfo($taskUUID)['src_type'],//sub_module_type
                'task_orchestration_plan_flag' => $utils->parseFlagToBool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $this->getTapeGroupStrategy($d['storage_uuid']) ?? '',
                'storage_type' => $this->getStorageTypeByTaskId($taskUUID, $d['task_type']),
                //安全策略
                'safe_strategy' => $this->groupSafeStrategy($d),
                //重试策略
                'retry_strategy' => $this->groupRetryStrategyInfo($d),
                'ignore_resource_limiting_flag' => $utils->parseFlagToBool($d['ignore_resource_limiting_flag']),
                'current_stage' => $d['current_stage'], // 任务阶段
                'stage_percent' => $d['stage_percent'], // 任务百分比
                'current_stage_value' => $currentstage, // 任务阶段+任务百分比
                'task_user_uuid' => $d['user_uuid'],
            );
        }
        return json_encode($basicInfo);
    }

    /**
     * 任务详情-组合安全策略相关信息
     * @param object 任务所有信息
     * @return object 安全策略相关信息
     */
    public function groupSafeStrategy($data) {
        $info = array();
        if (!empty($data)) {
            $utils = Xphp::instance('Utils');
            $info = array(
                //获取worm开关
                'worm_flag' => $utils->parseFlagToBool($data['worm_flag']),
                //获取worm保护期限
                'worm_protection_time' => intval($data['worm_protection_time']),
                //获取病毒是否开关
                'virus_scan_flag' => $utils->parseFlagToBool($data['virus_scan_flag']),
                //获取病毒检测配置
                'virus_scan_config_list' => json_decode($data['virus_scan_config_list'], true),  
                //获取完整性效验开关
                'integrity_check_flag' => $utils->parseFlagToBool($data['integrity_check_flag']),
                //获取完整性校验数据
                'integrity_check_config' => array(
                    //获取效验周期
                    'check_strategy' => intval($data['integrity_check_strategy']),
                    //获取完全备份点异常
                    'full_error_policy' => intval($data['backup_integrity_check_full_error_policy']),
                    //获取其他备份点异常
                    'inc_error_policy' => intval($data['backup_integrity_check_inc_error_policy']),
                    //获取恢复完整性校验
                    'recovery_error_policy' => intval($data['recovery_integrity_check_error_policy']),
                ),
                //存储是否开启了worm
                'worm_storage_flag' => $utils->parseFlagToBool($data['worm_storage_flag']),
            );
        }
        return $info;
    }

    /**
     * 任务详情-组合重试策略相关信息
     * @param object 任务所有信息
     * @return object 重试策略相关信息
     */
    public function groupRetryStrategyInfo($data) {
        $info = array();
        if (!empty($data)) {
            //操作重试开关
            $opRetryFlag = true;
            if ($data['op_retry_times'] == 0) {
                $opRetryFlag = false;
            }
            //任务重试开关
            $taskRetryFlag = true;
            if ($data['task_retry_times'] == 0) {
                $taskRetryFlag = false;
            }
            $info = array(
                'network_retry_times' => $data['network_retry_times'],
                'network_retry_interval' => $data['network_retry_interval'],
                'op_retry_flag' => $opRetryFlag,
                'op_retry_times' => $data['op_retry_times'],
                'op_retry_interval' => $data['op_retry_interval'],
                'task_retry_flag' => $taskRetryFlag,
                'task_retry_object' => $data['task_retry_object'],
                'task_retry_times' => $data['task_retry_times'],
                'task_retry_interval' => $data['task_retry_interval'],
            );
        }
        return $info;
    }

    /**
     * 任务详情-获取安全策略
     */
    public function getSafeConfigStrategy($taskUuid)
    {
        $sql = "SELECT bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
                    btsc.worm_protection_time, btsc.virus_scan_config_list,
                    btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,
                    btsc.backup_integrity_check_inc_error_policy, btsc.recovery_integrity_check_error_policy
                FROM bd_task bt
                    INNER JOIN bd_task_safe_config btsc ON bt.task_uuid = btsc.task_uuid
                WHERE bt.task_uuid = ? ";
        $safeConfigData = $this->dbSelect($sql, [$taskUuid]);
        if (!is_array($safeConfigData) || !$safeConfigData) {
            return [];
        }
        $utils = Xphp::instance('Utils');
        $wormFlag = $utils->parseFlagToBool($safeConfigData[0]['worm_flag']);
        $integrityCheckFlag = $utils->parseFlagToBool($safeConfigData[0]['integrity_check_flag']);
        return [
            'worm_flag' => $wormFlag,
            'worm_protection_time' => $wormFlag ? intval($safeConfigData[0]['worm_protection_time']) : 7,  // 默认7天
            'virus_scan_flag' => $utils->parseFlagToBool($safeConfigData[0]['virus_scan_flag']),
            'virus_scan_config_list' => json_decode($safeConfigData[0]['virus_scan_config_list'], true) ?: [],
            'integrity_check_flag' => $integrityCheckFlag,
            'integrity_check_config' => [
                'check_strategy' => $integrityCheckFlag ? intval($safeConfigData[0]['integrity_check_strategy']) : 2,  // 默认每次备份
                'full_error_policy' => $integrityCheckFlag ? intval($safeConfigData[0]['backup_integrity_check_full_error_policy']) : 1,  // 默认重做完备
                'inc_error_policy' => $integrityCheckFlag ? intval($safeConfigData[0]['backup_integrity_check_inc_error_policy']) : 1,  // 默认重做完备
                'recovery_error_policy' => $integrityCheckFlag ? intval($safeConfigData[0]['recovery_integrity_check_error_policy']) : 0,  // 默认中断恢复
            ],
        ];
    }

    /**
     * 任务详情-根据任务uuid获取存储类型--只用于恢复任务
     * @param string $taskUUID 任务uuid
     * @param string $taskType 任务类型
     * @return string 存储类型
     */
    private function getStorageTypeByTaskId($taskUUID,$taskType){
        $storageType = '';
        if ($taskType == Xphp::$_config['TASKTYPE']['BACKUP']) {
            return $storageType;
        }
        $sql = "select bsr.storage_type from bd_storage_resource bsr, bd_backup_timepoint bbt, fs_path_list fpl where 
        fpl.task_uuid = ? and fpl.recovery_timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }
    /**
     * 任务详情-概览: 文件是否配置通配符
     * @param unknown $params
     */
    private function getWildCardInfo($task_uuid) {
        $sql = "select detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $count = 0;
        foreach($data as $d) {
            $count += intval(json_decode($d['detail'],true)["wildcard_mode"]);
        }
        if( $count > 0) {//大于0则配置了通配符
            return true;
        }
       return false;
    }
    /**
     * 任务详情: 得到NAS模块任务基本信息
     * @param unknown $params
     */
    public function getNasBasicInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT
                    bt.task_uuid,
                    bt.task_name,
                    bt.module_type,
                    bt.sub_module_type,
                    bt.task_type,
                    bt.task_status,
                    bt.thread_num,
                    bt.strategy_id,
                    UNIX_TIMESTAMP( bt.create_time ) AS create_time,
                    bt.transport_ip_segment,
                    bt.node_uuid,
                    bt.storage_uuid,
                    bt.task_orchestration_plan_flag,
                    bt.worm_flag,
                    bt.virus_scan_flag,
                    bt.integrity_check_flag,
                    bt.ignore_resource_limiting_flag,
                    bt.current_stage,
                    bt.stage_percent,
                    bt.user_uuid,
                    bu.user_name,
                    UNIX_TIMESTAMP( bs.next_start_time ) AS next_start_time,
                    bri.total_object_size,
                    bri.total_object_completed_size,
                    bri.speed,
                    UNIX_TIMESTAMP( bri.start_time ) AS start_time,
                    bal.detail,
                    nt.detail AS ntdetail,
                    nt.file_archive_flag,
                    nt.skip_file_alarm_flag,
                    nt.skip_file_alarm_min_num,
                    nt.skip_file_alarm_min_ratio,
                    nt.permission_operate_flag,
                    nt.same_file_strategy,
                    nt.link_file_pass_flag,
                    nt.dir_tree_recovery_flag,
                    nt.snap_shot_flag,
                    fpl.new_root_path,
                    btsc.worm_protection_time,
                    btsc.virus_scan_config_list,
                    btsc.integrity_check_strategy,
                    btsc.backup_integrity_check_full_error_policy,
                    btsc.backup_integrity_check_inc_error_policy,
                    btsc.recovery_integrity_check_error_policy,
                    bsr.worm_flag AS worm_storage_flag,
                    nsr.snapshot_flag 
                FROM
                    bd_task bt
                    JOIN bd_user bu ON bt.user_uuid = bu.user_uuid
                    JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid
                    JOIN bd_strategy bs ON bt.strategy_id = bs.strategy_id
                    JOIN bd_task_agent_list bal ON bt.task_uuid = bal.task_uuid
                    JOIN nas_task nt ON bt.task_uuid = nt.task_uuid
                    JOIN fs_path_list fpl ON bt.task_uuid = fpl.task_uuid
                    JOIN bd_task_safe_config btsc ON bt.task_uuid = btsc.task_uuid
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid 
                    LEFT JOIN nas_storage_resource nsr ON nt.nas_uuid = nsr.nas_uuid 
                WHERE
                    bt.task_uuid = ? GROUP BY bt.task_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            // 处理下阶段的显示
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $stageArr = $ptDes['COMMON_STAGE'];
            $currentstage = !empty($stageArr[$d['current_stage']]) ?
            $stageArr[$d['current_stage']] : Xphp::$_config['NULLSPACE'];
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }
            $ntdetail = json_decode($d['ntdetail'], true);
            $nasInfo = $this->getNasResorceInfo($taskUUID);
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
                                $d['module_type'], $d['task_uuid']),
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'statusValue' => intval($d['task_status']),
                'totalSize' => $utils->calSize($d['total_object_size']),
                'currentSize' => $utils->calSize($d['total_object_completed_size']),
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'timeStrategyBackupType' => $vmHandler->getTimeStrategyBackupType($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transportStrategy' => $this->getTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid'],$taskUUID),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                // 'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'fulldiskRestore' => $this->getRecoveryFullDisk($d['task_uuid']),
                'nosnapshot' => $this->getXenSnapshotFlag($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'snapShotFlag' => $d['snap_shot_flag'],
                'wildcardMode' => intval(json_decode($d['detail'],true)["wildcard_mode"]),
                'scan_thread_num' => intval($ntdetail['scan_thread_num']),
                'scan_file_num' => intval($ntdetail['scan_file_num']),
                'archiveflag' => $d['file_archive_flag'],
                'skip_file_alarm_flag' => $d['skip_file_alarm_flag'] == 1 ? true : false,
                'skip_file_alarm_min_num' => $d['skip_file_alarm_min_num'],
                'skip_file_alarm_min_ratio' => $d['skip_file_alarm_min_ratio'] . '%',
                'permission_operate_flag' => $d['permission_operate_flag'] == 1 ? true : false,
                'same_file_strategy' => $d['same_file_strategy'],
                'link_file_pass_flag' => $d['link_file_pass_flag'] == 1 ? true : false,
                'dir_tree_recovery_flag' => $d['dir_tree_recovery_flag'] == 1 ? true : false,
                'new_root_path' => $d['new_root_path'] == '' ?  Xphp::$_lang['UI_FILE_RECOVERY_OLD_PATH']  : $d['new_root_path'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $nasInfo['source_agent_type'],//agent_type
                'src_sub_module_type' => $nasInfo['src_type'],//sub_module_type
                'task_orchestration_plan_flag' => $utils->parseFlagToBool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $this->getTapeGroupStrategy($d['storage_uuid']) ?? '',
                'storage_type' => $this->getStorageTypeByTaskId($taskUUID, $d['task_type']),
                'retry_strategy' => (new NasHandler())->getRetryStrategy($taskUUID),
                //安全策略
                'safe_strategy' => $this->groupSafeStrategy($d),
                'ignore_resource_limiting_flag' => $utils->parseFlagToBool($d['ignore_resource_limiting_flag']),
                'current_stage' => $d['current_stage'], // 任务阶段
                'stage_percent' => $d['stage_percent'], // 任务百分比
                'current_stage_value' => $currentstage, // 任务阶段+任务百分比
                'task_user_uuid' => $d['user_uuid'],
                'snapshot_flag' => $d['snapshot_flag'],
            );
        }
        return json_encode($basicInfo);
    }
    /**
     * 任务详情: 得到副本任务基本信息
     * @param unknown $params
     */
    public function getCopyBasicInfo($params){
    	$taskUUID = $params['uuid'];
    	$this->paramsCheck($taskUUID);
    	$sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status,
                    bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time,bsr.node_uuid, 
                    bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
                    bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, bt.thread_num
                    from bd_task bt 
                    left join bd_user bu on bt.user_uuid = bu.user_uuid 
                    left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
                    left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
                    left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid 
                    where bt.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$basicInfo = array();
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$utils = Xphp::instance('Utils');
    	if(!$data){
    		$basicInfo = array('flag' => false);
    		return json_encode($basicInfo);
    	}
    	foreach ($data as $d){
    	    $opInfo = $this->getOpInfo($d);
    		$basicInfo = array(
    				'taskName' => $d['task_name'],
    				'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
    				'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],$d['module_type'], $d['task_uuid']),
    				'user' => $d['user_name'],
    				'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
    				'statusValue' => intval($d['task_status']),
    				'totalSize' => $utils->calSize($d['total_object_size']),
    				'currentSize' => $utils->calSize($d['total_object_completed_size']),
    				'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
    		        'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
    		        'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
    				'createTime' => $this->parseDate($d['create_time']),
    				'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
    				'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
    				'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
    		        'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
    				'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
    		        'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
    				'transportStrategy' => $this->getCopyTransportStrategy($taskUUID, $d['strategy_id']),
    				'storageInfo' => $this->getCopyStorageInfo($d['task_uuid'], $d['storage_uuid'], $d['node_uuid']),
    				'flag' => true,
    				'taskTypeFlag' => intval($d['task_type']),
    		        'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
    		        'subModule' => $opInfo['subModule'],
                    'thread_num' => $d['thread_num']
    		);
    	}
    	return json_encode($basicInfo);
    }

    /**
     * 任务详情: 得到任务基本信息
     * @param unknown $params
     */
    public function getDBBasicInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
                    bt.module_type, bt.strategy_id, UNIX_TIMESTAMP(bt.create_time) AS create_time,
                	bt.node_uuid, bt.storage_uuid, bt.recovery_type, bt.task_orchestration_plan_flag,
                	bt.current_stage, bt.stage_percent,
                	bu.user_name,
                	UNIX_TIMESTAMP(bs.next_start_time) AS next_start_time,
                	bri.total_object_size, bri.total_object_completed_size , bri.speed,
                	UNIX_TIMESTAMP(bri.start_time) AS start_time,
                    bres.network_retry_times, bres.network_retry_interval, bres.op_retry_times, bres.op_retry_interval, 
                    bres.task_retry_object, bres.task_retry_times, bres.task_retry_interval 
                FROM bd_task bt
                    INNER JOIN bd_user bu ON bt.user_uuid = bu.user_uuid
                    INNER JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid
                    INNER JOIN bd_strategy bs ON bt.strategy_id = bs.strategy_id
                    INNER JOIN bd_retry_strategy bres ON bt.task_uuid = bres.task_uuid
                WHERE bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        //获取数据库类型名字
        $sql_type = "select db_type from db_task where task_uuid = ?";
        $result_type = $this->dbSelect($sql_type,array($taskUUID));
        $db_type_name = Xphp::$_config['DB_TYPE_DES'][$result_type[0]['db_type']];
        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $stageArr = $ptDes['COMMON_STAGE'];
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            $currentStage = !empty($stageArr[$d['current_stage']]) ?  $stageArr[$d['current_stage']] : Xphp::$_config['NULLSPACE'];
            if ($d['stage_percent'] != -1) {
                $currentStage .= '(' . $d['stage_percent'] . '%)';
            }
            // 数据库模块的任务阶段在任务状态为等待和停止时显示--
            if ($d['task_status'] == Xphp::$_config['TASKSTATUS']['WAITTING'] || $d['task_status'] == Xphp::$_config['TASKSTATUS']['STOPPED']) {
                $currentStage = Xphp::$_config['NULLSPACE'];
            }
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
                    $d['module_type'], $d['task_uuid']),
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'statusValue' => intval($d['task_status']),
                'totalSize' => $utils->calSize($d['total_object_size']),
                'current_stage' => $currentStage,
                'currentSize' => $utils->calSize($d['total_object_completed_size']),
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'timeStrategyBackupType' => $vmHandler->getTimeStrategyBackupType($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transportStrategy' => $this->getDBTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid'], $taskUUID),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getFullSpeedlimitDes($d['task_uuid']),
                'agentInfo' => $this->getDBAgentConfig($d['task_uuid'], $d['task_type'], intval($d['recovery_type'])),
                'db_type_name' => $db_type_name,
                'db_hostname' =>$this->getAgentNameOrHostname($d['agent_name'], $d['hostname'], $d['ip']),
                'db_ip' => $d['ip'],
                'networkFlag' => $this->getNetworkShowFlag($taskUUID, $d['module_type'], $d['task_type']),//检查显示传输网络标志
                'timepoint_recovery_type' => intval($d['recovery_type']),
                'task_orchestration_plan_flag' => $utils->parseFlagToBool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $this->getTapeGroupStrategy($d['storage_uuid']) ?? '',
                'retry_strategy' => $this->groupRetryStrategyInfo($d),
                'safe_config_strategy' => $this->getSafeConfigStrategy($taskUUID),  // 安全配置策略
            );
            //如果是虚拟机备份添加备份模式配置
            if(intval($d['module_type']) == Xphp::$_config['MODULE_TYPE']['VM']){
                $basicInfo['modeStrategy'] = $this->getVMBackupModeStrategy($d['task_uuid']);
            }
        }
        return json_encode($basicInfo);
    }

    /**
     * 判断数据库别名和ip是否相同  如果相同返回主机名 如果不同返回别名
     * @param unknown $agent_name
     * @param unknown $hostname
     * @param unknown $ip
     * @author liushuai@vinchin.com
     * @return string
     */
    private function getAgentNameOrHostname($agent_name,$hostname,$ip){
        if($agent_name == '' || $agent_name == $ip){
            return $hostname;
        }else{
            return $agent_name;
        }

    }

    /**
     * 得到虚拟机备份模式配置信息
     * 静默快照,高速模式
     * @param string $taskuuid
     * @return array
     */
    private function getVMBackupModeStrategy($taskuuid){
        $sql = "select pre_create_snap_flag, level, quiesce_snapshot, hypervisor_type, valid_data_backup, serial_snapshot_flag, parse_fs_flag, 
                not_backup_swap_file_flag, not_backup_deleted_file_flag, not_backup_partition_gap_flag, storage_snapshot_enable_flag, detail  
                 from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $utils = Xphp::instance('Utils');
        $incMode = $this->getIncModeDes(intval($data[0]['level']));
        $hypervisor = intval($data[0]['hypervisor_type']);
        //红帽的CBT模式
        if(intval($data[0]['level']) == 3 && ($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM'])){
//             $incMode .= '（preview）';
        }
        $detail = $data[0]['detail'];
        $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
        $error_reset_cbt_flag = $utils->parseFlagToBool($detailArr['error_reset_cbt_flag']);
        $full_backup_reset_cbt_flag = $utils->parseFlagToBool($detailArr['full_backup_reset_cbt_flag']);
        $resetCbtLevel = 1;
        if ($full_backup_reset_cbt_flag) {
            $resetCbtLevel = 3;
        } elseif ($error_reset_cbt_flag) {
            $resetCbtLevel = 2;
        }
        $mode = array(
            'hypervisor' => $hypervisor,
            'serialSnapshot' => intval($data[0]['serial_snapshot_flag']),
            'highLevel' => $this->getHighLevel($data[0]['level']),
        	'cbtMode' => $utils->parseFlagToBool($data[0]['valid_data_backup']),
            'resetCbt' => $this->getResetCbtDes($resetCbtLevel),
            'incMode' => $incMode,
            'quiesceSnapshot' => $utils->parseFlagToBool($data[0]['quiesce_snapshot']),
            'parseFs' => $utils->parseFlagToBool($data[0]['parse_fs_flag']),
            'noSwapFile' => $utils->parseFlagToBool($data[0]['not_backup_swap_file_flag']),
            'noDeletedFile' => $utils->parseFlagToBool($data[0]['not_backup_deleted_file_flag']),
            'noPartitionGap' => $utils->parseFlagToBool($data[0]['not_backup_partition_gap_flag']),
            'presnapshot' => $utils->parseFlagToBool($data[0]['pre_create_snap_flag']),
            'storageSnapshot' => $utils->parseFlagToBool($data[0]['storage_snapshot_enable_flag']),
            'highspeedDiskCbt' => $utils->parseFlagToBool($detailArr['highspeed_disk_cbt_flag']),
        );
        return $mode;
    }

    /**
     * 得到是否高速模式
     * @param $level
     * @return bool
     */
    private function getHighLevel($level){
        return $level == 2;
    }

    /**
     * 得到增量模式描述
     * @param unknown $level
     */
    private function getIncModeDes($level){
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        return $vmDes['IncModeDes'][$level];
    }

    /**
     * 得到重置CBT描述
     * @param int $level
     * @return string
     */
    private function getResetCbtDes(int $level)
    {
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        return $vmDes['RESET_CBT_LEVEL'][$level];
    }

    /**
     * 得到任务监控界面的任务类型 ,主要是获取虚拟机的子模块
     * @param string  $taskTypeDes
     * @param int $moduleType
     * @param string $taskuuid
     */
    private function getBasicInfoTaskTypeDes($taskTypeDes, $moduleType, $taskuuid){
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $hypervisor = intval($data[0]['hypervisor_type']);
            //如果是华为CBR 不展示后面的虚拟化类型
            if($hypervisor != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CBR']){
                $taskTypeDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$hypervisor] . "]";
            }
        }
        return $taskTypeDes;
    }

    /**
     * 得到当前任务节点和存储信息
     * @param string $nodeuuid
     * @param string $storageuuid
     */
    private function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid, $taskUuid = ''){
        $info = array('flag' => false, 'worm_flag' => false);
        if(Xphp::$_config['TASKTYPE']['BACKUP'] != $tasktype
            && Xphp::$_config['TASKTYPE']['RECOVERY'] != $tasktype
            && Xphp::$_config['TASKTYPE']['DB_BACKUP'] != $tasktype
            && Xphp::$_config['TASKTYPE']['DB_RECOVERY'] != $tasktype
            && Xphp::$_config['TASKTYPE']['DRILL'] != $tasktype
            && Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] !=$tasktype
            && Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'] !=$tasktype
            && Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'] !=$tasktype
            && Xphp::$_config['TASKTYPE']['OS_BACKUP'] != $tasktype
            &&Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']!= $tasktype){
            return $info;
        }
        //恢复任务显示节点信息
        if(Xphp::$_config['TASKTYPE']['RECOVERY'] == $tasktype
            || Xphp::$_config['TASKTYPE']['OS_RECOVERY'] == $tasktype
            || Xphp::$_config['TASKTYPE']['DB_RECOVERY'] == $tasktype
            || Xphp::$_config['TASKTYPE']['DRILL'] == $tasktype
        ){
            $info['flag'] = true;
        }
        $utils = Xphp::instance('Utils');
        $sql = "SELECT bt.task_uuid, bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid,
                    bnp.node_pool_nickname, bsrp.storage_pool_nickname
                FROM bd_task bt
                    LEFT JOIN bd_node_pool bnp ON bt.node_pool_uuid = bnp.node_pool_uuid
                    LEFT JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid
                WHERE bt.task_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        if (is_array($taskData) && $taskData) {
            $info['node_uuid'] = $taskData[0]['node_uuid'];
            $info['node_pool_uuid'] = $taskData[0]['node_pool_uuid'];
            $info['storage_uuid'] = $taskData[0]['storage_uuid'];
            $info['node_pool_uuid'] = $taskData[0]['node_pool_uuid'];
            $info['storage_pool_uuid'] = $taskData[0]['storage_pool_uuid'];
            $info['node_pool_nickname'] = $taskData[0]['node_pool_nickname'];
            $info['storage_pool_nickname'] = $taskData[0]['storage_pool_nickname'];
        }
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size, worm_flag from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        if($data){
            $info['flag'] = true;
            $storageHandler = Xphp::instance('StorageHandler');
            $totalSize = $utils->calSize($data[0]['total_size']);
            $freeSize = $utils->calSize($data[0]['free_size']);
            $quotaDes = '';
            $userHandler = Xphp::instance('UsersHandler');
            $quotaInfo = $userHandler->getUserQuotaInfo();
            $quotaFlag = false;     //分配配额标记
            //设置了配额或者是在租户内部
            if($quotaInfo['quota'] != -1 || !empty($_SESSION['tenantuuid'])){
                $quotaDes = $quotaInfo['des'];
                $quotaFlag = true;
            }
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => $storageHandler->getStorageTypeDes($data[0]['storage_type']),
                'size' => $totalSize,
                'freesize' => $freeSize,
                'quotades' => $quotaDes,
                'tenantuuid' => $_SESSION['tenantuuid'],
                'quotaFlag' => $quotaFlag,
                'typenum'=>$data[0]['storage_type']
            );
            $info['worm_flag'] = $utils->parseFlagToBool($data[0]['worm_flag']);
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag, encrypted_flag, password_auto_flag, compress_method, encrypt_method from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if($data){
            $info['high'] = array(
                'deduplication' => $utils->parseFlagToBool( $data[0]['deduplication_flag']),
                'blocksize' => intval($data[0]['block_size'])/1024 . "KB",
                'compressed' => $utils->parseFlagToBool( $data[0]['compressed_flag']),
                'encrypt_flag' => $utils->parseFlagToBool( $data[0]['encrypted_flag']),
                'password_auto_flag' => $utils->parseFlagToBool( $data[0]['password_auto_flag']),
                'compress_method' => intval($data[0]['compress_method']),
                'encrypt_method' => intval($data[0]['encrypt_method']),
            );
        }
        return $info;
    }

    /**
     * 获取副本任务配置存储信息
     * @param string $taskuuid
     * @param string $storageuuid
     * @param string $nodeuuid
     */
    private function getCopyStorageInfo($taskuuid, $storageuuid, $nodeuuid){
    	$info = array('flag' => false);
    	$info['remoteflag'] = false;
    	$info['storage'] = array();
    	$utils = Xphp::instance('Utils');
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
    	//存储信息
    	$sql = "select storage_nickname, storage_type, total_size, free_size, storage_config, node_uuid from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	$storageHandler = Xphp::instance('StorageHandler');
    	if($data){
    	    $info['flag'] = true;
    		if($data[0]['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
    			$config = json_decode($data[0]['storage_config'], true);
    			$info['remoteflag'] = true;
    			$info['remote'] = array(
    					'name' => $data[0]['storage_nickname'],
    					'ip' => $config['remote_ip'].":".$config['remote_port']
    			);
    			$sqlRemote = "select real_storage_name, real_storage_type, real_storage_total_size, real_storage_free_size from backup_copy_task where task_uuid = ?";
    			$dataRemote = $this->dbSelect($sqlRemote, array($taskuuid));
    			$info['storage'] = array(
    				'name' => $dataRemote[0]['real_storage_name'],
    				'type' => $storageHandler->getStorageTypeDes($dataRemote[0]['real_storage_type']),
    				'size' => $utils->calSize($dataRemote[0]['real_storage_total_size']),
    				'freesize' => $utils->calSize($dataRemote[0]['real_storage_free_size']),
    			    'node' => $config['remote_ip'].":".$config['remote_port'],
    			);
    		}else{
    			$info['storage'] = array(
    				'name' => $data[0]['storage_nickname'],
    				'type' => $storageHandler->getStorageTypeDes($data[0]['storage_type']),
    				'size' => $utils->calSize($data[0]['total_size']),
    				'freesize' => $utils->calSize($data[0]['free_size']),
    			    'node' => $storageHandler->getNodeName($data[0]['node_uuid']),
    			);
    		}
    	}
    	return $info;
    }

    /**
     * 得到任务运行的持续时间
     * @param timestamp $startTime
     */
    public function getTimeInterval($startTime, $status){
        if($status == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == Xphp::$_config['TASKSTATUS']['ABNORMAL'] ||
            $status == Xphp::$_config['TASKSTATUS']['SUCCESSED']){
            $systemHandler = Xphp::instance('SystemHandler');
            $nowTime = $systemHandler->getSystemTime();
            if(!$startTime || $startTime == 0){
                return Xphp::$_config['TIMESPACE'];
            }
            $intval = $nowTime - $startTime;
            $utils = Xphp::instance('Utils');
            return $utils->secToTime($intval);
        }
        return Xphp::$_config['TIMESPACE'];
    }

    /**
     * 根据任务状态计算进度
     * @param int $taskStatus
     * @param int $totalSize
     * @param int $currentSize
     * @param boolean $percentFlag  是否一定要得到百分比格式
     */
    public function getTaskTotalProgress($taskStatus, $totalSize, $currentSize, $percentFlag, $tasktype){
        if($tasktype == Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] ||
           $tasktype == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] ||
           $tasktype == Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'] ||
           $tasktype == Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP']){
            //细粒度恢复,瞬时恢复,数据库实时备份,文件实时备份不显示进度
            return Xphp::$_config['NULLSPACE'];
        }

        $utils = Xphp::instance('Utils');
        $speed = $utils->calPercent($totalSize, $currentSize);

        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['PAUSED'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //如果任务没有运行
            if($percentFlag){
                return "0%";
            }else{
                return Xphp::$_config['NULLSPACE'];
            }
        }

        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        $speed = sprintf("%.2f",substr($speed, 0, -1)) . "%";
        return $speed;
    }

    /**
     * 根据任务状态计算速度
     * @param unknown $taskStatus
     * @param unknown $speed
     */
    private function getJobSpeed($taskStatus, $speed){
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING']){
            //如果任务没有运行
            return Xphp::$_config['NULLSPACE'];
        }
        $utils = Xphp::instance('Utils');
        return $utils->calSpeed($speed);
    }

    /**
     * 任务详情: 得到虚拟机列表
     * @param unknown $params
     */
    public function getDetailsVM($params){
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select vml.vm_name, vml.mode, vml.vm_size, vml.vm_valid_size, vml.completed_size, vml.transport_size, vml.write_size, vml.task_status, vml.dir_path, vml.new_name, 
                       vml.vm_uuid, vml.error_code, vml.datastore, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid, vml.extension_info,
                		bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_transport_size, bri.current_object_write_size, bri.current_object_completed_size,
                        bt.task_status as bd_task_status, bt.task_type, bt.module_type, vv.hypervisor_type
                from vm_machine_list vml 
                left join bd_running_info bri 
                on vml.task_uuid = bri.task_uuid 
                left join bd_task bt 
                on bt.task_uuid = vml.task_uuid  
                left join vm_vcenter vv
                on vml.vcenter_uuid = vv.vcenter_uuid
                where vml.task_uuid = ? group by vml.vm_uuid order by vml.machine_id";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $records["data"] = array();
        //虚拟机备份任务获取虚拟机上级对象
        $vmBackupFlag = false;
        $obj_names = [];
        if (Xphp::$_config['MODULE_TYPE']['VM'] == $data[0]['module_type'] && Xphp::$_config['TASKTYPE']['BACKUP'] == $data[0]['task_type']) {
            $vmBackupFlag = true;
            $sql = "select distinct object_name from vm_object_list where task_uuid = ? and type != ? order by type";
            $objData = $this->dbSelect($sql, [$taskUUID, Xphp::$_config['VM_TREE_TYPE']['VM']]);
            $obj_names = array_column($objData, 'object_name');
        }
        foreach ($data as $d){
            $d['objName'] = '';
            if ($vmBackupFlag) {
                foreach ($obj_names as $obj_name) {
                    if (false !== strpos($d['dir_path'], $obj_name)) {
                        $d['objName'] = $obj_name;
                        break;
                    }
                }
            }
            //如果任务正在运行,需要过滤掉新添加的虚拟机
            if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
               $d['task_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
                continue;
            }
            if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
                $d['task_status'] == Xphp::$_config['VmTaskStatus']['UNKNOWN']){
                    if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION']){
                        $records["data"][] = array(
                            $i++,
                            $d['vm_name'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            '',
                            $this->getVMDetailsInfo($d, $taskUUID),
                            $d['vcenter_uuid'],
                            intval($d['hypervisor_type'])
                        );
                    }else{
                        $records["data"][] = array(
                            '<input type="checkbox" name="id'. $d['vm_uuid'] .'" value="'. $d['vm_uuid'] .'">',
                            $i++,
                            $d['vm_name'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            Xphp::$_config['NULLSPACE'],
                            '',
                            $this->getVMDetailsInfo($d, $taskUUID),
                            $d['vcenter_uuid'],
                            intval($d['hypervisor_type'])
                        );
                    }
                    continue;
            }
            if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION']){
                $records["data"][] = array(
                    $i++,
                    $d['vm_name'],
                    $this->getCurrentVMListTaskType($d['task_type'], $d['mode'], $d['bd_task_status']),
                    $this->getVMListSize($d['bd_task_status'], $d['vm_size']),
                    $this->getVMListSize($d['bd_task_status'], $d['vm_valid_size']),
                    $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['task_status']),
                    $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['task_status']),
                    $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time'], $d['module_type']),
                    $this->getVMPercent($d['vm_valid_size'], $d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['task_status']),
                    $this->getVMStatus($d['bd_task_status'], $d['task_status']),
                    $this->getErrorCodeDes($d['task_status'], $d['error_code']),
                    $this->getVMDetailsInfo($d, $taskUUID),
                    $d['vcenter_uuid'],
                    intval($d['hypervisor_type'])
                );
            }else{
                $records["data"][] = array(
                    '<input type="checkbox" name="id'. $d['vm_uuid'] .'" value="'. $d['vm_uuid'] .'">',
                    $i++,
                    $d['vm_name'],
                    $this->getCurrentVMListTaskType($d['task_type'], $d['mode'], $d['bd_task_status']),
                    $this->getVMListSize($d['bd_task_status'], $d['vm_size']),
                    $this->getVMListSize($d['bd_task_status'], $d['vm_valid_size']),
                    $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['task_status']),
                    $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['task_status']),
                    $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time'], $d['module_type']),
                    $this->getVMPercent($d['vm_valid_size'], $d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['task_status']),
                    $this->getVMStatus($d['bd_task_status'], $d['task_status']),
                    $this->getErrorCodeDes($d['task_status'], $d['error_code']),
                    $this->getVMDetailsInfo($d, $taskUUID),
                    $d['vcenter_uuid'],
                    intval($d['hypervisor_type'])
                );
            }
        }

        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }

    private function getVMSelectConditions($taskuuid) {
        $sql = "select select_conditions from vm_task where task_uuid = ?";
        $rules = $this->dbSelect($sql, [$taskuuid])[0]['select_conditions'];
        $rules =  $rules ?? 'NULL';
        return json_decode($rules, true);
    }

    private function getVMTaskDetail($taskuuid) {
        $sql = "select detail from vm_task where task_uuid = ?";
        $rules = $this->dbSelect($sql, [$taskuuid])[0]['detail'];
        return json_decode($rules, true);
    }

    /**
     * 任务详情: 得到备份对象
     * @param array $params
     * @return string
     */
    public function getDetailsObjects($params) {
        $taskUUID = $params['uuid'];
        $sql = "select type, vcenter_uuid, exclude_vm_uuid_list, object_name, dir_path from vm_object_list 
            where task_uuid = ? and type != ?";
        $data = $this->dbSelect($sql, [$taskUUID, Xphp::$_config['VM_TREE_TYPE']['VM']]);
        $objects = [];
        $i = 1;
        $vmHandler = Xphp::instance('Vmhandler');
        foreach ($data as $d) {
            $ex_vm_uuids = implode("','", explode(',', trim($d['exclude_vm_uuid_list'], ',')));
            $sql = "select vt.dir_path from vm_tree vt join vm_machine vm on vt.vcenter_uuid = vm.vcenter_uuid 
                        and vt.uuid = vm.vm_uuid where vt.uuid in('" . $ex_vm_uuids . "') and vt.vcenter_uuid = ? and vt.display_mode = ?";
            $data2 = $this->dbSelect($sql, [$d['vcenter_uuid'], Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']]);
            $ex_vms = array_column($data2, 'dir_path');
            $objects[] = [
                $i++,
                $d['object_name'],
                $vmHandler->getVmTreeDes($d['type']),
                $d['dir_path'],
                $ex_vms
            ];
        }
        return json_encode(['data' => $objects]);
    }

    /**
     * 任务详情: 得到虚拟机列表
     * @param unknown $params
     */
    public function getCopyDetailsVM($params){
    	//TODO
    	$start = $params['start'];
    	$length = $params['length'];
    	$draw = $params['draw'];
    	$taskUUID = $params['uuid'];
    	$sql = "select bcil.item_name as vm_name, bcil.source_timepoint_list,bcil.src_timepoint_count, bcil.write_size, bcil.total_copy_size, bcil.complete_size, bcil.transport_size, bcil.write_size, bcil.copy_status,
                       bcil.item_uuid as vm_uuid, bcil.error_code, bcil.new_timepoint_list, bcil.vcenter_uuid,
                		bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_transport_size, bri.current_object_write_size, bri.current_object_completed_size,
                        bt.task_status as bd_task_status, bt.task_type
                from backup_copy_item_list bcil
                left join bd_running_info bri
                on bcil.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = bcil.task_uuid
                where bcil.task_uuid = ? group by bcil.vcenter_uuid, bcil.item_uuid order by bcil.item_id";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$records = array();
    	$i = 1;
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$records["data"] = array();
    	foreach ($data as $d){
    		//如果任务正在运行,需要过滤掉新添加的虚拟机
//     		if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
//     		    $d['copy_status'] == Xphp::$_config['CopyTaskStatus']['NEW_ADD']){
//     			continue;
//     		}
    		$timepointCount = intval($d['src_timepoint_count']);
    		$records["data"][] = array(
    			$i++,
    			$d['vm_name'],
    			$this->getCopyTimepointNum($timepointCount,$d['bd_task_status']),
    			$this->getVMListSize($d['bd_task_status'], $d['total_copy_size']),
                $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMSpeed($d['copy_status'], $d['speed'], $d['speed_time'],0,true),
                $this->getVMPercent($d['total_copy_size'], $d['current_object_completed_size'], $d['complete_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMStatus($d['bd_task_status'], $d['copy_status'],true),
                $this->getErrorCodeDes($d['copy_status'], $d['error_code'],true),
    			$this->getVMDetailsInfo($d, $taskUUID),
        	);
    	}

    	$records["draw"] = $params['draw'];
    	return  json_encode($records);
    }

    /**
     * 获取数据库任务详情信息
     * @param array $params
     * @return string
     */
    public function getDetailsDB($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $searchValue = $params['search_value'];
        $utils = Xphp::instance('Utils');
        $sql = "SELECT dl.db_name, dl.instance_name, dl.task_status AS db_status, dl.dir_path, dl.backup_mode,
                    UNIX_TIMESTAMP(dl.log_rollback_time) AS log_rollback_time, dl.database_id, dl.db_uuid, dl.error_code,
                    dl.transport_size, dl.write_size, dl.timepoint_uuid, dl.new_db_name, dl.data_file_path,
                    dl.agent_uuid, dl.recovery_mode, dl.source_instance_name, dl.source_db_name,
                    dl.source_agent_uuid, dl.before_task_script, dl.after_task_script, dl.verification_script,
                    dl.detail,
                    bri.current_object_transport_size, bri.current_object_write_size, bri.speed, bri.speed_time,
                    bt.task_status AS bd_task_status, bt.task_type, bt.recovery_type,
                    dt.db_type, dt.multi_task_flag
                FROM db_list dl
                    LEFT JOIN bd_running_info bri ON dl.task_uuid = bri.task_uuid
                    LEFT JOIN bd_task bt ON bt.task_uuid = dl.task_uuid 
                    LEFT JOIN db_task dt ON dl.task_uuid = dt.task_uuid
                WHERE dl.task_uuid = ? ORDER BY dl.database_id";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $records["data"] = array();
        $allDbType = Xphp::$_config['DB_TYPE'];
        foreach ($data as $d) {
            //如果任务正在运行,需要过滤掉新添加的虚拟机
            if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
                $d['db_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
                    continue;
            }
            $backupMode = $d['backup_mode'];
            if($backupMode == 4){
                if(Xphp::$_config['DB_TYPE']['ORACLE'] == intval($d['db_type']) || Xphp::$_config['DB_TYPE']['DM'] == intval($d['db_type'])){
                    $backupMode = 5;
                }
            }
            $sql = "SELECT ba.ip, ba.agent_name, ba.hostname, baa.cluster_flag, baa.cluster_uuid,
                        baa.cluster_name, baa.cluster_service_ip, baa.app_service_name, baa.app_name
                    FROM bd_agent ba
                        LEFT JOIN bd_agent_app baa ON baa.agent_uuid = ba.agent_uuid
                    WHERE baa.app_type = ? AND baa.app_name = ? AND baa.agent_uuid = ? ";
            $sqlParams = [$d['db_type'], $d['instance_name'], $d['agent_uuid']];
            $agentInfo = $this->dbSelect($sql, $sqlParams);
            $clusterFlag = $utils->parseFlagToBool($agentInfo[0]['cluster_flag']) && $agentInfo[0]['cluster_uuid'];
            $name = '';
            $dbName = $d['db_name'];
            if ($clusterFlag) {
                switch ($d['db_type']) {
                    case $allDbType['ORACLE']:  // 主机名(IP 实例名)
                        $sql = "SELECT ba.ip, ba.agent_name, ba.hostname, baa.app_name
                                FROM bd_agent ba
                                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = ba.agent_uuid
                                WHERE baa.cluster_uuid = ? ";
                        $appData = $this->dbSelect($sql, [$agentInfo[0]['cluster_uuid']]);
                        foreach ($appData as $appInfo) {
                            if ($appInfo['agent_name'] && $appInfo['agent_name'] != $appInfo['ip']) {
                                $name .= $appInfo['agent_name'] . '(' . $appInfo['ip'] . ' ' . $appInfo['app_name'] . ')<br>';
                            } else {
                                $name .= $appInfo['hostname'] . '(' . $appInfo['ip'] . ' ' . $appInfo['app_name'] . ')<br>';
                            }
                        }
                        break;
                    case $allDbType['TIDB']:  // 角色名(ip)
                        $sql = "SELECT ba.ip, ba.agent_name, ba.hostname, baa.app_name, baa.app_detail
                                FROM bd_agent ba
                                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = ba.agent_uuid
                                WHERE baa.cluster_uuid = ? ";
                        $appData = $this->dbSelect($sql, [$agentInfo[0]['cluster_uuid']]);
                        foreach ($appData as $appInfo) {
                            $appDetail = json_decode($appInfo['app_detail'], true);
                            if ($appInfo['agent_name'] && $appInfo['agent_name'] != $appInfo['ip']) {
                                $name .= $appInfo['agent_name'] . '(' . $appInfo['ip'] . ' ' . $appDetail['node_role'] . ')<br>';
                            } else {
                                $name .= $appInfo['hostname'] . '(' . $appInfo['ip'] . ' ' . $appDetail['node_role'] . ')<br>';
                            }
                        }
                        break;
                    case $allDbType['MONGODB']:  // 主机名(IP)
                    case $allDbType['SQLSERVER']:
                    case $allDbType['DM']:
                    case $allDbType['POSTGRE']:
                    case $allDbType['ANTDB']:
                    case $allDbType['KINGBASE']:
                    case $allDbType['UXDB']:
                    case $allDbType['HIGHGO']:
                    case $allDbType['OPENGAUSS']:
                    case $allDbType['VASTBASE']:
                    case $allDbType['MYSQL']:
                    case $allDbType['MARIA']:
                    case $allDbType['SAPHANA']:
                        $sql = "SELECT ba.ip, ba.agent_name, ba.hostname, baa.app_name, baa.app_detail
                                FROM bd_agent ba
                                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = ba.agent_uuid
                                WHERE baa.cluster_uuid = ? ";
                        $appData = $this->dbSelect($sql, [$agentInfo[0]['cluster_uuid']]);
                        foreach ($appData as $appInfo) {
                            if ($appInfo['agent_name'] && $appInfo['agent_name'] != $appInfo['ip']) {
                                $name .= $appInfo['agent_name'] . '(' . $appInfo['ip'] . ')<br>';
                            } else {
                                $name .= $appInfo['hostname'] . '(' . $appInfo['ip'] . ')<br>';
                            }
                        }
                        break;
                    default:
                        $name = $agentInfo[0]['cluster_name'];
                    break;
                }
                if ($d['task_type'] == Xphp::$_config['TASKTYPE']['DB_BACKUP']) {  // 备份
                    switch ($d['db_type']) {
                        case $allDbType['DM']:
                        case $allDbType['POSTGRE']:
                        case $allDbType['ANTDB']:
                        case $allDbType['KINGBASE']:
                        case $allDbType['UXDB']:
                        case $allDbType['HIGHGO']:
                        case $allDbType['OPENGAUSS']:
                        case $allDbType['VASTBASE']:
                        case $allDbType['MONGODB']:
                            $dbName = $agentInfo[0]['cluster_name'];
                            break;
                    }
                } else {  // 恢复
                    if ($d['recovery_type'] == 2) {  // 定时恢复最新备份点
                        switch ($d['db_type']) {
                            case $allDbType['DM']:
                            case $allDbType['POSTGRE']:
                            case $allDbType['ANTDB']:
                            case $allDbType['KINGBASE']:
                            case $allDbType['UXDB']:
                            case $allDbType['HIGHGO']:
                            case $allDbType['OPENGAUSS']:
                            case $allDbType['VASTBASE']:
                            case $allDbType['MONGODB']:
                                $dbName = $agentInfo[0]['cluster_name'];
                                break;
                        }
                    } else {
                        $sql = "SELECT cluster_name FROM db_backup_timepoint WHERE timepoint_uuid = ? ";
                        $pointData = $this->dbSelect($sql, [$d['timepoint_uuid']]);
                        if (is_array($pointData) && $pointData && $pointData[0]['cluster_name']) {
                            switch ($d['db_type']) {
                                case $allDbType['DM']:
                                case $allDbType['POSTGRE']:
                                case $allDbType['ANTDB']:
                                case $allDbType['KINGBASE']:
                                case $allDbType['UXDB']:
                                case $allDbType['HIGHGO']:
                                case $allDbType['OPENGAUSS']:
                                case $allDbType['VASTBASE']:
                                case $allDbType['MONGODB']:
                                    $dbName = $pointData[0]['cluster_name'];
                                    break;
                            }
                        }
                    }
                }
            } else {
                //客户端名  别名不为空并且不等于IP 显示别名+ip
                if (!empty($agentInfo[0]['agent_name']) && $agentInfo[0]['agent_name'] != $agentInfo[0]['ip']) {
                    $name = $agentInfo[0]['agent_name'] . "(" . $agentInfo[0]['ip'] . ")";
                }else {
                    $name = $agentInfo[0]['hostname'] . "(" . $agentInfo[0]['ip'] . ")";
                }
            }
            if ($searchValue) {
                $dbNameFlag = strpos($dbName, $searchValue);
                $filterDbName = str_replace('<br>', '', $name);
                $nameFlag = strpos($filterDbName, $searchValue);
                if ($dbNameFlag === false && $nameFlag === false) {
                    continue;
                }
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id'. $d['db_uuid'] .'" value="'. $d['db_uuid'] .'">',
                $i++,
                $dbName,
                $name,
                $this->getCurrentVMListTaskType($d['task_type'], $backupMode, $d['bd_task_status'], intval($d['db_type'])),
                $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['db_status']),
                $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['db_status']),
                $this->getVMSpeed($d['db_status'], $d['speed'], $d['speed_time']),
                $this->getVMStatus($d['bd_task_status'], $d['db_status']),
                $this->getErrorCodeDes($d['db_status'], $d['error_code']),
                $this->getDBDetailsInfo($d, $taskUUID),
            );
        }

        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }

    /**
     * 任务详情: 得到副本数据库列表
     * @param unknown $params
     */
    public function getCopyDetailsDB($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select bcil.item_name as db_name, bcil.source_timepoint_list,bcil.src_timepoint_count, bcil.write_size, bcil.total_copy_size, bcil.complete_size, bcil.transport_size, bcil.write_size, bcil.copy_status,
                       bcil.item_uuid as vm_uuid, bcil.error_code, bcil.new_timepoint_list, bcil.vcenter_uuid,
                		bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_write_size, bri.current_object_write_size, bri.current_object_completed_size,
                        bt.task_status as bd_task_status, bt.task_type
                from backup_copy_item_list bcil
                left join bd_running_info bri
                on bcil.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = bcil.task_uuid
                where bcil.task_uuid = ? group by bcil.vcenter_uuid, bcil.item_uuid order by bcil.item_id";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            //如果任务正在运行,需要过滤掉新添加的虚拟机
//             if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
//                 $d['copy_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
//                     continue;
//             }
            $timepointCount = intval($d['src_timepoint_count']);
            $records["data"][] = array(
                $i++,
                $d['db_name'],
                $this->getCopyTimepointNum($timepointCount,$d['bd_task_status']),
                $this->getVMListSize($d['bd_task_status'], $d['total_copy_size']),
                $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMSpeed($d['copy_status'], $d['speed'], $d['speed_time'],0,true),
                $this->getVMPercent($d['total_copy_size'], $d['current_object_completed_size'], $d['complete_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMStatus($d['bd_task_status'], $d['copy_status'],true),
                $this->getErrorCodeDes($d['copy_status'], $d['error_code'],true),
                $this->getVMDetailsInfo($d, $taskUUID),
            );
        }

        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }
    /**
     * 获取卷/应用信息详情
     * @param array $params
     */
    public function getDetailsVolInfo($params){
        $taskType = $params['task_type'];
        $taskUuid = $params['uuid'];
        $sql = "SELECT current_task_running_stage from cdp_vol_task where task_uuid= ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskCurrentStage = 0; //任务阶段
        if(!empty($data)){
            $taskCurrentStage = $data[0]['current_task_running_stage'];
        }
        $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] || $taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']){   //备份
            if($taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER'] || $taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER_STARTING']){ //任务阶段处于接管中
                $taskVolInfo = $volCdpHandler->getTakeoverTaskVolInfo($params);
            }else{
                $taskVolInfo = $volCdpHandler->getBackupTaskVolInfo($params);
            }
        }else if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){ //恢复
            $taskVolInfo = $volCdpHandler->getRecoverTaskVolInfo($params);
        }else if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){ //接管
            $taskVolInfo = $volCdpHandler->getTakeoverTaskVolInfo($params);
        }
        return $taskVolInfo;
    }
    /**
     * 获取卷CDP任务数据流向状态
     * @param string task_id,int task_type
     * @return 可操作项array
     */
    public function getVolCdpTaskMapInfo($params){
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];
        $volCdpHandler = Xphp::instance('VolCDPTaskHandler');

        if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] || $taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']){   //备份 or 复制
            $taskMapInfo = $volCdpHandler->getBackupTaskMapInfo($params);

        }else if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){ //恢复
            $taskMapInfo = $volCdpHandler->getRecoverTaskMapInfo($params);

        }else if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){ //接管
            $taskMapInfo = $volCdpHandler->getTakeoverTaskMapInfo($params);
        }
        return $taskMapInfo;
    }

    private function getCopyTimepointNum($timepointCount,$status){
    	if($status != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
    			$status != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
    		//如果任务不在运行状态,这个时候不显示大小
    		return Xphp::$_config['NULLSPACE'];
    	}
    	if(!$timepointCount) return Xphp::$_config['NULLSPACE'];
    	$length = $timepointCount;
    	return $length;
    }

    /**
     * 得到虚拟机列表的大小
     * @param int $taskStatus   任务状态
     * @param int $size         大小
     */
    private function getVMListSize($taskStatus, $size){
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //如果任务不在运行状态,这个时候不显示大小
            return Xphp::$_config['NULLSPACE'];
        }
        $utils = Xphp::instance('Utils');
        return $utils->calSize($size, true);
    }

    /**
     * 得到虚拟机列表详情信息
     * @param array $d
     * @param string $taskuuid
     */
    private function getVMDetailsInfo($d, $taskuuid){
        if($d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP']){
            //虚拟机备份
            return $this->getBackupDetailsInfo($d);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['RECOVERY']){
            //虚拟机恢复
            return $this->getRecoveryDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION']){
            //虚拟机迁移
            return $this->getMotionDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'] ||
            $d['task_type'] == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY_FETCH'] ||
            $d['task_type'] == Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY_FETCH']){
        	//虚拟机副本|数据库副本
            return $this->getCopyDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
            //归档|归档回传
            return $this->getArchiveDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['DB_BACKUP']){
            //数据库备份
            return $this->getDBBackupDetailsInfo($d, $taskuuid);
        }elseif(
            $d['task_type'] == Xphp::$_config['TASKTYPE']['DB_RECOVERY'] ||
            $d['task_type'] == Xphp::$_config['TASKTYPE']['DRILL']
        ){
            //数据库恢复
            return $this->getDBRecoveryDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['OS_BACKUP']){
            return $this->getOSBackupDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['OS_RECOVERY']){
            return $this->getOSRecoveryDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY_MOTION']){
            //迁移
            return $this->getOSMotionDetailsInfo($d,$taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
            //数据验证
            return $this->getVerifyDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']){
            //下云同步
            return $this->getHUAWEICBRDetailsInfo($d,$taskuuid);
        }
    }
    /**
     * 获取文件任务详情信息
     * @param unknown $params
     * @return string
     */
    public function getDetailsFs($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $keyword = $params['keyword'];
        $sql = "select distinct ba.ip, ba.hostname,  ba.detail,ba.agent_uuid,ba.agent_name as nickname, 
        bt.task_name, bt.task_type,bt.task_status as fs_task_status, bt.module_type,bt.sub_module_type,    
        fpl.recovery_timepoint_uuid as sour_timepointuuid,fpl.backup_mode,fpl.new_root_path,  
        fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail,fbt.agent_uuid as uuid,ft.file_archive_flag,
        btal.task_status,btal.task_uuid,btal.detail from bd_task bt left join fs_task ft on
        bt.task_uuid = ft.task_uuid left join fs_path_list fpl on
        bt.task_uuid = fpl.task_uuid left join bd_task_agent_list btal on
        fpl.task_uuid = btal.task_uuid left join bd_agent ba on 
        btal.agent_uuid = ba.agent_uuid left join fs_backup_timepoint fbt on
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid where bt.task_uuid = ? group by ba.agent_uuid order by btal.id";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $records["data"] = array();
        // 客户端数量
        $sqlCount = "select count(agent_uuid) as total from bd_task_agent_list where task_uuid = ? ";
    	$agentcount = $this->dbSelect($sqlCount, array($taskUUID));
        $src_module_type = $this->getNasResorceInfo($taskUUID)['src_type'];
    	foreach ($data as $d){
            //文件列表
            $list = array();
            if($d['task_type']==2) {//恢复
                // 路径
                $sql_path = "select path_name from fs_path_list where task_uuid = ?;";
                $sqlParams_path = array($d['task_uuid']);
                // 文件个数总个数等信息
                $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.task_uuid = ?;";
                $fileHandler = Xphp::instance('FileHandler');
                $sourhost = $fileHandler->getAgentNameStr($d['uuid'],$d['agent_name'],$d['agent_ip'],$src_module_type);
                // 若“别名”等于“IP”，则显示“主机名/IP”
                if($d['ip'] ==$d['nickname']) {
                    $desagent = $d['hostname'].'('.$d['ip'].')';
                }else {
                    $desagent = $d['nickname'].'('.$d['ip'].')';
                }
            }else {
                $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.agent_uuid = ? and fri.task_uuid = ?;";
                $sql_path = "select path_name from fs_path_list where agent_uuid = ? and task_uuid = ?;";
                $sqlParams_path = array($d['agent_uuid'],$d['task_uuid']);
                // 备份
                // 若“别名”等于“IP”，则显示“主机名/IP”
                if($d['ip'] ==$d['nickname']) {
                    $sourhost = $d['hostname'].'('.$d['ip'].')';
                }else {
                    $sourhost = $d['nickname'].'('.$d['ip'].')';
                }
            }
            //如果关键词不是空 并且 搜索内容不在源端主机名中，则跳过
            if (!empty($keyword) && strpos($sourhost, $keyword) === false) {
                continue;
            }
            $data_path = $this->dbSelect($sql_path, $sqlParams_path);
            foreach($data_path as $each_path) {
                array_push($list,$each_path['path_name']);
            }
            //文件数量
            $data_count = $this->dbSelect($sql_count, $sqlParams_path);
            //通配符
            if($d['fs_task_status']!=2 || $d['task_status']==1) {//任务不是运行中,客户端是等待状态都应显示--
                $ttype = Xphp::$_config['NULLSPACE'];
                $total_fs_count = Xphp::$_config['NULLSPACE'];
                $current_fs_count = Xphp::$_config['NULLSPACE'];
                $current_dir_count = Xphp::$_config['NULLSPACE'];
                $agent_status =Xphp::$_config['NULLSPACE'];
                $progress = Xphp::$_config['NULLSPACE'];

            }else {
                if($d['task_type']==2) {//恢复
                    $ttype = $ptDes['TASKTYPEDES'][$d['task_type']];
                }else {
                    $ttype =  Xphp::$_pfdes['BACKUP_MODE_DES'][$d['backup_mode']]. Xphp::$_lang['UI_PLATFORM_BACKUP'];
                }
                $total_fs_count = $data_count[0]['total_fs_count'];
                $current_fs_count = $data_count[0]['current_fs_count'];
                $current_dir_count = $data_count[0]['current_dir_count'];
                $agent_status = $ptDes['AGENT_TASK_STATUS'][$d['task_status']];
                $progress = $this->getFsAgentProgress($taskUUID,$d['agent_uuid'],intval($d['fs_task_status']),intval($d['task_status']));
            }
            $wildcardInfo = json_decode($d['detail'],true);
            $records["data"][] = array(
                '<input type="checkbox"  name="id'. $d['agent_uuid'] .'" value="' . $d['agent_uuid'] . '">',
                $i++,
				$sourhost,
                $ttype,
                $total_fs_count,
                $current_fs_count,
                $current_dir_count,
                $progress,
                $agent_status,
                '',
                $list,
                array(
                    'level'=>$d['task_status'],
                    'popover' => $ptDes['AGENT_TASK_STATUS'][$d['task_status']]
                ),
                'des_agent' => $desagent,
                array(
                    'wildcardmode' => $wildcardInfo['wildcard_mode'],
                    'wildcard' => $wildcardInfo['wildcard']
                ),
                'tasktype' => $ptDes['TASKTYPEDES'][$d['task_type']],
                'taskStatus' => $d['fs_task_status'],
                'path' => $d['new_root_path'] == "" ? Xphp::$_lang['UI_FILE_RECOVERY_OLD_PATH'] : $d['new_root_path'],
                'file_archive_flag' => $d['file_archive_flag'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $src_module_type,
                'cross_flag' => $d['sub_module_type'] == $src_module_type ? false : true,//跨平台标记
                'cross_des' => $this->getCrossDes($src_module_type, $d['sub_module_type'],$d['task_type']),//跨平台描述
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $agentcount[0]['total'];
        $records["recordsFiltered"] = $agentcount[0]['total'];
        return  json_encode($records);
    }
    /**
     * 根据任务状态计算进度
     * @param int $taskStatus
     * @param int $totalSize
     */
    public function getFsAgentProgress($taskUUID, $agentUUID,$taskStatus,$agentStatus){
        $sql = "select current_object_total_size, current_object_completed_size from fs_running_info where task_uuid = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID,$agentUUID));
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['PAUSED'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //如果任务没有运行
            return Xphp::$_config['NULLSPACE'];
        }
        //备份任务只有目录时，文件大小为0  导致进度计算出来一直是0
        if($agentStatus == 4) {
            return 100.00 ."%";
        }
        $utils = Xphp::instance('Utils');
        $speed = $utils->calPercent(intval($data[0]['current_object_total_size']), intval($data[0]['current_object_completed_size']));
        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        $speed = sprintf("%.2f",substr($speed, 0, -1)) . "%";
        return $speed;
    }

    /**
     * 获取NAS任务详情信息
     * @param unknown $params
     * @return string
     */
    public function getDetailsNas($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select distinct ba.ip, ba.hostname,  ba.detail,ba.agent_uuid,
        bt.task_name, bt.task_type,bt.task_status as fs_task_status, bt.module_type,bt.sub_module_type,   
        fpl.recovery_timepoint_uuid as sour_timepointuuid,fpl.backup_mode,fpl.new_root_path,  
        fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail,fbt.agent_uuid as uuid,nt.file_archive_flag,
        btal.task_status,btal.task_uuid,btal.detail,
        nt.nas_uuid from bd_task bt left join nas_task nt on
        bt.task_uuid = nt.task_uuid left join fs_path_list fpl on
        nt.task_uuid = fpl.task_uuid left join bd_task_agent_list btal on
        fpl.task_uuid = btal.task_uuid left join bd_agent ba on 
        btal.agent_uuid = ba.agent_uuid left join fs_backup_timepoint fbt on
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid where nt.task_uuid = ? group by ba.agent_uuid order by ba.id ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $records["data"] = array();
        // nas设备数量
        $sqlCount = "select count(nas_uuid) as total from nas_task where task_uuid = ? ";
    	$agentcount = $this->dbSelect($sqlCount, array($taskUUID));
        //恢复目标nas设备  备份源nas设备
        $nasdevice = $this->dbSelect('select nsr.ip, nsr.nas_nickname, nsr.share_path, snapshot_flag from nas_storage_resource nsr, nas_task nt where nt.nas_uuid = nsr.nas_uuid and nt.task_uuid = ?;', array($taskUUID));
        if($nasdevice[0]['nas_nickname'] == $nasdevice[0]['ip']) {
            $des_nas = $nasdevice[0]['ip'].'('.$nasdevice[0]['share_path'].')';
        }else {
            $des_nas = $nasdevice[0]['ip'].'('.$nasdevice[0]['nas_nickname'].')';
        }
        $src_module_type = $this->getNasResorceInfo($taskUUID)['src_type'];
        //文件列表
        $list = array();
        // 路径
        $sql_path = "select path_name from fs_path_list where task_uuid = ?;";
         // 文件个数总个数等信息
         $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.task_uuid = ?;";
        $sqlParams_path = array($taskUUID);
        $data_path = $this->dbSelect($sql_path, $sqlParams_path);
        $list = array_column($data_path,'path_name');
        //文件数量
        $data_count = $this->dbSelect($sql_count, $sqlParams_path);
        foreach ($data as $d){
            if($d['task_type']==2) {//恢复
                $sourhost = Xphp::instance('FileHandler')->getAgentNameStr($d['agent_uuid'], $d['agent_name'], $d['agent_ip'],$src_module_type);
            } else {
                $sourhost = $des_nas;
            }
            //通配符
            if($d['fs_task_status']!=2 || $d['task_status']==1) {//任务不是运行中,客户端不是等待状态都应显示--
                $ttype = Xphp::$_config['NULLSPACE'];
                $total_fs_count = Xphp::$_config['NULLSPACE'];
                $current_fs_count = Xphp::$_config['NULLSPACE'];
                $current_dir_count = Xphp::$_config['NULLSPACE'];
                $agent_status =Xphp::$_config['NULLSPACE'];

            }else {
                if($d['task_type']==2) {//恢复
                    $ttype = $ptDes['TASKTYPEDES'][$d['task_type']];
                }else {
                    $ttype =  Xphp::$_pfdes['BACKUP_MODE_DES'][$d['backup_mode']]. Xphp::$_lang['UI_PLATFORM_BACKUP'];
                }
                $total_fs_count = $data_count[0]['total_fs_count'];
                $current_fs_count = $data_count[0]['current_fs_count'];
                $current_dir_count = $data_count[0]['current_dir_count'];
                $agent_status = $ptDes['AGENT_TASK_STATUS'][$d['task_status']];
            }
            $wildcardInfo = json_decode($d['detail'],true);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                $i++,
                $sourhost,
                $ttype,
                $total_fs_count,
                $current_fs_count,
                $current_dir_count,
                $agent_status,
                '',
                $list,
                array(
                    'level'=>$d['task_status'],
                    'popover' => $ptDes['AGENT_TASK_STATUS'][$d['task_status']]
                ),
                'res_nas' => $sourhost,
                array(
                    'wildcardmode' => $wildcardInfo['wildcard_mode'],
                    'wildcard' => $wildcardInfo['wildcard']
                ),
                'tasktype' => $ptDes['TASKTYPEDES'][$d['task_type']],
                'path' => $d['new_root_path'] == "" ? Xphp::$_lang['UI_FILE_RECOVERY_OLD_PATH'] : $d['new_root_path'],
                'sharepath' => $nasdevice[0]['share_path'],
                'file_archive_flag' => $d['file_archive_flag'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $src_module_type,
                'cross_flag' => $d['sub_module_type'] == $src_module_type ? false : true,//跨平台标记
                'cross_des' => $this->getCrossDes($src_module_type, $d['sub_module_type'],$d['task_type']),//跨平台描述
                'des_nas' => $des_nas,
                'snapshot_flag' => $nasdevice[0]['snapshot_flag'],
            );
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $agentcount[0]['total'];
        $records["recordsFiltered"] = $agentcount[0]['total'];
        return  json_encode($records);
    }

    /**
     * 获取跨平台恢复描述
     * @param unknown $params
     */
    private function getCrossDes($src_module_type,$des_module_type,$task_type) {
        $des = Xphp::$_config['NULLSPACE'];
        if ($task_type == Xphp::$_config['TASKTYPE']['RECOVERY']) {
            $des = $this->getFsSubDes($src_module_type) . Xphp::$_lang['UI_NAS_RECOVER_DEVICE'] . $this->getFsSubDes($des_module_type);
        }
        return $des;
    }

    private function getFsSubDes($sub_type) {
        $des = Xphp::$_config['NULLSPACE'];
        switch (intval($sub_type)) {
            case 1:   //fs
                $des = Xphp::$_lang['UI_FILE_RECOVERY_AGENT'];
                break;
            case 2:   //nas
                $des = Xphp::$_lang['UI_NAS_DEVICE_NAME'];
                break;
            case 3:   //hadoop
                $des = Xphp::$_lang['WEB_HADOOP_CLUSTER'];
                break;
            case 4:   //obs
                $des = Xphp::$_lang['UI_PLATFORM_OBS'];
                break;
        }
        return $des;
    }

    /**
     * 获取nas源设备的信息
     * @param unknown $params
     */
    private function getNasResorceInfo($taskuuid) {
        //先找到恢复时间点
        $sql = " select fbt.detail,fbt.source_agent_type, bbt.sub_module_type from fs_path_list fpl,fs_backup_timepoint fbt,bd_backup_timepoint bbt where
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and fpl.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
        $detail = json_decode($data[0]['detail'], true);
        $sql = " select ip, share_path,nas_nickname from nas_storage_resource where nas_uuid = ?";
    	$resdata = $this->dbSelect($sql, array($detail["nas_uuid"]));
        if ($resdata[0]['nas_nickname'] != $resdata[0]['ip']) {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['nas_nickname'] . ")";
        } else {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['share_path'] . ")";
        }
        $info = array(
            "nas_name" => $nas_name,
            "source_agent_type" => $data[0]['source_agent_type'],
            "src_type" => $data[0]['sub_module_type'],
        );
        return $info;
    }

    /**
     * 得到数据库列表详情信息
     * @param array $d
     * @param string $taskuuid
     */
    private function getDBDetailsInfo($d, $taskuuid){
        if($d['task_type'] == Xphp::$_config['TASKTYPE']['DB_BACKUP'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY']){
            return $this->getDBBackupDetailsInfo($d, $taskuuid);
        }elseif(
            $d['task_type'] == Xphp::$_config['TASKTYPE']['DB_RECOVERY'] ||
            $d['task_type'] == Xphp::$_config['TASKTYPE']['DRILL']
        ){
            return $this->getDBRecoveryDetailsInfo($d, $taskuuid);
        }
    }


    /**
     * 获取数据库备份数据库详情信息
     * @param array $d
     * @param string $taskuuid
     * @return number[]|unknown[]
     */
    private function getDBBackupDetailsInfo($d, $taskuuid){
        $utils = Xphp::instance('Utils');
        $detail = [];
        $multiTaskFlag = $utils->parseFlagToBool($d['multi_task_flag']);
        if ($d['db_type'] == Xphp::$_config['DB_TYPE']['ORACLE']) {
            $detail = json_decode($d['detail'], true);
        }
        $sql = "SELECT cluster_flag, cluster_uuid FROM bd_agent_app WHERE app_type = ? AND agent_uuid = ? AND app_name = ? ";
        $appData = $this->dbSelect($sql, [$d['db_type'], $d['agent_uuid'], $d['instance_name']]);
        $dirPathInfo = $this->getDesAgentInfo($d['agent_uuid'], intval($d['db_type']), $d['instance_name'], $taskuuid);
        $info = array(
            'type' => intval($d['task_type']),
            'sPath' => $dirPathInfo['des_path'],
            'agentuuid' => $d['agent_uuid'],
            'dbuuid' => $d['db_uuid'],
            'dbtype' => intval($d['db_type']),
            'id' => $d['database_id'],
            'before_task_script' => json_decode($d['before_task_script'], true),
            'after_task_script' => json_decode($d['after_task_script'], true),
            'detail' => $detail,
            'cluster_flag' => Xphp::instance('Utils')->parseFlagToBool($appData[0]['cluster_flag']) && $appData[0]['cluster_uuid'],
            'cluster_uuid' => $appData[0]['cluster_uuid'],
            'multi_task_flag' => $multiTaskFlag,
        );

        return $info;
    }

    /**
     * 获取数据库恢复数据库详情信息
     * @param array $d
     * @param string $taskuuid
     * @return number[]|unknown[]|NULL[]|string[]
     */
    private function getDBRecoveryDetailsInfo($d, $taskuuid){
        $info = array();
        $desInfo = $this->getDesAgentInfo($d['agent_uuid'], intval($d['db_type']), $d['instance_name'], $taskuuid);
        $timepointInfo = [
            "timepoint" => '',
            "taskname" => '',
            "dirpath" => '',
        ];
        $dbType = intval($d['db_type']);
        $allDbType = Xphp::$_config['DB_TYPE'];
        if ($d['timepoint_uuid']) {
            $timepointInfo = $this->getDBTimepointInfo($d['timepoint_uuid'], intval($d['db_type']));
        } else {
            $sql = "SELECT agent_name, hostname, ip FROM bd_agent WHERE agent_uuid = ? ";
            $agentData = $this->dbSelect($sql, [$d['source_agent_uuid']]);
            $timepointInfo['dirpath'] = '--';
            if (is_array($agentData) && $agentData) {
                $sql = "SELECT ba.agent_name, ba.ip, ba.hostname, baa.app_name,
                            baa.cluster_flag, baa.cluster_name, baa.app_detail, baa.cluster_uuid
                        FROM bd_agent ba
                            LEFT JOIN bd_agent_app baa ON ba.agent_uuid = baa.agent_uuid
                        WHERE ba.agent_uuid = ? AND baa.app_type = ? AND baa.app_name = ? ";
                $data = $this->dbSelect($sql, [$d['source_agent_uuid'], $dbType, $d['source_instance_name']]);
                if (is_array($data) && $data) {  // 表示应用还在
                    if ($data[0]['cluster_uuid']) {  // 源是集群
                        $sql = "SELECT baa.app_name, baa.app_service_name, baa.cluster_name, ba.ip, baa.app_detail, baa.cluster_uuid
                                FROM bd_agent_app baa
                                    INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                                WHERE baa.cluster_uuid = ? ";
                        $clusterData = $this->dbSelect($sql, [$data[0]['cluster_uuid']]);
                        $srcPath = '--';
                        switch ($dbType) {
                            case $allDbType['ORACLE']:
                                $srcPath = $clusterData[0]['app_service_name'];
                                $appList = [];
                                foreach ($clusterData as $clusterInfo) {
                                    $appList[] = $clusterInfo['ip'] . '/' . $clusterInfo['app_name'];
                                }
                                $srcPath .= '(' . implode(', ', $appList) . ')';
                                break;
                            case $allDbType['TIDB']:
                                $srcPath = $clusterData[0]['app_name'];
                                $appList = [];
                                foreach ($clusterData as $clusterInfo) {
                                    $appDetail = json_decode($clusterInfo['app_detail'], true);
                                    $appList[] = $clusterInfo['ip'] . '/' . $appDetail['node_role'];
                                }
                                $srcPath .= '(' . implode(', ', $appList) . ')';
                                break;
                            case $allDbType['SQLSERVER']:
                            case $allDbType['SAPHANA']:
                                $srcPath = $clusterData[0]['cluster_name'];
                                $appList = [];
                                foreach ($clusterData as $clusterInfo) {
                                    $appList[] = $clusterInfo['ip'] . '/' . $clusterInfo['app_name'];
                                }
                                $srcPath .= '(' . implode(', ', $appList) . ')';
                                $srcPath  .= '/' . $d['source_db_name'];
                                break;
                            default:
                                $srcPath = $clusterData[0]['cluster_name'];
                                $appList = [];
                                foreach ($clusterData as $clusterInfo) {
                                    $appList[] = $clusterInfo['ip'] . '/' . $clusterInfo['app_name'];
                                }
                                $srcPath .= '(' . implode(', ', $appList) . ')';
                                break;
                        }
                        $timepointInfo['dirpath'] = $srcPath;
                    } else {  // 源是单机
                        $srcPath = $data[0]['ip'] . '/' . $d['source_instance_name'];
                        if ($dbType == $allDbType['SQLSERVER'] || $dbType == $allDbType['SAPHANA']) {
                            $srcPath .= '/' . $d['source_db_name'];
                        }
                        $timepointInfo['dirpath'] = $srcPath;
                    }
                } else {  // 应用被删除了
                    $dirPath = $agentData[0]['hostname'] . '(' . $agentData[0]['ip'] . ')';
                    if ($agentData[0]['ip'] != $agentData[0]['agent_name']) {
                        $dirPath = $agentData[0]['agent_name'] . '(' . $agentData[0]['ip'] . ')';
                    }
                    $dirPath .= '/' . $d['source_instance_name'];
                    if ($dbType == $allDbType['SQLSERVER'] || $dbType == $allDbType['SAPHANA']) {
                        $dirPath .= '/' . $d['source_db_name'];
                    }
                    $timepointInfo['dirpath'] = $dirPath;
                }
            }
        }
        $logrollbackTime = $d['log_rollback_time'];
        if(!empty($logrollbackTime)){
            $logrollbackTime = date('Y-m-d H:i:s', $d['log_rollback_time']);
        }
        $newName = $d['new_db_name'];
        if(empty($newName)){
            $newName = $d['db_name'];
        }
        $info = array(
            "type" => intval($d['task_type']),
            "path" => $timepointInfo['dirpath'],
            "dAgentname" => $desInfo['agentname'],
            "dInstancename" => $d['instance_name'],
            "dName" => $newName,
            "timepoint" => $timepointInfo['timepoint'],
            "taskname" => $timepointInfo['taskname'],
            "log_rollback_time" => $logrollbackTime,
            "db_type" => $dbType,
            'recovery_mode' => (int) $d['recovery_mode'],
            'recovery_level' => (int) $d['recovery_level'],
            'timepoint_recovery_type' => (int) $d['recovery_type'],
            'des_path' => $desInfo['des_path'],
            'before_task_script' => json_decode($d['before_task_script'], true),
            'after_task_script' => json_decode($d['after_task_script'], true),
            'verification_script' => json_decode($d['verification_script'], true),
        );

        return $info;
    }

    private function getDesAgentInfo($agentuuid, $dbType, $instanceName, $taskuuid) {
        $sql = "SELECT ba.agent_name, ba.ip, ba.hostname,
                    baa.cluster_flag, baa.cluster_name, baa.app_detail, baa.cluster_uuid
                FROM bd_agent ba
                    LEFT JOIN bd_agent_app baa ON ba.agent_uuid = baa.agent_uuid
                WHERE ba.agent_uuid = ? AND baa.app_type = ? AND baa.app_name = ? ";
        $data = $this->dbSelect($sql, array($agentuuid, $dbType, $instanceName));
        $info = array(
            'agentname' => $data[0]['agent_name'] == $data[0]['ip'] ? $data[0]['hostname']."(". $data[0]['ip'] .")" : $data[0]['agent_name']."(". $data[0]['ip'] .")",
            'des_path' => $data[0]['ip'] . '/' . $instanceName,
        );
        $sql = "SELECT dl.recovery_mode, dl.data_file_path,
                    dt.task_uuid
                FROM db_task dt INNER JOIN db_list dl ON dt.task_uuid = dl.task_uuid
                WHERE dt.task_uuid = ? AND dt.db_type = ?
                    AND dl.instance_name = ? AND dl.agent_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskuuid, $dbType, $instanceName, $agentuuid]);
        $utils = Xphp::instance('Utils');
        $allDbType = Xphp::$_config['DB_TYPE'];
        $appDetail = json_decode($data[0]['app_detail'], true);
        if ($taskData) {
            if ($taskData[0]['recovery_mode'] == Xphp::$_config['DB_RECOVERY_TYPE']['CREATE']) {  // 新建恢复
                if (
                    $dbType === $allDbType['POSTGRE'] ||
                    $dbType === $allDbType['ANTDB'] ||
                    $dbType === $allDbType['KINGBASE'] ||
                    $dbType === $allDbType['UXDB'] ||
                    $dbType === $allDbType['HIGHGO'] ||
                    $dbType === $allDbType['OPENGAUSS'] ||
                    $dbType === $allDbType['VASTBASE']
                ) {
                    $datafilePath = $taskData[0]['data_file_path'];
                    $info['des_path'] =  $data[0]['ip'] . '/' . explode(':', $datafilePath)[1];
                }
            }
        }
        $clusterFlag = $utils->parseFlagToBool($data[0]['cluster_flag']) && $data[0]['cluster_uuid'];
        if ($clusterFlag) {
            $sql = "SELECT baa.app_name, baa.cluster_name, baa.app_detail, ba.ip, baa.app_service_name
                    FROM bd_agent_app baa
                        INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                    WHERE baa.cluster_uuid =? AND baa.app_type = ? 
                    ORDER BY ba.ip ASC ";
            $clusterData = $this->dbSelect($sql, array($data[0]['cluster_uuid'], $dbType));
            switch ($dbType) {
                case $allDbType['ORACLE']:  // 集群服务名(ip/应用名, ip/应用名...)
                    $desPath = $clusterData[0]['app_service_name'];
                    $appList = [];
                    foreach ($clusterData as $clusterInfo) {
                        $appList[] = $clusterInfo['ip'] . '/' . $clusterInfo['app_name'];
                    }
                    $desPath .= '(' . implode(',', $appList) . ')';
                    $info['des_path'] = $desPath;
                    break;
                case $allDbType['TIDB']:  // 集群名(ip/角色名, ip/角色名...)
                    $desPath = $clusterData[0]['app_name'];
                    $appList = [];
                    foreach ($clusterData as $clusterInfo) {
                        $appDetail = json_decode($clusterInfo['app_detail'], true);
                        $appList[] = $clusterInfo['ip'] . '/' . $appDetail['node_role'];
                    }
                    $desPath .= '(' . implode(', ', $appList) . ')';
                    $info['des_path'] = $desPath;
                    break;
                case $allDbType['SQLSERVER']:
                case $allDbType['DM']:
                case $allDbType['POSTGRE']:
                case $allDbType['ANTDB']:
                case $allDbType['KINGBASE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['VASTBASE']:
                case $allDbType['MYSQL']:
                case $allDbType['MARIA']:
                case $allDbType['MONGODB']:
                case $allDbType['SAPHANA']:  // 集群名(ip/应用名, ip/应用名...)
                    $desPath = $clusterData[0]['cluster_name'];
                    $appList = [];
                    foreach ($clusterData as $clusterInfo) {
                        $appList[] = $clusterInfo['ip'] . '/' . $clusterInfo['app_name'];
                    }
                    $desPath .= '(' . implode(',', $appList) . ')';
                    $info['des_path'] = $desPath;
                    break;
            }

        }
        return $info;
    }

    /**
     * 得到备份任务虚拟机详情
     * @param array $d
     */
    private function getBackupDetailsInfo($d){
        $info = array(
            "type" => intval($d['task_type']),
            "sPath" => $d['dir_path'],
            "objName" => $d['objName']
        );
        return $info;
    }

    /**
     * 得到副本任务虚拟机详情
     * @param array $d
     */
    private function getCopyDetailsInfo($d, $taskuuid){
    	$copyHandler = Xphp::instance('CopyHandler');
    	$info = array(
    		"type" => intval($d['task_type']),
    		"sPath" => $copyHandler->getDirpath($d['vm_uuid'],$taskuuid),
    	);
    	return $info;
    }



    /**
     * 得到恢复任务虚拟机详情
     * @param array $d
     */
    private function getRecoveryDetailsInfo($d, $taskuuid){
    	$hypervisor = $this->getHypervisor($taskuuid);
    	if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){

    		$desHostInfo = $this->getOpenstackDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
    	}else{
    		$desHostInfo = $this->getDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
    	}
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $timepointDes = $timepointInfo['taskname'] . " => " . $timepointInfo['timepoint'];
        //时间点被意外删除
        if(empty($timepointInfo)){
            $timepointDes = Xphp::$_lang['WEB_JOB_TIMEPOINT_NOE_EXIST'];
        }
        $info = array(
            "type" => intval($d['task_type']),
            "path" => $d['dir_path'],
            "dVcenterIP" => $desHostInfo['vcenterIP'],
            "dHostIP" => $desHostInfo['hostIP'],
            "dName" => $d['new_name'],
            "timepoint" => $timepointInfo['timepoint'],
            "taskname" => $timepointInfo['taskname'],
            "storage" => $d['datastore'],
            "timepoint_des" => $timepointDes
        );
        return $info;
    }

    /**
     * 得到下云同步任务虚拟机详情
     * @param array $d
     */
     private function getHUAWEICBRDetailsInfo($d,$taskuuid){
        $extensioInfo =  json_decode($d['extension_info'],true);
        //如果为空
        if($extensioInfo == null){
            $info =  array(
                "path" => $d['dir_path'],
                "count"=>0,
                "backups" =>null
            );
        }else{
            $info =  array(
                "path" => $d['dir_path'],
                "count"=>intval($extensioInfo['count']),
                "backups" => $extensioInfo['backups']
            );
        }
        return $info;
    }

    /**
     * 得到迁移任务虚拟机详情
     * @param array $d
     */
    private function getMotionDetailsInfo($d, $taskuuid){
    	//迁移和恢复一个方法
    	return $this->getRecoveryDetailsInfo($d, $taskuuid);
    }


    /**
     * 根据vcenteruuid和hostuuid得到vcenterip和hostip
     * @param string $vcenteruuid
     * @param string $hostuuid
     * @return array
     */
    private function getDesHostInfo($vcenteruuid, $hostuuid){
    	$sql = "select vh.host_ip, vv.vcenter_ip
                from  vm_host vh, vm_vcenter vv
                where vh.vcenter_uuid = vv.vcenter_uuid
                and vv.vcenter_uuid = ? and vh.host_uuid = ?";
    	$data = $this->dbSelect($sql, array($vcenteruuid, $hostuuid));
    	if($data){
    		return array(
    				"hostIP" => $data[0]['host_ip'],
    				"vcenterIP" => $data[0]['vcenter_ip'],
    		);
    	}
    }

    /**
     * Openstack一类得到目的宿主机信息
     * @param string $vcenteruuid
     * @param string $hostuuid
     * @return array
     */
    private function getOpenstackDesHostInfo($vcenteruuid, $hostuuid){
        $sql = "select vh.host_ip, vv.vcenter_ip, vv.detail, vv.username 
                from  vm_host vh, vm_vcenter vv 
                where vh.vcenter_uuid = vv.vcenter_uuid
                and vv.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        if($data){
            return array(
                "hostIP" => $data[0]['host_ip'].'('.$data[0]['username'].')',
                "vcenterIP" => $data[0]['vcenter_ip'],
            );
        }
    }

    /**
     * 根据时间点uuid获取时间点的其他信息
     * @param unknown $timepointUUID
     * @return multitype:NULL
     */
    private function getTimepointInfo($timepointUUID){
        $sql = "select timepoint, task_name, backup_mode from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($timepointUUID));
        $vmHandler = Xphp::instance('Vmhandler');
        if($data){
            return array(
                "timepoint" => $data[0]['timepoint'] . "(" . $vmHandler->getTimepointTypeDes($data[0]['backup_mode']) . ")",
                "taskname" => $data[0]['task_name']
            );
        }
    }

    /**
     * 根据数据库时间点uuid获取时间点的其他信息
     * @param string $timepointUUID
     * @param int $dbType
     * @return multitype:NULL
     */
    private function getDBTimepointInfo($timepointUUID, $dbType){
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, dbt.dir_path from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($timepointUUID));
        $vmHandler = Xphp::instance('Vmhandler');
        if($data){
            $backMode = intval($data[0]['backup_mode']);
            if($backMode == 4){
                //日志备份
                if($dbType == Xphp::$_config['DB_TYPE']['ORACLE'] || $dbType == Xphp::$_config['DB_TYPE']['DM'] || $dbType == Xphp::$_config['DB_TYPE']['POSTGRE']
                || $dbType == Xphp::$_config['DB_TYPE']['ANTDB'] || $dbType == Xphp::$_config['DB_TYPE']['VASTBASE']){
                    //Oracle|DM 统称归档日志
                    $backMode = 5;
                }
            }
            return array(
                "timepoint" => $data[0]['timepoint'] . "(" . $this->getTimepointTypeDes($backMode, $dbType) . ")",
                "taskname" => $data[0]['task_name'],
                "dirpath" => $data[0]['dir_path']
            );
        }
    }

    /**
     * 得到虚拟机列表百分比
     * @param int $vmSize
     * @param int $briCompletedSize
     * @param int $vmlCompletedSize
     * @param int $bdTaskStatus
     * @param int $vmTaskStatus
     * @return string
     */
    private function getVMPercent($vmSize, $briCompletedSize, $vmlCompletedSize, $bdTaskStatus, $vmTaskStatus,$copyFlag = false){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //首先任务是在运行状态
            $utils = Xphp::instance('Utils');
            if($copyFlag){
                if($vmTaskStatus == Xphp::$_config['CopyTaskStatus']['RUNNING']){
                    return $utils->calPercent($vmSize, $briCompletedSize);
                }elseif($vmTaskStatus == Xphp::$_config['CopyTaskStatus']['WAITTING']){
                    return '0%';
                }else{
                    if($vmSize == $vmlCompletedSize) return '100%';
                    if(0 == $vmlCompletedSize) return '0%';
                    return $utils->calPercent($vmSize, $vmlCompletedSize);
                }
            }else{
                if($vmTaskStatus == Xphp::$_config['VmTaskStatus']['RUNNING']){
                    return $utils->calPercent($vmSize, $briCompletedSize);
                }elseif($vmTaskStatus == Xphp::$_config['VmTaskStatus']['WAITTING']){
                    return '0%';
                }else{
                    if($vmSize == $vmlCompletedSize) return '100%';
                    if(0 == $vmlCompletedSize) return '0%';
                    return $utils->calPercent($vmSize, $vmlCompletedSize);
                }
            }

        }else{
            return Xphp::$_config['NULLSPACE'];
        }
    }

    /**
     * 得到虚拟机列表的已完成容量
     * @param int $briCompletedSize
     * @param int $vmlCompletedSize
     * @param int $bdTaskStatus
     * @param int $vmTaskStatus
     * @param bool $copyFlag 是否是副本  因为枚举不一样所以分开处理
     * @return string
     */
    private function getVMCompletedSize($briCompletedSize, $vmlCompletedSize, $bdTaskStatus, $vmTaskStatus,$copyFlag = false){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //首先任务是在运行状态
            $utils = Xphp::instance('Utils');
            if($copyFlag){
                if($vmTaskStatus == Xphp::$_config['CopyTaskStatus']['RUNNING'] ){
                    //虚拟机是运行状态,显示bd_running_info的完成大小
                    return $utils->calSize($briCompletedSize);
                }elseif($vmTaskStatus == Xphp::$_config['CopyTaskStatus']['WAITTING'] ){
                    //虚拟机是等待状态,显示--
                    return '--';
                }else{
                    //虚拟机不在运行状态,显示vm_machine_list的完成大小
                    return $utils->calSize($vmlCompletedSize, true);
                }
            }else{
                if($vmTaskStatus == Xphp::$_config['VmTaskStatus']['RUNNING'] ){
                    //虚拟机是运行状态,显示bd_running_info的完成大小
                    return $utils->calSize($briCompletedSize);
                }elseif($vmTaskStatus == Xphp::$_config['VmTaskStatus']['WAITTING'] ){
                    //虚拟机是等待状态,显示--
                    return "--";
                }else{
                    //虚拟机不在运行状态,显示vm_machine_list的完成大小
                    return $utils->calSize($vmlCompletedSize, true);
                }
            }
        }else{
            return Xphp::$_config['NULLSPACE'];
        }
    }

    /**
     * 得到虚拟机列表的虚拟机状态
     * @param int $bdTaskStatus
     * @param int $vmTaskStatus
     */
    public function getVMStatus($bdTaskStatus, $vmTaskStatus,$copyFlag = false){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //任务在运行状态时,显示虚拟机的状态
            $vmDes = include APP_PATH . 'vm/VmDescription.php';
            if($copyFlag){
                return $vmDes['CopyTaskStatus'][$vmTaskStatus];
            }else{
                return $vmDes['VmTaskStatus'][$vmTaskStatus];
            }
        }else{
            return Xphp::$_config['NULLSPACE'];
        }
    }

    /**
     * 得到虚拟机列表的虚拟机状态
     * @param int $bdTaskStatus
     * @param int $vmTaskStatus
     */
    private function getVMVerifyStatus($bdTaskStatus, $vmTaskStatus){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                //任务在运行状态时,显示虚拟机的状态
                if($vmTaskStatus != Xphp::$_config['VERIFY_STATUS']['SKIP']){
                    $vmDes = include APP_PATH . 'vm/VmDescription.php';
                    return $vmDes['VERIFY_VM_STATUS'][$vmTaskStatus];
                }else{
                    return Xphp::$_config['NULLSPACE'];
                }
        }else{
            return Xphp::$_config['NULLSPACE'];
        }
    }

    /**
     * 根据任务运行情况估算完成时间
     * @param int $totalSize
     * @param int $currentSize
     * @param int $taskStatus
     * @param int $speed
     */
    private function getCalEndTime($totalSize, $currentSize, $taskStatus, $speed){
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //任务没有运行
            return Xphp::$_config['TIMESPACE'];
        }
        if(0 == intval($speed)){
            return Xphp::$_config['TIMESPACE'];
        }
        $needTime = ($totalSize - $currentSize) / $speed ;
        return date("Y-m-d H:i:s", time() + intval($needTime));
    }

    /**
     * 过滤开始时间
     * @param unknown $startTime
     * @return multitype:|unknown
     */
    private function getStartTIme($startTime, $status){
        if($status == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == Xphp::$_config['TASKSTATUS']['ABNORMAL'] ||
            $status == Xphp::$_config['TASKSTATUS']['SUCCESSED']){
            return $this->parseDate($startTime);
        }
        return Xphp::$_config['TIMESPACE'];
    }

    /**
     * 过滤下次启动时间
     * @param unknown $nextTime     下次开始时间
     * @param unknown $taskStatus   任务状态
     */
    public function getNextStartTime($nextTime, $taskStatus){
        $nowTime = time();
        if($nextTime <= 0){
            return Xphp::$_config['TIMESPACE'];
        }
        if($nextTime < $nowTime){
            return Xphp::$_config['TIMESPACE'];
        }

        //任务处于停止状态下,没有下次开始时间
        if(intval($taskStatus) == Xphp::$_config['TASKSTATUS']['STOPPED']){
            return Xphp::$_config['TIMESPACE'];
        }
        return $this->parseDate($nextTime);
    }

    /**
     * 得到错误描述
     * @param int $status
     * @param int $errorCode
     * @return string
     */
    private function getErrorCodeDes($status, $errorCode,$copyFlag = false){
        if($copyFlag){
            if(Xphp::$_config['CopyTaskStatus']['ERROR'] == intval($status)){
                    $error = include CONF_PATH . "error.php";
                    $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
            }else{
                $des = '';
            }
        }else{
            if(Xphp::$_config['TASKSTATUS']['ABNORMAL'] == intval($status) ||
                Xphp::$_config['TASKSTATUS']['ERROR'] == intval($status) ||
                Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] == intval($status) ){
                    $error = include CONF_PATH . "error.php";
                    $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
            }else{
                $des = '';
            }
        }

        return $des;
    }

    /**
     * 得到文件错误描述
     * @param unknown $errorCode
     */
    private function getFileErrorCodeDes($errorCode){
        $error = include CONF_PATH . "error.php";
        $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
        return $des;
    }

    /**
     * 得到虚拟机错误描述
     * @param unknown $status
     * @param unknown $errorCode
     */
    public function getVMErrorCodeDes($status, $errorCode){
        if(Xphp::$_config['VmTaskStatus']['ERROR'] == intval($status)){
            $des = Xphp::$_error['errorCodeDes'][Xphp::$_error['errorCode'][$errorCode]];
        }else{
            $des = '';
        }
        return $des;
    }

    /**
     * 得到副本错误描述
     * @param unknown $status
     * @param unknown $errorCode
     */
    public function getCopyErrorCodeDes($status, $errorCode){
    	if(Xphp::$_config['CopyTaskStatus']['ERROR'] == intval($status)){
    		$des = Xphp::$_error['errorCodeDes'][Xphp::$_error['errorCode'][$errorCode]];
    	}else{
    		$des = '';
    	}
    	return $des;
    }


    /**
     * 根据每个虚拟机的状态得到速度
     * @param int $status
     * @param int $speed
     * @return string
     */
    private function getVMSpeed($status, $speed, $speedTime, $moduleType = 0,$copyFlag = false){
        if(time() - $speedTime > 12){
            $speed = 0;
        }
        if($copyFlag){
            if(Xphp::$_config['CopyTaskStatus']['RUNNING'] == intval($status) ||
                Xphp::$_config['CopyTaskStatus']['ABNORMAL'] == intval($status)){
                    $utils = Xphp::instance('Utils');
                    $speed = $utils->calSpeed($speed);
            }else{
                $speed = '--';
            }
        }else{
            if(Xphp::$_config['TASKSTATUS']['RUNNING'] == intval($status) ||
                Xphp::$_config['TASKSTATUS']['ABNORMAL'] == intval($status)){
                    $utils = Xphp::instance('Utils');
                    $speed = $utils->calSpeed($speed);
            }else{
                $speed = '--';
            }
        }
        return $speed;
    }

    /**
     * 任务详情: 得到详细任务历史任务
     * @param unknown $params
     */
    public function getDetailsHistory($params){
        $taskUUID = $params['uuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $this->paramsCheck($taskUUID);
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'current_mode', 'error_code', 'total_object_size', '', 'total_object_transport_size',
            'total_object_write_size', 'start_time', 'finish_time'
        );
        $sql = "select task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size, total_object_write_size, 
                average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ? 
                 order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
        $sqlCountParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $utils = Xphp::instance('Utils');
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                $i++,
                $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                $this->getHistoryJobResultDes($d['error_code']),
                $utils->calSize($d['total_object_size'], true),
            	$this->getTotalValidSize($details, intval($d['module_type']), intval($d['task_type']), intval($d['total_object_size'])),
                $this->getTotalTransportSize($details, $d['module_type'], $d['total_object_transport_size']),
                $this->getTotalWriteSize($details, $d['module_type'], $d['total_object_write_size']),
                $this->parseDate($d['start_time']),
                $this->parseDate($d['finish_time']),
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type'])
                ),
            );
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 任务详情: 得到副本详细任务历史任务
     * @param unknown $params
     */
    public function getCopyDetailsHistory($params){
    	$taskUUID = $params['uuid'];
    	$start = $params['start'];
    	$length = $params['length'];
    	$draw = $params['draw'];
    	$this->paramsCheck($taskUUID);
    	$sortColumn = $params['sortColumn'];
    	$sortType = $params['sortType'];
    	$sortArr = array('', 'task_type', 'error_code', 'total_object_size','total_object_transport_size',
    			'total_object_write_size', 'start_time', 'finish_time'
    	);
    	$sql = "select task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size, total_object_write_size,
    	average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ?
    	order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
    	$sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
    	$sqlCountParams = array($taskUUID);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$count = $this->dbSelect($sqlCount, $sqlCountParams);
    	$records = array();
    	$utils = Xphp::instance('Utils');
    	$i = 1;
    	$records["data"] = array();
    	foreach ($data as $d){
	    	$details = json_decode($d['details'], true);
	    	$records["data"][] = array(
	    		$i++,
	    		$this->getHistoryTaskType($d['task_type'], $d['current_mode'] ,$d['submodule_type']),
	    		$this->getHistoryJobResultDes($d['error_code']),
	    		$utils->calSize($d['total_object_size'], true),
	    		$utils->calSize($d['total_object_transport_size'], true),
	    		$utils->calSize($d['total_object_write_size'], true),
	    		$this->parseDate($d['start_time']),
	    		$this->parseDate($d['finish_time']),
	    		array(
	    			'level'=>$this->getJobStatusShowLevel($d['error_code']),
	    			'popover' => $this->getJobStatusShowPopover($d['error_code'])
	    		),
	    		array(
	    			'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
	    			'taskType' => intval($d['task_type'])
	    		),
	    	);
    	}

    	$records["draw"] = $params['draw'];;
    	$records["recordsTotal"] = $count[0]['total'];
    	$records["recordsFiltered"] = $count[0]['total'];
    	return  json_encode($records);
    }


    /**
     * 任务详情: 得到归档详细任务历史任务
     * @param unknown $params
     */
    public function getArchiveDetailsHistory($params){
    	$taskUUID = $params['uuid'];
    	$start = $params['start'];
    	$length = $params['length'];
    	$draw = $params['draw'];
    	$this->paramsCheck($taskUUID);
    	$sortColumn = $params['sortColumn'];
    	$sortType = $params['sortType'];
    	$sortArr = array('', 'task_type', 'error_code', 'total_object_size','total_object_transport_size',
    			'total_object_write_size', 'start_time', 'finish_time'
    	);
    	$sql = "select task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size, total_object_write_size,
    	average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ?
    	order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
    	$sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
    	$sqlCountParams = array($taskUUID);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$count = $this->dbSelect($sqlCount, $sqlCountParams);
    	$records = array();
    	$utils = Xphp::instance('Utils');
    	$i = 1;
    	$records["data"] = array();
    	foreach ($data as $d){
	    	$details = json_decode($d['details'], true);
	    	$records["data"][] = array(
	    		$i++,
	    		$this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
	    		$this->getHistoryJobResultDes($d['error_code']),
	    		$utils->calSize($d['total_object_size'], true),
	    		$utils->calSize($d['total_object_transport_size'], true),
	    		$utils->calSize($d['total_object_write_size'], true),
	    		$this->parseDate($d['start_time']),
	    		$this->parseDate($d['finish_time']),
	    		array(
	    			'level'=>$this->getJobStatusShowLevel($d['error_code']),
	    			'popover' => $this->getJobStatusShowPopover($d['error_code'])
	    		),
	    		array(
	    			'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
	    			'taskType' => intval($d['task_type'])
	    		),
	    	);
    	}

    	$records["draw"] = $params['draw'];;
    	$records["recordsTotal"] = $count[0]['total'];
    	$records["recordsFiltered"] = $count[0]['total'];
    	return  json_encode($records);
    }

    /**
     * 任务详情:卷CDP详细任务历史任务
     * @param json string params
     */
    public function getVolCdpDetailsHistory($params){
        $taskUUID = $params['uuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $this->paramsCheck($taskUUID);
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'task_type', 'error_code', 'total_transport_size',
            'write_size', 'start_time', 'finish_time'
        );
        $sql = "select task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size, 
                total_object_write_size,average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time 
                from bd_history_task 
                where task_uuid = ? order by $sortArr[$sortColumn]  $sortType limit ? , ? ";

        $sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";
        $sqlParams = array($taskUUID, $start, $length);
        $sqlCountParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $utils = Xphp::instance('Utils');
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            $details = array();
            $details = json_decode($d['details'], true);
            $startTimeValue = $d['start_time'];
            if(empty($startTimeValue)){
                $startTimeStr = "--";
            }else{
                $startTimeStr = $this->parseDate($startTimeValue);
            }
            $records["data"][] = array(
                $i++,
                $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                $this->getHistoryJobResultDes($d['error_code']),
                $utils->calSize($d['total_object_transport_size'], true),
                $utils->calSize($d['total_object_write_size'], true),
                $startTimeStr,
                $this->parseDate($d['finish_time']),
                array(
                    'level'=> $this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type'])
                ),
            );
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 任务详情: 得到数据库详细任务历史任务
     * @param unknown $params
     */
    public function getDBDetailsHistory($params){
        $taskUUID = $params['uuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $this->paramsCheck($taskUUID);
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'current_mode', 'error_code', 'total_object_transport_size',
            'total_object_write_size', 'start_time', 'finish_time'
        );
        $sql = "SELECT id, task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size,
                    total_object_transport_size, total_object_write_size, average_speed,
                    UNIX_TIMESTAMP(start_time) AS start_time, UNIX_TIMESTAMP(finish_time) AS finish_time, error_detail
                FROM bd_history_task
                WHERE task_uuid = ?
                ORDER BY $sortArr[$sortColumn] $sortType, start_time DESC LIMIT ? , ? ";
        $sqlCount = "SELECT COUNT(task_type) AS total FROM bd_history_task WHERE task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
        $sqlCountParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $utils = Xphp::instance('Utils');
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d) {
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                $i++,
                $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                $this->getHistoryJobResultDes($d['error_code']),
//                 $utils->calSize($d['total_size'], true),
//                 $utils->calSpeed(intval($d['average_speed'])),
                $utils->calSize($d['total_object_transport_size'], true),
                $utils->calSize($d['total_object_write_size'], true),
                $this->parseDate($d['start_time']),
                $this->parseDate($d['finish_time']),
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type']),
                    'error_details' => $d['error_detail'],
                    'history_id' => $d['id'],
                ),
            );
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 任务详情: 得到详细任务历史任务
     * @param unknown $params
     */
    public function getFSDetailsHistory($params){
    	$taskUUID = $params['uuid'];
    	$start = $params['start'];
    	$length = $params['length'];
    	$draw = $params['draw'];
    	$this->paramsCheck($taskUUID);
    	$sortColumn = $params['sortColumn'];
    	$sortType = $params['sortType'];
    	$sortArr = array('', 'current_mode', 'error_code', 'total_object_size', 'total_object_completed_size',
    			'total_object_write_size', 'start_time', 'finish_time'
    	);
    	$sql = "select history_uuid, task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size, total_object_write_size,total_object_completed_size,
    	average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ?
    	order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
    	$sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
    	$sqlCountParams = array($taskUUID);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$count = $this->dbSelect($sqlCount, $sqlCountParams);
    	$records = array();
    	$utils = Xphp::instance('Utils');
    	$i = 1;
    	$records["data"] = array();
    	foreach ($data as $d){
	    	$details = json_decode($d['details'], true);
            $details['task_uuid'] = $taskUUID;
	    	$records["data"][] = array(
	    		$i++,
	    		$this->getHistoryTaskType($d['task_type'], $d['current_mode'],$d['submodule_type']),
    			$this->getHistoryJobResultDes($d['error_code']),
    			$utils->calSize($d['total_object_size'], true),
	    		$utils->calSize($d['total_object_completed_size'], true),
	    		$utils->calSize($d['total_object_write_size'], true),
	    		$this->parseDate($d['start_time']),
	    		$this->parseDate($d['finish_time']),
	            array(
	                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
	                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
	            ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type'])
                ),
                'history_uuid' => $d['history_uuid'],
            );
	    }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return  json_encode($records);
    }
     /**
     * 得到主机传输策略
     * @return NULL[]
     */
    private function gettransportFsinfo($strategyid){
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name from bd_transport_strategy bts left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
        $info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['compress_flag']);
        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        return $info;

    }
    /**
     * 任务详情: 得到详细任务历史任务
     * @param unknown $params
     */
    public function getNASDetailsHistory($params){
    	$taskUUID = $params['uuid'];
    	$start = $params['start'];
    	$length = $params['length'];
    	$draw = $params['draw'];
    	$this->paramsCheck($taskUUID);
    	$sortColumn = $params['sortColumn'];
    	$sortType = $params['sortType'];
    	$sortArr = array('', 'current_mode', 'error_code', 'total_object_size', 'total_object_completed_size',
    			'total_object_write_size', 'start_time', 'finish_time'
    	);
    	$sql = "select history_uuid, task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size, total_object_write_size,total_object_completed_size,
    	average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ?
    	order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
    	$sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
    	$sqlCountParams = array($taskUUID);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$count = $this->dbSelect($sqlCount, $sqlCountParams);
    	$records = array();
    	$utils = Xphp::instance('Utils');
    	$i = 1;
    	$records["data"] = array();
    	foreach ($data as $d){
	    	$details = json_decode($d['details'], true);
            $details['task_uuid'] = $taskUUID;
	    	$records["data"][] = array(
	    		$i++,
	    		$this->getHistoryTaskType($d['task_type'], $d['current_mode'],$d['submodule_type']),
    			$this->getHistoryJobResultDes($d['error_code']),
    			$utils->calSize($d['total_object_size'], true),
	    		$utils->calSize($d['total_object_completed_size'], true),
	    		$utils->calSize($d['total_object_write_size'], true),
	    		$this->parseDate($d['start_time']),
	    		$this->parseDate($d['finish_time']),
	            array(
	                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
	                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
	            ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type'])
                ),
                'history_uuid' => $d['history_uuid'],
            );
	    }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return  json_encode($records);
    }

    private function getHistorySrc($agent_ip,$agent_name,$taskUUID){
        $des = '';
        $src_module_type = $this->getNasResorceInfo($taskUUID)['src_type'];
        switch ($src_module_type) {
            case Xphp::$_config['SUBMODULE_TYPE']['FS']: // 源端为文件客户端
               return $agent_name . '(' . $agent_ip . ')';
            case Xphp::$_config['SUBMODULE_TYPE']['NAS']: // 源端为NAS设备
                return $agent_ip . '(' . $agent_name . ')';
            case Xphp::$_config['SUBMODULE_TYPE']['HADOOP']: // 源端为Hadoop集群
                return $agent_name . '(' . $agent_ip . ')';
            case Xphp::$_config['SUBMODULE_TYPE']['OBS']: // 源端为对象存储
                return $agent_name;
            default:
                break;
        }
        return $des;
    }

    /**
     * 得到虚拟机列表任务状态
     * @param int $taskType
     * @param int $currentMode
     * @param int $taskStatus
     * @return string
     */
    private function getCurrentVMListTaskType($taskType, $currentMode, $taskStatus, $submoduleType = ''){
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //如果任务不在运行状态,这个时候不知道任务时什么类型的
            return Xphp::$_config['NULLSPACE'];
        }
        return $this->getHistoryTaskType($taskType, $currentMode, $submoduleType);
    }

    /**
     * 得到历史任务的任务类型描述
     * @param int $taskType
     * @param int $currentMode //备份模式指完全差异增量这些模式
     * @return string
     */
    private function getHistoryTaskType($taskType, $currentMode, $submoduleType = ''){
        $des = '';
        if($taskType == Xphp::$_config['TASKTYPE']['BACKUP'] || $taskType == Xphp::$_config['TASKTYPE']['DB_BACKUP'] || $taskType == Xphp::$_config['TASKTYPE']['OS_BACKUP']){
            if($currentMode == 4){
                if($submoduleType == Xphp::$_config['DB_TYPE']['ORACLE'] || $submoduleType == Xphp::$_config['DB_TYPE']['DM']  ||
                    $submoduleType == Xphp::$_config['DB_TYPE']['POSTGRE'] || $submoduleType == Xphp::$_config['DB_TYPE']['ANTDB'] || $submoduleType == Xphp::$_config['DB_TYPE']['KINGBASE'] ||
                    $submoduleType == Xphp::$_config['DB_TYPE']['OPENGAUSS'] || $submoduleType == Xphp::$_config['DB_TYPE']['UXDB'] ||
                    $submoduleType == Xphp::$_config['DB_TYPE']['HIGHGO'] || $submoduleType == Xphp::$_config['DB_TYPE']['VASTBASE']){
                    //归档日志备份
                    $des = Xphp::$_pfdes['BACKUP_MODE_DES'][5];
                }else{
                    //日志备份
                    $des = Xphp::$_pfdes['BACKUP_MODE_DES'][$currentMode];
                }
            }else{
                $des = Xphp::$_pfdes['BACKUP_MODE_DES'][$currentMode];
            }
        }

        if ($taskType == Xphp::$_config['TASKTYPE']['DB_BACKUP'] || $taskType == Xphp::$_config['TASKTYPE']['OS_BACKUP']){
            $des .= Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'];
        }else{
            $des .= Xphp::$_pfdes['TASKTYPEDES'][$taskType];
        }
        return $des;
    }

    /**
     * 根据模块统一发送消息
     * @param array $params
     * @return Ambigous|boolean
     */
    private function opUnifyMsg($params, $opName){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_current_job_manager");

        $uuid = $params['uuid'];
        $module = intval($params['module']);
        $subModule = intval($params['subModule']);
        $this->paramsCheck($uuid, $module);
        $msg = json_encode(array('task_uuid'=>$uuid));
        if('BD_TASK_OP_BACKUP_DELETE' == $opName ||
            'BD_TASK_OP_RECOVERY_DELETE' == $opName ||
            'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK' == $opName ||
            'VM_PRIVATE_TASK_OP_DELETE_ORCH_TASK' == $opName ||
            'BD_TASK_OP_CDP_BACKUP_DELETE' == $opName ||
            'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK' == $opName ||
            'BD_TASK_OP_BACKUP_COPY_DELETE' == $opName ||
            'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE' == $opName ||
            'BD_TASK_OP_ARCHIVE_DELETE' == $opName ||
            'BD_TASK_OP_ARCHIVE_FETCH_DELETE' == $opName ||
            'VM_PRIVATE_TASK_OP_DELETE_SURE_BACKUP_TASK' == $opName){
                $msg = json_encode(array('task_uuid_list'=>array($uuid)));
        }
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        if($module== $moduleType['VOL_CDP']){
            $task_list = array($uuid);
            $msg = array(
                'task_uuid_set' => $task_list,
                'control_code' => $opName
            );
            $msg = json_encode($msg);
        }
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid, false);
        $operate = $this->getUnifyOpcodeDes($opName);
        switch ($module){
            case $moduleType['VM']:
                $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['FS']:
                //检查代理是否属于当前用户
//                $this->pCheckAgentPermission($uuid, $operate);
                $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            case $moduleType['NAS']:
                $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            case $moduleType['DB']:
                $mbResult = $this->mbDBMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['OS']:
                $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            case $moduleType['VDDT_SERVER']:
                $mbResult = $this->mbCDPMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['BACKUP_COPY_CLIENT']:
                $mbResult = $this->mbCopyMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['VOL_CDP']:
                $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            default:
                $mbResult = array('result'=>false, 'msg'=>'');
                break;
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 检查任务操作的任务类型参数
     * @param int $taskType
     * @return boolean
     */
    private function checkTaskType($taskType){
        $taskType = intval($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if(in_array($taskType, $taskTypeConf)){
                return true;
        }else{
            $this->writeLog("task operation tasktype error.", 4);
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_JOB_TYPE_ERROR'], 'warning'));
        }
    }

    /**
     * 备份任务启动检查
     * @param string $taskUUID  任务UUID
     * @param int $module       模块号
     * @param int $subModule    子模块号
     */
    private function backupTaskStartCheck($taskUUID, $module, $subModule){
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        switch ($module){
            case $moduleType['VM']:
                $this->vmBackupTaskStartCheck($taskUUID);
                break;
            case $moduleType['FS'] :
            case $moduleType['NAS']:
                $this->fileBackupTaskStartCheck($taskUUID);
                break;
            case $moduleType['DB']:
                $this->dbBackupTaskStartCheck($taskUUID, $subModule);
                break;
            case $moduleType['OS']:
                $this->osBackupTaskStartCheck($taskUUID);
                break;
            case $moduleType['VOL_CDP']:
                //任务启动前监测,确保不影响其他业务
                $this->volCdpBackupTaskStartCheck($taskUUID, $subModule);
                break;
            default:
                break;
        }
    }

    /**
     * 虚拟机模块启动备份任务检查
     * @param string $taskUUID
     */
    private function vmBackupTaskStartCheck($taskUUID){
        //v3.5放开限制
        return true;
        //检查当前备份任务列表中的虚拟机是否在恢复/瞬时恢复/迁移任务中存在，且任务处于未停止状态,如果是则不能启动备份任务
        /**
         * 检查恢复任务
         * 1.通过taskuuid 找到vcenteruuid
         * 2.通过vcenteruuid,taskuuid,找到不处于停止状态的恢复任务的vmuuid列表,如果没找到,返回,否则到3
         * 3.检查备份任务的uuid 是否在vmuuid列表中
         */

        $sql = "select vcenter_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $vcenteruuid = $data[0]['vcenter_uuid'];

        $sql = "select bt.task_name from bd_task bt, vm_machine_list vml, vm_backup_timepoint vbt, bd_backup_timepoint bbt    
                where bt.task_uuid = vml.task_uuid 
                and bbt.timepoint_uuid = vml.timepoint_uuid 
                and vbt.timepoint_uuid = vml.timepoint_uuid 
                and vbt.vcenter_uuid = ? 
                and bbt.task_uuid = ? 
                and bt.task_type = ? 
                and bt.task_status != ? 
                and vml.vm_uuid  in (
                    select vm_uuid from vm_machine_list where task_uuid = ?
                )";
        $taskType = Xphp::$_config['TASKTYPE']['RECOVERY'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $sqlParams = array($vcenteruuid,$taskUUID, $taskType, $taskStatus, $taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //检查如果有恢复任务 选择的虚拟机存在于 不处于停止状态的恢复任务中,程序中断返回提示信息
            $msg = Xphp::$_lang['WEB_JOB_START_BACKUP_TIPS'] . "'" . $data[0]['task_name'] . "'";
            exit($this->muOpResult(false, Xphp::$_lang['WEB_JOB_START'], $msg, warning));
        }

        //检查瞬时恢复和迁移任务
        $sql = "select bt.task_name from bd_task bt, vm_instant vi, vm_backup_timepoint vbt, bd_backup_timepoint bbt 
                where bt.task_uuid = vi.task_uuid 
                and bbt.timepoint_uuid = vi.timepoint_uuid 
                and vbt.timepoint_uuid = vi.timepoint_uuid 
                and vbt.vcenter_uuid = ? 
                and bbt.task_uuid = ? 
                and (task_type = ? or task_type = ?)
                and bt.task_status != ? 
                and vi.orig_vm_uuid in (
                    select vm_uuid from vm_machine_list where task_uuid = ? 
                )";
        $taskTypeInstant = Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'];
        $taskTypeMotion = Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $sqlParams = array($vcenteruuid,$taskUUID, $taskTypeInstant, $taskTypeMotion, $taskStatus, $taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //检查如果有瞬时恢复或迁移任务 选择的虚拟机存在于 不处于停止状态的瞬时恢复或迁移任务中,程序中断返回提示信息
            $msg = Xphp::$_lang['WEB_JOB_START_BACKUP_CHECK_INSTANT_TIPS'] . "'" . $data[0]['task_name'] . "'";
            exit($this->muOpResult(false, Xphp::$_lang['WEB_JOB_START'], $msg, warning));
        }

        return true;
    }

    /**
     * 文件模块启动备份任务检查
     * @param string $taskUUID
     */
    private function fileBackupTaskStartCheck($taskUUID){
        //TODO
        return true;
    }

    /**
     * 数据库模块启动备份任务检查
     * @param string $taskUUID
     * @param int $subModule
     */
    private function dbBackupTaskStartCheck($taskUUID, $subModule){
        //TODO
        return true;
    }
    /**
     * 主机模块启动备份任务检查
     * @param string $taskUUID
     */
    private function osBackupTaskStartCheck($taskUUID){
        //TODO
        return true;
    }
    /**
     * 卷cdp备份任务启动前检查
     * @param string $taskUUID
     * @param int $subModule
     */
    private function volCdpBackupTaskStartCheck($taskUUID,$suModule){
        //TODO 添加监测内容
        return true;
    }
    /**
     * 恢复任务启动检查
     * @param string $taskUUID
     * @param int $module
     * @param int $subModule
     */
    private function recoveryTaskStartCheck($taskUUID, $module, $subModule){
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        switch ($module){
            case $moduleType['VM']:
                $this->vmRecoveryTaskStartCheck($taskUUID, $subModule);
                break;
            case $moduleType['FS']:
            case $moduleType['NAS']:
                $this->fileRecoveryTaskStartCheck($taskUUID);
                break;
            case $moduleType['DB']:
                $this->dbRecoveryTaskStartCheck($taskUUID, $subModule);
                break;
            case $moduleType['OS']:
                break;
            default:
                break;
        }
    }

    /**
     * 虚拟机模块启动恢复任务检查
     * @param string $taskUUID
     * @param int $subModule
     */
    private function vmRecoveryTaskStartCheck($taskUUID, $subModule){
        //v3.5放开限制
        return true;
        //检查当前恢复任务的时间点中的虚拟机是否在备份任务中存在，其备份任务未处于停止状态，如果是则不能启动恢复任务
	    //(提示：恢复时间点的任务uuid==备份任务uuid, vcenter_uuid==备份任务vcenter_uuid, vm_uuid=备份任务vm_uuid)
        $sql = "select vml.vcenter_uuid, bbt.task_uuid from vm_machine_list vml, bd_backup_timepoint bbt 
                where vml.timepoint_uuid = bbt.timepoint_uuid and vml.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $vcenteruuid = $data[0]['vcenter_uuid'];    //vcenteruuid
        $taskuuid = $data[0]['task_uuid'];          //恢复任务使用的时间点的备份任务UUID

        $sql = "select bt.task_name from bd_task bt, vm_machine_list vml 
                where bt.task_uuid = vml.task_uuid
                and vml.vcenter_uuid = ? 
                and bt.task_uuid = ?
                and bt.task_type = ?
                and bt.task_status != ?
                and vml.vm_uuid  in (
                    select vm_uuid from vm_machine_list where task_uuid = ?
                )";
        $taskType = Xphp::$_config['TASKTYPE']['BACKUP'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $sqlParams = array($vcenteruuid,$taskuuid, $taskType, $taskStatus, $taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //检查如果有备份任务 选择的虚拟机存在于 不处于停止状态的备份任务中,程序中断返回提示信息
            $msg = Xphp::$_lang['WEB_JOB_START_RECOVERY_TIPS'] . "'" . $data[0]['task_name'] . "'";
            exit($this->muOpResult(false, Xphp::$_lang['WEB_JOB_START'], $msg, warning));
        }
        return true;
    }

    /**
     * 文件模块启动恢复任务检查
     * @param string $taskUUID
     */
    private function fileRecoveryTaskStartCheck($taskUUID){
        //TODO
        return true;
    }

    /**
     * 虚拟机瞬时恢复启动任务检测
     * @param unknown $taskUUID
     */
    private function vmInstantTaskStartCheck($taskUUID){
        //v3.5放开限制
        return true;
        //检查当前瞬时恢复任务的时间点中的虚拟机是否在备份任务中存在，其备份任务未处于停止状态，如果是则不能启动瞬时恢复任务
        //(提示：瞬时恢复时间点的任务uuid==备份任务uuid, vcenter_uuid==备份任务vcenter_uuid, vm_uuid=备份任务vm_uuid)
        $sql = "select vbt.vcenter_uuid, bbt.task_uuid from vm_instant vi, bd_backup_timepoint bbt, vm_backup_timepoint vbt
                where vi.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid and vi.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $vcenteruuid = $data[0]['vcenter_uuid'];    //vcenteruuid
        $taskuuid = $data[0]['task_uuid'];          //瞬时恢复任务使用的时间点的备份任务UUID

        $sql = "select bt.task_name from bd_task bt, vm_machine_list vml
                where bt.task_uuid = vml.task_uuid
                and vml.vcenter_uuid = ?
                and bt.task_uuid = ?
                and bt.task_type = ?
                and bt.task_status != ?
                and vml.vm_uuid  in (
                    select orig_vm_uuid from vm_instant where task_uuid = ?
                )";
        $taskType = Xphp::$_config['TASKTYPE']['BACKUP'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $sqlParams = array($vcenteruuid,$taskuuid, $taskType, $taskStatus, $taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //检查如果有备份任务 选择的虚拟机存在于 不处于停止状态的备份任务中,程序中断返回提示信息
            $msg = Xphp::$_lang['WEB_JOB_START_INSTANT_TIPS'] . "'" . $data[0]['task_name'] . "'";
            exit($this->muOpResult(false, Xphp::$_lang['WEB_JOB_START'], $msg, warning));
        }
        return true;
    }

    /**
     * 数据库模块启动恢复任务检查
     * @param string $taskUUID
     * @param int $subModule
     */
    private function dbRecoveryTaskStartCheck($taskUUID, $subModule){
        //TODO
        return true;
    }

    /**
     * 启动任务
     * @param unknown $params
     */
    public function startJob($params){
        $uuid = $params['uuid'];
        $status = $params['status'];
        $module = intval($params['module']);
        $subModule = intval($params['subModule']);
        $this->paramsCheck($uuid);
        $taskType = $params['taskType'];
        $startType = intval($params['startType']);

        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType || $taskTypeConf['OS_BACKUP'] == $taskType){
            //启动备份任务
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_BACKUP_START';
        }elseif($taskTypeConf['RECOVERY'] == $taskType || $taskTypeConf['OS_RECOVERY'] == $taskType){
            //启动恢复任务
            $this->recoveryTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_RECOVERY_START';
        }elseif($taskTypeConf['OS_INSTANT_RECOVERY'] == $taskType){
            $opName = 'OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY';
        }
        elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //启动瞬时恢复任务
            $this->vmInstantTaskStartCheck($uuid);
            $opName = 'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_FILE_RECOVERY'] == $taskType){
            //启动细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_START_GRAIN_RECOVERY_TASK';
        }elseif($taskTypeConf['ORCH_TASK'] == $taskType){
            //启动演练任务
            $opName = 'VM_PRIVATE_TASK_OP_START_ORCH_TASK';
        }elseif($taskTypeConf['VM_CDP_BACKUP'] == $taskType){
            //启动CDP任务
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_CDP_BACKUP_START';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY'] == $taskType || $taskTypeConf['DB_BACKUP_COPY'] == $taskType || $taskTypeConf['OS_BACKUP_COPY'] == $taskType || $taskTypeConf['NAS_BACKUP_COPY'] == $taskType){
            //启动副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_BACKUP_COPY_CONTINUE';
            }
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['DB_BACKUP_COPY_FETCH'] == $taskType ||  $taskTypeConf['OS_BACKUP_COPY_FETCH'] == $taskType ||  $taskTypeConf['NAS_BACKUP_COPY_FETCH'] == $taskType){
            //启动副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE';
            }
        }elseif($taskTypeConf['ARCHIVE'] == $taskType){
            //启动归档任务
            $opName = 'BD_TASK_OP_ARCHIVE_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_ARCHIVE_CONTINUE';
            }
        }elseif($taskTypeConf['ARCHIVE_FETCH'] == $taskType){
            //启动归档恢复任务
            $opName = 'BD_TASK_OP_ARCHIVE_FETCH_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_ARCHIVE_FETCH_CONTINUE';
            }
        }elseif($taskTypeConf['DB_CDP_BACKUP'] == $taskType){
            //启动数据库CDP实时备份任务
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            return $dbCDPHandler->startBackupJob($params);
        }elseif($taskTypeConf['DB_CDP_RECOVERY'] == $taskType){
            //启动数据库CDP数据恢复任务
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            return $dbCDPHandler->startRecoveryJob($params);
        }elseif($taskTypeConf['DB_BACKUP'] == $taskType){
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_BACKUP_START';
        }elseif(
            $taskTypeConf['DB_RECOVERY'] == $taskType ||
            $taskTypeConf['DRILL'] == $taskType
        ){
            $this->recoveryTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_RECOVERY_START';
        }elseif($taskTypeConf['FILE_CDP_BACKUP'] == $taskType){
            //启动文件CDP实时备份任务
            $fileCDPHandler = Xphp::instance('FileCDPHandler');
            return $fileCDPHandler->startBackupJob($params);

        }elseif($taskTypeConf['FILE_CDP_RECOVERY'] == $taskType){
            //启动文件CDP数据恢复任务
            $fileCDPHandler = Xphp::instance('FileCDPHandler');
            return $fileCDPHandler->startRecoveryJob($params);

        }elseif($taskTypeConf['VOL_CDP_BACKUP'] == $taskType || $taskTypeConf['VOL_CDP_REPLICATION'] == $taskType){
            //启动VolCDP实时备份任务
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_BACKUP_START';
            if($startType==$taskTypeConf['VOL_CDP_TAKEOVER']){
                $opName = 'BD_TASK_OP_TAKEOVER_START';
            }
        }elseif($taskTypeConf['VOL_CDP_RECOVERY'] == $taskType){
            //启动VolCDP实时恢复任务
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_RECOVERY_START';
        }elseif ($taskTypeConf['VOL_CDP_TAKEOVER'] ==$taskType){
            //启动VolCDP接管任务
            $opName = 'BD_TASK_OP_TAKEOVER_START';

        }elseif($taskTypeConf['SURE_BACKUP'] == $taskType){
            //启动数据验证任务
            $opName = 'VM_PRIVATE_TASK_OP_START_SURE_BACKUP_TASK';
        }elseif($taskTypeConf['VM_HUAWEI_CBR_SYNC'] == $taskType ){
            //启动华为CBR下云同步任务（任务类型暂定为49）
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_BACKUP_START';
        }
        $operate = $this->getUnifyOpcodeDes($opName);
        //检查是否有操作权限
        $this->checkOpPermission($uuid, $operate);
        $msg = array(
            'task_uuid' => $uuid,
            'backup_mode' => $startType,
            'time_strategy_id' => 0,
            'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
        );
        $msg = json_encode($msg);
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid);   //任务UUID对应的存储节点uuid

        switch ($module){
            case $moduleType['VM']:
                $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['FS']:
                //检查代理是否属于当前用户
//                $this->pCheckAgentPermission($uuid, Xphp::$_lang['WEB_JOB_START']);
                $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            case $moduleType['NAS']:
                $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            case $moduleType['DB']:
                $mbResult = $this->mbDBMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['OS']:
                $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;
            case $moduleType['VDDT_SERVER']:
                $mbResult = $this->mbCDPMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['BACKUP_COPY_CLIENT']:
                $mbResult = $this->mbCopyMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;

            case $moduleType['VOL_CDP']:
                $task_list = array($uuid);
                $msg = array(
                    'task_uuid_set' => $task_list,
                    'control_code' => $opName,
                    'backup_mode' => $startType,
                    'time_strategy_id' => 0,
                    'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
                );
                $msg = json_encode($msg);
                $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);
                break;

            default:
                $mbResult = array('result'=>false, 'msg'=>'');
                break;
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 暂停任务
     * @param unknown $params
     */
    public function pauseJob($params){
        $taskType = $params['taskType'];
        $moduleType = $params['moduletype'];
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType || $taskTypeConf['OS_BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_PAUSE';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_PAUSE';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY'] == $taskType || $taskTypeConf['DB_BACKUP_COPY'] == $taskType || $taskTypeConf['OS_BACKUP_COPY'] == $taskType){
            //暂停副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_PAUSE';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['DB_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['OS_BACKUP_COPY_FETCH'] == $taskType){
            //暂停副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE';
        }elseif($taskTypeConf['ARCHIVE'] == $taskType){
            //暂停归档任务
            $opName = 'BD_TASK_OP_ARCHIVE_PAUSE';
        }elseif($taskTypeConf['ARCHIVE_FETCH'] == $taskType){
            //暂停归档恢复任务
            $opName = 'BD_TASK_OP_ARCHIVE_FETCH_PAUSE';
        }
        return $this->opUnifyMsg($params, $opName);
    }

    /**
     * 停止任务
     * @param unknown $params
     */
    public function stopJob($params){
        $taskType = $params['taskType'];
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType || $taskTypeConf['OS_BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }elseif($taskTypeConf['RECOVERY'] == $taskType || $taskTypeConf['OS_RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['OS_INSTANT_RECOVERY'] == $taskType){
            //停止操作系统瞬时恢复
            $opName = 'OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY';
        }elseif($taskTypeConf['OS_INSTANT_RECOVERY_MOTION'] == $taskType){
            //停止迁移
            $opName = 'OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION';
        }
        elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //停止瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_FILE_RECOVERY'] == $taskType){
            //停止细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_GRAIN_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY_MOTION'] == $taskType){
            //停止迁移任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['ORCH_TASK'] == $taskType){
            //停止演练任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_ORCH_TASK';
        }elseif($taskTypeConf['VM_CDP_BACKUP'] == $taskType){
            //停止CDP任务
            $opName = 'BD_TASK_OP_CDP_BACKUP_STOP';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY'] == $taskType || $taskTypeConf['DB_BACKUP_COPY'] == $taskType || $taskTypeConf['OS_BACKUP_COPY'] == $taskType || $taskTypeConf['NAS_BACKUP_COPY'] == $taskType){
            //停止副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_STOP';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['DB_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['OS_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['NAS_BACKUP_COPY_FETCH'] == $taskType){
            //停止副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_STOP';
        }elseif($taskTypeConf['ARCHIVE'] == $taskType){
            //停止归档任务
            $opName = 'BD_TASK_OP_ARCHIVE_STOP';
        }elseif($taskTypeConf['ARCHIVE_FETCH'] == $taskType){
            //停止归档恢复任务
            $opName = 'BD_TASK_OP_ARCHIVE_FETCH_STOP';
        }elseif($taskTypeConf['DB_CDP_BACKUP'] == $taskType){
            //停止数据库CDP实时备份任务
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            return $dbCDPHandler->stopBackupJob($params);
        }elseif($taskTypeConf['DB_CDP_RECOVERY'] == $taskType){
            //停止数据库CDP数据恢复任务
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            return $dbCDPHandler->stopRecoveryJob($params);
        }elseif($taskTypeConf['DB_BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }elseif(
            $taskTypeConf['DB_RECOVERY'] == $taskType ||
            $taskTypeConf['DRILL'] == $taskType
        ){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['FILE_CDP_BACKUP'] == $taskType){
            //停止文件CDP实时备份任务
            $fileCDPHandler = Xphp::instance('FileCDPHandler');
            return $fileCDPHandler->stopBackupJob($params);
        }elseif($taskTypeConf['FILE_CDP_RECOVERY'] == $taskType){
            //停止文件CDP数据恢复任务
            $fileCDPHandler = Xphp::instance('FileCDPHandler');
            return $fileCDPHandler->stopRecoveryJob($params);

        }elseif($taskTypeConf['VOL_CDP_BACKUP'] == $taskType || $taskTypeConf['VOL_CDP_REPLICATION']  == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';

        }elseif($taskTypeConf['VOL_CDP_RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';

        }elseif($taskTypeConf['VOL_CDP_TAKEOVER'] == $taskType){
            $opName = 'BD_TASK_OP_TAKEOVER_STOP';

        }elseif($taskTypeConf['SURE_BACKUP'] == $taskType){
            //停止数据验证任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_SURE_BACKUP_TASK';
        }elseif($taskTypeConf['VM_HUAWEI_CBR_SYNC'] == $taskType){
            //停止下云同步任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }
        $operate = $this->getUnifyOpcodeDes($opName);
        $this->checkOpPermission($params['uuid'], $operate);
        return $this->opUnifyMsg($params, $opName);
    }

    /**
     * 删除任务
     * @param unknown $params
     */
    public function deleteJob($params){
        $taskType = $params['taskType'];
        $taskuuid = $params['taskuuid'];
        //检查任务是否正在运行中
        $this->checkTaskRun($taskuuid);
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType || $taskTypeConf['OS_BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
//             $this->checkCopyOrArchiveExist($params['uuid']);
        }elseif($taskTypeConf['RECOVERY'] == $taskType || $taskTypeConf['OS_RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }elseif($taskTypeConf['OS_INSTANT_RECOVERY'] == $taskType){
            //删除操作系统瞬时恢复任务
            $opName  = 'OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //删除瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_FILE_RECOVERY'] == $taskType){
            //删除细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK';
        }elseif($taskTypeConf['ORCH_TASK'] == $taskType){
            //删除演练任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_ORCH_TASK';
        }elseif($taskTypeConf['VM_CDP_BACKUP'] == $taskType){
            //删除CDP任务
            $opName = 'BD_TASK_OP_CDP_BACKUP_DELETE';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY'] == $taskType || $taskTypeConf['DB_BACKUP_COPY'] == $taskType || $taskTypeConf['OS_BACKUP_COPY'] == $taskType || $taskTypeConf['NAS_BACKUP_COPY'] == $taskType){
            //删除副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_DELETE';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['DB_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['OS_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['NAS_BACKUP_COPY_FETCH'] == $taskType){
            //删除副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE';
        }elseif($taskTypeConf['ARCHIVE'] == $taskType){
            //删除归档任务
            $opName = 'BD_TASK_OP_ARCHIVE_DELETE';
        }elseif($taskTypeConf['ARCHIVE_FETCH'] == $taskType){
            //删除归档恢复任务
            $opName = 'BD_TASK_OP_ARCHIVE_FETCH_DELETE';
        }elseif($taskTypeConf['DB_CDP_BACKUP'] == $taskType){
            //删除数据库CDP实时备份任务
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            return $dbCDPHandler->deleteBackupJob($params);
        }elseif($taskTypeConf['DB_CDP_RECOVERY'] == $taskType){
            //删除数据库CDP数据恢复任务
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            return $dbCDPHandler->deleteRecoveryJob($params);
        }else if($taskTypeConf['DB_BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        }elseif(
            $taskTypeConf['DB_RECOVERY'] == $taskType ||
            $taskTypeConf['DRILL'] == $taskType
        ){
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }elseif($taskTypeConf['FILE_CDP_BACKUP'] == $taskType){
            //删除文件CDP实时备份任务
            $fileCDPHandler = Xphp::instance('FileCDPHandler');
            return $fileCDPHandler->deleteBackupJob($params);
        }elseif($taskTypeConf['FILE_CDP_RECOVERY'] == $taskType){
            //删除文件CDP数据恢复任务
            $fileCDPHandler = Xphp::instance('FileCDPHandler');
            return $fileCDPHandler->deleteRecoveryJob($params);

        }elseif($taskTypeConf['VOL_CDP_BACKUP'] == $taskType || $taskTypeConf['VOL_CDP_REPLICATION'] == $taskType){  //卷CDP备份任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';

        }elseif($taskTypeConf['VOL_CDP_RECOVERY'] == $taskType){  //卷CDP恢复
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';

        }elseif($taskTypeConf['VOL_CDP_TAKEOVER'] == $taskType){  //卷CDP接管
            $opName = 'BD_TASK_OP_TAKEOVER_DELETE';

        }elseif($taskTypeConf['SURE_BACKUP'] == $taskType){
            //删除数据验证任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_SURE_BACKUP_TASK';
        }elseif($taskTypeConf['VM_HUAWEI_CBR_SYNC'] == $taskType){
            //删除下云同步任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        }
        $operate = $this->getUnifyOpcodeDes($opName);
        //检查是否有操作权限
        $this->checkOpPermission($taskuuid, $operate);
        return $this->opUnifyMsg($params, $opName);
    }

    /**
     * 启用时间策略
     * @param unknown $params
     */
    public function startStra($params){
        $taskType = $params['taskType'];
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
        return $this->opUnifyMsg($params, $opName);
    }

    /**
     * 启动接管
     * @param unknown $params
     */
    public function takeover($params){
        $dbCDPHandler = Xphp::instance('DbCDPHandler');
        return $dbCDPHandler->takeoverJob($params);
    }

    /**
     * 停止接管
     * @param unknown $params
     */
    public function stopTakeover($params){
        $dbCDPHandler = Xphp::instance('DbCDPHandler');
        return $dbCDPHandler->stopTakeoverJob($params);
    }

    /**
     * 获取操作名
     * @param string $opName
     */
    private function getUnifyOpcodeDes($opCode){
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opCode);
        if($operate == Xphp::$_lang['WEB_PUBLIC_OPRATION_UNKNOWN']){
            $pfOpcode = Xphp::instance('VMOpcode');
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        if($operate == Xphp::$_lang['WEB_PUBLIC_OPRATION_UNKNOWN']){
            $pfOpcode = Xphp::instance('OSOpcode');
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        return $operate;
    }

     /**
     * 得到历史任务
     * @param unknown $params
     */
    public function getHistoryJobs($params){
        // 直接走优化后的方法
        return $this->getHistoryJobsV3($params);

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
        $utils = Xphp::instance('Utils');
        $name = $utils->escapeWildcard($search['name']);
        $draw = $params['draw'];
        $accurateFlag = $params['accurateFlag'];
        $sortArr = array('', '', 'task_name', 'submodule_type', 'task_type', 'user_name',
            'total_object_size', '', '', 'total_object_write_size', 'start_time', 'finish_time', 'error_code'
        );

        $sql = "select distinct id, task_name, module_type, submodule_type, task_type, current_mode, error_code, details, total_object_size, total_object_transport_size, total_object_completed_size,
                total_object_write_size, average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, user_name, task_uuid, history_uuid from bd_history_task ";
        $sqlCount = "select count(distinct id) as total from bd_history_task ";

        //此处作用是为了添加where，代替下面判断不确定的地方加where
        $sql .= " where id != 0 ";
        $sqlCount .= " where id != 0 ";
        $sqlParams = array();
        $sqlCountParams = array();
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= " and (user_uuid = ? or (user_uuid = '' and user_name = ?)) ";
            $sqlCount .= " and (user_uuid = ? or (user_uuid = '' and user_name = ?)) ";

            $sqlParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
            $sqlCountParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
        }

        if($this->checkEmpty($name) && !$accurateFlag){
            //按任务名或虚拟机名搜索
            $searchParams = "%" . $name . "%";
            $sql .= " and task_name like ? ";
            $sqlCount .= " and task_name like ? ";
            $sqlParams = array_merge($sqlParams, array($searchParams));
            $sqlCountParams = array_merge($sqlCountParams, array($searchParams));
        }
        //精确搜索
        $vmnameFlag = false;
        $vmname = '';
        if($accurateFlag){
            $search = $params['search'];
            $taskName = $search['taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $hypervisor = intval($search['hypervisor']);
            $dbType = intval($search['dbtype']);
            $taskType = intval($search['taskType']);
            $moduleType = intval($search['moduleType']);
            $errorCode = $search['errorCode'];
            $vmname = $search['vmname'];
            $timeType = intval($search['timeType']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['nodeuuid'];
            if(!empty($nodeuuid)){
                //如果按节点查询历史任务
                return $this->getHistoryJobsV2($params);
            }

            if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
                //虚拟化类型
                $submoduleType = $hypervisor;
            }else if($moduleType == Xphp::$_config['MODULE_TYPE']['DB']){
                //数据库类型
                $submoduleType = $dbType;
            }

            //默认备份恢复包含vm/fs/db/os/nas
            $tasktypeList = array($taskType);
            if(empty($moduleType)){
                if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
                    $tasktypeList = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['TASKTYPE']['DB_BACKUP'], Xphp::$_config['TASKTYPE']['OS_BACKUP']);
                }else if($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']){
                    $tasktypeList = array(Xphp::$_config['TASKTYPE']['RECOVERY'], Xphp::$_config['TASKTYPE']['DB_RECOVERY'], Xphp::$_config['TASKTYPE']['OS_RECOVERY']);
                }
            }

            //子模块类型
            if(!empty($submoduleType)){
                $sql .= " and submodule_type = ? ";
                $sqlCount .= " and submodule_type = ? ";
                $sqlParams = array_merge($sqlParams, array($submoduleType));
                $sqlCountParams = array_merge($sqlCountParams, array($submoduleType));
            }


            //模块类型
            if(!empty($moduleType)){
                $sql .= " and module_type = ? ";
                $sqlCount .= " and module_type = ? ";
                $sqlParams = array_merge($sqlParams, array($moduleType));
                $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
            }

            $tasktypeListDes = implode(',', $tasktypeList);

            //任务类型
            if(!empty($taskType)){
                $sql .= " and task_type in (".$tasktypeListDes.") ";
                $sqlCount .= " and task_type in (".$tasktypeListDes.") ";
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and task_name like ? ";
                $sqlCount .= " and task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            //虚拟机名
            $vmnameFlag = $this->checkEmpty($vmname);


            //如果选了错误，没有填错误码
            if(!$this->checkEmpty($errorCode) || $errorCode == "all"){
                //所有任务状态的
            }else if($errorCode == "error"){
                $sql .=" and error_code not in (0, 45, 47)";
                $sqlCount .=" and error_code not in (0, 45, 47)";
            }else{
                $sql .=" and error_code = ?";
                $sqlCount .=" and error_code = ?";
                $errorCode = intval($errorCode);
                $sqlParams = array_merge($sqlParams, array($errorCode));
                $sqlCountParams = array_merge($sqlCountParams, array($errorCode));
            }

            //如果填了开始时间范围查询
            if($startTime && $endTime){

                if($timeType == "1"){
                    $key = "start_time";
                }else{
                    $key = "finish_time";
                }
                $sql .= " and ".$key." between ? and ? ";
                $sqlCount .= " and ".$key." between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }
        $sql .= " order by $sortArr[$sortColumn]  $sortType limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!$count){
            $total = intval($count);
        }else{
            $total = $count[0]['total'];
        }
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';

        $records["data"] = array();
        foreach ($data as $d){
            //获取vm_machine_list
            $sqlvm = "select vml.extension_info,vml.dir_path,bht.task_type from vm_machine_list vml,bd_history_task bht where bht.task_uuid = vml.task_uuid and bht.task_uuid = ?";
            $vmdata = $this->dbSelect($sqlvm,array($d['task_uuid']));
            $details = json_decode($d['details'], true);
            //筛选虚拟机名
            if ($accurateFlag && $vmnameFlag) {
                $vmnamePassFlag = false; //是否通过筛选
                $vmNameArr = array_column($details, 'vm_name');
                foreach ($vmNameArr as $vmName) {
                    if (false !== strpos($vmName, $vmname)) {
                        $vmnamePassFlag = true;
                        break;
                    }
                }
                if (!$vmnamePassFlag) {
                    --$total;
                    continue;
                }
            }
            $transportSize = $this->getTransportSize($d['total_object_transport_size'], $d['total_object_completed_size'], intval($d['module_type']));
            $validSize = $this->getTotalValidSize($details, intval($d['module_type']), intval($d['task_type']), intval($d['total_object_size']));
            $transportSize = $utils->calSize($transportSize, true);
            $writeSize = $utils->calSize($d['total_object_write_size'], true);
            //验证任务没有大小
            if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                $validSize = Xphp::$_config['NULLSPACE'];
                $transportSize = Xphp::$_config['NULLSPACE'];
                $writeSize = Xphp::$_config['NULLSPACE'];
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['task_name'],
                $this->getVMTaskType($d['module_type'], $d['submodule_type'], $d['task_type']),
                $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                $d['user_name'],
            	$utils->calSize($d['total_object_size'], true),
                $validSize,
                $transportSize,
                $writeSize,
                $this->parseDate($d['start_time']),
                $this->parseDate($d['finish_time']),
                $this->getHistoryJobResultDes($d['error_code']),
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    //'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'module' => intval($d['module_type']),
                    'taskType' => intval($d['task_type']),
                    'history_id' => $d['id'],
                    'backup'=> $this->getVMDetailsInfo($vmdata[0], $d['task_uuid']),
                ),
            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return  json_encode($records);
    }

    /**
     * 得到历史任务带入节点查询
     * @param array $params
     */
    public function getHistoryJobsV3($params){
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
        $utils = Xphp::instance('Utils');
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $name = $search['name'];
        $name = $utils->escapeWildcard($name);
        $draw = $params['draw'];
        $accurateFlag = $params['accurateFlag'];
        $sortArr = array('', '', 'bht.task_name', 'bht.submodule_type', 'bht.task_type', 'bht.user_name',
            'bht.total_object_size', '', '', 'bht.total_object_write_size', 'bht.start_time', 'bht.finish_time', 'bht.error_code'
        );

        $sql = "select bht.id, bht.history_uuid, bht.task_name, bht.module_type, bht.submodule_type, bht.task_type, bht.current_mode, bht.error_code, bht.details, bht.total_object_size, bht.total_object_transport_size, bht.total_object_completed_size,
                bht.total_object_write_size, bht.average_speed, unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time, bht.user_name, bht.task_uuid, bht.history_uuid from bd_history_task bht ";
        $sqlCount = "select count(*) as total from bd_history_task bht ";

        //此处作用是为了添加where，代替下面判断不确定的地方加where
        $sqls = [];
        $sqlParams = array();
        $is_job_name = true;
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            array_push($sqls, 'bht.user_uuid = ?');
            array_push($sqlParams, Xphp::$_user['useruuid']);
        }

        //精确搜索
        if($accurateFlag){
            $search = $params['search'];
            $taskName = $search['taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $hypervisor = intval($search['hypervisor']);
            $dbType = intval($search['dbtype']);
            $taskType = intval($search['taskType']);
            $moduleType = intval($search['moduleType']);
            $errorCode = $search['errorCode'];
            $vmname = $search['vmname'];
            $timeType = intval($search['timeType']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['nodeuuid'];

            if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
                //虚拟化类型
                $submoduleType = $hypervisor;
            }else if($moduleType == Xphp::$_config['MODULE_TYPE']['DB']){
                //数据库类型
                $submoduleType = $dbType;
            }

            //默认备份恢复包含vm/fs/db/os/nas
            $tasktypeList = array($taskType);
            if(empty($moduleType)){
                if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
                    $tasktypeList = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['TASKTYPE']['DB_BACKUP'], Xphp::$_config['TASKTYPE']['OS_BACKUP']);
                }else if($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']){
                    $tasktypeList = array(Xphp::$_config['TASKTYPE']['RECOVERY'], Xphp::$_config['TASKTYPE']['DB_RECOVERY'], Xphp::$_config['TASKTYPE']['OS_RECOVERY']);
                }
            }

            //子模块类型
            if(!empty($submoduleType)){
                array_push($sqls, 'bht.submodule_type = ?');
                array_push($sqlParams, $submoduleType);
                $is_job_name = false;
            }


            //模块类型
            if(!empty($moduleType)){
                array_push($sqls, 'bht.module_type = ?');
                array_push($sqlParams, $moduleType);
                $is_job_name = false;
            }

            $tasktypeListDes = implode(',', $tasktypeList);

            //任务类型
            if(!empty($taskType)){
                array_push($sqls, "bht.task_type in (".$tasktypeListDes.")");
                $is_job_name = false;
            }



            //虚拟机名
            if($this->checkEmpty($vmname)){
                array_push($sqls, 'bht.details like ? ');
                array_push($sqlParams, '%'.$vmname.'%');
                $is_job_name = false;
            }

            //如果选择了节点
            if(!empty($nodeuuid)){
                $left = " left join bd_task_alarm bta on bht.history_uuid = bta.history_uuid";
                $sql .= $left;
                $sqlCount .= $left;
                array_push($sqls, "bta.node_uuid = ? and bht.history_uuid != ''");
                array_push($sqlParams, $nodeuuid);
                $is_job_name = false;
            }

            //如果选了错误，没有填错误码
            if(!$this->checkEmpty($errorCode) || $errorCode == "all"){
                //所有任务状态的
            }else if($errorCode == "error"){
                array_push($sqls, 'bht.error_code not in (0, 45, 47)');
                $is_job_name = false;
            }else{
                array_push($sqls, 'bht.error_code = ?');
                array_push($sqlParams,  intval($errorCode));
                $is_job_name = false;
            }

            //如果填了开始时间范围查询
            if($startTime && $endTime){
                $is_job_name = false;
                if($timeType == "1"){
                    $key = "bht.start_time";
                }else{
                    $key = "bht.finish_time";
                }
                array_push($sqls, $key." between ? and ? ");
                array_push($sqlParams, $startTime);
                array_push($sqlParams, $endTime);
            }
        }

        // 这里稍微处理下如果只是带了搜索task_name的情况
        $taskName = !empty($taskName) ? $taskName : (!empty($name) ? $name : '');

        //任务名
        if(!empty($taskName) && $this->checkEmpty($taskName)){
            if ($is_job_name) {
                // 因为只有name字段，所以不是预编译的查询，需要临时处理下sql注入
                // 过滤掉一些关键的sql数据
                $skipstr = "select|update|delete|insert|union|['\"]|\*|%";
                $taskName = preg_replace(array("/($skipstr)/i"), '.', $taskName);

                $sql_new = $sqls;
                foreach ($sql_new as $key=>&$val) {
                    $keys = $sqlParams[$key];
                    if (strpos($val, 'user_uuid') !== false) {
                        $keys = "'" . $keys . "'";
                    }
                    $val = str_replace('?', $keys, $val);
                }
                $sqlCounts = "SELECT count(*) AS total FROM (
                SELECT
                    a.id 
                FROM
                    ( 
                    SELECT id FROM bd_history_task bht WHERE ". implode(' and ', $sql_new)."
                    UNION ALL 
                    SELECT id FROM bd_history_task WHERE task_name LIKE '%{$taskName}%'
                    ) a 
                GROUP BY a.id HAVING COUNT( a.id )= 2 
                ) t";
            }
            array_push($sqls, 'bht.task_name like ? ');
            array_push($sqlParams, '%'.$taskName.'%');
        }

        $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));

        $sqlCount .= $sqls;
        $sql .= $sqls;

        $sqls_c = !empty($sqlCounts) ? $sqlCounts : $sqlCount;
        $where = !empty($sqlCounts) ? [] : $sqlParams;
        $count = $this->dbSelect($sqls_c, $where);

        $records = [];
        $records["data"] = [];

        if (!empty($count)) {
            $total = $count[0]['total'];
            $sql .= " order by $sortArr[$sortColumn]  $sortType limit ?, ?";
            $sqlParams = array_merge($sqlParams, array($start, $length));
            $data = $this->dbSelect($sql, $sqlParams);

            foreach ($data as $d){
                $details = json_decode($d['details'], true);
                $transportSize = $this->getTransportSize($d['total_object_transport_size'], $d['total_object_completed_size'], intval($d['module_type']));
                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                    ++$start,
                    $d['task_name'],
                    $this->getVMTaskType($d['module_type'], $d['submodule_type'], $d['task_type']),
                    $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                    $d['user_name'],
                    $utils->calSize($d['total_object_size'], true),
                    $this->getTotalValidSize($details, intval($d['module_type']), intval($d['task_type']), intval($d['total_object_size'])),
                    $utils->calSize($transportSize, true),
                    $utils->calSize($d['total_object_write_size'], true),
                    $this->parseDate($d['start_time']),
                    $this->parseDate($d['finish_time']),
                    $this->getHistoryJobResultDes($d['error_code']),
                    array(
                        'level'=>$this->getJobStatusShowLevel($d['error_code']),
                        'popover' => $this->getJobStatusShowPopover($d['error_code'])
                    ),
                    array(
                        //'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                        'module' => intval($d['module_type']),
                        'taskType' => intval($d['task_type']),
                        'history_id' => $d['id']
                    ),
                    'history_uuid' => $d['history_uuid'],
                );
            }
        }

        $records["draw"] = $draw;
        $records["recordsTotal"] = $total ?? 0;
        $records["recordsFiltered"] = $total ?? 0;

        return json_encode($records);
    }

    /**
     * get history detail
     */
    public function getHistoryDetail($params)
    {
        $id = $params['id'];
        $sql = "select bht.id, bht.task_name, bht.module_type, bht.submodule_type, bht.task_type, bht.error_code, bht.current_mode, bht.details, 
                bht.user_name, bht.task_uuid, bht.history_uuid from bd_history_task bht ";

        $data = $this->dbSelect($sql . " where id = ?", [$id]);

        $d = $data[0];

        $details = json_decode($d['details'], true);
        $info = $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d);
        return json_encode($info);
    }
    /**
     * 得到演练历史任务
     * @param unknown $params
     */
    public function getOrchHistoryJobs($params){
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
        $module = $search['module'];
        $draw = $params['draw'];
        $sortArr = array('', '', 'task_name', 'hypervisor_type', 'recovery_mode', 'user_name',
            'vm_num', 'vm_total_size', 'start_time', 'finish_time', 'error_code'
        );

        $sql = "select sum(orv.total_size) as vm_total_size, count(orv.vm_name) as vm_num, orr.report_uuid, 
                       orr.task_name, unix_timestamp(orr.start_time) start_time, unix_timestamp(orr.finish_time) finish_time, orr.error_code, orr.user_name, 
                       orv.vm_name, orv.hypervisor_type, orv.dir_path, orv.vm_config, orv.recovery_mode, orv.timepoint, 
                       orv.new_host_name, orv.new_vcenter_name, orv.total_size, orv.error_code as vm_error_code, 
                       orv.plan_name, orv.group_name, orv.child_name 
                       from orch_report orr, orch_report_vm orv 
                       where orr.report_uuid = orv.report_uuid and orr.user_name = ? 
                       group by orr.report_uuid 
                       order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select orr.report_uuid from orch_report orr, orch_report_vm orv 
                     where orr.report_uuid = orv.report_uuid and orr.user_name = ? 
                       group by orr.report_uuid";


        $sqlParams = array(Xphp::$_user['username'], $start, $length);
        $sqlCountParams = array(Xphp::$_user['username']);

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $vmDescription = include APP_PATH . 'vm/VmDescription.php';
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['report_uuid'] . '">',
                ++$start,
                $d['task_name'],
                Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor_type']],
                $vmDescription['OrchRecoveryMode'][$d['recovery_mode']],
                $d['user_name'],
                $d['vm_num'],
                $utils->calSize($d['vm_total_size'], true),
                $this->parseDate($d['start_time']),
                $this->parseDate($d['finish_time']),
                $this->getHistoryJobResultDes($d['error_code']),
                $d['report_uuid'],
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    'info' => $this->getOrchHistoryJobsVMHandler($d['report_uuid']),
                    'recoveryMode' => intval($d['recovery_mode'])
                ),
            );
        }

        $total = count($count);
        $records["draw"] = $draw;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return  json_encode($records);
    }

    /**
     * 得到演练当前历史记录
     */
    public function getOrchInstanJobHistoryList($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
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
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('report_uuid', 'recovery_mode', 'error_code', 'vm_num', 'vm_total_size', 'start_time', 'finish_time');


        $sql = "select sum(orv.total_size) as vm_total_size, count(orv.vm_name) as vm_num, orr.report_uuid,
        orr.task_name, orr.start_time, orr.finish_time, orr.error_code, orr.user_name,
        orv.vm_name, orv.hypervisor_type, orv.dir_path, orv.vm_config, orv.recovery_mode, orv.timepoint,
        orv.new_host_name, orv.new_vcenter_name, orv.total_size, orv.error_code as vm_error_code,
        orv.plan_name, orv.group_name, orv.child_name
        from orch_report orr, orch_report_vm orv
        where orr.report_uuid = orv.report_uuid and orr.user_name = ? and orr.task_uuid = ?
        group by orr.report_uuid
        order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select orr.report_uuid from orch_report orr, orch_report_vm orv
                     where orr.report_uuid = orv.report_uuid and orr.user_name = ? 
                        and orr.task_uuid = ? 
                       group by orr.report_uuid";


        $sqlParams = array(Xphp::$_user['username'], $taskuuid, $start, $length);
        $sqlCountParams = array(Xphp::$_user['username'], $taskuuid);

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $records = array();
        $records["data"] = array();
        $vmDescription = include APP_PATH . 'vm/VmDescription.php';
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                ++$start,
                $vmDescription['OrchRecoveryMode'][$d['recovery_mode']],
                $this->getHistoryJobResultDes($d['error_code']),
                $d['vm_num'],
                $utils->calSize($d['vm_total_size'], true),
                $d['start_time'],
                $d['finish_time'],
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
            );
        }
        $total = count($count);
        $records["draw"] = $draw;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return  json_encode($records);
    }

    /**
     * 得到演练报告任务的所有虚拟机信息
     * @param string $reportuuid
     */
    private function getOrchHistoryJobsVMHandler($reportuuid){
        $vmInfo = array();
        $sql = "select vm_name, hypervisor_type, dir_path, vm_config, timepoint, new_host_name, 
                       new_vcenter_name, total_size, vm_status, error_code, plan_name, group_name, child_name 
                from orch_report_vm where report_uuid = ?";
        $data = $this->dbSelect($sql, array($reportuuid));
        $utils = Xphp::instance('Utils');
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        foreach ($data as $d){
            $vmInfo[] = array(
                'vmname' => $d['vm_name'],
                'dirpath' => $d['dir_path'],
                'host' => $d['new_vcenter_name'] . ">" . $d['new_host_name'],
                'plans' => $d['plan_name'] . ">" . $d['group_name'] . ">" .$d['child_name'],
                'timepoint' => $d['timepoint'],
                'totalsize' => $utils->calSize($d['total_size']),
                'status' => $vmDes['VmTaskStatus'][intval($d['vm_status'])],
                'des' => $this->getVMErrorCodeDes($d['vm_status'], $d['error_code']),
            );
        }
        return $vmInfo;
    }

    /**
     * 删除应急恢复演练报告
     * @param unknown $params
     */
    public function deleteOrchHistoryJobs($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_report_delete");
        $reportuuids = $params['uuid'];
        $this->paramsCheck($reportuuids);
        $this->dbBeginTransaction();
        $result = true;
        foreach ($reportuuids as $uuid){
            //删除表orch_report
            $sql = "delete from orch_report where report_uuid = ?";
            $result = $result && $this->dbExec($sql, array($uuid));
            //删除表orch_report_vm
            $sql = "delete from orch_report_vm where report_uuid = ?";
            $result = $result && $this->dbExec($sql, array($uuid));
        }
        if($result){
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }
        $operate = Xphp::$_lang['WEB_DRILLS_DELETE_REPORT'];
        return $this->muOpResult($result, $operate);
    }

    /**
     * 得到各模块的显示描述
     * @param int $moduleType
     * @param int $subModuleType
     * @param int $taskType
     * @return string
     */
    private function getVMTaskType($moduleType, $subModuleType, $taskType){
//         $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $moduleTypeDes = Xphp::$_pfdes['MODULE_TYPE_DES'][$moduleType];
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
            $hypervisor = intval($subModuleType);
//             $moduleTypeDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$hypervisor] . "]";
            $moduleTypeDes = Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
        }else if($moduleType == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
            if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']
                || $taskType == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'] || $taskType == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY_FETCH'] ||
                $taskType == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'] || $taskType == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY_FETCH'] ||
                $taskType == Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY'] || $taskType == Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY_FETCH']){
                //副本
                $moduleTypeDes = Xphp::$_lang['WEB_PLATFORM_DES_COPY'];
            }else if($taskType == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $taskType == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                //归档
                $moduleTypeDes = Xphp::$_lang['UI_PLATFORM_ARCHIVE'];
            }
        }
        return $moduleTypeDes;
    }

    /**
     * 得到历史任务结果描述
     * @param int $errorCode
     */
    public function getHistoryJobResultDes($errorCode){
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
//         $error = include CONF_PATH . "error.php";
        if(in_array(Xphp::$_error['errorCode'][$errorCode], $abnormal)){
            return Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'];
        }
        if(in_array(Xphp::$_error['errorCode'][$errorCode], $discontinue)){
            return Xphp::$_lang['WEB_PLATFORM_DES_DISCONTINUE'];
        }
        return $errorCode == 0 ? Xphp::$_lang['WEB_PUBLIC_SUCCESS'] : Xphp::$_lang['WEB_PUBLIC_FAILURE'];
    }

    /**
     * 根据任务状态得到显示状态等级
     * 1 success/2 warning/3 danger/4 info
     * @param int $errorCode
     */
    public function getJobStatusShowLevel($errorCode){
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
//         $error = include CONF_PATH . "error.php";
        if(in_array(Xphp::$_error['errorCode'][$errorCode], $abnormal)){
            return 2;
        }
        if(in_array(Xphp::$_error['errorCode'][$errorCode], $discontinue)){
            return 4;
        }
        $level = 3;
        if(0 == $errorCode){
            $level = 1;
        }
        return $level;
    }

    /**
     * 根据任务状态得到显示的popover提示信息
     * @param int $errorCode
     */
    public function getJobStatusShowPopover($errorCode){
        $des = Xphp::$_error['errorCodeDes'][Xphp::$_error['errorCode'][$errorCode]];
        if(0 != $errorCode){
            $des .= "," . Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ": #". $errorCode;
        }
        return $des;
    }

    /**
     * 处理历史任务详情
     * @param int $module       模块类型
     * @param int $taskType     任务类型
     * @param array $details    详情
     * @param array $info       所有信息
     * @return array
     */
    private function historyTaskDetailsHandler($module, $taskType, $details, $info){
        if(empty($details)){
            return false;
        }
        if($module == Xphp::$_config['MODULE_TYPE']['VM']){
            //数据验证任务
            if($taskType == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                return $this->historyTaskDetailsVerify($details, $info);
            }
            if($taskType == Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']){
                return $this->historyTaskDetailsSync($details,$info);

            }
            return $this->historyTaskDetailsHandlerVM($details);
        }elseif($module == Xphp::$_config['MODULE_TYPE']['FS'] || $module == Xphp::$_config['MODULE_TYPE']['NAS']){//这里文件和nas一样
            return $this->historyTaskDetailsHandlerFile($details,$taskType,$info);
        }else if($module == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
            return $this->historyTaskDetailsHandlerCopy($details, $taskType);
        }elseif($module == Xphp::$_config['MODULE_TYPE']['DB']){
            return $this->historyTaskDetailsHandlerDB($details, $taskType, $info);
        }elseif($module == Xphp::$_config['MODULE_TYPE']['OS']){
            if($taskType ==   Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY_MOTION']){
                //如果是迁移
                return $this->historyTaskDetailsMotion($details, $taskType, $info);
            }
            return $this->historyTaskDetailsHandlerOS($details, $taskType, $info);
        }elseif($module == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            return $this->historyTaskDetailsHandlerVolCdp($details, $taskType, $info);
        }else{
            //TODO other module
            return false;
        }
    }


    /**
     *  处理文件历史任务详情
     * @param string $details
     * @return array
     */
    private function historyTaskDetailsHandlerFile($details,$taskType,$info){
        foreach($details['agent_info_list'] as $key=>$a) {
            $details['agent_info_list'][$key]['description'] = $this->getFileErrorCodeDes($details['agent_info_list'][$key]['description']);
            //得到备份模式
            $details['agent_info_list'][$key]['backup_mode'] = $this->getHistoryTaskType($taskType, $a['backup_mode']);
            $details['backup_mode'] = Xphp::$_pfdes['BACKUP_MODE_DES'][$details['backup_mode']] . Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
            $details['src_agent'] = $this->getHistorySrc($a['src_agent_ip'],$a['src_agent_name'],$details['task_uuid']);
        }
        return $details;
    }

    /**
     * 获取数据验证历史任务虚拟机详情信息
     * @param unknown $details
     * @param unknown $info
     * @return array
     */
    private function historyTaskDetailsVerify($details, $info){
        $historyuuid = $info['history_uuid'];
        $sqlVm = "select vm_name, ping_test, heartbeat_test, unix_timestamp(start_time) start_time, unix_timestamp(end_time) end_time, screen_shot
                  from sr_sure_backup_report where history_uuid = ?";
        $dataVm = $this->dbSelect($sqlVm, array($historyuuid));
        //获取报告中的虚拟机
        $vmInfo = array();
        foreach ($details as $d){
            $info = array(
                'vm_name' => $d['vm_name'],//虚拟机名
                'status' => Xphp::$_vmdes['VERIFY_VM_STATUS'][intval($d['task_status'])],//状态
                'error_code' => $this->getVMErrorCodeDes($d['task_status'], $d['error_code']),//错误信息描述
                'start_time' => Xphp::$_config['TIMESPACE'], //开始时间
                'end_time' => Xphp::$_config['TIMESPACE'],//结束时间
                'ping_status' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][0],//ping
                'heartbeat_status' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][0],//心跳测试
                'screen_status' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][0],//截屏
            );
            foreach ($dataVm as $vm){
                //获取虚拟机报告信息
                if($d['new_name'] == $vm['vm_name']){
                    //ping
                    $info['ping_status'] =  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($vm['ping_test'])];    //默认等待
                    //心跳测试
                    $info['heartbeat_status'] =  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($vm['heartbeat_test'])];    //默认等待
                    //开始时间
                    $info['start_time'] = $this->parseDate($vm['start_time']);
                    //结束时间
                    $info['end_time'] = $this->parseDate($vm['end_time']);
                }
            }
            $vmInfo[] = $info;
        }

        return $vmInfo;
    }
    /**
     * 获取下云同步历史任务虚拟机详情信息
     * @param unknown $details
     * @param unknown $info
     * @return array
     */
    private function historyTaskDetailsSync($details,$info){
        $infoList = array();
        $vmsdetail  = $details['vms_details'];
        foreach ($vmsdetail as $d){
            $extensioInfo = json_decode($d['cbr_detail'],true);
            $detailinfo  = array(
                "path" => $d['dir_path'],
                "count"=>intval($extensioInfo['count']),
                "backups" => $extensioInfo['backups']
            );
            $infoList[] =  $detailinfo;
        }
        return $infoList;
    }

    /**
     *  处理虚拟机历史任务详情
     * @param string $details
     * @return array
     */
    private function historyTaskDetailsHandlerVM($details){
        $utils = Xphp::instance('Utils');
        $i = 0;
        $vms_details = $details;
        $changed = ['add_vms' => [], 'remove_vms' => []];
        if (isset($details['vms_details'])) {
            $vms_details = $details['vms_details'];
        }
        if (isset($details['vms_changed_status'])) {
            $changed = $details['vms_changed_status'];
        }
        foreach ($vms_details as $d){
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $vms_details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
            $vms_details[$i]['vm_size'] = $utils->calSize($d['vm_size'], true);
            $vms_details[$i]['vm_valid_size'] = $utils->calSize($d['vm_valid_size'], true);
            $vms_details[$i]['real_size'] = $utils->calSize($d['write_size'], true);
            $vms_details[$i]['task_status'] = Xphp::$_vmdes['VmTaskStatus'][intval($d['task_status'])];
            $vms_details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            $vms_details[$i]['backup_mode'] = $this->getHistoryTaskType(Xphp::$_config['TASKTYPE']['BACKUP'], $d['mode']);
            $vms_details[$i]['transfer_speed'] = $utils->calSpeed(intval($d['transfer_speed']));
            if(!$d['version'] || empty($d['version'])){
                $vms_details[$i]['version'] = Xphp::$_config['NULLSPACE'];
            }
            $i++;
        }
        return ['vms_details' => $vms_details, 'vms_changed_status' => $changed];
    }
    /**
     * 处理卷CDP历史任务详情
     * @param string $details
     * @return array
     */
    private function historyTaskDetailsHandlerVolCdp($details,$taskType,$info){
        $utils = Xphp::instance('Utils');
        $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
        $pfDes = Xphp::$_pfdes;
        $i = 0;
        foreach ($details as $d){
            $details[$i]['task_status'] = $pfDes['TASKSTATUSDES'][intval($d['task_status'])];
            $details[$i]['agent_name'] = $volCdpHandler->agentStr($d['agent_name'],$d['hostname'],$d['agent_ip']);
            $details[$i]['agent_ip'] = $d['agent_ip'];
            $details[$i]['vol_display_name'] = $d['vol_display_name'];

            $details[$i]['vol_size'] = $utils->calSize($d['vol_size'], true);
            $details[$i]['vol_complete_size'] = $utils->calSize($d['vol_complete_size'], true);
            $details[$i]['real_complete_size'] = $utils->calSize($d['real_complete_size'], true);
            $details[$i]['real_size'] = $utils->calSize($d['real_size'], true);
            $details[$i]['draw_speed'] = $utils->calSize($d['draw_speed'], true)."/s";

            //双机镜像部分
            $details[$i]['standby_host_name'] =  $volCdpHandler->agentStr($d['standby_agent_name'],$d['standby_hostname'],$d['standby_host_ip']);
            $details[$i]['standby_host_ip'] = $d['standby_host_ip'];
            $details[$i]['standby_map_mount_point'] = $d['standby_map_mount_point'];
            //接管部分
            $details[$i]['takeover_host_name'] = $volCdpHandler->agentStr($d['takeover_agent_name'],$d['takeover_hostname'],$d['takeover_host_ip']);
            $details[$i]['takeover_host_ip'] = $d['takeover_host_ip'];
            $details[$i]['takeover_target_mount_point'] = $d['takeover_target_mount_point'];
            $details[$i]['takeover_time_point'] = $d['takeover_time_point'];
            //恢复任务部分
            $details[$i]['recovery_host_name'] = $volCdpHandler->agentStr($d['recovery_agent_name'],$d['recovery_hostname'],$d['recovery_host_ip']);
            $details[$i]['recovery_host_ip'] = $d['recovery_host_ip'];
            $details[$i]['recovery_target_mount_point'] = $d['recovery_target_mount_point'];
            $details[$i]['recovery_time_point'] = $d['recovery_time_point'];


            $details[$i]['description'] = $this->getJobStatusShowPopover($info['error_code']);
            $i++;
        }
        return $details;
    }

    /**
     * 处理数据库历史任务详情
     * @param array $details
     * @return array
     */
    private function historyTaskDetailsHandlerDB($details, $tasktype, $info)
    {
        $utils = Xphp::instance('Utils');
        $i = 0;
        $allDbType = Xphp::$_config['DB_TYPE'];
        $allTaskType = Xphp::$_config['TASKTYPE'];
        if (isset($details['resource_limiting_node_config'])) {
            return $details;
        }
        foreach ($details as $d) {
            $dbType = (int) $d['db_type'];
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
            $details[$i]['db_size'] = $utils->calSize($d['total_size'], true);
            $details[$i]['real_size'] = $utils->calSize($d['write_size'], true);
            $details[$i]['task_status'] = Xphp::$_vmdes['VmTaskStatus'][intval($d['task_status'])];
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            $details[$i]['backup_mode'] = $this->getHistoryTaskType($tasktype, $d['mode'], $info['submodule_type']);
            $details[$i]['transfer_speed'] = $utils->calSpeed(intval($d['transfer_speed']));
            $details[$i]['average_speed'] = $utils->calSpeed(intval($info['average_speed']));
            $details[$i]['timepoint_des'] = $d['timepoint'] . "(" . $this->getTimepointTypeDes($d['mode'], $dbType) . ")";

            //恢复方式
            $details[$i]['recovery_mode_des'] = Xphp::$_dbdes['DB_RECOVERY_LEVEL_DES'][intval($d['recovery_level'])];
            $details[$i]['recovery_mode'] = intval($d['recovery_level']);
            $details[$i]['timepoint_recovery_type'] = intval($d['recovery_type']);
            if ($dbType == $allDbType['CACHE'] || $dbType == $allDbType['IRIS']) {  // Cache/IRIS与其他逻辑不一致
                $details[$i]['recovery_mode_des'] =
                    Xphp::$_dbdes['DB_RECOVERY_LEVEL_DES'][intval($d['recovery_mode'])];
                $details[$i]['recovery_mode'] = intval($d['recovery_mode']);
            }
            if(empty($d['new_db_name'])){
                $details[$i]['new_db_name'] = $d['db_name'];
            }

            if (
                $allTaskType['DB_RECOVERY'] == $tasktype ||
                $allTaskType['DRILL'] == $tasktype
            ) {  // 数据库恢复
                if ($dbType == $allDbType['ORACLE']) {  // Oracle有pfile
                    $details[$i]['pfile_content'] = $d['detail'];
                    $details[$i]['modify_pfile_flag'] = $utils->parseFlagToBool($d['modify_pfile_flag']);
                } elseif ($allDbType['MONGODB'] == $dbType) {
                    $details[$i]['max_object_transport_parallel_nums'] = (int) $d['max_object_transport_parallel_nums'];
                }
            }
            $details[$i]['instance_name'] = $d['instance_name'];
            $i++;
        }
        return $details;
    }

    /**
     * 处理主机历史任务详情
     * @param unknown $details
     * @return unknown
     */
    private function historyTaskDetailsHandlerOS($details, $tasktype, $info){
        $utils = Xphp::instance('Utils');
        //         $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $osHandler = Xphp::instance('OsHandler');
        $agentList = $osHandler->getAllAgentList();

        $i = 0;
        //如果是其他,直接获取模块描述
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($details as $d){
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            //得到应该展示的名称
            $details[$i]['display_name'] = $osHandler->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']);
            //得到传输大小
            $details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
            //得到总大小
            $details[$i]['os_size'] = $utils->calSize($d['os_size'], true);
            //得到写入大小
            $details[$i]['write_size'] = $utils->calSize($d['write_size'], true);
            //得到任务状态
            $details[$i]['task_status'] = $ptDes['TASKSTATUSDES'][intval($d['task_status'])];
            //得到错误码
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            //得到备份模式
            $details[$i]['backup_mode'] = $this->getHistoryTaskType($tasktype, $d['mode']);
            //得到平均速度
            $details[$i]['average_speed'] = $utils->calSpeed(intval($info['average_speed']));
            //得到传输速度
            $details[$i]['transfer_speed'] = $utils->calSpeed(intval($d['transfer_speed']));
            //得到有效数据大小
            $details[$i]['os_valid_size'] = $utils->calSize(intval($d['os_valid_size']),true);
            //得到错误描述
            $details[$i]['error_code_des'] = $this->getErrorCodeDes($d['task_status'], $d['error_code']);
            //恢复方式
            $details[$i]['recovery_level_des'] = Xphp::$_dbdes['DB_RECOVERY_LEVEL_DES'][intval($d['recovery_level'])];
            if(empty($d['new_os_name'])){
                $details[$i]['new_os_name'] = $d['os_name'];
            }
            $i++;
        }
        return $details;
    }


    /**
     * 处理主机迁移历史任务详情
     * @param unknown $details
     * @return unknown
     */
    private function historyTaskDetailsMotion($details, $tasktype, $info){
        $utils = Xphp::instance('Utils');
        //         $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $osHandler = Xphp::instance('OsHandler');
        $agentList = $osHandler->getAllAgentList();

        $i = 0;
        //如果是其他,直接获取模块描述
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        //这里获取时间点 源主机 目标主机
        //获取时间点
        $sql =  "select ol.timepoint_uuid, omh.migration_map from os_list ol, os_migration_history omh where omh.task_uuid  = ol.task_uuid and ol.task_uuid  = ?";
        $data =  $this->dbSelect($sql,array($info['task_uuid']));
        $timepoint = $this->getTimepoint($data[0]['timepoint_uuid']);
        $migration_map  = json_decode($data[0]['migration_map']);
        $sourceidlist =  array();
        $sourcehost =  array();
        $targetidlistfinal =  array();
        $targethost =  array();
        foreach($migration_map as $sourceid=>$targetidlist){
            $sourceidlist[] = $sourceid;
            $targetidlistfinal = $targetidlist;
        }
        foreach($sourceidlist as $sourceitem){
            $sourceinfo = $this->getOSHostInfo($sourceitem);
            $sourcehost[] = $this->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")";
        }
        foreach($targetidlistfinal as $targetitem){
            $targetinfo = $this->getOSHostInfo($targetitem);
            $targethost[] = $this->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]) . "(" . $targetinfo['ip'] . ")";
        }

        $sourcehoststr = implode("<br>", $sourcehost);
        $targethoststr = implode("<br>", $targethost);
        foreach ($details as $d){
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $details[$i]['timepoint'] = $timepoint;
            //得到传输大小
            $details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
            //得到总大小
            $details[$i]['os_size'] = $utils->calSize($d['os_size'], true);
            //得到写入大小
            $details[$i]['write_size'] = $utils->calSize($d['write_size'], true);
            //得到任务状态
            $details[$i]['task_status'] = $ptDes['TASKSTATUSDES'][intval($d['task_status'])];
            //得到错误码
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            //得到错误描述
            $details[$i]['error_code_des'] = $this->getErrorCodeDes($d['task_status'], $d['error_code']);
            //迁移源主机
            $details[$i]['sourcehost'] = $sourcehoststr;
            //迁移目标主机
            $details[$i]['targethost'] = $targethoststr;

            $i++;
        }
        return $details;

    }

    /**
     * 处理副本历史任务详情
     * @param unknown $details
     * @param unknown $tasktype
     * @return boolean|number
     */
    private function historyTaskDetailsHandlerCopy($details, $tasktype){
    	if (empty($details)) return false;
    	$utils = Xphp::instance('Utils');
    	$i = 0;
    	$osHandler = Xphp::instance('OsHandler');
    	$agentList = $osHandler->getAllAgentList();
    	foreach ($details as $d){
    		$details[$i]['db_size'] = $utils->calSize($d['db_size'], true);
    		$details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
    		$details[$i]['completed_size'] = $utils->calSize($d['completed_size'], true);
    		$details[$i]['real_size'] = $utils->calSize($d['write_size'], true);
    		$details[$i]['error_code'] = $this->getCopyErrorCodeDes($d['vm_status'], $d['error_code']);
    		$details[$i]['vm_status'] = Xphp::$_vmdes['CopyTaskStatus'][intval($d['vm_status'])];
    		$details[$i]['transfer_speed'] = $utils->calSpeed(intval($d['transfer_speed']));
    		$details[$i]['timepoint_count'] = intval($d['timepoint_count']);
    		//这里是从数据库的detail字段取得的,然后二次处理一些数据
    		if($tasktype == Xphp::$_config['TASKTYPE']['BACKUP_COPY']){
    		    $details[$i]['vm_valid_size'] = $utils->calSize($d['vm_valid_size'], true);
    		}else if($tasktype == Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY'] ||  $tasktype == Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY_FETCH']){
    		    //得到有效数据大小
    		    $details[$i]['display_name'] = $osHandler->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']);
    		    $details[$i]['os_valid_size'] = $utils->calSize(intval($d['os_valid_size']),true);
    		    $details[$i]['os_status'] = Xphp::$_vmdes['CopyTaskStatus'][intval($d['os_status'])];
    		    $details[$i]['error_code'] = $this->getCopyErrorCodeDes(intval($d['os_status']), $d['error_code']);
    		}

    		$i++;
    	}
    	return $details;
    }

    /**
     * 删除历史任务
     * @param unknown $params
     */
    public function deleteHistoryTask($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_history_job_delete");
        $deleteinfo = $params['deleteinfo'];
        $taskIDArr = array();
        $nodelist = array();//保存不同的节点信息
        $nodeinfo = array();//保存节点和已经分过组的id
        $fsTaskIDArr = array();
        if(!empty($deleteinfo)) {//将文件模块的id与其他模块的id区分出来
            foreach($deleteinfo as $d) {
                $this->paramsCheck($d['id']);
                if($d['module_type']==3) {//文件
                    $fsTaskIDArr[] = array(
                        'id'=>intval($d['id']),
                        'nodeuuid'=>$d['nodeuuid']
                    );
                    if(!in_array($d['nodeuuid'], $nodelist)) {//文件需要筛选出不同的nodeuuid
                        $nodelist[] = $d['nodeuuid'];
                    }
                }else {//其他模块
                    $taskIDArr[] = intval($d['id']);
                }
            }
        }

        //文件模块得到不同nodeuuid下的不同id
        if(!empty($fsTaskIDArr) && count($nodelist)>0) {
            foreach($nodelist as $n) {
                $temparr = array();
                foreach($fsTaskIDArr as $f) {
                    if($n == $f['nodeuuid']) {
                        $msg=array(
                            // 'key'=>$key,
                            $temparr[] = $f['id'],
                        );
                        $tempnodeuuid = $f['nodeuuid'];
                    }
                }
                $nodeinfo[] = array(
                    "msg" => $temparr,
                    "nodeuuid" => $tempnodeuuid);
            }

        }
        // var_dump($nodeinfo);
        // return;
        //把按nodeuuid区分过的id发到不同的后台模块
        $opName = 'BD_TASK_OP_HISTORY_TASK_DELETE';

        // var_dump($nodeinfo);
        if(!empty($nodeinfo)) {
            foreach($nodeinfo as $node) {
                $fsmsg = array('id_list' => $node['msg']);
                $fsResult = $this->mbFSMsg($node["nodeuuid"], $opName, json_encode($fsmsg));
            }
        }
        if(!empty($taskIDArr)) {
            $msg = array('id_list' => $taskIDArr);
            $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        }
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);

        $msg = $mbResult['msg'];
        $fsmsg = $fsResult['msg'];

        //返回结果到UI
        $result = $mbResult['result'] && $fsResult['result'];
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else if($mbResult['result'] && !$fsResult['result']){//删除其他错误，删文件正确
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }else if(!$mbResult['result'] && $fsResult['result']) {//文件报错  其他正确
            return $this->muOpResult($fsResult['result'], $operate, $fsmsg, '', $fsResult['errorCode']);
        }else {//都报错
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取瞬时恢复任务的基本信息和虚拟机信息
     * @param unknown $params
     */
    public function getIntantBaseInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $hypervisor = $this->getHypervisor($taskUUID);
        if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){

        	$sql = "select bt.task_name, bt.task_status, bt.task_type, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state, vi.target_vcenter_uuid
	                from bd_task bt, vm_instant vi, vm_host vh
	                where bt.task_uuid = vi.task_uuid and
	        			  vh.vcenter_uuid = vi.target_vcenter_uuid and
	                	  bt.task_uuid = ? ";
        }else{
        	$sql = "select bt.task_name, bt.task_status, bt.task_type, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state
	                from bd_task bt, vm_instant vi, vm_host vh
	                where bt.task_uuid = vi.task_uuid and
	        			  vh.vcenter_uuid = vi.target_vcenter_uuid and
	                      vh.host_uuid = vi.target_host_uuid and
	                	  bt.task_uuid = ? ";
        }
        $data = $this->dbSelect($sql, array($taskUUID));
       	$hostIp =  $data[0]['host_ip'];
       	if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){
       		$tenantName = $this->getOpenstackTenant($data[0]['target_vcenter_uuid']);
       		$hostIp = $data[0]['host_ip'].'('.$tenantName.')';

       	}
        $info = array(
            'server_ip' => $data[0]['nfs_server_ip'],
            'host_ip' => $hostIp,
            'vm_name' => $data[0]['new_vm_name'],
            'nfs_state' => intval($data[0]['nfs_datastore_state']),
            'host_state' => intval($data[0]['instant_host_state']),
            'vm_state' => intval($data[0]['new_vm_status']),
            'task_type' => intval($data[0]['task_type']),
        );

        if(intval($data[0]['new_vm_status']) == Xphp::$_config['MACHINESTATUS']['POWEREDOFF'] &&
            intval($data[0]['nfs_datastore_state']) == Xphp::$_config['VMDATASTORESTATUS']['CONNECT']){
            //虚拟机关机状态,NFS连接状态
            $info['nfs_state'] = 3;
        }
        if(intval($data[0]['nfs_datastore_state']) == Xphp::$_config['VMDATASTORESTATUS']['DISCONNECT']){
            //NFS断开状态
            $info['vm_state'] = 0;
        }
        if(intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['ERROR']){
        	//任务处于错误状态
        	$info['nfs_state'] = 0;
        	$info['host_state'] = 0;
        	$info['vm_state'] = 0;
        }
        if(intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['STOPPED']){
            //任务处于停止状态
            $info['nfs_state'] = 0;
            $info['host_state'] = 0;
            $info['vm_state'] = 0;
        }

        return json_encode($info);
    }

    /**
     * 获取迁移任务的基本信息和虚拟机信息
     * @param unknown $params
     */
    public function getMotionBaseInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $hypervisor = $this->getHypervisor($taskUUID);
        if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){

        	$sql = "select bt.task_name, bt.task_status, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state, vi.target_vcenter_uuid,
                       vi.target_vcenter_uuid, vi.target_host_uuid, vi.motion_host_state
                from bd_task bt, vm_instant vi, vm_host vh
                where bt.task_uuid = vi.task_uuid and
                      vh.vcenter_uuid = vi.target_vcenter_uuid and
                	  bt.task_uuid = ? ";
        }else{
        	$sql = "select bt.task_name, bt.task_status, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state,
                       vi.target_vcenter_uuid, vi.target_host_uuid, vi.motion_host_state
                from bd_task bt, vm_instant vi, vm_host vh
                where bt.task_uuid = vi.task_uuid and
                      vh.vcenter_uuid = vi.target_vcenter_uuid and
        			  vh.host_uuid = vi.target_host_uuid and
                	  bt.task_uuid = ? ";
        }
        $InstantData = $this->dbSelect($sql, array($taskUUID));
        $sql = "select vml.vm_name, vml.task_status, vml.dir_path, vml.new_name,
                       vml.vm_uuid, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid,
                        bt.task_status as bd_task_status
                from vm_machine_list vml
                left join bd_task bt
                on bt.task_uuid = vml.task_uuid
                where vml.task_uuid = ? group by vml.vm_uuid";
        $taskData = $this->dbSelect($sql, array($taskUUID));
        $instantIp =  $InstantData[0]['host_ip'];
        if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){
        	$tenantName = $this->getOpenstackTenant($InstantData[0]['target_vcenter_uuid']);
        	$instantIp = $InstantData[0]['host_ip'].'('.$tenantName.')';

        }
        $info = array(
            'server_ip' => $InstantData[0]['nfs_server_ip'],                                //备份中心IP
            'instant_host_ip' => $instantIp,                                				//瞬时恢复宿主机IP
            'instant_vm_name' => $InstantData[0]['new_vm_name'],                            //瞬时恢复虚拟机名
            'nfs_state' => intval($InstantData[0]['nfs_datastore_state']),                  //NFS状态
            'instant_host_state' => intval($InstantData[0]['instant_host_state']),          //瞬时恢复宿主机状态
            'instant_vm_state' => intval($InstantData[0]['new_vm_status']),                 //瞬时恢复虚拟机状态
            'datastore_state' => $this->getDataStoreStatus($taskData[0]['bd_task_status']), //迁移数据状态
            'motion_vm_state' => 0,                                                         //暂时没有迁移虚拟机状态
            'motion_vm_name' => $taskData[0]['new_name'],                                   //迁移虚拟机名称
        );

        if($InstantData[0]['target_vcenter_uuid'] == $taskData[0]['vcenter_uuid'] &&
            $InstantData[0]['target_host_uuid'] == $taskData[0]['host_uuid']){
            //如果是原机恢复
            $info['flag'] = 1;  //原机迁移
        }else{
            //异机恢复
            if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){

            	$hostInfo = $this->getOpenstackDesHostInfo($taskData[0]['vcenter_uuid'], $taskData[0]['host_uuid']);
            }else{
            	$hostInfo = $this->getDesHostInfo($taskData[0]['vcenter_uuid'], $taskData[0]['host_uuid']);
            }
            $info['flag'] = 2;  //原机迁移
            $info['motion_host_ip'] = $hostInfo['hostIP'];  //迁移宿主机IP
            $info['motion_host_state'] = intval($InstantData[0]['motion_host_state']);  //迁移宿主机状态
        }

        return json_encode($info);
    }

    /**
     * 根据任务状态得到数据迁移的状态码
     * 0无状态
     * 1错误
     * 2传输
     * @param unknown $taskStatus
     */
    private function getDataStoreStatus($taskStatus){
        $status = intval($taskStatus);
        if($status == Xphp::$_config['TASKSTATUS']['RUNNING']){
            return $status;
        }elseif($status == Xphp::$_config['TASKSTATUS']['WAITTING']){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 得到模块类型,有子模块的需要获取子模块类型
     * @param unknown $module   模块号
     * @param unknown $submodule    子模块号
     */
    public function getModuleSubTypeDes($module, $submodule){
        //如果是虚拟机,获取子模块号
        if($module == Xphp::$_config['MODULE_TYPE']['VM']){
            return Xphp::$_config['VMHYPERVISORDES'][$submodule];
        }
        //如果是其他,直接获取模块描述
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        return $ptDes['MODULE_TYPE_DES'][$module];
    }

    /**
     * 得到任务历史任务信息/灾备中心
     * @param unknown $params
     */
    public function getHistoryTaskInfo($params){
        $start = 0;
        $length = 20;
        $sql = "select module_type, submodule_type, task_type, task_name, unix_timestamp(finish_time) finish_time, error_code
                from bd_history_task ";
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['auditor']){
            //如果是审计员
            $sql .= " order by finish_time desc limit ? , ? ";
            $sqlParams = array($start, $length);
        }else{
            $sql .= " where user_name = ? order by finish_time desc limit ? , ? ";
            $sqlParams = array(Xphp::$_user['username'], $start, $length);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $taskInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
            $taskInfo[] = array(
                'module' => intval($d['module_type']),
                'submodule' => intval($d['submodule_type']),
                'mdes' => $this->getModuleSubTypeDes($d['module_type'], $d['submodule_type']),
                'tdes' => $ptDes['TASKTYPEDES'][$d['task_type']],
                'taskname' => $d['task_name'],
                'result' => $this->getJobStatusShowLevel($d['error_code']),
                'resultdes' => $this->getHistoryJobResultDes($d['error_code']),
                'error' => $d['error_code'],
                'timedes' => $utils->formatDate($d['finish_time']),
            );
        }

        return json_encode($taskInfo);
    }

    /**
     * 得到任务子模块号,没有为0
     * @param unknown $moduleType   模块类型
     * @param unknown $taskuuid     任务uuid
     * @return number
     */
    public function getSubmoduleType($moduleType, $taskuuid){
        $submodule = 0;
        //TODO 根据不同模块类型 ,添加不一样的子模块信息或其他信息
        if(Xphp::$_config['MODULE_TYPE']['VM'] == intval($moduleType)){
            $submodule = $this->getVMTaskHypervisor($taskuuid);
        }
        return $submodule;
    }

    private function getNextTimeDes(){

    }

    /**
     * 得到计划任务信息/灾备中心
     * @param unknown $params
     */
    public function getCurrentTaskInfo($params){
        $start = 0;
        $length = 20;
        //
        $taskStatus = "(bt.task_status, " .
                        Xphp::$_config['TASKSTATUS']['RUNNING'] . "," .
                        Xphp::$_config['TASKSTATUS']['WAITTING'] . "," .
                        Xphp::$_config['TASKSTATUS']['ERROR'] . "," .
                        Xphp::$_config['TASKSTATUS']['ABNORMAL'] . "," .
                        Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] . "," .
                        Xphp::$_config['TASKSTATUS']['STOPPED'] . "," .
                        Xphp::$_config['TASKSTATUS']['STOPPING'] . "," .
                        Xphp::$_config['TASKSTATUS']['PAUSED'] . ")";

        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, 
                		unix_timestamp(bs.next_start_time) next_start_time, bri.current_object_total_size, bri.current_object_completed_size, bri.speed, bri.total_object_size, bri.total_object_completed_size  
                from bd_task bt, bd_strategy bs, bd_running_info bri  
                where bt.task_uuid = bri.task_uuid and  
                     bt.strategy_id = bs.strategy_id  and 
                     bt.delete_flag = ? ";
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['auditor']){
            //如果是审计员
            $sql .= " order by field$taskStatus , bs.next_start_time, bt.task_name limit ? , ? ";
            $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $start, $length);
        }else{
            $sql .= " and bt.user_uuid = ? order by field$taskStatus , bs.next_start_time, bt.task_name limit ? , ? ";
            $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid'], $start, $length);
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $taskInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
        	if($d['task_type'] == Xphp::$_config['TASKTYPE']['ORCH_TASK'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_EXPORT'] ||
        			$d['module_type'] == Xphp::$_config['MODULE_TYPE']['FS']) continue;
            $submodule = $this->getSubmoduleType($d['module_type'], $d['task_uuid']);
            $taskInfo[] = array(
                'module' => intval($d['module_type']),
                'submodule' => intval($submodule),
                'mdes' => $this->getModuleSubTypeDes($d['module_type'], $submodule),
                'tdes' => $ptDes['TASKTYPEDES'][$d['task_type']],
                'tasktype' => $d['task_type'],
                'uuid' => $d['task_uuid'],
                'taskname' => $d['task_name'],
                'timedes' => $utils->formatDate($d['next_start_time']),
                'sdes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'status' => intval($d['task_status']),
                'href' => $this->getCurrentJobHref($d['module_type'], $d['task_type'], $d['task_uuid']),
                'speed' => $utils->calSpeed($d['speed']),
                'totalsize' => $utils->calSize($d['total_object_size']),
                'completedsize' => $utils->calSize($d['total_object_completed_size'])
            );
        }
        return json_encode($taskInfo);
    }

    /**
     * 根据用户类型得到灾备中心当前任务的超链接地址
     * @param unknown $module
     * @param unknown $tasktype
     * @param unknown $taskuuid
     */
    public function getCurrentJobHref($module, $tasktype, $taskuuid){
        $url = '';
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['auditor']){
            //如果是审计员
            return $url;
        }
        $url = '';
        if(Xphp::$_config['MODULE_TYPE']['VM'] == $module){
            //虚拟机
            $url = './content/vm/vm_job_details.php';
            if(Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] == $tasktype){
                //瞬时恢复
                $url = "./content/vm/vm_instant_job_details.php";
            }
            if(Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'] == $tasktype){
                //迁移
                $url = "./content/vm/vm_motion_job_details.php";
            }
            if(Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] == $tasktype){
            	//细粒度
            	$url = "./content/vm/vm_grain_job_details.php";
            }
            if(Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC'] == $tasktype){
                //CBR同步
                $url = "./content/cbr/cbr_job_details.php";
            }
        }else if(Xphp::$_config['MODULE_TYPE']['FS'] == $module){
            //文件
            $url = './content/fs/fs_job_details.php';
        }else if(Xphp::$_config['MODULE_TYPE']['NAS'] == $module){
            //nas
            $url = './content/nas/nas_job_details.php';
        }else if(Xphp::$_config['MODULE_TYPE']['DB'] == $module){
            //数据库
            $url = './content/dbprotect/db_job_details.php';
        }else if(Xphp::$_config['MODULE_TYPE']['OS'] == $module){
            //操作系统
            $url = './content/os/os_job_details.php';
        }else if(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] == $module){
        	$url = './content/copy/copy_job_details.php';
        }else if(Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'] == $tasktype){
            $url = './content/db/db_cdp_job_details.php';
        }else if(Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY'] == $tasktype){
            $url = './content/db/db_recovery_job_details.php';
        }else if(Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'] == $tasktype){
            $url = './content/fs/fs_cdp_job_details.php';
        }else if(Xphp::$_config['TASKTYPE']['FILE_CDP_RECOVERY'] == $tasktype){
            $url = './content/fs/fs_recovery_job_details.php';
        }

        $nameStr = $url . '?type=' . $tasktype . '&uuid=' . $taskuuid ;
        return $nameStr;
    }

    /**
     * 得到当前运行的所有文件备份任务列表信息
     * @param string $agentuuid 客户端代理唯一id
     * @param string taskuuid
     * @param int tasktype
     * @param int status
     * @param int speed
     * @param string nexttime
     * @param string finishtime
     * @param string progress
     * @param string details
     */
    public function getCurrentFileJobs($agentuuid){
    	$agentuuid = $_GET['agentuuid'];
    	$this->paramsCheck($agentuuid);
    	$sql = "select unix_timestamp(bs.next_start_time) next_start_time, bt.user_uuid, bt.task_uuid, task_type, bt.task_name, bt.task_status,
    			bri.speed, bri.start_time, bri.finish_time, bri.total_object_size, bri.total_object_completed_size
				from bd_task bt, bd_running_info bri, bd_strategy bs where bt.strategy_id = bs.strategy_id and
    			bt.task_uuid = bri.task_uuid and bt.agent_uuid = ? ";
    	$data = $this->dbSelect($sql,array($agentuuid));
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	foreach($data as $d){
    		$info[] = array(
    				'taskuuid' => $d['task_uuid'],
    				'tasktype' => intval($d['task_type']),
    				'taskname' => $d['task_name'],
    		        'status' => $ptDes['TASKSTATUSDES'][intval($d['task_status'])],
    				'speed' => $this->getJobSpeed(intval($d['task_status']), intval($d['speed'])),
    				'nexttime' => $this->getJobsTime($d['task_status'], $this->getNextStartTime($d['next_start_time'], $d['task_status'])),
    				'finishtime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
    		    	'progress' => $this->getTaskTotalProgress(intval($d['task_status']), intval($d['total_object_size']), intval($d['total_object_completed_size']), false, $d['task_type']),
    				'details' => Xphp::$_lang['UI_PUBLIC_DETAIL'],
    		);
    	}

    	//返回每一项文件备份任务信息的JSON数据
    	return json_encode($info);
    }

    /**
     * 根据任务状态得到任务下次开始时间显示
     * @param int $status
     * @param string $nexttime
     *
     */
    public function getJobsTime($status,$nexttime){
    	if($status == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
    			$status == Xphp::$_config['TASKSTATUS']['WAITTING']){
    		return $nexttime;
    	}else{
    		return Xphp::$_config['TIMESPACE'];
    	}
    }

    /**
     * 得到备份系统的所有导出任务
     * @param unknown $params
     */
    public function getExportJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'vet.hypervisor_type', 'bt.storage_uuid', 'bri.speed',
            'bri.total_object_completed_size', 'bt.task_status', 'bt.create_time', 'bu.user_name'
        );

        $taskName = $search['name'];
//         任务名	模块类型	任务类型	创建时间	状态	速度	创建者	操作
        $sql = "select bt.task_uuid, bt.task_name, unix_timestamp(bt.create_time) create_time, bt.node_uuid, bt.storage_uuid, 
                        bt.task_status, bt.strategy_id,
                		bri.speed, bri.speed_time, bri.total_object_size, bri.total_object_completed_size, 
                        bu.user_name, vet.hypervisor_type 
                from bd_task bt, bd_running_info bri, bd_user bu, vm_export_task vet 
                where bt.task_uuid = bri.task_uuid and 
                	 bt.user_uuid = bu.user_uuid and 
                     bt.task_uuid = vet.task_uuid and 
                     bt.delete_flag = 2 and bt.task_type = ? ";
        $sqlCount = "select count(id) as total from bd_task where task_type = ? ";

        $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        $sqlCountParams = array(Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);

        $sql .= "and bu.user_uuid = ? ";
        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount .= "and user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid'], $start, $length));
        $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));


        $data = $this->dbSelect($sql, $sqlParams);


        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        $storage = Xphp::instance('StorageHandler');
        $records["data"] = array();
        foreach ($data as $d){
            $hypervisor = intval($d['hypervisor_type']);
            $records["data"][] = array(
                $d['task_name'],
                Xphp::$_config['VMHYPERVISORDES'][$hypervisor],
                $storage->getOneBackupStorageInfo($d['storage_uuid']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->parseDate($d['create_time']),
                $this->getTaskOpCode($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                intval($d['task_status']),
                $this->getExportTaskVMTimepoints($d['task_uuid'])
            );
        }

        $utils = Xphp::instance('Utils');
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到导出任务的虚拟机信息和
     * @param string $taskuuid
     */
    private function getExportTaskVMTimepoints($taskuuid){
        $sql = "select vml.dir_path, bbt.timepoint from vm_machine_list vml, bd_backup_timepoint bbt 
                where vml.timepoint_uuid = bbt.timepoint_uuid and vml.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d){
            $info[] = array(
                'dirpath' => $d['dir_path'],
                'timepoint' => $d['timepoint']
            );
        }
        return $info;
    }

    /**
     * 启动备份数据导出任务
     * @param unknown $params
     */
    public function startExportJob($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $opName = 'BD_TASK_OP_BACKUP_EXPORT_START';
        return $this->unifyExportJobOperation($taskuuid, $opName);
    }

    /**
     * 停止备份数据导出任务
     * @param unknown $params
     */
    public function stopExportJob($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $opName = 'BD_TASK_OP_BACKUP_EXPORT_STOP';
        return $this->unifyExportJobOperation($taskuuid, $opName);
    }

    /**
     * 删除备份数据导出任务
     * @param unknown $params
     */
    public function deleteExportJob($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $opName = 'BD_TASK_OP_BACKUP_EXPORT_DELETE';
        $msg = array(
            "task_uuid_list" => array($taskuuid),
        );

        $msg = json_encode($msg);

        $sql = "select bt.node_uuid, vet.hypervisor_type from bd_task bt, vm_export_task vet 
                where bt.task_uuid = vet.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $nodeuuid = $data[0]['node_uuid'];
        $hypervisor = $data[0]['hypervisor_type'];

        //TODO,数据库查找,$nodeuuid, $hypervisor
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = $this->getUnifyOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    private function unifyExportJobOperation($taskuuid, $opName){
        $msg = array(
            "task_uuid" => $taskuuid,
        );

        $msg = json_encode($msg);

        $sql = "select bt.node_uuid, vet.hypervisor_type from bd_task bt, vm_export_task vet 
                where bt.task_uuid = vet.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $nodeuuid = $data[0]['node_uuid'];
        $hypervisor = $data[0]['hypervisor_type'];

        //TODO,数据库查找,$nodeuuid, $hypervisor
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = $this->getUnifyOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取有效数据总大小
     * @param array $detail
     * @param int $modeType
     * @param int $taskType
     * @param int $totalSize
     *
     */
    private function getTotalValidSize($detail, $modeType, $taskType, $totalSize){
    	$utils = Xphp::instance('Utils');
    	if($modeType == Xphp::$_config['MODULE_TYPE']['DB']){
    	    return Xphp::$_config['NULLSPACE'];
    	}else if($modeType == Xphp::$_config['MODULE_TYPE']['OS']){
    	    foreach($detail as $d){
    	        $validSize += intval($d['os_valid_size']);
    	    }
    	    return $utils->calSize($validSize, true);
    	}else if($modeType == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
    	    foreach($detail as $d){
    	        $validSize += intval($d['real_size']);
    	    }
    	    return $utils->calSize($validSize, true);
    	}else if($modeType != Xphp::$_config['MODULE_TYPE']['VM']){
    		return $utils->calSize($totalSize, true);
    	}else if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']){
    		return Xphp::$_config['NULLSPACE'];
        }
        //虚拟机详情信息结构不一样
        $vmDetail = $detail['vms_details'] ?? $detail;
        foreach($vmDetail as $d){
            $validSize += intval($d['vm_valid_size']);
        }
    	return $utils->calSize($validSize, true);
    }

    /**
     * 获取传输数据总大小
     * @param $detail
     * @param $moduleType
     * @param $transportSize
     * @return string
     */
    private function getTotalTransportSize($detail, $moduleType, $transportSize): string
    {
        $utils = Xphp::instance('Utils');
        if (Xphp::$_config['MODULE_TYPE']['VM'] == $moduleType) {
            $transportSize = 0;
            $vmDetail = $detail['vms_details'] ?? $detail;
            foreach ($vmDetail as $d) {
                $transportSize += intval($d['transport_size']);
            }
        }
        return $utils->calSize($transportSize, true);
    }

    /**
     * 获取写入数据总大小
     * @param $detail
     * @param $moduleType
     * @param $writeSize
     * @return string
     */
    private function getTotalWriteSize($detail, $moduleType, $writeSize): string
    {
        $utils = Xphp::instance('Utils');
        if (Xphp::$_config['MODULE_TYPE']['VM'] == $moduleType) {
            $writeSize = 0;
            $vmDetail = $detail['vms_details'] ?? $detail;
            foreach ($vmDetail as $d) {
                $writeSize += intval($d['write_size']);
            }
        }
        return $utils->calSize($writeSize, true);
    }

    /**
     * 获取虚拟机或文件传输大小
     * @param string $VMTranSize
     * @param string $FSTranSize
     * @param int $taskType
     */
    private function getTransportSize($VMTranSize, $FSTranSize,$taskType){
        if($taskType == Xphp::$_config['MODULE_TYPE']['VM'] || $taskType == Xphp::$_config['MODULE_TYPE']['OS'] || $taskType == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
    		return $VMTranSize;
    	}else if($taskType == Xphp::$_config['MODULE_TYPE']['FS'] || $taskType == Xphp::$_config['MODULE_TYPE']['NAS']){
    		return $FSTranSize;
    	}else if($taskType == Xphp::$_config['MODULE_TYPE']['DB']){
    	    return $VMTranSize;
    	}else if($taskType == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
    		return $VMTranSize;
    	}

    }

    /**
     * 获取任务对应的虚拟化类型
     * @param string $taskuuid
     */
    private function getHypervisor($taskuuid){
    	$sql = "select hypervisor_type from vm_task where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	return intval($data[0]['hypervisor_type']);
    }

    /**
     * 获取细粒度恢复任务展开详情信息
     * @param unknown $taskuuid
     */
    private function getGrainDetailInfo($taskuuid){
        $sql = "select vgi.vm_name, bbt.timepoint from vm_grain_info vgi left join bd_backup_timepoint bbt on  vgi.timepoint_uuid = bbt.timepoint_uuid
                where vgi.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = $data[0]['timepoint'];
        if(empty($timepoint)){
            $timepoint = Xphp::$_lang['WEB_JOB_TIMEPOINT_NOE_EXIST'];
        }
        $info = array(
            'vmname' => $data[0]['vm_name'],
            'timepoint' => $timepoint
        );
        return $info;
    }

    /**
     * 获取当前恢复展开详情信息
     * @param string $taskuuid
     */
    private function getDesRecoveryInfo($taskuuid){
        $sql = "select timepoint_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d){
            $timepoint[] = $this->getTimepoint($d['timepoint_uuid']);
        }
        $info = array(
            "timepoint" => $timepoint

        );
        return $info;

    }

    /**
     * 获取当前主机恢复展开详情信息
     * @param string $taskuuid
     */
    private function getDesOSRecoveryInfo($taskuuid){
        $sql = "select timepoint_uuid from os_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d){
            $timepoint[] = $this->getTimepoint($d['timepoint_uuid']);
        }
        $info = array(
            "timepoint" => $timepoint

        );
        return $info;
    }

    /**
     * 获取当前主机瞬时恢复展开详情信息
     * @param string $taskuuid
     */
    private function getOSInstantInfo($taskuuid){
         //获取 目标主机  cache_target
         $sql = "select ol.timepoint_uuid, bsr.storage_nickname,obt.agent_uuid as sourceid
         from os_list ol
         left join os_task ot on ol.task_uuid = ot.task_uuid
         left join bd_storage_resource bsr on bsr.storage_uuid = ot.cache_target
         left join os_backup_timepoint obt on ol.timepoint_uuid  = obt.timepoint_uuid
         where ol.task_uuid = ?";
         $data = $this->dbSelect($sql, array($taskuuid));
         $sourceinfo = $this->getOSHostInfo($data[0]['sourceid']);
         //如果是瞬时恢复任务 目标主机和源主机一样
         $targetinfo =  $sourceinfo;
        //获取cache存放位置
        $info = array(
            "timepoint" => $this->getTimepoint($data[0]['timepoint_uuid']),
            "sourcehost"=> $this->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")",
            "targethost"=> $this->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]). "(" . $targetinfo['ip'] . ")",
            "cachetarget"=>$data[0]['storage_nickname'] //瞬时恢复cache存放位置
        );
        return $info;
    }

     /**
     * 获取当前主机迁移展开详情信息
     * @param string $taskuuid
     */
    private function getOSMotionInfo($taskuuid){
        //获取 目标主机  cache_target
        $sql = "select ol.timepoint_uuid, ol.agent_uuid as targetid, obt.agent_uuid as sourceid
        from os_list ol
        left join os_task ot on ol.task_uuid = ot.task_uuid
        left join os_backup_timepoint obt on ol.timepoint_uuid  = obt.timepoint_uuid
        where ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $sourceinfo = $this->getOSHostInfo($data[0]['sourceid']);
        //如果是瞬时恢复任务 目标主机和源主机一样
        $targetinfo = $this->getOSHostInfo($data[0]['targetid']); //目标主机只取第一个原主机下的
       //获取cache存放位置
       $info = array(
           "timepoint" => $this->getTimepoint($data[0]['timepoint_uuid']),
           "sourcehost"=> $this->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")",
           "targethost"=> $this->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]). "(" . $targetinfo['ip'] . ")"
       );
       return $info;
   }

    /**
     * 获取操作系统迁移任务主机信息
     * @param string $taskuuid
     */
    private function getOSHostInfo($agentuuid){
        $sql = "select hostname, agent_name, ip, online_flag from bd_agent where agent_uuid  = ? ";
        $data = $this->dbSelect($sql,array($agentuuid));
        //组装信息
        $agentInfo = array(
            "hostname" => $data[0]['hostname'],
            "agent_name" => $data[0]['agent_name'],
            'ip' => $data[0]['ip'],
            "online_flag"=> $data[0]['online_flag']
        );
        return $agentInfo;
    }



    /**
     * 获取当前文件恢复展开详情信息
     * @param string $taskuuid
     */
    private function getFsDesRecoveryInfo($taskuuid){
        $sql = "select distinct bbt.timepoint from fs_path_list fpl,bd_backup_timepoint bbt where fpl.recovery_timepoint_uuid =bbt.timepoint_uuid and fpl.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d){
            $timepoint[] = $d['timepoint'];
        }
        $info = array(
            "timepoint" => $timepoint

        );
        return $info;

    }


    /**
     * 获取当前瞬时恢复展开详情信息
     * @param string $taskuuid
     */
    private function getDesInstantInfo($taskuuid){
    	$sql = "select timepoint_uuid, orig_vm_name, new_vm_name from vm_instant where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$info = array(
    		"oldname" => $data[0]['orig_vm_name'],
    		"newname" => $data[0]['new_vm_name'],
    	    "timepoint" => $this->getTimepoint($data[0]['timepoint_uuid'])

    	);
    	return $info;

    }
    /**
     * 获取当前迁移展开详情信息
     * @param string $taskuuid
     */
    private function getDesMotionInfo($taskuuid){
    	$sql = "select timepoint_uuid, vm_name, new_name from vm_machine_list where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$info = array(
    			"oldname" => $data[0]['vm_name'],
    			"newname" => $data[0]['new_name'],
    			"timepoint" => $this->getTimepoint($data[0]['timepoint_uuid'])

    	);
    	return $info;

    }

    /**
     * 获取备份时间点
     * @param string $timepointuuid
     */
    private function getTimepoint($timepointuuid){
    	$sql = "select timepoint from bd_backup_timepoint where timepoint_uuid = ?";
    	$data = $this->dbSelect($sql, array($timepointuuid));
    	if($data){
    		$timepoint = $data[0]['timepoint'];
    	}else{
    		$timepoint = Xphp::$_lang['WEB_JOB_TIMEPOINT_NOE_EXIST'];
    	}
    	return $timepoint;
    }

    /**
     * 获取有权限的用户组
     * @param string $user
     * @param int $userType
     */
    public function getUserGroups($user, $userType){
    	$userGroup = array();
    	if($userType == Xphp::$_config['USERTYPE']['manager']){
    		$sql = "select user_name from bd_user where create_user_name = ? and user_type in (1, 3)";
    		$data = $this->dbSelect($sql, array($user));
    		foreach ($data as $d){
    			$userGroup[] = $d['user_name'];
    		}
    		if(!in_array($user, $userGroup)){
    		    $userGroup[] = $user;
    		}
    	}else if($userType == Xphp::$_config['USERTYPE']['operator']){
    		$userGroup[] = $user;
    	}

    	return $userGroup;
    }

    /**
     * 得到细粒度恢复任务详情
     * @param unknown $params
     */
    public function getVMGrainJobDetails($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_status, bt.module_type, bt.task_type, vgi.vm_name, bbt.timepoint, 
                bbt.backup_mode, vt.hypervisor_type from 
                bd_task bt, vm_task vt, vm_grain_info vgi left join bd_backup_timepoint bbt on vgi.timepoint_uuid = bbt.timepoint_uuid where 
                bt.task_uuid = vgi.task_uuid and bt.task_uuid = vt.task_uuid and 
                bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $timepoint = $data[0]['timepoint'];
        if(empty($timepoint)){
            $timepoint = Xphp::$_lang['WEB_JOB_TIMEPOINT_NOE_EXIST'];
        }
        $info = array(
            'status' => intval($data[0]['task_status']),
            'module' => intval($data[0]['module_type']),
            'subModule' => intval($data[0]['hypervisor_type']),
            'taskType' => intval($data[0]['task_type']),
            'statusdes' => $ptDes['TASKSTATUSDES'][$data[0]['task_status']],
            'vmname' => $data[0]['vm_name'],
            'timepoint' => $timepoint,
            'backupmode' => intval($data[0]['backup_mode']),

        );
        return json_encode($info);
    }

    /**
     * 获取openstack租户名
     * @param string $vcenteruuid
     */
    private function getOpenstackTenant($vcenteruuid){
    	$sql = "select detail from vm_vcenter where vcenter_uuid = ?";
    	$data = $this->dbSelect($sql, array($vcenteruuid));
    	$detail = json_decode($data[0]['detail'], true);
    	return $detail['tenant_name'];
    }

    /**
     * 更改分发存储策略加密的时候密码修改
     * @param unknown $password
     * @param unknown $encrypt
     * @param unknown $password_auto
     * @author liushuai@vinchin.com
     * @return str  新的密码
     */
    private function updateStorePassword($password,$encrypt,$password_auto){
        $utils = Xphp::instance('Utils');
        $password_str = $password;
        if($encrypt && $password_auto){  //如果加密和自动生成密码都打开,使用默认密码
            $password_default = "/mnt/vm_vinfs";
            $password_str = $utils->ptPassEncrypt($password_default);
        }else if($encrypt && !$password_auto){ //如果加密打开  自动密码关闭  使用传输的密码
            //先base64解码
            $password_decode = base64_decode($password);
            //再平台加密
            $password_str = $utils->ptPassEncrypt($password_decode);
        }else{
            $password_str = '';
        }
        return $password_str;
    }

    /**
     * 插入时间策略
     * @param int $strategyID 策略ID
     * @param int $modeType
     * @param array $strategyInfo
     */
    public function insertTimeStrategy($taskuuid,$strategygroupuuid,$strategyID, $modeType, $strategyInfo){
        $utils = Xphp::instance('Utils');
        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
        $rollFlag = $utils->parseBoolToFlag($strategyInfo['rollFlag']);
        $sql = "insert bd_time_strategy ( task_uuid, strategy_group_uuid, strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array($taskuuid,$strategygroupuuid,$strategyID, $modeType, $strategyInfo['type'], $days,
            $utils->formartTime($strategyInfo['startTime']), $rollFlag, $utils->timeToSec($strategyInfo['rollInterval']),
            $utils->formartTime($strategyInfo['endTime'])
        );

        return $this->dbQuery($sql, $sqlParams);
    }

    /**
     * 获取时间策略天数的字符串表示
     * @param array $days
     */
    public function getTimeStrategyDaysStr($days){
        $str = '';
        if(empty($days)){
            return $str;
        }
        $str = implode('', $days);
        return $str;
    }

    /**
     * 组合限速策略列表
     * @param unknown $params
     */
    public function groupTaskSpeedList($speedList, $strategygroupuuid){
        $info = array();
        $utils = Xphp::instance('Utils');
        foreach ($speedList as $speed){
            $info[] = array(
                'strategy_uuid' => $speed['uuid'],
                'strategy_name' => '',
                'strategy_type' => $speed['type'],
                'start_time' => $utils->formartTime($speed['startTime']),
                'end_time' =>  $utils->formartTime($speed['endTime']),
                'days' => implode("", $speed['days']),
                'speed_limited_value' => $speed['value'],
                'extra_info' => '',
                'remark' => $speed['des'],
                'strategy_group_uuid' => $strategygroupuuid,
            );
        }
        return $info;
    }

     /**
     * 更新GFS的策略信息
     * @param unknown $info为GFS的js插件封装传出的结构体
     * @param string $task_uuid
     * @return boolean
     */
    public function updateGFSStrategy($info,$oldinfoflag,$task_uuid,$vm_list){
        //$oldinfoflag为true有做修改,$oldinfoflag为false未作修改
        if(!$oldinfoflag){
            //未做修改则直接返回
            return true;
        }
        $result = true;
        $deleteList = array(1,2,3); //未被勾选的策略集合
        if(!empty($info)){
            //修改GFS等待表,统一先删除后再添加 并且等待状态全部置成2,所以如果以前有1状态的就不管了 重新来
            //先删除所有此任务有关的等待表
            $sql_wait_delete = "delete from bd_gfs_entity_waiting_map where task_uuid = ?";
            $result_wait_delete = $this->dbExec($sql_wait_delete,array($task_uuid));
            //修改勾选的GFS策略信息
            foreach ($info as $oneGFS){
                $level1_type = intval($oneGFS['level1_type']);
                //删除完以后再添加
                foreach ($vm_list as $each_vm){
                    $sqladd = "insert into bd_gfs_entity_waiting_map(task_uuid, entity_uuid, level1_type, waiting_flag) values(?,?,?,?)";
                    $sqladdParams = array($task_uuid, $each_vm['vmuuid'], $level1_type, 2);
                    $resultadd = $this->dbExec($sqladd,$sqladdParams);
                    if(!$resultadd){
                        return false;
                    }
                }
                //---
                unset($deleteList[$level1_type-1]);
                //先检查数据库是否有 有就修改 没有就添加
                $checkSql ="select id from bd_task_gfs_retention_strategy where task_uuid = ? and level1_type = ?";
                $checkResult = $this->dbSelect($checkSql,array($task_uuid,$level1_type));
                if(!empty($checkResult)){
                    //修改bd_task_gfs_retention_strategy
                    $sql = "update bd_task_gfs_retention_strategy set level2_type = ?, retention_num = ? where task_uuid = ? and level1_type = ?";
                    $sqlParams = array($oneGFS['level2_type'],$oneGFS['retention_num'],$task_uuid,$oneGFS['level1_type']);
                    $result1 = $this->dbExec($sql,$sqlParams);
                }else{
                    $sql = "insert into bd_task_gfs_retention_strategy(task_uuid,level1_type,level2_type,retention_num) values(?,?,?,?)";
                    $sqlParams = array($task_uuid,$oneGFS['level1_type'],$oneGFS['level2_type'],$oneGFS['retention_num']);
                    $result1 = $this->dbExec($sql,$sqlParams);
                }
                if(!$result1){
                    $result = false;
                    return $result;
                }
            }
        }else{
            //如果传入的为空则清空所有GFS有关
            $deleteStrategy = "delete from bd_task_gfs_retention_strategy where task_uuid = ?";
            $deleteMap = "delete from bd_gfs_entity_waiting_map where task_uuid = ?";
            $resultStrategy = $this->dbExec($deleteStrategy,array($task_uuid));
            $resultMap = $this->dbExec($deleteMap,array($task_uuid));
            if(!$resultStrategy || !$resultMap){
                $result = false;
                return false;
            }
        }
        //开始删除未被勾选的集合
        if(!empty($deleteList)){
            $ThisList = implode(",", $deleteList);
            $deleteSql = "delete from bd_task_gfs_retention_strategy where task_uuid = ? and level1_type in(".$ThisList.") ";
            $result2 = $this->dbExec($deleteSql,array($task_uuid));
            if(!$result2){
                $result = false;
                return false;
            }
        }
        return $result;
    }

    /**
     * 获取恢复任务策略差异
     * @param string $taskuuid
     * @param array $info
     */
    private function getRecoveryDiffStrategyDes($taskuuid, $info){
        $vmHandler = Xphp::instance('Vmhandler');
        $sql = "select strategy_id, thread_num, strategy_group_uuid, task_type
        from bd_task where task_uuid = ? ";

        $data = $this->dbSelect($sql, array($taskuuid));
        $strategyDiff = "";
        $timeInfo = array();
        $timeDes = "";
        if($data[0]['strategy_id']){
            $timeInfo = $vmHandler->getTimeStrategyInfo($data['0']['strategy_id']);
            $timeData = $timeInfo['data'];
            $timeDes .= $this->getTimeStrategyDes($timeData[0], $data[0]['task_type']);

        }else{
            $timeDes .= Xphp::$_lang['UI_RECOVERY_TYPE_NOW'];
        }
        //限速策略
        $speedInfo = $vmHandler->getSpeedStrategyInfo($taskuuid);
        $speedDes = "";
        if(!empty($speedInfo)){
            foreach($speedInfo as $speed){
                $speedDes.= $speed['des']. '. '."\n";
            }
        }else{
            $speedDes.= Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'];
        }
        //高级策略
        $highDes = "";
        $highDes .= Xphp::$_lang['UI_BACKUP_THREAD_NUM'].": ".intval($data[0]['thread_num']);

        $diffStrategy = array();
        $timeDiff = array();
        $speedDiff = array();
        $highDiff = array();
        if($info['time']['check']){
            if($info['time']['des'] != $timeDes){
                $timeDiff =  array(
                    'title' => Xphp::$_lang['UI_STRATEGY_TIME'],
                    'old' => $timeDes,
                    'new' => $info['time']['des']
                );
                $strategyDiff .= Xphp::$_lang['UI_STRATEGY_TIME'] . " ";
            }
        }

        if($info['speedlimit']['check']){
            if($info['speedlimit']['des'] != $speedDes){
                $speedDiff =  array(
                    'title' => Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'],
                    'old' => $speedDes,
                    'new' => $info['speedlimit']['des']
                );
                $strategyDiff .= Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] . " ";
            }
        }

        if($info['high']['check']){
            if($info['high']['des'] != $highDes){
                $highDiff =  array(
                    'title' => Xphp::$_lang['UI_GLOBAL_STRATEGY_HIGH'],
                    'old' => $highDes,
                    'new' => $info['high']['des']
                );
                $strategyDiff .= Xphp::$_lang['UI_GLOBAL_STRATEGY_HIGH'] . " ";
            }
        }

        $diffStrategy = array(
            'time' => $timeDiff,
            'speed' => $speedDiff,
            'high' => $highDiff
        );

        $dataInfo = array(
            'strategyDes' => $strategyDiff,
            'details' => $diffStrategy
        );

        return $dataInfo;

    }

    private function getTimeStrategyDes($info, $type){
        $des = "";
        $modeDes = array('', Xphp::$_lang['UI_BACKUP_FULL'], Xphp::$_lang['UI_BACKUP_INCREMENT'], Xphp::$_lang['UI_BACKUP_DIFFERENCE'], Xphp::$_lang['UI_GLOBAL_STRATEGY_RECOVERY_STRATEGY']);
		$typeDes = array('', Xphp::$_lang['UI_STRATEGY_EVERY_DAY'], Xphp::$_lang['UI_STRATEGY_EVERY_WEEK'], Xphp::$_lang['UI_STRATEGY_EVERY_MONTH']);
        if($type == Xphp::$_config['TASKTYPE']['RECOVERY']){
            $des .= Xphp::$_lang['UI_GLOBAL_STRATEGY_RECOVERY_STRATEGY'] . " (";
        }else{
            $des .= $modeDes[$info['mode']] . " (";
        }

        $des .= $typeDes[$info['strategy_type']];
        for($i=0; $i<count($info['days']); $i++){
            if(1 == $info['strategy_type']) break;	//每天的话不读取days
            if($info['days'][$i]){
                $num = $i + 1;
                $des .= $num . ", ";
            }
        }
        if($type == Xphp::$_config['TASKTYPE']['RECOVERY']){
            $des .= $info['start_time'] . Xphp::$_lang['UI_PUBLIC_START'];
        }else{
            $des .= $info['start_time'] .Xphp::$_lang['UI_PUBLIC_START'].", ";
            if($info['roll_flag']){
                $des .= Xphp::$_lang['UI_STRATEGY_ROLL_INTERVAL'] . $info['roll_interval'] .", ";
                $des .= Xphp::$_lang['UI_STRATEGY_OVER_TIME']. $info['roll_end_time'];
            }else{
                $des .= Xphp::$_lang['UI_STRATEGY_ROLL_NO'];
            }
        }
        $des .= ")";
        return $des;
    }

    private function getSnapshotTypeDes($flag){
        $des = Xphp::$_lang['UI_BACKUP_SNAPSHOT_MODE_SERIAL'];
        if($flag == Xphp::$_config['FLAG']['SET']){
    		$des = Xphp::$_lang['UI_BACKUP_SNAPSHOT_MODE_SERIAL'];
    	}else if($flag == Xphp::$_config['FLAG']['UNSET']){
    		$des = Xphp::$_lang['UI_BACKUP_SNAPSHOT_MODE_PARALLEL'];
        }

        return $des;
    }

    private function getSwitchDes($flag){
        $switch = Xphp::$_lang['UI_PUBLIC_OFF_TWO'];
    	if($flag == Xphp::$_config['FLAG']['SET']){
    		$switch = Xphp::$_lang['UI_PUBLIC_ON_TWO'];
    	}else if($flag == Xphp::$_config['FLAG']['UNSET']){
    		$switch = Xphp::$_lang['UI_PUBLIC_OFF_TWO'];
    	}
    	return $switch;
    }

    /**
     * @param string $strategyName
     */
    private function checkStrategyName($strategyName){
        $sql = "select count(strategy_group_uuid) as total from bd_strategy_group where strategy_group_name = ? and user_uuid = ?";
        $data = $this->dbSelect($sql, array($strategyName, Xphp::$_user['useruuid']));
        if(intval($data[0]['total']) != 0){
            exit($this->muOpResult(false, Xphp::$_lang['UI_GLOBAL_STRATEGY_NAME'], Xphp::$_lang['UI_GLOBAL_STRATEGY_NAME_EXIST'], 'warning'));
        }
    }

    /**
     * 任务详情: 得到归档虚拟机列表
     * @param unknown $params
     */
    public function getArchiveDetailsVM($params){
    	//TODO
    	$start = $params['start'];
    	$length = $params['length'];
    	$draw = $params['draw'];
    	$taskUUID = $params['uuid'];
    	$sql = "select bcil.item_name as vm_name, bcil.source_timepoint_list,bcil.src_timepoint_count, bcil.write_size, bcil.total_copy_size, bcil.complete_size, bcil.transport_size, bcil.write_size, bcil.copy_status,
                       bcil.item_uuid as vm_uuid, bcil.error_code, bcil.new_timepoint_list, bcil.vcenter_uuid,
                		bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_write_size, bri.current_object_write_size, bri.current_object_completed_size,
                        bt.task_status as bd_task_status, bt.task_type
                from backup_copy_item_list bcil
                left join bd_running_info bri
                on bcil.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = bcil.task_uuid
                where bcil.task_uuid = ? order by bcil.item_id";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$records = array();
    	$i = 1;
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$records["data"] = array();
    	foreach ($data as $d){
    		//如果任务正在运行,需要过滤掉新添加的虚拟机
//     		if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
//     				$d['copy_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
//     			continue;
//     		}
    		$timepointCount = intval($d['src_timepoint_count']);
    		$records["data"][] = array(
    			$i++,
    			$d['vm_name'],
    			$this->getCopyTimepointNum($timepointCount,$d['bd_task_status']),
    			$this->getVMListSize($d['bd_task_status'], $d['total_copy_size']),
                $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['copy_status'],true),
    		    $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['copy_status'],true),
    		    $this->getVMSpeed($d['copy_status'], $d['speed'], $d['speed_time'],0,true),
    		    $this->getVMPercent($d['total_copy_size'], $d['current_object_completed_size'], $d['complete_size'], $d['bd_task_status'], $d['copy_status'],true),
    		    $this->getVMStatus($d['bd_task_status'], $d['copy_status'],true),
    		    $this->getErrorCodeDes($d['copy_status'], $d['error_code'],true),
    			$this->getVMDetailsInfo($d, $taskUUID),
        	);
    	}

    	$records["draw"] = $params['draw'];
    	return  json_encode($records);
    }

    /**
     * 得到归档任务虚拟机详情
     * @param array $d
     */
    private function getArchiveDetailsInfo($d, $taskuuid){
    	$archiveHandler = Xphp::instance('ArchiveHandler');
    	$info = array(
    		"type" => intval($d['task_type']),
    		"sPath" => $archiveHandler->getDirpath($d['vm_uuid'],$taskuuid),
    	);
    	return $info;
    }

     /**
     * 任务详情: 得到归档任务基本信息
     * @param unknown $params
     */
    public function getArchiveBasicInfo($params){
    	$taskUUID = $params['uuid'];
    	$this->paramsCheck($taskUUID);
    	$sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status,
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time,
                	   bsr.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time
                from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs, bd_storage_resource bsr 
                where bt.user_uuid = bu.user_uuid and
                      bt.task_uuid = bri.task_uuid and
                      bt.strategy_id = bs.strategy_id and
                      bt.storage_uuid = bsr.storage_uuid and
                	  bt.task_uuid = ? ";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$basicInfo = array();
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$utils = Xphp::instance('Utils');
    	if(!$data){
    		$basicInfo = array('flag' => false);
    		return json_encode($basicInfo);
    	}
    	foreach ($data as $d){
    	    $opInfo = $this->getOpInfo($d);
    		$basicInfo = array(
    				'taskName' => $d['task_name'],
    				'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
    				'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
    						$d['module_type'], $d['task_uuid']),
    				'user' => $d['user_name'],
    				'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
    				'statusValue' => intval($d['task_status']),
    				'totalSize' => $utils->calSize($d['total_object_size']),
    				'currentSize' => $utils->calSize($d['total_object_completed_size']),
    				'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
    		        'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
    		        'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
    				'createTime' => $this->parseDate($d['create_time']),
    				'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
    				'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
    				'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
    		        'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
    				'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
    		        'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
    				'transportStrategy' => $this->getCopyTransportStrategy($taskUUID, $d['strategy_id']),
    				'storageInfo' => $this->getArchiveStorageInfo($d['task_uuid'], $d['storage_uuid'], $d['node_uuid']),
    				'flag' => true,
    				'taskTypeFlag' => intval($d['task_type']),
    		        'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
    		        'subModule' => $opInfo['subModule']
    		);
    	}
    	return json_encode($basicInfo);
    }

    /**
     * 获取归档任务配置存储信息
     * @param string $taskuuid
     * @param string $storageuuid
     * @param string $nodeuuid
     */
    private function getArchiveStorageInfo($taskuuid, $storageuuid, $nodeuuid){
    	$info = array('flag' => false);
    	$info['cloudflag'] = false;
    	$info['storage'] = array();
    	$utils = Xphp::instance('Utils');
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
    	//存储信息
    	$sql = "select storage_nickname, storage_type, total_size, free_size, storage_config from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	if($data){
    	    $info['flag'] = true;
    		$storageHandler = Xphp::instance('StorageHandler');
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => $storageHandler->getStorageTypeDes($data[0]['storage_type']),
                'size' => $utils->calSize($data[0]['total_size']),
                'freesize' => $utils->calSize($data[0]['free_size']),
            );
    	}
    	return $info;
    }


    /**
     * 获取限速策略配置信息
     * @param string $taskuuid
     */
    private function getSpeedlimitDes($taskuuid){
        // 先在 bd_task_speed_limit_strategy 根据task_uuid 查询出是否有关联的 全局限速策略 strategy_uuid
        $sql = "select strategy_uuid, task_priority from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!empty($data)) {
            $task_priority = $data[0]['task_priority'];
            // 在 bd_global_speed_limit_strategy 表里查询出extra_info信息
            $sql = "select extra_info,is_global,strategy_name from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
            $data = $this->dbSelect($sql, array($data[0]['strategy_uuid']));
            if (!empty($data)) {
                $extra_info = json_decode($data[0]['extra_info'], true);
                $des = '';
                foreach($extra_info as $d){
                    // $value .= $d['des']."<br>";
                     $des .= $d['des']."\n";
                }
                $value = $extra_info[0]['des'];
                if (count($extra_info) > 1) {
                    $value .= '...';
                }
                $global = $data[0]['is_global'] ? (Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME'].':' . $data[0]['strategy_name']) : '';
                $text = '';
                if ($global) {
                    $text = $global . '<br>';
                    $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY'] . ': ';
                    switch ($task_priority) {
                        case 1:
                            $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY_PRIMARY'] . '<br>';
                            break;
                        case 2:
                            $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY_HIGH'] . '<br>';
                            break;
                        default:
                            $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY_HIGHEST'] . '<br>';
                            break;
                    }
                }
                $text .= $value;
                return [
                    'task_priority' => $data[0]['is_global'] ? $task_priority : 0,
                    'value' => ($global ? $global . '<br>' : '') . $value,
                    'des' => $des,
                    'text' => $text,
                ];
            }
        }

        return [
            'task_priority' => 0,
            'value' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
            'des' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
            'text' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
        ];
    }

    /**
     * 获取完整的限速策略配置信息
     * @param string $taskuuid
     */
    private function getFullSpeedlimitDes($taskuuid){
        // 先在 bd_task_speed_limit_strategy 根据task_uuid 查询出是否有关联的 全局限速策略 strategy_uuid
        $sql = "select strategy_uuid, task_priority from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!empty($data)) {
            $task_priority = $data[0]['task_priority'];
            // 在 bd_global_speed_limit_strategy 表里查询出extra_info信息
            $sql = "select extra_info,is_global,strategy_name from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
            $data = $this->dbSelect($sql, array($data[0]['strategy_uuid']));
            if (!empty($data)) {
                $extra_info = json_decode($data[0]['extra_info'], true);
                $des = '';
                $value = '';
                foreach($extra_info as $d){
                    $value .= $d['des']."<br>";
                    $des .= $d['des']."\n";
                }
                $global = $data[0]['is_global'] ? (Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME'].':' . $data[0]['strategy_name']) : '';
                $text = '';
                if ($global) {
                    $text = $global . '<br>';
                    $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY'] . ': ';
                    switch ($task_priority) {
                        case 1:
                            $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY_PRIMARY'] . '<br>';
                            break;
                        case 2:
                            $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY_HIGH'] . '<br>';
                            break;
                        default:
                            $text .= Xphp::$_lang['UI_JOB_TASK_PRIORITY_HIGHEST'] . '<br>';
                            break;
                    }
                }
                $text .= $value;
                return [
                    'task_priority' => $data[0]['is_global'] ? $task_priority : 0,
                    'value' => ($global ? $global . '<br>' : '') . $value,
                    'des' => $des,
                    'text' => $text,
                ];
            }
        }

        return [
            'task_priority' => 0,
            'value' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
            'des' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
            'text' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
        ];
    }

    /**
     * 获取时间点对应任务名
     */
    public function getTimepointTaskname($taskuuid, $taskname){
        $sql = "select  task_name from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            $name = $data[0]['task_name'];
        }else{
            $name = $taskname;
        }

        return $name;
    }

    /**
     * 获取任务appliance信息
     * @param string $taskuuid
     */
    private function getJobAppliance($taskuuid){
        $sql = "select ba.ip, ba.agent_name from bd_agent ba, vm_task vt where vt.agent_uuid = ba.agent_uuid and vt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            $flag = true;
            $des = $data[0]['agent_name'].'('.$data[0]['ip']. ')';
        }else{
            $sql = "SELECT bap.agent_pool_nickname
                    FROM bd_agent_pool bap
                        INNER JOIN vm_task vt on bap.agent_pool_uuid = vt.agent_pool_uuid
                    WHERE vt.task_uuid = ? ";
            $data = $this->dbSelect($sql, array($taskuuid));
            if (is_array($data) && $data) {
                $flag = true;
                $des = $data[0]['agent_pool_nickname'];
            } else {
                $flag = false;
                $des = Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'];
            }
        }

        return ['flag' => $flag, 'des' => $des];
    }

    /**
     * 选择虚拟机启动备份任务
     * @param unknown $params
     * @return string
     */
    public function startVMJob($params){
        $mode = $params['mode'];
        $subModule = intval($params['hypervisor']);
        $taskuuid = $params['taskuuid'];
        $this->checkTaskRun($taskuuid);
        $uuidList = $params['vmuuids'];
        $vcenteruuidList = $params['vcenteruuid'];
        $vmuuids = array();
        $vcenteruuids = array();
        foreach ($vcenteruuidList as $vcenteruuid){
            $vcenteruuids[] = array(
                'vcenter_uuid' => $vcenteruuid
            );
        }
        foreach ($uuidList as $uuid){
            $vmuuids[] = array(
                "vm_uuid" => $uuid
            );
        }
        $opName = 'BD_TASK_OP_BACKUP_START';
        $msg = array(
            'task_uuid' => $taskuuid,
            'backup_mode' => $mode,
            'time_strategy_id' => 0,
            'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
            "vm_uuid_list" => $vmuuids,
            "vcenter_uuid_list" => $vcenteruuids
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
        $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 检查任务是否在运行状态
     * @param string $taskuuid
     */
    private function checkTaskRun($taskuuid){
        $sql = "select task_status from bd_task where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        if($data[0]['task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_BACKUP_ALREADY_RUNNING'], Xphp::$_lang['WEB_VM_BACKUP_ALREADY_RUNNING_TIPS'], 'warning'));
        }
    }

    /**
     * 删除任务中选择的虚拟机
     * @param unknown $params
     */
    public function deleteSelectVms($params){
        $taskuuid = $params['taskuuid'];
        $vmuuids = $params['vmuuids'];
        $this->checkTaskStatus($taskuuid, $vmuuids);  //检查任务是否处于停止状态,任务是否只有一台虚拟机

        $vmuuidStr = implode("','", $vmuuids);
        $sql = "delete from vm_machine_list where task_uuid = ? and vm_uuid in ('$vmuuidStr')";
        $result = $this->dbExec($sql, array($taskuuid));

        return $this->muOpResult($result, Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS']);

    }

    private function checkTaskStatus($taskuuid, $vmuuids){
        $sql = "select bt.task_status, count(vml.machine_id) as vm_num from bd_task bt, vm_machine_list vml where bt.task_uuid = vml.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(intval($data[0]['task_status']) != Xphp::$_config['TASKSTATUS']['STOPPED']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS_STATUS_ERROR'], Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS_STATUS_ERROR_TIPS'], 'warning'));
        }

        $count = intval($data[0]['vm_num']) - count($vmuuids);
        if($count == 0){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS_ALL_ERROR'], Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS_ALL_ERROR_TIPS'], 'warning'));
        }
    }

    /**
     * 下载历史任务日志检查
     * @param unknown $params
     */
    public function downloadHistoryCheck($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_history_job_download");
        $id = $params['ids'][0];

        //卷cdp没有日志,告警表中未关联历史任务uuid
        $productSql = "select module_type from bd_history_task where id = ?";
        $productData = $this->dbSelect($productSql, array($id));
        if($productData[0]['module_type']==Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_LOG_NOT_DOWNLOAD_TASK_LOG_ERROR'], Xphp::$_lang['WEB_LOG_VOL_CDP_TASK_LOG_NOT_EXIST_ERROR_TIPS'], 'warning'));
        }

        $sql = "select bta.task_alarm_id, bta.alarm_level, bta.module_type from bd_history_task bht, bd_task_alarm bta where bht.history_uuid = bta.history_uuid and bht.history_uuid != '' and bht.id = ? ";
        $data = $this->dbSelect($sql, array($id));
        if(empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_LOG_NOT_EXIST_ERROR'], Xphp::$_lang['WEB_LOG__TASK_LOG_ERROR_NOT_EXIST_TIPS'], 'warning'));
        }

        if(intval($data[0]['alarm_level']) != 2 && intval($data[0]['alarm_level']) != 3){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_LOG_NOT_DOWNLOAD_TASK_LOG_ERROR'], Xphp::$_lang['WEB_LOG_NOT_DOWNLOAD_TASK_LOG_ERROR_TIPS'], 'warning'));
        }

        $msg = array(
            'alarm_id' => $data[0]['task_alarm_id']
        );

        return json_encode($msg);

    }

    /**
     * 下载跳过文件
     * @param unknown $params
     */
    public function downLoadPassFile($params){
        $nodeuuid =  $params['fsnodeuuid'];
        $historyUuid = $params['history_uuid'];
        $filename = 'passfilelist';
        $agentUuid = $params['agent_uuid'];
        $opName = "FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET";
        $blockSize = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];
        $msg = array(
            "history_uuid" => $historyUuid,
            "read_offset" => 0,
            "read_size" => $blockSize,
            "agent_uuid" => $agentUuid,
        );
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);
        if($mbResult['result']) {
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Content-Disposition: attachment; filename=". $filename . ".txt");
            //如果大于分块大小,分块下载
            $filesize = $mbResult['msg']['file_size'];
            for($i=0; $i<$filesize; $i = $i + $blockSize){
                if($filesize - $i <= $blockSize){
                    $readLen = $filesize - $i;
                }else{
                    $readLen = $blockSize;
                }
                $msg = array(
                    "history_uuid" => $historyUuid,
                    "read_offset" => $i,
                    "read_size" => $readLen,
                    "agent_uuid" => $agentUuid,
                );
                $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);
                echo  $mbResult['msg']['data'];
                ob_flush(); //将数据从php的buffer中释放出来
                flush(); //将释放出来的数据发送给浏览器
            }
        } else {
            //获取文件大小失败
            return $this->muOpResult(false, Xphp::$_lang['UI_FILE_DOWNLOAD_SKIP_FILE'], Xphp::$_lang['UI_FILE_DOWNLOAD_SKIP_FILE'] . Xphp::$_lang['WEB_ERROR_BD_GENERIC_ERROR'], "warning");
        }

    }

    /**
     * 获取任务告警虚机列表详情信息
     * @param unknown $params
     * @return string
     */
    public function getTaskAlarmDetailsVmInfo($params){
        $id = $params['id'];
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sql = "select bht.details, bht.module_type, bht.task_type from bd_history_task bht, bd_task_alarm  bta where bht.task_uuid = bta.task_uuid and bht.history_uuid = bta.history_uuid and bta.task_alarm_id = ? ";
        $data = $this->dbSelect($sql, array($id));
        $details = json_decode($data[0]['details'],true);
        $details = $details ?? [];
        $moduleType = intval($data[0]['module_type']);

        $records = array("data" => array());
        $utils = Xphp::instance('Utils');
        $id = 0;
        if (isset($details['vms_changed_status'])) {
            $vms_details = $details['vms_details'] ?: [];
        } else {
            $vms_details = $details;
        }
        foreach ($vms_details as $d){
            if($moduleType == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
                $status = $d['task_status'];
                $statusDes = Xphp::$_vmdes['CopyTaskStatus'][intval($status)];
                $errorDes = $this->getCopyErrorCodeDes($status, $d['error_code']);
            }else{
                $status = $d['task_status'];
                //数据验证
                if(intval($data[0]['task_type']) == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                    $statusDes = Xphp::$_vmdes['VERIFY_VM_STATUS'][intval($status)];
                }else{
                    $statusDes = Xphp::$_vmdes['VmTaskStatus'][intval($status)];
                }
                $errorDes = $this->getVMErrorCodeDes($status, $d['error_code']);
            }
            $records["data"][] = array(
                ++$id,
                $d['vm_name'],
                $statusDes,
                intval($status),
                $errorDes,
                intval($data[0]['module_type']),
                intval($data[0]['task_type'])
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = count($details);
        $records["recordsFiltered"] = count($details);
        return json_encode($records);

    }


    private function getDBAgentConfig($taskuuid, $taskType, $timepointRecoveryType){
        $utils = Xphp::instance('Utils');
        $sql = "SELECT dt.db_type, dt.channel_count, dt.last_archive_days, dt.delete_archive_log_flag, dt.check_db_flag,
                    dt.compress_flag, dt.checksum_flag, dt.detail, dt.set_filesperset_flag, dt.datafile_filesperset_num,
                    dt.archivelog_filesperset_num, dt.skip_inaccessible_file_flag, dt.skip_offline_file_flag,
                    dt.auto_log_backup_interval, dt.recovery_time_flag,
                    dt.log_backup_times_flag, dt.log_backup_times, dt.log_backup_days_flag, dt.log_backup_days,
                    dt.enable_bct_flag, dt.set_section_size_flag, dt.section_size,
                    dt.compress_method, dt.compress_level, dt.multi_task_flag, dt.custom_rman_cmd,
                    dt.depend_task_uuid,
                    dl.data_file_path, dl.log_file_path, dl.log_rollback_time, dl.db_name, dl.instance_name,
                    dl.new_db_name,
                    dl.recovery_mode, dl.timepoint_uuid, dl.detail AS list_config, dl.modify_pfile_flag,
                    dl.detail AS dl_detail, dl.agent_uuid AS target_agent_uuid,
                    dl.log_restore_start_time, dl.log_restore_end_time,
                    bts.max_object_transport_parallel_nums,
                    dl.new_db_password, dl.open_db_flag, dl.recovery_time,
                    dl.before_task_script, dl.after_task_script, dl.verification_script,
                    dbt.instance_name AS target_instance_name, dbt.db_config,
                    bt.ignore_resource_limiting_flag,
                    bbt.timepoint, bbt.timepoint_uuid
                FROM db_task dt
                    LEFT JOIN db_list dl ON dt.task_uuid = dl.task_uuid
                    INNER JOIN bd_task bt ON dt.task_uuid = bt.task_uuid
                    LEFT JOIN bd_transport_strategy bts ON bt.strategy_id = bts.strategy_id
                    LEFT JOIN db_backup_timepoint dbt ON dl.timepoint_uuid = dbt.timepoint_uuid
                    LEFT JOIN bd_backup_timepoint bbt ON dl.timepoint_uuid = bbt.timepoint_uuid
                WHERE dt.task_uuid = ? ";
        $data= $this->dbSelect($sql, array($taskuuid));
        $recoveryMode = (int) $data[0]['recovery_mode'];
        $allRecoveryMode = Xphp::$_config['DB_RECOVERY_TYPE'];
        $info = array(
            'checkdbflag' => false,
            'compressflag' => false,
            'checksumflag' =>false,
            'channel_count' => 0,
            'last_archive_days' => 0,
            'delete_archive_log_flag' => false,
            'recovery_type' => Xphp::$_lang['UI_DB_ORIGINAL_COVERAGE_RECOVERY'],
            'recovery_mode' => $recoveryMode,
            'data_file_path' => '',
            'log_file_path' => '',
            'orginal_flag' => true,
            'log_rollback_time' => "",
            'pdb_design_time' => "",
            'details' => "",
            'timepoint_type' => 0,
            'set_filesperset_flag' => false,
            'datafile_filesperset_num' => 0,
            'archivelog_filesperset_num' => 0,
            'modify_pfile_flag' => false,
            'oracle_detail' => [],
            'max_object_transport_parallel_nums' => 0,
            'log_restore_start_time' => '',
            'log_restore_end_time' => '',
            'log_backup_times_flag' => false,
            'log_backup_times' => 0,
            'log_backup_days_flag' => false,
            'log_backup_days' => 0,
            'new_db_password' => '',
            'open_db_flag' => false,
            'db_config' => '',
            'target_cluster_flag' => false,
            'skip_inaccessible_file_flag' => false,
            'skip_offline_file_flag' => false,
            'recovery_time_flag' => 0,
            'ignore_resource_limiting_flag' => false,
            'enable_bct_flag' => false,
            'source_storage_uuid' => '',
            'source_storage_type' => '',
            'compress_method' => 0,
            'set_section_size_flag' => false,
            'section_size' => 0,
            'recovery_time' => '',
            'before_task_script' => [],
            'after_task_script' => [],
            'verification_script' => [],
            'multi_task_flag' => false,
            'custom_rman_cmd' => '',
            'depend_task_uuid' => '',
            'new_db_name' => '',
            'instance_name' => '',
            'timepoint' => '',
            'timepoint_uuid' => '',
        );
        $dbDes = include APP_PATH . 'dbprotect/DbDescription.php';
        if(!empty($data)){
            $info['ignore_resource_limiting_flag'] = $utils->parseFlagToBool($data[0]['ignore_resource_limiting_flag']);
            if (
                Xphp::$_config['TASKTYPE']['DB_RECOVERY'] == $taskType ||
                Xphp::$_config['TASKTYPE']['DRILL'] == $taskType
            ) {  // 数据库恢复
                // 备份目标是否为集群
                $sql = "SELECT baa.cluster_flag, baa.cluster_uuid
                        FROM bd_agent_app baa
                        WHERE baa.agent_uuid = ? ";
                $clusterData = $this->dbSelect($sql, [$data[0]['target_agent_uuid']]);
                if ($clusterData && is_array($clusterData)) {
                    $info['target_cluster_flag'] = $utils->parseFlagToBool($clusterData[0]['cluster_flag']) && $clusterData[0]['cluster_uuid'];
                }

                // 查看备份点的存储信息
                $sql = "SELECT bsr.storage_uuid, bsr.storage_type
                        FROM bd_storage_resource bsr
                            INNER JOIN bd_backup_timepoint bbt ON bsr.storage_uuid = bbt.storage_uuid
                        WHERE bbt.timepoint_uuid = ? ";
                $sourceStorageData = $this->dbSelect($sql, [$data[0]['timepoint_uuid']]);
                $info['source_storage_uuid'] = $sourceStorageData[0]['storage_uuid'];
                $info['source_storage_type'] = $sourceStorageData[0]['storage_type'];
                $info['timepoint'] = $data[0]['timepoint'];
                $info['timepoint_uuid'] = $data[0]['timepoint_uuid'];
            }

            $dbType = intval($data[0]['db_type']);
            $info['data_file_path'] = $data[0]['data_file_path'];
            $info['log_file_path'] = $data[0]['log_file_path'];
            $info['db_config'] = $data[0]['db_config'];
            $info['recovery_time'] = $data[0]['recovery_time'];
            $info['before_task_script'] = json_decode($data[0]['before_task_script'], true);
            $info['after_task_script'] = json_decode($data[0]['after_task_script'], true);
            $info['verification_script'] = json_decode($data[0]['verification_script'], true);
            //有日志回滚时间
            if(!empty($data[0]['log_rollback_time']) && $data[0]['log_rollback_time'] != "0000-00-00 00:00:00"){
                $info['log_rollback_time'] = $data[0]['log_rollback_time'];
            }
            $info['dbtype'] = $dbType;

            switch ($dbType){
                case Xphp::$_config['DB_TYPE']['SQLSERVER']:
                    $info['checkdbflag'] = $utils->parseFlagToBool($data[0]['check_db_flag']);
                    $info['compressflag'] = $utils->parseFlagToBool($data[0]['compress_flag']);
                    $info['checksumflag'] = $utils->parseFlagToBool($data[0]['checksum_flag']);
                    //新建数据库恢复
                    if($allRecoveryMode['CREATE'] == $recoveryMode){
                        $info['orginal_flag'] = false;
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_ADD_NEW_DATABASE_RECOVERY'];
                    }
                    break;
                case Xphp::$_config['DB_TYPE']['ORACLE']:
                    $info['compressflag'] = $utils->parseFlagToBool($data[0]['compress_flag']);
                    $info['checkdbflag'] = $utils->parseFlagToBool($data[0]['check_db_flag']);
                    $info['channel_count'] = intval($data[0]['channel_count']);
                    $info['last_archive_days'] = intval($data[0]['last_archive_days']);
                    $info['delete_archive_log_flag'] = intval($data[0]['delete_archive_log_flag']);
                    $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY'];
                    if ($recoveryMode == $allRecoveryMode['EXPORT']) {
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_EXPORT_REDIRECT_RECOVERY'];
                    } elseif ($recoveryMode == $allRecoveryMode['SPECIFY_FOLDER']) {
                        //指定文件夹恢复
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_DM_FILE_DIR_RECOVERY'];
                    } elseif ($recoveryMode == $allRecoveryMode['RESTORE_ARCHIVELOG']) {
                        //还原归档日志恢复
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_RESTORE_ARCHIVELOG'];
                        $info['log_restore_start_time'] = $data[0]['log_restore_start_time'];
                        $info['log_restore_end_time'] = $data[0]['log_restore_end_time'];
                    } elseif ($recoveryMode == $allRecoveryMode['PDB']) {
                        //pdb恢复
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_PDB_RECOVER'];
                        $info['pdb_design_time'] = $data[0]['log_rollback_time'];
                        $info['log_rollback_time'] = "";
                    } elseif ($recoveryMode == $allRecoveryMode['FULL']) {
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_FULL_RECOVERY'];
                    } elseif ($recoveryMode == $allRecoveryMode['INCOMPLETE']) {
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_INCOMPLETE_RECOVERY'];
                        $info['recovery_time_flag'] = intval($data[0]['recovery_time_flag']);
                    }
                    $info['set_filesperset_flag'] = $utils->parseFlagToBool($data[0]['set_filesperset_flag']);
                    $info['datafile_filesperset_num'] = (int) $data[0]['datafile_filesperset_num'];
                    $info['archivelog_filesperset_num'] = (int) $data[0]['archivelog_filesperset_num'];
                    $info['modify_pfile_flag'] = $utils->parseFlagToBool($data[0]['modify_pfile_flag']);
                    $info['log_backup_times_flag'] = $utils->parseFlagToBool($data[0]['log_backup_times_flag']);
                    $info['log_backup_times'] = (int) $data[0]['log_backup_times'];
                    $info['log_backup_days_flag'] = $utils->parseFlagToBool($data[0]['log_backup_days_flag']);
                    $info['log_backup_days'] = (int) $data[0]['log_backup_days'];
                    $info['open_db_flag'] = $utils->parseFlagToBool($data[0]['open_db_flag']);
                    // 是否恢复到空实例上
                    $info['oracle_recovery_type'] = $dbDes['ORACLE_RECOVERY_TYPE']['NORMAL'];
                    if ($data[0]['instance_name'] === $data[0]['target_instance_name']) {
                        $sql = "SELECT app_auth_type FROM bd_agent_app WHERE app_type = ? AND app_name = ? AND agent_uuid = ? ";
                        $appData = $this->dbSelect($sql, [$dbType, $data[0]['instance_name'], $data[0]['target_agent_uuid']]);
                        if ($appData) {
                            if ($appData[0]['app_auth_type'] == $dbDes['ORACLE_AUTH_TYPE']['OS_AUTH']) {
                                $info['oracle_recovery_type'] = $dbDes['ORACLE_RECOVERY_TYPE']['EMPTY_INSTANCE'];
                            }
                        }
                    }
                    $info['new_db_password'] = $data[0]['new_db_password'];
                    try {
                        $oracleDetail = json_decode($data[0]['dl_detail'], true);
                        if ($oracleDetail) {
                            $info['oracle_detail'] = $oracleDetail;
                        }
                    } catch (Exception $e) {}
                    $info['skip_inaccessible_file_flag'] = $utils->parseFlagToBool($data[0]['skip_inaccessible_file_flag']);
                    $info['skip_offline_file_flag'] = $utils->parseFlagToBool($data[0]['skip_offline_file_flag']);
                    $info['enable_bct_flag'] = $utils->parseFlagToBool($data[0]['enable_bct_flag']);
                    $info['set_section_size_flag'] = $utils->parseFlagToBool($data[0]['set_section_size_flag']);
                    $info['section_size'] = (int) $data[0]['section_size'];
                    $info['multi_task_flag'] = $utils->parseFlagToBool($data[0]['multi_task_flag']);
                    $info['custom_rman_cmd'] = $data[0]['custom_rman_cmd'] ?: '';
                    $info['instance_name'] = $data[0]['instance_name'];
                    $info['new_db_name'] = $data[0]['new_db_name'];
                    break;
                case Xphp::$_config['DB_TYPE']['MYSQL']:
                case Xphp::$_config['DB_TYPE']['MARIA']:
                    $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY'];
                    //重定向目录恢复
                    if($recoveryMode == $allRecoveryMode['REDIRECT_DIR']){
                        $info['orginal_flag'] = false;
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_DM_REDIRECT_RECOVERY'];
                    }
                    $details = json_decode($data[0]['list_config'], true);
                    $otherInfo =  array(
                        'command' => $details['start_command'],
                        'stop_command' => $details['stop_command'],
                        'start_type' => intval($details['start_type']),
                    );
                    $info['details'] = $otherInfo;
                    $info['open_db_flag'] = $utils->parseFlagToBool($data[0]['open_db_flag']);
                    $info['channel_count'] = (int) $data[0]['channel_count'];
                    $info['compressflag'] = $utils->parseFlagToBool($data[0]['compress_flag']);
                    break;
                case Xphp::$_config['DB_TYPE']['DM']:
                    $info['compressflag'] = $utils->parseFlagToBool($data[0]['compress_flag']);
                    $info['compress_level'] = $data[0]['compress_level'];
                    $info['delete_archive_log_flag'] = $utils->parseFlagToBool($data[0]['delete_archive_log_flag']);
                    $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY'];
                    //指定文件夹恢复
                    if($recoveryMode == $allRecoveryMode['SPECIFY_FOLDER']){
                        $info['orginal_flag'] = false;
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_DM_FILE_DIR_RECOVERY'];
                    }
                    break;
                case Xphp::$_config['DB_TYPE']['POSTGRE']:
                case Xphp::$_config['DB_TYPE']['ANTDB']:
                case Xphp::$_config['DB_TYPE']['KINGBASE']:
                case Xphp::$_config['DB_TYPE']['UXDB']:
                case Xphp::$_config['DB_TYPE']['HIGHGO']:
                case Xphp::$_config['DB_TYPE']['OPENGAUSS']:
                case Xphp::$_config['DB_TYPE']['VASTBASE']:
                    $info['open_db_flag'] = $utils->parseFlagToBool($data[0]['open_db_flag']);
                    $info['compressflag'] = intval($data[0]['compress_flag']);
                    $info['delete_archive_log_flag'] = $this->getDbDeleteLogDes(intval($data[0]['delete_archive_log_flag']));
                    $info['details'] = $this->getDbDetails(json_decode($data[0]['detail'],true), $utils);
                    $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY'];
                    //指定文件夹恢复
                    if($recoveryMode == $allRecoveryMode['SPECIFY_FOLDER']){
                        $info['orginal_flag'] = false;
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_DM_FILE_DIR_RECOVERY'];
                    } elseif ($recoveryMode == $allRecoveryMode['CREATE']) {  // 新建实例恢复
                        $info['orginal_flag'] = false;
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_ADD_NEW_INSTANCE_RECOVERY'];
                    }
                    break;
                case Xphp::$_config['DB_TYPE']['MONGODB']:
                    $info['max_object_transport_parallel_nums'] = $data[0]['max_object_transport_parallel_nums'];
                    $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY'];
                    $info['open_db_flag'] = $utils->parseFlagToBool($data[0]['open_db_flag']);
                    //指定文件夹恢复
                    if($allRecoveryMode['SPECIFY_FOLDER'] == $recoveryMode){
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_DM_FILE_DIR_RECOVERY'];
                    }
                    break;
                case Xphp::$_config['DB_TYPE']['TIDB']:
                    $info['compress_method'] = $data[0]['compress_method'];
                    $info['compress_level'] = $data[0]['compress_level'];
                    $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY'];
                    $info['depend_task_uuid'] = $data[0]['depend_task_uuid'];
                    $info['recovery_time_flag'] = intval($data[0]['recovery_time_flag']);
                    $info['open_db_flag'] = $utils->parseFlagToBool($data[0]['open_db_flag']);
                    break;
                case Xphp::$_config['DB_TYPE']['SAPHANA']:
                    $info['auto_log_backup_interval'] = intval($data[0]['auto_log_backup_interval']);
                    $info['compressflag'] = $utils->parseFlagToBool($data[0]['compress_flag']);
                    $info['channel_count'] = intval($data[0]['channel_count']);
                    $info['recovery_time_flag'] = intval($data[0]['recovery_time_flag']);
                    //新建数据库恢复
                    if($allRecoveryMode['CREATE'] == $recoveryMode){
                        $info['orginal_flag'] = false;
                        $info['recovery_type'] = Xphp::$_lang['UI_DB_ADD_NEW_DATABASE_RECOVERY'];
                    }
                    break;
            }

            if($recoveryMode == $allRecoveryMode['COVER'] && $timepointRecoveryType === 2) {  // 定时恢复最新点
                $info['recovery_type'] = Xphp::$_lang['UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY'];  // 显示为覆盖恢复
            }

            //获取时间点类型
            $sql = "select backup_mode from bd_backup_timepoint where timepoint_uuid = ?";
            $dataPoint = $this->dbSelect($sql, array($data[0]['timepoint_uuid']));
            $info['timepoint_type'] = intval($dataPoint[0]['backup_mode']);
        }


        return $info;
    }

    /**
     * 获取策略所有相关任务
     * @param string $strategyID
     */
    private function getTaskInStrategy($strategyID){
        $sql = "select task_name from bd_task where strategy_group_uuid = ? ";
        $data = $this->dbSelect($sql, array($strategyID));
        $info = "";
        if(!empty($data)){
            foreach ($data as $d){
                $info .= $d['task_name']. "<br>";
            }
        }else{
            $info = Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'];
        }
        return $info;
    }


    /**
     * 获取xen版本判断有无静默快照flag
     * @param string $taskuuid
     */
    private function getXenSnapshotFlag($taskuuid){
        $sql = "select distinct vcenter_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $vcenter = Xphp::instance('Vcenter');
        return $vcenter->getSnapshotFlag($data[0]['vcenter_uuid']);
    }

    /**
     * 获取恢复全磁盘恢复开启/关闭
     * @param unknown $taskuuid
     * @return number
     */
    private function getRecoveryFullDisk($taskuuid){
        $sql = "select level from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;
        if(!empty($data)){
            $level = intval($data[0]['level']);
            if($level == Xphp::$_config['VM_RECOVERY_ZERO']['INCLOUD_ZERO']){
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * 获取任务下次开始时间
     * @param unknown $taskuuid
     */
    private function getJobNextstarttime($taskuuid, $taskStatus){
        $sql ="select unix_timestamp(bs.next_start_time) next_start_time from bd_task bt, bd_strategy bs where bt.strategy_id = bs.strategy_id and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nextTime = Xphp::$_config['TIMESPACE'];
        if(!empty($data)){
            $utils = Xphp::instance('Utils');
            $nowTime = time();
            if($data[0]['next_start_time'] < $nowTime){
                $nextTime = Xphp::$_config['TIMESPACE'];
            }else{
                $nextTime = date('Y-m-d H:i:s', $data[0]['next_start_time']);
            }
        }

        if(intval($taskStatus) == Xphp::$_config['TASKSTATUS']['STOPPED']){
            $nextTime = Xphp::$_config['TIMESPACE'];
        }

        return $nextTime;
    }

    /**
     * 启动文件任务检测用户使用代理权限
     * @param string $uuid
     * @param string $opName
     */
    public function pCheckAgentPermission($uuid, $opName){
        $sql = "select ba.user_uuid from bd_agent ba, bd_task bt where bt.agent_uuid = ba.agent_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql,array($uuid));

        //获取文件代理关联用户
        $sqlUser = "select mur.user_uuid from bd_task bt, mt_user_resource mur where bt.user_uuid = mur.user_uuid and bt.task_uuid = ? ";
        $dataUser = $this->dbSelect($sql, array($uuid));
        if(!empty($data)){
            $useruuid = $data[0]['user_uuid'];
            //文件代理不属于当前用户，或未分配给当前用户直接返回错误
            if($useruuid != Xphp::$_user['useruuid'] && !in_array(Xphp::$_user['useruuid'], $dataUser) && (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] != "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
                exit($this->muOpResult(false, $opName, Xphp::$_lang['WEB_FILE_AGENT_NOT_AUTH'], "warning"));
            }
        }

    }


    /**
     * 获取任务告警数据库详情信息列表
     * @param unknown $params
     * @return string
     */
    public function getTaskAlarmDetailsDbInfo($params){
        $id = $params['id'];
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sql = "select bht.details, bht.module_type from bd_history_task bht, bd_task_alarm  bta where bht.history_uuid = bta.history_uuid and bta.task_alarm_id = ? ";
        $data = $this->dbSelect($sql, array($id));
        $details = json_decode($data[0]['details'],true);
        $details = $details ?? [];
        $moduleType = intval($data[0]['module_type']);

        $records = array("data" => array());
        $utils = Xphp::instance('Utils');
        $id = 0;
        foreach ($details as $d){
            $status = $d['task_status'];
            $statusDes = Xphp::$_vmdes['VmTaskStatus'][intval($status)];
            $errorDes = $this->getVMErrorCodeDes($status, $d['error_code']);
            $records["data"][] = array(
                ++$id,
                $d['db_name'],
                $statusDes,
                intval($status),
                $errorDes,
                intval($data[0]['module_type'])
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = count($details);
        $records["recordsFiltered"] = count($details);

        return json_encode($records);

    }


    /**
     * 获取任务告警操作系统详情信息列表
     * @param unknown $params
     * @return string
     */
    public function getTaskAlarmDetailsOSInfo($params){
        $id = $params['id'];
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sql = "select bht.details, bht.module_type from bd_history_task bht, bd_task_alarm  bta where bht.history_uuid = bta.history_uuid and bta.task_alarm_id = ? ";
        $data = $this->dbSelect($sql, array($id));
        $details = json_decode($data[0]['details'],true);
        $details = $details ?? [];
        $moduleType = intval($data[0]['module_type']);
        $ptDes = include APP_PATH . 'platform/PFDescription.php';

        $records = array("data" => array());
        $utils = Xphp::instance('Utils');
        $id = 0;
        foreach ($details as $d){
            $status = $d['task_status'];
            $statusDes = $ptDes['TASKSTATUSDES'][intval($status)];
            $errorDes = $this->getErrorCodeDes($status, $d['error_code']);
            $records["data"][] = array(
                ++$id,
                $d['os_name']."(".$d['agent_ip'].")",
                $statusDes,
                intval($status),
                $errorDes,
                intval($data[0]['module_type'])
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = count($details);
        $records["recordsFiltered"] = count($details);

        return json_encode($records);

    }




    /**
     * 获取任务告警操作系统详情信息列表
     * @param unknown $params
     * @return string
     */
    public function getTaskAlarmDetailsCopyInfo($params){
        $id = $params['id'];
        $sql = "select bht.details,bht.task_type, bht.module_type, bht.submodule_type from bd_history_task bht, bd_task_alarm  bta where bht.history_uuid = bta.history_uuid and bta.task_alarm_id = ? ";
        $data = $this->dbSelect($sql, array($id));
        $details = json_decode($data[0]['details'],true);
        $details = $details ?? [];
        $task_type = intval($data[0]['task_type']);
        $ptDes = include APP_PATH . 'platform/PFDescription.php';

        $records = array("data" => array());
        $id = 0;
        $module = $data[0]['module_type'];
        if ($d['submodule_type'] == Xphp::$_config['MODULE_TYPE']) {
            $sub_module_type = intval($details[0]['hypervisor_type']);
            if ($sub_module_type == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_AWS']) {
                $module = 17;
            }
        }
        foreach ($details as $d){
            $status = $d['item_status'];
            $statusDes = $ptDes['COPY_TASK_STATUS'][intval($status)];
            $errorDes = $this->getErrorCodeDes($status, $d['error_code'],true);
            $records["data"][] = array(
                ++$id,
                $d['ip'] ? $d['item_name']."(".$d['ip'].")" : $d['item_name'],
                $statusDes,
                intval($status),
                $errorDes,
                intval($module),
                $task_type
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = count($details);
        $records["recordsFiltered"] = count($details);

        return json_encode($records);

    }





    /**
     * 得到历史任务带入节点查询
     * @param unknown $params
     */
    public function getHistoryJobsV2($params){
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
        $utils = Xphp::instance('Utils');
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $name = $search['name'];
        $name = $utils->escapeWildcard($name);
        $draw = $params['draw'];
        $accurateFlag = $params['accurateFlag'];
        $sortArr = array('', '', 'bht.task_name', 'bht.submodule_type', 'bht.task_type', 'bht.user_name',
            'bht.total_object_size', '', '', 'bht.total_object_write_size', 'bht.start_time', 'bht.finish_time', 'bht.error_code'
        );

        $sql = "select distinct bht.id, bht.history_uuid, bht.task_name, bht.module_type, bht.submodule_type, bht.task_type, bht.current_mode, bht.error_code, bht.details, bht.total_object_size, bht.total_object_transport_size, bht.total_object_completed_size,
                bht.total_object_write_size, bht.average_speed, unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time, bht.user_name, bht.task_uuid, bht.history_uuid from bd_history_task bht left join bd_task_alarm bta on bht.history_uuid = bta.history_uuid ";
        $sqlCount = "select count(distinct bht.id) as total from bd_history_task bht left join bd_task_alarm bta on bht.history_uuid = bta.history_uuid ";

        //此处作用是为了添加where，代替下面判断不确定的地方加where
        $sql .= " where bht.id != 0 ";
        $sqlCount .= " where bht.id != 0 ";
        $sqlParams = array();
        $sqlCountParams = array();
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= " and (bht.user_uuid = ? or (bht.user_uuid = '' and bht.user_name = ?)) ";
            $sqlCount .= " and (bht.user_uuid = ? or (bht.user_uuid = '' and bht.user_name = ?)) ";
            $sqlParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
            $sqlCountParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
        }
        if($this->checkEmpty($name)  && !$accurateFlag){
            //按任务名或虚拟机名搜索
            $searchParams = "%" . $name . "%";
            $sql .= " and bht.task_name like ? ";
            $sqlCount .= " and bht.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array($searchParams));
            $sqlCountParams = array_merge($sqlCountParams, array($searchParams));
        }
        //精确搜索
        if($accurateFlag){
            $search = $params['search'];
            $taskName = $search['taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $hypervisor = intval($search['hypervisor']);
            $dbType = intval($search['dbtype']);
            $taskType = intval($search['taskType']);
            $moduleType = intval($search['moduleType']);
            $errorCode = $search['errorCode'];
            $vmname = $search['vmname'];
            $timeType = intval($search['timeType']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['nodeuuid'];

            if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
                //虚拟化类型
                $submoduleType = $hypervisor;
            }else if($moduleType == Xphp::$_config['MODULE_TYPE']['DB']){
                //数据库类型
                $submoduleType = $dbType;
            }

            //默认备份恢复包含vm/fs/db/os/nas
            $tasktypeList = array($taskType);
            if(empty($moduleType)){
                if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
                    $tasktypeList = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['TASKTYPE']['DB_BACKUP'], Xphp::$_config['TASKTYPE']['OS_BACKUP']);
                }else if($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']){
                    $tasktypeList = array(Xphp::$_config['TASKTYPE']['RECOVERY'], Xphp::$_config['TASKTYPE']['DB_RECOVERY'], Xphp::$_config['TASKTYPE']['OS_RECOVERY']);
                }
            }

            //子模块类型
            if(!empty($submoduleType)){
                $sql .= " and bht.submodule_type = ? ";
                $sqlCount .= " and bht.submodule_type = ? ";
                $sqlParams = array_merge($sqlParams, array($submoduleType));
                $sqlCountParams = array_merge($sqlCountParams, array($submoduleType));
            }


            //模块类型
            if(!empty($moduleType)){
                $sql .= " and bht.module_type = ? ";
                $sqlCount .= " and bht.module_type = ? ";
                $sqlParams = array_merge($sqlParams, array($moduleType));
                $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
            }

            $tasktypeListDes = implode(',', $tasktypeList);

            //任务类型
            if(!empty($taskType)){
                $sql .= " and bht.task_type in (".$tasktypeListDes.") ";
                $sqlCount .= " and bht.task_type in (".$tasktypeListDes.") ";
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bht.task_name like ? ";
                $sqlCount .= " and bht.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            //虚拟机名
            if($this->checkEmpty($vmname)){
                $sql .= " and bht.details like ? ";
                $sqlCount .= " and bht.details like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$vmname.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$vmname.'%'));
            }

            //如果选择了节点
            if(!empty($nodeuuid)){
                $sql .= " and bta.node_uuid = ? and bht.history_uuid != ''";
                $sqlCount .= " and bta.node_uuid = ? and bht.history_uuid != ''";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

            //如果选了错误，没有填错误码
            if(!$this->checkEmpty($errorCode) || $errorCode == "all"){
                //所有任务状态的
            }else if($errorCode == "error"){
                $sql .=" and bht.error_code not in (0, 45, 47)";
                $sqlCount .=" and bht.error_code not in (0, 45, 47)";
            }else{
                $sql .=" and bht.error_code = ?";
                $sqlCount .=" and bht.error_code = ?";
                $errorCode = intval($errorCode);
                $sqlParams = array_merge($sqlParams, array($errorCode));
                $sqlCountParams = array_merge($sqlCountParams, array($errorCode));
            }

            //如果填了开始时间范围查询
            if($startTime && $endTime){

                if($timeType == "1"){
                    $key = "bht.start_time";
                }else{
                    $key = "bht.finish_time";
                }
                $sql .= " and ".$key." between ? and ? ";
                $sqlCount .= " and ".$key." between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }


        }


        $sql .= " order by $sortArr[$sortColumn]  $sortType limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!$count){
            $total = intval($count);
        }else{
            $total = $count[0]['total'];
        }
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';

        $records["data"] = array();
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $transportSize = $this->getTransportSize($d['total_object_transport_size'], $d['total_object_completed_size'], intval($d['module_type']));
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['task_name'],
                $this->getVMTaskType($d['module_type'], $d['submodule_type'], $d['task_type']),
                $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                $d['user_name'],
                $utils->calSize($d['total_object_size'], true),
                $this->getTotalValidSize($details, intval($d['module_type']), intval($d['task_type']), intval($d['total_object_size'])),
                $utils->calSize($transportSize, true),
                $utils->calSize($d['total_object_write_size'], true),
                $this->parseDate($d['start_time']),
                $this->parseDate($d['finish_time']),
                $this->getHistoryJobResultDes($d['error_code']),
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'module' => intval($d['module_type']),
                    'taskType' => intval($d['task_type'])
                ),
                'history_uuid' => $d['history_uuid'],
            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return  json_encode($records);
    }


    /**
     * 公共方法
     * 获取对应功能模块的子模块号
     * @param string $taskuuid 任务唯一标识
     * @param int $type  任务模块类型
     * @param int $taskType    任务类型
     * @author luokai@vinchin.com
     * @return number  返回子模块号
     */
    public function pGetTaskSubmodule($taskuuid, $type, $taskType){
        $subModule = 0;
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        switch ($type){
            case $moduleType['VM']:
                $subModule = $this->getVMTaskHypervisor($taskuuid);
                break;
            case $moduleType['DB']:
                $subModule = $this->getDBTaskHypervisor($taskuuid);
                break;
            case $moduleType['OS']:
                break;
            case $moduleType['VDDT_SERVER']:
                break;
            case $moduleType['BACKUP_COPY_CLIENT']:
                //副本|归档
                if(intval($taskType) == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || intval($taskType) == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                    $subModule = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_VM_BACKUP_COPY'];  //虚拟机副本子模块
                }else if(intval($taskType) == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'] || intval($taskType) == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY_FETCH']){
                    $subModule = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  //文件副本子模块
                }else if(intval($taskType) == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'] || intval($taskType) == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY_FETCH']){
                    $subModule = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_DB_BACKUP_COPY'];  //数据库副本子模块
                }else if(intval($taskType) == Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY'] || intval($taskType) == Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY_FETCH']){
                    $subModule = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  //nas副本子模块
                }else if(intval($taskType) == Xphp::$_config['TASKTYPE']['ARCHIVE'] || intval($taskType) == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                    //虚拟机归档
                    $subModule = Xphp::$_config['COPY_ARCHIVE_SUB_TYPE']['BACKUP_COPY_TYPE_VM_ARCHIVE'];
                }


                break;
            default:
                break;
        }

        return $subModule;
    }


    /**
     * 检查是否有备份任务依赖的副本或者归档任务存在
     * @param unknown $taskuuid
     * @return boolean
     */
    private function checkCopyOrArchiveExist($taskuuid){
       $sql = "select item_id from backup_copy_item_list where source_task_uuid = ?";
       $data = $this->dbSelect($sql, array($taskuuid));
       if(!empty($data)){
           exit($this->muOpResult(false, Xphp::$_lang['WEB_PT_OP_BACKUP_DELETE'], Xphp::$_lang['UI_COPY_DELETE_BACKUP_TASK_CHECK_TIPS'], "warning"));
       }

       return true;
    }

    /**
     * 返回任务建议执行时间
     * @param unknown $params
     */
    public function getSuggestTime($params){
        $time = "";
        //先检测小时
        $sqlHour ="SELECT timetable.hour Hour, ifnull(sumtable.count, 0) Count FROM (SELECT 0 hour UNION ALL SELECT 1 hour UNION ALL SELECT 2 hour UNION ALL SELECT 3 hour UNION ALL SELECT 4 hour UNION ALL SELECT 5 hour UNION ALL SELECT 6 hour UNION ALL SELECT 7 hour UNION ALL SELECT 8 hour UNION ALL SELECT 9 hour UNION ALL SELECT 10 hour UNION ALL SELECT 11 hour UNION ALL SELECT 12 hour UNION ALL SELECT 13 hour UNION ALL SELECT 14 hour UNION ALL SELECT 15 hour UNION ALL SELECT 16 hour UNION ALL SELECT 17 hour UNION ALL SELECT 18 hour UNION ALL SELECT 19 hour UNION ALL SELECT 20 hour UNION ALL SELECT 21 hour UNION ALL SELECT 22 hour UNION ALL SELECT 23 hour) timetable LEFT JOIN( SELECT
        hour(start_time)  hour, count(id) count from bd_time_strategy group by date_format(start_time, '%H'), hour) sumtable ON timetable.hour = sumtable.hour ORDER BY count asc, hour asc";
        $dataHour = $this->dbSelect($sqlHour);
        $hourInfo  = array();
        foreach ($dataHour as $hour){
            if($hour['Count'] == $dataHour[0]['Count']){
                $hourInfo[] = $hour['Hour'];
            }
        }
        //随机取最低消耗其中一个
        $hour = array_rand($hourInfo, 1);

        //继续检测分钟
        $sqlMinute ="SELECT timetable.minute Minute, ifnull(sumtable.count, 0) Count FROM (SELECT 0 minute UNION ALL SELECT 1 minute UNION ALL SELECT 2 minute UNION ALL SELECT 3 minute UNION ALL SELECT 4 minute UNION ALL SELECT 5 minute UNION ALL SELECT 6 minute UNION ALL SELECT 7 minute UNION ALL SELECT 8 minute UNION ALL SELECT 9 minute UNION ALL SELECT 10 minute UNION ALL SELECT 11 minute UNION ALL SELECT 12 minute UNION ALL SELECT 13 minute UNION ALL SELECT 14 minute
                    UNION ALL SELECT 15 minute UNION ALL SELECT 16 minute UNION ALL SELECT 17 minute UNION ALL SELECT 18 minute UNION ALL SELECT 19 minute UNION ALL SELECT 20 minute UNION ALL SELECT 21 minute UNION ALL SELECT 22 minute UNION ALL SELECT 23 minute UNION ALL SELECT 24 minute UNION ALL SELECT 25 minute UNION ALL SELECT 26 minute UNION ALL SELECT 27 minute UNION ALL SELECT 28 minute UNION ALL SELECT 29 minute
                    UNION ALL SELECT 30 minute UNION ALL SELECT 31 minute UNION ALL SELECT 32 minute UNION ALL SELECT 33 minute UNION ALL SELECT 34 minute UNION ALL SELECT 35 minute UNION ALL SELECT 36 minute UNION ALL SELECT 37 minute UNION ALL SELECT 38 minute UNION ALL SELECT 39 minute UNION ALL SELECT 40 minute UNION ALL SELECT 41 minute UNION ALL SELECT 42 minute UNION ALL SELECT 43 minute UNION ALL SELECT 44 minute
                    UNION ALL SELECT 45 minute UNION ALL SELECT 46 minute UNION ALL SELECT 47 minute UNION ALL SELECT 48 minute UNION ALL SELECT 49 minute UNION ALL SELECT 50 minute UNION ALL SELECT 51 minute UNION ALL SELECT 52 minute UNION ALL SELECT 53 minute UNION ALL SELECT 54 minute UNION ALL SELECT 55 minute UNION ALL SELECT 56 minute UNION ALL SELECT 57 minute UNION ALL SELECT 58 minute UNION ALL SELECT 59 minute
                    ) timetable LEFT JOIN( SELECT minute(start_time)  minute, count(id) count from bd_time_strategy group by date_format(start_time, '%i'), minute) sumtable ON timetable.minute = sumtable.minute  ORDER BY count asc, minute asc";
        $dataMinute = $this->dbSelect($sqlMinute);
        $minuteInfo = array();
        foreach ($dataMinute as $minute){
            if($minute['Count'] == $dataMinute[0]['Count']){
                $minuteInfo[] = $minute['Minute'];
            }
        }
        //随机取最低消耗其中一个
        $minute = array_rand($minuteInfo, 1);

        $timestamp = intval($hour)*3600 + intval($minute)*60;
        $endtimestamp = $timestamp + 30*60;
        $time = date('H:i:s', $timestamp);
        $endtime = date('H:i:s', $endtimestamp);
        $rollendstamp = $timestamp + 60*60;
        $rollendtime = date('H:i:s', $rollendstamp);
        $info = array(
            'start_time'=> $time,
            'end_time' => $endtime,
            'roll_end_time' => $rollendtime
        );
        return $info;

    }

    /**
     * 获取每个小时任务占用个数列表
     * @param unknown $params
     * @return string
     */
    public function getTimeCrowdList($params){
        $sqlHour ="SELECT distinct timetable.hour Hour, ifnull(sumtable.count, 0) Count FROM (SELECT 0 hour UNION ALL SELECT 1 hour UNION ALL SELECT 2 hour UNION ALL SELECT 3 hour UNION ALL SELECT 4 hour UNION ALL SELECT 5 hour UNION ALL SELECT 6 hour UNION ALL SELECT 7 hour UNION ALL SELECT 8 hour UNION ALL SELECT 9 hour UNION ALL SELECT 10 hour UNION ALL SELECT 11 hour UNION ALL SELECT 12 hour UNION ALL SELECT 13 hour UNION ALL SELECT 14 hour UNION ALL SELECT 15 hour UNION ALL SELECT 16 hour UNION ALL SELECT 17 hour UNION ALL SELECT 18 hour UNION ALL SELECT 19 hour UNION ALL SELECT 20 hour UNION ALL SELECT 21 hour UNION ALL SELECT 22 hour UNION ALL SELECT 23 hour) timetable LEFT JOIN( SELECT
        hour(start_time) hour, count(id) count from bd_time_strategy group by hour) sumtable ON timetable.hour = sumtable.hour ORDER BY hour asc";
        $dataHour = $this->dbSelect($sqlHour);
        $hourInfo  = array();
        foreach ($dataHour as $hour){
            $hourInfo[] = array(
                'hour' => $hour['Hour'],
                'num' => intval($hour['Count']),
                'date' => $hour['Hour'].":00:00". " ~ " . $hour['Hour'].":59:59"
            );
        }
        $showFlag = true;
        //租户内不显示任务个数描述
        if(!empty($_SESSION['tenantuuid'])){
            $showFlag = false;
        }

        $info = array(
            'timeList' => $hourInfo,
            'suggestTime' => $this->getSuggestTime(array()),
            'showFlag' => $showFlag
        );

        return json_encode($info);
    }
    /**
     * 获取卷CDP所有任务信息
     * @param unknown $params
     */
    public function getCurrentVolcdpJobs($params){
        $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
        return $volCdpHandler->getCurrentVolCdpJobInfo($params);
    }

    /**
     * 得到当前所有主机任务信息
     * @param unknown $params
     */
    public function getCurrentOsJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $utils = Xphp::instance('Utils');
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type',"", "bn.node_uuid", "",
            'bt.task_status', "", 'bri.speed', 'bri.total_object_completed_size', ''
        );

        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型  虚拟化类型  所在节点  下次运行时间  状态  持续时间  速度  进度  操作
        $sql = "select  bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, bn.node_uuid,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time, unix_timestamp(bri.start_time) start_time
                from bd_running_info bri, bd_task bt left join bd_node bn
                on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and
                     bt.delete_flag = 2 and bt.module_type = ?";
        $sqlCount = "select count(bt.id) as total from bd_running_info bri, bd_task bt left join bd_node bn
                on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and
                     bt.delete_flag = 2 and bt.module_type = ?";

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['OS']);
        $sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['OS']);
        if($this->checkEmpty($taskName) && !$accurateFlag){
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }


        $sql .= " and bt.user_uuid = ? ";
        $sqlCount .= " and bt.user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));

        if(!empty($accurateFlag)){
            $search = $params['search'];
            $createUser = $search['createUser'];
            $taskType = intval($search['os_taskType']);
            $taskStatus =intval($search['osjob_status']);
            $taskName = $search['os_taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['os_node'];


            //任务类型
            if(!empty($taskType)){
                $sql .= " and bt.task_type = ? ";
                $sqlCount .= " and bt.task_type = ?";
                $sqlParams = array_merge($sqlParams, array($taskType));
                $sqlCountParams = array_merge($sqlCountParams, array($taskType));
            }
            //任务状态
            if(!empty($taskStatus)){
                $sql .= " and bt.task_status = ? ";
                $sqlCount .= " and bt.task_status = ? ";
                $sqlParams = array_merge($sqlParams, array($taskStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bt.task_name like ? ";
                $sqlCount .= " and bt.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            if(!empty($nodeuuid)){
                $sql .= " and bt.node_uuid = ? ";
                $sqlCount .= " and bt.node_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between ? and ? ";
                $sqlCount .= " and bt.create_time between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $nodeHandler = Xphp::instance('NodeHandler');

        $records["data"] = array();
        foreach ($data as $d){

            $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']);
            $records["data"][] = array(
                $d['task_name'],
                $ptDes['TASKTYPEDES'][$d['task_type']],
                $this->getAgentInfo($d['task_uuid']),
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getTimeInterval($d['start_time'], $d['task_status']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $progress,
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
                $this->getJobVmInfo($d['task_uuid'], $d['task_type'])
            );
        }

        //         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }


    /**
     * 得到当前所有虚拟机任务信息
     * @param unknown $params
     */
    public function getCurrentVmJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type', 'vt.hypervisor_type', "bn.node_uuid", "",
            'bt.task_status', "", 'bri.speed', 'bri.total_object_completed_size', ''
        );
        $utils = Xphp::instance('Utils');
        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型  虚拟化类型  所在节点  下次运行时间  状态  持续时间  速度  进度  操作
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, vt.hypervisor_type, bn.node_uuid,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time, unix_timestamp(bri.start_time) start_time
                from bd_running_info bri, vm_task vt, bd_task bt left join sr_sure_backup ssb on bt.task_uuid = ssb.task_uuid left join bd_node bn
                on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and  
                     bt.task_uuid = vt.task_uuid and 
                     bt.delete_flag = 2 and bt.module_type = ? ";
        $sqlCount = "select count(bt.id) as total from bd_running_info bri, vm_task vt, bd_task bt left join bd_node bn
                    on bt.node_uuid = bn.node_uuid
                    where bt.task_uuid = bri.task_uuid and  
                     bt.task_uuid = vt.task_uuid and bt.delete_flag = 2 and bt.module_type = ? ";

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
        $sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
        if($this->checkEmpty($taskName) && !$accurateFlag){
            //按任务名搜索
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }


        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bt.user_uuid = ? ";
            $sqlCount .= "and bt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }

        if(!empty($accurateFlag)){
            $search = $params['search'];
            $createUser = $search['createUser'];
            $hypervisor = intval($search['vm_hypervisor']);
            $taskType = intval($search['vm_taskType']);
            $taskStatus =intval($search['vmjob_status']);
            $taskName = $search['vm_taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['vm_node'];

            if($hypervisor > 0){
                $sql .= " and vt.hypervisor_type = ".$hypervisor;
                $sqlCount .= " and vt.hypervisor_type = ".$hypervisor;
            }


            //任务类型
            if(!empty($taskType)){
                $sql .= " and bt.task_type = ? ";
                $sqlCount .= " and bt.task_type = ?";
                $sqlParams = array_merge($sqlParams, array($taskType));
                $sqlCountParams = array_merge($sqlCountParams, array($taskType));
            }
            //任务状态
            if(!empty($taskStatus)){
                $sql .= " and bt.task_status = ? ";
                $sqlCount .= " and bt.task_status = ? ";
                $sqlParams = array_merge($sqlParams, array($taskStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bt.task_name like ? ";
                $sqlCount .= " and bt.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            if(!empty($nodeuuid)){
                $sql .= " and bt.node_uuid = ? ";
                $sqlCount .= " and bt.node_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between ? and ? ";
                $sqlCount .= " and bt.create_time between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start,$length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $nodeHandler = Xphp::instance('NodeHandler');

        $records["data"] = array();
        foreach ($data as $d){
            $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']);
            //数据验证任务单独获取状态
            if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                $progress = $this->getSureBackupProgress($d['task_status'], $d['task_progress'], false);
            }
            $records["data"][] = array(
                $d['task_name'],
                $ptDes['TASKTYPEDES'][$d['task_type']],
                Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getTimeInterval($d['start_time'], $d['task_status']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $progress,
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
                $this->getJobVmInfo($d['task_uuid'], $d['task_type'])
            );
        }

        //         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到当前所有文件任务信息
     * @param unknown $params
     */
    public function getCurrentFsJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type', 'ba.agent_uuid', "bn.node_uuid", "",
            'bt.task_status', "", 'bri.speed', 'bri.total_object_completed_size', ''
        );
        $utils = Xphp::instance('Utils');
        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型  代理  所在节点  下次运行时间  状态  持续时间  速度  进度  操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, ba.agent_name, ba.hostname,ba.ip, bn.node_uuid,ft.file_archive_flag,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time, unix_timestamp(bri.start_time) start_time
                from bd_running_info bri, bd_agent ba,fs_task ft, bd_task bt left join bd_node bn
                on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and
                     bt.task_uuid = ft.task_uuid and
                     bt.agent_uuid = ba.agent_uuid and
                     bt.delete_flag = 2 and bt.module_type = ? ";
        $sqlCount = "select count(bt.id) as total from bd_running_info bri, bd_agent ba, bd_task bt left join bd_node bn
                    on bt.node_uuid = bn.node_uuid
                    where bt.task_uuid = bri.task_uuid and
                    bt.agent_uuid = ba.agent_uuid and bt.delete_flag = 2 and bt.module_type = ? ";

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['FS']);
        $sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['FS']);

        if($this->checkEmpty($taskName) && !$accurateFlag){
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }


        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bt.user_uuid = ? ";
            $sqlCount .= "and bt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }

        if(!empty($accurateFlag)){
            $search = $params['search'];
            $taskType = intval($search['fs_tasktype']);
            $taskStatus =intval($search['fs_status']);
            $taskName = $search['fs_taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['fs_node'];

            //任务类型
            if(!empty($taskType)){
                $sql .= " and bt.task_type = ? ";
                $sqlCount .= " and bt.task_type = ?";
                $sqlParams = array_merge($sqlParams, array($taskType));
                $sqlCountParams = array_merge($sqlCountParams, array($taskType));
            }
            //任务状态
            if(!empty($taskStatus)){
                $sql .= " and bt.task_status = ? ";
                $sqlCount .= " and bt.task_status = ? ";
                $sqlParams = array_merge($sqlParams, array($taskStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bt.task_name like ? ";
                $sqlCount .= " and bt.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            if(!empty($nodeuuid)){
                $sql .= " and bt.node_uuid = ? ";
                $sqlCount .= " and bt.node_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between ? and ? ";
                $sqlCount .= " and bt.create_time between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start,$length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $nodeHandler = Xphp::instance('NodeHandler');
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        foreach ($data as $d){
            $records["data"][] = array(
                $d['task_name'],
                $ptDes['TASKTYPEDES'][$d['task_type']],
                $this->getfsAgentInfo($d['task_uuid']),
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getTimeInterval($d['start_time'], $d['task_status']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getFileBackupProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], $d['task_uuid']),
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
                'file_archive_flag' => $d['file_archive_flag'] !=2 ? true : false,
            );
        }

        //         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }
    /**
     * 得到当前所有nas任务信息
     * @param unknown $params
     */
    private function getfsAgentInfo($taskuuid){
        $sql = "select ba.agent_name, ba.hostname, ba.ip from bd_task_agent_list bta, bd_agent ba where bta.agent_uuid = ba.agent_uuid and task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $namearr = array();
        $name = "";
        foreach($data as $d) {
            $name = $d["hostname"] .  "(".$d["ip"].")";
            if(!empty($d["agent_name"]) && $d["agent_name"] != $d["ip"]){
                $name = $d["agent_name"] .  "(".$d["ip"].")";
            }
            $namearr[] = $name;
        }
        return $namearr;
    }

    /**
     * 得到当前所有nas任务信息
     * @param unknown $params
     */
    public function getCurrentNasJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type', 'nsr.nas_uuid', "bn.node_uuid", "",
            'bt.task_status', "", 'bri.speed', 'bri.total_object_completed_size', ''
        );
        $utils = Xphp::instance('Utils');

        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型  代理  所在节点  下次运行时间  状态  持续时间  速度  进度  操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, nsr.ip, nsr.share_path,nsr.nas_nickname, bn.node_uuid,nt.file_archive_flag,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time, unix_timestamp(bri.start_time) start_time
                from bd_running_info bri, nas_storage_resource nsr, nas_task nt, bd_task bt left join bd_node bn
                on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and
                    bt.task_uuid = nt.task_uuid and
                     nt.nas_uuid = nsr.nas_uuid and
                     bt.delete_flag = 2 and bt.module_type = ? ";
        $sqlCount = "select count(bt.id) as total from bd_running_info bri, nas_storage_resource nsr, nas_task nt, bd_task bt left join bd_node bn
                    on bt.node_uuid = bn.node_uuid
                    where bt.task_uuid = bri.task_uuid and bt.task_uuid = nt.task_uuid and
                    nt.nas_uuid = nsr.nas_uuid and bt.delete_flag = 2 and bt.module_type = ? ";

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['NAS']);
        $sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['NAS']);

        if($this->checkEmpty($taskName) && !$accurateFlag){
            //按任务名搜索
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }


        $sql .= "and bt.user_uuid = ? ";
        $sqlCount .= "and bt.user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));

        if(!empty($accurateFlag)){
            $search = $params['search'];
            $taskType = intval($search['nas_tasktype']);
            $taskStatus =intval($search['nas_status']);
            $taskName = $search['nas_taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['nas_node'];

            //任务类型
            if(!empty($taskType)){
                $sql .= " and bt.task_type = ? ";
                $sqlCount .= " and bt.task_type = ?";
                $sqlParams = array_merge($sqlParams, array($taskType));
                $sqlCountParams = array_merge($sqlCountParams, array($taskType));
            }
            //任务状态
            if(!empty($taskStatus)){
                $sql .= " and bt.task_status = ? ";
                $sqlCount .= " and bt.task_status = ? ";
                $sqlParams = array_merge($sqlParams, array($taskStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bt.task_name like ? ";
                $sqlCount .= " and bt.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            if(!empty($nodeuuid)){
                $sql .= " and bt.node_uuid = ? ";
                $sqlCount .= " and bt.node_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between ? and ? ";
                $sqlCount .= " and bt.create_time between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $nodeHandler = Xphp::instance('NodeHandler');
        $records["data"] = array();
        foreach ($data as $d){
            if($d['nas_nickname'] == $d['ip']) {
                $nasname = $d['ip'].'('.$d['share_path'].')';
            }else {
                $nasname = $d['ip'].'('.$d['nas_nickname'].')';
            }
            $records["data"][] = array(
                $d['task_name'],
                $ptDes['TASKTYPEDES'][$d['task_type']],
                $nasname,
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getTimeInterval($d['start_time'], $d['task_status']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getFileBackupProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], $d['task_uuid']),
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
                'file_archive_flag' => $d['file_archive_flag'] !=2 ? true : false,
            );
        }

        //         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到当前所有数据库任务信息
     * @param unknown $params
     */
    public function getCurrentDbJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type', 'dt.db_type', "bn.node_uuid", "",
            'bt.task_status', "", 'bri.speed', 'bri.current_transport_size', ''
        );
        $utils = Xphp::instance('Utils');
        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型  数据库类型  代理  所在节点  下次运行时间  状态  持续时间  速度  传输大小  操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, bn.node_uuid,
                		bri.total_object_size, bri.total_object_transport_size, bri.speed, bri.speed_time, dt.db_type, unix_timestamp(bri.start_time) start_time
                from  bd_running_info bri, db_task dt, bd_task bt left join bd_node bn
                on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and
                     bt.task_uuid = dt.task_uuid and
                     bt.delete_flag = 2 and bt.module_type = ? ";
        $sqlCount = "select count(bt.id) as total from  bd_running_info bri, db_task dt, bd_task bt left join bd_node bn
                    on bt.node_uuid = bn.node_uuid
                    where bt.task_uuid = bri.task_uuid and
                     bt.task_uuid = dt.task_uuid and
                     bt.delete_flag = 2 and bt.module_type = ? ";

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['DB']);
        $sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['DB']);

        if($this->checkEmpty($taskName) && !$accurateFlag){
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }

        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bt.user_uuid = ? ";
            $sqlCount .= "and bt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }

        if(!empty($accurateFlag)){
            $search = $params['search'];
            $taskType = intval($search['db_tasktype']);
            $taskStatus =intval($search['db_status']);
            $taskName = $search['db_taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['db_node'];
            $dbtype = intval($search['dbtype']);

            if(!empty($dbtype)){
                $sql .= " and dt.db_type = ?";
                $sqlCount .= " and dt.db_type = ?";
                $sqlParams = array_merge($sqlParams, array($dbtype));
                $sqlCountParams = array_merge($sqlCountParams, array($dbtype));
            }

            //任务类型
            if(!empty($taskType)){
                $sql .= " and bt.task_type = ? ";
                $sqlCount .= " and bt.task_type = ?";
                $sqlParams = array_merge($sqlParams, array($taskType));
                $sqlCountParams = array_merge($sqlCountParams, array($taskType));
            }
            //任务状态
            if(!empty($taskStatus)){
                $sql .= " and bt.task_status = ? ";
                $sqlCount .= " and bt.task_status = ? ";
                $sqlParams = array_merge($sqlParams, array($taskStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bt.task_name like ? ";
                $sqlCount .= " and bt.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            if(!empty($nodeuuid)){
                $sql .= " and bt.node_uuid = ? ";
                $sqlCount .= " and bt.node_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between ? and ? ";
                $sqlCount .= " and bt.create_time between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $nodeHandler = Xphp::instance('NodeHandler');
        $agentHandler = Xphp::instance('AgentHandler');

        $records["data"] = array();
        foreach ($data as $d){
            $currentTransportSize = $utils->calSize($d['total_object_transport_size']);
            $records["data"][] = array(
                $d['task_name'],
                $this->getTaskNameString($d['module_type'], $d['task_type']),
                Xphp::$_config['DB_TYPE_DES'][intval($d['db_type'])],
                $this->getAgentInfo($d['task_uuid']),
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getTimeInterval($d['start_time'], $d['task_status']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getDbTransportSize($currentTransportSize, $d['task_status'], $d['task_type']),
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
            );
        }
        //         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }


    /**
     * 得到当前所有副本|归档任务信息
     * @param unknown $params
     */
    public function getCopyArchiveJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type',  "bsr.storage_uuid", "bsr.node_uuid", "",
            'bt.task_status', "", 'bri.speed', 'bri.total_object_completed_size', ''
        );
        $utils = Xphp::instance('Utils');
        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型    目标存储  下次运行时间  状态  持续时间  速度  进度  操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id,  bsr.storage_nickname, bsr.storage_type, bsr.node_uuid,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time, unix_timestamp(bri.start_time) start_time
                from bd_running_info bri, bd_task bt left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid 
                where bt.task_uuid = bri.task_uuid and
                     bt.delete_flag = 2 and bt.module_type = ? ";
        $sqlCount = "select count(bt.id) as total from bd_task bt left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid  where 
                	 bt.delete_flag = 2 and bt.module_type = ? ";

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']);
        $sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']);

        //任务名
        if($this->checkEmpty($taskName) && !$accurateFlag){
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }

        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bt.user_uuid = ? ";
            $sqlCount .= "and bt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }

        if($accurateFlag){
            $search = $params['search'];
            $taskType = intval($search['copyarchive_tasktype']);
            $taskStatus =intval($search['taskStatus']);
            $taskName = $search['copyarchive_taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];

            //任务类型
            if(!empty($taskType)){
                $sql .= " and bt.task_type = ? ";
                $sqlCount .= " and bt.task_type = ?";
                $sqlParams = array_merge($sqlParams, array($taskType));
                $sqlCountParams = array_merge($sqlCountParams, array($taskType));
            }
            //任务状态
            if(!empty($taskStatus)){
                $sql .= " and bt.task_status = ? ";
                $sqlCount .= " and bt.task_status = ? ";
                $sqlParams = array_merge($sqlParams, array($taskStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($taskStatus));
            }

            //任务名
            if($this->checkEmpty($taskName)){
                $sql .= " and bt.task_name like ? ";
                $sqlCount .= " and bt.task_name like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
            }

            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between ? and ? ";
                $sqlCount .= " and bt.create_time between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
                $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start,$length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $storageHandler = Xphp::instance('StorageHandler');
        $nodeHandler = Xphp::instance('NodeHandler');

        $records["data"] = array();
        foreach ($data as $d){
            $storageName =  $d['storage_nickname'] ? $d['storage_nickname'] : "--";
            $records["data"][] = array(
                $d['task_name'],
                $ptDes['TASKTYPEDES'][$d['task_type']],
                $storageName,
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->getJobNextstarttime($d['task_uuid'], $d['task_status']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getTimeInterval($d['start_time'], $d['task_status']),
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
            );
        }

        //         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到当前所有副本|归档任务信息
     * @param unknown $params
     */
    public function getCurrentCDPJobs($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bt.task_name', 'bt.task_type',  "", "",
            'bt.task_status', "", ''
        );
        $utils = Xphp::instance('Utils');
        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        //         任务名	 任务类型    目标存储  下次运行时间  状态  持续时间  速度  进度  操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, 
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time, unix_timestamp(bri.start_time) start_time
                from bd_task bt, bd_running_info bri
                where bt.task_uuid = bri.task_uuid and
                     bt.delete_flag = 2 and bt.task_type != ? and bt.task_type != ? and bt.module_type in (".Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'].",".Xphp::$_config['MODULE_TYPE']['OEM_FSCDP'].") ";
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_running_info bri
                    where bt.task_uuid = bri.task_uuid and bt.delete_flag = 2 and bt.task_type != ? and bt.task_type != ? and bt.module_type in (".Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'].",".Xphp::$_config['MODULE_TYPE']['OEM_FSCDP'].") ";

        $sqlParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK'], Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        $sqlCountParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK'],  Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);

        if(!empty($taskName) && !$accurateFlag){
            //按任务名搜索
            $sql .= " and bt.task_name like '%" . $taskName . "%' ";
            $sqlCount .= " and bt.task_name like '%" . $taskName . "%' ";
        }

        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bt.user_uuid = ? ";
            $sqlCount .= "and bt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }

        if(!empty($accurateFlag)){
            $search = $params['search'];
            $taskType = intval($search['taskType']);
            $taskStatus =intval($search['taskStatus']);
            $taskName = $search['taskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];

            $sql .= "  and
        			(bt.task_type = ". $taskType ." or ". $taskType ." = '') and
        			(bt.task_status = ". $taskStatus ." or ". $taskStatus ." = '') and
        			bt.task_name like '%" . $taskName . "%' ";
            $sqlCount .= "  and
        			(bt.task_type = ". $taskType ." or ". $taskType ." = '') and
        			(bt.task_status = ". $taskStatus ." or ". $taskStatus ." = '') and
        			bt.task_name like '%" . $taskName . "%' ";


            //如果填了开始时间范围查询
            if(!empty($startTime) && !empty($endTime)){
                $sql .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
                $sqlCount .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
            }
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $storageHandler = Xphp::instance('StorageHandler');
        //远程获取状态

        $records["data"] = array();
        foreach ($data as $d){
            $cdpInfo = $this->getCDPHostInfo($d['task_uuid'], $d['module_type']);

            $operate = Xphp::$_lang['UI_FILE_BAK_GET_REALTIME_DETAIL'];
            $rpc = Xphp::instance('DbRPCHandler');
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'hostip' => $cdpInfo['product_ip'],
                'remoteip' => $cdpInfo['backup_ip'],
            );
            $result = $rpc->getBackupStatus($cdpInfo['backup_ip'], $msgData);
            $data = $dbCDPHandler->checkRPCMsg($operate, $result);

            //             var_dump($result, $data);

            $result = $rpc->getBackupDbStatus($cdpInfo['backup_ip'], $msgData);
            $dbData = $dbCDPHandler->checkRPCMsg($operate, $result);
            $startTime = 0;
            if(!empty($dbData[0])){
                $startTime = strtotime($dbData[0]['btime0']);
            }
            $records["data"][] = array(
                $d['task_name'],
                $this->getTaskNameString($d['module_type'], $d['task_type']),
                $cdpInfo['product_host'],
                $cdpInfo['backup_host'],
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getCDPTimeInterval($startTime, $d['task_status']),
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                date('Y-m-d H:i:s', intval($d['create_time'])),
                $cdpInfo['config']
            );
        }

        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }




    /**
     * 如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * @param unknown $host_name 主机名
     * @param unknown $agent_name 别名
     * @return name
     */
    private function getAgentNamebyName($host_name,$agent_name,$ip){
        if(empty($host_name) && !empty($agent_name)){
            return $agent_name;
        }else if(!empty($host_name) && empty($agent_name)){
            return $host_name;
        }else{
            if($agent_name == $ip){
                return $host_name;
            }else{
                return $agent_name;
            }
        }
    }

    /**
     * 主机保护模块都可以用,得到任务的agent信息  一对多关系
     * @param unknown $task_uuid
     */
    private function getAgentInfo($task_uuid){
        $sql = "select ba.agent_name, ba.hostname, ba.ip from bd_task_agent_list btal,bd_agent ba where btal.agent_uuid = ba.agent_uuid and btal.task_uuid = ?";
        $result = $this->dbSelect($sql,array($task_uuid));
        $info = array();
        if(empty($result)){
            return $info;
        };
        $i = 0;
        foreach ($result as $each){
            $info[] = array(
              'agent_name' => $this->getAgentNamebyName($each['hostname'], $each['agent_name'], $each['ip']),
              'agent_ip' => $each['ip'],
            );
            if($i == 30){
                break;
            };
            $i++;
        }
        return $info;
    }


    /**
     * 获取当前虚拟机任务虚拟机个数和虚拟化中心
     * @param unknown $taskuuid
     * @param unknown $taskType
     * @return array|number[]|mixed[]|string[]|fetchAll()[]
     */
    private function getJobVmInfo($taskuuid, $taskType){
        $info = array();
        if($taskType != Xphp::$_config['TASKTYPE']['BACKUP']) return $info;
        $sql = "select count(vml.machine_id) vm_num, vv.vcenter_ip, nickname from vm_machine_list vml, vm_vcenter vv where vml.vcenter_uuid = vv.vcenter_uuid and vml.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $vmNum = 0;
        $vcenterName = Xphp::$_config['NULLSPACE'];
        if(!empty($data[0]['vcenter_ip'])){
            $vmNum = intval($data[0]['vm_num']);
            $vcenterName = $data[0]['vcenter_ip'] == $data[0]['nickname'] ? $data[0]['vcenter_ip'] : $data[0]['nickname'] . "(" . $data[0]['vcenter_ip'] . ")";
        }

        $info = array(
            'vm_num' => $vmNum,
            'vcenter_name' => $vcenterName
        );
        return $info;
    }

    /**
     * 获取数据库备份传输大小
     * @param string $transportSize
     * @param int $taskStatus
     * @param int $taskType
     * @return unknown|mixed
     */
    private function getDbTransportSize($transportSize, $taskStatus, $taskType){
        $size = Xphp::$_config['NULLSPACE'];
//         if($taskType == Xphp::$_config['TASKTYPE']['DB_RECOVERY']) return $size;
        if($taskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $taskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                //首先任务是在运行状态
                $size = $transportSize;
        }

        return $size;
    }

    /**
     * 获取CDP生成主机和备份主机信息
     * @param string $taskuuid
     * @param int $moduleType
     * @return string[]|mixed[]
     */
    private function getCDPHostInfo($taskuuid, $moduleType){
        $productHost = Xphp::$_config['NULLSPACE'];
        $backupHost = Xphp::$_config['NULLSPACE'];
        $productIP = "";
        $backupIP = "";
        $highConfig = array();
        if($moduleType == Xphp::$_config['MODULE_TYPE']['OEM_DBCDP']){
            //数据库CDP
            $sql = "select cdh.host_type, cdh.host_name, cdh.ip, cdt.config from cdp_db_host cdh, cdp_db_task cdt where cdt.task_uuid = ? and (cdt.product_host_uuid = cdh.host_uuid or cdt.standby_host_uuid = cdh.host_uuid)";
        }else if($moduleType == Xphp::$_config['MODULE_TYPE']['OEM_FSCDP']){
            //文件CDP
            $sql = "select cdh.host_type, cdh.host_name, cdh.ip, cft.config from cdp_db_host cdh, cdp_fs_task cft where cft.task_uuid = ? and (cft.product_host_uuid = cdh.host_uuid or cft.standby_host_uuid = cdh.host_uuid)";
        }
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            foreach ($data as $d){
                if($d['host_type'] == 1){
                    $productHost = $d['host_name'] . "(" . $d['ip'] . ")";
                    $productIP = $d['ip'];
                }else{
                    $backupHost = $d['host_name'] . "(" . $d['ip'] . ")";
                    $backupIP = $d['ip'];
                }
            }
            $taskConfig = json_decode($data[0]['config'], true);
            $highConfig = $taskConfig['highInfo']['log'];
        }

        $info = array(
            'product_host' => $productHost,
            'backup_host' => $backupHost,
            'config' => $highConfig,
            'product_ip' => $productIP,
            'backup_ip' => $backupIP
        );

        return $info;
    }

    /**
     * 得到任务运行的持续时间
     * @param timestamp $startTime
     */
    public function getCDPTimeInterval($startTime, $status){
        if($status == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                $systemHandler = Xphp::instance('SystemHandler');
                $nowTime = $systemHandler->getSystemTime();
                if(!$startTime || $startTime == 0){
                    return Xphp::$_config['TIMESPACE'];
                }
                $intval = $nowTime - $startTime;
                $utils = Xphp::instance('Utils');
                return $utils->secToDayTime($intval);
        }
        return Xphp::$_config['TIMESPACE'];
    }

    /**
     * 获取需要展示的模块任务列表
     * @return boolean[]
     */
    public function getTaskTypeExist(){
        $list = array(
            'vm' => false,
            'fs' => false,
            'db' => false,
            'copy_archive' => false,
            'cdp' => false,
            'vol_cdp' => false,
            'os' => false,
            'nas' => false,
            'dbcdp' => false
        );
        $sql = "select task_uuid, module_type, task_type from bd_task ";
        $sqlParams = array();
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= " where user_uuid = ? ";
            $sqlParams = array($_SESSION['userUUID']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            foreach ($data as $d){
                $type = intval($d['module_type']);
                if($type == Xphp::$_config['MODULE_TYPE']['VM']){
                    $list['vm'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['FS']){
                    $list['fs'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['DB']){
                    $list['db'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
                    $list['copy_archive'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'] || $type == Xphp::$_config['MODULE_TYPE']['OEM_FSCDP']){
                    $list['cdp'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
                    $list['vol_cdp'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['OS']){
                    $list['os'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['NAS']){
                    $list['nas'] = true;
                }else if($type == Xphp::$_config['MODULE_TYPE']['DB_CDP']){
                    $list['dbcdp'] = true;
                }
            }
        }

        return $list;
    }

    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode
     * @return string
     */
    public function getTimepointTypeDes($bakcupMode, $db_type){
        $des ='';
        if($bakcupMode == 4){
            if($db_type == Xphp::$_config['DB_TYPE']['DM'] || $db_type == Xphp::$_config['DB_TYPE']['ORACLE'] || $db_type == Xphp::$_config['DB_TYPE']['OPENGAUSS'] ||
                $db_type == Xphp::$_config['DB_TYPE']['POSTGRE'] || $db_type == Xphp::$_config['DB_TYPE']['ANTDB'] || $db_type == Xphp::$_config['DB_TYPE']['KINGBASE'] ||
                $db_type == Xphp::$_config['DB_TYPE']['UXDB'] || $db_type == Xphp::$_config['DB_TYPE']['HIGHGO'] || $db_type == Xphp::$_config['DB_TYPE']['VASTBASE']){
                //归档日志备份
                $des = Xphp::$_pfdes['BACKUP_MODE_DES'][5];
            }else{
                //日志备份
                $des = Xphp::$_pfdes['BACKUP_MODE_DES'][$bakcupMode];
            }
        }else{
            $des = Xphp::$_pfdes['BACKUP_MODE_DES'][$bakcupMode];
        }
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }
    /**
     * 获取卷cdp任务IO复制模式
     * @param int io_mode
     * @return IO mode string
     */
    private function  getVolCdpTaskIoMode($io_mode){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $IoModeStr = $VolCdpDes['IoReplicationMode'][$io_mode]; //Io复制模式
        return  $IoModeStr;
    }
    /**
     * 获取卷CDP客户主机别名及IP信息
     * @param string agent_uuid
     * @return string agent_info
     */
    private  function getHostInfo($agent_uuid,$tasktype,$taskuuid){
        $sql = "select  ";
        if(Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] == $tasktype || Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'] == $tasktype){  //备份
            $sql = "SELECT hostname,ip,os_type FROM bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($agent_uuid));
            $hostname = $data[0]['hostname'];
            $agentIp = $data[0]['ip'];
            $osType = $data[0]['os_type'];

            $hostInfo = $hostname."(".$agentIp.")";
            $agentInfo = array(
                "host_info" => $hostInfo,
                "agent_ip" => $agentIp,
                'os_type' => $osType,
            );
            
        }else if(Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] == $tasktype || Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'] == $tasktype){  //恢复，接管
            $agentInfo = $this->getVolCdpBackupAgentInfo($agent_uuid);
        }else if(Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = "select takeover_agent_type from cdp_vol_task_takeover_info where task_uuid = ?";
            $data = $this->dbSelect($sql,array($taskuuid));
            $takeoverAgentType = $data[0]['takeover_agent_type'];
            if($takeoverAgentType == Xphp::$_config['AGENT_TYPE']['NORMAL']){
                $agentInfo = $this->getVolCdpBackupAgentInfo($agent_uuid);
            }else if($takeoverAgentType == Xphp::$_config['AGENT_TYPE']['TEMP_AGENT']){
                $tempSql = "select name,os_type from vm_emd where uuid = ?";
                $data = $this->dbSelect($tempSql, array($agent_uuid));
                $hostInfo = $data[0]['name']."(".Xphp::$_lang['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC'].")";
                $agentInfo = array(
                    "host_info" => $hostInfo,
                    "agent_ip" => Xphp::$_lang['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC'],
                    'os_type' => $data[0]['os_type'],
                );
            }
        }
        return $agentInfo;
    }
    /**
     * 从备份集中获取agent信息 通过master uuid
     * @param $agentuuid
     * @return void
     */
    private function getVolCdpBackupAgentInfo($agent_uuid){
        $sql = "select master_agent_detail from cdp_vol_backup_agent where master_agent_uuid  = ?";
        $data = $this->dbSelect($sql, array($agent_uuid));
        try {
            $master_agent_detail = json_decode($data[0]['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agentIp = $master_agent_detail->ip;
            $osType = $master_agent_detail->os_type;
        }catch (Throwable $e) {
            $hostname = "--";
            $agentIp = "--";
            $osType = "--";
        }
        $hostInfo = $hostname."(".$agentIp.")";
        $agentInfo = array(
            "host_info" => $hostInfo,
            "agent_ip" => $agentIp,
            'os_type' => $osType,
        );
        return $agentInfo;
    }
    /**
     * 任务详情 : 得到主机基础信息
     * $params task_uuid
     */
    public function getOSBasicInfo($params){
        //任务名,任务类型,任务状态,任务总容量,已处理容量,开始时间,持续时间,备份节点,存储设备
        //创建/修改时间,下次开始时间,完全备份,增量备份,差异备份,加密传输,限速策略
        //操作系统类型
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,bt.task_orchestration_plan_flag,bt.user_uuid, 
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.ignore_resource_limiting_flag,
                	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
                	   bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, 
                       ol.reparted_flag,ol.os_config,
                       obt.os_type,
                       brs.network_retry_times,brs.network_retry_interval,brs.op_retry_times,brs.op_retry_interval,brs.task_retry_object,brs.task_retry_times, brs.task_retry_interval  
                       from bd_task bt 
                       left join bd_user bu on bt.user_uuid = bu.user_uuid 
                       left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
                       left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
                       left join os_list ol on bt.task_uuid = ol.task_uuid 
                       left join os_backup_timepoint obt on obt.timepoint_uuid = ol.timepoint_uuid
                       left join bd_retry_strategy brs on bt.task_uuid = brs.task_uuid
                       where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        $vmhandler = Xphp::instance('Vmhandler');
        foreach ($data as $d){
            $list = json_decode($d['os_config'],true);
            $basicInfo = array(
                //任务名
                'taskName' => $d['task_name'],
                'user_uuid' => $d['user_uuid'],
                //             'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                //任务类型
                'taskType' => $this->getBasicInfoOsTaskType($d['task_type']),
                //任务状态
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                //任务总容量
                'totalSize' => $utils->calSize($d['total_object_size']),
                //已处理容量
                'currentSize' => $utils->calSize($d['total_object_completed_size']),
                //开始时间
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                //持续时间
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                //节点信息
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid'], $taskUUID),
                //时间策略信息
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                //保留策略
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                //限速信息
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                //创建/修改时间
                'createTime' => $this->parseDate($d['create_time']),
                //下次开始时间
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                //得到高级策略
                'highInfo' => $this->gethighOSinfo($taskUUID),
                'flag' => true,
                //任务类型int
                'task_type_num' => $d['task_type'],
                //传输策略
                'transportStrategy' => $this->gettransportOSinfo($d['strategy_id']),
                //任务状态int
                'status_num' => $d['task_status'],
                //任务进度
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                //任务进度百分比
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                //网络传输
                'networkFlag' => $this->getNetworkShowFlag($taskUUID, $d['module_type'], $d['task_type']),//检查显示传输网络标志
                //手动启动
                'timeStrategyBackupType' =>  $vmhandler->getTimeStrategyBackupType($d['strategy_id']),


                'reparted_flag' => $d['reparted_flag'],  //重建分区(只有恢复有)
                'repair_linux_flag' => $list['repair_linux_flag'], //引导恢复(只有恢复有)
				'osType' => $d['os_type'],           //操作系统类型
                'task_orchestration_plan_flag' => $utils->parseFlagToBool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $this->getTapeGroupStrategy($d['storage_uuid']) ?? '',
                'storage_type' => $this->getOsStorageTypeByTaskId($taskUUID, $d['task_type']),
                //重试策略
                'retry_strategy' => $this->groupRetryStrategyInfo($d),
                'ignore_resource_limiting_flag' => $utils->parseFlagToBool($d['ignore_resource_limiting_flag']),
            );
        }
        return json_encode($basicInfo);

    }

    /**
     * 任务详情-根据任务uuid获取存储类型--只用于恢复任务
     * @param string $taskUUID 任务uuid
     * @param string $taskType 任务类型
     * @return string 存储类型
     */
    private function getOsStorageTypeByTaskId($taskUUID,$taskType){
        $storageType = '';
        if ($taskType == Xphp::$_config['TASKTYPE']['BACKUP']) {
            return $storageType;
        }
        $sql = "select bsr.storage_type from bd_storage_resource bsr, bd_backup_timepoint bbt, os_list ol where 
        ol.task_uuid = ? and ol.timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }

    /**
     * 得到主机传输策略
     * @return NULL[]
     */
    private function gettransportOSinfo($strategyid){
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method, bts.network_uuid, bts.network_pool_uuid,
                bnnp.network_pool_nickname from bd_transport_strategy bts 
                left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid 
                left join bd_node_network_pool bnnp on bts.network_pool_uuid = bnnp.network_pool_uuid where strategy_id = ? ";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
        $info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['compress_flag']);
        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['network_uuid'] = $data[0]['network_uuid'];
        $info['network_pool_uuid'] = $data[0]['network_pool_uuid'];
        $info['network_pool_nickname'] = $data[0]['network_pool_nickname'];
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];
        return $info;

    }

    /**
     * 主机任务详情得到任务类型的描述
     * @param unknown $task_type
     */
    private function getBasicInfoOsTaskType($task_type){
        $strDes = "";
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        if($task_type == Xphp::$_config['TASKTYPE']['OS_BACKUP']){
            $strDes = Xphp::$_lang['WEB_OS_HOST_BACKUP'];
        }else if($task_type == Xphp::$_config['TASKTYPE']['OS_RECOVERY']){
            $strDes = Xphp::$_lang['WEB_OS_HOST_RECOVERY'];
        }
        return $strDes;

    }

    /**
     * 得到主机相关部分高级选项
     * task_uuid 任务uuid
     */
    private function gethighOSinfo($task_uuid){
        $sql = "select bt.thread_num, ot.cbt_flag, ot.serial_snapshot_flag,ot.valid_data_flag,ot.silent_snapshot_flag 
from bd_task bt,os_task ot where bt.task_uuid = ot.task_uuid and bt.task_uuid = ?";
        $result = $this->dbSelect($sql,array($task_uuid));
        $highinfo = array();
        foreach ($result as $d){
            $highinfo = array(
                'thread_num' => $d['thread_num'],
                'cbt_flag' => $d['cbt_flag'],
                'serial_snapshot_flag' => $d['serial_snapshot_flag'],
                'valid_data_flag' => $d['valid_data_flag'],
                'silent_snapshot_flag' => $d['silent_snapshot_flag'],
            );
        }
        return $highinfo;
    }


    /**
     * 主机任务详情-系统信息列表
     * @param unknown $params
     */
    public function getDetailsOS($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $keyword = $params['keyword'];
        $sql = "select ol.os_name, ol.total_size as ol_total_size, ol.task_status, ol.transport_size as ol_transport_size, ol.backup_mode,ol.agent_ip, ol.os_config, ol.timepoint_uuid, ol.write_size as ol_write_size, ol.valid_size as ol_valid_size,ol.agent_uuid,     
                bri.total_object_transport_size, bri.current_object_write_size, bri.speed, bri.speed_time, bri.current_object_transport_size, bri.current_object_total_size, bri.current_object_valid_size,
                bt.task_status as bd_task_status, bt.task_type
                from os_list ol
                left join bd_running_info bri
                on ol.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = ol.task_uuid
                left join os_task ot 
                on ol.task_uuid = ot.task_uuid where
                ol.task_uuid = ?";
        $sqlParams = array($taskUUID);
        if(!empty($keyword)){
            $sql .= " and ol.os_name like ?";
            $sqlParams = array_merge($sqlParams, array('%' .$keyword. '%'));
        }
        $data = $this->dbSelect($sql, $sqlParams);

        $records = array();
        $i = 1;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        $osHandler = Xphp::instance('OsHandler');
        $agentList = $osHandler->getAllAgentList();
        foreach ($data as $d){
            //这里显示要根据任务状态显示,状态为已完成或停止等状态 显示ol_list里面的数据大小
            //状态为等待状态时大小统一显示--
            //状态为正在运行状态时显示bd_running_info里面的数据大小

            //获取名字
            $name = $osHandler->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']);

            //获取ol_list中的状态
            $os_task_status = $d['task_status']; //主机状态
            $bd_task_status = $d['bd_task_status']; //任务状态
            if($d['task_type'] == Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY_MOTION']){
                $records["data"][] = array(
                    //编号
                    $i++,
                    //系统名称
                    $name,
                    //备份类型
                    $this->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']),
                    //主机大小
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_total_size'])),
                    //有效数据大小
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_valid_size'])),
                    //传输大小
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_transport_size'])),
                    //写入大小
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_write_size'])),
                    //传输速度
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time'])),
                    //传输进度
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $this->getOSPercent($d['current_object_valid_size'], $d['current_object_transport_size'])),
                    //状态
                    $this->getOSDisplayStr($bd_task_status, $os_task_status, $ptDes['TASKSTATUSDES'][$d['task_status']]),
                    //其他详情
                    $this->getVMDetailsInfo($d, $taskUUID),
                );
            }else{
                if($bd_task_status == Xphp::$_config['TASKSTATUS']['RUNNING'] || $bd_task_status == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                    if($os_task_status == Xphp::$_config['TASKSTATUS']['RUNNING']){

                        $records["data"][] = array(
                            //勾选框
                            '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                            //编号
                            $i++,
                            //系统名称
                            $name,
                            //备份类型
                            $this->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']),
                            //主机大小
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_total_size'])),
                            //有效数据大小
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_valid_size'])),
                            //传输大小
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_transport_size'])),
                            //写入大小
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['current_object_write_size'])),
                            //传输速度
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time'])),
                            //传输进度
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $this->getOSPercent($d['current_object_valid_size'], $d['current_object_transport_size'])),
                            //状态
                            $this->getOSDisplayStr($bd_task_status, $os_task_status, $ptDes['TASKSTATUSDES'][$d['task_status']]),
                            //其他详情
                        $this->getVMDetailsInfo($d, $taskUUID),
                    );
                }else if($os_task_status == Xphp::$_config['TASKSTATUS']['UNKNOWN']){
                        $records["data"][] = array(
                            //勾选框
                            '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                            //编号
                            $i++,
                            //系统名称
                            $name,
                            //备份类型
                            Xphp::$_config['NULLSPACE'],
                            //主机大小
                            Xphp::$_config['NULLSPACE'],
                            //有效数据大小
                            Xphp::$_config['NULLSPACE'],
                            //传输大小
                            Xphp::$_config['NULLSPACE'],
                            //写入大小
                            Xphp::$_config['NULLSPACE'],
                            //传输速度
                            Xphp::$_config['NULLSPACE'],
                            //传输进度
                            Xphp::$_config['NULLSPACE'],
                            //状态
                            Xphp::$_config['NULLSPACE'],
                            //其他详情
                            $this->getVMDetailsInfo($d, $taskUUID),
                        );
                    }else{
                            $records["data"][] = array(
                                //勾选框
                                '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                                //编号
                                $i++,
                                //系统名称
                                $name,
                                //备份类型
                                $this->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']),
                                //主机大小
                                $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['ol_total_size'])),
                                //有效数据大小
                                $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['ol_valid_size'])),
                                //传输大小
                                $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['ol_transport_size'])),
                                //写入大小
                                $this->getOSDisplayStr($bd_task_status, $os_task_status, $utils->calSize($d['ol_write_size'])),
                                //传输速度
                                Xphp::$_config['NULLSPACE'],
                                //传输进度
                                $this->getOSDisplayStr($bd_task_status, $os_task_status, $this->getOSPercent($d['ol_valid_size'], $d['ol_transport_size'])),
                                //状态
                                $this->getOSDisplayStr($bd_task_status, $os_task_status, $ptDes['TASKSTATUSDES'][$d['task_status']]),
                                //其他详情
                                $this->getVMDetailsInfo($d, $taskUUID),
                            );
                    }
                }else{
                    $records["data"][] = array(
                        //勾选框
                        '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                        //编号
                        $i++,
                        //系统名称
                        $name,
                        //备份类型
                        Xphp::$_config['NULLSPACE'],
                        //主机大小
                        Xphp::$_config['NULLSPACE'],
                        //有效数据大小
                        Xphp::$_config['NULLSPACE'],
                        //传输大小
                        Xphp::$_config['NULLSPACE'],
                        //写入大小
                        Xphp::$_config['NULLSPACE'],
                        //传输速度
                        Xphp::$_config['NULLSPACE'],
                        //传输进度
                        Xphp::$_config['NULLSPACE'],
                        //状态
                        Xphp::$_config['NULLSPACE'],
                        //其他详情
                        $this->getVMDetailsInfo($d, $taskUUID),
                    );
                }

            }
        }
        return  json_encode($records);
    }


    /**
     * 根据任务状态和单个主机状态一起判断是否显示数据还是显示--
     * @param unknown $bd_task_status  任务状态
     * @param unknown $os_task_status  主机状态
     * @param unknown $Data 数据
     */
    public function getOSDisplayStr($bd_task_status,$os_task_status,$Data){
        return $Data;
        if($bd_task_status == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $bd_task_status == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //主机在任务中
                if($os_task_status == Xphp::$_config['TASKSTATUS']['RUNNING'] ){
                    //虚拟机是运行状态,显示bd_running_info的完成大小
                    return $Data;
                }else{
                    //虚拟机不在运行状态,显示vm_machine_list的完成大小
                    return Xphp::$_config['NULLSPACE'];
                }
        }else{
            return Xphp::$_config['NULLSPACE'];
        }

    }





    /**
     * 获取传输百分比
     * @param unknown $valid_size 分母
     * @param unknown $transport_size 分子
     */
    public function getOSPercent($valid_size, $transport_size){
        $valid_size = intval($valid_size);
        $transport_size = intval($transport_size);
        if($valid_size == $transport_size){
            //如果相等
            if(empty($valid_size)){
                return '0%';
            }else{
                return '100%';
            }

        }
        if($transport_size > $valid_size){
            //如果分子比分母大
            return Xphp::$_config['NULLSPACE'];
        }
        $utils = Xphp::instance('Utils');
        return $utils->calPercent($valid_size, $transport_size);
    }





    /**
     * 任务详情: 得到主机详细任务历史任务
     * @param unknown $params
     */
    public function getOSDetailsHistory($params){
        $taskUUID = $params['uuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $this->paramsCheck($taskUUID);
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('','current_mode', 'error_code', 'total_object_size', 'total_object_completed_size',
            'total_object_transport_size', 'total_object_write_size','start_time', 'finish_time');

        $sql = "select task_type, module_type, submodule_type, current_mode, error_code, details, total_object_size, total_object_transport_size,total_object_write_size,
        average_speed,total_object_completed_size, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ?
         order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
        $sqlCountParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $utils = Xphp::instance('Utils');
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            $details = array();
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                $i++,
                //任务类型
                $this->getHistoryTaskType($d['task_type'], $d['current_mode']),
                //任务状态
                $this->getHistoryJobResultDes($d['error_code']),
                //总大小
                $utils->calSize($d['total_object_size'], true),
                //处理大小
                $utils->calSize($d['total_object_completed_size'], true),
                //传输大小
                $utils->calSize($d['total_object_transport_size'], true),
                //写入大小
                $utils->calSize($d['total_object_write_size'], true),
                //开始时间
                $this->parseDate($d['start_time']),
                //结束时间
                $this->parseDate($d['finish_time']),
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
                    'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type'])
                ),
            );
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }


    /**
     * 得到备份的详情信息
     * @param unknown $d taskuuid
     *
     */
    public function getOSBackupDetailsInfo($d, $taskUUID){
        //处理主机备份的分区信息
        $os_config = $d['os_config'];
        $volList = json_decode($os_config,true);
        $volList = $volList['backup_info_list'];
        $infoList  = array();
        $utils = Xphp::instance('Utils');
        foreach ($volList as $each){
            $infoList[] = array(
                'name' => empty($each['name']) ? '--' : $each['name'],
                'mount_path' => empty($each['mount_path'])? '--' : $each['mount_path'],
                'total_size' => $utils->calSize($each['total_size'],true),
            );
        }
        array_multisort($infoList);
        $info = array(
            "task_type_num" => intval($d['task_type']),
            "msgVol" => $infoList,
        );
        return $info;
    }


    /**
     * 得到恢复的详情信息
     * @param unknown $d
     * @param unknown $taskUUID
     */
    public function getOSRecoveryDetailsInfo($d, $taskUUID){
        //只有回复才有时间点信息
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $os_config = $d['os_config'];
        $volList = json_decode($os_config,true);
        $volList = $volList['recovery_info_list'];
        $infoList  = array();
        $utils = Xphp::instance('Utils');
        foreach ($volList as $each){
            $infoList[] = array(
                "timepoint" => $timepointInfo['timepoint'],
                'source_mount_path' => empty($each['source_mount_path']) ? '--' : $each['source_mount_path'],
                'source_name' => empty($each['source_name'])? '--' : $each['source_name'],
                'transfer_size' => $utils->calSize($each['transfer_size'],true),
            );
        }
        array_multisort($infoList);
        $info = array(
            "task_type_num" => intval($d['task_type']),
            "msgVol" => $infoList,
            "timepoint" => $timepointInfo['timepoint'],
        );
        return $info;
    }
      /**
     * 得到操作系统迁移的详情信息
     * @param unknown $d
     * @param unknown $taskUUID
     */
    public function getOSMotionDetailsInfo($d,$taskuuid){
        //获取迁移源主机和迁移目标主机
        $sql = "select obt.agent_uuid as sourceid, ol.agent_uuid as targetid from os_list ol, os_backup_timepoint obt where
        ol.timepoint_uuid =  obt.timepoint_uuid and
        ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $sourceinfo = $this->getOSHostInfo($data[0]['sourceid']);
        $targetinfo = $this->getOSHostInfo($data[0]['targetid']); //目标主机只取第一个原主机下的
        $sourcehost =  $this->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")";
        $targethost = $this->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]) . "(" . $targetinfo['ip'] . ")";
        //时间点 迁移源主机 迁移目标主机
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $info = array(
            "taskname" => $timepointInfo['taskname'],
            "timepoint" => $timepointInfo['timepoint'],
            "source_name" => $sourcehost,
            "target_name" => $targethost
        );
        return  $info;
    }

    /**
     * 操作系统任务详情-操作系统列表-对单个主机进行操作相关
     * @param unknown $params
     */
    public function startOSJob($params){
        //获取备份模式
        $backup_mode = $params['backup_mode'];
        //获取任务uuid
        $task_uuid = $params['taskuuid'];
        //获取主机列表
        $os_uuid_list = $params['os_uuids'];
        //参数检查
        $this->paramsCheck($backup_mode,$task_uuid,$os_uuid_list);
        //以下参数置位空
        $time_strategy_id = 0;
        $auto_start_flag = Xphp::$_config['FLAG']['UNSET'];
        //定义操作码
        $opName = 'BD_TASK_OP_BACKUP_START';
        $operate = $this->getUnifyOpcodeDes($opName);
        $this->checkOpPermission($task_uuid, $operate);
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => $auto_start_flag,
            "os_uuid_list" => $os_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }


    /**
     * 操作系统任务详情-操作系统列表-对单个主机进行操作相关-删除
     * @param unknown $params
     */
    public function deleteSelectOS($params){
        //获取任务uuid
        $task_uuid = $params['taskuuid'];
        //获取主机列表
        $os_uuid_list = $params['os_uuids'];
        //参数检查
        $this->paramsCheck($task_uuid,$os_uuid_list);
        //定义操作码
        $opName = 'OS_PRIVATE_TASK_OP_CODE_DELETE_OS_LIST';
        $msg = array(
            'task_uuid' => $task_uuid,
            "os_uuid_list" => $os_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
     /**
     * 文件任务详情-主机列表-对单个主机进行操作相关
     * @param unknown $params
     */
    public function startFSJob($params){
        //获取备份模式
        $backup_mode = $params['backup_mode'];
        //获取任务uuid
        $task_uuid = $params['taskuuid'];
        //获取主机列表
        $fs_uuid_list = $params['fs_uuids'];
        //参数检查
        $this->paramsCheck($backup_mode,$task_uuid,$fs_uuid_list);
        //以下参数置位空
        $time_strategy_id = 0;
        $auto_start_flag = Xphp::$_config['FLAG']['UNSET'];
        //定义操作码
        $opName = 'BD_TASK_OP_BACKUP_START';
        $operate = $this->getUnifyOpcodeDes($opName);
        $this->checkOpPermission($task_uuid, $operate);
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => $auto_start_flag,
            "fs_uuid_list" => $fs_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }


    /**
     * 文件任务详情-主机列表-对单个主机进行操作相关-删除
     * @param unknown $params
     */
    public function deleteSelectFs($params){
        //获取任务uuid
        $task_uuid = $params['taskuuid'];
        //获取主机列表
        $fs_uuid_list = $params['fs_uuids'];
        //参数检查
        $this->paramsCheck($task_uuid,$fs_uuid_list);
        //定义操作码
        $opName = 'OS_PRIVATE_TASK_OP_CODE_DELETE_OS_LIST';
        $msg = array(
            'task_uuid' => $task_uuid,
            "fs_uuid_list" => $fs_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }















    //-----------------------------------------------------------------------
    /**
     * 获取数据验证任务高级策略
     * @param unknown $taskuuid
     * @return boolean[]|number[]|NULL[]|unknown[]
     */
    private function getVerifyModeStrategy($taskuuid, $utils){
        $sql = "select ssb.ping_warn_flag, ssb.heartbeat_warn_flag, ssb.screenshot_warn_flag, ssbiv.nfs_server_ip, ssb.virtual_lab_uuid, ssb.hypervisor_type, ssb.limit_boot_vm_num, ssb.automatic_verifitied_flag
                 from sr_sure_backup ssb, sr_sure_backup_instant_vm ssbiv where ssbiv.task_uuid = ssb.task_uuid and ssb.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $virtual_lab_uuid = $data[0]['virtual_lab_uuid'];
        //获取虚拟实验室信息
        $sqlLab = "select network_map, virtual_lab_name, proxy_name, proxy_ip from sr_virtual_lab where virtual_lab_uuid = ?";
        $dataLab = $this->dbSelect($sqlLab, array($virtual_lab_uuid));
        $labName = $dataLab[0]['virtual_lab_name'];
        $proxyName = $dataLab[0]['proxy_name']."(".$dataLab[0]['proxy_ip'].")";
        $networkList = array();
        if (!empty($dataLab[0]['network_map'])) {
            $networkMap = json_decode($dataLab[0]['network_map'],true);
            foreach ($networkMap['map_list'] as $net){
                //生产网络
                $productInfo = array(
                    'name' => $net['product_network_network_name'],
                    'netmask' => $net['product_network_netmask'],
                    'gateway' => $net['product_network_gateway']
                );
                //隔离网络
                $isolatedInfo = array(
                    'name' => $net['isolate_network_network_name'],
                    'netmask' => $net['isolate_network_netmask'],
                    'gateway' => $net['isolate_network_gateway']
                );

                $networkList[] = array(
                    'productInfo' => $productInfo,
                    'isolatedInfo' => $isolatedInfo
                );
            }
        }

        $mode = array(
            'hypervisor' => $data[0]['hypervisor_type'],
            'limit_boot_vm_num' => intval($data[0]['limit_boot_vm_num']),
            'backup_system_ip' => $data[0]['nfs_server_ip'],
            'verify_type_des' => intval($data[0]['automatic_verifitied_flag']) == 1 ? Xphp::$_lang['UI_VERIFY_MANUAL'] : Xphp::$_lang['UI_VERIFY_AUTOMATIC'],
            'lab_name' => $labName,
            'proxy_name' => $proxyName,
            'network_list' => $networkList,
            'verify_type' => intval($data[0]['automatic_verifitied_flag']),
            'ping_warn_flag' => $utils->parseFlagToBool(intval($data[0]['ping_warn_flag'])),
            'heartbeat_warn_flag' => $utils->parseFlagToBool(intval($data[0]['heartbeat_warn_flag'])),
            'screenshot_warn_flag' => $utils->parseFlagToBool(intval($data[0]['screenshot_warn_flag'])),

        );
        return $mode;
    }

    /**
     * 获取数据验证任务详情虚拟机列表
     * @param unknown $params
     * @return string
     */
    public function getDetailsVMVerify($params){
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select vm.original_ip, vm.advance_vm_config, vm.target_vcenter_uuid, vm.orig_vm_uuid, vm.new_vm_name, vm.orig_vm_name, vm.task_status, vm.ping_test_flag, vm.heartbeat_flag, vm.print_screen_flag,vm.max_boot_time,
                        bt.task_status as bd_task_status, bt.task_type, vv.hypervisor_type
                from sr_sure_backup_instant_vm vm
                left join bd_task bt
                on bt.task_uuid = vm.task_uuid
                left join vm_vcenter vv
                on vm.target_vcenter_uuid = vv.vcenter_uuid
                where vm.task_uuid = ? group by vm.orig_vm_uuid order by vm.vm_id asc";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id'. $d['orig_vm_uuid'] .'" value="'. $d['orig_vm_uuid'] .'">',
                $i++,
                $d['orig_vm_name'],
                $this->getVerifyStatusDes($d['bd_task_status'], $d['task_status'], intval($d['ping_test_flag']),$vmDes),
                $this->getVerifyStatusDes($d['bd_task_status'], $d['task_status'], intval($d['heartbeat_flag']),$vmDes),
                $this->getVerifyStatusDes($d['bd_task_status'], $d['task_status'], intval($d['print_screen_flag']),$vmDes),
                $this->getVMVerifyStatus($d['bd_task_status'], $d['task_status']),
                $this->getVMDetailsInfo($d, $taskUUID),
                $d['target_vcenter_uuid'],
                intval($d['hypervisor_type']),
                intval($d['bd_task_status']),
                intval($d['task_status'])
            );
        }


        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }

    /**
     * 获取数据验证虚拟机详情信息
     * @param unknown $d
     * @param unknown $taskuuid
     */
    private function getVerifyDetailsInfo($d, $taskuuid){
        $utils = Xphp::instance('Utils');
        $config = json_decode($d['advance_vm_config'],true);
        $socket = intval($config['cpu_socket']);
        $cores = intval($config['cores_per_socket']);
        $memory = $utils->calSize(intval($config['vm_memory']));
        if($socket == 0){
            $socket = Xphp::$_lang['UI_PLATFORM_ORIGINNAL_CONF'];
        }
        if($cores == 0){
            $cores = Xphp::$_lang['UI_PLATFORM_ORIGINNAL_CONF'];
        }
        if($memory == 0){
            $memory = Xphp::$_lang['UI_PLATFORM_ORIGINNAL_CONF'];
        }
        $info = array(
            "new_vm_name" => $d['new_vm_name'],
            "max_boot_time" => intval($d['max_boot_time']),
            "cpu_socket" => $socket,
            "cores_per_socket" => $cores,
            "memory" => $memory,
            "vm_status" => intval($d['task_status']),
            "ping_test_flag" => intval($d['ping_test_flag']),
            "heartbeat_flag" => intval($d['heartbeat_flag']),
            "print_screen_flag" => intval($d['print_screen_flag']),
            "vm_uuid" => $d['orig_vm_uuid'],
            "original_ip" => !empty($d['original_ip']) ? $d['original_ip']: Xphp::$_config['NULLSPACE']
        );
        return $info;
    }

    /**
     * 获取数据验证任务详情历史显示
     * @param unknown $params
     * @return string
     */
    public function getVerifyDetailsHistory($params){
        $taskUUID = $params['uuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $this->paramsCheck($taskUUID);
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', "", 'task_type', 'error_code', 'start_time', 'finish_time'
        );
        $sql = "select id,task_type, module_type, submodule_type, current_mode, error_code, details, 
        unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ?
        order by $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select count(id) as total from bd_history_task where task_uuid = ? ";

        $sqlParams = array($taskUUID, $start, $length);
        $sqlCountParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $utils = Xphp::instance('Utils');
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                $i++,
                $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                $this->getHistoryJobResultDes($d['error_code']),
                $this->parseDate($d['start_time']),
                $this->parseDate($d['finish_time']),
                Xphp::$_lang['UI_PLATFORM_VERIFY_REPORT'],
                array(
                    'level'=>$this->getJobStatusShowLevel($d['error_code']),
                    'popover' => $this->getJobStatusShowPopover($d['error_code'])
                ),
                array(
//                     'info' => $this->historyTaskDetailsHandler(intval($d['module_type']), intval($d['task_type']), $details, $d),
                    'taskType' => intval($d['task_type']),
                    'history_id' => $d['id']
                ),
            );
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 获取数据验证虚拟机运行信息
     * @param unknown $params
     */
    public function getVerifyVmRunInfo($params){
        $taskuuid = $params['uuid'];
        $sql = "select ssbiv.orig_vm_uuid, ssbiv.orig_vm_name, ssbiv.new_vm_name, ssbiv.task_status, ssbiv.ping_test_flag,
       ssbiv.heartbeat_flag, ssbiv.print_screen_flag, ta.console_url, ssbiv.new_vm_uuid
from sr_sure_backup_instant_vm ssbiv left join vm_emd ta on ssbiv.new_vm_uuid = ta.uuid and ta.status = 1 
where ssbiv.task_uuid = ? and ssbiv.task_status != ? 
order by ssbiv.vm_id asc ";
        $data = $this->dbSelect($sql, array($taskuuid, Xphp::$_config['VERIFY_STATUS']['SKIP']));
        $list = array();
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        foreach ($data as $d){
            $verifyDes = Xphp::$_config['NULLSPACE'];
            $color = array();
//             if(intval($d['task_status']) == Xphp::$_config['VERIFY_STATUS']['VERIFY']){
                //获取验证中虚拟机信息
                if($d['ping_test_flag'] == Xphp::$_config['VERIFY_STATUS']['VERIFY']){
                    $verifyDes = "Ping"."<br>"."(".$vmDes['VERIFY_FUNC_STATUS'][intval($d['ping_test_flag'])].")";
                }else if($d['heartbeat_flag'] == Xphp::$_config['VERIFY_STATUS']['VERIFY']){
                    $verifyDes = Xphp::$_lang['UI_PLATFORM_HEART_BEAT']."<br>"."(".$vmDes['VERIFY_FUNC_STATUS'][intval($d['heartbeat_flag'])].")";
                }else if($d['print_screen_flag'] == Xphp::$_config['VERIFY_STATUS']['VERIFY']){
                    $verifyDes = Xphp::$_lang['UI_PLATFORM_SCREEN_SHOT']."<br>"."(".$vmDes['VERIFY_FUNC_STATUS'][intval($d['print_screen_flag'])].")";
                }
                $color[] = $this->getVerifyColor(intval($d['ping_test_flag']));
                $color[] = $this->getVerifyColor(intval($d['heartbeat_flag']));
                $color[] = $this->getVerifyColor(intval($d['print_screen_flag']));
//             }
            if (!empty($d['console_url'])) {
                $newconsoleurl = str_replace('0.0.0.0:6080', $_SERVER['HTTP_HOST'] . '/web_console', $d['console_url']);
				 $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($newconsoleurl);
            } else {
                $newconsoleurl = '';
            }
            $list[] = array(
                'orig_vm_uuid' => $d['orig_vm_uuid'],
                'orig_vm_name' => $d['orig_vm_name'],
                'new_vm_name' => $d['new_vm_name'],
                'task_status' => $d['task_status'],
                'ping_test_flag' => $d['ping_test_flag'],
                'heartbeat_flag' => $d['heartbeat_flag'],
                'print_screen_flag' => $d['print_screen_flag'],
                'ping_des' => $vmDes['VERIFY_FUNC_STATUS'][intval($d['ping_test_flag'])],
                'heartbeat_des' => $vmDes['VERIFY_FUNC_STATUS'][intval($d['heartbeat_flag'])],
                'print_screen_des' => $vmDes['VERIFY_FUNC_STATUS'][intval($d['print_screen_flag'])],
                'current_verify_des' => $verifyDes,
                'color' => $color,
                'console_url' => $newconsoleurl,
                'new_vm_uuid' => $d['new_vm_uuid'],
            );
        }

        return json_encode($list);
    }

    /**
     * 启动卷CDP接管任务
     * @param unknown $params
     */
    public function startVolTakeover($params){

    }

    /**
     * 停止接管
     * @param unknown $params
     */
    public function stopVolCdpTakeover($params){
        //检查是否有操作权限
        $operate = $this->getUnifyOpcodeDes('BD_TASK_OP_TAKEOVER_STOP');
        $this->checkOpPermission($params['uuid'], $operate);
        $volCDPHandler = Xphp::instance('VolCDPTaskHandler');
        return $volCDPHandler->stopTakeoverJob($params);
    }

    /**
     * 启动任务回切
     * @param unknown $params
     */
    public function startVolCdpTaskCatback($params){
        $operate = $this->getUnifyOpcodeDes('VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START');
        $this->checkOpPermission($params['uuid'], $operate);
        $volCDPHandler = Xphp::instance('VolCDPTaskHandler');
        return $volCDPHandler->startTaskCatBack($params);
    }

    /**
     * 创建手动标签
     * @param unknown $params
     */
    public function createVolCdpLablePoint($params){
        $operate = $this->getUnifyOpcodeDes('VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START');
        $this->checkOpPermission($params['uuid'], $operate);
        $volCDPHandler = Xphp::instance('VolCDPTaskHandler');
        return $volCDPHandler->createLablePoint($params);
    }

    /**
     * 获取数据验证任务进度
     * @param int $taskStatus
     * @param int $progress
     * @param boolean $percentFlag  是否一定要得到百分比格式
     */
    public function getSureBackupProgress($taskStatus, $progress, $percentFlag){
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['PAUSED']){
                //如果任务没有运行
                if($percentFlag){
                    return "0%";
                }else{
                    return Xphp::$_config['NULLSPACE'];
                }
        }
        $speed = $progress . "%";
        return $speed;
    }

    /**
     * 获取验证功能进度颜色
     * @param unknown $flag
     * @return string
     */
    private function getVerifyColor($flag){
        $color = "#F7F7F7";
        switch ($flag){
            case Xphp::$_config['VERIFY_STATUS']['WAIT']:
            case Xphp::$_config['VERIFY_STATUS']['VERIFY']:
                $color = "#F7F7F7";
                break;
            case Xphp::$_config['VERIFY_STATUS']['FINISH']:
            case Xphp::$_config['VERIFY_STATUS']['SKIP']:
                $color = "#72DDC8";
                break;
            case Xphp::$_config['VERIFY_STATUS']['ERROR']:
                $color = "#DB7272";
                break;
        }
        return $color;
    }

    /**
     * 获取删除归档日志对应描述
     * @param int $flag
     * @return string|mixed
     */
    private function getDbDeleteLogDes($flag){
        $des= Xphp::$_config['NULLSPACE'];
        switch ($flag){
            case 1:
                $des = Xphp::$_lang['UI_DB_DELETE_ARCHIVE_LOG_TYPE1'];
                break;
            case 2:
                $des = Xphp::$_lang['UI_DB_DELETE_ARCHIVE_LOG_TYPE2'];
                break;

            case 3:
                $des = Xphp::$_lang['UI_DB_DELETE_ARCHIVE_LOG_TYPE3'];
                break;

        }

        return $des;
    }

    /**
     * 获取数据库备份归档存储告警信息
     * @param array $details
     * @param Object $utils
     * @return string[]|NULL[]
     */
    private function getDbDetails($details, $utils){
        $info = array();
        $info['warn_check'] = $utils->parseFlagToBool($details['warn_check']);
        $info['warn_type'] = intval($details['warn_type']);
        if(intval($details['warn_type']) == 1){
            //百分比
            $info['warn_value'] =  $details['warn_value'] . "%";
        }else{
            //大小
            $info['warn_value'] =  $utils->calSize(intval($details['warn_value']));
        }

        return $info;
    }

    /**
     * 任务详情: 得到副本操作系统主机列表
     * @param unknown $params
     */
    public function getCopyDetailsOS($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select ba.agent_name, ba.hostname, ba.ip, bcil.source_timepoint_list,bcil.src_timepoint_count, bcil.write_size, bcil.total_copy_size, bcil.complete_size, bcil.transport_size, bcil.write_size, bcil.copy_status,
                       bcil.item_uuid as vm_uuid, bcil.error_code, bcil.new_timepoint_list, bcil.vcenter_uuid,
                		bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_write_size, bri.current_object_write_size, bri.current_object_completed_size,
                        bt.task_status as bd_task_status, bt.task_type
                from backup_copy_item_list bcil
                left join bd_running_info bri
                on bcil.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = bcil.task_uuid
                left join bd_agent ba
                on ba.agent_uuid = bcil.vcenter_uuid
                where bcil.task_uuid = ? group by bcil.vcenter_uuid, bcil.item_uuid order by bcil.item_id ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $records["data"] = array();
        $osHandler = Xphp::instance('OsHandler');
        foreach ($data as $d){
            //如果任务正在运行,需要过滤掉新添加
//             if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
//                 $d['copy_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
//                     continue;
//             }
            $timepointCount = intval($d['src_timepoint_count']);
            $records["data"][] = array(
                $i++,
                $osHandler->getAgentName($d['hostname'],$d['agent_name'],$d['ip']),
                $this->getCopyTimepointNum($timepointCount,$d['bd_task_status']),
                $this->getVMListSize($d['bd_task_status'], $d['total_copy_size']),
                $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMSpeed($d['copy_status'], $d['speed'], $d['speed_time'],0,true),
                $this->getVMPercent($d['total_copy_size'], $d['current_object_completed_size'], $d['complete_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMStatus($d['bd_task_status'], $d['copy_status'],true),
                $this->getErrorCodeDes($d['copy_status'], $d['error_code'],true),
                $this->getVMDetailsInfo($d, $taskUUID),
            );
        }

        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getNodeNetworkFlag($taskuuid){
        $judgeRet = $this->judgeBackupTargetForShowNetwork($taskuuid);
        if (!$judgeRet) {
            return false;
        }
        $sql = "select ba.agent_uuid, ba.net_model from bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d){
            if(intval($d['net_model']) == 2){
                $flag = true;
            }
        }
        if ($flag) {
            // 多主机备份不需要传输网络
            $agentUuidMap = [];
            foreach ($data as $d) {
                $agentUuidMap[$d['agent_uuid']] = $d['agent_uuid'];
            }
            if (count(array_values($agentUuidMap)) > 1) {
                $flag = false;
            }
        }
        return $flag;
    }

    /**
     * 获取网络显示标记
     * @param $taskUuid
     * @param $moduleType
     * @param $taskType
     * @return bool|void
     */
    public function getNetworkShowFlag($taskUuid, $moduleType, $taskType)
    {
        $allModuleType = Xphp::$_config['MODULE_TYPE'];
        switch ($moduleType) {
            case $allModuleType['DB']:
                return $this->getDBNodeNetworkFlag($taskUuid, $taskType);
            case $allModuleType['OS']:
            case $allModuleType['FS']:
                return $this->getNodeNetworkFlag($taskUuid);
            default:
                // 默认只判断备份对象是否为计算资源池
                return $this->judgeBackupTargetForShowNetwork($taskUuid);
        }
    }

    /**
     * 备份目标为计算资源池时，不显示传输网络
     * @param $taskUuid
     * @return bool
     */
    private function judgeBackupTargetForShowNetwork($taskUuid)
    {
        $sql = "SELECT node_pool_uuid, storage_pool_uuid FROM bd_task WHERE task_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        if ($taskData[0]['node_pool_uuid']) {  // 备份目标为计算资源池不显示传输网络
            return false;
        }
        if ($taskData[0]['storage_pool_uuid']) {  // 备份目标为计算资源池不显示传输网络
            $sql = "SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ";
            $storagePoolData = $this->dbSelect($sql, [$taskData[0]['storage_pool_uuid']]);
            if ($storagePoolData && $storagePoolData[0]['storage_pool_type'] == 1) {  // 集中式存储资源池不需要初始化传输网络
                return false;
            }
        }
        return true;
    }

    /**
     * 获取数据库任务是否显示传输网络标记
     * @param string $taskuuid
     * @param int $taskType
     * @return boolean
     */
    public function getDBNodeNetworkFlag($taskuuid, $taskType){
        if ($taskType == Xphp::$_config['TASKTYPE']['DRILL']) {
            return false;
        }
        $judgeRet = $this->judgeBackupTargetForShowNetwork($taskuuid);
        if (!$judgeRet) {
            return false;
        }
        $sql = "SELECT ba.net_model, ba.agent_uuid, baa.cluster_uuid
                FROM db_list dl
                    INNER JOIN bd_agent ba ON ba.agent_uuid = dl.agent_uuid
                    INNER JOIN db_task dt ON dt.task_uuid = dl.task_uuid
                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = dl.agent_uuid AND baa.app_name = dl.instance_name AND baa.app_type = dt.db_type
                WHERE dl.task_uuid = ?";
        $data = $this->dbSelect($sql, [$taskuuid]);
        $flag = false;  //定义传输网络标志
        foreach ($data as $d){
            if(intval($d['net_model']) == 2){
                $flag = true;
            }
        }
        if ($flag) {
            // 集群不需要传输网络
            foreach ($data as $d) {
                if ($d['cluster_uuid']) {
                    $flag = false;
                }
            }
        }
        if ($flag) {
            // 数据库多主机备份不需要传输网络
            $agentUuidMap = [];
            foreach ($data as $d) {
                $agentUuidMap[$d['agent_uuid']] = $d['agent_uuid'];
            }
            if (count(array_values($agentUuidMap)) > 1) {
                $flag = false;
            }
        }

        return $flag;
    }


    /**
     * 选择虚拟机启动数据验证任务
     * @param unknown $params
     * @return string
     */
    public function startVerifyVm($params){
        $subModule = intval($params['hypervisor']);
        $taskuuid = $params['taskuuid'];
        $this->checkTaskRun($taskuuid); //检查任务是否在运行
        $uuidList = $params['vmuuids'];
        $vcenteruuidList = $params['vcenteruuid'];
        $vmuuids = array();
        $vcenteruuids = array();
        foreach ($vcenteruuidList as $vcenteruuid){
            $vcenteruuids[] = array(
                'vcenter_uuid' => $vcenteruuid
            );
        }
        foreach ($uuidList as $uuid){
            $vmuuids[] = array(
                "vm_uuid" => $uuid
            );
        }
        $opName = 'VM_PRIVATE_TASK_OP_START_SURE_BACKUP_TASK';
        $msg = array(
            'task_uuid' => $taskuuid,
            'backup_mode' => 1,
            'time_strategy_id' => 0,
            'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
            "vm_uuid_list" => $vmuuids,
            "vcenter_uuid_list" => $vcenteruuids
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
        $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 选择虚拟机停止验证任务
     * @param unknown $params
     * @return string
     */
    public function stopVerifyVm($params){
        $subModule = intval($params['hypervisor']);
        $taskuuid = $params['taskuuid'];
        $this->checkTaskRun($taskuuid);
        $uuidList = $params['vmuuids'];
        $vcenteruuidList = $params['vcenteruuid'];
        $vmuuids = array();
        $vcenteruuids = array();
        foreach ($vcenteruuidList as $vcenteruuid){
            if(!in_array($vcenteruuid, $vcenteruuids))
            $vcenteruuids[] = array(
                'vcenter_uuid' => $vcenteruuid
            );
        }
        foreach ($uuidList as $uuid){
            $vmuuids[] = array(
                "vm_uuid" => $uuid
            );
        }
        $opName = 'VM_PRIVATE_TASK_OP_STOP_SURE_BACKUP_TASK';
        $msg = array(
            'task_uuid' => $taskuuid,
            "vm_uuid_list" => $vmuuids,
            "vcenter_uuid_list" => $vcenteruuids
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
        $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 得到数据验证各项指标验证状态
     * @param int $bdTaskStatus
     * @param int $verifyStatus
     */
    private function getVerifyStatusDes($bdTaskStatus, $vmStatus, $verifyStatus, $vmDes){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                if($vmStatus == Xphp::$_config['VERIFY_STATUS']['SKIP']){
                    //跳过返回
                    return Xphp::$_config['NULLSPACE'];
                }
                //任务在运行状态时,显示虚拟机的状态
                return $vmDes['VERIFY_FUNC_STATUS'][$verifyStatus];
        }else{
            return Xphp::$_config['NULLSPACE'];
        }
    }

    /**
     * 获取节点名称和ip
     * @param $nodeuuid
     * @return array
     */
    private function getNodeNameAndIp($nodeuuid){
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $re = array(
            'name' => Xphp::$_config['NULLSPACE'],
            'ip' => "",
        );
        if ($data) {
            $nodeHandler = Xphp::instance('NodeHandler');
            $re = array(
                'name' => $nodeHandler->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
                'ip' => $data[0]['ip'],
            );
        }
        return $re;
    }


    /**
     * 选择数据库启动备份任务
     * @param unknown $params
     * @return string
     */
    public function startDBJob($params){
        $mode = $params['mode'];
        $subModule = intval($params['dbtype']);
        $taskuuid = $params['taskuuid'];
        $crosscheck = $params['crosscheck'] ?? false;
        $utils = Xphp::instance('Utils');
        $opName = 'BD_TASK_OP_BACKUP_START';
        $operate = $this->getUnifyOpcodeDes($opName);
        //检查是否有操作权限
        $this->checkOpPermission($taskuuid, $operate);
        $this->checkTaskRun($taskuuid);
        $dbList = $params['dbList'];
        $msg = array(
            'task_uuid' => $taskuuid,
            'backup_mode' => $mode,
            'time_strategy_id' => 0,
            'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
            "choose_db_list" => $dbList,
            'crosscheck_flag' => $utils->parseBoolToFlag($crosscheck),
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
        $mbResult = $this->mbDBMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 删除任务中选择的数据库
     * @param unknown $params
     */
    public function deleteSelectDbs($params){
        $taskuuid = $params['taskuuid'];
        $dbList= $params['dbList'];
        $this->checkDbTaskStatus($taskuuid, $dbList);  //检查任务是否处于停止状态,任务是否只有一个数据库

        $dbListStr = implode(",", $dbList);
        $sql = "delete from db_list where task_uuid = ? and database_id in ($dbListStr)";
        $result = $this->dbExec($sql, array($taskuuid));

        return $this->muOpResult($result, Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS']);

    }

    /**
     * 检查数据库任务状态
     * @param unknown $taskuuid
     * @param unknown $dbuuids
     */
    private function checkDbTaskStatus($taskuuid, $dbList){
        $sql = "select bt.task_status, count(dl.database_id) as db_num from bd_task bt, db_list dl where bt.task_uuid = dl.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(intval($data[0]['task_status']) != Xphp::$_config['TASKSTATUS']['STOPPED']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS_STATUS_ERROR'], Xphp::$_lang['WEB_VM_BACKUP_DELETE_SELECT_VMS_STATUS_ERROR_TIPS'], 'warning'));
        }

        $count = intval($data[0]['db_num']) - count($dbList);
        if($count == 0){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_SELECT_DELETE_TITLE'], Xphp::$_lang['WEB_DB_SELECT_DELETE_TITLE_TIPS'], 'warning'));
        }
    }

    /**
     * NTP同步时检查是否有运行中的任务
     * @return string
     */
    public function ntpSyncCheckRunningJob(){
        $sql = "select id from bd_task where task_status = ? and delete_flag = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['RUNNING'], Xphp::$_config['FLAG']['UNSET']));
        if (!is_array($data)) {
            return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_TIME_NTP_SYNC_CHECK_RUNNING_JOB'], '', 'error');
        }
        $result = boolval($data[0]);
        return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_TIME_NTP_SYNC_CHECK_RUNNING_JOB'], '', 'info', 0, ['has_running_job' => $result]);
    }

    //---------------------------CBR新增-------------------------------
     /**
     * 获取CBR任务同步粒度
     * @return string
     */
    public function getCBRSyncType($params){
        $sql = "select version from vm_machine_list  where task_uuid = ?";
        $data =  $this->dbSelect($sql,array($params['uuid']));
        $result  = array(
            "synctype"=>$data[0]['version']
        );
        return json_encode($result);

    }

    // ------------------------------全局策略--------------

    /**
     * @description: 获取策略列表
     * @param {*} $params
     * @return {*}
     */
    public function getStrategyList($params){
        $start = $params['offset'];
        $length = $params['limit'];
        $sql = "select bsg.strategy_group_uuid, bsg.strategy_group_name, unix_timestamp(bsg.create_time) create_time, 
        bsg.strategy_group_type, bsg.remark, bsg.extra_info, bu.user_name from bd_strategy_group bsg, bd_user bu where bu.user_uuid = bsg.user_uuid and bsg.user_uuid = ? limit ?, ?";
        $sqlCount = "select count(bsg.strategy_group_id) as total from bd_strategy_group bsg, bd_user bu where bu.user_uuid = bsg.user_uuid and bsg.user_uuid = ? limit ?, ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'],$start, $length));
        $count = $this->dbSelect($sqlCount, array(Xphp::$_user['useruuid'],$start, $length));
        $records = array();
        $records['rows'] = array();
        $id = 0;

        if(!empty($data)){
            foreach($data as $d){
                $records['rows'][] = array(
                    'uuid' => $d['strategy_group_uuid'],
                    'id' => ++$id,
                    'strategy_name' => $d['strategy_group_name'],
                    'strategy_type' => $d['strategy_group_type'],
                    'update_time' => date('Y-m-d H:i:s', $d['create_time']),
                    'update_user' => $d['user_name'],
                    'strategy_marks' => !empty($d['remark']) ? $d['remark'] : '---',
                    'strategy_actions' => array(1,2),
                    'strategy_details' => json_decode($d['extra_info'], true),
                    'related_task' => $this->getTaskInStrategy($d['strategy_group_uuid']),
                );
            }
        }
        $records["total"] = $count[0]['total'];
        return json_encode($records);
    }

    /**
     * @description: 添加新的策略组
     * @param {*} $params
     * @return {*}
     */
    public function addGlobalStrategy($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_global_strategy_add");
        $strategy = $params['strategyInfo'];
        // 对strategyname 和 remark 两参数进行xss_filter过滤
        $params['strategyname'] = Xphp::xssFilter($params['strategyname']);
        $params['remark'] = Xphp::xssFilter($params['remark']);
        // 策略名称检查
        $strategyName = $params['strategyname'];
        $this->checkStrategyName($strategyName);
        $remark = $params['remark'];
        $strategyType = $params['strategytype'];
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid();
        $createTime = date('Y-m-d H:i:s');
        $useruuid =  Xphp::$_user['useruuid'];
        $sqlParams = array($uuid, $strategyName, $strategyType, $useruuid, $createTime, $remark, json_encode($strategy));
        // 插入新的策略
        $sql = "insert bd_strategy_group (strategy_group_uuid, strategy_group_name, strategy_group_type, user_uuid, create_time, remark, extra_info) values (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);

        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $strategytypeDes = $pfDes['TASKTYPEDES'][$strategyType];
        $this->systemLog('SYSTEM_LOG_DESC_KEY_ADD_STRATEGY', array($strategytypeDes, $strategyName));
        return $this->muOpResult($result, Xphp::$_lang['UI_GLOBAL_STRATEGY_ADD']);
    }

    /**
     * @description: 删除前检查是否有关联任务并树形战术
     * @param {*} $params
     * @return {*}
     */
    public function checkTaskStrategy($params){
        $uuidList = $params['uuids'];
        $uuidDes =  implode("','", $uuidList);
        $sql = "select bt.task_name, bsg.strategy_group_name, bt.task_uuid, bsg.strategy_group_uuid from bd_task bt, bd_strategy_group bsg  where bsg.strategy_group_uuid = bt.strategy_group_uuid and bsg.strategy_group_uuid in ('".$uuidDes."')";
        $data = $this->dbSelect($sql);
        // 没有关联任务直接删除
        if(empty($data)){
            return $this->deleteStrategy($params);
        }
        $node = array();
        $strategy = array();
        $task = array();
        foreach($data as $d){
            $taskuuid = $d['task_uuid'];
            $strategyuuid = $d['strategy_group_uuid'];
            $taskname = $d['task_name'];
            $strategyname = $d['strategy_group_name'];

            //检查添加策略
            if(!in_array($strategyuuid, $strategy)){
                $node[] = array(
                    "id" =>  $strategyuuid,
                    "pId" => 0,
                    "name" => $strategyname,
                    "title" => $strategyname,
                    "open" => true,
                    "nocheck" => true,
                    "type" => -1,
                    "icon" => './img/platform/strategy.png',
                    "taskuuid" => $taskuuid,
                    "strategyuuid" => $strategyuuid
                );
                $strategy[] = $strategyuuid;
            }

            //检查添加任务
            if(!in_array($taskuuid, $task)){
                $node[] = array(
                    "id" =>  $taskuuid,
                    "pId" => $strategyuuid,
                    "name" => $taskname,
                    "title" => $taskname,
                    "nocheck" => true,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "taskuuid" => $taskuuid,
                    "strategyuuid" => $strategyuuid
                );
                $task[] = $taskuuid;
            }

        }
        $info = json_encode($node);
        return $this->muOpResult(true, '', '', '', '', $info);

    }

    /**
     * @description: 删除单个策略
     * @param {*} $params
     * @return {*}
     */
    public function deleteStrategy($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_global_strategy_delete");
        $uuidList = $params['uuids'];
        $uuidDes = implode("','", $uuidList);
        $sql = "delete from bd_strategy_group where strategy_group_uuid in ('". $uuidDes ."')";
        $result = $this->dbExec($sql);
        $descriptionParams = array(count($uuidList));
        $this->systemLog('SYSTEM_LOG_DESC_KEY_DELETE_STRATEGY', $descriptionParams);
        if($result){
            //取消任务与策略关联
            $strategy = "";
            $sql = "update bd_task set strategy_group_uuid = ? where strategy_group_uuid in ('". $uuidDes ."')";
            $result = $this->dbExec($sql, array($strategy));
        }
        return $this->muOpResult($result, Xphp::$_lang['UI_GLOBAL_STRATEGY_DELETE']);
    }

    /**
     * @description: 获取需要修改的旧策略信息
     * @param {*} $params
     * @return {*}
     */
    public function initOldStrategy($params){
    	$uuid = $params['uuid'];
    	$sql = "select strategy_group_name, strategy_group_type, remark, extra_info from bd_strategy_group where strategy_group_uuid = ? ";
    	$data = $this->dbSelect($sql, array($uuid));
    	$info = array(
    		'strategyname' => $data[0]['strategy_group_name'],
    		'strategytype' => intval($data[0]['strategy_group_type']),
    		'remark' => $data[0]['remark'],
    		'strategyInfo' => json_decode($data[0]['extra_info'], true)
    	);

    	return json_encode($info);
    }

    /**
     * @description: 修改提交前检查相关联任务
     * @param {*} $params
     * @return {*}
     */
    public function editGlobalStrategyCheck($params){
        $strategyuuid = $params['strategyuuid'];
        $strategyInfoDes = json_encode($params['strategyInfo']);
    	$sql = "select bt.task_uuid, bsg.extra_info from bd_task bt, bd_strategy_group bsg
    			where bsg.strategy_group_uuid = bt.strategy_group_uuid and bsg.strategy_group_uuid = ?";
        $data = $this->dbSelect($sql, array($strategyuuid));
        $taskuuidList = array();
        // 没有关联任务直接删除
    	if(!empty($data)){
            foreach($data as $d){
                if(trim($strategyInfoDes) == trim($d['extra_info'])) continue;
                $taskuuidList[] = $d['task_uuid'];
            }
        }
        $count = count($taskuuidList);
        $info = array();
        if($count == 0){
            return $this->muOpResult(true, '', '', '', '', $info);
        }
    	$info = array(
    		'task_count' => $count
    	);
    	return $this->muOpResult(true, '', '', '', '', $info);
    }

    /**
     * @description: 修改全局策略
     * @param {*} $params
     * @return {*}
     */
    public function editGlobalStrategy($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_global_strategy_edit");
        // 对remark 两参数进行xss_filter过滤
        $params['strategyname'] = Xphp::xssFilter($params['strategyname']);
        $params['remark'] = Xphp::xssFilter($params['remark']);
    	$strategyuuid = $params['strategyuuid'];
        $strategyName = $params['strategyname'];
    	$strategyType = $params['strategytype'];
    	$remark = $params['remark'];
    	$strategyInfo = $params['strategyInfo'];
    	$taskuuids = $params['taskuuids'];
    	$createTime = date('Y-m-d H:i:s');
        // 修改策略
    	$sqlParams = array($strategyName, $remark, $createTime, json_encode($strategyInfo), $strategyuuid);
    	$sql = "update bd_strategy_group set strategy_group_name = ?, remark = ?, create_time = ?, extra_info = ? where strategy_group_uuid = ? ";
        $result= $this->dbExec($sql, $sqlParams);
        // 分发策略到关联任务
    	if(!empty($taskuuids)){
                foreach($taskuuids as $taskuuid){
                    $result = $result && $this->dispenseStrategyToJob($strategyInfo, $taskuuid, $strategyuuid,$strategyType);
                }

            if(!$result){
                return $this->muOpResult(false, Xphp::$_lang['UI_GLOBAL_STRATEGY_DISPENSE'], "", "warning");
            }
    	}

    	$pfDes = include APP_PATH . "platform/PFDescription.php";
    	$strategytypeDes = $pfDes['TASKTYPEDES'][$strategyType];
    	$this->systemLog('SYSTEM_LOG_DESC_KEY_EDIT_STRATEGY', array($strategytypeDes, $strategyName));
    	return $this->muOpResult($result, Xphp::$_lang['UI_GLOBAL_STRATEGY_EDIT']);

    }

    /**
     * @description: 批量分发到任务
     * @param {*} $params
     * @return {*}
     */
    public function dispenseStrategyBatch($params){
        $strategyInfo = $params['strategyInfo'];
        $strategyuuid = $params['strategyuuid'];
        $taskuuids = $params['taskuuids'];
        $strategyType = $params['strategytype'];
        $result = true;
        // 检查是否是停止状态 只有停止状态才能分发
        if(!empty($taskuuids)){
            foreach($taskuuids as $taskuuid){
                $this -> taskIsStopped($taskuuid);
            }
        };
        // 逐条分发
        if(!empty($taskuuids)){
            foreach($taskuuids as $taskuuid){
                $result = $result && $this->dispenseStrategyToJob($strategyInfo, $taskuuid, $strategyuuid,$strategyType);
            }
            if(!$result){
                return $this->muOpResult(false, Xphp::$_lang['UI_GLOBAL_STRATEGY_DISPENSE'], "", "warning");
            }
    	}
        return $this->muOpResult($result, Xphp::$_lang['UI_GLOBAL_STRATEGY_DISPENSE']);
    }

    /**
     * @description: 检查任务是否是停止状态  否则不可分发
     * @param {*} $taskuuid
     * @return {*}
     */
    public function taskIsStopped($taskuuid){
        $sql = "select task_status from bd_task where task_uuid = ?";
        $data = $this -> dbSelect($sql, array($taskuuid));
        if($data[0]['task_status'] != Xphp::$_config['TASKSTATUS']['STOPPED']){
            exit($this->muOpResult(false, Xphp::$_lang['UI_GLOBAL_STRATEGY_DISPENSE'],Xphp::$_lang['UI_GLOBAL_STRATEGY_TASK_NOT_STOPPED'], "warning"));
        }
    }

    /**
     * @description: 分发策略到具体的任务
     * @param {*} $info 策略信息
     * @param {*} $taskuuid 分发的任务uuid
     * @param {*} $strategygroupuuid 策略组uuid
     * @param {*} $strategyType 策略组类型
     * @return {*}
     */
    public function dispenseStrategyToJob($info, $taskuuid, $strategygroupuuid,$strategyType){
        $sql = "select task_type, strategy_id from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $strategyID = $data[0]['strategy_id'];
        return $this->dispenseBackupJob($info,$taskuuid, $strategyID, $strategygroupuuid,$strategyType);
    }

    /**
     * @description:
     * @param {*} $info
     * @param {*} $taskuuid
     * @param {*} $strategyID
     * @param {*} $strategygroupuuid
     * @param {*} $strategyType
     * @return {*}
     */
    private function dispenseBackupJob($info, $taskuuid, $strategyID, $strategygroupuuid,$strategyType){
        //开始事务
        $this->dbBeginTransaction();
        $utils = Xphp::instance('Utils');
        $result = true;
        //时间策略
        if($info['time']['check']){
            //更新时间策略表 bd_time_strategy
            $sql = "delete from bd_time_strategy where strategy_id = ? and mode != ?";
            $result =  $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));
            $timeStrategy = $info['time']['timeInfo'];
            if("oncetime" == $timeStrategy['type']){
                //一次性策略
                $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time)
                        values (?, ?, ?, ?)";
                $sqlParams = array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL'],
                    Xphp::$_config['STRATEGY_TYPE']['ONCE'], $timeStrategy['datetime']);
                $result = $result && $this->dbExec($sql, $sqlParams);
            }elseif("strategy" == $timeStrategy['type']){
                //时间策略
                if(!empty($timeStrategy['fullInfo'])){
                    //完全策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['FULL'];

                    //单独处理完全备份修改
                    $sql = "select strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                        from bd_time_strategy where strategy_id = ?";
                    $data = $this->dbSelect($sql, array($strategyID));
                    if(empty($data[0])){
                        //如果数据库没有,直接插入,
                        $result = $result && $this->insertTimeStrategy($taskuuid,$strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);

                    }else{
                        //如果数据库有,比较内容,如果一样,不做操作,如果不一样,删除后插入
                        $strategyInfo = $timeStrategy['fullInfo'];
                        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
                        //不一样,删除后再重新插入
                        $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                        $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));

                        $result = $result && $this->insertTimeStrategy($taskuuid,$strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                    }
                }else{
                    $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                    $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));
                }
                if(!empty($timeStrategy['incrInfo'])){
                    //增量策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                    $result = $result && $this->insertTimeStrategy($taskuuid,$strategygroupuuid, $strategyID, $modeType, $timeStrategy['incrInfo']);
                }
                if(!empty($timeStrategy['diffInfo'])){
                    //差异策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'];
                    $result = $result && $this->insertTimeStrategy($taskuuid,$strategygroupuuid, $strategyID, $modeType, $timeStrategy['diffInfo']);
                }

            }
        }
        //限速策略
        if($info['speedlimit']['check']){
            $speedList = $this->groupTaskSpeedList($info['speedlimit']['speedInfo'], $strategygroupuuid);
            //更新时间策略表 bd_task_speed_limit_strategy
            $sql = "delete from bd_task_speed_limit_strategy where task_uuid = ?";
            $result = $result && $this->dbExec($sql, array($taskuuid));
            $sql = "insert bd_task_speed_limit_strategy (strategy_uuid, strategy_group_uuid, strategy_type, days, start_time, end_time, remark, speed_limited_value, task_uuid) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            foreach($speedList as $speed){
                $sqlParams = array($speed['strategy_uuid'], $strategygroupuuid, intval($speed['strategy_type']), $speed['days'], $speed['start_time'], $speed['end_time'], $speed['remark'], intval($speed['speed_limited_value']),$taskuuid);
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
        //存储策略
        if($info['store']['check']){
            //更新存储策略表 bd_storage_strategy
            $sql = "update bd_storage_strategy set deduplication_flag = ?, compressed_flag = ?, encrypted_flag =?, password_auto_flag=?, password=? where task_uuid = ?";
            $sqlParams = array($utils->parseBoolToFlag($info['store']['storeInfo']['deduplication']),
                $utils->parseBoolToFlag($info['store']['storeInfo']['compress']),
                $utils->parseBoolToFlag($info['store']['storeInfo']['encrypt']),
                $utils->parseBoolToFlag($info['store']['storeInfo']['password_auto_flag']),
                $this->updateStorePassword($info['store']['storeInfo']['password'], $info['store']['storeInfo']['encrypt'], $info['store']['storeInfo']['password_auto_flag']),
                $taskuuid
            );
            $result = $result && $this->dbExec($sql, $sqlParams);

        }
        //保留策略
        if($info['reserve']['check']){
            //更新保留策略表 bd_reserved_strategy
            $sql = "update bd_reserved_strategy set strategy_type = ?, number = ? where task_uuid = ?";
            $sqlParams = array($info['reserve']['reserveInfo']['type'], intval($info['reserve']['reserveInfo']['value']), $taskuuid);
            $result = $result && $this->dbExec($sql, $sqlParams);
            //更新GFS策略
            //先查询此任务下有多少个虚拟机,有关虚拟机的都要修改
            if($strategyType == Xphp::$_config['POLICY_TYPE_BACKUP']['VIRTUAL_MACHINE']){
                $sqlvm = "select vm_uuid from vm_machine_list where task_uuid = ?";
                $resultvm = $this->dbSelect($sqlvm, array($taskuuid));
                $vm_list = array();
                foreach ($resultvm as $each){
                    $vm_list[] = array(
                        'vmuuid' => $each['vm_uuid'],
                    );
                };
                $result = $result && $this->updateGFSStrategy($info['reserve']['reserveInfo']['gfs_strategy_item_list'], true, $taskuuid, $vm_list);
            }
        }
        $sql = "select strategy_group_uuid from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if($data[0]['strategy_group_uuid'] != $strategygroupuuid){
            $sql = "update bd_task set strategy_group_uuid = ? where task_uuid = ?";
            $result = $result && $this -> dbExec($sql, array($strategygroupuuid,$taskuuid));
        }
        if($result){
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }

        return $result;
    }

    /**
     * 获取分发策略相关的任务列表
     * @param {*} $params
     */
    public function getTaskStrategyList($params){
        $start = $params['offset'];
        $length = $params['limit'];
        $draw = $params['draw'];
        $old_strategyInfo = $params['old_strategyInfo'];
        $strategyuuid = $params['uuid'];
        $strategyInfo = $params['strategyInfo'];
        $distribute_flag = $params['distribute_flag'];//策略管理标志
        $sqlCountParams = array();
        $sqlParams = array();
        // 策略管理页面
        if($distribute_flag){
            $sql = "select bt.task_name, bt.task_uuid, bt.task_type, unix_timestamp(bt.create_time) create_time, bsg.extra_info, bsg.strategy_group_type, bt.module_type from bd_task bt, bd_strategy_group bsg where bt.module_type = bsg.strategy_group_type and bt.task_type in (" . Xphp::$_config['TASKTYPE']['BACKUP'] . "," . Xphp::$_config['TASKTYPE']['OS_BACKUP'] . ") and bsg.strategy_group_uuid = ? ";
            $sqlCount = "select count(bt.task_uuid) as total from bd_task bt, bd_strategy_group bsg where bt.module_type = bsg.strategy_group_type and bt.task_type in (" . Xphp::$_config['TASKTYPE']['BACKUP'] . "," . Xphp::$_config['TASKTYPE']['OS_BACKUP'] . ")  and bsg.strategy_group_uuid = ?";
            $sqlParams = array($strategyuuid);
            $sqlCountParams = array($strategyuuid);
        }else{
            $sql = "select bt.task_name, bt.task_uuid, bt.task_type, unix_timestamp(bt.create_time) create_time, bsg.extra_info, bt.module_type from bd_task bt, bd_strategy_group bsg where bt.strategy_group_uuid = bsg.strategy_group_uuid and bsg.strategy_group_uuid = ? order by bt.create_time desc limit ?, ? ";
            $sqlCount = "select count(bt.task_uuid) as total  from bd_task bt, bd_strategy_group bsg where bt.strategy_group_uuid = bsg.strategy_group_uuid and bsg.strategy_group_uuid = ? ";
            $sqlParams = array($strategyuuid, $start, $length);
            $sqlCountParams = array($strategyuuid);
        }
        $data = $this -> dbSelect($sql, $sqlParams);
        $count = $this -> dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $records['rows'] = array();
        foreach($data as $d){
            $taskuuid = $d['task_uuid'];
            $diffDes = $this->getBackupDiffStrategyDes($strategyInfo,$old_strategyInfo);
            $records['rows'][] = array(
                'id' => ++$start,
                'task_name' => $d['task_name'],
                'module_type' => intval($d['module_type']),
                'create_time' => $this->parseDate($d['create_time']),
                'strategy_diff' => $diffDes['strategyDes'],
                'diff_info' => Xphp::$_lang['UI_PUBLIC_DETAIL'],
                'task_uuid' => $taskuuid,
                'diff_detail' => $diffDes['details'],
                'uuid' => $d['task_uuid'],
                'type' => intval($d['task_type']),
            );
        }
        $records["total"] = $count[0]['total'];

        return  json_encode($records);

    }

    /**
     * @description: 对比获取差异
     * @param {*} $params
     * @return {*}
     */
    public function getStrategyDiff($params){
        $data = $params['strategy_details'];
        $records = array();
        $records['rows'] = array();
        foreach ($data as $d){
            if($d['title']){
                $records['rows'][] = array(
                    'strategy_type' => $d['title'],
                    'old_info' => $d['oldtitle'],
                    'new_info' => $d['newtitle'],
                );
            }
        }
        return  json_encode($records);
    }

    /**
     * @description: 获取策略列表初始化下拉选择策略
     * @param {*} $params
     * @return {*}
     */
    public function getStrategySelect($params){
        // 模块
        $type = intval($params['type']);
    	$info = array();
    	$sql ="select strategy_group_uuid, strategy_group_name, extra_info from bd_strategy_group where strategy_group_type = ? and user_uuid = ? order by create_time desc";
    	$sqlParams = array($type, Xphp::$_user['useruuid']);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$info[] = array(
    		'uuid' => '',
    		'text' => Xphp::$_lang['UI_GLOBAL_STRATEGY_AUTO'],
    		'strategy' => array()
    	);
    	if(!empty($data)){
    		foreach ($data as $d){
    			$info[] = array(
    				'uuid' => $d['strategy_group_uuid'],
    				'text' => $d['strategy_group_name'],
    				'strategy' => json_decode($d['extra_info'],true)
    			);
    		}
    	}
    	return json_encode($info);
    }

    /**
     * @description: 获取单个策略修改前后的差异
     * @param {*} $info
     * @param {*} $oldInfo
     * @return {*}
     */
    private function getBackupDiffStrategyDes($info, $oldInfo){
        $strategyDiff = "";
        $diffStrategy = array();
        $timeDiff = array();
        $speedDiff = array();
        $storeDiff = array();
        $reserveDiff = array();
        $oldTimeInfo = $oldInfo['strategyInfo']['time'];
        $oldSpeedInfo = $oldInfo['strategyInfo']['speedlimit'];
        $oldStoreInfo = $oldInfo['strategyInfo']['store'];
        $oldReserveInfo = $oldInfo['strategyInfo']['reserve'];
        // 时间策略
        if($info['time']['check']){
            if(!$oldTimeInfo['check']){
                $timeDiff =  array(
                    'title' => Xphp::$_lang['UI_STRATEGY_TIME'],
                    'old' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
                    'oldtitle' => str_replace("<br>", "\n", Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE']),
                    'new' => $info['time']['des'],
                    'newtitle' => str_replace("<br>", "\n", $info['time']['des'])
                );
                $strategyDiff .= Xphp::$_lang['UI_STRATEGY_TIME'] . " ";
            }else{
                if($info['time']['des'] != $oldTimeInfo['des']){
                    $timeDiff =  array(
                        'title' => Xphp::$_lang['UI_STRATEGY_TIME'],
                        'old' =>  $oldTimeInfo['des'],
                        'oldtitle' => str_replace("<br>", "\n", $oldTimeInfo['des']),
                        'new' => $info['time']['des'],
                        'newtitle' => str_replace("<br>", "\n", $info['time']['des'])
                    );
                    $strategyDiff .= Xphp::$_lang['UI_STRATEGY_TIME'] . " ";
                }
            }

        }
        // 限速策略
        if($info['speedlimit']['check']){
            if(!$oldSpeedInfo['check']){
                $speedDiff =  array(
                    'title' => Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'],
                    'old' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
                    'oldtitle' => str_replace("<br>", "\n", Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE']),
                    'new' => $info['speedlimit']['des'],
                    'newtitle' => str_replace("<br>", "\n", $info['speedlimit']['des'])
                );
                $strategyDiff .= Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] . " ";
            }else{
                if($info['speedlimit']['des'] != $oldSpeedInfo['des']){
                    $speedDiff =  array(
                        'title' => Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'],
                        'old' => $oldSpeedInfo['des'],
                        'oldtitle' => str_replace("<br>", "\n", $oldSpeedInfo['des']),
                        'new' => $info['speedlimit']['des'],
                        'newtitle' => str_replace("<br>", "\n", $info['speedlimit']['des'])
                    );
                    $strategyDiff .= Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] . " ";
                }
            }
        }
        // 存储策略
        if($info['store']['check']){
            if(!$oldStoreInfo['check']){
                $storeDiff =  array(
                    'title' => Xphp::$_lang['UI_BACKUP_STORAGE_STRATEGY'],
                    'old' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
                    'oldtitle' => str_replace("<br>", "\n", Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE']),
                    'new' => $info['store']['des'],
                    'newtitle' => str_replace("<br>", "\n", $info['store']['des'])
                );
                $strategyDiff .= Xphp::$_lang['UI_BACKUP_STORAGE_STRATEGY'] . " ";
            }else{
                if($info['store']['des'] != $oldStoreInfo['des']){
                    $storeDiff =  array(
                        'title' => Xphp::$_lang['UI_BACKUP_STORAGE_STRATEGY'],
                        'old' => $oldStoreInfo['des'],
                        'oldtitle' => str_replace("<br>", "\n",$oldStoreInfo['des']),
                        'new' => $info['store']['des'],
                        'newtitle' => str_replace("<br>", "\n", $info['store']['des'])
                    );
                    $strategyDiff .= Xphp::$_lang['UI_BACKUP_STORAGE_STRATEGY'] . " ";
                }
            }
        }
        // 保留策略
        if($info['reserve']['check']){
            if(!$oldReserveInfo['check']){
                $reserveDiff =  array(
                    'title' => Xphp::$_lang['UI_BACKUP_RESERVE_STRATEGY'],
                    'old' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
                    'oldtitle' => str_replace("<br>", "\n", Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE']),
                    'new' => $info['reserve']['des'],
                    'newtitle' => str_replace("<br>", "\n", $info['reserve']['des'])
                );
                $strategyDiff .= Xphp::$_lang['UI_BACKUP_RESERVE_STRATEGY'] . " ";
            }else{
                if($info['reserve']['des'] != $oldReserveInfo['des']){
                    $reserveDiff =  array(
                        'title' => Xphp::$_lang['UI_BACKUP_RESERVE_STRATEGY'],
                        'old' => $oldReserveInfo['des'],
                        'oldtitle' => str_replace("<br>", "\n", $oldReserveInfo['des']),
                        'new' => $info['reserve']['des'],
                        'newtitle' => str_replace("<br>", "\n", $info['reserve']['des'])
                    );
                    $strategyDiff .= Xphp::$_lang['UI_BACKUP_RESERVE_STRATEGY'] . " ";
                }
            }
        }
        $diffStrategy = array(
            'time' => $timeDiff,
            'speed' => $speedDiff,
            'store' => $storeDiff,
            'reserve' => $reserveDiff,
        );
        $dataInfo = array(
            'strategyDes' => $strategyDiff,
            'details' => $diffStrategy
        );
        return $dataInfo;
    }

    /**
     * 得到策略组名
     * @param unknown $params
     */
    public function getStrategyName($params){
        $strategyName = Xphp::$_lang['UI_GLOBAL_STRATEGY_GROUP'];
        $name = $this->getValidName($strategyName);
        $info = array(
            'name' => $name
        );
        return json_encode($info);
    }

    /**
     * 获取可用的策略组名
     * @param string $strategyName  策略组名
     * @return string
     */
    public function getValidName($strategyName){
        $oldName = $strategyName;
        for($i=1; $i<1000; $i++){
            $strategyName .= $i;
            $sql = "select strategy_group_id from bd_strategy_group where strategy_group_name = ?";
            $data = $this->dbSelect($sql, array($strategyName));
            if(empty($data)){
                return $strategyName;
            }
            $strategyName = $oldName;
        }
        return $oldName;
    }


    //-------------------------------操作系统瞬时恢复任务新增----------------------------------------
    /**
     * 获取瞬时恢复任务的基本信息和主机信息
     * @param unknown $params
     */
    public function getOSIntantBaseInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $utils = Xphp::instance('Utils');
        //先获取备份服务器IP
        // $sql  = "select bn.ip as server_ip, ba.ip  as host_ip, ba.online_flag, bt.task_status,bt.task_type
        // from bd_task bt
        // left join bd_node bn on bn.node_uuid =  bt.node_uuid
        // left join os_list ol on ol.task_uuid =  bt.task_uuid
        // left join bd_agent ba on ba.agent_uuid  = ol.agent_uuid
        // where bt.task_uuid = ? ";
        $sql  = "select  ol.inst_recovery_agent_uuid  as host_ip, ba.online_flag, bt.task_status, bt.task_type, ot.inst_iscsi_network_uuid
        from bd_task bt
        left join os_list ol on bt.task_uuid = ol.task_uuid 
        left join os_backup_timepoint obt on obt.timepoint_uuid = ol.timepoint_uuid
        left join bd_agent ba on obt.agent_uuid  = ba.agent_uuid
        left join os_task ot on  bt.task_uuid = ot.task_uuid 
        where bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $targetinfo = $this->getOSHostInfo($data[0]['host_ip']);
        $serverip = $this->getServerIp($data[0]['inst_iscsi_network_uuid']);
        $info =  array(
            "server_ip"=>$serverip,
            "host_ip"=>$targetinfo['ip'],
            "online_flag"=> $utils->parseFlagToBool($targetinfo['online_flag']),
            "task_status"=>$data[0]['task_status'],
            "task_type"=>$data[0]['task_type']
        );
        return json_encode($info);
    }
    //获取操作系统迁移任务的基本信息和主机信息
    public function getOSMotionBaseInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $utils = Xphp::instance('Utils');
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time,
        bt.node_uuid, bt.storage_uuid, unix_timestamp(bs.next_start_time) next_start_time,
        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, 
        ol.reparted_flag, ol.agent_uuid as targetid, ol.os_config,
        ba.ip  as host_ip, ba.online_flag,
        obt.agent_uuid as sourceid, obt.os_type,
        ot.inst_iscsi_network_uuid 
        from bd_task bt 
        left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
        left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
        left join os_list ol on bt.task_uuid = ol.task_uuid 
        left join bd_agent ba on ba.agent_uuid  = ol.agent_uuid
        left join os_backup_timepoint obt on obt.timepoint_uuid = ol.timepoint_uuid
        left join os_task ot on  bt.task_uuid = ot.task_uuid 
        where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($data as $d){
            $list = json_decode($d['os_config'],true);
            //这里需要获取瞬时恢复主机名和迁移主机名
            // $migration_map = json_decode($d['migration_map'],true);
            // foreach($migration_map as $sourceid=>$targetidlist){
            //     $sourceidlist[] = $sourceid;
            // }
            // $sourceid = $sourceidlist[0];
            $sourceinfo = $this->getOSHostInfo($d['sourceid']);
            $targetinfo = $this->getOSHostInfo($d['targetid']); //目标主机只取第一个原主机下的
            $sourcehost =  $this->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")";
            $targethost = $this->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]) . "(" . $targetinfo['ip'] . ")";
            $serverip = $this->getServerIp($d['inst_iscsi_network_uuid']);
            $basicInfo = array(
                //任务名
                'taskName' => $d['task_name'],
                //任务状态
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                //任务总容量
                'totalSize' => $utils->calSize($d['total_object_size']),
                //已处理容量
                'currentSize' => $utils->calSize($d['total_object_completed_size']),
                //开始时间
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                //持续时间
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                //限速信息
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                //创建/修改时间
                'createTime' => $this->parseDate($d['create_time']),
                //下次开始时间
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                //传输策略
                'transportStrategy' => $this->gettransportOSinfo($d['strategy_id']),
                //任务状态int
                'status_num' => $d['task_status'],
                //任务进度
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                //任务进度百分比
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                //网络传输
                'networkFlag' => $this->getNetworkShowFlag($taskUUID, $d['module_type'], $d['task_type']),//检查显示传输网络标志
                //重建分区
                'reparted_flag' =>  $utils->parseFlagToBool($d['reparted_flag']),  //重建分区(只有恢复有)
                'repair_linux_flag' => $utils->parseFlagToBool($list['repair_linux_flag']), //引导恢复(只有恢复有)
                'osType' => $d['os_type'],              //操作系统类型
                //操作系统迁移用 新增
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
                $d['module_type'], $d['task_uuid']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'thread_num' => intval($d['thread_num']),
                "server_ip"=> $serverip,
                "host_ip"=> $d['host_ip'],
                "online_flag"=> $utils->parseFlagToBool($d['online_flag']),
                "task_status"=> $d['task_status'],
                "recoverhostname"=>$sourcehost,
                "motionhostname"=>$targethost,
                "tasytype"=>intval($d['task_type']),//用于判断是否跳转到瞬时恢复页面
            );
        }
        return json_encode($basicInfo);


    }
    //获取服务器IP地址
    public function getServerIp($networkuuid){
        $sql = "select ip from bd_node_network where network_uuid  = ? ";
        $data = $this->dbSelect($sql,array($networkuuid));
        return $data[0]['ip'];
    }
    /**
     * @param $params 获取启动任务启动对象详细信息
     */
    public function getStartTaskObjectInfo($params){
        $volCdpHandler = Xphp::instance('VolCDPTaskHandler');
        $startObjectInfo = $volCdpHandler->getStartTaskObjectDetail($params);
        return json_encode($startObjectInfo);
    }


    public function getCopyDetails($params){
        $task_uuid = $params['uuid'];
        $sql = "select bcil.item_name as vm_name, bcil.source_timepoint_list,bcil.src_timepoint_count, bcil.write_size, bcil.total_copy_size, bcil.complete_size, bcil.transport_size, bcil.write_size, bcil.copy_status, bcil.item_uuid as vm_uuid, bcil.error_code, bcil.new_timepoint_list, bcil.vcenter_uuid, bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_transport_size, bri.current_object_write_size, bri.current_object_completed_size, bt.task_status as bd_task_status, bt.task_type 
        from backup_copy_item_list bcil 
        left join bd_running_info bri on bcil.task_uuid = bri.task_uuid
        left join bd_task bt on bt.task_uuid = bcil.task_uuid 
        where bcil.task_uuid = ? group by bcil.vcenter_uuid, bcil.item_uuid order by bcil.item_id";
        $data = $this -> dbSelect($sql, array($task_uuid));
        $records = array();
    	$i = 1;
    	$records["data"] = array();
    	foreach ($data as $d){
    		$timepointCount = intval($d['src_timepoint_count']);
    		$records["data"][] = array(
    			$i++,
    			$d['vm_name'],
    			$this->getCopyTimepointNum($timepointCount,$d['bd_task_status']),
    			$this->getVMListSize($d['bd_task_status'], $d['total_copy_size']),
                $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMSpeed($d['copy_status'], $d['speed'], $d['speed_time'],0,true),
                $this->getVMPercent($d['total_copy_size'], $d['current_object_completed_size'], $d['complete_size'], $d['bd_task_status'], $d['copy_status'],true),
                $this->getVMStatus($d['bd_task_status'], $d['copy_status'],true),
                $this->getErrorCodeDes($d['copy_status'], $d['error_code'],true),
    			$this->getVMDetailsInfo($d, $task_uuid),
        	);
    	}

    	$records["draw"] = $params['draw'];
    	return  json_encode($records);
    }

    public function getTapeGroupStrategy($groupUuid)
    {
        $sql = "select name, reserve_strategy_type, reserve_days, backup_set_strategy_type, backup_set_generated_days from bd_tape_group where group_uuid = ?";
        $sqlParams = [$groupUuid];
        $data = $this->dbSelect($sql, $sqlParams);

        if (empty($data)) {
            return '';
        }
        $d = $data[0];

        $backupSetDes = '';
        $reserveDes = '';

        // 生成策略
        if ($d['backup_set_strategy_type'] == 1) {
            $backupSetDes .= Xphp::$_lang['UI_TAPE_SELECT_GENERATE_STRATEGY1'];
        } else if ($d['backup_set_strategy_type'] == 2) {
            $backupSetDes .= Xphp::$_lang['UI_TAPE_SELECT_GENERATE_STRATEGY2'];
        } else if ($d['backup_set_strategy_type'] == 3){
            $backupSetDes .= Xphp::$_lang['UI_TAPE_SELECT_GENERATE_STRATEGY3'] . '(' . $d['backup_set_generated_days'] . ''.Xphp::$_lang['WEB_UTILS_DAY'].')';
        }

        // 保留策略
        if ($d['reserve_strategy_type'] == 1) {
            $reserveDes .= Xphp::$_lang['UI_TAPE_RESERVE_STRATEGY1'];
        } else if ($d['reserve_strategy_type'] == 2) {
            $reserveDes .= Xphp::$_lang['UI_TAPE_RESERVE_STRATEGY2'] . '(' . $d['reserve_days'] . ''.Xphp::$_lang['WEB_UTILS_DAY'].')';
        } else if ($d['reserve_strategy_type'] == 3){
            $reserveDes .= Xphp::$_lang['UI_TAPE_RESERVE_STRATEGY3'];
        }

        $info = array(
            'name' => $d['name'],
            'backup_set_strategy_type' => $d['backup_set_strategy_type'],
            'backup_set_generated_days' => $d['backup_set_generated_days'] ?? 0,
            'reserve_strategy_type' => $d['reserve_strategy_type'],
            'reserve_days' => $d['reserve_days'] ?? 0,
            'backup_set_strategy_des' => $backupSetDes,
            'reserve_strategy_des' => $reserveDes,
        );
        return $info;
    }

    /**
     * 检查用户是否具有足够的权限-用于详情页面
     * @param string $taskUuid 任务uuid
     * @param string $operate 操作描述
     * @return unknown 检查结果
     */

    public function checkOpPermission($taskUuid, $operate){
        
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr']) && $_SESSION['userUuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            // 没有全局观察者操作权限,有全局观察者查看权限，直接返回不能操作 包括自己创建的任务也不能操作，反正啥都不能干
            //超级管理员不受全局观察者权限影响
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], "warning"));
        }
        //不是全局观察者，也不是admin，才判断是否有操作权限
        if (
            !in_array('global_write', $_SESSION['permission'])
            && $_SESSION['userUuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'
        ) {
            $authUser = $_SESSION['authUser'];//当前用户关联的用户
            $userUuid = array_merge([$_SESSION['userUuid']], $authUser['current_job_operate'] ?? []);
            // 需要判断任务里面的用户uuid是否属于$userUuid里面
            $data = $this->dbSelect("select user_uuid from bd_task where task_uuid = ?", [$taskUuid]);
            if (!in_array($data[0]['user_uuid'],$userUuid) ) {
                // 表示有不属于自己的资源 那么提示错误
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], "warning"));
            }
        }
    }

    /**
     * 组合重试策略
     * @param {} $params
     * @return array
     */
    public function groupRetryStrategy($params = [])
    {
        $utils = Xphp::instance('Utils');
        $params['op_retry_flag'] =  $utils->parseBoolToFlag($params['op_retry_flag']);
        $params['task_retry_flag'] =  $utils->parseBoolToFlag($params['task_retry_flag']);
        return $params;
    }

    /**
     * 组合安全策略
     * @param mixed $safeConfigStartegy 安全策略
     * @param mixed  $storageUuid 存储设备uuid
     * @return mixed
     */
    public function groupSafeConfigStrategy($safeConfigStrategy, $storageUuid = '')
    {
        $utils = Xphp::instance('Utils');
        if ($storageUuid) {
            $sql = "SELECT storage_type FROM bd_storage_resource WHERE storage_uuid = ? ";
            $storageData = $this->dbSelect($sql, [$storageUuid]);
            if (is_array($storageData) && $storageData) {
                if ($storageData[0]['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['TAPE']) {
                    return [
                        'worm_flag' => $utils->parseBoolToFlag(false),
                        'worm_protection_time' => 0,
                        'virus_scan_flag' => $utils->parseBoolToFlag(false),
                        'virus_scan_config_list' => '',
                        'integrity_check_flag' => $utils->parseBoolToFlag(false),
                        'integrity_check_config' => [],
                    ];
                }
            }
        }
        $safeConfigStrategy['worm_flag'] =  $utils->parseBoolToFlag($safeConfigStrategy['worm_flag']);
        $safeConfigStrategy['virus_scan_flag'] =  $utils->parseBoolToFlag($safeConfigStrategy['virus_scan_flag']);
        $safeConfigStrategy['integrity_check_flag'] =  $utils->parseBoolToFlag($safeConfigStrategy['integrity_check_flag']);
        return $safeConfigStrategy;
    }

    /**
     * 获取状态提示信息
     * 根据操作状态、病毒状态和完整性状态生成相应的提示信息
     * 操作中的时间点
     * 
     * 
     * @param int $merge_status 合并状态
     * @param int $operation_status 操作状态码
     * @param int $virusStatus 病毒状态码
     * @param int $integrityStatus 完整性状态码
     * 
     * @return {
     * avaliable_flag:时间点可用标志，操作中的点不可恢复，其他状态均可恢复
     * status:状态码，1操作中，2正常，3异常
     * status_des:状态描述，例：该备份点已经被感染
     * } 提示信息 例：该备份点已经被感染
     * 1 操作中
     * 2 正常
     * 3 异常
     */
    public function getTimePointStatus($merge_status, $operation_status, $virus_scan_status, $integrity_check_status){
        
        // 操作状态码
        $OP_CODE = Xphp::$_config['OPERATION_STATUS'];
        // 病毒状态
        $VIRUS_STATUS = Xphp::$_config['VIRUS_STATUS'];
        // 完整性状态
        $INTEGRITY_STATUS = Xphp::$_config['INTEGRITY_STATUS'];
        // 合并状态
        $MERGE_STATUS = Xphp::$_config['MERGE_STATUS'];
        $return = [
            "avaliable_flag" => true,
            "status" => 2,
            "status_des" => Xphp::$_lang['UI_REPORT_NODE_NORMAL'],
        ];
        // 获取操作描述配置---操作状态优先显示
        if($operation_status != 0){
            // 时间点处于操作中
            $statusDes = $this->getOpStatusDes($operation_status);
            $avaliable_flag = true;
            if(($operation_status & $OP_CODE['MERGING']) || ($operation_status & $OP_CODE['DELETING'])) {
                $avaliable_flag = false;
            }
            $return = [
                "avaliable_flag" => $avaliable_flag,
                "status" => 1,
                "status_des" => str_replace('%s', $statusDes, Xphp::$_lang['UI_BACKUP_DATA_TIP_OPERATING']),
            ];
            return $return;
        }
        // 正常状态
        if($virus_scan_status !== $VIRUS_STATUS['INFECTED'] && $integrity_check_status !== $INTEGRITY_STATUS['BROKEN'] && $merge_status !== $MERGE_STATUS['FAILED'] && $merge_status !== $MERGE_STATUS['FAILED']){
            $return = [
                "avaliable_flag" => true,
                "status" => 2,
                "status_des" => Xphp::$_lang['UI_REPORT_NODE_NORMAL'],
            ];
            return $return;
        // 异常状态
        } else {
            $des = '';
            // 感染、合并失败、损坏
            if(($virus_scan_status == $VIRUS_STATUS['INFECTED'] || $virus_scan_status == $VIRUS_STATUS['INFECTED_PARTLY_SCAN']) && $integrity_check_status == $INTEGRITY_STATUS['BROKEN'] && $merge_status == $MERGE_STATUS['FAILED']){
                $des = Xphp::$_lang['UI_BACKUP_DATA_TIP_INFECTED_AND_BROKEN_AND_FAILED'];
            }
            // 合并失败、损坏
            if($integrity_check_status == $INTEGRITY_STATUS['BROKEN'] && $merge_status == $MERGE_STATUS['FAILED']){
                $des .= Xphp::$_lang['UI_BACKUP_DATA_TIP_BROKEN_AND_FAILED'];
            }
            // 感染、损坏
            if(($virus_scan_status == $VIRUS_STATUS['INFECTED'] || $virus_scan_status == $VIRUS_STATUS['INFECTED_PARTLY_SCAN']) && $integrity_check_status == $INTEGRITY_STATUS['BROKEN']){
                $des = Xphp::$_lang['UI_BACKUP_DATA_TIP_INFECTED_AND_BROKEN'];
            }
            // 感染且合并失败
            if(($virus_scan_status == $VIRUS_STATUS['INFECTED'] || $virus_scan_status == $VIRUS_STATUS['INFECTED_PARTLY_SCAN']) && $merge_status == $MERGE_STATUS['FAILED']){
                $des = Xphp::$_lang['UI_BACKUP_DATA_TIP_INFECTED_AND_FAILED'];
            }
            // 感染
            if(($virus_scan_status == $VIRUS_STATUS['INFECTED'] || $virus_scan_status == $VIRUS_STATUS['INFECTED_PARTLY_SCAN'])){
                $des = Xphp::$_lang['UI_BACKUP_DATA_TIP_INFECTED'];
            }
            // 数据损坏
            if($integrity_check_status == $INTEGRITY_STATUS['BROKEN']){
                $des = Xphp::$_lang['UI_BACKUP_DATA_TIP_BROKEN'];
            }
            // 合并失败
            if($merge_status == $MERGE_STATUS['FAILED'] || $merge_status == $MERGE_STATUS['DEPEND_MERGE_FAILED']){
                $des = Xphp::$_lang['UI_BACKUP_DATA_TIP_MERGE_FAILED'];
            }
            $return = [
                "avaliable_flag" => true,
                "status" => 3,
                "status_des" => $des,
            ];
        }
        return $return;
    }

    /**
     * 根据状态码获取状态描述字符串
     * 
     * 此函数接受一个状态码作为输入，然后根据该状态码与预定义的操作状态码进行   按位与运算，
     * 来判断当前状态包括哪些操作状态，并将这些操作状态的描述组合成一个字符串返回。
     * 主要用于将状态码转换为人类可读的描述信息。
     * 
     * @param int $status 当前的状态码，是与操作状态码进行位运算的依据。
     * @return string 当前状态的描述字符串，各描述信息之间用逗号和空格分隔。 例：“扫描中，校验中”
     */
    protected function getOpStatusDes($status)
    {
        $OP_CODE = Xphp::$_config['OPERATION_STATUS'];
        // 获取操作描述配置
        $OP_DES = [
            0 => Xphp::$_lang['UI_BACKUP_DATA_STATUS_NO_OPERATION'],
            1 => Xphp::$_lang['UI_BACKUP_DATA_STATUS_MERGING'],
            2 => Xphp::$_lang['UI_BACKUP_DATA_STATUS_DELETING'],
            4 => Xphp::$_lang['UI_BACKUP_DATA_STATUS_SCANNING'],
            8 => Xphp::$_lang['UI_BACKUP_DATA_STATUS_CHECKING'],
        ];
        // 0是未操作
        if ($status == 0){
            return $OP_DES[0];
        }
        // 初始化结果数组，用于存储当前状态对应的操作描述
        $result = [];

        // 检查当前状态是否包含合并操作
        if ($status & $OP_CODE['MERGING']) {
            $result[] = $OP_DES[$OP_CODE['MERGING']];
        }
        // 检查当前状态是否包含删除操作
        if ($status & $OP_CODE['DELETING']) {
            $result[] = $OP_DES[$OP_CODE['DELETING']];
        }
        // 检查当前状态是否包含扫描操作
        if ($status & $OP_CODE['SCANNING']) {
            $result[] = $OP_DES[$OP_CODE['SCANNING']];
        }
        // 检查当前状态是否包含验证操作
        if ($status & $OP_CODE['VERIFY']) {
            $result[] = $OP_DES[$OP_CODE['VERIFY']];
        }
        // 将结果数组中的描述信息组合成一个字符串返回
        return implode(', ', $result);
    }
}
?>