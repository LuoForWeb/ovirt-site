<?php

namespace app\v2\vm\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          虚拟机概览
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmOverview extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取所有虚拟机个数
     * @return void
     */
    public function getAllVms()
    {
        $this->checkParams('count');
        $data = $this->logic()->getAllVms($this->param);
        $this->success('', $data);
    }

    /**
     * 获取虚拟机概览列表
     * @return void
     */
    public function getVMReport()
    {
        $this->checkParams('list');
        $data = $this->logic()->getVMReport($this->param);
        $this->success('', $data);
    }

    /**
     * 获取已添加虚拟化中心列表
     * @return void
     */
    public function getVcenterList(): string
    {
        $data = $this->logic()->getVcenterList($this->param);
        $this->success('', $data);
    }
}
