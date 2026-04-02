<?php

namespace app\v1\cloud\v0\logic;

use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\tape\v0\logic\TapeInfo;
use app\v1\vm\v0\logic\VmJobInfo;
use app\v1\job\v0\logic\JobInfo as JobInfo;
use app\v1\common\logic\Backup;

/**
 * Class CloudJobInfo
 * @package app\v1\cloud\v0\logic
 */
class CloudJobInfo extends VmJobInfo
{
    /**
     * 获取任务的实例列表
     * @param array $params 参数
     * @return array
     */
    public function getJobInstances(array $params): array
    {
        $taskUuid = $params['jobs_uuid'];
        $searchName = $params['search_name'];
        $sql = "select vml.vm_name, vml.mode, vml.vm_size, vml.vm_valid_size, vml.completed_size, vml.transport_size, 
            vml.write_size, vml.task_status, vml.dir_path, vml.new_name, vml.vm_uuid, 
            vml.error_code, vml.datastore, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid, 
            bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_transport_size, 
            bri.current_object_write_size, bri.current_object_completed_size,
            bt.task_status as bd_task_status, bt.task_type, bt.module_type, vv.hypervisor_type, vt.level 
                from vm_machine_list vml 
                left join bd_running_info bri on vml.task_uuid = bri.task_uuid 
                left join vm_task vt on vml.task_uuid = vt.task_uuid 
                left join bd_task bt on bt.task_uuid = vml.task_uuid  
                left join vm_vcenter vv on vml.vcenter_uuid = vv.vcenter_uuid
                where vml.task_uuid = ? ";
        if ($searchName) {
            $sql .= " and vml.vm_name like '%{$searchName}%'";
        }
        $sql .= " group by vml.machine_id order by vml.machine_id";
        $data = $this->dbSelect($sql, array($taskUuid));
        $records = array();
        $i = 1;
        $records['rows'] = array();
        $jobInfo = JobInfo::instance();
        foreach ($data as $d) {
            //如果任务正在运行,需要过滤掉新添加的实例
            if (
                $d['bd_task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
                $d['task_status'] == xphp_get_config('vm', 'VmTaskStatus')['NEW_ADD']
            ) {
                continue;
            }
            if (
                $d['bd_task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
                $d['task_status'] == xphp_get_config('vm', 'VmTaskStatus')['UNKNOWN']
            ) {
                $records['rows'][] = array(
                    'checkbox_html' => '<input type="checkbox" name="id' . $d['vm_uuid']
                        . '" value="' . $d['vm_uuid'] . '">',
                    'no' => $i++,
                    'name' => $d['vm_name'],
                    'task_type' => xphp_get_config('app', 'NULLSPACE'),
                    'size' => xphp_get_config('app', 'NULLSPACE'),
                    'valid_size' => xphp_get_config('app', 'NULLSPACE'),
                    'transport_size' => xphp_get_config('app', 'NULLSPACE'),
                    'write_size' => xphp_get_config('app', 'NULLSPACE'),
                    'speed' => xphp_get_config('app', 'NULLSPACE'),
                    'percent' => xphp_get_config('app', 'NULLSPACE'),
                    'status' => xphp_get_config('app', 'NULLSPACE'),
                    'description' => '',
                    'detail' => $this->getVMDetailsInfo($d, $taskUuid),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'hypervisor_type' => intval($d['hypervisor_type']),
                    'vm_uuid' => $d['vm_uuid'],
                    'level' => $d['level']
                );
            } else {
                $records['rows'][] = array(
                    'checkbox_html' => '<input type="checkbox" name="id' . $d['vm_uuid']
                        . '" value="' . $d['vm_uuid'] . '">',
                    'no' => $i++,
                    'name' => $d['vm_name'],
                    //备份模式（完备、增量、差异、永久）或恢复类型（实例恢复、卷恢复）
                    'task_type' => $this->getInstanceListTaskType(
                        $d['task_type'],
                        $d['mode'],
                        $d['bd_task_status'],
                        $d['level']
                    ),
                    'size' => $this->getVMListSize($d['bd_task_status'], $d['vm_size']),
                    'valid_size' => $this->getVMListSize($d['bd_task_status'], $d['vm_valid_size']),
                    'transport_size' => $this->getVMCompletedSize(
                        $d['current_object_transport_size'],
                        $d['transport_size'],
                        $d['bd_task_status'],
                        $d['task_status']
                    ),
                    'write_size' => $this->getVMCompletedSize(
                        $d['current_object_write_size'],
                        $d['write_size'],
                        $d['bd_task_status'],
                        $d['task_status']
                    ),
                    'speed' => $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time'], $d['module_type']),
                    'percent' => $this->getVMPercent(
                        $d['vm_valid_size'],
                        $d['current_object_transport_size'],
                        $d['transport_size'],
                        $d['bd_task_status'],
                        $d['task_status']
                    ),
                    'status' => $this->getVMStatus($d['bd_task_status'], $d['task_status']),
                    'description' => $jobInfo->getErrorCodeDes($d['task_status'], $d['error_code']),
                    'detail' => $this->getVMDetailsInfo($d, $taskUuid),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'hypervisor_type' => intval($d['hypervisor_type']),
                    'vm_uuid' => $d['vm_uuid'],
                    'level' => $d['level']
                );
            }
        }
        $records['total'] = count($records['rows']);
        return $records;
    }

    /**
     * 获取任务详情基本信息
     * @param array $params 参数
     * @return array
     */
    public function getBasicInfo(array $params): array
    {
        $taskUUID = $params['job_uuid'];
        $this->paramsCheck($taskUUID);
        $jobInfo = JobInfo::instance()->getJobInfo($taskUUID); // 任务基础信息
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,bt.task_orchestration_plan_flag,
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, 
                        bt.transport_ip_segment, bt.node_uuid, bt.storage_uuid, bt.ignore_resource_limiting_flag, bt.current_stage, bt.stage_percent,
                	   bu.user_name, bt.user_uuid, unix_timestamp(bs.next_start_time) next_start_time, 
                		bri.total_object_size, bri.total_object_completed_size, 
                        bri.speed, unix_timestamp(bri.start_time) start_time, bt.backup_server_ip 
                from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs    
                where bt.user_uuid = bu.user_uuid and 
                      bt.task_uuid = bri.task_uuid and 
                      bt.strategy_id = bs.strategy_id and 
                	  bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        if (!$data) {
            return array('flag' => false);
        }
        $stageArr = xphp_get_config('task', 'COMMON_STAGE');
        foreach ($data as $d) {
            $vmTaskDetails = $this->getVMTaskDetail($d['task_uuid']);
            // 任务阶段
            $currentstage = !empty($stageArr[$d['current_stage']]) ?
                xphp_get_lang($stageArr[$d['current_stage']]) : xphp_get_config('app', 'NULLSPACE');
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => xphp_get_lang($ptDes['MODULE_TYPE_DES'][$d['module_type']]),
                'taskType' => $this->getBasicInfoTaskTypeDes(
                    xphp_get_lang($ptDes['TASKTYPEDES'][$d['task_type']]),
                    $d['module_type'],
                    $d['task_uuid']
                ),
                'user' => $d['user_name'],
                'user_uuid' => $d['user_uuid'],
                'status' => xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                'statusValue' => intval($d['task_status']),
                'totalSize' => $d['total_object_size']
                    ? v1_calsize($d['total_object_size'], true) : xphp_get_config('app')['NULLSPACE'],
                'currentSize' => $d['total_object_completed_size']
                    ? v1_calsize($d['total_object_completed_size'], true) : xphp_get_config('app')['NULLSPACE'],
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress(
                    $d['task_status'],
                    $d['total_object_size'],
                    $d['total_object_completed_size'],
                    false,
                    $d['task_type']
                ),
                'totalprogress' => $this->getTaskTotalProgress(
                    $d['task_status'],
                    $d['total_object_size'],
                    $d['total_object_completed_size'],
                    true,
                    $d['task_type']
                ),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime(
                    $d['total_object_size'],
                    $d['total_object_completed_size'],
                    $d['task_status'],
                    $d['speed']
                ),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'taskStage' => $currentstage,
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'], $d['task_uuid']),
                'transportStrategy' => $this->getTransportStrategy($taskUUID, $d['strategy_id']),
                'timeStrategyBackupType' => Backup::instance()->getTimeStrategyBackupType($d['strategy_id']),
                'storageInfo' => $jobInfo['storage_info']['flag'] ? $jobInfo['storage_info'] : $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid']),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),
                'modeStrategy' => $this->getVMBackupModeStrategy($d['task_uuid']),
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                'applianceDes' => $this->getJobApplianceInstance($d['task_uuid']),
                'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'snapshotPriority' => $this->getSnapshotPriority($d['task_uuid']),
                'selectConditions' => $this->getVMSelectConditions($d['task_uuid']),
                'autoJoinFlag' => v1_parse_flag_to_bool($vmTaskDetails['auto_join_flag'] ?? xphp_get_config('app', 'FLAG')['UNSET']),
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'tape_strategy' => TapeInfo::instance()->getTapeGroupStrategy(['group_uuid' => $d['storage_uuid']]) ?? '',
                // 重试策略
                'retry_strategy' => ExchangeJobInfo::instance()->getRetryStrategy($taskUUID),
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($d['ignore_resource_limiting_flag']),
                // 安全策略
                'safe_strategy' => $jobInfo['safe_strategy'],
            );
        }
        return $basicInfo;
    }

    /**
     * 得到实例列表任务类型描述
     * @param int $taskType      任务类型：1备份，2恢复
     * @param int $currentMode   任务模式：备份模式（1完备，2增量，3差异，9永久）
     * @param int $taskStatus    任务状态
     * @param int $recoveryLevel 恢复模式（1实例，2卷）
     * @return string
     */
    private function getInstanceListTaskType(
        int $taskType,
        int $currentMode,
        int $taskStatus,
        int $recoveryLevel
    ): string {
        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //如果任务不在运行状态,这个时候不知道任务时什么类型的
            return xphp_get_config('app', 'NULLSPACE');
        }
        if (xphp_get_config('task', 'TASKTYPE')['RECOVERY'] == $taskType) {
            if (xphp_get_config('vm', 'VmTaskLevel')['VM'] == $recoveryLevel) {
                return xphp_get_lang('UI_RECOVERY_AWS_INSTANCE_RECOVER');
            } else {
                return xphp_get_lang('UI_RECOVERY_AWS_VOL_RECOVER');
            }
        }
        return JobInfo::instance()->getHistoryTaskType($taskType, $currentMode, '');
    }

