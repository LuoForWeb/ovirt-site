<?php

namespace app\v1\homepage\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          首页
 * @author       liushuai@vinchin.com
 * @date         2023/11/23
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class homePage extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取首页基本信息
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getHomePageInfo()
    {

        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->getHomePageInfo($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }


    /**
     * 获取首页布局信息
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getHomePageGrid()
    {

        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->getHomePageGrid($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }

    /**
     * 获取主题信息存入session,返回true或者false
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getThemeInfo()
    {

        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->getThemeInfo($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }

    /**
     * 获取主题布局并保存
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function saveCardInfo()
    {

        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->saveCardInfo($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }


    /**
     * 获取节点信息
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function getNodeList()
    {
        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->getNodeList($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }


    /**
     * 清除自定义布局
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function clearUserGrid()
    {
        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->clearUserGrid($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }

    
    /**
     * 清除自定义布局
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function reoverUserGrid()
    {
        $data = $this->param;
        //获取首页基本数据
        $info = $this->logic()->reoverUserGrid($this->param);
        if(!$info['success']){
            return $this->error($info['message'], $info['data']);
        }
        return $this->success($info['message'], $info['data']);
    }


    //--------------------标准版首页所需接口开始--------------------
    /*  获取日志信息 */
    public function getLogInfo()
    {
         //获取首页日志信息
        $info = $this->logic()->getLogInfo($this->param);
        $this->success('', $info);

    }
    
    /* 获取告警信息 */
    public function getAlarmInfo()
    {
        //获取首页告警信息
        $info = $this->logic()->getAlarmInfo($this->param);
        $this->success('', $info);
    }

    /* 获取各模块受保护数据 */
    public function getProtectData(){
        $info = $this->logic()->getProtectData($this->param);
        $this->success('', $info);
    }

    /* 获取各模块受保护数据详情 */
    public function getProtectDataTrend(){
        $info = $this->logic()->getProtectDataTrend($this->param);
        $this->success('', $info);
    }

    /* 获取存储数据使用详情 */
    public function getStorageDataTrend(){
        $info = $this->logic()->getStorageDataTrend($this->param);
        $this->success('', $info);
    }
    
    //--------------------标准版首页所需接口结束--------------------

    



}
