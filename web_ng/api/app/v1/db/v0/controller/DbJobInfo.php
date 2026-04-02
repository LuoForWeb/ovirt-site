<?php

namespace app\v1\db\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库 -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbJobInfo extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取数据库任务运行信息
     * @return void
     */
    public function getDbJobInfo()
    {
        $this->checkParams('get_db_job_info');
        $result = $this->logic()->getDbJobInfo($this->param['jobs_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库验证报告
     * @return void
     */
    public function generateDbValidateReport()
    {
        $this->checkParams('generate_db_validate_report');
        $result = $this->logic()->generateDbValidateReport($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库验证报告
     * @return void
     */
    public function getDbValidateReport()
    {
        $this->checkParams('get_db_validate_report');
        $result = $this->logic()->getDbValidateReport($this->param);
        $this->outputHandle($result);
    }

    /**
     * 发送数据库验证报告
     * @return void
     */
    public function sendDbValidateReport()
    {
        $this->checkParams('send_db_validate_report');
        $result = $this->logic()->sendDbValidateReport($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库任务的关联任务
     * @return void
     */
    public function getDbJobAssociation()
    {
        $this->checkParams('get_db_job_association');
        $result = $this->logic()->getDbJobAssociation($this->param['jobs_uuid']);
        $this->outputHandle($result);
    }
}
