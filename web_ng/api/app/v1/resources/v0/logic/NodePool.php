<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;

class NodePool extends Base
{
    private $nodeOpcode;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
    }

    /**
     * 根据节点uuid获取计算资源池列表
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    private function getNodePoolListByNodeUuid(array $nodeUuidList): array
    {
        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $sql = "SELECT bnp.node_pool_uuid, bnp.node_pool_nickname, bnpl.node_uuid
                FROM bd_node_pool bnp
                    INNER JOIN bd_node_pool_list bnpl ON bnp.node_pool_uuid = bnpl.node_pool_uuid
                WHERE bnpl.node_uuid IN ($nodeUuids) ";

        // 资源拥有过滤
        $includeNodePoolUuidList = $this->getIncludeNodePoolUuidListByResource();
        if (is_array($includeNodePoolUuidList)) {
            $includeNodePoolUuids = "'" . implode("', '", $includeNodePoolUuidList) . "'";
            $sql .= " AND bnp.node_pool_uuid IN ($includeNodePoolUuids) ";
        }
        $nodePoolData = $this->dbSelect($sql);
        $ret = [];
        foreach ($nodePoolData as $nodePoolInfo) {
            $ret[] = [
                'node_pool_uuid' => $nodePoolInfo['node_pool_uuid'],
                'node_pool_nickname' => $nodePoolInfo['node_pool_nickname'],
                'node_uuid' => $nodePoolInfo['node_uuid'],
            ];
        }
        return $ret;
    }

    /**
     * 构建计算资源池信息
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    public function buildNodePoolInfoWithNodeUuid(array $nodeUuidList): array
    {
        $nodePoolData = $this->getNodePoolListByNodeUuid($nodeUuidList);
        $nodePoolMap = [];
        foreach ($nodePoolData as $nodePoolInfo) {
            if (!isset($nodePoolMap[$nodePoolInfo['node_uuid']])) {
                $nodePoolMap[$nodePoolInfo['node_uuid']] = [];
            }
            $nodePoolMap[$nodePoolInfo['node_uuid']][] = [
                'node_pool_uuid' => $nodePoolInfo['node_pool_uuid'],
                'node_pool_nickname' => $nodePoolInfo['node_pool_nickname'],
                'node_uuid' => $nodePoolInfo['node_uuid'],
            ];
        }
        return $nodePoolMap;
    }

    /**
     * 根据资源分配获取排除的计算资源池列表
     * @return array
     */
    private function getExcludeNodePoolUuidListByResource(): array
    {
        $excludeNodePoolUuidList = [];
        if (v1_auth_need_check_look()) {
            // 获取拥有的节点资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);
            // 查询不包含这些存储的资源池
            $sql = "SELECT node_pool_uuid FROM bd_node_pool_list WHERE node_uuid NOT IN ($resourceUuidSql) GROUP BY node_pool_uuid ";
            $excludeData = $this->dbSelect($sql);
            if (!is_array($excludeData)) {
                $excludeData = [];
            }
            $excludeNodePoolUuidList = array_values(array_unique(array_column($excludeData, 'node_pool_uuid')));
        }
        return $excludeNodePoolUuidList;
    }

    /**
     * 根据资源分配获取排除的计算资源池列表
     * @return array|boolean
     */
    private function getIncludeNodePoolUuidListByResource()
    {
        $includeNodePoolUuidList = true;
        if (v1_auth_need_check_look()) {
            // 获取拥有的节点资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);
            // 查询不包含这些存储的资源池
            $sql = "SELECT node_pool_uuid FROM bd_node_pool_list WHERE node_uuid IN ($resourceUuidSql) GROUP BY node_pool_uuid ";
            $includeData = $this->dbSelect($sql);
            if (!is_array($includeData)) {
                $includeData = [];
            }
            $includeNodePoolUuidList = array_values(array_unique(array_column($includeData, 'node_pool_uuid')));
        }
        return $includeNodePoolUuidList;
    }

    /**
     * 获取计算资源池列表
     * @param array $params 资源池名称
     * @return array
     */
    public function getNodePoolList(array $params): array
    {
        $sortFields = [
            'node_pool_name' => 'bnp.node_pool_nickname',
            'update_time' => 'bnp.last_modify_time',
            'creator' => 'bu.user_name',
            'remark' => 'bnp.remark',
        ];
        $sort = $sortFields[$params['sort'] ?? 'update_time'] ?? 'bnp.last_modify_time';
        $order = $params['order'] ?? 'DESC';
        $sql = "SELECT bnp.node_pool_uuid, bnp.node_pool_nickname,
                    bnp.last_modify_time, bnp.user_uuid, bnp.remark,
                    bu.user_name
                FROM bd_node_pool bnp
                    INNER JOIN bd_user bu On bu.user_uuid = bnp.user_uuid
                WHERE true ";
        $sqlCount = "SELECT COUNT(*) AS total FROM (
                        SELECT * FROM bd_node_pool GROUP BY node_pool_uuid
                    ) bnp WHERE true ";
        if ($params['node_pool_name'] ?? '') {
            $sql .= " AND bnp.node_pool_nickname LIKE '%{$params['node_pool_name']}%' ";
            $sqlCount .= " AND bnp.node_pool_nickname LIKE '%{$params['node_pool_name']}%' ";
        }

        // 资源拥有过滤
        $excludeNodePoolUuidList = $this->getExcludeNodePoolUuidListByResource();
        $excludeNodePoolUuids = "'" . implode("', '", $excludeNodePoolUuidList) . "'";
        $sql .= " AND bnp.node_pool_uuid NOT IN ($excludeNodePoolUuids) ";
        $sqlCount .= " AND bnp.node_pool_uuid NOT IN ($excludeNodePoolUuids) ";

        $sql .= " GROUP BY bnp.node_pool_uuid ORDER BY $sort $order LIMIT {$params['offset']}, {$params['limit']}";
        $countData = $this->dbSelect($sqlCount);
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult('', true, 200, [
                'total' => 0,
                'rows' => [],
            ]);
        }

        // 这里需要分开查询，因为bd_node_pool是冗余的，可能有多个值; 如果合在一起查询，对分页有影响
        $nodePoolUuidList = array_column($data, 'node_pool_uuid');
        $nodePoolUuids = "'" . implode("', '", $nodePoolUuidList) . "'";
        $sql = "SELECT bnpl.node_uuid, bnpl.node_pool_uuid,
                    bn.host_name, bn.ip, bn.node_type
                FROM bd_node_pool_list bnpl
                    LEFT JOIN bd_node bn ON bnpl.node_uuid = bn.node_uuid
                WHERE bnpl.node_pool_uuid IN ($nodePoolUuids) ";
        $nodeData = $this->dbSelect($sql);
        if (!$nodeData || !is_array($nodeData)) {
            $nodeData = [];
        }

        $nodeHandler = new Node();
        $rows = [];
        foreach ($data as $nodePoolItem) {
            if (!isset($rows[$nodePoolItem['node_pool_uuid']])) {
                $rows[$nodePoolItem['node_pool_uuid']] = [
                    'node_pool_uuid' => $nodePoolItem['node_pool_uuid'],
                    'node_pool_name' => $nodePoolItem['node_pool_nickname'],
                    'node_list' => [],
                    'update_time' => $nodePoolItem['last_modify_time'],
                    'creator' => $nodePoolItem['user_name'],
                    'user_uuid' => $nodePoolItem['user_uuid'],
                    'remark' => $nodePoolItem['remark'],
                ];
            }
            foreach ($nodeData as $nodeInfo) {
                if ($nodeInfo['node_pool_uuid'] == $nodePoolItem['node_pool_uuid']) {
                    // 节点状态
                    $nodeStatus = $nodeHandler->getNodeStatus($nodeInfo['node_uuid']);
                    $rows[$nodePoolItem['node_pool_uuid']]['node_list'][] = [
                        'node_name' => $nodeInfo['host_name'],
                        'node_uuid' => $nodeInfo['node_uuid'],
                        'node_ip' => $nodeInfo['ip'],
                        'online_flag' => $nodeStatus['online_flag'],
                        'node_type' => intval($nodeInfo['node_type']),
                    ];
                }
            }
        }

        $rows = array_values($rows);
        // $rows = array_merge($rows, $rows, $rows, $rows);
        // $rows = array_merge($rows, $rows, $rows, $rows);
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => $countData[0]['total'],
        ]);
    }

    /**
     * 获取计算资源池详情
     * @param string $nodePoolsUuid 计算资源池uuid
     * @return array
     */
    public function getNodePoolDetail(string $nodePoolsUuid): array
    {
        $checkResult = $this->checkNodePoolList([$nodePoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $sql = "SELECT bnp.node_pool_uuid, bnp.node_pool_nickname, bnp.last_modify_time,
                    bnp.create_time, bnp.user_uuid, bnp.remark,
                    bn.host_name, bn.ip AS node_ip, bn.node_uuid,
                    bu.user_name,
                    bnn.network_name, bnn.network_uuid, bnn.ip AS network_ip, bnn.port AS network_port
                FROM bd_node_pool bnp
                    INNER JOIN bd_node_pool_list bnpl ON bnp.node_pool_uuid = bnpl.node_pool_uuid
                    INNER JOIN bd_node bn ON bn.node_uuid = bnpl.node_uuid
                    INNER JOIN bd_user bu ON bnp.user_uuid = bu.user_uuid
                    LEFT JOIN bd_node_network bnn ON bnpl.node_uuid = bnn.node_uuid
                WHERE bnp.node_pool_uuid = ? ";
        $data = $this->dbSelect($sql, [$nodePoolsUuid]);
        if (!is_array($data)) {
            $data = [];
        }
        $nodeUuidList = array_column($data, 'node_uuid');
        $nodeList = [];
        $nodeHandler = new Node();
        $nodeStorageMap = $nodeHandler->batchGetStorageInfoByNodeUuidList($nodeUuidList);
        foreach ($data as $nodeItem) {
            if (!isset($nodeList[$nodeItem['node_uuid']])) {
                $nodeStatus = $nodeHandler->getNodeStatus($nodeItem['node_uuid']);
                $nodeList[$nodeItem['node_uuid']] = [
                    'node_name' => $nodeItem['host_name'],
                    'node_uuid' => $nodeItem['node_uuid'],
                    'node_ip' => $nodeItem['node_ip'],
                    'storage_list' => [],
                    'network_list' => [],
                    'online_flag' => $nodeStatus['online_flag'],
                ];
            }
            if (isset($nodeStorageMap[$nodeItem['node_uuid']])) {
                $storageMap = $nodeStorageMap[$nodeItem['node_uuid']];
                $nodeList[$nodeItem['node_uuid']]['storage_list'] = array_values($storageMap);
            }
            $nodeHandler->buildNodeNetworkInfo($nodeList[$nodeItem['node_uuid']]['network_list'], $nodeItem);
        }

        foreach ($nodeList as $index => $item) {
            $networkList = array_values($item['network_list']);
            $storageList = array_values($item['storage_list']);

            $item['network_list'] = $networkList;
            $item['storage_list'] = $storageList;
            $nodeList[$index] = $item;
        }

        $nodeList = array_values($nodeList);

        return $this->sendResult('', true, 200, [
            'node_pool_uuid' => $data[0]['node_pool_uuid'],
            'node_pool_name' => $data[0]['node_pool_nickname'],
            'node_list' => $nodeList,
            'update_time' => $data[0]['last_modify_time'],
            'creator' => $data[0]['user_name'],
            'remark' => $data[0]['remark'],
        ]);
    }

    /**
     * 添加计算资源池
     * @param array $params 参数
     * @return array
     */
    public function addNodePool(array $params): array
    {
        // 去除html转义
        $params['node_pool_name'] = htmlspecialchars_decode($params['node_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $nodePoolName = trim($params['node_pool_name']);
        $checkResult = $this->checkNodePoolName($nodePoolName);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $nodeLogicHandler = new Node();
        $checkResult = $nodeLogicHandler->checkNodeList($params['node_uuid_list']);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeList = $checkResult['data'];

        $opcodeName = 'NODE_SYS_OP_ADD_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->addOrEditNodePoolService(
            $nodePoolName,
            array_column($nodeList, 'node_uuid'),
            $params['remark'],
            $opcodeName
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_ADD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_ADD_SUCCESS'));
    }

    /**
     * 检查计算资源池名称
     * @param string $nodePoolName 计算资源池名称
     * @param string $nodePoolUuid 计算资源池uuid
     * @return array
     */
    private function checkNodePoolName(string $nodePoolName, string $nodePoolUuid = ''): array
    {
        $sql = "SELECT * FROM bd_node_pool WHERE node_pool_nickname = ? ";
        $sqlParams = [trim($nodePoolName)];
        if ($nodePoolUuid) {
            $sql .= ' AND node_pool_uuid != ? ';
            $sqlParams[] = $nodePoolUuid;
        }
        if ($this->dbSelect($sql, $sqlParams)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_NAME_ALREADY_EXISTS'), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * @param array $params 参数
     * @return array
     */
    public function editNodePool(array $params): array
    {
        // 去除html转义
        $params['node_pool_name'] = htmlspecialchars_decode($params['node_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $checkResult = $this->checkNodePoolList([$params['node_pools_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        $nodePoolName = trim($params['node_pool_name']);
        $checkResult = $this->checkNodePoolName($nodePoolName, $params['node_pools_uuid']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $nodeLogicHandler = new Node();
        $checkResult = $nodeLogicHandler->checkNodeList($params['node_uuid_list']);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodeList = $checkResult['data'];

        $opcodeName = 'NODE_SYS_OP_MODIFY_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);

        $editResult = $this->service()->addOrEditNodePoolService(
            $nodePoolName,
            array_column($nodeList, 'node_uuid'),
            $params['remark'],
            $opcodeName,
            $params['node_pools_uuid']
        );
        if (!$editResult['result']) {
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_EDIT_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_EDIT_SUCCESS'));
    }

    /**
     * 验证是否可以删除计算资源池
     * @param array $nodePoolUuidList 计算资源池uuid列表
     * @return array
     */
    private function validDeleteNodePool(array $nodePoolUuidList): array
    {
        $nodePoolUuidList = array_values(array_unique($nodePoolUuidList));
        $nodePoolUuids = "'" . implode("', '", $nodePoolUuidList) . "'";
        $sql = "SELECT bt.task_name, bnp.node_pool_nickname
                FROM bd_task bt
                    INNER JOIN bd_node_pool bnp ON bt.node_pool_uuid = bnp.node_pool_uuid
                WHERE bt.node_pool_uuid IN ($nodePoolUuids) ";
        $taskData = $this->dbSelect($sql);
        if (is_array($taskData) && count($taskData)) {
            $nodePoolNicknameList = array_column($taskData, 'node_pool_nickname');
            $nodePoolNicknameList = array_values(array_unique($nodePoolNicknameList));
            return $this->sendResult(sprintf(
                xphp_get_lang('WEB_NODE_POOL_DELETE_HAS_TASK'),
                implode('、', $nodePoolNicknameList),
                implode('、', array_column($taskData, 'task_name'))
            ), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 删除计算资源池
     * @param string $nodePoolsUuid 计算资源池uuid
     * @return array
     */
    public function deleteNodePool(string $nodePoolsUuid): array
    {
        $checkResult = $this->checkNodePoolList([$nodePoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteNodePool([$nodePoolsUuid]);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_SYS_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $deleteResult = $this->service()->deleteNodePoolService([$nodePoolsUuid]);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_DELETE_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_DELETE_SUCCESS'));
    }

    /**
     * 批量删除计算资源池
     * @param array $nodePoolUuidList 存储池uuid列表
     * @return array
     */
    public function batchDeleteNodePool(array $nodePoolUuidList): array
    {
        $checkResult = $this->checkNodePoolList($nodePoolUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $nodePoolList = $checkResult['data'];

        // 资源拥有判断
        $userUuidList = array_column($nodePoolList, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $this->checkAuthByUserUuid(implode(',', $userUuidList), 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteNodePool($nodePoolUuidList);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_SYS_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $batchDeleteResult = $this->service()->deleteNodePoolService($nodePoolUuidList);
        if (!$batchDeleteResult['result']) {
            $this->muOpResult(false, $operate, $batchDeleteResult['msg'], 0, $batchDeleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_BATCH_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($item) {
            return sprintf(xphp_get_lang('WEB_NODE_POOL_DELETE_DETAIL'), $item['node_pool_nickname']);
        }, $nodePoolList);
        return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_BATCH_DELETE_SUCCESS'), true, 200, $detail);
    }

    /**
     * 检查计算资源池uuid列表
     * @param array $nodePoolUuidList 计算资源池uuid列表
     * @return array
     */
    private function checkNodePoolList(array $nodePoolUuidList): array
    {
        $nodePoolUuidList = array_values(array_unique($nodePoolUuidList));
        $nodePoolUuids = "'" . implode("', '", $nodePoolUuidList) . "'";
        $sql = "SELECT * FROM bd_node_pool WHERE node_pool_uuid IN ($nodePoolUuids) GROUP BY node_pool_uuid";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_POOL_POOL_NOT_EXISTS'), false, 0);
        }

        if (count($nodePoolUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_NODE_POOL_SOME_POOL_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($nodePoolUuidList, array_column($data, 'node_pool_uuid')))
            );
        }
        return $this->sendResult('', true, 200, $data);
    }
}
