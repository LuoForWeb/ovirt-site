<?php

/*******************************************
 ** 客户端管理处理类
 **
 ** @author       luokai@vinchin.com
 ** @date         2021-12-28 15:05:44
 ** @version      1.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
class ClientHandler extends OPHandler
{
    private $opcodeHandler;

    function __construct()
    {
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('NodeOpcode');
    }

    /**
     * 添加客户端
     * @param unknown $params
     * @return string
     */
    public function addClient($params)
    {
        $msg = array();
        $addType = intval($params['addType']);
        $agentInfo = $this->groupAgentInfo($params['agentInfo'], $params['agentType']);
        $addMode = intval($params['addMode']);
        //检查IP地址是否符合规则
        $this->checkAgentIpRules($agentInfo);
        $length = sizeof($agentInfo);
        //检查客户端是否存在
        $agentInfo = $this->checkAgentExist($agentInfo, $addType, $params['agentType']);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $nodeIpList = $nodeHandler->getNodeIPAddrMap(array('nodeuuid' => $nodeuuid));
        if (count($nodeIpList['ipv4_list'])) {
            $serverIp = $nodeIpList['ipv4_list'][0];
        } else {
            $serverIp = $nodeIpList['ipv6_list'][0];
        }
        if ($addMode == 2) {  // 远程部署要根据IP地址类型传IPv4或IPv6
            if (1 == count($agentInfo)) {
                if (
                    filter_var($agentInfo[0]['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) &&
                    count($nodeIpList['ipv6_list'])
                ) {
                    $serverIp = $nodeIpList['ipv6_list'][0];
                }
            }
        }
        //定义操作名
        $opcodeName = 'NODE_AGENT_OP_ADD';
        //组合消息
        $msg = array(
            'agent_info_list' => $agentInfo,    //添加客户端信息
            'server_ip' => $serverIp,  //备份系统IP
            'server_port' => $params['serverport'],  //备份系统端口
            'add_mode' => $addMode    //添加方式 1手动添加 2远程部署
        );
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $exMsg = Xphp::$_lang['WEB_GROUP_CLIENT_EXIST_TIPS'];
        $msg = sizeof($agentInfo) == $length ? $mbResult['msg'] : $exMsg;
        //返回结果到UI
        if ($result) {
            // 设置添加客户端的所有者
            $agentIpList = array_column($agentInfo, 'ip');
            $agentIps = "'" . implode("', '", $agentIpList) . "'";
            $userUuid = Xphp::$_user['useruuid'];
            $sql = "UPDATE bd_agent SET user_uuid = ? WHERE ip in ($agentIps) ";
            $this->dbExec($sql, [$userUuid]);
            return $this->muOpResult($result, $operate, $msg);
        } else {
            //添加超时重定义信息返回
            if ($mbResult['errorCode'] == 51505) {
                return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_CLIENT_ADD_TIMEOUT_TIPS'], 'info');
            }
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 修改客户端
     * @param unknown $params
     * @return string
     */
    public function editClient($params)
    {
        $msg = array();
        $agentuuid = $params['agentuuid'];
        $ip = $params['ip'];
        $nickname = $params['nickname'];
        $port = $params['port'];
        $netmodel = $params['net_model'];
        //定义操作名
        $opcodeName = 'NODE_AGENT_OP_MODIFY';
        //组合消息
        $msg = array(
            'agent_uuid' => $agentuuid,
            'ip' => $ip,
            'net_model' => $netmodel,
            'port' => $port,
            'nickname' => $nickname,
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除客户端
     * @param unknown $params
     * @return string
     */
    public function deleteClient($params)
    {
        $uuids = $params['uuids'];
        // 检查是否关联有组织(office 365)
        $this->checkClientOrganizationExist($uuids);
        //检查是否有任务存在
        $this->checkClientTaskExist($uuids);
        $pluginFlag = $params['plugin_flag'];
        $msg = array();
        //定义操作名
        $opcodeName = 'NODE_AGENT_OP_DEL';
        //组合消息
        $msg = array(
            'agent_uuid_list' => $uuids,
            'uninstall_plugin_flag' => (int)$pluginFlag,
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 授权客户端(可多选)
     * @param unknown $params
     * @return string
     */
    public function authClient($params)
    {
        $flag = $params['flag'];    //添加/取消标记
        $authModule = $params['authmodule'];
        $list = $params['clientlist'];
        $editFlag = $params['editFlag'];    //修改授权
        $agentHandler = Xphp::instance('AgentHandler');
        $pfOpcode = Xphp::instance('PFOpcodePrivate');
        if ($flag) {
            $opName = "PT_LICENSE_OP_ADD_AGENT_LICENSE"; //添加授权
            $this->checkAuthEnough($list, $authModule, $flag); //检查授权是否足够
        } else {
            $opName = "PT_LICENSE_OP_MODIFY_AGENT_LICENSE";    //取消授权
        }
        $operate = $pfOpcode->getOpcodeDes($opName);
        if ($flag) {
            //检查系统授权是否正常，不正常不允许执行授权操作
            $this->checkSystemAuthStatus($operate);
        }

        //合并授权信息
        foreach ($list as $key => $d) {
            $list[$key]['newmodule'] = $list[$key]['authmodule'];
            if ($d['authmodule']['file']) {
                if (!$flag) {
                    $list[$key]['authmodule']['file'] = false;
                } else {
                    $list[$key]['authmodule']['file'] = true;
                }
            } else {
                $list[$key]['authmodule']['file'] = $list[$key]['oldmodule']['file'];
            }

            if ($d['authmodule']['database']) {
                if (!$flag) {
                    $list[$key]['authmodule']['database'] = false;
                } else {
                    $list[$key]['authmodule']['database'] = true;
                }
            } else {
                $list[$key]['authmodule']['database'] = $list[$key]['oldmodule']['database'];
            }

            if ($d['authmodule']['os']) {
                if (!$flag) {
                    $list[$key]['authmodule']['os'] = false;
                } else {
                    $list[$key]['authmodule']['os'] = true;
                }
            } else {
                $list[$key]['authmodule']['os'] = $list[$key]['oldmodule']['os'];
            }

            if ($d['authmodule']['cdp']) {
                if (!$flag) {
                    $list[$key]['authmodule']['cdp'] = false;
                } else {
                    $list[$key]['authmodule']['cdp'] = true;
                }
            } else {
                $list[$key]['authmodule']['cdp'] = $list[$key]['oldmodule']['cdp'];
            }

        }

        $result = true; //进行批量授权
        foreach ($list as $d) {
            $info = array(
                'agentUUID' => $d['agentuuid'],
                'userUUID' => Xphp::$_user['useruuid'],
                'hostName' => $d['hostname'],
                'module' => $d['authmodule'],
                'newmodule' => $d['newmodule']
            );
            $result = $result && $agentHandler->registAndEditAgent($opName, $info);
        }

        //返回结果到UI
        return $this->muOpResult($result, $operate);

    }

    /**
     * 手动刷新客户端
     * @param unknown $params
     * @return string
     */
    public function refreshClient($params)
    {
        $agentuuid = $params['agentuuid'];

        $msg = array();
        //定义操作名
        $opcodeName = 'NODE_AGENT_OP_REFRESH';
        //组合消息
        $msg = array(
            'agent_uuid' => $agentuuid,
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 分配客户端
     * @param unknown $params
     * @return string
     */
    public function allocationClient($params)
    {
        $agentUuidList = $params['agent_uuid_list'];
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
        $useruuid = $params['useruuid'];
        $sql = "update bd_agent set user_uuid = ? where agent_uuid IN ($agentUuids) ";
        $result = $this->dbExec($sql, array($useruuid));
        //定义操作名
        $opcodeName = 'NODE_AGENT_OP_ALLOCATION';
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate);
        } else {
            return $this->muOpResult($result, $operate, '', 'warning');
        }
    }

    /**
     * 升级客户端(可多选)
     * @param array $params
     * @return string
     */
    public function upgradeClient(array $params)
    {
        $upgradeData = $params['upgrade_data'];

        $msg = [
            'agent_info' => [],
        ];
        foreach ($upgradeData as $item) {
            $msg['agent_info'][] = [
                'agent_uuid' => $item['agent_uuid'],
                'agent_path' => $item['agent_path'],
                'agent_version' => $item['agent_version'],
            ];
        }

        //定义操作名
        $opcodeName = 'NODE_AGENT_OP_UPGRADE';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到客户端管理客户端列表信息
     * @param unknown $params
     * @return string
     */
    public function getClientInfo($params)
    {
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortParams = [
            0 => '', 'ba.ip', 'ba.hostname ' . $sortType . ', ba.agent_name',
            3 => 'ba.os_version', 'ba.authorization_module', 'baa.app_name',
            6 => 'ba.plugin_version', 'ba.agent_type', 'ba.register_time',
            9 => 'ba.online_flag', 'bu.user_name',
        ];

        $sql = "select ba.agent_uuid, ba.agent_name, ba.os_type, ba.hostname, ba.ip, ba.os_version, ba.detail,
                        ba.process_type, ba.agent_username, ba.agent_password, ba.net_model, ba.agent_os_type, ba.port,  ba.agent_type, 
                		ba.client_transport_port, unix_timestamp(ba.register_time) AS register_time, ba.plugin_deploy_status,
                        ba.online_flag, ba.authorization_module, ba.plugin_version, bu.user_name, bu.user_uuid 
                from bd_agent ba 
                    left join  bd_user bu on ba.user_uuid = bu.user_uuid
                    left join (select agent_uuid, group_concat(app_name) as app_name from bd_agent_app group by agent_uuid) baa ON baa.agent_uuid=ba.agent_uuid
                where ba.agent_type != 3 ";
        $sqlCount = "select count(ba.id) as total
                from bd_agent ba 
                    left join  bd_user bu on ba.user_uuid = bu.user_uuid
                    left join (select agent_uuid, group_concat(app_name) as app_name from bd_agent_app group by agent_uuid) baa ON baa.agent_uuid=ba.agent_uuid
                where ba.agent_type != 3 ";
        $sqlParams = array();
        $sqlCountParams = array();
        $utils = Xphp::instance('Utils');
        $accurateFlag = $params['accurateFlag'];
        $setFlag = Xphp::$_config['FLAG'];
        $deployFlag = Xphp::$_config['AGENT_DEPLOY_STATUS'];
        if ($accurateFlag) {
            $search = $params['search'];

            // 添加时间
            $startTime = $search['start_time'];
            $endTime = $search['end_time'];
            if ($this->checkEmpty($startTime) && $this->checkEmpty($endTime)) {
                $startTime = strtotime($startTime);
                $endTime = strtotime($endTime);
                $sql .= " AND UNIX_TIMESTAMP(ba.register_time) BETWEEN $startTime AND $endTime ";
                $sqlCount .= " AND UNIX_TIMESTAMP(ba.register_time) BETWEEN $startTime AND $endTime ";
            }

            // ip
            $ip = $utils->escapeWildcard($search['ip']);
            if ($this->checkEmpty($ip)) {
                $sql .= " AND ba.ip LIKE ? ";
                $sqlCount .= " AND ba.ip LIKE ? ";
                $sqlParams = array_merge($sqlParams, ["%$ip%"]);
                $sqlCountParams = array_merge($sqlCountParams, ["%$ip%"]);
            }

            // 主机名
            $hostname = $utils->escapeWildcard($search['hostname']);
            if ($this->checkEmpty($hostname)) {
                $sql .= " AND ba.hostname LIKE ? ";
                $sqlCount .= " AND ba.hostname LIKE ? ";
                $sqlParams = array_merge($sqlParams, ["%$hostname%"]);
                $sqlCountParams = array_merge($sqlCountParams, ["%$hostname%"]);
            }

            // 别名
            $alias = $utils->escapeWildcard($search['alias']);
            if ($this->checkEmpty($alias)) {
                $sql .= " AND ba.agent_name LIKE ? ";
                $sqlCount .= " AND ba.agent_name LIKE ? ";
                $sqlParams = array_merge($sqlParams, ["%$alias%"]);
                $sqlCountParams = array_merge($sqlCountParams, ["%$alias%"]);
            }

            // 操作系统
            $osVersion = $utils->escapeWildcard($search['os_version']);
            if ($this->checkEmpty($osVersion)) {
                $sql .= "AND ba.os_version LIKE ? ";
                $sqlCount .= "AND ba.os_version LIKE ? ";
                $sqlParams = array_merge($sqlParams, ["%$osVersion%"]);
                $sqlCountParams = array_merge($sqlCountParams, ["%$osVersion%"]);
            }

            // 授权模块
            // 系统没有授权模块的定义, 因此在这里做简单定义
            $modules = [
                1 => 'file',
                2 => 'database',
                3 => 'os',
            ];
            $authModule = (int)$search['auth_module'];
            if ($authModule && isset($modules[$authModule])) {
                //  采用模糊匹配, 即匹配 "file":true / "database":true / "os":true
                $sql .= " AND ba.authorization_module LIKE '%\"$modules[$authModule]\":true%' ";
                $sqlCount .= " AND ba.authorization_module LIKE '%\"$modules[$authModule]\":true%' ";
            }

            // 应用配置
            $appName = $utils->escapeWildcard($search['app_name']);
            if ($this->checkEmpty($appName)) {
                // 采用group_concat的模糊搜索
                $sql .= " AND baa.app_name LIKE ? ";
                $sqlCount .= " AND baa.app_name LIKE ? ";
                $sqlParams = array_merge($sqlParams, ["%$appName%"]);
                $sqlCountParams = array_merge($sqlCountParams, ["%$appName%"]);
            }

            // 状态
            $status = explode('-', $search['status']);
            if (count($status) == 2) {  // 这里必须等于2才进行查询
                $onlineStatus = (int)$status[0];
                $deployStatus = (int)$status[1];
                if (in_array($onlineStatus, $setFlag) && in_array($deployStatus, $deployFlag)) {  // 需要枚举值都会才进行筛选
                    $sql .= " AND ba.online_flag = $onlineStatus AND ba.plugin_deploy_status = $deployStatus ";
                    $sqlCount .= " AND ba.online_flag = $onlineStatus AND ba.plugin_deploy_status = $deployStatus  ";
                }
            }

            // 所有者
            $owner = $utils->escapeWildcard($search['owner']);
            if ($this->checkEmpty($owner)) {
                $sql .= " AND bu.user_name LIKE ? ";
                $sqlCount .= " AND bu.user_name LIKE ? ";
                $sqlParams = array_merge($sqlParams, ["%$owner%"]);
                $sqlCountParams = array_merge($sqlCountParams, ["%$owner%"]);
            }
        } else {
            //根据主机名搜索
            $search = $params['search'];
            $hostName = $search['name'];
            $hostName = $utils->escapeWildcard($hostName);

            if ($this->checkEmpty($hostName)) {
                $sql .= " and (ba.hostname like ? or ba.ip like ? or ba.agent_name like ?) ";
                $sqlCount .= " and (ba.hostname like ? or ba.ip like ? or ba.agent_name like ?) ";
                $sqlParams = array_merge($sqlParams, array('%' . $hostName . '%', '%' . $hostName . '%', '%' . $hostName . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $hostName . '%', '%' . $hostName . '%', '%' . $hostName . '%'));
            }
        }

        $agentuuidArr = $this->getClientUuids(0);
        if (empty($agentuuidArr)) {
            // 表示没得数据
            $sql .= " and ba.user_uuid = ''";
            $sqlCount .= " and ba.user_uuid = ''";
        } else {
            if (is_array($agentuuidArr)) {
                $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
                $sql .= " and ba.agent_uuid in {$agentuuidArrStr} ";
                $sqlCount .= " and ba.agent_uuid in {$agentuuidArrStr} ";
            }
        }

        $sql .= " order by $sortParams[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $total = intval($count[0]['total']);
        //如果是租户内
//        if(!empty($_SESSION['tenantuuid'])){
//            $search['accurateFlag'] = $accurateFlag;
//            $resourceHandler = Xphp::instance('ResourceHandler');
//            $resourceInfo = $resourceHandler->pGetUserResourceFileHost(Xphp::$_user['useruuid'], "ba.id", $sortType, $start, $length, $search);
//            $data = $resourceInfo['data'];
//            $total = intval($resourceInfo['total']);
//        }
        $records = array("data" => array());
        //应用配置
        $appList = $this->getApplicationList();
        $op = [1, 2, 3, 4, 5];  //1 应用配置  2.修改授权 3 刷新客户端 4详情 5日志下载
        if (!in_array('p_agent_manager_application_config', $_SESSION['permissionArr'])) {
            // 没有应用配置权限
            $op = array_values(array_diff($op, [1, 3]));
        }
        if (!in_array('p_agent_log_download', $_SESSION['permissionArr'])) {
            // 没有日志下载权限
            $op = array_values(array_diff($op, [5]));
        }
        foreach ($data as $d) {
            $info = $this->getClientDetails($d, $appList);  //获取客户端详情信息
            $agentuuid = $d['agent_uuid'];
            $detail = json_decode($d['detail'], true);
            $ip = $d['ip'];

            // 需要查询下当前的资源的使用者是谁
            $userName = $this->getClientUuidName($d['agent_uuid'], $info['username']);
            if ('admin' == $userName && $_SESSION['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $userName = 'sysadmin';
            }
            $creator = $info['username'];
            if ('admin' == $creator && $_SESSION['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $creator = 'sysadmin';
            }

            if (Xphp::$_config['AGENT_TYPE']['APPLIANCE'] == $d['agent_type']) {
                // 传输代理没有应用配置
                $op = array_values(array_diff($op, [1]));
            }

            $records["data"][] = array(
                '<input type="checkbox" value="' . $agentuuid . '">',
                $ip,
                $info['hostname'] . '/' . $info['nickname'],
                $info['os_version'],
                $info['agent_type_des'],
                $info['module'],
                $info['appdes'],
                $info['plugin_version'],
                $info['register_time'],
                $info['statusdes'],
                $creator,
                $userName,
                $op,
                $info,
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return json_encode($records);
    }

    /**
     * 组装修改客户端需要显示信息
     * @param unknown $d
     * @return number[]|unknown[]|mixed[]
     */
    private function getClientDetails($d, $appList = array())
    {
        $agentuuid = $d['agent_uuid'];
        $detail = json_decode($d['detail'], true);
        //应用配置描述
        $appDes = "";
        foreach ($appList as $app) {
            if ($agentuuid != $app['agent_uuid']) continue;
            $appDes .= $app['app_name'] . "(" . Xphp::$_config['DB_TYPE_DES'][intval($app['app_type'])] . ")" . PHP_EOL;
        }
        //未获取到应用配置
        if (empty($appDes)) {
            $appDes = Xphp::$_config['NULLSPACE'];
        }
        $ip = $d['ip'];

        $ipList = $detail['nic_list'];
        $networkList = array();
        foreach ($ipList as $i) {
            $list = $i['ip_set'];
            $ip = "";
            $netmask = "";
            foreach ($list as $net) {
                if ($net['ip_addr'] != "127.0.0.1") {
                    $ip .= $net['ip_addr'] . PHP_EOL;
                    $netmask .= $net['netmask'] . PHP_EOL;
                }
            }
            $networkList[] = array(
                'network_name' => $i['name'],
                'ip' => $ip,
                'mac' => $i['mac_address'],
                'netmask' => $netmask,
                'gateway' => $i['gateway_address']
            );
        }

        $info = array(
            // 'os_type' => Xphp::$_config['AGENT_OS_TYPE_DES'][intval($d['agent_os_type'])],
            // NOTE 2023-04-26: bg_agent的agent_os_type的值有问题，数值混乱，还有的为32675
            'os_type' => $d['os_type'],
            'ip' => $d['ip'],
            'hostname' => !empty($d['hostname']) ? $d['hostname'] : Xphp::$_config['NULLSPACE'],
            'nickname' => !empty($d['agent_name']) ? $d['agent_name'] : Xphp::$_config['NULLSPACE'],
            'username' => !empty($d['user_name']) ? $d['user_name'] : Xphp::$_config['NULLSPACE'],
            'password' => $d['agent_password'],
            'net_model' => intval($d['net_model']),
            'net_model_des' => intval($d['net_model']) == 1 ? Xphp::$_lang['UI_CLIENT_SERVER_TO_CLIENT'] : Xphp::$_lang['UI_CLIENT_CLIENT_TO_SERVER'],
            'port' => intval($d['port']),
            'agentuuid' => $d['agent_uuid'],
            'module' => !empty($d['authorization_module']) ? $d['authorization_module'] : "",
            'status' => $this->getClientStatus(intval($d['online_flag']), intval($d['plugin_deploy_status'])),
            'client_port' => intval($d['client_transport_port']),
            'statusdes' => $this->getAgentStatusDes(intval($d['online_flag']), intval($d['plugin_deploy_status'])),
            'os_version' => !empty($d['os_version']) ? $d['os_version'] : Xphp::$_config['NULLSPACE'],
            'plugin_version' => !empty($d['plugin_version']) ? $d['plugin_version'] : Xphp::$_config['NULLSPACE'],
            'register_time' => $this->parseDate($d['register_time']),
            'appdes' => $appDes,
            'network_list' => $networkList,
            'useruuid' => $d['user_uuid'],
            'agent_type' => $d['agent_type'],
            'agent_type_des' => $d['agent_type'] == Xphp::$_config['AGENT_TYPE']['APPLIANCE'] ? Xphp::$_lang['UI_CLIENT_AGENT_TYPE_TRANSPORT'] : Xphp::$_lang['UI_CLIENT_AGENT_TYPE_CLIENT'],
            'operations' => $d['agent_type'] == Xphp::$_config['AGENT_TYPE']['APPLIANCE'] ? [2, 3, 4] : [1, 2, 3, 4],
        );
        return $info;
    }

    /**
     * 得到代理端状态的描述(管理员)
     * @param int $onlineFlag
     * @param int $deployFlag
     */
    public function getAgentStatusDes($onlineFlag, $deployFlag)
    {
        //在线离线
        $des = Xphp::$_pfdes['ONLINEDES'][$onlineFlag];
        if ($deployFlag != 0) {
            $des .= "(" . Xphp::$_pfdes['AGENT_DEPLOY_STATUS'][$deployFlag] . ")";
        }

        return $des;
    }


    /**
     * 得到代理端授权模块
     * @param string $authModule
     */
    public function getAgentModule($authModule)
    {
        $module = array();
        if (empty($authModule)) {
            return $module;
        }
        $authModule = json_decode($authModule, true);
        $moduleKey = array("file", "mysql", "oracle", "sqlserver", "dm", "database");
        foreach ($moduleKey as $key) {
            if ($authModule[$key]) {
                $module[] = true;
            } else {
                $module[] = false;
            }
        }
        return $module;
    }

    /**
     * 上传客户端excel文件获取批量添加参数
     * @return string
     */
    public function getBatchClientInfo()
    {
        $key = file_get_contents($_FILES['files']['tmp_name']);
        include_once("../tools/PHPExcel.php");
        $objPHPExcel = new \PHPExcel();

        //初始化返回列表
        $excelInfo = array();

        $reader = \PHPExcel_IOFactory::load($_FILES['files']['tmp_name']);
        $objInfo = $reader->getWorksheetIterator();
        $list = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
        foreach ($objInfo as $sheet) {
            $sheetInfo = $sheet->getRowIterator();
            foreach ($sheetInfo as $row) {
                //确定从第几行开始读取
                if ($row->getRowIndex() < 3) continue;
                $rowInfo = $row->getCellIterator();
                $data = array();
                $flag = false;    //检查是否读取的数据满足加载，排除null
                foreach ($rowInfo as $key => $cell) {    //逐列读取
                    if (!in_array($key, $list)) continue;
                    $data[] = $cell->getValue(); //获取cell中数据
                    if (!$cell->getValue()) {
                        $flag = true;
                    }
                }
                if ($flag) continue;
                $excelInfo[] = array(
                    'ip' => $data[0],
                    'os_type' => $data[1],
                    'admin_name' => $data[2],
                    'password' => $data[3],
                    'nickname' => (string)$data[4],
                    'port' => $data[6],
                    'net_model' => $data[5],
                    'client_transport_port' => 23101    //传输端口改为默认23101
                );
            }
        }
        return json_encode($excelInfo);
    }


    /**
     * 获取批量添加客户端列表
     * @param unknown $params
     * @return string
     */
    public function getBatchClientTable($params)
    {
        $list = $params['list'];
        $records = array("data" => array());
        foreach ($list as $d) {
            $records["data"][] = array(
                $d['ip'],
                $d['os_type'],
                Xphp::$_lang['WEB_CLIENT_WAIT_TO_ADD'],
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = count($list);
        $records["recordsFiltered"] = count($list);

        return json_encode($records);
    }

    private function getClientPackages()
    {

        $clientVendor = $this->getClientVendor();
        $agentHandler = Xphp::instance('AgentHandler');
        $packageList = $agentHandler->getAllPakagesInfo();
        $ret = [];
        foreach ($clientVendor as $item) {
            $ret[$item['value']] = $packageList[$item['value']];
        }
        return $ret;
    }

    /**
     * 判断代理是否有正在运行的任务
     * @param $agentUuid
     * @return boolean
     */
    private function checkAgentIsRunningTask($agentUuid): bool
    {
        $allTaskStatus = Xphp::$_config['TASKSTATUS'];
        // 查找任务
        $sql = "SELECT bt.task_status FROM bd_task_agent_list btal
                    LEFT JOIN bd_task bt ON btal.task_uuid = bt.task_uuid
                WHERE btal.agent_uuid = ? ";
        $data = $this->dbSelect($sql, [$agentUuid]);
        // 任务处于运行、临界值将不能升级
        if (!$data || !is_array($data)) {
            return true;
        }
        foreach ($data as $item) {
            if (
                $item['task_status'] == $allTaskStatus['RUNNING'] ||
                $item['task_status'] == $allTaskStatus['STOPPING'] ||
                $item['task_status'] == $allTaskStatus['STARTING']
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取升级客户端列表
     * @param unknown $params
     */
    public function getUpgradeClientTable($params)
    {
        $list = $params['list'];
        $clientStr = implode("','", $list);
        $sql = "select ip, os_version, online_flag, plugin_deploy_status, agent_uuid, plugin_version,
                    agent_type, agent_package_type
                from bd_agent
                where agent_uuid in ('" . $clientStr . "') AND agent_type != ? ";
        $allAgentType = Xphp::$_config['AGENT_TYPE'];
        $data = $this->dbSelect($sql, [$allAgentType['APPLIANCE']]);
        $clientPackages = $this->getClientPackages();
        $utils = Xphp::instance('Utils');
        $clientDes = include_once APP_PATH . 'client/ClientDescription.php';
        $allAgentPackageTypeMap = $clientDes['AGENT_PACKAGE_TYPE_MAP'];

        $records = array("data" => array());
        foreach ($data as $d) {
            $upgradeFlag = true;
            $upgradeDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_YES'];

            $packagePath = $clientPackages[$allAgentPackageTypeMap[$d['agent_package_type']]];
            $packageVersion = '--';
            if ($packagePath) {
                if (preg_match('/.*backup-agent-(.*)-AGENT.*/', $packagePath, $matches)) {
                    $packageVersion = $matches[1];
                } elseif (preg_match('/.*windows\.(.*)\.exe/', $packagePath, $matches)) {
                    $packageVersion = $matches[1];
                }
            } else {
                $upgradeFlag = false;
            }
            // 升级版本比较
            if ($packageVersion <= $d['plugin_version']) {
                // $currentVersions = implode('.', $d['plugin_version']);
                // $targetVersions = implode('.', $packageVersion);
                $upgradeFlag = false;
            }
            // 在线离线判断
            if (!$utils->parseFlagToBool($d['online_flag'])) {
                $upgradeFlag = false;
            }
            // 任务判断
            if (!$this->checkAgentIsRunningTask($d['agent_uuid'])) {
                $upgradeFlag = false;
            }

            // 构建check
            $checkEle = "<input type='checkbox' value='{$d['agent_uuid']}' />";
            $href = "<a href='$packagePath' target='_blank'>$packageVersion</a>";
            if (!$upgradeFlag) {
                $checkEle = "<input type='checkbox' value='' disabled />";
                $upgradeDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_NO'];
                $href = $packageVersion;
            }
            $records["data"][] = array(
                $checkEle,
                $d['ip'],
                $d['os_version'],
                $d['plugin_version'],
                $href,
                $upgradeDes,
                Xphp::$_pfdes['ONLINEDES'][$d['online_flag']],
                [
                    'package_path' => ROOT_PATH . $packagePath,
                    'agent_uuid' => $d['agent_uuid'],
                    'agent_type' => intval($d['agent_type']),
                    'upgrade_flag' => $upgradeFlag,
                    'agent_version' => $packageVersion,
                ],
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = count($list);
        $records["recordsFiltered"] = count($list);

        return json_encode($records);
    }


    /**
     * 根据客户端 uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $agentUuid 客户端uuid
     * @return string
     */
    private function getClientUuidName(string $agentUuid, string $userName = '')
    {
        $sqlParms = [$agentUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 10 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql2, $sqlParms);
        if (empty($array)) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 10";
            $array = $this->dbSelect($sql, $sqlParms);
        }

        if (!empty($array)) {
            $sql = "select user_name from bd_user where user_uuid = ?";
            $data = $this->dbSelect($sql, [$array[0]['user_uuid']]);
            $userName = $data[0]['user_name'];
        }

        return $userName;
    }

    /**
     * 获取所有租户的所有客户端 资源
     * @return array|boolean
     */
    private function getAllTenantAgentResource()
    {
        $sql = "SELECT ba.agent_uuid
                FROM bd_agent ba
                    LEFT JOIN mt_user_tenant mut ON ba.user_uuid = mut.user_uuid
                WHERE (mut.tenant_uuid = '' OR mut.tenant_uuid is null) ";

        $tenantAgentData = $this->dbSelect($sql);
        if (is_array($tenantAgentData) && count($tenantAgentData)) {
            return array_column($tenantAgentData, 'agent_uuid');
        }
        return true;
    }

    /**
     * 获取客户端的uuid集合
     * 1, 用户本身未分配的客户端
     * 2，分配给谁使用的客户端
     * 其中分配的又包括资源和资源组两种情况
     * @param int $type 类型0是查看的（包括下级管理用户的） 1表示操作要用的(创建任务这些)
     * @return array|boolean
     */
    public function getClientUuids($type = 1)
    {
        if (Xphp::$_user['useruuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {  // 超级管理员不能查看租户添加的客户端
            return $this->getAllTenantAgentResource();
        }
        //如果是超级管理员、本地/管理员、sysadmin、safeadmin展示所有的客户端
        if ($this->judgeShowAllAgent()) {
            return true;
        }
        // 如果是三权模式并且是系统管理员也显示所有
        $userUuid = [Xphp::$_user['useruuid']];
        if (!$type) {
            // 关联管理用户判断 存储资源 - 查看  resmanagement_look
            $authUser = $_SESSION['authUser']['resmanagement_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $userUuid = array_merge($userUuid, $authUser);
            }
        }

        $userUuidStr = "'" . implode("','", $userUuid) . "'";
        // resource_type 为 10
        $return = [];
        // 创建者的客户端uuid
        $sql = "select agent_uuid from bd_agent where user_uuid in ( {$userUuidStr} )";
        $data1 = $this->dbSelect($sql);
        if (!empty($data1)) {
            $array = array_column($data1, 'agent_uuid');
            $arrayStr = "'" . implode("','", $array) . "'";
            // 根据创建者的客户端uuid去查询是否有分配到资源/组给其它用户
            $sql2 = "select resource_uuid from mt_user_resource where resource_type = 10 and resource_uuid in ( {$arrayStr} )";
            $array2 = $this->dbSelect($sql2);
            $array2 = !empty($array2) ? array_column($array2, 'resource_uuid') : [];

            // 资源组的创建不算，要分配了的才能不算
            $sql3 = "select mrrg.resource_uuid from mt_resource_resource_group mrrg
                    join mt_user_resource_group murg on murg.resource_group_uuid = mrrg.resource_group_uuid
                    where mrrg.resource_type = 10 and mrrg.resource_uuid in ( {$arrayStr} )";
            $array3 = $this->dbSelect($sql3);
            $array3 = !empty($array3) ? array_column($array3, 'resource_uuid') : [];

            // 计算差集
            $arrays = array_unique(array_merge($array2, $array3));
            $return = array_diff($array, $arrays);
        }

        // 分配的资源
        $sql = "select resource_uuid from mt_user_resource where resource_type = 10 and user_uuid in ( {$userUuidStr} )";
        $data1 = $this->dbSelect($sql);
        $array1s = !empty($data1) ? array_column($data1, 'resource_uuid') : [];
        $return = array_merge($return, $array1s);

        // 分配的资源组资源
        $sql = "select mrrg.resource_uuid from mt_resource_resource_group mrrg
                    join mt_user_resource_group murg on murg.resource_group_uuid = mrrg.resource_group_uuid
                    where mrrg.resource_type = 10 and murg.user_uuid in ( {$userUuidStr} )";
        $data2 = $this->dbSelect($sql);
        $array2s = !empty($data2) ? array_column($data2, 'resource_uuid') : [];
        return array_merge($return, $array2s);
    }

    /**
     * 判断是否显示所有客户端
     * @return boolean
     */
    private function judgeShowAllAgent(): bool
    {
        /**
         * 1. 超级管理员展示所有的客户端
         * 2. 三权模式并且是系统管理员、安全管理员显示所有客户端
         */
        // admin用户
        $showAllAgent = Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9";
        // admin类别
        $showAllAgent = $showAllAgent || Xphp::$_user['usertype'] == Xphp::$_config['USER_TYPES']['USER_ADMIN'];
        // 三权模式
        if ($_SESSION['isThreePowers']) {
            $showAllAgent = $showAllAgent || $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin'];
            $showAllAgent = $showAllAgent || $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['safeadmin'];
        } else {  // 非三权模式的普通用户只显示自己拥有的客户端
            // $showAllAgent = $showAllAgent || false;
        }
        return $showAllAgent;
    }

    /**
     * 获取客户端分组的客户端列表信息
     * @param unknown $params
     * @return string
     */
    public function getGroupClientList($params)
    {
        $groupuuid = $params['groupuuid'];
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortParams = array('', 'ba.hostname', 'ba.ip', 'ba.os_version', 'ba.register_time', 'ba.authorization_module', 'ba.online_flag');

        $sql = "select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_version, ba.detail, ba.process_type,  ba.agent_type, 
                		unix_timestamp(ba.register_time) register_time, ba.plugin_deploy_status, ba.online_flag, ba.authorization_module,
                		bu.user_name, bu.user_uuid
                from bd_agent ba
                left join  bd_user bu
                on ba.user_uuid = bu.user_uuid ";
        $sqlCount = "select count(ba.id) as total from bd_agent ba left join  bd_user bu on ba.user_uuid = bu.user_uuid ";

        $sql .= " where ba.agent_type != 3 ";
        $sqlCount .= " where ba.agent_type != 3 ";

        $setFlag = Xphp::$_config['FLAG']['SET'];
        $sqlParams = array();
        $sqlCountParams = array();

        //如果是获取指定代理分组下的客户端
        if (!empty($groupuuid)) {
            $sql .= " and ba.group_uuid = ? ";
            $sqlCount .= " and ba.group_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($groupuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($groupuuid));
        }

        //根据主机名搜索
        $search = $params['search'];
        $hostName = $search['name'];
        $utils = Xphp::instance('Utils');
        $hostName = $utils->escapeWildcard($hostName);
        if ($this->checkEmpty($hostName)) {
            $sql .= " and (ba.hostname like ? or ba.ip like ?) ";
            $sqlCount .= " and (ba.hostname like ? or ba.ip like ?) ";
            $sqlParams = array_merge($sqlParams, array('%' . $hostName . '%', '%' . $hostName . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $hostName . '%', '%' . $hostName . '%'));
        }

//         $sql .= " and ba.user_uuid = ? ";
//         $sqlCount .= "and ba.user_uuid = ? ";
//         $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
//         $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));

        $agentuuidArr = $this->getClientUuids(0);
        if (empty($agentuuidArr)) {
            // 表示没得数据
            $sql .= " and ba.user_uuid = ''";
            $sqlCount .= " and ba.user_uuid = ''";
        } else {
            if (is_array($agentuuidArr)) {
                $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
                $sql .= " and ba.agent_uuid in {$agentuuidArrStr} ";
                $sqlCount .= " and ba.agent_uuid in {$agentuuidArrStr} ";
            }
        }

        $sql .= " order by $sortParams[$sortColumn] $sortType limit ? , ? ";
        //合并查询个数参数
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $total = intval($count[0]['total']);
        $records = array("data" => array());
        foreach ($data as $d) {
            $info = $this->getClientDetails($d);
            $records["data"][] = array(
                '<input type="checkbox" value="' . $d['agent_uuid'] . '">',
                !empty($d['hostname']) ? $d['hostname'] : Xphp::$_config['NULLSPACE'],
                $d['ip'],
                $d['os_version'],
                $info['agent_type_des'],
                $this->parseDate($d['register_time']),
                !empty($d['authorization_module']) ? $d['authorization_module'] : "",
                $info['statusdes'],
                $info,

            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return json_encode($records);
    }

    /**
     * 获取客户端分组树结构
     * @param unknown $params
     * @return string
     */
    public function getClientGroupTree()
    {

        $node = array();
        $sql = "select bag.group_uuid, bag.group_name, bag.remark, bag.group_type,
                    bag.user_uuid
                from bd_agent_group bag
                    LEFT JOIN bd_agent ba ON ba.group_uuid = bag.group_uuid 
                WHERE bag.user_uuid = '' OR bag.user_uuid = ? ";
        $sqlParams = [Xphp::$_user['useruuid']];
        if ($_SESSION['isThreePowers']) {
            // 三权模式
            $agentuuidArr = $this->getClientUuids(0);
            if (empty($agentuuidArr)) {
                // 表示没得数据
                $sql .= " OR ba.user_uuid = ''";
            } else {
                if (is_array($agentuuidArr)) {
                    $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
                    $sql .= " OR ba.agent_uuid in {$agentuuidArrStr} ";
                } else {
                    // 查看全部
                    $sql .= " OR true ";
                }
            }
        }
        $data = $this->dbSelect($sql, $sqlParams);
        if (!empty($data)) {
            foreach ($data as $d) {
                $pid = 0;
                $node[$d['group_uuid']] = array(
                    "id" => $d['group_uuid'],
                    "name" => $d['group_name'],
                    "title" => !empty($d['remark']) ? $d['remark'] : $d['group_name'],
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "pId" => 0,
                    "open" => false,
                    "nocheck" => true,
                    "groupuuid" => $d['group_uuid'],
                    "remark" => $d['remark'],
                    "create_time" => $this->parseDate($d['create_time']),
                    "grouptype" => $d['group_type']

                );
            }
        }

        return json_encode(array_values($node));
    }

    /**
     * 添加客户端分组
     * @param unknown $params
     * @return string
     */
    public function addClientGroup($params)
    {
        $name = $params['name'];
        //检查名字是否重复
//         $this->checkClientGroupExist($name);
        $remark = $params['remark'];
        $utils = Xphp::instance('Utils');
        $groupuuid = $utils->uuid();
        $createTime = date('Y-m-d H:i:s');
        $useruuid = Xphp::$_user['useruuid'];
        $groupType = 2;//1默认分组 2新建分组
        $sql = "insert bd_agent_group (group_uuid, group_name, remark, user_uuid, create_time, group_type) values (?, ?,?,?,?,?)";
        $sqlParams = array($groupuuid, $name, $remark, $useruuid, $createTime, $groupType);
        $result = $this->dbExec($sql, $sqlParams);
        if ($result) {
            $newNode = array(
                "id" => $groupuuid,
                "name" => $name,
                "title" => !empty($remark) ? $remark : $name,
                "type" => 1,
                "icon" => './img/platform/flag.png',
                "pId" => 0,
                "open" => false,
                "nocheck" => true,
                "groupuuid" => $groupuuid,
                "remark" => $remark,
                "create_time" => $createTime,
                "grouptype" => $groupType
            );
            return $this->muOpResult($result, Xphp::$_lang['WEB_AGENT_GROUP_ADD'], null, null, 0, $newNode);
        } else {
            return $this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_ADD'], '', 'warning');
        };
    }

    /**
     * 修改客户端分组
     * @param unknown $params
     * @return string
     */
    public function editClientGroup($params)
    {
        $name = $params['name'];
        $groupuuid = $params['groupuuid'];
        $remark = $params['remark'];
        $editTime = date('Y-m-d H:i:s');
        $sql = "update bd_agent_group set group_name = ?, remark = ?, create_time = ? where group_uuid = ? ";
        $sqlParams = array($name, $remark, $editTime, $groupuuid);
        $result = $this->dbExec($sql, $sqlParams);
        if ($result) {
            return $this->muOpResult(true, Xphp::$_lang['WEB_AGENT_GROUP_EDIT']);

        } else {
            return $this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_EDIT'], '', 'warning');
        };
    }

    /**
     * 删除客户端分组
     * @param unknown $params
     */
    public function deleteClientGroup($params)
    {
        $groupuuid = $params['uuid'];
        $agentInfo = $this->checkTaskAgentGroup($groupuuid);
        //检查分组的客户端数量，提示数量
        //检查分组是否在任务中，有任务不能删除
        //提示用户分组删除的后果
        $sql = "delete from bd_agent_group where group_uuid = ?";
        $result = $this->dbExec($sql, array($groupuuid));
        if ($result) {
            $msg = "";
            if ($agentInfo['num'] != 0) {
                $this->resetAgentToGroup($agentInfo['list'], $groupuuid);
                $msg = Xphp::$_lang['WEB_AGENT_GROUP_DELETE_REESET_DEFAULT_TIPS'] . ":" . $agentInfo['num'];
            }
            return $this->muOpResult(true, Xphp::$_lang['WEB_AGENT_GROUP_DELETE'], $msg);

        } else {
            return $this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_DELETE'], '', 'warning');
        }

    }

    /**
     * 从分组移除客户端
     * @param unknown $params
     * @return string
     */
    public function removeClient($params)
    {
        $uuids = $params['uuids'];
        $groupuuid = $params['groupuuid'];
        $result = $this->resetAgentToGroup($uuids, $groupuuid);
        if ($result) {
            return $this->muOpResult(true, Xphp::$_lang['WEB_AGENT_GROUP_REMOVE_CLIENT']);

        } else {
            return $this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_REMOVE_CLIENT'], '', 'warning');
        }
    }

    /**
     * 移动客户端到分组
     * @param unknown $params
     * @return string
     */
    public function moveClientToGroup($params)
    {
        $uuids = $params['list'];
        $groupuuid = $params['groupuuid'];
        $groupname = $params['groupname'];
        $listDes = implode("','", $uuids);
        $title = Xphp::$_lang['UI_CLIENT_GROUP_MOVE_CLIENT_TO'] . "'" . $groupname . "'";

        // 检测是否有不同类型的代理移动至一个分组（默认分组除外）
        $sql = "select ba.agent_type, bag.group_type from bd_agent ba join bd_agent_group bag on ba.group_uuid = bag.group_uuid 
                    where ba.group_uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$groupuuid]);
        $existsAgentType = null; // 已有类型
        if ($data && 1 != $data[0]['group_type']) {
            $existsAgentType = $data[0]['agent_type'];
        }
        $sql = "select distinct agent_type from bd_agent where agent_uuid in ('" . $listDes . "')";
        $data = $this->dbSelect($sql, []);
        if (isset($existsAgentType) && (count($data) > 1 || $data[0]['agent_type'] != $existsAgentType)) {
            // 非默认分组有不同类型的代理移动进来，或与分组内代理类型不同则提示
            return $this->muOpResult(false, $title, Xphp::$_lang['UI_CLIENT_GROUP_MOVE_CLIENT_MULTI_TYPE_TIPS'], 'warning');
        }

        $sql = "update bd_agent set group_uuid = ? where agent_uuid in ('" . $listDes . "')";
        $result = $this->dbExec($sql, array($groupuuid));
        if ($result) {
            return $this->muOpResult(true, $title);

        } else {
            return $this->muOpResult(false, $title, '', 'warning');
        }
    }

    /**
     * 获取应用配置列表
     * @param unknown $params
     * @return string
     */
    public function getApplicationConf($params)
    {
        $clientuuid = $params['clientuuid'];
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortParams = array('', 'app_type', 'app_version', 'app_name', 'app_username', 'app_auth_type', 'update_time', '');

        $sql = "select app_uuid, app_name, app_type, app_auth_type, app_version, app_username, unix_timestamp(update_time) update_time, app_detail  from bd_agent_app where agent_uuid = ? ";
        $sqlCount = "select count(id) as total from bd_agent_app where agent_uuid = ? ";

        $sqlParams = array($clientuuid);
        $sqlCountParams = array($clientuuid);


        $sql .= " order by $sortParams[$sortColumn] $sortType limit ? , ? ";
        //合并查询个数参数
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $total = intval($count[0]['total']);
        $records = array("data" => array());
        $dbDes = include APP_PATH . 'dbprotect/DbDescription.php';
        $allDbType = Xphp::$_config['DB_TYPE'];
        foreach ($data as $d) {
            if ($d['app_type'] == 1000) {//不显示exchange的应用配置
                continue;
            }
            $authTypeDes = $dbDes['AUTH_TYPE_DES'][intval($d['app_auth_type'])];
            $detailConfig = Xphp::$_config['NULLSPACE'];
            $appType = (int)$d['app_type'];
            $appInfo = json_decode($d['app_detail'], true);
            if ($appType != $allDbType['SQLSERVER']) {
                $authTypeDes = Xphp::$_config['NULLSPACE'];
            }
            if ($appType == $allDbType['MYSQL'] || $appType == $allDbType['MARIA']) {
                $authTypeDes = $dbDes['MYSQL_AUTH_TYPE_DES'][intval($d['app_auth_type'])];
                $detailConfig = Xphp::$_lang['UI_DB_MYSQL_CNF_PATH'] . ': ' . $appInfo['cnf_path'];
            }
            if ($appType == $allDbType['ORACLE']) {
                $authTypeDes = $dbDes['ORACLE_AUTH_TYPE_DES'][intval($d['app_auth_type'])];
            }
            if (
                $appType == $allDbType['POSTGRE'] ||
                $appType == $allDbType['ANTDB'] ||
                $appType == $allDbType['KINGBASE'] ||
                $appType == $allDbType['UXDB'] ||
                $appType == $allDbType['HIGHGO'] ||
                $appType == $allDbType['OPENGAUSS'] ||
                $appType == $allDbType['VASTBASE']
            ) {
                $detailConfig = Xphp::$_lang['UI_CLIENT_APP_CROWD_PATH'] . ': ' . $appInfo['cluster_path'];
            } elseif ($appType == $allDbType['CACHE'] || $appType == $allDbType['IRIS']) {  // InterSystems Cache/IRIS
                $detailConfig = Xphp::$_lang['UI_DB_INSTANCE_PATH'] . ': ' . $appInfo['instance_path'];
                $detailConfig .= '<br>' . Xphp::$_lang['UI_DB_PORT'] . ': ' . $appInfo['port'];
                $detailConfig .= '<br>' . Xphp::$_lang['UI_DB_TABLE_SPACE'] . ': ' . $appInfo['table_space'];
            }
            $records["data"][] = array(
                '<input type="checkbox" value="' . $d['app_uuid'] . '">',
                Xphp::$_config['DB_TYPE_DES'][$appType],
                $d['app_version'],
                $d['app_name'],
                $d['app_username'],
                $authTypeDes,
                $this->parseDate($d['update_time']),
                $detailConfig,
                array(
                    'app_name' => $d['app_name'],
                    'app_type' => $appType
                ),
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return json_encode($records);
    }

    /**
     * 获取分组下的客户端信息并检查分组是否在任务中不允许删除
     * @param string $groupuuid 代理分组唯一标识
     * @return number[]|fetchAll()[][]
     */
    private function checkTaskAgentGroup($groupuuid)
    {
        $sql = "select ba.agent_uuid, bag.group_type from bd_agent ba, bd_agent_group bag where bag.group_uuid = ba.group_uuid and ba.group_uuid = ?";
        $data = $this->dbSelect($sql, array($groupuuid));
        $agentNum = count($data);
        $list = array();
        if (!empty($data)) {
            if (intval($data[0]['group_type']) == 1) {
                //默认分组不允许删除
                exit($this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_DELETE'], Xphp::$_lang['WEB_AGENT_GROUP_DELETE_DEFAULT_TIPS'], "warning"));
            }
            foreach ($data as $d) {
                $list[] = $d['agent_uuid'];
            }
            //检查有没有代理在任务中出现
            $listDes = implode(",", $list);
            $sql = "select task_uuid from bd_task where agent_uuid in('" . $listDes . "')";
            $agents = $this->dbSelect($sql);
            if (!empty($agents)) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_DELETE'], Xphp::$_lang['WEB_AGENT_GROUP_DELETE_HAVE_TASK_TIPS'], "warning"));
            }
        }
        $info = array(
            'num' => $agentNum,
            'list' => $list
        );
        return $info;
    }

    /**
     * 移除分组中的客户端重新分配到默认分组
     * @param array $list
     * @param string $groupuuid
     * @return boolean
     */
    private function resetAgentToGroup($list, $groupuuid)
    {
        $sql = "select group_uuid from bd_agent_group where group_type = 1";
        $data = $this->dbSelect($sql);
        //获取默认分组uuid
        $defaultGroup = $data[0]['group_uuid'];
        $listDes = implode("','", $list);
        $sql = "update bd_agent set group_uuid = ? where group_uuid = ? and agent_uuid in ('" . $listDes . "')";
        $result = $this->dbExec($sql, array($defaultGroup, $groupuuid));

        return $result;
    }


    private function groupAgentInfo($data, $agentType)
    {
        $utils = Xphp::instance('Utils');
        $sql = "select user_name, password from bd_user where user_uuid = ? ";
        $info = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $list = array();
        $proxyFlag = $agentType == Xphp::$_config['AGENT_TYPE']['APPLIANCE']; // 传输代理标志
        $clientVendor = $this->getClientVendor();
        foreach ($data as $d) {
            $agent = $this->getAgentPlugin($d['os_type'], $proxyFlag);
            $pluginPath = '';
            foreach ($clientVendor as $k1 => $eachVendor){
                if($d['os_type'] == $eachVendor['text']){
                    $osType = $eachVendor['value'];
                    if('#' != $eachVendor['value']){
                        //如果是"#"就不替换,如果指定了安装包名就替换
                        $pluginPath = $agent[$eachVendor['value']];
                    }
                }
            }
            if (!empty($pluginPath)) {
                $pluginPath = ROOT_PATH . $pluginPath;
            }
            $list[] = array(
                "ip" => $d['ip'],
                "os_type" => Xphp::$_config['AGENT_OS_TYPE'][$osType],
                "port" => $d['port'],
                "net_model" => intval($d['net_model']),
                "agent_username" => $d['agent_username'],
                "agent_password" => $utils->ptPassEncrypt(base64_decode($d['agent_password'])),
                "server_username" => $info[0]['user_name'],
                "server_password" => $info[0]['password'],
                "group_uuid" => $this->getDefaultGroupUUID(),
                "user_uuid" => Xphp::$_user['useruuid'],
                "plugin_path" => $pluginPath,
                "nickname" => $d['nickname'],
                "agent_type" => $agentType ?? 0,
                "client_transport_port" => $d['client_transport_port']
            );

        }
        return $list;
    }


    private function getDefaultGroupUUID()
    {
        $sql = "select group_uuid from bd_agent_group where group_type = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
        return $data[0]['group_uuid'];
    }

    /**
     * 获取代理程序路径
     * @param string $ostype 操作系统类型
     * @param int $proxyFlag 是否传输代理
     * @return string
     */
    private function getAgentPlugin($ostype, $proxyFlag = false)
    {
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
        foreach ($agentInfo as $name){
            //$name:包名
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

            //客户端
            if(strpos(strtoupper($name), strtoupper('backup-agent.windows'))){
                $agent['WINDOWS'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.RHEL.5'))){
                $agent['RHEL5'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.RHEL.6'))){
                $agent['RHEL6'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.RHEL.7'))){
                $agent['RHEL7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.RHEL.8'))){
                $agent['RHEL8'] = "/agent/" . $name;
                $agent['ANOLISOSX64'] = "/agent/" . $name;  // 龙蜥X64下载指向CentOS 8
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.RHEL.9'))){
                $agent['RHEL9'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.Ubuntu'))){
                $agent['UBUNTU'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('AGENT.Debian'))){
                $agent['DEBIAN'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Kylin-aarch64'))){
                $agent['KYLIN'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Kylin-x86_64'))){
                $agent['KYLINX86'] = "/agent/" . $name;
                continue;
            }

            //统信arm
            if(strpos(strtoupper($name), strtoupper('AGENT.UOS-aarch64'))){
                $agent['UNIONTECH'] = "/agent/" . $name;
                continue;
            }

            //统信x86
            if(strpos(strtoupper($name), strtoupper('AGENT.UOS-x86_64'))){
                $agent['UNIONTECHX86'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('winpe-os-agent'))){
                $agent['WINPE'] = "/agent/" . $name;
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
            //中科v4
            if(strpos(strtoupper($name), strtoupper('AGENT.NFSChina-x86_64'))){
                $agent['ZKFD-V4'] = "/agent/" . $name;
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
            if(strpos(strtoupper($name), strtoupper('RHEL.7-aarch64'))){
                $agent['ARM-RHEL7'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('RHEL.8-aarch64'))){
                $agent['ARM-RHEL8'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.6'))){
                $agent['RHEL.6'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.7'))){
                $agent['RHEL.7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('RHEL.8'))){
                $agent['RHEL.8'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('Ubuntu.12'))){
                $agent['Ubuntu.12'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('Debian.7'))){
                $agent['Debian.7'] = "/agent/" . $name;
                continue;
            }
            if(strpos(strtoupper($name), strtoupper('whrelease'))){
                $agent['whrelease'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('release'))){
                $agent['release'] = "/agent/" . $name;
                continue;
            }
            //add
            //欧拉系统(X86)
            if(strpos(strtoupper($name), strtoupper('AGENT.openEuler-x86_64'))){
                $agent['EulerX86'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.openEuler-aarch64'))){
                $agent['EulerAARCH64'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.SUSE-x86_64'))){
                $agent['SUSE'] = "/agent/" . $name;
                continue;
            }


            if(strpos(strtoupper($name), strtoupper('AGENT.Rocky.8-x86_64'))){
                $agent['ROCKYLINUX8'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Rocky.9-x86_64'))){
                $agent['ROCKYLINUX9'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.OracleLinux.6-x86_64'))){
                $agent['ORACLELINUX6'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.OracleLinux.7-x86_64'))){
                $agent['ORACLELINUX7'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.OracleLinux.8-x86_64'))){
                $agent['ORACLELINUX8'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.OracleLinux.9-x86_64'))){
                $agent['ORACLELINUX9'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Anolis.7-x86_64'))){
                $agent['ANOLIS7OSX64'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Anolis.8-x86_64'))){
                $agent['ANOLIS8OSX64'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Astra-x86_64'))){
                $agent['ASTRALINUX'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.RedOS.7-x86_64'))){
                $agent['REDOS'] = "/agent/" . $name;
                continue;
            }

            if(strpos(strtoupper($name), strtoupper('AGENT.Linx-x86_64'))){
                $agent['NSLINUX'] = "/agent/" . $name;
                continue;
            }
        }

        return $agent;
    }

    /**
     * 获取客户端状态
     * @param int $onlineFlag
     * @param int $deployFlag
     * @return mixed
     */
    private function getClientStatus($onlineFlag, $deployFlag)
    {
        $status = Xphp::$_config['FLAG']['SET'];
        if ($onlineFlag == Xphp::$_config['FLAG']['SET']) {
            if ($deployFlag == Xphp::$_config['AGENT_DEPLOY_STATUS']['DEPLOY_FAILED'] ||
                $deployFlag == Xphp::$_config['AGENT_DEPLOY_STATUS']['UPGRADE_FAILED']) {
                $status = Xphp::$_config['FLAG']['UNSET'];
            }
        } else {
            $status = Xphp::$_config['FLAG']['UNSET'];
        }

        return $status;
    }

    /**
     * 获取客户端应用配置信息
     * @return fetchAll()
     */
    private function getApplicationList()
    {
        $sql = "select agent_uuid, app_name, app_type  from bd_agent_app ";
        return $this->dbSelect($sql, array());

    }


    /**
     * 检查分组名是否已被使用
     * @param string $name
     */
    private function checkClientGroupExist($name)
    {
        $sql = "select user_uuid from bd_agent_group where group_name = ?";
        $data = $this->dbSelect($sql, array($name));
        if (!empty($data)) {
            //默认分组和已存在当前用户的分组名
            if (empty($data[0]['user_uuid']) || $data[0]['user_uuid'] == Xphp::$_user['useruuid']) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_AGENT_GROUP_ADD'], Xphp::$_lang['WEB_AGENT_GROUP_ADD_EXIST_TIPS']));
            }
        }
    }

    /**
     * 检查删除的客户端是否关联有office 365组织
     * @param array $uuids
     * @return void
     */
    private function checkClientOrganizationExist(array $uuids)
    {
        $uuidDes = implode("','", $uuids);
        // 因为m365_organization存储的是json格式的客户端列表，因此这里要将所有的office 365组织查询出来
        $sql = "SELECT agent_uuid_list, organization_name FROM m365_organization";
        $organizationList = $this->dbSelect($sql);
        if (!$organizationList || !is_array($organizationList)) {
            return;
        }
        $sql = "SELECT ip, agent_uuid FROM bd_agent WHERE agent_uuid IN ('$uuidDes')";
        $agentList = $this->dbSelect($sql);
        foreach ($organizationList as $organization) {
            $agentUuidList = json_decode($organization['agent_uuid_list'], true);
            if (!$agentUuidList) {
                continue;
            }
            $intersectUuidList = array_intersect($agentUuidList, $uuids);
            if ($intersectUuidList) {  // 存在交集，说明某个客户端有关联组织
                $msg = sprintf(
                    Xphp::$_lang['WEB_CLIENT_ASSOCIATED_ORGANIZATION'],
                    implode(',', array_map(function ($uuid) use ($agentList) {
                        foreach ($agentList as $agent) {
                            if ($agent['agent_uuid'] == $uuid) {
                                return $agent['ip'];
                            }
                        }
                        return '';
                    }, $intersectUuidList)),
                    $organization['organization_name']
                );
                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'], $msg));
            }
        }
    }

    /**
     * 检查删除的客户端是否有任务存在
     * @param array $uuids
     */
    private function checkClientTaskExist(array $uuids)
    {
        $uuidDes = implode("','", $uuids);
        $sql = "select task.task_name,bd_agent.ip 
        	from bd_task_agent_list agent_list,bd_task task,bd_agent 
        	where agent_list.agent_uuid in ('" . $uuidDes . "') and agent_list.task_uuid=task.task_uuid and agent_list.agent_uuid = bd_agent.agent_uuid";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'] . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name']));
        }
        //判断客户端是否关联备份任务
        $volCdpBackupSql = "select vol_task.master_agent_ip,vol_task.id,task.task_name 
                from cdp_vol_task vol_task,bd_task task
                where vol_task.master_agent_uuid in ('" . $uuidDes . "') and task.task_type = ? and vol_task.task_uuid=task.task_uuid";
        $data = $this->dbSelect($volCdpBackupSql, array(Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']));
        if (!empty($data)) {
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'] . ":" . $data[0]['master_agent_ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name']));
        }


        //判断客户端是否做为卷cdp恢复目标主机使用
        $volCdpRecoverySql = "select vol_task.id,task.task_name,bd_agent.ip
                from cdp_vol_task vol_task,bd_task task,bd_agent
                where vol_task.recovery_target_agent_uuid in ('" . $uuidDes . "') and task.task_type = ? and vol_task.task_uuid=task.task_uuid and vol_task.recovery_target_agent_uuid=bd_agent.agent_uuid";
        $data = $this->dbSelect($volCdpRecoverySql, array(Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']));
        if (!empty($data)) {
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'] . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name']));
        }

        //判断客户端是否用作卷cdp双机镜像备机使用
        $volCdpDoubleMirrorSql = "select vol_task.id,task.task_name,bd_agent.ip 
                from cdp_vol_task vol_task,bd_task task,bd_agent
                where vol_task.standby_agent_uuid in ('" . $uuidDes . "') and task.task_type = ? and vol_task.task_uuid=task.task_uuid and vol_task.standby_agent_uuid=bd_agent.agent_uuid";
        $data = $this->dbSelect($volCdpDoubleMirrorSql, array(Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']));
        if (!empty($data)) {
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'] . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name']));
        }

        //判断客户端是否用作卷cdp接管备机使用
        $volCdpTakeoverSql = "select takeover_info.takeover_standby_agent_uuid,task.task_name,bd_agent.ip
                from cdp_vol_task_takeover_info takeover_info,bd_task task,bd_agent
                where takeover_info.takeover_standby_agent_uuid in ('" . $uuidDes . "') and (task.task_type = ? or task.task_type = ?) and takeover_info.task_uuid=task.task_uuid and takeover_info.takeover_standby_agent_uuid=bd_agent.agent_uuid";
        $data = $this->dbSelect($volCdpTakeoverSql, array(Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'], Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']));
        if (!empty($data)) {
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'] . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name']));
        }

        //判断客户端是否用作卷cdp回切目标机使用
        $volCdpFailBackSql = "select failback.failback_target_agent_uuid,task.task_name,bd_agent.ip
                from cdp_vol_task_takeover_failback_info failback,bd_task task,bd_agent
                where failback.failback_target_agent_uuid in ('" . $uuidDes . "') and (task.task_type = ? or task.task_type = ?) and failback.task_uuid=task.task_uuid and failback.failback_target_agent_uuid=bd_agent.agent_uuid";
        $data = $this->dbSelect($volCdpFailBackSql, array(Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'], Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']));
        if (!empty($data)) {
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'] . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name']));
        }
        // 判断客户端是否作为恢复最新点的目标机
        $recoveryNewestSql = "SELECT bt.task_uuid, bt.task_name, ba.ip
                            FROM bd_task bt
                                INNER JOIN db_list dl ON dl.task_uuid = bt.task_uuid
                                INNER JOIN bd_agent ba ON ba.agent_uuid = dl.source_agent_uuid
                            WHERE dl.source_agent_uuid IN ('$uuidDes') ";
        $data = $this->dbSelect($recoveryNewestSql);
        if ($data) {
            $title = Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP']
                . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name'];
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'], $title));
        }
        $this->checkClusterTaskExists($uuids);
    }

    /**
     * 检查应用集群下有无数据库任务
     * @param array $uuids
     * @return void
     */
    private function checkClusterTaskExists(array $uuids)
    {
        $agentUuidList = [];
        foreach ($uuids as $agentUuid) {
            $tmpList = $this->getAllAgentUuidFromAgentUuid($agentUuid);
            $agentUuidList = array_merge($agentUuidList, $tmpList);
        }
        $agentUuidList = array_values(array_unique($agentUuidList));  // 去重, array_unique会带索引的，所以需要array_values去除索引
        $uuidDes = implode("','", $agentUuidList);
        $allTaskType = Xphp::$_config['TASKTYPE'];
        $allowTaskType = [$allTaskType['DB_BACKUP'], $allTaskType['DB_RECOVERY']];
        $allowTaskTypeDes = "'" . implode("', '", $allowTaskType) . "'";
        $sql = "SELECT bt.task_name, ba.ip FROM bd_task_agent_list btal
                    INNER JOIN bd_task bt ON bt.task_uuid=btal.task_uuid
                    INNER JOIN bd_agent ba ON btal.agent_uuid=ba.agent_uuid
                WHERE btal.agent_uuid IN ('" . $uuidDes . "') AND bt.task_type IN ($allowTaskTypeDes) ";
        $data = $this->dbSelect($sql);
        if ($data) {
            $msg = Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'] . "," . Xphp::$_lang['WEB_SYSTEM_SETTING_IP']
                . ":" . $data[0]['ip'] . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ":" . $data[0]['task_name'];
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'], $msg));
        }
    }

    /**
     * 根据客户端的uuid获取到集群下所有的客户端uuid
     * @param string $agentUuid
     * @return array
     */
    public function getAllAgentUuidFromAgentUuid(string $agentUuid): array
    {
        $flagSet = Xphp::$_config['FLAG'];
        $sql = "SELECT cluster_uuid FROM bd_agent_app WHERE agent_uuid = ? AND cluster_flag = ? ";
        $clusterList = $this->dbSelect($sql, [$agentUuid, $flagSet['SET']]);
        if (!is_array($clusterList) || !$clusterList) {
            return [$agentUuid];
        }
        return $this->getAllAgentUuidFromClusterUuid($clusterList[0]['cluster_uuid']);
    }

    /**
     * 根据集群uuid获取所有的客户端uuid
     * @param string $clusterUuid
     * @return array
     * @see DBProtectHandler::getBackupTreeOldInfo() 编辑数据库备份任务的实例、集群、数据库
     */
    public function getAllAgentUuidFromClusterUuid(string $clusterUuid): array
    {
        $flagSet = Xphp::$_config['FLAG'];
        $sql = "SELECT agent_uuid, cluster_uuid
                FROM bd_agent_app
                WHERE cluster_flag = ? AND cluster_uuid = ? AND cluster_uuid != '' ";
        $agentList = $this->dbSelect($sql, [$flagSet['SET'], $clusterUuid]);
        if (!is_array($agentList) || !$agentList) {
            return [];
        }
        return array_values(array_unique(array_column($agentList, 'agent_uuid')));
    }

    /**
     * 得到客户端授权信息
     * @param unknown $params
     * @return string
     */
    public function getClientLisenceInfo()
    {
        $systemHandler = Xphp::instance('SystemHandler');
        $fileInfo = $systemHandler->getOneModuleLisenceInfo('file');
        $dbInfo = $systemHandler->getOneModuleLisenceInfo('database');
        $osInfo = $systemHandler->getOneModuleLisenceInfo('os');
        $cdpInfo = $systemHandler->getOneModuleLisenceInfo('cdp');
        $licenseInfo = json_decode($systemHandler->getSystemLicenseType(), true);
        $info = array(
            'file' => $fileInfo,
            'database' => $dbInfo,
            'os' => $osInfo,
            'cdp' => $cdpInfo,
            'licensetype' => $licenseInfo['licensetype']
        );
        return json_encode($info);
    }


    /**
     * 增加主机授权检测
     * @param int $list 客户端信息
     * @param array $authModule 当前授权模块对象
     * @return boolean
     */
    public function checkAuthEnough($list, $authModule)
    {
        //获取授权模式，如果是容量授权不需要检测个数
        $systemHandler = Xphp::instance('SystemHandler');
        $licenseInfo = json_decode($systemHandler->getSystemLicenseType(), true);
        if ($licenseInfo['licensetype'] == Xphp::$_config['LISENCE_INFO']['type']['storage']) return true;
        $authInfo = json_decode($this->getClientLisenceInfo(), true);
        //计算授权数量
        $file = 0;
        $database = 0;
        $os = 0;
        $cdp = 0;

        foreach ($list as $key => $d) {
            //计算这次添加授权个数
            if ($d['authmodule']['file']) {
                $file++;
            }

            if ($d['authmodule']['database']) {
                $database++;
            }

            if ($d['authmodule']['os']) {
                $os++;
            }

            if ($d['authmodule']['cdp']) {
                $cdp++;
            }

            //已经授权的不计算个数
            if ($d['oldmodule']['file']) {
                $file--;
            }

            if ($d['oldmodule']['database']) {
                $database++;
            }

            if ($d['oldmodule']['os']) {
                $os--;
            }

            if ($d['oldmodule']['cdp']) {
                $cdp--;
            }
        }

        //是否授权文件
        if ($authModule['file']) {
            if ($file > $authInfo['file']['valid']) {
                //超出授权个数报错退出
                exit($this->muOpResult(false, Xphp::$_lang['UI_DB_AGENT_AUTH'], Xphp::$_lang['WEB_CLIENT_FILE_AUTH_NOT_ENOUGH']));
            }
        }
        //是否授权数据库
        if ($authModule['database']) {
            if ($database > $authInfo['database']['valid']) {
                //超出授权个数报错退出
                exit($this->muOpResult(false, Xphp::$_lang['UI_DB_AGENT_AUTH'], Xphp::$_lang['WEB_CLIENT_DB_AUTH_NOT_ENOUGH']));
            }
        }
        //是否授权操作系统
        if ($authModule['os']) {
            if ($os > $authInfo['os']['valid']) {
                //超出授权个数报错退出
                exit($this->muOpResult(false, Xphp::$_lang['UI_DB_AGENT_AUTH'], Xphp::$_lang['WEB_CLIENT_OS_AUTH_NOT_ENOUGH']));
            }
        }
        //是否授权实时容灾
        if ($authModule['cdp']) {
            if ($cdp > $authInfo['cdp']['valid']) {
                //超出授权个数报错退出
                exit($this->muOpResult(false, Xphp::$_lang['UI_DB_AGENT_AUTH'], Xphp::$_lang['WEB_CLIENT_CDP_AUTH_NOT_ENOUGH']));
            }
        }

    }


    /**
     * 检查客户端是否已经添加,单个添加直接返回错误提示，批量添加不再添加已存在的客户端
     * @param array $list
     * @param int $addType
     * @param int $agentType
     */
    private function checkAgentExist($list, $addType, $agentType)
    {
        $ipList = array();
        foreach ($list as $d) {
            $ipList[] = $d['ip'];
        }
        $ipListDes = implode("','", $ipList);
        $sql = "select agent_uuid, ip from bd_agent where ip in('" . $ipListDes . "') and agent_type = ?";
        $data = $this->dbSelect($sql, [$agentType]);
        if ($addType == 1 && !empty($data)) {
            if (Xphp::$_config['AGENT_TYPE']['APPLIANCE'] == $agentType) {
                // 传输代理IP已添加，返回提示
                exit($this->muOpResult(false, Xphp::$_lang['UI_APPLIANCE_ADD'], Xphp::$_lang['WEB_APPLIANCE_EXIST_TIPS'], "warning"));
            }
            //客户端IP已添加，返回提示
            exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_AGENT_OP_ADD'], Xphp::$_lang['WEB_CLIENT_EXIST_TIPS'], "warning"));
        }
        $newList = array();
        foreach ($list as $l) {
            $flag = true;
            foreach ($data as $d) {
                if ($d['ip'] == $l['ip']) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $newList[] = $l;
            }
        }
        return $newList;
    }


    /**
     * 检查IP地址是否符合规则
     * @param array $list
     */
    private function checkAgentIpRules($list)
    {
        foreach ($list as $l) {
            if (false === filter_var($l['ip'], FILTER_VALIDATE_IP)) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_AGENT_OP_ADD'], Xphp::$_lang['UI_SETTINGS_TOOL_IP_OR_DOMAIN_ERROR'], "warning"));
            }
        }

    }

    /**
     * 获取客户端网卡列表
     * @param unknown $params
     * @return string
     */
    public function getClientNetworkList($params)
    {
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $networkList = $params['network'];
        $records = array("data" => array());
        $id = 0;
        foreach ($networkList as $d) {
            $records["data"][] = array(
                ++$id,
                $d['network_name'],
                $d['mac'],
                $d['ip'],
                $d['netmask'],
                $d['gateway'],
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = count($networkList);
        $records["recordsFiltered"] = count($networkList);

        return json_encode($records);

    }

    /**
     * 获取分配的用户列表
     * @return string
     */
    public function getAllocationUsers()
    {
        $sql = "select bu.user_uuid, bu.user_name from bd_user bu left join bd_tenant bt on bu.user_uuid = bt.admin_uuid where bt.tenant_uuid is null";
        $data = $this->dbSelect($sql);
        $list = array();
        foreach ($data as $d) {
            $list[] = array(
                'uuid' => $d['user_uuid'],
                'name' => $d['user_name']
            );
        }

        return json_encode($list);
    }

    /**
     * 删除客户端应用
     * @param unknown $params
     * @return string
     */
    public function deleteClientApp($params)
    {
        $uuids = $params['uuidList'];

        // 检查下是否是集群的应用，是的话查询出相关的集群的应用的uuid，一起删除
        foreach ($uuids as $item) {
            // bd_agent_app 里的 app_uuid 获取 cluster_flag cluster_uuid
            $chkarr = $this->dbSelect("select cluster_flag,cluster_uuid from bd_agent_app where app_uuid = ?", [$item]);
            if (!empty($chkarr[0]) && $chkarr[0]['cluster_flag'] == Xphp::$_config['FLAG']['SET'] && !empty($chkarr[0]['cluster_uuid'])) {
                // 那么根据 cluster_uuid 获取出另外的uuid
                $arr = $this->dbSelect("select app_uuid from bd_agent_app where cluster_uuid = ? and cluster_flag = ? and app_uuid != ?", [$chkarr[0]['cluster_uuid'], Xphp::$_config['FLAG']['SET'], $item]);
                $uuid = array_column($arr, 'app_uuid');
                $uuids = array_merge($uuids, $uuid);
            }
        }

        //检查是否有任务存在
        $this->checkAppExistTask($uuids);
        $pluginFlag = $params['uuidList'];
        $msg = array();
        //定义操作名
        $opName = 'DB_INSTANCE_OP_DELETE_INSTANCE';
        //组合消息
        $msg = array(
            'app_uuid_list' => $uuids,
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbDBMsg($nodeuuid, 0, $opName, json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $dbProtectOpcode = Xphp::instance('DBProtectOpcode');
        $operate = $dbProtectOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }


    /**
     * 检查客户端应用是否正在被任务使用
     * @param unknown $list
     */
    private function checkAppExistTask($list)
    {
        $uuidDes = implode("','", $list);
        $sql = "select dl.task_uuid from db_list dl, bd_agent_app baa where baa.agent_uuid = dl.agent_uuid and  baa.app_name =  dl.instance_name and baa.app_uuid in ('" . $uuidDes . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_INSTANCE_OP_DELETE_INSTANCE'], Xphp::$_lang['WEB_ERROR_BD_AGENT_APP_IN_USE_BY_TASK_ERROR'], "warning"));
        }

    }

    /**
     * 授权客户端检查系统授权状态
     * @param unknown $opreate
     */
    private function checkSystemAuthStatus($operate)
    {
        $sql = "select authorized_flag from bd_system ";
        $data = $this->dbSelect($sql);
        //未正常授权返回错误提示
        if (intval($data[0]['authorized_flag']) != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_CLIENT_SYSTEM_AUTH_ERORR'], "warning"));
        }
    }

    /**
     * 获取选择文件的树
     * @param array $params
     * @return string
     * @link searchClientPathFile()
     */
    public function getSelectFileTree(array $params): string
    {
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $dir = $params['dir'];
        $agentUuid = $params['agent_uuid'];
        $pid = $params['pid'];          //父节点ID
        $searchFileName = $params['search_file_name'];//未完成时,下一个开始名字
        $codeType = 2;
        $selectMode = $params['select_mode'];  // 选择的模式[1文件 2目录 3混合]
        if ($params['code_type']) {
            $codeType = $params['code_type'];//编码类型
        }
        $setFlag = Xphp::$_config['FLAG'];

        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        if ($selectMode == 2) {
            $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        }
        $msg = array(
            'agent_uuid' => $agentUuid,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $codeType,
        );
        $mbResult = $this->getAgentPathList($opName, $msg);
        //返回结果到UI
        if (!$mbResult['result']) {
            //失败
            $operate = Xphp::instance('NodeOpcode')->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];

        $tree = [];
        foreach ($data['item_list'] as $d) {
            $node = array(
                "id" => $d['item_path'],
                "pId" => $pid == "" ? 0 : $pid,
                "name" => $d['item_name'],
                "title" => $d['item_path'],
                "isParent" => !($d['item_type'] == 1),
                "nocheck" => false,
                "icon" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                "iconOpen" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjiaopen.png",
                "iconClose" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                "type" => $d['item_type'],
                "more" => false,
                "noRemoveBtn" => true,
                "code_type" => $d['code_type'],
                "is_new" => false,//文件夹是否是最新的新建
                "new_dir_create" => $setFlag['UNSET'],//文件夹是否是新建的
                "noEditBtn" => true,
                'checked' => false,
                'search_file_name' => $mbResult['msg']['search_file_name'],
                'open' => false,
                'search_value' => '',
                'search_status' => 1,
            );
            $tree[] = $node;
        }
        if (intval($data['is_search_finish']) == $setFlag['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "pId" => !$pid ? 0 : $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "icon" => "./img/fs/wenjianjia.png",
                "iconOpen" => "./img/fs/wenjianjiaopen.png",
                "iconClose" => "./img/fs/wenjianjia.png",
                "isParent" => false,
                "nocheck" => true,
                "more" => true,
                "next_index" => $data['current_next_index'],       //从哪个位置开始加载
                "search_file_name" => $data['search_file_name'],          //从哪个目录开始加载
                "dir_path" => $dir,                                  //当前目录名
                "noRemoveBtn" => true,
                "is_new" => false,//文件夹是否是最新的新建
                "new_dir_create" => $setFlag['UNSET'],//文件夹是否是新建的
                "noEditBtn" => true,
                'checked' => false,
                'open' => false,
            );
            $tree[] = $more;
        }

        /**
         * 选中操作
         * 1. 用于初次扫描
         * 2. 解析选择路径
         * 3. 逐级遍历已解析的路径
         * 4. 找到即结束
         * 5. 遍历完成未找到，不勾选
         */
        $selectedPath = $params['selected_path'];
        if ($selectedPath) {
            $loadItemList = [];
            $pathFragment = explode('/', $selectedPath);
            $matchPid = $tree[0]['id'];
            foreach ($pathFragment as $index => $path) {
                if ($index == 0) {  // 表示选中了顶级目录
                    if ($path . '/' == $selectedPath) {
                        $tree[0]['checked'] = true;
                        break;
                    }

                    $loadItemList = $this->loadAgentAllPathItem($opName, $codeType, $agentUuid, $path . '/');
                    if (is_string($loadItemList)) {
                        return $loadItemList;
                    }
                    $tree[0]['open'] = true;
                    $tree[0]['checked'] = true;
                    continue;
                } elseif ($index == count($pathFragment) - 1) {
                    if (!$path) {  // 搜索的可能是路径
                        continue;
                    }
                }
                $matchItem = null;
                foreach ($loadItemList as $loadItem) {
                    $node = array(
                        "id" => $loadItem['item_path'],
                        "pId" => $matchPid,
                        "name" => $loadItem['item_name'],
                        "title" => $loadItem['item_path'],
                        "isParent" => !($loadItem['item_type'] == 1),
                        "nocheck" => false,
                        "icon" => $loadItem['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                        "iconOpen" => $loadItem['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjiaopen.png",
                        "iconClose" => $loadItem['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                        "type" => $loadItem['item_type'],
                        "more" => false,
                        "noRemoveBtn" => true,
                        "code_type" => $loadItem['code_type'],
                        "is_new" => false,//文件夹是否是最新的新建
                        "new_dir_create" => $setFlag['UNSET'],//文件夹是否是新建的
                        "noEditBtn" => true,
                        'checked' => false,
                        'search_file_name' => $mbResult['msg']['search_file_name'],
                        'open' => false,
                        'search_value' => '',
                        'search_status' => 1,
                    );
                    if ($loadItem['item_name'] == $path) {
                        $matchItem = $loadItem;
                        $node['checked'] = true;
                        if (!($loadItem['item_type'] == 1 || ($index == count($pathFragment) - 2 && !$pathFragment[$index + 1]))) {
                            $node['open'] = true;
                        }
                    }
                    $tree[] = $node;
                }
                if (!$matchItem) {
                    break;
                }
                if ($matchItem['item_type'] == 1) {
                    continue;
                }
                $matchPid = $matchItem['item_path'];
                $loadItemList = $this->loadAgentAllPathItem($opName, $codeType, $agentUuid, $matchItem['item_path']);
                if (is_string($loadItemList)) {
                    return $loadItemList;
                }
            }
        }

        return json_encode([
            're' => true,
            'tree' => $this->sortAgentPath($tree),
        ]);
    }

    /**
     * 加载目录下所有的文件
     * @param $opName
     * @param $codeType
     * @param $agentUuid
     * @param $searchDir
     * @return array|string
     */
    private function loadAgentAllPathItem($opName, $codeType, $agentUuid, $searchDir)
    {
        $ret = [];
        $msg = array(
            'agent_uuid' => $agentUuid,
            'search_index' => 0,
            'limit_count' => 2,
            'search_file_name' => '',
            'dir_path' => $searchDir,
            'code_type' => $codeType,
        );
        do {
            $mbResult = $this->getAgentPathList($opName, $msg);
            if (!$mbResult['result']) {
                $operate = Xphp::instance('NodeOpcode')->getOpcodeDes($opName);
                return $this->muOpResult(false, $operate, '', '', $mbResult['errorCode']);
            }
            if (!isset($mbResult['msg']['item_list']) || !is_array($mbResult['msg']['item_list'])) {
                break;
            }
            $ret = array_merge($ret, $mbResult['msg']['item_list']);
            if ($mbResult['msg']['is_search_finish'] == Xphp::$_config['FLAG']['SET']) {
                break;
            }
            $msg['search_index'] = $mbResult['msg']['current_next_index'];
            $msg['search_file_name'] = $mbResult['msg']['search_file_name'];
        } while ($mbResult['msg']['is_search_finish'] == Xphp::$_config['FLAG']['UNSET']);
        return $ret;
    }

    /**
     * 获取客户端目录信息
     * @param $opName
     * @param $msg
     * @return array
     */
    private function getAgentPathList($opName, $msg)
    {
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        return $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true);
    }

    /**
     * 文件树排序
     * @param array $tree
     * @return array
     */
    private function sortAgentPath(array $tree): array
    {
        $folderList = [];
        $fileList = [];
        $moreList = [];
        foreach ($tree as $node) {
            if ($node['more']) {
                $moreList[] = $node;
                continue;
            }
            if (1 == $node['type']) {  // 文件
                $fileList[] = $node;
            } elseif (2 == $node['type']) {  // 目录
                $folderList[] = $node;
            } elseif (3 == $node['type']) {  // 盘符
                $folderList[] = $node;
            }
        }

        // 排序
        $utils = Xphp::instance('Utils');
        $utils->secondaryArraySort($folderList, 'title');
        $utils->secondaryArraySort($fileList, 'title');

        return array_merge($folderList, $fileList, $moreList);
    }

    /**
     * @param array $params
     * @return string
     */
    public function searchClientPathFile(array $params): string
    {
        $searchValue = $params['search_value'];
        $searchType = (int)$params['operate_type'];  // 搜索类别[1搜索 2停止]
        $searchMode = (int)$params['search_mode'];   // 搜索模式[1目录搜索 2系统全文检索]
        $isFinish = true;
        $nextIndex = (int)$params['start'];
        $searchFileName = $params['search_file_name'];
        $searchRet = [];

        $ret = $this->getSelectFileTree($params);
        $ret = json_decode($ret, true);
        if (!$ret['re']) {
            return json_encode($ret);
        }
        $allNodes = $ret['tree'];

        foreach ($allNodes as $index => $node) {
            if ($node['more']) {
                $nextIndex = (int)$node['next_index'];
                $searchFileName = $node['search_file_name'];
                $isFinish = false;
                unset($allNodes[$index]);
                continue;
            }
            //
            if (strpos($node['name'], $searchValue) !== false) {
                $searchRet[] = $node;
            }
        }

        return json_encode([
            'is_finish' => $isFinish,
            're' => true,
            'tree' => $searchRet,
            'next_index' => $nextIndex,
            'search_file_name' => $searchFileName,
        ]);
    }

    /**
     * 获取客户端日志
     * @param array $params
     * @return string
     */
    public function getClientLogList(array $params): string
    {
        $msg = [
            'agent_uuid' => $params['agent_uuid'],
        ];
        $opcodeName = 'NODE_AGENT_OP_LIST_AGENT_LOG';
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeUuid, json_encode($msg), true);
        // $mbResult = '{"result":true,"msg":[{"log_info":[{"name":"fs_client_manager-2024-01-12.log","path":"\/var\/log\/vinchin\/\/fs_client_manager\/fs_client_manager-2024-01-12.log","time":"2024-01-12"},{"name":"fs_client_manager-2024-01-16.log","path":"\/var\/log\/vinchin\/\/fs_client_manager\/fs_client_manager-2024-01-16.log","time":"2024-01-16"},{"name":"fs_client_manager-2024-01-30.log","path":"\/var\/log\/vinchin\/\/fs_client_manager\/fs_client_manager-2024-01-30.log","time":"2024-01-30"},{"name":"fs_client_manager-2024-01-31.log","path":"\/var\/log\/vinchin\/\/fs_client_manager\/fs_client_manager-2024-01-31.log","time":"2024-01-31"},{"name":"fs_client_transport-2024-01-12.log","path":"\/var\/log\/vinchin\/\/fs_client_transport\/fs_client_transport-2024-01-12.log","time":"2024-01-12"},{"name":"fs_client_transport-2024-01-16.log","path":"\/var\/log\/vinchin\/\/fs_client_transport\/fs_client_transport-2024-01-16.log","time":"2024-01-16"},{"name":"fs_client_transport-2024-01-30.log","path":"\/var\/log\/vinchin\/\/fs_client_transport\/fs_client_transport-2024-01-30.log","time":"2024-01-30"},{"name":"fs_client_transport-2024-01-31.log","path":"\/var\/log\/vinchin\/\/fs_client_transport\/fs_client_transport-2024-01-31.log","time":"2024-01-31"}],"module_type":3},{"log_info":[{"name":"database_backup_service-2024-01-12.log","path":"\/var\/log\/vinchin\/\/database_backup_service\/database_backup_service-2024-01-12.log","time":"2024-01-12"},{"name":"database_backup_service-2024-01-16.log","path":"\/var\/log\/vinchin\/\/database_backup_service\/database_backup_service-2024-01-16.log","time":"2024-01-16"},{"name":"database_backup_service-2024-01-30.log","path":"\/var\/log\/vinchin\/\/database_backup_service\/database_backup_service-2024-01-30.log","time":"2024-01-30"},{"name":"database_backup_service-2024-01-31.log","path":"\/var\/log\/vinchin\/\/database_backup_service\/database_backup_service-2024-01-31.log","time":"2024-01-31"},{"name":"database_backup_service_dm-2024-01-12.log","path":"\/var\/log\/vinchin\/\/database_backup_service_dm\/database_backup_service_dm-2024-01-12.log","time":"2024-01-12"},{"name":"database_backup_service_dm-2024-01-16.log","path":"\/var\/log\/vinchin\/\/database_backup_service_dm\/database_backup_service_dm-2024-01-16.log","time":"2024-01-16"},{"name":"database_backup_service_dm-2024-01-30.log","path":"\/var\/log\/vinchin\/\/database_backup_service_dm\/database_backup_service_dm-2024-01-30.log","time":"2024-01-30"},{"name":"database_backup_service_dm-2024-01-31.log","path":"\/var\/log\/vinchin\/\/database_backup_service_dm\/database_backup_service_dm-2024-01-31.log","time":"2024-01-31"},{"name":"database_backup_service_pg-2024-01-12.log","path":"\/var\/log\/vinchin\/\/database_backup_service_pg\/database_backup_service_pg-2024-01-12.log","time":"2024-01-12"},{"name":"database_backup_service_pg-2024-01-16.log","path":"\/var\/log\/vinchin\/\/database_backup_service_pg\/database_backup_service_pg-2024-01-16.log","time":"2024-01-16"},{"name":"database_backup_service_pg-2024-01-30.log","path":"\/var\/log\/vinchin\/\/database_backup_service_pg\/database_backup_service_pg-2024-01-30.log","time":"2024-01-30"},{"name":"database_backup_service_pg-2024-01-31.log","path":"\/var\/log\/vinchin\/\/database_backup_service_pg\/database_backup_service_pg-2024-01-31.log","time":"2024-01-31"},{"name":"database_transfer_service-2024-01-12.log","path":"\/var\/log\/vinchin\/\/database_transfer_service\/database_transfer_service-2024-01-12.log","time":"2024-01-12"},{"name":"database_transfer_service-2024-01-16.log","path":"\/var\/log\/vinchin\/\/database_transfer_service\/database_transfer_service-2024-01-16.log","time":"2024-01-16"},{"name":"database_transfer_service-2024-01-30.log","path":"\/var\/log\/vinchin\/\/database_transfer_service\/database_transfer_service-2024-01-30.log","time":"2024-01-30"},{"name":"database_transfer_service-2024-01-31.log","path":"\/var\/log\/vinchin\/\/database_transfer_service\/database_transfer_service-2024-01-31.log","time":"2024-01-31"}],"module_type":4,"sub_module":[{"name":"sbt_log","type":1},{"name":"work_log","type":2}]},{"log_info":[{"name":"os_client_manager-2024-01-12.log","path":"\/var\/log\/vinchin\/\/os_client_manager\/os_client_manager-2024-01-12.log","time":"2024-01-12"},{"name":"os_client_manager-2024-01-16.log","path":"\/var\/log\/vinchin\/\/os_client_manager\/os_client_manager-2024-01-16.log","time":"2024-01-16"},{"name":"os_client_manager-2024-01-30.log","path":"\/var\/log\/vinchin\/\/os_client_manager\/os_client_manager-2024-01-30.log","time":"2024-01-30"},{"name":"os_client_manager-2024-01-31.log","path":"\/var\/log\/vinchin\/\/os_client_manager\/os_client_manager-2024-01-31.log","time":"2024-01-31"},{"name":"os_client_transfer-2024-01-12.log","path":"\/var\/log\/vinchin\/\/os_client_transfer\/os_client_transfer-2024-01-12.log","time":"2024-01-12"},{"name":"os_client_transfer-2024-01-16.log","path":"\/var\/log\/vinchin\/\/os_client_transfer\/os_client_transfer-2024-01-16.log","time":"2024-01-16"},{"name":"os_client_transfer-2024-01-30.log","path":"\/var\/log\/vinchin\/\/os_client_transfer\/os_client_transfer-2024-01-30.log","time":"2024-01-30"},{"name":"os_client_transfer-2024-01-31.log","path":"\/var\/log\/vinchin\/\/os_client_transfer\/os_client_transfer-2024-01-31.log","time":"2024-01-31"}],"module_type":5},{"log_info":[{"name":"agent_manager_client-2024-01-12.log","path":"\/var\/log\/vinchin\/\/agent_manager_client\/agent_manager_client-2024-01-12.log","time":"2024-01-12"},{"name":"agent_manager_client-2024-01-16.log","path":"\/var\/log\/vinchin\/\/agent_manager_client\/agent_manager_client-2024-01-16.log","time":"2024-01-16"},{"name":"agent_manager_client-2024-01-30.log","path":"\/var\/log\/vinchin\/\/agent_manager_client\/agent_manager_client-2024-01-30.log","time":"2024-01-30"},{"name":"agent_manager_client-2024-01-31.log","path":"\/var\/log\/vinchin\/\/agent_manager_client\/agent_manager_client-2024-01-31.log","time":"2024-01-31"},{"name":"rt_client-2024-01-12.log","path":"\/var\/log\/vinchin\/\/rt_client\/rt_client-2024-01-12.log","time":"2024-01-12"},{"name":"rt_client-2024-01-16.log","path":"\/var\/log\/vinchin\/\/rt_client\/rt_client-2024-01-16.log","time":"2024-01-16"},{"name":"rt_client-2024-01-30.log","path":"\/var\/log\/vinchin\/\/rt_client\/rt_client-2024-01-30.log","time":"2024-01-30"},{"name":"rt_client-2024-01-31.log","path":"\/var\/log\/vinchin\/\/rt_client\/rt_client-2024-01-31.log","time":"2024-01-31"}],"module_type":1},{"module_type":14},{"log_info":[{"name":"volcdp_client-2024-01-12.log","path":"\/var\/log\/vinchin\/\/volcdp_client\/volcdp_client-2024-01-12.log","time":"2024-01-12"},{"name":"volcdp_client-2024-01-16.log","path":"\/var\/log\/vinchin\/\/volcdp_client\/volcdp_client-2024-01-16.log","time":"2024-01-16"},{"name":"volcdp_client-2024-01-19.log","path":"\/var\/log\/vinchin\/\/volcdp_client\/volcdp_client-2024-01-19.log","time":"2024-01-19"},{"name":"volcdp_client-2024-01-30.log","path":"\/var\/log\/vinchin\/\/volcdp_client\/volcdp_client-2024-01-30.log","time":"2024-01-30"},{"name":"volcdp_client-2024-01-31.log","path":"\/var\/log\/vinchin\/\/volcdp_client\/volcdp_client-2024-01-31.log","time":"2024-01-31"}],"module_type":10}]}';
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
        $logList = [];
        $nullSpace = Xphp::$_config['NULLSPACE'];
        foreach ($mbResult['msg'] as $logItem) {
            $moduleType = (int) $logItem['module_type'];
            if (!isset($logItem['log_info']) || !is_array($logItem['log_info']) || !$logItem['log_info']) {
                continue;
            }

            foreach ($logItem['log_info'] as $logInfo) {
                $logList[] = [
                    'log_name' => $logInfo['name'] ?: $nullSpace,
                    'log_module' => $moduleType,
                    'sub_log_module' => 0,
                    'sub_module_name' => $nullSpace,
                    'log_time' => $logInfo['time'] ?: $nullSpace,
                    'log_path' => $logInfo['path'] ?: $nullSpace,
                ];
            }
            if (isset($logItem['sub_module']) && is_array($logItem['sub_module']) && $logItem['sub_module']) {
                foreach ($logItem['sub_module'] as $subLogItem) {
                    $subModuleType = (int) $subLogItem['type'];
                    if (!isset($subLogItem['log_info']) || !is_array($subLogItem['log_info']) || !$subLogItem['log_info']) {
                        continue;
                    }

                    foreach ($subLogItem['log_info'] as $subLogInfo) {
                        $logList[] = [
                            'log_name' => $subLogInfo['name'] ?: $nullSpace,
                            'log_module' => $moduleType,
                            'sub_log_module' => $subModuleType,
                            'sub_module_name' => $subLogItem['name'] ?: $nullSpace,
                            'log_time' => $subLogInfo['time'] ?: $nullSpace,
                            'log_path' => $subLogInfo['path'] ?: $nullSpace,
                        ];
                    }
                }
            }
        }
        return $this->muOpResult(true, $operate, '', '', '', $logList);
    }

    /**
     * 下载客户端日志
     * @param array $params
     * @return string|void
     */
    public function downloadClientLog(array $params)
    {
        $opcodeName = 'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG';
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = [
            'agent_uuid' => $params['agent_uuid'],
            'log_path_list' => $params['log_path_list'],
            'log_return_path' => Xphp::$_config['TMP_PATH'],
        ];
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeUuid, json_encode($msg), true);

        if (!$mbResult['result']) {
            exit($this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']));
        }

        $systemHandler = Xphp::instance('SystemHandler');
        $url = $systemHandler->groupUnifyDownloadUrl($nodeUuid, trim($mbResult['msg']['file_path']), true);
        return $this->muOpResult($mbResult['result'], $operate, '', '', 0, $url);
    }

    /**
     * 解析CLIENT_OS的数组结构
     * @return array
     */
    public function getClientVendor(){
        $vendor = require_once API_PATH . 'xphp/conf/vendor.php';
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
        return $clientVendor;
    }
}

?>