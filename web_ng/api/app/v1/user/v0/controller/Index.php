<?php

namespace app\v1\user\v0\controller;

use app\v1\common\controller\AuthBase;
use app\v1\resources\v0\logic\Storage;
use app\v1\system\v0\logic\Index as SystemIndex;

/**
 * note          用户管理类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:09
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends AuthBase
{
    /**
     * 获取用户列表 / 详情
     * @return json
     */
    public function getUser()
    {
        // 参数验证
        if (empty($this->param['users_uuid'])) {
            // 获取列表
            $this->checkParams('list');

            $records = $this->logic()->getUserList($this->param);
        } else {
            // 获取单个详情
            $this->checkParams('get');
            $records = $this->logic()->getUser($this->param['users_uuid']);
        }

        $this->success('', $records);
    }

    /**
    * 获取当前登录用户的密码
     * @return string
     */
    public function getUserPassword()
    {
        $return = $this->logic()->getUserPassword();
        $this->success('', $return);
    }

    /**
     * 新建用户
     * @return json
     */
    public function addUser()
    {
        // 参数验证
        $this->checkParams('add');

        //检查用户配额是否超出总配额
        $this->checkUserMaxStorage(intval($this->param['quota']), xphp_get_lang('WEB_USERS_ADD_USER'));

        $result = $this->logic()->addUser($this->param);

        if (!$result && !is_array($result)) {
            $this->error($result);
        } elseif (is_array($result) && !$result['success']) {
            $this->error($result['message'], $result, 0);
        }
        $this->success($result);
    }

    /**
     * 编辑用户
     * @return json
     */
    public function editUser()
    {
        // 参数验证
        $this->checkParams('edit');

        //检查用户配额是否超出总配额
        $this->checkUserMaxStorage(intval($this->param['quota']), xphp_get_lang('UI_PLATFORM_EDIT_USER'));

        $result = $this->logic()->editUser($this->param);

        if (!$result) {
            $this->error();
        }
        $this->success($result);
    }

    /**
     * 解锁用户
     * @return json
     */
    public function unlockUser()
    {
        // 参数验证
        $this->checkParams('lock');

        $result = $this->logic()->unlockUser($this->param);

        if (!$result) {
            $this->error(xphp_get_lang('UI_USER_ENABLE'));
        }

        $this->success(xphp_get_lang('UI_USER_ENABLE')  . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 锁定用户
     * @return json
     */
    public function lockUser()
    {
        // 参数验证
        $this->checkParams('lock');

        $result = $this->logic()->lockUser($this->param);

        if (!$result) {
            $this->error(xphp_get_lang('UI_USER_DISABLE'));
        }

        $this->success(xphp_get_lang('UI_USER_DISABLE') . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 删除用户
     * @return json
     */
    public function delUser()
    {
        if (!empty($this->param['users_uuid'])) {
            // 表示删除单个用户
            $this->param['users'] = [$this->param['users_uuid']];
        } else {
            $this->param['users'] = $this->param['users'];
        }

        // 参数验证
        $this->checkParams('del');

        $result = $this->logic()->delUser($this->param);

        if (!$result) {
            $this->error(xphp_get_lang('WEB_USERS_DELETE_USER') . xphp_get_lang('WEB_ERROR_BD_GENERIC_ERROR'));
        }
        $this->success(xphp_get_lang('WEB_USERS_DELETE_USER') . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 获取当前登录用户的历史登录信息
     * @return json
     */
    public function getLoginHistorys()
    {
        $return = $this->logic()->getLoginHistorys();
        $this->success('', $return);
    }

    /**
     * 获取当前登录用户的关联管理用户列表
     * @return json
     */
    public function getAuthLists()
    {
        $return = $this->logic()->getAuthLists();
        $this->success('', $return);
    }

    /**
     * 获取用户资源/组列表
     * @return json
     */
    public function getResource()
    {
        // 参数验证
        $this->checkParams('users_resource');

        $result = $this->logic()->getResource($this->param);

        $this->success('', $result);
    }

    /**
     * 获获取用户权限列表
     * @return json
     */
    public function getRoles()
    {
        // 参数验证
        $this->checkParams('users_roles');

        $result = $this->logic()->getRoles($this->param);

        $this->success('', $result);
    }

    /**
     * 查看分配管理用户
     * @return json
     */
    public function getManager()
    {
        // 参数验证
        $this->checkParams('users_manager_get');

        $result = $this->logic()->getManager($this->param);

        $this->success('', $result);
    }

    /**
     * 分配管理用户
     * @return json
     */
    public function doManager()
    {
        // 参数验证
        $this->checkParams('users_manager_post');

        $result = $this->logic()->doManager($this->param);

        if ($result) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 权限资源列表
     * @return json
     */
    public function getAuth()
    {

        $result = $this->logic()->getAuth($this->param);

        $this->success('', $result);
    }

    /**
     * 用户资源分配
     * @return json
     */
    public function doAllocation()
    {

        // 参数验证
        $this->checkParams('users_allocation_post');

        $result = $this->logic()->doAllocation($this->param);

        $result ? $this->success() : $this->error(xphp_get_lang('UI_RESOURCE_NODE'));
    }

    /**
     * 用户资源/组删除
     * @return json
     */
    public function delAllocation()
    {

        // 参数验证
        $this->checkParams('users_allocation_del');

        $result = $this->logic()->delAllocation($this->param);

        $result ? $this->success() : $this->error();
    }

    /**
     * 用户资源转移
     * @return json
     */
    public function doTransfer()
    {

        // 参数验证
        $this->checkParams('users_transfer');

        $result = $this->logic()->doTransfer($this->param);

        $result ? $this->success() : $this->error();
    }

    /**
     * 分配用户角色权限
     * @return json
     */
    public function doUserRole()
    {

        // 参数验证
        $this->checkParams('users_roles_post');

        $result = $this->logic()->doUserRole($this->param);

        $result ? $this->success() : $this->error();
    }

    /**
     * 验证用户名是否存在
     * @return json
     */
    public function checkUser()
    {
        // 参数验证
        $this->checkParams('users_check');

        $result = $this->logic()->checkUser($this->param);

        $this->success('', $result);
    }

    /**
     * 检查用户配额是否超出限制
     * @param int    $quota   配额大小
     * @param string $operate 操作描述
     * @return json
     */
    private function checkUserMaxStorage(int $quota, string $operate)
    {
        $maxStorageInfo = (new Storage())->getMaxStorage();
        if ($maxStorageInfo['svalue'] != -1 && $quota > $maxStorageInfo['svalue']) {
            $this->muOpResult(false, $operate, xphp_get_lang('UI_PLATFORM_SIZE_GT_TATAL_REDISTRIBUTE'), 'warning');
        }
        return true;
    }

    /**
     * 获取授权的细节功能
     * @return void
     */
    public function getAuthFunc()
    {
        $data = SystemIndex::instance()->getExtensionLicense();
        $this->success('', $data['f']);
    }

    /**
     * 验证用户名和邮箱
     */
    public function userInfoVerify()
    {
        $result = $this->logic()->userInfoVerify($this->param);
        if ($result['result'] == 1) {
            $this->success($result['message'], $result);
        }
        $this->error($result['message'], $result);
    }

    /**
     * 重置密码
     */
    public function resetPassWord()
    {
        $result = $this->logic()->resetPassWord($this->param);
        $this->success('', $result);
    }

    /**
     * 获取密码复杂度
     */
    public function getPassComplexity()
    {
        $result = $this->logic()->getPassComplexity($this->param);
        $this->success('', $result);
    }

    /**
     * 获取旧密码
     */
    public function oldpassAvailable()
    {
        $result = $this->logic()->oldpassAvailable($this->param);
        $this->success('', $result);
    }

    /**
     * 修改密码
     */
    public function editPassword()
    {
        $result = $this->logic()->editPassword($this->param);
        $this->success('', $result);
    }

    /**
     * 获取租户下用的角色
     */
    public function getRoleList()
    {
        $result = $this->logic()->getRoleList($this->param);
        $this->success('', $result);
    }

    /**
     * 根据传递来的permission数组组装成树返回
     */
    public function getUserPermissionRole()
    {
        $result = $this->logic()->getUserPermissionRole($this->param);
        $this->success('', $result);
    }

    /**
     * 获取用户组列表用于分配
     */
    public function getUserGroupList()
    {
        $result = $this->logic()->getUserGroupList($this->param);
        $this->success('', $result);
    }

    /**
     * 用户自己修改资料
     */
    public function editSelfInfo()
    {
        $result = $this->logic()->editSelfInfo($this->param);
        $this->success('', $result);
    }

    /**
     * 验证用户名是否存在
     */
    public function usernameExist()
    {
        $this->checkParams('user_name_check');
        $result = $this->logic()->usernameExist($this->param);
        $this->success('', $result);
    }

    /**
     * 初始化个人信息
     */
    public function getSelfInfo()
    {
        $result = $this->logic()->getUserSelfInfo();
        $this->success('', $result);
    }

    /**
     * 根据用户类型得到用户操作权限
     */
    public function getUserPermission()
    {
        $result = $this->logic()->getUserPermission();
        $this->success('', $result);
    }

    /**
     * 添加用户组和文件代理关联关系
     */
    public function addUserGroupFileHost()
    {
        $result = $this->logic()->addUserGroupFileHost($this->param);
        $this->success('', $result);
    }

    /**
     * 验证用户密码是否正确
     * @return json
     */
    public function checkUserPassword()
    {
        $result = $this->logic()->checkUserPassword($this->param);
        if ($result['code'] != 0) {
            return $this->error($result['msg']);
        }
        return $this->success($result['msg']);
    }

    /**
     * 验证用户密码是否正确
     * @return json
     */
    public function checkUserAuth()
    {
        $result = $this->logic()->checkUserAuth($this->param);
        if (!$result) {
            return $this->error(xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'));
        }
        return $this->success();
    }

    /**
     * 获取旧独立密码
     */
    public function oldCustomePassAvailable()
    {
        $result = $this->logic()->oldCustomePassAvailable($this->param);
        $this->success('', $result);
    }

    /**
     * 修改独立密码
     */
    public function editCustomepass()
    {
        $result = $this->logic()->editCustomepass($this->param);
        if (!$result) {
            $this->error(xphp_get_lang('UI_USER_EDIT_CUSTOME_PASSWORD') . xphp_get_lang('WEB_ERROR_BD_GENERIC_ERROR'));
        }
        $this->success(xphp_get_lang('UI_USER_EDIT_CUSTOME_PASSWORD') . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }
}
