<?php
/*******************************************
 ** cdp备份数据集关联
 **
 ** @author       jiangyongjie@vinchin.com
 ** @date         2022-1-7 17:32:22
 ** @version      1.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
class VolCDPBackupSetHandler extends BLLHandler{
    /**
     * 获取当前节点下备份客户端
     */
    public function getBackupSetTree ($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $useruuid = Xphp::$_user['useruuid'];
        $flag = Xphp::$_config['FLAG'];
        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];

        $taskSql = "select DISTINCT bbt.timepoint_uuid,bbt.task_name,bbt.task_uuid ,bbt.task_create_time  
                    from bd_backup_timepoint bbt,bd_storage_resource bsr 
                    WHERE bbt.deleted_flag = ? and bbt.available_flag =? and bbt.module_type = ? 
                        and bbt.storage_uuid = bsr.storage_uuid ";
        $taskSqlParams = array($deleted_flag, $available_flag, Xphp::$_config['MODULE_TYPE']['VOL_CDP']);
        if ($nodeuuid){     //如果是选择了某个节点,显示这个节点下面的
            $taskSql .= " and bsr.node_uuid = ? ";
            array_push($taskSqlParams, $nodeuuid);
        }
        $taskSql .= " group by bbt.task_uuid order by bbt.task_name desc";
        $taskData = $this->dbSelect($taskSql, $taskSqlParams);

        $filter = false;
        $filterStr = $params['search_info'];
        if( $filterStr!=""){
            $filter = true;
        }
        $jobHandler = Xphp::instance('JobHandler');
        $currentTaskUUID = $this->getCurrentAllTaskUUID();//得到当前任务所有uuid
        $tree = array();
        if(!empty($taskData)){
            $tree[] = array(
                "id" => $nodeuuid,
                "title"=> Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DATA_CENTER'],
                "pId"=> "",
                "isParent" =>true,
                "icon" => "./img/platform/vmgroup.png",
                "name" => Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DATA_CENTER'],
                "open" =>  true,
                "nocheck" => true,
            );
        }else{
            return json_encode($tree);
        }
        $isShow = false;
        foreach ($taskData as $d){
            $taskuuid = $d['task_uuid'];
            $taskName = $this->getTimepointTaskname($d['task_uuid'],$d['task_name']);
            //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
            $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
            $taskNameStr = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
            $taskNameFlag = false;
            if(strpos($taskNameStr,$filterStr)!==false){
                $taskNameFlag = true;
            }
            //第二层 任务名称
            $taskArray= array(
                "id" => $nodeuuid.$d['task_uuid'],
                "pId" => $nodeuuid,
                "name" => $taskNameStr,
                "open" => false,
                "nocheck" => true,
                "type" => 0,
                "icon" => './img/platform/flag.png',
                "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . $d['task_create_time'],
                "isParent" => true,
            );
            $childTree = $this->getBackupAgentSetInfo($taskuuid,$nodeuuid,$useruuid,$filterStr);
            if(count($childTree)>0){
                $tree[] = $taskArray;
                $tree = array_merge($tree, $childTree);
                $isShow = true;
            } 
        }
        if(!$isShow){
            $tree = array();
        }
        return json_encode($tree);
    }
    /**
     * 获取客户端对应的备份集信息，通过任务uuid
    * @return void
     */
    private function getBackupAgentSetInfo($taskuuid,$nodeuuid,$useruuid,$filterStr){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $flag = Xphp::$_config['FLAG'];
        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        $filter = false;
        if( $filterStr!=""){
            $filter = true;
        }
        $sql = "SELECT distinct agent.master_agent_uuid,agent.master_agent_detail,agent.id,bsr.node_uuid,bbt.task_name,
                    bbt.task_uuid,unix_timestamp(bbt.task_create_time) task_create_time
                FROM bd_backup_timepoint bbt,cdp_vol_backup_agent agent,bd_storage_resource bsr
                WHERE bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
                    and bbt.deleted_flag = ? and bbt.available_flag = ? and bbt.module_type = ? 
                    and agent.storage_location !=? and agent.storage_location !=?   and bbt.task_uuid = ? ";
        $sqlParams = array($deleted_flag, $available_flag, Xphp::$_config['MODULE_TYPE']['VOL_CDP'],$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$VolCdpDes['DATA_SOURCE']['STANDBY'],$taskuuid);
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            if (!empty($authUser)) { // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = ?  ";
                array_push($sqlParams, $useruuid);
            }
        }
        if ($nodeuuid){     //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ? ";
            array_push($sqlParams, $nodeuuid);
        }
        $sql .= " group by agent.master_agent_uuid order by agent.id desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();
        $hostInfoList = [];
        foreach ($data as $d){
            $agentInfoExists = false;
            $agentUUID = $d['master_agent_uuid'];
            $agentDetail = $this->parseBackupAgentDetail($d['master_agent_detail']);
            $agentName = $agentDetail['agent_name'];
            $agentIp = $agentDetail['agent_ip'];
            $hostName = $agentDetail['hostname'];

            //判断主机IP或者uuid是否已存在数组中，兼容同一主机使用不同ip或者不同主机使用相同ip的情况
            $agentInfoExists = in_array($taskuuid.$agentUUID, $hostInfoList);
            if($agentInfoExists ){
                continue;
            }
            $strposFlag = false;
            if(strpos($agentIp,$filterStr)!==false){
                $strposFlag = true;
            }

            $hostNameFlag = false;
            if(strpos($hostName,$filterStr)!==false){
                $hostNameFlag = true;
            }
            $agentNameFlag = false;
            if(strpos($agentName,$filterStr)!==false){
                $agentNameFlag = true;
            }
            if($filter && !$strposFlag && !$hostNameFlag && !$agentNameFlag){  //筛选且筛选内容未包含在$agentName,$agentIp,$hostName中时
                continue;
            }
            $os_type = $agentDetail['os_type'];
            $backupSetNodeUuid = $d['node_uuid'];
            $type_icon = "./img/platform/linux.png";
            if($os_type=='Windows'){
                $type_icon = "./img/platform/windows.png";
            }
            $agentUUID = $d['master_agent_uuid'];
            $treeId = $nodeuuid.$taskuuid.$d['master_agent_uuid'].$agentIp;
            $treePid =$nodeuuid.$taskuuid;
            $tree[] = array(
                "id" => $treeId,
                "pId" => $treePid,
                "name" =>  $this->agentStr($agentName,$hostName,$agentIp),
                "title" => Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_CLIENT_IP'].":".$agentIp,
                "isParent" => true,
                "agent_uuid" => $agentUUID,
                "node_uuid" =>$backupSetNodeUuid,
                "isApp" =>false,
                "type" => 1,
                "icon" => $type_icon,
                "clickshow" => false,
                "checked" => false,
                "chkDisabled" => false,
                "open" =>  true,
                "host_ip" => $agentIp,
                'task_uuid' => $taskuuid
            );
            $childTree = $this->getBackupVolData($agentUUID,$treeId,$nodeuuid,$backupSetNodeUuid,$agentIp,$taskuuid);
            $tree = array_merge($tree, $childTree);
            $hostInfo = $taskuuid.$agentUUID;
            array_push($hostInfoList,$hostInfo);
        }
       
        return $tree;
    }
    /**
     * 获取时间点对应任务名
     */
    private function getTimepointTaskname($taskuuid, $taskname){
        $sql = "select distinct task_name from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            $name = $data[0]['task_name'];
        }else{
            $name = $taskname;
        }

        return $name;
    }
    /**
     * 获取任务关联的客户端信息
     * @return void
     */
    private function getBackupHostData($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $useruuid = Xphp::$_user['useruuid'];
        $flag = Xphp::$_config['FLAG'];
        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志位

        $sql = "SELECT agent.master_agent_uuid,agent.master_agent_detail,agent.id,bsr.node_uuid 
                FROM bd_backup_timepoint bbt,cdp_vol_backup_agent agent,bd_storage_resource bsr
                WHERE bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
                    and bbt.deleted_flag = ? and bbt.available_flag = ? and bbt.module_type = ? 
                    and agent.storage_location !=?";
        $sqlParams = array($deleted_flag, $available_flag, Xphp::$_config['MODULE_TYPE']['VOL_CDP'],$VolCdpDes['DATA_SOURCE']['UNKNOWN']);
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            if (!empty($authUser)) { // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = ?  ";
                array_push($sqlParams, $useruuid);
            }
        }

        if ($nodeuuid){     //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?  ";
            array_push($sqlParams, $nodeuuid);
        }
        $sql .= "  order by agent.id desc ";
        $data = $this->dbSelect($sql, $sqlParams);




    }
    /**
     * 获取客户端对应的备份卷信息
     * @param $agentUUID:选中节点UUID,$treeId:父节点ID,$nodeuuid:存储节点uuid
     */
    private  function getBackupVolData($agentUUID,$treeId,$nodeuuid,$backupSetNodeUuid,$parentAgentIp,$taskuuid){
        $flag = Xphp::$_config['FLAG'];
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        
        $utils = Xphp::instance('Utils');
        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志位
        $useruuid = Xphp::$_user['useruuid'];
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        $sql = 'SELECT vol.vol_display_name,vol.vol_uuid,vol.capacity,vol.is_boot,bbt.encrypted_flag,agent.storage_location,vol.vol_name,bsr.node_uuid,agent.master_agent_detail 
                FROM bd_backup_timepoint bbt,cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol,bd_storage_resource bsr
                WHERE bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and
                      vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and bbt.deleted_flag = ? and
                      bbt.available_flag = ? and bbt.module_type = ? and agent.storage_location !=? and agent.storage_location !=?  and bbt.task_uuid = ?';
        
        $sqlParams = array($agentUUID,$deleted_flag, $available_flag, Xphp::$_config['MODULE_TYPE']['VOL_CDP'],$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$VolCdpDes['DATA_SOURCE']['STANDBY'],$taskuuid);
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            if (!empty($authUser)) {
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = ?  ";
                array_push($sqlParams, $useruuid);
            }
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $volList = [];
        foreach ($data as $d){
            $id =  $treeId.$d['vol_uuid'].$parentAgentIp;
            $volCapacity = $utils->calSize($d['capacity'], true);
            $isBoot = $d['is_boot'];
            $isBootStr = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DATA_VOL'];
            if($isBoot == $flag['SET']){
                $isBootStr = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_SYSTEM_VOL'];
            }
            $storageLocation = $d['storage_location'];
            $masterAgentDetail = $d['master_agent_detail'];
            $agentDetail = $this->parseBackupAgentDetail($d['master_agent_detail']);
            $agentName = $agentDetail['agent_name'];
            $agentIp = $agentDetail['agent_ip'];
            $hostName = $agentDetail['hostname'];

            if($agentIp!=$parentAgentIp){
                continue;
            }
            $exists = in_array($d['vol_name'], $volList); //判断主机IP是否已存在数组中
            if($exists){
                continue;
            }
            $info[] = array(
                "id" => $id,
                "pId" => $treeId,
                "name" =>$d['vol_display_name']."(".$isBootStr."，".Xphp::$_lang['UI_PUBLIC_CAPACITY'].":".$volCapacity.")",
                "title" => $d['vol_display_name']."(".$isBootStr."，".Xphp::$_lang['UI_PUBLIC_CAPACITY'].":".$volCapacity.")",
                "vol_name" => $d['vol_name'],
                "uuid" => $d['vol_uuid'],
                "icon" => "./img/vm/pool.png",
                'node_uuid' =>$backupSetNodeUuid,
                "clickshow" => false,
                "agent_uuid" => $agentUUID,
                "checked" => false,
                "chkDisabled" => false,
                "host_ip" => $parentAgentIp,
                'task_uuid' => $taskuuid,
            );
            array_push($volList,$d['vol_name']);
        }
        return $info;
    }
    /**
     * 得到当前所有任务的UUID
     * return array
     */
    public function getCurrentAllTaskUUID(){
        $taskuuid = array();
        $sql = "select task_uuid from bd_task where module_type = ? and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VOL_CDP'],Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d){
            $taskuuid[] = $d['task_uuid'];
        }
        return $taskuuid;
    }
    /**
     * 获取恢复数据源主机信息
     */
    private function parseBackupAgentDetail($detail){
        $bkAgentArry = array();
        if(!empty($detail)){
            $master_agent_detail = json_decode($detail);
            $agent_name = $master_agent_detail->agent_name;
            $host_name = $master_agent_detail->hostname;
            $agent_ip = $master_agent_detail->ip;
            $os_type = $master_agent_detail->os_type; 
            if($agent_ip==""){
                $agent_ip = "--";
            }
            $bkAgentArry = array(
                "agent_name" =>$agent_name,
                "agent_ip" => $agent_ip,
                "os_type" => $os_type,
                "hostname" => $host_name,
            );
        }
        return $bkAgentArry;
    }
    /**
     * 获取指定客户端对应的备份标签点信息
     */
    public function getBackupTagPointGrid($params){
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $nodeuuid = $params['nodeuuid'];
        $useruuid = Xphp::$_user['useruuid'];
        $sortArr = array('', 'label.label_timestamp', '', '', '','bbt.remarks', '', 'bbt.importance_flag');
        $flag = Xphp::$_config['FLAG'];
        $sql = "select label.id,bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(label.label_timestamp) label_timepoint,bbt.data_local_flag,
                    bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.task_name,bbt.task_uuid,bbt.backup_mode,label.remarks,label.backup_agent_id
                from bd_backup_timepoint bbt,cdp_vol_backup_agent agent,cdp_vol_agent_label_set as label,bd_storage_resource bsr
                where bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and agent.master_agent_uuid =?  
                    and agent.id = label.backup_agent_id and bbt.deleted_flag = ?  and bbt.task_uuid = ? ";
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        if (!empty($authUser)) {  // 表示有管理的用户
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
        }else{
            $sql .= " and bbt.user_uuid = '{$useruuid}'";
        }
        if($nodeuuid!=""){
            $sql = $sql . " and bsr.node_uuid = ? ";
            $sqlParams = array($agentuuid,$flag['UNSET'],$taskuuid,$nodeuuid);
            $countSqlParams = array($agentuuid,$flag['UNSET'],$nodeuuid,$taskuuid);
        }else{
            $sqlParams = array($agentuuid,$flag['UNSET'],$taskuuid);
            $countSqlParams = array($agentuuid,$flag['UNSET'],$taskuuid);
        }
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array_merge($sqlParams, array($start, $length));
        if($accurateFlag){
            $search = $params['search'];
            $timepointType = intval($search['timepointType']);
            $starFlag = intval($search['starFlag']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            
            $sql .= " and (bbt.importance_flag = ". $starFlag ." or ". $starFlag . " = '')";
            //如果填了开始时间范围查询
            if($startTime && $endTime){
                $sql .= " and label.label_timestamp >= '" . $startTime . "' and label.label_timestamp <= '" . $endTime . "' ";
            } elseif ($startTime) {
                $sql .= " and label.label_timestamp >= '" . $startTime . "' ";
            } elseif ($endTime) {
                $sql .= " and label.label_timestamp <= '" . $endTime . "' ";
            }
        }
        $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";


        
        $data = $this->dbSelect($sql, $sqlParams);
        $countData = $this->dbSelect($sqlCount,$countSqlParams);
        
        $utils = Xphp::instance('Utils');
        $records = array("data" => array());
        $i = 1;
        if(!empty($data)){
            foreach ($data as $d){
                $op = array(1, 2); //1:修改备注;2:删除备份集
                if($d['archive_flag'] == Xphp::$_config['FLAG']['SET']){ //时间点在合并中，不让删除
                    $op = array(1);
                }
                $remarks = $d['remarks'];
                $records["data"][] = array(
                    $i++,
                    '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['label_timepoint']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                    $d['task_name'],
                    $this->getStorageName($d['storage_uuid']),
                    $remarks,
                    $op,
                    array(
                        'uuid'=>$d['timepoint_uuid'],
                        'agentuuid'=>$agentuuid,
                        'node_uuid' =>$nodeuuid,
                        'remote_flag' => !$utils->parseFlagToBool(intval($d['data_local_flag'])),
                        'task_uuid' =>$d['task_uuid'],
                        'time_point' =>$this->parseDate($d['label_timestamp']),
                        'task_name' =>$d['task_name'],
                        'lable_id' =>$d['id'],
                    ),
                    $d['backup_agent_id'],
                    $this->parseDate($d['label_timepoint'])
                );
            }
        }
        $countNum = count($countData);
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $countNum;
        $records["recordsFiltered"] = $countNum;
        return  json_encode($records);
    }
    /**
     * 获取指定客户端选择卷对应的备份数据集
     */
    public function getBackupSetGrid($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $nodeuuid = $params['nodeuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $vol_uuid = $params['vol_uuid'];
        $agentuuid = $params['agentuuid'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $volName = $params['vol_name'];
        $volHostIP = $params['host_ip'];
        $useruuid = Xphp::$_user['useruuid'];
        $taskuuid = $params['taskuuid'];
        $sortArr = array('vol.start_timestamp', 'vol.end_timestamp', 'bbt.total_size', 'bbt.write_size', '','bbt.remarks', '', 'bbt.importance_flag');
        
        $sql = "SELECT bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(vol.start_timestamp) start_timepoint,vol.capacity,
                    bbt.encrypted_flag,unix_timestamp(vol.end_timestamp) end_timepoint,bbt.data_local_flag,bbt.importance_flag,
                    bbt.remarks,bbt.archive_flag,bbt.task_name,bbt.task_uuid,bbt.backup_mode,vol.id,vol.backup_file_size,
                    vol.log_file_total_size,vol.storage_status,agent.storage_location,bsr.node_uuid,agent.master_agent_detail  
                FROM bd_backup_timepoint bbt, cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol,bd_storage_resource bsr
                WHERE bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and 
                      agent.master_agent_uuid =? and vol.vol_uuid = ? and vol.backup_agent_id = agent.id and 
                      bbt.deleted_flag = ? and vol.vol_name = ? and bbt.task_uuid = ?";
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        $userLevel = $_SESSION['userLevel'];
        if($userLevel!=Xphp::$_config['THREE_POWERS_USER']['admin']){
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}'";
            }
        }

        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($agentuuid,$vol_uuid,$flag['UNSET'],$volName,$taskuuid);
        if($nodeuuid){
            $sql .=" and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $accurateFlag = $params['accurateFlag'];
        $sqlCountParams = $sqlParams;
        
        $sqlParams = array_merge($sqlParams, array($start, $length));
        if($accurateFlag){
            $search = $params['search'];
            $timepointType = intval($search['timepointType']);
            $starFlag = intval($search['starFlag']);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            
            $sql .= " and (bbt.backup_mode = ". $timepointType ." or ". $timepointType . " = '') and
        			(bbt.importance_flag = ". $starFlag ." or ". $starFlag . " = '')";
            //如果填了开始时间范围查询
            if($startTime && $endTime){
                $sql .= " and vol.start_timestamp >= '" . $startTime . "' and vol.end_timestamp <= '" . $endTime . "' ";
            } elseif ($startTime) {
                $sql .= " and vol.start_timestamp >= '" . $startTime . "' ";
            } elseif ($endTime) {
                $sql .= " and vol.end_timestamp <= '" . $endTime . "' ";
            }
        }
        $sqlCount = $sql." order by $sortArr[$sortColumn] $sortType";
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $countData = $this->dbSelect($sqlCount,$sqlCountParams);

        $data = $this->dbSelect($sql, $sqlParams);
        $utils = Xphp::instance('Utils');
        $records = array("data" => array());
        $i = 1;
        if(!empty($data)){
            foreach ($data as $d){
                $op = array(1, 2); //1:修改备注;2:删除备份集
                if($d['archive_flag'] == Xphp::$_config['FLAG']['SET']){ //时间点在合并中，不让删除
                    $op = array(1);
                }
                $taskIsDelete = $this->getTaskIsDeleted($d['task_uuid']);
                $storageLocation = $d['storage_location'];
                
                if($storageLocation == $VolCdpDes['DATA_SOURCE']['UNKNOWN'] || $storageLocation == $VolCdpDes['DATA_SOURCE']['STANDBY']){
                    continue;
                }

                //根据测试bug要求，在存储数据为新建和初始同步节点不做显示
                if($d['storage_status'] == $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']
                    || $d['storage_status'] == $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_NEW']){
                    continue;
                }
                $agentDetail = $d['detail'];
                $masterAgentDetail = $d['master_agent_detail'];
                $agentDetail = $this->parseBackupAgentDetail($d['master_agent_detail']);
                $agentName = $agentDetail['agent_name'];
                $agentIp = $agentDetail['agent_ip'];
                if($agentIp!=$volHostIP){
                    continue;
                }
                $storageStatus = $this->getBackupSetStorageStatus($d['storage_status'],$d['start_timestamp'],$agentuuid,$storageLocation);
                if($taskIsDelete){
                    $taskInfo = $d['task_name']." (" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                }else{
                    $taskInfo = $d['task_name'];
                }
                $encryptedFlag = $d['encrypted_flag'];
                
                $encryptedStr = Xphp::$_lang['UI_PUBLIC_OFF_TWO'];
                if($encryptedFlag == Xphp::$_config['FLAG']['SET']){
                    $encryptedStr = Xphp::$_lang['UI_PUBLIC_ON_TWO'];
                }
                
                $records["data"][] = array(
                    '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['start_timepoint']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                    '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['end_timepoint']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                    $encryptedStr,
                    $taskInfo,
                    $utils->calSize($d['backup_file_size'], true),
                    $utils->calSize($d['log_file_total_size'], true),
                    $storageStatus,
                    $this->getStorageName($d['storage_uuid']),
                    $d['remarks'],
                    $op,
//                     $d['importance_flag'],
                    array(
                        'uuid'=>$d['timepoint_uuid'],
                        'mode'=>$d['backup_mode'],
                        'vol_uuid'=>$vol_uuid,
                        'agentuuid'=>$agentuuid,
                        'node_uuid' =>$d['node_uuid'],
                        'remote_flag' => !$utils->parseFlagToBool(intval($d['data_local_flag'])),
                        'task_uuid' =>$d['task_uuid'],
                        'time_point' =>$this->parseDate($d['start_timepoint']),
                        'task_name' =>$d['task_name'],
                        'vol_name' =>$volName,
                        'vol_id' => $d['id'],
                        'encrypted_flag' => $encryptedFlag,
                        'task_delete' =>$taskIsDelete,
                    ),
                );
            }
        }
        $countNum = count($countData);
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $countNum;
        $records["recordsFiltered"] = $countNum;
        return  json_encode($records);
        
    }
    /**
     * 解析备份集当前状态，返回可执行操作
     * @param unknown $status
     */
    private function getBackupSetStorageStatus($status,$startTimestamp,$agentuuid,$storageLocation){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $verifyResult = "";
        switch ($status){
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_NEW']:  //新建状态
                $verifyResult =Xphp::$_lang['UI_PUBLIC_ADD'];
                break;
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']:  //初始同步
                $verifyResult = Xphp::$_lang['UI_VOL_CDP_INIT_SYNC'];
                break;
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_REALTIME_SYNC']:  //实时同步
                $verifyResult = Xphp::$_lang['UI_VOL_CDP_TAKEOVER_RECOVERY'];  
                if($storageLocation== $VolCdpDes['RECOVERY_DATA_SOURCE']['STANDBY']){
                    $verifyResult = Xphp::$_lang['UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER'];
                }
                break;
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_IMAGE_MERGE']:  //镜像合并中
                $verifyResult =Xphp::$_lang['UI_VOL_CDP_TAKEOVER_RECOVERY']; 
                if($storageLocation== $VolCdpDes['RECOVERY_DATA_SOURCE']['STANDBY']){
                    $verifyResult = Xphp::$_lang['UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER'];;
                }
                break;
        }
        return $verifyResult;
    }
    
    /**
     *  获取当前任务是否删除
     */
    private function getTaskIsDeleted($taskuuid){
        $sql = "select * from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            return false;  //任务存在
        }else{
            return true;  //任务已删除
        }
    }
    /**
     * 获取存储名字
     * @param string $storageuuid
     */
    private function getStorageName($storageuuid){
        $sql = "select storage_nickname, node_uuid, storage_config, storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = $data[0]['storage_nickname'];
        $type = intval($data[0]['storage_type']);
        $nodeHandler = Xphp::instance('NodeHandler');
        if($type == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
            $storageConfig = json_decode($data[0]['storage_config'], true);
            $nodename = $storageConfig['remote_ip'];
        }else{
            $nodename = $nodeHandler->getNodeName($data[0]['node_uuid']);
        }
        $name .= "\n" . "(" . $nodename . ")";
        return $name;
    }
    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode
     * @return string
     */
    private function getTimepointTypeDes($bakcupMode){
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }
    /**
     * 删除选中的事件信息
     * @param unknown $params
     */
    public function deleteSelectEventInfo($params){
        $checkList = $params['checkList'];
        $nodeuuid = $params['node_uuid'];
        $eventArray = implode(",", $checkList);
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 先从 cdp_vol_agent_event_info 取出 backup_agent_id ， 然后从 cdp_vol_backup_agent 取出 timepoint_uuid ，最后在 bd_backup_timepoint 取出用户uuid
            $backup_agent_id_list = $this->dbSelect("select backup_agent_id from cdp_vol_agent_event_info where id in (".$eventArray .")");
            $timepoint_uuid_list = $this->dbSelect("select timepoint_uuid from cdp_vol_backup_agent where id in (". implode(',', array_column($backup_agent_id_list, 'backup_agent_id')) .")");

            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", array_column($timepoint_uuid_list, 'timepoint_uuid')) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DELETE_INFO'], $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'],'error'));
            }
        }

        $sql = "delete from cdp_vol_agent_event_info where id in (".$eventArray .")";
        $result = $this->dbExec($sql);
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DELETE_INFO'],"" , "success");
        }else{
            return $this->muOpResult(false, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DELETE_INFO'],"" , "warning");
        }
    }
    /**
     * 删除选中标签点
     * @param unknown $params
     */
    public function deleteSelectLablePoint($params){
        $lebelList = $params['lebelList'];
        $nodeuuid = $params['node_uuid'];
        $opName = 'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL';
        $pfOpcode = Xphp::instance('VolCDPOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            $chk1_list = $this->dbSelect("select timepoint_uuid from cdp_vol_backup_agent where id = ?", [$lebelList['backup_agent_id']]);
            $pointUUID = array_column($chk1_list, 'timepoint_uuid');
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $pointUUID) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'],'error'));
            }
        }
        
        $sync = FALSE;
        $command = TRUE;
        $msg = json_encode($lebelList);
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * @desc 删除选中备份点
     * @param objcet{'delete_level':0,"vol_backup_set_id":1,"vol_uuid":'',"agent_uuid":"xx","node_uuid":""}
     * @return object
     */
    public function deleteSelectBackupSet($params){
        $backupsetList = $params['backupsetList'];
        $delObj = $params['delObj'];
        $deleteLevel = $backupsetList['delete_level'];
        $node_uuid = $params['node_uuid'];
        $taskDelte = $params['task_delete'];
        $agentuuid = $params['agent_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $userUuidData = array();
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $backupsetList['vol_backup_set_id'] 获取 timepointuuid
            $backup_agent_idArr = $this->dbSelect("select backup_agent_id from cdp_vol_backup_vol_set where id = ?", [$backupsetList['vol_backup_set_id']]);
            $backup_agent_id = array_column($backup_agent_idArr, 'backup_agent_id');
            $timepointuuidArr = $this->dbSelect("select timepoint_uuid from cdp_vol_backup_agent where id in (" . implode(',', $backup_agent_id) . ")", []);
            $timepointuuid = array_column($timepointuuidArr, 'timepoint_uuid');

            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'],'error'));
            }
        }

        switch ($deleteLevel){
            case 0:
                $timepointDes = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_UNKNOWN_TYPE'];
                break;
            case 1:
                $timepointDes = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DELETE_SINGLE_SET'];
                // 删除单个备份集
                // 备份任务获取任务状态状态，如果任务正在运行择判断当前删除备份集是否是最新备份集，是则不允许删除
                // 恢复任务：在任务对应的表cdp_vol_task_vol表中recovery_target_timestamp是否在删除备份集的时间范围内，是则不允许删除
                // 接管任务： 在任务对应的cdp_vol_task_takeover_info表中是否有对应任务，并获取takeover_timestamp时间是否在删除备份集时间范围内，是则不允许删除
                $pointUUID = $this->getPointuuidByVolBkId($backupsetList['vol_uuid'],$backupsetList['vol_backup_set_id']);
                /** * 验证权限*/
                $timepointuuids = implode("','", $pointUUID);
                $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
                $this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'),$timepointDes);

                $taskDelte = $backupsetList['task_delete'];
                $singSet = $this->singleSetDelExist($backupsetList['vol_uuid'],$backupsetList['vol_backup_set_id'],$pointUUID[0],$taskDelte);
                if(!empty($singSet)){
                    exit($this->muOpResult(false, $operate,Xphp::$_lang['UI_VOL_CDP_DELETE_FAIL'].", ".$singSet['desc'].", ".Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].": ".$singSet['task_name'],'warning'));
                }
                break;
            case 2:  //判断删除整个卷备份集是否有任务，有择不允许删除；
                $timepointDes = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DELETE_ALL_SET'];
                $voluuid = $backupsetList['vol_uuid'];
                $pointUUID = $this->getPointuuidByVoluuid($voluuid);
                $pointUUID = $this->getPointuuidByVoluuid($backupsetList['vol_uuid']);
                $taskUuid = $backupsetList['task_uuid'];
                /** * 验证权限*/
                $timepointuuids = implode("','", $pointUUID);
                $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
                $this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'),$timepointDes);
                
                $this->checkClientRecoveryTask($agentuuid,$taskUuid,$voluuid);
                
                $this->checkClientTakeoverTask($agentuuid,$taskUuid,$voluuid);
                $volSet = $this->volSetDelExist($backupsetList['vol_uuid'],$backupsetList['vol_backup_set_id'],$pointUUID,$taskUuid);
              
                if(!empty($volSet) ){
                    // $reTaskName = count($volSet)>0?$volSet['task_name']:$data[0]['task_name'];
                    $reTaskName = $volSet['task_name'];
                    if($volSet['desc']==""){
                        $volDesc = Xphp::$_lang['WEB_VOL_CDP_DELETE_VOL_SET_CORRELATION_TASK_TIPS'];
                    }else{
                        $volDesc = $volSet['desc'];
                    }
                    exit($this->muOpResult(false, $operate,Xphp::$_lang['UI_VOL_CDP_DELETE_FAIL'].", ".$volDesc.", ".Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].": ".$reTaskName,'warning'));
                }
                break;
            case 3:  //判断删除客户端是否有任务，有择不允许删除；
                $timepointDes = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_DELETE_CLIENT_SET_SELECTED'];
                $pointUUID = $this->getPointuuidByAgentuuid($agentuuid);
                $taskUuid = $backupsetList['task_uuid'];
                /** * 验证权限*/
                $timepointuuids = implode("','", $pointUUID);
                $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
                $this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'),$timepointDes);

                //检查删除客户端备份数据是否有备份任务正在运行，恢复任务和接管任务存在
                $this->checkClientBackupTaskIsRunning($agentuuid,$taskUuid);
                $this->checkClientRecoveryTask($agentuuid,$taskUuid,"");
                $this->checkClientTakeoverTask($agentuuid,$taskUuid,"");
                break;
            default:
                $timepointDes = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_UNKNOWN_TYPE'];
        }

        //判断删除的备份数据是否关联回切任务
        $isFailBackupList = $this->getFailbackTaskInfo($backupsetList['task_uuid'],$backupsetList['agent_uuid']);
        $isFailBackupTask = $isFailBackupList['is_failbackup_task'];
        $reTaskName = $isFailBackupList['task_name'];
        if($isFailBackupTask){
            exit($this->muOpResult(false, $operate,Xphp::$_lang['UI_VOL_CDP_DELETE_FAIL'].", ".Xphp::$_lang['UI_VOL_CDP_DELETE_BACKUP_SET_FAILBACK_TIPS']." ".Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].": ".$reTaskName,'warning'));
        }

        $msg = json_encode($backupsetList);
        $nodeHandler = Xphp::instance('NodeHandler');
        if(($deleteLevel == 2 || $deleteLevel==3) && $node_uuid==""){  //整卷和整个客户端
            $nodeInfo = $this->agentAllNode($backupsetList);
            for($i=0;$i<count($nodeInfo);$i++){
                $mbResult = $this->mbVOLCDPMsg($nodeInfo[$i], $opName, $msg);
            }
        }else{
            $mbResult = $this->mbVOLCDPMsg($node_uuid, $opName, $msg);
        }
        
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        
        $taskName = "";
        $descriptionParam = array($timepointDes, $taskName, $delObj);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 检查是否拥有主机保护的操作权限
     * @param array $userIdList 操作资源的拥有者uuid列表
     * @return void
     */
    private function checkHostOperatePermission(array $userIdList,$operateDes)
    {
        // 关联管理用户判断 数据库保护 - 操作 vol_cdp_protect_operate
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['vol_cdp_protect_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], $userIdList, $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, $operateDes, Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }
    }
    /**
     * 获取删除客户端是否有备份任务正在运行，有择不允许删除
     * @param $agent_uuid
     */
    private function checkClientBackupTaskIsRunning($agent_uuid,$taskUuid){
        $sql = "SELECT task.task_status,task.task_name 
                from bd_task task,cdp_vol_task vol 
                where vol.task_uuid = task.task_uuid and vol.master_agent_uuid = ? and task.task_type = ? and task_status = ? and task.task_uuid = ? ";
        $data = $this->dbSelect($sql,array($agent_uuid,Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'],Xphp::$_config['TASKSTATUS']['RUNNING'],$taskUuid));
        if(!empty($data)){
            exit($this->muOpResult(false,Xphp::$_lang['UI_VOL_CDP_DELETE_AGENT_ALL_DATA'],
                Xphp::$_lang['UI_VOL_CDP_DELETE_AGENT_ALL_DATA_FAIL'].",".Xphp::$_lang['UI_VOL_CDP_DELETE_AGENT_BACKUP_TASK_RUNNING_EXIST'].". ".Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].":".$data[0]['task_name'],'warning'));
        }
    }
    /**
     * 获取数据源客户端是否有恢复任务正在运行
     * @param $agnet_uuid
     */
    private function checkClientRecoveryTask($agnet_uuid,$taskUuid,$delVoluuid){
        //获取任务生成的备份集
        $volBackupSet = $this->getTaskCreateBackupSet($taskUuid);
        if($delVoluuid != ""){
            $dataList = [];
            $volBackupSetId = 0;
            foreach($volBackupSet as $d){
                if($delVoluuid == $d['vol_uuid']){
                    $volBackupSetId = $d['backup_set_id'];
                }
            }
            $dataList = $this->getRecoverTaskBkSetInfo($delVoluuid,$volBackupSetId);  //判断备份集是否有恢复任务存在
        }else{
            $dataList =[];
            foreach($volBackupSet as $d){
                $voluuid = $d['vol_uuid'];
                $volBackupSetId = $d['backup_set_id'];
                $dataList = $this->getRecoverTaskBkSetInfo($voluuid,$volBackupSetId);  //判断备份集是否有恢复任务存在
                if(!empty($dataList)){
                    break;
                }
            }
        }

        if(!empty($dataList)){
            exit($this->muOpResult(
                false,
                Xphp::$_lang['UI_VOL_CDP_DELETE_AGENT_ALL_DATA'],
                Xphp::$_lang['UI_VOL_CDP_DELETE_FAIL'].",".Xphp::$_lang['UI_VOL_CDP_BACKUP_RECOVERY_TASK_EXIST'].". ".Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].":".$dataList['task_name'],'warning'
            ));
        }

    }


    /**
     * 获取数据源客户端是否有接管任务
     * @param $agnet_uuid
     */
    private function checkClientTakeoverTask($agnet_uuid,$taskUuid,$delVoluuid){
        $volBackupSet = $this->getTaskCreateBackupSet($taskUuid);
        if($delVoluuid!=""){
            $dataList = [];
            $volBackupSetId = 0;
            foreach($volBackupSet as $d){
                if($delVoluuid == $d['vol_uuid']){
                    $volBackupSetId = $d['backup_set_id'];
                }
            }
            $dataList = $this->getTakeoverBkSetInfo($delVoluuid,$volBackupSetId);  //判断备份集是否有接管任务存在
        }else{
            $dataList =[];
            foreach($volBackupSet as $d){
                $voluuid = $d['vol_uuid'];
                $volId = $d['backup_set_id'];
                $dataList = $this->getTakeoverBkSetInfo($voluuid,$volId);  //判断备份集是否有接管任务存在
                if(!empty($dataList)){
                    break;
                }
            }
        }
        if(!empty($dataList)){
            exit($this->muOpResult(false,
                Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
                Xphp::$_lang['WEB_CLIENT_DELETE_TASK_EXIST'].",".Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].":".$dataList['task_name'],'warning'));
        }
    }

    /**
     * 获取主机所有的备份节点信息
     */
    private  function agentAllNode ($backupsetList){
        $masterAgentuuid  = $backupsetList['agent_uuid'];
        $sql = "SELECT bsr.node_uuid from bd_backup_timepoint bbt,cdp_vol_backup_agent cba,bd_storage_resource bsr 
                WHERE bbt.timepoint_uuid = cba.timepoint_uuid and cba.master_agent_uuid = ? 
                AND bbt.storage_uuid = bsr.storage_uuid ";
        $data = $this->dbSelect($sql, array($masterAgentuuid));
        $nodeInfo = array();
        foreach ($data as $d){
            $node_uuid = $d['node_uuid'];
            if (in_array($node_uuid, $nodeInfo)) {
                continue;
            }else{
                array_push($nodeInfo,$node_uuid);
            }
        }
        return $nodeInfo;
    }
    /**
     * 判断删除备份集对应卷是否有任务存在，有则不允许删除
     */
    private function volSetDelExist($vol_uuid,$vol_id,$point_uuid,$task_uuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $sql = "select task.task_name,task.task_type,task.task_status,vt.current_task_running_stage 
                from cdp_vol_task_vol vol,bd_task task,cdp_vol_task vt 
                where vol.task_uuid = task.task_uuid and vol.vol_uuid =? and vt.task_uuid = task.task_uuid 
                  and (task.task_status =? or task.task_status = ?  or task.task_status = ?)  and task.task_uuid = ? ";
        $parameter = array($vol_uuid,Xphp::$_config['TASKSTATUS']['RUNNING'],Xphp::$_config['TASKSTATUS']['SUCCESSED'],Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'],$task_uuid);
        $data = $this->dbSelect($sql, $parameter);
        $dataList = array();
        if(!empty($data)){
            $currentStage =$data[0]['current_task_running_stage'];
            $taskStatus = $data[0]['task_status'];
            if($taskStatus == Xphp::$_config['TASKSTATUS']['SUCCESSED']  && ($currentStage == $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER_STARTING'] || $currentStage == $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER'])){
                $dataList = array(
                    'task_name' => $data[0]['task_name'],
                    'task_type' => $data[0]['task_type'],
                    'desc' => Xphp::$_lang['UI_VOL_CDP_DELETE_BACKUP_TASK_EXIST'],
                );
            }elseif($taskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] || Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
                $dataList = array(
                    'task_name' => $data[0]['task_name'],
                    'task_type' => $data[0]['task_type'],
                    'desc' => Xphp::$_lang['UI_VOL_CDP_DELETE_BACKUP_TASK_EXIST'],
                );
            }
        }
        return $dataList;
    }
    
    /**
     * 判断单个备份集是否有任务存在，存在则不允许删除
     * 通过备份集ID获取备份集所在客户端UUID，再通过客户端uuid 在cdp_vol_task中获取对应任务表是否有任务存在，获取任务类型。
     */    
    private  function singleSetDelExist($vol_uuid,$vol_id,$pointUUID,$taskDelte){
        $sql = "select agent.master_agent_uuid from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol_set 
            where vol_set.vol_uuid = ? and vol_set.id = ? and vol_set.backup_agent_id = agent.id";
        $data = $this->dbSelect($sql, array($vol_uuid,$vol_id));
        $dataList = array();
        if(!empty($data)){
            $flag = Xphp::$_config['FLAG'];
            $masterAgentuuid = $data[0]['master_agent_uuid'];
            $tasksql = "select task.task_name,task.task_type,task.task_status,task.task_uuid 
                        from cdp_vol_task vol_task,bd_task as task 
                        where vol_task.master_agent_uuid =? and vol_task.task_uuid = task.task_uuid and task.delete_flag !=?";
            $taskData = $this->dbSelect($tasksql, array($masterAgentuuid,$flag['SET']));
            foreach ($taskData as $d){
                $taskType = $d['task_type'];
                $taskStatus = $d['task_status'];
                $taskuuid = $d['task_uuid'];
                $taskName = $d['task_name'];
                
                if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']){  //备份
                    //备份任务获取任务状态状态，如果任务正在运行择判断当前删除备份集是否是最新备份集，是则不允许删除
                    if($taskDelte){
                        continue;
                    }
                    $dataList = $this->getBackupTaskBkSetInfo($taskuuid,$taskStatus,$taskName,$taskType,$pointUUID,$masterAgentuuid);
                    if(count($dataList)>0){
                        break;
                    }
                }
                //  if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] && $taskStatus==Xphp::$_config['TASKSTATUS']['RUNNING']){  //运行中的恢复任务
                if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){  //有恢复任务存在
                    $dataList = $this->getRecoverTaskBkSetInfo($vol_uuid,$vol_id);
                    if(!empty($dataList)){
                        break;
                    }
                }
                
                if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){  //接管
                    $dataList = $this->getTakeoverBkSetInfo($vol_uuid,$vol_id);
                    if(count($dataList)>0){
                        break;
                    }
                }
                
            }
        }
        return $dataList;
    }
     /**
     * 获取删除备份集是否有关联回切任务正在运行
     * @return void
     */
    private function getFailbackTaskInfo($taskuuid,$agentuuid){
        $sql = "select cvt.current_task_running_stage,bt.task_name 
                from cdp_vol_task cvt,cdp_vol_task_takeover_info cvtti,bd_task bt 
                where cvt.task_uuid = ? and cvt.task_uuid = cvtti.task_uuid and cvtti.takeover_standby_agent_uuid = ? and bt.task_uuid = cvt.task_uuid";
        $data = $this->dbSelect($sql, array($taskuuid,$agentuuid));
        $currentTaskRunningStage = 0;
        $taskName = '';
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if(!empty($data)){
            $currentTaskRunningStage = $data[0]['current_task_running_stage'];
            $taskName = $data[0]['task_name'];
        }
        $isFailBackup = false;
        if($currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC'] ||
            $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC'] ||
            $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING'] 
        ){
            $isFailBackup = true;
        }
        $list = array(
            "is_failbackup_task" => $isFailBackup,
            'task_name' => $taskName
        );
        return $list;
    }
    /**
     * 获取删除备份集是否包含正在运行的恢复任务
     * @param unknown $voluuid
     * @param unknown $volId
     * @param unknown $taskName
     * @param unknown $taskType
     */
    private function getRecoverTaskBkSetInfo($voluuid,$volId){
        $sql = "select tv.task_uuid,task.task_name from cdp_vol_task_vol tv,cdp_vol_backup_vol_set vs,bd_task task 
                where tv.vol_uuid = ? and tv.vol_uuid = vs.vol_uuid and (UNIX_TIMESTAMP(tv.recovery_target_timestamp) >= UNIX_TIMESTAMP(vs.start_timestamp) 
                 and UNIX_TIMESTAMP(tv.recovery_target_timestamp) <= UNIX_TIMESTAMP(vs.end_timestamp)) and vs.id = ? and task.task_uuid = tv.task_uuid and task.task_type = ?";
        $data = $this->dbSelect($sql, array($voluuid,$volId,Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']));
        $dataList = array();
        if(!empty($data)){
            $taskName = $data[0]['task_name'];
            $dataList = array(
                'task_name' => $taskName,
                'task_type' => Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'],
                'desc' => Xphp::$_lang['UI_VOL_CDP_BACKUP_RECOVERY_TASK_EXIST'],
            );
        }
        return  $dataList;
    }
    
    /**
     * 获取删除卷及及时间集是否有接管任务关联
     * @param unknown $voluuid
     * @param unknown $volId
     * @param unknown $taskName
     * @param unknown $taskType
     * @return string[]|unknown[]
     */
    private  function getTakeoverBkSetInfo($voluuid,$volId){
        $sql = "select tv.task_uuid,task.task_name from cdp_vol_task_takeover_info ti,cdp_vol_task_vol tv,cdp_vol_backup_vol_set vs,bd_task task 
                where tv.task_uuid = ti.task_uuid and tv.vol_uuid = ? and tv.task_uuid =ti.task_uuid and vs.vol_uuid = tv.vol_uuid
                    and (UNIX_TIMESTAMP(ti.takeover_timestamp) >= UNIX_TIMESTAMP(vs.start_timestamp) and 
                    UNIX_TIMESTAMP(ti.takeover_timestamp) <= UNIX_TIMESTAMP(vs.end_timestamp)) and vs.id = ? and task.task_uuid = tv.task_uuid and task.task_type = ?";
        $data = $this->dbSelect($sql, array($voluuid,$volId,Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']));
        $dataList = array();
        if(!empty($data)){
            $dataList = array(
                'task_name' => $data[0]['task_name'],
                'task_type' => Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'],
                'desc' => Xphp::$_lang['UI_VOL_CDP_BACKUP_TAKEOVER_TASK_EXIST'],
            );
        }
        return  $dataList;
    }

    /**
     * 获取选中任务生成的备份集信息
     * @param mixed $task_uuid
     * @param mixed $agent_uuid
     * @return void
     */
    private function getTaskCreateBackupSet($task_uuid){
        $sql = "SELECT cvbcs.vol_uuid,cvbcs.start_timestamp,cvbcs.end_timestamp,cvbcs.vol_name,cvbcs.id  
                from bd_backup_timepoint bbt,cdp_vol_backup_agent ccba,cdp_vol_backup_vol_set cvbcs 
                where bbt.task_uuid = ? and bbt.timepoint_uuid = ccba.timepoint_uuid and ccba.id = cvbcs.backup_agent_id ";
        $data = $this->dbSelect($sql, array($task_uuid));
        $volList = array();
        foreach ($data as $d){
            $volList[] = array(
                'vol_uuid' => $d['vol_uuid'],
                'backup_set_id' => $d['id']
            );
        }
        return $volList;
    }
    /**
     * 获取备份任务对应的备份集是否可以删除
     * @param unknown $taskuuid
     * @param unknown $taskStatus
     * 判断备份任务是否有多个备份集，如果只有一个备份集择当前备份集不能删除。如果有多个备份集，需要判断当前备份集是否是最新备份集(最新备份集可以从bd_time_point中获取)，是不允许删除
     */
    private function getBackupTaskBkSetInfo($taskuuid,$taskStatus,$taskName,$taskType,$pointUUID,$agentuuid){
        $dataList = array();
        $sql = "select bbt.id,bbt.timepoint_uuid 
                from bd_backup_timepoint bbt, cdp_vol_backup_agent cvba 
                where bbt.task_uuid = ? and bbt.timepoint_uuid = cvba.timepoint_uuid and cvba.master_agent_uuid = ?";

        $data = $this->dbSelect($sql, array($taskuuid,$agentuuid));
        $num = count($data);
        if($num==1 && ($taskStatus==Xphp::$_config['TASKSTATUS']['RUNNING']
                || $taskStatus==Xphp::$_config['TASKSTATUS']['SUCCESSED']
                || $taskStatus==Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'])){  //运行中,成功
            $dataList = array(
                'task_name' => $taskName,
                'task_type' => $taskType,
                'desc' => Xphp::$_lang['UI_VOL_CDP_BACKUP_TASK_RUNNING'],
            );
        }else if($num>1){
            $lastSql = $sql ." order by bbt.id desc limit 0,1";
            $lastData = $this->dbSelect($lastSql, array($taskuuid,$agentuuid));
            $newTimepointuuid = $lastData[0]['timepoint_uuid'];
            if($newTimepointuuid == $pointUUID && ($taskStatus==Xphp::$_config['TASKSTATUS']['RUNNING']
                    || $taskStatus==Xphp::$_config['TASKSTATUS']['SUCCESSED']
                    || $taskStatus==Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'])){ //是否为最新备份集，且
                $dataList = array(
                    'task_name' => $taskName,
                    'task_type' => $taskType,
                    'desc' => Xphp::$_lang['UI_VOL_CDP_DELETE_BACK_TASK_NEW'],
                );
            }
        }
       
        return  $dataList;
    }
    /**
     * 通过卷备份集id获取time_point_uuid
     */
    private  function getPointuuidByVolBkId($vol_uuid,$vol_id){
        $sql = "SELECT agent.timepoint_uuid from cdp_vol_backup_agent as agent,cdp_vol_backup_vol_set as vol
                where vol.vol_uuid = ? and vol.id = ? and vol.backup_agent_id = agent.id";
        $data = $this->dbSelect($sql, array($vol_uuid,$vol_id));
        $timepoint_list = array();
        if(!empty($data)){
            foreach ($data as $d){
                $timepoint_list = array(
                    $d['timepoint_uuid']
                );
            }
        }
        return  $timepoint_list;
    }
    /**
     * 通过agent id获取time_point_uuid
     */
    private  function getPointuuidByAgentuuid($agent_uuid){
        $sql = "SELECT timepoint_uuid from cdp_vol_backup_agent where master_agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agent_uuid));
        $timepoint_list = array();
        if(!empty($data)){
            foreach ($data as $d){
                $timepoint_list = array(
                    $d['timepoint_uuid']
                );
            }
        }
        return  $timepoint_list;
    }


    /**
     * 通过卷uuid获取对应的time_point_uuid
     */
    private  function getPointuuidByVoluuid($vol_uuid){
        $sql = "SELECT agent.timepoint_uuid from cdp_vol_backup_agent as agent,cdp_vol_backup_vol_set as vol
                where vol.vol_uuid = ? and vol.backup_agent_id = agent.id ";
        $data = $this->dbSelect($sql, array($vol_uuid));
        $timepoint_list = array();
        if(!empty($data)){
            foreach ($data as $d){
                $timepoint_list[] = $d['timepoint_uuid'];
            }
        }
        return  $timepoint_list;
    }
    /**
     * 检查时间点是否有任务存在
     * @param unknown $timepointuuids
     * @param unknown $operate
     */
    private function  taskExist($timepointuuids, $operate){
        $flag = Xphp::$_config['FLAG'];
        $deleted_flag  = $flag['UNSET'];  //未删除标志位
        $available_flag = $flag['SET'];  //已删除标志位
        $points = implode("','", $timepointuuids[0]);
        $sql = "SELECT task_uuid from bd_backup_timepoint where timepoint_uuid in ('$points')";
        $datamore  =$this->dbSelect($sql);
        $taskuuids = array();
        if(!empty($datamore)){
            foreach($datamore as $d){
                $taskuuids[] = $d['task_uuid'];
            }
        }
        //前期版本:判断是否有作业存在,存在不允许删除该备份集;
        //后期需要完善功能:判断改作业任务类型,任务状态.备份作业正在运行,以及存在接管和恢复作业时不允许删除;
        if(!empty($taskuuids)){
            $tasks = implode("','", $taskuuids);
            $sqlexport = "select task_status,task_type,task_name from bd_task where task_uuid in ('$tasks') and delete_flag ='{$deleted_flag}'";
            $dataexport = $this->dbSelect($sqlexport);
            if(!empty($dataexport)){
                $taskType = $dataexport[0]['task_type'];
                $taskName = $dataexport[0]['task_name'];
                if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] || $taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){  //接管任务或恢复任务
                    exit($this->muOpResult(false, $operate,Xphp::$_lang['UI_VOL_CDP_TIME_TASK_RUNNIG'].$taskName.Xphp::$_lang['UI_VOL_CDP_USE_DELETE_TIP'],'warning'));
                }
                
            }
        }
    }
    /**
     * 修改指定客户端标签点备注信息
     * @param object
     */
    public function remarkTagPoint($params){
        $lableId = $params['lable_id'];
        $backupAgentId = $params['backup_agent_id'];
        $remark = $params['remark'];
        $this->paramsCheck($lableId);
        $sql = "update cdp_vol_agent_label_set set remarks = ? where backup_agent_id = ? and id = ?";
        $result = $this->dbExec($sql,array($remark,$backupAgentId,$lableId));
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'],"" , "success");
        }else{
            return $this->muOpResult(false, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'],"" , "warning");
        }
    }
    /**
     * 获取客户端对应的备份数据
     * @param node_uuid,agent_uuid;
     */
    public function getAgentBkTimelineData($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $nodeUuid = $params['node_uuid'];
        $agentUuid = $params['agent_uuid'];
        $timeIntervalType = $params['time_interval'];
        $volUuid = $params['vol_uuid'];
        $taskType = $params['task_type'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $taskuuid = $params['task_uuid'];
        $timeLineData = array();
        $records = array();
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = Xphp::$_user['useruuid'];

        if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_TEN_MIN'] ){
            $sql = "select se.id,se.backup_timestamp 
                from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where agent.master_agent_uuid = ? and agent.id = se.backup_agent_id and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid ='{$useruuid}' ";
            }

            $sql .= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid,$VolCdpDes['DATA_SOURCE']['STANDBY']);

            if($volUuid!=""){
                $sql.=" and se.vol_uuid ='{$volUuid}'";
            }
            $latestTimeSql = $sql ." order by se.id  desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams);
            if($startTime) {
                $records["time_data"]  = $this->getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_timestamp'];
                    $records["time_data"]  = $this->getSecondLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid);
                } else{
                    $records["time_data"] =[];
                }
            }
        }else if($timeIntervalType==$VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_HOUR']
            || $timeIntervalType==$VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_DAY']
            || $timeIntervalType ==$VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_SEVEN_DAY']){
            $sql = "select mi.id,mi.backup_end_timestamp 
                from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
               where agent.master_agent_uuid = ? and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid,$VolCdpDes['DATA_SOURCE']['STANDBY']);
            
            if($volUuid!=""){
                $sql.=" and mi.vol_uuid ='{$volUuid}'";
            }
            $latestTimeSql = $sql. " order by mi.id  desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams);
            if($startTime) {
                $records["time_data"]  = $this->getMinuteLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_end_timestamp'];
                    $records["time_data"]  = $this->getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid);
                }else{
                    $records["time_data"] =[];
                }
            }
        }else if($timeIntervalType==$VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_THIRTY_DAY'] || $timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_NINETY_DAY']){
            $sql = "select hour.id,hour.backup_end_timestamp 
                from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent,bd_backup_timepoint bbt        
                where agent.master_agent_uuid = ? and agent.id = hour.backup_agent_id and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid,$VolCdpDes['DATA_SOURCE']['STANDBY']);
            
            if($volUuid!=""){
                $sql.=" and hour.vol_uuid ='{$volUuid}'";
            }
            $latestTimeSql = $sql." order by hour.id desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams );
            if($startTime) {
                $records["time_data"]  = $this->getHourLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_end_timestamp'];
                    $records["time_data"]  = $this->getHourLevelData($latestTime,$agentUuid,$timeIntervalType);
                }else{
                    $records["time_data"] =[];
                }
            }

        }
        return json_encode($records);
    }
    /**
     * 获取秒级表对应时间轴需要的数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function  getSecondLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_TEN_MIN']){   //最近10分钟数据,客户端对应的最新时间减600秒
            $timeIntervalValue = 600;
            $timeDif = strtotime($latestTime)-$timeIntervalValue;
        }else if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_HOUR']){    //最近一小时数据,客户端对应的最新时间减3600
            $timeIntervalValue = 3600;
            $timeDif = strtotime($latestTime)-$timeIntervalValue;
        }
        $difTimeStr =  date('Y-m-d H:i:s',$timeDif); //60分钟后的时间字符串
        $bakcupFlowData = array();
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select se.backup_timestamp,se.data_flow,se.label_point_count,se.event_point_count
            from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt   
            where se.backup_timestamp between '". $difTimeStr ."' and '". $latestTime ."' and agent.master_agent_uuid = ? 
                and agent.id = se.backup_agent_id and agent.storage_location!=? and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            //获取当前时间，减去最新时间，获取二维数组下标位置
            foreach ($data as $d){
                $timeStr = $d['backup_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
//        else{
//            $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
//
//        }
        return $bakcupFlowData;
    }
    private function  getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $timeIntervalValue = strtotime($endTime) - strtotime($startTime);  // 最多一个小时 3600点
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType, $endTime, $timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select se.backup_timestamp,se.data_flow,se.label_point_count,se.event_point_count
            from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
            where se.backup_timestamp between '". $startTime ."' and '". $endTime ."' and agent.master_agent_uuid = ? 
                and agent.id = se.backup_agent_id and agent.storage_location!=? and 
                agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            //获取当前时间，减去最新时间，获取二维数组下标位置
            foreach ($data as $d){
                $timeStr = $d['backup_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($endTime) - $timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }
    
    /**
     * 获取分钟级表对应的时间轴数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_HOUR']){   //最近小时数据,读取分钟级表，对应的最新时间减60分钟
            $timeIntervalValue = 60;
            $timeDif = strtotime($latestTime)-($timeIntervalValue*60);
        }
        else if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_DAY']){   //最近1天数据,读取分钟级表，对应的最新时间减1440分钟
            $timeIntervalValue = 10080;  //单位分钟
            $timeDif = strtotime($latestTime)-($timeIntervalValue*60);
        }
        $difTimeStr =  date('Y-m-d H:i:s',$timeDif);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        
        $dataTimeArray = array();
        $sql = "SELECT mi.backup_end_timestamp,mi.data_flow,mi.label_point_count,mi.event_point_count
            from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
            where mi.backup_end_timestamp BETWEEN '". $difTimeStr ."' and '". $latestTime ."' and agent.master_agent_uuid = ? 
                and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }
    private function getMinuteLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType,$taskuuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';

        $timeIntervalValue = ceil((strtotime($endTime) - strtotime($startTime))/60);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$endTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "SELECT mi.backup_end_timestamp,mi.data_flow,mi.label_point_count,mi.event_point_count
            from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
            where mi.backup_end_timestamp BETWEEN '". $startTime ."' and '". $endTime ."' and agent.master_agent_uuid = ? 
                and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($endTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }
    /**
     * 获取小时级表对应的时间轴数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function getHourLevelData($latestTime,$agentUuid,$timeIntervalType){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_THIRTY_DAY']){  //最近30天数据,读取小时级表，对应的最新时间减720小时
            $timeIntervalValue = 720;  //30天折合720小时
        }else if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_NINETY_DAY']){  //最近90天数据,读取小时级表，对应的最新时间减2160小时
            $timeIntervalValue = 2160;  //30天折合2160个小时
        }
        $intervalTimestamp = $timeIntervalValue*60*60;  //间隔时间戳
        $timeDif = strtotime($latestTime)-$intervalTimestamp; //间隔时间
        
        $difTimeStr =  date('Y-m-d H:i:s',$timeDif); 
        
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "SELECT hour.backup_end_timestamp,hour.data_flow,hour.label_point_count,hour.event_point_count
            from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent
            where hour.backup_end_timestamp BETWEEN '". $difTimeStr ."' and '". $latestTime ."'
                  and agent.master_agent_uuid = ? and agent.id = hour.backup_agent_id and agent.storage_location!=?";
        $data = $this->dbSelect($sql, array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp']; 
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }
    private function getHourLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $timeIntervalValue = floor((strtotime($endTime) - strtotime($startTime))/60/60);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$endTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "SELECT hour.backup_end_timestamp,hour.data_flow,hour.label_point_count,hour.event_point_count
            from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent
            where hour.backup_end_timestamp BETWEEN '". $startTime ."' and '". $endTime ."'
                  and agent.master_agent_uuid = ? and agent.id = hour.backup_agent_id and agent.storage_location!=?";
        $data = $this->dbSelect($sql, array($agentUuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($endTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }
    /**
     * 通过当前选择的时间区间类型及当前客户端可供配置的最新时间，计算默认data list
     * @param 最新时间  $latestTime
     * @param 时间间隔  $timeInterval
     */
    private function createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $defaultData = array();
        for($i = $timeIntervalValue;$i>0;$i--){
            if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_HOUR'] ){
                $defaultTime = strtotime($latestTime)-$i; //秒
            }else if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_DAY'] || $timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_ONE_HOUR'] || $timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_SEVEN_DAY']){
                $defaultTime = strtotime($latestTime)-$i*60; //分钟
            }else if($timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_THIRTY_DAY'] || $timeIntervalType == $VolCdpDes['BACKUP_SET_INTERVAL_TYPE']['LAST_NINETY_DAY']){
                $defaultTime = strtotime($latestTime)-$i*60*60; //小时
            }
            $defaultTimeStr =  date('Y-m-d H:i:s',$defaultTime);
            $defaultFlow = 0;
            $defaultLabelCount = 0;
            $defaultEventCount = 0;
            $defaultData[] = array(
                $defaultTimeStr,$defaultFlow,$defaultLabelCount,$defaultEventCount
            );
        }
        return $defaultData;
    }
    
    /**
     * 合并时间数组,匹配$getDataTime在$defaultDataTime的第一个下标,取出1-3的键位的值,为每一个$defaultDataTime数组中的1-3的键位进行赋值;
     * @param 根据时间间隔计算出的默认数组 $defaultDataTime
     * @param 获取时间间隔对应的流量、标签点及事件点信息 $getDataTime
     * 因效率问题，该函数弃用。
     */
    private function mergeTimeList($defaultDataTime,$getDataTime){
        $timeList = array();
        foreach ($defaultDataTime as $deTime){
            $timeStr = $deTime[0];
            $dataFlow = 0;
            $labelCount = 0;
            $eventCount =0;
            foreach ($getDataTime as $getTime){
                if($deTime[0]==$getTime[0]){
                    $dataFlow = $getTime[1];
                    $labelCount = $getTime[2];
                    $eventCount = $getTime[3];
                }
            }
            $timeList[] = array(
                $timeStr,
                $dataFlow,
                $labelCount,
                $eventCount
            );
        }
        return $timeList;
    }
    /**
     * 测试暂时使用修改标签点备注函数
     */
    public function remarkTagPointTest($params){
        
        $result = $this->addSecondTestData(); //second
        //$result = $this->addMinTestData(); //min
        $result = $this->addHourTestData(); //hour
        
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'],"" , "success");
        }else{
            return $this->muOpResult(false, Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'],"" , "warning");
        }
        
    }
    /**
     *新增秒级测试数据
     *创建循环，每次time+1秒
     */
    private function addSecondTestData(){
        $timeStamp = 1643003013;
        $max = 1000000;
        $this->dbBeginTransaction();
        for ($i=0;$i<$max;$i++){
            $dataFlow = rand(1,128);
            $labelCount = rand(0,1);
            $remarkCount = rand(0,1);
            $timeStamp +=1;
            $addTime = date('Y-m-d H:i:s',$timeStamp);
            if($addTime!="0000-00-00 00:00:00"){
                $sql = "insert into cdp_vol_backup_agent_second_level_data_flow (backup_agent_id,backup_timestamp,data_flow,label_point_count,event_point_count)
                 values (?,?,?,?,?)";
                $result = $this->dbQuery($sql,array(35,$addTime,$dataFlow,$labelCount,$remarkCount));
            }
        }
        if($result){
            $this->dbCommit();
            return true;
        }else{
            $this->dbRollBack();
            return false;
        }
    }
    /**
     * 新增分钟级测试数据
     * 设置起始时间,获取时间戳,间隔+60
     * 生成流量随机数,1-512
     * 生成流量随机数，0-128整数
     */
    private function addMinTestData(){
        $timeStamp = 1641003010;
        $max = 11000;
        $this->dbBeginTransaction();
        for ($i=0;$i<$max;$i++){
            $dataFlow = rand(1,512);
            $lableCount = rand(0,10);
            $remarkCount = rand(0,5);
            $startStamp = $timeStamp;
            $startAddTime = date('Y-m-d H:i:s',$startStamp);
            
            $timeStamp +=60;
            $endAddTime = date('Y-m-d H:i:s',$timeStamp);
            
            if($endAddTime!="0000-00-00 00:00:00"){
                $sql = "insert into cdp_vol_backup_agent_minute_level_data_flow (backup_agent_id,backup_start_timestamp,backup_end_timestamp,data_flow,label_point_count,event_point_count)
                 values (?,?,?,?,?,?)";
                $result = $this->dbQuery($sql,array(35,$startAddTime,$endAddTime,$dataFlow,$lableCount,$remarkCount));
            }
        }
        if($result){
            $this->dbCommit();
            return true;
        }else{
            $this->dbRollBack();
            return false;
        }
    }
    
    /**
     * 新增小时级测试数据
     */
    private function addHourTestData(){
        $timeStamp = 1641003010;
        $max = 9999;
        $this->dbBeginTransaction();
        for ($i=0;$i<$max;$i++){
            $dataFlow = rand(1,1024);
            $lableCount = rand(0,60);
            $remarkCount = rand(0,20);
            $startStamp = $timeStamp;
            $startAddTime = date('Y-m-d H:i:s',$startStamp);
            
            $timeStamp +=3600;
            $endAddTime = date('Y-m-d H:i:s',$timeStamp);
            
            if($endAddTime!="0000-00-00 00:00:00"){
                $sql = "insert into cdp_vol_backup_agent_hour_level_data_flow (backup_agent_id,backup_start_timestamp,backup_end_timestamp,data_flow,label_point_count,event_point_count)
                 values (?,?,?,?,?,?)";
                $result = $this->dbQuery($sql,array(35,$startAddTime,$endAddTime,$dataFlow,$lableCount,$remarkCount));
            }
        }
        if($result){
            $this->dbCommit();
            return true;
        }else{
            $this->dbRollBack();
            return false;
        }
    }
    /**
     * 获取区间范围内的事件点信息
     * @param unknown $startTime
     * @param unknown $finishTime
     */
    private function getTimeIntervalData($startTime,$finishTime){
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = Xphp::$_user['useruuid'];
        $sql = "SELECT event.id,event.event_time,event.event_type,event.event_level,event.description_key,event.description_param, event.event_detail 
             FROM cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent,bd_backup_timepoint bbt  
             WHERE agent.id = event.backup_agent_id and agent.master_agent_uuid= ? and event.event_time 
                       and bbt.timepoint_uuid = agent.timepoint_uuid BETWEEN '". $startTime ."' and '". $finishTime."'";
        if (!empty($authUser)) {  // 表示有管理的用户
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
        }else{
            $sql .= " and bbt.user_uuid = '{$useruuid}' ";
        }
        return $sql;
    }
    /**
     * 获取选择客户端生成的事件信息
     * @param host_uuid:选择主机UUID,vol_uuid:当前选中卷
     */
    public  function getAgentEventInfo($params){
        $volUuid = $params['vol_uuid'];
        $hostUuid = $params['host_uuid'];
        $nodeuuid = $params['nodeuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $timeList = $params['time_list']; //通过时间轴查看事件点的详情
        $sortArr = array('', 'event.event_time', '');
        $taskuuid = $params['task_uuid'];

        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = Xphp::$_user['useruuid'];
        if(count($timeList)>0){
            $confTime = $timeList[0]['confTime'];
            $timeInterval = $timeList[0]['timeInterval'];
            if($timeInterval==1||$timeInterval==2){ //秒级时间表
                $sql = "select event.id,event.event_time,event.event_type,event.event_level,event.description_key,
                        event.description_param, event.event_detail,agent.storage_location 
                    from cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent,bd_backup_timepoint bbt 
                    where agent.id = event.backup_agent_id and agent.master_agent_uuid =?  and event.event_time = ? 
                      and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid =? ";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bbt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bbt.user_uuid = '{$useruuid}' ";
                }
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                
                $sqlParams = array($hostUuid,$confTime,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$confTime,$taskuuid);
                
                $data = $this->dbSelect($sql,$sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);
            }else if($timeInterval==3 || $timeInterval==4){  //分钟级表
                //获取时间范围内的开始时间
                $minDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_minute_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                       and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid =? ";
                $minData = $this->dbSelect($minDataSql,array($hostUuid,$confTime,$taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];
                
                $sql = $this->getTimeIntervalData($startTimestamp,$confTime); //组装获取事件信息的sql语句
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                
                $sqlParams = array($hostUuid,$start, $length);
                $countParams = array($hostUuid);
                
                $data = $this->dbSelect($sql,$sqlParams);
                
                $countData = $this->dbSelect($sqlCount,$countParams);
                
            }else if($timeInterval==5 || $timeInterval==6){ //小时级表
                $hourDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_hour_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                        and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid =? ";
                $minData = $this->dbSelect($hourDataSql,array($hostUuid,$confTime,$taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];
                
                $sql = $this->getTimeIntervalData($startTimestamp,$confTime);
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                
                $sqlParams = array($hostUuid,$start, $length);
                $countParams = array($hostUuid);
                
                $data = $this->dbSelect($sql,$sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);
            }
        }else{
            if($nodeuuid!=""){
                $sql = "select event.id,event.event_time,event.event_type,event.event_level,event.description_key,
                        event.description_param, event.event_detail 
                    from cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent, bd_backup_timepoint bt,bd_storage_resource bsr 
                    where agent.id = event.backup_agent_id and agent.master_agent_uuid= ? and agent.timepoint_uuid = bt.timepoint_uuid 
                      and bt.storage_uuid = bsr.storage_uuid and bsr.node_uuid = ? and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bt.user_uuid ='{$useruuid}' ";
                }
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                $sqlParams = array($hostUuid,$nodeuuid,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$nodeuuid,$taskuuid);
            }else{
                $sql = "select event.id,event.event_time,event.event_type,event.event_level,event.description_key,
                        event.description_param, event.event_detail
                    from cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent, bd_backup_timepoint bt
                    where agent.id = event.backup_agent_id and agent.master_agent_uuid= ? 
                        and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bt.user_uuid = '{$useruuid}' ";
                }

                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                $sqlParams = array($hostUuid,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$taskuuid);
            }
            $data = $this->dbSelect($sql, $sqlParams);
            $countData = $this->dbSelect($sqlCount,$countParams);
        }
        
        $records = array();
        $icon = '<img src ="./img/platform/timepoint.png"> ';
        $records["data"] = array();
        $utils = Xphp::instance('Utils');
        
        if(!empty($data)){
            foreach ($data as $d){
                $eventId = $d['id'];
                $eventType = $d['event_type'];
                $eventLevel = $d['event_level'];
                
                $descriptionKey = $d['description_key'];
                $descriptionParam = $d['description_param'];
                
                $eventTimeStr = $d['event_time'];
                $eventDetail = $d['event_detail'];
                $eventDescription = $this->getEventDesriptionNotice($descriptionKey, $descriptionParam);
                
                $records["data"][] = array(
                    '<input type="checkbox" class="editor-active" name="id[]" value="'.$eventTimeStr.'">',
                    $icon.$eventTimeStr,
                    $this->getEventTypeDesc($eventType),
                    $this->getEventLevelDesc($eventLevel),
                    $eventDescription,
                    $eventTimeStr,
                    $eventId,
                    
                );
            }
        }
        $countNum = count($countData);
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $countNum;
        $records["recordsFiltered"] = $countNum;
        return  json_encode($records);
    }
    
    /**
     * 校验输入时间是否有效
     *  @param host_uuid:选择主机UUID,timepoint:配置时间
     */
    public function verifyTimepointisValid($params){
        $agentUuid = $params['agent_uuid'];
        $timepoint = $params['timepoint'];
        $volUuid = $params['vol_uuid'];
        $createTaskType = $params['task_type']; //创建任务类型
        $taskuuid = $params['task_uuid']; //客户端对应的任务名
        $matchingRecord= 0;
        $unixTimeInfo = $this->getUnixTimetamp($timepoint);
        $unixTime = $unixTimeInfo[0];
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        //获取有效的时间区间
        $sql = "SELECT time.src_start_timepoint,time.src_end_timepoint,time.timepoint_uuid,time.storage_uuid,st.node_uuid,
                    vol.storage_status,time.encrypted_flag,time.detail,agent.storage_location,agent.master_agent_detail 
                FROM cdp_vol_backup_agent agent,bd_backup_timepoint time,bd_storage_resource st,cdp_vol_backup_vol_set vol 
                WHERE agent.timepoint_uuid = time.timepoint_uuid and agent.master_agent_uuid = ? and time.storage_uuid= st.storage_uuid 
                    and vol.backup_agent_id = agent.id and time.task_uuid = ?";
        if($createTaskType==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){
            $sql = $sql." and (agent.storage_location!=? and agent.storage_location!=?)";
            $data = $this->dbSelect($sql,array($agentUuid,$taskuuid,$VolCdpDes['DATA_SOURCE']['STANDBY'],$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        }else{
            $sql = $sql." and agent.storage_location!=?";
            $data = $this->dbSelect($sql,array($agentUuid,$taskuuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        }

        $list = array();
        if(!empty($data)){  
            foreach ($data as $d){
                $srcStartTime = $d['src_start_timepoint'];
                $srcEndTime = $d['src_end_timepoint'];
                $storageStatus = $d['storage_status'];
                $timepointUuid = $d['timepoint_uuid'];
                $storageLocation = $d['storage_location'];
                $masterAgentDetail = json_decode($d['master_agent_detail']);
               
                $osType = $masterAgentDetail->os_type;


                $detail = json_decode($d['detail'],true);
                if($d["encrypted_flag"]==1) { // 时间点加锁
                    $encryptedFlag = true;
                }else {
                    $encryptedFlag = false;
                }
                $passwordAutoFlag = intval($detail['password_auto_flag'])==1 ? true: false;
                $verifyResult =  $this->verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid);
                if($storageLocation==$VolCdpDes['DATA_SOURCE']['STANDBY']){  //如果当前校验数据为备机，择获取最新点
                    $standbySql = "select vol.end_timestamp 
                                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol, bd_backup_timepoint bbt 
                                where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location =? 
                                  and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
                    $standbyData = $this->dbSelect($standbySql,array($agentUuid,$VolCdpDes['DATA_SOURCE']['STANDBY'],$taskuuid));
                    $newTimeStr = $standbyData[0]['end_timestamp'];
                }else{
                    $standbySql = "select vol.end_timestamp 
                                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol , bd_backup_timepoint bbt 
                                where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location !=? 
                                    and agent.storage_location !=? and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ? ";
                    $standbyData = $this->dbSelect($standbySql,array($agentUuid,$VolCdpDes['DATA_SOURCE']['STANDBY'],$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
                    $newTimeStr = $standbyData[0]['end_timestamp'];
                }
                if($unixTime>=$srcStartTime && $unixTime<=$srcEndTime){
                    $storageUuid = $d['storage_uuid'];
                    $nodeUuid = $d['node_uuid'];
                    $timeVolInfo = $this->getSetTimeVolInfo($agentUuid,$timepointUuid,$timepoint,$volUuid,$nodeUuid,$storageUuid,$verifyResult);
                    $list = array(
                        'time_vol_info' => $timeVolInfo,
                        'encrypted_flag' => $encryptedFlag,
                        'password_auto_flag' => $passwordAutoFlag,
                        'timepoint_uuid' => $timepointUuid,
                        'storage_location' =>$storageLocation,
                        'new_time_str' => $newTimeStr,
                        'os_type' =>$osType,
                    );

                }
            }
        }
        return  json_encode($list);
    }

    /**
     * 校验备份集的状态在对应的创建任务类型下是否可用
     * @param integer $storageStatus：备份集状态
     * @param integer $createTaskType：任务创建类型
     */
    public function verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if($createTaskType == Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){  //恢复任务校验
            $taskDesc = Xphp::$_lang['WEB_OS_RECOVERY_TASK'];
        }else if($createTaskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){  //接管任务校验
            $taskDesc = Xphp::$_lang['UI_VOL_CDP_TAKEOVER_TASK'];
        }
        $verifyResult = "";
        switch ($storageStatus){
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_NEW']:  //新建状态 
                $verifyResult = Xphp::$_lang['UI_VOL_CDP_BACKUP_CREATE_TIP'].$taskDesc;
                break;
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']:  //初始同步
                $verifyResult = Xphp::$_lang['UI_VOL_CDP_BACKUP_INIT_SYNC_TIP'].$taskDesc;
                break;
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_REALTIME_SYNC']:  //实时同步
                // 如果当前关联的备份任务正在运行该状态下可以被 接管(只能有一个接管)，但不能被恢复。(注意只是最新的一段时间集，之前停了再启动的那种不算关联)
                // 如果当前关联的备份任务未运行，该状态下可以被 接管 或 恢复。
                $backupTaskArray = $this->getBackupTaskStatus($agentUuid,$timepoint);
                
                $taskStatus = $backupTaskArray['status'];
                $taskuuid = $backupTaskArray['task_uuid'];
                
                $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? and task_type =? order by id desc limit 0,1";
                $lastData = $this->dbSelect($lastSql, array($taskuuid,Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']));
                $newTimepointuuid = $lastData[0]['timepoint_uuid'];

                if($newTimepointuuid == $timepointUuid && $taskStatus==Xphp::$_config['TASKSTATUS']['RUNNING'] && $createTaskType==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){
                    //创建恢复任务判断逻辑
                    $verifyResult =Xphp::$_lang['UI_VOL_CDP_BACKUP_RUNNING_TIP'].$taskDesc;
                }else if($newTimepointuuid == $timepointUuid && $taskStatus==Xphp::$_config['TASKSTATUS']['RUNNING'] && $createTaskType==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
                    //创建接管任务判断逻辑，只能创建一个接管任务
                    $takeoverSql = "SELECT task.task_name FROM cdp_vol_task vol_task,bd_task task 
                                    WHERE task.task_uuid = vol_task.task_uuid and vol_task.master_agent_uuid = ? and task.task_type = ?";
                    $taskoverData = $this->dbSelect($takeoverSql, array($agentUuid,Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']));
                    if(!empty($taskoverData)){
                        $verifyResult = Xphp::$_lang['UI_VOL_CDP_BACKUP_RUNNING_CREATE_TAKEOVER_TASK_TIP'].$taskDesc;
                    }
                } else {
                    //如果在回切实时同步阶段，备机也不能恢复
                    $takeoverSql = "SELECT cvt.task_uuid FROM cdp_vol_task cvt,bd_task bt,cdp_vol_task_takeover_info cti
                        WHERE cti.takeover_standby_agent_uuid =? and bt.task_uuid =cti.task_uuid and cvt.master_agent_uuid =bt.agent_uuid and  cvt.current_task_running_stage=?";
                    $lastData = $this->dbSelect($takeoverSql, [$agentUuid,$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']]);
                    if(!empty($lastData)){
                        $verifyResult =Xphp::$_lang['UI_VOL_CDP_BACKUP_RUNNING_TIP'].$taskDesc;
                    }
                }
                break;
            case $VolCdpDes['VOL_STORAGE_STATUS']['VOL_CDP_VOL_STORAGE_IN_IMAGE_MERGE']:  //镜像合并中
                //如果当前关联的备份任务正在运行，该状态下不能被 接管 或 恢复，则不能即不能被选择创建手动接管或恢复作业【注意只是最新的一段时间集，之前停了再启动的那种不算关联】
                $backupTaskArray = $this->getBackupTaskStatus($agentUuid,$timepoint);
                $taskStatus = $backupTaskArray['status'];
                $taskuuid = $backupTaskArray['task_uuid'];
                
                $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? order by id desc limit 0,1";
                $lastData = $this->dbSelect($lastSql, array($taskuuid));
                $newTimepointuuid = $lastData[0]['timepoint_uuid'];
                if($newTimepointuuid == $timepointUuid){
                    $verifyResult = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_MERGE_TIP'].$taskDesc;
                }
                break;
        }
        return $verifyResult;
    }
    //获取关联备份任务是否有任务，并获取任务状态；
    private  function getBackupTaskStatus($agentUuid,$timepoint){
        $sql = "select task.task_status,task.task_uuid from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol,cdp_vol_task vol_task,bd_task task 
            where task.task_uuid = vol_task.task_uuid and vol_task.master_agent_uuid = agent.master_agent_uuid and agent.id = vol.backup_agent_id
                and (vol.start_timestamp <= ? or vol.end_timestamp >= ?) and agent.master_agent_uuid = ?";
        $taskList = $this->dbSelect($sql,array($timepoint,$timepoint,$agentUuid));
        $statusArray = array();
        if(!empty($taskList)){
            $status = $taskList[0]['task_status'];
            $taskuuid = $taskList[0]['task_uuid'];
            $statusArray = array(
                'status' => $status,
                'task_uuid' => $taskuuid
            );
        }
        return  $statusArray;
    }
    /**
     * 获取有效时间区间内的卷信息
     * @param unknown $agentUuid
     * @param unknown $timepointUuid
     */
    private function getSetTimeVolInfo($agentUuid,$timepointUuid,$timepoint,$volUuid,$nodeUuid,$storageUuid,$verifyResult){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $volSql = "select vol.vol_display_name,vol.vol_uuid,vol.standby_vol_uuid,vol.capacity,vol.is_boot,
                    agent.master_agent_detail,vol.detail 
                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol
                WHERE agent.master_agent_uuid =? and agent.timepoint_uuid = ? and vol.backup_agent_id = agent.id";
        if($volUuid!=""){
            $volSql.= " and vol.vol_uuid = '{$volUuid}'";
        }
        $utils = Xphp::instance('Utils');
        $volList = $this->dbSelect($volSql,array($agentUuid,$timepointUuid));
        $list = array();
        if(!empty($volList)){
            foreach ($volList as $vol){
                $standbyVoluuid = $vol['standby_vol_uuid'];
                $agentDetail = $this->parseBackupAgentDetail($vol['master_agent_detail']);
                $osType = $agentDetail['os_type'];
                $detail = $vol['detail'];

                $volDetail = json_decode($vol['detail']);
                $volType = $volDetail->vol_type;
                $systemVolume = false;
                if($volType & $VolCdpDes['VOL_TYPE']['BD_SYSTEM_VOLUME']){
                    $systemVolume = true;
                }
                 $uefi = false;
                if($volType & $VolCdpDes['VOL_TYPE']['BD_EFI_VOLUME']){
                    $uefi = true;
                }
                $list[] = array(
                    'vol_name' => $vol['vol_display_name'],
                    'new_timestamp' =>$timepoint,
                    'vol_uuid' => $vol['vol_uuid'],
                    'capacity' => $utils->calSize($vol['capacity'], true),
                    'capacity_value' => $vol['capacity'],
                    'standby_vol' => $this->getVolInfoByVoluuid($standbyVoluuid,$agentUuid),
                    'node_uuid' => $nodeUuid,
                    'storage_uuid' => $storageUuid,
                    'is_boot' => $vol['is_boot'],
                    'os_type' => $osType,
                    'verify_result' =>$verifyResult,
                    'system_volume' =>$systemVolume,
                    'is_uefi' => $uefi,
                );
            }
        }
        return $list;
    }
    /**
     * 获取对应卷名
     * @param unknown $standby_vol_uuid
     * @param unknown $agent_uuid
     * @desc 和后台确认，当前版本不考虑备用服务器被删除情况
     */
    private function getVolInfoByVoluuid($standby_vol_uuid,$agent_uuid){
        $sql = "select vol.display_name from bd_agent_disk disk,bd_agent_vol vol
            where disk.agent_uuid = ? and disk.disk_uuid = vol.disk_uuid and vol.vol_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid,$standby_vol_uuid));
        $displayName = "";
        if(!empty($data)){
            $displayName = $data[0]['display_name'];
        }
        return $displayName;
    }
    /**
     * 时间时间字符串转换unix时间戳
     * @param unknown $params
     */
    private function getUnixTimetamp($params){
        $sql = "SELECT UNIX_TIMESTAMP(?)";
        $data = $this->dbSelect($sql,array($params));
        return $data[0];
    }
    
    
    /**
     * 解析系统事件及应用事件
     * @param int $logType      事件类型   系统事件1/应用事件：2
     * @param int $errorCode    错误码
     * @param string $desription    描述
     * @param string $descriptionParam  描述参数
     * @return string
     */
    public function getEventDesriptionNotice($desription, $descriptionParam){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $desStr = $VolCdpDes['VolCdpEventDes'][$desription];
        if($descriptionParam){
            $param = json_decode($descriptionParam, true);
            $desArr = explode("%s", $desStr);
            $desStr = "";
            foreach ($desArr as $k => $v){
                $desStr .= $v . $this->getEachParamsDes($param[$k], false);
            }
        }
        return $desStr;
    }
    
    /**
     * 得到每一项参数的描述
     * @param string $eachParams
     * @param boolean $classShowFlag  是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    private function getEachParamsDes($eachParams, $classShowFlag = true){
        if(empty($eachParams)){
            return "";
        }
        $des = "";
        $arr = explode(":", $eachParams, 2);
        switch($arr[0]){
            case "S":
                if($classShowFlag){
                    $des = '<span  class="font-blue">' . $arr[1] . '</span>';
                }else{
                    $des = $arr[1];
                }
                break;
            case "module_type":
                $des = $this->logModuleTypeDes($arr[1]);
                break;
            case "backup_mode":
                $des = $this->logBackupModeDes($arr[1]);
                break;
            default:
                break;
        }
        return $des;
    }
    
    /**
     * 得到事件对应的级别
     * @param unknown $eventLevel
     */
    private function getEventLevelDesc($eventLevel){
        $eventClass = "label label-sm label-info";   //一般日志
        $levelStr = Xphp::$_lang['WEB_PLATFORM_DES_GENERAL'];
        if(Xphp::$_config['LOGLEVEL']['WARN'] == $eventLevel){
            $eventClass = "label label-sm label-warning";
            $levelStr = Xphp::$_lang['WEB_PLATFORM_DES_WARNING'];
        }elseif(Xphp::$_config['LOGLEVEL']['ERROR'] == $eventLevel){
            $eventClass = "label label-sm label-danger";
            $levelStr = Xphp::$_lang['WEB_PLATFORM_DES_ERROR'];
        }
        $eventLevelStr = '<span class="'.$eventClass.'">'.$levelStr.'</span>';
        return $eventLevelStr;
    }
    /**
     * 得到事件类型
     * @param unknown $eventType 1,系统事件；2,任务事件
     */
    private function getEventTypeDesc($eventType){
        if($eventType==1){
            $eventTypeDesc = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_SYSTEM_EVENT'];
        }else if($eventType==2){
            $eventTypeDesc = Xphp::$_lang['UI_VOL_CDP_BACKUP_SET_TASK_EVENT'];
        }
        return $eventTypeDesc;
    }
    
    /**
     * 得到日志模块描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logModuleTypeDes($index){
        $desConf = include APP_PATH. 'platform/PFDescription.php';
        return $desConf['MODULE_TYPE_DES'][intval($index)];
    }
    
    /**
     * 得到日志备份模式描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logBackupModeDes($index){
        $desConf = include APP_PATH. 'platform/PFDescription.php';
        return $desConf['BACKUP_MODE_DES'][intval($index)];
    }
    
    /**
     * 根据条件组合客户端的名称
     */
    private function agentStr($agentName,$hostName,$ip){
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
        }else {
            $hostInfo = $hostName."(". $ip .")";
        }
        return $hostInfo;
    }
   
}

?>
