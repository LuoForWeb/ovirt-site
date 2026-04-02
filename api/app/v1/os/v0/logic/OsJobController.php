<?php

namespace app\v1\os\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\os\v0\service\Service;

/**
 * note          操作系统 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsJobController extends JobController
{
    /**
     * 启动任务 demo
     * @param string $taskUuid  任务uuid
     * @param int    $startType 启动类型
     * @return json
     */
    public function startJob(string $taskUuid, int $startType)
    {
        // 进行启动任务的逻辑
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        // 有2种启动任务类型 备份：启动任务（完备）、增量、差异、策略，恢复：启动任务
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            $taskType = xphp_get_config('task', 'TASKTYPE');

            $taskStatus = xphp_get_config('task', 'TASKSTATUS');
            if ($task['task_type'] == $taskType['OS_BACKUP']) {
                // 启动备份任务
                $opName = 'BD_TASK_OP_BACKUP_START';
                $this->backupTaskStartCheck($taskUuid);
            } else if($task['task_type'] == $taskType['OS_INSTANT_RECOVERY']){
                //启动瞬时恢复任务
                $opName = 'OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY';
                $this->backupTaskStartCheck($taskUuid);
            }
            else {
                // 启动恢复任务
                $opName = 'BD_TASK_OP_RECOVERY_START';
                $this->recoveryTaskStartCheck($taskUuid);
            }
            $msg = [
                'task_uuid' => $taskUuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
        }
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
    }

    /**
     * 暂停任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function pauseTask(string $taskUuid)
    {

        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        $taskType = xphp_get_config('task', 'TASKTYPE');

        if ($task['task_type'] == $taskType['OS_BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_PAUSE';
        } else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_PAUSE';
        }

        $msg = [
            'task_uuid' => $taskUuid
        ];

        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
    }

    /**
     * 停止任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function stopJob(string $taskUuid)
    {
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        $taskType = xphp_get_config('task', 'TASKTYPE');

        if ($task['task_type'] == $taskType['OS_BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } else if($task['task_type'] == $taskType['OS_INSTANT_RECOVERY']){
            //瞬时恢复任务
            $opName = 'OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY';
            $this->backupTaskStartCheck($taskUuid); 
        } else if($task['task_type'] == $taskType['OS_INSTANT_RECOVERY_MOTION'] ){
            // 迁移
            $opName = 'OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION';
        }
        else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }

        $msg = [
            'task_uuid' => $taskUuid
        ];
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
    }

    /**
     * 删除任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function delJob(string $taskUuid)
    {

        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        // 检查任务是否在运行中
        if ($task['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING'),
                xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING_TIPS'),
                'warning'
            );
        }

        $taskType = xphp_get_config('task', 'TASKTYPE');

        if ($task['task_type'] == $taskType['OS_BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        } else if($task['task_type'] == $taskType['OS_INSTANT_RECOVERY']){
            //瞬时恢复任务
            $opName = 'OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY';
            $this->backupTaskStartCheck($taskUuid);
        }else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }

        $msg = [
            'task_uuid_list' => [$taskUuid]
        ];

        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
    }

    /**
     * 统一发送消息到后台
     * @param string $taskUuid 任务uuid
     * @param string $opName   操作码
     * @param array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return void
     */
    private function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $opName, $msg, $sync, $command);
        return $this->outMsg($mbResult, $opName);
    }

    /**
     * 启动备份任务前的检测
     * @param string $taskUuid 任务uuid
     * @return bool
     */
    private function backupTaskStartCheck(string $taskUuid)
    {
        return true;
    }

    /**
     * 启动恢复任务前的检测
     * @param string $taskUuid 任务uuid
     * @return bool
     */
    private function recoveryTaskStartCheck(string $taskUuid)
    {
        return true;
    }
}
