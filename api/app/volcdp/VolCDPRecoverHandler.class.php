<?php
/*******************************************
 ** cdp备份任务创建
 **
 ** @author       jiangyongjie@vinchin.com
 ** @date         2021-11-30 15:44:45
 ** @version      6.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
class VolCDPRecoverHandler extends BLLHandler
{

    public function getDataSourceHostInfo($params)
    {

        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $user_uuid = Xphp::$_user['useruuid'];
        $node_uuid = $params['node_uuid'];
        $start = intval($params['start']);
        $length = intval($params['length']);
        $flag = Xphp::$_config['FLAG'];
        $available_flag = $flag['SET'];  //时间点是否可用标志位
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看

        $sql = 'select DISTINCT agent.master_agent_uuid,agent.master_agent_detail,agent.storage_location,bbt.timepoint,
                    bbt.user_uuid,bbt.task_name,bbt.task_uuid,bbt.id   
                from bd_backup_timepoint as bbt,bd_storage_resource as store,cdp_vol_backup_agent as agent
                where agent.timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = store.storage_uuid
                      and bbt.available_flag = ? and agent.storage_location !=? and agent.dev_type = ? and bbt.import_flag = ? ';
        if (!empty($authUser)) {  // 表示有管理的用户
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr . " group by bbt.task_uuid,agent.master_agent_uuid ORDER BY bbt.id";
            $sqlParams = array($available_flag, $VolCdpDes['DATA_SOURCE']['UNKNOWN'], Xphp::$_config['FLAG']['SET'], Xphp::$_config['FLAG']['UNSET']);
        } else {
            $sql .= " and bbt.user_uuid = ? group by bbt.task_uuid,agent.master_agent_uuid ORDER BY bbt.id";
            $sqlParams = array($available_flag, $VolCdpDes['DATA_SOURCE']['UNKNOWN'], Xphp::$_config['FLAG']['SET'], Xphp::$_config['FLAG']['UNSET'], $user_uuid);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $host = array();
        $host["data"] = array();
        $hostInfoList = [];
        $userUuidData = array();
        $backupSetHandler = Xphp::instance('VolCDPBackupSetHandler');
        if (!empty($data)) {
            foreach ($data as $d) {
                $masterAgentuuid = $d['master_agent_uuid'];
                $storage_location = $d['storage_location'];
                $master_agent_detail = json_decode($d['master_agent_detail']);
                $hostName = $master_agent_detail->hostname;
                $agentIp = $master_agent_detail->ip;
                $agentName = $master_agent_detail->agent_name;
                $taskName = $d['task_name'];
                $taskuuid = $d['task_uuid'];

                if (!empty($agentName) && $agentName != $agentIp) {
                    $hostInfo = $agentName ? $agentName : $hostName;
                } else {
                    $hostInfo = $hostName;
                }

                $os_type = $master_agent_detail->os_type;
                $os_version = $master_agent_detail->os_version;
                $sysIcon = '<img src ="./img/platform/linux.png">';
                if (strpos($os_version, 'Windows') !== false) {
                    $sysIcon = '<img src ="./img/platform/windows.png">';
                }
                if ($storage_location == $VolCdpDes['DATA_SOURCE']['UNKNOWN'] || $storage_location == $VolCdpDes['DATA_SOURCE']['STANDBY']) {
                    continue;
                }

                $agentInfoExists = in_array($taskuuid . $masterAgentuuid, $hostInfoList); //判断主机IP或者uuid是否已存在数组中，兼容同一主机使用不同ip或者不同主机使用相同ip的情况
                if ($agentInfoExists) {
                    continue;
                }

                $currentTaskUUID = $backupSetHandler->getCurrentAllTaskUUID();//得到当前任务所有uuid
                $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
                $taskNameStr = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";

                $host["data"][] = array(
                    '<input type="checkbox" class="editor-active" name="id[]" data-taskuuid = "' . $d['task_uuid'] . '" value="' . $masterAgentuuid . '">',
                    $hostInfo,
                    $agentIp,
                    $taskNameStr,
                    $sysIcon . $os_version,
                    $masterAgentuuid,
                    $storage_location,
                    $os_type,
                    $d['task_uuid']
                );
                $hostInfo = $taskuuid . $masterAgentuuid;
                array_push($hostInfoList, $hostInfo);
                array_push($userUuidData, $d['user_uuid']);
            }
        }
        $dataList = $host["data"];
        //判断是否具有操作权限
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['vol_cdp_protect_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], $userUuidData, $authUser);
        if (!$checkOperate) {
            $dataList = array();
        }

        $countNum = count($dataList);
        // 2023-03-24 NOTE: 分页，这里做简单修改，不改逻辑。采用内存分页，因为上面对查询记结果进行了二次筛选，不能直接对数据库进行分页
        $host['data'] = array_slice($dataList, $start, $length);
        $host["draw"] = $params['draw'];
        $host["recordsTotal"] = $countNum;
        $host["recordsFiltered"] = $countNum;
        return json_encode($host);

    }
    /**
     * 判断当前输入值是否存在二维数组中
     * @param unknown $value
     * @param unknown $array
     * @return boolean
     */
    private function deepInArray($value, $array)
    {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if (in_array($value, $item)) {
                return true;
            } else if ($this->deepInArray($value, $item)) {
                return true;
            }
        }
        return false;
    }
    /**
     * 获取生产有备份集的客户端
     * @param unknown $params
     */
    public function getDataSourceHost($params)
    {
        $user_uuid = Xphp::$_user['useruuid'];
        $sql = "select DISTINCT timepoint_uuid,master_agent_uuid,master_agent_detail,standby_agent_uuid from cdp_vol_backup_agent GROUP BY master_agent_uuid";
        $data = $this->dbSelect($sql);
        $info = array();
        foreach ($data as $d) {
            $master_agent_uuid = $d['master_agent_uuid'];
            $master_agent_detail = json_decode($d['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agent_ip = $master_agent_detail->ip;
            $info[] = array(
                'master_agent_uuid' => $master_agent_uuid,
                'host_info' => $hostname . "(" . $agent_ip . ")",
                'standby_agent_uuid' => $d['standby_agent_uuid']
            );
        }
        return json_encode($info);
    }
    /**
     * 获取选择主机的卷及时间点信息
     * @param string host_uuid
     */
    public function getHostBackupData($params)
    {
        $storageType = $params['storage_type'];
        $hostUuid = $params['host_uuid'];
        $sql = "select  vol_set.id,vol_set.vol_name,vol_set.vol_display_name,vol_set.capacity,vol_set.vol_uuid,vol_set.is_boot
                from cdp_vol_backup_agent as agent,cdp_vol_backup_vol_set as vol_set 
                where agent.id = vol_set.backup_agent_id ";
        if ($storageType == 1) {
            $exec_sql = $sql . " and agent.master_agent_uuid = '{$hostUuid}' GROUP BY vol_set.vol_uuid";
        } else {
            $exec_sql = $sql . " and agent.standby_agent_uuid = '{$hostUuid}' GROUP BY vol_set.vol_uuid";
        }

        $data = $this->dbSelect($exec_sql);
        $volumes = array();
        $volumes["data"] = array();
        $utils = Xphp::instance('Utils');
        if (!empty($data)) {
            foreach ($data as $vol_info) {
                $isBoot = $vol_info['is_boot'];
                $volUuid = $vol_info['vol_uuid'];
                $newBackupTimePoint = $this->getBackupVolLatestTime($volUuid);
                if ($isBoot == 1) {
                    $volIcon = '<img src ="./img/platform/windows.png">';
                } else {
                    $volIcon = '<img src = "./img/vm/pool.png">';
                }
                $icon = '<img src ="./img/platform/timepoint.png"> ';
                if ($storageType == 1) {
                    $modifyIcon = '<img style = "float:right;" src ="./img/cdp/modify-timepoint.png" id="' . $volUuid . '" class = "mdTimepoint" name = "mdTimepoint" title = "' . Xphp::$_lang['WEB_VOL_CDP_MODIFY_RECOVERY_TIME_POINT'] . '">';
                } else {
                    $modifyIcon = "";
                }
                $parseDateInfo = $this->parseDate($newBackupTimePoint);
                $volcapacitySize = $utils->calSize($vol_info['capacity'], true);
                $timePointHtml = '<span id ="timepoint_' . $volUuid . '">' . $parseDateInfo . '</span>';
                $volNameHtml = '<span id = "volname_' . $volUuid . '">' . $vol_info['vol_display_name'] . '</span>';
                $volcapacity = '<span id = "volcapacity_' . $volUuid . '">' . $volcapacitySize . '</span>';
                $volumes["data"][] = array(
                    '<input type="checkbox" class="editor-active" name="id[]" value="' . $volUuid . '">',
                    $volIcon . $volNameHtml,
                    $volcapacity,
                    $icon . $timePointHtml . $modifyIcon,
                    $vol_info['id'],
                    $volUuid,
                    $this->parseDate($newBackupTimePoint),
                    $newBackupTimePoint,
                    $hostUuid,
                    $vol_info['capacity'],
                    $vol_info['vol_name']
                );
            }
        }
        $countNum = count($data);
        $volumes["draw"] = $params['draw'];
        $volumes["recordsTotal"] = $countNum;
        $volumes["recordsFiltered"] = $countNum;
        return json_encode($volumes);
    }

    /**
     * 获取当前客户端下卷的最新备份时间点
     * @param vol_uuid,host_uuid
     */
    private function getBackupVolLatestTime($volUuid)
    {
        $sql = "select unix_timestamp(start_timestamp) start_time,unix_timestamp(end_timestamp) end_time 
               from cdp_vol_backup_vol_set where vol_uuid = '{$volUuid}' ORDER BY end_timestamp DESC";
        $data = $this->dbSelect($sql);
        $timestamp = $data[0]['end_time'];
        return $timestamp;
    }

    /**
     * 获取存储对象备机信息
     * @param  host_uuid:data source host uuid
     */
    public function getStandbyStorageInfo($params)
    {
        $hostUuid = $params['host_uuid'];
        $sql = "select DISTINCT standby_agent_uuid,standby_agent_detail from cdp_vol_backup_agent where master_agent_uuid = '{$hostUuid}'";
        $data = $this->dbSelect($sql);
        $info = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $standbyAgentUuid = $d['standby_agent_uuid'];
                $standbyAgentDetail = json_decode($d['standby_agent_detail']);
                $hostname = $standbyAgentDetail->hostname;
                $agent_ip = $standbyAgentDetail->agent_ip;
                if ($standbyAgentUuid != "") {
                    $info[] = array(
                        'standby_agent_uuid' => $standbyAgentUuid,
                        'standby_agent_info' => $hostname . "(" . $agent_ip . ")"
                    );
                }
            }
        }
        return json_encode($info);
    }
    /**
     * 获取区间范围内的标签点信息
     * @param unknown $startTime
     * @param unknown $finishTime
     * @return fetchAll()
     */
    private function getTimeIntervalData($startTime, $finishTime)
    {
        $sql = "select label.id,label.label_timestamp,label.remarks,agent.storage_location
                    from cdp_vol_agent_label_set as label,cdp_vol_backup_agent as agent
                    where agent.id = label.backup_agent_id and agent.master_agent_uuid =?
                        and label.label_timestamp BETWEEN '" . $startTime . "' and '" . $finishTime . "'";
        return $sql;
    }
    /**
     * 获取选择客户端及卷对应的标签点信息
     * @param host_uuid:选择主机UUID,vol_uuid:当前选中卷
     */
    public function getVolTagPointInfo($params)
    {
        $hostUuid = $params['host_uuid'];
        $timeList = $params['time_list']; //通过时间轴查看标签点的详情
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'label.label_timestamp');
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = Xphp::$_user['useruuid'];
        $taskuuid = $params['task_uuid'];
        if (count($timeList) > 0) {
            $confTime = $timeList[0]['confTime'];
            $timeInterval = $timeList[0]['timeInterval'];
            if ($timeInterval == 1 || $timeInterval == 2) { //秒级时间表
                $sql = "select label.id,label.label_timestamp,label.remarks,agent.storage_location  
                    from cdp_vol_agent_label_set as label,cdp_vol_backup_agent as agent, bd_backup_timepoint bt 
                    where agent.id = label.backup_agent_id and agent.master_agent_uuid =? and label.label_timestamp = ? 
                      and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bt.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and bt.user_uuid = '{$useruuid}' ";
                }
                $sqlCount = $sql . " order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid, $confTime, $taskuuid, $start, $length);
                $countParams = array($hostUuid, $confTime, $taskuuid);

                $data = $this->dbSelect($sql, $sqlParams);
                $countData = $this->dbSelect($sqlCount, $countParams);

            } else if ($timeInterval == 3 || $timeInterval == 4) {  //分钟级表
                $minDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_minute_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                      and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $minDataSql .= " and bt.user_uuid in " . $useruuidArr;
                } else {
                    $minDataSql .= " and bt.user_uuid = '{$useruuid}' ";
                }

                $minData = $this->dbSelect($minDataSql, array($hostUuid, $confTime, $taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];
                $sql = $this->getTimeIntervalData($startTimestamp, $confTime);
                $sqlCount = $sql . " order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid, $start, $length);
                $countParams = array($hostUuid);
                $data = $this->dbSelect($sql, $sqlParams);
                $countData = $this->dbSelect($sqlCount, $countParams);

            } else if ($timeInterval == 5 || $timeInterval == 6) { //小时级表
                $hourDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_hour_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                      and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $hourDataSql .= " and bt.user_uuid in " . $useruuidArr;
                } else {
                    $hourDataSql .= " and bt.user_uuid = '{$useruuid}' ";
                }
                $minData = $this->dbSelect($hourDataSql, array($hostUuid, $confTime, $taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];

                $sql = $this->getTimeIntervalData($startTimestamp, $confTime);
                $sqlCount = $sql . " order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid, $start, $length);
                $countParams = array($hostUuid);

                $data = $this->dbSelect($sql, $sqlParams);
                $countData = $this->dbSelect($sqlCount, $countParams);
            }
        } else {
            $sql = "select label.id,label.label_timestamp,label.remarks,agent.storage_location
                from cdp_vol_agent_label_set as label,cdp_vol_backup_agent as agent,bd_backup_timepoint bt 
                where agent.id = label.backup_agent_id and agent.master_agent_uuid = ? and 
                      agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bt.user_uuid in " . $useruuidArr;
            } else {
                $sql .= " and bt.user_uuid = '{$useruuid}' ";
            }
            $sqlCount = $sql . " order by $sortArr[$sortColumn] $sortType";
            $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

            $sqlParams = array($hostUuid, $taskuuid, $start, $length);
            $countParams = array($hostUuid, $taskuuid);

            $data = $this->dbSelect($sql, $sqlParams);
            $countData = $this->dbSelect($sqlCount, $countParams);

        }
        $volumes = array();
        $icon = '<img src ="./img/platform/timepoint.png"> ';
        $volumes["data"] = array();
        $utils = Xphp::instance('Utils');
        if (!empty($data)) {
            $j = 1;
            foreach ($data as $d) {
                $index_id = $j++;
                $label_id = $d['id'];
                $label_time_str = $d['label_timestamp'];
                $description = $d['remarks'];
                $storage_location = $d['storage_location'];
                $volumes["data"][] = array(
                    '<input type="checkbox" class="editor-active" name="id[]" id= "backupSetTag" value="' . $label_time_str . '">',
                    $index_id,
                    $icon . $label_time_str,
                    $description,
                    $label_time_str,
                    $storage_location,
                    $label_id,
                );
            }
        }
        $countNum = count($countData);
        $volumes["draw"] = $params['draw'];
        $volumes["recordsTotal"] = $countNum;
        $volumes["recordsFiltered"] = $countNum;
        return json_encode($volumes);
    }


    /**
     * 获取接管目标客户端
     * @param unknown $params
     */
    public function getRecoveryTargetHost($params)
    {
        $userUuid = Xphp::$_user['useruuid'];
        $nodeuuid = $params['node_uuid'];
        $masterAgentuuid = $params['master_uuid'];
        $masterOsType = $params['master_os_type'];
        $standbyuuid = $params['standby_uuid'];
        $clientHandler = Xphp::instance('ClientHandler');
        $agentuuidArr = $clientHandler->getClientUuids();
        $list = array();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  (agent_type =? or agent_type = ?) ";

        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr} ";
        }

        if (!empty($standbyuuid)) {
            $sql .= " and agent_uuid =?";
            $sqlParams = array(Xphp::$_config['AGENT_TYPE']['NORMAL'], Xphp::$_config['AGENT_TYPE']['MEMORY_OS'], $standbyuuid);
        } else {
            $sqlParams = array(Xphp::$_config['AGENT_TYPE']['NORMAL'], Xphp::$_config['AGENT_TYPE']['MEMORY_OS']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $list[] = array(
            "uuid" => "",
            "text" => Xphp::$_lang['UI_VOL_CDP_TAKEOVER_TARGET_MACHINE_SELECT'],
            "value" => 0,
            "os_type" => "",
            "in_task" => false,
            "task_name" => "",
            "vol_info" => [],
        );
        foreach ($data as $host_info) {
            //如果节点状态正常
            $agentUUID = $host_info['agent_uuid'];
            $agentType = $host_info['agent_type'];
            $osType = $host_info['os_type'];
            $onlineFlag = $host_info['online_flag'];

            if ($agentType != Xphp::$_config['AGENT_TYPE']['MEMORY_OS'] && $osType != $masterOsType) {
                continue;
            }
            $inTask = false;
            $task_name = " ";
            $isCreateTask = $this->taskExist($agentUUID);
            if (count($isCreateTask) > 0) {
                $task_name = $isCreateTask['task_name'];
                $inTask = true;
            }
            $hostVolInfo = $this->getHostVolInfoByUUID($agentUUID, $osType);

            $title = Xphp::$_lang['UI_VOL_CDP_HOST'];
            $hostDec = $this->agentStr($host_info['agent_name'], $host_info['hostname'], $host_info['ip']);
            if ($host_info['agent_type'] == Xphp::$_config['AGENT_TYPE']['MEMORY_OS']) {
                $title = Xphp::$_lang['UI_VOL_CDP_STANDBY'];
                //                 $hostDec =  $host_info['hostname']." (". $host_info['ip'] .")"."--".$statusDes;
            }
            if (Xphp::$_config['FLAG']['SET'] == $onlineFlag) {
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
            } else {
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
                $hostDec = $hostDec . "--" . $statusDes;
            }

            $list[] = array(
                'uuid' => $agentUUID,
                'text' => $hostDec,
                'value' => $host_info['agent_uuid'],
                'os_type' => $osType,
                'in_task' => $inTask,
                'task_name' => $task_name,
                'vol_info' => $hostVolInfo['vol_info'],
                'disk_info' => $this->getHostDiskInfo($agentUUID),
                'mount_info' => $hostVolInfo['mount_info'],
                'agent_type' => $host_info['agent_type'],
                'net_model' => intval($host_info['net_model']),
                'title' => $title,
            );
        }
        return json_encode($list);
    }

    /**
     * 检查选择的恢复目标是否有被其它恢复任务选中
     * -----当前仅限制是否有恢复作业，后续会完善到是否有备份作业、恢复作业是否处于运行中等
     * @param unknown $agentUuids
     */
    private function taskExist($agentUUID)
    {
        $sql = "SELECT task.task_status,task.task_name from bd_task task,cdp_vol_task vol_task
            where vol_task.task_uuid = task.task_uuid and task.task_type = ? and vol_task.recovery_target_agent_uuid = ? and task.delete_flag = ? ";
        $taskType = Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'];
        $delFlag = Xphp::$_config['FLAG']['UNSET'];
        $data = $this->dbSelect($sql, array($taskType, $agentUUID, $delFlag));
        $taskList = array();
        if (!empty($data)) {
            $taskList = array(
                'task_name' => $data[0]['task_name'],
            );
        }
        return $taskList;
    }
    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    private function getHostVolInfoByUUID($uuid, $osType)
    {
        $utils = Xphp::instance('Utils');
        //获取客户端类型
        $agentSql = "select agent_type from bd_agent where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($uuid));
        $agentType = 1;
        if (!empty($data)) {
            $agentType = $data[0]['agent_type'];
        }

        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol
                where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $list = array();
        $HostMountPointArry = array();
        foreach ($data as $v) {
            $detail = json_decode($v['detail']);
            $mountPoint = $v['mount_point'];
            $volName = $v['vol_name'];
            $volTypeValue = $detail->vol_type;
            $canSelect = true;

            //保留分区、恢复分区、pv分区、扩展分区
            if (
                $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_PV_VOLUME']
                || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_EXTEND_VOLUME']
            ) {
                continue;
            }
            //            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if ($volTypeValue & $VolCdpDes['VOL_TYPE']['BD_SWAP_VOLUME']) {
                continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName, "live-");
            if ($pos !== false) {
                continue;
            }
            if (
                ($volTypeValue & $VolCdpDes['VOL_TYPE']['BD_BOOT_VOLUME'] || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_SYSTEM_VOLUME']
                    || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_EFI_VOLUME']) && $agentType != Xphp::$_config['AGENT_TYPE']['MEMORY_OS']
            ) {
                $canSelect = false;
            }

            array_push($HostMountPointArry, $v['mount_point']);
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" => $utils->calSize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "is_boot" => $v['is_boot'],
                "display_name" => $v['display_name'],
                "agent_type" => $agentType,
                "mount_point" => $mountPoint,
                'can_select' => $canSelect,
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
        $utils = Xphp::instance('Utils');
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
                "capacity" => $utils->calSize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "display_name" => $v['display_name']
            );
        }
        return $list;
    }

    /**
     * 获取数据源存储节点信息
     * @param: vol_uuid,time_point
     */
    public function getDataSourceNodeInfo($params)
    {
        $vol_uuid = $params['vol_uuid'];
        $timepoint = $params['timepoint'];
        $timestamp = strtotime($timepoint);
        $sql = "select DISTINCT st.node_uuid,st.storage_uuid 
                from cdp_vol_backup_vol_set as vol_set, cdp_vol_backup_agent as bk_agent,
                     bd_backup_timepoint as timepoint,bd_storage_resource as st 
                where vol_set.vol_uuid = ? 
                    and (unix_timestamp(vol_set.start_timestamp) <= ? or unix_timestamp(vol_set.start_timestamp) >=?)
                    and vol_set.backup_agent_id = bk_agent.id and bk_agent.timepoint_uuid = timepoint.timepoint_uuid 
                    and timepoint.storage_uuid = st.storage_uuid ";
        $data = $this->dbSelect($sql, array($vol_uuid, $timestamp, $timestamp));
        $info = array();
        if (!empty($data)) {
            $node_uuid = $data[0]['node_uuid'];
            $info[] = array(
                "node_uuid" => $node_uuid,
                "data_is_valid" => true
            );
        } else {
            $info[] = array(
                "node_uuid" => "",
                "data_is_valid" => false
            );
        }
        return json_encode($info);
    }
    /**
     * 创建恢复作业
     * @param：object
     */
    public function createRecoverJob($params)
    {
        $task_name = htmlspecialchars_decode($params['taskName']);
        $strategy_group_uuid = $params['strategygroupuuid'];
        $vmHandler = Xphp::instance('Vmhandler');
        $time_strategy_list = $vmHandler->groupRecoverTimeList($params['timeInfo'], $strategy_group_uuid);//组合时间策略
        $module_type = Xphp::$_config['MODULE_TYPE']['VOL_CDP'];
        $master_agent_uuid = $params['master_agent_uuid'];
        $standby_agent_uuid = $params['standby_agent_uuid'];  //数据源来自于备机,

        $recovery_position = $params['recovery_position'];
        $restore_data_source = $params['restore_data_source'];  //恢复数据来源
        $restoreType = $params['restoreType'];  //恢复类型
        $rebuild_partition_flag = $params['rebuild_partition_flag']; //是否重建分区

        $node_uuid = $params['node_uuid'];
        $recovery_target_agent_uuid = $params['recovery_target_agent_uuid'];
        $recovery_object = $params['recovery_object'];
        $speedInfo = $params['speedInfo'];
        $highInfo = $params['highInfo'];
        $thread_num = $highInfo['threadnum'];
        $transport_strategy = $params['highInfo']['transfer'];
        $recovery_time_type = intval($params['timeInfo']['type']);  //得到恢复方式

        $pfMSg = $this->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']; //作业类型
        $pfMSg['dev_type'] = 1;   //备份类型 1卷 2磁盘
        $pfMSg['thread_num'] = $thread_num;
        $pfMSg['strategy_group_uuid'] = $strategy_group_uuid;
        $pfMSg['rebuild_partition_flag'] = $rebuild_partition_flag;
        $pfMSg['restore_data_source'] = $restore_data_source;
        $pfMSg['master_agent_uuid'] = $master_agent_uuid;
        $pfMSg['standby_agent_uuid'] = $standby_agent_uuid;
        $pfMSg['recovery_target_agent_uuid'] = $recovery_target_agent_uuid;
        $pfMSg['recovery_object'] = $recovery_object;
        $pfMSg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($speedInfo, $strategy_group_uuid);
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategy_group_uuid;
        $pfMSg['timepoint_uuid'] = $params['timepoint_uuid'];
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVolCdpMsg($node_uuid, $opName, $msg);
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
     * 得到卷CDP恢复任务名
     * @param unknown $params
     */
    public function getVolCdpRecoverTaskName($params)
    {
        $taskName = $params['task_name'];
        $newTaskName = $this->getValidTaskName($taskName);
        return html_entity_decode($newTaskName);
    }
    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    private function getValidTaskName($taskName)
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
     * 将选中的客户端对应的卷信息进行组装（从JS移到PHP处理是为了满足全选需求）
     * @param $params
     * @return false|string
     */
    public function getDataSourceVolInfo($params)
    {
        $data = $params['volinfo'];
        $start = $params['start'];
        $length = $params['length'];
        $volumesInfo = array();
        $i = 0;
        foreach ($data as $d) {
            $voluuid = $d['vol_uuid'];
            $volName = $d['vol_name'];
            $capacity = $d['capacity'];
            $capacityValue = $d['capacity_value'];
            $osType = $d['os_type'];
            $timePoint = $d['new_timestamp'];
            $timeIcon = '<img src ="./img/platform/timepoint.png"> ';
            $volIcon = '<img src ="./img/vm/pool.png"> ';
            $standbyVol = $d['standby_vol'];
            $isBootVolume = $d['is_boot']; //系统卷类型：0 非系统卷，1：系统卷
            $isDisabled = "";
            $targetInfo = '<select class="form-control input-sm" name="targetvol" id=' . "target_volume_" . $i . ' >
					<option value = "' . $capacityValue . '" data-time ="' . $timePoint . '" 
					data-option ="0" data-hostvoluuid="' . $voluuid . '" data-isbootvol = "' . $isBootVolume . '" >' . Xphp::$_lang['UI_VOL_CDP_SELECT_RECOVER_TARGET_VOL'] . ' </option></select>';
            $volumesInfo[] = array(
                '<input type="checkbox"' . $isDisabled . ' class="editor-active" name = "recoveryVolume" id="recovery_vol_id_' . $i . '" value="' . $voluuid . '">',
                $volIcon . $volName,
                $capacity,
                $timeIcon . '<span id = "recovery_time_' . $voluuid . '">' . $timePoint . '</span>',
                $targetInfo,
                $timePoint,
                $voluuid,
                $volName,
                $osType,
                $isBootVolume,
            );
            $i += 1;
        }
        $sliceArray = array_slice($volumesInfo, $start, $length, false);
        $volumes['data'] = $sliceArray;

        $countNum = count($volumesInfo);
        $volumes["draw"] = $params['draw'];
        $volumes["recordsTotal"] = $countNum;
        $volumes["recordsFiltered"] = $countNum;
        return json_encode($volumes);
    }

}