<?php
/******************************************* call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));call_user_func(array($this, $version[$this->version]));
** 备份时间点处理类
** 
** @author       luokai@vinchin.com 
** @date         2018-1-31 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APIBackupPointHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/points/vmconfig" => array(
        	"GET" => 'getBackupVMConfig',
        ),
    	"/points/vmlists" => array(
        	"GET" => 'getBackupVmList',
        ),
    	"/points/lists" => array(
    		"GET" => 'getPointList',
    		"DELETE" => 'deleteTimepoint'
    	),
    	"/points/search" => array(
    		"GET" => 'getSearchPoint'
    	),
    	"/points/getnode" => array(
    		"GET" => 'getStorageNode'
    	),
    	"/points/taskpointlist" => array(
    		"POST" => 'getTasksPointList'
    	),
        "/points/backupvms" => array(
            "GET" => "getBackupVMs"
        )

    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    
    /**
     * 获取备份时间点存储和网络路由
     */
    protected function getBackupVMConfig(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getBackupVMConfigV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取所有已备份虚拟机列表路由
     */
    protected function getBackupVmList(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getBackupVmListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 获取所有已备份虚拟机列表路由
     */
    protected function getPointList(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getPointListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 删除虚拟机对应的备份时间点
     */
    protected function deleteTimepoint(){
    	//定义方法版本
    	$version = array(
    		"v1" => "deleteTimepointV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除虚拟机对应的备份时间点
     */
    protected function getSearchPoint(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getSearchPointV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到存储对应的节点
     */
    protected function getStorageNode(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getStorageNodeV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到存储对应的节点
     */
    protected function getTasksPointList(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getTasksPointListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到已备份虚拟机容量信息
     */
    protected function getBackupVMs(){
        //定义方法版本
        $version = array(
            "v1" => "getBackupVMsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    
    /**********getBackupVMConfig**********/
    private function getBackupVMConfigV1(){
    	$timepointuuid = $this->params['timepoint_uuid'];
    	$this->apiParamsCheck($timepointuuid);
    	$vmInfo = array();
    	$sql = "select vm_config, vm_uuid, vcenter_uuid, hypervisor_type from vm_backup_timepoint where timepoint_uuid = ?";
    	$sqlParams = array($timepointuuid);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$hypervisor = $data[0]['hypervisor_type'];
    	$config = json_decode($data[0]['vm_config'], true);
    	 
    	$vmInfo[] = array(
    		'timepoint_uuid' => $timepointuuid,
    		'vcenter_uuid' => $data[0]['vcenter_uuid'],
    		'vm_uuid' => $data[0]['vm_uuid'],
    		'disk_list' => $this->getVMConfigStorageInfo($config['disk_list'], $hypervisor),
    		'network_list' => $this->getVMConfigNetworkInfo($config['network_list'], $hypervisor),
    	);
    	 
    	return $this->apiResponse(true, 'API_CODE_POINTS_GET_VM_CONFIG', $vmInfo);
    }
    
    /**********getBackupVmList**********/
    private function getBackupVmListV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$hypervisor = $this->params['hypervisor'];
    	$this->apiParamsCheck($count, $hypervisor);
    	 
    	$sql = "select bbt.timepoint_uuid, bbt.task_uuid, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, vbt.version, vbt.dir_path
                from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.hypervisor_type = ? and bbt.module_type = ? limit ? , ?";
    	$sqlCount = "select count(distinct vbt.vm_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.hypervisor_type = ? and bbt.module_type = ? ";
    	 
    	$sqlParams = array($hypervisor, Xphp::$_config['MODULE_TYPE']['VM'], $begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, array($hypervisor, Xphp::$_config['MODULE_TYPE']['VM']));
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
    				'hypervisor' => $hypervisor,
    				'source_task_uuid' => $d['task_uuid'],
    				'timepointuuids' => $this->getVmTimepointuuids($d['vcenter_uuid'], $d['vm_uuid'])
    			);
    		}
    	}
    	 
    	$data = array(
    		"total" => intval($dataCount[0]['total']),
    		"begin" => $begin,
    		"count" => $count,
    		"records" => $records
    	);
    	 
    	return $this->apiResponse(true, "API_CODE_POINTS_GET_BACKUP_VM_LIST", $data);
    }
	
    /**********getPointList**********/
    private function getPointListV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$vcenteruuid = $this->params['vcenter_uuid'];
    	$vmuuid = $this->params['vm_uuid'];
    	$this->apiParamsCheck($vcenteruuid, $vmuuid);
    	$sqlParams = array($vcenteruuid, $vmuuid, Xphp::$_config['FLAG']['UNSET'], $begin, $count);
    	$sql = "select vbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) 
    		task_create_time, bbt.task_uuid, bbt.total_size, bbt.write_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? and bbt.copy_flag = ? order by bbt.timepoint limit ?, ?";
    	$data = $this->dbSelect($sql, $sqlParams);
    	$sqlCount = "select count(bbt.timepoint_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? and bbt.copy_flag = ?";
    	$dataCount = $this->dbSelect($sqlCount, array($vcenteruuid, $vmuuid, Xphp::$_config['FLAG']['UNSET']));
    	
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
    	
    	return $this->apiResponse(true, 'API_CODE_POINTS_GET_VM_POINTS_LIST', $data);
    	
    }
    
    /**********deleteTimepoint**********/
    private function deleteTimepointV1(){
    	//得到二维数组
    	//遍历，并按节点uuid分组
    	$deleteType = $this->params['delete_type'];
    	$this->apiParamsCheck($deleteType);
    	$utils = Xphp::instance('Utils');
    	$timepoints = array();
    	switch ($deleteType){
    		case Xphp::$_config['DELETETIMEPOINT']['VM_ALL_TIMEPOINT']:
    			$vcenteruuid = $this->params['vcenter_uuid'];
    			$vmuuid = $this->params['vm_uuid'];
    			$sql = "select timepoint_uuid, hypervisor_type from vm_backup_timepoint where vcenter_uuid = ? and vm_uuid = ?";
    			$data = $this->dbSelect($sql, array($vcenteruuid, $vmuuid));
    			if(empty($data)){
    				return $this->apiResponse(false, 'API_CODE_POINTS_TIME_POINT_NOT_EXIST');
    			}
    			foreach ($data as $d){
    				$timepoints[] = $d['timepoint_uuid'];
    			}
    			break;
    		case Xphp::$_config['DELETETIMEPOINT']['TIMEPOINT_LIST']:
    			$timepoints = $this->params['timepoint_list'];
    			foreach($timepoints as $t){
    				$sql = "select hypervisor_type from vm_backup_timepoint where timepoint_uuid = ?";
    				$data = $this->dbSelect($sql, array($t));
    				//如果时间点不存在
    				if(empty($data)){
    					return $this->apiResponse(false, 'API_CODE_POINTS_TIME_POINT_NOT_EXIST');
    				}
    				
    			}
    			break;
    		
    	}
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepoints[0]);
    	$hypervisor = $data[0]['hypervisor_type'];
    	$opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
    	$msg = array(
    		'timepoint_uuids' => $timepoints
    	);
    	$msg = json_encode($msg);
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
    	$result = $mbResult['result'];
    	//返回结果到UI
    	return $this->apiResponse($result, 'API_CODE_POINTS_DELETE_TIME_POINT', '', $mbResult);
    }
    
    /**********getSearchPoint**********/
    private function getSearchPointV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$vmname = $this->params['vm_name'];
    	$vcenteruuid = $this->params['vcenter_uuid'];
    	$this->apiParamsCheck($count);
    	$sql = "select vbt.dir_path, vbt.vcenter_uuid, vbt.vm_uuid, vbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time)
    		task_create_time, bbt.storage_uuid, bbt.task_uuid, bbt.total_size, bbt.write_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid ";
    	$sqlCount = "select count(bbt.timepoint_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid ";
    	
    	$sqlParams = array();
    	$sqlCountParams = array();
    	$utils = Xphp::instance('Utils');
    	$vmname = $utils->escapeWildcard($vmname);
    	//按虚拟机名搜索
    	if($this->checkEmpty($vmname)){
    	    $sql .= " and vbt.vm_name like ? ";
    	    $sqlCount .= " and vbt.vm_name like ? ";
    	    $sqlParams = array_merge($sqlParams,array('%'.$vmname.'%'));
    	    $sqlCountParams = array_merge($sqlCountParams,array('%'.$vmname.'%'));
    	}
    	
    	//虚拟化中心id
    	if(!empty($vcenteruuid)){
    	    $sql .= " and vbt.vcenter_uuid = ? ";
    	    $sqlCount .= " and vbt.vcenter_uuid = ? ";
    	    $sqlParams = array_merge($sqlParams,array($vcenteruuid));
    	    $sqlCountParams = array_merge($sqlCountParams,array($vcenteruuid));
    	}
    	$sql .= " order by vbt.vm_name asc limit ?, ?";
    	$sqlParams = array_merge($sqlParams, array($begin, $count));
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
    	 
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    				"timepoint_uuid" => $d['timepoint_uuid'],
    				"timepoint_time" => $d['timepoint'],
    				"storage_uuid" => $d['storage_uuid'],
    				"backup_mode" => intval($d['backup_mode']),
    				"depend_point_uuid" => $d['depend_point_uuid'],
    				"total_size" => intval($d['total_size']),
    				"real_size" => intval($d['write_size']),
    				"job_uuid" => $d['task_uuid'],
    				"job_name" => $d['task_name'],
    				"job_create_time" => $d['task_create_time'],
    				"vcenter_uuid" => $d['vcenter_uuid'],
    				"vm_uuid" => $d['vm_uuid'],
    				"vm_name" => $this->getNameWithUuid($d['vcenter_uuid'], $d['vm_uuid']),
    				"dir_path" => $d['dir_path'],
    				"child_point_list" => $this->getChildPointList($d['timepoint_uuid'])
    				
    		);
    	}
    	 
    	 
    	$data = array(
    			"total" => intval($dataCount[0]['total']),
    			"begin" => $begin,
    			"count" => $count,
    			"records" => $records
    	);
    	 
    	return $this->apiResponse(true, 'API_CODE_POINTS_GET_SELECT_POINTS_LIST', $data);
    }
    
    /**********getStorageNode**********/
    private function getStorageNodeV1(){
    	$storageuuid = $this->params['storage_uuid'];
    	$sql = "select bn.ip, bn.node_uuid from bd_node bn, bd_storage_resource bsr 
                where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	$info = array(
    		"node_uuid" => $data[0]['node_uuid'],
    		"node_ip" => $data[0]['ip']
    	);
    	
    	return $this->apiResponse(true, 'API_CODE_POINTS_GET_STORAGE_NODE_INFO', $info);
    			
    }
    
    /**********getTasksPointList**********/
    private function getTasksPointListV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$uuids = implode("','", $this->params['task_uuid_list']);
    	$this->apiParamsCheck($uuids);
    	$sql = "select vbt.dir_path, vbt.vcenter_uuid, vbt.vm_uuid, vbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time)
    		task_create_time, bbt.storage_uuid, bbt.task_uuid, bbt.total_size, bbt.write_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and bbt.backup_mode = ? and bbt.task_uuid in ('$uuids')order by bbt.timepoint limit ?, ?";
    	$sqlCount = "select count(bbt.timepoint_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and bbt.backup_mode = ? and bbt.task_uuid in ('$uuids')";
    	$data = $this->dbSelect($sql, array(Xphp::$_config['BACKUP_MODE']['FULL'], $begin, $count));
    	$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['BACKUP_MODE']['FULL']));
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    				"timepoint_uuid" => $d['timepoint_uuid'],
    				"timepoint_time" => $d['timepoint'],
    				"storage_uuid" => $d['storage_uuid'],
    				"backup_mode" => intval($d['backup_mode']),
    				"depend_point_uuid" => $d['depend_point_uuid'],
    				"total_size" => intval($d['total_size']),
    				"real_size" => intval($d['write_size']),
    				"job_uuid" => $d['task_uuid'],
    				"job_name" => $d['task_name'],
    				"job_create_time" => $d['task_create_time'],
    				"vcenter_uuid" => $d['vcenter_uuid'],
    				"vm_uuid" => $d['vm_uuid'],
    				"vm_name" => $this->getNameWithUuid($d['vcenter_uuid'], $d['vm_uuid']),
    				"dir_path" => $d['dir_path'],
    				"child_point_list" => $this->getChildPointList($d['timepoint_uuid'])
    	
    		);
    	}
    	$data = array(
    			"total" => intval($dataCount[0]['total']),
    			"begin" => $begin,
    			"count" => $count,
    			"records" => $records
    	);
    	
    	return $this->apiResponse(true, 'API_CODE_POINTS_GET_TASKUUID_POINTS_LIST', $data);
    	
    }
    
    
    /**********getBackupVMs**********/
    private function getBackupVMsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $this->apiParamsCheck($count);
        
        $sql = "select distinct vbt.vm_uuid, vbt.vcenter_uuid from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid = bbt.timepoint_uuid and
            bbt.module_type = ? order by bbt.timepoint desc limit ?, ? ";
        $sqlCount = "select count(distinct vbt.vm_uuid, vbt.vcenter_uuid) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid = bbt.timepoint_uuid 
            and bbt.module_type = ?";
        
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], $begin, $count);
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['MODULE_TYPE']['VM']));
        $records = array();
        $vm = array();
        foreach ($data as $d){
            if(!in_array($d['vcenter_uuid'] . $d['vm_uuid'], $vm)){
                $vm[] = $d['vcenter_uuid'] . $d['vm_uuid'];
                $taskInfo = $this->getVMTaskInfo($d['vcenter_uuid'], $d['vm_uuid']);
                $vmInfo = $this->getVmHistoryInfo($taskInfo['task_uuid'], $d['vm_uuid'], $d['vcenter_uuid']);
                $records[] = array(
                    'vm_uuid' => $d['vm_uuid'],
                    'vcenter_uuid' => $d['vcenter_uuid'],
                    'storage_size' => $taskInfo['storage_size'],
                    'task_uuid' => $taskInfo['task_uuid'],
                    'task_name' => $taskInfo['task_name'],
                    'vm_status' => $vmInfo['vm_status'],
                    'vm_exec_time' => $vmInfo['vm_exec_time'],
                    'task_exec_time' => $vmInfo['task_exec_time'],
                    'backup_type' => $vmInfo['backup_type']
                );
            }
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_POINTS_GET_VM_STORAGE_SIZE_LIST", $data);
    }
    
    /**************************************其他工具方法*******************************************/
    /**
     * 得到备份虚拟机的磁盘信息
     * @param unknown $storageList
     */
    public function getVMConfigStorageInfo($storageList, $hypervisor){
        $hypervisor = intval($hypervisor);
        $info = array();
        $utils = Xphp::instance("Utils");
        foreach ($storageList as $storage){
            switch ($hypervisor){
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
                    $info[] = array(
                    'vdi_uuid' => $storage['uuid'],
                    'vdi_name' => $storage['diskName'],
                    'virtual_size' => $utils->calSize($storage['totalSize']),
                    'size' => $storage['totalSize'],
                    'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']:
                    $info[] = array(
                    'vdi_uuid' => $storage['_disk_uuid'],
                    'vdi_name' => $storage['_disk_path'],
                    'virtual_size' => $utils->calSize($storage['_size']),
                    'size' => $storage['_size'],
                    'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XCP_NG']:
                    
                    if($storage['is_backup_or_recovery'] == false) break;
                    $info[] = array(
                        'vdi_uuid' => $storage['vdi_uuid'],
                        'vdi_name' => $storage['vdi_name'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
                    if($storage['is_backup_or_recovery'] == false) break;
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $storage['target_attr_dev'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
                    if($storage['is_backup_or_recovery'] == false) break;
                    $name = '['.$storage['storage_pool']['pool_name'].']'.$storage['target_attr_dev'];
                    if(empty($storage['storage_pool']['pool_name'])){
                        $name = $storage['target_attr_dev'];
                    }
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $name,
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'bootable' => $storage['bootable'],
                        'storage_type' => $storage['storage_type'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SDC_OS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ZSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XSKY']:
                    if($storage['is_backup_or_recovery'] == false) break;
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $storage['driver_attr_name'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
            }
        }
        return $info;
    }
    
    /**
     * 得到备份虚拟机的网络信息
     * @param unknown $networkList
     */
    public function getVMConfigNetworkInfo($networkList, $hypervisor){
        $hypervisor = intval($hypervisor);
        $info = array();
        foreach ($networkList as $network){
            switch ($hypervisor){
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
                    $info[] = array(
                    'net_num' => $network['label'],
                    'mac' => $network['macAddress'],
                    'data_network_uuid' => $network['deviceName']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']:
                    $info[] = array(
                    'net_num' => $network['_network_adapter_name'],
                    'mac' => $network['_mac_address'],
                    'data_network_uuid' => $network['deviceName']
                    
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XCP_NG']:
                    $info[] = array(
                    'net_num' => $network['net_num'],
                    'mac' => $network['mac'],
                    'data_network_uuid' => $network['deviceName']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SDC_OS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ZSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XSKY']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
                    $info[] = array(
                    'net_num' => $network['target_attr_dev'],
                    'mac' => $network['mac_attr_address'],
                    'data_network_uuid' => $network['deviceName']
                    );
                    break;
            }
        }
        $utils = Xphp::instance('Utils');
        $info = $utils->arraySort($info, 0, 'asc', 0, -1);
        return $info;
    }
    
    /**
     * 得到备份点虚拟机名字
     * @param string $vcenteruuid
     * @param string $vmuuid
     */
    private function getNameWithUuid($vcenteruuid, $vmuuid){
    	$sql = "select name from vm_tree where vcenter_uuid = ? and uuid = ?";
    	$data = $this->dbSelect($sql, array($vcenteruuid, $vmuuid));
    	$vmname = $data[0]['name'];
    	return $vmname;	
    }
    
    /**
     * 得到完备点下依赖的增量和差异点
     * @param string $timepountuuid
     */
    private function getChildPointList($timepointuuid){
    	$sql = "select vbt.dir_path, vbt.vcenter_uuid, vbt.vm_uuid, vbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time)
    		task_create_time, bbt.task_uuid, bbt.total_size, bbt.write_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and depend_point_uuid = ? order by bbt.timepoint asc";
    	$data = $this->dbSelect($sql, array($timepointuuid));
    	$childList = array();
    	$incrList = array();
    	foreach ($data as $d){
    		if($d['backup_mode']  == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']){
    			$childList[] = array(
    				"timepoint_uuid" => $d['timepoint_uuid'],
    				"timepoint_time" => $d['timepoint'],
    				"backup_mode" => intval($d['backup_mode']),
    				"full_timepoint_uuid" => $timepointuuid,
    				"depend_point_uuid" => $d['depend_point_uuid'],
    				"total_size" => intval($d['total_size']),
    				"real_size" => intval($d['write_size']),
    				"job_uuid" => $d['task_uuid'],
    				"job_name" => $d['task_name'],
    				"job_create_time" => $d['task_create_time'],
    				"vcenter_uuid" => $d['vcenter_uuid'],
    				"vm_uuid" => $d['vm_uuid'],
    				"vm_name" => $this->getNameWithUuid($d['vcenter_uuid'], $d['vm_uuid']),
    				"dir_path" => $d['dir_path']
    			);
    		}
    		if($d['backup_mode']  == Xphp::$_config['BACKUP_MODE']['INCREMENTAL']){
    			$incrList[] = array(
    				"timepoint_uuid" => $d['timepoint_uuid'],
    				"timepoint_time" => $d['timepoint'],
    				"backup_mode" => intval($d['backup_mode']),
    				"full_timepoint_uuid" => $timepointuuid,
    				"depend_point_uuid" => $d['depend_point_uuid'],
    				"total_size" => intval($d['total_size']),
    				"real_size" => intval($d['write_size']),
    				"job_uuid" => $d['task_uuid'],
    				"job_name" => $d['task_name'],
    				"job_create_time" => $d['task_create_time'],
    				"vcenter_uuid" => $d['vcenter_uuid'],
    				"vm_uuid" => $d['vm_uuid'],
    				"vm_name" => $this->getNameWithUuid($d['vcenter_uuid'], $d['vm_uuid']),
    				"dir_path" => $d['dir_path']
    			);
    			$incrChildList = $this->getIncrTimepoint($d['timepoint_uuid'], $timepointuuid, array());
    			$incrList = array_merge($incrList, $incrChildList);
    		}
    	}
    	$childList = array_merge($childList, $incrList);
    	return $childList;
    }
    
    
    /**
     * 得到所有的增备点
     * @param string $timepountuuid
     * @param string $fulltimepoint
     */
    private function getIncrTimepoint($timepointuuid, $fulltimepoint, $childPoint){
    	$sql = "select vbt.dir_path, vbt.vcenter_uuid, vbt.vm_uuid, vbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time)
    		task_create_time, bbt.task_uuid, bbt.total_size, bbt.write_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where vbt.timepoint_uuid =
    		bbt.timepoint_uuid and depend_point_uuid = ? order by bbt.timepoint asc";
    	$data = $this->dbSelect($sql, array($timepointuuid));
    	if($data[0]){
    		$list = array(
    				"timepoint_uuid" => $data[0]['timepoint_uuid'],
    				"timepoint_time" => $data[0]['timepoint'],
    				"backup_mode" => intval($data[0]['backup_mode']),
    				"full_timepoint_uuid" => $fulltimepoint,
    				"depend_point_uuid" => $data[0]['depend_point_uuid'],
    				"total_size" => intval($data[0]['total_size']),
    				"real_size" => intval($data[0]['write_size']),
    				"job_uuid" => $data[0]['task_uuid'],
    				"job_name" => $data[0]['task_name'],
    				"job_create_time" => $data[0]['task_create_time'],
    				"vcenter_uuid" => $data[0]['vcenter_uuid'],
    				"vm_uuid" => $data[0]['vm_uuid'],
    				"vm_name" => $this->getNameWithUuid($data[0]['vcenter_uuid'], $data[0]['vm_uuid']),
    				"dir_path" => $data[0]['dir_path']
    		);
    		
    		$childPoint[] = $list;
    		return $this->getIncrTimepoint($data[0]['timepoint_uuid'], $fulltimepoint, $childPoint);
    	}else{
    		return $childPoint;
    	}
    }
    
    /**
     * 获取对应虚拟机的时间点uuid列表
     * @param string $vcenteruuid
     * @param string $vmuuid
     */
    private function getVmTimepointuuids($vcenteruuid, $vmuuid){
    	$sql = "select vbt.timepoint_uuid from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? and bbt.copy_flag = ?";
    	$data = $this->dbSelect($sql,array($vcenteruuid, $vmuuid, Xphp::$_config['FLAG']['UNSET']));
    	$timepointuuids = array();
    	foreach ($data as $d){
    		$timepointuuids[] = $d['timepoint_uuid'];
    	}
    	
    	return $timepointuuids;
    	
    }
    
    
    /**
     * 获取对应虚拟机的历史任务存储量
     */
    private function getVmHistoryInfo($taskuuid, $vmuuid, $vcenteruuid){
        $sql = "select details, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time from bd_history_task where task_uuid = ? and module_type = ? and task_type = ? order by finish_time desc limit 0, 1";
        $data = $this->dbSelect($sql, array($taskuuid, Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP']));
        $info = array();
        if(!empty($data)){
            foreach($data as $key => $d){
                $details = json_decode($d['details'], true);
                foreach($details as $detail){
                    if($detail['vm_uuid'] == $vmuuid){
                        if($key == 0){
                            $taskStatus = intval($detail['task_status']);
                            $backupMode = intval($detail['mode']);
                            $execTime = strtotime($detail['end_transfer_time']) - strtotime($detail['start_transfer_time']);
                        }
                    }
                }
            }
            $info = array(
                'vm_status' => $taskStatus,
                'vm_exec_time' => $execTime,
                'task_exec_time' => intval($data[0]['finish_time']) - intval($data[0]['start_time']),
                'backup_type' => $backupMode
            );
        }
            
        return $info;
    }
    
    /**
     * 获取虚拟机最近一条任务uuid任务名
     */
    private function getVMTaskInfo($vcenteruuid, $vmuuid){
        $sql = "select sum(bbt.write_size) as storage_size, bbt.task_name, bbt.task_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt 
            where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? and bbt.module_type = ? order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, array($vcenteruuid, $vmuuid, Xphp::$_config['MODULE_TYPE']['VM']));
        $info = array();
        if(!empty($data)){
            $info =array(
                'task_uuid' => $data[0]['task_uuid'],
                'task_name' => $data[0]['task_name'],
                'storage_size' => intval($data[0]['storage_size'])
            );
        }
        
        return $info;
    }
    
    /**
     * 获取恢复/瞬时恢复虚拟机配置信息
     * @param unknown $params
     * @return string|boolean
     */
    public function getVMConfigInfo($params){
        
        $hypervisor = $params['hypervisor'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $points = $params['points'];
        $pointsDetail = $params['pointsDetail'];
        
        $nodeuuid = $pointsDetail[0]['nodeuuid'];   //从时间点获取节点uuid
        
        $opName = 'VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF';
        $mbMsg = array(
            'target_hypervisor_type' => $hypervisor,
            'recovery_timepoint_uuid_list' => $points,
        );
        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $mbMsg, true);
        if(!$mbResult['result']){
            //获取后台消息失败
            $info = array(
                "flag" => false
            );
            return json_encode($info);
        }
        
        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        $info = $vmRecoveryHandler->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $points, $pointsDetail, $mbResult['msg']);
        $info['flag'] = true;
        $info['instantFlag'] = $params['instantFlag'];  //瞬时恢复标记,瞬时恢复的时候不选择存储
        $info['vmotionFlag'] = false;                   //迁移标记,插件统一处理的,这里补齐这个字段
        
        
        if($params['instantFlag']){
            //如果是瞬时恢复,不显示磁盘置备模式
            $info['control']['disk_setting_mode'] = false;
        }
        
        return $info;
    }
    
    /**
     * 获取迁移虚拟机配置信息
     * @param unknown $params
     * @return string|boolean
     */
    public function getMotionConfigInfo($params){
        
        $hypervisor = intval($params['hypervisor']);
        $taskuuid = $params['taskuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $newName = $params['newname'];
        $sql = "select vm_config, new_vm_name, orig_vm_uuid, orig_vm_name, timepoint_uuid from vm_instant where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $config = json_decode($data[0]['vm_config'], true);
        $points = array($data[0]['timepoint_uuid']);
        $pointsDetail = array(
            array(
                'vmname' => $newName,
                'oldname' => $data[0]['new_vm_name'],
                'config' => array(
                    'password' => "",
                    'password_auto_flag' => 1
                )
            )
        );
        
        
        
        //获取中间统一结构消息
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($points);
        $opName = 'VM_VCENTER_OP_GET_ADVANCE_MIGRATION_CONF';
        
        $mbMsg = array(
            'target_hypervisor_type' => $hypervisor,
            'task_uuid' => $taskuuid,                               //瞬时恢复任务uuid
            'orig_vm_uuids' => array($data[0]['orig_vm_uuid']), //瞬时恢复虚拟机列表,为后续支持多个做准备,这里是一个列表,目前是一个
        );
        
        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $mbMsg, true);
        if(!$mbResult['result']){
            //获取后台消息失败
            $info = array(
                "flag" => false
            );
            return json_encode($info);
        }
        
        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        $info = $vmRecoveryHandler->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $points, $pointsDetail, $mbResult['msg']);
        $info['flag'] = true;
        $info['instantFlag'] = false;  //瞬时恢复标记,这里补齐这个字段
        $info['vmotionFlag'] = true;   //迁移标记,迁移的时候通过这个判断是迁移
        
        
        
        return $info;
    }
    
}
    
?>