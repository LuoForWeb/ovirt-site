<?php

namespace app\v1\recovery\v0\logic;

use app\v1\job\v0\logic\JobInfo as JobInfos;
use app\v1\complete_machine_volcdp\v0\logic\CmJobInfo;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\vm\v0\logic\VmJobInfo;
use app\v1\vm\v0\logic\VmRecover;
use app\v1\volcdp\v0\logic\VolcdpJobInfo;

/**
 * note          高级恢复：恢复、瞬时恢复、迁移、细粒度恢复 信息展示的逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:44
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class RecoveryJobInfo extends JobInfos
{
    /**
     * 根据任务阶段和速度组装返回
     * @param int $stage   阶段值
     * @param int $percent 进度
     * @param int $status  任务状态
     * @param int $module  所属模块
     * @return string
     */
    private function makeJobStage(int $stage, int $percent, int $status, int $module)
    {
        $statusArr = xphp_get_config('task', 'TASKSTATUS');
        if ($status == $statusArr['WAITTING']) {
            // 任务在等待阶段显示 --
            return xphp_get_config('app', 'NULLSPACE');
        }

        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        if ($module == $moduleType['VOL_CDP']) {
            // 卷实时
            $stageArr = xphp_get_config('task', 'REAL_PROTECT_STAGE');
        } elseif ($module == $moduleType['DB_CDP']) {
            // 数据库实时
            $stageArr = xphp_get_config('task', 'DB_CDP_PROTECT_STAGE');
        } else {
            $stageArr = xphp_get_config('task', 'COMMON_STAGE');
        }
        $currentstage = !empty($stageArr[$stage]) ?
            xphp_get_lang($stageArr[$stage]) : xphp_get_config('app', 'NULLSPACE');
        if ($percent != -1) {
            $currentstage .= '(' . $percent . '%)';
        }
        return $currentstage;
    }

    /**
     * 细粒度恢复任务详情
     * @param array $params 请求参数
     * @return array
     */
    public function graininessJobDetail(array $params)
    {
        $jobuuid = $params['recovery_uuid']; // 任务uuid

        $sql = "select bt.task_status, bt.module_type, bt.task_type, bbt.timepoint,vbt.vm_name,bbt.timepoint_uuid,
                bbt.backup_mode,obt.os_name,bgrt.cdp_time,cvba.master_agent_detail,bt.node_uuid,
                bt.task_name,bt.current_stage,bt.stage_percent,bgrt.os_type,obt.agent_ip,bnn.ip,
                bt.ignore_resource_limiting_flag,bt.user_uuid
                from  bd_task bt, bd_node_network bnn, bd_grain_recovery_task bgrt
                left join bd_backup_timepoint bbt on bgrt.timepoint_uuid = bbt.timepoint_uuid
                left join vm_backup_timepoint vbt on bgrt.timepoint_uuid = vbt.timepoint_uuid
                left join os_backup_timepoint obt on bgrt.timepoint_uuid = obt.timepoint_uuid
				left join cdp_vol_backup_agent cvba on cvba.timepoint_uuid = bgrt.timepoint_uuid
                where bt.task_uuid = bgrt.task_uuid and bnn.node_uuid = bt.node_uuid and bnn.type = 1 and
                bt.task_uuid = ? order by bnn.network_order asc limit 1";
        $data = $this->dbSelect($sql, array($jobuuid));
        $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $sqlSafe = "select btsc.virus_scan_config_list, btsc.recovery_integrity_check_error_policy
                    from  bd_task_safe_config btsc
                    where btsc.task_uuid = ?";
        $dataSafe = $this->dbSelect($sqlSafe, array($jobuuid));
        $detail = json_decode($dataSafe[0]['virus_scan_config_list'], true);
        $recoverpolicy = $detail[0]['recover_policy'];
        $interruptpolicy = $detail[0]['interrupt_policy'];
        $vmName = $data[0]['vm_name'] ?: ($data[0]['os_name'] . '(' . $data[0]['agent_ip'] . ')');
        if (empty($data[0]['vm_name']) && empty($data[0]['os_name']) && !empty($data[0]['master_agent_detail'])) {
            $cdp = json_decode($data[0]['master_agent_detail'], true);
            $vmName = $cdp['hostname'] . '(' . $cdp['ip'] . ')';
        }

        // 查询是否有正在运行的客户端传输和网络共享任务
        $sqlParams = [$jobuuid, 2];
        $count1 = $this->dbSelect(
            'select count(*) as num from bd_grain_recovery_share where task_uuid = ? and task_status = ?',
            $sqlParams
        );
        $count2 = $this->dbSelect(
            'select count(*) as num from bd_grain_recovery_transport where task_uuid = ? and task_status = ?',
            $sqlParams
        );

        return [
            'status' => intval($data[0]['task_status']),
            'timepoint' => !empty($data[0]['cdp_time']) ? $data[0]['cdp_time'] : $data[0]['timepoint'],
            'module' => intval($data[0]['module_type']),
            'task_type' => intval($data[0]['task_type']),
            'is_cdp' => !empty($data[0]['cdp_time']), // 源是否是实时
            'recover_policy' => $recoverpolicy,
            'interrupt_policy' => $interruptpolicy,
            'node_uuid' => $data[0]['node_uuid'],
            'node_ip' => $data[0]['ip'],
            'job_name' => $data[0]['task_name'],
            'os_type' => $data[0]['os_type'],
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
            'current_stage_value' => $this->makeJobStage(
                $data[0]['current_stage'],
                $data[0]['stage_percent'],
                $data[0]['task_status'],
                $data[0]['module_type']
            ),
            'vm_name' => $vmName ?? '--',
            'recovery_error_policy' => $dataSafe[0]['recovery_integrity_check_error_policy'],
            'status_des' => xphp_get_lang($ptDes[$data[0]['task_status']] ?? 'unknown'),
            'share_num' => $count1[0]['num'] ?? 0, // 是否有运行中的共享任务
            'transfer_num' => $count2[0]['num'] ?? 0, // 是否有运行中的传输任务
            'safe_strategy' => $this->getSafeConfigStrategy($jobuuid),
            'user_uuid' => $data[0]['user_uuid']
        ];
    }

    /**
     * 跨平台恢复任务详情
     * @param array $params 请求参数
     * @return array
     */
    public function jobDetail(array $params)
    {
        $jobuuid = $params['detail_uuid']; // 任务uuid
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        bt.strategy_id, unix_timestamp(bt.create_time) create_time,bt.transport_ip_segment,bt.sub_module_type,
        bt.node_uuid, bt.storage_uuid, unix_timestamp(bs.next_start_time) next_start_time,bt.user_uuid,
        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time
        ,bt.current_stage,bt.stage_percent,bri.total_object_completed_valid_size,vol_task.current_task_running_stage
        from bd_task bt 
        left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
        left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
        left join cdp_vol_task vol_task on bt.task_uuid = vol_task.task_uuid 
        where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobuuid));
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_COPY_DATA_TASK_NOT_EXIST')
            ];
        }

        $taskstatusDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $tasktypeDes = xphp_get_desc('Pf', 'TASKTYPEDES');
        $info = $data[0];
        $currentSize = v1_calsize($info['total_object_completed_size'], true);
        $progress = $this->getTaskTotalProgress(
            $info['task_status'],
            $info['total_object_size'],
            $info['total_object_completed_size'],
            false,
            $info['task_type']
        );
        $totalprogress = $this->getTaskTotalProgress(
            $info['task_status'],
            $info['total_object_size'],
            $info['total_object_completed_size'],
            true,
            $info['task_type']
        );
        $totalSize = v1_calsize($info['total_object_size'], true);
        $currentStage = $this->makeJobStage(
            $info['current_stage'],
            $info['stage_percent'],
            $info['task_status'],
            $info['module_type']
        );
        if ($info['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP']) {
            // 实时的已完成容量单独计算
            $currentStage = $this->makeJobStage(
                $info['current_task_running_stage'],
                $info['stage_percent'],
                $info['task_status'],
                $info['module_type']
            );
            $valueFlag = false;
            $currentSizeValue = 0;
            $totalObjectSize = $info['total_object_size'];
            $completedValidSize = $info['total_object_completed_valid_size'];
            if ($totalObjectSize != 0 && $completedValidSize != 0) {
                $valueFlag = true;
                $currentSizeValue = (new CmJobInfo())->getTaskTotalCapacityInfo($jobuuid);
            }
            if (!(is_numeric($currentSizeValue))) {
                $valueFlag = false;
            }
            $currentSize = v1_calsize($currentSizeValue, $valueFlag);
            $totalSize = v1_calsize($info['total_object_size'], $valueFlag);

            $progress = $this->getTaskTotalProgress(
                $info['task_status'],
                $info['total_object_size'],
                $currentSizeValue,
                false,
                $info['task_type']
            );

            $totalprogress = $this->getTaskTotalProgress(
                $info['task_status'],
                $info['total_object_size'],
                $currentSizeValue,
                true,
                $info['task_type']
            );


            $currentTaskRunningStage = $info['current_task_running_stage'];
            //任务处于实时同步阶段或逆向实时同步阶段
            $taskRunningStage = xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE');
            if (
                $currentTaskRunningStage == $taskRunningStage['REALTIME_SYNC']
                || $currentTaskRunningStage == $taskRunningStage['WAIT_CONVERT_TO_REALTIME_SYNC']
                || $currentTaskRunningStage == $taskRunningStage['FAILBACK_REALTIME_SYNC']
            ) {
                $currentSize = $this->getTaskCurrentTotalSize($info['task_uuid']);
            }

            //服务端数据一致性校验阶段
            if ($currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['SERVER_CONS_CHECK']) {
                $serverConsCheckInfo = (new VolcdpJobInfo())->getServerConsCheckInfo($jobuuid);
                $totalSize = v1_calsize($serverConsCheckInfo['total_size']);
                $currentSize = v1_calsize($serverConsCheckInfo['completed_size']);
                $consistencyCheckVol = $serverConsCheckInfo['current_vol'];
                $totalprogress = $serverConsCheckInfo['total_progress'];
                $progress = $totalprogress;
            }
        }

        $basicInfo = array(
            //任务名
            'job_name' => $info['task_name'],
            //任务状态
            'job_status_value' => $taskstatusDes[$info['task_status']],
            'job_status' => $info['task_status'],
            'current_stage_value' => $currentStage,
            //任务总容量
            'total_size' => $totalSize,
            //已处理容量
            'current_size' => $currentSize,
            //开始时间
            'start_time' => $this->getStartTIme($info['start_time'], $info['task_status']),
            //持续时间
            'interval_time' => $this->getTimeInterval($info['start_time'], $info['task_status']),
            //创建/修改时间
            'create_time' => $this->parseDate($info['create_time']),
            //下次开始时间
            'next_time' => $this->getNextStartTime($info['next_start_time'], $info['task_status']),
            //任务状态int
            'status_num' => $info['task_status'],
            //任务进度
            'progress' => $progress,
            //任务进度百分比
            'total_progress' => $totalprogress,
            'speed' => $this->getJobSpeed($info['task_status'], $info['speed']),
            'job_type_des' => $tasktypeDes[$info['task_type']],
            'end_time' => $this->getCalEndTime(
                $info['total_object_size'],
                $info['total_object_completed_size'],
                $info['task_status'],
                $info['speed']
            ),
            'task_status' => $info['task_status'],
            'module_type' => $info['module_type'],
            'sub_module_type' => $info['sub_module_type'],
            'job_type' => intval($info['task_type']),
            'transport_ip_segment' => $info['transport_ip_segment'],
            'user_uuid' => $info['user_uuid']
        );

        if ($params['simple'] == 2) {
            // 根据module查询出虚拟机和整机的各个不同的策略信息
            $strategy = $this->getSteategyDetail(
                $jobuuid,
                $basicInfo['module_type'],
                1,
                $info['current_task_running_stage']
            );
            $basicInfo = array_merge($basicInfo, $strategy);
            $basicInfo['safe_strategy'] = $this->getSafeConfigStrategy($jobuuid);
        }

        return [
            'code' => 0,
            'msg' => $basicInfo
        ];
    }

    /**
     * 瞬时恢复任务详情
     * @param array $params 请求参数
     * @return array
     */
    public function instanceJobDetail(array $params)
    {
        $jobuuid = $params['recovery_uuid']; // 任务uuid

        $sql  = "select bt.task_status,bt.current_stage,bt.stage_percent,bt.ignore_resource_limiting_flag,
                        bt.task_type, bt.task_name,bt.module_type,bt.task_uuid,birt.instant_machine_status,
                        unix_timestamp(bri.start_time) start_time, unix_timestamp(bt.create_time) create_time
                        ,birt.mount_point_config,birt.instant_machine_name as vm_name,birt.instant_target_info,
                        bt.user_uuid
                from bd_task bt
                left join bd_instant_recovery_task birt on bt.task_uuid = birt.task_uuid
                left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
                where bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($jobuuid));
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_COPY_DATA_TASK_NOT_EXIST')
            ];
        }

        $taskStatusDes = xphp_get_config('task', 'TASKSTATUSDES');
        $tasktypeDes = xphp_get_desc('Pf', 'TASKTYPEDES');
        $mountConfig = json_decode($data[0]['mount_point_config'], true);

        // 需要查询出host的ip信息
        $hostArr = json_decode($data[0]['instant_target_info'], true);
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        if ($data[0]['module_type'] == $moduleArr['VM']) {
            if ($hostArr['hypervisor_type'] == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD']) {
                // 如果是内嵌的需要，查询出跳转的url
                $host[0]['host_ip'] = xphp_get_lang('UI_VM_MACHINE_LIST');
                $url = $this->dbSelect('select console_url,uuid,`name` from vm_emd where task_uuid = ?', [$jobuuid]);
                if (!empty($url)) {
                    $newconsoleurl = str_replace(
                        '0.0.0.0:6080',
                        $_SERVER['HTTP_HOST'] . '/web_console',
                        $url[0]['console_url']
                    );
                    $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] .
                        ':6080/jump.html?url=' . urlencode($newconsoleurl);
                }
                $vmUuid = $url[0]['uuid'];
                $vmName = $url[0]['name'];
            } else {
                $host = $this->dbSelect(
                    "select host_ip,host_name from vm_host where vcenter_uuid = ? and host_uuid = ?",
                    [$hostArr['target_uuid'], $hostArr['host_uuid']]
                );
            }

            $goal = xphp_get_lang('UI_HOMEPAGE_VM_PLATFORM');
            // 还需要查询出具体的虚拟化类型 如 私有云[OpenStack]
            $hypervisor = $this->getVersionByHyper($hostArr['hypervisor_type']);
        } else {
            // 整机
            $host = $this->dbSelect(
                "select ip host_ip,hostname host_name from bd_agent where agent_uuid = ?",
                [$hostArr['target_uuid']]
            );
            $goal = xphp_get_lang('UI_COMPLETE_MACHINE');
        }

        $basicInfo = [
            'job_name' => $data[0]['task_name'],
            'server_ip' => $mountConfig['ip'],
            'mount_service' => $mountConfig['service_txt'],
            'host_ip' => $host[0]['host_ip'],
            'online_flag' => $data[0]['instant_machine_status'] == xphp_get_config('vm', 'MACHINESTATUS')['POWEREDON'],
            'job_status' => $data[0]['task_status'],
            'job_status_des' => xphp_get_lang($taskStatusDes[$data[0]['task_status']]),
            'job_type' => $data[0]['task_type'],
            'module_type' => $data[0]['module_type'],
            'job_type_des' =>  $tasktypeDes[$data[0]['task_type']],
            'current_stage_value' => $this->makeJobStage(
                $data[0]['current_stage'],
                $data[0]['stage_percent'],
                $data[0]['task_status'],
                $data[0]['module_type']
            ),
            //开始时间
            'start_time' => $this->getStartTIme($data[0]['start_time'], $data[0]['task_status']),
            //持续时间
            'interval_time' => $this->getTimeInterval($data[0]['start_time'], $data[0]['task_status']),
            //创建/修改时间
            'create_time' => $this->parseDate($data[0]['create_time']),
            'goal_type' => $goal, // 目标类型
            'hypervisor' => $hypervisor ?? '', // 虚拟化类型
            'hypervisor_type' => intval($hostArr['hypervisor_type']), // 虚拟化类型
            'vm_name' => $vmName ?? '', // 内嵌机器名称
            'console_url' => $newconsoleurl ?? '', // hypervisor_type 为内嵌的时候的跳转url
            'vm_uuid' => $vmUuid ?? '', // hypervisor_type 为内嵌的时候的跳转的机器uuid
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
            'user_uuid' => $data[0]['user_uuid']
        ];
        if ($params['simple'] == 2) {
            $basicInfo['safe_strategy'] = $this->getSafeConfigStrategy($jobuuid);
        }

        return [
            'code' => 0,
            'msg' => $basicInfo
        ];
    }

    /**
     * 迁移任务详情
     * @param array $params 请求参数
     * @return array
     */
    public function migratesJobDetail(array $params)
    {

        $jobuuid = $params['recovery_uuid'];
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        bt.strategy_id, unix_timestamp(bt.create_time) create_time,bt.transport_ip_segment,
        bt.node_uuid, bt.storage_uuid, unix_timestamp(bs.next_start_time) next_start_time,
        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time,
        birt.instant_machine_name, birt.migrate_machine_name,birt.mount_point_config,birt.migrate_phase,
		birt.auto_migrate_flag, birt.stop_instant_task_flag,birt.migrate_target_info,birt.migrate_status,
        birt.cache_data_interval,bt.ignore_resource_limiting_flag,bt.user_uuid
        from bd_task bt 
        left join bd_instant_recovery_task birt on bt.task_uuid = birt.task_uuid 
        left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
        left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
        where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobuuid));
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_COPY_DATA_TASK_NOT_EXIST')
            ];
        }

        $taskstatusDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $tasktypeDes = xphp_get_desc('Pf', 'TASKTYPEDES');
        $stageDes = xphp_get_config('task', 'TASK_STAGE_MOTION');
        $info = $data[0];

        // 需要查询出host的ip信息
        $hostArr = json_decode($info['migrate_target_info'], true);
        if ($hostArr['target_type'] == 1 || !empty($hostArr['hypervisor_type'])) {
            $host = $this->dbSelect(
                "select host_ip,host_name from vm_host where vcenter_uuid = ? and host_uuid = ?",
                [$hostArr['target_uuid'], $hostArr['host_uuid']]
            );
        } else {
            // 整机
            $host = $this->dbSelect(
                "select ip host_ip,hostname host_name from bd_agent where agent_uuid = ?",
                [$hostArr['target_uuid']]
            );
        }

        $mountConfig = json_decode($info['mount_point_config'], true);
        $basicInfo = array(
            //任务名
            'job_name' => $info['task_name'],
            //任务状态
            'job_status_value' => $taskstatusDes[$info['task_status']],
            'job_status' => $info['task_status'],
            //任务总容量
            'total_size' => v1_calsize($info['total_object_size'], true),
            //已处理容量
            'current_size' => v1_calsize($info['total_object_completed_size'], true),
            //开始时间
            'start_time' => $this->getStartTIme($info['start_time'], $info['task_status']),
            //持续时间
            'interval_time' => $this->getTimeInterval($info['start_time'], $info['task_status']),
            //创建/修改时间
            'create_time' => $this->parseDate($info['create_time']),
            //下次开始时间
            'next_time' => $this->getNextStartTime($info['next_start_time'], $info['task_status']),
            //任务状态int
            'status_num' => $info['task_status'],
            //任务进度
            'progress' => $this->getTaskTotalProgress(
                $info['task_status'],
                $info['total_object_size'],
                $info['total_object_completed_size'],
                false,
                $info['task_type']
            ),
            //任务进度百分比
            'total_progress' => $this->getTaskTotalProgress(
                $info['task_status'],
                $info['total_object_size'],
                $info['total_object_completed_size'],
                true,
                $info['task_type']
            ),

            'speed' => $this->getJobSpeed($info['task_status'], $info['speed']),
            'job_type_des' =>  $tasktypeDes[$info['task_type']],
            'end_time' => $this->getCalEndTime(
                $info['total_object_size'],
                $info['total_object_completed_size'],
                $info['task_status'],
                $info['speed']
            ),
            'server_ip' => $mountConfig['ip'],
            'host_ip' => $host[0]['host_ip'],
            'online_flag' => true,
            'task_status' => $info['task_status'],
            'stage' => $info['migrate_phase'] == 0 ? '--' : xphp_get_lang($stageDes[$info['migrate_phase']]),
            'recover_hostname' => $info['instant_machine_name'],
            'motion_hostname' => $info['migrate_machine_name'] ?? '--',
            'module_type' => $info['module_type'],
            'auto_migrate_flag' => v1_parse_flag_to_bool($info['auto_migrate_flag']),
            'auto_migrate_interval' => $info['cache_data_interval'] / 60, // 手动完成的缓存数据同步间隔
            'stop_instant_task_flag' => v1_parse_flag_to_bool($info['stop_instant_task_flag']),
            'job_type' => intval($info['task_type']),//用于判断是否跳转到瞬时恢复页面
            'migrate_status' => intval($info['migrate_status']),//用于判断是否 显示停止瞬时恢复读写按钮
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($info['ignore_resource_limiting_flag']),
            'user_uuid' => $info['user_uuid']
        );

        // 因为整机的迁移不会去更改模块类型，所以我这边需要通过任务类型来判断到底是什么模块的
        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        if ($basicInfo['job_type'] == $taskTypeArr['OS_INSTANT_RECOVERY_MOTION']) {
            // 是整机的迁移
            $basicInfo['module_type'] = $moduleTypeArr['OS'];
        }

        if ($params['simple'] == 2) {
            $strategy = $this->getSteategyDetail($jobuuid, $basicInfo['module_type'], 2);
            $basicInfo = array_merge($basicInfo, $strategy);
        }

        return [
            'code' => 0,
            'msg' => $basicInfo
        ];
    }

    /**
     * 获取跨平台、瞬时恢复和迁移任务详情的对象列表（包括vm和整机的）
     * @param array $params 请求参数
     * @return array
     */
    public function getJobObject(array $params)
    {
        $jobUuid = $params['job_uuid'];
        $job = $this->dbSelect('select module_type,task_type from bd_task where task_uuid = ?', [$jobUuid]);
        if (empty($job)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $search = v1_escape_wildcard($params['search'] ?? '');

        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $scriptType = xphp_get_desc('Script', 'SCRIPT_TYPE');
        // 这里判断下整机的迁移任务类型
        if ($job[0]['task_type'] == $taskType['OS_INSTANT_RECOVERY_MOTION']) {
            // 因为整机的迁移不会去更改瞬时恢复的任务的模块类型，所以只能这样判断
            $job[0]['module_type'] = $moduleType['OS'];
        }
        $records = [];
        if ($job[0]['module_type'] == $moduleType['VM']) {
            // 虚拟化
            $sqlr = 'vml.task_uuid = ?';
            $paramss = [$jobUuid];

            if (in_array($job[0]['task_type'], [$taskType['INSTANT_RECOVERY'], $taskType['INSTANT_RECOVERY_MOTION']])) {
                // 如果是瞬时恢复或者迁移任务，需要根据 bd_instant_recovery_task 表的extension_info去取关联的 machine_id 集合
                $extension = $this->dbSelect(
                    'select extension_info from bd_instant_recovery_task where task_uuid = ?',
                    [$jobUuid]
                );
                $extensionInfo = json_decode($extension[0]['extension_info'], true);
                $type = [
                    $taskType['INSTANT_RECOVERY'] => 'instant_machine_ids', // 瞬时恢复对应的
                    $taskType['INSTANT_RECOVERY_MOTION'] => 'migrate_machine_ids', // 迁移对应的
                ];
                $machine = $extensionInfo[$type[$job[0]['task_type']]];
                if (!empty($machine)) {
                    $machineList = implode(',', $machine);
                    $sqlr = "vml.machine_id in ($machineList)";
                    $paramss = [];
                }
            }
            if (!empty($search)) {
                $sqlr .= " and vml.new_name like '%{$search}%' ";
            }
            $sqlCount = "select count(distinct vml.machine_id) as num
                from vm_machine_list vml 
                left join bd_running_info bri 
                on vml.task_uuid = bri.task_uuid 
                left join bd_task bt 
                on bt.task_uuid = vml.task_uuid  
                left join vm_vcenter vv
                on vml.vcenter_uuid = vv.vcenter_uuid
                where {$sqlr}";

            $total = $this->dbSelect($sqlCount, $paramss);
            if (empty($total[0]['num'])) {
                return [
                    'rows' => [],
                    'total' => 0
                ];
            }
            $sql = "select vml.vm_name, vml.mode, vml.vm_size, vml.vm_valid_size, vml.completed_size,
            vml.transport_size, vml.write_size, vml.task_status, vml.dir_path, vml.new_name, vml.vm_uuid, 
            vml.error_code, vml.datastore, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid, 
            vml.new_name,vml.vm_config,vml.after_task_script,
            bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_transport_size, 
            bri.current_object_write_size, bri.current_object_completed_size,
            bt.task_status as bd_task_status, bt.task_type, bt.module_type, vv.hypervisor_type
                from vm_machine_list vml 
                left join bd_running_info bri 
                on vml.task_uuid = bri.task_uuid 
                left join bd_task bt 
                on bt.task_uuid = vml.task_uuid  
                left join vm_vcenter vv
                on vml.vcenter_uuid = vv.vcenter_uuid
                where {$sqlr} group by vml.machine_id order by vml.machine_id limit {$offset},{$limit}";
            $data = $this->dbSelect($sql, $paramss);
            $i = 1;
            $records['rows'] = [];
            foreach ($data as $d) {
                $scriptlist = [
                    'after_task_script' => []
                ];
                // 获取恢复后脚本
                $afterScript = json_decode($d['after_task_script'], true);
                if (!empty($afterScript)) {
                    foreach ($afterScript as $value) {
                        //获取脚本名称
                        $scriptlist['after_task_script'][] =
                            $value['script_name'] . '.' . $scriptType[intval($value['script_type'])];
                    }
                }

                $records['rows'][] = array(
                    'uuid' => $d['vm_uuid'],
                    'no' => $i++,
                    'source_name' => $d['vm_name'],
                    'name' => $d['new_name'],
                    // 脚本列表
                    'script_list' => $scriptlist,
                    'size' => v1_calsize($d['vm_size'], true),
                    'valid_size' => v1_calsize($d['vm_valid_size'], true),
                    'transport_size' => $this->getVMCompletedSize(
                        $d['task_status'],
                        $d['current_object_transport_size'],
                        $d['transport_size']
                    ),
                    'write_size' => $this->getVMCompletedSize(
                        $d['task_status'],
                        $d['current_object_write_size'],
                        $d['write_size']
                    ),
                    'speed' => $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                    'percent' => $this->getVMPercent(
                        $d['vm_valid_size'],
                        $d['current_object_transport_size'],
                        $d['transport_size'],
                        $d['bd_task_status'],
                        $d['task_status']
                    ),
                    'status' => $this->getVMStatus($d['bd_task_status'], $d['task_status']),
                    'description' => $this->getErrorCodeDes($d['task_status'], $d['error_code']),
                    'detail' => $this->getRecoveryDetailsInfo($d, $jobUuid),
                );
            }
        } else {
            $sqlr = '';
            if (!empty($search)) {
                $sqlr = " and (ol.os_name like '%{$search}%' or ol.agent_ip like '%{$search}%') ";
            }
            $sqlCount = "select count(ol.agent_uuid) num from os_list ol
                    left join bd_running_info bri on ol.task_uuid = bri.task_uuid
                    left join bd_task bt on bt.task_uuid = ol.task_uuid
                    left join os_backup_timepoint obt on ol.timepoint_uuid = obt.timepoint_uuid
                    left join os_task ot on ol.task_uuid = ot.task_uuid
                    left join bd_instant_recovery_task birt on birt.task_uuid = ol.task_uuid
                    where ol.task_uuid = ? {$sqlr}";
            $total = $this->dbSelect($sqlCount, [$jobUuid]);
            if (empty($total[0]['num'])) {
                return [
                    'rows' => [],
                    'total' => 0
                ];
            }
            // 整机
            $sql = "select ol.os_name,ol.agent_ip, ol.total_size as ol_total_size, ol.task_status,
                       ol.transport_size as ol_transport_size,ol.error_code,ol.dir_path,ol.after_task_script,
                       ol.backup_mode,ol.agent_ip,ol.os_config,ol.timepoint_uuid,ol.inst_recovery_agent_uuid,
                       ol.write_size as ol_write_size, ol.valid_size as ol_valid_size,ol.agent_uuid,
                        ol.exclude_devices_list,ol.recovery_strategy,obt.os_config as obt_os_config,
                        bri.total_object_transport_size, bri.current_object_write_size, bri.speed,
                        bri.speed_time, bri.current_object_transport_size, bri.current_object_total_size,
                        bri.current_object_valid_size, bt.task_status as bd_task_status, bt.task_type,
                        birt.instant_target_info,obt.agent_ip agent_ips,obt.os_name os_names,obt.agent_uuid agent_uuids,
                        birt.extension_info,vbt.vm_name
                    from os_list ol
                    left join bd_running_info bri on ol.task_uuid = bri.task_uuid
                    left join bd_task bt on bt.task_uuid = ol.task_uuid
                    left join os_backup_timepoint obt on ol.timepoint_uuid = obt.timepoint_uuid
                    left join vm_backup_timepoint vbt on ol.timepoint_uuid = vbt.timepoint_uuid
                    left join os_task ot on ol.task_uuid = ot.task_uuid
                    left join bd_instant_recovery_task birt on birt.task_uuid = ol.task_uuid
                    where ol.task_uuid = ? {$sqlr} limit {$offset},{$limit}";
            $data = $this->dbSelect($sql, array($jobUuid));
            $agentList = $this->getAllAgentList();
            $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
            $taskStatus = xphp_get_config('task', 'TASKSTATUS');
            $nullSpace = xphp_get_config('app', 'NULLSPACE');
            $i = 1;
            foreach ($data as $d) {
                $names = $d['os_name'] . '(' . $d['agent_ip'] . ')';
                if ($job[0]['task_type'] == $taskType['PLATFORM_RECOVERY']) {
                    // 跨平台恢复
                    $sourceName = !empty($d['vm_name']) ? $d['vm_name'] : $d['os_names'] . '(' . $d['agent_ips'] . ')';
                } elseif ($job[0]['task_type'] == $taskType['OS_INSTANT_RECOVERY']) {
                    // 瞬时恢复 inst_recovery_agent_uuid
                    $sourceName = $this->getAgentNameByList(
                        $agentList,
                        $d['agent_uuids'],
                        $d['os_names'],
                        $d['agent_ips']
                    );
                    $names = $this->getSourceName(
                        $agentList,
                        $d,
                        $d['instant_target_info']
                    );
                } else {
                    $sourceName =  $this->getSourceName(
                        $agentList,
                        $d,
                        $d['instant_target_info']
                    );
                }
                // 判断当前任务和当前主机状态
                $size = v1_calsize($d['ol_total_size'], true);
                $validSize = v1_calsize($d['ol_valid_size'], true);
                $transportSize = v1_calsize($d['ol_transport_size'], true);
                $writeSize = v1_calsize($d['ol_write_size'], true);
                $speed = '--';
                $percent = $this->getOSPercent($d['ol_valid_size'], $d['ol_transport_size']);
                if ($d['bd_task_status'] == $taskStatus['RUNNING'] && $d['task_status'] == $taskStatus['RUNNING']) {
                    // 都是运行中
                    $size = v1_calsize($d['current_object_total_size'], true);
                    $validSize = v1_calsize($d['current_object_valid_size'], true);
                    $transportSize = v1_calsize($d['current_object_transport_size'], true);
                    $speed = $this->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time']);
                    $writeSize = v1_calsize($d['current_object_write_size'], true);
                    $percent = $this->getOSPercent(
                        $d['current_object_valid_size'],
                        $d['current_object_transport_size']
                    );
                }

                // 瞬时恢复有四个脚本，再 bd_instant_recovery_task 的 extension_info里面去找
                if ($d['task_type'] == $taskType['OS_INSTANT_RECOVERY']) {
                    // 是瞬时恢复
                    $scriptlist = [
                        'after_start_success_scripts' => [], // 恢复前
                        'after_stop_scripts' => [], // 恢复后
                        'before_run_scripts' => [], // 停止前
                        'before_stop_scripts' => [], // 停止后
                    ];
                    $extension = json_decode($d['extension_info'], true);
                    if (!empty($extension[0]['after_start_success_scripts'])) {
                        foreach ($extension[0]['after_start_success_scripts'] as $value) {
                            //获取脚本名称
                            $scriptlist['after_start_success_scripts'][] =
                                $value['script_name'] . '.' . $scriptType[intval($value['script_type'])];
                        }
                    }
                    if (!empty($extension[0]['after_stop_scripts'])) {
                        foreach ($extension[0]['after_stop_scripts'] as $value) {
                            //获取脚本名称
                            $scriptlist['after_stop_scripts'][] =
                                $value['script_name'] . '.' . $scriptType[intval($value['script_type'])];
                        }
                    }
                    if (!empty($extension[0]['before_run_scripts'])) {
                        foreach ($extension[0]['before_run_scripts'] as $value) {
                            //获取脚本名称
                            $scriptlist['before_run_scripts'][] =
                                $value['script_name'] . '.' . $scriptType[intval($value['script_type'])];
                        }
                    }
                    if (!empty($extension[0]['before_stop_scripts'])) {
                        foreach ($extension[0]['before_stop_scripts'] as $value) {
                            //获取脚本名称
                            $scriptlist['before_stop_scripts'][] =
                                $value['script_name'] . '.' . $scriptType[intval($value['script_type'])];
                        }
                    }
                } else {
                    $scriptlist = [
                        'after_task_script' => []
                    ];
                    // 获取恢复后脚本 跨平台恢复和迁移都是这个
                    $afterScript = json_decode($d['after_task_script'], true);
                    if (!empty($afterScript)) {
                        foreach ($afterScript as $value) {
                            //获取脚本名称
                            $scriptlist['after_task_script'][] =
                                $value['script_name'] . '.' . $scriptType[intval($value['script_type'])];
                        }
                    }
                }

                $records['rows'][] = array(
                    'uuid' => $d['agent_uuid'],
                    //获取编号
                    'no' => $i++,
                    // 目标对象名
                    'name' => $names,
                    // 源对象名 应该是瞬时恢复任务对象的目标
                    'source_name' => $sourceName,
                    // 脚本列表
                    'script_list' => $scriptlist,
                    //主机大小
                    'size' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace : $size,
                    //有效数据大小
                    'valid_size' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace : $validSize,
                    //传输大小
                    'transport_size' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace : $transportSize,
                    //写入大小
                    'write_size' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace : $writeSize,
                    //传输速度
                    'speed' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace : $speed,
                    //传输进度
                    'percent' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace : $percent,
                    //状态
                    'status' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace :
                        xphp_get_lang($ptDes[$d['task_status']]),
                    'description' => $d['bd_task_status'] !== $taskStatus['RUNNING'] ? $nullSpace :
                        $this->getErrorCodeDes($d['task_status'], $d['error_code']),
                    //其他详情
                    'detail' => $this->getOSDetailsInfo($d, $jobUuid, $names),
                );
            }
        }
        $records['total'] = $total[0]['num'] ?? 0;
        return $records;
    }

    /**
     * 获取跨平台、瞬时恢复和迁移任务详情的脚本详情（包括vm和整机的）
     * @param array $params 请求参数
     * @return array
     */
    public function getJobScript(array $params): array
    {
        $jobUuid = $params['job_uuid'];
        $uuid = $params['uuid']; // 虚拟化对应vm_uuid，客户端对应agent_uuid
        $name = $params['name']; // 脚本名称+后缀
        // 如果是瞬时恢复，需要flag区分是哪个时候的脚本
        //  'after_start_success_scripts', // 恢复前 1
        //  'after_stop_scripts', // 恢复后 2
        //  'before_run_scripts', // 停止前 3
        // 'before_stop_scripts', // 停止后 4
        $flag = $params['flag'] ?? 0;
        // 先根据任务uuid查询是什么模块，整机还是虚拟机，已经任务类型
        $task = $this->dbSelect('select task_type,module_type from bd_task where task_uuid = ?', [$jobUuid]);
        if (empty($task)) {
            return [
                'code' => -1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        if ($task[0]['task_type'] == $taskType['OS_INSTANT_RECOVERY']) {
            // 整机瞬时恢复，要去 bd_instant_recovery_task 的 extension_info 里面去查询
            $data = $this->dbSelect(
                'select extension_info from bd_instant_recovery_task where task_uuid = ?',
                [$jobUuid]
            );
            if (empty($data)) {
                return [
                    'code' => -1,
                    'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
                ];
            }
            $script = json_decode($data[0]['extension_info'], true);
            switch (intval($flag)) {
                case 1:
                    $script = $script[0]['after_start_success_scripts'];
                    break;
                case 2:
                    $script = $script[0]['after_stop_scripts'];
                    break;
                case 3:
                    $script = $script[0]['before_run_scripts'];
                    break;
                default:
                    $script = $script[0]['before_stop_scripts'];
            }
        } elseif ($task[0]['module_type'] == $moduleType['VM']) {
            // 虚拟化 去 vm_machine_list 的 after_task_script 查询
            $data = $this->dbSelect(
                'select after_task_script from vm_machine_list where task_uuid = ? and vm_uuid = ?',
                [$jobUuid, $uuid]
            );
            if (empty($data)) {
                return [
                    'code' => -1,
                    'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
                ];
            }
            $script = json_decode($data[0]['after_task_script'], true);
        } else {
            // 整机 去 os_list 的 after_task_script 查询
            $sql = 'select after_task_script from os_list where task_uuid = ?';
            $parm = [$jobUuid];
            if (!empty($uuid)) {
                $sql .= ' and agent_uuid = ?';
                $parm = array_merge($parm, [$uuid]);
            }
            $data = $this->dbSelect($sql, $parm);
            if (empty($data)) {
                return [
                    'code' => -1,
                    'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
                ];
            }
            $script = json_decode($data[0]['after_task_script'], true);
        }

        if (empty($script)) {
            return [
                'code' => -1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }

        $scriptType = xphp_get_desc('Script', 'SCRIPT_TYPE');
        $msg = '';
        foreach ($script as $item) {
            //获取脚本名称
            $names = $item['script_name'] . '.' . $scriptType[intval($item['script_type'])];
            if ($names == $name) {
                $msg = $item;
                break;
            }
        }
        if (empty($msg)) {
            return [
                'code' => -1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }
        return [
            'code' => 0,
            'msg' => $msg
        ];
    }

    /**
     * 获取迁移的源对象名
     * @param array  $agentList 客户端列表
     * @param array  $d         迁移详情
     * @param string $instant   瞬时恢复目标信息
     * @return string
     */
    private function getSourceName(array $agentList, array $d, $instant = '')
    {
        if (empty($instant)) {
            // 跨平台
            return $this->getAgentNameByList(
                $agentList,
                $d['agent_uuid'],
                $d['os_name'],
                $d['agent_ip']
            );
        }
        $instantTarget = json_decode($instant, true);
        $hypervisor = $instantTarget['hypervisor_type'];
        if ($hypervisor == 0) {
            // 目标是整机
            return $this->getAgentNameByList(
                $agentList,
                $instantTarget['target_uuid'],
                $d['os_name'],
                $d['agent_ip']
            );
        }
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $desHostInfo = $this->getOpenstackDesHostInfo($instantTarget['target_uuid']);
        } else {
            if ($hypervisor == 108) {
                // 内嵌 返回默认的
                $node = $this->dbSelect(
                    "select ip from bd_node where node_type = ?",
                    [xphp_get_config('app')['NODETYPE']['MASTER']]
                );
                $desHostInfo = [
                    'vcenterIP' => xphp_get_lang('UI_VM_MACHINE_MANAGER'),
                    'hostIP' => $node[0]['ip'] ?? '--',
                ];
            } else {
                $desHostInfo = $this->getDesHostInfo($instantTarget['target_uuid'], $instantTarget['host_uuid']);
            }
        }
        return $desHostInfo['vcenterIP'] . '(' . $desHostInfo['hostIP'] . ')';
    }

    /**
     * 得到虚拟机列表的已完成容量
     * @param int $vmTaskStatus     任务状态
     * @param int $briCompletedSize 运行时完成容量
     * @param int $vmlCompletedSize 已完成容量
     * @return string
     */
    private function getVMCompletedSize($vmTaskStatus, $briCompletedSize, $vmlCompletedSize)
    {
        if ($vmTaskStatus == xphp_get_config('vm', 'VmTaskStatus')['RUNNING']) {
            //虚拟机是运行状态,显示bd_running_info的完成大小
            return v1_calsize($briCompletedSize, true);
        } elseif ($vmTaskStatus == xphp_get_config('vm', 'VmTaskStatus')['WAITTING']) {
            //虚拟机是等待状态,显示0B
            return '0B';
        } else {
            //虚拟机不在运行状态,显示vm_machine_list的完成大小
            return v1_calsize($vmlCompletedSize, true);
        }
    }

    /**
     * 根据每个虚拟机的状态得到速度
     * @param int $status    状态
     * @param int $speed     速度
     * @param int $speedTime 时间
     * @return string
     */
    private function getVMSpeed(int $status, int $speed, int $speedTime): string
    {
        if (time() - $speedTime > 12) {
            $speed = 0;
        }
        if (xphp_get_config('vm', 'VmTaskStatus')['RUNNING'] == $status) {
            $speed = v1_calspeed($speed);
        } else {
            $speed = '--';
        }

        return $speed;
    }

    /**
     * 得到虚拟机列表百分比
     * @param int $vmSize           虚拟机大小
     * @param int $briCompletedSize 运行时完成容量
     * @param int $vmlCompletedSize 已完成容量
     * @param int $bdTaskStatus     bd任务状态
     * @param int $vmTaskStatus     vm任务状态
     * @return string
     */
    private function getVMPercent(
        int $vmSize,
        int $briCompletedSize,
        int $vmlCompletedSize,
        int $bdTaskStatus,
        int $vmTaskStatus
    ): string {
        if (
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //首先任务是在运行状态
            if ($vmTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                return v1_calpercent($vmSize, $briCompletedSize);
            } elseif ($vmTaskStatus == xphp_get_config('task', 'TASKSTATUS')['WAITTING']) {
                return '0%';
            } else {
                if (0 == $vmlCompletedSize) {
                    return '0%';
                }
                if ($vmSize == $vmlCompletedSize) {
                    return '100%';
                }
                return v1_calpercent($vmSize, $vmlCompletedSize);
            }
        } else {
            return xphp_get_config('app', 'NULLSPACE');
        }
    }

    /**
     * 获取传输百分比
     * @param unknown $validsize     分母
     * @param unknown $transportsize 分子
     * @return string
     */
    public function getOSPercent($validsize, $transportsize)
    {
        $validsize = intval($validsize);
        $transportsize = intval($transportsize);
        if ($validsize == $transportsize) {
            //如果相等
            if (empty($validsize)) {
                return '0%';
            } else {
                return '100%';
            }
        }
        if ($transportsize > $validsize) {
            //如果分子比分母大
            return xphp_get_config('app', 'NULLSPACE');
        }
        return v1_calpercent($validsize, $transportsize);
    }

    /**
     * 得到虚拟机列表的虚拟机状态
     * @param int $bdTaskStatus bd任务状态
     * @param int $vmTaskStatus vm任务状态
     * @return string
     */
    private function getVMStatus(int $bdTaskStatus, int $vmTaskStatus): string
    {
        if (
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //任务在运行状态时,显示虚拟机的状态
            $vmDes = xphp_get_desc('Vm', 'VmTaskStatus');
            return xphp_get_lang($vmDes[$vmTaskStatus]);
        } else {
            return xphp_get_config('app', 'NULLSPACE');
        }
    }

    /**
     * 得到恢复任务虚拟机详情
     * @param array  $d        虚拟机信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    protected function getRecoveryDetailsInfo(array $d, string $taskUuid): array
    {
        $hypervisor = $this->getHypervisor($taskUuid);
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $desHostInfo = $this->getOpenstackDesHostInfo($d['vcenter_uuid']);
        } else {
            if ($hypervisor == 108) {
                // 内嵌 返回默认的
                $node = $this->dbSelect(
                    "select ip from bd_node where node_type = ?",
                    [xphp_get_config('app')['NODETYPE']['MASTER']]
                );
                $desHostInfo = [
                    'vcenterIP' => xphp_get_lang('UI_VM_MACHINE_MANAGER'),
                    'hostIP' => $node[0]['ip'] ?? '--',
                ];
            } else {
                $desHostInfo = $this->getDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
            }
        }
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $timepointDes = $timepointInfo['taskname'] . ' => ' . $timepointInfo['timepoint'];
        //时间点被意外删除
        if (empty($timepointInfo['taskname'])) {
            $timepointDes = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }

        // 处理下磁盘对照关系
        $newName = $d['new_name'];
        $disk = '';
        $vmConfig = json_decode($d['vm_config'], true);
        if (!empty($vmConfig)) {
            // 判断下目标对象名称是否有更改
            if (!empty($vmConfig['new_vm_name']) && $vmConfig['new_vm_name'] != $d['new_name']) {
                // 不一致，那么需要显示出来
                $newName = $vmConfig['new_vm_name'] . '(' . xphp_get_lang('UI_PLATFORM_RECOVERY_JOB_CONFIG_OBJECT')
                    . '：' . $newName . ')';
            }
            // 磁盘处理
            if (is_array($vmConfig['vm_disk_list'])) {
                foreach ($vmConfig['vm_disk_list'] as $items) {
                    $srcDisk = $items['src_disk_name'] ?? '--'; // 兼容字段不存在
                    $newDisk = $items['disk_name'];
                    if (!empty($items['final_disk_name']) && $items['final_disk_name'] != $newDisk) {
                        $newDisk .= '(' . $items['final_disk_name'] . ')';
                    }
                    $disk .= $srcDisk . ' => ' . $newDisk . '</br>';
                }
            }
        }

        return array(
            'path' => empty($d['dir_path']) ? '--' : $d['dir_path'],
            'd_vcenter_iP' => $desHostInfo['vcenterIP'],
            'd_host_op' => $desHostInfo['hostIP'],
            'd_name' => $newName,
            'disk' => $disk,
            'timepoint' => $timepointInfo['timepoint'],
            'taskname' => $timepointInfo['taskname'],
            'timepoint_des' => $timepointDes
        );
    }

    /**
     * 得到恢复任务整机详情
     * @param array  $d        整机信息
     * @param string $taskUuid 任务uuid
     * @param string $names    恢复主机
     * @return array
     */
    protected function getOSDetailsInfo(array $d, string $taskUuid, string $names): array
    {

        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $timepointDes = $timepointInfo['taskname'] . ' => ' . $timepointInfo['timepoint'];
        //时间点被意外删除
        if (empty($timepointInfo['taskname'])) {
            $timepointDes = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }
        // 处理下磁盘映射关系
        $disk = [];
        if (empty($d['obt_os_config'])) {
            // 说明源是无代理，那么树就只有一层
            $disks = json_decode($d['recovery_strategy'], true);
            foreach ($disks as $item) {
                $disk[] = [
                    'id' => $item['source_dev_uuid'],
                    'pId' => 0,
                    'open' => true,
                    'name' => $item['source_name'] . '=>' . $item['destination_name'],
                    'children' => [],
                ];
            }
        } else {
            // 需要去解析处理
            $diskAll = json_decode($d['obt_os_config'], true);
            $diskChoose = json_decode($d['recovery_strategy'], true);
            $diskExclude = [];
            if (!empty($d['exclude_devices_list'])) {
                $diskExclude = json_decode($d['exclude_devices_list'], true);
                $diskExclude = array_column($diskExclude, 'dev_uuid');
            }
            $diskChoose1 = [];
            foreach ($diskChoose as $item1) {
                $diskChoose1[$item1['source_dev_uuid']] = $item1;
            }
            foreach ($diskAll['all_disk_list'] as $item) {
                if (!empty($diskChoose1[$item['dev_uuid']])) {
                    $id = $item['dev_uuid'] . xphp_uuid();
                    // 存在选择的磁盘
                    $disk1 = [
                        'id' => $id,
                        'pId' => 0,
                        'open' => true,
                        'name' => $item['dev_name'] . '(' . v1_calsize($item['allocated_size'], true)
                            . ') => ' . $diskChoose1[$item['dev_uuid']]['destination_name'],
                        'children' => [],
                    ];
                    if (!empty($item['sub_devices'])) {
                        $disk1['children'] = $this->calcTwoArray(
                            $item['sub_devices'],
                            $diskExclude,
                            $id
                        );
                    }
                    $disk[] = $disk1;
                }
            }
        }
        return [
            'path' => empty($d['dir_path']) ? '--' : $d['dir_path'],
            'd_name' => $d['os_name'] ?: $names,
            'timepoint' => $timepointInfo['timepoint'],
            'taskname' => $timepointInfo['taskname'],
            'timepoint_des' => $timepointDes,
            'disk' => $disk
        ];
    }

    /**
     * 递归处理数组差异
     * @param array  $array1 原始数组
     * @param array  $array2 不存在的数组
     * @param string $pid    父类id
     * @return array
     */
    private function calcTwoArray(array $array1, array $array2, $pid = '')
    {
        $arr = [];
        foreach ($array1 as $item) {
            if (!in_array($item['dev_uuid'], $array2)) {
                $id = $item['dev_uuid'] . xphp_uuid();
                $arr1 = [
                    'id' => $id,
                    'pId' => $pid,
                    'open' => true,
                    'name' => $item['dev_name'] . '(' . v1_calsize($item['total_size'], true) . ')',
                    'children' => [],
                ];
                if (!empty($item['sub_devices'])) {
                    // 递归处理
                    $arr1['children'] = $this->calcTwoArray(
                        $item['sub_devices'],
                        $array2,
                        $id
                    );
                }
                $arr[] = $arr1;
            }
        }
        return $arr;
    }

    /**
     * Openstack一类得到目的宿主机信息
     * @param string $vcenterUuid 虚拟化平台uuid
     * @return array
     */
    private function getOpenstackDesHostInfo(string $vcenterUuid): array
    {
        $sql = "select vh.host_ip, vv.vcenter_ip, vv.detail, vv.username 
                from  vm_host vh, vm_vcenter vv 
                where vh.vcenter_uuid = vv.vcenter_uuid
                and vv.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenterUuid));
        if ($data) {
            return array(
                'hostIP' => $data[0]['host_ip'] . '(' . $data[0]['username'] . ')',
                'vcenterIP' => $data[0]['vcenter_ip'],
            );
        }
    }

    /**
     * 根据vcenteruuid和hostuuid得到vcenterip和hostip
     * @param string $vcenterUuid 虚拟化平台uuid
     * @param string $hostUuid    主机uuid
     * @return array
     */
    protected function getDesHostInfo(string $vcenterUuid, string $hostUuid): array
    {
        $sql = "select vh.host_ip, vv.vcenter_ip
                from  vm_vcenter vv left join vm_host vh
                on vh.vcenter_uuid = vv.vcenter_uuid and vh.host_uuid = ?
                where vv.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($hostUuid, $vcenterUuid));
        if ($data) {
            return array(
                'hostIP' => $data[0]['host_ip'] ?: '--',
                'vcenterIP' => $data[0]['vcenter_ip'],
            );
        }
        return array(
            'hostIP' => '--',
            'vcenterIP' => '--',
        );
    }

    /**
     * 根据时间点uuid获取时间点的其他信息
     * @param string $timepointUuid 时间点uuid
     * @return array
     */
    public function getTimepointInfo(string $timepointUuid): array
    {
        $sql = "select timepoint, task_name, backup_mode from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($timepointUuid));
        if ($data) {
            return array(
                'timepoint' => $data[0]['timepoint'] .
                    ($data[0]['backup_mode'] != 0 ?
                        '(' . $this->getTimepointTypeDes($data[0]['backup_mode']) . ')'
                        : ''),
                'taskname' => $data[0]['task_name']
            );
        }
        return ['timepoint' => '', 'taskname' => ''];
    }

    /**
     * 获取客户端网络列表
     * @param string $agentUuid 客戶端uuid
     * @return array
     */
    public function agentNetworkList(string $agentUuid): array
    {
        $data = $this->dbSelect("select detail from bd_agent where agent_uuid = ?", [$agentUuid]);
        $return = [
            'total' => 0,
            'rows' => []
        ];

        $detail = json_decode($data[0]['detail'], true);

        if (!empty($detail['nic_list']) && is_array($detail['nic_list'])) {
            foreach ($detail['nic_list'] as $item) {
                $return['rows'][] = [
                    'network_uuid' => $item['name'],
                    'network_away' => $item['gateway_address'],
                    'network_name' => $item['name'],
                    'mac_address' => $item['mac_address'],
                ];
            }
            $return['total'] = count($return['rows']);
        }

        return $return;
    }

    /**
     * 获取虚拟化平台对应的磁盘总线类型和网络类型
     * @param array $params 请求参数
     * @return array
     */
    public function diskNetworkList(array $params): array
    {
        $data = (new VmRecover())->getVMRecoveryControlContent(
            $params['hypervisor'],
            $params['vcenter_uuid'],
            $params['host_uuid']
        );
        // 进行数据组装
        $disk = [];
        foreach ($data['disk_bus'] as $item) {
            $disk[] = [
                'name' => $item['value'],
                'value' => $item['key'],
            ];
        }
        $network = [];
        foreach ($data['network_bus'] as $item) {
            $network[] = [
                'name' => $item['value'],
                'value' => $item['key'],
            ];
        }
        return [
            'enable_change' => $data['control']['disk_bus_type'] && $data['control']['network_bus_type'],
            'default_disk' => $disk[0]['value'],
            'default_network' => $network[0]['value'],
            'disk_bus' => $disk,
            'network_bus' => $network,
        ];
    }

    /**
     * 根据任务运行情况估算完成时间
     * @param int $totalSize   总大小
     * @param int $currentSize 当前大小
     * @param int $taskStatus  状态
     * @param int $speed       速度
     * @return string
     */
    private function getCalEndTime($totalSize, $currentSize, $taskStatus, $speed): string
    {
        $taskStatusArr = xphp_get_config('task', 'TASKSTATUS');
        if (!in_array($taskStatus, [$taskStatusArr['RUNNING'], $taskStatusArr['ABNORMAL']])) {
            //任务没有运行
            return xphp_get_config('app', 'TIMESPACE');
        }

        if (0 == intval($speed)) {
            return xphp_get_config('app', 'TIMESPACE');
        }
        $needTime = ($totalSize - $currentSize) / $speed ;
        return date('Y-m-d H:i:s', time() + intval($needTime));
    }

    /**
     * 根据任务状态计算速度
     * @param int $taskStatus 任务状态
     * @param int $speed      速度
     * @return string
     */
    private function getJobSpeed($taskStatus, $speed)
    {
        if ($taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }

        return v1_calspeed($speed);
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    private function getNodeNetworkFlag(string $taskuuid): bool
    {
        $sql = "select ba.net_model, bts.network_uuid
                from bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal
                where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d) {
            if (intval($d['net_model']) == 2 && !empty($d['network_uuid'])) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * 得到主机传输策略
     * @param string $strategyid 传输uuid
     * @return NULL[]
     */
    private function gettransportOSinfo($strategyid)
    {
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times,
                       bts.reconnect_interval, bts.encrypt_method
                from bd_transport_strategy bts
                    left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid
                where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v1_parse_flag_to_bool($data[0]['encrypt_flag']);
        $info['compress'] = v1_parse_flag_to_bool($data[0]['compress_flag']);
        //获取传输网络描述
        $name = '';
        if (!empty($data[0]['ip'])) {
            $name = $data[0]['ip'] . ':' . $data[0]['port'];
            if (!empty($data[0]['alias_name'])) {
                $name .= '(' . $data[0]['alias_name'] . ')';
            }
        }
        $info['network'] = $name;
        $info['encrypt_method'] = $data[0]['encrypt_method'];
        return $info;
    }

    /**
     * //获取服务器IP地址
     * @param string $networkuuid 网络uuid
     * @return string
     */
    public function getServerIp(string $networkuuid): string
    {
        $sql = "select ip from bd_node_network where network_uuid  = ? ";
        $data = $this->dbSelect($sql, array($networkuuid));
        return $data[0]['ip'] ?? '';
    }

    /**
     * 获取任务虚拟机的策略配置信息
     * @param string $jobUuid                 任务uuid
     * @param int    $module                  模块类型
     * @param int    $taskType                1
     *                                        跨平台恢复，2迁移
     * @param int    $currentTaskRunningStage 实时任务阶段
     * @return array
     */
    private function getSteategyDetail(string $jobUuid, int $module, int $taskType = 1, $currentTaskRunningStage = 0)
    {
        $return = [];
        // 需要查询出恢复方向
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        $taskArr = xphp_get_config('task', 'TASKTYPE');
        $flag = xphp_get_config('app', 'FLAG');
        if ($module == $moduleArr['VM']) {
            // 虚拟化平台
            if ($taskType == 1) {
                // 跨平台恢复
                $hypervisorNow = $this->dbSelect('select hypervisor_type from vm_task where task_uuid = ?', [$jobUuid]);
                $hypervisorNow = $hypervisorNow[0]['hypervisor_type'];
            } else {
                // 迁移
                $hypervisorNow = $this->dbSelect(
                    'select migrate_target_info from bd_instant_recovery_task where task_uuid = ?',
                    [$jobUuid]
                );
                $hypervisorNow = json_decode($hypervisorNow[0]['migrate_target_info'], true);
                $hypervisorNow = $hypervisorNow['hypervisor_type'];
            }
            $goals = $this->getVersionByHyper($hypervisorNow);
        } else {
            // 整机
            $goals = xphp_get_lang('UI_COMPLETE_MACHINE');
        }

        $sources = $this->getSourceByJobUuid($jobUuid, $taskType, $module);
        $other = '';
        if ($sources[1] == 3) {
            // 源是实时的，那么需要区分是 复制还是实时保护
            if ($taskType == 1) {
                // 跨平台
                $sql = 'select bbt.task_type from cdp_vol_task_restore_info cvtri,bd_backup_timepoint bbt
                        where cvtri.task_uuid = ? and cvtri.recovery_datetime_uuid = bbt.timepoint_uuid';
            } else {
                // 迁移
                $sql = 'select bbt.task_type from bd_instant_recovery_task birt,bd_backup_timepoint bbt
                        where birt.task_uuid = ? and birt.timepoint_uuid = bbt.timepoint_uuid';
            }
            $sourceTask = $this->dbSelect($sql, [$jobUuid]);
            if (!empty($sourceTask)) {
                if ($sourceTask[0]['task_type'] == $taskArr['VOL_CDP_REPLICATION']) {
                    // 复制任务
                    $other = '[' . xphp_get_lang('WEB_PLATFORM_REPLICATION') . ']';
                } else {
                    // 实时保护
                    $other = '[' . xphp_get_lang('UI_PLATFORM_CDP_PROTECT') . ']';
                }
            }
        }

        $return['direction'] = $sources[0]  . $other . '->' . $goals;

        $return['point_type'] = $sources[1]; // 时间点源类型 1无代理 2整机定时 3整机实时

        $sql = "select bt.thread_num, bt.ignore_resource_limiting_flag,
        bt.strategy_id, bt.transport_ip_segment,bt.strategy_id
        from bd_task bt where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobUuid));
        if (empty($data)) {
            return $return;
        }
        $d = $data[0];
        if ($taskType == 2) {
            // 迁移
            $applianceInfo = $this->getJobAppliance($jobUuid);
            $return['source_point'] = $sources[2]; // 时间点源类型
        } else {
            $applianceInfo = (new VmJobInfo())->getJobAppliance($jobUuid);
            // 跨平台还需要查询出时间点使用的存储类型，因为磁带的详情不显示安全策略
            $return['storage_type'] = $this->getStorageTypeByJob($jobUuid);
        }

        $agentPoolInfo = $this->getJobAgentPoolInfo($jobUuid);
        $return['time_strategy'] = $this->getJobTimeStrategy($d['strategy_id']);
        if ($module == $moduleArr['VOL_CDP']) {
            // 实时
            $return['transport_strategy'] = (new CmJobInfo())->getVolCdpTransportStrategy(
                $jobUuid,
                $d['strategy_id'],
                $currentTaskRunningStage
            );
            if ($taskType == 1) {
                // 跨平台恢复
                // 查询出重置主机名
                $resetHost = $this->dbSelect(
                    'select cvtri.host_reset_name,cvba.master_agent_detail
                    from cdp_vol_task_restore_info cvtri,cdp_vol_backup_agent cvba
                where cvtri.task_uuid = ? and cvtri.recovery_datetime_uuid = cvba.timepoint_uuid',
                    [$jobUuid]
                );
                $hostName = [];
                foreach ($resetHost as $items) {
                    $osConfig = json_decode($items['master_agent_detail'], true);
                    $names = $this->getAgentName(
                        $osConfig['hostname'],
                        $osConfig['agent_name'],
                        $osConfig['ip']
                    );
                    if (!empty($items['host_reset_name'])) {
                        $names2 = $items['host_reset_name'];
                    } else {
                        $names2 = xphp_get_lang('UI_PUBLIC_OFF_TWO');
                    }
                    $hostName[] = [
                        'source' => $names,
                        'value' => $names2,
                    ];
                }
                $return['reset_host_name'] = $hostName;
            }
        } elseif ($module != $moduleArr['VM']) {
            // 整机传输策略
            $return['transport_strategy'] = $this->gettransportOSinfo($d['strategy_id']);
            // 联表 os_backup_timepoint 和 os_list 通过task_uuid
            if ($taskType == 1) {
                $osInfo = $this->dbSelect(
                    'select obt.agent_ip,obt.os_name,ol.os_config 
                        from os_list ol,os_backup_timepoint obt
                        where ol.task_uuid = ? and ol.timepoint_uuid = obt.timepoint_uuid',
                    [$jobUuid]
                );
                $hostName = [];
                foreach ($osInfo as $items) {
                    $osConfig = json_decode($items['os_config'], true);
                    $names = $items['os_name'] . '(' . $items['agent_ip'] . ')';
                    if ($osConfig['rename_host'] == $flag['SET']) {
                        $names2 = $osConfig['new_host_name'];
                    } else {
                        $names2 = xphp_get_lang('UI_PUBLIC_OFF_TWO');
                    }
                    $hostName[] = [
                        'source' => $names,
                        'value' => $names2,
                    ];
                }
                $return['reset_host_name'] = $hostName;
            }
        } else {
            // 虚拟化传输策略
            if ($taskType == 2) {
                // 迁移重新写，，全部
                $return['transport_strategy'] = $this->getTransportStrategy($jobUuid, $d['strategy_id']);
            } else {
                $return['transport_strategy'] = (new VmJobInfo())->getTransportStrategy($jobUuid, $d['strategy_id']);
                // 从 vm_machine_list 表查询
                $vmInfo = $this->dbSelect(
                    'select vm_name,vm_config from vm_machine_list where task_uuid = ?',
                    [$jobUuid]
                );
                $hostName = [];
                foreach ($vmInfo as $items) {
                    $osConfig = json_decode($items['vm_config'], true);
                    $names = $items['vm_name'];
                    if ($osConfig['reset_hostname_flag'] == $flag['SET']) {
                        $names2 = $osConfig['new_hostname'];
                    } else {
                        $names2 = xphp_get_lang('UI_PUBLIC_OFF_TWO');
                    }
                    $hostName[] = [
                        'source' => $names,
                        'value' => $names2,
                    ];
                }
                $return['reset_host_name'] = $hostName;
            }
        }

        $return['thread_num'] = intval($d['thread_num']);
        $return['speed_limit'] = $this->getSpeedlimitDes($jobUuid);
        $return['appliance_flag'] = $applianceInfo != '--';
        $return['appliance_des'] = $applianceInfo;
        $return['agent_pool_info'] = $agentPoolInfo;
        $return['hypervisor'] = $this->getHypervisor($jobUuid);
        $return['transport_ip_segment'] = $d['transport_ip_segment'];
        $return['retry_strategy'] = (new ExchangeJobInfo())->getRetryStrategy($jobUuid);
        $return['ignore_resource_limiting_flag'] = v1_parse_flag_to_bool($d['ignore_resource_limiting_flag']);

        if ($module != $moduleArr['VM'] && $taskType == 2) {
            // 整机的迁移 查询出对应的客户端的 net_model 的值
            $target = $this->dbSelect(
                'select migrate_target_info from bd_instant_recovery_task where task_uuid = ?',
                [$jobUuid]
            );
            if (!empty($target)) {
                $infos = json_decode($target[0]['migrate_target_info'], true);
                $model = $this->dbSelect(
                    'select net_model from bd_agent where agent_uuid = ?',
                    [$infos['target_uuid']]
                );
            }
            $return['net_model'] = $model[0]['net_model'] ?? 0;
        } else {
            $return['net_model'] = 0;
        }
        return $return;
    }

    /**
     * 根据任务uuid查询出跨平台恢复的时间点所关联的存储设备类型
     * @param string $jobUuid 任务uuid
     * @return int
     */
    private function getStorageTypeByJob(string $jobUuid): int
    {
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        $sql = "SELECT bt.task_uuid,
                CASE
                    WHEN bt.module_type = {$moduleArr['VM']} THEN (
		            SELECT bsr.storage_type FROM
                        vm_machine_list vml,
                        bd_backup_timepoint bbt,
                        bd_storage_resource bsr 
		            WHERE
			            vml.timepoint_uuid = bbt.timepoint_uuid AND vml.task_uuid = bt.task_uuid
		                and bsr.storage_uuid = bbt.storage_uuid LIMIT 1 
		            ) 
		            WHEN bt.module_type = {$moduleArr['OS']} THEN (
		            SELECT bsr.storage_type  FROM
                        os_list ol,
                        bd_backup_timepoint bbt,
                        bd_storage_resource bsr  
		            WHERE
			            ol.timepoint_uuid = bbt.timepoint_uuid 
			            AND ol.task_uuid = bt.task_uuid  and bsr.storage_uuid = bbt.storage_uuid
			            LIMIT 1 
		            ) 
		            WHEN bt.module_type = {$moduleArr['VOL_CDP']} THEN (
		                SELECT bsr.storage_type 
		                FROM
                        cdp_vol_task_restore_info cvtri,
                        bd_backup_timepoint bbt,
                        bd_storage_resource bsr 
                    WHERE
                        cvtri.recovery_datetime_uuid = bbt.timepoint_uuid 
                        AND cvtri.task_uuid = bt.task_uuid and bsr.storage_uuid = bbt.storage_uuid
                        LIMIT 1 
                    )
                     ELSE bt.id 
                END AS storage_type 
            FROM bd_task bt where task_uuid = ?";

        $data = $this->dbSelect($sql, [$jobUuid]);
        if (empty($data)) {
            return 0;
        }
        return intval($data[0]['storage_type']);
    }

    /**
     * 获取迁移任务appliance信息
     * @param string $taskUuid 任务uuid
     * @return string
     */
    private function getJobAppliance(string $taskUuid)
    {

        $agentInfo = $this->dbSelect(
            'select extension_info from bd_instant_recovery_task where task_uuid = ?',
            [$taskUuid]
        );
        if (empty($agentInfo)) {
            return '--';
        }
        $agent = json_decode($agentInfo[0]['extension_info'], true);
        $agentId = $agent['agent_uuid'];
        if (empty($agentId)) {
            return '--';
        }
        $sql = "select ip, agent_name from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentId));
        if (!empty($data)) {
            $des = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        } else {
            $des = '--';
        }

        return $des;
    }

    /**
     * 得到迁移任务传输策略
     * @param string $taskuuid   任务UUID
     * @param int    $strategyid 策略ID
     * @return array
     */
    private function getTransportStrategy($taskuuid, $strategyid)
    {

        $info = [];
        $infos = $this->dbSelect(
            'select migrate_target_info,extension_info from bd_instant_recovery_task where task_uuid = ?',
            [$taskuuid]
        );
        $target = json_decode($infos[0]['migrate_target_info'], true);
        $data = json_decode($infos[0]['extension_info'], true);
        //传输模式,VMware使用
        $info['hypervisor'] = intval($target['hypervisor_type']);
        $info['mode'] = (new VmJobInfo())->getTransportModeDes($target['hypervisor_type'], $data['transport_priority']);
        $info['mode_index'] = $data['transport_priority'];
        $info['transfer_compress'] = $data['source_compression'] ?? '';
        $info['async_transfer'] = isset($data['async_rw_flag']) && v1_parse_flag_to_bool($data['async_rw_flag']);

        // 传输代理实例类型，公有云使用
        $info['agent_type'] = '';
        if (in_array(intval($target['hypervisor_type']), xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            $info['agent_type'] = $data['agent_uuid'];
        }
        // 并行传输
        $info['parallel_transfer_vm_count'] = $data['parallel_transfer_vm_count'] ?? 0;
        $info['single_vm_parallel_disk_transfer_count'] = $data['single_vm_parallel_disk_transfer_count'] ?? 0;
        $info['vm_single_disk_parallel_transfer_count'] = $data['vm_single_disk_parallel_transfer_count'] ?? 0;
        //加密传输,XenServer使用
        $sql = "select encrypt_flag,reconnect_times,reconnect_interval,encrypt_method
                from bd_transport_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v1_parse_flag_to_bool($data[0]['encrypt_flag']);
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];
        //获取传输网络描述
        $name = '';
        if (!empty($data['ip'])) {
            $name = $data['ip'] . ':' . $data['port'];
            if (!empty($data['alias_name'])) {
                $name .= '(' . $data['alias_name'] . ')';
            }
        }
        $info['network'] = $name;
        return $info;
    }

    /**
     * 根据任务uuid获取恢复源的类型
     * @param string $jobUuid  任务uuid
     * @param int    $taskType 任务类型
     *                         1跨平台恢复2迁移
     * @param int    $module   模块
     * @return array
     */
    private function getSourceByJobUuid(string $jobUuid, int $taskType, int $module)
    {
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        if ($taskType == 1) {
            // 跨平台恢复
            if ($module == $moduleArr['VM']) {
                // 虚拟化平台
                // 目标是的 vm_machine_list 表的 hypervisor_type
                $sql = 'select bbt.module_type,vbt.hypervisor_type
                        from vm_machine_list vml,bd_backup_timepoint bbt
                        left join vm_backup_timepoint vbt on bbt.timepoint_uuid = vbt.timepoint_uuid
                        where vml.timepoint_uuid = bbt.timepoint_uuid and vml.task_uuid = ? limit 1';
            } else {
                // 整机
                $sql = 'select bbt.module_type,vbt.hypervisor_type
                        from os_list ol,bd_backup_timepoint bbt
                        left join vm_backup_timepoint vbt on bbt.timepoint_uuid = vbt.timepoint_uuid
                        where ol.timepoint_uuid = bbt.timepoint_uuid and ol.task_uuid = ? limit 1';
            }
            $source = $this->dbSelect($sql, [$jobUuid]);
            $hypervisor = empty($source[0]['hypervisor_type']) ? 0 : ($source[0]['hypervisor_type']);
        } else {
            // 迁移在 bd_instant_recovery_task 表中取 instant_target_info
            $sql = 'select bbt.module_type,birt.instant_target_info,vbt.hypervisor_type
                from bd_instant_recovery_task birt,bd_backup_timepoint bbt
                    left join vm_backup_timepoint vbt on vbt.timepoint_uuid = bbt.timepoint_uuid
                where birt.timepoint_uuid = bbt.timepoint_uuid and birt.task_uuid = ? limit 1';
            $source = $this->dbSelect($sql, [$jobUuid]);
            $instant = json_decode($source[0]['instant_target_info'], true);
            $hypervisor = empty($instant['hypervisor_type']) ? 0 : ($instant['hypervisor_type']);
            // 时间点的类型
            $sourceHypervisor = empty($source[0]['hypervisor_type']) ? 0 : ($source[0]['hypervisor_type']);
        }
        $sourceModule = empty($source[0]['module_type']) ? 0 : ($source[0]['module_type']);

        $pointType = 1; // 默认时间点源是无代理
        if (empty($hypervisor)) {
            // 那么源是整机
            if ($sourceModule == $moduleArr['OS']) {
                // 定时整机
                $sources = $this->getVersionByHyper(-1);
                $pointType = 2;
            } else {
                // 实时整机
                $sources = $this->getVersionByHyper(-2);
                $pointType = 3;
            }
        } else {
            $sources = $this->getVersionByHyper($hypervisor);
        }
        $timeSource = '';
        if ($taskType == 2) {
            // 迁移需要判断时间点类型
            if (!empty($sourceHypervisor)) {
                // 表示是虚拟化
                $timeSource = $this->getVersionByHyper($sourceHypervisor);
            } elseif ($sourceModule == $moduleArr['OS']) {
                // 定时整机
                $timeSource = $this->getVersionByHyper(-1);
            } else {
                // 实时整机
                $timeSource = $this->getVersionByHyper(-2);
            }
        }
        return [$sources, $pointType, $timeSource];
    }

    /**
     * 根据传入的虚拟化类型值返回具体的类型
     * @param int $hypervisor 类型。-1 是定时整机 -2 是实时整机
     * @return string
     */
    private function getVersionByHyper(int $hypervisor = 0)
    {
        $hypervisorDes = xphp_get_config('vm', 'VMHYPERVISORDES'); // 虚拟化描述
        $hypervisorArr = xphp_get_config('vm', 'VMHYPERVISORGROUP'); // 虚拟化类型
        if ($hypervisor == 108) {
            // 容灾演练平台
            return xphp_get_lang('UI_VM_MACHINE_MANAGER');
        } elseif ($hypervisor == -1) {
            return xphp_get_lang('UI_COMPLETE_MACHINE');
        } elseif ($hypervisor == -2) {
            // 实时
            return xphp_get_lang('UI_PLATFORM_CDP_COMPLETE_BACKUP');
        }

        $other = $hypervisorDes[$hypervisor];
        if (in_array($hypervisor, $hypervisorArr['openstack'])) {
            // 私有云
            $title = xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD');
        } elseif (in_array($hypervisor, $hypervisorArr['publiccloud'])) {
            // 公有云
            $title = xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD');
        } else {
            // 虚拟化
            $title = xphp_get_lang('UI_PLATFORM_VM_VIRTUAL');
        }
        return $title . '[' . $other . ']';
    }

    /**
     * 根据任务状态得到数据迁移的状态码
     * 0无状态
     * 1错误
     * 2传输
     * @param mixed $taskStatus 任务状态
     * @return int
     */
    private function getDataStoreStatus($taskStatus): int
    {
        $status = intval($taskStatus);
        if ($status == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            return $status;
        } elseif ($status == xphp_get_config('task', 'TASKSTATUS')['WAITTING']) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 获取任务对应的虚拟化类型
     * @param string $taskUuid 任务uuid
     * @return int
     */
    protected function getHypervisor(string $taskUuid): int
    {
        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (empty($data)) {
            // 那么去瞬时恢复表查询
            $sql = 'select instant_target_info from bd_instant_recovery_task where task_uuid = ?';
            $data = $this->dbSelect($sql, [$taskUuid]);
            $info = json_decode($data[0]['instant_target_info'], true);
            return intval($info['hypervisor_type']);
        }
        return intval($data[0]['hypervisor_type']);
    }

    /**
     * 获取openstack租户名
     * @param string $vcenteruuid 虚拟化中心uuid
     * @return string
     */
    private function getOpenstackTenant(string $vcenteruuid): string
    {
        $sql = "select detail from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $detail = json_decode($data[0]['detail'], true);
        return $detail['tenant_name'];
    }
}
