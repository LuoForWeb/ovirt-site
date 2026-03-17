<?php

namespace app\v1\copy\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\copy\v0\service\Service;

/**
 * note          副本 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class CopyJobController extends JobController
{
    /**
     * 启动任务 demo
     * @param string $taskUuid  任务uuid
     * @param int    $startType 启动类型
     * @return {}
     */
    public function startJob(string $taskUuid, int $startType)
    {
        // 进行启动任务的逻辑
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid, 'module_type,task_type,task_status,sub_module_type');

        // 有2种启动任务类型 备份：启动任务、策略
        // dump($task,$startType);
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            $taskStatus = xphp_get_config('task', 'TASKSTATUS');
            if ($task['task_type'] == 17 || $task['task_type'] == 18 || $task['task_type'] == 19 || $task['task_type'] == 20) {
                // 启动备份任务
                $opName = 'BD_TASK_OP_BACKUP_COPY_START';
                if (in_array($task['task_status'], [$taskStatus['PAUSED'], $taskStatus['NETWORK_FAULT']])) {
                    $opName = 'BD_TASK_OP_BACKUP_COPY_CONTINUE';
                }
            }
            $msg = [
                'task_uuid' => $taskUuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
            $operateDes = xphp_get_lang('WEB_PT_OP_COPY_START');
            switch($task['task_type']){
                case '17':
                    $operateDes = xphp_get_lang('WEB_PT_OP_COPY_START');
                    break;
                case '18':
                    $operateDes = xphp_get_lang('WEB_PT_OP_COPY_FETCH_START');
                    break;
                case '19':
                    $operateDes = xphp_get_lang('WEB_PT_OP_ARCHIVE_START');
                    break;
                case '20':
                    $operateDes = xphp_get_lang('WEB_PT_OP_ARCHIVE_FETCH_START');
                    break;
                default:
                    return '';
            }
            //检查授权是否过期
            $expireDate = v1_license_get_expire_days();
            if ($expireDate['expire_days'] < 0) {
                exit($this->muOpResult(false, $operateDes, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
            }
        }
        return $this->opUnifyMsg($taskUuid, $task['sub_module_type'], $opName, $msg, false, true, $task['task_type']);
    }

    /**
     * 暂停任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function pauseTask(string $taskUuid)
    {

        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid, 'module_type,task_type,task_status,sub_module_type');

        $taskType = xphp_get_config('task', 'TASKTYPE');

        $array = [
            $taskType['BACKUP_COPY'],
            $taskType['ARCHIVE'],
            $taskType['DB_BACKUP_COPY'],
            $taskType['OS_BACKUP_COPY'],
            $taskType['NAS_BACKUP_COPY'],
        ];
        if (in_array($task['task_type'], $array)) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_PAUSE';
        } else {
            // 回传
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE';
        }

        $msg = [
            'task_uuid' => $taskUuid
        ];
        return $this->opUnifyMsg($taskUuid, $task['sub_module_type'], $opName, $msg, false, true, $task['task_type']);
    }

    /**
     * 停止任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function stopJob(string $taskUuid)
    {
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid, 'module_type,task_type,task_status,sub_module_type');

        // 副本
        if ($task['task_type'] == 17 || $task['task_type'] == 19) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_STOP';
        } else {
            // 回传任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_STOP';
        }

        $msg = [
            'task_uuid' => $taskUuid
        ];
        return $this->opUnifyMsg($taskUuid, $task['sub_module_type'], $opName, $msg, false, true, $task['task_type']);
    }

    /**
     * 删除任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function delJob(string $taskUuid)
    {

        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid, 'module_type,task_type,task_status,task_orchestration_plan_flag,sub_module_type');
        if (intval($task['task_orchestration_plan_flag']) == xphp_get_config('app','FLAG')['SET']) {
            // 任务编排计划中的任务不能删除
            return $this->muOpResult(
                false,
                xphp_get_lang('UI_PLATFORM_JOB_MONITOR'),
                xphp_get_lang('UI_JOB_TASK_ORCHESTRATION_OCCUPATION_TIP')
            );
        }

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

        $array = [
            $taskType['BACKUP_COPY'],
            $taskType['ARCHIVE'],
            $taskType['DB_BACKUP_COPY'],
            $taskType['OS_BACKUP_COPY'],
            $taskType['NAS_BACKUP_COPY'],
        ];
        if (in_array($task['task_type'], $array)) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_DELETE';
        } else {
            // 恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE';
        }

        $msg = [
            'task_uuid_list' => [$taskUuid]
        ];
        return $this->opUnifyMsg($taskUuid, $task['sub_module_type'], $opName, $msg, false, true, $task['task_type']);
    }

    /**
     * 统一发送消息到后台
     * @param string $taskUuid  任务uuid
     * @param int    $subModule 子模块编号
     * @param string $opName    操作码
     * @param array  $msg       消息
     * @param bool   $sync      是否同步
     * @param bool   $command   是否命令
     * @return array
     */
    private function opUnifyMsg(
        string $taskUuid,
        int $subModule,
        string $opName,
        array $msg,
        $sync = false,
        $command = false,
        $task_type = ''
    ) {
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $subModule, $opName, $msg, $sync, $command);
        $return = $this->outMsg($mbResult, $opName);
        if ($task_type == $taskType['BACKUP_COPY_FETCH']) {
            $return[1] = str_ireplace(xphp_get_lang('WEB_PLATFORM_DES_COPY'), xphp_get_lang('UI_PLATFORM_VM_COPY_RECOVERY'), $return[1]);
        }
        if ($task_type == $taskType['ARCHIVE']) {
            $return[1] = str_ireplace(xphp_get_lang('WEB_PLATFORM_DES_COPY'), xphp_get_lang('UI_PLATFORM_ARCHIVE'), $return[1]);
        }
        if ($task_type == $taskType['ARCHIVE_FETCH']) {
            $return[1] = str_ireplace(xphp_get_lang('WEB_PLATFORM_DES_COPY'), xphp_get_lang('UI_PLATFORM_ARCHIVE_FETCH'), $return[1]);
        }
        return $return;
    }
}
