<?php

namespace app\v1\backupmanager\v0\controller;

use app\v1\common\controller\AuthBase;

class ReportRunningData extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取报表数据
     * 获取模块运行数据
     * @author       jiangyongjie@vinchin.com
     * @date         2023/10/18
     */
    public function getReportRunningData()
    {
        //获取接受到的参数
        $info = $this->logic()->getRunDataInfo($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
}
