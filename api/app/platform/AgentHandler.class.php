<?php
/*******************************************
** 代理端管理处理类
**
** @author       xiezhuowei@vinchin.com
** @date         2015-08-04 下午12:16:44
** @version      1.0.0
** @copyright    Copyright 2015 vinchin.com
********************************************/
class AgentHandler extends OPHandler{
    private $opcodeHandler;

    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('PFOpcodePrivate');
    }
    /**
     * 得到代理端管理所有代理信息
     * @param unknown $params
     * @return string
     */
    public function getAgentInfo($params){
        $start = intval($params['start']);
        $length = intval($params['length']);
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortParams = array('ba.hostname','ba.ip','ba.os_version','ba.register_time','ba.register_flag','bu.user_name','ba.online_flag');

        $sql = "select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.os_version, ba.process_type, 
                		ba.register_time, ba.register_flag, ba.online_flag, ba.authorization_module, 
                		bu.user_name, bu.user_uuid 
                from bd_agent ba 
                left join  bd_user bu 
                on ba.user_uuid = bu.user_uuid ";
        $sqlCount = "select count(ba.id) as total from bd_agent ba left join  bd_user bu on ba.user_uuid = bu.user_uuid ";
        $utils = Xphp::instance('Utils');
        $accurateFlag = $params['accurateFlag'];
        if($accurateFlag){
        	$search = $params['search'];
        	$hostname = $search['hostName'];
        	$hostname = $utils->escapeWildcard($hostname);
        	$ip = $search['agentIp'];
        	$onlineFlag = $search['onlineFlag'];
        	$registerFlag = $search['registerFlag'];
        	$username = $search['userName'];
        	$sql .= " where (ba.online_flag = ". $onlineFlag ." or ". $onlineFlag . " = 0) and
                 (ba.register_flag = ". $registerFlag ." or ". $registerFlag . " = 0) and
                 hostname like '%". $hostname ."%' ";
        	$sqlCount .= " where (ba.online_flag = ". $onlineFlag ." or ". $onlineFlag . " = '') and
                 (ba.register_flag = ". $registerFlag ." or ". $registerFlag . " = '') and
                 hostname like '%". $hostname ."%'";
        	$sqlParams = array();
        	$sqlCountParams = array();

        	if(!empty($ip)){
        		$sql .= " and ba.ip like '%". $ip ."%' ";
        		$sqlCount .= "and ba.ip like '%". $ip ."%' ";
        		$sqlParams = array_merge($sqlParams);
        		$sqlCountParams = array_merge($sqlCountParams);
        	}

        	if(!empty($username)){
        		$sql .= " and bu.user_name = ? ";
        		$sqlCount .= " and bu.user_name = ?";
        		$sqlParams = array_merge($sqlParams, array($username));
        		$sqlCountParams = array_merge($sqlCountParams, array($username));
        	}
        	$sqlParams = array_merge($sqlParams, array($start, $length));
        }else{
        	$sql .= " where (ba.online_flag = ? 
                or ba.register_flag = ?) ";
        	$sqlCount .= " where (ba.online_flag = ? or ba.register_flag = ?) ";
        	//根据主机名搜索
        	$search = $params['search'];
        	$hostName = $search['name'];
        	$hostName = $utils->escapeWildcard($hostName);
        	if(!empty($hostName)){
        		$sql .= " and ba.hostname like '%". $hostName ."%' ";
        		$sqlCount .= " and ba.hostname like '%". $hostName ."%' ";
        	}
        	$setFlag = Xphp::$_config['FLAG']['SET'];
        	$sqlParams = array($setFlag, $setFlag, $start, $length);
        	$sqlCountParams = array($setFlag, $setFlag);
        }
        $sql .=" and ba.agent_type = 0";
        $sqlCount .=" and ba.agent_type = 0";
        $sql .= " order by $sortParams[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $total = intval($count[0]['total']);
        //如果是租户内
        if(!empty($_SESSION['tenantuuid'])){
            $search['accurateFlag'] = $accurateFlag;
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserResourceFileHost(Xphp::$_user['useruuid'], "ba.id", $sortType, $start, $length, $search);
            $data = $resourceInfo['data'];
            $total = intval($resourceInfo['total']);
        }
        $records = array("data" => array());
        foreach ($data as $d){
            $records["data"][] = array(
                $this->getAgentName($d['hostname'], $d['agent_name']),
                $d['ip'],
                $d['os_version'],
                $d['register_flag'] == Xphp::$_config['FLAG']['SET'] ? $d['register_time'] : "----",
                $this->getAgentModule($d['authorization_module']),
                $d['user_name'],
                $this->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                $this->getAgentStatus($d['online_flag'], $d['register_flag']),
                array('uuid' => $d['agent_uuid'], 'useruuid' => $d['user_uuid']),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;
        return  json_encode($records);
    }

    /**
     * 得到代理端授权模块
     * @param string $authModule
     */
    public function getAgentModule($authModule){
        $module = array();
        if(empty($authModule)){
            return $module;
        }
        $authModule = json_decode($authModule, true);
        $moduleKey = array("file", "mysql", "oracle", "sqlserver", "dm", "database");
        foreach ($moduleKey as $key){
            if($authModule[$key]){
                $module[] = true;
            }else{
                $module[] = false;
            }
        }
        return $module;
    }

    /**
     * 得到代理端是否授权标志
     * @param string $authModule
     * @return boolean      已经有某个模块授权true/没有任何模块授权 false
     */
    public function getAgentAuthorise($authModule){
        $flag = false;
        $authModule = json_decode($authModule, true);
        foreach ($authModule as $auth){
            $flag = $flag || $auth;
            if($flag){
                //如果检查到有有授权,直接返回
                break;
            }
        }
        return $flag;
    }

    /**
     * 得到代理端名字
     * 如果用户自定义了名字就取自定义的名字;否则,直接取主机的名字
     * @param string $hostName
     * @param string $agentName
     */
    public function getAgentName($hostName, $agentName){
        if(empty($agentName) || $hostName == $agentName){
            return $hostName;
        }
        return $agentName;
    }

    /**
     * 获取代理端状态
     * @param int $onlineFlag
     * @param int $registerFlag
     */
    public function getAgentStatus($onlineFlag, $registerFlag){
        if($onlineFlag == Xphp::$_config['FLAG']['SET']){
            //在线
            if($registerFlag == Xphp::$_config['FLAG']['SET']){
                //注册
                return Xphp::$_config['AGENTSTATUS']['ONLINEREGISTER'];
            }else{
                //未注册
                return Xphp::$_config['AGENTSTATUS']['ONLINEUNREGISTERED'];
            }
        }else{
            //离线
            if($registerFlag == Xphp::$_config['FLAG']['SET']){
                //注册
                return Xphp::$_config['AGENTSTATUS']['OFFLINEREGISTER'];
            }else{
                //未注册
                return Xphp::$_config['AGENTSTATUS']['OFFLINEUNREGISTERED'];
            }
        }
    }

    /**
     * 得到代理端状态的描述(管理员)
     * @param int $onlineFlag
     * @param int $registerFlag
     */
    public function getAgentStatusDes($onlineFlag, $registerFlag){
        $status = $this->getAgentStatus($onlineFlag, $registerFlag);
        $pfDes = include APP_PATH . 'platform/PFDescription.php';
        return $pfDes['AGENTSTATUSDES'][$status];
    }

    /**
     * 得到代理端在线离线状态(操作员)
     * @param int $onlineFlag
     */
    public function getOnlineDes($onlineFlag){
        $pfDes = include APP_PATH . 'platform/PFDescription.php';
        return $pfDes['ONLINEDES'][$onlineFlag];
    }

    /**
     * 得到当前用户管理下的所有操作员用户
     * @param unknown $params
     */
    public function getManagerUsers($params){
        $fileFlag = $params['fileFlag'];    //文件代理入口
        $useruuid = $params['useruuid'];
        $sql = "select distinct bu.user_uuid, bu.user_name, mut.tenant_uuid from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid where bu.create_user_uuid = ? ";
        $sqlParams = array(Xphp::$_user['useruuid']);
        if($fileFlag && !empty($useruuid) && empty($_SESSION['tenantuuid'])){
            $sql .= " or bu.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        $data = $this->dbSelect($sql, $sqlParams);


        $users = array();
        $tenantHandler = Xphp::instance('TenantHandler');
        foreach ($data as $user){
            //如果选中的是当前用户自己先排除，在循环后已经加上了
            if(Xphp::$_user['useruuid'] == $user['user_uuid']) continue;
            $name =  $user['user_name'];
            if(!empty($user['tenant_uuid'])){
                $name = $tenantHandler->getTenantNameByUUID($user['tenant_uuid']) . "\\" . $user['user_name'];
            }
            $users[] = array(
                'uuid' => $user['user_uuid'],
                'name' => $name,
            );
        }
        //放开管理员权限,代理端可以分配给自己
        $name = Xphp::$_user['username'];
        if(!empty($_SESSION['tenantuuid'])){
            $name = $tenantHandler->getTenantNameByUUID($user['tenant_uuid']) . "\\" . $name;
        }
        $users[] = array(
            'uuid' => Xphp::$_user['useruuid'],
            'name' => $name,
        );

        return json_encode($users);
    }

    /**
     * 得到每个模块的授权信息
     * @param unknown $params
     */
    public function getModulesLisence($params){
        $systemHandler = Xphp::instance('SystemHandler');
        $valid = $systemHandler->getModulesLisenceValid();
        return json_encode($valid);
    }

    /**
     * 删除代理端
     * @param unknown $params
     */
    public function deleteAgent($params){
        $agentUUID = $params['agentUUID'];
        $this->paramsCheck($agentUUID);
        $msg = array(
            'agent_uuid' => $agentUUID,
        );
        $usersHandler = Xphp::instance('UsersHandler');
        //清除主机与用户关联
        $usersHandler->pDeleteUserResource("", array($agentUUID));
        $opName = "PT_LICENSE_OP_DEL_AGENT";
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 取消注册代理端
     * @param unknown $params
     */
    public function unregistAgent($params){
        $agentUUID = $params['agentUUID'];
        $this->paramsCheck($agentUUID);
        $msg = array(
            'agent_uuid' => $agentUUID,
        );
        $jsonMsg = json_encode($msg);
        $opName = "PT_LICENSE_OP_DEL_AGENT";
        $mbResult = $this->mbPFMsg($opName, $jsonMsg, false);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        $this->writeAuthLog($opName, $mbResult, json_decode($jsonMsg, true));

        $operate = Xphp::$_lang['WEB_PT_LICENSE_OP_UNREGIST_AGENT'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 注册代理端
     * @param unknown $params
     * [hostName(string)主机名,userUUID(string)用户UUID,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function registAgent($params){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_agent_manager_register");
        $opName = "PT_LICENSE_OP_ADD_AGENT";
        $systemHandler = Xphp::instance('SystemHandler');
        $fileInfo = $systemHandler->getFileLisenceInfo(array());
        $fileInfo = json_decode($fileInfo, true);
        $liceseTypeInfo = $systemHandler->getSystemLicenseType();
        $liceseTypeInfo = json_decode($liceseTypeInfo, true);
        $liceseType = $liceseTypeInfo['licensetype'];
        $tenantHandler = Xphp::instance('TenantHandler');
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        $tenantAuthType = intval($settings['common']['authtype']);
        if((empty($_SESSION['tenantuuid']) && $liceseType != Xphp::$_config['LISENCE_INFO']['type']['storage']) || (!empty($_SESSION['tenantuuid']) && $tenantAuthType != 1)){
            if(intval($fileInfo['valid']) < 1 && $params['module']['file']){
                $operate = $this->opcodeHandler->getOpcodeDes($opName);
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_AGENT_FS_NO_AVAILABLE_ERROR'], "warning"));
            }
        }
        return $this->registAndEditAgent($opName, $params);
    }

    /**
     * 注册数据库代理端
     * @param unknown $params
     * [hostName(string)主机名,userUUID(string)用户UUID,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function registDbAgent($params){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_db_agent_manager_license");
        //是否注册
        $registerFlag = $this->getAgentRegisterFlag($params['agentUUID']);
        if($registerFlag == Xphp::$_config['FLAG']['UNSET']){
            $opName = "PT_LICENSE_OP_ADD_AGENT_LICENSE";
        }else{
            $opName = "PT_LICENSE_OP_MODIFY_AGENT_LICENSE";
        }
        $systemHandler = Xphp::instance('SystemHandler');
        $tenantHandler = Xphp::instance('TenantHandler');
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        $tenantAuthType = intval($settings['common']['authtype']);
        $dbInfo = $systemHandler->getDBLisenceInfo(array());
        $dbInfo = json_decode($dbInfo,true);
        $liceseTypeInfo = $systemHandler->getSystemLicenseType();
        $liceseTypeInfo = json_decode($liceseTypeInfo, true);
        $liceseType = $liceseTypeInfo['licensetype'];
        //备份系统不是容量授权，或租户里面不是按容量授权执行
        if((empty($_SESSION['tenantuuid']) && $liceseType != Xphp::$_config['LISENCE_INFO']['type']['storage']) || (!empty($_SESSION['tenantuuid']) && $tenantAuthType != 1)){
            if(intval($dbInfo['valid']) < 1 && $params['module']['database']){
                $operate = $this->opcodeHandler->getOpcodeDes($opName);
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_AGENT_DB_NO_AVAILABLE_ERROR'], "warning"));
            }
        }

        $params['userUUID'] = Xphp::$_user['useruuid'];
        return $this->registAndEditAgent($opName, $params);
    }

    /**
     * 修改代理端
     * @param unknown $params
     * [hostName(string)主机名,userUUID(string)用户UUID,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function editAgent($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_agent_manager_modify");
        $opName = "PT_LICENSE_OP_MODIFY_AGENT";
        $usersHandler = Xphp::instance('UsersHandler');
        //检查代理有无任务存在
        $this->checkAgentTaskExist($params['agentUUID']);
        //清除主机与其他用户关联
        $usersHandler->pDeleteUserResource("", array($params['agentUUID']));
        return $this->registAndEditAgent($opName, $params);
    }

    /**
     * 统一处理管理员注册和修改代理端(数据包一样的)
     * @param string $opName
     * @param array $params
     */
     public function registAndEditAgent($opName, $params){
        $agentUUID = $params['agentUUID'];
        $userUUID = $params['userUUID'];
        $hostName = $params['hostName'];
        $newmodule  =$params['newmodule'];
        $this->paramsCheck($agentUUID, $userUUID);
        //msg里面的authorization_module是一个包含了所有模块授权信息的JSON字符串
        $msg = array(
            'agent_uuid' => $agentUUID,
            'authorization_module' => $this->getFullAuthorizationModule($params['module']),
            'agent_name' => $hostName,
            'user_uuid' => $userUUID,
            'newmodule'=>$newmodule
        );
        $usersHandler = Xphp::instance('UsersHandler');
        $resourceList[] = array(
            'resourceuuid' => $agentUUID,
            'vmuuid' => "",
            'vcenteruuid' => "",
            'resourceType' => Xphp::$_config['RESOURCE_TYPE']['FILE_HOST']
        );
        //获取当前用户是否为租户管理员
        $tenantMangerFlag = $usersHandler->pCheckTenantManager();
//         //添加资源与用户关联
        $usersHandler->pAddUserResource($userUUID, $resourceList);
        if(!$tenantMangerFlag){
            $usersHandler->pAddUserResource(Xphp::$_user['useruuid'], $resourceList);
        }
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 得到完全版本的授权信息
     * @param array $params
     * @return json
     */
    public function getFullAuthorizationModule($params){
        $fullAuthModule = array(
            'file' => false,
            'vm' => false,
            'mysql' => false,
            'oracle' => false,
            'sqlserver' => false,
            'dm' => false,
            'os' => false,
            'cdp' => false,
            'desktop' => false,
            'database' => false,
        );
        foreach ($fullAuthModule as $key => $value){
            if(!empty($params[$key])){
                $fullAuthModule[$key] = $params[$key];
            }
        }
        return json_encode($fullAuthModule);
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param int $submodule_type
     * @param string $opName
     * @param json $msg
     * @param bool $sync 默认异步
     * @return string
     */
    private function unifyMsg($opName, $jsonMsg, $sync = false){
        $mbResult = $this->mbPFMsg($opName, $jsonMsg, $sync);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        $this->writeAuthLog($opName, $mbResult, json_decode($jsonMsg, true));
        return $mbResult['result'];
//         //返回结果到UI
//         if($result){
//             return $this->muOpResult($result, $operate, $msg);
//         }else{
//             return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
//         }
    }

    /**
     * 得到自己的代理端信息
     * @param unknown $params
     */
    public function getMyAgentInfo($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $accurateFlag = $params['accurateFlag'];
        $sortParams = array('','hostname','ip','os_version','register_time','authorization_module','online_flag');

        $sql = "select agent_uuid, agent_name, hostname, ip, os_type, os_version, process_type,
                		register_time, register_flag, online_flag, authorization_module 
                from bd_agent 
                where user_uuid = ? and agent_type = 0 ";
        $sqlCount = "select count(id) as total from bd_agent 
                    where user_uuid = ? and agent_type = 0 ";
        $sql .= " order by $sortParams[$sortColumn] $sortType limit ? , ? ";
        $userUUID = Xphp::$_user['useruuid'];
        $sqlParams = array($userUUID, $start, $length);
        $sqlCountParams = array($userUUID);

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $total = intval($count[0]['total']);
        //如果是租户内
        if(!empty($_SESSION['tenantuuid'])){
            $search['accurateFlag'] = $accurateFlag;
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserResourceFileHost(Xphp::$_user['useruuid'], "ba.id", $sortType, $start, $length, $search);
            $data = $resourceInfo['data'];
            $total = intval($resourceInfo['total']);
        }

        $records = array("data" => array());
        foreach ($data as $d){
            $div = "";
            if(intval($d['online_flag']) == Xphp::$_config['FLAG']['UNSET']){
                $div ='<input type="checkbox" name="id[]" value="' . $d['agent_uuid'] . '">';
            }
            $records["data"][] = array(
                $div,
                $this->getAgentName($d['hostname'], $d['agent_name']),
                $d['ip'],
                $d['os_version'],
                $d['register_flag'] == Xphp::$_config['FLAG']['SET'] ? $d['register_time'] : "----",
                $this->getAgentModule($d['authorization_module']),
                $this->getOnlineDes($d['online_flag']),
                array('uuid' => $d['agent_uuid'], "onlineFlag" => intval($d['online_flag'])),
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return  json_encode($records);
    }

    /**
     * 得到可下载的代理地址
     * @param unknown $params
     */
    public function getDoloadAgentName($params){
        $pfDes = require_once API_PATH.'app/platform/PFDescription.php';
        $vendor = require_once API_PATH.'xphp/conf/vendor.php';
        $agent = $this->getAllPakagesInfo();

        $agentType = $vendor['AGENT_TYPE'];
        $agentVendor = $vendor['AGENT_VENDOR'];
        $dbcdpVendor = $vendor['DBREALTIME_CLIENT'];

        //客户端插件
        $clientOs = $vendor['CLIENT_OS'];
        $clientVendor = array();
        foreach ($clientOs as $os){
            $temp = array();
            if(empty($os['version'])){
                $temp['text'] = $os['text'];
                $temp['value'] = $os['value'];
                $clientVendor[] = $temp;
            }else{
                foreach ($os['version'] as $version){
                    $temp['text'] = $version['text'];
                    $temp['value'] = $version['value'];
                    $clientVendor[] = $temp;
                }
            }

        }

      

        //获取是否授权
        $sql ="select authorized_flag from bd_system";
        $data = $this->dbSelect($sql);
        $authflag = intval($data[0]['authorized_flag']);
        // 授权控制操作系统
        $licenceOs = Xphp::instance('SystemHandler')->getExtensionLicense()['os'];  // 授权的操作系统
        if($authflag != Xphp::$_config['FLAG']['UNSET'] && empty($licenceOs)){
            // 系统授权其他状态(正常,异常,过期等),如果extendLicense['os']为空,显示所有操作系统
            foreach ($clientOs as $os){
                $licenceOs[] = $os['licence_key'];
                if(!empty($os['version'])){
                    foreach ($os['version'] as $version){
                        $licenceOs[] = $version['licence_key'];
                    }
                }
            }
        }


        // 筛选出整数（操作系统）
        // 筛选出小数（版本架构）
        $osArr = array();
        $versionArr = array();
        foreach ($licenceOs as $os){
            if(is_int($os)){
                $osArr[] = $os;
            }else if(is_float($os)){
                $versionArr[] = $os;
            }
        }
        $arr = array();
        // 循环CLIENT_OS
        foreach ($clientOs as $client){
            $temp = array();
            // 客户端
            if(in_array($client['licence_key'],$osArr)){
                $temp['text'] = $client['text'];
                $temp['value'] = $client['value'];
                $temp['oversea_flag'] = $client['oversea_flag'];
                $temp['licence_key'] = $client['licence_key'];
                foreach ($client['version'] as $version){
                    // 再循环version数组
                    if(in_array($version['licence_key'],$versionArr)){
                        $temp['version'][] = $version;
                    }
                }
                $arr[] = $temp;
            }
        }
        $clientOs = $arr;

        // 初始化版本架构
        $clientVersion = $clientOs[0]['version'];
        $versionArr = array();
        if(!empty($clientVersion)){
            foreach ($clientVersion as $version){
                $temp['text'] = $version['text'];
                $temp['value'] = $version['value'];
                $temp['licence_key'] = $version['licence_key'];
                $versionArr[] = $temp;
            }
        }

        // 构造新的操作系统数组
        $dbTimingHandler = Xphp::instance('DBTimingHandler');
        $dbCDPHandler = Xphp::instance('DbCDPHandler');
        $rpc = Xphp::instance('DbRPCHandler');
        //得到类型的列表
        $agentTypeDes = array();


        //判断文件模块是否授权
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        $pagelist = $extension['p'];
        foreach ($agentType as $type){
            if($type == Xphp::$_config['FLAG']['UNSET']  && $authflag == Xphp::$_config['FLAG']['UNSET']){
                // 系统未授权,不显示任何下载的操作系统
                continue;
            }
            //sangfor代理类型显示不一致
            if(Xphp::$_config['SYSTEM_INFO']['enterprise'] == 'sangfor_enterprise' && $type == 2){
                $text = Xphp::$_lang['WEB_AGENT_TIMING'];
            }else{
                $text = $pfDes['AGENT_TYPE_DES'][$type];
            }
            $eachAgent = array(
                'text' => $text,
                'value' => $type,
                'href' => '#'
            );
            $flag = true;
            switch ($type){
                case $vendor['AGENT_TYPE']['NODE']:
                    //添加节点链接
                    $eachAgent['href'] = $agent['node'];
                    break;
                case $vendor['AGENT_TYPE']['VM']:

                    break;
                case $vendor['AGENT_TYPE']['CLIENT']:

                    break;
                case $vendor['AGENT_TYPE']['DBTIMING']:
                    $cmdStr = "ps aux|grep daserver";
                    exec($cmdStr, $cmdinfo);
                    if(count($cmdinfo) > 2){
                        if(!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime){
                            $dbTimingHandler->getDBAuth(); //获取datapp认证
                        }
                        $dbTimingInfo = $dbTimingHandler->getDBLisenceInfo(); //获取datapp授权信息
                        if($dbTimingInfo['state'] == 0) continue; //主机保护未授权不显示插件下载
                    }else{
                        continue;
                    }
                    break;
                case $vendor['AGENT_TYPE']['DBREALTIME']:
                    $cmdStr = "ps aux|grep lzbackupsys";
                    exec($cmdStr, $cmdinfo);
                    if(count($cmdinfo) > 2){
                        //是否获取cdp授权信息
                        //$hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
                        $hostip = $_SERVER['SERVER_ADDR'];
                        $result = $rpc->getLicenseCenterAuthorInfo($hostip, array());
                        if($result['result']){
                            $data = $dbCDPHandler->getLicenseCenterAuthorInfo();
                            if($data[0]['liceselflag'] != Xphp::$_config['FLAG']['SET']){
                                //数据库CDP未授权不显示插件下载
                                $flag = false;
                            }
                        }else{
                            $flag = false;
                        }

                    }else{
                        $flag = false;
                    }
                    break;
            }
            if($flag){
                $agentTypeDes[] = $eachAgent;
            }
        }
        

        //根据需要虚拟化屏蔽相应虚拟化插件
        $releaseHypervisor = array();
        if(!empty($extension)){
            $releaseHypervisor = $extension['v'];
        }else{
            $configFile = Xphp::$_config['SPECIAL_DIR'] . Xphp::$_config['SPECIAL_CONFIG'];
            if(file_exists($configFile)){
                $content = file_get_contents($configFile);
                $config = json_decode($content, true);
                $releaseHypervisor = $config['RELEASE_HYPERVISOR'];
            }
        }
        if(!empty($releaseHypervisor)){
            $agentVendorUsed = array();
            foreach ($agentVendor as $key => $eachVendor){
                foreach ($releaseHypervisor as $hypervisor){
                    if(Xphp::$_config['VMHYPERVISORDES'][intval($hypervisor)] == $eachVendor['text']){
                        //如果配置有这个虚拟化就展开相应插件
                        $agentVendorUsed[] = $eachVendor;
                    }
                    continue;
                }
            }
            //使用配置所有的虚拟化包含的插件
            $agentVendor = $agentVendorUsed;
        }

        //操作系统对应的架构版本
        $agentOsUsed = array();
        $lang = $_SESSION['language'] ?? Xphp::$_config['lang'];
        foreach ($clientOs as $key => $eachOS){
            if ($lang != 'zh-cn' && $lang != 'zh-tw') {
                if (!$eachOS['oversea_flag']) {  // 海外不显示的操作系统
                    continue;
                }
            }
            $agentOsUsed[] = $eachOS;
        }
        //使用配置所有的虚拟化包含的插件
        $clientOs = $agentOsUsed;

        //数据库实时安装包
        foreach ($dbcdpVendor as $k1 => $version){
            if('#' != $version['value']){
                //如果是"#"就不替换,如果指定了安装包名就替换
                $dbcdpVendor[$k1]['value'] = $agent[$version['value']];
            }
        }
        //数据库定时客户端
        $dbtimingClient = $vendor['DBTIMING_CLIENT'];
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $info = array(
            'type' => $agentTypeDes,
            'vendor' => $agentVendor,
            'softwareType' => $softwareType,
            'client' => $versionArr,
            'clientOs' => $clientOs,
            'dbtiming' => $dbtimingClient,
            'dbcdp' => $dbcdpVendor,
        );


        return json_encode($info);
    }

    /**
     * 得到系统所有的安装包信息
     * @link ClientHandler::getUpgradeClientTable()
     * return array
     */
    public function getAllPakagesInfo(){
        $agent = array(
            'WINDOWS' => '#',
            'RHEL5' => '#',
            'RHEL6' => "#",
            'RHEL7' => "#",
            'RHEL8' => "#",
            'RHEL9' => '#',
            'UBUNTU' => "#",
            'DEBIAN' => "#",
            'KYLIN' => "#",
            'UNIONTECH' => "#",
            'WINPE' => '#',
            'xe.6.2' => '#',
            'xe.6.5' => '#',
            'RHEL.6' => '#',
            'RHEL.7' => '#',
            'RHEL.8' => '#',
            'Ubuntu.12' => '#',
            'whrelease' => '#',
            'release' => '#',
            'stack-cloud.RHEL' => '#',
            'stack-cloud.Ubuntu' => '#',
            'stack-docker.RHEL' => '#',
            'stack-docker.Ubuntu' => '#',
            "vinchin-agent" => "#",
            "dbcdp-agent.windows" => "#",
            "dbcdp-agent.linux" => "#",
            'el7' => "#",
            'el8' => "#",
            'ARM-RHEL7' => "#",
            'ARM-RHEL8' => "#",
            'ARM-el7' => "#",
            'KYLINX86' => "#",
            'ZKFD-V4' => "#",
            'UNIONTECHX86' => '#',
            'ANOLISOSX64' => '#',
            //add
            'EulerX86' => '#',
            'EulerAARCH64' => '#',
            'SUSE' => '#',
            'ROCKYLINUX8' => '#',
            'ROCKYLINUX9' => '#',
            'ORACLELINUX6' => '#',
            'ORACLELINUX7' => '#',
            'ORACLELINUX8' => '#',
            'ORACLELINUX9' => '#',
            'ANOLIS7OSX64' => '#',
            'ANOLIS8OSX64' => '#',
            'ASTRALINUX' => '#',
            'REDOS' => '#',
            'NSLINUX' => '#',
        );
        //按时间排序,时间从前到后,防止升级的时候有多个匹配成功也可以返回最新的
        $cmdStr = "ls -tr " . Xphp::$_config['AGENT_PATH'];
        exec($cmdStr, $agentInfo);
        //统一换成大写字母做判断,避免写错,排错要一个个字母去看,比较麻烦

        // 使用正则表达式匹配包名中的操作系统、Linux 版本和架构
        $systemVendor = '';
        $enterpriseVersions = Xphp::$_config['ENTERPRISE'];
        $enterpriseVersions['enterprise_ak'] = 'vinchin_enterprise_ak';  // 新创麒麟arm版
        $enterpriseVersions['enterprise_ck'] = 'vinchin_enterprise_ck';  // 新创麒麟x86版
        if(!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], $enterpriseVersions)){
            //oem统一用backup-system
            $systemVendor = 'backup-system';
        }else{
            $systemVendor = Xphp::$_config['SYSTEM_INFO']['vendor'];
        }
        foreach ($agentInfo as $name){

            // 匹配操作系统子项的正则表达式
            $pattern = '/'.$systemVendor.'-backup-agent-(.*)-AGENT\.([^\.]+)(?:\.(\d+))?-(.+)\.tar\.gz/';

            preg_match($pattern, $name, $matches);
            $packageNames = [];
            if (!empty($matches)){
                $package_version = $matches[1];
                $operating_systems = explode('-', $matches[2]);
                $linux_version = isset($matches[3]) ? $matches[3] : '';
                $architecture = $matches[4];

                foreach ($operating_systems as $os) {
                    $packageNames[] = strtoupper($os) . $linux_version . '-'.$architecture;
                    $packageNames['linux_version'] = $linux_version;
                    $packageNames['architecture'] = $architecture;
                }
            }

            if(strpos(strtoupper($name), strtoupper('dbcdp-agent.windows'))){
                $agent['dbcdp-agent.windows'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('dbcdp-agent.linux')) === 0){
                $agent['dbcdp-agent.linux'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('hyper-v-agent'))){
                $agent['vinchin-agent'] = "/agent/" . $name;
                continue;
            }

            //客户端   示例:RHEL8-x86_64
            if(strpos(strtoupper($name), strtoupper('backup-agent.windows'))){
                $agent['WINDOWS'] = array();
                $agent['WINDOWS']['fileName'] = "/agent/" . $name;
                $agent['WINDOWS']['showName'] = $name;
                continue;
            }
            if(in_array('RHEL5-Based-x86_64',$packageNames)){
                $agent['RHEL5'] = array();
                $agent['RHEL5']['fileName'] = "/agent/" . $name;
                $agent['RHEL5']['showName'] = $name;
                $agent['RHEL5']['linux_version'] = $packageNames['linux_version'];
                $agent['RHEL5']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('RHEL6-Based-x86_64',$packageNames)){
                $agent['RHEL6'] = "/agent/" . $name;
                $agent['ORACLELINUX6'] = "/agent/" . $name;
                $systems = ['RHEL6', 'ORACLELINUX6'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('RHEL7-Based-x86_64',$packageNames)){
                $agent['RHEL7'] = "/agent/" . $name;
                $agent['ORACLELINUX7'] = "/agent/" . $name;
                $agent['REDOS'] = "/agent/" . $name;
                $agent['ANOLIS7OSX64'] = "/agent/" . $name;
                $systems = ['RHEL7', 'ORACLELINUX7','REDOS','ANOLIS7OSX64'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('RHEL8-Based-x86_64',$packageNames)){
                // 下载的是同一个包
                $agent['RHEL8'] = "/agent/" . $name;
                $agent['ORACLELINUX8'] = "/agent/" . $name;
                $agent['ANOLIS8OSX64'] = "/agent/" . $name;  // 龙蜥X64下载指向CentOS 8
                $agent['ROCKYLINUX8'] = "/agent/" . $name;  // 龙蜥X64下载指向CentOS 8
                $agent['EulerX86'] = "/agent/" . $name;
                $systems = ['RHEL8', 'ORACLELINUX8','ANOLIS8OSX64','ROCKYLINUX8','EulerX86'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('RHEL9-Based-x86_64',$packageNames)){
                // 下载的是同一个包
                $agent['RHEL9'] = "/agent/" . $name;
                $agent['ORACLELINUX9'] = "/agent/" . $name;
                $agent['ROCKYLINUX9'] = "/agent/" . $name;
                $systems = ['RHEL9', 'ORACLELINUX9','ROCKYLINUX9'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('DEBIAN-x86_64',$packageNames) && in_array('BASED-x86_64',$packageNames)){
                // 下载的是同一个包
                $agent['DEBIAN'] = "/agent/" . $name;
                $agent['UBUNTU'] = "/agent/" . $name;
                $agent['SUSE'] = "/agent/" . $name;
                $agent['NSLINUX'] = "/agent/" . $name;
                $agent['ASTRALINUX'] = "/agent/" . $name;
                $systems = ['DEBIAN', 'UBUNTU', 'SUSE', 'NSLINUX', 'ASTRALINUX'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = $name;
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }
                continue;
            }
            if(in_array('KYLIN-aarch64',$packageNames)){
                $agent['KYLIN'] = array();
                $agent['KYLIN']['fileName'] = "/agent/" . $name;
                $agent['KYLIN']['showName'] = $name;
                $agent['KYLIN']['linux_version'] = $packageNames['linux_version'];
                $agent['KYLIN']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('KYLIN-x86_64',$packageNames)){
                $agent['KYLINX86'] = array();
                $agent['KYLINX86']['fileName'] = "/agent/" . $name;
                $agent['KYLINX86']['showName'] = $name;
                $agent['KYLINX86']['linux_version'] = $packageNames['linux_version'];
                $agent['KYLINX86']['architecture'] = $packageNames['architecture'];
                continue;
            }
            //统信x86
            if(in_array('UOS-x86_64',$packageNames)){
                $agent['UNIONTECHX86'] = array();
                $agent['UNIONTECHX86']['fileName'] = "/agent/" . $name;
                $agent['UNIONTECHX86']['showName'] = $name;
                $agent['UNIONTECHX86']['linux_version'] = $packageNames['linux_version'];
                $agent['UNIONTECHX86']['architecture'] = $packageNames['architecture'];
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('winpe-os-agent'))){
                $agent['WINPE'] = array();
                $agent['WINPE']['fileName'] = "/agent/" . $name;
                $agent['WINPE']['showName'] = $name;
                $agent['WINPE']['linux_version'] = $packageNames['linux_version'];
                $agent['WINPE']['architecture'] = $packageNames['architecture'];
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('xe.6.2'))){
                $agent['xe.6.2'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('xe.6.5'))){
                $agent['xe.6.5'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.6'))){
                $agent['RHEL.6'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('Ubuntu.12'))){
                $agent['Ubuntu.12'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.7-x86'))){
                $agent['RHEL.7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.8-aarch64'))){
                $agent['ARM-RHEL8'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.7-aarch64'))){
                $agent['ARM-RHEL7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('Debian.7-x86_64'))){
                $agent['Debian.7'] = "/agent/" . $name;
                continue;
            }
            //中科v4
            if(in_array('NFSCHINA-x86_64',$packageNames)){
                $agent['ZKFD-V4'] = array();
                $agent['ZKFD-V4']['fileName'] = "/agent/" . $name;
                $agent['ZKFD-V4']['showName'] = $name;
                $agent['ZKFD-V4']['linux_version'] = $packageNames['linux_version'];
                $agent['ZKFD-V4']['architecture'] = $packageNames['architecture'];
                continue;
            }

            //云宏kvm arm版本
            if(strpos(strtoupper($name), strtoupper('el7.aarch64'))){
                $agent['ARM-el7'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-cloud')) && strpos(strtoupper($name), strtoupper('el7'))){
                $agent['el7'] = "/agent/" . $name;
                $agent['stack-cloud.RHEL'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-cloud')) && strpos(strtoupper($name), strtoupper('el8'))){
                $agent['el8'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-cloud'))){
                //合并处理5.0.8的OpenStack插件,需要两次判断
                $suffixStr = substr($name, -3, 3);  //获取后缀
                if($suffixStr == "deb"){
                    $agent['stack-cloud.Ubuntu'] = "/agent/" . $name;
                }
                if($suffixStr == "rpm"){
                    $agent['stack-cloud.RHEL'] = "/agent/" . $name;
                }
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('stack-patch-docker'))){
                //合并处理5.0.8的OpenStack插件,需要两次判断
                $suffixStr = substr($name, -3, 3);  //获取后缀
                if($suffixStr == "deb"){
                    $agent['stack-docker.Ubuntu'] = "/agent/" . $name;
                }
                if($suffixStr == "rpm" && strpos(strtoupper($name), strtoupper('el7'))){
                    $agent['stack-docker.RHEL'] = "/agent/" . $name;
                }
                continue;
            }
            if(in_array('RHEL6-X86_64',$packageNames)){
                $agent['RHEL.6'] = array();
                $agent['Ubuntu.6']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.6']['showName'] = $name;
                $agent['Ubuntu.6']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.6']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('RHEL7-X86_64',$packageNames)){
                $agent['RHEL.7'] = array();
                $agent['Ubuntu.7']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.7']['showName'] = $name;
                $agent['Ubuntu.7']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.7']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('RHEL8-X86_64',$packageNames)){
                $agent['RHEL.8'] = array();
                $agent['Ubuntu.8']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.8']['showName'] = $name;
                $agent['Ubuntu.8']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.8']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('UBUNTU12-X86_64',$packageNames)){
                $agent['Debian.12'] = array();
                $agent['Ubuntu.12']['fileName'] = "/agent/" . $name;
                $agent['Ubuntu.12']['showName'] = $name;
                $agent['Ubuntu.12']['linux_version'] = $packageNames['linux_version'];
                $agent['Ubuntu.12']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(in_array('DEBIAN7-X86_64',$packageNames)){
                $agent['Debian.7'] = array();
                $agent['Debian.7']['fileName'] = "/agent/" . $name;
                $agent['Debian.7']['showName'] = $name;
                $agent['Debian.7']['linux_version'] = $packageNames['linux_version'];
                $agent['Debian.7']['architecture'] = $packageNames['architecture'];
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('whrelease'))){
                $agent['whrelease'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('wh7.5release'))){
                $agent['release'] = "/agent/" . $name;
                continue;
            }
            //add
            if(in_array('OPENEULER-aarch64',$packageNames)){
                $systems = ['EulerAARCH64', 'UNIONTECH'];
                foreach ($systems as $system) {
                    $agent[$system] = array();
                    $agent[$system]['fileName'] = "/agent/" . $name;
                    $agent[$system]['showName'] = Xphp::$_config['SYSTEM_INFO']['vendor'].'-'.'backup-agent-'.$package_version.'-AGENT.'.$system.'-'.$packageNames['architecture'].'.tar'.'.gz';
                    $agent[$system]['linux_version'] = $packageNames['linux_version'];
                    $agent[$system]['architecture'] = $packageNames['architecture'];
                }


                continue;
            }
            if(in_array('REDOS7-x86_64',$packageNames)){
                $agent['REDOS'] = array();
                $agent['REDOS']['fileName'] = "/agent/" . $name;
                $agent['REDOS']['showName'] = $name;
                $agent['REDOS']['linux_version'] = $packageNames['linux_version'];
                $agent['REDOS']['architecture'] = $packageNames['architecture'];
                continue;
            }

        }
        return $agent;
    }

    /**
     * 删除添加的离线代理
     */
    public function deleteOfflineAgent($params){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_my_agent_delete");
        $uuids = $params['uuids'];
        $uuidDes = implode("','", $uuids);
        $sql = "delete from bd_agent where agent_uuid in ('". $uuidDes ."')";
        $result = $this->dbExec($sql);
        $this->systemLog('SYSTEM_LOG_DELETE_OFFLINE_FILE_AGENT');
        return $this->muOpResult($result, Xphp::$_lang['UI_AGENT_DELETE_OFFLINE']);
    }

      /**
     * 写授权系统日志
     * @param unknown $opName
     * @param unknown $mbResult
     * @param unknown $msg
     * @return boolean
     */
    private function writeAuthLog($opName, $mbResult, $msg){
        $key = '';
        $params = array();
        if($opName == "PT_LICENSE_OP_ADD_AGENT"){
            $key = 'SYSTEM_LOG_ADD_FILE_AGENT_AUTH';
            $params = array($msg['agent_name']);
        }else if($opName == "PT_LICENSE_OP_MODIFY_AGENT"){
            $key = 'SYSTEM_LOG_EDIT_FILE_AGENT_AUTH';
            $params = array($msg['agent_name']);
        }else if($opName == "PT_LICENSE_OP_DEL_AGENT"){
            $key = 'SYSTEM_LOG_DELETE_FILE_AGENT';
            $sql = "select agent_name from bd_agent where agent_uuid = ? ";
            $data = $this->dbSelect($sql, array($msg['agent_uuid']));
            $params = array($data[0]['agent_name']);
        }else if($opName == "PT_LICENSE_OP_ADD_AGENT_LICENSE"){
            $key = 'SYSTEM_LOG_ADD_DB_AGENT_AUTH';
            $sql = "select bda.agent_name,bda.hostname,bda.ip from bd_agent bda where bda.agent_uuid= ?" ;
            $data = $this->dbSelect($sql, array($msg['agent_uuid']));
            $hostname = $data[0]['hostname']? $data[0]['hostname']: $data[0]['agent_name'];
            $newmodule  = $msg['newmodule'];
            $modulename  =[];
            foreach($newmodule as $modulekey => $value){
                if($value){
                    $name = $this->getAuthModuleName($modulekey);
                    array_push($modulename,$name);
                }
            }
            if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
                $modulenames =  implode("、",$modulename);
            }else{
                $modulenames =  implode(",",$modulename);
            }

            $params = array($hostname."[".$data[0]['ip']."]",$modulenames);
        }else if($opName == "PT_LICENSE_OP_MODIFY_AGENT_LICENSE"){
            $key = 'SYSTEM_LOG_CANCEL_DB_AGENT_AUTH';
            $sql = "select bda.agent_name,bda.hostname,bda.ip from bd_agent bda where bda.agent_uuid= ?" ;
            $data = $this->dbSelect($sql, array($msg['agent_uuid']));
            $hostname = $data[0]['hostname']? $data[0]['hostname']: $data[0]['agent_name'];
            $newmodule  = $msg['newmodule'];
            $modulename  =[];
            foreach($newmodule as $modulekey => $value){
                if($value){
                    $name = $this->getAuthModuleName($modulekey);
                      array_push($modulename,$name);
                }
            }
            if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
                $modulenames =  implode("、",$modulename);
            }else{
                $modulenames =  implode(",",$modulename);
            }
            $params = array($hostname."[".$data[0]['ip']."]",$modulenames);
        }
        if($mbResult['result']){
            $this->systemLog($key, $params);
        }else{
            $this->systemLog($key, $params,  Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
        }
        return true;
    }

    private function getAuthModuleName($modulename){
            $modulenameDes = "";
            switch ($modulename){
                case 'file':
                    $modulenameDes =  Xphp::$_lang['WEB_PLATFORM_DES_FS'];
                    break;
                case 'vm':
                    $modulenameDes =  Xphp::$_lang['WEB_PLATFORM_DES_VM'];
                    break;
                case 'database':
                    $modulenameDes =  Xphp::$_lang['WEB_PLATFORM_DES_DB'];
                    break;
                case 'os':
                    $modulenameDes =  Xphp::$_lang['WEB_PLATFORM_DES_OS'];
                    break;

            }
            return $modulenameDes;
    }



    /**
     * 得到代理端名字
     * 如果用户自定义了名字就取自定义的名字;否则,直接取主机的名字
     * @param string $hostName
     * @param string $agentName
     * @param string $ip
     */
    public function getJobAgentName($hostName, $agentName, $ip){
        $name = $agentName;
        if(empty($agentName) || $hostName == $agentName){
            $name = $hostName;
        }
        if($name != $ip){
            $name .= "(".$ip.")";
        }
        return $name;
    }

    /**
     * 获取代理是否注册标志
     * @param unknown $agentuuid
     * @return number|mixed
     */
    public function getAgentRegisterFlag($agentuuid){
        $sql = "select register_flag, authorization_module from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $flag = Xphp::$_config['FLAG']['UNSET'];
        if(!empty($data)){
            if(!empty($data[0]['authorization_module'])){
                $flag = Xphp::$_config['FLAG']['SET'];
            }
        }

        return $flag;
    }

    /**
     * 得到代理分组信息
     * @param unknown $params
     * @return string
     */
    public function getAgentGroupInfo($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $search = $params['search'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'bag.group_name', 'bag.remark', 'bag.detail',
            'bag.create_time', 'bag.user_uuid'
        );

        $sql = "select bag.detail,bag.group_uuid, bag.group_name, bag.remark, unix_timestamp(bag.create_time) as create_time, bu.user_name 
                from bd_agent_group bag left join bd_user bu on bag.user_uuid = bu.user_uuid where bag.group_type = ? and bag.user_uuid = ? ";

        $sqlCount = "select count(bag.id) as total from bd_agent_group bag left join  bd_user bu on bag.user_uuid = bu.user_uuid where bag.group_type = ? and bag.user_uuid = ? ";

        $sqlParams = array(Xphp::$_config['AGENT_GROUP_TYPE']['FILE'], Xphp::$_user['useruuid'], $start, $length);
        $sqlCountParams = array(Xphp::$_config['AGENT_GROUP_TYPE']['FILE'], Xphp::$_user['useruuid']);

        $name = $search['name'];
        $utils = Xphp::instance('Utils');
        $name = $utils->escapeWildcard($name);
        //按代理分组名模糊搜索
        if(!empty($name)){
            $sql .= " and bag.group_name like '%".$name."%'";
            $sqlCount .= " and bag.group_name like '%".$name."%'";
        }

        $sql .=" order by $sortArr[$sortColumn]  $sortType limit ? , ? ";

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array("data" => array());
        foreach ($data as $d){
            $detail = json_decode($d['detail'], true);
            $agentCount = count($detail['agent_list']);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'.$d['group_uuid'].'">',
                $d['group_name'],
                $d['remark'],
                $agentCount,
                date('Y-m-d H:i:s', $d['create_time']),
                $d['user_name'],
                $d['group_uuid']
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 添加代理分组
     * @param unknown $params
     * @return string
     */
    public function addAgentGroup($params){
        $groupname = $params['groupname'];
        $description = $params['description'];
        $agentList = $params['agentList'];
        $detail = array();
        $detail['agent_list'] = $agentList;
        $groupType = Xphp::$_config['AGENT_GROUP_TYPE']['FILE'];

        //创建分组唯一标识uuid
        $utils = Xphp::instance('Utils');
        $groupuuid = $utils->uuid();
        $createTime = date('Y-m-d H:i:s');

        $operate = Xphp::$_lang['WEB_AGENT_GROUP_ADD'];

        //插入代理分组记录
        $sqlParams = array($groupuuid, $groupname, $groupType, $description, $createTime, Xphp::$_user['useruuid'], json_encode($detail));
        $sql = "insert into bd_agent_group (group_uuid, group_name, group_type, remark, create_time, user_uuid, detail) values (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);
        if($result){
            return $this->muOpResult(true, $operate);
        }else{
            return $this->muOpResult(false, $operate, '', 'warning');
        }
    }


    /**
     * 修改代理分组
     * @param unknown $params
     * @return string
     */
    public function editAgentGroup($params){
        $groupuuid = $params['groupuuid'];
        $description = $params['description'];
        $agentList = $params['agentList'];
        $detail = array();
        $detail['agent_list'] = $agentList;

        $editTime = date('Y-m-d H:i:s');

        $operate = Xphp::$_lang['WEB_AGENT_GROUP_EDIT'];

        //更新代理分组记录
        $sqlParams = array($description, $editTime, json_encode($detail), $groupuuid);
        $sql = "update bd_agent_group set remark = ?, create_time = ?,  detail = ? where group_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);
        if($result){
            return $this->muOpResult(true, $operate);
        }else{
            return $this->muOpResult(false, $operate, '', 'warning');
        }

    }

    /**
     * 删除代理分组
     * @param unknown $params
     * @return string
     */
    public function deleteAgentGroup($params){
        $groupuuids = $params['uuids'];
        $groupuuidDes = implode("','", $groupuuids);
        //检查有没有代理分组的任务
        $this->checkGroupExistTask($groupuuidDes);
        $operate = Xphp::$_lang['WEB_AGENT_GROUP_DELETE'];
        $sql = "delete from bd_agent_group where group_uuid in ('".$groupuuidDes."')";
        $result = $this->dbExec($sql);
        if($result){
            return $this->muOpResult(true, $operate);
        }else{
            return $this->muOpResult(false, $operate, '', 'warning');
        }
    }

    /**
     * 获取代理分组修改信息
     * @param unknown $params
     * @return string
     */
    public function getAgentGroupEditInfo($params){
        $groupuuid = $params['groupuuid'];
        $sql = "select group_name, remark from bd_agent_group where group_uuid = ?";
        $data = $this->dbSelect($sql, array($groupuuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                'groupname' => $data[0]['group_name'],
                'description' => $data[0]['remark']
            );
        }

        return json_encode($info);
    }

    /**
     * 获取代理端节点树
     * @param unknown $params
     * @return string
     */
    public function getAgentSelectTree($params){
        $groupuuid = $params['groupuuid'];
        $agentList = $this->getOldAgentList($groupuuid);
        $editFlag = $params['editFlag'];
        $sql = "select agent_uuid, agent_name, hostname, ip, os_type, os_version, online_flag, register_flag, authorization_module from bd_agent where user_uuid = ? and agent_type = 0 ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $tree = array();
        $systemHandler = Xphp::instance('SystemHandler');
        $group_sql = "select detail,group_name from bd_agent_group where user_uuid = ?";
        $group_data = $this->dbSelect($group_sql, array(Xphp::$_user['useruuid']));
        if(!empty($data)){
            $checked = false;
            //如果是修改任务固定勾选上全部
            if($editFlag){
                $checked = true;
            }
            //全选
            $tree[] = array(
                "id" => 'all',
                "pId" => 0,
                "name" => Xphp::$_lang['UI_PUBLIC_ALL'],
                "name" => Xphp::$_lang['UI_PUBLIC_ALL'],
                "isParent" => true,
                "nocheck" => false,
                "open" => true,
                "type" => -1,
                "icon" => "",
                "checked" => $checked
            );
            foreach ($data as $d){
                $name = $d['agent_name'] == $d['ip'] ? $d['ip']."(". $d['hostname'] .")" : $d['ip']."(". $d['agent_name'] .")";
                $disable = false;
                $checkFlag = false;
                //如果已经有分组禁止选择
                if(!empty($group_data)) {
                    foreach($group_data as $g) {
                        if(in_array($d['agent_uuid'], json_decode($g["detail"],true)["agent_list"])) {
                            $disable = true;
                            $name .= "(".$g["group_name"].")";
                        }
                    }
                }
                //如果代理未授权禁止选择
                if(!($systemHandler->checkModuleValid($d['authorization_module'], 'file'))){
                    $disable = true;
                    $name .= "(".Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'].")";
                }
                //修改代理分组使用,选中添加时选中的代理
                if($editFlag && in_array($d['agent_uuid'], $agentList)){
                    $checkFlag = true;
                    $disable = false;
                }
                $node = array(
                    "id" => $d['agent_uuid'],
                    "pId" => 'all',
                    "name" => $name,
                    "title" => $name,
                    "isParent" => false,
                    "agentuuid" => $d['agent_uuid'],
                    "nocheck" => false,
                    "type" => 1,
                    "icon" => "./img/vm/host.png",
                    "module" => $this->getAgentModule($d['authorization_module']),
                    "chkDisabled" => $disable,
                    "checked" => $checkFlag
                );
                $tree[] = $node;
            }

        }

        return json_encode($tree);

    }


    /**
     * 获取文件备份
     * @return array
     */
    public function getAgentGroupBackupTree($params=array()){
        $nodes = array();
        $search = $params['content'];
        $utils = Xphp::instance('Utils');
        $search = $utils->escapeWildcard($search);
        $sql = "select ba.id, ba.agent_uuid,ba.os_type, ba.agent_name, ba.hostname, ba.ip, ba.os_type,online_flag, ba.authorization_module,bag.group_uuid,ba.net_model,  
        bag.group_name, bag.detail from bd_agent ba left join bd_agent_group bag on ba.group_uuid = bag.group_uuid where ba.agent_type not in (3, 4, 5)";
        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源列表
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到分配的资源
            $resourceUuidSql = $utils->v1_auth_get_source_by_type(10, 'ba.agent_uuid');
            $sqlNew = " and ({$resourceUuidSql}) ";
            $sql .= $sqlNew;
        }
        if(!empty($search)) {
            $sql .= " and (ba.agent_name like '%". $search ."%' or ba.hostname like '%". $search ."%' or ba.ip like '%". $search ."%' or bag.group_name like '%". $search ."%')";
        }
        $data = $this->dbSelect($sql);
        $systemHandler = Xphp::instance('SystemHandler');
        $group = array();
        $agent = array();
        if(!empty($data)){
            foreach ($data as $d){
                //分组
                if(!in_array($d['group_uuid'], $group)) {
                    //查分组下的agent
                    $sqlagent = "select agent_uuid from bd_agent where agent_type != 4 and group_uuid = ?";
                    $dataagent = $this->dbSelect($sqlagent, array($d['group_uuid']));
                    array_push($group,$d['group_uuid']);
                    $nodes[] = array(
                        "id" => $d['group_uuid'],
                        "pId" => 0,
                        "name" => htmlspecialchars_decode($d['group_name']),
                        "title" => htmlspecialchars_decode($d['group_name']),
                        "isParent" => true,
                        "nocheck" => true,
                        "open" => false,
                        "type" => 1,
                        "icon" => "./img/platform/flag.png",
                        "uuid" => $d['group_uuid'],
                        "eventtype" => "group",
                        "agentlist" => $dataagent[0],
                        "nocheck" => false,
                    );

                }
                //客户端
                $authorization = $systemHandler->checkModuleValid($d['authorization_module'], 'file');
                if($d['online_flag'] != 1) {
                    $authorization_online_info = '('. Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] .')';
                }else {
                    $authorization_online_info = '';
                }
                //客户端名  别名不为空并且不等于IP 显示别名+ip
                if (!empty($d['agent_name']) && $d['agent_name'] != $d['ip']) {
                    $name = $authorization_online_info . $d['agent_name']."(". $d['ip'] .")";
                }else {
                    $name = $authorization_online_info . $d['hostname']."(". $d['ip'] .")";
                }
                if(!in_array($d['agent_uuid'], $agent)){
                    array_push($agent,$d['agent_uuid']);
                    $nodes[] = array(
                        "id" => $d['group_uuid'].'_'.$d['agent_uuid'],
                        "pId" => $d['group_uuid'],
                        "name" => htmlspecialchars_decode($name),
                        "title" => $d['ip'],
                        "isParent" => false,
                        "uuid" => $d['agent_uuid'],
                        "nocheck" => false,
                        "type" => 2,
                        "icon" => $d['os_type'] == "Windows" ? "./img/os/Windows.png" : "./img/os/Linux.png",
                        "eventtype" =>  "agent",
                        "ostype" => $d['os_type'],
                        "chkDisabled" => $authorization_online_info == '('. Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] .')' ? true : false,
                        "authorization_module" => $authorization,
                        "net_model" => intval($d['net_model']),
                        //是否可以被选中
                        "isCheckFlag" => $authorization_online_info == '('. Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] .')' ? true : false,
                    );
                }
            }
        }
        //分组个数为1时，展开分组；多个分组时，不展开，避免客户端太多，看不到下面的分组
        if (count($group) == 1) {
            $nodes[0]['open'] = true;
        }
        return json_encode($nodes);
    }


    /**
     * 获取修改代理分组需要的代理列表
     * @param unknown $groupuuid
     * @return mixed[]
     */
    private function getOldAgentList($groupuuid){
        $sql = "select detail from bd_agent_group where group_uuid = ?";
        $data = $this->dbSelect($sql, array($groupuuid));
        $list = array();
        if(!empty($data)){
            $detail = json_decode($data[0]['detail'], true);
            $agentList = $detail['agent_list'];
            if(!empty($agentList)){
                foreach ($agentList as $d){
                    $list[] = $d;
                }
            }
        }

        return $list;
    }

    /**
     * 获取新建代理分组名
     * @return string
     */
    public function getAgentGroupName(){
        $groupName = Xphp::$_lang['WEB_AGENT_GROUP_TITLE'];
        $groupname = $this->getValidName($groupName);
        $info = array(
            'groupname' => $groupname
        );
        return json_encode($info);
    }

    /**
     * 获取可用的代理分组名
     * @param string $groupName  策略组名
     * @return string
     */
    public function getValidName($groupName){
        $oldName = $groupName;
        for($i=1; $i<1000; $i++){
            $groupName .= $i;
            $sql = "select group_uuid from bd_agent_group where group_name = ?";
            $data = $this->dbSelect($sql, array($groupName));
            if(empty($data)){
                return $groupName;
            }
            $groupName = $oldName;
        }
        return $oldName;
    }
    /**
     * 检测分组名是否已被使用
     * @param unknown $params
     * @return string
     */
    public function groupnameAvailable($params){
        $groupname = $params['name'];
        $sql = "select group_uuid from bd_agent_group where group_name = ? ";
        $sqlParams = array($groupname);
        $data = parent::dbSelect($sql, $sqlParams);
        return json_encode(empty($data));
    }


    /**
     * 检查是否有文件任务正在使用该代理分组
     * @param string $groupuuids 代理分组集合uuid字符串
     * @return boolean
     */
    private function checkGroupExistTask($groupuuids){
        $sql = "select task_uuid from fs_task where agent_group_uuid in ('".$groupuuids."')";
        $data = $this->dbSelect($sql);
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_DELETE'], Xphp::$_lang['WEB_AGENT_GROUP_DELETE_TASK_EXIST_ERROR'], "warning"));
        }

        return true;
    }

    /**
     * 修改代理检查是否有使用该代理的任务
     * @param unknown $agentuuid
     */
    private function checkAgentTaskExist($agentuuid){
        $sql = "select task_uuid from bd_task where agent_uuid = ? ";
        $data = $this->dbSelect($sql, array($agentuuid));
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_AGENT'], Xphp::$_lang['WEB_AGENT_GROUP_DELETE_TASK_EXIST_ERROR']));
        }
    }

    /**
     * 提供给客户端下载的接口
     * 例子:https://192.168.8.60/api/?m=9&f=downloadAgent&k=6e24cc40bfdb6963c04a4f1983c8af71&arch_type=x86&os_type=windows
     * 成功直接下载
     * 失败返回404
     * @param unknown $params
     */
    public function downloadAgent($params){
        //获取参数
        $arch_type = strtoupper($_GET['arch_type']);  //底层架构,和发布系统客户端代理对应
        $os_type = strtoupper($_GET['os_type']);      //操作系统架构,和发布系统客户端代理对应
        //检查参数
        $archTypeArr = Xphp::$_config['AGENT_ENVIRONMENT']['arch_type'];
        $osTypeArr = Xphp::$_config['AGENT_ENVIRONMENT']['os_type'];


        if(!in_array($arch_type, $archTypeArr) || !in_array($os_type, $osTypeArr)){
            //获取参数失败
            http_response_code(404);
            exit;
        }
        $agent = $this->getAllPakagesInfo();
        $filePath = $agent[$os_type]['fileName'];
        $showName = $agent[$os_type]['showName'];

        if(empty($filePath)){
            //没有找到对应的文件
            http_response_code(404);
            exit;
        }
        //组合绝对路径
        $filePath = dirname(Xphp::$_config['AGENT_PATH']) . $filePath;

        //通过统一的下载接口下载文件
        $systemHandler = Xphp::instance('SystemHandler');
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $url = $systemHandler->groupUnifyDownloadUrl($nodeuuid, $filePath,false,$showName);
        Header("HTTP/1.1 303 See Other");
        Header("Location: $url");
        exit;
    }

    /**
     * 组合下载链接
     * @param string 获取到的虚拟机版本/客户端版本的text
     * @retrun string 下载链接
     */
    public function getDownloadLink($params)
    {
        $vendor = require_once API_PATH.'xphp/conf/vendor.php';
        $agent = $this->getAllPakagesInfo();
        if($params['type'] == 0){
            //虚拟机代理
            //替换厂商和版本配置中的安装包路径
            $agentVendor = $vendor['AGENT_VENDOR'];
            $linkStr = '';
            foreach ($agentVendor as $k1 => $eachVendor){
                if($params['selectedText'] == $eachVendor['text']){
                    foreach ($eachVendor['version'] as $k2 => $version){
                        $value = $version['value'];
                        $text = $version['text'];
                        if($params['version'] == $text){
                            //如果是"#"就不替换,如果指定了安装包名就替换
                            $fileName = preg_replace('~^/[^/]+/~', '', $agent[$value]);
                            $return = array(
                                'linkStr' => $agent[$value],
                                'path' => '/usr/share/nginx/'.Xphp::$_config['SYSTEM_INFO']['vendor'],
                                'fileName' => $fileName,
                                'showName' => $fileName,
                            );
                        }
                    }
                }
            }
        }elseif ($params['type'] == 2){
            //文件代理
            //客户端插件
            $clientOs = $vendor['CLIENT_OS'];
            $clientVendor = array();
            foreach ($clientOs as $os){
                $temp = array();
                if(empty($os['version'])){
                    $temp['text'] = $os['text'];
                    $temp['value'] = $os['value'];
                    $clientVendor[] = $temp;
                }else{
                    foreach ($os['version'] as $version){
                        $temp['text'] = $version['text'];
                        $temp['value'] = $version['value'];
                        $clientVendor[] = $temp;
                    }
                }
            }
            foreach ($clientVendor as $k1 => $eachVendor){
                if($params['selectedText'] == $eachVendor['text']){
                    if('#' != $eachVendor['value']){
                        //如果是"#"就不替换,如果指定了安装包名就替换
                        if('#' === $agent[$eachVendor['value']]){
                            $return = array(
                                'linkStr' => '#',
                                'path' => '',
                                'showName' => '',  //下载后显示的文件名
                            );
                        }else{
                            $showName = $agent[$eachVendor['value']]['showName'];
                            $return = array(
                                'linkStr' => $agent[$eachVendor['value']]['fileName'],
                                'path' => '/usr/share/nginx/'.Xphp::$_config['SYSTEM_INFO']['vendor'],
                                'showName' => $showName,  //下载后显示的文件名
                            );
                        }
                    }
                }
            }
        }elseif ($params['type'] == 4) {
            //数据库实时插件
            $dbcdpVendor = $vendor['DBREALTIME_CLIENT'];
            foreach ($dbcdpVendor as $k1 => $eachVendor) {
                if ($params['selectedText'] == $eachVendor['text']) {
                    if ('#' != $eachVendor['value']) {
                        //如果是"#"就不替换,如果指定了安装包名就替换
                        $fileName = preg_replace('~^/[^/]+/~', '', $agent[$eachVendor['value']]);
                        $return = array(
                            'linkStr' => $agent[$eachVendor['value']],
                            'path' => '/usr/share/nginx/'.Xphp::$_config['SYSTEM_INFO']['vendor'],
                            'fileName' => $fileName,
                            'showName' => $fileName,
                        );
                    }
                }
            }
        }
        return json_encode($return);
    }

}
?>