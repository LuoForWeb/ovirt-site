<?php

namespace app\v1\cloud\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * Class CloudJobInfo
 * @package app\v1\cloud\v0\controller
 */
class CloudJobInfo extends AuthBase
{
    /**
     * 获取任务的实例列表
     * @return string
     */
    public function getJobInstances(): string
    {
        //$this->checkParams('getJobInstances');
        $data = $this->logic()->getJobInstances($this->param);
        $this->success('', $data);
    }

    /**
     * 获取任务详情基本信息
     * @return string
     */
    public function getJobBasicInfo(): string
    {
        $this->checkParams('getJobBasicInfo');
        $data = $this->logic()->getBasicInfo($this->param);
        $this->success('', $data);
    }
}
