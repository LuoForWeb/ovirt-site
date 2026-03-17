<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;

class StoragePool extends Base
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
     * 根据存储uuid获取存储资源池列表
     * @param array $storageUuidList 存储uuid列表
     * @return array
     */
    public function getStoragePoolListByStorageUuid(array $storageUuidList): array
    {
        $storageUuids = "'" . implode("', '", $storageUuidList) . "'";
        $sql = "SELECT bsrp.storage_pool_nickname, bsrpl.storage_uuid, bsrpl.storage_pool_uuid
                FROM bd_storage_resource_pool bsrp
                    INNER JOIN bd_storage_resource_pool_list bsrpl ON bsrp.storage_pool_uuid = bsrpl.storage_pool_uuid
                WHERE bsrpl.storage_uuid IN ($storageUuids) ";

        // 资源拥有过滤
        $excludeStoragePoolUuidList = $this->getExcludeStoragePoolUuidListByResource();
        $excludeStoragePoolUuids = "'" . implode("', '", $excludeStoragePoolUuidList) . "'";
        $sql .= " AND bsrp.storage_pool_uuid NOT IN ($excludeStoragePoolUuids) ";

        $storagePoolData = $this->dbSelect($sql);
        $ret = [];
        foreach ($storagePoolData as $storagePoolInfo) {
            $ret[] = [
                'storage_pool_uuid' => $storagePoolInfo['storage_pool_uuid'],
                'storage_pool_nickname' => $storagePoolInfo['storage_pool_nickname'],
                'storage_uuid' => $storagePoolInfo['storage_uuid'],
            ];
        }
        return $ret;
    }

    /**
     * @param array $storageUuidList 存储uuid列表
     * @return array
     */
    public function buildStoragePoolInfoWithStorageUuid(array $storageUuidList): array
    {
        $storagePoolData = $this->getStoragePoolListByStorageUuid($storageUuidList);
        $storagePoolMap = [];
        foreach ($storagePoolData as $storagePoolInfo) {
            if (!isset($storagePoolMap[$storagePoolInfo['storage_uuid']])) {
                $storagePoolMap[$storagePoolInfo['storage_uuid']] = [];
            }
            $storagePoolMap[$storagePoolInfo['storage_uuid']][] = $storagePoolInfo;
        }
        return $storagePoolMap;
    }

    /**
     * 根据存储uuid获取存储挂载信息
     * @param array $storageUuidList 存储uuid列表
     * @return array
     */
    private function getStorageMountInfoByStorageUuidList(array $storageUuidList): array
    {
        $storageUuidList = array_values(array_unique($storageUuidList));
        $storageUuids = "'" . implode("', '", $storageUuidList) . "'";
        $sql = "SELECT storage_uuid, node_uuid, mount_flag, mount_point, status
                FROM bd_shared_storage_node_layout
                WHERE storage_uuid IN ($storageUuids) ";
        $storageMountData = $this->dbSelect($sql);
        if (!is_array($storageMountData)) {
            $storageMountData = [];
        }
        $nodeUuidList = array_column($storageMountData,'node_uuid');
        $nodeData = (new Node())->checkNodeList($nodeUuidList)['data'];
        $nodeMap = [];
        foreach ($nodeData as $nodeInfo) {
            $nodeMap[$nodeInfo['node_uuid']] = $nodeInfo;
        }
        $storageMountMap = [];
        foreach ($storageMountData as $storageMountInfo) {
            $tmpStorageInfo = [
                'mount_flag' => intval($storageMountInfo['mount_flag']),
                'mount_point' => $storageMountInfo['mount_point'],
                'mount_status' => intval($storageMountInfo['status']),
                'node_uuid' => '',
                'node_ip' => '',
                'node_name' => '',
            ];
            if (isset($nodeMap[$storageMountInfo['node_uuid']])) {
                $tmpStorageInfo['node_uuid'] = $storageMountInfo['node_uuid'];
                $tmpStorageInfo['node_ip'] = $nodeMap[$storageMountInfo['node_uuid']]['ip'];
                $tmpStorageInfo['node_name'] = $nodeMap[$storageMountInfo['node_uuid']]['host_name'];
            }
            $storageMountMap[$storageMountInfo['storage_uuid']][] = $tmpStorageInfo;
        }
        return $storageMountMap;
    }

    /**
     * 根据资源分配获取排除的存储资源池列表
     * @return array
     */
    private function getExcludeStoragePoolUuidListByResource(): array
    {
        $excludeStoragePoolUuidList = [];
        if (v1_auth_need_check_look()) {
            // 获取拥有的存储资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);
            // 查询不包含这些存储的资源池
            $sql = "SELECT storage_pool_uuid FROM bd_storage_resource_pool_list WHERE storage_uuid NOT IN ($resourceUuidSql) GROUP BY storage_pool_uuid ";
            $excludeData = $this->dbSelect($sql);
            if (!is_array($excludeData)) {
                $excludeData = [];
            }
            $excludeStoragePoolUuidList = array_values(array_unique(array_column($excludeData, 'storage_pool_uuid')));
        }
        return $excludeStoragePoolUuidList;
    }

    /**
     * 获取排除的存储uuid列表
     * @param array $excludeStorageTypeList 排除的存储类型列表
     * @return array
     */
    private function getExcludeStorageUuidByExcludeStorageTypeList(array $excludeStorageTypeList): array
    {
        $excludeStorageTypeList = array_values(array_unique($excludeStorageTypeList));
        $excludeStorageTypeUuids = "'" . implode("', '", $excludeStorageTypeList) . "'";
        $sql = "SELECT storage_uuid FROM bd_storage_resource WHERE storage_type IN ($excludeStorageTypeUuids) ";
        $storageUuidList = $this->dbSelect($sql);
        if (!is_array($storageUuidList) || !$storageUuidList) {
            return [];
        }
        return array_column($storageUuidList, 'storage_uuid');
    }

    /**
     * 根据存储uuid获取排除的存储资源池列表
     * @param array $storageUuidList 存储uuid列表
     * @return array
     */
    private function getExcludeStoragePoolUuidByExcludeStorageUuidList(array $storageUuidList): array
    {
        $storageUuidList = array_values(array_unique($storageUuidList));
        $storageUuids = "'" . implode("', '", $storageUuidList) . "'";
        $sql = "SELECT storage_pool_uuid, storage_uuid FROM bd_storage_resource_pool_list WHERE storage_uuid IN ($storageUuids) ";
        $excludeStoragePoolData = $this->dbSelect($sql);
        if (!is_array($excludeStoragePoolData) || !$excludeStoragePoolData) {
            return [];
        }
        $excludeStoragePoolMap = [];
        foreach ($excludeStoragePoolData as $excludeStoragePoolInfo) {
            if (!isset($excludeStoragePoolMap[$excludeStoragePoolInfo['storage_pool_uuid']])) {
                $excludeStoragePoolMap[$excludeStoragePoolInfo['storage_pool_uuid']] = [];
            }
            $excludeStoragePoolMap[$excludeStoragePoolInfo['storage_pool_uuid']][] = $excludeStoragePoolInfo['storage_uuid'];
        }
        // 查询存储资源池的所有存储uuid
        $storagePoolUuidList = array_values(array_unique(array_column($excludeStoragePoolData, 'storage_pool_uuid')));
        $storagePoolUuids = "'" . implode("', '", $storagePoolUuidList) . "'";
        $sql = "SELECT storage_pool_uuid, storage_uuid FROM bd_storage_resource_pool_list WHERE storage_pool_uuid IN ($storagePoolUuids) ";
        $storagePoolData = $this->dbSelect($sql);
        $excludeStoragePoolUuidList = [];
        if (!is_array($storagePoolData) || !$storagePoolData) {
            return $storagePoolUuidList;
        }
        $storagePoolMap = [];
        foreach ($storagePoolData as $storagePoolInfo) {
            if (!isset($storagePoolMap[$storagePoolInfo['storage_pool_uuid']])) {
                $storagePoolMap[$storagePoolInfo['storage_pool_uuid']] = [];
            }
            $storagePoolMap[$storagePoolInfo['storage_pool_uuid']][] = $storagePoolInfo['storage_uuid'];
        }
        foreach ($storagePoolMap as $storagePoolUuid => $storageUuidList) {
            if (isset($excludeStoragePoolMap[$storagePoolUuid])) {
                $diffStorageUuid = array_diff($storageUuidList, $excludeStoragePoolMap[$storagePoolUuid]);
                if (!$diffStorageUuid) {
                    $excludeStoragePoolUuidList[] = $storagePoolUuid;
                }
            }
        }
        // 查看
        return $excludeStoragePoolUuidList;
    }

    /**
     * 获取存储资源池列表
     * @param array $params 参数
     * @return array
     */
    public function getStoragePoolList(array $params): array
    {
        $sortFields = [
            'storage_pool_name' => 'bsrp.storage_pool_nickname',
            'update_time' => 'bsrp.last_modify_time',
            'creator' => 'bu.user_name',
            'remark' => 'bsrp.remark',
            'storage_pool_type' => 'bsrp.storage_pool_type',
        ];
        $sort = $sortFields[$params['sort'] ?? 'update_time'] ?? 'bnp.last_modify_time';
        $order = $params['order'] ?? 'DESC';
        $sql = "SELECT bsrp.storage_pool_uuid, bsrp.storage_pool_nickname, bsrp.storage_pool_type,
                    bsrp.last_modify_time, bsrp.user_uuid, bsrp.remark,
                    bu.user_name
                FROM bd_storage_resource_pool bsrp
                    INNER JOIN bd_user bu On bu.user_uuid = bsrp.user_uuid
                WHERE true ";
        $sqlCount = "SELECT COUNT(*) AS total FROM (
                        SELECT * FROM bd_storage_resource_pool GROUP BY storage_pool_uuid
                    ) bsrp WHERE true ";
        if ($params['storage_pool_name'] ?? '') {
            $sql .= " AND bsrp.storage_pool_nickname LIKE '%{$params['storage_pool_name']}%' ";
            $sqlCount .= " AND bsrp.storage_pool_nickname LIKE '%{$params['storage_pool_name']}%' ";
        }
        $excludeStoragePoolUuidList = [];
        $excludeStorageUuidList = [];
        if (isset($params['exclude_storage_type_list']) && is_array($params['exclude_storage_type_list']) && $params['exclude_storage_type_list']) {
            $excludeStorageUuidList = $this->getExcludeStorageUuidByExcludeStorageTypeList($params['exclude_storage_type_list']);
            if ($excludeStorageUuidList) {
                $excludeStoragePoolUuidList = $this->getExcludeStoragePoolUuidByExcludeStorageUuidList($excludeStorageUuidList);
            }
        }
        if ($excludeStoragePoolUuidList) {
            $excludeStoragePoolUuids = "'" . implode("', '", $excludeStoragePoolUuidList) . "'";
            $sql .= " AND bsrp.storage_pool_uuid NOT IN ($excludeStoragePoolUuids) ";
            $sqlCount .= " AND bsrp.storage_pool_uuid NOT IN ($excludeStoragePoolUuids) ";
        }

        // 资源拥有过滤
        $excludeStoragePoolUuidList = $this->getExcludeStoragePoolUuidListByResource();
        $excludeStoragePoolUuids = "'" . implode("', '", $excludeStoragePoolUuidList) . "'";
        $sql .= " AND bsrp.storage_pool_uuid NOT IN ($excludeStoragePoolUuids) ";
        $sqlCount .= " AND bsrp.storage_pool_uuid NOT IN ($excludeStoragePoolUuids) ";

        $sql .= " GROUP BY bsrp.storage_pool_uuid ORDER BY $sort $order LIMIT {$params['offset']}, {$params['limit']}";
        $countData = $this->dbSelect($sqlCount);
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult('', true, 200, [
                'total' => 0,
                'rows' => [],
            ]);
        }

        // 分开查询，同计算资源池
        $storagePoolUuidList = array_column($data, 'storage_pool_uuid');
        $storagePoolUuids  = "'" . implode("', '", $storagePoolUuidList) . "'";
        $sql = "SELECT bsrpl.storage_uuid, bsrpl.storage_pool_uuid,
                    bsr.storage_nickname, bsr.storage_type, bsr.node_uuid, bsr.total_size, bsr.free_size,
                    bsr.worm_flag, bsr.worm_allocate_type, bsr.storage_config, bsr.status, bsr.mount_flag,
                    bn.host_name, bn.ip, bn.node_nickname
                FROM bd_storage_resource_pool_list bsrpl
                    LEFT JOIN bd_storage_resource bsr ON bsrpl.storage_uuid = bsr.storage_uuid
                    LEFT JOIN bd_node bn ON bn.node_uuid = bsr.node_uuid
                WHERE bsrpl.storage_pool_uuid IN ($storagePoolUuids) ";
        if ($excludeStorageUuidList) {
            $excludeStorageUuids = "'" . implode("', '", $excludeStorageUuidList) . "'";
            $sql .= " AND bsr.storage_uuid NOT IN ($excludeStorageUuids) ";
        }
        $storageData = $this->dbSelect($sql);
        if (!$storageData || !is_array($storageData)) {
            $storageData = [];
        }

        $storageUuidList = array_column($storageData,'storage_uuid');
        $storageMountMap = $this->getStorageMountInfoByStorageUuidList($storageUuidList);

        $allStorageTypeDes = xphp_get_desc('Pf', 'STORAGETYPE');
        $allStorageWormType = xphp_get_config('resource', 'STORAGEWORMTYPE');
        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $storageHandler = new Storage();
        $nodeHandler = new Node();
        $rows = [];
        foreach ($data as $storagePoolItem) {
            if (!isset($rows[$storagePoolItem['storage_pool_uuid']])) {
                $rows[$storagePoolItem['storage_pool_uuid']] = [
                    'storage_pool_uuid' => $storagePoolItem['storage_pool_uuid'],
                    'storage_pool_name' => $storagePoolItem['storage_pool_nickname'],
                    'storage_pool_type' => (int) $storagePoolItem['storage_pool_type'],
                    'storage_list' => [],
                    'update_time' => $storagePoolItem['last_modify_time'],
                    'creator' => $storagePoolItem['user_name'],
                    'user_uuid' => $storagePoolItem['user_uuid'],
                    'remark' => $storagePoolItem['remark'],
                ];
            }
            foreach ($storageData as $storageInfo) {
                if ($storageInfo['storage_pool_uuid'] == $storagePoolItem['storage_pool_uuid']) {
                    $nodeAllStatus = $nodeHandler->getNodeAllStatus($storageInfo['node_uuid']);
                    $nodeStatus = $storageHandler->getNodeStatus($storageInfo)[0];
                    $storageConfig = json_decode($storageInfo['storage_config'], true);
                    //异地备份系统节点显示
                    if ($storageInfo['storage_type'] == $allStorageType['REMOTE']) {
                        $nodeStatus = v1_parse_flag_to_bool(intval($storageConfig['remote_status']));
                    }
                    $storageStatus = $storageHandler->getStorageStatus(
                        $nodeAllStatus,
                        intval($storageInfo['status']),
                        intval($storageInfo['mount_flag']),
                        $storageInfo['storage_type']
                    );
                    $wormAllocateValue = $storageInfo['worm_allocate_value'] ?? '';
                    if ($allStorageWormType['SIZE'] == $storageInfo['worm_allocate_type']) {
                        //如果是按照大小来告警
                        $wormAllocateValue = v1_calsize($storageInfo['worm_allocate_value']);
                    }
                    $rows[$storagePoolItem['storage_pool_uuid']]['storage_list'][] = [
                        'storage_name' => $storageInfo['storage_nickname'],
                        'storage_nickname' => $storageInfo['storage_nickname'],
                        'storage_uuid' => $storageInfo['storage_uuid'],
                        'storage_type' => $storageInfo['storage_type'],
                        'storage_type_des' => $allStorageTypeDes[$storageInfo['storage_type']],
                        'total_size' => (int) $storageInfo['total_size'],
                        'free_size' => (int) $storageInfo['free_size'],
                        'storage_status' => $storageStatus,
                        'node_online_flag' => $nodeStatus,
                        'worm' => [
                            'worm_flag' => v1_parse_flag_to_bool($storageInfo['worm_flag']),
                            'worm_allocate_type' => intval($storageInfo['worm_allocate_type']),
                            'worm_allocate_value' => $wormAllocateValue,
                        ],
                        'node_hostname' => $storageInfo['host_name'],
                        'node_nickname' => $storageInfo['node_nickname'] ?? '',
                        'node_ip' => $storageInfo['ip'],
                        'node_uuid' => $storageInfo['node_uuid'],
                        'mount_point_list' => $storageMountMap[$storageInfo['storage_uuid']] ?? [],
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
     * 获取存储资源池详情
     * @param string $storagePoolsUuid 存储资源池uuid
     * @return array
     */
    public function getStoragePoolDetail(string $storagePoolsUuid): array
    {
        $checkResult = $this->checkStoragePoolList([$storagePoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $sql = "SELECT bsrp.storage_pool_uuid, bsrp.storage_pool_nickname, bsrp.storage_pool_type,
                    bsrp.last_modify_time, bsrp.create_time, bsrp.user_uuid, bsrp.remark,
                    bn.host_name, bn.ip AS node_ip, bn.node_uuid,
                    bu.user_name,
                    bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type, bsr.total_size, bsr.free_size
                FROM bd_storage_resource_pool bsrp
                    INNER JOIN bd_storage_resource_pool_list bsrpl ON bsrp.storage_pool_uuid = bsrpl.storage_pool_uuid
                    LEFT JOIN bd_storage_resource bsr ON bsrpl.storage_uuid = bsr.storage_uuid
                    INNER JOIN bd_node bn ON bn.node_uuid = bsr.node_uuid
                    INNER JOIN bd_user bu ON bsrp.user_uuid = bu.user_uuid
                WHERE bsrp.storage_pool_uuid = ? ";
        $data = $this->dbSelect($sql, [$storagePoolsUuid]);
        $storageList = [];
        foreach ($data as $storageItem) {
            if (!isset($storageList[$storageItem['storage_uuid']])) {
                $storageList[$storageItem['storage_uuid']] = [
                    'storage_name' => $storageItem['storage_nickname'],
                    'storage_uuid' => $storageItem['storage_uuid'],
                    'storage_type' => $storageItem['storage_type'],
                    'total_size' => (int) $storageItem['total_size'],
                    'free_size' => (int) $storageItem['free_size'],
                    'node_uuid' => $storageItem['node_uuid'],
                    'node_name' => $storageItem['host_name'],
                    'node_ip' => $storageItem['node_ip'],
                ];
            }
        }

        return $this->sendResult('', true, 200, [
            'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
            'storage_pool_name' => $data[0]['storage_pool_nickname'],
            'storage_pool_type' => (int) $data[0]['storage_pool_type'],
            'storage_list' => array_values($storageList),
            'update_time' => $data[0]['last_modify_time'],
            'creator' => $data[0]['user_name'],
            'remark' => $data[0]['remark'],
        ]);
    }

    /**
     * 检查存储资源池uuid列表
     * @param array $storagePoolUuidList 存储资源池uuid列表
     * @return array
     */
    private function checkStoragePoolList(array $storagePoolUuidList): array
    {
        $storagePoolUuidList = array_values(array_unique($storagePoolUuidList));
        $storagePoolUuids = "'" . implode("', '", $storagePoolUuidList) . "'";
        $sql = "SELECT * FROM bd_storage_resource_pool
                WHERE storage_pool_uuid IN ($storagePoolUuids)
                GROUP BY storage_pool_uuid ";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_POOL_NOT_EXISTS'), false, 0);
        }

        if (count($storagePoolUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_STORAGE_POOL_SOME_POOL_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($storagePoolUuidList, array_column($data, 'storage_pool_uuid')))
            );
        }

        return $this->sendResult('', true, 200, $data);
    }

    /**
     * 添加存储资源池详情
     * @param array $params 参数
     * @return array
     */
    public function addStoragePool(array $params): array
    {
        // 去除html转义
        $params['storage_pool_name'] = htmlspecialchars_decode($params['storage_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $storagePoolName = trim($params['storage_pool_name']);
        $storagePoolType = (int) $params['storage_pool_type'];
        $checkResult = $this->checkStoragePoolName($storagePoolName);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $checkResult = $this->checkStorageListWithType($params['storage_uuid_list'], $storagePoolType);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $opcodeName = 'NODE_SR_OP_ADD_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $addResult = $this->service()->addOrEditStoragePoolService(
            $storagePoolName,
            $storagePoolType,
            $params['storage_uuid_list'],
            $params['remark'],
            $opcodeName
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_ADD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_ADD_SUCCESS'));
    }

    /**
     * 检查存储资源池名称
     * @param string $storagePoolName 存储资源池名称
     * @param string $storagePoolUuid 存储资源池uuid
     * @return array
     */
    private function checkStoragePoolName(string $storagePoolName, string $storagePoolUuid = ''): array
    {
        $sql = "SELECT * FROM bd_storage_resource_pool WHERE storage_pool_nickname = ? ";
        $sqlParams = [trim($storagePoolName)];
        if ($storagePoolUuid) {
            $sql .= ' AND storage_pool_uuid != ? ';
            $sqlParams[] = $storagePoolUuid;
        }
        if ($this->dbSelect($sql, $sqlParams)) {
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_NAME_ALREADY_EXISTS'), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 检查存储设备uuid
     * <p>不能跨存储池类别</p>
     * @param array $storageUuidList 存储设备uuid列表
     * @param int   $storagePoolType 存储资源池类别【1集中式存储 2NAS存储 3云存储】
     * @return array
     */
    private function checkStorageListWithType(array $storageUuidList, int $storagePoolType): array
    {
        $storageUuidList = array_values(array_unique($storageUuidList));
        $storageUuids = "'" . implode("', '", $storageUuidList) . "'";
        $sql = "SELECT * FROM bd_storage_resource
                WHERE storage_uuid IN ($storageUuids)";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_NOT_EXISTS'), false, 0);
        }

        if (count($storageUuidList) != count($data)) {
            return $this->sendResult(
                xphp_get_lang('WEB_STORAGE_SOME_NOT_EXISTS'),
                false,
                0,
                array_values(array_diff($storageUuidList, array_column($data, 'storage_uuid')))
            );
        }

        // 跨存储池判断
        $storagePoolTypeMap = xphp_get_config('storage', 'STORAGE_POOL_TYPE_MAP');
        $storageTypeList = $storagePoolTypeMap[$storagePoolType];
        $otherStorageList = [];
        foreach ($data as $item) {
            if (!in_array($item['storage_type'], $storageTypeList)) {
                $otherStorageList[] = $item['storage_nickname'];
            }
        }
        if ($otherStorageList) {
            return $this->sendResult(sprintf(
                xphp_get_lang('WEB_STORAGE_POOL_CONTAIN_OTHER_STORAGE'),
                xphp_get_config('storage', 'STORAGE_POOL_TYPE_DES')[$storagePoolType],
                implode(', ', $otherStorageList)
            ), false, 0, $otherStorageList);
        }

        return $this->sendResult('', true, 200, $data);
    }

    /**
     * 修改存储资源池详情
     * @param array $params 参数
     * @return array
     */
    public function editStoragePool(array $params): array
    {
        // 去除html转义
        $params['storage_pool_name'] = htmlspecialchars_decode($params['storage_pool_name']);
        $params['remark'] = htmlspecialchars_decode($params['remark']);
        $checkResult = $this->checkStoragePoolList([$params['storage_pools_uuid']]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        $storagePoolName = trim($params['storage_pool_name']);
        $storagePoolType = (int) $params['storage_pool_type'];
        $checkResult = $this->checkStoragePoolName($storagePoolName, $params['storage_pools_uuid']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $checkResult = $this->checkStorageListWithType($params['storage_uuid_list'], $storagePoolType);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $opcodeName = 'NODE_SR_OP_MODIFY_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $editResult = $this->service()->addOrEditStoragePoolService(
            $storagePoolName,
            $storagePoolType,
            $params['storage_uuid_list'],
            $params['remark'],
            $opcodeName,
            $params['storage_pools_uuid']
        );
        if (!$editResult['result']) {
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_EDIT_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_EDIT_SUCCESS'));
    }

    /**
     * 验证是否可以删除存储资源池
     * @param array $storagePoolUuidList 存储资源池uuid列表
     * @return array
     */
    private function validDeleteStoragePool(array $storagePoolUuidList): array
    {
        $storagePoolUuidList = array_values(array_unique($storagePoolUuidList));
        $storagePoolUuids = "'" . implode("', '", $storagePoolUuidList) . "'";
        $sql = "SELECT bt.task_name, bsrp.storage_pool_nickname
                FROM bd_task bt
                    INNER JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid
                WHERE bt.storage_pool_uuid IN ($storagePoolUuids) ";
        $taskData = $this->dbSelect($sql);
        if (is_array($taskData) && count($taskData)) {
            $storagePoolNicknameList = array_column($taskData, 'storage_pool_nickname');
            $storagePoolNicknameList = array_values(array_unique($storagePoolNicknameList));
            return $this->sendResult(sprintf(
                xphp_get_lang('WEB_STORAGE_POOL_DELETE_HAS_TASK'),
                implode('、', $storagePoolNicknameList),
                implode('、', array_column($taskData, 'task_name'))
            ), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 删除存储资源池详情
     * @param string $storagePoolsUuid 存储资源池uuid
     * @return array
     */
    public function deleteStoragePool(string $storagePoolsUuid): array
    {
        $checkResult = $this->checkStoragePoolList([$storagePoolsUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($checkResult['data'][0]['user_uuid'], 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteStoragePool([$storagePoolsUuid]);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_SR_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $deleteResult = $this->service()->deleteStoragePoolService([$storagePoolsUuid]);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_DELETE_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_DELETE_SUCCESS'));
    }

    /**
     * 批量删除存储资源池详情
     * @param array $storagePoolUuidList 存储资源池uuid列表
     * @return array
     */
    public function batchDeleteStoragePool(array $storagePoolUuidList): array
    {
        $checkResult = $this->checkStoragePoolList($storagePoolUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $networkPoolList = $checkResult['data'];

        // 资源拥有判断
        $userUuidList = array_column($networkPoolList, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $this->checkAuthByUserUuid(implode(',', $userUuidList), 'resmanagement');

        // 验证资源池
        $validResult = $this->validDeleteStoragePool($storagePoolUuidList);
        if (!$validResult['success']) {
            return $validResult;
        }

        $opcodeName = 'NODE_SR_OP_DELETE_POOL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $batchDeleteResult = $this->service()->deleteStoragePoolService($storagePoolUuidList);
        if (!$batchDeleteResult['result']) {
            $this->muOpResult(false, $operate, $batchDeleteResult['msg'], 0, $batchDeleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_BATCH_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($item) {
            return sprintf(xphp_get_lang('WEB_STORAGE_POOL_DELETE_DETAIL'), $item['storage_pool_nickname']);
        }, $networkPoolList);
        return $this->sendResult(xphp_get_lang('WEB_STORAGE_POOL_BATCH_DELETE_SUCCESS'), true, 200, $detail);
    }
}
