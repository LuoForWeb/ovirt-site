<?php

namespace app\v1\homepage\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          首页
 * @author       wuxian@vinchin.com
 * @date         2025/04/27 11:26
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class homePageInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }
     /**
     * 数据概览
     * @param unknown $params
     */
    public function getDataCenterView(){
        $info = $this->logic()->getDataCenterView();
        if(!$info){
            return $this->error('', $info);
        }
        return $this->success('', $info);
    }

      /**
     * 获取存储信息
     * @param unknown $params
     */
    public function getSystemStorageData(){
        $info = $this->logic()->getSystemStorageData();
        if(!$info){
            return $this->error('', $info);
        }
        return $this->success('', $info);
    }
    
    /**
     * 获取任务各个状态个数概览
     * @param unknown $params
     */
    public function getTaskStatusView(){
        $info = $this->logic()->getTaskStatusView();
        if(!$info){
            return $this->error('', $info);
        }
        return $this->success('', $info);
    }
     /**
     * 受保护设备个数
     */
    public function getProtectedDevice()
    {
        $info = $this->logic()->getProtectedDevice($this->param);
        if(!$info){
            return $this->error('', $info);
        }
        return $this->success('', $info);
    }

    /**
     * 备份数据增长趋势
     */
    public function getBackupData()
    {
        $info = $this->logic()->getBackupData($this->param);
        if(!$info){
            return $this->error('', $info);
        }
        return $this->success('', $info);
    }
    
     /**
     * 获取系统授权状态
     * @param unknown $params
     */
    public function getSystemAuthStatus(){
        $info = $this->logic()->getSystemAuthStatus();
        if(!$info){
            return $this->error('', $info);
        }
        return $this->success('', $info);
    }



}
