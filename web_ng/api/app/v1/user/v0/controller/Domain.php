<?php

namespace app\v1\user\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          用户组管理类
 * @author       ZHENGXIANGQIN@vinchin.com
 * @date         2024/4/16 11:17
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */

class Domain extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取域服务器列表
     * @return json
     */
    public function getDomainList()
    {
        // 参数验证
//        $this->checkParams('list');
        // 获取列表
        $records = $this->logic()->getDomainList($this->param);
        $this->success('', $records);
    }

    /**
     * 新建域服务器
     * @return json
     */
    public function addDomainServer()
    {
        // 参数验证
//        $this->checkParams('list');

        $records = $this->logic()->addDomainServer($this->param);
        $this->success('', $records);
    }

    /**
     * 修改域服务器
     * @return json
     */
    public function editDomainServer()
    {
        // 参数验证
//        $this->checkParams('list');

        $records = $this->logic()->editDomainServer($this->param);
        $this->success('', $records);
    }

    /**
     * 删除域服务器
     * @return json
     */
    public function deleteDomainServer()
    {
        // 参数验证
//        $this->checkParams('list');

        $records = $this->logic()->deleteDomainServer($this->param);
        $this->success('', $records);
    }

    /**
     * 删除域服务器
     * @return json
     */
    public function getDomainSelectList()
    {
        $records = $this->logic()->getDomainSelectList($this->param);
        $this->success('', $records);
    }

    /**
     *
     */
    public function getOldDomainServerInfo()
    {
        $records = $this->logic()->getOldDomainServerInfo($this->param);
        $this->success('', $records);
    }

}