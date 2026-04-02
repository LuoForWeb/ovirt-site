<?php
/******************************************* 
** 副本API处理类
** 
** @author       luokai@vinchin.com 
** @date         2019-08-19 
** @version      1.0.0 
** @copyright    Copyright 2019 vinchin.com 
********************************************/
class APICopyHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/copy/copybackup" => array(
            'POST' => 'createCopyBackup',
        	'PUT' => 'editCopyBackup',
        	'GET' => 'getCopyTaskAllInfo'
        ),
        
        "/copy/copyback" => array(
            'POST' => 'createCopyBack'
        ),
    	"/copy/vmlist" => array(
    		'GET' => "getCopyVmList"
    	),
        "/copy/copydata" => array(
            'GET' => 'getVmCopyData'
        ),
        
        "/copy/details" => array(
            'GET' => 'getCopyBasicInfo'
        )
        
        
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建副本备份任务路由控制
     */
    protected function createCopyBackup(){
        //定义方法版本
        $version = array(
            "v1" => "createCopyBackupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改副本备份任务路由控制
     */
    protected function editCopyBackup(){
        //定义方法版本
        $version = array(
            "v1" => "editCopyBackupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取副本任务所有信息(修改提供)
     */
    protected function getCopyTaskAllInfo(){
    	//定义方法版本
    	$version = array(
    		'v1' => "getCopyTaskAllInfoV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 创建副本回传任务路由控制
     */
    protected function createCopyBack(){
        //定义方法版本
        $version = array(
            "v1" => "createCopyBackV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取做了副本任务的虚拟机列表
     */
    protected function getCopyVmList(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getCopyVmListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取对应虚拟机副本数据
     */
    protected function getVmCopyData(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getVmCopyDataV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取副本任务运行详情
     */
    protected function getCopyBasicInfo(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getCopyBasicInfoV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**********createCopyBackup**********/
    private function createCopyBackupV1(){
    	$jobName = $this->params['task_name'];
    	$jobHandler = Xphp::instance('APIJobsHandler');
    	 //检查任务名是否重复
        $jobHandler->checkJobNameExist($jobName);
        $vmInfo = $this->groupCopyVms($this->params['vm_info'], array(), false);
        
    	$msg = array(
    		"task_name" => $jobName,
    		"module_type" => Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],
    		"time_strategy_list" => $this->groupCopyTimeList($this->params['time_strategy_list'], array(), false),
    	
    		"reserved_strategy" =>  $this->groupCopyReserved($this->params['reserved_strategy'], array(), false),
    		"transport_strategy" => $this->groupCopyTransport($this->params['transport_strategy'], array(), false),
    		"storage_strategy" => array(
    			"encrypt_flag" => null,
    			"compress_flag" => null,
    			"deduplication_flag" => null,
    			"block_size" => null
    		),
    		
    		"auto_find_sr_flag" => 2,
    		"storage_uuid" => $this->params['storage_info']['target_storage_uuid'],
    		"node_uuid" => $vmInfo['nodeuuid'],
    		"target_repository_uuid" => $this->params['storage_info']['target_storage_uuid'],
    		"target_storage_type" => $this->params['storage_info']['target_storage_type'],

    		"backup_copy_vms" => $vmInfo['vms'],
    		"task_type" => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],
    		"real_storage_info" => $this->groupRealStorageInfo($this->params['storage_info'])
    	);
    	$opName = 'BD_TASK_OP_BACKUP_COPY_CREATE';
    	$hypervisor = 0; //虚拟化类型
    	$mbResult = $this->mbCopyMsg($vmInfo['nodeuuid'], $hypervisor, $opName, json_encode($msg));
    	
    	$data = array();
    	if($mbResult['result']){
    		//如果成功,返回新建资源uuid
    		$data['job_uuid'] = $jobHandler->getJobUUIDWithJobName($jobName);
    	}
    	
    	return $this->apiResponse($mbResult['result'], 'API_CODE_COPY_CREATE_COPY_JOB',$data, $mbResult);
    }
    
    /**********editCopyBackup**********/
    private function editCopyBackupV1(){
    	$jobName = $this->params['task_name'];
    	$jobUUID = $this->params['task_uuid'];
    	$oldInfo = $this->getCopyBackupInfo($jobUUID);  //获取以前的配置信息
    	$jobHandler = Xphp::instance('APIJobsHandler');
    	$this->apiParamsCheck($jobUUID, $jobName);
    	$vmInfo = $this->groupCopyVms($this->params['vm_info'], $oldInfo, true);
    	$storageInfo = $this->params['storage_info'];
    	if(empty($storageInfo)){
    		$storageInfo = $oldInfo['storage_info'];
    	}
    	
    	$msg = array(
    		"task_uuid" => $jobUUID,
    		"task_name" => $jobName,
    		"module_type" => Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],
    		"time_strategy_list" => $this->groupCopyTimeList($this->params['time_strategy_list'], $oldInfo, true),
    			 
    		"reserved_strategy" =>  $this->groupCopyReserved($this->params['reserved_strategy'], $oldInfo, true),
    		"transport_strategy" => $this->groupCopyTransport($this->params['transport_strategy'], $oldInfo, true),
    		"storage_strategy" => array(
    			"encrypt_flag" => null,
    			"compress_flag" => null,
    			"deduplication_flag" => null,
    			"block_size" => null
    		),
    	
    		"auto_find_sr_flag" => 2,
    		"storage_uuid" => $storageInfo['target_storage_uuid'],
    		"node_uuid" => $vmInfo['nodeuuid'],
    		"target_repository_uuid" => $storageInfo['target_storage_uuid'],
    		"target_storage_type" => $storageInfo['target_storage_type'],
    	
    		"backup_copy_vms" => $vmInfo['vms'],
    		"task_type" => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],
    		"real_storage_info" => $this->groupRealStorageInfo($storageInfo)
    	);
    	$opName = 'BD_TASK_OP_BACKUP_COPY_MODIFY';
    	$hypervisor = 0; //虚拟化类型
    	$mbResult = $this->mbCopyMsg($vmInfo['nodeuuid'], $hypervisor, $opName, json_encode($msg));
    	 
    	$data = array();
    	if($mbResult['result']){
    		//如果成功,返回新建资源uuid
    		$data['job_uuid'] = $jobHandler->getJobUUIDWithJobName($jobName);
    	}
    	 
    	return $this->apiResponse($mbResult['result'], 'API_CODE_COPY_EDIT_COPY_JOB',$data, $mbResult);
    }
    
    /**********getCopyTaskAllInfo**********/
    private function getCopyTaskAllInfoV1(){
    	$jobUUID = $this->params['job_uuid'];
    	$this->apiParamsCheck($jobUUID);
    	$jobInfo = $this->getCopyBackupInfo($jobUUID);
    	return $this->apiResponse(true, 'API_CODE_COPY_GET_ALL_TASK_INFO', $jobInfo);
    	
    }
    
    /**********createCopyBack**********/
    private function createCopyBackV1(){
    	$jobName = $this->params['task_name'];
    	$jobHandler = Xphp::instance('APIJobsHandler');
    	//检查任务名是否重复
    	$jobHandler->checkJobNameExist($jobName);
    	$vmInfo = $this->groupCopyVms($this->params['vm_info'], array(), false);
    	$nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
    	
    	$msg = array(
    		"task_name" => $jobName,
            'module_type' => Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],
            'task_type' => Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'],
            'node_uuid' => Xphp::instance('APIStoragesHandler', 'getStorageInNode', $this->params['storage_info']['target_storage_uuid']),	//获取存储所在节点uuid
            'target_repository_uuid' => $this->params['storage_info']['target_storage_uuid'], //目标存储uuid
            'target_storage_type' => $this->params['storage_info']['target_storage_type'],   //目标存储类型
            'backup_copy_vms' => $vmInfo['vms'],
            'transport_strategy' => $this->groupCopyTransport($this->params['transport_strategy'], array(), false),
    	);
    	$opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_CREATE';
    	$hypervisor = 0; //虚拟化类型
    	$mbResult = $this->mbCopyMsg($nodeuuid, $hypervisor, $opName, json_encode($msg));
    	 
    	$data = array();
    	if($mbResult['result']){
    		//如果成功,返回新建资源uuid
    		$data['job_uuid'] = $jobHandler->getJobUUIDWithJobName($jobName);
    		return $jobHandler->startBackukpJobUnify('API_CODE_COPY_CREATE_COPY_BACK_JOB',
    			Xphp::$_config['BACKUP_MODE']['FULL'], $data['job_uuid']);
    	}
    	 
    	return $this->apiResponse($mbResult['result'], 'API_CODE_COPY_CREATE_COPY_BACK_JOB',$data, $mbResult);
    }
    
    /**********getCopyVmList**********/
    private function getCopyVmListV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	
    	$sql = "select bbt.timepoint_uuid, bbt.task_uuid, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, vbt.version, vbt.dir_path, vbt.hypervisor_type
                from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid and vbt.timepoint_uuid and bbt.copy_flag = ? group by vbt.dir_path limit ? , ?";
    	$sqlCount = "select count(distinct vbt.vm_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.copy_flag = ?";
    	
    	$sqlParams = array(Xphp::$_config['FLAG']['SET'], $begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['FLAG']['SET']));
    	$records = array();
    	$vm = array();
    	foreach ($data as $d){
    		if(!in_array($d['vcenter_uuid'] . $d['vm_uuid'], $vm)){
    			$vm[] = $d['vcenter_uuid'] . $d['vm_uuid'];
    			$records[] = array(
    					'vcenter_uuid' => $d['vcenter_uuid'],
    					'vm_uuid' => $d['vm_uuid'],
    					'vm_name' => $d['vm_name'],
    					'version' => $d['version'],
    					'dir_path' => $d['dir_path'],
    					'hypervisor' => intval($d['hypervisor_type']),
    					'task_uuid' => $d['task_uuid'],
    			);
    		}
    	}
    	
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	
    	return $this->apiResponse(true, "API_CODE_COPY_GET_VM_LIST", $data);
    }
    
    /**********getVmCopyData**********/
    private function getVmCopyDataV1(){
    	$vmuuid = $this->params['vm_uuid'];
    	$vcenteruuid =$this->params['vcenter_uuid'];
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($vmuuid, $vcenteruuid, $count);
    	$sqlParams = array($vcenteruuid, $vmuuid, Xphp::$_config['FLAG']['SET'], $begin, $count);
    	$sql = "select vbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) 
    		task_create_time, bbt.task_uuid, bbt.total_size, bbt.write_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? and bbt.copy_flag = ? order by bbt.timepoint limit ?, ?";
    	$data = $this->dbSelect($sql, $sqlParams);
    	$sqlCount = "select count(bbt.timepoint_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? and bbt.copy_flag = ?";
    	$dataCount = $this->dbSelect($sqlCount, array($vcenteruuid, $vmuuid, Xphp::$_config['FLAG']['SET']));
    	
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    			"timepoint_uuid" => $d['timepoint_uuid'],
    			"timepoint_time" => $d['timepoint'],
    			"backup_mode" => intval($d['backup_mode']),
    			"depend_point_uuid" => $d['depend_point_uuid'],
    			"total_size" => intval($d['total_size']),
    			"real_size" => intval($d['write_size']),
    			"job_uuid" => $d['task_uuid'],
    			"job_name" => $d['task_name'],
    			"job_create_time" => $d['task_create_time'],
    			"vcenter_uuid" => $vcenteruuid,
    			"vm_uuid" => $vmuuid
    		);
    	}
    	
    	
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	
    	return $this->apiResponse(true, 'API_CODE_COPY_GET_COPY_DATA', $data);
    	
    }
    
    /**********getCopyBasicInfo**********/
    private function getCopyBasicInfoV1(){
    	$taskUUID = $this->params['job_uuid'];
    	$this->apiParamsCheck($taskUUID);
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
    	$jobHandler = Xphp::instance('APIJobsHandler');
    	$utils = Xphp::instance('Utils');
    	if(!empty($data)){
    		foreach ($data as $d){
    			$progress = number_format(intval($d['current_total_size'])*100/intval($d['total_size']), 2).'%';
    			$basicInfo = array(
    				'task_name' => $d['task_name'],
    				'module_type' => intval($d['module_type']),
    				'task_type' => intval($d['task_type']),
    				'user_name' => $d['user_name'],
    				'task_status' => intval($d['task_status']),
    				'total_size' => intval($d['total_size']),
    				'current_size' => intval($d['current_total_size']),
    				'speed' => intval($d['speed']),
    				'progress' => $progress,
    				'create_time' => $this->parseDate($d['create_time']),
    				'start_time' => (string)$d['start_time'],
    				'interval' => (string)$jobHandler->getTimeInterval($d['start_time'], $d['task_status']),
    				'end_time' => (string)strtotime($jobHandler->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed'])),
    				'next_time' => (string)strtotime($jobHandler->getNextStartTime($d['next_start_time'])),
    				'time_strategy' => $this->getCopyTimeStrategy(intval($d['strategy_id'])),
    				'reserved_strategy' => $jobHandler->getReservedStrategy(intval($d['strategy_id'])),
    				'transport_strategy' => $this->getCopyTransportStrategy(intval($d['strategy_id'])),
    				'storage_info' => $this->getCopyStorageInfo($taskUUID),
    			);
    		}
    	}
    	return $this->apiResponse(true, 'API_CODE_COPY_GET_BASIC_DETAILS', $basicInfo);
    }
    
    /**********************************其他工具方法************************************/
    
    /**
     * 组合需要传递的虚拟机信息
     * @param array $vmInfo
     * @param array $oldInfo
     * @param boolean $editFlag
     */
    private function groupCopyVms($vmInfo, $oldInfo, $editFlag){
    	
    	$vms = array();
    	$info = array();
    	$nodeuuid = '';
    	if(empty($vmInfo) && $editFlag){
    		$vmInfo = $oldInfo['vm_info'];
    	}
    	foreach ($vmInfo as $vm){
    		$vmNodeuuid = $this->getSourceNodeuuid($vm['task_uuid']);
    		if(empty($nodeuuid)){
    			$nodeuuid = $vmNodeuuid;
    		}else if($nodeuuid != $vmNodeuuid){
    			return $this->apiResponse(false, 'API_CODE_COPY_SOURCE_NODE_UUID_NOT_SAME');
    		}
    		$vms[] = array(
    				'hypervisor_type' => $vm['hypervisor'],
    				'vm_uuid' => $vm['vm_uuid'],
    				'vcenter_uuid' => $vm['vcenter_uuid'],
    				'source_task_uuid' => $vm['task_uuid'],
    				'specified_timepoint_uuid' => $vm['timepointuuids'],
    		);
    	}
    	
    	$info = array(
    		'vms' => $vms,
    		'nodeuuid' => $nodeuuid
    	);
    	return $info;
    }
    
    /**
     * 组合需要传递的存储信息
     * @param array $storageInfo
     */
    private function groupRealStorageInfo($realstorageInfo){
		$storageInfo = array(
			'real_storage_uuid' => $realstorageInfo['real_storage_uuid'],
			'real_storage_name' => $realstorageInfo['real_storage_name'],
			'real_storage_type' => $realstorageInfo['real_storage_type'],
			'real_storage_total_size' => $realstorageInfo['total_size'],
			'real_storage_free_size' => $realstorageInfo['free_size']
		);
    	return $storageInfo;
    }
    
    /**
     * 组合副本传输策略
     * @param array $params
     * @param array $oldInfo
     * @param boolean $editFlag
     * @return array
     */
    private function groupCopyTransport($params, $oldInfo, $editFlag){
    	if(empty($params)){
    		if(!$editFlag){
    			$msg = array(
    					'encrypt_flag' => 1,
    					'compress_flag' => 1,
    					'speed_limit_flag' => 2,
    					'max_speed' => 0
    			);
    		}else{
    			$strategy = $oldInfo['transport_strategy'];
    			$msg = array(
    					'encrypt_flag' => $strategy['encrypt_flag'],
    					'compress_flag' => $strategy['compress_flag'],
    					'speed_limit_flag' => 2,
    					'max_speed' => 0
    			);
    		}
    	}else{
    		$msg = array(
    				'encrypt_flag' => $params['encrypt_flag'],
    				'compress_flag' => $params['compress_flag'],
    				'speed_limit_flag' => 2,
    				'max_speed' => 0
    		);
    	}
    	return $msg;
    }
    
    /**
     * 组合副本保留策略
     * @param array $params
     * @param array $oldInfo
     * @param boolean $editFlag
     * @return array
     */
    private function groupCopyReserved($params, $oldInfo, $editFlag){
    	if(empty($params)){
    		if(!$editFlag){
    			$msg = array(
    				'strategy_type' => 1,
    				'number' => 30,
    				'auto_archive_flag' => 2,
    			);
    		}else{
    			$strategy = $oldInfo['reserved_strategy'];
    			$msg = array(
    				'strategy_type' => $strategy['strategy_type'],
    				'number' => $strategy['number'],
    				'auto_archive_flag' => 2,
    			);
    		}
    	}else{
    		$msg = array(
    			'strategy_type' => $params['reserved_type'],
    			'number' => $params['number'],
    			'auto_archive_flag' => Xphp::$_config['FLAG']['UNSET']  //自动归档
    		);
    	}
    	return $msg;
    }
    
    /**
     * 组合副本时间策略
     * @param array $params
     * @param array $oldInfo
     * @param booleanl $editFlag
     * @return array
     */
    private function groupCopyTimeList($params, $oldInfo, $editFlag){
    	$jobHandler = Xphp::instance('APIJobsHandler');
    	$msg = array();
    	if(empty($params)){
    		if(!$editFlag){
    			$msg[] = array(
    					'mode' => Xphp::$_config['BACKUP_MODE']['COPY'],
    					'strategy_type' => Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'],
    					'days' => '0000100',
    					'start_time' => '23:00:00',
    					'roll_flag' => 2,
    					'roll_interval' => 0,
    					'roll_end_time' => '',
    					'global_id' => 0,
    			);
    		}else{
    			$timestrategy = $oldInfo['time_strategy_list'][0];
    			$msg[] = array(
    					'mode' => $timestrategy['mode'],
    					'strategy_type' => $timestrategy['strategy_type'],
    					'days' => $timestrategy['days'],
    					'start_time' => $timestrategy['start_time'],
    					'roll_flag' => $timestrategy['roll_flag'],
    					'roll_interval' => $timestrategy['roll_interval'],
    					'roll_end_time' => $timestrategy['roll_end_time'],
    					'global_id' => 0,
    			);
    		}
    	}else if('strategy' == $jobHandler->getTimeStrategyType($params['type'])){
    		$msg[] = $jobHandler->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['COPY'], $params['time_info']);
    	}else{
    		//一次性备份
    		$strategy = array('start_time' => $params['datetime']);
    		$strategy['strategy_type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
    		$msg[] = $jobHandler->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $strategy);
    	}
    
    	return $msg;
    }
    
    /**
     * 获取源对应节点uuid
     * @param string $taskuuid
     */
    private function getSourceNodeuuid($taskuuid){
    	$sql = "select bsr.node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.task_uuid = ? ";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$nodeuuid= '';
    	if(empty($data)){
    		 $sql = "select node_uuid from bd_task where task_uuid = ? ";
    		 $dataTask = $this->dbSelect($sql, array($taskuuid));
    		 $nodeuuid = $dataTask[0]['node_uuid'];
    	}else{
    		$nodeuuid = $data[0]['node_uuid'];
    	}
    	
    	return $nodeuuid;
    }
    
    /**
     * 获取对应副本任务所有配置信息
     * @param string  $jobuuid
     */
    private function getCopyBackupInfo($jobuuid){
    	$sql = "select bt.task_name, bt.strategy_id, bt.storage_uuid,
                       bct.real_storage_uuid, bct.real_storage_type, bct.real_storage_name, bct.real_storage_total_size, bct.real_storage_free_size, bcil.target_storage_type,
                	   bts.encrypt_flag, bts.compress_flag, brs.strategy_type, brs.number
                from bd_task bt, backup_copy_item_list bcil, backup_copy_task bct, bd_transport_strategy bts, bd_reserved_strategy brs
                where bt.task_uuid = bct.task_uuid
    			and bt.task_uuid = bcil.task_uuid
                and bt.strategy_id = bts.strategy_id
    			and bt.strategy_id = brs.strategy_id
                and bt.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($jobuuid));
    	$info = array();
    	if($data){
    		$info = array(
    				//任务UUID
    				'task_uuid' => $jobuuid,
    				//任务名
    				'task_name' => $data[0]['task_name'],
    				//保留策略
    				'reserved_strategy' => array(
    					'strategy_type' => $data[0]['strategy_type'],
    					'number' => $data[0]['number'],
    				),
    				//节点
    				'storage_info' => array(
    					'target_storage_uuid' => $data[0]['storage_uuid'],
    					'target_storage_type' => $data[0]['target_storage_type'],
    					'real_storage_uuid' => $data[0]['real_storage_uuid'],
    					'real_storage_name' => $data[0]['real_storage_name'],
    					'real_storage_type' => $data[0]['real_storage_type'],
    					'total_size' => $data[0]['real_storage_total_size'],
    					'free_size' => $data[0]['real_storage_free_size']
    				),
    				//传输策略
    				'transport_strategy' => array(
    					'encrypt_flag' => intval($data[0]['encrypt_flag']),
    					'compress_flag' => intval($data[0]['compress_flag']),
    				),
    				//虚拟机信息
    				'vm_info' => $this->getCopyEditInfo($jobuuid),
    				//时间策略
    				'time_strategy_list' => $this->getCopyTimeStrategy($data[0]['strategy_id']),
    		);
    	}
    	
    	return $info;
    }
    
    /**
     * 得到副本修改的虚拟机信息
     * @param unknown $params
     * @return string
     */
    private function getCopyEditInfo($taskuuid){
    	$info = array();
    	$sql =  "select item_uuid, vcenter_uuid, item_name, source_task_uuid, specified_timepoint_list, hypervisor_type from backup_copy_item_list where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	foreach ($data as $d){
    		$timepointuuids = array();
    		if(!empty($d['specified_timepoint_list'])){
    			$timepointuuids = json_decode($d['specified_timepoint_list']);
    		}
    		$info[] = array(
    				"vm_uuid" => $d['item_uuid'],
    				"vcenter_uuid" => $d['vcenter_uuid'],
    				"vm_name" => $d['item_name'],
    				"task_uuid" => $d['source_task_uuid'],
    				"timepointuuids" => $timepointuuids,
    				"hypervisor" => $d['hypervisor_type'],
    		);
    	}
    
    	return $info;
    }
    

    /**
     * 得到时间策略信息
     * @param int $strategyID
     * @return array
     */
    private function getCopyTimeStrategy($strategyID){
    	$this->apiParamsCheck($strategyID);
    	$sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                from bd_time_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql, array($strategyID));
    	$strategy = array();
    	foreach ($data as $d){
    		$strategy[] = array(
    			'mode' => $d['mode'],
    			'strategy_type' => $d['strategy_type'],
    			'days' => $d['days'],
    			'start_time' => $d['start_time'],
    			'roll_flag' => intval($d['roll_flag']),
    			'roll_interval' => intval($d['roll_interval']),
    			'roll_end_time' => $d['roll_end_time'],
    		);
    	}
    	return $strategy;
    }
    
    /**
     * 获取副本任务传输策略参数
     * @param int $strategyID
     */
    private function getCopyTransportStrategy($strategyID){
    	$sql = "select encrypt_flag, compress_flag from bd_transport_strategy where strategy_id = ? ";
    	$data = $this->dbSelect($sql, array($strategyID));
    	$info = array(
    		'encrypt_flag' => $data[0]['encrypt_flag'],
    		'compress_flag' => $data[0]['compress_flag']
    	);
    	return $info;
    }
    
    /**
     * 获取副本任务配置存储信息
     * @param string $taskuuid
     */
    private function getCopyStorageInfo($taskuuid){
    	$sql = "select bcil.target_storage_uuid, bcil.target_storage_type, bct.real_storage_uuid, bct.real_storage_name, bct.real_storage_type, 
    			bct.real_storage_total_size, bct.real_storage_free_size from backup_copy_item_list bcil, backup_copy_task bct where bcil.task_uuid = bct.task_uuid and bcil.task_uuid = ? ";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$info = array(
    		'target_storage_uuid' => $data[0]['target_storage_uuid'],
    		'target_storage_type' => $data[0]['target_storage_type'],
    		'real_storage_uuid' => $data[0]['real_storage_uuid'],
    		'real_storage_name' => $data[0]['real_storage_name'],
    		'real_storage_type' => $data[0]['real_storage_type'],
    		'total_size' => $data[0]['real_storage_total_size'],
    		'free_size' => $data[0]['real_storage_free_size']
    	);
    	return $info;
    }
    
}