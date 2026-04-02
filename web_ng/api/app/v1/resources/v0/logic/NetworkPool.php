<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;

class NetworkPool extends Base
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
     * 根据资源分配获取排除的网络资源池列表
     * @return array
     */
    private function getExcludeNodeNetworkPoolUuidListByResource(): array
    {
        $excludeNodeNetworkPoolUuidList = [];
        if (v1_auth_need_check_look()) {
            // 获取拥有的节点
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NODE'], 'node_uuid');
            // 查询这些节点的网络资源
            $sql = "SELECT node_uuid, network_uuid FROM bd_node_network WHERE ($resourceUuidSql)  ";
            $networkData = $this->dbSelect($sql);
            if (!is_array($networkData)) {
                $networkData = [];
            }
            $networkUuidList = array_column($networkData, 'network_uuid');
            $networkUuids = "'" . implode("', '", $networkUuidList) . "'";
            // 排除这些网络资源的网络资源池
            $sql = "SELECT network_pool_uuid
                    FROM bd_node_network_pool_list
                    WHERE bd_node_network_pool_list.network_uuid NOT IN ($networkUuids)
                    GROUP BY network_pool_uuid ";
            $excludeData = $this->dbSelect($sql);
            if (!is_array($excludeData)) {
                $excludeData = [];
            }
            $excludeNodeNetworkPoolUuidList = array_values(array_unique(array_column($excludeData, 'network_pool_uuid')));
        }
        return $excludeNodeNetworkPoolUuidList;
    }

    /**
     * 获取网络资源池列表
     * @param array $params 参数
     * @return array
     */
    public function getNetworkPoolList(array $params): array
    {
        $sortFields = [
            'network_pool_name' => 'bnnp.network_pool_nickname',
            'update_time' => 'bnnp.last_modify_time',
            'creator' => 'bu.user_name',
            'remark' => 'bnnp.remark',
            'node_info' => 'bn.ip',
        ];
        $sort = $sortFields[$params['sort'] ?? 'update_time'] ?? 'bnp.last_modify_time';
        $order = $params['order'] ?? 'DESC';
        $sql = "SELECT bnnp.network_pool_uuid, bnnp.network_pool_nickname,
                    bnnp.last_modify_time, bnnp.user_uuid, bnnp.remark,
                    bu.user_name,
                    bn.node_uuid, bn.host_name, bn.node_nickname, bn.ip
                FROM bd_node_network_pool bnnp
                    INNER JOIN bd_user bu On bu.user_uuid = bnnp.user_uuid
                    INNER JOIN bd_node bn ON bn.node_uuid=bnnp.node_uuid
                WHERE true ";
        $sqlCount = "SELECT COUNT(*) AS total FROM (
                        SELECT * FROM bd_node_network_pool GROUP BY network_pool_uuid
                    ) bnnp WHERE true ";
        if ($params['network_pool_name'] ?? '') {
            $sql .= " AND bnnp.network_pool_nickname LIKE '%{$params['network_pool_name']}%' ";
            $sqlCount .= " AND bnnp.network_pool_nickname LIKE '%{$params['network_pool_name']}%' ";
        }
        if ($params['node_uuid'] ?? '') {
            $subQql = "SELECT bnnp.network_pool_uuid
                    FROM bd_node_network_pool bnnp
                        LEFT JOIN bd_node_network_pool_list bnnpl ON bnnpl.network_pool_uuid = bnnp.network_pool_uuid
                        LEFT JOIN bd_node_network bnn ON bnn.network_uuid = bnnpl.network_uuid
                    WHERE bnn.node_uuid = ? ";
            $networkPoolUuidData = $this->dbSelect($subQql, [$params['node_uuid']]);
            $networkPoolUuidList = [];
            if (is_array($networkPoolUuidData)) {
                $networkPoolUuidList = array_column($networkPoolUuidData, 'network_pool_uuid');
                $networkPoolUuidList = array_values(array_unique($networkPoolUuidList));
            }
            $networkPoolUuids = "'" .  implode("', '", $networkPoolUuidList) . "'";
            $sql .= " AND bnnp.network_pool_uuid IN ($networkPoolUuids) ";
            $sqlCount .= " AND bnnp.network_pool_uuid IN ($networkPoolUuids) ";
        }

        // 资源拥有过滤
        $excludeNodeNetworkPoolUuidList = $this->getExcludeNodeNetworkPoolUuidListByResource();
        $excludeNodeNetworkPoolUuids = "'" . implode("', '", $excludeNodeNetworkPoolUuidList) . "'";
        $sql .= " AND bnnp.network_pool_uuid NOT IN ($excludeNodeNetworkPoolUuids) ";
        $sqlCount .= " AND bnnp.network_pool_uuid NOT IN ($excludeNodeNetworkPoolUuids) ";

        $sql .= " GROUP BY bnnp.network_pool_uuid ORDER BY $sort $order LIMIT {$params['offset']}, {$params['limit']}";
        $countData = $this->dbSelect($sqlCount);
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult('', true, 200, [
                'total' => 0,
                'rows' => [],
            ]);
        }

        // 分开查询，同计算资源池
        $networkPoolUuidList = array_column($data, 'network_pool_uuid');
        $networkPoolUuids  = "'" . implode("', '", $networkPoolUuidList) . "'";
        $sql = "SELECT bnnpl.network_uuid, bnnpl.network_pool_uuid,
                    bnn.ip, bnn.port, bnn.network_name
                FROM bd_node_network_pool_list bnnpl
                    LEFT JOIN bd_node_network bnn ON bnnpl.network_uuid = bnn.network_uuid
                WHERE bnnpl.network_pool_uuid IN ($networkPoolUuids) ";
        $networkData = $this->dbSelect($sql);
        if (!$networkData || !is_array($networkData)) {
            $networkData = [];
        }

        $rows = [];
        foreach ($data as $networkPoolItem) {
            if (!isset($rows[$networkPoolItem['network_pool_uuid']])) {
                $rows[$networkPoolItem['network_pool_uuid']] = [
                    'network_pool_uuid' => $networkPoolItem['network_pool_uuid'],
                    'network_pool_name' => $networkPoolItem['network_pool_nickname'],
                    'network_list' => [],
                    'update_time' => $networkPoolItem['last_modify_time'],
                    'creator' => $networkPoolItem['user_name'],
                    'user_uuid' => $networkPoolItem['user_uuid'],
                    'remark' => $networkPoolItem['remark'],
                    'node_info' => [
                        'node_uuid' => $networkPoolItem['node_uuid'],
                        'host_name' => $networkPoolItem['host_name'],
                        'node_ip' => $networkPoolItem['ip'],
                        'node_nickname' => $networkPoolItem['node_nickname'] ?: '',
                    ],
                ];
            }
            foreach ($networkData as $networkInfo) {
                if ($networkInfo['network_pool_uuid'] == $networkPoolItem['network_pool_uuid']) {
                    $rows[$networkPoolItem['network_pool_uuid']]['network_list'][] = [
                        'network_name' => $networkInfo['network_name'],
                        'network_uuid' => $networkInfo['network_uuid'],
                        'network_ip' => $networkInfo['ip'],
                        'network_port' => (int) $networkInfo['port'],
                    ];
                }
            }
        }

        $rows = array_values($rows);
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => $countData[0]['total'],
        ]);
    }

    /**
     * 获取网络资源池详情
     * @param string $networkPoolsUuid 网络资源池uuid
     * @return array
     */
    public function getNetworkPoolDetail(string $networkPoolsUuid): array
    {
        $checkResult = $this->checkNetworkPoolList([$networkPoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $sql = "SELECT bnnp.network_pool_uuid, bnnp.network_pool_nickname, bnnp.last_modify_time,
                    bnnp.create_time, bnnp.user_uuid, bnnp.remark,
                    bn.host_name, bn.ip AS node_ip, bn.node_uuid,
                    bu.user_name,
                    bnn.network_name, bnn.network_uuid, bnn.ip AS network_ip, bnn.port AS network_port
                FROM bd_node_network_pool bnnp
                    INNER JOIN bd_node_network_pool_list bnnpl ON bnnp.network_pool_uuid = bnnpl.network_pool_uuid
                    LEFT JOIN bd_node_network bnn ON bnnpl.network_uuid = bnn.network_uuid
                    INNER JOIN bd_node bn ON bn.node_uuid = bnn.node_uuid
                    INNER JOIN bd_user bu ON bnnp.user_uuid = bu.user_uuid
                WHERE bnnp.network_pool_uuid = ? ";
        $data = $this->dbSelect($sql, [$networkPoolsUuid]);
        $networkList = [];
        foreach ($data as $networkItem) {
            if (!isset($networkList[$networkItem['network_uuid']])) {
                $networkList[$networkItem['network_uuid']] = [
                    'network_name' => $networkItem['network_name'],
                    'network_uuid' => $networkItem['network_uuid'],
                    'network_ip' => $networkItem['network_ip'],
                    'network_port' => $networkItem['network_port'],
                    'node_uuid' => $networkItem['node_uuid'],
                    'node_name' => $networkItem['host_name'],
                ];
            }
        }

        return $this->sendResult('', true, 200, [
            'network_pool_uuid' => $data[0]['network_pool_uuid'],
            'network_pool_name' => $data[0]['network_pool_nickname'],
            'network_list' => array_values($networkList),
            'update_time' => $data[0]['last_modify_time'],
            'creator' => $data[0]['user_name'],
            'remark' => $data[0]['remark'],
        ]);
    }

    /**
     * 检查网络资源池uuid列表
     * @param array $networkPoolUuidList 网络资源池uuid列表
     * @return array
     */
    private function checkNetworkPoolList(array $networkPoolUuidList): array
    {
        $networkPoolUuidList = array_values(array_unique($networkPoolUuidList));
        $networkPoolUuids = "'" . implode("', '", $networkPoolUuidList) . "'";
        $sql = "SELECT *
                FROM bd_node_network_pool
                WHERE network_pool_uuid IN ($networkPoolUuids)
                GROUP BY network_pool_uuid ";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_POOL_NOT_EXISTS'), false, 0);
        }

        if (count($networkPoolUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_NODE_NETWORK_POOL_SOME_POOL_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($networkPoolUuidList, array_column($data, 'network_pool_uuid')))
            );
        }

        return $this->sendResult('', true, 200, $data);
    }

    /**
     * 添加网络资源池详情
     * @param array $params 参数
     * @return array
     */
    public function addNetworkPool(array $params): array
    {
        // 去除html转义
        $params['network_pool_name'] = htmlspecialchars_decode($params['network_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $networkPoolName = trim($params['network_pool_name']);
        $checkResult = $this->checkNetworkPoolName($networkPoolName);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $nodeLogicHandler = new Node();
        $checkResult = $nodeLogicHandler->checkNodeNetworkList($params['network_uuid_list']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $opcodeName = 'NODE_NETWORK_OP_ADD_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->addOrEditNetworkPoolService(
            $networkPoolName,
            $params['network_uuid_list'],
            $params['remark'],
            $opcodeName,
            $params['node_uuid']
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_ADD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_ADD_SUCCESS'));
    }

    /**
     * 检查网络资源池名称
     * @param string $networkPoolName 网络资源池名称
     * @param string $networkPoolUuid 网络资源池uuid
     * @return array
     */
    private function checkNetworkPoolName(string $networkPoolName, string $networkPoolUuid = ''): array
    {
        $sql = "SELECT * FROM bd_node_network_pool WHERE network_pool_nickname = ? ";
        $sqlParams = [trim($networkPoolName)];
        if ($networkPoolUuid) {
            $sql .= ' AND network_pool_uuid != ? ';
            $sqlParams[] = $networkPoolUuid;
        }
        if ($this->dbSelect($sql, $sqlParams)) {
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_NAME_ALREADY_EXISTS'), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 修改网络资源池详情
     * @param array $params 参数
     * @return array
     */
    public function editNetworkPool(array $params): array
    {
        // 去除html转义
        $params['network_pool_name'] = htmlspecialchars_decode($params['network_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $checkResult = $this->checkNetworkPoolList([$params['network_pools_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkPoolInfo = $checkResult['data'][0];

        // 资源拥有判断
        $this->checkAuthByUserUuid($networkPoolInfo['user_uuid'], 'resmanagement');

        $networkPoolName = trim($params['network_pool_name']);
        $checkResult = $this->checkNetworkPoolName($networkPoolName, $params['network_pools_uuid']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $nodeLogicHandler = new Node();
        // 验证添加到资源池的网络uuid
        $addNetworkUuidList = $params['add_network_uuid_list'] ?? [];
        if ($addNetworkUuidList) {
            $checkResult = $nodeLogicHandler->checkNodeNetworkList($addNetworkUuidList);
            if (!$checkResult['success']) {
                return $checkResult;
            }
            $addNetworkList = $checkResult['data'];
        } else {
            $addNetworkList = [];
        }

        // 验证移出资源池的网络uuid
        $deleteNodeUuidList = $params['delete_network_uuid_list'] ?? [];
        if ($deleteNodeUuidList) {
            $checkResult = $nodeLogicHandler->checkNodeNetworkList($deleteNodeUuidList);
            if (!$checkResult['success']) {
                return $checkResult;
            }
            $deleteNetworkList = $checkResult['data'];
        } else {
            $deleteNetworkList = [];
        }

        $opcodeName = 'NODE_NETWORK_OP_MODIFY_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $editResult = $this->service()->addOrEditNetworkPoolService(
            $networkPoolName,
            $params['network_uuid_list'],
            $params['remark'],
            $opcodeName,
            $params['node_uuid'],
            $params['network_pools_uuid']
        );
        if (!$editResult['result']) {
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_EDIT_ERROR'), false, 0);
        }
        $detail = array_map(function ($networkInfo) use ($networkPoolInfo) {
            return sprintf(
                xphp_get_lang('WEB_NODE_NETWORK_POOL_EDIT_DETAIL_DELETE'),
                $networkInfo['ip'] . ':' . $networkInfo['port'] . '(' . $networkInfo['network_name'] . ')',
                $networkPoolInfo['network_pool_nickname']
            );
        }, $deleteNetworkList);
        $detail = array_merge($detail, array_map(function ($networkInfo) use ($networkPoolInfo, $networkPoolName) {
            return sprintf(
                xphp_get_lang('WEB_NODE_NETWORK_POOL_EDIT_DETAIL_ADD'),
                $networkInfo['ip'] . ':' . $networkInfo['port'] . '(' . $networkInfo['network_name'] . ')',
                $networkPoolName
            );
        }, $addNetworkList));
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_EDIT_SUCCESS'), true, 200, $detail);
    }

    /**
     * 验证是否可以删除网络资源池
     * @param array $networkPoolUuidList 网络资源池uuid列表
     * @return array
     */
    private function validDeleteNetworkPool(array $networkPoolUuidList): array
    {
        $networkPoolUuidList = array_values(array_unique($networkPoolUuidList));
        $networkPoolUuids = "'" . implode("', '", $networkPoolUuidList) . "'";
        $sql = "SELECT bt.task_name, bnnp.network_pool_nickname
                FROM bd_task bt
                    INNER JOIN bd_transport_strategy bts ON bt.strategy_id = bts.strategy_id
                    INNER JOIN bd_node_network_pool bnnp ON bts.network_pool_uuid = bnnp.network_pool_uuid
                WHERE bts.network_pool_uuid IN ($networkPoolUuids) ";
        $taskData = $this->dbSelect($sql);
        if (is_array($taskData) && count($taskData)) {
            $networkPoolNicknameList = array_column($taskData, 'network_pool_nickname');
            $networkPoolNicknameList = array_values(array_unique($networkPoolNicknameList));
            return $this->sendResult(sprintf(
                xphp_get_lang('WEB_NODE_NETWORK_POOL_DELETE_HAS_TASK'),
                implode('、', $networkPoolNicknameList),
                implode('、', array_column($taskData, 'task_name'))
            ), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 删除网络资源池详情
     * @param string $networkPoolsUuid 网络资源池uuid
     * @return array
     */
    public function deleteNetworkPool(string $networkPoolsUuid): array
    {
        $checkResult = $this->checkNetworkPoolList([$networkPoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteNetworkPool([$networkPoolsUuid]);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_NETWORK_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $deleteResult = $this->service()->deleteNetworkPoolService([$networkPoolsUuid]);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_DELETE_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_DELETE_SUCCESS'));
    }

    /**
     * 批量删除网络资源池详情
     * @param array $networkPoolUuidList 网络资源池uuid列表
     * @return array
     */
    public function batchDeleteNetworkPool(array $networkPoolUuidList): array
    {
        $checkResult = $this->checkNetworkPoolList($networkPoolUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkPoolList = $checkResult['data'];

        // 资源拥有判断
        $userUuidList = array_column($networkPoolList, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $this->checkAuthByUserUuid(implode(',', $userUuidList), 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteNetworkPool($networkPoolUuidList);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_NETWORK_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $batchDeleteResult = $this->service()->deleteNetworkPoolService($networkPoolUuidList);
        if (!$batchDeleteResult['result']) {
            $this->muOpResult(false, $operate, $batchDeleteResult['msg'], 0, $batchDeleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_BATCH_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($item) {
            return sprintf(xphp_get_lang('WEB_NODE_NETWORK_POOL_DELETE_DETAIL'), $item['network_pool_nickname']);
        }, $networkPoolList);
        return $this->sendResult(xphp_get_lang('WEB_NODE_NETWORK_POOL_BATCH_DELETE_SUCCESS'), true, 200, $detail);
    }
}
