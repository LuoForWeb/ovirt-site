<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2026-03-23 15:25:09
 * @Description: 
 * @version: 1.0
 */

namespace app\v2\tenant\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          租户管理首页
 * @author      liushuai@vinchin.com
 * @date         2024/4/17 16:20
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class TenantHomePage extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取首页布信息
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getHomePageInfo()
    {

        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->getHomePageInfo($this->param);
        if (!$info['success']) {
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }


}
