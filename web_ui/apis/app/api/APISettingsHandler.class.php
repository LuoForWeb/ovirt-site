<?php
/******************************************* 
** 系统配置处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APISettingsHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/settings/license/fingerprint" => array(
            'GET' => 'getLicenseFingerPrint'
        ),
    
        "/settings/license" => array(
            'POST' => 'systemLicense'
        ),
        
        "/settings/license/details" => array(
            'GET' => 'licenseDetails'
        ),
    	"/settings/vmused" => array(
    		'GET' => 'getUsedVm'
    	),
    	"/settings/storageused" => array(
    		'GET' => 'getUsedStorage'
    	),
    	"/settings/message_push" => array(
    		'POST' => 'messagePush',
    		'GET' => 'getMessageDetails',
    	),
        "/settings/survey" => array(
            'GET' => 'getSystemSurvey'
        ),
    		
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 获取机器指纹路由控制
     */
    protected function getLicenseFingerPrint(){
        //定义方法版本
        $version = array(
            "v1" => "getLicenseFingerPrintV1",
            "v2" => "getLicenseFingerPrintV2"
            
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 系统授权路由控制
     */
    protected function systemLicense(){
        //定义方法版本
        $version = array(
            "v1" => "systemLicenseV1",
            "v2" => "systemLicenseV2"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取授权详细信息路由控制
     */
    protected function licenseDetails(){
        //定义方法版本
        $version = array(
            "v1" => "licenseDetailsV1",
            "v2" => "licenseDetailsV2"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 获取虚拟机受保护情况
     */
    protected function getUsedVm(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getUsedVmV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取存储使用情况
     */
    protected function getUsedStorage(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getUsedStorageV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取存储使用情况
     */
    protected function messagePush(){
    	//定义方法版本
    	$version = array(
    			"v1" => "messagePushV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取存储使用情况
     */
    protected function getMessageDetails(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getMessageDetailsV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 获取系统运行情况
     */
    protected function getSystemSurvey(){
        //定义方法版本
        $version = array(
            "v1" => "getSystemSurveyV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
	
    /**********getLicenseFingerPrint**********/
    private function getLicenseFingerPrintV1(){
    	$opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        $config = require_once ROOT_PATH.'api/xphp/conf/config.php';
		// 判读下配置文件是否是加密过
		if (Xphp::isEncryptedArray($config)) {
		    // 对数组进行解密
            $config = Xphp::decryptArrayValues($config);
        }
        $data = array(
    		"thumbprint" => $msg,
    		"version" => $config['SYSTEM_INFO']['enterprise']. ' ' .$config['SYSTEM_INFO']['version']
        );
        if($result){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=". Xphp::$_config['LISENCE_INFO']['thumbprintFileName']);
            return $this->apiResponse(true, "API_CODE_SETTINGS_GET_FINGERPRINT", $data);
        }else{
            $operate = $this->opcodeHandler->getOpcodeDes(0);
            return $this->apiResponse(false, "API_CODE_SETTINGS_GET_FINGERPRINT", '', $mbResult);
        }
    }
    
    /**********getLicenseFingerPrint**********/
    private function getLicenseFingerPrintV2(){
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        $cdpKey = "";
        $dbtimingKey = "";
        
        //添加数据库实时,先判断是否有
        $cmdStr = "ps aux|grep lzbackupsys";
        exec($cmdStr, $info);
        if(count($info) > 2){
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            $cdpKey = $dbCDPHandler->getEnvStr();
        }
        //添加数据库定时,先判断是否有
        $cmdStr = "ps aux|grep daserver";
        exec($cmdStr, $info);
        $dbTimingHandler = Xphp::instance('DBTimingHandler');
        if(count($info) > 2){
            if(!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime){
                $dbTimingHandler->getDBAuth(); //获取datapp认证
            }
            $thumbprint = $dbTimingHandler->getDBThumbprint(); //获取datapp授权指纹信息
            $dbtimingKey = $thumbprint;
        }
        $config = require_once ROOT_PATH.'api/xphp/conf/config.php';
		// 判读下配置文件是否是加密过
		if (Xphp::isEncryptedArray($config)) {
		    // 对数组进行解密
            $config = Xphp::decryptArrayValues($config);
        }
        $data = array(
            "thumbprint" => $msg,
            "version" => $config['SYSTEM_INFO']['enterprise']. ' ' .$config['SYSTEM_INFO']['version'],
            "cdp_thumbprint" => $cdpKey,
            "dbtiming_thumbprint" => $dbtimingKey
        );
        if($result){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=". Xphp::$_config['LISENCE_INFO']['thumbprintFileName']);
            return $this->apiResponse(true, "API_CODE_SETTINGS_GET_FINGERPRINT", $data);
        }else{
            $operate = $this->opcodeHandler->getOpcodeDes(0);
            return $this->apiResponse(false, "API_CODE_SETTINGS_GET_FINGERPRINT", '', $mbResult);
        }
    }
    
    /**********systemLicense**********/
    private function systemLicenseV1(){
    	if(strlen($this->params['key']) != 684){
    		return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE_ERROR");
    	}
    	$msg = array('license' => $this->params['key']);
        $opName = 'PT_LICENSE_OP_ADD_LICENSE';
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), false);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        //返回结果到UI
        if($result){
            $usersHandler = Xphp::instance('UsersHandler');
            $usersHandler -> updateUserPermissionAndSoftwareType();
            return $this->apiResponse(true, "API_CODE_SETTINGS_LICENSE");
        }else{
            return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE", '', $mbResult);
        }
    }
    
    /**********systemLicense**********/
    private function systemLicenseV2(){
        $utils = Xphp::instance('Utils');
        $key = $utils->decrypt($this->params['key']);
        $key = json_decode($key, true);
        //vinchin license,数据库定时license,数据库实时license
        $filetype = $key['filetype'];
        $vinchinLic = $key['v'];
        $dbtimingLic = $key['d'];
        $dbcdpLic = $key['c'];
        $serviceLic = $key['s'];
        $opName = 'PT_LICENSE_OP_ADD_LICENSE';
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        
        //如果只是上传的服务授权文件,直接到服务授权文件处理.
        if($filetype == Xphp::$_config['LISENCE_INFO']['filetype']['service']){
            $result = $this->serviceLisenceKey($serviceLic, $operate);
            if($result){
                return $this->apiResponse(true, "API_CODE_SETTINGS_LICENSE");
            }
            else{
                return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE", '处理授权文件失败!');
            }
        }
        //提交数据库实时
        $cmdStr = "ps aux|grep lzbackupsys";
        exec($cmdStr, $info);
        if(count($info) > 2){
            //授权数据库实时,先看是否有
            if(!empty($dbcdpLic)){
                $dbCDPHandler = Xphp::instance('DbCDPHandler');
                $result = $dbCDPHandler->upLicenseFile($dbcdpLic);
                if(!$result['result']){
                    $mbResult = array(
                        'result' => false,
                        'errorCode' => $result['errorCode']
                    );
                    //授权失败
                    return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE", "", $mbResult);
                }
            }
        }
        //提交数据库定时
        $cmdStr = "ps aux|grep daserver";
        exec($cmdStr, $info);
        $dbTimingHandler = Xphp::instance('DBTimingHandler');
        if(count($info) > 2){
            if(!empty($dbtimingLic)){
                if(!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime){
                    $dbTimingHandler->getDBAuth(); //获取datapp认证
                }
                $result = $dbTimingHandler->uploadDBLicense($dbtimingLic); //授权datapp
                $result = json_decode($result, true);
                if(0 != $result['code']){
                    //授权失败
                    $mbResult =  array(
                        "result" => false,
                        "errorCode" => 60000 + $result['code']
                    );
                    return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE", "", $mbResult);
                }
            }
        }
        $msg = array('license' => $key['v']);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), false);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        $result = true;
        if($result){
            $extension = $this->getExtensionLicense();
            if(!empty($extension['f'])){
                session_start();
                $_SESSION['authfun'] = $extension['f'];
                session_commit();
            }
            $this->systemLog('PT_SYSTEMLOG_DESC_KEY_UPDATE_LICENSE');
            //系统授权成功后,处理服务授权
            $result = $this->serviceLisenceKey($serviceLic, $operate);
            if(!$result){
                return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE", '处理授权文件失败!', 'error');
            }
            $usersHandler = Xphp::instance('UsersHandler');
            $usersHandler -> updateUserPermissionAndSoftwareType();
            return $this->apiResponse(true, "API_CODE_SETTINGS_LICENSE");
        }else{
            return $this->apiResponse(false, "API_CODE_SETTINGS_LICENSE", '', $mbResult);
        }
    }
    
    /**********licenseDetails**********/
    private function licenseDetailsV1(){
    	$systemStatus = $this->getSystemAuthorizationStatus();
    	$systemStatusDes = $this->getSystemAuthorizationDes($systemStatus);
    	
    	return $this->getAuthorizedInfo(intval($systemStatus), $systemStatusDes);
    }
    
    /**********licenseDetails**********/
    private function licenseDetailsV2(){
        $systemStatus = $this->getSystemAuthorizationStatus();
        $systemStatusDes = $this->getSystemAuthorizationDes($systemStatus);
        
        return $this->getAuthorizedInfoV2(intval($systemStatus), $systemStatusDes);
    }
    
    
    /**********getUsedVm**********/
    private function getUsedVmV1(){
    	//获取所有虚拟机
        $sql = "select count(tree_id) as total from vm_tree where display_mode = ? and type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']));
    	$allVM = intval($data[0]['total']);
    	//受保护虚拟机
    	$sql = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv, bd_task bt, vm_machine vm
                        where vml.vcenter_uuid = vm.vcenter_uuid and vml.vm_uuid = vm.vm_uuid and bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? ";
    	$data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP']));
    	$inBackupVM = intval($data[0]['protect_vms']);
    	
    	$info = array(
    		"all_vm" => $allVM,
    		"inbackup_vm" => $inBackupVM,
    	    "not_inbackup_vm"=> $allVM - $inBackupVM
    	);
    	return $this->apiResponse(true, "API_CODE_SETTINGS_GET_VM_DISTRIBUTION", $info);
    }
    
    /**********getUsedStorage**********/
    private function getUsedStorageV1(){
        //获取系统授权总容量
        $sql = "select storage_count from bd_license ";
        $data = $this->dbSelect($sql);
        $systemStorage = intval($data[0]['strorage_count']);
        
    	$sql = "select sum(total_size) as total, sum(free_size) as free from bd_storage_resource where lan_free_flag = ?";
    	$data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET']));
    	$info = array();
    	foreach ($data as $d){
    		$used = $d['total'] - $d['free'];
    		$info = array(
    				"total_storage" => intval($d['total']),
    				"free_storage" => intval($d['free']),
    				"used_storage" => intval($used),
    		        "system_storage" => $systemStorage
    		);
    	}
    	return $this->apiResponse(true, "API_CODE_SETTINGS_GET_STORAGE_USAGE", $info);
    }
   
    /**********messagePush**********/
    private function messagePushV1(){
    	$username = $this->params['user_name'];
    	$password = $this->params['password'];
    	$protocol = $this->params['protocol'];
    	$this->apiParamsCheck($username, $password, $protocol);
    	//连接测试
    	$this->messagePushTest($this->params);
    	 
    	$domain = $this->params['domain'];
    	$port = $this->params['port'];
    	$mode = $this->params['mode'];
    	$pushtype = $this->params['push_type'];
    	$utils = Xphp::instance('Utils');
    	$pushflag = $this->params['push_flag'];
    	$password = $utils->encrype($password);
    	
    	$sql = "update bd_message_push set user_name = ?, password = ?, protocol = ?, ip_domain = ?, port = ?, push_flag = ?, mode = ?, push_type = ?";
    	$result = $this->dbExec($sql, array($username, $password,  $protocol, $domain, $port, $pushflag, $mode, $pushtype));
    	
    	return $this->apiResponse(true, "API_CODE_SETTINGS_MESSAGE_PUSH");
    }
    
    /**********getMessageDetails**********/
    private function getMessageDetailsV1(){
    	$utils = Xphp::instance('Utils');
    	$sql = "select user_name, password, protocol, ip_domain, port, mode, push_flag, push_type from bd_message_push";
    	$data = $this->dbSelect($sql, array());
    	$info = array(
    			'user_name' => $data[0]['user_name'],
    			'password' => $utils->decrypt($data[0]['password']),
    			'protocol' => intval($data[0]['protocol']),
    			'domain' => $data[0]['ip_domain'],
    			'port' => $data[0]['port'],
    			'push_flag' => intval($data[0]['push_flag']),
    			'push_type' => intval($data[0]['push_type']),
    			'mode' => intval($data[0]['mode']),
    	);
    	return $this->apiResponse(true, 'API_CODE_SETTINGS_GET_MESSAGE_PUSH_INFO', $info);
    }
    
    /**********getSystemSurvey**********/
    private function getSystemSurveyV1(){
        $info = array(
            "server_run_time" => $this->getSystemRunningTime(),
            "accumulated_protect_data" => $this->getAccumulatedData(),
            "system_time" => intval($this->getSystemTime())
        );
        return $this->apiResponse(true, 'API_CODE_SETTINGS_GET_SYSTEM_SURVEY', $info);
    }
    
    
    /**********************************其他工具方法************************************/
    private $opcodeHandler;
    
    function __construct(){
    	//初始化消息等级
    	$this->opcodeHandler = Xphp::instance('PFOpcodePrivate');
    }
    
    /**
     * 得到某个模块的授权信息
     * @param string $moduleName   模块名
     * @return array [total, used, valid]
     */
    public function getOneModuleLisenceInfo($moduleName){
    	$moduleInfo = array(
    			'total' => 0,
    			'used' => 0,
    			'valid' => 0,
    	);
    	$moduleNames = array('file', 'vm', 'oracle', 'sqlserver', 'os', 'cdp', 'desktop', 'host', 'cpu', 'storage', 'node');
    	if(!in_array($moduleName, $moduleNames)){
    		//非法请求
    		return $moduleInfo;
    	}
    
    	$total = $this->getModulesLisenceTotal();
    	if($total['authFlag'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
    		//未授权或授权异常
    		return $moduleInfo;
    	}
    	$used = $this->getModulesLisenceUsed();
    	$valid = $this->getModulesLisenceValid();
    	$moduleInfo = array(
    			'total' => intval($total['module'][$moduleName]),
    			'used' => $used[$moduleName],
    			'valid' => $valid[$moduleName],
    	);
    	return $moduleInfo;
    }
    
    
    /**
     * 得到所有模块授权总数
     */
    public function getModulesLisenceTotal(){
    	$authFlag = $this->getSystemAuthorizationStatus();
    	$total = array(
    			"authFlag" => $authFlag
    	);
    	if($authFlag == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
    		//如果是已授权状态
    		$sql = "select file_max_num, vm_max_num, mysql_max_num, oracle_max_num, sqlserver_max_num, os_max_num,
                    cdp_max, desktop_max, cpu_count, storage_count, node_count from bd_license";
    		$data = $this->dbSelect($sql);
    		foreach ($data as $d){
    			$total['module'] = array(
    					'file' => $d['file_max_num'],
    					'vm' => $d['vm_max_num'],
    					'mysql' => $d['mysql_max_num'],
    					'oracle' => $d['oracle_max_num'],
    					'sqlserver' => $d['sqlserver_max_num'],
    					'os' => $d['os_max_num'],
    					'cdp' => $d['cdp_max'],
    					'desktop' => $d['desktop_max'],
    					'host' => $d['vm_max_num'],     //根据类型和虚拟机共用
    					'cpu' => $d['cpu_count'],
    					'storage' => $d['storage_count'],
    					'node' => $d['node_count']
    			);
    		}
    	}
    
    	return $total;
    }
    
    /**
     * 得到所有模块使用总数
     */
    public function getModulesLisenceUsed(){
    	$used = array(
    			'file' => 0,
    			'vm' => 0,
    			'mysql_max_num' => 0,
    			'oracle' => 0,
    			'sqlserver' => 0,
    			'os' => 0,
    			'cdp' => 0,
    			'desktop' => 0,
    			'host' => 0,
    			'cpu' => 0,
    			'storage' => 0,
    			'node' => 0,
    	);
    
    	$sql = "select authorization_module from bd_agent where register_flag = ?";
    	$data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
    	foreach ($data as $d){
    		if(!empty($d['authorization_module'])){
    			$authInfo = json_decode($d['authorization_module'], true);
    			$used['file'] += $authInfo['file'];
    			$used['mysql'] += $authInfo['mysql'];
    			$used['oracle'] += $authInfo['oracle'];
    			$used['sqlserver'] += $authInfo['sqlserver'];
    			$used['os'] += $authInfo['os'];
    			$used['desktop'] += $authInfo['desktop'];
    		}
    	}
    	//获取虚拟机模块的使用量
    	$vmModuleUsed = $this->getVMModuleUsedArr();
    	$used['vm'] = $vmModuleUsed['vm'];
    	$used['cpu'] = $vmModuleUsed['cpu'];
    	$used['host'] = $vmModuleUsed['host'];
    	$used['storage'] = $vmModuleUsed['storage'];
    
    	return $used;
    }
    
     /**
     * 得到虚拟机模块的使用量
     */
    private function getVMModuleUsedArr(){
        $vmUsed = array(
            'vm' => 0,
            'host' => 0,
            'storage' => 0,
            'cpu' => 0,
        );
        $authFlag = $this->getSystemAuthorizationStatus();
        if($authFlag != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            return $vmUsed;
        }
        $sql = "select license_type  from bd_license";
        $data = $this->dbSelect($sql);
        $licenseType = intval($data[0]['license_type']);
        $licenseTypeConf = Xphp::$_config['LISENCE_INFO']['type'];
        $sql ="";
        $sqlParams = array();
        $key = "";
        switch ($licenseType){
            case $licenseTypeConf['host']:
                $sql = "select count(host_id) as total from vm_host where authorization_flag = ? ";
                $sqlParams = array(Xphp::$_config['FLAG']['SET']);
                $key = 'host';
                break;
            case $licenseTypeConf['cpu']:
                $sql = "select sum(cpu_count) as total from vm_host where authorization_flag = ? ";
                $sqlParams = array(Xphp::$_config['FLAG']['SET']);
                $key = 'cpu';
                break;
            case $licenseTypeConf['storage']:
                $sql = "select sum(total_size) as total from bd_storage_resource where lan_free_flag = ?";
                $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
                $key = 'storage';
                break;
            case $licenseTypeConf['vm']:
                $sql = "select count(vml.vm_uuid) as total from vm_machine_list vml, bd_task bt 
                        where vml.task_uuid = bt.task_uuid and bt.task_type = ?";
                $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP']);
                $key = 'vm';
                break;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $data[0]['total'];
        if(empty($count)) $count = 0;
        $vmUsed[$key] = $count;
        return $vmUsed;
    }
    
    /**
     * 得到所有模块可用总数
     */
    public function getModulesLisenceValid(){
    	$valid = array(
    			'file' => 0,
    			'vm' => 0,
    			'mysql' => 0,
    			'oracle' => 0,
    			'sqlserver' => 0,
    			'os' => 0,
    			'cdp' => 0,
    			'desktop' => 0,
    			'host' => 0,
    			'cpu' => 0,
    			'storage' => 0,
    			'node' => 0
    	);
    	$totalInfo = $this->getModulesLisenceTotal();
    	if($totalInfo['authFlag'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
    		return $valid;
    	}
    	$used = $this->getModulesLisenceUsed();
    	$total = $totalInfo['module'];
    	$valid = array(
    			'file' => $total['file'] - $used['file'],
    			'vm' => $total['vm'] - $used['vm'],
    			'mysql' => $total['mysql'] - $used['mysql'],
    			'oracle' => $total['oracle'] - $used['oracle'],
    			'sqlserver' => $total['sqlserver'] - $used['sqlserver'],
    			'os' => $total['os'] - $used['os'],
    			'cdp' => $total['cdp'] - $used['cdp'],
    			'desktop' => $total['desktop'] - $used['desktop'],
    			'host' => $total['host'] - $used['host'],
    			'cpu' => $total['cpu'] - $used['cpu'],
    			'storage' => $total['storage'] - $used['storage'],
    			'node' => $total['node'] - $used['node'],
    	);
    
    	return $valid;
    }
    
    /**
     * 如果是虚拟机授权,需要转换存储类型的格式,如1TB
     * @param int $licenseType
     * @param array $moduleInfo
     */
    private function filterModuleInfo($licenseType, $moduleInfo){
    	if($licenseType == Xphp::$_config['LISENCE_INFO']['type']['storage']){
    		$utils = Xphp::instance('Utils');
    		$moduleInfo['total'] = intval($moduleInfo['total']);
    		$moduleInfo['used'] = intval($moduleInfo['used']);
    		$moduleInfo['valid'] = intval($moduleInfo['valid']);
    	}
    
    	$moduleInfo['type'] = $licenseType;
    	return $moduleInfo;
    }
    /**
     * 获取系统授权状态
     */
    public function getSystemAuthorizationStatus(){
    	$sql = "select authorized_flag from bd_system ";
    	$data = $this->dbSelect($sql);
    	if($data){
    		return intval($data[0]['authorized_flag']);
    	}
    }
    /**
     * 获取授权描述
     * @param int $status   授权状态标志
     * @return string
     */
    public function getSystemAuthorizationDes($status){
    	$pfDes = include APP_PATH . "platform/PFDescription.php";
    	$des = $pfDes['LISENCE_STATUS_DES'][$status];
    	return $des;
    }
    
    /**
     * 得到已授权的信息
     * @param int $status       授权状态
     * @param string $statusDes 授权描述
     */
    private function getAuthorizedInfo($status, $statusDes){
    	 $info = array(
            'status' => $status,
        );
        $sql = "select unix_timestamp(register_time) register_time, license_type, vm_max_num, cpu_count, storage_count, node_count, file_max_num, 
                days, trial_type, software_type, user_name from bd_license ";
        $data = $this->dbSelect($sql);
        $registerTime = $this->parseDate($data[0]['register_time']);
        $licenseType = intval($data[0]['license_type']);
        $vmMaxNum = intval($data[0]['vm_max_num']);
        $cpuMaxNum = intval($data[0]['cpu_count']);
        $storageMaxNum = intval($data[0]['storage_count']);
        $nodeMaxNum = intval($data[0]['node_count']);
        $fileMaxNum = intval($data[0]['file_max_num']);
        $days = $data[0]['days'];
        $expireTime = "----";
        
        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        if($status == $authFlag['authorized']){
            //已授权
            $trialType = intval($data[0]['trial_type']);
            if($trialType == Xphp::$_config['TRIAL_TYPE']['TRIAL']){
                $statusDes = Xphp::$_lang['WEB_SYSTEM_TRIAL_AUTHIORIZED'];
            }elseif ($trialType == Xphp::$_config['TRIAL_TYPE']['NORMAL']){
                $statusDes = Xphp::$_lang['WEB_SYSTEM_FORMAL_AUTHIORIZED'];
            }
            
            //剩余天数 等于 授权天数 - 授权时间到当前时间的天数
            if("-1" == $days){
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_FOREVER'] . ")";
            }else{
                $dayInterval = round((time() - strtotime($registerTime))/3600/24);
                $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_VALID'] . $expireDays  . Xphp::$_lang['WEB_SYSTEM_AUTH_DAY'] . ")";
                //到期天数等于注册时间+授权天数转换成时间
                $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            }
        }elseif ($status == $authFlag['expire'] || $status == $authFlag['invalid']){
            //到期天数等于注册时间+授权天数转换成时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
        }
        
        
        
        $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
        //虚拟机要根据授权类型得到使用量
        $info['vm_info'] = $this->getOneModuleLisenceInfo($vmTypeName[$licenseType], $licenseType);
        $info['vm_info'] = $this->filterModuleInfo($licenseType, $info['vm_info']);
        $info['trial'] = intval($data[0]['trial_type']);
        $info['software'] = intval($data[0]['software_type']);
        $info['customer'] = empty($data[0]['user_name']) ? "----" : $data[0]['user_name'];
        $info['lisence_day'] = $days;
        $info['lisence_time'] = $data[0]['register_time'];
        return $this->apiResponse(true, "API_CODE_SETTINGS_GET_AUTHORIZED_INFO", $info);
    }
    
    /**
     * 得到已授权的信息
     * @param int $status       授权状态
     * @param string $statusDes 授权描述
     */
    private function getAuthorizedInfoV2($status, $statusDes){
        $utils = Xphp::instance('Utils');
        $info = array(
            'status' => $status,
        );
        $sql = "select unix_timestamp(register_time) register_time, license_type, vm_max_num, cpu_count, storage_count, node_count, file_max_num,
                days, trial_type, software_type, user_name, extension from bd_license ";
        $data = $this->dbSelect($sql);
        $registerTime = $this->parseDate($data[0]['register_time']);
        $licenseType = intval($data[0]['license_type']);
        $vmMaxNum = intval($data[0]['vm_max_num']);
        $cpuMaxNum = intval($data[0]['cpu_count']);
        $storageMaxNum = intval($data[0]['storage_count']);
        $nodeMaxNum = intval($data[0]['node_count']);
        $fileMaxNum = intval($data[0]['file_max_num']);
        $days = $data[0]['days'];
        $expireTime = "----";
        
        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        if($status == $authFlag['authorized']){
            //已授权
            $trialType = intval($data[0]['trial_type']);
            if($trialType == Xphp::$_config['TRIAL_TYPE']['TRIAL']){
                $statusDes = Xphp::$_lang['WEB_SYSTEM_TRIAL_AUTHIORIZED'];
            }elseif ($trialType == Xphp::$_config['TRIAL_TYPE']['NORMAL']){
                $statusDes = Xphp::$_lang['WEB_SYSTEM_FORMAL_AUTHIORIZED'];
            }
            
            //剩余天数 等于 授权天数 - 授权时间到当前时间的天数
            if("-1" == $days){
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_FOREVER'] . ")";
                $expireTime = Xphp::$_config['TIMESPACE'];
            }else{
                $dayInterval = round((time() - strtotime($registerTime))/3600/24);
                $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_VALID'] . $expireDays  . Xphp::$_lang['WEB_SYSTEM_AUTH_DAY'] . ")";
                //到期天数等于注册时间+授权天数转换成时间
                $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
                $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
            }
        }elseif ($status == $authFlag['expire'] || $status == $authFlag['invalid']){
            //到期天数等于注册时间+授权天数转换成时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
        }
        
        
        
        $info['statusDes'] = $statusDes;
        $info['lisence_day'] = $days;
        $info['lisence_time'] = $data[0]['register_time'];
        $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
        //虚拟机要根据授权类型得到使用量
        $info['vm_info'] = $this->getOneModuleLisenceInfo($vmTypeName[$licenseType], $licenseType);
        $info['vm_info'] = $this->filterModuleInfo($licenseType, $info['vm_info']);
        $info['file_info'] = $this->getOneModuleLisenceInfo('file');
        $info['expireTime'] = $expireTime;
        $info['trial'] = intval($data[0]['trial_type']);
        $info['software'] = intval($data[0]['software_type']);
        if(empty($data)){
            $info['software'] = $this->getSoftwareType();
        }
        $info['customer'] = empty($data[0]['user_name']) ? "----" : $data[0]['user_name'];
        
        $extension = $utils->decrypt($data[0]['extension']);
        $extension = json_decode($extension, true);
        $info['page'] = $extension['p'];
        $info['hyperviosr'] = $extension['v'];
        $info['func_module'] = $extension['f'];
        $cmdStr = "ps aux|grep daserver";
        exec($cmdStr, $cmdinfo);
        $dbTimingHandler = Xphp::instance('DBTimingHandler');
        $file = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES']. ($info['file_info']['total'] - $info['file_info']['valid']) . '/' . $info['file_info']['total'];
        $info['file_info']['filedes'] = $file;
        //         $database = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'].'0/0';
        //获取 数据库定时信息
        if(count($cmdinfo) > 2 && $status == $authFlag['authorized']){
            if(!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime){
                $dbTimingHandler->getDBAuth(); //获取datapp认证
            }
            $dbTimingInfo = $dbTimingHandler->getDBLisenceInfo(); //获取datapp授权信息
            if(!empty($dbTimingInfo['agent'])){
                foreach($dbTimingInfo['agent'] as $agent){
                    $database_size += $agent['size'];
                    $database_free += $agent['free'];
                }
                if($database_size != 0){
                    //                     $database = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . ($database_size - $database_free) . '/' . $database_size;
                    $info['dbtiming'] = array(
                        'total' => $database_size,
                        'used' => $database_size - $database_free
                    );
                }
            }
        }
        //获取数据库实时信息
        $cmdStr = "ps aux|grep lzbackupsys";
        exec($cmdStr, $cmdinfo);
        if(count($cmdinfo) > 2 && $status == $authFlag['authorized']){
            
            $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
            $rpc = Xphp::instance('DbRPCHandler');
            $data = array();
            //是否获取cdp授权信息
            $result = $rpc->getLicenseCenterAuthorInfo($hostip, $data);
            if($result['result']){
                $cdpInfo =  $this->getDBcdpLicenseInfo();
                if($cdpInfo['total']['producthost'] !=0){
                    //                     $producthost = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['producthost'] . '/' . $cdpInfo['total']['producthost'];
                    $producthost = array(
                        'total' => $cdpInfo['total']['producthost'],
                        'used' => $cdpInfo['used']['producthost']
                    );
                    
                }
                if($cdpInfo['total']['standbyhost'] !=0){
                    //                     $standbyhost = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['standbyhost'] . '/' . $cdpInfo['total']['standbyhost'];
                    $standbyhost = array(
                        'total' => $cdpInfo['total']['standbyhost'],
                        'used' => $cdpInfo['used']['standbyhost']
                    );
                }
                if($cdpInfo['total']['takeover'] !=0){
                    //                     $takeover = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['takeover'] . '/' . $cdpInfo['total']['takeover'];
                    $takeover = array(
                        'total' => $cdpInfo['total']['takeover'],
                        'used' => $cdpInfo['used']['takeover']
                    );
                }
                $cdpendtime = $cdpInfo['endtime'];
                if(!empty($cdpendtime)){
                    $info['cdp_auth'] = array(
                        'endtime' => $cdpendtime,
                        'producthost' => $producthost,
                        'standbyhost' => $standbyhost,
                        'takeover' => $takeover
                        
                    );
                }
            }
        }
        //服务信息
        $serverInfo = $this->getServiceLicense();
        $info['server_auth'] = array(
            'serverType' => Xphp::$_config['NULLSPACE'],
            'serverTime' => Xphp::$_config['NULLSPACE'],
            'serviceFlag' => false,
        );
        if(!empty($serverInfo)){
            $info['server_auth'] = array(
                'serverType' =>intval($serverInfo['serviceType']),
                'serverTime' => $serverInfo['serviceTime'],
                'serviceFlag' => $serverInfo['serviceFlag'],
            );
        }
        return $this->apiResponse(true, "API_CODE_SETTINGS_GET_AUTHORIZED_INFO", $info);
    }
    
    /**
     * 消息推送测试
     * @param array $settings
     */
    private function messagePushTest($settings){
    	switch (intval($settings['protocol'])){
    		case Xphp::$_config['MQPROTOCOL']['STOMP']:
    			return $this->messagePushTestStomp($settings);
    			break;
    		case Xphp::$_config['MQPROTOCOL']['OPENWIRE']:
    			return $this->messagePushTestOpenwire($settings);
    			break;
    	}
    }
    
    /**
     * 消息推送测试 stomp协议
     * @param array $settings
     */
    private function messagePushTestStomp($settings){
    	$username = $settings['username'];
    	$password = $settings['password'];
    	$domain = $settings['domain'];
    	$port = $settings['port'];
    	$connect = "tcp://" . $domain . ":" . $port;
    	try{
    		$stomp = new Stomp($connect, $username, $password);
    	} catch(StompException $e) {
    		exit($this->apiResponse(false, "API_CODE_SETTINGS_MESSAGE_PUSH_CONNECT_TEST_ERROR"));
    	}
    }
    
    /**
     * 消息推送测试 openwire协议
     * @param array $settings
     */
    private function messagePushTestOpenwire($settings){
    	$opName = 'BD_SYSTEM_OP_MQ_CONNCT_TEST';
    	$msg = array(
    			'msg_name' => '',
    			'msg_content' => '',
    			'config' => array(
    					'middleware' => Xphp::$_config['MIDDLEWARE']['ACTIVEMQ'],
    					'protocol' => intval($settings['protocol']),
    					'mode' => intval($settings['mode']),
    					'ip_domain' => $settings['domain'],
    					'port' => $settings['port'],
    					'user_name' => $settings['username'],
    					'password' => $settings['password'],
    			)
    	);
    	$msg = $this->mbPFMsg($opName, json_encode($msg), true);
    	if(!$msg['result']){
    		exit($this->apiResponse(false, "API_CODE_SETTINGS_MESSAGE_PUSH_CONNECT_TEST_ERROR"));
    	}
    }
    
    /**
     * 处理服务授权文件
     * @param unknown $serviceLic
     */
    private function serviceLisenceKey($serviceLic, $operate){
        //检查机器指纹,保存文件
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if($msg != $serviceLic['thumbprint'] && $serviceLic['thumbprint'] != "-1"){
            //机器指纹不一致
            exit($this->apiResponse(false, "API_CODE_SETTINGS_GET_FINGERPRINT", "指纹信息不一致,请检查!"));
        }
        //加密存储服务授权信息到文件
        $utils = Xphp::instance('Utils');
        $serviceLic = $utils->encrype(json_encode($serviceLic, true));
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $cmd = "echo $serviceLic > " . Xphp::$_config['LISENCE_INFO']['serviceFilePath'];
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        $result = $mbResult['result'];
        return $result;
    }
    
    /**
     * 获取数据库CDP授权信息
     */
    private function getDBcdpLicenseInfo(){
        //         $info = array(
        //             'endtime' => "---",
        //             'total' => array(
        //                 'producthost' => 0,
        //                 'standbyhost' => 0,
        //                 'takeover' => 0,
        //             ),
        //             'used' => array(
        //                 'producthost' => 0,
        //                 'standbyhost' => 0,
        //                 'takeover' => 0,
        //             )
        //         );
        //         return $info;
        $dbCDPHandler = Xphp::instance('DbCDPHandler');
        $result = $dbCDPHandler->getLicenseCenterAuthorInfo();
        $totalArr = $result[0]['units'];
        $usedArr = $result[0]['alloced'];
        $info = array(
            'endtime' => "",
            'total' => array(
                'producthost' => 0,
                'standbyhost' => 0,
                'takeover' => 0,
            ),
            'used' => array(
                'producthost' => 0,
                'standbyhost' => 0,
                'takeover' => 0,
            )
        );
        if($result && !empty($result)){
            foreach ($totalArr as $total){
                if($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['producthost']){
                    $info['total']['producthost'] = $total['authors'];
                }
                if($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']){
                    $info['total']['standbyhost'] = $total['authors'];
                }
                if($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['takeover']){
                    $info['total']['takeover'] = $total['authors'];
                }
                $info['endtime'] = $total['etime'];
            }
            
            foreach ($usedArr as $used){
                if($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['producthost']){
                    $info['used']['producthost'] = $info['used']['producthost'] + 1;
                }
                if($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']){
                    $info['used']['standbyhost'] = $info['used']['standbyhost'] + 1;
                }
                if($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['takeover']){
                    $info['used']['takeover'] = $info['used']['takeover'] + 1;
                }
            }
        }
        return $info;
    }
    
    /**
     * 得到软件版本,
     * 1标准版,2企业版,3企业增强版,4免费版本
     * OEM版本暂时划归为企业版,企业增强版暂时没有
     */
    public function getSoftwareType(){
        $systemInfo = Xphp::$_config['SYSTEM_INFO'];
        $enterprise = Xphp::$_config['ENTERPRISE'];
        $sql = "select software_type from bd_license";
        $data = $this->dbSelect($sql, array());
        if(empty($data)){
            //如果没有记录,就是还没有授权,需要获取配置文件的软件版本
            if($systemInfo['enterprise'] == $enterprise['standard']){
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['STANDARD'];
            }else if($systemInfo['enterprise'] == $enterprise['enterprise']){
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE'];
            }else if($systemInfo['enterprise'] == $enterprise['enterprise_en']){
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE_EN'];
            }else{
                //如果是其他OEM版本,默认为企业版
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE'];
            }
        }else{
            //有记录,获取数据库的记录
            $softwareVersion = intval($data[0]['software_type']);
        }
        return $softwareVersion;
    }
    
    /**
     * 得到服务授权信息
     */
    private function getServiceLicense(){
        $fileInfo = file_get_contents(Xphp::$_config['LISENCE_INFO']['serviceFilePath']);
        if(!$fileInfo){
            return false;
        }
        $utils = Xphp::instance('Utils');
        $data = $utils->decrypt($fileInfo);
        return json_decode($data, true);
    }
    
    /**
     * 得到extension license
     */
    public function getExtensionLicense(){
        $utils = Xphp::instance('Utils');
        $sql = "select extension from bd_license";
        $data = $this->dbSelect($sql, array());
        $data = $utils->decrypt($data[0]['extension']);
        return json_decode($data, true);
    }
    
    /**
     * 获取系统时间
     * @param unknown $params
     */
    public function getSystemTime(){
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return strtotime($data[0]);
    }
    
    /**
     * 得到系统运行时间
     */
    public function getSystemRunningTime(){
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
                'hours' => $value
            );
        }
        return $info;
    }
    
    /**
     * 得到累计备份数据
     */
    private function getAccumulatedData(){
        $sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data, db_data, os_data from bd_user_extension ";
        $sqlParams = array();
        if(Xphp::$_user['username'] == "admin" && empty($_SESSION['tenantuuid'])){
            
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
        $info['byte'] = $total;
        return $info;
    }
    
}