    /**
     * 获取传输代理实例类型
     * @param string $taskUuid 任务uuid
     * @return string
     */
    private function getJobApplianceInstance(string $taskUuid): string
    {
        $sql = "select vm_config from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, [$taskUuid]);
        if ($data) {
            $re = json_decode($data[0]['vm_config'], true)['agent_type'];
            return $re ?? xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        } else {
            return xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        }
    }

    /**
     * 重载方法，得到备份任务实例详情
     * @param array $d 实例信息
     * @return array
     */
    protected function getBackupDetailsInfo(array $d): array
    {
        return array(
            'type' => intval($d['task_type']), //任务类型：1备份，2恢复
            'spath' => $d['dir_path'], //源实例路径
        );
    }

    /**
     * 重载方法，得到恢复任务实例详情
     * @param array  $d        实例信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    protected function getRecoveryDetailsInfo(array $d, string $taskUuid): array
    {
        $desHostInfo = $this->getDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $timepointDes = $timepointInfo['taskname'] . ' => ' . $timepointInfo['timepoint'];
        //时间点被意外删除
        if (empty($timepointInfo)) {
            $timepointDes = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }
        $detailsInfo = $this->getTaskDetailsInfo($taskUuid);
        return array(
            'type' => intval($d['task_type']), //任务类型：1备份，2恢复
            'spath' => $d['dir_path'], //源实例路径
            'tpath' => $desHostInfo['vcenterName'] . '/' . $desHostInfo['hostName'] . '/' . $d['new_name'], //目标实例路径
            'timepoint_des' => $timepointDes, //时间点描述
            'volume_info' => $detailsInfo['volume_info'] ?? [], //卷恢复的卷信息
        );
    }

    /**
     * 重载方法，得到vcentername和hostname
     * @param string $vcenterUuid 虚拟化平台uuid
     * @param string $hostUuid    主机uuid
     * @return array
     */
    protected function getDesHostInfo(string $vcenterUuid, string $hostUuid): array
    {
        $sql = "select vh.host_name, vv.nickname
                from  vm_host vh, vm_vcenter vv
                where vh.vcenter_uuid = vv.vcenter_uuid
                and vv.vcenter_uuid = ? and vh.host_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenterUuid, $hostUuid));
        if ($data) {
            return array(
                'hostName' => $data[0]['host_name'],
                'vcenterName' => $data[0]['nickname'],
            );
        }
        return ['hostName' => '', 'vcenterName' => ''];
    }

    /**
     * 获取任务详情
     * @param string $taskUuid 任务uuid
     * @return array
     */
    private function getTaskDetailsInfo(string $taskUuid): array
    {
        $sql = "select detail from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, [$taskUuid]);
        if ($data) {
            $re = json_decode($data[0]['detail'], true);
            return $re ?? [];
        }
        return [];
    }

    /**
     * 获取恢复任务是否开启优先快照模式
     * @param string $taskUuid 任务uuid
     * @return bool
     */
    private function getSnapshotPriority(string $taskUuid): bool
    {
        $sql = "select vm_config from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, [$taskUuid]);
        if ($data) {
            $re = json_decode($data[0]['vm_config'], true)['snapshot_priority'];
            return $re ?? true;
        }
        return true;
    }
}
