<?php

namespace app\v1\db\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库 -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbBackUp extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取(已认证)数据库的类别
     * @return void
     */
    public function getDbType()
    {
        $this->checkParams('get_db_type');
        $ret = $this->logic()->getDbType($this->param);
        $this->success('', $ret, 200);
    }

    /**
     * 加载数据库应用/实例的数据库信息
     * @return void
     */
    public function loadInstanceDatabase()
    {
        $this->checkParams('load_instance_database');
        $ret = $this->logic()->loadInstanceDatabase(
            $this->param['app_uuid']
        );
        $this->outputHandle($ret);
    }

    /**
     * 加载数据库数据文件
     * @return void
     */
    public function loadDbDataFile()
    {
        $this->checkParams('load_db_data_file');
        $ret = $this->logic()->loadDbDataFile($this->param);
        $this->outputHandle($ret);
    }

    /**
     * 创建数据库备份任务
     * @return void
     */
    public function createDbBackupJob()
    {
        $this->checkParams('create_db_backup_job');
        $ret = $this->logic()->createDbBackupJob($this->param);
        $this->outputHandle($ret);
    }

    /**
     * 修改数据库备份任务
     * @return void
     */
    public function editDbBackupJob()
    {
        $this->checkParams('edit_db_backup_job');
        $ret = $this->logic()->editDbBackupJob($this->param);
        $this->outputHandle($ret);
    }

    /**
     * 获取数据库备份任务
     * @return void
     */
    public function getDbBackupJob()
    {
        $this->checkParams('get_db_backup_job');
        $ret = $this->logic()->getDbBackupJob(
            $this->param['jobs_uuid'],
            $this->param['with_encrypt_password_flag'] ?: false
        );
        $this->outputHandle($ret);
    }

    /**
     * 获取所有的数据库实例
     * @return void
     */
    public function getAllInstance()
    {
        $ret = $this->logic()->getAllInstance($this->param);
        $this->outputHandle($ret);
    }
}
