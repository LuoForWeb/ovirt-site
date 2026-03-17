<?php

namespace app\v1\fileback\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\file\v0\service\Service;
use app\v1\resources\v0\logic\Node;

/**
 * note          文件 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileJobController extends JobController
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
                $this->backupTaskStartCheck($taskUuid);
            } else {
                // 启动恢复任务
                $this->recoveryTaskStartCheck($taskUuid);
                $opName = 'BD_TASK_OP_RECOVERY_START';
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

        if ($task['task_type'] == $taskType['BACKUP']) {
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

    /**
     * 启动文件任务检测用户使用代理权限
     * @param string $uuid   任务uuid
     * @param string $opName 操作名
     * @return string
     */
    private function pCheckAgentPermission($uuid, $opName)
    {
        if (xphp_get_user_info()['userUuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            $sql = "select user_uuid,agent_uuid from
                         bd_task  where task_uuid = ?";
            $data = $this->dbSelect($sql, array($uuid));
            if (!empty($data[0]['agent_uuid'])) {
                $agentUuid = $this->getClientUuids($data[0]['user_uuid']);
                if (is_array($agentUuid)) {
                    if (!in_array($data[0]['agent_uuid'], $agentUuid)) {
                        return $this->muOpResult(false, $opName, xphp_get_lang('WEB_FILE_AGENT_NOT_AUTH'), 'warning');
                    }
                }
            }
        }
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

    /**
     * 操作按钮-启动单个对象备份任务
     * @param $params
     * @return string
     */
    public function startBackupJob($params){
     
        //获取备份模式
        $backup_mode = $params['backup_mode'];
        //获取任务uuid
        $task_uuid = $params['job_uuid'];
        //获取对象列表
        $fs_uuid_list = $params['object_uuids'];
        //以下参数置位空
        $time_strategy_id = 0;
        $auto_start_flag = xphp_get_config('app', 'FLAG')['UNSET'];
        //定义操作码
        $opName = 'BD_TASK_OP_BACKUP_START';
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => $auto_start_flag,
            "fs_uuid_list" => $fs_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

}
