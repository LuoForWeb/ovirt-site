<?php

namespace app\v1\hadoop\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          hadoop 之任务信息 logic
 * @author       lilingyu@vinchin.com
 * @date         2023/11/29 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取当前任务的某个任务的基本信息
     */
    public function getBasicInfo()
    {
        $info = $this->logic()->getBasicInfo($this->param);  // 逻辑层转发
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error('', $info);
    }

     /**
     * 获取当前任务的某个任务的任务流量
     * @return 返回任务基本信息
     */
    public function getTaskSpeed()
    {
        $return = $this->logic()->getTaskSpeed($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }


     /**
     * 获取集群列表表格详情信息
     * @return 返回任务基本信息
     */
    public function getHadoopDetail()
    {
        $return = $this->logic()->getHadoopDetail($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }
    /**
     * 启动备份任务
     * @return null
     */
    public function startHadoopBackupJob()
    {
        $return = $this->logic()->startHadoopBackupJob($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }
    /**
     * 下载跳过文件
     * @return null
     */
    public function downLoadPassFile()
    {
        $result = $this->logic()->downLoadPassFile($this->param);
        return $result;
    }

}
