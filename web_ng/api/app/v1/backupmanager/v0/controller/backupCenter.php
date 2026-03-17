<?php

namespace app\v1\backupmanager\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          容器 -- 集群管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/13
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class backupCenter extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取备份中心基本信息
     * 获取单个脚本详情
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getBasicInfo()
    {
        //获取接受到的参数
        $data = $this->param;

        //获取脚本列表
        $info = $this->logic()->getBasicInfo($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }

    /**
     * 获获取备份中心所有数据
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getAllBasicInfo()
    {
        //获取接受到的参数
        $data = $this->param;
        //获取脚本列表
        $info = $this->logic()->getAllBasicInfo($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }





}
