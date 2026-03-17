<?php

namespace app\v1\backupmanager\v0\controller;

use app\v1\common\controller\AuthBase;

class ReportOverView extends AuthBase
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取各个模块报表运行概览数据
     * @return void
     */
    public function getReportOverviewData()
    {
        $info = $this->logic()->getOverviewDataInfo($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
}
