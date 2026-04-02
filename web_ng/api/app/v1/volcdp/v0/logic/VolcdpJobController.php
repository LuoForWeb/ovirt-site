<?php

namespace app\v1\volcdp\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\resources\v0\logic\Node;
use app\v1\volcdp\v0\service\Service;

/**
 * note          卷实时 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpJobController extends JobController
{
    /**
     * 启动任务 demo 
     * @param string $taskUuid  任务uuid
     * @param int    $startType 启动类型
     * @return array
     */
    public function startJob(string $taskUuid, int $startType)
    {
        // 进行启动任务的逻辑
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        // 有2种启动任务类型 备份：启动任务(完备)、策略、增量、差异、接管
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            $taskType = xphp_get_config('task', 'TASKTYPE');

            if ($task['task_type'] == $taskType['VOL_CDP_BACKUP'] || $task['task_type'] == $taskType['VOL_CDP_REPLICATION']) {
                // 启动VolCDP实时备份任务
                $opName = 'BD_TASK_OP_BACKUP_START';
                if ($startType == 11) {
                    $opName = 'BD_TASK_OP_TAKEOVER_START';
                }
            } elseif ($task['task_type'] == $taskType['VOL_CDP_TAKEOVER']) {
                // 启动VolCDP接管任务
                $opName = 'BD_TASK_OP_TAKEOVER_START';
            } else {
                // 启动VolCDP实时恢复任务
                $opName = 'BD_TASK_OP_RECOVERY_START';
            }
            $msg = [
                'task_uuid_set' => [$taskUuid],
                'control_code' => $opName,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
        }
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'start');
    }

    /**
     * 启动接管任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function startTakeover(string $taskUuid)
    {
        // 启动接管任务的启动类型是 11
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);
        $taskType = xphp_get_config('task', 'TASKTYPE');
        // 不应该有这个任务类型判断，接管是都可以操作
        // if ($task['task_type'] == $taskType['VOL_CDP_TAKEOVER'] || $task['task_type'] == $taskType['VOL_CDP_BACKUP']) {
        // 启动函数
        return $this->startJob($taskUuid, 11);
        // }
        // return [false, '', ''];
    }

    /**
     * 启动回切任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function startFailback(string $taskUuid)
    {
        // 查询出任务的一些信息
        // $task = $this->getTask($taskUuid);

        // 1、获取当前任务是否有进行接管回切配置
        // 2、存在则进行启动接管任务/自动接管任务回切
        // 否则提示 fail
        $array = $this->getTaskFailbackInfo($taskUuid);
        if (empty($array)) {
            return false;
        }
        // 停止接管启动回切
        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START';
        $msg = [
            'task_uuid_set' => [$taskUuid],
            'control_code' => $opName
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

        if ($task['task_type'] == $taskType['VM_INSTANT_RECOVERY_MOTION']) {
            // 停止迁移任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        } elseif ($task['task_type'] == $taskType['VOL_CDP_BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } elseif ($task['task_type'] == $taskType['VOL_CDP_RECOVERY']) {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        } elseif ($task['task_type'] == $taskType['VOL_CDP_REPLICATION']) {
            // 复制任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } else {
            // 接管任务 VOL_CDP_TAKEOVER
            $opName = 'BD_TASK_OP_TAKEOVER_STOP';
        }

        $msg = [
            'task_uuid_set' => [$taskUuid],
            'control_code' => $opName
        ];

        if ($opName == 'BD_TASK_OP_TAKEOVER_STOP') {
            // 停止接管
            $msg['backup_mode'] = 0;
            $msg['time_strategy_id'] = 0;
            $msg['auto_start_flag'] = xphp_get_config('app', 'FLAG')['UNSET'];
        }
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'stop');
    }

    /**
     * 停止接管任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function stopTakeoverJob(string $taskUuid)
    {
        $opName = 'BD_TASK_OP_TAKEOVER_STOP';
        $msg = [
            'task_uuid_set' => [$taskUuid],
            'control_code' => $opName
        ];

        if ($opName == 'BD_TASK_OP_TAKEOVER_STOP') {
            // 停止接管
            $msg['backup_mode'] = 0;
            $msg['time_strategy_id'] = 0;
            $msg['auto_start_flag'] = xphp_get_config('app', 'FLAG')['UNSET'];
        }
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

        if ($task['task_type'] == $taskType['VOL_CDP_BACKUP']) {
            // 卷CDP备份任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        } elseif ($task['task_type'] == $taskType['VOL_CDP_RECOVERY']) {
            //卷CDP恢复
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        } else {
            // 卷CDP接管
            $opName = 'BD_TASK_OP_TAKEOVER_DELETE';
        }

        $msg = [
            'task_uuid_set' => [$taskUuid],
            'control_code' => $opName
        ];
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
    }

    /**
     * 根据任务uuid获取是否有进行接管回切配置
     * @param string $taskUuid 任务uuid
     * @return array
     */
    public function getTaskFailbackInfo(string $taskUuid): array
    {
        $sql = "SELECT failback_target_agent_uuid FROM cdp_vol_task_takeover_failback_info WHERE task_uuid =?";
        $data = $this->dbSelect($sql, array($taskUuid));

        return array(
            'failback_target_agent_uuid' => !empty($data) ? array_column($data, 'failback_target_agent_uuid') : []
        );
    }

    /**
     * 自动接管是否启用控制
     * @param $params
     * @return string
     */
    public function autoTakeoverOperation($params)
    {
        $pfMSg = array();
        $taskUuid = $params['job_uuid'];
        $autoTakeoverEnableFlag = $params['enable_flag'];
        $opName = "VOL_CDP_TASK_OP_AUTO_TAKEOVER_SET";  //自动接管设置，已配置可以禁用，禁用中可以启用
        $msg = array(
            'task_uuid' => $taskUuid,
            'auto_takeover_enable_flag' => $autoTakeoverEnableFlag
        );
        // $msg = json_encode($msg);
        $nodeHandler = new Node();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid
        $mbResult = $this->opUnifyMsg($taskUuid, $opName, $msg, FALSE, TRUE);
        $result = $mbResult[0];
        $msg = $mbResult['msg'];
        if ($autoTakeoverEnableFlag == xphp_get_config('app', 'FLAG')['SET']) {
            $operate = xphp_get_lang('UI_VOL_CDP_ENABLE_AUTO_TAKEOVER');  //启用自动接管
        } else if ($autoTakeoverEnableFlag == xphp_get_config('app', 'FLAG')['UNSET']) {
            $operate = xphp_get_lang('UI_VOL_CDP_DISABLED_AUTO_TAKEOVER'); //禁用自动接管
        }
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
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
    private function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false, $taskType = 0, $operateType = '')
    {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $opName, $msg, $sync, $command);
        return $this->outMsg($mbResult, $opName, 'volcdp', $taskType, $operateType);
    }
}
