<?php

namespace app\v1\k8s\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          容器 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sRecover extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 创建备份任务 demo
     */
    public function createRecoveryJob(){
        $info = $this->logic()->createRecoveryJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
}
