<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\Base;

class S3 extends Base
{

    /**
     * 添加对象存储列表
     *
     * @return void
     */
    public function addOBStorage()
    {

        $return = $this->logic()->addOBStorage($this->param);

        $this->success('', $return);
    }

    /**
     * 获取对象存储列表
     *
     * @return void
     */
    public function getOBStroage()
    {
        $this->checkParams('lists');

        $return = $this->logic()->getOBStorageList($this->param);

        $this->success('', $return);
    }

    /**
     * 获取已授权的对象存储
     * @return void
     */
    public function getObsAuthList()
    {
        $this->checkParams('lists');

        $return = $this->logic()->getObsAuthList($this->param);

        $this->success('', $return);
    }

    /**
     * 修改对象存储
     *
     * @return void
     */
    public function editOBStorage()
    {
        $return = $this->logic()->editOBStorage($this->param);

        $this->success('', $return);
    }

    /**
     * 删除对象存储
     * @return void
     */
    public function delOBStorage()
    {
        $return = $this->logic()->delOBStorage($this->param);

        $this->success('', $return);
    }

    /**
     * 刷新对象存储
     *
     * @return void
     */
    public function refreshOBStorage()
    {
        $return = $this->logic()->refreshOBStorage($this->param);

        $this->success('', $return);
    }

    /**
     * 获取对象存储自动刷新间隔
     *
     * @return void
     */
    public function getAutoRefreshInterval()
    {
        $return = $this->logic()->getAutoRefreshInterval();

        $this->success('', $return);
    }

    public function editAutoRefreshInterval()
    {
        $this->checkParams('refresh_interval');

        $return = $this->logic()->editAutoRefreshInterval($this->param);

        $this->success('', $return);
    }

    /**
     * 获取对象存储默认名
     * @return void
     */
    public function getObsDefaultName()
    {
        $return = $this->logic()->getObsDefaultName($this->param);

        $this->success('', $return);
    }

    /**
     * 获取对象存储授权信息
     *
     * @return void
     */
    public function getObsLisenceInfo()
    {
        $return = $this->logic()->getObsLisenceInfo();

        $this->success('', $return);
    }

    /**
     * 
     * 添加授权
     * @return void
     */
    public function addAuth()
    {
        $return = $this->logic()->addAuth($this->param);

        $this->success('', $return);
    }

    /**
     * 
     * 取消授权
     * @return void
     */
    public function removeAuth()
    {
        $return = $this->logic()->removeAuth($this->param);

        $this->success('', $return);
    }
}