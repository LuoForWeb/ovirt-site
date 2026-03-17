<?php

namespace app\v1\vm\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          虚拟机管理 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmData extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取备份数据的虚拟机树
     * @return string
     */
    public function getRestoreData(): string
    {
        $this->checkParams('getRestoreData');
        if ($this->param['_rows']) {
            // 以rows&total方式获取
            $data = $this->logic()->getRestoreDataVmTree($this->param);
            $this->success('', $data);
        }
        return $this->logic()->getRestoreDataVmTree($this->param);
    }

    /**
     * 异步获取备份时间点
     * @return string
     */
    public function getRestorePoints(): string
    {
        $this->checkParams('getRestorePoints');
        $data = $this->logic()->getRestorePoints($this->param);
        $this->success('', $data);
    }

    /**
     * 删除备份数据
     * @return string
     */
    public function deleteRestoreData(): string
    {
        return $this->logic()->deleteRestoreData($this->param);
    }

    /**
     * 删除单个备份数据
     * @return string
     */
    public function deleteRestoreDataOne(): string
    {
        $this->checkParams('deleteRestoreDataOne');
        return $this->logic()->deleteRestoreDataOne($this->param);
    }

    /**
     * 给时间点添加备注
     * @return string
     */
    public function addRestorePointsRemark(): string
    {
        $this->checkParams('addRemark');
        $re = $this->logic()->addRestorePointsRemark($this->param);
        $re['success'] ? $this->success($re['message']) : $this->error($re['message'], $re['data'], $re['code']);
    }

    /**
     * 给时间点设置保留标记
     * @return string
     */
    public function setRestorePointsGfsMark(): string
    {
        $this->checkParams('setGfsMark');
        return $this->logic()->setRestorePointsGfsMark($this->param);
    }

    /**
     * 获取所有虚拟机个数
     * @return string
     */
    public function getAllVms(): string
    {
        $data = $this->logic()->getAllVms();
        $this->success('', $data);
    }

    /**
     * 给备份时间点添加星标
     * @return string
     */
    public function addStar(): string
    {
        $this->checkParams('addRemark');
        $re = $this->logic()->addStar($this->param);
        $re['success'] ? $this->success($re['message']) : $this->error($re['message'], $re['data'], $re['code']);
    }

    /**
     * 删除备份时间点星标
     * @return string
     */
    public function deleteStar(): string
    {
        $this->checkParams('addRemark');
        $re = $this->logic()->deleteStar($this->param);
        $re['success'] ? $this->success($re['message']) : $this->error($re['message'], $re['data'], $re['code']);
    }
}
