<?php

namespace app\v1\user\v0\logic;

use app\v1\common\logic\Base;
use app\v1\system\v0\logic\Index;

/**
 * note          用户相关的logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:25
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class User extends Base
{
    /**
     * 获取首页和租户界面展示权限
     * @param  $userUUID uuid
     * @return string
     */
    public function getUserPermission()
    {
        $useruuid = xphp_get_user_info()['userUuid'];
        $sql = "select mut.tenant_uuid 
                from bd_user bu, mt_user_tenant mut 
                where bu.user_uuid = mut.user_uuid and bu.user_uuid = ?";
        $data = dbSelect($sql, array($useruuid));

        return !empty($data) ? $data[0]['tenant_uuid'] : '';
    }

    /**
     * 公共方法
     * 获取用户所有权限(包括用户和所在用户组的权限)
     * @param string $useruuid 用户uuid
     * @param bool   $pageFlag flag
     * @return array 一维数组,合并后的所有权限
     * @author xiezhuowei@vinchin.com
     */
    public function pGetUserAllPermission(string $useruuid, $pageFlag = false)
    {
        if ($useruuid == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            // 超级管理员直接以授权的为准,那么默认的 permission 这个就是读取所有的菜单配置
            $permission = v1_get_all_name();
        } else {
            // 如果用户表里存在 user_auth不为空的情况，那么就表示是用户直接分配的这个权限
            $check = $this->dbSelect("select user_auth from bd_user where user_uuid = ?", [$useruuid]);
            if (!empty($check)) {
                $permission = json_decode($check[0]['user_auth'], true);
            }
            if (empty($permission)) {
                //先获取用户的直接关联角色对应的权限
                $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content, br.config from
                bd_user bu, mt_user_role mur, bd_role br, bd_permission bp 
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and 
                br.permission_uuid = bp.permission_uuid and bu.user_uuid = ? and br.lock_flag = ?";
                $data1 = $this->dbSelect($sql, array($useruuid, xphp_get_config('app', 'FLAG')['SET']));

                //再获取用户所在用户组关联的角色对应的权限
                $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content, br.config from
                                bd_user bu, bd_user_group bug, mt_user_user_group muug,
                                mt_user_group_role mugr, bd_role br, bd_permission bp 
                where bu.user_uuid = muug.user_uuid and 
                      muug.user_group_uuid = mugr.user_group_uuid and 
                      bug.user_group_uuid = mugr.user_group_uuid and 
                      mugr.role_uuid = br.role_uuid and 
                      br.permission_uuid = bp.permission_uuid and 
                      bu.user_uuid = ? and 
                      br.lock_flag = ? and 
                      bug.lock_flag = ?";
                $data2 = $this->dbSelect(
                    $sql,
                    array($useruuid, xphp_get_config('app', 'FLAG')['SET'], xphp_get_config('app', 'FLAG')['SET'])
                );

                //合并两个结果权限,先合并,再去重
                $data = !empty($data1) ? array_merge($data1, $data2) : $data2;

                $data = v1_unique_multidim_array($data, 'permission_uuid');
                //合并所有权限
                $permission = [];
                $roleType = [];
                foreach ($data as $d) {
                    $permission = array_merge($permission, json_decode($d['content'], true));
                    $roleType = array_merge($roleType, [$d['config']]);
                }
                $permission = array_unique($permission);
                $roleType = array_unique($roleType);
                if (!in_array(2, $roleType)) {
                    // 那么删除对应的全局观察者权限
                    $permission = array_diff($permission, ['global_observer', 'global_read', 'global_write']);
                }
            }
        }
        //角色权限创建以及页面显示
        if ($pageFlag) {
            return $this->getAuthSoftwarePermission($permission, $useruuid);
        } else {
            // 需要剔除授权系统未授权的权限-全局观察者
            $extension = (new Index())->getExtensionLicense();

            $pagelist = !empty($extension) ? $extension['p'] : [];
            if (!in_array('global_observer', $pagelist)) {
                // 系统未授权全局观察者，那么删除对应的全局观察者权限
                $permission = array_diff($permission, ['global_observer', 'global_read', 'global_write']);
            }
            if (!in_array('global_read', $pagelist)) {
                // 系统未授权全局观察者，那么删除对应的全局观察者权限
                $permission = array_diff($permission, ['global_read']);
            }
            if (!in_array('global_write', $pagelist)) {
                // 系统未授权全局观察者，那么删除对应的全局观察者权限
                $permission = array_diff($permission, ['global_write']);
            }

            if (!empty($extension)) {
                $authFun = $extension['f'];
                // 这里需要判读下可视化大屏的权限，因为这个从授权系统那边过来的
                // 如果是master的话 那么主动追加上 p_visual_screen 大屏权限
                if ($this->pCheckUserIsMaster($useruuid) && $authFun['visualization']) {
                    $permission = array_merge($permission, ['p_visual_screen']);
                }

                // 重新授权没有大屏的，那么这里直接给去掉
                if (!$authFun['visualization']) {
                    $permission = array_diff($permission, ['p_visual_screen']);
                }
            }
            return $permission;
        }
    }

    /**
     * 获取授权文件显示相应模块
     * @param array  $permission permisson
     * @param string $useruuid   useruuid
     * @return array
     */
    public function getAuthSoftwarePermission(array $permission, string $useruuid)
    {
        $systemHandler = new Index();
        $extension = $systemHandler->getExtensionLicense();

        $pagelist = !empty($extension) ? $extension['p'] : [];

        //获取是否授权
        $sql = "select authorized_flag from bd_system";
        $data = dbSelect($sql);
        $authflag = intval($data[0]['authorized_flag']);
        if ($authflag == xphp_get_config('app', 'FLAG')['UNSET']) { //如果备份系统没授权
            //未授权给出初始界面
            $pageList = array(
                'homepage',
                'monitor','data_manager',
                'task', 'current_job', 'history_job',
                'alarm', 'task_alarm', 'system_alarm',
                'log', 'job_log', 'system_log','ha_log',
                'report', 'vm_report', 'storage_report',
                'vmprotect',
                'vm_overview',
                'backup','vmprotect','data_manager','backup_data',
                'recovery','vmprotect_recover','vmrecover','vm_instant_recovery',
                'vm_grain_recovery','vm_platform_recovery',
                'resmanagement', 'infrastructure','vcenter_manager','storage_lanfree',
                'storage_manager','manager','storage_manager_list','storage_pool',
                'backup_manager','node','node_manager',
                'sysmanagement', 'setting_manager', 'system_network', 'set_time', 'set_ip', 'system_dns',
                'authorization_module',
            );
            $permission = $pageList;
        } else {
            //如果备份系统授权并且不是调试模式
            if (!empty($pagelist) && !getEnvs()) {
                //检查是否是admin超级管理员
                $sql = "select user_name from bd_user where user_uuid = ? and user_level = 1";
                $data = $this->dbSelect($sql, array($useruuid));
                //如果授权文件存在页面信息
                if (empty($data)) {
                    $page = array();
                    foreach ($permission as $p) {
                        if (in_array($p, $pagelist)) {
                            $page[] = $p;
                        }
                    }
                    $permission = $page;
                } else {
                    $permission = $pagelist;
                }
            }
        }

        //根据功能授权,再剔除部分页面
        return $this->fromPermissionFilterLicence($permission, $extension['f']);
    }

    /**
     * 根据license的功能项,从permisssion里面筛掉部分页面权限
     * @param array $permission         permission
     * @param  $entensionFunctions func
     * @return array
     */
    private function fromPermissionFilterLicence(array $permission, $entensionFunctions): array
    {
        foreach ($entensionFunctions as $key => $value) {
            switch ($key) {
                case 'nodeExtend':  //节点
                    if (!$value) {
                        $deletePage = array('node_manager');
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
                case 'copy':        //副本
                    if (!$value) {
                        $deletePage = array('datacopy', 'vmcopy', 'vmcopyback', 'vmcopydata', 'remote_system');
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
                case 'archive':     //归档
                    if (!$value) {
                        $deletePage = ['data_archive', 'archive_add', 'archive_back', 'archive_data', 'cloud_storage'];
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
                case 'backupStorageProtect':    //存储保护
                    if (!$value) {
                        $deletePage = array('storage_safe');
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
            }
        }

        return $permission;
    }

    /**
     * 获取page里面设置的所有的方法操作
     * @param bool $flag 标记是否获取老版本的 默认不是
     * @return array
     */
    public function pGetPageFunctions($flag = false)
    {

        $page = xphp_get_menu('', false, $flag);
        return v1_multi_to_twos($page);
    }

    /**
     * 获取page权限的最后一层的所有数组
     * 获取到方法层次的所有权限数组
     * @param array  $permission 最终的授权左侧菜单模块数组
     * @param string $userUUID   登录的用户ID
     * @param bool   $flag       是否是取老的数据
     * @return array 二维数组,合并后的所有权限
     */
    public function pGetPageFunction(array $permission, string $userUUID, bool $flag = false): array
    {
        $page = xphp_get_menu('', false, $flag);

        $pageArr = v1_multi_to_two($page); // 父类(name) => array( 类(name) => array( 'method1', 'method2'))

        // 和左侧的菜单最终授权比较， 保留有权限的子类  子类(name) => array( 'method1', 'method2')
        // 1也就是取出的是最终的授权文件下的所有类和方法
        $array = [];
        foreach ($pageArr as $key => $item) {
            if (in_array($key, $permission)) {
                // 如果存在这个父类 那么这个类下面的所有子类就保留
                foreach ($item as $key2 => $item2) {
                    $array[$key2] = $item2;
                }
            }
        }

        // 获取后台授权的数组(不包含授权文件)
        $permission2 = $this->pGetUserAllPermission($userUUID);

        // 根据授权的name数组从所有的方法数组里面获得最终的二维方法数组
        // 2这步就是在第一步取出的授权文件下面的类和方法在和后台赋权的交集的所有类和方法
        $return = [];
        foreach ($permission2 as $item) {
            if (isset($array[$item]) && !empty($array[$item])) {
                if (is_array($array[$item])) {
                    foreach ($array[$item] as $items) {
                        $arrays = explode('-', $items);
                        $return[$arrays[0]][] = $arrays[1];
                    }
                } else {
                    $arrays = explode('-', $array[$item]);
                    $return[$arrays[0]][] = $arrays[1];
                }
            }
        }
        return $return;
    }

    /**
     * 公共方法
     * 检查用户是否属于Master用户组或角色
     * @param string $useruuid uuid
     * @return boolean 返回检查结果 是|否
     * @author luokai@vinchin.com
     */
    public function pCheckUserIsMaster($useruuid = '')
    {
        $masterFlag = false;
        //获取用户所在所有用户组和所有角色
        if (!empty($useruuid)) {
            $userGroupInfo = $this->pGetUserAllUserGroup($useruuid);
            $roleInfo = $this->pGetUserAllRole($useruuid);
            if (!empty($userGroupInfo)) {
                foreach ($userGroupInfo as $info) {
                    if ($info['user_group_uuid'] == 'e99ae858-d549-4754-9310-457477f4c2e3') {
                        $masterFlag = true;
                    }
                }
            }
            if (!empty($roleInfo)) {
                foreach ($roleInfo as $info) {
                    if ($info['role_uuid'] == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5') {
                        $masterFlag = true;
                    }
                }
            }
        }
        return $masterFlag;
    }

    /**
     * 公共方法
     * 获取用户所在用户组信息
     * @param string $useruuid 用户uuid
     * @return array    多维数组,对应bd_user_group表所有字段
     * @author xiezhuowei@vinchin.com
     */
    public function pGetUserAllUserGroup(string $useruuid)
    {
        $this->paramsCheck($useruuid);
        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, 
                bug.user_group_type, bug.lock_flag, bug.description, bug.config 
                from bd_user bu, bd_user_group bug, mt_user_user_group muug 
                where bu.user_uuid = muug.user_uuid and 
                      muug.user_group_uuid = bug.user_group_uuid and 
                      bu.user_uuid = ?";
        return $this->dbSelect($sql, array($useruuid));
    }

    /**
     * 公共方法
     * 获取用户关联角色
     * @param string $useruuid 用户uuid
     * @return array    多维数组,对应bd_role表所有字段
     * @author luokai@vinchin.com
     */
    public function pGetUserAllRole(string $useruuid)
    {
        $this->paramsCheck($useruuid);
        $sql = "select distinct br.role_uuid, br.role_name, br.lock_flag, br.permission_uuid, br.config
                from bd_user bu, bd_role br, mt_user_role mur
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and bu.user_uuid = ?";
        return $this->dbSelect($sql, array($useruuid));
    }

    /**
     * 取消用户和存储关联关系
     * @param array $params 数组包含 用户Uuid和资源storage_uuid
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserStorage($params = [])
    {

        $storageuuid = is_string($params['storage_uuid']) ? [$params['storage_uuid']] : $params['storage_uuid'];
        //检查资源是否正在使用
        $this->checkDeleteUserStorage($params['userUuid'], $storageuuid);
        //调用统一处理取消用户和存储关联
        return $this->pDeleteUserResourceUnify(
            $params['userUuid'],
            $storageuuid,
            xphp_get_lang('WEB_USERS_CANCEL_STORAGE')
        );
    }

    /**
     * 检查删除存储是否被任务使用
     * @param string $useruuid      用户uuid
     * @param array  $resourceuuids 资源ID集合
     * @return void
     */
    private function checkDeleteUserStorage(string $useruuid, $resourceuuids = [])
    {
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select bt.task_uuid from bd_task bt 
                    where bt.user_uuid = ? and bt.storage_uuid in ('" . $resourceDes . "')";
        $data = $this->dbSelect($sql, array($useruuid));
        if (!empty($data)) {
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_USERS_CANCEL_STORAGE'),
                xphp_get_lang('UI_PLATFORM_SELECTED_RES_USING'),
                'warning'
            ));
        }
    }

    /**
     * 公共方法
     * 取消用户和资源关联
     * @param string $userUuid     用户uuid
     * @param array  $resourceList 资源uuid列表
     * @param string $opName       操作名
     * @return string
     * @author luokai@vinchin.com
     */
    public function pDeleteUserResourceUnify(string $userUuid, array $resourceList, string $opName)
    {
        //删除前检查是否有属于资源组或用户组的资源
        $resourceCount = count($resourceList);
        $resourceDes = implode("','", $resourceList);
        $sql = "select count(resource_uuid) as total 
                    from mt_user_resource where user_uuid = ? and resource_uuid in ('" . $resourceDes . "')";
        $data = $this->dbSelect($sql, array($userUuid));
        $realCount = intval($data[0]['total']);
        $otherFromFlag = ($resourceCount - $realCount) != 0;//选择了来自资源组或用户组关联的资源标志
        //调用取消用户与资源关联公共方法
        $result = $this->pDeleteUserResource($userUuid, $resourceList);
        if ($result) {
            if ($otherFromFlag) {
                return $this->muOpResult(true, $opName, xphp_get_lang('UI_PLATFORM_CANCEL_RESOURCE'), 'success');
            } else {
                return $this->muOpResult(true, $opName);
            }
        } else {
            return $this->muOpResult(false, $opName, '', 'warning');
        }
    }

    /**
     * 删除用户和资源关联关系
     * @param string $useruuid     用户唯一标识
     * @param array  $resourceList 资源唯一标识列表
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    private function pDeleteUserResource(string $useruuid, array $resourceList): bool
    {
        if (empty($resourceList)) {
            return false;
        }
        $resourceDes = implode("','", $resourceList);
        $sql = "delete from mt_user_resource where resource_uuid in ('" . $resourceDes . "') ";
        $sqlParams = array();
        if (!empty($useruuid)) {
            $sql .= ' and user_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 公共方法
     * 检查是否是租户直属管理员
     * @return bool
     */
    public function pCheckTenantManager(): bool
    {
        $user = xphp_get_user_info();
        $flag = false;
        if (!$user || !$user['tenantuuid']) {
            return false;
        }
        $sql = "SELECT admin_uuid FROM bd_tenant WHERE tenant_uuid = ?";
        $data = $this->dbSelect($sql, [$user['tenantuuid']]);
        if ($data) {
            if ($data[0]['admin_uuid'] == $user['userUuid']) {
                $flag = true;   //如果是租户直属管理员
            }
        }

        return $flag;
    }

    /**
     * 公共方法
     * 添加用户和资源关联
     * @param string $userUuid     用户唯一标识
     * @param array  $resourceList 添加的资源信息
     * @return bool 执行结果 成功|失败
     */
    public function pAddUserResource(string $userUuid, array $resourceList): bool
    {
        if (!$resourceList) {
            return false;
        }
        $sql = "INSERT INTO mt_user_resource (user_uuid, resource_uuid, vm_uuid, vcenter_uuid, resource_type) VALUES ";
        foreach ($resourceList as $key => $l) {
            $resourceUuid = $l['resourceuuid'];
            $vmUuid = $l['vmuuid'];
            $vcenterUuid = $l['vcenteruuid'];
            $resourceType = $l['resourceType'];
            $sql .= "('" . $userUuid . "','" . $resourceUuid . "','" . $vmUuid . "','"
                . $vcenterUuid . "','" . $resourceType . "')";
            if ($key != (count($resourceList) - 1)) {
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
     * 添加用户和存储关联关系
     * @param string $userUuid     用户uuid
     * @param array  $resourceList 资源
     * @return boolean 返回取消关联结果 成功|失败
     *@author luokai@vinchin.com
     */
    public function addUserStorage(string $userUuid, array $resourceList)
    {

        $resourceHandler = new \app\v1\resources\v0\logic\Index();
        //关联存储所在节点
        $resourceHandler->addUserStorageNode($userUuid, $resourceList);
        $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'];
        $opName = xphp_get_lang('WEB_USERS_ADD_STORAGE');
        return $this->pAddUserResourceUnify($userUuid, $resourceList, $resourceType, $opName);
    }

    /**
     * 公共方法
     * 添加资源与用户关联
     * @param string $userUUID     用户唯一标识
     * @param array  $resourceList 资源信息列表
     * @param int    $resourceType 资源类型
     * @param string $opName       添加关联操作描述
     * @return string 统一返回操作结果消息到界面
     *@author luokai@vinchin.com
     */
    public function pAddUserResourceUnify(string $userUUID, $resourceList, $resourceType = '', $opName = '')
    {
        //调用添加用户与资源关联公共方法
        $resourceInfo =  array();
        if (!empty($resourceList)) {
            foreach ($resourceList as $res) {
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resourceuuid'],
                    'vmuuid' => $res['vmuuid'],
                    'vcenteruuid' => $res['vcenteruuid'],
                    'resourceType' => $resourceType
                );
            }
        }
        $result = $this->pAddUserResource($userUUID, $resourceInfo);
        if ($result) {
            return $this->muOpResult(true, $opName);
        } else {
            return $this->muOpResult(false, $opName, '', 'warning');
        }
    }

    /**
     * 获取当前用户配额信息
     * @return array
     */
    public function getUserQuotaInfo(): array
    {

        $user = xphp_get_user_info();
        $sql = "select quota from bd_user_extension where user_uuid = ?";
        $data = $this->dbSelect($sql, array($user['userUuid']));
        $sqlUsed = "select sum(write_size) as used_size from bd_backup_timepoint where user_uuid = ? ";
        $dataUsed = $this->dbSelect($sqlUsed, array($user['userUuid']));
        $quota = 0;
        $usedSize = 0;
        $freeSize = 0;
        if (!empty($data)) {
            $quota = intval($data[0]['quota']);
        }
        if (!empty($dataUsed)) {
            $usedSize = intval($dataUsed[0]['used_size']);
        }

        if ($quota == -1) {
            $des = xphp_get_lang('WEB_USERS_QUOTA_UNLIMIT');
        } else {
            $freeSize = $quota - $usedSize;
            $freeSizeDes = v1_calsize($freeSize, true);
            if ($freeSize < 0) {
                $freeSize = 0;
                $freeSizeDes = 0;
            }
            $des = xphp_get_lang('WEB_USERS_QUOTA_TOTAL_SIZE') . ': ' . v1_calsize($quota, true);
            $des .= ',' . xphp_get_lang('WEB_USERS_QUOTA_FREE_SIZE') . ': ' . $freeSizeDes;
        }

        return array(
            'quota' => $quota,
            'freeSize' => $freeSize,
            'des' => $des
        );
    }

    /**
     * 公共方法 根据传递来的最终的授权数组， 获取最终后台授权到操作层面的授权数组
     * @param array  $permission 最终的授权数组（不包含最底层的方法操作）
     * @param string $typeId     类型ID
     *                           type为0表示用户ID，1表示角色ID，2表示分组ID
     * @param int    $type       类型
     *                           默认用户，1角色，2分组
     * @return array 一维数组,合并后的所有权限
     */
    public function gGetPageLatest(array $permission, string $typeId, int $type = 0): array
    {
        $page = xphp_get_menu();

        $pageArr = v1_multi_to_two($page); // 父类(name) => array( 类(name) => array( 'method1', 'method2'))

        // 和左侧的菜单最终授权比较， 保留有权限的子类  子类(name) => array( 'method1', 'method2')
        // 1也就是取出的是最终的授权文件下的所有类和方法
        $array = [];
        foreach ($pageArr as $key => $item) {
            if (in_array($key, $permission)) {
                // 如果存在这个父类 那么这个类下面的所有子类就保留
                foreach ($item as $key2 => $item2) {
                    $array[] = $key2;
                }
            }
        }

        // 获取后台授权的数组(不包含授权文件)
        if ($type === 1) {
            $permission2 = $this->pGetRoleAllPermission($typeId, false);
        } elseif ($type === 2) {
            $permission2 = $this->pGetUserGroupAllPermission($typeId, false);
        } else {
            $permission2 = $this->pGetUserAllPermission($typeId);
        }

        // 2取出后台授权数组和所有的操作数组的交集 就是最终得到的所有的操作数组
        $result = array_intersect($permission2, $array);

        $extension = (new Index())->getExtensionLicense();
        if (!empty($extension)) {
            $authFun = $extension['f'];
            // 这里需要判读下可视化大屏的权限，因为这个从授权系统那边过来的
            // 如果是master的话 那么主动追加上 p_visual_screen 大屏权限
            if (
                (
                    ($typeId == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5' &&
                    $type == 1) ||
                    ($typeId == 'e99ae858-d549-4754-9310-457477f4c2e3' &&
                    $type == 2) ||
                    ($typeId == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9' &&
                    $type == 0)
                ) &&
                $authFun['visualization']
            ) {
                $permission = array_merge($permission, ['p_visual_screen']);
            }

            // 重新授权没有大屏的，那么这里直接给去掉
            if (!$authFun['visualization']) {
                $permission = array_diff($permission, ['p_visual_screen']);
            }
        }

        return array_unique(array_merge($permission, $result)); // 合并返回原来的授权数组
    }

    /**
     * 公共方法
     * 获取用户所有权限(包括用户组的权限)
     * @param string  $roleuuid 用户uuid
     * @param boolean $pageFlag 用户uuid
     * @author luokai@vinchin.com
     * @return array 一维数组,合并后的所有权限
     */
    public function pGetRoleAllPermission($roleuuid, $pageFlag = true)
    {

        if ($roleuuid == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5') {
            // 超级管理组以授权的为准,返回所有配置的菜单权限
            $permission = v1_get_all_name();
        } else {
            //再获取用户所在用户组关联的角色对应的权限
            $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content, br.config from
                bd_role br, bd_permission bp where br.permission_uuid = bp.permission_uuid and br.role_uuid = ?";
            $data = $this->dbSelect($sql, array($roleuuid));
            $permission = array();

            //合并所有权限
            if (!empty($data)) {
                $data = v1_unique_multidim_array($data, 'permission_uuid');
                foreach ($data as $d) {
                    $permission = array_merge($permission, json_decode($d['content'], true));
                }
                $permission = array_unique($permission);
                if (!in_array(2, array_column($data, 'config'))) {
                    // 那么删除对应的全局观察者权限
                    $permission = array_diff($permission, ['global_observer', 'global_read', 'global_write']);
                }
            }
        }

        if ($pageFlag) {
            //在根据系统授权筛选
            $permission = $this->pGetSystemMergePermission($permission);
        }

        return $permission;
    }

    /**
     * 查询出的权限合并系统授权展示
     * @param array $permission 参数数组
     * @return array
     */
    public function pGetSystemMergePermission($permission)
    {
        //获取系统授权页面
        $extension = (new Index())->getExtensionLicense();
        $pagelist = [];
        if (!empty($extension)) {
            $pagelist = $extension['p'];
        }

        $newPermission = [];
        //系统已授权
        if (!empty($pagelist)) {
            foreach ($permission as $p) {
                if (in_array($p, $pagelist)) {
                    $newPermission[] = $p;
                }
            }
        } else {
            //系统未授权用原来的
            $newPermission = $permission;
        }

        return $newPermission;
    }

    /**
     * 公共方法
     * 获取用户所有权限(包括用户组的权限)
     * @param string  $usergroupuuid 用户uuid
     * @param boolean $pageFlag      是否显示页面
     * @return array 一维数组,合并后的所有权限
     *@author luokai@vinchin.com
     */
    public function pGetUserGroupAllPermission(string $usergroupuuid, $pageFlag = true)
    {

        if ($usergroupuuid == 'e99ae858-d549-4754-9310-457477f4c2e3') {
            // 如果是超级管理组 那么以菜单的配置为准
            $permission = v1_get_all_name();
        } else {
            //再获取用户所在用户组关联的角色对应的权限
            $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_user_group bug, mt_user_group_role mugr, bd_role br, bd_permission bp
                where bug.user_group_uuid = mugr.user_group_uuid and mugr.role_uuid = br.role_uuid
                  and br.permission_uuid = bp.permission_uuid and bug.user_group_uuid = ? and br.lock_flag = ?";
            $data = $this->dbSelect($sql, array($usergroupuuid, xphp_get_config('app', 'FLAG')['SET']));
            $permission = array();

            //合并所有权限
            if (!empty($data)) {
                $data = v1_unique_multidim_array($data, 'permission_uuid');
                foreach ($data as $d) {
                    $permission = array_merge($permission, json_decode($d['content'], true));
                }
                $permission = array_unique($permission);
            }
        }

        if ($pageFlag) {
            //在根据系统授权筛选
            $permission = $this->pGetSystemMergePermission($permission);
        }

        return $permission;
    }
}
