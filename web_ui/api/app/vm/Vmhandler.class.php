<?php
/*******************************************
 ** 虚拟机备份恢复数据处理类
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-1-13 下午11:20:41
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
require_once XPHP_PATH . 'utils/BLLHandler.class.php';
class Vmhandler extends BLLHandler
{

    /**
     * 创建备份任务
     * @param unknown $params
     */
    public function createBackupJob($params)
    {
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($task_name);

        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
//        $this->checkTaskLegal($operate, $params['srcInfo']['vminfo'], null);

        //租户内检查虚拟机个数是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['VM'], $params['srcInfo']['vminfo'], "");
        }
        //如果是华为CBR 需要设置路径
        $storagepath = "";
        if ($params['highInfo']['node']['storagetype'] == Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']) {
            //需要从数据库中获取存储库的id
            $sql = "select storage_config from  bd_storage_resource where storage_uuid = ?";
            $sqlParams = array($params['highInfo']['node']['storageuuid']);
            $data = $this->dbSelect($sql, $sqlParams);
            $configinfo = json_decode($data[0]['storage_config'], true);
            $storagepath = $configinfo['vault_id'];
        }
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $params['strategygroupuuid']);
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve'], $params['strategygroupuuid']);
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer'], $params['strategygroupuuid']);
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'], $params['strategygroupuuid']);
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);

        $backup_type = $params['backupInfo']['type']; // 时间策略备份方式
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $backup_type
        );
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['BACKUP'];

        if (!$nodeInfo['node_uuid']) {
            //没有节点可用
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_NODE_NOT_FIND_NODE'], 'error');
        }


        $utils = Xphp::instance('Utils');
        //private params
        //急速备份模式
        //         $pfMsg['backup_level'] = $this->getVMTaskLevel($params['highInfo']['mode']['snapshotcheck']);
        $pfMsg['backup_level'] = $this->getBackupSnapshotMode(
            $params['srcInfo']['vmType'],
            $params['highInfo']['mode']['snapshotcheck'],
            $params['highInfo']['mode']['incmode']
        );
        //静默快照
        $pfMsg['quiesce_snapshot'] = $utils->parseBoolToFlag($params['highInfo']['mode']['silentsnapshotcheck']);
        //CBT模式
        $pfMsg['valid_data_backup'] = $utils->parseBoolToFlag($params['highInfo']['mode']['cbtcheck']);
        $pfMsg['reset_cbt_flag'] = (string)$utils->parseBoolToFlag(false);
        $pfMsg['error_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(false);
        $pfMsg['full_backup_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(false);
        if (Xphp::$_config['RESET_CBT_LEVEL']['FULL_BACKUP'] == $params['highInfo']['mode']['resetcbtLevel']) {
            $pfMsg['error_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(true);
            $pfMsg['full_backup_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(true);
        } elseif (Xphp::$_config['RESET_CBT_LEVEL']['ERROR'] == $params['highInfo']['mode']['resetcbtLevel']) {
            $pfMsg['error_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(true);
        }
        // 高速磁盘CBT
        $pfMsg['highspeed_disk_cbt_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['highspeedDiskCbtCheck']);
        //V-CBT
        $pfMsg['parse_fs_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['parsefscheck']);
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['swapcheck']);
        //排除删除文件块
        $pfMsg['not_backup_deleted_file_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['deletefilecheck']);
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['gapcheck']);
        //快照模式
        $pfMsg['serial_snapshot_flag'] = intval($params['highInfo']['mode']['snapshottype']);
        $pfMsg['pre_create_snap_flag'] = intval($params['highInfo']['mode']['presnapshot']);
        //存储快照
        $pfMsg['storage_snapshot_enable_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['storagesnapshot']);
        //细粒度保留字段
        $pfMsg['grain_level'] = 0;

        //线程数量
        $pfMsg['thread_num'] = intval($params['highInfo']['mode']['threadnum']);

        $pfMsg['speed_limit_strategy_list'] = $this->groupTaskSpeedList($params['speedInfo'], $params['strategygroupuuid']);

        // 全局限速策略配置
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);

        //如果是租户内部的用户操作，检查设置速度是否超出租户配置限制速度
//         if(!empty($_SESSION['tenantuuid'])){
//             $this->pCheckSpeedLimit($params['speedInfo'], $operate);
//         }

        //appliance
        $pfMsg['appliance_uuid'] = $params['applianceuuid'];
        $pfMsg['agent_uuid'] = $params['applianceuuid'];
        $pfMsg['agent_pool_uuid'] = $params['appliance_pool_uuid'];
        //display mode
        $pfMsg['display_mode'] = $params['srcInfo']['display_mode'] ?? Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
        //自动加入备份开关
        $pfMsg['auto_join_flag'] = (string) $utils->parseBoolToFlag($params['srcInfo']['auto_join_flag']);
        //全局筛选条件
        $pfMsg['global_select'] = $params['srcInfo']['global_select'] ?: [];

        $submodule_type = intval($params['srcInfo']['vmType']);
        //有效数据备份
        //	    $pfMsg['valid_data_backup'] = $params['highInfo']['store']['valid'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'];
        $pfMsg['filter_criteria'] = $this->groupBackupVM($params['srcInfo']['vminfo']);
        $pfMsg['transport_priority'] = $this->getTransportMode($params['highInfo']['transfer']['mode'], $submodule_type);
        //全局策略uuid
        $pfMsg['strategy_group_uuid'] = $params['strategygroupuuid'];

        //备份系统节点IP
        $pfMsg['backup_server_ip'] = $params['backup_server_ip'];
        //指定网段
        $pfMsg['transport_ip_segment'] = $params['transport_ip_segment'];
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $params['highInfo']['reserve']['gfs_strategy_item_list'];
        //新增存储路径和校验
        $pfMsg['storage_location'] = $storagepath;
        $pfMsg['calibreate_strategy_list'] = array(
            "int_create_calibration_algorithm" => $utils->parseBoolToFlag($params['verifyInfo']['ischeck']), //是否生成数据校验
            "int_calibration_algorithm" => $params['verifyInfo']['verifytype'] //选择校验算法
        );
        //cbr任务需要，此处传空
        $pfMsg['extension_info'] = null;
        // var_dump("pfmsg---",json_encode($pfMsg));
        // return;
        //disk select
        //         $pfMsg['vm_config'] = $this ->groupBackupDisk($params['srcInfo']['disklist']);
        $msg = json_encode($pfMsg);
        $mbResult = $this->mbVMMsg($nodeInfo['node_uuid'], $submodule_type, $opName, $msg);
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
     * @param unknown $params
     */
    public function editBackupJob($params)
    {

        $hypervisor = $params['srcInfo']['vmType'];
        $taskName = htmlspecialchars_decode($params['taskName']);
        $taskuuid = $params['taskuuid'];
        $strategygroupuuid = $params['strategygroupuuid'];
        $this->paramsCheck($taskName, $taskuuid);
        $operate = Xphp::$_lang['UI_BACKUP_VM_EDIT_TASK'];
//        $this->checkTaskLegal($operate, $params['srcInfo']['vminfo'], $taskuuid);
        //租户内检查虚拟机个数是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['VM'], $params['srcInfo']['vminfo'], $taskuuid);
        }
        //如果是租户内部的用户操作，检查设置速度是否超出租户配置限制速度
//         if(!empty($_SESSION['tenantuuid'])){
//             $this->pCheckSpeedLimit($params['speedInfo'], $operate);
//         }
        $utils = Xphp::instance('Utils');

        //优先检测一次性备份的时候其时间是否已过期
        $timeStrategy = $params['backupInfo'];
        if ("oncetime" == $timeStrategy['type']) {
            //获取一次性备份的时间
            $taskCreateTime = strtotime($timeStrategy['datetime']);
            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_BACKUP_TIME'], Xphp::$_lang['WEB_DB_BACKUP_TIME_TIPS'], 'warning'));
            }
        }
        //监测任务状态是否在停止中
        $sql = "select task_status, strategy_id, cache_dir_path from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskStatus = intval($data[0]['task_status']);
        $strategyID = $data[0]['strategy_id'];
        $cachePath = $data[0]['cache_dir_path'];
        if ($taskStatus != Xphp::$_config['TASKSTATUS']['STOPPED']) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_JOB_EDIT_TIPS']);
        }
        $nodeInfo = $this->getModifyJobNodeAndStorageInfo($params['highInfo']['node']);
        $this->dbBeginTransaction();
        //更新任务表         bd_task
        if ($params['emptyCachePath']) {
            // 存储类型变化则清空缓存路径
            $cachePath  = '';
        }
        if (empty($nodeInfo['storageuuid'])) {
            //自动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, auto_find_sr_flag = ?, cache_dir_path = ? 
                 where task_uuid = ?";
            $result = $this->dbExec(
                $sql,
                array(
                    $taskName,
                    date("Y-m-d H:i:s"),
                    $nodeInfo['nodeuuid'],
                    $nodeInfo['auto_find_sr_flag'],
                    $cachePath,
                    $taskuuid
                )
            );
        } else {
            //手动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, auto_find_sr_flag = ?,
                storage_uuid = ?, cache_dir_path = ?  where task_uuid = ?";
            $result = $this->dbExec(
                $sql,
                array(
                    $taskName,
                    date("Y-m-d H:i:s"),
                    $nodeInfo['nodeuuid'],
                    $nodeInfo['auto_find_sr_flag'],
                    $nodeInfo['storageuuid'],
                    $cachePath,
                    $taskuuid
                )
            );
        }

        //更新时间点 bd_backup_timepoint
        $sql = "update bd_backup_timepoint set task_name = ? where task_uuid = ?";
        $result = $result && $this->dbExec($sql, [$taskName, $taskuuid]);

        //更新线程数量
        $sql = "update bd_task set thread_num = ?, strategy_group_uuid = ?, backup_server_ip = ?, transport_ip_segment= ? where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array(intval($params['highInfo']['mode']['threadnum']), $strategygroupuuid, $params['backup_server_ip'], $params['transport_ip_segment'], $taskuuid));
        //更新虚拟机任务表 vm_task
        $sql = "select display_mode from vm_task where task_uuid = ?";
        $oldDisplayMode = $this->dbSelect($sql, [$taskuuid])[0]['display_mode'];
        $newDisplayMode = 0 == $oldDisplayMode ? Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'] : $oldDisplayMode;
        $sql = "update vm_task set level = ?, quiesce_snapshot = ?, transport_priority = ?, valid_data_backup = ?, serial_snapshot_flag = ?, 
                   parse_fs_flag = ?, not_backup_swap_file_flag = ?, not_backup_deleted_file_flag = ?, not_backup_partition_gap_flag = ?, 
                   agent_uuid = ?, display_mode = ?, select_conditions = ?, pre_create_snap_flag = ?, storage_snapshot_enable_flag = ?, detail = ?,
                   agent_pool_uuid = ? where task_uuid = ?";
        //         $level = $this->getVMTaskLevel($params['highInfo']['mode']['snapshotcheck']);
        $level = $this->getBackupSnapshotMode(
            $params['srcInfo']['vmType'],
            $params['highInfo']['mode']['snapshotcheck'],
            $params['highInfo']['mode']['incmode']
        );
        $quiesceSnapshot = $utils->parseBoolToFlag($params['highInfo']['mode']['silentsnapshotcheck']);
        $transportMode = $this->getTransportMode($params['highInfo']['transfer']['mode'], intval($params['srcInfo']['vmType']));
        //CBT模式
        $cbtMode = $utils->parseBoolToFlag($params['highInfo']['mode']['cbtcheck']);

        //V-CBT
        $parsefsMode = $utils->parseBoolToFlag($params['highInfo']['mode']['parsefscheck']);
        //排除交换文件块
        $swapMode = $utils->parseBoolToFlag($params['highInfo']['mode']['swapcheck']);
        //排除删除文件块
        $deletefileMode = $utils->parseBoolToFlag($params['highInfo']['mode']['deletefilecheck']);
        //排除分区间隙
        $gapMode = $utils->parseBoolToFlag($params['highInfo']['mode']['gapcheck']);
        $presnapshot = $utils->parseBoolToFlag($params['highInfo']['mode']['presnapshot']);
        //创建存储快照
        $storagesnapshot = $utils->parseBoolToFlag($params['highInfo']['mode']['storagesnapshot']);

        //虚拟化类型为FLEX_CLOUD或者OPENSTACK或者FLEX_HCS时，并且传输模式不是网络传输，清掉代理
        if (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack']) && $transportMode != 3) {
            $agentUuid = '';
            $agentPoolUuid = '';
        } else {
            $agentUuid = $params['applianceuuid'];
            $agentPoolUuid = $params['appliance_pool_uuid'];
        }
        //全局筛选条件
        $globalSelect = $params['srcInfo']['global_select'] ?: [['select_type' => 1, 'select_value' => '']];
        $selectType = $globalSelect[0]['select_type'];
        $selectValue = $globalSelect[0]['select_value'];
        $globalSelectJson = json_encode($globalSelect);
        //快照模式
        $snapshotMode = intval($params['highInfo']['mode']['snapshottype']);
        // 快照模式为并行时，默认关闭提前创建快照
        if (2 == $snapshotMode) {
            $presnapshot = Xphp::$_config['FLAG']['UNSET'];
        }
        //显示方式
        $displayMode = intval($params['srcInfo']['display_mode']);
        //任务详情
        $detailSql = "select detail from vm_task where task_uuid = ?";
        $detail = $this->dbSelect($detailSql, [$taskuuid])[0]['detail'];
        $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
        $detailArr['error_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(false);
        $detailArr['full_backup_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(false);
        if (Xphp::$_config['RESET_CBT_LEVEL']['FULL_BACKUP'] == $params['highInfo']['mode']['resetcbtLevel']) {
            $detailArr['error_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(true);
            $detailArr['full_backup_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(true);
        } elseif (Xphp::$_config['RESET_CBT_LEVEL']['ERROR'] == $params['highInfo']['mode']['resetcbtLevel']) {
            $detailArr['error_reset_cbt_flag'] = (string)$utils->parseBoolToFlag(true);
        }
        $detailArr['auto_join_flag'] = (string) $utils->parseBoolToFlag($params['srcInfo']['auto_join_flag']);
        // 高速磁盘CBT
        $detailArr['highspeed_disk_cbt_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['highspeedDiskCbtCheck']);
        $result = $result && $this->dbExec(
            $sql,
            array(
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
                $agentPoolUuid,
                $taskuuid
            )
        );

        //更新虚拟机列表 vm_machine_list 或 vm_object_list
        $sql = "delete from vm_machine_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "delete from vm_object_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "insert vm_machine_list (task_uuid, vcenter_uuid, vm_uuid, vm_name, version, host_uuid, dir_path, vm_config)
                values (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql2 = "insert vm_object_list (task_uuid, object_uuid, vcenter_uuid, type, exclude_vm_uuid_list, object_name, vm_config, dir_path) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $vmInfo = $params['srcInfo']['vminfo'];
        //获取单个选择的虚拟机
        $checkedVmUuids = [];
        foreach ($vmInfo as $vm) {
            if (Xphp::$_config['VM_TREE_TYPE']['VM'] != $vm['type']) {
                continue;
            }
            $checkedVmUuids[] = $vm['vmuuid'];
        }
        //记录对象下已插入表的用于去重
        $insertedVmUuids = [];
        foreach ($vmInfo as $vm) {
            $vmConfig = $vm['vm_config'];
            if (empty($vmConfig)) {
                $vmConfig = array(
                    'disk_list' => array()
                );
            }
            if (Xphp::$_config['VM_TREE_TYPE']['VM'] == $vm['type']) {
                //虚拟机同时更新vm_machine_list和vm_object_list
                $sqlParams = array(
                    $taskuuid,
                    $vm['vcuuid'],
                    $vm['vmuuid'],
                    htmlspecialchars_decode($vm['vmname']),
                    $vm['version'],
                    $vm['hostuuid'],
                    htmlspecialchars_decode($vm['path']),
                    json_encode($vmConfig, JSON_UNESCAPED_UNICODE)
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            } else {
                //其他对象递归获取下面的虚拟机并更新vm_machine_list
                $vms = $this->getObjectVms($displayMode, $vm['vcuuid'], $vm['vmuuid'], $taskuuid);
                //遍历插入
                foreach ($vms as $vm2) {
                    //在排除列表的不插入
                    if (in_array($vm2['vmuuid'], array_column($vm['exclude_vm_list'], 'vm_uuid'))) {
                        continue;
                    }
                    //已单选的、已插入的不插入
                    if (in_array($vm2['vmuuid'], $checkedVmUuids) || in_array($vm2['vmuuid'], $insertedVmUuids)) {
                        continue;
                    }
                    //前缀排除的
                    if ('0' === $selectType && $selectValue && 0 === strpos($vm2['vmname'], $selectValue)) {
                        continue;
                    }
                    //前缀匹配时前缀不符合的
                    if ('1' === $selectType && $selectValue && 0 !== strpos($vm2['vmname'], $selectValue)) {
                        continue;
                    }
                    $sqlParams = array(
                        $taskuuid,
                        $vm2['vcuuid'],
                        $vm2['vmuuid'],
                        htmlspecialchars_decode($vm2['vmname']),
                        $vm2['version'],
                        $vm2['hostuuid'],
                        htmlspecialchars_decode($vm2['path']),
                        json_encode($vmConfig, JSON_UNESCAPED_UNICODE)
                    );
                    $re = $this->dbExec($sql, $sqlParams);
                    if ($re) {
                        $insertedVmUuids[] = $vm2['vmuuid'];
                    }
                    $result = $result && $re;
                }
            }
            $exclude_vm_uuids = $vm['exclude_vm_list'] ? implode(',', array_column($vm['exclude_vm_list'], 'vm_uuid')) : '';
            $sql2Params = array($taskuuid, $vm['vmuuid'], $vm['vcuuid'], $vm['type'], $exclude_vm_uuids, htmlspecialchars_decode($vm['vmname']),
                json_encode($vmConfig, JSON_UNESCAPED_UNICODE), htmlspecialchars_decode($vm['path']));
            $result = $result && $this->dbExec($sql2, $sql2Params);
        }
        //更新时间策略表 bd_time_strategy
        $sql = "delete from bd_time_strategy where strategy_id = ? ";
        $result = $result && $this->dbExec($sql, array($strategyID));

        $timeStrategy = $params['backupInfo'];
        if ("oncetime" == $timeStrategy['type']) {
            //一次性策略
            $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time)
                    values (?, ?, ?, ?)";
            $sqlParams = array(
                $strategyID,
                Xphp::$_config['BACKUP_MODE']['FULL'],
                Xphp::$_config['STRATEGY_TYPE']['ONCE'],
                $timeStrategy['datetime']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        } elseif ("strategy" == $timeStrategy['type']) {
            //时间策略
            if (!empty($timeStrategy['fullInfo'])) {
                //完全策略
                $modeType = Xphp::$_config['BACKUP_MODE']['FULL'];

                //单独处理完全备份修改
                $sql = "select strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                        from bd_time_strategy where strategy_id = ?";
                $data = $this->dbSelect($sql, array($strategyID));
                if (empty($data[0])) {
                    //如果数据库没有,直接插入,
                    $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);

                } else {
                    //如果数据库有,比较内容,如果一样,不做操作,如果不一样,删除后插入
                    $strategyInfo = $timeStrategy['fullInfo'];
                    $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
                    //不一样,删除后再重新插入
                    $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                    $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));

                    $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                }
            } else {
                $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));
            }
            if (!empty($timeStrategy['incrInfo'])) {
                //增量策略
                $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['incrInfo']);
            }
            if (!empty($timeStrategy['diffInfo'])) {
                //差异策略
                $modeType = Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'];
                $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['diffInfo']);
            }
            if (!empty($timeStrategy['pIncrInfo'])) {
                //永久增量
                $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['pIncrInfo']);
            }
        } elseif ('manual' == $timeStrategy['type']) {  // 手动启动
            //手动启动不需要插入时间策略
        }
        // 更新bd_strategy表
        $sql = "UPDATE bd_strategy SET
                       next_mode = ?, next_strategy_type = ? , next_start_time = ?, time_strategy_backup_type = ?
                WHERE strategy_id = ? ";
        $timeStrategyBackupType = Xphp::$_config['TIME_STRATEGY_BACKUP_TYPE'][$timeStrategy['type']];
        $result = $result && $this->dbExec($sql, [0, 0, '0000-00-00 00:00:00', $timeStrategyBackupType, $strategyID]);

        $highInfo = $params['highInfo'];
        //更新保留策略表 bd_reserved_strategy
        $sql = "update bd_reserved_strategy set strategy_type = ?, number = ?, strategy_mode = ? where strategy_id = ?";
        $sqlParams = array($highInfo['reserve']['type'], $highInfo['reserve']['value'], $highInfo['reserve']['strategyMode'], $strategyID);
        $result = $result && $this->dbExec($sql, $sqlParams);
        //更新GFS策略
        $result = $result && $this->updateGFSStrategy($highInfo['reserve']['gfs_strategy_item_list'], $highInfo['reserve']['gfs_strategy_item_list_old_flag'], $taskuuid, $params['srcInfo']['vminfo']);


        //更新传输策略表 bd_transport_strategy
        $sql = "update bd_transport_strategy set encrypt_flag = ?, encrypt_method = ?,
            reconnect_times = ?, reconnect_interval = ? where strategy_id = ?";
        $sqlParams = array(
            $utils->parseBoolToFlag($highInfo['transfer']['encrypt']),
            $highInfo['transfer']['encrypt_method'],
            $highInfo['transfer']['reconnect_times'],
            $highInfo['transfer']['reconnect_interval'],
            $strategyID
        );
        $result = $result && $this->dbExec($sql, $sqlParams);


        //更新存储策略表 bd_storage_strategy
        $sql = "update bd_storage_strategy set deduplication_flag = ?, block_size = ?, compressed_flag = ?,
                encrypted_flag = ?, password_auto_flag = ?, password = ?, compress_method = ?, encrypt_method = ? where strategy_id = ?";

        //AES-CBC 256位加密密码
        $password = base64_decode($highInfo['store']['password']);
        if ($highInfo['store']['password_auto_flag']) {
            //固定密码
            $password = "/mnt/vm_vinfs";
        }
        //判断修改密码是否没有变化
        $sqlPass = "select password from bd_storage_strategy where strategy_id = ? ";
        $data = $this->dbSelect($sqlPass, array($strategyID));
        $oldPass = "";
        if (!empty($data)) {
            $oldPass = $data[0]['password'];
        }
        $passwordAES = $utils->ptPassEncrypt($password);
        if ($oldPass == $password) {
            $passwordAES = $password;
        }

        if (!$highInfo['store']['encrypt']) {
            $passwordAES = "";
        }

        $sqlParams = array(
            $utils->parseBoolToFlag($highInfo['store']['deduplication']),
            intval($highInfo['store']['blocksize']) * 1024,
            $utils->parseBoolToFlag($highInfo['store']['compress']),
            $utils->parseBoolToFlag($highInfo['store']['encrypt']),
            $utils->parseBoolToFlag($highInfo['store']['password_auto_flag']),
            $passwordAES,
            $highInfo['store']['compress_method'],
            $highInfo['store']['encrypt_method'],
            $strategyID
        );
        $result = $result && $this->dbExec($sql, $sqlParams);

        //限速策略
        $sql = "SELECT * FROM bd_task_speed_limit_strategy WHERE task_uuid = ? ";
        $speedData = $this->dbSelect($sql, [$taskuuid]);
        if ($speedData) {  // 删除之前存在的策略
            $sql = "DELETE FROM bd_global_speed_limit_strategy WHERE strategy_uuid = ? AND is_global = 0 ";
            $result = $result && $this->dbExec($sql, [$speedData[0]['strategy_uuid']]);
            $sql = "DELETE FROM bd_task_speed_limit_strategy WHERE task_uuid = ? ";
            $result = $result && $this->dbExec($sql, [$taskuuid]);
        }
        $speedList = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        if ($speedList) {
            $customStrategyEmpty = false;  // 自定义限速策略是否为空，为空需要删除bd_task_speed_limit_strategy里面的记录
            //如果是自定义的话，那么需要在 bd_global_speed_limit_strategy  表里先加记录
            if (!$speedList['is_global']) {
                $strategy_uuid = $utils->uuid();
                // 自定义
                $sql = "insert into bd_global_speed_limit_strategy (strategy_uuid,strategy_name,strategy_type,extra_info,is_global,user_uuid,create_time,days,start_time,end_time,speed_limited_value) values (?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?)";
                $sqlParams = [
                    $strategy_uuid,
                    $taskName,
                    intval($speedList['strategy_type']),
                    $speedList['extra_info'],
                    0,
                    Xphp::$_user['useruuid'],
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
                $result = $result && $this->dbExec($sql, [$strategy_uuid, $taskName, $taskuuid, $speedList['task_priority']]);
            }
        }

        //更新数据校验策略表 bd_integrity_check_strategy
//        $verifyInfo = $params['verifyInfo'];
//        $sql = "update bd_integrity_check_strategy set is_integrity_check = ?,calibration_algorithm  = ? where task_uuid = ?";
//        $sqlParams = array($utils->parseBoolToFlag($verifyInfo['ischeck']), $verifyInfo['verifytype'],$taskuuid);
//        $result = $result && $this->dbExec($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }
        if ($result) {
            $logParams = array(
                'task_uuid' => $taskuuid,
                'task_name' => $taskName,
                'task_type' => Xphp::$_config['TASKTYPE']['BACKUP'],
                'module_type' => Xphp::$_config['MODULE_TYPE']['VM'],
                'submodule_type' => $hypervisor,
            );
            $this->taskLog($logParams, 'WEB_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS', array($taskName));
        }
        return $this->muOpResult($result, $operate);
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
        $sql = "select distinct vt.type, vt.name, vt.uuid, vt.dir_path path, vt.vcenter_uuid vcuuid, 
            vm.vm_uuid vmuuid, vm.vm_name vmname, vm.version, vm.host_uuid hostuuid 
            from vm_tree vt left join vm_machine vm on vt.uuid = vm.vm_uuid and vt.vcenter_uuid = vm.vcenter_uuid 
            where vt.display_mode = ? and vt.vcenter_uuid = ? and vt.parent_uuid = ? and vt.uuid != vt.parent_uuid 
            and vt.uuid not in (select vm_uuid from vm_machine_list vml join bd_task bt on vml.task_uuid = bt.task_uuid where bt.task_type = ? and bt.task_uuid != ?) order by vt.name";
        $data = $this->dbSelect($sql, [$display_mode, $vcenter_uuid, $object_uuid, Xphp::$_config['TASKTYPE']['BACKUP'], $task_uuid]);
        foreach ($data as $item) {
            if (Xphp::$_config['VM_TREE_TYPE']['VM'] == $item['type']) {
                $vms[] = $item;
            } else {
                $vms = array_merge($vms, $this->getObjectVms($display_mode, $item['vcuuid'], $item['uuid'], $task_uuid));
            }
        }
        return $vms;
    }

    /**
     * 创建CDP备份任务
     * @param unknown $params
     */
    public function createCDPJob($params)
    {
        $task_name = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($task_name);

        $opName = 'BD_TASK_OP_CDP_BACKUP_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $this->checkTaskLegal($operate, $params['srcInfo']['vminfo'], null);


        $module_type = Xphp::$_config['MODULE_TYPE']['VDDT_SERVER'];
        $time_strategy_list = $this->groupCDPTimeList($params['backupInfo']);
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve']);
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer'], '');
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store']);
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);

        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo
        );
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['VM_CDP_BACKUP'];


        if (!$nodeInfo['node_uuid']) {
            //没有节点可用
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_NODE_NOT_FIND_NODE'], 'error');
        }


        $utils = Xphp::instance('Utils');
        //private params
        //急速备份模式
        //         $pfMsg['backup_level'] = $this->getVMTaskLevel($params['highInfo']['mode']['snapshotcheck']);
        $pfMsg['backup_level'] = $this->getBackupSnapshotMode(
            $params['srcInfo']['vmType'],
            $params['highInfo']['mode']['snapshotcheck'],
            $params['highInfo']['mode']['incmode']
        );
        //静默快照
        $pfMsg['quiesce_snapshot'] = $utils->parseBoolToFlag($params['highInfo']['mode']['silentsnapshotcheck']);
        //CBT模式
        $pfMsg['valid_data_backup'] = $utils->parseBoolToFlag($params['highInfo']['mode']['cbtcheck']);
        //V-CBT
        $pfMsg['parse_fs_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['parsefscheck']);
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['swapcheck']);
        //排除删除文件块
        $pfMsg['not_backup_deleted_file_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['deletefilecheck']);
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = $utils->parseBoolToFlag($params['highInfo']['mode']['gapcheck']);
        //快照模式
        $pfMsg['serial_snapshot_flag'] = intval($params['highInfo']['mode']['snapshottype']);
        //细粒度保留字段
        $pfMsg['grain_level'] = 0;

        $submodule_type = intval($params['srcInfo']['vmType']);
        //有效数据备份
        //	    $pfMsg['valid_data_backup'] = $params['highInfo']['store']['valid'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'];
        $pfMsg['backup_vm_uuids'] = $this->groupBackupVM($params['srcInfo']['vminfo']);
        $pfMsg['transport_priority'] = $this->getTransportMode($params['highInfo']['transfer']['mode'], $submodule_type);
        //disk select
        //         $pfMsg['vm_config'] = $this ->groupBackupDisk($params['srcInfo']['disklist']);

        $pfMsg['proxy_uuid'] = "";

        $msg = json_encode($pfMsg);

        $mbResult = $this->mbCDPMsg($nodeInfo['node_uuid'], $submodule_type, $opName, $msg);
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
     * XenServer相关模块的高速模式和增量模式选择
     * @param int $hypervisor
     * @param boolean $speedMode
     * @param int $incMode
     */
    private function getBackupSnapshotMode($hypervisor, $speedMode, $incMode)
    {
        //下列是支持增量模式的虚拟化类型
        $hypervisor = intval($hypervisor);
        if (
            $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OVIRT_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XCP_NG']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XFUSION_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HOSTVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RED_VIRT']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ROSA_VIRT']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_LENOVO_AIO']
        ) {
            //XenServer
            return $incMode;
        }
        return $this->getVMTaskLevel($speedMode);
    }

    /**
     * 创建和修改备份任务做检测(vcenter.class.php有调用)
     * @param string $operate   操作描述
     * @param array $vmInfo     本次备份的虚拟机
     * @param string $taskuuid  任务UUID(修改的时候用)
     */
    public function checkTaskLegal($operate, $vmInfo, $taskuuid)
    {
        $vmCount = $this->getAllTaskCount($vmInfo, $taskuuid);
        //这里暂时只检测在按虚拟机授权的时候虚拟机个数是否足够,不足够直接退出并提示
        $systemHandler = Xphp::instance('SystemHandler');
        $systemLisenceInfo = $systemHandler->getSystemLisenceInfo();
        $systemLisenceInfo = json_decode($systemLisenceInfo, true);

        if ($systemLisenceInfo['status'] == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            //已授权
            //如果是按照虚拟机授权,检测虚拟机个数是否超出限制
            if ($systemLisenceInfo['vminfo']['type'] == Xphp::$_config['LISENCE_INFO']['type']['vm']) {
                $validCount = $systemLisenceInfo['vminfo']['valid'];
                if ($vmCount > $validCount) {
                    exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_LISENCE_USED_ALL'], 'error'));
                }
            }

        } else {
            //未授权
            exit($this->muOpResult(false, $operate, $systemLisenceInfo['statusDes'], 'error'));
        }
        return true;
    }

    /**
     * 得到除当前任务的所有虚拟机个数
     * @param array $vmInfo     本次备份的虚拟机
     * @param string $taskuuid
     */
    private function getAllTaskCount($vmInfo, $taskuuid)
    {
        $vmNum = count($vmInfo);
        if (empty($taskuuid)) {
            //如果是新建任务,直接返回本次虚拟机个数
            return $vmNum;
        }
        //如果是修改任务,需要减掉修改前的虚拟个数,才是新增的虚拟机个数
        $sql = "select count(machine_id) as vm_num from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $oldNum = $data[0]['vm_num'];
        $newNum = $vmNum - $oldNum;

        return $newNum;
    }

    /**
     * 得到是否保留快照标志
     * @param bool $snapshotFlag
     */
    private function getVMTaskLevel($snapshotFlag)
    {
        if ($snapshotFlag) {
            return Xphp::$_config['XENSERVER_BACKUP_LEVEL']['KEEP_SNAPSHOT'];
        }
        return Xphp::$_config['XENSERVER_BACKUP_LEVEL']['NORMAL'];
    }

    /**
     * 得到修改任务的节点和存储UUID信息
     * @return array('nodeuuid','auto_find_sr_flag','storageuuid')
     */
    private function getModifyJobNodeAndStorageInfo($nodeInfo)
    {
        $info = array(
            'nodeuuid' => $nodeInfo['nodeuuid'],
            'auto_find_sr_flag' => Xphp::$_config['FLAG']['UNSET'],
            'storageuuid' => $nodeInfo['storageuuid']
        );
        if (empty($nodeInfo['nodeuuid'])) {
            $nodeHandler = Xphp::instance('NodeHandler');
            $info['nodeuuid'] = $nodeHandler->getAutoFindNode();
        }
        if ($nodeInfo['storagecheck']) {
            //自动选择存储
            $info['auto_find_sr_flag'] = Xphp::$_config['FLAG']['SET'];
            $info['storageuuid'] = '';
        }
        return $info;
    }

    /**
     * 插入时间策略
     * @param int $strategyID 策略ID
     * @param int $modeType
     * @param array $strategyInfo
     */
    public function insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $strategyInfo)
    {
        $utils = Xphp::instance('Utils');
        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
        $rollFlag = $utils->parseBoolToFlag($strategyInfo['rollFlag']);
        $sql = "insert bd_time_strategy ( task_uuid, strategy_group_uuid, strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array(
            $taskuuid,
            $strategygroupuuid,
            $strategyID,
            $modeType,
            $strategyInfo['type'],
            $days,
            $utils->formartTime($strategyInfo['startTime']),
            $rollFlag,
            $utils->timeToSec($strategyInfo['rollInterval']),
            $utils->formartTime($strategyInfo['endTime'])
        );

        return $this->dbQuery($sql, $sqlParams);
    }

    /**
     * 获取时间策略天数的字符串表示
     * @param array $days
     */
    public function getTimeStrategyDaysStr($days)
    {
        $str = '';
        if (empty($days)) {
            return $str;
        }
        $str = implode('', $days);
        return $str;
    }

    /**
     * 得到备份虚拟机数组
     * @param array $vmInfo
     * @return array
     */
    private function groupBackupVM($vmInfo)
    {
        $vmInfoMsg = array();
        foreach ($vmInfo as $vm) {
            $vmConfig = $vm['vm_config'];
            if (empty($vmConfig)) {
                $vmConfig = array(
                    'disk_list' => array()
                );
            }
            $excludeVmList = $vm['exclude_vm_list'] ? array_map(function ($v) {
                return ['vm_uuid' => $v['vm_uuid']];
            }, $vm['exclude_vm_list']) : [];
            //相同则是虚拟机中心
            if ($vm['vcuuid'] == $vm['vmuuid']) {
                $sql = "select nickname, vcenter_ip from vm_vcenter where vcenter_uuid = ?";
                $data = $this->dbSelect($sql, array($vm['vcuuid']));
                $name = $data[0]['nickname'] == "" ? $data[0]['vcenter_ip'] : $data[0]['vcenter_ip'];
            } else {
                $sql = "select name from vm_tree where vcenter_uuid = ? and uuid = ?";
                $name = $this->dbSelect($sql, [$vm['vcuuid'], $vm['vmuuid']])[0]['name'];
            }

            $vmInfoMsg[] = array(
                'object_uuid' => $vm['vmuuid'],
                'object_name' => $name,
                'type' => $vm['type'],
                'vcenter_uuid' => $vm['vcuuid'],
                'dir_path' => $vm['path'],
                'vm_config' => json_encode($vmConfig, JSON_UNESCAPED_UNICODE),
                'exclude_vm_list' => $excludeVmList,
                'log_cache_storage_uuid' => "",
                'log_cache_size' => 2147483648,
            );
        }
        return $vmInfoMsg;
    }

    /**
     * 得到备份虚拟机对应的磁盘数组
     * @param array $diskInfo
     * @return array $diskInfoMsg
     */
    private function groupBackupDisk($diskInfo)
    {
        $diskInfoMsg = array();
        foreach ($diskInfo as $disk) {
            $diskInfoList = array();
            $diskList = array();
            foreach ($disk as $disk1) {
                $diskInfoList[] = array(
                    'disk_name' => $disk1['disk_name'],
                    'disk_uuid' => $disk1['disk_uuid'],
                );
            }
            $diskInfoMsg[] = array_merge($diskList, $diskInfoList);
        }
        return $diskInfoMsg;
    }


    /**
     * 得到从数据库获取的传输模式,修改任务用
     * @param string $transpoertMode
     * @param array
     */
    private function getTransportModeToArr($transpoertMode, $hypervisor)
    {
        $modeArr = explode(":", $transpoertMode);
        //因为现在存入数据库只有三种情况,所以直接取第二项就是设置的模式
        //nbd    file:nbd:nbdssl:san:hotadd
        //nbdssl file:nbdssl:nbd:san:hotadd
        //san    file:san:nbd:nbdssl:hotadd
        $mode = $modeArr[1];
        if (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['vmware']) || in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['huawei'])) {
            $mode = $modeArr[1];
        } else {
            $mode = $transpoertMode;
        }

        return $mode;
    }

    /**
     * 创建恢复任务
     * @param unknown $params
     */
    public function createRecoverJob($params)
    {
        $utils = Xphp::instance('Utils');
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $strategygroupuuid = $params['strategygroupuuid'];
        $submodule_type = intval($params['recoverInfo']['hypervisor']);
        $this->paramsCheck($task_name, $submodule_type);
        //任务一般检查
        $this->createRecoverJobCheck($submodule_type, $params['recoverInfo']['vcenteruuid'], $params['recoverInfo']['names']);
        //跨平台恢复检查
        $this->crossHypervisorRecoveryCheck($params['pointInfo']['type'], $params['recoverInfo']['hypervisor']);
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        $recovery_position = intval($params['recoverInfo']['recover2']);
        $recovery_time_type = intval($params['typeInfo']['type']);
        $time_strategy_list = $this->groupRecoverTimeList($params['typeInfo'], $strategygroupuuid);
        $transport_strategy = $this->groupTransportStrategy($params['typeInfo']['high']['trasfer'], $strategygroupuuid);
        $pfMSg = $this->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['RECOVERY'];
        //private params
        //disk or file
        $pfMSg['recovery_level'] = Xphp::$_config['VmTaskLevel']['VM'];
        $pfMSg['recovery_vm_uuids'] = $this->groupRebuildVMInfo(
            $recovery_position,
            $params['pointInfo']['points'],
            $params['recoverInfo']
        );
        $pfMSg['transport_priority'] = $this->getTransportMode($params['typeInfo']['high']['trasfer']['mode'], $submodule_type);
        $pfMSg['speed_limit_strategy_list'] = $this->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        $pfMSg['strategy_group_uuid'] = $strategygroupuuid;
        //appliance
        $pfMSg['appliance_uuid'] = $params['applianceuuid'];
        $pfMSg['agent_uuid'] = $params['applianceuuid'];
        //openstack跨平台恢复所有
        $pfMSg['stop_on_high_load_flag'] = $utils->parseBoolToFlag($params['highInfo']['storageHighLoadStopTask']);
        //线程数量
        $pfMSg['thread_num'] = intval($params['highInfo']['threadnum']);
        //备份系统节点IP
        $pfMSg['backup_server_ip'] = $params['backup_server_ip'];
        $pfMSg['transport_ip_segment'] = $params['transport_ip_segment'];
        //新增存储媒介 存储路径 校验列表
        $pfMSg['storage_media'] = $params['pointInfo']['storage_media'];
        $pfMSg['storage_path'] = $params['pointInfo']['storagepath'];
        //时间点存储介质的uuid
        $pfMSg['storage_uuid'] = $params['pointInfo']['storageuuid'];
        $pfMSg['calibreate_strategy_list'] = array(
            "int_create_calibration_algorithm" => $utils->parseBoolToFlag($params['verifyInfo']['is_integrity_check']), //是否生成数据校验
            "integrity_error_handle" => $utils->parseBoolToFlag($params['verifyInfo']['integrity_error_handle']), //校验出错后是否继续
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['points'][0]['timepointuuid']);
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        //如果是租户内部的用户操作，检查设置速度是否超出租户配置限制速度
//         if(!empty($_SESSION['tenantuuid'])){
//             $this->pCheckSpeedLimit($params['speedInfo'], $operate);
//         }


        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if (Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($task_name);
            } else {
                $startResult = true;
            }
            if ($startResult) {
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            } else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
                return $this->muOpResult($result, $operate, $msg);
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建虚拟机恢复/瞬时恢复/迁移任务检测目的虚拟化中心是否有同名的虚拟机存在
     * @param string $vcenteruuid  目的VCENTERUUID
     * @param array $vmnames       新的虚拟机列表
     * @return boolean
     */
    private function createRecoverJobCheck($hypervisor, $vcenteruuid, $vmnames)
    {
        //ics/ics-vvdk支持虚拟机同名
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor) {
            return true;
        }
        //检查target虚拟化中心是否有相同名字的虚拟机存在，如果存在则对其进行提示
        $nameStr = '';
        foreach ($vmnames as $name) {
            $nameStr .= "'" . $name . "',";
        }
        $nameStr = substr($nameStr, 0, -1);
        if (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['vmware'])) {
            //如果是VMware
            $displayMode = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'];
            //         }elseif($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']){
        } else {
            //如果是XenServer
            $displayMode = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
        }
        $sql = "select name from vm_tree where vcenter_uuid = ? and display_mode = ? and name in (" . $nameStr . ") ";
        $data = $this->dbSelect($sql, array($vcenteruuid, $displayMode));
        if (!empty($data)) {
            //检查如果有相同名字的虚拟机,程序中断返回提示信息
            $msg = Xphp::$_lang['WEB_JOB_CREATE_VM_RECOVERY_TIPS'];
            foreach ($data as $key => $d) {
                if ($key == (count($data) - 1)) {
                    $msg .= "'" . $d['name'];
                } else {
                    $msg .= "'" . $d['name'] . "',";
                }
            }
            $msg .= "'";
            exit($this->muOpResult(false, Xphp::$_lang['UI_RECOVERY_VM_DESCRIPTION'], $msg, 'warning'));
        }
        return true;
    }

    /**
     * 跨平台恢复检查
     * @param int $oldHypervisor    原虚拟化平台
     * @param int $newHypervisor    新虚拟化平台
     */
    private function crossHypervisorRecoveryCheck($oldHypervisor, $newHypervisor)
    {
        //如果是本平台恢复,不做检查
        if ($oldHypervisor == $newHypervisor) {
            return true;
        }

        $icsTypes = [
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'],
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
        ];
        $sangforTypes = [
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM'],
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
        ];
        // 同组之间恢复不做检查
        if (in_array($oldHypervisor, $icsTypes) && in_array($newHypervisor, $icsTypes)
            || in_array($oldHypervisor, $sangforTypes) && in_array($newHypervisor, $sangforTypes)
        ) {
            return true;
        }

        //获取系统的授权
        $systemHandler = Xphp::instance('SystemHandler');
        $license = $systemHandler->getSystemLisenceInfo();
        $license = json_decode($license, true);

        //如果跨平台恢复未授权,不能恢复
        if(true !== $license['authfun']['crossHypervisorRecovery']){
            $msg = Xphp::$_lang['UI_RECOVERY_CHECK_CROSS_HYPERVISOR_LICENSE_TIPS1'];
            exit($this->muOpResult(false, Xphp::$_lang['UI_RECOVERY_VM_DESCRIPTION'], $msg, 'warning'));
        }
        //如果跨平台恢复授权数量不足,不能恢复
        if($license['v2v']['valid'] <= 0 && $license['v2v']['total'] != -1){
            $msg = Xphp::$_lang['UI_RECOVERY_CHECK_CROSS_HYPERVISOR_LICENSE_TIPS2'];
            exit($this->muOpResult(false, Xphp::$_lang['UI_RECOVERY_VM_DESCRIPTION'], $msg, 'warning'));
        }

        return true;
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName)
    {
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type, vt.hypervisor_type
                from bd_task bt, vm_task vt where bt.task_uuid = vt.task_uuid and bt.task_name = ? and bt.module_type = ? and bt.task_type = ?
                order by bt.id desc";
        $data = $this->dbSelect($sql, array($taskName, Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['RECOVERY']));
        if (!$data)
            return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'uuid' => $data[0]['task_uuid'],
            'module' => $data[0]['module_type'],
            'subModule' => $data[0]['hypervisor_type'],
            'taskType' => $data[0]['task_type'],
            'startType' => Xphp::$_config['BACKUP_MODE']['FULL'],
        );
        //调用系统统一启动任务接口.不重新写
        $jobHandler = Xphp::instance('JobHandler');
        $result = $jobHandler->startJob($params);
        $result = json_decode($result, true);
        //这里直接返回成功或失败 bool
        return $result['re'];
    }

    /**
     * 创建瞬时恢复任务
     * @param unknown $params
     */
    public function createInstantRecoverJob($params)
    {
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $submodule_type = intval($params['recoverInfo']['hypervisor']);
        $this->paramsCheck($task_name, $submodule_type);
        $this->createRecoverJobCheck($submodule_type, $params['recoverInfo']['vcenteruuid'], array($params['recoverInfo']['newname']));
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        $recovery_position = intval($params['recoverInfo']['recover2']);
        $recovery_time_type = Xphp::$_config['FLAG']['UNSET'];  //补齐,无用
        $time_strategy_list = array();
        $transport_strategy = $this->groupTransportStrategy(array(), '');
        $pfMSg = $this->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'];
        //private params
        //disk or file
        $pfMSg['recovery_level'] = Xphp::$_config['VmTaskLevel']['INSTANT_RECOVERY'];
        $pfMSg['hypervisor_type'] = $submodule_type;
        $pfMSg['instant_vm_info'] = $this->groupInstantVMInfo($params['pointInfo']['points'], $params['recoverInfo']);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['points']['uuid']);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_INSTANT_RECOVERY_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建备份数据导出任务
     * @param unknown $params
     */
    public function createDataExportJob($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $storageuuid = $params['storageuuid'];
        $hypervisor = intval($params['hypervisor']);
        $timepointuuids = $params['timepointuuids'];
        $taskName = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($nodeuuid, $storageuuid, $hypervisor, $timepointuuids, $taskName);

        $opName = 'BD_TASK_OP_BACKUP_EXPORT_CREATE';
        $msg = array(
            "task_name" => $taskName,
            "storage_uuid" => $storageuuid,
            "node_uuid" => $nodeuuid,
            "module_type" => Xphp::$_config['MODULE_TYPE']['VM'],
            "task_type" => Xphp::$_config['TASKTYPE']['BACKUP_EXPORT'],
            "hypervisor_type" => $hypervisor,
            "export_type" => 0,
            "export_timepoint_uuids" => $timepointuuids,
        );
        $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建虚拟机迁移任务
     * @param unknown $params
     */
    public function createMotionJob($params)
    {
        $hypervisor = intval($params['hostInfo']['hypervisor']);            //虚拟化类型
        $this->createRecoverJobCheck($hypervisor, $params['hostInfo']['vcenteruuid'], array($params['vmName']));
        $hostInfo = $params['hostInfo'];                //宿主机信息
        $storeType = 1;      //存储类型 1 自动 /2 手动
        $storeName = '';                                //存储名字
        $vmNewName = $params['vmName'];                 //新虚拟机名字
        $startVMFlag = $params['startVMFlag'];          //迁移完成启动虚拟机标志
        $instantTaskUUID = $params['instantTaskUUID'];  //瞬时恢复任务uuid
        $threadNum = intval($params['threadNum']);      //线程数量
        $transportmode = $params['transportmode'];      //传输模式
        //appliance
        $applianceuuid = $params['applianceuuid'];
        // 加密传输
        $encryptFlag = $params['encrypt_flag'];
        // 加密传输算法
        $encryptMethod = $params['encrypt_method'];


        //获取原始虚拟机的信息
        $sql = "select orig_vm_uuid, timepoint_uuid from vm_instant where task_uuid = ?";
        $data = $this->dbSelect($sql, array($instantTaskUUID));
        if (!empty($data)) {
            $vmuuid = $data[0]['orig_vm_uuid'];
            $timepointUUID = $data[0]['timepoint_uuid'];
        } else {
            return $this->muOpResult(FALSE, Xphp::$_lang['WEB_VM_GET_OLD_VM_INFO']);
        }

        //更新线程数量
        $sql = "update bd_task set thread_num = ? where task_uuid = ?";
        $result = $this->dbQuery($sql, array($threadNum, $instantTaskUUID));

        $hostuuid = $hostInfo['hostuuid'];
        if (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
            $hostuuid = $hostInfo['groupuuid'];
        }
        //备份系统节点IP
        $backup_server_ip = $params['backup_server_ip'];
        //数据传输网段
        $transport_ip_segment = $params['transport_ip_segment'];
        $utils = Xphp::instance('Utils');
        $rebuildVMInfo = array(
            'vm_uuid' => $vmuuid,
            'new_name' => $vmNewName,
            'timepoint_uuid' => $timepointUUID,
            'destination_host_uuid' => $hostuuid,
            'destination_vcenter_uuid' => $hostInfo['vcenteruuid'],
            'auto_datastore_flag' => $storeType,
            'destination_datastore_name' => $storeName,
            'start_vm_flag' => $utils->parseBoolToFlag($startVMFlag),
            'vm_config' => $this->groupVMConfig($params['vmconfigs'][0]),
            'extension_info' => $this->groupExtensionInfo($hostInfo)    //扩展消息
        );
        $pfMSg = array(
            'task_uuid' => $instantTaskUUID,
            'rebuild_vm' => $rebuildVMInfo,
            'transport_priority' => $this->getTransportMode($transportmode, $hypervisor),
            'appliance_uuid' => $applianceuuid,
            'agent_uuid' => $applianceuuid,
            'backup_server_ip' => $backup_server_ip,
            'transport_ip_segment' => $transport_ip_segment,
            'encrypt_flag' => $utils->parseBoolToFlag($encryptFlag),
            'encrypt_method' => $encryptMethod
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 深信服scp迁移
     * @param $params
     * @return string
     */
    public function createSangforScpMotionJob($params)
    {
        $hypervisor = Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'];            //虚拟化类型
        $storeType = 1;      //存储类型 1 自动 /2 手动
        $storeName = '';                                //存储名字
        $startVMFlag = false;          //迁移完成启动虚拟机标志
        $instantTaskUUID = $params['instantTaskUUID'];  //瞬时恢复任务uuid
        $threadNum = 3;      //线程数量
        $transportmode = 'nbd';      //传输模式
        //appliance
        $applianceuuid = '';

        //获取原始虚拟机的信息
        $sql = "select orig_vm_uuid, new_vm_name, timepoint_uuid, target_vcenter_uuid, target_host_uuid 
            from vm_instant where task_uuid = ?";
        $data = $this->dbSelect($sql, array($instantTaskUUID));
        if (empty($data)) {
            return $this->muOpResult(FALSE, Xphp::$_lang['WEB_VM_GET_OLD_VM_INFO']);
        }
        $vmuuid = $data[0]['orig_vm_uuid'];
        $vmNewName = $data[0]['new_vm_name'];                 //新虚拟机名字
        $timepointUUID = $data[0]['timepoint_uuid'];
        $hostInfo = [
            'vcenteruuid' => $data[0]['target_vcenter_uuid'],
            'hostuuid' => $data[0]['target_host_uuid'],
            'hypervisor' => $hypervisor
        ];                //宿主机信息

        //更新线程数量
        $sql = "update bd_task set thread_num = ? where task_uuid = ?";
        $this->dbQuery($sql, array($threadNum, $instantTaskUUID));

        //备份系统节点IP
        $backup_server_ip = '';
        //数据传输网段
        $transport_ip_segment = '';
        $utils = Xphp::instance('Utils');
        $rebuildVMInfo = array(
            'vm_uuid' => $vmuuid,
            'new_name' => $vmNewName,
            'timepoint_uuid' => $timepointUUID,
            'destination_host_uuid' => $hostInfo['hostuuid'],
            'destination_vcenter_uuid' => $hostInfo['vcenteruuid'],
            'auto_datastore_flag' => $storeType,
            'destination_datastore_name' => $storeName,
            'start_vm_flag' => $utils->parseBoolToFlag($startVMFlag),
            'vm_config' => '',
            'extension_info' => $this->groupExtensionInfo($hostInfo)    //扩展消息
        );
        $pfMSg = array(
            'task_uuid' => $instantTaskUUID,
            'rebuild_vm' => $rebuildVMInfo,
            'transport_priority' => $this->getTransportMode($transportmode, $hypervisor),
            'appliance_uuid' => $applianceuuid,
            'agent_uuid' => $applianceuuid,
            'backup_server_ip' => $backup_server_ip,
            'transport_ip_segment' => $transport_ip_segment
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建细粒度恢复任务
     * @param unknown $params
     */
    public function createGrainRecoverJob($params)
    {
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $hypervisor = intval($params['pointInfo']['type']);
        $point = $params['pointInfo']['points'];
        $this->paramsCheck($task_name, $hypervisor);
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        $recovery_position = 2;
        $recovery_time_type = 2;
        $time_strategy_list = array();
        $transport_strategy = $this->groupTransportStrategy(array(), '');

        $pfMSg = $this->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'];
        $pfMSg['recovery_level'] = Xphp::$_config['VmTaskLevel']['FILE'];
        $pfMSg['recovery_vm_uuids'] = array();
        $pfMSg['transport_priority'] = "";

        //上面是恢复的参数,下面是细粒度参数
        $pfMSg['vm_uuid'] = $point['vmuuid'];
        $pfMSg['vm_name'] = $point['vmname'];
        $pfMSg['timepoint_uuid'] = $point['uuid'];


        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($point['uuid']);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_GRAIN_RECOVERY_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);


        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到传输模式到后台的字符串
     * @param string $mode      nbd/nas
     */
    private function getTransportMode($mode, $hypervisor)
    {

        $transportMode = "file:nbd:nbdssl:san:hotadd";  //网络传输  nbd
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
     * 组合瞬时恢复数据
     * @param array $pointInfo
     * @param array $recoverInfo
     */
    private function groupInstantVMInfo($pointInfo, $recoverInfo)
    {
        $hostuuid = $recoverInfo['hostuuid'];
        $hypervisor = intval($recoverInfo['hypervisor']);
        if (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
            $hostuuid = $recoverInfo['groupuuid'];
        }
        $utils = Xphp::instance('Utils');
        $vmInfo = array(
            'orig_vm_uuid' => $pointInfo['vmuuid'],      //虚拟机uuid
            'orig_vm_name' => $pointInfo['vmname'],      //虚拟机名字
            'orig_vm_version' => $pointInfo['version'],   //虚拟机版本
            'new_vm_name' => $recoverInfo['newname'],         //虚拟机新名字
            'timepoint_uuid' => $pointInfo['uuid'],              //时间点uuid
            'target_vcenter_uuid' => $recoverInfo['vcenteruuid'], //恢复到vcenter uuid
            'target_host_uuid' => $hostuuid,   //恢复到数组机 uuid
            'nfs_server_ip' => $recoverInfo['addr'],          //挂载点IP
            'start_vm_flag' => $utils->parseBoolToFlag($recoverInfo['startvm']),     //启动标志
            'advance_vm_config' => $this->groupVMConfig($recoverInfo['vmconfigs'][0]),       //虚拟机配置
            'extension_info' => $this->groupExtensionInfo($recoverInfo),    //扩展消息
        );
        return $vmInfo;
    }

    /**
     * 组合恢复虚拟机和时间点,新名字信息
     * @param int $recoveryPos  原机/异机
     * @param array $points     时间点信息
     * @param array $names      名字信息
     * @return array
     */
    private function groupRebuildVMInfo($recoveryPos, $points, $recoverInfo)
    {
        $pointInfo = array();
        $utils = Xphp::instance('Utils');
        if (Xphp::$_config['RECOVERY_POSITION']['ORIGINAL'] == $recoveryPos) {
            //原机恢复,新版本已经去除,这里暂时不修改,也不删除
            foreach ($points as $point) {
                $pointInfo[] = array(
                    'vm_uuid' => $point['vmuuid'],
                    'new_name' => '',
                    'timepoint_uuid' => $point['timepointuuid'],
                    'destination_host_uuid' => '',
                    'destination_vcenter_uuid' => $point['vcenteruuid'],

                    'auto_datastore_flag' => 0,
                    'destination_datastore_name' => '',
                    'start_vm_flag' => $utils->parseBoolToFlag($recoverInfo['startvm']),
                    'vm_config' => '',
                    'extension_info' => '',
                    'project_uuid' => $point['projectid']
                );
            }
        } elseif (Xphp::$_config['RECOVERY_POSITION']['OTHER'] == $recoveryPos) {
            //异机恢复
            $i = 0;
            foreach ($points as $point) {
                $pointInfo[] = array(
                    'vm_uuid' => $point['vmuuid'],
                    'new_name' => $recoverInfo['vmconfigs'][$i]['vmname'],
                    'timepoint_uuid' => $point['timepointuuid'],
                    'destination_host_uuid' => $recoverInfo['hostuuid'],
                    'destination_vcenter_uuid' => $recoverInfo['vcenteruuid'],

                    'auto_datastore_flag' => Xphp::$_config['FLAG']['UNSET'],
                    'destination_datastore_name' => '',
                    'start_vm_flag' => $utils->parseBoolToFlag($recoverInfo['vmconfigs'][$i]['power']),
                    'vm_config' => $this->groupVMConfig($recoverInfo['vmconfigs'][$i]),
                    'extension_info' => $this->groupExtensionInfo($recoverInfo),
                    'project_uuid' => $point['projectid']
                );
                $i++;
            }
        }
        return $pointInfo;
    }

    /**
     * 组合extensionInfo
     * @param unknown $recoverInfo
     */
    private function groupExtensionInfo($recoverInfo)
    {
        $utils = Xphp::instance("Utils");
        $info = array(
            'group_name' => $recoverInfo['groupname'],
            'group_uuid' => $recoverInfo['groupuuid'],
            'user_name' => $recoverInfo['username'],
            'password' => $utils->ptPassDecrypt($recoverInfo['password']),
            'controller_ip' => $recoverInfo['controllerip'],
        );
        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 组合虚拟机配置
     * @param unknown $vmConfig
     */
    private function groupVMConfig($vmConfig)
    {
        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        $other = $vmConfig['other'];
        $info = array(
            'cpu_socket' => intval($vmConfig['cpu_socket']),
            'cores_per_socket' => intval($vmConfig['cpu_core']),
            'vm_memory' => $vmRecoveryHandler->calUnitToSize($vmConfig['vm_memory'], $vmConfig['vm_memory_unit']),
            'auto_conf_flag' => Xphp::$_config['FLAG']['UNSET'],
            'boot_mode' => intval($other['boot_type_select']),  //引导模式
            'vm_version' => $other['vm_version'],
            'vm_network_list' => $vmRecoveryHandler->processRecoveryVmNetwork($vmConfig['network']),    //网络
            'vm_disk_list' => $vmRecoveryHandler->processRecoveryVmDisk($vmConfig['storage']),          //磁盘
            'zone_config' => $vmRecoveryHandler->processRecoveryVmZoneConfig($other['available_domain_select']),  //虚拟机可用域
            'disk_zone_config' => $vmRecoveryHandler->processRecoveryVmZoneConfig($other['disk_domain_select']), //磁盘机可用域
            'video_device' => array('video_type' => 0, 'vram_memory' => 0),                              //显卡,暂时没有用,保留字段
            'other_config' => $vmRecoveryHandler->processRecoveryVmOtherConfig($other),                 //其他配置
            'openstack_start_mode' => intval($vmConfig['openstack_start_mode']),
            'root_disk_size' => $vmRecoveryHandler->calUnitToSize(intval($vmConfig['root_disk_size']), "GB"),
            'os_type' => $vmConfig['os_type'],
            'delete_on_termination' => $vmConfig['openstack_del_vm_del_disk_mode'] ?? false,
            'flavor_id' => $vmConfig['flavor_id'],//openstack实例类id
            'original_recovery' => $other['original_recovery'],
            'image_metadata' => json_encode($other['image_metadata']),
        );
        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    public function groupBackupTimeList($params, $strategygroupuuid)
    {
        $msg = array();
        if ('strategy' == $params['type']) {
            //按时间策略备份
            if (!empty($params['fullInfo'])) {
                //完全策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['fullInfo'], $strategygroupuuid);
            }
            if (!empty($params['incrInfo'])) {
                //增量策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['incrInfo'], $strategygroupuuid);
            }
            if (!empty($params['diffInfo'])) {
                //差异策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'], $params['diffInfo'], $strategygroupuuid);
            }
            if (!empty($params['copystrategy'])) {
                //副本策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['COPY'], $params['copystrategy'], $strategygroupuuid);
            }
            if (!empty($params['archivestrategy'])) {
                //归档策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['ARCHIVE'], $params['archivestrategy'], $strategygroupuuid);
            }

            if (!empty($params['logInfo'])) {
                //日志策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['LOG'], $params['logInfo'], $strategygroupuuid);
            }
            if (!empty($params['pIncrInfo'])) {
                //永久增量（mode==增量备份）
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['pIncrInfo'], $strategygroupuuid);
            }
        } else if ('1' == $params['type']) {
            //CDP使用
            $strategy = array('startTime' => "");
            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
        } else if ('2' == $params['type']) {
            //CDP使用
            $strategy = array('startTime' => $params['datetime']);
            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
        } elseif ('manual' == $params['type']) {  // 手动启动不需要传list
            $msg = [];
        } else {
            //一次性备份
            $strategy = array('startTime' => $params['datetime']);
            //检查时间和系统时间 如果一次性备份时间超过了系统时间则退出创建并返回结果
            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            //获取一次性备份时间
            $taskCreateTime = strtotime($params['datetime']);
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_BACKUP_TIME'], Xphp::$_lang['WEB_DB_BACKUP_TIME_TIPS'], 'warning'));
            }
            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
            $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $strategy, $strategygroupuuid);
        }

        return $msg;
    }

    /**
     * 组合CDP备份启动方式
     * @param array $params
     * @return array
     */
    private function groupCDPTimeList($params)
    {
        $msg = array();
        if ('1' == $params['type']) {
            //CDP使用
            $strategy = array('startTime' => "");
            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
        } else if ('2' == $params['type']) {
            //CDP使用
            $strategy = array('startTime' => $params['datetime']);
            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
        }

        return $msg;
    }

    /**
     * 组合恢复时间策略
     * @param array $params
     * @return array
     */
    public function groupRecoverTimeList($params, $strategygroupuuid)
    {
        $timeList = array();
        if (Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == intval($params['type'])) {
            //立即恢复
            return $timeList;
        } elseif (Xphp::$_config['RECOVERY_TIME_TYPE']['STRATEGY'] == intval($params['type'])) {
            //按时间策略恢复
            $timeList[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['strategy'], $strategygroupuuid);
            return $timeList;
        } else if(Xphp::$_config['RECOVERY_TIME_TYPE']['ONCETIME'] == intval($params['type'])){
            //获取一次性恢复的时间
             $taskCreateTime = strtotime($params['strategy']['startTime']);
             //获取系统时间
             $systemTime = strtotime(date('Y-m-d H:i:s'));
             if ($systemTime >= $taskCreateTime) {
                 exit($this->muOpResult(
                     false,
                     Xphp::$_lang['UI_RECOVERY_TYPE_TIMING_TIME'],
                     Xphp::$_lang['WEB_RECOVERY_TIME_TIPS'],
                     'warning'
                 )
                 );
             }
            $timeList[] = $this->groupEachTimestrategy(
               Xphp::$_config['BACKUP_MODE']['FULL'],
                $params['strategy'],
                $strategygroupuuid
            );
            return $timeList;
        }
    }

    /**
     * 组合每一个时间策略
     * @param int $mode         完全1/增量2/差异3/日志4/标签7
     * @param array $strategy
     * @return array
     */
    private function groupEachTimestrategy($mode, $strategy, $strategygroupuuid)
    {
        $utils = Xphp::instance('Utils');
        $this->paramsCheck($mode, $strategy);
        $strArr = array("mode" => $mode);
        $strArr['strategy_group_uuid'] = $strategygroupuuid;

        if ($strategy['globalID']) {
            //使用全局策略
            $strArr['global_id'] = $strategy['globalID'];
        }
        //时间策略
        if ($mode == 7) {
            $strategy = $strategy[0];
        }
        $strategy['days'] = $strategy['days'] ?? [];
        $strArr['full_backup_compensation_flag'] = $utils->parseBoolToFlag($strategy['full_backup_compensation_flag']);
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['days'] = implode("", $strategy['days']) . $strategy['frequency'];
        $strArr['start_time'] = $strategy['startTime'];
        $strArr['roll_flag'] = $strategy['rollFlag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['rollInterval']);
        $strArr['roll_end_time'] = $strategy['endTime'];

        if (Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY'] == intval($strArr['strategy_type'])) {
            //每天备份
            $strArr['days'] = "1111111";
        }

        if (Xphp::$_config['STRATEGY_TYPE']['ONCE'] == intval($strArr['strategy_type'])) {
            //一次性备份
            $strArr['days'] = '';
        }

        if ($strArr['roll_flag']) {
            //滚动备份
            $strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['ON'];
        } else {
            //不滚动
            $strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }

    /**
     * 转化滚动间隔为秒
     * @param string $rollInterval
     * @return number
     */
    private function getRollInterval($rollInterval)
    {
        if (empty($rollInterval)) {
            return 0;
        }
        $intervalArr = explode(":", $rollInterval);
        $second = intval($intervalArr[0]) * 3600 + intval($intervalArr[1]) * 60 + intval($intervalArr[2]);
        return $second;
    }

    /**
     * 组合保留策略
     * @param array $reserve    保留策略信息
     *  @param  int type        类型
     *  @param  int value       值
     *  @param  bool archive    归档标记
     * @return array
     */
    public function groupReserverStrategy($reserve, $strategygroupuuid = "")
    {
        $this->paramsCheck($reserve);
        $strArr = array(
            'enable_flag' => $reserve['enable_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'strategy_mode' => intval($reserve['strategyMode']) ? intval($reserve['strategyMode']) : 0,
            'strategy_type' => intval($reserve['type']),
            'number' => intval($reserve['value']),
            'auto_archive_flag' => $reserve['archive'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'strategy_group_uuid' => $strategygroupuuid

        );
        return $strArr;
    }

    /**
     * 组合传输策略
     * @param array $transport  传输策略信息
     *  @param  bool encrypt    加密
     *  @param  bool compress   压缩
     *  @param  bool speedFlag  限速
     *  $param  string 传输网络指定唯一标识
     *  @param  int  speed
     * @return array
     */
    public function groupTransportStrategy($transport, $strategygroupuuid = "")
    {
        $strArr = array(
            'encrypt_flag' => $transport['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $transport['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'speed_limit_flag' => $transport['speedFlag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'max_speed' => intval($transport['speed']),
            'network_uuid' => !empty($transport['network']) ? $transport['network'] : "", //传输网络
            'network_pool_uuid' => !empty($transport['network_pool_uuid'])? $transport['network_pool_uuid']: "", //传输网络资源池
            'compress_method' => $transport['compress_method'] ? $transport['compress_method'] : 0,
            'strategy_group_uuid' => $strategygroupuuid,
            'reconnect_times' => intval($transport['reconnect_times']),
            'reconnect_interval' => intval($transport['reconnect_interval']),
            'encrypt_method' => $transport['encrypt'] && $transport['encrypt_method'] ? $transport['encrypt_method'] : 0,
        );
        return $strArr;
    }
    /**
     * 组合存储策略
     * @param array $storage  存储策略信息
     *  @param  int blocksize    数据块大小
     *  @param  bool compress    压缩
     *  @param  bool deduplication  重删
     *  @param  bool  encrypt     加密
     * @return array
     */
    public function groupStorageStrategy($storage, $strategygroupuuid)
    {
        $strArr = array(
            'deduplication_flag' => $storage['deduplication'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'blocksize' => intval($storage['blocksize']) * 1024,
            'encrypt_flag' => $storage['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $storage['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'strategy_group_uuid' => $strategygroupuuid,
            'password_auto_flag' => $storage['password_auto_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'password' => $storage['password'],
            'compress_method' => $storage['compress_method'] ? $storage['compress_method'] : 0,
            'encrypt_method' => intval($storage['encrypt_method']),
        );
        return $strArr;
    }

    public function getVcenterType($vcenteruuid)
    {
        $sql = "select vcenter_flag from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        return intval($data[0]['vcenter_flag']);
    }
    /**
     * 得到备份的虚拟机配置信息
     * @param unknown $params
     */
    public function getVMConfigInfo($params)
    {
        $vmInfo = array();
        foreach ($params as $p) {
            $sql = "select vm_config, hypervisor_type from vm_backup_timepoint where timepoint_uuid = ?
                    and vcenter_uuid = ? and vm_uuid = ?";
            $sqlParams = array($p['timepointuuid'], $p['vcenteruuid'], $p['vmuuid']);
            $data = $this->dbSelect($sql, $sqlParams);
            $hypervisor = $data[0]['hypervisor_type'];
            $config = json_decode($data[0]['vm_config'], true);
            $vmInfo[] = array(
                'vmuuid' => $config['vm_uuid'],
                'vmname' => $p['vmname'],
                'oldname' => $p['oldname'] . "_" . $p['timepoint'],
                'storage' => $this->getVMConfigStorageInfo($config['disk_list'], $hypervisor),
                'network' => $this->getVMConfigNetworkInfo($config['network_list'], $hypervisor),
                'hypervisor' => intval($hypervisor)
            );
        }
        return json_encode($vmInfo);
    }


    /**
     * 得到备份的虚拟机配置信息
     * @param unknown $params
     */
    public function getVMConfigInfoV2($params)
    {
        $hypervisor = $params['hypervisor'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $points = $params['points'];
        $pointsDetail = $params['pointsDetail'];
        $this->paramsCheck($hypervisor, $points);

        $nodeuuid = $pointsDetail[0]['nodeuuid'];   //从时间点获取节点uuid
//         var_dump($hypervisor, $points);

        $opName = 'VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF';
        $mbMsg = array(
            'target_hypervisor_type' => $hypervisor,
            'recovery_timepoint_uuid_list' => $points,
        );
        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $mbMsg, true);

        //         var_dump($mbResult);
        if (!$mbResult['result']) {
            //获取后台消息失败
            $info = array(
                "flag" => false,
                "errorCode" => 1,
                "errorMsg" => '',
            );
            if (50 == $mbResult['errorCode'] || 95 == $mbResult['errorCode']) {
                //时间点不存在
                $info['errorCode'] = 50;
                $info['errorMsg'] = Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_NOT_EXIST_ERROR'];
            }
            return json_encode($info);
        }

        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        $info = $vmRecoveryHandler->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $points, $pointsDetail, $mbResult['msg'], $params['instantFlag']);
        $info['flag'] = true;
        $info['instantFlag'] = $params['instantFlag'];  //瞬时恢复标记,瞬时恢复的时候不选择存储
        $info['vmotionFlag'] = false;                   //迁移标记,插件统一处理的,这里补齐这个字段


        if ($params['instantFlag']) {
            //如果是瞬时恢复,不显示磁盘置备模式
            $info['control']['disk_setting_mode'] = false;
        }

        return json_encode($info);

    }

    /**
     * 得到灾难演练虚拟机配置信息,创建预案虚拟机
     * @param unknown $params
     */
    public function getVMConfigInfoOrch($params)
    {
        $this->paramsCheck($params);
        $vmInfo = array();
        foreach ($params as $p) {
            $sql = "SELECT vm_name, vm_config, dir_path FROM `vm_backup_timepoint` where vm_uuid = ? and vcenter_uuid = ?
                    and hypervisor_type = ? ORDER BY vm_timepoint_id desc limit 0,1 ";
            $sqlParams = array($p['vmuuid'], $p['vcenteruuid'], $p['hypervisor']);
            $data = $this->dbSelect($sql, $sqlParams);
            $hypervisor = $p['hypervisor'];
            $config = json_decode($data[0]['vm_config'], true);
            $vmInfo[] = array(
                'vmuuid' => $config['vm_uuid'],
                'vcenteruuid' => $p['vcenteruuid'],
                'childuuid' => $p['childuuid'],
                'vmname' => $p['vmname'] . Xphp::$_lang['WEB_DRILLS'],
                'oldname' => $data[0]['vm_name'],
                'storage' => $this->getVMConfigStorageInfo($config['disk_list'], $hypervisor),
                'network' => $this->getVMConfigNetworkInfo($config['network_list'], $hypervisor),
                'cpu' => $this->getVMConfigCPUInfo($config, $hypervisor),
                'memory' => $this->getVMConfigMemoryInfo($config, $hypervisor),
                'host' => array()
            );
        }
        return json_encode($vmInfo);
    }

    /**
     * 得到预案虚拟机配置信息,创建任务
     * @param unknown $params
     */
    public function getVMPlanConfigInfo($params)
    {
        $this->paramsCheck($params);
        $vmInfo = array();
        foreach ($params as $p) {
            //从备份时间点获取网络和存储信息
            $sql = "SELECT vm_config, hypervisor_type FROM `vm_backup_timepoint` where vm_uuid = ? and vcenter_uuid = ?
                    and timepoint_uuid = ? ";
            $sqlParams = array($p['vmuuid'], $p['vcenteruuid'], $p['timepointuuid']);
            $data = $this->dbSelect($sql, $sqlParams);
            $config = json_decode($data[0]['vm_config'], true);
            $hypervisor = $data[0]['hypervisor_type'];

            //从预案虚拟机表获取配置的虚拟机信息
            $sql = "select vm_config from orch_plan_vm where vm_uuid = ? and vcenter_uuid = ? and child_uuid = ?";
            $sqlParams = array($p['vmuuid'], $p['vcenteruuid'], $p['childuuid']);
            $data = $this->dbSelect($sql, $sqlParams);
            $planConfig = json_decode($data[0]['vm_config'], true);
            $vmInfo[] = array(
                'vmuuid' => $p['vmuuid'],
                'vcenteruuid' => $p['vcenteruuid'],
                'childuuid' => $p['childuuid'],
                'vmname' => $planConfig['vmname'],
                'oldname' => $planConfig['vmname'],
                'storage' => $this->getVMConfigStorageInfo($config['disk_list'], $hypervisor),
                'network' => $this->getVMConfigNetworkInfo($config['network_list'], $hypervisor),
                'cpu' => $this->getVMConfigCPUInfoPlan($planConfig),
                'memory' => $this->getVMConfigMemoryInfoPlan($planConfig),
                'power' => $planConfig['power'],
                'host' => array()
            );
        }
        return json_encode($vmInfo);
    }

    /**
     * 得到备份虚拟机的CPU信息,从备份时间点
     * @param array $config
     * @param int $hypervisor
     */
    private function getVMConfigCPUInfo($config, $hypervisor)
    {
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])) {
            $info = array(
                'cpunum' => intval($config['numCPUs']),
                'cpucore' => intval($config['numCoresPerSocket']),

            );
            //         }elseif (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER'] == $hypervisor){
        } else {
            //TODO 时间点需要添加每个CPU核心数
            $info = array(
                'cpunum' => intval($config['vcpu_num']),
                'cpucore' => 1,
            );
        }
        //配合控件,设置默认最大值
        $info['maxnum'] = 1000;
        $info['maxcore'] = 1000;
        return $info;
    }

    /**
     * 得到预案的CPU配置信息,创建任务
     * @param unknown $config
     */
    private function getVMConfigCPUInfoPlan($config)
    {
        $info = array(
            'cpunum' => $config['cpu']['cpunum'],
            'cpucore' => $config['cpu']['cpucore']
        );
        //配合控件,设置默认最大值
        $info['maxnum'] = 1000;
        $info['maxcore'] = 1000;
        return $info;
    }

    /**
     * 得到备份虚拟机的内存信息,从备份时间点
     * @param array $config
     * @param int $hypervisor
     */
    private function getVMConfigMemoryInfo($config, $hypervisor)
    {
        //统一单位为MB
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])) {
            $info = array(
                'memorynum' => intval($config['memoryMB'])
            );
            //         }elseif (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER'] == $hypervisor){
        } else {
            $info = array(
                'memorynum' => intval($config['vm_memory'] / 1024 / 1024)
            );
        }
        //配合控件,设置默认最大值
        $info['maxmemory'] = 1048576;
        return $info;
    }

    /**
     * 得到预案的内存配置信息,创建任务
     * @param unknown $config
     */
    private function getVMConfigMemoryInfoPlan($config)
    {
        $info = array(
            'memorynum' => $config['memory']
        );
        //配合控件,设置默认最大值
        $info['maxmemory'] = 1048576;
        return $info;
    }

    /**
     * 得到备份虚拟机的磁盘信息
     * @param unknown $storageList
     */
    private function getVMConfigStorageInfo($storageList, $hypervisor)
    {
        $hypervisor = intval($hypervisor);
        $info = array();
        $utils = Xphp::instance("Utils");
        foreach ($storageList as $storage) {
            switch ($hypervisor) {
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
                    $info[] = array(
                        'vdi_uuid' => $storage['uuid'],
                        'vdi_name' => $storage['diskName'],
                        'virtual_size' => $utils->calSize($storage['totalSize']),
                        'size' => $storage['totalSize'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']:
                    $info[] = array(
                        'vdi_uuid' => $storage['_disk_uuid'],
                        'vdi_name' => $storage['_disk_path'],
                        'virtual_size' => $utils->calSize($storage['_size']),
                        'size' => $storage['_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XCP_NG']:

                    if ($storage['is_backup_or_recovery'] == false)
                        break;
                    $info[] = array(
                        'vdi_uuid' => $storage['vdi_uuid'],
                        'vdi_name' => $storage['vdi_name'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
                    if ($storage['is_backup_or_recovery'] == false)
                        break;
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $storage['target_attr_dev'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EASYSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CTSI_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_AW_CLOUD']:
                    if ($storage['is_backup_or_recovery'] == false)
                        break;
                    $name = '[' . $storage['storage_pool']['pool_name'] . ']' . $storage['target_attr_dev'];
                    if (empty($storage['storage_pool']['pool_name'])) {
                        $name = $storage['target_attr_dev'];
                    }
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $name,
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'bootable' => $storage['bootable'],
                        'storage_type' => $storage['storage_type'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SDC_OS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ZSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XSKY']:
                    if ($storage['is_backup_or_recovery'] == false)
                        break;
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $storage['driver_attr_name'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_PROXMOX']:
                    if ($storage['is_backup_or_recovery'] == false)
                        break;
                    $info[] = array(
                        'vdi_uuid' => $storage['target_attr_dev'],
                        'vdi_name' => $storage['driver_attr_name'],
                        'virtual_size' => $utils->calSize($storage['virtual_size']),
                        'size' => $storage['virtual_size'],
                        'data_storage_uuid' => $storage['datastoreUuid']
                    );
                    break;
            }
        }
        return $info;
    }

    /**
     * 得到备份虚拟机的网络信息
     * @param unknown $networkList
     */
    private function getVMConfigNetworkInfo($networkList, $hypervisor)
    {
        $hypervisor = intval($hypervisor);
        $info = array();
        foreach ($networkList as $network) {
            switch ($hypervisor) {
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
                    $info[] = array(
                        'net_num' => $network['label'],
                        'mac' => $network['macAddress'],
                        'data_network_uuid' => $network['deviceName']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']:
                    $info[] = array(
                        'net_num' => $network['_network_adapter_name'],
                        'mac' => $network['_mac_address'],
                        'data_network_uuid' => $network['deviceName']

                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XCP_NG']:
                    $info[] = array(
                        'net_num' => $network['net_num'],
                        'mac' => $network['mac'],
                        'data_network_uuid' => $network['deviceName']
                    );
                    break;
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SDC_OS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ZSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_OLVM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XSKY']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EASYSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CTSI_OPENSTACK']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_AW_CLOUD']:
                case Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_PROXMOX']:
                    $info[] = array(
                        'net_num' => $network['target_attr_dev'],
                        'mac' => $network['mac_attr_address'],
                        'data_network_uuid' => $network['deviceName']
                    );
                    break;
            }
        }
        $utils = Xphp::instance('Utils');
        $info = $utils->arraySort($info, 0, 'asc', 0, -1);
        return $info;
    }

    /**
     * 得到网络和存储(适用于openstack,得到某个项目的网络和存储)
     * @param unknown $params
     */
    public function getOpenStackNetworkAndStorage($params)
    {
        $vcenteruuid = $params['vcenteruuid'];
        $hypervisor = $params['hypervisor'];
        $groupname = $params['groupname'];
        $groupuuid = $params['groupuuid'];
        $username = $params['username'];
        $password = $params['password'];
        $rootSize = intval($params['rootSize']);
        $zonename = $params['zonename'];
        $this->paramsCheck($vcenteruuid, $groupname, $username, $password);

        $network = array();
        $storage = array();
        $domain = array();
        $instance = array();
        $storage[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['WEB_AUTO_SELECT'],
            'totalsize' => 0,
            'freesize' => 0,
        );
        $network[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['WEB_AUTO_SELECT'],
        );
        $domain[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['WEB_AUTO_SELECT'],
        );
        $instance[] = array(
            'uuid' => '0',
            'text' => '',
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        //获取存储信息
        $opName = 'VM_VCENTER_OP_QUERY_OPENSTACK_STORAGE_LIST';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'zone_name' => $zonename,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $storageList = $mbResult['msg']['storage_resource_list'];
            foreach ($storageList as $list) {
                $storage[] = array(
                    'uuid' => $list['storage_uuid'],
                    'text' => $this->getHostStorageNameText($hypervisor, $list),
                    'totalsize' => $list['total_size'],
                    'freesize' => $list['free_size'],
                );
            }
        }

        //获取网络信息
        $opName = 'VM_VCENTER_OP_QUERY_USER_GROUP_PHYSICAL_NETWORK';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'zone_name' => $zonename,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $networkList = $mbResult['msg']['network_list'];
            foreach ($networkList as $list) {
                $network[] = array(
                    'uuid' => $list['network_uuid'],
                    'text' => $list['network_name'],
                );
            }
        }


        //获取可用域
        $opName = 'VM_VCENTER_OP_QUERY_AVAILABILITY_ZONE';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $domainList = $mbResult['msg']['availability_zone_list'];
            foreach ($domainList as $list) {
                $domain[] = array(
                    'uuid' => $list['zone_name'],
                    'text' => $list['zone_name'],
                );
            }
        }

        //获取实例类型
        $opName = 'VM_VCENTER_OP_QUERY_OPENSTACK_FLAVORS_LIST';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'zone_name' => $zonename,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $instanceList = $mbResult['msg']['flavors_list'];
            foreach ($instanceList as $list) {
                //只保留根磁盘大小和原来一样的
                //$rootSizeUnit = $rootSize/1024/1024/1024;//转换成GB
                //if($list['root_disk_size'] != $rootSizeUnit && Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM'] != $hypervisor) continue;
                $text = $list['flavor_name'] . "(" . $list['vcpu_num'] . "/" . round($list['ram_size'] / 1024, 1) . "GB " . "/" . $list['root_disk_size'] . "GB)";
                $uuid = $list['vcpu_num'] . "_" . round($list['ram_size'] / 1024, 1) . "_" . $list['root_disk_size'] . "_" . $list['flavor_id'];
                $instance[] = array(
                    'text' => $text,
                    'uuid' => $uuid,
                    'flavor_id' => $list['flavor_id']
                );
            }
        }
        $info = array(
            'network' => $network,
            'storage' => $storage,
            'domain' => $domain,
            'instance' => $instance
        );
        return json_encode($info);
    }

    /**
     * 获取私有云可用域
     * @param $params
     * @return false|string
     */
    public function getOpenStackAvailableDomain($params)
    {
        $vcenteruuid = $params['vcenteruuid'];
        $hypervisor = $params['hypervisor'];
        $groupname = $params['groupname'];
        $groupuuid = $params['groupuuid'];
        $username = $params['username'];
        $password = $params['password'];
        $region = $params['region'];
        $timepointuuid = $params['timepointuuid'];
        $this->paramsCheck($vcenteruuid, $groupname, $username, $password);

        $domain = array();
        $domain[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['UI_PUBLIC_SELECT'],
        );

        //获取可用域
        $opName = 'VM_VCENTER_OP_QUERY_AVAILABILITY_ZONE';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'region' => $region,
        );
        $msg = json_encode($msg);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $domainList = $mbResult['msg']['availability_zone_list'];
            foreach ($domainList as $list) {
                $domain[] = array(
                    'uuid' => $list['zone_name'],
                    'text' => $list['zone_name'],
                );
            }
        }
        // 获取时间点的可用域
        $sql = "select vm_config from vm_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, [$timepointuuid]);
        $vmConfig = json_decode($data[0]['vm_config'], true);
        $thisDomain = $vmConfig['availability_zone'] ?? '';
        return json_encode([
            'domain' => $domain,
            'thisDomain' => $thisDomain
        ]);
    }

    /**
     * 获取xhere块存储策略
     * @param  array $params
     * @return string
     */
    public function getXhereVolumePolicyList($params)
    {
        $vcenteruuid = $params['vcenteruuid'];
        $msg = array(
            'vcenter_uuid' => $vcenteruuid
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $msg = json_encode($msg);

        $opName = 'VM_VCENTER_OP_QUERY_VOLUME_POLICY_LIST';
        $mbResult = $this->mbVMMsg($nodeuuid, Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XHERE'], $opName, $msg, true);
        $this->writeLog(json_encode($mbResult));
        $info = [];
        if ($mbResult['result']) {
            $info = $mbResult['msg']['volume_policy_list'];
        }
        return json_encode($info);
    }

    /**
     * 得到网络和存储(某台宿主机)
     * @param unknown $params
     */
    public function getNetworkAndStorage($params)
    {
        $hypervisor = $params['hypervisor'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hypervisor, $vcenteruuid, $hostuuid);
        $submodule_type = $hypervisor;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $network = array();
        $storage = array();
        $storage[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['WEB_AUTO_SELECT'],
            'totalsize' => 0,
            'freesize' => 0,
        );
        $network[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['WEB_AUTO_SELECT'],
        );

        //获取存储信息
        $opName = 'VM_VCENTER_OP_QUERY_STORAGE';
        $msg = json_encode(array('vcenter_uuid' => $vcenteruuid, 'host_uuid' => $hostuuid));
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);

        //如果是租户内用户操作，检测是否已配置指定目标存储和网络
        $tenantDesStorage = "";
        $tenantNetwork = "";
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['recover']) {
                //指定目标存储
                $tenantDesStorage = $settings['recover']['desstorage'];
                $allStorageFlag = $settings['recover']['desstoragetype'] == "0";

                //指定目标网络
                $tenantNetwork = $settings['recover']['network'];
                $allNetworkFlag = $settings['recover']['networktype'] == "0";
            }
        }

        if ($mbResult['result']) {
            $storageList = $mbResult['msg']['storage_resource_list'];
            foreach ($storageList as $list) {
                //指定目标存储
                if (!empty($_SESSION['tenantuuid']) && !$allStorageFlag) {
                    if ($list['storage_uuid'] != $tenantDesStorage)
                        continue;
                }
                if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'] == $hypervisor && 'netfs' == $list['driver_type']) {
                    continue;
                }
                $storage[] = array(
                    'uuid' => $list['storage_uuid'],
                    'text' => $this->getHostStorageNameText($hypervisor, $list),
                    'totalsize' => $list['total_size'],
                    'freesize' => $list['free_size'],
                );
            }
        }
        //获取网络信息
        $opName = 'VM_VCENTER_OP_QUERY_HOST_NETWORK_LIST';
        $msg = json_encode(array('vcenter_uuid' => $vcenteruuid, 'host_uuid' => $hostuuid));
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);
        if ($mbResult['result']) {
            $networkList = $mbResult['msg']['network_list'];

            foreach ($networkList as $list) {
                //指定目标网络
                if (!empty($_SESSION['tenantuuid']) && !$allNetworkFlag) {
                    if ($list['network_uuid'] != $tenantNetwork)
                        continue;
                }
                $network[] = array(
                    'uuid' => $list['network_uuid'],
                    'text' => $this->getHostNetworkNameText($list)
                );
            }
        }
        $info = array(
            'network' => $network,
            'storage' => $storage,
            'vcenter_type' => $this->getVcenterType($vcenteruuid)
        );
        return json_encode($info);
    }

    /**
     * 根据虚拟化类型得到存储显示的名字
     * XenServer只有驱动器类型
     * VMware只有文件系统类型
     * @param unknown $hypervisor
     * @param unknown $storage
     */
    private function getHostStorageNameText($hypervisor, $storage)
    {
        $utils = Xphp::instance("Utils");
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware']) || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']) {
            //如果是VMware
            $typeStr = 'filesystem_type';
        } elseif (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
            //如果是openstack就不显示存储类型
            $text = $storage['storage_uuid'];

            return $text;
        } else {
            //如果是XenServer,KVM
            $typeStr = 'driver_type';
        }
        $text = $storage['storage_name'] . "(" . $storage[$typeStr] .
            ", " . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($storage['total_size']) .
            ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($storage['free_size']) . ")";

        return $text;
    }

    /**
     * 得到宿主机网卡的名字显示
     * @param unknown $network
     */
    private function getHostNetworkNameText($network)
    {
        $text = $network['network_name'];
        if (!empty($network['ip_address'])) {
            $text .= "(" . $network['ip_address'] . ")";
        }
        return $text;
    }

    /**
     * 得到恢复机器的存储信息(迁移)
     * @param unknown $params
     */
    public function getStoreDev($params)
    {
        $hypervisor = $params['hypervisor'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hypervisor, $vcenteruuid, $hostuuid);
        $submodule_type = $hypervisor;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $opName = 'VM_VCENTER_OP_QUERY_STORAGE';
        $msg = json_encode(array('vcenter_uuid' => $vcenteruuid, 'host_uuid' => $hostuuid));
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);

        if ($mbResult['result']) {
            //获取消息成功,组合消息
            $records = array("data" => array());
            $records["draw"] = $params['draw'];
            $list = $mbResult['msg']['storage_resource_list'];
            $count = count($list);
            $records["recordsTotal"] = $count;
            $records["recordsFiltered"] = $count;
            $utils = Xphp::instance('Utils');
            foreach ($list as $d) {
                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="' . $d['storage_name'] . '">',
                    $d['storage_name'],
                    $d['driver_type'],
                    $utils->calSize($d['total_size']),
                    $utils->calSize($d['used_size']),
                    $utils->calSize($d['free_size']),
                    $d['filesystem_type'],
                );
            }
            return json_encode($records);
        } else {
            $vmOpcode = Xphp::instance('VMOpcode');
            $operate = $vmOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate);
        }
    }

    /**
     * 得到恢复按时间点分组显示方式树
     * @param string $nodeuuid  节点UUID
     * @param boolean  $instantflag 瞬时恢复标志
     */
    private function getTimepointTimeGroup($nodeuuid, $instantflag)
    {
        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_type, bbt.module_type,
                        bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type in (2,9) and
                       bbt.user_uuid = ? and data_local_flag = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_user['useruuid'], $flag['SET']);
        if ($nodeuuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?";
            array_push($sqlParams, $nodeuuid);
        }
        $sql .= " order by  vbt.vm_uuid,  vbt.vm_timepoint_id desc  ";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        //定义task vm timepoint
        $hypervisor = array();
        $timepoint = array();
        $task = array();
        foreach ($data as $d) {
            if ($instantflag) {
                //如果是瞬时恢复，hyper-v不支持
                if (intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']) {
                    continue;
                }
            }
            $taskuuid = $d['task_uuid'];
            $vmuuid = $d['vm_uuid'];
            $timepointuuid = $d['timepoint_uuid'];
            $taskCreateTime = $d['task_create_time'];
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor)) {
                $hypervisorType = intval($d['hypervisor_type']);
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $d['hypervisor_type'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => true,
                    "type" => -1,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, false),
                );
                $hypervisor[] = $hypervisorType;
            }
            //检查并添加task
            if (!in_array($d['hypervisor_type'] . $taskuuid, $task)) {
                $task[] = $d['hypervisor_type'] . $taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $d['task_name'] : $d['task_name'] . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                if ($d['module_type'] == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']) {
                    $name = $d['task_name'] . "(" . Xphp::$_lang['UI_COPY_DATA'] . ")";
                }
                $node[] = array(
                    "id" => $d['hypervisor_type'] . $taskuuid,
                    "pId" => $d['hypervisor_type'],
                    "name" => $name,
                    "open" => false,
                    "nocheck" => true,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            }

        }
        return json_encode($node);
    }

    /**
     * 得到虚拟机备份点,创建恢复任务/瞬时恢复任务/备份数据管理
     * @param unknown $params
     */
    public function getTimepoint($params)
    {
        $showtype = intval($params['showtype']);
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $instantflag = $params['instantflag'];      //瞬时恢复标志,瞬时恢复的时候在按时间点分组展示方式的时候,时间点没有选择框
        $manageflag = $params['manageflag'];       //备份数据管理标志,备份数据管理的树带有checkbox
        $disabledflag = $params['disabledflag'];    //备份数据禁用勾选增量差异标志
        $exportflag = $params['exportflag'];        //备份数据导出的标志,暂时只支持VMware备份数据导出
        $this->paramsCheck($showtype);
        if ($showtype == Xphp::$_config['POINTSHOWTYPE']['TIMEGROUP']) {
            //如果是按时间点方式显示
            return $this->getTimepointTimeGroup($nodeuuid, $instantflag);
        }

        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
                       unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and
                       bbt.user_uuid = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array(
            $flag['UNSET'],
            $flag['SET'],
            $flag['UNSET'],
            Xphp::$_config['MODULE_TYPE']['VM'],
            Xphp::$_user['useruuid']
        );
        if ($nodeuuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?";
            array_push($sqlParams, $nodeuuid);
        }
        $sql .= " order by  vbt.vm_uuid,  vbt.vm_timepoint_id desc  ";


        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();




        //定义task vm timepoint
        $hypervisor = array();
        $vm = array();
        $timepoint = array();
        $task = array();
        $pid = null;

        foreach ($data as $d) {
            if ($exportflag) {
                //如果是备份数据导出任务创建,这里只显示VMware的,其他的虚拟化类型暂时过滤,以后支持了之后再添加
                if (!in_array(intval($d['hypervisor_type']), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])) {
                    continue;
                }
            }
            $taskuuid = $d['task_uuid'];
            $vmuuid = $d['vm_uuid'];
            $timepointuuid = $d['timepoint_uuid'];
            $taskCreateTime = $d['task_create_time'];
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor)) {
                $hypervisorType = intval($d['hypervisor_type']);
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $d['hypervisor_type'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => !$manageflag,
                    "type" => -1,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, false),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                );
                $hypervisor[] = $hypervisorType;
            }



            //检查并添加task
            if (!in_array($taskuuid, $task)) {
                $task[] = $taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $node[] = array(
                    "id" => $taskuuid,
                    "pId" => $d['hypervisor_type'],
                    "name" => $taskAvailable ? $d['task_name'] : $d['task_name'] . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")",
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                );
            }

            //检查并添加vm
            if (!in_array($vmuuid . $taskuuid, $vm)) {
                $vm[] = $vmuuid . $taskuuid;
                $node[] = array(
                    "id" => $vmuuid . $taskuuid,
                    "pId" => $taskuuid,
                    "name" => $d['vm_name'],
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 1,
                    "icon" => './img/vm/vm.png',
                    "iconSkin" => 'vm',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "vmuuid" => $d['vm_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "createtime" => $this->parseDate($d['task_create_time']),
                    "hypervisor" => $d['hypervisor_type'],
                );
            } else {
                continue;
            }



            $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
                       unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = ? and
                       bbt.task_uuid = ?  and
                       vbt.vm_uuid = ? order by vbt.vm_uuid,  bbt.timepoint";

            $sqlParams = array(
                $flag['UNSET'],
                $flag['SET'],
                $flag['UNSET'],
                Xphp::$_config['MODULE_TYPE']['VM'],
                $taskuuid,
                $vmuuid
            );

            $pointData = $this->dbSelect($sql, $sqlParams);
            $pid = $vmuuid . $taskuuid;
            foreach ($pointData as $point) {
                $taskuuid = $point['task_uuid'];
                $vmuuid = $point['vm_uuid'];
                $timepointuuid = $point['timepoint_uuid'];
                $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
                //检查并添加完备点
                if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                    if (!in_array($timepointuuid, $timepoint)) {
                        $timepoint[] = $timepointuuid;
                        $node[] = array(
                            "id" => $timepointuuid,
                            "pId" => $vmuuid . $taskuuid,
                            "name" => $this->parseDate($point['timepoint']) . "(" . $this->getTimepointTypeDes($point['backup_mode']) . ")",
                            "checked" => false,
                            "type" => 3,
                            "vmuuid" => $point['vm_uuid'],
                            "vmname" => $point['vm_name'],
                            "pointname" => $this->parseDate($point['timepoint']),
                            "vcenteruuid" => $point['vcenter_uuid'],
                            "timepointuuid" => $timepointuuid,
                            "createtime" => $taskCreateTimeIn,
                            "hypervisor" => $point['hypervisor_type'],
                            "nodeuuid" => $point['node_uuid'],
                            "taskuuid" => $taskuuid,
                            "path" => $point['dir_path'],
                            "version" => $point['version'],
                            "icon" => $this->getTimepointIcon($point['backup_mode']),
                            "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                            "hypervisor" => $d['hypervisor_type'],
                        );
                        //添加了完全备份时间点继续下一次
                        $pid = $timepointuuid;
                        continue;
                    }
                }


                $node[] = array(
                    "id" => $timepointuuid,
                    "pId" => $pid,
                    "name" => $this->parseDate($point['timepoint']) . "(" . $this->getTimepointTypeDes($point['backup_mode']) . ")",
                    "checked" => false,
                    "type" => 4,
                    "vmuuid" => $point['vm_uuid'],
                    "vmname" => $point['vm_name'],
                    "pointname" => $this->parseDate($point['timepoint']),
                    "vcenteruuid" => $point['vcenter_uuid'],
                    "nodeuuid" => $point['node_uuid'],
                    "timepointuuid" => $timepointuuid,
                    "hypervisor" => $point['hypervisor_type'],
                    "path" => $point['dir_path'],
                    "version" => $point['version'],
                    "icon" => $this->getTimepointIcon($point['backup_mode']),
                    "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                    "chkDisabled" => $disabledflag,
                );


            }
        }

        return json_encode($node);
    }


    /**
     * 备份数据管理,得到备份数据任务树
     * @param unknown $params
     */
    public function getTimepointTree($params)
    {
        $showtype = intval($params['showtype']);
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $instantflag = $params['instantflag'];      //瞬时恢复标志,瞬时恢复的时候在按时间点分组展示方式的时候,时间点没有选择框
        $grainflag = $params['grainflag'];          //细粒度恢复标记
        $manageflag = $params['manageflag'];       //备份数据管理标志,备份数据管理的树带有checkbox
        $disabledflag = $params['disabledflag'];    //备份数据禁用勾选增量差异标志
        $exportflag = $params['exportflag'];        //备份数据导出的标志,暂时只支持VMware备份数据导出
        $dataflag = $params['dataflag'];			//备份数据标志
        if ($showtype == Xphp::$_config['POINTSHOWTYPE']['TIMEGROUP']) {
            //如果是按时间点方式显示
            return $this->getTimepointTimeGroup($nodeuuid, $instantflag);
        }
        $this->paramsCheck($showtype);
        $sql = "select bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type,
    			bbt.task_uuid, bbt.user_uuid, bbt.user_name, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_config, vbt.vm_name, vbt.dir_path, vbt.hypervisor_type, vbt.version, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and 
                       bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type = " . Xphp::$_config['MODULE_TYPE']['VM'] . "  and bbt.data_local_flag = ?
	                   and vbt.hypervisor_type != ?";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['SET'], $flag['UNSET'], $flag['SET'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_AWS']);

        //租户管理员特殊处理数据显示
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($_SESSION['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($dataflag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                $sql .= " and bbt.user_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            }
        }

        //备份数据页面
        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], Xphp::$_config['MODULE_TYPE']['VM']));
        }

        if ($nodeuuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $sql .= " group by vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, bbt.task_uuid, bbt.task_name order by bbt.task_name, vbt.vm_name, max(bbt.timepoint) desc ";


        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        //定义task vm timepoint
        $hypervisor = array();
        $task = array();
        $vm = array();
        $pid = null;
        $jobHandler = Xphp::instance('JobHandler');
        foreach ($data as $d) {
            $vmConfig = json_decode($d['vm_config'], true);
            //如果是细粒度恢复，暂时不支持hyper-v
//             if($grainflag && intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
//                 continue;
//             }
            if ($exportflag) {
                //如果是备份数据导出任务创建,这里只显示VMware的,其他的虚拟化类型暂时过滤,以后支持了之后再添加
                if (!in_array(intval($d['hypervisor_type']), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])) {
                    continue;
                }
            }
            if ($instantflag) {
                //如果是瞬时恢复，hyper-v、smartx不支持

                if (
                    intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] ||
                    intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM']
                ) {
                    continue;
                }
            }

            $taskuuid = $d['task_uuid'];
            $taskCreateTime = $this->getTaskNewModifyTimeFromBdbackupTimepoint($d['task_uuid']);
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor)) {
                $hypervisorType = intval($d['hypervisor_type']);
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $d['hypervisor_type'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => !$manageflag,
                    "type" => -1,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, false),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                );
                $hypervisor[] = $hypervisorType;
            }
            $hypervisortype = $d['hypervisor_type'];

            $taskName = $jobHandler->getTimepointTaskname($d['task_uuid'], $d['task_name']);
            //检查并添加task
            if (!in_array($hypervisortype . $taskuuid, $task)) {
                $task[] = $hypervisortype . $taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                // 副本数据
                if ($d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']) {
                    $name = $taskName . "(" . Xphp::$_lang['UI_COPY_DATA'] . ")";
                }
                // 归档数据
                if ($d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']) {
                    $name = $taskName . "(" . Xphp::$_lang['UI_ARCHIVE_DATA'] . ")";
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != Xphp::$_user['useruuid']) {
                    $name .= "(" . $d['user_name'] . ")";
                }
                $node[] = array(
                    "id" => $hypervisortype . $taskuuid,
                    "pId" => $d['hypervisor_type'],
                    "name" => $name,
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . $taskCreateTime,
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                );
            }
            $vmuuid = $d['vm_uuid'];
            //检查并添加vm
            if (!in_array($hypervisortype . $vmuuid . $taskuuid, $vm)) {
                $vm[] = $hypervisortype . $vmuuid . $taskuuid;
                $vmname = $d['vm_name'];
                if (in_array($hypervisortype, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
                    $ipList = $vmConfig['network_list'][0]['ip_list'];
                    if (!empty($ipList)) {
                        $vmname .= "(" . $ipList[0]['ipaddr'] . ")";
                    }
                }
                $iconSkin = 'vm';
                $icon = './img/vm/vm.png';
                //如果是华为CBR中的存储库、时间点 需要修改图标
                if ($hypervisortype == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CBR']) {
                    if ($d['version'] == 1) {
                        $iconSkin = 'vm_huawei_cbr_memorypool';
                        $icon = '';
                    } else if ($d['version'] == 3) {
                        $iconSkin = 'vm_huawei_cbr_timepoint';
                        $icon = '';
                    }
                }
                $node[] = array(
                    "id" => $hypervisortype . $vmuuid . $taskuuid,
                    "pId" => $hypervisortype . $taskuuid,
                    "name" => $vmname,
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 1,
                    "icon" => $icon,
                    "iconSkin" => $iconSkin,
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "vmuuid" => $d['vm_uuid'],
                    "taskuuid" => $taskuuid,
                    "nodeuuid" => $d['node_uuid'],
                    "createtime" => $taskCreateTime,
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            } else {
                continue;
            }

        }
        return json_encode($node);
    }

    /**
     * 根据任务uuid获取最新的任务修改时间
     * 主要用于时间点树通过group筛选后，任务创建修改时间没有到最新的情况，这里只能从bd_backup_timepoint中获取，因为任务可能已经删除
     * @param unknown $taskuuid
     */
    private function getTaskNewModifyTimeFromBdbackupTimepoint($taskuuid)
    {
        $sql = "select task_create_time from bd_backup_timepoint where task_uuid = ? order by id desc limit 0, 1";
        $data = $this->dbSelect($sql, array($taskuuid));
        return $data[0]['task_create_time'];
    }

    /**
     * 异步获取按时间点显示虚拟机
     * @param unknown $params
     */
    public function getSyncTimepointTimeGroup($params)
    {
        $taskuuid = $params['id'];
        $nodeuuid = $params['nodeuuid'];
        $hypervisor = $params['hypervisor'];
        $instantflag = $params['instantflag'];      //瞬时恢复标志,瞬时恢复的时候在按时间点分组展示方式的时候,时间点没有选择框
        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode,
                       bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) task_create_time,
		               vbt.hypervisor_type,vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid,bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type in (2,9) and
                       bbt.user_uuid = ? and bbt.task_uuid= ? and vbt.hypervisor_type = ? and data_local_flag = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], Xphp::$_user['useruuid'], $taskuuid, $hypervisor, $flag['SET']);
        if ($nodeuuid) {
            $sql .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $sql .= " order by  vbt.vm_uuid,  vbt.vm_timepoint_id desc";
        $data = $this->dbSelect($sql, $sqlParams);
        //添加时间点
        $timepoint = array();
        $node = array();
        foreach ($data as $d) {
            if (!in_array($d['hypervisor_type'] . $d['timepoint_uuid'] . $taskuuid, $timepoint)) {
                $timepoint[] = $d['hypervisor_type'] . $d['timepoint_uuid'] . $taskuuid;
                $node[] = array(
                    "id" => $d['hypervisor_type'] . $d['timepoint_uuid'] . $taskuuid,
                    "pId" => $d['hypervisor_type'] . $taskuuid,
                    "name" => $this->parseDate($d['timepoint']) . "(" . $this->getTimepointTypeDes($d['backup_mode']) . ")",
                    "checked" => false,
                    "nocheck" => !!$instantflag,
                    "type" => 1,
                    "vmuuid" => $d['vm_uuid'],
                    "vmname" => $d['vm_name'],
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "timepointuuid" => $d['timepoint_uuid'],
                    "hypervisor" => $hypervisor,
                    "nodeuuid" => $d['node_uuid'],
                    "path" => $d['dir_path'],
                    "version" => $d['version'],
                    "icon" => $this->getTimepointIcon($d['backup_mode']),
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
                );
            }

            //添加虚拟机
            $node[] = array(
                "id" => $d['vcenter_uuid'] . $d['vm_uuid'],
                "pId" => $d['hypervisor_type'] . $d['timepoint_uuid'] . $taskuuid,
                "name" => $d['vm_name'],
                "vmname" => $d['vm_name'],
                "open" => false,
                "nocheck" => false,
                "type" => 2,
                "icon" => './img/vm/vm.png',
                "iconSkin" => 'vm',
                "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
                "vmuuid" => $d['vm_uuid'],
                "vcenteruuid" => $d['vcenter_uuid'],
                "timepointuuid" => $d['timepoint_uuid'],
                "hypervisor" => $hypervisor,
                "nodeuuid" => $d['node_uuid'],
                "pointname" => $this->parseDate($d['timepoint']),
                "path" => $d['dir_path'],
            );
        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }


    /**
     * 异步获取每个任务对应的虚拟机时间点
     * @param unknown $params
     */
    public function getSyncTimepoint($params)
    {
        $taskuuid = $params['taskuuid'];
        $hypervisor = $params['hypervisor'];
        $vmuuid = $params['vmuuid'];
        $disabledflag = $params['disabledflag'];    //备份数据禁用勾选增量差异标志
        $manageflag = $params['manageflag'];       //备份数据管理标志,备份数据管理的树带有checkbox
        $vmcheck = $params['vmcheck'];
        $nodeuuid = $params['nodeuuid'];
        $storageUuid = $params['storageuuid'];

        $newinstantflag = $params['newinstantflag']; //瞬时恢复及细粒度恢复标志
        $newgrainflag = $params['newgrainflag'];
        $node = array();
        $sqlPoint = "select distinct bbt.detail, bbt.deleted_flag, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
                      unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,bbt.weekly_flag,bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag, bbt.storage_uuid,
		              vbt.vm_uuid, 
		              (SELECT vbt2.vm_name FROM vm_backup_timepoint vbt2 JOIN bd_backup_timepoint bbt2 ON vbt2.timepoint_uuid = bbt2.timepoint_uuid 
                        WHERE bbt2.task_uuid = ? AND vbt2.vm_uuid = vbt.vm_uuid ORDER BY vbt2.vm_timepoint_id DESC LIMIT 1) as current_vm_name, 
		              vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.node_uuid, bsr.status, bsr.storage_type 
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = ?
	                   and bbt.import_flag = ?  and
                       bbt.task_uuid = ?  and
                       vbt.vm_uuid = ? and vbt.hypervisor_type = ? and data_local_flag = ? ";


        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($taskuuid, $flag['SET'], $flag['UNSET'], $taskuuid, $vmuuid, $hypervisor, $flag['SET']);
        if (!empty($nodeuuid)) {
            $sqlPoint .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        if ($storageUuid) {
            $sqlPoint .= ' and bsr.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        if ($newinstantflag || $newgrainflag) {
            //瞬时恢复和细粒度恢复排除云上的时间点 也要排除CBR的时间点
            $sqlPoint .= " and bsr.storage_type  not in (" . Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'] . "," . Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'] . ") ";
        }
        $sqlPoint .= " order by vbt.vm_uuid,  bbt.timepoint";
        $pointData = $this->dbSelect($sqlPoint, $sqlParams);
        $pid = $vmuuid . $taskuuid;

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($pointData as $point) {
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $unfullList[] = $point;

            }
        }
        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $k => $value) {
                    if ($dependId == $k) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$k];
                        $unfullList = array_splice($unfullList, $key, 1);

                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;

        }
        $timepoint = array();
        $utils = Xphp::instance('Utils');
        foreach ($pointData as $point) {
            $availableFlag = true;

            //如果时间点正在合并且不可用
            if ($point['archive_flag'] == Xphp::$_config['FLAG']['SET'] || $point['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']) {
                $availableFlag = false;
            }


            //判断是合并中还是离线状态
            $archive_status_str = true;
            $vm_status_str = true;

            //删除中
            $delete_status_str = false;

            //删除中
            if ($point['archive_flag'] == Xphp::$_config['FLAG']['UNSET'] && $point['deleted_flag'] == Xphp::$_config['FLAG']['SET']) {
                //如果是在恢复页面不可用
                if (!$manageflag) {
                    $availableFlag = false;
                }
                $delete_status_str = true;
            }

            //如果是合并中
            if ($point['archive_flag'] == Xphp::$_config['FLAG']['SET']) {
                $archive_status_str = false;
            }
            //如果不是在线状态
            if ($point['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']) {
                $vm_status_str = false;
            }

            $taskuuid = $point['task_uuid'];
            $vmuuid = $point['vm_uuid'];
            $timepointuuid = $point['timepoint_uuid'];
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
            $config = json_decode($point['detail'], true);
            if ($manageflag) {
                $mark = $this->pGetTimepointMark($utils->parseFlagToBool($point['weekly_flag']), $utils->parseFlagToBool($point['monthly_flag']), $utils->parseFlagToBool($point['yearly_flag']), $utils->parseFlagToBool($point['importance_flag']));
            } else {
                $mark = "";
            }

            //标记列表
            $markList = array(
                'weekly_flag' => $utils->parseFlagToBool($point['weekly_flag']),
                'monthly_flag' => $utils->parseFlagToBool($point['monthly_flag']),
                'yearly_flag' => $utils->parseFlagToBool($point['yearly_flag']),
                'importance_flag' => $utils->parseFlagToBool($point['importance_flag']),
            );
            //备份数据显示备注
//             if($manageflag && !empty($point['remarks'])){
//                 $remark = '<a id="remark_'.$timepointuuid.'" style="display:inline-block;color: #5b9bd1;position: relative;top:5px;left:-4px;" class="popovers remarktips" data-container="body" data-trigger="hover"
//                             data-placement="right" data-content="'.$point['remarks'].'"><i class="viconfont vicon-tishi" style="font-size: 21px !important; position:relative;top:-4px;left:-4px;"></i></a>';
//             }else{
//                 $remark = "";
//             }

            //检查并添加完备点
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                if (!in_array($timepointuuid, $timepoint)) {
                    $name = $this->parseDate($point['timepoint']) . " (" . $this->getTimepointTypeDes($point['backup_mode']) . ")";
                    //根据合并和是否在线展示不同文字信息
                    if (!$archive_status_str) {
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_IN_MERGE'] . ")";
                    } else if ($delete_status_str) {
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_MERGE_ERROR'] . ")";
                    }
                    if (!$vm_status_str) {
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_STORAGE_OFF'] . ")";
                    }

                    if (!empty($config) && $config['password_auto_flag'] == 2 && !empty($config['password'])) {
                        $name .= '<i class="fa fa-lock"></i>';
                    }
                    $timepoint[] = $timepointuuid;
                    $vmName = $point['current_vm_name'] ?: $point['vm_name'];
                    $node[] = array(
                        "id" => $timepointuuid,
                        "pId" => $point['hypervisor_type'] . $vmuuid . $taskuuid,
                        "name" => $name . $mark,
                        "oldname" => $name,
                        "checked" => $vmcheck && $availableFlag,
                        "type" => 3,
                        "vmuuid" => $point['vm_uuid'],
                        "vmname" => rawurldecode($vmName),
                        "pointname" => $this->parseDate($point['timepoint']),
                        "vcenteruuid" => $point['vcenter_uuid'],
                        "timepointuuid" => $timepointuuid,
                        "createtime" => $taskCreateTimeIn,
                        "nodeuuid" => $point['node_uuid'],
                        "taskuuid" => $taskuuid,
                        "path" => htmlspecialchars($point['dir_path']),
                        "version" => $point['version'],
                        "icon" => $this->getTimepointIcon($point['backup_mode']),
                        "title" => htmlspecialchars(rawurldecode($this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']))),
                        "hypervisor" => $hypervisor,
                        "chkDisabled" => !$availableFlag,
                        "config" => $config,
                        "markList" => $markList,
                        "remark" => $point['remarks'],
                        "storageuuid" => $point['storage_uuid'],
                        "storagetype" => $point['storage_type']
                    );
                    //添加了完全备份时间点继续下一次
                    $pid = $timepointuuid;
                    continue;
                }
            }

            $name = $this->parseDate($point['timepoint']) . " (" . $this->getTimepointTypeDes($point['backup_mode']) . ")";
            //根据合并和是否在线展示不同文字信息
            if (!$archive_status_str) {
                $name .= "(" . Xphp::$_lang['UI_PUBLIC_IN_MERGE'] . ")";
            } else if ($delete_status_str) {
                $name .= "(" . Xphp::$_lang['UI_PUBLIC_MERGE_ERROR'] . ")";
            }
            $vmName = $point['current_vm_name'] ?: $point['vm_name'];

            $node[] = array(
                "id" => $timepointuuid,
                "pId" => $fulluuidList[$timepointuuid],
                "name" => $name . " " . $mark,
                "oldname" => $name,
                "checked" => $vmcheck && $availableFlag,
                "type" => 4,
                "vmuuid" => $point['vm_uuid'],
                "vmname" => rawurldecode($vmName),
                "pointname" => $this->parseDate($point['timepoint']),
                "vcenteruuid" => $point['vcenter_uuid'],
                "nodeuuid" => $point['node_uuid'],
                "timepointuuid" => $timepointuuid,
                "hypervisor" => $point['hypervisor_type'],
                "path" => htmlspecialchars($point['dir_path']),
                "version" => $point['version'],
                "icon" => $this->getTimepointIcon($point['backup_mode']),
                "title" => htmlspecialchars(rawurldecode($this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']))),
                "chkDisabled" => $disabledflag || !$availableFlag,
                "config" => $config,
                "markList" => $markList,
                "remark" => $point['remarks'],
                "storageuuid" => $point['storage_uuid'],
                "storagetype" => $point['storage_type']
            );


        }
        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);

    }

    /**
     * 公共方法
     * 得到标记图标,即WMYF的标记  表示GFS的W周,M月,Y年,F星标
     * 传入参数都为布尔值
     * @param boolean $Wflag 保留周标记
     * @param boolean $Mflag 保留月标记
     * @param boolean $Yflag 保留年标记
     * @param boolean $Fflag 永久保留标记
     */
    public function pGetTimepointMark($Wflag, $Mflag, $Yflag, $Fflag)
    {
        $markStr = '';
        if ($Wflag) {
            $markStr .= '<i title="' . Xphp::$_lang['WEB_VM_GFS_WEEK_POINT'] . '" class="viconfont vicon-remark-week"></i>';
        }
        if ($Mflag) {
            $markStr .= '<i title="' . Xphp::$_lang['WEB_VM_GFS_MONTH_POINT'] . '" class="viconfont vicon-remark-month"></i>';
        }
        if ($Yflag) {
            $markStr .= '<i title="' . Xphp::$_lang['WEB_VM_GFS_YEAR_POINT'] . '" class="viconfont vicon-remark-year"></i>';
        }
        if ($Fflag) {
            $markStr .= '<i title="' . Xphp::$_lang['WEB_VM_GFS_FOREVER_POINT'] . '" class="viconfont vicon-remark-forever"></i>';
        }
        return $markStr;
    }

    /**
     * 得到虚拟机备份时间点的备注信息,
     * 如果有备注添加到虚拟机源路径后面就是
     * @param string $dirPath
     * @param string $remarks
     */
    public function getBackupTimepointTreeTitle($dirPath, $remarks)
    {
        $title = Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $dirPath;
        if (!empty($remarks)) {
            $title .= "  " . Xphp::$_lang['UI_PUBLIC_REMARK'] . ": " . $remarks;
        }
        return $title;
    }

    /**
     * 根据备份模式得到时间点图标
     * @param int $backupMode
     */
    public function getTimepointIcon($backupMode)
    {
        $backupMode = intval($backupMode);
        $icon = "./img/platform/timepoint.png";
        switch ($backupMode) {
            case Xphp::$_config['BACKUP_MODE']['FULL']:
                $icon = "./img/platform/timepoint-f.png";
                break;
            case Xphp::$_config['BACKUP_MODE']['INCREMENTAL']:
                $icon = "./img/platform/timepoint-i.png";
                break;
            case Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']:
                $icon = "./img/platform/timepoint-d.png";
                break;
        }
        return $icon;
    }

    /**
     * 根据虚拟化类型得到虚拟化中心名称显示
     * @param int $hypervisor   虚拟化类型
     * @param array $vcenterArr 虚拟化中心数据库信息
     */
    private function getRecoverHostName($hypervisor, $vcenterArr)
    {
        $name = $vcenterArr['vcenter_ip'] == $vcenterArr['nickname'] ? $vcenterArr['vcenter_ip'] : $vcenterArr['nickname'] . "(" . $vcenterArr['vcenter_ip'] . ")";
        if (in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['xenserver'])) {
            //如果是XenServer
            if ($vcenterArr['vcenter_flag'] == Xphp::$_config['FLAG']['SET']) {
                //如果是vcenter
                $name = $vcenterArr['vcenter_name'] . " (" . Xphp::$_lang['WEB_VM_VCENTER_XENSERVER_MASTER_NODE'] . ":" . $vcenterArr['vcenter_ip'] . ")";
            } else {
                $name = $vcenterArr['vcenter_name'] . " (" . $vcenterArr['vcenter_ip'] . ")";
            }
        } else if (in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
            $detail = json_decode($vcenterArr['detail'], true);
            $name = $name . '(' . $vcenterArr['username'] . ')';
        }
        return htmlspecialchars_decode($name);
    }

    /**
     * 得到虚拟机恢复/瞬时恢复/迁移等可以到的目标虚拟化类型
     * @param int $hypervisor   源虚拟化类型
     */
    private function getVMRecoveryHypervisorArr($hypervisor)
    {
        //return Xphp::$_config['VMHYPERVISORTYPE']; //todo:新增虚拟化调试使用
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();

        $licFunction = $extension['f'];
        $crossHypervisorRecovery = $extension['crossHypervisorRecovery'];

        $ics_types = [
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'],
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
        ];
        if (in_array($hypervisor, $ics_types)) {
            //先检查是否选择了跨平台恢复功能,如果没有选择,只能恢复到ics/ics-vvdk
            if (!$licFunction['crossHypervisorRecovery'] || !$crossHypervisorRecovery[$hypervisor]) {
                return $ics_types;
            }

            //如果选择了跨平台恢复,返回跨平台恢复授权中定义的内容+ics+ics-vvdk
            return array_merge($crossHypervisorRecovery[$hypervisor], $ics_types);
        }

        // hci/scp无需v2v授权即可相互恢复
        $sangfor_types = [
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM'],
            Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
        ];
        if (in_array($hypervisor, $sangfor_types)) {
            //先检查是否选择了跨平台恢复功能,如果没有选择,只能恢复到hci和scp
            if (!$licFunction['crossHypervisorRecovery'] || !$crossHypervisorRecovery[$hypervisor]) {
                return $sangfor_types;
            }
            //如果选择了跨平台恢复,返回跨平台恢复授权中定义的内容+hci和scp
            return array_merge($crossHypervisorRecovery[$hypervisor], $sangfor_types);
        }

        //先检查是否选择了跨平台恢复功能,如果没有选择,只能恢复到本平台
        if (!$licFunction['crossHypervisorRecovery']) {
            return [$hypervisor];
        }

        //如果选择了跨平台恢复,返回跨平台恢复授权中定义的内容
        return $crossHypervisorRecovery[$hypervisor] ?: [$hypervisor];
    }

    /**
     * 得到恢复的宿主机
     * @param unknown $params ['type'] 虚拟化类型
     */
    public function getRecoverHost($params)
    {
        $tenantFlag = $params['tenantFlag'];    //租户处
        $hypersior = intval($params['type']);   //虚拟化类型
        $instantFlag = $params['instantflag'];  //瞬时恢复标志
        $oneHypersiorFlag = $params['onehypersiorflag'];    //是否只显示一个虚拟化,迁移的时候用

        //获取当前用户所有拥有虚拟机
        $vcenterList = array();
        $resourceHandler = Xphp::instance('ResourceHandler');
        $vmList = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'], "tree_id", "desc", $limit = 0, "all");
        foreach ($vmList['data'] as $vm) {
            //获取分配资源所给的虚拟化中心
            if (!in_array($vm['vcenter_uuid'], $vcenterList)) {
                $vcenterList[] = $vm['vcenter_uuid'];
            }
        }

        $sql = "select user_uuid, vcenter_uuid, vcenter_ip, nickname, vcenter_flag, hypervisor_type, vcenter_name, detail, username from vm_vcenter
                where online_flag = ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET']);

        if ($oneHypersiorFlag) {
            $sql .= " and hypervisor_type = ? ";
            $sqlParams = array(Xphp::$_config['FLAG']['SET'], $hypersior);
        }

        $sql .= " order by hypervisor_type ";

        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();

        $tenantVcenter = "";
        $allVcenterFlag = false;  //租户内是否运行恢复到全部宿主机
        //如果是租户内用户操作，检测是否已配置指定虚拟化中心
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['recover']) {
                $tenantVcenter = $settings['recover']['vcenter'];
                $allVcenterFlag = $settings['recover']['hosttype'] == "0";
            }
        }
        $recoveryHypervisorArr = $this->getVMRecoveryHypervisorArr($hypersior);

        foreach ($data as $d) {
            //如果是瞬时恢复排除其他虚拟化恢复到smartx/hyper-v
            if (
                $instantFlag && $hypersior != intval($d['hypervisor_type']) && (
                    intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM'] ||
                    intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']
                )
            )
                continue;
            //指定虚拟化中心
            if (!empty($_SESSION['tenantuuid']) && !$allVcenterFlag) {
                if ($d['vcenter_uuid'] != $tenantVcenter)
                    continue;
            }

            //针对全局用户，虚拟化中心不属于该用户,也不是分配的资源直接排除
            if (Xphp::$_user['useruuid'] != $d['user_uuid'] && !in_array($d['vcenter_uuid'], $vcenterList))
                continue;

            if (!in_array($d['hypervisor_type'], $recoveryHypervisorArr) && !$tenantFlag) {
                //如果目标虚拟化未授权,不能恢复到这个虚拟化,跳过
                //TODO
                continue;
            }

            if (in_array($d['hypervisor_type'], Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
                // 排除公有云平台
                continue;
            }

            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                //                 "name" => $d['vcenter_ip'] == $d['nickname'] ? $d['vcenter_ip'] : $d['nickname'] . "(" . $d['vcenter_ip'] . ")",
                "name" => $this->getRecoverHostName($d['hypervisor_type'], $d),
                "open" => false,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => intval($d['hypervisor_type']),
                "type" => 1,
                "iconSkin" => $this->getHypervisorIcon($d['hypervisor_type'], false)
            );
            $tree[] = $node;
        }

        return json_encode($tree);

    }

    /**
     * 得到恢复目的用户组，适用于flexcloud and openstack
     * @param unknown $params
     */
    public function getRecoverUserGroup($params)
    {
        $hypervisorType = intval($params['type']);
        $this->paramsCheck($hypervisorType);
        $openstackGroup = implode("','", Xphp::$_config['VMHYPERVISORGROUP']['openstack']);

        $sql = "select vcenter_uuid, vcenter_ip, nickname, vcenter_flag, hypervisor_type, vcenter_name, detail, username from vm_vcenter
                where hypervisor_type in (14, 15 , 22)";
        $data = $this->dbSelect($sql, array());
        $tree = array();
        $vcenter = Xphp::instance('Vcenter');
        foreach ($data as $d) {
            $name = $this->getRecoverHostName($d['hypervisor_type'], $d);
            $status = $vcenter->getVcenterHostLisenceStatus($d['vcenter_uuid']);
            if (!empty($status['statusDes'])) {
                $name = $name . '(' . $status['statusDes'] . ')';
            }
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                "name" => $d['vcenter_ip'] == $d['nickname'] ? $d['vcenter_ip'] : $d['nickname'] . "(" . $d['vcenter_ip'] . ")",
                "name" => $name,
                "open" => false,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => $hypervisorType,
                "type" => 1,
                "iconSkin" => $this->getHypervisorIcon($d['hypervisor_type'], false),
            );
            $tree[] = $node;
        }

        return json_encode($tree);

    }

    /**
     * 得到当前所有任务的UUID
     * return array
     */
    public function getCurrentAllTaskUUID()
    {
        $taskuuid = array();
        $sql = "select task_uuid from bd_task where module_type in(2, 3, 4, 9, 11) and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d) {
            $taskuuid[] = $d['task_uuid'];
        }
        return $taskuuid;
    }

    /**
     * 得到当前子预案的所有虚拟机
     * @param string $childuuid
     */
    private function getCurrentChildAllVM($childuuid)
    {
        $vms = array();
        if (empty($childuuid))
            return $vms;
        $sql = "select vm_uuid, vcenter_uuid from orch_plan_vm where child_uuid = ?";
        $data = $this->dbSelect($sql, array($childuuid));
        foreach ($data as $d) {
            $vms[] = array(
                'vmuuid' => $d['vm_uuid'],
                'vcenteruuid' => $d['vcenter_uuid']
            );
        }
        return $vms;
    }

    /**
     * 得到虚拟机备份数据树
     * 添加虚拟机到子预案使用
     * @param unknown $params
     */
    public function getVmDataTree($params)
    {
        //虚拟机是否带有选择框(添加虚拟机到子预案使用)
        $vmcheck = !!$params['vmcheck'];
        $childuuid = $params['childuuid'];
        //得到当前预案的所有虚拟机
        $childVMs = $this->getCurrentChildAllVM($childuuid);


        $hypervisorList = array(Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']);
        $type = implode("','", $hypervisorList);
        $sql = "select bbt.timepoint_uuid, bbt.module_type, bbt.timepoint, bbt.backup_mode, bbt.task_name, bbt.task_uuid, bbt.task_create_time,
        vbt.vm_uuid, vbt.vm_name, vbt.hypervisor_type, vbt.dir_path, vbt.vcenter_uuid
        from bd_backup_timepoint bbt, vm_backup_timepoint vbt
        where bbt.timepoint_uuid = vbt.timepoint_uuid
        and vbt.hypervisor_type in (' $type ')
        and bbt.deleted_flag = ?
        and bbt.import_flag = ? and bbt.available_flag = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sql .= "and user_uuid = ? order by vbt.vm_uuid, vbt.vm_timepoint_id desc ";
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        //定义hypervisor task  vm 数组
        $hypervisor = array();
        $tasktime = array();
        $vcenter = array();
        $vm = array();
        foreach ($data as $d) {
            $hypervisorType = $d['hypervisor_type'];
            $vcenteruuid = $d['vcenter_uuid'];
            $taskuuid = $d['task_uuid'];
            $vmuuid = $d['vm_uuid'];
            $taskCreateTime = $d['task_create_time'];   //利用创建时间来区分不同任务,修改任务后创建时间会改变
            //检查并添加hyppervisor
            if (!in_array($hypervisorType, $hypervisor)) {
                $hypervisor[] = $hypervisorType;
                $node[] = array(
                    "id" => $hypervisorType,
                    "pId" => 0,
                    "name" => Xphp::$_config['VMHYPERVISORDES'][$hypervisorType],
                    "open" => false,
                    "nocheck" => true,
                    "type" => 1,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, flase),
                );
            }
            //检查并添加vcenter
            if (!in_array($vcenteruuid, $vcenter)) {
                $vcenter[] = $vcenteruuid;
                $nameInfo = $this->getVcenterNameInfo($vcenteruuid);
                $node[] = array(
                    "id" => $vcenteruuid,
                    "pId" => $hypervisorType,
                    "name" => $nameInfo['name'],
                    "open" => false,
                    "nocheck" => true,
                    "type" => 2,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, $nameInfo['flag']),
                    "title" => $nameInfo['name']
                );
            }

            // //检查并添加task
            // if(!in_array($taskuuid, $tasktime)){
            //     $tasktime[] = $taskuuid;
            //     $taskAvailable = in_array($taskuuid, $currentTaskUUID);
            //     $node[] = array(
            //         "id" => $taskuuid,
            //         "pId" => $hypervisorType,
            //         "name" => $taskAvailable ? $d['task_name'] : $d['task_name'] . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")",
            //         "open" => false,
            //         "nocheck" => true,
            //         "type" => 2,
            //         "icon" => './img/platform/flag.png',
            //         "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . $d['task_create_time'],
            //     );
            // }
            //检查并添加vm
            if (!in_array($vcenteruuid . $vmuuid, $vm)) {
                $vm[] = $vcenteruuid . $vmuuid;
                $node[] = array(
                    "id" => $vcenteruuid . $vmuuid,
                    "pId" => $vcenteruuid,
                    "name" => $d['vm_name'],
                    "checked" => false,
                    "type" => 3,
                    "taskuuid" => $taskuuid,
                    "vcenteruuid" => $vcenteruuid,
                    "vmuuid" => $vmuuid,
                    "createtime" => $taskCreateTime,
                    "hypervisor" => $hypervisorType,
                    "icon" => './img/vm/vm.png',
                    "iconSkin" => 'vm',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
                    "dirpath" => $d['dir_path'],
                    //这两项添加子预案用
                    "nocheck" => !$vmcheck,
                    "chkDisabled" => $this->getPlanVMCheckDisabled($childVMs, $vmuuid, $d['vcenter_uuid']),
                );
            }
        }

        return json_encode($node);
    }

    /**
     * 得到添加子预案虚拟机的时候虚拟机的checkbox是否可用
     * 如果此虚拟机已经在子预案中,将不能再次选择
     * @param array $vms
     * @param string $vmuuid
     * @param string $vcenteruuid
     */
    private function getPlanVMCheckDisabled($vms, $vmuuid, $vcenteruuid)
    {
        $chkDisabled = false;
        if (empty($vms))
            return $chkDisabled;
        foreach ($vms as $vm) {
            if ($vm['vmuuid'] == $vmuuid && $vm['vcenteruuid'] == $vcenteruuid) {
                //如果存在,直接返回不可用
                return true;
            }
        }
        return $chkDisabled;
    }

    /**
     * 得到某虚拟机备份时间点表
     * @param unknown $params
     */
    public function getVmTimepointGrid($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $storageUuid = $params['storageuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $vmuuid = $params['vmuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $taskuuid = $params['taskuuid'];
        $createtime = $params['createtime'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $localflag = intval($params['localflag']); //本地标志
        $sortArr = array(
            '',
            'bbt.timepoint',
            'bbt.backup_mode',
            'bbt.total_size',
            'bbt.write_size',
            '',
            ''
        );

        $copyFlag = $params['copyFlag'];        //副本数据标志
        $archiveFlag = $params['archiveFlag'];  //归档数据标志
//         if($copyFlag || $archiveFlag){
//             return $this->getVmTimepointGridCopy($params);
//         }
        $storageuuid = $params['storageuuid'];

        //         编号	时间点	类型	数据大小	用户	备注	操作	星标
        $sql = "select bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.total_size, bbt.write_size,
		               bbt.data_local_flag, bbt.importance_flag, bbt.remarks, bbt.archive_flag, bbt.weekly_flag, bbt.monthly_flag, bbt.yearly_flag,
                       bsr.node_uuid, bsr.storage_type,
                       vbt.hypervisor_type,
                       bu.user_name 
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr, bd_user bu 
                where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = bu.user_uuid and 
        			  bbt.storage_uuid = bsr.storage_uuid and bbt.import_flag = ? and bbt.available_flag = ? and
                      vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ? ";
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.import_flag = ? and
                    bbt.available_flag = ? and vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ?
                    ";
        $flag = Xphp::$_config['FLAG'];
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $vmuuid, $vcenteruuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['SET'], $vmuuid, $vcenteruuid, $taskuuid);
        //区分异地本地
        if (!empty($localflag)) {
            $sql .= " and bbt.data_local_flag = ?";
            $sqlParams = array_merge($sqlParams, array($localflag));
        }
        //如果切换了节点
        if ($nodeuuid) {
            $sql .= " and bsr.node_uuid = ? ";
            $sqlCount .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
        }
        //切换存储
        if ($storageUuid) {
            $sql .= ' and bsr.storage_uuid = ? ';
            $sqlCount .= ' and bsr.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
        }

        if ($copyFlag || $archiveFlag) {

            if ($storageuuid && !empty($storageuuid)) {
                $sql .= " and bsr.storage_uuid = ? ";
                $sqlCount .= " and bsr.storage_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($storageuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
            }
        }

        if ($accurateFlag) {
            $search = $params['search'];
            $timepointType = intval($search['timepointType']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $week = $search['week'];
            $month = $search['month'];
            $year = $search['year'];
            $forever = $search['forever'];

            if (!empty($timepointType)) {
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($timepointType));
                $sqlCountParams = array_merge($sqlCountParams, array($timepointType));
            }
            if ($week == 1) {
                $sql .= " and bbt.weekly_flag = 1";
                $sqlCount .= " and bbt.weekly_flag = 1";
            }
            if ($month == 1) {
                $sql .= " and bbt.monthly_flag = 1";
                $sqlCount .= " and bbt.weekly_flag = 1";
            }
            if ($year == 1) {
                $sql .= " and bbt.yearly_flag = 1";
                $sqlCount .= " and bbt.weekly_flag = 1";
            }
            if ($forever == 1) {
                $sql .= " and bbt.importance_flag = 1";
                $sqlCount .= " and bbt.weekly_flag = 1";
            }

            //如果填了开始时间范围查询
            if ($startTime && $endTime) {
                $sql .= " and bbt.timepoint between '" . $startTime . "' and '" . $endTime . "' ";
                $sqlCount .= " and bbt.timepoint between '" . $startTime . "' and '" . $endTime . "' ";
            }
        }

        // 查看权限
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
            $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
            $sqlCount .= " and bbt.user_uuid in " . $useruuidArr;
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $utils = Xphp::instance('Utils');
        $records = array("data" => array());
        $i = 1;
        foreach ($data as $d) {
            $mark = $this->pGetTimepointMark($utils->parseFlagToBool($d['weekly_flag']), $utils->parseFlagToBool($d['monthly_flag']), $utils->parseFlagToBool($d['yearly_flag']), $utils->parseFlagToBool($d['importance_flag']));
            $remark = "";   //备注
            $op = array(1, 2, 3);
            // 异地副本数据不能永久标记
            $remotFlag = !$utils->parseFlagToBool(intval($d['data_local_flag']));
            if ($remotFlag) {//如果是异地副本，不能设置星标
                $op = array(1, 2);
            }
            //hyperv不支持设置永久标志
//             if(intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] &&
//                 intval($d['backup_mode']) != Xphp::$_config['BACKUP_MODE']['FULL'] || $d['data_local_flag'] != Xphp::$_config['FLAG']['SET']){
//                 $op = array(1, 2);
//             }
            //如果该时间点所在的存储在云存储上，不让删除
            if ($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']) {
                $op = array(1, 3);
            }
            //时间点在合并中，不让删除
            if ($d['archive_flag'] == Xphp::$_config['FLAG']['SET']) {
                $op = array(1);
            }

            //添加备注
            if ($this->checkEmpty($d['remarks'])) {
                //根据标记是否显示固定备注显示高度
                $top = "";
                if (!empty($mark)) {
                    $top = "top:-4px;";
                }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover"
                            data-placement="right" data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
            }

            $timepointDiv = '<div>' . $this->parseDate($d['timepoint']) . '</div>' . '<div class="table-body__timepoint">' . $mark . $remark . '</div>';

            $records["data"][] = array(
                '<span tid="' . $d['timepoint_uuid'] . '" sid="' . $d['storage_uuid'] . '">' . $i++ . '</span>',
                $timepointDiv,
                $this->getTimepointTypeDes($d['backup_mode']),
                $utils->calSize($d['total_size'], true),
                $utils->calSize($d['write_size'], true),
                $this->getStorageName($d['storage_uuid']),
                $op,
                array(
                    'uuid' => $d['timepoint_uuid'],
                    'hypervisor' => $d['hypervisor_type'],
                    'mode' => $d['backup_mode'],
                    'vmuuid' => $vmuuid,
                    'vcenteruuid' => $vcenteruuid,
                    'taskuuid' => $taskuuid,
                    'remote_flag' => !$utils->parseFlagToBool(intval($d['data_local_flag'])),
                    'weekly_flag' => $utils->parseFlagToBool($d['weekly_flag']),
                    'monthly_flag' => $utils->parseFlagToBool($d['monthly_flag']),
                    'yearly_flag' => $utils->parseFlagToBool($d['yearly_flag']),
                    'importance_flag' => $utils->parseFlagToBool($d['importance_flag']),
                    'remark' => $d['remarks'],
                    'nodeuuid' => $d['node_uuid'],
                ),
                $d['user_name']
            );
        }
        $records["draw"] = $params['draw'];
        ;
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];

        return json_encode($records);
    }

    /**
     * 得到某虚拟机备份时间点表--如果是副本归档 先用下面数据返回结构
     * @param unknown $params
     */
    public function getVmTimepointGridCopy($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $vmuuid = $params['vmuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $taskuuid = $params['taskuuid'];
        $createtime = $params['createtime'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array(
            '',
            'bbt.timepoint',
            'bbt.backup_mode',
            'bbt.total_size',
            'bbt.write_size',
            '',
            'bbt.remarks',
            '',
            'bbt.importance_flag'
        );

        $copyFlag = $params['copyFlag'];        //副本数据标志
        $archiveFlag = $params['archiveFlag'];  //归档数据标志
        $storageuuid = $params['storageuuid'];

        //         编号	时间点	类型	数据大小	用户	备注	操作	星标
        $sql = "select bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.total_size, bbt.write_size,
		               bbt.data_local_flag, bbt.importance_flag, bbt.remarks, bbt.archive_flag, vbt.hypervisor_type
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
        			  bbt.storage_uuid = bsr.storage_uuid and bbt.import_flag = ? and bbt.available_flag = ? and
                      vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ? ";
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.import_flag = ? and
                    bbt.available_flag = ? and vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ?
                    ";
        $flag = Xphp::$_config['FLAG'];
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $vmuuid, $vcenteruuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['SET'], $vmuuid, $vcenteruuid, $taskuuid);
        //如果切换了节点
        if ($nodeuuid) {
            $sql .= " and bsr.node_uuid = ? ";
            $sqlCount .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
        }

        if ($copyFlag || $archiveFlag) {

            if ($storageuuid && !empty($storageuuid)) {
                $sql .= " and bsr.storage_uuid = ? ";
                $sqlCount .= " and bsr.storage_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($storageuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
            }
        }

        $sqlParams = array_merge($sqlParams, array($start, $length));
        if ($accurateFlag) {
            $search = $params['search'];
            $timepointType = intval($search['timepointType']);
            $starFlag = intval($search['starFlag']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];

            $sql .= " and (bbt.backup_mode = " . $timepointType . " or " . $timepointType . " = '') and
        			(bbt.importance_flag = " . $starFlag . " or " . $starFlag . " = '')";
            $sqlCount .= " and (bbt.backup_mode = " . $timepointType . " or " . $timepointType . " = '') and
        			(bbt.importance_flag = " . $starFlag . " or " . $starFlag . " = '')";
            //如果填了开始时间范围查询
            if ($startTime && $endTime) {
                $sql .= " and bbt.timepoint between '" . $startTime . "' and '" . $endTime . "' ";
                $sqlCount .= " and bbt.timepoint between '" . $startTime . "' and '" . $endTime . "' ";
            }
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $utils = Xphp::instance('Utils');
        $records = array("data" => array());
        $i = 1;
        //         $op = array(1, 2);
        foreach ($data as $d) {
            $op = array(1, 2);
            //时间点在合并中，不让删除
            if ($d['archive_flag'] == Xphp::$_config['FLAG']['SET']) {
                $op = array(1);
            }
            $records["data"][] = array(
                $i++,
                '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['timepoint']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                $this->getTimepointTypeDes($d['backup_mode']),
                $utils->calSize($d['total_size'], true),
                $utils->calSize($d['write_size'], true),
                $this->getStorageName($d['storage_uuid']),
                $d['remarks'],
                $op,
                $d['importance_flag'],
                array(
                    'uuid' => $d['timepoint_uuid'],
                    'hypervisor' => $d['hypervisor_type'],
                    'mode' => $d['backup_mode'],
                    'vmuuid' => $vmuuid,
                    'vcenteruuid' => $vcenteruuid,
                    'taskuuid' => $taskuuid,
                    'remote_flag' => !$utils->parseFlagToBool(intval($d['data_local_flag']))
                ),
            );
        }
        $records["draw"] = $params['draw'];
        ;
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];

        return json_encode($records);
    }

    /**
     * 获取删除的虚拟机对应时间点信息
     * @param array $vminfo
     * @param string $nodeuuid
     */
    public function getTimepointByVM($vminfo, $nodeuuid)
    {
        $info = array();
        $sql = "select bbt.timepoint_uuid, bsr.node_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid and
    			bbt.task_uuid = ? and vbt.vm_uuid = ? ";
        $sqlParams = array($vminfo['taskuuid'], $vminfo['vmuuid']);
        if (!empty($nodeuuid)) {
            $sql .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d) {
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $d['node_uuid'],
                'hypervisor' => $vminfo['hypervisor']
            );
        }
        return $info;
    }


    /**
     * 删除批量备份时间点
     * @param array $params 二维数组
     * 如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     */
    public function deleteSelectTimepoint($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vmdata_detele");
        //得到二维数组
        //遍历，并按节点uuid分组
        //发送删除消息到不同的节点。删除消息array(timepointuuid,timepointuuid,timepointuuid,timepointuuid)
        $timepointList = $params['timepointlist'];
        $vmList = $params['vmlist'];
        $nodeuuid = $params['nodeuuid'];
        $selectTimepoints = array();
        if (!empty($vmList)) {
            foreach ($vmList as $vm) {
                $selectTimepoints = $this->getTimepointByVM($vm, $nodeuuid);
                $timepointList = array_merge($timepointList, $selectTimepoints);
            }
        }
        $utils = Xphp::instance('Utils');
        $timepointList = $utils->arraySort($timepointList, 'nodeuuid', '', 0, -1);

        $info = array();
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach ($timepointList as $d) {
            $i++;
            if (!in_array($d['nodeuuid'], $nodeuuids)) {
                $nodeuuids[] = $d['nodeuuid'];
                $info[] = array(
                    "nodeuuid" => $d['nodeuuid'],
                    "hypervisor" => $d['hypervisor']
                );
                if (!empty($timepointuuid)) {
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }

                $timepointuuid[] = $d['timepointuuid'];
            } else {
                $timepointuuid[] = $d['timepointuuid'];
            }
            // 时间点信息
            $details .= $this->getPointDetails($i, $d['timepointuuid']);
        }

        $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        Xphp::instance('DataHandler')->checkTimepointOperateAuth($timepointuuid);


        $timepointuuids[] = $timepointuuid;
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        $this->paramsCheck($timepointuuids[0][0], $nodeuuids[0]);
        //检测是否在任务中在任务就直接返回
        $uuidList = array();
        foreach ($timepointuuids as $d) {

            $uuidList = array_merge($uuidList, $d);
        }

        //检查时间点是否有任务存在
        $this->taskExist($uuidList, $operate);
        $this->grainTaskExist($uuidList, $operate);
        $countPoint = 0;
        for ($i = 0; $i < $nodeuuidsCount; $i++) {
            $msg = $timepointuuids[$i];
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            $msg = json_encode($msg);
            $mbResult = $this->mbVMMsg($nodeuuids[$i], $info[$i]['hypervisor'], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, Xphp::$_lang['WEB_PLATFORM_DES_VM']);
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        } else {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, vbt.vm_name from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = ? and vbt.timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_VM'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_VCENTER_MACHINE_NAME'] . "：" . $data[0]['vm_name'];
        } else {
            $details = xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_VM'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_VCENTER_MACHINE_NAME'] . "：" . $data[0]['vm_name'];
        }
        return $details;
    }

    /**
     * 检查需要删除的完备点是否在恢复、瞬时恢复和迁移任务中
     * @param array $timepointuuids
     *
     */
    public function taskExist($timepointuuids, $operate)
    {
        //判断完备点是否在瞬时恢复任务中
        $sql = "select vi.task_uuid from vm_instant vi join bd_task bt on vi.task_uuid = bt.task_uuid 
            where vi.timepoint_uuid in ('$timepointuuids') and bt.task_status != ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['STOPPED']));
        if (!empty($data)) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_DELETE_TIMEPOINT_INSTANT_WARNING'], 'warning'));
        }

        //判断是否在恢复或迁移任务中
        $sqlmore = "select vml.task_uuid from vm_machine_list vml join bd_task bt on vml.task_uuid = bt.task_uuid 
            where vml.timepoint_uuid in ('$timepointuuids') and bt.task_status != ?";
        $datamore = $this->dbSelect($sqlmore, array(Xphp::$_config['TASKSTATUS']['STOPPED']));
        //        $taskuuids = array();
