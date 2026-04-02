<?php
/******************************************* 
** 平台统一数据处理 
** 
** @author       luokai@vinchin.com 
** @date         2019-4-19 上午10:40:40 
** @version      1.0.0 
** @copyright    Copyright 2019 vinchin.com 
********************************************/
class VisualHandler extends OPHandler{
   	
   	/**
   	 *获取长时间获取一次的平台数据 
   	 */
	
	public function getDataSurvery($params){
		$survey = array(
				"vcenter_data" => $this->getVcenterData(),  //虚拟机使用情况统计
				"fs_data" => $this->getFsData(),            //文件代理使用情况统计
				"db_data" => $this->getDbData(),             //数据库代理使用情况统计
				"storage_data" => $this->getStorageData(),  //存储统计
				"latest_jobs" => $this->getLatestJobs(),	//最近完成任务
				"latest_vms" => $this->getLatestVms(),		//最近备份虚拟机	
				"daily_vms" => $this->getDailyVms(),		//每日备份虚拟机
				"daily_storages" => $this->getDailyStorages(),	//每日存储用量
				"today_jobs" => $this->getTodayJobs(),
				
		);
		return json_encode($survey);
	}
	
	
	/**
	 *获取实时数据监控
	 */
	public function getRealtimeData($params){
		$sql = "select bt.task_uuid, bt.module_type from bd_task bt, bd_running_info bri where bt.task_uuid = bri.task_uuid and bt.task_status = ? and bt.module_type in (2,3,4) and bt.task_type in (1, 2, 8,28,29) order by bri.start_time asc limit 0, 4";
		$data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['RUNNING']));
		$info = array();
		if(!empty($data)){
			$taskList = array();
			foreach ($data as $d){
				$taskList[] = $d['task_uuid'];
				$moduleType = intval($d['module_type']);
				switch ($moduleType){
				    case Xphp::$_config['MODULE_TYPE']['VM']:
				        $info[] = $this->getTaskRunInfo($d['task_uuid']);
				        break;
				    case Xphp::$_config['MODULE_TYPE']['FS']:
				        $info[] = $this->getFsTaskRunInfo($d['task_uuid']);
				        break;
				    case Xphp::$_config['MODULE_TYPE']['DB']:
				        $info[] = $this->getDbTaskRunInfo($d['task_uuid']);
				        break;
				}
			}
		}
		$platformHandler = Xphp::instance('PlatformHandler');
		$jobInfo = $platformHandler->_array_column($info,'start_time');
		array_multisort($jobInfo ,SORT_ASC,$info);
		if(!empty($taskList)){
			$info[0]['task_list'] = $taskList;
		}
		
		return json_encode($info);
	}
	
	/**
	 * 获取运行数据库任务详细信息
	 * @param string $taskuuid
	 */
	private function getDbTaskRunInfo($taskuuid){
	    $sql = "select dl.db_name, dl.total_size, dl.transport_size, dl.write_size, dl.task_status, dl.dir_path, dl.new_db_name,
                       dl.db_uuid, bri.speed, bri.speed_time, bri.current_object_write_size,bri.total_object_write_size, unix_timestamp(bri.start_time) start_time,
                         bt.task_status as bd_task_status, bt.storage_uuid, bt.task_uuid,bt.module_type, bt.task_type,bt.task_name
                from db_list dl
                left join bd_running_info bri
                on dl.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = dl.task_uuid where bt.task_uuid = ?
                group by dl.instance_name, dl.db_name order by dl.database_id asc";
	    $data = $this->dbSelect($sql, array($taskuuid));
	    $info = array();
	    $dbNumInfo = $this->getDbTaskRunNum($taskuuid);
	    $ptDes = include APP_PATH . 'platform/PFDescription.php';
	    if(!empty($data)){
	        foreach ($data as $d){
	            if($d['task_status'] != Xphp::$_config['VmTaskStatus']['RUNNING']) continue;
	            $storageSize = $this->getStorageByuuid($d['storage_uuid']);
	            $freeSize = intval($d['total_object_write_size']) - intval($d['current_object_write_size']);
	            $info = array(
	                'status' => $d['task_status'],
	                'task_name' => $d['task_name'],
	                'task_type' => intval($d['task_type']),
	                'module_type' => intval($d['module_type']),
	                'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
	                'vm_name' => $d['db_name'],
	                'speedDes' => $this->getVmSpeed($d['speed'],$d['speed_time']),
	                'speed' => intval($d['speed']),
	                'progress' => $this->calPercent($d['total_object_write_size'], $d['current_object_write_size']),
	                'realSize' => intval($d['current_object_write_size']),
	                'realSize_des' => $this->calSize(intval($d['current_object_write_size'])),
	                'vmSize' => intval($d['total_size']),
	                'vmSize_des' => $this->calSize(intval($d['total_object_write_size'])),
	                'freeSize' => $freeSize,
	                'freeSize_des' => $this->calSize($freeSize),
	                'time' => date("H:i:s"),
	                'timestamp' => time(),
	                'task_list' => array(),
	                'task_uuid' => $taskuuid,
	                'start_time' => $d['start_time'],
	                'vm_num' => $dbNumInfo['db_num'],
	                'finish_num' => $dbNumInfo['finish_num'],
	            );
	            
	        }
	        
	        //虚拟机都还在等待状态时，获取虚拟机初始信息
	        if(empty($info)){
	            $vmSize =  1000;
	            $realSize = 1;
	            $freeSize = $vmSize - $realSize;
	            $info =  array(
	                'status' => $data[0]['task_status'],
	                'task_name' => $data[0]['task_name'],
	                'task_type' => intval($data[0]['task_type']),
	                'module_type' => intval($d['module_type']),
	                'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
	                'vm_name' => $data[0]['db_name'],
	                'speedDes' => $this->getVmSpeed($data[0]['speed'],$data[0]['speed_time']),
	                'speed' => intval($data[0]['speed']),
	                'progress' => $this->calPercent($data[0]['total_object_write_size'], $data[0]['current_object_write_size']),
	                'realSize' => $realSize,
	                'realSize_des' => $this->calSize(0),
	                'vmSize' => $vmSize,
	                'vmSize_des' => $this->calSize(0),
	                'freeSize' => $freeSize,
	                'freeSize_des' => $this->calSize(0),
	                'time' => date("H:i:s"),
	                'timestamp' => time(),
	                'task_list' => array(),
	                'task_uuid' => $taskuuid,
	                'start_time' => $data[0]['start_time'],
	                'waitFlag' => true,
	                'vm_num' => $dbNumInfo['db_num'],
	                'finish_num' => $dbNumInfo['finish_num']
	            );
	        }
	    }
	    return $info;
	}
	
	/**
	 * 获取运行文件任务详细信息
	 * @param string $taskuuid
	 */
	private function getFsTaskRunInfo($taskuuid){
	    $sql = "select fri.total_fs_count, fri.current_fs_count, ba.agent_name, ba.hostname,ba.ip, bri.speed, bri.speed_time, bri.total_object_size, 
                        bri.current_object_completed_size, bri.total_object_completed_size, unix_timestamp(bri.start_time) start_time,
                         bt.task_status as bd_task_status, bt.storage_uuid, bt.task_uuid, bt.task_type,bt.module_type,bt.task_name
                from fs_running_info fri, bd_task bt
                left join bd_running_info bri
                on bt.task_uuid = bri.task_uuid
                left join bd_agent ba 
                on bt.agent_uuid = ba.agent_uuid
                where bt.task_uuid = ?
               order by bt.task_uuid asc";
	    $data = $this->dbSelect($sql, array($taskuuid));
	    $info = array();
	    $ptDes = include APP_PATH . 'platform/PFDescription.php';
	    if(!empty($data)){
	        foreach ($data as $d){
	            if($d['task_status'] != Xphp::$_config['VmTaskStatus']['RUNNING']) continue;
	            $storageSize = $this->getStorageByuuid($d['storage_uuid']);
	            $freeSize = intval($d['total_object_size']) - intval($d['total_object_completed_size']);
	            $info = array(
	                'status' => $d['task_status'],
	                'task_name' => $d['task_name'],
	                'task_type' => intval($d['task_type']),
	                'module_type' => intval($d['module_type']),
	                'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
	                'vm_name' => $data[0]['agent_name'] == $d['ip'] ? $d['ip']."(". $d['hostname'] .")" : $d['ip']."(". $d['agent_name'] .")",
	                'speedDes' => $this->getVmSpeed($d['speed'],$d['speed_time']),
	                'speed' => intval($d['speed']),
	                'progress' => $this->calPercent($d['total_object_size'], $d['total_object_completed_size']),
	                'realSize' => intval($d['current_object_completed_size']),
	                'realSize_des' => $this->calSize(intval($d['total_object_completed_size'])),
	                'vmSize' => intval($d['total_object_size']),
	                'vmSize_des' => $this->calSize(intval($d['total_object_size'])),
	                'freeSize' => $freeSize,
	                'freeSize_des' => $this->calSize($freeSize),
	                'time' => date("H:i:s"),
	                'timestamp' => time(),
	                'task_list' => array(),
	                'task_uuid' => $taskuuid,
	                'start_time' => $d['start_time'],
	                'vm_num' => intval($d['total_fs_count']),
	                'finish_num' => intval($d['current_fs_count'])
	            );
	            
	        }
	        
	        //虚拟机都还在等待状态时，获取虚拟机初始信息
	        if(empty($info)){
	            $vmSize =  1000;
	            $realSize = 1;
	            $freeSize = $vmSize - $realSize;
	            $info =  array(
	                'status' => $data[0]['task_status'],
	                'task_name' => $data[0]['task_name'],
	                'task_type' => intval($data[0]['task_type']),
	                'module_type' => intval($d['module_type']),
	                'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
	                'vm_name' => $data[0]['agent_name'] == $d['ip'] ? $d['ip']."(". $d['hostname'] .")" : $d['ip']."(". $d['agent_name'] .")",
	                'speedDes' => $this->getVmSpeed($data[0]['speed'],$data[0]['speed_time']),
	                'speed' => intval($data[0]['speed']),
	                'progress' => $this->calPercent($data[0]['total_object_size'], $data[0]['total_object_completed_size']),
	                'realSize' => $realSize,
	                'realSize_des' => $this->calSize(0),
	                'vmSize' => $vmSize,
	                'vmSize_des' => $this->calSize(0),
	                'freeSize' => $freeSize,
	                'freeSize_des' => $this->calSize(0),
	                'time' => date("H:i:s"),
	                'timestamp' => time(),
	                'task_list' => array(),
	                'task_uuid' => $taskuuid,
	                'start_time' => $data[0]['start_time'],
	                'waitFlag' => true,
	                'vm_num' => intval($d['total_fs_count']),
	                'finish_num' => intval($d['current_fs_count'])
	            );
	        }
	    }
	    return $info;
	}
	
	/**
	 * 获取运行虚拟机任务详细信息
	 * @param string $taskuuid
	 */
	private function getTaskRunInfo($taskuuid){
		$sql = "select vml.vm_name, vml.vm_size, vml.completed_size, vml.write_size, vml.task_status, vml.dir_path, vml.new_name,
                       vml.vm_uuid, bri.speed, bri.speed_time, bri.current_object_completed_size, unix_timestamp(bri.start_time) start_time,
                         bt.module_type, bt.task_status as bd_task_status, bt.storage_uuid, bt.task_uuid, bt.task_type,bt.task_name
                from vm_machine_list vml
                left join bd_running_info bri
                on vml.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = vml.task_uuid where bt.task_uuid = ?
                group by vml.vm_uuid order by vml.machine_id asc";
		$data = $this->dbSelect($sql, array($taskuuid));
		$info = array();
		$vmNumInfo = $this->getFinshVm($taskuuid);
		$ptDes = include APP_PATH . 'platform/PFDescription.php';
		if(!empty($data)){
			foreach ($data as $d){
				if($d['task_status'] != Xphp::$_config['VmTaskStatus']['RUNNING']) continue;
				$storageSize = $this->getStorageByuuid($d['storage_uuid']);
				$freeSize = intval($d['vm_size']) - intval($d['current_object_completed_size']);
				$info = array(
						'status' => $d['task_status'],
						'task_name' => $d['task_name'],
						'task_type' => intval($d['task_type']),
				        'module_type' => intval($d['module_type']),
						'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
						'vm_name' => $d['vm_name'],
						'speedDes' => $this->getVmSpeed($d['speed'],$d['speed_time']),
						'speed' => intval($d['speed']),
						'progress' => $this->calPercent($d['vm_size'], $d['current_object_completed_size']),
						'realSize' => intval($d['current_object_completed_size']),
						'realSize_des' => $this->calSize(intval($d['current_object_completed_size'])),
						'vmSize' => intval($d['vm_size']),
						'vmSize_des' => $this->calSize(intval($d['vm_size'])),
						'freeSize' => $freeSize,
						'freeSize_des' => $this->calSize($freeSize),
						'time' => date("H:i:s"),
						'timestamp' => time(),
						'vm_num' => $vmNumInfo['vm_num'],
						'finish_num' => $vmNumInfo['finish_num'],
						'task_list' => array(),
						'task_uuid' => $taskuuid,
						'start_time' => $d['start_time']
				);
					
			}
			
			//虚拟机都还在等待状态时，获取虚拟机初始信息
			if(empty($info)){
				$vmSize =  1000;
				$realSize = 1;
				$freeSize = $vmSize - $realSize;
				$info =  array(
						'status' => $data[0]['task_status'],
						'task_name' => $data[0]['task_name'],
						'task_type' => intval($data[0]['task_type']),
				        'module_type' => intval($d['module_type']),
						'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
						'vm_name' => $data[0]['vm_name'],
						'speedDes' => $this->getVmSpeed($data[0]['speed'],$data[0]['speed_time']),
						'speed' => intval($data[0]['speed']),
						'progress' => $this->calPercent($data[0]['vm_size'], $data[0]['current_object_completed_size']),
						'realSize' => $realSize,
						'realSize_des' => $this->calSize(0),
						'vmSize' => $vmSize,
						'vmSize_des' => $this->calSize(0),
						'freeSize' => $freeSize,
						'freeSize_des' => $this->calSize(0),
						'time' => date("H:i:s"),
						'timestamp' => time(),
						'vm_num' => $vmNumInfo['vm_num'],
						'finish_num' => $vmNumInfo['finish_num'],
						'task_list' => array(),
						'task_uuid' => $taskuuid,
						'start_time' => $data[0]['start_time'],
						'waitFlag' => true
				);
			}
		}
		return $info;
	}
	
	/**
	 * 获取任务完成虚拟机个数
	 * @param string $taskuuid
	 */
	private function getFinshVm($taskuuid){
		$sql = "select count(machine_id) as vm_num from vm_machine_list where task_uuid = ? ";
		$dataAll = $this->dbSelect($sql, array($taskuuid));
		$vmNum = intval($dataAll[0]['vm_num']);
		$sql = "select count(machine_id) as finish_num from vm_machine_list where task_uuid = ? and task_status = ?";
		$dataFinish = $this->dbSelect($sql, array($taskuuid,Xphp::$_config['VmTaskStatus']['FINISH']));
		$finishNum = intval($dataFinish[0]['finish_num']);
		$info = array(
			'vm_num' => $vmNum,
			'finish_num' => $finishNum	
		);
		return $info;
	}
	
	/**
	 * 获取任务完成数据库个数
	 * @param string $taskuuid
	 */
	private function getDbTaskRunNum($taskuuid){
	    $sql = "select count(database_id) as db_num from db_list where task_uuid = ? ";
	    $dataAll = $this->dbSelect($sql, array($taskuuid));
	    $dbNum = intval($dataAll[0]['db_num']);
	    $sql = "select count(database_id) as finish_num from db_list where task_uuid = ? and task_status = ?";
	    $dataFinish = $this->dbSelect($sql, array($taskuuid,Xphp::$_config['VmTaskStatus']['FINISH']));
	    $finishNum = intval($dataFinish[0]['finish_num']);
	    $info = array(
	        'db_num' => $dbNum,
	        'finish_num' => $finishNum
	    );
	    return $info;
	}
	
	/**
	 * 根据存储uuid获取存储总大小
	 * @param string $storageuuid
	 */	
	private function getStorageByuuid($storageuuid){
		$sql = "select total_size from bd_storage_resource where storage_uuid =?";
		$data = $this->dbSelect($sql, array($storageuuid));
		return intval($data[0]['total_size']);
	}
	
	/**
	 * 获取任务虚拟机运行速度
	 * @param intval $speed
	 * @param intval $speedtime
	 */
	private function getVmSpeed($speed,$speedtime){
		$speed = $this->calSpeed($speed);
		return $speed;
	}
	
	/**
	 * 获取虚拟化中心统计 
	 */
	private function getVcenterData(){
		$info = array();
		//获取宿主机个数
		$sqlHost ="select count(distinct vh.host_uuid) as host_count from vm_host vh, vm_vcenter vv where vh.vcenter_uuid = vv.vcenter_uuid ";
    	$dataHost = $this->dbSelect($sqlHost);
    	
    	//获取受保护虚拟机个数
    	$sqlProtect = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv, bd_task bt, vm_machine vm 
                        where vml.vcenter_uuid = vm.vcenter_uuid and vml.vm_uuid = vm.vm_uuid and bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? ";
    	$dataProtect = $this->dbSelect($sqlProtect, array(Xphp::$_config['TASKTYPE']['BACKUP']));
    	
    	
    	//获取备份系统所有虚拟机个数
    	$sqlAll = "select count(distinct vt.uuid, vt.vcenter_uuid) as all_vms from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.display_mode = ? and vt.type = ? ";
    	$sqlAllParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], 
		Xphp::$_config['VM_TREE_TYPE']['VM']);
    	$dataAll = $this->dbSelect($sqlAll, $sqlAllParams);
    	$allNum = intval($dataAll[0]['all_vms']);
    	$protectNum = intval($dataProtect[0]['protect_vms']);
    	if($protectNum > $allNum){
    	    $protectNum = $allNum;
    	}
    	
    	if($allNum > 0){
    	    $percent = round(($protectNum/$allNum)*100, 1);
    	}else{
    	    $percent = 0;
    	}
    	$unprotectNum = $allNum - $protectNum;
    	if($unprotectNum < 0){
    	    $unprotectNum = 0;
    	}
    	$info = array(
    		"host_num" => intval($dataHost[0]['host_count']),
    		"protect_vm_num" => $protectNum,
    	    "unprotect_vm_num" => $unprotectNum,
    		"all_vm_num" => $allNum,
    		'percent' => sprintf("%.1f",$percent).'%'
    	);
    	
    	return $info;
		
	}
	
	
	/**
	 *  获取存储统计
	 */
	private function getStorageData(){
		$sql = "select count(bsr.storage_uuid) as storage_count, sum(bsr.total_size) as total_size, sum(bsr.free_size) as free_size from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ?";
		$sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
		$data = $this->dbSelect($sql,$sqlParams);
		$utils = Xphp::instance('Utils');
		if(!$data){
			$totalSize = 0;
			$freeSize = 0;
		}else{
			$totalSize = intval($data[0]['total_size']);
			$freeSize = intval($data[0]['free_size']);
		}
		
		if($totalSize > 0){
		    $percent = round(($freeSize/$totalSize)*100, 1);
		}else{
		    $percent = 0;
		}
		$systemInfo = $this->getAccumulatedData();
		$info = array(
				"storage_num" => intval($data[0]['storage_count']),
				"total_size" => $utils->calSize($totalSize),
				"use_size" => $totalSize-$freeSize,
				"free_size" => $freeSize,
				"use_size_des" => $utils->calSize($totalSize-$freeSize),
				"free_size_des" => $utils->calSize($freeSize),
				"percent" => $percent,
				"total_protect_size" => $systemInfo['value'],
				"total_protect_des" => $systemInfo['unit']
				
		);
		 
		return $info;
	}
	
	/**
	 * 得到累计备份数据
	 */
	private function getAccumulatedData(){
		$sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data, db_data from bd_user_extension ";
		$sqlParams = array();
		$data = $this->dbSelect($sql, $sqlParams);
		$total = 0;
		foreach ($data as $d){
			$total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] +
			$d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'] + $d['db_data'];
		}
	
		if($total == 0){
			$info = array(
				'value' => 0,
				'unit' => '',
			);
			return $info;
		}
		$utils = Xphp::instance('Utils');
		$info = $utils->calSizeToValueAndUnit($total, true);
		return $info;
	}
	
	/**
	 * 获取最近20条已完成任务
	 */
	private function getLatestJobs(){
		$sql = "select module_type, submodule_type, task_type, task_name,  unix_timestamp(finish_time) finish_time, error_code, total_object_size
                from bd_history_task where module_type in (2, 3, 4) and task_type in (1, 2, 8, 28, 29) order by finish_time desc limit ? , ?";
		$data = $this->dbSelect($sql, array(0, 20));
		$taskInfo = array();
		$jobHandler = Xphp::instance('JobHandler');
		$id = 0;
		foreach ($data as $d){
			$id++;
			$taskInfo[] = array(
					'id' => $id,
					'task_name' => $d['task_name'],
					'task_type' => $d['task_type'],
					'total_size' => $this->calSize(intval($d['total_object_size'])),
					'resultdes' => $jobHandler->getHistoryJobResultDes($d['error_code']),
					'finish_time' => date('m-d H:i:s', $d['finish_time']),
			);
		}
		
		return $taskInfo;
	}
	
	/**
	 * 获取最近20个备份的虚拟机
	 */
	private function getLatestVms(){
		$sql = "select distinct vbt.vm_uuid, vbt.vcenter_uuid, vbt.vm_name from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? 
				order by vbt.hypervisor_type asc, bbt.timepoint desc limit ?, ?";
		$data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['VM'],0, 20));
		//文件
		$sqlFs = "select distinct fbt.agent_uuid from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.module_type = ?
				order by bbt.timepoint desc limit ?, ?";
		$dataFs = $this->dbSelect($sqlFs, array(Xphp::$_config['MODULE_TYPE']['FS'], 0, 20));
		
		//数据库
		$sqlDb = "select distinct dbt.agent_uuid from bd_backup_timepoint bbt, db_backup_timepoint dbt
                where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.module_type = ?
				order by bbt.timepoint desc limit ?, ?";
		$dataDb = $this->dbSelect($sqlDb, array(Xphp::$_config['MODULE_TYPE']['DB'], 0, 20));
		
		$vmInfo = array();
		$info = array();
		$utils = Xphp::instance('Utils');
		$id = 0;
		$reportHandler = Xphp::instance('ReportHandler');
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$vmHandler = Xphp::instance("Vmhandler");
		//虚拟机
		foreach ($data as $d){
		    $vmBackupInfo = $reportHandler->getVmBackupInfo($d, $vmHandler, $tenantHandler, $userHandler);
			$vmInfo[] = array(
					'name' => $vmBackupInfo['vm_name'],
					'count' => $vmBackupInfo['backup_count'],
					'last_time' => $vmBackupInfo['last_backup_time'],
			);
		}
		//文件代理
		foreach ($dataFs as $fs){
		    $agentInfo = $reportHandler->getFsAgentInfo($fs['agent_uuid']);
		    $vmInfo[] = array(
		        'name' => $agentInfo['agent_name'],
		        'count' => $agentInfo['backup_count'],
		        'last_time' => $agentInfo['last_backup_time'],
		    );
		}
		//数据库
		foreach ($dataDb as $db){
		    $agentInfo = $reportHandler->getDbAgentInfo($db['agent_uuid']);
		    $vmInfo[] = array(
		        'name' => $agentInfo['agent_name'],
		        'count' => $agentInfo['backup_count'],
		        'last_time' => $agentInfo['last_backup_time'],
		    );
		}
		
		$vmInfo = $utils->arraySort($vmInfo, 'last_time', 'desc', 0, 20);
		foreach ($vmInfo as $i){
		    $id ++;
		    $i['id'] = $id;
		    $info[] = $i;
		}
		return $info;
	}
	
	/**
	 *获取每天备份虚拟机个数 
	 */
	private function getDailyVms(){
		$days = 6; //一周
		$sql = "select remarks, success_vm_num, unix_timestamp(date) date from bd_storage_monitor where DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= date order by date asc";
		$data = $this->dbSelect($sql,array());
		$info = array();
		$i = 1;
		$num = 0;
		foreach ($data as $d){
		    $remarks = json_decode($d['remarks'], true);
		    $successNum = 0;
		    $vm = 0;
		    $fs = 0;
		    $db = 0;
		    if(!empty($remarks)){
		        $vm = intval($remarks['vm']);
		        $fs = intval($remarks['fs']);
		        $db = intval($remarks['db']);
		        $successNum = $vm + $fs + $db;
		    }
		    $num += $successNum;
			$dateTime = intval($d['date']) - 3600*24;
			$averageNum = $num / $i;
			$info[] = array(
				'date' => date("m-d", $dateTime),
			    'vm' => $vm,
			    'fs' => $fs,
			    'db' => $db,
				'average_num' =>  round($averageNum,1)
			);
			$i++;
			$days--;
		}
		$info[] = $this->getTodayNum($num, $i);
		return $info;
	}
	
	/**
	 * 获取今天备份虚拟机个数
	 * @param int $num
	 * @param unintknown $i
	 */
	private function getTodayNum($num, $i){
		$today = date('Y-m-d');
		$sqlVm = "select count(distinct vbt.dir_path) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint like '%". $today . "%'";
		$dataVm = $this->dbSelect($sqlVm, array());
		
		$sqlFs = "select count(distinct fbt.agent_uuid) as total from fs_backup_timepoint fbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.timepoint like '%". $today . "%'";
		$dataFs = $this->dbSelect($sqlFs, array());
		
		$sqlDb = "select count(distinct dbt.agent_uuid) as total from db_backup_timepoint dbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.timepoint like '%". $today . "%'";
		$dataDb = $this->dbSelect($sqlDb, array());
		$vm = intval($dataVm[0]['total']);
		$fs = intval($dataFs[0]['total']);
		$db = intval($dataDb[0]['total']);
		$num += $vm + $fs + $db;
		$averageNum = $num / $i;
		$info = array(
			'date' => date("m-d"),
		    'vm' => $vm,
		    'fs' => $fs,
		    'db' => $db,
			'average_num' =>  round($averageNum,1)
		);
		return $info;
	}
	
	/**
	 *获取每天备份存储容量
	 */
	private function getDailyStorages(){
		$days = 3; //一周
		$sql = "select distinct unix_timestamp(date) date, storage_size from bd_storage_monitor where DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= date order by date asc";
		$data = $this->dbSelect($sql,array());
		$info = array();
		foreach ($data as $d){
			$dateTime = intval($d['date']) - 3600*24;
			$info[] = array(
				'date' => date("m/d", $dateTime),
				'storage_size_des' => $this->calSize(intval($d['storage_size'])),
				'storage_size' => intval($d['storage_size'])
			);
		}
		$info[] = $this->getTodayStorage();
		return $info;
	}
	
	/**
	 * 获取今天备份的数据量
	 */
	private function getTodayStorage(){
		$today = date('Y-m-d');
		$sql = "select total_object_write_size, task_type from bd_history_task where finish_time like '%". $today . "%'";
		$data = $this->dbSelect($sql, array());
		$totalSize = 0;
		if(!empty($data)){
			foreach ($data as $d){
				$totalSize += intval($d['total_object_write_size']);	
			}
		}
		$info = array(
				'date' => date("m/d"),
				'storage_size_des' => $this->calSize($totalSize),
				'storage_size' => $totalSize
		);
		
		return $info;
	}
	
	/**
	 * 获取网络流量监控
	 */
	public function getNetworkFlows($params){
		$count = $params['count'];
		$flag = $params['flag'];
		$sqluuid= "select distinct node_uuid from bd_network_monitor ";
		$datauuid = $this->dbSelect($sqluuid);
		$nodeuuids = array();
		$allData = array();
		foreach ($datauuid as $uuid){
			$nodeuuids[] = $uuid['node_uuid'];
			$allData[] = $this->getNetworkByUUID($uuid['node_uuid'],$count);
		}
		$netData = array();
		$networkFlows = array();
		for($i=0; $i<$count;$i++){
			$receive_size = 0;
			$transmit_size = 0;
			for ($j=0;$j<count($nodeuuids);$j++){
				$receive_size += $allData[$j][$i]['receive_size'];
				$transmit_size += $allData[$j][$i]['transmit_size'];
			}
			$networkFlows[] = array(
					'receive_size' => $receive_size,
					'transmit_size' => $transmit_size,
					'monitor_time' => $allData[0][$i]['monitor_time'],
			);
		}
		foreach ($networkFlows as $network){
			$netData[] = array(
					'timeDes' => date('H:i:s',$network['monitor_time']),
					'time' => $network['monitor_time'],
					'network' =>  array(
						'receive' => floatval($network['receive_size']),
						'transmit' => floatval($network['transmit_size']),
						'net_time' =>  date('H:i:s', $network['monitor_time'])
					)
			);
		}
		if(!$initFlag){
			$platformHandler = Xphp::instance('PlatformHandler');
			$sortNet = $platformHandler->_array_column($netData,'time');
			array_multisort($sortNet ,SORT_ASC,$netData);
		}
		$data = array(
			"timeInterval" => time(),
			"currentTime" => date("H:i:s"),
			"netData" => $netData
		);
		 
		return json_encode($data);
	}
	
	/**
	 * 
	 * @param string $nodeuuid
	 * @param int $count
	 */
	private function getNetworkByUUID($nodeuuid, $count){
		$sqlTime = "select distinct monitor_time from bd_network_monitor where node_uuid = ? and monitor_time  < now() order by unix_timestamp(monitor_time) desc limit ?";
		$sqlParams = array($nodeuuid,$count);
		$dataTime = $this->dbSelect($sqlTime, $sqlParams);
		$timeArray = array();
		foreach ($dataTime as $d){
			$timeArray[] = $d['monitor_time'];
		}
		$timeStr = implode("','", $timeArray);
		$sqlNetwork = "select unix_timestamp(monitor_time) monitor_time,  sum(receive_size) receive_size, sum(transmit_size) transmit_size from bd_network_monitor where monitor_time  in ('$timeStr') and node_uuid = ? group by monitor_time order by unix_timestamp(monitor_time) desc";
		$dataNetwork = $this->dbSelect($sqlNetwork, array($nodeuuid));
		$info = array();
		foreach ($dataNetwork as $d){
			$info[] = array(
				'receive_size' => floatval($d['receive_size']),
				'transmit_size' => floatval($d['transmit_size']),
				'monitor_time' =>  $d['monitor_time']
			);
		}
		return $info;
	}
	

	/**
	 * 获取cpu和内存使用监控
	 */
	public function getCpuandMemory($params){
		$count = $params['count'];
		$initFlag = $params['initFlag'];
		$sqlParams = array($nodeuuid, $count);
		
		$sqlTotal = "select node_uuid, cpu_total, memory_total from bd_cpu_memory_monitor group by node_uuid";
		$dataTotal = $this->dbSelect($sqlTotal);
		$cpuData = array();
		$memoryData = array();
		$memoryTotal = 0;
		$cpuTotal  = 0;
		
		$nodeuuids = array();
		$allData = array();
		foreach ($dataTotal as $total){
			$cpuTotal += $total['cpu_total'];
			$memoryTotal += $total['memory_total'];
			$nodeuuids[] = $total['node_uuid'];
			$allData[] = $this->getCpuandMemoryByUUID($total['node_uuid'], $count); //获取每个节点对应的cpu和内存统计
		}
		
		$cpuMemory = array();
		for($i=0; $i<$count;$i++){
			$cpu_used = 0;
			$memory_used = 0;
			for ($j=0;$j<count($nodeuuids);$j++){
				$cpu_used += $allData[$j][$i]['cpu_used'];
				$memory_used += $allData[$j][$i]['memory_used'];
			}
			$cpuMemory[] = array(
				'cpu_used' => $cpu_used,
				'memory_used' => $memory_used,
				'monitor_time' => $allData[0][$i]['monitor_time'],
			);
		}
		
		foreach ($cpuMemory as $d){
			$cpuRate = floatval($d['cpu_used']) / floatval($cpuTotal) ;
			$cpuData[] = array(
					'cpuRate' => sprintf("%.2f", $cpuRate * 100),
					'cpu_time' => date('H:i:s',$d['monitor_time']),
					'time' => $d['monitor_time'],
					'cpu_total' => sprintf("%.2f", floatval($cpuTotal))
			);
			$memoryRate = intval($d['memory_used']) / intval($memoryTotal);
			$free = intval($memoryTotal) - intval($d['memory_used']);
			$memoryData[] = array(
					'memoryRate' => sprintf("%.2f", $memoryRate * 100),
					'total' => $this->calSize($memoryTotal),
					'free' => $this->calSize($free),
					'time' => $d['monitor_time'],
					'memory_time' => date('H:i:s',$d['monitor_time'])
					 
			);
		}
		if(!$initFlag){
			$platformHandler = Xphp::instance('PlatformHandler');
			$sortCpu = $platformHandler->_array_column($cpuData,'time');
			array_multisort($sortCpu ,SORT_ASC,$cpuData);
			
			$sortMemory = $platformHandler->_array_column($memoryData,'time');
			array_multisort($sortMemory ,SORT_ASC,$memoryData);
		}
		//显示接收(备份)和发送(恢复)流量
		$charData = array(
				"cpuData" => $cpuData,
				"memData" => $memoryData,
				"timeInterval" => time(),
				"currentTime" => date("H:i:s")
		);
		
		return json_encode($charData);
	}
	
	/**
	 * 获取每个节点的cpu和内存统计记录
	 * @param string $nodeuuid
	 * @param intval $count
	 */
	private function getCpuandMemoryByUUID($nodeuuid,$count){
		$sql = "select unix_timestamp(monitor_time) monitor_time, cpu_used, memory_used
				from bd_cpu_memory_monitor where  monitor_time  < now() and node_uuid = ? order by unix_timestamp(monitor_time) desc limit ?";
		$data = $this->dbSelect($sql, array($nodeuuid,$count));
		$info = array();
		foreach ($data as $d){
			$info[] = array(
				'cpu_used' => floatval($d['cpu_used']),
				'monitor_time' => $d['monitor_time'],
				'memory_used' => intval($d['memory_used'])
			);
		}
		return $info;
		
	}
	
	/**
	 * 获取当日任务完成情况 
	 */
	private function getTodayJobs(){
		$sqlParams = array();
		$sql = "select count(distinct task_uuid) as finish_num from bd_history_task where module_type in (2, 3, 4) and error_code = 0 and task_type in (1, 2, 8, 28, 29) and to_days(finish_time) = to_days(now())  order by finish_time desc";
		$data = $this->dbSelect($sql,$sqlParams);
		$finishNum = intval($data[0]['finish_num']);
		$sqlwait = "select count(bs.strategy_id) as wait_num from bd_task bt, bd_strategy bs where bt.strategy_id = bs.strategy_id and to_days(next_start_time) = to_days(now()) and bt.module_type in (2,3,4) and bt.task_status = ? and bt.task_type in (1, 2, 8, 28, 29) order by bs.next_start_time desc";
		$datawait = $this->dbSelect($sqlwait,array(Xphp::$_config['TASKSTATUS']['WAITTING']));
		$waitNum = intval($datawait[0]['wait_num']);
		
		$allNum = $waitNum+$finishNum;
		if($allNum>0){
		    $rate = round($finishNum/($allNum) * 100,2);
		}else{
		    $rate = 0;
		}
		
		$info = array(
			'wait_num' => $waitNum,
			'finish_num' => $finishNum,
			'rate' => $rate.'%'
		);
		return $info;
	}
	
	
	/**
	 * 获取等待任务的数据
	 */
	public function getWaitJobData($params){
		$sqlParams = array(Xphp::$_config['TASKSTATUS']['WAITTING']);
		$sql = "select bt.agent_uuid, bt.task_uuid, unix_timestamp(bs.next_start_time) next_start_time, bt.task_name, bt.task_uuid from bd_task bt, bd_strategy bs where bt.strategy_id = bs.strategy_id and to_days(next_start_time) = to_days(now()) and bt.module_type in (2,3,4) and bt.task_type in (1, 2, 8, 28, 29) and bt.task_status = ? order by bs.next_start_time asc, bs.strategy_id asc";
		$data = $this->dbSelect($sql, $sqlParams);
		
		$sqlRun = "select task_uuid from bd_task where module_type in (2,3,4) and task_status = ? and task_type in (1, 2, 8, 28, 29)";
		$dataRun = $this->dbSelect($sqlRun, array(Xphp::$_config['TASKSTATUS']['RUNNING']));
		$uiStatus  = 0;
		if(empty($data) && empty($dataRun)){
			$uiStatus = 3;
		}else if(empty($data) && !empty($dataRun)){
			$uiStatus = 2;
		}else if(!empty($data) && empty($dataRun)){
			$uiStatus = 1;
		}
		$waitInfo = array();
		foreach ($data as $d){
			$waitTime = intval($d['next_start_time']) -  time();
			if($waitTime > 15) continue;
			$waitInfo[] = array(
				'task_uuid' => $d['task_uuid']
			);
		}
		if(empty($waitInfo) && !empty($dataRun)){
			$uiStatus = 2;
		}else if(!empty($waitInfo)){
			$uiStatus = 1;
		}
		$nowTime = time();
		$nextTime = intval($data[0]['next_start_time']);
		$waitTime = $nextTime - $nowTime;
		$info = array(
			'task_type' => $data[0]['task_type'],
			'next_time' => $nextTime,
			'wait_time'=>$waitTime,
			'wait_time_des' => date('Y/m/d H:i:s', $nextTime),
			'task_name' => $data[0]['task_name'],
			'vm_num' => $this->getTaskVmnum($data[0]['task_uuid']),
		    'agnetuuid' => $data[0]['agent_uuid'],
		    'agent_num' => 1,
			'taskuuid' => $data[0]['task_uuid'],
			'taskStatus' => false,
			'ui_status' => $uiStatus
		);
		
		return json_encode($info);
		
	}
	
	/**
	 * 获取任务虚拟机个数
	 * @param string $taskuuid
	 */
	private function getTaskVmnum($taskuuid){
		$sql = "select count(machine_id) as vm_num from vm_machine_list where task_uuid = ? ";
		$data = $this->dbSelect($sql, array($taskuuid));
		$vmNum = $data[0]['vm_num'];
		return $vmNum;
	}
	
	
	/**
	 * 方法库-字节转换-转换成MB格式等
	 * @param int $num  数值
	 * @param $valueFlag   是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
	 * @return string
	 */
	public function calSize($num, $valueFlag = false) {
		$num = floatval($num);
		if(0 == $num && !$valueFlag){
			return Xphp::$_config['NULLSPACE'];
		}
		$type = array( "B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
		$j = 0;
		while($num >= 1024) {
			if( $j >= 11 ) return $num.$type[$j];
			$num = $num / 1024;
			$j++;
		}
		$num = sprintf("%.2f",$num);
		return $num." ".$type[$j];
	}
	
	/**
	 * 计算百分比
	 * @param int $total   分母
	 * @param int $value   分子
	 * @return number|string
	 */
	public function calPercent($total, $value){
		$total = floatval($total);
		$value = floatval($value);
		if(0 == $total) return 0 . "%";
		if($total <= $value) return '100%';
		$percent =sprintf("%.2f",$value * 100 / $total) . "%";
		return $percent;
	}

	/**
	 * 计算速度-转换成MB/s格式等
	 * @param int $num
	 * @return string
	 */
	public function calSpeed($num){
	
		$speed = $this->calSize($num);
		if(0 == $speed){
			return $speed;
		}
		return $speed . "/s";
	}

	/**
	 * 记录刷新日志
	 */
	public function writeRefreshLog($params)
	{
		// 构建文件路径
		$filepath = Xphp::$_config['LOG_PATH'] . '/web/';
		$filename = $filepath . 'refresh_time-' . date('Y-m-d') . '.log';
		$date = date('Y-m-d H:i:s');
		if(!file_exists($filename)){
			$addFile = "touch " . $filename;
    		exec($addFile);
			// 删除日志，保证只有15个，按照时间顺序排序
			$cmd = "ls -tr " . $filepath . "refresh_time-*";
			exec($cmd, $output);
			$count = count($output);
			// 保留90天，一天一个文件，保留90个
			if($count > 90) {
				$row = $count - 90;
				// 删除90个之前的日志文件
				for ($i = 0; $i < $row; $i++) {
					if(file_exists($output[$i])) {
						// 这里采用unlink，不采用rm -rf， 避免风险
						unlink($output[$i]);
					}
				}
			}
		}
		$count = file_put_contents($filename, $date."\n", FILE_APPEND | LOCK_EX);
		if($count > 0){
    		return true;
    	}
    	return false;
	}
	
	/**
	 * 获取文件代理统计
	 */
	private function getFsData(){
	    $info = array();
	    //获取所有代理个数
	    $sqlHost ="select count(distinct agent_uuid) as host_count from bd_agent where agent_type = 0 and (online_flag = 1 or register_flag = 1)";
	    $dataHost = $this->dbSelect($sqlHost, array());
	    
	    //获取受保护代理个数
	    $sqlProtect =  "select count(distinct bt.agent_uuid) as protect_num from bd_task bt left join bd_agent ba on bt.agent_uuid = ba.agent_uuid where ba.agent_type = 0 and bt.module_type = ? and bt.task_type = ?";
	    $dataProtect = $this->dbSelect($sqlProtect, array(Xphp::$_config['MODULE_TYPE']['FS'], Xphp::$_config['TASKTYPE']['BACKUP']));
	    
	    $allHost = intval($dataHost[0]['host_count']);
	    $protectHost = intval($dataProtect[0]['protect_num']);
	    
	    if($allHost > 0){
	        $percent = round(($protectHost/$allHost)*100, 1);
	    }else{
	        $percent = 0;
	    }
	    $unprotectHost = $allHost - $protectHost;
	    $info = array(
	        "host_num" => $allHost,
	        "protect_num" => $protectHost,
	        "unprotect_num" => $unprotectHost,
	        'percent' => sprintf("%.1f",$percent).'%'
	    );
	    
	    return $info;
	    
	}
	
	/**
	 * 获取数据库代理统计
	 */
	private function getDbData(){
	    $info = array();
	    //获取所有代理个数
	    $sqlHost ="select count(distinct agent_uuid) as host_count from bd_agent where agent_type = 1";
	    $dataHost = $this->dbSelect($sqlHost, array());
	    
	    //获取受保护代理个数
	    $sqlProtect =  "select count(distinct bt.agent_uuid) as protect_num from bd_task bt left join bd_agent ba on bt.agent_uuid = ba.agent_uuid where ba.agent_type = 1 and bt.module_type = ? and bt.task_type = ?";
	    $dataProtect = $this->dbSelect($sqlProtect, array(Xphp::$_config['MODULE_TYPE']['DB'], Xphp::$_config['TASKTYPE']['DB_BACKUP']));
	    
	    $allHost = intval($dataHost[0]['host_count']);
	    $protectHost = intval($dataProtect[0]['protect_num']);
	    //超出总数按总数计算
	    if($protectHost > $allHost){
	        $protectHost = $allHost;
	    }
	    
	    if($allHost > 0){
	        $percent = round(($protectHost/$allHost)*100, 1);
	    }else{
	        $percent = 0;
	    }
	    $unprotectHost = $allHost - $protectHost;
	    $info = array(
	        "host_num" => $allHost,
	        "protect_num" => $protectHost,
	        "unprotect_num" => $unprotectHost,
	        'percent' => sprintf("%.1f",$percent).'%'
	    );
	    
	    return $info;
	    
	}
}	
?>	