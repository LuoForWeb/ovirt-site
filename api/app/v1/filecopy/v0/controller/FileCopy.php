<?php

namespace app\v1\filecopy\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件同步 -- 备份
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FileCopy                                                                                                        extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取客户端、nas设备、hadoop集群、对象存储信息
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getResourceInfo()
    {
        $result = $this->logic()->getResourceInfo($this->param);
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_GET_RESOURCE_INFO'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_GET_RESOURCE_INFO'), $result);
    }

    /**
     * 获取修改同步任务选中的源和目标信息
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function editResourceInfo($params = [])
    {
        $result = $this->logic()->editResourceInfo($this->param);
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_EDIT_RESOURCE_INFO'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_EDIT_RESOURCE_INFO'), $result);
    }

    /**
     * 创建文件同步任务
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function createFileCopyJob()
    {
        $result = $this->logic()->createFileCopyJob($this->param);
        if (empty($this->param['job_uuid'])) { //备份
            if ($result) {
                $this->success(xphp_get_lang('UI_FILE_COPY_CREATE_JOB'), $result);
            }
            $this->error(xphp_get_lang('UI_FILE_COPY_CREATE_JOB'), $result);
        } else {//修改
            if ($result) {
                $this->success(xphp_get_lang('UI_FILE_COPY_EDIT_JOB'), $result);
            }
            $this->error(xphp_get_lang('UI_FILE_COPY_EDIT_JOB'), $result);
        }
        
    }

    /**
     * 绝对路径访问
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function accessByAbsolutePath($params = [])
    {
        $result = $this->logic()->accessByAbsolutePath($this->param);
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_ABOSOLUTE_ACCESS'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_ABOSOLUTE_ACCESS'), $result);
    }


}
