<?php

namespace app\v1\user\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          用户角色管理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/8 15:33
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Role extends AuthBase
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取角色列表
     * @return json
     */
    public function getRoleList()
    {
        if (!empty($this->param['roles_uuid'])) {
            // 获取详情
            $this->checkParams('get'); // 参数验证
            $records = $this->logic()->getRole($this->param['roles_uuid']);
            $this->success('', $records);
        }

        // 参数验证
        $this->checkParams('list');

        if (!empty($this->param['type']) && $this->param['type'] == 1) {
            // 获取分配列表
            $records = $this->logic()->getRoleLists($this->param);
        } else {
            // 获取列表
            $records = $this->logic()->getRoleList($this->param);
        }

        $this->success('', $records);
    }

    /**
     * 获取角色信息列表为了其它调取而分配
     * @return json
     */
    public function getUserRoleList()
    {
        $info = $this->logic()->getRoleLists($this->param);

        $this->success('', $info);
    }

    /**
    * 获取权限树 编辑/新增 角色的时候会用
     * @return json
     */
    public function getUserPermission()
    {
        $info = $this->logic()->getUserPermission($this->param);

        $this->success('', $info);
    }

    /**
     * 新建角色
     * @return json
     */
    public function addRole()
    {
        // 参数验证
        $this->checkParams('add');

        $result = $this->logic()->addRole($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 获取角色信息
     * @return json
     */
    public function getRole()
    {
        // 参数验证
        $this->checkParams('get');

        $info = $this->logic()->getRole($this->param['roleuuid']);

        $this->success('', $info);
    }

    /**
     * 编辑角色
     * @return json
     */
    public function editRole()
    {
        // 参数验证
        $this->checkParams('edit');

        $result = $this->logic()->editRole($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 解锁角色
     * @return json
     */
    public function unlockRole()
    {
        // 参数验证
//        $this->checkParams('lock');

        $result = $this->logic()->unlockRole($this->param);
        if ($result){
            $this->success(xphp_get_lang('UI_ROLE_ENABLE_SUCCESS'),$result);
        }
        $this->error(xphp_get_lang('UI_ROLE_ENABLE_FAIL'),$result);
    }

    /**
     * 锁定角色
     * @return json
     */
    public function lockRole()
    {
        // 参数验证
//        $this->checkParams('lock');

        $result = $this->logic()->lockRole($this->param);
        if ($result){
            $this->success(xphp_get_lang('UI_ROLE_DISABLE_SUCCESS'),$result);
        }
        $this->error(xphp_get_lang('UI_ROLE_DISABLE_FAIL'),$result);
    }

    /**
     * 删除角色
     * @return json
     */
    public function delRole()
    {
        // 参数验证
//        $this->checkParams('lock');
        $result = $this->logic()->delRole($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
    * 初始化角色关联用户、用户组列表
     * @return json
     */
    public function initAllocationList()
    {
        // 参数验证
//        $this->checkParams('init');

        $info = $this->logic()->initAllocationList($this->param);

        $this->success('', $info);
    }

    /**
     * 添加角色和用户关联
     * @return json
     */
    public function addUserRoleAllocation()
    {
        // 参数验证
        $this->checkParams('init');

        $result = $this->logic()->addUserRoleAllocation($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 添加角色和用户组关联
     * @return json
     */
    public function addUsergroupRoleAllocation()
    {
        // 参数验证
        $this->checkParams('init');

        $result = $this->logic()->addUsergroupRoleAllocation($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 初始化角色关联用户和用户组列表
     * @return json
     */
    public function getUserList()
    {
        $records = $this->logic()->getUserList($this->param);
        $this->success('', $records);
    }

    /*
     * 初始化用户组列表
     * @return json
     */
    public function getUsergroupList()
    {
        $records = $this->logic()->getUsergroupList($this->param);
        $this->success('', $records);
    }

    /**
     * 取用户关联用户组
     * @return json
     */
    public function getRoleUser()
    {
        $records = $this->logic()->getRoleUser($this->param);
        $this->success('', $records);
    }

    /**
     * 加载关联用户组
     * @return json
     */
    public function getRoleUserGroup()
    {
        $records = $this->logic()->getRoleUserGroup($this->param);
        $this->success('', $records);
    }

    /**
     * 得到角色用来修改的信息
     * @return json
     */
    public function getRoleOldInfo()
    {
        // 参数验证
        $this->checkParams('init');
        $records = $this->logic()->getRoleOldInfo($this->param);
        $this->success('', $records);
    }

    /**
     * 获取角色权限树
     * @return json
     */
    public function getRolePermissionTree()
    {
        $records = $this->logic()->getRolePermissionTree($this->param);
        $this->success('', $records);
    }
}
