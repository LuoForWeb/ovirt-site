<?php

namespace app\v1\common\logic;

use app\v1\opcode\OsOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VerifyOpcode;
use app\v1\opcode\VmOpcode;
use app\v1\opcode\VolcdpOpcode;
use app\v1\opcode\FcOpcode;

/**
 * note          任务操作基类
 * @author       wanggongxi@vinchin.com
 * @date         2023/5/24 11:07
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobController extends Base
{
    /**
     * 获取操作名
     * @param string $opCode opCode
     * @param string $name   模块名称
     * @return string
     */
    protected function getUnifyOpcodeDes(string $opCode, $name = '', $taskType = 0, $operateType = ''): string
    {

        if ($name == 'volcdp') {
            if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION']) { // 整机复制
                switch ($operateType) {
                    case 'start':
                        $operate = xphp_get_lang('WEB_DB_CDP_LOG_DESC_KEY_START_SYNC');
                        break;
                    case 'stop':
                        $operate = xphp_get_lang('WEB_DB_CDP_LOG_DESC_KEY_STOP_SYNC');
                        break;
                    default:
                        break;
                }
            } else {
                // 卷实时
                $operate = (new VolcdpOpcode())->getOpcodeDes($opCode);
            }
        } elseif ($name == 'dbcdp'){
            // 数据库实时
            if ($taskType == xphp_get_config('dbcdp','DBCDP_TASK_TYPE')['DB_CDP_TASK_TYPE_BACKUP']){
                switch ($operateType) {
                    case 'start':
                        $operate = xphp_get_lang('WEB_DB_CDP_LOG_DESC_KEY_START_SYNC');
                        break;
                    case 'stop':
                        $operate = xphp_get_lang('WEB_DB_CDP_LOG_DESC_KEY_STOP_SYNC');
                        break;
                    case 'delete':
                        $operate = xphp_get_lang('WEB_DB_CDP_LOG_DESC_KEY_DELETE_SYNC');
                        break;
                    default:
                        break;
                }
            }
        } else {
            $operate = '';
        }

        if (empty($operate) || $operate == xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')) {
            $pfOpcode = new PfOpcode();
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }

        if ($operate == xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')) {
            $pfOpcode = new VmOpcode();
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        if ($operate == xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')) {
            $pfOpcode = new OsOpcode();
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        if ($name == 'filecopy') {//文件复制
            $pfOpcode = new FcOpcode();
            $operate = $pfOpcode->getOpcodeDes($opCode);
        } elseif ($name == 'verify') {
            // 数据验证
            if ($opCode == 'BD_TASK_OP_START_TIMESTRATEGY') {
                $operate = (new PfOpcode())->getOpcodeDes($opCode);
            } else {
                $operate = (new VerifyOpcode())->getOpcodeDes($opCode);
            }
        }
        return $operate;
    }

    /**
     * 统一返回
     * @param array  $mbResult 与后台通信后的返回结果
     * @param string $opName   操作名
     * @param string $name     模块名称
     * @return array
     **/
    protected function outMsg(array $mbResult, string $opName, $name = '', $taskType = 0, $operateType = ''): array
    {

        if ($opName == 'BD_TASK_OP_START_TIMESTRATEGY') {
            $name = ''; //启动策略调用公共的
        }
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName, $name, $taskType, $operateType);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            return [$result, $operate, $msg];
        } else {
            return [$result, $operate, $msg, $mbResult['errorCode']];
        }
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
     * 获取历史任务的一些信息
     * @param string $taskUuid 任务uuid
     * @param string $field    查询的字段
     * @return array
     */
    protected function getHistoryTask(string $taskUuid, string $field = 'id,module_type,details')
    {
        // 查询出任务的一些信息
        $task = $this->dbSelect("select {$field} from bd_history_task where task_uuid = ? ", [$taskUuid]);

        if (empty($task)) {
            return $this->muOpResult(false, '');
        }
        return $task[0];
    }

    /**
     * 获取子模块
     * @param int    $module   模块编号
     * @param int    $task     任务类型
     * @param string $taskUuid 任务uuid
     * @return int
     */
    protected function getSubModule(int $module, int $task, string $taskUuid): int
    {
        // 副本
        if ($task == 17 || $task == 18 || $task == 19 || $task == 20) {
            $taskType = xphp_get_config('task', 'TASKTYPE');
            if ($module == 2 || $module == 17) {
                return xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE')['BACKUP_COPY_TYPE_VM_BACKUP_COPY'];  //虚拟机副本子模块
            }
            if ($module == 3) {
                return xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE')['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  //文件副本子模块
            }
            if ($module == 4) {
                return xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE')['BACKUP_COPY_TYPE_DB_BACKUP_COPY'];  //数据库副本子模块
            }
            if ($module == 5) {
                return xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE')['BACKUP_COPY_TYPE_OS_BACKUP_COPY'];  //操作系统副本子模块
            }
            if ($module == 11) {
                return xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE')['BACKUP_COPY_TYPE_NAS_BACKUP_COPY'];  //nas子模块
            }
            if (in_array($task, [$taskType['ARCHIVE'], $taskType['ARCHIVE_FETCH']])) {
                return xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE')['BACKUP_COPY_TYPE_VM_ARCHIVE'];  //虚拟机归档
            }
        }
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        //TODO 根据不同模块类型 ,添加不一样的子模块信息或其他信息
        if (in_array($module, [$moduleType['VM'], $moduleType['VDDT_SERVER']])) {
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return !empty($data) ? $data[0]['hypervisor_type'] : 0;
        }

        //公有云
        if ($moduleType['PUBLIC_CLOUD'] == $module) {
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return !empty($data) ? $data[0]['hypervisor_type'] : 0;
        }

        // 演练任务,增加恢复模式
        if ($moduleType['ORCH_TASK'] == $module) {
            $sql = "select hypervisor_type from orch_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return !empty($data) ? $data[0]['hypervisor_type'] : 0;
        }

        // 数据库任务
        if ($moduleType['DB'] == $module) {
            $sql = "select db_type from db_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return !empty($data) ? $data[0]['db_type'] : 0;
        }
        return 0;
    }
}
