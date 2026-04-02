<?php
/******************************************* 
** 平台统一数据处理 
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-5-15 下午01:18:40 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class PlatFormHandler extends OPHandler{
    
    /**
     * 得到灾备中心概况/运行时间&备份数据&备份成功率&当前任务数量
     * @param unknown $params
     */
    public function getDataCenterSurvey($params){
        $systemHandler = Xphp::instance('SystemHandler');
        $survey = array(
            "serverRunTime" => $this->getSystemRunningTime(),
            "totalBackupData" => $this->getAccumulatedData(),
            "successRate" => $this->getSuccessRate(),
            "totalJobNum" => $this->getTaskTotal(),
            "systemTime" => date("Y/m/d H:i:s", $systemHandler->getSystemTime())
        );
        return json_encode($survey);
    }
    
    /**
     * 得到系统运行时间
     */
    public function getSystemRunningTime($params){
        $info = array(
            'date' => '0'.Xphp::$_lang['WEB_UTILS_DAY'],
            'time' => '0'.Xphp::$_lang['WEB_UTILS_HOUR']
        );
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        if($data){
            $value = round($data[0]['system_run_time'], 1);
            $date = intval($value/24);
            $time = round($value - ($date*24), 1);
            $timeDes = $time.Xphp::$_lang['WEB_UTILS_HOUR'];
            $dateDes = $date.Xphp::$_lang['WEB_UTILS_DAY'];
            if($date >= 365){
            	$year = intval($date/365);
            	$days = $date % 365;
            	$dateDes = $year .Xphp::$_lang['WEB_UTILS_YEAR']. $days . Xphp::$_lang['WEB_UTILS_DAY'];
            }
            
            $info = array(
            	'date' => $dateDes,
            	'time' => $timeDes,
            	'hours' => $time
            );
        }
        return json_encode($info);
    }
    
    /**
     * 得到累计备份数据
     */
    private function getAccumulatedData(){
        $sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data from bd_user_extension ";
        $sqlParams = array();
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $total = 0;
        foreach ($data as $d){
            $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] + 
                      $d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'];
        }
        
        $utils = Xphp::instance('Utils');
        $info = $utils->calSizeToValueAndUnit($total, true);
        return $info;
    }
    
    /**
     * 得到任务运行次数
     */
    private function getSuccessRate(){
        $sql = "select sum(task_success) as success, sum(task_failure) as failure from bd_user_extension ";
        $sqlParams = array();
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array(
            'value' => Xphp::$_config['NULLSPACE'],
            'unit' => ''
        );
        if ($data){
            $total = $data[0]['success'] + $data[0]['failure'];
            $info['value'] = $total;
            $info['unit'] = Xphp::$_lang['WEB_PALTFORM_DC_TIME'];
            return $info;
        }
        return $info;
    }
    
    /**
     * 得到任务总数
     */
    private function getTaskTotal(){
        $sql = "select count(id) as total from bd_task ";
        $sqlParams = array();
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        
        $info = array(
            'value' => 0,
            'unit' => Xphp::$_lang['WEB_PLATFORM_DC_JOB_NUM'],
        );
        if($data){
            if($data[0]['total'] > 0){
                $info['value'] = $data[0]['total'];
                $info['unit'] = Xphp::$_lang['WEB_PLATFORM_DC_JOB_NUM'];
                return $info;
            }
        }
        return $info;
    }
    
    public function getNetworkCard($params){
		$nodeuuid = $params['nodeuuid'];
		$sql = "select distinct card_name from bd_network_monitor where node_uuid = ?";
		$data = $this->dbSelect($sql, array($nodeuuid));
		$networkList = array();
		foreach ($data as $d){
			$networkList[] = array(
				'nodeuuid' => $nodeuuid, 
				'name' => $d['card_name']
			);
		}
    	return json_encode($networkList);
    }
    
    	
    /**
     * //TODO
     * 得到
     * @param unknown $params
     */
    public function getCharData($params){
		$count = $params['count'];
		$nodeuuid = $params['nodeuuid'];
		$initFlag = $params['initFlag'];
		$sqlParams = array($nodeuuid, $count);
		$sqlCpu = "select unix_timestamp(monitor_time) monitor_time, cpu_rate, memory_rate, memory_total, memory_used, details 
				from bd_cpu_memory_monitor where node_uuid = ? and monitor_time  < now() order by unix_timestamp(monitor_time) desc limit ?";
    	$dataCpu = $this->dbSelect($sqlCpu, $sqlParams);
        if(!$initFlag){
			$sortCpu = $this->_array_column($dataCpu,'monitor_time');
			array_multisort($sortCpu ,SORT_ASC,$dataCpu);
        }
        $cpuData = array();
        $memoryData = array();
        $utils = Xphp::instance('Utils');
       	foreach ($dataCpu as $d){
       		if($d['details']){
       			$details = json_decode($d['details'], true);
       			$cpuList =$details['cpu_list'];
       		}else{
       			$cpuList = array();
       		}
       		
       		$cpuData[] = array(
       			'cpuRate' => floatval($d['cpu_rate']),
       			'cpu_time' => date('H:i:s',$d['monitor_time']),
       			'cpuList' => $cpuList
       		);
       		$free = intval($d['memory_total']) - intval($d['memory_used']);
       		$memoryData[] = array(
       			'memoryRate' => floatval($d['memory_rate']),
       			'total' => $utils->calSize($d['memory_total']),
       			'free' => $utils->calSize($free),
       			'memory_time' => date('H:i:s',$d['monitor_time']) 
       				
       		);
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
    
    public function getNetworkChart($params){
    	$cardName = $params['name'];
    	$nodeuuid = $params['nodeuuid'];
    	$count = $params['count'];
    	$flag = $params['flag'];
    	$sqlTime = "select distinct monitor_time from bd_network_monitor where node_uuid = ? ";
    	$sqlParams = array($nodeuuid, $count);
		if($cardName && $cardName != "0"){
			$sqlTime .= " and card_name = ? ";
			$sqlParams = array($nodeuuid, $cardName, $count);
		}		    	
    	$sqlTime .= " and monitor_time  < now() order by unix_timestamp(monitor_time) desc limit ?";
    	$dataTime = $this->dbSelect($sqlTime, $sqlParams);
    	$timeArray = array();
    	foreach ($dataTime as $time){
    		$timeArray[] = $time['monitor_time'];
    	}
    	$timeStr = implode("','", $timeArray);
    	$sqlNetwork = "select unix_timestamp(monitor_time) monitor_time, card_name, receive_size, transmit_size from bd_network_monitor where node_uuid = ? and monitor_time in (' $timeStr ') ";
    	$sqlParams = array($nodeuuid);
    	if($cardName && $cardName != "0"){
    		$sqlNetwork .= " and card_name = ? ";
    		$sqlParams = array($nodeuuid, $cardName);
    	}
    	$sqlNetwork .= " order by unix_timestamp(monitor_time) desc";
    	$dataNetwork = $this->dbSelect($sqlNetwork, $sqlParams);
    	$netData = array();
    	foreach ($timeArray as $time){
    		$network = array();
    		foreach($dataNetwork as $data){
    			if($data['monitor_time'] == strtotime($time)){
    				$network[] = array(
    						'name' => $data['card_name'],
    						'receive' => floatval($data['receive_size']),
    						'transmit' => floatval($data['transmit_size']),
    						'net_time' => date('H:i:s',$data['monitor_time'])
    				);
    			}
    		}
    		//通过网卡名字给网卡排序
    		$sortList = $this->_array_column($network,'name');
    		array_multisort($sortList ,SORT_ASC,$network);
    		$netData[] = array(
    				'timeDes' => date('H:i:s',strtotime($time)),
    				'time' => $time,
    				'networkList' => $network
    		);
    	}
    	if(!$flag){
    		//第一次初始化通过时间升序排列
    		$sortNetwork = $this->_array_column($netData,'time');
    		array_multisort($sortNetwork ,SORT_ASC,$netData);
    	}
    	 
    	$data = array(
    		"timeInterval" => time(),
    		"currentTime" => date("H:i:s"),
    		"netData" => $netData
    	);
    	
    	return json_encode($data);
    }
    
    //数组根据字段排序
    public function _array_column(array $array, $column_key, $index_key=null){
	    $result = [];
	    foreach($array as $arr) {
	        if(!is_array($arr)) continue;
	
	        if(is_null($column_key)){
	            $value = $arr;
	        }else{
	            $value = $arr[$column_key];
	        }
	
	        if(!is_null($index_key)){
	            $key = $arr[$index_key];
	            $result[$key] = $value;
	        }else{
	            $result[] = $value;
	        }
	    }
	    return $result; 
	}
    
    
    private function getNetworkMac($name){
    	$cmd = "cat /sys/class/net/".$name."/address";
        exec($cmd, $mac);
        return $mac[0];
    }
    
    /**
     * 得到任务饼图数据
     * @param unknown $params
     */
    public function getTaskPie($params){
        //获取所有虚拟机
        $sql = "select vt.uuid, vt.vcenter_uuid from vm_tree vt, vm_vcenter vv
                where vt.vcenter_uuid = vv.vcenter_uuid and vt.display_mode = ?
                and vt.type = ? and vv.user_uuid = ? order by vt.name ";
        $sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            Xphp::$_config['VM_TREE_TYPE']['VM'], Xphp::$_user['useruuid']
        );
        $data = $this->dbSelect($sql, $sqlParams);
                        
        //获取所有正在备份任务中的虚拟机
        $vcenterHandler = Xphp::instance('Vcenter');
        $vmInbackupInfo = $vcenterHandler->getBackupVMInfo();
        $inBackupVM = count($vmInbackupInfo['vmuuid']);
        
        //所有虚拟机的数组
        $allVMInfo = array();
        foreach ($data as $d){
        	$allVMInfo[] = $d['vcenter_uuid'] . $d['uuid'];
        }
        //受保护虚拟机的数组
        $inBackupVMArr = array();
        foreach ($vmInbackupInfo['vmuuid'] as $key => $value){
        	$inBackupVMArr[] = $vmInbackupInfo['vcenteruuid'][$key] . $value;
        }
        
        $i = 0;
        foreach ($inBackupVMArr as $vm){
        	if(in_array($vm, $allVMInfo)){
        		$i++;
        	}
        }        
        $allVM = count($allVMInfo);
        $notInBackupVM = $allVM - $i;              
        
        $taskData = array();
        $utils = Xphp::instance('Utils');
        $taskData[] = array(
            "value" => $inBackupVM,
            "des" => $inBackupVM . Xphp::$_lang['WEB_PLATFORM_DC_NUM'],
            "name" => Xphp::$_lang['WEB_PLATFORM_DC_VM_IN_PROTECED'],
        );
        $taskData[] = array(
            "value" => $notInBackupVM,
            "des" => $notInBackupVM . Xphp::$_lang['WEB_PLATFORM_DC_NUM'],
            "name" => Xphp::$_lang['WEB_PLATFORM_DC_VM_UNPROTECED'],
        );
        return json_encode($taskData);
    }
    
    /**
     * 得到存储饼图数据
     * @param unknown $params
     */
    public function getStorePie($params){
        $sql = "select sum(total_size) as total, sum(free_size) as free from bd_storage_resource where lan_free_flag = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET']));
        $utils = Xphp::instance('Utils');
        $storageData = array();
        foreach ($data as $d){
            $used = $d['total'] - $d['free'];
            $storageData[] = array(
                "value" => intval($used),
                "des" => $utils->calSize($used),
                "name" => Xphp::$_lang['WEB_PLATFORM_DC_USED_SPACE']
            );
            $storageData[] = array(
                "value" => intval($d['free']),
                "des" => $utils->calSize($d['free']),
                "name" => Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE']
            );
        }
        return json_encode($storageData);
    }
    
    /**
     * 得到系统授权信息
     * @param unknown $params
     */
    public function getLisenceInfo($params){
        $systemHandler = Xphp::instance('SystemHandler');
        $status = $systemHandler->getSystemAuthorizationStatus();
        $statusDes = $systemHandler->getSystemAuthorizationDes($status);
        $flag = false;
        if($status == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            $flag = true;
        }
        $info = array(
            'status' => $flag,
            'title' => $statusDes,
            'info' => Xphp::$_lang['WEB_PLATFORM_DC_LISENCE_TIPS']
        );
        return json_encode($info);
    }
    
    /**
     * 更新灾备中心页面布局,对应到每一个用户
     * @param unknown $params
     */
    public function updatePageLayout($params){
        if(!Xphp::$_config['DATACENTER_DIY']) return true;
        $leftID = $params['leftID'];
        $rightID = $params['rightID'];
        $pageLayout = array(
            'leftID' => $leftID,
            'rightID' => $rightID
        );
        $sql = "update bd_user set page_layout = ? where user_uuid = ?";
        $sqlParams = array(json_encode($pageLayout), Xphp::$_user['useruuid']);
        return $this->dbExec($sql, $sqlParams);
    }
    
    /**
     * 得到后端配置文件信息
     * 虚拟化类型,描述,系统名称
     */
    public function getConfig(){
        $config = array(
            'VM_TYPE' => $this->getConfigVMType(),
            'VM_DES' => $this->getConfigVMDes(),
            'SYSTEMNAME' => Xphp::$_config['SYSTEM_INFO']['system_name'],
            'SOFTWARE' => Xphp::instance('SystemHandler', 'getSoftwareType'),
        );
        return json_encode($config);
    }
    /**
     * 得到虚拟化类型配置
     * @return multitype:unknown
     */
    private function getConfigVMType(){
        //name 是在JS里面的键名
        $name = array('UNKNOWN', 'VMWARE', 'HYPERV', 'CITRIX', 'KVM', 'XEN', 'ORACLEVM', 'CLOUDVIEW', 
                      'INCLOUD', 'VGATE', 'NEOKYLIN', 'H3C', 'SANGFOR', 'SDCOS', 'FLEXCLOUD', 'OPENSTACK', 
                      'FUSIONKVM', 'FUSIONXEN', 'WINSERVER', 'RHV', 'DSERVER', 'CLOUDVIEWSVM', 'FLEXHCS', 'OSEASYVSERVER',
                      'INCLOUDKVM', 'WINDIY'
        );
        $newConfig = array();
        foreach (Xphp::$_config['VMHYPERVISORTYPE'] as $value){
            $newConfig[$name[$value]] = $value;
        }
        return $newConfig;
    }
    
    /**
     * 得到虚拟化名称
     */
    private function getConfigVMDes(){
        $newConfig = array();
        foreach (Xphp::$_config['VMHYPERVISORDES'] as $value){
            $newConfig[] = $value;
        }
        return $newConfig;
    }
    
    
    /**
     * 得到某个用户的页面布局配置
     * @param unknown $params
     */
    public function getPageLayout($params){
        $sql = "select page_layout from bd_user where user_uuid = ?";
        $sqlParams = array(Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0][0];
    }
    
    /**
     * 通过url对于参数得到页面路径
     * @param unknown $params
     */
    public function getPage($params){
    	$name = $params['name'];
    	$pages = require_once CONF_PATH . 'page.php';
    	$info = array();
    	if($name == 'homepage'){
    		$info = array(
    			'url' => "./content/platform/databackup_center.php",
    		);
    		return json_encode($info);
    	}
    		
    	$pageArray = array();
    	$pageArrayChild = array();
    	foreach ($pages as $page){
    		$childPages = $page['child'];
    		if(!$childPages) continue;
    		foreach ($childPages as $child){
    			if($child['level'] == 1){
    				$pageArray[] = $child;
    			}
    			$childTwo = $child['child'];
    			if(!$childTwo) continue;
    			foreach($childTwo as $c){
    				if($c['level'] == 1){
    					$pageArrayChild[] = $c;
    				}
    			}
    		}
    	}
    	$pageArray = array_merge($pageArray, $pageArrayChild);
    	
    	$pageThree = array();
    	foreach ($pageArray as $p){
    		if($p['name'] == $name){
    			$info = array(
    				'url' => $p['path'],
    			);
    		}
    		$childThree = $p['child'];
    		if(!$childThree) continue;
    		foreach ($childThree as $three){
    			if($three['level'] == 2 && $three['name'] == $name){
    				$info = array(
    						'url' => $three['path'],
    				);
    			}
    		}
    	}
    	return json_encode($info);
    }
    
    /**
     * 得到灾备中心概况/运行时间&备份数据&功能选项&虚拟机使用统计&备份存储使用统计
     * @param unknown $params
     */
    public function getDataSurvey($params){
    	$systemHandler = Xphp::instance('SystemHandler');
    	$info = array();
    	$survey = array(
    			"systemTime" => date("Y/m/d H:i:s", $systemHandler->getSystemTime()),
    			"licenseInfo" => $systemHandler->getSystemLisenceInfo(),
    			"totalBackupData" => $this->getAccumulatedData(),
    	);
    	
    	$info = array(
    		'survery' => $survey,
    		'vcenter_select' => $this->getVcenters(),
    		'storage_select' => $this->getStorages(),
    		'node_select' => $this->getNodes(),
    	);
    	
    	return json_encode($info);
    }
    
    /**获取系统授权状态
     * 
     */
    private function getTrialType(){
    	$sql = "select trial_type from bd_license";
    	$data = $this->dbSelect($sql);
    	return $data[0]['trial_type'];
    }
    /**
     * 获取任务虚拟机信息
     * @param string $uuid
     * @param int $status
     * @param int $tasktype
     */
    private function getVmDetails($uuid,$status, $tasktype){
    	$sqlvm = "select count(machine_id) as vm_num, vm_name from vm_machine_list where task_uuid = ?";
    	if($tasktype == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY']){
    		$sqlvm = "select count(instant_id) as vm_num, orig_vm_name as vm_name from vm_instant where task_uuid = ?";
    	}
    	$dataVm  = $this->dbSelect($sqlvm, array($uuid));
    	$vmNum = intval($dataVm[0]['vm_num']);
    	$sqlrunTime = "select count(id) as run_num from bd_history_task where task_uuid = ?";
    	$dataRun  = $this->dbSelect($sqlrunTime, array($uuid));
    	$runNum = intval($dataRun[0]['run_num']);
    	$info = array(
    		'vm_num' => $vmNum,
    		'run_num' => $runNum,
    		'vm_name' => $dataVm[0]['vm_name']
    	);
    	if($status == Xphp::$_config['TASKSTATUS']['RUNNING']){
    		$sqlComplete = "select count(machine_id) as complete_num from vm_machine_list where task_uuid = ? and task_status = ?";
    		$dataComplete = $this->dbSelect($sqlvm, array($uuid, Xphp::$_config['VmTaskStatus']['WEB_VM_COMPLETION']));
    		$completeNum = intval($dataComplete[0]['complete_num']);
    		$vmRunNum =$completeNum."/".$vmNum;
    		if($tasktype == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY']){
    			$vmRunNum = $vmNum;
    		}
    		$info = array(
    			'vm_num' => $vmRunNum,
    			'run_num' => $runNum,
    			'vm_name' => $dataVm[0]['vm_name']
    		);
    	}
    	
    	return $info;
    	
    }
    
    /**
     * 细粒度恢复信息
     * @param string $uuid
     * @param int $status
     */
    private function getGrainDetails($uuid,$status){
    	$sqlvm = "select vm_name from vm_grain_info where task_uuid = ?";
    	$dataVm  = $this->dbSelect($sqlvm, array($uuid));
    	$info = array(
    			'vm_name' => $dataVm[0]['vm_name'],
    	);
    	 
    	return $info;
    }
    
    /**
     * 
     * @param string $uuid
     * @param int $status
     */
    private function getCopyDetails($uuid,$status){
    	$sqlvm = "select count(vm_id) as vm_num from backup_copy_vm_list where task_uuid = ?";
    	$dataVm  = $this->dbSelect($sqlvm, array($uuid));
    	$vmNum = intval($dataVm[0]['vm_num']);
    	$sqlrunTime = "select count(id) as run_num from bd_history_task where task_uuid = ?";
    	$dataRun  = $this->dbSelect($sqlrunTime, array($uuid));
    	$runNum = intval($dataRun[0]['run_num']);
    	$info = array(
    			'vm_num' => $vmNum,
    			'run_num' => $runNum
    	);
    	if($status == Xphp::$_config['TASKSTATUS']['RUNNING']){
    		$sqlComplete = "select count(vm_id) as complete_num from backup_copy_vm_list where task_uuid = ? and copy_status = ?";
    		$dataComplete = $this->dbSelect($sqlvm, array($uuid, Xphp::$_config['VmTaskStatus']['WEB_VM_COMPLETION']));
    		$completeNum = intval($dataComplete[0]['complete_num']);
    		$info = array(
    				'vm_num' => $completeNum."/".$vmNum,
    				'run_num' => $runNum
    		);
    	}
    	 
    	return $info;
    }
    
    /**
     * 得到计划任务信息/灾备中心
     */
    public function getCurrentTaskInfo($params){
    	$start = 0;
    	$length = $params['length'];
    	//任务状态数组
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
                		unix_timestamp(bs.next_start_time) next_start_time, bri.current_file_size, bri.current_completed_size, bri.speed, bri.total_size, bri.current_total_size,unix_timestamp(bri.start_time) start_time
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
    	$jobHandler = Xphp::instance('JobHandler');
    	foreach ($data as $d){
    		if( $d['task_type'] == Xphp::$_config['TASKTYPE']['ORCH_TASK'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']) continue;
    		$submodule = $jobHandler->getSubmoduleType($d['module_type'], $d['task_uuid']);
    		$totalSize= $utils->calSize($d['total_size']);
    		$completesize = $utils->calSize($d['current_total_size']);
    		$vmInfo = array();
    		$fsInfo = array();
    		switch ($d['module_type']){
    			case Xphp::$_config['MODULE_TYPE']['VM']:
    				if($d['task_type'] == Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY']){
    					//细粒度
    					$vmInfo = $this->getGrainDetails($d['task_uuid'], $d['task_status']);
    				}else{
    					$vmInfo =$this->getVmDetails($d['task_uuid'], $d['task_status'], $d['task_type']);
    				}
    				break;
    			case Xphp::$_config['MODULE_TYPE']['FS']:
    				$fsInfo = $this->getFsDetails($d['task_uuid'], intval($d['task_status']));
    				break;
    			case Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']:
    				//副本
    				$vmInfo =$this->getCopyDetails($d['task_uuid'], $d['task_status']);
    				break;
    		}
    		$nowTime = time();
    		if($d['next_start_time'] < $nowTime){
    			$timeDes = Xphp::$_config['TIMESPACE'];
    		}else{
    			$timeDes = $utils->formatDate($d['next_start_time']);
    		}
    		$taskInfo[] = array(
    				'moduletype' => intval($d['module_type']),
    				'taskdes' => $ptDes['TASKTYPEDES'][$d['task_type']],
    				'tasktype' => intval($d['task_type']),
    				'uuid' => $d['task_uuid'],
    				'taskname' => $d['task_name'],
    				'timedes' => $timeDes,
    				'sdes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
    				'status' => intval($d['task_status']),
    				'href' => $jobHandler->getCurrentJobHref($d['module_type'], $d['task_type'], $d['task_uuid']),
    				'speed' => $utils->calSpeed($d['speed']),
    				'vm_info' => $vmInfo,
    				'fs_info' => $fsInfo,
    				'intervalTime' => $jobHandler->getTimeInterval($d['start_time'], $d['task_status']),
    				'tasksize' => $completesize . "/" . $totalSize,
    				'progress' => $jobHandler->getTaskTotalProgress($d['task_status'], $d['total_size'], $d['current_total_size'], false),
    				'start_time' => date('Y-m-d H:i:s', $d['start_time']),
    				'next_start_time' => $jobHandler->getNextStartTime($d['next_start_time'])
    		);
    	}
    	return json_encode($taskInfo);
    }
    
    /**
     * 得到任务历史任务信息/灾备中心
     * 
     */
    public function getHistoryTaskInfo($params){
    	$start = 0;
    	$length = $params['length'];
    	$sql = "select module_type, submodule_type, task_type, task_name, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, error_code, total_size, details
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
    	$jobHandler = Xphp::instance('JobHandler');
    	foreach ($data as $d){
    		$details = json_decode($d['details'], true);
    		$listLength = count($details);
    		if(intval($d['error']) != 0){
    			$i = 0;
    			foreach ($details as $detail){
    				if($detail['task_status'] == 4){
    					$i++;
    				}
    			}
    			$vmNum = $i."/".$listLength;
    		}else{
    			$vmNum = $listLength;
    		}
    		if($d['module_type'] == Xphp::$_config['MODULE_TYPE']['FS']){
    			$fsNum = count($details['file_list']);
    		}
    		$taskInfo[] = array(
    				'module' => intval($d['module_type']),
    				'submodule' => intval($d['submodule_type']),
    				'task_type' => intval($d['task_type']),
    				'tdes' => $ptDes['TASKTYPEDES'][$d['task_type']],
    				'taskname' => $d['task_name'],
    				'resultdes' => $jobHandler->getHistoryJobResultDes($d['error_code']),
    				'error' => intval($d['error_code']),
    				'timedes' => $utils->formatDate($d['finish_time']),
    				'finish_time' => date('Y-m-d H:i:s', $d['finish_time']),
    				'total_size' => $utils->calSize($d['total_size']),
    				'vm_num' => $vmNum,
    				'fs_num' => $fsNum,
    				'intervalTime' =>$utils->secToTime($d['finish_time'] -$d['start_time']),
    				'finish_time_en' => date('H:i:s, m-d-Y')
    		);
    	}
    
    	return json_encode($taskInfo);
    }
    
    /**
     * 得到虚拟化中心概况/灾备中心
     */
    public function getSystemVms($params){
    	$hypervisor = $params['hypervisor'];
    	$sqlHost ="select count(vh.host_uuid) as host_count from vm_host vh, vm_vcenter vv where vh.vcenter_uuid = vv.vcenter_uuid ";
    	if($hypervisor && $hypervisor != "0"){
    		$sqlHost .= " and vv.hypervisor_type = ? ";
    		$sqlHostParams = array($hypervisor);	
    	}
    	$dataHost = $this->dbSelect($sqlHost, $sqlHostParams);
    	
    	$sqlProtect = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv where vml.vcenter_uuid = vv.vcenter_uuid ";
    	if($hypervisor && $hypervisor != "0"){
    		$sqlProtect .= " and vv.hypervisor_type = ? ";
    		$sqlProtectParams = array($hypervisor);
    	}
    	$dataProtect = $this->dbSelect($sqlProtect, $sqlProtectParams);
    	
    	$sqlAll = "select count(distinct vt.uuid) as all_vms from vm_tree vt, vm_vcenter vv where vt.type = 7 and vt.vcenter_uuid = vv.vcenter_uuid ";
    	$sqlAllParams = array();
    	if($hypervisor && $hypervisor != "0"){
    		$sqlAll .= " and vv.hypervisor_type = ? ";
    		$sqlAllParams =array($hypervisor);	
    	}
    	$dataAll = $this->dbSelect($sqlAll, $sqlAllParams);
    	
    	$protectNum = intval($dataProtect[0]['protect_vms']);
    	$allNum = intval($dataAll[0]['all_vms']);
    	$percent = round(($protectNum/$allNum)*100, 1);
    	$info = array(
    		"host_num" => intval($dataHost[0]['host_count']),
    		"protect_vm_num" => $protectNum,
    		"all_vm_num" => $allNum,
    		'percent' => $percent
    	);
    	
    	return json_encode($info);
    }
    
    /**
     * 得到备份存储容量概况/灾备中心
     */
    public function getSyStemStorage($params){
    	$storageuuid = $params['storageuuid'];
    	$sql = "select count(bsr.storage_uuid) as storage_count, sum(bsr.total_size) as total_size, sum(bsr.free_size) as free_size from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid ";
    	$sqlParams = array();
    	if($storageuuid && $storageuuid !="0"){
    		$sql .= " and storage_uuid = ?";
    		$sqlParams = array($storageuuid);
    	}
    	$data = $this->dbSelect($sql,$sqlParams);
    	$utils = Xphp::instance('Utils');
    	$totalSize = intval($data[0]['total_size']);
    	$freeSize = intval($data[0]['free_size']);
    	$percent = round(($freeSize/$totalSize)*100, 1);
    	$info = array(
    		"storage_num" => intval($data[0]['storage_count']),
    		"total_size" => $utils->calSize($totalSize),
    		"free_size" => $utils->calSize($freeSize),
    		"percent" => $percent,
    		"total_info" => $utils->calSizeToValueAndUnit($totalSize, true),
    		"free_info" => $utils->calSizeToValueAndUnit($freeSize, true),
    	);
    	
    	return json_encode($info);
    }
    
    
   	/**
   	 * 获取虚拟化中心列表
   	 */
    private function getVcenters(){
    	$sql = "select distinct hypervisor_type from vm_vcenter";
    	$data = $this->dbSelect($sql);
    	$info = array();
    	foreach($data as $d){
    		$info[] = array(
    			'hypervisor' => intval($d['hypervisor_type']),
    			'text' => Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])]
    		);
    	}
    	
    	return $info;
    }
    
    /**
     * 获取存储列表
     */
    private function getStorages(){
    	$sql = "select bsr.storage_nickname, bsr.storage_uuid from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ";
    	$data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET']));
    	$info = array();
    	foreach($data as $d){
    		$info[] = array(
    			'storage_uuid' => $d['storage_uuid'],
    			'storgae_name' => $d['storage_nickname']
    		);
    	} 
    	return $info;
    }
    
    /**
     * 获取节点列表
     */
    private function getNodes(){
   		$sql = "select ip, node_uuid, node_type from bd_node order by node_type asc";
   		$data = $this->dbSelect($sql);
   		$info = array();
   		$nodeHandler = Xphp::instance('NodeHandler');
   		foreach($data as $d){
			$nodeInfo = $nodeHandler->getNodeAllStatus($d['node_uuid']);
			if(!$nodeInfo['flag']) continue;
   			$name = $d['ip'];
   			if($d['node_type'] == 1){
   				$name = Xphp::$_lang['UI_PALTFORM_MASTER_NODE']."(".$d['ip']. ")";
   			}
   			
   			$info[] = array(
   				'name' => $name,
   				'node_uuid' => $d['node_uuid']	
   			);
   		}
   		
   		return $info;
    }
    
    /**
     * 获取文件模块当前任务详情信息
     * @param string $taskuuid
     * @param int $status
     * @param int $tasktype
     */
    private function getFsDetails($taskuuid, $status){
    	$sqlvm = "select count(path_id) as fs_num from fs_path_list where task_uuid = ?";
    	$dataVm  = $this->dbSelect($sqlvm, array($taskuuid));
    	$fsNum = intval($dataVm[0]['fs_num']);
    	$sqlrunTime = "select count(id) as run_num from bd_history_task where task_uuid = ?";
    	$dataRun  = $this->dbSelect($sqlrunTime, array($taskuuid));
    	$runNum = intval($dataRun[0]['run_num']);
    	$info = array(
    			'fs_num' => $fsNum,
    			'run_num' => $runNum
    	);
    	
    	return $info;
    }
}
?>