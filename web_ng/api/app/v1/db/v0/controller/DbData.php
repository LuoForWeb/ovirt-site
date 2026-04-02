<?php

namespace app\v1\db\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbData extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取数据库备份数据
     * @return void
     */
    public function getDbBackupData()
    {
        $this->checkParams('get_db_backup_data');
        $result = $this->logic()->getDbBackupData($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库实例或单个数据库的备份点信息
     * @return void
     */
    public function getDbBackupTimePoint()
    {
        $this->checkParams('get_db_backup_time_point');
        $result = $this->logic()->getDbBackupTimePoint($this->param);
        $this->outputHandle($result);
    }

    /**
     * 批量获取数据库实例的备份点信息
     * @return void
     */
    public function getDbBackupTimePointList()
    {
        $this->checkParams('get_db_backup_time_point_list');
        $result = $this->logic()->getDbBackupTimePointList($this->param);
        $this->outputHandle($result);
    }

    /**
     * 校验数据加密密码是否正确
     * @return void
     */
    public function verifyDbBackupPassword()
    {
        $this->checkParams('verify_db_backup_password');
        $result = $this->logic()->verifyDbBackupPassword(
            $this->param['encrypt_password'],
            $this->param['time_point_uuid']
        );
        $this->outputHandle($result);
    }

    /**
     * 删除数据库备份时间点
     * @return void
     */
    public function deleteDbBackupTimePoint()
    {
        $this->checkParams('delete_db_backup_time_point');
        $result = $this->logic()->deleteDbBackupTimePoint(
            $this->param['time_point_list'] ?? [],
            $this->param['backup_db_list'] ?? [],
            (int) $this->param['db_type']
        );
        $this->outputHandle($result);
    }

    /**
     * 获取配置文件内容
     * @return void
     */
    public function getConfigFileContent()
    {
        $this->checkParams('get_config_file_content');
        $result = $this->logic()->getConfigFileContent(
            $this->param['time_point_uuid'],
            intval($this->param['db_type'])
        );
        $this->outputHandle($result);
    }

    /**
     * 解析并转换spfile文件
     * @return void
     */
    public function parseConfigFileContent()
    {
        $this->checkParams('parse_config_file_content');
        $result = $this->logic()->parseConfigFileContent($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库备份存储列表
     * @return void
     */
    public function getDbBackupStorageList()
    {
        $result = $this->logic()->getDbBackupStorageList($this->param);
        $this->outputHandle($result);
    }
}