//        foreach($datamore as $d){
//            $taskuuids[] = $d['task_uuid'];
//        }
//        $sqlexport = "select task_status from bd_task where task_uuid in ('$taskuuids') ";
//        $dataexport = $this->dbSelect($sqlexport, array());
        if (!empty($datamore)) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_DELETE_TIMEPOINT_RECOVER_MOTION_WARNING'], 'warning'));
        }
    }


    /**
     * 删除一个备份时间点
     * @param unknown $params
     * storageHandler deleteVMImportData调用
     */
    public function deleteTimepoint($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vmdata_detele");
        $pointUUID = $params['uuid'];
        $hypervisor = $params['hypervisor'];
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, vbt.vm_name  from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID));

        $this->paramsCheck($pointUUID, $hypervisor);

        $vmuuid = $params['vmuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $taskuuid = $params['taskuuid'];

        $submodule_type = intval($hypervisor);
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        //检测是否在任务中在任务就直接返回
        $this->taskExist(array($pointUUID), $operate);
        $this->grainTaskExist(array($pointUUID), $operate);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        Xphp::instance('DataHandler')->checkTimepointOperateAuth([$pointUUID]);

        $msg = array($pointUUID);
        $msg = json_encode(array('timepoint_uuids' => $msg));
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $timepointDes = $data[0]['timepoint'] . "(" . $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . ")";

        $descriptionParam = array($timepointDes, $data[0]['task_name'], $data[0]['vm_name']);
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_TIMEPOINT', $descriptionParam);
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                    ";
            $flag = Xphp::$_config['FLAG'];
            $dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $vmuuid, $vcenteruuid, $taskuuid, $pointUUID));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count" => intval($count), "id" => $pointUUID));
        } else {
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 检查时间点是否有细粒度任务存在
     * @param unknown $pointList
     * @param unknown $operate
     */
    private function grainTaskExist($pointList, $operate)
    {
        $points = implode("','", $pointList);
        $sql = "select vm_grain_info_id from vm_grain_info vgi join bd_task bt on vgi.task_uuid = bt.task_uuid 
            where timepoint_uuid in ('$points') and bt.task_status != ?";
        $data = $this->dbSelect($sql, [Xphp::$_config['TASKSTATUS']['STOPPED']]);
        if (!empty($data)) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_DELETE_TIMEPOINT_GRAIN_WARNING'], 'warning'));
        }
    }

    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode
     * @return string
     */
    public function getTimepointTypeDes($bakcupMode)
    {
        $pfDes = Xphp::$_pfdes;
        $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }

    /**
     * 根据虚拟化类型得到树的图标
     * @param int $hypervisor
     * @param boolean $vcenterFlag
     */
    public function getHypervisorIcon($hypervisor, $vcenterFlag)
    {
        $vcenter = Xphp::instance('Vcenter');
        return $vcenter->getHypervisorIcon($hypervisor, $vcenterFlag);
    }

    /**
     * 得到虚拟机备份任务名
     * @param unknown $params
     */
    public function getVMBackupTaskName($params)
    {
        $type = intval($params['type']);
        $this->paramsCheck($type);
        $taskName = Xphp::$_config['VMHYPERVISORDES'][$type];
        $taskName .= Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到虚拟机CDP任务名
     * @param unknown $params
     */
    public function getVMCDPTaskName($params)
    {
        $type = intval($params['type']);
        $this->paramsCheck($type);
        $taskName = Xphp::$_config['VMHYPERVISORDES'][$type];
        $taskName .= Xphp::$_lang['WEB_PLATFORM_DES_CDP'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到虚拟机恢复任务名
     * @param unknown $params
     */
    public function getVMRecoverTaskName($params)
    {
        $type = intval($params['type']);
        $this->paramsCheck($type);
        $taskName = Xphp::$_config['VMHYPERVISORDES'][$type];
        $taskName .= Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到虚拟机瞬时恢复任务名
     * @param unknown $params
     */
    public function getVMInstantRecoveryTaskName($params)
    {
        $type = intval($params['type']);
        $this->paramsCheck($type);
        $taskName = Xphp::$_config['VMHYPERVISORDES'][$type];
        $taskName .= Xphp::$_lang['WEB_VM_INSTANT_RECOVERY'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到细粒度恢复任务名
     * @param unknown $params
     */
    public function getVMGrainRecoveryTaskName($params)
    {
        $type = intval($params['type']);
        $this->paramsCheck($type);
        $taskName = Xphp::$_config['VMHYPERVISORDES'][$type];
        $taskName .= Xphp::$_lang['WEB_VM_GRAIN_RECOVERY'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到虚拟机演练任务名
     * @param unknown $params
     */
    public function getVMOrchTaskName($params)
    {
        $taskName = Xphp::$_lang['WEB_PLATFORM_DES_RECOVER_EMERGENCY_PLAN'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到备份数据导出任务
     * @param unknown $params
     */
    public function getDataExportTaskName($params)
    {
        $taskName = Xphp::$_lang['WEB_PLATFORM_DES_BACKUP_DATE_EXPORT'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName)
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
     * 获取迁移虚拟机的配置信息
     * @param array $params
     */
    public function getMotionVMConfigs($params)
    {
        $hypervisor = intval($params['hypervisor']);
        $taskuuid = $params['taskuuid'];
        $tasktype = $params['tasktype'];
        $this->paramsCheck($hypervisor, $taskuuid, $tasktype);
        $sql = "select vm_config, new_vm_name, orig_vm_name from vm_instant where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $config = json_decode($data[0]['vm_config'], true);
        $name = $data[0]['new_vm_name'] . Xphp::$_lang['WEB_VM_MOTION_CREATE'];
        if (
            $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ZSTACK']
            || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XSKY'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KVM_ZVIRT']
        ) {
            $name = $data[0]['new_vm_name'] . "_Migration";
        }
        $vmInfo[] = array(
            'vmuuid' => $config['vm_uuid'],
            'vmname' => $name,
            'oldname' => $data[0]['orig_vm_name'],    //统一数据结构,保留
            'storage' => $this->getVMConfigStorageInfo($config['disk_list'], $hypervisor),
            'network' => $this->getVMConfigNetworkInfo($config['network_list'], $hypervisor),
            'hypervisor' => $hypervisor,
        );

        return json_encode($vmInfo);
    }

    /**
     * 获取迁移虚拟机的配置信息
     * @param array $params
     */
    public function getMotionVMConfigsV2($params)
    {
        $hypervisor = intval($params['hypervisor']);
        $taskuuid = $params['taskuuid'];
        $tasktype = $params['tasktype'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hypervisor, $taskuuid, $tasktype, $vcenteruuid);
        $sql = "select vt.vm_config, vt.new_vm_name, vt.orig_vm_uuid, vt.orig_vm_name, vt.timepoint_uuid, vbt.hypervisor_type from vm_instant vt, vm_backup_timepoint vbt where vt.timepoint_uuid = vbt.timepoint_uuid and vt.task_uuid = ?";
        $sqlParams = array($taskuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $config = json_decode($data[0]['vm_config'], true);
        $points = array($data[0]['timepoint_uuid']);
        $pointsDetail = array(
            array(
                'vmname' => $data[0]['new_vm_name'] . "_Migration",
                'oldname' => $data[0]['new_vm_name'],
                'config' => array(
                    'password' => "",
                    'password_auto_flag' => 1
                ),
                'hypervisor' => $data[0]['hypervisor_type']
            )
        );



        //获取中间统一结构消息
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($points);
        $opName = 'VM_VCENTER_OP_GET_ADVANCE_MIGRATION_CONF';

        $mbMsg = array(
            'target_hypervisor_type' => $hypervisor,
            'task_uuid' => $taskuuid,                               //瞬时恢复任务uuid
            'orig_vm_uuids' => array($data[0]['orig_vm_uuid']), //瞬时恢复虚拟机列表,为后续支持多个做准备,这里是一个列表,目前是一个
        );

        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $mbMsg, true);
        if (!$mbResult['result']) {
            //获取后台消息失败
            $info = array(
                "flag" => false
            );
            return json_encode($info);
        }
        //找到原本的hypervisor
        $sql1 = "select hypervisor_type from vm_backup_timepoint where timepoint_uuid = ? ";
        $sqlParams1 = array($data[0]['timepoint_uuid']);
        $data1 = $this->dbSelect($sql1, $sqlParams1);
        $original_hypervisor = $data1[0]['hypervisor_type'];
        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        $info = $vmRecoveryHandler->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $points, $pointsDetail, $mbResult['msg']);
        $info['original_hypervisor'] = $original_hypervisor;
        $info['flag'] = true;
        $info['instantFlag'] = false;  //瞬时恢复标记,这里补齐这个字段
        $info['vmotionFlag'] = true;   //迁移标记,迁移的时候通过这个判断是迁移



        return json_encode($info);
    }

    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int $StrategyType
     * @param array $days
     * @return string  空字符串  s1 - s4
     */
    private function parseTimeStrategyFrequency($strategyType, $days)
    {
        $frequency = "";
        if (Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'] != intval($strategyType)) {
            return $frequency;
        }
        $strIndex = strpos($days, "s");
        if ($strIndex) {
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }

    /**
     * 转化时间策略天数为一个数组,每一项为boll
     * @param unknown $days
     */
    private function parseTimeStrategyDay($days)
    {
        if (empty($days)) {
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        $trueDays = array();
        foreach ($daysArr as $key => $d) {
            //如果遇到s,结束    现在每周存储格式为0000001s1   s1表示间隔一周,以此类推
            if ("s" == $d) {
                break;
            }
            if ($d) {
                $trueDays[$key] = true;
            } else {
                $trueDays[$key] = false;
            }
        }
        return $trueDays;
    }

    /**
     * 获取时间策略的备份方式[strategy oncetime manual]
     * @param $strategyID
     * @return mixed|string
     */
    public function getTimeStrategyBackupType($strategyID)
    {
        $sql = "SELECT time_strategy_backup_type FROM bd_strategy WHERE strategy_id = ? ";
        $timeStrategyBackupData = $this->dbSelect($sql, [$strategyID]);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time 
                from bd_time_strategy where strategy_id = ? ";
        $data = $this->dbSelect($sql, array($strategyID));

        $timeStrategyBackupType = 'strategy';
        if (
            !$timeStrategyBackupData ||
            !is_array($timeStrategyBackupData) ||
            !$timeStrategyBackupData[0]['time_strategy_backup_type']
        ) {  // 走之前逻辑
            if (1 == count($data) && Xphp::$_config['STRATEGY_TYPE']['ONCE'] == $data[0]['strategy_type']) {
                $timeStrategyBackupType = 'oncetime';
            }
        } else {
            $timeStrategyBackupType = Xphp::$_config['TIME_STRATEGY_BACKUP_TYPE_MAP'][$timeStrategyBackupData[0]['time_strategy_backup_type']];
        }
        return $timeStrategyBackupType;
    }

    /**
     * 根据策略id得到时间策略信息
     * @param int $strategyID
     */
    public function getTimeStrategyInfo($strategyID)
    {
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time,full_backup_compensation_flag
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
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
                $utils = Xphp::instance('Utils');
                $allBackupMode = array_column($data, 'mode');
                //可能有多个策略类型(每天,每周,每月)
                foreach ($data as $d) {
                    if ($d['roll_flag'] == Xphp::$_config['FLAG']['SET']) {
                        $rollInterval = $d['roll_interval'];
                        $endTime = $d['roll_end_time'];
                    } else {
                        $rollInterval = '3600';
                        $endTime = '23:59:59';
                    }
                    $strategyData[] = array(
                        'start_time' => $d['start_time'],
                        'roll_flag' => $utils->parseFlagToBool($d['roll_flag']),
                        'roll_interval' => $utils->secToTime($rollInterval),
                        'roll_end_time' => $endTime,
                        'mode' => $d['mode'],  // 没有完全备份就是永久增量
                        'strategy_type' => intval($d['strategy_type']),
                        'days' => $this->parseTimeStrategyDay($d['days']),
                        'frequency' => $this->parseTimeStrategyFrequency($d['strategy_type'], $d['days']),
                    	"full_backup_compensation_flag"=> $utils->parseFlagToBool($d['full_backup_compensation_flag']),
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
     * 获取虚拟机备份任务信息(修改任务用)
     */
    public function getBackupTaskAllInfo($params)
    {
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        // $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, vt.appliance_uuid,
        //                vt.hypervisor_type, vt.level, vt.transport_priority, vt.quiesce_snapshot, vt.valid_data_backup, vt.serial_snapshot_flag, vt.parse_fs_flag, vt.not_backup_swap_file_flag, vt.not_backup_deleted_file_flag, vt.not_backup_partition_gap_flag, vt.pre_create_snap_flag, vt.select_conditions, vt.display_mode, vt.detail,
        //         	   brs.strategy_type, brs.number,
        //         	   bts.encrypt_flag, bts.compress_flag,
        //         	   bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.compress_method, bss.encrypted_flag, bss.password_auto_flag, bss.password,
        //                bcs.is_integrity_check,bcs.calibration_algorithm
        //         from bd_task bt, vm_task vt, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss,bd_integrity_check_strategy bcs
        //         where bt.task_uuid = vt.task_uuid
        //         and bt.strategy_id = brs.strategy_id
        //         and bt.strategy_id = bts.strategy_id
        //         and bt.strategy_id = bss.strategy_id
        //         and bt.task_uuid = bcs.task_uuid
        //         and bt.task_uuid = ?";
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, vt.agent_uuid, 
        vt.hypervisor_type, vt.level, vt.transport_priority, vt.quiesce_snapshot, vt.valid_data_backup, vt.serial_snapshot_flag, vt.parse_fs_flag, vt.not_backup_swap_file_flag, vt.not_backup_deleted_file_flag, vt.not_backup_partition_gap_flag, vt.pre_create_snap_flag, vt.storage_snapshot_enable_flag, vt.select_conditions, vt.display_mode, vt.detail, 
        vt.agent_pool_uuid,
        brs.strategy_type, brs.number, brs.strategy_mode,
        bts.encrypt_flag, bts.compress_flag, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
        bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.compress_method, bss.encrypted_flag, bss.password_auto_flag, bss.password, bss.encrypt_method,
        bsr.storage_type
        from vm_task vt, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss, bd_task bt
        left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
        where bt.task_uuid = vt.task_uuid
        and bt.strategy_id = brs.strategy_id
        and bt.strategy_id = bts.strategy_id
        and bt.strategy_id = bss.strategy_id
        and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        //获取GFS信息
        //         {
        //             //初始化数值,数组中第一个代表level2_type即周几/第几周/哪月   第二个代表保留数量,第三个代表是否勾选(默认值为checked选中和''不选中)
        //             'week':[7,5,''],
        //             'month':[1,5,''],
        //             'year':[1,5,''],
        //         }
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
        $utils = Xphp::instance('Utils');
        if ($data) {
            $select_conditions = trim($data[0]['select_conditions']);
            if (!$select_conditions || 'null' == $select_conditions) {
                $global_select = [];
            } else {
                $global_select = json_decode($select_conditions, true);
                //统一为二维数组
                if (count($global_select) == count($global_select, 1)) {
                    $global_select = [$global_select];
                }
            }
            //自动备份之前的任务这里为0，改为1用于树的加载
            $display_mode = $data[0]['display_mode'] == 0 ? 1 : intval($data[0]['display_mode']);
            // detail
            $detail = $data[0]['detail'];
            $detailArr = (!$detail || 'null' == $detail) ? [] : json_decode($detail, true);
            $reset_cbt_flag = $detailArr['error_reset_cbt_flag'] ?? Xphp::$_config['FLAG']['SET'];
            $full_backup_reset_cbt = $detailArr['full_backup_reset_cbt_flag'] ?? Xphp::$_config['FLAG']['UNSET'];
            $auto_join_flag = $utils->parseFlagToBool($detailArr['auto_join_flag']);
            $agent_public_ip = $detailArr['agent_public_ip'] ?? ['ip_uuid' => '', 'chargemode' => '', 'size' => 0];
            $keep_snapshot_flag = $detailArr['keep_snapshot_flag'] ?? Xphp::$_config['FLAG']['UNSET'];
            $highspeed_disk_cbt_flag = $detailArr['highspeed_disk_cbt_flag'] ?? Xphp::$_config['FLAG']['UNSET'];
            $snapshot_timeout = $detailArr['snapshot_timeout'] ?? 3600;

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
                'agent_pool_uuid' => $data[0]['agent_pool_uuid'] ?: '',
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
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
                    'compress' => $utils->parseFlagToBool($data[0]['compress_flag']),
                    'mode' => $this->getTransportModeToArr($data[0]['transport_priority'], $data[0]['hypervisor_type']),
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                ),
                //存储策略
                'bss' => array(
                    'deduplication' => $utils->parseFlagToBool($data[0]['deduplication_flag']),
                    'blocksize' => intval($data[0]['block_size']) / 1024,
                    'compress' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'password_auto_flag' => $utils->parseFlagToBool($data[0]['password_auto_flag']),
                    'password' => base64_encode($utils->ptPassDecrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method'])
                ),
                //备份模式
                'mode' => array(
                    'snapshot' => $data[0]['level'] == Xphp::$_config['XENSERVER_BACKUP_LEVEL']['KEEP_SNAPSHOT'],
                    'keepsnapshot' => $utils->parseFlagToBool($keep_snapshot_flag),
                    'incmode' => 1 == $data[0]['valid_data_backup'] ? Xphp::$_config['HIGH_MODE']['CBT'] : $data[0]['level'],
                    'quiescesnapshot' => $utils->parseFlagToBool($data[0]['quiesce_snapshot']),
                    'cbtmode' => $utils->parseFlagToBool($data[0]['valid_data_backup']),
                    'resetcbt' => $utils->parseFlagToBool($reset_cbt_flag),
                    'fullresetcbt' => $utils->parseFlagToBool($full_backup_reset_cbt),
                    'snapshotmode' => $data[0]['serial_snapshot_flag'],
                    'parsefsmode' => $utils->parseFlagToBool($data[0]['parse_fs_flag']),
                    'swapmode' => $utils->parseFlagToBool($data[0]['not_backup_swap_file_flag']),
                    'deletefilemode' => $utils->parseFlagToBool($data[0]['not_backup_deleted_file_flag']),
                    'gapmode' => $utils->parseFlagToBool($data[0]['not_backup_partition_gap_flag']),
                    'threadnum' => intval($data[0]['thread_num']),      //线程数量
                    'presnapshot' => $utils->parseFlagToBool($data[0]['pre_create_snap_flag']),
                    'storagesnapshot' => $utils->parseFlagToBool($data[0]['storage_snapshot_enable_flag']),
                    'highspeeddiskcbt' => $utils->parseFlagToBool($highspeed_disk_cbt_flag),
                    'snapshottimeout' => intval($snapshot_timeout)
                ),
                //虚拟机信息
                'vm_info' => $this->getVMEditInfo($taskUUID, intval($data[0]['display_mode']), $auto_join_flag),
                //时间策略
                'timestrategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                // 'speedInfo' => $this->getSpeedStrategyInfo($taskUUID),
                'speedInfo' => $this->getSpeedGlobalStrategyInfo($taskUUID),
                'nosnapshot' => $this->getXenSnapshotFlag($taskUUID),
                'backup_server_ip' => $data[0]['backup_server_ip'],
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
                'agent_public_ip' => $agent_public_ip
            );
        }
        return json_encode($info);
    }

    /**
     * 得到虚拟机备份报告数据
     * @param unknown $params
     */
    public function getVMReport($params)
    {
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $params['search'];
        if ($params['start'] > $start) {
            $start = $params['start'];
        }
        if ($params['length'] > $length) {
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $draw = $params['draw'];
        $searchValue = $search['search'];


        //获取用户已分配的虚拟机
        $resourceHandler = Xphp::instance('ResourceHandler');
        $vmResource = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'], "tree_id", "desc", $limit = 0, "all");
        $vmList = array();
        $vcenterList = array();
        foreach ($vmResource['data'] as $vm) {
            if (!in_array($vm['vcenter_uuid'], $vcenterList)) {
                $vcenterList[] = $vm['vcenter_uuid'];
            }
            if (!in_array($vm['uuid'], $vmList)) {
                $vmList[] = $vm['uuid'];
            }
        }

        //获取所有虚拟机
        $sql = "select vt.tree_id, vt.vcenter_uuid, vv.user_uuid, vv.hypervisor_type, vv.vcenter_ip, vv.detail, 
                vv.username, vt.name, vt.uuid, vt.dir_path,vt.detail as vm_detail, bu.user_name, 
                mur.user_uuid as owner_uuid, bu2.user_name as owner_name 
                from vm_tree vt join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                join bd_user bu on vv.user_uuid = bu.user_uuid 
                left join mt_user_resource mur on vt.uuid = mur.vm_uuid
                left join bd_user bu2 on mur.user_uuid = bu2.user_uuid";

        $sql .= " where vt.display_mode = ?
                and vt.type = ? and vv.hypervisor_type not in (" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ")";

        // 查看权限
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
            $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and (bu.user_uuid in " . $useruuidArr . " or mur.user_uuid in " . $useruuidArr . ")";
        }

        $sqlParams = array(
            Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            Xphp::$_config['VM_TREE_TYPE']['VM']
        );
        $accurateFlag = $params['accurateFlag'];
        if ($accurateFlag) {
            $search = $params['search'];
            $hypervisor = intval($search['hypervisor']);
            $backupFlag = intval($search['backupFlag']);
            $vcenteruuid = $search['vcenteruuid'];
            $vmName = $search['vmName'];
            $searchvmIP = $search['vmIP'];
            $hostName = $search['hostName'];
            $vmCluster = $search['vmCluster'];
            $taskName = $search['taskName'];
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];

            //虚拟机名
            if ($this->checkEmpty($vmName)) {
                $sql .= " and vt.name like ? ";
                $sqlParams = array_merge($sqlParams, array('%' . $vmName . '%'));
            }
            //虚拟机路径
            if ($this->checkEmpty($vmCluster)) {
                $sql .= " and vt.parent_uuid in (
                    select distinct vt2.uuid from vm_tree vt2 where vt2.type in (?, ?) and vt2.name like ?  
                    union select distinct vt3.uuid from vm_tree vt3 join vm_tree vt4 on vt3.parent_uuid = vt4.uuid 
                        where vt4.type in (?, ?, ?) and vt4.name like ? 
                )";
                $sqlParams = array_merge(
                    $sqlParams,
                    array(
                        Xphp::$_config['VM_TREE_TYPE']['CLUSTER'],
                        Xphp::$_config['VM_TREE_TYPE']['OVIRT_CLUSTER'],
                        '%' . $vmCluster . '%',
                        Xphp::$_config['VM_TREE_TYPE']['CLUSTER'],
                        Xphp::$_config['VM_TREE_TYPE']['OVIRT_CLUSTER'],
                        Xphp::$_config['VM_TREE_TYPE']['POOL'], // sangfor scp通过集群搜索资源池的虚拟机
                        '%' . $vmCluster . '%',
                    )
                );
            }
            //虚拟化中心
            if (!empty($vcenteruuid)) {
                $sql .= " and vt.vcenter_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($vcenteruuid));
            }
            //虚拟化中心
            if (!empty($hypervisor)) {
                $sql .= " and vv.hypervisor_type = ? ";
                $sqlParams = array_merge($sqlParams, array($hypervisor));
            }
            //宿主机或租户
            if ($this->checkEmpty($hostName)) {
                $sql .= " and vt.host_uuid in (select distinct vh.host_uuid from vm_host vh where vh.host_name like ? or vh.host_ip like ?)";
                $sqlParams = array_merge($sqlParams, array('%' . $hostName . '%', '%' . $hostName . '%'));
            }

        } else if ($this->checkEmpty($searchValue)) {
            $searchValue = str_replace('\\', '\\\\', $searchValue); //like查询中的"\"需要转成"\\\\"才会生效
            $backupFlag = false;
            $sql .= " and (vt.name like ? or vt.dir_path like ?)";
            $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%', '%' . $searchValue . '%'));
        }
        $sql .= " order by vt.name ";
        $data = $this->dbSelect($sql, $sqlParams);
        //获取所有正在备份任务中的虚拟机
        $vcenterHandler = Xphp::instance('Vcenter');
        $vmInbackupInfo = $vcenterHandler->getBackupVMInfo();
        $vminfo = array();
        $i = 1;
        $searchCount = 0;
        $utils = Xphp::instance('Utils');
        $vcenter = Xphp::instance('Vcenter');
        if (!empty($data)) {
            $lastBackupInfoList = $this->getVMReportLastBackupInfo($data);
            foreach ($data as $d) {
                //如果是不属于当前用户的虚拟机需要排除
                if (Xphp::$_user['useruuid'] != $d['user_uuid'] && Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager']) {
                    if (in_array($d['uuid'], $vmList) && in_array($d['vcenter_uuid'], $vcenterList)) {

                    } else {
                        continue;
                    }
                }

                // 操作权限
                $authUser = $_SESSION['authUser']['vmprotect_operate'] ?? [];
                $userUuids = $d['owner_uuid'] ? [$d['owner_uuid']] : [$d['user_uuid']];
                $operateFlag = Xphp::instance('Utils')->xphp_check_operate(Xphp::$_user['useruuid'], $userUuids, $authUser);

                $searchCount++;
                $lastBackupInfo = array(
                    'lastbackuptime' => Xphp::$_config['TIMESPACE'],
                    'backuptask' => Xphp::$_config['NULLSPACE'],
                    'backupcount' => Xphp::$_config['NULLSPACE'],
                    'backupstorage' => Xphp::$_config['NULLSPACE'],

                );

                $vmBackupFlag = $vcenterHandler->getVMInBackupFlag(Xphp::$_config['VM_TREE_TYPE']['VM'], $d['uuid'], $d['vcenter_uuid'], $vmInbackupInfo);
                //虚拟机是否在任务中
                if (!empty($lastBackupInfoList)) {
                    foreach ($lastBackupInfoList as $list) {
                        if ($list['vm_uuid'] == $d['uuid'] && $list['vcenter_uuid'] == $d['vcenter_uuid']) {
                            $lastBackupInfo = $list;
                            if (!$vmBackupFlag) {
                                $tasks_arrays = $this->dbSelect('select task_name from bd_task');
                                $tasks_array = array();
                                foreach ($tasks_arrays as $t) {
                                    $tasks_array[] = $t['task_name'];
                                }
                                //任务是否还存在
                                if (in_array($lastBackupInfo['backuptask'], $tasks_array)) {
                                    $lastBackupInfo['backuptask'] = Xphp::$_config['NULLSPACE'];
                                } else {
                                    $lastBackupInfo['backuptask'] = $lastBackupInfo['backuptask'] . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                                }
                            }
                        }
                    }
                }
                if ($vmBackupFlag) {
                    $idStr = $d['vcenter_uuid'] . '_' . $d['uuid'];
                    $lastBackupInfo['backuptask'] = $vmInbackupInfo['tasklist'][$idStr];
                }
                //匹配查询备份中任务名
                if ($accurateFlag && $this->checkEmpty($taskName) && stripos($lastBackupInfo['backuptask'], $taskName) === false) {
                    $searchCount--;
                    continue;
                }
                //匹配最后一次备份时间
                if ($accurateFlag && $startTime && $endTime) {
                    if (strtotime($startTime) < strtotime($lastBackupInfo['lastbackuptime']) && strtotime($endTime) > strtotime($lastBackupInfo['lastbackuptime'])) {

                    } else {
                        $searchCount--;
                        continue;
                    }
                }
                if ($backupFlag && $vmBackupFlag != $utils->parseFlagToBool($backupFlag)) {
                    $searchCount--;
                    continue;
                }
                $vmIp = Xphp::$_config['NULLSPACE'];
                //获取虚拟机IP
                if (!empty($d['vm_detail'])) {
                    $detail = json_decode($d['vm_detail'], true);
                    if (
                        in_array(intval($d['hypervisor_type']), Xphp::$_config['VMHYPERVISORGROUP']['openstack']) ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_WINHONG_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XFUSION_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'] ||
                        intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']
                    ) {
                        $network = $detail['network_list'];
                        $vmIp = '';
                        $ipList = array();
                        if (!empty($network)) {
                            foreach ($network as $net) {
                                $list = $net['ip_list'];
                                foreach ($list as $ip) {
                                    $ipList[] = $ip;
                                    $vmIp .= $ip . PHP_EOL;
                                }
                            }
                        }
                        if ($accurateFlag && !empty($searchvmIP)) {
                            //改为模糊搜索
                            $ipFlag = false;
                            foreach ($ipList as $ip) {
                                if (false !== strpos($ip, $searchvmIP)) {
                                    $ipFlag = true;
                                    break;
                                }
                            }
                            if (!$ipFlag) {
                                $searchCount--;
                                continue;
                            }
                        }

                    } else if ($accurateFlag && !empty($searchvmIP)) {
                        //通过IP搜索,没有虚拟机详情的一并排除
                        $searchCount--;
                        continue;
                    }
                } else if ($accurateFlag && !empty($searchvmIP)) {
                    //通过IP搜索,没有虚拟机详情的一并排除
                    $searchCount--;
                    continue;
                }
                $vminfo[] = array(
                    '<input type="checkbox" name="id[]" value="' . $d['tree_id'] . '">',
                    $i++,
                    $d['name'],
                    $vmIp,
                    $vcenter->getVcenterIp($d['vcenter_ip'], $d['detail'], intval($d['hypervisor_type']), $d['username']),
                    $vmBackupFlag,
                    $lastBackupInfo['lastbackuptime'],
                    $lastBackupInfo['backuptask'],
                    $lastBackupInfo['backupcount'],
                    $lastBackupInfo['backupstorage'],
                    $d['owner_uuid'] ? $d['owner_name'] : $d['user_name'], //被分配的虚拟机显示被分配的用户名
                    $operateFlag && !$vmBackupFlag ? array(4, 5) : array(),
                    array(
                        'treeid' => $d['tree_id'],
                        'dirpath' => $d['dir_path'],
                        'vcenteruuid' => $d['vcenter_uuid'],
                        'vmuuid' => $d['uuid'],
                        'hypervisor' => $d['hypervisor_type'],
                        'displaymode' => Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']
                    ),
                    $d['dir_path'],
                    $lastBackupInfo['backupstorageSize'],
                );
            }
        }
        //存储空间排序取int字节排序
        if ($sortColumn == 9) {
            $sortColumn = 14;  //处理导出获取的是字节int类型
        }
        $vminfo = $utils->arraySort($vminfo, $sortColumn, $sortType, $start, $length);  //排序

        $records = array("data" => $vminfo);
        $records["draw"] = $draw;
        $records["recordsTotal"] = $searchCount;
        $records["recordsFiltered"] = $searchCount;

        return json_encode($records);
    }

    /**
     * 得到虚拟机上次备份时间/备份任务/备份次数/当前备份所占用存储空间等信息
     * @param array $vmList uuid, vcenter_uuid
     */
    public function getVMReportLastBackupInfo($vmList)
    {
        $vmlistDes = "";
        foreach ($vmList as $key => $vm) {
            if ($key == count($vmList) - 1) {
                $vmlistDes .= '("' . $vm['vcenter_uuid'] . '","' . $vm['uuid'] . '")';
            } else {
                $vmlistDes .= '("' . $vm['vcenter_uuid'] . '","' . $vm['uuid'] . '"),';
            }
        }

        $sql = "select max(unix_timestamp(bbt.timepoint)) as timepoint, vbt.vm_uuid, vbt.vcenter_uuid, bbt.task_name, bbt.task_uuid, count(distinct bbt.timepoint) as count, sum(bbt.write_size) as real_size, sum(bbt.total_size) as total_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                 where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.available_flag = ?
                and (vbt.vcenter_uuid, vbt.vm_uuid) in (" . $vmlistDes . ") and bbt.task_type = ? and bbt.module_type = ? group by vbt.vm_uuid, vbt.vcenter_uuid order by bbt.timepoint desc";
        $sqlParams = array(
            Xphp::$_config['FLAG']['SET'],
            Xphp::$_config['TASKTYPE']['BACKUP'],
            Xphp::$_config['MODULE_TYPE']['VM']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $i = 0;
        $backupStorage = 0;

        $list = array();
        $utils = Xphp::instance('Utils');
        foreach ($data as $d) {
            //备份次数先用历史任务成功个数统计，未匹配到历史任务获取时间点个数
//             $count = $d['task_count'];
//             if(empty($count)){
            $count = $d['count'];
            //             }
            $info = array(
                'lastbackuptime' => Xphp::$_config['TIMESPACE'],
                'backuptask' => Xphp::$_config['NULLSPACE'],
                'backupcount' => Xphp::$_config['NULLSPACE'],
                'backupstorage' => Xphp::$_config['NULLSPACE'],
                'timepointcount' => Xphp::$_config['NULLSPACE'],
            );
            $name = $d['task_name'];
            //上次备份时间
            $info['lastbackuptime'] = $this->parseDate($d['timepoint']);
            //备份任务
            $info['backuptask'] = $name;
            //备份次数
            $info['backupcount'] = $count;
            $info['timepointcount'] = $d['count'];
            //             $info['backupstorage'] = $utils->calSize(intval($d['real_size']));
            //获取虚拟机 备份写入大小和总大小
//             $sizeInfo = $this->getVmStorageSize($d['vm_uuid'], $d['vcenter_uuid']);
            //备份数据大小
            $info['backupstorage'] = $utils->calSize($d['real_size']);
            $info['backupstorageSize'] = $d['real_size'];
            //虚拟机备份总大小
            $info['totalsizeDes'] = $utils->calSize($d['total_size']);
            $info['totalsize'] = $d['total_size'];
            $info['vm_uuid'] = $d['vm_uuid'];
            $info['vcenter_uuid'] = $d['vcenter_uuid'];

            $list[] = $info;
        }
        return $list;
    }

    private function getTaskDeleteFlag($taskuuid)
    {
        $flag = false;
        $sql = "select task_name from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (empty($data)) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * 得到虚拟机细粒度恢复文件树
     * @param unknown $params
     * @return string
     */
    public function getVmGrainRecoveryTree($params)
    {
        $taskuuid = $params['taskuuid'];
        $tree = array();
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module
                from bd_agent
                where online_flag = ?
                and user_uuid = ?  ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);

        $systemHandler = Xphp::instance('SystemHandler');
        foreach ($data as $d) {
            if (!($systemHandler->checkModuleValid($d['authorization_module'], 'file'))) {
                continue;
            }


            for ($i = 1; $i < 100; $i++) {
                $name = $d['agent_name'] == $d['hostname'] ? $d['ip'] . "(" . $d['hostname'] . ")" : $d['ip'] . "(" . $d['agent_name'] . ")";
                $name .= $name . $i . $name;
                $node = array(
                    "id" => $i,
                    "pId" => $i - 2,
                    "name" => $name,
                    "title" => $d['ip'],
                    "open" => false,
                    "uuid" => $d['agent_uuid'],
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => "./img/vm/host.png",
                );
                $tree[] = $node;
            }
        }

        return json_encode($tree);
    }

    /**
     * 检查细粒度任务状态,只在运行状态的时候可以继续,如果不是运行状态,直接返回没有数据的参数恢复
     */
    private function checkGrainRecoveryTaskStatus($taskuuid)
    {
        $sql = "select task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskStatus = intval($data[0]['task_status']);
        if (Xphp::$_config['TASKSTATUS']['RUNNING'] != $taskStatus) {
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
     * 得到细粒度恢复任务展示模式
     * @param unknown $params 返回不展示的项
     */
    public function getVmGrainRecoveryShowType($params)
    {
        $task_uuid = $params['task_uuid'];
        $this->paramsCheck();

        $sql = "select task_status, os_type, root_list from vm_grain_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        if (empty($data[0]['root_list'])) {
            return json_encode(array());
        }

        $rootList = json_decode($data[0]['root_list'], true);
        $count = count($rootList['guest_struct']);
        $allShowType = array(
            Xphp::$_config['GUEST_DISPLAY_MODE']['SYSTEM'],
            Xphp::$_config['GUEST_DISPLAY_MODE']['NORMAL_DEVICE'],
            Xphp::$_config['GUEST_DISPLAY_MODE']['LOGICAL_DEVICE'],
        );
        $showType = array();
        foreach ($rootList['guest_struct'] as $struct) {
            $showType[] = $struct['display_mode'];
        }

        $noShowType = array_diff($allShowType, $showType);
        return json_encode($noShowType);
    }

    /**
     * 得到细粒度恢复文件列表
     *
     */
    public function getVmGrainRecoveryFileDir($params)
    {
        $task_uuid = $params['task_uuid'];
        $rootFlag = intval($params['root_flag']);
        $start = intval($params['start']);
        $number = intval($params['number']);
        $tmpFilePath = $params['tmp_file_path'];
        $path = $params['path'];
        $sclass = $params['sclass'];
        $showtype = intval($params['showtype']);
        $device = $params['device'];
        $searchName = $params['search_name'];
        $firstSearchFlag = $params['first_search_flag'];

        $this->checkGrainRecoveryTaskStatus($task_uuid);
        //如果是请求根
        if (1 == $rootFlag) {
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

        $nodeHandler = Xphp::instance('NodeHandler');
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
            $vmOpcode = Xphp::instance('VMOpcode');
            $operate = $vmOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        $list = array(
            "re" => true,              //成功标志,方面前端统一处理
            "path" => $path,           //当前路径
            'taskuuid' => $task_uuid,
            "isFinished" => $mbResult['msg']['is_finished'],
            "tmpFilePath" => $mbResult['msg']['tmp_file_path'],
            "nextStart" => $start + $number
        );
        $fileList = array();
        $utils = Xphp::instance('Utils');
        $fileHandler = Xphp::instance('FileHandler');
        $msgFileList = json_decode($mbResult['msg']['result_json_str'], true);
        foreach ($msgFileList as $d) {
            $filename = $d['name'];
            $filesize = $utils->calSize($d['size'], true);
            if ($path == "/") {
                $pathHead = $path;
            } else {
                $pathHead = $path . "/";
            }

            $filename = $d['name'];
            $filesize = $utils->calSize($d['size'], true);
            $fileList[] = array(
                $filename,                  //文件名
                $filesize,                  //文件大小
                $d['modify_time'],          //修改时间
                array(
                    "path" => $pathHead . $d['name'],                                              //路径
                    "type" => $this->getGrainFileType($d['type']),                    //文件类型
                    "isfile" => $d['type'] == Xphp::$_config['GUEST_FILE_ITEM_TYPE']['DIR'] ? false : true,   //是否是文件
                    "sclass" => $this->getGrainFileClassName($d['type'], $filename, $sclass),    //显示类型
                    "createTime" => $d['create_time'],
                    "modifyTime" => $d['modify_time'],
                    "size" => $d['size'],
                    "device" => $device,     //为了统一,此模式下设备为空
                    "btype" => $d['type'],
                ),
            );
        }
        $list['filelist'] = $fileList;

        return json_encode($list);

    }

    /**
     * 得到细粒度文件类型
     * @param unknown $type
     */
    private function getGrainFileType($filetype)
    {
        $filetypeConf = Xphp::$_config['GUEST_FILE_ITEM_TYPE'];
        $fileClass = "unknown";
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
     * @param string $filename  文件名
     * @param string $size  图标大小   s/m/l 24/32/48px
     */
    private function getGrainFileClassName($filetype, $filename, $size)
    {
        $class = "filetype-unknown-" . $size;
        if (intval($filetype) == Xphp::$_config['GUEST_FILE_ITEM_TYPE']['DIR']) {
            $class = "filetype-dir-" . $size;
            return $class;
        } elseif (
            intval($filetype) == Xphp::$_config['GUEST_FILE_ITEM_TYPE']['SLINK'] ||
            intval($filetype) == Xphp::$_config['GUEST_FILE_ITEM_TYPE']['SLINK_TARGET_UNREACHABLE']
        ) {
            $class = "filetype-link-" . $size;
            return $class;
        }
        return $class;
    }

    /**
     * 根据展示方式解析数据类型
     * @param array $guest_struct     数据类型
     * @param int $showtype         展示方式
     */
    private function getVMGrainFileWithShowType($guest_struct, $showtype)
    {
        $showlist = array();
        foreach ($guest_struct as $list) {
            if ($list['display_mode'] == $showtype) {
                switch ($showtype) {
                    case Xphp::$_config['GUEST_DISPLAY_MODE']['SYSTEM']:
                        $showlist = $list['root_list'];
                        break;
                    case Xphp::$_config['GUEST_DISPLAY_MODE']['NORMAL_DEVICE']:
                    case Xphp::$_config['GUEST_DISPLAY_MODE']['LOGICAL_DEVICE']:
                        $showlist = $list['dev_list'];
                        break;
                }
            }
        }
        return $showlist;
    }

    /**
     * 根据展示方式解析数据类型
     * @param array $d       单个数据
     * @param int $showtype     展示方式
     */
    private function getVMGrainFileOneWithShowType($d, $showtype, $sclass, $pathHead)
    {
        $showInfo = array();
        $utils = Xphp::instance('Utils');
        $fileHandler = Xphp::instance('FileHandler');
        switch ($showtype) {
            case Xphp::$_config['GUEST_DISPLAY_MODE']['SYSTEM']:
                $filename = $d['name'];
                $filesize = $utils->calSize($d['size'], true);
                $showInfo = array(
                    $filename,                  //文件名
                    $filesize,                  //文件大小
                    $d['modify_time'],          //修改时间
                    array(
                        "path" => $pathHead . $d['name'],
                        //                         "path" => '/',                                                        //路径
                        "type" => $this->getGrainFileType($d['type']),                    //文件类型
                        "isfile" => false,   //是否是文件
                        "sclass" => $this->getGrainFileClassName($d['type'], $filename, $sclass),    //显示类型
                        "createTime" => $d['create_time'],
                        "modifyTime" => $d['modify_time'],
                        "size" => "",
                        "device" => "",     //为了统一,此模式下设备为空
                        "btype" => $d['type'],
                    ),
                );
                break;
            case Xphp::$_config['GUEST_DISPLAY_MODE']['NORMAL_DEVICE']:
            case Xphp::$_config['GUEST_DISPLAY_MODE']['LOGICAL_DEVICE']:
                $showInfo = array(
                    $d['device_path'],                  //文件名
                    "--",                  //文件大小
                    $d['fs_type_str'],          //文件系统类型
                    array(
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
     * 首次获取细粒度恢复文件列表,第一次是从数据库中获取,获取根目录
     */
    private function getVmGrainRecoveryFileDirFirst($taskuuid, $sclass, $showtype, $searchName)
    {
        $sql = "select task_status, os_type, root_list from vm_grain_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $show_root_list = json_decode($data[0]['root_list'], true);
        $rootList = $this->getVMGrainFileWithShowType($show_root_list['guest_struct'], $showtype);

        $list = array(
            "re" => true,                               //成功标志,方面前端统一处理
            "path" => "",           //当前路径
            'taskuuid' => $taskuuid,
            "tmpFilePath" => '',
            "isFinished" => true,
            "nextStart" => 0,
        );
        $fileList = array();
        $utils = Xphp::instance('Utils');
        $fileHandler = Xphp::instance('FileHandler');
        foreach ($rootList as $d) {
            //按名称模糊搜索
            if (!empty($searchName)) {
                if (!in_array($searchName, $d))
                    continue;
            }
            $fileList[] = $this->getVMGrainFileOneWithShowType($d, $showtype, $sclass, "");
        }
        $list['filelist'] = $fileList;

        return json_encode($list);
    }

    /**
     * 检查细粒度恢复文件是否可以下载
     * @param unknown $params
     */
    public function downloadGrainRecoveryFileCheck($params)
    {
        $task_uuid = $params['taskuuid'];
        $filepath = $params['path'];
        $filesize = $params['size'];
        $filename = $params['name'];
        $showtype = intval($params['showtype']);
        $device = $params['device'];
        $this->paramsCheck($task_uuid, $filepath, $filesize, $filename, $showtype);

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

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK';


        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);
        if (1 == strlen($mbResult)) {
            //如果读取成功
            return $this->muOpResult(true, $opName);
        } else {
            //失败,返回后台失败信息
            return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_GRAIN_RECOVERY_FILE_DOWNLOAD'], Xphp::$_lang['WEB_GRAIN_RECOVERY_FILE_DOWNLOAD_ERROR'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 下载细粒度恢复文件
     * @param unknown $params
     */
    public function downloadGrainRecoveryFile($params)
    {
        $task_uuid = $_GET['taskuuid'];
        $filepath = $_GET['path'];
        $filesize = $_GET['size'];
        $filename = $_GET['name'];
        $showtype = intval($_GET['showtype']);
        $device = $_GET['device'];
        $this->paramsCheck($task_uuid, $filepath, $filesize, $filename);

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK';

        //         $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);
        //         var_dump($mbResult);
        //         return;
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $filesize);
        Header("Content-Disposition: attachment; filename=" . $filename);
        $blockSize = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];
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
     * @param unknown $params
     */
    public function downloadGrainRecoveryDirCheck($params)
    {
        $task_uuid = $params['taskuuid'];
        $filepath = $params['path'];
        $filename = $params['name'];
        $showtype = intval($params['showtype']);
        $device = $params['device'];
        $this->paramsCheck($task_uuid, $filepath, $filename, $showtype);

        $uuid = Xphp::instance('Utils', 'uuid');

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

        $nodeHandler = Xphp::instance('NodeHandler');
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
                Xphp::$_lang['VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR'],
                Xphp::$_lang['VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR'],
                '',
                $mbResult['errorCode']
            );
        }
    }

    /**
     * 细粒度下载目录
     * @param unknown $params
     */
    public function downloadGrainRecoveryDir($params)
    {
        $task_uuid = $_GET['taskuuid'];
        $filepath = $_GET['path'];
        $filesize = $_GET['size'];
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
            'read_len' => Xphp::$_config['GRAIN_FILE_BLOCK_SIZE']
        );

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR';

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        //         Header("Accept-Length: " . $filesize);
        Header('Content-Disposition: attachment; filename="'. $filename . '.tar.gz"');

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
     * 获取存储名字
     * @param string $storageuuid
     */
    public function getStorageName($storageuuid, $realNodeUuid = '')
    {
        $sql = "select storage_nickname, node_uuid, storage_config, storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = $data[0]['storage_nickname'];
        $type = intval($data[0]['storage_type']);
        $nodeHandler = Xphp::instance('NodeHandler');
        if ($type == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']) {
            $storageConfig = json_decode($data[0]['storage_config'], true);
            $nodename = $storageConfig['remote_ip'];
        } else {
            if ($realNodeUuid && $type == Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']) {
                $nodename = $nodeHandler->getNodeName($realNodeUuid);
            } else {
                $nodename = $nodeHandler->getNodeName($data[0]['node_uuid']);
            }
        }
        $name .= "\n" . "(" . $nodename . ")";
        return $name;
    }

    /**
     * 得到备份修改的虚拟机信息
     * @param unknown $params
     * @return array
     */
    public function getVMEditInfo($taskuuid, $display_mode, $auto_join_flag)
    {
        $info = array();
        $sql = "select vol.object_uuid, vol.vcenter_uuid, vol.type, vol.exclude_vm_uuid_list, vol.object_name, vol.vm_config, 
            vt.dir_path, vt.host_uuid
            from vm_object_list vol left join vm_tree vt 
                on vol.vcenter_uuid = vt.vcenter_uuid and vol.object_uuid = vt.uuid and vol.type = vt.type and vt.display_mode = ? 
            where vol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($display_mode, $taskuuid));
        if (empty($data)) {
            $sql = "select vm_uuid as object_uuid, 7 as type, '' as exclude_vm_uuid_list, vcenter_uuid, vm_config, vm_name as object_name, host_uuid, dir_path from vm_machine_list where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
        }
        $Vcenter = Xphp::instance('Vcenter');
        foreach ($data as $d) {
            //获取排除的虚拟机
            $exclude_vms = [];
            $exclude_vm_ids = explode(',', trim($d['exclude_vm_uuid_list'], ','));
            if ($exclude_vm_ids) {
                $exclude_vm_ids_str = implode("','", $exclude_vm_ids);
                $sql = "select uuid, dir_path from vm_tree where uuid in ('" . $exclude_vm_ids_str . "') and type = ? and display_mode = ?";
                $exclude_vms_data = $this->dbSelect($sql, [Xphp::$_config['VM_TREE_TYPE']['VM'], Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']]);
                foreach ($exclude_vms_data as $vm) {
                    $exclude_vms[] = [
                        'vm_uuid' => $vm['uuid'],
                        'vm_path' => $vm['dir_path']
                    ];
                }
            }

            //未开启自动备份要排除创建任务后新增的虚拟机
            if (Xphp::$_config['VM_TREE_TYPE']['VM'] != $d['type'] && !$auto_join_flag) {
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
                "hostuuid" => $d['host_uuid'] ?: $d['object_uuid'],
            );
        }

        return $info;
    }

    /**
     * 获取备份系统天际的所有虚拟机个数
     * @param unknown $params
     * @return string
     */
    public function getAllVms($params)
    {
        //获取所有虚拟机
        $sql = "select vt.tree_id from vm_tree vt, vm_vcenter vv
                where vt.vcenter_uuid = vv.vcenter_uuid  and vt.display_mode = ?
                and vt.type = ? and vv.hypervisor_type not in ('" .
            implode("','", Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . "')";

        $sqlParams = array(
            Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            Xphp::$_config['VM_TREE_TYPE']['VM']
        );
        // 查看权限
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
            $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and vv.user_uuid in " . $useruuidArr;
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $info = array(
            'vmLength' => count($data)
        );

        return json_encode($info);
    }

    /**
     * 组合限速策略列表
     * @param unknown $params
     */
    public function groupTaskSpeedList($speedList, $strategygroupuuid)
    {
        $info = array();
        $utils = Xphp::instance('Utils');
        foreach ($speedList as $speed) {
            $info[] = array(
                'strategy_uuid' => $speed['uuid'],
                'strategy_name' => '',
                'strategy_type' => $speed['type'],
                'start_time' => $utils->formartTime($speed['startTime']),
                'end_time' => $utils->formartTime($speed['endTime']),
                'days' => implode("", $speed['days'] ?? []),
                'speed_limited_value' => $speed['value'],
                'extra_info' => '',
                'remark' => $speed['des'],
                'strategy_group_uuid' => $strategygroupuuid,
            );
        }
        return $info;
    }

    /**
     * 组合全局限速策略
     */
    public function groupTaskSpeedGlobalList($speedList)
    {
        $utils = Xphp::instance('Utils');
        $info = [
            'task_priority' => $speedList['level'], // 任务级别
            'remark' => ''
        ];
        if ($speedList['type'] == 1) {
            // 全局策略
            if (empty($speedList['uuid'])) {
                return [];
            }
            $info['strategy_uuid'] = $speedList['uuid'];
            $info['strategy_name'] = $speedList['name'];
            $info['strategy_type'] = $speedList['strategy_type'];
            $info['is_global'] = 1;
            $info['speed_limit_info'] = [];
            $info['extra_info'] = '';
        } else {
            $speedList['speedInfo'] = $speedList['speedInfo'] ?: [];
            // 自定义
            $info['strategy_uuid'] = '';
            $info['strategy_name'] = '';
            $info['is_global'] = 0;
            foreach ($speedList['speedInfo'] as &$speedItem) {
                $speedItem['startTime'] = $utils->formartTime($speedItem['startTime']);
                $speedItem['endTime'] = $utils->formartTime($speedItem['endTime']);
            }
            $info['extra_info'] = json_encode($speedList['speedInfo']);
            // 查询出type和组装下策略
            if (isset($speedList['speedInfo']) && count($speedList['speedInfo'])) {
                $strategy_type = $speedList['speedInfo'][0]['type'];
                $info['strategy_type'] = $strategy_type;
                if (in_array($strategy_type, [1, 4, 5])) {
                    // 这三个类型的没有days的值
                    $timelist = [];
                    foreach ($speedList['speedInfo'] as $item) {
                        $timelist[] = [
                            'start_time' => $strategy_type != 5 ? $utils->formartTime($item['startTime']) : $item['startTime'],
                            'end_time' => $strategy_type != 5 ? $utils->formartTime($item['endTime']) : $item['endTime'],
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedlimitinfo[] = [
                        'days' => '',
                        'time_list' => $timelist
                    ];
                } else {
                    $speedlimitinfos = [];
                    foreach ($speedList['speedInfo'] as $item) {
                        $days = implode('', $item['days']);
                        $speedlimitinfos[$days][] = [
                            'start_time' => $utils->formartTime($item['startTime']),
                            'end_time' => $utils->formartTime($item['endTime']),
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedlimitinfo = [];
                    foreach ($speedlimitinfos as $keys => $items) {
                        $speedlimitinfo[] = [
                            'days' => $keys,
                            'time_list' => $items,
                        ];
                    }
                }
                $info['speed_limit_info'] = $speedlimitinfo;
            } else {
                // 未定义，直接传空数组
                return [];
            }
        }

        return $info;
    }

    /**
     * 获取任务限速策略列表信息
     * @param string $taskuuid
     */
    public function getSpeedStrategyInfo($taskuuid)
    {
        $sql = "select strategy_uuid, speed_limited_value, start_time, end_time, days, remark, strategy_type from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        $utils = Xphp::instance('Utils');
        foreach ($data as $d) {
            $value = intval($d['speed_limited_value']);
            $valueList = $utils->calSizeToValueAndUnit($value);
            $info[] = array(
                'uuid' => $d['strategy_uuid'],
                'type' => $d['strategy_type'],
                'startTime' => $d['start_time'],
                'endTime' => $d['end_time'],
                'days' => $this->parseSpeedStrategyDay($d['days']),
                'value' => $value,
                'des' => $d['remark'],
                'unit' => $valueList['unit'] . '/s',
                'speednum' => intval($valueList['value'])
            );
        }
        return $info;
    }

    /**
     * 获取任务全局限速策略列表信息
     * @param string $taskuuid
     */
    public function getSpeedGlobalStrategyInfo($taskuuid)
    {
        $sql = "select strategy_uuid,task_priority from bd_task_speed_limit_strategy where task_uuid = ? limit 1";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = [
            'level' => 1,
            'type' => 2,
            'speedInfo' => [],
        ];
        if (!empty($data)) {
            // 查询出全局限速策略
            $sql = "select * from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
            $strategy = $this->dbSelect($sql, array($data[0]['strategy_uuid']));
            if (!empty($strategy)) {
                $info['level'] = $data[0]['task_priority']; // 任务级别
                $info['strategy_type'] = $strategy[0]['strategy_type']; // 限速类型
                $info['type'] = $strategy[0]['is_global'] ? 1 : 2; // 任务类型 选择策略还是自定义策略
                if ($info['type'] == 1) {
                    // 全局策略
                    $info['uuid'] = $strategy[0]['strategy_uuid'];
                } else {
                    $info['speedInfo'] = json_decode($strategy[0]['extra_info'], true);
                }
            }
        }
        return $info;
    }

    /**
     * 转换限速策略天数为一个数组,每一项为0,1
     * @param unknown $days
     */
    private function parseSpeedStrategyDay($days)
    {
        if (empty($days)) {
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        foreach ($daysArr as $key => $d) {
            if ($d) {
                $daysArr[$key] = 1;
            } else {
                $daysArr[$key] = 0;
            }
        }
        return $daysArr;
    }

    public function dispenseStrategyToJob($info, $taskuuid, $strategygroupuuid)
    {
        $utils = Xphp::instance('Utils');
        $sql = "select task_type, strategy_id from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskType = intval($data[0]['task_type']);
        $strategyID = $data[0]['strategy_id'];
        if ($taskType == Xphp::$_config['TASKTYPE']['BACKUP']) {
            return $this->dispenseBackupJob($info, $taskuuid, $strategyID, $strategygroupuuid);
        } else if ($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']) {
            return $this->dispenseRecoveryJob($info, $taskuuid, $strategyID, $strategygroupuuid);
        }
    }

    /**
     * 分发备份任务
     * @param array $info
     * @param string $taskuuid
     * @param int $strategyID
     * @param string $strategygroupuuid
     */
    private function dispenseBackupJob($info, $taskuuid, $strategyID, $strategygroupuuid)
    {
        //开始事务
        $this->dbBeginTransaction();
        $utils = Xphp::instance('Utils');
        $result = true;
        //时间策略
        if ($info['time']['check']) {
            //更新时间策略表 bd_time_strategy
            $sql = "delete from bd_time_strategy where strategy_id = ? and mode != ?";
            $result = $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));
            $timeStrategy = $info['time']['timeInfo'];
            if ("oncetime" == $timeStrategy['type']) {
                //一次性策略
                $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time)
                        values (?, ?, ?, ?)";
                $sqlParams = array(
                    $strategyID,
                    Xphp::$_config['BACKUP_MODE']['FULL'],
                    Xphp::$_config['STRATEGY_TYPE']['ONCE'],
                    $timeStrategy['datetime']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            } elseif ("strategy" == $timeStrategy['type']) {
                //时间策略
                if (!empty($timeStrategy['fullInfo'])) {
                    //完全策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['FULL'];

                    //单独处理完全备份修改
                    $sql = "select strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                        from bd_time_strategy where strategy_id = ?";
                    $data = $this->dbSelect($sql, array($strategyID));
                    if (empty($data[0])) {
                        //如果数据库没有,直接插入,
                        $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);

                    } else {
                        //如果数据库有,比较内容,如果一样,不做操作,如果不一样,删除后插入
                        $strategyInfo = $timeStrategy['fullInfo'];
                        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
                        //不一样,删除后再重新插入
                        $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                        $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));

                        $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                    }
                } else {
                    $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                    $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));
                }
                if (!empty($timeStrategy['incrInfo'])) {
                    //增量策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                    $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['incrInfo']);
                }
                if (!empty($timeStrategy['diffInfo'])) {
                    //差异策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'];
                    $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['diffInfo']);
                }

            }
        }
        //限速策略
        if ($info['speedlimit']['check']) {
            $speedList = $this->groupTaskSpeedList($info['speedlimit']['speedInfo'], $strategygroupuuid);
            //更新时间策略表 bd_task_speed_limit_strategy
            $sql = "delete from bd_task_speed_limit_strategy where task_uuid = ?";
            $result = $result && $this->dbExec($sql, array($taskuuid));
            $sql = "insert bd_task_speed_limit_strategy (strategy_uuid, strategy_group_uuid, strategy_type, days, start_time, end_time, remark, speed_limited_value, task_uuid) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            foreach ($speedList as $speed) {
                $sqlParams = array($speed['strategy_uuid'], $strategygroupuuid, intval($speed['strategy_type']), $speed['days'], $speed['start_time'], $speed['end_time'], $speed['remark'], intval($speed['speed_limited_value']), $taskuuid);
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }

        //存储策略
        if ($info['store']['check']) {
            //更新存储策略表 bd_storage_strategy
            $sql = "update bd_storage_strategy set deduplication_flag = ?, compressed_flag = ?, encrypted_flag =?, password_auto_flag=?, password=? where task_uuid = ?";
            $sqlParams = array(
                $utils->parseBoolToFlag($info['store']['storeInfo']['deduplication']),
                $utils->parseBoolToFlag($info['store']['storeInfo']['compress']),
                $utils->parseBoolToFlag($info['store']['storeInfo']['encrypt']),
                $utils->parseBoolToFlag($info['store']['storeInfo']['password_auto_flag']),
                $this->updateStorePassword($info['store']['storeInfo']['password'], $info['store']['storeInfo']['encrypt'], $info['store']['storeInfo']['password_auto_flag']),
                $taskuuid
            );
            $result = $result && $this->dbExec($sql, $sqlParams);

        }
        //保留策略
        if ($info['reserve']['check']) {
            //更新保留策略表 bd_reserved_strategy
            $sql = "update bd_reserved_strategy set strategy_type = ?, number = ? where task_uuid = ?";
            $sqlParams = array($info['reserve']['reserveInfo']['type'], intval($info['reserve']['reserveInfo']['value']), $taskuuid);
            $result = $result && $this->dbExec($sql, $sqlParams);
            //更新GFS策略
            //先查询此任务下有多少个虚拟机,有关虚拟机的都要修改
            $sqlvm = "select vm_uuid from vm_machine_list where task_uuid = ?";
            $resultvm = $this->dbSelect($sqlvm, array($taskuuid));
            $vm_list = array();
            foreach ($resultvm as $each) {
                $vm_list[] = array(
                    'vmuuid' => $each['vm_uuid'],
                );
            }
            ;
            $result = $result && $this->updateGFSStrategy($info['reserve']['reserveInfo']['gfs_strategy_item_list'], true, $taskuuid, $vm_list);

        }
        //高级策略
        if ($info['high']['check']) {
            //更新线程数量
            $sql = "update bd_task set thread_num = ? where task_uuid = ?";
            $result = $this->dbExec($sql, array(intval($info['high']['highInfo']['threadnum']), $taskuuid));
            //更新虚拟机任务表 vm_task
            $sql = "update vm_task set serial_snapshot_flag = ?, parse_fs_flag = ?, not_backup_swap_file_flag = ?, not_backup_partition_gap_flag = ? where task_uuid = ?";
            //V-CBT
            $parsefsMode = $utils->parseBoolToFlag($info['high']['highInfo']['parsefscheck']);
            //排除交换文件块
            $swapMode = $utils->parseBoolToFlag($info['high']['highInfo']['swapcheck']);
            //排除分区间隙
            $gapMode = $utils->parseBoolToFlag($info['high']['highInfo']['gapcheck']);

            //快照模式
            $snapshotMode = intval($info['high']['highInfo']['snapshottype']);
            $result = $result && $this->dbExec($sql, array($snapshotMode, $parsefsMode, $swapMode, $gapMode, $taskuuid));
        }
        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        return $result;
    }


    /**
     * 更改分发存储策略加密的时候密码修改
     * @param unknown $password
     * @param unknown $encrypt
     * @param unknown $password_auto
     * @author liushuai@vinchin.com
     * @return str  新的密码
     */
    private function updateStorePassword($password, $encrypt, $password_auto)
    {
        $utils = Xphp::instance('Utils');
        $password_str = $password;
        if ($encrypt && $password_auto) {  //如果加密和自动生成密码都打开,使用默认密码
            $password_default = "/mnt/vm_vinfs";
            $password_str = $utils->ptPassEncrypt($password_default);
        } else if ($encrypt && !$password_auto) { //如果加密打开  自动密码关闭  使用传输的密码
            //先base64解码
            $password_decode = base64_decode($password);
            //再平台加密
            $password_str = $utils->ptPassEncrypt($password_decode);
        } else {
            $password_str = '';
        }
        return $password_str;
    }


    /**
     * 分发恢复任务
     * @param array $info
     * @param string $taskuuid
     * @param int $strategyID
     * @param string $strategygroupuuid
     */
    private function dispenseRecoveryJob($info, $taskuuid, $strategyID, $strategygroupuuid)
    {
        //开始事务
        $this->dbBeginTransaction();
        //时间策略
        if ($info['time']['check']) {
            //更新时间策略表 bd_time_strategy
            $sql = "delete from bd_time_strategy where strategy_id = ?";
            $result = $this->dbExec($sql, array($strategyID));
            $timeStrategy = $info['time']['timeInfo'];
            if (2 == $timeStrategy['type']) { //立即恢复
                //时间策略
                if (!empty($timeStrategy['recInfo'])) {
                    //恢复策略
                    $modeType = Xphp::$_config['BACKUP_MODE']['FULL'];
                    $result = $result && $this->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['recInfo']);
                }
            }
        }
        //限速策略
        if ($info['speedlimit']['check']) {
            $speedList = $this->groupTaskSpeedList($info['speedlimit']['speedInfo'], $strategygroupuuid);
            //更新时间策略表 bd_task_speed_limit_strategy
            $sql = "delete from bd_task_speed_limit_strategy where task_uuid = ?";
            $result = $result && $this->dbExec($sql, array($taskuuid));
            $sql = "insert bd_task_speed_limit_strategy (strategy_uuid, strategy_group_uuid,  strategy_type, days, start_time, end_time, remark, speed_limited_value, task_uuid) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            foreach ($speedList as $speed) {
                $sqlParams = array($speed['strategy_uuid'], $strategygroupuuid, intval($speed['strategy_type']), $speed['days'], $speed['start_time'], $speed['end_time'], $speed['remark'], intval($speed['speed_limited_value']), $taskuuid);
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }

        //高级策略
        if ($info['high']['check']) {
            //更新线程数量
            $sql = "update bd_task set thread_num = ? where task_uuid = ?";
            $result = $this->dbExec($sql, array(intval($info['high']['highInfo']['threadnum']), $taskuuid));
        }

        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        return $result;
    }

    /**
     * 获取虚拟化中心名字
     */
    public function getVcenterNameInfo($vcenteruuid)
    {
        $vcenter = Xphp::instance('Vcenter');
        $sql = "select vcenter_ip, vcenter_uuid, nickname, vcenter_name,  hypervisor_type, vcenter_flag, detail, username from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $info = array(
            'name' => $vcenter->getVcenterNameInTree($vcenteruuid, intval($data[0]['hypervisor_type']), $data[0]['vcenter_flag'], $data[0]['vcenter_ip'], $data[0]['nickname'], $data[0]['vcenter_name'], $data[0]['detail'], $data[0]['username']),
            'flag' => intval($data[0]['vcenter_flag'])
        );
        return $info;
    }

    //     /**
    //      * 写任务操作日志
    //      * @param unknown $taskuuid
    //      * @param unknown $taskName
    //      * @param unknown $task_type
    //      * @param unknown $desKey
    //      */
    //     private function writeTaskLog($taskuuid, $taskName, $task_type, $desKey){
    //         $sql="select hypervisor_type from vm_task where task_uuid = ?";
    //         $data = $this->dbSelect($sql, array($taskuuid));
    //         $hypervisor = intval($data[0]['hypervisor_type']);
    //         $desParam = '["S:' . $taskName . '"]';
    //         $sql = "insert into bd_task_log(task_uuid, task_name, task_type, user_uuid, user_name,
    //                         module_type, submodule_type, error_code, op_time, description_key, description_param,
    //                         log_level, running_flag) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    //         $sqlParams = array(
    //             $taskuuid, $taskName, $task_type, Xphp::$_user['useruuid'], Xphp::$_user['username'],
    //             Xphp::$_config['MODULE_TYPE']['VM'], $hypervisor, 0, date("Y-m-d H:i:s"), $desKey, $desParam,
    //             1, Xphp::$_config['FLAG']['UNSET']
    //         );
    //         $result = $this->dbQuery($sql, $sqlParams);

    //         return $result;
    //     }

    /**
     * 获取xen版本判断有无静默快照flag
     * @param string $taskuuid
     */
    private function getXenSnapshotFlag($taskuuid)
    {
        $sql = "select distinct vcenter_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $vcenter = Xphp::instance('Vcenter');
        return $vcenter->getSnapshotFlag($data[0]['vcenter_uuid']);
    }


    /**
     * 公共方法
     * 检测是否超出租户限制速度
     * @param array $speedList
     * @param string $opName
     * @author luokai@vinchin.com
     */
    public function pCheckSpeedLimit($speedList, $operate)
    {
        if (empty($speedList))
            return;
        $tenantHandler = Xphp::instance('TenantHandler');
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        if (!$settings['common'])
            return;
        $speedUnit = $settings['common']['speedunit'];
        $num = 0;
        if ($speedUnit == "KB/s") {
            $num = 1024;
        } else if ($speedUnit == "MB/s") {
            $num = 1024 * 1024;
        } else if ($speedUnit == "GB/s") {
            $num = 1024 * 1024 * 1024;
        }
        $tenantSpeed = intval($settings['common']['speed']) * $num;

        foreach ($speedList as $speed) {
            if (intval($speed['value']) > $tenantSpeed) {
                exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_PLATFORM_SPEED_GT_MAX_RECONFIG'], "warning"));
            }
        }
    }

    /**
     * 校验数据加密密码正确性
     * @param unknown $params
     * @return string
     */
    public function checkVMEncryptPass($params)
    {
        $vms = $params['vms'];
        $utils = Xphp::instance('Utils');
        foreach ($vms as $vm) {
            //校验输入密码
            if ($vm['passFlag']) {
                $sql = "select bbt.detail, vbt.vm_name from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
                $data = $this->dbSelect($sql, array($vm['timepointuuid']));
                if (!empty($data)) {
                    $detail = json_decode($data[0]['detail'], true);
                    $oldPassword = $detail['password'];
                    $password = $utils->ptPassEncrypt(base64_decode($vm['encrypt_pass']));
                    if ($oldPassword != $password) {
                        return $this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_DATA_ENCRYPT_VERIFY'], Xphp::$_lang['UI_VCENTER_MACHINE'] . "'" . $data[0]['vm_name'] . "'" . Xphp::$_lang['UI_PLATFORM_VERIFI_FAIL_REENTER'], "warning");
                    }
                }
            }
        }

        $info = array(
            'flag' => true
        );
        return json_encode($info);
    }
    /**
     * 更新GFS的策略信息
     * @param unknown $info为GFS的js插件封装传出的结构体
     * @param string $task_uuid
     * @return boolean
     */
    public function updateGFSStrategy($info, $oldinfoflag, $task_uuid, $vm_list)
    {
        //$oldinfoflag为true有做修改,$oldinfoflag为false未作修改
        if (!$oldinfoflag) {
            //未做修改则直接返回
            return true;
        }
        $result = true;
        $deleteList = array(1, 2, 3); //未被勾选的策略集合
        if (!empty($info)) {
            //修改GFS等待表,统一先删除后再添加 并且等待状态全部置成2,所以如果以前有1状态的就不管了 重新来
            //先删除所有此任务有关的等待表
            $sql_wait_delete = "delete from bd_gfs_entity_waiting_map where task_uuid = ?";
            $result_wait_delete = $this->dbExec($sql_wait_delete, array($task_uuid));
            //修改勾选的GFS策略信息
            foreach ($info as $oneGFS) {
                $level1_type = intval($oneGFS['level1_type']);
                //删除完以后再添加
                foreach ($vm_list as $each_vm) {
                    $sqladd = "insert into bd_gfs_entity_waiting_map(task_uuid, entity_uuid, level1_type, waiting_flag) values(?,?,?,?)";
                    $sqladdParams = array($task_uuid, $each_vm['vmuuid'], $level1_type, 2);
                    $resultadd = $this->dbExec($sqladd, $sqladdParams);
                    if (!$resultadd) {
                        return false;
                    }
                }
                //---
                unset($deleteList[$level1_type - 1]);
                //先检查数据库是否有 有就修改 没有就添加
                $checkSql = "select id from bd_task_gfs_retention_strategy where task_uuid = ? and level1_type = ?";
                $checkResult = $this->dbSelect($checkSql, array($task_uuid, $level1_type));
                if (!empty($checkResult)) {
                    //修改bd_task_gfs_retention_strategy
                    $sql = "update bd_task_gfs_retention_strategy set level2_type = ?, retention_num = ? where task_uuid = ? and level1_type = ?";
                    $sqlParams = array($oneGFS['level2_type'], $oneGFS['retention_num'], $task_uuid, $oneGFS['level1_type']);
                    $result1 = $this->dbExec($sql, $sqlParams);
                } else {
                    $sql = "insert into bd_task_gfs_retention_strategy(task_uuid,level1_type,level2_type,retention_num) values(?,?,?,?)";
                    $sqlParams = array($task_uuid, $oneGFS['level1_type'], $oneGFS['level2_type'], $oneGFS['retention_num']);
                    $result1 = $this->dbExec($sql, $sqlParams);
                }
                if (!$result1) {
                    $result = false;
                    return $result;
                }
            }
        } else {
            //如果传入的为空则清空所有GFS有关
            $deleteStrategy = "delete from bd_task_gfs_retention_strategy where task_uuid = ?";
            $deleteMap = "delete from bd_gfs_entity_waiting_map where task_uuid = ?";
            $resultStrategy = $this->dbExec($deleteStrategy, array($task_uuid));
            $resultMap = $this->dbExec($deleteMap, array($task_uuid));
            if (!$resultStrategy || !$resultMap) {
                $result = false;
                return false;
            }
        }
        //开始删除未被勾选的集合
        if (!empty($deleteList)) {
            $ThisList = implode(",", $deleteList);
            $deleteSql = "delete from bd_task_gfs_retention_strategy where task_uuid = ? and level1_type in(" . $ThisList . ") ";
            $result2 = $this->dbExec($deleteSql, array($task_uuid));
            if (!$result2) {
                $result = false;
                return false;
            }
        }
        return $result;
    }

    /**
     * 检查租户虚拟机个数是否超出租户授权
     * @param unknown $vmNum
     */
    private function checkTenantVmAuth($vmNum, $taskuuid)
    {
        $tenantHandler = Xphp::instance('TenantHandler');
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        if ($settings['common']['authtype'] == 2) {
            //按个数授权
            $authVm = intval($settings['common']['vm']);
            $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
            $userDes = implode("','", $userList);
            $sql = "select count(vml.machine_id) as total from vm_machine_list vml, bd_task bt where bt.task_uuid = vml.task_uuid and bt.user_uuid in('" . $userDes . "') ";
            $sqlParams = array();
            if (!empty($taskuuid)) {
                $sql .= " and bt.task_uuid != ? ";
                $sqlParams = array($taskuuid);
            }
            $data = $this->dbSelect($sql);
            $allVm = $vmNum + intval($data[0]['total']);
            if ($allVm > $authVm) {
                exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_SYSAUTHOR_CHECK'], Xphp::$_lang['UI_PLATFORM_NUM_NOT_ENOUGH_CONTAC_FACTORY'], "warning"));
            }
        } else if (empty($settings['common']['authtype'])) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_SYSAUTHOR_CHECK'], Xphp::$_lang['UI_PLATFORM_TENANT_NOT_AUTH'], "warning"));
        }

        return true;
    }

    /**
     * 获取创建虚拟实验室选择宿主机
     * @return string
     */
    public function getVirtualLabHost($params)
    {
        $editFlag = $params['editFlag'];    //修改虚拟实验室
        $vcenteruuid = $params['vcenteruuid'];  //虚拟实验室所在虚拟化中心唯一标识
        $sql = "select vcenter_uuid, vcenter_ip, nickname, vcenter_flag, hypervisor_type, vcenter_name, detail, username from vm_vcenter
                where online_flag = ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET']);

        //目前只支持VMware虚拟化
        $sql .= " and hypervisor_type = ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']);

        $sql .= " order by hypervisor_type ";

        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();

        foreach ($data as $d) {
            $openFlag = false;
            if ($editFlag) {
                //修改虚拟实验室只展示当前使用的虚拟化中心
                if ($vcenteruuid != $d['vcenter_uuid'])
                    continue;
                $openFlag = true;
            }
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                "name" => $this->getRecoverHostName($d['hypervisor_type'], $d),
                "open" => $openFlag,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => intval($d['hypervisor_type']),
                "type" => 1,
                "iconSkin" => $this->getHypervisorIcon($d['hypervisor_type'], false),
                "vcenteruuid" => $d['vcenter_uuid']
            );
            $tree[] = $node;
        }
        return json_encode($tree);
    }

    /**
     * 获取虚拟机备份数据大小
     * @param unknown $vmuuid
     * @param unknown $vcenteruuid
     * @return number
     */
    private function getVmStorageSize($vmuuid, $vcenteruuid)
    {
        $sql = "select sum(bbt.write_size) as real_size, sum(bbt.total_size) as total_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? ";
        $data = $this->dbSelect($sql, array($vcenteruuid, $vmuuid));
        $info = array(
            'total_size' => 0,
            'real_size' => 0
        );
        if (!empty($data)) {
            $info = array(
                'total_size' => intval($data[0]['total_size']),
                'real_size' => intval($data[0]['real_size'])
            );
        }
        return $info;
    }

    /**
     * 搜索虚拟机时间点
     * @param unknown $params
     */
    public function searchTimepoint($params)
    {
        $search = $params['search'];
        $week = $params['week'];
        $month = $params['month'];
        $year = $params['year'];
        $forever = $params['forever'];
        $sql = "select bbt.detail, bbt.deleted_flag, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint,
 bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) task_create_time,
 bbt.task_uuid, bbt.remarks, bbt.archive_flag,bbt.weekly_flag,bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag,
 vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type,
 bsr.node_uuid, bsr.status from bd_backup_timepoint bbt, vm_backup_timepoint vbt,
 bd_storage_resource bsr where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
 and bbt.available_flag = ? and bbt.import_flag = ? and bbt.module_type = ? and bbt.data_local_flag = ? 
 and bbt.task_type = ? and vbt.hypervisor_type not in (" . implode("','", Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ")";
        //筛选出每个增备点和差异点对应PID
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['FLAG']['SET'], Xphp::$_config['TASKTYPE']['BACKUP']);
        //        $fulluuidList = array();
//        $unfullList = array();
//        if($week) {
//            $sql .= " and bbt.weekly_flag like '%". $week ."%'";
//        }
//        if($month) {
//            $sql .= " and bbt.monthly_flag like '%". $month ."%'";
//        }
//        if($year) {
//            $sql .= " and bbt.yearly_flag like '%". $year ."%'";
//        }
//        if($forever) {
//            $sql .= " and bbt.importance_flag like '%". $forever ."%'";
//        }
//        if(!empty($search)) {
//            $search = '%'.$search.'%';
//            $sql .= " and (bbt.timepoint like '".$search."' or bbt.task_name like '".$search."' or vbt.vm_name like '".$search."')";
//        }
        // 查看权限
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
            $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
        }

        $sql .= ' order by vbt.vm_uuid, bbt.timepoint';
        $pointData = $this->dbSelect($sql, $sqlParams);


        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($pointData as $point) {
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = array(
                    'timepoint' => $point['timepoint'],
                    'timepoint_uuid' => $point['timepoint_uuid']
                );
            } else {
                $unfullList[] = $point;

            }
        }
        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $k => $value) {
                    if ($dependId == $k) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$k];
                        $unfullList = array_splice($unfullList, $key, 1);

                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;

        }


        $timepoint = array();
        $utils = Xphp::instance('Utils');
        foreach ($pointData as $point) {

            $availableFlag = true;

            //如果时间点正在合并且不可用
            if ($point['archive_flag'] == Xphp::$_config['FLAG']['SET'] || $point['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']) {
                $availableFlag = false;
            }


            //判断是合并中还是离线状态
            $archive_status_str = true;
            $vm_status_str = true;

            //删除中
            $delete_status_str = false;

            //删除中
            if ($point['archive_flag'] == Xphp::$_config['FLAG']['UNSET'] && $point['deleted_flag'] == Xphp::$_config['FLAG']['SET']) {
                // //如果是在恢复页面不可用
                // if(!$manageflag){
                //     $availableFlag = false;
                // }
                $delete_status_str = true;
            }

            //如果是合并中
            if ($point['archive_flag'] == Xphp::$_config['FLAG']['SET']) {
                $archive_status_str = false;
            }
            //如果不是在线状态
            if ($point['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']) {
                $vm_status_str = false;
            }

            $taskuuid = $point['task_uuid'];
            $vmuuid = $point['vm_uuid'];
            $timepointuuid = $point['timepoint_uuid'];
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
            $config = json_decode($point['detail'], true);
            $mark = $this->pGetTimepointMark($utils->parseFlagToBool($point['weekly_flag']), $utils->parseFlagToBool($point['monthly_flag']), $utils->parseFlagToBool($point['yearly_flag']), $utils->parseFlagToBool($point['importance_flag']));
            //检查并添加完备点


            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                if (!in_array($timepointuuid, $timepoint)) {
                    $name = $this->parseDate($point['timepoint']) . " (" . $this->getTimepointTypeDes($point['backup_mode']) . ")";

                    //根据合并和是否在线展示不同文字信息
                    if (!$archive_status_str) {
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_IN_MERGE'] . ")";
                    } else if ($delete_status_str) {
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_MERGE_ERROR'] . ")";
                    }
                    if (!$vm_status_str) {
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_STORAGE_OFF'] . ")";
                    }

                    if (!empty($config) && $config['password_auto_flag'] == 2 && !empty($config['password'])) {
                        $name .= '<i class="fa fa-lock"></i>';
                    }
                    $timepoint[] = $timepointuuid;
                    $node[] = array(
                        "id" => $timepointuuid,
                        "pId" => $point['hypervisor_type'] . $vmuuid . $taskuuid,
                        "name" => $name . " " . $mark,
                        "oldname" => $name,
                        // "checked" =>  $availableFlag,
                        "type" => 3,
                        "vmuuid" => $point['vm_uuid'],
                        "vmname" => $point['vm_name'],
                        "pointname" => $this->parseDate($point['timepoint']),
                        "vcenteruuid" => $point['vcenter_uuid'],
                        "timepointuuid" => $timepointuuid,
                        "createtime" => $taskCreateTimeIn,
                        "hypervisor" => $point['hypervisor_type'],
                        "nodeuuid" => $point['node_uuid'],
                        "taskuuid" => $taskuuid,
                        "path" => $point['dir_path'],
                        "version" => $point['version'],
                        "icon" => $this->getTimepointIcon($point['backup_mode']),
                        "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                        "hypervisor" => $point['hypervisor_type'],
                        "chkDisabled" => !$availableFlag,
                        "config" => $config,
                        "backup_mode" => intval($point['backup_mode'])
                    );
                    // //添加了完全备份时间点继续下一次
//                     $pid = $timepointuuid;
                    continue;
                }
            }

            $name = $this->parseDate($point['timepoint']) . " (" . $this->getTimepointTypeDes($point['backup_mode']) . ")";
            //根据合并和是否在线展示不同文字信息
            if (!$archive_status_str) {
                $name .= "(" . Xphp::$_lang['UI_PUBLIC_IN_MERGE'] . ")";
            } else if ($delete_status_str) {
                $name .= "(" . Xphp::$_lang['UI_PUBLIC_MERGE_ERROR'] . ")";
            }
            if ($point['backup_mode'] != Xphp::$_config['BACKUP_MODE']['FULL']) {
                $node[] = array(
                    "id" => $timepointuuid,
                    "pId" => $fulluuidList[$timepointuuid]['timepoint_uuid'],
                    "name" => $name . " " . $mark,
                    "oldname" => $name,
                    "type" => 4,
                    "vmuuid" => $point['vm_uuid'],
                    "vmname" => $point['vm_name'],
                    "pointname" => $this->parseDate($point['timepoint']),
                    "vcenteruuid" => $point['vcenter_uuid'],
                    "nodeuuid" => $point['node_uuid'],
                    "timepointuuid" => $timepointuuid,
                    "hypervisor" => $point['hypervisor_type'],
                    "path" => $point['dir_path'],
                    "version" => $point['version'],
                    "icon" => $this->getTimepointIcon($point['backup_mode']),
                    "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                    "chkDisabled" => true,
                    "config" => $config,
                    "backup_mode" => intval($point['backup_mode'])
                );
            }
        }
        return json_encode($node);
    }

    /**
     * 找增量点的完备点
     * @param unknown $params
     */
    private function getFulllPoint($timepoint_uuid)
    {
        $sqlfull = "select bbt.detail, bbt.deleted_flag, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,bbt.weekly_flag,bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag, vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.node_uuid, bsr.status from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and bbt.import_flag = 2 and bbt.module_type in (2, 9) and data_local_flag = 1 and bbt.timepoint and bbt.timepoint_uuid = ? order by vbt.vm_uuid, bbt.timepoint";
        $data = $this->dbSelect($sqlfull, array($timepoint_uuid));
        if (!empty($data[0]['depend_point_uuid'])) {//不是完备点继续找
            return $this->getFulllPoint($data[0]['depend_point_uuid']);
        } else {
            return $data[0];
        }
    }






    //--------------------------------------------华为CBR--start-----------------------------------------------

    // //获取华为CBR的树 前四层
    public function getCBRDetailsTreeNew($params)
    {
        $sql = "select vt.uuid, vt.name, vt.type, vt.parent_uuid, vt.detail,vt.vcenter_uuid 
        from vm_tree vt right join bd_storage_resource bsr
        on bsr.storage_uuid = vt.vcenter_uuid and bsr.storage_type = ?";
        $sqlParams = array(Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']);
        if (!empty($params['storageuuid'])) {
            $sql .= " where bsr.storage_uuid  = ?";
            $sqlParams = array(Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'], $params['storageuuid']);
        } else {
            $sqlParams = array(Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();
        $projectids = [];
        $areaids = [];
        $storageids = [];
        //第一层
        $tree[] = array(
            "id" => "1",
            "pid" => 0,
            "name" => Xphp::$_lang['UI_STORAGE_TYPE12'],
            "clickshow" => true,
            "nocheck" => true,
            "iconSkin" => "vm_huawei_cbr",
            "isParent" => true,
            "type" => 0,
            "title" => Xphp::$_lang['UI_STORAGE_TYPE12'],
        );
        foreach ($data as $d) {
            //  $detail = json_decode($d['detail'], true);
            //最底层存储是项目 通过项目找到区域 通过区域找到存储
            //存储库进行判断
            //需要根据类型进行判断 存储5 区域4 项目7
            if ($d['type'] == 5) {
                if (!in_array($d['uuid'], $storageids)) {
                    //存储库 第二层
                    $storageids[] = $d['uuid'];
                    $tree[] = array(
                        "id" => $d['uuid'],
                        "pid" => 1,
                        "name" => $d['name'],
                        "clickshow" => true,
                        "nocheck" => true,
                        "iconSkin" => "vm_huawei_cbr",
                        "isParent" => true,
                        "type" => 1,
                        "title" => $d['name'],
                        "vcenteruuid" => $d['vcenter_uuid']
                    );
                }
            } else if ($d['type'] == 4) {
                //区域进行判断
                //存储id：【区域id】
                $areaids[$d['vcenter_uuid']] = $areaids[$d['vcenter_uuid']] ?? [];
                if (!in_array($d['uuid'], $areaids[$d['vcenter_uuid']])) {
                    //区域 第三层
                    $areaids[$d['vcenter_uuid']][] = $d['uuid'];
                    $tree[] = array(
                        "id" => $d['vcenter_uuid'] . "_" . $d['uuid'],
                        "pid" => $d['parent_uuid'],
                        "name" => $d['name'],
                        "clickshow" => true,
                        "nocheck" => true,
                        "iconSkin" => "vm_huawei_cbr_area",
                        "isParent" => true,
                        "type" => 2,
                        "title" => $d['name'],
                        "vcenteruuid" => $d['vcenter_uuid']
                    );
                }
            } else if ($d['type'] == 7) {
                //项目进行判断
                $projectids[$d['vcenter_uuid']] = $projectids[$d['vcenter_uuid']] ?? [];
                if (!in_array($d['uuid'], $projectids[$d['vcenter_uuid']])) {
                    //项目 第四层
                    $projectids[$d['vcenter_uuid']][] = $d['uuid'];
                    $tree[] = array(
                        "id" => $d['vcenter_uuid'] . "_" . $d['uuid'], //项目id
                        "pid" => $d['vcenter_uuid'] . "_" . $d['parent_uuid'], //区域id
                        "name" => $d['name'], //项目名
                        "clickshow" => true,
                        "nocheck" => true,
                        "eventtype" => "project",
                        "iconSkin" => "vm_huawei_cbr_project",
                        "isParent" => true,
                        "type" => 3,
                        "checked" => false,
                        "title" => $d['name'],
                        "vcenteruuid" => $d['vcenter_uuid']
                    );
                }
            }
        }
        //不管是新增还是修改 如果没有添加CBR存储 那么返回空数组
        if (count($tree) == 1) {
            return json_encode([]);
        }
        if ($params['backupflag']) {
            return json_encode($tree);
        }
        //修改
        if ($params['editflag']) {
            return $this->getCBREditTree($tree, $params);

        }
    }
    // /**
    //  * 根据虚拟化类型得到树图标
    //  * @param unknown $hypervisor
    //  * @param unknown $vcenter_flag 是否是VCENTER
    //  * @param liushuai@vinchin.com
    //  */
    // public function getHypervisorIcon($hypervisor, $vcenter_flag = true){
    //     $hypervisor = intval($hypervisor);
    //     $this->paramsCheck($hypervisor);
    //     $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
    //     if($vcenter_flag == Xphp::$_config['FLAG']['UNSET']){
    //         //如果不是vcenter
    //         return $iconskin."_host";
    //     }else if($vcenter_flag == Xphp::$_config['FLAG']['SET']){
    //         return $iconskin."_vcenter";
    //     }else if($vcenter_flag == 3){
    //         return $iconskin."_hostcluster";
    //     }

    //     return $iconskin;
    // }

    /**
     * 获取修改时华为CBRTree
     * @param array $params
     */
    public function getCBREditTree($tree, $params)
    {
        $utils = Xphp::instance('Utils');
        //通过detail来判断
        $sql = "select detail from vm_task where task_uuid = ?";
        $sqlParams = array($params['taskuuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $detail = json_decode($data[0]['detail'], true);
        $resources = $detail['cbr_sync_list'];
        //如果detail里面的storage_uuid不等于请求的storage_uuid 直接返回tree
        if (!empty($params['storage_uuid_list'])) { //刷新时用
            $getuuid = $params['storage_uuid_list'][0]['storage_uuid'];
            $detailuuid = $resources[0]['storage_uuid'];
            if ($getuuid != $detailuuid) {
                return json_encode($tree);
            }
        }


        //通过项目获取存储库
        //如果有多个存储库 通过循环遍历获取
        $questparams = array();
        //如果是按照存储库同步
        if ($params['type'] == 1) {
            //构建获取存储库参数 设置参数
            foreach ($resources as $resource) {
                $questparam = array(
                    "storage_uuid" => $resource['storage_uuid'],
                    "region_id" => $resource['region_id'],
                    "project_id" => $resource['project_id'],
                    "vault_id" => $resource['vault_id'],
                    "resource_flag" => $utils->parseBoolToFlag(false)
                );
                $questparams[] = $questparam;
            }
            //请求接口
            $opName = "NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST";
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($questparams), true);
        } else if ($params['type'] == 2) {
            //如果是按照虚拟机同步 获取当前存储库下的所有虚拟机
            foreach ($resources as $resource) {
                $questparam = array(
                    "storage_uuid" => $resource['storage_uuid'],
                    "region_id" => $resource['region_id'],
                    "project_id" => $resource['project_id'],
                    "vault_id" => $resource['vault_id'],
                    "resource_flag" => $utils->parseBoolToFlag(true)
                );
                $questparams[] = $questparam;
            }
            $opName = "NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST";
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($questparams), true);
        } else if ($params['type'] == 3) {
            //如果是按照时间点进行同步 得到当前存储库下的当前时间点
            foreach ($resources as $resource) {
                $questparam = array(
                    "storage_uuid" => $resource['storage_uuid'],
                    "region_id" => $resource['region_id'],
                    "project_id" => $resource['project_id'],
                    "backup_id" => $resource['backup_id'],
                );
                $questparams[] = $questparam;
            }
            $opName = "NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST";
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($questparams), true);
        }
        //这里统一写调用接口的方法

        // 写入假数据
        // if($params['type']  == 1){
        //     // 写入假数据
        //     $mbResult =  array(
        //         "error_code" => 0,
        //         "result"=> true,
        //         "msg"=>array(
        //             "vaults"=>[
        //                 array(
        //                     "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19c",
        //                     "name"=>"vault-8538",
        //                     ),
        //             ]
        //         )
        //     );
        // }else if($params['type']  == 2){
        //     $mbResult =  array(
        //         "error_code" => 0,
        //         "result"=> true,
        //         "msg"=>array(
        //             "vaults"=>[
        //                 array(
        //                     "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19c",
        //                     "name"=>"vault-8538",
        //                     "resources"=>[
        //                         array(
        //                             "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19d",
        //                             "name"=>"resource-8538"
        //                         ),
        //                         array(
        //                             "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19e",
        //                             "name"=>"resource-8539"
        //                         )
        //                     ]
        //                 )
        //             ]
        //         )
        //     );
        // }elseif($params['type']  == 3){
        //     $mbResult =  array(
        //         "error_code" => 0,
        //         "result"=> true,
        //         "msg"=>array(
        //             "backups"=>[
        //                 array(
        //                     "id"=>"b1c4afd9-e7a6-4888-9010-c2bac3aa7910",
        //                     "name"=>"autobk_b629",
        //                     "resource_id"=>"1a503932-ee8f-4dd5-8248-8dfb57e584c5",
        //                     "resource_name"=>"test001-02",
        //                     "created_at"=>"2020-02-21T07:00:54.065135",
        //                     "vault_id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19c",
        //                     "vault_name"=>"vault-8538"
        //                 )
        //             ]
        //         )
        //     );
        // }
        //如果获取失败
        $opcodeHandler = Xphp::instance('NodeOpcode');
        $opcodeDes = $opcodeHandler->getOpcodeDes($opName);
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        }
        //处理数据
        if ($params['type'] == 1) {
            $gettree = $this->handleDataVault($mbResult, $resources, $tree);
        }
        //如果是按照虚拟机同步
        else if ($params['type'] == 2) {
            $gettree = $this->handleDataResource($mbResult, $resources, $tree);
        }
        //如果是按照时间点同步
        else if ($params['type'] == 3) {
            $gettree = $this->handleDataBackups($mbResult, $resources, $tree);

        }
        return json_encode($gettree);
    }
    /**
     * 处理华为CBR修改时的同步存储库数据
     * @param array $params
     */
    public function handleDataVault($mbResult, $resources, $tree)
    {
        //构建存储库节点
        $vaults = $mbResult['msg']['vaults'];
        //var_dump("vaults----",json_encode($vaults));
        for ($i = 0; $i < count($vaults); $i++) {
            //通过存储库id 找到项目id 存储id 在resource里面找
            $project_id = "";
            $storage_id = "";
            foreach ($resources as $resource) {
                if ($resource['vault_id'] == $vaults[$i]['id']) {
                    $project_id = $resource['project_id'];
                    $storage_id = $resource['storage_uuid'];
                }
            }
            //这里需要做处理
            $vaultnode = array(
                "id" => $vaults[$i]['id'],
                "pid" => $storage_id . "_" . $project_id,
                "name" => $vaults[$i]['name'],
                "clickshow" => true,
                "eventtype" => "mp",
                "iconSkin" => "vm_huawei_cbr_memorypool",
                "isParent" => false,
                "nocheck" => false,
                "checked" => true,
                "type" => 4,
                "title" => $vaults[$i]['name']
            );
            //
            $tree[] = $vaultnode;
        }
        //设置项目那一层是否可以选择并且是否需要勾选
        $projectvaults = array();
        foreach ($resources as $resource) {
            $projectvaults[$resource['project_id']][] = $resource['vault_id'];
            //设置该存储库上的项目是否需要全选
            //将资源按照项目分组 创建一个对象 第一个是项目id  第二个是存储库id
            // {
            //     "projectid":["vaultid1","vaultid2"]
            // }
        }
        for ($i = 0; $i < count($tree); $i++) {
            foreach ($resources as $resource) {
                //获取项目id
                $treeproid = "";
                if ($tree[$i]['eventtype'] == "project") {
                    $storageprojectid = explode("_", $tree[$i]['id']);
                    $treeproid = $storageprojectid[1];
                }
                if (($resource['project_id'] == $treeproid) && ($resource['storage_uuid']) == $tree[$i]['vcenteruuid']) {
                    //如果已经设置了全选/勾选 则不继续下面的步骤
                    if (!$tree[$i]['nocheck'])
                        continue;
                    //是否可以勾选
                    $tree[$i]['nocheck'] = false;
                    //是否设置全选
                    $tree[$i]['checked'] = true; //设置该项目节点成勾选状态

                    $mpselectnodecount = count($projectvaults[$resource['project_id']]);
                    // 获取本来的该项目下的所有存储库
                    $getmpcountpar = array(
                        "storage_uuid" => $resource['storage_uuid'],
                        "region_id" => $resource['region_id'],
                        "project_id" => $resource['project_id'],
                        "current_page" => 1,
                        "limit" => 1,
                        "need_resource" => false
                    );
                    $projectinfo = json_decode($this->getCBRMP($getmpcountpar), true);
                    //获取当前项目下的总存储库数量
                    $totalvaultnum = $projectinfo['total_pages']; //由于分页是1  总页数等于总条数
                    //如果选择的存储库的数量=总数量  设置成勾选
                    if ($totalvaultnum == $mpselectnodecount) {

                    } else {
                        //添加加载更多节点
                        $mploadmorenode = array(
                            "id" => $resource['project_id'] . "_" . "loadmore",
                            "pid" => $resource['storage_uuid'] . "_" . $resource['project_id'],
                            "name" => Xphp::$_lang['WEB_M365_LOAD_MORE'],
                            "clickshow" => false,
                            "eventtype" => "mploadmore",
                            "iconSkin" => "loadmore",
                            "isParent" => false,
                            "nocheck" => true,
                            "checked" => false,
                            "type" => 4
                        );
                        $tree[] = $mploadmorenode;
                    }
                }
            }

        }
        return $tree;

    }
    /**
     * 处理华为CBR修改时的同步虚拟机数据
     * @param array $params
     */
    public function handleDataResource($mbResult, $resources, $tree)
    {
        //构建存储库和虚拟机节点
        $vaults = $mbResult['msg']['vaults'];
        for ($i = 0; $i < count($vaults); $i++) {
            //通过存储库id 找到项目id 在resource里面找
            $project_id = "";
            $storage_id = "";
            foreach ($resources as $resource) {
                if ($resource['vault_id'] == $vaults[$i]['id']) {
                    $project_id = $resource['project_id'];
                    $storage_id = $resource['storage_uuid'];
                }
            }
            $vaultnode = array(
                "id" => $vaults[$i]['id'],
                "pid" => $storage_id . "_" . $project_id,
                "name" => $vaults[$i]['name'],
                "clickshow" => true,
                "eventtype" => "mp",
                "iconSkin" => "vm_huawei_cbr_memorypool",
                "isParent" => false,
                "nocheck" => false,
                "checked" => true,
                "type" => 4,
                "title" => $vaults[$i]['name']
            );
            $tree[] = $vaultnode;
            //构建虚拟机节点
            foreach ($vaults[$i]['resources'] as $vmresource) {
                //由于这里拿到的是所有虚拟机 因此需要判断某些虚拟机是否进行勾选
                $vmcheckflag = false;
                foreach ($resources as $resource) {
                    if ($vmresource['id'] == $resource['resource_id']) {
                        $vmcheckflag = true;
                    }
                }
                $vmnode = array(
                    "id" => $vmresource['id'],
                    "pid" => $vaults[$i]['id'],
                    "name" => $vmresource['name'],
                    "eventtype" => "vm",
                    "iconSkin" => "vm_huawei_cbr_vm",
                    "isParent" => false,
                    "nocheck" => false, //如果是按虚拟机进行同步 这是最底层  故设置成false
                    "checked" => $vmcheckflag,
                    "type" => 5,
                    "title" => $vmresource['name'],
                );
                $tree[] = $vmnode;
            }
            //由于存储库会自己设置成勾选（如果选择完了当前存储库下的虚拟机） 只需要设置项目是否可以勾选

        }

        //设置项目那一层是否可以选择并且是否需要勾选
        $projectvaults = array();
        foreach ($resources as $resource) {
            $projectvaults[$resource['project_id']][] = $resource['vault_id'];
            //设置该存储库上的项目是否需要全选
            //将资源按照项目分组 创建一个对象 第一个是项目id  第二个是存储库id
            // {
            //     "projectid":["vaultid1","vaultid2"]
            // }
        }
        for ($i = 0; $i < count($tree); $i++) {
            foreach ($resources as $resource) {
                $storageproid = $resource['storage_uuid'] . "_" . $resource['project_id'];
                if ($storageproid == $tree[$i]['id']) {
                    //如果已经设置了全选/勾选 则不继续下面的步骤
                    if (!$tree[$i]['nocheck'])
                        continue;
                    //是否可以勾选
                    $tree[$i]['nocheck'] = false;
                    //是否设置全选
                    $tree[$i]['checked'] = true; //设置该项目节点成勾选状态
                    $mpselectnodecount = count($projectvaults[$resource['project_id']]);
                    // 获取本来的该项目下的所有存储库
                    $getmpcountpar = array(
                        "storage_uuid" => $resource['storage_uuid'],
                        "region_id" => $resource['region_id'],
                        "project_id" => $resource['project_id'],
                        "current_page" => 1,
                        "limit" => 1,
                        "need_resource" => false
                    );
                    $projectinfo = json_decode($this->getCBRMP($getmpcountpar), true);
                    //获取当前项目下的总存储库数量
                    $totalvaultnum = $projectinfo['total_pages']; //由于分页是1  总页数等于总条数
                    //如果选择的存储库的数量=总数量  设置成勾选
                    if ($totalvaultnum == $mpselectnodecount) {

                    } else {
                        //添加加载更多节点
                        $mploadmorenode = array(
                            "id" => $resource['project_id'] . "_" . "loadmore",
                            "pid" => $resource['storage_uuid'] . "_" . $resource['project_id'],
                            "name" => Xphp::$_lang['WEB_M365_LOAD_MORE'],
                            "clickshow" => false,
                            "eventtype" => "mploadmore",
                            "iconSkin" => "loadmore",
                            "isParent" => false,
                            "nocheck" => true,
                            "checked" => false,
                            "type" => 4
                        );
                        $tree[] = $mploadmorenode;
                    }
                }
            }
        }
        return $tree;
    }
    /**
     * 处理华为CBR修改时的同步时间点数据
     * @param array $params
     */
    public function handleDataBackups($mbResult, $resources, $tree)
    {
        //构建存储库 时间点节点
        $backups = $mbResult['msg']['backups'];
        $vaultslist = array();//由于存储库节点需要去重
        for ($i = 0; $i < count($backups); $i++) {
            //通过存储库id 找到项目id 在resource里面找
            $project_id = "";
            foreach ($resources as $resource) {
                if ($resource['backup_id'] == $backups[$i]['id']) {
                    if (!in_array($resource['vault_id'], $vaultslist)) {
                        $vaultslist[] = $resource['vault_id'];
                        //设置存储库节点
                        $project_id = $resource['storage_uuid'] . "_" . $resource['project_id'];
                        $vaultnode = array(
                            "id" => $resource['vault_id'],
                            "pid" => $project_id,
                            "name" => $backups[$i]['vault_name'],
                            "clickshow" => true,
                            "eventtype" => "mp",
                            "iconSkin" => "vm_huawei_cbr_memorypool",
                            "isParent" => true,
                            "nocheck" => true,
                            "checked" => true,
                            "type" => 4,
                            "title" => $backups[$i]['vault_name'],
                            "vcenteruuid" => $resource['storage_uuid']
                        );
                        $tree[] = $vaultnode;
                    }
                    //设置时间点节点
                    $backupnode = array(
                        "id" => $backups[$i]['id'],
                        "pid" => $resource['vault_id'],
                        "name" => $backups[$i]['name'] . "(" . $backups[$i]['created_at'] . ")",
                        "eventtype" => "tp",
                        "parentname" => $backups[$i]['resource_name'],
                        "parentid" => $backups[$i]['resource_id'],
                        "iconSkin" => "vm_huawei_cbr_timepoint",
                        "isParent" => false,
                        "nocheck" => false,
                        //   "path"=>"华为CBR/华为CBR存储1/区域1/项目1/存储库1/虚拟机1",
                        "checked" => true,
                        "type" => 6,
                        "title" => $backups[$i]['name'] . "(" . $backups[$i]['created_at'] . ")",
                    );
                    $tree[] = $backupnode;
                }
            }
        }
        //设置项目那一层是否可以选择并且是否需要勾选
        // 存储 区域 项目 存储库 时间点
        $projectvaults = array();
        $vaultbackups = array();
        foreach ($resources as $resource) {
            if (!in_array($resource['vault_id'], $projectvaults[$resource['project_id']])) {
                $projectvaults[$resource['project_id']][] = $resource['vault_id'];
            }
            //时间点肯定不一样 不用做判断
            $vaultbackups[$resource['vault_id']][] = $resource['backup_id'];
            //设置该存储库上的项目是否需要全选
            //将资源按照项目分组 创建一个对象 第一个是项目id  第二个是存储库id
            // {
            //     "projectid":["vaultid1","vaultid2"]
            // }
            //  {
            //     "vaultid":['backupid1','backupid2']
            //  }
        }
        for ($i = 0; $i < count($tree); $i++) {
            foreach ($resources as $resource) {
                //获取项目id
                $treeproid = "";
                if ($tree[$i]['eventtype'] == "project") {
                    $storageprojectid = explode("_", $tree[$i]['id']);
                    $treeproid = $storageprojectid[1];
                }
                if ($resource['project_id'] == $treeproid && $resource['storage_uuid'] == $tree[$i]['vcenteruuid']) {
                    //如果已经设置了全选/勾选 则不继续下面的步骤
                    if (!$tree[$i]['nocheck'])
                        continue;
                    //是否可以勾选
                    $tree[$i]['nocheck'] = false;
                    //是否设置全选
                    $tree[$i]['checked'] = true; //设置该项目节点成勾选状态
                    $mpselectnodecount = count($projectvaults[$resource['project_id']]);
                    // 获取本来的该项目下的所有存储库
                    $getmpcountpar = array(
                        "storage_uuid" => $resource['storage_uuid'],
                        "region_id" => $resource['region_id'],
                        "project_id" => $resource['project_id'],
                        "current_page" => 1,
                        "limit" => 1,
                        "need_resource" => false
                    );
                    $projectinfo = json_decode($this->getCBRMP($getmpcountpar), true);
                    //获取当前项目下的总存储库数量
                    $totalvaultnum = $projectinfo['total_pages']; //由于分页是1  总页数等于总条数
                    //如果选择的存储库的数量=总数量  设置成勾选
                    if ($totalvaultnum == $mpselectnodecount) {

                    } else {
                        //添加加载更多节点
                        $mploadmorenode = array(
                            "id" => $resource['project_id'] . "_" . "loadmore",
                            "pid" => $resource['storage_uuid'] . "_" . $resource['project_id'],
                            "name" => Xphp::$_lang['WEB_M365_LOAD_MORE'],
                            "clickshow" => false,
                            "eventtype" => "mploadmore",
                            "iconSkin" => "loadmore",
                            "isParent" => false,
                            "nocheck" => true,
                            "checked" => false,
                            "type" => 4
                        );
                        $tree[] = $mploadmorenode;
                    }
                }
                //设置存储库勾选 是否需要加载更多
                if ($resource['vault_id'] == $tree[$i]['id'] && $resource['storage_uuid'] == $tree[$i]['vcenteruuid']) {
                    //如果已经设置了全选/勾选 则不继续下面的步骤
                    if (!$tree[$i]['nocheck'])
                        continue;
                    //是否可以勾选
                    $tree[$i]['nocheck'] = false;
                    //是否设置全选
                    $tree[$i]['checked'] = true; //设置该存储库节点成勾选状态
                    $tpselectnodecount = count($vaultbackups[$resource['vault_id']]);
                    //获取该存储库下的所有时间点
                    $gettpcountpar = array(
                        "storage_uuid" => $resource['storage_uuid'],
                        "region_id" => $resource['region_id'],
                        "project_id" => $resource['project_id'],
                        "vault_id" => $resource['vault_id'],
                        "current_page" => 1,
                        "limit" => 1
                    );
                    $vaultinfo = json_decode($this->getCBRTP($gettpcountpar), true);
                    //获取当前存储库下的总时间点数量
                    $totalbackupnum = $vaultinfo['total_pages']; //由于分页是1  总页数等于总条数
                    if ($totalbackupnum != $tpselectnodecount) {
                        //需要添加加载更多节点（时间点）
                        $tploadmorenode = array(
                            "id" => $resource['vault_id'] . "_" . "loadmore",
                            "pid" => $resource['vault_id'],
                            "name" => Xphp::$_lang['WEB_M365_LOAD_MORE'],
                            "clickshow" => false,
                            "eventtype" => "tploadmore",
                            "iconSkin" => "loadmore",
                            "isParent" => false,
                            "nocheck" => true,
                            "checked" => false,
                            "type" => 6
                        );
                        $tree[] = $tploadmorenode;
                    }
                }

            }
        }
        return $tree;
    }


    public function getVmTreeDes(int $vm_tree_type)
    {
        $map = [
            'WEB_PLATFORM_PUBLIC_UNKNOWN',
            'WEB_PLATFORM_DES_FOLDER',
            'WEB_PLATFORM_DES_DATACENTER',
            'WEB_PLATFORM_DES_CLUSTER',
            'WEB_PLATFORM_DES_HOST',
            'WEB_PLATFORM_DES_POOL',
            'WEB_PLATFORM_DES_VAPP',
            'WEB_PLATFORM_DES_VM',
        ];
        return Xphp::$_lang[$map[$vm_tree_type]];
    }

    /**
     * 获取华为CBR存储库
     * @param array $params
     */
    public function getCBRMP($params)
    {
        //获取数据，强制转换数据，处理异常参数
        $utils = Xphp::instance('Utils');
        $storage_uuid = $params['storage_uuid'];
        $region_id = $params['region_id'];
        $project_id = $params['project_id'];
        $current_page = $params['current_page'];
        $show_type = $params['showtype'];
        $limit = $params['limit'];
        $need_resource = $params['need_resource'];
        $vcenter_uuid = $params['vcenter_uuid'];
        $pfMsg['storage_uuid'] = $storage_uuid;
        $pfMsg['region_id'] = $region_id;
        $pfMsg['project_id'] = $project_id;
        $pfMsg['current_page'] = $current_page;
        $pfMsg['limit_count'] = $limit;
        $pfMsg['resource_flag'] = $utils->parseBoolToFlag($need_resource);
        //获取操作码
        $opName = "NODE_HUAWEI_CBR_OP_GET_VAULTS";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg), true);
        // 写入假数据
        // $mbResult =  array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>array(
        //         "vaults"=>[
        //             array(
        //                 "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19c",
        //                 "name"=>"vault-8538",
        //                 ),
        //                 array(
        //                     "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19f",
        //                     "name"=>"vault-8539",
        //                 )

        //         ],
        //         "total_pages"=>3,
        //         "current_page"=> $current_page
        //     )
        // );
        // //  如果需要获取资源
        //     if($need_resource){
        //         $mbResult['msg']['vaults'][0]['resources'] = [
        //             array(
        //                 "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19d",
        //                 "name"=>"resource-8538"
        //             ),
        //             array(
        //                 "id"=>"a335f9e1-1628-4c64-a7be-38656e5ec19e",
        //                 "name"=>"resource-8539"
        //             )
        //         ];
        //     }
        //如果获取失败
        $opcodeHandler = Xphp::instance('NodeOpcode');
        $opcodeDes = $opcodeHandler->getOpcodeDes($opName);
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        } else {
            $mpisParentFlag = true;
            $mpnocheckFlag = false;


            if ($show_type == 1) {
                $mpisParentFlag = false;
            }
            if ($show_type == 3) {
                $mpnocheckFlag = true;
            }
            $msg = $mbResult['msg'];
            //循环
            $tree = array();
            foreach ($msg['vaults'] as $vault) {
                //如果是按存储库同步
                $storagenode = array(
                    "id" => $vault['id'],
                    "pid" => $project_id,
                    "name" => $vault['name'],
                    "clickshow" => true,
                    "eventtype" => "mp",
                    "iconSkin" => "vm_huawei_cbr_memorypool",
                    "isParent" => $mpisParentFlag,
                    "nocheck" => $mpnocheckFlag,
                    "checked" => false,
                    "type" => 4,
                    "title" => $vault['name']
                );
                $tree[] = $storagenode;
                //如果是按虚拟机进行同步 则需要再返回资源信息
                if ($show_type == 2) {
                    foreach ($vault['resources'] as $resource) {
                        $resourcenode = array(
                            "id" => $resource['id'],
                            "pid" => $vault['id'],
                            "name" => $resource['name'],
                            "eventtype" => "vm",
                            "iconSkin" => "vm_huawei_cbr_vm",
                            "isParent" => false,
                            "nocheck" => false, //如果是按虚拟机进行同步 这是最底层  故设置成false
                            "checked" => false,
                            "type" => 5,
                            "title" => $resource['name'],
                        );
                        $tree[] = $resourcenode;
                    }
                }
            }
            $mpinfo = array(
                "tree" => $tree,
                "total_pages" => $mbResult['msg']['total_pages'],  //总页数
                "current_page" => $mbResult['msg']['current_page'] //当前页数
            );
            return json_encode($mpinfo);
        }
    }
    /**
     * 获取华为CBR时间点
     * @param array $params
     */
    public function getCBRTP($params)
    {
        $storage_uuid = $params['storage_uuid'];
        $region_id = $params['region_id'];
        $project_id = $params['project_id'];
        $vault_id = $params['vault_id'];
        $current_page = $params['current_page'];
        $show_type = $params['showtype'];
        $limit = $params['limit'];
        $pfMsg['storage_uuid'] = $storage_uuid;
        $pfMsg['region_id'] = $region_id;
        $pfMsg['project_id'] = $project_id;
        $pfMsg['vault_id'] = $vault_id;
        $pfMsg['current_page'] = $current_page;
        $pfMsg['limit_count'] = $limit;
        //获取操作码
        $opName = "NODE_HUAWEI_CBR_OP_GET_BACKUPS";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        //编写sql语句 获取当前存储下的node_uuid
        $sql = "select node_uuid from bd_storage_resource where storage_uuid = ?";
        $data1 = $this->dbSelect($sql, array($storage_uuid));
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg), true);
        //写入假数据
        // $mbResult =  array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>array(
        //         "backups"=>[
        //             array(
        //                 "id"=>"b1c4afd9-e7a6-4888-9010-c2bac3aa7910",
        //                 "name"=>"autobk_b629",
        //                 "resource_id"=>"1a503932-ee8f-4dd5-8248-8dfb57e584c5",
        //                 "resource_name"=>"test001-02",
        //                 "created_at"=>"2020-02-21T07:00:54.065135"
        //             ),
        //             array(
        //                 "id"=>"b1c4afd9-e7a6-4888-9010-c2bac3aa7911",
        //                 "name"=>"autobk_b630",
        //                 "resource_id"=>"1a503932-ee8f-4dd5-8248-8dfb57e584c5",
        //                 "resource_name"=>"test001-02",
        //                 "created_at"=>"2020-02-21T07:00:54.065136"
        //             )
        //         ],
        //         "total_pages"=>3,
        //         "current_page" => $current_page
        //     )
        // );
        $opcodeHandler = Xphp::instance('NodeOpcode');
        $opcodeDes = $opcodeHandler->getOpcodeDes($opName);
        //如果获取失败
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        } else {
            $msg = $mbResult['msg'];
            //循环
            $tree = array();
            foreach ($msg['backups'] as $backup) {
                $timenode = array(
                    "id" => $backup['id'],
                    "pid" => $vault_id,
                    "name" => $backup['name'] . "(" . $backup['created_at'] . ")",
                    "eventtype" => "tp",
                    "parentname" => $backup['resource_name'],
                    "parentid" => $backup['resource_id'],
                    "iconSkin" => "vm_huawei_cbr_timepoint",
                    "isParent" => false,
                    "nocheck" => false,
                    "checked" => false,
                    "type" => 6,
                    "title" => $backup['name'] . "(" . $backup['created_at'] . ")",
                    "hypervisor" => Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][46], //恢复时候用
                    "nodeuuid" => $data1[0]['node_uuid'], //恢复的时候用
                );
                $tree[] = $timenode;
            }
            $mpinfo = array(
                "tree" => $tree,
                "total_pages" => $mbResult['msg']['total_pages'],  //总页数
                "current_page" => $mbResult['msg']['current_page'] //当前页数
            );
            return json_encode($mpinfo);
        }
    }
    /**
     * 得到cbr云存储同步存储类型 与storagehandler中的getBackupStorageList类似 //用于华为CBR 取消了租户
     * @param unknown $params
     */
    //这个方法暂时不用了
    public function getAddStorageTypeSelect()
    {
        $sql = "select mount_flag, node_uuid, storage_nickname, storage_uuid, storage_type, total_size, free_size, status, error_code 
        from bd_storage_resource where status = ? and mount_flag = ? 
        and error_code = ? and lan_free_flag = ? and use_mode in (0,1) and storage_type not in (8)";
        $sqlParams = array(
            Xphp::$_config['STORAGE_STATUS']['ONLINE'],
            Xphp::$_config['FLAG']['SET'],
            0,
            Xphp::$_config['FLAG']['UNSET']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $utils = Xphp::instance("Utils");
        $nodeHandler = Xphp::instance('NodeHandler');
        $storageHandler = Xphp::instance('StorageHandler');
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        // if (!empty($_SESSION['tenantuuid'])){
        //     $resourceHandler = Xphp::instance('ResourceHandler');
        //     $userHandler =  Xphp::instance('UsersHandler');
        //     $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        //     if(!empty($resourceInfo)){
        //         foreach ($resourceInfo as $r){
        //             $resourceList[] = $r['resource_uuid'];
        //         }
        //     }
        //     //获取用户配额
        // }
        foreach ($data as $d) {
            //如果是租户内部检查是否有该资源
            // if(!empty($_SESSION['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) continue;
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);

            $storageStatus = $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
            if ($storageStatus != Xphp::$_config['STORAGE_STATUS']['ONLINE'])
                continue;
            $nodeName = $storageHandler->getNodeName($d['node_uuid']);
            $name = "";
            $storageDes = $d['storage_nickname'];

            $storageDes .= "(" . $storageHandler->getStorageTypeDes($d['storage_type']) . ", " . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) .
                ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")";

            // if(empty($_SESSION['tenantuuid'])){
            //     $storageDes .= "(" . $storageHandler->getStorageTypeDes($d['storage_type']) . ", ".Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) .
            //     ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")";
            // }else{
            //     $quotaInfo = $userHandler->getUserQuotaInfo();
            //     if(!empty($quotaInfo['des'])){
            //         $storageDes .= "(" . $storageHandler->getStorageTypeDes($d['storage_type']) . ", " . $quotaInfo['des'] . ")";
            //     }
            // }
            $storageDes .= $name;

            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $storageDes,
                'name' => $d['storage_nickname'],
                'type' => intval($d['storage_type']),
                'total_size' => intval($d['total_size']),
                'free_size' => intval($d['free_size'])
            );
        }
        $platformHandler = Xphp::instance('PlatformHandler');
        $sortInfo = $platformHandler->_array_column($info, 'free_size');
        array_multisort($sortInfo, SORT_DESC, $info);
        return json_encode($info);
    }
    /**
     * 得到虚拟机备份任务名
     * @param unknown $params
     */
    public function getCBRSyncTaskName()
    {
        $taskName = Xphp::$_lang['WEB_VM_SYNC_TASK_NAME'];
        return $this->getValidTaskName($taskName);
        // return "CBR同步1";
    }

    /**
     * 下云同步树异步获取 刷新存储库信息
     * 异步获取CBR信息:先刷新,在从数据库获取
     * @param array $params
     * @return string
     */
    public function getSyncCBR($params)
    {
        $storageuuid = $params['storage_uuid_list'][0]['storage_uuid'];
        $pfMsg['storage_uuid_list'] = $params['storage_uuid_list'];
        $opName = "NODE_HUAWEI_CBR_OP_REFERSH";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg), false);
        //  //写入假数据
        // $mbResult =  array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>array()
        // );
        $opcodeHandler = Xphp::instance('NodeOpcode');
        $opcodeDes = $opcodeHandler->getOpcodeDes($opName);
        //如果获取失败
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        } else {
            //需要获取当前存储下的区域 项目
            $sql = "select vt.uuid, vt.name, vt.type, vt.parent_uuid, vt.detail,vt.vcenter_uuid  
            from vm_tree vt right join bd_storage_resource bsr
            on bsr.storage_uuid = vt.vcenter_uuid and bsr.storage_uuid = ?";
            $sqlParams = array($storageuuid);
            $data = $this->dbSelect($sql, $sqlParams);
            $tree = array();
            $projectids = [];
            $areaids = [];
            $storageids = [];
            foreach ($data as $d) {
                //  $detail = json_decode($d['detail'], true);
                //最底层存储是项目 通过项目找到区域 通过区域找到存储
                //存储库进行判断
                //需要根据类型进行判断 存储5 区域4 项目7
                if ($d['type'] == 5) {
                    if (!in_array($d['uuid'], $storageids)) {
                        //存储库 第二层
                        $storageids[] = $d['uuid'];
                        $tree[] = array(
                            "id" => $d['uuid'],
                            "pid" => 1,
                            "name" => $d['name'],
                            "clickshow" => true,
                            "nocheck" => true,
                            "iconSkin" => "vm_huawei_cbr",
                            "isParent" => true,
                            "type" => 1,
                            "title" => $d['name'],
                            "vcenteruuid" => $d['vcenter_uuid']
                        );
                    }
                } else if ($d['type'] == 4) {
                    //区域进行判断
                    $areaids[$d['vcenter_uuid']] = $areaids[$d['vcenter_uuid']] ?? [];
                    if (!in_array($d['uuid'], $areaids[$d['vcenter_uuid']])) {
                        //区域 第三层
                        $areaids[$d['vcenter_uuid']][] = $d['uuid'];
                        $tree[] = array(
                            "id" => $d['vcenter_uuid'] . "_" . $d['uuid'],
                            "pid" => $d['parent_uuid'],
                            "name" => $d['name'],
                            "clickshow" => true,
                            "nocheck" => true,
                            "iconSkin" => "vm_huawei_cbr_area",
                            "isParent" => true,
                            "type" => 2,
                            "title" => $d['name'],
                            "vcenteruuid" => $d['vcenter_uuid']
                        );
                    }
                } else if ($d['type'] == 7) {
                    //项目进行判断
                    $projectids[$d['vcenter_uuid']] = $projectids[$d['vcenter_uuid']] ?? [];
                    if (!in_array($d['uuid'], $projectids[$d['vcenter_uuid']])) {
                        //项目 第四层
                        $projectids[$d['vcenter_uuid']][] = $d['uuid'];
                        $tree[] = array(
                            "id" => $d['vcenter_uuid'] . "_" . $d['uuid'], //项目id
                            "pid" => $d['vcenter_uuid'] . "_" . $d['parent_uuid'], //区域id
                            "name" => $d['name'], //项目名
                            "clickshow" => true,
                            "nocheck" => true,
                            "eventtype" => "project",
                            "iconSkin" => "vm_huawei_cbr_project",
                            "isParent" => true,
                            "type" => 3,
                            "checked" => false,
                            "title" => $d['name'],
                            "vcenteruuid" => $d['vcenter_uuid']
                        );
                    }
                }
            }
            // return json_encode($tree);
            //添加
            if ($params['backupflag']) {
                return json_encode($tree);
            }
            //修改
            if ($params['editflag']) {
                return $this->getCBREditTree($tree, $params);

            }
            return json_encode($tree);
        }
    }
    /**
     * 得到已经已经在任务中存在的节点
     * @param array $params
     * @return string
     */
    public function getNodeExist($params)
    {
        $nodelist = [];
        //返回已经存在的任务使用的节点id
        if ($params['storetype'] == 2) {
            $nodelist = [21];
        }
        return json_encode($nodelist);
    }
    /**
     * 创建CBR同步任务
     * @param unknown $params
     */
    public function createCBRSyncJob($params)
    {
        $utils = Xphp::instance('Utils');
        //----------------------------获取数据，强制转换数据，处理异常参数---------------------------
        //任务名
        $task_name = htmlspecialchars_decode($params['taskName']);
        //数据检测
        $this->paramsCheck($task_name);
        // 创建和修改备份任务做检测(vcenter.class.php有调用)
        // $this->checkTaskLegal($operate, $params['srcInfo']['vminfo'], null);
        //租户内检查虚拟机个数是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $this->checkTenantVmAuth(count($params['srcInfo']['vminfo']), "");
        }
        //模块类型
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        //时间策略
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $params['strategygroupuuid']); //不确定strategygroupuuid
        //保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve'], $params['strategygroupuuid']);
        //传输策略
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer'], $params['strategygroupuuid']);
        //存储策略
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'], $params['strategygroupuuid']); //只有存储块大小
        //任务类型
        $task_type = Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC'];
        //备份等级 完全备份
        $backup_level = 2;
        //静默快照
        $quiesce_snapshot = $utils->parseBoolToFlag(false);
        //有效备份数据
        $valid_data_backup = $utils->parseBoolToFlag(false);
        //V-CBT
        $parse_fs_flag = $utils->parseBoolToFlag(false);
        //排除交换文件块
        $not_backup_swap_file_flag = $utils->parseBoolToFlag(false);
        //排除已删除文件
        $not_backup_deleted_file_flag = $utils->parseBoolToFlag(false);
        //排除分区间隙
        $not_backup_partition_gap_flag = $utils->parseBoolToFlag(false);
        //快照串行标志
        $serial_snapshot_flag = $utils->parseBoolToFlag(false);
        //提前创建快照
        $pre_create_snap_flag = $utils->parseBoolToFlag(false);
        //细粒度等级[不使用]
        $grain_level = 0;
        //传输线程
        $thread_num = intval($params['highInfo']['mode']['threadnum']);
        //限速策略
        $speed_limit_list = $this->groupTaskSpeedList($params['speedInfo'], $params['strategygroupuuid']);
        $speed_limit_strategy = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        $filter_criteria = $params['srcInfo']['vminfo'];
        //传输优先级
        $transport_priority = 1;
        //GFS保留策略
        $gfs_strategy_list = $params['highInfo']['reserve']['gfs_strategy_item_list'];
        $detail = $params['detail'];
        //存储信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //pfCreateBackupTaskMessage 未返回node_uuid
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo
        );
        //【不使用】显示模式 默认为1
        $display_mode = 1;
        //【不使用】全局筛选条件，默认为[]
        $global_select = [];
        //自动加入
        $auto_join_flag = $utils->parseBoolToFlag(false);
        //重置CBT
        $reset_cbt_flag = $utils->parseBoolToFlag(false);
        //返回了task_name module_type "auto_find_sr_flag storage_uuid time_strategy_list reserved_strategy transport_strategy storage_strategy
        //节点node_uuid
        // $node_uuid= $nodeInfo['node_uuid'];
        //------------------------整理参数--------------------------
        $pfMsg['task_type'] = $task_type;
        $pfMsg['backup_level'] = $backup_level;
        //静默快照
        $pfMsg['quiesce_snapshot'] = $quiesce_snapshot;
        //CBT模式
        $pfMsg['valid_data_backup'] = $valid_data_backup;
        //V-CBT
        $pfMsg['parse_fs_flag'] = $parse_fs_flag;
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = $not_backup_swap_file_flag;
        //排除删除文件块
        $pfMsg['not_backup_deleted_file_flag'] = $not_backup_deleted_file_flag;
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = $not_backup_partition_gap_flag;
        //快照模式
        $pfMsg['serial_snapshot_flag'] = $serial_snapshot_flag;
        $pfMsg['pre_create_snap_flag'] = $pre_create_snap_flag;
        $pfMsg['grain_level'] = $grain_level;
        //线程数量
        $pfMsg['thread_num'] = $thread_num;
        $pfMsg['speed_limit_strategy_list'] = $speed_limit_list;
        $pfMsg['speed_limit_strategy'] = $speed_limit_strategy;
        $pfMsg['transport_priority'] = $transport_priority;
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $gfs_strategy_list;
        $pfMsg['extension_info'] = json_encode($detail);
        $pfMsg['display_mode'] = $display_mode;
        $pfMsg['global_select'] = $global_select;
        $pfMsg['filter_criteria'] = $filter_criteria;
        $pfMsg['auto_join_flag'] = $auto_join_flag;
        $pfMsg['reset_cbt_flag'] = $reset_cbt_flag;
        $msg = json_encode($pfMsg);
        // var_dump("msg---",$msg);
        // return;
        $opName = 'BD_TASK_OP_HUAWEI_CBR_SYNC_CREATE';
        $mbResult = $this->mbVMMsg($nodeInfo['node_uuid'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CBR'], $opName, $msg, false);
        // // //写入假数据
        // $mbResult = array(
        //     "error_code" => 0,
        //     "result" => true,
        //     "msg" => array()
        // );
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if (!$result) {
            return $this->muOpResult(false, $operate, '', 'info', $mbResult['errorCode']);
        } else {
            return $this->muOpResult(true, $operate, '');
        }
    }

    /**
     * 获取虚拟机备份任务信息(修改任务用)
     */
    public function getCBRSyncTaskAllInfo($params)
    {
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        // $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, vt.appliance_uuid,
        //         vt.hypervisor_type, vt.level, vt.transport_priority, vt.quiesce_snapshot, vt.valid_data_backup, vt.serial_snapshot_flag, vt.parse_fs_flag, vt.not_backup_swap_file_flag, vt.not_backup_deleted_file_flag, vt.not_backup_partition_gap_flag, vt.pre_create_snap_flag,vt.detail,
        //         brs.strategy_type, brs.number,
        //         bts.encrypt_flag, bts.compress_flag,
        //         bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.encrypted_flag, bss.password_auto_flag, bss.password,
        //         bcs.is_integrity_check
        // from bd_task bt, vm_task vt, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss, bd_integrity_check_strategy bcs
        // where bt.task_uuid = vt.task_uuid
        // and bt.strategy_id = brs.strategy_id
        // and bt.strategy_id = bts.strategy_id
        // and bt.strategy_id = bss.strategy_id
        // and bt.task_uuid = bcs.task_uuid
        // and bt.task_uuid = ?";
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, vt.agent_uuid, 
        vt.hypervisor_type, vt.level, vt.transport_priority, vt.quiesce_snapshot, vt.valid_data_backup, vt.serial_snapshot_flag, vt.parse_fs_flag, vt.not_backup_swap_file_flag, vt.not_backup_deleted_file_flag, vt.not_backup_partition_gap_flag, vt.pre_create_snap_flag,vt.detail,
        brs.strategy_type, brs.number, brs.strategy_mode,
        bts.encrypt_flag, bts.compress_flag,
        bss.deduplication_flag, bss.block_size, bss.compressed_flag,  bss.compress_method, bss.encrypted_flag, bss.password_auto_flag, bss.password, bss.encrypt_method

        from bd_task bt, vm_task vt, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss
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
        $utils = Xphp::instance('Utils');
        if ($data) {
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
                //保留策略
                'brs' => array(
                    'type' => intval($data[0]['strategy_type']),
                    'number' => intval($data[0]['number']),
                    'strategyMode' => intval($data[0]['strategy_mode']),
                    'GFS' => $thisGFS,
                ),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid']
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
                    'compress' => $utils->parseFlagToBool($data[0]['compress_flag']),
                    'mode' => $this->getTransportModeToArr($data[0]['transport_priority'], $data[0]['hypervisor_type']),
                ),
                //存储策略
                'bss' => array(
                    'deduplication' => $utils->parseFlagToBool($data[0]['deduplication_flag']),
                    'blocksize' => intval($data[0]['block_size']) / 1024,
                    'compress' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'password_auto_flag' => $utils->parseFlagToBool($data[0]['password_auto_flag']),
                    'password' => base64_encode($data[0]['password']),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method'])
                ),
                //备份模式
                'mode' => array(
                    'snapshot' => $data[0]['level'] == Xphp::$_config['XENSERVER_BACKUP_LEVEL']['KEEP_SNAPSHOT'] ? true : false,
                    'incmode' => $data[0]['level'],
                    'quiescesnapshot' => $utils->parseFlagToBool($data[0]['quiesce_snapshot']),
                    'cbtmode' => $utils->parseFlagToBool($data[0]['valid_data_backup']),
                    'snapshotmode' => $data[0]['serial_snapshot_flag'],
                    'parsefsmode' => $utils->parseFlagToBool($data[0]['parse_fs_flag']),
                    'swapmode' => $utils->parseFlagToBool($data[0]['not_backup_swap_file_flag']),
                    'deletefilemode' => $utils->parseFlagToBool($data[0]['not_backup_deleted_file_flag']),
                    'gapmode' => $utils->parseFlagToBool($data[0]['not_backup_partition_gap_flag']),
                    'threadnum' => intval($data[0]['thread_num']),      //线程数量
                    'presnapshot' => $utils->parseFlagToBool($data[0]['pre_create_snap_flag'])
                ),
                // //虚拟机信息
                // 'vm_info' => $this->getVMEditInfo($taskUUID),
                //时间策略
                'timestrategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $this->getSpeedGlobalStrategyInfo($taskUUID),
                'nosnapshot' => $this->getXenSnapshotFlag($taskUUID),
                'backup_server_ip' => $data[0]['backup_server_ip'],
                'transport_ip_segment' => $data[0]['transport_ip_segment'],
                // //新增校验策略
                // 'verifyInfo' => array(
                //     'ischeck'=>$utils->parseFlagToBool($data[0]['is_integrity_check'])
                // ),
                //新增校验策略
                'verifyInfo' => array(
                    'ischeck' => $utils->parseFlagToBool(2)
                ),
                //详细信息
                "detail_info" => json_decode($data[0]['detail'], true),
            );
        }
        return json_encode($info);
    }

    //修改下云同步任务
    public function editCBRSyncJob($params)
    {
        $utils = Xphp::instance('Utils');
        //----------------获取数据，转换数据----------------
        $task_name = htmlspecialchars_decode($params['taskName']);
        $task_uuid = $params['taskuuid'];
        $this->paramsCheck($task_name, $task_uuid);
        // 创建和修改备份任务做检测(vcenter.class.php有调用)
        // $this->checkTaskLegal($operate, $params['srcInfo']['vminfo'],null);
        //租户内检查虚拟机个数是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['VM'], $params['srcInfo']['vminfo'], $task_uuid);
        }
        //模块类型
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        //时间策略
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $params['strategygroupuuid']); //不确定strategygroupuuid
        //保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve'], $params['strategygroupuuid']);
        //传输策略
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer'], $params['strategygroupuuid']);
        //存储策略
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'], $params['strategygroupuuid']); //只有存储块大小
        //任务类型
        $task_type = Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC'];
        //备份等级 完全备份
        $backup_level = 2;
        //静默快照
        $quiesce_snapshot = $utils->parseBoolToFlag(false);
        //有效备份数据
        $valid_data_backup = $utils->parseBoolToFlag(false);
        //V-CBT
        $parse_fs_flag = $utils->parseBoolToFlag(false);
        //排除交换文件块
        $not_backup_swap_file_flag = $utils->parseBoolToFlag(false);
        //排除已删除文件
        $not_backup_deleted_file_flag = $utils->parseBoolToFlag(false);
        //排除分区间隙
        $not_backup_partition_gap_flag = $utils->parseBoolToFlag(false);
        //快照串行标志
        $serial_snapshot_flag = $utils->parseBoolToFlag(false);
        //提前创建快照
        $pre_create_snap_flag = $utils->parseBoolToFlag(false);
        //细粒度等级[不使用]
        $grain_level = 0;
        //传输线程
        $thread_num = intval($params['highInfo']['mode']['threadnum']);
        //限速策略
        $speed_limit_list = $this->groupTaskSpeedList($params['speedInfo'], $params['strategygroupuuid']);
        $speed_limit_strategy = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        $filter_criteria = $params['srcInfo']['vminfo'];
        //传输优先级
        $transport_priority = 1;
        //GFS保留策略
        $gfs_strategy_list = $params['highInfo']['reserve']['gfs_strategy_item_list'];
        $detail = $params['detail'];
        //存储信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //pfCreateBackupTaskMessage 未返回node_uuid
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo
        );
        //【不使用】显示模式 默认为1
        $display_mode = 1;
        //【不使用】全局筛选条件，默认为[]
        $global_select = [];
        //自动加入
        $auto_join_flag = $utils->parseBoolToFlag(false);
        //重置CBT
        $reset_cbt_flag = $utils->parseBoolToFlag(false);
        //节点node_uuid
        // $node_uuid= $nodeInfo['node_uuid'];
        //----------------------------整理参数----------------------------
        $pfMsg['task_uuid'] = $task_uuid; //修改的时候新增了任务id
        $pfMsg['task_type'] = $task_type; //新定义一个任务类型 后续需要修改
        $pfMsg['backup_level'] = $backup_level;
        //静默快照
        $pfMsg['quiesce_snapshot'] = $quiesce_snapshot;
        //CBT模式
        $pfMsg['valid_data_backup'] = $valid_data_backup;
        //V-CBT
        $pfMsg['parse_fs_flag'] = $parse_fs_flag;
        //排除交换文件块
        $pfMsg['not_backup_swap_file_flag'] = $not_backup_swap_file_flag;
        //排除删除文件块
        $pfMsg['not_backup_deleted_file_flag'] = $not_backup_deleted_file_flag;
        //排除分区间隙
        $pfMsg['not_backup_partition_gap_flag'] = $not_backup_partition_gap_flag;
        //快照模式
        $pfMsg['serial_snapshot_flag'] = $serial_snapshot_flag;
        $pfMsg['pre_create_snap_flag'] = $pre_create_snap_flag;
        $pfMsg['grain_level'] = $grain_level;
        //线程数量
        $pfMsg['thread_num'] = $thread_num;
        $pfMsg['speed_limit_strategy_list'] = $speed_limit_list;
        // 全局限速策略配置
        $pfMsg['speed_limit_strategy'] = $speed_limit_strategy;
        $pfMsg['transport_priority'] = $transport_priority;
        //GFS保留策略
        $pfMsg['gfs_strategy_item_list'] = $gfs_strategy_list;
        $pfMsg['extension_info'] = json_encode($detail);
        $pfMsg['display_mode'] = $display_mode;
        $pfMsg['global_select'] = $global_select;
        $pfMsg['filter_criteria'] = $filter_criteria;
        $pfMsg['auto_join_flag'] = $auto_join_flag;
        $pfMsg['reset_cbt_flag'] = $reset_cbt_flag;
        $opName = 'BD_TASK_OP_HUAWEI_CBR SYNC_MODIFY';  //定义操作名 操作名待确定
        $msg = json_encode($pfMsg);
        $mbResult = $this->mbVMMsg($nodeInfo['node_uuid'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CBR'], $opName, $msg);
        // //写入假数据
        // $mbResult = array(
        //     "error_code" => 0,
        //     "result" => true,
        //     "msg" => array()
        // );

        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if (!$result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 备份数据管理,得到备份数据树 与getTimepointTree方法类似
     * @param unknown $params
     */
    public function getBackupTimepointTree($params)
    {
        $showtype = intval($params['showtype']);
        $storageuuid = $params['storage'];           //节点UUID,为空的时候显示所有节点数据
        $instantflag = $params['instantflag'];      //瞬时恢复标志,瞬时恢复的时候在按时间点分组展示方式的时候,时间点没有选择框
        $grainflag = $params['grainflag'];          //细粒度恢复标记
        $manageflag = $params['manageflag'];       //备份数据管理标志,备份数据管理的树带有checkbox
        $disabledflag = $params['disabledflag'];    //备份数据禁用勾选增量差异标志
        $exportflag = $params['exportflag'];        //备份数据导出的标志,暂时只支持VMware备份数据导出
        $dataflag = $params['dataflag'];			//备份数据标志
        // if($showtype == Xphp::$_config['POINTSHOWTYPE']['TIMEGROUP']){
        //     //如果是按时间点方式显示
        //     return $this->getTimepointTimeGroup($nodeuuid, $instantflag);
        // }
        // $this->paramsCheck($showtype);
        $sql = "select distinct bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type,
    			bbt.task_uuid, bbt.user_uuid, bbt.user_name, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_config, vbt.vm_name, 
    			vbt.dir_path, vbt.hypervisor_type, bsr.node_uuid, bsr.storage_uuid,bsr.storage_type, vbt.version
                from bd_backup_timepoint bbt, 
                     vm_backup_timepoint vbt, 
                     bd_storage_resource bsr 
                where bbt.timepoint_uuid = vbt.timepoint_uuid and 
                      bbt.storage_uuid = bsr.storage_uuid and 
                      bbt.available_flag = ? 
	                  and bbt.import_flag = ? and bbt.module_type in (" . Xphp::$_config['MODULE_TYPE']['VM'] . ", " . Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] . ")  and bbt.data_local_flag = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['SET'], $flag['UNSET'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($_SESSION['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($dataflag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                // 备份数据管理页面可查看被管理用户的，恢复页面只能查看自己的
                if ($manageflag) {
                    // 查看权限
                    if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
                        $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
                        $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                        $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                        $sql .= " and bbt.user_uuid in " . $useruuidArr;
                    }
                } else {
                    $sql .= " and bbt.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                }
            }
        }

        //备份数据页面
        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], Xphp::$_config['MODULE_TYPE']['VM']));
        }
        if (empty($storageuuid)) {
            //所有存储（不包括网络存储-- 目前网络存储有华为CBR,S3,磁带）


            //如果是瞬时恢复或者细粒度恢复 所有存储还要排除云存储/磁带
            if ($instantflag || $grainflag) {
                $sql .= " and bsr.storage_type not in (" . Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'] . "," . Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'] . "," . Xphp::$_config['BD_STORAGE_TYPE']['TAPE'] . ") ";
            } else {
                $sql .= " and bsr.storage_type not in (" . Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'] . ")";
            }
        } else {
            //如果是选择了某个存储,显示这个存储下面的
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageuuid));
        }

        // 默认显示虚拟机的，如果有公有云到虚拟机的跨平台授权，则恢复页面要显示
        $cloudTypes = Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'];
        $hypervisorTypesTmp = $hypervisorTypes = array_diff(Xphp::$_config['VMHYPERVISORTYPE'], $cloudTypes, [0]);
        if (!$manageflag && !$instantflag && !$grainflag) {
            foreach ($cloudTypes as $cloudType) {
                $authTypes = $this->getVMRecoveryHypervisorArr($cloudType);
                foreach ($authTypes as $authType) {
                    // 判断是否有其他虚拟化类型的跨平台授权，若有则显示该类型的时间点
                    if (in_array($authType, $hypervisorTypesTmp)) {
                        $hypervisorTypes[] = $cloudType;
                        break;
                    }
                }
            }
        }
        $sql .= " and vbt.hypervisor_type in ('" . implode("','", $hypervisorTypes) . "')";

        $sql .= " group by vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, bbt.task_uuid, bbt.task_name 
            order by bbt.task_name, bbt.timepoint desc, vbt.vm_name ";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        //定义task vm timepoint
        $hypervisor = array();
        $task = array();
        $vm = array();
        $pid = null;
        $jobHandler = Xphp::instance('JobHandler');
        foreach ($data as $d) {
            $vmConfig = json_decode($d['vm_config'], true);
            //            //如果是细粒度恢复，暂时不支持hyper-v
//            if($grainflag && intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
//                continue;
//            }
            if ($exportflag) {
                //如果是备份数据导出任务创建,这里只显示VMware的,其他的虚拟化类型暂时过滤,以后支持了之后再添加
                if (!in_array(intval($d['hypervisor_type']), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])) {
                    continue;
                }
            }
            if ($instantflag) {
                //如果是瞬时恢复，hyper-v、smartx、xhere不支持
                if(intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']
                    || intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM']
                    || intval($d['hypervisor_type']) == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XHERE']
                ){
                    continue;
                }
            }
            $taskuuid = $d['task_uuid'];
            $taskCreateTime = $this->getTaskNewModifyTimeFromBdbackupTimepoint($d['task_uuid']);
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor)) {
                $hypervisorType = intval($d['hypervisor_type']);
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $d['hypervisor_type'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => !$manageflag,
                    "type" => -1,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, false),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                );
                $hypervisor[] = $hypervisorType;
            }

            $hypervisortype = $d['hypervisor_type'];
            $cloudTimepointFlag = in_array($hypervisortype, $cloudTypes); // 是否为公有云备份时间点
            $taskName = $jobHandler->getTimepointTaskname($d['task_uuid'], $d['task_name']);
            //检查并添加task
            if (!in_array($hypervisortype . $taskuuid, $task)) {
                $task[] = $hypervisortype . $taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                if ($d['module_type'] == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']) {
                    if (intval($d['task_type']) == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']) {
                        $name = $taskName . "(" . Xphp::$_lang['UI_COPY_DATA'] . ")";
                    } else if (intval($d['task_type']) == Xphp::$_config['TASKTYPE']['ARCHIVE'] || intval($d['task_type']) == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']) {
                        $name = $taskName . "(" . Xphp::$_lang['UI_ARCHIVE_DATA'] . ")";
                    }
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != Xphp::$_user['useruuid']) {
                    $name .= "(" . $d['user_name'] . ")";
                }
                // 区分虚拟机和公有云的任务图标
                $icon = $cloudTimepointFlag ? './img/vm/AWS/aws-job.png' : './img/platform/flag.png';
                $node[] = array(
                    "id" => $hypervisortype . $taskuuid,
                    "pId" => $d['hypervisor_type'],
                    "name" => $name,
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 0,
                    "icon" => $icon,
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . $taskCreateTime,
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                    "storageuuid" => $d['storage_uuid'],
                    "storagetype" => $d['storage_type']
                );
            }
            $vmuuid = $d['vm_uuid'];
            //检查并添加vm
            if (!in_array($hypervisortype . $vmuuid . $taskuuid, $vm)) {
                $vm[] = $hypervisortype . $vmuuid . $taskuuid;
                $vmname = $d['vm_name'];
                if (in_array($hypervisortype, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
                    $networkList = $vmConfig['network_list'];
                    $ipList = $networkList[0]['ip_list'];
                    // 是否有浮动ip，有则显示为浮动ip
                    $floatingFlag = false;
                    foreach ($networkList as $network) {
                        if ('floating' == $network['interface_attr_type']) {
                            $ipList = $network['ip_list'];
                            $floatingFlag = true;
                            break;
                        }
                    }
                    if (!empty($ipList)) {
                        $prefix = $floatingFlag ? 'Floating:' : '';
                        $vmname .= "(" . $prefix . $ipList[0]['ipaddr'] . ")";
                    }
                }
                // 区分虚拟机和实例图标
                $iconSkin = $cloudTimepointFlag ? 'vm_aws_vm' : 'vm';
                $icon = $cloudTimepointFlag ? './img/vm/AWS/aws-instance.png' : './img/vm/vm.png';
                //如果是华为CBR中的存储库、时间点 需要修改图标
                if ($hypervisortype == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CBR']) {
                    if ($d['version'] == 1) {
                        $iconSkin = 'vm_huawei_cbr_memorypool';
                        $icon = '';
                    } else if ($d['version'] == 3) {
                        $iconSkin = 'vm_huawei_cbr_timepoint';
                        $icon = '';
                    }
                }
                $node[] = array(
                    "id" => $hypervisortype . $vmuuid . $taskuuid,
                    "pId" => $hypervisortype . $taskuuid,
                    "name" => rawurldecode($vmname),
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 1,
                    "icon" => $icon,
                    "iconSkin" => $iconSkin,
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . rawurldecode($d['dir_path']),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "vmuuid" => $d['vm_uuid'],
                    "taskuuid" => $taskuuid,
                    "nodeuuid" => $d['node_uuid'],
                    "createtime" => $taskCreateTime,
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                    "clickshow" => true,
                    "storageuuid" => $d['storage_uuid'],
                    "storagetype" => $d['storage_type']
                );
            } else {
                continue;
            }

        }
        return json_encode($node);
    }
    /**
     * 得到华为CBR虚拟机配置信息
     * @param unknown $params
     */
    public function getCBRVMConfig($params)
    {
        $opName = "VM_CHECK_AND_BUILD_HUAWEI_CBR_TIMEPOINT";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $hypersior = Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'];
        $pfMsg = $params['cbrinfo'];
        $mbResult = $this->mbVMMsg($nodeuuid, $hypersior, $opName, json_encode($pfMsg), true);
        //写入假数据
        // $mbResult =  array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>''
        // );
        $timepoint = $mbResult['msg']['new_timepoint_uuid'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        //如果获取失败
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $operate, '', 'info', $mbResult['errorCode']);
        } else {
            return $this->muOpResult(true, $operate, '', '', '', $timepoint);
        }
    }
    public function getCBRVMConfigInfoV2($params)
    {
        $backup_id = $params['backup_id'];
        $hypervisor = $params['hypervisor'];
        $vcenteruuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $pointsDetail = $params['pointsDetail'];
        $vmuuid = $params['vm_uuid'];
        //在vm_backup_timepoint中通过时间点id查询vm_config信息
        $sql = "select vm_config from vm_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($backup_id));
        $configinfo = json_decode($data[0]['vm_config'], true);
        //得到虚拟机名称限制条件
        $vmConfig = include XPHP_PATH . 'conf/vm_config.php';
        $vmNameLimit = $vmConfig['VmNameCheck'][$hypervisor];
        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        $controlContent = $vmRecoveryHandler->getVMRecoveryControlContent($hypervisor, $vcenteruuid, $hostuuid);

        $diskList = $configinfo['disk_list'];
        $utils = Xphp::instance('Utils');
        foreach ($diskList as $dkey => $dvalue) {
            $controller_type = intval($dvalue['controller_type']);
            $diskList[$dkey]['virtual_size_unit'] = $utils->calSize($dvalue['virtual_size']);
            $diskList[$dkey]['controller_des'] = $vmConfig['VmMiddleControllerTypeDes'][$controller_type];
            $diskList[$dkey]['disk_key_base64'] = base64_encode($dvalue['disk_key']);

        }
        ;
        //直接得到信息
        $target_vm_list[] = array(
            'target_hypervisor_type' => intval($hypervisor),
            'source_hypervisor_type' => intval($configinfo['source_hypervisor_type']),
            'new_vm_name' => $pointsDetail[0]['vmname'],
            'vm_name' => $pointsDetail[0]['oldname'],
            'memory_array' => $vmRecoveryHandler->getSizeToUnit($configinfo['memory']),
            'data_encrypt' => false,
            'timepointuuid' => $pointsDetail[0]['timepointuuid'],
            'disk_list' => $diskList,
            'net_list' => $configinfo['network_list'],
            'vm_version_enable' => $vmRecoveryHandler->getVmVersionSelectEnable($hypervisor, $vcenteruuid),
            'vm_uuid' => $vmuuid,
            'os_type' => intval($configinfo['os_type'])
        );
        $info = array(
            'hypervisor' => intval($hypervisor),
            'old_hypervisor' => intval($pointsDetail[0]['hypervisor']),
            'config' => $target_vm_list,
            'control' => $controlContent['control'],
            'disk_bus' => $controlContent['disk_bus'],
            'network_bus' => $controlContent['network_bus'],
            'available_domain' => $controlContent['available_domain'],
            'des_dir' => $controlContent['des_dir'],
            'mirror_image' => $controlContent['mirror_image'],
            'vmNameLimit' => $vmNameLimit,
            'maintain_model' => $controlContent['maintain_model'],
            'cbrflag' => true
        );
        $info['flag'] = true;
        $info['instantFlag'] = false;  //瞬时恢复标记,瞬时恢复的时候不选择存储
        $info['vmotionFlag'] = false;                   //迁移标记,插件统一处理的,这里补齐这个字段
        //显示磁盘置备模式
        $info['control']['disk_setting_mode'] = true;
        return json_encode($info);

    }
    //--------------------------------------------华为CBR--end-----------------------------------------------
}
?>