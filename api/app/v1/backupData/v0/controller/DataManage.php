<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 备份数据管理控制类
 * @Date: 2024-07-16 14:53:37
 * @LastEditTime: 2025-09-18 17:46:25
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

namespace app\v1\backupData\v0\controller;

use app\v1\common\controller\AuthBase;

class DataManage extends AuthBase
{
    // 获取所有数据的任务列表
    public function getTaskGrid()
    {
        $info = $this->logic()->getTaskGrid($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取所有数据的对象列表
    public function getItemGrid()
    {
        $info = $this->logic()->getItemGrid($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function getVolTagPointGrid()
    {
        $info = $this->logic()->getVolTagPointGrid($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取异地存储任务列表
    public function getRemoteGrid()
    {
        $info = $this->logic()->getRemoteGrid($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取异地存储任务列表
    public function getRemoteItem()
    {
        $info = $this->logic()->getRemoteItem($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取所有的时间点
    public function getAllPoints()
    {
        $info = $this->logic()->getAllPoints($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 删除时间点
    public function deletePoint()
    {
        $info = $this->logic()->deletePoint($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取时间点的路径
    public function getPointDetailsInfo()
    {
        $info = $this->logic()->getPointDetailsInfo($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 时间点添加备注
    public function updateRemarks()
    {
        $info = $this->logic()->updateRemarks($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    public function setGfsMark()
    {
        $info = $this->logic()->setGfsMark($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 设置永久标记
    public function addStar()
    {
        $info = $this->logic()->addStar($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 删除永久标记
    public function deleteStar()
    {
        $info = $this->logic()->deleteStar($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 配置worm保护期限
    public function setWormTime()
    {
        $info = $this->logic()->setWormTime($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 删除标签点
    public function deleteTagPoint()
    {
        $info = $this->logic()->deleteTagPoint($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 标记标签点
    public function remarkTagPoint()
    {
        $info = $this->logic()->remarkTagPoint($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取病毒感染列表
    public function getVirusList()
    {
        $info = $this->logic()->getVirusList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取子任务列表
    public function getSubTaskList()
    {
        $info = $this->logic()->getSubTaskList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取病毒扫描历史记录
    public function getVirusHistoryList()
    {
        $info = $this->logic()->getVirusHistoryList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取验证记录
    public function getDataVerifyHistory()
    {
        $info = $this->logic()->getDataVerifyHistory($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 获取任务下的对象树
    public function getTaskTree()
    {
        $info = $this->logic()->getTaskTree($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 导出所有任务列表
    public function exportAllTaskList()
    {
        $info = $this->logic()->exportAllTaskList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 导出所有对象列表
    public function exportAllItemList()
    {
        $info = $this->logic()->exportAllItemList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 导出所有时间点列表
    public function exportAllPointList()
    {
        $info = $this->logic()->exportAllPointList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    // 导出所有时间点列表
    public function exportAllRemoteTaskList()
    {
        $info = $this->logic()->exportAllRemoteTaskList($this->param);
        
        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
}