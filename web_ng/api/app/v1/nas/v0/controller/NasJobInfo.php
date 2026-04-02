<?php

namespace app\v1\nas\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          NAS -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasJobInfo extends AuthBase
{
    /**
     * 构造方法
     */
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
     * @return void
     */
    public function getNasHistory()
    {
        $this->checkParams('nas_job_history');

        $return = $this->logic()->getNasHistory($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }
}