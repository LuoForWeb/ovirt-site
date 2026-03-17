<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          黑白名单管理
 * @author       zhengxiangqin@vinchin.com
 * @date         2023/11/20 14:21
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */

class WBList extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 展示黑/白名单列表
     */
    public function getWBList()
    {
        $result = $this->logic()->getWBList($this->param);
        if(!$result)
        {
            $this->error();
        }
        $this->success('',$result);
    }


    /**
     * 添加黑/白名单
     * @return json
     */
    public function addWBList()
    {
        $result = $this->logic()->addWBList($this->param);
        if(!$result)
        {
            $this->error();
        }
        $this->success($result['msg'], $result['data']);
    }

    /**
     * 修改黑/白名单
     * @return json
     */
    public function editWBList()
    {
        $result = $this->logic()->editWBList($this->param);
        if(!$result)
        {
            $this->error();
        }
        $this->success($result['msg']);
    }

    /**
     * 删除黑/白名单
     * @return json
     */
    public function deleteWBlist()
    {
        $result = $this->logic()->deleteWBlist($this->param);
        if(!$result){
            $this->error();
        }
        $this->success();
    }

    /**
     * 启用名单
     * @return json
     */
    public function unlockWBList()
    {
        $result = $this->logic()->unlockWBList($this->param);
        if(!$result){
            $this->error('',$result);
        }
        $this->success('',$result);
    }

    /**
     * 锁定名单
     * @return json
     */
    public function lockWBList()
    {
        $result = $this->logic()->lockWBList($this->param);
        if(!$result){
            $this->error('',$result);
        }
        $this->success('',$result);
    }

    /**
     * 添加IP时候确保没重复
     */
    public function compareList()
    {
        $result = $this->logic()->compareList($this->param);
        if(!$result){
            $this->error();
        }
        $this->success();
    }

}