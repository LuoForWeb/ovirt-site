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
            'bt.task_status', 'bri.speed','bri.current_total_size', 'bu.user_name', ''
        );
        
        $taskName = $search['name'];
        $accurateFlag = $params['accurateFlag'];
//         任务名	模块类型	任务类型	创建时间	状态	速度	创建者	操作
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time, 
                        bt.task_status, bt.strategy_id,
                		bri.total_size, bri.current_total_size, bri.speed, bri.speed_time, bu.user_name
                from bd_task bt, bd_running_info bri, bd_user bu 
                where bt.task_uuid = bri.task_uuid and 
                	 bt.user_uuid = bu.user_uuid and 
                     bt.delete_flag = 2 and bt.task_type != ? and bt.task_type != ? ";
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  bt.delete_flag = 2 and bt.task_type != ? and bt.task_type != ? and bt.user_uuid = bu.user_uuid ";
        
        $sqlParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK'], Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        $sqlCountParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK'],  Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        if(!empty($taskName) && !$accurateFlag){
            //按任务名搜索
            $sql .= " and bt.task_name like '%" . $taskName . "%' ";
            $sqlCount .= " and bt.task_name like '%" . $taskName . "%' ";
        }
        
        //获取当前用户所拥有的用户
        $userType = intval($_SESSION['userType']);
        $userGroup = $this->getUserGroups(Xphp::$_user['username'], $userType);
        $users = implode("','", $userGroup);
        
        if(!$accurateFlag){
        	$sql .= "and bu.user_name in ('". $users ."') ";
        	$sqlCount .= "and bu.user_name in ('". $users ."') ";
        	$sqlParams = array_merge($sqlParams, array( $start, $length));
        }else{
        	$search = $params['search'];
        	$createUser = $search['createUser'];
        	$hypervisor = intval($search['hypervisor']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
        	$taskStatus =intval($search['taskStatus']);
        	$taskName = $search['taskName'];
//         	$vmname = $search['vmname'];
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	$nodeuuid = $search['nodeuuid'];
        	 
        	if($moduleType && $moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
        		$sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id,
                		bri.total_size, bri.current_total_size, bri.speed, bri.speed_time, bu.user_name
                from bd_task bt, bd_running_info bri, bd_user bu, vm_task vt
                where bt.task_uuid = vt.task_uuid and
        			bt.task_uuid = bri.task_uuid and
                	 bt.user_uuid = bu.user_uuid and
                     bt.delete_flag = 2 and bt.task_type != ? and bt.task_type != ? ";
        		$sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu, vm_task vt where bt.task_uuid = vt.task_uuid and bt.user_uuid = bu.user_uuid  and  bt.delete_flag = 2 and bt.task_type != ? and bt.task_type != ? ";
        		$sqlParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK'], Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        		$sqlCountParams = array(Xphp::$_config['TASKTYPE']['ORCH_TASK'],  Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        		if($hypervisor != 0){
        			$sql .= " and vt.hypervisor_type =? ";
        			$sqlCount .= " and vt.hypervisor_type =? ";
        			$sqlParams = array_merge($sqlParams, array($hypervisor));
        			$sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
        		}
        	}
        	
        	//判断输入的用户名是否在当前用户的管理范围
        	if(in_array($createUser, $userGroup)){
        		$sql .= " and bu.user_name = ? ";
        		$sqlCount .= " and bu.user_name = ? ";
        		$sqlParams = array_merge($sqlParams, array($createUser, $start, $length));
        		$sqlCountParams = array_merge($sqlCountParams, array($createUser));
        	}else if(!$createUser){
        		$sql .= " and bu.user_name in ('". $users ."') ";
        		$sqlCount .= " and bu.user_name in ('". $users ."') ";
        		$sqlParams = array_merge($sqlParams, array($start, $length));
        	}else{
        		$sql .= " and bu.user_name = ? ";
        		$sqlCount .= " and bu.user_name = ? "; 
        		$createUser = '';
        		$sqlParams = array_merge($sqlParams, array($createUser, $start, $length));
        		$sqlCountParams = array_merge($sqlCountParams, array($createUser));
        	
        	}
        	 
        	$sql .= "  and
        			(bt.task_type = ". $taskType ." or ". $taskType ." = '') and
        			(bt.module_type = ". $moduleType ." or ". $moduleType ." = '') and
        			(bt.task_status = ". $taskStatus ." or ". $taskStatus ." = '') and
        			bt.task_name like '%" . $taskName . "%' ";
        	$sqlCount .= "  and
        			(bt.task_type = ". $taskType ." or ". $taskType ." = '') and
        			(bt.module_type = ". $moduleType ." or ". $moduleType ." = '') and
        			(bt.task_status = ". $taskStatus ." or ". $taskStatus ." = '') and
        			bt.task_name like '%" . $taskName . "%' ";
        	
        	if($nodeuuid){
        		$sql .= " and (bt.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '') ";
        		$sqlCount .= " and (bt.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '') ";
        	}
        	 
        	//如果填了开始时间范围查询
        	if($startTime && $endTime){
        		$sql .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
        		$sqlCount .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
        	}
        }
        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        foreach ($data as $d){
            $records["data"][] = array(
                $d['task_name'],
                $this->getCurrentJobsModuleType($d['module_type'], $d['task_uuid']),
                $ptDes['TASKTYPEDES'][$d['task_type']],
                $this->parseDate($d['create_time']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
            	$this->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], false, $d['task_type']),
                $d['user_name'],
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d),
            	$d['module_type'],
            	$d['task_status'],
            	$this->getBackupStrategy($d['strategy_id'])
            );
        }
        
        $utils = Xphp::instance('Utils');
