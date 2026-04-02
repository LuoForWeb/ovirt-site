<?php


namespace app\v1\hadoop\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\hadoop\v0\service\Service;

/**
 * note          hadoop 之任务操作 logic
 * @author       lilingyu@vinchin.com
 * @date         2023/11/17 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopJobController extends JobController
{
    /**
     * 启动任务 demo
     * @param string $taskUuid  任务uuid
     * @param int    $startType 启动类型
     * @return json
     */
    public function startJob(string $taskUuid, int $startType){
        $task = $this->getTask($taskUuid);
        // 有四种启动任务类型 恢复：启动任务；备份：启动完备、增量、差异、策略
        $taskType = xphp_get_config('task', 'TASKTYPE');
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            if ($task['task_type'] == $taskType['BACKUP']) {
                // 启动备份任务
                $opName = 'BD_TASK_OP_BACKUP_START';
            } else {
                // 启动恢复任务
                $opName = 'BD_TASK_OP_RECOVERY_START';
            }
            
            $msg = [
                'task_uuid' => $taskUuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                'module_type' =>  xphp_get_config('module', 'MODULE_TYPE')['FS'],
                'submodule_type' =>  xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']
            ];
        }
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
        if ($task['task_type'] == $taskType['BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } else {
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

        if ($task['task_type'] == $taskType['BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        } else {
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
     * @param  array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return void
     */
    private function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $opName, $msg, $sync, $command);
        return $this->outMsg($mbResult, $opName);
    }


    

}