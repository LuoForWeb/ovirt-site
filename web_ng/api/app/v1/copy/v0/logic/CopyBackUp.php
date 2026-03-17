<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 副本容灾  --- 新建/修改备份逻辑
 * @Date: 2023-08-22 10:30:59
 * @LastEditTime: 2026-01-23 09:51:28
 * @Version: 1.0
 * @copyright: Copyright 2023 vinchin.com
 */

namespace app\v1\copy\v0\logic;

use app\v1\common\logic\JobInfo;
use app\v1\resources\v0\logic\Storage;
use app\v1\opcode\PfOpcode;
use app\v1\common\logic\Backup;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\backupData\v0\logic\DataManage;

class CopyBackUp extends Backup
{
    private $namespace_arr = [];
    private $app_arr = [];
    private $pvc_arr = [];
    private $cluster_arr = [];
    private $cluster_instance = [];
    private $cluster_node = [];
    private $cluster_item_arr = [];
    private $instance_arr = [];
    /**
     * 创建备份任务 demo
     * @param array $params 数组
     * @return {}
     */
    public function createCopyJob($params)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        // 公共创建消息
        // 模块
        $task_uuid = $params['task_uuid'];
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $TASK = xphp_get_config('task', 'TASKTYPE');
        // 任务名
        $task_name = htmlspecialchars_decode($params['taskName']);
        // 策略组uuid
        $strategy_group_uuid = '';
        // 时间策略
        $time_strategy_list = $this->groupTimeList($params['strategyInfo']['timeStrategy'], $params['copyItemInfo']['copy_mode'], $strategy_group_uuid);
        // 保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['strategyInfo']['reserveStrategy']['reserveInfo']);
        // 传输策略
        $transport_strategy = $this->groupTransportStrategy($params['strategyInfo']['transferStrategy']);
        // 节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['strategyInfo']['nodeInfo']);
        //组合存储策略
        $storageStrategy = $this->groupStorageStrategy($params['strategyInfo']['storageStrategy']['storageInfo']);
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storageStrategy,
            $nodeInfo,
            $params['strategyInfo']['timeStrategy']['type']
        ); //组合消息
        // 子类型
        $node_uuid = $params['strategyInfo']['nodeInfo']['node_uuid'];
        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['strategyInfo']['speedStrategy']);
        // 真实存储
        $pfMsg['real_storage_info'] = $this->getRealStorageInfo($params['strategyInfo']['nodeInfo']['real_storage_info']);

        $storageInfo = $this->getRemoteStorageInfo($params['strategyInfo']['nodeInfo']['storage_uuid']);
        $pfMsg['node_uuid'] = $node_uuid;
        $pfMsg['target_repository_uuid'] = $storageInfo['target_repository_uuid'];
        $pfMsg['target_storage_type'] = $storageInfo['target_storage_type'];
        // 重试策略
        $pfMsg['retry_strategy'] = $this->groupRetryStrategy($params['retry_strategy']);
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['strategyInfo']['highInfo']['ignore_resource_limiting_flag']);

        // 操作
        $opName = 'BD_TASK_OP_BACKUP_COPY_CREATE';
        if (!empty($task_uuid)) {
            $this->editCheckStatus($task_uuid);
            $opName = 'BD_TASK_OP_BACKUP_COPY_MODIFY';
            $pfMsg['task_uuid'] = $task_uuid;
        }
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        // 副本对象数组
        $pfMsg['copy_list'] = ($params['module_type'] == $MODULE['OS']) ? $params['copyItemInfo']['copy_list'] : $this->getCopyList($params['copyItemInfo']['copy_list'], $params['module_type'], $params['data_type']);
        // 副本模式
        $pfMsg['copy_mode'] = $params['copyItemInfo']['copy_mode'];
        // 传输网络
        $pfMsg['transport_ip'] = $params['strategyInfo']['transferStrategy']['transferNet'];
        // 传输网络
        $pfMsg['transport_port'] = $params['strategyInfo']['transferStrategy']['transferPort'];
        // 数据链长度
        $pfMsg['chain_length'] = $params['copyItemInfo']['chain_length'];
        $pfMsg['inc_mode'] = $params['strategyInfo']['transferStrategy']['inc_mode'] ?? 1;
        $pfMsg['hash_inc_flag'] = 0;
        $pfMsg['wan_accelerate_flag'] = $params['strategyInfo']['transferStrategy']['wan_accelerate_flag'] ? $FLAG['SET'] : $FLAG['UNSET'];
        // 传输数据加密
        $pfMsg['transport_data_encrypt'] = $params['strategyInfo']['transferStrategy']['encryptData'] ? $FLAG['SET'] : $FLAG['UNSET'];
        $pfMsg['task_type'] = $params['archive_flag'] ? $TASK['ARCHIVE'] : $TASK['BACKUP_COPY'];
        $pfMsg['thread_num'] = $params['strategyInfo']['transferStrategy']['threadNum'];
        $pfMsg['sub_module_type'] = $sub_module_type;
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['strategyInfo']['safe_strategy']);
        // 副本gfs保留策略，只副本备份标记的gfs点，镜像副本配置
        $pfMsg['copy_week_flag'] = v1_parse_bool_to_flag($params['strategyInfo']['copy_gfs_strategy']['copy_week_flag']);
        $pfMsg['copy_month_flag'] = v1_parse_bool_to_flag($params['strategyInfo']['copy_gfs_strategy']['copy_month_flag']);
        $pfMsg['copy_year_flag'] = v1_parse_bool_to_flag($params['strategyInfo']['copy_gfs_strategy']['copy_year_flag']);
        // $pfMsg['cache_uuid'] =  $params['cache_uuid'];//选择云存储后  作为缓存的本地存储的nodeuuid
        // 副本最新链标志
        $pfMsg['copy_last_chain_flag'] = v1_parse_bool_to_flag($params['copy_last_chain_flag']);
        // gfs保留策略
        $pfMsg['gfs_strategy_item_list'] = $params['strategyInfo']['reserveStrategy']['gfs_strategy_item_list'];
        if (empty($node_uuid)) {
            //没有节点可用
            $pfMsg['node_uuid'] = $this->getLocalNodeUUID();
            // return $this->muOpResult(false, $operate, xphp_get_lang('WEB_NODE_NOT_FIND_NODE'), 'error');
        }
        $msg = json_encode($pfMsg);
        // 选择是否接受服务通信结果 然后返回到控制器
        $operateDes = $params['archive_flag'] ? str_replace(xphp_get_lang('UI_PLATFORM_BACKUP_COPY'), xphp_get_lang('UI_PLATFORM_ARCHIVE_PROTECT'), $operate) : $operate;
        //检查授权是否过期
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operateDes, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        // 需要和后台通信的 需要再次转发到 service 服务层去处理
        $mbResult = $this->service()->createCopyJob($node_uuid, $sub_module_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            return $this->muOpResult($result, $operateDes, $msg);
        } else {
            return $this->muOpResult($result, $operateDes, $msg, '', $mbResult['errorCode']);
        }
    }
    private function getCopyList($List, $module_type, $type)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        $copyList = [];
        foreach ($List as $l) {
            $dirPath = [];
            if ($type == $TYPE['COPY_TASK']) {
                $sql = "select dir_path FROM copy_list WHERE task_uuid = '{$l['source_task_uuid']}' and item_uuid = '{$l['item_uuid']}'";
                $data = $this->dbSelect($sql, []) ?? [];
                $dirPath = $data[0]['dir_path'];
            } else {
                switch ($module_type) {
                    case $MODULE['VM']:
                    case $MODULE['PUBLIC_CLOUD']:
                    case $MODULE['PRIVATE_CLOUD']:
                        if ($type == $TYPE['BACKUP_TASK']) {
                            $sql = "select dir_path FROM vm_machine_list WHERE task_uuid = ? and vm_uuid = '{$l['item_uuid']}'";
                        }
                        if ($type == $TYPE['COPY_DATA'] || $type == $TYPE['BACKUP_DATA']) {
                            $sql = "select vbt.dir_path FROM bd_backup_timepoint bbt, vm_backup_timepoint vbt WHERE bbt.task_uuid = ? and vbt.timepoint_uuid = bbt.timepoint_uuid and vbt.vm_uuid = '{$l['item_uuid']}'";
                        }
                        $data = $this->dbSelect($sql, [$l['source_task_uuid']]) ?? [];
                        $dirPath = $data[0]['dir_path'];
                        break;
                    case $MODULE['FS']:
                    case $MODULE['NAS']:
                    case $MODULE['OBS']:
                    case $MODULE['HADOOP']:
                        if ($type == $TYPE['BACKUP_TASK']) {
                            $sql = "select path_name FROM fs_path_list WHERE agent_uuid = '{$l['item_uuid']}' and task_uuid = ?";
                            $data = $this->dbSelect($sql, [$l['source_task_uuid']]);
                            foreach ($data as $each_path) {
                                if (!in_array($each_path['path_name'], $dirPath)) {
                                    array_push($dirPath, $each_path['path_name']);
                                }
                            }
                        }
                        if ($type == $TYPE['COPY_DATA'] || $type == $TYPE['BACKUP_DATA']) {
                            $sql = "select fbt.backup_path_list FROM bd_backup_timepoint bbt, fs_backup_timepoint fbt WHERE fbt.agent_uuid = '{$l['item_uuid']}' and bbt.task_uuid = ? and bbt.timepoint_uuid = fbt.fs_timepoint_uuid";
                            $data = $this->dbSelect($sql, [$l['source_task_uuid']]) ?? [];
                            $fsList = json_decode($data[0]['backup_path_list'], true);
                            foreach ($fsList as $each_path) {
                                if (!in_array($each_path['backup_path'], $dirPath)) {
                                    array_push($dirPath, $each_path['backup_path']);
                                }
                            }
                        }
                        break;
                    case $MODULE['DB']:
                        if ($type == $TYPE['BACKUP_TASK']) {
                            $sql = "select dir_path FROM db_list WHERE db_uuid = '{$l['item_uuid']}' and task_uuid = ?";
                        }
                        if ($type == $TYPE['COPY_DATA'] || $type == $TYPE['BACKUP_DATA']) {
                            $sql = "select dbt.dir_path FROM bd_backup_timepoint bbt, db_backup_timepoint dbt WHERE dbt.db_uuid = '{$l['item_uuid']}' and bbt.task_uuid = ? and dbt.timepoint_uuid = bbt.timepoint_uuid";
                        }
                        $data = $this->dbSelect($sql, [$l['source_task_uuid']]) ?? [];
                        $dirPath = $data[0]['dir_path'];
                        break;
                    case $MODULE['M365']:
                        if ($type == $TYPE['BACKUP_TASK']) {
                            $sql = "select backup_object_info FROM m365_object_list WHERE organization_uuid = '{$l['item_uuid']}' and task_uuid = ?";
                            $data = $this->dbSelect($sql, [$l['source_task_uuid']]);
                            foreach ($data as $each_path) {
                                $backupObjectInfo = json_decode($each_path['backup_object_info'], true);
                                $objectList = $backupObjectInfo['backup_object_name'] . "(" . $backupObjectInfo['backup_object_mail'] . ')';
                                array_push($dirPath, $objectList);
                            }
                        }
                        if ($type == $TYPE['COPY_DATA'] || $type == $TYPE['BACKUP_DATA']) {
                            $sql = "select mbt.user_config FROM m365_backup_timepoint mbt, bd_backup_timepoint bbt WHERE mbt.organization_uuid = '{$l['item_uuid']}' and bbt.task_uuid = ? and bbt.timepoint_uuid = mbt.m365_timepoint_uuid";
                            $data = $this->dbSelect($sql, [$l['source_task_uuid']]) ?? [];
                            $m365Object = json_decode($data[0]['user_config'], true);
                            $infoList = $m365Object['backup_m365_object_info_list'];
                            foreach ($infoList as $m365) {
                                array_push($dirPath, explode('@', $m365['backup_object_mail'])[0] . "(" . $m365['backup_object_mail'] . ')');
                            }
                        }
                        break;
                }
            }
            $copyList[] = [
                "item_uuid" => $l['item_uuid'],
                "parent_uuid" => $l['parent_uuid'],
                "source_storage_uuid" => $l['source_storage_uuid'],
                "sub_type" => $l['sub_type'],
                "source_task_uuid" => $l['source_task_uuid'],
                "detail" => $l['detail'],
                "dir_path" => is_array($dirPath) ? json_encode($dirPath) : $dirPath,
                "item_name" => $l['item_name'],
                "timepoint_uuid_list" => $l['timepoint_uuid_list'],
                "archive_timepoint_uuid" => $l['archive_timepoint_uuid']
            ];
        }
        return $copyList;
    }

    private function editCheckStatus($task_uuid)
    {
        $sql = "select task_status FROM bd_task WHERE task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid)) ?? [];
        if ($data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
            return true;
        } else {
            exit($this->muOpResult(false, xphp_get_lang('WEB_PT_OP_COPY_MODIFY'), xphp_get_lang('UI_COPY_JOB_EDIT_NOT_STOPPED'), 'warning'));
        }
    }

    public function createCopyBackJob($params)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        // 公共创建消息
        // 模块
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $TASK = xphp_get_config('task', 'TASKTYPE');
        // 任务名
        $task_name = htmlspecialchars_decode($params['taskName']);
        // 时间策略
        $time_strategy_list = array();
        // 保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['strategyInfo']['reserveStrategy']);
        // 传输策略
        $transport_strategy = $this->groupTransportStrategy($params['strategyInfo']['transferStrategy']);
        // 节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['strategyInfo']['nodeInfo']);
        //组合存储策略
        $storageStrategy = $this->groupStorageStrategy($params['strategyInfo']['storageStrategy']['storageInfo']);
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storageStrategy,
            $nodeInfo,
            $params['strategyInfo']['timeStrategy']['type']
        ); //组合消息
        // 子类型
        $node_uuid = $params['strategyInfo']['nodeInfo']['node_uuid'];
        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['strategyInfo']['speedStrategy']);
        // 真实存储
        $pfMsg['real_storage_info'] = $this->getRealStorageInfo($params['strategyInfo']['nodeInfo']['real_storage_info']);

        $storageInfo = $this->getRemoteStorageInfo($params['strategyInfo']['nodeInfo']['storage_uuid']);
        $pfMsg['node_uuid'] = $node_uuid;
        $pfMsg['target_repository_uuid'] = $storageInfo['target_repository_uuid'];
        $pfMsg['target_storage_type'] = $storageInfo['target_storage_type'];
        // 重试策略
        $pfMsg['retry_strategy'] = $this->groupRetryStrategy($params['retry_strategy']);
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['strategyInfo']['highInfo']['ignore_resource_limiting_flag']);

        // 操作
        $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_CREATE';
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        // 副本对象数组
        $pfMsg['copy_list'] = $params['module_type'] == $MODULE['OS'] || ($params['remoteSource'] && $params['data_type'] == 4) ? $params['copyItemInfo']['copy_list'] : $this->getCopyList($params['copyItemInfo']['copy_list'], $params['module_type'], $params['data_type']);
        // 副本模式
        $pfMsg['copy_mode'] = $params['copyItemInfo']['copy_mode'];
        // 传输网络
        $pfMsg['transport_ip'] = $params['strategyInfo']['transferStrategy']['transferNet'];
        // 传输网络
        $pfMsg['transport_port'] = $params['strategyInfo']['transferStrategy']['transferPort'];
        // 数据链长度
        $pfMsg['chain_length'] = $params['copyItemInfo']['chain_length'];
        $pfMsg['inc_mode'] = $params['strategyInfo']['transferStrategy']['inc_mode'] ?? 1;
        $pfMsg['hash_inc_flag'] = 0;
        $pfMsg['wan_accelerate_flag'] = $params['strategyInfo']['transferStrategy']['wan_accelerate_flag'] ? $FLAG['SET'] : $FLAG['UNSET'];
        // 传输数据加密
        $pfMsg['transport_data_encrypt'] = $params['strategyInfo']['transferStrategy']['encryptData'] ? $FLAG['SET'] : $FLAG['UNSET'];
        $pfMsg['task_type'] = $params['archive_flag'] ? $TASK['ARCHIVE_FETCH'] : $TASK['BACKUP_COPY_FETCH'];
        $pfMsg['thread_num'] = $params['strategyInfo']['transferStrategy']['threadNum'];
        $pfMsg['sub_module_type'] = $sub_module_type;
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['strategyInfo']['safe_strategy']);
        // 副本gfs保留策略，只副本备份标记的gfs点，镜像副本配置
        $pfMsg['copy_week_flag'] = $FLAG['UNSET'];
        $pfMsg['copy_month_flag'] = $FLAG['UNSET'];
        $pfMsg['copy_year_flag'] = $FLAG['UNSET'];
        // 副本最新链标志
        $pfMsg['copy_last_chain_flag'] = $FLAG['UNSET'];
        // gfs保留策略
        $pfMsg['gfs_strategy_item_list'] = [];
        if (empty($node_uuid)) {
            //没有节点可用
            $pfMsg['node_uuid'] = $this->getLocalNodeUUID();
            // return $this->muOpResult(false, $operate, xphp_get_lang('WEB_NODE_NOT_FIND_NODE'), 'error');
        }
        $msg = json_encode($pfMsg);
        $operateDes = $params['archive_flag'] ? str_replace(xphp_get_lang('UI_PLATFORM_BACKUP_COPY'), xphp_get_lang('UI_PLATFORM_ARCHIVE_PROTECT'), $operate) : $operate;
        //检查授权是否过期
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operateDes, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        $mbResult = $this->mbCopyMsg($node_uuid, $sub_module_type, $opName, $msg) ?? [];
        if ($mbResult['result']) {
            $this->startCopyBackJob($task_name);
            return $this->muOpResult($mbResult['result'], $operateDes, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operateDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * @description: 获取存储信息
     * @param {*} $storageuuid
     */
    private function getRemoteStorageInfo($storageuuid)
    {
        $sql = "SELECT storage_type FROM bd_storage_resource WHERE storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $info = array(
            'target_storage_type' => intval($data[0]['storage_type']),
            'target_repository_uuid' => $storageuuid,
        );
        return $info;
    }

    /**
     * @description: 获取真实存储信息
     * @param {*} $relStorageInfo
     */
    private function getRealStorageInfo($relStorageInfo)
    {
        $storageInfo = array(
            'real_storage_uuid' => $relStorageInfo['uuid'],
            'real_storage_name' => $relStorageInfo['name'],
            'real_storage_type' => $relStorageInfo['type'],
            'real_storage_total_size' => $relStorageInfo['total'],
            'real_storage_free_size' => $relStorageInfo['free']
        );
        return $storageInfo;
    }
    /**
     * 备份任务查询sql，根据不同模块，联查对应二级表
     * @param mixed $module_type
     * @return string
     */
    private function getBackupTaskSql($params)
    {
        // 模块类型
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $FS_SUB = xphp_get_config('module', 'SUBMODULE_TYPE');
        $FLAG = xphp_get_config('app', 'FLAG');
        // 任务类型
        $TASK = xphp_get_config('task', 'TASKTYPE');
        $module_type = intval($params['module_type']);
        $sub_module_type = intval($params['sub_module_type']);
        $search = $params['search'];
        $storage_uuid = $params['storage_uuid'];
        $sql = "SELECT bt.task_uuid, bt.task_name, bt.create_time,bt.module_type, bt.sub_module_type, bsr.storage_uuid, bsr.storage_nickname, bsr.node_uuid, bsr.storage_type";
        switch ($module_type) {
            //虚拟机
            case $MODULE['VM']:
                //备份任务
                $sql .= ",vml.vcenter_uuid as parent_uuid, vml.vm_uuid, vml.vcenter_uuid, vml.vm_name,vml.vm_uuid AS item_uuid,
                vt.hypervisor_type
                FROM vm_machine_list vml, vm_task vt, bd_task bt
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WHERE bt.task_uuid = vml.task_uuid
                AND vt.task_uuid = bt.task_uuid
                AND bt.module_type = {$module_type}
                AND bt.sub_module_type = {$sub_module_type}
                AND bt.task_type in (" . $TASK['BACKUP'] . "," . $TASK['VM_HUAWEI_CBR_SYNC'] . ")";
                if (!empty($search)) {
                    $sql .= " AND (bt.task_name LIKE '%{$search}%' OR vml.vm_name LIKE '%{$search}%')";
                }
                break;
            //文件
            case $MODULE['FS']:
                if ($sub_module_type == $FS_SUB['FS']) {
                    //备份任务
                    $sql .= ",btal.agent_uuid,btal.agent_uuid AS item_uuid, ba.ip AS fs_ip, ba.agent_name,ba.hostname,ba.os_type
                    FROM bd_task_agent_list btal,bd_agent ba,bd_task bt
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                    WHERE bt.task_uuid = btal.task_uuid AND btal.agent_uuid = ba.agent_uuid AND bt.sub_module_type = {$sub_module_type} AND bt.task_type = {$TASK['BACKUP']}";
                    if (!empty($search)) {
                        $sql .= " AND (bt.task_name LIKE '%{$search}%' OR ba.agent_name LIKE '%{$search}%' OR ba.hostname LIKE '%{$search}%')";
                    }
                }
                if ($sub_module_type == $FS_SUB['OBS']) {
                    $sql .= ", btal.agent_uuid as item_uuid, obsr.endpoint_override, obsr.obs_uuid, obsr.obs_nickname as item_name, obsr.obs_nickname, obsr.access_key_id, obsr.vendor
                    FROM fs_task ft,bd_task bt
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                    LEFT JOIN fs_path_list fpl ON bt.task_uuid = fpl.task_uuid
                    LEFT JOIN bd_task_agent_list btal ON fpl.task_uuid = btal.task_uuid
                    LEFT JOIN obs_resource obsr ON btal.agent_uuid = obsr.obs_uuid
                    WHERE bt.task_uuid = ft.task_uuid AND bt.sub_module_type = {$sub_module_type} AND bt.task_type = {$TASK['BACKUP']}";
                    if (!empty($search)) {
                        $sql .= " AND (bt.task_name LIKE '%{$search}%' OR obsr.obs_nickname LIKE '%{$search}%')";
                    }
                }
                if ($sub_module_type == $FS_SUB['HADOOP']) {
                    $sql .= ",btal.agent_uuid as item_uuid, hc.hadoop_cluster_name as item_name, hn.namenode_ip as fs_ip
                    FROM bd_task_agent_list btal, hadoop_cluster hc,hadoop_namenode hn, bd_task bt
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                    WHERE btal.agent_uuid = hc.hadoop_cluster_uuid AND btal.task_uuid = bt.task_uuid AND hn.hadoop_cluster_uuid = btal.agent_uuid AND bt.sub_module_type = {$sub_module_type} AND bt.task_type = {$TASK['BACKUP']}";
                    if (!empty($search)) {
                        $sql .= " AND (bt.task_name LIKE '%{$search}%' OR hc.hadoop_cluster_name LIKE '%{$search}%' OR hn.namenode_ip LIKE '%{$search}%')";
                    }
                }
                break;
            //数据库
            case $MODULE['DB']:
                //备份任务
                $sql .= ", btal.agent_uuid, btal.cluster_uuid as db_cluster_uuid,btal.agent_uuid AS db_agent_uuid, baa.cluster_name as db_cluster_name,
                dl.db_uuid, dl.db_name, dl.instance_name, dt.db_type, dl.dir_path as db_path,dt.depend_task_uuid,
                ba.ip AS db_ip, ba.agent_name, ba.hostname
                FROM bd_task_agent_list btal
                LEFT JOIN bd_agent_app baa ON baa.agent_uuid = btal.agent_uuid
                LEFT JOIN bd_agent ba ON btal.agent_uuid = ba.agent_uuid
                LEFT JOIN db_list dl ON btal.agent_uuid = dl.agent_uuid AND baa.app_name = dl.instance_name
                LEFT JOIN db_task dt ON btal.task_uuid = dt.task_uuid AND baa.app_type = dt.db_type
                LEFT JOIN bd_task bt ON btal.task_uuid = bt.task_uuid
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WHERE dt.task_uuid = dl.task_uuid
                AND bt.task_type = {$TASK['DB_BACKUP']}
                AND bt.sub_module_type = {$sub_module_type}
                AND dt.db_type != 14"; // #27974 屏蔽tidb副本源
                if (!empty($search)) {
                    $sql .= " AND (bt.task_name LIKE '%{$search}%' OR ba.agent_name LIKE '%{$search}%' OR ba.hostname LIKE '%{$search}%' OR dl.db_name LIKE '%{$search}%' OR dl.instance_name LIKE '%{$search}%')";
                }
                break;
            //操作系统
            case $MODULE['OS']:
                //备份任务
                $sql .= ",btal.agent_uuid as os_uuid,btal.agent_uuid,btal.agent_uuid AS item_uuid,
                ba.ip, ba.ip as os_ip, ba.agent_name as os_name, ba.hostname, ba.os_type
                FROM bd_task_agent_list btal,bd_agent ba,bd_task bt
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WHERE bt.task_uuid = btal.task_uuid
                AND btal.agent_uuid = ba.agent_uuid
                AND bt.sub_module_type = {$sub_module_type}
                AND bt.task_type = {$TASK['OS_BACKUP']}";
                if (!empty($search)) {
                    $sql .= " AND (bt.task_name LIKE '%{$search}%' OR ba.agent_name LIKE '%{$search}%' OR ba.hostname LIKE '%{$search}%')";
                }
                break;
            // nas
            case $MODULE['NAS']:
                $sql .= ",nt.nas_uuid,nt.nas_uuid AS item_uuid,
                nsr.ip AS agent_ip, nsr.share_path, nsr.nas_nickname,nsr.nas_type
                FROM nas_task nt,nas_storage_resource nsr,bd_task bt
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WHERE bt.task_uuid = nt.task_uuid
                AND nt.nas_uuid = nsr.nas_uuid
                AND bt.sub_module_type = {$sub_module_type}
                AND bt.task_type = {$TASK['BACKUP']}";
                if (!empty($search)) {
                    $sql .= " AND (bt.task_name LIKE '%{$search}%' OR nsr.nas_nickname LIKE '%{$search}%' OR nsr.share_path LIKE '%{$search}%')";
                }
                break;
            case $MODULE['M365']:
                //备份任务
                $sql .= ", mo.organization_uuid, mo.nickname as m365_name, mo.organization_name, mo.region,online_flag,
                mt.m365_type, mo.organization_uuid as item_uuid
                FROM m365_organization mo, m365_task mt, bd_task bt
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WHERE bt.task_uuid = mt.task_uuid
                AND mt.organization_uuid = mo.organization_uuid
                AND bt.task_type = {$TASK['BACKUP']}";
                if (!empty($search)) {
                    $sql .= " AND (bt.task_name LIKE '%{$search}%' OR mo.nickname LIKE '%{$search}%' OR mo.organization_name LIKE '%{$search}%')";
                }
                break;
            case $MODULE['KUBERNETES']:
                $sql .= ", kt.cluster_uuid, kt.cluster_uuid AS item_uuid, kc.cluster_name, kt.by_type
                FROM kube_task kt, kube_cluster kc, bd_task bt
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WhERE bt.task_uuid = kt.task_uuid
                AND kt.cluster_uuid = kc.cluster_uuid
                AND bt.task_type = {$TASK['KUBE_BACKUP']}";
                if (!empty($search)) {
                    $sql .= " AND (bt.task_name LIKE '%{$search}%' OR kc.cluster_name LIKE '%{$search}%')";
                }
                break;
        }
         $sql .= " AND bt.module_type = {$module_type} AND bt.delete_flag = {$FLAG['UNSET']}";
        if ($module_type == $MODULE['DB']) {
            $sql .= " AND (dt.depend_task_uuid IS NULL OR dt.depend_task_uuid = '')";
        }
        //按存储筛选
        if (!empty($storage_uuid)) {
            $sql .= " AND bt.storage_uuid = '{$storage_uuid}'";
        }
        if (v1_auth_need_check_look()) {
            // 权限判断
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['data']);
            $sql .= " AND bt.user_uuid IN ({$user_uuid_arr})";
        }
        if ($params['archive_flag']) {
            $sql .= " AND bsr.storage_type != 9";
        }
        if ($params['archive_back_flag']) {
            $sql .= " AND bsr.storage_type = 9";
        }
        return $sql;
    }
    /**
     * 获取备份任务的子任务列表
     * @param mixed $params
     * @param mixed $task_uuid_arr
     * @return {}
     */
    private function getBackupSubList($params, $task_uuid_arr)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = intval($params['module_type']);
        $data_type = intval($params['data_type']);
        //得到所有任务uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $sql = $this->getBackupTaskSql($params);
        if ($module_type == $MODULE['DB']) {
            $sql .= " AND (dt.depend_task_uuid IS NOT NULL OR dt.depend_task_uuid != '') GROUP BY bt.task_uuid";
        }
        $data = $this->dbSelect($sql);
        $node = [];
        // 调用公共方法获取存储在线状态
        foreach ($data as $d) {
            // 任务是否已删除
            if (in_array($d['depend_task_uuid'], $task_uuid_arr)) {
                $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
                $node[] = $this->getTaskNode(
                    $d,
                    $data_type,
                    false,
                    $taskAvailable,
                    $d['depend_task_uuid'] . $d['depend_task_uuid']
                );
            }
        }
        return $node;
    }
    // 获取所有被副本的虚拟化对象
    private function getItemInCopyTask()
    {
        $sql = "SELECT cl.item_uuid FROM copy_list cl, bd_task bt WHERE cl.task_uuid = bt.task_uuid AND bt.module_type = 2";
        $data = $this->dbSelect($sql) ?? [];
        return array_column((array)$data, 'item_uuid');
    }
    /**
     * 组装以备份任务为源的副本源树结构
     * @param mixed $params
     * @return array
     */
    private function getBackupSourceTask($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = intval($params['module_type']);
        $source_task_uuid = json_decode($params['source_task_uuid'], true);
        $nodes = [];
        $task_uuid_arr = [];
        $sql = $this->getBackupTaskSql($params);
            // 查询任务列表
            $sqlTask = $sql . " GROUP BY bt.task_uuid";
            // 将副本的任务排列在最前面
            if (!empty($source_task_uuid)) {
                $sqlTask .= " ORDER BY CASE WHEN bt.task_uuid IN ('" . implode("','", $source_task_uuid) . "') THEN 0 ELSE 1 END,bt.task_uuid ";
            }
            $dataTask = $this->dbSelect($sqlTask);

            // 获取当前所有任务节点相关的对象节点
            $task_uuid_arr = array_column((array)$dataTask, 'task_uuid');
            // 组装任务节点
            $task_node_list = $this->getTaskNodeList(
                $module_type,
                $dataTask,
                $params['data_type'],
                $task_uuid_arr
            );
            $nodes = array_merge($nodes, $task_node_list);
            if ($module_type == $MODULE['DB']) {
                $sub_task_node = $this->getBackupSubList($params, $task_uuid_arr);
                $nodes = array_merge($nodes, $sub_task_node);
            }
        return $nodes;
    }
    /**
     * 获取副本源树--对象节点
     * @param mixed $params
     */
    public function getCopySrcItem($params)
    { 
        $this->cluster_instance = [];
        $this->cluster_node = [];
        $this->cluster_item_arr = [];
        $this->instance_arr = [];
        // 副本源类型 备份数据|任务  副本数据|任务
        $SOURCE_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        switch ($params['data_type']) {
            case $SOURCE_TYPE['BACKUP_TASK']:
                return $this->getBackupSourceItem($params);
            case $SOURCE_TYPE['COPY_DATA']:
            case $SOURCE_TYPE['BACKUP_DATA']:
                return $this->getBackupDataItem($params);
            case $SOURCE_TYPE['COPY_TASK']:
                return $this->getCopySourceItem($params);
        }

    }
    /**
     * 获取副本源树--备份数据对象节点
     * @param mixed $params
     */
    private function getCopySourceItem($params)
    {
        $nodes = [];
        $data_type = intval($params['data_type']); //副本源类型：备份任务|数据 副本任务|数据
        $copyItemArr = $this->getItemInCopyTask();
        $source_item_uuid = json_decode($params['source_item_uuid'], true);
        //副本任务
        $sql = $this->getCopyTaskSql($params);
        $sqlItem = $sql . " AND bt.task_uuid = '{$params['task_uuid']}' GROUP BY bt.task_uuid,cl.item_uuid ";
        // 将副本的对象排列在最前面
        if (!empty($source_item_uuid)) {
            $source_item_str = implode("','", $source_item_uuid);
            $sqlItem .= " ORDER BY CASE WHEN cl.item_uuid IN ('{$source_item_str}') THEN 0 ELSE 1 END,cl.item_uuid";
        }
        // 组装对象节点
        $nodes = $this->getItemInTask($sqlItem, $data_type, $copyItemArr, false);
        return $nodes;
    }
    /**
     * 获取副本源树--备份数据对象节点
     * @param mixed $params
     */
    private function getBackupDataItem($params)
    {
        $nodes = [];
        $source_item_uuid = json_decode($params['source_item_uuid'], true);
        $copyItemArr = $this->getItemInCopyTask();
        // 查询语句
        $sql = $this->getBackupDataSql($params, $source_item_uuid);
        $sqlItem = $sql['sqlItem'];
        $sqlItem .= " AND bbt.task_uuid = '{$params['task_uuid']}'";
        // 组装对象节点
        $nodes = $this->getItemInTask($sqlItem, $params['data_type'], $copyItemArr, true);
        return $nodes;
    }
    private function getBackupSourceItem($params)
    { 
        $module_type = intval($params['module_type']);
        $source_item_uuid = json_decode($params['source_item_uuid'], true);
        $nodes = [];
        $sql = $this->getBackupTaskSql($params);
        $copyItemArr = $this->getItemInCopyTask();
        // 查询更多对象取传入的task_uuid查询对象
        $sql .= " AND bt.task_uuid = '{$params['task_uuid']}'" . $this->taskGroupBySql($module_type, $source_item_uuid);
        // 组装对象节点
        $nodes = $this->getItemInTask(
            $sql,
            1,
            $copyItemArr,
            false,
        );
        return $nodes;
    }
    /**
     * 获取任务列表下的所有对象列表
     * @param mixed $sql
     * @param mixed $data_type
     * @param mixed $copyItemArr
     * @param mixed $no_check_flag
     * @return {}
     */
    private function getItemInTask($sql, $data_type, $copyItemArr, $no_check_flag)
    {
        // 模块类型
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        // 副本源类型 备份数据|任务  副本数据|任
        $dataItem = [];
        $dataItem = $this->dbSelect($sql);
        $nodes = [];
        $detail = ["cluster_flag" => false];
        // 组装对象节点
        if (!empty($dataItem)) {
            foreach ($dataItem as $d) {
                $unifiedField = $this->getUnifiedFields($d, $data_type);
                $task_uuid = $d['task_uuid'];
                // 虚拟机是否被副本
                $inCopyFlag = in_array($unifiedField['item_uuid'], $copyItemArr);
                if ($d['module_type'] == $MODULE['DB']) {
                    $dbNodes = $this->getDBNode($d, $data_type, $unifiedField);
                    if (!empty($dbNodes)) {
                        $nodes = array_merge($nodes, $dbNodes);
                    }
                } else {
                    // k8s显示资源节点，并将资源节点信息存入detail中
                    if ($d['module_type'] == $MODULE['KUBERNETES']) {
                        $k8s_mode = $this->getK8sShowNode($d, $data_type, $unifiedField);
                        if (!empty($k8s_mode['node'])) {
                            $nodes = array_merge($nodes, $k8s_mode['node']);
                        }
                        $detail = $k8s_mode['detail'];
                    }
                    // 添加主机
                    $nodes[] = $this->getItemNode(
                        $d,
                        $task_uuid . $task_uuid,
                        $data_type,
                        $inCopyFlag,
                        $detail,
                        $unifiedField,
                        $no_check_flag
                    );
                }
            }
        }
        return $nodes;
    }
    /**
     * 组装数据库节点信息
     * @param mixed $d
     * @param mixed $data_type
     * @param mixed $unifiedField
     * @return {}
     */
    private function getDBNode($d, $data_type, $unifiedField, $no_check_flag = true)
    {
        // 副本源类型 备份数据|任务  副本数据|任务
        $SOURCE_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        // 模块类型
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $task_uuid = $d['task_uuid'];
        $task_node_id = $task_uuid . $task_uuid;
        $instanceDb = [xphp_get_config('db', 'DB_TYPE')['SQLSERVER'], xphp_get_config('db', 'DB_TYPE')['SAPHANA']];
        $node = [];
        $no_check = in_array($data_type, [$SOURCE_TYPE['COPY_DATA'], $SOURCE_TYPE['BACKUP_DATA']]);
        $detail = ["cluster_flag" => false];
        // 集群数据源树节点
        if (!empty($d['db_cluster_uuid'])) {
            $cluster_id = $d['task_uuid'] . '_' . $d['db_cluster_uuid'];
            $cluster_item_id = $unifiedField['item_uuid'] . '_' . $d['task_uuid'];

            // 以实例为备份对象，节点显示在实例同一级
            if (in_array($unifiedField['sub_type'], $instanceDb)) {
                if (!isset($this->cluster_instance[$cluster_id])) {
                    $this->cluster_instance[$cluster_id] = true;
                    $node[] = [
                        "id" => $cluster_id,
                        "pId" => $d['task_uuid'] . $d['task_uuid'],
                        "name" => $d['db_cluster_name'],
                        "title" => $d['db_cluster_name'],
                        "nocheck" =>  !$no_check_flag ? false : $no_check,
                        "type" => 0,
                        "icon" => './img/platform/storage.png',
                        'event_type' => 'show_node',
                        "task_uuid" => $task_uuid,
                    ];
                }

                $agentList = $this->parseAgentList($d['db_path'], $unifiedField['sub_type']);
                $detail_node_arr = [];

                // 集群所有节点展示的树节点
                foreach ($agentList as $agentInfoStr) {
                    $instanceInfo = explode('/', $agentInfoStr);
                    $agentIp = trim($instanceInfo[0]);
                    $agentInstanceName = trim($instanceInfo[1]);
                    $agentName = $agentInstanceName . '(' . $agentIp . ')';
                    $cluster_node_id = $d['task_uuid'] . '_' . $d['db_cluster_uuid'] . '_' . $agentName;

                    if (!isset($this->cluster_node[$cluster_node_id])) {
                        $this->cluster_node[$cluster_node_id] = true;
                        $detail_node_arr[] = $agentName;
                        array_unshift($node, [
                            "id" => $cluster_node_id,
                            "pId" => $cluster_id,
                            "name" => $agentName,
                            "title" => $agentName,
                            "nocheck" => true,
                            "type" => 0,
                            "icon" => './img/platform/storage.png',
                            'event_type' => 'show_node',
                            "task_uuid" => $task_uuid,
                        ]);
                    }
                }

                $detail = [
                    "cluster_flag" => true,
                    "cluster_uuid" => $d['db_cluster_uuid'],
                    "db_name" => $d['db_name'],
                    "agent_uuid" => $d['db_agent_uuid'],
                    "cluster_node" => $detail_node_arr,
                    "cluster_name" => $d['db_cluster_name'],
                ];
            }
            // 增加集群备份对象显示
            if (!isset($this->cluster_item_arr[$cluster_item_id])) {
                $this->cluster_item_arr[$cluster_item_id] = true;
                // 以数据库为备份对象，节点显示在数据库下一级
                if (!in_array($unifiedField['sub_type'], $instanceDb)) {
                    $agentList = $this->parseAgentList($d['db_path'], $unifiedField['sub_type']);
                    $detail_node_arr = [];
                    // 集群所有节点展示的树节点
                    foreach ($agentList as $agentInfoStr) {
                        $instanceInfo = explode('/', $agentInfoStr);
                        $agentIp = trim($instanceInfo[0]);
                        $agentInstanceName = trim($instanceInfo[1]);
                        $agentName = $agentInstanceName . '(' . $agentIp . ')';

                        $cluster_node_id = $d['task_uuid'] . '_' . $d['db_cluster_uuid'] . '_' . $agentName;
                        if (!isset($this->cluster_node[$cluster_node_id]) && $data_type == 1) {
                            $this->cluster_node[$cluster_node_id] = true;
                            $detail_node_arr[] = $agentName;
                            $node[] = [
                                "id" => $cluster_node_id,
                                "pId" => $cluster_item_id,
                                "name" => $agentName,
                                "title" => $agentName,
                                "nocheck" => true,
                                "type" => 0,
                                "icon" => './img/platform/storage.png',
                                'event_type' => 'show_node',
                                "task_uuid" => $task_uuid,
                            ];
                        }
                    }

                    $detail = [
                        "cluster_flag" => true,
                        "cluster_uuid" => $d['db_cluster_uuid'],
                        "db_name" => $d['db_name'],
                        "agent_uuid" => $d['db_agent_uuid'],
                        "cluster_node" => $detail_node_arr,
                        "cluster_name" => $d['db_cluster_name'],
                    ];
                }
                $pid = in_array($unifiedField['sub_type'], $instanceDb) ? $cluster_id : $task_node_id;
                $node[] = $this->getItemNode(
                    $d,
                    $pid,
                    $data_type,
                    false,
                    $detail,
                    $unifiedField,
                    !$no_check_flag ? false : $no_check
                );
            }
        } else {
            $instanceName = $d['instance_name'];
            $instanceShowName = $d['instance_name'];
            $pId = $task_node_id;
            $id = $unifiedField['item_uuid'] . "_" .  $task_uuid;

            // 集群副本任务 从detail读取集群信息
            if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
                $copy_detail = json_decode($d['detail'], true);
                if (!empty($copy_detail) && is_array($copy_detail)) {
                    $detail = [
                        "cluster_flag" => $copy_detail['cluster_flag'],
                        "cluster_uuid" => $copy_detail['cluster_uuid'],
                        "db_name" => $copy_detail['db_name'],
                        "agent_uuid" => $copy_detail['agent_uuid'],
                        "cluster_node" => $copy_detail['cluster_node'],
                        "cluster_name" => $copy_detail['cluster_name'],
                    ];

                    if (!empty($copy_detail['cluster_uuid']) && in_array($d['sub_type'], $instanceDb)) {
                        // 显示集群实例
                        $id = $d['task_uuid'] . '_' . $copy_detail['cluster_uuid'];
                        if (!isset($this->cluster_instance[$id])) {
                            $this->cluster_instance[$id] = true;
                            $node[] = [
                                "id" => $id,
                                "pId" => $pId,
                                "name" => $copy_detail['cluster_name'],
                                "title" => $copy_detail['cluster_name'],
                                "nocheck" => true,
                                "type" => 0,
                                "icon" => './img/platform/storage.png',
                                'event_type' => 'show_node',
                                "icon" => './img/platform/storage.png',
                                "task_uuid" => $task_uuid,
                            ];
                        }
                        $pId = $id;
                        // 显示集群节点
                        if (!empty($copy_detail['cluster_node'])) {
                            foreach ($copy_detail['cluster_node'] as $key) {
                                $cluster_node_id = $d['task_uuid'] . $key;
                                if (!isset($this->cluster_node[$cluster_node_id])) {
                                    $this->cluster_node[$cluster_node_id] = true;
                                    $node[] = [
                                        "id" => $cluster_node_id,
                                        "pId" => $pId,
                                        "name" => $key,
                                        "title" => $key,
                                        "nocheck" => true,
                                        "type" => 0,
                                        "icon" => './img/platform/storage.png',
                                        'event_type' => 'show_node',
                                        "task_uuid" => $task_uuid,
                                    ];
                                }
                            }
                        }
                        $id = $unifiedField['item_uuid'] . "_" .  $task_uuid;
                    }
                }
            }
            //SQLSERVER多一层数据库实例
            if (in_array($unifiedField['sub_type'], $instanceDb) && $d['module_type'] == $MODULE['DB'] && !$detail['cluster_flag']) {
                $id = $d['db_agent_uuid'] . $d['instance_name'] . $task_uuid;
                if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
                    $parts = explode(':', $d['item_name'], 2); // 只分割两次
                    if (!empty($parts)) {
                        $instanceName = $parts[1];
                        $instanceShowName = $parts[1];
                        $id =  $d['db_agent_uuid'] . $parts[1] . $task_uuid;
                    }
                }
                if ($data_type != $SOURCE_TYPE['COPY_TASK']) {
                    $instanceShowName = $instanceName . '(' . $d['db_ip'] . ')';
                }
                //检查并添加INSTANCE
                if (!isset($this->instance_arr[$id])) {
                    $this->instance_arr[$id] = true;
                    array_unshift($node, [
                        "id" => $id,
                        "pId" => $task_node_id,
                        "name" => $instanceShowName,
                        "open" => false,
                        "nocheck" => ($data_type == 2 || $data_type == 4),
                        "icon" => './img/vm/host.png',
                        "title" => $instanceName,
                        "agent_uuid" => $d['agent_uuid'],
                        "instance_name" => $d['instance_name'],
                        "task_uuid" => $task_uuid,
                        "node_uuid" => $d['node_uuid'],
                        "create_time" => $d['task_create_time'],
                        "sub_type" => $unifiedField['sub_type'],
                        "isParent" => true,
                        "data_type" => $data_type,
                        'click_show' => false,
                        "module_type" => $d['module_type'],
                        "copy_flag" => $d['copy_flag'],
                        "event_type" => "instance",
                        "item_uuid" => $unifiedField['item_uuid'],
                        "parent_uuid" => $d['parent_uuid'],
                        "path" => '',
                        "type" => 0,
                        "source_storage_uuid" => $d["storage_uuid"],
                        "storage_uuid" => $d["storage_uuid"],
                        "item_name" => $d['item_name'],
                        "sub_module_type" => $d['sub_module_type'],
                    ]);
                }

                $pId = $id;
                $name = $unifiedField['name'];
            }
            // 处理副本任务sqlserver和hana数据库实例显示
            if ($d['module_type'] == $MODULE['DB'] && $data_type == $SOURCE_TYPE['COPY_TASK'] && in_array($unifiedField['sub_type'], $instanceDb)) {
                $parts = explode(':', $d['item_name'], 2); // 只分割两次
                if ($parts) {
                    $name = $parts[0];
                    $instanceName = $parts[1];
                }
            }
            // 添加主机
            $node[] = $this->getItemNode(
                $d,
                $pId,
                $data_type,
                false,
                $detail,
                $unifiedField,
                !$no_check_flag ? false : $no_check,
                $name,
                $instanceName
            );
            if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
                $copy_detail = json_decode($d['detail'], true);
                if (!empty($copy_detail)) {
                    if (!empty($copy_detail['cluster_uuid'])) {
                        if (!empty($copy_detail['cluster_node']) && !in_array($unifiedField['sub_type'], $instanceDb)) {
                            foreach ($copy_detail['cluster_node'] as $key) {
                                $cluster_node_id = $d['task_uuid'] . $key;
                                if (!isset($this->cluster_node[$cluster_node_id])) {
                                    $this->cluster_node[$cluster_node_id] = true;
                                    $node[] = [
                                        "id" => $d['task_uuid'] . $key,
                                        "pId" => $pId,
                                        "name" => $key,
                                        "title" => $key,
                                        "nocheck" => true,
                                        "type" => 0,
                                        "icon" => './img/platform/storage.png',
                                        'event_type' => 'show_node',
                                        "task_uuid" => $task_uuid,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $node;
    }
    /**
     * 解析 db_path 中的 agent 列表
     *
     * @param string $dbPath
     * @param string $dbType
     * @return array
     */
    private function parseAgentList($dbPath, $dbType)
    {
        $start = strpos($dbPath, '(');
        $end = strrpos($dbPath, ')');

        if ($start === false || $end === false || $start >= $end) {
            return [];
        }

        $agentStr = substr($dbPath, $start + 1, $end - $start - 1);
        $separator = ($dbType == xphp_get_config('db', 'DB_TYPE')['TIDB']) ? ', ' : ',';
        return explode($separator, $agentStr);
    }
    /**
     * 获取k8s资源节点信息
     * @param mixed $d
     * @param mixed $data_type
     * @param mixed $unifiedField
     * @return {}
     */
    private function getK8sShowNode($d, $data_type, $unifiedField)
    {
        // 副本源类型 备份数据|任务  副本数据|任务
        $SOURCE_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        $task_uuid = $d['task_uuid'];
        $id = $unifiedField['item_uuid'] . '_' . $task_uuid;
        $node = [];
        $isBackupTask = ($data_type == $SOURCE_TYPE['BACKUP_TASK']);
        if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
            $copy_detail = json_decode($d['detail'], true);
            if (!empty($copy_detail['k8s_tree'])) {
                $detail = [
                    "k8s_tree" => $copy_detail['k8s_tree'],
                    "by_type" => $copy_detail['by_type'],
                ];
                foreach ($copy_detail['k8s_tree'] as $k) {
                    if ($k['type'] != 4) {
                        $k['pId'] = $id;
                    }
                    $node[] = $k;
                }
            }
        } else {
            $sql = "SELECT kjl.app, kjl.name,kjl.app_type,kjl.namespace,kjl.id,kt.by_type,kt.task_uuid,kjl.type 
                FROM kube_object_list kjl
                JOIN kube_task kt ON kjl.task_uuid = kt.task_uuid
                WHERE kt.task_uuid = '{$d['task_uuid']}'";
            $dataK8s = $this->dbSelect($sql);
            $k8sSourceTree = [];
            if (!empty($dataK8s)) {
                foreach ($dataK8s as $k) {
                    $resource_node = $this->getK8sSourceNode($k, $unifiedField['item_uuid'], $task_uuid);
                    $k8sSourceTree = array_merge($k8sSourceTree, $resource_node);
                    if ($isBackupTask) {
                        $node = array_merge($node, $resource_node);
                    }
                }
            }
            $detail = [
                "k8s_tree" => $k8sSourceTree,
                "by_type" => $d['by_type'],
            ];
        }
        return [
            "node" => $node,
            "detail" => $detail,
        ];
    }
    /**
     * 组装k8s资源节点信息
     * @param mixed $k
     * @param mixed $item_uuid
     * @param mixed $task_uuid
     * @return {}
     */
    private function getK8sSourceNode($k, $item_uuid, $task_uuid)
    {
        $node = [];
        // 命名空间
        if ($k['type'] == 1) {
            $namespace_id = $k['namespace'] . $k['task_uuid'];
            if (!isset($this->namespace_arr[$namespace_id])) {
                $this->namespace_arr[$namespace_id] = true;
                $node[] = [
                    "id" => $namespace_id,
                    "pId" =>  $item_uuid . "_" . $task_uuid,
                    "type" => 4,
                    'name' => $k['namespace'],
                    'title' => $k['namespace'],
                    'iconSkin' => 'ztree_namespace',
                    'namespace' => $k['namespace'],
                    "task_uuid" => $task_uuid,
                    "item_uuid" => $item_uuid,
                    "nocheck" => true,
                    'event_type' => 'show_node',
                ];
            }
        }
        // app 
        if ($k['type'] == 2) {
            $namespace_id = $k['namespace'] . $k['task_uuid'];
            // 命名空间
            if (!isset($this->namespace_arr[$namespace_id])) {
                $this->namespace_arr[$namespace_id] = true;
                $node[] = [
                    "id" => $namespace_id,
                    "pId" =>  $item_uuid . "_" . $task_uuid,
                    "type" => 4,
                    'name' => $k['namespace'],
                    'title' => $k['namespace'],
                    'iconSkin' => "ztree_namespace",
                    'namespace' => $k['namespace'],
                    "task_uuid" => $task_uuid,
                    "nocheck" => true,
                    'event_type' => 'show_node',
                ];
            }
            // app
            $app_id = $k['namespace'] . $k['app'];
            if (!isset($this->app_arr[$app_id])) {
                $this->app_arr[$app_id] = true;
                $node[] = [
                    "id" => $app_id,
                    "pId" =>  $namespace_id,
                    "type" => 4,
                    'name' => $k['app'],
                    'title' => $k['app'],
                    'iconSkin' => "ztree_app",
                    'namespace' => $k['namespace'],
                    "task_uuid" => $task_uuid,
                    "item_uuid" => $item_uuid,
                    "nocheck" => true,
                    'event_type' => 'show_node',
                ];
            }
        }
        // pvc
        if ($k['type'] == 3) {
            $pvc_id = $k['name'] . $k['namespace'];
            if (!isset($this->pvc_arr[$pvc_id])) {
                $this->pvc_arr[$pvc_id] = true;
                $node[] = [
                    "id" => $pvc_id,
                    "pId" =>  $k['namespace'] . $k['task_uuid'],
                    "type" => 4,
                    'name' => $k['name'],
                    'title' => $k['name'],
                    'iconSkin' => "ztree_group",
                    'namespace' => $k['namespace'],
                    "nocheck" => true,
                    'event_type' => 'show_node',
                    "task_uuid" => $task_uuid,
                ];
            }
        }
        // 集群资源
        if ($k['type'] == 5) {
            $cluster_id = $k['name'] . $k['task_uuid'];
            if (!isset($this->cluster_arr[$cluster_id])) {
                $this->cluster_arr[$cluster_id] = true;
                $node[] = [
                    "id" => $pvc_id,
                    "pId" =>  $item_uuid . "_" . $task_uuid,
                    "type" => 4,
                    'name' => $k['name'],
                    'title' => $k['name'],
                    'iconSkin' => "ztree_group",
                    'namespace' => $k['namespace'],
                    "nocheck" => true,
                    'event_type' => 'show_node',
                    "task_uuid" => $task_uuid,
                ];
            }
        }
        return $node;
    }
    /**
     * 组装对象节点信息
     * @param mixed $d
     * @param mixed $pId
     * @param mixed $data_type
     * @param mixed $inCopyFlag
     * @param mixed $detail
     * @param mixed $unifiedField
     * @param mixed $no_check_flag
     * @param mixed $name
     * @param mixed $instanceName
     * @return {}
     */
    private function getItemNode($d, $pId, $data_type, $inCopyFlag, $detail, $unifiedField, $no_check_flag = false, $name = '', $instanceName = '', $is_parent_flag = false)
    {
        // 模块类型配置缓存（假设类中已缓存）
        static $MODULE = null;
        if ($MODULE === null) {
            $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        }

        // 参数校验防御
        $task_uuid = isset($d['task_uuid']) ? $d['task_uuid'] : '';
        $node_name = $name ?: (isset($unifiedField['name']) ? $unifiedField['name'] : '');
        $item_uuid = isset($unifiedField['item_uuid']) ? $unifiedField['item_uuid'] : '';
        $sub_type = isset($unifiedField['sub_type']) ? $unifiedField['sub_type'] : '';
        $item_name = isset($unifiedField['item_name']) ? $unifiedField['item_name'] : '';

        // 提前解码 name 字段，避免重复调用
        $decoded_node_name = htmlspecialchars_decode(rawurldecode($node_name));
        $encoded_node_name = htmlspecialchars($node_name);
        $decoded_item_name = htmlspecialchars_decode(rawurldecode($item_name));

        // 复用 DataManage 实例（假设类中已有缓存）
        static $dataManage = null;
        if ($dataManage === null) {
            $dataManage = new DataManage();
        }

        return [
            "id" => $item_uuid . "_" . $task_uuid,
            "pId" => $pId,
            "name" => $decoded_node_name,
            "open" => false,
            "nocheck" => $no_check_flag,
            'iconSkin' => $dataManage->getIconByModule(
                isset($d['module_type']) ? $d['module_type'] : '',
                isset($d['sub_module_type']) ? $d['sub_module_type'] : ''
            ),
            "title" => $decoded_node_name,
            'click_show' => $no_check_flag,
            "node_uuid" => isset($d['node_uuid']) ? $d['node_uuid'] : '',
            "task_name" => isset($d['task_name']) ? $d['task_name'] : '',
            "task_uuid" => $task_uuid,
            "sub_type" => $sub_type,
            "isParent" => $no_check_flag || $is_parent_flag,
            "module_type" => isset($d['module_type']) ? $d['module_type'] : '',
            "copy_flag" => isset($d['copy_flag']) ? $d['copy_flag'] : '',
            "data_type" => (isset($d['module_type']) && $d['module_type'] == $MODULE['DB']) ? 'db_copy' : $data_type,
            'instance_name' => isset($d['instance_name']) ? $d['instance_name'] : $instanceName,
            "event_type" => "host",
            "item_uuid" => $item_uuid,
            "parent_uuid" => isset($d['parent_uuid']) ? $d['parent_uuid'] : '',
            "path" => '',
            "type" => 2, // 副本数据管理 点击加载DataTable
            "source_storage_uuid" => isset($d["storage_uuid"]) ? $d["storage_uuid"] : '',
            "storage_uuid" => isset($d["storage_uuid"]) ? $d["storage_uuid"] : '',
            "storage_type" => isset($d["storage_type"]) ? $d["storage_type"] : '',
            "show_name" => $encoded_node_name,
            "item_name" => $decoded_item_name,
            "inCopyTask" => $inCopyFlag,
            "sub_module_type" => isset($d['sub_module_type']) ? $d['sub_module_type'] : '',
            "db_name" => isset($d['db_name']) ? $d['db_name'] : '',
            "db_cluster_uuid" => isset($d['db_cluster_uuid']) ? $d['db_cluster_uuid'] : '',
            "db_agent_uuid" => isset($d['db_agent_uuid']) ? $d['db_agent_uuid'] : '',
            'detail' => $detail,
        ];
    }
    /**
     * 组装任务节点信息
     * @param mixed $d
     * @param mixed $data_type
     * @param mixed $inCopyFlag
     * @param mixed $taskAvailable
     * @param mixed $no_check_flag
     * @param mixed $pId
     * @return {}
     */
    private function getTaskNode($d, $data_type, $inCopyFlag, $taskAvailable, $pId = '', $click_show = true)
    {
        $task_node_name = $taskAvailable ? $d['task_name'] : $d['task_name'] . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
        $task_uuid = $d['task_uuid'];
        return [
            "id" => $task_uuid . $task_uuid,
            "pId" => $pId,
            "name" => $task_node_name,
            "open" => false,
            "nocheck" => $click_show,
            "isParent" => empty($pId) ? true : false,
            "icon" => "./img/backup_data/backupData-task.png",
            "title" => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . $d['create_time'],
            "path" => '',
            "task_uuid" => $task_uuid,
            "node_uuid" => $d['node_uuid'],
            "task_name" => $d['task_name'],
            "module_type" => $d['module_type'],
            "sub_module_type" => $d['sub_module_type'],
            "parent_uuid" => $d['parent_uuid'],
            "copy_flag" => $d['copy_flag'],
            "data_type" => $data_type,
            "sub_type" => $d['sub_type'],
            "event_type" => "task",
            "type" => 0,
            "source_storage_uuid" => $d["storage_uuid"],
            "storage_uuid" => $d["storage_uuid"],
            "storage_type" => $d["storage_type"],
            "item_name" => $d['item_name'],
            "inCopyTask" => $inCopyFlag,
            "click_show" => $click_show,
        ];
    }
    private function getBackupDataSql($params, $source_item_uuid = [], $point_flag = false)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        // 副本源类型 1 备份数据| 2 备份任务  3 副本数据| 4 副本任务
        $COPY_SRC_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        $FLAG = xphp_get_config('app', 'FLAG');
        $CHAIN_STATUS = xphp_get_config('point', 'CHAIN_STATUS');
        // 任务类型
        $TASK = xphp_get_config('task', 'TASKTYPE');
        $data_type = intval($params['data_type']);
        $module_type = intval($params['module_type']);
        $sub_module_type = intval($params['sub_module_type']);
        $archive_back_flag = $params['archive_back_flag']; //回传标志
        $archive_data_flag = $params['archive_data_flag'];
        $item_uuid = $params['item_uuid'];
        $task_uuid = $params['task_uuid'];
        $item_uuid = $params['item_uuid'];
        $data_type = $params['data_type']; //数据类型 任务|数据
        // 所有模块查询公共字段
        $sql = "SELECT
                    bbt.module_type,
                    bbt.task_type,
                    bbt.task_name,
                    bbt.timepoint,
                    bbt.sub_module_type,
                    bbt.task_uuid,
                    bbt.timepoint_uuid,
	                bbt.depend_point_uuid,
                    bbt.backup_mode,
                    bbt.operation_status,
                    bbt.merge_status,
                    bbt.worm_flag,
                    bbt.chain_uuid,
                    bbt.real_node_uuid,
                    bbt.available_flag,
                    bbt.deleted_flag,
                    bbt.importance_flag,
                    bbt.user_uuid,
                    bbt.chain_status,
                    bbt.storage_uuid,
                    bsr.storage_nickname,
                    bsr.node_uuid,
                    bsr.storage_type,";
        // 查询时间点需要查询安全策略相关字段
        if ($point_flag) {
            $sql .= " bsi.virus_scan_status,bsi.virus_list,bsi.last_virus_scan_time,bsi.last_integrity_check_time,bsi.integrity_check_status,bsi.worm_expire_date,";
        }
        // 查询数量
        $sqlCount = "SELECT COUNT(bbt.timepoint_uuid) AS total ";
        $itemWhereSql = " AND bbt.task_uuid = '{$params['task_uuid']}'";
        // 查询条件
        $whereSql = "   WHERE
                            bbt.deleted_flag = {$FLAG['UNSET']}
                            AND bbt.import_flag = {$FLAG['UNSET']}
                            AND bbt.available_flag = {$FLAG['SET']} 
                            AND bbt.module_type = {$module_type}
                            AND bbt.chain_status IN  ({$CHAIN_STATUS['UNKNOWN']},{$CHAIN_STATUS['NORMAL']}) ";
        switch ($module_type) {
            case $MODULE['VM']:
                $tempSql = "
                        FROM
                            bd_backup_timepoint bbt
                            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                            LEFT JOIN vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid ";
                // 基础查询字段
                $sql .= "
                            vbt.vm_name,
                            vbt.vm_uuid,
                            vbt.vcenter_uuid,
                            vbt.hypervisor_type " . $tempSql;
                $sqlCount .= $tempSql;
                // 模糊匹配
                if (!empty($params['search'])) {
                    $whereSql .= " AND (bbt.task_name LIKE '%{$params['search']}%' OR vbt.vm_name LIKE '%{$params['search']}%' OR bbt.timepoint LIKE '%{$params['search']}%') ";
                }
                if (!empty($item_uuid)) {
                    $whereSql .= " AND vbt.vm_uuid = '{$item_uuid}' ";
                }
                // 对象列表分组
                $itemWhereSql .= " GROUP BY bbt.task_uuid,vbt.vm_uuid";
                // 将副本的对象排列在最前面
                if (!empty($source_item_uuid)) {
                    $source_item_str = implode("','", $source_item_uuid);
                    $itemWhereSql .= " ORDER BY CASE WHEN vbt.vm_uuid IN ('{$source_item_str}') THEN 0 ELSE 1 END, bbt.task_uuid";
                }
                break;
            case $MODULE['FS']:
            case $MODULE['NAS']:
                $tempSql = "
                        FROM
                            bd_backup_timepoint bbt
                            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                            LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid ";
                // 基础查询字段
                $sql .= "
                        fbt.agent_name,
                        fbt.agent_ip AS fs_ip,
                        fbt.agent_uuid,
                        fbt.agent_uuid AS fs_uuid,
                        fbt.source_agent_type AS fs_type " . $tempSql;
                $sqlCount .= $tempSql;
                // 模糊匹配
                if (!empty($params['search'])) {
                    $whereSql .= " AND (bbt.task_name LIKE '%{$params['search']}%' OR fbt.agent_ip LIKE '%{$params['search']}%' OR fbt.agent_name LIKE '%{$params['search']}%' ";
                }
                if (!empty($item_uuid)) {
                    $whereSql .= " AND fbt.agent_uuid = '{$item_uuid}' ";
                }
                // 对象列表分组
                $itemWhereSql .= " GROUP BY bbt.task_uuid,fbt.agent_uuid";
                // 将副本的对象排列在最前面
                if (!empty($source_item_uuid)) {
                    $source_item_str = implode("','", $source_item_uuid);
                    $itemWhereSql .= " ORDER BY CASE WHEN fbt.agent_uuid IN ('{$source_item_str}') THEN 0 ELSE 1 END, bbt.task_uuid";
                }
                break;
            case $MODULE['DB']:
                $tempSql = "
                        FROM
                            bd_backup_timepoint bbt
                            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                            LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid";
                // 基础查询字段
                $sql .= "
                        dbt.agent_ip AS db_ip,
                        dbt.db_uuid,
                        dbt.db_name,
                        dbt.db_type,
                        dbt.agent_uuid AS db_agent_uuid,
                        dbt.cluster_name AS db_cluster_name,
                        dbt.cluster_uuid AS db_cluster_uuid,
                        dbt.dir_path AS db_path,
                        dbt.instance_name " . $tempSql;
                $sqlCount .= $tempSql;
                // 数据库子任务不在这查询，单独查询添加显示
                $whereSql .= " AND (dbt.depend_task_uuid IS NULL OR dbt.depend_task_uuid = '') AND dbt.db_type != 14"; // #27974 屏蔽tidb副本源
                // 模糊匹配
                if (!empty($params['search'])) {
                    $whereSql .= " AND (bbt.task_name LIKE '%{$params['search']}%' OR dbt.dir_path LIKE '%{$params['search']}%' OR bbt.timepoint LIKE '%{$params['search']}%') ";
                }
                if (!empty($params['db_cluster_uuid'])) {
                    $sql .= " AND dbt.cluster_uuid = '{$params['db_cluster_uuid']}'";
                } else {
                    // 数据库需要查实例名
                    if (!empty($params['instance_name'])) {
                        $sql .= " AND dbt.instance_name = '{$params['instance_name']}' ";
                    }
                    if (!empty($params['db_agent_uuid'])) {
                        $sql .= " AND dbt.agent_uuid = '{$params['db_agent_uuid']}' ";
                    }
                }
                if (!empty($item_uuid)) {
                    // 主机uuid
                    if (!empty($params['parent_uuid'])) {
                        $whereSql .= " AND dbt.agent_uuid = '{$params['parent_uuid']}' ";
                    }
                }
                // sqlserver和hana需要查询数据库名
                if (($params['db_type'] == 1 || $params['db_type'] == 13) && !empty($params['db_name'])) {
                    $sql .= " AND dbt.db_name = '{$params['db_name']}'";
                }
                // 对象列表分组
                $itemWhereSql .= " GROUP BY bbt.task_uuid,dbt.dir_path,db_cluster_uuid";
                // 将副本的对象排列在最前面
                if (!empty($source_item_uuid)) {
                    $source_item_str = implode("','", $source_item_uuid);
                    $itemWhereSql .= " ORDER BY CASE WHEN dbt.db_uuid IN ('{$source_item_str}') THEN 0 ELSE 1 END, bbt.task_uuid";
                }
                break;
            case $MODULE['OS']:
                $tempSql = "
                        FROM
                            bd_backup_timepoint bbt
                            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                            LEFT JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid ";
                // 基础查询字段
                $sql .= "
                            obt.agent_ip AS os_ip,
                            obt.agent_uuid AS os_uuid,
                            obt.os_name,
                            obt.os_type " . $tempSql;
                $sqlCount .= $tempSql;
                // 模糊匹配
                if (!empty($params['search'])) {
                    $whereSql .= " AND (bbt.task_name LIKE '%{$params['search']}%' OR obt.agent_ip LIKE '%{$params['search']}%' OR obt.os_name LIKE '%{$params['search']}%' OR bbt.timepoint LIKE '%{$params['search']}%')";
                }
                if (!empty($item_uuid)) {
                    $whereSql .= " AND obt.agent_uuid = '{$item_uuid}' ";
                }
                // 对象列表分组
                $itemWhereSql .= " GROUP BY bbt.task_uuid,obt.agent_uuid";
                // 将副本的对象排列在最前面
                if (!empty($source_item_uuid)) {
                    $source_item_str = implode("','", $source_item_uuid);
                    $itemWhereSql .= " ORDER BY CASE WHEN obt.agent_uuid IN ('{$source_item_str}') THEN 0 ELSE 1 END, bbt.task_uuid";
                }
                break;
            case $MODULE['M365']:
                $tempSql = "
                        FROM
                            bd_backup_timepoint bbt
                            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                            LEFT JOIN m365_backup_timepoint mbt ON bbt.timepoint_uuid = mbt.m365_timepoint_uuid ";
                // 基础查询字段
                $sql .= "
                            mbt.m365_timepoint_uuid AS m365_uuid,
                            mbt.organization_name,
                            mbt.organization_uuid,
                            mbt.organization_info " . $tempSql;
                $sqlCount .= $tempSql;
                // 模糊匹配
                if (!empty($params['search'])) {
                    $whereSql .= " AND (bbt.task_name LIKE '%{$params['search']}%' mbt.organization_name LIKE '%{$params['search']}%' OR bbt.timepoint LIKE '%{$params['search']}%') ";
                }
                if (!empty($item_uuid)) {
                    $whereSql .= " AND JSON_EXTRACT(mbt.organization_info, '$.organization_uuid') = '{$item_uuid}' ";
                }
                // 对象列表分组
                $itemWhereSql .= " GROUP BY bbt.task_uuid, JSON_EXTRACT(mbt.organization_info, '$.organization_uuid')";
                // 将副本的对象排列在最前面
                if (!empty($source_item_uuid)) {
                    $source_item_str = implode("','", $source_item_uuid);
                    $itemWhereSql .= " ORDER BY CASE WHEN JSON_EXTRACT(mbt.organization_info, '$.organization_uuid') IN ('{$source_item_str}') THEN 0 ELSE 1 END, bbt.task_uuid";
                }
                break;
            case $MODULE['KUBE']:
                $tempSql = "
                        FROM
                            bd_backup_timepoint bbt
                            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                            LEFT JOIN kube_backup_timepoint kbt ON bbt.timepoint_uuid = kbt.timepoint_uuid ";
                // 基础查询字段
                $sql .= "
                            kbt.cluster_uuid,
                            kbt.cluster_name,
                            kbt.by_type,
                            kbt.meta AS k8s_detail " . $tempSql;
                $sqlCount .= $tempSql;
                // 模糊匹配
                if (!empty($params['search'])) {
                    $whereSql .= " AND (bbt.task_name LIKE '%{$params['search']}%' kbt.cluster_name LIKE '%{$params['search']}%') ";
                }
                if (!empty($item_uuid)) {
                    $whereSql .= " AND kbt.cluster_uuid = '{$item_uuid}' ";
                }
                // 对象列表分组
                $itemWhereSql .= " GROUP BY bbt.task_uuid,kbt.cluster_uuid";
                // 将副本的对象排列在最前面
                if (!empty($source_item_uuid)) {
                    $source_item_str = implode("','", $source_item_uuid);
                    $itemWhereSql .= " ORDER BY CASE WHEN kbt.cluster_uuid IN ('{$source_item_str}') THEN 0 ELSE 1 END, bbt.task_uuid";
                }
                break;
        }
        // 副本源类型 备份数据|任务  副本数据|任务
        if ($data_type == $COPY_SRC_TYPE['BACKUP_DATA']) {
            $whereSql .= " AND bbt.copy_flag = {$FLAG['UNSET']}";
        } else {
            $whereSql .= " AND bbt.copy_flag = {$FLAG['SET']}";
            if ($archive_back_flag || $archive_data_flag) {
                $whereSql .= " AND bbt.task_type IN (" . $TASK['ARCHIVE'] . "," . $TASK['ARCHIVE_FETCH'] . ")";
            } else {
                $whereSql .= " AND bbt.task_type IN (" . $TASK['BACKUP_COPY'] . "," . $TASK['BACKUP_COPY_FETCH'] . ")";
            }
        }
        // 虚拟化、文件、整机需要查询子模块类型
        if (in_array($module_type, [$MODULE['VM'], $MODULE['PUBLIC_CLOUD'], $MODULE['PRIVATE_CLOUD'], $MODULE['FS'], $MODULE['OBS'], $MODULE['HADOOP'], $MODULE['OS']])) {
            $whereSql .= " AND bbt.sub_module_type = {$sub_module_type}";
        }
        if (v1_auth_need_check_look()) {
            // 权限判断
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['data']);
            $whereSql .= " AND bbt.user_uuid IN ({$user_uuid_arr})";
        }
        //按存储筛选
        if (!empty($params['storage_uuid'])) {
            $whereSql .= " AND bbt.storage_uuid = '{$params['storage_uuid']}' ";
        }
        if (!empty($task_uuid) && $point_flag) {
            $whereSql .= " AND bbt.task_uuid = '{$task_uuid}' ";
        }
        if ($point_flag) {
            $sql .= " LEFT JOIN bd_backup_timepoint_safe_info bsi ON bsi.timepoint_uuid = bbt.timepoint_uuid ";
        }
        return [
            'sqlData' => $sql . $whereSql,
            'sqlCount' => $sqlCount . $whereSql,
            'sqlItem' => $sql . $whereSql . $itemWhereSql,
        ];
    }
    /**
     * 获取备份数据副本源树
     * @param mixed $params
     * @return {}
     */
    private function getBackupDataTask($params)
    {
        $data_type = intval($params['data_type']);    //副本源类型：备份任务|数据 副本任务|数据
        $module_type = intval($params['module_type']);
        $source_task_uuid = json_decode($params['source_task_uuid'], true);
        //备份数据
        // 查询语句
        $sql = $this->getBackupDataSql($params);
        $sqlData = $sql['sqlData'];
        // dump($sqlCount);
        $nodes = [];
            // 获取任务列表
            $sqlTask = $sqlData . " GROUP BY bbt.task_uuid";
            // 将副本的任务排列在最前面
            if (!empty($source_task_uuid)) {
                $sqlTask .= " ORDER BY CASE WHEN bbt.task_uuid IN ('" . implode("','", $source_task_uuid) . "') THEN 0 ELSE 1 END,bbt.task_uuid ";
            }
            $dataTask = $this->dbSelect($sqlTask);
            // 获取当前所有任务节点相关的对象节点
            $task_uuid_arr = array_column((array)$dataTask, 'task_uuid');
            // 组装任务节点
            $task_node_list = $this->getTaskNodeList($module_type, $dataTask, $data_type, $task_uuid_arr);
            $nodes = array_merge($nodes, $task_node_list);
        return $nodes;
    }
    /**
     * 获取任务列表
     * @param mixed $module_type
     * @param mixed $dataTask
     * @param mixed $dataCount
     * @param mixed $data_type
     * @param mixed $task_uuid_arr
     * @return {}
     */
    private function getTaskNodeList($module_type, $dataTask, $data_type, $task_uuid_arr)
    {
        $inCopyFlag = false;
        // 副本源类型 备份数据|任务  副本数据|任务
        $SOURCE_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        $nodes = [];
        // 模块类型
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        //得到所有任务uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $dataCopy = $this->getAllCopyTask() ?? [];
        $task_in_copy = array_column((array)$dataCopy, 'source_task_uuid');
        // 组装任务节点
        if (!empty($dataTask)) {
            foreach ($dataTask as $d) {
                $task_uuid = $d['task_uuid'];
                // 虚拟化需要判断虚拟机是否被副本
                if ($module_type == $MODULE['VM']) {
                    // 获取被副本的虚拟机
                    $inCopyFlag = in_array($task_uuid, $task_in_copy);
                }
                // 任务是否已删除
                $taskAvailable = in_array($task_uuid, $currentTaskUUID);
                // 添加任务节点
                $nodes[] = $this->getTaskNode(
                    $d,
                    $data_type,
                    $inCopyFlag,
                    $taskAvailable,
                );
            }
        }
        if ($module_type == $MODULE['DB'] && in_array($data_type, [$SOURCE_TYPE['BACKUP_DATA'], $SOURCE_TYPE['COPY_DATA']])) {
            $sub_task_node = $this->getAllSubTaskList($data_type, $currentTaskUUID, $task_uuid_arr);
            $nodes = array_merge($nodes, $sub_task_node);
        }
        return $nodes;
    }
    /**
     * 获取备份数据的子任务列表
     * @param mixed $data_type
     * @param mixed $currentTaskUUID
     * @param mixed $task_uuid_arr
     * @return {}
     */
    private function getAllSubTaskList($data_type, $currentTaskUUID, $task_uuid_arr)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $sql = "SELECT
                    bbt.module_type,
                    bbt.task_type,
                    bbt.task_name,
                    bbt.timepoint,
                    bbt.sub_module_type,
                    bbt.task_uuid,
                    bbt.src_data_deleted_flag,
                    bbt.timepoint_uuid,
                    bbt.user_uuid,
                    bbt.chain_status,
                    bsi.virus_scan_status,
                    bsi.virus_list,
                    bsi.last_virus_scan_time,
                    bsi.last_integrity_check_time,
                    bsi.integrity_check_status,
                    bsi.worm_expire_date,
                    dbt.agent_ip AS db_ip,
                    dbt.db_uuid,
                    dbt.db_name,
                    dbt.db_type,
                    dbt.agent_uuid AS db_agent_uuid,
                    dbt.cluster_name AS db_cluster_name,
                    dbt.cluster_uuid AS db_cluster_uuid,
                    dbt.dir_path AS db_path,
                    dbt.depend_task_uuid,
                    dbt.instance_name 
                FROM
                    bd_backup_timepoint bbt
                    LEFT JOIN bd_backup_timepoint_safe_info bsi ON bbt.timepoint_uuid = bsi.timepoint_uuid
                    LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid 
                WHERE
                    bbt.deleted_flag = {$FLAG['UNSET']} 
                    AND bbt.import_flag = {$FLAG['UNSET']} 
                    AND dbt.depend_task_uuid IS NOT NULL 
                    AND dbt.depend_task_uuid != ''";
        $data = $this->dbSelect($sql, []);
        $node = [];
        // 调用公共方法获取存储在线状态
        foreach ($data as $d) {
            // 任务是否已删除
            if (in_array($d['depend_task_uuid'], $task_uuid_arr)) {
                $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
                $node[] = $this->getTaskNode(
                    $d,
                    $data_type,
                    false,
                    $taskAvailable,
                    $d['depend_task_uuid'] . $d['depend_task_uuid']
                );
            }
        }
        return $node;
    }
    /**
     * 获取所有副本任务列表
     * @return {}
     */
    private function getAllCopyTask()
    {

        // 获取被副本的虚拟机
        $sqlCopy = "SELECT cl.item_uuid,cl.source_task_uuid FROM copy_task ct, copy_list cl, bd_task bt WHERE ct.task_uuid = cl.task_uuid AND bt.task_uuid = ct.task_uuid AND bt.module_type = ? AND bt.task_type = 17 GROUP BY cl.source_task_uuid";
        $dataCopy = $this->dbSelect($sqlCopy, [xphp_get_config('module', 'MODULE_TYPE')['VM']]) ?? [];
        return $dataCopy;
    }
    private function getCopyTaskSql($params)
    { 
        // flag
        $FLAG = xphp_get_config('app', 'FLAG');
        $TASK = xphp_get_config('task', 'TASKTYPE');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = intval($params['module_type']);
        $sub_module_type = intval($params['sub_module_type']);
        $archive_back_flag = $params['archive_back_flag'];
        $taskType = $TASK['BACKUP_COPY'];
        if ($archive_back_flag) {
            $taskType = $TASK['ARCHIVE'];
        }
        //副本任务
        $sql = "SELECT
                    bt.task_uuid,
                    bt.task_name,
                    bt.create_time,
                    bt.module_type,
                    bt.sub_module_type,
                    bsr.storage_uuid,
                    bt.storage_pool_uuid,
                    bsr.storage_nickname,
                    bsr.node_uuid,
                    bsr.storage_type,
                    cl.sub_type,
                    cl.parent_uuid,
                    cl.item_uuid,
                    cl.item_name,
                    cl.detail,
                    cl.parent_uuid AS db_agent_uuid 
                FROM
                    copy_list cl,
                    bd_task bt
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                    LEFT JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid 
                WHERE
                    cl.task_uuid = bt.task_uuid 
                    AND bt.module_type = '{$module_type}' 
                    AND bt.delete_flag = '{$FLAG['UNSET']}' 
                    AND bt.task_type = '{$taskType}'";
        if (in_array($module_type, [$MODULE['VM'], $MODULE['PUBLIC_CLOUD'], $MODULE['PRIVATE_CLOUD'], $MODULE['FS'], $MODULE['OBS'], $MODULE['HADOOP'], $MODULE['OS']])) {
            $sql .= " AND bt.sub_module_type = {$sub_module_type}";
        }
        // #27974 屏蔽tidb副本源
        if($module_type == $MODULE['DB']){
            $sql .= " AND cl.sub_type != 14";
        }
        if (!empty($params['search'])) {
            $sql .= " AND (cl.item_name LIKE '%{$params['search']}%' OR bt.task_name LIKE '%{$params['search']}%')";
        }
        //按存储筛选
        if (!empty($params['storage_uuid'])) {
            $sql .= " AND bt.storage_uuid = '{$params['storage_uuid']}'";
        }
        // 超级管理员权限判断
        if (v1_auth_need_check_look()) {
            // 权限判断
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['data']);
            $sql .= " AND bt.user_uuid IN ({$user_uuid_arr})";
        }
        if ($params['archive_flag']) {
            $sql .= " AND bsr.storage_type != 9";
        }
        if ($params['archive_back_flag']) {
            $sql .= " AND bsr.storage_type = 9";
        }
        // 搜索关键字
        if (!empty($search)) {
            $sql .= " AND bt.task_name LIKE '%{$search}%'";
        }
        return $sql;
    }
    // 获取副本副本源树结构
    private function getCopySourceTask($params)
    {
        $data_type = intval($params['data_type']); //副本源类型：备份任务|数据 副本任务|数据
        $module_type = intval($params['module_type']);
        $nodes = [];
        $source_task_uuid = json_decode($params['source_task_uuid'], true);
        // 任务类型
        $TASK = xphp_get_config('task', 'TASKTYPE');
        //副本任务
        $sql = $this->getCopyTaskSql($params);
        
            // 查询任务列表
            $sqlTask = $sql . " GROUP BY bt.task_uuid";
            // 将副本的任务排列在最前面
            if (!empty($source_task_uuid)) {
                $sqlTask .= " ORDER BY CASE WHEN bt.task_uuid IN ('" . implode("','", $source_task_uuid) . "') THEN 0 ELSE 1 END,bt.task_uuid ";
            }
            $dataTask = $this->dbSelect($sqlTask);
            $dataCount = $this->dbSelect($sql . " GROUP BY bt.task_uuid") ?? [];
            // 获取当前所有任务节点相关的对象节点
            $task_uuid_arr = array_column((array)$dataTask, 'task_uuid');
            // 组装任务节点列表
            $task_node_list = $this->getTaskNodeList($module_type, $dataTask, $data_type, $task_uuid_arr);
            $nodes = array_merge($nodes, $task_node_list);
        
        return $nodes;
    }
    /**
     * 按不同模块进行分组排序
     * @param mixed $module_type
     * @param mixed $source_item_uuid
     * @return {}
     */
    private function taskGroupBySql($module_type, $source_item_uuid)
    {
        $sql = '';
        $task_uuid_str = '';

        // 安全处理 source_item_uuid
        if (!empty($source_item_uuid) && is_array($source_item_uuid)) {
            // 转义每个元素防止 SQL 注入（假设数据库连接可用）
            $source_item_uuid = array_map(function ($uuid) {
                return addslashes($uuid); // 或使用更安全的方法如预处理语句
            }, $source_item_uuid);
            $task_uuid_str = implode("','", $source_item_uuid);
        }

        // 模块类型映射配置
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');

        // 定义模块类型与字段的映射关系
        $moduleFieldMap = [
            $MODULE['VM']         => 'vml.vm_uuid',
            $MODULE['OS']         => 'btal.agent_uuid',
            $MODULE['FS']         => 'btal.agent_uuid',
            $MODULE['DB']         => 'dl.db_uuid',
            $MODULE['NAS']        => 'nt.nas_uuid',
            $MODULE['M365']       => 'mo.organization_uuid',
            $MODULE['KUBERNETES'] => 'kt.cluster_uuid',
        ];

        // 获取当前模块对应的字段
        if (isset($moduleFieldMap[$module_type])) {
            $field = $moduleFieldMap[$module_type];
            $sql .= " GROUP BY bt.task_uuid, {$field}";
            // 将副本的对象排列在最前面
            if (!empty($task_uuid_str)) {
                $sql .= " ORDER BY CASE WHEN {$field} IN ('{$task_uuid_str}') THEN 0 ELSE 1 END, {$field}";
            }
        }

        return $sql;
    }
    /**
     * @description: 获取副本源树
     * @param {*} $params
     */
    public function getCopySrcTask($params)
    {
        // 副本源类型 备份数据|任务  副本数据|任务
        $SOURCE_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        switch ($params['data_type']) {
            case $SOURCE_TYPE['BACKUP_TASK']:
                return $this->getBackupSourceTask($params);
            case $SOURCE_TYPE['COPY_DATA']:
            case $SOURCE_TYPE['BACKUP_DATA']:
                return $this->getBackupDataTask($params);
            case $SOURCE_TYPE['COPY_TASK']:
                return $this->getCopySourceTask($params);
        }
    }
    private function getUnifiedFields($d, $data_type)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $SOURCE_TYPE = xphp_get_config('copy', 'COPY_SRC_TYPE');
        $FS_SUB = xphp_get_config('module', 'SUBMODULE_TYPE');
        $instanceDb = [xphp_get_config('db', 'DB_TYPE')['SQLSERVER'], xphp_get_config('db', 'DB_TYPE')['SAPHANA']];
        $sub_module_type = $d['sub_module_type'] ?? 0;
        switch ($d['module_type']) {
            //虚拟机 获取$name/$item_uuid/$icon/$parent_uuid
            case $MODULE['VM']:
            case $MODULE['PUBLIC_CLOUD']:
            case $MODULE['PRIVATE_CLOUD']:
                $item_name = $d['vm_name'] . "(" . xphp_get_config('vm', 'VMHYPERVISORDES')[$d['hypervisor_type']] . ")";
                if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
                    $item_name = $d['item_name'] ?? $d['vm_name'];
                }
                $item_uuid = $d['item_uuid'] ?? $d['vm_uuid'];
                $parent_uuid = $d['parent_uuid'] ?? $d['vcenter_uuid'];
                $sub_type = $d['hypervisor_type'] ?? $d['sub_type'];
                $name = $item_name;
                break;
            //文件 获取$name/$item_uuid/$icon/$parent_uuid
            case $MODULE['FS']:
                $item_uuid = $d['item_uuid'] ?? $d['agent_uuid'];
                $parent_uuid = $d['item_uuid'] ?? $d['agent_uuid'];
                if ($sub_module_type == $FS_SUB['FS']) {
                    $item_name = $d['item_name'] ?? $this->getAgentNameStr($d['agent_uuid'], $d['agent_name'], $d['fs_ip']);
                }
                if ($sub_module_type == $FS_SUB['OBS']) {
                    $item_name = $d['item_name'] ?? $d['agent_name'];
                    if ($data_type == $SOURCE_TYPE['BACKUP_TASK']) {
                        $item_name .= "(" . $d['access_key_id'] . "@" . $d['endpoint_override'] . ")";
                    }
                }
                if ($sub_module_type == $FS_SUB['HADOOP']) {
                    $item_name = ($d['item_name'] ?? $d['agent_name']) . '(' . $d['fs_ip'] . ')';
                    if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
                        $item_name = $d['item_name'];
                    }
                }
                $name = $item_name;
                break;
            //数据库 获取$name/$item_uuid/$icon/$parent_uuid
            case $MODULE['DB']:
                $item_uuid = $d['item_uuid'] ?? $d['db_uuid'];
                $parent_uuid = $d['parent_uuid'] ?? $d['db_agent_uuid'];
                //副本任务显示hostname
                $sub_type = $d['db_type'] ?? $d['sub_type'];
                $item_name =  $d['item_name'] ?? $d['db_name'] . "(" . $d['db_ip'] . ")";
                if ($data_type == $SOURCE_TYPE['COPY_TASK']) {
                    $item_name =  $d['item_name'];
                }
                $name = $item_name;
                if (!empty($d['db_cluster_uuid'])) {
                    // $parent_uuid = $d['db_cluster_uuid'];
                    if (in_array($sub_type, $instanceDb)) {
                        $name =  $d['db_name'];
                        if ($data_type != $SOURCE_TYPE['COPY_TASK']) {
                            $item_name = $d['db_name'] . ':' . $d['db_cluster_name'];
                        }
                    } else {
                        $name =  $d['db_cluster_name'];
                        $item_name =  $d['db_cluster_name'];
                    }
                } else {
                    if (in_array($sub_type, $instanceDb) && $data_type != $SOURCE_TYPE['COPY_TASK']) {
                        $item_name = $d['db_name'] . ':' . $d['instance_name'] . "(" . $d['db_ip'] . ")";
                    }
                }
                break;
            //操作系统 获取$name/$item_uuid/$icon/$parent_uuid
            case $MODULE['OS']:
                $item_uuid = $d['item_uuid'] ?? $d['os_uuid'];
                $parent_uuid = $d['item_uuid'] ?? $d['os_uuid'];
                $item_name = $d['item_name'] ?? $this->getAgentNameStr($d['agent_uuid'], $d['os_name'], $d['os_ip']);
                $name = $item_name;
                break;
            //nas 获取$name/$item_uuid/$icon/$parent_uuid
            case $MODULE['NAS']:
                $item_name = $d['item_name'] ?? ($d['agent_ip'] == $d['nas_nickname'] ? $d['agent_ip'] . "(" . $d['share_path'] . ")" : $d['agent_ip'] . "(" . $d['nas_nickname'] . ")"); //图标区分不同类型
                $sub_type = $d['nas_type'];
                // 获取nas类型 nfs| cifs
                if ($data_type == $SOURCE_TYPE['BACKUP_DATA'] || $data_type == $SOURCE_TYPE['COPY_DATA']) {
                    $item_name = $d['fs_ip'] . "(" . $d['agent_name'] . ")";
                    $detailNas = json_decode($d['detail'], true);
                    $sub_type = $detailNas['nas_type'];
                }
                $parent_uuid = $d['parent_uuid'] ?? $d['agent_uuid'];
                $item_uuid = $d['item_uuid'] ?? $d['nas_uuid'] ?? $d['agent_uuid'];
                $name = $item_name;
                break;
            case $MODULE['M365']:
                $item_name = $d['item_name'] ?? $d['m365_name'];
                $item_uuid = $d['item_uuid'] ?? $d['m365_uuid'];
                $parent_uuid = $d['item_uuid'] ?? $d['m365_uuid'];
                $sub_type = 0;
                // 兼容低版本 603之前的版本m365_backup_timepoint中没有存organization_uuid和organization_name 
                // 如果表中无值就从json中读取
                if ($data_type == $SOURCE_TYPE['COPY_DATA'] || $data_type == $SOURCE_TYPE['BACKUP_DATA']) {
                    $organizationInfo = json_decode($d['organization_info'], true);
                    if (!$item_uuid || !$item_name) {
                        $item_name = $organizationInfo['organization_name'];
                        $item_uuid = $organizationInfo['organization_uuid'];
                    }
                }
                $name = $item_name;
                break;
            case $MODULE['KUBERNETES']:
                $item_name = $d['item_name'] ?? $d['cluster_name'];
                $item_uuid = $d['item_uuid'] ?? $d['cluster_uuid'];
                $parent_uuid = $d['item_uuid'] ?? $d['cluster_uuid'];
                $sub_type = $d['by_type'];
                $name = $item_name;
                break;
        }
        return [
            'item_name' => $item_name,
            'item_uuid' => $item_uuid,
            'parent_uuid' => $parent_uuid,
            'sub_type' => $sub_type,
            'name' => $name,
        ];
    }

    /**
     * @description: 获取主机+ip（别名+ip）
     * @param {*} $agent_uuid
     * @param {*} $fbt_name
     * @param {*} $ip
     */
    public function getAgentNameStr($agent_uuid, $fbt_name, $ip)
    {
        $sql = "SELECT agent_uuid, agent_name, hostname, ip FROM bd_agent WHERE agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agent_uuid)) ?? [];
        if (!empty($result)) {
            if ($result[0]['agent_name'] == $ip) {
                return $result[0]['hostname'] . '(' . $ip . ')';
            } else {
                return $result[0]['agent_name'] . '(' . $ip . ')';
            }
        } else {
            return $fbt_name . '(' . $ip . ')';
        }
    }

    /**
     * @description: 异步获取副本源时间点
     * @param {*} $params
     */
    public function getSyncCopyTimePoint($params)
    {
        $DB_TYPE = xphp_get_config('db', 'DB_TYPE');
        $instanceDb = [$DB_TYPE['SQLSERVER'], $DB_TYPE['SAPHANA']];
        $module_type = intval($params['module_type']); //模块类型
        $task_uuid = $params['task_uuid'];
        $item_uuid = $params['item_uuid'];
        $data_type = $params['data_type']; //数据类型 任务|数据
        $sub_type = $params['sub_type']; //数据库类型
        $checkNode = $params['checkNode']; // 勾选标志
        $point_offset = $params['point_offset'];
        $point_limit = $params['point_limit'];
        // config配置信息
        // 模块类型
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        // 时间点类型
        $MODE = xphp_get_config('task', 'BACKUP_MODE');
        $node = [];
        $timepoint = [];
        $cluster_instance = [];
        $jobInfo = new JobInfo();
        //判断是副本数据还是备份数据
        $sql = $this->getBackupDataSql($params, [], true);
        $sqlCount = $sql['sqlCount'];
        $sqlData = $sql['sqlData'];
        // 排序方式
        $sqlData .= " ORDER BY bbt.timepoint";
        // dump($sqlData);
        $data = $this->dbSelect($sqlData . " LIMIT {$params['point_offset']}, {$params['point_limit']}") ?? [];
        $dataCount = $this->dbSelect($sqlCount) ?? [];
        $full_uuid_List = $this->getPointChainArray($data);
        $chain_list_full_node = [];
        // 找出以chain_uuid作为链关系的完备点
        foreach ($data as $point) {
            if (!empty($point['chain_uuid']) && $point['backup_mode'] == $MODE['FULL']) {
                $chain_list_full_node[$point['chain_uuid']] = $point['timepoint_uuid'];
            }
        }
        $num = 0;
        $unfull_head_flag = false; // 链中加载更多标志
        foreach ($data as $point) {
            $unifiedField = $this->getUnifiedFields($point, $data_type);
            $pId = $item_uuid . '_' . $task_uuid;
            $task_uuid = $point['task_uuid'];
            $time_point_uuid = $point['timepoint_uuid'];
            if ($params['item_uuid'] != $item_uuid) {
                continue;
            }
            $num += 1;
            $pointMixedStatus = $this->getTimePointStatus($point['merge_status'], $point['operation_status'], $point['virus_scan_status'], $point['integrity_check_status']);
            $node_name = $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")";
            // k8s需要显示集群备份的app和命名空间
            if ($module_type == $MODULE['KUBERNETES']) {
                $k8sDetail = json_decode($point['k8s_detail'], true);
                $k8sSource = $k8sDetail['resources'];
                foreach ($k8sSource as $k) {
                    $resource_node = $this->getK8sSourceNode($k, $unifiedField['item_uuid'], $task_uuid);
                    $node = array_merge($node, $resource_node);
                }
            }
            // 增加集群实例信息显示
            if (!empty($point['db_cluster_uuid']) && $module_type == $MODULE['DB'] && !in_array($unifiedField['sub_type'], $instanceDb)) {
                $start = strpos($point['db_path'], '(');
                $end = strrpos($point['db_path'], ')');
                $agentStr = substr($point['db_path'], $start + 1, $end - $start - 1);
                $agentList = explode(',', $agentStr);
                if ($unifiedField['sub_type'] == $DB_TYPE['TIDB']) {
                    $agentList = explode(', ', $agentStr);
                }
                foreach ($agentList as $agentInfoStr) {
                    $instanceInfo = explode('/', $agentInfoStr);
                    $agentIp = trim($instanceInfo[0]);
                    $agentInstanceName = trim($instanceInfo[1]);
                    $agentName = $agentInstanceName . '(' . $agentIp . ')';
                    if (!in_array($agentName, $cluster_instance)) {
                        $cluster_instance[] = $agentName;
                        array_unshift($node,  [
                            "id" => $agentName,
                            "pId" => $item_uuid . $task_uuid,
                            "name" => $agentName,
                            "title" => $agentName,
                            "nocheck" => true,
                            "type" => 0,
                            "icon" => './img/platform/storage.png',
                            "task_uuid" => $task_uuid,
                            "event_type" => 'show_node',
                        ]);
                    }
                }
            }
            //检查并添加完备点
            if ($point['backup_mode'] == $MODE['FULL'] || $module_type == $MODULE['M365']) {
                if (!in_array($time_point_uuid, $timepoint)) {
                    array_push($timepoint, $time_point_uuid);
                    $node[] = array(
                        "id" =>  $time_point_uuid,
                        "pId" =>  $pId,
                        "name" => $node_name,
                        "oldname" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                        "checked" => $checkNode ? true : false,
                        "type" => $point['backup_mode'] != $MODE['FULL'] ? 4 : 3,
                        "item_name" => $unifiedField['item_name'],
                        "point_name" => $point['timepoint'],
                        "vcenter_uuid" => $point['vcenter_uuid'],
                        "time_point_uuid" => $time_point_uuid,
                        "create_time" => $point['task_create_time'],
                        "sub_type" => $sub_type,
                        "node_uuid" => $point['node_uuid'],
                        "task_uuid" => $task_uuid,
                        "icon" => $jobInfo->getTimepointIcon($point['backup_mode']),
                        "title" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                        "chkDisabled" => $point['backup_mode'] != $MODE['FULL'] || $point['operation_status'] != 0,
                        "event_type" => "point",
                        "dataType" => $data_type,
                        "item_uuid" => $item_uuid,
                        "parent_uuid" => $unifiedField['parent_uuid'],
                        "path" => '',
                        'module_type' => $module_type,
                        "source_storage_uuid" => $point["storage_uuid"],
                        "storage_uuid" => $point["storage_uuid"],
                        "depend_point_uuid" => $point["depend_point_uuid"],
                        "task_name" => $point["task_name"],
                        "timepoint" => $point["timepoint"],
                        "sub_module_type" => $point['sub_module_type'],
                        "point_status" => $pointMixedStatus['status'],
                        "available_flag" => $pointMixedStatus['available_flag'],
                    );
                    //添加了完全备份时间点继续下一次
                    if ($dataCount[0]['total'] > $point_limit) {
                        if (!in_array('point_more_node', $timepoint)) {
                            // 主机对象超过20个时，加载更多节点显示
                            if ($num == $point_limit) {
                                array_push($timepoint, 'point_more_node');
                                $node[] = [
                                    "id" => 'point_more_node',
                                    "pId" => $item_uuid . '_' . $task_uuid, // 如果是链中加载更多，需要父级ID
                                    "name" => xphp_get_lang('WEB_FILE_MORE'),
                                    "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                                    "task_uuid" => $task_uuid,
                                    "nocheck" => true,
                                    "point_more" => true,
                                    "point_offset" => $point_offset + $point_limit,
                                    "point_limit" => $point_limit,
                                    "type" => "point",
                                ];
                            }
                        }
                    };
                    continue;
                }
            }
            if (!in_array($time_point_uuid, $timepoint)) {
                array_push($timepoint, $time_point_uuid);
                if (!empty($point['chain_uuid'])) {
                    $pId = $chain_list_full_node[$point['chain_uuid']];
                } else {
                    $pId = $full_uuid_List[$time_point_uuid];
                }
                // 如果是加载更多且查询出来的时间点中找不到指向的完备点，则可能是上一条的剩余时间点，则使用当前时间点作为指向的传入的完备点
                if ($params['point_more'] && empty($pId)) {
                    $pId = $params['pId'];
                }
                $node[] = array(
                    "id" => $time_point_uuid,
                    "pId" => $pId,
                    "name" => $node_name,
                    "oldname" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                    "checked" => $checkNode ? true : false,
                    "type" => 4,
                    "item_name" => $unifiedField['item_name'],
                    "point_name" => $point['timepoint'],
                    "vcenter_uuid" => $point['vcenter_uuid'],
                    "node_uuid" => $point['node_uuid'],
                    "time_point_uuid" => $time_point_uuid,
                    "sub_type" => $sub_type,
                    "version" => $point['version'],
                    "icon" => $this->getTimepointIcon($point['backup_mode']),
                    "title" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode'], $sub_type) . ")",
                    'chkDisabled' => $module_type == $MODULE['DB'] || $point['operation_status'] != 0,
                    "task_uuid" => $task_uuid,
                    'depend_uuid' => $point['depend_point_uuid'],
                    "data_type" => $data_type,
                    "event_type" => "point",
                    "item_uuid" => $item_uuid,
                    "parent_uuid" => $unifiedField['parent_uuid'],
                    "path" => '',
                    'module_type' => $module_type,
                    "source_storage_uuid" => $point["storage_uuid"],
                    "storage_uuid" => $point["storage_uuid"],
                    "depend_point_uuid" => $point["depend_point_uuid"],
                    "task_name" => $point["task_name"],
                    "timepoint" => $point["timepoint"],
                    "sub_module_type" => $point['sub_module_type'],
                );
                if ($dataCount[0]['total'] >= $point_limit) {
                    if (!in_array('point_more_node', $timepoint)) {
                        // 主机对象超过20个时，加载更多节点显示
                        if ($num == $point_limit) {
                            array_push($timepoint, 'point_more_node');
                            $node[] = [
                                "id" => 'point_more_node',
                                "pId" => $item_uuid . '_' . $task_uuid, // 如果是链中加载更多，需要父级ID
                                "name" => xphp_get_lang('WEB_FILE_MORE'),
                                "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                                "task_uuid" => $task_uuid,
                                "nocheck" => true,
                                "point_more" => true,
                                "point_offset" => $point_offset + $point_limit,
                                "point_limit" => $point_limit,
                                "type" => "point",
                            ];
                        }
                        continue;
                    }
                };
            }
        }
        // 判断是否是加载更多且第一个非完备点
        if ($params['point_more']) {
            if ($node[0]['type'] == 4) {
                $unfull_head_flag = true;
            }
        }
        // 判断是否是加载更多且第一个非完备点
        if ($params['point_more']) {
            if ($node[0]['type'] == 4) {
                $unfull_head_flag = true;
            }
        }
        $msg = array(
            're' => true,
            'msg' => $node,
            'unfull_head_flag' => $unfull_head_flag,
        );
        return $msg;
    }

    /**
     * @description: 获取副本任务信息
     * @param {*} $params
     */
    public function getCopyTaskInfo($params)
    {
        $taskUUID = $params['task_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT bt.task_name, bt.task_type, bt.node_uuid, bt.thread_num, bt.strategy_id, bt.storage_uuid, bt.module_type, bt.sub_module_type, bt.ignore_resource_limiting_flag,bt.storage_pool_uuid,
        cl.source_storage_uuid, cl.source_task_uuid, ct.copy_mode, ct.transport_ip,ct.transport_port, cl.sub_type,
        ct.transport_data_encrypt, bt.inc_mode, ct.wan_accelerate_flag, ct.chain_length, ct.detail, ct.detail,ct.copy_week_flag,ct.copy_month_flag,ct.copy_year_flag,ct.copy_last_chain_flag,
        bts.encrypt_flag, bts.compress_flag, bts.compress_method,bts.encrypt_method,bts.network_uuid,bts.network_pool_uuid,
        bsr.storage_nickname, bsr.storage_type, bsr.total_size, bsr.free_size, bsr.storage_type,
        bss.redundant_data_proportiont, bss.data_container_size
        FROM copy_list cl, copy_task ct, bd_transport_strategy bts, bd_storage_strategy bss, bd_task bt
        LEFT JOIN bd_storage_resource bsr ON bsr.storage_uuid = bt.storage_uuid
        WHERE bt.task_uuid = ct.task_uuid
    	AND bt.task_uuid = cl.task_uuid
        AND bt.strategy_id = bts.strategy_id
        AND bt.strategy_id = bss.strategy_id
        AND bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $info = array();
        $copy_info = $data[0];
        $copy_type = intval($copy_info['task_type']);
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = $copy_info['module_type'];
        if ($data) {
            switch ($copy_info['module_type']) {
                case $MODULE['VM']:
                    if ($copy_info['sub_module_type'] == xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']) {
                        $module_type = $MODULE['PUBLIC_CLOUD'];
                    }
                    if ($copy_info['sub_module_type'] == xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD']) {
                        $module_type = $MODULE['PRIVATE_CLOUD'];
                    }
                    break;
                case $MODULE['FS']:
                    if ($copy_info['sub_module_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']) {
                        $module_type = $MODULE['OBS'];
                    }
                    if ($copy_info['sub_module_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']) {
                        $module_type = $MODULE['HADOOP'];
                    }
                    break;
            }
            // 查询存储池类型
            $storagePoolType = 0;
            if (!empty($copy_info['storage_pool_uuid'])) {
                $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$copy_info['storage_pool_uuid']]);
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }
            $reserve_strategy = $this->getCopyReserve($copy_info['strategy_id']);
            $reserve_strategy['gfs_strategy_item_list'] = $this->getGfsStrategyInfo($taskUUID);
            $copy_list_info = $this->getCopyEditInfo($taskUUID);
            $info = array(
                //任务UUID
                'task_uuid' => $taskUUID,
                //任务名
                'task_name' => $copy_info['task_name'],
                //保留策略
                'brs' => $reserve_strategy,
                // 存储策略
                'bss' => [
                    'redundant_data_proportiont' => intval($copy_info['redundant_data_proportiont']),
                    'data_container_size' => intval($copy_info['data_container_size']),
                ],
                //节点
                'node' => array(
                    'storage_uuid' => $copy_info['storage_uuid'],
                    'real_storage_info' => [
                        'uuid' => $copy_info['storage_uuid'],
                        'name' => $copy_info['storage_nickname'],
                        'type' => $copy_info['storage_type'],
                        'total' => intval($copy_info['total_size']),
                        'free' => intval($copy_info['free_size'])
                    ],
                    'type' => intval($copy_info['storage_type']) == xphp_get_config('BD_STORAGE_TYPE')['REMOTE'] ? 1 : 2,
                    'node_uuid' => $copy_info['node_uuid'],
                    'source_storage_uuid' => $copy_info['source_storage_uuid'],
                    'source_storage_type' => $this->getStorageType($copy_info['source_storage_uuid']),
                    'storage_pool_uuid' => $copy_info['storage_pool_uuid'],
                    'node_pool_uuid' => $copy_info['node_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => v1_parse_flag_to_bool($copy_info['encrypt_flag']),
                    'compress' => v1_parse_flag_to_bool($copy_info['compress_flag']),
                    'thread_num' => $copy_info['thread_num'],
                    'compress_method' => $copy_info['compress_method'],
                    "transferNet" => $copy_info['transport_ip'],
                    "encryptData" => v1_parse_flag_to_bool($copy_info['transport_data_encrypt']),
                    "encrypt_method" => $copy_info['encrypt_method'],
                    "transferPort" => $copy_info['transport_port'],
                    "network_pool_uuid" => $copy_info['network_pool_uuid'],
                    "network_uuid" => $copy_info['network_uuid'],
                ),
                //副本源信息
                'copy_list' => $copy_list_info['copy_list'],
                'source_item_uuid' => json_encode($copy_list_info['source_item_uuid']),
                //时间策略
                'time_strategy' => (new JobInfo())->getTimeStrategyInfo($copy_info['strategy_id']),
                //副本类型
                'module_type' => $module_type,
                //限速策略
                'speedInfo' => $this->getSpeedStrategy($taskUUID),
                "task_type" => $copy_type,
                "source_task_uuid" => json_encode($copy_list_info['source_task_uuid']),
                "transport_data_encrypt" => v1_parse_flag_to_bool($copy_info['transport_data_encrypt']),
                "inc_mode" => $copy_info['inc_mode'],
                "wan_accelerate_flag" => v1_parse_flag_to_bool($copy_info['wan_accelerate_flag']),
                "chain_length" => intval($copy_info['chain_length']),
                "copy_mode" => $copy_info['copy_mode'],
                //重试策略
                'retry_strategy' => (new ExchangeJobInfo())->getRetryStrategy($taskUUID),
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($copy_info['ignore_resource_limiting_flag']),
                // 安全策略
                'safe_strategy' => $this->getSafeConfigStrategy($taskUUID),
                "copy_last_chain_flag" => v1_parse_flag_to_bool($copy_info['copy_last_chain_flag']),
                'copy_gfs_strategy' => [
                    "copy_week_flag" => v1_parse_flag_to_bool($copy_info['copy_week_flag']),
                    "copy_month_flag" => v1_parse_flag_to_bool($copy_info['copy_month_flag']),
                    "copy_year_flag" => v1_parse_flag_to_bool($copy_info['copy_year_flag']),
                ],
            );
        }
        return $info;
    }

    /**
     * @description: 得到保留策略
     * @param {*} $strategyID
     */
    private function getCopyReserve($strategyID)
    {
        $sql = "SELECT strategy_type, number, strategy_mode FROM bd_reserved_strategy WHERE strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $info = array();
        if (empty($data)) {
            $info = array(
                'strategy_mode' => 1,
                'type' => 1,
                'value' => 1,
            );
        } else {
            $info = array(
                'strategy_mode' => intval($data[0]['strategy_mode']),
                'type' => intval($data[0]['strategy_type']),
                'value' => intval($data[0]['number']),
            );
        }
        return $info;
    }

    /**
     * @description: 得到修改任务的信息
     * @param {*} $task_uuid
     */
    public function getCopyEditInfo($task_uuid)
    {
        $info = [];
        $source_task_uuid = [];
        $source_item_uuid = [];
        $sql =  "SELECT cl.item_id, cl.item_uuid, cl.parent_uuid, cl.item_name, cl.source_task_uuid, cl.source_storage_uuid, cl.specified_timepoint_list, cl.sub_type, bt.node_uuid,cl.archive_timepoint_uuid FROM copy_list cl, bd_task bt WHERE bt.task_uuid = cl.task_uuid  AND cl.task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid)) ?? [];
        if (!empty($data)) {
            $source_task_uuid = array_values(array_unique(array_column((array)$data, 'source_task_uuid')));
            foreach ($data as $d) {
                $time_point_uuids = array();
                if (!empty($d['specified_timepoint_list'])) {
                    $time_point_uuids = json_decode($d['specified_timepoint_list']);
                }
                $item_uuid = $d['item_uuid'];
                $info[] = array(
                    "id" => $d['item_id'],
                    "item_uuid" => $item_uuid,
                    "parent_uuid" => $d['parent_uuid'],
                    "item_name" => $d['item_name'],
                    "time_point_uuids" => $time_point_uuids,
                    "sub_type" => intval($d['sub_type']),
                    "node_uuid" => $d['node_uuid'],
                    "source_task_uuid" => $d['source_task_uuid'],
                    "task_uuid" => $d['source_task_uuid'],
                    "source_storage_uuid" => $d['source_storage_uuid'],
                    "archive_timepoint_uuid" => $d['archive_timepoint_uuid'],
                );
            }
        }
        $source_item_uuid = array_values(array_unique(array_column((array)$info, 'item_uuid')));
        return [
            "source_task_uuid" => $source_task_uuid,
            "copy_list" => $info,
            "source_item_uuid" => $source_item_uuid,
        ];
    }

    /**
     * @description: 得到修改任务的原任务数据类型
     * @param {*} $params
     */
    public function getSourceDataType($params)
    {
        $source_task_uuid = json_decode($params['source_task_uuid'], true);
        $arr = implode("','", $source_task_uuid);
        $data_type = $params['data_type'];
        if ($data_type == 1 || $data_type == 3) {
            $sql = "SELECT task_type FROM bd_task WHERE task_uuid IN ('$arr') AND delete_flag = 2";
        } else {
            $sql = "SELECT task_type FROM bd_backup_timepoint WHERE task_uuid IN ('$arr') AND deleted_flag = 2";
        }
        $data = $this->dbSelect($sql, []);
        return $data[0];
    }

    /**
     * @description: 得到副本任务名
     * @param {*} $params
     */
    public function getCopyTaskName($params)
    {
        $taskName = xphp_get_lang('UI_PLATFORM_COPY_JOB');
        if ($params['copy_back_flag']) {
            $taskName = xphp_get_lang('UI_PLATFORM_COPY_BACK_JOB');
        } elseif ($params['archive_flag']) {
            $taskName = xphp_get_lang('UI_PLATFORM_ARCHIVE_JOB');
        } else if ($params['archive_back_flag']) {
            $taskName = xphp_get_lang('UI_PLATFORM_ARCHIVE_FETCH_JOB');
        }
        return $this->getValidTaskName($taskName);
    }

    /**
     * @description: 获取任务名
     * @param {*} $taskName
     * @return {*}
     */
    private function getValidTaskName($taskName)
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "SELECT id FROM bd_task WHERE task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "SELECT id FROM bd_backup_timepoint WHERE task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if (empty($data) && empty($data1)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * @description: 获取当前存储的类型
     * @param {*} $params
     */
    public function getStorageType($storage_uuid)
    {
        $sql = "SELECT storage_type FROM bd_storage_resource WHERE storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storage_uuid));
        return $data[0]['storage_type'];
    }
    public function getLocalNodeUUID()
    {
        $sql = "SELECT node_uuid FROM bd_node WHERE node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }

    /**
     * @description:组装时间策略列表
     * @param {*} $params
     * @param {*} $strategy_group_uuid
     * @return {*}
     */
    public function groupTimeList($params, $copy_mode, $strategy_group_uuid)
    {
        $backup_mode = xphp_get_config('task', 'BACKUP_MODE');
        $strategy_type = xphp_get_config('task', 'STRATEGY_TYPE');
        $msg = array();
        if ('strategy' == $params['type']) {
            if (!empty($params['copyStrategy'])) {
                //副本策略
                if ($copy_mode == 1) { //镜像副本
                    $msg[] = $this->groupTimeStrategy($backup_mode['FULL'], $params['copyStrategy'], $strategy_group_uuid);
                }
                if ($copy_mode == 2) { //合并副本
                    $msg[] = $this->groupTimeStrategy($backup_mode['INCREMENTAL'], $params['copyStrategy'], $strategy_group_uuid);
                }
            }
            if (!empty($params['archiveStrategy'])) {
                //归档策略
                if ($copy_mode == 1) { //镜像副本
                    $msg[] = $this->groupTimeStrategy($backup_mode['FULL'], $params['archiveStrategy'], $strategy_group_uuid);
                }
                if ($copy_mode == 2) { //合并副本
                    $msg[] = $this->groupTimeStrategy($backup_mode['INCREMENTAL'], $params['archiveStrategy'], $strategy_group_uuid);
                }
            }
        } else if ('1' == $params['type']) {
            //CDP使用
            $strategy = array('startTime' => "");
            $strategy['type'] = $strategy_type['ONCE'];
        } else if ('2' == $params['type']) {
            //CDP使用
            $strategy = array('startTime' => $params['startTime']);
            $strategy['type'] = $strategy_type['ONCE'];
        } else {
            //一次性备份
            $strategy = array('startTime' => $params['startTime']);
            //检查时间和系统时间 如果一次性备份时间超过了系统时间则退出创建并返回结果
            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            //获取一次性备份时间
            $taskCreateTime = strtotime($params['startTime']);
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_DB_BACKUP_TIME'), xphp_get_lang('WEB_DB_BACKUP_TIME_TIPS'), 'warning'));
            }
            $strategy['type'] = $strategy_type['ONCE'];
            $msg[] = $this->groupTimeStrategy($backup_mode['FULL'], $strategy, $strategy_group_uuid);
        }

        return $msg;
    }
    /**
     * @description: 组装时间策略
     * @param {*} $mode
     * @param {*} $strategy
     * @param {*} $strategy_group_uuid
     * @return {*}
     */
    private function groupTimeStrategy($mode, $strategy, $strategy_group_uuid)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $strategy_type = xphp_get_config('task', 'STRATEGY_TYPE');
        $strategy_roll_type = xphp_get_config('task', 'STRATEGY_ROLL_TYPE');
        $this->paramsCheck($mode, $strategy);
        $strArr = array("mode" => $mode);
        $strArr['strategy_group_uuid'] = $strategy_group_uuid;

        if ($strategy['globalID']) {
            //使用全局策略
            $strArr['global_id'] = $strategy['globalID'];
            return $strArr;
        }
        //时间策略
        if ($mode == 7) {
            $strategy = $strategy[0];
        }
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['days'] = empty($strategy['days']) ? "" : implode("", $strategy['days']) . $strategy['frequency'];
        $strArr['start_time'] = $strategy['startTime'];
        $strArr['roll_flag'] = $strategy['rollFlag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['rollInterval']);
        $strArr['roll_end_time'] = $strategy['endTime'];

        if ($strategy_type['EVERY_DAY'] == intval($strArr['strategy_type'])) {
            //每天备份
            $strArr['days'] = "1111111";
        }

        if ($strategy_type['ONCE'] == intval($strArr['strategy_type'])) {
            //一次性备份
            $strArr['days'] = '';
        }

        if ($strArr['roll_flag']) {
            //滚动备份
            $strArr['roll_flag'] = $strategy_roll_type['ON'];
        } else {
            //不滚动
            $strArr['roll_flag'] = $strategy_roll_type['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }

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
     * @description: 获取异地副本数据的任务信息
     * @param {*} $params
     * @return {*}
     */
    public function getRemoteTreeTask($params)
    {
        $storage_uuid = $params['storage_uuid'];
        $module_type = $params['module_type'];
        //得到所有的副本任务
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $sub_module_type = $params['sub_module_type'];
        $opName = 'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT';
        $node_uuid = $this->getLocalNodeUUID();
        $msg = array(
            'storage_uuid' => $storage_uuid,
            'tree_name_type' => 1,
            'parent_tree_uuid' => '',
            'module_type' => $module_type,
            'sub_module_type' => $sub_module_type,
            'offset' => 0,
            'limit' => 99999,
        );
        $data = $this->service()->getRemoteTree($node_uuid, 1, $opName, $msg);
        $node = array();
        $icon = './img/platform/flag.png';
        $taskList = $data['msg'];
        if (!empty($taskList)) {
            foreach ($taskList as $d) {
                // 任务是否已删除
                $taskAvailable = in_array($d['tree_uuid'], $currentTaskUUID);
                $node[] = array(
                    'id' => $d['tree_uuid'],
                    'pid' => '',
                    'open' => false,
                    "name" => $taskAvailable ? $d['tree_name'] : $d['tree_name'] . "(" . xphp_get_lang('UI_COPY_DATA_TASK_NOT_EXIST') . ")",
                    'item_uuid' => $d['tree_uuid'],
                    'task_uuid' => $d['tree_uuid'],
                    'click_show' => true,
                    'nocheck' => true,
                    'title' => $d['tree_name'],
                    'isParent' => true,
                    "icon" => $icon,
                    'type' => 0,
                    'module_type' => $module_type,
                    'remote_flag' => true,
                    'storage_uuid' => $storage_uuid,
                    'event_type' => 'task',
                    'data_type' => 4,
                    'node_uuid' => $node_uuid,
                    'sub_module_type' => $sub_module_type,
                    'task_name' => $d['tree_name'],
                );
            }
        }
        return $node;
    }

    /**
     * @description: 获取异地副本数据的主机信息
     * @param {*} $params
     * @return {*}
     */
    public function getRemoteTreeHost($params)
    {
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $parent_tree_uuid = $params['task_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $task_name = $params['task_name'];
        $opName = 'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT';
        $node_uuid = $this->getLocalNodeUUID();
        $instanceDb = [xphp_get_config('db', 'DB_TYPE')['SQLSERVER'], xphp_get_config('db', 'DB_TYPE')['SAPHANA']];
        $msg = array(
            'storage_uuid' => $storage_uuid,
            'tree_name_type' => 2,
            'parent_tree_uuid' => $parent_tree_uuid,
            'module_type' => $module_type,
            'sub_module_type' => $sub_module_type,
        );
        $data = $this->service()->getRemoteTree($node_uuid, 1, $opName, $msg);
        $icon = './img/vm/host.png';
        if ($module_type == $MODULE_TYPE['VM']) {
            $icon = './img/vm/vm.png';
        }
        $taskList = $data['msg'];
        $taskArray = array();
        $instanceArray = [];
        if (!empty($taskList)) {
            foreach ($taskList as $d) {
                $name = $d['tree_name'];
                $pId = $parent_tree_uuid;
                if ($module_type == $MODULE_TYPE['DB'] && in_array($d['sub_type'], $instanceDb)) {
                    $parts = explode(':', $d['tree_name'], 2); // 只分割两次
                    if (!in_array($parts[0] . $parent_tree_uuid, $instanceArray)) {
                        array_push($instanceArray,  $parts[0] . $parent_tree_uuid);
                        $node[] = array(
                            'id' => $parts[0] . $parent_tree_uuid,
                            'pId' => $parent_tree_uuid,
                            'name' => $parts[0],
                            'item_name' => $name,
                            'item_uuid' => $d['tree_uuid'],
                            'click_show' => true,
                            'nocheck' => true,
                            'title' => $name,
                            'isParent' => true,
                            "icon" => $icon,
                            'type' => 2,
                            'task_uuid' => $parent_tree_uuid,
                            'module_type' => $module_type,
                            'remote_flag' => true,
                            'storage_uuid' => $storage_uuid,
                            'open' => true,
                            'event_type' => 'instance',
                            'data_type' => 4,
                            'node_uuid' => $node_uuid,
                            'parent_uuid' => $parent_tree_uuid,
                            'source_storage_uuid' => $storage_uuid,
                            'task_name' => $task_name,
                            'sub_module_type' => $sub_module_type,
                            'sub_type' => $d['sub_type'],
                        );
                    }
                }
                if ($module_type == $MODULE_TYPE['DB'] && in_array($d['sub_type'], $instanceDb)) {
                    $parts = explode(':', $d['tree_name'], 2); // 只分割两次
                    $pId = $parts[0] . $parent_tree_uuid;
                    $name = $parts[1];
                }
                if (!in_array($d['tree_uuid'] . $parent_tree_uuid, $taskArray)) {
                    array_push($taskArray, $d['tree_uuid'] . $parent_tree_uuid);
                    $node[] = array(
                        'id' => $d['tree_uuid'] . $parent_tree_uuid,
                        'pId' => $pId,
                        'name' => $name,
                        'item_name' => $name,
                        'item_uuid' => $d['tree_uuid'],
                        'click_show' => true,
                        'nocheck' => true,
                        'title' => $name,
                        'isParent' => true,
                        "icon" => $icon,
                        'type' => 2,
                        'task_uuid' => $parent_tree_uuid,
                        'module_type' => $module_type,
                        'remote_flag' => true,
                        'storage_uuid' => $storage_uuid,
                        'open' => false,
                        'event_type' => 'host',
                        'data_type' => 4,
                        'node_uuid' => $node_uuid,
                        'parent_uuid' => $parent_tree_uuid,
                        'source_storage_uuid' => $storage_uuid,
                        'task_name' => $task_name,
                        'sub_module_type' => $sub_module_type,
                        'sub_type' => $d['sub_type'],
                    );
                }
            }
        }
        return $node;
    }

    /**
     * @description: 获取异地副本数据的时间点信息
     * @param {*} $params
     * @return {*}
     */
    public function getRemoteTreeTimePoint($params)
    {
        $item_name = $params['item_name'];
        $item_uuid = $params['item_uuid'];
        $task_uuid = $params['task_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $data_table_flag = $params['data_table_flag'];
        $task_name = $params['task_name'];
        $checked = $params['checked'];
        $opName = 'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TIMEPOINT';
        $node_uuid = $this->getLocalNodeUUID();
        $jobInfo = new JobInfo();
        $storage = new Storage();
        $timepoint = array();
        $records = array();
        $records['rows'] = array();
        $MODE = xphp_get_config('task', 'BACKUP_MODE');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $msg = array(
            'storage_uuid' => $storage_uuid,
            'parent_uuid' => '',
            'module_type' => $module_type,
            'sub_module_type' => $sub_module_type,
            'item_uuid' => $item_uuid,
            'task_uuid' => $task_uuid,
            'offset' => 0,
            'limit' => 99999,
        );
        $pId = '';
        $data = $this->service()->getRemoteTree($node_uuid, 1, $opName, $msg);
        $taskList = $data['msg']['timepoint_info_list'];
        $count = 0;
        if (!empty($taskList)) {
            $op = array(1, 2, 3);
            foreach ($taskList as $d) {
                // 归档时间点不展示
                if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] || $d['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']) {
                    continue;
                }
                // 虚拟机信息
                $info = json_decode($d['module_timepoint_info'], true);
                if ($d['module_type'] == $MODULE['VM']) {
                    $dir_path = $info['dir_path'];
                    $item_name = $info['vm_name'];
                    $parent_uuid = $info['vcenter_uuid'];
                }
                // 文件|nas信息
                if ($d['module_type'] == $MODULE['FS'] || $d['module_type'] == $MODULE['NAS']) {
                    $path_list = json_decode($info['backup_path_list'], true);
                    $dir_path = array();
                    foreach ($path_list as $p) {
                        array_push($dir_path, $p['backup_path']);
                    };
                    $item_name = $info['agent_name'];
                    $parent_uuid = $info['agent_uuid'];
                }
                // 数据库信息
                if ($d['module_type'] == $MODULE['DB']) {
                    $db_type = intval($info['db_type']);
                    $dir_path = $info['dir_path'];
                    $item_name = $info['db_name'];
                    $parent_uuid = $info['agent_uuid'];
                }
                // 操作系统信息
                if ($d['module_type'] == $MODULE['OS']) {
                    $item_name = $info['os_name'];
                    $parent_uuid = $info['agent_uuid'];
                }
                if ($d['module_type'] == $MODULE['M365']) {
                    $config = json_decode($info['user_config'], true);
                    $infoList = $config['backup_m365_object_info_list'];
                    $dir_path = array();
                    foreach ($infoList as $l) {
                        array_push($dir_path, $l['backup_object_mail']);
                    }
                }
                if ($d['backup_mode'] == $MODE['FULL'] || $d['module_type'] == $MODULE['M365']) {
                    if (!in_array($d['timepoint_uuid'], $timepoint)) {
                        array_push($timepoint, $d['timepoint_uuid']);
                        $node[] = array(
                            'id' => $d['timepoint_uuid'],
                            'time_point_uuid' => $d['timepoint_uuid'],
                            'name' => $this->parseDate($d['timepoint']) . '(' . $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type) . ')' . $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag'])),
                            'oldname' => $this->parseDate($d['timepoint']) . '(' . $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type) . ')',
                            'click_show' => true,
                            'nocheck' => false,
                            "title" => $this->parseDate($d['timepoint']) . '(' . $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type) . ')',
                            "isParent" => false,
                            "icon" => $this->getTimepointIcon($d['backup_mode']),
                            'pId' => $item_uuid . $task_uuid,
                            'remote_flag' => true,
                            'write_size' => v1_calsize($d['write_size'], true),
                            'timepoint' => $this->parseDate($d['timepoint']),
                            'type' => $d['backup_mode'] != $MODE['FULL'] ? 4 : 3,
                            'event_type' => 'point',
                            'item_name' => $item_name,
                            'item_uuid' => $item_uuid,
                            'task_uuid' => $task_uuid,
                            'node_uuid' => $node_uuid,
                            'data_type' => 4,
                            'storage_uuid' => $storage_uuid,
                            'path' => $dir_path,
                            'depend_point_uuid' => $d['depend_point_uuid'],
                            'chkDisabled' => $d['backup_mode'] != $MODE['FULL'],
                            'checked' => $checked ? $checked : false,
                            'parent_uuid' => $parent_uuid,
                            'sub_module_type' => $sub_module_type,
                            'task_name' => $task_name,
                        );
                        $pId = $d['timepoint_uuid'];
                    }
                    continue;
                }
                if ($d['module_type'] == $MODULE['M365']) {
                    $pId = $item_uuid . $task_uuid;
                }
                $node[] = array(
                    'id' => $d['timepoint_uuid'],
                    'time_point_uuid' => $d['timepoint_uuid'],
                    'name' => $this->parseDate($d['timepoint']) . '(' . $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type) . ')',
                    'oldname' => $this->parseDate($d['timepoint']) . '(' . $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type) . ')',
                    'click_show' => true,
                    'nocheck' => false,
                    "title" => $this->parseDate($d['timepoint']) . '(' . $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type) . ')',
                    "isParent" => false,
                    "icon" => $this->getTimepointIcon($d['backup_mode']),
                    'pId' => $pId,
                    'remote_flag' => true,
                    'write_size' => v1_calsize($d['write_size'], true),
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'type' => 4,
                    'event_type' => 'point',
                    'item_name' => $item_name,
                    'item_uuid' => $item_uuid,
                    'task_uuid' => $task_uuid,
                    'node_uuid' => $node_uuid,
                    'data_type' => 4,
                    'chkDisabled' => $d['module_type'] == $MODULE['DB'],
                    'storage_uuid' => $storage_uuid,
                    'path' => $dir_path,
                    'depend_point_uuid' => $d['depend_point_uuid'],
                    'checked' => $checked ? $checked : false,
                    'parent_uuid' => $parent_uuid,
                    'task_name' => $task_name,
                );
            }
        }
        if (!empty($taskList) && $data_table_flag) {
            $count = count($taskList);
            // 按时间点进行降序排列
            array_multisort(array_column($taskList, 'timepoint'), SORT_DESC, $taskList);
            if (!empty($params['sort']) && !empty($params['order'])) {
                if ($params['order'] == 'desc') {
                    array_multisort(array_column($taskList, $params['sort']), SORT_DESC, $taskList);
                } else {
                    array_multisort(array_column($taskList, $params['sort']), $taskList);
                }
            }
            $i = 0;
            foreach ($taskList as $d) {
                $i++;
                if ($i <= $params['offset'] || $i > ($params['offset'] + $params['limit'])) {
                    continue;
                }
                // 表格---
                // 星标
                if ($d['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    $op = array(1);
                }
                //添加备注
                if (!empty($d['remarks'])) {
                    //根据标记是否显示固定备注显示高度
                    $remark = "<a class=\"popovers remarktips\" data-container=\"body\" data-trigger=\"hover\"
                                data-placement=\"right\" data-content=\"" . preg_replace('/\"/', "\'", $d['remarks']) . "\"><i class=\"viconfont vicon-remark-info\"></i></a>";
                }
                $records['rows'][] = array(
                    'id' => $i,
                    'timepoint' => "<span title=\"" . $d['timepoint_uuid'] . "\">" . $this->parseDate($d['timepoint']) . "</span>",
                    'backup_mode' => $jobInfo->getTimepointTypeDes($d['backup_mode'], $db_type), //策略组名称
                    'total_size' => v1_calSize($d['total_size'], true), //策略组类型
                    'write_size' => v1_calSize($d['write_size'], true), //更新时间
                    'storage' => $storage->getStorageName($storage_uuid), //作者
                    'action' => $op, //备注
                    'details' => array(1, 2), //策略详细信息
                    'uuid' => $d['timepoint_uuid'], //
                    // 'hypervisor' => $point['hypervisor_type'],
                    'remark' => $remark,
                    'remark_info' => $d['remarks'],
                    'importance_flag' => false, //
                    'mark' => $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag'])),
                );
            }
        }
        $records["total"] = $count;
        if ($data_table_flag) {
            return $records;
        }
        return $node;
    }

    /**
     * @description: 回传任务创建之后直接启动
     * @param {*} $taskName
     * @return {*}
     */
    private function startCopyBackJob($taskName)
    {
        $sql = "SELECT bt.task_uuid, bt.module_type, bt.task_type FROM bd_task bt, copy_list cl WHERE bt.task_uuid = cl.task_uuid AND bt.task_name = ?
                order by bt.id desc";
        $data = $this->dbSelect($sql, array($taskName));
        if (!$data) return false;
        //调用系统统一启动任务接口.不重新写
        $result = (new CopyJobController())->startJob($data[0]['task_uuid'], 1);
        //这里直接返回成功或失败 bool
        return $result[0];
    }
    /**
     * @description: 获取异地存储网络
     * @param {*} $params
     * @return {*}
     */
    public function getRemoteNetInfo($params)
    {
        $node_uuid = $params['node_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $result = $this->service()->getRemoteNet($storage_uuid, $node_uuid);
        return $result;
    }

    /**
     * 根据给定的数据获取完整的备份点链数组
     * 
     * 本函数的目的是区分出哪些时间点是完整备份，哪些是增量备份，并且要找出所有依赖于完整备份的增量备份时间点
     * 完整备份（full backup）是备份系统中的一种备份方式，它备份的是所有指定的数据，不遗漏任何内容
     * 增量备份（incremental backup）则只备份与上次备份相比新增或修改的数据
     * 
     * @param array $data 包含备份点信息的数组，每个备份点包括备份模式（完整或增量）、时间点UUID和依赖的时间点UUID等信息
     * @return array 返回一个数组，键和值都是时间点UUID，表示完整的备份链
     */
    private function getPointChainArray($data)
    {
        // 如果输入数据为空，则直接返回空数组
        if (empty($data)) {
            return [];
        }
        // 初始化两个数组，分别用于存储完整备份和非完整备份的时间点信息
        $full_uuid_list = [];
        $un_uuid_list = [];
        $backupModes = xphp_get_config('task', 'BACKUP_MODE');
        foreach ($data as $key => $point) {
            if ($point['backup_mode'] == $backupModes['FULL']) {
                $full_uuid_list[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $un_uuid_list[] = $point;
            }
        }
        while (!empty($un_uuid_list)) {
            $un_full_count = count($un_uuid_list);
            $un_full_count_tmp = count($un_uuid_list);
            foreach ($un_uuid_list as $key => $un_full) {
                $dependId = $un_full['depend_point_uuid'];
                foreach ($full_uuid_list as $k => $value) {
                    if ($dependId == $k) {
                        $full_uuid_list[$un_full['timepoint_uuid']] = $full_uuid_list[$k];
                        $un_uuid_list = array_splice($un_uuid_list, $key, 1);
                        $un_full_count_tmp--;
                    }
                    continue;
                }
            }

            if ($un_full_count == $un_full_count_tmp || $un_full_count_tmp == 0)
                break;
        }
        // 返回完整的备份链数组
        return $full_uuid_list;
    }
    /**
     * 搜索源数据
     * @param mixed $params
     * @return {}
     */
    public function searchCopySource($params)
    {
        $data_type = $params['data_type'];
        switch ($data_type) {
            case 1:
                return $this->searchSourceBackupTask($params);
            case 2:
            case 4:
                return $this->searchBackupData($params);
            case 3:
                return $this->searchCopyTask($params);
            default:
                break;
        }
    }
    private function searchCopyTask($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $module_type = intval($params['module_type']);
        $result = [];
        $task_uuid_array = [];
        $item_uuid_array = [];
        //副本任务
        $sql = $this->getCopyTaskSql($params);
        $data = $this->dbSelect($sql) ?? [];
        if (empty($data)) {
            return $result;
        }
        $dataCopy = $this->getAllCopyTask();
        $copyItemArr = $this->getItemInCopyTask();
        $task_in_copy = array_column((array)$dataCopy, 'source_task_uuid');
        foreach ($data as $d) {
            $task_uuid = $d['task_uuid'];
            // 虚拟化需要判断虚拟机是否被副本
            if ($module_type == $MODULE['VM']) {
                // 获取被副本的虚拟机
                $inCopyFlag = in_array($task_uuid, $task_in_copy);
            }
            if (!in_array($task_uuid, $task_uuid_array)) {
                // 添加任务节点
                $result[] = $this->getTaskNode(
                    $d,
                    3,
                    $inCopyFlag,
                    true,
                    '',
                    false,
                );
                $task_uuid_array[] = $task_uuid;
            }
            if (!in_array($d['item_uuid'], $item_uuid_array)) {
                $unifiedField = $this->getUnifiedFields($d, 3);
                // 虚拟机是否被副本
                $inCopyFlag = in_array($unifiedField['item_uuid'], $copyItemArr);
                if ($d['module_type'] == $MODULE['DB']) {
                    $dbNodes = $this->getDBNode($d, 3, $unifiedField);
                    if (!empty($dbNodes)) {
                        $result = array_merge($result, $dbNodes);
                    }
                } else {
                    // k8s显示资源节点，并将资源节点信息存入detail中
                    if ($d['module_type'] == $MODULE['KUBERNETES']) {
                        $k8s_mode = $this->getK8sShowNode($d, 3, $unifiedField);
                        if (!empty($k8s_mode['node'])) {
                            $result = array_merge($result, $k8s_mode['node']);
                        }
                        $detail = $k8s_mode['detail'];
                    }
                    // 添加主机
                    $result[] = $this->getItemNode(
                        $d,
                        $task_uuid . $task_uuid,
                        3,
                        $inCopyFlag,
                        $detail,
                        $unifiedField,
                        false,
                    );
                }
                $item_uuid_array[] = $d['item_uuid'];
            }
        }
        return $result;
    }
    /**
     * 搜索时间点
     * @param mixed $params
     * @return {}
     */
    private function searchBackupData($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $data_type = intval($params['data_type']);    //副本源类型：备份任务|数据 副本任务|数据
        $module_type = intval($params['module_type']);
        $DB_TYPE = xphp_get_config('db', 'DB_TYPE');
        $instanceDb = [$DB_TYPE['SQLSERVER'], $DB_TYPE['SAPHANA']];
        // 时间点类型
        $MODE = xphp_get_config('task', 'BACKUP_MODE');
        //备份数据
        // 查询语句
        $sql = $this->getBackupDataSql($params);
        $sqlData = $sql['sqlData'];
        $data = $this->dbSelect($sqlData) ?? [];
        $result = [];
        if (empty($data)) {
            return $result;
        }
        $task_array = [];
        $item_array = [];
        $timepoint_array = [];
        $cluster_instance = [];
        $jobInfo = new JobInfo();
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $full_uuid_List = $this->getPointChainArray($data);
        $chain_list_full_node = [];
        // 找出以chain_uuid作为链关系的完备点
        foreach ($data as $point) {
            if (!empty($point['chain_uuid']) && $point['backup_mode'] == $MODE['FULL']) {
                $chain_list_full_node[$point['chain_uuid']] = $point['timepoint_uuid'];
            }
        }
        foreach ($data as $d) {
            // 增加任务节点
            $taskNodeId = $d['task_uuid'] . $d['task_uuid'];
            if (!in_array($taskNodeId, $task_array)) {
                $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
                $result[] = $this->getTaskNode(
                    $d,
                    $data_type,
                    false,
                    $taskAvailable,
                    $d['depend_task_uuid'] . $d['depend_task_uuid'],
                    false,
                );
                $task_array[] = $taskNodeId;
            }
            // 增加对象节点
            $unifiedField = $this->getUnifiedFields($d, $data_type);
            $item_uuid = $unifiedField['item_uuid'];
            $task_uuid = $d['task_uuid'];
            $sub_type = $unifiedField['sub_type'];
            $itemNodeId = $d['task_uuid'] . $unifiedField['item_uuid'];
            if (!in_array($itemNodeId, $item_array)) {
                // 虚拟机是否被副本
                if ($d['module_type'] == $MODULE['DB']) {
                    $dbNodes = $this->getDBNode($d, $data_type, $unifiedField, false);
                    if (!empty($dbNodes)) {
                        $result = array_merge($result, $dbNodes);
                    }
                } else {
                    // k8s显示资源节点，并将资源节点信息存入detail中
                    if ($d['module_type'] == $MODULE['KUBERNETES']) {
                        $k8s_mode = $this->getK8sShowNode($d, $data_type, $unifiedField);
                        if (!empty($k8s_mode['node'])) {
                            $result = array_merge($result, $k8s_mode['node']);
                        }
                        $detail = $k8s_mode['detail'];
                    }
                    // 添加主机
                    $result[] = $this->getItemNode(
                        $d,
                        $taskNodeId,
                        $data_type,
                        false,
                        $detail,
                        $unifiedField,
                        false,
                        '',
                        '',
                        true
                    );
                }
                $item_array[] = $itemNodeId;
            }
            // 增加时间点节点
            if (!in_array($d['timepoint_uuid'], $timepoint_array)) {
                $timepoint_array[] = $d['timepoint_uuid'];
                $pId = $item_uuid . "_" . $task_uuid;
                $task_uuid = $d['task_uuid'];
                $time_point_uuid = $d['timepoint_uuid'];
                $pointMixedStatus = $this->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
                $node_name = $d['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($d['backup_mode']) . ")";
                // k8s需要显示集群备份的app和命名空间
                if ($module_type == $MODULE['KUBERNETES']) {
                    $k8sDetail = json_decode($d['k8s_detail'], true);
                    $k8sSource = $k8sDetail['resources'];
                    foreach ($k8sSource as $k) {
                        $resource_node = $this->getK8sSourceNode($k, $unifiedField['item_uuid'], $task_uuid);
                        $result = array_merge($result, $resource_node);
                    }
                }
                // 增加集群实例信息显示
                if (!empty($d['db_cluster_uuid']) && $module_type == $MODULE['DB'] && !in_array($unifiedField['sub_type'], $instanceDb)) {
                    $start = strpos($d['db_path'], '(');
                    $end = strrpos($d['db_path'], ')');
                    $agentStr = substr($d['db_path'], $start + 1, $end - $start - 1);
                    $agentList = explode(',', $agentStr);
                    if ($unifiedField['sub_type'] == $DB_TYPE['TIDB']) {
                        $agentList = explode(', ', $agentStr);
                    }
                    foreach ($agentList as $agentInfoStr) {
                        $instanceInfo = explode('/', $agentInfoStr);
                        $agentIp = trim($instanceInfo[0]);
                        $agentInstanceName = trim($instanceInfo[1]);
                        $agentName = $agentInstanceName . '(' . $agentIp . ')';
                        if (!in_array($agentName, $cluster_instance)) {
                            $cluster_instance[] = $agentName;
                            array_unshift($result,  [
                                "id" => $agentName,
                                "pId" => $item_uuid . "_" . $task_uuid,
                                "name" => $agentName,
                                "title" => $agentName,
                                "nocheck" => true,
                                "type" => 0,
                                "icon" => './img/platform/storage.png',
                                "task_uuid" => $task_uuid,
                            ]);
                        }
                    }
                }
                //检查并添加完备点
                if ($d['backup_mode'] == $MODE['FULL'] || $d['module_type'] == $MODULE['M365']) {
                    $result[] = [
                        "id" =>  $time_point_uuid,
                        "pId" =>  $pId,
                        "name" => $node_name,
                        "oldname" => $d['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($d['backup_mode']) . ")",
                        "checked" => false,
                        "type" => $d['backup_mode'] != $MODE['FULL'] ? 4 : 3,
                        "item_name" => $unifiedField['item_name'],
                        "point_name" => $d['timepoint'],
                        "vcenter_uuid" => $d['vcenter_uuid'],
                        "time_point_uuid" => $time_point_uuid,
                        "create_time" => $d['task_create_time'],
                        "sub_type" => $sub_type,
                        "node_uuid" => $d['node_uuid'],
                        "task_uuid" => $task_uuid,
                        "icon" => $jobInfo->getTimepointIcon($d['backup_mode']),
                        "title" => $d['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($d['backup_mode']) . ")",
                        "chkDisabled" => $d['backup_mode'] != $MODE['FULL'] || $d['operation_status'] != 0,
                        "event_type" => "point",
                        "dataType" => $data_type,
                        "item_uuid" => $item_uuid,
                        "parent_uuid" => $unifiedField['parent_uuid'],
                        "path" => '',
                        'module_type' => $module_type,
                        "source_storage_uuid" => $d["storage_uuid"],
                        "storage_uuid" => $d["storage_uuid"],
                        "depend_point_uuid" => $d["depend_point_uuid"],
                        "task_name" => $d["task_name"],
                        "timepoint" => $d["timepoint"],
                        "sub_module_type" => $d['sub_module_type'],
                        "point_status" => $pointMixedStatus['status'],
                        "available_flag" => $pointMixedStatus['available_flag'],
                    ];
                    continue;
                }
                if (!empty($d['chain_uuid'])) {
                    $pId = $chain_list_full_node[$d['chain_uuid']];
                } else {
                    $pId = $full_uuid_List[$time_point_uuid];
                }
                $result[] = [
                    "id" => $time_point_uuid,
                    "pId" => $pId,
                    "name" => $node_name,
                    "oldname" => $d['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($d['backup_mode']) . ")",
                    "checked" => false,
                    "type" => 4,
                    "item_name" => $unifiedField['item_name'],
                    "point_name" => $d['timepoint'],
                    "vcenter_uuid" => $d['vcenter_uuid'],
                    "node_uuid" => $d['node_uuid'],
                    "time_point_uuid" => $time_point_uuid,
                    "sub_type" => $sub_type,
                    "version" => $d['version'],
                    "icon" => $this->getTimepointIcon($d['backup_mode']),
                    "title" => $d['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($d['backup_mode'], $sub_type) . ")",
                    'chkDisabled' => $module_type == $MODULE['DB'] || $d['operation_status'] != 0,
                    "task_uuid" => $task_uuid,
                    'depend_uuid' => $d['depend_point_uuid'],
                    "data_type" => $data_type,
                    "event_type" => "point",
                    "item_uuid" => $item_uuid,
                    "parent_uuid" => $unifiedField['parent_uuid'],
                    "path" => '',
                    'module_type' => $module_type,
                    "source_storage_uuid" => $d["storage_uuid"],
                    "storage_uuid" => $d["storage_uuid"],
                    "depend_point_uuid" => $d["depend_point_uuid"],
                    "task_name" => $d["task_name"],
                    "timepoint" => $d["timepoint"],
                    "sub_module_type" => $d['sub_module_type'],
                ];
            }
        }
        return $result;
    }
    /**
     * 搜索备份任务
     * @param mixed $params
     * @return {}
     */
    private function searchSourceBackupTask($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $sql = $this->getBackupTaskSql($params);
        $data = $this->dbSelect($sql);
        $result = [];
        if (empty($data)) {
            return $result;
        }
        //得到所有任务uuid
        $copyItemArr = $this->getItemInCopyTask();
        $task_array = [];
        $item_array = [];
        foreach ($data as $d) {
            // 增加任务节点
            $taskNodeId = $d['task_uuid'] . $d['task_uuid'];
            if (!in_array($taskNodeId, $task_array)) {
                $result[] = $this->getTaskNode(
                    $d,
                    1,
                    false,
                    true,
                    $d['depend_task_uuid'] . $d['depend_task_uuid'],
                    false,
                );
                $task_array[] = $taskNodeId;
            }
            // 增加对象节点
            $unifiedField = $this->getUnifiedFields($d, 1);
            $itemNodeId = $d['task_uuid'] . $unifiedField['item_uuid'];
            if (!in_array($itemNodeId, $item_array)) {
                $unifiedField = $this->getUnifiedFields($d, 1);
                // 虚拟机是否被副本
                $inCopyFlag = in_array($unifiedField['item_uuid'], $copyItemArr);
                if ($d['module_type'] == $MODULE['DB']) {
                    $dbNodes = $this->getDBNode($d, 1, $unifiedField);
                    if (!empty($dbNodes)) {
                        $result = array_merge($result, $dbNodes);
                    }
                } else {
                    // k8s显示资源节点，并将资源节点信息存入detail中
                    if ($d['module_type'] == $MODULE['KUBERNETES']) {
                        $k8s_mode = $this->getK8sShowNode($d, 1, $unifiedField);
                        if (!empty($k8s_mode['node'])) {
                            $result = array_merge($result, $k8s_mode['node']);
                        }
                        $detail = $k8s_mode['detail'];
                    }
                    // 添加主机
                    $result[] = $this->getItemNode(
                        $d,
                        $taskNodeId,
                        1,
                        $inCopyFlag,
                        $detail,
                        $unifiedField,
                    );
                }
                $item_array[] = $itemNodeId;
            }
        }
        return $result;
    }
}
