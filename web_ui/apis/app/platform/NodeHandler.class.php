<?php
/******************************************* 
** 节点管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2016-05-23 下午15:06:44 
** @version      1.0.0 
** @copyright    Copyright 2016 vinchin.com 
********************************************/
class NodeHandler extends OPHandler{
    
    /**
     * 添加节点
     * @param unknown $params
     */
    public function addNode($params){
        //TODO 暂时不支持远程部署
        return $this->muOpResult(false, Xphp::$_lang['WEB_NODE_NOT_SUPPORT_AUTO_DEPLOY'], Xphp::$_lang['WEB_NODE_NOT_SUPPORT_AUTO_DEPLOY_TIPS']);
        $ip = $params['ip'];
        $port = $params['port'];
        $username = $params['username'];
        $password = base64_decode($params['password']);
        $rname = $params['rname'];
        $this->paramsCheck($ip, $port, $username, $password);
        $sshAvailable = $this->checkSshAvailable($ip, $port, $username, $password);
        if(!$sshAvailable){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_ADD'], Xphp::$_lang['WEB_NODE_SSH_FAILURE'], 'warning'));
        }
        $sql = "insert into bd_node (auto_upgrade_flag, ip, port, user_name, password, node_uuid, node_nickname, register_time, host_name) 
            values (?, ?, ?, ?, ?, ?, ?, ?, ?) ";
        $utils = Xphp::instance('Utils');
        $sqlParams = array(
            Xphp::$_config['FLAG']['SET'], $ip, $port, $username, base64_encode($password), 
            $utils->uuid(), $rname, date('Y-m-d H:i:s'), $this->getRemoteServerHostname($ip, $port, $username, $password)
        );
        $result = $this->dbQuery($sql, $sqlParams);
        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_ADD']);
    }
    
    /**
     * 删除节点
     * @param unknown $params
     */
    public function deleteNode($params){
        $nodeuuid = $params['uuids'][0];
        $this->paramsCheck($nodeuuid);
        //检测是否是本地节点,本地节点不能删除
        $sql = "select node_type, ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if($data[0]['node_type'] == Xphp::$_config['NODETYPE']['MASTER']){
            return $this->muOpResult(false, Xphp::$_lang['WEB_NODE_DELETE_TIPS'], Xphp::$_lang['WEB_NODE_DELETE_LOCAL_TIPS'], 'info');
        }
        $name = empty($data[0]['node_nickname']) ? $data[0]['host_name'] : $data[0]['node_nickname'];
        $ip = $data[0]['ip'];
        $descriptionParam = array($name, $ip);
        
