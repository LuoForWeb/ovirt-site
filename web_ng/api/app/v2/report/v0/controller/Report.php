<?php

namespace app\v2\report\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          报表
 * @author       liushuai@vinchin.com
 * @date         2023/10/24 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Report extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取报表树
     * @return void
     */
    public function getReportTree()
    {
        $info = $this->logic()->getReportTree($this->param);

        $this->success('', $info);
    }

    /**
     * 获取报表列表
     * @return json
     */
    public function getReportList()
    {
        $info = $this->logic()->getReportList($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 创建报表组
     * @return void
     */
    public function createReportGroup()
    {
        $info = $this->logic()->createReportGroup($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 更新报表组
     * @return json
     */
    public function updateReportGroup()
    {
        $info = $this->logic()->updateReportGroup($this->param);

        if (!$info['success']) {
            $this->error($info['message'], []);
        }

        $this->success($info['message'], $info);
    }

    /**
     * 删除报表组
     * @return json
     */
    public function deleteReportGroup()
    {
        $info = $this->logic()->deleteReportGroup($this->param);

        if (!$info['success']) {
            $this->error($info['message'], []);
        }

        $this->success($info['message'], []);
    }

    /**
     * 删除报表
     * @return void
     */
    public function deleteReport()
    {
        $info = $this->logic()->deleteReport($this->param);

        if (!$info['success']) {
            $this->error($info['message'], []);
        }

        $this->success($info['message'], []);
    }

    /**
     * 获取实时容灾概览
     * @return json
     */
    public function getStorageOverview()
    {
        $info = $this->logic()->getStorageOverview();
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    public function getStorageUsageTendency()
    {
        $info = $this->logic()->getStorageUsageTendency($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    public function getStorageAvailabilityForecast()
    {
        $info = $this->logic()->getStorageAvailabilityForecast($this->param);

        if (empty($info)) {
            $this->error('未添加任何存储设备');
        }

        $this->success('', $info);
    }

    public function getTapeOverview()
    {
        $info = $this->logic()->getTapeOverview($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 获取磁带备份数据明细
     * @return void
     */
    public function getTapeReportList()
    {
        $info = $this->logic()->getTapeReportList($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    public function getNodeOverview()
    {
        $info = $this->logic()->getNodeOverview($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    public function getNodeLoadTendency()
    {
        $info = $this->logic()->getNodeLoadTendency($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 获取节点备份数据明细
     * @return void
     */
    public function getNodeReportList()
    {
        $info = $this->logic()->getNodeReportList($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 新建报表
     * @return void
     */
    public function createReport()
    {
        $info = $this->logic()->createReport($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 更新报表
     * @return void
     */
    public function updateReport()
    {
        $info = $this->logic()->updateReport($this->param);

        if (!$info['success']) {
            $this->error($info['message'], []);
        }

        $this->success('', $info['message']);
    }

    /**
     * 获取存储列表
     * @return void
     */
    public function getStorageList()
    {
        $info = $this->logic()->getStorageList($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 获取报表路径树
     * @return void
     */
    public function getCustomReportFolderTree()
    {
        $info = $this->logic()->getCustomReportFolderTree($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }

    /**
     * 获取报表详情
     * @return void
     */
    public function getReportDetail()
    {
        $info = $this->logic()->getReportDetail($this->param);

        if (empty($info)) {
            $this->error();
        }

        $this->success('', $info);
    }
}