//         $records["data"] = $utils->arraySort($records["data"], $sortColumn, $sortType, $start, $length);  //排序
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
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
    		$info[] = intval($d['mode']);
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
     */
    private function getCurrentJobsModuleType($moduleType, $taskuuid){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $moduleTypeDes = $ptDes['MODULE_TYPE_DES'][$moduleType];
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $hypervisor = intval($data[0]['hypervisor_type']);
//             $moduleTypeDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$hypervisor] . "]";
            $moduleTypeDes = Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
        }
        return $moduleTypeDes;
    }
    
    /**
     * 得到任务速度
     * @param int $status
     * @param int $speed
     * @return string
     */
    private function getCurrentJobSpeed($status, $speed, $speedTime){
        if(time() - $speedTime > 12){
            $speed = 0;
        }
        if($status == Xphp::$_config['TASKSTATUS']['RUNNING']){
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
     * 得到每个任务的额外信息展示            策略信息/保留策略/存储信息/其他信息    
     * @param array $d 每一条数据
     * @return array
     */
    private function getJobOtherInfo($d){
        $info = array();
        $info['timeStrategy'] = $this->getJobTimeStrategy($d['strategy_id']);
        $reservedStrategy = false;
        $instantInfo = false;
        $motionInfo = false;
        $grainInfo = false;
        $transportStrategy = false;
        if(Xphp::$_config['TASKTYPE']['BACKUP'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['BACKUP_COPY'] == intval($d['task_type'])){
            //如果是备份任务,获取保留策略
            $reservedStrategy = $this->getReservedStrategy($d['strategy_id']);
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
        
        if(Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'] == intval($d['task_type'])){
        	//如果是副本回传任务
        	$transportStrategy = $this->getTransportInfo($d['task_uuid']);
        }
        $info['reservedStrategy'] = $reservedStrategy;
        $info['transportStrategy'] = $transportStrategy;
        $info['instantDetail'] = $instantInfo; 
        $info['motionDetail'] = $motionInfo;
        $info['grainDetail'] = $grainInfo;
        //TODO  根据情况,添加额外信息,会发送到界面统一处理        
        return $info;
    }
    
    /**
     * 获取副本回传传输策略
     * @param string $taskuuid
     */
    private function getTransportInfo($taskuuid){
    	$sql = 'select bts.encrypt_flag, bts.compress_flag from bd_task bt, bd_transport_strategy bts where bt.strategy_id = bts.strategy_id and bt.task_uuid = ?';
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$info = array(
    		'encrypt_flag' => $this->getStrategySwitch(intval($data[0]['encrypt_flag'])),
    		'compress_flag' => $this->getStrategySwitch(intval($data[0]['compress_flag']))
    	);
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
        $this->paramsCheck($strategyID);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time 
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $utils = Xphp::instance('Utils');
        $strategy = array();
        foreach ($data as $d){
        	$startTime = $d['start_time'];
        	if(intval($d['strategy_type']) == Xphp::$_config['STRATEGY_TYPE']['ONCE'] && strtotime($d['start_time']) < time()){
        		$startTime = $d['start_time'].'('.Xphp::$_lang['UI_PUBLIC_EXPIRED'].')';
        	}
            $strategy[] = array(
                'mode' => $d['mode'],
                'type' => $d['strategy_type'],
                'days' => $this->getDaysArr($d['days']),
                'startTime' => $startTime,
                'rollFlag' => Xphp::$_config['FLAG']['SET'] == intval($d['roll_flag']) ? true : false ,
                'rollInterval' => $utils->secToTime($d['roll_interval']),
                'endTime' => $d['roll_end_time'],
            );
        }
        return $strategy;
    }
    
    /**
     * 得到天的数组
     * @param unknown $days
     */
    private function getDaysArr($days){
        $count = strlen($days);
        $daysArr = array();
        for ($i =0; $i<$count; $i++){
            $daysArr[] = intval(substr($days, $i, 1));
        }
        return $daysArr;
    }
    
    /**
     * 得到保留策略信息
     * @param int $strategyID
     * @return array
     */
    private function getReservedStrategy($strategyID){
        $this->paramsCheck($strategyID);
        $sql = "select strategy_type, number, auto_archive from bd_reserved_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $reserved = false;
        foreach ($data as $d){
            $reserved = array(
                'type' => $d['strategy_type'],
                'value' => $d['number'],
                'archive' => $d['auto_archive'],
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
        //加密传输,XenServer使用
        $sql = "select encrypt_flag from bd_transport_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
        return $info;
    }
    
    /**
     * 得到副本任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getCopyTransportStrategy($taskuuid, $strategyid){
    	$info = array();
    	$sql = "select encrypt_flag, compress_flag from bd_transport_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql, array($strategyid));
    	$info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['encrypt_flag']);
    	$info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $data[0]['compress_flag']);
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
     * @return array 启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备
     */
    private function getTaskOpCodeByType($taskuuid,$tasktype, $status){
    	$status = intval($tasktype);
    	$this->paramsCheck($tasktype);
    	$taskTypeConf = Xphp::$_config['TASKTYPE'];
    	$opCode = array();
    	switch ($tasktype){
    		case $taskTypeConf['BACKUP']:
    			$opCode = array( 8, 10, 7, 6, 2, 3, 4);
    			break;
    		case $taskTypeConf['RECOVERY']:
    			$opCode = array(1, 2, 4);
    			break;
    		case $taskTypeConf['VM_INSTANT_RECOVERY']:
    			$opCode = array(1, 9, 2, 4);
    			break;
    		case $taskTypeConf['VM_INSTANT_RECOVERY_MOTION']:
    			$opCode = array(2);
    			break;
			case $taskTypeConf['VM_FILE_RECOVERY']:
			    $opCode = array(1, 2, 4);
			    break;
			case $taskTypeConf['VM_CDP_BACKUP']:
			    $opCode = array(1, 2, 3, 4);
			    break;
		    case $taskTypeConf['BACKUP_COPY']:
		        $opCode = array(8, 1, 2, 3, 4, 5);
		        break;
	        case $taskTypeConf['BACKUP_COPY_FETCH']:
	            $opCode = array(1, 2, 4, 5);
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
        $sql = "select bri.speed, bri.speed_time, bt.task_status from bd_running_info bri, bd_task bt 
                where bri.task_uuid = bt.task_uuid and bri.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $speed = 0;
        if($data){
            if($data[0]['task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] || 
                $data[0]['task_status'] == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
                $speed = round($data[0]['speed'] / 1024, 2);
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
     * 任务详情: 得到任务基本信息
     * @param unknown $params
     */
    public function getBasicInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, 
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, 
                	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time, 
                		bri.total_size, bri.current_total_size, bri.speed, unix_timestamp(bri.start_time) start_time
                from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs   
                where bt.user_uuid = bu.user_uuid and 
                      bt.task_uuid = bri.task_uuid and 
                      bt.strategy_id = bs.strategy_id and 
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
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']], 
                                $d['module_type'], $d['task_uuid']),
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'statusValue' => intval($d['task_status']),
                'totalSize' => $utils->calSize($d['total_size']),
                'currentSize' => $utils->calSize($d['current_total_size']),
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], false),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], true),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id']),
                'transportStrategy' => $this->getTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid']),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => ""
            );
            //如果是虚拟机备份添加备份模式配置
            if(intval($d['module_type']) == Xphp::$_config['MODULE_TYPE']['VM']){
                $basicInfo['modeStrategy'] = $this->getVMBackupModeStrategy($d['task_uuid']);
            }
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
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time,
                	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
                		bri.total_size, bri.current_total_size, bri.speed, unix_timestamp(bri.start_time) start_time
                from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs
                where bt.user_uuid = bu.user_uuid and
                      bt.task_uuid = bri.task_uuid and
                      bt.strategy_id = bs.strategy_id and
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
    		$basicInfo = array(
    				'taskName' => $d['task_name'],
    				'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
    				'taskType' => $this->getBasicInfoTaskTypeDes($ptDes['TASKTYPEDES'][$d['task_type']],
    						$d['module_type'], $d['task_uuid']),
    				'user' => $d['user_name'],
    				'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
    				'statusValue' => intval($d['task_status']),
    				'totalSize' => $utils->calSize($d['total_size']),
    				'currentSize' => $utils->calSize($d['current_total_size']),
    				'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
    				'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], false),
    				'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], true),
    				'createTime' => $this->parseDate($d['create_time']),
    				'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
    				'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
    				'endTime' => $this->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed']),
    				'nextTime' => $this->getNextStartTime($d['next_start_time']),
    				'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
    				'reservedStrategy' => $this->getReservedStrategy($d['strategy_id']),
    				'transportStrategy' => $this->getCopyTransportStrategy($taskUUID, $d['strategy_id']),
    				'storageInfo' => $this->getCopyStorageInfo($d['task_uuid'], $d['storage_uuid']),
    				'flag' => true,
    				'taskTypeFlag' => intval($d['task_type']),
    		);
    	}
    	return json_encode($basicInfo);
    }
    
    /**
     * 得到虚拟机备份模式配置信息
     * 静默快照,高速模式
     * @param string $taskuuid
     * @return array
     */
    private function getVMBackupModeStrategy($taskuuid){
        $sql = "select level, quiesce_snapshot, hypervisor_type, valid_data_backup, serial_snapshot_flag, parse_fs_flag, 
                not_backup_swap_file_flag, not_backup_deleted_file_flag, not_backup_partition_gap_flag 
                 from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $utils = Xphp::instance('Utils');
        $mode = array(
            'hypervisor' => intval($data[0]['hypervisor_type']),
            'serialSnapshot' => intval($data[0]['serial_snapshot_flag']),
            'highLevel' => !($utils->parseFlagToBool($data[0]['level'])),
        	'cbtMode' => $utils->parseFlagToBool($data[0]['valid_data_backup']),
            'incMode' => $this->getIncModeDes(intval($data[0]['level'])),
            'quiesceSnapshot' => $utils->parseFlagToBool($data[0]['quiesce_snapshot']),
            'parseFs' => $utils->parseFlagToBool($data[0]['parse_fs_flag']),
            'noSwapFile' => $utils->parseFlagToBool($data[0]['not_backup_swap_file_flag']),
            'noDeletedFile' => $utils->parseFlagToBool($data[0]['not_backup_deleted_file_flag']),
            'noPartitionGap' => $utils->parseFlagToBool($data[0]['not_backup_partition_gap_flag']),
        );
        return $mode;
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
            $taskTypeDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$hypervisor] . "]";
        }
        return $taskTypeDes;
    } 
    
    /**
     * 得到当前任务节点和存储信息
     * @param string $nodeuuid
     * @param string $storageuuid
     */
    private function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid){
        $info = array('flag' => false);
        if(Xphp::$_config['TASKTYPE']['BACKUP'] != $tasktype){
            return $info;
        }
        $info['flag'] = true;
        $utils = Xphp::instance('Utils');
        //节点信息
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if($data){
            $nodeHandler = Xphp::instance('NodeHandler');
            $info['node'] = array(
                'name' => $nodeHandler->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
                'ip' => $data[0]['ip'],
            );
        }
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        if($data){
            $storageHandler = Xphp::instance('StorageHandler');
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => $storageHandler->getStorageTypeDes($data[0]['storage_type']),
                'size' => $utils->calSize($data[0]['total_size']),
                'freesize' => $utils->calSize($data[0]['free_size']),
            );
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if($data){
            $info['high'] = array(
                'deduplication' => $utils->parseFlagToBool( $data[0]['deduplication_flag']),
                'blocksize' => $data[0]['block_size'] . "KB",
                'compressed' => $utils->parseFlagToBool( $data[0]['compressed_flag']),
            );
        }
        return $info;
    }
    
    private function getCopyStorageInfo($taskuuid, $storageuuid){
    	$info = array('flag' => false);
    	$info['flag'] = true;
    	$info['remoteflag'] = false;
    	$info['storage'] = array();
    	$utils = Xphp::instance('Utils');
    	//存储信息
    	$sql = "select storage_nickname, storage_type, total_size, free_size, storage_config from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	if($data){
    		$storageHandler = Xphp::instance('StorageHandler');
    		if($data[0]['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
    			$config = json_decode($data[0]['storage_config'], true);
    			$info['remoteflag'] = true;
    			$info['remote'] = array(
    					'name' => $data[0]['storage_nickname'],
    					'ip' => $config['remote_ip']." : ".$config['remote_port']
    			);
    			$sqlRemote = "select real_storage_name, real_storage_type, real_storage_total_size, real_storage_free_size from vm_backup_copy_task where task_uuid = ?";
    			$dataRemote = $this->dbSelect($sqlRemote, array($taskuuid));
    			$info['storage'] = array(
    				'name' => $dataRemote[0]['real_storage_name'],
    				'type' => $storageHandler->getStorageTypeDes($dataRemote[0]['real_storage_type']),
    				'size' => $utils->calSize($dataRemote[0]['real_storage_total_size']),
    				'freesize' => $utils->calSize($dataRemote[0]['real_storage_free_size']),
    			);
    		}else{
    			$info['storage'] = array(
    				'name' => $data[0]['storage_nickname'],
    				'type' => $storageHandler->getStorageTypeDes($data[0]['storage_type']),
    				'size' => $utils->calSize($data[0]['total_size']),
    				'freesize' => $utils->calSize($data[0]['free_size']),
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
            $status == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            $systemHandler = Xphp::instance('SystemHandler');
            $nowTime = $systemHandler->getSystemTime();
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
        if($tasktype == Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] || $tasktype == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY']){
            //细粒度恢复不显示进度
            return Xphp::$_config['NULLSPACE'];
        }
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] && 
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL'] && $taskStatus != Xphp::$_config['TASKSTATUS']['PAUSED']){
            //如果任务没有运行
            if($percentFlag){
                return "0%";
            }else{
                return Xphp::$_config['NULLSPACE'];
            }
        }
        $utils = Xphp::instance('Utils');
        $speed = $utils->calPercent($totalSize, $currentSize);
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
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select vml.vm_name, vml.mode, vml.vm_size, vml.vm_valid_size, vml.completed_size, vml.transport_size, vml.real_size, vml.task_status, vml.dir_path, vml.new_name, 
                       vml.vm_uuid, vml.error_code, vml.datastore, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid, 
                		bri.speed, bri.speed_time, bri.current_transport_size, bri.total_real_size, bri.current_real_size, bri.current_completed_size,
                        bt.task_status as bd_task_status, bt.task_type 
                from vm_machine_list vml 
                left join bd_running_info bri 
                on vml.task_uuid = bri.task_uuid 
                left join bd_task bt 
                on bt.task_uuid = vml.task_uuid  
                where vml.task_uuid = ? group by vml.vm_uuid order by vml.machine_id";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $records["data"] = array();
        foreach ($data as $d){
            //如果任务正在运行,需要过滤掉新添加的虚拟机
            if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] && 
               $d['task_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
                continue;
            }
            $records["data"][] = array(
                $i++,
                $d['vm_name'],
                $this->getCurrentVMListTaskType($d['task_type'], $d['mode'], $d['bd_task_status']),
                $this->getVMListSize($d['bd_task_status'], $d['vm_size']),
            	$this->getVMListSize($d['bd_task_status'], $d['vm_valid_size']),
                $this->getVMCompletedSize($d['current_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['task_status']),
                $this->getVMCompletedSize($d['current_real_size'], $d['real_size'], $d['bd_task_status'], $d['task_status']),
                $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getVMPercent($d['vm_size'], $d['current_completed_size'], $d['completed_size'], $d['bd_task_status'], $d['task_status']),
                $this->getVMStatus($d['bd_task_status'], $d['task_status']),
                $this->getErrorCodeDes($d['task_status'], $d['error_code']),
                $this->getVMDetailsInfo($d, $taskUUID),
            );
        }
        
        $records["draw"] = $params['draw'];
        return  json_encode($records);
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
    	$sql = "select bcvl.vm_name, bcvl.source_timepoint_list,bcvl.src_timepoint_count, bcvl.real_size, bcvl.total_copy_size, bcvl.complete_size, bcvl.transport_size, bcvl.real_size, bcvl.copy_status,
                       bcvl.vm_uuid, bcvl.error_code, bcvl.new_timepoint_list, bcvl.vcenter_uuid,
                		bri.speed, bri.speed_time, bri.current_transport_size, bri.total_real_size, bri.current_real_size, bri.current_completed_size,
                        bt.task_status as bd_task_status, bt.task_type
                from backup_copy_vm_list bcvl
                left join bd_running_info bri
                on bcvl.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = bcvl.task_uuid
                where bcvl.task_uuid = ? group by bcvl.vm_uuid order by bcvl.vm_id";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$records = array();
    	$i = 1;
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$records["data"] = array();
    	foreach ($data as $d){
    		//如果任务正在运行,需要过滤掉新添加的虚拟机
    		if($d['bd_task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING'] &&
    				$d['copy_status'] == Xphp::$_config['VmTaskStatus']['NEW_ADD']){
    			continue;
    		}
    		$timepointCount = intval($d['src_timepoint_count']);
    		$records["data"][] = array(
    			$i++,
    			$d['vm_name'],
    			$this->getCopyTimepointNum($timepointCount,$d['bd_task_status']),
    			$this->getVMListSize($d['bd_task_status'], $d['total_copy_size']),
                $this->getVMCompletedSize($d['current_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['copy_status']),
                $this->getVMCompletedSize($d['current_real_size'], $d['real_size'], $d['bd_task_status'], $d['copy_status']),
                $this->getVMSpeed($d['copy_status'], $d['speed'], $d['speed_time']),
                $this->getVMPercent($d['total_copy_size'], $d['current_completed_size'], $d['complete_size'], $d['bd_task_status'], $d['copy_status']),
                $this->getVMStatus($d['bd_task_status'], $d['copy_status']),
                $this->getErrorCodeDes($d['copy_status'], $d['error_code']),
    			$this->getVMDetailsInfo($d, $taskUUID),
        	);
    	}
    
    	$records["draw"] = $params['draw'];
    	return  json_encode($records);
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
            return $this->getBackupDetailsInfo($d);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['RECOVERY']){
            return $this->getRecoveryDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION']){
            return $this->getMotionDetailsInfo($d, $taskuuid);
        }elseif($d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
        	return $this->getCopyDetailsInfo($d, $taskuuid);
        }
    }
    
    /**
     * 得到备份任务虚拟机详情
     * @param array $d
     */
    private function getBackupDetailsInfo($d){
        $info = array(
            "type" => intval($d['task_type']),
            "sPath" => $d['dir_path'],
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
    	if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']){

    		$desHostInfo = $this->getOpenstackDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
    	}else{
    		$desHostInfo = $this->getDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
    	}
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $info = array(
            "type" => intval($d['task_type']),
            "path" => $d['dir_path'],
            "dVcenterIP" => $desHostInfo['vcenterIP'],
            "dHostIP" => $desHostInfo['hostIP'],
            "dName" => $d['new_name'],
            "timepoint" => $timepointInfo['timepoint'],
            "taskname" => $timepointInfo['taskname'],
            "storage" => $d['datastore'],
        );
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
        $sql = "select vh.host_ip, vv.vcenter_ip, vv.detail 
                from  vm_host vh, vm_vcenter vv 
                where vh.vcenter_uuid = vv.vcenter_uuid
                and vv.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        if($data){
        	$detail = json_decode($data[0]['detail'],true);
        	$tenant = $detail['tenant_name'];
            return array(
                "hostIP" => $data[0]['host_ip'].'('.$tenant.')',
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
     * 得到虚拟机列表百分比
     * @param int $vmSize
     * @param int $briCompletedSize
     * @param int $vmlCompletedSize
     * @param int $bdTaskStatus
     * @param int $vmTaskStatus
     * @return string
     */
    private function getVMPercent($vmSize, $briCompletedSize, $vmlCompletedSize, $bdTaskStatus, $vmTaskStatus){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] || 
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //首先任务是在运行状态
            $utils = Xphp::instance('Utils');
            if($vmTaskStatus == Xphp::$_config['VmTaskStatus']['RUNNING']){
                return $utils->calPercent($vmSize, $briCompletedSize);
            }elseif($vmTaskStatus == Xphp::$_config['VmTaskStatus']['WAITTING']){
                return '0%';
            }else{
                if($vmSize == $vmlCompletedSize) return '100%';
                return $utils->calPercent($vmSize, $vmlCompletedSize);
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
     * @return string
     */
    private function getVMCompletedSize($briCompletedSize, $vmlCompletedSize, $bdTaskStatus, $vmTaskStatus){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] || 
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //首先任务是在运行状态
            $utils = Xphp::instance('Utils');
            if($vmTaskStatus == Xphp::$_config['VmTaskStatus']['RUNNING'] ){
                //虚拟机是运行状态,显示bd_running_info的完成大小
                return $utils->calSize($briCompletedSize);
            }elseif($vmTaskStatus == Xphp::$_config['VmTaskStatus']['WAITTING'] ){
                //虚拟机是等待状态,显示0B
                return "0B";
            }else{
                //虚拟机不在运行状态,显示vm_machine_list的完成大小
                return $utils->calSize($vmlCompletedSize, true);
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
    private function getVMStatus($bdTaskStatus, $vmTaskStatus){
        if($bdTaskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] || 
            $bdTaskStatus == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //任务在运行状态时,显示虚拟机的状态
            $vmDes = include APP_PATH . 'vm/VmDescription.php';
            return $vmDes['VmTaskStatus'][$vmTaskStatus];
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
            $status == Xphp::$_config['TASKSTATUS']['ABNORMAL'] ){
            return $this->parseDate($startTime);
        }
        return Xphp::$_config['TIMESPACE'];
    }
    
    /**
     * 过滤下次启动时间
     * @param unknown $nextTime
     */
    public function getNextStartTime($nextTime){
        $nowTime = time();
        if($nextTime < $nowTime){
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
    private function getErrorCodeDes($status, $errorCode){
        if(Xphp::$_config['TASKSTATUS']['ABNORMAL'] == intval($status) || 
            Xphp::$_config['TASKSTATUS']['ERROR'] == intval($status) ||
            Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] == intval($status) ){
            $error = include CONF_PATH . "error.php";
            $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
        }else{
            $des = '';
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
//             $error = include CONF_PATH . "error.php";
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
    		//             $error = include CONF_PATH . "error.php";
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
    private function getVMSpeed($status, $speed, $speedTime){
        if(time() - $speedTime > 12){
            $speed = 0;
        }
        if(Xphp::$_config['TASKSTATUS']['RUNNING'] == intval($status) || 
            Xphp::$_config['TASKSTATUS']['ABNORMAL'] == intval($status)){
            $utils = Xphp::instance('Utils');
            $speed = $utils->calSpeed($speed);
        }else{
            $speed = '--';
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
        $sortArr = array('', 'task_type', 'error_code', 'total_size', '', 'total_transport_size', 
            'real_size', 'start_time', 'finish_time'
        );
        $sql = "select task_type, module_type, current_mode, error_code, details, total_size, total_transport_size, real_size, 
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
                $this->getHistoryTaskType($d['task_type'], $d['current_mode']),
                $this->getHistoryJobResultDes($d['error_code']),
                $utils->calSize($d['total_size'], true),
            	$this->getTotalValidSize($details, intval($d['module_type']), intval($d['task_type'])),
                $utils->calSize($d['total_transport_size'], true),
                $utils->calSize($d['real_size'], true),
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
    	$sortArr = array('', 'task_type', 'error_code', 'total_size','total_transport_size',
    			'real_size', 'start_time', 'finish_time'
    	);
    	$sql = "select task_type, module_type, current_mode, error_code, details, total_size, total_transport_size, real_size,
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
	    		$this->getHistoryTaskType($d['task_type'], $d['current_mode']),
	    		$this->getHistoryJobResultDes($d['error_code']),
	    		$utils->calSize($d['total_size'], true),
	    		$utils->calSize($d['total_transport_size'], true),
	    		$utils->calSize($d['real_size'], true),
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
    	$sortArr = array('', 'task_type', 'error_code', 'total_size', 'total_completed_size',
    			'real_size', 'start_time', 'finish_time'
    	);
    	$sql = "select task_type, module_type, current_mode, error_code, details, total_size, total_transport_size, real_size,total_completed_size,
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
	    		$this->getHistoryTaskType($d['task_type'], $d['current_mode']),
    			$this->getHistoryJobResultDes($d['error_code']),
    			$utils->calSize($d['total_size'], true),
	    		$utils->calSize($d['total_completed_size'], true),
	    		$utils->calSize($d['real_size'], true),
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
     * 得到虚拟机列表任务状态
     * @param int $taskType
     * @param int $currentMode
     * @param int $taskStatus
     * @return string
     */
    private function getCurrentVMListTaskType($taskType, $currentMode, $taskStatus){
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] && 
            $taskStatus != Xphp::$_config['TASKSTATUS']['ABNORMAL']){
            //如果任务不在运行状态,这个时候不知道任务时什么类型的
            return Xphp::$_config['NULLSPACE'];
        }
        return $this->getHistoryTaskType($taskType, $currentMode);
    }
    
    /**
     * 得到历史任务的任务类型描述  
     * @param int $taskType
     * @param int $currentMode
     * @return string
     */
    private function getHistoryTaskType($taskType, $currentMode){
//         $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = '';
        if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
            $des = Xphp::$_pfdes['BACKUP_MODE_DES'][$currentMode];
        }
        $des .= Xphp::$_pfdes['TASKTYPEDES'][$taskType];
        return $des;
    }
    
    /**
     * 根据模块统一发送消息
     * @param array $params
     * @return Ambigous|boolean
     */
    private function opUnifyMsg($params, $opName){
        $uuid = $params['uuid'];
        $module = intval($params['module']);
        $subModule = intval($params['subModule']);
        $this->paramsCheck($uuid, $module);
        
        $msg = json_encode(array('task_uuid'=>$uuid));
        if('BD_TASK_OP_BACKUP_DELETE' == $opName || 'BD_TASK_OP_RECOVERY_DELETE' == $opName 
            || 'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK' == $opName || 
            'VM_PRIVATE_TASK_OP_DELETE_ORCH_TASK' == $opName || 
            'BD_TASK_OP_CDP_BACKUP_DELETE' == $opName || 
            'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK' == $opName ||
            'BD_TASK_OP_BACKUP_COPY_DELETE' == $opName ||
            'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE' == $opName){
            $msg = json_encode(array('task_uuid_list'=>array($uuid)));
        }
        
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid, false);
        
        switch ($module){
            case $moduleType['VM']:
                $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['FS']:
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
            default:
                $mbResult = array('result'=>false, 'msg'=>'');
                break;
        }
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
     * 检查任务操作的任务类型参数
     * @param int $taskType
     * @return boolean
     */
    private function checkTaskType($taskType){
        $taskType = intval($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType || $taskTypeConf['RECOVERY'] == $taskType || 
            $taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType || $taskTypeConf['VM_INSTANT_RECOVERY_MOTION'] == $taskType || 
            $taskTypeConf['ORCH_TASK'] == $taskType || $taskTypeConf['VM_CDP_BACKUP'] == $taskType || 
            $taskTypeConf['VM_FILE_RECOVERY'] == $taskType || 
            $taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
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
            case $moduleType['FS']:
                $this->fileBackupTaskStartCheck($taskUUID);
                break;
            case $moduleType['DB']:
                $this->dbBackupTaskStartCheck($taskUUID, $subModule);
                break;
            case $moduleType['OS']:
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
        $this->paramsCheck($uuid, $module);
        $taskType = $params['taskType'];
        $startType = intval($params['startType']);
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType){
            //启动备份任务
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_BACKUP_START';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            //启动恢复任务
            $this->recoveryTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_RECOVERY_START';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //启动瞬时恢复任务
            $this->vmInstantTaskStartCheck($uuid);
            $opName = 'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_FILE_RECOVERY'] == $taskType){
            //启动细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_START_GRAIN_RECOVERY_TASK';
        }elseif($taskTypeConf['ORCH_TASK'] == $taskType){
            //启动演练任务
//             $this->vmInstantTaskStartCheck($uuid);
            $opName = 'VM_PRIVATE_TASK_OP_START_ORCH_TASK';
        }elseif($taskTypeConf['VM_CDP_BACKUP'] == $taskType){
            //启动CDP任务
            $this->backupTaskStartCheck($uuid, $module, $subModule);
            $opName = 'BD_TASK_OP_CDP_BACKUP_START';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType){
            //启动副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_BACKUP_COPY_CONTINUE';
            }
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
            //启动副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE';
            }
        }
        
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
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid);
        
        switch ($module){
            case $moduleType['VM']:
                $mbResult = $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
                break;
            case $moduleType['FS']:
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
            default:
                $mbResult = array('result'=>false, 'msg'=>'');
                break;
        }
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
     * 暂停任务
     * @param unknown $params
     */
    public function pauseJob($params){
        $taskType = $params['taskType'];
        $moduleType = $params['moduletype'];
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_PAUSE';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_PAUSE';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType){
            //暂停副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_PAUSE';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
            //暂停副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE';
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
        if($taskTypeConf['BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
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
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType){
            //停止副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_STOP';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
            //停止副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_STOP';
        }
        
        return $this->opUnifyMsg($params, $opName);
    }
    
    /**
     * 删除任务
     * @param unknown $params
     */
    public function deleteJob($params){
        $taskType = $params['taskType'];
        $this->checkTaskType($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
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
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType){
            //删除副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_DELETE';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
            //删除副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE';
        }
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
        return $operate;
    }
    
    /**
     * 得到历史任务
     * @param unknown $params
     */
    public function getHistoryJobs($params){
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
        $name = $search['name'];
        $draw = $params['draw'];
        $accurateFlag = $params['accurateFlag'];
        $sortArr = array('', '', 'task_name', 'submodule_type', 'task_type', 'user_name', 
            'total_size', '', '', 'real_size', 'start_time', 'finish_time', 'error_code'
        );
        
        $sql = "select id, task_name, module_type, submodule_type, task_type, current_mode, error_code, details, total_size, total_transport_size, total_completed_size, 
                real_size, average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, user_name, task_uuid from bd_history_task ";
        $sqlCount = "select count(task_uuid) as total from bd_history_task ";
        
        //获取当前用户所拥有的用户
        $userType = intval($_SESSION['userType']);
        $userGroup = $this->getUserGroups(Xphp::$_user['username'], $userType);
        $users = implode("','", $userGroup);
        if(!$accurateFlag){
        	$sql .= "where user_name in ('". $users ."') ";
        	$sqlCount .= "where user_name in ('". $users ."') ";
        }
        
        if(!empty($name) && !$accurateFlag){
            //按任务名或虚拟机名搜索
            $searchParams = "%" . $name . "%";
//             $sql .= " and (task_name like '%" . $name . "%' or details like '%" . $name . "%') ";
            $sql .= " and (task_name like '". $searchParams ."' 
            			or details like '". $searchParams ."') ";
            $sqlCount .= " and (task_name like '". $searchParams ."' or details like '". $searchParams ."') ";
        }
        $sqlParams = array($start, $length);
        $sqlCountParams = array();
        //精确搜索
        if($accurateFlag){
        	$search = $params['search'];
        	$createUser = $search['createUser'];
        	$hypervisor = intval($search['hypervisor']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
        	$errorCode = $search['errorCode'];
        	$vmname = $search['vmname'];
        	$timeType = intval($search['timeType']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	
        	//判断输入的用户名是否在当前用户的管理范围
        	if(in_array($createUser, $userGroup)){
        		$sql .= "where user_name = ? ";
        		$sqlCount .= "where user_name =?";
        		$sqlParams = array($createUser);
        		$sqlCountParams = array($createUser);
        	}else if(!$createUser){
        		$sql .= "where user_name in ('". $users ."') ";
        		$sqlCount .= "where user_name in ('". $users ."') ";
        		$sqlParams = array();
        		$sqlCountParams = array();
        	}else{
        		$sql .= "where user_name = ? ";
        		$sqlCount .= "where user_name =?";
        		$createUser = '';
        		$sqlParams = array($createUser);
        		$sqlCountParams = array($createUser);
        		
        	}
        	if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
        		$sql .= " and (submodule_type = ". $hypervisor ." or ". $hypervisor . " = '') ";
        		$sqlCount .=" and (submodule_type = ". $hypervisor ." or ". $hypervisor . " = '') ";
        	}
        		
        	$sql .= " and (task_type = ". $taskType ." or ". $taskType ." = '') and
        			(module_type = ". $moduleType ." or ". $moduleType ." = '') and
        				details like '%". $vmname ."%'";
        	$sqlCount .= " and (task_type = ". $taskType ." or ". $taskType ." = '') and
        				(module_type = ". $moduleType ." or ". $moduleType ." = '') and
        				details like '%". $vmname ."%'";
        	//如果选了错误，没有填错误码
        	if($errorCode == "all"){
        		 //所有任务状态的
        		 $sqlParams = array_merge($sqlParams, array($start, $length));
        	}else if($errorCode == "error"){
        		$sql .=" and error_code not in (0, 45, 47)";
        		$sqlCount .=" and error_code not in (0, 45, 47)";
        		$sqlParams = array_merge($sqlParams, array($start, $length));
        	}else{
        		$sql .=" and error_code = ?";
        		$sqlCount .=" and error_code = ?";
        		$errorCode = intval($errorCode);
        		$sqlParams = array_merge($sqlParams, array($errorCode, $start, $length));
        		$sqlCountParams = array_merge($sqlCountParams, array($errorCode));
        	}
        	
        	//如果填了开始时间范围查询
        	if($startTime && $endTime){
        		if($timeType == "1"){
        			$sql .= " and start_time between '". $startTime ."' and '". $endTime ."' ";
        			$sqlCount .= " and start_time between '". $startTime ."' and '". $endTime ."' ";
        		}else{
        			$sql .= " and finish_time between '". $startTime ."' and '". $endTime ."' ";
        			$sqlCount .= " and finish_time between '". $startTime ."' and '". $endTime ."' ";
        		}
        	}
        }
        
        
        $sql .= " order by $sortArr[$sortColumn]  $sortType limit ?, ?";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');
        $records["data"] = array();
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $transportSize = $this->getTransportSize($d['total_transport_size'], $d['total_completed_size'], intval($d['module_type']));
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['id'] . '">',
                ++$start,
                $d['task_name'],
                $this->getVMTaskType($d['module_type'], $d['submodule_type']),
                $this->getHistoryTaskType($d['task_type'], $d['current_mode']),
                $d['user_name'],
            	$utils->calSize($d['total_size'], true),
            	$this->getTotalValidSize($details, intval($d['module_type']), intval($d['task_type'])),
                $utils->calSize($transportSize, true),
                $utils->calSize($d['real_size'], true),
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
            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
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
     * @param unknown $moduleType
     * @param unknown $subModuleType
     * @return string
     */
    private function getVMTaskType($moduleType, $subModuleType){
//         $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $moduleTypeDes = Xphp::$_pfdes['MODULE_TYPE_DES'][$moduleType];
        if($moduleType == Xphp::$_config['MODULE_TYPE']['VM']){
            $hypervisor = intval($subModuleType);
//             $moduleTypeDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$hypervisor] . "]";
            $moduleTypeDes = Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
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
    private function getJobStatusShowLevel($errorCode){
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
    private function getJobStatusShowPopover($errorCode){

//     	$error = include CONF_PATH . "error.php";
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
            return $this->historyTaskDetailsHandlerVM($details);
        }elseif($module == Xphp::$_config['MODULE_TYPE']['FS']){
            return $this->historyTaskDetailsHandlerFile($details, $info);
        }else if($module == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
        	return $this->historyTaskDetailsHandlerCopy($details);
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
    private function historyTaskDetailsHandlerFile($details, $info){
        $details['description'] = $this->getFileErrorCodeDes($info['error_code']);
        return $details;
    }
    
    /**
     *  处理虚拟机历史任务详情
     * @param string $details
     * @return array
     */
    private function historyTaskDetailsHandlerVM($details){
        $utils = Xphp::instance('Utils');
//         $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $i = 0;
        foreach ($details as $d){
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
            $details[$i]['vm_size'] = $utils->calSize($d['vm_size'], true);
            $details[$i]['vm_valid_size'] = $utils->calSize($d['vm_valid_size'], true);
            $details[$i]['real_size'] = $utils->calSize($d['real_size'], true);
            $details[$i]['task_status'] = Xphp::$_vmdes['VmTaskStatus'][intval($d['task_status'])];
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            $i++;
        }
        return $details;
    }
    
    private function historyTaskDetailsHandlerCopy($details){
    	if (empty($details)) return false;
    	$utils = Xphp::instance('Utils');
    	$i = 0;
    	foreach ($details as $d){
    		//这里是从数据库的detail字段取得的,然后二次处理一些数据
    		$details[$i]['transport_size'] = $utils->calSize($d['transport_size'], true);
    		$details[$i]['completed_size'] = $utils->calSize($d['completed_size'], true);
    		$details[$i]['vm_valid_size'] = $utils->calSize($d['vm_valid_size'], true);
    		$details[$i]['real_size'] = $utils->calSize($d['real_size'], true);
    		$details[$i]['error_code'] = $this->getCopyErrorCodeDes($d['vm_status'], $d['error_code']);
    		$details[$i]['vm_status'] = Xphp::$_vmdes['CopyTaskStatus'][intval($d['vm_status'])];
    		$details[$i]['timepoint_count'] = intval($d['timepoint_count']);
    		$i++;
    	}
    	return $details;
    }
    
    /**
     * 删除历史任务
     * @param unknown $params
     */
    public function deleteHistoryTask($params){
        $ids = $params['id'];
        $this->paramsCheck($ids);
        $taskIDArr = array();
        foreach ($ids as $id){
            $taskIDArr[] = intval($id);
        }
        $opName = 'BD_TASK_OP_HISTORY_TASK_DELETE';
        $msg = array('id_list' => $taskIDArr);
        
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
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
     * 获取瞬时恢复任务的基本信息和虚拟机信息
     * @param unknown $params
     */
    public function getIntantBaseInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $hypervisor = $this->getHypervisor($taskUUID);
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
    			$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM'] ||
    			$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']){
        	
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
       	if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
    			$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM'] ||
    			$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']){
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
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']){
        	
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
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']){
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
            if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM'] ||
        		$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']){
            	
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
                		unix_timestamp(bs.next_start_time) next_start_time, bri.current_file_size, bri.current_completed_size, bri.speed, bri.total_size, bri.current_total_size  
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
                'totalsize' => $utils->calSize($d['total_size']),
                'completedsize' => $utils->calSize($d['current_total_size'])
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
        }else if(Xphp::$_config['MODULE_TYPE']['FS'] == $module){
            //文件
            $url = './content/fs/fs_job_details.php';
        }else if(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] == $module){
        	$url = './content/vm/vm_copy_job_details.php';
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
    			bri.speed, bri.start_time, bri.finish_time, bri.total_size, bri.current_total_size
				from bd_task bt, bd_running_info bri, bd_strategy bs where bt.strategy_id = bs.strategy_id and
    			bt.task_uuid = bri.task_uuid and bt.agent_uuid = ? ";
    	$data = $this->dbSelect($sql,array($agentuuid));
    	foreach($data as $d){
    		$info[] = array(
    				'taskuuid' => $d['task_uuid'],
    				'tasktype' => intval($d['task_type']),
    				'taskname' => $d['task_name'],
    				'status' => intval($d['task_status']),
    				'speed' => $this->getJobSpeed(intval($d['task_status']), intval($d['speed'])),
    				'nexttime' => $this->getJobsTime($d['task_status'], $this->getNextStartTime($d['next_start_time'])),
    				'finishtime' => $this->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed']),
    				'progress' => $this->getTaskTotalProgress(intval($d['task_status']), intval($d['total_size']), intval($d['current_total_size']), false),
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
            'bri.current_total_size', 'bt.task_status', 'bt.create_time', 'bu.user_name'
        );
        
        $taskName = $search['name'];
//         任务名	模块类型	任务类型	创建时间	状态	速度	创建者	操作
        $sql = "select bt.task_uuid, bt.task_name, unix_timestamp(bt.create_time) create_time, bt.node_uuid, bt.storage_uuid, 
                        bt.task_status, bt.strategy_id,
                		bri.speed, bri.speed_time, bri.total_size, bri.current_total_size, 
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
                $this->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], false),
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
     * 
     */
    private function getTotalValidSize($detail, $modeType, $taskType){
    	$utils = Xphp::instance('Utils');
    	if($modeType != Xphp::$_config['MODULE_TYPE']['VM']){
    		return Xphp::$_config['NULLSPACE'];
    	}else if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']){
    		return Xphp::$_config['NULLSPACE'];
    	}
    	foreach($detail as $d){
    		$validSize += intval($d['vm_valid_size']);
    	}
    	return $utils->calSize($validSize, true);
    }
    
    /**
     * 获取虚拟机或文件传输大小
     * @param string $VMTranSize
     * @param string $FSTranSize
     * @param int $taskType
     */
    private function getTransportSize($VMTranSize, $FSTranSize,$taskType){
    	if($taskType == Xphp::$_config['MODULE_TYPE']['VM']){
    		return $VMTranSize;
    	}else if($taskType == Xphp::$_config['MODULE_TYPE']['FS']){
    		return $FSTranSize;
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
    	return $data[0]['hypervisor_type'];
    }
    
    /**
     * 获取细粒度恢复任务展开详情信息
     * @param unknown $taskuuid
     */
    private function getGrainDetailInfo($taskuuid){
        $sql = "select vgi.vm_name, bbt.timepoint from vm_grain_info vgi, bd_backup_timepoint bbt 
                where vgi.timepoint_uuid = bbt.timepoint_uuid and vgi.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array(
            'vmname' => $data[0]['vm_name'],
            'timepoint' => $data[0]['timepoint']
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
                bd_task bt, vm_task vt, vm_grain_info vgi, bd_backup_timepoint bbt where 
                bt.task_uuid = vgi.task_uuid and bt.task_uuid = vt.task_uuid and 
                vgi.timepoint_uuid = bbt.timepoint_uuid and 
                bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $info = array(
            'status' => intval($data[0]['task_status']),
            'module' => intval($data[0]['module_type']),
            'subModule' => intval($data[0]['hypervisor_type']),
            'taskType' => intval($data[0]['task_type']),
            'statusdes' => $ptDes['TASKSTATUSDES'][$data[0]['task_status']],
            'vmname' => $data[0]['vm_name'],
            'timepoint' => $data[0]['timepoint'],
            'backupmode' => $data[0]['backup_mode'],
            
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
   
}
?>