<?php
/******************************************* 
** 归档管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-04-10 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
require_once XPHP_PATH.'utils/BLLHandler.class.php';
class ArchiveHandler extends BLLHandler{
     /**
     * 创建副本任务,备份数据到异地
     * @param unknown $params
     */
    public function creatArchivetoCloud($params){
        
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        
        $opName = 'BD_TASK_OP_ARCHIVE_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        $vmHandler = Xphp::instance('Vmhandler');
        $strategy_group_uuid = '';
        $time_strategy_list = $vmHandler->groupBackupTimeList($params['backupInfo'], $strategy_group_uuid);
        $reserver_strategy = $vmHandler->groupReserverStrategy($params['highInfo']['reserve']);
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer']);
		if($params['backupInfo']['type'] == "oncetime"){
			$reserver_strategy = array();
		}
        $nodeInfo = $vmHandler->groupBackupNodeInfo($params['highInfo']['node']);
        $module_type = Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
        $pfMsg = $this->pfCreateBackupTaskMessage($task_name, $module_type,
            $time_strategy_list, $reserver_strategy, $transport_strategy, array(), $nodeInfo); //组合消息
        //$nodeuuid = $this->getCopyNodeuuid($params['highInfo']['node']);
		$nodeuuid = $params['nodeuuid'];
        $storageInfo = $this->getRemoteStorageInfo($params['highInfo']['node']['storageuuid']);
        $vmsInfo = $this->getBackupArchiveVMsInfo($params['srcInfo']['vminfo']);
        
        $pfMsg['node_uuid'] = $nodeuuid;
        $pfMsg['target_repository_uuid'] = $storageInfo['target_repository_uuid'];
        $pfMsg['target_storage_type'] = $storageInfo['target_storage_type'];
        $pfMsg['backup_copy_vms'] = $vmsInfo;
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['ARCHIVE'];
		$pfMsg['real_storage_info'] = $this->getRealStorageInfo($params['highInfo']['node']['realstorageinfo']);
        if(!$nodeuuid){
            //没有节点可用
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_NODE_NOT_FIND_NODE'], 'error');
        }
        
        
        $utils = Xphp::instance('Utils');
        $submodule_type = intval($params['srcInfo']['vmType']);
        $msg = json_encode($pfMsg);
        
        
        $mbResult = $this->mbCopyMsg($nodeuuid, $submodule_type, $opName, $msg);
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
     * 修改归档任务，备份数据到云存储
     * @param unknown $params
     */
    public function editArchiveJob($params){
    	
        $task_name = $params['taskName'];
        $taskuuid = $params['taskuuid'];
        $this->paramsCheck($task_name);
        
        $opName = 'BD_TASK_OP_ARCHIVE_MODIFY';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        $vmHandler = Xphp::instance('Vmhandler');
        $strategy_group_uuid = '';
        $time_strategy_list = $vmHandler->groupBackupTimeList($params['backupInfo'], $strategy_group_uuid);
        $reserver_strategy = $vmHandler->groupReserverStrategy($params['highInfo']['reserve']);
        if($params['backupInfo']['type'] == "oncetime"){
			$reserver_strategy = array();
		}
		$transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer']);
        $nodeInfo = $vmHandler->groupBackupNodeInfo($params['highInfo']['node']);
        $module_type = Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
        
        $pfMsg = $this->pfCreateBackupTaskMessage($task_name, $module_type,
            $time_strategy_list, $reserver_strategy, $transport_strategy, array(), $nodeInfo); //组合消息
        
        
//         $nodeuuid = $this->getCopyNodeuuid($params['highInfo']['node']);
		$nodeuuid = $params['nodeuuid'];
        
        $storageInfo = $this->getRemoteStorageInfo($params['highInfo']['node']['storageuuid']);
        $vmsInfo = $this->getBackupArchiveVMsInfo($params['srcInfo']['vminfo']);
        
        $pfMsg['task_uuid'] = $taskuuid;
        $pfMsg['node_uuid'] = $nodeuuid;
        $pfMsg['target_repository_uuid'] = $storageInfo['target_repository_uuid'];
        $pfMsg['target_storage_type'] = $storageInfo['target_storage_type'];
        $pfMsg['backup_copy_vms'] = $vmsInfo;
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['ARCHIVE'];
        $pfMsg['real_storage_info'] = $this->getRealStorageInfo($params['highInfo']['node']['realstorageinfo']);
        
        if(!$nodeuuid){
            //没有节点可用
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_NODE_NOT_FIND_NODE'], 'error');
        }
        
        
        $utils = Xphp::instance('Utils');
        $submodule_type = intval($params['srcInfo']['vmType']);
        $msg = json_encode($pfMsg);
        
        $mbResult = $this->mbCopyMsg($nodeuuid, $submodule_type, $opName, $msg);
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
     * 创建归档回传任务,从异地恢复数据
     * @param unknown $params
     */
    public function creatArchivefromCloud($params){
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        
        $opName = 'BD_TASK_OP_ARCHIVE_FETCH_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        $vmHandler = Xphp::instance('Vmhandler');
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer']);
        $module_type = Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
        
        $task_type = Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'];
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        
        $vmsInfo = $this->getBackupArchiveVMsInfo($params['vmInfo']['points']);
        
        //组合副本回传消息
        $pfMsg = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'task_type' => $task_type,
            'node_uuid' => $params['storageInfo']['nodeuuid'],
            'target_repository_uuid' => $params['storageInfo']['storageuuid'], //目标存储uuid
            'target_storage_type' => $params['storageInfo']['storagetype'],
            'backup_copy_vms' => $vmsInfo,
            'transport_strategy' => $transport_strategy,
        );
        
        
        $submodule_type = intval($params['vmInfo']['vmType']);
        
        $msg = json_encode($pfMsg);
        
        $mbResult = $this->mbCopyMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
        	$startResult = $this->startArchiveBackJob($task_name);
        	$startResult = true;
        	if($startResult){
        		//启动任务成功,直接返回创建任务成功
        		return $this->muOpResult($result, $operate, $msg);
        	}else {
        		//启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
        		return $this->muOpResult($result, $operate, $msg);
        	}
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startArchiveBackJob($taskName){
    	$sql = "select bt.task_uuid, bt.module_type, bt.task_type, bcil.hypervisor_type
                from bd_task bt, backup_copy_item_list bcil where bt.task_uuid = bcil.task_uuid and bt.task_name = ?
                order by bt.id desc";
    	$data = $this->dbSelect($sql, array($taskName));
    	if(!$data) return false;
    
    	//任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
    	$params = array(
    		'uuid' => $data[0]['task_uuid'],
    		'module' => $data[0]['module_type'],
    		'subModule' => $data[0]['hypervisor_type'],
    		'taskType' => $data[0]['task_type'],
    		'startType' => Xphp::$_config['BACKUP_MODE']['FULL'],
    	);
    	//调用系统统一启动任务接口.不重新写
    	$jobHandler = Xphp::instance('JobHandler');
    	$result = $jobHandler->startJob($params);
    	$result = json_decode($result, true);
    	//这里直接返回成功或失败 bool
    	return $result['re'];
    }
    
    /**
     * 获取异地存储信息,根据存储uuid
     * @param unknown $storageuuid
     */
    private function getRemoteStorageInfo($storageuuid){
        $sql = "select storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $info = array(
            'target_storage_type' => intval($data[0]['storage_type']),
            'target_repository_uuid' => $storageuuid,
        );
        return $info;
    }
    
    /**
     * 得到归档备份任务的虚拟机信息
     * @param unknown $vminfo
     */
    private function getBackupArchiveVMsInfo($vminfo){
        
        $vms = array();
        foreach ($vminfo as $vm){
            $vms[] = array(
                'hypervisor_type' => $vm['hypervisor'],
                'vm_uuid' => $vm['vmuuid'],
                'vcenter_uuid' => $vm['vcuuid'],
				'source_task_uuid' => $vm['taskuuid'],
                'specified_timepoint_uuid' => $vm['timepointuuids'], 
            );
        }
        
        return $vms;
    }
    
    
    /**
     * 得到异地存储信息,用于创建任务消息
     * @param unknown $relStorageInfo
     */
    private function getRealStorageInfo($relStorageInfo){
        $storageInfo = array(
            'real_storage_uuid' => $relStorageInfo['uuid'],
            'real_storage_name' => $relStorageInfo['name'],
            'real_storage_type' => $relStorageInfo['type'],
        	'real_storage_total_size' => $relStorageInfo['total'],
        	'real_storage_free_size' => $relStorageInfo['free']
		);
        return $storageInfo;
    }
    
    //TODO,得到异地列表,暂用,后面根据需要删除或修改
    public function getCloudStorageList($params){
        $sql = "select storage_nickname, storage_uuid, storage_type, total_size, free_size from bd_storage_resource where storage_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']));
        $info = array();
        $utils = Xphp::instance("Utils");
        $storageHandler = Xphp::instance('StorageHandler');
        foreach ($data as $d){
            $info[] = array(
                'value' => $d['storage_uuid'],
                'text' => $d['storage_nickname'] . "(" .   $storageHandler->getStorageTypeDes(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']) . ")",
				'name' => $d['storage_nickname'],
				'type' => intval($d['storage_type'])
			);
        }
        return json_encode($info);
    }
    
    
    
    /**
     * 得到副本任务 当前任务的虚拟机树
     * @param unknown $params
     */
    public function getTaskVmTree($params){
    	$nodeuuid = $params['nodeuuid'];
    	$sql = "select bt.task_uuid, bt.task_name, unix_timestamp(bt.create_time) create_time, vml.vcenter_uuid, vml.vm_uuid, vml.vm_name, vml.dir_path, vt.hypervisor_type  
    			from bd_task bt, vm_machine_list vml, vm_task vt where bt.task_uuid = vml.task_uuid and vt.task_uuid = bt.task_uuid and bt.module_type = ? and bt.task_type = ? and bt.delete_flag = ? and bt.node_uuid = ? ";
    	$data = $this->dbSelect($sql,array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['FLAG']['UNSET'], $nodeuuid));
    	$task = array();
    	$vm = array();
    	$node = array();
    	if(!empty($data)){
    		foreach ($data as $d){
    			$taskuuid = $d['task_uuid'];
    			$vmuuid = $d['vm_uuid'];
    			//检查并添加task
    			if(!in_array($taskuuid, $task)){
    				$task[] = $taskuuid;
    				$node[] = array(
    						"id" => $taskuuid,
    						"pId" => 0,
    						"name" => $d['task_name'],
    						"open" => false,
    						"nocheck" => true,
    						"type" => 0,
    						"icon" => './img/platform/flag.png',
    						"title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['create_time']),
    						"vcenteruuid" => $d['vcenter_uuid'],
    						"hypervisor" => $d['hypervisor_type'],
    						"isParent" => true,
    						'clickshow' => true
    				);
    			}
    		
    		}
    	}
    	
    	return json_encode($node); 
    	
    	
    }
    
    /**
     * 异步获取每个备份任务的虚拟机
     * @param unknown $params
     */
    public function getTaskVmDetails($params){
		$taskuuid = $params['taskuuid'];
    	$sql = "select bt.node_uuid, bt.task_name, unix_timestamp(bt.create_time) create_time, vml.vcenter_uuid, vml.vm_uuid, vml.vm_name, vml.dir_path, vt.hypervisor_type
    			from bd_task bt, vm_machine_list vml, vm_task vt where bt.task_uuid = vml.task_uuid and vt.task_uuid = bt.task_uuid and bt.module_type = ? and bt.task_type = ? and bt.delete_flag = ? and bt.task_uuid = ?";
    	$data = $this->dbSelect($sql,array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['FLAG']['UNSET'], $taskuuid));
    	$vm = array();
    	$node = array();
    	foreach ($data as $d){
    		//检查并添加vm
    		$vmuuid = $d['vm_uuid'];
    		if(!in_array($vmuuid . $taskuuid, $vm)){
    			$vm[] = $vmuuid . $taskuuid;
    			$node[] = array(
    					"id" => $vmuuid . $taskuuid,
    					"pId" => $taskuuid,
    					"name" => $d['vm_name'],
    					"open" => false,
						"nocheck" => false,
    					"type" => 1,
    					"eventtype" => 'vm',
    					"icon" => './img/vm/vm.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"vmuuid" => $d['vm_uuid'],
    					"taskuuid" => $taskuuid,
    					"createtime" => $this->parseDate($d['create_time']),
    					"hypervisor" => $d['hypervisor_type'],
    					"path" => $d['dir_path'],
    					"nodeuuid" => $d['node_uuid']
    			);
    		}else {
    			continue;
    		}
    	}
    	
    	$msg = array(
    		're' => true,
    		'msg' => $node,
    	);
    	return json_encode($msg);
    }
    
    /**
     * 获取创建副本任务的虚拟化中心
     * @param unknown $params
     */
    public function getBackupSyncVcenter($params){
		$nodeuuid = $params['nodeuuid'];
    	$uuid = $params['id'];            //vcenter uuid
    	$vcflag = intval($params['vcflag']);    //vcenter 标志
    	$hypervisor = intval($params['hypervisor']);
    	$showtype = intval($params['showtype']);
    	$openFlag = $params['open'];
    	$refresh = $params['refresh'];          //是否刷新
    	$modifyFlag = $params['modifyflag'];    //是否是修改任务
    	$Vcenter = Xphp::instance('Vcenter');
    	
    	$vcenterFlag = $vcenterFlag == Xphp::$_config['FLAG']['SET'];
        $sql = "select tree_id, type, name, uuid, parent_uuid, dir_path, conn_state, power_state, 
                host_uuid, vcenter_uuid, version, detail  
                from vm_tree where vcenter_uuid = ? and display_mode = ? order by type";
        if(in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
            //VMware按照中文排序,保持和vcenter显示顺序一致
            $sql .= ", convert(name USING gbk) COLLATE gbk_chinese_ci";
        }else{
            $sql .= ", name";
        }
        $sqlParams = array($uuid, $showtype);
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        $vmInfo = $this->getBackupVMInfo();
        $lisenceHosts = $Vcenter->getAllLisenceHostUUID(); //得到所有授权的宿主机uuid列表
        $showVMFlag = true;
        foreach ($data as $d){
            if(!$vcflag){
                if(intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST']){
                    //如果是宿主机,不再显示宿主机层
                    continue;
                }
            } 
            $vmFlag = intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['VM'];
            if($vmFlag && !$showVMFlag) continue;   //如果是虚拟机,而且不显示虚拟机
            $name = $Vcenter->getVMTreeName($d['type'], $d['name']);
            $name = $Vcenter->getOffLineStatusDes($d['conn_state'], $name);
            $name = $Vcenter->getVMNameWithLisence($lisenceHosts, $d['vcenter_uuid'], $d['host_uuid'], $name, $d['type']);
           	$detail = json_decode($d['detail']['annotation']);
            $title = $Vcenter->getBackupTreeNodeTitle($d['type'], $name, $d['uuid'], $d['vcenter_uuid'], $vmInfo, $detail);
            $inBackupFlag = $Vcenter->getVMInBackupFlag($d['type'], $d['uuid'], $d['vcenter_uuid'], $vmInfo);
			foreach ($vmInfo['vmuuid'] as $key => $eachUUID){
				if($d['uuid'] == $eachUUID){
					//如果不在选中的节点上不可选
					if(!empty($nodeuuid)){
						if($nodeuuid != $vmInfo['nodeuuid'][$key]){
							$inBackupFlag = false;
						}
					}else{
						$nodeuuid = $vmInfo['nodeuuid'][$key];
					}
				}
			}
            $taskuuid = '';
            if($inBackupFlag){
            	$taskuuid = $this->getVmTaskuuid($d['uuid']);
            }
            $node[] = array(
                'id' => $d['uuid'],
                'pid' => $d['parent_uuid'],
                'name' => $name,
                'hostuuid' => $d['host_uuid'],
                'vcenteruuid' => $uuid,
                'hypervisor' => $hypervisor,
                'icon' => $Vcenter->getTreeTypeIcon($d['type'], $d['conn_state'], $d['power_state']),
                "nocheck" => !$vmFlag,
                "path" => $d['dir_path'],
                "eventtype" => $vmFlag ? 'vm' : '',
                "version" =>$d['version'],
                "inbackup" => $inBackupFlag,
                "title" => $title,
                "chkDisabled" => !$inBackupFlag,
                "open" => $openFlag,
                "detail" => json_decode($d['detail']),
            	"online" => $Vcenter->getVMHostOnlineFlag($d['conn_state']),
            	"diskChecked" => "",
            	"taskuuid" => $taskuuid,
				"nodeuuid" => $nodeuuid
            );
        }
        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }
    
    
    /**
     * 获取虚拟机任务uuid
     * @param string $uuid
     */
    public function getVmTaskuuid($uuid){
    	$sql = "select task_uuid from vm_machine_list where vm_uuid = ?";
    	$data = $this->dbSelect($sql, array($uuid));
    	return $data[0]['task_uuid'];
    }
    
    /**
     * 获取备份数据任务树
     * @param unknown $params
     */
    public function getStorageVmTree($params){
		$nodeuuid = $params['nodeuuid'];
    	$sql = "select bbt.task_name,unix_timestamp(bbt.task_create_time) task_create_time, bbt.data_local_flag,
    			bbt.task_uuid,vbt.vcenter_uuid, vbt.hypervisor_type, bsr.node_uuid,bsr.storage_nickname,bsr.storage_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and
                       bbt.user_uuid = ? and bsr.node_uuid = ? order by  vbt.vm_uuid,  vbt.vm_timepoint_id desc, bbt.timepoint desc";
    	$flag = Xphp::$_config['FLAG'];
    	$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['VM'],
    			Xphp::$_user['useruuid'], $nodeuuid);
    	$data = $this->dbSelect($sql,$sqlParams);
    	
    	$storage = array();
    	$task = array();
    	$node = array();
    	$pid = null;
    	$vmHandler = Xphp::instance('Vmhandler');
    	$jobHandler = Xphp::instance('JobHandler');
    	$currentTaskUUID = $vmHandler->getCurrentAllTaskUUID();
    	foreach ($data as $d){
    		$storageuuid = $d['storage_uuid'];
    		$taskuuid = $d['task_uuid'];
    		$taskCreateTime = $d['task_create_time'];
    		//检查并添加虚拟化类型
    		if(!in_array($storageuuid, $storage)){
    			$node[] = array(
    					"id" => $storageuuid,
    					"pId" => 0,
    					"name" => $d['storage_nickname'],
    					"title" => $d['storage_nickname'],
    					"open" => true,
    					"nocheck" => true,
    					"type" => -1,
    					"icon" => './img/platform/flag.png',
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"hypervisor" => $d['hypervisor_type'],
    			);
    			$storage[] = $storageuuid;
    		}
    		 
    		 
    		$taskName = $jobHandler->getTimepointTaskname($d['task_uuid'],$d['task_name']);
    		//检查并添加task
    		if(!in_array($taskuuid, $task)){
    			$task[] = $taskuuid;
				$taskAvailable = in_array($taskuuid, $currentTaskUUID);
				if(intval($d['data_local_flag']) == Xphp::$_config['FLAG']['UNSET']){
					$taskAvailable = true;
				}
    			$node[] = array(
    					"id" => $taskuuid,
    					"pId" => $storageuuid,
    			        "name" => $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")",
    					"open" => false,
    					"nocheck" => true,
    					"type" => 0,
    					"icon" => './img/platform/flag.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"hypervisor" => $d['hypervisor_type'],
    					"isParent" => true,
    					"clickshow" => true
    			);
    		}
    	}
    	return json_encode($node);
    }
    
    /**
     * 异步获取备份虚拟机和时间点树
     * @param unknown $params
     */
    public function getSyncVmTimepoint($params){
    	$taskuuid = $params['id'];
    	$hypervisor = $params['hypervisor'];
    	$vmHandler = Xphp::instance('Vmhandler');
    	$sql = "select bsr.node_uuid, vbt.vcenter_uuid,vbt.vm_uuid, vbt.vm_name, vbt.dir_path, unix_timestamp(bbt.task_create_time) task_create_time  from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where bbt.timepoint_uuid = vbt.timepoint_uuid
    			and bbt.storage_uuid = bsr.storage_uuid and bbt.task_uuid = ? and vbt.hypervisor_type = ?";
    	$data = $this->dbSelect($sql,array($taskuuid, $hypervisor));
    	$node = array();
    	$vm = array();
    	$timepoint = array();
    	foreach ($data as $d){
    		$vmuuid = $d['vm_uuid'];
    		//检查并添加vm
    		if(!in_array($vmuuid . $taskuuid, $vm)){
    			$vm[] = $vmuuid . $taskuuid;
    			$node[] = array(
    					"id" => $vmuuid . $taskuuid,
    					"pId" => $taskuuid,
    					"name" => $d['vm_name'],
    					"open" => false,
    					"nocheck" => false,
    					"type" => 1,
    					"icon" => './img/vm/vm.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"vmuuid" => $d['vm_uuid'],
    					"taskuuid" => $taskuuid,
    					"createtime" => $this->parseDate($d['task_create_time']),
    					"hypervisor" => $hypervisor,
    					"path" => $d['dir_path'],
    					"eventtype" => 'vm',
    					"isParent" => true,
    					"nodeuuid" => $d['node_uuid']
    			);
    		}else {
                continue;
            }
    		 
    		 
    		$sqlPoint = "select bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
                       unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and
                       bbt.task_uuid = ?  and
                       vbt.vm_uuid = ? order by vbt.vm_uuid,  bbt.timepoint";
    		 
    		$flag = Xphp::$_config['FLAG'];
    		$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['VM'],$taskuuid,  $vmuuid);
    		 
    		$pointData = $this->dbSelect($sqlPoint, $sqlParams);
    		$pid =  $vmuuid . $taskuuid;
    		foreach ($pointData as $point){
    			$taskuuid = $point['task_uuid'];
    			$vmuuid = $point['vm_uuid'];
    			$timepointuuid = $point['timepoint_uuid'];
    			$taskCreateTimeIn = $this->parseDate($point['task_create_time']);
    			//检查并添加完备点
    			if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
    				if(!in_array($timepointuuid, $timepoint)){
    					$timepoint[] =  $timepointuuid;
    					$node[] = array(
    							"id" =>  $timepointuuid,
    							"pId" =>  $vmuuid . $taskuuid,
    							"name" => $this->parseDate($point['timepoint']) . "(" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
    							"checked" => false,
    							"type" => 3,
    							"vmuuid" => $point['vm_uuid'],
    							"vmname" => $point['vm_name'],
    							"pointname" => $this->parseDate($point['timepoint']),
    							"vcenteruuid" => $point['vcenter_uuid'],
    							"timepointuuid" => $timepointuuid,
    							"createtime" => $taskCreateTimeIn,
    							"hypervisor" => $point['hypervisor_type'],
    							"nodeuuid" => $point['node_uuid'],
    							"taskuuid" => $taskuuid,
    							"path" => $point['dir_path'],
    							"version" => $point['version'],
    							"icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
    							"title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
    							"hypervisor" => $hypervisor,
    							"chkDisabled" => false,
    							"eventtype" => "point"
    					);
    					//添加了完全备份时间点继续下一次
    					$pid = $timepointuuid;
    					continue;
    				}
    			}
    			 
    			 
    			$node[] = array(
    					"id" => $timepointuuid,
    					"pId" => $pid,
    					"name" => $this->parseDate($point['timepoint']) . "(" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
    					"checked" => false,
    					"type" => 4,
    					"vmuuid" => $point['vm_uuid'],
    					"vmname" => $point['vm_name'],
    					"pointname" => $this->parseDate($point['timepoint']),
    					"vcenteruuid" => $point['vcenter_uuid'],
    					"nodeuuid" => $point['node_uuid'],
    					"timepointuuid" => $timepointuuid,
    					"hypervisor" => $point['hypervisor_type'],
    					"path" => $point['dir_path'],
    					"version" => $point['version'],
    					"icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
    					"title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
    					"chkDisabled" => false,
    					"eventtype" => "point",
    					"taskuuid" => $taskuuid,
    					'dependuuid' => $point['depend_point_uuid']
    			);
    			 
    			 
    		}
    	}
    	
    	$msg = array(
    		're' => true,
    		'msg' => $node,
    	);
    	return json_encode($msg);
    }
    
    /**
     * 获取虚拟机归档任务信息(修改任务用)
     */
    public function getArchiveTaskAllInfo($params){
    	$taskUUID = $params['taskuuid'];
    	$this->paramsCheck($taskUUID);
    	$sql = "select bt.task_name, bt.strategy_id, bt.storage_uuid,
                       bct.real_storage_uuid, bct.real_storage_type, bct.real_storage_name, bct.real_storage_total_size, bct.real_storage_free_size, bcil.target_storage_type,
                	   bts.encrypt_flag, bts.compress_flag
                from bd_task bt, backup_copy_item_list bcil, backup_copy_task bct, bd_transport_strategy bts
                where bt.task_uuid = bct.task_uuid
    			and bt.task_uuid = bcil.task_uuid
                and bt.strategy_id = bts.strategy_id
                and bt.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskUUID));
    	$info = array();
    	$utils = Xphp::instance('Utils');
    	$vmHanlder = Xphp::instance('Vmhandler');
    	if($data){
    		$info = array(
    				//任务UUID
    				'taskuuid' => $taskUUID,
    				//任务名
    				'taskname' => $data[0]['task_name'],
    				//保留策略
    				'brs' => $this->getArchiveReserve($data[0]['strategy_id']),
    				//节点
    				'node' => array(
    						'storageuuid' => $data[0]['storage_uuid'],
    						'realstorageinfo' => $this->groupRealStorageInfo($data[0]['real_storage_uuid'],$data[0]['real_storage_name'],$data[0]['real_storage_type'], intval($data[0]['real_storage_total_size']), intval($data[0]['real_storage_free_size'])),
    						'type' => $this->getArchiveStorageType(intval($data[0]['target_storage_type']))
    				),
    				//传输策略
    				'bts' => array(
    						'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
    						'compress' => $utils->parseFlagToBool($data[0]['compress_flag']),
    				),
    				//虚拟机信息
    				'vm_info' => $this->getArchiveEditInfo($taskUUID),
    				//时间策略
    				'timestrategy' => $vmHanlder->getTimeStrategyInfo($data[0]['strategy_id']),
    		);
    	}
    	return json_encode($info);
    }
    
    /**
     * 得到归档修改的虚拟机信息
     * @param unknown $params
     * @return string
     */
    public function getArchiveEditInfo($taskuuid){
    	$info = array();
    	$sql =  "select node_uuid, item_uuid, vcenter_uuid, item_name, source_task_uuid, specified_timepoint_list, hypervisor_type from backup_copy_item_list where task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	foreach ($data as $d){
    		$timepointuuids = array();
    		if(!empty($d['specified_timepoint_list'])){
    			$timepointuuids = json_decode($d['specified_timepoint_list']);
    		}
    		$info[] = array(
    				"vmuuid" => $d['item_uuid'],
    				"vcuuid" => $d['vcenter_uuid'],
    				"vmname" => $d['item_name'],
    				"taskuuid" => $d['source_task_uuid'],
    				"timepointuuids" => $timepointuuids,
    				"hypervisor" => $d['hypervisor_type'],
    				"path" => $this->getDirpath($d['vm_uuid'],$taskuuid),
    		        "nodeuuid" => $d['node_uuid']
    		);
    	}
    	 
    	return $info;
    }
    
    /**
     * 获取虚拟机根路径
     * @param string $vmuuid
     * @param string $vcenteruuid
     */
    public function getDirpath($vmuuid,$taskuuid){
    	$sql ="select dir_path from backup_copy_item_list where item_uuid =? and task_uuid =?";
    	$data = $this->dbSelect($sql,array($vmuuid,$taskuuid));
    	return $data[0]['dir_path'];
    } 
    
    /**
     * 组合副本任务所需真正的存储信息
     * @param string $uuid
     * @param string $name
     * @param int $type
	 * @param int $total
	 * @param int $free
     * @return $storageInfo
     */
    private function groupRealStorageInfo($uuid,$name,$type, $total, $free){
    	$storageInfo = array(
    			'uuid' => $uuid,
    			'name' => $name,
				'type' => $type,
				'total' => $total,
				'free' => $free
    	);
    	return $storageInfo;
    }
  	
    /**
     * 获取归档任务存储类型 1本地 2云存储
     * @param int $storageType
     * @return $type
     */
    private function getArchiveStorageType($storageType){
    	$type = 1;  //本地存储
    	if($storageType == Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']){
    		$type = 2;//云存储
    	}
    	return $type;
    }
    
    /**
     * 获取副本任务原来选择的虚拟机信息
     * @param unknown $params
     */
    public function getCopyTreeOldInfo($params){
    	$timepointuuids = $params['timepointuuids'];
    	$sourcetaskuuid = $params['source_task_uuid'];
    	$vmInfo = $params['vmInfo'];
    	$taskuuid = $params['taskuuid'];
    	if($sourcetaskuuid && empty($timepointuuids)){
    		$msg = $this->getTaskTreeOldInfo($taskuuid, $vmInfo);
    	}else if(!empty($timepointuuids)){
    		$msg = $this->getStorageTreeOldInfo($taskuuid, $vmInfo);
    	}else{
    		$msg = $this->getVcenterTreeOldInfo($vmInfo);
    	}
    	return json_encode($msg);
    }
    
    /**
     * 获取修改副本任务虚拟机树(按备份任务展示)
     * @param string $taskuuid
     * @param array $vmInfo
     */
    public function getTaskTreeOldInfo($taskuuid, $vmInfo){
    	$utils = Xphp::instance('Utils');
    	$sysvmTree = array();
    	$taskuuids = array();
    	$results = array();
    	$nodeuuid = "";
    	foreach ($vmInfo as $vm){
    		$taskuuid = $vm['taskuuid'];
    		$nodeuuid = $vm['nodeuuid'];
    		if(!in_array($taskuuid, $taskuuids)){
    			$syncParams = array(
    				'taskuuid' => $taskuuid
    			);
    			$syncnode = $this->getTaskVmDetails($syncParams);
    			
    			$syncnode = $utils->object_array(json_decode($syncnode));
    			if($syncnode['re'] && !empty($syncnode['msg'])){
    				$sysvmTree[] = array(
    						'node' => $syncnode['msg'],
    						'taskuuid' => $taskuuid
    				);
    				$nodeuuid = $syncnode['msg'][0]['nodeuuid'];
    			}
    			$taskuuids[] = $taskuuid;
    		}
    	}
    	$treeParams = array(
    		'nodeuuid' => $nodeuuid
    	);
    	$topNode = $this->getTaskVmTree($treeParams);
    	$topNode = $utils->object_array(json_decode($topNode));
    	if(!empty($sysvmTree)){
    	    foreach ($sysvmTree as $key1 => $tree){
    	        $node = $tree['node'];
    	        foreach ($topNode as $key => $t){
    	            if($t['id'] == $tree['taskuuid']){
    	                $topNode[$key]['open'] = true;
    	            }
    	        }
    	        foreach ($node as $key2 => $n){
    	            foreach ($vmInfo as $vm){
    	                if($n['vmuuid'].$n['vcenteruuid'] == $vm['vmuuid'].$vm['vcuuid']){
    	                    $node[$key2]['checked'] = true;
    	                    // $node[$key2]['chkDisabled'] = true;
    	                    
    	                    $n['checked'] = true;
    	                    // $n['chkDisabled'] = true;
    	                }
    	            }
    	            $topNode[] = $n;
    	        }
    	        
    	    }
    	    
    	}
    	$msg = array(
    		'node' => json_encode($topNode),
    		'nodeuuid' => $nodeuuid,
    	);
    	
    	return $msg;
    }
    
    /**
     * 获取修改副本任务虚拟机树(按备份数据展示)
     * @param string $taskuuid
     * @param array $vmInfo
     */
    public function getStorageTreeOldInfo($taskuuid, $vmInfo){
    	$utils = Xphp::instance('Utils');
    	$sysvmTree = array();
    	$taskuuids = array();
    	$timepointuuids = array();
    	$nodeuuid = "";
    	foreach ($vmInfo as $vm){
    		$taskuuid = $vm['taskuuid'];
    		if(!in_array($taskuuid, $taskuuids)){
    			$syncParams = array(
    					'id' => $taskuuid,
    					'hypervisor' => $vm['hypervisor']
    			);
    			$syncnode = $this->getSyncVmTimepoint($syncParams);
    			$syncnode = $utils->object_array(json_decode($syncnode));
    			if($syncnode['re']){
    				$sysvmTree[] = array(
    						'node' => $syncnode['msg'],
    						'taskuuid' => $taskuuid
    				);
    				$nodeuuid = $syncnode['msg'][0]['nodeuuid'];
    			}
    			$taskuuids[] = $taskuuid;
    			$timepointuuids = array_merge($timepointuuids, $vm['timepointuuids']);
    		}
    	}
    	$treeParams = array(
    		'nodeuuid'=> $nodeuuid
    	);
    	$topNode = $this->getStorageVmTree($treeParams);
    	$topNode = $utils->object_array(json_decode($topNode));
    	
    	foreach ($sysvmTree as $tree){
    		$node = $tree['node'];
    		foreach ($topNode as $key => $t){
    			
    			if($t['id'] == $tree['taskuuid']){
    				$topNode[$key]['open'] = true;
    			}
    		}
    		foreach ($node as $n){
    			foreach ($vmInfo as $vm){
    				if($n['eventtype'] == "vm" && $n['vmuuid'].$n['vcenteruuid'] == $vm['vmuuid'].$vm['vcuuid']){
    					$n['checked'] = true;
    					// $n['chkDisabled'] = true;
    					$n['open'] = true;
    				}
    				
    			}
    			foreach ($timepointuuids as $uuid){
    				if($n['eventtype'] == "point" && $n['id'] == $uuid){
    					$n['checked'] = true;
    					// $n['chkDisabled'] = true;
    					$n['open'] = true;
    				}
    			}
    			$topNode[] = $n;
    		}
    	
    	}
    	
    	$msg = array(
    		'node' => json_encode($topNode),
    		'nodeuuid' => $nodeuuid
    	);
    	
    	return $msg;
    	
    }
    
    /**
     * 获取修改副本任务虚拟机树(按虚拟化中心展示)
     * @param string $taskuuid
     * @param array $vmInfo
     */
    public function getVcenterTreeOldInfo($taskuuid, $vmInfo){
    	$treeParams = array(
    		'type' => 1,
    		'vcuuid' => $vmInfo[0]['vcuuid']
    	);
    	$utils = Xphp::instance('Utils');
    	$vcenter = Xphp::instance('Vcenter');
    	$topNode = $vcenter->getVcenterDetailsTree($treeParams);
    	$topNode = $utils->object_array(json_decode($topNode));
    	$sysvmTree = array();
    	$vcenteruuids = array();
    	foreach ($vmInfo as $vm){
    		$vcenteruuid = $vm['vcuuid'];
    		if(!in_array($vcenteruuid, $vcenteruuids)){
    			$syncParams = array(
    					'id' => $vcenteruuid,
    					'vcflag' => 1,
    					'hypervisor' => $vm['hypervisor'],
    					'open' => true,
    					'showtype' => 1,
    					'modifyflag' => true
    			);
    			$syncnode = $this->getBackupSyncVcenter($syncParams);
    			
    			$syncnode = $utils->object_array(json_decode($syncnode));
    			if($syncnode['re']){
    				$sysvmTree[] = array(
    						'node' => $syncnode['msg'],
    						'vcenter_uuid' => $vcenteruuid
    				);
    			}
    			$vcenteruuids[] = $vcenteruuid;
    		}
    	}
    	
    	foreach ($sysvmTree as $tree){
    		$node = $tree['node'];
    		foreach ($topNode as $key => $t){
    			if($t['id'] == $tree['vcenter_uuid']){
    				$topNode[$key]['open'] = true;
    			}
    		}
    		foreach ($node as $n){
    			foreach ($vmInfo as $vm){
    				if($n['vmuuid'].$n['vcenteruuid'] == $vm['vmuuid'].$vm['vcuuid']){
    					$n['checked'] = true;
    					// $n['chkDisabled'] = true;
    				}
    			}
    			$topNode[] = $n;
    		}
    		 
    	}
    	
    	$msg = array(
    		'node' => json_encode($topNode),
    		'nodeuuid' => ''
    	);
    	
    	return $topNode;
    }
    
    /**
     * 获取副本数据存储列表
     * @param unknown $params
     */
    public function getArchiveStorage($params){
    	$sql = "select storage_nickname, storage_uuid, storage_type, storage_config from bd_storage_resource where use_mode = ?
    			and status = ? and mount_flag = ? 
                and error_code = ? and lan_free_flag = ? ";
    	$sqlParams = array(Xphp::$_config['BD_STORAGE_USE_MODE']['ARCHIVE'],Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET']);
    	
    	$data = $this->dbSelect($sql, $sqlParams);
    	$info = array();
		$utils = Xphp::instance("Utils");
		if(!empty($data)){
			foreach ($data as $d){
				$text = $d['storage_nickname'];
				$info[] = array(
					'uuid' => $d['storage_uuid'],
					'text' => $text,
					'name' => $d['storage_nickname'],
					'type' => intval($d['storage_type'])
				);
			}
		}
        
        return json_encode($info);
    
    }
    
    /**
     *获取归档任务树 
     * @param unknown $params
     */
    public function getArchiveTaskTree($params){
    	$storageuuid = $params['storageuuid'];
    	$type = $params['showtype'];
    	$sql = "select bbt.task_name,unix_timestamp(bbt.task_create_time) task_create_time, bbt.data_local_flag,
    			bbt.task_uuid,vbt.vcenter_uuid, vbt.hypervisor_type, bsr.storage_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where  bbt.timepoint_uuid = vbt.timepoint_uuid and
    				   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") and
                       bbt.user_uuid = ? ";
    	$flag = Xphp::$_config['FLAG'];
    	$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],
    			Xphp::$_user['useruuid']);
    	if($storageuuid && $storageuuid != "0"){
    		$sql.= " and bbt.storage_uuid = ?";
    		array_push($sqlParams, $storageuuid);
    	}
    	$data = $this->dbSelect($sql, $sqlParams);
    	$task = array();
    	$vm = array();
    	
    	//得到当前任务所有副本任务uuid
    	$currentTaskUUID = $this->getAllArchiveTaskUUID();
    	
    	foreach ($data as $d){
    		$taskuuid = $d['task_uuid'];
    		$vmuuid = $d['vm_uuid'];
    		if($type ==  2){
    			$sqlChek = "select timepoint_uuid from bd_backup_timepoint where task_uuid = ?";
    			$dataCheck = $this->dbSelect($sqlChek,array($taskuuid));
    			if(empty($dataCheck)) continue;
    		}
    		//检查并添加task
    		if(!in_array($taskuuid, $task)){
				$taskAvailable = in_array($taskuuid, $currentTaskUUID);
				if(intval($d['data_local_flag']) == Xphp::$_config['FLAG']['UNSET']){
					$taskAvailable = true;
				}
    			$task[] = $taskuuid;
    			$node[] = array(
    					"id" => $taskuuid,
    					"pId" => 0,
    					"name" => $taskAvailable ? $d['task_name'] : $d['task_name'] . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")",
    					"open" => false,
    					"nocheck" => true,
    					"type" => 0,
    					"icon" => './img/platform/flag.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"hypervisor" => $d['hypervisor_type'],
    					"isParent" => true,
    					'clickshow' => true
    			);
    		}
    	
    	}
    	 
    	return json_encode($node);
    }
    
    /**
     * 获取副本任务虚拟机树
     * @param unknown $params
     */
    public function getArchiveTaskVmTree($params){
    	$taskuuid = $params['taskuuid'];
    	$sql = "select bsr.node_uuid, bbt.import_flag, vbt.vcenter_uuid,vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.hypervisor_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_name  from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.task_uuid = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].")";
    	$data = $this->dbSelect($sql,array($taskuuid));
    	$vm = array();
    	$vcenter = array();
    	$node = array();
    	foreach ($data as $d){
    		//检查并添加vcenter
    		$vcenteruuid = $d['vcenter_uuid'];
    		$vmuuid = $d['vm_uuid'];
    		if(!in_array( $vcenteruuid . $taskuuid, $vcenter)){
    			$vcenterInfo = $this->getVcenterInfo($vcenteruuid, $d['hypervisor_type'], $d['dir_path']);
    			$vcenter[] = $vcenteruuid . $taskuuid;
    			$node[] = array(
    					"id" => $vcenteruuid . $taskuuid,
    					"pId" => $taskuuid,
    					"name" => $vcenterInfo['name']."(".Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor_type']].")",
    					"open" => true,
    					"nocheck" => true,
    					"type" => 1,
    					"icon" => $vcenterInfo['icon'],
    					"title" => $vcenterInfo['name']."(".Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor_type']].")",
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"vmuuid" => $d['vm_uuid'],
    					"taskuuid" => $taskuuid,
    					"hypervisor" => $d['hypervisor_type'],
    					"nodeuuid" => $d['node_uuid']
    			);
    		}
    		
    		if(!in_array($vmuuid . $vcenteruuid, $vm)){
    			$vm[] = $vmuuid . $vcenteruuid;
    			$path = $d['dir_path'];
    			$node[] = array(
    					"id" => $vmuuid . $vcenteruuid,
    					"pId" => $vcenteruuid . $taskuuid,
    					"name" => $d['vm_name'],
    					"open" => false,
    					"nocheck" => false,
    					"type" => 2,
    					"eventtype" => 'vm',
    					"icon" => './img/vm/vm.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $path,
    					"vcenteruuid" => $vcenteruuid,
    					"vmuuid" => $vmuuid,
    					"taskuuid" => $taskuuid,
    					"createtime" => $this->parseDate($d['task_create_time']),
    					"taskname" => $d['task_name'],
    					"hypervisor" => $d['hypervisor_type'],
    					"path" => $path,
    					"nodeuuid" => $d['node_uuid']
    			);
    		}else {
    			continue;
    		}
    	}
    	 
    	$msg = array(
    			're' => true,
    			'msg' => $node,
    	);
    	return json_encode($msg);
    }
    
    /**
     * 获取归档任务虚拟机时间点树
     * @param unknown $params
     */
    public function getArchivePointVmTree($params){
    	$taskuuid = $params['taskuuid'];
    	$sql = "select vbt.vcenter_uuid,vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.hypervisor_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_name  from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") ";
    	$data = $this->dbSelect($sql,array($taskuuid));
    	$vm = array();
    	$node = array();
    	$timepoint = array();
    	$pid = null;
    	$vmHandler = Xphp::instance('Vmhandler');
    	foreach ($data as $d){
    		//检查并添加虚拟机
    		$vmuuid = $d['vm_uuid'];
    		if(!in_array($vmuuid . $taskuuid, $vm)){
    			$vm[] = $vmuuid . $taskuuid;
    			$node[] = array(
    					"id" => $vmuuid . $taskuuid,
    					"pId" => $taskuuid,
    					"name" => $d['vm_name'],
    					"open" => false,
    					"nocheck" => false,
    					"type" => 2,
    					"eventtype" => 'vm',
    					"icon" => './img/vm/vm.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"vmuuid" => $vmuuid,
    					"taskuuid" => $taskuuid,
    					"createtime" => $this->parseDate($d['task_create_time']),
    					"taskname" => $d['task_name'],
    					"hypervisor" => $d['hypervisor_type'],
    					"path" => $this->getDirpath($vmuuid, $taskuuid),
    			);
    		}else {
    			continue;
    		}
    		$sqlPoint = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.task_name, bbt.depend_point_uuid, bbt.backup_mode,
                       unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.storage_uuid, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and
                       bbt.task_uuid = ?  and
                       vbt.vm_uuid = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") order by vbt.vm_uuid,  bbt.timepoint";
    		 
    		$flag = Xphp::$_config['FLAG'];
    		$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],$taskuuid,  $vmuuid);
    		 
    		$pointData = $this->dbSelect($sqlPoint, $sqlParams);
    		$pid =  $vmuuid . $taskuuid;
    		foreach ($pointData as $point){
    			$taskuuid = $point['task_uuid'];
    			$vmuuid = $point['vm_uuid'];
    			$timepointuuid = $point['timepoint_uuid'];
    			$taskCreateTimeIn = $this->parseDate($point['task_create_time']);
    			//检查并添加完备点
    			if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
    				if(!in_array($timepointuuid, $timepoint)){
    					$timepoint[] =  $timepointuuid;
    					$node[] = array(
    							"id" =>  $timepointuuid,
    							"pId" =>  $vmuuid . $taskuuid,
    							"name" => $this->parseDate($point['timepoint']) . "(" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
    							"checked" => false,
    							"type" => 3,
    							"vmuuid" => $point['vm_uuid'],
    							"vmname" => $point['vm_name'],
    							"pointname" => $this->parseDate($point['timepoint']),
    							"vcenteruuid" => $point['vcenter_uuid'],
    							"timepointuuid" => $timepointuuid,
    							"createtime" => $taskCreateTimeIn,
    							"hypervisor" => $point['hypervisor_type'],
    							"storageuuid" => $point['storage_uuid'],
    							"nodeuuid" => $point['node_uuid'],
    							"taskuuid" => $taskuuid,
    							"path" => $point['dir_path'],
    							"version" => $point['version'],
    							"icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
    							"title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
    							"eventtype" => 'point',
    							'dependuuid' => $point['depend_point_uuid']
    					);
    					//添加了完全备份时间点继续下一次
    					$pid = $timepointuuid;
    					continue;
    				}
    			}
    		
    		
    			$node[] = array(
    					"id" => $timepointuuid,
    					"pId" => $pid,
    					"name" => $this->parseDate($point['timepoint']) . "(" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
    					"checked" => false,
    					"type" => 4,
    					"vmuuid" => $point['vm_uuid'],
    					"vmname" => $point['vm_name'],
    					"pointname" => $this->parseDate($point['timepoint']),
    					"vcenteruuid" => $point['vcenter_uuid'],
    					"storageuuid" => $point['storage_uuid'],
    					"nodeuuid" => $point['node_uuid'],
    					"timepointuuid" => $timepointuuid,
    					"hypervisor" => $point['hypervisor_type'],
    					"path" => $point['dir_path'],
    					"version" => $point['version'],
    					"icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
    					"title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
    					"eventtype" => 'point',
    					'dependuuid' => $point['depend_point_uuid'],
    					"taskuuid" => $taskuuid,
    			);
    		
    		
    		}
    	}
    	
    	$msg = array(
    			're' => true,
    			'msg' => $node,
    	);
    	return json_encode($msg);
    }
    
    /**
     * 获取虚拟化中心名字和图标
     * @param string $vcenteruuid
     */
    private function getVcenterInfo($vcenteruuid, $hypervisor, $dirPath){
    	$sql = "select hypervisor_type, vcenter_flag, vcenter_ip, vcenter_name, nickname, detail from vm_vcenter where vcenter_uuid = ?";
    	$data = $this->dbSelect($sql, array($vcenteruuid));
    	$vcenter = Xphp::instance('Vcenter');
    	$pathStr = explode("/", $dirPath);
    	$name = $pathStr[0];
    	$icon = $vcenter->getHypervisorIcon($hypervisor, Xphp::$_config['FLAG']['SET']);
    	$info = array(
    		'name' => $name,
    		'icon' => $icon
    	);
    	return $info;
    }
    
    /**
     * 获取副本数据树
     * @param unknown $params
     */
    public function getArchivePointTree($params){
    	$dataflag = $params['dataflag'];
    	$storageuuid = $params['storageuuid'];       //存储uuid,为空时显示所有副本任务数据
    	$sql = "select bbt.task_name,unix_timestamp(bbt.task_create_time) task_create_time, bbt.data_local_flag,
    			bbt.task_uuid,vbt.vcenter_uuid, vbt.hypervisor_type, vbt.vcenter_uuid, vbt.dir_path, vbt.vm_name,vbt.vm_uuid, bsr.storage_uuid, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where  bbt.timepoint_uuid = vbt.timepoint_uuid and
    				   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") and 
                       bbt.user_uuid = ? ";
    	$flag = Xphp::$_config['FLAG'];
    	$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],
    			Xphp::$_user['useruuid']);
    	if ($storageuuid){
    		//如果是选择了某个存储,显示这个存储下面的
    		$sql .= " and bsr.storage_uuid = ?";
    		array_push($sqlParams, $storageuuid);
    	}
    	$sql .= " order by  bbt.task_create_time asc, bbt.timepoint desc  ";
    	 
    	 
		$data = $this->dbSelect($sql, $sqlParams);
    	$node = array();
    	 
    	//得到当前任务所有归档任务uuid
    	$currentTaskUUID = $this->getAllArchiveTaskUUID();
    	 
    	//定义task vm 
    	$task = array();
    	$vm = array();
    	$pid = null;
    	$storage = array();
    	
    	$jobHandler = Xphp::instance('JobHandler');
    	foreach ($data as $d){
    		$taskuuid = $d['task_uuid'];
    		$taskCreateTime = $d['task_create_time'];
    		//按本地存储和异地存储区分
    		if($dataflag){
    			$localFlag = intval($d['data_local_flag']);
    			if(!in_array($localFlag, $storage)){
    				$storage[] = $localFlag;
    				if($localFlag == Xphp::$_config['FLAG']['SET']){
    					$name = Xphp::$_lang['UI_COPY_LOCAL_STORAGE'];
    				}else{
    					$name = Xphp::$_lang['UI_STORAGE_TYPE9'];
    				}
    				$node[] = array(
    						"id" => $localFlag,
    						"pId" => 0,
    						"name" => $name,
    						"open" => true,
    						"nocheck" => false,
    						"type" => -1,
    						"icon" => './img/platform/storage.svg',
    						"title" => $name,
    						"isParent" => true,
    				);
    			}
    		}
    		$taskName = $jobHandler->getTimepointTaskname($d['task_uuid'],$d['task_name']);
    		//检查并添加task
    		if(!in_array($d['data_local_flag'].$taskuuid, $task)){
				$task[] = $d['data_local_flag'].$taskuuid;
				$taskAvailable = in_array($taskuuid, $currentTaskUUID);
				if(intval($d['data_local_flag']) == Xphp::$_config['FLAG']['UNSET']){
					$taskAvailable = true;
				}
    			$node[] = array(
    					"id" => $d['data_local_flag'].$taskuuid,
    					"pId" => $d['data_local_flag'],
    			        "name" => $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")",
    					"open" => false,
    					"nocheck" => false,
    					"type" => 0,
    					"icon" => './img/platform/flag.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
    					"isParent" => true,
    			);
    		}
    		
			$vmuuid = $d['vm_uuid'];
			$vcenteruuid = $d['vcenter_uuid'];
    		//检查并添加vm
    		if(!in_array($d['data_local_flag'].$vcenteruuid.$vmuuid . $taskuuid, $vm)){
    			$vm[] = $d['data_local_flag'].$vcenteruuid.$vmuuid . $taskuuid;
    			$node[] = array(
    					"id" => $d['data_local_flag'].$vcenteruuid.$vmuuid . $taskuuid,
    					"pId" => $d['data_local_flag'].$taskuuid,
    					"name" => $d['vm_name']."(". Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor_type']] .")",
    					"open" => false,
    					"nocheck" => false,
    					"type" => 1,
    					"icon" => './img/vm/vm.png',
    					"title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
    					"vcenteruuid" => $d['vcenter_uuid'],
    					"vmuuid" => $d['vm_uuid'],
    					"taskuuid" => $taskuuid,
    					"nodeuuid" => $d['node_uuid'],
    					"createtime" => $this->parseDate($d['task_create_time']),
    					"hypervisor" => $d['hypervisor_type'],
    					"isParent" => true,
    					"eventtype" => 'vm',
    					"clickshow" => true
    			);
    		}else {
    			continue;
    		}
    	}
    	return json_encode($node);
    }
    
    /**
     * 异步获取副本时间点
     * @param unknown $params
     */
    public function getSyncArchivePoiont($params){
    	$taskuuid = $params['id'];
		$vmuuid = $params['vmuuid'];
		$vcenteruuid = $params['vcenteruuid'];
    	$storageuuid = $params['storageuuid'];
    	$checked = $params['checked'];
    	$node = array();
    	$timepoint = array();
    	$pid = null;
    	$vmHandler = Xphp::instance('Vmhandler');
    	$sqlPoint = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.task_name, bbt.depend_point_uuid, bbt.backup_mode,bbt.data_local_flag,
                       unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.storage_uuid, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
	                   and bbt.import_flag = ? and bbt.module_type = ? and
                       bbt.task_uuid = ?  and
                       vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") ";
    	 
    	$flag = Xphp::$_config['FLAG'];
    	$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],$taskuuid,  $vmuuid, $vcenteruuid);
    	if(!empty($storageuuid)){
    		$sqlPoint .= " and bbt.storage_uuid = ?";
    		$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],$taskuuid,  $vmuuid, $vcenteruuid, $storageuuid);
    	
    	}
    	$sqlPoint .= " order by vbt.vm_uuid,  bbt.timepoint";
    	$pointData = $this->dbSelect($sqlPoint, $sqlParams);
    	$pid =  $vmuuid . $taskuuid;
    	foreach ($pointData as $point){
    		$taskuuid = $taskuuid;
			$vmuuid = $vmuuid;
			$vcenteruuid = $d['vcenter_uuid'];
    		$timepointuuid = $point['timepoint_uuid'];
    		$taskCreateTimeIn = $this->parseDate($point['task_create_time']);
    		//检查并添加完备点
    		if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
    			if(!in_array($timepointuuid, $timepoint)){
    				$timepoint[] =  $timepointuuid;
    				$node[] = array(
    						"id" =>  $timepointuuid,
    						"pId" =>  $d['data_local_flag'].vcenteruuid.$vmuuid . $taskuuid,
    						"name" => $this->parseDate($point['timepoint']) . "(" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
    						"checked" => $checked,
    						"type" => 3,
    						"vmuuid" => $point['vm_uuid'],
    						"vmname" => $point['vm_name'],
    						"pointname" => $this->parseDate($point['timepoint']),
    						"vcenteruuid" => $point['vcenter_uuid'],
    						"timepointuuid" => $timepointuuid,
    						"createtime" => $taskCreateTimeIn,
    						"hypervisor" => $point['hypervisor_type'],
    						"storageuuid" => $point['storage_uuid'],
    						"nodeuuid" => $point['node_uuid'],
    						"taskuuid" => $taskuuid,
    						"path" => $point['dir_path'],
    						"version" => $point['version'],
    						"icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
    						"title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
    						"eventtype" => 'point',
    						'dependuuid' => $point['depend_point_uuid']
    				);
    				//添加了完全备份时间点继续下一次
    				$pid = $timepointuuid;
    				continue;
    			}
    		}
                
            $node[] = array(
                "id" => $timepointuuid,
                "pId" => $pid,
                "name" => $this->parseDate($point['timepoint']) . "(" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
                "checked" => $checked,
                "type" => 4,
                "vmuuid" => $point['vm_uuid'],
                "vmname" => $point['vm_name'],
                "pointname" => $this->parseDate($point['timepoint']),
                "vcenteruuid" => $point['vcenter_uuid'],
                "storageuuid" => $point['storage_uuid'],
              	"nodeuuid" => $point['node_uuid'],
                "timepointuuid" => $timepointuuid,
                "hypervisor" => $point['hypervisor_type'],
                "path" => $point['dir_path'],
                "version" => $point['version'],
                "icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
                "title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                "chkDisabled" => true,
                "eventtype" => 'point',
                'dependuuid' => $point['depend_point_uuid']
            );
                
                
        }
    	
    	 
    	$msg = array(
    			're' => true,
    			'msg' => $node,
    	);
    	return json_encode($msg);
    	
    }

    /**
     * 得到当前所有归档任务的UUID
     * return array
     */
    public function getAllArchiveTaskUUID(){
    	$taskuuid = array();
    	$sql = "select task_uuid from bd_task where module_type = ? and delete_flag = ? and task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].")";
    	$sqlParams = array(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'], Xphp::$_config['FLAG']['UNSET']);
    	$data = $this->dbSelect($sql, $sqlParams);
    	foreach ($data as $d){
    		$taskuuid[] = $d['task_uuid'];
    	}
    	return $taskuuid;
    }
    
    /**
     * 得到虚拟机副本任务名
     * @param unknown $params
     */
    public function getVMArchiveTaskName($params){
    	$taskName = Xphp::$_lang['UI_PLATFORM_ARCHIVE_JOB'];
    	$vmHandler = Xphp::instance('Vmhandler');
    	return $vmHandler->getValidTaskName($taskName);
    }
    
    /**
     * 得到虚拟机副本拉回任务名
     * @param unknown $params
     */
    public function getVMArchiveBackName($params){
    	$taskName = Xphp::$_lang['UI_PLATFORM_ARCHIVE_FETCH_JOB'];
    	$vmHandler = Xphp::instance('Vmhandler');
    	return $vmHandler->getValidTaskName($taskName);
    }
    
    /**
     * 删除批量归档备份时间点
     * @param array $params 二维数组
     */
    public function deleteBatchArchivepoint($params){
    	//得到二维数组
    	//遍历，并按节点uuid分组
    	//发送删除消息到不同的节点。删除消息array(timepointuuid,timepointuuid,timepointuuid,timepointuuid)
    	
    	$timepointList = $params['timepointList'];
    	$vmList = $params['vmList'];
    	$selectTimepoints = array();
    	$vmHandler = Xphp::instance('Vmhandler');
    	foreach ($vmList as $vm){
    		$selectTimepoints = $vmHandler->getTimepointByVM($vm);
    		$timepointList =  array_merge($timepointList, $selectTimepoints);
    	}
    	$utils = Xphp::instance('Utils');
    	$timepointList = $utils->arraySort($timepointList, 'nodeuuid', '', 0, -1);
    	 
    	$info = array();
    	$nodeuuids = array();
    	$timepointuuids = array();
    	$timepointuuid = array();
    	foreach($timepointList as $d){
    		if(!in_array($d['nodeuuid'], $nodeuuids)){
    			$nodeuuids[] = $d['nodeuuid'];
    			$info[] =  array(
    					"nodeuuid" => $d['nodeuuid'],
    					"hypervisor" => $d['hypervisor']);
    			if(!empty($timepointuuid)){
    				$timepointuuids[] = $timepointuuid;
    				$timepointuuid = array();
    			}
    
    			$timepointuuid[] = $d['timepointuuid'];
    		}else{
    			$timepointuuid[] = $d['timepointuuid'];
    		}
    	}
    	$timepointuuids[] = $timepointuuid;
    	$nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
    	$this->paramsCheck( $timepointuuids[0][0],$nodeuuids[0]);
    	//检测是否在任务中在任务就直接返回
    	$uuidList = array();
    	foreach($timepointuuids as $d){
    
    		$uuidList = array_merge($uuidList,$d);
    	}
    	 
    	$opName = 'BD_ARCHIVE_POINT_OP_BATCH_DELETE';
    	for($i=0;$i<$nodeuuidsCount.length;$i++){
    		$msg = $timepointuuids[$i];
    		$msg = array(
    				'timepoint_uuids' => $timepointuuids[$i]
    		);
    		$msg = json_encode($msg);
    		$mbResult = $this->mbCopyMsg($nodeuuids[$i], $info[$i]['hypervisor'], $opName, $msg);
    	}
    	$result = $mbResult['result'];
    	$pfOpcode = Xphp::instance('PFOpcode');
    	$operate = $pfOpcode->getOpcodeDes($opName);
    	$msg = $mbResult['msg'];
    	//返回结果到UI
    	if($result){
    		return $this->muOpResult($result, $operate, $msg, '', 0, '');
    	}else{
    		return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
    	}
    }
    
    /**
     * 删除一个归档时间点
     * @param unknown $params
     */
    public function deleteArchivepoint($params){
    	$pointUUID = $params['uuid'];
    	$hypervisor = $params['hypervisor'];
    	$this->paramsCheck($pointUUID, $hypervisor);
    
    	//检测是否在任务中在任务就直接返回
    	$vmuuid = $params['vmuuid'];
    	$vcenteruuid = $params['vcenteruuid'];
    	$taskuuid = $params['taskuuid'];
    
    	$submodule_type = intval($hypervisor);
    	$opName = 'BD_ARCHIVE_POINT_OP_DELETE';
    	$msg = array($pointUUID);
    	$msg = json_encode(array('timepoint_uuids'=>$msg));
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
    	$mbResult = $this->mbCopyMsg($nodeuuid, $submodule_type, $opName, $msg);
    	$result = $mbResult['result'];
    	$pfOpcode = Xphp::instance('PFOpcode');
    	$operate = $pfOpcode->getOpcodeDes($opName);
    	$msg = $mbResult['msg'];
    
    	//返回结果到UI
    	if($result){
    		$sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ? and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].", ".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") and bbt.timepoint_uuid = ?
                    ";
    		$flag = Xphp::$_config['FLAG'];
    		$dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $vmuuid, $vcenteruuid, $taskuuid, $pointUUID));
    		$count = $dataCount[0]['total'];
    		return $this->muOpResult($result, $operate, $msg, '', 0, array("count"=>intval($count), "id"=>$pointUUID));
    	}else{
    		return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
    	}
    }
    
    /**
     * 获取副本节点uuid
     * @param string $nodeInfo
     */
    private function getCopyNodeuuid($nodeInfo){
    	$storageuuid = $nodeInfo['storageuuid'];
    	$sql ="select node_uuid from bd_storage_resource where storage_uuid = ?";
    	$data =$this->dbSelect($sql, array($storageuuid));
    	return $data[0]['node_uuid'];
    }
    
    /**
     * 
     * @param string $strategyID
     */
    private function getArchiveReserve($strategyID){
    	$sql = "select strategy_type, number from bd_reserved_strategy where strategy_id = ?";
    	$data = $this->dbSelect($sql, array($strategyID));
    	$info = array();
    	if(empty($data)){
    		$info = array(
    			'type' => 1,
    			'number' => 1,
    		);
    	}else{
    		$info = array(
    			'type' => intval($data[0]['strategy_type']),
    			'number' => intval($data[0]['number']),
    		);
    	}
    	return $info;
    	
    }
	
	  
    /**
     * 得到处于备份任务的虚拟机信息
     * Vmhandler需要调用,所以public
     */
    private function getBackupVMInfo(){
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, bt.task_name, bt.node_uuid from vm_machine_list vml, bd_task bt 
                where vml.task_uuid = bt.task_uuid and bt.task_type = ? and bt.user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_user['useruuid']));
        $info = array(
            'vmuuid' => array(),
            'vcenteruuid' => array(),
            'taskname' =>array()
        );
        foreach ($data as $d){
            $info['vmuuid'][] = $d['vm_uuid'];
            $info['vcenteruuid'][] = $d['vcenter_uuid'];
            $info['taskname'][] = $d['task_name'];
			$info['nodeuuid'][] = $d['node_uuid'];
         }
        return $info;
    }
	
}
?>