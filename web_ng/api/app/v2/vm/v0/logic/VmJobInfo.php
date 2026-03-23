<?php

namespace app\v2\vm\v0\logic;

use app\v2\common\logic\Base;
use app\v2\common\logic\Backup;
use app\v2\common\logic\Data;
use app\v2\common\logic\Log;
use app\v2\exchange\v0\logic\ExchangeJobInfo;
use app\v2\opcode\VmOpcode;
use app\v2\resources\v0\logic\Node;
use app\v2\resources\v0\logic\Storage;
use app\v2\system\v0\logic\Index;
use app\v2\job\v0\logic\JobInfo as JobInfo;
use app\v2\system\v0\logic\Time;
use app\v2\tape\v0\logic\TapeInfo;
use app\v2\user\v0\logic\User;
use app\v2\log\v0\logic\Index as LogHandler;

/**
 * note          虚拟机 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmJobInfo extends Base
{
    /**
     * 任务详情: 得到虚拟机模块任务基本信息
     * @param array $params
     * @return array
     */
    public function getBasicInfo(array $params): array
    {
        $taskUUID = $params['job_uuid'];
        $this->paramsCheck($taskUUID);
        $jobInfo = JobInfo::instance()->getJobInfo($taskUUID); // 任务基础信息
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
        bt.node_uuid, bt.storage_uuid, bt.ignore_resource_limiting_flag, bt.current_stage, bt.stage_percent,
        bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
        bu.user_name, bu.user_uuid, unix_timestamp(bs.next_start_time) next_start_time,
        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, bt.backup_server_ip,
        bn.ip, bn.host_name, bn.node_nickname,
        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy,
                        brs.network_retry_times,brs.network_retry_interval,brs.op_retry_times,brs.op_retry_interval,brs.task_retry_object,brs.task_retry_times, brs.task_retry_interval,
                        bsr.worm_flag AS worm_storage_flag 
        from bd_task bt left join bd_node bn on bt.node_uuid = bn.node_uuid
            left join bd_task_safe_config btsc on bt.task_uuid = btsc.task_uuid 
            left join bd_retry_strategy brs on bt.task_uuid = brs.task_uuid
            left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid, 
            bd_user bu, bd_running_info bri, bd_strategy bs 
        where bt.user_uuid = bu.user_uuid and 
        bt.task_uuid = bri.task_uuid and 
        bt.strategy_id = bs.strategy_id and 
        bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'v2/description/Pf.php';
        $nodeHandler = Node::instance();
        if (!$data) {
            return array('flag' => false);
        }
        $stageArr = xphp_get_config('task', 'COMMON_STAGE');
        foreach ($data as $d) {
            $applianceInfo = $this->getJobAppliance($d['task_uuid']);
            $agentPoolInfo = $this->getJobAgentPoolInfo($d['task_uuid']);
            // 任务阶段
            $currentstage = !empty($stageArr[$d['current_stage']]) ?
                xphp_get_lang($stageArr[$d['current_stage']]) : xphp_get_config('app', 'NULLSPACE');
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }
            // 备份节点传输ip
            $backupServerIp = '';
            if ($d['backup_server_ip']) {
                $backupServerIp = json_decode($data[0]['backup_server_ip'], true);
                if (!is_array($backupServerIp)) {
                    // 老版本数据
                    $backupServerIp = [
                        [
                            'node_uuid' => $data[0]['node_uuid'],
                            'node_ip' => $data[0]['ip'],
                            'transfer_ip' => $data[0]['backup_server_ip']
                        ]
                    ];
                } else {
                    foreach ($backupServerIp as &$item) {
                        $nodeSql = "select ip from bd_node where node_uuid = ?";
                        $item['node_ip'] = $this->dbSelect($nodeSql, [$item['node_uuid']])[0]['ip'];
                    }
                }
            }
            $taskStopFlag = in_array($d['task_status'], [
                xphp_get_config('task', 'TASKSTATUS')['WAITTING'],
                xphp_get_config('task', 'TASKSTATUS')['STOPPED'],
            ]);
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
                'totalSize' => $taskStopFlag ? xphp_get_config('app')['NULLSPACE'] : v2_calsize($d['total_object_size'], true),
                'currentSize' => $taskStopFlag ? xphp_get_config('app')['NULLSPACE'] : v2_calsize($d['total_object_completed_size'], true),
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'taskStage' => $currentstage,
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'timeStrategyBackupType' => Backup::instance()->getTimeStrategyBackupType($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'], $d['task_uuid']),
                'transportStrategy' => $this->getTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $jobInfo['storage_info']['flag'] ? $jobInfo['storage_info'] : $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid']),
                'tape_strategy' => TapeInfo::instance()->getTapeGroupStrategy(array("group_uuid" => $jobInfo['storage_uuid'])) ?? '',
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                'applianceFlag' => $applianceInfo != '--',
                'applianceDes' => $applianceInfo,
                'agent_pool_info' => $agentPoolInfo,
                'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'fulldiskRestore' => Data::instance()->getRecoveryFullDisk($d['task_uuid']),
                'nosnapshot' => VmBackUp::instance()->getXenSnapshotFlag($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'backupNodeIp' => $backupServerIp,

                //华为CBR下云同步任务/备份上云任务新增数据完整性校验
                // 'verify' => $ptDes['INTEGRITY_CHECK_DES'][$d['is_integrity_check']], //是否开启数据完整性校验
                // 'verifyValue'=> intval($d['is_integrity_check']),
                // 'verifyType'=>$ptDes['INTEGRITY_CHECKTYPE_DES'][$d['calibration_algorithm']], //校验类型
                // 'verifyAlarm'=>$ptDes['INTEGRITY_CHECK_DES'][$d['error_handle']], //校验失败是否继续任务
                // 'verifyAlarmValue'=> intval($d['error_handle']),
                'verify' => xphp_get_lang('UI_PUBLIC_OFF_TWO'), //是否开启数据完整性校验
                'verifyValue' => 2,
                'verifyType' => "MD5", //校验类型
                'verifyAlarm' => xphp_get_lang('UI_PUBLIC_CONTINUE'), //校验失败是否继续任务
                'verifyAlarmValue' => 2,
                "nodename" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                // 重试策略
                'retry_strategy' => ExchangeJobInfo::instance()->getRetryStrategy($taskUUID),
                'ignore_resource_limiting_flag' => v2_parse_flag_to_bool($d['ignore_resource_limiting_flag']),
                // 安全策略
                'safe_strategy' => $jobInfo['safe_strategy'],
            );
            //如果是虚拟机备份添加备份模式配置
            if (intval($d['module_type']) == xphp_get_config('module', 'MODULE_TYPE')['VM']) {
                $basicInfo['modeStrategy'] = $this->getVMBackupModeStrategy($d['task_uuid']);
                if (intval($d['task_type']) == xphp_get_config('task', 'TASKTYPE')['SURE_BACKUP']) {
                    $basicInfo['modeStrategy'] = $this->getVerifyModeStrategy($d['task_uuid']);
                }

                //获取筛选条件
                $basicInfo['selectConditions'] = $this->getVMSelectConditions($d['task_uuid']);
                //获取是否自动加入备份
                $auto_join_flag = $this->getVMTaskDetail($d['task_uuid'])['auto_join_flag'];
                $basicInfo['autoJoinFlag'] = v2_parse_flag_to_bool($auto_join_flag);
            }
        }
        return $basicInfo;
    }

    /**
     * 得到虚拟机任务监控运行日志
     * @param array $params
     * @return array
     */
    public function getVMRunningJobLog(array $params): array
    {
        $taskuuid = $params['job_uuid'];
        $sql = "select btl.id, btl.task_name, btl.task_type, btl.user_name, btl.agent_name, btl.module_type, btl.submodule_type,
        btl.error_code, unix_timestamp(btl.op_time) op_time, btl.description_key, btl.description_param, btl.log_level, btl.id, btl.error_detail,
        vml.vm_config, bht.details 
        from bd_task_log btl left join vm_machine_list vml on btl.task_uuid = vml.task_uuid and btl.task_type = ? 
        left join bd_history_task bht on btl.task_uuid = bht.task_uuid 
        where btl.task_uuid = ? and btl.running_flag = ? group by btl.id order by btl.id desc";
        $sqlParams = array(xphp_get_config('task', 'TASKTYPE')['RECOVERY'], $taskuuid, xphp_get_config('app', 'FLAG')['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        foreach ($data as $d) {
            $des = LogHandler::instance()->getLogDescription(
                xphp_get_config('log', 'LOGTYPE')['TASK'],
                $d['error_code'],
                $d['description_key'],
                $d['description_param'],
                false,
                $d['log_level'],
                $d['module_type'],
                $d['submodule_type']
            );
            // 公有云替换描述
            if (in_array($d['submodule_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
                $vmConfig = $d['vm_config'] ?? '';
                $vmConfig = json_decode($vmConfig, true);
                $historyDetail = $d['details'] ?? '';
                $historyDetail = json_decode($historyDetail, true);
                // 任务运行时从vm_machine_list.vm_config获取恢复类型，完成时从历史任务详情获取是否有卷列表判断恢复类型
                if (
                    xphp_get_config('task', 'TASKTYPE')['RECOVERY'] == $d['task_type']
                    && (!empty($vmConfig) && 2 == $vmConfig['recovery_level'] || !empty($historyDetail[0]['disk_list']))
                ) {
                    // 卷恢复替换第一条描述的"实例"为"卷"
                    $des = str_replace(xphp_get_lang('WEB_LOG_TASK_GET_RECOVERY_INSTANCE_LIST'), xphp_get_lang('WEB_LOG_TASK_GET_RECOVERY_VOL_LIST'), $des);
                    // "开始恢复实例‘实例名（实例ID）’"改为"开始恢复实例‘实例名（实例ID）’的卷数据"
                    if (0 === strpos($des, xphp_get_lang('UI_RECOVERY_AWS_START_RECOVERY_INSTANCE'))) {
                        $des .= xphp_get_lang('UI_RECOVERY_AWS_VOL_DATA_OF_INSTANCE');
                    }
                }
            }
            if (trim($d['description_param']) && trim($d['description_param']) != 'null') {
                $trimmedInput = trim($d['description_param'], '[]\'');
                $array = explode('","', $trimmedInput);
                $processedArray = array_map(function ($value) {
                    return preg_replace('/^S:/', '', $value);
                }, $array);
                $output = implode(',', $processedArray);
                $text_des = $output . xphp_get_lang('WEB_' . $d['description_key']);
            } else {
                $text_des = xphp_get_lang('WEB_' . $d['description_key']);
            }
            $info[] = array(
                'time' => $this->parseDate($d['op_time']),
                'level_des' => LogHandler::instance()->getLogLevelDes($d['log_level']),
                'html_des' => urldecode($des),
                'level_value' => intval($d['log_level']),
                'text_des' => $text_des,
            );
        }
        return $info;
    }

    /**
     * 获取瞬时恢复任务的基本信息和虚拟机信息
     * @param array $params
     * @return array
     */
    public function getInstantBaseInfo(array $params): array
    {
        $taskUUID = $params['job_uuid'];
        $this->paramsCheck($taskUUID);
        $hypervisor = $this->getHypervisor($taskUUID);
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {

            $sql = "select bt.task_name, bt.task_status, bt.task_type, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state, vi.target_vcenter_uuid
	                from bd_task bt, vm_instant vi, vm_host vh
	                where bt.task_uuid = vi.task_uuid and
	        			  vh.vcenter_uuid = vi.target_vcenter_uuid and
	                	  bt.task_uuid = ? ";
        } else {
            $sql = "select bt.task_name, bt.task_status, bt.task_type, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state
	                from bd_task bt, vm_instant vi, vm_host vh
	                where bt.task_uuid = vi.task_uuid and
	        			  vh.vcenter_uuid = vi.target_vcenter_uuid and
	                      vh.host_uuid = vi.target_host_uuid and
	                	  bt.task_uuid = ? ";
        }
        $data = $this->dbSelect($sql, array($taskUUID));
        $hostIp = $data[0]['host_ip'];
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $tenantName = $this->getOpenstackTenant($data[0]['target_vcenter_uuid']);
            $hostIp = $data[0]['host_ip'] . '(' . $tenantName . ')';

        }
        $info = array(
            'server_ip' => $data[0]['nfs_server_ip'],
            'host_ip' => $hostIp,
            'vm_name' => $data[0]['new_vm_name'],
            'nfs_state' => intval($data[0]['nfs_datastore_state']),
            'host_state' => intval($data[0]['instant_host_state']),
            'vm_state' => intval($data[0]['new_vm_status']),
            'task_type' => intval($data[0]['task_type']),
        );

        if (
            intval($data[0]['new_vm_status']) == xphp_get_config('vm', 'MACHINESTATUS')['POWEREDOFF'] &&
            intval($data[0]['nfs_datastore_state']) == xphp_get_config('vm', 'VMDATASTORESTATUS')['CONNECT']
        ) {
            //虚拟机关机状态,NFS连接状态
            $info['nfs_state'] = 3;
        }
        if (intval($data[0]['nfs_datastore_state']) == xphp_get_config('vm', 'VMDATASTORESTATUS')['DISCONNECT']) {
            //NFS断开状态
            $info['vm_state'] = 0;
        }
        if (intval($data[0]['task_status']) == xphp_get_config('task', 'TASKSTATUS')['ERROR']) {
            //任务处于错误状态
            $info['nfs_state'] = 0;
            $info['host_state'] = 0;
            $info['vm_state'] = 0;
        }
        if (intval($data[0]['task_status']) == xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
            //任务处于停止状态
            $info['nfs_state'] = 0;
            $info['host_state'] = 0;
            $info['vm_state'] = 0;
        }

        return $info;
    }

    /**
     * 获取迁移任务的基本信息和虚拟机信息
     * @param array $params
     * @return array
     */
    public function getMotionBaseInfo(array $params): array
    {
        $taskUUID = $params['job_uuid'];
        $this->paramsCheck($taskUUID);
        $hypervisor = $this->getHypervisor($taskUUID);
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {

            $sql = "select bt.task_name, bt.task_status, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state, vi.target_vcenter_uuid,
                       vi.target_vcenter_uuid, vi.target_host_uuid, vi.motion_host_state
                from bd_task bt, vm_instant vi, vm_host vh
                where bt.task_uuid = vi.task_uuid and
                      vh.vcenter_uuid = vi.target_vcenter_uuid and
                	  bt.task_uuid = ? ";
        } else {
            $sql = "select bt.task_name, bt.task_status, vh.host_ip,
                	   vi.nfs_server_ip, vi.nfs_datastore_state, vi.new_vm_name, vi.new_vm_status, vi.instant_host_state,
                       vi.target_vcenter_uuid, vi.target_host_uuid, vi.motion_host_state
                from bd_task bt, vm_instant vi, vm_host vh
                where bt.task_uuid = vi.task_uuid and
                      vh.vcenter_uuid = vi.target_vcenter_uuid and
        			  vh.host_uuid = vi.target_host_uuid and
                	  bt.task_uuid = ? ";
        }
        $InstantData = $this->dbSelect($sql, array($taskUUID));
        $sql = "select vml.vm_name, vml.task_status, vml.dir_path, vml.new_name,
                       vml.vm_uuid, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid,
                        bt.task_status as bd_task_status
                from vm_machine_list vml
                left join bd_task bt
                on bt.task_uuid = vml.task_uuid
                where vml.task_uuid = ? group by vml.vm_uuid";
        $taskData = $this->dbSelect($sql, array($taskUUID));
        if (empty($taskData)) {
            $this->muOpResult(
                false,
                xphp_get_lang('WEB_LOG_TASK_DESC_KEY_LOAD_MIGRATION_TASK_INFO'),
                xphp_get_lang('WEB_LOG_TASK_DESC_KEY_LOAD_MIGRATION_TASK_INFO_ERROR')
            );
        }
        $instantIp = $InstantData[0]['host_ip'];
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $tenantName = $this->getOpenstackTenant($InstantData[0]['target_vcenter_uuid']);
            $instantIp = $InstantData[0]['host_ip'] . '(' . $tenantName . ')';

        }
        $info = array(
            'server_ip' => $InstantData[0]['nfs_server_ip'],                                //备份中心IP
            'instant_host_ip' => $instantIp,                                                //瞬时恢复宿主机IP
            'instant_vm_name' => $InstantData[0]['new_vm_name'],                            //瞬时恢复虚拟机名
            'nfs_state' => intval($InstantData[0]['nfs_datastore_state']),                  //NFS状态
            'instant_host_state' => intval($InstantData[0]['instant_host_state']),          //瞬时恢复宿主机状态
            'instant_vm_state' => intval($InstantData[0]['new_vm_status']),                 //瞬时恢复虚拟机状态
            'datastore_state' => $this->getDataStoreStatus($taskData[0]['bd_task_status']), //迁移数据状态
            'motion_vm_state' => 0,                                                         //暂时没有迁移虚拟机状态
            'motion_vm_name' => $taskData[0]['new_name'],                                   //迁移虚拟机名称
        );

        if (
            $InstantData[0]['target_vcenter_uuid'] == $taskData[0]['vcenter_uuid'] &&
            $InstantData[0]['target_host_uuid'] == $taskData[0]['host_uuid']
        ) {
            //如果是原机恢复
            $info['flag'] = 1;  //原机迁移
        } else {
            //异机恢复
            if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {

                $hostInfo = $this->getOpenstackDesHostInfo($taskData[0]['vcenter_uuid']);
            } else {
                $hostInfo = $this->getDesHostInfo($taskData[0]['vcenter_uuid'], $taskData[0]['host_uuid']);
            }
            $info['flag'] = 2;  //原机迁移
            $info['motion_host_ip'] = $hostInfo['hostIP'];  //迁移宿主机IP
            $info['motion_host_state'] = intval($InstantData[0]['motion_host_state']);  //迁移宿主机状态
        }

        return $info;
    }

    /**
     * 得到细粒度恢复任务详情
     * @param array $params
     * @return array
     */
    public function getVMGrainJobDetails(array $params): array
    {
        $taskuuid = $params['job_uuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_status, bt.module_type, bt.task_type, vgi.vm_name, bbt.timepoint, 
                bbt.backup_mode, vt.hypervisor_type from 
                bd_task bt, vm_task vt, vm_grain_info vgi left join bd_backup_timepoint bbt on vgi.timepoint_uuid = bbt.timepoint_uuid where 
                bt.task_uuid = vgi.task_uuid and bt.task_uuid = vt.task_uuid and 
                bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $ptDes = include APP_PATH . 'v2/description/Pf.php';
        $timepoint = $data[0]['timepoint'];
        if (empty($timepoint)) {
            $timepoint = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }
        return array(
            'status' => intval($data[0]['task_status']),
            'module' => intval($data[0]['module_type']),
            'hypervisor_type' => intval($data[0]['hypervisor_type']),
            'task_type' => intval($data[0]['task_type']),
            'status_des' => xphp_get_lang($ptDes['TASKSTATUSDES'][$data[0]['task_status']]),
            'vm_name' => $data[0]['vm_name'],
            'timepoint' => $timepoint,
            'backup_mode' => intval($data[0]['backup_mode']),
        );
    }

    /**
     * 获取细粒度恢复文件列表/搜索/加载更多
     * @param array $params
     * @return array
     */
    public function getVmGrainRecoveryFileDir(array $params): array
    {
        $task_uuid = $params['job_uuid'];
        $rootFlag = $params['root_flag'];
        $start = intval($params['offset']);
        $number = intval($params['limit']);
        $tmpFilePath = $params['tmp_file_path'];
        $path = $params['path'];
        $sclass = $params['sclass'];
        $showtype = intval($params['showtype']);
        $device = $params['device'];
        $searchName = $params['search_name'] ?? '';
        $firstSearchFlag = $params['first_search_flag'];

        $this->checkGrainRecoveryTaskStatus($task_uuid);
        //如果是请求根
        if ($rootFlag) {
            return $this->getVmGrainRecoveryFileDirFirst($task_uuid, $sclass, $showtype, $searchName);
        }

        $this->paramsCheck($task_uuid);

        $msg = array(
            'task_uuid' => $task_uuid,
            'guest_dir' => $path,
            'start_offset' => $start,
            'limit_size' => $number,
            'tmp_file_path' => $tmpFilePath,
            'display_mode' => $showtype,
            'device_path' => $device
        );

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR_NAMES';

        if (!empty($searchName)) {
            //如果有搜索
            $opName = 'VM_PRIVATE_TASK_OP_GRAIN_SEARCH_FILES';
            $searchArr = array(
                'search_pattern' => $searchName,
                'search_item_type' => 0,
                'start_mtime' => '',
                'end_mtime' => '',
                'suffix' => '',
            );
            $msg = array_merge($msg, $searchArr);
            //如果是同一个目录下第一次搜索,不带tmp目录
            if ($firstSearchFlag) {
                $msg['tmp_file_path'] = '';
            }
        }

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);

        //返回结果到UI
        $result = $mbResult['result'];
        if (!$result) {
            //失败
            $vmOpcode = VmOpcode::instance();
            $operate = $vmOpcode->getOpcodeDes($opName);
            $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        $list = array(
            "path" => $path,           //当前路径
            'job_uuid' => $task_uuid,
            "isFinished" => $mbResult['msg']['is_finished'],
            "tmpFilePath" => $mbResult['msg']['tmp_file_path'],
            "nextStart" => $start + $number
        );
        $fileList = array();
        $msgFileList = json_decode($mbResult['msg']['result_json_str'], true);
        foreach ($msgFileList as $d) {
            if ($path == "/") {
                $pathHead = $path;
            } else {
                $pathHead = $path . "/";
            }

            $filename = $d['name'];
            $filesize = v2_calsize($d['size'], true);
            $fileList[] = array(
                'file_name' => $filename,                  //文件名
                'file_size' => $filesize,                  //文件大小
                'modify_time' => $d['modify_time'],          //修改时间
                'details' => array(
                    "path" => $pathHead . $d['name'],                                              //路径
                    "type" => $this->getGrainFileType($d['type']),                    //文件类型
                    "isfile" => $d['type'] != xphp_get_config('vm', 'GUEST_FILE_ITEM_TYPE')['DIR'],   //是否是文件
                    "sclass" => $this->getGrainFileClassName($d['type'], $sclass),    //显示类型
                    "createTime" => $d['create_time'],
                    "modifyTime" => $d['modify_time'],
                    "size" => $d['size'],
                    "device" => $device,     //为了统一,此模式下设备为空
                    "btype" => $d['type'],
                ),
            );
        }
        $list['filelist'] = $fileList;

        return $list;
    }

    /**
     * 检查细粒度恢复文件是否可以下载
     * @param array $params
     * @return string
     */
    public function downloadGrainRecoveryFileCheck(array $params): string
    {
        $task_uuid = $params['job_uuid'];
        $filepath = $params['path'];
        $showtype = intval($params['showtype']);
        $device = $params['device'];
        $this->paramsCheck($task_uuid, $filepath, $showtype);

        $msg = array(
            "task_uuid" => $task_uuid,
            "guest_file_path" => $filepath,
            "read_offset" => 0,
            "read_len" => 1,
            'display_mode' => $showtype,
            'device_path' => $device
        );

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK';


        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);
        if (is_string($mbResult) && 1 == strlen($mbResult)) {
            //如果读取成功
            return $this->muOpResult(true, VmOpcode::instance()->getOpcodeDes($opName));
        } else {
            //失败,返回后台失败信息
            return $this->muOpResult($mbResult['result'], xphp_get_lang('WEB_GRAIN_RECOVERY_FILE_DOWNLOAD'), xphp_get_lang('WEB_GRAIN_RECOVERY_FILE_DOWNLOAD_ERROR'), '', $mbResult['errorCode']);
        }
    }

    /**
     * 下载细粒度恢复文件
     * @param array $params
     * @return void
     */
    public function downloadGrainRecoveryFile(array $params): void
    {
        $task_uuid = $_GET['job_uuid'];
        $filepath = $_GET['path'];
        $filesize = $_GET['size'];
        $filename = $_GET['name'];
        $showtype = intval($_GET['showtype']);
        $device = $_GET['device'];
        $this->paramsCheck($task_uuid, $filepath, $filesize, $filename);

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK';

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $filesize);
        Header("Content-Disposition: attachment; filename=" . $filename);
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                "task_uuid" => $task_uuid,
                "guest_file_path" => $filepath,
                "read_offset" => $i,
                "read_len" => $readLen,
                'display_mode' => $showtype,
                'device_path' => $device
            );
            $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);
            echo $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
    }

    /**
     * 细粒度下载目录检查
     * @param array $params
     * @return string
     */
    public function downloadGrainRecoveryDirCheck(array $params): string
    {
        $task_uuid = $params['job_uuid'];
        $filepath = $params['path'];
        $showtype = intval($params['showtype']);
        $device = $params['device'];
        $this->paramsCheck($task_uuid, $filepath, $showtype);

        $uuid = xphp_uuid();

        $msg = array(
            "task_uuid" => $task_uuid,
            "key" => $uuid,
            "dir_path" => $filepath,
            'display_mode' => $showtype,
            'device_path' => $device
        );

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR';

        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), false);

        //         var_dump($mbResult,$nodeuuid, $hypervisor, $opName, $msg);
        //         return;
        if ($mbResult['result']) {
            //如果打开成功
            return $this->muOpResult(true, $opName, '', '', $mbResult['errorCode'], array('uuid' => $uuid));
        } else {
            //失败,返回后台失败信息
            return $this->muOpResult(
                $mbResult['result'],
                xphp_get_lang('VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR'),
                xphp_get_lang('VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR'),
                '',
                $mbResult['errorCode'],
                array('uuid' => $uuid)
            );
        }
    }

    /**
     * 细粒度下载目录
     * @return void
     */
    public function downloadGrainRecoveryDir()
    {
        $task_uuid = $_GET['job_uuid'];
        $filepath = $_GET['path'];
        $filename = $_GET['name'];
        $showtype = intval($_GET['showtype']);
        $device = $_GET['device'];
        $uuid = $_GET['uuid'];

        $this->paramsCheck($task_uuid, $filepath, $filename, $showtype, $uuid);

        $msg = array(
            "task_uuid" => $task_uuid,
            "key" => $uuid,
            "dir_path" => $filepath,
            'display_mode' => $showtype,
            'device_path' => $device,
            'read_len' => xphp_get_config('vm', 'GRAIN_FILE_BLOCK_SIZE')
        );

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR';

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        //         Header("Accept-Length: " . $filesize);
        Header('Content-Disposition: attachment; filename="' . $filename . '.tar.gz"');

        $this->readGrainRecoveryDirContent($nodeuuid, $hypervisor, $opName, $msg);
    }

    /**
     * 循环读取细粒度恢复目录内容
     */
    private function readGrainRecoveryDirContent($nodeuuid, $hypervisor, $opName, $msg)
    {
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);
        //$mbResult socket.class中特殊处理
        //返回数据  array('head'=>array(),'msg'=>string)
        /*!!!!!!!!!!!!!!!!!!!!!!!!!!!注意这几个下标从1开始的!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * uint32    reserved   保留字段
         * uint32    is_eof     是否读完   1读完 0未读完
         * unit32    error_code 错误码，正确为0
         * uint32    size       本次读取大小
         * */
        //         var_dump($mbResult['head']);
        if (0 == $mbResult['head'][3]) {
            //如果成功,输出内容
            echo $mbResult['msg'];
            //判断是否结束,结束关闭读取,未结束,继续读取
            if (1 == $mbResult['head'][2]) {
                //结束
                $opName = 'VM_PRIVATE_TASK_OP_GRAIN_STOP_DOWNLOAD_DIR';
                $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), false);
                //                 var_dump($mbResult);
            } else {
                //未结束
                $mbResult = null;
                $this->readGrainRecoveryDirContent($nodeuuid, $hypervisor, $opName, $msg);
            }
        } else {
            exit('Read Dir error!111');
        }
    }

    /**
     * 得到细粒度恢复任务展示模式
     * @param array $params
     * @return array 返回不展示的项
     */
    public function getVmGrainRecoveryShowType(array $params): array
    {
        $task_uuid = $params['job_uuid'];
        $this->paramsCheck();

        $sql = "select task_status, os_type, root_list from vm_grain_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        if (empty($data[0]['root_list'])) {
            return array();
        }

        $rootList = json_decode($data[0]['root_list'], true);
        $allShowType = array(
            xphp_get_config('vm', 'GUEST_DISPLAY_MODE')['SYSTEM'],
            xphp_get_config('vm', 'GUEST_DISPLAY_MODE')['NORMAL_DEVICE'],
            xphp_get_config('vm', 'GUEST_DISPLAY_MODE')['LOGICAL_DEVICE'],
        );
        $showType = array();
        foreach ($rootList['guest_struct'] as $struct) {
            $showType[] = $struct['display_mode'];
        }

        return ['not_show' => array_values(array_diff($allShowType, $showType))];
    }

    /**
     * 得到任务流量
     * @param array $params 参数
     * @return array
     */
    public function getTaskSpeed(array $params): array
    {
        $taskUUID = $params['job_uuid'];
        $this->paramsCheck($taskUUID);

        $sql = "select bt.module_type, bri.speed, bri.speed_time, bt.task_status from bd_running_info bri, bd_task bt 
                where bri.task_uuid = bt.task_uuid and bri.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));

        $speed = 0;
        if ($data) {
            if (
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
            ) {
                $speedCount = intval($data[0]['speed']);
                if ($speedCount < 0) {
                    $speedCount = 0;
                }
                $speed = round($speedCount / 1024, 2);
            }
        }

        if (time() - $data[0]['speed_time'] > 12) {
            //如果长时间没有更新速度,处理速度为0
            $speed = 0;
        }

        return array(
            'speed' => $speed,
            'test' => $data[0]['speed_time'],
            't' => time(),
            'nowTime' => date('H:i:s')
        );
    }

    /**
     * 从配置文件得到系统支持的所有虚拟化类型
     * @param array $params 参数
     * @return array
     */
    public function getAllHypervisorType($params = [])
    {
        $tenantFlag = $params['tenant_flag'];   //租户内部
        $userFlag = $params['user_flag'];    //用户拥有
        $cloudFlag = $params['cloud_flag']; //是否是云平台
        $cloudType = $params['cloud_type'] ?? 'public'; // 云平台类型：public/private
        $allTypeFlag = $params['all_type_flag']; //获取虚拟化+私有云平台
        $extension = (new Index())->getExtensionLicense();
        $list = [];
        if (!empty($extension)) {
            $list = v2_license_get_v(2);
        } else {
            //未授权出厂虚拟化
            if (!empty(xphp_get_config('vm', 'RELEASE_HYPERVISOR'))) {
                //如果存在指定选择需要的虚拟化 直接应用
                $list = xphp_get_config('vm', 'RELEASE_HYPERVISOR');
            }
        }
        //        dump($list);
        $hypervisors = [];
        $vendor = xphp_get_config('vendor');
        if ($cloudFlag) {
            if ('private' == $cloudType) {
                $hypervisor = $vendor['CLOUDHYPERVISORTYPE'];
                $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE')['PRIVATE_CLOUD'];
            } else {
                $hypervisor = $vendor['PUBLICCLOUDHYPERVISORTYPE'];
                $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE')['PUBLIC_CLOUD'];
            }
        } else {
            $hypervisor = $vendor['VMHYPERVISORTYPE'];
            $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE')['VM'];
        }
        if ($allTypeFlag) {
            $hypervisor = $vendor['VMHYPERVISORTYPE'] + $vendor['CLOUDHYPERVISORTYPE'];
        }
        if (!empty($list)) {
            foreach ($hypervisor as $key => $value) {
                foreach ($list as $h) {
                    if ($h == $key) {
                        $hypervisors[$key] = $value;
                    }
                }
            }
        } else {
            $hypervisors = $hypervisor;
        }
        $info = [];
        $user = xphp_get_user_info();
        $vmHypervisor = $this->getTenantHypervisor($user['tenantuuid'], $resourceType);
        $userHypervisor = $this->getUserHypervisor($user['userUuid'], $resourceType);
        foreach ($hypervisors as $key => $value) {
            if (($tenantFlag && !in_array($key, $vmHypervisor)) || ($userFlag && !in_array($key, $userHypervisor))) {
                continue;
            }
            $info[] = array(
                'text' => $value,
                'value' => $key
            );
        }
        return $info;
    }

    /**
     * 获取分配的虚拟化类型
     * @param string $tenantuuid 租户uuid
     * @param int $resourceType 资源类型
     * @return array|fetchAll()[]
     */
    public function getTenantHypervisor($tenantuuid = '', $resourceType = 3)
    {
        if (empty($tenantuuid)) {
            return [];
        }
        // 获取租户下所有资源
        $resourceList = (new \app\v2\resources\v0\logic\Index())->pGetTenantAllResource(
            $tenantuuid,
            $resourceType
        );
        $vcenterUuids = $list = [];
        if (!empty($resourceList)) {
            foreach ($resourceList as $resource) {
                $vcenterUuids[] = $resource['vcenter_uuid'];
            }
            $vcenterDes = implode("','", $vcenterUuids);
            $sql = "select distinct hypervisor_type from vm_vcenter where vcenter_uuid in ('" . $vcenterDes . "')";
            $data = $this->dbSelect($sql);
            if (!empty($data)) {
                foreach ($data as $d) {
                    $list[] = $d['hypervisor_type'];
                }
            }
        }

        return $list;
    }

    /**
     * 获取用户所有虚拟化类型
     * @param string $useruuid 用户uuid
     * @param int $resourceType 资源类型
     * @return array|fetchAll()[]
     */
    public function getUserHypervisor($useruuid = '', $resourceType = 3)
    {
        if (empty($useruuid)) {
            return [];
        }

        // 获取当前用户下所有资源
        $resourceList = (new \app\v2\resources\v0\logic\Index())->pGetUserAllResource(
            $useruuid,
            $resourceType
        );

        $vcenterUuids = $list = [];
        $user = xphp_get_user_info();
        if (!empty($user['tenantuuid'])) {
            if (!empty($resourceList)) {
                foreach ($resourceList as $resource) {
                    $vcenterUuids[] = $resource['vcenter_uuid'];
                }
                $vcenterDes = implode("','", $vcenterUuids);
                $sql = "select distinct hypervisor_type from vm_vcenter where vcenter_uuid in ('" . $vcenterDes . "')";
                $data = $this->dbSelect($sql);
                if (!empty($data)) {
                    foreach ($data as $d) {
                        $list[] = $d['hypervisor_type'];
                    }
                }
            }
        } else {
            //租户外直接读取用户创建的虚拟化中心
            $sql = "select distinct hypervisor_type from vm_vcenter where user_uuid = ?";
            $data = $this->dbSelect($sql, array($useruuid));
            if (!empty($data)) {
                foreach ($data as $d) {
                    $list[] = $d['hypervisor_type'];
                }
            }
        }

        return $list;
    }

    /**
     * 获取任务的虚拟机列表
     * @param array $params 参数
     * @return array
     */
    public function getJobVms(array $params): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $taskUuid = $params['job_uuid'];
        $searchName = $params['search_name'];
        $sql = "select vt.hypervisor_type, bt.task_type, bt.task_status, bt.module_type 
            from vm_task vt 
            join bd_task bt on vt.task_uuid = bt.task_uuid 
            where vt.task_uuid = ?";
        $taskData = $this->dbSelect($sql, [$taskUuid])[0];
        $taskType = (int) $taskData['task_type'];
        $hypervisorType = (int) $taskData['hypervisor_type'];
        $taskStatus = (int) $taskData['task_status'];
        $moduleType = (int) $taskData['module_type'];
        $sql = "select vml.vm_name, vml.mode, vml.vm_size, vml.vm_valid_size, vml.completed_size, vml.transport_size, 
            vml.write_size, vml.task_status, vml.dir_path, vml.new_name, vml.vm_uuid, 
            vml.error_code, vml.datastore, vml.new_name, vml.timepoint_uuid, vml.vcenter_uuid, vml.host_uuid, vml.speed as vm_speed,
            bri.speed, bri.speed_time, bri.current_object_transport_size, bri.total_object_transport_size, 
            bri.current_object_write_size, bri.current_object_completed_size 
                from vm_machine_list vml 
                left join bd_running_info bri on vml.task_uuid = bri.task_uuid 
                where vml.task_uuid = ? and vml.delete_flag != 1 ";
        $sqlCount = "select count(distinct vml.vm_uuid) as total
                from vm_machine_list vml 
                where vml.task_uuid = ? and vml.delete_flag != 1 ";
        if ($searchName) {
            $sql .= " and vml.vm_name like '%{$searchName}%'";
            $sqlCount .= " and vml.vm_name like '%{$searchName}%'";
        }
        $sql .= " group by vml.machine_id order by vml.machine_id limit ? , ? ";
        $data = $this->dbSelect($sql, array($taskUuid, $start, $length));
        $count = $this->dbSelect($sqlCount, array($taskUuid));
        $records = array();
        $i = 1;
        $records['rows'] = array();
        $jobInfo = JobInfo::instance();
        // 处理支持并行传输的虚拟化
        $parallelHypervisors = [
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM'],
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XFUSION_KVM']
        ];
        $parallelFlag = in_array($hypervisorType, $parallelHypervisors);

        foreach ($data as $d) {
            $d['task_type'] = $taskType;
            //如果任务正在运行,需要过滤掉新添加的虚拟机
            if (
                $taskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
                $d['task_status'] == xphp_get_config('vm', 'VmTaskStatus')['NEW_ADD']
            ) {
                continue;
            }
            // 非备份任务
            if (1 != $taskType) {
                $parallelFlag = false;
            }
            if (
                $taskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
                $d['task_status'] == xphp_get_config('vm', 'VmTaskStatus')['UNKNOWN']
            ) {
                $records['rows'][] = array(
                    'checkbox_html' => '<input type="checkbox" name="id' . $d['vm_uuid']
                        . '" value="' . $d['vm_uuid'] . '">',
                    'no' => $i++,
                    'name' => $d['vm_name'],
                    //备份模式（完备、增量、差异、永久）或恢复类型（实例恢复、卷恢复）
                    'task_type' => xphp_get_config('app', 'NULLSPACE'),
                    'size' => xphp_get_config('app', 'NULLSPACE'),
                    'valid_size' => xphp_get_config('app', 'NULLSPACE'),
                    'transport_size' => xphp_get_config('app', 'NULLSPACE'),
                    'write_size' => xphp_get_config('app', 'NULLSPACE'),
                    'speed' => xphp_get_config('app', 'NULLSPACE'),
                    'percent' => xphp_get_config('app', 'NULLSPACE'),
                    'status' => xphp_get_config('app', 'NULLSPACE'),
                    'task_status' => intval($d['task_status']),
                    'description' => '',
                    'detail' => $this->getVMDetailsInfo($d, $taskUuid),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'hypervisor_type' => $hypervisorType,
                    'vm_uuid' => $d['vm_uuid']
                );
            } else {
                $speed = '--';
                if (xphp_get_config('task', 'TASKSTATUS')['STOPPING'] != $taskStatus) {
                    $speed = $parallelFlag
                        ? $this->getVMSpeed($d['task_status'], intval($d['vm_speed']), $d['speed_time'], $moduleType)
                        : $this->getVMSpeed($d['task_status'], intval($d['speed']), $d['speed_time'], $moduleType);
                }
                $records['rows'][] = array(
                    'checkbox_html' => '<input type="checkbox" name="id' . $d['vm_uuid']
                        . '" value="' . $d['vm_uuid'] . '">',
                    'no' => $i++,
                    'name' => $d['vm_name'],
                    //备份模式（完备、增量、差异、永久）或恢复类型（实例恢复、卷恢复）
                    'task_type' => $this->getCurrentVMListTaskType($taskType, $d['mode'], $taskStatus),
                    'size' => $this->getVMListSize($taskStatus, $d['vm_size']),
                    'valid_size' => $this->getVMListSize($taskStatus, $d['vm_valid_size']),
                    'transport_size' => $this->getVMCompletedSize(
                        $d['current_object_transport_size'],
                        $d['transport_size'],
                        $taskStatus,
                        $d['task_status'],
                        false,
                        $parallelFlag
                    ),
                    'write_size' => $this->getVMCompletedSize(
                        $d['current_object_write_size'],
                        $d['write_size'],
                        $taskStatus,
                        $d['task_status'],
                        false,
                        $parallelFlag
                    ),
                    'speed' => $speed,
                    'percent' => $this->getVMPercent(
                        $d['vm_valid_size'],
                        $d['current_object_transport_size'],
                        $d['transport_size'],
                        $taskStatus,
                        $d['task_status'],
                        false,
                        $parallelFlag
                    ),
                    'status' => $this->getVMStatus($taskStatus, $d['task_status']),
                    'task_status' => intval($d['task_status']),
                    'description' => $jobInfo->getErrorCodeDes($d['task_status'], $d['error_code']),
                    'detail' => $this->getVMDetailsInfo($d, $taskUuid),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'hypervisor_type' => $hypervisorType,
                    'vm_uuid' => $d['vm_uuid']
                );
            }
        }
        $records['total'] = $count[0]['total'];
        return $records;
    }

    /**
     * 得到虚拟机列表任务状态
     * @param int    $taskType      任务类型：1备份，2恢复
     * @param int    $currentMode   任务模式：备份模式（1完备，2增量，3差异，9永久）或恢复模式（1实例，2卷）
     * @param int    $taskStatus    任务状态
     * @param string $submoduleType 子模块类型
     * @return string
     */
    public function getCurrentVMListTaskType(
        int $taskType,
        int $currentMode,
        int $taskStatus,
        $submoduleType = ''
    ): string {
        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //如果任务不在运行状态,这个时候不知道任务时什么类型的
            return xphp_get_config('app', 'NULLSPACE');
        }
        return JobInfo::instance()->getHistoryTaskType($taskType, $currentMode, $submoduleType);
    }

    /**
     * 得到虚拟机列表的大小
     * @param int $taskStatus 任务状态
     * @param int $size       大小
     * @return string
     */
    protected function getVMListSize(int $taskStatus, int $size): string
    {
        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //如果任务不在运行状态,这个时候不显示大小
            return xphp_get_config('app', 'NULLSPACE');
        }
        return v2_calsize($size, true);
    }

    /**
     * 得到虚拟机列表的已完成容量
     * @param int  $briCompletedSize 运行时完成容量
     * @param int  $vmlCompletedSize 已完成容量
     * @param int  $bdTaskStatus     任务状态
     * @param int  $vmTaskStatus     任务状态
     * @param bool $copyFlag         是否是副本
     *                               因为枚举不一样所以分开处理
     * @param bool $parallelFlag     是否支持并行传输
     * @return string
     */
    protected function getVMCompletedSize(
        int $briCompletedSize,
        int $vmlCompletedSize,
        int $bdTaskStatus,
        int $vmTaskStatus,
        bool $copyFlag = false,
        bool $parallelFlag = false
    ): string {
        if (
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //首先任务是在运行状态
            if ($copyFlag) {
                if ($vmTaskStatus == xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_RUNNING']) {
                    //虚拟机是运行状态,显示bd_running_info的完成大小
                    return v2_calsize($briCompletedSize, true);
                } elseif ($vmTaskStatus == xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_WAITTING']) {
                    //虚拟机是等待状态,显示0B
                    return '0B';
                } else {
                    //虚拟机不在运行状态,显示vm_machine_list的完成大小
                    return v2_calsize($vmlCompletedSize, true);
                }
            } else {
                if ($vmTaskStatus == xphp_get_config('vm', 'VmTaskStatus')['RUNNING']) {
                    //虚拟机是运行状态
                    return $parallelFlag ? v2_calsize($vmlCompletedSize, true) : v2_calsize($briCompletedSize, true);
                } elseif ($vmTaskStatus == xphp_get_config('vm', 'VmTaskStatus')['WAITTING']) {
                    //虚拟机是等待状态,显示0B
                    return '0B';
                } else {
                    //虚拟机不在运行状态,显示vm_machine_list的完成大小
                    return v2_calsize($vmlCompletedSize, true);
                }
            }
        } else {
            return xphp_get_config('app', 'NULLSPACE');
        }
    }

    /**
     * 根据每个虚拟机的状态得到速度
     * @param int  $status     状态
     * @param int  $speed      速度
     * @param int  $speedTime  时间
     * @param int  $moduleType 虚拟化类型
     * @param bool $copyFlag   是否副本任务
     * @return string
     */
    public function getVMSpeed(int $status, int $speed, int $speedTime, $moduleType = 0, $copyFlag = false): string
    {
        if (time() - $speedTime > 12) {
            $speed = 0;
        }
        if ($copyFlag) {
            if (xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_RUNNING'] == intval($status)) {
                $speed = v2_calspeed($speed);
            } else {
                $speed = '--';
            }
        } else {
            if (xphp_get_config('vm', 'VmTaskStatus')['RUNNING'] == intval($status)) {
                $speed = v2_calspeed($speed);
            } else {
                $speed = '--';
            }
        }
        return $speed;
    }

    /**
     * 得到虚拟机列表百分比
     * @param int  $vmSize           虚拟机大小
     * @param int  $briCompletedSize 运行时完成容量
     * @param int  $vmlCompletedSize 已完成容量
     * @param int  $bdTaskStatus     bd任务状态
     * @param int  $vmTaskStatus     vm任务状态
     * @param bool $copyFlag         是否副本任务
     * @param bool $parallelFlag     是否支持并行传输
     * @return string
     */
    protected function getVMPercent(
        int $vmSize,
        int $briCompletedSize,
        int $vmlCompletedSize,
        int $bdTaskStatus,
        int $vmTaskStatus,
        bool $copyFlag = false,
        bool $parallelFlag = false
    ): string {
        if (
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //首先任务是在运行状态
            if ($copyFlag) {
                if ($vmTaskStatus == xphp_get_config('copy', 'CopyTaskStatus')['RUNNING']) {
                    return v2_calpercent($vmSize, $briCompletedSize);
                } elseif ($vmTaskStatus == xphp_get_config('copy', 'CopyTaskStatus')['WAITTING']) {
                    return '0%';
                } else {
                    if (0 == $vmlCompletedSize) {
                        return '0%';
                    }
                    if ($vmSize == $vmlCompletedSize) {
                        return '100%';
                    }
                    return v2_calpercent($vmSize, $vmlCompletedSize);
                }
            } else {
                if ($vmTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                    return $parallelFlag ? v2_calpercent($vmSize, $vmlCompletedSize) : v2_calpercent($vmSize, $briCompletedSize);
                } elseif ($vmTaskStatus == xphp_get_config('task', 'TASKSTATUS')['WAITTING']) {
                    return '0%';
                } else {
                    if (0 == $vmlCompletedSize) {
                        return '0%';
                    }
                    if ($vmSize == $vmlCompletedSize) {
                        return '100%';
                    }
                    return v2_calpercent($vmSize, $vmlCompletedSize);
                }
            }
        } else {
            return xphp_get_config('app', 'NULLSPACE');
        }
    }

    /**
     * 得到虚拟机列表的虚拟机状态
     * @param int  $bdTaskStatus bd任务状态
     * @param int  $vmTaskStatus vm任务状态
     * @param bool $copyFlag     是否副本任务
     * @return string
     */
    public function getVMStatus(int $bdTaskStatus, int $vmTaskStatus, $copyFlag = false): string
    {
        if (
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
            $bdTaskStatus == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //任务在运行状态时,显示虚拟机的状态
            $vmDes = include APP_PATH . 'v2/description/Vm.php';
            if ($copyFlag) {
                return xphp_get_lang($vmDes['CopyTaskStatus'][$vmTaskStatus]);
            } else {
                if (xphp_get_config('vm', 'VmTaskStatus')['UNKNOWN'] == $vmTaskStatus) {
                    // 任务为运行中/异常时，虚拟机状态为0代表未加入此次任务所以显示为--
                    return xphp_get_config('app', 'NULLSPACE');
                }
                return xphp_get_lang($vmDes['VmTaskStatus'][$vmTaskStatus]);
            }
        } else {
            return xphp_get_config('app', 'NULLSPACE');
        }
    }

    /**
     * 得到任务虚拟机详情
     * @param array  $d        虚拟机信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    protected function getVMDetailsInfo(array $d, string $taskUuid): array
    {
        if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP']) {
            //虚拟机备份
            return $this->getBackupDetailsInfo($d);
        } elseif ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['RECOVERY']) {
            //虚拟机恢复
            return $this->getRecoveryDetailsInfo($d, $taskUuid);
        }
    }

    /**
     * 得到备份任务虚拟机详情
     * @param array $d 虚拟机信息
     * @return array
     */
    protected function getBackupDetailsInfo(array $d): array
    {
        return array(
            'type' => intval($d['task_type']),
            'sPath' => $d['dir_path'],
            'objName' => $d['objName']
        );
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
            $desHostInfo = $this->getDesHostInfo($d['vcenter_uuid'], $d['host_uuid']);
        }
        $timepointInfo = $this->getTimepointInfo($d['timepoint_uuid']);
        $timepointDes = $timepointInfo['taskname'] . ' => ' . $timepointInfo['timepoint'];
        //时间点被意外删除
        if (empty($timepointInfo)) {
            $timepointDes = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }
        return array(
            'type' => intval($d['task_type']),
            'path' => $d['dir_path'],
            'dVcenterIP' => $desHostInfo['vcenterIP'],
            'dHostIP' => $desHostInfo['hostIP'],
            'dName' => $d['new_name'],
            'timepoint' => $timepointInfo['timepoint'],
            'taskname' => $timepointInfo['taskname'],
            'storage' => $d['datastore'],
            'timepoint_des' => $timepointDes
        );
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
        return intval($data[0]['hypervisor_type']);
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
                    '(' . JobInfo::instance()->getTimepointTypeDes($data[0]['backup_mode']) . ')',
                'taskname' => $data[0]['task_name']
            );
        }
        return ['timepoint' => '', 'taskname' => ''];
    }

    /**
     * 得到任务监控界面的任务类型 ,主要是获取虚拟机的子模块
     * @param string $taskTypeDes
     * @param int    $moduleType
     * @param string $taskuuid
     */
    protected function getBasicInfoTaskTypeDes($taskTypeDes, $moduleType, $taskuuid)
    {
        if ($moduleType == xphp_get_config('module')['MODULE_TYPE']['VM']) {
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $hypervisor = intval($data[0]['hypervisor_type']);
            $taskTypeDes .= '[' . xphp_get_config('vm')['VMHYPERVISORDES'][$hypervisor] . ']';
        }
        return $taskTypeDes;
    }

    /**
     * 根据任务状态计算速度
     * @param int $taskStatus
     * @param int $speed
     */
    protected function getJobSpeed($taskStatus, $speed)
    {
        if ($taskStatus != xphp_get_config('task')['TASKSTATUS']['RUNNING']) {
            //如果任务没有运行
            return xphp_get_config('app')['NULLSPACE'];
        }
        return v2_calspeed($speed);
    }

    /**
     * 根据任务状态计算进度
     * @param int     $taskStatus
     * @param int     $totalSize
     * @param int     $currentSize
     * @param boolean $percentFlag 是否一定要得到百分比格式
     */
    public function getTaskTotalProgress($taskStatus, $totalSize, $currentSize, $percentFlag, $tasktype)
    {
        if (
            $tasktype == xphp_get_config('task')['TASKTYPE']['VM_FILE_RECOVERY'] ||
            $tasktype == xphp_get_config('task')['TASKTYPE']['VM_INSTANT_RECOVERY'] ||
            $tasktype == xphp_get_config('task')['TASKTYPE']['DB_CDP_BACKUP'] ||
            $tasktype == xphp_get_config('task')['TASKTYPE']['FILE_CDP_BACKUP'] ||
            $tasktype == xphp_get_config('task')['TASKTYPE']['DB_BACKUP']
        ) {
            //细粒度恢复,瞬时恢复,数据库实时备份,文件实时备份不显示进度
            return xphp_get_config('app')['NULLSPACE'];
        }

        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['PAUSED'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //如果任务没有运行
            if ($percentFlag) {
                return '0%';
            } else {
                return xphp_get_config('app')['NULLSPACE'];
            }
        }

        if (0 == $totalSize) {
            return '0%';
        }
        $speed = v2_calpercent($totalSize, $currentSize);
        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        return sprintf('%.2f', substr($speed, 0, -1)) . '%';
    }

    /**
     * 过滤开始时间
     * @param unknown $startTime
     * @return string
     */
    public function getStartTIme($startTime, $status)
    {
        if (
            $status == xphp_get_config('task')['TASKSTATUS']['RUNNING'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['ABNORMAL'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['SUCCESSED']
        ) {
            return $this->parseDate($startTime);
        }
        return xphp_get_config('app')['TIMESPACE'];
    }

    /**
     * 得到任务运行的持续时间
     * @param timestamp $startTime
     */
    public function getTimeInterval($startTime, $status)
    {
        if (
            $status == xphp_get_config('task')['TASKSTATUS']['RUNNING'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['ABNORMAL'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['SUCCESSED']
        ) {
            $nowTime = Time::instance()->getSystemTime();
            if (!$startTime || $startTime == 0) {
                return xphp_get_config('app')['TIMESPACE'];
            }
            $intval = $nowTime - $startTime;
            return v2_sec_to_time($intval);
        }
        return xphp_get_config('app')['TIMESPACE'];
    }

    /**
     * 根据任务运行情况估算完成时间
     * @param int $totalSize
     * @param int $currentSize
     * @param int $taskStatus
     * @param int $speed
     */
    protected function getCalEndTime($totalSize, $currentSize, $taskStatus, $speed)
    {
        if (
            $taskStatus != xphp_get_config('task')['TASKSTATUS']['RUNNING'] &&
            $taskStatus != xphp_get_config('task')['TASKSTATUS']['ABNORMAL']
        ) {
            //任务没有运行
            return xphp_get_config('app')['TIMESPACE'];
        }
        if (0 == intval($speed)) {
            return xphp_get_config('app')['TIMESPACE'];
        }
        $needTime = ($totalSize - $currentSize) / $speed;
        return date('Y-m-d H:i:s', time() + intval($needTime));
    }

    /**
     * 过滤下次启动时间
     * @param unknown $nextTime   下次开始时间
     * @param unknown $taskStatus 任务状态
     */
    public function getNextStartTime($nextTime, $taskStatus)
    {
        $nowTime = time();
        if ($nextTime <= 0) {
            return xphp_get_config('app')['TIMESPACE'];
        }
        if ($nextTime < $nowTime) {
            return xphp_get_config('app')['TIMESPACE'];
        }

        //任务处于停止状态下,没有下次开始时间
        if (intval($taskStatus) == xphp_get_config('task')['TASKSTATUS']['STOPPED']) {
            return xphp_get_config('app')['TIMESPACE'];
        }
        return $this->parseDate($nextTime);
    }

    /**
     * 得到时间策略信息，重载方法
     * @param int $strategyID
     * @return array
     */
    public function getJobTimeStrategy(int $strategyID): array
    {
        //         $this->paramsCheck($strategyID);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time ,full_backup_compensation_flag
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $strategy = array();
        foreach ($data as $d) {
            $startTime = $d['start_time'];
            if (
                intval($d['strategy_type']) == xphp_get_config('task')['STRATEGY_TYPE']['ONCE']
                && strtotime($d['start_time']) < time()
            ) {
                $startTime = $d['start_time'] . '(' . xphp_get_lang('UI_PUBLIC_EXPIRED') . ')';
            }
            $strategy[] = array(
                //只有一条增量则是永久增量
                'mode' => 1 == count($data)
                    && xphp_get_config('task')['BACKUP_MODE']['INCREMENTAL'] == $data[0]['mode']
                    ? xphp_get_config('task')['BACKUP_MODE']['PINCREMENTAL']
                    : $d['mode'],
                'type' => $d['strategy_type'],// 1是天,2是周,3是月,4是一次性
                'days' => $this->getDaysArr($d['days']),
                'frequency' => $this->getFrequency($d['strategy_type'], $d['days']),
                'startTime' => $startTime,
                'rollFlag' => xphp_get_config('app')['FLAG']['SET'] == intval($d['roll_flag']),
                'rollInterval' => v2_sec_to_time(intval($d['roll_interval'])),
                'endTime' => $d['roll_end_time'],
                'full_backup_compensation_flag' => v2_parse_flag_to_bool($d['full_backup_compensation_flag']),
            );
        }
        return $strategy;
    }

    /**
     * 得到保留策略信息
     * @param int $strategyID
     * @return array
     */
    public function getReservedStrategy($strategyID, $taskuuid)
    {
        $this->paramsCheck($strategyID);
        $sql = "select strategy_type, number, auto_archive, strategy_mode from bd_reserved_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $reserved = false;

        $sqlGFS = "select level1_type, level2_type, retention_num from bd_task_gfs_retention_strategy 
            where task_uuid = ?";
        $dataGFS = $this->dbSelect($sqlGFS, array($taskuuid));
        $thisGFS = array();
        if (!empty($dataGFS)) {
            foreach ($dataGFS as $d) {
                switch ($d['level1_type']) {
                    case 1:
                        $thisGFS['week'] = array(
                            $d['level2_type'],
                            $d['retention_num'],
                            'checked',
                        );
                        break;
                    case 2:
                        $thisGFS['month'] = array(
                            $d['level2_type'],
                            $d['retention_num'],
                            'checked',
                        );
                        break;
                    case 3:
                        $thisGFS['year'] = array(
                            $d['level2_type'],
                            $d['retention_num'],
                            'checked',
                        );
                        break;
                }
            }
            ;
        }
        ;
        foreach ($data as $d) {
            $reserved = array(
                'type' => $d['strategy_type'],
                'strategyMode' => $d['strategy_mode'],
                'value' => $d['number'],
                'archive' => $d['auto_archive'],
                'GFSinfo' => $thisGFS,
            );
        }
        return $reserved;
    }

    /**
     * 得到任务传输策略
     * @param string $taskuuid   任务UUID
     * @param int    $strategyid 策略ID
     */
    public function getTransportStrategy($taskuuid, $strategyid)
    {
        $info = array();
        $sql = "select hypervisor_type, transport_priority, agent_uuid, detail, parallel_transfer_vm_count, single_vm_parallel_disk_transfer_count, vm_single_disk_parallel_transfer_count from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $detailArr = json_decode($data[0]['detail'], true);
        //传输模式,VMware使用
        $info['hypervisor'] = intval($data[0]['hypervisor_type']);
        $info['mode'] = $this->getTransportModeDes($data[0]['hypervisor_type'], $data[0]['transport_priority']);
        $info['mode_index'] = $data[0]['transport_priority'];
        $info['transfer_compress'] = $detailArr['source_compression'] ?? '';
        $info['async_transfer'] = isset($detailArr['async_rw_flag']) && v2_parse_flag_to_bool($detailArr['async_rw_flag']);
        // 传输代理实例类型，公有云使用
        $info['agent_type'] = '';
        if (in_array(intval($data[0]['hypervisor_type']), xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])) {
            $info['agent_type'] = $data[0]['agent_uuid'];
        }
        // 并行传输
        $info['parallel_transfer_vm_count'] = $data[0]['parallel_transfer_vm_count'];
        $info['single_vm_parallel_disk_transfer_count'] = $data[0]['single_vm_parallel_disk_transfer_count'];
        $info['vm_single_disk_parallel_transfer_count'] = $data[0]['vm_single_disk_parallel_transfer_count'];
        //加密传输,XenServer使用
        $sql = "select encrypt_flag,reconnect_times,reconnect_interval,encrypt_method from bd_transport_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v2_parse_flag_to_bool($data[0]['encrypt_flag']);
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];
        //获取传输网络描述
        $name = '';
        if (!empty($data[0]['ip'])) {
            $name = $data[0]['ip'] . ':' . $data[0]['port'];
            if (!empty($data[0]['alias_name'])) {
                $name .= '(' . $data[0]['alias_name'] . ')';
            }
        }
        $info['network'] = $name;
        return $info;
    }

    /**
     * 得到当前任务节点和存储信息
     * @param string $nodeuuid
     * @param string $storageuuid
     */
    protected function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid)
    {
        $info = array('flag' => false);
        if (
            xphp_get_config('task')['TASKTYPE']['BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['RECOVERY'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['OS_BACKUP'] != $tasktype
        ) {
            return $info;
        }
        //恢复任务显示节点信息
        if (
            xphp_get_config('task')['TASKTYPE']['RECOVERY'] == $tasktype
            || xphp_get_config('task')['TASKTYPE']['OS_RECOVERY'] == $tasktype
            || xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] == $tasktype
        ) {
            $info['flag'] = true;
        }
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size from bd_storage_resource 
            where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        if ($data) {
            $info['flag'] = true;
            $totalSize = v2_calsize($data[0]['total_size'], true);
            $freeSize = v2_calsize($data[0]['free_size'], true);
            $quotaDes = '';
            $quotaInfo = User::instance()->getUserQuotaInfo();
            $quotaFlag = false;     //分配配额标记
            //设置了配额或者是在租户内部
            if ($quotaInfo['quota'] != -1 || !empty($_SESSION['tenantuuid'])) {
                $quotaDes = $quotaInfo['des'];
                $quotaFlag = true;
            }
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => Storage::instance()->getStorageTypeDes($data[0]['storage_type']),
                'typenum' => $data[0]['storage_type'],
                'size' => $totalSize,
                'freesize' => $freeSize,
                'quotades' => $quotaDes,
                'tenantuuid' => $_SESSION['tenantuuid'],
                'quotaFlag' => $quotaFlag
            );
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag, encrypted_flag, password_auto_flag, compress_method, encrypt_method, 
                redundant_data_proportiont, data_container_size 
            from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if ($data) {
            $info['high'] = array(
                'deduplication' => v2_parse_flag_to_bool($data[0]['deduplication_flag']),
                'blocksize' => intval($data[0]['block_size']) / 1024 . 'KB',
                'compressed' => v2_parse_flag_to_bool($data[0]['compressed_flag']),
                'compress_method' => intval($data[0]['compress_method']),
                'encrypt_flag' => v2_parse_flag_to_bool($data[0]['encrypted_flag']),
                'password_auto_flag' => v2_parse_flag_to_bool($data[0]['password_auto_flag']),
                'encrypt_method' => intval($data[0]['encrypt_method']),
                'redundant_data_proportion' => intval($data[0]['redundant_data_proportiont']) . '%',
                'data_container_size' => intval($data[0]['data_container_size']) / 1073741824 . 'GB'
            );
        }
        return $info;
    }

    /**
     * 得到虚拟机备份模式配置信息
     * 静默快照,高速模式
     * @param string $taskuuid
     * @return array
     */
    protected function getVMBackupModeStrategy($taskuuid)
    {
        $sql = "select pre_create_snap_flag, level, quiesce_snapshot, hypervisor_type, 
                valid_data_backup, serial_snapshot_flag, parse_fs_flag, 
                not_backup_swap_file_flag, not_backup_deleted_file_flag, not_backup_partition_gap_flag, detail  
                 from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $level = $data[0]['level'];
        if (xphp_get_config('app')['FLAG']['SET'] == $data[0]['valid_data_backup']) {
            // 开启了CBT则增量模式设为CBT
            $level = 3;
        }
        $hypervisor = intval($data[0]['hypervisor_type']);
        $incMode = $this->getIncModeDes($hypervisor, intval($level));

        $detail = $data[0]['detail'];
        $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
        $snapshotTimeout = $detailArr['snapshot_timeout'] ?? 3600;
        $highspeedDiskCbt = $detailArr['highspeed_disk_cbt_flag'] ?? 2;
        $keepSnapshot = $detailArr['keep_snapshot_flag'] ?? 2; // 保留快照
        $snapshotDelSpeed = $detailArr['snapshot_del_speed'] ?? 0; // 快照删除速度
        // 重置CBT级别
        $resetCbtLevel = xphp_get_config('vm', 'RESET_CBT_LEVEL')['NONE'];
        if (xphp_get_config('app')['FLAG']['SET'] == $detailArr['error_reset_cbt_flag']) {
            $resetCbtLevel = xphp_get_config('vm', 'RESET_CBT_LEVEL')['ERROR'];
        }
        if (xphp_get_config('app')['FLAG']['SET'] == $detailArr['full_backup_reset_cbt_flag']) {
            $resetCbtLevel = xphp_get_config('vm', 'RESET_CBT_LEVEL')['FULL_BACKUP'];
        }
        return array(
            'hypervisor' => $hypervisor,
            'serialSnapshot' => intval($data[0]['serial_snapshot_flag']),
            'highLevel' => $this->getHighLevel($data[0]['level']),
            'cbtMode' => v2_parse_flag_to_bool($data[0]['valid_data_backup']),
            'resetCbt' => $this->getResetCbtLevelDes($resetCbtLevel),
            'incMode' => $incMode,
            'incModeValue' => intval($level),
            'quiesceSnapshot' => v2_parse_flag_to_bool($data[0]['quiesce_snapshot']),
            'parseFs' => v2_parse_flag_to_bool($data[0]['parse_fs_flag']),
            'noSwapFile' => v2_parse_flag_to_bool($data[0]['not_backup_swap_file_flag']),
            'noDeletedFile' => v2_parse_flag_to_bool($data[0]['not_backup_deleted_file_flag']),
            'noPartitionGap' => v2_parse_flag_to_bool($data[0]['not_backup_partition_gap_flag']),
            'presnapshot' => v2_parse_flag_to_bool($data[0]['pre_create_snap_flag']),
            'snapshotTimeout' => intval($snapshotTimeout),
            'highspeedDiskCbt' => v2_parse_flag_to_bool($highspeedDiskCbt),
            'keepSnapshot' => v2_parse_flag_to_bool($keepSnapshot),
            'snapshotDelSpeed' => intval($snapshotDelSpeed)
        );
    }

    /**
     * 获取限速策略配置信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getSpeedlimitDes(string $taskuuid): array
    {
        $sql = "select bgsls.extra_info from bd_global_speed_limit_strategy bgsls 
            join bd_task_speed_limit_strategy btsls on bgsls.strategy_uuid = btsls.strategy_uuid
            where btsls.task_uuid = ? limit 1";
        $data = $this->dbSelect($sql, array($taskuuid));
        $value = '';
        $des = '';
        if (!empty($data)) {
            $extraInfo = json_decode($data[0]['extra_info'], true);
            foreach ($extraInfo as $item) {
                $value .= $item['des'] . '<br>';
                $des .= $item['des'] . "\n";
            }
        } else {
            $value .= xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
            $des .= xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        }
        return array(
            'value' => $value,
            'des' => $des
        );
    }

    /**
     * 获取自动加入备份筛选条件
     * @param $taskuuid
     * @return mixed
     */
    protected function getVMSelectConditions($taskuuid)
    {
        $sql = "select select_conditions from vm_task where task_uuid = ?";
        $rules = $this->dbSelect($sql, [$taskuuid])[0]['select_conditions'];
        $rules = $rules ?? 'NULL';
        $rulesArr = json_decode($rules, true);
        // 604及之前的格式转605的
        if (!isset($rulesArr['select_type'])) {
            return [
                'select_type' => $rulesArr[0]['select_type'] ?: '3', // 没有值设为按关键词匹配
                'select_value' => [$rulesArr[0]['select_value'] ?? '']
            ];
        }
        return $rulesArr;
    }

    /**
     * 获取任务详情
     * @param $taskuuid
     * @return mixed
     */
    protected function getVMTaskDetail($taskuuid)
    {
        $sql = "select detail from vm_task where task_uuid = ?";
        $rules = $this->dbSelect($sql, [$taskuuid])[0]['detail'];
        return json_decode($rules, true);
    }

    /**
     * 得到天的数组
     * @param unknown $days
     */
    private function getDaysArr($days)
    {
        $count = strlen($days);
        $daysArr = array();
        for ($i = 0; $i < $count; $i++) {
            if ('s' == substr($days, $i, 1)) {
                break;
            }
            $daysArr[] = intval(substr($days, $i, 1));
        }
        return $daysArr;
    }

    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int   $StrategyType
     * @param array $days
     * @return string  空字符串  s1 - s4
     */
    private function getFrequency($strategyType, $days)
    {
        $frequency = '';
        if (xphp_get_config('task')['STRATEGY_TYPE']['EVERY_WEEK'] != intval($strategyType)) {
            return $frequency;
        }
        $strIndex = strpos($days, 's');
        if ($strIndex) {
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }

    /**
     * 得到传输模式的描述 跨平台有调用
     * @param int    $hypervisor
     * @param string $transportPriority
     */
    public function getTransportModeDes($hypervisor, $transportPriority)
    {
        $vmDes = include APP_PATH . 'v2/description/Vm.php';
        if (
            in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['vmware'])
            || in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['huawei'])
        ) {
            if (is_numeric($transportPriority)) {
                $map = ['file', 'nbd', 'nbdssl', 'san', 'hotadd'];
                $mode = $map[$transportPriority];
            } else {
                $modeArr = explode(':', $transportPriority);
                $mode = $modeArr[1];
            }
            return xphp_get_lang($vmDes['VmTransportMode'][$mode]);
        }
        $transportPriority = intval($transportPriority);
        if (in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])) {
            return xphp_get_lang($vmDes['VmTransportModeOpenstack'][$transportPriority]);
        }
        //如果是华为KVM
        if (
            $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XFUSION_KVM']
        ) {
            return xphp_get_lang($vmDes['VmTransportModeHuaWeiKVM'][$transportPriority]);
        }

        //如果是oVirt系
        if (
            $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HOSTVM']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RED_VIRT']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ROSA_VIRT']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OVIRT_KVM']
        ) {
            return xphp_get_lang($vmDes['VmTransportModeRedHat'][$transportPriority]);
        }

        // 部分KVM
        if (
            $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM']
            || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VOLC']
        ) {
            return xphp_get_lang($vmDes['VmTransportModeKvm'][$transportPriority]);
        }

        return xphp_get_lang($vmDes['VmTransportModeXenServer'][$transportPriority]);
    }

    /**
     * 获取任务appliance信息
     * @param string $taskUuid
     */
    public function getJobAppliance(string $taskUuid)
    {
        $sql = "select ba.ip, ba.agent_name from bd_agent ba, vm_task vt 
            where vt.agent_uuid = ba.agent_uuid and vt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (!empty($data)) {
            $des = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        } else {
            $des = '--';
        }

        return $des;
    }

    /**
     * 获取节点名称和ip
     * @param $nodeuuid
     * @return array
     */
    private function getNodeNameAndIp($nodeuuid)
    {
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $re = array(
            'name' => xphp_get_config('app')['NULLSPACE'],
            'ip' => '',
        );
        if ($data) {
            $re = array(
                'name' => Node::instance()
                    ->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
                'ip' => $data[0]['ip'],
            );
        }
        return $re;
    }

    /**
     * 得到增量模式描述
     * @param int $hypervisorType
     * @param int $level
     */
    private function getIncModeDes($hypervisorType, $level)
    {
        if (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisorType && 1 == $level) {
            return xphp_get_lang('UI_PUBLIC_OFF_TWO');
        }
        $vmDes = include APP_PATH . 'v2/description/Vm.php';
        return xphp_get_lang($vmDes['IncModeDes'][$level]);
    }

    /**
     * 得到重置CBT级别描述
     * @param int $level
     * @return string
     */
    private function getResetCbtLevelDes(int $level): string
    {
        $vmDes = include APP_PATH . 'v2/description/Vm.php';
        return xphp_get_lang($vmDes['RESET_CBT_LEVEL'][$level]);
    }

    /**
     * 得到是否高速模式
     * @param $level
     * @return bool
     */
    private function getHighLevel($level)
    {
        return $level == 2;
    }

    /**
     * 获取数据验证任务高级策略
     * @param string $taskuuid
     * @return array
     */
    private function getVerifyModeStrategy(string $taskuuid)
    {
        $sql = "select ssb.ping_warn_flag, ssb.heartbeat_warn_flag, ssb.screenshot_warn_flag, ssbiv.nfs_server_ip, ssb.virtual_lab_uuid, ssb.hypervisor_type, ssb.limit_boot_vm_num, ssb.automatic_verifitied_flag
                 from sr_sure_backup ssb, sr_sure_backup_instant_vm ssbiv where ssbiv.task_uuid = ssb.task_uuid and ssb.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $virtual_lab_uuid = $data[0]['virtual_lab_uuid'];
        //获取虚拟实验室信息
        $sqlLab = "select network_map, virtual_lab_name, proxy_name, proxy_ip from sr_virtual_lab where virtual_lab_uuid = ?";
        $dataLab = $this->dbSelect($sqlLab, array($virtual_lab_uuid));
        $labName = $dataLab[0]['virtual_lab_name'];
        $proxyName = $dataLab[0]['proxy_name'] . "(" . $dataLab[0]['proxy_ip'] . ")";
        $networkMap = json_decode($dataLab[0]['network_map'], true);
        $networkList = array();
        foreach ($networkMap['map_list'] as $net) {
            //生产网络
            $productInfo = array(
                'name' => $net['product_network_network_name'],
                'netmask' => $net['product_network_netmask'],
                'gateway' => $net['product_network_gateway']
            );
            //隔离网络
            $isolatedInfo = array(
                'name' => $net['isolate_network_network_name'],
                'netmask' => $net['isolate_network_netmask'],
                'gateway' => $net['isolate_network_gateway']
            );

            $networkList[] = array(
                'productInfo' => $productInfo,
                'isolatedInfo' => $isolatedInfo
            );
        }

        return array(
            'hypervisor' => $data[0]['hypervisor_type'],
            'limit_boot_vm_num' => intval($data[0]['limit_boot_vm_num']),
            'backup_system_ip' => $data[0]['nfs_server_ip'],
            'verify_type_des' => intval($data[0]['automatic_verifitied_flag']) == 1
                ? xphp_get_config('UI_VERIFY_MANUAL') : xphp_get_config('UI_VERIFY_AUTOMATIC'),
            'lab_name' => $labName,
            'proxy_name' => $proxyName,
            'network_list' => $networkList,
            'verify_type' => intval($data[0]['automatic_verifitied_flag']),
            'ping_warn_flag' => v2_parse_flag_to_bool(intval($data[0]['ping_warn_flag'])),
            'heartbeat_warn_flag' => v2_parse_flag_to_bool(intval($data[0]['heartbeat_warn_flag'])),
            'screenshot_warn_flag' => v2_parse_flag_to_bool(intval($data[0]['screenshot_warn_flag'])),

        );
    }

    /**
     * 获取openstack租户名
     * @param string $vcenteruuid
     * @return string
     */
    private function getOpenstackTenant(string $vcenteruuid): string
    {
        $sql = "select detail from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $detail = json_decode($data[0]['detail'], true);
        return $detail['tenant_name'];
    }

    /**
     * 根据任务状态得到数据迁移的状态码
     * 0无状态
     * 1错误
     * 2传输
     * @param mixed $taskStatus
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
     * 检查细粒度任务状态,只在运行状态的时候可以继续,如果不是运行状态,直接返回没有数据的参数恢复
     * @param string $taskuuid
     * @return bool
     */
    private function checkGrainRecoveryTaskStatus(string $taskuuid): bool
    {
        $sql = "select task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskStatus = intval($data[0]['task_status']);
        if (xphp_get_config('task', 'TASKSTATUS')['RUNNING'] != $taskStatus) {
            $list = array(
                "re" => true,                               //成功标志,方面前端统一处理
                "path" => "",           //当前路径
                'taskuuid' => $taskuuid,
                "tmpFilePath" => false,
                "nextStart" => 0,
                "filelist" => array()
            );
            exit(json_encode($list));
        }
        return true;
    }

    /**
     * 首次获取细粒度恢复文件列表,第一次是从数据库中获取,获取根目录
     * @param string $taskuuid
     * @param string $sclass
     * @param int $showtype
     * @param string $searchName
     * @return array
     */
    private function getVmGrainRecoveryFileDirFirst(string $taskuuid, string $sclass, int $showtype, string $searchName)
    {
        $sql = "select task_status, os_type, root_list from vm_grain_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $show_root_list = json_decode($data[0]['root_list'], true);
        $rootList = $this->getVMGrainFileWithShowType($show_root_list['guest_struct'], $showtype);

        $list = array(
            "path" => "",           //当前路径
            'job_uuid' => $taskuuid,
            "tmpFilePath" => '',
            "isFinished" => true,
            "nextStart" => 0,
        );
        $fileList = array();
        foreach ($rootList as $d) {
            //按名称模糊搜索
            if (!empty($searchName)) {
                if (!in_array($searchName, $d))
                    continue;
            }
            $fileList[] = $this->getVMGrainFileOneWithShowType($d, $showtype, $sclass, "");
        }
        $list['filelist'] = $fileList;

        return $list;
    }

    /**
     * 根据展示方式解析数据类型
     * @param array $guest_struct 数据类型
     * @param int $showtype 展示方式
     * @return array
     */
    private function getVMGrainFileWithShowType(array $guest_struct, int $showtype): array
    {
        $showlist = array();
        $grainDisplayMode = xphp_get_config('vm', 'GUEST_DISPLAY_MODE');
        foreach ($guest_struct as $list) {
            if ($list['display_mode'] == $showtype) {
                switch ($showtype) {
                    case $grainDisplayMode['SYSTEM']:
                        $showlist = $list['root_list'];
                        break;
                    case $grainDisplayMode['NORMAL_DEVICE']:
                    case $grainDisplayMode['LOGICAL_DEVICE']:
                        $showlist = $list['dev_list'];
                        break;
                }
            }
        }
        return $showlist;
    }

    /**
     * 根据展示方式解析数据类型
     * @param array $d 单个数据
     * @param int $showtype 展示方式
     * @param string $sclass 图标大小   s/m/l 24/32/48px
     * @param string $pathHead 上级目录路径
     * @return array
     */
    private function getVMGrainFileOneWithShowType(array $d, int $showtype, string $sclass, string $pathHead): array
    {
        $showInfo = array();
        $grainDisplayMode = xphp_get_config('vm', 'GUEST_DISPLAY_MODE');
        switch ($showtype) {
            case $grainDisplayMode['SYSTEM']:
                $filename = $d['name'];
                $filesize = v2_calsize($d['size'], true);
                $showInfo = array(
                    'file_name' => $filename,                  //文件名
                    'file_size' => $filesize,                  //文件大小
                    'modify_time' => $d['modify_time'],          //修改时间
                    'details' => array(
                        "path" => $pathHead . $d['name'],
                        //                         "path" => '/',                                                        //路径
                        "type" => $this->getGrainFileType($d['type']),                    //文件类型
                        "isfile" => false,   //是否是文件
                        "sclass" => $this->getGrainFileClassName($d['type'], $sclass),    //显示类型
                        "createTime" => $d['create_time'],
                        "modifyTime" => $d['modify_time'],
                        "size" => "",
                        "device" => "",     //为了统一,此模式下设备为空
                        "btype" => $d['type'],
                    ),
                );
                break;
            case $grainDisplayMode['NORMAL_DEVICE']:
            case $grainDisplayMode['LOGICAL_DEVICE']:
                $showInfo = array(
                    'file_name' => $d['device_path'],                  //文件名
                    'file_size' => "--",                  //文件大小
                    'fs_type' => $d['fs_type_str'],          //文件系统类型
                    'details' => array(
                        //                         "path" => $pathHead . $d['device_path'],
                        "path" => "/",                                                 //路径
                        "type" => $this->getGrainFileType($d['type']),                    //文件类型
                        "isfile" => false,   //是否是文件
                        "sclass" => "filetype-dir-s",    //显示类型
                        "createTime" => $d['create_time'],
                        "modifyTime" => $d['modify_time'],
                        "size" => "",
                        "fs_type" => $d['fs_type'],
                        "fs_type_str" => $d['fs_type_str'],
                        "device" => $d['device_path'],
                        "btype" => $d['type'],
                    ),
                );
                break;
        }

        return $showInfo;
    }

    /**
     * 得到细粒度文件类型
     * @param int $filetype
     * @return string
     */
    private function getGrainFileType(int $filetype): string
    {
        $filetypeConf = xphp_get_config('vm', 'GUEST_FILE_ITEM_TYPE');
        switch ($filetype) {
            case $filetypeConf['FILE']:
                //TODO加入文件类型
                $fileClass = "file";
                break;
            case $filetypeConf['DIR']:
                $fileClass = "dir";
                break;
            case $filetypeConf['SLINK']:
                $fileClass = "slink";
                break;
            case $filetypeConf['SLINK_TARGET_UNREACHABLE']:
                $fileClass = "slink_target_unreacheable";
                break;
            default:
                $fileClass = "unknown";
                break;
        }
        return $fileClass;
    }

    /**
     * 根据文件类型得到显示的Class
     * @param int $filetype 文件类型
     * @param string $size 图标大小   s/m/l 24/32/48px
     * @return string
     */
    private function getGrainFileClassName(int $filetype, string $size): string
    {
        $class = "filetype-unknown-" . $size;
        $grainFileType = xphp_get_config('vm', 'GUEST_FILE_ITEM_TYPE');
        if ($filetype == $grainFileType['DIR']) {
            return "filetype-dir-" . $size;
        } elseif (
            $filetype == $grainFileType['SLINK'] ||
            $filetype == $grainFileType['SLINK_TARGET_UNREACHABLE']
        ) {
            return "filetype-link-" . $size;
        }
        return $class;
    }

    /**
     * 获取合并模式描述
     * @param int $mergeMode
     * @return string
     */
    protected function getMergeModeInfo(int $mergeMode): string
    {
        if (1 == $mergeMode) {
            return xphp_get_lang('UI_BACKUP_HIGH_PERFORMANCE');
        } elseif (2 == $mergeMode) {
            return xphp_get_lang('UI_BACKUP_LOW_REDUNDANCY');
        } else {
            return '--';
        }
    }
}
