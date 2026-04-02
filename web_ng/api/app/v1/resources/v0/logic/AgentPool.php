<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;

class AgentPool extends Base
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
     * 获取排除的代理资源池uuid列表
     * @return array
     */
    private function getExcludeAgentUuidListByResource(): array
    {
        $excludeAgentPoolUuidList = [];
        if (v1_auth_need_check_look()) {
            // 获取拥有的代理资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']);
            // 查询不包含这些代理的资源池
            $sql = "SELECT agent_pool_uuid FROM bd_agent_pool_list WHERE agent_uuid NOT IN ($resourceUuidSql) GROUP BY agent_pool_uuid ";
            $excludeData = $this->dbSelect($sql);
            if (!is_array($excludeData)) {
                $excludeData = [];
            }
            $excludeAgentPoolUuidList = array_values(array_unique(array_column($excludeData, 'agent_pool_uuid')));
        }
        return $excludeAgentPoolUuidList;
    }

    /**
     * 获取计算资源池列表
     * @param array $params 资源池名称
     * @return array
     */
    public function getAgentPoolList(array $params): array
    {
        $sortFields = [
            'agent_pool_name' => 'bap.agent_pool_nickname',
            'update_time' => 'bap.last_modify_time',
            'creator' => 'bu.user_name',
            'remark' => 'bap.remark',
        ];
        $sort = $sortFields[$params['sort'] ?? 'update_time'] ?? 'bnp.last_modify_time';
        $order = $params['order'] ?? 'DESC';
        $sql = "SELECT bap.agent_pool_uuid, bap.agent_pool_nickname,
                    bap.last_modify_time, bap.user_uuid, bap.remark,
                    bu.user_name
                FROM bd_agent_pool bap
                    INNER JOIN bd_user bu ON bu.user_uuid = bap.user_uuid
                WHERE true ";
        $sqlCount = "SELECT COUNT(*) AS total FROM (
                        SELECT * FROM bd_agent_pool GROUP BY agent_pool_uuid
                    ) bap WHERE true ";
        if ($params['agent_pool_name'] ?? '') {
            $sql .= " AND bap.agent_pool_nickname LIKE '%{$params['agent_pool_name']}%' ";
            $sqlCount .= " AND bap.agent_pool_nickname LIKE '%{$params['agent_pool_name']}%' ";
        }

        // 资源拥有过滤
        $excludeAgentPoolUuidList = $this->getExcludeAgentUuidListByResource();
        $excludeAgentPoolUuids = "'" . implode("', '", $excludeAgentPoolUuidList) . "'";
        $sql .= " AND bap.agent_pool_uuid NOT IN ($excludeAgentPoolUuids) ";
        $sqlCount .= " AND bap.agent_pool_uuid NOT IN ($excludeAgentPoolUuids) ";

        $sql .= " GROUP BY bap.agent_pool_uuid ORDER BY $sort $order LIMIT {$params['offset']}, {$params['limit']}";
        $countData = $this->dbSelect($sqlCount);
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult('', true, 200, [
                'total' => 0,
                'rows' => [],
            ]);
        }

        // 这里需要分开查询，因为bd_agent_pool是冗余的，可能有多个值; 如果合在一起查询，对分页有影响
        $agentPoolUuidList = array_column($data, 'agent_pool_uuid');
        $agentPoolUuids = "'" . implode("', '", $agentPoolUuidList) . "'";
        $sql = "SELECT bapl.agent_uuid, bapl.agent_pool_uuid,
                    ba.agent_name, ba.ip, ba.hostname
                FROM bd_agent_pool_list bapl
                    LEFT JOIN bd_agent ba ON bapl.agent_uuid = ba.agent_uuid
                WHERE bapl.agent_pool_uuid IN ($agentPoolUuids) AND ba.agent_type = 4 ";
        $agentData = $this->dbSelect($sql);
        if (!$agentData || !is_array($agentData)) {
            $agentData = [];
        }

        $rows = [];
        foreach ($data as $agentPoolItem) {
            if (!isset($rows[$agentPoolItem['agent_pool_uuid']])) {
                $rows[$agentPoolItem['agent_pool_uuid']] = [
                    'agent_pool_uuid' => $agentPoolItem['agent_pool_uuid'],
                    'agent_pool_name' => $agentPoolItem['agent_pool_nickname'],
                    'agent_list' => [],
                    'update_time' => $agentPoolItem['last_modify_time'],
                    'creator' => $agentPoolItem['user_name'],
                    'user_uuid' => $agentPoolItem['user_uuid'],
                    'remark' => $agentPoolItem['remark'],
                ];
            }
            foreach ($agentData as $agentInfo) {
                if ($agentInfo['agent_pool_uuid'] == $agentPoolItem['agent_pool_uuid']) {
                    $rows[$agentPoolItem['agent_pool_uuid']]['agent_list'][] = [
                        'agent_name' => $agentInfo['agent_name'],
                        'agent_uuid' => $agentInfo['agent_uuid'],
                        'agent_ip' => $agentInfo['ip'],
                        'hostname' => $agentInfo['hostname'],
                    ];
                }
            }
        }

        return $this->sendResult('', true, 200, [
            'rows' => array_values($rows),
            'total' => $countData[0]['total'],
        ]);
    }

    /**
     * 获取计算资源池详情
     * @param string $agentPoolsUuid 计算资源池uuid
     * @return array
     */
    public function getAgentPoolDetail(string $agentPoolsUuid): array
    {
        $checkResult = $this->checkAgentPoolList([$agentPoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $sql = "SELECT bap.agent_pool_uuid, bap.agent_pool_nickname, bap.last_modify_time,
                    bap.create_time, bap.user_uuid, bap.remark,
                    ba.hostname, ba.ip AS agent_ip, ba.agent_uuid, ba.agent_name,
                    bu.user_name
                FROM bd_agent_pool bap
                    INNER JOIN bd_agent_pool_list bapl ON bap.agent_pool_uuid = bapl.agent_pool_uuid
                    INNER JOIN bd_agent ba ON ba.agent_uuid = bapl.agent_uuid
                    INNER JOIN bd_user bu ON bap.user_uuid = bu.user_uuid
                WHERE bap.agent_pool_uuid = ? ";
        $data = $this->dbSelect($sql, [$agentPoolsUuid]);
        $agentList = [];
        foreach ($data as $agentItem) {
            if (!isset($agentList[$agentItem['agent_uuid']])) {
                $agentList[$agentItem['agent_uuid']] = [
                    'agent_name' => $agentItem['agent_name'],
                    'agent_uuid' => $agentItem['agent_uuid'],
                    'agent_ip' => $agentItem['agent_ip'],
                    'hostname' => $agentItem['hostname'],
                ];
            }
        }

        return $this->sendResult('', true, 200, [
            'agent_pool_uuid' => $data[0]['agent_pool_uuid'],
            'agent_pool_name' => $data[0]['agent_pool_nickname'],
            'agent_list' => array_values($agentList),
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
    public function addAgentPool(array $params): array
    {
        // 去除html转义
        $params['agent_pool_name'] = htmlspecialchars_decode($params['agent_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $agentPoolName = trim($params['agent_pool_name']);
        $checkResult = $this->checkAgentPoolName($agentPoolName);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $checkResult = $this->checkAgentList($params['agent_uuid_list']);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $agentList = $checkResult['data'];

        $opcodeName = 'NODE_APPLIANCE_OP_ADD_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->addOrEditAgentPoolService(
            $agentPoolName,
            array_column($agentList, 'agent_uuid'),
            $params['remark'],
            $opcodeName
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_ADD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_ADD_SUCCESS'));
    }

    /**
     * 检查计算资源池名称
     * @param string $agentPoolName 计算资源池名称
     * @param string $agentPoolUuid 计算资源池uuid
     * @return array
     */
    private function checkAgentPoolName(string $agentPoolName, string $agentPoolUuid = ''): array
    {
        $sql = "SELECT * FROM bd_agent_pool WHERE agent_pool_nickname = ? ";
        $sqlParams = [trim($agentPoolName)];
        if ($agentPoolUuid) {
            $sql .= ' AND agent_pool_uuid != ? ';
            $sqlParams[] = $agentPoolUuid;
        }
        if ($this->dbSelect($sql, $sqlParams)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_NAME_ALREADY_EXISTS'), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 检查传输代理uuid列表
     * @param array $agentUuidList 传输代理uuid列表
     * @return array
     */
    private function checkAgentList(array $agentUuidList): array
    {
        $agentUuidList = array_values(array_unique($agentUuidList));
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
        $sql = "SELECT * FROM bd_agent WHERE bd_agent.agent_uuid IN ($agentUuids) AND agent_type = 4 ";
        $data = dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NOT_EXISTS'), false, 0);
        }
        if (count($agentUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_AGENT_SOME_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($agentUuidList, array_column($data, 'agent_uuid')))
            );
        }
        return $this->sendResult('', true, 200, $data);
    }

    /**
     * 修改传输代理资源池
     * @param array $params 参数
     * @return array
     */
    public function editAgentPool(array $params): array
    {
        // 去除html转义
        $params['agent_pool_name'] = htmlspecialchars_decode($params['agent_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $checkResult = $this->checkAgentPoolList([$params['agent_pools_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        $agentPoolName = trim($params['agent_pool_name']);
        $checkResult = $this->checkAgentPoolName($agentPoolName, $params['agent_pools_uuid']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $checkResult = $this->checkAgentList($params['agent_uuid_list']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $opcodeName = 'NODE_APPLIANCE_OP_MODIFY_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $editResult = $this->service()->addOrEditAgentPoolService(
            $agentPoolName,
            $params['agent_uuid_list'],
            $params['remark'],
            $opcodeName,
            $params['agent_pools_uuid']
        );
        if (!$editResult['result']) {
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_EDIT_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_EDIT_SUCCESS'));
    }

    /**
     * 验证是否可以删除代理资源池
     * @param array $agentPoolUuidList 代理资源池uuid列表
     * @return array
     */
    private function validDeleteAgentPool(array $agentPoolUuidList): array
    {
        $agentPoolUuidList = array_values(array_unique($agentPoolUuidList));
        $agentPoolUuids = "'" . implode("', '", $agentPoolUuidList) . "'";
        $sql = "SELECT bt.task_name, bap.agent_pool_nickname
                FROM bd_task bt
                    INNER JOIN bd_task_agent_pool btap ON bt.task_uuid = btap.task_uuid
                    INNER JOIN bd_agent_pool bap ON btap.agent_pool_uuid = bap.agent_pool_uuid
                WHERE btap.agent_pool_uuid IN ($agentPoolUuids) ";
        $taskData = $this->dbSelect($sql);
        if (is_array($taskData) && count($taskData)) {
            $agentPoolNicknameList = array_column($taskData, 'agent_pool_nickname');
            $agentPoolNicknameList = array_values(array_unique($agentPoolNicknameList));
            return $this->sendResult(sprintf(
                xphp_get_lang('WEB_AGENT_POOL_DELETE_HAS_TASK'),
                implode('、', $agentPoolNicknameList),
                implode('、', array_column($taskData, 'task_name'))
            ), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 删除计算资源池
     * @param string $agentPoolsUuid 计算资源池uuid
     * @return array
     */
    public function deleteAgentPool(string $agentPoolsUuid): array
    {
        $checkResult = $this->checkAgentPoolList([$agentPoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteAgentPool([$agentPoolsUuid]);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_APPLIANCE_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $deleteResult = $this->service()->deleteAgentPoolService([$agentPoolsUuid]);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_DELETE_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_DELETE_SUCCESS'));
    }

    /**
     * 批量删除计算资源池
     * @param array $agentPoolUuidList 存储池uuid列表
     * @return array
     */
    public function batchDeleteAgentPool(array $agentPoolUuidList): array
    {
        $checkResult = $this->checkAgentPoolList($agentPoolUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $agentPoolList = $checkResult['data'];

        // 资源拥有判断
        $userUuidList = array_column($agentPoolList, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $this->checkAuthByUserUuid(implode(',', $userUuidList), 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteAgentPool($agentPoolUuidList);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_APPLIANCE_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $batchDeleteResult = $this->service()->deleteAgentPoolService($agentPoolUuidList);
        if (!$batchDeleteResult['result']) {
            $this->muOpResult(false, $operate, $batchDeleteResult['msg'], 0, $batchDeleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_BATCH_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($item) {
            return sprintf(xphp_get_lang('WEB_AGENT_POOL_DELETE_DETAIL'), $item['agent_pool_nickname']);
        }, $agentPoolList);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_BATCH_DELETE_SUCCESS'), true, 200, $detail);
    }

    /**
     * 检查计算资源池uuid列表
     * @param array $agentPoolUuidList 计算资源池uuid列表
     * @return array
     */
    private function checkAgentPoolList(array $agentPoolUuidList): array
    {
        $agentPoolUuidList = array_values(array_unique($agentPoolUuidList));
        $agentPoolUuids = "'" . implode("', '", $agentPoolUuidList) . "'";
        $sql = "SELECT * FROM bd_agent_pool WHERE agent_pool_uuid IN ($agentPoolUuids) GROUP BY agent_pool_uuid";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_POOL_NOT_EXISTS'), false, 0);
        }

        if (count($agentPoolUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_AGENT_POOL_SOME_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($agentPoolUuidList, array_column($data, 'agent_pool_uuid')))
            );
        }
        return $this->sendResult('', true, 200, $data);
    }
}