        //检测节点是否还有存储使用
        $sql = "select count(storage_id) as total from bd_storage_resource where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if($data[0]['total'] > 0){
            return $this->muOpResult(false, Xphp::$_lang['WEB_NODE_DELETE_TIPS'], Xphp::$_lang['WEB_NODE_STORAGE_TIPS'], 'warning');
        }
        
        //检测是否正常运行
        $nodeStatus = $this->getNodeAllStatus($nodeuuid);
        if($nodeStatus['flag']){
            //如果节点在线,提示去卸载
            return $this->muOpResult(false, Xphp::$_lang['WEB_NODE_DELETE_TIPS'], Xphp::$_lang['WEB_NODE_IN_USED'], 'info');
        }
        
        //删除节点
        $sql = "delete from bd_node where node_uuid = ?";
        $result = $this->dbExec($sql, array($nodeuuid));
        
        //开始事务
        $this->dbBeginTransaction();
        //删除表bd_node
        $sql = "delete from bd_node where node_uuid = ?";
        $result = $this->dbExec($sql, array($nodeuuid));
        //删除表bd_module_server
        $sql = "delete from bd_module_server where node_uuid = ?";
        $result = $result && $this->dbExec($sql, array($nodeuuid));
        
        //写系统日志
        if($result){
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_NODE_DELETE', $descriptionParam);
            $this->dbCommit();
        }else{
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_NODE_DELETE', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
            $this->dbRollBack();
        }
        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_DELETE']);
    }
    
    /**
     * 修改节点名字
     * @param unknown $params
     */
    public function editNodeHostName($params){
        $nodename = trim($params['nodename']);
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodename, $nodeuuid);
        
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        
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
        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_EDIT_INFO']);
    }
    
    /**
     * 得到节点名字
     * @param unknown $params
     */
    public function getNodeHostName($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        
        $sql = "select host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $nodename = $data[0]['node_nickname'];
        if(empty($nodename)){
            $nodename = $data[0]['host_name'];
        }
        $info = array(
            'nodeuuid' => $nodeuuid,
            'nodename' => $nodename
        );
        return json_encode($info);
    }
    
    /**
     * 检测SSH连接是否成功
     * @param string $ip    
     * @param string $port
     * @param string $username
     * @param string $password
     * @return boolean 
     */
    private function checkSshAvailable($ip, $port, $username, $password){
        $connection = ssh2_connect($ip, $port);
        if(!$connection) return FALSE;  //Connection failed
        return ssh2_auth_password($connection, $username, $password);
    }
    
    /**
     * 得到远程服务器的主机名
     * @param unknown $ip
     * @param unknown $port
     * @param unknown $username
     * @param unknown $password
     * @return string
     */
    private function getRemoteServerHostname($ip, $port, $username, $password){
        $connection = ssh2_connect($ip, $port);
        ssh2_auth_password($connection, $username, $password);
        $stream = ssh2_exec($connection, 'hostname');
        stream_set_blocking($stream,true);
        return trim(stream_get_contents($stream));
    }
    
    /**
     * 修改节点
     * @param unknown $params
     * @return string
     */
    public function editNode($params){
        $nodeuuid = $params['nodeuuid'];
        $ip = $params['ip'];
        $port = $params['port'];
        $username = $params['username'];
        $password = base64_decode($params['password']);
        $rname = $params['rname'];
        $this->paramsCheck($nodeuuid, $ip, $port, $username, $password);
        $sshAvailable = $this->checkSshAvailable($ip, $port, $username, $password);
        if(!$sshAvailable){
            //此处修改,防止用户没有修改密码也可以修改其他信息
            $sshAvailable = $this->checkSshAvailable($ip, $port, $username, base64_decode($password));
            if(!$sshAvailable){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_MODIFY'], Xphp::$_lang['WEB_NODE_SSH_FAILURE'], 'warning'));
            }else{
                $password = base64_decode($password);
            }
        }
        $sql = "update bd_node set auto_upgrade_flag = ?, ip = ?, port = ?, user_name = ?, password = ?,  node_nickname = ?  
                where node_uuid = ?";
        $sqlParams = array(
            Xphp::$_config['FLAG']['SET'], $ip, $port, $username, base64_encode($password),
            $rname, $nodeuuid
        );
        $result = $this->dbQuery($sql, $sqlParams);
        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_MODIFY']);
    }
    
    /**
     * 得到节点信息(修改用)
     * @param unknown $params
     */
    public function getEditNodeInfo($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $sql = "select ip, port, user_name, password, node_nickname from bd_node where node_uuid = ? ";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $d = $data[0];
        $info = array(
            'ip' => $d['ip'],
            'port' => $d['port'],
            'username' => $d['user_name'],
            'password' => $d['password'],
            'rname' => $d['node_nickname']
        );
        return json_encode($info);
    }
    
    /**
     * 得到所有节点信息
     * @param unknown $params
     */
    public function getNodeInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'node_nickname', 'ip', 'register_time', 'auto_upgrade_flag');
        
        $sql = "select  ip, node_uuid, host_name, node_nickname, unix_timestamp(register_time) register_time, auto_upgrade_flag from bd_node ";
        $sqlCount = "select count(ip) as total from bd_node ";
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, array($start, $length));
        $count = parent::dbSelect($sqlCount, array());
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        
        foreach ($data as $d){
            $nodeDeployStatus = $this->getNodeDeployStatus($d['node_uuid']);
            $nodeAllStatus = $this->getNodeAllStatus($d['node_uuid']);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['node_uuid'] .'">',
                $id++,
                $this->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']),
                $d['ip'],
                $this->parseDate($d['register_time']),
                $nodeDeployStatus,
                $nodeAllStatus['flag'],
                array(
                    intval($d['auto_upgrade_flag']),    //远程部署
                    $nodeDeployStatus,          //部署状态
                    $this->getOffLineModuleDes($nodeAllStatus['module'])    //不在线的模块进程
                ),
            );
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到所有不在线的节点进程描述(存储有调用)
     * @param array $modules
     * @return string
     */
    public function getOffLineModuleDes($modules){
        $des = '';
        if(empty($modules)) return $des;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($modules as $m){
            $des .= $ptDes['MODULE_TYPE_DES'][$m] . ",";
        }
        $des = substr($des, 0, -1) . Xphp::$_lang['WEB_NODE_PROCESS_OFFLINE'];
        return $des;
    }
    
    /**
     * 得到节点是否支持远程部署描述
     * @param string $flag
     */
    private function getNodeUpgradeDes($flag){
        if($flag == Xphp::$_config['FLAG']['SET']){
            return Xphp::$_lang['WEB_NODE_SUPPORT'];
        }else{
            return Xphp::$_lang['WEB_NODE_NO_SUPPORT'];
        }
    }
    
    /**
     * 得到节点部署状态
     * @param string $nodeuuid
     * @return boolean
     */
    private function getNodeDeployStatus($nodeuuid){
        $sql = "select count(module_uuid) as total from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        if($data[0]['total']){
            return true;
        }else{
            return false;
        }
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
     * 得到表格展示的节点名
     */
    public function getNodeGridName($ip, $nickname, $hostname){
        if($ip == $nickname || empty($nickname)){
            return $hostname;
        }else{
            return $nickname;
        }
    }
    
    /**
     * 检测用户选择的节点是否都支持远程部署
     * @param unknown $params
     */
    public function checkAutoDeploy($params){
        $uuids = $params['uuids'];
        $this->paramsCheck($uuids);
        foreach ($uuids as $uuid){
            $uuidStr .= "'" . $uuid . "',";
        }
        $uuidStr = substr($uuidStr, 0, -1);
        $sql = "select auto_upgrade_flag from bd_node where node_uuid in ($uuidStr)";
        $data = $this->dbSelect($sql);
        $allFlag = array();
        foreach($data as $d){
            $allFlag[] = $d['auto_upgrade_flag'];
        }
        $result = !in_array(Xphp::$_config['FLAG']['UNSET'], $allFlag);
        return $this->muOpResult($result, '');
    }
    
    /**
     * 开始部署节点软件
     * @param unknown $params
     */
    public function startDeployNodeSoft($params){
        $uuids = explode(',', $params['uuids']);
        $softname = $params['softname'];
        $this->paramsCheck($uuids, $softname);
        var_dump($uuids);
        var_dump($softname);
    }
    
    /**
     * 得到即将部署节点信息
     * @param unknown $params
     */
    public function getDeployNodeInfo($params){
        $uuids = $params['uuids'];
        $this->paramsCheck($uuids);
        $uuids = explode(',', $uuids);
        foreach ($uuids as $uuid){
            $uuidStr .= "'" . $uuid . "',";
        }
        $uuidStr = substr($uuidStr, 0, -1);
        $sql = "select ip, node_nickname, host_name, node_uuid from bd_node where node_uuid in ($uuidStr)";
        $data = $this->dbSelect($sql);
        $info = array();
        foreach ($data as $d){
            $info[] = array(
                'name' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                'uuid' => $d['node_uuid']
            );
        }
        return json_encode($info);
    }
    
    /**
     * 得到节点展示名称
     * @param string $ip
     * @param string $nickname
     * @param string $hostname
     */
    public function getNodeShowName($ip, $nickname, $hostname){
        //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
        if($ip == $nickname || empty($nickname)){
            $name = $hostname . '(' . $ip . ')';
        }else{
            $name = $nickname . '(' . $ip . ')';
        }
        return $name;
    }
    
    /**
     * 得到所有节点软件列表
     * @param unknown $params
     */
    public function getSoftInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        
        $rStart = $start + 1;
        $rLength = $start * $length + $length;
        
        
        $packagePath = Xphp::$_config['NODE_SOFT'];
        $cmd = "cd ". $packagePath;
        $cmd .= ";ls -l -t --time-style=long-iso |grep tar.gz|sed -n '".
            $rStart .", " . $rLength . "p'";
        $cmd .= "| tr : \" \" | awk '{print $5, $6, $7, $8, $9}'";
        exec($cmd, $data);
        
        $countCmd = 'cd '. $packagePath;
        $countCmd .= ";ls -l -t --time-style=long-iso |grep tar.gz|wc -l";
        exec($countCmd, $count);
        
        $utils = Xphp::instance(Utils);
        $records = array("data" => array());
        $i = 1;
        foreach($data as $d){
            $package = explode(" ", $d);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . Xphp::$_config['NODE_SOFT_RE'] . $package[4] . '">',
                $i++,
                $package[4],
                $utils->calSize($package[0]),
                $package[1] . " " . $package[2] . ":" . $package[3] . ":00",
            );
        }
        
        $records["draw"] = $draw;
        $records["recordsTotal"] = intval($count[0]);
        $records["recordsFiltered"] = intval($count[0]);
        
        return  json_encode($records);
    }
    
    /**
     * 得到可用的软件包(部署下拉使用)
     * @param unknown $params
     */
    public function getSoftSelectInfo($params){
        $packagePath = Xphp::$_config['NODE_SOFT'];
        $cmd = "cd ". $packagePath;
        $cmd .= ";ls -l -t --time-style=long-iso |grep tar.gz";
        $cmd .= "| tr : \" \" | awk '{print $5, $6, $7, $8, $9}'";
        exec($cmd, $data);
        
        $utils = Xphp::instance(Utils);
        $info = array();
        foreach($data as $d){
            $package = explode(" ", $d);
            $info[] = array(
                'text' => $package[4] . " (" . $package[1] . " " . $package[2] . ":" . $package[3] . ":00" . ")",
                'name' => $package[4]
            );
        }
        return  json_encode($info);
    }
    
    /**
     * 上传节点软件
     * @param unknown $params
     */
    public function uploadNodeSoft($params){
        $uploadfile = Xphp::$_config['NODE_SOFT_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        if($files){
            //检测上传文件状态
            if(0 != $files['error']){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_UPLOAD_FILE']));
            }
            //检测文件后缀名
            $arr = explode(".", $files['name']);
            $suffixesArr = explode("|", $uploadfile['suffixes']);
            if(!in_array($arr[count($arr) - 1], $suffixesArr)){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_UPLOAD_FILE'], Xphp::$_lang['WEB_NODE_UPLOAD_TYPE'], 'error'));
            }
            //检测上传文件大小
            if($files['size'] > $uploadfile['size']){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_UPLOAD_FILE'], Xphp::$_lang['WEB_NODE_UPLOAD_SIZE'], 'error'));
            }
            //保存临时文件
            $count = file_put_contents(Xphp::$_lang['NODE_SOFT'] . $files['name'], file_get_contents($files['tmp_name']));
            if($count > 0){
                return $this->muOpResult(true, Xphp::$_lang['WEB_NODE_UPLOAD_FILE'], '', 'success', 0, Xphp::$_config['NODE_SOFT_RE']);
            }
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_UPLOAD_FILE']));
        }else {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_UPLOAD_FILE']));
        }
    }
    
    /**
     * 删除节点软件
     * @param unknown $params
     */
    public function deleteNodeSoft($params){
        $paths = $params['paths'];
        $names = ' ';
        foreach($paths as $path){
            $pathArr = explode('/', $path);
            $names .= $pathArr[count($pathArr) - 1] . " ";
        }
        $cmd = "cd " . Xphp::$_config['NODE_SOFT'] . "; rm -rf " . $names;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_NODE_DELETE_SOFT']);
    }
    
    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     * @param unknown $params
     */
    public function getAddStorageNodeSelect($params){
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
     * 得到恢复的时候所有有虚拟机备份数据的节点列表
     * @param unknown $params
     */
    public function getTimepointAllNode($params){
        $trueNode = $params['truenode'];    //是不是得到实实在在的节点,没有所有节点那一项
        $flag = Xphp::$_config['FLAG'];
        $sql = "select storage_uuid from bd_backup_timepoint 
                where module_type = ? and user_uuid = ? 
                and deleted_flag = ? and available_flag = ? and import_flag = ? 
                group by storage_uuid";
        $sqlParams = array($flag['UNSET'], Xphp::$_user['useruuid'], $flag['UNSET'], $flag['SET'], $flag['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $uuidArr = array();
        foreach ($data as $d){
            $uuidArr[] = $d['storage_uuid'];
        }
        $info = array();
        if(!$trueNode){
            $info = array(
                array(
                    'value' => 0,
                    'text' => Xphp::$_lang['WEB_NODE_ALL_NODE'],
                )
            );
        }
        $uuidStr = implode("','", $uuidArr);
        $sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname from bd_node bn, bd_storage_resource bsr 
                where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid in ('$uuidStr') group by bn.node_uuid";
        $data = $this->dbSelect($sql);
        foreach ($data as $d){
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if($nodeStatus['flag']){
                //如果节点状态正常
                $info[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name'])
                );
            }
        }
        return json_encode($info);
    }
    
    /**
     * 得到恢复的时候所有有文件备份数据的节点列表
     * @param unknown $params
     */
    public function getFSTimepointAllNode($params){
    	$trueNode = $params['truenode'];    //是不是得到实实在在的节点,没有所有节点那一项
    	$flag = Xphp::$_config['FLAG'];
    	$sql = "select storage_uuid from bd_backup_timepoint
                where module_type = ? and user_uuid = ?
                and deleted_flag = ? and available_flag = ? and import_flag = ?
                group by storage_uuid";
    	$sqlParams = array(Xphp::$_config['MODULE_TYPE']['FS'], Xphp::$_user['useruuid'], $flag['UNSET'], $flag['SET'], $flag['UNSET']);
    	$data = $this->dbSelect($sql, $sqlParams);
    	$uuidArr = array();
    	foreach ($data as $d){
    		$uuidArr[] = $d['storage_uuid'];
    	}
    	$info = array();
    	if(!$trueNode){
    		$info = array(
    				array(
    						'value' => 0,
    						'text' => Xphp::$_lang['WEB_NODE_ALL_NODE'],
    				)
    		);
    	}
    	$uuidStr = implode("','", $uuidArr);
    	$sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname from bd_node bn, bd_storage_resource bsr
    	where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid in ('$uuidStr') group by bn.node_uuid";
    	$data = $this->dbSelect($sql);
    	foreach ($data as $d){
    	$nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
    	if($nodeStatus['flag']){
    		//如果节点状态正常
    	$info[] = array(
    	'uuid' => $d['node_uuid'],
    		'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name'])
    	);
    	}
    	}
    	return json_encode($info);
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_NO_BACKUP_NODE'], Xphp::$_lang['WEB_NODE_CHECK_NODE_STATUS']));
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_NO_FIND_STORAGE'],Xphp::$_lang['WEB_NODE_NO_FIND_STORAGE_ALL']));
        }
        return $availableNode;
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
     * 根据任务UUID得到所在节点UUID
     * @param string $taskUUID
     * @param boolean $flag 是否一定要获取节点uuid，默认是，如果不是的话，节点不在线会返回主节点uuid
     * @return string
     */
    public function getNodeUUIDWithTaskUUID($taskUUID, $flag = true){
        $sql = "select node_uuid from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $nodeuuid = $data[0]['node_uuid'];
        
        $nodeAllStatus = $this->getNodeAllStatus($nodeuuid);
        
        if($nodeAllStatus['flag'] == false && false == $flag){
            //节点不在线就需要获取主节点uuid
            $nodeuuid = $this->getLocalNodeUUID();
        }
        
        return $nodeuuid;
    }
    
    /**
     * 得到VCENTER操作的节点UUID(本地节点UUID)
     */
    public function getvCenterUUID(){
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }
    
    /**
     * 得到本地节点UUID
     */
    public function getLocalNodeUUID(){
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }
    
    /**
     * 得到节点IP地址列表
     * @param unknown $params
     */
    public function getNodeIPAddr($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $msg = array();
        $opName = "BD_SYSTEM_OP_GET_IP_LIST";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $list = array();
        if($mbResult['result']){
            $list = $mbResult['msg']['ipv4_list'];
        }
        return json_encode($list);
    }
    
    /**
     * 得到某个备份节点的正在运行的任务列表
     * @param unknown $params
     */
    public function getNodeRunningTaskList($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $sql = "select task_uuid, task_name from bd_task where task_status = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['RUNNING'], $nodeuuid));
        $tasks = array();
        foreach ($data as $d){
            $tasks[] = array(
                'taskuuid' => $d['task_uuid'],
                'taskname' => $d['task_name']
            );
        }
        return json_encode($tasks);
    }
    
    /**
     * 得到所有已添加备份节点
     * @param unknown $params
     */
    public function getNodeList($params){
    	$sql = "select ip, host_name, node_nickname, node_uuid from bd_node";
    	$data = $this->dbSelect($sql, array());
    	$info = array();
    	foreach ($data as $d){
    		$info[] = array(
    			"node_name" => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
    			"node_uuid" => $d['node_uuid'],
    			"ip" => $d['ip']
    		);
    	} 
    	return json_encode($info);
    }
    
    /**
     * 根据节点uuid获取节点名称
     * @param string $nodeuuid
     * @return string
     */
    public function getNodeName($nodeuuid){
    	$sql ="select ip, host_name, node_nickname from bd_node where node_uuid = ? ";
    	$data =$this->dbSelect($sql, array($nodeuuid));
    	$nodename = $this->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']);
    	return $nodename;
    }
}