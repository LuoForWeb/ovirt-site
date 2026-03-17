<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * 系统配置 - 系统升级
 */
class Upgrade extends AuthBase
{
    /**
     * 获取升级包列表
     * @return void
     */
    public function getPatches()
    {
        $this->checkParams('getPatches');
        $data = $this->logic()->getPatches($this->param);
        $this->success('', $data);
    }

    /**
     * 获取升级历史列表
     * @return void
     */
    public function getPatchHistory()
    {
        $this->checkParams('getHistory');
        $data = $this->logic()->getPatchHistory($this->param);
        $this->success('', $data);
    }

    /**
     * 删除升级包
     * @return void
     */
    public function deletePatches()
    {
        $this->checkParams('deletePatches');
        $data = $this->logic()->deletePatches($this->param);
        $this->success('', $data);
    }

    /**
     * 删除升级包历史
     * @return void
     */
    public function deletePatchHistory()
    {
        $this->checkParams('deleteHistory');
        $data = $this->logic()->deletePatchHistory($this->param);
        $this->success('', $data);
    }

    /**
     * 下载升级历史日志
     * @return void
     */
    public function downloadPatchHistory()
    {
        $this->checkParams('downloadHistory');
        $data = $this->logic()->downloadPatchHistory($this->param);
        $this->success('', $data);
    }

    /**
     * 上传前检查
     * @return void
     */
    public function checkSystemSpaceEnough()
    {
        $this->checkParams('checkSystemSpaceEnough');
        return $this->logic()->checkSystemSpaceEnough($this->param);
    }

    /**
     * 上传升级包
     * @return void
     */
    public function uploadPatches()
    {
        $this->logic()->uploadPatches();
        $this->success($_SESSION['fileName']);
    }

    /**
     * 上传升级包成功更新记录
     * @return void
     */
    public function updatePatchList()
    {
        $this->checkParams('updatePatchList');
        $data = $this->logic()->updatePatchList($this->param);
        $this->success('', $data);
    }


    /**
     * 获取选中的升级包的信息
     * @return void
     */
    public function getSelectPatch()
    {
        $this->checkParams('getSelectPatch');
        $data = $this->logic()->getSelectPatch($this->param);
        $this->success('', $data);
    }

    /**
     * 升级前检查是否有升级中的包
     * @return void
     */
    public function checkIsUpgrading()
    {
        $data = $this->logic()->checkIsUpgrading();
        $this->success('', $data);
    }

    /**
     * 升级前检查
     * @return void
     */
    public function upgradeCheck()
    {
        $this->checkParams('upgradeCheck');
        $data = $this->logic()->upgradeCheck($this->param);
        $this->success('', $data);
    }

    /**
     * 得到升级可用的备份节点列表
     * @return void
     */
    public function getUpdateNodeList()
    {
        $this->checkParams('getUpdateNodeList');
        $data = $this->logic()->getUpdateNodeList($this->param);
        $this->success('', $data);
    }

    /**
     * 检查主节点是否已经升级
     * @return void
     */
    public function checkMasterUpdate()
    {
        $this->checkParams('checkMasterUpdate');
        $data = $this->logic()->checkMasterUpdate($this->param);
        $this->success('', $data);
    }

    /**
     * 启动升级
     * @return void
     */
    public function upgradeStart()
    {
        $this->checkParams('upgrade');
        $result = $this->logic()->upgradeStart($this->param);
        $data = array();
        if($result){
            $this->success('', $data);
        }else{
            $this->error('');
        }
    }

    /**
     * 升级子节点
     * @return void
     */
    public function upgradeChildNode()
    {
        $this->checkParams('upgradeChild');
        $this->logic()->upgradeChildNode($this->param);
    }

    /**
     * 获取升级信息
     * @return void
     */
    public function getUpgradeInfo()
    {
        $this->checkParams('getUpgradeInfo');
        $data = $this->logic()->getUpgradeInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 获取升级包状态
     * @return void
     */
    public function getPacketStatus()
    {
        $this->checkParams('getPacketStatus');
        $info = $this->logic()->getPacketStatus($this->param);
        if ($info['success']) {
            $this->success($info['message'], $info['data']);
        } else {
            $this->error($info['message'], $info['data']);
        }
    }

    /**
     * 获取升级日志
     * @return void
     */
    public function getUpgradeLog()
    {
        $this->checkParams('getUpgradeLog');
        $info = $this->logic()->getUpgradeLog($this->param);
        if ($info['success']) {
            $this->success($info['message'], $info['data']);
        } else {
            $this->error($info['message'], $info['data']);
        }
    }

}
