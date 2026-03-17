<?php

namespace app\v1\resources\v0\service;

use app\v1\common\service\Base;

class Service extends Base
{
    //////////////// 客户端 ///////////////////

    /**
     * 删除客户端
     * @param array $agentUuids          客户端uuid列表
     * @param int   $uninstallPluginFlag 卸载插件标志【1卸载 2不卸载】
     * @return array
     */
    public function deleteClientService(array $agentUuids, int $uninstallPluginFlag): array
    {
        $opcodeName = 'NODE_AGENT_OP_DEL';
        $msg = array(
            'agent_uuid_list' => $agentUuids,
            'uninstall_plugin_flag' => $uninstallPluginFlag,
        );
        $deleteResult = $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
        // 删除资源分配关系
        if ($deleteResult['result']) {
            $allResourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
            $this->deleteResourceAssociationService($agentUuids, [$allResourceType['APPLICE'], $allResourceType['CLIENT']]);
        }
        return $deleteResult;
    }

    /**
     * 更新客户端
     * @param array $params 参数信息
     * @return array
     */
    public function updateClientService(array $params): array
    {
        $opcodeName = 'NODE_AGENT_OP_MODIFY';
        $msg = [
            'agent_uuid' => $params['agents_uuid'],
            'ip' => $params['ip'],
            'net_model' => $params['net_model'],
            'port' => $params['port'],
            'nickname' => $params['nickname'],
            'auto_change_network_flag' => v1_parse_bool_to_flag($params['auto_change_network_flag']),
            'applied_vcenters' => json_encode($params['applied_vcenters']),
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 添加客户端
     * @param array  $agentInfo  客户端列表
     * @param int    $serverPort 备份系统端口
     * @param int    $addMode    添加方式【1手动添加 2远程部署】
     * @param string $serverIp   服务器IP地址
     * @param string $authCode   用户授权码
     * @return array
     */
    public function addClientService(
        array $agentInfo,
        int $serverPort,
        int $addMode,
        string $serverIp,
        string $authCode
    ): array {
        $clientAddMode = xphp_get_config('client', 'AGENT_ADD_MODE', 'resources');
        $opcodeName = 'NODE_AGENT_OP_ADD';
        $nodeUuid = $this->getLocalNodeUuid();
        // 手动添加获取IP地址
        if ($addMode == xphp_get_config('client', 'AGENT_ADD_MODE', 'resources')['manual']) {
            $nodeIpList = json_decode($this->getNodeIPAddress($nodeUuid), true);
            if (count($nodeIpList['ipv4_list'])) {
                $serverIp = $nodeIpList['ipv4_list'][0];
            } else {
                $serverIp = $nodeIpList['ipv6_list'][0];
            }
        }
        $msg = [
            'agent_info_list' => $agentInfo,
            'server_ip' => $serverIp,
            'server_port' => $serverPort,
            'add_mode' => $addMode,
            'auth_code' => $authCode,
        ];
        if ($addMode == $clientAddMode['manual']) {  // 手动添加异步非命令模式(需要登台bd_operate)
            return $this->mbNodeMsg($opcodeName, $nodeUuid, json_encode($msg));
        } else {  // 远程部署异步命令模式
            return $this->mbNodeMsg($opcodeName, $nodeUuid, json_encode($msg), false, true);
        }
    }

    /**
     * 客户端授权/取消授权
     * @param bool   $authFlag  授权类型【true授权 false取消授权】
     * @param array  $agentInfo 授权的客户端信息
     * @param string $userUuid  登录用户的uuid
     * @return array
     */
    public function authClientServer(bool $authFlag, array $agentInfo, string $userUuid): array
    {
        if ($authFlag) {
            // 授权
            $opName = 'PT_LICENSE_OP_ADD_AGENT_LICENSE';
        } else {
            // 取消授权
            $opName = 'PT_LICENSE_OP_MODIFY_AGENT_LICENSE';
        }
        $msg = [
            'agent_uuid' => $agentInfo['agent_uuid'],
            'authorization_module' => json_encode($agentInfo['module']),
            'agent_name' => $agentInfo['agent_name'],
            'user_uuid' => $userUuid,
            'newmodule' => $agentInfo['new_module'],
        ];
        return (array) $this->mbPFMsg($opName, json_encode($msg));
    }

    /**
     * 获取应用实例
     * @param int    $dbType        数据库类型
     * @param array  $agentUuidList 集群关联的用户uuid
     * @param string $agentUuid     安装数据库用户
     * @return array
     */
    public function getInstanceService(int $dbType, array $agentUuidList, string $agentUuid): array
    {
        $opcodeName = 'DB_CLIENT_OP_GET_INSTANCE_LIST';
        $msg = [
            'agent_uuid' => $agentUuid,
            'db_type' => $dbType,
            'agent_uuid_list' => $agentUuidList,
        ];
        return $this->mbDBMsg($this->getLocalNodeUuid(), $dbType, $opcodeName, json_encode($msg), true);
    }

    /**
     * 刷新客户端
     * @param string $agentUuid 客户端uuid
     * @return array
     */
    public function refreshClientService(string $agentUuid): array
    {
        $opcodeName = 'NODE_AGENT_OP_REFRESH';
        $msg = ['agent_uuid' => $agentUuid];

        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 客户端升级消息
     * @param array $upgradeData 升级信息
     * @return array
     */
    public function upgradeClientService(array $upgradeData): array
    {
        $opcodeName = 'NODE_AGENT_OP_UPGRADE';
        $msg = [
            'agent_info' => array_map(function ($item) {
                return [
                    'agent_uuid' => $item['agent_uuid'],
                    'agent_path' => dirname(API_PATH, 2) . $item['agent_path'],
                    'agent_version' => $item['agent_version'],
                ];
            }, $upgradeData),
        ];

        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg), false, true);
    }

    /**
     * 获取客户端日志消息
     * @param string $agentUuid 客户端uuid
     * @return array
     */
    public function getClientLogService(string $agentUuid): array
    {
        $opcodeName = 'NODE_AGENT_OP_LIST_AGENT_LOG';
        $msg = [
            'agent_uuid' => $agentUuid,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg), true);
    }

