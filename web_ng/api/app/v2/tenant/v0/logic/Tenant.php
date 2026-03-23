<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2026-03-23 15:25:09
 * @Description: 
 * @version: 1.0
 */

namespace app\v2\tenant\v0\logic;
use app\v2\common\logic\Base;
use app\v2\common\logic\Log;
use app\v2\job\v0\logic\JobController;
use app\v2\job\v0\logic\JobInfo;
use app\v2\resources\v0\logic\Client;
use app\v2\resources\v0\logic\Index as resourceIndex;
use app\v2\resources\v0\logic\Node;
use app\v2\system\v0\logic\Auth;
use app\v2\system\v0\logic\Settings;
use app\v2\tenant\v0\controller\Index;
use app\v2\user\v0\logic\Index as userIndex;
use app\v2\user\v0\logic\User;
use app\v2\vm\v0\logic\VmPlatform;
use app\v2\vm\v0\logic\VmRecover;

/**
 * note          租户管理
 * @author      wuxian@vinchin.com
 * @date         2024/4/16 16:20
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Tenant extends Base
{
    /**
     * 添加租户
     * @param unknown $params
     */
    public function addTenant($params)
    {
        //TODO
        $tenantName = $params['tenant_name'];//租户名
        $remarks = $params['remarks'];//备注
        $adminUsername = $params['user_name'];//管理员账户
        //生成租户uuid
        $tenantUuid = xphp_uuid();
        //检测可用数量
        $this->checkAvailableNum($params['auth_info'], '');
        //创建管理员用户
        $adminUuid = $this->addTenantAdminUser($params, $tenantUuid);
        $createTime = date('Y-m-d H:i:s');
        $user = xphp_get_user_info();
        $createUserName = $user['userName'];
        $createUserUUID = $user['userUuid'];
        $sqlParams = array($tenantUuid, $tenantName, $remarks, $createTime, $adminUuid, '', xphp_get_config('app', 'FLAG')['SET'], $createUserUUID, $createUserName);
        $sql = "insert into bd_tenant (tenant_uuid, tenant_name, nick_name, create_time, admin_uuid, config, lock_flag, create_user_uuid,create_user_name) 
                values (?, ?, ?, ?, ?, ?, ?,?,?)";
        $result = $this->dbQuery($sql, $sqlParams);
        if ($result) {
            //配置(包括授权、备份存储、恢复宿主机、vcenter_uuid)
            // $params['auth_info']['host_list'] = $params['host_list'];
            // $params['auth_info']['host_list_des'] = $params['host_list_des'];
            $params['auth_info']['storage_list'] = $params['storage_list'];
            $params['auth_info']['storage_list_des'] = $params['storage_list_des'];
            $params['auth_info']['quota_size'] = intval($params['auth_info']['quota_size']);
            $this->addAuthSettings($params['auth_info'], $tenantUuid);
            //添加租户管理员和管理员角色关联
            // 这里根据role_uuid查询出role_name
            $tenantAdmin = $this->dbSelect("SELECT role_name FROM bd_role WHERE role_uuid = 'eee859d5-341a-b95a-33d0-6ed583d0f7a1'", []);
            // 改为根据role_uuid 分别查出对应的role_name
            $roleList = $this->dbSelect("SELECT role_name FROM bd_role WHERE role_uuid in ('eee859d5-341a-b95a-33d0-6ed583d0f7a1','2b214439-8f0b-ec23-a3be-2014ad9baecc','03e12c93-9dc1-371a-6a64-b74965d36723')", []);
            //添加租户管理员和管理员角色关联
            foreach ($roleList as $role) {
                $roleUUID = $this->pGetRoleuuidByName($role['role_name']);
                if (!empty($tenantAdmin) && $role['role_name'] == $tenantAdmin[0]['role_name']) {
                    \app\v2\user\v0\logic\Index::instance()->pAddUserRole($adminUuid, array($roleUUID));
                }
            }
            //添加系统操作日志
            $this->systemLog('SYSTEM_TENANT_ADD_SUCCESS', array($tenantName));
            return $this->muOpResult(true, xphp_get_lang('UI_TENANT_ADD'));
        } else {
            return $this->muOpResult(false, xphp_get_lang('UI_TENANT_ADD'), "", "warning");
        }
    }

    /**
     * 授权配置
     * @params unknown $params config里面的参数
     * @params array $storageList 备份存储
     * @params array $hostList 恢复目的地
     * @params string $tenantUuid 租户uuid
     * @return string $result 添加配置信息的结果
     */
    public function addAuthSettings($params, $tenantUuid)
    {
        $sql = "update bd_tenant set config = ? where tenant_uuid = ?";
        $result = $this->dbExec($sql, array(json_encode($params, true), $tenantUuid));
        if (!$result) {
            return $this->muOpResult(false, xphp_get_lang('WEB_TENANT_SAVE_CONFIG_INFO'), "", "warning");
        }
    }

    /**
     * 通过角色名字获取角色唯一标识
     * @param string $name
     * @return string|fetchAll()
     */
    public function pGetRoleuuidByName($name)
    {
        $sql = "select role_uuid from bd_role where role_name = ?";
        $data = $this->dbSelect($sql, array($name));
        $roleUuid = "";
        if (!empty($data)) {
            $roleUuid = $data[0]['role_uuid'];
        }
        return $roleUuid;
    }

    /**
     * 创建租户管理员
     * @param string $params
     * @param string $tenantUuid
     */
    private function addTenantAdminUser($params, $tenantUuid)
    {
        $adminUsername = $params['user_name'];//管理员账户
        //1本地用户 2外部用户
        $userType = xphp_get_config('app', 'FLAG')['SET'];
        $permission = "";
        $user = xphp_get_user_info();
        $createUserName = $user['userName'];
        $createUserUUID = $user['userUuid'];
        $language = xphp_get_config('app')['lang'];
        $createTime = date('Y-m-d H:i:s');
        //生成一个UUID
        $userUUID = xphp_uuid();
        //绑定节点和存储
        $this->toAddResource($userUUID, $params);
        $quota = intval($params['auth_info']['quota_size']);

        //生成用户唯一认证码
        $code = xphp_random_code(4);
        if (\app\v2\user\v0\logic\Index::instance()->checkCodeExist($code)) {
            //验证码重复，需要重新生成
            $code = xphp_random_code(4);
        }

        $sqlExtentionParams = array($userUUID, $quota);
        $sqlParam = array(
            $userUUID,
            $adminUsername,
            $params['admin_password'],
            $params['admin_email'],
            $userType,
            $permission,
            $createTime,
            $createUserName,
            $createUserUUID,
            $language,
            0,
            $code
        );
        $sql = "insert bd_user (user_uuid, user_name, password, email, user_type, permission,
            create_time, create_user_name, create_user_uuid, language,user_level, auth_code) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlExtention = "insert bd_user_extension (user_uuid,quota) values (?, ?)";
        $this->dbBeginTransaction(); //事务
        $result = $this->dbQuery($sql, $sqlParam);

        $result = $result && $this->dbQuery($sqlExtention, $sqlExtentionParams);
        if ($result) {
            //添加用户和租户关系
            (new \app\v2\user\v0\logic\Index())->pAddUserTenant(array($userUUID), $tenantUuid);
            $this->systemLog('SYSTEM_USER_ADD_SUCCESS', array($adminUsername));
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }
        if (!$result) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_ADD'), "", "warning"));
        }
        return $userUUID;
    }

    /**
     * 修改租户
     * @param unknown $params
     */
    public function editTenant($params)
    {
        $tenantUuid = $params['tenant_uuid'];
        $remarks = $params['remarks'];
        $userUuid = $params['user_uuid'];
        $password = $params['admin_password'];
        $email = $params['email'];
        $createTime = date('Y-m-d H:i:s');
        //检测可用数量
        $this->checkAvailableNum($params['auth_info'], $tenantUuid);
        //更新用户
        //密码是空代表没有修改密码
        $sql = "select password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUuid));
        if ($data) {
            $password = empty($password) ? $data[0]['password'] : $password;
        }
        $this->dbBeginTransaction(); //事务
        //更新bd_user那张表
        $sqlUser = "update bd_user set password = ?, create_time = ?, email = ? where user_uuid = ? ";
        $sqlUserParams = array($password, $createTime, $email, $userUuid);
        $result = $this->dbExec($sqlUser, $sqlUserParams);
        $config = array();
        //配置(包括授权、备份存储、恢复宿主机)
        // $params['auth_info']['host_list'] = $params['host_list'];
        // $params['auth_info']['host_list_des'] = $params['host_list_des'];
        $params['auth_info']['storage_list'] = $params['storage_list'];
        $params['auth_info']['storage_list_des'] = $params['storage_list_des'];
        $params['auth_info']['quota_size'] = intval($params['auth_info']['quota_size']);
        $sqlParams = array($remarks, $createTime, json_encode($params['auth_info'], true), $tenantUuid);
        $sql = "update bd_tenant set nick_name = ?, create_time = ?, config = ? where tenant_uuid = ?";
        $result = $result && $this->dbExec($sql, $sqlParams);
        if ($result) {
            $this->dbCommit();
            //先删除用户所有关联的资源  再添加
            $this->editTenatResource($params);
        } else {
            $this->dbRollBack();
        }
        return $this->muOpResult($result, xphp_get_lang('UI_TENANT_MODIFY'));
    }

    /**
     * 修改用户和资源关联关系 -- 7,8是存储和节点
     * @param object $params  修改信息
     * @return boolean 执行结果 成功|失败
     */
    private function editTenatResource($params)
    {
        $sql = "delete from mt_user_resource where resource_type in (7,8) and user_uuid = ?";
        $sqlParams = array($params['user_uuid']);
        $result = $this->dbExec($sql, $sqlParams);
        $this->toAddResource($params['user_uuid'], $params);
        return $result;
    }

    /**
     * 启用租户
     * @param unknown $params
     */
    public function enableTenant($params)
    {
        $tenantList = $params['uuids'];
        $userList = [];//租户下的用户
        $tenantStr = implode("','", $tenantList);
        $tenantNameDes = $this->getTenantNameList($tenantList);
        $sql = "update bd_tenant set lock_flag = ? where tenant_uuid in ('" . $tenantStr . "')";
        $result = $this->dbExec($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        //禁用租户下的用户
        foreach ($tenantList as $tenant) {
            $userList = array_merge($userList, $this->getTenantAllUser($tenant));
        }
        (new userIndex)->unlockUser(array(
            "users" => $userList
        ));
        $this->systemLog('SYSTEM_TENANT_UNLOCK_SUCCESS', array($tenantNameDes));
        if (!$result)
            return false;
        return xphp_get_lang('UI_TENANT_ENABLE');
    }

    /**
     * 禁用租户
     * @param unknown $params
     */
    public function disableTenant($params)
    {
        $tenantuuid = $params['tenant_uuid'];
        $tenantNameDes = $this->getTenantNameList(array($tenantuuid));
        //获取租户下所有用户
        $userList = $this->getTenantAllUser($tenantuuid);
        //停止租户上所有任务
        $this->checkTaskIfRunning($userList, xphp_get_lang('UI_TENANT_DISABLE'));
        $sql = "update bd_tenant set lock_flag = ? where tenant_uuid = ? ";
        $result = $this->dbExec($sql, array(xphp_get_config('app', 'FLAG')['UNSET'], $tenantuuid));
        //禁用租户下的用户
        (new userIndex)->lockUser(array(
            "users" => $userList
        ));
        $this->systemLog('SYSTEM_TENANT_LOCK_SUCCESS', array($tenantNameDes));
        if (!$result)
            return false;
        return xphp_get_lang('UI_TENANT_DISABLE');
    }

    /**
     * 删除租户
     * @param unknown $params
     */
    public function deleteTenant($params)
    {
        //TODO
        $tenantUuid = $params['tenant_uuid'];
        $sql = "select tenant_name from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($tenantUuid));
        $tenantName = "";
        if (!empty($data)) {
            $tenantName = $data[0]['tenant_name'];
        }
        //获取租户下所有用户、用户组、角色
        $userList = $this->getTenantAllUser($tenantUuid);
        $userGroupList = $this->getTenantAllUserGroup($tenantUuid);
        $roleInfo = $this->getTenantAllRole($tenantUuid);
        $roleList = $roleInfo['roleList'];
        $permissionList = $roleInfo['permissionList'];
        $resourceGroupList = $this->getTenantAllResourceGroup($tenantUuid);
        //首先清空删除租户日志信息
        $this->cleanTenantLog();

        //检查租户内是否有备份数据
        $this->checkIfTimepointExist($userList);
        //检查租户内资源是否未清理
        $this->checkTenantResourceExist($userList);
        //停止租户上所有任务
        $this->checkTaskIfRunning($userList, xphp_get_lang('UI_TENANT_DELETE'));
        //开始事务
        $this->dbBeginTransaction();
        $result = true;
        //删除用户:用户的任务(强制停止,然后再删除),用户的告警,用户的日志, 租户用户关联
        $result = $result && $this->deleteTenantAllJob($userList);
        //删除用户的告警和日志
        $result = $result && $this->deleteTenantLogAndAlarm($userList);
        //删除用户
        $result = $result && $this->deleteTenantAllUser($tenantUuid, $userList);
        //删除用户组
        $result = $result && $this->deleteTenantAllUserGroup($tenantUuid, $userGroupList);
        //删除角色
        $result = $result && $this->deleteTenantAllRole($tenantUuid, $roleList, $permissionList);
        //删除资源组
        $result = $result && $this->deleteTenantAllResourceGroup($tenantUuid, $resourceGroupList);
        //删除租户的域服务器
        $result = $result && $this->deleteTenantAllDomainServer($tenantUuid);
        //删除租户
        $sql = "delete from bd_tenant where tenant_uuid = ? ";
        $result = $result && $this->dbExec($sql, array($tenantUuid));
        if ($result) {
            //成功删除租户
            $this->writeTenantLog("UI_DELETE_TENANT_LOG20_1");
            $this->systemLog('SYSTEM_TENANT_DELETE_SUCCESS', array($tenantName));
            $this->dbCommit();
        } else {
            //删除租户失败，回滚操作
            $this->writeTenantLog("UI_DELETE_TENANT_LOG20_0");
            $this->dbRollBack();
        }
        if (!$result)
            return false;
        return xphp_get_lang('UI_TENANT_DELETE');
    }

    /**
     * 检查租户内是否还有资源存在
     * @param $userList
     * @return void
     */
    private function checkTenantResourceExist($userList)
    {
        $userListDes = implode("','", $userList);
        //检查是否有添加的虚拟化中心
        $this->checkVcenterExist($userListDes);
        //检查是否有添加的私有云平台
        $this->checkPrivateCloudExist($userListDes);
        //检查是否有添加的公有云平台
        $this->checkPublicCloudExist($userListDes);
        //检查是否有添加的客户端
        $this->checkClientExist($userListDes);
        //检查是否有添加的NAS设备
        $this->checkNasExist($userListDes);
        //检查是否有添加的M365组织
        $this->checkM365OrganizationExist($userListDes);
        return true;
    }

    /**
     * 检查是否有虚拟化中心未删除
     * @param $userListDes
     * @return void
     */
    private function checkVcenterExist($userListDes)
    {
        $privateCloudType = xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'];
        $publicCloudType = xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'];
        $privateCloudTypeStr = implode("','", $privateCloudType);
        $publicCloudTypeStr = implode("','", $publicCloudType);
        $sql = "select vcenter_id from vm_vcenter where user_uuid in ('" . $userListDes . "') 
        and hypervisor_type not in ('" . $privateCloudTypeStr . "') and hypervisor_type not in ('" . $publicCloudTypeStr . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('WEB_TENANT_DELETE_VM_CENTER'), "warning"));
        }
    }

    /**
     * 检查是否有私有云平台未删除
     * @param $userListDes
     * @return void
     */
    private function checkPrivateCloudExist($userListDes)
    {
        $privateCloudType = xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'];
        $privateCloudTypeStr = implode("','", $privateCloudType);
        $sql = "select vcenter_id from vm_vcenter where user_uuid in ('" . $userListDes . "') 
        and hypervisor_type in ('" . $privateCloudTypeStr . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('WEB_TENANT_DELETE_PRIVATE_CLOUD'), "warning"));
        }
    }

    /**
     * 检查是否有公有云平台未删除
     * @param $userListDes
     * @return void
     */
    private function checkPublicCloudExist($userListDes)
    {
        $publicCloudType = xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'];
        $publicCloudTypeStr = implode("','", $publicCloudType);
        $sql = "select vcenter_id from vm_vcenter where user_uuid in ('" . $userListDes . "') 
        and hypervisor_type in ('" . $publicCloudTypeStr . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('WEB_TENANT_DELETE_PUBLIC_CLOUD'), "warning"));
        }
    }

    /**
     * 检查是否有客户端未删除
     * @param $userListDes
     * @return void
     */
    private function checkClientExist($userListDes)
    {
        $sql = "select id from bd_agent where user_uuid in ('" . $userListDes . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('WEB_TENANT_DELETE_AGENT'), "warning"));
        }
    }

    /**
     * 检查是否有NAS设备未删除
     * @param $userListDes
     * @return void
     */
    private function checkNasExist($userListDes)
    {
        $sql = "select id from nas_storage_resource where user_uuid in ('" . $userListDes . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('WEB_TENANT_DELETE_NAS'), "warning"));
        }
    }

    /**
     * 检查是否有m365组织未删除
     * @param $userListDes
     * @return void
     */
    private function checkM365OrganizationExist($userListDes)
    {
        $sql = "select id from m365_organization where user_uuid in ('" . $userListDes . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('WEB_TENANT_DELETE_M365_ORGANIZATION'), "warning"));
        }
    }

    /**
     * 获取租户列表
     * @param unknown $params
     */
    public function getTenantList($params)
    {
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //搜索参数
        $keyword = $params['search'];
        //获取数据
        $sql = "select bt.tenant_uuid, bt.tenant_name, bt.nick_name,unix_timestamp(bt.create_time) create_time, bt.lock_flag, bt.config, bu.user_uuid, bu.user_name
                from bd_tenant bt left join bd_user bu on bt.admin_uuid = bu.user_uuid ";
        $sqlcount = "select count(bt.id) as count from bd_tenant bt left join bd_user bu on bt.admin_uuid = bu.user_uuid ";
        $sqlparams = array();
        //按租户名搜索
        if (!empty($keyword)) {
            $sql .= " where bt.tenant_name like '%" . $keyword . "%' or bt.nick_name like '%" . $keyword . "%'";
            $sqlcount .= " where bt.tenant_name like '%" . $keyword . "%' or bt.nick_name like '%" . $keyword . "%'";
        }
        //如果还有排序参数,则进行排序
        if (!empty($sort) && !empty($order)) {
            $sql .= " order by " . $sort . " " . $order;
        }
        $sql .= " limit ?, ?";
        $sqlparams = array_merge($sqlparams, array($offset, $limit));
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount);
        $info = array(
            'rows' => array(),
            'total' => intval($count[0]['count']),
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        //循环处理
        foreach ($result as $each) {
            $quotaArr = array();
            $quotaStr = "";
            $config = json_decode($each['config'], true);
            //判断系统授权方式，根据授权方式判断容量字段
            switch ($config['auth_way']) {
                case 1://普通容量
                    $quotaArr = array(
                        "normal_capacity_mode" => $config['normal_quota_size'] ?? 1,
                        'normal_quota_size' => $config['normal_quota_size'] ?? 0,
                        'normal_quota_show_size' => $config['normal_quota_show_size'] ?? "0",
                        'normal_quota_show_unit' => $config['normal_quota_show_unit'] ?? "GB",
                    );
                    $quotaStr = "normal";
                    break;
                case 2://实时和定时容量分开（不支持实时，暂时只有定时）
                    $quotaArr = array(
                        "time_capacity_mode" => $config['time_quota_size'] ?? 1,
                        'time_quota_size' => $config['time_quota_size'] ?? 0,
                        'time_quota_show_size' => $config['time_quota_show_size'] ?? "0",
                        'time_quota_show_unit' => $config['time_quota_show_unit'] ?? "GB",
                    );
                    $quotaStr = "time";
                    break;
                case 5:
                case 6://文件系列按容量
                    $quotaArr = array(
                        "file_capacity_mode" => $config['file_quota_size'] ?? 1,
                        'file_quota_size' => $config['file_quota_size'] ?? 0,
                        'file_quota_show_size' => $config['file_quota_show_size'] ?? "0",
                        'file_quota_show_unit' => $config['file_quota_show_unit'] ?? "GB",
                    );
                    $quotaStr = "file";
                    break;
            }
            if (!array_key_exists('machinecopy_num', $config)) {
                $config = array(
                    "auth_way" => 1,
                    "machinecopy_num" => 0,
                    "filecopy_num" => 0,
                    "nascopy_num" => 0,
                    "dbcdpcopy_num" => 0,
                    "storage_list" => [],
                    "storage_list_des" => []
                );
                $config = array_merge($config, $quotaArr);
                $info['rows'][] = array(
                    //租户唯一标识
                    'tenant_uuid' => $each['tenant_uuid'],
                    //租户名
                    'tenant_name' => $each['tenant_name'],
                    //备注信息
                    'nickname' => $each['nick_name'],
                    //租户管理员名
                    'user_name' => $each['user_name'],
                    'user_uuid' => $each['user_uuid'],
                    //已备份数据
                    'backup_data' => v2_calSize($this->getTenantBackupData($each['tenant_uuid']), true) . '/' . '--',
                    //配额
                    'quota' => '--',
                    //状态 启用|禁用
                    'lock_flag' => intval($each['lock_flag']),
                    //租户添加时间
                    'create_time' => date('Y-m-d H:i:s', $each['create_time']),
                    'config' => $config,
                    'old_version' => true,//区分是否是旧版本数据，旧版本数据提示去修改重新配置
                );
                continue;
            }
            $quota = $config[$quotaStr . '_quota_size'] == -1 ? xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED') : $config[$quotaStr . '_quota_show_size'] . $config[$quotaStr . '_quota_show_unit'];
            $info['rows'][] = array(
                //租户唯一标识
                'tenant_uuid' => $each['tenant_uuid'],
                //租户名
                'tenant_name' => $each['tenant_name'],
                //备注信息
                'nickname' => $each['nick_name'],
                //租户管理员名
                'user_name' => $each['user_name'],
                'user_uuid' => $each['user_uuid'],
                //已备份数据
                'backup_data' => v2_calSize($this->getTenantBackupData($each['tenant_uuid']), true) . '/' . $quota,
                //配额
                'quota' => $quota,
                //状态 启用|禁用
                'lock_flag' => intval($each['lock_flag']),
                //租户添加时间
                'create_time' => date('Y-m-d H:i:s', $each['create_time']),
                'config' => $config,
                'old_version' => false,
            );
        }
        return $info;
    }

    /**
     * 获取租户详情信息
     * @param unknown $params
     */
    public function getTenantDetail($params)
    {
        $tenantUuid = $params['tenant_uuid'];
        $sql = "select config, admin_uuid from bd_tenant where tenan_uuid = ?";
        //获取租户配置信息
        $data = $this->dbSelect($sql, array($tenantUuid));
        $config = json_decode($data[0]['config'], true);
        //获取租户拥有资源
        $resourceList = $this->getTenantAllResource($tenantUuid);
        $info = array(
            'vm_info' => array(
                'used' => '',
                'total' => ''
            ),
            'aws_info' => array(
                'used' => '',
                'total' => ''
            ),
            'ops_info' => array(
                'used' => '',
                'total' => ''
            ),
            'db_info' => array(
                'used' => '',
                'total' => ''
            ),
            'vm_info' => array(
                'used' => '',
                'total' => ''
            ),
            'os_info' => array(
                'used' => '',
                'total' => ''
            ),
            'm365_info' => array(
                'used' => '',
                'total' => ''
            ),
            'restore_host_list' => '',
            'backup_storage_list' => '',
        );
        return $info;
    }

    /**
     * 获取租户下所有用户使用的数据量
     * @param $useruuid
     * @return void
     */
    public function getTenantBackupData($tenantuuid)
    {
        //获取租户下所有用户
        $userList = $this->getTenantAllUser($tenantuuid);
        //获取租户下所有用户已使用的备份数据量
        $userListDes = implode("','", $userList);
        $sql = "select sum(write_size) as backup_size from bd_backup_timepoint where user_uuid in ('" . $userListDes . "') 
                and task_type not in (" . xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] . "," . xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] . "," . xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] . "," . xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH'] . ")";
        $data = $this->dbSelect($sql);
        return intval($data[0]['backup_size']);
    }

    /**
     * 获取所有租户名称
     * @param array $tenantList
     */
    private function getTenantNameList($tenantList)
    {
        $tenantStr = implode("','", $tenantList);
        $sql = "select tenant_name from bd_tenant where tenant_uuid in ('" . $tenantStr . "')";
        $data = $this->dbSelect($sql);
        $info = "";
        foreach ($data as $key => $d) {
            if ($key == count($data) - 1) {
                $info .= $d['tenant_name'];
            } else {
                $info .= $d['tenant_name'] . ",";
            }
        }

        return $info;

    }

    /**
     * 获取租户下所有用户
     * @param string $tenantuuid
     * @return unknown[]
     */
    public function getTenantAllUser($tenantuuid)
    {
        $list = array();
        $sql = "select bu.user_uuid from bd_user bu, bd_tenant bt where (bu.create_user_uuid = bt.admin_uuid or bu.user_uuid = bt.admin_uuid) and bt.tenant_uuid = ? ";
        $userInfo = $this->dbSelect($sql, array($tenantuuid));
        if (!empty($userInfo)) {
            foreach ($userInfo as $user) {
                $list[] = $user['user_uuid'];
            }
        }

        return $list;
    }

    /**
     * 检查并停止当前租户所有任务
     * @param array $userList
     * @param string $opName 操作名
     * @return void|boolean
     */
    private function checkTaskIfRunning($userList, $opName)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG1_1");
        if (empty($userList))
            return true;
        $userDes = implode("','", $userList);
        $stopFlag = false;
        $i = 0;
        while (!$stopFlag) {
            $sql = "select task_uuid, task_type, module_type, task_status from bd_task where task_status != ? and user_uuid in ('" . $userDes . "')";
            $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED']));
            $t1 = time();
            if (!empty($data)) {
                //循环去停止任务
                $taskList = [];
                foreach ($data as $d) {
                    $taskList[] = $d['task_uuid'];
                }
                (new JobController)->getClass($taskList, "stopJob");
                $t2 = time();
                //间隔10秒检查
                sleep(2 + ($t2 - $t1));
                $i++;
                //尝试10次停止任务后，直接失败退出
                if ($i == 10) {
                    $this->writeTenantLog("UI_DELETE_TENANT_LOG2_2");
                    exit($this->muOpResult(false, $opName));
                }
            } else {
                $stopFlag = true;
                break;
            }

        }

        //stop all jobs;
        $this->writeTenantLog("UI_DELETE_TENANT_LOG2_1");
        return true;

    }

    /**
     * 写入删除租户日志内容
     * @param string $content
     */
    private function writeTenantLog($content)
    {
        $file = xphp_get_config('app', 'DELETE_TENANT_PATH');
        if (!file_exists($file)) {
            $addFile = "touch " . $file;
            exec($addFile);
        }
        return file_put_contents($file, $content . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * 获取租户所有用户组
     * @param string $tenantuuid
     * @return unknown[]
     */
    private function getTenantAllUserGroup($tenantuuid)
    {
        $list = array();
        $sql = "select bug.user_group_uuid from bd_user_group bug, bd_tenant bt where bug.create_user_uuid = bt.admin_uuid and bt.tenant_uuid = ? ";
        $userGroupInfo = $this->dbSelect($sql, array($tenantuuid));
        if (!empty($userGroupInfo)) {
            foreach ($userGroupInfo as $usergroup) {
                $list[] = $usergroup['user_group_uuid'];
            }
        }

        return $list;
    }

    /**
     * 获取租户所有角色
     * @param string $tenantuuid
     * @return unknown[]
     */
    private function getTenantAllRole($tenantuuid)
    {
        $roleList = array();
        $permissionList = array();
        $roleInfo = $this->pGetTenantAllRole($tenantuuid);
        if (!empty($roleInfo)) {
            foreach ($roleInfo as $role) {
                $roleList[] = $role['role_uuid'];
                $permissionList[] = $role['permission_uuid'];
            }
        }
        $list = array(
            'roleList' => $roleList,
            'permissionList' => $permissionList
        );
        return $list;
    }

    /**
     * 公共方法
     * 获取租户的所有角色
     * @param string $tenantuuid 租户uuid
     * @return array  角色列表,包含bd_role表内容
     * @author luokai@vinchin.com
     */
    public function pGetTenantAllRole($tenantuuid)
    {

        $sql = "select distinct role_uuid, role_name, permission_uuid, lock_flag, tenant_uuid from bd_role where tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        return $data;
    }

    /**
     * 公共方法
     * 取消租户与所有用户组关联
     * @param string $tenantuuid
     * @return boolean
     * @author luokai@vinchin.com
     */
    public function pDeleteTenantUserGroup($tenantuuid)
    {
        if (empty($tenantuuid))
            return true;
        $sql = "delete from mt_user_group_tenant where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($tenantuuid));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取租户所有资源组
     * @param string $tenantuuid
     * @return unknown[]
     */
    public function getTenantAllResourceGroup($tenantuuid)
    {
        $list = array();
        $sql = "select resource_group_uuid from bd_resource_group where tenant_uuid = ?";
        $info = $this->dbSelect($sql, array($tenantuuid));
        if (!empty($info)) {
            foreach ($info as $d) {
                $list[] = $d['resource_group_uuid'];
            }
        }
        return $list;
    }

    /**
     * 清空删除租户日志文件内容
     */
    private function cleanTenantLog()
    {
        $file = xphp_get_config('app', 'DELETE_TENANT_PATH');
        if (!file_exists($file)) {
            $addFile = "touch " . $file;
            exec($addFile);
        }
        return file_put_contents($file, "", LOCK_EX);
    }

    /**
     * 检查租户是否存在备份数据
     * @param array $userList
     * @return boolean
     */
    private function checkIfTimepointExist($userList)
    {
        if (empty($userList))
            return true;
        $userDes = implode("','", $userList);
        $sql = "select bbt.timepoint_uuid, bbt.module_type, bbt.task_type, bsr.node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.user_uuid in ('" . $userDes . "') and bbt.available_flag = ? ";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_TENANT_DELETE'), xphp_get_lang('UI_PLATFORM_NOT_CLEAR_BAKDATA'), "warning"));
        }

        return true;
    }

    /**
     * 检查并停止当前租户所有任务
     * @param array $userList
     * @return void|boolean
     */
    private function deleteTenantAllJob($userList)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG3_1");
        if (empty($userList))
            return true;
        $userDes = implode("','", $userList);
        $sql = "select task_uuid, task_type, module_type, task_status from bd_task where user_uuid in ('" . $userDes . "')";
        $data = $this->dbSelect($sql);
        $t1 = time();
        if (!empty($data)) {
            //循环去删除任务
            $taskList = [];
            foreach ($data as $d) {
                $taskList[] = $d['task_uuid'];
            }
            (new JobController)->getClass($taskList, "delJob");
            $t2 = time();
            //间隔10秒检查
            sleep(2 + ($t2 - $t1));
        }
        //删除历史任务
        $sql = "delete from bd_history_task where user_uuid in ('" . $userDes . "')";
        $result = $this->dbExec($sql);
        if ($result) {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG4_1");
        } else {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG4_0");
        }
        //delete all jobs;
        return $result;

    }

    /**
     * 删除租户所有日志和告警
     * @param array $userList
     * @return boolean
     */
    public function deleteTenantLogAndAlarm($userList)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG5_1");
        if (empty($userList))
            return true;
        $userDes = implode("','", $userList);

        //查询任务日志并删除
        $sqlTaskLog = "select id from bd_task_log where user_uuid in ('" . $userDes . "')";
        $dataTaskLog = $this->dbSelect($sqlTaskLog);
        $t1 = time();
        if (!empty($dataTaskLog)) {
            $info = array();
            foreach ($dataTaskLog as $d) {
                $info[] = $d['id'];
            }
            $params = array('id' => $info, 'tenantFlag' => true);
            Log::instance()->deleteTaskLog($params);
            // \app\v2\log\v0\logic\Index::instance()->deleteTaskLog($params);
            $t2 = time();
            //间隔1秒检查
            sleep(1 + ($t2 - $t1));
        }

        //查询系统日志并删除
        $sqlSystemLog = "select id from bd_system_log where user_uuid in ('" . $userDes . "')";
        $dataSystemLog = $this->dbSelect($sqlSystemLog);
        $t3 = time();
        if (!empty($dataSystemLog)) {
            $info = array();
            foreach ($dataSystemLog as $d) {
                $info[] = $d['id'];
            }
            $params = array('id' => $info, 'tenantFlag' => true);
            Log::instance()->deleteSystemLog($params);
            $t4 = time();
            //间隔1秒检查
            sleep(1 + ($t4 - $t3));
        }

        //查询任务告警并删除
        $sqlTaskAlarm = "select task_alarm_id from bd_task_alarm where user_uuid in ('" . $userDes . "')";
        $dataTaskAlarm = $this->dbSelect($sqlTaskAlarm);
        $t5 = time();
        if (!empty($dataTaskAlarm)) {
            $info = array();
            foreach ($dataTaskAlarm as $d) {
                $info[] = $d['task_alarm_id'];
            }
            $params = array('id' => $info, 'tenantFlag' => true);
            //            Alarm::instance()->deleteTaskAlarm($params);
            $t6 = time();
            //间隔1秒检查
            sleep(1 + ($t6 - $t5));
        }

        $this->writeTenantLog("UI_DELETE_TENANT_LOG6_1");
        return true;
    }

    /**
     * 删除租户所有用户
     * @param string $tenantuuid
     * @param array $userList
     * @return boolean
     */
    public function deleteTenantAllUser($tenantuuid, $userList)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG7_1");
        //取消租户和所有用户关联
        $result = $this->pDeleteTenantUser($tenantuuid);
        //删除租户创建用户

        if (empty($userList))
            return $result;
        $userDes = implode("','", $userList);
        //删除用户与用户组关联
        $sql = "delete from mt_user_user_group where user_uuid in ('" . $userDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户与角色关联
        $sql = "delete from mt_user_role where user_uuid in ('" . $userDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户与资源关联
        $sql = "delete from mt_user_resource where user_uuid in ('" . $userDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户与资源组关联
        $sql = "delete from mt_user_resource_group where user_uuid in ('" . $userDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户与组织结构关联
        $sql = "delete from mt_user_organization where user_uuid in ('" . $userDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户
        $sql = "delete from bd_user where user_uuid in ('" . $userDes . "')";
        $result = $result && $this->dbExec($sql);
        $des = "error";
        if ($result) {
            $des = "success";
        }
        if ($des == 'error') {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG8_0");
        } else {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG8_1");
        }

        return $result;
    }

    /**
     * 删除租户所有用户组
     * @param string $tenantuuid
     * @param array $userGroupList
     * @return boolean
     */
    public function deleteTenantAllUserGroup($tenantuuid, $userGroupList)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG9_1");
        //取消租户和所有用户组关联
        $result = $this->pDeleteTenantUserGroup($tenantuuid);
        //删除租户创建用户

        if (empty($userGroupList))
            return $result;
        $userGroupDes = implode("','", $userGroupList);
        //删除用户组与用户关联
        $sql = "delete from mt_user_user_group where user_group_uuid in ('" . $userGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户组与角色关联
        $sql = "delete from mt_user_group_role where user_group_uuid in ('" . $userGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户组与资源关联
        $sql = "delete from mt_user_group_resource where user_group_uuid in ('" . $userGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户组与资源组关联
        $sql = "delete from mt_user_group_resource_group where user_group_uuid in ('" . $userGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除用户组
        $sql = "delete from bd_user_group where user_group_uuid in ('" . $userGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        $des = "error";
        if ($result) {
            $des = "success";
        }
        if ($des == 'error') {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG10_0");
        } else {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG10_1");
        }
        return $result;
    }


    /**
     * 删除租户所有角色
     * @param string $tenantuuid
     * @param array $roleList
     * @param array $permissionList
     * @return boolean
     */
    public function deleteTenantAllRole($tenantuuid, $roleList, $permissionList)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG11_1");
        if (empty($roleList) || empty($permissionList))
            return true;
        $roleDes = implode("','", $roleList);
        $permissionDes = implode("','", $permissionList);
        //删除用户组与角色关联
        $sql = "delete from mt_user_role where role_uuid in ('" . $roleDes . "')";
        $result = $this->dbExec($sql);

        //删除用户组与角色关联
        $sql = "delete from mt_user_group_role where role_uuid in ('" . $roleDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除角色对应权限
        $sql = "delete from bd_permission where permission_uuid in ('" . $permissionDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除角色
        $sql = "delete from bd_role where role_uuid in ('" . $roleDes . "')";
        $result = $result && $this->dbExec($sql);

        $des = "error";
        if ($result) {
            $des = "success";
        }
        if ($des == 'error') {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG12_0");
        } else {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG12_1");
        }
        return $result;
    }

    /**
     * 删除租户所有组织结构
     * @param string $tenantuuid
     * @return boolean
     */
    public function deleteTenantAllDomainServer($tenantuuid)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG21_1");
        if (empty($tenantuuid))
            return true;
        //删除与租户内的组织结构
        $sql = "delete from bd_domain_server where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($tenantuuid));
        $des = "error";
        if ($result) {
            $des = "success";
        }
        if ($des == 'error') {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG19_0");
        } else {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG19_1");
        }
        return $result;
    }

    /**
     * 删除租户所有资源组
     * @param string $tenantuuid
     * @param array $resourceGroupList
     * @return boolean
     */
    public function deleteTenantAllResourceGroup($tenantuuid, $resourceGroupList)
    {
        $this->writeTenantLog("UI_DELETE_TENANT_LOG13_1");
        if (empty($resourceGroupList))
            return true;
        $resourceGroupDes = implode("','", $resourceGroupList);

        //删除用户与资源组关联
        $sql = "delete from mt_user_resource_group where resource_group_uuid in ('" . $resourceGroupDes . "')";
        $result = $this->dbExec($sql);

        //删除用户组与资源组关联
        $sql = "delete from mt_user_group_resource_group where resource_group_uuid in ('" . $resourceGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除资源组与资源关联
        $sql = "delete from mt_resource_resource_group where resource_group_uuid in ('" . $resourceGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        //删除资源组
        $sql = "delete from bd_resource_group where resource_group_uuid in ('" . $resourceGroupDes . "')";
        $result = $result && $this->dbExec($sql);

        $des = "error";
        if ($result) {
            $des = "success";
        }
        if ($des == 'error') {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG14_0");
        } else {
            $this->writeTenantLog("UI_DELETE_TENANT_LOG14_1");
        }
        return $result;
    }

    /**
     * 公共方法
     * 取消租户与所有用户关联
     * @param string $tenantuuid
     * @return boolean
     * @author luokai@vinchin.com
     */
    public function pDeleteTenantUser($tenantuuid)
    {
        if (empty($tenantuuid))
            return true;
        $sql = "delete from mt_user_tenant where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($tenantuuid));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取租户需要修改的信息
     * @param unknown $params
     */
    public function getTenantEditInfo($params)
    {
        $tenantUuid = $params['tenant_uuid'];
        $sql = "select bt.admin_uuid, bt.tenant_name, bt.nick_name, bt.config, bu.user_name, bu.password, bu.email from bd_tenant bt, bd_user bu 
                where bt.admin_uuid = bu.user_uuid and bt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantUuid));
        $info = array();
        if (!empty($data)) {
            $info = array(
                'tenant_name' => $data[0]['tenant_name'],
                'remarks' => $data[0]['nick_name'],
                'admin_username' => $data[0]['user_name'],
                'admin_password' => $data[0]['password'],
                'admin_email' => $data[0]['email'],
                'admin_uuid' => $data[0]['admin_uuid'],
                'config' => json_decode($data[0]['config'], true),
            );
        }
        return $info;
    }

    /**
     * 检测租户可用资源数量
     * @param unknown $params
     * @param string $tenantUuid 租户uuid
     */
    private function checkAvailableNum($params, $tenantUuid)
    {
        $sql = "SELECT
                    unix_timestamp( register_time ) register_time,
                    license_type,
                    file_max_num,
                    vm_max_num,
                    oracle_max_num,
                    os_max_num,
                    nas_max_num,
                    cdp_max,
                    desktop_max,
                    cpu_count,
                    storage_count,
                    node_count,
                    exchange_user_max_num,
                    hadoop_cluster_max_num,
                    obs_max_num,
                    k8s_max_num,
                    days,
                    trial_type,
                    software_type,
                    user_name,
                    extension ,
                    private_cloud_instance_max_num,
                    public_cloud_instance_max_num,
                    client_max,
                    client_replication_max_num,
                    db_replication_max_num,
                    file_replication_max_num,
                    nas_replication_max_num
                FROM
                    bd_license";
        $data = $this->dbSelect($sql);
        $allAuthInfo = (new Auth)->getSystemAuthInfo()["data"];
        $tenantAuthInfo = $this->getAllTenantAuthInfo($tenantUuid);
        //备份系统的授权类型(6种)
        switch ($allAuthInfo['first_type']) {
            case 1: //普通容量 + 复制按数量
                if ($params['normal_quota_size'] != -1) {
                    //备份系统按容量授权，租户也按容量（不是无限配额）需计算storage_count是否足够
                    if (intval($params['normal_quota_size']) + intval($tenantAuthInfo['normal_storage_size']) > ($allAuthInfo['module_info']['capacity']['total'] - $allAuthInfo['module_info']['capacity']['used'])) {
                        exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_CAPACITY'), xphp_get_lang('UI_PLATFORM_SPACE_NOT_ENOUGH'), "warning"));
                    }
                }
                break;
            case 2: //实时和定时容量分开（不支持实时，暂时只有定时） + 复制按数量
                if ($params['time_quota_size'] != -1) {
                    //备份系统按容量授权，租户也按容量（不是无限配额）需计算storage_count是否足够
                    if (intval($params['time_quota_size']) + intval($tenantAuthInfo['time_storage_size']) > $allAuthInfo['all_storage_capacity']['avail']) {
                        exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_CAPACITY'), xphp_get_lang('UI_PLATFORM_SPACE_NOT_ENOUGH'), "warning"));
                    }
                }
                break;
            case 3:
                //虚拟机的授权
                switch ($data[0]['license_type']) {
                    case 1: //宿主机个数授权
                        if (intval($params['vm_num']) + intval($tenantAuthInfo['vm_num']) > intval($allAuthInfo['module_info']['vm']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_HOST_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                    case 2: //CPU个数授权
                        if (intval($params['vm_num']) + intval($tenantAuthInfo['vm_num']) > intval($allAuthInfo['module_info']['vm']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_CPU_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                    case 4: //虚拟机个数授权
                        if (intval($params['vm_num']) + intval($tenantAuthInfo['vm_num']) > intval($allAuthInfo['module_info']['vm']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_VM_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                }
                switch ($allAuthInfo['second_type']) {
                    case 1: //全按数量
                        if (intval($params['aws_num']) + intval($tenantAuthInfo['aws_num']) > intval($allAuthInfo['module_info']['cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_AWS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['ops_num']) + intval($tenantAuthInfo['ops_num']) > intval($allAuthInfo['module_info']['private_cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_OPS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['file_num']) + intval($tenantAuthInfo['file_num']) > intval($allAuthInfo['module_info']['file']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('UI_PLATFORM_FAGENT_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['db_num']) + intval($tenantAuthInfo['db_num']) > intval($allAuthInfo['module_info']['oracle']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('UI_PLATFORM_DBAGENT_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['os_num']) + intval($tenantAuthInfo['os_num']) > intval($allAuthInfo['module_info']['os']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_CLIENT_OS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['nas_num']) + intval($tenantAuthInfo['nas_num']) > intval($allAuthInfo['module_info']['nas']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_NAS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num']) + intval($tenantAuthInfo['m365_num']) > intval($allAuthInfo['module_info']['exchange']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num_online']) + intval($tenantAuthInfo['m365_num_online']) > intval($allAuthInfo['module_info']['exchange_online']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['hadoop_num']) + intval($tenantAuthInfo['hadoop_num']) > intval($allAuthInfo['module_info']['hadoop']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_HADOOP_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['obs_num']) + intval($tenantAuthInfo['obs_num']) > intval($allAuthInfo['module_info']['obs']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_HADOOP_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['k8s_num']) + intval($tenantAuthInfo['k8s_num']) > intval($allAuthInfo['module_info']['k8s']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_KUBE_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                    case 2: //文件、数据库、整机按客户端数量，其余模块按数量
                        if (intval($params['aws_num']) + intval($tenantAuthInfo['aws_num']) > intval($allAuthInfo['module_info']['cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_AWS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['ops_num']) + intval($tenantAuthInfo['ops_num']) > intval($allAuthInfo['module_info']['private_cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_OPS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['agent_num']) + intval($tenantAuthInfo['agent_num']) > intval($allAuthInfo['module_info']['file']['avail_des'])) {//文件、数据库、整机中任意一个的值都可以
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_AGENT_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['nas_num']) + intval($tenantAuthInfo['nas_num']) > intval($allAuthInfo['module_info']['nas']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_NAS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num']) + intval($tenantAuthInfo['m365_num']) > intval($allAuthInfo['module_info']['exchange']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num_online']) + intval($tenantAuthInfo['m365_num_online']) > intval($allAuthInfo['module_info']['exchange_online']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['hadoop_num']) + intval($tenantAuthInfo['hadoop_num']) > intval($allAuthInfo['module_info']['hadoop']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_HADOOP_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['obs_num']) + intval($tenantAuthInfo['obs_num']) > intval($allAuthInfo['module_info']['obs']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_HADOOP_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['k8s_num']) + intval($tenantAuthInfo['k8s_num']) > intval($allAuthInfo['module_info']['k8s']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_KUBE_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                    case 3: //文件系列按容量，其余模块按数量
                        if ($params['file_quota_size'] != -1) {
                            //备份系统按容量授权，租户也按容量（不是无限配额）需计算storage_count是否足够
                            if (intval($params['file_quota_size']) + intval($tenantAuthInfo['file_storage_size']) > $allAuthInfo['file_capacity']['avail']) {
                                exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_CAPACITY'), xphp_get_lang('UI_PLATFORM_SPACE_NOT_ENOUGH'), "warning"));
                            }
                        }
                        if (intval($params['aws_num']) + intval($tenantAuthInfo['aws_num']) > intval($allAuthInfo['module_info']['cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_AWS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['ops_num']) + intval($tenantAuthInfo['ops_num']) > intval($allAuthInfo['module_info']['private_cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_OPS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['db_num']) + intval($tenantAuthInfo['db_num']) > intval($allAuthInfo['module_info']['oracle']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('UI_PLATFORM_DBAGENT_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['os_num']) + intval($tenantAuthInfo['os_num']) > intval($allAuthInfo['module_info']['os']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_CLIENT_OS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num']) + intval($tenantAuthInfo['m365_num']) > intval($allAuthInfo['module_info']['exchange']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num_online']) + intval($tenantAuthInfo['m365_num_online']) > intval($allAuthInfo['module_info']['exchange_online']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['k8s_num']) + intval($tenantAuthInfo['k8s_num']) > intval($allAuthInfo['module_info']['k8s']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_KUBE_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                    case 4: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
                        if ($params['file_quota_size'] != -1) {
                            //备份系统按容量授权，租户也按容量（不是无限配额）需计算storage_count是否足够
                            if (intval($params['file_quota_size']) + intval($tenantAuthInfo['file_storage_size']) > $allAuthInfo['file_capacity']['avail']) {
                                exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_CAPACITY'), xphp_get_lang('UI_PLATFORM_SPACE_NOT_ENOUGH'), "warning"));
                            }
                        }
                        if (intval($params['aws_num']) + intval($tenantAuthInfo['aws_num']) > intval($allAuthInfo['module_info']['cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_AWS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['ops_num']) + intval($tenantAuthInfo['ops_num']) > intval($allAuthInfo['module_info']['private_cloud']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_OPS_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['agent_num']) + intval($tenantAuthInfo['agent_num']) > intval($allAuthInfo['module_info']['oracle']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_AGENT_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num']) + intval($tenantAuthInfo['m365_num']) > intval($allAuthInfo['module_info']['exchange']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['m365_num_online']) + intval($tenantAuthInfo['m365_num_online']) > intval($allAuthInfo['module_info']['exchange_online']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_M365_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        if (intval($params['k8s_num']) + intval($tenantAuthInfo['k8s_num']) > intval($allAuthInfo['module_info']['k8s']['avail_des'])) {
                            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_KUBE_AUTH_NOT_ENOUGH'), "warning"));
                        }
                        break;
                }
                break;
        }
        if (intval($params['machinecopy_num']) + intval($tenantAuthInfo['machinecopy_num']) > intval($allAuthInfo['module_info']['copy_machine']['avail_des'])) {
            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_HADOOP_AUWEB_TENANT_CLIENT_REPLICATION_AUTH_NOT_ENOUGHH_NOT_ENOUGH'), "warning"));
        }
        if (intval($params['dbcdpcopy_num']) + intval($tenantAuthInfo['dbcdpcopy_num']) > intval($allAuthInfo['module_info']['copy_db']['avail_des'])) {
            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_DB_REPLICATION_AUTH_NOT_ENOUGH'), "warning"));
        }
        if (intval($params['nascopy_num']) + intval($tenantAuthInfo['nascopy_num']) > intval($allAuthInfo['module_info']['copy_nas']['avail_des'])) {
            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_NAS_REPLICATION_AUTH_NOT_ENOUGH'), "warning"));
        }
        if (intval($params['filecopy_num']) + intval($tenantAuthInfo['filecopy_num']) > intval($allAuthInfo['module_info']['copy_fs']['avail_des'])) {
            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_AUTHOR_BY_NUM'), xphp_get_lang('WEB_TENANT_FILE_REPLICATION_AUTH_NOT_ENOUGH'), "warning"));
        }
    }

    /**
     * 获取所有租户授权情况
     * @param string $tenantUuid 租户uuid
     */
    public function getAllTenantAuthInfo($tenantUuid)
    {
        $sql = "select config from bd_tenant where tenant_uuid != ?";
        $data = $this->dbSelect($sql, array($tenantUuid));
        $normalStorageSize = 0;
        $timeStorageSize = 0;
        $fileStorageSize = 0;
        $vm = 0;
        $agent = 0;
        $aws = 0;
        $ops = 0;
        $file = 0;
        $db = 0;
        $os = 0;
        $nas = 0;
        $m365 = 0;
        $m365online = 0;
        $obs = 0;
        $hadoop = 0;
        $k8s = 0;
        $machinecopy = 0;
        $filecopy = 0;
        $nascopy = 0;
        $dbcdpcopy = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $config = json_decode($d['config'], true);
                $authWay = intval($config['auth_way']);
                switch ($authWay) {
                    case 1: //普通容量 + 复制按数量
                        if ($config['normal_quota_size'] != -1) {
                            $normalStorageSize += intval($config['normal_quota_size']);
                        }
                        break;
                    case 2: //实时和定时容量分开（不支持实时，暂时只有定时） + 复制按数量
                        if ($config['time_quota_size'] != -1) {
                            $timeStorageSize += intval($config['time_quota_size']);
                        }
                        break;
                    case 3: //全按数量
                        $vm += intval($config['vm_num']);
                        $aws += $config['aws_num'] ? intval($config['aws_num']) : 0;
                        $ops += $config['ops_num'] ? intval($config['ops_num']) : 0;
                        $file += intval($config['file_num']);
                        $db += intval($config['db_num']);
                        $os += intval($config['os_num']);
                        $nas += intval($config['nas_num']);
                        $m365 += intval($config['m365_num']);
                        $m365online += intval($config['m365_online_num']);
                        $obs += $config['obs_num'] ? intval($config['obs_num']) : 0;//因为是后面加的字段，需要兼容处理
                        $hadoop += $config['hadoop_num'] ? intval($config['hadoop_num']) : 0;
                        $k8s += $config['k8s_num'] ? intval($config['k8s_num']) : 0;
                        break;
                    case 4: //文件、数据库、整机按客户端数量，其余模块按数量
                        $vm += intval($config['vm_num']);
                        $agent += $config['agent_num'] ? intval($config['agent_num']) : 0;
                        $aws += $config['aws_num'] ? intval($config['aws_num']) : 0;
                        $ops += $config['ops_num'] ? intval($config['ops_num']) : 0;
                        $nas += intval($config['nas_num']);
                        $m365 += intval($config['m365_num']);
                        $m365online += intval($config['m365_online_num']);
                        $obs += $config['obs_num'] ? intval($config['obs_num']) : 0;//因为是后面加的字段，需要兼容处理
                        $hadoop += $config['hadoop_num'] ? intval($config['hadoop_num']) : 0;
                        $k8s += $config['k8s_num'] ? intval($config['k8s_num']) : 0;
                        break;
                    case 5: //文件系列按容量，其余模块按数量
                        if ($config['file_quota_size'] != -1) {
                            $fileStorageSize += intval($config['file_quota_size']);
                        }
                        $vm += intval($config['vm_num']);
                        $aws += $config['aws_num'] ? intval($config['aws_num']) : 0;
                        $ops += $config['ops_num'] ? intval($config['ops_num']) : 0;
                        $db += intval($config['db_num']);
                        $os += intval($config['os_num']);
                        $m365 += intval($config['m365_num']);
                        $m365online += intval($config['m365_online_num']);
                        $k8s += $config['k8s_num'] ? intval($config['k8s_num']) : 0;
                        break;
                    case 6: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
                        if ($config['file_quota_size'] != -1) {
                            $fileStorageSize += intval($config['file_quota_size']);
                        }
                        $vm += intval($config['vm_num']);
                        $agent += $config['agent_num'] ? intval($config['agent_num']) : 0;
                        $aws += $config['aws_num'] ? intval($config['aws_num']) : 0;
                        $ops += $config['ops_num'] ? intval($config['ops_num']) : 0;
                        $m365 += intval($config['m365_num']);
                        $m365online += intval($config['m365_online_num']);
                        $k8s += $config['k8s_num'] ? intval($config['k8s_num']) : 0;
                        break;

                }
                $machinecopy += $config['machinecopy_num'] ? intval($config['machinecopy_num']) : 0;
                $filecopy += $config['filecopy_num'] ? intval($config['filecopy_num']) : 0;
                $nascopy += $config['nascopy_num'] ? intval($config['nascopy_num']) : 0;
                $dbcdpcopy += $config['dbcdpcopy_num'] ? intval($config['dbcdpcopy_num']) : 0;
            }
        }
        $info = array(
            'normal_storage_size' => $normalStorageSize,
            'time_storage_size' => $timeStorageSize,
            'file_storage_size' => $fileStorageSize,
            'vm_num' => $vm,
            'agent_num' => $agent,
            'aws_num' => $aws,
            'ops_num' => $ops,
            'file_num' => $file,
            'db_num' => $db,
            'os_num' => $os,
            'nas_num' => $nas,
            'm365_num' => $m365,
            'm365_online_num' => $m365online,
            'obs_num' => $obs,
            'hadoop_num' => $hadoop,
            'k8s_num' => $k8s,
            'machinecopy_num' => $machinecopy,
            'filecopy_num' => $filecopy,
            'nascopy_num' => $nascopy,
            'dbcdpcopy_num' => $dbcdpcopy,
        );
        return $info;
    }
    /**
     * 得到恢复的宿主机
     * @param unknown $params ['type'] 虚拟化类型
     */
    public function getRecoverHost($params)
    {
        $tenantFlag = $params['tenantFlag'];    //租户处
        $editFlag = $params['editFlag'];
        $checked_nodes = $params['checked_nodes'];//修改选中的宿主机
        $hypersior = intval($params['type']);   //虚拟化类型
        //获取当前用户所有拥有虚拟机
        $vcenterList = array();
        $subModule = in_array($hypersior, xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'])
            ? xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD']
            : xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        $vmList = \app\v2\resources\v0\logic\Index::instance()->pGetUserResourceVM(xphp_get_user_info()['userUuid'], "tree_id", "desc", $limit = 0, "all", [], $subModule);
        foreach ($vmList['data'] as $vm) {
            //获取分配资源所给的虚拟化中心
            if (!in_array($vm['vcenter_uuid'], $vcenterList)) {
                $vcenterList[] = $vm['vcenter_uuid'];
            }
        }
        $sql = "select vv.user_uuid, vv.vcenter_uuid, vv.vcenter_ip, vv.nickname, vv.vcenter_flag, vv.hypervisor_type, vv.vcenter_name, vv.detail, vv.username from vm_vcenter vv 
        LEFT JOIN mt_user_tenant mut on vv.user_uuid = mut.user_uuid where online_flag = ? ";
        // 非租户用户不能查看租户的资源
        $user = xphp_get_user_info();
        if (empty($user['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL ";
        }
        $sqlParams = array(xphp_get_config('app', 'FLAG')['SET']);
        $sql .= " order by hypervisor_type ";
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();
        $tenantVcenter = [];
        $allVcenterFlag = false;  //租户内是否运行恢复到全部宿主机
        //如果是租户内用户操作，检测是否已配置指定虚拟化中心
        if (!empty(xphp_get_user_info()['tenantuuid'])) {
            $tenantFlag = true;
            $settings = $this->pGetTenantSettings(xphp_get_user_info()['tenantuuid']);
            if ($settings['recover']) {
                $tenantVcenter = $settings['recover']['vcenter'];
                //                $allVcenterFlag = $settings['recover']['hosttype'] == "0";
            }
        }
        $recoveryHypervisorArr = VmRecover::instance()->getVMRecoveryHypervisorArr($hypersior);
        foreach ($data as $d) {
            //指定虚拟化中心
//            if(!empty(xphp_get_user_info()['tenantuuid']) && !$allVcenterFlag){
//                if($d['vcenter_uuid'] != $tenantVcenter) continue;
//            }
            if (is_string($tenantVcenter)) {
                $tenantVcenter = [$tenantVcenter];
            }
            if (!empty($_SESSION['tenantuuid'])) {
                if (!in_array($d['vcenter_uuid'], $tenantVcenter))
                    continue;
            }
            //针对全局用户，虚拟化中心不属于该用户,也不是分配的资源直接排除
            if (empty($_SESSION['tenantuuid']) && xphp_get_user_info()['userUuid'] != $d['user_uuid'] && !in_array($d['vcenter_uuid'], $vcenterList))
                continue;
            if (!in_array($d['hypervisor_type'], $recoveryHypervisorArr) && !$tenantFlag) {
                //如果目标虚拟化未授权,不能恢复到这个虚拟化,跳过
                //TODO
                continue;
            }
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                "name" => VmRecover::instance()->getRecoverHostName($d['hypervisor_type'], $d),
                //                "open" => false,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => intval($d['hypervisor_type']),
                "type" => 1,
                "iconSkin" => VmPlatform::instance()->getHypervisorIcon($d['hypervisor_type'], false)
            );
            $tree[] = $node;
            if ($editFlag) {//修改租户
                $params = array(
                    'edit_flag' => true,
                    'checked_nodes' => $checked_nodes,
                    'id' => $node['id'],
                    'pid' => $node['hypervisor'],
                    'nocheck' => false,
                    'refresh' => false
                );
                $sonNode = $this->getSyncRecoveryVcenter($params);
                $tree = array_merge($tree, $sonNode);
            }
        }
        return $tree;
    }

    /**
     * 公共方法
     * 获取租户对应的高级配置信息
     * @param string $tenantuuid
     * @return array|mixed
     */
    public function pGetTenantSettings($tenantuuid)
    {
        $sql = "select config from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $settings = array();
        if (!empty($data)) {
            $settings = json_decode($data[0]['config'], true);
        }
        return $settings;
    }

    /**
     * 得到备份存储
     * @param unknown $params
     */
    public function getStorage($params)
    {
        $storageList = array();
        $sql = "select node_uuid from bd_node";
        $data = $this->dbSelect($sql, array());
        if (!empty($data)) {
            foreach ($data as $d) {
                $storageList = array_merge($storageList, Node::instance()->getBackupStorageList(array('node_uuid' => $d['node_uuid'])));
            }
        }
        return $storageList;
    }
    /**
     * 获取恢复到的宿主机
     * 异步获取vcenter信息:先刷新,在从数据库获取
     * @param unknown $params
     * @return string
     */
    public function getSyncRecoveryVcenter($params)
    {
        $checkedNodes = array();
        if ($params['edit_flag']) {
            $checkedNodes = $params['checked_nodes'];    //获取修改选中的
        }
        $id = $params['id'];
        $pid = intval($params['pid']);
        $nocheck = $params['nocheck'];
        $refresh = $params['refresh'];
        //说明一下,此处pid刚好是子模块号
        $submodule_type = $pid;
        if (in_array($submodule_type, xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])) {
            //如果是VMware
            $showtype = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_VM'];
            //         }elseif($submodule_type == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XENSERVER']){
        } else {
            //如果是XenServer
            $showtype = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
        }
        if ($refresh) {
            //如果是刷新,刷新后再从数据库取
            // $opName = 'VM_VCENTER_OP_REFLASH';
            $mbResult = $this->refleshVcenter($submodule_type, $id, $showtype);
            $result = $mbResult['result'];
            if (!$result) {
                //如果刷新失败,返回错误消息
                // $operate = $this->opcodeHandler->getOpcodeDes($opName);
                $operate = xphp_get_lang('WEB_VM_VCENTER_SYNC');
                return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
            }
        }
        $vmTreeTypes = [xphp_get_config('vm', 'VM_TREE_TYPE')['DATACENTER'], xphp_get_config('vm', 'VM_TREE_TYPE')['CLUSTER'], xphp_get_config('vm', 'VM_TREE_TYPE')['HOST']];
        //获取虚拟化类型，hyperv/smartx需特殊处理
        $sql = "select hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $hypervisor = $this->dbSelect($sql, [$id])[0]['hypervisor_type'];
        if (
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor
            || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SMARTX_KVM'] == $hypervisor
        ) {
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid, vh.host_name, vh.host_ip, vh.host_uuid, vh.host_id, vh.authorization_flag, vh.online_flag
                from vm_tree vt left join vm_host vh on vt.uuid = vh.host_uuid and vt.type = ? and vt.vcenter_uuid = vh.vcenter_uuid 
                where vt.vcenter_uuid = ? and vt.display_mode = 1 and vt.type in (" . implode(",", $vmTreeTypes) . ") order by vt.type";
            $data = $this->dbSelect($sql, [xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'], $id]);
        } else {
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid, vh.host_name, vh.host_ip, vh.host_uuid, vh.host_id, vh.authorization_flag, vh.online_flag
                from vm_tree vt left join vm_host vh on vt.name = vh.host_name and vt.type = ? and vt.vcenter_uuid = vh.vcenter_uuid 
                where vt.vcenter_uuid = ? and vt.host_uuid != vt.vcenter_uuid and vt.display_mode = 1 and vt.type in (" . implode(",", $vmTreeTypes) . ") order by vt.type";
            $data = $this->dbSelect($sql, [xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'], $id]);

            // 树形结构没有主机层则清空不显示
            $hostFlag = false;
            foreach ($data as $item) {
                if (xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'] == $item['type']) {
                    $hostFlag = true;
                    break;
                }
            }
            if (!$hostFlag) {
                $data = [];
            }
            $sql = "select 4 as type, host_name as name, host_uuid as uuid, vcenter_uuid as parent_uuid, 
            host_name, host_ip, host_uuid, host_id, authorization_flag, online_flag from vm_host where vcenter_uuid = ?";
            $dataHost = $this->dbSelect($sql, array($id));
            $hostUuids = array_column($data, 'host_uuid');
            foreach ($dataHost as $host) {
                if (!in_array($host['host_uuid'], $hostUuids)) {
                    // 不在树形结构中的主机单独显示
                    $data[] = $host;
                }
            }
        }
        $node = array();
        $tenantHost = [];
        //如果是租户内用户操作，检测是否已配置指定宿主机
        if (!empty($_SESSION['tenantuuid'])) {
            $settings = $this->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['host_list']) {
                $tenantHost = $settings['host_list'];
            }
        }
        foreach ($data as $d) {
            if ($d['type'] != 4) {
                continue;
            }
            //指定宿主机
            if (!empty($_SESSION['tenantuuid']) && !empty($tenantHost)) {
                if (!in_array($d['host_uuid'], $tenantHost))
                    continue;
            }
            if (!$d['host_uuid']) {
                $d['host_uuid'] = $d['uuid'];
            }

            $name = $d['host_ip'] ?: $d['name'];
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'] == $d['type']) {
                if (in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])) {
                    // 私有云分组
                    $d['online_flag'] = xphp_get_config('app', 'FLAG')['SET'];
                    $name = $d['name'];
                }
                //添加离线/在线,授权/未授权标志,先检查在线/离线状态,在线的时候再检查授权/未授权标志
                if ($d['online_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    //在线
                    if ($d['authorization_flag'] != xphp_get_config('app', 'FLAG')['SET']) {
                        $name = $d['host_ip'] . "(" . xphp_get_lang('WEB_SYSTEM_UNAUTHIORIZED') . ")";
                    }
                    if ($submodule_type == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV']) {
                        $name = $d['host_name'];
                        if ($d['authorization_flag'] != xphp_get_config('app', 'FLAG')['SET']) {
                            $name = $name . "(" . xphp_get_lang('WEB_SYSTEM_UNAUTHIORIZED') . ")";
                        }
                    }
                } else {
                    //离线
                    $name = $d['host_ip'] . "(" . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ")";
                    //                 if($hideOffline) continue; //隐藏离线的
                    if ($submodule_type == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] || $submodule_type == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV']) {
                        $name = $d['host_name'] . "(" . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ")";
                    }
                }
            }
            //判断修改虚拟实验室选中宿主机
            $checked = false;
            if ($checkedNodes && in_array($d['host_uuid'], $checkedNodes)) {
                $checked = true;
            }
            $node[] = array(
                "id" => $d['host_uuid'] ?: $d['uuid'],
                "pid" => $id,
                "pId" => $id,
                "name" => $name,
                "isParent" => xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'] != $d['type'],
                "sid" => $d['host_id'],
                "nocheck" => $nocheck,
                "type" => 2,
                "ip" => $d['host_ip'],
                "hypervisor" => $pid,
                "icon" => "./img/vm/host.png",
                "clickShow" => true,
                //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                "refresh" => false,
                "vcuuid" => $id,
                "chkDisabled" => $this->getOffLine($d['online_flag']),
                "hypervisor_des" => xphp_get_config('vm', 'VMHYPERVISORDES')[intval($pid)],
                "checked" => $checked,
                "online_flag" => intval($d['online_flag'])
            );
        }
        array_multisort(array_column($node, 'name'), SORT_ASC, $node);
        return $node;
    }

    /**
     * 如果主机离线禁用checkbox
     * @param int $online_flag
     */
    public function getOffline($online_flag)
    {
        if ($online_flag != xphp_get_config('app', 'FLAG')['SET']) {
            return true;
        }
        return false;
    }
    /**
     * 获取首页数据
     */
    public function getHomePageData()
    {
        $homePageInfo = TenantHomePage::instance()->getHomePageInfo(array('backup_days' => 7));
    }

    /**
     * 获取租户拥有用户列表
     * @param unknown $params
     */
    public function getTenantUserList($params)
    {
        $tenantUuid = $params['tenant_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        $sql = "select bu.user_uuid,bu.create_time, bu.user_name, bu.lock_flag from mt_user_tenant mut, bd_user bu where mut.user_uuid = bu.user_uuid and mut.tenant_uuid = ? ";
        $sqlParams = array($tenantUuid);
        //如果还有排序参数,则进行排序
        if (!empty($sort) && !empty($order)) {
            $sql .= " order by " . $sort . " " . $order;
        }
        if (!empty($offset) && !empty($limit)) {
            $sql .= " limit ?, ?";
            $sqlParams = array_merge($sqlParams, array($offset, $limit));
        }
        $sqlCount = "select count(bu.user_uuid) as total from mt_user_tenant mut, bd_user bu where mut.user_uuid = bu.user_uuid and mut.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, array($tenantUuid));
        $info = array(
            'rows' => array(),
            'total' => intval($dataCount[0]['total']),
        );
        $config = $this->pGetTenantSettings($tenantUuid);
        if (!empty($data)) {
            $quotaStr = '';
            switch ($config['auth_way']) {
                case 1://普通容量
                    $quotaStr = "normal";
                    break;
                case 2://实时和定时容量分开（不支持实时，暂时只有定时）
                    $quotaStr = "time";
                    break;
                case 5:
                case 6://文件系列按容量
                    $quotaStr = "file";
                    break;
            }
            foreach ($data as $d) {
                $quota = $config[$quotaStr . '_quota_size'] == -1 ? xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED') : $config[$quotaStr . '_quota_show_size'] . $config[$quotaStr . '_quota_show_unit'];
                $info['rows'][] = array(
                    'user_name' => $d['user_name'],
                    'backup_data' => v2_calSize($this->getEachTenantBackupData($d['user_uuid']), true) . '/' . $quota,
                    'lock_flag' => $d['lock_flag'],
                    'create_time' => $d['create_time'],
                    'used_backup_data' => v2_calSize($this->getEachTenantBackupData($d['user_uuid']), true),
                    'total_backup_data' => $quota,
                    'tenant_uuid' => $tenantUuid,
                );
            }
        }
        return $info;
    }

    /**
     * 获取单个租户下使用的数据量
     * @param $useruuid
     * @return void
     */
    public function getEachTenantBackupData($useruuid)
    {
        $sql = "select sum(write_size) as backup_size from bd_backup_timepoint where user_uuid = ? and task_type not in 
        (" . xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] . "," . xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] . "," . xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] . "," . xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH'] . ")";
        $data = $this->dbSelect($sql, array($useruuid));
        return intval($data[0]['backup_size']);
    }

    /**
     * 得到所有模块可用总数(可用-已经分配给其他租户的)
     */
    public function getModulesLisenceValid($params)
    {
        $allAuthInfo = (new Auth)->getSystemAuthInfo()["data"];
        $sysAuthInfo = $allAuthInfo["module_info"];
        $show_flag = [];
        $sysAuthInfo['show_flag'] = array_merge($show_flag, $allAuthInfo["backup_show_flag"], $allAuthInfo["copy_show_flag"], $allAuthInfo["cdp_show_flag"], $allAuthInfo["advance_show_flag"]);
        $tenantAuth = $this->getAllTenantAuthInfo($params['tenant_uuid']);
        $vmNum = intval($sysAuthInfo['vm']['total']) - intval($sysAuthInfo['vm']['used']) - intval($tenantAuth['vm_num']);
        $awsNum = intval($sysAuthInfo['cloud']['total']) - intval($sysAuthInfo['cloud']['used']) - intval($tenantAuth['aws_num']);
        $opsNum = intval($sysAuthInfo['private_cloud']['total']) - intval($sysAuthInfo['private_cloud']['used']) - intval($tenantAuth['ops_num']);
        $dbNum = intval($sysAuthInfo['oracle']['total']) - intval($sysAuthInfo['oracle']['used']) - intval($tenantAuth['db_num']);
        $osNum = intval($sysAuthInfo['os']['total']) - intval($sysAuthInfo['os']['used']) - intval($tenantAuth['os_num']);
        $nasNum = intval($sysAuthInfo['nas']['total']) - intval($sysAuthInfo['nas']['used']) - intval($tenantAuth['nas_num']);
        $exchangeNum = intval($sysAuthInfo['exchange']['total']) - intval($sysAuthInfo['exchange']['used']) - intval($tenantAuth['m365_num']);
        $exchangeOnlineNum = intval($sysAuthInfo['exchange_online']['total']) - intval($sysAuthInfo['exchange_online']['used']) - intval($tenantAuth['m365_online_num']);
        $fileNum = intval($sysAuthInfo['file']['total']) - intval($sysAuthInfo['file']['used']) - intval($tenantAuth['file_num']);
        $hadoopNum = intval($sysAuthInfo['hadoop']['total']) - intval($sysAuthInfo['hadoop']['used']) - intval($tenantAuth['hadoop_num']);
        $obsNum = intval($sysAuthInfo['obs']['total']) - intval($sysAuthInfo['obs']['used']) - intval($tenantAuth['obs_num']);
        $k8sNum = intval($sysAuthInfo['k8s']['total']) - intval($sysAuthInfo['k8s']['used']) - intval($tenantAuth['k8s_num']);
        $machinecopyNum = intval($sysAuthInfo['copy_machine']['total']) - intval($sysAuthInfo['copy_machine']['used']) - intval($tenantAuth['machinecopy_num']);
        $filecopyNum = intval($sysAuthInfo['copy_fs']['total']) - intval($sysAuthInfo['copy_fs']['used']) - intval($tenantAuth['filecopy_num']);
        $nascopyNum = intval($sysAuthInfo['copy_nas']['total']) - intval($sysAuthInfo['copy_nas']['used']) - intval($tenantAuth['nascopy_num']);
        $dbcdpcopyNum = intval($sysAuthInfo['copy_db']['total']) - intval($sysAuthInfo['copy_db']['used']) - intval($tenantAuth['dbcdpcopy_num']);
        $sysAuthInfo['vm']['valid'] = $vmNum < 0 ? 0 : $vmNum;
        $sysAuthInfo['agent']['valid'] = $dbNum < 0 ? 0 : $dbNum; //agent随便用客户端授权中的一个获取
        $sysAuthInfo['cloud']['valid'] = $awsNum < 0 ? 0 : $awsNum;
        $sysAuthInfo['private_cloud']['valid'] = $opsNum < 0 ? 0 : $opsNum;
        $sysAuthInfo['oracle']['valid'] = $dbNum < 0 ? 0 : $dbNum;
        $sysAuthInfo['os']['valid'] = $osNum < 0 ? 0 : $osNum;
        $sysAuthInfo['nas']['valid'] = $nasNum < 0 ? 0 : $nasNum;
        $sysAuthInfo['exchange']['valid'] = $exchangeNum < 0 ? 0 : $exchangeNum;
        $sysAuthInfo['exchange_online']['valid'] = $exchangeOnlineNum < 0 ? 0 : $exchangeOnlineNum;
        $sysAuthInfo['file']['valid'] = $fileNum < 0 ? 0 : $fileNum;
        $sysAuthInfo['hadoop']['valid'] = $hadoopNum < 0 ? 0 : $hadoopNum;
        $sysAuthInfo['obs']['valid'] = $obsNum < 0 ? 0 : $obsNum;
        $sysAuthInfo['k8s']['valid'] = $k8sNum < 0 ? 0 : $k8sNum;
        $sysAuthInfo['machinecopy']['valid'] = $machinecopyNum < 0 ? 0 : $machinecopyNum;
        $sysAuthInfo['filecopy']['valid'] = $filecopyNum < 0 ? 0 : $filecopyNum;
        $sysAuthInfo['nascopy']['valid'] = $nascopyNum < 0 ? 0 : $nascopyNum;
        $sysAuthInfo['dbcdpcopy']['valid'] = $dbcdpcopyNum < 0 ? 0 : $dbcdpcopyNum;
        return $sysAuthInfo;
    }


    /**
     * 得到所有的租户用户
     * @return unknown[]
     */
    public function getAllUserTenant()
    {
        $userTenantList = [];
        $sql = "select bu.user_uuid,user_name from bd_user bu inner join mt_user_tenant mut on bu.user_uuid = mut.user_uuid and mut.tenant_uuid IS NOT NULL";
        $userInfo = $this->dbSelect($sql);
        if (!empty($userInfo)) {
            foreach ($userInfo as $user) {
                $userTenantList[] = $user['user_uuid'];
            }
        }
        return $userTenantList;
    }

    /**
     * 绑定资源
     */
    public function toAddResource($adminUuid, $params)
    {
        //绑定存储
        $resourceInfo = array();
        if (!empty($params['storage_list'])) {
            foreach ($params['storage_list'] as $storage) {
                $resourceInfo[] = array(
                    'resourceuuid' => $storage,
                    'vmuuid' => '',
                    'vcenteruuid' => '',
                    'resourceType' => xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']
                );
            }
            //绑定节点
            $list = array_column($resourceInfo, 'resourceuuid');
            $listDes = implode("','", $list);
            //获取存储所在节点uuid
            $sql = "select distinct node_uuid from bd_storage_resource where storage_uuid in ('" . $listDes . "')";
            $dataNode = $this->dbSelect($sql);
            if (!empty($dataNode)) {
                foreach ($dataNode as $node) {
                    $resourceInfo[] = array(
                        'resourceuuid' => $node['node_uuid'],
                        'vmuuid' => '',
                        'vcenteruuid' => '',
                        'resourceType' => xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']
                    );
                }
            }
            //关联存储所在节点
            User::instance()->pAddUserResource($adminUuid, $resourceInfo);
        }
    }
    /**
     * 检查租户内可用数量是否足够
     * @param int $type 模块类型
     * @param array $info 备份对象数组
     * @param string $taskuuid 任务uuid,修改任务用
     * @param boolean $numFlag 是否返回数字,多租户首页显示用（默认为不返回数量只做检测）
     * @param string $tenantUuid 租户uuid
     * @param int $subType 子模块类型
     * @param array $userUuids 非租户的所有用户，用于获取非租户用户已用授权
     */
    public function checkTenantAuth($type, $info, $taskuuid = "", $numFlag = false, $tenantUuid = '', $subType = '', $userUuids = [])
    {
        $tenantUuid = $tenantUuid ? $tenantUuid : $_SESSION['tenantuuid'];
        $enoughFlag = true;//授权数量是否足够
        $settings = $this->pGetTenantSettings($tenantUuid);
        switch (intval($type)) {
            case xphp_get_config('module')['MODULE_TYPE']['VM']:
                switch (intval($subType)) {
                    case xphp_get_config('module', 'VM_SUB_MODULE')['VM']:
                        $dataInfo = v2_license_get_auth('vm', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['vm_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                    case xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD']:
                        $dataInfo = v2_license_get_auth('private_cloud', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['ops_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                    case xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']:
                        $dataInfo = v2_license_get_auth('cloud', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['aws_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['FS']:
                $dataInfo = v2_license_get_auth('file', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['file_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['DB']:
                $dataInfo = v2_license_get_auth('oracle', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['db_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['OS']:
                $dataInfo = v2_license_get_auth('os', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['os_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['NAS']:
                $dataInfo = v2_license_get_auth('nas', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['nas_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['M365']:
                switch ($subType) {
                    case 1://server
                        $dataInfo = v2_license_get_auth('exchange', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['m365_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                    case 2://online
                        $dataInfo = v2_license_get_auth('exchange_online', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['m365_online_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['OBS']:
                $dataInfo = v2_license_get_auth('obs', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['obs_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['HADOOP']:
                $dataInfo = v2_license_get_auth('hadoop', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['hadoop_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['KUBERNETES']:
                $dataInfo = v2_license_get_auth('k8s', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['k8s_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['VOL_CDP']://整机复制
                $dataInfo = v2_license_get_auth('copy_machine', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['machinecopy_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['FILE_COPY']:
                switch (intval($subType)) {//文件复制暂时只支持文件复制和nas复制
                    case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']:
                        $dataInfo = v2_license_get_auth('copy_fs', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['filecopy_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                    case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']:
                        $dataInfo = v2_license_get_auth('copy_nas', $info, $taskuuid, $tenantUuid);
                        //total为-1代表按数量授权无限制
                        if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                            $dataInfo['total'] = $settings['nascopy_num'];
                            $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                            $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                        }
                        break;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['DB_CDP']://数据库复制
                $dataInfo = v2_license_get_auth('copy_db', $info, $taskuuid, $tenantUuid);
                //total为-1代表按数量授权无限制
                if ($dataInfo['auth_type'] == 1 && $dataInfo['total'] != -1) {//按数量授权
                    $dataInfo['total'] = $settings['dbcdpcopy_num'];
                    $dataInfo['avail'] = $dataInfo['total'] - $dataInfo['used'];
                    $enoughFlag = $dataInfo['avail'] <= 0 ? false : true;
                }
                break;
        }
        //返回数据，多租户首页用
        if ($numFlag) {
            return $dataInfo;
        }
        if (!$enoughFlag) {//授权不足
            exit($this->muOpResult(false, xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK'), xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK_ERROR'), "warning"));
        }
        if (empty($settings['auth_way'])) {
            exit($this->muOpResult(false, xphp_get_lang('UI_PLATFORM_SYSAUTHOR_CHECK'), xphp_get_lang('UI_PLATFORM_TENANT_NOT_AUTH'), "warning"));
        }
        return true;
    }
    /**
     * 检查租户是否注册
     * @param string $params [username]
     * @return string
     */
    public function tenantNameAvailable($params)
    {
        $tenantName = $params['tenant_name'];
        $sql = "select id from bd_tenant where tenant_name = ?";
        $tenants = $this->dbSelect($sql, array($tenantName));
        return empty($tenants);
    }

    /**
     * 获取可以给租户分配的剩余容量，限容量授权
     */
    public function getSystemFreeStorage()
    {
        $allAuthInfo = (new Auth)->getSystemAuthInfo()["data"];
        if ($allAuthInfo['first_type'] == 1) {
            return $allAuthInfo['module_info']['capacity'];
        } else if ($allAuthInfo['first_type'] == 2) {
            return array(
                "time" => $allAuthInfo['all_storage_capacity'],
                "real_time" => $allAuthInfo['module_info']['vol_capacity'],
            );
        } else if ($allAuthInfo['first_type'] == 3) {
            if ($allAuthInfo['second_type'] == 3 || $allAuthInfo['second_type'] == 4) {
                return $allAuthInfo['file_capacity'];
            }
            if ($allAuthInfo['module_info']['cdp']['auth_type'] == 2) {
                return $allAuthInfo['all_storage_capacity'];
            }
        }
    }
}
