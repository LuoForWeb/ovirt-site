<?php

namespace app\v1\nas\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          NAS -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasBackUp extends AuthBase
{
    /**
     * 得到所有挂载成功节点
     * @return json
     */
    public function getAllMountNode()
    {
        // 1 参数验证
        $this->checkParams('nas_mount_node');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getAllMountNode($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到所有挂载成功节点
     * @return json
     */
    public function getNasBackupTree()
    {

        // 请求转发到logic去处理
        $return = $this->logic()->getNasBackupTree($this->param);

        //  返回结果到用户
        $this->success('', $return);
    }

    /**
     * 代理端文件子树
     * @return json
     */
    public function getNasSonTree()
    {
        // 1 参数验证
        $this->checkParams('nas_agent_sontree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getNasSonTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到备份任务的所有信息
     * @return json
     */
    public function getBackupTaskAllInfo()
    {
        // 1 参数验证
        $this->checkParams('nas_get_backup_info');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getBackupTaskAllInfo($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到修改nas备份任务nas设备树
     * @return json
     */
    public function getBackupTreeOldInfo()
    {
        // 1 参数验证
        $this->checkParams('nas_backup_old_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getBackupTreeOldInfo($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 创建备份任务
     * @return json
     */
    public function createBackupJob()
    {
        // 1 参数验证
        $this->checkParams('nas_backup_job_add');

        // 2 请求转发到logic去处理
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
        $this->checkParams('nas_backup_job_edit');

        // 2 请求转发到logic去处理
        $return = $this->logic()->editBackupJob($this->param);
        // 3 返回结果到用户
        if ($return[0]) {
            $this->success('', $return[1]);
        }
        $this->error($return[1]);
    }
}