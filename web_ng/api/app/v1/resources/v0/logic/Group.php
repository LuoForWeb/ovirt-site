<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          资源组列表logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:23
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Group extends Base
{
    // 表示是租户管理员 (说明，只知道是租户成员，但是有这个资源分配权限就默认是管理员)
    private $isTenantAdmin;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->isTenantAdmin = !empty(xphp_get_user_info()['tenantuuid']);
    }

    /**
     * 获取资源组列表
     * @param array $params 数组
     * @return array 数组
     */
    public function getResourceData(array $params): array
    {
        $offset = ((int)$params['offset']) ?: 0;
        $limit = ((int)$params['limit']) ?: 10;
        $search = $params['search'];

        $where = ' where 1 = 1 ';
        $join = '';

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['source']
            );
            $sqlNew = " and brg.create_user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        $sql = "select distinct brg.create_time, brg.create_user_name, brg.resource_group_uuid,
                brg.resource_group_name, brg.description, brg.config,murg.resource_type,
                brg.create_user_uuid user_uuid
                from bd_resource_group brg {$join}
                left join mt_resource_resource_group murg 
                on brg.resource_group_uuid = murg.resource_group_uuid ";

        $sqlParams = [];
        if ($this->checkEmpty($search)) {
            $where .= ' and brg.resource_group_name like ? ';
            $sqlParams = ['%' . $search . '%'];
        }

        $sqlCount = "select count(brg.resource_group_uuid) as total from bd_resource_group brg {$join} {$where}";

        $dataCount = $this->dbSelect($sqlCount, $sqlParams);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'resource_group_name' => 'brg.resource_group_name',
                'description' => 'brg.description',
                'create_time' => 'brg.create_time',
                'create_user_name' => 'brg.create_user_name',
                'source_type_des' => 'murg.resource_type',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' brg.id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', brg.id desc');
            $order = " order by " . $sort;
        } else {
            $order = '';
        }

        $sql .= $where . $order . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);

        $rows = [];
        $sourceType = xphp_get_config('resource', 'RESOURCE_DIS_TYPE');
        foreach ($data as $item) {
            $rows[] = [
                'resource_group_name' => $item['resource_group_name'],
                'source_type' => $item['resource_type'],
                'source_type_des' => xphp_get_lang($sourceType[$item['resource_type']] ?? ''),
                'description' => $item['description'],
                'create_user_name' => $item['create_user_name'],
                'create_time' => $item['create_time'],
                'user_uuid' => $item['user_uuid'],
                'uuid' => $item['resource_group_uuid'],
            ];
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $rows
        ];
    }

    /**
     * 删除资源组
     * @param array $params 数组
     * @return string
     */
    public function deleteResource(array $params)
    {

        $uuids = implode("','", $params['uuids']);
        $list = $params['uuids'];

        // 操作权限判读
        $sql = "select create_user_uuid from bd_resource_group where resource_group_uuid in ('{$uuids}')";
        $data = $this->dbSelect($sql);
        $userArr = implode(',', array_unique(array_column($data, 'create_user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['source']);

        $sql = "delete from bd_resource_group where resource_group_uuid in ('{$uuids}')";
        $result = $this->dbExec($sql);
        //删除资源组返回结果到界面
        if ($result) {
            foreach ($list as $item) {
                //取消资源组与用户关联
                $this->pDeleteResourceGroupUser($item);
                //取消资源组与用户组关联
                $this->pDeleteResourceGroupUserGroup($item);
                //取消资源组与资源关联
                $this->pDeleteResourceGroupResource($item);
            }

            return $this->muOpResult(true, xphp_get_lang('UI_RESOURCE_GROUP_DELETE'));
        } else {
            return $this->muOpResult(false, xphp_get_lang('UI_RESOURCE_GROUP_DELETE'));
        }
    }

    /**
     * 修改资源组
     * @param array $params 数组
     * @return string
     */
    public function editResource(array $params)
    {
        $resourceGroupUUID = $params['group_uuid'];
        // 操作权限判读
        $sql = "select create_user_uuid from bd_resource_group where resource_group_uuid = ? ";
        $data = $this->dbSelect($sql, [$resourceGroupUUID]);
        $this->checkAuthByUserUuid($data[0]['create_user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);

        $resourceGroupName = $params['name'];
        $opName = xphp_get_lang('UI_RESOURCE_GROUP_MODIFY'); //修改资源组
        $description = $params['remark'];
        $editTime = date('Y-m-d H:i:s');
        $sqlParams = array($resourceGroupName, $description, $editTime, $resourceGroupUUID);
        $sql = "update bd_resource_group set resource_group_name = ?, description = ?, create_time = ? 
                where resource_group_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);
        $type = $params['source_type'];
        $rsourceList = $params['source_list'];
        $sql = "delete from mt_resource_resource_group where resource_group_uuid = ? ";
        $sqlParams = $resourceGroupUUID;
        $resultDelete = $this->dbExec($sql, [$sqlParams]);
        $resultId = true;
        if (!empty($rsourceList)) {
            if (in_array($type, [3, 58])) { //如果是虚拟机类型需要填入vcenter_uuid
                foreach ($rsourceList as $d) {
                    $sqlVcenter = "select vcenter_uuid from vm_tree where uuid = '$d'";
                    $sqlVcenterParams = array();
                    $dataVcenter = $this->dbSelect($sqlVcenter, $sqlVcenterParams);
                    $sqlIdParams = array($d, $resourceGroupUUID, $d,$dataVcenter[0]['vcenter_uuid'], $type);
                    $sqlId = "insert into mt_resource_resource_group 
                              (resource_uuid, resource_group_uuid, vm_uuid,vcenter_uuid,resource_type) 
                              value(?, ?, ?, ?, ?)";
                    $resultId = $this->dbQuery($sqlId, $sqlIdParams);
                }
            } else {
                foreach ($rsourceList as $d) {
                    $sqlIdParams = array($d, $resourceGroupUUID, $type);
                    $sqlId = "insert into mt_resource_resource_group 
                              (resource_uuid, resource_group_uuid, resource_type) value(?, ?, ?)";
                    $resultId = $this->dbQuery($sqlId, $sqlIdParams);
                }
            }
        }
        //修改资源组返回结果
        if ($result && $resultDelete && $resultId) {
            return $this->muOpResult(true, $opName);
        } else {
            return $this->muOpResult(false, $opName);
        }
    }

    /**
     * 添加资源组
     * @param array $params 数组
     * @return array 数组
     */
    public function addResource($params)
    {
        $resourceGroupName = $params['name'];
        $opName = xphp_get_lang('UI_RESOURCE_GROUP_ADD'); //添加资源组
        $description = $params['remark'];
        $type = $params['source_type'];
        $rsourceList = $params['source_list'];
        $user = xphp_get_user_info();
        $uuid = xphp_uuid();
        $createTime = date('Y-m-d H:i:s');
        $useruuid = $user['userUuid'] ?: '';
        $username = $user['userName'] ?: '';

        $sqlParams = array($uuid, $resourceGroupName, $description,
            $user['tenantuuid'] ?: '', $createTime, $useruuid, $username);
        $sql = "insert into bd_resource_group 
                (resource_group_uuid, resource_group_name, description, 
                 tenant_uuid, create_time, create_user_uuid, create_user_name) 
                values (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbQuery($sql, $sqlParams);
        $resultId = true;
        if (!empty($rsourceList)) {
            if (in_array($type, [3, 58])) {
                foreach ($rsourceList as $d) {
                    $sqlVcenter = "select  vcenter_uuid from vm_tree where uuid = '$d'";
                    $sqlVcenterParams = array();
                    $dataVcenter = $this->dbSelect($sqlVcenter, $sqlVcenterParams);
                    $sqlIdParams = array($d,$uuid, $d,$dataVcenter[0]['vcenter_uuid'], $type );
                    $sqlId = "insert into mt_resource_resource_group 
                             (resource_uuid, resource_group_uuid, vm_uuid,vcenter_uuid,resource_type) 
                             value(?, ?, ?, ?,?)";
                    $resultId = $this->dbQuery($sqlId, $sqlIdParams);
                }
            } else {
                foreach ($rsourceList as $d) {
                    $sqlIdParams = array($d, $uuid, $type);
                    $sqlId = "insert into mt_resource_resource_group 
                              (resource_uuid, resource_group_uuid, resource_type) value(?, ?, ?)";
                    $resultId = $this->dbQuery($sqlId, $sqlIdParams);
                }
            }
        }
        //添加资源组返回结果
        if ($result && $resultId) {
            //添加系统操作日志
            $this->systemLog('SYSTEM_RESOURCE_GROUP_ADD_SUCCESS', array($resourceGroupName));
            return $this->muOpResult(true, $opName);
        } else {
            return $this->muOpResult(false, $opName);
        }
    }

    /**
     * 添加资源组和用户关联
     * @param string $useruuid          字符串
     * @param array  $resourceGroupList 数组
     * @return bool 布尔
     */
    public function pAddUserResourceGroup($useruuid, $resourceGroupList)
    {
        if (empty($resourceGroupList)) {
            return false;
        }
        $sql = "insert into mt_user_resource_group (user_uuid, resource_group_uuid) values ";
        foreach ($resourceGroupList as $key => $resourceGroupUUID) {
            $sql .= "('" . $useruuid . "','" . $resourceGroupUUID . "')";
            if ($key != (count($resourceGroupList) - 1)) {
                $sql .= ',';
            }
        }

        $result = $this->dbExec($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加资源组和租户关联
     * @param string $tenantuuid        字符串
     * @param array  $resourceGroupList 数组
     * @return bool 布尔
     */
    private function pAddResourceGroupTenant($tenantuuid, $resourceGroupList)
    {
        if (empty($resourceGroupList)) {
            return false;
        }
        $tmpSql = '';
        foreach ($resourceGroupList as $key => $l) {
            $resourcegroupuuid = $l;

            $tmpSql .= "('" . $resourcegroupuuid . "','" . $tenantuuid . "')";
            if ($key != (count($resourceGroupList) - 1)) {
                $tmpSql .= ',';
            }
        }
        $sql = "insert into mt_resource_group_tenant (resource_group_uuid, tenant_uuid) values $tmpSql";

        $result = $this->dbExec($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除资源组-内部调用
     * @param string $list 字符串
     * @return bool 布尔
     */
    public function pDeleteResourceGroupUser($list)
    {
        if (empty($list)) {
            return true;
        }
        $sql = "delete from mt_user_resource_group where resource_group_uuid = '$list'";
        $result = $this->dbExec($sql);

        return true;
    }

    /**
     * 删除资源组和用户组关联
     * @param string $list 字符串
     * @return bool 布尔
     */
    public function pDeleteResourceGroupUserGroup($list)
    {
        if (empty($list)) {
            return true;
        }
        $sql = "delete from mt_user_group_resource_group where resource_group_uuid = '$list'";
        $result = $this->dbExec($sql);

        return true;
    }

    /**
     * 取消资源组和资源关联
     * @param string $list 字符串
     * @return bool 布尔
     */
    public function pDeleteResourceGroupResource($list)
    {
        if (empty($list)) {
            return true;
        }
        $sql = "delete from mt_resource_resource_group where resource_group_uuid = '$list'";
        $result = $this->dbExec($sql);

        return true;
    }

    /**
     * 获取资源组详细的资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getDetail($params)
    {
        $uuid = $params['groupDetail_uuid'];
        $sql = "select resource_uuid, vm_uuid, vcenter_uuid,resource_type from mt_resource_resource_group 
                where resource_group_uuid = '$uuid'";
        $data = $this->dbSelect($sql);
        $idList = [];

        foreach ($data as $d) {
            if (!empty($d['vm_uuid'])) {
                $idList[] = $d['vm_uuid'];
            } elseif (!empty($d['resource_uuid'])) {
                $idList[] = $d['resource_uuid'];
            }
        }
        return array(
            'type' => $data[0]['resource_type'] ?? '',
            'sourceList' => $idList
        );
    }

    /**
     * 获取资源组详细的虚拟机资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getDetailVm(array $params)
    {

        $resourceVm = [];
        if (!empty($params)) {
            $vmList = implode("','", $params);
            $sqlvm = "select vt.uuid from vm_tree vt";
            $sqlvm .= " where vt.uuid in ('$vmList')";
            $dataVm = $this->dbSelect($sqlvm);
            $resourceVm = array_column($dataVm, 'uuid');
        }
        return $resourceVm;
    }

    /**
     * 获取资源组详细的虚拟机传输代理资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getDetailAppliance($params)
    {

        $resourceList = $params;
        foreach ($resourceList as $item) {
            $result[] = "'" . $item . "'";
        }
        $resourceAPP = array();
        if (!empty($resourceList)) {
            $appList = implode(', ', $result);
            $sqlAPP = "select appliance_uuid, node_uuid, nickname, online_flag from bd_appliance";
            $sqlvmParams = array();
            $sqlAPP .= " where appliance_uuid in ($appList)";
            $dataAPP = $this->dbSelect($sqlAPP, $sqlvmParams);
            foreach ($dataAPP as $d) {
                if (!empty($d)) {
                    $vmId = $d['appliance_uuid'];
                    $resourceAPP [] = $vmId;
                }
            }
        }

        return $resourceAPP;
    }

    /**
     * 获取资源组详细的节点资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getDetailNode($params)
    {
        $resourceList = $params;
        $resourceNode = array();
        if (!empty($resourceList)) {
            foreach ($resourceList as $item) {
                $result[] = "'" . $item . "'";
            }
            $nodeList = implode(', ', $result);
            $sqlnode = "select DISTINCT bd.node_uuid from bd_node bd";
            $sqlvmParams = array();
            $sqlnode .= " where bd.node_uuid in ($nodeList)";
            $dataNode = $this->dbSelect($sqlnode, $sqlvmParams);
            foreach ($dataNode as $d) {
                if (!empty($d)) {
                    $vmId = $d['node_uuid'];
                    $resourceNode [] = $vmId;
                }
            }
        }
        return $resourceNode;
    }

    /**
     * 获取资源组详细的存储资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getDetailStorage($params)
    {
        $resourceList = $params;
        $resourceStorage = array();
        if (!empty($resourceList)) {
            foreach ($resourceList as $item) {
                $result[] = "'" . $item . "'";
            }
            $storageList = implode(', ', $result);
            $sqlstorage = "select DISTINCT  storage_uuid from bd_storage_resource";
            $sqlParams = array();
            $sqlstorage .= " where storage_uuid in ($storageList)";
            $dataStorage = $this->dbSelect($sqlstorage, $sqlParams);
            foreach ($dataStorage as $d) {
                if (!empty($d)) {
                    $vmId = $d['storage_uuid'];
                    $resourceStorage [] = $vmId;
                }
            }
        }
        return $resourceStorage;
    }

    /**
     * 获取资源组详细的客户端资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getDetailclient($params)
    {
        $resourceList = $params;
        $resourceClient = array();
        if (!empty($resourceList)) {
            foreach ($resourceList as $item) {
                $result[] = "'" . $item . "'";
            }
            $clientList = implode(', ', $result);
            $sqlclient = "SELECT ba.agent_uuid,ba.agent_name,ba.hostname,ba.online_flag
	                  FROM bd_agent ba
	                  WHERE ba.agent_type != 3";
            $sqlParams = array();
            $sqlclient .= " and ba.agent_uuid in ($clientList)";
            $dataClient = $this->dbSelect($sqlclient, $sqlParams);
            foreach ($dataClient as $d) {
                if (!empty($d)) {
                    $vmId = $d['agent_uuid'];
                    $resourceClient [] = $vmId;
                }
            }
        }


        return $resourceClient;
    }

    /**
     * 获取全部资源
     * @param array $params 数组
     * @return array 数组
     */
    public function getAllResource($params)
    {
        $sourcetype = $params['source_type'];
        $vmtype = $params['vm_type'];
        $offset = ((int)$params['offset']) ?: 0;
        $limit = ((int)$params['limit']) ?: 10;
        $uuid = $params['uuid'] ?: ''; // 修改资源组的uuid
        $userUuid = $params['user_uuid'] ?: '';
        $search = v1_escape_wildcard($params['search']) ?? '';
        if (!empty($params['type']) && $params['type'] == 2) {
            // 获取资源组
            return $this->getResourceList($sourcetype, $offset, $limit, $search, $userUuid);
        }
        switch ($sourcetype) {
            case 2:// 传输代理合并到客户端里面去了
                return $this->getAllclient($offset, $limit, $uuid, 2, $search, $userUuid);
                break;
            case 3:
                return $this->getAllVm($offset, $limit, $uuid, $search, $sourcetype, $vmtype);
                break;
            case 7:
                return $this->getAllNode($offset, $limit, $search, $sourcetype, $userUuid);
                break;
            case 8:
                return $this->getAllStorage($offset, $limit, $search, $sourcetype, $userUuid);
                break;
            case 10:
                return $this->getAllclient($offset, $limit, $uuid, 1, $search, $userUuid);
                break;
            case 56: // 组织管理
                return $this->getAllM365Org($offset, $uuid, $limit, $search);
                break;
            case 57: // nas设备
                return $this->getAllNas($offset, $uuid, $limit, $search);
                break;
            case 58:// 公有云平台 和虚拟机类似
                return $this->getAllVm($offset, $limit, $uuid, $search, $sourcetype, $vmtype);
                break;
            case 59: // 对象存储
                return $this->getAllObs($offset, $uuid, $limit, $search);
                break;
            case 60: // Hadoop集群
                return $this->getAllHadoop($offset, $uuid, $limit, $search);
                break;
            case 61: // 私有云
                return $this->getAllVm($offset, $limit, $uuid, $search, $sourcetype, $vmtype);
                break;
            case 62: // kubernetes集群
                return $this->getAllK8s($offset, $limit, $search, $sourcetype, $userUuid);
                break;
            default:
                return [
                    'total' => 0,
                    'rows' => []
                ];
        }
    }

    /**
     * 获取全部资源组为了分配
     * @param int    $sourcetype 资源类型
     * @param int    $offset     起始数
     * @param int    $limit      返回数量
     * @param string $search     过滤条件
     * @param string $uuid       当前分配的用户uuid
     * @return array
     */
    public function getResourceList(int $sourcetype, int $offset, int $limit, string $search = '', string $uuid = '')
    {
        // 获取所有的资源组
        $where = '';
        $join = '';

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['source']
            );
            $sqlNew = " and brg.create_user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        $sqlParams = [$sourcetype];
        $sql = "select count(DISTINCT brg.resource_group_uuid) total from bd_resource_group brg {$join}
                join mt_resource_resource_group mrrg on brg.resource_group_uuid = mrrg.resource_group_uuid
                where mrrg.resource_type = ? " . $where;
        if (!empty($search)) {
            $where .= "and brg.resource_group_name like '%{$search}%'";
        }
        $dataCount = $this->dbSelect($sql, $sqlParams);
        if (empty($dataCount[0]['total'])) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $sql = "SELECT DISTINCT brg.resource_group_uuid uuid,brg.resource_group_name name,
                GROUP_CONCAT(DISTINCT bu.user_name SEPARATOR ',') AS user_name,
                GROUP_CONCAT(DISTINCT murg.user_uuid SEPARATOR ',') AS user_uuid
                FROM bd_resource_group brg {$join}
                JOIN mt_resource_resource_group mrrg ON brg.resource_group_uuid = mrrg.resource_group_uuid
                LEFT JOIN mt_user_resource_group murg ON murg.resource_group_uuid = brg.resource_group_uuid
                left join bd_user bu on bu.user_uuid = murg.user_uuid
                WHERE mrrg.resource_type = ? {$where} 
                GROUP BY brg.resource_group_uuid
                 LIMIT {$offset},{$limit}";

        $list = $this->dbSelect($sql, $sqlParams);

        $rows = [];
        // 客户端和虚拟机等资源只能一对一
        $resourceSelf = xphp_get_config('resource', 'RESOURCE_SELF_TYPE') ?? [];
        foreach ($list as $item) {
            if (in_array($sourcetype, $resourceSelf) && !empty($item['user_uuid'])) {
                // 只能一对一，并且已经使用，那么不能再次分配
                $isactive = false;
            } elseif (!in_array($sourcetype, $resourceSelf) && !empty($item['user_uuid'])) {
                // 多对多,并且存在使用用户
                $userIdArrr = explode(',', $item['user_uuid']);
                if (in_array($uuid, $userIdArrr)) {
                    $isactive = false;
                } else {
                    $isactive = true;
                }
            } else {
                $isactive = true;
            }
            $rows[] = [
                'uuid' => $item['uuid'],
                'name' => $item['name'],
                'user' => empty($item['user_uuid']) ? '--' : $item['user_name'],
                'status' => '---',
                'class' => 'success',
                'is_active' => $isactive,
            ];
        }

        return [
            'rows' => $rows,
            'total' => $dataCount[0]['total']
        ];
    }

    /**
     * 获取节点全部资源
     * @param int    $offset     数字
     * @param int    $limit      数字
     * @param string $search     过滤条件
     * @param int    $sourceType 资源类型标识
     * @param string $uuid       当前分配的用户uuid
     * @return array 数组
     */
    public function getAllNode($offset, $limit, $search = '', $sourceType = 7, $uuid = '')
    {
        $sql = "select DISTINCT ip, bd.node_uuid,host_name,online_flag
                    from bd_node bd
                    left join bd_module_server bms on bms.node_uuid = bd.node_uuid";
        $sqlCount = "select count(bd.node_id) as total from bd_node bd";
        $sqlParams = $sqlCountParams = [];

        $where = '';

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['NODE'],
                'bd.node_uuid'
            );
            $sqlNew = " ({$resourceUuidSql}) ";
            $where .= $sqlNew;
        }

        if (!empty($search)) {
            $where = ' where ' . $where . (empty($where) ? ' ' : ' and ') .
                " (bd.node_nickname like '%{$search}%' or bd.ip like '%{$search}%')";
            $sql .= $where;
            $sqlCount .= $where;
        }
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }
        $sql .= " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $userArr = $this->getUsedUser($sourceType);

        $rows = [];
        $node = new Node();
        foreach ($data as $items) {
            $nodes = $node->getNodeStatus($items['node_uuid']);
            if ($nodes['online_flag']) {
                $nodeStatus = xphp_get_lang('WEB_PLATFORM_DES_NORMAL');
                $label = 'success';
            } else {
                $nodeStatus = xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
                $label = 'danger';
            }
            if (!empty($userArr[0][$items['node_uuid']]) && in_array($uuid, $userArr[0][$items['node_uuid']])) {
                $active = false;
            } else {
                $active = true;
            }
            $rows[] = [
                'uuid' => $items['node_uuid'],
                'name' => $items['host_name'] . '(' . $items['ip'] . ')',
                'user' => !empty($userArr[1][$items['node_uuid']]) ?
                    implode(',', array_unique($userArr[1][$items['node_uuid']])) : '--',
                'status' => $nodeStatus,
                'class' => $label,
                'is_active' => $active,
            ];
        }
        return array(
            'total' => $dataCount[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * n对n的资源查询哪些使用了
     * @param int $type 资源类型标识
     * @return array
     */
    private function getUsedUser(int $type)
    {
        $user = xphp_get_user_info();

        // 1.查询出所有资源组里面存在客户端资源uuid（排除编辑的时候的资源组）
        // 2.查询出用户已经分配了的客户端资源
        if ($this->isTenantAdmin) {
            // 如果是租户管理员 那么排除自身 因为自身的可以再进行一次分配
            $table1 = ',mt_user_resource_group murg ';
            $where1 = ' and murg.resource_group_uuid = mrrg.resource_group_uuid
                        and murg.user_uuid != ' . "'" . $user['userUuid'] . "'";
            $where2 = 'and user_uuid != ' . "'" . $user['userUuid'] . "'";
        } else {
            $table1 = $where1 = $where2 = '';
        }
        $sqlUuid =  "select mrrg.resource_uuid from mt_resource_resource_group mrrg{$table1}
                        where mrrg.resource_type = {$type} {$where1}
                    union all 
                    select resource_uuid from mt_user_resource where resource_type = {$type} {$where2}";

        $dataUuid = $this->dbSelect($sqlUuid);

        $dataArr = [];
        if (!empty($dataUuid)) {
            $dataArr = array_column($dataUuid, 'resource_uuid');
        }

        // 查询出具体的哪些用户使用了的
        $dataArr2 = $dataArr2u = [];
        if (!empty($dataArr)) {
            $agentUuid = "('" . implode("','", $dataArr) . "')";
            // 1.在资源关联表查询
            $sqls1 = "select mur.user_uuid,mur.resource_uuid,bu.user_name
                    from mt_user_resource mur,bd_user bu
                    where mur.resource_uuid in {$agentUuid} and mur.resource_type = {$type}
                      and mur.user_uuid = bu.user_uuid";
            $dataUserUuid = $this->dbSelect($sqls1);

            // 2.在资源组关联表查询
            $sqls2 = "select murg.user_uuid,mrrg.resource_uuid,bu.user_name from
                       mt_user_resource_group murg,mt_resource_resource_group mrrg,bd_user bu
                       where mrrg.resource_uuid in {$agentUuid} and mrrg.resource_type = {$type}
                         and mrrg.resource_group_uuid=murg.resource_group_uuid
                         and murg.user_uuid = bu.user_uuid";
            $dataUserUuid2 = $this->dbSelect($sqls2);

            $dataUserUuid = array_merge($dataUserUuid, $dataUserUuid2);

            if (!empty($dataUserUuid)) {
                // 处理合并组装
                foreach ($dataUserUuid as $item) {
                    $dataArr2[$item['resource_uuid']][] = $item['user_name'];
                    $dataArr2u[$item['resource_uuid']][] = $item['user_uuid'];
                }
            }
        }

        return [
            $dataArr2u,
            $dataArr2
        ];
    }

    /**
     * 获取存储资源全部资源
     * @param int    $offset     数字
     * @param int    $limit      数字
     * @param string $search     过滤条件
     * @param int    $sourceType 资源类型标识
     * @param string $uuid       当前分配的用户uuid
     * @return array 数组
     */
    public function getAllStorage($offset, $limit, $search = '', $sourceType = 8, string $uuid = '')
    {
        $sqlCount = "select count(bsr.storage_uuid) as total from bd_storage_resource bsr ";

        $sql = "select DISTINCT bsr.storage_nickname, bsr.storage_uuid, bsr.use_mode,bsr.lan_free_flag,
                bms.online_flag,bsr.status,bsr.mount_flag,bsr.storage_type,bsr.node_uuid
        from bd_storage_resource bsr
        left join bd_module_server bms on bms.node_uuid = bsr.node_uuid ";

        $where = ' where bsr.lan_free_flag = ? ';

        $sqlParams = [xphp_get_config('app', 'FLAG')['UNSET']];
        if (!empty($search)) {
            $where .= " and (bsr.storage_nickname like '%{$search}%')";
        }

        $user = xphp_get_user_info();
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             bsr.user_uuid = mut.user_uuid ';
            $where .= ' and mut.tenant_uuid is NULL ';
            $sqlCount .= $join;
            $sql .= $join;
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
                'bsr.storage_uuid'
            );
            $sqlNew = " and ({$resourceUuidSql}) ";
            $where .= $sqlNew;
        }

        $dataCount = $this->dbSelect($sqlCount . $where, $sqlParams);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $where . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);

        $userArr = $this->getUsedUser($sourceType);

        $rows = [];
        $logic = new Storage();
        $nodeHandler = new Node();
        $statusDesArr = [
            0 => 'WEB_NODE_NAS_OP_UNKNOWN',
            1 => 'WEB_AGENT_STATUS_ONLINE',
            2 => 'WEB_NODE_NAS_OP_CREATING',
            3 => 'WEB_AGENT_STATUS_OFFLINE',
            4 => 'WEB_STORAGE_STATUS_UNMOUNT',
        ];
        foreach ($data as $items) {
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($items['node_uuid']);
            $storageStatus = $logic->getStorageStatus(
                $nodeAllStatus,
                $items['status'],
                $items['mount_flag'],
                $items['storage_type']
            );
            if (!empty($userArr[0][$items['storage_uuid']]) && in_array($uuid, $userArr[0][$items['storage_uuid']])) {
                $active = false;
            } else {
                $active = true;
            }
            $statusDes = $statusDesArr[$storageStatus] ?? 'WEB_NODE_NAS_OP_WARNING';
            $rows[] = [
                'uuid' => $items['storage_uuid'],
                'name' => $items['storage_nickname'],
                'user' => !empty($userArr[1][$items['storage_uuid']]) ?
                    implode(',', array_unique($userArr[1][$items['storage_uuid']])) : '--',
                'status' => xphp_get_lang($statusDes),
                'class' => $storageStatus == 1 ? 'success' : 'danger',
                'is_active' => $active,
            ];
        }
        return array(
            'total' => $dataCount[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * 获取虚拟机全部资源
     * @param int    $offset 数字
     * @param int    $limit  数字
     * @param string $uuid   字符串
     * @param string $search 过滤条件
     * @param int    $type   类型 默认3虚拟机 58公有云平台 61 私有云
     * @param int    $vmtype 筛选的虚拟化类型
     * @return array 数组
     */
    public function getAllVm($offset, $limit, $uuid, $search = '', $type = 3, $vmtype = 0): array
    {

        $sqlCount = "select count(vt.tree_id) as total from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid ";

        $sql = "select vt.vcenter_uuid,vt.name, vt.uuid, vt.power_state from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid ";

        $where = ' where vt.display_mode = ? and vt.type = ? ';

        if (!empty($vmtype)) {
            $where .= ' and vv.hypervisor_type = ' . intval($vmtype);
        }
        $user = xphp_get_user_info();
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             vv.user_uuid = mut.user_uuid ';
            $sqlCount .= $join;
            $sql .= $join;
            $where .= ' and mut.tenant_uuid is NULL ';
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type($type, 'vt.uuid');
            $sqlNew = " and ({$resourceUuidSql}) ";
            $where .= $sqlNew;
        }

        $sqlParams = array(xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'],
            xphp_get_config('vm', 'VM_TREE_TYPE')['VM']);

        $vmConfig = xphp_get_config('vm', 'VMHYPERVISORGROUP');

        if ($type == 58) {
            // 公有云平台
            $where .= ' and vv.hypervisor_type in (' . implode(',', $vmConfig['publiccloud']) . ')';
        } elseif ($type == 61) {
            // 私有云
            $where .= ' and vv.hypervisor_type in (' . implode(',', $vmConfig['privatecloud']) . ')';
        } else {
            // 虚拟机
            $where .= ' and vv.hypervisor_type not in (' . implode(',', $vmConfig['publiccloud']) . ')';
            $where .= ' and vv.hypervisor_type not in (' . implode(',', $vmConfig['privatecloud']) . ')';
        }

        if (!empty($search)) {
            $where .= " and vt.name like '%{$search}%'";
        }
        $dataCount = $this->dbSelect($sqlCount . $where, $sqlParams);

        if (empty($dataCount[0]['total'])) {
            return  [
                'rows' => [],
                'total' => 0
            ];
        }

        $sql .= $where . " LIMIT $offset, $limit";
        $data = $this->dbSelect($sql, $sqlParams);

        $dataArrs = $this->getUsersName($type, $data, 'uuid', $uuid);
        $dataArr = $dataArrs[0];
        $dataArr2 = $dataArrs[1];

        $rows = [];
        $statusDesArr = [
            0 => xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
            1 => xphp_get_lang('WEB_VM_POWER_OFF'),
            2 => xphp_get_lang('WEB_VM_POWER_ON'),
            3 => xphp_get_lang('WEB_VM_SUSPEND'),
        ];
        $statusDes = xphp_get_lang('WEB_VM_PAUSE');
        foreach ($data as $d) {
            $status = in_array($d['power_state'], [0, 1, 2, 3]) ? $statusDesArr[$d['power_state']] : $statusDes;
            if ($d['power_state'] == 2) {
                $label = 'success';
            } else {
                $label = 'danger';
            }

            $rows[] = [
                'uuid' => $d['uuid'],
                'name' => $d['name'],
                'user' => !empty($dataArr2[$d['uuid']]) ? $dataArr2[$d['uuid']] : '--',
                'vcenter_uuid' => $d['vcenter_uuid'],
                'status' => $status,
                'class' => $label,
                'is_active' => !in_array($d['uuid'], $dataArr),
            ];
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $rows
        ];
    }

    /**
     * 获取客户端全部资源
     * @param int    $offset   数字
     * @param int    $limit    数字
     * @param string $uuid     字符串
     * @param int    $type     默认[1客户端
     *                         2传输代理]
     * @param string $search   字符串
     * @param string $userUuid 当前分配的用户uuid
     * @return array 数组
     */
    public function getAllclient($offset, $limit, $uuid, $type = 1, $search = '', $userUuid = ''): array
    {
        /**
         * agent_type说明
         * 1. 客户端
         * 2. livecd、winpe
         * 3. nas
         * 4. 传输代理
         */
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $user = xphp_get_user_info();
        $where = " WHERE ba.agent_type != {$allAgentType['NAS']} ";

        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             ba.user_uuid = mut.user_uuid ';
            $where .= ' and mut.tenant_uuid is NULL ';
        } else {
            $join = '';
        }

        $sql = "SELECT ba.agent_uuid,ba.ip,ba.agent_name,ba.hostname,ba.online_flag
	                FROM bd_agent ba {$join} ";
        $sqlCount = "select count(ba.agent_uuid) as total from bd_agent ba {$join} ";

        if ($type == 1) { // 查询客户端
            $where .= " AND ba.agent_type != {$allAgentType['APPLIANCE']} ";
        } else {  // 传输代理
            $where .= " AND ba.agent_type = {$allAgentType['APPLIANCE']} ";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                $type == 1 ? 10 : 2,
                'ba.agent_uuid'
            );
            $sqlNew = " and ({$resourceUuidSql}) ";
            $where .= $sqlNew;
        }

        if (!empty($search)) {
            $where .= " and ( ba.agent_name like '%{$search}%'
             or ba.hostname like '%{$search}%' or ba.ip like '%{$search}%' )";
        }
        $dataCount = $this->dbSelect($sqlCount . $where);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $where . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);
        if ($type != 1) {
            // 传输代理 n v n
            $dataArrs = $this->getUsedUser(2);
        } else {
            // 客户端 1v1
            $dataArrs = $this->getUsersName(10, $data, 'agent_uuid', $uuid);
        }
        $dataArr = $dataArrs[0];
        $dataArr2 = $dataArrs[1];

        $rows = [];

        $statusDesOnline = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
        $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');

        foreach ($data as $d) {
            // 不是1对1的，才判断是否被使用
            if ($type != 1) {
                // 传输代理
                if (!empty($dataArr[$d['agent_uuid']]) && in_array($userUuid, $dataArr[$d['agent_uuid']])) {
                    $active = false;
                } else {
                    $active = true;
                }
                $user = !empty($dataArr2[$d['agent_uuid']]) ?
                    implode(',', array_unique($dataArr2[$d['agent_uuid']])) : '--';
            } else {
                // 客户端
                $active = !in_array($d['agent_uuid'], $dataArr);
                $user = !empty($dataArr2[$d['agent_uuid']]) ? $dataArr2[$d['agent_uuid']] : '--';
            }

            $rows[] = [
                'uuid' => $d['agent_uuid'],
                'name' => $d['hostname'] . '(' . $d['ip'] . ')',
                'vcenter_uuid' => '',
                'user' => $user,
                'status' => $d['online_flag'] == 1 ? $statusDesOnline : $statusDes,
                'class' => $d['online_flag'] == 1 ? 'success' : 'danger',
                'is_active' => $active,
            ];
        }

        return array(
            'total' => $dataCount[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * 获取组织管理全部资源
     * @param int    $offset     数字
     * @param string $uuid       字符串
     * @param int    $limit      数字
     * @param string $search     关键词
     * @param int    $sourceType 资源类型标识
     * @return array 数组
     */
    public function getAllM365Org($offset, $uuid, $limit, $search = '', $sourceType = 56): array
    {

        $user = xphp_get_user_info();
        $where = [];
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             mo.user_uuid = mut.user_uuid ';
            $where[] = ' mut.tenant_uuid is NULL';
        } else {
            $join = '';
        }

        $sqlCount = "select count(mo.organization_uuid) as total from m365_organization mo {$join} ";

        $sql = "select mo.organization_name,mo.organization_uuid,mo.online_flag
        from m365_organization mo {$join} ";

        if (!empty($search)) {
            $where[] = " mo.organization_name like '%{$search}%'";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['M365_EXCHANGE'],
                'mo.organization_uuid'
            );
            $sqlNew = " ({$resourceUuidSql}) ";
            $where[] = $sqlNew;
        }

        $wheres = '';
        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        }
        $dataCount = $this->dbSelect($sqlCount . $wheres);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $wheres . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);

        $dataArrs = $this->getUsersName($sourceType, $data, 'organization_uuid', $uuid);
        $dataArr = $dataArrs[0];
        $dataArr2 = $dataArrs[1];

        $lists = [];
        $arr = [
            xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
            xphp_get_lang('WEB_AGENT_STATUS_ONLINE'),
            xphp_get_lang('WEB_AGENT_STATUS_OFFLINE'),
        ];
        foreach ($data as $item) {
            $lists[] = [
                'uuid' => $item['organization_uuid'],
                'name' => $item['organization_name'],
                'user' => !empty($dataArr2[$item['organization_uuid']]) ? $dataArr2[$item['organization_uuid']] : '--',
                'status' =>  $arr[$item['online_flag']] ?? '---',
                'class' => $item['online_flag'] == 1 ? 'success' : 'danger',
                'is_active' => !in_array($item['organization_uuid'], $dataArr),
            ];
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $lists
        ];
    }

    /**
     * 获取nas管理全部资源
     * @param int    $offset     数字
     * @param string $uuid       字符串
     * @param int    $limit      数字
     * @param string $search     关键词
     * @param int    $sourceType 资源类型标识
     * @return array 数组
     */
    public function getAllNas($offset, $uuid, $limit, $search = '', $sourceType = 57): array
    {
        $user = xphp_get_user_info();
        $where = [];
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             nsr.user_uuid = mut.user_uuid ';
            $where[] = ' mut.tenant_uuid is NULL';
        } else {
            $join = '';
        }
        $sqlCount = "select count(nsr.nas_uuid) as total from nas_storage_resource nsr {$join} ";

        $sql = "select nsr.nas_nickname,nsr.share_path,nsr.nas_uuid,nsr.nas_status
        from nas_storage_resource nsr {$join} ";

        if (!empty($search)) {
            $where[] = " (nsr.nas_nickname like '%{$search}%' or nsr.share_path like '%{$search}%')";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['NAS'],
                'nsr.nas_uuid'
            );
            $sqlNew = " ({$resourceUuidSql}) ";
            $where[] = $sqlNew;
        }

        $wheres = '';
        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        }

        $dataCount = $this->dbSelect($sqlCount . $wheres);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $wheres . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);

        $dataArrs = $this->getUsersName($sourceType, $data, 'nas_uuid', $uuid);
        $dataArr = $dataArrs[0];
        $dataArr2 = $dataArrs[1];

        $lists = [];
        if (!empty($data)) {
            $arr = [
                xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
                xphp_get_lang('WEB_AGENT_STATUS_ONLINE'),
                xphp_get_lang('WEB_AGENT_STATUS_OFFLINE'),
            ];
            foreach ($data as $item) {
                $lists[] = [
                    'uuid' => $item['nas_uuid'],
                    'name' => $item['nas_nickname'] . '(' . $item['share_path'] . ')',
                    'user' => !empty($dataArr2[$item['nas_uuid']]) ? $dataArr2[$item['nas_uuid']] : '--',
                    'status' =>  $arr[$item['nas_status']] ?? '---',
                    'class' => $item['nas_status'] == 1 ? 'success' : 'danger',
                    'is_active' => !in_array($item['nas_uuid'], $dataArr),
                ];
            }
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $lists
        ];
    }

    /**
     * 获取对象存储全部资源
     * @param int    $offset     数字
     * @param string $uuid       字符串
     * @param int    $limit      数字
     * @param string $search     关键词
     *  @param int    $sourceType 资源类型标识
     * @return array 数组
     */
    public function getAllObs($offset, $uuid, $limit, $search = '', $sourceType = 59): array
    {
        $user = xphp_get_user_info();
        $where = [];
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             ors.user_uuid = mut.user_uuid ';
            $where[] = ' mut.tenant_uuid is NULL';
        } else {
            $join = '';
        }
        $sqlCount = "select count(ors.obs_uuid) as total from obs_resource ors {$join} ";

        $sql = "select ors.obs_nickname,ors.obs_uuid,ors.status,ors.access_key_id,ors.endpoint_override
                from obs_resource ors {$join} ";

        if (!empty($search)) {
            $where[] = " (ors.obs_nickname like '%{$search}%' or ors.access_key_id like '%{$search}%'
             or ors.endpoint_override like '%{$search}%')";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'],
                'ors.obs_uuid'
            );
            $sqlNew = " ({$resourceUuidSql}) ";
            $where[] = $sqlNew;
        }

        $wheres = '';
        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        }

        $dataCount = $this->dbSelect($sqlCount . $wheres);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $wheres . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);

        $dataArrs = $this->getUsersName($sourceType, $data, 'obs_uuid', $uuid);
        $dataArr = $dataArrs[0];
        $dataArr2 = $dataArrs[1];

        $lists = [];
        if (!empty($data)) {
            $arr = [
                xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL'),
                xphp_get_lang('WEB_PLATFORM_DES_NORMAL'),
            ];
            foreach ($data as $item) {
                $lists[] = [
                    'uuid' => $item['obs_uuid'],
                    'name' => $item['obs_nickname'] . '(' . $item['access_key_id'] .
                        '@' . $item['endpoint_override'] . ')',
                    'user' => !empty($dataArr2[$item['obs_uuid']]) ? $dataArr2[$item['obs_uuid']] : '--',
                    'status' =>  $arr[$item['status']] ?? '---',
                    'class' => $item['status'] == 1 ? 'success' : 'danger',
                    'is_active' => !in_array($item['obs_uuid'], $dataArr),
                ];
            }
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $lists
        ];
    }

    /**
     * 获取Hadoop全部资源
     * @param int    $offset     数字
     * @param string $uuid       字符串
     * @param int    $limit      数字
     * @param string $search     关键词
     *  @param int    $sourceType 资源类型标识
     * @return array 数组
     */
    public function getAllHadoop($offset, $uuid, $limit, $search = '', $sourceType = 60): array
    {
        $user = xphp_get_user_info();
        $where = [];
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             hc.user_uuid = mut.user_uuid ';
            $where[] = ' mut.tenant_uuid is NULL';
        } else {
            $join = '';
        }
        $sqlCount = "select count(hc.hadoop_cluster_uuid) as total from hadoop_cluster hc {$join} ";

        $sql = "select hc.hadoop_cluster_name,hc.hadoop_cluster_uuid,hc.status from hadoop_cluster hc {$join} ";

        if (!empty($search)) {
            $where[] = " (hc.hadoop_cluster_name like '%{$search}%')";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP'],
                'hc.hadoop_cluster_uuid'
            );
            $sqlNew = " ({$resourceUuidSql}) ";
            $where[] = $sqlNew;
        }

        $wheres = '';
        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        }

        $dataCount = $this->dbSelect($sqlCount . $wheres);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $wheres . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);

        $dataArrs = $this->getUsersName($sourceType, $data, 'hadoop_cluster_uuid', $uuid);
        $dataArr = $dataArrs[0];
        $dataArr2 = $dataArrs[1];

        $lists = [];
        if (!empty($data)) {
            $arr = [
                xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
                xphp_get_lang('WEB_AGENT_STATUS_ONLINE'),
                xphp_get_lang('WEB_AGENT_STATUS_OFFLINE'),
                xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL'),
            ];
            foreach ($data as $item) {
                $lists[] = [
                    'uuid' => $item['hadoop_cluster_uuid'],
                    'name' => $item['hadoop_cluster_name'],
                    'user' => !empty($dataArr2[$item['hadoop_cluster_uuid']]) ?
                        $dataArr2[$item['hadoop_cluster_uuid']] : '--',
                    'status' =>  $arr[$item['status']] ?? '---',
                    'class' => $item['status'] == 1 ? 'success' : 'danger',
                    'is_active' => !in_array($item['hadoop_cluster_uuid'], $dataArr),
                ];
            }
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $lists
        ];
    }

    /**
     * 获取kubernetes集群全部资源
     * @param int    $offset     数字
     * @param int    $limit      数字
     * @param string $search     关键词
     *  @param int    $sourceType 资源类型标识
     * @param string $userUuid   所属用户
     * @return array 数组
     */
    public function getAllK8s($offset, $limit, $search = '', $sourceType = 62, $userUuid = ''): array
    {
        $user = xphp_get_user_info();
        $where = [];
        if (in_array($user['userLevel'], [1, 2, 3])) {
            // 超级管理员、系统管理员和安全管理不能看租户创建的
            $join = ' left join mt_user_tenant mut on
             kc.user_uuid = mut.user_uuid ';
            $where[] = ' mut.tenant_uuid is NULL';
        } else {
            $join = '';
        }
        $sqlCount = "select count(kc.cluster_uuid) as total from kube_cluster kc {$join} ";

        $sql = "select kc.cluster_name,kc.cluster_uuid,kc.online_flag from kube_cluster kc {$join} ";

        if (!empty($search)) {
            $where[] = " (kc.cluster_name like '%{$search}%')";
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['K8S'],
                'kc.cluster_uuid'
            );
            $sqlNew = " ({$resourceUuidSql}) ";
            $where[] = $sqlNew;
        }

        $wheres = '';
        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        }

        $dataCount = $this->dbSelect($sqlCount . $wheres);
        if (empty($dataCount[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $wheres . " LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);

        $userArr = $this->getUsedUser($sourceType);

        $lists = [];
        if (!empty($data)) {
            $arr = [
                xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
                xphp_get_lang('WEB_AGENT_STATUS_ONLINE'),
                xphp_get_lang('WEB_AGENT_STATUS_OFFLINE'),
                xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL'),
            ];
            foreach ($data as $item) {
                if (
                    !empty($userArr[0][$item['cluster_uuid']]) &&
                    in_array($userUuid, $userArr[0][$item['cluster_uuid']])
                ) {
                    $active = false;
                } else {
                    $active = true;
                }
                $lists[] = [
                    'uuid' => $item['cluster_uuid'],
                    'name' => $item['cluster_name'],
                    'user' => !empty($userArr[1][$item['cluster_uuid']]) ?
                        implode(',', array_unique($userArr[1][$item['cluster_uuid']])) : '--',
                    'status' =>  $arr[$item['online_flag']] ?? '---',
                    'class' => $item['online_flag'] == 1 ? 'success' : 'danger',
                    'is_active' => $active,
                ];
            }
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $lists
        ];
    }

    /**
     * 根据资源类型获取当前的资源的所有者
     * @param int    $sourceType 资源类型
     * @param array  $data       资源列表
     * @param string $filed      资源的主键
     * @param string $uuid       修改资源组时的uuid
     * @return array
     */
    private function getUsersName(int $sourceType, array $data, $filed = 'cluster_uuid', $uuid = ''): array
    {
        $userUuid = xphp_get_user_info()['userUuid'];
        // 1.查询出所有资源组里面存在资源uuid（排除编辑的时候的资源组）
        // 2.查询出用户已经分配了的资源
        if ($this->isTenantAdmin) {
            // 如果是租户管理员 那么排除自身 因为自身的可以再进行一次分配
            $table1 = ',mt_user_resource_group murg ';
            $where1 = ' and murg.resource_group_uuid = mrrg.resource_group_uuid
                        and murg.user_uuid != ' . "'" . $userUuid . "'";
            $where2 = 'and user_uuid != ' . "'" . $userUuid . "'";
        } else {
            $table1 = $where1 = $where2 = '';
        }
        $sqlUuid =  "select mrrg.resource_uuid from mt_resource_resource_group mrrg{$table1}
                        where mrrg.resource_group_uuid != '$uuid' and mrrg.resource_type = {$sourceType} {$where1}
                    union all 
                    select resource_uuid from mt_user_resource where resource_type = {$sourceType} {$where2}";

        $dataUuid = $this->dbSelect($sqlUuid);

        if (!empty($dataUuid)) {
            foreach ($data as $index => $d) {
                $data[$index]['is_active'] = true;
                foreach ($dataUuid as $t) {
                    if ($d[$filed] == $t['resource_uuid']) {
                        $data[$index]['is_active'] = false;
                    }
                }
            }
        }

        $dataArr = [];
        if (!empty($dataUuid)) {
            $dataArr = array_column($dataUuid, 'resource_uuid');
        }

        // 查询出具体的哪些用户使用了的
        $dataArr2 = [];
        if (!empty($dataArr)) {
            $agentUuid = "('" . implode("','", $dataArr) . "')";
            // 1.在资源关联表查询
            $sqls1 = "select mur.user_uuid,mur.resource_uuid,bu.user_name
                    from mt_user_resource mur,bd_user bu
                    where mur.resource_uuid in {$agentUuid} and mur.resource_type = {$sourceType}
                      and mur.user_uuid = bu.user_uuid";
            $dataUserUuid = $this->dbSelect($sqls1);

            // 2.在资源组关联表查询
            $sqls2 = "select murg.user_uuid,mrrg.resource_uuid,bu.user_name from
                       mt_user_resource_group murg,mt_resource_resource_group mrrg,bd_user bu
                       where mrrg.resource_uuid in {$agentUuid} and mrrg.resource_type = {$sourceType}
                         and mrrg.resource_group_uuid=murg.resource_group_uuid
                         and murg.user_uuid = bu.user_uuid";
            $dataUserUuid2 = $this->dbSelect($sqls2);
            $dataUserUuid = array_merge($dataUserUuid, $dataUserUuid2);

            if (!empty($dataUserUuid)) {
                // 处理合并组装
                foreach ($dataUserUuid as $item) {
                    $dataArr2[$item['resource_uuid']] = $item['user_name'];
                }
            }
        }
        return [$dataArr, $dataArr2];
    }
}
