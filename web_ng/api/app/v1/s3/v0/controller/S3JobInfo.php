<?php

namespace app\v1\s3\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          对象存储保护 - 任务详情
 * @auther       chengjiafu@vinchin.com
 * @date         2023/11/27 11:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class S3JobInfo extends AuthBase {
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取任务基本信息
     * @return null
     */
    public function getBasicInfo() {
        $return = $this->logic()->getBasicInfo($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }

    /**
     * 获取任务流量信息
     * @return null
     */
    public function getTaskSpeed() {
        $return = $this->logic()->getTaskSpeed($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }

    /**
     *
     * @return null
     */
    public function getTaskDetail() {
        $return = $this->logic()->getTaskDetail($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }


}