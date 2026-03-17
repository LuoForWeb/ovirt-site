<?php

namespace app\v1\exchange\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          office365（exchange） -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeBackUp extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取备份任务的组织树信息
     * @return object 组织相关信息
     */
    public function getOrganizationInfo()
    {
        $data = $this->logic()->getOrganizationInfo($this->param);
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_ORGANIZATION_INFO_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_ORGANIZATION_INFO_ERROR'), $data);
        }
    }

    /**
     * 获取备份任务的组织下的用户信息
     * @return object 用户相关信息
     */
    public function getUserInfo()
    {
        $data = $this->logic()->getUserInfo($this->param);
        return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_USER_INFO_SUCCESS'), $data);
    }

    /**
     * 修改备份任务的组织树信息
     * @return object 修改备份任务的组织树信息
     */
    public function editOrganizationInfo()
    {
        $param = $this->param;
        if (!empty($param['organization_uuid'])) {
            $data = $this->logic()->getUserList($this->param);
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_USER_INFO_SUCCESS'), $data);
        } else {
            $data = $this->logic()->getOrganizationInfo($this->param);
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_ORGANIZATION_INFO_ERROR'), $data);
        }
    }

    /**
     * 修改任务,得到备份任务的所有信息
     * @return void 备份任务的所有信息
     */
    public function getBackupTaskInfo()
    {
        $data = $this->logic()->getBackupTaskInfo($this->param);
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_BACKUP_TASK_INFO_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_BACKUP_TASK_INFO_ERROR'), $data);
        }
    }

    /**
     * 创建备份任务
     * @return string 创建的结果
     */
    public function createBackupJob()
    {
        $result = $this->logic()->createBackupJob($this->param);
        if ($result) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_CREATE_BACKUP_TASK_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_CREATE_BACKUP_TASK_ERROR'), $result);
        }
    }

    /**
     * 修改备份任务
     * @return string 修改的结果
     */
    public function editBackupJob()
    {
        $result = $this->logic()->editBackupJob($this->param);
        if ($result) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_EDIT_BACKUP_TASK_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_EDIT_BACKUP_TASK_ERROR'), $result);
        }
    }

    /**
     * 获取备份任务名
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getBackupTaskName($params = [])
    {
        $result = $this->logic()->getBackupTaskName($this->param);
        if ($result) {
            $this->success('', $result);
        }
        $this->error('', $result);
    }

    /**
     * 获取告警信息
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getM365Alarm($params = [])
    {
        $result = $this->logic()->getM365Alarm($this->param);
        if ($result) {
            $this->success(xphp_get_lang('WEB_M365_SERVER_GET_ALARM_INFO_SUCCESS'), $result);
        }
        $this->error(xphp_get_lang('WEB_M365_SERVER_GET_ALARM_INFO_ERROR'), $result);
    }

     /**
     * 获获取组织下本次要备份的用户数
     * @return int
     */
    public function getM365UserNum() {
        $result = $this->logic()->getM365UserNum($this->param);
        if ($result) {
            $this->success('', $result);
        }
        $this->error('', $result);
    }
}
