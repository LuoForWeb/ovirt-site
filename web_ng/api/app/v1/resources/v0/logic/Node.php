<?php

namespace app\v1\resources\v0\logic;

use app\v1\backupData\v0\logic\DataManage;
use app\v1\cluster\v0\logic\Cluster;
use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\OrchestrationOpcode;
use app\v1\system\v0\logic\Index as SystemHandler;
use app\v1\user\v0\logic\User;
use stdClass;
use app\v1\resources\v0\logic\Index as ResourceHandler;

/**
 * note          节点管理logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:23
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Node extends Base
{
    private $nodeOpcode;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
    }

    /**
     * 简单的数据查询获取，不对查询结果进行特殊的处理，这个函数的目的是提高代码复用
     * @param array  $fields    字段
     * @param string $sql       sql
     * @param array  $sqlParams 条件
     * @return array
     */
    private function getSimpleInfo(array $fields, string $sql, array $sqlParams): array
    {
        $selectData = $this->dbSelect($sql, $sqlParams);

        $data = [];
        foreach ($selectData as $row) {
            $tmpRow = [];
            foreach ($fields as $field) {
                $tmpRow[$field] = $row[$field];
            }
            $data[] = $tmpRow;
        }
        return $data;
    }

    /**
     * 获取节点的模块信息
     * @param string $nodeUuid uuid
     * @return array [{"module_type": 2, "online_flag": 1}]
     */
    public function getNodeModuleInfo(string $nodeUuid): array
    {
        $sql = "SELECT module_type, online_flag FROM bd_module_server WHERE node_uuid = ?";
        $sqlParams = [$nodeUuid];
        $selectData = $this->dbSelect($sql, $sqlParams);

        $moduleInfo = [];
        foreach ($selectData as $row) {
            $moduleInfo[] = [
                'module_type' => $row['module_type'],
                'online_flag' => $row['online_flag'],
            ];
        }
        foreach ($moduleInfo as $index => $row) {
            // 是否在线标记: 1在线, 其他不在线
            $moduleInfo[$index]['online_flag'] = $row['online_flag'] == xphp_get_config('app', 'FLAG')['SET'];
        }
        return $moduleInfo;
    }

    /**
     * 获取节点的资源限制信息
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    private function getNodeResourceLimitByNodeUuidList(array $nodeUuidList): array
    {
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $sql = "SELECT node_uuid, node_config_flag, prohibit_start_time,
                    prohibit_end_time, days, prohibit_time_type, max_task_running_num
                FROM bd_resource_limiting_strategy_node_config
                WHERE node_uuid IN ($nodeUuids) ";
        $data = $this->dbSelect($sql);
        $nodeResourceLimitMap = [];
        $allProhibitTimeType = xphp_get_config('node', 'PROHIBIT_TIME_TYPE', 'resources');
        foreach ($data as $row) {
            if (!isset($nodeResourceLimitMap[$row['node_uuid']])) {
                $nodeResourceLimitMap[$row['node_uuid']] = [
                    'node_config_flag' => v1_parse_flag_to_bool($row['node_config_flag']),
                    'max_task_running_num' => intval($row['max_task_running_num']),
                    'prohibit_time_type' => intval($row['prohibit_time_type']),
                    'prohibit_time_info' => [],
                ];
            }
            $tmpTime = [
                'start_timestamp' => v1_time_to_sec($row['prohibit_start_time']),
                'end_timestamp' => v1_time_to_sec($row['prohibit_end_time']),
                'start_time' => $row['prohibit_start_time'],
                'end_time' => $row['prohibit_end_time'],
                'days' => array_map('intval', str_split($row['days'])),
            ];
            if ($row['prohibit_time_type'] == $allProhibitTimeType['CUSTOM']) {  // 用户自定义
                $tmpTime['start_time'] = date('Y-m-d H:i:s', strtotime($row['prohibit_start_time']));
                $tmpTime['end_time'] = date('Y-m-d H:i:s', strtotime($row['prohibit_end_time']));
                $tmpTime['start_timestamp'] = strtotime($row['prohibit_start_time']);
                $tmpTime['end_timestamp'] = strtotime($row['prohibit_end_time']);
            }
            $nodeResourceLimitMap[$row['node_uuid']]['prohibit_time_info'][] = $tmpTime;
        }
        foreach ($nodeUuidList as $nodeUuid) {
            if (!isset($nodeResourceLimitMap[$nodeUuid])) {
                $nodeResourceLimitMap[$nodeUuid] = [
                    'node_config_flag' => false,
                    'max_task_running_num' => 0,
                    'prohibit_time_type' => $allProhibitTimeType['DAY'],
                    'prohibit_time_info' => [],
                ];
            }
        }
        return $nodeResourceLimitMap;
    }

    /**
     * 查询节点数据
     * @param array $params 参数信息
     * @return array
     */
    private function queryNodeInfo(array $params): array
    {
        $sortFields = [
            'node_nickname' => 'bn.node_nickname',
            'ip' => 'bn.ip',
            'register_time' => 'bn.register_time',
            'node_function' => 'bn.node_function',
        ];
        $sort = $sortFields[$params['sort'] ?? 'bn.node_type'] ?? 'bn.node_type';
        $order = strtoupper($params['order'] ?? 'ASC') == 'ASC' ? 'ASC' : 'DESC';

        // 构建查询SQL
        $sql = "SELECT bn.node_type, bn.detail, bn.ip, bn.node_uuid, bn.host_name, bn.node_nickname,
                    bn.register_time, bn.auto_upgrade_flag, bn.status, bn.node_function
                FROM bd_node bn
                WHERE 1 = 1 ";
        $sqlCount = "SELECT COUNT(*) AS count from bd_node bn WHERE 1=1 ";
        $sqlParams = [];
        if (isset($params['nodes_uuid'])) {
            $sql .= ' AND bn.node_uuid = ? ';
            $sqlCount .= ' AND bn.node_uuid = ? ';
            $sqlParams[] = $params['nodes_uuid'];
        }
        if (isset($params['time_point_module_type']) && $params['time_point_module_type']) {
            $subSql = "SELECT bsr.node_uuid
                       FROM bd_backup_timepoint bbt
                        INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid=bsr.storage_uuid
                       WHERE bbt.module_type = ? ";
            $data = $this->dbSelect($subSql, [(int) $params['time_point_module_type']]);
            if (!$data || !is_array($data)) {
                $data = [];
            }
            $nodeUuidDes = "'" . implode("', '", array_unique(array_column($data, 'node_uuid'))) . "'";
            $sql .= " AND bn.node_uuid in ($nodeUuidDes) ";
            $sqlCount .= " AND bn.node_uuid in ($nodeUuidDes) ";
        }
        if (isset($params['node_type']) && $params['node_type']) { // 节点类型
            $sql .= ' AND bn.node_type = ? ';
            $sqlCount .= ' AND bn.node_type = ? ';
            $sqlParams[] = $params['node_type'];
        }
        if (isset($params['node_function']) && $params['node_function']) { // 节点功能
            $allNodeFunction = xphp_get_config('node', 'NODE_FUNCTION', 'resources');
            if ($params['node_function'] == $allNodeFunction['MANAGEMENT']) {  // 包含管理
                $sql .= " AND bn.node_function IN ({$allNodeFunction['MANAGEMENT']}, {$allNodeFunction['MANAGEMENT_CALCULATION']}) ";
                $sqlCount .= " AND bn.node_function IN ({$allNodeFunction['MANAGEMENT']}, {$allNodeFunction['MANAGEMENT_CALCULATION']}) ";
            } elseif ($params['node_function'] == $allNodeFunction['CALCULATION']) {  // 包含计算
                $sql .= " AND bn.node_function IN ({$allNodeFunction['CALCULATION']}, {$allNodeFunction['MANAGEMENT_CALCULATION']}) ";
                $sqlCount .= " AND bn.node_function IN ({$allNodeFunction['CALCULATION']}, {$allNodeFunction['MANAGEMENT_CALCULATION']}) ";
            } else {  // 同时拥有管理和计算
                $sql .= " AND bn.node_function = {$allNodeFunction['MANAGEMENT_CALCULATION']} ";
                $sqlCount .= " AND bn.node_function = {$allNodeFunction['MANAGEMENT_CALCULATION']} ";
            }
        }

        if (v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NODE'], 'bn.node_uuid');
            $sql .= " AND ($resourceUuidSql) ";
            $sqlCount .= " AND ($resourceUuidSql) ";
        }
        $sql .= " ORDER BY $sort $order ";
        $selectCount = $this->dbSelect($sqlCount, $sqlParams);
        if (isset($params['offset']) && isset($params['limit'])) {
            $sql .= ' LIMIT ?,? ';
            $sqlParams = array_merge($sqlParams, [intval($params['offset']), intval($params['limit'])]);
        }

        $selectData = $this->dbSelect($sql, $sqlParams);
        if (!$selectData || !is_array($selectData)) {
            $selectData = [];
        }
        $nodeUuidList = array_column($selectData, 'node_uuid');
        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $allStorageCategory = xphp_get_config('storage', 'BD_STORAGE_CATEGORY');
        // 查询新磁盘信息
        $sql = "SELECT storage_uuid, storage_nickname, mount_point, status AS storage_status,
                    total_size, free_size, storage_type, node_uuid, lan_free_flag
                FROM bd_storage_resource
                WHERE node_uuid IN ($nodeUuids) AND source_type = ? ";
        $sqlParams = [$allStorageCategory['BACKUP']];  // 只查询备份存储

        // 排除的存储类型
        if (isset($params['exclude_storage_type_list']) && $params['exclude_storage_type_list']) {
            $excludeStorageTypes = "'" . implode("', '", $params['exclude_storage_type_list']) . "'";
            $sql .= " AND storage_type NOT IN ($excludeStorageTypes) ";
        }
        $storageData = $this->dbSelect($sql, $sqlParams);
        if (!$storageData || !is_array($storageData)) {
            $storageData = [];
        }

        // 查询存储资源池
        $storageUuidList = array_column($storageData, 'storage_uuid');
        $storagePoolMap = (new StoragePool())->buildStoragePoolInfoWithStorageUuid($storageUuidList);
        foreach ($storageData as $index => $storageInfo) {
            $storageData[$index]['storage_pool_list'] = $storagePoolMap[$storageInfo['storage_uuid']] ?? [];
        }
        // 查询网络信息
        $sql = "SELECT ip AS network_ip, network_uuid, network_name, port AS network_port, node_uuid
                FROM bd_node_network
                WHERE node_uuid IN ($nodeUuids)
                ORDER BY network_order ";
        $networkData = $this->dbSelect($sql);
        if (!$networkData || !is_array($networkData)) {
            $networkData = [];
        }
        return [
            'count' => $selectCount[0]['count'],
            'rows' => $selectData,
            'storage_data' => $storageData,
            'network_data' => $networkData,
        ];
    }

    /**
     * 构建节点网络信息
     * @param array  $networkData 网络信息
     * @param string $nodeUuid    节点uuid
     * @return array
     */
    private function getNodeNetworkInfo(array $networkData, string $nodeUuid): array
    {
        $networkList = [];
        foreach ($networkData as $networkInfo) {
            if ($networkInfo['node_uuid'] != $nodeUuid) {
                continue;
            }
            $this->buildNodeNetworkInfo($networkList, $networkInfo);
        }
        return array_values($networkList);
    }

    /**
     * 构建节点网络信息
     * @param array $networkList 网络列表，引用，用于构建网络信息
     * @param array $networkInfo 网络信息
     * @return void
     */
    public function buildNodeNetworkInfo(array &$networkList, array $networkInfo)
    {
        if ($networkInfo['network_uuid']) {
            $networkList[$networkInfo['network_uuid']] = [
                'network_uuid' => $networkInfo['network_uuid'],
                'network_ip' => $networkInfo['network_ip'],
                'network_port' => (int) $networkInfo['network_port'],
                'network_name' => $networkInfo['network_name'],
            ];
        }
    }

    /**
     * 获取节点存储设备信息
     * @param array  $storageData 存储设备信息
     * @param string $nodeUuid    节点uuid
     * @return array
     */
    private function getNodeStorageInfo(array $storageData, string $nodeUuid): array
    {
        $storageList = [];
        foreach ($storageData as $storageInfo) {
            if ($storageInfo['node_uuid'] != $nodeUuid) {
                continue;
            }
            $this->buildNodeStorageInfo($storageList, $storageInfo);
        }
        return array_values($storageList);
    }

    /**
     * 构建节点存储设备信息
     * @param array $storageList 存储设备列表，引用，用于构建存储设备信息
     * @param array $storageInfo 存储设备信息
     * @return void
     */
    public function buildNodeStorageInfo(array &$storageList, array $storageInfo)
    {
        if ($storageInfo['storage_uuid']) {
            $storageList[$storageInfo['storage_uuid']] = [
                'storage_uuid' => $storageInfo['storage_uuid'],
                'storage_name' => $storageInfo['storage_nickname'],
                'mount_point' => $storageInfo['mount_point'],
                'storage_status' => (int) $storageInfo['storage_status'],
                'total_size' => (int) $storageInfo['total_size'],
                'free_size' => (int) $storageInfo['free_size'],
                'storage_type' => (int) $storageInfo['storage_type'],
                'storage_pool_list' => $storageInfo['storage_pool_list'],
                'lan_free_flag' => v1_parse_flag_to_bool($storageInfo['lan_free_flag']),
            ];
        }
    }


    /**
     * 根据类型返回拥有权限的节点资源ID
     * @param int $type 1查看 2操作
     * @return array
     */
    private function getNodeSourceUuidByType(int $type = 1): array
    {
        $user = xphp_get_user_info();
        // 三权模式下的操作员只能查看分配的存储列表
        // 非三权模式，并且不是超级管理员也不是全局观察者，也只能看到分配的资源
        $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
        $resourceInfo = (new \app\v1\resources\v0\logic\Index())->pGetUserAllResource(
            $user['userUuid'],
            $resourceType['NODE']
        );

        $source = $type == 1 ? '_look' : '_operate';
        // 关联管理用户判断 节点资源 - 查看?操作
        $authUser = $user['authUser']['resmanagement' . $source] ?? [];

        if (empty($resourceInfo) && empty($authUser)) {
            return [];
        }
        $resourceUuid = [];
        if (!empty($resourceInfo)) {
            $resourceUuid = array_column($resourceInfo, 'resource_uuid');
        }
        // 如果有被管理的用户，那么需要查询出被关联的用户资源
        if (!empty($authUser)) {
            $authsLogic = new \app\v1\resources\v0\logic\Index();
            foreach ($authUser as $items) {
                $resourceInfo = ($authsLogic)->pGetUserAllResource(
                    $items,
                    $resourceType['NODE']
                );

                $resourceUuid = array_merge($resourceUuid, array_column($resourceInfo, 'resource_uuid'));
            }
        }

        return $resourceUuid;
    }

    /**
     * 获取节点列表，提供给node控制器使用
     * @param array $params 参数列表
     *                      {"offset": 1, "limit": 20, "sort": "register_time", "order": "desc"}
     *                      {"nodes_uuid": "xxx"}
     *                      {}
     * @return array|stdClass
     */
    public function getNodes(array $params)
    {
        $queryData = $this->queryNodeInfo($params);
        $nodeUuidList = array_column($queryData['rows'], 'node_uuid');
        $nodePoolMap = (new NodePool())->buildNodePoolInfoWithNodeUuid($nodeUuidList);
        // 获取节点资源限制信息
        $nodeResourceLimitMap = $this->getNodeResourceLimitByNodeUuidList($nodeUuidList);

        $nodeData = [];
        foreach ($queryData['rows'] as $row) {
            // 节点版本
            $detail = json_decode($row['detail'], true);
            $version = xphp_get_config('app', 'NULLSPACE');
            if ($detail) {
                $version = $detail['version']['major_version']
                    . '.' . $detail['version']['minor_version']
                    . '.' . $detail['version']['build_number'];
            }
            // 节点状态
            $nodeStatus = $this->getNodeStatus($row['node_uuid']);
            // 计算资源池
            $nodePoolList = $nodePoolMap[$row['node_uuid']] ?? [];

            $nodeData[] = [
                'node_type' => $row['node_type'],
                'detail' => $row['detail'],
                'ip' => $row['ip'],
                'node_uuid' => $row['node_uuid'],
                'host_name' => $row['host_name'],
                'node_nickname' => $row['node_nickname'] ?? '',
                'node_function' => intval($row['node_function']),
                'register_time' => $row['register_time'],
                // 自动升级标记: 1自动升级, 其他不自动升级
                'auto_upgrade_flag' => $row['auto_upgrade_flag'] == xphp_get_config('app', 'FLAG')['SET'],
                'status' => $row['status'],
                // 模块信息
                'module_info' => $this->getNodeModuleInfo($row['node_uuid']),
                'version' => $version,
                'node_pool_name' => array_column($nodePoolList, 'node_pool_nickname'),
                'node_pool_list' => $nodePoolList,
                'online_flag' => $nodeStatus['online_flag'],
                'deploy_flag' => $nodeStatus['deploy_flag'],
                'offline_module' => $nodeStatus['offline_module'],
                'offline_module_des' => $this->getOffLineModuleDes($nodeStatus['offline_module']),
                'network_list' => $this->getNodeNetworkInfo($queryData['network_data'], $row['node_uuid']),
                'storage_list' => $this->getNodeStorageInfo($queryData['storage_data'], $row['node_uuid']),
                'node_resource_limit_flag' => $nodeResourceLimitMap[$row['node_uuid']]['node_config_flag'],
            ];
        }

        if (isset($params['nodes_uuid'])) {
            return $nodeData[0] ?? new stdClass();
        }
        return [
            'total' => $queryData['count'],
            'rows' => $nodeData,
        ];
    }

    /**
     * 获取节点关联的资源池名称
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    private function getNodePoolNameList(string $nodeUuid): array
    {
        $sql = "SELECT bnp.node_pool_nickname
                FROM bd_node_pool bnp
                    INNER JOIN bd_node_pool_list bnpl on bnp.node_pool_uuid = bnpl.node_pool_uuid
                WHERE bnpl.node_uuid = ? GROUP BY bnp.node_pool_uuid ";
        $data = $this->dbSelect($sql, [$nodeUuid]);
        if ($data && is_array($data)) {
            return array_values(array_unique(array_column($data, 'node_pool_nickname')));
        }
        return [];
    }

    /**
     * 获取节点的状态
     * @param string $nodeUuid 节点uuid
     * @link \app\v1\cluster\v0\logic\Cluster::getClusterConfig()
     * @link Storage::getStorageList()
     * @link NodePool::getNodePoolDetail()
     * @return array
     */
    public function getNodeStatus(string $nodeUuid): array
    {
        $sql = "SELECT status, node_uuid FROM bd_node WHERE node_uuid = ? ";
        $nodeData = $this->dbSelect($sql, [$nodeUuid]);
        if (!is_array($nodeData)) {
            $nodeData = [];
        }
        $sql = "SELECT online_flag, module_type FROM bd_module_server WHERE node_uuid = ? ";
        $data = $this->dbSelect($sql, [$nodeUuid]);
        $ret = [
            'online_flag' => false,
            'deploy_flag' => false,
            'offline_module' => [],
            'node_uuid' => $nodeUuid,
            'status' => intval($nodeData[0]['status']),
        ];
        if (!$data || !is_array($data)) {
            return $ret;
        }
        $ret['deploy_flag'] = true;
        if ($ret['status'] == xphp_get_config('node', 'NODE_OPERATE_STATUS', 'resources')['OFFLINE']) {  // 离线
            return $ret;
        }
        $ret['online_flag'] = true;
        foreach ($data as $item) {
            if ($item['online_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                $ret['online_flag'] = false;
                $ret['offline_module'][] = (int) $item['module_type'];
            }
        }
        return $ret;
    }

    /**
     * 批量获取节点的状态
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    public function batchGetNodeStatus(array $nodeUuidList): array
    {
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $sql = "SELECT status, node_uuid FROM bd_node WHERE node_uuid IN ($nodeUuids) ";
        $nodeData = $this->dbSelect($sql);
        $nodeMap = [];
        foreach ($nodeData as $nodeInfo) {
            $nodeMap[$nodeInfo['node_uuid']] = $nodeInfo['status'];
        }
        $sql = "SELECT online_flag, module_type, node_uuid FROM bd_module_server WHERE node_uuid IN ($nodeUuids) ";
        $moduleData = $this->dbSelect($sql);
        $moduleMap = [];
        foreach ($moduleData as $moduleInfo) {
            if (!isset($moduleMap[$moduleInfo['node_uuid']])) {
                $moduleMap[$moduleInfo['node_uuid']] = [];
            }
            $moduleMap[$moduleInfo['node_uuid']][] = $moduleInfo;
        }
        $setFlag = xphp_get_config('app', 'FLAG');
        $allNodeStatus = xphp_get_config('node', 'NODE_OPERATE_STATUS', 'resources');
        $nodeStatusList = [];

        foreach ($nodeUuidList as $nodeUuid) {
            $tmp = [
                'online_flag' => false,
                'deploy_flag' => false,
                'offline_module' => [],
                'node_uuid' => $nodeUuid,
                'status' => $allNodeStatus['OFFLINE'],
            ];
            if (!isset($nodeMap[$nodeUuid])) {
                $nodeStatusList[] = $tmp;
                continue;
            }
            if (!isset($moduleMap[$nodeUuid])) {
                $nodeStatusList[] = $tmp;
                continue;
            }
            $tmp['deploy_flag'] = true;
            $tmp['status'] = intval($nodeMap[$nodeUuid]);
            if ($tmp['status'] == $allNodeStatus['OFFLINE']) {  // 离线
                $nodeStatusList[] = $tmp;
                continue;
            }
            $tmp['online_flag'] = true;
            foreach ($moduleMap[$nodeUuid] as $moduleInfo) {
                if ($moduleInfo['online_flag'] == $setFlag['UNSET']) {
                    $tmp['online_flag'] = false;
                    $tmp['offline_module'][] = intval($moduleInfo['module_type']);
                }
            }
            $nodeStatusList[] = $tmp;
        }
        return $nodeStatusList;
    }

    /**
     * 获取单个节点信息，提供给node控制器使用
     * @param string $nodesUuid uuid
     * @return array|stdClass|bool
     */
    public function getNode(string $nodesUuid)
    {
        if (!$this->dbSelect("SELECT node_uuid FROM bd_node WHERE node_uuid = ?", [$nodesUuid])) {
            return false;
        }
        return $this->getNodes(['nodes_uuid' => $nodesUuid]);
    }

    /**
     * 添加节点
     * @param array $params 数据
     * @return array
     */
    public function addNode($params = [])
    {
        // 集群的网卡配置
        $clusterHandler = new Cluster();
        if ($clusterHandler->getClusterIsConfig()) {
            // 集群已配置，添加新节点需要配置网卡信息
            // FIXME
        }

        $addResult = $this->service()->nodeOperateService(
            $params['common_server_info'],
            $params['remote_ip'],
            (int) $params['remote_port'],
            xphp_get_config('node', 'NODE_OPERATE_TYPE', 'resources')['add'],
            v1_parse_bool_to_flag($params['force_flag'] ?? false),
            '',
            intval($params['node_function'])
        );
        $operate = $this->nodeOpcode->getOpcodeDes('NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER');
        if (!$addResult['result']) {
            if ($addResult['errorCode'] == 51505) {
                $this->muOpResult(
                    false,
                    $operate,
                    xphp_get_lang('WEB_NODE_ADD_TIMEOUT_TIPS'),
                    '',
                    $addResult['errorCode']
                );
            }
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_ADD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_ADD_SUCCESS'));
    }

    /**
     * 删除节点逻辑
     * @param string $nodesUuid 节点uuid
     * @return array
     */
    public function deleteNode(string $nodesUuid): array
    {
        // 检查节点是否存在
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $this->checkAuthBySourceUuid($nodesUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        //检测是否是本地节点,本地节点不能删除
        if ($nodeInfo['node_type'] == xphp_get_config('app', 'NODETYPE')['MASTER']) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_LOCAL_TIPS'), false, 0);
        }

        //检测节点是否还有存储使用
        $storageList = $this->getStorageInfoByNodeUuid($nodesUuid);
        if (count($storageList)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_STORAGE_TIPS'), false, 0);
        }

        // 检查节点是否处于资源池中
        $sql = "SELECT bnpl.node_uuid, bnp.node_pool_uuid, bnp.node_pool_nickname
                FROM bd_node_pool_list bnpl
                    INNER JOIN bd_node_pool bnp ON bnpl.node_pool_uuid = bnp.node_pool_uuid
                WHERE bnpl.node_uuid = ? ";
        $nodePoolData = $this->dbSelect($sql, [$nodesUuid]);
        if (is_array($nodePoolData) && $nodePoolData) {
            $nodePoolNameList = array_column($nodePoolData, 'node_pool_nickname');
            return $this->sendResult(sprintf(xphp_get_lang('WEB_NODE_DELETE_IN_POOL'), implode("、", $nodePoolNameList)), false, 0);
        }

        // 检查节点是否处于集群中
        $sql = "SELECT node_uuid, cluster_uuid FROM bd_cluster_node WHERE node_uuid = ? ";
        $clusterNodeData = $this->dbSelect($sql, [$nodesUuid]);
        if (is_array($clusterNodeData) && $clusterNodeData) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_IN_CLUSTER'), false, 0);
        }

        $deleteResult = $this->service()->nodeOperateService(
            [],
            $nodeInfo['ip'],
            22711,  // 固定为22711
            xphp_get_config('node', 'NODE_OPERATE_TYPE', 'resources')['delete'],
            v1_parse_bool_to_flag(false),
            '',
            0,
            $nodeInfo['node_uuid']
        );
        $operate = xphp_get_lang('WEB_BD_NOT_MASTER_NODE_DELETE');
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_SUCCESS'));
    }

    /**
     * 批量删除节点
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    public function deleteNodeList(array $nodeUuidList): array
    {
        // 检查节点是否存在
        $checkResult = $this->checkNodeList($nodeUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeList = $checkResult['data'];

        foreach ($nodeList as $nodeInfo) {
            //检测是否是本地节点,本地节点不能删除
            if ($nodeInfo['node_type'] == xphp_get_config('app', 'NODETYPE')['MASTER']) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_LOCAL_TIPS'), false, 0);
            }
        }

        //检测节点是否还有存储使用
        $allResourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
        foreach ($nodeUuidList as $nodeUuid) {
            $this->checkAuthBySourceUuid($nodeUuid, $allResourceType['NODE']);

            $storageList = $this->getStorageInfoByNodeUuid($nodeUuid);
            if (count($storageList)) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_STORAGE_TIPS'), false, 0);
            }

            // 检查节点是否处于集群中
            $sql = "SELECT node_uuid, cluster_uuid FROM bd_cluster_node WHERE node_uuid = ? ";
            $clusterNodeData = $this->dbSelect($sql, [$nodeUuid]);
            if (is_array($clusterNodeData) && $clusterNodeData) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_IN_CLUSTER'), false, 0);
            }
        }

        $operate = xphp_get_lang('WEB_BD_NOT_MASTER_NODE_DELETE');
        foreach ($nodeList as $nodeInfo) {
            // 检查节点是否处于资源池中
            $sql = "SELECT bnpl.node_uuid, bnp.node_pool_uuid, bnp.node_pool_nickname
                FROM bd_node_pool_list bnpl
                    INNER JOIN bd_node_pool bnp ON bnpl.node_pool_uuid = bnp.node_pool_uuid
                WHERE bnpl.node_uuid = ? ";
            $nodePoolData = $this->dbSelect($sql, [$nodeInfo['node_uuid']]);
            if (is_array($nodePoolData) && $nodePoolData) {
                $nodePoolNameList = array_column($nodePoolData, 'node_pool_nickname');
                return $this->sendResult(sprintf(xphp_get_lang('WEB_NODE_DELETE_IN_POOL'), implode("、", $nodePoolNameList)), false, 0);
            }

            $deleteResult = $this->service()->nodeOperateService(
                [],
                $nodeInfo['ip'],
                22711,  // 固定为22711
                xphp_get_config('node', 'NODE_OPERATE_TYPE', 'resources')['delete'],
                v1_parse_bool_to_flag(false),
                '',
                0,
                $nodeInfo['node_uuid']
            );
            if (!$deleteResult['result']) {
                $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
                return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_ERROR'), false, 0);
            }
        }

        return $this->sendResult(xphp_get_lang('WEB_NODE_DELETE_SUCCESS'));
    }

    /**
     * 修改节点 & 修改节点名称
     * @param string $nodesUuid 节点uuid
     * @param array  $params    参数数据
     * @return array
     */
    public function editNode(string $nodesUuid, array $params): array
    {
        // 检查节点是否存在
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $this->checkAuthBySourceUuid($nodesUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        // 节点处于集群中，不能取消管理节点
        $allNodeFunction = xphp_get_config('node', 'NODE_FUNCTION', 'resources');
        if ($params['node_function'] == $allNodeFunction['CALCULATION']) {  // 只有计算功能
            $sql = "SELECT cluster_uuid FROM bd_cluster_node WHERE node_uuid = ? ";
            $clusterData = $this->dbSelect($sql, [$nodesUuid]);
            if ($clusterData && is_array($clusterData)) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_ONLY_CALCULATION_IN_CLUSTER'), false, 0);
            }
        } elseif ($params['node_function'] == $allNodeFunction['MANAGEMENT']) {  // 只有管理功能
            // 处于资源池的节点不能取消计算功能
            $sql = "SELECT node_pool_uuid FROM bd_node_pool_list WHERE node_uuid = ? ";
            $nodePoolData = $this->dbSelect($sql, [$nodesUuid]);
            if ($nodePoolData && is_array($nodePoolData)) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_ONLY_MANAGEMENT_IN_NODE_POOL'), false, 0);
            }
            // 该节点存在存储或被共享存储挂载，不能取消计算功能
            $storageList = $this->getStorageInfoByNodeUuid($nodesUuid);
            if (count($storageList)) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_ONLY_MANAGEMENT_WITH_STORAGE'), false, 0);
            }
        }

        $allNodeType = xphp_get_config('app', 'NODETYPE');
        $operate = $this->nodeOpcode->getOpcodeDes('NODE_SYS_OP_MODIFY_MASTER_NODE_INFO');
        if ($params['node_type'] == $allNodeType['MASTER']) {  // 修改主节点名称由php处理
            $modifyResult = $this->service()->masterNodeOperateService(
                $nodesUuid,
                $params['node_nickname'],
                intval($params['node_function'])
            );
        } else {  // 修改子节点由后台处理
            $modifyResult = $this->service()->nodeOperateService(
                $params['common_server_info'],
                $params['remote_ip'],
                intval($params['remote_port']),
                xphp_get_config('node', 'NODE_OPERATE_TYPE', 'resources')['modify'],
                v1_parse_bool_to_flag(false),
                $params['node_nickname'],
                intval($params['node_function']),
                $nodesUuid
            );
            $operate = $this->nodeOpcode->getOpcodeDes('NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER');
        }
        if (!$modifyResult['result']) {
            if ($modifyResult['errorCode'] == 51505) {
                $this->muOpResult(
                    false,
                    $operate,
                    xphp_get_lang('WEB_NODE_ADD_TIMEOUT_TIPS'),
                    '',
                    $modifyResult['errorCode']
                );
            }
            $this->muOpResult(false, $operate, $modifyResult['msg'], 0, $modifyResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_EDIT_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_EDIT_SUCCESS'));
    }

    /**
     * 得到所有节点信息
     * @param unknown $params 数据
     * @return json
     */
    public function getNodeInfo($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'node_nickname', 'ip', 'detail', 'register_time', 'auto_upgrade_flag');

        $sql = "select node_type, detail, ip, node_uuid, host_name, node_nickname,
                unix_timestamp(register_time) register_time, auto_upgrade_flag from bd_node ";
        $sqlCount = "select count(ip) as total from bd_node ";

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, array($start, $length));
        $count = $this->dbSelect($sqlCount, array());

        $records = array();
        $records['data'] = array();
        $id = $start + 1;

        foreach ($data as $d) {
            $nodeDeployStatus = $this->getNodeDeployStatus($d['node_uuid']);
            $nodeAllStatus = $this->getNodeAllStatus($d['node_uuid']);
            $info = json_decode($d['detail'], true);
            if (!empty($info)) {
                $version = $info['version']['major_version'] . '.' .
                    $info['version']['minor_version'] . '.' . $info['version']['build_number'];
            } else {
                $version = xphp_get_config('app', 'NULLSPACE');
            }
            $nodeName = $this->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']);
            if (intval($d['node_type']) == xphp_get_config('app', 'FLAG')['SET']) {
                $nodeName .= '(' . xphp_get_lang('UI_PALTFORM_MASTER_NODE') . ')';
            } else {
                $nodeName .= '(' . xphp_get_lang('UI_PALTFORM_CHILD_NODE') . ')';
            }
            $records['data'][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['node_uuid'] . '">',
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
            );
        }

        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count[0]['total'];
        $records['recordsFiltered'] = $count[0]['total'];

        return json_encode($records);
    }

    /**
     * 得到节点是否支持远程部署描述
     * @param string $flag flag
     * @return string
     */
    private function getNodeUpgradeDes($flag)
    {
        if ($flag == xphp_get_config('app', 'FLAG')['SET']) {
            return xphp_get_lang('WEB_NODE_SUPPORT');
        } else {
            return xphp_get_lang('WEB_NODE_NO_SUPPORT');
        }
    }

    /**
     * 得到所有不在线的节点进程描述(存储有调用)
     * @param array $modules 模块
     * @return string
     */
    public function getOffLineModuleDes($modules)
    {
        if (!$modules) {
            return '';
        }
        $ptDes = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $list = [];
        foreach ($modules as $m) {
            $list[] = $ptDes[$m];
        }
        return implode(',', $list) . xphp_get_lang('WEB_NODE_PROCESS_OFFLINE');
    }

    /**
     * 得到节点部署状态
     * @param string $nodeuuid uuid
     * @return boolean
     */
    public function getNodeDeployStatus($nodeuuid)
    {
        $sql = "select count(module_uuid) as total from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        return !empty($data[0]['total']);
    }

    /**
     * 得到表格展示的节点名
     * @param $ip       ip
     * @param $nickname name
     * @param $hostname hostname
     * @return mixed
     */
    public function getNodeGridName($ip, $nickname, $hostname)
    {
        if ($ip == $nickname || empty($nickname)) {
            return $hostname;
        } else {
            return $nickname;
        }
    }

    /**
     * 得到节点信息(修改用)
     * @param unknown $params 数据
     * @return josn
     */
    public function getEditNodeInfo($params)
    {
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
     * 得到节点总状态(存储也要调用,所以PUBLIC)
     * @param string $nodeuuid uuid
     * @return array('flag'=> boolean, 'module'=>array(modules))
     */
    public function getNodeAllStatus(string $nodeuuid)
    {
        $sql = "select module_type, online_flag from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));

        $flag = true;

        $module = array();
        foreach ($data as $d) {
            if ($d['online_flag'] == xphp_get_config('app')['FLAG']['UNSET']) {
                $flag = false;
                $module[] = $d['module_type'];
            }
        }
        if (empty($data)) {
            $flag = false; //如果没有记录
        }
        return array(
            'flag' => $flag,
            'module' => $module
        );
    }

    /**
     * 根据任务UUID得到所在节点UUID
     * @param string  $taskUUID 任务uuid
     * @param boolean $flag     是否一定要获取节点uuid，默认是，如果不是的话，节点不在线会返回主节点uuid
     * @return string
     */
    public function getNodeUUIDWithTaskUUID(string $taskUUID, $flag = true): string
    {
        $sql = "select node_uuid from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $nodeuuid = $data[0]['node_uuid'];

        $nodeAllStatus = $this->getNodeAllStatus($nodeuuid);

        if (!$nodeAllStatus['flag'] && !$flag) {
            //节点不在线就需要获取主节点uuid
            $nodeuuid = $this->getLocalNodeUUID();
        }

        return $nodeuuid;
    }

    /**
     * 得到本地节点UUID
     * @see \app\v1\common\service\Base::getLocalNodeUuid() 用于获取后台服务使用
     * @return string
     */
    public function getLocalNodeUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'NODETYPE')['MASTER']));
        return $data[0]['node_uuid'] ?? '';
    }

    /**
     * 得到自动选择的节点(任务最少的节点)
     * @return string nodeuuid
     */
    public function getAutoFindNode()
    {
        $allAvailableNode = $this->getAddStorageNodeSelect();
        $allAvailableNode = $this->filterDisableNode($allAvailableNode);

        if (empty($allAvailableNode)) {
            //如果没有可用的节点
            return false;
        }
        if (1 == count($allAvailableNode)) {
            //如果只查找到 一个节点
            return $allAvailableNode[0]['uuid'];
        }
        //所有可用节点的UUID
        $allAvailableNodeuuid = array();
        foreach ($allAvailableNode as $node) {
            $allAvailableNodeuuid[] = $node['uuid'];
        }
        //找到所有节点拥有的任务个数,注意下面排序使用了sql语句里面的num
        $sql = "select bn.node_uuid, count(*) as num from bd_task bt, bd_node bn where 
                bt.node_uuid = bn.node_uuid group by bn.node_uuid";
        $data = $this->dbSelect($sql);

        //找到所有使用过的节点
        $allUsedNode = array();
        $allUsedNodeFitler = array();
        foreach ($data as $d) {
            if (in_array($d['node_uuid'], $allAvailableNodeuuid)) {
                //筛选可用节点中使用过的节点
                $allUsedNode[] = $d['node_uuid'];
                $allUsedNodeFitler[] = $d;
            }
        }

        //从可用节点帅选一个从未使用过的节点返回UUID
        foreach ($allAvailableNodeuuid as $uuid) {
            if (!in_array($uuid, $allUsedNode)) {
                return $uuid;
            }
        }
        //到此所有的节点都使用过了,对可用的排个序,取出使用次数最少的,也就是任务数量最少的节点

        $allUsedNodeFitler = v1_array_sort($allUsedNodeFitler, 'num', 'asc', 0, -1);

        return $allUsedNodeFitler[0]['node_uuid'];
    }

    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     * @return array
     */
    public function getAddStorageNodeSelect()
    {
        $sql = "select ip, node_uuid, host_name, node_nickname, node_type from bd_node order by node_type";
        $data = $this->dbSelect($sql, array());
        $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
        $list = array();
        $resourceList = array();
        $loginUser = xphp_get_user_info();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($loginUser['tenantuuid'])) {
            $resourceUuid = $this->getNodeSourceUuidByType(1);
            if (empty($resourceUuid)) {
                return [];
            }
            $resourceList = $resourceUuid;
        }
        $softwareversion = xphp_get_config('app', 'SOFTWARE_VERSION');
        foreach ($data as $d) {
            //如果是租户内部检查是否有该资源
            if (!empty($loginUser['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) {
                continue;
            }

            if (
                $softwareType == $softwareversion['EN_FREE_EDITION'] ||
                $softwareType == $softwareversion['STANDARD_EN'] ||
                $softwareType == $softwareversion['ESSENTIAL_EN']
            ) {
                if (intval($d['node_type']) != xphp_get_config('app', 'FLAG')['SET']) {
                    continue;
                }
            }
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if ($nodeStatus['flag']) {
                //如果节点状态正常
                $list[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                    'type' => intval($d['node_type'])
                );
            }
        }
        return $list;
    }

    /**
     * 得到节点展示名称
     * @param string $ip       ip
     * @param string $nickname name
     * @param string $hostname host
     * @return string
     */
    public function getNodeShowName($ip, $nickname, $hostname)
    {
        if (empty($ip) && empty($nickname) && empty($hostname)) {
            return '--';
        }
        //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
        if ($ip == $nickname || empty($nickname)) {
            $name = $hostname . '(' . $ip . ')';
        } else {
            $name = $nickname . '(' . $ip . ')';
        }
        return $name;
    }

    /**
     * 过滤掉不可用的节点(节点上没有可用存储)
     * @param array $allAvailableNode arr
     *                                uuid => '', text => ''
     * @return array
     */
    private function filterDisableNode($allAvailableNode)
    {
        if (empty($allAvailableNode)) {
            //没有可用的节点
            $this->muOpResult(
                false,
                xphp_get_lang('WEB_NODE_NO_BACKUP_NODE'),
                xphp_get_lang('WEB_NODE_CHECK_NODE_STATUS')
            );
        }
        $availableNode = array();
        foreach ($allAvailableNode as $node) {
            $sql = "select count(storage_id) as num from bd_storage_resource where status = ? and node_uuid = ?";
            $data = $this->dbSelect($sql, [xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE'], $node['uuid']]);
            if ($data[0]['num'] > 0) {
                $availableNode[] = $node;
            }
        }
        if (empty($availableNode)) {
            //可选择的节点上都没有可用的存储
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_NODE_NO_FIND_STORAGE'),
                xphp_get_lang('WEB_NODE_NO_FIND_STORAGE_ALL')
            ));
        }
        return $availableNode;
    }

    /**
     * 检测用户选择的节点是否都支持远程部署
     * @param unknown $params 数据
     * @return string
     */
    public function checkAutoDeploy($params)
    {
        $uuids = $params['uuids'];
        $this->paramsCheck($uuids);
        $uuidStr = '';
        foreach ($uuids as $uuid) {
            $uuidStr .= "'" . $uuid . "',";
        }
        $uuidStr = substr($uuidStr, 0, -1);
        $sql = "select auto_upgrade_flag from bd_node where node_uuid in ($uuidStr)";
        $data = $this->dbSelect($sql);
        $allFlag = array();
        foreach ($data as $d) {
            $allFlag[] = $d['auto_upgrade_flag'];
        }
        $result = !in_array(xphp_get_config('app', 'FLAG')['UNSET'], $allFlag);
        return $this->muOpResult($result, '');
    }

    /**
     * 得到即将部署节点信息
     * @param unknown $params 数据
     * @return string
     */
    public function getDeployNodeInfo($params)
    {
        $uuids = $params['uuids'];
        $this->paramsCheck($uuids);
        $uuids = explode(',', $uuids);
        $uuidStr = '';
        foreach ($uuids as $uuid) {
            $uuidStr .= "'" . $uuid . "',";
        }
        $uuidStr = substr($uuidStr, 0, -1);
        $sql = "select ip, node_nickname, host_name, node_uuid from bd_node where node_uuid in ($uuidStr)";
        $data = $this->dbSelect($sql);
        $info = array();
        foreach ($data as $d) {
            $info[] = array(
                'name' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                'uuid' => $d['node_uuid']
            );
        }
        return json_encode($info);
    }

    /**
     * 得到所有节点软件列表
     * @param unknown $params 数据
     * @return string
     */
    public function getSoftInfo($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];

        $rStart = $start + 1;
        $rLength = $start * $length + $length;


        $packagePath = xphp_get_config('app', 'NODE_SOFT');
        $cmd = 'cd ' . $packagePath;
        $cmd .= ";ls -l -t --time-style=long-iso |grep tar.gz|sed -n '" .
            $rStart . ', ' . $rLength . "p'";
        $cmd .= "| tr : \" \" | awk '{print $5, $6, $7, $8, $9}'";
        exec($cmd, $data);

        $countCmd = 'cd ' . $packagePath;
        $countCmd .= ';ls -l -t --time-style=long-iso |grep tar.gz|wc -l';
        exec($countCmd, $count);

        $records = array('data' => array());
        $i = 1;
        foreach ($data as $d) {
            $package = explode(' ', $d);
            $records['data'][] = array(
                '<input type="checkbox" name="id[]" value="' .
                xphp_get_config('app', 'NODE_SOFT_RE') . $package[4] . '">',
                $i++,
                $package[4],
                v1_calsize($package[0], true),
                $package[1] . ' ' . $package[2] . ':' . $package[3] . ':00',
            );
        }

        $records['draw'] = $draw;
        $records['recordsTotal'] = intval($count[0]);
        $records['recordsFiltered'] = intval($count[0]);

        return json_encode($records);
    }

    /**
     * 得到可用的软件包(部署下拉使用)
     * @param unknown $params 数据
     * @return string
     */
    public function getSoftSelectInfo($params = [])
    {
        $packagePath = xphp_get_config('app', 'NODE_SOFT');
        $cmd = 'cd ' . $packagePath;
        $cmd .= ';ls -l -t --time-style=long-iso |grep tar.gz';
        $cmd .= "| tr : \" \" | awk '{print $5, $6, $7, $8, $9}'";
        exec($cmd, $data);

        $info = array();
        foreach ($data as $d) {
            $package = explode(' ', $d);
            $info[] = array(
                'text' => $package[4] . ' (' . $package[1] . ' ' . $package[2] . ':' . $package[3] . ':00' . ')',
                'name' => $package[4]
            );
        }
        return json_encode($info);
    }

    /**
     * 上传节点软件
     * @param unknown $params 数据
     * @return string
     */
    public function uploadNodeSoft($params = [])
    {
        $uploadfile = xphp_get_config('app', 'NODE_SOFT_INFO')['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        if ($files) {
            //检测上传文件状态
            if (0 != $files['error']) {
                return $this->muOpResult(false, xphp_get_lang('WEB_NODE_UPLOAD_FILE'));
            }
            //检测文件后缀名
            $arr = explode('.', $files['name']);
            $suffixesArr = explode('|', $uploadfile['suffixes']);
            if (!in_array($arr[count($arr) - 1], $suffixesArr)) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_NODE_UPLOAD_FILE'),
                    xphp_get_lang('WEB_NODE_UPLOAD_TYPE'),
                    'error'
                );
            }
            //检测上传文件大小
            if ($files['size'] > $uploadfile['size']) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_NODE_UPLOAD_FILE'),
                    xphp_get_lang('WEB_NODE_UPLOAD_SIZE'),
                    'error'
                );
            }
            //保存临时文件
            $count = file_put_contents(
                xphp_get_config('app', 'NODE_SOFT') . $files['name'],
                file_get_contents($files['tmp_name'])
            );
            if ($count > 0) {
                return $this->muOpResult(
                    true,
                    xphp_get_lang('WEB_NODE_UPLOAD_FILE'),
                    '',
                    'success',
                    0,
                    xphp_get_config('app', 'NODE_SOFT_RE')
                );
            }
        }
        return $this->muOpResult(false, xphp_get_lang('WEB_NODE_UPLOAD_FILE'));
    }

    /**
     * 删除节点软件
     * @param unknown $params 数据
     * @return string
     */
    public function deleteNodeSoft($params)
    {
        $paths = $params['paths'];
        $names = ' ';
        foreach ($paths as $path) {
            $pathArr = explode('/', $path);
            $names .= $pathArr[count($pathArr) - 1] . ' ';
        }
        $cmd = 'cd ' . xphp_get_config('app', 'NODE_SOFT') . '; rm -rf ' . $names;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        return $this->muOpResult($mbResult['result'], xphp_get_lang('WEB_NODE_DELETE_SOFT'));
    }

    /**
     * 得到恢复的时候所有有虚拟机|数据库备份数据的节点列表
     * @param array $params 数据
     * @return string
     */
    public function getTimepointAllNode(array $params)
    {
        $moduleType = intval($params['module_type']);
        $submoduleType = intval($params['sub_module_type']);
        $dataFlag = $params['data_flag'];    //备份数据标志
        $trueNode = $params['true_node'];    //是不是得到实实在在的节点,没有所有节点那一项
        $flag = xphp_get_config('app', 'FLAG');

        $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
        $sql = "select bsr.node_uuid, bbt.real_node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr
                where bbt.storage_uuid = bsr.storage_uuid and bbt.user_uuid = ?
                    and bbt.deleted_flag = ? and bbt.available_flag = ?
                    and bbt.import_flag = ? and bbt.data_local_flag = ? ";
        //恢复增加副本点所在获取
        if (!$dataFlag) {
            $mODULETYPE = xphp_get_config('module', 'MODULE_TYPE');
            $sUBMODULETYPE = xphp_get_config('module', 'SUBMODULE_TYPE');
            $tASKTYPE = xphp_get_config('task', 'TASKTYPE');
            switch ($moduleType) {
                case $mODULETYPE['VM']:
                    $moduleTypeArr = $mODULETYPE['VM'];
                    $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ',' . $tASKTYPE['BACKUP_COPY'] . ',' .
                        $tASKTYPE['BACKUP_COPY_FETCH'] . ',' . $tASKTYPE['ARCHIVE'] . ',' .
                        $tASKTYPE['ARCHIVE_FETCH'] . ') ';
                    break;
                case $mODULETYPE['FS']:
                    switch ($submoduleType) {
                        case $sUBMODULETYPE['FS']:
                            $moduleTypeArr = $mODULETYPE['FS'];
                            $submoduleTypeArr = $sUBMODULETYPE['FS'];
                            $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ',' . $tASKTYPE['BACKUP_COPY']
                                . ',' . $tASKTYPE['BACKUP_COPY_FETCH'] . ') ';

                            break;
                        case $sUBMODULETYPE['NAS']:
                            $moduleTypeArr = $mODULETYPE['FS'];
                            $submoduleTypeArr = $sUBMODULETYPE['NAS'];
                            $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ',' . $tASKTYPE['BACKUP_COPY']
                                . ',' . $tASKTYPE['BACKUP_COPY_FETCH'] . ') ';

                            break;
                        case $sUBMODULETYPE['HADOOP']:
                            $moduleTypeArr = $mODULETYPE['FS'];
                            $submoduleTypeArr = $sUBMODULETYPE['HADOOP'];
                            $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ',' . $tASKTYPE['BACKUP_COPY']
                                . ',' . $tASKTYPE['BACKUP_COPY_FETCH'] . ') ';

                            break;
                        case $sUBMODULETYPE['OBS']:
                            $moduleTypeArr = $mODULETYPE['FS'];
                            $submoduleTypeArr = $sUBMODULETYPE['OBS'];
                            $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ',' . $tASKTYPE['BACKUP_COPY']
                                . ',' . $tASKTYPE['BACKUP_COPY_FETCH'] . ') ';

                            break;
                        default:
                            break;
                    }

                    break;
                case $mODULETYPE['NAS']:
                    $moduleTypeArr = $mODULETYPE['NAS'];
                    $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ',' . $tASKTYPE['NAS_BACKUP_COPY']
                        . ',' . $tASKTYPE['NAS_BACKUP_COPY_FETCH'] . ') ';
                    break;
                case $mODULETYPE['DB']:
                    $moduleTypeArr = $mODULETYPE['DB'];
                    $sql .= ' and bbt.task_type in (' . $tASKTYPE['DB_BACKUP'] . ',' . $tASKTYPE['DB_BACKUP_COPY']
                        . ',' . $tASKTYPE['DB_BACKUP_COPY_FETCH'] . ') ';
                    break;
                case $mODULETYPE['OS']:
                    $moduleTypeArr = $mODULETYPE['OS'];
                    $sql .= ' and bbt.task_type in (' . $tASKTYPE['OS_BACKUP'] . ',' . $tASKTYPE['OS_BACKUP_COPY']
                        . ',' . $tASKTYPE['OS_BACKUP_ARCHIVE'] . ') ';
                    break;
                case $mODULETYPE['M365']:
                    $moduleTypeArr = $mODULETYPE['M365'];
                    $sql .= ' and bbt.task_type in (' . $tASKTYPE['BACKUP'] . ') ';
                    break;
                case $mODULETYPE['VOL_CDP']:
                    $moduleTypeArr = $mODULETYPE['VOL_CDP'];
                    $sql .= ' and bbt.task_type in (' . $tASKTYPE['VOL_CDP_BACKUP'] . ') ';
                    break;
            }
        }
        $loginUser = xphp_get_user_info();
        $sql .= ' and bbt.module_type = ' . $moduleTypeArr . ' ';

        if (!empty($submoduleType)) { // 如果存在子模块类型
            $sql .= ' and bbt.sub_module_type = ' . $submoduleTypeArr . ' ';
        }
        //        $sql .= " group by bbt.storage_uuid";
        $sqlParams = array($loginUser['userUuid'], $flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $uuidArr = array();
        //获取对应节点
        foreach ($data as $d) {
            $uuidArr[] = !empty($d['real_node_uuid']) ? $d['real_node_uuid'] : $d['node_uuid'];
        }
        $info = array();
        if (!$trueNode) {
            $info = array(
                array(
                    'node_uuid' => 0,
                    'text' => xphp_get_lang('WEB_NODE_ALL_NODE'),
                )
            );
        }
        $uuidStr = implode("','", $uuidArr);
        $sql = "select ip, node_uuid, host_name, node_nickname, node_type from bd_node 
                where node_uuid in ('$uuidStr') group by node_uuid";
        $data = $this->dbSelect($sql);
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($loginUser['tenantuuid'])) {
            $resourceHandler = new Index();
            $resourceInfo = $resourceHandler->pGetTenantAllResource(
                $_SESSION['tenantuuid'],
                xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']
            );
            if (!empty($resourceInfo)) {
                foreach ($resourceInfo as $r) {
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d) {
            //如果是租户内部检查是否有该资源
            if (!empty($loginUser['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) {
                continue;
            }

            $softwareversion = xphp_get_config('app', 'SOFTWARE_VERSION');

            if (
                $softwareType == $softwareversion['EN_FREE_EDITION'] ||
                $softwareType == $softwareversion['STANDARD_EN'] ||
                $softwareType == $softwareversion['ESSENTIAL_EN']
            ) {
                if (intval($d['node_type']) != xphp_get_config('app', 'FLAG')['SET']) {
                    continue;
                }
            }
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            if ($nodeStatus['flag']) {
                //如果节点状态正常
                $info[] = array(
                    'node_uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                );
            }
        }
        return $info;
    }

    /**
     * 根据节点uuid获取节点名称
     * @param string $nodeuuid uuid
     * @return string
     */
    public function getNodeName($nodeuuid)
    {
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ? ";
        $data = $this->dbSelect($sql, array($nodeuuid));
        return $this->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']);
    }

    /**
     * 根据节点uuid数组批量获取节点名称
     *
     * @param [type] $node_uuids
     * @return void 以UUID为键、名称为值的映射数组
     */
    public function getNodesName($node_uuids)
    {
        if (empty($node_uuids)) {
            return [];
        }

        $node_uuids = array_unique(array_filter($node_uuids));
        $node_uuids = array_values($node_uuids);

        if (empty($node_uuids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($node_uuids), '?'));
        $sql = "select node_uuid, ip, host_name, node_nickname from bd_node where node_uuid IN ($placeholders)";
        $data = $this->dbSelect($sql, $node_uuids);

        $node_names_map = [];
        foreach ($data as $row) {
            $node_names_map[$row['node_uuid']] = $this->getNodeShowName($row['ip'], $row['node_nickname'], $row['host_name']);
        }

        return $node_names_map;
    }

    /**
     * 获取节点的网络信息
     * @param  $params 数据
     * @return array|bool
     */
    public function getNodesNetworkCard($params)
    {
        if (!$this->dbSelect("SELECT node_uuid FROM bd_node_network WHERE node_uuid = ?", [$params['nodes_uuid']])) {
            // 没有这个节点的网络信息
            return false;
        }

        $fields = [
            'node_uuid',
            'network_uuid',
            'alias_name',
            'ip',
            'port',
            'network_order',
            'type',
            'mac',
            'netmask',
            'gateway',
            'network_name',
            'dns',
        ];
        $sql = "SELECT " . implode(', ', $fields) . " FROM bd_node_network WHERE node_uuid = ? order by network_order ASC";


        $selectData = $this->dbSelect($sql, [$params['nodes_uuid']]);

        $nodeData = [];
        foreach ($selectData as $row) {
            $tmpRow = [];
            foreach ($fields as $field) {
                $tmpRow[$field] = $row[$field];
            }
            $nodeData[] = $tmpRow;
        }
        return $nodeData;
    }

    /**
     * 获取本地主节点UUID
     * @return string
     */
    public function getMasterNodeUuid(): string
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }

    /**
     * 根据时间点UUID得到所在节点UUID
     * @param mixed $timepointUUID 时间点UUID
     * @return string
     */
    public function getNodeUUIDWithTimepointUUID($timepointUUID): string
    {
        $timepointUUID = is_array($timepointUUID) ? $timepointUUID : [$timepointUUID];
        $storageData = (new DataManage())->getStorageInfoByTimepoints($timepointUUID);
        return $storageData[0]['node_uuid'];
    }

    /**
     * 得到某个节点下的可用存储(新建备份任务,修改备份任务使用)
     * @param array $params 参数
     * @return array
     */
    public function getBackupStorageList(array $params): array
    {
        $exchangeCloudFlag = $params['exchange_cloud_flag'];//用于exchange云存储获取
        $copyFlag = $params['copy_flag'];//用于副本初始化可用存储列表
        $cloudFlag = $params['cloud_flag'];//用于云存储初始化可用存储列表
        $copyBackFlag = $params['copy_back_flag'];//用于副本拉回初始化可用存储列表
        $archivebackFlag = $params['archiveback_flag']; //用于归档回传初始化化可用存储
        $copySourceFlag = $params['copySourceFlag'];
        $allFlag = $params['all_flag']; //当前任务高级搜索使用，返回包含云存储和副本归档存储
        $lunFlag = $params['lun_flag']; //生产存储

        $nodeuuid = $params['node_uuid'];
        $sql = "select mount_flag,node_uuid,storage_nickname,storage_uuid,
                    storage_type,total_size,free_size,status,error_code,storage_config, worm_flag
                from bd_storage_resource where status = ? and mount_flag = ? 
                and error_code = ? and lan_free_flag = ? ";

        $storageStatusArr = xphp_get_config('resource', 'STORAGE_STATUS');
        $bdStorageUseMode = xphp_get_config('resource', 'BD_STORAGE_USE_MODE');
        $flag = xphp_get_config('app', 'FLAG');

        if (v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'], 'storage_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }
        if ($copyFlag) {
            // 副本目的存储
            $sql .= ' and use_mode = ?';
            $sqlParams = array($storageStatusArr['ONLINE'], $flag['SET'], 0, $flag['UNSET'], $bdStorageUseMode['COPY']);
        } elseif ($copySourceFlag) {
            // 备份存储
            $sql .= '  and storage_type not in (8)';
            $sqlParams = [$storageStatusArr['ONLINE'], $flag['SET'], 0, $flag['UNSET']];
        } elseif ($copyBackFlag) {
            // 副本回传存储
            $sql .= ' and use_mode in (1,2) and storage_type not in (8,9)';
            $sqlParams = array($storageStatusArr['ONLINE'], $flag['SET'], 0, $flag['UNSET']);
        } elseif ($archivebackFlag) {
            // 归档回传存储
            $sql .= ' and node_uuid = ? and use_mode in (1,3) and storage_type not in (8,9)';
            $sqlParams = array($storageStatusArr['ONLINE'], $flag['SET'], 0, $flag['UNSET'], $nodeuuid);
        } elseif ($cloudFlag) {
            // 归档存储
            $sql .= ' and use_mode = ? and storage_type not in (8,9)';
            $sqlParams = array(
                $storageStatusArr['ONLINE'],
                $flag['SET'],
                0,
                $flag['UNSET'],
                $bdStorageUseMode['ARCHIVE']
            );
        } elseif ($exchangeCloudFlag) {
            //备份存储带有云存储    8异地存储  9云存储
            $sql .= ' and use_mode = ? and storage_type not in (8)';
            $sqlParams = array(
                $storageStatusArr['ONLINE'],
                $flag['SET'],
                0,
                $flag['UNSET'],
                $bdStorageUseMode['BACKUP']
            );
        } elseif ($allFlag) {
            // 当前任务高级搜索使用，返回包含云存储和副本归档存储
            $sql .= ' and storage_type not in (8)';
            $sqlParams = array(
                $storageStatusArr['ONLINE'],
                $flag['SET'],
                0,
                $flag['UNSET']
            );
        } elseif ($lunFlag) {
            //生产存储
            $sql .= ' and use_mode = ? and storage_type not in (8) and source_type = ? and storage_type = ?';
            $sqlParams = array(
                $storageStatusArr['ONLINE'],
                $flag['UNSET'],
                0,
                $flag['UNSET'],
                0,
                2,
                $params['type']
            );
        } else {
            // 备份存储
            $sql .= ' and use_mode = ? and storage_type not in (8) ';
            $sqlParams = array(
                $storageStatusArr['ONLINE'],
                $flag['SET'],
                0,
                $flag['UNSET'],
                $bdStorageUseMode['BACKUP']
            );
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();

        $resourceList = array();
        $loginUser = xphp_get_user_info();

        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($loginUser['tenantuuid'])) {
            $resourceInfo = (new Index())->pGetUserAllResource(
                $loginUser['userUuid'],
                xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']
            );
            if (!empty($resourceInfo)) {
                foreach ($resourceInfo as $r) {
                    $resourceList[] = $r['resource_uuid'];
                }
            }
            //获取用户配额
        }

        //磁带获取授权信息
        $systemHandle = new SystemHandler();
        $permission = $systemHandle->getExtensionLicense();
        $permissionArr = $permission['p'];
        $quotaInfo = (new User())->getUserQuotaInfo();
        $storageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        foreach ($data as $d) {
            $storageuuid = $d['storage_uuid'];
            $storagetype = intval($d['storage_type']);
            if (!empty($nodeuuid)) {
                //共享存储绑定多个节点检查
                if (in_array($storagetype, [$storageTypeArr['NFS'], $storageTypeArr['CIFS'], $storageTypeArr['CLOUD']])) {
                    $nodeList = Storage::instance()->getNasMountPointList($storageuuid, $storagetype);
                    $list = array();
                    foreach ($nodeList as $n) {
                        $list[] = $n['node_uuid'];
                    }
                    if (!in_array($nodeuuid, $list))
                        continue;
                } else if ($nodeuuid != $d['node_uuid']) {
                    continue;
                }
            }
            //如果是租户内部检查是否有该资源
            if (!empty($loginUser['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) {
                continue;
            }
            //如果当前存储不是云存储的情况
            if ($d['storage_type'] != xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']) {
                //exchange排除不是当前节点的存储
                if ($exchangeCloudFlag && $d['node_uuid'] != $nodeuuid) {
                    continue;
                }
            }
            //如果当前存储不是云存储的情况
            if ($d['storage_type'] != xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']) {
                //当前任务排除不是当前节点的非云存储
                if ($allFlag && $d['node_uuid'] != $nodeuuid) {
                    continue;
                }
            }
            $nodeAllStatus = $this->getNodeAllStatus($d['node_uuid']);
            $storageStatus = $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));

            if ($storageStatus != $storageStatusArr['ONLINE'] && !$lunFlag) {
                //生产存储不需要这个判断
                continue;
            }

            if (!in_array('tape_manage', $permissionArr) && $d['storage_type'] == 10) {
                //磁带没有授权不显示
                continue;
            }

            $nodeName = $this->getNodeName($d['node_uuid']);
            $name = '';

            //副本|归档没有选中节点，需要显示节点信息
            if ($copyFlag || $copyBackFlag || $cloudFlag || $archivebackFlag) {
                if ($d['storage_type'] == 8) {
                    $storageConfig = json_decode($d['storage_config'], true);
                    $nodeIp = $storageConfig['remote_ip'];
                    $name = ', ' . xphp_get_lang('WEB_PLATFORM_DES_NODE') . ': ' . $nodeIp;
                }
                if ($d['storage_type'] != 9 && $d['storage_type'] != 8) {
                    $name = ', ' . xphp_get_lang('WEB_PLATFORM_DES_NODE') . ': ' . $nodeName;
                }
            }
            $storageDes = $d['storage_nickname'];
            if (empty($loginUser['tenantuuid'])) {
                $storageDes .= '(' . $this->getStorageTypeDes($d['storage_type']) . ', ' .
                    xphp_get_lang('UI_STORAGE_TOTAL_SIZE') . ':' . v1_calsize($d['total_size'], true)
                    . ', ' . xphp_get_lang('WEB_PLATFORM_DC_AVAILABLE_SPACE')
                    . ':' . v1_calsize($d['free_size'], true) . ')';
            }
            $storageDes .= $name;

            $info[] = array(
                'storage_uuid' => $d['storage_uuid'],
                'text' => $storageDes,
                'name' => $d['storage_nickname'],
                'storage_type' => intval($d['storage_type']),
                'total_size' => intval($d['total_size']),
                'free_size' => intval($d['free_size']),
                'worm_flag' => v1_parse_flag_to_bool($d['worm_flag']),
            );
        }
        $sortInfo = array_column($info, 'free_size');
        array_multisort($sortInfo, SORT_DESC, $info);

        return $info;
    }

    /**
     * 切换节点后获取默认网络
     * @param string $taskuuid 任务uuid
     * @param string $nodeuuid 节点uuid
     * @param string $network  网络
     * @return string
     */
    public function pGetDiffNodeNetwork($taskuuid, $nodeuuid, $network): string
    {
        $sql = "select node_uuid from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        //检查是否节点不同
        if ($data[0]['node_uuid'] != $nodeuuid) {
            //继续获取新加节点的网络
            $sql = "select network_uuid from bd_node_network where node_uuid =? order by network_order asc";
            $data = $this->dbSelect($sql, array($nodeuuid));
            foreach ($data as $d) {
                if ($network == $d['network_uuid']) {
                    //如果选择了当前节点网络，直接返回
                    return $network;
                }
            }
            //返回选择节点默认第一顺序网络
            return $data[0]['network_uuid'];
        } else {
            //和原来是同一节点，返回选择的节点网络
            return $network;
        }
    }

    /**
     * 得到存储类型描述
     * @param int $storageType 类型
     * @return string
     */
    private function getStorageTypeDes(int $storageType): string
    {
        $des = '';
        if (empty($storageType)) {
            return $des;
        }
        $ptDes = xphp_get_desc('Pf', 'STORAGETYPE');
        return $ptDes[$storageType] ?? '';
    }

    /**
     * 得到存储状态码
     * @param array $nodeAllStatus 节点所有程序状态
     * @param int   $storageStatus 存储状态码
     * @param int   $mountFlag     存储挂载标志
     * @return int
     */
    private function getStorageStatus(array $nodeAllStatus, int $storageStatus, int $mountFlag)
    {

        $storageStatusArr = xphp_get_config('resource', 'STORAGE_STATUS');
        $flag = xphp_get_config('app', 'FLAG');

        //首先检查节点的状态
        if (!$nodeAllStatus['flag']) {
            //节点已经有异常了,部分程序不在线
            return $storageStatusArr['OFFLINE'];
        }
        if (
            $storageStatus == $storageStatusArr['ONLINE'] &&
            $mountFlag == $flag['SET']
        ) {
            //存储在线 且挂载
            return $storageStatusArr['ONLINE'];
        } elseif ($storageStatus == $storageStatusArr['OFFLINE']) {
            //存储离线
            return $storageStatusArr['OFFLINE'];
        } elseif (
            $storageStatus == $storageStatusArr['ONLINE'] &&
            $mountFlag == $flag['UNSET']
        ) {
            //在线未挂载
            return $storageStatusArr['UNMOUNT'];
        } else {
            //其他所有状态(创建中,离线已挂载)统一为异常
            return $storageStatusArr['CREATING'];
        }
    }

    /**
     * 获取节点缓存
     * @param string $nodesUuid 节点uuid
     * @return array
     */
    public function getNodeCache(string $nodesUuid): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $retCacheInfo = [
            'cache_type' => xphp_get_config('node', 'NODE_CACHE_TYPE', 'resources')['local_dir'],
            'cache_dir_path' => '',
            'mount_point' => '',
            'switch_strategy' => xphp_get_config('node', 'NODE_CACHE_SWITCH_STRATEGY', 'resources')['auto'],
            'cache_storage_uuid' => '',
            'warning_flag' => true,
            'warning_value' => 1024 * 1024 * 1024,  // 默认1GB
            'disk_storage_resource' => [],
        ];
        $sql = "SELECT cache_type, cache_dir_path, cache_storage_uuid, mount_point,
                    switch_strategy, warning_flag, warning_value,last_warning_time 
                FROM bd_system_cache_config
                WHERE node_uuid = ? ";
        $nodeCacheInfo = $this->dbSelect($sql, [$nodesUuid]);
        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        if (is_array($nodeCacheInfo) && $nodeCacheInfo) {
            $retCacheInfo = [
                'cache_type' => intval($nodeCacheInfo[0]['cache_type']),
                'cache_dir_path' => $nodeCacheInfo[0]['cache_dir_path'],
                'mount_point' => $nodeCacheInfo[0]['mount_point'],
                'switch_strategy' => intval($nodeCacheInfo[0]['switch_strategy']),
                'cache_storage_uuid' => $nodeCacheInfo[0]['cache_storage_uuid'],
                'warning_flag' => v1_parse_flag_to_bool($nodeCacheInfo[0]['warning_flag']),
                'warning_value' => intval($nodeCacheInfo[0]['warning_value']),
                'disk_storage_resource' => $this->getStorageInfoByNodeUuid($nodesUuid, [
                    $allStorageType['CLOUD'],
                    $allStorageType['TAPE'],
                ]),
            ];
        }

        return $this->sendResult('', true, 200, $retCacheInfo);
    }

    /**
     * 根据节点uuid获取磁盘信息
     * @param string $nodeUuid               节点uuid
     * @param array  $excludeStorageTypeList 排除的存储类型
     * @return array
     */
    private function getStorageInfoByNodeUuid(string $nodeUuid, array $excludeStorageTypeList = []): array
    {
        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $allUseMode = xphp_get_config('resource', 'BD_STORAGE_USE_MODE');
        $allStorageCategory = xphp_get_config('storage', 'BD_STORAGE_CATEGORY');
        $flag = xphp_get_config('app', 'FLAG');
        $storageTypeList = [
            $allStorageType['REMOTE'],
            // $allStorageType['CLOUD'],
            $allStorageType['HUAWEI_CBR'],
            // $allStorageType['TAPE'],
            // $allStorageType['HUAWEI_OCEAN'],  // 生产存储  // 通过bd_storage_resource.source_type   1备份存储 2生产存储
        ];
        $notUseType = "'" . implode("','", $storageTypeList) . "'";
        $excludeStorageTypes = "'" . implode("','", $excludeStorageTypeList) . "'";
        $sql = "SELECT storage_nickname,mount_point,storage_uuid,total_size, free_size,storage_type 
                FROM bd_storage_resource 
                WHERE node_uuid = ? AND storage_type NOT IN ($notUseType) AND storage_type NOT IN ($excludeStorageTypes)
                    AND storage_type != ? AND storage_type != ? AND lan_free_flag = ?
                    AND use_mode != ? AND source_type = ? ";
        $storageList = $this->dbSelect(
            $sql,
            [$nodeUuid, $allStorageType['NFS'], $allStorageType['CIFS'], $flag['UNSET'], $allUseMode['READ_ONLY'], $allStorageCategory['BACKUP']]
        );
        $sql = "SELECT bsr.storage_nickname, bssnl.mount_point, bsr.storage_uuid, bsr.total_size,
                    bsr.free_size, bsr.storage_type 
                FROM bd_shared_storage_node_layout bssnl
                    INNER JOIN bd_storage_resource bsr ON bssnl.storage_uuid = bsr.storage_uuid
                WHERE bssnl.node_uuid = ? AND bsr.lan_free_flag = ?
                    AND bsr.use_mode != ? AND bsr.storage_type NOT IN ($notUseType)
                    AND bsr.storage_type NOT IN ($excludeStorageTypes) AND source_type = ? ";
        $shareStorageList = $this->dbSelect($sql, [$nodeUuid, $flag['UNSET'], $allUseMode['READ_ONLY'], $allStorageCategory['BACKUP']]);
        $retStorageList = [];
        if (is_array($storageList)) {
            foreach ($storageList as $storageInfo) {
                $retStorageList[] = [
                    'storage_name' => $storageInfo['storage_nickname'],
                    'mount_point' => $storageInfo['mount_point'],
                    'storage_uuid' => $storageInfo['storage_uuid'],
                    'storage_type' => intval($storageInfo['storage_type']),
                    'total_size' => $storageInfo['total_size'],
                    'free_size' => $storageInfo['free_size'],
                ];
            }
        }
        if (is_array($shareStorageList)) {
            foreach ($shareStorageList as $shareStorageInfo) {
                $retStorageList[] = [
                    'storage_name' => $shareStorageInfo['storage_nickname'],
                    'mount_point' => $shareStorageInfo['mount_point'],
                    'storage_uuid' => $shareStorageInfo['storage_uuid'],
                    'storage_type' => intval($shareStorageInfo['storage_type']),
                    'total_size' => $shareStorageInfo['total_size'],
                    'free_size' => $shareStorageInfo['free_size'],
                ];
            }
        }
        return $retStorageList;
    }

    /**
     * 设置节点缓存
     * @param string $nodesUuid 节点uuid
     * @param array  $params    请求参数
     * @return array
     */
    public function setNodeCache(string $nodesUuid, array $params): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $this->checkAuthBySourceUuid($nodesUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        // 关闭缓存告警，告警阈值设置为0
        if (!$params['warning_flag']) {
            $params['warning_value'] = 0;
        }

        $opcodeName = 'NODE_SYS_OP_CONFIG_SYSTEM_CACHE';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $setResult = $this->service()->setNodeCacheService($nodesUuid, $params);
        if (!$setResult['result']) {
            $this->muOpResult(false, $operate, $setResult['msg'], 0, $setResult['errorCode']);
            $msg = xphp_get_lang('UI_NODE_SET_CACHE_NAME') . xphp_get_lang('WEB_PUBLIC_FAILURE');
            return $this->sendResult($msg, false, 0);
        }
        return $this->sendResult(xphp_get_lang('UI_NODE_SET_CACHE_NAME') . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    //////////////// 节点网络 ///////////////////

    /**
     * 配置节点资源限制
     * @param array $params 参数
     * @return array
     */
    public function setNodeResourcesLimit(array $params): array
    {
        $checkResult = $this->checkNodeList($params['node_uuid_list']);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeList = $checkResult['data'];

        $allResourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
        foreach ($params['node_uuid_list'] as $nodeUuid) {
            $this->checkAuthBySourceUuid($nodeUuid, $allResourceType['NODE']);
        }

        $opcodeName = 'SS_OP_CONFIG_RESOURCE_LIMITING';
        $operate = (new OrchestrationOpcode())->getOpcodeDes($opcodeName);
        $resourceLimitMsg = $this->buildNodeResourceLimitMsg($nodeList, $params);
        $setResult = $this->service()->setNodeResourceLimitService($resourceLimitMsg);
        if (!$setResult['result']) {
            $this->muOpResult(false, $operate, $setResult['msg'], 0, $setResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_SET_RESOURCE_LIMIT_ERROR'), false, 0);
        }
        $detail = array_map(function ($nodeInfo) {
            return sprintf(
                xphp_get_lang('WEB_NODE_SET_RESOURCE_LIMIT_DETAIL'),
                $nodeInfo['host_name'] . '(' . $nodeInfo['ip'] . ')'
            );
        }, $nodeList);
        return $this->sendResult(xphp_get_lang('WEB_NODE_SET_RESOURCE_LIMIT_SUCCESS'), true, 200, $detail);
    }

    /**
     * 检查备份节点列表
     * @link \app\v1\resources\v0\logic\NodePool::addNodePool()
     * @link \app\v1\resources\v0\logic\NodePool::editNodePool()
     * @link \app\v1\resources\v0\logic\NodePool::batchDeleteNodePool()
     * @param array $nodeUuidList 备份节点uuid列表
     * @return array
     */
    public function checkNodeList(array $nodeUuidList): array
    {
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $sql = "SELECT * FROM bd_node WHERE node_uuid IN ($nodeUuids)";
        $data = dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NODE_NOT_EXISTS'), false, 0);
        }
        if (count($nodeUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_NODE_SOME_NODE_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($nodeUuidList, array_column($data, 'node_uuid')))
            );
        }
        return $this->sendResult('', true, 200, $data);
    }

    /**
     * 构建节点资源限制消息
     * @param array $nodeList 节点列表
     * @param array $params   参数信息
     * @return array
     */
    private function buildNodeResourceLimitMsg(array $nodeList, array $params): array
    {
        $allProhibitTimeType = xphp_get_config('node', 'PROHIBIT_TIME_TYPE', 'resources');
        $nodeConfigFlag = v1_parse_bool_to_flag($params['node_config_flag']);
        $prohibitTimeType = (int) $params['prohibit_time_type'];
        $maxTaskRunningNum = (int) $params['max_task_running_num'];
        $prohibitTimeInfo = [];
        foreach ($params['prohibit_time_info'] ?? [] as $prohibitTime) {
            $days = '';
            if (
                $prohibitTimeType == $allProhibitTimeType['WEEK'] ||
                $prohibitTimeType == $allProhibitTimeType['MONTH']
            ) {
                $days = implode('', array_map(function ($day) {
                    return $day ? 1 : 0;
                }, $prohibitTime['days']));
            }
            $timeList = [];
            foreach ($prohibitTime['time_list'] as $time) {
                $startTime = v1_sec_to_time($time['start_time']);
                $endTime = v1_sec_to_time($time['end_time']);
                if ($prohibitTimeType == $allProhibitTimeType['CUSTOM']) {
                    // 用户自定义的是UNIX时间戳
                    $startTime = date('Y-m-d H:i:s', $time['start_time']);
                    $endTime = date('Y-m-d H:i:s', $time['end_time']);
                }
                $timeList[] = [
                    'prohibit_start_time' => $startTime,
                    'prohibit_end_time' => $endTime,
                ];
            }
            $prohibitTimeInfo[] = [
                'prohibit_days' => $days,
                'prohibit_time_list' => $timeList,
            ];
        }
        if (!$prohibitTimeInfo) {
            $prohibitTimeType = xphp_get_config('node', 'PROHIBIT_TIME_TYPE', 'resources')['UNKNOWN'];
        }
        $nodeUuidList = array_column($nodeList, 'node_uuid');
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        return [
            'node_uuid' => $nodeUuidList,
            'node_config_flag' => $nodeConfigFlag,
            'prohibit_time_type' => $prohibitTimeType,
            'prohibit_time_info' => $prohibitTimeInfo,
            'max_task_running_num' => $maxTaskRunningNum,
            'details' => '',
        ];
    }

    /**
     * 获取节点的资源限制
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    public function getNodeResourcesLimit(string $nodeUuid): array
    {
        $allProhibitTimeType = xphp_get_config('node', 'PROHIBIT_TIME_TYPE', 'resources');
        $checkResult = $this->checkNodeList([$nodeUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $sql = "SELECT node_uuid, node_config_flag, prohibit_start_time,
                    prohibit_end_time, days, prohibit_time_type, max_task_running_num
                FROM bd_resource_limiting_strategy_node_config
                WHERE node_uuid = ? ";
        $data = $this->dbSelect($sql, [$nodeUuid]);

        if (!$data || !is_array($data)) {
            return $this->sendResult('', true, 200, [
                'init_flag' => false,
                'node_config_flag' => false,
                'node_uuid' => $nodeUuid,
                'ip' => $nodeInfo['ip'] ?: '',
                'host_name' => $nodeInfo['host_name'] ?: '',
                'node_nickname' => $nodeInfo['node_nickname'] ?: '',
                'max_task_running_num' => 30,
                'prohibit_time_type' => $allProhibitTimeType['DAY'],  // 默认为<每天>
                'prohibit_time_info' => [],
            ]);
        }

        return $this->sendResult('', true, 200, $this->buildResourcesLimit($nodeUuid, $data, $checkResult['data']));
    }

    /**
     * 构建节点的资源限制
     * @param string $nodeUuid           节点uuid
     * @param array  $resourcesLimitList 资源限制列表
     * @param array  $nodeData           节点数据
     * @return array
     */
    private function buildResourcesLimit(string $nodeUuid, array $resourcesLimitList, array $nodeData): array
    {
        $nodeInfo = [
            'ip' => '',
            'host_name' => '',
            'node_nickname' => '',
            'node_uuid' => $nodeUuid,
        ];
        foreach ($nodeData as $row) {
            if ($row['node_uuid'] == $nodeUuid) {
                $nodeInfo = $row;
                break;
            }
        }
        $currentNodeResourcesLimitData = [];
        foreach ($resourcesLimitList as $resourcesLimitInfo) {
            if ($resourcesLimitInfo['node_uuid'] == $nodeUuid) {
                $currentNodeResourcesLimitData[] = $resourcesLimitInfo;
            }
        }
        $allProhibitTimeType = xphp_get_config('node', 'PROHIBIT_TIME_TYPE', 'resources');
        $resourceLimit = [
            'init_flag' => false,
            'node_config_flag' => false,
            'max_task_running_num' => 30,
            'prohibit_time_type' => $allProhibitTimeType['DAY'],  // 默认为<每天>
            'prohibit_time_info' => [],
            'node_uuid' => $nodeUuid,
            'ip' => $nodeInfo['ip'] ?: '',
            'host_name' => $nodeInfo['host_name'] ?: '',
            'node_nickname' => $nodeInfo['node_nickname'] ?: '',
        ];
        if (!$currentNodeResourcesLimitData) {
            return $resourceLimit;
        }
        $resourceLimit['init_flag'] = true;
        $resourceLimit['node_config_flag'] = v1_parse_flag_to_bool($currentNodeResourcesLimitData[0]['node_config_flag']);
        $resourceLimit['max_task_running_num'] = (int) $currentNodeResourcesLimitData[0]['max_task_running_num'];
        $resourceLimit['prohibit_time_type'] = (int) $currentNodeResourcesLimitData[0]['prohibit_time_type'];
        $prohibitTimeInfo = [];
        foreach ($currentNodeResourcesLimitData as $item) {
            if (!$item['prohibit_start_time'] || !$item['prohibit_end_time']) {
                continue;
            }
            $tmpTime = [
                'start_timestamp' => v1_time_to_sec($item['prohibit_start_time']),
                'end_timestamp' => v1_time_to_sec($item['prohibit_end_time']),
                'start_time' => $item['prohibit_start_time'],
                'end_time' => $item['prohibit_end_time'],
                'days' => array_map('intval', str_split($item['days'])),
            ];
            if ($resourceLimit['prohibit_time_type'] == $allProhibitTimeType['CUSTOM']) {  // 用户自定义
                $tmpTime['start_time'] = date('Y-m-d H:i:s', strtotime($item['prohibit_start_time']));
                $tmpTime['end_time'] = date('Y-m-d H:i:s', strtotime($item['prohibit_end_time']));
                $tmpTime['start_timestamp'] = strtotime($item['prohibit_start_time']);
                $tmpTime['end_timestamp'] = strtotime($item['prohibit_end_time']);
            }
            $prohibitTimeInfo[] = $tmpTime;
        }
        $resourceLimit['prohibit_time_info'] = array_values($prohibitTimeInfo);
        return $resourceLimit;
    }

    /**
     * 批量获取节点的资源限制
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    public function getNodeResourcesLimitByNodeUuidList(array $nodeUuidList): array
    {
        $checkResult = $this->checkNodeList($nodeUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $sql = "SELECT node_uuid, node_config_flag, prohibit_start_time,
                    prohibit_end_time, days, prohibit_time_type, max_task_running_num
                FROM bd_resource_limiting_strategy_node_config
                WHERE node_uuid IN ($nodeUuids) ";
        $data = $this->dbSelect($sql);

        $resourcesLimitList = [
            'total' => 0,
            'rows' => []
        ];
        if (!$data || !is_array($data)) {
            return $this->sendResult('', true, 200, $resourcesLimitList);
        }
        foreach ($nodeUuidList as $nodeUuid) {
            $resourcesLimitList['rows'][] = $this->buildResourcesLimit($nodeUuid, $data, $checkResult['data']);
        }
        $resourcesLimitList['total'] = count($resourcesLimitList['rows']);
        return $this->sendResult('', true, 200, $resourcesLimitList);
    }

    /**
     * 获取节点网络列表
     * @param string $nodesUuid 节点uuid
     * @param array  $params    参数列表
     * @return array
     */
    public function getNodeNetworkList(string $nodesUuid, array $params): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $allowSortFields = [
            'network_ip' => 'ip',
            'network_alias' => 'alias_name',
            'network_order' => 'network_order',
        ];
        $sort = $allowSortFields[$params['sort'] ?? 'network_order'] ?? 'network_order';
        $order = strtoupper($params['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT network_uuid, ip, port, network_name, alias_name,
                    type, node_uuid, network_order
                FROM bd_node_network
                WHERE node_uuid = ? AND ip != ''
                ORDER BY $sort $order
                LIMIT ?, ? ";
        $sqlCount = "SELECT COUNT(*) AS total FROM bd_node_network WHERE node_uuid = ? AND ip != '' ";
        $countData = $this->dbSelect($sqlCount, [$nodesUuid]);
        $data = $this->dbSelect($sql, [$nodesUuid, (int) $params['offset'], (int) $params['limit']]);

        $rows = [];
        $allNetworkTypeDes = xphp_get_desc('node', 'NETWORK_TYPE_DES', 'resources');
        foreach ($data as $networkInfo) {
            $rows[] = [
                'node_uuid' => $nodeInfo['node_uuid'],
                'network_uuid' => $networkInfo['network_uuid'],
                'network_ip' => $networkInfo['ip'],
                'network_port' => (int) $networkInfo['port'],
                'network_name' => $networkInfo['network_name'],
                'network_alias' => $networkInfo['alias_name'],
                'network_type' => (int) $networkInfo['type'],
                'network_type_des' => $allNetworkTypeDes[$networkInfo['type']] ?? xphp_get_lang('WEB_NODE_MAP'),
                'network_pool_name' => $this->getNetworkPoolName($networkInfo['network_uuid']),
            ];
        }

        return $this->sendResult('', true, 200, [
            'total' => $countData[0]['total'],
            'rows' => $rows,
        ]);
    }

    /**
     * 获取网卡的网络资源池
     * @param string $nodeNetworkUuid 节点网络uuid
     * @return array
     */
    private function getNetworkPoolName(string $nodeNetworkUuid): array
    {
        $sql = "SELECT network_pool_nickname
                FROM bd_node_network_pool bnnp
                    INNER JOIN bd_node_network_pool_list bnnpl ON bnnp.network_pool_uuid = bnnpl.network_pool_uuid
                WHERE bnnpl.network_uuid = ? GROUP BY bnnp.network_pool_uuid ";
        $data = $this->dbSelect($sql, [$nodeNetworkUuid]);
        if ($data && is_array($data)) {
            return array_column($data, 'network_pool_nickname');
        }
        return [];
    }

    /**
     * 获取节点网络列表
     * @param string $nodesUuid   节点uuid
     * @param string $networkUuid 节点网络uuid
     * @return array
     */
    public function getNodeNetworkDetail(string $nodesUuid, string $networkUuid): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $checkResult = $this->checkNodeNetworkList([$networkUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkInfo = $checkResult['data'][0];
        if ($networkInfo['node_uuid'] !== $nodeInfo['node_uuid']) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_NOT_MATCH_NODE'), false, 0);
        }

        return $this->sendResult('', true, 200, [
            'node_uuid' => $nodeInfo['node_uuid'],
            'network_uuid' => $networkInfo['network_uuid'],
            'network_ip' => $networkInfo['ip'],
            'network_port' => (int) $networkInfo['port'],
            'network_name' => $networkInfo['network_name'],
            'network_alias' => $networkInfo['alias_name'],
            'network_type' => (int) $networkInfo['type'],
            'network_pool_name' => $this->getNetworkPoolName($networkInfo['network_uuid']),
        ]);
    }

    /**
     * 检查节点网络列表
     * @link \app\v1\resources\v0\logic\NetworkPool::addNetworkPool()
     * @param array $networkUuidList 节点网络uuid列表
     * @return array
     */
    public function checkNodeNetworkList(array $networkUuidList): array
    {
        $networkUuidList = array_values(array_unique($networkUuidList));
        $networkUuids = "'" . implode("', '", $networkUuidList) . "'";
        $sql = "SELECT * FROM bd_node_network WHERE network_uuid IN ($networkUuids)";
        $data = dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_NOT_EXISTS'), false, 0);
        }
        if (count($networkUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_NODE_NETWORK_SOME_NETWORK_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($networkUuidList, array_column($data, 'network_uuid')))
            );
        }
        return $this->sendResult('', true, 200, $data);
    }

    /**
     * 添加网络节点
     * @param array $params 参数列表
     * @return array
     */
    public function addNodeNetwork(array $params): array
    {
        $checkResult = $this->checkNodeList([$params['nodes_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $this->checkAuthBySourceUuid($params['nodes_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        $opcodeName = 'NODE_NETWORK_OP_ADD';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->addNodeNetworkService(
            $params['nodes_uuid'],
            $params['network_ip'],
            (int) $params['network_port'],
            $params['network_alias']
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_ADD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_ADD_SUCCESS'));
    }

    /**
     * 修改网络节点
     * @param array $params 参数列表
     * @return array
     */
    public function editNodeNetwork(array $params): array
    {
        $checkResult = $this->checkNodeList([$params['nodes_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $this->checkAuthBySourceUuid($params['nodes_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        $checkResult = $this->checkNodeNetworkList([$params['network_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkInfo = $checkResult['data'][0];
        if ($networkInfo['node_uuid'] !== $nodeInfo['node_uuid']) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_NOT_MATCH_NODE'), false, 0);
        }
        if ($networkInfo['type'] == xphp_get_config('node', 'NETWORK_TYPE', 'resources')['default']) {
            if ($params['network_ip'] != $networkInfo['ip']) {  // 本地默认不能修改ip
                return $this->sendResult(xphp_get_lang('WEB_MODE_NETWORK_EDIT_DEFAULT_IP'), false, 0);
            }
        }

        $opcodeName = 'NODE_NETWORK_OP_MODIFY';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->editNodeNetworkService(
            $params['nodes_uuid'],
            $params['network_uuid'],
            $params['network_ip'],
            (int) $params['network_port'],
            $params['network_alias']
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_EDIT_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_EDIT_SUCCESS'));
    }

    /**
     * 删除网络节点
     * @param string $nodesUuid   节点uuid
     * @param string $networkUuid 节点网络uuid
     * @return array
     */
    public function deleteNodeNetwork(string $nodesUuid, string $networkUuid): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $this->checkAuthBySourceUuid($nodesUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        $checkResult = $this->checkNodeNetworkList([$networkUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkInfo = $checkResult['data'][0];
        if ($networkInfo['node_uuid'] !== $nodeInfo['node_uuid']) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_NOT_MATCH_NODE'), false, 0);
        }
        // 检查节点网络是否处于资源池中
        $sql = "SELECT bnnpl.network_uuid, bnnp.network_pool_uuid, bnnp.network_pool_nickname
                FROM bd_node_network_pool bnnp
                    INNER JOIN bd_node_network_pool_list bnnpl ON bnnp.network_pool_uuid = bnnpl.network_pool_uuid
                WHERE bnnpl.network_uuid = ? ";
        $networkPoolData = $this->dbSelect($sql, [$networkUuid]);
        if (is_array($networkPoolData) && $networkPoolData) {
            $networkPoolNameList = array_column($networkPoolData, 'network_pool_nickname');
            return $this->sendResult(sprintf(xphp_get_lang('WEB_NODE_NETWORK_DELETE_IN_POOL'), implode("、", $networkPoolNameList)), false, 0);
        }

        $opcodeName = 'NODE_NETWORK_OP_DEL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->deleteNodeNetworkService($nodesUuid, [$networkUuid]);
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_DELETE_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_DELETE_SUCCESS'));
    }

    /**
     * 删除网络节点
     * @param string $nodesUuid       节点uuid
     * @param array  $networkUuidList 节点网络uuid
     * @return array
     */
    public function batchDeleteNodeNetwork(string $nodesUuid, array $networkUuidList): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $this->checkAuthBySourceUuid($nodesUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        $checkResult = $this->checkNodeNetworkList($networkUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkList = $checkResult['data'];
        foreach ($networkList as $networkInfo) {
            if ($networkInfo['node_uuid'] !== $nodeInfo['node_uuid']) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_SOME_NETWORK_NOT_MATCH_NODE'), false, 0);
            }

            // 检查节点网络是否处于资源池中
            $sql = "SELECT bnnpl.network_uuid, bnnp.network_pool_uuid, bnnp.network_pool_nickname
                FROM bd_node_network_pool bnnp
                    INNER JOIN bd_node_network_pool_list bnnpl ON bnnp.network_pool_uuid = bnnpl.network_pool_uuid
                WHERE bnnpl.network_uuid = ? ";
            $networkPoolData = $this->dbSelect($sql, [$networkInfo['network_uuid']]);
            if (is_array($networkPoolData) && $networkPoolData) {
                $networkPoolNameList = array_column($networkPoolData, 'network_pool_nickname');
                return $this->sendResult(sprintf(xphp_get_lang('WEB_NODE_NETWORK_DELETE_IN_POOL'), implode("、", $networkPoolNameList)), false, 0);
            }
        }

        $opcodeName = 'NODE_NETWORK_OP_DEL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->deleteNodeNetworkService($nodesUuid, $networkUuidList);
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($networkInfo) {
            return sprintf(
                xphp_get_lang('WEB_NODE_NETWORK_DELETE_DETAIL'),
                $networkInfo['ip'] . ':' . $networkInfo['port']
            );
        }, $networkList);
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_DELETE_SUCCESS'), true, 200, $detail);
    }

    /**
     * 排序网络节点
     * @param string $nodesUuid       节点uuid
     * @param array  $networkUuidList 节点网络uuid
     * @return array
     */
    public function sortNodeNetwork(string $nodesUuid, array $networkUuidList): array
    {
        $checkResult = $this->checkNodeList([$nodesUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeInfo = $checkResult['data'][0];

        $this->checkAuthBySourceUuid($nodesUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);

        $checkResult = $this->checkNodeNetworkList($networkUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $checkNetworkList = $checkResult['data'];
        foreach ($checkNetworkList as $networkInfo) {
            if ($networkInfo['node_uuid'] !== $nodeInfo['node_uuid']) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_SOME_NETWORK_NOT_MATCH_NODE'), false, 0);
            }
        }

        $opcodeName = 'NODE_NETWORK_OP_ADJUST_ORDER';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->sortNodeNetworkService($nodesUuid, $networkUuidList);
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_ORDER_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_ORDER_SUCCESS'));
    }

    /**
     * 获取节点分配列表
     * @param array $params 参数
     * @return array
     */
    public function getNodeAllocationList(array $params): array
    {
        $vcenteruuid = $params['platform_uuid'];

        //获取备份节点列表
        $sql = "select ip, node_nickname, host_name, node_uuid from bd_node";
        $data = $this->dbSelect($sql);

        //获取已绑定的节点
        $sql1 = "select node_uuid from mt_platform_node where platform_uuid = ? and type = ?";
        $data1 = $this->dbSelect($sql1, array($vcenteruuid, 1));
        $nodeList = array();
        $allocationList = array();
        //备份节点列表数组
        foreach ($data as $d) {
            $nodeList[] = array(
                'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                'uuid' => $d['node_uuid']
            );
        }

        //已绑定节点数组
        foreach ($data1 as $d) {
            $allocationList[] = $d['node_uuid'];
        }

        return array(
            'node_list' => $nodeList,
            'allocation_list' => $allocationList
        );
    }

    /**
     * 分配备份节点到虚拟化平台
     * @param array $params 参数
     * @return string
     */
    public function allocationNodePlatform($params)
    {
        $vcenteruuid = $params['platform_uuid'];
        $nodes = $params['node_uuids'];

        if (!empty($vcenteruuid)) {
            //如果虚拟化中心唯一标识不为空，清空当前虚拟化中心关联的备份节点
            $sql = "delete from mt_platform_node where platform_uuid = ? and type = ? ";
            $result = $this->dbExec($sql, array($vcenteruuid, 1));
        }
        //添加平台和节点关联关系
        if (!empty($nodes)) {
            $nodesDes = implode("','", $nodes);
            $type = 1;
            $sql = "insert into mt_platform_node (node_uuid, platform_uuid, type) values ";
            foreach ($nodes as $key => $l) {
                $sql .= "('" . $l . "','" . $vcenteruuid . "'," . $type . ')';
                if ($key != (count($nodes) - 1)) {
                    $sql .= ',';
                }
            }
            $result = $this->dbExec($sql);
        } else {
            $result = true;
        }

        return $this->muOpResult($result, xphp_get_lang('UI_VCENTER_ALLOCATION_NODE'));
    }

    /**
     * 获取虚拟机传输代理
     * @param array $params 参数
     * @return array
     */
    public function getApplianceSelect(array $params): array
    {
        $platformUuid = $params['platform_uuid'];
        // 新版本从bd_agent获取
        $sql = "select agent_uuid, agent_name, ip, applied_vcenters from bd_agent 
                where agent_type = 4 and node_uuid = '' and online_flag = ? and os_type != ? and agent_os_type != ?";
        $data = $this->dbSelect(
            $sql,
            array(
                xphp_get_config('app', 'FLAG')['SET'],
                'Windows',
                xphp_get_config('client', 'AGENT_OS_TYPE', 'resources')['WINDOWS']
            )
        );
        $info = array();
        $resourceList = array();
        $userInfo = xphp_get_user_info();
        //除了管理员admin以外的用户需要检查分配或者创建的传输代理
        if ($userInfo['userType'] != xphp_get_config('user', 'USERTYPE')['manager']) {
            $resourceHandler = ResourceHandler::instance();
            $resourceInfo = $resourceHandler->pGetUserResourceAgent(
                $userInfo['userUuid'],
                'id',
                'desc',
                0,
                'all'
            );
            if (!empty($resourceInfo['data'])) {
                foreach ($resourceInfo['data'] as $r) {
                    $resourceList[] = $r['agent_uuid'];
                }
            }
        }
        foreach ($data as $d) {
            //除了管理员admin以外的用户需要检查分配或者创建的传输代理
            if (
                $userInfo['userType'] != xphp_get_config('user', 'USERTYPE')['manager'] &&
                !in_array($d['agent_uuid'], $resourceList)
            ) {
                continue;
            }

            // 如果传输代理已分配到虚拟化中心，且当前虚拟化中心未被分配则不显示
            $appliedVcenters = json_decode($d['applied_vcenters'], true);
            if ($platformUuid && $appliedVcenters && !in_array($platformUuid, $appliedVcenters)) {
                continue;
            }

            $info[] = array(
                'text' => $d['agent_name'] . '(' . $d['ip'] . ')',
                'value' => $d['agent_uuid']
            );
        }

        return ['appliance' => $info];
    }

    /**
     * 扫描本地节点的IP列表
     * @param array $params 参数
     * @return array
     */
    public function scanLocalNodeIPList(array $params): array
    {
        $nodeUuid = $params['node_uuid'] ?? $this->getLocalNodeUUID();
        $nodeIpList = $this->service()->getNodeIPAddress($nodeUuid);
        $nodeIpList = json_decode($nodeIpList, true);
        $ipList = [];
        foreach ($nodeIpList['ipv4_list'] as $ipv4) {
            $ipList[] = $ipv4;
        }
        foreach ($nodeIpList['ipv6_list'] as $ipv6) {
            $ipList[] = $ipv6;
        }
        return $this->sendResult('', true, 200, $ipList);
    }

    /**
     * 获取备份系统节点IP
     * @param array $params 参数
     * @return array
     */
    public function getBackupServerIp(array $params): array
    {
        $nodeuuid = $params['node_uuid'];
        $taskuuid = $params['job_uuid'];
        $nodeUuidList = $params['node_uuid_list'];
        if (!empty($nodeUuidList)) {
            $re = [];
            foreach ($nodeUuidList as $nodeUuid) {
                $sql = "select bnn.ip, bn.host_name, bn.ip as node_ip from bd_node_network bnn join bd_node bn on bnn.node_uuid = bn.node_uuid
                            where bnn.node_uuid = ? order by bnn.network_order";
                $data = (array) $this->dbSelect($sql, array($nodeUuid));
                $re[$nodeUuid] = [
                    'host_name' => $data[0]['host_name'],
                    'node_ip' => $data[0]['node_ip'],
                    'ip_list' => array_column($data, 'ip')
                ];
            }
            return $re;
        } else {
            if (!empty($taskuuid)) {
                $sql = "select node_uuid from bd_task where task_uuid = ?";
                $data = $this->dbSelect($sql, array($taskuuid));
                if (!empty($data)) {
                    $nodeuuid = $data[0]['node_uuid'];
                }
            }
            $sql = "select ip from bd_node_network where node_uuid = ? order by network_order";
            $data = (array) $this->dbSelect($sql, array($nodeuuid));

            return ['rows' => array_column($data, 'ip')];
        }
    }

    /**
     * 获取存在的资源池名称
     * @param string $poolType       资源池类型【node、network、storage、agent】
     * @param string $poolPrefixName 资源池前缀名称
     * @return string
     */
    private function getExistsResourceName(string $poolType, string $poolPrefixName): string
    {
        $maxSuffix = 1;
        $poolName = $poolPrefixName . $maxSuffix;
        while (true) {
            $findFlag = true;
            $sql = "SELECT node_pool_nickname FROM bd_node_pool WHERE node_pool_nickname = ? ";
            if ($poolType == 'network') {
                $sql = "SELECT network_pool_nickname FROM bd_node_network_pool WHERE network_pool_nickname = ? ";
            } elseif ($poolType == 'storage') {
                $sql = "SELECT storage_pool_nickname FROM bd_storage_resource_pool WHERE storage_pool_nickname = ? ";
            } elseif ($poolType == 'agent') {
                $sql = "SELECT agent_pool_nickname FROM bd_agent_pool WHERE agent_pool_nickname = ? ";
            }
            $resourceNameData = $this->dbSelect($sql, [$poolName]);
            if (is_array($resourceNameData) && $resourceNameData) {
                $maxSuffix++;
                $poolName = $poolPrefixName . $maxSuffix;
                $findFlag = false;
            }
            if ($findFlag) {
                break;
            }
        }
        return $poolName;
    }

    /**
     * 获取资源池名称
     * @param array $params 参数
     * @return array
     */
    public function getResourcePoolName(array $params): array
    {
        return $this->sendResult('', true, 200, [
            'pool_name' => $this->getExistsResourceName($params['pool_type'], $params['pool_prefix_name']),
        ]);
    }

    /**
     * 获取主节点网络信息
     * @return array
     */
    public function getNodesMasterNetwork(): array
    {
        // 查询主节点
        $sql = "SELECT node_uuid FROM bd_node WHERE node_type = ? ";
        $nodeData = $this->dbSelect($sql, [xphp_get_config('app', 'NODETYPE')['MASTER']]);
        if (!is_array($nodeData) || !$nodeData) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_CLUSTER_MASTER_NODE_ABNORMAL'), false, 0);
        }
        // 查询网络信息
        $sql = "SELECT node_uuid, network_uuid, ip FROM bd_node_network WHERE node_uuid = ? ORDER BY network_order ";
        $nodeNetworkData = $this->dbSelect($sql, [$nodeData[0]['node_uuid']]);
        if (!is_array($nodeNetworkData)) {
            $nodeNetworkData = [];
        }
        $rows = [];
        foreach ($nodeNetworkData as $nodeNetwork) {
            $rows[] = [
                'node_uuid' => $nodeNetwork['node_uuid'],
                'network_uuid' => $nodeNetwork['network_uuid'],
                'network_ip' => $nodeNetwork['ip'],
            ];
        }
        return $this->sendResult('', true, 200, [
            'total' => count($rows),
            'rows' => $rows,
        ]);
    }

    /**
     * 获取资源池名称
     * @param array $params 参数
     * @return array
     */
    public function getNodesByStorageUUID(array $params): array
    {
        $storageuuid = $params['storage_uuid'];
        $storageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $sql = "select bsr.storage_type, bsr.node_uuid, bsr.lan_free_flag, bn.ip, bn.node_nickname, bn.host_name from bd_storage_resource bsr left join bd_node bn on bsr.node_uuid = bn.node_uuid where bsr.storage_uuid = ? ";
        $data = $this->dbSelect($sql, array($storageuuid));
        $storagetype = intval($data[0]['storage_type']);
        $info = array();
        if (in_array($storagetype, [$storageTypeArr['NFS'], $storageTypeArr['CIFS'], $storageTypeArr['CLOUD']])) {
            $nodeList = Storage::instance()->getNasMountPointList($storageuuid, $storagetype);
            foreach ($nodeList as $d) {
                $info[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $d['node_name'],
                );
            }
        } else {
            $info[] = array(
                'uuid' => $data[0]['node_uuid'],
                'text' => $this->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
            );
        }

        return $info;
    }
}
