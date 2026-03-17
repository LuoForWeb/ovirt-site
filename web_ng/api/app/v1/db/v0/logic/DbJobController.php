<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\db\v0\service\Service;
use app\v1\opcode\PfOpcode;

/**
 * note          数据库 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbJobController extends JobController
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

        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            $taskType = xphp_get_config('task', 'TASKTYPE');

            if ($task['task_type'] == $taskType['DB_BACKUP']) {
                // 备份任务
                $opName = 'BD_TASK_OP_BACKUP_START';
            } else {
                // 恢复任务
                $opName = 'BD_TASK_OP_RECOVERY_START';
            }
            $msg = [
                'task_uuid' => $taskUuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
        }
        $subModule = $this->getSubModule($task['module_type'], $task['task_type'], $taskUuid);
        return $this->opUnifyMsg($taskUuid, $subModule, $opName, $msg, false, true);
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

        if ($task['task_type'] == $taskType['DB_BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }

        $msg = [
            'task_uuid' => $taskUuid
        ];
        $subModule = $this->getSubModule($task['module_type'], $task['task_type'], $taskUuid);
        return $this->opUnifyMsg($taskUuid, $subModule, $opName, $msg, false, true);
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
        $taskUuidList = [$taskUuid];

        if ($task['task_type'] == $taskType['DB_BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
            $allDbType = xphp_get_config('db', 'DB_TYPE');
            $dbData = $this->dbSelect("SELECT db_type, depend_task_uuid FROM db_task WHERE task_uuid = ? ", [$taskUuid]);
            if ($dbData && is_array($dbData)) {
                if ($dbData[0]['db_type'] == $allDbType['TIDB']) {
                    // 删除的任务是完备任务
                    $sql = "SELECT bt.task_uuid, bt.task_name, bt.task_status
                            FROM bd_task bt
                                INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                            WHERE dt.depend_task_uuid = ? ";
                    $dependTaskData = $this->dbSelect($sql, [$taskUuid]);
                    if (is_array($dependTaskData) && $dependTaskData) {
                        // 检查依赖任务是否在运行中
                        if (
                            $dependTaskData[0]['task_status'] != xphp_get_config('task', 'TASKSTATUS')['STOPPED'] &&
                            $dependTaskData[0]['task_status'] != xphp_get_config('task', 'TASKSTATUS')['DELETING']
                        ) {
                            return $this->muOpResult(
                                false,
                                xphp_get_lang('WEB_PT_OP_BACKUP_DELETE'),
                                sprintf(xphp_get_lang('WEB_DB_TIDB_DELETE_DEPEND_TASK_NOT_STOPPED'), $dependTaskData[0]['task_name']),
                                'warning'
                            );
                        }
                        $dependTaskUuidList = array_column($dependTaskData, 'task_uuid');
                        $taskUuidList = array_merge($taskUuidList, $dependTaskUuidList);
                    }
                } elseif ($dbData[0]['db_type'] == $allDbType['ORACLE']) {
                    // Oracle需要主任务和子任务都停止
                    // 查看所有数据库备份任务
                    $allBackupTask = (new DbBackUp())->getAllInstanceBackupInfo();
                    // 查看当前任务的所有实例名、客户端uuid
                    $appList = (new DbJobInfo())->getAppListByTaskUuid($taskUuid);
                    foreach ($appList as $appInfo) {
                        foreach ($allBackupTask as $backupInfo) {
                            if (
                                $backupInfo['instance_name'] == $appInfo['app_name'] &&
                                $backupInfo['agent_uuid'] == $appInfo['agent_uuid'] &&
                                $backupInfo['db_type'] == $appInfo['app_type']
                            ) {
                                if (
                                    $backupInfo['task_status'] != xphp_get_config('task', 'TASKSTATUS')['STOPPED'] &&
                                    $backupInfo['task_status'] != xphp_get_config('task', 'TASKSTATUS')['DELETING']
                                ) {
                                    return $this->muOpResult(
                                        false,
                                        xphp_get_lang('WEB_PT_OP_BACKUP_DELETE'),
                                        sprintf(xphp_get_lang('WEB_DB_ORACLE_DELETE_DEPEND_TASK_NOT_STOPPED'), $backupInfo['task_name']),
                                        'warning'
                                    );
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }

        $msg = [
            'task_uuid_list' => $taskUuidList,
        ];
        $subModule = $this->getSubModule($task['module_type'], $task['task_type'], $taskUuid);
        return $this->opUnifyMsg($taskUuid, $subModule, $opName, $msg, false, true);
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
        $command = false
    ) {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $subModule, $opName, $msg, $sync, $command);
        return $this->outMsg($mbResult, $opName);
    }

    /**
     * 启动数据库备份任务
     * @param array $params 启动参数
     * @return array
     */
    public function startBackupJob(array $params): array
    {
        $checkResult = $this->checkTaskRunning($params['jobs_uuid']);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $pfOpcode = new PfOpcode();
        $opcodeName = 'BD_TASK_OP_BACKUP_START';
        $operate = $pfOpcode->getOpcodeDes($opcodeName);
        $startResult = $this->service()->startBackupJobService(
            $params['jobs_uuid'],
            intval($params['db_type']),
            intval($params['backup_mode']),
            $params['crosscheck'],
            $params['db_list']
        );
        if (!$startResult['result']) {
            $this->muOpResult(false, $operate, $startResult['msg'], '', $startResult['errorCode']);
            return $this->sendResult('', false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_LOG_TASK_START_BACKUP') . xphp_get_lang('WEB_PUBLIC_SUCCESS'));
    }

    /**
     * 检查任务是否在运行
     * @param string $jobsUuid 任务uuid
     * @return array
     */
    private function checkTaskRunning(string $jobsUuid): array
    {
        $sql = "SELECT task_status FROM bd_task WHERE task_uuid = ? ";
        $jobData = $this->dbSelect($sql, [$jobsUuid]);
        if (!$jobData || !is_array($jobData)) {
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_TASK_NOT_EXISTS'), false, 400);
        }
        if ($jobData[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            return $this->sendResult(xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING_TIPS'), false, 400);
        }
        return $this->sendResult('');
    }
}
