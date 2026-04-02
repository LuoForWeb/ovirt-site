<?php

namespace app\v1\vm\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Unification;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Index as SystemHandler;
use app\v1\tenant\v0\logic\Tenant;
use xphp\BLLHandler;

/**
 * note          虚拟机 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmBackUp extends Backup
{
    /**
     * 创建备份任务 demo
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

        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['VM'];
        //私有云
        if (in_array($params['hypervisor_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $subType = xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        }
        //租户内检查可用数量是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $tenantHandler->checkTenantAuth($moduleType, $params['src_info']['vm_info'], '', '', '', $subType);
        } else {
            $this->checkTaskLegal($operate, $params['src_info']['vm_info'], '', $subType);
        }

        //如果是华为CBR 需要设置路径
        $storagepath = "";
        if (
            $params['advanced_strategy']['node']['storage_type']
            == xphp_get_config('resource', 'BD_STORAGE_TYPE')['HUAWEI_CBR']
        ) {
            //需要从数据库中获取存储库的id
            $sql = "select storage_config from  bd_storage_resource where storage_uuid = ?";
            $sqlParams = array($params['highInfo']['node']['storageuuid']);
            $data = $this->dbSelect($sql, $sqlParams);
            $configinfo = json_decode($data[0]['storage_config'], true);
            $storagepath = $configinfo['vault_id'];
        }

        $unification = new Unification();
        //组合时间策略
        $timeStrategy = $this->groupBackupTimeList($params['time_strategy'], $strategyGroupUuid);
        //组合保留策略
        $reservedStrategy = $unification->UnifyReserveStrategy($params['reserved_strategy'], $strategyGroupUuid);
        //组合传输策略
        $transportStrategy = array(
            'mode' => $params['transport_strategy']['mode'],
            'encrypt_flag' => v1_parse_bool_to_flag($params['transport_strategy']['encrypt']), // 加密传输
            'encrypt_method' => $params['transport_strategy']['encrypt']
                ? intval($params['transport_strategy']['encrypt_method']) : 0, // 加密传输算法
            'compress_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'compress_method' => 0,
            'speed_limit_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'max_speed' => 0,
            'network_uuid' => '',
            'strategy_group_uuid' => $strategyGroupUuid,
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
        $pfMsg['backup_level'] = $this->getBackupSnapshotMode(
            $params['hypervisor_type'],
            $params['advanced_strategy']['mode']['snapshot_flag'],
            $params['advanced_strategy']['mode']['inc_mode']
        );
        // 保留快照（公有云使用）
        $pfMsg['keep_snapshot_flag'] = v1_parse_bool_to_flag(false);
        // 快照超时时间（公有云使用）
        $pfMsg['snapshot_timeout'] = intval(3600);
        //CBT模式
        $pfMsg['valid_data_backup'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['cbt_flag']);
        $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $pfMsg['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        if (xphp_get_config('vm', 'RESET_CBT_LEVEL')['FULL_BACKUP'] == $params['advanced_strategy']['mode']['reset_cbt_level']) {
            $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
            $pfMsg['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
        } elseif (xphp_get_config('vm', 'RESET_CBT_LEVEL')['ERROR'] == $params['advanced_strategy']['mode']['reset_cbt_level']) {
            $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
        }
        //快照模式
        $pfMsg['serial_snapshot_flag'] = intval($params['advanced_strategy']['mode']['snapshot_type']);
        // 高速磁盘CBT
        $pfMsg['highspeed_disk_cbt_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['highspeed_disk_cbt_flag']);
        //V-CBT深度有效数据提取
        $pfMsg['parse_fs_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['parse_fs_flag']);
        //静默快照
        $pfMsg['quiesce_snapshot'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['silent_snapshot_flag']);
        //传输模式
        $pfMsg['transport_priority'] = $this->getTransportMode($params['transport_strategy']['mode']);
        //传输代理资源池
        $pfMsg['agent_pool_uuid'] = $params['transport_strategy']['agent_pool_uuid'];
        //传输代理
        $pfMsg['agent_uuid'] = $params['transport_strategy']['agent_uuid'];
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['swap_flag']);
        //排除删除文件块
        $pfMsg['not_backup_deleted_file_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['delete_file_flag']);
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = v1_parse_bool_to_flag(
            $params['advanced_strategy']['mode']['gap_flag']
        );
        //提前创建快照
        $pfMsg['pre_create_snap_flag'] = intval($params['advanced_strategy']['mode']['pre_snapshot_flag']);
        //细粒度保留字段
        $pfMsg['grain_level'] = 0;
        //线程数量
        $pfMsg['thread_num'] = intval($params['advanced_strategy']['mode']['thread_num']);
        //虚拟机信息
        $pfMsg['filter_criteria'] = $this->groupBackupVM($params['src_info']['vm_info']);
        //全局策略uuid
        $pfMsg['strategy_group_uuid'] = $strategyGroupUuid;
        //备份系统节点IP
        $pfMsg['backup_server_ip'] = $params['backup_server_ip'];
        //指定网段
        $pfMsg['transport_ip_segment'] = $params['transport_ip_segment'];
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $reservedStrategy['gfs'];
        //新增存储路径和校验
        $pfMsg['storage_location'] = $storagepath;
        //存储校验
        $pfMsg['calibreate_strategy_list'] = array(
            "int_create_calibration_algorithm" => v1_parse_bool_to_flag($params['verify_info']['is_check']), //
            "int_calibration_algorithm" => $params['verify_info']['verify_type'] //选择校验算法
        );
        //cbr任务需要，此处传空
        $pfMsg['extension_info'] = null;
        //display mode
        $pfMsg['display_mode'] = $params['src_info']['type']
            ?? xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
        //自动加入备份开关
        $pfMsg['auto_join_flag'] = (string) v1_parse_bool_to_flag($params['src_info']['auto_join_flag']);
        //全局筛选条件
        $pfMsg['global_select'] = $this->globalSelectHandler($params['src_info']['global_select']);
        //传输代理公有ip配置（虚拟机不使用，补全默认值）
        $pfMsg['agent_public_ip'] = ['ip_uuid' => '', 'ip_address' => '', 'chargemode' => 'traffic', 'size' => 300];
        //创建存储快照
        $pfMsg['storage_snapshot_enable_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['storage_snapshot_flag']);
        // 传输压缩
        $pfMsg['source_compression'] = $params['transport_strategy']['transfer_compress'];
        // 异步传输
        $pfMsg['async_rw_flag'] = v1_parse_bool_to_flag($params['transport_strategy']['async_transfer']);
        // 重试策略
        $pfMsg['retry_strategy'] = $this->groupRetryStrategy($params['retry_strategy']);
        // 合并模式
        $pfMsg['merge_mode'] = intval($params['advanced_strategy']['mode']['merge_mode']);
        // 并行传输
        $pfMsg['parallel_transfer_vm_count'] = $params['transport_strategy']['parallel_transfer_vm_count'];
        $pfMsg['single_vm_parallel_disk_transfer_count'] = $params['transport_strategy']['single_vm_parallel_disk_transfer_count'];
        $pfMsg['vm_single_disk_parallel_transfer_count'] = $params['transport_strategy']['vm_single_disk_parallel_transfer_count'];
        // 删除快照速度
        $pfMsg['snapshot_del_speed'] = intval($params['advanced_strategy']['mode']['snapshot_del_speed']);

        $msg = json_encode($pfMsg);
//                dump($msg);
        $nodeUuid = $nodeInfo['node_uuid'] ?: Node::instance()->getMasterNodeUuid();
        $mbResult = $this->service()->unifyVmPlatformService(
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
        $strategygroupuuid = $params['strategy_group_uuid'];
        $this->paramsCheck($taskUuid);
        $operate = xphp_get_lang('UI_BACKUP_VM_EDIT_TASK');

        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['VM'];
        //私有云
        if (in_array($params['hypervisor_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $subType = xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        }
        //租户内检查可用数量是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $tenantHandler->checkTenantAuth(xphp_get_config('module', 'MODULE_TYPE')['VM'], $params['src_info']['vm_info'], $taskUuid, '', '', $subType);
        } else {
            $this->checkTaskLegal($operate, $params['src_info']['vm_info'], $taskUuid, $subType);
        }
        //如果是租户内部的用户操作，检查设置速度是否超出租户配置限制速度
//         if(!empty($_SESSION['tenantuuid'])){
//             $this->pCheckSpeedLimit($params['speedInfo'], $operate);
//         }

        //优先检测一次性备份的时候其时间是否已过期
        $timeStrategy = $params['time_strategy'];
        $this->checkOnceBackupTimeStrategy($timeStrategy);

        //监测任务状态是否在停止中
        $bdTaskData = $this->checkJobIsStopped($taskUuid, $operate);
        $strategyID = $bdTaskData['strategy_id'];
        $cachePath = $bdTaskData['cache_dir_path'];

        //获取节点信息
        $nodeInfo = $this->getModifyJobNodeAndStorageInfo($params['advanced_strategy']['node']);

        //开始事务
        $this->dbBeginTransaction();

        //更新任务表         bd_task
        $result = $this->dbUpdateBdTask($nodeInfo, $taskName, $taskUuid, $params, $cachePath);

        //更新时间点 bd_backup_timepoint
        $result = $this->dbUpdateBdBackupTimepoint($result, $taskName, $taskUuid);

        //更新 bd_task
        $sql = "update bd_task set thread_num = ?, strategy_group_uuid = ?, backup_server_ip = ?, 
                   transport_ip_segment= ? where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array(
            intval($params['advanced_strategy']['mode']['thread_num']),
            $strategygroupuuid,
            $params['backup_server_ip'],
            $params['transport_ip_segment'],
            $taskUuid
        )
        );

        //更新虚拟机任务表 vm_task
        $sql = "select display_mode from vm_task where task_uuid = ?";
        $oldDisplayMode = $this->dbSelect($sql, [$taskUuid])[0]['display_mode'];
        $newDisplayMode = 0 == $oldDisplayMode
            ? xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'] : $oldDisplayMode;
        $sql = "update vm_task set level = ?, quiesce_snapshot = ?, transport_priority = ?, valid_data_backup = ?, 
            serial_snapshot_flag = ?, parse_fs_flag = ?, not_backup_swap_file_flag = ?, not_backup_deleted_file_flag = ?, 
            not_backup_partition_gap_flag = ?, agent_uuid = ?, display_mode = ?, select_conditions = ?, 
            pre_create_snap_flag = ?, storage_snapshot_enable_flag = ?, detail = ? where task_uuid = ?";
        //         $level = $this->getVMTaskLevel($params['highInfo']['mode']['snapshotcheck']);
        $level = $this->getBackupSnapshotMode(
            $params['src_info']['hypervisor_type'],
            $params['advanced_strategy']['mode']['snapshot_type'],
            $params['advanced_strategy']['mode']['inc_mode']
        );
        $quiesceSnapshot = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['silent_snapshot_flag']);
        $transportMode = $this->getTransportMode($params['transport_strategy']['mode']);
        //CBT模式
        $cbtMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['cbt_flag']);
        //V-CBT
        $parsefsMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['parse_fs_flag']);
        //排除交换文件块
        $swapMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['swap_flag']);
        //排除删除文件块
        $deletefileMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['delete_file_flag']);
        //排除分区间隙
        $gapMode = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['gap_flag']);
        $presnapshot = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['pre_snapshot_flag']);
        //创建存储快照
        $storagesnapshot = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['storage_snapshot_flag']);
        //虚拟化类型为FLEX_CLOUD或者OPENSTACK或者FLEX_HCS时，并且传输模式不是网络传输，清掉代理
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']) && $transportMode != 3) {
            $agentUuid = '';
        } else {
            $agentUuid = $params['transport_strategy']['appliance_uuid'];
        }
        //全局筛选条件
        $globalSelect = $this->globalSelectHandler($params['src_info']['global_select']);
        $globalSelectJson = json_encode($globalSelect);
        //快照模式
        $snapshotMode = intval($params['advanced_strategy']['mode']['snapshot_type']);
        // 快照模式为并行时，默认关闭提前创建快照
        if (2 == $snapshotMode) {
            $presnapshot = xphp_get_config('app', 'FLAG')['UNSET'];
        }
        //任务详情
        $detailSql = "select detail from vm_task where task_uuid = ?";
        $detail = $this->dbSelect($detailSql, [$taskUuid])[0]['detail'];
        $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
        $detailArr['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $detailArr['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        if (xphp_get_config('vm', 'RESET_CBT_LEVEL')['FULL_BACKUP'] == $params['advanced_strategy']['mode']['reset_cbt_level']) {
            $detailArr['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
            $detailArr['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
        } elseif (xphp_get_config('vm', 'RESET_CBT_LEVEL')['ERROR'] == $params['advanced_strategy']['mode']['reset_cbt_level']) {
            $detailArr['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
        }
        $detailArr['auto_join_flag'] = (string) v1_parse_bool_to_flag($params['src_info']['auto_join_flag']);
        // 高速磁盘CBT
        $detailArr['highspeed_disk_cbt_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['highspeed_disk_cbt_flag']);
        // 传输压缩
        $detailArr['source_compression'] = $params['transport_strategy']['transfer_compress'];
        // 异步传输
        $detailArr['async_rw_flag'] = v1_parse_bool_to_flag($params['transport_strategy']['async_transfer']);
        $result = $result && $this->dbExec($sql, array(
            $level,
            $quiesceSnapshot,
            $transportMode,
            $cbtMode,
            $snapshotMode,
            $parsefsMode,
            $swapMode,
            $deletefileMode,
            $gapMode,
            $agentUuid,
            $newDisplayMode,
            $globalSelectJson,
            $presnapshot,
            $storagesnapshot,
            json_encode($detailArr),
            $taskUuid
        )
        );

        //更新虚拟机列表 vm_machine_list 或 vm_object_list
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
            $params['src_info']['vm_info']
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

        //更新数据校验策略表 bd_integrity_check_strategy
//        $verifyInfo = $params['verifyInfo'];
//        $sql = "update bd_integrity_check_strategy set is_integrity_check = ?,calibration_algorithm  = ? where task_uuid = ?";
//        $sqlParams = array($utils->parseBoolToFlag($verifyInfo['ischeck']), $verifyInfo['verifytype'],$taskUuid);
//        $result = $result && $this->dbExec($sql, $sqlParams);

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
        $hypervisor = $params['src_info']['hypervisor_type'];
        $taskName = htmlspecialchars_decode($params['job_name']);
        $taskUuid = $params['job_uuid'];
        $strategyGroupUuid = $params['strategy_group_uuid'];
        $this->paramsCheck($taskUuid);

        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $operate = xphp_get_lang('UI_BACKUP_VM_EDIT_TASK');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $backupType = $params['time_strategy']['type'];

        $subType = xphp_get_config('module', 'VM_SUB_MODULE')['VM'];
        //私有云
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $subType = xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        }
        //租户内检查可用数量是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $tenantHandler->checkTenantAuth(xphp_get_config('module', 'MODULE_TYPE')['VM'], $params['src_info']['vm_info'], $taskUuid, '', '', $subType);
        } else {
            $this->checkTaskLegal($operate, $params['src_info']['vm_info'], $taskUuid, $subType);
        }

        $unification = new Unification();
        //组合时间策略
        $timeStrategy = $this->groupBackupTimeList($params['time_strategy'], $strategyGroupUuid);
        //组合保留策略
        $reservedStrategy = $unification->UnifyReserveStrategy($params['reserved_strategy'], $strategyGroupUuid);
        //组合传输策略
        $transportStrategy = array(
            'mode' => $params['transport_strategy']['mode'],
            'encrypt_flag' => v1_parse_bool_to_flag($params['transport_strategy']['encrypt']), // 加密传输
            'encrypt_method' => $params['transport_strategy']['encrypt']
                ? intval($params['transport_strategy']['encrypt_method']) : 0, // 加密传输算法
            'compress_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'compress_method' => 0,
            'speed_limit_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'max_speed' => 0,
            'network_uuid' => '',
            'strategy_group_uuid' => $strategyGroupUuid,
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
        $pfMsg['backup_level'] = $this->getBackupSnapshotMode(
            $hypervisor,
            $params['advanced_strategy']['mode']['snapshot_flag'],
            $params['advanced_strategy']['mode']['inc_mode']
        );
        // 保留快照（公有云使用）
        $pfMsg['keep_snapshot_flag'] = v1_parse_bool_to_flag(false);
        // 快照超时时间（公有云使用）
        $pfMsg['snapshot_timeout'] = intval(3600);
        //CBT模式
        $pfMsg['valid_data_backup'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['cbt_flag']);
        $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        $pfMsg['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(false);
        if (xphp_get_config('vm', 'RESET_CBT_LEVEL')['FULL_BACKUP'] == $params['advanced_strategy']['mode']['reset_cbt_level']) {
            $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
            $pfMsg['full_backup_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
        } elseif (xphp_get_config('vm', 'RESET_CBT_LEVEL')['ERROR'] == $params['advanced_strategy']['mode']['reset_cbt_level']) {
            $pfMsg['error_reset_cbt_flag'] = (string)v1_parse_bool_to_flag(true);
        }
        //快照模式
        $pfMsg['serial_snapshot_flag'] = intval($params['advanced_strategy']['mode']['snapshot_type']);
        // 高速磁盘CBT
        $pfMsg['highspeed_disk_cbt_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['highspeed_disk_cbt_flag']);
        //V-CBT深度有效数据提取
        $pfMsg['parse_fs_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['parse_fs_flag']);
        //静默快照
        $pfMsg['quiesce_snapshot'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['silent_snapshot_flag']);
        //传输模式
        $pfMsg['transport_priority'] = $this->getTransportMode($params['transport_strategy']['mode']);
        //传输代理资源池
        $pfMsg['agent_pool_uuid'] = $params['transport_strategy']['agent_pool_uuid'];
        //传输代理
        $pfMsg['agent_uuid'] = $params['transport_strategy']['appliance_uuid'];
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['swap_flag']);
        //排除删除文件块
        $pfMsg['not_backup_deleted_file_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['delete_file_flag']);
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = v1_parse_bool_to_flag(
            $params['advanced_strategy']['mode']['gap_flag']
        );
        //提前创建快照
        $pfMsg['pre_create_snap_flag'] = intval($params['advanced_strategy']['mode']['pre_snapshot_flag']);
        //细粒度保留字段
        $pfMsg['grain_level'] = 0;
        //线程数量
        $pfMsg['thread_num'] = intval($params['advanced_strategy']['mode']['thread_num']);
        //虚拟机信息
        $pfMsg['filter_criteria'] = $this->groupBackupVM($params['src_info']['vm_info']);
        //全局策略uuid
        $pfMsg['strategy_group_uuid'] = $strategyGroupUuid;
        //备份系统节点IP
        $pfMsg['backup_server_ip'] = $params['backup_server_ip'];
        //指定网段
        $pfMsg['transport_ip_segment'] = $params['transport_ip_segment'];
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $reservedStrategy['gfs'];
        //存储校验
        $pfMsg['calibreate_strategy_list'] = array(
            "int_create_calibration_algorithm" => v1_parse_bool_to_flag($params['verify_info']['is_check']), //
            "int_calibration_algorithm" => $params['verify_info']['verify_type'] //选择校验算法
        );
        //display mode
        $pfMsg['display_mode'] = $params['src_info']['display_mode']
            ?? xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
        //自动加入备份开关
        $pfMsg['auto_join_flag'] = (string) v1_parse_bool_to_flag($params['src_info']['auto_join_flag']);
        //全局筛选条件
        $pfMsg['global_select'] = $this->globalSelectHandler($params['src_info']['global_select']);
        //传输代理公有ip配置（虚拟机不使用，补全默认值）
        $pfMsg['agent_public_ip'] = ['ip_uuid' => '', 'ip_address' => '', 'chargemode' => 'traffic', 'size' => 300];
        //创建存储快照
        $pfMsg['storage_snapshot_enable_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['storage_snapshot_flag']);
        // 传输压缩
        $pfMsg['source_compression'] = $params['transport_strategy']['transfer_compress'];
        // 异步传输
        $pfMsg['async_rw_flag'] = v1_parse_bool_to_flag($params['transport_strategy']['async_transfer']);
        // 重试策略
        $pfMsg['retry_strategy'] = $this->groupRetryStrategy($params['retry_strategy']);
        // 合并模式
        $pfMsg['merge_mode'] = intval($params['advanced_strategy']['mode']['merge_mode']);
        // 并行传输
        $pfMsg['parallel_transfer_vm_count'] = $params['transport_strategy']['parallel_transfer_vm_count'];
        $pfMsg['single_vm_parallel_disk_transfer_count'] = $params['transport_strategy']['single_vm_parallel_disk_transfer_count'];
        $pfMsg['vm_single_disk_parallel_transfer_count'] = $params['transport_strategy']['vm_single_disk_parallel_transfer_count'];
        // 删除快照速度
        $pfMsg['snapshot_del_speed'] = intval($params['advanced_strategy']['mode']['snapshot_del_speed']);

        $msg = json_encode($pfMsg);
//                dump($msg);
        $nodeUuid = $nodeInfo['node_uuid'] ?: '';
        $mbResult = $this->service()->unifyVmPlatformService(
            $nodeUuid,
            $hypervisor,
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
     * 获取虚拟机备份任务信息
     * @param string $taskUUID
     * @return array
     */
    public function getBackupTaskAllInfo(string $taskUUID): array
    {
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, bt.ignore_resource_limiting_flag, bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
        vt.agent_uuid, vt.hypervisor_type, vt.level, vt.transport_priority, vt.quiesce_snapshot, vt.valid_data_backup, vt.serial_snapshot_flag, vt.parse_fs_flag, vt.not_backup_swap_file_flag, vt.not_backup_deleted_file_flag, vt.not_backup_partition_gap_flag, vt.pre_create_snap_flag, vt.storage_snapshot_enable_flag, vt.select_conditions, vt.display_mode, vt.detail, vt.parallel_transfer_vm_count, vt.single_vm_parallel_disk_transfer_count, vt.vm_single_disk_parallel_transfer_count,
        brs.strategy_type, brs.number, brs.strategy_mode,
        bts.encrypt_flag, bts.compress_flag, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
        bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.compress_method, bss.encrypted_flag, bss.password_auto_flag, bss.password, bss.encrypt_method, bss.redundant_data_proportiont, bss.data_container_size,
        bsr.storage_type, bsrp.storage_pool_type,
        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy, btsc.backup_integrity_check_inc_error_policy
        from vm_task vt, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss, bd_task bt
        left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
        left join bd_storage_resource_pool bsrp on bt.storage_pool_uuid = bsrp.storage_pool_uuid
        left join bd_task_safe_config btsc on bt.task_uuid = btsc.task_uuid
        where bt.task_uuid = vt.task_uuid
        and bt.strategy_id = brs.strategy_id
        and bt.strategy_id = bts.strategy_id
        and bt.strategy_id = bss.strategy_id
        and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $sqlGFS = "select level1_type, level2_type, retention_num from bd_task_gfs_retention_strategy where task_uuid = ?";
        $dataGFS = $this->dbSelect($sqlGFS, array($taskUUID));
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

        $info = array();
        if ($data) {
            $agentPoolInfo = $this->getJobAgentPoolInfo($taskUUID);
            $select_conditions = trim($data[0]['select_conditions']);
            if (!$select_conditions || 'null' == $select_conditions) {
                $global_select = [];
            } else {
                $global_select = json_decode($select_conditions, true);
                //统一为二维数组
//                if (count($global_select) == count($global_select, 1)) {
//                    $global_select = [$global_select];
//                }
            }
            //自动备份之前的任务这里为0，改为1用于树的加载
            $display_mode = $data[0]['display_mode'] == 0 ? 1 : intval($data[0]['display_mode']);
            // 备份节点传输ip
            $backupServerIp = json_decode($data[0]['backup_server_ip'], true);
            if (!is_array($backupServerIp)) {
                // 老版本数据
                $backupServerIp = [
                    ['node_uuid' => $data[0]['node_uuid'], 'transfer_ip' => $data[0]['backup_server_ip']]
                ];
            }

            // detail
            $detail = $data[0]['detail'];
            $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
            $reset_cbt_flag = $detailArr['error_reset_cbt_flag'] ?? xphp_get_config('app', 'FLAG')['UNSET'];
            $full_backup_reset_cbt = $detailArr['full_backup_reset_cbt_flag'] ?? xphp_get_config('app', 'FLAG')['UNSET'];
            $auto_join_flag = v1_parse_flag_to_bool($detailArr['auto_join_flag']);
            $agent_public_ip = $detailArr['agent_public_ip'] ?? ['chargemode' => '', 'size' => 0];
            $keep_snapshot_flag = $detailArr['keep_snapshot_flag'] ?? xphp_get_config('app', 'FLAG')['UNSET'];
            $highspeed_disk_cbt_flag = $detailArr['highspeed_disk_cbt_flag'] ?? xphp_get_config('app', 'FLAG')['UNSET'];
            $snapshot_timeout = $detailArr['snapshot_timeout'] ?? 3600;
            $source_compression = $detailArr['source_compression'] ?? 'unzip';
            $async_rw_flag = $detailArr['async_rw_flag'] ?? false;
            $agentNetwork = $detailArr['agent_network'] ?? [];
            $agentNetwork = array_column($agentNetwork, null, 'region_uuid');
            $snapshotDelSpeed = $detailArr['snapshot_del_speed'] ?? 0;

            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
                //虚拟化类型
                'hypervisor' => $data[0]['hypervisor_type'],
                //appliance
                'applianceuuid' => $data[0]['agent_uuid'],
                'agentuuid' => $data[0]['agent_uuid'],
                'agent_pool_info' => $agentPoolInfo,
                //保留策略
                'brs' => array(
                    'type' => intval($data[0]['strategy_type']),
                    'strategyMode' => intval($data[0]['strategy_mode']),
                    'number' => intval($data[0]['number']),
                    'GFS' => $thisGFS,
                ),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'storage_type' => $data[0]['storage_type'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $data[0]['storage_pool_type'],
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    'compress' => v1_parse_flag_to_bool($data[0]['compress_flag']),
                    'mode' => $this->getTransportModeToArr($data[0]['transport_priority'], $data[0]['hypervisor_type']),
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                    'source_compression' => $source_compression,
                    'async_rw_flag' => v1_parse_flag_to_bool($async_rw_flag),
                    'agent_network' => $agentNetwork,
                    'parallel_transfer_vm_count' => intval($data[0]['parallel_transfer_vm_count']),
                    'single_vm_parallel_disk_transfer_count' => intval($data[0]['single_vm_parallel_disk_transfer_count']),
                    'vm_single_disk_parallel_transfer_count' => intval($data[0]['vm_single_disk_parallel_transfer_count']),
                ),
                //存储策略
                'bss' => array(
                    'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                    'blocksize' => intval($data[0]['block_size']) / 1024,
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' => base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method']),
                    'data_container_size' => intval($data[0]['data_container_size']),
                    'redundant_data_proportion' => intval($data[0]['redundant_data_proportiont']),
                ),
                //备份模式
                'mode' => array(
                    'snapshot' => $data[0]['level'] == xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['KEEP_SNAPSHOT'],
                    'keepsnapshot' => v1_parse_flag_to_bool($keep_snapshot_flag),
                    'incmode' => $data[0]['level'],
                    'quiescesnapshot' => v1_parse_flag_to_bool($data[0]['quiesce_snapshot']),
                    'cbtmode' => v1_parse_flag_to_bool($data[0]['valid_data_backup']),
                    'resetcbt' => v1_parse_flag_to_bool($reset_cbt_flag),
                    'fullresetcbt' => v1_parse_flag_to_bool($full_backup_reset_cbt),
                    'snapshotmode' => $data[0]['serial_snapshot_flag'],
                    'parsefsmode' => v1_parse_flag_to_bool($data[0]['parse_fs_flag']),
                    'swapmode' => v1_parse_flag_to_bool($data[0]['not_backup_swap_file_flag']),
                    'deletefilemode' => v1_parse_flag_to_bool($data[0]['not_backup_deleted_file_flag']),
                    'gapmode' => v1_parse_flag_to_bool($data[0]['not_backup_partition_gap_flag']),
                    'threadnum' => intval($data[0]['thread_num']),      //线程数量
                    'presnapshot' => v1_parse_flag_to_bool($data[0]['pre_create_snap_flag']),
                    'storagesnapshot' => v1_parse_flag_to_bool($data[0]['storage_snapshot_enable_flag']),
                    'snapshotdelspeed' => intval($snapshotDelSpeed),
                    'highspeeddiskcbt' => v1_parse_flag_to_bool($highspeed_disk_cbt_flag),
                    'snapshottimeout' => intval($snapshot_timeout),
                    'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
                ),
                //虚拟机信息
                'vm_info' => $this->getVMEditInfo($taskUUID, intval($data[0]['display_mode']), $auto_join_flag),
                //时间策略
                'timestrategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                // 'speedInfo' => $this->getSpeedStrategyInfo($taskUUID),
                'speedInfo' => $this->getSpeedStrategy($taskUUID),
                'nosnapshot' => $this->getXenSnapshotFlag($taskUUID),
                'backup_server_ip' => $backupServerIp,
                'transport_ip_segment' => $data[0]['transport_ip_segment'],
                //新增校验策略
                'verifyInfo' => array(
                    // 'ischeck'=>$utils->parseFlagToBool($data[0]['is_integrity_check']),
                    // 'algorithm'=>$data[0]['calibration_algorithm'],
                    'ischeck' => false,
                    'algorithm' => false,
                ),
                //全局筛选条件
                'global_select' => $global_select,
                //虚拟机展示方式
                'display_mode' => $display_mode,
                //自动加入备份开关
                'auto_join_flag' => $auto_join_flag,
                //传输代理公有ip配置
                'agent_public_ip' => $agent_public_ip,
                //安全策略
                'safe_strategy' => [
                    'worm_flag' => v1_parse_flag_to_bool($data[0]['worm_flag']),
                    'worm_protection_time' => $data[0]['worm_protection_time'],
                    'virus_scan_flag' => v1_parse_flag_to_bool($data[0]['virus_scan_flag']),
                    'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true) ?: [
                        [
                            'strategy_type' => 0,
                            'recover_policy' => 0,
                            'interrupt_policy' => 0,
                            'scan_strategy' => 0,
                            'all_timepoints_flag' => 0,
                            'skip_application_group_flag' => 0
                        ]
                    ], // 兼容配置为空的
                    'integrity_check_flag' => v1_parse_flag_to_bool($data[0]['integrity_check_flag']),
                    'integrity_check_config' => [
                        'check_strategy' => $data[0]['integrity_check_strategy'],
                        'full_error_policy' => $data[0]['backup_integrity_check_full_error_policy'],
                        'inc_error_policy' => $data[0]['backup_integrity_check_inc_error_policy'],
                    ]
                ],
                // 重试策略
                'retry_strategy' => ExchangeJobInfo::instance()->getRetryStrategy($taskUUID),
            );
        }
        return $info;
    }

    /**
     * 得到备份修改的虚拟机信息
     * @param string $taskuuid
     * @param int $display_mode
     * @param bool $auto_join_flag
     * @return array
     */
    public function getVMEditInfo(string $taskuuid, int $display_mode, bool $auto_join_flag)
    {
        $info = array();
        $sql = "select vol.object_uuid, vol.vcenter_uuid, vol.type, vol.exclude_vm_uuid_list, vol.object_name, vol.vm_config, 
            vt.dir_path, vt.host_uuid
            from vm_object_list vol 
            left join vm_tree vt on vol.object_uuid = vt.uuid and vol.type = vt.type and vt.display_mode = ?
            where vol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($display_mode, $taskuuid));
        if (empty($data)) {
            $sql = "select vm_uuid as object_uuid, 7 as type, '' as exclude_vm_uuid_list, vcenter_uuid, vm_config, 
                vm_name as object_name, host_uuid, dir_path from vm_machine_list where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
        }
        $Vcenter = VmPlatform::instance();
        foreach ($data as $d) {
            //获取排除的虚拟机
            $exclude_vms = [];
            $exclude_vm_ids = explode(',', trim($d['exclude_vm_uuid_list'], ','));
            if ($exclude_vm_ids && $exclude_vm_ids[0] != '') {
                $exclude_vm_ids_str = implode("','", $exclude_vm_ids);
                $sql = "select uuid, dir_path from vm_tree where uuid in ('" . $exclude_vm_ids_str . "') and type = ? and display_mode = ? and vcenter_uuid = ?";
                $exclude_vms_data = $this->dbSelect($sql, [
                    xphp_get_config('vm', 'VM_TREE_TYPE')['VM'],
                    xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'],
                    $d['vcenter_uuid']
                ]);
                foreach ($exclude_vms_data as $vm) {
                    $exclude_vms[] = [
                        'vm_uuid' => $vm['uuid'],
                        'vm_path' => $vm['dir_path']
                    ];
                }
            }

            //未开启自动备份要排除创建任务后新增的虚拟机
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] != $d['type'] && !$auto_join_flag) {
                $notInTaskVms = $Vcenter->getObjectVmsNotInTask($display_mode, $d['vcenter_uuid'], $d['object_uuid'], $taskuuid);
                foreach ($notInTaskVms as $vm2) {
                    if (!in_array($vm2['uuid'], $exclude_vm_ids)) {
                        $exclude_vms[] = [
                            'vm_uuid' => $vm2['uuid'],
                            'vm_path' => $vm2['dir_path']
                        ];
                    }
                }
            }

            $info[] = array(
                "object_uuid" => $d['object_uuid'],
                "type" => $d['type'],
                'exclude_vms' => $exclude_vms,
                "vcuuid" => $d['vcenter_uuid'],
                "path" => $d['dir_path'] ?: $d['object_name'],
                "vm_config" => json_decode($d['vm_config']),
                "hostuuid" => $d['host_uuid'],
            );
        }

        return $info;
    }

    /**
     * 根据策略id得到时间策略信息
     * @param int $strategyID
     * @return array
     */
    public function getTimeStrategyInfo(int $strategyID): array
    {
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time, full_backup_compensation_flag
                from bd_time_strategy where strategy_id = ?";
        $data = (array) $this->dbSelect($sql, array($strategyID));
        if (!$data) {
            // 手动启动
            return [
                'type' => 'manual',
                'data' => [],
            ];
        }
        $timeStrategyBackupType = $this->getTimeStrategyBackupType($strategyID);
        $info = [
            'type' => $timeStrategyBackupType,
            'data' => [],
        ];

        switch ($timeStrategyBackupType) {
            case 'oncetime':
                $info['data'] = $data[0]['start_time'];
                break;
            case 'strategy':
                $strategyData = array();
                $allBackupMode = array_column($data, 'mode');
                //可能有多个策略类型(每天,每周,每月)
                foreach ($data as $d) {
                    if ($d['roll_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                        $rollInterval = $d['roll_interval'];
                        $endTime = $d['roll_end_time'];
                    } else {
                        $rollInterval = '3600';
                        $endTime = '23:59:59';
                    }
                    $strategyData[] = array(
                        'start_time' => $d['start_time'],
                        'roll_flag' => v1_parse_flag_to_bool($d['roll_flag']),
                        'roll_interval' => v1_sec_to_time($rollInterval),
                        'roll_end_time' => $endTime,
                        'mode' => !in_array(xphp_get_config('task', 'BACKUP_MODE')['FULL'], $allBackupMode)
                            && xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'] == $d['mode']
                            ? xphp_get_config('task', 'BACKUP_MODE')['PINCREMENTAL'] : $d['mode'],  // 没有完全备份就是永久增量
                        'strategy_type' => intval($d['strategy_type']),
                        'days' => $this->parseTimeStrategyDay($d['days']),
                        'frequency' => $this->parseTimeStrategyFrequency($d['strategy_type'], $d['days']),
                        'full_backup_compensation_flag' => v1_parse_flag_to_bool($data[0]['full_backup_compensation_flag']),
                    );
                }
                $info['data'] = $strategyData;
                break;
            default:
                break;
        }
        return $info;
    }

    /**
     * 获取对象下可备份的虚拟机
     * @param $display_mode
     * @param $vcenter_uuid
     * @param $object_uuid
     * @param $task_uuid
     * @return array
     */
    public function getObjectVms($display_mode, $vcenter_uuid, $object_uuid, $task_uuid): array
    {
        $vms = [];
        $sql = "select vt.type, vt.name, vt.uuid, vt.dir_path path, vt.vcenter_uuid vcuuid, 
            vm.vcenter_uuid vcuuid, vm.vm_uuid vmuuid, vm.vm_name vmname, vm.version, vm.host_uuid hostuuid 
            from vm_tree vt left join vm_machine vm on vt.uuid = vm.vm_uuid and vt.vcenter_uuid = vm.vcenter_uuid 
            where vt.display_mode = ? and vt.vcenter_uuid = ? and vt.parent_uuid = ? and vt.uuid != vt.parent_uuid 
            and vt.uuid not in (select vm_uuid from vm_machine_list vml join bd_task bt on vml.task_uuid = bt.task_uuid where bt.task_type = ? and bt.task_uuid != ?) order by vt.name";
        $data = $this->dbSelect($sql, [$display_mode, $vcenter_uuid, $object_uuid, xphp_get_config('task', 'TASKTYPE')['BACKUP'], $task_uuid]);
        foreach ($data as $item) {
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] == $item['type']) {
                $vms[] = $item;
            } else {
                $vms = array_merge($vms, $this->getObjectVms($display_mode, $item['vcuuid'], $item['uuid'], $task_uuid));
            }
        }
        return $vms;
    }

    /**
     * 得到虚拟机备份任务名
     * @param array $params
     * @return string
     */
    public function getVMBackupTaskName(array $params): string
    {
        $type = intval($params['hypervisor_type']);
        $this->paramsCheck($type);
        $taskName = xphp_get_config('vm', 'VMHYPERVISORDES')[$type];
        $taskName .= xphp_get_lang('WEB_PLATFORM_DES_BACKUP');
        return $this->getValidTaskName($taskName);
    }

    /**
     * 获取可用的任务名
     * @param string $taskName 任务名前缀
     * @return string
     */
    public function getValidTaskName(string $taskName): string
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if (empty($data) && empty($data1)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * 得到传输模式到后台的字符串
     * @param string $mode nbd/nbdssl/san/hotadd/数字
     * @return string
     */
    public function getTransportMode(string $mode): string
    {
        if ("san" == $mode) {
            //vmware
            $transportMode = "file:san:nbd:nbdssl:hotadd";  //san传输
        } else if ("nbd" == $mode) {
            $transportMode = "file:nbd:nbdssl:san:hotadd";  //网络传输  nbd
        } else if ("nbdssl" == $mode) {
            $transportMode = "file:nbdssl:nbd:san:hotadd";  //网络加密传输  nbdssl
        } else if ("hotadd" == $mode) {
            $transportMode = "file:hotadd:nbd:nbdssl:san";
        } else {
            //其他
            $transportMode = $mode;
        }
        return $transportMode;
    }

    /**
     * 创建和修改备份任务做检测(vcenter.class.php有调用)
     * @param string $operate 操作描述
     * @param array $vmInfo 本次备份的虚拟机
     * @param string $taskUuid 任务UUID(修改的时候用)
     * @param int $subModuleType 子模块类型
     */
    public function checkTaskLegal($operate, $vmInfo, $taskUuid, $subModuleType)
    {
        $vmCount = $this->getAllTaskCount($vmInfo, $taskUuid);
        //这里暂时只检测在按虚拟机授权的时候虚拟机个数是否足够,不足够直接退出并提示
        $systemLisenceInfo = SystemHandler::instance()->getSystemLisenceInfo();
        $systemLisenceInfo = json_decode($systemLisenceInfo, true);
        if (xphp_get_config('auth', 'LICENSE_BIG_TYPE')['MIX_STORAGE'] != $systemLisenceInfo['extension']['licenseBigType']) {
            // 不是数量授权则放开
            return true;
        }
        if ((xphp_get_config('vm', 'VM_SUB_MODULE')['VM'] == $subModuleType || xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] == $subModuleType)
            && $systemLisenceInfo['vminfo']['type'] != xphp_get_config('auth', 'LISENCE_INFO')['type']['vm']) {
            // 不是按虚拟机个数则放开
            return true;
        }

        if ($systemLisenceInfo['status'] == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            //已授权
            $sql = "select vm_max_num, private_cloud_instance_max_num, public_cloud_instance_max_num from bd_license";
            $bdLicense = $this->dbSelect($sql, [])[0];

            $hyperversionType = xphp_get_config('vm', 'VMHYPERVISORGROUP');
            $notIn = implode(',', array_merge($hyperversionType['privatecloud'], $hyperversionType['publiccloud']));
            $privatecloud = implode(',', $hyperversionType['privatecloud']);
            $publiccloud = implode(',', $hyperversionType['publiccloud']);
            switch ($subModuleType) {
                case xphp_get_config('vm', 'VM_SUB_MODULE')['VM']:
                    $authNum = intval($bdLicense['vm_max_num']);
                    $sql = "select count(distinct vml.machine_id) as total from vm_machine_list vml, bd_task bt, vm_vcenter vv where bt.task_uuid = vml.task_uuid and bt.task_type = ? 
                            and vml.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type not in ({$notIn})";
                    $errorMsg = xphp_get_lang('WEB_VM_LISENCE_USED_ALL');
                    break;
                case xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD']:
                    $authNum = intval($bdLicense['private_cloud_instance_max_num']);
                    $sql = "select count(distinct vml.machine_id) as total from vm_machine_list vml, bd_task bt, vm_vcenter vv where bt.task_uuid = vml.task_uuid and bt.task_type = ? 
                            and vml.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type in ({$privatecloud})";
                    $errorMsg = xphp_get_lang('WEB_PRIVATE_CLOUD_INSTANCE_LICENSE_USED_ALL');
                    break;
                case xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']:
                    $authNum = intval($bdLicense['public_cloud_instance_max_num']);
                    $sql = "select count(distinct vml.machine_id) as total from vm_machine_list vml, bd_task bt, vm_vcenter vv where bt.task_uuid = vml.task_uuid and bt.task_type = ? 
                            and vml.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type in ({$publiccloud})";
                    $errorMsg = xphp_get_lang('WEB_PUBLIC_CLOUD_INSTANCE_LICENSE_USED_ALL');
                    break;
                default:
                    exit($this->muOpResult(false, $operate, 'VM_SUB_MODULE error', 'error'));
            }
            $usedData = $this->dbSelect($sql, [xphp_get_config('task')['TASKTYPE']['BACKUP']])[0];
            // 检查当前任务的vm数量加上已使用数量是否大于授权数量，-1为无限制
            if ($authNum > -1 && $vmCount + $usedData['total'] > $authNum) {
                exit($this->muOpResult(false, $operate, $errorMsg, 'error'));
            }
        } else {
            //未授权
            exit($this->muOpResult(false, $operate, $systemLisenceInfo['statusDes'], 'error'));
        }
        return true;
    }

    //todo:protected方法

    /**
     * 组合存储策略，重载Backup的方法
     * @param array  $storage           存储策略信息
     * @param string $strategyGroupUuid 策略组uuid
     * @return array|string
     */
    public function groupStorageStrategy(array $storage, string $strategyGroupUuid = ''): array
    {
        if ($storage['encrypt_flag'] && !$storage['auto_create_password_flag'] && empty($storage['password'])) {
            // 数据加密检测手动输入的密码不为空
            return $this->muOpResult(false, (new PfOpcode())->getOpcodeDes('BD_TASK_OP_BACKUP_CREATE'), xphp_get_lang('UI_PLATFORM_VERIFI_FAIL_REENTER'));
        }
        $flag = xphp_get_config('app', 'FLAG');
        return array(
            'deduplication_flag' => $storage['deduplication_flag'] ? $flag['SET'] : $flag['UNSET'],
            'block_size' => intval($storage['block_size']) * 1024,
            'blocksize' => intval($storage['block_size']) * 1024,
            'encrypt_flag' => $storage['encrypt_flag'] ? $flag['SET'] : $flag['UNSET'],
            'password_auto_flag' => $storage['auto_create_password_flag'] ? $flag['SET'] : $flag['UNSET'],
            'password' => $storage['password'],
            'compress_flag' => $storage['compress_flag'] ? $flag['SET'] : $flag['UNSET'],
            'compress_method' => $storage['compress_flag'] ? intval($storage['compress_method']) : 0,
            'strategy_group_uuid' => $strategyGroupUuid,
            'encrypt_method' => intval($storage['encrypt_method']),
            'redundant_data_proportion' => intval($storage['redundant_data_proportion']),
            'data_container_size' => intval($storage['data_container_size']),
        );
    }

    /**
     * 得到备份实例数组
     * @param array $vmInfo 实例信息
     * @return array
     */
    protected function groupBackupVM(array $vmInfo): array
    {
        $vmInfoMsg = array();
        foreach ($vmInfo as $vm) {
            $vmConfig = $vm['config'];
            if (empty($vmConfig)) {
                $vmConfig = array(
                    'disk_list' => array()
                );
            }
            $excludeVmList = $vm['exclude_vm_list'] ? array_map(function ($v) {
                return ['vm_uuid' => $v['vm_uuid']];
            }, $vm['exclude_vm_list']) : [];
            $sql = "select name from vm_tree where vcenter_uuid = ? and uuid = ?";
            $vmUuid = $vm['vm_uuid'] ?? $vm['instance_uuid'];
            $name = $this->dbSelect($sql, [$vm['platform_uuid'], $vmUuid])[0]['name'];
            $vmInfoMsg[] = array(
                'object_uuid' => $vmUuid,
                'object_name' => $name,
                'type' => $vm['type'],
                'vcenter_uuid' => $vm['platform_uuid'],
                'dir_path' => $vm['path'],
                'vm_config' => json_encode($vmConfig, JSON_UNESCAPED_UNICODE),
                'exclude_vm_list' => $excludeVmList,
                'log_cache_storage_uuid' => '',
                'log_cache_size' => 2147483648,
            );
        }
        return $vmInfoMsg;
    }

    /**
     * 检测一次性备份的时候其时间是否已过期
     * @param array $timeStrategy
     * @return void
     */
    protected function checkOnceBackupTimeStrategy(array $timeStrategy): void
    {
        if ("oncetime" == $timeStrategy['type']) {
            //获取一次性备份的时间
            $taskCreateTime = strtotime($timeStrategy['datetime']);
            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(
                    false,
                    ('WEB_DB_BACKUP_TIME'),
                    xphp_get_lang('WEB_DB_BACKUP_TIME_TIPS'),
                    'warning'
                )
                );
            }
        }
    }

    /**
     * 监测任务状态是否在停止中
     * @param string $taskUuid
     * @param string $operate
     * @return array
     */
    protected function checkJobIsStopped(string $taskUuid, string $operate): array
    {
        $sql = "select task_status, strategy_id, cache_dir_path from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskStatus = intval($data[0]['task_status']);
        $strategyID = $data[0]['strategy_id'];
        if ($taskStatus != xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
//            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_JOB_EDIT_TIPS'));
        }
        return [
            'strategy_id' => $strategyID,
            'cache_dir_path' => $data[0]['cache_dir_path']
        ];
    }

    /**
     * 得到修改任务的节点和存储UUID信息
     * @param array $nodeInfo 节点信息
     * @return array('nodeuuid','auto_find_sr_flag','storageuuid')
     */
    protected function getModifyJobNodeAndStorageInfo(array $nodeInfo): array
    {
        $info = array(
            'nodeuuid' => $nodeInfo['node_uuid'],
            'node_pool_uuid' => $nodeInfo['node_pool_uuid'],
            'auto_find_sr_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'storageuuid' => $nodeInfo['storage_uuid'],
            'storage_pool_uuid' => $nodeInfo['storage_pool_uuid'],
        );
        if (empty($nodeInfo['node_uuid'])) {
            $info['nodeuuid'] = Node::instance()->getAutoFindNode();
        }
        if ($nodeInfo['storagecheck']) {
            //自动选择存储
            $info['auto_find_sr_flag'] = xphp_get_config('app', 'FLAG')['SET'];
            $info['storageuuid'] = '';
        }
        return $info;
    }

    /**
     * @param array $nodeInfo
     * @param string $taskName
     * @param string $taskUuid
     * @param array $params
     * @param string $cachePath
     * @return bool
     */
    protected function dbUpdateBdTask(array $nodeInfo, string $taskName, string $taskUuid, array $params, string $cachePath): bool
    {
        $emptyCachePath = $params['emptyCachePath'];
        $ignoreResourceLimit = v1_parse_bool_to_flag($params['advanced_strategy']['mode']['ignore_resource_limiting_flag']);
        if ($emptyCachePath) {
            // 存储类型变化则清空缓存路径
            $cachePath  = '';
        }
        if (empty($nodeInfo['storageuuid']) && empty($nodeInfo['storage_pool_uuid'])) {
            //自动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, node_pool_uuid = ?, auto_find_sr_flag = ?, cache_dir_path = ?,
                ignore_resource_limiting_flag = ?, worm_flag = ?, virus_scan_flag = ?, integrity_check_flag = ?, merge_mode = ?  
                 where task_uuid = ?";
            $result = $this->dbExec($sql, array(
                    $taskName,
                    date("Y-m-d H:i:s"),
                    $nodeInfo['nodeuuid'],
                    $nodeInfo['node_pool_uuid'],
                    $nodeInfo['auto_find_sr_flag'],
                    $cachePath,
                    $ignoreResourceLimit,
                    $params['safe_strategy']['worm_flag'],
                    $params['safe_strategy']['virus_scan_flag'],
                    $params['safe_strategy']['integrity_check_flag'],
                    $params['advanced_strategy']['mode']['merge_mode'],
                    $taskUuid
                )
            );
        } else {
            //手动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, node_pool_uuid = ?, auto_find_sr_flag = ?,
                storage_uuid = ?, storage_pool_uuid = ?, cache_dir_path = ?, ignore_resource_limiting_flag = ?,
                worm_flag = ?, virus_scan_flag = ?, integrity_check_flag = ?, merge_mode = ? 
               where task_uuid = ?";
            $result = $this->dbExec($sql, array(
                    $taskName,
                    date("Y-m-d H:i:s"),
                    $nodeInfo['nodeuuid'],
                    $nodeInfo['node_pool_uuid'],
                    $nodeInfo['auto_find_sr_flag'],
                    $nodeInfo['storageuuid'],
                    $nodeInfo['storage_pool_uuid'],
                    $cachePath,
                    $ignoreResourceLimit,
                    $params['safe_strategy']['worm_flag'],
                    $params['safe_strategy']['virus_scan_flag'],
                    $params['safe_strategy']['integrity_check_flag'],
                    $params['advanced_strategy']['mode']['merge_mode'],
                    $taskUuid
                )
            );
        }
        return $result;
    }

    /**
     * @param bool $result
     * @param string $taskName
     * @param string $taskUuid
     * @return bool
     */
    protected function dbUpdateBdBackupTimepoint(bool $result, string $taskName, string $taskUuid): bool
    {
        if (!$result)
            return false;
        $sql = "update bd_backup_timepoint set task_name = ? where task_uuid = ?";
        return $this->dbExec($sql, [$taskName, $taskUuid]);
    }

    /**
     * @param bool $result
     * @param array $params
     * @param string $taskUuid
     * @param int $subType
     * @return bool
     */
    protected function dbUpdateVmMachineList(bool $result, array $params, string $taskUuid, int $subType = 1): bool
    {
        if (!$result)
            return false;
        //全局筛选条件
        $globalSelect = $this->globalSelectHandler($params['src_info']['global_select']);
        $selectType = $globalSelect['select_type'];
        $selectValue = $globalSelect['select_value'];
        //显示方式
        $displayMode = $params['src_info']['display_mode'] ? intval($params['src_info']['display_mode']) : 1;

        $oriSql = "select vm_uuid from vm_machine_list where task_uuid = ?";
        $data = (array)$this->dbSelect($oriSql, [$taskUuid]);
        $oriVmUuids = array_column($data, 'vm_uuid');

        $sql = "delete from vm_machine_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskUuid));
        $sql = "delete from vm_object_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskUuid));
        $sql = "insert vm_machine_list (task_uuid, vcenter_uuid, vm_uuid, vm_name, version, host_uuid, dir_path, vm_config)
                values (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql2 = "insert vm_object_list (task_uuid, object_uuid, vcenter_uuid, type, exclude_vm_uuid_list, object_name, vm_config, dir_path) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $vmInfo = $params['src_info']['vm_info'] ?? $params['src_info']['instance_info'];
        //获取单个选择的虚拟机
        $checkedVmUuids = [];
        foreach ($vmInfo as $vm) {
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] != $vm['type']) {
                continue;
            }
            $checkedVmUuids[] = $vm['vm_uuid'] ?? $vm['instance_uuid'];
        }

        // 记录vm_backup_license_records最终保留的虚拟机的uuid
        $licenseVmUuids = [];

        //记录对象下已插入表的用于去重
        $insertedVmUuids = [];
        foreach ($vmInfo as $vm) {
            $vmConfig = $vm['vm_config'] ?? $vm['config'];
            if (empty($vmConfig)) {
                $vmConfig = array(
                    'disk_list' => array()
                );
            }
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] == $vm['type']) {
                //获取虚拟机名称、版本、主机uuid
                //获取实例名称、版本、区域uuid
                $machineSql = "select vm_name, version, host_uuid from vm_machine where vm_uuid = ? and vcenter_uuid = ?";
                $machineData = $this->dbSelect($machineSql, [$vm['vm_uuid'] ?? $vm['instance_uuid'], $vm['platform_uuid']])[0];

                //虚拟机同时更新vm_machine_list和vm_object_list
                $sqlParams = array(
                    $taskUuid,
                    $vm['platform_uuid'],
                    $vm['vm_uuid'] ?? $vm['instance_uuid'],
                    $machineData['vm_name'],
                    $machineData['version'],
                    $machineData['host_uuid'],
                    $vm['path'],
                    json_encode($vmConfig, JSON_UNESCAPED_UNICODE)
                );
                $result = $result && $this->dbExec($sql, $sqlParams);

                // 是新增的则插入授权记录
                $licenseVmUuids[] = $vmUuid = $vm['vm_uuid'] ?? $vm['instance_uuid'];
                if (!in_array($vmUuid, $oriVmUuids)) {
                    $result = $this->dbUpdateVmBackupLicenseRecords($result, $taskUuid, $subType, $vmUuid, $machineData['host_uuid'], $vm['platform_uuid']);
                }
            } else {
                //其他对象递归获取下面的虚拟机并更新vm_machine_list
                $vms = $this->getObjectVms($displayMode, $vm['vcenter_uuid'] ?? $vm['platform_uuid'],$vm['vm_uuid'] ?? $vm['instance_uuid'], $taskUuid);
                //遍历插入
                foreach ($vms as $vm2) {
                    //已单选的、已插入的不插入
                    if (in_array($vm2['vmuuid'], $checkedVmUuids) || in_array($vm2['vmuuid'], $insertedVmUuids)) {
                        continue;
                    }
                    // 有过滤规则
                    if ($selectValue) {
                        $continueFlag = false;
                        if ('0' === $selectType) {
                            //前缀排除的 匹配到则排除
                            foreach ($selectValue as $item) {
                                if (0 === strpos($vm2['vmname'], $item)) {
                                    $continueFlag = true;
                                    break;
                                }
                            }
                        } elseif ('1' === $selectType) {
                            //前缀匹配时 匹配到则不排除
                            $continueFlag = true;
                            foreach ($selectValue as $item) {
                                if (0 === strpos($vm2['vmname'], $item)) {
                                    $continueFlag = false;
                                    break;
                                }
                            }
                        } elseif ('2' === $selectType) {
                            //关键词排除的 匹配到则排除
                            foreach ($selectValue as $item) {
                                if (false !== strpos($vm2['vmname'], $item)) {
                                    $continueFlag = true;
                                    break;
                                }
                            }
                            if ($continueFlag) {
                                continue;
                            }
                        } elseif ('3' === $selectType) {
                            //关键词匹配时 匹配到则不排除
                            $continueFlag = true;
                            foreach ($selectValue as $item) {
                                if (false !== strpos($vm2['vmname'], $item)) {
                                    $continueFlag = false;
                                    break;
                                }
                            }
                        }
                        if ($continueFlag) {
                            // 被排除的不插入
                            continue;
                        }
                    } else {
                        //在排除列表的不插入
                        if (in_array($vm2['vmuuid'], array_column($vm['exclude_vm_list'], 'vm_uuid'))) {
                            continue;
                        }
                    }
                    $sqlParams = array(
                        $taskUuid,
                        $vm2['vcuuid'],
                        $vm2['vmuuid'],
                        $vm2['vmname'],
                        $vm2['version'],
                        $vm2['hostuuid'],
                        $vm2['path'],
                        json_encode($vmConfig, JSON_UNESCAPED_UNICODE)
                    );
                    $re = $this->dbExec($sql, $sqlParams);
                    if ($re) {
                        $insertedVmUuids[] = $vm2['vmuuid'];
                    }
                    $result = $result && $re;

                    // 是新增的则插入授权记录
                    $licenseVmUuids[] = $vm2['vmuuid'];
                    if (!in_array($vm2['vmuuid'], $oriVmUuids)) {
                        $result = $this->dbUpdateVmBackupLicenseRecords($result, $taskUuid, $subType, $vm2['vmuuid'], $vm2['hostuuid'], $vm2['vcuuid']);
                    }
                }
            }
            $exclude_vm_uuids = $vm['exclude_vm_list']
                ? implode(',', array_column($vm['exclude_vm_list'], 'vm_uuid')) : '';
            $pathArr = explode('/', $vm['path']);
            $name = $pathArr[count($pathArr) - 1];
            $sql2Params = array(
                $taskUuid,
                $vm['vm_uuid'] ?? $vm['instance_uuid'],
                $vm['platform_uuid'],
                $vm['type'],
                $exclude_vm_uuids,
                $name,
                json_encode($vmConfig, JSON_UNESCAPED_UNICODE),
                $vm['path']
            );
            $result = $result && $this->dbExec($sql2, $sql2Params);
        }

        // 移除的虚拟机同步删除授权记录表
        $licenseVmUuids = array_unique($licenseVmUuids);
        $delLicenseStr = implode("','", array_diff($oriVmUuids, $licenseVmUuids));
        $delLicenseSql = "delete from vm_backup_license_records where task_uuid = '" . $taskUuid . "' and vm_uuid in ('" . $delLicenseStr ."')";
        return $result && $this->dbExec($delLicenseSql);
    }

    /**
     * @param bool $result
     * @param string $strategyID
     * @param array $timeStrategy
     * @return bool
     */
    protected function dbUpdateBdTimeStrategy(bool $result, string $strategyID, array $timeStrategy): bool
    {
        if (!$result)
            return false;
        $sql = "delete from bd_time_strategy where strategy_id = ? ";
        $result = $result && $this->dbExec($sql, array($strategyID));
        if ("oncetime" == $timeStrategy['type']) {
            //一次性策略
            $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time)
                    values (?, ?, ?, ?)";
            $sqlParams = array(
                $strategyID,
                xphp_get_config('task', 'BACKUP_MODE')['FULL'],
                xphp_get_config('task', 'STRATEGY_TYPE')['ONCE'],
                $timeStrategy['datetime']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        } elseif ("strategy" == $timeStrategy['type']) {
            //时间策略
            if (!empty($timeStrategy['full_info'])) {
                //完全策略
                $modeType = xphp_get_config('task', 'BACKUP_MODE')['FULL'];

                //单独处理完全备份修改
                $sql = "select strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                        from bd_time_strategy where strategy_id = ?";
                $data = $this->dbSelect($sql, array($strategyID));
                if (!empty($data[0])) {
                    //如果数据库有,比较内容,如果一样,不做操作,如果不一样,删除后插入
                    //不一样,删除后再重新插入
                    $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                    $result = $result && $this->dbExec($sql, array($strategyID, $modeType));
                }
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['full_info']);
            } else {
                $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                $result = $result && $this->dbExec($sql, array($strategyID, xphp_get_config('task', 'BACKUP_MODE')['FULL']));
            }
            if (!empty($timeStrategy['incr_info'])) {
                //增量策略
                $modeType = xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'];
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['incr_info']);
            }
            if (!empty($timeStrategy['diff_info'])) {
                //差异策略
                $modeType = xphp_get_config('task', 'BACKUP_MODE')['DIFFERENTIAL'];
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['diff_info']);
            }
            if (!empty($timeStrategy['pincr_info'])) {
                //永久增量
                $modeType = xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'];
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['pincr_info']);
            }
        } elseif ('manual' == $timeStrategy['type']) {  // 手动启动
            //手动启动不需要插入时间策略
            $result = true;
        }
        return $result;
    }

    /**
     * @param bool $result
     * @param string $strategyID
     * @param array $timeStrategy
     * @return bool
     */
    protected function dbUpdateBdStrategy(bool $result, string $strategyID, array $timeStrategy): bool
    {
        if (!$result)
            return false;
        $sql = "UPDATE bd_strategy SET
                       next_mode = ?, next_strategy_type = ? , next_start_time = ?, time_strategy_backup_type = ?
                WHERE strategy_id = ? ";
        $timeStrategyBackupType = xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE')[$timeStrategy['type']];
        return $this->dbExec($sql, [0, 0, '0000-00-00 00:00:00', $timeStrategyBackupType, $strategyID]);
    }

    /**
     * @param bool $result
     * @param string $strategyID
     * @param array $reservedStrategy
     * @return bool
     */
    protected function dbUpdateBdReservedStrategy(bool $result, string $strategyID, array $reservedStrategy): bool
    {
        if (!$result)
            return false;
        $sql = "update bd_reserved_strategy set strategy_type = ?, number = ?, strategy_mode = ? where strategy_id = ?";
        $sqlParams = array(
            $reservedStrategy['reserved_type'],
            $reservedStrategy['value'],
            $reservedStrategy['strategyMode'],
            $strategyID
        );
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 更新GFS的策略信息
     * @param array  $info       为GFS的js插件封装传出的结构体
     * @param bool   $modifyFlag 是否有修改
     * @param string $taskUuid   任务UUID
     * @param array  $vmList     实例信息
     * @return bool
     */
    protected function updateGFSStrategy(array $info, bool $modifyFlag, string $taskUuid, array $vmList): bool
    {
        if (!$modifyFlag) {
            //未做修改则直接返回
            return true;
        }
        $result = true;
        $deleteList = array(1, 2, 3); //未被勾选的策略集合
        if (!empty($info)) {
            //修改GFS等待表,统一先删除后再添加 并且等待状态全部置成2,所以如果以前有1状态的就不管了 重新来
            //先删除所有此任务有关的等待表
            $sqlWaitDelete = "delete from bd_gfs_entity_waiting_map where task_uuid = ?";
            $resultWaitDelete = $this->dbExec($sqlWaitDelete, array($taskUuid));
            //修改勾选的GFS策略信息
            foreach ($info as $oneGFS) {
                $level1type = intval($oneGFS['gfs_reserved_type']);
                //删除完以后再添加
                foreach ($vmList as $eachVm) {
                    $sqlAdd = "insert into bd_gfs_entity_waiting_map(task_uuid, entity_uuid, level1_type, waiting_flag) 
                        values(?,?,?,?)";
                    $sqlAddParams = array($taskUuid, $eachVm['vm_uuid'] ?? $eachVm['instance_uuid'], $level1type, 2);
                    $resultAdd = $this->dbExec($sqlAdd, $sqlAddParams);
                    if (!$resultAdd) {
                        return false;
                    }
                }
                //---
                unset($deleteList[$level1type - 1]);
                //先检查数据库是否有 有就修改 没有就添加
                $checkSql = "select id from bd_task_gfs_retention_strategy where task_uuid = ? and level1_type = ?";
                $checkResult = $this->dbSelect($checkSql, array($taskUuid, $level1type));
                if (!empty($checkResult)) {
                    //修改bd_task_gfs_retention_strategy
                    $sql = "update bd_task_gfs_retention_strategy set level2_type = ?, retention_num = ? 
                        where task_uuid = ? and level1_type = ?";
                    $sqlParams = array(
                        $oneGFS['gfs_reserved_start'],
                        $oneGFS['gfs_reserved_value'],
                        $taskUuid,
                        $level1type
                    );
                } else {
                    $sql = "insert into bd_task_gfs_retention_strategy(task_uuid,level1_type,level2_type,retention_num) 
                        values(?,?,?,?)";
                    $sqlParams = array(
                        $taskUuid,
                        $level1type,
                        $oneGFS['gfs_reserved_start'],
                        $oneGFS['gfs_reserved_value']
                    );
                }
                $result1 = $this->dbExec($sql, $sqlParams);
                if (!$result1) {
                    return false;
                }
            }
        } else {
            //如果传入的为空则清空所有GFS有关
            $deleteStrategy = "delete from bd_task_gfs_retention_strategy where task_uuid = ?";
            $deleteMap = "delete from bd_gfs_entity_waiting_map where task_uuid = ?";
            $resultStrategy = $this->dbExec($deleteStrategy, array($taskUuid));
            $resultMap = $this->dbExec($deleteMap, array($taskUuid));
            if (!$resultStrategy || !$resultMap) {
                return false;
            }
        }
        //开始删除未被勾选的集合
        if (!empty($deleteList)) {
            $thisList = implode(',', $deleteList);
            $deleteSql = "delete from bd_task_gfs_retention_strategy 
                where task_uuid = ? and level1_type in(" . $thisList . ') ';
            $result2 = $this->dbExec($deleteSql, array($taskUuid));
            if (!$result2) {
                return false;
            }
        }
        return $result;
    }

    /**
     * @param bool $result
     * @param string $strategyID
     * @param array $transportStrategy
     * @return bool
     */
    protected function dbUpdateBdTransportStrategy(bool $result, string $strategyID, array $transportStrategy): bool
    {
        if (!$result)
            return false;
        $encryptFlag = v1_parse_bool_to_flag($transportStrategy['encrypt']);
        $encryptMethod = $transportStrategy['encrypt']
            ? intval($transportStrategy['encrypt_method']) : 0;
        $reconnectTimes = intval($transportStrategy['reconnect_times']);
        $reconnectInterval = intval($transportStrategy['reconnect_interval']);
        $sql = "update bd_transport_strategy set encrypt_flag = ?, encrypt_method = ?, 
            reconnect_times = ?, reconnect_interval = ? where strategy_id = ?";
        $sqlParams = array($encryptFlag, $encryptMethod, $reconnectTimes, $reconnectInterval, $strategyID);
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * @param bool $result
     * @param string $strategyID
     * @param array $storageStrategy
     * @return bool
     */
    protected function dbUpdateBdStorageStrategy(bool $result, string $strategyID, array $storageStrategy): bool
    {
        if (!$result)
            return false;
        $sql = "update bd_storage_strategy set deduplication_flag = ?, block_size = ?, compressed_flag = ?,
                encrypted_flag = ?, password_auto_flag = ?, password = ?, compress_method = ?, encrypt_method = ? where strategy_id = ?";
        //AES-CBC 256位加密密码
        $password = base64_decode($storageStrategy['password']);
        //判断修改密码是否没有变化
        $sqlPass = "select password from bd_storage_strategy where strategy_id = ? ";
        $data = $this->dbSelect($sqlPass, array($strategyID));
        $oldPass = "";
        if (!empty($data)) {
            $oldPass = $data[0]['password'];
        }
        $passwordAES = v1_pt_pass_encrypt($password);
        if ($oldPass == $password) {
            $passwordAES = $password;
        }
        if ($storageStrategy['auto_create_password_flag']) {
            // 自动生成密码
            $passwordAES = v1_pt_pass_encrypt('/mnt/vm_vinfs');
        }
        if (!$storageStrategy['encrypt_flag']) {
            $passwordAES = '';
        }
        $sqlParams = array(
            v1_parse_bool_to_flag($storageStrategy['deduplication_flag']),
            intval($storageStrategy['block_size']) * 1024,
            v1_parse_bool_to_flag($storageStrategy['compress_flag']),
            v1_parse_bool_to_flag($storageStrategy['encrypt_flag']),
            v1_parse_bool_to_flag($storageStrategy['auto_create_password_flag']),
            $passwordAES,
            intval($storageStrategy['compress_method']),
            intval($storageStrategy['encrypt_method']),
            $strategyID,
        );
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * @param bool $result
     * @param array $speedStrategy
     * @param string $taskUuid
     * @param string $taskName
     * @return bool
     */
    protected function dbUpdateBdSpeedStrategy(bool $result, array $speedStrategy, string $taskUuid, string $taskName): bool
    {
        $sql = "SELECT * FROM bd_task_speed_limit_strategy WHERE task_uuid = ? ";
        $speedData = $this->dbSelect($sql, [$taskUuid]);
        if ($speedData) {  // 删除之前存在的策略
            $sql = "DELETE FROM bd_global_speed_limit_strategy WHERE strategy_uuid = ? AND is_global = 0 ";
            $result = $result && $this->dbExec($sql, [$speedData[0]['strategy_uuid']]);
            $sql = "DELETE FROM bd_task_speed_limit_strategy WHERE task_uuid = ? ";
            $result = $result && $this->dbExec($sql, [$taskUuid]);
        }
        $speedList = $this->groupTaskSpeedGlobalList($speedStrategy);
        if ($speedList) {
            $customStrategyEmpty = false;  // 自定义限速策略是否为空，为空需要删除bd_task_speed_limit_strategy里面的记录
            //如果是自定义的话，那么需要在 bd_global_speed_limit_strategy  表里先加记录
            if (!$speedList['is_global']) {
                $strategy_uuid = xphp_uuid();
                // 自定义
                $sql = "insert into bd_global_speed_limit_strategy 
                    (strategy_uuid,strategy_name,strategy_type,extra_info,is_global,user_uuid,
                     create_time,days,start_time,end_time,speed_limited_value) 
                    values (?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?)";
                $sqlParams = [
                    $strategy_uuid,
                    $taskName,
                    intval($speedList['strategy_type']),
                    $speedList['extra_info'],
                    0,
                    xphp_get_user_info()['userUuid'],
                    date('Y-m-d H:i:s')
                ];
                if (!$speedList['speed_limit_info']) {
                    $customStrategyEmpty = true;
                }
                foreach ($speedList['speed_limit_info'] as $speed) {
                    $sqlParams2 = array_merge($sqlParams, [$speed['days']]);
                    foreach ($speed['time_list'] as $speeds) {
                        $sqlParams3 = array_merge($sqlParams2, [$speeds['start_time'], $speeds['end_time'], intval($speeds['speed_limited_value'])]);
                        $result = $result && $this->dbExec($sql, $sqlParams3);
                    }
                }
            } else {
                $strategy_uuid = $speedList['strategy_uuid'];
            }
            if ($speedList['is_global'] || !$customStrategyEmpty) {
                $sql = "INSERT INTO bd_task_speed_limit_strategy(strategy_uuid, strategy_name, task_uuid, task_priority) VALUES(?, ?, ?, ?) ";
                $result = $result && $this->dbExec($sql, [$strategy_uuid, $taskName, $taskUuid, $speedList['task_priority']]);
            }
        }
        return $result;
    }

    /**
     * @param bool $result
     * @param array $safeStrategy
     * @param string $taskUuid
     * @return bool
     */
    protected function dbUpdateBdTaskSafeConfig(bool $result, array $safeStrategy, string $taskUuid): bool
    {
        if (!$result) return false;
        $sql = "update bd_task_safe_config set worm_protection_time = ?, virus_scan_config_list = ?, integrity_check_strategy = ?,
                backup_integrity_check_full_error_policy = ?, backup_integrity_check_inc_error_policy = ?, recovery_integrity_check_error_policy = ? where task_uuid = ?";
        $sqlParams = array(
            $safeStrategy['worm_protection_time'],
            $safeStrategy['virus_scan_config_list'],
            $safeStrategy['integrity_check_config']['check_strategy'],
            $safeStrategy['integrity_check_config']['full_error_policy'],
            $safeStrategy['integrity_check_config']['inc_error_policy'],
            $safeStrategy['integrity_check_config']['recovery_error_policy'],
            $taskUuid
        );
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * @param bool $result
     * @param array $retryStrategy
     * @param string $taskUuid
     * @return bool
     */
    protected function dbUpdateBdRetryStrategy(bool $result, array $retryStrategy, string $taskUuid): bool
    {
        if (!$result) return false;
        $sql = "update bd_retry_strategy set network_retry_times = ?, network_retry_interval = ?, op_retry_times = ?,
                op_retry_interval = ?, task_retry_object = ?, task_retry_times = ?, task_retry_interval = ? where task_uuid = ?";
        $sqlParams = array(
            intval($retryStrategy['network_retry_times']),
            intval($retryStrategy['network_retry_interval']),
            intval($retryStrategy['op_retry_times']),
            intval($retryStrategy['op_retry_interval']),
            intval($retryStrategy['task_retry_object']),
            intval($retryStrategy['task_retry_times']),
            intval($retryStrategy['task_retry_interval']),
            $taskUuid
        );
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * @param bool $result
     * @param string $taskUuid
     * @param int $subType
     * @param string $vmUuid
     * @param string $hostUuid
     * @param string $vcenterUuid
     * @return bool
     */
    protected function dbUpdateVmBackupLicenseRecords(
        bool $result, string $taskUuid, int $subType, string $vmUuid, string $hostUuid, string $vcenterUuid): bool
    {
        if (!$result) return false;
        $sql = "insert into vm_backup_license_records(vm_uuid, host_uuid, vcenter_uuid, mode, used_flag, task_uuid)
            values(?, ?, ?, ?, 0, ?)";
        return $this->dbExec($sql, [$vmUuid, $hostUuid, $vcenterUuid, $subType, $taskUuid]);
    }

    //todo:private方法

    /**
     * 得到除当前任务的所有虚拟机个数
     * @param array $vmInfo 本次备份的虚拟机
     * @param string $taskUuid
     */
    private function getAllTaskCount($vmInfo, $taskUuid)
    {
        $vmUuids = [];
        foreach ($vmInfo as $item) {
            $itemUuid = $item['instance_uuid'] ?? $item['vm_uuid'];
            // 判断是否为单个虚拟机
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] == $item['type']) {
                $vmUuids[] = $itemUuid;
            } else {
                $thisVmUuids = $this->getNotBackupVmUuids($item['platform_uuid'], $itemUuid, $taskUuid);
                // 减去粒度下排除的
                $excludeVmUuids = array_column($item['exclude_vm_list'], 'vm_uuid');
                $thisVmUuids = array_diff($thisVmUuids, $excludeVmUuids);
                $vmUuids = array_merge($vmUuids, $thisVmUuids);
            }
        }
        $vmUuids = array_unique($vmUuids);
        $vmNum = count($vmUuids);
        if (empty($taskUuid)) {
            //如果是新建任务,直接返回本次虚拟机个数
            return $vmNum;
        }
        //如果是修改任务,需要减掉修改前的虚拟个数,才是新增的虚拟机个数
        $sql = "select count(machine_id) as vm_num from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $oldNum = $data[0]['vm_num'];
        return $vmNum - $oldNum;
    }

    /**
     * 获取对象下可备份虚拟机的uuid
     * @param string $vcenterUuid
     * @param string $objectUuid
     * @param string $taskUuid
     * @return array
     */
    private function getNotBackupVmUuids(string $vcenterUuid, string $objectUuid, string $taskUuid = ''): array
    {
        $vmUuids = [];
        if ($taskUuid) {
            // 修改任务时要统计当前任务的
            $sql = "select vt.vcenter_uuid, vt.uuid, vt.type from vm_tree vt 
                where vt.vcenter_uuid = ? and vt.parent_uuid = ? and vt.uuid != vt.parent_uuid 
                and vt.uuid not in (select vm_uuid from vm_machine_list where vcenter_uuid = ? and task_uuid != ?)";
            $data = $this->dbSelect($sql, [$vcenterUuid, $objectUuid, $vcenterUuid, $taskUuid]);
        } else {
            $sql = "select vt.vcenter_uuid, vt.uuid, vt.type from vm_tree vt 
                where vt.vcenter_uuid = ? and vt.parent_uuid = ? and vt.uuid != vt.parent_uuid 
                and vt.uuid not in (select vm_uuid from vm_machine_list where vcenter_uuid = ?)";
            $data = $this->dbSelect($sql, [$vcenterUuid, $objectUuid, $vcenterUuid]);
        }
        foreach ($data as $item) {
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] == $item['type']) {
                $vmUuids[] = $item['uuid'];
            } else {
                $vmUuids = array_merge($vmUuids, $this->getNotBackupVmUuids($item['vcenter_uuid'], $item['uuid'], $taskUuid));
            }
        }
        return $vmUuids;
    }

    /**
     * XenServer相关模块的高速模式和增量模式选择
     * @param int $hypervisor
     * @param boolean $speedMode
     * @param int $incMode
     * @return int
     */
    private function getBackupSnapshotMode(int $hypervisor, bool $speedMode, int $incMode): int
    {
        //下列是支持增量模式的虚拟化类型
        $hypervisor = intval($hypervisor);
        if (
            $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XENSERVER']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_RHV_KVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_OVIRT_KVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_OLVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XCP_NG']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_KVM_ZVIRT']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XFUSION_KVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HOSTVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_RED_VIRT']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_ROSA_VIRT']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_LENOVO_AIO']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_VOLC']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_KSPHERE']
        ) {
            //XenServer
            return $incMode;
        }
        return $this->getVMTaskLevel($speedMode);
    }

    /**
     * 得到是否保留快照标志
     * @param bool $snapshotFlag
     * @return int
     */
    private function getVMTaskLevel(bool $snapshotFlag): int
    {
        if ($snapshotFlag) {
            return xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['KEEP_SNAPSHOT'];
        }
        return xphp_get_config('app', 'XENSERVER_BACKUP_LEVEL')['NORMAL'];
    }

    /**
     * 得到从数据库获取的传输模式,修改任务用
     * @param string $transpoertMode
     * @param int $hypervisor
     * @return mixed
     */
    private function getTransportModeToArr($transpoertMode, $hypervisor)
    {
        if (
            in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])
            || in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['huawei'])
        ) {
            $modeArr = explode(":", $transpoertMode);
            //因为现在存入数据库只有三种情况,所以直接取第二项就是设置的模式
            //nbd    file:nbd:nbdssl:san:hotadd
            //nbdssl file:nbdssl:nbd:san:hotadd
            //san    file:san:nbd:nbdssl:hotadd
            return $modeArr[1];
        } else {
            return $transpoertMode;
        }
    }

    /**
     * 获取xen版本判断有无静默快照flag
     * @param string $taskuuid
     * @return bool
     */
    public function getXenSnapshotFlag($taskuuid): bool
    {
        $sql = "select distinct vcenter_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!$data) {
            $sql = "select distinct vcenter_uuid from vm_object_list where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
        }
        $vcenter = VmPlatform::instance();
        return $vcenter->getSnapshotFlag($data[0]['vcenter_uuid']);
    }

    /**
     * 获取备份配置
     * @param int $hypervisorType
     * @return array
     */
    public function getBackupConfigs(int $hypervisorType): array
    {
        return [
            'initConfig' => $this->getBackupInitConfigs($hypervisorType),
            'linkageConfig' => $this->getBackupLinkageConfigs($hypervisorType),
        ];
    }

    /**
     * 获取备份配置 - 初始化配置
     * @param int $hypervisorType
     * @return array
     */
    private function getBackupInitConfigs(int $hypervisorType): array
    {
        $hypervisors = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        switch ($hypervisorType) {
            // VMware
            case $hypervisors['VM_HYPERVISOR_TYPE_VMWARE']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'vmwaretransport_mode',
                    // 传输模式默认值
                    'transport_mode_value' => 'nbd',
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => true,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // 显示传输压缩
                    'transfer_compress' => true,
                    // 传输压缩取值
                    'transfer_compress_value' => 'unzip',
                    // 显示异步传输
                    'async_transfer' => true,
                    // 异步传输是否开启
                    'async_transfer_open' => false,
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => true,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalctbtips',
//                    // 显示高速模式
//                    'highspeed_mode' => false,
//                    // 高速模式是否开启
//                    'highspeed_mode_open' => true,
                    // 显示静默快照
                    'silent_snapshot' => true,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
//                    // 显示CBT模式
//                    'cbt_mode' => true,
//                    // 禁用CBT模式选择
//                    'cbt_mode_disable' => false,
//                    // CBT模式是否开启
//                    'cbt_mode_open' => true,
                    // 显示修复CBT
                    'reset_cbt' => true,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // CloudView SRM/SVM (VMware OEM)
            case $hypervisors['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'transport_mode',
                    // 传输模式默认值
                    'transport_mode_value' => 'nbd',
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => true,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // 显示传输压缩
                    'transfer_compress' => true,
                    // 传输压缩取值
                    'transfer_compress_value' => 'unzip',
                    // 显示异步传输
                    'async_transfer' => true,
                    // 异步传输是否开启
                    'async_transfer_open' => false,
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => true,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalctbtips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => true,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // HyperV
            case $hypervisors['VM_HYPERVISOR_TYPE_HYPERV']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => '',
                    // 传输模式默认值
                    'transport_mode_value' => '1',
                    // 传输模式默认值描述
                    'transport_mode_value_des' => xphp_get_lang('UI_BACKUP_TRANSPORT_NBD_VMWARE'),
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 2048,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'hypervincmodeltips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // XenServer / XCP-NG
            case $hypervisors['VM_HYPERVISOR_TYPE_XENSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_XCP_NG']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'xentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodeltips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // InCloud Xen / vGate / WinServer / D-Server / V-Server
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
            case $hypervisors['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
            case $hypervisors['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'xentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => true,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // Huawei KVM / FusionOne Compute KVM
            case $hypervisors['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_XFUSION_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'huaweikvmtransport_mode',
                    // 传输模式默认值
                    'transport_mode_value' => 5,
                    // 传输模式是否锁定
                    'transport_mode_disable' => true,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => true,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // 支持并行传输
                    'parallel_transport' => true,
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => true,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelctbtips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => true,
                    // 显示快照删除速度
                    'snapshot_del_speed' => true,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // H3C
            case $hypervisors['VM_HYPERVISOR_TYPE_H3C_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => '',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // H3C CAS_CVD
            case $hypervisors['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => '',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => true,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'h3ccvdincmodeltips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => true,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // ICS
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => true,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'leixentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => true,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodeltips',
                    // 显示静默快照
                    'silent_snapshot' => true,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // ICS vvdk
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_VOLC']:
            case $hypervisors['VM_HYPERVISOR_TYPE_KSPHERE']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'icsvvdktransport_mode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => true,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelctbtips',
                    // 显示静默快照
                    'silent_snapshot' => true,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // Sangfor HCI
            case $hypervisors['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => '',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // Sangfor SCP
            case $hypervisors['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'sangforvvdktransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => true,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelctbtips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // OpenStack系
            case $hypervisors['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_EASYSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_CTSI_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_AW_CLOUD']:
            case $hypervisors['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'openstacktransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 4,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelhighctbtips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => true,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // NeoKylin / OsEasyVServer / EastedVServer / Proxmox VE
            case $hypervisors['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_PROXMOX']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'leixentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // Lenovo AIO
            case $hypervisors['VM_HYPERVISOR_TYPE_LENOVO_AIO']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'leixentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => true,
                    // 增量模式支持高速
                    'inc_mode_option_high' => false,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 3,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelctbtips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // Zstack / Xsky / CloudView KVM / WinHong KVM
            case $hypervisors['VM_HYPERVISOR_TYPE_ZSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_ZSTACK_ZSPHERE']:
            case $hypervisors['VM_HYPERVISOR_TYPE_NEXAVM_NSSV']:
            case $hypervisors['VM_HYPERVISOR_TYPE_NEXAVM_NCSSV']:
            case $hypervisors['VM_HYPERVISOR_TYPE_XSKY']:
            case $hypervisors['VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_WINHONG_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'leixentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 2,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // Xsky XHERE
            case $hypervisors['VM_HYPERVISOR_TYPE_XHERE']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => false,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'leixentransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 2,
                    // 传输模式是否锁定
                    'transport_mode_disable' => true,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // SmartX
            case $hypervisors['VM_HYPERVISOR_TYPE_SMARTX_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_ARCFRA_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'smartxtransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 2,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => false,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，oVirt系/华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => false,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => true,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => true,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => false,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodelnormalhightips',
                    // 显示静默快照
                    'silent_snapshot' => true,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            // RedHat Ovirt系
            case $hypervisors['VM_HYPERVISOR_TYPE_RHV_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OLVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
            case $hypervisors['VM_HYPERVISOR_TYPE_HOSTVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_RED_VIRT']:
            case $hypervisors['VM_HYPERVISOR_TYPE_ROSA_VIRT']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OVIRT_KVM']:
                $config = [
                    // 显示传输策略tab
                    'tab_transfer' => true,
                    // 显示高级配置tab
                    'tab_high' => true,
                    // 是否支持差异备份，ics-vvdk不支持
                    'diff_backup' => false,
                    // --传输策略--
                    // 传输模式ID
                    'transport_mode' => 'redhattransmode',
                    // 传输模式默认值
                    'transport_mode_value' => 1,
                    // 传输模式是否锁定
                    'transport_mode_disable' => false,
                    // 显示传输代理
                    'transport_agent' => false,
                    // 传输代理是否开启
                    'transport_agent_open' => false,
                    // 传输代理取值
                    'transport_agent_value' => '',
                    // 显示加密传输开关
                    'transport_encrypt' => true,
                    // 加密传输是否开启
                    'transport_encrypt_open' => false,
                    // 加密算法取值：1-RSA，2-SM2
                    'transport_encrypt_value' => 1,
                    // 显示备份系统IP，华为KVM支持
                    'system_ip' => false,
                    // 备份系统IP取值
                    'system_ip_value' => '',
                    // 显示数据传输网段
                    'transport_segment' => true,
                    // 数据传输网段取值
                    'transport_segment_value' => '',
                    // --高级配置--
                    // CBT模式下是否支持串/并行快照，oVirt系列不支持
                    'cbt_sp_snapshot' => false,
                    // 快照模式取值：1-串行，2-并行
                    'cbt_sp_snapshot_value' => 1,
                    // CBT模式下是否支持串行提前创建快照，oVirt系列不支持
                    'cbt_pre_snapshot' => false,
                    // 提前创建快照是否开启
                    'cbt_pre_snapshot_open' => false,
                    // 是否支持nfs存储访问
                    'nfs_storage' => false,
                    // 显示数据块大小
                    'block_size' => false,
                    // 数据块大小取值
                    'block_size_value' => 1024,
                    // 显示增量模式
                    'inc_mode' => true,
                    // 锁定增量模式
                    'inc_mode_disable' => false,
                    // 增量模式支持高速
                    'inc_mode_option_high' => true,
                    // 增量模式支持CBT
                    'inc_mode_option_cbt' => true,
                    // 增量模式取值
                    'inc_mode_value' => 2,
                    // 增量模式提示的类名
                    'inc_mode_tips_class' => 'incmodeltips',
                    // 显示静默快照
                    'silent_snapshot' => false,
                    // 显示一致性快照，华为KVM、OpenStack系支持
                    'consistent_snapshot' => false,
                    // 显示修复CBT
                    'reset_cbt' => false,
                    // 修复CBT取值
                    'reset_cbt_value' => 2,
                    // 显示高速磁盘CBT
                    'highspeed_disk_cbt' => false,
                    // 显示合并模式
                    'merge_mode' => true,
                    // 合并模式取值
                    'merge_mode_value' => 1,
                ];
                break;
            default:
                $config = [];
        }
        return $config;
    }

    /**
     * 获取备份配置 - 联动配置
     * @param int $hypervisorType
     * @return array
     */
    private function getBackupLinkageConfigs(int $hypervisorType): array
    {
        $hypervisors = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        switch ($hypervisorType) {
            // VMware
            case $hypervisors['VM_HYPERVISOR_TYPE_VMWARE']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => true,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => true,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => true,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => true,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => false,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输 !!!
                    'transport_agent_encrypt' => true,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
                // CloudView SRM/SVM (VMware OEM)
            case $hypervisors['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => true,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => true,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => true,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => true,
                    // 选中自适应传输显示传输代理 !!!
                    'transport_adaptive_agent' => true,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => false,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输 !!!
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
                // HyperV
            case $hypervisors['VM_HYPERVISOR_TYPE_HYPERV']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => true,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => true,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => true,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => true,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => true,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => true,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => true,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => true,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => true,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
                // XenServer / XCP-NG
            case $hypervisors['VM_HYPERVISOR_TYPE_XENSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_XCP_NG']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段 !!!
                    'transport_nbd_segment' => true,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中网络传输（nbd）显示加密传输 !!!
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // InCloud Xen / vGate / WinServer / D-Server / V-Server
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
            case $hypervisors['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
            case $hypervisors['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段 !!!
                    'transport_nbd_segment' => true,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中网络传输（nbd）显示加密传输 !!!
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值 !!!
                    'transport_adaptive_incmode_value' => 3,
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值 !!!
                    'incmode_cbt_transmode_value' => 5,
                ];
                break;
            // Huawei KVM / FusionOne Compute KVM
            case $hypervisors['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_XFUSION_KVM']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式 !!!
                    'transport_adaptive_incmode_disable' => true,
                    // 选中自适应传输增量模式锁定的值 !!!
                    'transport_adaptive_incmode_value' => 3,
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式 !!!
                    'incmode_cbt_transmode_disable' => true,
                    // 增量模式选中CBT后传输模式锁定的值 !!!
                    'incmode_cbt_transmode_value' => 5,
                ];
                break;
            // H3C / H3C CAS_CVD
            case $hypervisors['VM_HYPERVISOR_TYPE_H3C_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']:
            // ICS
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式 !!!
                    'transport_adaptive_incmode_disable' => true,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // ICS vvdk
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_VOLC']:
            case $hypervisors['VM_HYPERVISOR_TYPE_KSPHERE']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理 !!!
                    'transport_proxy_agent' => true,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => false,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输 !!!
                    'transport_proxy_encrypt' => true,
                    // 选中自适应传输是否锁定增量模式 !!!
                    'transport_adaptive_incmode_disable' => true,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输 !!!
                    'transport_agent_encrypt' => true,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // Sangfor HCI
            case $hypervisors['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理
                    'transport_proxy_agent' => false,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输
                    'transport_proxy_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // Xsky XHERE
            case $hypervisors['VM_HYPERVISOR_TYPE_XHERE']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理
                    'transport_proxy_agent' => false,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => false,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输
                    'transport_proxy_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // Sangfor SCP
            case $hypervisors['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']:
            // SmartX
            case $hypervisors['VM_HYPERVISOR_TYPE_SMARTX_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_ARCFRA_KVM']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => false,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理 !!!
                    'transport_proxy_agent' => true,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => false,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输 !!!
                    'transport_proxy_encrypt' => true,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // OpenStack系
            case $hypervisors['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_EASYSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_CTSI_OPENSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_AW_CLOUD']:
            case $hypervisors['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段
                    'transport_nbd_segment' => true,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => true,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理 !!!
                    'transport_proxy_agent' => true,
                    // 选中网络传输（nbd）显示加密传输 !!!
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输
                    'transport_proxy_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // NeoKylin / OsEasyVServer / EastedVServer / Proxmox VE / Lenovo AIO
            case $hypervisors['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
            case $hypervisors['VM_HYPERVISOR_TYPE_PROXMOX']:
            case $hypervisors['VM_HYPERVISOR_TYPE_LENOVO_AIO']:
            // Zstack / Xsky / CloudView KVM / WinHong KVM
            case $hypervisors['VM_HYPERVISOR_TYPE_ZSTACK']:
            case $hypervisors['VM_HYPERVISOR_TYPE_ZSTACK_ZSPHERE']:
            case $hypervisors['VM_HYPERVISOR_TYPE_NEXAVM_NSSV']:
            case $hypervisors['VM_HYPERVISOR_TYPE_NEXAVM_NCSSV']:
            case $hypervisors['VM_HYPERVISOR_TYPE_XSKY']:
            case $hypervisors['VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_WINHONG_KVM']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段 !!!
                    'transport_nbd_segment' => true,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => false,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理
                    'transport_proxy_agent' => false,
                    // 选中网络传输（nbd）显示加密传输 !!!
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输
                    'transport_proxy_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => false,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => '',
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => false,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => '',
                ];
                break;
            // RedHat Ovirt系
            case $hypervisors['VM_HYPERVISOR_TYPE_RHV_KVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OLVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
            case $hypervisors['VM_HYPERVISOR_TYPE_HOSTVM']:
            case $hypervisors['VM_HYPERVISOR_TYPE_RED_VIRT']:
            case $hypervisors['VM_HYPERVISOR_TYPE_ROSA_VIRT']:
            case $hypervisors['VM_HYPERVISOR_TYPE_OVIRT_KVM']:
                $config = [
                    // 选中网络传输（nbd）显示数据传输网段 !!!
                    'transport_nbd_segment' => true,
                    // 选中网络加密传输（nbd-ssl）显示数据传输网段
                    'transport_nbdssl_segment' => false,
                    // 选中SAN传输显示数据传输网段
                    'transport_san_segment' => false,
                    // 选中hotadd传输显示数据传输网段
                    'transport_hotadd_segment' => false,
                    // 选中自适应传输（ImageIO）显示数据传输网段
                    'transport_adaptive_segment' => true,
                    // 选中网络传输（nbd）显示传输代理
                    'transport_nbd_agent' => false,
                    // 选中网络加密传输（nbd-ssl）显示传输代理
                    'transport_nbdssl_agent' => false,
                    // 选中SAN传输显示传输代理
                    'transport_san_agent' => false,
                    // 选中hotadd传输显示传输代理
                    'transport_hotadd_agent' => false,
                    // 选中自适应传输显示传输代理
                    'transport_adaptive_agent' => false,
                    // 选中传输代理显示传输代理
                    'transport_proxy_agent' => true,
                    // 选中网络传输（nbd）显示加密传输
                    'transport_nbd_encrypt' => true,
                    // 选中网络加密传输（nbd-ssl）显示加密传输
                    'transport_nbdssl_encrypt' => false,
                    // 选中SAN传输显示加密传输
                    'transport_san_encrypt' => false,
                    // 选中hotadd传输显示加密传输
                    'transport_hotadd_encrypt' => false,
                    // 选中自适应传输显示加密传输
                    'transport_adaptive_encrypt' => false,
                    // 选中传输代理显示加密传输
                    'transport_proxy_encrypt' => false,
                    // 选中自适应传输是否锁定增量模式
                    'transport_adaptive_incmode_disable' => true,
                    // 选中自适应传输增量模式锁定的值
                    'transport_adaptive_incmode_value' => 3,
                    // 开启传输代理显示加密传输
                    'transport_agent_encrypt' => false,
                    // 增量模式选中CBT是否锁定传输模式
                    'incmode_cbt_transmode_disable' => true,
                    // 增量模式选中CBT后传输模式锁定的值
                    'incmode_cbt_transmode_value' => 5,
                ];
                break;
            default:
                $config = [];
        }
        return $config;
    }

    /**
     * 处理虚拟机过滤的参数
     * @param array $paramGlobalSelect
     * @return array
     */
    protected function globalSelectHandler(array $paramGlobalSelect): array
    {
        if (!$paramGlobalSelect) {
            $globalSelect = ['select_type' => '1', 'select_value' => ['']];
        } else {
            $paramGlobalSelect['select_type'] = (string)$paramGlobalSelect['select_type'];
            if (!$paramGlobalSelect['select_value']) {
                $paramGlobalSelect['select_value'] = [''];
            }
            $globalSelect = $paramGlobalSelect;
        }
        return $globalSelect;
    }
}