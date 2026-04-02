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
     * @var object|OpcodeHandler|OPHandler|Socket|Utils
     */
    private $opcodeHandler;

    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('NodeOpcode');
    }
    
    /**
     * 添加节点
     * @param unknown $params
     */
    public function addNode($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_node_manager_add");
        $opcodeName = 'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $operate = Xphp::$_lang['WEB_BD_NOT_MASTER_NODE_ADD'];

        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($params));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            //添加超时重定义信息返回
            if($mbResult['errorCode'] == 51505){
                return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_NODE_ADD_TIMEOUT_TIPS'], 'info');
            }
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 获取主节点IP信息
     * @param $params
     * @return void
     */
    public function getMasterNode($params){
        $sql = "select node_type, ip, host_name, node_nickname from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['NODETYPE']['MASTER']));
        $nodeIp = array();
        foreach ($data as $d){
            $nodeIp[] = array(
                'node_ip' => $d['ip']
            );
        }
        return json_encode($nodeIp);
    }

    /**
     * 获取主节点uuid
     * @return string|mixed
     */
    public function getMasterNodeUuid()
    {
        $sql = "SELECT node_uuid FROM bd_node WHERE node_type = ? ";
        $data = $this->dbSelect($sql,[Xphp::$_config['NODETYPE']['MASTER']]);
        return $data[0]['node_uuid'];
    }

    /**
     * 删除节点
     * @param unknown $params
     */
    public function deleteNode($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_node_manager_delete");

        $nodeuuid = $params['uuids'][0];
        $remote_port = $params['remote_port'];
        $operation_type = $params['operation_type'];
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
        $msg = array(
            'operation_type' => $operation_type,
            'remote_port' => $remote_port,
            'remote_ip' => $ip,
        );
        $opcodeName = 'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeserveruuid = $nodeHandler->getLocalNodeUUID();

        $operate = Xphp::$_lang['WEB_BD_NOT_MASTER_NODE_DELETE'];
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeserveruuid, json_encode($msg),false,true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改节点名字
     * @param unknown $params
     */
    public function editNodeHostName($params){
        $nodename = trim($params['nodename']);
        //检查节点名字是否为空
        $this->checkNodenameEmpty($nodename);
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

        $sql = "select host_name, node_nickname, node_type from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $nodename = $data[0]['node_nickname'];
        $nodetype = $data[0]['node_type'];
        if(empty($nodename)){
            $nodename = $data[0]['host_name'];
        }
        $info = array(
            'nodeuuid' => $nodeuuid,
            'nodename' => $nodename,
            'nodetype' =>  $nodetype,
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
            //权限检查
            $roleHandler = Xphp::instance('RoleHandler');
            $roleHandler->pOperationPermissionCheckExit("p_node_manager_add");
            $opcodeName = 'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER';
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $operate = Xphp::$_lang['WEB_BD_NOT_MASTER_NODE_EDIT'];
            $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($params));
            $result = $mbResult['result'];
            $msg = $mbResult['msg'];
            if($result){
                return $this->muOpResult($result, $operate, $msg);
            }else{
                //添加超时重定义信息返回
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }

        }

//    public function editNode($params){
//        $nodeuuid = $params['nodeuuid'];
//        $ip = $params['ip'];
//        $port = $params['port'];
//        $username = $params['username'];
//        $password = base64_decode($params['password']);
//        $rname = $params['rname'];
//        $this->paramsCheck($nodeuuid, $ip, $port, $username, $password);
//        $sshAvailable = $this->checkSshAvailable($ip, $port, $username, $password);
//        if(!$sshAvailable){
//            //此处修改,防止用户没有修改密码也可以修改其他信息
//            $sshAvailable = $this->checkSshAvailable($ip, $port, $username, base64_decode($password));
//            if(!$sshAvailable){
//                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_MODIFY'], Xphp::$_lang['WEB_NODE_SSH_FAILURE'], 'warning'));
//            }else{
//                $password = base64_decode($password);
//            }
//        }
//        $sql = "update bd_node set auto_upgrade_flag = ?, ip = ?, port = ?, user_name = ?, password = ?,  node_nickname = ?
//                where node_uuid = ?";
//        $sqlParams = array(
//            Xphp::$_config['FLAG']['SET'], $ip, $port, $username, base64_encode($password),
//            $rname, $nodeuuid
//        );
//        $result = $this->dbQuery($sql, $sqlParams);
//        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_MODIFY']);
//    }
//
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
        $sortArr = array('', '', 'node_nickname', 'ip', 'detail', 'register_time', 'auto_upgrade_flag');
        
        $sql = "select node_type, detail, ip, node_uuid, host_name, node_nickname,
                        unix_timestamp(register_time) register_time, auto_upgrade_flag,status from bd_node ";
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
            $info = json_decode($d['detail'], true);
            if(!empty($info)){
                $version = $info['version']['major_version'] . ".".$info['version']['minor_version']. ".".$info['version']['build_number'];
            }else{
                $version = Xphp::$_config['NULLSPACE'];
            }
            $nodeName = $this->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']);
            if(intval($d['node_type']) == Xphp::$_config['FLAG']['SET']){
                $nodeName .= "(".Xphp::$_lang['UI_PALTFORM_MASTER_NODE'].")";
            }else{
                $nodeName .= "(".Xphp::$_lang['UI_PALTFORM_CHILD_NODE'].")";
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['node_uuid'] .'">',
                $id++,
                $nodeName,
                $d['ip'],
                $version,
                $this->parseDate($d['register_time']),
                $nodeDeployStatus,
                $nodeAllStatus['flag'],
                array(
                    intval($d['auto_upgrade_flag']),    //远程部署
                    $nodeDeployStatus,          //部署状态
                    $this->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
                    $d['node_uuid']
                ),
                $d['status']
            );
        }
        
        $records["draw"] = $params['draw'];
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
    public function getNodeDeployStatus($nodeuuid){
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
        if(empty($ip) && empty($nickname) && empty($hostname)){
            return "--";
        }
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

        $utils = Xphp::instance('Utils');
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
        
        $utils = Xphp::instance('Utils');
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
    public function getAddStorageNodeSelect(){
        $sql = "select be.use_vt_flag, bn.ip, bn.node_uuid, bn.host_name,bn.node_nickname, bn.node_type from bd_node bn left join bd_emd be on be.node_uuid = bn.node_uuid  order by node_type";
        $data = $this->dbSelect($sql, array());
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $list = array();
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['NODE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) continue;
            
            if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
            $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
                if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
            }
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if($nodeStatus['flag']){
                //如果节点状态正常
                $list[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                    'type' => intval($d['node_type']),
                    'vt_flag' => intval($d['use_vt_flag'])
                );
            }
        }
        return json_encode($list);
    }
    
    /**
     * 得到恢复的时候所有有虚拟机|数据库备份数据的节点列表
     * @param unknown $params
     */
    public function getTimepointAllNode($params){
        $moduleType = intval($params['moduleType']);
        $dataFlag = $params['dataFlag'];    //备份数据标志
        $trueNode = $params['truenode'];    //是不是得到实实在在的节点,没有所有节点那一项
        $flag = Xphp::$_config['FLAG'];
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $sql = "select bsr.node_uuid, bbt.real_node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr
                where bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.available_flag = ?  
                and bbt.import_flag = ? and bbt.data_local_flag = ? ";
        //恢复增加副本点所在获取
        if(!$dataFlag){
            switch ($moduleType){
                case Xphp::$_config['MODULE_TYPE']['VM']:
                    $moduleType = Xphp::$_config['MODULE_TYPE']['VM'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
                    $sql .= " and bbt.task_type in (".Xphp::$_config['TASKTYPE']['BACKUP'].",".Xphp::$_config['TASKTYPE']['BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'].",".Xphp::$_config['TASKTYPE']['ARCHIVE'].",".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") ";
                    break;
                case Xphp::$_config['MODULE_TYPE']['FS']:
                    $moduleType = Xphp::$_config['MODULE_TYPE']['FS'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
                    $sql .= " and bbt.task_type in (".Xphp::$_config['TASKTYPE']['BACKUP'].",".Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY_FETCH'].") ";
                    break;
                case Xphp::$_config['MODULE_TYPE']['NAS']:
                    $moduleType = Xphp::$_config['MODULE_TYPE']['NAS'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
                    $sql .= " and bbt.task_type in (".Xphp::$_config['TASKTYPE']['BACKUP'].",".Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY_FETCH'].") ";
                    break;
                case Xphp::$_config['MODULE_TYPE']['DB']:
                    $moduleType = Xphp::$_config['MODULE_TYPE']['DB'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
                    $sql .= " and bbt.task_type in (".Xphp::$_config['TASKTYPE']['DB_BACKUP'].",".Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY_FETCH'].") ";
                    break;
                case Xphp::$_config['MODULE_TYPE']['OS']:
                    $moduleType = Xphp::$_config['MODULE_TYPE']['OS'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'];
                    $sql .= " and bbt.task_type in (".Xphp::$_config['TASKTYPE']['OS_BACKUP'].",".Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['OS_BACKUP_ARCHIVE'].") ";
                    break;
            }
        }
        // 查看权限
        if (Xphp::$_config['MODULE_TYPE']['VM'] == $moduleType) {
            if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
                $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }
        }

        $sql .= " and bbt.module_type in (".$moduleType.") ";
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $uuidArr = array();
        //获取对应节点
        foreach ($data as $d){
            $uuidArr[] = !empty($d['real_node_uuid']) ? $d['real_node_uuid'] : $d['node_uuid'];
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
        $sql = "select ip, node_uuid, host_name, node_nickname, node_type from bd_node 
                where node_uuid in ('$uuidStr') group by node_uuid";
        $data = $this->dbSelect($sql);
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetTenantAllResource($_SESSION['tenantuuid'], Xphp::$_config['RESOURCE_TYPE']['NODE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) continue;
            
            if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
            $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
                if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
            }
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if($nodeStatus['flag']){
                //如果节点状态正常
                $info[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                );
            }
        }
        return json_encode($info);
    }
    /**
     * 得到恢复的时候所有有VOL_CDP备份数据的节点列表
     * @param unknown $params
     */
    public function getVolCdpTimepointAllNode($params){
        $moduleType = intval($params['moduleType']);
        $flag = Xphp::$_config['FLAG'];
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $sql = "select storage_uuid from bd_backup_timepoint
                where module_type = ? and user_uuid = ? and deleted_flag = ?  and import_flag = ? and data_local_flag = ?
                group by storage_uuid";
        $sqlParams = array($moduleType, Xphp::$_user['useruuid'], $flag['UNSET'], $flag['UNSET'], $flag['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $uuidArr = array();
        foreach ($data as $d){
            $uuidArr[] = $d['storage_uuid'];
        }
        $info = array();
        $uuidStr = implode("','", $uuidArr);
        $sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname, bn.node_type from bd_node bn, bd_storage_resource bsr
        where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid in ('$uuidStr') group by bn.node_uuid";
        $data = $this->dbSelect($sql);
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetTenantAllResource($_SESSION['tenantuuid'], Xphp::$_config['RESOURCE_TYPE']['NODE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) continue;
            
            if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
                $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
                    if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
            }
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
     * 得到恢复的时候所有有数据库备份数据的节点列表
     * @param unknown $params
     */
    public function getDBTimepointAllNode($params){
        $trueNode = $params['truenode'];    //是不是得到实实在在的节点,没有所有节点那一项
        $flag = Xphp::$_config['FLAG'];
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $sql = "select storage_uuid from bd_backup_timepoint
                where module_type = ? and user_uuid = ?
                and deleted_flag = ? and available_flag = ? and import_flag = ?
                group by storage_uuid";
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['DB'], Xphp::$_user['useruuid'], $flag['UNSET'], $flag['SET'], $flag['UNSET']);
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
        $sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname, bn.node_type from bd_node bn, bd_storage_resource bsr
        where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid in ('$uuidStr') group by bn.node_uuid";
        $data = $this->dbSelect($sql);
        foreach ($data as $d){
            if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
                $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
                    if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
            }
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
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
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
    	$sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname, bn.node_type from bd_node bn, bd_storage_resource bsr
    	where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid in ('$uuidStr') group by bn.node_uuid";
    	$data = $this->dbSelect($sql);
    	foreach ($data as $d){
        if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
        $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
            if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
        }
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
    public function getNASTimepointAllNode($params){
    	$trueNode = $params['truenode'];    //是不是得到实实在在的节点,没有所有节点那一项
        $flag = Xphp::$_config['FLAG'];
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
    	$sql = "select storage_uuid from bd_backup_timepoint
                where module_type = ? and user_uuid = ?
                and deleted_flag = ? and available_flag = ? and import_flag = ?
                group by storage_uuid";
    	$sqlParams = array(Xphp::$_config['MODULE_TYPE']['NAS'], Xphp::$_user['useruuid'], $flag['UNSET'], $flag['SET'], $flag['UNSET']);
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
    	$sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname, bn.node_type from bd_node bn, bd_storage_resource bsr
    	where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid in ('$uuidStr') group by bn.node_uuid";
    	$data = $this->dbSelect($sql);
    	foreach ($data as $d){
        if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
        $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
            if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
        }
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
     * 从web_ng根据时间点UUID得到所在节点UUID
     * @param $timepointUUID
     */
    private function getNodeUUIDWithTimepointUUIDByWebNg($timepointUUID)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://{$_SERVER['SERVER_ADDR']}/api/v1/nodes/timepoints/$timepointUUID/node",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'x-api-version: 1.0-rev0',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $responseJson = json_decode($response, true);
        return $responseJson['data']['node_uuid'];
    }

    /**
     * 根据时间点UUID得到所在节点UUID
     * @param string $timepointUUID
     * @return string
     */
    public function getNodeUUIDWithTimepointUUID($timepointUUID){
        return $this->getNodeUUIDWithTimepointUUIDByWebNg($timepointUUID);
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
     * @param array $params
     */
    public function getNodeIPAddr($params){
        $list = $this->getNodeIPAddrMap($params);
        return json_encode(array_merge([], $list['ipv4_list'], $list['ipv6_list']));
    }

    /**
     * 得到节点IP地址列表映射
     * @param array $params
     */
    public function getNodeIPAddrMap($params): array
    {
        $nodeUuid = $params['nodeuuid'];
        $this->paramsCheck($nodeUuid);
        $msg = array();
        $opName = "BD_SYSTEM_OP_GET_IP_LIST";
        $mbResult = $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true);
        $list = [
            'ipv4_list' => [],
            'ipv6_list' => [],
        ];
        if($mbResult['result']){
            $list['ipv4_list'] = $mbResult['msg']['ipv4_list'] ?: [];
            $list['ipv6_list'] = $mbResult['msg']['ipv6_list'] ?: [];
        }
        return $list;
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

    /**
     * 
     */
    public function getAppliances($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'ba.nickname', 'ba.ip', 'ba.port', 'ba.online_flag', 'ba.register_time', 'ba.user_uuid');
        
        
        //除了管理员admin以外的用户需要检查分配或者创建的传输代理
        if (Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager']){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserResourceAppliance(Xphp::$_user['useruuid'], "id", "desc", $limit = 0, "all");
            if(!empty($resourceInfo['data'])){
                foreach ($resourceInfo['data'] as $r){
                    $resourceList[] = $r['appliance_uuid'];
                }
            }
        }
        
        $sql = "select bu.user_name, ba.appliance_uuid, ba.node_uuid, ba.nickname, ba.ip, ba.port, ba.online_flag,
        unix_timestamp(ba.register_time) register_time, ba.progress_server_listen_port, ba.progress_server_start_port, progress_server_end_port,
        ba.cdp_client_listen_port, ba.cdp_client_log_listen_port, ba.log_server_listen_port from bd_appliance ba, bd_user bu where ba.user_uuid = bu.user_uuid and ba.node_uuid = '' ";
        $sqlCount = "select count(appliance_uuid) as total from bd_appliance where node_uuid = '' ";
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, array($start, $length));
        $count = parent::dbSelect($sqlCount, array());
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $total = intval($count[0]['total']);
        foreach ($data as $d){
            //除了管理员admin以外的用户需要检查分配或者创建的传输代理
            if(Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager'] && !in_array($d['appliance_uuid'], $resourceList)){
                $total--;
                continue;
            } 
            $processPort = array(
                'progress_server_listen_port' => $d['progress_server_listen_port'],
                'progress_server_start_port' => $d['progress_server_start_port'],
                'progress_server_end_port' => $d['progress_server_end_port'],
                'cdp_client_listen_port' => $d['cdp_client_listen_port'],
                'cdp_client_log_listen_port' => $d['cdp_client_log_listen_port'],
                'log_server_listen_port' => $d['log_server_listen_port'],
            );
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['appliance_uuid'] .'">',
                $id++,
                $d['nickname'],
                $d['ip'],
                $d['port'],
                $this->getApplianceStatus(intval($d['online_flag'])),
                $this->parseDate($d['register_time']),
                $d['user_name'],
                intval($d['online_flag']),
                $processPort
            );
        }
        
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;
        
        return  json_encode($records);
    }

    /**
     * 添加Appliance
     */
    public function addAppliance($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_appliance_manager_add");
        
        $ip = $params['ip'];
        //检查是否同一IP一直添加
        $this->checkIpExist($ip);
        $port = $params['port'];
        $nickname = $params['nickname'];
        $opName = 'NODE_APPLIANCE_OP_ADD';
        $msg = array(
            'ip' => $ip,
            'port' => $port,
            'nickname' => $nickname
        );
        $nodeuuid = $this->getLocalNodeUUID();
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $descriptionParam = array($nickname, $ip, $port);
        if($result){
            $applianceUUID = $this->pGetApplianceUuidByIp($ip);
            if($applianceUUID != ""){
                $usersHandler = Xphp::instance('UsersHandler');
                $resourceList = array(
                    'resourceuuid' => $applianceUUID,
                    'vmuuid' => "",
                    'vcenteruuid' => "",
                    'resourceType' => Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']
                );
                //添加资源与用户关联
                $usersHandler->pAddUserResource(Xphp::$_user['useruuid'], $resourceList);
            }
            
            $this->writeLog('SYSTEM_LOG_DESC_KEY_PROXY_ADD', $descriptionParam);
            //写入系统日志
            $this->systemLog('SYSTEM_LOG_DESC_KEY_PROXY_ADD', $descriptionParam);
            return $this->muOpResult($result, $operate, $mbResult['msg']);
        }else{
            $this->writeLog('SYSTEM_LOG_DESC_KEY_PROXY_ADD', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除选中的Appliance
     */
    public function deleteAppliance($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_appliance_manager_delete");
        $applianceList = $params['list'];
        $applianceArray = array();
        foreach ($applianceList as $appliance){
            $applianceArray[] = $appliance;
        }
        $sql = "select nickname, ip, port from bd_appliance where appliance_uuid = ? ";
        $data = $this->dbSelect($sql, array($applianceList[0]));
        $descriptionParam = array($data[0]['nickname'], $data[0]['ip'], $data[0]['port']);
        $opName = 'NODE_APPLIANCE_OP_DEL';
        $msg = array(
            'appliance_uuid_list' => $applianceArray
        );
        $nodeuuid = $this->getLocalNodeUUID();
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        
        if($result){
            $usersHandler = Xphp::instance('UsersHandler');
            //清除选中虚拟机备份代理与用户关联
            $usersHandler->pDeleteUserResource(Xphp::$_user['useruuid'], $applianceArray);
            
            $this->writeLog('SYSTEM_LOG_DESC_KEY_PROXY_DELETE', $descriptionParam);
            //写入系统日志
            $this->systemLog('SYSTEM_LOG_DESC_KEY_PROXY_DELETE', $descriptionParam);
            return $this->muOpResult($result, $operate, $mbResult['msg']);
        }else{
            $this->writeLog('SYSTEM_LOG_DESC_KEY_PROXY_DELETE', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 修改Appliance
     */
    public function editAppliance($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_appliance_manager_edit");
        $uuid = $params['uuid'];
        $ip = $params['ip'];
        $port = $params['port'];
        $nickname = $params['nickname'];
        $progress_server_listen_port = $params['progress_server_listen_port'];
        $progress_server_start_port = $params['progress_server_start_port'];
        $progress_server_end_port = $params['progress_server_end_port'];
        $cdp_client_listen_port = $params['cdp_client_listen_port'];
        $cdp_client_log_listen_port = $params['cdp_client_log_listen_port'];
        $log_server_listen_port = $params['log_server_listen_port'];
        $settings = $params['dnslist'];
        $msg = array(
            "appliance_uuid" => $uuid,
            "ip" => $ip,
            "port" => $port,
            "progress_server_listen_port" => $progress_server_listen_port,
            "progress_server_start_port" => $progress_server_start_port,
            "progress_server_end_port" => $progress_server_end_port,
            "cdp_client_listen_port" => $cdp_client_listen_port,
            "cdp_client_log_listen_port" => $cdp_client_log_listen_port,
            "log_server_listen_port" => $log_server_listen_port,
            "nickname" => $nickname,
            "domain_config" => $settings
        );
        $opName = "NODE_APPLIANCE_OP_MODIFY";
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $descriptionParam = array($nickname, $ip, $port);
        if($result){
            $this->writeLog('SYSTEM_LOG_DESC_KEY_PROXY_EDIT', $descriptionParam);
            //写入系统日志
            $this->systemLog('SYSTEM_LOG_DESC_KEY_PROXY_EDIT', $descriptionParam);
            return $this->muOpResult($result, $operate, $mbResult['msg']);
        }else{
            
            $this->writeLog('SYSTEM_LOG_DESC_KEY_PROXY_EDIT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }

    }

    /**
     * 获取修改Appliance的信息
     */
    public function getModifyApplianceInfo($params){
        $uuid = $params['uuid'];
        $sql = "select ip, port, nickname, progress_server_listen_port, progress_server_start_port, progress_server_end_port, cdp_client_listen_port, cdp_client_log_listen_port, log_server_listen_port, system_info from bd_appliance where appliance_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $systemInfo = json_decode($data[0]['system_info'], true);
        $info = array(
            'ip' => $data[0]['ip'],
            'port' => $data[0]['port'],
            'nickname' => $data[0]['nickname'],
            'progress_server_listen_port' => $data[0]['progress_server_listen_port'],
            'progress_server_start_port' => $data[0]['progress_server_start_port'],
            'progress_server_end_port' => $data[0]['progress_server_end_port'],
            'cdp_client_listen_port' => $data[0]['cdp_client_listen_port'],
            'cdp_client_log_listen_port' => $data[0]['cdp_client_log_listen_port'],
            'log_server_listen_port' => $data[0]['log_server_listen_port'],
            'settings' => $systemInfo['domain_config']
        );
        return json_encode($info);
    }

    /**
     * 获取Appliance状态
     */
    public function getApplianceStatus($onlineFlag){
        $statusDes = '';
        if(Xphp::$_config['FLAG']['SET'] == $onlineFlag){
            $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
        }else{
            $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
        }

        return $statusDes;
    }

    /**
     * 获取虚拟机传输代理
     */
    public function getApplianceSelect($params){
        
//        $sql = "select appliance_uuid, nickname, ip, port from bd_appliance where node_uuid = '' and online_flag = ? ";
//        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
//        $info = array();
//        $resourceList = array();
//        //除了管理员admin以外的用户需要检查分配或者创建的传输代理
//        if (Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager']){
//            $resourceHandler = Xphp::instance('ResourceHandler');
//            $resourceInfo = $resourceHandler->pGetUserResourceAppliance(Xphp::$_user['useruuid'], "id", "desc", $limit = 0, "all");
//            if(!empty($resourceInfo['data'])){
//                foreach ($resourceInfo['data'] as $r){
//                    $resourceList[] = $r['appliance_uuid'];
//                }
//            }
//        }
//        foreach($data as $d){
//            //除了管理员admin以外的用户需要检查分配或者创建的传输代理
//            if(Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager'] && !in_array($d['appliance_uuid'], $resourceList)) continue;
//            
//            $info[] = array(
//                'text' => $d['nickname'].'('.$d['ip']. ')',
//                'value' => $d['appliance_uuid']
//            );
//        }
//    
//        return json_encode($info);

        // 新版本从bd_agent获取
        $sql = "select agent_uuid, agent_name, ip from bd_agent where agent_type = 4 and node_uuid = '' and online_flag = ? and os_type != ? and agent_os_type != ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET'], 'Windows', Xphp::$_config['AGENT_OS_TYPE']['WINDOWS']));
        $info = array();
        $resourceList = array();
        //除了管理员admin以外的用户需要检查分配或者创建的传输代理
        if (Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager']) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserResourceAgent(Xphp::$_user['useruuid'], "id", "desc", $limit = 0, "all");
            if (!empty($resourceInfo['data'])) {
                foreach ($resourceInfo['data'] as $r) {
                    $resourceList[] = $r['agent_uuid'];
                }
            }
        }
        foreach ($data as $d) {
            //除了管理员admin以外的用户需要检查分配或者创建的传输代理
            if (Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager'] && !in_array($d['agent_uuid'], $resourceList)) continue;

            $info[] = array(
                'text' => $d['agent_name'] . '(' . $d['ip'] . ')',
                'value' => $d['agent_uuid']
            );
        }

        return json_encode($info);
    }
    
    
    /**
     * 获取节点昵称和IP
     */
    public function getNodeNameWithUUID($nodeuuid){
        $sql = "select node_type, ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $name = empty($data[0]['node_nickname']) ? $data[0]['host_name'] : $data[0]['node_nickname'];
        $ip = $data[0]['ip'];
        
        $info = array(
            'name' => $name,
            'ip' => $ip
        );
        
        return $info;
        
    }
    
    
    /**
     * 公共方法
     * 通过虚拟机备份代理ip获取代理唯一标识
     * @param string $ip
     * @author luokai@vinchin.com
     * @return string 返回虚拟机备份代理唯一标识
     */
    public function pGetApplianceUuidByIp($ip){
        $sql = "select appliance_uuid from bd_appliance where ip = ?";
        $data = $this->dbSelect($sql, array($ip));
        $uuid = "";
        if(!empty($data)){
            $uuid = $data[0]['appliance_uuid'];
        }
        
        return $uuid;
    }
    
    /**
     * 公共方法
     * 通过节点唯一标识获取节点描述
     * @param string $nodeuuid 节点唯一标识
     * @author luokai@vinchin.com
     * @return string 返回节点名称
     */
    public function pGetStorageNodeDes($nodeuuid){
        $sql = "select ip, node_nickname, host_name from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $nodeDes = "";
        if(!empty($data)){
            $nodeDes =  $this->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']) . "(" . $data[0]['ip'] . ")";
        }
        
        return $nodeDes;
        
    }
    
    /**
     * 检查备份代理是否已经添加过
     * @param string $ip
     */
    public function checkIpExist($ip){
        $sql = "select appliance_uuid from bd_appliance where ip = ?";
        $data  = $this->dbSelect($sql, array($ip));
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_APPLIANCE_OP_ADD'], Xphp::$_lang['WEB_NODE_PLEASE_ADD_REPEAT_PROXY'], "warning"));
        }
        return true;
    }
    
    /**
     * 获取备份系统节点IP
     */
    public function getBackupServerIp($params){
        $nodeuuid = $params['nodeuuid'];
        $taskuuid = $params['taskuuid'];
        if(!empty($taskuuid)){
            $sql = "select node_uuid from bd_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            if(!empty($data)){
                $nodeuuid = $data[0]['node_uuid'];
            }
        }
        $sql = "select ip from bd_node_network where node_uuid = ? order by network_order";
        $data = (array)$this->dbSelect($sql, array($nodeuuid));

        return json_encode(array_column($data, 'ip'));
    }
    
    /**
     * 修改节点名称为空提示
     * @param unknown $name
     */
    private function checkNodenameEmpty($name){
        if(empty($name)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_EDIT_INFO'], Xphp::$_lang['WEB_NODE_NAME_EMPTY']));
        }
    }
    
    /**
     * 得到所有节点网络信息
     * @param unknown $params
     */
    public function getNodeNetworkInfo($params){
        $nodeuuid = $params['nodeuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'ip', 'alias_name', "type", 'port', 'network_order');

        $sql = "select network_uuid, node_uuid, alias_name, ip, port, type, network_order from bd_node_network where ip != '' and node_uuid = ? order by network_order asc ";

        $sqlCount = "select count(id) as total from bd_node_network where ip != '' and node_uuid = ? ";
        $sqlParams = array($nodeuuid);
        $sqlCountParams = array($nodeuuid);

        $sql .= "   limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['network_uuid'] .'">',
                $d['ip'],
                $d['alias_name'],
                intval($d['type']) == 1 ? Xphp::$_lang['WEB_NODE_LOCAL_DEFAULT'] : Xphp::$_lang['WEB_NODE_MAP'],
                $d['port'],
                $d['network_order'],
                array(
                    "nodeuuid" => $nodeuuid,
                    "networkuuid" => $d['network_uuid'],
                    "ip" => $d['ip'],
                    "nickname" => $d['alias_name'],
                    "port" => $d['port'],
                    "type" => intval($d['type'])
                ),
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 添加节点网络
     * @param unknown $params
     * @return string
     */
    public function addNodeNetwork($params){
        $msg = array();
        $nodeuuid = $params['nodeuuid'];
        //定义操作名
        $opcodeName = 'NODE_NETWORK_OP_ADD';
        //组合消息
        $msg = array(
            'node_uuid' => $nodeuuid,
            'ip' => $params['ip'],    
            'port' => $params['port'],   
            'alias_name' => $params['nickname'],
            'order' => 0,
            
        );
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    /**
     * 修改节点网络
     * @param unknown $params
     * @return string
     */
    public function editNodeNetwork($params){
        $msg = array();
        $nodeuuid = $params['nodeuuid'];
        //定义操作名
        $opcodeName = 'NODE_NETWORK_OP_MODIFY';
        //组合消息
        $msg = array(
            'network_uuid' => $params['networkuuid'],
            'ip' => $params['ip'],
            'port' => $params['port'],
            'alias_name' => $params['nickname'],
            
        );
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    /**
     * 节点网络排序
     * @param unknown $params
     * @return string
     */
    public function orderNodeNetwork($params){
        $msg = array();
        $nodeuuid = $params['nodeuuid'];
        $orderList = $this->groupNetworkList($params['orderList']); //组装排序网络列表
        //定义操作名
        $opcodeName = 'NODE_NETWORK_OP_ADJUST_ORDER';
        //组合消息
        $msg = array(
            'network_order_list' => $orderList,
            
        );
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    /**
     * 删除节点网络
     * @param unknown $params
     * @return string
     */
    public function deleteNodeNetwork($params){
        $msg = array();
        $nodeuuid = $params['nodeuuid'];
        $networkList = $params['networkList'];
        //检查是否选中了本地默认网络，禁止删除
        //定义操作名
        $opcodeName = 'NODE_NETWORK_OP_DEL';
        //组合消息
        $msg = array(
            'network_uuid_list' => $networkList
            
        );
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    
    /**
     * 获取节点网络列表
     * @param unknown $params
     * @return string
     */
    public function getNodeNetworkList($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $sql = "select ip, port, alias_name, network_order, network_uuid from bd_node_network where ip != '' and node_uuid = ?  order by network_order asc";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $list = array();
        foreach ($data as $d){
            $list[] = array(
                'ip' => $d['ip'],
                'port' => $d['port'],
                'alias_name' => $d['alias_name'],
                'network_order' => $d['network_order'],
                'network_uuid' => $d['network_uuid'],
            );
            
        }
        
        return json_encode($list);
    }
    
    /**
     * 组装排序的节点网络列表
     * @param array $list
     * @return number[][]|unknown[][]
     */
    public function groupNetworkList($list){
        $info = array();
        foreach ($list as $key => $d){
            $info[] = array(
                'network_uuid' => $d,
                'order' => $key + 1
            );
        }
        
        return $info;
    }
    
    
    /**
     * 切换节点后获取默认网络
     * @param string $taskuuid
     * @param string $nodeuuid
     * @param string $network
     * @return string
     */
    public function pGetDiffNodeNetwork($taskuuid, $nodeuuid, $network){
        $sql = "select node_uuid from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        //检查是否节点不同
        if($data[0]['node_uuid'] != $nodeuuid){
            //继续获取新加节点的网络
            $sql = "select network_uuid from bd_node_network where node_uuid =? order by network_order asc";
            $data = $this->dbSelect($sql, array($nodeuuid));
            foreach ($data as $d){
                
                if($network == $d['network_uuid']){
                    //如果选择了当前节点网络，直接返回
                    return $network;
                }
            }
            //返回选择节点默认第一顺序网络
            return $data[0]['network_uuid'];
        }else{
            //和原来是同一节点，返回选择的节点网络
            return $network;
        }
    }
    
    
    //获取节点分配列表
    public function getNodeAllocationList($params){
        $vcenteruuid = $params['vcenteruuid'];
        
        //获取备份节点列表
        $sql = "select ip, node_nickname, host_name, node_uuid from bd_node";
        $data = $this->dbSelect($sql);
        
        //获取已绑定的节点
        $sql1 = "select node_uuid from mt_platform_node where platform_uuid = ? and type = ?";
        $data1 = $this->dbSelect($sql1, array($vcenteruuid, 1));
        $nodeList = array();
        $allocationList = array();
        //备份节点列表数组
        foreach ($data as $d){
           $nodeList[] = array(
               'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
               'uuid' => $d['node_uuid']
           );
        }
        
        //已绑定节点数组
        foreach ($data1 as $d){
            $allocationList[] = $d['node_uuid'];
        }
        
        $info = array(
            'node_list' => $nodeList,
            'allocation_list' => $allocationList
        );
        
        return json_encode($info);
    }
    
    //分配备份节点到虚拟化平台
    public function allocationNodePlatform($params){
        $vcenteruuid = $params['vcenteruuid'];
        $nodes = $params['nodes'];
        
        if(!empty($vcenteruuid)){
            //如果虚拟化中心唯一标识不为空，清空当前虚拟化中心关联的备份节点
            $sql = "delete from mt_platform_node where platform_uuid = ? and type = ? ";
            $result = $this->dbExec($sql, array($vcenteruuid, 1));
        }
        //添加平台和节点关联关系
        if(!empty($nodes)){
            $nodesDes = implode("','", $nodes);
            $type = 1;
            $sql = "insert into mt_platform_node (node_uuid, platform_uuid, type) values ";
            foreach ($nodes as $key => $l){
                
                $sql .="('".$l."','".$vcenteruuid."',".$type.")";
                if($key != (count($nodes) - 1)){
                    $sql .= ",";
                }
            }
            $result = $this->dbExec($sql);
        }else{
            $result = true;
        }
        
       return $this->muOpResult($result, Xphp::$_lang['UI_VCENTER_ALLOCATION_NODE']);
    }
    //--------------------------------华为CBR新增 --------------------------
    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     * @param unknown $params
     */
    public function getAddStorageNodeSelectNew($params){
        //先找出存储类型 备份上云云存储不能和节点关联
        $sql1 ="select storage_type from bd_storage_resource where storage_uuid = ?";
        $data1 = $this->dbSelect($sql1,array($params['storageuuid']));
        $storagetype = $data1[0]['storage_type'];
        $sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname, bn.node_type from bd_node bn order by bn.node_type";
        $data = $this->dbSelect($sql);
        if($storagetype != Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']){
            //需要限制节点关联
            $sql = "select bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname, bn.node_type from bd_node bn, bd_storage_resource bsr where bn.node_uuid = bsr.node_uuid and bsr.storage_uuid  = ? order by bn.node_type";
            $data = $this->dbSelect($sql, array($params['storageuuid']));
        }
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $list = array();
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合  huaweicbr租户先不考虑
        // if (!empty($_SESSION['tenantuuid'])){
        //     $resourceHandler = Xphp::instance('ResourceHandler');
        //     $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['NODE']);
        //     if(!empty($resourceInfo)){
        //         foreach ($resourceInfo as $r){
        //             $resourceList[] = $r['resource_uuid'];
        //         }
        //     }
        // }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源 华为CBR先不考虑租户
            // if(!empty($_SESSION['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) continue;
            
            if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
            $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
                if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
            }
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if($nodeStatus['flag']){
                //如果节点状态正常
                $list[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                    'type' => intval($d['node_type'])
                );
            }
        }
        return json_encode($list);
    }
     /**
     * 得到恢复是华为CBR展示主节点
     * @param unknown $params
     */
    public function getCBRNode(){
        $sql = "select node_uuid, ip, node_nickname, host_name from bd_node";
        $data = $this->dbSelect($sql);
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if($nodeStatus['flag']){
                //如果节点状态正常
                $info[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                );
            }
        }
        return json_encode($info);

    }

    /**
     * 设置节点缓存
     * @return void
     */
    public function setNodeCache($params){
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_node_manager_add");
        $opcodeName = 'NODE_SYS_OP_CONFIG_SYSTEM_CACHE';
        $nodeuuid = $params['node_uuid'];
        $operate = Xphp::$_lang['WEB_NODE_OP_SET_CACHE'];
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($params));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 获取节点缓存配置
     * @param $params
     */
    public function getNodeCacheConf($params){
        $nodeUuid = $params['nodeuuid'];
        $this->paramsCheck($nodeUuid);

        $sql = "select cache_type, cache_dir_path, cache_storage_uuid,mount_point,switch_strategy,warning_flag,warning_value,last_warning_time 
                from bd_system_cache_config where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeUuid));
        $info = array();
        if(!empty($data)){
            $hisCacheType = $data[0]['cache_type'];
            $hisCacheDirPath = $data[0]['cache_dir_path'];
            $hisMountPoint = $data[0]['mount_point'];
            $hisCacheStorageUuid = $data[0]['cache_storage_uuid'];
            $hisSwitchStrategy = $data[0]['switch_strategy'];
            $hisWarningFlag = $data[0]['warning_flag'];
            $hisWarningValue = $data[0]['warning_value'];
            $storageResourceInfo = $this->getStorageInfoByUuid($nodeUuid);
            $info = array(
                'his_cache_type' => $hisCacheType,
                'his_cache_dir_path' => $hisCacheDirPath,
                'his_mount_point' =>  $hisMountPoint,
                'his_switch_strategy' => $hisSwitchStrategy,
                'his_cache_storage_uuid' => $hisCacheStorageUuid,
                'his_warning_flag' => $hisWarningFlag,
                'his_warning_value' => $hisWarningValue,
                'disk_storage_resource' => $storageResourceInfo
            );
        }

        return json_encode($info);
    }

    /**
     * 获取所有磁盘存储相关信息
     * @param $uuid
     * @return void
     */
    private function getStorageInfoByUuid($nodeUuid){
        $remote = Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'];
        $cloudStorage = Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'];
        $huaweiCbr = Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'];
        $storateTypeList = array(
            $remote,$cloudStorage,$huaweiCbr
        );
        $notUseType = implode("','", $storateTypeList);
        $sql = "select storage_nickname,mount_point,storage_uuid,total_size, free_size,storage_type 
                from bd_storage_resource 
                where node_uuid = ? and storage_type not in ('".$notUseType."')";
        $data = $this->dbSelect($sql,array($nodeUuid));
        $info = array();
        $utils = Xphp::instance("Utils");
        if(!empty($data)){
            foreach ($data as $d){
                $totalSize = $d['total_size'];
                $freeSize = $d['free_size'];
                $storageType = $d['storage_type'];

                $storageInfo = $d['storage_nickname'] . "(" . $this->getStorageTypeDes($storageType) .
                    ", " . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($totalSize) .
                    ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($freeSize) . ")";
                $info[] = array(
                    'storage_name' => $storageInfo,
                    'mount_point' => $d['mount_point'],
                    'storage_uuid' => $d['storage_uuid']
                );
            }
        }
        return $info;
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
    
}