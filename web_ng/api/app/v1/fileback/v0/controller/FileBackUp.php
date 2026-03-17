<?php

namespace app\v1\fileback\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件备份
 * @author       wuxian@vinchin.com
 * @date         2025/7/14 16:15
 * @copyright    Copyright 2025 vinchin.com
 */
class FileBackUp extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取备份任务文件备份树
     * @return json
     */
    public function getAgentGroupBackupTree()
    {
        // 1 参数验证
        // $this->checkParams('files_agent_backup_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getAgentGroupBackupTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 创建备份任务
     * @return json
     */
    public function createBackupJob()
    {
         $this->checkParams('files_backup_job_add');
        $return = $this->logic()->createBackupJob($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 修改备份任务
     * @return json
     */
    public function editBackupJob()
    {
        // 1 参数验证
        $this->checkParams('files_backup_job_edit');

        // 2 请求转发到logic去处理
        $return = $this->logic()->editBackupJob($this->param);
        // 3 返回结果到用户
        if ($return[0]) {
            $this->success('', $return[1]);
        }
        $this->error($return[1]);
    }

    /**
     * 获取任务基本信息
     * @return json
     */
    public function getBackupTaskAllInfo()
    {
        // 1 参数验证
        $this->checkParams('files_info');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getBackupTaskAllInfo($this->param['info_uuid']);

        // 3 返回结果到用户
        $this->success('', $return);
    }


    /**
     * 代理端文件子树
     * @return json
     */
    public function getFileDirSonTree()
    {
        // 1 参数验证
        $this->checkParams('files_agent_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getFileDirSonTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到修改文件备份任务客户端树
     * @return json
     */
    public function getBackupTreeOldInfo()
    {
        // 1 参数验证
        $this->checkParams('files_backup_old_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getBackupTreeOldInfo($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到文件备份任务名
     * @return string
     */
    public function getFileBackupTaskName()
    {
        $return = $this->logic()->getFileBackupTaskName($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

     /**
     * 获取备份源-客户端、nas设备、Hadoop集群、对象存储
     * @return object
     */
    public function getBackupSource()
    {
        $return = $this->logic()->getBackupSource($this->param);
        $this->success('', $return);
    }


    /**
     * 获取文件目录
     * @return object
     */
     /**
     * 代理端文件树
     * @return json
     */
    public function getFileDirTree()
    {
        // 1 参数验证
        // $this->checkParams('files_agent_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getFileDirTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }
}
