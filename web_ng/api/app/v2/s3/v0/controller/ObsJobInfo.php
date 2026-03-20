<?php

namespace app\v2\s3\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          对象存储保护 - 任务详情
 * @auther       chengjiafu@vinchin.com
 * @date         2023/11/27 11:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取任务基本信息
     * @return null
     */
    public function getBasicInfo()
    {
        $return = $this->logic()->getBasicInfo($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }

    /**
     * 获取任务流量信息
     * @return null
     */
    public function getTaskSpeed()
    {
        $return = $this->logic()->getTaskSpeed($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }

    /**
     * 获取任务详情
     * @return null
     */
    public function getTaskDetail()
    {
        $return = $this->logic()->getTaskDetail($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }

    /**
     * 获取历史任务
     * @return null
     */
    public function getObsHistory()
    {
        $this->checkParams('obs_job_history');

        $return = $this->logic()->getObsHistory($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }

    /**
     * 启动备份任务
     * @return null
     */
    public function startObsBackupJob()
    {
        $this->checkParams('obs_backup_job_start');

        $return = $this->logic()->startObsBackupJob($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }

    /**
     * @return mixed
     */
    public function downLoadPassFile()
    {
        // 参数验证
        $this->checkParams('files_download_pass');

        // 逻辑层转发
        return $this->logic()->downLoadPassFile($this->param);
    }

}