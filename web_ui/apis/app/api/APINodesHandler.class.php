<?php
/******************************************* 
** 虚拟化中心处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APINodesHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/nodes/lists" => array(
        	"GET" => 'getNodes',
        ),
    	"/nodes" => array(
    		"GET" => 'getNodeUuid',
    		"PUT" => 'editNodeName',
    		"DELETE" => 'deleteNode',
    	),
    	"/nodes/nodeinfo" => array(
    		"GET" => 'getNodeInfo',
    	),
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    
    /**
     * 获取节点路由
     */
    protected function getNodes(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getNodesV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改节点名字路由
     */
    protected function getNodeUuid(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getNodeUuidV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改节点名字路由
     */
    protected function editNodeName(){
    	//定义方法版本
    	$version = array(
    		"v1" => "editNodeNameV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除节点路由
     */
    protected function deleteNode(){
    	//定义方法版本
    	$version = array(
    		"v1" => "deleteNodeV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 根据节点uuid得到节点信息路由
     */
    protected function getNodeInfo(){
    	//定义方法版本
    	$version = array(
    		"v1" => "getNodeInfoV1"
    	);
    	//检查版本
    	$this->checkVersion($version, $this->version);
    	return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********getNodes**********/
    private function getNodesV1(){
    	$begin = $this->params['begin'];
    	$count = $this->params['count'];
    	$this->apiParamsCheck($count);
    	$sql = "select ip, node_uuid, host_name, node_nickname, unix_timestamp(register_time) register_time from bd_node order by node_type limit ? , ?";
    	$sqlCount = "select count(node_uuid) as total from bd_node";
    	 
    	$sqlParams = array($begin, $count);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$dataCount = $this->dbSelect($sqlCount, array());
    	$utils = Xphp::instance("Utils");
    	$records = array();
    	$vcenterInfo = $this->getAllocationVcenters();

    	foreach ($data as $d){
    		$nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
    		$storageInfo = $this->getNodeStorageInfo($d['node_uuid']);
    		$vcenterList = array();
    		foreach ($vcenterInfo as $v){
    		    if($v['node_uuid'] == $d['node_uuid']){
                    $vcenterList[] = $v['vcenter_uuid'];
                }
            }

    		$records[] = array(
				'node_uuid' => $d['node_uuid'],
				'status' => $utils->parseBoolToFlag($nodeStatus['flag']),
				'ip' => $d['ip'],
				'name' => $d['host_name'],
				'nickname' => $d['node_nickname'],
				'register_time' => $d['register_time'],
    		    'total_size' => $storageInfo['total'],
    		    'free_size' => $storageInfo['free'],
    		    'used_size' => $storageInfo['used'],
                'vcenter_list' => $vcenterList
    		);
    	}
    	
    	$data = array(
    	    "total" => intval($dataCount[0]['total']),
    	    "begin" => $begin,
    	    "count" => $count,
    	    "records" => $records
    	);
    	
    	return $this->apiResponse(true, 'API_CODE_NODES_GET_NODE_LIST', $data);
    }
    
    /**********getNodeUuid**********/
    private function getNodeUuidV1(){
    	$nodeIp = $this->params['node_ip'];
    	$sql = "select node_uuid from bd_node where ip = ?";
    	$data = $this->dbSelect($sql, array($nodeIp));
    	$info = array(
    		"node_uuid" => $data[0]['node_uuid'],
    	);
    	return $this->apiResponse(true, 'API_CODE_NODES_GET_NODE_UUID', $info);
    }
    
    /**********editNodeName**********/
    private function editNodeNameV1(){
    	$nodename = trim($this->params['node_name']);
    	$nodeuuid = $this->params['node_uuid'];
    	$this->apiParamsCheck($nodename, $nodeuuid);
    	
    	$sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
    	$data = $this->dbSelect($sql, array($nodeuuid));
    	if (empty($data)) {
            return $this->apiResponse(false, 'API_CODE_NODES_DELETE_NOT_EXIST');
        }
    	
    	$sql = "update bd_node set node_nickname = ? where node_uuid = ?";
    	$result = $this->dbExec($sql, array($nodename, $nodeuuid));
    	//插系统日志
    	$oldName = empty($data[0]['node_nickname']) ? $data[0]['host_name'] : $data[0]['node_nickname'];
    	$ip = $data[0]['ip'];
    	$descriptionParam = array($oldName, $ip, $nodename, $ip);
    	if($result){
    		$this->systemLog('BD_SYSTEMLOG_DESC_KEY_NODE_MODIFY', $descriptionParam);
    	}else{
    		$this->systemLog('BD_SYSTEMLOG_DESC_KEY_NODE_MODIFY', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
    	}
    	return $this->apiResponse($result, 'API_CODE_NODES_EDIT_NODE_NAME');
    }
    
    /**********deleteNode**********/
    private function deleteNodeV1(){
     	$nodeuuid = $this->params['node_uuid'];
        $this->paramsCheck($nodeuuid);
        //检测是否是本地节点,本地节点不能删除
        $sql = "select node_type, ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        //传入的节点uuid不存在
        if(empty($data)){
        	return $this->apiResponse(false, 'API_CODE_NODES_DELETE_NOT_EXIST');
        }
        if($data[0]['node_type'] == Xphp::$_config['NODETYPE']['MASTER']){
            return $this->apiResponse(false, 'API_CODE_NODES_DELETE_LOCAL_ERROR');
        }
        $name = empty($data[0]['node_nickname']) ? $data[0]['host_name'] : $data[0]['node_nickname'];
        $ip = $data[0]['ip'];
        $descriptionParam = array($name, $ip);
        
        //检测节点是否还有存储使用
        $sql = "select count(storage_id) as total from bd_storage_resource where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if($data[0]['total'] > 0){
            return $this->apiResponse(false, 'API_CODE_NODES_DELETE_EXIST_STORAGE_ERROR');
        }
        
        //检测是否正常运行
        $nodeStatus = $this->getNodeAllStatus($nodeuuid);
        if($nodeStatus['flag']){
            //如果节点在线,提示去卸载
            return $this->apiResponse(false, 'API_CODE_NODES_DELETE_NODE_USED_ERROR');
        }
        
        //删除节点
        $sql = "delete from bd_node where node_uuid = ?";
        $result = $this->dbExec($sql, array($nodeuuid));
        
        //写系统日志
        if($result){
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_NODE_DELETE', $descriptionParam);
        }else{
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_NODE_DELETE', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
        }
        
        return $this->apiResponse($result, 'API_CODE_NODES_DELETE_NODE');
    }
    
    /**********getNodeInfo**********/
    private function getNodeInfoV1(){
    	$nodeuuid = $this->params['node_uuid'];
    	$sql = "select ip, host_name, node_nickname, unix_timestamp(register_time) register_time from bd_node where node_uuid = ?";
    	$data = $this->dbSelect($sql, array($nodeuuid));
    	$nodeStatus = $this->getNodeAllStatus($nodeuuid);
    	$utils = Xphp::instance("Utils");
    	$info = array(
			'status' => $utils->parseBoolToFlag($nodeStatus['flag']),
			'ip' => $data[0]['ip'],
			'name' => $data[0]['host_name'],
			'nickname' =>$data[0]['node_nickname'],
			'register_time' => $data[0]['register_time']
    	);
    	return $this->apiResponse(true, 'API_CODE_NODES_GET_NODE_INFO', $info);
    
    }
    
    
    /**************************************其他工具方法*******************************************/
    /**
     * 得到本地节点UUID
     * @return string
     */
    public function getLocalNodeUUID(){
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }
    
    /**
     * 得到存储所在节点UUID
     * @param string $storageUUID
     * @return string
     */
    public function getStorageExitNodeUUID($storageUUID){
        $sql = "select node_uuid from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageUUID));
        return $data[0]['node_uuid'];
    }
    
    /**
     * 得到任务所在的节点UUID
     * @param string $jobUUID
     * @return string
     */
    public function getJobExitNodeUUID($jobUUID){
        $sql = "select node_uuid from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobUUID));
        return $data[0]['node_uuid'];
    }
    
    /**
     * 得到节点总状态(存储也要调用,所以PUBLIC)
     * @param string $nodeuuid
     * @return array('flag'=> boolean, 'module'=>array(modules))
     */
    public function getNodeAllStatus($nodeuuid){
    	$sql = "select module_type, online_flag from bd_module_server where node_uuid = ?";
    	$data = $this->dbSelect($sql, array($nodeuuid));
    	$flag = true;
    	$module = array();
    	foreach ($data as $d){
    		if($d['online_flag'] == Xphp::$_config['FLAG']['UNSET']){
    			$flag = false;
    			$module[] = $d['module_type'];
    		}
    	}
    	if(empty($data)) $flag = false; //如果没有记录
    	$info = array(
    		'flag' => $flag,
    		'module' => $module
    	);
    	return $info;
    }
    
    /**
     * 得到节点展示名称
     * @param string $ip
     * @param string $nickname
     * @param string $hostname
     */
    private function getNodeShowName($ip, $nickname, $hostname){
    	//如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
    	if($ip == $nickname || empty($nickname)){
    		$name = $hostname . '(' . $ip . ')';
    	}else{
    		$name = $nickname . '(' . $ip . ')';
    	}
    	return $name;
    }
    
    /**
     * 得到自动选择的节点(任务最少的节点)
     * @return string nodeuuid
     */
    public function getAutoFindNode(){
    	$allAvailableNode = $this->getAddStorageNodeSelect();
    	$allAvailableNode = json_decode($allAvailableNode, true);
    	$allAvailableNode = $this->filterDisableNode($allAvailableNode);
    
    	if(empty($allAvailableNode)){
    		//如果没有可用的节点
    		return false;
    	}
    	if(1 == count($allAvailableNode)){
    		//如果只查找到 一个节点
    		return $allAvailableNode[0]['uuid'];
    	}
    	//所有可用节点的UUID
    	$allAvailableNodeuuid = array();
    	foreach ($allAvailableNode as $node){
    		$allAvailableNodeuuid[] = $node['uuid'];
    	}
    	//找到所有节点拥有的任务个数,注意下面排序使用了sql语句里面的num
    	$sql = "select bn.node_uuid, count(*) as num from bd_task bt, bd_node bn where
                bt.node_uuid = bn.node_uuid group by bn.node_uuid";
    	$data = $this->dbSelect($sql);
    
    	//找到所有使用过的节点
    	$allUsedNode = array();
    	$allUsedNodeFitler = array();
    	foreach ($data as $d){
    		if(in_array($d['node_uuid'], $allAvailableNodeuuid)){
    			//筛选可用节点中使用过的节点
    			$allUsedNode[] = $d['node_uuid'];
    			$allUsedNodeFitler[] = $d;
    		}
    	}
    
    	//从可用节点帅选一个从未使用过的节点返回UUID
    	foreach ($allAvailableNodeuuid as $uuid){
    		if(!in_array($uuid, $allUsedNode)){
    			return $uuid;
    		}
    	}
    	//到此所有的节点都使用过了,对可用的排个序,取出使用次数最少的,也就是任务数量最少的节点
    	$utils = Xphp::instance('Utils');
    	$allUsedNodeFitler = $utils->arraySort($allUsedNodeFitler, 'num', 'asc', 0, -1);
    
    	return $allUsedNodeFitler[0]['node_uuid'];
    }
    
    /**
     * 过滤掉不可用的节点(节点上没有可用存储)
     * @param array $allAvailableNode
     *  uuid => '', text => ''
     */
    private function filterDisableNode($allAvailableNode){
    	if(empty($allAvailableNode)){
    		//没有可用的节点
    		exit($this->apiResponse(false, 'API_CODE_NODES_NO_BACKUP_NODE'));
    	}
    	$availableNode = array();
    	foreach ($allAvailableNode as $node){
    		$sql = "select count(storage_id) as num from bd_storage_resource where status = ? and node_uuid = ?";
    		$data = $this->dbSelect($sql, array(Xphp::$_config['STORAGE_STATUS']['ONLINE'], $node['uuid']));
    		if($data[0]['num'] > 0){
    			$availableNode[] = $node;
    		}
    	}
    	if(empty($availableNode)){
    		//可选择的节点上都没有可用的存储
    		exit($this->apiResponse(false, 'API_CODE_NODES_NO_FIND_STORAGE'));
    	}
    	return $availableNode;
    }
    
    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     * @param unknown $params
     */
    public function getAddStorageNodeSelect(){
    	$sql = "select ip, node_uuid, host_name, node_nickname from bd_node order by node_type";
    	$data = $this->dbSelect($sql, array());
    	$list = array();
    	foreach ($data as $d){
    		$nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
    		if($nodeStatus['flag']){
    			//如果节点状态正常
    			$list[] = array(
    					'uuid' => $d['node_uuid'],
    					'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name'])
    			);
    		}
    	}
    	return json_encode($list);
    }
    
    /**
     * 得到节点部署状态
     * @param string $nodeuuid
     * @return intval
     */
    public function getNodeDeployStatus($nodeuuid){
        $sql = "select count(module_uuid) as total from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if($data[0]['total']){
            return Xphp::$_config['FLAG']['SET'];
        }else{
            return Xphp::$_config['FLAG']['UNSET'];
        }
    }
    
    
    public function getNodeStorageInfo($nodeuuid){
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size from bd_storage_resource where node_uuid = ? and storage_nickname = ? ";
        $data = $this->dbSelect($sql, array($nodeuuid, Xphp::$_config['FLAG']['UNSET']));
        $totalSize = intval($data[0]['total_size']);
        $freeSize = intval($data[0]['free_size']);
        $usedSize = $totalSize - $freeSize;
        $info = array(
            'total' => $totalSize,
            'used' => $usedSize,
            'free' => $freeSize,
        );
        
        return $info;
    }

    /**
     * 获取平台和节点关联列表
     * @return array
     */
    public function getAllocationVcenters(){
        $sql = "select node_uuid, platform_uuid from mt_platform_node where type = 1 order by node_uuid asc";
        $data = $this->dbSelect($sql);
        $list = array();
        foreach($data as $d){
             $list[] = array(
                 'node_uuid' => $d['node_uuid'],
                 'vcenter_uuid' => $d['platform_uuid']
             );
        }

        return $list;

    }

}

?>