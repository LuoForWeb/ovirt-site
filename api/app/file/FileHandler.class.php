<?php
/*******************************************
 ** 文件备份处理类
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-8-07 下午03:20:22
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
require_once XPHP_PATH . 'utils/BLLHandler.class.php';
class FileHandler extends BLLHandler
{

    /**
     * 创建备份任务
     * @param unknown $params
     */
    public function createFsBackupJob($params)
    {
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $utils = Xphp::instance('Utils');
        $expireDate = $utils->getExpireDays();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_LICENSE_AUTH_INFO_EXPIRED'] , "warning"));
        }
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        $nodeHandler = Xphp::instance('NodeHandler');
        //租户内检查可用数量是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['FS'], $params['srcInfo']['agentList'], "");
        }
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategygroupuuid'];
        $this->paramsCheck($task_name);
        $module_type = Xphp::$_config['MODULE_TYPE']['FS'];
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve'], $strategygroupuuid);
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'], $strategygroupuuid);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $params['backupInfo']['type']
        );
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupFS($params['srcInfo']['fileInfo']);
        $pfMsg['group_list'] = $this->getGroupList($params['srcInfo']['groupList'], $params['srcInfo']['fileInfo']);
        $pfMsg['snap_shot_flag'] = $params['highInfo']['newstr']['silentsnapshotcheck'] ? 1 : 2; //快照
        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backupThreadNum']; //传输线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scanThreadNum']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scanFileNum']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcard_list']); //通配符
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略配置
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['file_archive_flag'] = $params['highInfo']['file_archive']; //归档
        $pfMsg['skip_file_alarm_flag'] = $params['highInfo']['skip_file_alarm_flag'] ? 1 : 2; //跳过文件告警开关
        $pfMsg['permission_operate_flag'] = $params['highInfo']['permission_operate_flag'] ? 1 : 2; //文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num']; //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio']; //跳过文件告警比例
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);

        //得到安全策略
        $pfMsg['safe_config_strategy'] = $jobHandler->groupSafeConfigStrategy($params['safe_strategy'], $params['highInfo']['node']['storageuuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['submodule_type'] = Xphp::$_config['SUBMODULE_TYPE']['FS'];
        //按代理分组批量创建文件备份任务
        $agentList = $params['srcInfo']['agentList'];
        if ($pfMsg['node_uuid']) {
            $nodeuuid = $nodeInfo['node_uuid'];
        } else {
            $nodeuuid = $nodeHandler->getMasterNodeUuid();
        }
        if (!empty($agentList)) {
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
            sleep(1);   //睡一秒
            //更新fs_task的group_uuid
            if (!empty($params['srcInfo']['groupuuid'])) {
                $this->updateTaskAgentGroup($task_name, $params['srcInfo']['groupuuid']);
            }
        } else {
            //单个代理创建任务
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        }


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
     * 插入时间策略
     * @param int $strategyID 策略ID
     * @param int $modeType
     * @param array $strategyInfo
     */
    private function insertTimeStrategy($strategyID, $modeType, $strategyInfo)
    {
        $utils = Xphp::instance('Utils');
        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
        $rollFlag = $utils->parseBoolToFlag($strategyInfo['rollFlag']);
        $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array(
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
    private function getTimeStrategyDaysStr($days)
    {
        $str = '';
        if (empty($days)) {
            return $str;
        }
        $str = implode('', $days);
        return $str;
    }

    /**
     * 修改备份任务
     * @param unknown $params
     * @return string
     */
    public function editFsBackupJob($params)
    {
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $utils = Xphp::instance('Utils');
        $expireDate = $utils->getExpireDays();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_LICENSE_AUTH_INFO_EXPIRED'] , "warning"));
        }
        $task_name = htmlspecialchars_decode($params['taskName']);
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        $nodeHandler = Xphp::instance('NodeHandler');
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategygroupuuid'];
        $this->paramsCheck($task_name);
        $module_type = Xphp::$_config['MODULE_TYPE']['FS'];
        //租户内检查虚拟机个数是否超过授权个数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['FS'], $params['srcInfo']['agentList'], $params['taskuuid']);
        }
           // 传输网络格式化
           $params['highInfo']['transfer']['network'] = $this->buildNetworkUuid(
            $params['highInfo']['node']['nodeuuid'] ?: '',
            $params['highInfo']['transfer']['network'] ?: ''
        );
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve'], $strategygroupuuid);
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'], $strategygroupuuid);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $params['backupInfo']['type']
        );
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['BACKUP'];
        $pfMsg['task_uuid'] = $params['taskuuid'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupFS($params['srcInfo']['fileInfo']);
        $pfMsg['group_list'] = $this->getGroupList($params['srcInfo']['groupList'], $params['srcInfo']['fileInfo']);
        $pfMsg['snap_shot_flag'] = $params['highInfo']['newstr']['silentsnapshotcheck'] ? 1 : 2; //快照
        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backupThreadNum']; //传输线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scanThreadNum']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scanFileNum']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcard_list']); //通配符
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);

        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['file_archive_flag'] = $params['highInfo']['file_archive']; //归档
        $pfMsg['skip_file_alarm_flag'] = $params['highInfo']['skip_file_alarm_flag'] ? 1 : 2; //跳过文件告警开关
        $pfMsg['permission_operate_flag'] = $params['highInfo']['permission_operate_flag'] ? 1 : 2; //文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num']; //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio']; //跳过文件告警比例
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $jobHandler->groupSafeConfigStrategy($params['safe_strategy'], $params['highInfo']['node']['storageuuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['submodule_type'] = Xphp::$_config['SUBMODULE_TYPE']['FS'];
        //按代理分组批量创建文件备份任务
        $agentList = $params['srcInfo']['agentList'];
        if ($pfMsg['node_uuid']) {
            $nodeuuid = $nodeInfo['node_uuid'];
        } else {
            $nodeuuid = $nodeHandler->getMasterNodeUuid();
        }
        if (!empty($agentList)) {
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
            sleep(1);   //睡一秒
            //更新fs_task的group_uuid
            if (!empty($params['srcInfo']['groupuuid'])) {
                $this->updateTaskAgentGroup($task_name, $params['srcInfo']['groupuuid']);
            }
        } else {
            //单个代理创建任务
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        }


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
     * 得到修改文件备份任务客户端树
     * @param unknown $params
     */
    public function getBackupTreeOldInfo($params)
    {
        $taskuuid = $params['taskuuid'];
        $agentlist = $params['agentlist'];
        $grouplist = $params['grouplist'];
        $agentHandler = Xphp::instance('AgentHandler');
        $utils = Xphp::instance('Utils');
        //增加文件代理分组节点
        $agentGroupList = $agentHandler->getAgentGroupBackupTree();
        $agentGroupList = $utils->object_array(json_decode($agentGroupList));
        if (!empty($agentGroupList)) {
            foreach ($agentGroupList as $key => $node) {
                if ($node["eventtype"] == "group") { //分组
                    foreach ($grouplist as $g) {
                        if ($g == $node["id"]) { //如果分组被选中
                            $agentGroupList[$key]["checked"] = true;
                            $agentGroupList[$key]["open"] = true;
                        }
                    }
                } else { //客户端
                    foreach ($agentlist as $a) {
                        if ($a == $node["uuid"]) {
                            $agentGroupList[$key]["checked"] = true;
                        }
                    }
                }
            }
        }
        return json_encode($agentGroupList);
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
     * 得到备份的文件列表
     * @param unknown $filelists
     */
    private function groupBackupFS($filelists)
    {
        $fileList = array();
        foreach ($filelists as $file) {
            $fileList[] = array(
                'path_type' => strval($file[0]),    //文件类型
                'path_name' => htmlspecialchars_decode($file[1]),             //文件路径
                'agent_uuid' => $file[2],
                'group_uuid' => $file[3],
                'code_type' => $file[4], //编码类型
            );
        }
        return $fileList;
    }
    /**
     * 得到客户端名字和IP
     * @param unknown $agentlists
     */
    public function getAgentName($agentlists)
    {
        $agentInfo = array();
        foreach ($agentlists as $agent) {
            $sql = "select agent_name, ip from bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($agent));
            $data = $data[0]["agent_name"] . '(' . $data[0]["ip"] . ')';
            array_push($agentInfo, $data);
        }
        return json_encode($agentInfo);
    }
    /**
     * 得到备份的客户端列表
     * @param unknown $agentlists
     */
    private function getGroupList($grouplists, $fileinfo)
    {
        $data = array();
        foreach ($grouplists as $g) {
            $agentList = array();
            foreach ($fileinfo as $f) {
                if ($f[3] == $g && !in_array($f[2], $agentList)) { //groupuuid相同
                    array_push($agentList, $f[2]);
                }
            }
            $data[] = array(
                'group_uuid' => $g,
                'agent_uuid' => $agentList,
            );
        }
        return $data;
    }
    /**
     * 得到备份的通配符相关信息
     * @param unknown $wildcard
     */
    private function getWildCardList($wildcard)
    {
        $data = array();
        foreach ($wildcard as $w) {
            // if($w[2]!=0){
            $data[] = array(
                'agent_uuid' => $w[0],
                'wildcard' => $w[1],
                'wildcard_mode' => $w[2],
            );
            // }
        }
        return $data;
    }

    /**
     * 得到文件备份任务名
     * @param unknown $params
     */
    public function getFileBackupTaskName($params)
    {
        return $this->getValidTaskName(Xphp::$_lang['WEB_FILE_BACKUP_TASKNAME']);
    }

    /**
     * 得到文件恢复任务名
     * @param unknown $params
     */
    public function getFileRecoverTaskName($params)
    {
        return $this->getValidTaskName(Xphp::$_lang['WEB_FILE_RECOVER_TASKNAME']);
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    private function getValidTaskName($taskName, $groupname = "")
    {
        $oldTaskName = $taskName;
        for($i=1; $i<1000; $i++){
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if(empty($data) && empty($data1)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }
    /**
     * 获取文件备份
     * @return array
     */
    public function getRecovHostTree($params = array())
    {
        $sql = "select ba.id, ba.agent_uuid,ba.os_type, ba.agent_name, ba.hostname,ba.net_model, ba.ip, ba.os_type,online_flag, ba.authorization_module,bag.group_uuid, bag.group_name, bag.detail from bd_agent ba left join bd_agent_group bag on ba.group_uuid = bag.group_uuid where agent_type not in (3, 4, 5)";
        if (!in_array($_SESSION['userLevel'], [1, 2, 3])) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 10);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $nodes = array();
                return json_encode($nodes);
            } else {
                // $uuidArr 是一个一维数组  organization_uuid in
                $agentUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and ba.agent_uuid in ($agentUuidsIn)";
            }
        }
        if (!empty($params['content'])) {
            $search = $params['content'];
            $sql .= " and (ba.agent_name like '%" . $search . "%' or ba.hostname like '%" . $search . "%' or ba.ip like '%" . $search . "%' or bag.group_name like '%" . $search . "%')";
        }
        $data = $this->dbSelect($sql, array());
        $systemHandler = Xphp::instance('SystemHandler');
        $nodes = array();
        $group = array();
        $agent = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                //分组
                if (!in_array($d['group_uuid'], $group)) {
                    //查分组下的agent
                    $sqlagent = "select agent_uuid from bd_agent where agent_type != 4 and group_uuid = ?";
                    $dataagent = $this->dbSelect($sqlagent, array($d['group_uuid']));
                    array_push($group, $d['group_uuid']);
                    $nodes[] = array(
                        "id" => $d['group_uuid'],
                        "pId" => 0,
                        "name" => $d['group_name'],
                        "title" => $d['group_name'],
                        "isParent" => true,
                        "nocheck" => true,
                        "open" => true,
                        "type" => 1,
                        "icon" => "./img/platform/flag.png",
                        "uuid" => $d['group_uuid'],
                        "eventtype" => "group",
                        "agentlist" => $dataagent[0],
                    );

                }
                //客户端
                $authorization = $systemHandler->checkModuleValid($d['authorization_module'], 'file');
                if (!$authorization && $d['online_flag'] != 1) {
                    $authorization_online_info = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')';
                } else if ($authorization && $d['online_flag'] != 1) {
                    $authorization_online_info = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')';
                } else if (!$authorization && $d['online_flag'] == 1) {
                    $authorization_online_info = '(' . Xphp::$_lang['WEB_SYSTEM_LISENCE_UNAUTHORIZED'] . ')';
                } else {
                    $authorization_online_info = '';
                }
                if (!in_array($d['agent_uuid'], $agent)) {
                    array_push($agent, $d['agent_uuid']);
                    if (!empty($d['agent_name']) && $d['agent_name'] != $d['ip']) {
                        $name = $d['agent_name'] . "(" . $d['ip'] . ")";
                    } else {
                        $name = $d['hostname'] . "(" . $d['ip'] . ")";
                    }
                    $nodes[] = array(
                        "id" => $d['group_uuid'] . '_' . $d['agent_uuid'],
                        "pId" => $d['group_uuid'],
                        "name" => $authorization_online_info . $name,
                        "title" => $d['ip'],
                        "isParent" => false,
                        "uuid" => $d['agent_uuid'],
                        "nocheck" => false,
                        "type" => 2,
                        "icon" => $d['os_type'] == "Windows" ? "./img/os/Windows.png" : "./img/os/Linux.png",
                        "eventtype" => "agent",
                        "ostype" => $d['os_type'],
                        "chkDisabled" => $authorization_online_info == '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')' ? true : false,
                        "net_model" => intval($d['net_model'])
                    );
                }
            }
        }

        return json_encode($nodes);
    }

    /**
     * 创建恢复任务
     * @param unknown $params
     */
    public function createRecoverJob($params)
    {
        $task_name = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($task_name);
        $vmHandler = Xphp::instance('Vmhandler');
        $module_type = Xphp::$_config['MODULE_TYPE']['FS'];
        $recovery_position = intval($params['recoverInfo']['pathtype']);
        $recovery_time_type = intval($params['typeInfo']['type']);
        $time_strategy_list = $this->groupRecoverTimeList($params['typeInfo']);
        $transport_strategy = $this->groupTransportStrategy($params['typeInfo']['high']['trasfer']);

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
        $pfMSg['recovery_level'] = 1;
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverFileList($params['pointInfo'], $params['recoverInfo']);
        $pfMSg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], "");
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        $pfMSg['password'] = $params['recoverInfo']['password'];
        $pfMSg['thread_num'] = $params['thread_num'];
        $pfMSg['new_dir_create'] = $params['recoverInfo']['new_dir_create'];
        //是否是跨平台传输
        $pfMSg['cross_platform_transform'] = $params['recoverInfo']['cross_platform_transform'];
        $submodule_type = intval($params['pointInfo']['type']);
        $pfMSg['distinct_flag'] = intval($params['distinct_flag']);
        //目录树恢复
        $pfMSg['dir_tree_recovery_flag'] = intval($params['highInfo']['dir_tree_recovery_flag']);
        //同名文件处理
        $pfMSg['same_file_strategy'] = intval($params['highInfo']['same_file_strategy']);
        //无效快捷方式清理
        $pfMSg['link_file_pass_flag'] = intval($params['highInfo']['link_file_pass_flag']);
        //文件权限恢复
        $pfMSg['permission_operate_flag'] = intval($params['highInfo']['permission_operate_flag']);
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['pointUUID']);
        $msg = json_encode($pfMSg);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
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
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName)
    {
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql, array($taskName, Xphp::$_config['MODULE_TYPE']['FS'], Xphp::$_config['TASKTYPE']['RECOVERY']));
        if (!$data)
            return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'uuid' => $data[0]['task_uuid'],
            'module' => $data[0]['module_type'],
            'subModule' => 0,
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
     * 得到恢复的文件列表信息
     * @param array $pointInfo
     *          agentUUID   恢复源代理UUID
     *          pointUUID   恢复时间点
     *          fileInfo    array
     *              [类型,路径,名字,MD5high, MD5low]
     * @param array $recoverInfo
     *          type        恢复类型    原机1/异机2
     *          agentUUID   恢复目标UUID    原机就是原机的UUID,异机就是异机的UUID
     *          pathtype    恢复路径类型      原路径1/新路径2
     *          path        恢复新路径
     * @return array
     */
    private function getRecoverFileList($pointInfo, $recoverInfo)
    {
        $fileInfo = $pointInfo['fileInfo'];
        $files = array();
        foreach ($fileInfo as $f) {
            $pathtype = intval($recoverInfo['pathtype']);
            $newRootPath = '';
            if ($pathtype == Xphp::$_config['FLAG']['UNSET']) {
                //异机恢复
                $newRootPath = $recoverInfo['path'];
                $agentUUID = $recoverInfo['agentUUID'];
            }
            $files[] = array(
                'agentUUID' => $recoverInfo['agentUUID'],
                'path_type' => intval($f[0]),
                'path_name' => $f[1],
                'new_root_path' => $newRootPath,
                'recovery_timepoint_uuid' => $pointInfo['pointUUID'],
                'md5_high' => $f[3],
                'md5_low' => $f[4],
                'code_type' => $recoverInfo['code_type']
            );
        }
        return $files;
    }

    /**
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    private function groupBackupTimeList($params, $strategygroupuuid)
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
            if (!empty($params['pincrInfo'])) {
                //永久增量（mode==增量备份）
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['pincrInfo'], $strategygroupuuid);
            }
        } else if ('oncetime' == $params['type']) {
            //一次性备份
            $strategy = array('startTime' => $params['datetime']);

            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            //获取一次性备份时间
            $taskCreateTime = strtotime($params['datetime']);
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_BACKUP_TIME'], Xphp::$_lang['WEB_DB_BACKUP_TIME_TIPS'], 'warning'));
            }

            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
            $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $strategy);
        } else if ('manual' == $params['type']) {
            $msg = [];
        }

        return $msg;
    }

    /**
     * 组合恢复时间策略
     * @param array $params
     * @return array
     */
    private function groupRecoverTimeList($params)
    {
        $timeList = array();
        if (Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == intval($params['type'])) {
            //立即恢复
            return $timeList;
        } elseif (Xphp::$_config['RECOVERY_TIME_TYPE']['STRATEGY'] == intval($params['type'])) {
            //按时间策略恢复
            $timeList[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['strategy']);
            return $timeList;
        }
    }

    /**
     * 组合每一个时间策略
     * @param int $mode         完全1/增量2/差异3/日志4/标签5
     * @param array $strategy
     * @return array
     */
    private function groupEachTimestrategy($mode, $strategy,$strategygroupuuid ='')
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
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['full_backup_compensation_flag'] = $utils->parseBoolToFlag($strategy['full_backup_compensation_flag']);
        $strArr['days'] = implode("", is_array($strategy['days']) ? $strategy['days'] : []) . $strategy['frequency'];
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
    private function groupReserverStrategy($reserve)
    {
        $this->paramsCheck($reserve);
        $strArr = array(
            'enable_flag' => $reserve['enable_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'strategy_mode' => intval($reserve['strategyMode']) ? intval($reserve['strategyMode']) : 0,
            'strategy_type' => intval($reserve['type']),
            'number' => intval($reserve['value']),
            'auto_archive_flag' => $reserve['archive'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
        );
        return $strArr;
    }

    /**
     * 组合传输策略
     * @param array $transport  传输策略信息
     *  @param  bool encrypt    加密
     *  @param  bool compress   压缩
     *  @param  bool speedFlag  限速
     *  @param  int  speed
     * @return array
     */
    private function groupTransportStrategy($transport)
    {
        $strArr = array(
            'encrypt_flag' => $transport['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $transport['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'speed_limit_flag' => $transport['speedFlag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'max_speed' => intval($transport['speed']),
            'network_uuid' => !empty($transport['network']) ? $transport['network'] : "", //传输网络
            'network_pool_uuid' => !empty($transport['network_pool_uuid']) ? $transport['network_pool_uuid'] : "", //传输网络资源池
            'compress_method' => $transport['compress_method'] ? $transport['compress_method'] : 0,
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
    private function groupStorageStrategy($storage)
    {
        $strArr = array(
            'deduplication_flag' => $storage['deduplication'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'blocksize' => intval($storage['blocksize']),
            'encrypt_flag' => $storage['dataencrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'password_auto_flag' => $storage['password_auto_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'password' => $storage['password'],
            'compress_flag' => $storage['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_method' => $storage['compress_method'] ? $storage['compress_method'] : 0,
            'encrypt_method' => intval($storage['encrypt_method']),
        );
        return $strArr;
    }

    /**
     * 得到代理端文件列表
     * @param array $params
     *      start  开始位置
     *      limit  查找个数
     *      searchFileName 从哪个文件名开始查找
     *      dir            进入目录的目录名
     *      三种情况
     *          1.初始展示[0, N, '', '']
     *          2.更多信息[N, N+M, searchFileName, '']
     *          3.进入目录[0, N, '', dir]
     * @return array
     */
    public function getFileDir($params)
    {
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
//         if(count($params) != 7){
//             exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
//         }
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $pid = $params['pid'];          //父节点ID
        $agentUUID = $params['agentuuid'];
        $groupUUID = $params['groupuuid'];
        $this->paramsCheck($agentUUID);
        $code_type = 2;
        if (!empty($params['code_type'])) {
            $code_type = $params['code_type']; //编码类型
        }
        $msg = array(
            'agent_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $code_type,
        );
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $mbResult['msg']['pid'] = $pid;
        $mbResult['msg']['groupUUID'] = $groupUUID;
        return $mbResult;
    }

    // 代理端文件树
    public function getFileDirTree($params)
    {
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $allresult = $this->getFileDir($params);
        $editFlag = $params['editFlag'];    //修改标记
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];
        $applyPathList = $params['applyPathList']; //用于应用到其他客户端
        $filelist = array(); //用于保存修改文件列表
        //获取当前代理端文件备份路径列表
        if ($editFlag) {
            if (!empty($applyPathList)) {
                $data = $applyPathList;
            } else {
                $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
                $data = $this->dbSelect($sql, array($agentuuid, $taskuuid));
            }
            foreach ($data as $d) {
                if ($d['path_type'] == 1 || $d['path_type'] == 2) { //文件或文件夹
                    //截取文件信息
                    $info = explode('/', $d['path_name']);
                    $path = "";
                    //获取已选择的文件信息列表
                    foreach ($info as $key => $i) {
                        if ($key == count($info) - 1) { //不是路径最后一层就拼接 /
                            if ($d['path_type'] == 1) { //是最后一层判断是否是文件，文件不用在最后拼 /
                                $path .= $i;
                            } else {
                                continue;
                            }
                        } else {
                            $path .= $i . "/";
                        }
                        if (!in_array($path, $filelist)) {
                            $filelist[] = $path;
                        }
                    }
                } else { //磁盘
                    if (!in_array($d['path_name'], $filelist)) {
                        $filelist[] = $d['path_name'];
                    }
                }
            }
        }
        //         $list = array('c','c/1','c/1/2', 'f','f/1','f/1/2')
        $result = $allresult['result'];
        $NodeOpcode = Xphp::instance('NodeOpcode');
        $operate = $NodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finishFlag" => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "searchIndex" => $result['current_next_index'],   //未完成时,下一个开始位置
            "searchFilename" => $result['search_file_name'],  //未完成时,下一个开始名字
        );
        $fileNodes = array();

        //$list0 = array(D,E,F); 长度
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            $checked = false;
            $open = false;
            //检查节点是否在修改列表里
            if (in_array($d['item_path'], $filelist)) {
                $checked = true;
                //从修改文件列表中 排除已经选中的节点
                foreach ($filelist as $key => $file) {
                    if ($file == $d['item_path']) {
                        unset($filelist[$key]);
                    }
                }
            }
            //检查节点是否需要展开
            $flag = false;
            foreach ($filelist as $l) {
                //strpos返回第二个字符串在第一个字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
                if (strpos($l, $d['item_path']) !== false) {
                    $flag = true;
                    $open = true;
                }
            }
            $fileNodes[] = array(
                "id" => $d['item_path'],
                "pId" => 0,
                "name" => $filename,      //文件名或者磁盘名
                "title" => $filename,
                "isParent" => $this->getBoolType($d['item_type']),
                "open" => $open,
                "uuid" => $agentuuid,
                "nocheck" => false,
                "type" => $d['item_type'], //1文件 2 文件夹 3 磁盘
                "icon" => "./img/fs/cipan.png",
                "filepath" => $d['item_path'],
                "groupuuid" => $result['groupUUID'],
                "checked" => $checked,
                "code_type" => $d['code_type'],
            );
            //获取要展开节点的子节点
            if ($checked && $d['item_type'] != 1) {
                if (!$flag)
                    continue;
                //获取需要展开的节点和加载更多节点
                $params = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $filename,
                    'dir' => $d['item_path'],
                    'agentuuid' => $agentuuid,
                    'groupuuid' => $result['groupUUID'],
                    'pid' => $d['item_path']

                );
                //展开子节点
                $sonList = $this->getOnLoadTree($filelist, $params, $d['item_path']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }

        }
        //如果list0长度不为空 继续加载更多
        // $list['filelist'] = $fileList;
        $list['fileNodes'] = $fileNodes;
        return json_encode($list);
    }

    /**
     * 递归获取加载子节点
     * @param array $list
     * @param unknown $params
     * @param unknown $node
     * @return array|unknown[]
     */
    private function getOnLoadTree($list, $params, $path)
    {
        $fileNodes = array();
        //获取子节点
        $data = $this->getFileDirSonTree($params);
        $data = json_decode($data, true);
        $fileData = $data['fileNodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, "/");
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, "/"));
                $str = substr($str, 0, strripos($str, "/"));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, "/"));
            }
            $str .= "/";
            if ($str == $path) {
                $info[] = $l;
            }
        }
        //获取展开子节点
        foreach ($fileData as $key => $d) {
            $checked = false;
            if (in_array($d['filepath'], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$list
                foreach ($list as $key => $file) {
                    if ($file == $d['filepath']) {
                        unset($list[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($info[$key]);
                    }
                }

            }

            if (count($info) != 0 && $d['more'])
                continue;
            //检查节点是否需要展开
            $flag = false;
            foreach ($list as $l) {
                if (strpos($l, $d['filepath']) !== false) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                //判判断$list存在父节点是c/1/2
                if (!$flag)
                    continue;
                $data = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'agentuuid' => $params['agentuuid'],
                    'groupuuid' => $params['groupuuid'],
                    'pid' => $d['filepath'],
                    'code_type' => $d['code_type'],
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }

        }
        //获取加载更多
        if (count($info) != 0 && $fileData[count($fileData) - 1]['more']) {
            //递归加载更多
            $data = array(
                'start' => $fileData[count($fileData) - 1]['next_index'],
                'limit' => 40,
                'filename' => $fileData[count($fileData) - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agentuuid' => $params['agentuuid'],
                'groupuuid' => $params['groupuuid'],
                'pid' => $params['dir']
            );
            $moreNodes = $this->getMoreData($list, $data);
            $fileNodes = array_merge($fileNodes, $moreNodes);

        }

        return $fileNodes;
    }


    /**
     * 递归获取加载更多
     * @param array $list
     * @param unknown $params
     * @return array
     */
    private function getMoreData($list, $params)
    {
        $fileNodes = array();
        $moreData = //获取加载更多的节点
            $data = $this->getFileDirSonTree($params);
        $data = json_decode($data, true);
        $moreData = $data['fileNodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, "/");
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, "/"));
                $str = substr($str, 0, strripos($str, "/"));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, "/"));
            }
            $str .= "/";
            if ($str == $params['dir']) {
                $info[] = $l;
            }
        }
        foreach ($moreData as $key => $d) {
            $checked = false;
            if (in_array($d['filepath'], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$info
                foreach ($list as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($list[$key]);
                    }
                }
                foreach ($info as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($info[$key]);
                    }
                }

            }
            if (count($list) != 0 && $d['more'])
                continue;
            //检查节点是否需要展开
            $flag = false;
            foreach ($list as $l) {
                if (strpos($l, $d['filepath']) !== false) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                //判判断$list存在父节点是c/1/2
                if (!$flag)
                    continue;
                $data = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'agentuuid' => $params['agentuuid'],
                    'groupuuid' => $params['groupuuid'],
                    'pid' => $d['filepath']
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        $count = count($moreData);
        if ($moreData[$count - 1]['more'] && count($info) != 0) {
            //加载更多
            $data = array(
                'start' => $moreData[$count - 1]['next_index'],   //依次累加
                'limit' => 40,
                'filename' => $moreData[$count - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agentuuid' => $params['agentuuid'],
                'groupuuid' => $params['groupuuid'],
                'pid' => $params['dir'],
            );
            $moreNodes = $this->getMoreData($list, $data);
            $fileNodes = array_merge($fileNodes, $moreNodes);

        }

        return $fileNodes;
    }

    // 代理端文件子树
    public function getFileDirSonTree($params)
    {
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $allresult = $this->getFileDir($params);
        $result = $allresult['result'];
        $NodeOpcode = Xphp::instance('NodeOpcode');
        $operate = $NodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finishFlag" => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "searchIndex" => $result['current_next_index'],   //未完成时,下一个开始位置
            "searchFilename" => $result['search_file_name'],  //未完成时,下一个开始名字
        );
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            if (!empty($params['dir'])) {
                // 截取字符串第一个/后面所有的内容
                // if($d['item_type']==1) {
                //     $pid = substr($d['item_path'],0, strrpos($d['item_path'],'/'));
                // }else {
                //     $pid = substr($d['item_path'],0, strrpos($d['item_path'],'/'));
                //     $pid = substr($pid,0, strrpos($pid,'/'));
                // }
                $fileNodes[] = array(
                    "id" => $d['item_path'],
                    "pId" => $result['pid'] == "" ? 0 : $result['pid'],
                    "name" => $filename,      //文件名或者磁盘名
                    "title" => $filename,
                    "isParent" => $this->getBoolType($d['item_type']),
                    "open" => false,
                    "uuid" => $params['agentuuid'],
                    "nocheck" => false,
                    "type" => $d['item_type'], //1文件 2 文件夹 3 磁盘
                    "icon" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                    "iconOpen" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjiaopen.png",
                    "iconClose" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                    "filepath" => $d['item_path'],
                    "more" => false,
                    "groupuuid" => $result['groupUUID'],
                    "code_type" => $d['code_type'],
                );
            }
        }
        if (intval($result['is_search_finish']) == Xphp::$_config['FLAG']['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                // "id" => $pid==0?0:$pid.'/',
                "pId" => $result['pid'],
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                // "isParent" => false,
                // "open" => false,
                "uuid" => $params['agentuuid'],
                "nocheck" => true,
                "more" => true,
                "next_index" => $result['current_next_index'],       //从哪个位置开始加载
                "search_file_name" => $result['search_file_name'],          //从哪个目录开始加载
                "dir_path" => $params['dir'],
                "groupuuid" => $result['groupUUID'],                                //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            $fileNodes[] = $more;
        }
        $list['fileNodes'] = $fileNodes;

        return json_encode($list);
    }

    /**
     * 得到代理端文件目录
     * @param array $params
     *      start  开始位置
     *      limit  查找个数
     *      searchFileName 从哪个文件名开始查找
     *      dir            进入目录的目录名
     *      三种情况
     *          1.初始展示[0, N, '', '']
     *          2.更多信息[N, N+M, searchFileName, '']
     *          3.进入目录[0, N, '', dir]
     * @return string
     */
    public function getAgentFileDir($params)
    {
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        if (count($params) != 5) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
        }
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agentuuid'];
        $this->paramsCheck($agentUUID);
        $msg = array(
            'agent_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
        );
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $result = $mbResult['result'];
        $NodeOpcode = Xphp::instance('NodeOpcode');
        $operate = $NodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $list = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finishFlag" => $data['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "searchIndex" => $data['current_next_index'],   //未完成时,下一个开始位置
            "searchFilename" => $data['search_file_name'],  //未完成时,下一个开始名字
        );
        $fileList = array();
        $utils = Xphp::instance('Utils');
        foreach ($data['item_list'] as $d) {
            $filename = $d['item_name'];
            $filesize = $utils->calSize($d['file_size']);
            $fileList[] = array(
                $filename,      //文件名
                $filesize,      //文件大小
                array(
                    "path" => $d['item_path'],                                                          //路径
                    "type" => $this->getFileType($d['item_type'], $filename),                           //文件类型
                    "btype" => $d['item_type'],                                                         //从后台获取的文件类型
                    "isfile" => $d['item_type'] == Xphp::$_config['FILETYPE']['FILE'] ? true : false,   //是否是文件
                    "sclass" => $this->getFileClassName($d['item_type'], $filename, 's'),                //显示类型
                ),
            );
        }
        $list['filelist'] = $fileList;
        return json_encode($list);
    }

    /**
     * 得到代理端文件类型
     * @param int $filetype
     * @param string $filename
     */
    public function getFileType($filetype, $filename)
    {
        $filetypeConf = Xphp::$_config['FILETYPE'];
        $fileClass = "unknown";
        switch ($filetype) {
            case $filetypeConf['FILE']:
                //TODO加入文件类型
                $fileClass = "file";
                break;
            case $filetypeConf['DIRECTORY']:
                $fileClass = "directory";
                break;
            case $filetypeConf['FIX_DRIVER']:
                $fileClass = "fixdriver";
                break;
            case $filetypeConf['REMOVABLE']:
                $fileClass = "removable";
                break;
            case $filetypeConf['REMOTE']:
                $fileClass = "remote";
                break;
            default:
                $fileClass = "unknown";
                break;
        }
        return $fileClass;
    }

    /**
     * 得到文件别分时间点树(灾备中心/文件备份数据)
     * @param unknown $params
     */
    public function getFileDataTree($params)
    {
        $dataflag = $params['dataflag'];
        $storageUuid = $params['storage_uuid'];
        $sql = "select bbt.real_node_uuid, bsr.node_uuid,bsr.storage_type, bbt.id, bbt.module_type, bbt.task_type, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.src_data_deleted_flag, 
		              bbt.user_uuid, bbt.user_name, fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.source_agent_type as os_type, bbt.task_name
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and 
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
                       and bbt.import_flag = ? and
	                   bbt.module_type = " . Xphp::$_config['MODULE_TYPE']['FS'] . " and bbt.sub_module_type = " . Xphp::$_config['SUBMODULE_TYPE']['FS'] . " and
	                   bbt.data_local_flag = ?";
        $flag = Xphp::$_config['FLAG'];
        $userUUID = Xphp::$_user['useruuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($_SESSION['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 备份数据恢复属于操作权限，不能归属于查看
            // 三权模式下的操作员只能查看自身的数据
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到自身的或者管理的用户的的数据
            $userUuidSql = $utils->v1_auth_get_users('fileprotect');
            $sqlNew = " and bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
        }

         if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }

        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], Xphp::$_config['MODULE_TYPE']['FS']));
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        $sql .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        //定义agent task 数组
        $agent = array();
        $task = array();

        $vmHandler = Xphp::instance('Vmhandler');
        //得到当前任务所有uuid
        $currentTaskUUID = $vmHandler->getCurrentAllTaskUUID();
        foreach ($data as $d) {
            if ($d['module_type'] == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] && $dataflag)
                continue;
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
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


                    "id" => $d['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "task_name" => $taskName,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => !$dataflag,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "nodeuuid" => $nodeUuid,
                    "taskuuid" => $d['task_uuid'],
                    "isParent" => true,
                    "agentuuid" => $d['agent_uuid'],
                    "storage_uuid" => $storageUuid,
                    "storage_type" => $d['storage_type'],
                );
            }
            //检查并添加agent
            $agentName = $this->getAgentNameStr($d['agent_uuid'], $d['agent_name'], $d['agent_ip'],$d['sub_module_type']);
            if (!in_array($d['agent_uuid'] . "_" . $d['task_uuid'], $agent)) {
                $agent[] = $d['agent_uuid'] . "_" . $d['task_uuid'];
                $node[] = array(
                    "id" => $d['agent_uuid'] . "_" . $d['task_uuid'],
                    "pId" => $d['task_uuid'],
                    'name' => $agentName,
                    "title" => $agentName,
                    // "open" => true,
                    "nocheck" => !$dataflag,
                    "type" => 2,
                    "clickshow" => true,
                    "icon" => $d['os_type'] == "Windows" ? "./img/os/Windows.png" : "./img/os/Linux.png",
                    "nodeuuid" => $nodeUuid,
                    "taskuuid" => $d['task_uuid'],
                    "agentuuid" => $d['agent_uuid'],
                    "isParent" => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    "storage_uuid" => $storageUuid,
                    "storage_type" => $d['storage_type'],
                );
            }

        }
        return json_encode($node);
    }

    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param unknown $fbt_name 时间点中的主机名
     * @return name(ip)
     */
    public function getAgentNameStr($agent_uuid, $fbt_name, $ip,$sub_module_type)
    {
        switch ($sub_module_type) {
            case Xphp::$_config['SUBMODULE_TYPE']['FS']: // 源端为文件客户端
                $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent where agent_uuid = ?";
                $result = $this->dbSelect($sql, array($agent_uuid));
                break;
            case Xphp::$_config['SUBMODULE_TYPE']['NAS']: // 源端为NAS设备
                return Xphp::instance('NasHandler')->getNasNameStr($agent_uuid,$fbt_name,$ip);
                break;
            case Xphp::$_config['SUBMODULE_TYPE']['HADOOP']: // 源端为Hadoop集群
                return $fbt_name . '(' . $ip . ')';
                break;
            case Xphp::$_config['SUBMODULE_TYPE']['OBS']: // 源端为对象存储
                return $fbt_name;
            default:
                break;
        }
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
     * 异步获取文件时间点
     * @param unknown $params
     * @return string
     */
    public function getSyncFileTimepoint($params)
    {
        $recoverflag = $params['recoverflag']; //文件恢复加载时间点
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $id = $params['id'];
        $storageUuid = $params['storage_uuid'];
        $dataflag = $params['dataFlag']; //备份数据的树形结构
        $chkDisabled = false;
        $sql = "select bsr.storage_uuid, bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.encrypted_flag,bbt.remarks,bbt.detail,bbt.src_data_deleted_flag,
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, bbt.task_name,fbt.detail as fbtdetail, bbt.integrity_check_flag,
                      bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status
                from fs_backup_timepoint fbt, bd_storage_resource bsr, bd_backup_timepoint bbt
                left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                    bbt.task_uuid = ? and fbt.agent_uuid = ? and bbt.data_local_flag = ? ";

        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $taskuuid, $agentuuid, $flag['SET']);

        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }

        $sql .= " order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint";
        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($data as $point) {
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
                foreach ($fulluuidList as $key => $value) {
                    if ($dependId == $key) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                        // $unfullList = array_splice($unfullList, $key, 1);
                        if (!empty($unfullList[$key])) {
                            unset($unfullList[$key]);
                            $unfullList = array_values($unfullList);
                            $unfullCountTmp--;
                        }
                    }
                    continue;
                }
            }
            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;
        }
        $storageHandler = Xphp::instance('StorageHandler');
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);
        $storageOfflineStatus = Xphp::$_config['STORAGE_STATUS']['OFFLINE'];
        $node = array();
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        $nodeHandler = Xphp::instance('NodeHandler');
        $jobHandler = Xphp::instance('JobHandler');
        $pid = null;
        foreach ($data as $d) {
            $pointMixedStatus = $jobHandler->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
             // 判断当前时间点所在存储是否离线，是则置灰节点
             $isOfflineStorage = 1;
             foreach ($storageStatusList as $key => $storageStatusInfo) {
                 if ($d['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                     $isOfflineStorage = $storageStatusInfo['storage_status'];
                     break;
                 }
             }
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $fbtdetail = json_decode($d['fbtdetail'], true);
            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")";
            $title = $name;
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . Xphp::$_lang['UI_PUBLIC_STORAGE_OFF'] . ')' . '</span>';
            }
            $chkDisabled = $isOfflineStorage == $storageOfflineStatus;
            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }
            $mark = "";
            if ($d["src_data_deleted_flag"] != 2) { //归档开启
                $mark = '(' . Xphp::$_lang['UI_ARCHIVE_DATA'] . ')' . $mark;
            }
            if ($dataflag) {
                //添加GFS标识
                $mark = $mark . $vmHandler->pGetTimepointMark(false, false, false, $utils->parseFlagToBool($d['importance_flag']));
                $gfsforever = $mark;
                //添加备注
                // if(!empty($d['remarks'])){
                //     $mark .= '<a id="remark_'.$d['timepoint_uuid'].'" style="display:inline-block;color: #5b9bd1;position: relative;top:5px;left:-4px;" class="popovers remarktips" data-container="body" data-trigger="hover"
                //             data-placement="right" data-content="'.preg_replace('/\"/', "'", $d['remarks']).'"><i class="fa fa-info-circle fa-lg" style="font-size: 21px !important; position:relative;top:-4px;left:-4px;"></i></a>';
                //         }
                $chkDisabled = true;
            }
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $id,
                    "name" => $name . $mark,
                    "title" => $title,
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "oldname" => $name,
                    "gfsforever" => $gfsforever,
                    "point_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => htmlspecialchars_decode($d['agent_name']),
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                    "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "timepointuuid" => $d['timepoint_uuid'],
                    "taskuuid" => $taskuuid,
                    "nodeuuid" => $nodeUuid,
                    'storagename' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    "encrypted_flag" => $encrypted_flag,
                    "ostype" => $fbtdetail['os_type'],
                    "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    "storage_type" => $d['storage_type'],
                    'chkDisabled' => $chkDisabled,
                    'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                    "point_status" => $pointMixedStatus['status'],
                    "available_flag" => $pointMixedStatus['available_flag'],
                );
                continue;
            }
            $node[] = array(
                "id" => $d['timepoint_uuid'],
                "pId" => $fulluuidList[$timepointuuid],
                "name" => $name . $mark,
                "title" => $title,
                "checked" => false,
                "type" => 4,
                "oldname" => $name,
                "gfsforever" => $gfsforever,
                "nocheck" => $recoverflag,
                "point_uuid" => $d['timepoint_uuid'],
                "depend_uuid" => $d['depend_point_uuid'],
                "agent_name" => htmlspecialchars_decode($d['agent_name']),
                "agent_ip" => $d['agent_ip'],
                "agent_uuid" => $d['agent_uuid'],
                "task_name" => $d['task_name'],
                "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                "mode" => intval($d['backup_mode']),
                "timepointuuid" => $d['timepoint_uuid'],
                "taskuuid" => $taskuuid,
                "nodeuuid" => $nodeUuid,
                'chkDisabled' => $chkDisabled,
                "storagename" => $d['storage_nickname'],
                'timepoint' => $this->parseDate($d['timepoint']),
                "encrypted_flag" => $encrypted_flag,
                "ostype" => $fbtdetail['os_type'],
                "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                "storage_type" => $d['storage_type'],
                'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                "point_status" => $pointMixedStatus['status'],
                "available_flag" => $pointMixedStatus['available_flag'],
            );
        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }
    /**
     * 搜索文件时间点
     * @param unknown $params
     */
    public function searchFsTimepoint($params)
    {
        $search = $params['search'];
        $forever = $params['forever'];
        $storageUuid = $params['storage'];
        $dataFlag = $params['dataFlag'];
        $chkDisabled = true;
        $sql = "select bbt.deleted_flag,bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag,
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail, bbt.integrity_check_flag,
        bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status 
  from fs_backup_timepoint fbt, bd_storage_resource bsr,bd_backup_timepoint bbt
  left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
  where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and bbt.deleted_flag = 2 and 
         bbt.module_type = ? and bbt.sub_module_type = " . Xphp::$_config['SUBMODULE_TYPE']['FS'] . " and bbt.data_local_flag = 1";
         if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . Xphp::$_user['useruuid'] . "'";
        }
         //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        if ($forever) {
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }
        if (!empty($search)) {
            $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        }
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['FS']);
        $taskType = Xphp::$_config['TASKTYPE'];
        if (!$dataFlag) { //恢复页面
            $chkDisabled = false;
            $sql .= " and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
        } else {
            $sql .= " and bbt.task_type = {$taskType['BACKUP']}";
        }
        if (!empty($params['recovery_range'])) {
            $sql .= ' and bbt.timepoint between ? and ?';
            $sqlParams = array_merge($sqlParams, array($params['startTime'], $params['endTime']));
        }
        //权限
        $authUser = $_SESSION['authUser']['fileprotect_look'] ?? [];
        if ($authUser) {
            $userUuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $userUuids = "('" . implode("','", $userUuidArr) . "')";
            $sql .= " and bbt.user_uuid IN $userUuids ";
        } else {
            $sql .= " and bbt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
            $pointData = $this->dbSelect($sql, array_merge($sqlParams,array($storageUuid)));
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql, $sqlParams);
        }
       
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else { //如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
                // $unfullList[] = $point;
                if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']) { //差异
                    $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid,bsr.storage_type, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag,
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail ,bbt.integrity_check_flag,
                    bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status  
                    from fs_backup_timepoint fbt, bd_storage_resource bsr,bd_backup_timepoint bbt
                    left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                     bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
                     bbt.module_type = 3 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=? order by fbt.agent_uuid, bbt.timepoint";
                    $data = $this->dbSelect($sqlfull, array($point['depend_point_uuid']));
                    if (!in_array($data[0]['timepoint_uuid'], $fulluuidList)) {
                        $pointData[] = $data[0]; //完备点
                    }
                } else { //增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                    if (!in_array($fullpoint['timepoint_uuid'], $fulluuidList)) {
                        $fulluuidList[] = $fullpoint['timepoint_uuid']; //避免搜出重复完备点
                        $pointData[] = $fullpoint;
                    }
                    $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }
        $timepoint = array();
        $node = array();
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        $nodeHandler = Xphp::instance('NodeHandler');
        $pid = null;
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        foreach ($pointData as $d) {
            $pointMixedStatus = $jobHandler->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
            $detail = json_decode($d['detail'], true);
            $ftDetail = json_decode($d['ft_detail'], true);
            $name = $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")";
            $title = $name;
            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }
            $mark = "";
            if ($d["src_data_deleted_flag"] != 2 && empty($params['recovery_range'])) { //归档开启 不是恢复页面的搜索才显示归档
                $mark = '(' . Xphp::$_lang['UI_ARCHIVE_DATA'] . ')' . $mark;
            }
            //添加GFS标识
            $mark = $mark . $vmHandler->pGetTimepointMark(false, false, false, $utils->parseFlagToBool($d['importance_flag']));
            $gfsforever = $mark;
            if (!$dataFlag) {
                $mark = '';
            }
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fullTimepoint = $d['timepoint_uuid'];
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $d['agent_uuid'] . "_" . $d['task_uuid'],
                    "name" => $name . $mark,
                    "title" => $title,
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "oldname" => $name,
                    "gfsforever" => $gfsforever,
                    "point_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => htmlspecialchars_decode($d['agent_name']),
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                    "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "timepointuuid" => $d['timepoint_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "nodeuuid" => $nodeUuid,
                    'storagename' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    "nodename" => $nodeHandler->getNodeName($d['real_node_uuid']),
                    "encrypted_flag" => $encrypted_flag,
                    "ostype" => $ftDetail['os_type'],
                    "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                    "storage_type" => $d['storage_type'],
                    'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                    "point_status" => $pointMixedStatus['status'],
                    "available_flag" => $pointMixedStatus['available_flag'],
                );
                $incList = $this->getSearchIncTimepoint($d['timepoint_uuid']);
                if (!empty($incList)) {
                    foreach ($incList as $d) {
                        if ($d['timepoint_uuid'] == $fullTimepoint) {
                            continue;
                        }
                        $detail = json_decode($d['detail'], true);
                        $name = $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")";
                        if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                            $name .= '<i class="fa fa-lock"></i>';
                            $encrypted_flag = true;
                        } else {
                            $encrypted_flag = false;
                        }
                        //添加GFS标识
                        $mark = $vmHandler->pGetTimepointMark(false, false, false, $utils->parseFlagToBool($d['importance_flag']));
                        $gfsforever = $mark;
                        if (!$dataFlag) {
                            $mark = '';
                        }
                        $node[] = array(
                            "id" => $d['timepoint_uuid'],
                            "pId" => $fullTimepoint,
                            "name" => $name . $mark,
                            "title" => $title,
                            "checked" => false,
                            "type" => 4,
                            "oldname" => $name,
                            "gfsforever" => $gfsforever,
                            "nocheck" => $dataFlag,
                            "point_uuid" => $d['timepoint_uuid'],
                            "depend_uuid" => $d['depend_point_uuid'],
                            "agent_name" => htmlspecialchars_decode($d['agent_name']),
                            "agent_ip" => $d['agent_ip'],
                            "agent_uuid" => $d['agent_uuid'],
                            "task_name" => $d['task_name'],
                            "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                            "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                            "mode" => intval($d['backup_mode']),
                            "timepointuuid" => $d['timepoint_uuid'],
                            "taskuuid" => $d['task_uuid'],
                            "nodeuuid" => $nodeUuid,
                            "chkDisabled" => $chkDisabled,
                            "storagename" => $d['storage_nickname'],
                            'timepoint' => $this->parseDate($d['timepoint']),
                            "nodename" => $nodeHandler->getNodeName($nodeUuid),
                            "encrypted_flag" => $encrypted_flag,
                            "ostype" => $ftDetail['os_type'],
                            "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                            "storage_type" => $d['storage_type'],
                            'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                            "point_status" => $pointMixedStatus['status'],
                            "available_flag" => $pointMixedStatus['available_flag'],
                        );
                    }
                }
                continue;
            }
        }

        return json_encode($node);
    }

    /**
     * 搜索时获取增量点
     */
    public function getSearchIncTimepoint($depend_point_uuid)
    {
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid,bsr.storage_type, bbt.integrity_check_flag, 
                   bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status
            FROM bd_backup_timepoint bbt
            LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            LEFT JOIN bd_backup_timepoint_safe_info bsi ON bsi.timepoint_uuid = bbt.timepoint_uuid 
            WHERE bbt.timepoint_uuid = ? 
                  AND bbt.available_flag = 1 
                  AND bbt.import_flag = 2
            UNION ALL
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid,bsr.storage_type, bbt.integrity_check_flag,
                   bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status 
            FROM bd_backup_timepoint bbt
            LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            JOIN cte ON bbt.depend_point_uuid = cte.timepoint_uuid
            LEFT JOIN bd_backup_timepoint_safe_info bsi ON bsi.timepoint_uuid = bbt.timepoint_uuid 
            WHERE bbt.available_flag = 1 
                  AND bbt.import_flag = 2
        )
        SELECT * FROM cte ORDER BY timepoint;
        ";
        $result = $this->dbSelect($sql, array($depend_point_uuid));
        if (empty($result)) {
            return array();
        }
        return $result;
    }

    /**
     * 找增量点的完备点
     * @param unknown $params
     */
    private function getFulllPoint($timepoint_uuid)
    {
        $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid,bsr.storage_type, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
        bbt.src_data_deleted_flag,fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, bbt.integrity_check_flag 
        from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
        where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
         bbt.module_type = 3 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=? order by fbt.agent_uuid, bbt.timepoint";
        $data = $this->dbSelect($sqlfull, array($timepoint_uuid));
        if (!empty($data[0]['depend_point_uuid'])) { //不是完备点继续找
            return $this->getFulllPoint($data[0]['depend_point_uuid']);
        } else {
            return $data[0];
        }
    }


    /**
     * 校验文件数据加密密码正确性
     * @param unknown $params
     * @return string
     */
    public function checkFSEncryptPass($params)
    {
        $timepointuuid = $params['timepointuuid'];
        $inputpass = base64_decode($params['inputpass']);
        $sqlencrypt = "select encrypted_flag, detail from bd_backup_timepoint where timepoint_uuid  = ? ";
        $sqlParamsencrypt = array($timepointuuid);
        $dataencrypt = $this->dbSelect($sqlencrypt, $sqlParamsencrypt); //password_auto_flag, password
        $dataencrypt = $dataencrypt[0];
        $detail = json_decode($dataencrypt['detail'], true);
        $utils = Xphp::instance('Utils');
        if ($dataencrypt['encrypted_flag'] == 1 && intval($detail['password_auto_flag']) == 2) { //加密开 自动生成密码关
            $inputpass = $utils->ptPassEncrypt(htmlspecialchars_decode($inputpass)); //htmlspecialchars_decode把特殊字符转成原来的字符
            if ($detail['password'] != $inputpass) { //数据库中的密码不等于页面输入密码
                return $this->muOpResult(false, Xphp::$_lang['UI_BACKUP_DATA_ENCRYPT'], Xphp::$_lang['UI_FILE_INCORRECT_PASSWORD'], "warning");
            }
        }

        $info = array(
            'encrypted_flag' => $dataencrypt['encrypted_flag'],
            'password_auto_flag' => intval($detail['password_auto_flag']),
            'flag' => true
        );
        return json_encode($info);
    }

    /**
     * 得到文件备份时间点树(文件恢复STEP1)
     * @param unknown $params
     */
    public function getFileTimepointTree($params)
    {
        $sql = "select bbt.id, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid,  
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name  
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? and
	                   bbt.import_flag = ? and bbt.module_type = ? and 
	                   bbt.user_uuid = ? 
                order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint desc  ";

        $flag = Xphp::$_config['FLAG'];
        $module = Xphp::$_config['MODULE_TYPE']['FS'];
        $userUUID = Xphp::$_user['useruuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $module, $userUUID);

        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        //定义host task 数组
        $host = array();
        $task = array();
        $pfDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($data as $d) {
            //检查并添加host
            if (!in_array($d['agent_uuid'], $host)) {
                $host[] = $d['agent_uuid'];
                $key = array_search($d['agent_uuid'], $host);
                $node[] = array(
                    "id" => '0_' . $key,
                    "pId" => 0,
                    "name" => $d['agent_ip'] . "(" . $d['agent_name'] . ")",
                    "open" => false,
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => './img/vm/host.png',
                );
            }
            $key = array_search($d['agent_uuid'], $host);
            $pid = '0_' . $key;

            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
                $key = array_search($d['task_uuid'], $task);
                $node[] = array(
                    "id" => $pid . "_" . $key,
                    "pId" => $pid,
                    "name" => $d['task_name'],
                    "open" => false,
                    "nocheck" => true,
                    "type" => 2,
                    "icon" => './img/platform/flag.png'
                );
            }

            $key = array_search($d['task_uuid'], $task);
            $pid .= "_" . $key;

            $mode = intval($d['backup_mode']);
            $pointMode = "(" . $pfDes['BACKUP_MODE_DES'][$mode] . Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . ")";

            $node[] = array(
                "id" => $pid . "_" . $d['id'],
                "pId" => $pid,
                "name" => $this->parseDate($d['timepoint']) . $pointMode,
                "checked" => false,
                "type" => 3,
                "nocheck" => true,
                "point_uuid" => $d['timepoint_uuid'],
                "agent_name" => $d['agent_name'],
                "agent_ip" => $d['agent_ip'],
                "agent_uuid" => $d['agent_uuid'],
                "task_name" => $d['task_name'],
                "icon" => './img/platform/timepoint.png'
            );
        }
        return json_encode($node);
    }

    /**
     * 得到备份文件列表
     * @param array $params
     *      sclass                  文件样式类型  s/m/l 24 32 48
     *
     *      timepoint_uuid(string)   时间点UUID
     *      root_flag(int)           是否是根节点
     *      start(int)               开始位置
     *      number(int)              获取条数
     *      path(string)             当前路径
     *      md5_flag(int)            是否有MD5信息标志
     *      md5_high(int)            MD5的高八位
     *      md5_low(int)             MD5的第八位
     *      三种情况
     *          1.初始展示[timepoint_uuid, 1, 0, N, '', 0, '', '']
     *          2.更多信息[timepoint_uuid, 0, N, N+M, path, md5_flag, md5_high, md5_low]
     *          3.进入目录[timepoint_uuid, 0, 0, N, path, md5_flag, md5_high, md5_low]
     *
     */
    public function getBackupFileDir($params)
    {

        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        if (count($params) != 11) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
        }
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = intval($params['root_flag']);
        $start = intval($params['start']);
        $number = intval($params['number']);
        $path = $params['path'];
        $md5Flag = intval($params['md5_flag']);
        $md5High = $params['md5_high'];
        $md5Low = $params['md5_low'];
        $pid = $params['pid'];
        $sclass = $params['sclass'];
        $taskuuid = $params['taskuuid'];
        $this->paramsCheck($timepointUUID);
        $msg = array(
            'timepoint_uuid' => $timepointUUID,
            'root_flag' => $rootFlag,
            'start' => $start,
            'number' => $number,
            'path' => $path,
            'md5_flag' => $md5Flag,
            'md5_high' => $md5High,
            'md5_low' => $md5Low
        );

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true, true);

        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $pfOpcode = Xphp::instance('PFOpcode');
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $list = array(
            "re" => true,                               //成功标志,方面前端统一处理
            "finishFlag" => $data['finish_flag'],       //文件是否列完成   1完成,2未完成
            "nextStart" => $data['next_start'],       //未完成时,下一个开始位置
            "path" => $data['path'],                    //当前路径
            "timepointUUID" => $data['timepoint_uuid'],
        );
        $fileList = array();
        // $utils = Xphp::instance('Utils');
        foreach ($data['item_list'] as $d) {
            switch ($d['type']) {
                case 1:
                    $icon = "./img/fs/wenjian.png";
                    break;
                case 2:
                    $icon = "./img/fs/wenjianjia.png";
                    break;
                case 3:
                    $icon = "./img/fs/cipan.png";
                    break;
                default:
                    $icon = "./img/fs/wenjian.png";
                    break;
            }
            // $filesize = $utils->calSize($d['file_size']);
            $fileList[] = array(
                // $filename,      //文件名
                // $filesize,      //文件大小
                "id" => $d['path'],
                "pid" => $pid,
                "name" => $d['filename'],
                "nocheck" => false,
                "title" => $d['filename'],
                "path" => $d['path'],
                "btype" => $d['type'],   //文件类型
                "isParent" => $this->getBoolType($d['type']),
                "icon" => $icon,
                "iconOpen" => $d['type'] == 2 ? './img/fs/wenjianjiaopen.png' : $icon,
                "iconClose" => $d['type'] == 2 ? './img/fs/wenjianjia.png' : $icon,                                       //从后台获取的文件类型
                "isfile" => $d['type'] == Xphp::$_config['FILETYPE']['FILE'] ? true : false,   //是否是文件
                "sclass" => $this->getFileClassName($d['type'], $d['filename'], $sclass),                //显示类型
                "md5Flag" => $d['md5_flag'],
                "md5High" => $d['md5_high'],
                "md5Low" => $d['md5_low'],
                "createTime" => $d['create_time'],
                "modifyTime" => $d['modify_time'],
                "pointuuid" => $params['timepoint_uuid'],
                "more" => false,
            );
        }
        $list['filelist'] = $fileList;
        if (intval($data['finish_flag']) == Xphp::$_config['FLAG']['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $data['path'] . '/more',
                "pId" => $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "nocheck" => true,
                "more" => true,
                "next_index" => $data['next_start'],       //从哪个位置开始加载
                // "search_file_name" => $data['path'],          //从哪个目录开始加载
                // "dir_path" => $params['dir'],
                "pointuuid" => $params['timepoint_uuid'],
                "sclass" => $sclass,                //显示类型
                "md5Flag" => $md5Flag,
                "md5High" => $md5High,
                "md5Low" => $md5Low,                             //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            array_push($list['filelist'], $more);
            // $list['filelist'] = $more;
        }


        return json_encode($list);
    }

    /**
     * 根据文件类型判断是否是父节点
     * @param int $type 文件类型
     * @return boolean
     */
    public function getBoolType($type)
    {
        $bool = '';
        switch (intval($type)) {
            case 0:
            case 1:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                $bool = false;
                break;
            case 2:
            case 3:
                $bool = true;
                break;
        }
        return $bool;
    }

    /**
     * 根据文件类型得到显示的Class
     * @param int $filetype 文件类型
     * @param string $filename  文件名
     * @param string $size  图标大小   s/m/l 24/32/48px
     */
    public function getFileClassName($filetype, $filename, $size)
    {
        $class = "filetype-unknown-" . $size;
        if (intval($filetype) != Xphp::$_config['FILETYPE']['FILE']) {
            $class = "filetype-dir-" . $size;
            return $class;
        }
        $allFileType = array(
            'aac',
            'ai',
            'aiff',
            'asp',
            'avi',
            'bmp',
            'c',
            'cpp',
            'css',
            'dat',
            'dmg',
            'doc',
            'docx',
            'dot',
            'dotx',
            'dwg',
            'dxf',
            'eps',
            'exe',
            'flv',
            'gif',
            'h',
            'html',
            'ics',
            'iso',
            'java',
            'jpg',
            'key',
            'm4v',
            'mid',
            'mov',
            'mp3',
            'mp4',
            'mpg',
            'odp',
            'ods',
            'odt',
            'otp',
            'ots',
            'ott',
            'pdf',
            'php',
            'png',
            'pps',
            'ppt',
            'psd',
            'py',
            'qt',
            'rar',
            'rb',
            'rtf',
            'sql',
            'tga',
            'tgz',
            'tiff',
            'txt',
            'wav',
            'xls',
            'xlsx',
            'xml',
            'yml',
            'zip'
        );
        $fileArr = explode('.', $filename);
        $index = (count($fileArr) - 1) <= 0 ? 0 : (count($fileArr) - 1);
        if (in_array(strtolower($fileArr[$index]), $allFileType)) {
            $class = "filetype-" . strtolower($fileArr[$index]) . "-" . $size;
        }
        return $class;
    }

    /**
     * 得到恢复代理(恢复到其他宿主机)
     * @param unknown $params
     */
    public function getRecoverAgentTree($params)
    {
        $agentUUID = $params['agentuuid'];
        $this->paramsCheck($agentUUID);
        $tree = array();
        $agentHandler = Xphp::instance('AgentHandler');
        $utils = Xphp::instance('Utils');
        //增加文件代理分组节点
        $agentGroupList = $agentHandler->getAgentGroupBackupTree();
        $agentGroupList = $utils->object_array(json_decode($agentGroupList));
        if (!empty($agentGroupList)) {
            foreach ($agentGroupList as $agent) {
                $tree[] = $agent;
            }
        }
        // 父节点不显示复选框
        $treenode = [];
        foreach ($tree as $t) {
            if ($t['eventtype'] == 'group') {
                $t['nocheck'] = true;
            }
            array_push($treenode, $t);
        }
        return json_encode($treenode);
    }

    /**
     * 得到代理端恢复文件目录树
     * @param array $params
     *      start  开始位置
     *      limit  查找个数
     *      searchFileName 从哪个文件名开始查找
     *      dir            进入目录的目录名
     *      三种情况
     *          1.初始展示[0, N, '', '']
     *          2.更多信息[N, N+M, searchFileName, '']
     *          3.进入目录[0, N, '', dir]
     * @return string
     */
    public function getRecoverPathTree($params)
    {
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agentuuid'];
        $pid = $params['pid'];          //父节点ID
        $this->paramsCheck($agentUUID);
        $code_type = 2;
        if (!empty($params['code_type'])) {
            $code_type = $params['code_type']; //编码类型
        }
        $msg = array(
            'agent_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $code_type,
        );
        $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $NodeOpcode = Xphp::instance('NodeOpcode');
            $operate = $NodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];

        $i = 0;
        foreach ($data['item_list'] as $d) {
            $node = array(
                "id" => $pid . "_" . $i++,
                "pId" => $pid == "" ? 0 : $pid,
                "name" => $d['item_name'],
                "title" => $d['item_path'],
                "isParent" => true,
                "nocheck" => false,
                "icon" => "./img/fs/wenjianjia.png",
                "iconOpen" => "./img/fs/wenjianjiaopen.png",
                "iconClose" => "./img/fs/wenjianjia.png",
                "type" => $d['item_type'],
                "more" => false,
                "noRemoveBtn" => true,
                "code_type" => $d['code_type'],
                "isnew" => false, //文件夹是否是最新的新建
                "new_dir_create" => Xphp::$_config['FLAG']['UNSET'], //文件夹是否是新建的
                "noEditBtn" => true,
                //                 "icon" => "./img/vm/host.png",
            );
            $tree[] = $node;
        }
        if (intval($data['is_search_finish']) == Xphp::$_config['FLAG']['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $pid . "_" . $i++,
                "pId" => $pid == "" ? 0 : $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "icon" => "./img/fs/wenjianjia.png",
                "iconOpen" => "./img/fs/wenjianjiaopen.png",
                "iconClose" => "./img/fs/wenjianjia.png",
                "isParent" => false,
                "nocheck" => true,
                "more" => true,
                "next_index" => $data['current_next_index'],       //从哪个位置开始加载
                "search_file_name" => $data['search_file_name'],          //从哪个目录开始加载
                "dir_path" => $dir,                                  //当前目录名
                "noRemoveBtn" => true,
                "isnew" => false, //文件夹是否是最新的新建
                "new_dir_create" => Xphp::$_config['FLAG']['UNSET'], //文件夹是否是新建的
                "noEditBtn" => true,
            );
            $tree[] = $more;
        }

        $info = array(
            're' => true,
            'tree' => $tree
        );

        return json_encode($info);

    }

    /**
     * 得到告警文件备份任务详情
     * @param unknown $params
     */
    public function getBackupTaskInfo($params)
    {
        $alarmid = $params['alarmid'];
        $this->paramsCheck($alarmid);
        $sql = "select bht.details  
                from bd_history_task bht, bd_task_alarm bta 
                where bht.history_uuid = bta.history_uuid
                and bta.task_alarm_id  = ? ";
        $sqlParams = array($alarmid);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $detail = json_decode($data[0]['details'], true);
        if ($detail) {
            foreach ($detail['agent_info_list'] as $a) {
                $info[] = array(
                    'agentName' => $a['src_agent_name'],
                    'agentIP' => $a['src_agent_ip'],
                    'file_list' => $a['file_list']
                );
            }
        }
        return json_encode($info);
    }

    /**
     * 删除备份时间点
     * @param unknown $params
     * storageHandler deleteFSImportData调用
     */
    public function deleteTimepoint($params)
    {
        $pointUUID = $params['uuid'];
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $sql = "select timepoint,timepoint_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID[0]));
        //检测时间点是否在磁带上
        $this->checkTimepointStorage([$data[0]['timepoint_uuid']]);
        $this->paramsCheck($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $authUser = $_SESSION['authUser']['fileprotect_operate'] ?? [];
            if (!empty($authUser)) {
                $authUserStr = "'" . implode("','", $authUser) . "'";
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid not in 
                   ({$authUserStr}) and timepoint_uuid = ?", [$pointUUID]);
            } else {
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [Xphp::$_user['useruuid'], $pointUUID]);
            }
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'error'));
            }
        }

        $msg = json_encode(array('timepoint_uuids' => $pointUUID));
        // $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = null;
        // $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];
        $descriptionParam = array($data[0]['timepoint']);
        //返回结果到UI
        if ($result) {
            //             $this->systemLog('SYSTEM_LOG_DELETE_FILE_ONE_TIMEPOINT', $descriptionParam);
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and fbt.agent_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                    ";
            $flag = Xphp::$_config['FLAG'];
            $dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid, $pointUUID));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count" => intval($count), "id" => $pointUUID));
        } else {
            //             $this->systemLog('SYSTEM_LOG_DELETE_FILE_ONE_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 删除批量备份时间点
     * @param array $params 二维数组
     * 如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     */
    public function deleteSelectTimepoint($params)
    {
        $pointList = $params['pointList'];
        $agentList = $params['agentList'];
        $selectTimepoints = array();
        if (!empty($agentList)) {
            foreach ($agentList as $agent) {
                $selectTimepoints = $this->getTimepointByFS($agent, $params['storage_uuid']);
                $pointList = array_merge($pointList, $selectTimepoints);
            }
        }
        $utils = Xphp::instance('Utils');
        $pointList = $utils->arraySort($pointList, 'nodeuuid', '', 0, -1);
        $info = array();
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach ($pointList as $d) {
            $i++;
            if (!in_array($d['nodeuuid'], $nodeuuids)) {
                $nodeuuids[] = $d['nodeuuid'];
                $info[] = array(
                    "nodeuuid" => $d['nodeuuid'],
                    "type" => $d['type']
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

        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'error'));
            }
        }

        $timepointuuids[] = $timepointuuid;
        //检测时间点是否在磁带上
        $this->checkTimepointStorage($timepointuuids[0]);
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        //检测是否在任务中  在任务就直接返回
        $uuidList = array();
        foreach ($timepointuuids as $d) {
            $uuidList = array_merge($uuidList, $d);
        }
        // $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        $countPoint = 0;
        $nodeHandler = Xphp::instance('NodeHandler');
        for ($i = 0; $i < $nodeuuidsCount; $i++) {
            $msg = $timepointuuids[$i];
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            if (empty($nodeuuids[$i])) {
                $nodeuuids[$i] = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointuuids[$i][0]);
            }
            //检查时间点是否有恢复任务正在使用
            $this->checkRecoveryPoint($nodeuuids[$i], $timepointuuids[$i], 'fileprotect_operate');
            $msg = json_encode($msg);
            $mbResult = $this->mbFSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, Xphp::$_lang['WEB_PLATFORM_DES_FS']);
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
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, fbt.agent_name, fbt.agent_ip from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = ? and fbt.fs_timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_FS'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_AGENT_HOST_NAME'] . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_FS'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_AGENT_HOST_NAME'] . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
    }

    /**
     * 检查是否拥有文件数据的操作权限
     * @param array $userIdList 操作资源的拥有者uuid列表
     * @param string $userModule 模块对应的uuid属性值
     * @return void
     */
    private function checkOperatePermission(array $userIdList, string $userModule)
    {
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser'][$userModule] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], $userIdList, $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }
    }

    /**
     * 检测恢复任务正在使用的备份点不能被删除
     */
    public function checkRecoveryPoint($nodeuuid, $pointList, $userModule)
    {
        //        操作权限
        $timepointuuids = implode("','", $pointList);
        $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
        $this->checkOperatePermission(array_column($userUuidData, 'user_uuid'), $userModule);
        $sql = "select bt.id from bd_task bt, fs_path_list fpl where fpl.recovery_timepoint_uuid = ? and fpl.task_uuid = bt.task_uuid and bt.task_status = 2 and bt.node_uuid = ?";
        foreach ($pointList as $pointuuid) {
            $params = array($pointuuid, $nodeuuid);
            $data = $this->dbSelect($sql, $params);
            if (!empty($data)) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_FILE_DELETE_TIME_POINT_ERROR'], Xphp::$_lang['WEB_FILE_DELETE_TIME_POINT_ERROR_TIPS'], 'warning'));
            }
        }



    }

    /**
     * 获取删除的主机对应时间点信息--文件副本备份数据
     * 根据类型和任务筛选
     * @param array $fsinfo
     */
    public function getTimepointByCopyFs($fsinfo, $storageuuid)
    {
        $info = array();
        $sql = "select bbt.timepoint_uuid from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['taskuuid'], $fsinfo['agentuuid']);
        if (!empty($storageuuid)) {
            $sql .= " and bbt.storage_uuid = ?";
            $params = array_merge($params, array($storageuuid));
        }
        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $fsinfo['nodeuuid'],
                'type' => $fsinfo['type']
            );
        }
        return $info;
    }

    /**
     * 获取删除的主机对应时间点信息--文件备份数据
     * 根据类型和任务筛选
     * @param array $fsinfo
     */
    public function getTimepointByFS($fsinfo, $storageuuid)
    {
        $info = array();
        $sql = "select distinct bbt.timepoint_uuid,  bsr.node_uuid,bbt.real_node_uuid from bd_backup_timepoint bbt,bd_storage_resource bsr,
        fs_backup_timepoint fbt where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['taskuuid'], $fsinfo['agentuuid']);
        if (!empty($storageuuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $params = array_merge($params, array($storageuuid));
        }
        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $nodeUuid,
                'type' => $fsinfo['type']
            );
        }
        return $info;
    }

    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode
     * @return string
     */
    public function getTimepointTypeDes($bakcupMode, $db_type)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = '';
        if ($bakcupMode == 4) {
            if ($db_type == Xphp::$_config['DB_TYPE']['DM'] || $db_type == Xphp::$_config['DB_TYPE']['ORACLE']) {
                $des = $pfDes['BACKUP_MODE_DES'][5];
            } else {
                $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
            }
        } else {
            $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
        }
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }

    /**
     * 获取文件时间点列表信息
     * @param unknown $params
     */
    public function getFsTimepointGrid($params)
    {
        $storageUuid = $params['storage_uuid'];
        $start = intval($params['start']);
        $length = intval($params['length']);
        $draw = $params['draw'];
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $search = $params['search'];
        $copyFlag = $params['copyflag'];        //副本数据标志
        $archiveFlag = $params['archiveFlag'];  //归档数据标志
        $localflag = intval($params['localflag']); //本地标志
        $sortArr = array(
            'bbt.timepoint',
            'bbt.backup_mode',
            'bbt.total_size',
            'bbt.write_size',
            '',
            'bbt.remarks',
            '',
            'bbt.importance_flag'
        );

        //         编号	时间点	类型	数据大小	用户	备注	操作	星标
        $sql = 'select bsr.storage_type,bsr.node_uuid, bbt.real_node_uuid, bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.total_size, bbt.write_size, bbt.data_local_flag ,bbt.user_uuid, bbt.importance_flag, bbt.remarks,bbt.src_data_deleted_flag from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ? and fst.agent_uuid = ? and bbt.task_uuid = ?';
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ? and fst.agent_uuid = ? and bbt.task_uuid = ?";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid);
        //区分异地本地
        if (!empty($localflag)) {
            $sql .= ' and bbt.data_local_flag = ?';
            $sqlParams = array_merge($sqlParams, array($localflag));
        }
        //如果带有搜索条件
        if (!empty($search)) {
            //如果开始时间和结束时间都有 则添加时间查询
            if (!empty($search['startTime']) && !empty($search['endTime'])) {
                $sql .= " and bbt.timepoint between ? and ? ";
                $sqlCount .= " and bbt.timepoint between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($search['startTime'], $search['endTime']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['startTime'], $search['endTime']));
            }
            //如果有类型 则添加类型
            if (!empty($search['timepointType'])) {
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($search['timepointType']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['timepointType']));
            }
            //如果有永久标记点 则添加永久标记的搜索
            if (!empty($search['forever'])) {
                $sql .= " and bbt.importance_flag = ? ";
                $sqlCount .= " and bbt.importance_flag = ? ";
                $sqlParams = array_merge($sqlParams, array($search['forever']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['forever']));
            }
        }
        //如果切换了节点
        if ($storageUuid) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlCount .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
        }
        if ($copyFlag || $archiveFlag) {
            if ($storageUuid && !empty($storageUuid)) {
                $sql .= " and bsr.storage_uuid = ? ";
                $sqlCount .= " and bsr.storage_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($storageUuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
            }
        }

        $sqlParams = array_merge($sqlParams, array($start, $length));

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $records = array("data" => array());
        $i = 1;
        $userUuidList = array_column($data, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";
        $sql = "select user_name, user_uuid from bd_user where user_uuid in ($userUuids)";
        $userData = $this->dbSelect($sql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }
        foreach ($data as $d) {
            $nodeuuid = $d['real_node_uuid'];
            if (empty($d['real_node_uuid'])) {
                $nodeuuid = $d['node_uuid'];
            }
            $mark = $vmHandler->pGetTimepointMark($utils->parseFlagToBool($d['weekly_flag']), $utils->parseFlagToBool($d['monthly_flag']), $utils->parseFlagToBool($d['yearly_flag']), $utils->parseFlagToBool($d['importance_flag']));
            $remark = "";   //备注
            $remotFlag = !$utils->parseFlagToBool(intval($d['data_local_flag']));
            $op = array(1, 2, 3);
            if ($remotFlag) { //如果是异地副本，不能设置星标
                $op = array(1, 2);
            }
            //1、增备和差备只能备注、2、时间点在合并中只能备注 3、磁带存储类型只能备注
            if ($d['backup_mode'] == Xphp::$_config['BACKUP_MODE']['INCREMENTAL'] || $d['backup_mode'] == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']
            || $d['archive_flag'] == Xphp::$_config['FLAG']['SET']
            || $d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['TAPE']) {
                $op = array(1);
            }

            //添加备注
            if (!empty($d['remarks'])) {
                //根据标记是否显示固定备注显示高度
                $top = "";
                if (!empty($mark)) {
                    $top = "top:-4px;";
                }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover"
                            data-placement="right" data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
            }
            $records["data"][] = array(
                '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['timepoint']) . '</span><br>' . $mark . $remark,  //隐藏展示时间点uuid出来,方便运维
                $this->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                $utils->calSize($d['total_size'], true),
                $utils->calSize($d['write_size'], true),
                $this->getStorageName($d['storage_uuid'], $d['timepoint_uuid']),
                //所有者
                $userMapping[$d['user_uuid']],
                $op,
                array(
                    'uuid' => $d['timepoint_uuid'],
                    'backupmode' => $d['backup_mode'],
                    'agentuuid' => $agentuuid,
                    'taskuuid' => $taskuuid,
                    'remote_flag' => $remotFlag,
                    'weekly_flag' => $utils->parseFlagToBool($d['weekly_flag']),
                    'monthly_flag' => $utils->parseFlagToBool($d['monthly_flag']),
                    'yearly_flag' => $utils->parseFlagToBool($d['yearly_flag']),
                    'importance_flag' => $utils->parseFlagToBool($d['importance_flag']),
                    'remark' => $d['remarks'],
                    'nodeuuid' => $nodeuuid,
                    'src_data_deleted_flag' => $d['src_data_deleted_flag'],
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
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int $StrategyType
     * @param string $days
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
     * 根据路径得到文件名
     * @param unknown $path
     */
    private function getFileName($pathType, $pathName)
    {
        $pathArr = explode("/", $pathName);
        $arrLen = count($pathArr);
        if (Xphp::$_config['FILETYPE']['FILE'] == $pathType) {
            //文件
            $filename = $pathArr[$arrLen - 1];
        } else {
            //目录
            $filename = $pathArr[$arrLen - 2];
        }
        return $filename;
    }

    /**
     * 根据任务ID得到文件备份备份列表信息
     * @param unknown $taskuuid
     */
    private function getFileBackupFiles($taskuuid)
    {
        $sql = "select path_name, path_type, agent_uuid,group_uuid,code_type from fs_path_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d) {
            $filename = $this->getFileName(intval($d['path_type']), $d['path_name']);
            $info[] = array(
                'agentuuid' => $d['agent_uuid'],
                'groupuuid' => $d['group_uuid'],
                'type' => $d['path_type'],
                'name' => $filename,
                'path' => $d['path_name'],
                'sclass' => $this->getFileClassName($d['path_type'], $filename, 's'),
                'codetype' => $d['code_type'],
            );
        }
        return $info;
    }

    /**
     * 修改任务/得到备份任务的所有信息
     * @param unknown $params
     */
    public function getBackupTaskAllInfo($params)
    {
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid,bt.strategy_group_uuid, bt.agent_uuid, bt.thread_num,bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
                       bt.node_pool_uuid, bt.storage_pool_uuid,bt.ignore_resource_limiting_flag,
                       ft.level, ft.agent_group_uuid,ft.snap_shot_flag,ft.detail as ftdetail,ft.file_archive_flag,ft.skip_file_alarm_flag,ft.skip_file_alarm_min_num,ft.skip_file_alarm_min_ratio,
                	   ft.permission_operate_flag,brs.strategy_type, brs.number, brs.strategy_mode,
                	   bts.encrypt_flag, bts.compress_flag,bts.network_uuid, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
                       bts.network_pool_uuid,
                	   bss.compressed_flag, bss.compress_method, bss.encrypted_flag,bss.password,bss.password_auto_flag, bss.encrypt_method,
                       btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy,
                       bres.network_retry_times,bres.network_retry_interval,bres.op_retry_times,bres.op_retry_interval,bres.task_retry_object,bres.task_retry_times, bres.task_retry_interval 
                from bd_task bt, fs_task ft, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss,bd_task_safe_config btsc,bd_retry_strategy bres 
                where bt.task_uuid = ft.task_uuid
                and bt.task_uuid = btsc.task_uuid
                and bt.task_uuid = bres.task_uuid 
                and bt.strategy_id = brs.strategy_id
                and bt.strategy_id = bts.strategy_id
                and bt.strategy_id = bss.strategy_id
                and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $sqlwild = 'select group_uuid,agent_uuid,detail from bd_task_agent_list where task_uuid = ?';
        $sqlwilddata = $this->dbSelect($sqlwild, array($taskUUID));
        $info = array();
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        if ($data) {
            $wildcardinfo = array();
            foreach ($sqlwilddata as $d) {
                $wildInfo = json_decode($d['detail'], true);
                if (empty($wildInfo)) {
                    $wildInfo = array(
                        "wildcard" => [],
                        "wildcard_mode" => "0",
                        "wildcard_real_length" => [],
                    );
                }
                $wildInfo[] = $d['agent_uuid'];
                array_push($wildcardinfo, $wildInfo);
            }
            $groupuuid = $this->getAllGroup($taskUUID);
            $agentInfo = $this->getAgentLists($taskUUID);
            $ftdetail = json_decode($data[0]['ftdetail'], true);
            // 查询存储池类型
            $storagePoolType = 0;
            if ($data[0]['storage_pool_uuid']) {
                $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$data[0]['storage_pool_uuid']]);
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }
            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
                // //代理端UUID
                // 'agentuuid' => $data[0]['agent_uuid'],
                //代理分组
                'groupList' => $groupuuid,
                'agentList' => $agentInfo[0]['agentList'],
                'agentOnlineNum' => $agentInfo[0]['agentOnlineNum'],
                'agentOfflineNum' => $agentInfo[0]['agentOfflineNum'],
                //level
                'level' => $data[0]['level'],
                //文件信息
                'fileinfo' => $this->getFileBackupFiles($taskUUID),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,  // 需要返回存储池类别
                ),
                //保留策略
                'brs' => array(
                    'type' => $data[0]['strategy_type'],
                    'number' => $data[0]['number'],
                    'strategy_mode' => $data[0]['strategy_mode'],
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
                    'compress' => $utils->parseFlagToBool($data[0]['compress_flag']),
                    'network' => $data[0]['network_uuid'],
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                    'network_pool_uuid' => $data[0]['network_pool_uuid'],
                ),
                //存储策略
                'bss' => array(
                    'compress' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'password_auto_flag' => $utils->parseFlagToBool($data[0]['password_auto_flag']),
                    'password' => base64_encode($utils->ptPassDecrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method'])
                ),
                //时间策略
                'timestrategy' => $vmHandler->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                // 'speedInfo' => $vmHandler->getSpeedStrategyInfo($taskUUID),
                'speedInfo' => $vmHandler->getSpeedGlobalStrategyInfo($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                    'snap_shot_flag' => $utils->parseFlagToBool($data[0]['snap_shot_flag']),
                    'wildcardinfo' => $wildcardinfo,
                    'scan_thread_num' => $ftdetail['scan_thread_num'],
                    'scan_file_num' => $ftdetail['scan_file_num'],
                    'file_archive_flag' => $data[0]['file_archive_flag'],
                    'skip_file_alarm_flag' => $data[0]['skip_file_alarm_flag'] == 1 ? true : false,
                    'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
                    'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
                    'permission_operate_flag' => $data[0]['permission_operate_flag'] == 1 ? true : false,
                    //安全策略
                    'safe_strategy' => array(
                        //获取worm开关
                        'worm_flag' => $utils->parseFlagToBool($data[0]['worm_flag']),
                        //获取worm保护期限
                        'worm_protection_time' => intval($data[0]['worm_protection_time']),
                        //获取病毒是否开关
                        'virus_scan_flag' => $utils->parseFlagToBool($data[0]['virus_scan_flag']),
                        //获取病毒检测配置
                        'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true),
                        //获取完整性效验开关
                        'integrity_check_flag' => $utils->parseFlagToBool($data[0]['integrity_check_flag']),
                        //获取完整性校验数据
                        'integrity_check_config' => array(
                            //获取效验周期
                            'check_strategy' => intval($data[0]['integrity_check_strategy']),
                            //获取完全备份点异常
                            'full_error_policy' => intval($data[0]['backup_integrity_check_full_error_policy']),
                            //获取其他备份点异常
                            'inc_error_policy' => intval($data[0]['backup_integrity_check_inc_error_policy']),
                        ),
                    ),
                    //重试策略
                    'retry_strategy' => Xphp::instance('JobHandler')->groupRetryStrategyInfo($data[0]),
                    'ignore_resource_limiting_flag' => $utils->parseFlagToBool($data[0]['ignore_resource_limiting_flag']),
                ),
            );

        }
        return json_encode($info);
    }

    /**
     *
     * @param string $taskname
     * @param string $groupuuid
     * @return boolean
     */
    public function updateTaskAgentGroup($taskname, $groupuuid)
    {
        $sql = "update fs_task ft, bd_task bt set ft.agent_group_uuid = ? where bt.task_uuid = ft.task_uuid and bt.task_name = ? ";
        return $this->dbExec($sql, array($groupuuid, $taskname));
    }


    /**
     * 获取代理列表信息
     * @param string $groupuuid
     */
    public function getAgentLists($taskuuid)
    {
        $systemHandler = Xphp::instance('SystemHandler');
        $sql = "select btal.agent_uuid,ba.authorization_module,ba.online_flag from bd_task_agent_list btal,bd_agent ba where btal.task_uuid = ? and btal.agent_uuid = ba.agent_uuid";
        $data = $this->dbSelect($sql, array($taskuuid));
        $agentOnlineNum = 0;
        $agentOfflineNum = 0;
        $agentInfo = array();
        $list = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $authorization = $systemHandler->checkModuleValid($d['authorization_module'], 'file');
                $list[] = $d["agent_uuid"];
                if ($authorization && $d["online_flag"] == 1) {
                    $agentOnlineNum++;
                } else {
                    $agentOfflineNum++;
                }
            }
        }
        $agentInfo[] = array(
            "agentOnlineNum" => $agentOnlineNum,
            "agentOfflineNum" => $agentOfflineNum,
            "agentList" => $list,
        );
        return $agentInfo;
    }
    /**
     * 获取代理分组代理列表信息
     * @param string $groupuuid
     */
    public function getAllGroup($taskuuid)
    {
        $sql = "select distinct(group_uuid) from fs_path_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $list = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                array_push($list, $d["group_uuid"]);
            }
        }
        return $list;
    }

    /**
     * 获取代理分组的所有任务
     * @param string $groupuuid
     * @return unknown[][]|fetchAll()[][]
     */
    public function getFileEditTaskInfo($groupuuid)
    {
        $sql = "select bt.strategy_id, bt.agent_uuid, ft.task_uuid from bd_task bt, fs_task ft where bt.task_uuid = ft.task_uuid and ft.agent_group_uuid = ?";
        $data = $this->dbSelect($sql, array($groupuuid));
        $list = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $list[] = array(
                    'taskuuid' => $d['task_uuid'],
                    'strategyID' => $d['strategy_id'],
                    'agentUUID' => $d['agent_uuid']
                );
            }
        }

        return $list;
    }

    /**
     *
     * @param string $taskuuid
     * @return string|unknown|fetchAll()
     */
    public function getFileEditAgent($taskuuid)
    {
        $sql = "select bt.agent_uuid, ft.agent_group_uuid from bd_task bt, fs_task ft where bt.task_uuid = ft.task_uuid and bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $agent = "";
        if (!empty($data)) {
            $agent = $data[0]['agent_uuid'];
            if (!empty($data[0]['agent_group_uuid'])) {
                $agent = $data[0]['agent_group_uuid'];
            }
        }

        return $agent;
    }
    /**
     * 文件恢复搜索---创建搜索消息
     * @param unknown $params
     */
    public function createSearchJob($params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_THREAD_CREATE';
        $nodeHandler = Xphp::instance('NodeHandler');
        $operate = Xphp::$_lang['WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE'];
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['info']['timepoint_uuid']);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($params['info']), true, true);
        if ($mbResult['result']) {
            $info = array(
                "re" => true,
                "thread_uuid" => $mbResult['msg']['task_uuid'],
            );
        } else {
            //失败
            return $this->muOpResult($mbResult['result'], $operate, '', '', $mbResult['errorCode']);
        }

        return json_encode($info);
    }

    /**
     * 文件恢复搜索---获取搜索结果
     * @param unknown $params
     */
    public function getRecoSearchInfo($params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_RESULT_GET';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['search_timepoint']);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($params['info']), true, false);
        $utils = Xphp::instance('Utils');
        $operate = Xphp::$_lang['WEB_FS_OP_TYPE_SEARCH_RESULT_GET'];
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $result = $mbResult['msg'];
        $filenode = array();
        foreach ($result['item_list'] as $file) {
            if ($file['file_type'] == 1) { //文件
                $titleDes = Xphp::$_lang['WEB_FILE_FILE_SIZE'] . $utils->calSize($file['file_size'], true) . PHP_EOL . Xphp::$_lang['WEB_FILE_MODIFY_TIME'] . $this->parseDate($file['modify_time']);
            } else {
                $titleDes = Xphp::$_lang['WEB_FILE_MODIFY_TIME'] . $this->parseDate($file['modify_time']);
            }
            $filenode[] = array(
                "pid" => 0,
                "id" => $file['path_name'],
                "name" => $file['path_name'],
                "title" => $titleDes,
                "file_type" => $file['file_type'],
                "md5High" => $file['md5_high'],
                "md5Low" => $file['md5_low'],
                "file_size" => $file['file_size'],
                "modify_time" => $file['modify_time'],
                "file_offset" => $file['offset'],
                "icon" => $file['file_type'] == 2 ? "./img/fs/wenjianjia.png" : "./img/fs/wenjian.png",
                "iconOpen" => $file['file_type'] == 2 ? "./img/fs/wenjianjiaopen.png" : "./img/fs/wenjian.png",
                "iconClose" => $file['file_type'] == 2 ? "./img/fs/wenjianjia.png" : "./img/fs/wenjian.png",
                "isParent" => $this->getBoolType($file['file_type']),
                "path" => $file['path_name'],
                "pointuuid" => $params['search_timepoint'],
                "md5Flag" => '1',
                "searchNode" => true,
            );

        }
        $info = array(
            "re" => true,
            "search_finish_flag" => $result['finish_flag'],
            "current_total_num" => $result['number'],
            "current_dir_num" => $result['dir_num'],
            "current_file_num" => $result['file_num'],
            "all_file_num" => $result['all_file_num'],
            "all_dir_num" => $result['all_dir_num'],
            "offset" => $result['offset'],
            "filenode" => $filenode,
        );
        return json_encode($info);
    }

    /**
     * 文件恢复搜索---停止搜索
     * @param unknown $params
     */
    public function stopSearchJob($params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_STOP';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['search_timepoint']);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($params['info']));
        if ($mbResult['result']) { //成功
            $info = array(
                "re" => true,
            );
            return json_encode($info);
        } else {
            //失败
            return $this->muOpResult($mbResult['result'], $opName, '', '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取存储名字
     * @param string $storageuuid 存储uuid
     * @param string $timepointUuid 时间点uuid
     */
    public function getStorageName($storageuuid, $timepointUuid)
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
            $sqlNode = "select real_node_uuid from bd_backup_timepoint where timepoint_uuid = ?";
            $dataNode = $this->dbSelect($sqlNode, array($timepointUuid));
            $nodeUuid = !empty($dataNode[0]['real_node_uuid']) ? $dataNode[0]['real_node_uuid'] : $data[0]['node_uuid'];
            $nodename = $nodeHandler->getNodeName($nodeUuid);
        }
        $name .= "\n" . "(" . $nodename . ")";
        return $name;
    }
    /**
     * 检测时间点是否在磁带上,如果在磁带上则退出不让其删除并给出提示
     */
    public function checkTimepointStorage($timepointList)
    {
        $timeDes = "'" . implode("','", $timepointList) . "'";
        $storage_type = Xphp::$_config['BD_STORAGE_TYPE']['TAPE'];  //磁带
        $sql = "SELECT bbt.timepoint_uuid, bsr.storage_uuid, bsr.storage_type FROM bd_backup_timepoint bbt JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid  
        WHERE bbt.timepoint_uuid IN ($timeDes) AND bsr.storage_type = " . $storage_type;
        $result = $this->dbSelect($sql);
        if (count($result) > 0) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_M365_SERVER_DELETE_TIME_POINT'], Xphp::$_lang['UI_TAPE_DELETE_BACKUP_POINT_TIPS'], 'warning'));
        }
        return;
    }
}
?>