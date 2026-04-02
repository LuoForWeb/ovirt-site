<?php
/******************************************* 
** 报表管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-04-10 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class ReportHandler extends OPHandler{
	
	/**
	 * 根据存储状态得到每个状态包含多少个存储
	 */
	public function getStoragePie($params){
		$sql = "select node_uuid,status, mount_flag, warning_type, warning_value, total_size, free_size 
		    from bd_storage_resource where lan_free_flag = ?";
		$data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET']));
		 
		$nodeHandler = Xphp::instance('NodeHandler');
		$storageHandler = Xphp::instance('StorageHandler');
		$info = array();
		//初始化每种状态
		$online = 0;	//在线
		$offline = 0;	//离线
		$unmount = 0;	//未挂载
		$warning = 0;	//告警
		foreach ($data as $d){
			$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
			$status = $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
			if($d['warning_type'] == 1){
				$value = round(intval($d['free_size'])/intval($d['total_size']) * 100, 2);
				if(floatval($value) <= floatval($d['warning_value'])){
					$status = Xphp::$_config['STORAGE_STATUS']['WARNING'];
				}
			}else{
				if($d['free_size'] <= intval($d['warning_value'])){
					$status = Xphp::$_config['STORAGE_STATUS']['WARNING'];
				}
			}
			
			//获取到存储的状态，根据状态增加对应状态值的个数
			if($status == Xphp::$_config['STORAGE_STATUS']['ONLINE']){
				$online ++;
			}else if($status == Xphp::$_config['STORAGE_STATUS']['OFFLINE']){
				$offline ++;
			}else if($status == Xphp::$_config['STORAGE_STATUS']['UNMOUNT']){
				$unmount ++;
			}else if ($status == Xphp::$_config['STORAGE_STATUS']['WARNING']){
				$warning ++;
			}
		}
		
		//在线个数
		$info[] = array(
				"value" => $online,
				"des" => $online . Xphp::$_lang['WEB_PLATFORM_DC_NUM'],
				"name" => Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'],
		);
		
		//离线个数
		$info[] = array(
				"value" => $offline,
				"des" => $offline . Xphp::$_lang['WEB_PLATFORM_DC_NUM'],
				"name" => Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'],
		);
		 
		//未挂载个数
		$info[] = array(
				"value" => $unmount,
				"des" => $unmount . Xphp::$_lang['WEB_PLATFORM_DC_NUM'],
				"name" => Xphp::$_lang['WEB_STORAGE_STATUS_UNMOUNT'],
		);
		 
		//告警个数
		$info[] = array(
				"value" => $warning,
				"des" => $warning . Xphp::$_lang['WEB_PLATFORM_DC_NUM'],
				"name" => Xphp::$_lang['WEB_STORAGE_STATUS_WARNING'],
		);
		 
		return json_encode($info);
	}
	
	
	
	/**
	 * 得到存储统计柱状图
	 */
	public function getStorageBar($params){
		$sql = "select ip, node_uuid, node_nickname, host_name from bd_node order by node_id asc";
		$data = $this->dbSelect($sql,array());
		$info = array();
		$nodeHandler = Xphp::instance('NodeHandler');
		foreach($data as $d){
			$info[] = array(
					'node_name' => $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']) . "\n(" . $d['ip'] . ")",
					'storage_list' => $this->getStorageList($d['node_uuid'])
			);
		}
		
		foreach ($info as $key => $row) {
			$list[$key]  = $row['storage_list']; //把每个节点存储列表取出来存入list
		}
		array_multisort($list, SORT_DESC, $info); //给存储列表排序
		return json_encode($info);
		 
	}
	
	/**
	 * 得到存储列表
	 * @param string $nodeuuid
	 */
	public function getStorageList($nodeuuid){
		$sql = 'select bn.ip, bsr.storage_nickname, bsr.total_size, bsr.free_size, bsr.warning_value from bd_storage_resource bsr, bd_node bn where bsr.lan_free_flag = ? and bsr.node_uuid = bn.node_uuid and bsr.node_uuid = ? order by bsr.node_uuid desc, free_size desc';
		$data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET'], $nodeuuid));
		$list = array();
		$utils = Xphp::instance('Utils');
		foreach($data as $d){
			$warningFlag = false;
			//超出阈值，告警
			if($d['free_size'] <= $d['warning_value']){
				$warningFlag = true;
			}
			$used = $d['total_size'] - $d['free_size']; //存储用量
			$list[] = array(
					'storage_name' => $d['storage_nickname'],
					'used_size' => round($used/1024/1024/1024, 2),
					'free_size' => round($d['free_size']/1024/1024/1024, 2),
					'warning' => $warningFlag,
			);
		}
		 
		return $list;
	}
	
	/**
	 * 查询今天是否有插入统计数据
	 */
	public function getStorageMonitor(){
		$sql = "select count(*) as total from bd_storage_monitor where date = ?";
		$result = $this->dbSelect($sql, array(date('Y-m-d')));
		return intval($result[0]['total']);
	}
	
	/**
	 * 插入每天存储监控信息
	 * @param date $storagedate
	 */
	public function addStorageMointor($storagedate){
		//获取查询的存储监控日期
		$backupDate = strtotime($storagedate) - 3600*24;
		$backupDate = date("Y-m-d", $backupDate);
		$sql = "select details, real_size, task_type from bd_history_task where module_type = ? and task_type = ? and finish_time like '%". $backupDate . "%'";
		$data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP']));
		$sqlStorage = "select  sum(bbt.real_size) as real_size from bd_backup_timepoint bbt,bd_storage_resource bsr where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? and bbt.timepoint like '%". $backupDate . "%'";
		$dataStorage = $this->dbSelect($sqlStorage, array(Xphp::$_config['FLAG']['UNSET']));
		$jobHandler = Xphp::instance('JobHandler');
		$vmList = array();
		if(!empty($data)){
			foreach($data as $d){
				$details = json_decode($d['details'],true);
				$vmList = array_merge($vmList, $details);
				foreach ($details as $detail){
					$validSize += intval($detail['vm_valid_size']);
				}
				$backupSize += $validSize;
			}
		}else{
			$backupSize = 0;
		}
		$storageSize = 0;
		if(!empty($dataStorage)){
			foreach ($dataStorage as $storage){
				$storageSize += $storage['real_size'];
			}
		}else{
			$storageSize = 0;
		}
		 
		$backupNum = $this->getBackupNum($backupDate);	//备份个数
		$successNum = $this->getSuccessNum($backupDate);//成功个数
		 
		$backupVmList = $this->array_unset($vmList, "dir_path");
		$backupVmNum = count($backupVmList);
		$vmSuccessNum = $this->getVmNum($backupDate);
		$vmDetails = $this->getStatisticsDetails($vmList); //获取存储使用统计详情
		$date = date("Y-m-d"); //获取当前日期
		$remark = "";
		$sqlParams = array($date, $backupSize, $storageSize, $backupNum, $successNum, $backupVmNum, $vmSuccessNum, $vmDetails, $remark);
		//插入前一天存储统计监控
		$sqlInsert = "insert into bd_storage_monitor (date, total_backup_size, storage_size, backup_num, backup_success_num, vm_num, success_vm_num, details, remarks)
				values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->dbQuery($sqlInsert, $sqlParams);
		return $result;
		 
		 
	}
	
	/**
	 * 获取存储统计详情信息
	 * @param array $vmList
	 */
	public function getStatisticsDetails($vmList){
		$value = array();
		//遍历虚拟机列表
		foreach ($vmList as $vm){
			$key = $vm['dir_path'];
			if(empty($value[$key])){
				$value[$key] = array(
						'vm_name' =>  $vm['vm_name'],
						'vm_path' => $vm['dir_path'],
						'storage_size' => $vm['real_size'],
						'backup_num' => 1
				);
			}else{
				$value[$key]['storage_size'] += $vm['real_size'];
				$value[$key]['vm_size'] += $vm['vm_size'];
				$value[$key]['backup_num'] += 1;
			}
		}
		return json_encode($value);
		 
	}
	
	/**
	 * 数组去重函数
	 */
	private function array_unset($arr,$key){
		//建立一个目标数组
		$res = array();
		foreach ($arr as $value) {
			//查看有没有重复项
			if(isset($res[$value[$key]])){
				//有：销毁
				unset($value[$key]);
			}
			else{
				$res[$value[$key]] = $value;
			}
		}
		return $res;
	}
	
	
	/**
	 * 获取当天虚拟机备份总次数
	 */
	public function getBackupNum($backupDate){
		$sqlCount = "select count(id) as total from bd_history_task where module_type = ? and task_type = ? and finish_time like '%". $backupDate . "%'";
		$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP']));
		return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取当天虚拟机备份成功总次数
	 */
	public function getSuccessNum($backupDate){
		$sqlCount = "select count(id) as total from bd_history_task where module_type = ? and task_type = ? and error_code = ? and finish_time like '%". $backupDate . "%'";
		$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP'], 0));
		return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取当天备份虚拟机个数
	 */
	public function getVmNum($backupDate){
		$sqlCount = "select count(distinct vbt.dir_path) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint like '%". $backupDate . "%'";
		$dataCount = $this->dbSelect($sqlCount, array());
		return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取存储近期统计折线图
	 * 
	 */
	public function getStorageLine($params){
		$days = intval($params['days']); //获取天数
		$sql = "select real_size, timepoint from bd_backup_timepoint where DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= timepoint order by timepoint asc";
		$data = $this->dbSelect($sql, array());
		$info = array();
		$utils = Xphp::instance(Utils);
		//遍历添加每天存储大小和日期
		for($i=$days;$i>0;$i--){
			$dayValue = strtotime(date("Y-m-d")) - 3600*24*($i-1);
			$dayDate = date("Y-m-d", $dayValue);
			$storageSize = 0;
			foreach($data as $d){
				$day = date('Y-m-d',strtotime($d['timepoint']));
				if($day == $dayDate){
					$storageSize += $d['real_size'];
				}
			}
			$info[] = array(
				'date' => $dayDate,
				'storage_size' => round($storageSize/1024/1024/1024, 2),
			);
		}
				
		return json_encode($info);
		 
	}
	
	/**
	 * 获取虚拟化类型虚拟机受保护情况列表
	 *
	 */
	public function getVmTypeList($params){
		$sql = 'select distinct vv.hypervisor_type from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ';
		$sqlCount = 'select count(distinct vv.hypervisor_type) as total from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ';
		
		$sqlParams = array();
		$sqlCountParams = array();
		if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
			$sql .= 'and vv.user_uuid = ? order by vv.hypervisor_type asc';
			$sqlCount .= 'and vv.user_uuid = ?';
			
			$sqlParams = array(Xphp::$_user['useruuid']);
			$sqlCountParams = array(Xphp::$_user['useruuid']);
			
		}else{
			$sql .= ' order by vv.hypervisor_type asc';
		}
		$data = $this->dbSelect($sql, $sqlParams);
		$dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
		$utils = Xphp::instance("Utils");
		$records = array();
		$id = 0;
		foreach ($data as $d){
			$id++;
			$vmNumList = $this->getVmNumList(intval($d['hypervisor_type']));
			$records[] = array(
				'num' => $id,
				'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
				'total_vm_num' => $vmNumList['total_vm_num'],
				'protected_vm_num' => $vmNumList['protected_vm_num'],
			);
		}
		 
	
		return  json_encode($records);
	}
	
	
	/**
	 * 获取系统添加虚拟机总个数和保护个数
	 * @param intval $hypervisor
	 */
	public function getVmNumList($hypervisor){
		$totalSql = "select count(vt.dir_path) as total_vm_num from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.type = ? and vt.display_mode =? and vv.hypervisor_type = ?";
		$dataTotal = $this->dbSelect($totalSql, array(7, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], $hypervisor));
		$protectedSql = "select count(distinct dir_path) as protected_vm_num from vm_backup_timepoint where hypervisor_type = ?";
		$dataProtect = $this->dbSelect($protectedSql, array($hypervisor));
		$info = array(
			'total_vm_num' => intval($dataTotal[0]['total_vm_num']),
			'protected_vm_num' => intval($dataProtect[0]['protected_vm_num'])
		);
		return $info;
	}
	
	/**
	 * 获取各个虚拟化虚拟机备份情况饼状图
	 *
	 */
	public function getVmTypePie($params){
		$sql = "select distinct vv.hypervisor_type from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ";
		$sqlParams = array();
		if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
			$sql .= 'and vv.user_uuid = ? order by vv.hypervisor_type asc';
			$sqlParams = array(Xphp::$_user['useruuid']);
		}else{
			$sql .= ' order by vv.hypervisor_type asc';
		}
		$data = $this->dbSelect($sql, $sqlParams);
		$info = array();
		$totalSql = "select count(dir_path) as all_total_vm_num from vm_tree  where type = ? and display_mode =?";
		$dataTotal = $this->dbSelect($totalSql, array(7, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
		foreach ($data as $d){
			$vmNumList = $this->getVmNumList(intval($d['hypervisor_type']));
			$hypervisor = intval($d['hypervisor_type']);
			$info[] = array(
				'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][$hypervisor],
				'total_vm_num' => $vmNumList['total_vm_num'],
				'protected_vm_num' => $vmNumList['protected_vm_num'],
				'color' => $this->getHypervisorColor($hypervisor),
				'all_total_vm_num' => intval($dataTotal[0]['all_total_vm_num'])
			);
		}
		
		return json_encode($info);
		
	}
	
	/**
	 * 获取虚拟机备份概况列表
	 * @param unknown $params
	 */
	public function getVmBackupList($params){
		$start = $params['start'];
		$length = $params['length'];
		$sql = 'select distinct vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? ';
		$sqlCount = 'select count(distinct vbt.dir_path) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid  and bbt.module_type = ? ';
		
		$sqlParams = array();
		$sqlCountParams = array();
		if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
			$sql .= " and bbt.user_uuid = ? order by vbt.hypervisor_type asc limit ?, ?";
			$sqlCount .= " and bbt.user_uuid = ?";
			
			$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_user['useruuid'], $start,$length);
			$sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_user['useruuid']);
		}else{
			$sql .= " order by vbt.hypervisor_type asc limit ?, ?";
			$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], $start, $length);
			$sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
		}
		$data = $this->dbSelect($sql, $sqlParams);
		$dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
		$utils = Xphp::instance("Utils");
		$records = array();
		$records["data"] = array();
		$id = $start + 1;
		foreach ($data as $d){
			$vmBackupInfo = $this->getVmBackupInfo($d['dir_path']); //通过虚拟机路径获取虚拟机备份情况
			$records["data"][] = array(
				$id++,
				$vmBackupInfo['vm_name'],
				$vmBackupInfo['hypervisor'],
// 				$vmBackupInfo['backup_task'],
// 				$vmBackupInfo['last_backup_time'],
				$vmBackupInfo['backup_count'],
				$vmBackupInfo['backup_storage'],
			);
		}
		
		$records["draw"] = $params['draw'];
		$records["recordsTotal"] = $dataCount[0]['total'];
		$records["recordsFiltered"] = $dataCount[0]['total'];
		
		return  json_encode($records);
	}
	
	/**
	 * 获取虚拟机备份统计信息
	 * @param string $dirPath
	 */
	public function getVmBackupInfo($dirPath){
		$sql = "select distinct  vbt.vm_uuid, vbt.vcenter_uuid, vbt.vm_name, vbt.hypervisor_type from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? and bbt.module_type = ?";
		$data = $this->dbSelect($sql, array($dirPath, Xphp::$_config['MODULE_TYPE']['VM']));
		$vmHandler = Xphp::instance("Vmhandler");
		$lastBackupInfo = $vmHandler->getVMReportLastBackupInfo($data[0]['vcenter_uuid'], $data[0]['vm_uuid']);
		
		$info = array(
			'vm_name' => $data[0]['vm_name'],
			'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][intval($data[0]['hypervisor_type'])],
			'backup_task' => $lastBackupInfo['backuptask'],
			'last_backup_time'=> $lastBackupInfo['lastbackuptime'],
			'backup_count'=> $lastBackupInfo['backupcount'],
			'backup_storage'=> $lastBackupInfo['backupstorage']
		);
		return $info;
	}
	
	/**
	 * 根据虚拟化类型获取对应虚拟化饼状图配色
	 * @param intval $hypervisor
	 */
	public function getHypervisorColor($hypervisor){
		$color = '#4ad1cd';
		$type = Xphp::$_config['VMHYPERVISORTYPE'];
		switch ($hypervisor){
			case $type['VM_HYPERVISOR_TYPE_VMWARE']:
			case $type['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
				$color = "#76B61A";
				break;
			case $type['VM_HYPERVISOR_TYPE_HYPERV']:
				$color = "#0D0B64";
				break;
			case $type['VM_HYPERVISOR_TYPE_XENSERVER']:
				$color = "#2268D5";
				break;
			case $type['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
				$color = "#002976";
				break;
			case $type['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
				$color = "#FD2B34";
				break;
			case $type['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
				$color = "#B41911";
				break;
			case $type['VM_HYPERVISOR_TYPE_KVM']:
				$color = "#7F9581";
				break;
			case $type['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
				$color = "#E8862F";
				break;
			case $type['VM_HYPERVISOR_TYPE_H3C_KVM']:
				$color = "#FB2619";
				break;
			case $type['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
				$color = "#39B860";
				break;
			case $type['VM_HYPERVISOR_TYPE_SDC_OS_KVM']:
				$color = "#A18B8B";
				break;
			case $type['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
				$color = "#00B8EF";
				break;
			case $type['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
			case $type['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
				$color = "#004E9A";
				break;
			case $type['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
			case $type['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
				$color = "#FBAB8B";
				break;
			case $type['VM_HYPERVISOR_TYPE_RHV_KVM']:
			case $type['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
				$color = "#FF1823";
				break;
			case $type['VM_HYPERVISOR_TYPE_XEN']:
				$color = "#1659DE";
				break;
			case $type['VM_HYPERVISOR_TYPE_ORACLEVM']:
				$color = "#E47277";
				break;
			case $type['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
				$color = "#74BCF9";
				break;
			case $type['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
				$color = "#327333";
				break;
			case $type['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
				$color = "#C2C621";
				break;
			case $type['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
			case $type['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
				$color = "#002976";
				break;
			case $type['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
				$color = "#BD1FB7";
				break;
				 
		}
		return $color;
	}
	
	/**
	 * 获取备份的虚拟机列表
	 * @param unknown $params
	 */
	public function getBackupVmList($params){
		$sql = "select distinct vbt.dir_path, vbt.vm_uuid, vbt.vm_name from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid ";  
		
		$sqlParams = array();
		if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
			$sql .= " and bbt.user_uuid = ? order by vbt.hypervisor_type asc";
			$sqlParams = array(Xphp::$_user['useruuid']);
		}else{
			$sql .=" order by vbt.hypervisor_type asc";
		}
		
		$data = $this->dbSelect($sql, $sqlParams);
		$info = array();
		foreach($data as $d){
			$info[] = array(
				'name' => $d['vm_name'],
				'value' => $d['dir_path']
			);
			
		}
		
		return json_encode($info);
	}
	
	/**
	 * 获取选中的虚拟机备份点统计信息
	 * @param unknown $params
	 */
	public function getPointPie($params){
		$path = $params['dirPath'];
		$sql = "select count(vbt.vm_timepoint_id) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? ";
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
		if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
			$sql .= " and bbt.user_uuid = ? ";
			$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_user['useruuid']);
			if($path != 0){	// 选择所有虚拟机
				$sql .= " and vbt.dir_path = ? ";
				$sqlParams = array_merge($sqlParams, array($path));
			}
		}else{
			if($path !=0){
				$sql .= " and vbt.dir_path = ? ";
				$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], $path);
			}
		}
		
		$data = $this->dbSelect($sql, $sqlParams);
		$info = array(
			'total_point' => $data[0]['total'],
			'full_point' => $this->getTimepointNum($path, Xphp::$_config['BACKUP_MODE']['FULL']),
			'incr_point' => $this->getTimepointNum($path, Xphp::$_config['BACKUP_MODE']['INCREMENTAL']),
			'diff_point' => $this->getTimepointNum($path, Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'])
		);
		
		return json_encode($info);
	}
	
	/**
	 * 获取备份时间点个数
	 * @param string $path
	 * @param intval $mode
	 */
	public function getTimepointNum($path, $mode){
		$sql = 'select count(vbt.vm_timepoint_id) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? ';
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
		if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
			$sql .= " and bbt.user_uuid = ? ";
			$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_user['useruuid']);
			if($path != 0){	// 选择所有虚拟机
				$sql .= " and vbt.dir_path =? ";
				$sqlParams = array_merge($sqlParams, array($path));
			}
		}else{
			if($path !=0){
				$sql .= " and vbt.dir_path = ? ";
				$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], $path);
			}
		}
		
		$sql .= " and bbt.backup_mode = ?";
		$sqlParams = array_merge($sqlParams, array($mode));
		
		$data = $this->dbSelect($sql, $sqlParams);
		
		return $data[0]['total'];
	}
	
	/**
	 * 获取虚拟机备份存储大小统计图信息
	 * @param unknown $params
	 */
	public function getVmStoragePie($params){
		$path = $params['dirPath'];
		$sql = "select sum(bbt.real_size) as used from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ?";
		$sqlParams = array($path);
		if(empty($path)){
			$sql = "select sum(bbt.real_size) as used from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid";
			$sqlParams = array();
		}
		$data = $this->dbSelect($sql, $sqlParams);
		
		$utils = Xphp::instance('Utils');
		$vmSize = $this->getVmFreeSize();
		$freeSize = $vmSize['free'];
		$usedSizeDes = $utils->calSize($data[0]['used']);
		$usedSize = $data[0]['used'];
		if(empty($data[0]['used'])){
			$usedSize = 0;
		}
		$info = array(
			'used_size' => $usedSize,
			'usedDes' => $usedSizeDes,
			'free_size' => $freeSize,
			'freeDes' => $utils->calSize($freeSize)
		);
		
		return json_encode($info);
	}
	
	/**
	 * 获取虚拟机空闲大小 
	 */
	public function getVmFreeSize(){
		$sql = "select sum(total_size) as total, sum(free_size) as free from bd_storage_resource where lan_free_flag = ?";
		$data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET']));
		$vmSize = array(
			'free' => $data[0]['free'],
			'used' => $data[0]['total'] - $data[0]['free'],
			'total' => $data[0]['total']
		);
		return $vmSize;
	}
	
	/**
	 * 获取虚拟机备份存储大小近期统计信息 
	 * @param unknown $params
	 */
	public function getVmStorageLine($params){
		$path = $params['dirPath'];
		$days = intval($params['days']);
		$sqlParams = array($path);
		$sql = "select bbt.real_size, bbt.timepoint from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and vbt.dir_path = ? and  DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= timepoint order by bbt.timepoint asc";
		if(!$path){
			$sql = "select bbt.real_size, bbt.timepoint from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and  DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= timepoint order by bbt.timepoint asc";
			$sqlParams = array();
		} 
		$data = $this->dbSelect($sql,$sqlParams);
		$info = array();
		$utils = Xphp::instance('Utils');
		for($i=$days;$i>0;$i--){
			$dayValue = strtotime(date("Y-m-d")) - 3600*24*($i-1);
			$dayDate = date("Y-m-d", $dayValue);
			$storageSize = 0;
			$storageDes = '0';
			foreach($data as $d){
				$day = date('Y-m-d',strtotime($d['timepoint']));
				if($day == $dayDate){
					$storageSize += $d['real_size'];
				}
			}
			$storageDes = $utils->calSize($storageSize);
			if($storageSize == 0){
				$storageDes = 0;
			}
			$info[] = array(
				'date' => $dayDate,
				'storage_size' => $storageSize,
				'des' => $storageDes
			);
			
		}
		
		return json_encode($info);
		
	}
	
	/**
	 * 获取日历时间点统计
	 * @param unknown $params
	 */
	public function getTimepointDate($params){
		$path = $params['dirPath'];
		$date = $params['date'];
		$flag = $params['changeFlag'];
		$sql = "select bbt.timepoint, bbt.backup_mode from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and vbt.dir_path = ? and bbt.timepoint like '%".$date."%' order by bbt.timepoint asc";
		$sqlParams = array($path);
		if(!$path){
			$sql = "select bbt.timepoint, bbt.backup_mode from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and bbt.timepoint like '%".$date."%' order by bbt.timepoint asc";
			$sqlParams= array();
			
		}
		$data = $this->dbSelect($sql, $sqlParams);
		$info =  array();
		$timepointInfo = array();
		$dayCount =  intval(date('t', strtotime($date)));
		for($i= 0;$i<$dayCount;$i++){
			$dayValue = strtotime(date('Y-m-01', strtotime(date("Y-m-d")))) + 3600*24*$i;
			if($flag){
				$dayValue = strtotime($date) + 3600*24*$i;
			}
			$dayDate = date("Y-m-d", $dayValue);
			$fullPoint = array();
			$incrPoint = array();
			$diffPoint = array();
			foreach ($data as $d){
				$timepoint = date('Y-m-d',strtotime($d['timepoint']));
				$mode = $d['backup_mode'];
				if($mode == Xphp::$_config['BACKUP_MODE']['FULL'] && $timepoint == $dayDate){
					$fullPoint[] = $d['timepoint'];
				}else if($mode == Xphp::$_config['BACKUP_MODE']['INCREMENTAL'] && $timepoint == $dayDate){
					$incrPoint[] = $d['timepoint'];
				}else if($mode == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'] && $timepoint == $dayDate){
					$diffPoint[] = $d['timepoint'];
				}
			}
			if(!empty($fullPoint)){
				$fullNum = count($fullPoint);
				$timepointInfo[] = array(
					"id" => $fullPoint[0],
					"title" => $fullNum . Xphp::$_lang['WEB_REPORT_FULL_BACKUP_NUM'],
					"class" =>"mode-full",
					"start" => strtotime($fullPoint[0])* 1000,
					"end"=> strtotime($fullPoint[$fullNum-1])*1000
				);
			}
			
			if(!empty($incrPoint)){
				$incrNum = count($incrPoint);
				$timepointInfo[] = array(
						"title" => $incrNum . Xphp::$_lang['WEB_REPORT_INCR_BACKUP_NUM'],
						"class" =>"mode-incr",
						"start" => strtotime($incrPoint[0])*1000,
						"end"=> strtotime($incrPoint[$incrNum-1])*1000
				);
			}
			
			if(!empty($diffPoint)){
				$diffNum = count($diffPoint);
				$timepointInfo[] = array(
						"title" => $diffNum . Xphp::$_lang['WEB_REPORT_DIFF_BACKUP_NUM'],
						"class" =>"mode-diff",
						"start" => strtotime($diffPoint[0])*1000,
						"end"=> strtotime($diffPoint[$diffNum-1])*1000
				);
			}
			
		}
		$day = date("Y-m-d");
		$language = $this->getSystemLang();
		$info = array(
			'language' => $language,
			'day'=> $day,
			'source' => $timepointInfo
		);
		return json_encode($info);
		
	}
	
	/**
	 * 获取系统设置语言
	 */
	public function getSystemLang(){
		$userName = Xphp::$_user['username'];
		$sql = "select language from bd_user where user_name = ?";
		$data = $this->dbSelect($sql, array($userName));
		return $data[0]['language'];
	}
}
?>