    /**
     * 下载客户端日志服务
     * @param string $agentUuid   客户端uuid
     * @param array  $logPathList 客户端日志路径
     * @return array
     */
    public function downloadClientLogService(string $agentUuid, array $logPathList): array
    {
        $opcodeName = 'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG';
        $msg = [
            'agent_uuid' => $agentUuid,
            'log_path_list' => $logPathList,
            'log_return_path' => xphp_get_config('app', 'TMP_PATH'),
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg), true);
    }

    /**
     * 删除客户端应用
     * @param array $agentApplicationUuids 客户端应用uuid列表
     * @return array
     */
    public function deleteClientAppService(array $agentApplicationUuids): array
    {
        $opName = 'DB_INSTANCE_OP_DELETE_INSTANCE';
        //组合消息
        $msg = [
            'app_uuid_list' => $agentApplicationUuids,
        ];

        return $this->mbDBMsg($this->getLocalNodeUuid(), 0, $opName, json_encode($msg), false);
    }

    /**
     * 扫描客户端磁盘文件服务
     * @param array  $msg    消息
     * @param string $opName 操作码
     * @return array
     */
    public function scanClientDiskFilesService(array $msg, string $opName): array
    {
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg), true);
    }

    /**
     * 扫描客户端磁盘目录
     * @param array $msg 消息
     * @return array
     */
    public function scanClientDiskDirectory(array $msg): array
    {
        $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg), true);
    }

    /**
     * 添加客户端应用
     * @param array $msg 给后台发送的消息
     * @return array
     */
    public function addClientAppService(array $msg): array
    {
        $opcodeName = 'DB_INSTANCE_OP_VERIFY_AUTH';
        return $this->mbDBMsg($this->getLocalNodeUuid(), $msg['db_type'], $opcodeName, json_encode($msg), false);
    }

    //////////////// 节点 ///////////////////

    /**
     * 获取节点的ip地址
     * @param string $nodeUuid 节点uuid
     * @return string
     */
    public function getNodeIPAddress(string $nodeUuid): string
    {
        $opName = 'BD_SYSTEM_OP_GET_IP_LIST';
        $mbResult = $this->mbNodeMsg($opName, $nodeUuid, json_encode([]), true);
        $list = [
            'ipv4_list' => [],
            'ipv6_list' => [],
        ];
        if ($mbResult['result']) {
            $list['ipv4_list'] = $mbResult['msg']['ipv4_list'] ?: [];
            $list['ipv6_list'] = $mbResult['msg']['ipv6_list'] ?: [];
        }
        return json_encode($list);
    }

    /**
     * 主节点操作服务【修改】
     * @param string $nodesUuid    节点uuid
     * @param string $nodeNickname 节点别名
     * @param int    $nodeFunction 节点功能【1仅管理 2仅计算 3管理+计算】
     * @return array
     */
    public function masterNodeOperateService(string $nodesUuid, string $nodeNickname, int $nodeFunction): array
    {
        $opcodeName = 'NODE_SYS_OP_MODIFY_MASTER_NODE_INFO';
        $msg  = [
            'node_uuid' => $nodesUuid,
            'node_nickname' => $nodeNickname,
            'node_function' => $nodeFunction,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 节点操作服务【添加、修改、删除】
     * @param array  $commonServerInfo 当前节点的信息
     * @param string $remoteIp         目标节点的IP地址
     * @param int    $remotePort       目标节点的端口好
     * @param int    $operationType    操作类别【1添加 2修改 3删除】
     * @param int    $forceFlag        是否强制添加
     * @param string $nodeNickname     节点别名
     * @param int    $nodeFunction     节点功能【1仅管理 2仅计算 3管理+计算】
     * @param string $remoteNodeUuid 节点uuid
     * @return array
     */
    public function nodeOperateService(
        array $commonServerInfo,
        string $remoteIp,
        int $remotePort,
        int $operationType,
        int $forceFlag,
        string $nodeNickname = '',
        int $nodeFunction = 0,
        string $remoteNodeUuid = ''
    ): array {
        $opcodeName = 'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER';
        $msg = [
            'common_server_info' => [
                'database_ip' => $commonServerInfo['database_ip'],
                'database_name' => $commonServerInfo['database_name'],
                'database_passwd' => $commonServerInfo['database_passwd'],
                'database_port' => $commonServerInfo['database_port'],
                'database_user' => $commonServerInfo['database_user'],
                'server_ip' => $commonServerInfo['server_ip'],
                'server_port' => $commonServerInfo['server_port'],
            ],
            'remote_ip' => $remoteIp,
            'remote_port' => $remotePort,
            'remote_node_uuid' => $remoteNodeUuid,
            'operation_type' => $operationType,
            'force_flag' => $forceFlag,
            'node_nickname' => $nodeNickname,
            'node_function' => $nodeFunction,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 全局限速策略新增、修改
     * @param array $msg 消息内容
     * @return string
     */
    public function saveSpeed(array $msg)
    {
        $opName = empty($msg['speed_limit_strategy']['strategy_uuid']) ?
            'SS_OP_ADD_SPEED_LIMITING_STRATEGY' : 'SS_OP_MOD_SPEED_LIMITING_STRATEGY';

        return $this->mbStrategyMsg($this->getLocalNodeUuid(), $opName, json_encode($msg));
    }

    /**
     * 全局限速策略删除
     * @param array $msg 消息内容
     * @return string
     */
    public function delSpeed(array $msg)
    {
        $opName = 'SS_OP_DEL_SPEED_LIMITING_STRATEGY';

        return $this->mbStrategyMsg($this->getLocalNodeUuid(), $opName, json_encode($msg));
    }

    /**
     * 扫描异地备份系统类型资源
     * @param array  $msg    消息内容
     * @param string $opName 操作码
     * @return array
     */
    public function getRemoteStorages(array $msg, string $opName)
    {

        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg), true);
    }

    /**
     * 修改容灾主机信息
     * @param array  $msg      消息内容
     * @param string $opName   操作码
     * @param string $nodeUuid 节点
     * @param bool   $sync     是否同步
     * @return array
     */
    public function mbTempAgentMsgs(array $msg, string $opName, string $nodeUuid, $sync = false)
    {

        return $this->mbTempAgentMsg($nodeUuid, $opName, json_encode($msg), $sync);
    }

    /**
     * 设置节点缓存服务
     * @param string $nodeUuid 节点uuid
     * @param array  $params   参数
     * @return array
     */
    public function setNodeCacheService(string $nodeUuid, array $params): array
    {
        $opcodeName = 'NODE_SYS_OP_CONFIG_SYSTEM_CACHE';
        $msg = [
            'cache_type' => intval($params['cache_type']),
            'cache_dir_path' => $params['cache_dir_path'],
            'cache_storage_uuid' => $params['cache_storage_uuid'],
            'switch_strategy' => intval($params['switch_strategy']),
            'warning_flag' => v1_parse_bool_to_flag($params['warning_flag']),
            'warning_value' => intval($params['warning_value']),
        ];
        return $this->mbNodeMsg($opcodeName, $nodeUuid, json_encode($msg));
    }

    /**
     * 设置节点资源限制服务
     * @param array $resourceLimitMsg 消息
     * @return array
     */
    public function setNodeResourceLimitService(array $resourceLimitMsg): array
    {
        $opcodeName = 'SS_OP_CONFIG_RESOURCE_LIMITING';
        $msg = [
            'resource_limiting_node_config' => $resourceLimitMsg,
        ];
        return $this->mbStrategyMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg));
    }

    ///////////////////存储///////////////////

    /**
     * @param array $msg 消息
     * @return array|\xphp\db\Ambigous
     */
    public function addLunStorageService(array $msg)
    {
        $opName = 'NODE_OP_LUN_ADD';
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * @param array $msg 消息
     * @return array|\xphp\db\Ambigous
     */
    public function checkInitiator(array $msg)
    {
        $opName = 'NODE_STORAGE_INFRASTRUCTURE_OP_CHECK_INITIATOR';
        // 因为是多选节点，需要循环去发送消息
        $array = [];
        foreach ($msg['node_uuid'] as $item) {
            $array[] = [
                'return' => $this->mbNodeMsg($opName, $item, json_encode($msg)),
                'node_uuid' => $item
            ];
        }
        return $array;
    }

    /**
     * 修改生产存储服务
     * @param array $msg 消息
     * @return array
     */
    public function editLunStorageService(array $msg)
    {
        $opName = 'NODE_OP_LUN_MODIFY';
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * @param array $msg 消息
     * @return array|\xphp\db\Ambigous
     */
    public function syncLunStorageService(array $msg)
    {
        $opName = 'NODE_OP_LUN_SYNC';
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    //////////////// 节点网络 ///////////////////

    /**
     * 添加节点网络服务
     * @param string $nodesUuid    节点uuid
     * @param string $networkIp    节点网络IP
     * @param int    $networkPort  节点网络端口
     * @param string $networkAlias 节点网络别名
     * @return array
     */
    public function addNodeNetworkService(
        string $nodesUuid,
        string $networkIp,
        int $networkPort,
        string $networkAlias
    ): array {
        $opcodeName = 'NODE_NETWORK_OP_ADD';
        $msg = [
            'node_uuid' => $nodesUuid,
            'ip' => $networkIp,
            'port' => $networkPort,
            'alias_name' => $networkAlias,
            'order' => 0,
        ];
        return $this->mbNodeMsg($opcodeName, $nodesUuid, json_encode($msg));
    }

    /**
     * 修改节点网络服务
     * @param string $nodesUuid    节点uuid
     * @param string $networkUuid  节点网络uuid
     * @param string $networkIp    节点网络IP
     * @param int    $networkPort  节点网络端口
     * @param string $networkAlias 节点网络别名
     * @return array
     */
    public function editNodeNetworkService(
        string $nodesUuid,
        string $networkUuid,
        string $networkIp,
        int $networkPort,
        string $networkAlias
    ): array {
        $opcodeName = 'NODE_NETWORK_OP_MODIFY';
        $msg = [
            'network_uuid' => $networkUuid,
            'ip' => $networkIp,
            'port' => $networkPort,
            'alias_name' => $networkAlias,
        ];
        return $this->mbNodeMsg($opcodeName, $nodesUuid, json_encode($msg));
    }

    /**
     * 删除节点网络服务
     * @param string $nodesUuid       节点uuid
     * @param array  $networkUuidList 节点网络uuid列表
     * @return array
     */
    public function deleteNodeNetworkService(string $nodesUuid, array $networkUuidList): array
    {
        $opcodeName = 'NODE_NETWORK_OP_DEL';
        $msg = [
            'network_uuid_list' => $networkUuidList,
        ];
        return $this->mbNodeMsg($opcodeName, $nodesUuid, json_encode($msg));
    }

    /**
     * 排序节点网络服务
     * @param string $nodesUuid       节点uuid
     * @param array  $networkUuidList 节点网络排序列表，uuid列表，有序列表
     * @return array
     */
    public function sortNodeNetworkService(string $nodesUuid, array $networkUuidList): array
    {
        $networkOrderList = [];
        foreach ($networkUuidList as $index => $networkUuid) {
            $networkOrderList[] = [
                'network_uuid' => $networkUuid,
                'order' => $index + 1,
            ];
        }
        $opcodeName = 'NODE_NETWORK_OP_ADJUST_ORDER';
        $msg = [
            'network_order_list' => $networkOrderList,
        ];
        return $this->mbNodeMsg($opcodeName, $nodesUuid, json_encode($msg));
    }

    /**
     * 对节点的操作
     * @param $opName   操作码
     * @param $nodeUuid 节点uuid
     * @param $msg      消息
     * @param $sync     是否异步
     * @param $command  命令模式
     * @return array
     */
    public function mbNodeMsgs($opName, $nodeUuid, $msg, $sync = false, $command = false)
    {
        return $this->mbNodeMsg($opName, $nodeUuid, $msg, $sync, $command);
    }

    ////////////////// 资源池 //////////////////

    /**
     * 添加节点资源池服务
     * @param string $nodePoolName 资源池名称
     * @param array  $nodeUuidList 节点uuid列表
     * @param string $remark       备注
     * @param string $opcodeName   操作名
     * @param string $nodePoolUuid 资源池uuid
     * @return array|string
     */
    public function addOrEditNodePoolService(
        string $nodePoolName,
        array $nodeUuidList,
        string $remark,
        string $opcodeName,
        string $nodePoolUuid = ''
    ): array {
        $loginUser = xphp_get_user_info();
        $msg = [
            'node_pool' => [
                'node_pool_uuid' => $nodePoolUuid,
                'node_pool_nickname' => $nodePoolName,
                'user_uuid' => $loginUser['userUuid'],
                'remark' => $remark,
                'details' => '',
                'node_pool_list' => $nodeUuidList,
            ]
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 删除节点资源池
     * @param array $nodePoolUuidList 节点资源池uuid
     * @return array
     */
    public function deleteNodePoolService(array $nodePoolUuidList): array
    {
        $opcodeName = 'NODE_SYS_OP_DELETE_POOL';
        $msg = [
            'node_pool_uuid_list' => $nodePoolUuidList,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 添加网络资源池服务
     * @param string $nodePoolName    资源池名称
     * @param array  $networkUuidList 网络uuid列表
     * @param string $remark          备注
     * @param string $opcodeName      操作名
     * @param string $nodeUuid        哪个节点的资源池
     * @param string $networkPoolUuid 资源池uuid
     * @return array|string
     */
    public function addOrEditNetworkPoolService(
        string $nodePoolName,
        array $networkUuidList,
        string $remark,
        string $opcodeName,
        string $nodeUuid,
        string $networkPoolUuid = ''
    ): array {
        $loginUser = xphp_get_user_info();
        $msg = [
            'network_pool' => [
                'node_uuid' => $nodeUuid,
                'network_pool_uuid' => $networkPoolUuid,
                'network_pool_nickname' => $nodePoolName,
                'user_uuid' => $loginUser['userUuid'],
                'remark' => $remark,
                'details' => '',
                'network_pool_list' => $networkUuidList,
            ]
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 删除网络资源池
     * @param array $networkPoolUuidList 网络资源池uuid
     * @return array
     */
    public function deleteNetworkPoolService(array $networkPoolUuidList): array
    {
        $opcodeName = 'NODE_NETWORK_OP_DELETE_POOL';
        $msg = [
            'network_pool_uuid_list' => $networkPoolUuidList,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 添加存储资源池服务
     * @param string $storagePoolName 资源池名称
     * @param int    $storagePoolType 存储池类别
     * @param array  $storageUuidList 存储uuid列表
     * @param string $remark          备注
     * @param string $opcodeName      操作名
     * @param string $storagePoolUuid 资源池uuid
     * @return array|string
     */
    public function addOrEditStoragePoolService(
        string $storagePoolName,
        int $storagePoolType,
        array $storageUuidList,
        string $remark,
        string $opcodeName,
        string $storagePoolUuid = ''
    ): array {
        $loginUser = xphp_get_user_info();
        $msg = [
            'storage_pool' => [
                'storage_pool_uuid' => $storagePoolUuid,
                'storage_pool_nickname' => $storagePoolName,
                'storage_pool_type' => $storagePoolType,
                'user_uuid' => $loginUser['userUuid'],
                'remark' => $remark,
                'details' => '',
                'storage_pool_list' => $storageUuidList,
            ]
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 删除存储资源池
     * @param array $storagePoolUuidList 存储资源池uuid
     * @return array
     */
    public function deleteStoragePoolService(array $storagePoolUuidList): array
    {
        $opcodeName = 'NODE_SR_OP_DELETE_POOL';
        $msg = [
            'storage_pool_uuid_list' => $storagePoolUuidList,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 添加代理资源池服务
     * @param string $agentPoolName 资源池名称
     * @param array  $agentUuidList 代理uuid列表
     * @param string $remark        备注
     * @param string $opcodeName    操作名
     * @param string $agentPoolUuid 资源池uuid
     * @return array|string
     */
    public function addOrEditAgentPoolService(
        string $agentPoolName,
        array $agentUuidList,
        string $remark,
        string $opcodeName,
        string $agentPoolUuid = ''
    ): array {
        $loginUser = xphp_get_user_info();
        $msg = [
            'agent_pool' => [
                'agent_pool_uuid' => $agentPoolUuid,
                'agent_pool_nickname' => $agentPoolName,
                'user_uuid' => $loginUser['userUuid'],
                'remark' => $remark,
                'details' => '',
                'agent_pool_list' => $agentUuidList,
            ]
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 删除代理资源池
     * @param array $agentPoolUuidList 代理资源池uuid
     * @return array
     */
    public function deleteAgentPoolService(array $agentPoolUuidList): array
    {
        $opcodeName = 'NODE_APPLIANCE_OP_DELETE_POOL';
        $msg = [
            'agent_pool_uuid_list' => $agentPoolUuidList,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 添加对象存储 
     * @param string $nodeuuid
     * @param string $opcodeName
     * @param array $msg
     * @return \xphp\db\Ambigous
     */
    public function addOBStorage(string $nodeuuid, string $opcodeName, array $msg)
    {
        return $this->mbOBSMsg($nodeuuid, $opcodeName, json_encode($msg), false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
    }

    /**
     * 修改对象存储
     * @param string $nodeuuid
     * @param string $opcodeName
     * @param array $msg
     * @return \xphp\db\Ambigous
     */
    public function editOBStorage(string $nodeuuid, string $opcodeName, array $msg)
    {
        return $this->mbOBSMsg($nodeuuid, $opcodeName, json_encode($msg), false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
    }

    /**
     * 删除对象存储
     * @param string $nodeuuid
     * @param string $opcodeName
     * @param array $msg
     * @return \xphp\db\Ambigous
     */
    public function delOBStorage(string $nodeuuid, string $opcodeName, array $msg)
    {
        return $this->mbOBSMsg($nodeuuid, $opcodeName, json_encode($msg), false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
    }

    /**
     * 添加授权
     * @param string $nodeuuid
     * @param string $opcodeName
     * @param array $msg
     * @return \xphp\db\Ambigous
     */
    public function addAuth(string $opcodeName, array $msg)
    {
        return $this->mbPFMsg($opcodeName, json_encode($msg));
    }

    /**
     * 取消授权
     * @param string $nodeuuid
     * @param string $opcodeName
     * @param array $msg
     * @return \xphp\db\Ambigous
     */
    public function removeAuth(string $opcodeName, array $msg)
    {
        return $this->mbPFMsg($opcodeName, json_encode($msg));
    }
    /**
     * 添加nas设备
     * @param string $opcodeName 操作码名称
     * @param array $nodeArr 节点数组
     * @param array $params 参数
     * @return string
     */
    public function addNasDevice($opcodeName, $nodeArr, $params)
    {
        //组合消息
        $msg = [
            'nas_type' => $params['nas_type'],
            'permission_flag' => $params['permission_flag'],
            'version' => $params['version'],
            'ip' => $params['ip'],
            'nickname' => $params['nickname'],
            'port' => $params['port'] ?? '',
            'share_path' => $params['share_path'],
            'username' => $params['username'] ?? '',
            'passwd' => $params['passwd'] ?? '',
            'mount_params' => $params['mount_params'] ?? '',
        ];
        foreach ($nodeArr as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        }
        return $mbResult;
    }
    /**
     *修改NAS设备信息
     * @param string $opcodeName 操作码名称
     * @param string $nodeUuid 节点uuid
     * @param array $msg 消息
     * @return string
     */
    public function editNasDevice($opcodeName, $nodeUuid, $msg)
    {
        return $this->mbNodeMsg($opcodeName, $nodeUuid, $msg);
    }

    //////////////////// 驱动检测 ////////////////////

    /**
     * 删除驱动库服务
     * @param array $driverUuidList 驱动uuid列表
     * @return array
     */
    public function deleteDriversService(array $driverUuidList): array
    {
        $opcodeName = 'TOOL_DR_OP_DELETE';
        $msg = [
            'uuid_list' => $driverUuidList,
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg));
    }

    /**
     * 解析驱动库服务
     * @param string $uploadPath 上传路径
     * @param int    $osType     系统类型【1Windows 2Linux】
     * @return array
     */
    public function parseDriverService(string $uploadPath, int $osType): array
    {
        $opcodeName = 'TOOL_DR_OP_PARSE';
        $msg = [
            'os_type' => $osType,
            'upload_path' => $uploadPath,
            'remark' => '',
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }

    /**
     * 添加驱动库服务
     * @param string $uploadPath 上传路径
     * @param int    $osType     系统类型【1Windows 2Linux】
     * @param string $remark     备注
     * @return array
     */
    public function addDriverService(string $uploadPath, int $osType, string $remark): array
    {
        $opcodeName = 'TOOL_DR_OP_PARSE_AND_STORE';
        $msg = [
            'os_type' => $osType,
            'upload_path' => $uploadPath,
            'remark' => $remark,
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }

    /**
     * 安装客户端驱动服务
     * @param array $agentUuidList 客户端uuid列表
     * @return array
     */
    public function installAgentDriverService(array $agentUuidList): array
    {
        $opcodeName = 'NODE_AGENT_OP_INSTALL_DRIVER_FOR_AGENT';
        $msg = [
            'agent_uuid_list' => $agentUuidList,
        ];
        return $this->mbNodeMsg($opcodeName, $this->getLocalNodeUuid(), json_encode($msg), false, true);
    }

    /**
     * 驱动检测服务服务
     * @param array $msg 消息
     * @return array
     */
    public function checkOsDriverService(array $msg): array
    {
        $opcodeName = 'TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST';
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }

    /**
     * 刷新对象存储
     * @param string $nodeuuid 节点uuid
     * @param string $opcodeName 操作码
     * @param array $msg 参数
     * @return string
     */
    public function refreshOBStorage(string $nodeuuid, string $opcodeName, array $msg)
    {
        return $this->mbOBSMsg($nodeuuid, $opcodeName, json_encode($msg), false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
    }
}