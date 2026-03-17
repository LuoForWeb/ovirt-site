<?php

namespace app\v1\cloud\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * Class CloudBackup
 * @package app\v1\cloud\v0\controller
 */
class CloudBackup extends AuthBase
{
    /**
     * 创建备份任务
     * @return string
     */
    public function createBackupJob(): string
    {
        //$this->checkParams('createJob');
        return $this->logic()->createBackupJob($this->param);
    }

    /**
     * 修改备份任务
     * @return string
     */
    public function editBackupJob(): string
    {
//        $this->checkParams('editJob');
        return $this->logic()->editBackupJobV2($this->param);
    }
}
