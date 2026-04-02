<?php
/******************************************* 
** 任务处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APIJobsHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/jobs" => array(
            'DELETE' => 'deleteJob'
        ),
        
        "/jobs/backup" => array(
            'POST' => 'createBackupJob',
            'PUT' => 'editBackupJob',
        ),
    
        "/jobs/fullbackup" => array(
            'GET' => 'startFullBackup'
        ),
        
        "/jobs/incbackup" => array(
            'GET' => 'startIncBackup'
        ),
        
        "/jobs/diffbackup" => array(
            'GET' => 'startDiffBackup'
        ),
        "/jobs/strategy" => array(
        	'GET' => 'startStrategy'
        ),
        "/jobs/stop" => array(
            'GET' => 'stopJob'
        ),
        
        "/jobs/uuid" => array(
            'GET' => 'getJobUUID'
        ),
        
        "/jobs/current_lists" => array(
            'GET' => 'getCurreentLists'
        ),
        
        "/jobs/current_details" => array(
            'GET' => 'getCurrentDetails'
        ),
        
        "/jobs/history_lists" => array(
            'GET' => 'getHistoryLists',
        	'DELETE' => 'deleteHistoryJob'
        ),
        
        "/jobs/history_details" => array(
            'GET' => 'getHistoryDetails'
        ),
        
        "/jobs/recovery" => array(
            'POST' => 'createRecoveryJob',
        	'GET' => 'startRecoveryJob'
        	
        ),
        "/jobs/instant_recovery" => array(
            'POST' => 'createInstantRecovery',
        	'GET' => 'startInstantJob'
        ),
        "/jobs/motion" => array(
            'POST' => 'createMotion'
        ),
    	"/jobs/get_current_taskuuid" => array(
    		'GET' => 'getCurrentTaskuuid'
    	),
    	"/jobs/protected_vm" => array(
    		'GET' => 'getProtectedVmList'
    	),
    	"/jobs/startcopy" => array(
    		'GET' => 'startCopyJob'
    	),	
    	"/jobs/startcopyback" => array(
    		'GET' => 'startCopyBackJob'
    	),
		"/jobs/pause" => array(
			'GET' => 'pauseJob'
		),
        "/jobs/gettaskbyvmid" => array(
            'GET' => 'getTaskuuidByVmuuid'
        ),
        "/jobs/startselectvm" => array(
            'POST' => 'startSelecVmBackup'
        ),
        "/jobs/getvmtaskdetails" => array(
            'GET' => 'getVmTaskDetails'
        )
        
        
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 删除任务路由控制
     */
    protected function deleteJob(){
        //定义方法版本
        $version = array(
            "v1" => "deleteJobV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 创建备份任务路由控制
     */
    protected function createBackupJob(){
        //定义方法版本
        $version = array(
            "v1" => "createBackupJobV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改备份任务路由控制
     */
    protected function editBackupJob(){
        //定义方法版本
        $version = array(
            "v1" => "editBackupJobV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动完全备份任务路由控制
     */
    protected function startFullBackup(){
        //定义方法版本
        $version = array(
            "v1" => "startFullBackupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动增量备份任务路由控制
     */
    protected function startIncBackup(){
        //定义方法版本
        $version = array(
            "v1" => "startIncBackupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动差异任务路由控制
     */
    protected function startDiffBackup(){
        //定义方法版本
        $version = array(
            "v1" => "startDiffBackupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动差异任务路由控制
     */
    protected function startStrategy(){
    	//定义方法版本
    	$version = array(
    			"v1" => "startStrategyV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 停止任务路由控制
     */
    protected function stopJob(){
        //定义方法版本
        $version = array(
            "v1" => "stopJobV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 查找任务uuid路由控制
     */
    protected function getJobUUID(){
        //定义方法版本
        $version = array(
            "v1" => "getJobUUIDV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到当前任务列表路由控制
     */
    protected function getCurreentLists(){
        //定义方法版本
        $version = array(
            "v1" => "getCurreentListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到当前任务详情路由控制
     */
    protected function getCurrentDetails(){
        //定义方法版本
        $version = array(
            "v1" => "getCurrentDetailsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到历史任务列表路由控制
     */
    protected function getHistoryLists(){
        //定义方法版本
        $version = array(
            "v1" => "getHistoryListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到任务任务详情路由控制
     */
    protected function getHistoryDetails(){
        //定义方法版本
        $version = array(
            "v1" => "getHistoryDetailsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 创建恢复任务路由控制
     */
    protected function createRecoveryJob(){
        //定义方法版本
        $version = array(
            "v1" => "createRecoveryJobV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动恢复任务路由控制
     */
    protected function startRecoveryJob(){
    	//定义方法版本
    	$version = array(
    		"v1" => "startRecoveryJobV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 创建瞬时恢复任务路由控制
     */
    protected function createInstantRecovery(){
        //定义方法版本
        $version = array(
            "v1" => "createInstantRecoveryV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动瞬时恢复任务路由控制
     */
    protected function startInstantJob(){
    	//定义方法版本
    	$version = array(
    		"v1" => "startInstantJobV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 创建迁移任务路由控制
     */
    protected function createMotion(){
        //定义方法版本
        $version = array(
            "v1" => "createMotionV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 通过vcenteruuid和vmuuid获取当前任务uuid
     */
    protected function getCurrentTaskuuid(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getCurrentTaskuuidV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除一个或多个历史任务
     */
    protected function deleteHistoryJob(){
    	//定义方法版本
    	$version = array(
    			"v1" => "deleteHistoryJobV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取已受保护的虚拟机列表
     */
    protected function getProtectedVmList(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getProtectedVmListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动副本备份任务
     */
    protected function startCopyJob(){
    	//定义方法版本
    	$version = array(
    			"v1" => "startCopyJobV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动副本回传任务
     */
    protected function startCopyBackJob(){
    	//定义方法版本
    	$version = array(
    			"v1" => "startCopyBackJobV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 暂停任务
     */
    protected function pauseJob(){
    	//定义方法版本
    	$version = array(
    			"v1" => "pauseJobV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 通过虚拟机uuid获取任务uuid
     */
    protected function getTaskuuidByVmuuid(){
        //定义方法版本
        $version = array(
            "v1" => "getTaskuuidByVmuuidV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启动任务备份选择的虚拟机
     */
    protected function startSelecVmBackup(){
        //定义方法版本
        $version = array(
            "v1" => "startSelecVmBackupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 获取当前运行虚拟机详情信息
     */
    protected function getVmTaskDetails(){
        //定义方法版本
        $version = array(
            "v1" => "getVmTaskDetailsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    
    /**********deleteJob**********/
    private function deleteJobV1(){
        $jobUUID = $this->params['job_uuid'];
        $this->apiParamsCheck($jobUUID);
        //检查任务状态是否为停止
        $this->checkDeleteStatus($jobUUID);
        $taskType = $this->getTaskTypeWithJobUUID($jobUUID);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //删除瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK';
        }
        elseif($taskTypeConf['ORCH_TASK'] == $taskType){
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
        
        return $this->opUnifyMsg($jobUUID, $opName, 'API_CODE_JOBS_DELETE');
        
    }
    
    /**********createBackupJob**********/
    private function createBackupJobV1(){
    	//检查时间格式
    	if($this->params['time_strategy_list']){
    		//检查差异和增量是不是都选了
    		$this->checkTimeStrategy($this->params['time_strategy_list']);
    		$this->checkTimeFormat($this->params['time_strategy_list']['datetime'], $this->params['time_strategy_list']['type']);
    	}
    	//检查存储块大小是否匹配
    	if($this->params['high_info']['storage_strategy']){
    		if(!$this->params['high_info']['storage_strategy']['block_size']){
    			$this->params['high_info']['storage_strategy']['block_size'] = 2048;
    		}
    		$this->checkBlocksize($this->params['high_info']['storage_strategy']['block_size']);
    	}
        $vcenterUUID = $this->params['vm_info'][0]['vcenter_uuid'];
        $hypervisor = Xphp::instance('APIVcentersHandler', 'getHypervisorWithVcenterUUID', $vcenterUUID);
        $vmInfoList = $this->params['vm_info'];
        $jobName = $this->params['job_name'];
        $this->apiParamsCheck($vcenterUUID, $jobName);
        
        //检查任务名是否重复
//         $this->checkJobNameExist($jobName);
        
        //组合选择节点信息
        if($this->params['auto_node_check']){
        	$autoNodeCheck = $this->params['auto_node_check'];
        }else{
        	$autoNodeCheck = Xphp::$_config['FLAG']['SET'];
        }
        if($this->params['auto_storage_check']){
        	$storageCheck = $this->params['auto_storage_check'];
        }else{
        	$storageCheck = Xphp::$_config['FLAG']['SET'];
        }
        //检查提前创建快照标记
        if($this->params['pre_create_snap_flag'] && $hypervisor != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
            $preSnapFlag = $this->params['pre_create_snap_flag'];
        }else{
            $preSnapFlag = Xphp::$_config['FLAG']['UNSET'];
        }
        
        //检查线程数量
        $threadNum = $this->params['thread_num'];
        
        //检查线程数量是否超出设置范围
        if(1 > $threadNum || 32 < $threadNum){
            return $this->apiResponse(false, 'API_CODE_JOBS_THREAD_NUM_OVER_ERROR');
        }
        
        
        
        $nodeuuid = $this->params['node_uuid'];
        $storageUUID = $this->params['storage_uuid'];
        $nodeInfo = $this->groupBackupNodeInfo($autoNodeCheck, $storageCheck, $storageUUID, $nodeuuid);
        $backupVM = array();
        foreach ($vmInfoList as $vmInfo){
        	$vmconfig = json_encode($vmInfo['vm_config'], JSON_UNESCAPED_UNICODE);
        	if(!$vmInfo['vm_config']){
        		$vmconfig = json_encode(array(
        				"disk_list" => []
        		),JSON_UNESCAPED_UNICODE);
        	}
            $excludeVmList = $vmInfo['exclude_vm_list'] ? array_map(function($v) {
                return ['vm_uuid' => $v['vm_uuid']];
            }, $vmInfo['exclude_vm_list']) : [];
            $sql = "select name, dir_path from vm_tree where vcenter_uuid = ? and uuid = ?";
            $vmTreeData = $this->dbSelect($sql, [$vmInfo['vcenter_uuid'], $vmInfo['vm_uuid']])[0];
            $name = $vmTreeData['name'];
            $dirPath = $vmTreeData['dir_path'];
            //相同则是虚拟机中心
            if($vmInfo['vcenter_uuid'] == $vmInfo['vm_uuid']){
                $sql  = "select nickname, vcenter_ip from vm_vcenter where vcenter_uuid = ?";
                $data =  $this->dbSelect($sql,array($vmInfo['vcenter_uuid']));
                $name = $data[0]['nickname'] == "" ? $data[0]['vcenter_ip'] : $data[0]['nickname'];
            }
            //$backupVM[] = $this->getVMInfoWithVMName($vcenterUUID, $vmInfo['vm_uuid'], $vmconfig);
            $backupVM[] = array(
                'object_uuid' => $vmInfo['vm_uuid'],
                'object_name' => $name,
                'type' => 7, //$vmInfo['type'],
                'vcenter_uuid' => $vmInfo['vcenter_uuid'],
                'dir_path' => $dirPath,
                'vm_config' => $vmconfig,
                'exclude_vm_list' => $excludeVmList,
                'log_cache_storage_uuid' => "",
                'log_cache_size' => 2147483648,
            );
        }
        
        //获取高级备份模式
        $backupMode = $this->groupBackupMode($this->params['high_info']['backup_mode'], $hypervisor);
        $msg = array(
            "task_name" => $jobName,
            "module_type" => Xphp::$_config['MODULE_TYPE']['VM'],
            "auto_find_sr_flag" => $nodeInfo['auto_find_sr_flag'],
            "storage_uuid" => $nodeInfo['storage_uuid'],
            "time_strategy_list" => $this->groupBackupTimeList($this->params['time_strategy_list']),
        		
            "reserved_strategy" =>  $this->groupBackupReserved($this->params['high_info']['reserved_strategy']),
            "transport_strategy" => $this->groupBackupTransport($this->params['high_info']['transport_strategy']),
        		
            "storage_strategy" => $this->groupBackupStorage($this->params['high_info']['storage_strategy']),
            "task_type" => Xphp::$_config['TASKTYPE']['BACKUP'],
            "backup_level" =>  $backupMode['backup_level'],
        		
            "quiesce_snapshot" => $backupMode['quiesce_snapshot'],
            "valid_data_backup" => $backupMode['valid_data_backup'],
            "reset_cbt_flag" => 2, //$backupMode['reset_cbt_flag'],
            "parse_fs_flag" => $backupMode['parse_fs_flag'],
            "not_backup_swap_file_flag" => $backupMode['not_backup_swap_file_flag'],
            "not_backup_deleted_file_flag" => $backupMode['not_backup_deleted_file_flag'],
            "not_backup_partition_gap_flag" => $backupMode['not_backup_partition_gap_flag'],
            "serial_snapshot_flag" => $backupMode['serial_snapshot_flag'],
        	
//            "backup_vm_uuids" => $backupVM,
            "filter_criteria" => $backupVM,
            "transport_priority" => $this->getTransportMode(intval($this->params['high_info']['transport_strategy']['transport_priority']),$hypervisor),
        	"grain_level" => 0,
            "pre_create_snap_flag" => $preSnapFlag,
            "thread_num" => $threadNum,
            "transport_ip_segment" => $this->params['transport_ip_segment'] ?? '',
            "speed_limit_strategy_list" => $this->groupTaskSpeedList($this->params['speed_limit_strategy_list']),
            "speed_limit_strategy" => [],

            "appliance_uuid" => '', //$this->params['appliance_uuid'],
            "agent_uuid" => '', //$this->params['appliance_uuid'],
            "display_mode" => 1, //$this->params['display_mode'],
            "auto_join_flag" => '2', //$this->params['auto_join_flag'],
            "global_select" => [], //$this->params['global_select'],
            "strategy_group_uuid" => "",
            "backup_server_ip" => "",
            "gfs_strategy_item_list" => "",
            "storage_location" => "",
            "calibreate_strategy_list" => [
                "int_create_calibration_algorithm" => 2,
                "int_calibration_algorithm" => 0
            ]
        );
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $mbResult = $this->mbVMMsg($nodeInfo['node_uuid'], $hypervisor, $opName, json_encode($msg));
        
        $data = array();
        if($mbResult['result']){
            //如果成功,返回新建资源uuid
            $data['job_uuid'] = $this->getJobUUIDWithJobName($jobName);
        }
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_CREATE_BACKUP',$data, $mbResult);
        
    }
    
    /**********editBackupJob**********/
    private function editBackupJobV1(){
        $utils = Xphp::instance('Utils');
    	//检查差异和增量是否都选了
        $this->checkTimeStrategy($this->params['time_strategy_list']);
        //检查时间格式
        $this->checkTimeFormat($this->params['time_strategy_list']['datetime'], $this->params['time_strategy_list']['type']);
    	//检查存储块大小是否匹配
    	if($this->params['high_info']['storage_strategy']){
    		if(!$this->params['high_info']['storage_strategy']['block_size']){
    			$this->params['high_info']['storage_strategy']['block_size'] = 2048;
    		}
    		$this->checkBlocksize($this->params['high_info']['storage_strategy']['block_size']);
    	}
        
    	$taskuuid = $this->params['job_uuid'];
    	$sql = "select bt.task_type, bt.task_status, bt.strategy_id, bt.thread_num, vt.pre_create_snap_flag from bd_task bt, vm_task vt where bt.task_uuid = vt.task_uuid and bt.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	//检查备份任务
    	$this->checkBackupTask($data);
    	
    	
        $vcenterUUID = $this->params['vm_info'][0]['vcenter_uuid'];
        $jobName = $this->params['job_name'];
        $vmInfoList = $this->params['vm_info'];
        $hypervisor = Xphp::instance('APIVcentersHandler', 'getHypervisorWithVcenterUUID', $vcenterUUID);
        //检查提前创建快照标记
        if($this->params['pre_create_snap_flag']){
            $preSnapFlag = $this->params['pre_create_snap_flag'];
        }else{
            $preSnapFlag = intval($data[0]['pre_create_snap_flag']);
        }
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
            $preSnapFlag = Xphp::$_config['FLAG']['UNSET'];
        }
        
        //检查线程数量
        $threadNum = $this->params['thread_num'];
        //检查线程数量是否超出设置范围
        if(1 > $threadNum || 32 < $threadNum){
            return $this->apiResponse(false, 'API_CODE_JOBS_THREAD_NUM_OVER_ERROR');
        }
        
        //获得高级传输模式 1 普通 2 高速  3 CBT
        $validBackup =intval($this->params['high_info']['backup_mode']['incremental_mode']);
        $validInfo = $this->getIncreMode($validBackup, $hypervisor);
        
        $this->apiParamsCheck($vcenterUUID);
        //组合选择节点信息
        $autoNodeCheck = $this->params['auto_node_check'];
        $storageCheck = $this->params['auto_storage_check'];
        $nodeuuid = $this->params['node_uuid'];
        $storageUUID = $this->params['storage_uuid'];
        $nodeInfo = $this->groupBackupNodeInfo($autoNodeCheck, $storageCheck, $storageUUID, $nodeuuid);
        $this->dbBeginTransaction();  //开始事务
        //更新bd_task表
        $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, auto_find_sr_flag = ?,
                storage_uuid = ?, thread_num = ?, transport_ip_segment = ? where task_uuid = ?";
        $result = $this->dbExec($sql, array($jobName, date("Y-m-d H:i:s"), $nodeInfo['node_uuid'],
            $nodeInfo['auto_find_sr_flag'], $nodeInfo['storage_uuid'], $threadNum, $this->params['transport_ip_segment'], $taskuuid));

        //更新时间点 bd_backup_timepoint
        $sql = "update bd_backup_timepoint set task_name = ? where task_uuid = ?";
        $result = $result && $this->dbExec($sql, [$jobName, $taskuuid]);
        
        $transport_priority = intval($this->params['high_info']['transport_strategy']['transport_priority']);
        //更新虚拟机任务表 vm_task
        $sql = "update vm_task set level = ?, quiesce_snapshot = ?, transport_priority = ?, valid_data_backup = ?, serial_snapshot_flag = ?, 
                   parse_fs_flag = ?, not_backup_swap_file_flag = ?, not_backup_deleted_file_flag = ?, not_backup_partition_gap_flag = ?, 
                   agent_uuid = ?, select_conditions = ?, pre_create_snap_flag = ?, detail = ? where task_uuid = ?";
        $level = $validInfo['backup_level'];
        $quiesceSnapshot = $this->params['high_info']['backup_mode']['quiesce_snapshot'];
        //传输模式
        $transportMode = $this->getTransportMode($transport_priority,$hypervisor);
        //CBT模式
        $cbtMode = $validInfo['valid_data_backup'];
        
        //V-CBT
        $parsefsMode = $this->params['high_info']['backup_mode']['vcbt_flag'];
        //排除交换文件块
        $swapMode = $this->params['high_info']['backup_mode']['not_backup_swap_file_flag'];
        //排除删除文件块
        $deletefileMode = $this->params['high_info']['backup_mode']['not_backup_deleted_file_flag'];
        //排除分区间隙
        $gapMode = $this->params['high_info']['backup_mode']['not_backup_partition_gap_flag'];
        //传输代理
        $agentUuid = "";
        //全局筛选条件
        $globalSelect = $this->params['global_select'] ?: [['select_type' => 1, 'select_value' => '']];
        $selectType = $globalSelect[0]['select_type'];
        $selectValue = $globalSelect[0]['select_value'];
        $globalSelectJson = json_encode($globalSelect);
        //快照模式
        $snapshotMode = $this->params['high_info']['backup_mode']['snapshot_flag'];
        //显示方式
        $displayMode = 1; //intval($this->params['display_mode']);
        //任务详情
        $detailSql = "select detail from vm_task where task_uuid = ?";
        $detail = $this->dbSelect($detailSql, [$taskuuid])[0]['detail'];
        $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
        $detailArr['reset_cbt_flag'] = '2'; //(string)$utils->parseBoolToFlag($this->params['high_info']['backup_mode']['reset_cbt_flag']);
        $detailArr['auto_join_flag'] = '2'; //(string)$utils->parseBoolToFlag($this->params['auto_join_flag']);

        $result = $result && $this->dbExec($sql, array($level, $quiesceSnapshot, $transportMode, $cbtMode, $snapshotMode, $parsefsMode, $swapMode, $deletefileMode, $gapMode, $agentUuid, $globalSelectJson, $preSnapFlag, json_encode($detailArr), $taskuuid));

        //更新虚拟机列表 vm_machine_list 或 vm_object_list
        $sql = "delete from vm_machine_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "delete from vm_object_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "insert vm_machine_list (task_uuid, vcenter_uuid, vm_uuid, vm_name, version, host_uuid, dir_path, vm_config)
                values (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql2 = "insert vm_object_list (task_uuid, object_uuid, vcenter_uuid, type, exclude_vm_uuid_list, object_name, vm_config, dir_path) values (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $backupVM = array();
        foreach ($vmInfoList as $vminfo){
        	$vmconfig = json_encode($vminfo['vm_config'], JSON_UNESCAPED_UNICODE);
        	if(!$vminfo['vm_config']){
        		$vmconfig = json_encode(array(
        				"disk_list" => []
        		), JSON_UNESCAPED_UNICODE);
        	}
        	$backupVM[] = $this->getVMInfoAndVMName($vcenterUUID, $vminfo['vm_uuid'], $vmconfig);
        }
        $vmInfo = $backupVM;
        foreach ($vmInfo as $vm){
            if (Xphp::$_config['VM_TREE_TYPE']['VM'] == $vm['type']) {
                //虚拟机同时更新vm_machine_list和vm_object_list
                $sqlParams = array($taskuuid, $vm['vcenter_uuid'], $vm['vm_uuid'], $vm['vm_name'], $vm['version'],
                    $vm['host_uuid'], $vm['dir_path'], $vm['vm_config']);
                $result = $result && $this->dbExec($sql, $sqlParams);
            } else {
                //todo:虚拟机上级粒度
            }
            $exclude_vm_uuids = $vm['exclude_vm_list'] ? implode(',', array_column($vm['exclude_vm_list'], 'vm_uuid')) : '';
            $sql2Params = array($taskuuid, $vm['vm_uuid'], $vm['vcenter_uuid'], $vm['type'], $exclude_vm_uuids, $vm['vm_name'], $vm['vm_config'], $vm['dir_path']);
            $result = $result && $this->dbExec($sql2, $sql2Params);
        }
        
        //更新时间策略表 bd_time_strategy
        $strategyID = $data[0]['strategy_id'];
        $sql = "delete from bd_time_strategy where strategy_id = ?";
        $result = $result && $this->dbExec($sql, array($strategyID));
        $timeStrategy = $this->params['time_strategy_list'];
        if("oncetime" == $this->getTimeStrategyType($timeStrategy['type'])){
        	//一次性策略
        	$sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time)
                    values (?, ?, ?, ?)";
        	$sqlParams = array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL'],
        			Xphp::$_config['STRATEGY_TYPE']['ONCE'], $timeStrategy['datetime']);
        	$result = $result && $this->dbExec($sql, $sqlParams);
        }elseif("strategy" == $this->getTimeStrategyType($timeStrategy['type'])){
        	//时间策略
        	if(!empty($timeStrategy['full_info'])){
        		//完全策略
        		$modeType = Xphp::$_config['BACKUP_MODE']['FULL'];
        		$result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['full_info']);
        	}
        	if(!empty($timeStrategy['incr_info'])){
        		//增量策略
        		$modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
        		$result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['incr_info']);
        	}
        	if(!empty($timeStrategy['diff_info'])){
        		//差异策略
        		$modeType = Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'];
        		$result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['diff_info']);
        	}
        
        }
        
        $highInfo = $this->params['high_info'];
        //更新保留策略表 bd_reserved_strategy
        $sql = "update bd_reserved_strategy set strategy_type = ?, number = ? where strategy_id = ?";
        $sqlParams = array($highInfo['reserved_strategy']['reserved_type'], $highInfo['reserved_strategy']['number'], $strategyID);
        $result = $result && $this->dbExec($sql, $sqlParams);
        
        //更新传输策略表 bd_transport_strategy
        $sql = "update bd_transport_strategy set encrypt_flag = ? where strategy_id = ?";
        $sqlParams = array($highInfo['transport_strategy']['encrypt_flag'], $strategyID);
        $result = $result && $this->dbExec($sql, $sqlParams);
        
        //更新存储策略表 bd_storage_strategy
        $sql = "update bd_storage_strategy set deduplication_flag = ?, block_size = ?, compressed_flag = ?,
                encrypted_flag = ?, password_auto_flag = ?, password = ?, compress_method = ?, encrypt_method = ? where strategy_id = ?";
        $password = $highInfo['storage_strategy']['password'];
        if(intval($highInfo['storage_strategy']['encrypt_flag']) == Xphp::$_config['FLAG']['SET']){
            //自动加密密码
            $password = !empty($password)? $password: "/mnt/vm_vinfs";
        }
        $sqlParams = array($highInfo['storage_strategy']['deduplication_flag'],
            $highInfo['storage_strategy']['block_size'],
            $highInfo['storage_strategy']['compress_flag'],
            $highInfo['storage_strategy']['encrypt_flag'],
            !empty($highInfo['storage_strategy']['password'])?Xphp::$_config['FLAG']['UNSET']:Xphp::$_config['FLAG']['SET'],
            !empty($password)?$utils->ptPassEncrypt($password):"",
            $highInfo['storage_strategy']['compress_method'] ?? 1,
            $highInfo['storage_strategy']['encrypt_method'] ?? 1,
            $strategyID
        );
        $result = $result && $this->dbExec($sql, $sqlParams);
        //限速策略
        $speedList = $this->groupTaskSpeedList($this->params['speed_limit_strategy_list'], "");
        //更新时间策略表 bd_task_speed_limit_strategy
        $sql = "delete from bd_task_speed_limit_strategy where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "insert bd_task_speed_limit_strategy (strategy_group_uuid, strategy_uuid, strategy_type, days, start_time, end_time, remark, speed_limited_value, task_uuid) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        foreach($speedList as $speed){
            $sqlParams = array("", $utils->uuid(), intval($speed['strategy_type']), $speed['days'], $speed['start_time'], $speed['end_time'], $speed['remark'], intval($speed['speed_limited_value']),$taskuuid);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
        if($result){
        	$this->dbCommit();
        }else{
        	$this->dbRollBack();
        	return $this->apiResponse(false, 'API_CODE_JOBS_EDIT_BACKUP_ERROR',array(), array());
        }
        
//         //检查任务名是否重复
//         $this->checkJobNameExist($jobName);
        
        
        $data = array();
        if($result){
            //如果成功,返回新建资源uuid
            $data['job_uuid'] = $this->getJobUUIDWithJobName($jobName);
        }
        
        return $this->apiResponse($result, 'API_CODE_JOBS_EDIT_BACKUP',$data, array());
    }
    
    /**********startFullBackup**********/
    private function startFullBackupV1(){
        return $this->startBackukpJobUnify('API_CODE_JOBS_START_FULL_BACKUP', 
            Xphp::$_config['BACKUP_MODE']['FULL'], $this->params['job_uuid']);
    }
    
    /**********startIncBackup**********/
    private function startIncBackupV1(){
        return $this->startBackukpJobUnify('API_CODE_JOBS_START_INCR_BACKUP', 
            Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $this->params['job_uuid']);
    }
    
    /**********startDiffBackup**********/
    private function startDiffBackupV1(){
        return $this->startBackukpJobUnify('API_CODE_JOBS_START_DIFF_BACKUP', 
            Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'], $this->params['job_uuid']);
    }
    
    /**********startStrategy**********/
    private function startStrategyV1(){
    	$jobuuid = $this->params['job_uuid'];
    	$this->apiparamsCheck($jobuuid);
    	
    	$status = $this->getTaskStatusWithJobUUID($jobuuid);
    	if($status != Xphp::$_config['TASKSTATUS']['STOPPED']){
    	    exit($this->apiResponse(false, 'API_CODE_JOBS_START_STRATEGY_JOB_STATUS_ERROR'));
    	}
    	$opName = 'BD_TASK_OP_START_TIMESTRATEGY';
    	
    	return $this->opUnifyMsg($jobuuid, $opName, 'API_CODE_JOBS_START_STRATEGY');
    }
    
    /**********stopJob**********/
    private function stopJobV1(){
        $jobUUID = $this->params['job_uuid'];
        $this->apiParamsCheck($jobUUID);
        $taskType = $this->getTaskTypeWithJobUUID($jobUUID);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //停止瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK';
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
        }
        
      	return $this->opUnifyMsg($jobUUID, $opName, 'API_CODE_JOBS_STOP');
    }
    
    /**********getJobUUID**********/
    private function getJobUUIDV1(){
        $jobName = $this->params['job_name'];
        $this->apiParamsCheck($jobName);
        $jobUUID = $this->getJobUUIDWithJobName($jobName);
        if(!$jobUUID){
        	return $this->apiResponse(false, 'API_CODE_JOBS_JOB_NAME_NOT_EXIST');
        }
        $data = array(
            'job_uuid' => $jobUUID
        );
        
        return $this->apiResponse(!empty($jobUUID), 'API_CODE_JOBS_GET_UUID', $data);
    }
    
    /**********getCurreentLists**********/
    private function getCurreentListsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $taskName = $this->params['task_name'];
        $taskType = $this->params['task_type'];
        $moduleType = $this->params['module_type'];
        $taskStatus = $this->params['task_status'];
        $nodeuuid = $this->params['node_uuid'];
        $startTime = $this->params['start_time'];
        $endTime = $this->params['end_time'];
        $this->apiParamsCheck($count);
        $utils = Xphp::instance('Utils');
        
        $sortColumn = !empty(intval($this->params['sort_column']))?intval($this->params['sort_column']):0;
        $sortType = intval($this->params['sort_type']) == 2 ? "desc":"asc";
        $sortArr = array('bt.task_name', 'bt.module_type', 'bt.task_type', 'bt.create_time',
            'bt.task_status', 'bri.speed','bri.total_object_completed_size', 'bu.user_name', ''
        );
        
        $sql = "select distinct bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, 
                        unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.storage_uuid,
                		bri.speed, bri.speed_time, bri.total_object_size, bri.total_object_completed_size, bt.strategy_id 
                from bd_task bt, bd_running_info bri
                where bt.task_uuid = bri.task_uuid and 
                      bt.delete_flag = ? ";
        $sqlCount = "select count(distinct bt.task_uuid) as total 
                from bd_task bt, bd_running_info bri
                where bt.task_uuid = bri.task_uuid and
                      bt.delete_flag = ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        //任务名
        $taskName = $utils->escapeWildcard($taskName);
        if($this->checkEmpty($taskName)){
            //按任务名搜索
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }
        //任务类型
        if(!empty($taskType)){
            $sql .= " and bt.task_type =".$taskType;
            $sqlCount .= " and bt.task_type =".$taskType;
        }
        //模块类型
        if(!empty($moduleType)){
            $sql .= " and bt.module_type =".$moduleType;
            $sqlCount .= " and bt.module_type =".$moduleType;
        }
        //状态
        if(!empty($taskStatus)){
            $sql .= " and bt.task_status =".$taskStatus;
            $sqlCount .= " and bt.task_status =".$taskStatus;
        }
        //节点
        if(!empty($nodeuuid)){
            $sql .= " and bt.node_uuid = '". $nodeuuid ."' ";
            $sqlCount .= " and bt.node_uuid = '". $nodeuuid ."' ";
        }
        
        //如果填了开始时间范围查询
        if(!empty($startTime) && !empty($endTime)){
            $sql .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
            $sqlCount .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
        }
        $sql .= " order by  $sortArr[$sortColumn]  $sortType , bt.id desc ";
        $sql .= " limit ?,?";
        $sqlParams = array_merge($sqlParams, array($begin, $count));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();

        foreach ($data as $d){
            
            $records[] = array(
                "job_name" => $d['task_name'],
                "job_uuid" => $d['task_uuid'],
                "job_type" => $d['task_type'],
                "create_time" => $d['create_time'],
                "job_status" => $d['task_status'],
//                "hypervisor" => $d['hypervisor_type'],
                "speed" => $d['speed'],
                "speed_time" => $d['speed_time'],
                "storage_uuid" => $d['storage_uuid'],
            	"strategy_info" => $this->getCurrentStrategy($d['strategy_id'],intval($d['task_type'])),
                "progress" => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type'])
                
            );
        }
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        return $this->apiResponse(true, "API_CODE_JOBS_GET_CURRENT_JOBS_LIST", $data);
    }
    
    /**********getCurrentDetails**********/
    private function getCurrentDetailsV1(){
        $jobUUID = $this->params['job_uuid'];
        $this->apiParamsCheck($jobUUID);
        
        //通过公有函数得到当前单个任务详情
        $record = $this->getCurrentPushJobDetail($jobUUID);
        
        return $this->apiResponse(true, 'API_CODE_JOBS_GET_CURRENT_JOB_DETAILS', $record);
    }
    
    
    /**********getHistoryLists**********/
    private function getHistoryListsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $taskName = $this->params['task_name'];
        $taskType = $this->params['task_type'];
        $moduleType = $this->params['module_type'];
        $taskStatus = $this->params['task_status'];
        $nodeuuid = $this->params['node_uuid'];
        $this->apiParamsCheck($count);
        $utils = Xphp::instance('Utils');
        $sortColumn = !empty(intval($this->params['sort_column']))?intval($this->params['sort_column']):0;
        $sortType = intval($this->params['sort_type']) == 2 ? "desc":"asc";
        $sortArr = array('bht.id', 'bht.task_name', 'bht.submodule_type', 'bht.task_type', 'bht.user_name',
            'bht.total_object_size', 'bht.total_object_transport_size', 'bht.total_object_transport_size', 'bht.total_object_write_size', 'bht.start_time', 'bht.finish_time', 'bht.error_code'
        );
        $sql = "select bht.id, bht.task_uuid, bht.task_name, bht.submodule_type, bht.task_type, bht.current_mode, bht.total_object_size,
                bht.total_object_write_size, bht.total_object_transport_size, bht.total_object_completed_size,
                unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                bht.error_code, bht.details from bd_history_task bht where bht.id is not null ";
        $sqlCount = "select count(bht.id) as total from bd_history_task bht where bht.id is not null ";
        $sqlParams = array();
        $sqlCountParams = array();
        
        //节点
        if(!empty($nodeuuid)){
            //节点uuid需要
            $sql = "select bht.id, bht.task_uuid, bht.task_name, bht.submodule_type, bht.task_type, bht.current_mode, bht.total_object_size,
                bht.total_object_write_size, bht.total_object_transport_size, bht.total_object_completed_size,
                unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                bht.error_code, bht.details from bd_history_task bht, bd_task_alarm bta where bta.history_uuid = bht.history_uuid and bht.id is not null ";
            $sqlCount = "select count(bht.id) as total from bd_history_task bht, bd_task_alarm bta where bta.history_uuid = bht.history_uuid and bht.id is not null ";
            
            $sql .= " and bta.node_uuid = ? ";
            $sqlCount .= " and bta.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
        }
        
        //按任务
        $taskName = $utils->escapeWildcard($taskName);
        if($this->checkEmpty($taskName)){
            //按任务名搜索
            $sql .= " and bht.task_name like ? ";
            $sqlCount .= " and bht.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$taskName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$taskName.'%'));
        }
        
        //任务类型
        if(!empty($taskType)){
            $sql .= " and bht.task_type = ? ";
            $sqlCount .= " and bht.task_type = ? ";
            $sqlParams = array_merge($sqlParams, array($taskType));
            $sqlCountParams = array_merge($sqlCountParams, array($taskType));
        }
        //模块类型
        if(!empty($moduleType)){
            $sql .= " and bht.module_type = ? ";
            $sqlCount .= " and bht.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($moduleType));
            $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
        }
        //状态,1成功  2失败
        if(!empty($taskStatus)){
            if($taskStatus == Xphp::$_config['FLAG']['SET']){
                $sql .= " and bht.error_code = 0 ";
                $sqlCount .= " and bht.error_code = 0 ";
            }else{
                $sql .= " and bht.error_code != 0 ";
                $sqlCount .= " and bht.error_code != 0 ";
            }
        }
        if(empty($nodeuuid)){
            $sql .= " order by $sortArr[$sortColumn]  $sortType ";
        }
        $sql .= " limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($begin, $count));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $total = intval($dataCount[0]['total']);
        $records = array();
        $error = include CONF_PATH . "error.php";
        foreach ($data as $d){  
        	$uuidList = array(); //用于存成功恢复后的虚拟机uuid
        	$details = json_decode($d['details'], true);
        	foreach ($details as $detail){
        		if($detail['vm_uuid'] == $detail['new_vm_uuid']) continue; //如果虚拟机还未创建起，要返回历史任务
        		$uuidList[] = $detail['new_vm_uuid'];
        	}
        	
            $records[] = array(
            	"delete_id" => $d['id'],
                "job_uuid" => $d['task_uuid'],
                "job_name" => $d['task_name'],
                "hypervisor" => $d['submodule_type'],
                "job_type" => $d['task_type'],
                "job_mode" => $d['current_mode'],
                "total_size" => $d['total_object_size'],
                "real_size" => $d['total_object_write_size'],
                "transport_size" => $d['total_object_transport_size'],
                "total_valid_size" => $this->getTotalValidSize($details),
                "start_time" => $d['start_time'],
                "finish_time" => $d['finish_time'],
                "error_code" => $d['error_code'],
                "vm_lists" => $this->getHistoryVMList($details, intval($d['task_type']), $error, $utils),
            );
        }
        
        $data = array(
            "total" => $total,
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_JOBS_GET_HISTORY_JOBS_LIST", $data);
    }
    
    /**********getHistoryDetails**********/
    private function getHistoryDetailsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $jobUUID = $this->params['job_uuid'];
        $utils = Xphp::instance('Utils');
        $this->apiParamsCheck($count);
        
        $sql = "select task_uuid, task_name, submodule_type, task_type, current_mode, total_object_size, 
                total_object_write_size, total_object_transport_size, total_object_completed_size, 
                unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, 
                error_code, details from bd_history_task where task_uuid = ? order by start_time desc limit ? , ?";
        $sqlCount = "select count(id) as total from bd_history_task where task_uuid = ? ";
        
        $data = $this->dbSelect($sql, array($jobUUID, $begin, $count));
        $dataCount = $this->dbSelect($sqlCount, array($jobUUID));
        $records = array();
        $error = include CONF_PATH . "error.php";
        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $records[] = array(
                "job_uuid" => $d['task_uuid'],
                "job_name" => $d['task_name'],
                "hypervisor" => $d['submodule_type'],
                "job_type" => $d['task_type'],
                "job_mode" => $d['current_mode'],
                "total_size" => $d['total_object_size'],
                "real_size" => $d['total_object_write_size'],
                "transport_size" => $d['total_object_transport_size'],
                "total_valid_size" => $this->getTotalValidSize($details),
                "start_time" => $d['start_time'],
                "finish_time" => $d['finish_time'],
                "error_code" => $d['error_code'],
                "vm_lists" => $this->getHistoryVMList($details, intval($d['task_type']), $error, $utils),
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_JOBS_GET_HISTORY_JOB_DETAILS", $data);
    }
    
    /**********createRecoveryJob**********/
    private function createRecoveryJobV1(){
        $timepointuuid = $this->params['recovery_vms'][0]['timepoint_uuid'];
        $jobName = $this->params['job_name'];
        $threadNum = $this->params['thread_num'];
        $this->apiParamsCheck($timepointuuid, $jobName);
        //检查任务名是否重复
//         $this->checkJobNameExist($jobName);
        
        $vcenterUUID = $this->params['recovery_destination']['vcenter_uuid'];
        $hypervisor = Xphp::instance('APIVcentersHandler', 'getHypervisorWithVcenterUUID', $vcenterUUID);
        //虚拟化中心不存在
        if(empty($hypervisor)){
            return $this->apiResponse(false, 'API_CODE_VCENTERS_DELETE_UUID_NOT_EXIST');
        }
        //检查恢复后虚拟机名是否符合标准
        $recoveryVM = $this->params['recovery_vms'];
        foreach ($recoveryVM as $vm){
        	$this->checkVMname($vm['new_name'], $hypervisor);
        	$this->createRecoverJobCheck($hypervisor, $vcenterUUID, $vm['new_name']);
        }
        $msg = array(
            "task_name" => $jobName,
            "module_type" => Xphp::$_config['MODULE_TYPE']['VM'],
            "recovery_position" => 2,       //异机恢复
            "recovery_time_type" => $this->params['recovery_type'],
        		
            "recovery_vm_uuids" => $this->groupRecoveryVM($this->params['recovery_vms'], $this->params['recovery_destination'], $hypervisor),
        	"transport_strategy" => array(
        			'encrypt_flag' => 2,
        			'compress_flag' => 2,
        			'speed_limit_flag' => 2,
        			'max_speed' => 0,
        	),
            "time_strategy_list" => $this->groupRecoverTimeList($this->params['time_strategy'], $this->params['recovery_type']),
        	"transport_priority" => $this->getTransportMode(intval($this->params['transport_priority']),$hypervisor),
            "task_type" => Xphp::$_config['TASKTYPE']['RECOVERY'],
            "recovery_level" =>  Xphp::$_config['VmTaskLevel']['VM'],
            "thread_num" => $threadNum,  //线程数量
        	"transport_ip_segment" => $this->params['transport_ip_segment'],
            "speed_limit_strategy_list" => $this->groupTaskSpeedList($this->params['speed_limit_strategy_list'])
        );
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $nodeuuid = $this->getNodeUUIDWithTimepointUUID($this->params['recovery_vms'][0]['timepoint_uuid']);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg));
        
        $data = array();
        if($mbResult['result']){
            //如果成功,返回新建资源uuid
            $data['job_uuid'] = $this->getJobUUIDWithJobName($jobName);
            $recoveryType = $this->params['recovery_type'];
            if(Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == $recoveryType){
            	$startResult = $this->startRecoverJob($jobName); //一次性恢复启动任务
            	if(!$startResult['result']){
            		return $this->apiResponse(false, 'API_CODE_JOBS_CREATE_RECOVERY', array(), $startResult);
            	}
            }
        }
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_CREATE_RECOVERY',$data, $mbResult);
    }
    
    /**********startRecoveryJob**********/
    private function startRecoveryJobV1(){
    	$taskuuid = $this->params['job_uuid'];
    	$hypervisor = $this->getHypervisorWithJobUUID($taskuuid);
    	$this->apiParamsCheck($taskuuid, $hypervisor);
    	$msg = array(
    			'task_uuid' => $taskuuid,
    			'backup_mode' => 1,
    			'time_strategy_id' => 0,
    			'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
    	);
    	$msg = json_encode($msg);
    	$opName = 'BD_TASK_OP_RECOVERY_START';
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, FALSE, TRUE);
    	return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_START_RECOVERY',array(), $mbResult);
    }
    
    /**********createInstantRecovery**********/
    private function createInstantRecoveryV1(){
        $timepointuuid = $this->params['instant_vm_info']['timepoint_uuid'];
        $jobName = $this->params['job_name'];
        $this->apiParamsCheck($timepointuuid, $jobName);
        
        //检查任务名是否重复
//         $this->checkJobNameExist($jobName);
        
        $vcenterUUID = $this->params['instant_target']['vcenter_uuid'];
        //检查恢复后虚拟机名是否符合标准
        $hypervisor = Xphp::instance('APIVcentersHandler', 'getHypervisorWithVcenterUUID', $vcenterUUID);
        $this->checkVMname($this->params['instant_vm_info']['new_vm_name'], $hypervisor);
        $this->createRecoverJobCheck($hypervisor, $vcenterUUID, $this->params['instant_vm_info']['new_vm_name']);
        $msg = array(
            "task_name" => $jobName,
            "module_type" => Xphp::$_config['MODULE_TYPE']['VM'],
            "hypervisor" => $hypervisor,
            "recovery_position" => 0,       //不需要的参数
            "recovery_time_type" => 2, //补齐，无用
        		
            "instant_vm_info" => $this->groupInstantVM($this->params['instant_vm_info'], $this->params['instant_target'], $hypervisor),
        	"transport_strategy" => array(
        		'encrypt_flag' => 2,
        		'compress_flag' => 2,
        		'speed_limit_flag' => 2,
        		'max_speed' => 0,
        	    'strategy_group_uuid' => ""
        	),
            "time_strategy_list" => array(),
            "task_type" => Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'],
            "recovery_level" =>  Xphp::$_config['VmTaskLevel']['INSTANT_RECOVERY'],
        		
        );
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_INSTANT_RECOVERY_TASK';
        $nodeuuid = $this->getNodeUUIDWithTimepointUUID($this->params['instant_vm_info']['timepoint_uuid']);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg));
        
        $data = array();
        if($mbResult['result']){
            //如果成功,返回新建资源uuid
            $data['job_uuid'] = $this->getJobUUIDWithJobName($jobName);
        }
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_CREATE_INSTANT',$data, $mbResult);
    }
    
    /**********startInstantJob**********/
    private function startInstantJobV1(){
    	$taskuuid = $this->params['job_uuid'];
    	$hypervisor = $this->getHypervisorWithJobUUID($taskuuid);
    	$this->apiParamsCheck($taskuuid, $hypervisor);
    	$msg = array(
    		'task_uuid' => $taskuuid,
    		'backup_mode' => 1,
    		'time_strategy_id' => 0,
    		'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
    	);
    	$msg = json_encode($msg);
    	$opName = 'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK';
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, FALSE, TRUE);
    	return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_START_INSTANT_RECOVERY',array(), $mbResult);
    }
    /**********createMotion**********/
    private function createMotionV1(){
    	//检查迁移任务
    	$taskuuid = $this->params['job_uuid'];
    	$this->checkMotionTask($taskuuid);
    	
        $vcenterUUID =	$this->params['motion_destination']['vcenter_uuid'];
        $this->apiParamsCheck($taskuuid,$vcenterUUID);
        //检查恢复后虚拟机名是否符合标准
        $hypervisor = Xphp::instance('APIVcentersHandler', 'getHypervisorWithVcenterUUID', $vcenterUUID);
        $this->checkVMname($this->params['rebuild_vm']['new_name'], $hypervisor);
        $this->createRecoverJobCheck($hypervisor, $vcenterUUID, $this->params['rebuild_vm']['new_name']);
        //检查任务名是否重复
//         $this->checkJobNameExist($jobName);
        $sql = "select timepoint_uuid from vm_instant where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepointuuid = $data[0]['timepoint_uuid'];
        $msg = array(
        	"task_uuid" => $taskuuid,
            "transport_priority" => $this->getTransportMode(intval($this->params['transport_priority']),$hypervisor),
            "rebuild_vm" => $this->groupMotionVM($this->params['rebuild_vm'],$timepointuuid, $this->params['motion_destination'], $hypervisor, $taskuuid),
            "appliance_uuid" => "",
            "backup_server_ip" => "",
            "transport_ip_segment" => ""
                        
        );
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK';
        $nodeuuid = $this->getNodeUUIDWithTimepointUUID($timepointuuid);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg));
        
        $data = array();
        if($mbResult['result']){
            //如果成功,返回新建资源uuid
            $data['job_uuid'] = $taskuuid;
        }
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_CREATE_MOTION',$data, $mbResult);
    }
    
    /**********getCurrentTaskuuid**********/
    private function getCurrentTaskuuidV1(){
    	$vcenteruuid = $this->params['vcenter_uuid'];
    	$vmuuid = $this->params['vm_uuid'];
    	
    	$this->apiParamsCheck($vcenteruuid, $vmuuid);
    	$sql ="select vml.task_uuid, bt.task_type, bt.task_status from vm_machine_list vml, bd_task bt where vml.task_uuid = bt.task_uuid and vml.vcenter_uuid = ? and vml.vm_uuid = ?";
    	$data = $this->dbSelect($sql, array($vcenteruuid, $vmuuid));
    	$info = array();
    	foreach($data as $d){
    		$info[] = array(
    				'task_uuid' => $d['task_uuid'],
    				'task_type' => $d['task_type'],
    				'task_status' => $d['task_status'],
    		);
    	}
    	
    	return $this->apiResponse(true, 'API_CODE_JOBS_GET_CURRENT_JOB_UUID', $info);
    }
    
    /**********deleteHistoryJob**********/
    private function deleteHistoryJobV1(){
    	$deleteType = $this->params['delete_type'];
    	$deleteValue = $this->params['delete_value_list'];
    	$this->paramsCheck($deleteType, $deleteValue);
    	$ids = array();
    	switch($deleteType){
    		case Xphp::$_config['DELETE_HISTORY']['HISTORY_ID']:
    			foreach ($deleteValue as $v){
    			    $ids[] = $v;
    			}
    			break;
    		case Xphp::$_config['DELETE_HISTORY']['HISTORY_TASK_UUID']:
    		    $taskuuidStr = implode("','", $deleteValue);
				$sql = "select id from bd_history_task where task_uuid in ('".$taskuuidStr."')";
				$data = $this->dbSelect($sql);
				if(!empty($data)){
				    foreach ($data as $d){
				        $ids[] = $d['id'];
				    }
				}					
    			break;
    		case Xphp::$_config['DELETE_HISTORY']['HISTORY_TASK_NAME']:
    		    $tasknameStr = implode("','", $deleteValue);
				$sql = "select id from bd_history_task where task_name in ('".$tasknameStr."')";
				$data = $this->dbSelect($sql);
				if(!empty($data)){
				    foreach ($data as $d){
				        $ids[] = $d['id'];
				    }
				}	
    			break;
    		
    	}
    	if(empty($ids)){
    		return $this->apiResponse(false, 'API_CODE_JOBS_DELETE_HISTORY_JOB_ERROR');
    	}
    	$taskIDArr = array();
    	foreach ($ids as $id){
    		$taskIDArr[] = intval($id);
    	}
    	$opName = 'BD_TASK_OP_HISTORY_TASK_DELETE';
    	$msg = array('id_list' => $taskIDArr);
    	
    	$mbResult = $this->mbPFMsg($opName, json_encode($msg));
    	$result = $mbResult['result'];
    	
    	return $this->apiResponse($result, 'API_CODE_JOBS_DELETE_HISTORY_JOB', '', $mbResult);
    }
    
    /**********getProtectedVmList**********/
    private function getProtectedVmListV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	
    	$sql = "select vml.vcenter_uuid, vml.vm_uuid, vml.vm_name, vml.dir_path, bt.task_uuid, bt.task_name, bt.task_status, vt.hypervisor_type
    			from vm_machine_list vml, bd_task bt, vm_task vt where vml.task_uuid = bt.task_uuid and bt.task_uuid = vt.task_uuid and bt.task_type = ? order by bt.create_time desc limit ?, ?";
    	$info = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP'], $begin, $count));
    	$sqlCount = "select count(vml.machine_id) as total from vm_machine_list vml, bd_task bt, vm_task vt where bt.task_uuid = vml.task_uuid and bt.task_uuid = vt.task_uuid and bt.task_type = ?";
    	$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['TASKTYPE']['BACKUP']));
    	$records = array();
    	foreach ($info as $i){
    		$records[] = array(
    			'vm_name' => $i['vm_name'],
    			'vm_uuid' => $i['vm_uuid'],
    			'vcenter_uuid' => $i['vcenter_uuid'],
    			'hypervisor' => $i['hypervisor_type'],
    			'dir_path' => $i['dir_path'],
    			'job_name' => $i['task_name'],
    			'job_status' => $i['task_status'],
    			'job_uuid' => $i['task_uuid'],
    		    'ifaces' => $this->getVmNetworkList($i['vm_uuid'], $i['vcenter_uuid'])
    		);
    	}
    	
    	$data = array(
    			"total" => intval($dataCount[0]['total']),
    			"begin" => $begin,
    			"count" => $count,
    			"records" => $records
    	);
    	 
    	return $this->apiResponse(true, "API_CODE_JOBS_GET_PROTECTED_VM", $data);
    	
    }
    
    /**********startCopyJob**********/
    private function startCopyJobV1(){
    	return $this->startBackukpJobUnify('API_CODE_JOBS_START_COPY_BACKUP',
    			Xphp::$_config['BACKUP_MODE']['FULL'], $this->params['job_uuid']);
    }
    
    /**********startCopyBackJob**********/
    private function startCopyBackJobV1(){
    	return $this->startBackukpJobUnify('API_CODE_JOBS_START_COPY_BACK',
    			Xphp::$_config['BACKUP_MODE']['FULL'], $this->params['job_uuid']);
    }
    
    /**********pauseJob**********/
    private function pauseJobV1(){
    	$jobUUID = $this->params['job_uuid'];
    	$this->apiParamsCheck($jobUUID);
    	$taskType = $this->getTaskTypeWithJobUUID($jobUUID);
    	$taskTypeConf = Xphp::$_config['TASKTYPE'];
     	if($taskTypeConf['BACKUP'] == $taskType){
     		//备份
            $opName = 'BD_TASK_OP_BACKUP_PAUSE';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
        	//恢复
            $opName = 'BD_TASK_OP_RECOVERY_PAUSE';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType){
            //暂停副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_PAUSE';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
            //暂停副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE';
        }
    	
    	return $this->opUnifyMsg($jobUUID, $opName, 'API_CODE_JOBS_PAUSE');
    }
    
    /**********getTaskuuidByVmuuid**********/
    private function getTaskuuidByVmuuidV1(){
        $vmuuid = $this->params['vm_uuid'];
        $sql = "select bt.task_uuid from bd_task bt, vm_machine_list vml where bt.task_uuid = vml.task_uuid and bt.task_type = 1 and vml.vm_uuid = ?";
        $data = $this->dbSelect($sql, array($vmuuid));
        $info = array();
        if (!empty($data)){
            $info = array(
                'task_uuid' => $data[0]['task_uuid']
            );
        }
        
        return $this->apiResponse(true, 'API_CODE_JOBS_GET_TASK_UUID_BY_VM_ID', $info);
    }
    
    
    /**********startSelecVmBackup**********/
    private function startSelecVmBackupV1(){
        $mode = $this->params['mode'];
        $uuidList = $this->params['vmuuids'];
        $taskuuid = $this->params['task_uuid'];
        $uuidStr = implode("' , '",  $uuidList);
        $sql = "select vt.task_uuid, vt.hypervisor_type, vml.vm_uuid, vml.vcenter_uuid from vm_task vt, vm_machine_list vml where vt.task_uuid = vml.task_uuid and vml.vm_uuid in ('". $uuidStr ."') and vt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $hypervisorList = array();
        $vcenteruuidList = array();
        $taskuuidList = array();
        if(empty($data)){
            return $this->apiResponse(false, 'API_CODE_JOBS_VM_NOT_IN_TASK');
        }
        $vmuuids = array();
        $vcenteruuids = array();
        foreach ($data as $d){
            $hypervisor = intval($d['hypervisor_type']);
            $vcenteruuid = $d['vcenter_uuid'];
            $taskuuid = $d['task_uuid'];
            if(!in_array($hypervisor, $hypervisorList)){
                $hypervisorList[] = $hypervisor;
            }
            $vcenteruuidList[] = $vcenteruuid;
            if(!in_array($taskuuid, $taskuuidList)){
                $taskuuidList[] = $taskuuid;
            }
            
            if(!in_array($d['vcenter_uuid'], $vcenteruuids)){
                $vcenteruuids[] = array(
                    'vcenter_uuid' => $d['vcenter_uuid']
                );
            }
            
            if(!in_array($d['vm_uuid'], $vmuuids)){
                $vmuuids[] = array(
                    "vm_uuid" => $d['vm_uuid']
                );
            }
        }
        
        if(count($taskuuidList) != 1){
            return $this->apiResponse(false, 'API_CODE_JOBS_SELECT_ONE_TASK');
        }
        
        if(count($hypervisorList) != 1){
            return $this->apiResponse(false, 'API_CODE_JOBS_SELECT_ONE_HYPERVISOR');
        }
        
        $subModule = $hypervisorList[0];
        $taskuuid = $taskuuidList[0];
        $this->checkTaskRun($taskuuid);
        
        if(!$mode){
            //任务默认按增量备份启动
            $mode = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
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
        $msg = $mbResult['msg'];
        
        //返回结果
        return $this->apiResponse($mbResult['result'], 'API_CODE_JOBS_START_SELECT_VM_BACKUP', array(), $mbResult);
    }
    
    /********** getVmTaskDetails**********/
    private function  getVmTaskDetailsV1(){
		$eventUUID = $this->params['event_uuid']; //这次任务事件唯一标识
        $taskuuid = $this->params['job_uuid'];
        $vmuuid = $this->params['vm_uuid'];
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, vml.mode, vml.task_status as vm_task_status, vml.error_code,
                vml.vm_size, vml.transport_size, vml.write_size,vml.vm_valid_size, vml.completed_size, bri.current_object_write_size, bri.current_object_transport_size, bri.speed, bri.speed_time, bri.current_object_completed_size,
                bt.task_status as bd_task_status, bt.task_type from vm_machine_list vml, bd_running_info bri, bd_task bt where vml.task_uuid = bri.task_uuid
        		and vml.task_uuid = bt.task_uuid and vml.vm_uuid = ? and vml.task_uuid = ?";
        $data = $this->dbSelect($sql, array($vmuuid, $taskuuid));
        $info = array();
        $timepointDes = "";
        foreach ($data as $d){
            if(intval($d['vm_task_status']) == Xphp::$_config['VmTaskStatus']['RUNNING'] ){
                $transportSize = intval($d['current_object_transport_size']);
            }else{
                $transportSize = intval($d['transport_size']);
            }
            $validSize = $d['vm_valid_size'];
            if($transportSize == $validSize && $transportSize != 0){
               $timepointDes = $this->getCurrentTimepoint($taskuuid,$vmuuid);
            }
            $info = array(
                "vcenter_uuid" => $d['vcenter_uuid'],
                "vm_uuid" => $d['vm_uuid'],
                "vm_job_status" => $d['vm_task_status'],
                "error_code" => $d['error_code'],   
                "transport_size" => $transportSize,
                "valid_size" => $validSize,
                "speed" => $this->getVMSpeed($d['vm_task_status'], $d['speed'], $d['speed_time']),
                "process" => $this->getVMPercent($d['vm_size'], $d['current_object_completed_size'], $d['completed_size'], $d['bd_task_status'], $d['vm_task_status']),
                "error_description" => $this->getErrorCodeDes($d['vm_task_status'], $d['error_code']),
                "vm_job_type" => $this->getCurrentVMListTaskType($d['task_type'], $d['mode'], $d['bd_task_status']),
                "backup_time_point" => $timepointDes       
            );
            
        }
        
        return $this->apiResponse(true, 'API_CODE_JOBS_GET_TASK_UUID_BY_VM_ID', $info);
    }
    
    
    /**********************************其他工具方法************************************/
    /**
     * 检查任务名是否存在
     * @param string $jobName
     */
   	public function checkJobNameExist($jobName){
        $sql = "select task_uuid from bd_task where task_name = ?";
        $data = $this->dbSelect($sql, array(trim($jobName)));
        if(!empty($data)){
            //如果找到了相同任务名的任务,返回错误
            return $this->apiResponse(false, 'API_CODE_JOBS_JOB_NAME_EXISTS');
        }
    }
    
    /**
     * 通过任务名查找任务uuid
     * @param string $jobName
     * @return string
     */
    public function getJobUUIDWithJobName($jobName){
        $sql = "select task_uuid from bd_task where task_name = ? order by create_time desc";
        $data = $this->dbSelect($sql, array(trim($jobName)));
        $jobUUID = empty($data[0]['task_uuid']) ? "" : $data[0]['task_uuid'];
        return $jobUUID;
    }
    
    /**
     * 通过虚拟机名字获取虚拟机uuid
     * @param string $vcenterUUID
     * @param string $vmName
     * @return array
     */
    private function getVMInfoWithVMName($vcenterUUID, $vmUUID, $vmconfig){
        $sql = "select uuid, dir_path from vm_tree where vcenter_uuid = ? and uuid = ? and 
                display_mode = ?";
        $data = $this->dbSelect($sql, array($vcenterUUID, $vmUUID, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
        if(empty($data)){
            return $this->apiResponse(false, 'API_CODE_JOBS_NOT_FIND_VM');
        }
        $vmInfo = array(
            "vm_uuid" => $data[0]['uuid'],
            "vcenter_uuid" => $vcenterUUID,
            "dir_path" => $data[0]['dir_path'],
            "vm_config" => $vmconfig,
        	'log_cache_storage_uuid' => "",
        	'log_cache_size' => 2147483648
        );
        return $vmInfo;
    }
    
    /**
     * 修改备份任务 获取每个虚拟机信息
     * @param string $vcenterUUID
     * @param string $vmName
     * @return array
     */
    private function getVMInfoAndVMName($vcenterUUID, $vmuuid, $vmconfig){
    	$sql = "select name, type, uuid, dir_path, host_uuid, version from vm_tree where vcenter_uuid = ? and uuid = ? and
                display_mode = ? and type = ? ";
    	$data = $this->dbSelect($sql, array($vcenterUUID, $vmuuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']));
    	if(empty($data)){
    		return $this->apiResponse(false, 'API_CODE_JOBS_NOT_FIND_VM');
    	}
    	$vmInfo = array(
    	    "vm_uuid" => $vmuuid,
    		"vm_name" => $data[0]['name'],
            "type" => $data[0]['type'],
    		"version" => $data[0]['version'],
    		"vcenter_uuid" => $vcenterUUID,
    		"host_uuid" => $data[0]['host_uuid'],
    		"dir_path" => $data[0]['dir_path'],
    		"vm_config" => $vmconfig,
    		'log_cache_storage_uuid' => "",
    		'log_cache_size' => 2147483648
    	);
    	return $vmInfo;
    }
    
    /**
     * 根据任务uuid获取虚拟化类型
     * @param string $jobUUID
     */
    private function getHypervisorWithJobUUID($jobUUID){
        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobUUID));
        if(empty($data)){
            return $this->apiResponse(false, 'API_CODE_JOBS_NOT_FIND_JOB');
        }
        
        return intval($data[0]['hypervisor_type']);
    }
    
    /**
     * 启动备份任务统一处理
     * @param string $apiCode
     * @param int $backupMode
     * @param string $jobUUID
     */
    public function startBackukpJobUnify($apiCode, $backupMode, $jobUUID){
        $this->apiParamsCheck($jobUUID);
       	$taskType = $this->getTaskTypeWithJobUUID($jobUUID);
       	$status = $this->getTaskStatusWithJobUUID($jobUUID);
       	if($status == Xphp::$_config['TASKSTATUS']['RUNNING']){
       	    exit($this->apiResponse(false, 'API_CODE_JOBS_START_RUNNING_JOB_ERROR'));
       	}
       	$taskTypeConf = Xphp::$_config['TASKTYPE'];
       	$msg = array(
       			'task_uuid' => $jobUUID,
       			'backup_mode' => $backupMode,
       			'time_strategy_id' => 0,
       			'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
       	);
       	$hypervisor = 0;
       	$nodeuuid = Xphp::instance('APINodesHandler', 'getJobExitNodeUUID', $jobUUID);
       	$returnInfo = array();
       	if($taskTypeConf['BACKUP'] == $taskType){
       		$opName = 'BD_TASK_OP_BACKUP_START';
       		$hypervisor = $this->getHypervisorWithJobUUID($jobUUID);
       		$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), false, true);
       	}elseif($taskTypeConf['BACKUP_COPY'] == $taskType){
            //启动副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_BACKUP_COPY_CONTINUE';
            }
            $mbResult = $this->mbCopyMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), false, true);
            
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType){
            //启动副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_START';
            if($status == Xphp::$_config['TASKSTATUS']['PAUSED'] || $status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
            	$opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE';
            }
            $mbResult = $this->mbCopyMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), false, true);
            if($mbResult['result']){
            	$returnInfo = array(
            		'job_uuid' => $jobUUID
            	);
            }
        }
        
        return $this->apiResponse($mbResult['result'], $apiCode, $returnInfo, $mbResult);
    }
    
    /**
     * 根据任务uuid得到任务类型
     * @param string $jobUUID
     * @return int
     */
    private function getTaskTypeWithJobUUID($jobUUID){
        $sql = "select task_type from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobUUID));
        if(!$data){
        	return $this->apiResponse(false, 'API_CODE_JOBS_JOB_UUID_NOT_EXIST');
        }
        return intval($data[0]['task_type']);
    }
    
    /**
     * 根据任务uuid得到任务状态
     * @param string $jobUUID
     * @return int
     */
    private function getTaskStatusWithJobUUID($jobUUID){
    	$sql = "select task_status from bd_task where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($jobUUID));
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_JOBS_JOB_UUID_NOT_EXIST');
    	}
    	return intval($data[0]['task_status']);
    }
    
    /**
     * 根据任务uuid得到模块类型
     * @param string $jobUUID
     * @return int
     */
    private function getModuleTypeWithJobUUID($jobUUID){
    	$sql = "select module_type from bd_task where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($jobUUID));
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_JOBS_JOB_UUID_NOT_EXIST');
    	}
    	return intval($data[0]['module_type']);
    }
    
    /**
     * 根据任务uuid得到虚拟机列表
     * @param string $jobUUID
     */
    private function getVMListsWithJobUUID($jobUUID){
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, vml.vm_name, vml.version, vml.dir_path, vml.mode, vml.task_status as vm_task_status, vml.error_code, 
                vml.vm_size, vml.transport_size, vml.write_size,vml.vm_valid_size, vml.completed_size, bri.current_object_write_size, bri.current_object_transport_size, bri.speed, bri.speed_time, bri.current_object_completed_size, 
                bt.task_status as bd_task_status, bt.task_type from vm_machine_list vml, bd_running_info bri, bd_task bt where vml.task_uuid = bri.task_uuid
        		and vml.task_uuid = bt.task_uuid and vml.task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobUUID));
        $lists = array();
        foreach ($data as $d){
            $lists[] = array(
                "vcenter_uuid" => $d['vcenter_uuid'],
                "vm_uuid" => $d['vm_uuid'],
                "vm_name" => $d['vm_name'],
                "version" => $d['version'],
                "dir_path" => $d['dir_path'],
                "vm_job_status" => intval($d['vm_task_status']),
                "error_code" => $d['error_code'],
                "vm_size" => $d['vm_size'],
                "transport_size" => $this->getVMCompletedSize($d['current_object_transport_size'], $d['transport_size'], $d['bd_task_status'], $d['vm_task_status']),
                "real_size" => $this->getVMCompletedSize($d['current_object_write_size'], $d['write_size'], $d['bd_task_status'], $d['vm_task_status']),
            	"valid_size" => $this->getVMListSize($d['bd_task_status'], $d['vm_valid_size']),
            	"speed" => $this->getVMSpeed($d['vm_task_status'], $d['speed'], $d['speed_time']),
            	"process" => $this->getVMPercent($d['vm_size'], $d['current_object_completed_size'], $d['completed_size'], $d['bd_task_status'], $d['vm_task_status']),
            	"error_description" => $this->getErrorCodeDes($d['vm_task_status'], $d['error_code']),
            	"vm_job_type" => $this->getCurrentVMListTaskType($d['task_type'], $d['mode'], $d['bd_task_status']),
            	"exclude_disk_list" => $this->getDiskInfo($d['vm_uuid'],$d['task_type'], $jobUUID)
            );
            
        }
        return $lists;
    }
    
    /**
     * 组合恢复虚拟机列表
     * @param array $reoveryVM
     * @param array $destination
     */
    private function groupRecoveryVM($reoveryVM, $destination, $hypervisor){
    	$lists = array();
    	$apiPointHandler = Xphp::instance('APIBackupPointHandler');
    	//获取虚拟机恢复详细信息
    	$params = $this->getRecoverVMInfo($reoveryVM, $destination, $hypervisor);
    	$vmConfigInfo = $apiPointHandler->getVMConfigInfo($params);
    	$otherInfo = array(
    	    'available_domain_select' => !empty($vmConfigInfo['available_domain'])?$vmConfigInfo['available_domain'][0]:"",
    	    'root_pass_input' => "",
    	    'des_dir_select' =>!empty($vmConfigInfo['des_dir'])?$vmConfigInfo['des_dir'][0]['key']:"",
    	    'mirror_image_select' => !empty($vmConfigInfo['mirror_image'])?$vmConfigInfo['mirror_image'][0]:"",
    	    'ha' => !$vmConfigInfo['control']['HA']? false: $vmConfigInfo['control']['HA'],
    	    'virtual_type_select' => "HVM",
    	);
    	$timepointuuidList = array();
    	$vmuuidList = array();
    	foreach ($reoveryVM as $vm){
    	    $vmuuid = $this->getVMuuid($vm['timepoint_uuid']);
    	    //重复使用时间点检测
    	    if(in_array($vm['timepoint_uuid'], $timepointuuidList) || in_array($vmuuid, $vmuuidList)){
    	        return $this->apiResponse(false, 'API_CODE_JOBS_TIMEPOINT_REPEAT_ERROR');
    	    }
    		//检查备份时间点是否合法
    	    $this->checkTimepointExist($vm['timepoint_uuid']);
    		$timepointuuidList[] = $vm['timepoint_uuid'];
    		$vmuuidList[] = $vmuuid;
    		foreach ($vmConfigInfo['config'] as $conf){
    		    if($conf['timepointuuid'] == $vm['timepoint_uuid']){
    		        $lists[] = array(
    		            "auto_datastore_flag" => 2,
    		            "destination_datastore_name" => "",
    		            "destination_host_uuid" => $destination['host_uuid'],
    		            "destination_vcenter_uuid" => $destination['vcenter_uuid'],
    		            "extension_info" => $this->groupOpenstackInfo($destination['openstack_group_info']),
    		            "new_name" => $vm['new_name'],
    		            "start_vm_flag" => $vm['start_vm_flag'],
    		            "timepoint_uuid" => $vm['timepoint_uuid'],
    		            "vm_config" => $this->groupRecoveryConfig($vm['vm_config'], $vm['timepoint_uuid'], $conf, $otherInfo, $hypervisor),
    		            "vm_uuid" => $vmuuid
    		        );
    		    }
    		}
    	}
    	return $lists;
    }
    
    /**
     * 根据时间节点uuid得到虚拟机uuid
     * @param string $timepointuuid
     */
    private function getVMuuid($timepointuuid){
    	$sql = 'select vm_uuid from vm_backup_timepoint where timepoint_uuid = ?';
    	$data = $this->dbSelect($sql, array($timepointuuid));
    	$vmuuid = $data[0]['vm_uuid'];
    	return $vmuuid;
    }
    /**
     * 组合openstack信息
     * @param array $info
     */
    private function groupOpenstackInfo($info){
    	$openstackInfo = array(
    		"group_name" => $info['group_name'],
			"group_uuid" => $info['group_uuid'],
			"user_name" => $info['user_name'],
			"password" => $info['password']
    	);
    	if(!$info){
    		$openstackInfo = array(
    				"group_name" => null,
    				"group_uuid" => null,
    				"user_name" => null,
    				"password" => ''
    		);
    	}
    	return json_encode($openstackInfo);
    }
    
    /**
     * 组合瞬时恢复虚拟机信息
     * @param array $instantVM
     * @param array $instantTarget
     */
    private function groupInstantVM($instantVM, $instantTarget, $hypervisor){
    	$timepointuuid = $instantVM['timepoint_uuid'];
    	//检查备份时间点是否合法
    	$this->checkTimepointExist($timepointuuid);
    	$sql = "select hypervisor_type, vm_uuid, vm_name, version from vm_backup_timepoint where timepoint_uuid = ?";
    	$data = $this->dbSelect($sql, array($timepointuuid));
    	$apiPointHandler = Xphp::instance('APIBackupPointHandler');
    	//获取虚拟机恢复详细信息
    	$vmList[] = array(
    	    'timepoint_uuid' => $instantVM['timepoint_uuid'],
    	    'new_name' => $instantVM['new_vm_name']
    	);
    	$params = $this->getRecoverVMInfo($vmList, $instantTarget, $hypervisor, true);
    	$vmConfigInfo = $apiPointHandler->getVMConfigInfo($params);
    	$otherInfo = array(
    	    'available_domain_select' => !empty($vmConfigInfo['available_domain'])?$vmConfigInfo['available_domain'][0]:"",
    	    'root_pass_input' => "",
    	    'des_dir_select' =>!empty($vmConfigInfo['des_dir'])?$vmConfigInfo['des_dir'][0]['key']:"",
    	    'mirror_image_select' => !empty($vmConfigInfo['mirror_image'])?$vmConfigInfo['mirror_image'][0]:"",
    	    'ha' => !$vmConfigInfo['control']['HA']? false: $vmConfigInfo['control']['HA'],
    	    'virtual_type_select' => "HVM",
    	);
    	$instantInfo = array(
    	    "advance_vm_config" => $this->groupRecoveryConfig(array(), $timepointuuid, $vmConfigInfo['config'][0], $otherInfo, $hypervisor),
    		"extension_info" => $this->groupOpenstackInfo($instantTarget['openstack_group_info']),
    		"new_vm_name" => $instantVM['new_vm_name'],
			"nfs_server_ip" => $instantVM['nfs_server_ip'],
			"orig_vm_name" => $data[0]['vm_name'],
			"orig_vm_uuid" => $data[0]['vm_uuid'],
    	    "orig_vm_version" =>$data[0]['version'],
			"start_vm_flag" => $instantVM['start_vm_flag'],
			"target_host_uuid" => $instantTarget['host_uuid'],
			"target_vcenter_uuid" => $instantTarget['vcenter_uuid'],
			"timepoint_uuid" => $timepointuuid,
    	);
    	return $instantInfo;
    }
    
    /**
     * 组合瞬时恢复虚拟机信息
     * @param array $network
     * @param string $timepointuuid
     */
    private function groupInstantNetwork($network, $timepointuuid){
   		$msg = $network;
   		if(!$network){
   			$sql = "select hypervisor_type, vm_config from vm_backup_timepoint where timepoint_uuid = ?";
   			$data = $this->dbSelect($sql, array($timepointuuid));
   			$hypervisor =$data[0]['hypervisor_type'];
   			$vmconfig =json_decode($data[0]['vm_config'], true);
   			$pointHandler = Xphp::instance('APIBackupPointHandler');
   			 
   			$network = $pointHandler->getVMConfigNetworkInfo($vmconfig['network_list'], $hypervisor);
   			foreach ($network as $p){
   				$msg[] = array(
   						"src_network_name" => $p['net_num'],
   						"mac_addr" => $p['mac'],
   						"keep_mac_flag" => 2,
   						"target_network_uuid" => "",
   						"auto_conf_flag" => 1
   				);
   			}
   		}
   		
   		return $msg;
    }
    
    /**
     * 组合迁移虚拟机信息
     * @param array $motionInfo
     * @param string $timepointuuid
     * @param array $destination
     */
    private function groupMotionVM($motionInfo, $timepointuuid,$destination, $hypervisor, $taskuuid){
        $apiPointHandler = Xphp::instance('APIBackupPointHandler');
        //获取虚拟机迁移详细信息
        $vmList[] = array(
            'timepoint_uuid' => $timepointuuid,
            'new_name' => $motionInfo['new_name']
        );
        $params = array(
            'vcenteruuid' => $destination['vcenter_uuid'],
            'hostuuid' => $destination['host_uuid'],
            'newname' => $motionInfo['new_name'],
            'hypervisor' => $hypervisor,
            'taskuuid' => $taskuuid,
        );
        $vmConfigInfo = $apiPointHandler->getMotionConfigInfo($params);
        $otherInfo = array(
            'available_domain_select' => !empty($vmConfigInfo['available_domain'])?$vmConfigInfo['available_domain'][0]:"",
            'root_pass_input' => "",
            'des_dir_select' =>!empty($vmConfigInfo['des_dir'])?$vmConfigInfo['des_dir'][0]['key']:"",
            'mirror_image_select' => !empty($vmConfigInfo['mirror_image'])?$vmConfigInfo['mirror_image'][0]:"",
            'ha' => !$vmConfigInfo['control']['HA']? false: $vmConfigInfo['control']['HA'],
            'virtual_type_select' => "HVM",
        );
    	$info = array(
    	    "vm_config" => $this->groupRecoveryConfig($motionInfo['vm_config'], $timepointuuid, $vmConfigInfo['config'][0], $otherInfo, $hypervisor),
    		"extension_info" => $this->groupOpenstackInfo($destination['openstack_group_info']),
    		"auto_datastore_flag" => 1,
			"destination_datastore_name" => "",
			"destination_host_uuid" => $destination['host_uuid'],
			"destination_vcenter_uuid" => $destination['vcenter_uuid'],
			"new_name" => $motionInfo['new_name'],
			"start_vm_flag" => $motionInfo['start_vm_flag'],
			"vm_uuid" => $this->getVMuuid($timepointuuid),
    		"timepoint_uuid" => $timepointuuid,
    	);
    	return $info;
    }
    
    /**
     * 过滤历史任务虚拟机列表
     * @param json $details
     * @param int $details
     */
    private function getHistoryVMList($details, $taskType, $error, $utils){
        $details = $details['vms_details'] ?? $details;
        $vmLists = array();
        if(empty($details)){
            return $vmLists;
        }
        
        foreach ($details as $vm){
        	$vmDetail = $this->getDiffDetail($vm, $taskType);
            $vmLists[] = array(
                "vm_uuid" => $vm['vm_uuid'],
                "vm_name" => $vm['vm_name'],
                "version" => $vm['version'],
                "dir_path" => $vm['dir_path'],
                "vm_job_status" => $vm['task_status'],
                "error_code" => $vm['error_code'],
                "vm_size" => $vm['vm_size'],
                "transport_size" => $vm['transport_size'],
                "real_size" => $vm['write_size'],
            	"vm_valid_size" => $vm['vm_valid_size'],
            	"diff_detail" => $vmDetail,
                "error_descrption" => $this->getVMErrorCodeDes($vm['task_status'], $vm['error_code'], $error),
                "transfer_speed" => $utils->calSpeed(intval($vm['transfer_speed'])),
                "start_transfer_time" => $vm['start_transfer_time'],
                "end_transfer_time" => $vm['end_transfer_time'],
                "vm_job_mode" => intval($vm['mode'])
                
            );
        }
        return $vmLists;
    }
    
    /**
     * 组合虚拟机配置信息
     * @param array $config
     * @param array $timepointuuid
     * @return array
     */
    private function groupRecoveryConfig($config,$timepointuuid, $conf, $otherInfo, $hypervisor){
    	$network = array();
    	foreach ($conf['net_list'] as $net){
    	    if(!empty($config['vm_network_list'])){
    	        foreach ($config['vm_network_list'] as $d){
    	            if($d['src_network_name'] == $net['network_name']){
    	                $network[] = array(
    	                    'net_num' => $d['src_network_name'],
    	                    'mac' => $d['mac_addr'],
    	                    'bus_type' => $net['controller_type'],
    	                    'target_network_uuid' => $d['target_network_uuid']
    	                );
    	            }
    	        }
    	    }else{
    	        $network[] = array(
    	            'net_num' => $net['network_name'],
    	            'mac' => $net['mac_address'],
    	            'bus_type' => $net['controller_type'],
    	            'target_network_uuid' => ""
    	        );
    	    }
    	}
    	$storage = array();
    	foreach ($conf['disk_list'] as $disk){
    	    if(!empty($config['vm_disk_list'])){
    	        foreach ($config['vm_disk_list'] as $d){
    	            if(base64_encode($d['src_disk_uuid']) == $disk['disk_key_base64']){
    	                $storage[] = array(
    	                    'disk_name' => !empty($d['src_disk_name'])? $d['src_disk_name'] : $disk['disk_name'],
    	                    'vdi_uuid' => base64_encode($d['src_disk_uuid']),
    	                    'size' => intval($disk['virtual_size']),
    	                    'bus_type' => $disk['controller_type'],
    	                    'disk_type' => $disk['disk_type'],
    	                    'target_storage_uuid' => $d['target_storage_uuid']
    	                );
    	            }
    	        }
    	    }else{
    	        $storage[] = array(
    	            'disk_name' => $disk['disk_name'],
    	            'vdi_uuid' => $disk['disk_key_base64'],
    	            'size' => intval($disk['virtual_size']),
    	            'bus_type' => $disk['controller_type'],
    	            'disk_type' => $disk['disk_type'],
    	            'target_storage_uuid' => "",
    	        );
    	    }
    	}
    	$vmRecoveryHandler  = Xphp::instance('VmRecoveryHandler');
	    $msg = array(
	        'cpu_socket' => intval($conf['sockets']),
	        'cores_per_socket' => intval($conf['cores']),
	        'vm_memory' => intval($conf['memory']),
	        'auto_conf_flag' => Xphp::$_config['FLAG']['UNSET'],
	        'boot_mode' => intval($conf['boot_mode']),  //引导模式
	        'vm_version' => $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] ? 'V1'
                : ($config['vm_version'] ?? $conf['vm_version']),
	        "vm_network_list" => $this->groupRecoveryNetowork($network), //网络
	        "vm_disk_list" => $this->groupRecoveryStorage($storage),     //磁盘
	        'zone_config' => $vmRecoveryHandler->processRecoveryVmZoneConfig($otherInfo['available_domain_select']),                   //可用域
	        'video_device' => array('video_type'=> 0, 'vram_memory' => 0),                              //显卡,暂时没有用,保留字段
	        'other_config' => $vmRecoveryHandler->processRecoveryVmOtherConfig($otherInfo),                 //其他配置
	        
	    );
    	return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 组合恢复存储列表
     * @param array $params
     * @return array
     */
    private function groupRecoveryStorage($params){
    	$msg = array();
    	foreach ($params as $p){
    		$msg[] = array(
    		    "src_disk_uuid" => base64_decode($p['vdi_uuid']),
    			"size" => $p['size'],
    		    "disk_name" => $p['disk_name'],
    			"auto_conf_flag" => 1,
    			"target_storage_uuid" => $p['target_storage_uuid'], //目标主机存储UUID
    		    "disk_type" => intval($p['disk_type']),  //磁盘类型
    		    "bus_type" => intval($p['bus_type']),   
    		    "cluster_size" => 64,
    		    "cache_type" => 0,//缓存类型,保留字段
    		    "preallocated_type" => 0// 预分配，针对vmware等,保留字段
    		);
    	}
    	return $msg;
    }
	
    /**
     * 组合恢复网络列表
     * @param array $params
     * @return array
     */
    private function groupRecoveryNetowork($params){
    	$msg = array();
    	foreach ($params as $p){
    	    if(empty($p['ipaddr'])){
    	        $ipv4_list = array();
    	    }else{
    	        $ipv4_list = explode(",", $p['ipaddr']);
    	    }
    		$msg[] = array(
	    		"src_network_name" => $p['net_num'],
				"mac_addr" => $p['mac'],
    		    "bus_type" => intval($p['bus_type']),
				"keep_mac_flag" => 2,
				"target_network_uuid" => $p['target_network_uuid'],
				"auto_conf_flag" => 1,
    		    'ipaddr' => "",
    		    'ipv4_list' => $ipv4_list,
    		    'ipv6_list' => array()//保留字段,设置成空数组
    		);
    	}
    	return $msg;
    }
    	
    /**
     * 组合高级备份模式
     * @param array $params
     * @return array
     */
    private function groupBackupMode($params, $hypervisor){
    	$validBackup =intval($params['incremental_mode']);
    	$validInfo = $this->getIncreMode($validBackup, $hypervisor);
    	if(!$params){
    		$msg = array(
    			"backup_level" => 2,
            	"quiesce_snapshot" => 2,
            	"valid_data_backup" => 1,
                "reset_cbt_flag" => 2,
            	"parse_fs_flag" => 1,
            	"not_backup_swap_file_flag" => 1,
            	"not_backup_deleted_file_flag" => 1,
            	"not_backup_partition_gap_flag" => 1,
            	"serial_snapshot_flag" => 1,
    		);
    	}else{
    		$msg = array(
    			"backup_level" => $validInfo['backup_level'],
            	"quiesce_snapshot" => $params['quiesce_snapshot'],
            	"valid_data_backup" => $validInfo['valid_data_backup'],
                "reset_cbt_flag" => 2, //$params['reset_cbt_flag'],
            	"parse_fs_flag" => $params['vcbt_flag'],
            	"not_backup_swap_file_flag" => $params['not_backup_swap_file_flag'],
            	"not_backup_deleted_file_flag" => $params['not_backup_deleted_file_flag'],
            	"not_backup_partition_gap_flag" => $params['not_backup_partition_gap_flag'],
            	"serial_snapshot_flag" => $params['snapshot_flag'],
    		);
    	}
    	return $msg;
    }
    
    
    /**
     * 组合备份存储策略
     * @param array $params
     * @return array
     */
    private function groupBackupStorage($params){
        $utils = Xphp::instance('Utils');
    	if(!$params){
    		$msg = array(
    		    'encrypt_flag' => Xphp::$_config['FLAG']['UNSET'],
    		    'compress_flag' => Xphp::$_config['FLAG']['SET'],
    		    'deduplication_flag' => Xphp::$_config['FLAG']['UNSET'],
                'block_size' => 64,
    		    'strategy_group_uuid' => "",
    		    'password_auto_flag' => Xphp::$_config['FLAG']['UNSET'],
    		    'password' => '',
                'compress_method' => 1,
                'encrypt_method' => 1,
    		);
    	}else{
    		$msg = array(
    		    'encrypt_flag' => $params['encrypt_flag'],
                'compress_flag' => $params['compress_flag'],
                'deduplication_flag' => $params['deduplication_flag'],
                'block_size' => $params['block_size'],
    		    'strategy_group_uuid' => "",
    		    'password_auto_flag' => !empty($params['password'])?Xphp::$_config['FLAG']['UNSET']:Xphp::$_config['FLAG']['SET'],
    		    'password' => !empty($params['password']) ? base64_encode($params['password']) : "",
                'compress_method' => $params['compress_method'] ?? 1,
                'encrypt_method' => $parmas['encrypt_method'] ?? 1,
    		);
    	}
    	return $msg;
    }
    
    
    /**
     * 组合备份传输策略
     * @param array $params
     * @return array
     */
    private function groupBackupTransport($params){
    	if(!$params){
    		$msg = array(
    			'encrypt_flag' => 2,
                'compress_flag' => 2,
                'speed_limit_flag' => 2,
                'max_speed' => 0,
                'network_uuid' => '',
                'strategy_group_uuid' => '',
                'compress_method' => 0,
                'reconnect_times' => 60,
                'reconnect_interval' => 30,
                'encrypt_method' => 1,
    		);
    	}else{
    		$msg = array(
    			'encrypt_flag' => $params['encrypt_flag'],
                'compress_flag' => Xphp::$_config['FLAG']['SET'],
                'speed_limit_flag' => 2,
                'max_speed' => 0,
                'network_uuid' => '',
                'strategy_group_uuid' => '',
                'compress_method' => 0,
                'reconnect_times' => 60,
                'reconnect_interval' => 30,
                'encrypt_method' => 1,
    		);
    	}
    	return $msg;
    }
    
    /**
     * 组合备份保留策略
     * @param array $params
     * @return array
     */
    private function groupBackupReserved($params){
    	if(!$params){
    		$msg = array(
    			'strategy_type' => 1,
    			'number' => 30,
    			'auto_archive_flag' => 2,
                'strategy_group_uuid' => '',
                'strategy_mode' => 1
    		);
    	}else{
    		$msg = array(
    			'strategy_type' => $params['reserved_type'],
    			'number' => $params['number'],
    			'auto_archive_flag' => Xphp::$_config['FLAG']['UNSET'],
                'strategy_group_uuid' => '',
                'strategy_mode' => 1
    		);
    	}
    	return $msg;
    }
    
    /**
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    private function groupBackupTimeList($params){
    	$msg = array();
    	if(!$params){
    		$msg[] = array(
    			'mode' => Xphp::$_config['BACKUP_MODE']['FULL'],
    			'strategy_type' => Xphp::$_config['STRATEGY_TYPE']['FOREVER'],
    			'days' => '',
    			'start_time' => '',
    			'roll_flag' => 0,
    			'roll_interval' => 0,
    			'roll_end_time' => '',
    			'global_id' => 0,
                'strategy_group_uuid' => '',
    		);
    	}else if('strategy' == $this->getTimeStrategyType($params['type'])){
    		//按时间策略备份
    		if(!empty($params['full_info'])){
    			//完全策略
    			$msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['full_info']);
    		}
    		if(!empty($params['incr_info'])){
    			//增量策略
    			$msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['incr_info']);
    		}
    		if(!empty($params['diff_info'])){
    			//差异策略
    			$msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'], $params['diff_info']);
    		}
    		if(!$msg){
    			$msg[] = array(
    				'mode' => Xphp::$_config['BACKUP_MODE']['FULL'],
    				'strategy_type' => Xphp::$_config['STRATEGY_TYPE']['FOREVER'],
    				'days' => '',
    				'start_time' => '',
    				'roll_flag' => 0,
    				'roll_interval' => 0,
    				'roll_end_time' => '',
    				'global_id' => 0,
                    'strategy_group_uuid' => '',
    			);
    		}
    	}else{
    		//一次性备份
    		$strategy = array('start_time' => $params['datetime']);
    		$strategy['strategy_type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
    		$msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $strategy);
    	}
    
    	return $msg;
    }
    
    /**
     * 组合每一个时间策略
     * @param int $mode         完全1/增量2/差异3/日志4/标签5
     * @param array $strategy
     * @return array
     */
    public function groupEachTimestrategy($mode, $strategy){
    	$this->paramsCheck($mode, $strategy);
    	$strArr = array("mode" => $mode);
    	//时间策略
    	//间隔周数
    	$frequency = 's'. $strategy['frequency'];
    	$strArr['strategy_type'] = $strategy['strategy_type'];
        if (!is_array($strategy['days'])) {
            $strategy['days'] = [];
        }
        $strArr['days'] =  implode("", $strategy['days']) . $frequency;
    	$strArr['start_time'] = $strategy['start_time'];
    	$strArr['roll_flag'] = $strategy['roll_flag'];
    	$strArr['roll_interval'] = $this->getRollInterval($strategy['roll_interval']);
    	$strArr['roll_end_time'] = $strategy['roll_end_time'];
    	if(Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY'] == intval($strArr['strategy_type'])){
    		//每天备份
    		$strArr['days'] = "1111111";
    	}
    
    	if(Xphp::$_config['STRATEGY_TYPE']['ONCE'] == intval($strArr['strategy_type'])){
    		//一次性备份
    		$strArr['days'] = '';
    	}
    	
    	if(Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'] == intval($strArr['strategy_type']) && count($strategy['days']) != 7){
    		return $this->apiResponse(false, 'API_CODE_JOBS_WEEK_DAYS_LENGTH_ERROR');
    	}
    	
    	if(Xphp::$_config['STRATEGY_TYPE']['EVERY_MONTH'] == intval($strArr['strategy_type']) && count($strategy['days']) != 31){
    		return $this->apiResponse(false, 'API_CODE_JOBS_MONTH_DAYS_LENGTH_ERROR');
    	}
    	if($strArr['roll_flag'] == Xphp::$_config['STRATEGY_ROLL_TYPE']['ON']){
    		//滚动备份
    		$strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['ON'];
    	}else{
    		//不滚动
    		$strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['OFF'];
    		$strArr['roll_interval'] = 0;
    		$strArr['roll_end_time'] = '';
    	}
    	$strArr['global_id'] = 0;
        $strArr['strategy_group_uuid'] = '';
    	return $strArr;
    }
    
    /**
     * 转化滚动间隔为秒
     * @param string $rollInterval
     * @return number
     */
    private function getRollInterval($rollInterval){
    	if(empty($rollInterval)){
    		return 0;
    	}
    	$intervalArr = explode(":", $rollInterval);
    	$second = intval($intervalArr[0]) * 3600 + intval($intervalArr[1]) * 60 + intval($intervalArr[2]);
    	return $second;
    }
    
    /**
     * 得到传输模式到后台的字符串
     * @param string $mode      nbd/nas
     */
    private function getTransportMode($mode,$hypervisor){
        if (!in_array($mode, array(1, 2, 3, 4))) {
            return $this->apiResponse(false, 'API_CODE_JOBS_TRANSPOT_PARAMS_ERROR');
        }

        $transportMode = 1;
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware']) || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']) {
            switch ($mode) {
                case 1:
                    $transportMode = "file:nbd:nbdssl:san:hotadd";  //网络传输  nbd
                    break;
                case 2:
                    $transportMode = "file:san:nbd:nbdssl:hotadd";  //san传输
                    break;
                case 3:
                    $transportMode = "file:nbdssl:nbd:san:hotadd";  //网络加密传输  nbdssl
                    break;
                case 4:
                    $transportMode = "file:hotadd:nbd:nbdssl:san"; //热添加传输
                    if ($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']) {
                        return $this->apiResponse(false, 'API_CODE_JOBS_TRANSPOT_PARAMS_ERROR');
                    }
                    break;
            }
        }
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['xenserver'])) {
            $transportMode = $mode;
        }

        return $transportMode;
    }
    
    /**
     * 根据时间点UUID得到所在节点UUID
     * @param string $timepointUUID
     * @return string
     */
    public function getNodeUUIDWithTimepointUUID($timepointUUID){
    	$sql = "select bsr.node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr
                where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = ?";
    	$data = $this->dbSelect($sql, array($timepointUUID));
    	return $data[0]['node_uuid'];
    }
    
    /**
     * 插入时间策略
     * @param int $strategyID 策略ID
     * @param int $modeType
     * @param array $strategyInfo
     */
    private function insertTimeStrategy($strategyID, $modeType, $strategyInfo){
    	$utils = Xphp::instance('Utils');
    	if(Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'] == intval($strategyInfo['strategy_type']) && count($strategyInfo['days']) != 7){
    		return $this->apiResponse(false, 'API_CODE_JOBS_WEEK_DAYS_LENGTH_ERROR');
    	}
    	 
    	if(Xphp::$_config['STRATEGY_TYPE']['EVERY_MONTH'] == intval($strategyInfo['strategy_type']) && count($strategyInfo['days']) != 31){
    		return $this->apiResponse(false, 'API_CODE_JOBS_MONTH_DAYS_LENGTH_ERROR');
    	}
    	$days = $this->getTimeStrategyDaysStr(intval($strategyInfo['strategy_type']), $strategyInfo['days']);
    	$sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
    	$sqlParams = array($strategyID, $modeType, $strategyInfo['strategy_type'], $days,
    			$utils->formartTime($strategyInfo['start_time']), $strategyInfo['roll_flag'], $utils->timeToSec($strategyInfo['roll_interval']),
    			$utils->formartTime($strategyInfo['roll_end_time'])
    	);
    
    	return $this->dbQuery($sql, $sqlParams);
    }
    
    /**
     * 获取时间策略天数的字符串表示
     * @param array $days
     */
    private function getTimeStrategyDaysStr($type, $days){
    	$str = '';
    	if(Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY'] == $type){
    	    //每天备份
    	    $days = [1,1,1,1,1,1,1];
    	}
    	if(empty($days)){
    		return $str;
    	}
    	$str = implode('', $days);
    	return $str;
    }
    
    /**
     * 根据时间策略type类型转为字符串 1. strategy 2.oncetime
     * @param array $days
     */
    public function getTimeStrategyType($type){
    	if($type == Xphp::$_config['FLAG']['SET']){
    		$strategyType = "strategy";
    	}else if($type == Xphp::$_config['FLAG']['UNSET']){
    		$strategyType = "oncetime";
    	}
    	return $strategyType;
    }
    
    /**
     * 组合恢复时间策略
     * @param array $params
     * @return array
     */
    public function groupRecoverTimeList($params,$type){
    	$timeList = array();
    	if(Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == intval($type)){
    		//立即恢复
    		return $timeList;
    	}else if(Xphp::$_config['RECOVERY_TIME_TYPE']['STRATEGY'] == intval($type)){
    		//按时间策略恢复
    		$timeList[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params);
    		return $timeList;
    	}
    }
    
    /**
     * 检查虚拟机名是否符合要求
     * @param string $vmname
     * @param int $hypervisor
     */
    private function checkVMname($vmname, $hypervisor){
		
		if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_KVM'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']){
			if(preg_match("/[^0-9a-zA-Z_]/", $vmname)){
				return $this->apiResponse(false, 'API_CODE_JOBS_VM_NAME_NOT_AVAILABLE');
			}
		}

        //ics/ics-vvdk只包含数字、字母、汉字、下划线(_)、连接符(-)、和点(.)
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor) {
            if(preg_match("/[^\w\x{4e00}-\x{9fa5}.-]/", $vmname)){
                return $this->apiResponse(false, 'API_CODE_JOBS_VM_NAME_NOT_AVAILABLE_ICS');
            }
        }
		
		if(preg_match("/[^\w\x{4e00}-\x{9fa5}]+/u", $vmname)){
			return $this->apiResponse(false, 'API_CODE_JOBS_VM_NAME_NOT_AVAILABLE');
		}
    }
    
    /**
     * 检测差异备份和增量备份是否共同存在
     * @param array $timeStrategy
     */
    private function checkTimeStrategy($timeStrategy){
    	if($timeStrategy['incr_info'] && $timeStrategy['diff_info']){
    		return $this->apiResponse(false, 'API_CODE_JOBS_TIME_STRATEGY_ERROR');
    	}
    }
    
    /**
     * 检查存储块大小是否符合规格
     * @param int $block_size
     */
    private function checkBlocksize($block_size){
    	if(!in_array(intval($block_size), Xphp::$_config['BLOCK_SIZE'])){
    		return $this->apiResponse(false, 'API_CODE_JOBS_STORAGE_BLOCK_SIZE_ERROR');
    	}
    }
    
    
    /**
     * 检查备份任务是否存在，任务状态是否正确
     * @param array $data
     */
    private function checkBackupTask($data){
	    //taskuuid不存在
        if(empty($data)){
	    	return $this->apiResponse(false, 'API_CODE_JOBS_JOB_UUID_NOT_EXIST');
	    }
	    if($data[0]['task_type'] != Xphp::$_config['TASKTYPE']['BACKUP'] || $data[0]['task_status'] != Xphp::$_config['TASKSTATUS']['STOPPED']){
	    	return $this->apiResponse(false, 'API_CODE_JOBS_EDIT_ERROR');
	    }
    }
     
    /**
     * 检查瞬时恢复任务是否存在，任务状态是否正确
     * @param string $taskuuid
     */
    private function checkMotionTask($taskuuid){
   		$sql = "select task_status, task_type from bd_task where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	//taskuuid不存在
    	if(empty($data)){
    		return $this->apiResponse(false, 'API_CODE_JOBS_JOB_UUID_NOT_EXIST');
    	}
    	if($data[0]['task_type'] != Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] || $data[0]['task_status'] != Xphp::$_config['TASKSTATUS']['RUNNING']){
    		return $this->apiResponse(false, 'API_CODE_JOBS_CREATE_MOTION_ERROR');
    	}
    }
    
    /**
     * 检查备份时间点是否存在
     * @param string $timepointuuid
     */
    private function checkTimepointExist($timepointuuid){
    	$sql = "select vm_config, hypervisor_type from vm_backup_timepoint where timepoint_uuid = ?";
    	$data = $this->dbSelect($sql, array($timepointuuid));
    	//timepointuuid不存在
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_JOBS_TIMEPOINT_UUID_NOT_EXIST');
    	}
    }
    
    /**
     * 检查时间格式的正确性
     * @param string $datetime
     * @param int $timetype
     */
    private function checkTimeFormat($datetime, $timetype){
    	if($timetype == 1) return;
    	$datetime = strtotime($datetime);   //转换为时间戳
    	$nowtime = strtotime(date("Y-m-d H:i:s"));
    	if($datetime < $nowtime){
    		return $this->apiResponse(false, 'API_CODE_JOBS_CREATE_TIME_NOT_CORRECT');
    	}
    
    }
    
    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName){
    	$sql = "select bt.task_uuid, bt.module_type, bt.task_type, vt.hypervisor_type
                from bd_task bt, vm_task vt where bt.task_uuid = vt.task_uuid and task_name = ?
                order by bt.id desc";
    	$data = $this->dbSelect($sql, array($taskName));
    	if(!$data) return false;
    	
    	$taskuuid = $data[0]['task_uuid'];
    	$hypervisor = $data[0]['hypervisor_type'];
    	//任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
    	$msg = array(
    			'task_uuid' => $taskuuid,
    			'backup_mode' => 1,
    			'time_strategy_id' => 0,
    			'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
    	);
    	$msg = json_encode($msg);
    	$opName = 'BD_TASK_OP_RECOVERY_START';
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskuuid);
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, FALSE, TRUE);
    	//这里直接返回成功或失败 bool
    	return $mbResult;
    	
    }
    
    
    /**
     * 根据备份时间点uuid得到vcenter_uuid
     * @param array $timepointuuid
     */
    private function getVcenteruuid($timepointuuid){
    	$sql = "select vcenter_uuid from vm_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($timepointuuid));
    	
        $vcenteruuid = $data[0]['vcenter_uuid'];
    	return $vcenteruuid;
    }
    
    /**
     * 得到任务运行日志
     * @param string $taskuuid
     * @param int $jobType
     */
    private function getRunningLog($taskuuid){
    	$sql = "select error_code, unix_timestamp(op_time) op_time, description_key, description_param, log_level from bd_task_log where task_uuid = ? and running_flag = ?";
    	$data = $this->dbSelect($sql, array($taskuuid, Xphp::$_config['FLAG']['SET']));
    	$runlog = array();
    	$logHandler = Xphp::instance('APILogsHandler');
    	foreach ($data as $d){
    		$runlog[] = array(
    			"log_time" => $d['op_time'],
    			"log_level" => $d['log_level'],
    			"description" => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],
    					$d['description_key'], $d['description_param'], $d['log_level']),
    		);
    	}
    	
    	return $runlog;
    }
    
    
    
    /**
     * 得到任务的高级功能配置
     * @param string $taskuuid
     * @param int $jobType
     */
    private function getHighConfig($taskuuid, $jobType){
    	$sql = "select level, hypervisor_type, quiesce_snapshot, valid_data_backup, transport_priority, serial_snapshot_flag, parse_fs_flag, not_backup_swap_file_flag,
    			 not_backup_deleted_file_flag, not_backup_partition_gap_flag from vm_task where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$hypervisor = $data[0]['hypervisor_type'];
    	$increMode = $this->groupIncreMode(intval($data[0]['level']), intval($data[0]['valid_data_backup']), intval($data[0]['hypervisor_type']));
    	$transport = $this->groupTransMode($data[0]['transport_priority']);
    	$highConfig = array(
    		"transport_priority" => $transport,
    		"quiesce_snapshot" => intval($data[0]['quiesce_snapshot']),
    		"incremental_mode" => $increMode,
    		"vcbt_flag" => intval($data[0]['parse_fs_flag']),
    		"not_backup_swap_file_flag" => intval($data[0]['not_backup_swap_file_flag']),
    		"not_backup_deleted_file_flag" => intval($data[0]['not_backup_deleted_file_flag']),
    		"not_backup_partition_gap_flag" => intval($data[0]['not_backup_partition_gap_flag']),
    		"snapshot_flag" =>  intval($data[0]['serial_snapshot_flag']),
    	);
    	if($jobType != Xphp::$_config['TASKTYPE']['BACKUP']){
    		$highConfig = array(
    			"transport_priority" => $transport,
    		);
    	}else if($jobType == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY']){
    		$highConfig = array();
    	}
    	return $highConfig;
    }
    
    /**
     * 得到传输方式
     * @param unknow $transport
     */
    private function groupTransMode($transport){
    	$type = '';
    	if($transport == "file:nbd:nbdssl:san:hotadd"){
    		$type = Xphp::$_config['TRANSPORT_MODE']['VMWARE_NBD']; //nbd
    	}else if($transport == "file:san:nbd:nbdssl:hotadd"){
    		$type = Xphp::$_config['TRANSPORT_MODE']['VMWARE_SAN']; //san
    	}else if($transport == Xphp::$_config['FLAG']['SET']){
    		$type = Xphp::$_config['TRANSPORT_MODE']['XEN_NBD'];
    	}else if($transport == Xphp::$_config['FLAG']['UNSET']){
    		$type = Xphp::$_config['TRANSPORT_MODE']['XEN_SAN'];
    	}
    	 
    	return $type;
    }
    
    /**
     * 得到高速模式
     * @param int $level
     * @param int $valid
     * @param int $hypervisor
     */
    private function groupIncreMode($level, $valid, $hypervisor){
    	$type = Xphp::$_config['HIGH_MODE']['NORMAL'];
    	if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']){
    		$type = $level;
    	}else if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
    		if($level == Xphp::$_config['FLAG']['UNSET'] && $valid == Xphp::$_config['FLAG']['UNSET']){
    			$type = Xphp::$_config['HIGH_MODE']['NORMAL'];
    		}else if($level == Xphp::$_config['FLAG']['UNSET'] && $valid == Xphp::$_config['FLAG']['SET']){
    			$type = Xphp::$_config['HIGH_MODE']['CBT'];
    		}
    	}else{
    		if($level == Xphp::$_config['FLAG']['SET'] && $valid == Xphp::$_config['FLAG']['SET']){
    			$type = Xphp::$_config['HIGH_MODE']['NORMAL'];
    		}else if($level == Xphp::$_config['FLAG']['UNSET'] && $valid == Xphp::$_config['FLAG']['SET']){
    			$type = Xphp::$_config['HIGH_MODE']['HIGH'];
    		}
    	}
    	return $type;
    }
    
    /**
     * 得到需要传输的磁盘信息
     * @param array $storage
     */
    private function groupStorage($storage){
    	$info = array();
    	foreach ($storage as $s){
    		$info[] = array(
    			"src_disk_uuid" => $s['vdi_uuid'],
    			"size" => intval($s['size']),
    			"auto_conf_flag" =>	1,
    			"target_storage_uuid" => "",
    			"disk_type" => null,
    		);
    	}
    	
    	return $info;
    }
    
    /**
     * 得到是否高速模式
     * @param int $type
     * 1 普通   2 高速   3 cbt
     */
    private function getIncreMode($type, $hypervisor){
        $info = array(
            'backup_level' => $type,   
            'valid_data_backup' => $type == 3? Xphp::$_config['FLAG']['SET']:Xphp::$_config['FLAG']['UNSET'],//CBT
        );
        if($type > 3){
            return $this->apiResponse(false, 'API_CODE_JOBS_BACKUP_MODE_PARAM_ERROR');
        }
    	return $info;
    }
    
    /**
     * 删除任务前检查任务状态
     * @param string $jobuuid
     */
    private function checkDeleteStatus($jobuuid){
    	$sql = "select task_status from bd_task where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($jobuuid));
    	//传入的jobuuid是否错误
    	if(empty($data)){
    		return $this->apiResponse(false, 'API_CODE_JOBS_DELETE_JOB_UUID_NOT_EXIST');
    	}
    	$status = intval($data[0]['task_status']);
    	if($status != Xphp::$_config['TASKSTATUS']['STOPPED']){
    		return $this->apiResponse(false, 'API_CODE_JOBS_DELETE_JOB_CHECK_STATUS');
    	}
    }
    
    /**
     * 得到时间策略信息
     * @param int $strategyID
     * @return array
     */
    public function getJobTimeStrategy($strategyID){
        $this->apiParamsCheck($strategyID);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $utils = Xphp::instance('Utils');
        $strategy = array();
        foreach ($data as $d){
            $type = intval($d['strategy_type']);
            $strategy[] = array(
                'mode' => intval($d['mode']),
                'type' => $type,
                'days' => $this->getDaysArr($type, $d['days']),
                'start_time' => $d['start_time'],
                'roll_flag' => intval($d['roll_flag']),
                'roll_interval' => $utils->secToTime($d['roll_interval']),
                'end_time' => $d['roll_end_time'],
            );
        }
        return $strategy;
    }
    
    /**
     * 得到天的数组
     * @param unknown $days
     */
    private function getDaysArr($type, $days){
        if(Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY'] == $type){
            //每天直接返回空字符串
            $days = "";
        }
        if (empty($days)){
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        $days = array();
        foreach ($daysArr as $key => $d){
            //如果遇到s,结束    现在每周存储格式为0000001s1   s1表示间隔一周,以此类推
            if("s" == $d){
                break;
            }
            $days[] = intval($d);
        }
        return $days;
    }
    /**
     * 得到保留策略信息
     * @param int $strategyID
     * @return array
     */
    public function getReservedStrategy($strategyID){
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
    				'type' => $data[0]['storage_type'],
    				'size' => $data[0]['total_size'],
    				'free_size' =>$data[0]['free_size'],
    		);
    	}
    	//高级信息(重删/压缩/数据块大小等)
    	$sql = "select deduplication_flag, block_size, compressed_flag, encrypted_flag, password from bd_storage_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql, array($strategyid));
    	if($data){
    		$info['storage_strategy'] = array(
    		      'deduplication' => intval($data[0]['deduplication_flag']),
    		      'block_size' => intval($data[0]['block_size']),
    		      'compressed' => intval($data[0]['compressed_flag']),
    		      'encrypted_flag' => intval($data[0]['encrypted_flag']),
    		      'password' => $utils->ptPassDecrypt($data[0]['password'])
    		);
    	}
    	return $info;
    }
    
    /**
     * 获取瞬时恢复任务的基本信息和虚拟机信息
     * @param unknown $params
     */
    public function getIntantBaseInfo($taskUUID){
    	$sql = "select bt.task_name, bt.task_status, bt.task_type, vh.host_ip,
                	   vi.nfs_server_ip, vi.timepoint_uuid, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state, target_host_uuid, target_vcenter_uuid 
                from bd_task bt, vm_instant vi, vm_host vh
                where bt.task_uuid = vi.task_uuid and
                      vh.vcenter_uuid = vi.target_vcenter_uuid and
                      vh.host_uuid = vi.target_host_uuid and
                	  bt.task_uuid = ? ";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$info = array(
    			'server_ip' => $data[0]['nfs_server_ip'],
    			'host_ip' => $data[0]['host_ip'],
    			'vm_name' => $data[0]['new_vm_name'],
    			'nfs_state' => intval($data[0]['nfs_datastore_state']),
    			'host_state' => intval($data[0]['instant_host_state']),
    			'vm_state' => intval($data[0]['new_vm_status']),
    			'timepoint_uuid' => $data[0]['timepoint_uuid'],
    			'destination_host_uuid' => $data[0]['target_host_uuid'],
    			'destination_vcenter_uuid' => $data[0]['target_vcenter_uuid'],
    			'vm_flag' => Xphp::$_config['INSTANT_VM_FLAG']['UNKNOWN']
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
    	if(intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['STOPPED']){
    		//任务处于停止状态
    		$info['nfs_state'] = 0;
    		$info['host_state'] = 0;
    		$info['vm_state'] = 0;
    	}
    
    	return $info;
    }
    
    /**
     * 获取迁移任务的基本信息和虚拟机信息
     * @param unknown $params
     */
    public function getMotionBaseInfo($taskUUID){
    	$this->paramsCheck($taskUUID);
    	$sql = "select bt.task_name, bt.task_status, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state,
                       vi.target_vcenter_uuid, vi.target_host_uuid, vi.motion_host_state
                from bd_task bt, vm_instant vi, vm_host vh
                where bt.task_uuid = vi.task_uuid and
                      vh.vcenter_uuid = vi.target_vcenter_uuid and
                      vh.host_uuid = vi.target_host_uuid and
                	  bt.task_uuid = ? ";
    	$InstantData = $this->dbSelect($sql, array($taskUUID));
    	$sql = "select vml.vm_name, vml.task_status, vml.dir_path, vml.new_name,
                       vml.vm_uuid, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid,
                        bt.task_status as bd_task_status
                from vm_machine_list vml
                left join bd_task bt
                on bt.task_uuid = vml.task_uuid
                where vml.task_uuid = ? group by vml.vm_uuid";
    	$taskData = $this->dbSelect($sql, array($taskUUID));
    	$info = array(
    			'server_ip' => $InstantData[0]['nfs_server_ip'],                                //备份中心IP
    			'instant_host_ip' => $InstantData[0]['host_ip'],                                //瞬时恢复宿主机IP
    			'instant_vm_name' => $InstantData[0]['new_vm_name'],                            //瞬时恢复虚拟机名
    			'nfs_state' => intval($InstantData[0]['nfs_datastore_state']),                  //NFS状态
    			'instant_host_state' => intval($InstantData[0]['instant_host_state']),          //瞬时恢复宿主机状态
    			'instant_vm_state' => intval($InstantData[0]['new_vm_status']),                 //瞬时恢复虚拟机状态
    			'datastore_state' => $this->getDataStoreStatus($taskData[0]['bd_task_status']), //迁移数据状态
    			'motion_vm_name' => $taskData[0]['new_name'],                                   //迁移虚拟机名称
    			'timepoint_uuid' => $taskData[0]['timepoint_uuid']
    			
    	);
    
    	if($InstantData[0]['target_vcenter_uuid'] == $taskData[0]['vcenter_uuid'] &&
    			$InstantData[0]['target_host_uuid'] == $taskData[0]['host_uuid']){
    		//如果是原机恢复
    		$info['flag'] = 1;  //原机迁移
    	}else{
    		//异机恢复
    		$hostInfo = $this->getDesHostInfo($taskData[0]['vcenter_uuid'], $taskData[0]['host_uuid']);
    		$info['flag'] = 2;  //原机迁移
    		$info['motion_host_ip'] = $hostInfo['hostIP'];  //迁移宿主机IP
    		$info['motion_host_state'] = intval($InstantData[0]['motion_host_state']);  //迁移宿主机状态
    	}
    
    	return $info;
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
     * 得到推送的单个当前任务详情
     * @param string $jobUUID
     */
    public function getCurrentPushJobDetail($jobUUID){
    	$sql = "select bt.transport_ip_segment, bt.thread_num, bt.task_uuid, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.task_name, bt.module_type, bt.task_type,
                        unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.storage_uuid, vt.hypervisor_type, vt.transport_priority,
                		bri.speed, bri.speed_time, bri.total_object_size, bri.current_object_total_size, bri.total_object_completed_size,
        				unix_timestamp(bri.start_time) start_time, unix_timestamp(bri.finish_time) finish_time, unix_timestamp(bs.next_start_time) next_start_time
                from bd_task bt, bd_running_info bri, vm_task vt, bd_strategy bs
                where bt.task_uuid = bri.task_uuid and
                      bt.task_uuid = vt.task_uuid and
    				  bt.strategy_id = bs.strategy_id and
                     bt.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($jobUUID));
    
    	//根据任务uuid和任务类型获取高级功能配置
    	$type = intval($data[0]['task_type']);
    	$config = $this->getHighConfig($jobUUID, $type);
    	$runLog = $this->getRunningLog($jobUUID);
    	$record = array();
    	if(!empty($data)){
    		$sqlvm = "select host_uuid, vcenter_uuid, new_name from vm_machine_list where task_uuid = ?";
    		$datavm = $this->dbSelect($sqlvm, array($jobUUID));
    		$vmLists = $this->getVMListsWithJobUUID($jobUUID);
    		$startTime = (string)$data[0]['start_time'];
    		$endTime = (string)strtotime($this->getCalEndTime($data[0]['total_size'], $data[0]['current_total_size'], $data[0]['task_status'], $data[0]['speed']));
    		$record = array(
    				"job_name" => $data[0]['task_name'],
    				"job_uuid" => $data[0]['task_uuid'],
    				"job_type" => $data[0]['task_type'],
    				"create_time" => $data[0]['create_time'],
    				"job_status" => $data[0]['task_status'],
    				"hypervisor" => $data[0]['hypervisor_type'],
    				"speed" => $data[0]['speed'],
    				"speed_time" => $data[0]['speed_time'],
    				"storage_uuid" => $data[0]['storage_uuid'],
    				"running_log" => $runLog,
    		        "node_uuid" => $data[0]['node_uuid'],
    		        "thread_num" => intval($data[0]['thread_num']),
    		        "transport_ip_segment" =>$data[0]['transport_ip_segment']
    
    		);
    		if($type == Xphp::$_config['TASKTYPE']['BACKUP']){
    			$info = array(
    					'start_time' => $startTime,
    					'end_time' => $endTime,
    					'interval_time' => (string)$this->getTimeInterval($data[0]['start_time'], $data[0]['task_status']),
    			        'next_time' => strtotime($this->getNextStartTime($data[0]['next_start_time'], $data[0]['task_status'])),
    					'total_size' => $data[0]['total_object_size'],
    					'processed_size' => $data[0]['total_object_completed_size'],
    			        'job_process' => $this->getTaskTotalProgress($data[0]['task_status'], $data[0]['total_object_size'], $data[0]['total_object_completed_size'], true, $type), 
    					'vm_list' => $vmLists,
    					'time_strategy' => $this->getJobTimeStrategy($data[0]['strategy_id']),
    					'reserved_strategy' => $this->getReservedStrategy($data[0]['strategy_id']),
    			        'transport_strategy' => $this->getTransportStrategy($jobUUID, $data[0]['strategy_id'], intval($data[0]['hypervisor_type']), $data[0]['transport_priority']),
    			        'speed_strategy' => $this->getSpeedStrategyInfo($jobUUID),
    			        'storage_info' => $this->getNodeInfo($type, $data[0]['strategy_id'], $data[0]['node_uuid'], $data[0]['storage_uuid']),
    			        'job_config' => $this->getVMBackupModeStrategy($jobUUID, intval($data[0]['hypervisor_type']), $data[0]['transport_priority']),
    					
    			);
    			$record = array_merge($record, $info);
    		}else if($type == Xphp::$_config['TASKTYPE']['RECOVERY']){
    			$info = array(
    					'start_time' => $startTime,
    					'end_time' => $endTime,
    					'interval_time' => (string)$this->getTimeInterval($data[0]['start_time'], $data[0]['task_status']),
    			        'next_time' => strtotime($this->getNextStartTime($data[0]['next_start_time'], $data[0]['task_status'])),
        			    'total_size' => $data[0]['total_object_size'],
    					'processed_size' => $data[0]['total_object_completed_size'],
    			        'job_process' => $this->getTaskTotalProgress($data[0]['task_status'], $data[0]['total_object_size'], $data[0]['total_object_completed_size'], true, $type),
    					"vm_list" => $vmLists,
    					'time_strategy' => $this->getJobTimeStrategy($data[0]['strategy_id']),
    			        'speed_strategy' => $this->getSpeedStrategyInfo($jobUUID),
    			        'destination_host_uuid' => $datavm[0]['host_uuid'],
    					'destination_vcenter_uuid' => $datavm[0]['vcenter_uuid'],
    					'new_name' => $datavm[0]['new_name'],
    					"job_config" => $config
    			);
    			$record = array_merge($record, $info);
    		}else if($type == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY']){
    			$info = $this->getIntantBaseInfo($jobUUID);
    			$record = array_merge($record, $info);
    		}else if($type == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION']){
    			$info = array(
    					'start_time' => $startTime,
    					'end_time' => $endTime,
    					'interval_time' => (string)$this->getTimeInterval($data[0]['start_time'], $data[0]['task_status']),
    					'total_size' => $data[0]['total_object_size'],
    					'processed_size' => $data[0]['total_object_completed_size'],
    			        'job_process' => $this->getTaskTotalProgress($data[0]['task_status'], $data[0]['total_object_size'], $data[0]['total_object_completed_size'], true, $type),
    					"job_config" => $config,
    					'motion_info' => $this->getMotionBaseInfo($jobUUID),
    					"vm_list" => $vmLists,
    					'destination_host_uuid' => $datavm[0]['host_uuid'],
    					'destination_vcenter_uuid' => $datavm[0]['vcenter_uuid']
    			);
    			$record = array_merge($record, $info);
    		}
    	}
    	return $record;
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
    			return $briCompletedSize;
    		}elseif($vmTaskStatus == Xphp::$_config['VmTaskStatus']['WAITTING'] ){
    			//虚拟机是等待状态,显示0B
    			return "0B";
    		}else{
    			//虚拟机不在运行状态,显示vm_machine_list的完成大小
    			return $vmlCompletedSize;
    		}
    	}else{
    		return Xphp::$_config['NULLSPACE'];
    	}
    }
    
    
    /**
     * 获取有效数据总大小
     * @param array $details
     */
    private function getTotalValidSize($details){
        $details = $details['vms_details'] ?? $details;
    	if(!$details){
    		return null;
    	}
    	foreach ($details as $d){
    		$totalValidSize += $d['vm_valid_size'];
    	}
    	return $totalValidSize;
    }
    
    /**
     * 获取虚拟化详细信息
     * @param array $detail
     * @param int $taskType 
     */
    private function getDiffDetail($detail, $taskType){
    	$info = array();
    	if(empty($detail['vcenter_uuid'])){
    	    return $info;
    	}
    	if(Xphp::$_config['TASKTYPE']['BACKUP'] == $taskType){
    		$info = array(
    			"vcenter_uuid" => $detail['vcenter_uuid'],
    		);
    	}else if(Xphp::$_config['TASKTYPE']['RECOVERY'] == $taskType){
    		$info = array(
    		    "vcenter_uuid" => $detail['vcenter_uuid'],
    			'timpoint_time' => $detail['timepoint'],
    			'recovery_path' => $detail['vcenter_ip'] . '/' . $detail['host_ip'] . '/' . $detail['new_name']
    		);
    	}else if(Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'] == $taskType){
    		$info = array(
    		    "vcenter_uuid" => $detail['vcenter_uuid'],
    			'timpoint_time' => $detail['timepoint'],
    			'motion_path' => $detail['vcenter_ip'] . '/' . $detail['host_ip'] . '/' . $detail['new_name']
    		);
    	}
    	
    	return $info;
    }
    
    /**
     * 获取虚拟化中心uuid
     * @param string $ip
     */
    private function getVcenteruuidByIp($ip){
    	$sql = "select vcenter_uuid from vm_vcenter where vcenter_ip = ?";
    	$data = $this->dbSelect($sql, array($ip));
    	$vcenteruuid = $data[0]['vcenter_uuid'];
    	return $vcenteruuid;
    }
    
    /**
     * 得到虚拟机错误描述
     * @param unknown $status
     * @param unknown $errorCode
     */
    public function getVMErrorCodeDes($status, $errorCode, $error){
    	if(Xphp::$_config['VmTaskStatus']['ERROR'] == intval($status)){
    	    $des = $error['errorCodeDes'][$error['errorCode'][intval($errorCode)]];
    	}else{
    		$des = '';
    	}
    	return $des;
    }
    
    /**
     * 获取当前备份任务策略信息
     * @param string $strategyId
     * @param int $taskType
     */
    private function getCurrentStrategy($strategyId, $taskType){
    	$modeList = array();
    	if(Xphp::$_config['TASKTYPE']['BACKUP'] != $taskType){
    		return $modeList;
    	}
    	$timeStrategy = $this->getJobTimeStrategy($strategyId);
    	foreach ($timeStrategy as $t){
    		$modeList[] = intval($t['mode']);
    	}
    	return $modeList;
    	
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
    	return $size;
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
     * 得到加密传输
     * @param string $strategyID
     */
    private function getEncrypt($strategyID){
    	$sql = "select encrypt_flag from bd_transport_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql, array($strategyID));
    	$encrypt = intval($data[0]['encrypt_flag']);
    	return $encrypt;
    }
    
    /**
     * 得到任务运行的持续时间
     * @param timestamp $startTime
     */
    public function getTimeInterval($startTime, $status){
    	if($status == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
    			$status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] ||
    			$status == Xphp::$_config['TASKSTATUS']['ABNORMAL']){
    		$apiSettingsHandler = Xphp::instance('APISettingsHandler');
    		$nowTime = $apiSettingsHandler->getSystemTime();
    		$intval = $nowTime - $startTime;
    		$utils = Xphp::instance('Utils');
    		return $utils->secToTime($intval);
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
            return $this->parseDate($nextTime)."(".Xphp::$_lang['UI_PUBLIC_EXPIRED'].")";
        }
        
        //任务处于停止状态下,没有下次开始时间
        if(intval($taskStatus) == Xphp::$_config['TASKSTATUS']['STOPPED']){
            return Xphp::$_config['TIMESPACE'];
        }
        return $this->parseDate($nextTime);
    }
    
    /**
     * 根据任务运行情况估算完成时间
     * @param int $totalSize
     * @param int $currentSize
     * @param int $taskStatus
     * @param int $speed
     */
    public function getCalEndTime($totalSize, $currentSize, $taskStatus, $speed){
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
    	$pfDes = include APP_PATH . "platform/PFDescription.php";
    	$des = '';
    	if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
    		$des = $pfDes['BACKUP_MODE_DES'][$currentMode];
    	}
    	$des .= $pfDes['TASKTYPEDES'][$taskType];
    	return $des;
    }
    
    /**
     * 组合备份节点信息
     *  @param int $autoNodeCheck   自动选择节点flag
     *  @param int $nodeuuid  节点UUID
     *  @param bool storagecheck 自动选择存储
     *  @param string storageuuid   存储UUID
     */
    protected function groupBackupNodeInfo($autoNodeCheck, $storageCheck, $storageUUID, $nodeuuid){
    	$storageuuid = $storageUUID;
    	if($storageCheck == Xphp::$_config['FLAG']['SET'] || $autoNodeCheck == Xphp::$_config['FLAG']['SET']){
    		$storageuuid = "";
    	}
    	$strArr = array(
    			'node_uuid' => $this->getBackupNodeUUID($autoNodeCheck, $nodeuuid),
    			'auto_find_sr_flag' => $storageCheck,
    			'storage_uuid' => $storageuuid,
    	);
    	return $strArr;
    }
    
    /**
     * 得到备份的节点信息
     *  @param int $autoNodeCheck   自动选择节点
     *  @param string $nodeuuid  节点UUID
     * @return string
     */
    protected function getBackupNodeUUID($autoNodeCheck, $nodeuuid){
    	if($autoNodeCheck == Xphp::$_config['FLAG']['UNSET']){
    		//自定义节点
    		return $nodeuuid;
    	}
    	//自动选择节点
    	$apiNodeHandler = Xphp::instance('APINodesHandler');
    	return $apiNodeHandler->getAutoFindNode();
    }
    
    
    /**
     * 得到排除备份的磁盘信息
     * @return string $vmuuid
     */
    private function getDiskInfo($vmuuid,$taskType, $jobuuid){
    	$sql = "select vm_config from vm_machine_list where vm_uuid = ? and task_uuid = ?";
    	$data = $this->dbSelect($sql, array($vmuuid, $jobuuid));
    	if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
    		$info = json_decode($data[0]['vm_config'], true);
    		$diskInfo = $info['disk_list'];
    	}else{
    		$diskInfo = array();
    	}
    	return $diskInfo;
    }
    
    /**
     * 创建虚拟机恢复/瞬时恢复/迁移任务检测目的虚拟化中心是否有同名的虚拟机存在
     * @param string $vcenteruuid  目的VCENTERUUID
     * @param string $vmname       新的虚拟机名
     * @return boolean
     */
    private function createRecoverJobCheck($hypervisor, $vcenteruuid, $vmname){
        //ics/ics-vvdk支持虚拟机同名
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor) {
            return true;
        }
    	//检查target虚拟化中心是否有相同名字的虚拟机存在，如果存在则对其进行提示
    	if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
    		//如果是VMware
    		$displayMode = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'];
    		//         }elseif($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']){
    	}else{
    		//如果是XenServer
    		$displayMode = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
    	}
    	$sql = "select name from vm_tree where vcenter_uuid = ? and display_mode = ? and name = ? ";
    	$data = $this->dbSelect($sql, array($vcenteruuid, $displayMode, $vmname));
    	if($data){
    		//检查如果有相同名字的虚拟机,程序中断返回提示信息
    		exit($this->apiResponse(false, 'API_CODE_JOBS_VM_NAME_EXIST'));
    	}
    	return true;
    }
    
    /**
     * 根据模块统一发送消息
     * @param string $jobuuid
     * @param string $opName  
     * @param string $apiCode
     */
    public function opUnifyMsg($jobuuid, $opName, $apiCode){
    	$module = $this->getModuleTypeWithJobUUID($jobuuid);  //模块类型
    	$subModule = 0; //初始化虚拟化类型
        $nodeuuid = Xphp::instance('APINodesHandler', 'getJobExitNodeUUID', $jobuuid);
    
    	$msg = json_encode(array('task_uuid'=>$jobuuid));
    	if('BD_TASK_OP_BACKUP_DELETE' == $opName || 'BD_TASK_OP_RECOVERY_DELETE' == $opName
    			|| 'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK' == $opName ||
    			'VM_PRIVATE_TASK_OP_DELETE_ORCH_TASK' == $opName ||
    			'BD_TASK_OP_CDP_BACKUP_DELETE' == $opName ||
    			'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK' == $opName ||
    			'BD_TASK_OP_BACKUP_COPY_DELETE' == $opName ||
    			'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE' == $opName){
    			$msg = json_encode(array('task_uuid_list'=>array($jobuuid)));
    	}
    
    	$moduleType = Xphp::$_config['MODULE_TYPE'];
    	$sync = FALSE;
    	$command = TRUE;
    	
    	switch ($module){
    		case $moduleType['VM']:
    			$subModule = $this->getHypervisorWithJobUUID($jobuuid);  //虚拟化类型
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
        
        return $this->apiResponse($mbResult['result'], $apiCode, array(), $mbResult);
    }
    
    
    /**
     * 检查任务是否在运行状态
     * @param string $taskuuid
     */
    private function checkTaskRun($taskuuid){
        $sql = "select task_status from bd_task where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        if($data[0]['task_status'] == Xphp::$_config['TASKSTATUS']['RUNNING']){
            exit($this->apiResponse(false, 'API_CODE_SELECT_VM_JOB_RUNNING_EXIST'));
        }
    }
    
    /**
     * 获取虚拟机网卡列表
     * @param string $vmuuid
     * @param string $vcenteruuid
     */
    private function getVmNetworkList($vmuuid, $vcenteruuid){
        $sql = "select vt.detail, vv.hypervisor_type from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.uuid = ? and vt.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vmuuid, $vcenteruuid));
        $info = array();
        if(!empty($data)){
            $hypervisor = $data[0]['hypervisor_type'];
            $detail = json_decode($data[0]['detail'], true);
            $networkList = $detail['network_list'];
            if(!empty($networkList)){
                foreach ($networkList as $network){
                    $ipList = explode(" ", $network['ip']);
                    $info[] = array(
                        'iface_name' => $network['network_name'],
                        'iface_mac' => $network['mac'],
                        'ipaddrs' => $ipList
                    );
                }
            }
        }
        return $info;
    }
    
    
    /**
     * 获取当前备份虚拟机生成的时间点
     * @param unknown $taskuuid
     * @param unknown $vmuuid
     */
    private function getCurrentTimepoint($taskuuid,$vmuuid){
        $sql = "select bbt.timepoint, bbt.backup_mode from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ? and vbt.vm_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid, $vmuuid));
        $des = "";
        $modeDes = array('', Xphp::$_lang['UI_BACKUP_FULL'], Xphp::$_lang['UI_BACKUP_INCREMENT'], Xphp::$_lang['UI_BACKUP_DIFFERENCE']);
        if(!empty($data)){
            $des = $data[0]['timepoint']."(".$modeDes[intval($data[0]['backup_mode'])].")";
        }
        
        return $des;
        
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
     * 组装虚拟机信息获取恢复需要配置
     * @param unknown $vmList
     * @param unknown $destination
     * @param unknown $desHypervisor
     * @return boolean[]|unknown[]|unknown[][]|number[][][]|unknown[][][]|NULL[][][]|fetchAll()[][][]
     */
    public function getRecoverVMInfo($vmList, $destination, $desHypervisor, $instantFlag = false){
        $points = array();
        $pointDetail = array();
        $vcenteruuid = $destination['vcenter_uuid'];
        $hostuuid = $destination['host_uuid'];
        foreach ($vmList as $vm){
            $points[] = $vm['timepoint_uuid'];
        }
        $pointsDes = implode("','", $points);
        $sql = "select vbt.vm_uuid, vbt.vcenter_uuid, vbt.vm_name, vbt.hypervisor_type, bbt.detail, bbt.timepoint_uuid, bbt.timepoint, bsr.node_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid in ('".$pointsDes."')";
        $data = $this->dbSelect($sql);
        $pointList = array();  //传递时间点集合给后台,和pointsDetail的时间点信息保持顺序一致
        foreach ($data as $d){
            foreach ($vmList as $vm){
                if($d['timepoint_uuid'] == $vm['timepoint_uuid']){
                    $pointList[] = $vm['timepoint_uuid'];
                    $pointDetail[] = array(
                        'hyperviosr' => intval($d['hypervisor_type']),
                        'timepointuuid' => $d['timepoint_uuid'],
                        'vcenteruuid' => $d['vcenter_uuid'],
                        'vmuuid' => $d['vm_uuid'],
                        'vmname' => $vm['new_name'],
                        'oldname' => $d['vm_name'],
                        'timepoint' => $d['timepoint'],
                        'nodeuuid' => $d['node_uuid'],
                        'config' => json_decode($d['detail'], true)
                    );
                }
            }
        }
        
        $info = array(
            'hypervisor' => $desHypervisor,
            'vcenteruuid' => $vcenteruuid,
            'points' => $pointList,
            'pointsDetail' => $pointDetail,
            'instantFlag' => $instantFlag
        );
        return $info;
    }
    
    /**
     * 组合限速策略列表
     * @param unknown $params
     */
    private function groupTaskSpeedList($speedList){
        $info = array();
        $utils = Xphp::instance('Utils');
        foreach ($speedList as $speed){
            $des = "";
            //永久限速
            if($speed['type'] == Xphp::$_config['STRATEGY_TYPE']['ONCE']){
                $des .= "永久限速，限速大小：" . $utils->calSpeed($speed['value']);
            }else{
                //按策略
                $des .= $this->getSpeedStrategyDes($speed) ."，限速大小：" . $utils->calSpeed($speed['value']);
            }
            $info[] = array(
                'strategy_uuid' => $utils->uuid(),
                'strategy_name' => '',
                'strategy_type' => $speed['type'],
                'start_time' => !empty($speed['start_time']) ? $utils->formartTime($speed['start_time']):"",
                'end_time' =>  !empty($speed['end_time']) ? $utils->formartTime($speed['end_time']):"",
                'days' => !empty($speed['days']) ? implode("", $speed['days']): "",
                'speed_limited_value' => $speed['value'],
                'extra_info' => '',
                'remark' => $des,
                'strategy_group_uuid' => "",
            );
        }
        return $info;
    }
    
    /**
     * 组合限速策略描述
     * @param unknown $speed
     */
    private function getSpeedStrategyDes($speed){
        $des = "";
        $modeDes = array('');
        $typeDes = array('', Xphp::$_lang['UI_STRATEGY_EVERY_DAY'], Xphp::$_lang['UI_STRATEGY_EVERY_WEEK'], Xphp::$_lang['UI_STRATEGY_EVERY_MONTH']);
        $des .= Xphp::$_lang['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] . " (";
        $des .= $typeDes[$speed['type']];
        foreach ($speed['days'] as $key=>$d){
            
            if(1 == $speed['type']) break;	//每天的话不读取days
            if($d){
                $des .= ($key + 1) . ", ";
            }
        }
        $des .= $speed['start_time'] . Xphp::$_lang['UI_STRATEGY_STARTTIME'] . ", ";
        $des .= $speed['end_time'] . Xphp::$_lang['UI_STRATEGY_OVER_TIME'] ;
        $des .= ")";
        
        return $des;
    }
    
    /**
     * 根据任务状态计算进度
     * @param int $taskStatus
     * @param int $totalSize
     * @param int $currentSize
     * @param boolean $percentFlag  是否一定要得到百分比格式
     */
    private function getTaskTotalProgress($taskStatus, $totalSize, $currentSize, $percentFlag, $tasktype){
        if($tasktype == Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] ||
            $tasktype == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] ||
            $tasktype == Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'] ||
            $tasktype == Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'] ||
            $tasktype == Xphp::$_config['TASKTYPE']['DB_BACKUP']){
                //细粒度恢复,瞬时恢复,数据库实时备份,文件实时备份不显示进度
                return Xphp::$_config['NULLSPACE'];
        }
        if($taskStatus != Xphp::$_config['TASKSTATUS']['RUNNING'] &&
            $taskStatus != Xphp::$_config['TASKSTATUS']['PAUSED']){
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
     * 获取任务限速策略列表信息
     * @param string $taskuuid
     */
    private function getSpeedStrategyInfo($taskuuid){
        $sql = "select strategy_uuid, speed_limited_value, start_time, end_time, days, remark, strategy_type from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        $utils = Xphp::instance('Utils');
        foreach($data as $d){
            $value = intval($d['speed_limited_value']);
            $valueList = $utils->calSizeToValueAndUnit($value);
            $info[] = array(
                'uuid' => $d['strategy_uuid'],
                'type' => $d['strategy_type'],
                'start_time' => $d['start_time'],
                'end_time' => $d['end_time'],
                'days' => $this->parseSpeedStrategyDay($d['days']),
                'value' => $value,
                'des' => $d['remark'],
                'unit' => $valueList['unit'].'/s',
                'speednum' => intval($valueList['value'])
            );
        }
        return $info;
    }
    
    /**
     * 得到任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     * @param int $hypervisor   虚拟化类型
     */
    private function getTransportStrategy($taskuuid, $strategyid, $hypervisor, $transport_priority){
       $sql = "select encrypt_flag, compress_flag from bd_transport_strategy where task_uuid = ? and strategy_id = ? ";
       $data = $this->dbSelect($sql, array($taskuuid, $strategyid));
       $utils = Xphp::instance('Utils');
       $info = array(
           'encrypt_flag' => intval($data[0]['encrypt_flag']),
           'compress_flag' => intval($data[0]['compress_flag']),
           'transport_priority' => $this->getTransportModeToArr($transport_priority,$hypervisor)
       );
       
       return $info;
    }
    
    /**
     * 得到虚拟机备份模式配置信息
     * 静默快照,高速模式
     * @param string $taskuuid
     * @return array
     */
    private function getVMBackupModeStrategy($taskuuid, $hypervisor, $transport_priority){
        $sql = "select pre_create_snap_flag, level, quiesce_snapshot, hypervisor_type, valid_data_backup, serial_snapshot_flag, parse_fs_flag,
                not_backup_swap_file_flag, not_backup_deleted_file_flag, not_backup_partition_gap_flag
                 from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $utils = Xphp::instance('Utils');
        $mode = array(
            'incremental_mode' => $data[0]['level'],
            'quiesce_snapshot' => intval($data[0]['quiesce_snapshot']),
            'snapshot_flag' => $data[0]['serial_snapshot_flag'],
            'vcbt_flag' => intval($data[0]['parse_fs_flag']),
            'not_backup_swap_file_flag' => intval($data[0]['not_backup_swap_file_flag']),
            'not_backup_deleted_file_flag' => intval($data[0]['not_backup_deleted_file_flag']),
            'not_backup_partition_gap_flag' => intval($data[0]['not_backup_partition_gap_flag']),
            'pre_create_snap_flag'=> intval($data[0]['pre_create_snap_flag']),
            'transport_priority' => $this->getTransportModeToArr($transport_priority,$hypervisor),
        );
        return $mode;
    }
    
     /**
     * 得到从数据库获取的传输模式,修改任务用
     * @param string $transpoertMode
     * @param array
     */
    private function getTransportModeToArr($transpoertMode, $hypervisor)
    {
        $mode = 1;
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware']) || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']) {
            if ($transpoertMode == "file:nbd:nbdssl:san:hotadd") { //网络传输  nbd
                $mode = 1;
            } else if ($transpoertMode == "file:san:nbd:nbdssl:hotadd") {//san传输
                $mode = 2;
            } else if ($transpoertMode == "file:nbdssl:nbd:san:hotadd") {//网络加密传输  nbdssl
                $mode = 3;
            } else if ($transpoertMode == "file:hotadd:nbd:nbdssl:san") {//热添加传输
                $mode = 4;
            }
        }
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['xenserver'])) {
            $mode = $transpoertMode;
        }

        if (!in_array($mode, array(1, 2, 3, 4))) {
            return $this->apiResponse(false, 'API_CODE_JOBS_TRANSPOT_PARAMS_ERROR');
        }

        return intval($mode);
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
     * 转换限速策略天数为一个数组,每一项为0,1
     * @param unknown $days
     */
    private function parseSpeedStrategyDay($days){
        if (empty($days)){
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        foreach ($daysArr as $key => $d){
            if($d){
                $daysArr[$key] = 1;
            }else{
                $daysArr[$key] = 0;
            }
        }
        return $daysArr;
    }
}