<?php

namespace app\v1\complete_machine_volcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VolcdpOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Index;
use app\v1\common\logic\Backup;
use xphp\BLLHandler;

class CmBackup extends Base
{
    /**
     * 获取客户端tree
     * @return array
     */
    public function getCompleteMachineVolcdpAgentTree()
    {
        $tree = array();
        $sql = "select ba.id, ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type,
                        ba.authorization_module,ba.online_flag,ba.net_model,ba.detail,
                        bag.group_uuid,bag.group_name,other_detail_hardware_info  
                from bd_agent ba 
                    left join bd_agent_group bag on ba.group_uuid = bag.group_uuid
                where ba.agent_type = ?";

        if (v1_auth_need_operation()) {
            // 不是全局观察者-查看并操作并且也不是admin，那么只能查看自身拥有的和关联用户的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'],'ba.agent_uuid');
            $sql .= " and ({$resourceUuidSql}) ";
        }

        $sql .= " order by ba.online_flag ";
        $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL']);
        $data = $this->dbSelect($sql, $sqlParams);
        $group = array();
        $agent = array();
        foreach ($data as $d) {
            if(!in_array($d['group_uuid'], $group)) {
                //查分组下的agent
                $sqlagent = "select agent_uuid from bd_agent where agent_type != 4 and group_uuid = ?";
                $dataagent = $this->dbSelect($sqlagent, array($d['group_uuid']));
                array_push($group,$d['group_uuid']);
                $tree[] = array(
                    "id" => $d['group_uuid'],
                    "pId" => 0,
                    "name" => $d['group_name'],
                    "title" => $d['group_name'],
                    "isParent" => true,
                    "nocheck" => true,
                    "open" => false,
                    "type" => 1,
                    "icon" => "./img/platform/flag.png",
                    "uuid" => $d['group_uuid'],
                    "eventtype" => "group",
                    "agentlist" => $dataagent[0],
                    "clickshow" => "false",
                    "checked" => false,
                );
            }
            $type_icon = "./img/platform/linux.png";
            if ($d['os_type'] == 'Windows') {
                $type_icon = "./img/platform/windows.png";
            }
            $agentUuid = $d['agent_uuid'];
            $otherDetailHardwareInfo = json_decode($d['other_detail_hardware_info']);
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
            } catch (Exception $e) {  // 错误捕获
                $nicList = [];
                $agentDefaultCachePath = "";
            }
            $tree[] = array(
                "id" => $d['group_uuid'].'_'.$d['agent_uuid'],
                "pId" => $d['group_uuid'],
                "name" => $hostName,
                "title" => $title,
                "isParent" => false,
                "uuid" => $agentUuid,
                "onlineFlag" => $onlineFlag,
                "nocheck" => false,
                "type" => 2,
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
                "other_detail_hardware_info" => $otherDetailHardwareInfo,
            );
        }
        //分组个数为1时，展开分组；多个分组时，不展开，避免客户端太多，看不到下面的分组
        if (count($group) == 1) {
            $tree[0]['open'] = true;
        }
        return $tree;
    }

    /**
     * 获取模块授权信息
     * @return boolean
     */
    public function verifyingCompleteMachineVolcdpAuthInfo($params)
    {
        $module = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];
        $authFunction = array();
        $authType = $params['type'];
        
        $authResult = v1_license_get_auth($authType);
        if($authResult['auth_type'] == 1 ){  //auth_type = 1是数量授权2是容量授权
            if($authResult['total'] ==-1){  //无限制
                $moduleAvailable = true;
                $authFunction = v1_license_get_func('f'); //获取功能授权配相关信息
            }else if($authResult['total'] > $authResult['used']){  //可用授权大于已用授权
                $moduleAvailable = true;
                $authFunction = v1_license_get_func('f'); //获取功能授权配相关信息
            }else{
                $moduleAvailable = false;
            }
        }elseif ($authResult['auth_type'] == 2){
            $moduleAvailable = true;
            $authFunction = v1_license_get_func('f'); //获取功能授权配相关信息
        }
        $authInfo = array(
            'auth_result' => $moduleAvailable,
            'auth_function' => $authFunction,
        );
        return $authInfo;
    }
    /**
     * 校验应急接管授权数量
     * @param mixed $params
     * @return void
     */
    public function verfiyEmergencyTakeoverAuthInfo($params)
    {
        $authFunction = array();
        $authType = $params['type'];
        $moduleAvailable = false;
        $authResult = v1_license_get_auth($authType);
        if($authResult['auth_type'] == 1 ){  //auth_type = 1是数量授权2是容量授权
             if($authResult['total'] ==-1){  //无限制
                $moduleAvailable = true;
                $authFunction = v1_license_get_func('f'); //获取功能授权配相关信息
            }else if($authResult['total'] > $authResult['used']){  //可用授权大于已用授权
                $moduleAvailable = true;
                $authFunction = v1_license_get_func('f'); //获取功能授权配相关信息
            }else{
                $moduleAvailable = false;
            }
        }
        $authInfo = array(
            'auth_result' => $moduleAvailable
        );
        return $authInfo;
    }

    /**
     * 更新客户端信息
     * @param array agent_uuid
     * @return array
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
     * 获取选择客户端对应的应用tree
     * @param $params array
     * @return array
     */
    public function getCompleteMachineVolcdpAppTree($params)
    {
        $agentUuid = $params['agent_uuid'];
        $agentName = $params['agent_name'];
        $agentIp = $params['agent_ip'];

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
        $appList = $this->getAppType($agentUuid, $appTree);//应用类型
        return $appList;
    }

    /**
     * @param $params
     * @return string
     */
    public function updateAppInfo($params)
    {
        $refreshType = $params['refresh_type'];
        $agentUuid = $params['agent_uuid'];
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
     * 获取客户端对应的网卡信息
     * @param array $params
     */
    public function getHostNetworkInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $sql = "select detail 
                from bd_agent 
                where agent_uuid = ?";
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
        $sql = "select detail 
                from bd_agent 
                where agent_uuid = ?";
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

    public function getNodeNetworkList($params){
        $agentuuid = $params['node_uuid'];
        $sql = "select ip, port, alias_name, network_order, network_uuid from bd_node_network where ip != '' and node_uuid = ?  order by network_order asc";
        $data = $this->dbSelect($sql, array($agentuuid));
        $list = array();
        foreach ($data as $d){
            $list[] = array(
                'ip' => $d['ip'],
                'port' => $d['port'],
                'alias_name' => $d['alias_name'],
                'network_order' => $d['network_order'],
                'network_uuid' => $d['network_uuid'],
            );
        }
        return $list;
    }
    /**
     * @method 获取主机已配置的应用，封装成tree
     * @parmas agentuuid,applist,...
     * @desc 根据已配置的主机应用UUID,组装成树形结构，且全部选中返回
     */
    public function checkStandbyApp($params){
        $agentUuid = $params['agent_uuid'];
        $appList = $params['app_list'];  //主机配置的监控应用uuid 集合
        $standbyHostUuid = $params['standby_host_uuid'];  //备机UUID

        $appInfo = array();       
        $sql = "select app_name,app_username,app_password,app_uuid,online_flag, app_type
               from bd_agent_app
               where agent_uuid = ? group by app_uuid";

        $sqlParams = array($standbyHostUuid); 
        $data = $this->dbSelect($sql, $sqlParams);
        if (!empty($data)) {
            foreach ($data as $d) {
                // $standbyAppUuid = $d['app_uuid'];
                $standbyAppName = $d['app_name'];
                $standbyAppType = $d['app_type'];
                $standbyAppUuid = $d['app_uuid'];
                $hostInfo = $this->getHostAppMapInfo($appList, $agentUuid, $standbyAppName,$standbyAppUuid  );
                if(empty($hostInfo)){
                    $appInfo = array();
                }else{
                    $appInfo[] = $hostInfo;
                }
            }
        }

        return $appInfo;
    }
    /**
     * 
     * 获取模块默认任务名
     * @return array | null
     */
    public function getDefaultTaskName($params){
        $taskName = html_entity_decode($params['task_name']);
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if (empty($data) && empty($data1)) {
                return array(
                    'task_name' => $taskName,
                );
            }
            $taskName = $oldTaskName;
        }
        return array(
            'task_name' => $oldTaskName,
        );
    }
    /**
     * 创建复制任务
     * @param array $params 请求参数
     * @return array | null
     */
    public function createBackupJob(array $params)
    {

        // 检查授权是否到期
        $this->licenseCheck();

        //全局策略uuid  (没有为空值)
        $taskName = htmlspecialchars_decode($params['task_name']);
        $this->paramsCheck($taskName);
        $isReplication = $params['is_replication'];
        if($isReplication){
            $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'];
        }else{
            $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        }
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $nodeUuid = $params['node_uuid'];
        if (!empty($params['task_uuid'])) {
            $opName = 'BD_TASK_OP_BACKUP_MODIFY';
            $nodeUuid = $params['old_node_uuid'];
        }
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $globalId =  '';
        $timeStrategyList = $this->groupBackupTimeList($params['tag_list'], $globalId);
        // var_dump(json_encode($timeStrategyList));
        $reserverStrategy = (new Backup())->groupReserverStrategy($params['high_info']['reserve']);
        $transportStrategy = (new Backup())->groupTransportStrategy($params['high_info']['transfer']);
        $storageStrategy = (new Backup())->groupStorageStrategy($params['high_info']['store']);
        $nodeInfo = (new BLLHandler())->groupBackupNodeInfo($params['high_info']['node']);
        $nodeUuid = $params['node_uuid'];
        $storageUuid = $params['storage_uuid'];
        $pfMsg = (new BLLHandler())->pfCreateBackupTaskMessage(
            $taskName,
            $moduleType,
            $timeStrategyList,
            $reserverStrategy,
            $transportStrategy,
            $storageStrategy,
            $nodeInfo
        );  //公共消息封装 
        $pfMsg['speed_limit_strategy_list'] = array();
        $pfMsg['speed_limit_strategy_list'] = (new Backup())->groupTaskSpeedList($params['speed_info'], $globalId);
        $pfMsg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speed_limit']);  // 全局限速策略

        if (!empty($params['task_uuid'])) {
            $pfMsg['task_uuid'] = $params['task_uuid'];
        }
        $pfMsg['task_type'] = $taskType;
        $pfMsg['node_uuid'] = $params['node_uuid'];
        $pfMsg['backup_server_ip'] = '';
        $pfMsg['safe_config_strategy'] = $params['safe_config_strategy'];
        $pfMsg['script_list'] = $params['script_list'];
        $pfMsg['ignore_resource_limiting_flag'] = $params['ignore_resource_limiting_flag'];
        $pfMsg['dev_type'] = $params['dev_type'];
        $pfMsg['all_disk_list'] = $params['all_disk_list'];
        $pfMsg['master_agent_uuid'] = $params['master_agent_uuid'];
        $pfMsg['backup_object'] = $params['backup_object'];
        $pfMsg['replication_object'] = $params['replication_object'];
        $pfMsg['takeover_object'] = $params['takeover_object'];
        $pfMsg['cache_config'] = $params['cache_config'];
        $pfMsg['high_pressure_strategy'] = $params['high_pressure_strategy'];
        $pfMsg['thread_num'] = $params['thread_num'];
        $pfMsg['storage_block_size'] = $params['storage_block_size'];
        $pfMsg['transport_block_size'] = $params['transport_block_size'];
        $pfMsg['full_backup'] = $params['full_backup'];
        $pfMsg['skip_bad_block'] = $params['skip_bad_block'];
        $pfMsg['resource_protect_config'] = $params['resource_protect_config'];

        $msg = json_encode($pfMsg);
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
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
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    private function groupBackupTimeList($params, $strategygroupuuid)
    {
        $msg = array();
        if (!empty($params['tag_info'])) {  //标签策略
            $msg = $this->groupEachTagstrategy(xphp_get_config('task', 'BACKUP_MODE')['TAG'], $params['tag_info'], $strategygroupuuid);
        }
        return $msg;
    }

    /**
     * 组合标签策略
     * @param int $mode
     * @param array $strategy
     * @return array
     */
    private function groupEachTagstrategy($mode, $strategyArray, $strategygroupuuid)
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
                'global_id' => 0,
            );
        }
        return $strArr;
    }

    /**
     * @method 解析主机应用
     * @desc 获取主机应 应用名与应用UUID的映射关系
     */
    private function getHostAppMapInfo($appList, $agentUuid, $standbyAppName,$standbyAppUuid)
    {
        $appMapList = array();
        for ($i = 0; $i < count($appList); $i++) {
            $appUuid = $appList[$i]['app_uuid'];
            $appType = $appList[$i]['app_type'];
            
            $sql = "select app_name,app_uuid,app_type from bd_agent_app 
                    where app_uuid = '{$appUuid}' and agent_uuid = '{$agentUuid}' and app_name = '{$standbyAppName}' and app_type = ?";
            $data = $this->dbSelect($sql,array($appType));
            if (!empty($data)) {
                $appMapList = array(
                    'master_app_uuid' => $appUuid,
                    'standby_app_uuid' => $standbyAppUuid
                );
            }
        }
        return $appMapList;
    }

    /**
     * 检查当前客户端是否有任务存在
     * -----当前仅现在是否有备份任务，后续会完善到当前客户端是否是否作为恢复和接管目标主机，且正在被使用的情况
     * @param string $agentUuids
     * @return array
     */
    private function taskExist($agentUuids)
    {
        // $sql = "select task.task_status,task.task_name 
        //         from bd_task task,cdp_vol_task vol_task 
        //         where vol_task.task_uuid = task.task_uuid  and (task.task_type = ? or task.task_type = ? or task.task_type = )  and vol_task.master_agent_uuid = ?  and task.delete_flag = ? ";
        $sql = "select task.task_name,task.task_status from bd_task as task 
	            left JOIN cdp_vol_task vol_task on vol_task.task_uuid = task.task_uuid 
	            left JOIN os_list on os_list.task_uuid = task.task_uuid
                where (task.task_type = ? or task.task_type =? or task.task_type=? ) and (vol_task.master_agent_uuid =? or os_list.agent_uuid = ? ) and task.delete_flag = ?";
        $backupTaskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $replactionTaskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'];
        $osBackupTaskType = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'];   
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql, array($backupTaskType,$replactionTaskType,$osBackupTaskType,$agentUuids, $agentUuids,$delFlag));
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
     * @param string $agentuuid
     * @return array
     */
    private function hostIsUsedTakeoverStandby($agentuuid)
    {
        $sqlTo = "select task.task_name 
                  from cdp_vol_task_takeover_info vt,bd_task task 
                  where takeover_standby_agent_uuid = ? 
                  and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo, array($agentuuid));
        if (!empty($dataTo)) {
            $isEnabled = true;
            $taskName = $dataTo[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array(
            'task_name' => $taskName,
            'is_enabled' => $isEnabled
        );
    }

    /**
     * 监测客户端是否作为镜像备机被使用
     * @param string $agentuuid
     * @return array
     */
    private function hostIsUsedHaStandby($agentuuid)
    {
        $sqlTo = "select task.task_name 
                  from cdp_vol_task vt,bd_task task 
                  where vt.standby_agent_uuid = ? 
                  and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo, array($agentuuid));
        if (!empty($dataTo)) {
            $isEnabled = true;
            $taskName = $dataTo[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array(
            'task_name' => $taskName,
            'is_enabled' => $isEnabled
        );
    }

    /**
     * 监测客户端是否作为回切目标机器使用
     * @param string $agentuuid
     * @return array
     */
    private function hostIsUsedFbTarget($agentuuid)
    {
        $sqlFa = "select task.task_name 
                  from cdp_vol_task_takeover_failback_info vt,bd_task task 
                  where vt.failback_target_agent_uuid = ? 
                  and vt.task_uuid = task.task_uuid";
        $dataFa = $this->dbSelect($sqlFa, array($agentuuid));
        if (!empty($dataFa)) {
            $isEnabled = true;
            $taskName = $dataFa[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array(
            'task_name' => $taskName,
            'is_enabled' => $isEnabled
        );
    }

    /**
     * 检测客户端是否作为恢复目标机器使用
     * @param string $agentuuid
     * @return array
     */
    private function hostIsUsedRecoveryTarget($agentuuid)
    {
        $sqlRe = "select task.task_name 
                  from cdp_vol_task vt,bd_task task 
                  where vt.recovery_target_agent_uuid =? 
                  and vt.task_uuid = task.task_uuid";

        $dataRe = $this->dbSelect($sqlRe, array($agentuuid));
        if (!empty($dataRe)) {
            $isEnabled = true;
            $taskName = $dataRe[0]['task_name'];
        } else {
            $isEnabled = false;
            $taskName = '';
        }
        return array(
            'task_name' => $taskName,
            'is_enabled' => $isEnabled
        );
    }

    /**
     * 根据条件组合客户端的名称
     * @param string $agentName
     * @param string $hostName
     * @param string $ip
     * @return string
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
     * 获取cdp剩余授权
     * @return array
     */
    private function getBdLicenseAboutVolcdp()
    {
        $sql = "select cdp_max,cdp_takeover_max_num from bd_license";
        $data = $this->dbSelect($sql);
        return $data[0];
    }

    /**
     * 获取客户端已扫描的应用类型
     * @param $agentUUID string
     * @param $appTree string
     * @return array
     */
    private function getAppType($agentUUID, $appTree)
    {
        $sql = "select distinct app_type,alias_name 
                from bd_agent_app 
                where agent_uuid = '{$agentUUID}' 
                group by app_type";
        $data = $this->dbSelect($sql);
        if (count($data) > 0) {
            foreach ($data as $d) {
                $appTypeValue = $d['app_type'];
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
     * @param $agentUUID string
     * @param $pId string
     * @param $appTypeValue int
     */
    private function getAppData($agentUUID, $pId, $appTypeValue)
    {
        $sql = "select app_name,app_username,app_password,app_type,app_uuid,online_flag 
                from bd_agent_app 
                where agent_uuid = ? 
                and app_type = ? 
                group by app_uuid";
        $data = $this->dbSelect($sql, array($agentUUID, $appTypeValue));
        $info = array();
        foreach ($data as $d) {
            $id = $pId . $d['app_uuid'];
            $chkDisabled = false;
            $nodeName = $d['app_name'];
            $nodeTitle = $d['app_name'];
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
                "icon" => "./img/cm_cdp/instance.svg",
                "clickshow" => false,
                'agent_uuid' => $agentUUID,
                "nodeType" => 'app',
                "online_flag" => $d['online_flag'],
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
     * 获取应用对应的卷信息
     * @param unknown $appuuid
     * @return array(mountPath,vol_uuid)
     */
    private function getAllModuleVolinfo($appuuid)
    {
        $sql = "select md.module_detail 
                from bd_agent_app app,cdp_vol_app_module md  
                where md.app_uuid = app.app_uuid 
                and app.app_uuid = ?";
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

    /**
     * 遍历获取应用对应的卷信息
     * @param unknown $obj1
     * @param unknown $obj2
     * @param unknown $obj3
     * @return array
     */
    private function getModuleVolinfo($obj1, $obj2, $obj3)
    {
        $volInfo = array();

        $objArry = array();
        $objArry[] = $obj1;
        $objArry[] = $obj2;
        $objArry[] = $obj3;

        for ($i = 0; $i < count($objArry); $i++) {
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
     * @param $appVolInfo array
     * @param $agentuuid string
     * @return array
     */
    private function getVolRelationDosVol($appVolInfo, $agentuuid)
    {
        $bootVolArray = array();
        foreach ($appVolInfo as $d) {
            $mountVoluuid = $d['vol_uuid'];
            $sql = "select vol.detail 
                    from bd_agent_vol vol,bd_agent_disk disk 
                    where vol.disk_uuid = disk.disk_uuid 
                    and disk.agent_uuid =? 
                    and vol.vol_uuid = ?";
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
                $bootSql = "select vol.mount_point,vol.vol_uuid 
                            from bd_agent_vol vol,bd_agent_disk disk 
                            where vol.disk_uuid = disk.disk_uuid 
                            and disk.agent_uuid = ? 
                            and is_boot = ? ";
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
     * @param string $pId
     * @param string $appUUID
     * @param string $appTypeValue
     * @param boolean $parentNodeOnlineFlag
     */
    private function getAppModuleInfo($pId, $appUUID, $appTypeValue, $parentNodeOnlineFlag)
    {
        $sql = "select id,app_uuid,module_name,module_detail 
                from cdp_vol_app_module 
                where app_uuid = '{$appUUID}'";
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
     * 获取客户端对应的所有应用UUID
     * @param $agentUuid string
     * @return array $appList
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