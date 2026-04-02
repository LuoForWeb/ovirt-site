<?php
/******************************************* 
** 虚拟化中心处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APIVcentersHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/vcenters" => array(
            'POST' => 'addVcenters',
            'PUT' => 'editVcenters',
            'DELETE' => 'deleteVcenters',
            'GET' => 'getVcenters'
        ),
        
        "/vcenters/uuid" => array(
            'GET' => 'getVcentersUUID'
        ),
    
        "/vcenters/lists" => array(
            'GET' => 'getVcentersLists'
        ),
        
        "/vcenters/hosts/lists" => array(
            'GET' => 'getVcentersHostsLists'
        ),
        
        "/vcenters/hosts/license" => array(
            'POST' => 'licensedHost',
            'DELETE' => 'unlicensedHost',
        ),
        
        "/vcenters/refresh" => array(
            'GET' => 'refreshVcenter'
        ),
    	"/vcenters/destination_vcenters" => array(
    		'GET' => 'getDestinationVcenters'
    	),
    	"/vcenters/vmlists" => array(
    		'GET' => 'getVmDetailsList'
    	),
    	"/vcenters/host_config" => array(
    		'GET' => 'getHostConfig'
    	),
    	"/vcenters/openstack_config" => array(
    		'GET' => 'getOpenstackConfig'
    	),
    	"/vcenters/vmdisks" => array(
    		'GET' => 'getVmDiskList'
    	),
        "/vcenters/openstack_vmnums" => array(
            'GET' => 'getProtectVms'
        ),
        "/vcenters/getvmbyip" => array(
            'GET' => 'getVmConfigByIP'
        )
    		
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 添加虚拟化中心路由控制
     */
    protected function addVcenters(){
        //定义方法版本
        $version = array(
            "v1" => "addVcentersV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改虚拟化中心路由控制
     */
    protected function editVcenters(){
        //定义方法版本
        $version = array(
            "v1" => "editVcentersV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除虚拟化中心路由控制
     */
    protected function deleteVcenters(){
        //定义方法版本
        $version = array(
            "v1" => "deleteVcentersV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟化中心信息路由控制
     */
    protected function getVcenters(){
        //定义方法版本
        $version = array(
            "v1" => "getVcentersV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟化中心UUID信息路由控制
     */
    protected function getVcentersUUID(){
        //定义方法版本
        $version = array(
            "v1" => "getVcentersUUIDV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟化中心列表路由控制
     */
    protected function getVcentersLists(){
        //定义方法版本
        $version = array(
            "v1" => "getVcentersListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟化中心下宿主机列表路由控制
     */
    protected function getVcentersHostsLists(){
        //定义方法版本
        $version = array(
            "v1" => "getVcentersHostsListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 授权虚拟化中心下的宿主机路由控制
     */
    protected function licensedHost(){
        //定义方法版本
        $version = array(
            "v1" => "licensedHostV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 取消授权虚拟化中心下的宿主机路由控制
     */
    protected function unlicensedHost(){
        //定义方法版本
        $version = array(
            "v1" => "unlicensedHostV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 刷新虚拟化中心路由控制
     */
    protected function refreshVcenter(){
        //定义方法版本
        $version = array(
            "v1" => "refreshVcenterV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到恢复目的宿主列表路由控制
     */
    protected function getDestinationVcenters(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getDestinationVcentersV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟机磁盘信息路由控制
     */
    protected function getVmDetailsList(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getVmDetailsListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟机磁盘信息路由控制
     */
    protected function getHostConfig(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getHostConfigV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟机磁盘信息路由控制
     */
    protected function getOpenstackConfig(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getOpenstackConfigV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到虚拟机磁盘列表信息
     */
    protected function getVmDiskList(){
    	//定义方法版本
    	$version = array(
    			"v1" => "getVmDiskListV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 得到openstack租户下总虚拟机个数
     */
    protected function getProtectVms(){
        //定义方法版本
        $version = array(
            "v1" => "getProtectVmsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 根据虚拟机IP获取虚拟机信息
     */
    protected function getVmConfigByIP(){
        //定义方法版本
        $version = array(
            "v1" => "getVmConfigByIPV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**********addVcenters**********/
    private function addVcentersV1(){
        $hypervisor = intval($this->params['hypervisor']);
        $vcenterIP = $this->params['vcenter_ip'];
        $username = $this->params['user_name'];
        $password = $this->params['password'];
        $alias = $this->params['alias'];
        $detail = $this->params['detail'];
       	$detailInfo = "";
       	if(!empty($detail)){
       		$detailInfo = json_encode($detail);
       	}
        $this->apiParamsCheck($hypervisor, $vcenterIP, $username, $password, $alias);
        
        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_ADD';
        //组合消息
        $msg = array(
            'hypervisor_type' => $hypervisor,
            'ip' => $vcenterIP,
            'username' => $username,
            'password' => base64_encode($password),
            'nickname' => $alias,
            'display_mode' => Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
        	'detail' => $detailInfo
        );
        $nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg));
        
        $data = array();
        if($mbResult['result']){
            //如果成功,返回新建资源uuid
            $data['vcenter_uuid'] = $this->getVcenterUUIDWithVcenterIP($vcenterIP);
        }
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_ADD', $data, $mbResult);
        
    }
    
    /**********editVcenters**********/
    private function editVcentersV1(){
        $vcenterUUID = $this->params['vcenter_uuid'];
        $hypervisor = intval($this->params['hypervisor']);
        $vcenterIP = $this->params['vcenter_ip'];
        $username = $this->params['user_name'];
        $password = $this->params['password'];
        $alias = $this->params['alias'];
        $detail = $this->params['detail'];
        //$detail = $this->getVcenterDetails($vcenterUUID);
        $detailInfo = "";
        if(!empty($detail)){
            $detailInfo = json_encode($detail);
        }
        //检查参数
        $this->apiParamsCheck($vcenterUUID, $hypervisor, $vcenterIP, $username, $password, $alias);
        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_MODIFY';
        //组合消息
        $msg = array(
            'vcenter_uuid' => $vcenterUUID,
            'hypervisor_type' => $hypervisor,
            'ip' => $vcenterIP,
            'username' => $username,
            'password' => base64_encode($password),
            'nickname' => $alias,
            'display_mode' => Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
        	'detail' => $detailInfo
        );
        
        $nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg));
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_EDIT', array(), $mbResult);
        
    }
    
    /**********deleteVcenters**********/
    private function deleteVcentersV1(){
        $hypervisor = $this->params['hypervisor'];
        $vcenterUUID = $this->params['vcenter_uuid'];
        $this->apiParamsCheck($hypervisor, $vcenterUUID);
        $this->cheackVcenterExist($hypervisor, $vcenterUUID);
        $this->deleteVcenterCheck(array($vcenterUUID));
        $opName = 'VM_VCENTER_OP_DELETE';
        $msg = array('vcenter_uuid_list' => array($vcenterUUID));
        
        $nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg));
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_DELETE', array(), $mbResult);
        
    }
    
    /**********getVcentersUUID**********/
    private function getVcentersUUIDV1(){
        $vcenterIP = $this->params['vcenter_ip'];
        $this->apiParamsCheck($vcenterIP);
        $vcenterUUID = $this->getVcenterUUIDWithVcenterIP($vcenterIP);
        $data = array(
            'vcenter_uuid' => $vcenterUUID
        );
        
        return $this->apiResponse(!empty($vcenterUUID), 'API_CODE_VCENTERS_GET_UUID', $data);
    }
    
    /**********getVcenters**********/
    private function getVcentersV1(){
        $vcenterUUID = $this->params['vcenter_uuid'];
        $this->apiParamsCheck($vcenterUUID);
        
        $sql = "select vcenter_ip, username, vcenter_name, nickname, online_flag, unix_timestamp(register_time) register_time,
                 unix_timestamp(refresh_time) refresh_time, version, 
                hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenterUUID));
        $record = array();
        if(!empty($data)){
            $record = array(
                "vcenter_ip" => $data[0]['vcenter_ip'],
                "user_name" => $data[0]['username'],
                "alias" => $data[0]['nickname'],
            	"vcenter_name" => $data[0]['vcenter_name'],
                "online_flag" => $data[0]['online_flag'],
                "register_time" => $data[0]['register_time'],
                "refresh_time" => $data[0]['refresh_time'],
                "version" => $data[0]['version'],
                "hypervisor" => $data[0]['hypervisor_type'],
                "vcenter_uuid" => $vcenterUUID
            );
        }
        
        return $this->apiResponse(true, 'API_CODE_VCENTERS_GET_VCENTER_INFO', $record);
    }
    
    /**********getVcentersLists**********/
    private function getVcentersListsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $this->apiParamsCheck($count);
        $sql = "select vcenter_ip, username, vcenter_name, nickname, online_flag, unix_timestamp(register_time) register_time,
                 unix_timestamp(refresh_time) refresh_time, version, detail, 
                hypervisor_type, vcenter_uuid from vm_vcenter limit ? , ?";
        $sqlCount = "select count(vcenter_uuid) as total from vm_vcenter";
        $data = $this->dbSelect($sql, array($begin, $count));
        $dataCount = $this->dbSelect($sqlCount, array());
        $records = array();
        foreach ($data as $d){
        	$detail = array();
        	if(!empty($d['detail'])){
        		$detail = json_decode($d['detail'], true);
        	}else{
        		$detail = '';
        	}
            $records[] = array(
                "vcenter_ip" => $d['vcenter_ip'],
                "user_name" => $d['username'],
                "alias" => $d['nickname'],
            	"vcenter_name" => $d['vcenter_name'],
                "online_flag" => $d['online_flag'],
                "register_time" => $d['register_time'],
                "refresh_time" => $d['refresh_time'],
                "version" => $d['version'],
                "hypervisor" => $d['hypervisor_type'],
                "vcenter_uuid" => $d['vcenter_uuid'],
            	"detail" => $detail
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_VCENTERS_GET_VCENTER_LIST", $data);
    }
    
    /**********getVcentersHostsLists**********/
    private function getVcentersHostsListsV1(){
        $vcenterUUID = $this->params['vcenter_uuid'];
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $this->apiParamsCheck($vcenterUUID, $count);
        $sql = "select host_uuid, host_ip, host_name, online_flag, authorization_flag, version, cpu_count 
                from vm_host where vcenter_uuid = ? limit ? , ?";
        $sqlCount = "select count(host_uuid) as total from vm_host where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenterUUID, $begin, $count));
        $dataCount = $this->dbSelect($sqlCount, array($vcenterUUID));
        
        $hypervisor = $this->getHypervisorWithVcenterUUID($vcenterUUID);
        $records = array();
        foreach ($data as $d){
            $records[] = array(
                "host_uuid" => $d['host_uuid'],
                "host_ip" => $d['host_ip'],
                "host_name" => $d['host_name'],
                "online_flag" => $d['online_flag'],
                "authorization_flag" => $d['authorization_flag'],
                "version" => $d['version'],
                "cpu_count" => $d['cpu_count'],
                "hypervisor" => $hypervisor,
                "vcenter_uuid" => $vcenterUUID,
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_VCENTERS_GET_HOST_LIST", $data);
    }
    
    /**********licensedHost**********/
    private function licensedHostV1(){
        $hosts = $this->params['hosts'];
        $this->apiParamsCheck($hosts);
        $hostList = array();
        foreach ($hosts as $host){
            $hostList[] = array(
                "vcenter_uuid" => $host['vcenter_uuid'],
                "vm_host_uuid" => $host['host_uuid'],
            );
        }
        $msg = array('host_list' => $hostList);
        $opName = "PT_LICENSE_OP_ADD_VM_LICENSE";
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_HOST_LICENSE', array(), $mbResult);
    }
    
    /**********unlicensedHost**********/
    private function unlicensedHostV1(){
        $hosts = $this->params['hosts'];
        $this->apiParamsCheck($hosts);
        $hostList = array();
        foreach ($hosts as $host){
            $hostList[] = array(
                "vcenter_uuid" => $host['vcenter_uuid'],
                "vm_host_uuid" => $host['host_uuid'],
            );
        }
        $msg = array('host_list' => $hostList);
        $opName = "PT_LICENSE_OP_DEL_VM_LICENSE";
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_HOST_UNLICENSE', array(), $mbResult);
    }
    
    /**********refreshVcenter**********/
    private function refreshVcenterV1(){
        $vcenterUUID = $this->params['vcenter_uuid'];
        $this->apiParamsCheck($vcenterUUID);
        
        $hypervisor = $this->getHypervisorWithVcenterUUID($vcenterUUID);
        $displayMode = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
        $msg = array(
            'vcenter_uuid'=>$vcenterUUID, 
            'display_mode' => $displayMode
        );
        
        $opName = 'VM_VCENTER_OP_REFLASH';
        $nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg));
        
        return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_REFRESH', array(), $mbResult);
    }
    
    /**********getDestinationVcenters**********/
    private function getDestinationVcentersV1(){
    	$hypervisor = $this->params['hypervisor'];
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$sql = "select vcenter_uuid, vcenter_ip, online_flag, nickname, version from vm_vcenter where hypervisor_type = ? limit ?, ?";
    	$sqlparams = array($hypervisor, $begin, $count);
    	$data = $this->dbSelect($sql,$sqlparams);
    	$sqlCount = "select count(vcenter_uuid) as total from vm_vcenter where hypervisor_type = ?";
    	$dataCount = $this->dbSelect($sqlCount, array($hypervisor));
    	$records = array();
    	foreach ($data as $d){
    		$records[] = array(
    			"hypervisor" => $hypervisor,
    			"vcenter_uuid" => $d['vcenter_uuid'],
    			"vcenter_ip" => $d['vcenter_ip'],
    		    "online_flag" =>$d['online_flag'],
    			"version" => $d['version'],	
    			"nickname" => $d['nickname']
    		);
    	}
    	
    	$data = array(
    			"total" => intval($dataCount[0]['total']),
    			"begin" => $begin,
    			"count" => $count,
    			"records" => $records
    	);
    	return $this->apiResponse(true, 'API_CODE_VCENTERS_GET_DESTINATION_LIST', $data);
    }
    
    /**********getVmDetailsList**********/
    private function getVmDetailsListV1(){
    	$vcenteruuid= $this->params['vcenter_uuid'];
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$vmname = $this->params['vm_name'];
    	$utils = Xphp::instance('Utils');
    	$this->apiParamsCheck($vcenteruuid, $count);
    	$sql = "select host_uuid, detail, type, uuid, name, dir_path from vm_tree where display_mode = ? and type = ? ";
    	$sqlCount = "select count(distinct uuid, vcenter_uuid) as total from vm_tree where display_mode = ? and type = ? ";
    	$sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
    	$sqlCountParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
    	//按虚拟机名搜索
    	$vmname = $utils->escapeWildcard($vmname);
    	if($this->checkEmpty($vmname)){
    	    $sql .= " and name like ? ";
    	    $sqlCount .= " and name like ? ";
    	    $sqlParams = array_merge($sqlParams,array('%'.$vmname.'%'));
    	    $sqlCountParams = array_merge($sqlCountParams,array('%'.$vmname.'%'));
    	}
    	
    	//虚拟化中心id
    	if(!empty($vcenteruuid)){
    	    $sql .= " and vcenter_uuid = ? ";
    	    $sqlCount .= " and vcenter_uuid = ? ";
    	    $sqlParams = array_merge($sqlParams,array($vcenteruuid));
    	    $sqlCountParams = array_merge($sqlCountParams,array($vcenteruuid));
    	}
    	
    	$sql .= " order by name asc limit ?, ? ";
    	$sqlParams = array_merge($sqlParams,array($begin, $count));
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
    	$records = array();
    	foreach ($data as $d){
    		if(intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST']) continue;
    		$vmconfig = json_decode($d['detail'], true);
    		$records[] = array(
				"vm_name" => $d['name'],
				"vm_uuid" => $d['uuid'],
				"vm_path" => $d['dir_path'],
				"vcenter_uuid" => $vcenteruuid,
    		    "host_uuid" => $d['host_uuid'],
				"vm_config" => $vmconfig,
    		);
    	}
    	 
    	$data = array(
			"total" => intval($dataCount[0]['total']),
			"begin" => $begin,
			"count" => $count,
			"records" => $records
    	);
    	return $this->apiResponse(true, 'API_CODE_VCENTERS_GET_VM_DISK_LIST', $data);
    	 
    }
    
    
    /**********getHostConfig**********/
    private function getHostConfigV1(){
    	$vcenteruuid = $this->params['vcenter_uuid'];
    	$hostuuid = $this->params['host_uuid'];
    	$this->apiParamsCheck($vcenteruuid, $hostuuid);
    	$sql = "select hypervisor_type from vm_vcenter where vcenter_uuid= ?";
    	$data= $this->dbSelect($sql, array($vcenteruuid));
    	$submoduletype = $data[0]['hypervisor_type'];
    	$nodeuuid = Xphp::instance('APINodesHandler', 'getLocalNodeUUID');
    	 
    	$network = array();
    	$storage = array();
    	 
    	//获取存储信息
    	$opName = 'VM_VCENTER_OP_QUERY_STORAGE';
    	$msg = json_encode(array('vcenter_uuid'=>$vcenteruuid, 'host_uuid'=>$hostuuid));
    	$mbResult = $this->mbVMMsg($nodeuuid, $submoduletype, $opName, $msg, true);
    	if($mbResult['result']){
    		$storageList = $mbResult['msg']['storage_resource_list'];
    		foreach ($storageList as $list){
    			$storage[] = array(
					'uuid' => $list['storage_uuid'],
					'storage_name' => (string)$list['storage_name'],
					'filesystem_type' => (string)$list['filesystem_type'],
					'totalsize' => $list['total_size'],
					'freesize' => $list['free_size'],
    			);
    		}
    	}
    	//获取网络信息
    	$opName = 'VM_VCENTER_OP_QUERY_HOST_NETWORK_LIST';
    	$msg = json_encode(array('vcenter_uuid'=>$vcenteruuid, 'host_uuid'=>$hostuuid));
    	$mbResult = $this->mbVMMsg($nodeuuid, $submoduletype, $opName, $msg, true);
    	if($mbResult['result']){
    		$networkList = $mbResult['msg']['network_list'];
    		foreach ($networkList as $list){
    			if($list['network_uuid'] == "0") continue;
    			$network[] = array(
					'uuid' => $list['network_uuid'],
					'text' => $this->getHostNetworkNameText($list)
    			);
    		}
    	}
    	$info = array(
    			'vcenter_uuid' => $vcenteruuid,
    			'host_uuid' => $hostuuid,
    			'network_list' => $network,
    			'storage_list' => $storage
    	);
    	return $this->apiResponse(true, 'API_CODE_VCENTERS_GET_HOST_CONFIG', $info);
    }
    
    /**********getOpenstackConfig**********/
    private function getOpenstackConfigV1(){
    	$vcenteruuid = $this->params['vcenter_uuid'];
    	$hypervisor = $this->params['hypervisor'];
    	$groupname = $this->params['group_name'];
    	$groupuuid = $this->params['group_uuid'];
    	$username = $this->params['user_name'];
    	$password = $this->params['password'];
    	$this->apiParamsCheck($vcenteruuid, $groupname, $username, $password);
    	 
    	$network = array();
    	$storage = array();
    	 
    	$msg = array(
			'vcenter_uuid' => $vcenteruuid,
			'group_name' => $groupname,
			'group_uuid' => $groupuuid,
			'user_name' => $username,
			'password' => $password
    	);
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeuuid = $nodeHandler->getLocalNodeUUID();
    	$msg = json_encode($msg);
    	//获取存储信息
    	$opName = 'VM_VCENTER_OP_QUERY_USER_GROUP_STORAGE_SIZE';
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
    	if($mbResult['result']){
    		$storageList = $mbResult['msg']['storage_list'];
    		foreach ($storageList as $list){
    			$storage[] = array(
					'uuid' => $list['host_uuid'],
					'storage_name' => $list['storage_name'],
					'filesystem_type' => $list['filesystem_type'],
					'totalsize' => $list['total_size'],
					'freesize' => $list['free_size'],
    			);
    		}
    	}
    	 
    	//获取网络信息
    	$opName = 'VM_VCENTER_OP_QUERY_USER_GROUP_PHYSICAL_NETWORK';
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
    	if($mbResult['result']){
    		$networkList = $mbResult['msg']['network_list'];
    		foreach ($networkList as $list){
    			$network[] = array(
					'uuid' => $list['network_uuid'],
					'text' => $list['network_name'],
    			);
    		}
    	}
    	 
    	$info = array(
    		'vcenter_uuid' => $vcenteruuid,
    		'group_uuid' => $groupuuid,
			'network_list' => $network,
			'storage_list' => $storage
    	);
    	return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_GET_OPENSTACK_CONFIG', $info, $mbResult);
    }
    
    /**********getVmDiskList**********/
	private function getVmDiskListV1(){
		$vcenteruuid = $this->params['vcenter_uuid'];
		$vmuuid = $this->params['vm_uuid'];
		//获取磁盘信息操作码
		$opcodeName = 'VM_VCENTER_OP_GET_VM_DISK_LIST';
		//组合消息
		$msg = array(
				'vm_uuid' => $vmuuid,
				'vcenter_uuid' => $vcenteruuid
		);
		
		$hypervisor = $this->getHypervisorWithVcenterUUID($vcenteruuid); //根据vcenteruuid获取hypervisor_type
		$apiNodesHandler = Xphp::instance('APINodesHandler');
		$nodeuuid = $apiNodesHandler->getLocalNodeUUID();
		$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), TRUE);
		$info =array();
		if($mbResult['result']){
			$info = $mbResult['msg'];
		}
		
		return $this->apiResponse($mbResult['result'], 'API_CODE_VCENTERS_GET_VM_DISKS', $info, $mbResult);
	}
	
	/**********getProtectVms**********/
	private function getProtectVmsV1(){
	   $name = $this->params['protect_name'];
	   $this->cheackProtectExist($name);
	   $sql = "select uuid from vm_tree where name = ? and type = ? ";
	   $data = $this->dbSelect($sql, array($name, Xphp::$_config['VM_TREE_TYPE']['FOLDER']));
	   $protectuuid = $data[0]['uuid'];
	   $sqlVms = "select uuid from vm_tree where parent_uuid = ? and type = ? ";
	   $dataVms = $this->dbSelect($sqlVms, array($protectuuid, Xphp::$_config['VM_TREE_TYPE']['VM']));
	   $totalSize = 0;
	   foreach ($dataVms as $vm){
	       $totalSize += $this->getVMTotalSize($vm['uuid']);
	   }
	   $info = array(
	       'backup_total_size' =>  $totalSize,
	       'vm_num' => count($dataVms)
	   );
	    
	   return $this->apiResponse(true, 'API_CODE_VCENTERS_GET_PROTECT_BACKUP_SIZE', $info);
	}
	
	/**********getVmConfigByIP**********/
	private function getVmConfigByIPV1(){
	    $ip = $this->params['ip'];
	    $sql = "select uuid, name, detail, vcenter_uuid from vm_tree where type = 7 and display_mode = 1 and detail like '%".$ip."%' ";
	    $data = $this->dbSelect($sql);
	    $info = array();
	    foreach ($data as $d){
            $info = array(
                'vm_uuid' => $d['uuid'],
                'vm_name' => $d['name'],
                'vcenter_uuid' => $d['vcenter_uuid']
            ); 
	        
	    }
	    
	    
	    return $this->apiResponse(true, 'API_CODE_VCENTERS_GET_VM_CONFIG_BY_IP', $info);
	}
	
   
    
    /*************************************其他工具方法********************************************/
    /**
     * 删除虚拟化中心检查
     * @param string $vcenterUUIDs
     * @return boolean
     */
    private function deleteVcenterCheck($vcenterUUIDs){
        $vcenterStr = "";
        foreach ($vcenterUUIDs as $vcenter){
            $vcenterStr .= $vcenter . " ,"; 
        }
        if($vcenterStr){
            $vcenterStr = substr($vcenterStr, 0, -1);
        }
        //检测备份和恢复任务
        $sql = "select vv.nickname, bt.task_name from vm_vcenter vv,vm_machine_list vml, bd_task bt  
                where vv.vcenter_uuid = vml.vcenter_uuid 
                and bt.delete_flag = ? 
                and vml.task_uuid = bt.task_uuid 
                and vml.vcenter_uuid in ( ? ) 
                group by vv.nickname ";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $vcenterStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //如果虚拟化中心有虚拟机存在于备份或恢复任务中,直接返回
            $msg = Xphp::$_lang['WEB_VM_VCENTER'] . "'" . $data[0]['nickname'] . "'" . 
                    Xphp::$_lang['WEB_VM_VCENTER_DELETE_TIPS'] . "'" . $data[0]['task_name'] . "'";
            
            return $this->apiResponse(false, 'API_CODE_VCENTERS_DELETE_CHECK_BAK_REC_JOB');
        }
        //检测瞬时恢复和迁移任务
        $sql = "select vv.nickname, bt.task_name from vm_vcenter vv,vm_instant vi, bd_task bt  
                where vv.vcenter_uuid = vi.target_vcenter_uuid 
                and bt.delete_flag = ? 
                and vi.task_uuid = bt.task_uuid 
                and vi.target_vcenter_uuid in ( ? ) 
                group by vv.nickname ";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $vcenterStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //如果虚拟化中心有虚拟机存在于瞬时恢复或迁移任务中,直接返回
            $msg = Xphp::$_lang['WEB_VM_VCENTER'] . "'" . $data[0]['nickname'] . "'" .
                Xphp::$_lang['WEB_VM_VCENTER_DELETE_TIPS'] . "'" . $data[0]['task_name'] . "'";
            return $this->apiResponse(false, 'API_CODE_VCENTERS_DELETE_CHECK_INS_MOT_JOB');
        }
        return true;
    }
    
    /**
     * 通过vcenteruuid得到虚拟化类型
     * @param string $vcenterUUID
     * @return string
     */
    public function getHypervisorWithVcenterUUID($vcenterUUID){
        $sql = "select hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenterUUID));
        return $data[0]['hypervisor_type'];
    }
    
    /**
     * 通过vcenterip得到vcenteruuid
     * @param string $vcenterIP
     * @return string
     */
    private function getVcenterUUIDWithVcenterIP($vcenterIP){
        $sql = "select vcenter_uuid from vm_vcenter where vcenter_ip like ?";
        $data = $this->dbSelect($sql, array('%'.$vcenterIP.'%'));
        $vcenterUUID = empty($data[0]['vcenter_uuid']) ? "" : $data[0]['vcenter_uuid'];
        return $vcenterUUID;
    }
    
    /**
     * 得到宿主机网卡的名字显示
     * @param unknown $network
     */
    private function getHostNetworkNameText($network){
    	$text = $network['network_name'];
    	if(!empty($network['ip_address'])){
    		$text .= "(" . $network['ip_address'] . ")";
    	}
    	return $text;
    }
    
    
    /**
     * 检查虚拟化中心uuid是否存在
     * @param int hypervisor
     * $param string $vcenteruuid 
     */
    private  function cheackVcenterExist($hypervisor, $vcenteruuid){
    	$sql = "select vcenter_ip from vm_vcenter where hypervisor_type = ? and vcenter_uuid = ?";
    	$data = $this->dbSelect($sql, array($hypervisor, $vcenteruuid));
    	if(empty($data)){
    		return $this->apiResponse(false, 'API_CODE_VCENTERS_DELETE_UUID_NOT_EXIST');
    	}
    }
    
    /**
     * 获取某个虚拟机的磁盘列表
     * @param string $vmuuid
     * @param string $vcenteruuid
     * @param int $hypervisor 
     */
    private function getAsyncVmDisks($vmuuid, $vcenteruuid){
    	//获取磁盘信息操作码
    	$opcodeName = 'VM_VCENTER_OP_GET_VM_DISK_LIST';
    	//组合消息
    	$msg = array(
    		'vm_uuid' => $vmuuid,
    		'vcenter_uuid' => $vcenteruuid
    	);
    	 
    	$hypervisor = $this->getHypervisorWithVcenterUUID($vcenteruuid); //根据vcenteruuid获取hypervisor_type
    	$apiNodesHandler = Xphp::instance('APINodesHandler');
    	$nodeuuid = $apiNodesHandler->getLocalNodeUUID();
    	$mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), TRUE);
    	$info =array();
    	if($mbResult['result']){
    		$info = $mbResult['msg'];
    	}
    	return $info;
    }
    
    /**
     * 获取某些虚拟化中心特殊参数配置
     * @param string $vcenteruuid
     */
    private function getVcenterDetails($vcenteruuid){
    	$sql = "select detail from vm_vcenter where vcenter_uuid = ? ";
    	$data = $this->dbSelect($sql, array($vcenteruuid));
    	$detail = $data[0]['detail'];
    	if(!empty($detail)){
    		$detail = json_decode($detail, true);
    	}
    	
    	return $detail;
    }
    
    /**
     * 检查租户是否在线
     * @param string $name
     * @return unknown|boolean
     */
    private function cheackProtectExist($name){
        $sql = "select uuid from vm_tree where name = ? and type = ?";
        $data = $this->dbSelect($sql, array($name, Xphp::$_config['VM_TREE_TYPE']['FOLDER']));
        if(empty($data)){
            return $this->apiResponse(false, 'API_CODE_VCENTERS_OPENSTACK_PROTECT_NOT_EXIST');
        }
            
        return true;
    }
    
    /**
     * 获取每个虚拟机备份容量
     * @param string $uuid
     * @return number
     */
    private function getVMTotalSize($uuid){
        $sql = "select sum(bbt.total_size) as total_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and vbt.vm_uuid = ? ";
        $data = $this->dbSelect($sql, array($uuid));
        $size = 0;
        if(!empty($data[0]['total_size'])){
            $size = intval($data[0]['total_size']);
        }
        
        return $size;
    }
    
    /**
     * 公共函数
     * 获取宿主机详细信息
     * @param unknown $hostuuid
     * @return fetchAll()
     */
    public function pGetSelectHostInfo($hostuuid){
        $sql = "select vv.vcenter_uuid, vv.hypervisor_type, vh.host_uuid, vh.host_name, vh.host_ip from vm_host vh, vm_vcenter vv where vh.vcenter_uuid = vv.vcenter_uuid and vh.host_uuid = ?";
        return $this->dbSelect($sql, array($hostuuid));
    }
    
}