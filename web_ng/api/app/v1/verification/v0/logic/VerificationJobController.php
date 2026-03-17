<?php

namespace app\v1\verification\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VerifyOpcode;
use app\v1\verification\v0\service\Service;

/**
 * note          数据验证CDM 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VerificationJobController extends JobController
{
    /**
     * 启动任务 demo
     * @param array $params 参数
     * @return void
     */
    public function startJob(string $taskUuid, int $startType)
    {
        //检查是否有操作权限
        $this->checkTaskAuth($taskUuid);
        // 进行启动任务的逻辑
        // 有四种启动任务类型 恢复：启动任务；备份：启动完备、增量、差异、策略
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
           $opName = 'SR_OP_CODE_START_SUREBACKUP';
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
     * 选择对象启动数据验证任务
     * @param array $params 参数
     * @return void
     */
    public function startSelectJob($params = [])
    {
        //检查是否有操作权限
        $this->checkTaskAuth($params['task_uuid']);

        $uuidList = array();
        foreach ($params['item_uuids'] as $d){
            $uuidList[] = array(
                'item_uuid' => $d
            );
        }
        $opName = 'SR_OP_CODE_START_SUREBACKUP';
        $msg = [
            'task_uuid' => $params['task_uuid'],
            'backup_mode' => 1,
            'time_strategy_id' => 0,
            "item_uuids" => $uuidList,
        ];

        return $this->opUnifyMsg($params['task_uuid'], $opName, $msg, false, true, true);
    }

    /**
     * 停止任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function stopJob(string $taskUuid)
    {

        //检查是否有操作权限
        $this->checkTaskAuth($taskUuid);
        $opName = 'SR_OP_CODE_STOP_SUREBACKUP';

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

        //检查是否有操作权限
        $this->checkTaskAuth($taskUuid);
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

        $opName = 'SR_OP_CODE_DELETE_SUREBACKUP';

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
     * @param bool   $isBack   是否输出
     * @return void
     */
    private function opUnifyMsg(
        string $taskUuid,
        string $opName,
        array $msg,
        $sync = false,
        $command = false,
        $isBack = false
    ) {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $opName, $msg, $sync, $command);
        if (!$isBack) {
            return $this->outMsg($mbResult, $opName, 'verify');
        }

        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($opName == 'BD_TASK_OP_START_TIMESTRATEGY') {
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } else {
            $operate = (new VerifyOpcode())->getOpcodeDes($opName);
        }
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 检查任务操作权限
     * @param $taskUuid
     */
    private function checkTaskAuth($taskUuid){
        $sql = "select user_uuid from bd_task where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUuid));
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['current_job']);
    }
}
