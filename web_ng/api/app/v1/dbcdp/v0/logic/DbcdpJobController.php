<?php

namespace app\v1\dbcdp\v0\logic;

use app\v1\common\logic\JobController;
use app\v1\common\logic\Base;
use app\v1\dbcdp\v0\service\Service;

/**
 * note          数据库实时 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpJobController extends JobController
{
    /**
     * 启动任务
     * @param string $taskUuid  任务uuid
     * @param int    $startType 启动类型
     * @return array
     */
    public function startJob(string $taskUuid, int $startType)
    {
        // 进行启动任务的逻辑
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);
        //  启动任务
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            $taskType = xphp_get_config('task', 'TASKTYPE');

            if ($task['task_type'] == $taskType['CDP_DB_BACKUP']) {
                // 启动数据库实时同步任务
                $opName = 'BD_TASK_OP_BACKUP_START';
            } elseif ($task['task_type'] == $taskType['CDP_DB_RECOVERY']) {
                // 启动数据库实时恢复任务
                $opName = 'BD_TASK_OP_RECOVERY_START';
            }
            $msg = [
                'task_uuid' => $taskUuid,
                'control_code' => $opName,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
        }
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'start');
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

        if ($task['task_type'] == $taskType['CDP_DB_BACKUP']) {
            // 数据库同步任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        } elseif ($task['task_type'] == $taskType['CDP_DB_RECOVERY']) {
            // 数据库实时恢复
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }

        $msg = [
            'task_uuid' => $taskUuid,
            'control_code'  =>  $opName
        ];
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'delete');
    }

    /**
     * 停止任务
     * @param string $taskUuid 任务uuid
     * @return string
     */
    public function stopJob(string $taskUuid)
    {
        // 查询出任务的一些信息
        $task = $this->getDbCdpTask($taskUuid);

        $taskType = xphp_get_config('task', 'TASKTYPE');
        if ($task['task_type'] == $taskType['CDP_DB_BACKUP']) {
            // 停止同步
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } elseif ($task['task_type'] == $taskType['CDP_DB_RECOVERY']) {
            // 停止恢复
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        } elseif (
            $task['task_status'] == $taskType['TASKSTATUS']['RUNNING']
            &&
            ($task['current_task_running_stage'] == $taskType['TASK_CONTROL']['FAILBACK_DICT_IMPORT']
                ||
                $task['current_task_running_stage'] == $taskType['TASK_CONTROL']['FAILBACK_LOG_SYNC'])
        ) {
            //停止回切
            $opName = xphp_get_lang('UI_DB_CDP_SEND_MESSAGE_TO_STOP_FAILBACK');
        }

        $msg = [
            'task_uuid' => $taskUuid,
            'control_code' => $opName
        ];
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'stop');
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

        if ($task['task_type'] == $taskType['CDP_DB_BACKUP']) {
            // 暂停同步
            $opName = 'BD_TASK_OP_BACKUP_PAUSE';
        }

        $msg = [
            'task_uuid' => $taskUuid,
            'control_code' => $opName
        ];

        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'pause');
    }

    /**
     * 启动接管
     * @param array $params
     * @return string
     */
    public function startTakeOverJob(array $params = [])
    {
        // 查询出任务的一些信息
        $taskUuid = $params['job_uuids'][0];
        $task = $this->getTask($taskUuid);

        $taskType = xphp_get_config('task', 'TASKTYPE');

        if ($task['task_type'] == $taskType['CDP_DB_BACKUP']) {
            $opName = 'DB_CDP_TASK_START_TAKEOVER';
        }

        $msg = [
            'task_uuid' => $taskUuid,
            'control_code' => $opName,
            'backup_mode' => 0,
            'time_strategy_id' => 0,
            'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'takeover_timestamp' => $params['takeover_timestamp'],
            'failback_target_ip' => $params['failback_target_ip'],
            'switch_ip_flag' => v1_parse_bool_to_flag($params['switch_ip_flag']),
            'takeover_business_ip_map' => $params['takeover_business_ip_map'],
            'takeover_scn' => $params['takeover_scn'],
            'takeover_timepoint_type' => $params['takeover_timepoint_type']
        ];
        return $this->opUnifyMsg($taskUuid, $opName, $msg, false, true, $task['task_type'], 'start_takeover');
    }

    /**
     * 停止接管
     * @param array $taskUuid 任务uuid
     * @return string
     */
    public function stopTakeOverJob(array $params = [])
    {
        $taskUuid = $params['job_uuids'];
        $confirmTakeoverFlag = $params['confirmTakeoverFlag'];

        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid[0]);

        $taskType = xphp_get_config('task', 'TASKTYPE');

        if ($task['task_type'] == $taskType['CDP_DB_BACKUP']) {
            // 停止接管
            if($confirmTakeoverFlag){
                $opName = 'DB_CDP_TASK_STOP_TAKEOVER_NOT_STOP_SERVICES';
            }else{
                $opName = 'BD_TASK_OP_TAKEOVER_STOP';
            }
        }
        $msg = [
            'task_uuid' => $taskUuid[0],
            'control_code' => $opName,
            'backup_mode' => 0,
            'time_strategy_id' => 0,
            'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
        ];
        return $this->opUnifyMsg($taskUuid[0], $opName, $msg, false, true, $task['task_type'], 'stop_takeover');
    }

    /**
     * 启动回切
     * @param array $taskUuid 任务uuid
     * @return string
     */
    public function startFailback(array $taskUuid)
    {
        // 1、获取当前任务是否有进行接管回切配置
        // 2、存在则进行启动接管任务/自动接管任务回切
        // 否则提示 fail
        $array = $this->getTaskFailbackInfo($taskUuid[0]);
        if (empty($array)) {
            return [false,xphp_get_lang('UI_DB_CDP_NOT_CONFIG_FAILBACK')];
        }
        // 启动回切
        $opName = 'DB_CDP_TASK_START_FAILBACK';
        $msg = [
            'task_uuid' => $taskUuid[0],
            'control_code' => $opName
        ];
        return $this->opUnifyMsg($taskUuid[0], $opName, $msg, false, true);
    }

    /**
     * 禁用/启用自动接管
     */
    public function switchAutoTakeover($params)
    {
        $taskUuid = $params['job_uuid'][0];
        $autoTakeoverEnableFlag = $params['enable_flag'];
        $opName = 'DB_CDP_TASK_ALLOW_AUTO_TAKEOVER';
        $msg = array(
            'task_uuid' => $taskUuid,
            'allow_auto_takeover_flag' => $autoTakeoverEnableFlag
        );
        $mbResult = $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
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
     * 暂停状态修改延时重放时间和文件缓存大小等配置
     */
    public function editHighConfig($params)
    {
        $taskUuid = $params['task_uuid'];
        $opName = 'DB_CDP_TASK_MODIFY_CACHE_CONFIG';
        $msg = array(
            'task_uuid' => $params['task_uuid'],
            'file_cache_alloc_space' => $params['file_cache_alloc_space'],
            'source_file_cache_alloc_space' => $params['source_file_cache_alloc_space'],
            'delay_load_time' => $params['delay_load_time']
        );
        $mbResult = $this->opUnifyMsg($taskUuid, $opName, $msg, false, true);
        $result = $mbResult[0];
        $msg = $mbResult['msg'];
        $operate = xphp_get_lang('UI_DB_CDP_EDIT_HIGH_CONFIG');  //启用自动接管
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 根据任务uuid获取是否有进行接管回切配置
     * @param string $taskUuid 任务uuid
     * @return array
     */
    private function getTaskFailbackInfo(string $taskUuid): array
    {
        $sql = "SELECT failback_target_agent_uuid FROM cdp_db_dr_task_takeover_failback_info WHERE task_uuid =?";
        $data = $this->dbSelect($sql, array($taskUuid));

        return !empty($data) ? array_column($data, 'failback_target_agent_uuid') : [];
    }

    /**
     * 获取任务的一些信息
     * @param string $taskUuid 任务uuid
     * @param string $field    查询的字段
     * @return array
     */
    protected function getTask(string $taskUuid, string $field = 'module_type,task_type,task_status')
    {
        // 查询出任务的一些信息
        $task = $this->dbSelect("select {$field} from bd_task where task_uuid = ? ", [$taskUuid]);

        if (empty($task)) {
            return $this->muOpResult(false, '');
        }
        return $task[0];
    }

    /**
     * 获取数据库实时任务的一些信息
     * @param string $taskUuid 任务uuid
     * @param string $field    查询的字段
     * @return array
     */
    public function getDbCdpTask(
        string $taskUuid,
        string $field = 'bt.task_type,bt.task_status,cddt.current_task_running_stage'
    ) {
        // 查询出任务的一些信息
        $task = $this->dbSelect("select {$field} from bd_task bt inner join cdp_db_dr_task cddt 
                                     on bt.task_uuid = cddt.task_uuid where bt.task_uuid = ? ", [$taskUuid]);

        if (empty($task)) {
            return $this->muOpResult(false, '');
        }
        return $task[0];
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
    private function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false, $taskType = '', $operateType = '')
    {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $opName, $msg, $sync, $command);
        return $this->outMsg($mbResult, $opName, 'dbcdp', $taskType, $operateType);
    }
}
