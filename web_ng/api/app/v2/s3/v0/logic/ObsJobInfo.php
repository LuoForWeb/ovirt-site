<?php

namespace app\v2\s3\v0\logic;

use app\v2\common\logic\JobInfo;
use app\v2\resources\v0\logic\Node;
use app\v2\opcode\PfOpcode;
use app\v2\tape\v0\logic\TapeInfo;
use app\v2\exchange\v0\logic\ExchangeJobInfo;
use app\v2\opcode\NodeOpcode;
use app\v2\user\v0\logic\User;

/**
 * note          对象存储保护 - 任务详情logic
 * @auther       chengjiafu@vinchin.com
 * @date         2023/11/27 15:56
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsJobInfo extends JobInfo
{
    /**
     * 任务详情: 得到Exchange模块任务基本信息
     * @param unknown $params 参数
     * @return array 任务基本信息
     */
    public function getBasicInfo($params = [])
    {
        $taskUUID = $params['task_uuid'];
        $info = (new \app\v2\job\v0\logic\JobInfo())->getJobInfo($taskUUID);

        $sql = "SELECT distinct 
                    bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.ignore_resource_limiting_flag, 
                    bt.thread_num, bt.task_orchestration_plan_flag, bt.sub_module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
                	bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid,
                    bu.user_name,bu.user_uuid, unix_timestamp(bs.next_start_time) next_start_time, bri.total_object_size, bri.total_object_completed_size, bri.speed, 
                    unix_timestamp(bri.start_time) start_time, ft.detail as ftdetail, ft.skip_file_alarm_flag, ft.skip_file_alarm_min_num, ft.skip_file_alarm_min_ratio, 
                    ft.permission_operate_flag, ft.same_file_strategy, ft.link_file_pass_flag, ft.dir_tree_recovery_flag, ft.proxy_uuid, fpl.new_root_path, bs.time_strategy_backup_type, 
                    bt.worm_flag, bt.integrity_check_flag, btsc.worm_protection_time, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy, btsc.recovery_integrity_check_error_policy   
                FROM 
                    bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs ,fs_task ft, fs_path_list fpl, bd_task_safe_config btsc    
                WHERE 
                    bt.user_uuid = bu.user_uuid AND 
                    bt.task_uuid = bri.task_uuid AND 
                    bt.task_uuid = fpl.task_uuid AND
                    bt.strategy_id = bs.strategy_id AND 
                    bt.task_uuid = ft.task_uuid AND 
                    bt.task_uuid = btsc.task_uuid AND 
                	bt.task_uuid = ?";

        $data = $this->dbSelect($sql, array($taskUUID));

        $sql2 = "select current_object_total_size, current_object_completed_size from fs_running_info where task_uuid = ?";
        $progressData = $this->dbSelect($sql2, array($taskUUID));

        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';

        if (!$data) {
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }

        $srcModuleInfo = $this->getNasResorceInfo($taskUUID);

        foreach ($data as $d) {
            $ftdetail = json_decode($d['ftdetail'], true);
            $applianceAgencyFlag = $d['proxy_uuid'] ? true : false;
            $applianceAgency = $applianceAgencyFlag ? $this->getApplianceAgency($d['proxy_uuid']) : '';

            $basicInfo = array(
                'job_name' => $info['job_name'],
                'job_type' => $info['job_type'],
                'user' => $d['user_name'],
                'user_uuid' => $d['user_uuid'],
                'sub_module_type' => $d['sub_module_type'],
                'sub_module_type_des' => xphp_get_config('module', 'SUBMODULE_TYPE_DES')[$d['sub_module_type']],
                'job_status' => $info['job_status'],
                'job_status_des' => xphp_get_lang($info['job_status_des']),
                'total_size' => $info['total_size'],
                'complate_size' => $info['complate_size'],
                'createTime' => $info['create_time'],
                'startTime' => $info['start_time'],
                'intervalTime' => $info['interval_time'],
                'job_stage' => $info['job_stage'],
                'speed' => $info['speed'],
                'speed_time' => $info['speed_time'],
                'threadNum' => $info['thread_num'],
                'timestamp' => $info['timestamp'],
                'progress' => $info['progress'],
                'nextTime' => $info['next_time'],
                'storageInfo' => $info['storage_info'], // 存储信息
                'storageType' => $info['job_type'] == 2 ? $this->getStorageType($taskUUID) : '', // 恢复任务获取存储类型 
                'speedLimit' => $info['speed_limit'], // 限速策略
                'timeStrategy' => $info['time_strategy'], // 时间策略
                'timeStrategyBackupType' => $d['time_strategy_backup_type'], // 时间策略备份类型
                'reserveStrategy' => $info['reserve_strategy'], // 保留策略
                'transportStrategy' => $info['transport_strategy'], // 传输策略
                'module_type' => $info['module_type'],
                'module_type_des' => $info['module_type_des'],
                'job_type_des' => $info['job_type_des'],
                'detail' => $ftdetail,
                'skip_file_alarm_flag' => $d['skip_file_alarm_flag'] == 1 ? true : false,
                'skip_file_alarm_min_num' => $d['skip_file_alarm_min_num'],
                'skip_file_alarm_min_ratio' => $d['skip_file_alarm_min_ratio'] . '%',
                'permissionOperateFlag' => $d['permission_operate_flag'] == 1 ? true : false,
                'sameFileStrategy' => $d['same_file_strategy'],
                'linkFilePassFlag' => $d['link_file_pass_flag'] == 1 ? true : false,
                'dirTreeRecoveryFlag' => $d['dir_tree_recovery_flag'] == 1 ? true : false,
                'newRootPath' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),
                'wildcardMode' => $this->getWildCardInfo($d['task_uuid']),
                'networkFlag' => $this->getNodeNetworkFlag($taskUUID),//检查显示传输网络标志
                'scanThreadNum' => intval($ftdetail['scan_thread_num']),
                'scan_file_num' => intval($ftdetail['scan_file_num']),
                'appliance_agency_flag' => $applianceAgencyFlag,
                'appliance_agency' => $applianceAgency,
                'src_module_type' => $srcModuleInfo['source_agent_type'],
                'src_sub_module_type' => $srcModuleInfo['src_type'],
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($info['task_orchestration_plan_flag']),
                'tape_strategy' => TapeInfo::instance()->getTapeGroupStrategy(array("group_uuid" => $info['storage_uuid'])) ?? '',
                'retry_strategy' => (new ExchangeJobInfo())->getRetryStrategy($taskUUID), // 重试策略
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($d['ignore_resource_limiting_flag']),
                'safe_config_strategy' => array(
                    'worm_flag' => $d['worm_flag'],
                    'worm_protection_time' => $d['worm_protection_time'],
                    'integrity_check_flag' => $d['integrity_check_flag'],
                    'integrity_check_config' => array(
                        'check_strategy' => $d['integrity_check_strategy'],
                        'full_error_policy' => $d['backup_integrity_check_full_error_policy'],
                        'recovery_error_policy' => $d['recovery_integrity_check_error_policy']
                    )
                ),
                'current_stage_value' => $info['current_stage_value'], // 任务阶段+任务百分比
            );
        }

        return $basicInfo;
    }

    /**
     * 得到任务流量
     * @param unknown $params 参数
     * @return 流量
     */
    public function getTaskSpeed($params)
    {
        $taskUUID = $params['taskUUID'];
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

        $data = array(
            'speed' => $speed,
            'test' => $data[0]['speed_time'],
            't' => time(),
            'nowTime' => date('H:i:s')
        );

        return $data;
    }

    /**
     * 获取对象存储列表
     * @param $params
     * @return array
     */
    public function getTaskDetail($params): array
    {
        $taskUUID = $params['jobs_uuid'];
        $taskType = intval($params['task_type']);
        $agentStatusDes = xphp_get_desc('Pf', 'AGENT_TASK_STATUS');

        $info = array(
            'rows' => array(),
            'total' => 1,
        );

        if ($taskType === 1) { // 备份任务 
            $search = $params['search'];

            $sql = "SELECT DISTINCT
                        bt.task_name, bt.task_type, bt.task_status AS fs_task_status, bt.module_type,
                        fpl.backup_mode, fpl.new_root_path, fpl.path_name,
                        btal.task_status, btal.task_uuid, btal.detail, btal.agent_uuid
                    FROM bd_task bt 
                        LEFT JOIN fs_task ft ON bt.task_uuid = ft.task_uuid 
                        LEFT JOIN fs_path_list fpl ON bt.task_uuid = fpl.task_uuid 
                        LEFT JOIN bd_task_agent_list btal ON fpl.task_uuid = btal.task_uuid 
                    WHERE 
                        bt.task_uuid = ? GROUP BY btal.agent_uuid ORDER BY btal.id";
            $data = $this->dbSelect($sql, array($taskUUID));

            if (!empty($data)) {
                $pfdesBmd = xphp_get_desc('Pf', 'BACKUP_MODE_DES');

                foreach ($data as $d) {
                    $res = $this->getObsClientIp($d['agent_uuid']);
                    $obsName = $res['obs_name']; // 对象存储名
                    $vendor = $res['vendor']; // 所属vendor

                    $sql_path = "select path_name from fs_path_list where agent_uuid = ? and task_uuid = ?";
                    $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.agent_uuid = ? and fri.task_uuid = ?";
                    $sqlParams_path = array($d['agent_uuid'], $d['task_uuid']);

                    $data_path = $this->dbSelect($sql_path, $sqlParams_path); // 文件路径
                    $data_count = $this->dbSelect($sql_count, $sqlParams_path); // 文件数量

                    $pathList = array();

                    foreach ($data_path as $each_path) {
                        $pathList[] = $each_path['path_name'];
                    }

                    if ($d['fs_task_status'] !== xphp_get_config('task', 'TASKSTATUS')['RUNNING']) { // 任务非运行状态
                        $info['rows'][] = array(
                            'obs_uuid' => $d['agent_uuid'],
                            'source_client' => $obsName,
                            'vendor' => $vendor,
                            'task_type' => '--',
                            'total_fs_count' => xphp_get_config('app', 'NULLSPACE'),
                            'current_fs_count' => xphp_get_config('app', 'NULLSPACE'),
                            'current_dir_count' => xphp_get_config('app', 'NULLSPACE'),
                            'agent_status' => xphp_get_config('app', 'NULLSPACE'),
                            'progress' => xphp_get_config('app', 'NULLSPACE'),
                            'wildcardInfo' => json_decode($d['detail'], true),
                            'path_name' => $pathList
                        );
                    } else {
                        $info['rows'][] = array(
                            'obs_uuid' => $d['agent_uuid'],
                            'source_client' => $obsName,
                            'vendor' => $vendor,
                            'task_type' => $pfdesBmd[$d['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DES_BACKUP'),
                            'total_fs_count' => $data_count[0]['total_fs_count'],
                            'current_fs_count' => $data_count[0]['current_fs_count'],
                            'current_dir_count' => $data_count[0]['current_dir_count'],
                            'agent_status' => xphp_get_lang($agentStatusDes[$d['task_status']]),
                            'progress' => $this->getObsAgentProgress($taskUUID, $d['agent_uuid'], intval($d['fs_task_status']), intval($d['task_type'])),
                            'wildcardInfo' => json_decode($d['detail'], true),
                            'path_name' => $pathList
                        );
                    }
                }

                // 前端传了搜索参数时
                if (!empty($search)) {
                    $filteredRows = array_filter($info['rows'], function ($row) use ($search) {
                        return stripos($row['source_client'], $search) !== false;
                    });

                    $info['rows'] = array_values($filteredRows);
                }

                $info['total'] = count($info['rows']);
            }
        } else { // 恢复任务
            $sql = "select distinct ft.submodule_type,
                        bt.task_name, bt.task_type, bt.task_status as fs_task_status, bt.module_type,   
                        fpl.recovery_timepoint_uuid as sour_timepointuuid, fpl.backup_mode, fpl.new_root_path, fpl.path_name,  
                        fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail, fbt.agent_uuid as uuid,
                        btal.task_status, btal.task_uuid, btal.detail, btal.agent_uuid as target_agent_uuid, bbt.sub_module_type AS source_submodule_type
                    from bd_task bt 
                        LEFT JOIN fs_task ft on bt.task_uuid = ft.task_uuid 
                        LEFT JOIN fs_path_list fpl on bt.task_uuid = fpl.task_uuid 
                        LEFT JOIN bd_task_agent_list btal on fpl.task_uuid = btal.task_uuid 
                        LEFT JOIN fs_backup_timepoint fbt on fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid 
                        LEFT JOIN bd_backup_timepoint bbt ON fpl.recovery_timepoint_uuid = bbt.timepoint_uuid
                    where bt.task_uuid = ? group by uuid order by btal.id";

            $data = $this->dbSelect($sql, array($taskUUID));

            if (!empty($data)) {
                foreach ($data as $d) {
                    $sql_path = "select path_name, new_root_path from fs_path_list where task_uuid = ?";
                    $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.task_uuid = ?";
                    $sqlParams_path = array($d['task_uuid']);

                    $data_path = $this->dbSelect($sql_path, $sqlParams_path); // 文件路径
                    $data_count = $this->dbSelect($sql_count, $sqlParams_path); // 文件数量

                    $pathList = array();

                    foreach ($data_path as $each_path) {
                        $pathList[] = $each_path['path_name'];
                    }

                    switch ($d['source_submodule_type']) {
                        case 1: // 源端为文件客户端
                            $sourceClient = $d['agent_name'] . '(' . $d['agent_ip'] . ')';
                            break;
                        case 2: // 源端为NAS设备
                            $sourceClient = $d['agent_name'] . '(' . $d['agent_ip'] . ')';
                            break;
                        case 3: // 源端为Hadoop集群
                            $sourceClient = $d['agent_name'] . '(' . $d['agent_ip'] . ')';
                            break;
                        case 4: // 源端为对象存储
                            $sourceClient = $d['agent_name'];
                            break;
                        default:
                            break;
                    }

                    $targetRes = $this->getObsClientIp($d['target_agent_uuid']);
                    $targetClient = $targetRes['obs_name']; // 目的客户端
                    $targetVendor = $targetRes['vendor']; // 目的对象存储所属vendor

                    if ($d['fs_task_status'] !== xphp_get_config('task', 'TASKSTATUS')['RUNNING']) { // 任务非运行状态
                        $info['rows'][] = array(
                            'obs_uuid' => $d['uuid'],
                            'obs_name' => $targetClient,
                            'task_type' => xphp_get_lang('WEB_PLATFORM_DES_RECOVERY'),
                            'total_fs_count' => xphp_get_config('app', 'NULLSPACE'),
                            'current_fs_count' => xphp_get_config('app', 'NULLSPACE'),
                            'current_dir_count' => xphp_get_config('app', 'NULLSPACE'),
                            'agent_status' => xphp_get_config('app', 'NULLSPACE'),
                            'progress' => xphp_get_config('app', 'NULLSPACE'),
                            'wildcardInfo' => json_decode($d['detail'], true),
                            'path_name' => $pathList,
                            'new_root_path' => $data_path[0]['new_root_path'],
                            'source_client' => $sourceClient,
                            'target_client' => $targetClient,
                            'target_vendor' => $targetVendor,
                            'submodule_type' => $d['submodule_type'],
                            'source_submodule_type' => $d['source_submodule_type'],
                            'cross_platform_flag' => $d['submodule_type'] !== $d['source_submodule_type'] ? true : false,
                            'cross_platform_des' => $this->getCrossPlatformDes($d['source_submodule_type'])
                        );
                    } else {
                        $info['rows'][] = array(
                            'obs_uuid' => $d['uuid'],
                            'obs_name' => $targetClient,
                            'task_type' => xphp_get_lang('WEB_PLATFORM_DES_RECOVERY'),
                            'total_fs_count' => $data_count[0]['total_fs_count'],
                            'current_fs_count' => $data_count[0]['current_fs_count'],
                            'current_dir_count' => $data_count[0]['current_dir_count'],
                            'agent_status' => xphp_get_lang($agentStatusDes[$d['task_status']]),
                            'progress' => $this->getObsAgentProgress($taskUUID, $d['target_agent_uuid'], intval($d['fs_task_status']), intval($d['task_type'])),
                            'wildcardInfo' => json_decode($d['detail'], true),
                            'path_name' => $pathList,
                            'new_root_path' => $data_path[0]['new_root_path'],
                            'source_client' => $sourceClient,
                            'target_client' => $targetClient,
                            'target_vendor' => $targetVendor,
                            'submodule_type' => $d['submodule_type'],
                            'source_submodule_type' => $d['source_submodule_type'],
                            'cross_platform_flag' => $d['submodule_type'] !== $d['source_submodule_type'] ? true : false,
                            'cross_platform_des' => $this->getCrossPlatformDes($d['source_submodule_type'])
                        );
                    }
                }

                $info['total'] = count($info['rows']);
            }
        }

        return $info;
    }

    /**
     * 获取恢复源信息
     *
     * @param [type] $sourceModuleType
     * @return void
     */
    private function getCrossPlatformDes($sourceModuleType)
    {
        $des = '';
        switch ($sourceModuleType) {
            case 1:
                $des = xphp_get_lang('UI_OBS_RECOVER_SOURCE_FILE');
                break;
            case 2:
                $des = xphp_get_lang('UI_OBS_RECOVER_SOURCE_NAS');
                break;
            case 3:
                $des = xphp_get_lang('UI_OBS_RECOVER_SOURCE_HADOOP');
                break;
            case 4:
                $des = '';
                break;
        }

        return $des;
    }

    /**
     * 任务详情 - 对象存储列表 - 操作 启动备份任务
     * @param $params
     * @return string
     */
    public function startObsBackupJob($params)
    {
        // 获取备份模式
        $backup_mode = $params['backup_mode'];
        // 获取任务uuid
        $task_uuid = $params['task_uuid'];
        // 获取对象存储列表
        $obs_uuid_list = $params['obs_uuids'];

        $time_strategy_id = 0;

        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => xphp_get_config('app')['FLAG']['UNSET'],
            "fs_uuid_list" => $obs_uuid_list,
        );

        $opName = 'BD_TASK_OP_BACKUP_START';
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->opUnifyMsg($task_uuid, $opName, $msg, false, true);

        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 下载异常文件
     * @param array $params
     * @return string|void
     */
    public function downLoadPassFile(array $params)
    {
        $nodeuuid = $params['fsnodeuuid'];
        $historyUuid = $params['history_uuid'];
        $filename = 'passfilelist';
        $agentUuid = $params['agent_uuid'];
        $opName = "FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET";
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        $msg = array(
            "history_uuid" => $historyUuid,
            "read_offset" => 0,
            "read_size" => $blockSize,
            "agent_uuid" => $agentUuid,
        );
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);

        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($result) {
            ob_clean();
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Content-Disposition: attachment; filename=" . $filename . ".txt");
            //如果大于分块大小,分块下载
            $filesize = $mbResult['msg']['file_size'];
            for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
                if ($filesize - $i <= $blockSize) {
                    $readLen = $filesize - $i;
                } else {
                    $readLen = $blockSize;
                }
                $msg = array(
                    "history_uuid" => $historyUuid,
                    "read_offset" => $i,
                    "read_size" => $readLen,
                    "agent_uuid" => $agentUuid,
                );

                $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);

                echo $mbResult['msg']['data'];
                ob_flush(); //将数据从php的buffer中释放出来
                flush(); //将释放出来的数据发送给浏览器
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取对象存储历史任务
     * @param $params
     * @return array
     */
    public function getObsHistory($params): array
    {
        $taskUUID = $params['task_uuid'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sortName = $params['sort'];
        $sortOrder = $params['order'];

        $sql = "select 
                    id,
                    history_uuid, 
                    task_type, 
                    module_type, 
                    submodule_type, 
                    current_mode, 
                    error_code, 
                    details, 
                    total_object_size, 
                    total_object_transport_size, 
                    total_object_write_size,
                    total_object_completed_size, 
                    average_speed, 
                    start_time, 
                    finish_time  
                from bd_history_task 
                where task_uuid = ? order by $sortName $sortOrder limit ?, ?";

        $data = $this->dbSelect($sql, array($taskUUID, $offset, $limit));

        $sqlCount = "select count(*) as total from bd_history_task where task_uuid = ? ";
        $countData = $this->dbSelect($sqlCount, array($taskUUID));

        $info = array(
            'rows' => array(),
            'total' => $countData[0]['total'],
        );

        if (!empty($data)) {
            $i = 1;
            foreach ($data as $d) {
                $info['rows'][] = array(
                    'num' => $i++,
                    'id' => $d['id'],
                    'history_uuid' => $d['history_uuid'],
                    'task_type' => $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                    'job_status' => $this->getHistoryJobResultDes($d['error_code']),
                    'job_status_value' => $d['error_code'],
                    'total_object_size' => v1_calsize($d['total_object_size'], true),
                    'total_object_completed_size' => v1_calsize($d['total_object_completed_size'], true),
                    'total_object_write_size' => v1_calsize($d['total_object_write_size'], true),
                    'start_time' => $d['start_time'],
                    'finish_time' => $d['finish_time']
                );
            }
        }

        return $info;
    }

    /**
     * 获取文件客户端IP
     *
     * @param string $agentUUID 客户端uuid
     * @return void
     */
    private function getFileClientIp(string $agentUUID)
    {
        $sql = "select agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUUID));

        $result = '';
        if (!empty($data)) {
            if (!empty($data[0]['hostname'])) {
                $result = $data[0]['hostname'] . '(' . $data[0]['ip'] . ')';
            } else {
                $result = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
            }
        }

        return $result;
    }

    /**
     * 获取NAS设备IP
     *
     * @param string $agentUUID nas设备uuid
     * @return void
     */
    private function getNasClientIp(string $agentUUID)
    {
        $sql = "select nas_nickname, share_path, ip from nas_storage_resource where nas_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUUID));

        $result = '';

        if (!empty($data)) {
            if (empty($data[0]['nas_nickname'])) {
                $result = $data[0]['ip'] . '(' . $data[0]['share_path'] . ')';
            } else {
                if ($data[0]['nas_nickname'] === $data[0]['ip']) {
                    $result = $data[0]['ip'] . '(' . $data[0]['share_path'] . ')';
                } else {
                    $result = $data[0]['ip'] . '(' . $data[0]['nas_nickname'] . ')';
                }
            }
        }

        return $result;
    }

    /**
     * 获取对象存储信息
     * @param string $obsUUID
     * @return string
     */
    private function getObsClientIp(string $obsUUID)
    {
        $sql = "SELECT obs_nickname, access_key_id, vendor, endpoint_override FROM obs_resource WHERE obs_uuid = ?";

        $data = $this->dbSelect($sql, array($obsUUID));

        $result = array();
        if (!empty($data)) {
            $result['obs_name'] = $data[0]['obs_nickname'] . '(' . $data[0]['access_key_id'] . '@' . $data[0]['endpoint_override'] . ')';
            $result['vendor'] = $data[0]['vendor'];
        }

        return $result;
    }

    /**
     * 获取对象存储进度
     * @param $taskUUID
     * @param $agentUUID
     * @param $taskStatus
     * @param $taskType
     * @return array|mixed|string
     */
    private function getObsAgentProgress($taskUUID, $agentUUID, $taskStatus, $taskType)
    {
        if (
            $taskStatus !== xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus !== xphp_get_config('task', 'TASKSTATUS')['PAUSED'] &&
            $taskStatus !== xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            return xphp_get_config('app', 'NULLSPACE');
        }

        $sql = "select current_object_total_size, current_object_completed_size from fs_running_info where task_uuid = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID, $agentUUID));

        //备份任务只有目录时，文件大小为0  导致进度计算出来一直是0
//        if($agentStatus == 4) {
//            return 100.00 ."%";
//        }

        $speed = $this->getTaskTotalProgress(
            $taskStatus,
            $data[0]['current_object_total_size'],
            $data[0]['current_object_completed_size'],
            false,
            $taskType
        );

        return $speed;
    }

    /**
     * 任务详情-概览: 文件是否配置通配符
     * @param unknown $params
     */
    private function getWildCardInfo($task_uuid)
    {
        $sql = "select detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $count = 0;
        foreach ($data as $d) {
            $count += intval(json_decode($d['detail'], true)["wildcard_mode"]);
        }
        if ($count > 0) {//大于0则配置了通配符
            return true;
        }
        return false;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    private function getNodeNetworkFlag($taskuuid)
    {
        $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
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
     * 获取传输代理 agent ip
     *
     * @param string $appliance_uuid
     * @return void
     */
    private function getApplianceAgency(string $appliance_uuid)
    {
        $sql = 'select agent_name, ip from bd_agent where agent_uuid = ?';
        $data = $this->dbSelect($sql, array($appliance_uuid));

        $result = '';
        if (!empty($data)) {
            $result = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        }

        return $result;
    }

    /**
     * 获取nas源设备的信息
     * @param unknown $params
     */
    private function getNasResorceInfo($taskuuid)
    {
        //先找到恢复时间点
        $sql = " select fbt.detail,fbt.source_agent_type, bbt.sub_module_type from fs_path_list fpl,fs_backup_timepoint fbt,bd_backup_timepoint bbt where
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and fpl.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $detail = json_decode($data[0]['detail'], true);
        $sql = " select ip, share_path,nas_nickname from nas_storage_resource where nas_uuid = ?";
        $resdata = $this->dbSelect($sql, array($detail["nas_uuid"]));
        if ($resdata[0]['nas_nickname'] != $resdata[0]['ip']) {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['nas_nickname'] . ")";
        } else {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['share_path'] . ")";
        }
        $info = array(
            "nas_name" => $nas_name,
            "source_agent_type" => $data[0]['source_agent_type'],
            "src_type" => $data[0]['sub_module_type'],
        );
        return $info;
    }

    /**
     * 根据TASKUUID获取存储类型
     * @param string $taskUUID
     * @return mixed
     */
    private function getStorageType(string $taskUUID)
    {
        $storageType = '';

        $sql = "SELECT bsr.storage_type 
                FROM 
                    bd_storage_resource bsr, bd_backup_timepoint bbt, fs_path_list fpl 
                WHERE 
                    fpl.task_uuid = ? AND fpl.recovery_timepoint_uuid = bbt.timepoint_uuid AND bbt.storage_uuid = bsr.storage_uuid";

        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }
}