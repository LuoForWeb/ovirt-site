<?php

namespace app\v1\user\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          用户组管理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:10
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Group extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户组列表
     * @return json
     */
    public function getGroupList()
    {
        if (!empty($this->param['usergroups_uuid'])) {
            // 获取详情
            $this->checkParams('get'); // 参数验证
            $records = $this->logic()->getGroup($this->param['usergroups_uuid']);
            $this->success('', $records);
        }

        // 参数验证
        $this->checkParams('list');

        if (!empty($this->param['type']) && $this->param['type'] == 1) {
            // 获取分配列表
            $records = $this->logic()->getUserGroupList($this->param);
        } else {
            // 获取列表
            $records = $this->logic()->getGroupList($this->param);
        }

        $this->success('', $records);
    }

    /**
     * 新建用户组
     * @return josn
     */
    public function addGroup()
    {
        // 参数验证
        $this->checkParams('add');

        $result = $this->logic()->addGroup($this->param);

        if ($result) {
            $this->success(xphp_get_lang('UI_USER_GROUP_ADD'));
        }

        $this->error(xphp_get_lang('UI_USER_GROUP_ADD'));
    }

    /**
     * 编辑用户组
     * @return json
     */
    public function editGroup()
    {
        // 参数验证
        $this->checkParams('edit');

        $result = $this->logic()->editGroup($this->param);

        if ($result) {
            $this->success(xphp_get_lang('UI_USER_GROUP_MODIFY'));
        }

        $this->error(xphp_get_lang('UI_USER_GROUP_MODIFY'));
    }

    /**
     * 解锁用户组
     * @return json
     */
    public function unlockGroup()
    {
        // 参数验证
//        $this->checkParams('lock');

        $result = $this->logic()->unlockGroup($this->param);

        if ($result) {
            $this->success(xphp_get_lang('UI_USER_GROUP_ENABLE_SUCCESS'),$result);
        }
        $this->error(xphp_get_lang('UI_USER_GROUP_ENABLE_FAIL'),$result);
    }

    /**
     * 锁定用户组
     * @return json
     */
    public function lockGroup()
    {
        // 参数验证
//        $this->checkParams('lock');

        $result = $this->logic()->lockGroup($this->param);

        if ($result) {
            $this->success(xphp_get_lang('UI_USER_GROUP_DISABLE_SUCCESS'),$result);
        }
        $this->error(xphp_get_lang('UI_USER_GROUP_DISABLE_FAIL'),$result);
    }

    /**
     * 删除用户组
     * @return json
     */
    public function delGroup()
    {
        $result = $this->logic()->delGroup($this->param);

        if ($result) {
            $this->success(xphp_get_lang('UI_USER_GROUP_DELETE'));
        }

        $this->error(xphp_get_lang('UI_USER_GROUP_DELETE'));
    }

    /**
     * 加载关联用户
     */
    public function getUserGroupUser()
    {
        $result = $this->logic()->getUserGroupUser($this->param);
        $this->success('', $result);
    }

    /**
     * 加载关联角色
     */
    public function getUserGroupRole()
    {
        $result = $this->logic()->getUserGroupRole($this->param);
        $this->success('', $result);
    }

    /**
     * 加载关联资源组
     */
    public function initResourcegroup()
    {
        $result = $this->logic()->initResourcegroup($this->param);
        $this->success('', $result);
    }

    /**
     * 获取用户组权限树
     */
    public function getUserGroupPermissionTree()
    {
        $result = $this->logic()->getUserGroupPermissionTree($this->param);
        $this->success('', $result);
    }

    /**
     * 获取单个用户组所有信息
     */
    public function getOneUserGroupData()
    {
        $result = $this->logic()->getOneUserGroupData($this->param);
        $this->success('', $result);
    }
}
