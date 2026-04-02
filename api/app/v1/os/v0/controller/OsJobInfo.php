<?php

namespace app\v1\os\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          操作系统 -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsJobInfo extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取瞬时恢复任务详细信息
     */
    public function getInstantOSRecoverJobInfo(){
        $info = $this->logic()->getInstantOSRecoverJobInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取迁移任务详细信息
     */
    public function getOSMotionJobInfo(){
        $info = $this->logic()->getOSMotionJobInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


    
    /**
     * 获取主机列表详细信息
     */
    public function getOSDetailList(){
        $info = $this->logic()->getOSDetailList($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

}
