<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\Base;

/**
 * note          nas设备管理 控制器
 * @author       wanggongxi@vinchin.com
 * @date         2023/5/22 16:43
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Nas extends Base
{
    /**
     * 获取nas存储设备列表
     * @return string
     */
    public function getNasInfo()
    {
        $this->checkParams('lists');

        $return = $this->logic()->getNasInfo($this->param);
        $this->success('', $return);
    }

    /**
     * 添加nas存储设备
     * @return string
     */
    public function addNasDevice()
    {
        //        $this->checkParams('add');
        $return = $this->logic()->addNasDevice($this->param);
        $this->success('', $return);
    }

    /**
     * 编辑nas存储设备
     * @return string
     */
    public function editNasDevice()
    {
        $return = $this->logic()->editNasDevice($this->param);

        if (!$return) {
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 检测是否修改了除了别名之外的信息
     * @return string
     */
    public function checkEditNasParams()
    {
        //        $this->checkParams('check');

        $return = $this->logic()->checkEditNasParams($this->param);
        if (!$return) {
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 删除nas设备 
     * @return string
     */
    public function delNasDevice()
    {
        $return = $this->logic()->delNasDevice($this->param);
        if (!$return) {
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * nas授权
     * @return string
     */
    public function nasLisence()
    {
        $return = $this->logic()->nasLisence($this->param);
        if (!$return) {
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     *挂载、解挂
     * @param unknown $params
     * @return string
     */
    public function opreateNas()
    {
        $return = $this->logic()->opreateNas($this->param);
        if (!$return) {
            $this->error();
        }
        $this->success('', $return);
    }
}
