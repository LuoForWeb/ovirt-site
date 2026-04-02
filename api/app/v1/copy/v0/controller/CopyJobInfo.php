<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 副本容灾 --- 副本任务信息控制
 * @Date: 2023-08-22 10:30:59
 * @LastEditTime: 2023-09-22 11:47:36
 * @Version: 1.0
 * @copyright: Copyright 2023 vinchin.com
 */

namespace app\v1\copy\v0\controller;

use app\v1\common\controller\AuthBase;

class CopyJobInfo extends AuthBase
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getCopyBasicInfo()
    {
        $info = $this->logic()->getCopyBasicInfo($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getCopyDetails()
    {
        $info = $this->logic()->getCopyDetails($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getCopyHistory()
    {
        $info = $this->logic()->getCopyHistory($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getHighInfo()
    {
        $info = $this->logic()->getHighInfo($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getCopyTransferInfo()
    {
        $info = $this->logic()->getCopyTransferInfo($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getTaskSpeed()
    {
        $info = $this->logic()->getTaskSpeed($this->param);
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
}
