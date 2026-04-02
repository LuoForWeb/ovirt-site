<?php

namespace app\v1\exchange\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          office365（exchange） -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeRecover extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取组织和任务备份数据
     * @return object 组织和任务备份数据
     */
    public function getRestoreData()
    {
        $data = $this->logic()->getRestoreData($this->param);
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_INFO_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_INFO_ERROR'), $data);
        }
    }

    /**
     * 获取某个任务下的恢复点
     * @return object 某个任务下的恢复点
     */
    public function getRestorePoints()
    {
        $data = $this->logic()->getRestorePoints($this->param);
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_INFO_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_INFO_ERROR'), $data);
        }
    }

    /**
     * 获取时间点下的用户列表/获取用户下的详情列表
     * @return object 用户列表/用户下的详情列表
     */
    public function getRestoreUsers()
    {
        $param = $this->param;
        if (!empty($param['user_uuid'])) {//详情列表
            switch (intval($param['dir_type'])) {
                case 4://得到顶级目录
                    $data = $this->logic()->getRootDir($param);
                    break;
                case 5://得到子目录
                    $data = $this->logic()->getChildDir($param);
                    break;
                case 6://得到元数据
                    $data = $this->logic()->getMetadata($param);
                    break;
                case 7://得到目录下的元数据详细信息
                    $data = $this->logic()->getMetadataDetail($param);
                    break;
            }
        } else {//用户列表
            $data = $this->logic()->getRestoreUsers($param);
        }
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_INFO_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_INFO_ERROR'), $data);
        }
    }

    /**
     * 导出压缩包
     * @return object 压缩包信息
     */
    public function getRestoreZip()
    {
        $data = $this->logic()->getRestoreZip($this->param);
        if (!empty($data)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_EXPORT_COMPRESS_FILE_SUCCESS'), $data);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_EXPORT_COMPRESS_FILE_ERROR'), $data);
        }
    }

    /**
     * 导出压缩包中的数据
     * @return object 压缩包中的数据
     */
    public function getRestoreZipData()
    {
        $data = $this->logic()->getRestoreZipData($this->param);
    }

    /**
     * 发送邮件（单个/批量发送）
     * @return object 发送邮件的结果
     */
    public function sendRestoreEmail()
    {
        $data = $this->logic()->sendRestoreEmail($this->param);
        if ($data['code'] == 0) {
            $this->success($data['msg']);
        }
        $this->error($data['msg']);
    }

    /**
     * 创建恢复任务
     * @return string 创建的结果
     */
    public function createRecoverJob()
    {
        $result = $this->logic()->createRecoverJob($this->param);
        if ($result) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_CREATE_RECOVERY_TASK_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_CREATE_RECOVERY_TASK_ERROR'), $result);
        }
    }

    /**
     * 获取恢复任务名
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getRestoreTaskName($params = [])
    {
        $result = $this->logic()->getRestoreTaskName($params);
        if ($result) {
            $this->success('', $result);
        }
        $this->error('', $result);
    }

    /**
     * 高级搜索
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getSearchInfo($params = [])
    {
        $result = $this->logic()->getSearchInfo($this->param);
        if ($result) {
            return $this->success('', $result);
        }
        return $this->error('', $result);
    }

    /**
     * 普通全文搜索
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getNormalSearch($params = [])
    {
        $result = $this->logic()->getNormalSearch($this->param);
        if ($result) {
            return $this->success('', $result);
        }
        return $this->error('', $result);
    }

    /**
     * 恢复时间点身份验证--server
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getAuthResult($params = [])
    {
        $result = $this->logic()->getAuthResult($this->param);
        if ($result) {
            return $this->success('', $result);
        }
        return $this->error('', $result);
    }
}
