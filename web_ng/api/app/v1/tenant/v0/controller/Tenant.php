<?php

namespace app\v1\tenant\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          租户管理
 * @author      wuxian@vinchin.com
 * @date         2024/4/16 16:20
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Tenant extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加租户
     * @return array|mixed
     * @author wuxian@vinchin.com
     */
    public function addTenant()
    {
        $return = $this->logic()->addTenant($this->param);
        if ($return['result']) {
            return $this->success(xphp_get_lang('UI_TENANT_ADD') . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
    }
        return $this->error(xphp_get_lang('UI_TENANT_ADD') .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }

    /**
     * 修改租户
     * @param string $tenantUuid 租户uuid
     * @return array
     * @author wuxian@vinchin.com
     */
    public function editTenant()
    {
        $return = $this->logic()->editTenant($this->param);
        if ($return['result']) {
            return $this->success(xphp_get_lang('UI_TENANT_MODIFY') . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
		}
		 return $this->error(xphp_get_lang('UI_TENANT_MODIFY') .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }

 	/**
     * 获取租户需要修改的信息
     * @param string $tenantUuid 租户uuid
     * @return array
     * @author wuxian@vinchin.com
     */
    public function getTenantEditInfo()
    {
        $return = $this->logic()->getTenantEditInfo($this->param);
        if (!empty($return)) {
            return $this->success(xphp_get_lang('WEB_TENANT_GET_INFO') . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
        }
        return $this->error(xphp_get_lang('WEB_TENANT_GET_INFO') .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }

    /**
     * 删除租户
     * @param string $tenantUuid 租户uuid
     * @return array
     * @author wuxian@vinchin.com
     */
    public function deleteTenant()
    {
        $result = $this->logic()->deleteTenant($this->param);
        if (!$result) {
            $this->error(xphp_get_lang('UI_TENANT_DELETE') .xphp_get_lang('WEB_PUBLIC_FAILURE'));
        }

        $this->success(xphp_get_lang('UI_TENANT_DELETE') . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 启用租户
     * @param string $tenantUuid 租户uuid
     * @return array
     * @author wuxian@vinchin.com
     */
    public function enableTenant()
    {
        $result = $this->logic()->enableTenant($this->param);
        if (!$result) {
            $this->error(xphp_get_lang('UI_TENANT_ENABLE') . xphp_get_lang('WEB_PUBLIC_FAILURE'));
        }

        $this->success(xphp_get_lang('UI_TENANT_ENABLE') .xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 禁用租户
     * @param string $tenantUuid 租户uuid
     * @return array
     * @author wuxian@vinchin.com
     */
    public function disableTenant()
    {
        $result = $this->logic()->disableTenant($this->param);
        if (!$result) {
            $this->error(xphp_get_lang('UI_TENANT_DISABLE') . xphp_get_lang('WEB_PUBLIC_FAILURE'));
        }
        $this->success(xphp_get_lang('UI_TENANT_DISABLE') .xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 租户信息
     * @return array|mixed
     * @author wuxian@vinchin.com
     */
    public function getTenant()
    {
        // 参数验证
        if (empty($this->param['tenant_uuid'])) {
            // 获取列表
//            $this->checkParams('list');

            $records = $this->logic()->getTenantList($this->param);
        } else {
            // 获取单个详情
//            $this->checkParams('get');
            $records = $this->logic()->getTenantDetail($this->param['tenant_uuid']);
        }

        $this->success('', $records);
    }

    /**
     * 恢复目的地
     * @return array|mixed
     * @author wuxian@vinchin.com
     */
    public function getRecoverHost()
    {
        // 参数验证
        $records = $this->logic()->getRecoverHost($this->param);
        if (!$records) {
            $this->error(xphp_get_lang('WEB_TENANT_GET_RECOVERY_DES') .xphp_get_lang('WEB_PUBLIC_FAILURE'));
        }
        $this->success(xphp_get_lang('WEB_TENANT_GET_RECOVERY_DES') . xphp_get_lang('WEB_PUBLIC_SUCCESS'), $records);
    }

    /**
     * 得到备份存储
     */
    public function getStorage(){
        $records = $this->logic()->getStorage($this->param);
        if (!$records) {
            $this->error(xphp_get_lang('WEB_TENANT_GET_STORAGE') .xphp_get_lang('WEB_PUBLIC_FAILURE'));
        }
        $this->success(xphp_get_lang('WEB_TENANT_GET_STORAGE') . xphp_get_lang('WEB_PUBLIC_SUCCESS'), $records);
    }

    /**
     * 获取恢复到的宿主机
     * 异步获取vcenter信息:先刷新,在从数据库获取
     * @return string
     */
    public function getSyncRecoveryVcenter(){
        $records = $this->logic()->getSyncRecoveryVcenter($this->param);
        if (!$records) {
            $this->error(xphp_get_lang('WEB_TENANT_GET_HOST_ASYN') .xphp_get_lang('WEB_PUBLIC_FAILURE'));
        }
        $this->success(xphp_get_lang('WEB_TENANT_GET_HOST_ASYN') . xphp_get_lang('WEB_PUBLIC_SUCCESS'), $records);
    }

    /**
     * 获取租户拥有用户列表
     * @param unknown $params
     */
    public function getTenantUserList(){
        $return = $this->logic()->getTenantUserList($this->param);
        if (!empty($return)) {
            return $this->success(xphp_get_lang('API_CODE_USERS_GET_USER_LIST') . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
        }
        return $this->error(xphp_get_lang('API_CODE_USERS_GET_USER_LIST') .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }

    /**
     * 获取所有模块可用数量
     * @param unknown $params
     */
    public function getModulesLisenceValid(){
        $return = $this->logic()->getModulesLisenceValid($this->param);
        if (!empty($return)) {
            return $this->success(xphp_get_lang('WEB_TENANT_GET_FREE_NUM') . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
        }
        return $this->error(xphp_get_lang('WEB_TENANT_GET_FREE_NUM') .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }
    /**
     * 检查租户是否注册
     * @param string $params 租户名
     * @return string
     */
    public function tenantNameAvailable(){
        $return = $this->logic()->tenantNameAvailable($this->param);
        if ($return) {
            return $this->success('' . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
        }
        return $this->error('' .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }
    /**
     * 获取可以给租户分配的剩余容量，限容量授权
     */
    public function getSystemFreeStorage(){
        $return = $this->logic()->getSystemFreeStorage();
        if (!empty($return)) {
            return $this->success('' . xphp_get_lang('WEB_PUBLIC_SUCCESS'),$return);
        }
        return $this->error('' .xphp_get_lang('WEB_PUBLIC_FAILURE'),$return);
    }

}
