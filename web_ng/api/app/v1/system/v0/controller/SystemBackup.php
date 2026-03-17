<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-23 17:35:28
 * @LastEditTime: 2025-08-18 09:51:15
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

class SystemBackup extends AuthBase
{
    /**
     * 获取备份源树
     */
    public function getSystemBackupTree()
    {
        $info = $this->logic()->getSystemBackupTreeInfo($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 手动备份
     */
    public function systemBackupMaual()
    {
        $info = $this->logic()->systemBackupMaual($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 自动备份配置保存
     */
    public function systemBackupAutoConfig()
    {
        $info = $this->logic()->systemBackupAutoConfig($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 获取自动备份点列表
     */
    public function getSystemBackupList()
    {
        $info = $this->logic()->getSystemBackupList($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 上传恢复源文件
     */
    public function uploadRecoverySrc()
    {
        $info = $this->logic()->uploadRecoverySrc($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 获取自动备份配置
     */
    public function getAutoBakConfig()
    {
        $info = $this->logic()->getAutoBakConfig($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 删除自动备份点
     */
    public function deleteSystemBackupAutoPoint()
    {
        $info = $this->logic()->deleteSystemBackupAutoPoint($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 获取上传文件对应的目录树
     */
    public function getUploadSrcTree()
    {
        $info = $this->logic()->getUploadSrcTree($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 检查恢复源数据
     */
    public function checkRecData()
    {
        $info = $this->logic()->checkRecData($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 启动恢复
     */
    public function doSystemRecovery()
    {
        $info = $this->logic()->doSystemRecovery($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 获取恢复进度
     */
    public function getRecoveryProgress()
    {
        $info = $this->logic()->getRecoveryProgress($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 获取自动备份点恢复树
     */
    public function getAutoSourceTree()
    {
        $info = $this->logic()->getAutoSourceTree($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 清理文件上传路径
     */
    public function cleanSystemTempDir()
    {
        $info = $this->logic()->cleanRecoveryDir($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
    /**
     * 下载自动备份文件
     */
    public function downloadBackupList()
    {
        $info = $this->logic()->downloadBackupList($this->param);

        if ($info) {
            return $this->success('', $info);
        }
        return $this->error();
    }
}
