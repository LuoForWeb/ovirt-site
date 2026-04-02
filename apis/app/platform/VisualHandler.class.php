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
		$sql = "select bt.task_uuid from bd_task bt, bd_running_info bri where bt.task_uuid = bri.task_uuid and bt.task_status = ? and bt.module_type = ? and bt.task_type in (1, 2, 8) order by bri.start_time asc limit 0, 4";
		$data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['RUNNING'],Xphp::$_config['MODULE_TYPE']['VM']));
		$info = array();
		if(!empty($data)){
			$taskList = array();
			foreach ($data as $d){
				$taskList[] = $d['task_uuid'];
				$info[] = $this->getTaskRunInfo($d['task_uuid']);
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
	 * 获取运行任务详细信息
	 * @param string $taskuuid
	 */
	private function getTaskRunInfo($taskuuid){
		$sql = "select vml.vm_name, vml.vm_size, vml.completed_size, vml.real_size, vml.task_status, vml.dir_path, vml.new_name,
                       vml.vm_uuid, bri.speed, bri.speed_time, bri.current_completed_size, unix_timestamp(bri.start_time) start_time,
                         bt.task_status as bd_task_status, bt.storage_uuid, bt.task_uuid, bt.task_type,bt.task_name
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
				$freeSize = intval($d['vm_size']) - intval($d['current_completed_size']);
				$info = array(
						'status' => $d['task_status'],
						'task_name' => $d['task_name'],
						'task_type' => intval($d['task_type']),
						'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
						'vm_name' => $d['vm_name'],
						'speedDes' => $this->getVmSpeed($d['speed'],$d['speed_time']),
						'speed' => intval($d['speed']),
						'progress' => $this->calPercent($d['vm_size'], $d['current_completed_size']),
						'realSize' => intval($d['current_completed_size']),
						'realSize_des' => $this->calSize(intval($d['current_completed_size'])),
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
						'task_type_des' => $ptDes['TASKTYPEDES'][$d['task_type']],
						'vm_name' => $data[0]['vm_name'],
						'speedDes' => $this->getVmSpeed($data[0]['speed'],$data[0]['speed_time']),
						'speed' => intval($data[0]['speed']),
						'progress' => $this->calPercent($data[0]['vm_size'], $data[0]['current_completed_size']),
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
		$sqlHost ="select count(vh.host_uuid) as host_count from vm_host vh, vm_vcenter vv where vh.vcenter_uuid = vv.vcenter_uuid ";
    	$dataHost = $this->dbSelect($sqlHost);
    	
    	//获取受保护虚拟机个数
    	$sqlProtect = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv where vml.vcenter_uuid = vv.vcenter_uuid ";
    	$dataProtect = $this->dbSelect($sqlProtect);
    	
    	//获取备份系统所有虚拟机个数
    	$sqlAll = "select count(distinct vt.uuid) as all_vms from vm_tree vt, vm_vcenter vv where vt.type = 7 and vt.vcenter_uuid = vv.vcenter_uuid ";
    	$sqlAllParams = array();
    	$dataAll = $this->dbSelect($sqlAll);
    	$protectNum = intval($dataProtect[0]['protect_vms']);
    	$allNum = intval($dataAll[0]['all_vms']);
    	$percent = round(($protectNum/$allNum)*100, 1);
    	$info = array(
    		"host_num" => intval($dataHost[0]['host_count']),
    		"protect_vm_num" => $protectNum,
    		"unprotect_vm_num" => $allNum - $protectNum,
    		"all_vm_num" => $allNum,
    		'percent' => sprintf("%.1f",$percent).'%'
    	);
    	
    	return $info;
		
	}
	
	/**
	 *  获取存储统计
	 */
	private function getStorageData(){
		$sql = "select count(bsr.storage_uuid) as storage_count, sum(bsr.total_size) as total_size, sum(bsr.free_size) as free_size from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid ";
		$sqlParams = array();
		$data = $this->dbSelect($sql,$sqlParams);
		$utils = Xphp::instance('Utils');
		if(!$data){
			$totalSize = 0;
			$freeSize = 0;
		}else{
			$totalSize = intval($data[0]['total_size']);
			$freeSize = intval($data[0]['free_size']);
		}
		$percent = round(($freeSize/$totalSize)*100, 1);
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
		$sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data from bd_user_extension ";
		$sqlParams = array();
		$data = $this->dbSelect($sql, $sqlParams);
		$total = 0;
		foreach ($data as $d){
			$total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] +
			$d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'];
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
		$sql = "select module_type, submodule_type, task_type, task_name,  unix_timestamp(finish_time) finish_time, error_code, total_size
                from bd_history_task where module_type = ? and task_type in (1, 2, 8) order by finish_time desc limit ? , ?";
		$data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['VM'], 0, 20));
		$taskInfo = array();
		$jobHandler = Xphp::instance('JobHandler');
		$id = 0;
		foreach ($data as $d){
			$id++;
			$taskInfo[] = array(
					'id' => $id,
					'task_name' => $d['task_name'],
					'task_type' => $d['task_type'],
					'total_size' => $this->calSize(intval($d['total_size'])),
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
		$sql = "select distinct vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? 
				order by vbt.hypervisor_type asc, bbt.timepoint desc limit ?, ?";
		$data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['VM'],0, 20));
		$vmInfo = array();
		$utils = Xphp::instance('Utils');
		$id = 0;
		$reportHandler = Xphp::instance('ReportHandler');
		foreach ($data as $d){
			$id++;
			$vmBackupInfo = $reportHandler->getVmBackupInfo($d['dir_path']);
			$vmInfo[] = array(
					'id' => $id,
					'vm_name' => $vmBackupInfo['vm_name'],
					'count' => $vmBackupInfo['backup_count'],
					'last_time' => $vmBackupInfo['last_backup_time'],
			);
		}
		
		return $vmInfo;
	}
	
	/**
	 *获取每天备份虚拟机个数 
	 */
	private function getDailyVms(){
		$days = 6; //一周
		$sql = "select vm_num, unix_timestamp(date) date from bd_storage_monitor vbt where DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= date order by date asc";
		$data = $this->dbSelect($sql,array());
		$info = array();
		$i = 1;
		$num = 0;
		foreach ($data as $d){
			$num += intval($d['vm_num']);
			$dateTime = intval($d['date']) - 3600*24;
			$averageNum = $num / $i;
			$info[] = array(
				'date' => date("m-d", $dateTime),
				'num' => intval($d['vm_num']),
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
		$sqlCount = "select count(distinct vbt.dir_path) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint like '%". $today . "%'";
		$dataCount = $this->dbSelect($sqlCount, array());
		$num += intval($dataCount[0]['total']);
		$averageNum = $num / $i;
		$info = array(
			'date' => date("m-d"),
			'num' => intval($dataCount[0]['total']),
			'average_num' =>  round($averageNum,1)
		);
		return $info;
	}
	
	/**
	 *获取每天备份存储容量
	 */
	private function getDailyStorages(){
		$days = 3; //一周
		$sql = "select storage_size, unix_timestamp(date) date from bd_storage_monitor vbt where DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= date order by date asc";
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
		$sql = "select real_size, task_type from bd_history_task where module_type = ? and task_type = ? and finish_time like '%". $today . "%'";
		$data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP']));
		$totalSize = 0;
		if(!empty($data)){
			foreach ($data as $d){
				$totalSize += intval($d['real_size']);	
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
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
		$sql = "select count(distinct task_uuid) as finish_num from bd_history_task where module_type = ? and error_code = 0 and task_type in (1, 2, 8) and to_days(finish_time) = to_days(now())  order by finish_time desc";
		$data = $this->dbSelect($sql,$sqlParams);
		$finishNum = intval($data[0]['finish_num']);
		$sqlwait = "select count(bs.strategy_id) as wait_num from bd_task bt, bd_strategy bs where bt.strategy_id = bs.strategy_id and to_days(next_start_time) = to_days(now()) and bt.module_type = ? and bt.task_status = ? and bt.task_type in (1, 2, 8) order by bs.next_start_time desc";
		$datawait = $this->dbSelect($sqlwait,array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKSTATUS']['WAITTING']));
		$waitNum = intval($datawait[0]['wait_num']);
		$rate = round($finishNum/($waitNum+$finishNum) * 100,2);
		
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
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKSTATUS']['WAITTING']);
		$sql = "select bt.task_uuid, unix_timestamp(bs.next_start_time) next_start_time, bt.task_name, bt.task_uuid from bd_task bt, bd_strategy bs where bt.strategy_id = bs.strategy_id and to_days(next_start_time) = to_days(now()) and bt.module_type = ? and bt.task_type in (1, 2, 8) and bt.task_status = ? order by bs.next_start_time asc, bs.strategy_id asc";
		$data = $this->dbSelect($sql, $sqlParams);
		
		$sqlRun = "select task_uuid from bd_task where module_type =? and task_status = ? and task_type in (1, 2, 8)";
		$dataRun = $this->dbSelect($sqlRun, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKSTATUS']['RUNNING']));
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
	public function writeRefreshLog($params){
		$date = date('Y-m-d H:i:s');
		$file = "/var/log/vinchin/web/refresh_time.log";
		if(!file_exists($file)){
			$addFile = "touch ".$file;
    		exec($addFile);
		}
		$count = file_put_contents($file, $date."\n", FILE_APPEND | LOCK_EX);
		if($count > 0){
    		return true;
    	}
    	return false;
	}
}	
?>	