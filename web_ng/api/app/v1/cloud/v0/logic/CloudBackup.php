<?php

namespace app\v1\cloud\v0\logic;

use app\v1\opcode\PfOpcode;
use app\v1\common\logic\Unification;
use app\v1\resources\v0\logic\Node;
use app\v1\vm\v0\logic\VmBackUp as VmBackUp;
use app\v1\tenant\v0\logic\Tenant;
use xphp\BLLHandler;

/**
 * Class CloudBackup
 * @package app\v1\cloud\v0\logic
 */
class CloudBackup extends VmBackUp
{
    /**
     * 创建备份任务
     * @param array $params 参数
     * @return string
     */
    public function createBackupJob(array $params): string
    {
        if (v1_license_get_expire_days()['expire_days'] < 0) {
            // 授权无效
            $msg = xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED');
            exit($this->muOpResult(false, xphp_get_lang('UI_LICENSE_AUTH_INFO_TITLE'), $msg, 'warning'));
        }
        //public params
        $taskName = htmlspecialchars_decode($params['job_name']);
        $strategyGroupUuid = $params['strategy_group_uuid'];

        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $backupType = $params['time_strategy']['type'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            (new Tenant())->checkTenantAuth(xphp_get_config('module', 'MODULE_TYPE')['VM'], $params['src_info']['instance_info'], '','','',xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
        } else {
            $this->checkTaskLegal($operate, $params['src_info']['instance_info'], '', xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
        }
        $unification = new Unification();
        //组合时间策略
        $timeStrategy = $this->groupBackupTimeList($params['time_strategy'], $strategyGroupUuid);
        //组合保留策略
        $reservedStrategy = $unification->UnifyReserveStrategy($params['reserved_strategy'], $strategyGroupUuid);
        //组合传输策略
        $transportStrategy = array(
            'encrypt_flag' => v1_parse_bool_to_flag($params['transport_strategy']['encrypt']), // 加密传输
            'encrypt_method' => $params['transport_strategy']['encrypt']
                ? intval($params['transport_strategy']['encrypt_method']) : 0, // 加密传输算法
            'compress_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'compress_method' => 0,
            'speed_limit_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'max_speed' => 0,
            'network_uuid' => '',
            'strategy_group_uuid' => $strategyGroupUuid,
            'block_size' => 0,
            'reconnect_times' => intval($params['transport_strategy']['reconnect_times']), // 重连次数
            'reconnect_interval' => intval($params['transport_strategy']['reconnect_interval']), // 重连间隔时间
        );
        //组合存储策略
        $storageStrategy = $this->groupStorageStrategy($params['storage_strategy'], $strategyGroupUuid);
        //组合限速策略
        $speedStrategy = $this->groupTaskSpeedGlobalList($params['speed_strategy']);

        $nodeInfo = $params['advanced_strategy']['node'];

        $pfMsg = (new BLLHandler())->pfCreateBackupTaskMessage(
            $taskName,
            $moduleType,
            $timeStrategy,
            $reservedStrategy['reserveStrategy'],
            $transportStrategy,
            $storageStrategy,
            $nodeInfo
        );
        $pfMsg['time_strategy_list'] = $timeStrategy;
        $pfMsg['reserved_strategy'] = $reservedStrategy['reserveStrategy'];
        $pfMsg['transport_strategy'] = $transportStrategy;
        $pfMsg['storage_strategy'] = $storageStrategy;
        $pfMsg['speed_limit_strategy'] = $speedStrategy;
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        // 时间策略备份方式
        $pfMsg['time_strategy_backup_type'] = xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE')[$backupType];
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy']);

        //private params

        //备份等级 即高速模式
        $pfMsg['backup_level'] = $params['advanced_strategy']['mode']['snapshot_check']
            ? xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['KEEP_SNAPSHOT']
            : xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['NORMAL'];
        //保留快照
        $pfMsg['keep_snapshot_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['keep_snapshot_check']);
        // 快照超时时间
        $pfMsg['snapshot_timeout'] = intval($params['advanced_strategy']['mode']['snapshot_timeout']);
        //CBT模式（AWS不需要）
        $pfMsg['valid_data_backup'] = v1_parse_bool_to_flag(true);
        $pfMsg['reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $pfMsg['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        //快照模式
        $pfMsg['serial_snapshot_flag'] = intval($params['advanced_strategy']['mode']['snapshot_type']);
        //V-CBT深度有效数据提取
        $pfMsg['parse_fs_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['parse_fs_flag']);
        //静默快照（AWS不需要）
        $pfMsg['quiesce_snapshot'] = v1_parse_bool_to_flag(false);
        //传输模式
        $pfMsg['transport_priority'] = $this->getTransportMode($params['transport_strategy']['mode']);
        //传输代理实例类型
        $pfMsg['agent_uuid'] = $params['transport_strategy']['appliance_uuid'];
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['swap_flag']);
        //排除删除文件块（AWS不需要）
        $pfMsg['not_backup_deleted_file_flag'] = '';
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = v1_parse_bool_to_flag(
            $params['advanced_strategy']['mode']['gap_flag']
        );
        //提前创建快照
        $pfMsg['pre_create_snap_flag'] = intval($params['advanced_strategy']['mode']['pre_snapshot_flag']);
        //细粒度保留字段（AWS不需要）
        $pfMsg['grain_level'] = 0;
        //线程数量
        $pfMsg['thread_num'] = intval($params['advanced_strategy']['mode']['thread_num']);
        //实例信息
        $pfMsg['filter_criteria'] = $this->groupBackupVM($params['src_info']['instance_info']);
        //全局策略uuid
        $pfMsg['strategy_group_uuid'] = $strategyGroupUuid;
        //备份系统节点IP（AWS不需要）
        $pfMsg['backup_server_ip'] = '';
        //指定网段（AWS不需要）
        $pfMsg['transport_ip_segment'] = '';
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $reservedStrategy['gfs'];
        //display mode
        $pfMsg['display_mode'] = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
        //自动加入备份开关
        $pfMsg['auto_join_flag'] = (string)v1_parse_bool_to_flag($params['src_info']['auto_join_flag']);
        //全局筛选条件
        $params['src_info']['global_select']['select_type'] = strval($params['src_info']['global_select']['select_type']);
        $pfMsg['global_select'] = $params['src_info']['global_select'] ?: [];
        //传输代理公有ip配置
        $pfMsg['agent_public_ip'] = $params['transport_strategy']['agent_public_ip'];
        // 传输代理网络配置
        $pfMsg['agent_network'] = $params['transport_strategy']['agent_network'];
        // 重试策略
        $pfMsg['retry_strategy'] = $this->groupRetryStrategy($params['retry_strategy']);
        // 合并模式
        $pfMsg['merge_mode'] = intval($params['advanced_strategy']['mode']['merge_mode']);

        $msg = json_encode($pfMsg);
        $nodeUuid = $nodeInfo['node_uuid'] ?: '';
        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            $params['hypervisor_type'],
            $opName,
            $msg
        );
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改备份任务
     * @param array $params 参数
     * @return string
     */
    public function editBackupJob(array $params): string
    {
        $hypervisor = $params['src_info']['hypervisor_type'];
        $taskName = htmlspecialchars_decode($params['job_name']);
        $taskUuid = $params['job_uuid'];
        $strategyGroupUuid = $params['strategy_group_uuid'];
        $this->paramsCheck($taskUuid);
        $operate = xphp_get_lang('UI_BACKUP_PUBLIC_CLOUD_EDIT_TASK');
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            (new Tenant())->checkTenantAuth(xphp_get_config('module', 'MODULE_TYPE')['VM'], $params['src_info']['instance_info'], $taskUuid, '', '', xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
        } else {
            $this->checkTaskLegal($operate, $params['src_info']['instance_info'], $taskUuid, xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
        }
        //优先检测一次性备份的时候其时间是否已过期
        $timeStrategy = $params['time_strategy'];
        $this->checkOnceBackupTimeStrategy($params['time_strategy']);

        //监测任务状态是否在停止中
        $bdTaskData = $this->checkJobIsStopped($taskUuid, $operate);
        $strategyID = $bdTaskData['strategy_id'];
        $cachePath = $bdTaskData['cache_dir_path'];

        $nodeInfo = $this->getModifyJobNodeAndStorageInfo($params['advanced_strategy']['node']);
        $this->dbBeginTransaction();
        //更新任务表         bd_task
        $result = $this->dbUpdateBdTask($nodeInfo, $taskName, $taskUuid, $params, $cachePath);

        //更新时间点 bd_backup_timepoint
        $result = $this->dbUpdateBdBackupTimepoint($result, $taskName, $taskUuid);

        //更新线程数量
        $sql = "update bd_task set thread_num = ?, strategy_group_uuid = ? where task_uuid = ?";
        $result = $result && $this->dbExec(
            $sql,
            array(intval($params['advanced_strategy']['mode']['thread_num']), $strategyGroupUuid, $taskUuid)
        );
        //更新虚拟机任务表 vm_task
        $sql = "update vm_task set level = ?, transport_priority = ?, serial_snapshot_flag = ?, parse_fs_flag = ?, 
            not_backup_swap_file_flag = ?, not_backup_partition_gap_flag = ?, agent_uuid = ?, select_conditions = ?,
            pre_create_snap_flag = ?, detail = ? where task_uuid = ?";
        //备份等级 即高速模式
        $highLevel = $params['advanced_strategy']['mode']['snapshot_check']
            ? xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['KEEP_SNAPSHOT']
            : xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['NORMAL'];
        $transportMode = $this->getTransportMode($params['transport_strategy']['mode']);
        //V-CBT深度有效数据提取
        $parseFsMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['parse_fs_flag']);
        //排除交换文件块
        $swapMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['swap_flag']);
        //排除分区间隙
        $gapMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['gap_flag']);
        //提前创建快照
        $preSnapshot = intval($params['advanced_strategy']['mode']['pre_snapshot_flag']);
        //传输代理实例类型
        $applianceUuid = $params['transport_strategy']['appliance_uuid'];
        //快照模式
        $snapshotMode = intval($params['advanced_strategy']['mode']['snapshot_type']);
        // 快照模式为并行时，默认关闭提前创建快照
        if (2 == $snapshotMode) {
            $preSnapshot = xphp_get_config('app', 'FLAG')['UNSET'];
        }
        //全局筛选条件
        $globalSelect = $this->globalSelectHandler($params['src_info']['global_select']);
        $globalSelectJson = json_encode($globalSelect);
        //任务详情
        $detailSql = "select detail from vm_task where task_uuid = ?";
        $detail = $this->dbSelect($detailSql, [$taskUuid])[0]['detail'];
        $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
        $detailArr['auto_join_flag'] = v1_parse_bool_to_flag($params['src_info']['auto_join_flag']);
        $detailArr['agent_public_ip'] = $params['transport_strategy']['agent_public_ip'];
        // 保留快照
        $detailArr['keep_snapshot_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['keep_snapshot_check']);
        // 快照超时时间
        $detailArr['snapshot_timeout'] = intval($params['advanced_strategy']['mode']['snapshot_timeout']);
        // 传输代理网络配置
        $detailArr['agent_network'] = $params['transport_strategy']['agent_network'];
        $result = $result && $this->dbExec(
                $sql,
                array($highLevel, $transportMode, $snapshotMode, $parseFsMode,
                    $swapMode, $gapMode, $applianceUuid, $globalSelectJson,
                    $preSnapshot, json_encode($detailArr), $taskUuid
                )
            );

        //更新虚拟机列表 vm_machine_list 或 vm_object_list
        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
        $result = $this->dbUpdateVmMachineList($result, $params, $taskUuid, $subType);

        //更新时间策略表 bd_time_strategy
        $result = $this->dbUpdateBdTimeStrategy($result, $strategyID, $timeStrategy);

        // 更新bd_strategy表
        $result = $this->dbUpdateBdStrategy($result, $strategyID, $timeStrategy);

        //更新保留策略表 bd_reserved_strategy
        $reservedStrategy = $params['reserved_strategy'];
        $result = $this->dbUpdateBdReservedStrategy($result, $strategyID, $reservedStrategy);

        //更新GFS策略
        $result = $result && $this->updateGFSStrategy(
                $reservedStrategy['gfs_reserved_strategy'],
                $reservedStrategy['gfs_modify_flag'], //是否有修改标记
                $taskUuid,
                $params['src_info']['instance_info']
            );

        //更新传输策略表 bd_transport_strategy
        $transportStrategy = $params['transport_strategy'];
        $result = $this->dbUpdateBdTransportStrategy($result, $strategyID, $transportStrategy);

        //更新存储策略表 bd_storage_strategy
        $storageStrategy = $params['storage_strategy'];
        $result = $this->dbUpdateBdStorageStrategy($result, $strategyID, $storageStrategy);

        //限速策略
        $speedStrategy = $params['speed_strategy'];
        $result = $this->dbUpdateBdSpeedStrategy($result, $speedStrategy, $taskUuid, $taskName);

        // 安全策略
        $result = $this->dbUpdateBdTaskSafeConfig($result, $params['safe_strategy'], $taskUuid);

        // 重试策略
        $result = $this->dbUpdateBdRetryStrategy($result, $params['retry_strategy'], $taskUuid);

        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }
        if ($result) {
            $logParams = array(
                'task_uuid' => $taskUuid,
                'task_name' => $taskName,
                'task_type' => xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                'module_type' => xphp_get_config('module', 'MODULE_TYPE')['VM'],
                'submodule_type' => $hypervisor,
            );
            $this->taskLog($logParams, 'WEB_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS', array($taskName));
        }
        return $this->muOpResult($result, $operate);
    }

    /**
     * 修改备份任务，新版本发后台消息
     * @param array $params 参数
     * @return string
     */
    public function editBackupJobV2(array $params): string
    {
        if (v1_license_get_expire_days()['expire_days'] < 0) {
            // 授权无效
            $msg = xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED');
            exit($this->muOpResult(false, xphp_get_lang('UI_LICENSE_AUTH_INFO_TITLE'), $msg, 'warning'));
        }
        //public params
        $taskName = htmlspecialchars_decode($params['job_name']);
        $taskUuid = $params['job_uuid'];
        $strategyGroupUuid = $params['strategy_group_uuid'];

        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $operate = xphp_get_lang('UI_BACKUP_VM_EDIT_TASK');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $backupType = $params['time_strategy']['type'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            (new Tenant())->checkTenantAuth(xphp_get_config('module', 'MODULE_TYPE')['VM'], $params['src_info']['instance_info'], '','','',xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
        } else {
            $this->checkTaskLegal($operate, $params['src_info']['instance_info'], '', xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
        }
        $unification = new Unification();
        //组合时间策略
        $timeStrategy = $this->groupBackupTimeList($params['time_strategy'], $strategyGroupUuid);
        //组合保留策略
        $reservedStrategy = $unification->UnifyReserveStrategy($params['reserved_strategy'], $strategyGroupUuid);
        //组合传输策略
        $transportStrategy = array(
            'encrypt_flag' => v1_parse_bool_to_flag($params['transport_strategy']['encrypt']), // 加密传输
            'encrypt_method' => $params['transport_strategy']['encrypt']
                ? intval($params['transport_strategy']['encrypt_method']) : 0, // 加密传输算法
            'compress_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'compress_method' => 0,
            'speed_limit_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'max_speed' => 0,
            'network_uuid' => '',
            'strategy_group_uuid' => $strategyGroupUuid,
            'block_size' => 0,
            'reconnect_times' => intval($params['transport_strategy']['reconnect_times']), // 重连次数
            'reconnect_interval' => intval($params['transport_strategy']['reconnect_interval']), // 重连间隔时间
        );
        //组合存储策略
        $storageStrategy = $this->groupStorageStrategy($params['storage_strategy'], $strategyGroupUuid);
        //组合限速策略
        $speedStrategy = $this->groupTaskSpeedGlobalList($params['speed_strategy']);

        $nodeInfo = $params['advanced_strategy']['node'];

        $pfMsg = (new BLLHandler())->pfCreateBackupTaskMessage(
            $taskName,
            $moduleType,
            $timeStrategy,
            $reservedStrategy['reserveStrategy'],
            $transportStrategy,
            $storageStrategy,
            $nodeInfo
        );
        $pfMsg['task_uuid'] = $taskUuid;
        $pfMsg['time_strategy_list'] = $timeStrategy;
        $pfMsg['reserved_strategy'] = $reservedStrategy['reserveStrategy'];
        $pfMsg['transport_strategy'] = $transportStrategy;
        $pfMsg['storage_strategy'] = $storageStrategy;
        $pfMsg['speed_limit_strategy'] = $speedStrategy;
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        // 时间策略备份方式
        $pfMsg['time_strategy_backup_type'] = xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE')[$backupType];
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy']);

        //private params

        //备份等级 即高速模式
        $pfMsg['backup_level'] = $params['advanced_strategy']['mode']['snapshot_check']
            ? xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['KEEP_SNAPSHOT']
            : xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['NORMAL'];
        //保留快照
        $pfMsg['keep_snapshot_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['keep_snapshot_check']);
        // 快照超时时间
        $pfMsg['snapshot_timeout'] = intval($params['advanced_strategy']['mode']['snapshot_timeout']);
        //CBT模式（AWS不需要）
        $pfMsg['valid_data_backup'] = v1_parse_bool_to_flag(true);
        $pfMsg['reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $pfMsg['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        //快照模式
        $pfMsg['serial_snapshot_flag'] = intval($params['advanced_strategy']['mode']['snapshot_type']);
        //V-CBT深度有效数据提取
        $pfMsg['parse_fs_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['parse_fs_flag']);
        //静默快照（AWS不需要）
        $pfMsg['quiesce_snapshot'] = v1_parse_bool_to_flag(false);
        //传输模式
        $pfMsg['transport_priority'] = $this->getTransportMode($params['transport_strategy']['mode']);
        //传输代理实例类型
        $pfMsg['agent_uuid'] = $params['transport_strategy']['appliance_uuid'];
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['swap_flag']);
        //排除删除文件块（AWS不需要）
        $pfMsg['not_backup_deleted_file_flag'] = '';
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = v1_parse_bool_to_flag(
            $params['advanced_strategy']['mode']['gap_flag']
        );
        //提前创建快照
        $pfMsg['pre_create_snap_flag'] = intval($params['advanced_strategy']['mode']['pre_snapshot_flag']);
        //细粒度保留字段（AWS不需要）
        $pfMsg['grain_level'] = 0;
        //线程数量
        $pfMsg['thread_num'] = intval($params['advanced_strategy']['mode']['thread_num']);
        //实例信息
        $pfMsg['filter_criteria'] = $this->groupBackupVM($params['src_info']['instance_info']);
        //全局策略uuid
        $pfMsg['strategy_group_uuid'] = $strategyGroupUuid;
        //备份系统节点IP（AWS不需要）
        $pfMsg['backup_server_ip'] = '';
        //指定网段（AWS不需要）
        $pfMsg['transport_ip_segment'] = '';
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $reservedStrategy['gfs'];
        //display mode
        $pfMsg['display_mode'] = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
        //自动加入备份开关
        $pfMsg['auto_join_flag'] = (string)v1_parse_bool_to_flag($params['src_info']['auto_join_flag']);
        //全局筛选条件
        $pfMsg['global_select'] = $this->globalSelectHandler($params['src_info']['global_select']);
        //传输代理公有ip配置
        $pfMsg['agent_public_ip'] = $params['transport_strategy']['agent_public_ip'];
        // 传输代理网络配置
        $pfMsg['agent_network'] = $params['transport_strategy']['agent_network'];
        // 重试策略
        $pfMsg['retry_strategy'] = $this->groupRetryStrategy($params['retry_strategy']);
        // 合并模式
        $pfMsg['merge_mode'] = intval($params['advanced_strategy']['mode']['merge_mode']);
        // 并行传输（公有云不需要）
        $pfMsg['parallel_transfer_vm_count'] = 1;
        $pfMsg['single_vm_parallel_disk_transfer_count'] = 1;
        $pfMsg['vm_single_disk_parallel_transfer_count'] = 1;

        $msg = json_encode($pfMsg);
        $nodeUuid = $nodeInfo['node_uuid'] ?: Node::instance()->getMasterNodeUuid();
        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            $params['hypervisor_type'],
            $opName,
            $msg
        );
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    //todo:protected方法

    //todo:private方法

    /**
     * 得到传输模式到后台的字符串
     * @param string $mode nbd/nbdssl
     * @return string
     */
    public function getTransportMode(string $mode): string
    {
        $transportMode = '1';  //网络传输  nbd
        if ('nbd' == $mode) {
            $transportMode = '1';  //网络传输  nbd
        } elseif ('nbdssl' == $mode) {
            $transportMode = '2';  //网络加密传输  nbdssl
        }
        return $transportMode;
    }
}