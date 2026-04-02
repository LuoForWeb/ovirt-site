<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\user\v0\logic\User;

/**
 * note          资源,资源组管理类 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/10 16:29
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends Base
{
    /**
     * 公共方法
     * 得到用户所有资源(只有资源uuid和类型)
     * 用户-资源
     * 用户-资源组-资源
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $useruuid     用户uuid
     * @param int    $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @return array  资源UUID和资源类型列表
     * @author xiezhuowei@vinchin.com
     */
    public function pGetUserAllResource($useruuid, $resourceType = '')
    {
        $this->paramsCheck($useruuid);
        //如果按类型查找
        $sqlMore = '';
        $sqlParams = array($useruuid);
        if (!empty($resourceType)) {
            $sqlMore = ' and resource_type = ? ';
            $sqlParams = array($useruuid, $resourceType);
        }
        //用户-资源
        $sql = "select distinct mur.resource_uuid, mur.vm_uuid, mur.vcenter_uuid, mur.resource_type from 
                bd_user bu, mt_user_resource mur 
                where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
        $sql .= $sqlMore;
        $data1 = $this->dbSelect($sql, $sqlParams);

        //用户-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user bu, mt_user_resource_group murg, mt_resource_resource_group mrrg 
                where bu.user_uuid = murg.user_uuid and murg.resource_group_uuid = mrrg.resource_group_uuid 
                and bu.user_uuid = ? ";
        $sql .= $sqlMore;
        $data2 = $this->dbSelect($sql, $sqlParams);

        //用户-用户组-资源
        $sql = "select distinct mugr.resource_uuid, mugr.vm_uuid, mugr.vcenter_uuid, mugr.resource_type from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource mugr
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid
                  and bug.user_group_uuid = mugr.user_group_uuid and bu.user_uuid = ? and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $data3 = $this->dbSelect($sql, $sqlParams);

        //用户-用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user bu, bd_user_group bug, mt_user_user_group muug,
                mt_user_group_resource_group mugrg,mt_resource_resource_group mrrg 
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugrg.user_group_uuid
                  and bug.user_group_uuid = mugrg.user_group_uuid and
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bu.user_uuid = ? and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $data4 = $this->dbSelect($sql, $sqlParams);

        //合并四个资源,并去重
        $data = array_merge($data1, $data2, $data3, $data4);

        // 根据 $useruuid 判断当前的用户是不是租户
        $tenant = (new User())->getUserPermission($useruuid);
        // if (!empty($tenant)) {
        // 是租户的话，需要把自己创建的这些资源都给包含进来
        // 改为所有的都需要
        $dataNew = [];
        if (!empty($resourceType)) {
            // 查询某一项的
            $datas = $this->pGetUserCreateResource($useruuid, $resourceType, 'array', true);
            foreach ($datas as $items) {
                $dataNew[] = [
                    'resource_uuid' => $items['uuid'],
                    'vm_uuid' => in_array($resourceType, [3, 58, 61]) ? $items['uuid'] : '',
                    'vcenter_uuid' => '',
                    'resource_type' => $resourceType,
                ];
            }
        } else {
            $resourceTypes = xphp_get_config('resource', 'RESOURCE_DIS_TYPE');
            foreach ($resourceTypes as $key => $item) {
                $datas = $this->pGetUserCreateResource($useruuid, $key, 'array', true);
                foreach ($datas as $items) {
                    $dataNew[] = [
                        'resource_uuid' => $items['uuid'],
                        'vm_uuid' => in_array($key, [3, 58, 61]) ? $items['uuid'] : '',
                        'vcenter_uuid' => '',
                        'resource_type' => $key,
                    ];
                }
            }
        }
        $data = array_merge($data, $dataNew);
        // }
        return v1_unique_multidim_array($data, 'resource_uuid');
    }

    /**
     * 公共方法
     * 得到用户所有资源uuid sql
     * 用户-资源
     * 用户-资源组-资源
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $useruuid     用户uuid
     * @param int    $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @param string $userType     请求useruuid类型，是某个uuid还是sql语句的结果，就是需要in
     * @param string $field        如果带入了主表的主键，那么就会直接返回一个完整的where条件，不需要主表再拼一次
     * @return string  资源UUID的sql集合
     */
    public function pGetUserAllResourceSql($useruuid, $resourceType = '', $userType = '', $field = '')
    {

        //如果按类型查找
        if ($userType == 'sql') {
            $sqlMore = " and bu.user_uuid in ({$useruuid})";
        } else {
            $sqlMore = " and bu.user_uuid = '{$useruuid}'";
        }
        if (!empty($resourceType)) {
            $sqlMore .= ' and resource_type = ' . $resourceType;
        }
        $sqls = [];
        //用户-资源
        $sql = "select distinct mur.resource_uuid uuid from 
                bd_user bu, mt_user_resource mur 
                where bu.user_uuid = mur.user_uuid";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        //用户-资源组-资源
        $sql = "select distinct mrrg.resource_uuid uuid from
                bd_user bu, mt_user_resource_group murg, mt_resource_resource_group mrrg 
                where bu.user_uuid = murg.user_uuid and murg.resource_group_uuid = mrrg.resource_group_uuid";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        //用户-用户组-资源
        $sql = "select distinct mugr.resource_uuid uuid from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource mugr
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid
                  and bug.user_group_uuid = mugr.user_group_uuid and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        //用户-用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid uuid from
                bd_user bu, bd_user_group bug, mt_user_user_group muug,
                mt_user_group_resource_group mugrg,mt_resource_resource_group mrrg 
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugrg.user_group_uuid
                  and bug.user_group_uuid = mugrg.user_group_uuid and
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        // 需要把自己创建的这些资源都给包含进来
        if (!empty($resourceType)) {
            // 查询某一项的
            $sqls[] = $this->pGetUserCreateResource($useruuid, $resourceType, 'sql', true, $userType);
        } else {
            $resourceTypes = xphp_get_config('resource', 'RESOURCE_DIS_TYPE');
            foreach ($resourceTypes as $key => $item) {
                $sqls[] = $this->pGetUserCreateResource($useruuid, $key, 'sql', true, $userType);
            }
        }
        $sqls = array_filter($sqls);
        $return = '(' . implode(' union all ', $sqls) . ')';
        if (!empty($field)) {
            $return = " EXISTS (select uuid from ({$return}) v1_auth_all where v1_auth_all.uuid = {$field})";
        }
        return $return;
    }

    /**
     * 公共方法
     * 获取用户自身创建的资源
     * @param string $userUuid     用户uuid
     * @param int    $resourceType 资源类型
     * @param string $back         返回类型 默认sql
     * @param bool   $allocation   默认不去除分配的资源
     * @param string $userType     请求useruuid类型，是某个uuid还是sql语句的结果，就是需要in
     * @return array|string
     */
    public function pGetUserCreateResource(
        string $userUuid = '',
        $resourceType = 0,
        $back = 'sql',
        $allocation = false,
        $userType = ''
    ) {

        // 定义的分配的资源的类型
        $resourceTypes = xphp_get_config('resource', 'RESOURCE_DIS_TYPE');
        if (empty($resourceTypes)) {
            return $back == 'sql' ? '' : [];
        }

        if (empty($userUuid)) {
            $userUuid = xphp_get_user_info()['userUuid'];
        }
        $not = ' NOT IN (
                SELECT resource_uuid
                FROM mt_user_resource
                where resource_type != 0
                UNION
                SELECT resource_uuid
                FROM mt_resource_resource_group
                where resource_type != 0
              )';
        $sqlArr = [];
        $userSql = $userType == 'sql' ? "user_uuid in ($userUuid)" : "user_uuid = '{$userUuid}'";
        foreach ($resourceTypes as $key => $item) {
            switch ($key) {
                case 3: // 虚拟机
                    $vmConfig = xphp_get_config('vm', 'VMHYPERVISORGROUP');
                    $sqls = "select vt.uuid from vm_tree vt, vm_vcenter vv
                                                where vt.vcenter_uuid = vv.vcenter_uuid and vv.{$userSql}
                                                and vv.hypervisor_type not in
                                                    (" . implode(',', $vmConfig['publiccloud']) . ')
                                                    and vv.hypervisor_type not in
                                                     (' . implode(',', $vmConfig['privatecloud']) . ')';
                    if ($allocation) {
                        $sqls .= ' and vt.uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 8: // 存储资源
                    $flag = xphp_get_config('app', 'FLAG')['UNSET'];
                    $sqls = "select storage_uuid as uuid from bd_storage_resource 
                                        where lan_free_flag = {$flag} and use_mode not in (2,3)
                                          and {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and storage_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 10: // 客户端
                    $agentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
                    $sqls = "select ba.agent_uuid as uuid from bd_agent ba
                                        where ba.agent_type not in ({$agentType['NAS']}, {$agentType['APPLIANCE']})
                                          and ba.{$userSql}";
                    if ($allocation) {
                        $sqls .= ' and ba.agent_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 2: // 传输代理
                    $agentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
                    $sqls = "select ba.agent_uuid as uuid from bd_agent ba
                                        where ba.agent_type = {$agentType['APPLIANCE']}
                                          and ba.{$userSql}";
                    if ($allocation) {
                        $sqls .= ' and ba.agent_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 56: // 组织管理
                    $sqls = "select organization_uuid as uuid from m365_organization
                                            where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and organization_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 57: // nas设备
                    $sqls = "select nas_uuid as uuid from nas_storage_resource where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and nas_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 58: // 公有云平台
                    $vmConfig = xphp_get_config('vm', 'VMHYPERVISORGROUP');
                    $sqls = "select vt.uuid from vm_tree vt, vm_vcenter vv
                                                where vt.vcenter_uuid = vv.vcenter_uuid and vv.{$userSql}
                                                and vv.hypervisor_type in
                                                    (" . implode(',', $vmConfig['publiccloud']) . ')';
                    if ($allocation) {
                        $sqls .= ' and vt.uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 59: // 对象存储
                    $sqls = "select obs_uuid as uuid from obs_resource where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and obs_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 60: // Hadoop集群
                    $sqls = "select hadoop_cluster_uuid as uuid
                                    from hadoop_cluster where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and hadoop_cluster_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 61: // 私有云
                    $vmConfig = xphp_get_config('vm', 'VMHYPERVISORGROUP');
                    $sqls = "select vt.uuid from vm_tree vt, vm_vcenter vv
                                                where vt.vcenter_uuid = vv.vcenter_uuid and vv.{$userSql}
                                                and vv.hypervisor_type in
                                                    (" . implode(',', $vmConfig['privatecloud']) . ')';
                    if ($allocation) {
                        $sqls .= ' and vt.uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 62: // k8s集群
                    $sqls = "select cluster_uuid as uuid
                                    from kube_cluster where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and cluster_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
            }
        }

        $sql = '';
        if (empty($resourceType)) {
            // 查询所有的
            $sql = implode(' union all ', array_values($sqlArr));
        } elseif (!empty($sqlArr[$resourceType])) {
            $sql = $sqlArr[$resourceType];
        }
        if ($back != 'sql') {
            return empty($sql) ? [] : $this->dbSelect($sql);
        }
        return $sql;
    }

    /**
     * 公共方法
     * 得到租户所有资源(只有资源uuid和类型)
     * @param string $tenantuuid   租户uuid
     * @param int    $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @return array  资源UUID和资源类型列表
     * @author xiezhuowei@vinchin.com
     */
    public function pGetTenantAllResource($tenantuuid, $resourceType = '')
    {
        $this->paramsCheck($tenantuuid);
        //获取租户下所有用户
        $tenantHandler = new \app\v1\tenant\v0\logic\Index();
        $users = $tenantHandler->pGetTenantAllUser($tenantuuid);

        //获取所有用户的资源列表
        $resourceList = array();
        foreach ($users as $user) {
            $resourceUUIDList = $this->pGetUserAllResource($user['user_uuid'], $resourceType);
            $resourceList = array_merge($resourceList, $resourceUUIDList);
        }

        return v1_unique_multidim_array($resourceList, 'resource_uuid');
    }

    /**
     * 公共方法
     * 得到用户文件代理主机
     * @param string $useruuid 用户uuid
     * @param string $orderby  按哪个字段排序,默认使用id字段
     * @param string $sort     排序方式,默认desc,可传入asc
     * @param number $limit    从哪个开始,默认0
     * @param number $count    本次取多少个,默认10,
     *                         特别:"all"取所有
     * @param array  $search   搜索参数
     * @return array  资源列表,对应资源表
     * @author xiezhuowei@vinchin.com
     */
    public function pGetUserResourceFileHost(
        string $useruuid,
        $orderby = 'ba.id',
        $sort = 'desc',
        $limit = 0,
        $count = 10,
        $search = []
    ) {
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource(
            $useruuid,
            xphp_get_config('resource', 'RESOURCE_TYPE')['FILE_HOST']
        );

        //检查当前用户是否属于Master组
        $masterFlag = (new User())->pCheckUserIsMaster($useruuid);

        //获取资源内容
        return $this->getResourceFileHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
    }

    /**
     * 通过资源uuid列表获取文件代理主机
     * @param unknown $resourceUUIDList 资源列表
     * @param string  $orderby          按哪个字段排序,默认使用id字段
     * @param string  $sort             排序方式,默认desc,可传入asc
     * @param number  $limit            从哪个开始,默认0
     * @param number  $count            本次取多少个,默认10,
     * @param array   $search           搜索参数
     * @param array   $flag             bool
     * @return array  资源列表,对应资源表
     * @author xiezhuowei@vinchin.com
     */
    private function getResourceFileHost(
        $resourceUUIDList,
        $orderby,
        $sort,
        $limit,
        $count,
        $search = array(),
        $flag = false
    ) {
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(ba.id) as total from bd_agent ba 
                            left join bd_user bu on ba.user_uuid = bu.user_uuid where ba.agent_type = 0 ";
        $sql = "select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.os_version,
                ba.process_type, ba.os_version, ba.process_type,
                ba.register_time, ba.register_flag, ba.online_flag, ba.user_uuid, ba.authorization_module
            from bd_agent ba left join bd_user bu on ba.user_uuid = bu.user_uuid
            where ba.agent_type = 0 ";

        $sqlParams = array();
        $sqlCountParams = array();

        //不是Master用户
        if (!$flag) {
            if (!empty($listStr)) {
                $sql .= " and ba.agent_uuid in ($listStr) ";
                $sqlCount .= " and ba.agent_uuid in ($listStr) ";
            } else {
                $sql .= " and ba.agent_uuid in ('') ";
                $sqlCount .= " and ba.agent_uuid in ('') ";
            }
        }

        $accurateFlag = $search['accurate_flag'];
        //高级搜索
        if ($accurateFlag) {
            $hostname = $search['host_name'];
            $hostname = v1_escape_wildcard($hostname);
            $ip = $search['agent_ip'];
            $onlineFlag = $search['online_flag'];
            $registerFlag = $search['register_flag'];
            $username = $search['user_name'];
            $sql .= ' and (ba.online_flag = ' . $onlineFlag . ' or ' . $onlineFlag . " = '') and
                 (ba.register_flag = " . $registerFlag . ' or ' . $registerFlag . " = '') and
                 hostname like '%" . $hostname . "%' ";
            $sqlCount .= ' and (ba.online_flag = ' . $onlineFlag . ' or ' . $onlineFlag . " = '') and
                 (ba.register_flag = " . $registerFlag . ' or ' . $registerFlag . " = '') and
                 hostname like '%" . $hostname . "%'";

            if (!empty($ip)) {
                $sql .= ' and ba.ip = ? ';
                $sqlCount .= 'and ba.ip = ? ';
                $sqlParams = array_merge($sqlParams, array($ip));
                $sqlCountParams = array_merge($sqlCountParams, array($ip));
            }

            if (!empty($username)) {
                $sql .= ' and bu.user_name = ? ';
                $sqlCount .= ' and bu.user_name = ?';
                $sqlParams = array_merge($sqlParams, array($username));
                $sqlCountParams = array_merge($sqlCountParams, array($username));
            }
        } else {
            $sql .= ' and (ba.online_flag = ?
                or ba.register_flag = ?) ';
            $sqlCount .= ' and (ba.online_flag = ? or ba.register_flag = ?) ';
            //根据主机名搜索
            $hostName = $search['name'];
            $hostName = v1_escape_wildcard($hostName);
            if (!empty($hostName)) {
                $sql .= " and ba.hostname like '%" . $hostName . "%' ";
                $sqlCount .= " and ba.hostname like '%" . $hostName . "%' ";
            }
            $setFlag = xphp_get_config('app', 'FLAG')['SET'];
            $sqlParams = array_merge($sqlParams, array($setFlag, $setFlag));
            $sqlCountParams = array_merge($sqlCountParams, array($setFlag, $setFlag));
        }

        $sql .= " order by $orderby $sort ";
        if ($count != 'all') {
            //如果不是查找所有,带上limit
            $sql .= ' limit ?, ? ';
            $sqlParams = array_merge($sqlParams, array($limit, $count));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        //如果资源为空
        if (!empty($data)) {
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        return $info;
    }

    /**
     * 组合资源列表成为可查询字符串形式,方便查询如:'uuid1','uuid2','uuid3'
     * @param array  $resourceUUIDList 数据
     * @param string $keyword          关键词
     * @return string
     */
    public function groupResourceUUIDToString($resourceUUIDList, $keyword = 'resource_uuid')
    {
        $listStr = '';
        foreach ($resourceUUIDList as $resource) {
            if (!empty($listStr)) {
                $listStr .= ", '" . $resource[$keyword] . "'";
            } else {
                $listStr .= "'" . $resource[$keyword] . "'";
            }
        }
        return $listStr;
    }

    /**
     * 公共方法
     * 得到用户虚拟机
     * @param string     $useruuid        用户uuid
     * @param string     $orderby         按哪个字段排序,默认使用id字段
     * @param string     $sort            排序方式,默认desc,可传入asc
     * @param int        $limit           从哪个开始,默认0
     * @param int|string $count           本次取多少个,默认10,特别:"all"取所有
     * @param array      $search          虚拟机资源搜索
     * @param int        $subModule       子模块类型：1虚拟化，2私有云，3公有云
     * @return array  资源列表,对应资源表
     * @author xiezhuowei@vinchin.com
     */
    public function pGetUserResourceVM(
        string $useruuid,
        $orderby = 'tree_id',
        $sort = 'desc',
        $limit = 0,
        $count = 10,
        $search = array(),
        $subModule = 1
    ): array
    {
        $this->paramsCheck($useruuid);
        // 根据uuid和auth查询关联的用户uuid集合
        $sql = "select user_uuid from bd_user where manager_uuid = '{$useruuid}'or user_uuid = '{$useruuid}'";
        $list = dbSelect($sql);
        $userUuidArr = !empty($list) ? array_column($list, 'user_uuid') : [];
        $resourceUUIDListAll = [];
        foreach ($userUuidArr as $item) {
            //获取用户所有指定资源uuid
            if ($subModule == 3) {
                $resourceUUIDList = $this->pGetUserAllResource($item, 58);
            } elseif ($subModule == 2) {
                $resourceUUIDList = $this->pGetUserAllResource($item, 61);
            } else {
                $resourceUUIDList = $this->pGetUserAllResource($item, xphp_get_config('resource')['RESOURCE_TYPE']['VM']);
            }
            $resourceUUIDListAll = array_merge($resourceUUIDListAll, $resourceUUIDList);
        }

        //检查用户是否属于Master组
        $masterFlag = (new User())->pCheckUserIsMaster($useruuid);
        //获取资源内容
        return $this->getResourceVM($resourceUUIDListAll, $orderby, $sort, $limit, $count, $masterFlag, $search);
    }

    /**
     * 通过资源uuid列表获取虚拟机
     * @param array      $resourceUUIDList 资源列表
     * @param string     $orderby          按哪个字段排序,默认使用id字段
     * @param string     $sort             排序方式,默认desc,可传入asc
     * @param int        $limit            从哪个开始,默认0
     * @param int|string $count            本次取多少个,默认10,特别:"all"取所有
     * @param bool       $flag             检查用户是否属于Master用户组
     * @param array      $search           虚拟机资源搜索
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceVM(
        array $resourceUUIDList,
        string $orderby,
        string $sort,
        int $limit,
        $count = 10,
        $flag = false,
        $search = array()
    ): array {
        //组合资源uuid方便查询
        $vmuuidStr = $this->groupResourceUUIDToString($resourceUUIDList, 'vm_uuid');
        $vcenterStr = $this->groupResourceUUIDToString($resourceUUIDList, 'vcenter_uuid');
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );

        $sqlCount = "select count(vt.tree_id) as total from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
        $sql = "select vt.vcenter_uuid, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.version, 
            vt.conn_state, vt.power_state, vt.host_uuid, vt.detail from vm_tree vt left join vm_vcenter vv
            on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
        $sqlParams = array(
            xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            xphp_get_config('vm')['VM_TREE_TYPE']['VM']
        );
        $sqlCountParams = array(
            xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            xphp_get_config('vm')['VM_TREE_TYPE']['VM']
        );
        if (!$flag) {
            if (!empty($vmuuidStr) && !empty($vcenterStr)) {
                $sql .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
                $sqlCount .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
            } else {
                $sql .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
                $sqlCount .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
            }
        }

        //条件搜索
        if (!empty($search)) {
            $name = $search['name'];
            $name = v1_escape_wildcard($name);
            $hypervisor = $search['hypervisor'];
            if ($this->checkEmpty($name)) {
                $sql .= ' and (vt.name like ? or vt.dir_path like ? )';
                $sqlCount .= ' and (vt.name like ? or vt.dir_path like ? )';
                $sqlParams = array_merge($sqlParams, array('%' . $name . '%', '%' . $name . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $name . '%', '%' . $name . '%'));
            }

            if (!empty($hypervisor)) {
                $sql .= ' and vv.hypervisor_type = ? ';
                $sqlCount .= ' and vv.hypervisor_type = ? ';
                $sqlParams = array_merge($sqlParams, array($hypervisor));
                $sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
            }
        }

        $sql .= " order by $orderby $sort";

        if ($count != 'all') {
            //如果不是查找所有,带上limit
            $sql .= ' limit ?, ? ';
            $sqlParams = array_merge($sqlParams, array($limit, $count));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if (!empty($data)) {
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        return $info;
    }

    /**
     * 添加存储关联时增加用户和节点强关联
     * @param string $userUUID     用户uuid
     * @param array  $resourceList 资源
     * @return void
     */
    public function addUserStorageNode(string $userUUID, array $resourceList)
    {
        if (empty($resourceList)) {
            return;
        }
        $list = array_column($resourceList, 'resourceuuid');

        $listDes = implode("','", $list);
        //获取存储所在节点uuid
        $sql = "select distinct node_uuid from bd_storage_resource where storage_uuid in ('" . $listDes . "')";
        $data = $this->dbSelect($sql);

        $nodes = !empty($data) ? array_column($data, 'node_uuid') : [];
        //节点资源类型
        $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE')['NODE'];
        //根据资源组UUID获取到已关联的节点UUID集合
        $resourceInfo = $this->pGetUserAllResource($userUUID, $resourceType);
        $haveNode = !empty($resourceInfo) ? array_column($resourceInfo, 'resource_uuid') : [];

        //整理需要加的节点
        $resourceInfo =  array();
        foreach ($nodes as $node) {
            if (!in_array($node, $haveNode)) {
                $resourceInfo[] = array(
                    'resourceuuid' => $node,
                    'vmuuid' => '',
                    'vcenteruuid' => '',
                    'resourceType' => $resourceType
                );
            }
        }

        if (!empty($resourceInfo)) {
            //调用添加资源组与资源关联公共方法
            (new User())->pAddUserResource($userUUID, $resourceInfo);
        }

        return;
    }

    /**
     * 公共方法
     * 得到用户agent
     * @param string $useruuid 用户uuid
     * @param string $orderby  按哪个字段排序,默认使用id字段
     * @param string $sort     排序方式,默认desc,可传入asc
     * @param number $limit    从哪个开始,默认0
     * @param number $count    本次取多少个,默认10,
     *                         特别:"all"取所有
     * @param array  $search   搜索参数
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceAgent($useruuid, $orderby = 'id', $sort = 'desc', $limit = 0, $count = 10, $search = array()): array
    {
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']);

        //检查当前用户是否属于Master组
        $userHandler = User::instance();
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);

        //获取资源内容
        return $this->getResourceAgent($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
    }

    /**
     * 通过资源uuid列表获取agent
     * @param array   $resourceUUIDList 资源列表
     * @param string  $orderby          按哪个字段排序,默认使用id字段
     * @param string  $sort             排序方式,默认desc,可传入asc
     * @param number  $limit            从哪个开始,默认0
     * @param number  $count            本次取多少个,默认10,
     *                                  特别:"all"取所有
     * @param array   $search           搜索参数集合
     * @param boolean $flag             是否是master组
     * @return array  资源列表,对应资源表
     */
    private function getResourceAgent($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false)
    {
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(id) as total from bd_agent where 1=1 ";

        $sql = "select * from bd_agent where 1=1 ";

        if (!$flag) {
            if (!empty($listStr)) {
                $sql .= " and agent_uuid in ($listStr)";
                $sqlCount .= " and agent_uuid in ($listStr)";
            } else {
                $sql .= " and agent_uuid in ('')";
                $sqlCount .= " and agent_uuid in ('')";
            }
        }

        //条件搜索
        if (!empty($search)) {
            $name = $search['name'];
            $sql .= " and ip like '%" . $name . "%'";
            $sqlCount .= " and ip like '%" . $name . "%'";
        }

        $sql .= " order by $orderby $sort ";
        if ($count != 'all') {
            //如果不是查找所有,带上limit
            $sql .= ' limit ?, ? ';
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if (!empty($data)) {
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }

        return $info;
    }
}
