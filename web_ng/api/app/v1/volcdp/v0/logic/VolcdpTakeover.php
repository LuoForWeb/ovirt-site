<?php

namespace app\v1\volcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\Recover;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;

class VolcdpTakeover extends Base
{
    public function getBackupAgentNetworkInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $sql = "select id, master_agent_detail from cdp_vol_backup_agent where master_agent_uuid = ? order by id limit 0,1";
        $data = $this->dbSelect($sql, array($agentuuid));
        $detail = json_decode($data[0]['master_agent_detail']);
        $nicList = $detail->nic_list;
        return $nicList;
    }

    /**
     * 获取接管目标客户端
     * @param unknown $params
     */
    public function getTakeoverTargetHost($params){
        $user_uuid = xphp_get_user_info()['userUuid'];
        $node_uuid = $params['node_uuid'];
        $master_agent_uuid = $params['master_uuid'];
        $master_os_type = $params['master_os_type'];
        $data_source = $params['data_source'];
        $taskuuid = $params['task_uuid'];
        $agentuuidArr = $this->getClientUuids();
        if (empty($agentuuidArr)) {
            // 表示没数据
            $list = array();
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  agent_type =?  and os_type = ? ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr} ";
        }

        $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'],$master_os_type);
        $data = $this->dbSelect($sql,$sqlParams);
        $list = array();
        $list[] = array(
            "uuid" => "",
            "text" => xphp_get_lang('UI_VOL_CDP_TAKEOVER_TARGET_MACHINE_SELECT'),
            "value" => 0,
            "os_type" => "",
            "in_task" => false,
            "task_name" => "",
            "vol_info" => [],
        );
        foreach ($data as $host_info){
            //如果节点状态正常
            $agentUUID = $host_info['agent_uuid'];
            $onlineFlag = $host_info['online_flag'];

            $inTask = false;
            $task_name = " ";
            $isCreateTask = $this->taskExist($agentUUID);

            if(count($isCreateTask)>0){
                $task_name = $isCreateTask['task_name'];
                $inTask = true;
            }
            //判断是否有备份任务
            $isCreateBackupTask = $this->hostIsUsedBackupTask($agentUUID);
            if(count($isCreateBackupTask)>0){
                $task_name = $isCreateBackupTask['task_name'];
                $inTask = true;
            }
            //判断目标主机是否作为接管备机被使用
            $isUsedTakeoverStandby = $this->hostIsUsedTakeoverStandby($agentUUID);
            if(count($isUsedTakeoverStandby)>0){
                $task_name = $isUsedTakeoverStandby['task_name'];
                $inTask = true;
            }
            //判断目标主机是否作为双机镜像备机使用
            $isUsedHaStandby = $this->hostIsUsedHaStandby($agentUUID);
            if(count($isUsedHaStandby)>0){
                $task_name = $isUsedHaStandby['task_name'];
                $inTask = true;
            }

            //判断目标主机是否作为双机镜像备机使用
            $isUsedFbTarget = $this->hostIsUsedFbTarget($agentUUID);
            if(count($isUsedFbTarget)>0){
                $task_name = $isUsedFbTarget['task_name'];
                $inTask = true;
            }

            //判断目标主机是否作为双机镜像备机使用
            $isUsedRecoveryTarget = $this->hostIsUsedRecoveryTarget($agentUUID);
            if(count($isUsedRecoveryTarget)>0){
                $task_name = $isUsedRecoveryTarget['task_name'];
                $inTask = true;
            }

            if($data_source==xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']){
                $standbyConfuuid = $this->getDataSourceStandbyInfo($master_agent_uuid);
                if(!in_array($agentUUID,$standbyConfuuid)){
                    continue;
                }
            }

            $hostVolInfo = $this->getHostVolInfoByUUID($agentUUID);
            $hostDesc = $this->agentStr($host_info['agent_name'],$host_info['hostname'],$host_info['ip']);

            if(xphp_get_config('app','FLAG')['SET'] == $onlineFlag){
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            }else{
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
                $hostDesc = $hostDesc."--".$statusDes;
            }


            $list[] = array(
                'uuid' => $agentUUID,
                'data_source_agentuuid' => $master_agent_uuid,
                'text' => $hostDesc,
                'value' => $host_info['agent_uuid'],
                'os_type' => $host_info['os_type'],
                'in_task' => $inTask,
                'task_name' => $task_name,
                'vol_info' => $hostVolInfo['vol_info'],
                'mount_info' => $hostVolInfo['mount_info'],
                'agent_type' => $host_info['agent_type'],
                'net_model' => intval($host_info['net_model']),
                'online_flag' => v1_parse_flag_to_bool($onlineFlag)
                //'vol_info' => $this->getHostVolInfoByUUID($host_info['agent_uuid'])
            );
        }
        return $list;
    }

    /**
     *  判断数据对应主机ip是否在线,
     */
    public function checkIpIsOnline($params){
        $agentuuid = $params['agent_uuid'];
        $sql = "select master_agent_detail from cdp_vol_backup_agent where master_agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $masterAgentDetail = json_decode($data[0]['master_agent_detail']);

        $agentIp = $masterAgentDetail->ip;
        $hostname = $masterAgentDetail->hostname;

        $output = exec('ping -c 1 -w 1 '.$agentIp,$out,$status);
        if($status==0){  //代表能ping通
            $exists = false;
        }else{
            $exists = true;
        }
        $list = array(
            'host_name' => $hostname,
            'ip' => $agentIp,
            'exists' => $exists
        );

        return $list;
    }

    /**
     * 获取选中客户端应用信息
     */
    public function getClientTakeoverAppInfo($params){
        $vol_set = $params['vol_set'];
        $agent_uuid = $params['agent_uuid'];
        $take_over_time = $params['take_over_time'];
        $standby_host_uuid = $params['standby_host_uuid'];


        $sql = "SELECT master_agent_detail,id,master_agent_uuid from cdp_vol_backup_agent where master_agent_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid));
        $host_info = "";
        $agent_id_list = [];
        $os_type = "";
        foreach ($data as $row){
            $master_agent_detail = json_decode($row['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agent_ip = $master_agent_detail->ip;
            $os_type = $master_agent_detail->os_type;
            $host_info = $hostname."(".$agent_ip .")";
            array_push($agent_id_list,$row['id']);
        }
        $type_icon = "./img/platform/linux.png";
        if($os_type=='Windows'){
            $type_icon = "./img/platform/windows.png";
        }

        $tree = array();
        $tree[] = array(
            "id" => $agent_uuid,
            "pId" => "",
            "name" =>  $host_info,
            "title" => xphp_get_lang('UI_VOL_CDP_HOST').$host_info,
            "isParent" => true,
            "uuid" => $agent_uuid,
            "nocheck" => true,
            "isApp" =>false,
            "type" => 1,
            "icon" => $type_icon,
            "clickshow" => false,
            "checked" => false,
            "chkDisabled" => false,
            "open" =>  true,
        );
        $appList = $this->getAppType($agent_uuid,$agent_id_list,$take_over_time,$standby_host_uuid);//应用类型
        if(!empty($appList)){
            $tree = array_merge($tree, $appList);
        }
        return $tree;
    }

    /**
     * 获取客户端备份集应用类型
     */
    private function getAppType ($agentUUID,$agent_id_list,$take_over_time,$standby_host_uuid){
        $id_str =  join(",", $agent_id_list);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid 
                FROM cdp_vol_backup_app 
                where backup_agent_id in ({$id_str}) GROUP BY app_type";
        $data = $this->dbSelect($sql);
        $appExist = false;
        $info = array();
        if(count($data)>0){
            foreach ($data as $appInfo){
                $alaisName = $appInfo['app_name'];
                $appTypeValue = $appInfo['app_type'];
                $appUuid = $appInfo['app_uuid'];
                $appTypeDes = xphp_get_config('db','DB_TYPE_DES')[intval($appTypeValue)];
                if($appTypeValue == xphp_get_config('db','DB_TYPE')['SQLSERVER']){
                    $icon = "./img/db/sqlserver.png";
                }else if($appTypeValue == xphp_get_config('db','DB_TYPE')['ORACLE']){
                    $icon = "./img/db/oracle.png";
                }else if($appTypeValue == xphp_get_config('db','DB_TYPE')['MYSQL']){
                    $icon = "./img/db/mysql.png";
                }
                $id = $agentUUID.$appTypeValue;
                $info[] = array(
                    "id" => $id,
                    "pId" => $agentUUID,
                    "name" => $appTypeDes,
                    "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_DB_APPLICATION'),
                    "appTypeValue" => $appTypeValue,
                    'agent_id_list' =>$agent_id_list,
                    "icon" => $icon,
                    "isApp" =>false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" =>  true,
                );
                $childTree = $this->getAppData($agentUUID,$id,$appTypeValue,$agent_id_list,$take_over_time,$standby_host_uuid);
                if(!empty($childTree)){
                    $info = array_merge($info, $childTree);
                }else{
                    $info = array();
                }
            }
        }
        return $info;
    }

    /**
     * 获取应用类型下的应用信息，此处需要注意节点对应关系
     */
    private function getAppData($agentUUID,$pId,$appTypeValue,$agent_id_list,$take_over_time,$standby_host_uuid){
        $id_str =  join(",", $agent_id_list);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid
                FROM cdp_vol_backup_app
                where backup_agent_id in ({$id_str}) and app_type = ? GROUP BY app_uuid";
        $data = $this->dbSelect($sql,array($appTypeValue));
        $info = array();
        foreach ($data as $d){
            $id = $pId.$d['app_uuid'];
            $app_name = $d['app_name'];
            $app_type = $d['app_type'];

            $standbyAppList = $this->getStandbyAppinfo($standby_host_uuid,$app_type,$app_name);
            $standbyAppUuid = $standbyAppList['app_uuid'];
            $standbyAppName = $standbyAppList['standby_app_name'];
            $info[] = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $app_name,
                "title" => $d['app_name'],
                "uuid" => $d['app_uuid'],
                "app_type" => $app_type,
                "app_type_value" => $appTypeValue,
                "icon" => "./img/db/instance.png",
                'appUUID' =>$d['app_uuid'],
                'standby_app_uuid' =>$standbyAppUuid,
                'standby_app_name' => $standbyAppName,
                "clickshow" => false,
                "checked" => false,
                "isApp" => true,
                "chkDisabled" => false,
            );
            $childTree = $this->getAppModuleInfo($id,$d['app_uuid'],$appTypeValue,$agent_id_list,$take_over_time);
            if(!empty($childTree)){
                $info = array_merge($info, $childTree);
            }else{
                $info = array();
            }
        }
        return $info;
    }

    /**
     * 通过选中备机的uuid及主机对应应用的类型及应用名,自动获取备机应用信息
     * @param $standby_host_uuid:备机UUID,$app_type:选定主机应用类型,$app_name:应用名
     */
    private function getStandbyAppinfo($standby_host_uuid,$app_type,$app_name){
        $sql = 'select app.app_uuid,app.app_name from bd_agent as agent,bd_agent_app as app 
                where agent.agent_uuid = ? and agent.agent_uuid = app.agent_uuid and app.app_type = ?';
        $data = $this->dbSelect($sql,array($standby_host_uuid,$app_type));
        if(!empty($data)){
            $app_uuid = $data[0]['app_uuid'];
            $standby_app_name = $data[0]['app_name'];
        }else{
            $app_uuid = "";
            $standby_app_name = "";
        }
        $standbyAppList = array(
            "app_uuid" => $app_uuid,
            "standby_app_name" => $standby_app_name,
        );
        return $standbyAppList;
    }

    /**
     * 获取app 组件信息
     */
    private function getAppModuleInfo($pId,$appUUID,$appTypeValue,$agent_id_list,$take_over_time){
        $takeoverTimetampArry = $this->getUnixTimetamp($take_over_time);
        $takeoverTimetamp = $takeoverTimetampArry[0];
        $sql = "SELECT DISTINCT module.id,module.module_name,module.module_detail  
               FROM cdp_vol_backup_app_module module,cdp_vol_backup_app app 
               WHERE module.backup_app_id = app.id and app.app_uuid = ? and 
                     (unix_timestamp(module.start_timestamp) <= ? and unix_timestamp(module.end_timestamp) >=?) 
                     GROUP BY module_name";
        $data = $this->dbSelect($sql,array($appUUID,$takeoverTimetamp,$takeoverTimetamp));
        $moduleInfo = array();
        if(!empty($data)){
            foreach ($data as $d){
                $id = $pId.$d['id'];
                $moduleInfo[] = array(
                    "id" => $id,
                    "pId" => $pId,
                    "name" => $d['module_name'],
                    "uuid" => $d['app_uuid'],
                    "module_name" => $d['module_name'],
                    "icon" => "./img/db/database.png",
                    "app_type_value" => $appTypeValue,
                    "clickshow" => false,
                    "checked" => false,
                    "nocheck" => true,
                    "isApp" => false,
                    "chkDisabled" => false,
                );
            }
        }
        return $moduleInfo;
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
     * 检查选择的目标是否有被其它任务选中
     * ---当主机配置了其他任务中的任意角色：备份主机，恢复目标机，双机镜像备机，自动接管备机，手动接管备机和回切目标机器时不能再作为手动接管备机使用
     * @param unknown $agentUuids
     */
    private function taskExist ($agentUUID){
        $sql = "SELECT task.task_status,task.task_name,task_type FROM bd_task task,cdp_vol_task_takeover_info takeover 
            WHERE takeover.task_uuid = task.task_uuid and task.task_type = ? and takeover.takeover_standby_agent_uuid = ? and task.delete_flag = ? ";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentUUID,$delFlag));
        $taskList = array();
        if(!empty($data)){
            $taskList = array(
                'task_name' => $data[0]['task_name'],
                'task_status' => $data[0]['task_status'],
                'task_type' => $data[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 目标机器是否被创建备份任务
     * @param $agentuuid
     */
    private function hostIsUsedBackupTask($agentuuid){
        $sql = "SELECT task.task_status,task.task_name,task_type FROM bd_task task,cdp_vol_task cvt 
                where cvt.task_uuid = task.task_uuid and task.task_type = ? and cvt.master_agent_uuid = ? and  task.delete_flag = ?";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentuuid,$delFlag));
        $taskList = array();
        if(!empty($data)){
            $taskList = array(
                'task_name' => $data[0]['task_name'],
                'task_status' => $data[0]['task_status'],
                'task_type' => $data[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedTakeoverStandby($agentuuid){
        $sqlTo = "select task.task_name,task.task_name,task.task_type,task.task_status from cdp_vol_task_takeover_info vt,bd_task task 
                where takeover_standby_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo,array($agentuuid));
        $taskList = array();
        if(!empty($dataTo)){
            $taskList = array(
                'task_name' => $dataTo[0]['task_name'],
                'task_status' => $dataTo[0]['task_status'],
                'task_type' => $dataTo[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为镜像备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedHaStandby($agentuuid){
        $sqlTo = "select task.task_name,task.task_type,task.task_status  from cdp_vol_task vt,bd_task task 
                where vt.standby_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo,array($agentuuid));
        $taskList = array();
        if(!empty($dataTo)){
            $isEnabled =  true;
            $taskName = $dataTo[0]['task_name'];
            $taskList = array(
                'task_name' => $taskName,
                'task_status' => $dataTo[0]['task_name'],
                'task_type' => $dataTo[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为回切目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedFbTarget($agentuuid){
        $sqlFa = "select task.task_name,task.task_type,task.task_status from cdp_vol_task_takeover_failback_info vt,bd_task task 
                where vt.failback_target_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataFa = $this->dbSelect($sqlFa,array($agentuuid));
        $taskList = array();
        if(!empty($dataFa)){
            $isEnabled =  true;
            $taskName = $dataFa[0]['task_name'];
            $taskList = array(
                'task_name' => $taskName,
                'task_status' => $dataFa[0]['task_name'],
                'task_type' => $dataFa[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 检测客户端是否作为恢复目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedRecoveryTarget($agentuuid){
        $sqlRe = "select task.task_name,task.task_type,task.task_status from cdp_vol_task vt,bd_task task 
                where vt.recovery_target_agent_uuid =? and vt.task_uuid = task.task_uuid";
        $dataRe = $this->dbSelect($sqlRe,array($agentuuid));
        $taskList = array();
        if(!empty($dataRe)){
            $isEnabled =  true;
            $taskList = array(
                'task_name' => $dataRe[0]['task_name'],
                'task_status' => $dataRe[0]['task_name'],
                'task_type' => $dataRe[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 获取数据源主机对应的备机信息
     * @param unknown $masteruuid
     */
    private  function getDataSourceStandbyInfo($masteruuid){
        $sql = "select standby_agent_uuid from cdp_vol_backup_agent where master_agent_uuid = ? and (storage_location =? or storage_location =?) ";
        $data = $this->dbSelect($sql,array($masteruuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],xphp_get_desc('Volcdp','DATA_SOURCE')['BACKUP_SERVER_OR_STANDBY']));
        $standbyAgentuuid = "";
        $standbyAgentArray = array();
        if (!empty($data)){
            foreach ($data as $v){
                $standbyAgentuuid = $v['standby_agent_uuid'];
                array_push($standbyAgentArray,$standbyAgentuuid);
                if(!$standbyAgentuuid){
                    break;
                }
            }
        }
        return $standbyAgentArray;
    }

    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    private function getHostVolInfoByUUID($hostUuid){
        $agentSql = "select agent_type from bd_agent where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($hostUuid));
        $agentType = 1;
        if(!empty($data)){
            $agentType = $data[0]['agent_type'];
        }

        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol
                where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ?";
        $data = $this->dbSelect($sql,array($hostUuid));
        $list = array();
        $HostMountPointArry = array();
        foreach ($data as $v){
            $detail = json_decode($v['detail']);
            $mountPoint = $v['mount_point'];
            $volName = $v['vol_name'];
            $volTypeValue = $detail->vol_type;
            $canSelect = true;

            $volTypeValue = $detail->vol_type;
            //保留分区、恢复分区、pv分区、扩展分区
            if($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_PV_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EXTEND_VOLUME']){
                continue;
            }
            //swap 分区
//            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SWAP_VOLUME']){
                continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName,"live-");
            if($pos !==false){
                continue;
            }
            if(($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_BOOT_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EFI_VOLUME']
                && $agentType!=xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS'])) {
                $canSelect = false;
            }

            // 普通分区，未挂载分区 自由勾选
            if(!empty($v['mount_point'])){
                array_push($HostMountPointArry,$v['mount_point']);
            }
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" =>v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "is_boot" => $v['is_boot'],
                "display_name" => $v['display_name'],
                "mount_point" => $v['mount_point'],
                "can_select" => $canSelect,
            );
        }
        $hostVolInfo = array(
            'mount_info' => $HostMountPointArry,
            'vol_info' => $list
        );
        return $hostVolInfo;
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

    /**
     * @param $params
     * @return string
     * 创建接管任务
     */
    public function createTakeoverJob($params){
        //public params
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $module_type = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];

        $time_strategy_list = array();
        $node = $params['node'];
        $transport_strategy = $params['transfer'];
        $pfMSg = array();
//         $recovery_position = "";
//         $recovery_time_type = 1;  //手动启动
//         $pfMSg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
//             $recovery_time_type, $time_strategy_list, $transport_strategy);
        $pfMSg['task_name'] = $task_name;
        $pfMSg['module_type'] = $module_type;
        $pfMSg['task_type'] = xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'];
        $pfMSg['master_agent_uuid'] = $params['master_agent_uuid'];
        $pfMSg['takeover_data_source'] = $params['takeover_data_source'];
        $pfMSg['takeover_timestamp'] = $params['takeover_timestamp'];
        $pfMSg['vol_uuid_set'] = $params['vol_uuid_set'];
        $pfMSg['takeover_object'] = $params['takeover_object'];
        $pfMSg['node_uuid'] = $node;
        $pfMSg['storage_uuid'] = $params['storage_uuid'];
        $pfMSg['takeover_standby_agent_uuid'] = $params['takeover_standby_agent_uuid'];
        /*
         * 下面7个参数为接管需要填充的默认参数,后台公共处理流程需要,后台接管模块暂不解析.
         */
        $pfMSg['recovery_position'] = 0;
        $pfMSg['time_strategy_list'] = array();
        $pfMSg['transport_strategy'] = $this->volcdpTransportStrategyMessage(0,0,0,0,$transport_strategy);

        $pfMSg['thread_num'] = 3;
        $pfMSg['speed_limit_strategy_list'] = array();
        $pfMSg['strategy_group_uuid'] = "";
        $pfMSg['rebuild_partition_flag'] = 0;

        $opName = 'BD_TASK_OP_TAKEOVER_CREATE';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbVolCdpMsg($node, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = (new PFOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 获取指定客户端选择卷对应的备份数据集
     */
    public function getBackupSetGrid($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $vol_uuid = $params['vol_uuid'];
        $agentuuid = $params['agentuuid'];
        $sortColumn = 0;
        $sortType = $params['sortType'];
        $volName = $params['vol_name'];
        $volHostIP = $params['host_ip'];
        $useruuid = xphp_get_user_info()['userUuid'];
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
        if($userLevel!=xphp_get_config('three_powers','THREE_POWERS_USER')['admin']){
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_config()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}'";
            }
        }

        $sqlParams = array($agentuuid,$vol_uuid,xphp_get_config('app','FLAG')['UNSET'],$volName,$taskuuid);
        if($nodeuuid){
            $sql .=" and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $accurateFlag = $params['accurateFlag'];
        $sqlCountParams = $sqlParams;

        $sqlParams = array_merge($sqlParams, array($start, $length));
        if($accurateFlag){
            $timepointType = intval($params['timepointType']);
            $starFlag = intval($params['starFlag']);
            $startTime = $params['startTime'];
            $endTime = $params['endTime'];

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
        $records = array();
        $records['rows'] = array();
        $i = 0;
        if(!empty($data)){
            foreach ($data as $d){
                $op = array(1, 2); //1:修改备注;2:删除备份集
                if($d['archive_flag'] == xphp_get_config('app','FLAG')['SET']){ //时间点在合并中，不让删除
                    $op = array(1);
                }
                $taskIsDelete = $this->getTaskIsDeleted($d['task_uuid']);
                $storageLocation = $d['storage_location'];

                if($storageLocation == xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']){
                    continue;
                }

                //根据测试bug要求，在存储数据为新建和初始同步节点不做显示
                if($d['storage_status'] == xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']
                    || $d['storage_status'] == xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_NEW']){
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
                    $taskInfo = $d['task_name']." (" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                }else{
                    $taskInfo = $d['task_name'];
                }
                $encryptedFlag = $d['encrypted_flag'];

                $encryptedStr = xphp_get_lang('UI_PUBLIC_OFF_TWO');
                if($encryptedFlag == xphp_get_config('app','FLAG')['SET']){
                    $encryptedStr = xphp_get_lang('UI_PUBLIC_ON_TWO');
                }
                $records["rows"][] = array(
                    'start_timepoint' => '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['start_timepoint']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                    'end_timepoint' => '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['end_timepoint']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                    'encryptedStr' => $encryptedStr,
                    'taskInfo' => $taskInfo,
                    'backup_file_size' => v1_calsize($d['backup_file_size'], true),
                    'log_file_total_size' => v1_calsize($d['log_file_total_size'], true),
                    'storageStatus' => $storageStatus,
                    'storage_info' => $this->getStorageName($d['storage_uuid']),
                    'remarks' => $d['remarks'],
                    'op' => $op,
                );
                $i++;
            }
        }
        $records["total"] = $i;
        return $records;
    }

    /**
     * 获取指定客户端对应的备份标签点信息
     */
    public function getBackupTagPointGrid($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $agentuuid = $params['agentuuid'];
        $nodeuuid = $params['nodeuuid'];
        $useruuid = xphp_get_user_info()['userUuid'];
        $sql = "select label.id,bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(label.label_timestamp) label_timepoint,bbt.data_local_flag,
                    bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.task_name,bbt.task_uuid,bbt.backup_mode,label.remarks,label.backup_agent_id
                from bd_backup_timepoint bbt,cdp_vol_backup_agent agent,cdp_vol_agent_label_set as label,bd_storage_resource bsr
                where bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and agent.master_agent_uuid =?  
                    and agent.id = label.backup_agent_id and bbt.deleted_flag = ? ";
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        if (!empty($authUser)) {  // 表示有管理的用户
            $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
        }else{
            $sql .= " and bbt.user_uuid = '{$useruuid}'";
        }
        if($nodeuuid!=""){
            $sql = $sql . " and bsr.node_uuid = ? ";
            $sqlParams = array($agentuuid,xphp_get_config('app','FLAG')['UNSET'],$nodeuuid);
            $countSqlParams = array($agentuuid,xphp_get_config('app','FLAG')['UNSET'],$nodeuuid);
        }else{
            $sqlParams = array($agentuuid,xphp_get_config('app','FLAG')['UNSET']);
            $countSqlParams = array($agentuuid,xphp_get_config('app','FLAG')['UNSET']);
        }
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array_merge($sqlParams, array($start, $length));
        if($accurateFlag){
            $starFlag = intval($params['starFlag']);
            $startTime = $params['startTime'];
            $endTime = $params['endTime'];

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
        $sql .= " limit ? , ? ";



        $data = $this->dbSelect($sql, $sqlParams);

        $records = array();
        $records['rows'] = array();
        $i = 0;
        if(!empty($data)){
            foreach ($data as $d){
                $op = array(1, 2); //1:修改备注;2:删除备份集
                if($d['archive_flag'] == xphp_get_config('app','FLAG')['SET']){ //时间点在合并中，不让删除
                    $op = array(1);
                }
                $remarks = $d['remarks'];
                $records['rows'][] = array(
                    'title' => '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d[' ']) . '</span>',  //隐藏展示时间点uuid出来,方便运维
                    'task_name' => $d['task_name'],
                    'storage_name' => $this->getStorageName($d['storage_uuid']),
                    'remarks' => $remarks,
                    'op' => $op,
                    'timepoint_uuid'=>$d['timepoint_uuid'],
                    'agentuuid'=>$agentuuid,
                    'node_uuid' =>$nodeuuid,
                    'remote_flag' => !v1_parse_flag_to_bool(intval($d['data_local_flag'])),
                    'task_uuid' =>$d['task_uuid'],
                    'time_point' =>$this->parseDate($d['label_timestamp']),
                    'lable_id' =>$d['id'],
                    'backup_agent_id' => $d['backup_agent_id'],
                    'label_timepoint' => $this->parseDate($d['label_timepoint'])
                );
                $i++;
            }
        }

        $records["total"] = $i;
        return $records;
    }

    /**
     * 获取客户端备份集信息,取最新时间点
     */
    public function getClientBackupSetInfo($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];

        $list = array();

        $firsTime = (new VolcdpRecover())->getClientFirstTime($params);
        $newTime = (new VolcdpRecover())->getClientNewTime($params);
        $sql = "SELECT bbt.timepoint_uuid,bbt.src_end_timepoint,agent.storage_location,st.storage_uuid,
                st.node_uuid,bbt.encrypted_flag,bbt.detail,agent.master_agent_detail 
            FROM cdp_vol_backup_agent as agent,bd_backup_timepoint as bbt,bd_storage_resource as st 
            WHERE st.storage_uuid = bbt.storage_uuid and  agent.timepoint_uuid = bbt.timepoint_uuid 
                and agent.master_agent_uuid = ? and bbt.user_uuid = ? and agent.storage_location !=? 
                and bbt.task_uuid = ? ";

        if($createTaskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
            $sql = $sql."   ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        }
        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timestamp = date('Y-m-d H:i:s',$srcEndTimeStamp);
        $timepointuuid = $data[0]['timepoint_uuid'];

        if($data[0]['encrypted_flag'] == xphp_get_config('app','FLAG')['SET']) { // 时间点加锁
            $encryptedFlag = true;
        }else {
            $encryptedFlag = false;
        }
        $detail = json_decode($data[0]['detail'],true);
        $passwordAutoFlag = intval($detail['password_auto_flag'])==xphp_get_config('app','FLAG')['SET'] ? true: false;
        if($newTime=="--"){
            $newTime = $timestamp;
        }else if($newTime<$timestamp && $data_source==0){
            $newTime = $timestamp;
        }

        $list[] = array(
            'new_timestamp' => $newTime,
            'newTime' =>$newTime,
            'timestamp'=>$timestamp,
            'storage_location' => $data[0]['storage_location'],
            'storage_uuid' => $data[0]['storage_uuid'],
            'node_uuid' => $data[0]['node_uuid'],
            'backup_set' => [],
            'timepoint_uuid' => $data[0]['timepoint_uuid'],
            'encrypted_flag' => $encryptedFlag,
            'password_auto_flag' => $passwordAutoFlag,
            'vol_set' => (new VolcdpRecover())->getClientVolSet($agent_uuid,$srcEndTimeStamp,$newTime,$createTaskType,$timepointuuid,$taskuuid),
            'time_range' => $firsTime." -- ".$newTime
        );
        return $list;
    }

    /**
     * 获取存储名字
     * @param string $storageuuid
     */
    private function getStorageName($storageuuid)
    {
        $sql = "select storage_nickname, node_uuid, storage_config, storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = $data[0]['storage_nickname'];
        $type = intval($data[0]['storage_type']);
        if($type == xphp_get_config('resource','BD_STORAGE_TYPE')['REMOTE']){
            $storageConfig = json_decode($data[0]['storage_config'], true);
            $nodename = $storageConfig['remote_ip'];
        }else{
            $nodename = (new Node())->getNodeName($data[0]['node_uuid']);
        }
        $name .= "\n" . "(" . $nodename . ")";
        return $name;
    }

    /**
     * 解析备份集当前状态，返回可执行操作
     * @param unknown $status
     */
    public function getBackupSetStorageStatus($status,$startTimestamp,$agentuuid,$storageLocation)
    {
        $verifyResult = "";
        switch ($status){
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_NEW']:  //新建状态
                $verifyResult =xphp_get_lang('UI_PUBLIC_ADD');
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']:  //初始同步
                $verifyResult = xphp_get_lang('UI_VOL_CDP_INIT_SYNC');
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_REALTIME_SYNC']:  //实时同步
                $verifyResult = xphp_get_lang('UI_VOL_CDP_TAKEOVER_RECOVERY');
                if($storageLocation== xphp_get_desc('Volcdp','RECOVERY_DATA_SOURCE')['STANDBY']){
                    $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER');
                }
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_IMAGE_MERGE']:  //镜像合并中
                $verifyResult =xphp_get_lang('UI_VOL_CDP_TAKEOVER_RECOVERY');
                if($storageLocation== xphp_get_desc('Volcdp','RECOVERY_DATA_SOURCE')['STANDBY']){
                    $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER');
                }
                break;
        }
        return $verifyResult;
    }

    /**
     * 获取恢复数据源主机信息
     */
    private function parseBackupAgentDetail($detail)
    {
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
     *  获取当前任务是否删除
     */
    private function getTaskIsDeleted($taskuuid)
    {
        $sql = "select * from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            return false;  //任务存在
        }else{
            return true;  //任务已删除
        }
    }

    /**
     * @param $params
     * @return mixed
     * 获取接管任务名
     */
    public function getVolCdpTakeoverTaskName($params){
        $DefaultTaskName =$params['task_type'];
        return array(
            'task_name' => $this->getValidTaskName($DefaultTaskName)
        );
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    private function getValidTaskName($taskName){
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
     * 传输策略消息
     * @param int $encrypt_flag
     * @param int $compress_flag
     * @param int $speed_limit_flag
     * @param int $max_speed
     * @return array $transportStrategyMessage
     */
    protected function volcdpTransportStrategyMessage($encrypt_flag, $compress_flag, $speed_limit_flag, $max_speed,$transport_strategy){
        $transportStrategyMessage = array(
            'encrypt_flag' => $encrypt_flag,
            'compress_flag' => $compress_flag,
            'speed_limit_flag' => $speed_limit_flag,
            'max_speed' => $max_speed,
            "network_uuid" =>$transport_strategy['network'],
            'strategy_group_uuid' => $transport_strategy['strategy_group_uuid']
        );
        return $transportStrategyMessage;
    }

}