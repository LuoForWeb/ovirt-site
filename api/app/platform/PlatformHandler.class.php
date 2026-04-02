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
        $sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data, db_data, os_data from bd_user_extension ";
        $sqlParams = array();
        if(Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
           
        }else{
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $total = 0;
        foreach ($data as $d){
            $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] + 
                      $d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'] + $d['db_data'] + $d['os_data'];
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
    
    /**
     * 得到网卡使用信息
     * @param unknown $params
     * @return string
     */
    public function getNetworkChart($params){
        $cardName = $params['name'];
        $nodeuuid = $params['nodeuuid'];
        $count = $params['count'];
        $flag = $params['flag'];
        $sqlTime = "select distinct unix_timestamp(monitor_time) as monitor_time from bd_network_monitor where node_uuid = ? ";
        $sqlParams = array($nodeuuid, $count);
        if($cardName && $cardName != "0"){
            $sqlTime .= " and card_name = ? ";
            $sqlParams = array($nodeuuid, $cardName, $count);
        }
        $sqlTime .= " and monitor_time < now()  order by unix_timestamp(monitor_time) desc limit ?";
        $dataTime = $this->dbSelect($sqlTime, $sqlParams);
        $timeArray = array();
        foreach ($dataTime as $time){
            $timeArray[] = $time['monitor_time'];
        }
        $timeStr = implode("','", $timeArray);
        $sqlNetwork = "select unix_timestamp(monitor_time) monitor_time, card_name, receive_size, transmit_size from bd_network_monitor where node_uuid = ? and unix_timestamp(monitor_time) in (' $timeStr ') ";
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
                if($data['monitor_time'] == $time){
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
                'timeDes' => date('H:i:s',$time),
                'time' => date('Y-m-d H:i:s',$time),
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
        $safeInfo = $this->getAccountSafe();
        $config = array(
            'VM_TYPE' => $this->getConfigVMType(),
            'VM_DES' => $this->getConfigVMDes(),
            'SYSTEMNAME' => Xphp::$_config['SYSTEM_INFO']['system_name'],
            'SOFTWARE' => Xphp::instance('SystemHandler', 'getSoftwareType'),
            'DB_TYPE' => $this->getConfigDBType(),
            'DB_DES' => $this->getConfigDBDes(),
            'IDLETIMEOUT' => $safeInfo['out_time'],
            'PASS_LENGTH' => $safeInfo['passlength'],
            'PASS_COMPLEXITY' => $safeInfo['passcomplexity'],
            'PERMISSION' => $_SESSION['permission'],
            'PERMISSION_ARR' => $_SESSION['permissionArr'],
            'HOSTNAME' => Xphp::$_config['HOSTNAME'],
            'TASK_TYPE' => $this->getConfigTaskType(),
            'TASK_TYPE_DES' => $this->getConfigTaskTypeDes(),
            'TASK_STATUS' => $this->getConfigTaskStatus(),
            'TASK_STATUS_DES' => $this->getConfigTaskStatusDes(),
            'MODULE_TYPE' => Xphp::$_config['MODULE_TYPE'],
            'MODULE_TYPE_DES' => Xphp::$_pfdes['MODULE_TYPE_DES'],
            'ENTERPRISE' => Xphp::$_config['SYSTEM_INFO']['enterprise'],
            'STORAGE_TYPE_DES' => Xphp::$_pfdes['STORAGETYPE'],
        );
        return json_encode($config);
    }
    /**
     * 得到虚拟化类型配置
     * @return multitype:unknown
     */
    private function getConfigVMType(){
        //name 是在JS里面的键名
        $name = array(
            'UNKNOWN', 'VMWARE', 'HYPERV', 'CITRIX', 'KVM', 'XEN', 'ORACLEVM', 'CLOUDVIEW', 'INCLOUD', 'VGATE',
            'NEOKYLIN', 'H3C', 'SANGFOR', 'SDCOS', 'FLEXCLOUD', 'OPENSTACK', 'FUSIONKVM', 'FUSIONXEN', 'WINSERVER', 'RHV',
            'DSERVER', 'CLOUDVIEWSVM', 'FLEXHCS', 'OSEASYVSERVER', 'INCLOUDKVM', 'WINDIY', 'ZSTACK', 'EASTEDVSERVER', 'XCPNG', 'OLVM',
            'XSKY', 'INCLOUDOPENSTACK', 'WINHONGKVM','SMARTX', 'SUGONCLOUDVIEW', 'INSPURCLOUDPLATFORM', 'EASYSTACK', 'FIBERHOMEOPENSTACK', 'CTSIOPENSTACK', 'AWCLOUD',
            'INSPURVVDK', 'ZVIRT', 'PROXMOX', 'XFUSIONKVM', 'XHERE', 'HOSTVM', 'HUAWEICBR', 'SANGFORVVDK', 'CLOUDVIEWKVM', 'REDVIRT',
            'ROSAVIRT', 'H3CCASCVD', 'OVIRT', 'LENOVOAIO', 'HUAWEICLOUDSTACK',
            100 => 'AWS', 'HUAWEICLOUD'
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
        foreach (Xphp::$_config['VMHYPERVISORDES'] as $hypervisor => $value){
            $newConfig[$hypervisor] = $value;
        }
        return $newConfig;
    }
    
    private function getConfigDBType(){
        return Xphp::$_config['DB_TYPE'];
    }
    
    /**
     * 得到虚拟化名称
     */
    private function getConfigDBDes(){
        $newConfig = array();
        foreach (Xphp::$_config['DB_TYPE_DES'] as $dbType => $value){
            $newConfig[$dbType] = $value;
        }
        return $newConfig;
    }

    /**
     * 获取任务类别
     * @return mixed
     */
    private function getConfigTaskType()
    {
        return Xphp::$_config['TASKTYPE'];
    }

    /**
     * 获取任务类别描述
     * @return mixed
     */
    private function getConfigTaskTypeDes()
    {
        return Xphp::$_pfdes['TASKTYPEDES'];
    }

    /**
     * 获取任务状态
     * @return mixed
     */
    private function getConfigTaskStatus()
    {
        return Xphp::$_config['TASKSTATUS'];
    }

    /**
     * 获取任务状态描述
     * @return mixed
     */
    private function getConfigTaskStatusDes()
    {
        return Xphp::$_pfdes['TASKSTATUSDES'];
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
    	$systemHandler = Xphp::instance("SystemHandler");
    	$tenantuuid = $systemHandler->getUserPermission();
    	if($name == 'homepage'){
    	    $url = "./content/platform/databackup_center.php";
    	    if($tenantuuid != ""){
    	        $url = "./content/platform/tenant_center.php";
    	    }
    	    $info = array(
    	        'url' => $url,
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
    		    $path = $p['path'];
    		    if($name == "vmbackup" && !empty($_SESSION['tenantuuid'])){
//    		      $path = "./content/vm/vm_backup.php";
    		      $path = "./content/vm/vmbackup.php";
    		    }
    			$info = array(
    			    'url' => $path,
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
    	// 如果是空的情况下，判断下是否是三权模式。
        if (empty($info) && $_SESSION['isThreePowers'] && in_array($_SESSION['userLevel'], [3, 4])) {
            // 三权模式下。安全员和审计员是没得首页的
            $info = array(
                'url' => './content/platform/' . ($_SESSION['userLevel'] == 3 ? 'users/users.php' : 'logs/logs.php')
            );
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
    private function getVmDetails($uuid, $status, $tasktype){
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
    		$dataComplete = $this->dbSelect($sqlComplete, array($uuid, Xphp::$_config['VmTaskStatus']['FINISH']));
    		$completeNum = intval($dataComplete[0]['complete_num']);
    		$vmRunNum = $completeNum."/".$vmNum;
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
    	$sqlvm = "select sum(src_timepoint_count) as timepoint_num from backup_copy_item_list where task_uuid = ?";
    	$dataVm  = $this->dbSelect($sqlvm, array($uuid));
    	$timepointNum = intval($dataVm[0]['timepoint_num']);
    	$sqlrunTime = "select count(id) as run_num from bd_history_task where task_uuid = ?";
    	$dataRun  = $this->dbSelect($sqlrunTime, array($uuid));
    	$runNum = intval($dataRun[0]['run_num']);
    	$info = array(
    	       'timepoint_num' => $timepointNum,
    		   'run_num' => $runNum
    	);
    	if($status == Xphp::$_config['TASKSTATUS']['RUNNING']){
    		$sqlComplete = "select sum(src_timepoint_count) as complete_num from backup_copy_item_list where task_uuid = ? and copy_status = ?";
    		$dataComplete = $this->dbSelect($sqlComplete, array($uuid, Xphp::$_config['VmTaskStatus']['FINISH']));
    		$completeNum = intval($dataComplete[0]['complete_num']);
    		$info = array(
    		        'timepoint_num' => $completeNum."/".$timepointNum,
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
                		bri.current_object_total_size, bri.current_object_completed_size, bri.speed, bri.speed_time, bri.total_object_size, bri.total_object_completed_size,unix_timestamp(bri.start_time) start_time
                from bd_task bt, bd_running_info bri
                where bt.task_uuid = bri.task_uuid and
                     bt.delete_flag = ? ";
		
    	$sql .= " and bt.user_uuid = ? order by field$taskStatus , bt.create_time desc, bt.task_name limit ? , ? ";
		$sqlParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid'], $start, $length);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$taskInfo = array();
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$utils = Xphp::instance('Utils');
    	$jobHandler = Xphp::instance('JobHandler');
    	foreach ($data as $d){
    		if( $d['task_type'] == Xphp::$_config['TASKTYPE']['ORCH_TASK'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']) continue;
    		$submodule = $jobHandler->getSubmoduleType($d['module_type'], $d['task_uuid']);
    		$totalSize= $utils->calSize($d['total_object_size']);
    		$completesize = $utils->calSize($d['total_object_completed_size']);
    		$vmInfo = array();
    		$fsInfo = array();
    		$dbInfo = array();
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
    			case Xphp::$_config['MODULE_TYPE']['DB']:
    			    $dbInfo = $this->getDbDetails($d['task_uuid'], intval($d['task_status']));
    			    break;
    			case Xphp::$_config['MODULE_TYPE']['OS']:
    			    //操作系统
    			    $osInfo = $this->getOsDetails($d['task_uuid'], intval($d['task_status']));
    			    break;
                case Xphp::$_config['MODULE_TYPE']['NAS']:
                    $nasInfo = $this->getNasDetails($d['task_uuid'], intval($d['task_status']));
                    break;
    			case Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']:
    				//副本|归档
    				$copyInfo =$this->getCopyDetails($d['task_uuid'], $d['task_status']);
    				break;
    		}
    		$timeInfo = $this->getJobNextstarttime($d['task_uuid']);
    		$nextTime = $timeInfo['next_time'];
    		$nextTimeDes = $timeInfo['next_time_des'];
    		if(intval($d['task_status']) == Xphp::$_config['TASKSTATUS']['STOPPED']){
    		    $nextTime = Xphp::$_config['TIMESPACE'];
    		    $nextTimeDes = Xphp::$_config['TIMESPACE'];
    		}
    		if(intval($d['start_time']) < time()){
    		    $startTime = Xphp::$_config['TIMESPACE'];
    		}else{
    		    $startTime = date('Y-m-d H:i:s', $d['start_time']);
    		}
    		$taskInfo[] = array(
    				'moduletype' => intval($d['module_type']),
    				'taskdes' => $ptDes['TASKTYPEDES'][$d['task_type']],
    				'tasktype' => intval($d['task_type']),
    				'uuid' => $d['task_uuid'],
    				'taskname' => $d['task_name'],
    		        'timedes' => $nextTimeDes,
    				'sdes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
    				'status' => intval($d['task_status']),
    				'href' => $jobHandler->getCurrentJobHref($d['module_type'], $d['task_type'], $d['task_uuid']),
    		        'speed' => $jobHandler->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
    				'vm_info' => $vmInfo,
    				'fs_info' => $fsInfo,
    		        'db_info' => $dbInfo,
    		        'os_info' => $osInfo,
                    'nas_info' => $nasInfo,
    		        'copy_info' => $copyInfo,
    				'intervalTime' => $jobHandler->getTimeInterval($d['start_time'], $d['task_status']),
    				'tasksize' => $completesize . "/" . $totalSize,
    				'progress' => $jobHandler->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
    		        'start_time' => $startTime,
    		        'next_start_time' => $nextTime
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
    	$sql = "select bht.module_type, bht.submodule_type, bht.task_type, bht.task_name, unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time, bht.error_code, bht.total_object_size, bht.details
                from bd_history_task bht ";
    	
    	$sql .= " where bht.user_uuid = ? or (bht.user_uuid = '' and bht.user_name = ?)";
    	$sql .= " order by bht.finish_time desc limit ? , ? ";
    	$sqlParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username'], $start, $length);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$taskInfo = array();
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$utils = Xphp::instance('Utils');
    	$jobHandler = Xphp::instance('JobHandler');
    	foreach ($data as $d){
    		$details = json_decode($d['details'], true);
    		$listLength = count($details);
    		$timepointNum = 0;
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
    		
    		foreach ($details as $detail){
    		    $timepointNum += intval($detail['timepoint_count']);
    		}
    		
    		if($d['module_type'] == Xphp::$_config['MODULE_TYPE']['FS']){
    			$fsNum = count($details['file_list']);
    		}
    		if($d['module_type'] == Xphp::$_config['MODULE_TYPE']['DB']){
    		    $dbNum = $listLength;
    		}
    		if($d['module_type'] == Xphp::$_config['MODULE_TYPE']['OS']){
    		    $osNum = $listLength;
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
    				'total_size' => $utils->calSize($d['total_object_size']),
    				'vm_num' => $vmNum,
    				'fs_num' => $fsNum,
    		        'db_num' => $dbNum,
    		        'os_num' => $osNum,
    		        'timepoint_num' => $timepointNum,
    				'intervalTime' =>$utils->secToTime($d['finish_time'] -$d['start_time']),
    		        'finish_time_en' => date('H:i:s, m-d-Y', $d['finish_time'])
    		);
    	}
    
    	return json_encode($taskInfo);
    }
    
    /**
     * 得到虚拟化中心概况/灾备中心
     */
    public function getSystemVms($params){
    	$hypervisor = $params['hypervisor'];
    	$sqlHost ="select count(distinct vh.host_uuid) as host_count from vm_host vh, vm_vcenter vv where vh.vcenter_uuid = vv.vcenter_uuid ";
    	if($hypervisor && $hypervisor != "0"){
    		$sqlHost .= " and vv.hypervisor_type = ? ";
    		$sqlHostParams = array($hypervisor);	
    	}
    	$dataHost = $this->dbSelect($sqlHost, $sqlHostParams);
    	
    	$sqlProtect = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv, bd_task bt, vm_machine vm 
                        where vml.vcenter_uuid = vm.vcenter_uuid and vml.vm_uuid = vm.vm_uuid and bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? ";
		$sqlProtectParams = array(Xphp::$_config['TASKTYPE']['BACKUP']);
		if($hypervisor && $hypervisor != "0"){
    		$sqlProtect .= " and vv.hypervisor_type = ? ";
    		$sqlProtectParams = array(Xphp::$_config['TASKTYPE']['BACKUP'], $hypervisor);
    	}
    	$dataProtect = $this->dbSelect($sqlProtect, $sqlProtectParams);
    	
    	$sqlAll = "select count(distinct vt.vcenter_uuid, vt.uuid) as all_vms from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.display_mode = ? and vt.type = ? ";
    	$sqlAllParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
    	if($hypervisor && $hypervisor != "0"){
    		$sqlAll .= " and vv.hypervisor_type = ? ";
    		$sqlAllParams =array( Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM'], $hypervisor);	
    	}
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
    public function getSystemStorage($params){
    	$storageuuid = $params['storageuuid'];
    	$sql = "select count(bsr.storage_uuid) as storage_count, sum(bsr.total_size) as total_size, sum(bsr.free_size) as free_size from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ";
    	$sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
    	if($storageuuid && $storageuuid !="0"){
    		$sql .= " and bsr.storage_uuid = ?";
    		$sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $storageuuid);
    	}
    	$data = $this->dbSelect($sql,$sqlParams);
    	$utils = Xphp::instance('Utils');
    	$totalSize = intval($data[0]['total_size']);
    	
    	$freeSize = intval($data[0]['free_size']);
    	if($totalSize > 0){
    	    $percent = round(($freeSize/$totalSize)*100, 1);
    	}else{
    	    $percent = 0;
    	}
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
    /**
     * 获取nas模块当前任务详情信息
     * @param string $taskuuid
     * @param int $status
     * @param int $tasktype
     */
    private function getNasDetails($taskuuid, $status){
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
    
    /**
     * 获取数据库模块当前任务详情信息
     * @param string $taskuuid
     * @param int $status
     * @param int $tasktype
     */
    private function getDbDetails($taskuuid, $status){
        $sqlvm = "select count(database_id) as db_num from db_list where task_uuid = ?";
        $dataVm  = $this->dbSelect($sqlvm, array($taskuuid));
        $dbNum = intval($dataVm[0]['db_num']);
        $sqlrunTime = "select count(id) as run_num from bd_history_task where task_uuid = ?";
        $dataRun  = $this->dbSelect($sqlrunTime, array($taskuuid));
        $runNum = intval($dataRun[0]['run_num']);
        $info = array(
            'db_num' => $dbNum,
            'run_num' => $runNum
        );
        
        return $info;
    }
    
    /**
     * 获取操作系统当前任务详情信息
     * @param string $taskuuid
     * @param int $status
     * @param int $tasktype
     */
    private function getOsDetails($taskuuid, $status){
        $sql = "select count(agent_uuid) as agent_num from bd_task_agent_list where task_uuid = ?";
        $data  = $this->dbSelect($sql, array($taskuuid));
        $agentNum = intval($data[0]['agent_num']);
        $sqlrunTime = "select count(id) as run_num from bd_history_task where task_uuid = ?";
        $dataRun  = $this->dbSelect($sqlrunTime, array($taskuuid));
        $runNum = intval($dataRun[0]['run_num']);
        $info = array(
            'agent_num' => $agentNum,
            'run_num' => $runNum
        );
        
        return $info;
    }
    
    /**
     * 获取任务下次开始时间
     * @param unknown $taskuuid
     */
    private function getJobNextstarttime($taskuuid){
        $sql ="select unix_timestamp(bs.next_start_time) next_start_time from bd_task bt, bd_strategy bs where bt.strategy_id = bs.strategy_id and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nextTime = Xphp::$_config['TIMESPACE'];
        $nextTimeDes = Xphp::$_config['TIMESPACE'];
        if(!empty($data)){
            $utils = Xphp::instance('Utils');
            $nowTime = time();
            if($data[0]['next_start_time'] < $nowTime){
                $nextTime = Xphp::$_config['TIMESPACE'];
                $nextTimeDes = Xphp::$_config['TIMESPACE'];
            }else{
                $nextTime = date('Y-m-d H:i:s', $data[0]['next_start_time']);
                $nextTimeDes = $utils->formatDate($data[0]['next_start_time']);
            }
        }
        
        $info = array(
            'next_time' => $nextTime,
            'next_time_des' => $nextTimeDes
            
        );
        
        
        return $info;
    }
    
    /**
     * 获取当前备份虚拟机和服务器列表
     * @param unknown $params
     */
    public function getCurrentBackupMachines($params){
        $length = $params['length'];
        $info = array();
        //获取当前备份的虚拟机
        $sqlVm = "select vml.vm_uuid, vml.vm_name, bt.create_time from bd_task bt, vm_machine_list vml where bt.task_uuid = vml.task_uuid and bt.module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataVm = $this->dbSelect($sqlVm, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_user['useruuid']));
        
        if(!empty($dataVm)){
            foreach ($dataVm as $d){
                $info[] = array(
                    "uuid" => $d['vm_uuid'],
                    "name" => $d['vm_name'],
                    "create_time" => $d['create_time'],
                    "type" => "vm",
                    "typeDes" => Xphp::$_lang['UI_VCENTER_MACHINE']
                );
            }
        }
        
        //获取当前备份的文件|数据库代理
        $sqlAgent = "select agent_uuid, agent_name, ip, register_time, agent_type from bd_agent where agent_type != 4 and user_uuid = ?";
        $dataAgent = $this->dbSelect($sqlAgent, array(Xphp::$_user['useruuid']));
        if(!empty($dataAgent)){
            foreach ($dataAgent as $agent){
                $typeDes = Xphp::$_lang['UI_PLATFORM_FILE'];
                if($agent['agent_type'] == 1){
                    $typeDes = Xphp::$_lang['UI_PLATFORM_DB'];
                }
                $info[] = array(
                    "uuid" => $agent['agent_uuid'],
                    "name" => $agent['agent_name']."(".$agent['ip'].")",
                    "create_time" => $agent['register_time'],
                    "type" => "agent",
                    "typeDes" => $typeDes
                );
            }
        }
        
        //获取当前保护数据库实时主机
        $sqlHost = "select host_uuid, host_name, ip, register_time from cdp_db_host where user_uuid = ? ";
        $dataHost = $this->dbSelect($sqlHost, array(Xphp::$_user['useruuid']));
        if(!empty($dataHost)){
            foreach ($dataHost as $host){
                $info[] = array(
                    "uuid" => $host['host_uuid'],
                    "name" => $host['host_name']."(".$agent['ip'].")",
                    "create_time" => $host['register_time'],
                    "type" => "agent",
                    "typeDes" => Xphp::$_lang['UI_PLATFORM_RT_HOST']
                );
            }
        }
        
        //根据时间降序排序
        $utils = Xphp::instance('Utils');
        $machineList = $utils->arraySort($info, 'create_time', '', 0, $length);
        
        return json_encode($machineList);
        
    }
    
    /**
     * 获取最近备份虚拟机和服务器列表
     * @param unknown $params
     */
    public function getHistoryBackupMachines($params){
        $days = $params['days'];    //最近几天
        //虚拟机|文件代理|数据库代理|数据库实时代理
        $sql = "select unix_timestamp(bht.finish_time) finish_time, bht.module_type, bht.task_type, bht.details from bd_history_task bht where DATE_SUB(CURDATE(), INTERVAL ".$days." DAY) <= date(bht.finish_time) and bht.task_type in (1, 21, 28) and bht.user_uuid = ? 
                 or (bht.user_uuid = '' and bht.user_name = ?) ";
        $sql .= "  order by finish_time desc";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'], Xphp::$_user['username']));
        $info = array();
        if(!empty($data)){
            $name = array();
            foreach ($data as $d){
                $moduleType = $d['module_type'];
                $details = json_decode($d['details'], true);
                $finishTime = date('Y-m-d H:i:s', intval($d['finish_time']));
                switch($moduleType){
                    case Xphp::$_config['MODULE_TYPE']['VM']:           //虚拟机
                        if(!empty($details)){
                           foreach ($details as $vm){
//                                if($vm['end_transfer_time']){
//                                    $finishTime = $vm['end_transfer_time'];
//                                }
                               if(!in_array($vm['vm_name'], $name)){
                                   $info[] = array(
                                       'name' => $vm['vm_name'],
                                       'type' => "vm",
                                       'finish_time' => $finishTime,
                                       "typeDes" => Xphp::$_lang['UI_VCENTER_MACHINE']
                                   );
                                   $name[] = $vm['vm_name'];
                               }
                           }
                        }
                        break;
                    case Xphp::$_config['MODULE_TYPE']['FS']:           //文件
                        $type = "agent";
                        if(!in_array($details['src_agent_name'], $name)){
                            $info[] = array(
                                'name' => $details['src_agent_name'],
                                'type' => "agent",
                                'finish_time' => $finishTime,
                                "typeDes" => Xphp::$_lang['UI_PLATFORM_FILE']
                            );
                            $name[] = $details['src_agent_name'];
                        }
                        break;
                    case Xphp::$_config['MODULE_TYPE']['DB']:           //数据库
                        $agentName = $this->getAgentNameByUUID($details[0]['agent_uuid']);
                        if(!in_array($agentName, $name)){
                            $info[] = array(
                                'name' => $agentName,
                                'type' => "agent",
                                'finish_time' => $finishTime,
                                "typeDes" => Xphp::$_lang['UI_PLATFORM_DB']
                            );
                            $name[] = $agentName;
                        }
                        break;
                    case Xphp::$_config['MODULE_TYPE']['OEM_DBCDP']:   //数据库实时
                        
                        break;
                    
                }
                
            }
        }
        
        return json_encode($info);
    }
    
    
    
    /**
     * 获取当前用户备份系统概览信息统计
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function getTenantSurvey($params){
        $info = array(
            "protect_vm" => $this->getUserProtectVM(),
            "protect_server" => $this->getUserProtectServer(),
            "current_task" => $this->getCurrentTaskNum(),
            "history_task" => $this->getHistoryTaskNum(),
            "backup_data" => $this->getUserStorageData()
        );
        
        
        return json_encode($info);
    }
    
    /**
     * 获取当前用户保护虚拟机个数
     * @return number
     * @author luokai@vinchin.com
     */
    public function getUserProtectVM(){
        $sql = "select count(distinct vml.machine_id) as protect_num from bd_task bt, vm_machine_list vml where bt.task_uuid = vml.task_uuid and bt.task_type = ? ";
        $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP']);
        //如果不是admin
        if(Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
            
        }else{
            $sql .= " and bt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        }
        
        $dataCount = $this->dbSelect($sql, $sqlParams);
        
        $count = 0;
        if(!empty($dataCount)){
            $count = intval($dataCount[0]['protect_num']);
        }
        
        return $count;
    }
    
    /**
     * 获取用户保护服务器
     * @return number
     * @author luokai@vinchin.com
     */
    public function getUserProtectServer(){
        //获取文件和数据库代理个数
        $sql = "select count(agent_uuid) as agent_num from bd_agent ";
        $sqlParams = array();
        //如果不是admin
        if(Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
            
        }else{
            $sql .= " where agent_type != 4 and user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $dataCount = $this->dbSelect($sql, $sqlParams);
        $count = 0;
        if(!empty($dataCount)){
            $count = $count + intval($dataCount[0]['agent_num']);
        }
        
        //获取数据库实时主机
        $sqlHost = "select count(host_uuid) as host_num from cdp_db_host ";
        $sqlParams = array();
        //如果不是admin
        if(Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
            
        }else{
            $sql .= " where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $dataHost = $this->dbSelect($sqlHost, $sqlParams);
        if(!empty($dataHost)){
            $count = $count + intval($dataHost[0]['host_num']);
        }
        
        return $count;
    }
    
    /**
     * 获取用户当前任务数量
     * @return number
     * @author luokai@vinchin.com
     */
    public function getCurrentTaskNum(){
        $sql = "select count(task_uuid) as total from bd_task ";
        $sqlParams = array();
        //如果不是admin
        if(Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
            
        }else{
            $sql .= " where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $dataCount = $this->dbSelect($sql, $sqlParams);
        $count = 0;
        if(!empty($dataCount)){
            $count = $count + intval($dataCount[0]['total']);
        }
        
        return $count;
    }
    
    /**
     * 获取用户历史任务数量记录
     * @return number
     * @author luokai@vinchin.com
     */
    public function getHistoryTaskNum(){
        $sql = "select count(bht.id) as total from bd_history_task bht ";
        $sqlParams = array();
        $sql .= " where bht.user_uuid = ? or (bht.user_uuid = '' and bht.user_name = ?)";
        $sqlParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
        $dataCount = $this->dbSelect($sql, $sqlParams);
        $count = 0;
        if(!empty($dataCount)){
            $count = $count + intval($dataCount[0]['total']);
        }
        
        return $count;
    }
    
    /**
     * 获取代理服务器名字
     * @param string $agentuuid
     */
    public function getAgentNameByUUID($agentuuid){
        $sql = "select agent_name from bd_agent where agent_uuid = ? ";
        $data =$this->dbSelect($sql, array($agentuuid));
        $name = "";
        if(!empty($data)){
            $name = $data[0]['agent_name'];
        }
        
        return $name;
    }
    
    /**
     * 获取安全配置信息
     * @return array|int[]
     */
    public function getAccountSafe(){
        $sql = "select login_timeout, login_failure, pass_timeout, pass_length, pass_complexity,login_failed_lock_time from bd_account_safe";

        $where = ' where create_user_level = ?';
        $userLevel = 0;
        if ($_SESSION['isThreePowers']) {
            // 是三权模式 那么就必须按照当前用户级别来读取配置
            // level 3配置level 2，level 2配置 level 大于3的 ，其余的读取level为0的默认的配置
            if ($_SESSION['userLevel'] > 3) {
                $userLevel = 2;
            } elseif ($_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin']) {
                $userLevel = 3;
            }
        }

        $data = $this->dbSelect($sql.$where, [$userLevel]);

        if(!empty($data)){
            if ($_SESSION['isThreePowers']) {
                $info = array(
                    'out_time' => min($data[0]['login_timeout'], 600),
                    'faild_count' => min($data[0]['login_failure'], 5),
                    'password_time' => min($data[0]['pass_timeout'], 7),
                    'passlength' => max($data[0]['pass_length'], 8),
                    'passcomplexity' => max($data[0]['pass_complexity'], 2),
                    'faild_lock_time' => max($data[0]['login_failed_lock_time'], 1800),
                );
            } else {
                $info = array(
                    'out_time' => $data[0]['login_timeout'],
                    'faild_count' => $data[0]['login_failure'],
                    'password_time' => $data[0]['pass_timeout'],
                    'passlength' => $data[0]['pass_length'],
                    'passcomplexity' => $data[0]['pass_complexity'],
                    'faild_lock_time' => $data[0]['login_failed_lock_time'],
                );
            }
            return $info;
        }

        return array(
            'out_time' => 600,
            'faild_count' => 5,
            'password_time' => 7,
            'passlength' => 8,
            'passcomplexity' => 2,
            'faild_lock_time' => 1800,
        );
    }
    
    /**
     * 得到数据库代理
     */
    public function getSystemDbs(){
        
        //获取所有代理个数
        $sqlHost ="select count(distinct agent_uuid) as host_count from bd_agent where agent_type = 1";
        $dataHost = $this->dbSelect($sqlHost, array());
        
        //获取受保护代理个数
        $sqlProtect =  "select count(distinct bt.agent_uuid) as protect_num from bd_task bt left join bd_agent ba on bt.agent_uuid = ba.agent_uuid where ba.agent_type = 1 and bt.module_type = ? and bt.task_type = ?";
        $dataProtect = $this->dbSelect($sqlProtect, array(Xphp::$_config['MODULE_TYPE']['DB'], Xphp::$_config['TASKTYPE']['DB_BACKUP']));
        
        $allHost = intval($dataHost[0]['host_count']);
        
        $protectHost = intval($dataProtect[0]['protect_num']);
        
        if($allHost > 0){
            $percent = round(($protectHost/$allHost)*100, 1);
        }else{
            $percent = 0;
        }
        $info = array(
            "host_num" => $allHost,
            "protect_host_num" => $protectHost,
            'percent' => $percent
        );
        
        return json_encode($info);
    }
    
    /**
     * 得到文件代理
     */
    public function getSystemFs(){
        //获取所有代理个数
        $sqlHost ="select count(distinct agent_uuid) as host_count from bd_agent where agent_type = 0 and (online_flag = 1 or register_flag = 1) ";
        $dataHost = $this->dbSelect($sqlHost, array());
        
        //获取受保护代理个数
        $sqlProtect =  "select count(distinct bt.agent_uuid) as protect_num from bd_task bt left join bd_agent ba on bt.agent_uuid = ba.agent_uuid where ba.agent_type = 0 and bt.module_type = ? and bt.task_type = ? ";
        $dataProtect = $this->dbSelect($sqlProtect, array(Xphp::$_config['MODULE_TYPE']['FS'], Xphp::$_config['TASKTYPE']['BACKUP']));
        
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
        $info = array(
            "host_num" => $allHost,
            "protect_host_num" => $protectHost,
            'percent' => $percent
        );
        
        
        return json_encode($info);
    }
    
    /**
     * 得到当前实际备份数据大小
     */
    private function getUserStorageData(){
        $sql = "select sum(write_size) as total_size from bd_backup_timepoint ";
        $sqlParams = array();
        if(Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
            
        }else{
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        
        $total = intval($data[0]['total_size']);
        
        $utils = Xphp::instance('Utils');
        $info = $utils->calSizeToValueAndUnit($total, true);
        return $info;
    }


    /**
     * 获得大屏可视化的语言
     */
    public function getScreenTime(){
        $useruuid = Xphp::$_user['useruuid'];
        $sql = "select language from bd_user where user_uuid = ?";
        $result = $this-> dbSelect($sql, array($useruuid));
        return json_encode($result[0]['language']);
    }

    /**
     * 操作存储权限判断-分配的资源
     * @param string $sourceUuid 资源uuid,多个以逗号隔开
     * @param int    $souceType  分配的资源类型标识 详见resource的 RESOURCE_TYPE 枚举
     * @param string $auth       权限标识，详见 user的 USER_AUTH 枚举
     * @return json|bool
     */
    public function checkAuthBySourceUuid(string $sourceUuid, int $souceType, string $auth = 'resmanagement')
    {

        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 非三权模式，并且不是超级管理员也不是全局观察者操作，也只能操作的资源
            $check = $utils->v1_auth_check_operate(
                $sourceUuid,
                $auth,
                $souceType
            );

            if (empty($check)) {
                return $this->muOpResult(false, Xphp::$_lang['UI_PUBLIC_TIPS'],  Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'info', 0);
            }
        }
        return true;
    }

    /**
     * 操作存储权限判断-非分配的资源
     * @param string $userUuid 资源所对应的用户uuid,多个以逗号隔开
     * @param string $auth     权限标识，详见 user的 USER_AUTH 枚举
     * @return json|bool
     */
    public function checkAuthByUserUuid(string $userUuid, string $auth)
    {

        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 非三权模式，并且不是超级管理员也不是全局观察者操作，也只能操作的资源
            if (empty($userUuid)) {
                return $this->muOpResult(false, Xphp::$_lang['UI_PUBLIC_TIPS'],  Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'info', 0);
            }
            $userUuidArr = $utils->v1_auth_get_users($auth, 2);
            $idArr = explode(',', $userUuid);
            $idArr = array_filter($idArr);
            if (count($idArr) == 1) {
                $sql = "SELECT CASE WHEN '{$idArr[0]}' IN (
                            {$userUuidArr}
                        ) THEN 1 ELSE 0 END AS result";
            } else {
                // 多个
                $subSql = '';
                $i = 0;
                foreach ($idArr as $item) {
                    if ($i == 0) {
                        $subSql = "SELECT '{$item}' AS user_uuid ";
                    } else {
                        $subSql .= "UNION ALL SELECT '{$item}' ";
                    }
                    $i++;
                }
                $sql = "WITH target_users AS (
                            {$userUuidArr}
                        ),
                        check_list AS (
                            {$subSql}
                        )
                        SELECT 
                            CASE WHEN COUNT(t.user_uuid) = COUNT(*) THEN 1 ELSE 0 END AS result
                        FROM check_list cl
                        LEFT JOIN target_users t ON cl.user_uuid = t.user_uuid";
            }

            $check = $this->dbSelect($sql);
            if (empty($check) || $check[0]['result'] != 1) {
                return $this->muOpResult(false, Xphp::$_lang['UI_PUBLIC_TIPS'],  Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'info', 0);
            }
        }
        return true;
    }

}
?>