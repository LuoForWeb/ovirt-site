<?php

namespace app\v1\volcdp\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VolcdpOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Index;
use app\v1\system\v0\logic\SystemMonitor;
use xphp\BLLHandler;

/**
 * note          卷实时 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpBackUp extends Base
{
    /**
     * 创建备份任务 demo
     */
    public function createBackupJob($params = [])
    {
        $task_name = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($task_name);
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $operate = (new PfOpcode())->getOpcodeDes($opName);
//        $this->checkTaskLegal($operate, $params['srcInfo']['cdpinfo'], null);  //校验授权
        $module_type = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];

        $strategygroupuuid = '';
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        $reserver_strategy = (new Backup())->groupReserverStrategy($params['highInfo']['reserve'], $strategygroupuuid);
        $transport_strategy = (new Backup())->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        $storage_strategy = (new Backup())->groupStorageStrategy($params['highInfo']['store'], $strategygroupuuid);

        $nodeInfo = (new BLLHandler())->groupBackupNodeInfo($params['highInfo']['node']);
        $node_uuid = $params['node_uuid'];
        $storage_uuid = $params['storage_uuid'];

        $pfMsg = (new BLLHandler())->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo
        );  //公共消息封装
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];

        $pfMsg['auto_find_sr_flag'] = 0; //default value
        $pfMsg['transport_block_size'] = $params['transport_block_size'];  //传输数据块大小
        $pfMsg['storage_block_size'] = $params['storage_block_size'];  //存储数据块大小
        $pfMsg['master_agent_uuid'] = $params['master_agent_uuid'];  //监控主机客户端uuid
        $pfMsg['backup_object'] = $params['backup_object'];  //备份对象
        $pfMsg['standby_enable'] = $params['standby_enable'];  //是否启动双击热备
        $pfMsg['standby_agent_uuid'] = $params['standby_agent_uuid'];  //双击热备备机uuid
        $pfMsg['host_ha_standby_agent_uuid'] = "";  //客户端集群，默认不可用
        $pfMsg['standby_vol_relation_set'] = $params['standby_vol_relation_set'];  //客户端集群，默认不可用
        $pfMsg['takeover_object'] = $params['takeover_object'];  //自动接管对象
        $pfMsg['cache_config'] = $params['cache_config'];  //客户端缓存配置 文件，内存
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['speed_limit_strategy_list'] = array();
        $pfMsg['thread_num'] = $params['thread_num'];
        $pfMsg['speed_limit_strategy_list'] = (new Backup())->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedLimit']);
        $msg = json_encode($pfMsg);
        $mbResult = $this->mbVolCdpMsg($node_uuid, $opName, $msg);
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
     * 获取客户端tree
     */
    public function getVolCdpBackupAgentTree()
    {
        $agentuuidArr = $this->getClientUuids();
        $tree = array();
        if (empty($agentuuidArr)) {
            return json_encode($tree);
        }

        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,net_model,detail
                from bd_agent 
                where agent_type = ? ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr} ";
        }
        $sql .= " order by online_flag ";
        $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL']);

        $data = $this->dbSelect($sql, $sqlParams);

        foreach ($data as $d) {
            $type_icon = "./img/platform/linux.png";
            if ($d['os_type'] == 'Windows') {
                $type_icon = "./img/platform/windows.png";
            }
            $agentUuid = $d['agent_uuid'];

            $inTask = false;
            $isCreateTask = $this->taskExist($agentUuid);  //是否创建任务
            $onlineFlag = $d['online_flag'];

            $istakeoverStandby = false;
            $isUsedTakeoverStandbyInfo = $this->hostIsUsedTakeoverStandby($agentUuid);  //是否作为接管备机使用

            $isHaStandby = false;
            $isUsedHaStandbyInfo = $this->hostIsUsedHaStandby($agentUuid);  //是否作为双机镜像备机使用

            $isFbTarget = false;
            $isUsedFbTargetInfo = $this->hostIsUsedFbTarget($agentUuid);  //是否作为回切目标机器使用

            $isRecoveryTarget = false;
            $isUsedRecoverTargetInfo = $this->hostIsUsedRecoveryTarget($agentUuid);  //是否作为恢复目标机器被使用

            $hostInfo = $d['agent_name'] ? $d['agent_name'] : $d['hostname'];
            $title = $hostInfo;
            if (count($isCreateTask) > 0) {
                $task_name = $isCreateTask['task_name'];
                $inTask = true;
                $title = $hostInfo . "( " . $task_name . xphp_get_lang('WEB_BACKUP_TASK_PROTECTED') . ")";
            }

            if ($isUsedTakeoverStandbyInfo['is_enabled']) {
                $istakeoverStandby = true;
                $task_name = $isUsedTakeoverStandbyInfo['task_name'];
                $title = $hostInfo . "( " . $task_name . xphp_get_lang('WEB_BACKUP_TASK_PROTECTED') . ")";
            }

            if ($isUsedHaStandbyInfo['is_enabled']) {
                $isHaStandby = true;
                $task_name = $isUsedHaStandbyInfo['task_name'];
                $title = $hostInfo . "( " . $task_name . xphp_get_lang('WEB_BACKUP_TASK_PROTECTED') . ")";
            }

            if ($isUsedFbTargetInfo['is_enabled']) {
                $isFbTarget = true;
                $task_name = $isUsedFbTargetInfo['task_name'];
                $title = $hostInfo . "( " . $task_name . xphp_get_lang('WEB_BACKUP_TASK_PROTECTED') . ")";
            }

            if ($isUsedRecoverTargetInfo['is_enabled']) {
                $isRecoveryTarget = true;
                $task_name = $isUsedRecoverTargetInfo['task_name'];
                $title = $hostInfo . "( " . $task_name . xphp_get_lang('WEB_BACKUP_TASK_PROTECTED') . ")";
            }

            $hostName = $this->agentStr($d['agent_name'], $d['hostname'], $d['ip']);

            if ($onlineFlag == xphp_get_config('app')['FLAG']['UNSET']) {
                $onlineStr = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
                $hostName = $hostName . "-" . $onlineStr;
            }
            try {
                $agentDetail = json_decode($d['detail']);
                if(!isset($agentDetail)){
                    $nicList = [];
                    $agentDefaultCachePath = "";
                }else{
                    $nicList = $agentDetail->nic_list;
                    $agentDefaultCachePath = $agentDetail->agent_default_cache_path;
                }
            } catch (Exception $e) {
                $nicList = [];
                $agentDefaultCachePath = "";
            }
            $node = array(
                "id" => $d['id'],
                "pId" => 0,
                "name" => $hostName,
                "title" => $title,
                "isParent" => false,
                "uuid" => $agentUuid,
                "onlineFlag" => v1_parse_flag_to_bool($onlineFlag),
                "nocheck" => true,
                "type" => 1,
                "icon" => $type_icon,
                "osType" => $d['os_type'],
                "inTask" => $inTask,
                "isTakeoverStandby" => $istakeoverStandby,
                "isHaStandby" => $isHaStandby,
                "isFbTarget" => $isFbTarget,
                "isRecoveryTarget" => $isRecoveryTarget,

                "clickshow" => false,
                "checked" => false,
                "chkDisabled" => false,
                "net_model" => intval($d['net_model']),
                "agent_nic" => $nicList,
                "agent_default_cache_path" => $agentDefaultCachePath,
            );
            $tree[] = $node;
        }
        return $tree;
    }

    /**
     * 检查当前客户端是否有任务存在
     * -----当前仅现在是否有备份任务，后续会完善到当前客户端是否是否作为恢复和接管目标主机，且正在被使用的情况
     * @param unknown $agentUuids
     */
    private function taskExist($agentUuids)
    {
        $sql = "SELECT task.task_status,task.task_name from bd_task task,cdp_vol_task vol_task 
            where vol_task.task_uuid = task.task_uuid and task.task_type = ? and vol_task.master_agent_uuid = ? 
                and task.delete_flag = ? ";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql, array($taskType, $agentUuids, $delFlag));
        $taskList = array();
        if (!empty($data)) {
            $taskList = array(
                'task_name' => $data[0]['task_name'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedTakeoverStandby($agentuuid)
    {
        $sqlTo = "select task.task_name from cdp_vol_task_takeover_info vt,bd_task task 
                where takeover_standby_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo, array($agentuuid));
        if (!empty($dataTo)) {
            $isEnabled = true;
            $taskName = $dataTo[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array('task_name' => $taskName, 'is_enabled' => $isEnabled);
    }

    /**
     * 监测客户端是否作为镜像备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedHaStandby($agentuuid)
    {
        $sqlTo = "select task.task_name from cdp_vol_task vt,bd_task task 
                where vt.standby_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo, array($agentuuid));
        if (!empty($dataTo)) {
            $isEnabled = true;
            $taskName = $dataTo[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array('task_name' => $taskName, 'is_enabled' => $isEnabled);
    }

    /**
     * 监测客户端是否作为回切目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedFbTarget($agentuuid)
    {
        $sqlFa = "select task.task_name from cdp_vol_task_takeover_failback_info vt,bd_task task 
                where vt.failback_target_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataFa = $this->dbSelect($sqlFa, array($agentuuid));
        if (!empty($dataFa)) {
            $isEnabled = true;
            $taskName = $dataFa[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array('task_name' => $taskName, 'is_enabled' => $isEnabled);
    }

    /**
     * 检测客户端是否作为恢复目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedRecoveryTarget($agentuuid)
    {
        $sqlRe = "select task.task_name from cdp_vol_task vt,bd_task task 
                where vt.recovery_target_agent_uuid =? and vt.task_uuid = task.task_uuid";

        $dataRe = $this->dbSelect($sqlRe, array($agentuuid));
        if (!empty($dataRe)) {
            $isEnabled = true;
            $taskName = $dataRe[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array('task_name' => $taskName, 'is_enabled' => $isEnabled);
    }

    /**
     * 根据条件组合客户端的名称
     */
    private function agentStr($agentName, $hostName, $ip)
    {
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo = $agentName ? $agentName . "(" . $ip . ")" : $hostName . "(" . $ip . ")";
        } else {
            $hostInfo = $hostName . "(" . $ip . ")";
        }
        return $hostInfo;
    }

    /**
     * 获取授权的细节功能
     * @param unknown $params
     */
    public function getSysAuthFunc()
    {
        $extension = (new Index())->getExtensionLicense();
        $info = $extension['f'];
        return $info;
    }

    /**
     * 更新客户端信息，应用信息和卷信息
     * @param unknown $params
     */
    public function updateAgentInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $refreshAgentSet = array();
        $refreshAgentSet['agent_uuid'] = $agentuuid;
        $refreshAgentSet['app_uuid_set'] = [];
        $refreshSet = array($refreshAgentSet);
        $pfMSg = array();
        $pfMSg['refresh_agent_set'] = $refreshSet;
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeuuid = (new Node())->getLocalNodeUUID();  //获取主节点UUID
        $opName = 'VOL_CDP_TASK_SCANNING_CLIENT_INFO';  //更新应用控制码

        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg);

        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);

        //返回结果到UI
        if ($result) {
            //获取应用跟新状态，读取最新应用信息
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取选择客户端对应的应用信息
     * @param unknown $params
     * #return json object
     */
    public function getVolCdpAppTree($params)
    {
        $agentUuid = $params['agentuuid'];
        $agentName = $params['agentname'];
        $agentIp = $params['agentip'];

        //此处是否需要添加更新应用接口
        $appTree = array();
        $appTree[] = array(
            "id" => $agentUuid,
            "pId" => "",
            "name" => $agentName,
            "title" => $agentIp,
            "isParent" => true,
            "uuid" => $agentUuid,
            "nocheck" => true,
            "isApp" => false,
            "agent_uuid" => $agentUuid,
            "type" => 1,
            "nodeType" => 'host',
            "icon" => "./img/db/host.png",
            "clickshow" => false,
            "checked" => false,
            "chkDisabled" => false,
            "open" => true,
        );
        $appTreeInfo = array();
        $appList = $this->getAppType($agentUuid, $appTree);//应用类型
        return $appList;
    }

    /**
     * 获取客户端已扫描的应用类型
     */
    private function getAppType($agentUUID, $appTree)
    {
        $sql = "select DISTINCT app_type,alias_name from bd_agent_app where agent_uuid = '{$agentUUID}' GROUP BY app_type";
        $data = $this->dbSelect($sql);
        if (count($data) > 0) {
            foreach ($data as $appInfo) {
                $alaisName = $appInfo['alias_name'];
                $appTypeValue = $appInfo['app_type'];
                $appTypeDes = xphp_get_config('db','DB_TYPE_DES')[intval($appTypeValue)] . PHP_EOL;
                if ($appTypeValue == xphp_get_config('db','DB_TYPE')['SQLSERVER']) {
                    $icon = "./img/db/sqlserver.png";
                } else if ($appTypeValue == xphp_get_config('db','DB_TYPE')['ORACLE']) {
                    $icon = "./img/db/oracle.png";
                } else if ($appTypeValue == xphp_get_config('db','DB_TYPE')['MYSQL']) {
                    $icon = "./img/db/mysql.png";
                }
                $id = $agentUUID . $appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => $agentUUID,
                    "name" => $appTypeDes,
                    "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_DB_APPLICATION'),
                    "appTypeValue" => $appTypeValue,
                    "icon" => $icon,
                    "isApp" => false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" => true,
                    "module_vol_info" => array()
                );

                $childTree = $this->getAppData($agentUUID, $id, $appTypeValue);
                $appTree = array_merge($appTree, $childTree);
            }
        }
        return $appTree;
    }

    /**
     * 获取应用类型下的应用信息，此处需要注意节点对应关系
     */
    private function getAppData($agentUUID, $pId, $appTypeValue)
    {
        $sql = "select app_name,app_username,app_password,app_type,app_uuid,online_flag from bd_agent_app 
                where agent_uuid = ? and app_type = ? group by app_uuid";
        $data = $this->dbSelect($sql, array($agentUUID, $appTypeValue));
        $info = array();
        foreach ($data as $d) {
            $id = $pId . $d['app_uuid'];
            $chkDisabled = false;
            $nodeName = $d['app_name'];
            $nodeTitle = $d['app_name'];
            if ($d['online_flag'] == xphp_get_config('app')['FLAG']['UNSET']) {
                $chkDisabled = true;
                $nodeName = $d['app_name'] . "(" . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ")";
                $nodeTitle = $d['app_name'] . xphp_get_lang('UI_VOL_CDP_BACKUP_APPLICATION_OFFLINE');
            }
            $volInfoArray = $this->getAllModuleVolinfo($d['app_uuid']);
            $dosVol = $this->getVolRelationDosVol($volInfoArray, $agentUUID); //获取客户端引导分区卷信息
            $volInfoArray = array_merge($volInfoArray, $dosVol);
            $info[] = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $nodeName,
                "title" => $nodeTitle,
                "uuid" => $d['app_uuid'],
                "app_type" => $d['app_type'],
                "app_type_value" => $appTypeValue,
                "icon" => "./img/db/instance.png",
                "clickshow" => false,
                'agent_uuid' => $agentUUID,
                "nodeType" => 'app',
                "online_flag" => v1_parse_flag_to_bool($d['online_flag']),
                "checked" => false,
                "isApp" => true,
                "chkDisabled" => $chkDisabled,
                "app_username" => $d['app_username'],
                "app_password" => $d['app_password'],
                "module_vol_info" => $volInfoArray
            );
            $childTree = $this->getAppModuleInfo($id, $d['app_uuid'], $appTypeValue, $chkDisabled);
            if (!empty($childTree)) {
                $info = array_merge($info, $childTree);
            }
        }
        return $info;
    }

    /**
     * 获取应用对应的说有卷信息
     * @param unknown $appuuid
     * @return array(mountPath,vol_uuid)
     */
    private function getAllModuleVolinfo($appuuid)
    {
        $sql = "select  md.module_detail from bd_agent_app app,cdp_vol_app_module md  where md.app_uuid = app.app_uuid and app.app_uuid = ?";
        $data = $this->dbSelect($sql, array($appuuid));
        $moduleInfo = array();
        foreach ($data as $d) {
            $moduleDetail = json_decode($d['module_detail']);
            $module_log_file = $moduleDetail->module_log_file;
            $module_config_file = $moduleDetail->module_config_file;
            $module_file_info = $moduleDetail->module_file_info;
            $volInfoArray = $this->getModuleVolinfo($module_log_file, $module_config_file, $module_file_info);
            $moduleInfo = array_merge($moduleInfo, $volInfoArray);
        }
        return $moduleInfo;
    }

    /*
     * 遍历获取应用对应的卷信息，
     */
    public function getModuleVolinfo($obj1, $obj2, $obj3)
    {
        $volInfo = array();

        $objArry = array();
        $objArry[] = $obj1;
        $objArry[] = $obj2;
        $objArry[] = $obj3;
        for ($i = 0; $i < count($objArry); $i++) {
            //            $list = $objArry[$i];
            $list = $objArry[$i] ?? [];
            for ($n = 0; $n < count($list); $n++) {
                $mountPath = $list[$n]->mount_path;
                $volUuid = $list[$n]->vol_uuid;
                if (count($volInfo) > 0) {
                    for ($j = 0; $j < count($volInfo); $j++) {
                        $exists = array_search($volUuid, $volInfo[$j]);
                        if ($exists) {
                            continue;
                        } else {
                            $volInfo[] = array(
                                "mountPath" => $mountPath,
                                "vol_uuid" => $volUuid
                            );
                        }
                    }
                } else {
                    $volInfo[] = array(
                        "mountPath" => $mountPath,
                        "vol_uuid" => $volUuid
                    );
                }
            }
        }
        return $volInfo;
    }

    /**
     * 获取应用关联卷是否是系统卷，如果是系统卷需要获取与之关联的引导分区
     * @param $appVolInfo
     * @param $agentuuid
     */
    private function getVolRelationDosVol($appVolInfo, $agentuuid)
    {
        $bootVolArray = array();
        foreach ($appVolInfo as $d) {
            $mountPath = $d['mountPath'];
            $mountVoluuid = $d['vol_uuid'];
            $sql = "select vol.detail 
                    from bd_agent_vol vol,bd_agent_disk disk 
                    where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid =? and vol.vol_uuid = ?";
            $data = $this->dbSelect($sql, array($agentuuid, $mountVoluuid));
            $systemVol = false;
            if (!empty($data)) {
                $detail = $data[0]['detail'];
                $volDetail = json_decode($detail);
                $volType = $volDetail->vol_type;

                if ($volType & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']) {
                    $systemVol = true;
                }
            }
            if ($systemVol) {
                $bootSql = "SELECT vol.mount_point,vol.vol_uuid 
                        from bd_agent_vol vol,bd_agent_disk disk 
                        where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ? and is_boot = ? ";
                $bootData = $this->dbSelect($bootSql, array($agentuuid, xphp_get_config('app')['FLAG']['SET']));
                if (!empty($bootData)) {
                    foreach ($bootData as $d) {
                        $bootVolArray[] = array(
                            "mountPath" => $d['mount_point'],
                            "vol_uuid" => $d['vol_uuid']
                        );
                    }
                }
            }
        }
        return $bootVolArray;
    }

    /**
     * 获取app 组件信息
     */
    private function getAppModuleInfo($pId, $appUUID, $appTypeValue, $parentNodeOnlineFlag)
    {
        $sql = "SELECT id,app_uuid,module_name,module_detail FROM cdp_vol_app_module WHERE app_uuid = '{$appUUID}'";
        $data = $this->dbSelect($sql);
        $moduleInfo = array();
        foreach ($data as $d) {
            $id = $pId . $d['id'];
            $moduleDetail = json_decode($d['module_detail']);

            $module_log_file = $moduleDetail->module_log_file;
            $module_config_file = $moduleDetail->module_config_file;
            $module_file_info = $moduleDetail->module_file_info;
            $volInfoArray = array();
            $volInfoArray = $this->getModuleVolinfo($module_log_file, $module_config_file, $module_file_info);
            $chkDisabled = false;
            if ($parentNodeOnlineFlag) {
                $chkDisabled = true;
            }
            $moduleInfo[] = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $d['module_name'],
                "title" => $d['module_name'],
                "uuid" => $d['app_uuid'],
                "module_name" => $d['module_name'],
                "icon" => "./img/db/database.png",
                "app_uuid" => $appUUID,
                "file_info" => $module_file_info,
                "app_type_value" => $appTypeValue,
                "clickshow" => false,
                "checked" => false,
                "isApp" => false,
                "nocheck" => true,
                "chkDisabled" => true,
                "module_vol_info" => $volInfoArray,
            );
        }
        return $moduleInfo;
    }

    /**
     * 获取指定客户端对应的卷信息
     */
    public function getHostVolinfo($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $agentuuid = $params['agentuuid'];
        $agentname = $params['agentname'];
        $agentIp = $params['agentip'];
        $selectvol = $params['selectvol'];
        $sortType = $params['sortType'];
        $sortColumn = $params['sortColumn'];
        $start = $params['start'];
        $length = $params['length'];
        $sortType = $params['sortType'];

        $agentSql = "select agent_type from bd_agent where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($agentuuid));
        $agentType = 1;
        if (!empty($data)) {
            $agentType = $data[0]['agent_type'];
        }

        $sortArr = array('', 'vol.vol_name', '', 'vol.capacity', 'vol.free_space');
        $sql = "select vol.vol_uuid,vol.vol_name,vol.capacity,vol.free_space,vol.mount_point, vol.display_name,vol.is_boot,agent.os_type,vol.detail,vol.disk_uuid
                from bd_agent_vol as vol,bd_agent_disk as disk,bd_agent as agent 
                where agent.agent_uuid = disk.agent_uuid and  disk.disk_uuid = vol.disk_uuid and disk.agent_uuid = ? ";
        $data = $this->dbSelect($sql, array($agentuuid));

        $volumes = array();
        $i = 0;
        if (!empty($data)) {
            foreach ($data as $vol_info) {
                $detail = json_decode($vol_info['detail']);
                $volTypeValue = $detail->vol_type;
                $mountPoint = $vol_info['mount_point'];
                $volName = $vol_info['vol_name'];
                $volCapacity = $vol_info['capacity'];
                $disabled = "";
                $volIsChecked = "";
                if (
                    $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RESERVE_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RECOVERY_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_PV_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EXTEND_VOLUME']
                ) {
                    continue;
                }
                if ($volTypeValue & $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SWAP_VOLUME']) {
                    continue;
                }
                //特殊情况下，出现的卷影复制服务。和后台同事协商，暂时屏蔽该卷的备份
                if ($volName == "Microsoft shadow copy partition") {
                    continue;
                }
                $systemVol = false;
                if ($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']) {
                    $systemVol = true;
                }
                $containsSystemVol = false;  //包含系统卷
                $bootPart = false;
                $bootPartStr = "volId" . $vol_info['vol_uuid'];
                $isBootOrSysVolume = false;  //既为BD_BOOT_VOLUME类型又为BD_SYSTEM_VOLUME类型

                if ($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_BOOT_VOLUME'] && $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']) {
                    $isBootOrSysVolume = true;
                }
                if (($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EFI_VOLUME'] || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_BOOT_VOLUME']) && $isBootOrSysVolume != true) {
                    $bootPart = true;
                    $bootPartStr = "isBootPart" . $vol_info['vol_uuid'];
                    if (!empty($selectvol)) {
                        $containsSystemVol = $this->getAppVolumeContainsSystemVol($selectvol, $vol_info);
                        if ($containsSystemVol) {
                            $volIsChecked = ' checked="checked"';
                        }
                    }
                } else {
                    if (!empty($selectvol)) {
                        $containsSystemVol = $this->getAppVolumeContainsSystemVol($selectvol, $vol_info);
                        if ($containsSystemVol) {
                            $volIsChecked = ' checked="checked"';
                        }
                    }
                }

                $is_boot = $vol_info['is_boot'];
//                if ($is_boot == 1) {
//                    $volIcon = '<img src ="./img/platform/windows.png">';
//                } else {
                $volIcon = '<img src = "./img/vm/pool.png">';
//                }
                if(!empty($selectvol)){
                    for ($i = 0; $i < count($selectvol); $i++) {
                        if ($selectvol[$i]['vol_uuid'] == $vol_info['vol_uuid']) {
                            $volIsChecked = ' checked="checked"';
                            //需要同时选中EFI volume 或BOOT volume 分区
                        }
                    }
                }

                if ($mountPoint == null || $mountPoint == "") {
                    $mountPoint = "--";
                }

                $volumes['rows'][] = array(
                    'boot_part_str' => $bootPartStr,
                    'disabled' => $disabled,
                    'volIsChecked' => $volIsChecked,
                    'volIcon' => $volIcon,
                    'vol_name' => $volIcon . $vol_info['display_name'],  // 卷名
                    'mount_point' => $mountPoint,  // 挂载点
                    'capacity' => v1_calsize($vol_info['capacity'], true),  // 总容量
                    'free_space' => v1_calsize($vol_info['free_space'], true),  // 可用容量
                    'vol_uuid' => $vol_info['vol_uuid'],
                    'display_name' => $vol_info['display_name'],
                    'os_type' => $vol_info['os_type'],
                    'vol_type_value' => $volTypeValue,
                    'disk_uuid' => $vol_info['disk_uuid'],
                );
                $i++;
            }
        }
        $volumes["total"] = $i;
        return $volumes;
    }

    /**
     *
     * @param unknown $appVol
     * @param unknown $VolData
     */
    private function getAppVolumeContainsSystemVol($selectvol, $volData)
    {
        $isCheck = false;
        $systemVol = false;
        for ($i = 0; $i < count($selectvol); $i++) {
            $detail = json_decode($volData['detail']);
            $volTypeValue = $detail->vol_type;
            if (($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']) && $selectvol[$i]['vol_uuid'] == $volData['vol_uuid']) {
                $isCheck = true;
                break;
            } else {
                if (($selectvol[$i]['vol_uuid'] == $volData['vol_uuid']) || ($selectvol[$i]['vol_uuid'] == $volData['disk_uuid'])) {
                    $isCheck = true;
                    break;
                }
            }
        }
        return $isCheck;
    }

    /**
     * 获取客户端对应的网卡信息
     * @param unknown $params
     */
    public function getHostNetworkInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $sql = "select detail from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $detail = json_decode($data[0]['detail']);
        $nicList = $detail->nic_list;
        return $nicList;
    }

    /**
     * 获取客户端默认缓存路径
     * @param $params
     */
    public function getAgentCachePath($params)
    {
        $agentuuid = $params['agent_uuid'];
        $sql = "select detail from bd_agent where agent_uuid = ?";
        $sqlParams = array($agentuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $agentDefaultCachePath = "";
        if (!empty($data)) {
            $agentDetail = json_decode($data[0]['detail']);
            $agentDefaultCachePath = $agentDetail->agent_default_cache_path;
        }
        $agentPathInfo = array();
        $agentPathInfo['agent_cache_path'] = $agentDefaultCachePath;
        return $agentPathInfo;
    }

    /**
     * 获取剩余可用的卷CDP接管授权个数
     */
    public function verifyVolCdpTakeoverAuthNum()
    {
        $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $productUsed = (new Index())->getVolcdpTakeoverNum();
        $licenseInfo = $this->getBdLicenseAboutVolcdp();
        $cdpMax = $licenseInfo['cdp_takeover_max_num'];
        $moduleAvailable = false;
        if ($cdpMax - $productUsed >= 1) {
            $moduleAvailable = true;
        }
        $info = array();
        $info['result'] = $moduleAvailable;
        return $info;
    }

    /**
     * 获取cdp剩余授权
     */
    private function getBdLicenseAboutVolcdp()
    {
        $sql = "select cdp_max,cdp_takeover_max_num from bd_license";
        $data = $this->dbSelect($sql);
        return $data[0];
    }

    /**
     * 获取双机镜像备用设备信息
     * @param：host_uuid已选主机UUID
     */
    public function getStandbyHostInfo($params)
    {
        $agentuuidArr = $this->getClientUuids();
        $masterAgentUuid = $params['agentuuid']; //主机uuid
        $standbyuuid = $params['standbyuuid']; //备机UUID （当对某一个选中目标机器进行更新时，仅仅取指定目标机对应卷及磁盘信息）
        $masterOsType = $params['master_os_type'];  //备份主机操作系统类型
        $hostType = $params['host_type']; //客户端用途
        $list = array();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }

        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model,detail
                from bd_agent
                where  (agent_type =? or agent_type =?) ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr} ";
        }

        if (!empty($masterAgentUuid)) {
            $sql .= " and agent_uuid != ?";
            $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'], xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS'], $masterAgentUuid);
        }

        if (!empty($standbyuuid)) {
            $sql .= " and agent_uuid =?";
            $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'], xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS'], $standbyuuid);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $host_info) {
            //当操作系统类型为Normal时，需要判断操作系统类型是否匹配。内存操作系统不做处理
            if ($host_info['agent_type'] == xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'] && $masterOsType != $host_info['os_type']) {
                continue;
            }
            //如果节点状态正常
            $agentType = $host_info['agent_type'];
            $onlineFlag = $host_info['online_flag'];

            $title = xphp_get_lang('UI_VOL_CDP_HOST');
            $hostDesc = $this->agentStr($host_info['agent_name'], $host_info['hostname'], $host_info['ip']);
            if (xphp_get_config('app')['FLAG']['SET'] == $onlineFlag) {
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            } else {
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
                $hostDesc = $hostDesc . "--" . $statusDes;
            }
            if ($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']) {
                $title = xphp_get_lang('UI_VOL_CDP_STANDBY');
                if ($hostType == "takeover") {
                    continue;
                }
            }

            $osType = $host_info['os_type'];

            if ($onlineFlag == xphp_get_config('app')['FLAG']['SET']) {
                $hostVolInfo = $this->getHostVolInfoByUUID($host_info['agent_uuid'], $osType, $agentType);
                $volInfo = $hostVolInfo['vol_info'];
                $diskInfo = $this->getHostDiskInfo($host_info['agent_uuid']);
            } else {
                $volInfo = array();
                $diskInfo = array();
            }

            $list[] = array(
                'uuid' => $host_info['agent_uuid'],
                'text' => $hostDesc,
                'value' => $host_info['agent_uuid'],
                'os_type' => $osType,
                'vol_info' => $volInfo,
                'disk_info' => $diskInfo,
                'mount_info' => $hostVolInfo['mount_info'],
                'agent_type' => $agentType,
                'net_mode' => $host_info['net_model'],
                'title' => $title,
                'online_flag' => v1_parse_flag_to_bool($onlineFlag)
            );
        }
        return $list;
    }

    /**
     * 获取剩余可用的卷cdp授权个数
     */
    public function verifyingVolCdpAuthNum()
    {
        $module = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];
        $productUsed = (new Index())->getVolcdpProtectHost($module);
        $licenseInfo = $this->getBdLicenseAboutVolcdp();
        $cdpMax = $licenseInfo['cdp_max'];
        $takeoverMax = $licenseInfo['cdp_takeover_max_num'];
        $moduleAvailable = false;

        if ($cdpMax - $productUsed >= 1) {  //此处仅限于一个客户端对应一个任务的情况
            $moduleAvailable = true;
        }
        $info = array();
        $info['result'] = $moduleAvailable;
        return $info;
    }

    /**
     * 更新客户端下的所有应用信息/更新当个应用信息
     * @param unknown $params
     * @return string
     */
    public function updateAppInfo($params)
    {
        $refreshType = $params['refreshType'];
        $agentUuid = $params['agentUuid'];
        $appUuidSet = array();
        if ($refreshType == "host") {
            $appUuidSet = $this->getHostAppInfo($agentUuid);
        } else if ($refreshType == "app") {
            $appUuid = $params['uuid'];
            $appUuidSet = array($appUuid);
        }
        $refreshAgentSet = array();
        $refreshAgentSet['agent_uuid'] = $agentUuid;
        $refreshAgentSet['app_uuid_set'] = $appUuidSet;
        $refreshSet = array($refreshAgentSet);

        $pfMSg = array();
        $pfMSg['refresh_agent_set'] = $refreshSet;
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeuuid = (new Node())->getLocalNodeUUID();  //获取主节点UUID
        $opName = 'VOL_CDP_TASK_SCANNING_APP_INFO';  //更新应用控制码

        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if ($result) {
            //获取应用跟新状态，读取最新应用信息
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取客户端对应的所有应用UUID
     * @param hostUuid
     * @return app uuid list
     */
    private function getHostAppInfo($agentUuid)
    {
        $sql = "select app_uuid from bd_agent_app where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid));
        $appList = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $appUuid = $d['app_uuid'];
                array_push($appList, $appUuid);
            }
        }
        return $appList;
    }

    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    public function getHostVolInfoByUUID($uuid, $osType, $agentType)
    {
        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol 
                where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $list = array();
        $HostMountPointArry = array();
        foreach ($data as $v) {
            $detail = json_decode($v['detail']);
            $volTypeValue = $detail->vol_type;
            $mountPoint = $v['mount_point'];
            $volName = $v['vol_name'];
            //swap 分区
//            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if ($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SWAP_VOLUME']) {
                continue;
            }
            $systemVol = false;
            if (
                ($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_BOOT_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EFI_VOLUME']) && $agentType != xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']
            ) {
                $systemVol = true;
            }

            //保留分区、恢复分区、pv分区、扩展分区
            if (
                $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_PV_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EXTEND_VOLUME']
            ) {
                continue;
            }

            if ($agentType != xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS'] && $v['is_boot'] == xphp_get_config('app')['FLAG']['SET']) {
                continue;
            }

            $mountPoint = $v['mount_point'];
            if (!$mountPoint) {
                // continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName, "live-");
            if ($pos !== false) {
                continue;
            }
            array_push($HostMountPointArry, $v['mount_point']);
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "agent_type" => $agentType,
                "capacity_value" => $v['capacity'],
                "capacity" => v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "is_boot" => $v['is_boot'],
                "display_name" => $v['display_name'],
                "mount_point" => $v['mount_point'],
                "agnet_uuid" => $uuid,
                "system_vol" => $systemVol,
            );
        }
        $hostVolInfo = array(
            'mount_info' => $HostMountPointArry,
            'vol_info' => $list
        );
        return $hostVolInfo;
    }

    /**
     * 获取主机磁盘信息
     */
    private function getHostDiskInfo($uuid)
    {
        $sql = "select disk_uuid,capacity,free_space,display_name,detail from bd_agent_disk where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $list = array();
        foreach ($data as $v) {
            $diskDetail = json_decode($v['detail']);
            $deviceType = $diskDetail->device_type;
            if ($deviceType == "12") {
                continue;
            }
            $list[] = array(
                "uuid" => $v['disk_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" => v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "display_name" => $v['display_name']
            );
        }
        return $list;
    }

    /**
     * @method 校验ip
     * @param string ip
     * @desc 监测当前输入IP在网络环境是否存在，存在返回1，反之0
     */
    public function checkIpExists($params)
    {
        $ip = $params['ip'];
        $output = exec('ping -c 1 -w 1 ' . $ip, $out, $status);
        if ($status == 0) { //代表能ping通
            $exists = 1;
        } else {
            $exists = 0;
        }
        return array(
            'exists' => v1_parse_flag_to_bool($exists)
        );

    }

    /**
     * @method 获取主机已配置的应用，封装成tree
     * @parmas agentuuid,applist,...
     * @desc 根据已配置的主机应用UUID,组装成树形结构，且全部选中返回
     */
    public function getHostConfiguredAppTree($params)
    {
        $agentUuid = $params['agentuuid'];
        $appList = $params['applist'];//主机配置的监控应用uuid 集合
        $agentIp = $params['agentip'];
        $agentname = $params['agentname'];

        $standbyHostUuid = $params['standbyHostUuid'];//备机UUID
        $standbyHostName = $params['standbyHostName'];//备机name
        $hostSelectedAppType = $params['hostSelectedAppType']; //主机配置的监控应用类型
        $app_tree = array();
        $app_tree[] = array(
            "id" => $agentUuid,
            "pId" => "",
            "name" => $standbyHostName,
            "title" => $standbyHostName,
            "isParent" => true,
            "uuid" => $agentUuid,
            "nocheck" => true,
            "isApp" => false,
            "type" => 1,
            "icon" => "./img/db/host.png",
            "clickshow" => false,
            "checked" => false,
            "chkDisabled" => false,
            "open" => true,
        );
        $appTreeInfo = array();
        $appTreeInfo = $app_tree;
        $appList = $this->getStandbyAppType($agentUuid, $standbyHostUuid, $hostSelectedAppType, $app_tree, $appList);//应用类型
        $appInfo = $appList[0];
        $appExist = $appList[1];
        if ($appExist) {
            $appTreeInfo = $appInfo;
        } else {
            $appTreeInfo = array();
        }
        return $appTreeInfo;
    }

    /**
     * @method 获取备机应用list
     * @param $agentUuid:主机uuid,$standbyHostUuid：备机uuid,$hostSelectedAppType:主机监控应用类型,
     *        $appTree：父节点,主机已配置应用list
     * @desc 根据选择的备机uuid、主机配置的监控应用类型筛选备机应用
     */
    public function getStandbyAppType($agentUuid, $standbyHostUuid, $hostSelectedAppType, $appTree, $appList)
    {
        $appTypeValue = $hostSelectedAppType;
        $appTypeDes = "";
        $appTypeDes .= xphp_get_config('db', 'DB_TYPE_DES')[intval($appTypeValue)];
        if ($appTypeValue == xphp_get_config('db','DB_TYPE')['SQLSERVER']) {
            $icon = "./img/db/sqlserver.png";
        } else if ($appTypeValue == xphp_get_config('db','DB_TYPE')['ORACLE']) {
            $icon = "./img/db/oracle.png";
        } else if ($appTypeValue == xphp_get_config('db','DB_TYPE')['MYSQL']) {
            $icon = "./img/db/mysql.png";
        }
        $id = $standbyHostUuid . $appTypeValue;

        $appTree[] = array(
            "id" => $id,
            "pId" => $standbyHostUuid,
            "name" => $appTypeDes,
            "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_DB_APPLICATION'),
            "appTypeValue" => $appTypeValue,
            "icon" => $icon,
            "isApp" => false,
            "clickshow" => false,
            "nocheck" => true,
            "checked" => true,
            "chkDisabled" => false,
            "open" => true,
            "module_vol_info" => array(),
            "standbyHostUuid" => $standbyHostUuid,
        );
        $appDataInfo = $this->getStandbyAppData($agentUuid, $standbyHostUuid, $id, $appTree, $hostSelectedAppType, $appList);

        $appData = $appDataInfo[0];
        $appDataExist = $appDataInfo[1];
        if ($appDataExist) {
            $appDataInfoTree = $appData;
        } else {
            $appDataInfoTree = [];
        }

        return array($appDataInfoTree, $appDataExist);
    }

    /**
     * @method 获取备机应用
     * @param  $standbyHostUuid：备机uuid，$pId:父节点树ID，$appTypeValue：应用类型
     * @desc 获取备机应用，根据主机以配置应用，通过应用名选中备机应用应用
     */
    public function getStandbyAppData($agentUuid, $standbyHostUuid, $pId, $appTree, $appTypeValue, $appList)
    {
        $sql = "select app_name,app_username,app_password,app_uuid,online_flag, app_type
               from bd_agent_app
               where agent_uuid = ? group by app_uuid";
        $sqlParams = array($standbyHostUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $appDataExist = false;
        if (!empty($data)) {
            foreach ($data as $d) {
                $id = $pId . $d['app_uuid'];
                $stAppUuid = $d['app_uuid'];
                $appName = $d['app_name'];
                $appType = $d['app_type'];
                $standby_name = "";
                $standby_type = "";
                $isChecked = false;

                $hostInfo = $this->getHostAppMapInfo($appList, $agentUuid, $appName);
                $appMap = $hostInfo['app_map'];
                $hostAppUuid = $hostInfo['app_uuid'];
                if (!empty($hostInfo)) {
                    $isChecked = true;
                    $appDataExist = true;
                }

                $appTree[] = array(
                    "id" => $id,
                    "pId" => $pId,
                    "name" => $d['app_name'],
                    "title" => $d['app_name'],
                    "st_app_uuid" => $stAppUuid,
                    "host_app_uuid" => $hostAppUuid,
                    "is_online" => v1_parse_flag_to_bool($d['online_flag']),
                    "app_type_value" => $appTypeValue,
                    "icon" => "./img/db/instance.png",
                    "clickshow" => false,
                    "checked" => $isChecked,
                    "isApp" => true,
                    "chkDisabled" => false,
                    "app_username" => $d['app_username'],
                    "app_password" => $d['app_password'],
                    "module_vol_info" => array()
                );
                $childTree = $this->getStandbyHostAppModuleInfo($id, $d['app_uuid'], $appTree, $appTypeValue);
                $appModuleInfo = array_merge($appTree, $childTree);
            }
        } else {
            $appModuleInfo = $appTree;
        }
        return array($appModuleInfo, $appDataExist);
    }

    /**
     * @method 解析主机应用
     * @desc 获取主机应 应用名与应用UUID的映射关系
     */
    public function getHostAppMapInfo($appList, $agentUuid, $appName)
    {
        $appMapList = array();
        $appMap = false;
        for ($i = 0; $i < count($appList); $i++) {
            $sql = "select app_name,app_uuid,app_type from bd_agent_app 
                    where app_uuid = '{$appList[$i]}' and agent_uuid = '{$agentUuid}' and app_name = '{$appName}'";
            $data = $this->dbSelect($sql);
            if (!empty($data)) {
                $appMapList = array(
                    'app_map' => true,
                    'app_uuid' => $data[0]['app_uuid']
                );
                $appMap = true;
            }
        }
        return $appMapList;
    }

    /**
     * @method 获取应用模块详情
     */
    public function getStandbyHostAppModuleInfo($pId, $app_uuid, $appTree, $appTypeValue)
    {
        $sql = "SELECT id,app_uuid,module_name,module_detail FROM cdp_vol_app_module WHERE app_uuid = '{$app_uuid}'";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                $id = $pId . $d['module_name'];
                $moduleDetail = json_decode($d['module_detail']);
                $module_log_file = $moduleDetail->module_log_file;
                $module_config_file = $moduleDetail->module_config_file;
                $module_file_info = $moduleDetail->module_file_info;
                $volInfoArray = array();
                $volInfoArray = $this->getModuleVolinfo($module_log_file, $module_config_file, $module_file_info);
                $moduleInfo[] = array(
                    "id" => $id . $d['module_name'],
                    "pId" => $pId,
                    "name" => $d['module_name'],
                    "uuid" => $d['app_uuid'],
                    "module_name" => $d['module_name'],
                    "icon" => "./img/db/database.png",
                    "app_type_value" => $appTypeValue,
                    "clickshow" => false,
                    "checked" => false,
                    "isApp" => false,
                    "nocheck" => true,
                    "chkDisabled" => false,
                    "module_vol_info" => $volInfoArray,
                );
            }
        }
        return $moduleInfo;
    }

    /**
     * 得到卷CDP恢复任务名
     * @param unknown $params
     */
    public function getVolCdpBackupTaskName($params)
    {
        $taskName = $params['task_name'];
        return array(
            'task_name' => (new VolcdpRecover())->getValidTaskName($taskName)
        );
    }

    /**
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    private function groupBackupTimeList($params, $strategygroupuuid)
    {
        $msg = array();
        if (!empty($params['tagInfo'])) {  //标签策略
            $msg = $this->groupEachTagstrategy(xphp_get_config('task', 'BACKUP_MODE')['TAG'], $params['tagInfo'], $strategygroupuuid, $globalID = 0);
        }
        return $msg;
    }

    /**
     * 组合标签策略
     * @param int $mode
     * @param array $strategy
     * @return array
     */
    private function groupEachTagstrategy($mode, $strategyArray, $strategygroupuuid, $globalID = 0)
    {
        $strArr = array();
        foreach ($strategyArray as $strategy) {
            if (xphp_get_config('task','STRATEGY_TYPE')['EVERY_DAY'] == intval($strArr['strategy_type'])) {
                $daysInfo = "1111111";
            } else {
                $daysInfo = implode("", $strategy['days']) . $strategy['frequency'];
            }
            if ($strategy['rollFlag']) {  //滚动执行
                $rollFlagInfo = xphp_get_config('task','STRATEGY_ROLL_TYPE')['ON'];
                $rollInterval = $this->getRollInterval($strategy['rollInterval']);
                $rollEndTime = $strategy['endTime'];
            } else {  //不滚动
                $rollFlagInfo = xphp_get_config('task','STRATEGY_ROLL_TYPE')['OFF'];
                $rollInterval = 0;
                $rollEndTime = '';
            }
            $strArr[] = array(
                'strategy_group_uuid' => $strategygroupuuid,
                'mode' => $mode,
                'strategy_type' => $strategy['type'],
                'days' => $daysInfo,
                'start_time' => $strategy['startTime'],
                'roll_flag' => $rollFlagInfo,
                'roll_interval' => $rollInterval,
                'roll_end_time' => $rollEndTime,
                'global_id' =>  $globalID,
            );
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
}
