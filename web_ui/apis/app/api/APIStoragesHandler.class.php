<?php
/******************************************* 
** 存储处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APIStoragesHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/storages" => array(
            'POST' => 'addStorages',
            'PUT' => 'editStorages',
            'DELETE' => 'deleteStorages',
            'GET' => 'getStorages'
        ),
    
        "/storages/lists" => array(
            'GET' => 'getStoragesLists'
        ),
    	
    	"/storages/resources" => array(
    		'GET' => 'getDiskLists'
    	),
    	"/storages/wwn" => array(
    		'GET' => 'getWwnInfo'
    	),
    	"/storages/iscsiname" => array(
    		'GET' => 'getIscsiName'
    	),
    	"/storages/lanfree" => array(
    		'POST' => 'addLanfree',
    		'PUT' => 'editLanfree',
    		'DELETE' => 'deleteLanfree',
    	),
    	"/storages/lanfreelists" => array(
    		'GET' => 'getLanfreeLists'
    	),
    	"/storages/iscsilun" => array(
    		'POST' => 'getIscsiLun'
    	),
    	"/storages/importdata" => array(
    		'POST' => 'assignData',
    		'GET' => 'getImportDataList',
    		'DELETE' => 'deleteImportData'
    	),
    	"/storages/copystorage"  => array(
    		'POST' => 'addCopyStorage'
    	)
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 添加存储路由控制
     */
    protected function addStorages(){
        //定义方法版本
        $version = array(
            "v1" => "addStoragesV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改存储路由控制
     */
    protected function editStorages(){
        //定义方法版本
        $version = array(
            "v1" => "editStoragesV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除存储路由控制
     */
    protected function deleteStorages(){
        //定义方法版本
        $version = array(
            "v1" => "deleteStoragesV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到存储信息路由控制
     */
    protected function getStorages(){
        //定义方法版本
        $version = array(
            "v1" => "getStoragesV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到存储列表路由控制
     */
    protected function getStoragesLists(){
        //定义方法版本
        $version = array(
            "v1" => "getStoragesListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到存储磁盘列表路由控制
     */
    protected function getDiskLists(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getDiskListsV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到光纤通道wwn信息路由控制
     */
    protected function getWwnInfo(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getWwnInfoV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到ISCSI名字路由控制
     */
    protected function getIscsiName(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getIscsiNameV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到ISCSI存储路径路由控制
     */
    protected function getIscsiLun(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getIscsiLunV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 添加lanfree路由控制
     */
    protected function addLanfree(){
    	//定义方法版本
    	$version = array(
    		"v1" => "addLanfreeV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改lanfree路由控制
     */
    protected function editLanfree(){
    	//定义方法版本
    	$version = array(
    		"v1" => "editLanfreeV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除lanfree路由控制
     */
    protected function deleteLanfree(){
    	//定义方法版本
    	$version = array(
    		"v1" => "deleteLanfreeV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到lanfree存储列表
     */
    protected function getLanfreeLists(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getLanfreeListsV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 分配导入数据给用户
     */
    protected function assignData(){
    	//定义方法版本
    	$version = array(
    			"v1" => "assignDataV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }

    /**
     * 得到导入的备份数据列表
     */
    protected function getImportDataList(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getImportDataListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除导入数据
     */
    protected function deleteImportData(){
    	//定义方法版本
    	$version = array(
    			"v1" => "deleteImportDataV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 添加异地副本存储
     */
    protected function addCopyStorage(){
    	//定义方法版本
    	$version = array(
    			"v1" => "addCopyStorageV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
   
    
    
    /**********addStorages**********/
    private function addStoragesV1(){
     	$nodeuuid = $this->params['node_uuid'];
        $storageType = $this->params['storage_type'];
        $rawDevPath = $this->params['storage_path'];
        $nickname = $this->params['nickname'];
        $mountparams = $this->params['mount_params'];
        $this->checkStorageNameExist($nickname);
        $this->apiParamsCheck($nodeuuid, $storageType, $nickname);
        $formatFlag = Xphp::$_config['FLAG']['SET'];
//         $importFlag = Xphp::$_config['FLAG']['UNSET'];
		$importFlag = $this->params['import_flag'];
		$this->checkImportFlag($importFlag,$storageType);
		if($importFlag == Xphp::$_config['FLAG']['SET'] && $storageType == Xphp::$_config['BD_STORAGE_TYPE']['PARTITION']){
			$formatFlag = Xphp::$_config['FLAG']['UNSET'];
		}
		
        $lanfreeFlag = Xphp::$_config['FLAG']['UNSET'];
        $utils = Xphp::instance('Utils');
        switch ($storageType){
            case Xphp::$_config['BD_STORAGE_TYPE']['DISK']:
            case Xphp::$_config['BD_STORAGE_TYPE']['LVM']:
            case Xphp::$_config['BD_STORAGE_TYPE']['PARTITION']:
            case Xphp::$_config['BD_STORAGE_TYPE']['FC']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType, 
                    'nickname' => $nickname,
                    "raw_dev_path" => $rawDevPath,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => ""
                );
                $opName = "NODE_SR_OP_ADD_DISK_PART_LVM";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
                $iscsiTargetList = array();
                foreach ($this->params['server_list'] as $server){
                    $iscsiTargetList[] = array(
                        'iscsi_target' => $server['ip'] . ":" . $server['port']
                    );
                }
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                	'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'raw_dev_path' => $rawDevPath,
                    "iscsi_target_list" => $iscsiTargetList,
                    'target_iqn' => '',
                    'username' => '',
                    'password' => '',
                    
                    'usemode' => 1,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => ""
                );
                $opName = "NODE_SR_OP_ADD_ISCSI_DISK";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType, 
                    'nickname' => $nickname,
                    'remote_path' => $this->params['remote_path'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
                );
                $opName = "NODE_SR_OP_ADD_NFS";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['CIFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType, 
                    'nickname' => $nickname,
                    'remote_path' => $this->params['remote_path'],
                    'username' => $this->params['user_name'],
                    'passwd' => $this->params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
                );
                $opName = "NODE_SR_OP_ADD_CIFS";
                break;
            
            case Xphp::$_config['BD_STORAGE_TYPE']['LOCALDIR']:         //本地目录
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => $this->params['storage_path'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => ""
                );
                $opName = "NODE_SR_OP_ADD_DIR";
                break;
                                
        }
        
        //添加存储用途
        $msg['usemode'] = $this->params['usemode'];
        $msg['lanfree_name_list'] = "";
        $msg['lanfree_nickname_list'] = "";
                
        //添加告警配置
        $warningSetting = $this->params['warning_settings'];
        $value = $warningSetting['warning_value'];
        $msg['warning_flag'] = $this->params['warning_flag'];
        $msg['warning_type'] = intval($warningSetting['warning_type']);
        if($value > 100 && $msg['warning_type'] == Xphp::$_config['STORAGEWARNINGTYPE']['PERCENT']){
        	return $this->apiResponse(false, 'API_CODE_ADD_STORAGE_WARNING_VALUE_ERROR');
        }
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
        	//如果是按照大小来告警
        	$msg['warning_value'] = intval($value) * 1024 * 1024 * 1024;
        }else{
        	$msg['warning_value'] = intval($value);
        }
        
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        $data = array();
        if($mbResult['result']){
        	//如果成功,返回新建资源uuid
        	$data['storage_uuid'] = $this->getStorageuuidWithName($nickname);
        }
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_STORAGES_ADD_STORAGE', $data, $mbResult);
    }
    
    /**********editStorages**********/
    private function editStoragesV1(){
        $storageuuid = $this->params['storage_uuid'];
        $storagename = $this->params['nickname'];
        return $this->UnifiedEditStorage($storageuuid, $storagename, "API_CODE_STORAGES_EDIT_STORAGE_NICKNAME");
    }
    
    /**********deleteStorages**********/
    private function deleteStoragesV1(){
    	$storageuuids = $this->params['storage_uuid_list'];
    	//检查传入的存储uuid是否有效存在
    	$this->checkStorageExist($storageuuids[0]);
    	return $this->UnifiedDeleteStorage($storageuuids, 'API_CODE_STORAGES_DELETE_STORAGE');
    	
    }
    
    
    /**********getStorages**********/
    private function getStoragesV1(){
        $storageUUID = $this->params['storage_uuid'];
        $this->apiParamsCheck($storageUUID);
        $sql = "select use_mode, mount_flag, storage_nickname, storage_type, node_uuid, total_size, free_size, 
                status from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageUUID));
        $nodeHandler = Xphp::instance('NodeHandler');
        $record = array();
        if(!empty($data)){
        	$nodeAllStatus = $nodeHandler->getNodeAllStatus( $data[0]['node_uuid']);
            $record = array(
                "storage_uuid" => $storageUUID,
                "nickname" => $data[0]['storage_nickname'],
                "type" => $data[0]['storage_type'],
                "node_uuid" => $data[0]['node_uuid'],
                "total_size" => $data[0]['total_size'],
                "free_size" => $data[0]['free_size'],
                "status" => $this->getStorageStatus($nodeAllStatus, intval($data[0]['status']), intval($data[0]['mount_flag'])),
            	"usemode" => intval($data[0]['use_mode'])
            		
            );
        }
        
        return $this->apiResponse(true, 'API_CODE_STORAGES_GET_STORAGE_INFO', $record);
    }
    
    /**********getStoragesLists**********/
    private function getStoragesListsV1(){
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $this->apiParamsCheck($count);
        $sql = "select use_mode, mount_flag, storage_uuid, storage_nickname, storage_type, node_uuid, total_size, free_size, 
                status from bd_storage_resource where lan_free_flag = ? limit ? , ?";
        $sqlCount = "select count(storage_id) as total from bd_storage_resource where lan_free_flag = ?";
        
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $begin, $count);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        if($this->params['node_uuid']){
        	$nodeuuid = $this->params['node_uuid'];
        	$sql = "select use_mode, mount_flag, storage_uuid, storage_nickname, storage_type, node_uuid, total_size, free_size,
                status from bd_storage_resource where node_uuid = ? and lan_free_flag = ? limit ? , ?";
        	$sqlCount = "select count(storage_id) as total from bd_storage_resource where node_uuid = ? and lan_free_flag = ?";
        	
        	$sqlParams = array($nodeuuid, Xphp::$_config['FLAG']['UNSET'], $begin, $count);
        	$sqlCountParams = array($nodeuuid, Xphp::$_config['FLAG']['UNSET']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $nodeHandler = Xphp::instance('NodeHandler');
        $records = array();
        foreach ($data as $d){
        	$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            $records[] = array(
                "storage_uuid" => $d['storage_uuid'],
                "nickname" => $d['storage_nickname'],
                "type" => $d['storage_type'],
                "node_uuid" => $d['node_uuid'],
                "total_size" => $d['total_size'],
                "free_size" => $d['free_size'],
                "status" =>  $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
            	"usemode" => intval($d['use_mode'])
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_STORAGES_GET_STORAGE_LIST", $data);
    }
    
    
    /**********getDiskLists**********/
    private function getDiskListsV1(){
    	$nodeuuid = $this->params['node_uuid'];
    	$storageType = $this->params['type'];
    	$lanfree = 2;
    	$this->apiParamsCheck($nodeuuid, $storageType);
    	$utis = Xphp::instance('Utils');
    	$msg = array("storage_type" => $storageType, "lan_free_flag" => $lanfree);
    	$opName = "NODE_SR_OP_SCAN_LOCAL";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
    	$info = array();
    	if($mbResult['result']){
    		$data = $mbResult['msg']['raw_storage_list'];
    		if(!$data){
    			return $this->apiResponse(false, 'API_CODE_STORAGES_NO_STORAGE_EXIST');
    		}
    		foreach ($data as $d){
    			$info[] = array(
    					"storage_name" =>$d['name'],
    					'disk_type' => $this->getStorageScanTypeDes(false, $d),
    					'disk_size' => $utis->calSize($d['size']),
    					
    			);
    		}
    	}
    	return $this->apiResponse(true, 'API_CODE_STORAGES_GET_DISK_LIST', $info);
    }
    
    /**********getWwnInfo**********/
    private function getWwnInfoV1(){
    	$nodeuuid = $this->params['node_uuid'];
    	$this->apiParamsCheck($nodeuuid);
    	$opName = "NODE_SR_OP_GET_FC_HOST_WWN";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, '', true);
    	$info = array();
    	if(!$mbResult['result']){
    		return $this->apiResponse(false, '', $info);
    	}
    	$data = $mbResult['msg']['wwn_list'];
    	if(!$data){
    		return $this->apiResponse(false, 'API_CODE_STORAGES_NO_WWN_EXIST');
    	}
    	foreach ($data as $d){
    		$info[] = array(
				'name' => $d['host_name'],
				'wwnn' => $d['wwnn'],
				'wwpn' => $d['wwpn'],
				'speed' => $d['speed'],
				'state' => $d['state'],
			);
    	}
    	
    	return $this->apiResponse(true, 'API_CODE_STORAGES_GET_WWN_INFO', $info);
    }
    
    /**********getIscsiName**********/
    private function getIscsiNameV1(){
    	$nodeuuid = $this->params['node_uuid'];
    	$msg = array();
    	$opName = "NODE_SR_OP_GET_ISCSI_INIT_IQN";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
    	if(!$mbResult['result']){
    		$opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
    		return $this->apiResponse(false, 'API_CODE_STORAGES_GET_ISCSI_NAME_FAILED', '', $mbResult['errorCode']);
    	}
    	$info = array();
    	$info[] = array(
    			'iscsi_name' => $mbResult['msg']['initiator_name'],
    	);
    	return $this->apiResponse(true, 'API_CODE_STORAGES_GET_ISCSI_NAME', $info);
    }
    
    /**********addLanfree**********/
    private function addLanfreeV1(){
    	$nodeuuid = $this->params['node_uuid'];
    	$storageType = $this->params['storage_type'];
    	$rawDevPath = $this->params['storage_path_list'][0];
    	$nickname = $this->params['nickname'];
    	$this->checkStorageNameExist($nickname);
    	$this->apiParamsCheck($nodeuuid, $storageType, $nickname);
    	$formatFlag = Xphp::$_config['FLAG']['SET'];
    	$importFlag = Xphp::$_config['FLAG']['UNSET'];
    	$lanfreeFlag = Xphp::$_config['FLAG']['SET'];
    	$pathList = $this->params['storage_path_list'];
    	$mountparams = $this->params['mount_params'];
    	$nameList = array();
    	$list = array();
    	$i = 1;
    	if(!empty($pathList)){
    	    foreach ($pathList as $path){
    	        $nameList[] = array(
    	            "lanfree_nickname" => $nickname.$i
    	        );
    	        $list[] = array(
    	            'lanfree_name' => $path
    	        );
    	        $i++;
    	    }
    	}
    	switch ($storageType){
    		case Xphp::$_config['BD_STORAGE_TYPE']['FC']:
    			$msg = array(
    			'lan_free_flag' => $lanfreeFlag,
    			'storage_type' => $storageType,
    			'nickname' => $nickname,
    			"raw_dev_path" => $rawDevPath,
    			'format_flag' => $formatFlag,
    			'import_flag' => $importFlag,
    			
    			'usemode' => 1,
    			'lanfree_name_list' => $list,
    			'lanfree_nickname_list' => $nameList,
    			'mount_params' => ""
    			);
    			$opName = "NODE_SR_OP_ADD_DISK_PART_LVM";
    			break;
    		case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
    			$iscsiTargetList = array();
    			foreach ($this->params['server_list'] as $server){
    				$iscsiTargetList[] = array(
    						'iscsi_target' => $server['ip'] . ":" . $server['port']
    				);
    			}
    			$msg = array(
    					'lan_free_flag' => $lanfreeFlag,
    					'storage_type' => $storageType,
    					'nickname' => $nickname,
    					'format_flag' => $formatFlag,
    					'import_flag' => $importFlag,
    					'raw_dev_path' => $rawDevPath,
    					"iscsi_target_list" => $iscsiTargetList,
    					'target_iqn' => '',
    					'username' => '',
    					'password' => '',
    			    
        			    'usemode' => 1,
        			    'lanfree_name_list' => $list,
        			    'lanfree_nickname_list' => $nameList,
    			         'mount_params' => ""
    			);
    			$opName = "NODE_SR_OP_ADD_ISCSI_DISK";
    			break;
    		case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
    			$msg = array(
    			'lan_free_flag' => $lanfreeFlag,
    			'storage_type' => $storageType,
    			'nickname' => $nickname,
    			'remote_path' => $this->params['remote_path'],
    			'format_flag' => $formatFlag,
    			'import_flag' => $importFlag,
    			
    			'usemode' => 1,
    			'lanfree_name_list' => "",
    			'lanfree_nickname_list' => "",
    			'mount_params' => $mountparams
    			);
    			$opName = "NODE_SR_OP_ADD_NFS";
    			break;
    	
    	}
    	//添加告警配置
    	$msg['warning_flag'] = '';
    	$msg['warning_type'] = '';
    	$msg['warning_value'] = '';
    	$msg['usemode'] = 1; //存储用于备份
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
    	$data = array();
    	if($mbResult['result']){
    		//如果成功,返回新建资源uuid
    		$data['storage_uuid'] = $this->getStorageuuidWithName($nickname);
    	}
    	
    	return $this->apiResponse($mbResult['result'], 'API_CODE_STORAGES_ADD_LANFREE', $data, $mbResult);
    }
    
    /**********editLanfree**********/
    private function editLanfreeV1(){
    	$storageuuid = $this->params['storage_uuid'];
    	$storagename = $this->params['nickname'];
    	return $this->UnifiedEditStorage($storageuuid, $storagename, "API_CODE_STORAGES_EDIT_LANFREE_NICKNAME");
    }
    
    /**********deleteLanfree**********/
    private function deleteLanfreeV1(){
    	$storageuuids = $this->params['storage_uuid_list'];
    	//检查传入的存储uuid是否有效存在
    	$this->checkStorageExist($storageuuids);
    	return $this->UnifiedDeleteStorage($storageuuids, 'API_CODE_STORAGES_DELETE_LANFREE');
    	 
    	 
    }
    
    /**********getIscsiLun**********/
    private function getIscsiLunV1(){
    	$nodeuuid = $this->params['node_uuid'];
    	$serverList = $this->params['server_list'];
    	$lanfree = $this->params['lanfree_flag'];
    	$this->apiParamsCheck($nodeuuid, $serverList);
    	
    	$iscsiTargetList = array();
    	foreach ($serverList as $server){
    		$iscsiTargetList[] = array(
    				'iscsi_target' => $server['ip'] . ":" . $server['port']
    		);
    	}
    	
    	$utis = Xphp::instance('Utils');
    	$msg = array(
    			'iscsi_target_list' => $iscsiTargetList,
    			'username' => '',
    			'password' => '',
    			"lan_free_flag" => $lanfree
    	);
    	$opName = "NODE_SR_OP_SCAN_ISCSI";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
    	$info = array();
    	if($mbResult['result']){
    		$data = $mbResult['msg']['raw_storage_list'];
    		foreach ($data as $d){
    			$iqn = substr($d['iqn'], 0, -1);
    			$iqn = explode("|", $iqn); 
    			$info[] = array(
    				'storage_path' => $d['name'],
    				'iqn' => $iqn,
    				'storage_type' => $d['type'],
    				'storage_size' => $d['size']
    			);
    		}
    	}
    	return $this->apiResponse($mbResult['result'], 'API_CODE_STORAGES_GET_ISCSI_LUN', $info);
    }
    
    /**********getLanfreeLists**********/
    private function getLanfreeListsV1(){
    	$begin = intval($this->params['begin']);
    	$count = intval($this->params['count']);
    	$this->apiParamsCheck($count);
    	$sql = "select mount_flag, storage_uuid, storage_nickname, storage_type, node_uuid, total_size, free_size,
                status from bd_storage_resource where lan_free_flag = ? limit ? , ?";
    	$sqlCount = "select count(storage_id) as total from bd_storage_resource where lan_free_flag = ?";
    	$sqlParams = array(Xphp::$_config['FLAG']['SET'], $begin, $count);
    	$sqlCountParams = array(Xphp::$_config['FLAG']['SET']);
    	if($this->params['node_uuid']){
    		$nodeuuid = $this->params['node_uuid'];
    		$sql = "select mount_flag, storage_uuid, storage_nickname, storage_type, node_uuid, total_size, free_size,
                status from bd_storage_resource where node_uuid = ? and lan_free_flag = ? limit ? , ?";
    		$sqlCount = "select count(storage_id) as total from bd_storage_resource where node_uuid = ? and lan_free_flag = ?";
    		 
    		$sqlParams = array($nodeuuid, Xphp::$_config['FLAG']['SET'], $begin, $count);
    		$sqlCountParams = array($nodeuuid, Xphp::$_config['FLAG']['SET']);
    	}
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
    	
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$records = array();
    	foreach ($data as $d){
    		$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
    		$records[] = array(
    				"lanfree_uuid" => $d['storage_uuid'],
    				"nickname" => $d['storage_nickname'],
    				"type" => $d['storage_type'],
    				"node_uuid" => $d['node_uuid'],
    				"total_size" => $d['total_size'],
    				"free_size" => $d['free_size'],
    				"status" => $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']))
    		);
    	}
    	
    	$data = array(
    			"total" => intval($dataCount[0]['total']),
    			"begin" => $begin,
    			"count" => $count,
    			"records" => $records
    	);
    	
    	return $this->apiResponse(true, "API_CODE_STORAGES_GET_LANFREE_LIST", $data);
    }
    
    
    /**********assignData**********/
    private function assignDataV1(){
    	$taskuuids = $this->params['uuids'];
    	$useruuid = $this->params['user_uuid'];
    	$username = $this->params['user_name'];
    	$this->apiParamsCheck($taskuuids, $useruuid);
    	$taskuuidsStr = implode("','", $taskuuids);
    	$sql = "update bd_backup_timepoint set user_uuid = ?, user_name = ?, import_flag = ?  where user_uuid = ? and task_uuid in ('$taskuuidsStr')";
    	$result = $this->dbExec($sql, array($useruuid, $username, Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid']));
    	return $this->apiResponse($result, "API_CODE_STORAGES_ASSIGN_DATA");
    }
    
    /**********deleteImportData**********/
    private function deleteImportDataV1(){
    	$taskuuids = $this->params['uuids'];
    	$this->paramsCheck($taskuuids);
    	$taskuuidsStr = implode("','", $taskuuids);
    	$flag = Xphp::$_config['FLAG'];
    	//因为XenServer不支持对增量点的删除,所以增量点要先选出来,所以这里反排
    	$sql = "select timepoint_uuid, module_type, backup_mode from bd_backup_timepoint
    	where task_uuid in ('$taskuuidsStr') and deleted_flag = ? and available_flag = ?
    	and import_flag = ? order by backup_mode desc";
    	$data = $this->dbSelect($sql,array($flag['UNSET'], $flag['SET'], $flag['SET']));
    	$vmTimepointuuids = array();
    	foreach($data as $d){
    		$vmTimepointuuids[] = $d['timepoint_uuid'];
        }
    	//处理虚拟机备份点
    	$result = $this->deleteVMImportData($vmTimepointuuids);
    	
    	return $this->apiResponse($result, "API_CODE_STORAGES_DELETE_IMPORT_DATA");
    	
    }
    
    /**********getImportDataList**********/
    private function getImportDataListV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$sql = "select task_uuid, task_name, task_create_time, count(task_uuid) as point, 
                sum(write_size) as size from bd_backup_timepoint  
                where module_type = ? and deleted_flag = ? and available_flag = ? and import_flag = ? and user_uuid = ? group by task_uuid limit ? , ? ";
    	$flag = Xphp::$_config['FLAG'];
        $importData = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['VM'], $flag['UNSET'], $flag['SET'], $flag['SET'], Xphp::$_user['useruuid'], $begin, $count));
        $records = array();
        $utils = Xphp::instance("Utils");
        foreach ($importData as $d){
        	$records[] = array(
        			"task_uuid" => $d['task_uuid'],
        			"task_name" => $d['task_name'],
        			"hypervisor" => $this->getTaskHypervisor($d['task_uuid']),
        			"task_create_time" => $d['task_create_time'],
        			"point" => intval($d['point']),
        			"size" => $utils->calSize($d['size']),
        	);
        }
        $data = array(
        		"total" => count($importData),
        		"begin" => $begin,
        		"count" => $count,
        		"records" => $records
        );
        return $this->apiResponse(true, "API_CODE_STORAGES_GET_IMPORT_DATA_LIST", $data);
    }
    
    /**********addCopyStorage**********/
    private function addCopyStorageV1(){
    	$nickname = $this->params['nickname'];
    	$remoteIp = $this->params['remote_ip'];
    	$this->apiParamsCheck($remoteIp, $nickname);
//     	$importFlag = $this->params['import_flag'];
    	
    	$nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
    	
    	$msg = array(
    		'node_uuid' => $nodeuuid,
    		'lan_free_flag' => Xphp::$_config['FLAG']['UNSET'],
    		'storage_type' => Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'],
    		'nickname' => $nickname,
    		'ip' => $remoteIp,
    		'port' => 30051,
    		'username' => $this->params['user_name'],
    		'passwd' => $this->params['password'],
    		'format_flag' => Xphp::$_config['FLAG']['SET'],
    		'import_flag' => Xphp::$_config['FLAG']['UNSET'],
    		'options' => '',
    		'usemode' => Xphp::$_config['BD_STORAGE_USE_MODE']['COPY'],
    	    'mount_params' => ""
    	);
    	//添加告警配置
        $warningSetting = $this->params['warning_settings'];
        $value = $warningSetting['warning_value'];
        $msg['warning_flag'] = $this->params['warning_flag'];
        $msg['warning_type'] = intval($warningSetting['warning_type']);
        if($value > 100 && $msg['warning_type'] == Xphp::$_config['STORAGEWARNINGTYPE']['PERCENT']){
        	return $this->apiResponse(false, 'API_CODE_ADD_STORAGE_WARNING_VALUE_ERROR');
        }
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
        	//如果是按照大小来告警
        	$msg['warning_value'] = intval($value) * 1024 * 1024 * 1024;
        }else{
        	$msg['warning_value'] = intval($value);
        }
    	
    	//测试异地备份系统连接
    	$opName = "NODE_SR_OP_TEST_REMOTE_SYSTEM";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
    	if(!$mbResult['result']){
    		return $this->apiResponse($mbResult['result'], 'API_CODE_STORAGES_ADD_COPY_STORAEG', array(), $mbResult);
    	}
    	$message = $mbResult['msg'];
    	if($message['import_flag'] == Xphp::$_config['FLAG']['SET'] ){
    		$msg['format_flag'] = Xphp::$_config['FLAG']['UNSET'];
    		$msg['import_flag'] = Xphp::$_config['FLAG']['SET'];
    	}
    	$opName = "NODE_SR_OP_ADD_REMOTE_SYSTEM"; //添加异地副本存储
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
    	$data = array();
    	if($mbResult['result']){
    		//如果成功,返回新建资源uuid
    		$data['storage_uuid'] = $this->getStorageuuidWithName($nickname);
    	}
    	
    	return $this->apiResponse($mbResult['result'], 'API_CODE_STORAGES_ADD_COPY_STORAEG', $data, $mbResult);
    	
    }
    
    /**********************************其他工具方法************************************/
    /**
     * 检查存储别名是否存在
     * @param string $nickName
     */
    private function checkStorageNameExist($nickName){
    	$sql = "select storage_uuid from bd_storage_resource where storage_nickname = ?";
    	$data = $this->dbSelect($sql, array(trim($nickName)));
    	if(!empty($data)){
    		//如果找到了相同任务名的任务,返回错误
    		return $this->apiResponse(false, 'API_CODE_STORAGES_NICKNAME_EXISTS');
    	}
    }
    
    /**
     * 通过存储名字得到存储uuid
     * @param string $name
     * @return string
     */
    private function getStorageuuidWithName($name){
    	$sql = "select storage_uuid from bd_storage_resource where storage_nickname = ?";
    	$data = $this->dbSelect($sql, array(trim($name)));
    	$storageuuid = empty($data[0]['storage_uuid']) ? "" : $data[0]['storage_uuid'];
    	return $storageuuid;
    }
    
    /**
     * 根据存储UUID得到节点uuid
     * @param string $storageuuid
     */
    public function getStorageInNode($storageuuid){
    	$sql = "select node_uuid from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	return $data[0]['node_uuid'];
    }
    
    /**
     * 检测并过滤存储删除结果
     * NFS/CIFS删除失败的时候需要判断存储是否是离线状态,离线的时候是无法删除的,需要提示用户重启备份系统,然后再执行删除操作
     * @param string $storageuuid   存储UUID
     * @param array $mbResult       后台删除存储结果
     */
    private function checkStorageDeleteResult($storageuuid, $mbResult){
    	if($mbResult['result']){
    		//如果删除是成功的,直接返回
    		return true;
    	}
    	//如果删除失败,先检测是否是CIFS/NFS,并且是否是离线状态
    	$sql = "select storage_nickname, storage_type, status, mount_flag from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	$storageType = intval($data[0]['storage_type']);
    	$status = intval($data[0]['status']);
    	if($storageType != Xphp::$_config['BD_STORAGE_TYPE']['NFS'] &&
    			$storageType != Xphp::$_config['BD_STORAGE_TYPE']['CIFS']){
    		//如果不是NFS/CIFS,直接返回
    	}
    	if($status == Xphp::$_config['STORAGE_STATUS']['OFFLINE']){
    		//如果是离线状态,报错,并直接退出
    		$opName = "NODE_SR_OP_DELETE";
    		$operate = $this->getUnifyOpcodeDes($opName);
    		return $this->apiResponse(false, 'API_CODE_STORAGES_DELETE_STORAGE_OFF_LINE');
    	}
    	return true;
    }
	
    /**
     * 获取操作名
     * @param string $opName
     */
    private function getUnifyOpcodeDes($opCode){
    	$nodeOpcode = Xphp::instance('NodeOpcode');
    	$operate = $nodeOpcode->getOpcodeDes($opCode);
    	return $operate;
    }
    
    /**
     * 节点不在线时,通过WEB自己删除存储
     * @param string $storageuuid
     * @param string $type
     */
    private function deleteStorageByWeb($storageuuid, $type){
    	//开始事务
    	$this->dbBeginTransaction();
    	//查找所有使用存储的时间点
    	$sql = "select timepoint_uuid from bd_backup_timepoint where storage_uuid = ? ";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	$timepoint = "";
    	foreach ($data as $d){
    		$timepoint .= "'" . $d['timepoint_uuid'] . "',";
    	}
    	$timepoint = substr($timepoint, 0, -1);
    	if(empty($timepoint)){
    		$timepoint = "''"; //房子为空的时候SQL出错
    	}
    
    	//删除bd_backup_timepoint
    	$sql = "delete from bd_backup_timepoint where storage_uuid = ?";
    	$result = $this->dbExec($sql, array($storageuuid));
    
    	//删除vm_backup_timepoint
    	$sql = "delete from vm_backup_timepoint where timepoint_uuid in ($timepoint)";
    	$result = $result && $this->dbExec($sql);
    
    	//删除fs_backup_timepoint
    	$sql = "delete from fs_backup_timepoint where fs_timepoint_uuid in ($timepoint)";
    	$result = $result && $this->dbExec($sql);
    
    	//删除bd_storage_resource
    	$sql = "delete from bd_storage_resource where storage_uuid = ?";
    	$result = $result && $this->dbExec($sql, array($storageuuid));
    
    	if($result){
    		$this->dbCommit();
    	}else{
    		$this->dbRollBack();
    	}
    
    	return $this->apiResponse($result, $type);
    }
    
    /**
     * 检测存储是否在正在运行的任务中,如果在的话,直接退出程序,给予提示
     * @param string $storageuuid
     */
    private function checkStorageInRunningTask($storageuuid){
    	$sql = "select task_name from bd_task where task_status = ? and storage_uuid = ?";
    	$sqlParams = array(Xphp::$_config['TASKSTATUS']['RUNNING'], $storageuuid);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$tasks = array();
    	foreach ($data as $d){
    		$tasks[] = $d['task_name'];
    	}
    	if(count($tasks) > 0){
    		$tasksStr = implode(',', $tasks);
    		return $this->apiResponse(false, 'API_CODE_STORAGES_DELETE_STORAGE_JOB_USED');
    	}
    }
    
    /**
     * 得到存储扫描的类型列显示结果
     * 添加备份存储显示标准类型 ; 添加lanfree存储显示虚拟化类型或者空
     * @param boolean $lanfreeFlag  lanfree标志
     * @param array $eachData       每项数据
     */
    private function getStorageScanTypeDes($lanfreeFlag, $eachData){
    	if(!$lanfreeFlag){
    		return $this->getStorageTypeDesDetail($eachData['type'], $eachData);
    	}else{
    		$hypervisor = intval($eachData['hypervisor']);
    		return Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
    	}
    }
    
    /**
     * 得到存储类型描述,这里主要是为了处理分区的类型,分区的时候获取详细类型,其余情况调用函数getStorageTypeDes
     * @param int $type
     * @param array $eachData
     */
    private function getStorageTypeDesDetail($type, $eachData){
    	if(intval($type) == Xphp::$_config['BD_STORAGE_TYPE']['PARTITION']){
    		$ptDes = include APP_PATH . 'platform/PFDescription.php';
    		$partitionType = intval($eachData['part_tran']);
    		$des = $ptDes['PARTITIONTYPE'][$partitionType];
    		return $des;
    	}else{
    		return $this->getStorageTypeDes($type);
    	}
    }
    /**
     * 得到存储类型描述
     * @param unknown $storageType
     */
    public function getStorageTypeDes($storageType){
    	$des = '';
    	if(empty($storageType)) return $des;
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$des = $ptDes['STORAGETYPE'][$storageType];
    	return $des;
    }
    
    /**
     * 得到WWN状态描述
     * @param unknown $status
     */
    private function getWwnStatusDes($status){
    	$ptDes = include APP_PATH . 'platform/PFDescription.php';
    	$des = $ptDes['ONLINEDES'][$status];
    	return $des;
    }
    
    
    /**
     * 根据虚拟化类型得到存储显示的名字
     * XenServer只有驱动器类型
     * VMware只有文件系统类型
     * @param unknown $hypervisor
     * @param unknown $storage
     */
    private function getHostStorageNameText($hypervisor, $storage){
    	$utils = Xphp::instance("Utils");
    	if(in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
    		//如果是VMware
    		$typeStr = 'filesystem_type';
    	}elseif($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM'] ||
    			$hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']){
    		//如果是openstack就不显示存储类型
    		$text = $storage['host_uuid'] . "(" . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($storage['total_size']) .
    		", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($storage['free_size']) . ")";
    
    		return $text;
    	}else{
    		//如果是XenServer,KVM
    		$typeStr = 'driver_type';
    	}
    	$text = $storage['storage_name'] . "(" . $storage[$typeStr] .
    	", " . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($storage['total_size']) .
    	", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($storage['free_size']) . ")";
    
    	return $text;
    }
    
    /**
     * 统一删除存储和lanfree的调用方法
     * @param string $storageuuid
     * @param string $type
     */
    private function UnifiedDeleteStorage($storageuuids, $type){
        $storageList = array();
        foreach($storageuuids as $uuid){
            $storageList[] = array(
                'storage_uuid' => $uuid
            );
        }
    	$nodeuuid = $this->getStorageInNode($storageuuids[0]);
    	//得到节点的状态
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeStatus = $nodeHandler->getNodeAllStatus($nodeuuid);
    	if($nodeStatus['flag']){
    		//如果节点在线,发送删除消息到节点进程处理
    	    $msg = array("storage_uuid_list" => $storageList);
    		$opName = "NODE_SR_OP_DELETE";
    		$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
    		foreach ($storageuuids as $uuid){
    		    $this->checkStorageDeleteResult($uuid, $mbResult);
    		}
    		return $this->apiResponse($mbResult['result'], $type, array(), $mbResult);
    	}
    	//节点不在线,检测是否有正在运行的任务使用该存储,如果有返回,没有的话直接删除存储和存储上的时间点记录
    	$result = true;
    	foreach ($storageuuids as $uuid){
    	    $this->checkStorageInRunningTask($uuid);
    	    $result = $result && $this->deleteStorageByWeb($uuid);
    	}
    	return $this->apiResponse($result, $type);
    }
    
    /**
     * 统一修改存储和lanfree别名的调用方法
     * @param string $storageuuid
     * @param string $storagename
     * @param string $type
     */
    private function UnifiedEditStorage($storageuuid, $storagename, $type){
    	$this->apiParamsCheck($storagename, $storageuuid);
    	$this->checkStorageNameExist($storagename); //检查是否重名
    	$sql = "select storage_nickname from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	
   		//添加告警配置
        $warningSetting = $this->params['warning_settings'];
        $value = $warningSetting['warning_value'];
        $warningFlag = $this->params['warning_flag'];
        $warningType = intval($warningSetting['warning_type']);
        if($value > 100 && $warningType == Xphp::$_config['STORAGEWARNINGTYPE']['PERCENT']){
        	return $this->apiResponse(false, 'API_CODE_ADD_STORAGE_WARNING_VALUE_ERROR');
        }
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $warningType){
        	//如果是按照大小来告警
        	$warningValue = intval($value) * 1024 * 1024 * 1024;
        }else{
        	$warningValue = intval($value);
        }
    	
    	$sql = "update bd_storage_resource set storage_nickname = ?, warning_flag = ?, warning_type = ?, 
                warning_value = ? where storage_uuid = ?";
    	$result = $this->dbExec($sql, array($storagename, $warningFlag, $warningType, $warningValue, $storageuuid));
    	
    	//插系统日志
    	$oldName = $data[0]['storage_nickname'];
    	$descriptionParam = array($oldName, $storagename);
    	if($result){
    		$this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_EDIT', $descriptionParam);
    	}else{
    		$this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_EDIT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
    	}
    	return $this->apiResponse($result, $type);
    }
    
    /**
     * 判断传入的存储uuid是否存在
     * @param string $storageuuid
     */
    private function checkStorageExist($storageuuid){
    	$sql = "select total_size from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	if(empty($data)){
    		return $this->apiResponse(false, 'API_CODE_STORAGE_DELETE_UUI_NOT_EXIST');
    	}
    }
    
    /**
     * 得到存储状态码
     * @param array $nodeAllStatus  节点所有程序状态
     * @param int $storageStatus    存储状态码
     * @param int $mountFlag        存储挂载标志
     * @return int
     */
    private function getStorageStatus($nodeAllStatus, $storageStatus, $mountFlag){
    	//首先检查节点的状态
    	if(!$nodeAllStatus['flag']){
    		//节点已经有异常了,部分程序不在线
    		return Xphp::$_config['STORAGE_STATUS']['OFFLINE'];
    	}
    	if($storageStatus == Xphp::$_config['STORAGE_STATUS']['ONLINE'] &&
    			$mountFlag == Xphp::$_config['FLAG']['SET']){
    		//存储在线 且挂载
    		return Xphp::$_config['STORAGE_STATUS']['ONLINE'];
    	}elseif ($storageStatus == Xphp::$_config['STORAGE_STATUS']['OFFLINE']){
    		//存储离线
    		return Xphp::$_config['STORAGE_STATUS']['OFFLINE'];
    	}elseif ($storageStatus == Xphp::$_config['STORAGE_STATUS']['ONLINE'] &&
    			$mountFlag == Xphp::$_config['FLAG']['UNSET']){
    		//在线未挂载
    		return Xphp::$_config['STORAGE_STATUS']['UNMOUNT'];
    	}else{
    		//其他所有状态(创建中,离线已挂载)统一为异常
    		return Xphp::$_config['STORAGE_STATUS']['CREATING'];
    	}
    }
    
    /**
     * 检查只有分区才支持导入功能
     * 
     */
    private function checkImportFlag($importFlag,$type){
    	if($type != Xphp::$_config['BD_STORAGE_TYPE']['PARTITION'] && $importFlag == Xphp::$_config['FLAG']['SET']){
    		return $this->apiResponse(false, 'API_CODE_STORAGE_NOT_SUPPORT_IMPORT_ERROR');
    	}
    }
    
    /**
     * 得到任务虚拟化类型
     *
     */
    private function getTaskHypervisor($uuid){
    	$sql = " select vbt.hypervisor_type from bd_backup_timepoint bbt,vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
    	$data = $this->dbSelect($sql,array($uuid));
    	return intval($data[0]['hypervisor_type']);
    }
    
    /**
     * 批量删除虚拟机导入数据
     * @param array $timepointuuids
     */
    private function deleteVMImportData($timepointuuids){
    	if(empty($timepointuuids)){
    		return true;
    	}
    	 
    	$deleteTimepointArr = array();
    	$timepointuuidsStr = implode("', '", $timepointuuids);
    	$sql = "select hypervisor_type, timepoint_uuid from vm_backup_timepoint where timepoint_uuid
    			in ('" . $timepointuuidsStr . "') order by hypervisor_type";
    	$data = $this->dbSelect($sql, array());
    	 
    	//根据hypervisor分组发送给后台批量删除，如果有一组失败直接返回失败错误消息，退出程序
    	$hypervisor = '0';
    	foreach ($data as $d){
    		if($hypervisor != $d['hypervisor_type']){
    			if(!empty($deleteTimepointArr)){
    				$jsonData = $this->deleteSelectTimepoint($deleteTimepointArr);
    				$result = json_decode($jsonData, true);
    				if(!$result['re']){
    					return false;
    				}
    				$deleteTimepointArr = array();
    			}else{
    				$deleteTimepointArr[] = array(
    						"nodeuuid" => $this->getNodeUUIDWithTimepointUUID($d['timepoint_uuid']),
    						"hypervisor" => $d['hypervisor_type'],
    						"timepointuuid" => $d['timepoint_uuid'],
    				);
    				$hypervisor = $d['hypervisor_type'];
    				continue;
    			}
    		}
    		$deleteTimepointArr[] = array(
    				"nodeuuid" => $this->getNodeUUIDWithTimepointUUID($d['timepoint_uuid']),
    				"hypervisor" => $d['hypervisor_type'],
    				"timepointuuid" => $d['timepoint_uuid'],
    		);
    		$hypervisor = $d['hypervisor_type'];
    	}
    	$jsonData = $this->deleteSelectTimepoint($deleteTimepointArr);
    	$result = json_decode($jsonData, true);
    	if(!$result['re']){
    		return false;
    	}
    	return true;
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
     * 删除批量备份时间点
     * @param array $params 二维数组
     * 如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     */
    public function deleteSelectTimepoint($params){
    	//得到二维数组
    	//遍历，并按节点uuid分组
    	//发送删除消息到不同的节点。删除消息array(timepointuuid,timepointuuid,timepointuuid,timepointuuid)
    	$utils = Xphp::instance('Utils');
    	$params = $utils->arraySort($params, 'nodeuuid', '', 0, -1);
    	 
    	$info = array();
    	$nodeuuids = array();
    	$timepointuuids = array();
    	$timepointuuid = array();
    	foreach($params as $d){
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
    	 
    	//     	$this->taskExist($uuidList);
    	$opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
    	for($i=0;$i<$nodeuuidsCount.length;$i++){
    		$msg = $timepointuuids[$i];
    		$msg = array(
    				'timepoint_uuids' => $timepointuuids[$i]
    		);
    		$msg = json_encode($msg);
    		$mbResult = $this->mbVMMsg($nodeuuids[$i], $info[$i]['hypervisor'], $opName, $msg);
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
    
}