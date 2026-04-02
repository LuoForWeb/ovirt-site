<?php

namespace app\v1\cloud\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * Class CloudRecover
 * @package app\v1\cloud\v0\controller
 */
class CloudRecover extends AuthBase
{
    /**
     * 获取存储列表
     * @return string
     */
    public function getStorageList(): string
    {
        $data = $this->logic()->getStorageList();
        $this->success('', $data);
    }

    /**
     * 创建恢复任务
     * @return string
     */
    public function createRecoverJob(): string
    {
        $this->checkParams('createJob');
        return $this->logic()->createRecoverJob($this->param);
    }
}
