<?php

namespace app\v1\hadoop\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          hadoop -- 之备份管理
 * @author       lilingyu@vinchin.com
 * @date         2023/10/19 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopBackUp extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  获取集群树
     */
    public function getBackupZtree(){
        $info = $this->logic()->getBackupZtree($this->param);
        $this->success('', $info);
    }

    /**
     *  获取集群文件树
     */
    public function getBackupFileZtree(){
        $info = $this->logic()->getBackupFileZtree($this->param);
        return $this->outputMsg($info['result'],$info['msg'], $info['data'], $info['errorCode']);
    }

    /**
     *  获取备份任务名
     */
    public function getBackupTaskName(){
        $info = $this->logic()->getBackupTaskName($this->param);
        $this->success('', $info);
    }

    /**
     *  创建备份任务
     */
    public function createBackupJob(){
        return $this->logic()->createBackupJob($this->param);
    }
     /**
     *  获取备份任务基本信息（用于修改任务）
     */
    public function getBackupTaskInfo(){
        $info = $this->logic()->getBackupTaskInfo($this->param);
        if (!empty($info)) {
            return $this->success(xphp_get_lang('WEB_M365_SERVER_GET_BACKUP_TASK_INFO_SUCCESS'), $info);
        } else {
            return $this->error(xphp_get_lang('WEB_M365_SERVER_GET_BACKUP_TASK_INFO_ERROR'), $info);
        }
    }

    /**
     *  修改备份任务
     */
    public function editBackupJob(){
        return $this->logic()->editBackupJob($this->param);
    }
    
    
    

}
