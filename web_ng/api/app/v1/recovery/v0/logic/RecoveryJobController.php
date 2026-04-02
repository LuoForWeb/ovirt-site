<?php

namespace app\v1\recovery\v0\logic;

use app\v1\cloud\v0\logic\CloudBackup;
use app\v1\cloud\v0\logic\CloudRecover;
use app\v1\common\logic\Backup;
use app\v1\common\logic\JobController;
use app\v1\common\logic\Recover;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\BackupDataOpcode;
use app\v1\opcode\OsOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VmOpcode;
use app\v1\recovery\v0\service\Service;
use app\v1\resources\v0\logic\Node;
use app\v1\vm\v0\logic\VmBackUp;
use app\v1\vm\v0\logic\VmRecover;
use xphp\BLLHandler;

/**
 * note          高级恢复：恢复、瞬时恢复、迁移、细粒度恢复操作的逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:44
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class RecoveryJobController extends JobController
{
    /**
     * @var Service
     */
    private $service;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new Service();
    }

    /**
     * 封装获取虚拟化类型
     * @param string $taskuuid 任务uuid
     * @return int
     */
    private function getHypertype(string $taskuuid): int
    {
        // 去 bd_instant_recovery_task 表查询出虚拟化类型
        $info = $this->dbSelect(
            "select instant_target_info from bd_instant_recovery_task where task_uuid = ?",
            [$taskuuid]
        );
        $targetInfo = json_decode($info[0]['instant_target_info'], true);
        $hypervisor = $targetInfo['hypervisor_type'] ?? 0;
        if (empty($hypervisor)) {
            // 去vm_task表取
            $info = $this->dbSelect(
                "select hypervisor_type from vm_task where task_uuid = ?",
                [$taskuuid]
            );
            $hypervisor = $info[0]['hypervisor_type'] ?? 0;
        }
        return $hypervisor;
    }

    /**
     * 封装判断是什么类型的任务
     * @param string $taskuuid 任务uuid
     * @return string
     */
    private function getModuleTypes(string $taskuuid): string
    {
        $task = $this->dbSelect("select task_type,module_type,node_uuid from bd_task where task_uuid = ?", [$taskuuid]);

        $taskType = xphp_get_config('task', 'TASKTYPE');
        $moduleTYpe = xphp_get_config('module', 'MODULE_TYPE');
        if ($task[0]['task_type'] == $taskType['GRAIN_RECOVERY']) {
            // 2-55 细粒度恢复任务
            return 'grained';
        } elseif ($task[0]['task_type'] == $taskType['OS_INSTANT_RECOVERY_MOTION']) {
            // 2-50 操作系统在线迁移
            return 'os_motion';
        } elseif ($task[0]['module_type'] == $moduleTYpe['VM']) {
            // 无代理
            if ($task[0]['task_type'] == $taskType['PLATFORM_RECOVERY']) {
                // 2-53 无代理跨平台恢复
                return 'vm_recovery';
            }
            if ($task[0]['task_type'] == $taskType['INSTANT_RECOVERY']) {
                // 2-53 无代理瞬时恢复
                return 'vm_instant';
            }
            if ($task[0]['task_type'] == $taskType['INSTANT_RECOVERY_MOTION']) {
                // 2-54无代理迁移任务
                return 'vm_motion';
            }
        } elseif ($task[0]['module_type'] == $moduleTYpe['OS']) {
            if ($task[0]['task_type'] == $taskType['OS_INSTANT_RECOVERY']) {
                // 5-49有代理瞬时任务
                return 'os_instant';
            }
            if ($task[0]['task_type'] == $taskType['OS_INSTANT_RECOVERY']) {
                // 5-50有代理迁移任务
                return 'os_motion';
            }
            if ($task[0]['task_type'] == $taskType['PLATFORM_RECOVERY']) {
                // 5-52 有代理跨平台恢复
                return 'os_recovery';
            }
        } elseif ($task[0]['module_type'] == $moduleTYpe['VOL_CDP']) {
            // 实时的
            return 'cdp_recovery';
        }
        return 'vm';
    }

    /**
     * 转换时间点配置 有代理和无代理互相转换
     * @param string $agentuuid    客户端uuid
     * @param string $timpointUuid 时间点uuid
     * @return array
     */
    public function convertPointsVerify(string $agentuuid, string $timpointUuid)
    {
        $data = $this->dbSelect(
            'select module_type,task_type from bd_backup_timepoint where timepoint_uuid = ? limit 1',
            [$timpointUuid]
        );
        $opName = 'TIMEPOINT_WEB_OP_CONVERT_TIMEPOINT_CONFIG_TO_MIDDLE_CONFIG';
        $msg = [
            'module_type' => $data[0]['module_type'],
            'task_type' => $data[0]['task_type'],
            'timepoint_uuid' => $timpointUuid,
            'agent_uuid' => $agentuuid
        ];

        $mbResult = (new Service())->mbPointMsgs($opName, $msg, true);
        if ($mbResult['result']) {
            // 消息结构
            $info =  $mbResult['msg'];
            $memoryInfo = v1_calsize_to_value_and_unit(intval($info['memory_size']));
            $info['memory_size_int'] = round(floatval($memoryInfo['value']));
            $info['memory_size_unit'] = $memoryInfo['unit'];
            return array(
                'msg' => $info
            );
        }
        $operate = (new BackupDataOpcode())->getOpcodeDes($opName);
        return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
    }

    /**
     * 转换时间点配置 有代理和无代理互相转换
     * @param string $timpointUuid 时间点uuid
     * @return array
     */
    public function convertPoints(string $timpointUuid)
    {
        $data = $this->dbSelect(
            'select module_type,task_type from bd_backup_timepoint where timepoint_uuid = ? limit 1',
            [$timpointUuid]
        );
        $opName = 'TIMEPOINT_WEB_OP_CONVERT_TIMEPOINT_CONFIG_TO_MIDDLE_CONFIG';
        $msg = [
            'module_type' => $data[0]['module_type'],
            'task_type' => $data[0]['task_type'],
            'timepoint_uuid' => $timpointUuid,
            'agent_uuid' => ''
        ];

        $mbResult = (new Service())->mbPointMsgs($opName, $msg, true);
        if ($mbResult['result']) {
            // 消息结构
            return $mbResult['msg'];
        }
        $operate = (new BackupDataOpcode())->getOpcodeDes($opName);
        return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
    }

    /**
     * 启动任务 根据任务类型发送给不同的后台
     * @param string $taskuuid  任务uuid
     * @param int    $startType 启动类型
     * @return void
     */
    public function startJob(string $taskuuid, int $startType)
    {
        $type = $this->getModuleTypes($taskuuid);
        if ($type == 'grained') {
            // 细粒度恢复任务
            $opName = 'ELITE_RCVY_WEB_OP_START_GRAIN_RECOVERY_TASK';
            $msg = [
                'task_uuid' => $taskuuid,
                'recovery_mode' => 1,
                'time_strategy_id' => 0,
                'auto_start_flag' => 2
            ];
            // 发送消息到后台
            $mbResult = $this->service->mbGrainMsgs(
                $opName,
                $msg,
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_instant') {
            // 2-7 无代理瞬时恢复
            $msg = [
                'task_uuid' => $taskuuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];

            // 发送消息到后台
            $opName = 'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new VmOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_recovery') {
            // 无代理跨平台恢复
            $msg = [
                'task_uuid' => $taskuuid,
                'recovery_mode' => 0,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];

            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_START';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_instant') {
            // 有代理瞬时恢复
            $msg = [
                'task_uuid' => $taskuuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];

            // 发送消息到后台
            $opName = 'OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new OsOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_recovery') {
            // 有代理跨平台恢复
            $msg = [
                'task_uuid' => $taskuuid,
                'time_strategy_id' => 0,
                'recovery_mode' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];

            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_START';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'cdp_recovery') {
            // 有代理实时跨平台恢复
            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_START';
            $msg = [
                'task_uuid_set' => [$taskuuid],
                'control_code' => $opName,
                'backup_mode' => 1,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
            $mbResult = $this->service->mbVolCdpMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        }

        $msg = $mbResult['msg'];
        //返回结果到UI
        $result = $mbResult['result'];
        if ($result) {
            return [$result, $operate, ''];
        } else {
            return [$result, $operate, $msg, $mbResult['errorCode']];
        }
    }

    /**
     * 停止任务 根据任务类型发送给不同的后台
     * @param string $taskuuid 任务uuid
     * @return void
     */
    public function stopJob(string $taskuuid)
    {
        $type = $this->getModuleTypes($taskuuid);
        if ($type == 'grained') {
            // 细粒度恢复任务
            $opName = 'ELITE_RCVY_WEB_OP_STOP_GRAIN_RECOVERY_TASK';
            $msg = [
                'task_uuid' => $taskuuid,
            ];
            // 发送消息到后台
            $mbResult = $this->service->mbGrainMsgs(
                $opName,
                $msg,
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_instant') {
            // 2-53 无代理瞬时恢复
            $msg = [
                'task_uuid' => $taskuuid,
            ];

            // 发送消息到后台
            $opName = 'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new VmOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_motion') {
            // 2-54 无代理迁移任务
            $msg = [
                'task_uuid' => $taskuuid,
            ];

            // 发送消息到后台
            $opName = 'VM_PRIVATE_TASK_OP_STOP_ONLINE_MOTION_TASK';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new VmOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_recovery') {
            // 无代理跨平台恢复
            $msg = [
                'task_uuid' => $taskuuid,
            ];

            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_instant') {
            // 有代理瞬时恢复
            $msg = [
                'task_uuid' => $taskuuid,
            ];

            // 发送消息到后台
            $opName = 'OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new OsOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_recovery') {
            // 有代理跨平台恢复
            $msg = [
                'task_uuid' => $taskuuid,
            ];

            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_motion') {
            // 有代理迁移
            $msg = [
                'task_uuid' => $taskuuid,
            ];

            // 发送消息到后台
            $opName = 'OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new OsOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'cdp_recovery') {
            // 有代理实时跨平台恢复
            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
            $msg = [
                'task_uuid_set' => [$taskuuid],
                'control_code' => $opName
            ];
            $mbResult = $this->service->mbVolCdpMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        }

        $msg = $mbResult['msg'];
        //返回结果到UI
        $result = $mbResult['result'];
        if ($result) {
            return [$result, $operate, ''];
        } else {
            return [$result, $operate, $msg, $mbResult['errorCode']];
        }
    }

    /**
     * 删除任务 根据任务类型发送给不同的后台
     * @param string $taskuuid 任务uuid
     * @return void
     */
    public function delJob(string $taskuuid)
    {

        $type = $this->getModuleTypes($taskuuid);
        if ($type == 'grained') {
            // 细粒度恢复任务
            $opName = 'ELITE_RCVY_WEB_OP_DELETE_GRAIN_RECOVERY_TASK';
            $msg = [
                'task_uuid_list' => [$taskuuid],
            ];
            $mbResult = $this->service->mbGrainMsgs(
                $opName,
                $msg,
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid)
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_instant') {
            // 2-7 无代理瞬时恢复
            $msg = [
                'task_uuid_list' => [$taskuuid],
            ];

            // 发送消息到后台
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new VmOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'vm_recovery') {
            // 无代理跨平台恢复
            $msg = [
                'task_uuid_list' => [$taskuuid],
            ];

            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
            $mbResult = $this->service->mbVMMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $this->getHypertype($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_instant') {
            // 有代理瞬时恢复
            $msg = [
                'task_uuid_list' => [$taskuuid],
            ];

            // 发送消息到后台
            $opName = 'OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new OsOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'os_recovery') {
            // 有代理跨平台恢复
            $msg = [
                'task_uuid_list' => [$taskuuid],
            ];

            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
            $mbResult = $this->service->mbOSMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                1,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        } elseif ($type == 'cdp_recovery') {
            // 有代理实时跨平台恢复
            // 发送消息到后台
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
            $msg = [
                'task_uuid_set' => [$taskuuid],
                'control_code'  =>  $opName
            ];
            $mbResult = $this->service->mbVolCdpMsgs(
                (new Node())->getNodeUUIDWithTaskUUID($taskuuid),
                $opName,
                $msg,
                false,
                true
            );
            $operate = (new PfOpcode())->getOpcodeDes($opName);
        }

        $msg = $mbResult['msg'];
        //返回结果到UI
        $result = $mbResult['result'];
        if ($result) {
            return [$result, $operate, $msg];
        } else {
            return [$result, $operate, $msg, $mbResult['errorCode']];
        }
    }

    /**
     * 停止瞬时恢复读写
     * @param string $taskuuid 任务uuid
     * @return void
     */
    public function stopInstanceJobOperate(string $taskuuid)
    {
        $taskStatusMotion = xphp_get_config('task', 'TASK_STATUS_MOTION');
        // 更改 bd_instant_recovery_task 表的 migrate_status 为 2
        $sql = 'select id,migrate_status from bd_instant_recovery_task where task_uuid = ?';
        $data = $this->dbSelect($sql, [$taskuuid]);
        if (empty($data)) {
            return [
                'code' => -1,
                'msg' => xphp_get_lang('UI_COPY_DATA_TASK_NOT_EXIST'),
            ];
        }
        if ($data[0]['migrate_status'] != $taskStatusMotion['SHOW_STOP_MIGRATE_BUTTON']) {
            return [
                'code' => -1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN'),
            ];
        }
        $result = $this->dbExec(
            'update bd_instant_recovery_task set migrate_status = ? where task_uuid = ?',
            [$taskStatusMotion['USER_STOP_MIGRATE'], $taskuuid]
        );

        if (!empty($result)) {
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS'),
            ];
        }
        return [
            'code' => -1,
            'msg' => xphp_get_lang('WEB_PUBLIC_FAILURE'),
        ];
    }

    /**
     * 创建细粒度恢复任务 1
     * @param array $params 请求参数
     * @return array
     */
    public function createGraininessJob(array $params)
    {

        // 检查授权是否到期
        $this->licenseCheck();

        $params['job_name'] = htmlspecialchars_decode($params['job_name']);

        // 检查任务名是否重复
        $this->checkJobName($params['job_name']);

        $pointinfo = $params['point_info']; // 时间点信息
        // 需要根据时间点的信息获取真实的模块类型
        $sql = "select module_type,sub_module_type from bd_backup_timepoint where timepoint_uuid = ?";
        $points = $this->dbSelect($sql, [$pointinfo[0]['timepoint_uuid']]);

        $transportstrategy = $this->groupTransportStrategy();
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $params['job_name'],
            $points[0]['module_type'],
            2,
            2,
            [],
            $transportstrategy
        );
        $pfMSg['sub_module_type'] =  $points[0]['sub_module_type'];
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['GRAIN_RECOVERY'];

        //上面是恢复的参数,下面是细粒度参数
        $pfMSg['timepoint_uuid'] = $pointinfo[0]['timepoint_uuid'];
        $pfMSg['os_type'] = $params['os_type'];
        $pfMSg['cdp_time'] = $pointinfo[0]['cdp_datetime'] ?? '';
        // $pfMSg['node_uuid'] = (new Node())->getMasterNodeUuid();
        // 根据时间点的节点
        $pfMSg['node_uuid'] = $pointinfo[0]['node_uuid'];
        // 组合安全策略-----------------------------
        $pfMSg['safe_config_strategy'] = ($params['safe_config_strategy']); // 安全策略
        // 过载保护
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);

        $opName = 'ELITE_RCVY_WEB_OP_CREATE_GRAIN_RECOVERY_TASK';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            $pfMSg['node_uuid']
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 组合恢复虚拟机和时间点,新名字信息
     * @param array $points      时间点信息
     * @param array $recoverInfo 恢复信息
     * @param array $dirverCheck 驱动返回信息
     * @param bool  $motionFlag  是否迁移任务
     * @return array
     */
    private function groupRebuildVMInfo($points, $recoverInfo, $dirverCheck, $motionFlag = false)
    {
        $arrs = [];
        if (!empty($dirverCheck)) {
            // 处理下驱动检测结果
            foreach ($dirverCheck as $item) {
                $arrs[$item['timepoint_uuid']] = [
                    'driver_hw_id_map' => $item['driver_hw_id_map'],
                    'driver_replace_flag' => $item['platform_type'] == 2 ? 1 : 2, // 如果是异构，才会要驱动替换
                ];
            }
        }
        $pointInfo = array();
        $i = 0;
        foreach ($points as $point) {
            $info = array(
                'vm_uuid' => $point['uuid'],
                'vm_name' => $point['host_name'],
                'new_name' => $recoverInfo['vmconfigs'][$i]['vmname'],
                'timepoint_uuid' => $point['timepoint_uuid'],
                'destination_host_uuid' => $recoverInfo['node']['id'],
                'destination_vcenter_uuid' => $recoverInfo['node']['vcuuid'],
                'auto_datastore_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                'destination_datastore_name' => '',
                'start_vm_flag' => v1_parse_bool_to_flag($recoverInfo['vmconfigs'][$i]['power'] ?? false),
                'vm_config' => (new VmRecover())->groupVMConfig(
                    $recoverInfo['vmconfigs'][$i],
                    [
                        'vmuuid' => $point['uuid'], 'vmname' => $point['host_name']
                    ],
                    $point['cdp_datetime'] ?? '',
                    $motionFlag
                ),
                'extension_info' => (new VmRecover())->groupExtensionInfo($recoverInfo), // 私有云才会存在
                'project_uuid' => $point['projectid'] ?? '',
                'after_task_script' => (new VmRecover())->groupScriptConfig(
                    $recoverInfo['vmconfigs'][$i]['other']['script_data'] ?? []
                ),
                'dir_path' => $point['dir_path'],
            );
            // 驱动检测结果
            $info['extension_info'] = json_decode($info['extension_info'], true);

            if (!empty($arrs[$point['timepoint_uuid']])) {
                $driverArr = $arrs[$point['timepoint_uuid']];
                $info['extension_info']['driver_hw_id_map'] = $driverArr['driver_hw_id_map'];
                $info['extension_info']['driver_replace_flag'] = $driverArr['driver_replace_flag']; // 0不知道 1设置 2不设置
            } else {
                $info['extension_info']['driver_hw_id_map'] = '';
                $info['extension_info']['driver_replace_flag'] = 2; // 0不知道 1设置 2不设置
            }
            $info['extension_info'] = json_encode($info['extension_info']);
            $pointInfo[] = $info;
            $i++;
        }
        return $pointInfo;
    }

    /**
     * 创建无代理跨平台恢复任务
     * @param array $params 请求参数
     * @return array
     */
    public function createAgentlessisJob(array $params)
    {

        if ($params['type_info']['type'] == 2) {
            // 定时恢复, 需要判断是否小于当前时间
            if (strtotime($params['type_info']['strategy']['start_time']) < time()) {
                return [
                    'code' => -1,
                    'msg' => xphp_get_lang('UI_RECOVERY_ONCE_TIME_TIPS'),
                ];
            }
        }
        $jobname = htmlspecialchars_decode($params['job_name']); // 任务名称
        // 检查任务名是否重复
        $this->checkJobName($jobname);
        // 组装消息结构
        $strategygroupuuid = $params['strategygroupuuid'];
        $hypervisor = intval($params['recover_info']['node']['hypervisor']);

        // 判断跨平台恢复授权数量
        $this->crossHypervisorRecoveryCheck(
            array_column($params['point_info'], 'hypervisor'),
            $hypervisor,
            1
        );

        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $recoveryposition = 2; // 固定为异机恢复
        $recoverytimetype = intval($params['type_info']['type']);

        //组合时间策略
        $timeStrategyList = (new Recover())->groupRecoverTimeList($params['type_info']);

        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['type_info']['high']['trasfer'], $strategygroupuuid);
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $jobname,
            $moduletype,
            $recoveryposition,
            $recoverytimetype,
            $timeStrategyList,
            $transportstrategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['PLATFORM_RECOVERY'];

        //private params
        //disk or file
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            // 公有云恢复
            $pointArr = [];
            foreach ($params['point_info'] as $items) {
                $pointArr[$items['timepoint_uuid']] = $items['dir_path'];
            }

            $pfMSg['recovery_level'] = 1; // 恢复类型：1实例，2卷
            $pfMSg['recovery_vm_uuids'] =
                (new CloudRecover())->groupRebuildVMInfo(
                    [
                        'recover_info' => $params['recover_info']['vmconfigs'],
                        'transport_strategy' => $params['type_info']['high']['trasfer'],
                        'advanced_strategy' => [
                            'priority_snapshot_flag' => false, // 是否快照优先，默认false
                        ],
                        'points' => $pointArr
                    ]
                );

            // 处理下公有云的驱动检测问题
            if (!empty($params['driver_check'])) {
                $arrs = [];
                foreach ($params['driver_check'] as $item) {
                    $arrs[$item['timepoint_uuid']] = [
                        'driver_hw_id_map' => $item['driver_hw_id_map'],
                        'driver_replace_flag' => $item['platform_type'] == 2 ? 1 : 2, // 如果是异构，才会要驱动替换
                    ];
                }
                $info = $pfMSg['recovery_vm_uuids'];
                foreach ($info as $key => $items) {
                    $items2 = [];
                    if (!empty($arrs[$items['timepoint_uuid']])) {
                        $driverArr = $arrs[$items['timepoint_uuid']];
                        $items2['driver_hw_id_map'] = $driverArr['driver_hw_id_map'];
                        $items2['driver_replace_flag'] = $driverArr['driver_replace_flag']; // 0不知道 1设置 2不设置
                    } else {
                        $items2['driver_hw_id_map'] = '';
                        $items2['driver_replace_flag'] = 2; // 0不知道 1设置 2不设置
                    }
                    $info[$key]['extension_info'] = json_encode($items2);
                }
                $pfMSg['recovery_vm_uuids'] = $info;
            }

            $pfMSg['transport_priority'] =
                (new CloudBackup())->getTransportMode($params['type_info']['high']['trasfer']['mode']); //传输模式
            //appliance
            $pfMSg['appliance_uuid'] = $params['type_info']['high']['trasfer']['appliance_uuid'];
            $pfMSg['agent_uuid'] = $params['type_info']['high']['trasfer']['appliance_uuid'];
            //线程数量
            $pfMSg['thread_num'] = intval($params['type_info']['high']['trasfer']['thread_num']);
            //任务详情
            $pfMSg['detail'] = json_encode([
                'snapshot_priority' => v1_parse_bool_to_flag(false), // 是否快照优先，默认false
                //传输代理公有ip配置
                'agent_public_ip' => $params['type_info']['high']['trasfer']['agent_public_ip'],
                // 传输代理网络配置
                'agent_network' => $params['type_info']['high']['trasfer']['agent_network'],
            ]);
        } else {
            // 私有云和虚拟机
            $pfMSg['recovery_level'] = xphp_get_config('vm', 'VmTaskLevel')['VM'];
            $pfMSg['recovery_vm_uuids'] = $this->groupRebuildVMInfo(
                $params['point_info'],
                $params['recover_info'],
                $params['driver_check']
            );
            $pfMSg['transport_priority'] = (new VmBackUp())->getTransportMode(
                $params['type_info']['high']['trasfer']['mode'],
                $hypervisor
            );
            //appliance
            $pfMSg['appliance_uuid'] = $params['agent_uuid'];
            $pfMSg['agent_uuid'] = $params['agent_uuid'];
            $pfMSg['agent_pool_uuid'] = $params['agent_pool_uuid'];
            //线程数量
            $pfMSg['thread_num'] = intval($params['high_info']['threadnum']);
        }

        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speed_limit']);
        $pfMSg['strategy_group_uuid'] = $strategygroupuuid;

        //openstack跨平台恢复所有
        $pfMSg['cross_platform_appliance_uuid'] = $params['cross_platform_appliance_uuid'];
        //备份系统节点IP
        $pfMSg['backup_server_ip'] = $params['backup_server_ip'];
        $pfMSg['transport_ip_segment'] = $params['transport_ip_segment'];
        //新增存储媒介 存储路径 校验列表
        $pfMSg['storage_media'] = $params['storage_media'] ?? '';
        $pfMSg['storage_path'] = $params['storagepath'] ?? '';
        //时间点存储介质的uuid
        $pfMSg['storage_uuid'] = $params['storageuuid'] ?? '';
        $pfMSg['sub_module_type'] = intval($params['sub_module_type']); // 分1虚拟化、2私有云、3公有云

        // 传输压缩
        $pfMSg['source_compression'] = $params['type_info']['high']['trasfer']['transfer_compress'];
        // 重试策略
        $pfMSg['retry_strategy'] = $params['retry_strategy'];
        // 过载保护
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
        // 病毒检测和完整性校验策略
        $pfMSg['safe_config_strategy'] = $params['safe_config_strategy'];
        // 并行传输
        $pfMSg['parallel_transfer_vm_count'] = 1;
        $pfMSg['single_vm_parallel_disk_transfer_count'] =
            $params['type_info']['high']['trasfer']['single_vm_parallel_disk_transfer_count'];
        $pfMSg['vm_single_disk_parallel_transfer_count'] =
            $params['type_info']['high']['trasfer']['vm_single_disk_parallel_transfer_count'];
        // 恢复失败保留卷数据
        $pfMSg['keep_recovery_volumes'] = v1_parse_bool_to_flag($params['keep_recovery_volumes'] ?? false);

        // 发送消息到后台
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $mbResult = $this->service()->mbVMMsgs(
            $params['node_uuid'],
            $params['recover_info']['node']['hypervisor'],
            $opName,
            $pfMSg
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoverytimetype) {
                // 立即恢复，需要手动触发启动任务事件
                $this->startRecoverJob($jobname, $moduletype);
            }
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建有代理跨平台恢复任务
     * @param array $params 请求参数
     * @return array
     */
    public function createAgentJob(array $params)
    {

        if ($params['type_info']['type'] == 2) {
            // 定时恢复, 需要判断是否小于当前时间
            if (strtotime($params['type_info']['strategy']['start_time']) < time()) {
                return [
                    'code' => -1,
                    'msg' => xphp_get_lang('UI_RECOVERY_ONCE_TIME_TIPS'),
                ];
            }
        }

        // 判断跨平台恢复授权数量
        $this->crossHypervisorRecoveryCheck(
            array_column($params['point_info'], 'hypervisor'),
            xphp_get_config('vm', 'MACHINE_VM_TYPE'),
            1
        );

        $jobname = htmlspecialchars_decode($params['job_name']); // 任务名称

        // 检查任务名是否重复
        $this->checkJobName($jobname);
        // 组装消息结构
        $strategygroupuuid = $params['strategygroupuuid'];
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        if ($params['point_type'] == 3) {
            // cdp 实时
            $moduletype = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        }

        $recoveryposition = 2; // 固定为异机恢复
        $recoverytimetype = intval($params['type_info']['type']);

        //组合时间策略
        $timeStrategyList = (new Recover())->groupRecoverTimeList($params['type_info']);

        $params['high_info']['transfer']['network'] = $params['recover_info'][0]['network_uuid'];
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['high_info']['transfer'], $strategygroupuuid);
        $pfMsg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $jobname,
            $moduletype,
            $recoveryposition,
            $recoverytimetype,
            $timeStrategyList,
            $transportstrategy
        );
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['PLATFORM_RECOVERY'];

        $pfMsg['storage_uuid' ] = $params['point_info'][0]['storage_uuid'] ?? '';
        $pfMsg['sub_module_type'] = 1;
        $pfMsg['node_uuid'] = $params['node_uuid'];
        $pfMsg['transport_ip_segment'] = '';
        $pfMsg['recovery_type'] = 1; // "1:指定时间点； 2：最新时间点",
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speed_limit']);
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        // 重试策略
        $pfMsg['retry_strategy'] = $params['retry_strategy'];
        // 过载保护
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
        // 病毒检测和完整性校验策略
        $pfMsg['safe_config_strategy'] = $params['safe_config_strategy'];

        //线程数量
        $pfMsg['thread_num'] = intval($params['high_info']['threadnum']);
        $pfMsg['machine_flag'] = 1; // "1:整机恢复； 2：卷恢复",
        // 备份服务器ip
        $ip = $this->dbSelect('select ip from bd_node where node_type = 1');
        $pfMsg['backup_server_ip'] = $ip[0]['ip']; // 备份服务器ip

        // 发送消息到后台
        if ($params['point_type'] == 3) {
            // cdp 实时
            //$pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'];

            $pfMsg['script_list']['after_restore_script_location'] =
                $params['recover_info'][0]['after_recovery_script_info'];
            $pfMsg['dev_type'] = 2;
            $pfMsg['master_agent_uuid'] = $params['point_info'][0]['agent_uuid'];
            $pfMsg['restore_mode'] = 1; // 1整机 2卷
            $pfMsg['restore_data_source'] = 1;
            // 恢复目标客户端
            $pfMsg['recovery_target_agent_uuid'] = $params['recover_info'][0]['agent_uuid'];
            $pfMsg['rebuild_partition_flag'] = 1;
            // 恢复机器配置
            $cdp = $params['recover_info'][0]['cdp'];
            $cdp['restore_ip_change_flag'] =
                v1_parse_bool_to_flag($cdp['restore_ip_change_flag']);
            $cdp['driver_consistent_flag'] = 2;
            // $cdp['cross_platform_flag'] = 2;

            $cdp['recovery_timepoint_uuid'] = $params['point_info'][0]['timepoint_uuid'];
            $cdp['recovery_datetime'] = $params['point_info'][0]['cdp_datetime'];

            $pfMsg['recovery_object'] = $cdp;
            $pfMsg['timepoint_uuid'] = $params['point_info'][0]['timepoint_uuid'];
            $pfMsg['recovery_position'] = $params['recovery_position'] ?? '';
            $pfMsg['backup_server_ip'] = $params['backup_server_ip'] ?? '';

            $opName = 'BD_TASK_OP_RECOVERY_CREATE';
            $mbResult = $this->service()->mbVolCdpMsgs(
                $params['node_uuid'],
                $opName,
                $pfMsg
            );
        } else {
            //组合备份源信息-----------------------------
            // 处理下驱动检测信息的特殊字符转换
            $reoverInfo = $params['recover_info'];
            foreach ($reoverInfo as $key => $item) {
                if (!empty($item['driver_info'])) {
                    $reoverInfo[$key]['driver_info'] = htmlspecialchars_decode($item['driver_info']);
                }
            }
            $pfMsg['recovery_oss_info'] = $reoverInfo;

            $opName = 'BD_TASK_OP_RECOVERY_CREATE';
            $mbResult = $this->service()->mbOSMsgs(
                $params['node_uuid'],
                $opName,
                $pfMsg
            );
        }

        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoverytimetype) {
                // 立即恢复，需要手动触发启动任务事件
                $this->startRecoverJob($jobname, $moduletype);
            }
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName   任务名
     * @param int    $moduleType 模块名称
     * @return bool|array
     */
    private function startRecoverJob(string $taskName, int $moduleType)
    {
        $sql = "select task_uuid from bd_task
                where task_name = ?  and module_type = ? and task_type = ? order by id desc";
        $data = $this->dbSelect(
            $sql,
            [
                $taskName,
                $moduleType,
                xphp_get_config('task', 'TASKTYPE')['PLATFORM_RECOVERY']
            ]
        );
        if (!$data) {
            return false;
        }


        return $this->startJob($data[0]['task_uuid'], 1);
    }

    /**
     * 创建无代理瞬时恢复任务 1
     * @param array $params 请求参数
     * @return array
     */
    public function createInstanceLessJob(array $params)
    {

        // 判断跨平台恢复授权数量
        $this->crossHypervisorRecoveryCheck(
            array_column($params['point_info'], 'hypervisor'),
            0,
            2,
            $params['instant_target_info']['hypervisor_type']
        );

        $jobname = htmlspecialchars_decode($params['job_name']); // 任务名称

        // 检查任务名是否重复
        $this->checkJobName($jobname);
        // 组装消息结构
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $recoverytimetype = xphp_get_config('app', 'FLAG')['UNSET'];  //补齐,无用
        $transportstrategy = $this->groupTransportStrategy();
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $jobname,
            $moduletype,
            0,
            $recoverytimetype,
            [],
            $transportstrategy
        );

        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['INSTANT_RECOVERY'];
        $pfMSg['timepoint_uuid'] = $params['timepoint_uuid']; // 时间点uuid
        $pfMSg['sub_module_type'] = intval($params['sub_module_type']); // 分1虚拟化、2私有云、3公有云
        $pfMSg['mount_point_config'] = json_encode($params['mount_point_config']); // 挂载点信息
        $pfMSg['target_info'] = json_encode($params['instant_target_info']); // 恢复虚拟化信息
        $pfMSg['recovery_vms'] = $this->groupRebuildVMInfo(
            $params['point_info'],
            $params['instant_web_config'],
            $params['driver_check']
        );
        $pfMSg['safe_config_strategy'] = ($params['safe_config_strategy']); // 安全策略
        // 过载保护
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);

        // 发送消息到后台
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_INSTANT_RECOVERY_TASK';
        $mbResult = $this->service()->mbVMMsgs(
            $params['node_uuid'],
            $params['instant_target_info']['hypervisor_type'],
            $opName,
            $pfMSg
        );
        $operate = (new VmOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建有代理瞬时恢复任务 1
     * @param array $params 请求参数
     * @return array
     */
    public function createInstanceJob(array $params)
    {

        // 判断跨平台恢复授权数量
        $this->crossHypervisorRecoveryCheck(
            array_column($params['point_info'], 'hypervisor'),
            0,
            2,
            $params['instant_target_info']['hypervisor_type']
        );

        $jobname = htmlspecialchars_decode($params['job_name']); // 任务名称

        // 检查任务名是否重复
        $this->checkJobName($jobname);
        // 组装消息结构
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        $recoverytimetype = xphp_get_config('app', 'FLAG')['UNSET'];  //补齐,无用
        $transportstrategy = $this->groupTransportStrategy();
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $jobname,
            $moduletype,
            0,
            $recoverytimetype,
            [],
            $transportstrategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY'];
        $pfMSg['timepoint_uuid'] = $params['timepoint_uuid']; // 时间点uuid
        $pfMSg['sub_module_type'] = 1;
        $pfMSg['auto_migrate_flag'] = false;
        $pfMSg['stop_instant_task_flag'] = false;
        $pfMSg['script_info'] = json_encode([
            $params['instant_web_config']['script_info']
        ]);

        $pfMSg['mount_point_config'] = json_encode($params['mount_point_config']); // 挂载点信息
        $pfMSg['target_info'] = json_encode($params['instant_target_info']); // 恢复目标机信息
        $pfMSg['instant_web_config'] = ''; // 宿主机配置信息
        $pfMSg['safe_config_strategy'] = ($params['safe_config_strategy']); // 安全策略
        $pfMSg['timepoint_inst_rev_conf'] =
            !empty($params['instant_web_config']['mount_config']) ?
                [$params['instant_web_config']['mount_config']] : '';
        // 过载保护
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
        // dump(json_encode($pfMSg));
        // 发送消息到后台
        $opName = 'OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY';
        $mbResult = $this->service()->mbOSMsgs(
            $params['node_uuid'],
            $opName,
            $pfMSg
        );
        $operate = (new OsOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 根据瞬时恢复的任务uuid组装迁移需要的数据
     * @param string $jobUuid 任务uuid
     * @return array
     */
    private function makeMigrates(string $jobUuid)
    {
        $data = [];
        $jobName = (new JobInfo())->getValidName('WEB_VM_MOTION');
        $data['job_name'] = $jobName['value'];
        // 查询出时间点
        $instant = $this->dbSelect(
            'select timepoint_uuid from bd_instant_recovery_task where task_uuid = ?',
            [$jobUuid]
        );
        $data['timepoint_uuid'] = $instant[0]['timepoint_uuid'];
        $data['auto_migrate_flag'] = false;
        $data['auto_migrate_time'] = false;
        $data['stop_instant_task_flag'] = false;
        $data['auto_migrate_time'] = 10;
        $data['migrate_target_info'] = [
            'target_type' => 1,
            'target_uuid' => '',
            'hypervisor_type' => '',
            'host_uuid' => '',
        ];
        // 这个node应该取时间点所在的节点
        $timepoint = $this->dbSelect(
            'select real_node_uuid from bd_backup_timepoint where timepoint_uuid = ?',
            [$data['timepoint_uuid']]
        );
        $data['node_uuid'] = $timepoint[0]['real_node_uuid'] ?: (new Node())->getMasterNodeUuid();
        $data['migrate_target_info']['hypervisor_type'] = 47;
        $return['data'] = $data;
        $data = [];
        $data['rebuild_vm'] = [
            'vm_uuid' => '',
            'vm_name' => '',
            'new_name' => '',
            'timepoint_uuid' => $data['timepoint_uuid'],
            'destination_host_uuid' => '',
            'destination_vcenter_uuid' => '',
            'auto_datastore_flag' => '',
            'destination_datastore_name' => '',
            'start_vm_flag' => '',
            'vm_config' => json_encode([
                'orig_vm_uuid' => '',
                'orig_vm_name' => '',
                'new_vm_name' => '',
                'cpu_socket' => '',
                'cores_per_socket' => '',
                'cpu_type' => '',
                'vm_memory' => '',
                'auto_conf_flag' => '',
                'boot_mode' => '',
                'vm_version' => '',
                'vm_network_list' => [
                    [
                        'src_network_uuid' => '',
                        'src_network_name' => '',
                        'keep_mac_flag' => '',
                        'bus_type' => '',
                        'mac_addr' => '',
                        'target_network_uuid' => '',
                        'auto_conf_flag' => '',
                        'ipv4_list' => [],
                        'ipv6_list' => [],
                        'reset_nic_list' => [],
                    ]
                ],
                'vm_disk_list' => [
                    [
                        'src_disk_uuid' => '',
                        'size' => '',
                        'disk_name' => '',
                        'src_disk_name' => '',
                        'auto_conf_flag' => '',
                        'target_storage_uuid' => '',
                        'disk_type' => '',
                        'bus_type' => '',
                        'cluster_size' => '',
                        'policy_id' => '',
                        'disk_size' => '',
                        'cache_type' => '',
                        'preallocated_type' => '',
                    ]
                ],
                'zone_config' => [
                    'zone_name' => '',
                    'auto_conf_flag' => '',
                ],
                'disk_zone_config' => [
                    'zone_name' => '',
                    'auto_conf_flag' => '',
                ],
                'video_device' => [
                    'video_type' => '',
                    'vram_memory' => '',
                ],
                'other_config' => [
                    'root_password' => '',
                    'target_resource_pool_or_dir' => '',
                    'image_uuid' => '',
                    'is_ha' => '',
                    'is_cluster' => '',
                    'cpu_type' => '',
                    'emulator_type' => '',
                    'vm_flavor_id' => '',
                    'virtualization_type' => '',
                ],
                'openstack_start_mode' => '',
                'root_disk_size' => '',
                'os_type' => '',
                'os_version' => '',
                'delete_on_termination' => '',
                'flavor_id' => '',
                'original_recovery' => '',
                'image_metadata' => '',
                'reset_hostname_flag' => '',
                'new_hostname' => '',
                'reset_bios_uuid_flag' => '',
                'cdp_datetime' => '',
            ]),
            'extension_info' => json_encode([
                'group_name' => '',
                'group_uuid' => '',
                'user_name' => '',
                'password' => '',
                'controller_ip' => '',
                'driver_hw_id_map' => '',
                'driver_replace_flag' => '',
            ])
        ];
        $data['project_uuid'] = '';
        $data['after_task_script'] = '';
        $data['agent_uuid'] = '';
        $data['source_compression'] = '';
        $data['transport_priority'] = '';
        $data['thread_num'] = '';
        $data['backup_server_ip'] = '';
        $data['transport_ip_segment'] = '';
        $data['speed_limit_strategy'] = [];
        $data['retry_strategy'] = [
            'network_retry_times' => '',
            'network_retry_interval' => '',
            'op_retry_flag' => '',
            'op_retry_times' => '',
            'op_retry_interval' => '',
            'task_retry_flag' => '',
            'task_retry_object' => '',
            'task_retry_times' => '',
            'task_retry_interval' => '',
        ];
        $data['safe_config_strategy'] = [
            'worm_flag' => 0,
            'worm_protection_time' => 0,
            'virus_scan_flag' => 0,
            'virus_scan_config_list' => '',
            'integrity_check_flag' => 0,
            'integrity_check_config' => [],
        ];
        $data['task_uuid'] = $jobUuid;
        $return['msg'] = $data;
        return $return;
    }

    /**
     * 创建无代理迁移任务
     * @param array $params 请求参数
     * @return array
     */
    public function createMigratesLessJob(array $params)
    {

        // 检查授权是否到期
        $this->licenseCheck();

        $isScpMotion = false;
        if (!empty($params['is_scp_motion'])) {
            // 是scp虚拟化类型迁移，那么直接给默认配置，因为页面不能配置任何东西
            $instant = $this->makeMigrates($params['task_uuid']);
            $params = $instant['data'];
            $special = $instant['msg'];
            $isScpMotion = true;
            // 根据时间点查询出最初的虚拟化类型
            $points = $this->dbSelect(
                'select hypervisor_type from vm_backup_timepoint where timepoint_uuid = ?',
                [$params['timepoint_uuid']]
            );
            if (empty($points)) {
                $hypervisor = [0];
            } else {
                $hypervisor = array_column($points, 'hypervisor_type');
            }
            // 判断跨平台恢复授权数量
            $this->crossHypervisorRecoveryCheck(
                $hypervisor,
                47,
                3,
                47
            );
        } else {
            // 判断跨平台恢复授权数量
            $this->crossHypervisorRecoveryCheck(
                array_column($params['point_info'], 'hypervisor'),
                $params['migrate_target_info']['hypervisor_type'],
                3,
                $params['point_info'][0]['hypervisor_new']
            );
        }

        $jobname = htmlspecialchars_decode($params['job_name']); // 任务名称


        // 组装消息结构
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $recoverytimetype = xphp_get_config('app', 'FLAG')['UNSET'];  //补齐,无用
        if (!empty($params['transfer'])) {
            $params['transfer']['encrypt'] =  $params['transfer']['encrypt_flag'];
        }
        $transportstrategy = $this->groupTransportStrategy($params['transfer'] ?? []);
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $jobname,
            $moduletype,
            0,
            $recoverytimetype,
            [],
            $transportstrategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['INSTANT_RECOVERY_MOTION'];

        $pfMSg['timepoint_uuid'] = $params['timepoint_uuid']; // 时间点uuid
        $pfMSg['auto_migrate_flag'] = v1_parse_bool_to_flag($params['auto_migrate_flag']);
        $pfMSg['cache_data_interval'] = $params['auto_migrate_time'] * 60; // 手动完成的缓存数据同步间隔
        $pfMSg['stop_instant_task_flag'] = v1_parse_bool_to_flag($params['stop_instant_task_flag']);
        $pfMSg['target_info'] = json_encode($params['migrate_target_info']); // 恢复虚拟化信息

        if ($isScpMotion) {
            $pfMSg = array_merge($pfMSg, $special);
        } else {
            $pfMSg['rebuild_vm'] = $this->groupRebuildVMInfo(
                $params['point_info'],
                $params['migrate_web_config'],
                $params['driver_check'],
                true
            )[0];
            $pfMSg['agent_uuid'] = $params['transfer']['agent_uuid'];
            $pfMSg['agent_pool_uuid'] = $params['transfer']['agent_pool_uuid'];
            // 传输压缩
            $pfMSg['source_compression'] = $params['transfer']['transfer_compress'];
            // 传输
            $pfMSg['transport_priority'] = (new VmBackUp())->getTransportMode($params['transfer']['transportmode']);

            $pfMSg['thread_num'] = intval($params['transfer']['threadNum']);
            $pfMSg['task_uuid'] = $params['task_uuid'];
            $pfMSg['backup_server_ip'] = $params['transfer']['backup_server_ip'];
            $pfMSg['transport_ip_segment'] = $params['transfer']['transport_ip_segment'];
            // 全局限速策略
            $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speed_limit']);
            // 重试策略
            $pfMSg['retry_strategy'] = $params['retry_strategy'];

            $pfMSg['safe_config_strategy'] = ($params['safe_config_strategy']); // 安全策略
        }
        $pfMSg['node_uuid'] = $params['node_uuid'];
        // 节点资源池
        $pfMSg['node_pool_uuid'] = '';
        // 存储资源池
        $pfMSg['storage_pool_uuid'] = '';
        // 过载保护
        $pfMSg['ignore_resource_limiting_flag'] =
            v1_parse_bool_to_flag($params['ignore_resource_limiting_flag'] ?? false);
        $transfer = $params['transfer'];
        // 异步传输
        $pfMSg['async_rw_flag'] = v1_parse_bool_to_flag($transfer['async_transfer'] ?? false);
        // 并行传输
        $pfMSg['parallel_transfer_vm_count'] = 0;
        $pfMSg['single_vm_parallel_disk_transfer_count'] = $transfer['single_vm_parallel_disk_transfer_count'] ?? 0;
        $pfMSg['vm_single_disk_parallel_transfer_count'] = $transfer['vm_single_disk_parallel_transfer_count'] ?? 0;

        // 发送消息到后台
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK';
        $mbResult = $this->service()->mbVMMsgs(
            $params['node_uuid'],
            $params['migrate_target_info']['hypervisor_type'],
            $opName,
            $pfMSg
        );
        $operate = (new VmOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建有代理迁移任务
     * @param array $params 请求参数
     * @return array
     */
    public function createMigratesJob(array $params)
    {

        // 判断跨平台恢复授权数量
        $this->crossHypervisorRecoveryCheck(
            array_column($params['point_info'], 'hypervisor'),
            $params['migrate_target_info']['hypervisor_type'],
            3,
            $params['point_info'][0]['hypervisor_new']
        );

        $jobname = htmlspecialchars_decode($params['job_name']); // 任务名称

        $strategygroupuuid = $params['strategygroupuuid'];
        // 组装消息结构
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        $recoverytimetype = xphp_get_config('app', 'FLAG')['UNSET'];  //补齐,无用

        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['high_info']['transfer'], $strategygroupuuid ?? '');
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $jobname,
            $moduletype,
            1,
            $recoverytimetype,
            [],
            $transportstrategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['INSTANT_RECOVERY_MOTION'];
        $pfMSg['timepoint_uuid'] = $params['timepoint_uuid']; // 时间点uuid
        $pfMSg['auto_migrate_flag'] = v1_parse_bool_to_flag($params['auto_migrate_flag']);
        $pfMSg['cache_data_interval'] = $params['auto_migrate_time'] * 60; // 手动完成的缓存数据同步间隔
        $pfMSg['stop_instant_task_flag'] = v1_parse_bool_to_flag($params['stop_instant_task_flag']);
        $pfMSg['target_info'] = json_encode($params['migrate_target_info']); // 迁移目标机信息

        // 处理下驱动检测信息的特殊字符转换
        $reoverInfo = $params['migrate_web_config']; // 迁移宿主机信息
        foreach ($reoverInfo as $key => $item) {
            if (!empty($item['driver_info'])) {
                $reoverInfo[$key]['driver_info'] = htmlspecialchars_decode($item['driver_info']);
            }
        }
        $pfMSg['recovery_oss_info'] = $reoverInfo;

        $pfMSg['thread_num'] = intval($params['high_info']['threadNum']);
        $pfMSg['task_uuid'] = $params['task_uuid'];
        $pfMSg['sub_module_type'] = 1;
        $pfMSg['strategy_group_uuid'] = $strategygroupuuid ?? '';
        $pfMSg['node_uuid'] = $params['node_uuid'];
        $pfMSg['safe_config_strategy'] = ($params['safe_config_strategy']); // 安全策略

        // 备份服务器ip
        $ip = $this->dbSelect('select ip from bd_node where node_type = 1');
        $pfMSg['backup_server_ip'] = $ip[0]['ip']; // 备份服务器ip
        $pfMSg['transport_ip_segment'] = '';
        $pfMSg['recovery_type'] = 1; // "1:指定时间点； 2：最新时间点",
        $pfMSg['machine_flag'] = 1; // "1:整机恢复； 2：卷恢复",
        $pfMSg['backup_server_ip'] = '';
        $pfMSg['transport_ip_segment'] = '';
        $pfMSg['migrate_web_config'] = '';
        $pfMSg['migrate_web_extension_config'] = '';
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speed_limit']);
        // 重试策略
        $pfMSg['retry_strategy'] = $params['retry_strategy'];
        // 过载保护
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);

        // 发送消息到后台
        $opName = 'OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION';
        $mbResult = $this->service()->mbOSMsgs(
            $params['node_uuid'],
            $opName,
            $pfMSg
        );
        $operate = (new OsOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 组合传输策略
     * @param array  $transport         传输策略信息
     * @param string $strategyGroupUuid 策略组UUID
     * @return array
     */
    private function groupTransportStrategy(array $transport = [], string $strategyGroupUuid = ''): array
    {
        $set = xphp_get_config('app', 'FLAG')['SET'];
        $unset = xphp_get_config('app', 'FLAG')['UNSET'];
        return array(
            'encrypt_flag' => $transport['encrypt'] ? $set : $unset,
            'encrypt_method' => intval($transport['encrypt_method']),
            'compress_flag' => $transport['compress'] ? $set : $unset,
            'speed_limit_flag' => $transport['speedFlag'] ? $set : $unset,
            'max_speed' => intval($transport['speed']),
            'network_uuid' => !empty($transport['network']) ? $transport['network'] : '', //传输网络
            'strategy_group_uuid' => $strategyGroupUuid,
            'compress_method' => $transport['compress_method'] ?: 0,
            'block_size' => $transport['block_size'] ?: 0,
            'reconnect_times' => intval($transport['reconnect_times']),
            'reconnect_interval' => intval($transport['reconnect_interval']),
        );
    }

    /**
     * 跨平台恢复检查
     * @param array $oldHypervisors 原虚拟化平台数组
     * @param int   $newHypervisor  新虚拟化平台
     * @param int   $jobType        任务类型，默认1跨平台 2瞬时恢复 3迁移
     * @param int   $instant        瞬时恢复端虚拟化类型
     * @return void
     */
    private function crossHypervisorRecoveryCheck(
        array $oldHypervisors,
        int $newHypervisor = 0,
        int $jobType = 1,
        int $instant = 0
    ): void {
        $this->licenseCheck();

        if (empty($oldHypervisors)) {
            return;
        }
        // 需要进行一个转换，因为整机，js那边存的0
        $machine = xphp_get_config('vm', 'MACHINE_VM_TYPE');
        foreach ($oldHypervisors as &$item) {
            if ($item == 0) {
                $item = $machine;
            }
        }

        if ($newHypervisor == 0) {
            // 如果恢复的目标是 0，那么转换为整机的授权标识
            $newHypervisor = $machine;
        }

        if ($instant == 0) {
            // 如果瞬时恢复的目标是 0，那么转换为整机的授权标识
            $instant = $machine;
        }

        if (!v1_license_v2v($oldHypervisors, $newHypervisor, $jobType, $instant)) {
            // 授权数不足
            $msg = xphp_get_lang('UI_RECOVERY_CHECK_CROSS_HYPERVISOR_LICENSE_TIPS2');
            exit($this->muOpResult(false, xphp_get_lang('UI_PUBLIC_TIPS'), $msg, 'warning'));
        }
    }

    /**
     * 统一发送消息到后台
     * @param string $opName  操作码
     * @param  array  $msg     消息
     * @param bool   $sync    是否同步
     * @param bool   $command 是否命令
     * @return void
     */
    private function opUnifyMsg(string $opName, array $msg, $sync = false, $command = false)
    {
        $mbResult = (new Service())->mbPFMsgs($opName, $msg, $sync, $command);
        return $this->outMsg($mbResult, $opName);
    }

    /**
     * 判断任务名是否重复
     * @param string $jobName 任务名称
     * @return bool
     **/
    private function checkJobName(string $jobName)
    {
        $sql = 'select count(id) num from bd_task where task_name = ?';
        $data = $this->dbSelect($sql, [$jobName]);
        if (empty($data) || empty($data[0]['num'])) {
            return true;
        }
        return $this->muOpResult(
            false,
            xphp_get_lang('WEB_OPHANDLER_PARAMS_CHECK'),
            xphp_get_lang('WEB_JOB_NAME_EMPTY_ERROR'),
            'warning'
        );
    }
}
