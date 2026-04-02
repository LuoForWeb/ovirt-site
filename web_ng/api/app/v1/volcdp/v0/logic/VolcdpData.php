<?php

namespace app\v1\volcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\PfOpcode;

/**
 * note          卷实时 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpData extends Base
{
    /**
     * 校验备份集的状态在对应的创建任务类型下是否可用
     * @param integer $storageStatus：备份集状态
     * @param integer $createTaskType：任务创建类型
     */
    public function verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid)
    {
        if($createTaskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']){  //恢复任务校验
            $taskDesc = xphp_get_lang('WEB_OS_RECOVERY_TASK');
        }else if($createTaskType ==xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']){  //接管任务校验
            $taskDesc = xphp_get_lang('UI_VOL_CDP_TAKEOVER_TASK');
        }
        $verifyResult = "";
        switch ($storageStatus){
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_NEW']:  //新建状态
                $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_CREATE_TIP').$taskDesc;
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']:  //初始同步
                $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_INIT_SYNC_TIP').$taskDesc;
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_REALTIME_SYNC']:  //实时同步
                // 如果当前关联的备份任务正在运行该状态下可以被 接管(只能有一个接管)，但不能被恢复。(注意只是最新的一段时间集，之前停了再启动的那种不算关联)
                // 如果当前关联的备份任务未运行，该状态下可以被 接管 或 恢复。
                $backupTaskArray = $this->getBackupTaskStatus($agentUuid,$timepoint);

                $taskStatus = $backupTaskArray['status'];
                $taskuuid = $backupTaskArray['task_uuid'];

                $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? and task_type =? order by id desc limit 0,1";
                $lastData = $this->dbSelect($lastSql, array($taskuuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP']));
                $newTimepointuuid = $lastData[0]['timepoint_uuid'];
                if($newTimepointuuid == $timepointUuid && $taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING'] && $createTaskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']){
                    //创建恢复任务判断逻辑
                    $verifyResult =xphp_get_lang('UI_VOL_CDP_BACKUP_RUNNING_TIP').$taskDesc;
                }else if($newTimepointuuid == $timepointUuid && $taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING'] && $createTaskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
                    //创建接管任务判断逻辑，只能创建一个接管任务
                    $takeoverSql = "SELECT task.task_name FROM cdp_vol_task vol_task,bd_task task 
                                    WHERE task.task_uuid = vol_task.task_uuid and vol_task.master_agent_uuid = ? and task.task_type = ?";
                    $taskoverData = $this->dbSelect($takeoverSql, array($agentUuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']));
                    if(!empty($taskoverData)){
                        $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_RUNNING_CREATE_TAKEOVER_TASK_TIP').$taskDesc;
                    }
                } else {
                    //如果在回切实时同步阶段，备机也不能恢复
                    $takeoverSql = "SELECT cvt.task_uuid FROM cdp_vol_task cvt,bd_task bt,cdp_vol_task_takeover_info cti
                        WHERE cti.takeover_standby_agent_uuid =? and bt.task_uuid =cti.task_uuid and cvt.master_agent_uuid =bt.agent_uuid and  cvt.current_task_running_stage=?";
                    $lastData = $this->dbSelect($takeoverSql, [$agentUuid,xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']]);
                    if(!empty($lastData)){
                        $verifyResult =xphp_get_lang('UI_VOL_CDP_BACKUP_RUNNING_TIP').$taskDesc;
                    }
                }
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_IMAGE_MERGE']:  //镜像合并中
                //如果当前关联的备份任务正在运行，该状态下不能被 接管 或 恢复，则不能即不能被选择创建手动接管或恢复作业【注意只是最新的一段时间集，之前停了再启动的那种不算关联】
                $backupTaskArray = $this->getBackupTaskStatus($agentUuid,$timepoint);
                $taskStatus = $backupTaskArray['status'];
                $taskuuid = $backupTaskArray['task_uuid'];

                $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? order by id desc limit 0,1";
                $lastData = $this->dbSelect($lastSql, array($taskuuid));
                $newTimepointuuid = $lastData[0]['timepoint_uuid'];
                if($newTimepointuuid == $timepointUuid){
                    $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_MERGE_TIP').$taskDesc;
                }
                break;
        }
        return $verifyResult;
    }

    /**
     * @param $agentUuid
     * @param $timepoint
     * @return array
     * 获取关联备份任务是否有任务，并获取任务状态；
     */
    private  function getBackupTaskStatus($agentUuid,$timepoint)
    {
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
     * 得到当前所有任务的UUID
     * return array
     */
    public function getCurrentAllTaskUUID(): array
    {
        $taskuuid = array();
        $sql = "select task_uuid from bd_task where module_type = ? and delete_flag = ?";
        $sqlParams = array(xphp_get_config('module','MODULE_TYPE')['VOL_CDP'],xphp_get_config('app','FLAG')['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d){
            $taskuuid[] = $d['task_uuid'];
        }
        return $taskuuid;
    }

    /**
     * 获取当前节点下备份客户端
     */
    public function getBackupSetTree ($params)
    {
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $useruuid = xphp_get_user_info()['userUuid'];
        $flag = xphp_get_config('app','FLAG');
        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];

        $taskSql = "select DISTINCT bbt.timepoint_uuid,bbt.task_name,bbt.task_uuid  
                    from bd_backup_timepoint bbt,bd_storage_resource bsr 
                    WHERE bbt.deleted_flag = ? and bbt.available_flag =? and bbt.module_type = ? 
                        and bbt.storage_uuid = bsr.storage_uuid ";
        $taskSqlParams = array($deleted_flag, $available_flag, xphp_get_config('module','MODULE_TYPE')['VOL_CDP']);
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
        $currentTaskUUID = $this->getCurrentAllTaskUUID();//得到当前任务所有uuid
        $tree = array();
        if(!empty($taskData)){
            $tree[] = array(
                "id" => $nodeuuid,
                "pId"=> "",
                "isParent" =>true,
                "icon" => "./img/platform/vmgroup.png",
                "name" => xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DATA_CENTER'),
                "open" =>  true,
                "nocheck" => true,
            );
        }else{
            return json_encode($tree);
        }
        foreach ($taskData as $d){
            $taskuuid = $d['task_uuid'];
            $taskName = (new JobInfo())->getTimepointTaskname($d['task_uuid'],$d['task_name']);
            //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
            $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
            $taskNameStr = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
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
                "title" => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
                "isParent" => true,
            );
            $childTree = $this->getBackupAgentSetInfo($taskuuid,$nodeuuid,$useruuid,$filterStr);
            if($childTree){
                $tree[] = $taskArray;
                $tree = array_merge($tree, $childTree);
            }
        }
        return $tree;
    }

    /**
     * @desc 删除选中备份点
     * @param objcet{'delete_level':0,"vol_backup_set_id":1,"vol_uuid":'',"agent_uuid":"xx","node_uuid":""}
     * @return object
     */
    public function deleteSelectBackupSet($params)
    {
        $backupsetList = $params['backupsetList'];
        $delObj = $params['delObj'];
        $deleteLevel = $backupsetList['delete_level'];
        $node_uuid = $params['node_uuid'];
        $taskDelte = $params['task_delete'];
        $agentuuid = $params['agent_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $operate = (new PFOpcode())->getOpcodeDes($opName);
        $userUuidData = array();
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $backupsetList['vol_backup_set_id'] 获取 timepointuuid
            $backup_agent_idArr = $this->dbSelect("select backup_agent_id from cdp_vol_backup_vol_set where id = ?", [$backupsetList['vol_backup_set_id']]);
            $backup_agent_id = array_column($backup_agent_idArr, 'backup_agent_id');
            $timepointuuidArr = $this->dbSelect("select timepoint_uuid from cdp_vol_backup_agent where id in (" . implode(',', $backup_agent_id) . ")", []);
            $timepointuuid = array_column($timepointuuidArr, 'timepoint_uuid');

            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [xphp_get_user_info()['userUuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'),'error'));
            }
        }

        switch ($deleteLevel){
            case 0:
                $timepointDes = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_UNKNOWN_TYPE');
                break;
            case 1:
                $timepointDes = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DELETE_SINGLE_SET');
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
                if(count($singSet)>0){
                    exit($this->muOpResult(false, $operate,xphp_get_lang('UI_VOL_CDP_DELETE_FAIL').", ".$singSet['desc'].", ".xphp_get_lang('UI_PUBLIC_TASK_RNAME').": ".$singSet['task_name'],'warning'));
                }
                break;
            case 2:  //判断删除整个卷备份集是否有任务，有择不允许删除；
                $timepointDes = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DELETE_ALL_SET');
                $pointUUID = $this->getPointuuidByVoluuid($backupsetList['vol_uuid']);
                /** * 验证权限*/
                $timepointuuids = implode("','", $pointUUID);
                $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
                $this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'),$timepointDes);

                $volSet = $this->volSetDelExist($backupsetList['vol_uuid'],$backupsetList['vol_backup_set_id'],$pointUUID);
                //判断是否有接管任务，主要判断停止状态的接管任务
                $sql = "select bt.task_name from bd_task bt, cdp_vol_task vol_task,cdp_vol_task_takeover_info takeover 
                    where bt.task_uuid = vol_task.task_uuid and bt.task_uuid = takeover.task_uuid 
                    and ( bt.agent_uuid = ? or takeover.takeover_standby_agent_uuid = ?)";
                $data = $this->dbSelect($sql, [$backupsetList['agent_uuid'],$backupsetList['agent_uuid']]);
                if(count($volSet)>0 || !empty($data)){
                    $reTaskName = count($volSet)>0?$volSet['task_name']:$data[0]['task_name'];
                    if($volSet['desc']==""){
                        $volDesc = xphp_get_lang('WEB_VOL_CDP_DELETE_VOL_SET_CORRELATION_TASK_TIPS');
                    }else{
                        $volDesc = $volSet['desc'];
                    }
                    exit($this->muOpResult(false, $operate,xphp_get_lang('UI_VOL_CDP_DELETE_FAIL').", ".$volDesc.", ".xphp_get_lang('UI_PUBLIC_TASK_RNAME').": ".$reTaskName,'warning'));
                }
                break;
            case 3:  //判断删除客户端是否有任务，有择不允许删除；
                $timepointDes = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DELETE_CLIENT_SET_SELECTED');
                $pointUUID = $this->getPointuuidByAgentuuid($agentuuid);
                /** * 验证权限*/
                $timepointuuids = implode("','", $pointUUID);
                $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
                $this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'),$timepointDes);

                //检查删除客户端备份数据是否有备份任务正在运行，恢复任务和接管任务存在
                $this->checkClientBackupTaskIsRunning($agentuuid);
                $this->checkClientRecoveryTask($agentuuid);
                $this->checkClientTakeoverTask($agentuuid);
                break;
            default:
                $timepointDes = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_UNKNOWN_TYPE');
        }
        $msg = json_encode($backupsetList);
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

        $taskName = "";
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
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
            return $this->muOpResult(true, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'),"" , "success");
        }else{
            return $this->muOpResult(false, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'),"" , "warning");
        }
    }

    /**
     * 删除选中的事件信息
     * @param unknown $params
     */
    public function deleteSelectEventInfo($params)
    {
        $checkList = $params['checkList'];
        $nodeuuid = $params['node_uuid'];
        $eventArray = implode(",", $checkList);
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 先从 cdp_vol_agent_event_info 取出 backup_agent_id ， 然后从 cdp_vol_backup_agent 取出 timepoint_uuid ，最后在 bd_backup_timepoint 取出用户uuid
            $backup_agent_id_list = $this->dbSelect("select backup_agent_id from cdp_vol_agent_event_info where id in (".$eventArray .")");
            $timepoint_uuid_list = $this->dbSelect("select timepoint_uuid from cdp_vol_backup_agent where id in (". implode(',', array_column($backup_agent_id_list, 'backup_agent_id')) .")");

            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", array_column($timepoint_uuid_list, 'timepoint_uuid')) . "')", [xphp_get_user_info()['userUuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DELETE_INFO'), $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'),'error'));
            }
        }

        $sql = "delete from cdp_vol_agent_event_info where id in (".$eventArray .")";
        $result = $this->dbExec($sql);
        if($result){
            return $this->muOpResult(true, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DELETE_INFO'),"" , "success");
        }else{
            return $this->muOpResult(false, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DELETE_INFO'),"" , "warning");
        }
    }

    /**
     * 删除选中标签点
     * @param unknown $params
     */
    public function deleteSelectLablePoint($params)
    {
        $labelList = $params['labelList'];
        $nodeuuid = $params['node_uuid'];
        $opName = 'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL';
        $operate = (new PfOpcode())->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            $chk1_list = $this->dbSelect("select timepoint_uuid from cdp_vol_backup_agent where id = ?", [$labelList['backup_agent_id']]);
            $pointUUID = array_column($chk1_list, 'timepoint_uuid');
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $pointUUID) . "')", [xphp_get_user_info()['userUuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'),'error'));
            }
        }

        $sync = FALSE;
        $command = TRUE;
        $msg = json_encode($labelList);
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
     * 检查是否拥有主机保护的操作权限
     * @param array $userIdList 操作资源的拥有者uuid列表
     * @return void
     */
    private function checkHostOperatePermission(array $userIdList,$operateDes)
    {
        // 关联管理用户判断 数据库保护 - 操作 vol_cdp_protect_operate
        $authUser = $_SESSION['authUser']['vol_cdp_protect_operate'] ?? [];
        $checkOperate = xphp_check_operate(xphp_get_user_info()['userUuid'], $userIdList, $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, $operateDes, xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'warning'));
        }
    }

    /**
     * 获取删除客户端是否有备份任务正在运行，有择不允许删除
     * @param $agent_uuid
     */
    private function checkClientBackupTaskIsRunning($agent_uuid){
        $sql = "SELECT task.task_status,task.task_name 
                from bd_task task,cdp_vol_task vol 
                where vol.task_uuid = task.task_uuid and vol.master_agent_uuid = ? and task.task_type = ? and task_status = ? ";
        $data = $this->dbSelect($sql,array($agent_uuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'],xphp_get_config('task','TASKSTATUS')['RUNNING']));
        if(!empty($data)){
            exit($this->muOpResult(false,xphp_get_lang('UI_VOL_CDP_DELETE_AGENT_ALL_DATA'),
                xphp_get_lang('UI_VOL_CDP_DELETE_AGENT_ALL_DATA_FAIL').",".xphp_get_lang('UI_VOL_CDP_DELETE_AGENT_BACKUP_TASK_RUNNING_EXIST').". ".xphp_get_lang('UI_PUBLIC_TASK_RNAME').":".$data[0]['task_name'],'warning'));
        }
    }

    /**
     * 获取数据源客户端是否有恢复任务正在运行
     * @param $agnet_uuid
     */
    private function checkClientRecoveryTask($agnet_uuid){
        $sql = "select vol_task.id,task.task_name  
                from bd_task task,cdp_vol_task vol_task 
                where vol_task.task_uuid = task.task_uuid and vol_task.master_agent_uuid = ? and task.task_type = ? and task_status = ? ";
        $data = $this->dbSelect($sql,array($agnet_uuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'],xphp_get_config('task','TASKSTATUS')['RUNNING']));
        if(!empty($data)){
            exit($this->muOpResult(false,
                xphp_get_lang('UI_VOL_CDP_DELETE_AGENT_ALL_DATA'),
                xphp_get_lang('UI_VOL_CDP_DELETE_FAIL').",".xphp_get_lang('UI_VOL_CDP_DELETE_AGENT_TAKEOVER_TASK_EXIST').". ".xphp_get_lang('UI_PUBLIC_TASK_RNAME').":".$data[0]['task_name'],'warning'));
        }
    }

    /**
     * 获取数据源客户端是否有接管任务
     * @param $agnet_uuid
     */
    private function checkClientTakeoverTask($agnet_uuid){
        $sql = "select vol_task.id,task.task_name from bd_task task,cdp_vol_task vol_task 
                where vol_task.task_uuid = task.task_uuid and vol_task.master_agent_uuid = ? and task.task_type = ?";
        $data = $this->dbSelect($sql,array($agnet_uuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']));
        if(!empty($data)){
            exit($this->muOpResult(false,
                xphp_get_lang('WEB_NODE_AGENT_OP_DEL'),
                xphp_get_lang('WEB_CLIENT_DELETE_TASK_EXIST').",".xphp_get_lang('UI_PUBLIC_TASK_RNAME').":".$data[0]['task_name'],'warning'));
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
    private function volSetDelExist($vol_uuid,$vol_id,$point_uuid){
        $sql = "select task.task_name,task.task_type,task.task_status,vt.current_task_running_stage 
                from cdp_vol_task_vol vol,bd_task task,cdp_vol_task vt 
                where vol.task_uuid = task.task_uuid and vol.vol_uuid =? and vt.task_uuid = task.task_uuid 
                  and (task.task_status =? or task.task_status = ?  or task.task_status = ?)";
        $parameter = array($vol_uuid,xphp_get_config('task','TASKSTATUS')['RUNNING'],xphp_get_config('task','TASKSTATUS')['SUCCESSED'],xphp_get_config('task','TASKSTATUS')['NETWORK_FAULT']);
        $data = $this->dbSelect($sql, $parameter);
        $dataList = array();
        if(!empty($data)){
            $currentStage =$data[0]['current_task_running_stage'];
            $taskStatus = $data[0]['task_status'];
            if($taskStatus == xphp_get_config('task','TASKSTATUS')['SUCCESSED']  && ($currentStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['IN_TAKEOVER_STARTING'] || $currentStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['IN_TAKEOVER'])){
                $dataList = array(
                    'task_name' => $data[0]['task_name'],
                    'task_type' => $data[0]['task_type'],
                    'desc' => xphp_get_lang('UI_VOL_CDP_DELETE_BACKUP_TASK_EXIST')
                );
            }elseif($taskStatus == xphp_get_config('task','TASKSTATUS')['RUNNING'] || xphp_get_config('task','TASKSTATUS')['NETWORK_FAULT']){
                $dataList = array(
                    'task_name' => $data[0]['task_name'],
                    'task_type' => $data[0]['task_type'],
                    'desc' => xphp_get_lang('UI_VOL_CDP_DELETE_BACKUP_TASK_EXIST')
                );
            }
        }
        return $dataList;
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
     * 判断单个备份集是否有任务存在，存在则不允许删除
     * 通过备份集ID获取备份集所在客户端UUID，再通过客户端uuid 在cdp_vol_task中获取对应任务表是否有任务存在，获取任务类型。
     */
    private  function singleSetDelExist($vol_uuid,$vol_id,$pointUUID,$taskDelte)
    {
        $sql = "select agent.master_agent_uuid from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol_set 
            where vol_set.vol_uuid = ? and vol_set.id = ? and vol_set.backup_agent_id = agent.id";
        $data = $this->dbSelect($sql, array($vol_uuid,$vol_id));
        $dataList = array();
        if(!empty($data)){
            $flag = xphp_get_config('app','FLAG');
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

                if($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP']){  //备份
                    //备份任务获取任务状态状态，如果任务正在运行择判断当前删除备份集是否是最新备份集，是则不允许删除
                    if($taskDelte){
                        break;
                    }
                    $dataList = $this->getBackupTaskBkSetInfo($taskuuid,$taskStatus,$taskName,$taskType,$pointUUID);
                    if(count($dataList)>0){
                        break;
                    }
                }

                if($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'] && $taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING']){  //运行中的恢复任务

                    $dataList = $this->getRecoverTaskBkSetInfo($vol_uuid,$vol_id,$taskName,$taskType);
                    if(count($dataList)>0){
                        break;
                    }
                }

                if($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){  //接管
                    $dataList = $this->getTakeoverBkSetInfo($vol_uuid,$vol_id,$taskName,$taskType);
                    if(count($dataList)>0){
                        break;
                    }
                }

            }
        }
        return $dataList;
    }

    /**
     * 获取删除备份集是否包含正在运行的恢复任务
     * @param unknown $voluuid
     * @param unknown $volId
     * @param unknown $taskName
     * @param unknown $taskType
     */
    private function getRecoverTaskBkSetInfo($voluuid,$volId,$taskName,$taskType)
    {
        $sql = "select tv.task_uuid from cdp_vol_task_vol tv,cdp_vol_backup_vol_set vs where tv.vol_uuid = ? 
                and tv.vol_uuid = vs.vol_uuid and (UNIX_TIMESTAMP(tv.recovery_target_timestamp) >= UNIX_TIMESTAMP(vs.start_timestamp) 
                and UNIX_TIMESTAMP(tv.recovery_target_timestamp) <= UNIX_TIMESTAMP(vs.end_timestamp)) and vs.id = ?";
        $data = $this->dbSelect($sql, array($voluuid,$volId));
        $dataList = array();
        if(!empty($data)){
            $dataList = array(
                'task_name' => $taskName,
                'task_type' => $taskType,
                'desc' => xphp_get_lang('UI_VOL_CDP_BACKUP_RECOVERY_TASK_EXIST'),
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
    private  function getTakeoverBkSetInfo($voluuid,$volId,$taskName,$taskType)
    {
        $sql = "select tv.task_uuid from cdp_vol_task_takeover_info ti,cdp_vol_task_vol tv,cdp_vol_backup_vol_set vs
                where tv.task_uuid = ti.task_uuid and tv.vol_uuid = ? and tv.task_uuid =ti.task_uuid and vs.vol_uuid = tv.vol_uuid
                    and (UNIX_TIMESTAMP(ti.takeover_timestamp) >= UNIX_TIMESTAMP(vs.start_timestamp) and
                    UNIX_TIMESTAMP(ti.takeover_timestamp) <= UNIX_TIMESTAMP(vs.end_timestamp)) and vs.id = ?";
        $data = $this->dbSelect($sql, array($voluuid,$volId));
        $dataList = array();
        if(!empty($data)){
            $dataList = array(
                'task_name' => $taskName,
                'task_type' => $taskType,
                'desc' => xphp_get_lang('UI_VOL_CDP_BACKUP_TAKEOVER_TASK_EXIST'),
            );
        }
        return  $dataList;
    }

    /**
     * 获取备份任务对应的备份集是否可以删除
     * @param unknown $taskuuid
     * @param unknown $taskStatus
     * 判断备份任务是否有多个备份集，如果只有一个备份集择当前备份集不能删除。如果有多个备份集，需要判断当前备份集是否是最新备份集(最新备份集可以从bd_time_point中获取)，是不允许删除
     */
    private function getBackupTaskBkSetInfo($taskuuid,$taskStatus,$taskName,$taskType,$pointUUID){
        $dataList = array();
        $sql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $num = count($data);
        if($num==1 && ($taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING']
                || $taskStatus==xphp_get_config('task','TASKSTATUS')['SUCCESSED']
                || $taskStatus==xphp_get_config('task','TASKSTATUS')['NETWORK_FAULT'])){  //运行中,成功
            $dataList = array(
                'task_name' => $taskName,
                'task_type' => $taskType,
                'desc' => xphp_get_lang('UI_VOL_CDP_BACKUP_TASK_RUNNING'),
            );
        }else if($num>1){
            $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? order by id desc limit 0,1";
            $lastData = $this->dbSelect($lastSql, array($taskuuid));
            $newTimepointuuid = $lastData[0]['timepoint_uuid'];
            if($newTimepointuuid == $pointUUID && ($taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING']
                    || $taskStatus==xphp_get_config('task','TASKSTATUS')['SUCCESSED']
                    || $taskStatus==xphp_get_config('task','TASKSTATUS')['NETWORK_FAULT'])){ //是否为最新备份集，且
                $dataList = array(
                    'task_name' => $taskName,
                    'task_type' => $taskType,
                    'desc' => xphp_get_lang('UI_VOL_CDP_DELETE_BACK_TASK_NEW'),
                );
            }
        }

        return  $dataList;
    }

    /**
     * 获取客户端对应的备份集信息，通过任务uuid
     * @return void
     */
    private function getBackupAgentSetInfo($taskuuid,$nodeuuid,$useruuid,$filterStr)
    {
        $flag = xphp_get_config('app','FLAG');
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
                    and agent.storage_location !=? and bbt.task_uuid = ? ";
        $sqlParams = array($deleted_flag, $available_flag, xphp_get_config('module','MODULE_TYPE')['VOL_CDP'],
            xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            if (!empty($authUser)) { // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
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
                "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_SET_CLIENT_IP').":".$agentIp,
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
     * 获取恢复数据源主机信息
     */
    public function parseBackupAgentDetail($detail){
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
     * 根据条件组合客户端的名称
     */
    public function agentStr($agentName,$hostName,$ip){
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
        }else {
            $hostInfo = $hostName."(". $ip .")";
        }
        return $hostInfo;
    }

    /**
     * 获取客户端对应的备份卷信息
     * @param $agentUUID:选中节点UUID,$treeId:父节点ID,$nodeuuid:存储节点uuid
     */
    public  function getBackupVolData($agentUUID,$treeId,$nodeuuid,$backupSetNodeUuid,$parentAgentIp,$taskuuid)
    {
        $flag = xphp_get_config('app','FLAG');

        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志位
        $useruuid = xphp_get_user_info()['userUuid'];
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        $sql = 'SELECT vol.vol_display_name,vol.vol_uuid,vol.capacity,vol.is_boot,bbt.encrypted_flag,agent.storage_location,vol.vol_name,bsr.node_uuid,agent.master_agent_detail 
                FROM bd_backup_timepoint bbt,cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol,bd_storage_resource bsr
                WHERE bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and
                      vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and bbt.deleted_flag = ? and
                      bbt.available_flag = ? and bbt.module_type = ? and storage_location !=? and bbt.task_uuid = ?';

        $sqlParams = array($agentUUID,$deleted_flag, $available_flag, xphp_get_config('module','MODULE_TYPE')['VOL_CDP'],xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            if (!empty($authUser)) {
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
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
            $volCapacity = v1_calsize($d['capacity'], true);
            $isBoot = $d['is_boot'];
            $isBootStr = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DATA_VOL');
            if($isBoot == $flag['SET']){
                $isBootStr = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_SYSTEM_VOL');
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
                "name" =>$d['vol_display_name']."(".$isBootStr."，".xphp_get_lang('UI_PUBLIC_CAPACITY').":".$volCapacity.")",
                "title" => $d['vol_display_name']."(".$isBootStr."，".xphp_get_lang('UI_PUBLIC_CAPACITY').":".$volCapacity.")",
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

}
