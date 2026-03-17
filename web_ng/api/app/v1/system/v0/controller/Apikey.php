<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          生成apikey管理
 * @author       luokai@vinchin.com
 * @date         2023/10/7 16:09
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Apikey extends AuthBase
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 生成apikey
     * @return json
     */
    public function createApikey()
    {
        // 参数验证
//        $this->checkParams('add');

        $result = $this->logic()->createApikey($this->param);

        if (!$result) {
            $this->error();
        }
        $this->success('', $result['data']);
    }

    /**
     * 获取apikey列表
     * @return json
     */
    public function getApikeyList()
    {
        $records = $this->logic()->getApikeyList($this->param);

        $this->success('', $records);
    }

    /**
     * 删除apikey
     * @return json
     */
    public function deleteApikey()
    {
        // 参数验证

        // 逻辑层转发
        $return = $this->logic()->deleteApikey($this->param);

        if ($return) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 禁用apikey
     * @return json
     */
    public function lockApikey()
    {
        // 参数验证

        // 逻辑层转发
        $return = $this->logic()->lockApikey($this->param);

        if ($return) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 启用apikey
     * @return json
     */
    public function unlockApikey()
    {
        // 参数验证

        // 逻辑层转发
        $return = $this->logic()->unlockApikey($this->param);

        if ($return) {
            $this->success();
        }
        $this->error();
    }


}
