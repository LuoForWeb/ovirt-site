<?php

namespace app\v1\complete_machine_os\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Recover;
use app\v1\recovery\v0\logic\InstantaneousData;
use app\v1\resources\v0\logic\Index;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\backupData\v0\logic\DataManage;
use xphp\BLLHandler as XphpBLLHandler;

/**
 * note          整机定时恢复
 * @author       liushuai@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class MachineOsRecover extends Recover
{
   /**
     * 获取时间点树
     * @param unknown $params node 节点uuid
     * @return string
     */
    public function getTimepointTree($params)
    {
        //-----------------------------------------------------------------
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>  xphp_get_lang('WEB_OS_GET_TIMEPOINT_TREE'),
            'data' => array(),
        );
        $storageuuid = $params['storageuuid'];
        $searchVal = $params['searchVal'];
        //来源
        // $recoverflag = $params['recoverflag']; //恢复的树形结构
        // $dataflag = $params['dataflag'];//备份数据的树形结构
       
       

        //获取所有时间点信息
        $sql = "select bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,
    			bbt.task_uuid, obt.os_name, obt.dir_path, bbt.real_node_uuid, bsr.node_uuid, obt.os_type,obt.agent_ip,obt.agent_uuid   
                from bd_backup_timepoint bbt
                left join os_backup_timepoint obt on bbt.timepoint_uuid = obt.timepoint_uuid
                left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid
                where bbt.available_flag = 1 and bbt.import_flag = 2 and 
                bbt.module_type in (" . xphp_get_config('module', 'MODULE_TYPE')['OS'] . ',' . xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT'] . ') and
                      bbt.sub_module_type = 1 and bbt.data_local_flag = ? ';
        $sqlParams = array(xphp_get_config('app', 'FLAG')['SET']);
        $flag = xphp_get_config('app', 'FLAG');
        if(!empty($storageuuid)){
            $sql .= ' and bbt.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageuuid));
        }
        if(!empty($searchVal)){
            $sql .= ' and (bbt.task_name like ? or obt.os_name like ? or obt.agent_ip like ?)';
            $sqlParams = array_merge($sqlParams, array('%' . $searchVal . '%', '%' . $searchVal . '%','%' . $searchVal . '%'));
        }
       
        if (!empty($storageuuid)) {
            $sql .= ' and bsr.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageuuid));
        }

        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源数据
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源数据
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['complete']
            );
            $sql .= " and bbt.user_uuid in ({$userUuidSql}) ";
        }

        // 排除云存储的数据
        if (!empty($params['notcloud'])) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['CLOUD']]);
        }

        // 排除磁带的数据
        if (!empty($params['nottape'])) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['TAPE']]);
        }

        $sql .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);


        //所有任务的集合
        $task = array();
        $info = array();
        $os = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $agentList = $this->getAllAgentList();
        foreach ($data as $op) {
            $taskuuid = $op['task_uuid'];
            $taskName = $op['task_name'];
            if (!in_array($taskuuid, $task)) {
                $task[] = $taskuuid;
                //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';
                // //如果是恢复的树  副本归档不显示删除 只显示副本数据和归档数据
                // if ($recoverflag) {
                //     // 副本数据
                if ($op['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] || $op['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY_FETCH']) {
                    $name .='(' . xphp_get_lang('UI_COPY_DATA') . ')';
                };
                // 归档数据
                if ($op['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] || $op['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']) {
                    $name .=  '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')';
                };
                // }


                //第一层 任务名称
                $info[] = array(
                    'id' => $op['task_uuid'],
                    'pId' => 0,
                    'name' => $name,
                    'open' => false,
                    'nocheck' => true,
                    'type' => 0,
                    'icon' => './img/platform/flag.png',
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . date('Y-m-d H:i:s', $op['task_create_time']),
                    'isParent' => true,
                );
            }

            if (!in_array($op['task_uuid'] . $op['agent_uuid'], $os)) {
                $os[] = $op['task_uuid'] . $op['agent_uuid'];
                //获取系统类型
                $ostype = $op['os_type'];
                $osTypeName = $this->getOSTypeStr($ostype);
                //第二层 任务系统类型
                $info[] = array(
                    'id' => $op['task_uuid'] . $op['agent_uuid'],
                    'pId' => $op['task_uuid'],
                    'name' => $this->getAgentNameByList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                    'open' => false,
                    'nocheck' => true,
                    'type' => 1,
                    'icon' => './img/os/' . $osTypeName . '.png',
                    'iconSkin' => $osTypeName . 'logo',
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_VM_SRC_DIR_PATH') . ': ' . $op['dir_path'],
                    'taskuuid' => $op['task_uuid'],
                    'agentuuid' => $op['agent_uuid'],
                    'nodeuuid' => $op['node_uuid'],
                    "createtime" => $this->parseDate($op['task_create_time']),
                    'osType' => $op['os_type'],
                    'isParent' => true,
                    'clickshow' => true
                );
            }
        }
        // 这里需要判断下，如果携带了 instant_flag = true则表示还需要返回瞬时恢复的时间点
        if (!empty($params['instant_flag'])) {
            $params['point_type'] = 'agent';
            $info1 = (new InstantaneousData())->instantanceTree($params);
            $info = array_merge($info, $info1);
            // 还要去重
            $info = v1_unique_by_two_keys_manual($info, 'id', 'pId');
        }

        $resultInfo['data'] = $info;
        return $resultInfo;
    }

     /**
     * 异步获取时间点数
     * @param unknown $params
     * taskuuid agentuuid nodeuuid  任务uuid 代理机uuid 节点uuid   3者确定一条数据
     * $recoverflag boolean
     * $dataflag  boolean
     */
    public function getSyncTimepoint($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_TIMEPOINT_ASYNC'),
            'data' => array(),
        );
        //----------------------------
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $storageuuid = $params['storageuuid'];
        $oschecked = $params['oschecked']; //父级是否被勾选上
        //来源
        $recoverflag = $params['recoverflag']; //恢复的树形结构
        $dataflag = $params['dataflag'];//备份数据的树形结构
        $instantflag = $params['instantflag'] ? true : false; //是否来自瞬时恢复
        $agentList = $this->getAgentuuidList(); //得到在任务中的一些代理机uuid


        //如果来自恢复则为true  来自备份数据为false
        $nocheckflag = $recoverflag && !$dataflag ? true : false;

        $sql = "select bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,bbt.backup_mode,
    			     bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.detail,bbt.importance_flag, bbt.remarks,bbt.archive_flag,bbt.deleted_flag, 
                     bbt.task_uuid, bbt.merge_status, bbt.operation_status,bbt.integrity_check_flag, 
                     obt.os_timepoint_id, obt.os_name, obt.dir_path, obt.os_config, 
                     bbt.real_node_uuid, bsr.storage_nickname, bsr.storage_type, bsr.node_uuid, obt.os_type,obt.agent_ip ,obt.agent_uuid , bsr.status,
                      bdtsi.virus_scan_status , bdtsi.integrity_check_status 
                from bd_backup_timepoint bbt
                left join os_backup_timepoint obt on bbt.timepoint_uuid = obt.timepoint_uuid
                left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid
                left join bd_backup_timepoint_safe_info bdtsi on bbt.timepoint_uuid = bdtsi.timepoint_uuid 
                where bbt.available_flag = 1 and
                      bbt.import_flag = 2 and   
                      bbt.module_type in (" . xphp_get_config('module', 'MODULE_TYPE')['OS'] . ',' . xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT'] . ') and
                      bbt.sub_module_type = 1 and
                      bbt.data_local_flag = ? and bbt.task_uuid = ? and 
                      obt.agent_uuid = ?';
        $sqlparams = array(xphp_get_config('app', 'FLAG')['SET'], $taskuuid, $agentuuid);
        if (!empty($storageuuid)) {
            $sql .= ' and bsr.storage_uuid = ? ';
            $sqlparams = array_merge($sqlparams, array($storageuuid));
        }
        //如果是瞬时恢复 需要屏蔽云存储的时间点
        if ($instantflag) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlparams = array_merge($sqlparams, array(xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']));
        }

        // 排除云存储的数据
        if (!empty($params['notcloud'])) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlparams = array_merge($sqlparams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['CLOUD']]);
        }

        // 排除磁带的数据
        if (!empty($params['nottape'])) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlparams = array_merge($sqlparams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['TAPE']]);
        }

        $sql .= " order by timepoint asc";
        $pointData = $this->dbSelect($sql, $sqlparams);

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($pointData as $point) {
            if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $unfullList[] = $point;
            }
        }
        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key1 => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $key => $value) {
                    if ($dependId == $key) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                        $unfullList = array_splice($unfullList, $key1, 1);
                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) {
                break;
            }
        }

        //判断是否可被勾选,先判断该代理主机是否存在恢复任务中,如果在任务中就看是否在运行状态  如果在就不让选中

        //默认为可勾选
        $chkDisabledflag = false;
        //         if($recoverflag){//如果是恢复
//             if(in_array($agentuuid, $agentList)){
//                 $chkDisabledflag = true;
//             }
//         }





        //得到所有数据后开始获取此机器备份的所有时间点
        $timepoint = array();
        $agentList = $this->getAllAgentList();

        $storageHandler = new Storage();
        $storageUuidList = array_column($pointData, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);
        $storageOfflineStatus = xphp_get_config('storage', 'STORAGE_STATUS')['OFFLINE'];
        foreach ($pointData as $op) {
             // 判断当前时间点所在存储是否离线，是则置灰节点
             $isOfflineStorage = 1;
             foreach ($storageStatusList as $key => $storageStatusInfo) {
                 if ($op['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                     $isOfflineStorage = $storageStatusInfo['storage_status'];
                     break;
                 }
             }
           

            //----判断是否时间点是否可用 true表示禁用
            $availableFlag = true;

            // //如果时间点正在合并且不可用
            // if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['SET'] || $op['status'] != xphp_get_config('storage', 'STORAGE_STATUS')['ONLINE']) {
            //     $availableFlag = false;
            // }


            $osTypeName = $this->getOSTypeStr($op['os_type']);

            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
            $details = json_decode($op['detail'], true);
            $name = $this->parseDate($op['timepoint']) . '(' . $this->getTimepointTypeDes($op['backup_mode']) . ')';

            //如果在合并中
            // if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
            //     $name .= xphp_get_lang('WEB_OS_MERGE');
            // } elseif ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['UNSET'] && $op['deleted_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
            //     $name .= '(' . xphp_get_lang('UI_PUBLIC_MERGE_ERROR') . ')';
            //     //如果是在恢复页面不可用
            //     if ($recoverflag) {
            //         $availableFlag = false;
            //     }
            // }
            
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name .= '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')';
                $availableFlag = false;
            }


            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
            if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                $name .= '<i class="viconfont vicon-a-Unlockjiesuo-011"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }


            //时间点是否可用
            //获取时间点是否可用
            $merge_status = $op['merge_status'];
            $operation_status = $op['operation_status'];
            $virus_scan_status = $op['virus_scan_status'];
            $integrity_check_status = $op['integrity_check_status'];
            $timepointAvailableFlag = $this->getTimePointStatus($merge_status, $operation_status, $virus_scan_status, $integrity_check_status);
            //时间点不可用, 状态1是操作中, 2是正常, 3是异常
            $status = $timepointAvailableFlag['status'];
            $status_des = $timepointAvailableFlag['status_des'];
            $icon_label = $timepointAvailableFlag['icon_label'];
            //获取时间点是否可用
            if($timepointAvailableFlag['avaliable_flag']){
                //时间点可用不做处理, 完整性效验没通过也是可用的
                if($status == 3){
                    //时间点是异常状态
                    $name .= $icon_label;
                }
            }else{
                
                if($status == 1){
                    //为1状态的时候时间点不可勾选
                    $availableFlag = false;
                    $name .= $icon_label;
                }elseif($status == 2){
                    //2为正常状态不处理
                }elseif($status == 3){
                    $availableFlag = false;
                    $name .= $icon_label;
                }
            }
            //是否是数据备份
            $mark = '';
            //添加GFS标识
            $mark .= $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($op['importance_flag']));

            //其父级是否被勾选
            if (!empty($oschecked)) {
                $oschecked = true;
            } else {
                $oschecked = false;
            }

            //获取real_node_uuid
            $recover_time_point = array();
            $recover_time_point[] = $op['timepoint_uuid'];
            $real_node_uuid = $op['real_node_uuid'];
            // 获取时间点所在存储可用的节点uuid
            $allStorageData = DataManage::instance()->getStorageInfoByTimepoints($recover_time_point);
            foreach ($allStorageData as $item) {
                if ($item['storage_online_flag']) {
                    $real_node_uuid = $item['node_uuid'];
                    break;
                }
            }
            //获取配置
            $os_config = json_decode($op['os_config'], true);
            $system_boot_type = intval($os_config['system_boot_type'] ?? '');
            if ($op['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                if (!in_array($op['timepoint_uuid'], $timepoint)) {
                    $timepoint[] = $op['timepoint_uuid'];
                    $info[] = array(
                        'agentuuid' => $agentuuid,
                        'checked' => $oschecked,
                        "createtime" => $this->parseDate($op['task_create_time']),
                        'osname' => $this->getAgentNameByList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),

                        'ostype' => $op['os_type'],
                        'os_type' => $op['os_type'] == 1 ? 'Windows' : 'Linux',
                        'icon' => './img/platform/timepoint-f.png',
                        'id' => $op['timepoint_uuid'],
                        'name' => $name . $mark,
                        'oldname' => $name,
                        'nodeuuid' => $op['node_uuid'],
                        'real_node_uuid' => $real_node_uuid,
                        'pId' => $op['task_uuid'] . $op['agent_uuid'],
                        'path' => $op['dir_path'],
                        'pointname' => $this->parseDate($op['task_create_time']),
                        'taskuuid' => $taskuuid,
                        'timepointuuid' => $op['timepoint_uuid'],
                        'timepoint_uuid' => $op['timepoint_uuid'],
                
                        'type' => 2,
                        'iconSkin' => $osTypeName . 'logo',
                        'chkDisabled' => !$availableFlag,
                        //是否是在任务中
                        'agentuuidInTask' => $chkDisabledflag,
                        'password_auto_flag' => intval($details['password_auto_flag']) == 1 ? true : false,
                        'encrypted_flag' => $encryptedflag,
                        'system_boot_type' => $system_boot_type,
                        //病毒检测状态
                        'virus_scan_status' => $op['virus_scan_status'],
                        //时间点描述
                        'timepoint_des' => $this->parseDate($op['timepoint']),
                        //备份是否开启完整性效验
                        'integrity_check_flag' => $op['integrity_check_flag'],
                        //获取时间点存储类型
                        'storage_type' => $op['storage_type'],
                        "point_status" => $timepointAvailableFlag['status'],
                        "available_flag" => $timepointAvailableFlag['available_flag'],
                    );
                    continue;
                }

            }


            //第四层 增备差异点
            $info[] = array(
                'agentuuid' => $agentuuid,
                'checked' => $oschecked,
                "createtime" => $this->parseDate($op['task_create_time']),
                'osname' => $this->getAgentNameByList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                'ostype' => $op['os_type'],
                'icon' => $this->getTimepointIcon($op['backup_mode']),
                'id' => $op['timepoint_uuid'],
                'name' => $name . $mark,
                'oldname' => $name,
                'nodeuuid' => $op['node_uuid'],
                'real_node_uuid' => $real_node_uuid,
                'pId' => $fulluuidList[$op['timepoint_uuid']],
                'path' => $op['dir_path'],
                'pointname' => $this->parseDate($op['task_create_time']),
                'taskuuid' => $taskuuid,
                'timepointuuid' => $op['timepoint_uuid'],
                'timepoint_uuid' => $op['timepoint_uuid'],
                'type' => 3,
                //                 "nocheck" => !$nocheckflag,
                'nocheck' => false,
                'iconSkin' => $osTypeName . 'logo',
                'chkDisabled' =>!$availableFlag,
                //是否是在任务中
                'agentuuidInTask' => $chkDisabledflag,
                'password_auto_flag' => intval($details['password_auto_flag']) == 1 ? true : false,
                'encrypted_flag' => $encryptedflag,
                 //病毒检测状态
                 'virus_scan_status' => $op['virus_scan_status'],
                 'system_boot_type' => $system_boot_type,
                 'timepoint_des' => $this->parseDate($op['timepoint']),
                 //备份是否开启完整性效验
                 'integrity_check_flag' => $op['integrity_check_flag'],
                 //获取时间点存储类型
                 'storage_type' => $op['storage_type'],
                 "point_status" => $timepointAvailableFlag['status'],
                 "available_flag" => $timepointAvailableFlag['available_flag'],
            );
        }
        // 这里需要判断下，如果携带了 instant_flag = true则表示还需要返回瞬时恢复的时间点
        if (!empty($params['instant_flag'])) {
            $params['point_type'] = 'agent';
            $info1 = (new InstantaneousData())->instantancePoints($params);
            $info = array_merge($info ?? [], $info1);
            // 还要去重
            $info = v1_unique_by_two_keys_manual($info, 'id', 'pId');
        }

        $resultInfo['data'] = $info;
        return $resultInfo;
    }

     /**
     * 得到所有IP地址用于组装option
      * @param array $params 请求参数
     * @return  array
     */
    public function getOptionIP($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_MACHINE_OS_GET_HOST_LIST_SUCCESS'),
            'data' => array(),
        );
        //先获取该用户分配了哪些主机
        //获取该用户拥有的所有agent_uuid集合
        $sql = "select agent_name, hostname, agent_uuid, os_type, ip, online_flag,
                        authorization_module,agent_type, net_model
                    from bd_agent where agent_type != 3";


        if (v1_auth_need_operation()) {
            // 不是全局观察者-查看并操作并且也不是admin，那么只能查看自身拥有的和关联用户的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'],'agent_uuid');
            $sql .= " and ({$resourceUuidSql}) ";
        }


        $result = $this->dbSelect($sql);
        $info = array();
        //获取在任务中的主机
        $InTaskAgentList = $this->getTaskAgent();
        foreach ($result as $op) {
            
        

            
            //判断该主机是否在任务中
            //获取在任务中的任务名字
            $task_uuid = "";
            $task_name = "";
            $InTask = false;
            foreach ($InTaskAgentList as $eachAgent) {
                if($eachAgent['agent_uuid'] == $op['agent_uuid']){
                    $task_uuid = $eachAgent['task_uuid'];
                    $task_name = $eachAgent['task_name'];
                    if($eachAgent['task_status'] == 4 && $eachAgent['task_type'] == 35){
                        //备份且停止状态则跳过
                        $InTask = false;
                        break;
                    }
                    $InTask = true;
                    break;
                }
            }


            //处理名字
            $titleDes = $this->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            if ($op['online_flag'] != 1) {
                $titleDes .= xphp_get_lang('WEB_OS_OFFLINE');
            }

            $info[] = array(
                'os_type' => $op['os_type'],
                'agent_name' => $titleDes,
                'agent_uuid' => $op['agent_uuid'],
                'agent_type' => $op['agent_type'],
                'online_flag' => $op['online_flag'] == 1 ? true : false,
                'in_task' => $InTask,
                'task_name' => $task_name,
                'task_uuid' => $task_uuid,
                'ip' => $op['ip'],
                'net_model' => $op['net_model'],
                // 'type' => 2,//1为授权有主机的机子
            );
            // }
        }
        $resultInfo['data'] = $info;
        return $resultInfo;
    }


    /**
     * 获取操作系统或整机定时在任务中的主机的集合
     */
    public function getTaskAgent(){
        $info = array();
        $sql = "select DISTINCT(ol.agent_uuid),bt.task_type, bt.task_name,bt.task_status, bt.task_uuid from os_list ol left join bd_task bt on ol.task_uuid = bt.task_uuid";
        $result = $this->dbSelect($sql);
        foreach ($result as $op) {
            $info[] = array(
                'agent_uuid' => $op['agent_uuid'],
                'task_name' => $op['task_name'],
                'task_uuid' => $op['task_uuid'],
                'task_status' => $op['task_status'],
                'task_type' => $op['task_type'],
            );
        }
        return $info;
    }





    /**
     * 得到主机任务名(恢复)
     * @param unknown $params
     */
    public function getMachineOsTaskName()
    {
        //返回数据
        $resultInfo = array(
           'success' => true,
           'code' => 0,
           'message' => xphp_get_lang('WEB_MACHINE_OS_GET_RECOVERY_JOB_NAME'),
           'data' => array(),
        );
        $taskName = xphp_get_lang('WEB_MACHINE_OS_RESTORE_JOB_NAME');
        $backupHandler = new MachineOsBackup();
        $resultInfo['data'] = $backupHandler->getValidTaskName($taskName);
        return $resultInfo;
    }

    /**
     * 创建恢复任务
     */
    public function createRecoverJob($params)
    {
        //组合通用策略---------------------------
        $backupCommonHanlder = new Backup();
        $bllhandler = new XphpBLLHandler;
        $pfMsg = array();
        //组合时间策略
        $timestrategylist = $this->groupRecoverTimeList($params['type_info']);
        //组合传输策略
        $task_name = htmlspecialchars_decode($params['task_name']);
        $module_type = xphp_get_config('module','MODULE_TYPE')['OS'];
        $recoverytimetype = 0;
        $recovery_position = 2;
        $transportstrategy =  $backupCommonHanlder->groupTransportStrategy($params['transfer_strategy']);
        $pfMsg = $bllhandler->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recoverytimetype,
            $timestrategylist,
            $transportstrategy
        );

        //获取任务名称
        // $pfMsg['task_name'] = htmlspecialchars_decode($params['task_name']);
        //获取模块类型
        // $pfMsg['module_type'] = xphp_get_config('module','MODULE_TYPE')['OS'];
        //获取任务类型
        $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['OS_RECOVERY'];
        //获取时间点密码解码之后的recovery_oss_info
        foreach($params['recovery_oss_info'] as &$item){
            $item['recovery_timepoint_pwd'] = base64_decode($item['recovery_timepoint_pwd']);
        }
        unset($item);
        //获取是那种备份类型 1为整机的  2为以前卷的
        // $pfMsg['sub_module_type'] = 1;
        //是否是原机恢复(没用)
        // $pfMsg['recovery_position'] = '';
        // $pfMsg['time_strategy_list'] = "";
        $pfMsg['node_uuid'] = $params['node_uuid'];
        //获取时间点数组
        $recover_time_point = array();
        foreach ($params['recovery_oss_info'] as $item) {
            $recover_time_point[] = $item['recovery_timepoint_uuid'];
        }
        $nodeuuid = $params['node_uuid'];
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints($recover_time_point);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }

        $pfMsg['transport_ip_segment'] = "";
        // $pfMsg['recovery_type'] = "";
       
        //组合限速策略
        $pfMsg['speed_limit_strategy'] = $backupCommonHanlder->groupTaskSpeedGlobalList($params['strategyInfo']['speedlimit']);
        //组合传输策略-----------------------------
        // $pfMsg['transport_strategy'] = $backupCommonHanlder->groupTransportStrategy($params['transfer_strategy']);
        //组合安全策略-----------------------------
        $backupHandler = new Backup();
        $pfMsg['safe_config_strategy'] = $backupHandler->groupSafeConfigStrategy($params['safe_config_strategy'],$params['node_info']['storage_uuid']);
        //组合备份源信息-----------------------------
        $pfMsg['recovery_oss_info'] = $params['recovery_oss_info'];
        //组合重试策略
        $pfMsg['retry_strategy'] = $params['retry_strategy'];
        //其他
        //组合传输线程
        $pfMsg['thread_num'] = $params['thread_num'];
        $pfMsg['machine_flag'] = $params['machine_flag'];
        //获取策略组uuid
        $pfMsg['strategy_group_uuid'] = '';
        $msg = json_encode($pfMsg);
        //发送消息
        //得到备份创建操作码
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, false, false, $submodule_type);
        $result = $mbResult['result'];
        $pfOpcode  = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //恢复默认是立即启动
            //如果是立即恢复,需要创建完成后启动任务
            if($params['type_info']['type'] == xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY']) {
                $this->startRecoverJob($params['task_name']);
            }
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName){
        $sql = "select task_uuid from bd_task where task_name = ? order by id desc";
        $data = $this->dbSelect($sql, array($taskName));
        if(!$data) return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'task_uuid' => $data[0]['task_uuid'],
            'backup_mode' => 1,
            'time_strategy_id' => 0,
            'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
        );
        //调用系统统一启动任务接口.不重新写
        $nodeHandler = new Node;
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($data[0]['task_uuid']);   //任务UUID对应的存储节点uuid
        $msg = json_encode($params);
        $sync = FALSE;
        $command = TRUE;
        $opName = 'BD_TASK_OP_RECOVERY_START';
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command,$submodule_type);
        $result = $mbResult['result'];

        //这里直接返回成功或失败 bool
        return $result;
    }

   

/**
     * 根据agent_uuid 查询出名称
     * @param unknown $list
     * @param unknown $agent_uuid
     * @return name
     */
    public function getNameByAgentList($list, $agentuuid, $osname, $osagentip)
    {
        $name = '--';
        //如果没获取到主机则直接返回空
        if (empty($list)) {
            if (empty($osname)) {
                return $name;
            } else {
                return $osname;
            }
        }
        //开始循环获取主机
        foreach ($list as $each) {
            if ($each['agent_uuid'] == $agentuuid) {
                if (empty($each['hostname']) && !empty($each['agent_name'])) {
                    return $each['agent_name'];
                } elseif (!empty($hostname) && empty($agentname)) {
                    return $each['hostname'];
                } else {
                    if ($each['agent_name'] == $each['ip']) {
                        return $each['hostname'];
                    } else {
                        return $each['agent_name'];
                    }
                }
            }
        }
        return $osname;
    }

 /**
     * 得到备份时间点类型描述
     * @param unknown $backup_mode
     * return
     */
    public function getTimepointTypeDes($backupmode)
    {
       
        $des = '';
        $des = xphp_get_desc('Pf','BACKUP_MODE_DES')[$backupmode];
        $des .= xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
        return $des;
    }

 /**
     * 得到所有在任务中的agentuuid集合 ,此主机在任务中并且是为在运行状态的
     *
     */
    public function getAgentuuidList()
    {
        $info = array();
        $sql = "select ol.agent_uuid from os_list ol, bd_task bt where ol.task_uuid = bt.task_uuid and ((bt.task_type = ? and bt.task_status = ?) or bt.task_type = ?)";
        $result = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'], xphp_get_config('task', 'TASKSTATUS')['RUNNING'], xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY']));
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $info[] = $each['agent_uuid'];
        }
        ;
        return $info;
    }

    /**
     * 获得所有agent的name和ip信息
     */
    public function getAllAgentList()
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $each) {
            $info[] = array(
                'agent_uuid' => $each['agent_uuid'],
                'agent_name' => $each['agent_name'],
                'hostname' => $each['hostname'],
                'ip' => $each['ip'],
            );
        }
        return $info;
    }

    /**
     * 获取主机类型
     * @param unknown $os_type 主机类型
     *                         return 主机文字描述
     */
    public function getOSTypeStr($ostype)
    {
        $typeStr = '';
        switch ($ostype) {
            case 1:
                $typeStr = 'Windows';
                break;
            case 2:
                $typeStr = 'Linux';
                break;
            default:
                $typeStr = 'unknown';
                break;
        }
        ;
        return $typeStr;
    }

    /**
     * 根据agent_uuid 查询出agent的信息并拼接成名字
     * @param unknown $list
     * @param unknown $agent_uuid
     * @return name(ip)
     */
    public function getAgentNameByList($list, $agentuuid, $osname, $osagentip)
    {
        $name = '--';
        //如果没获取到主机则直接返回空
        if (empty($list)) {
            if (empty($osname)) {
                return $name;
            } else {
                return $this->getAgentName($osname, '', $osagentip);
            }
        }
        //开始循环获取主机
        foreach ($list as $each) {
            if ($each['agent_uuid'] == $agentuuid) {
                return $this->getAgentName($each['hostname'], $each['agent_name'], $each['ip']);
            }
        }
        return $this->getAgentName($osname, '', $osagentip);
    }

     /**
     * 如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * @param unknown $host_name  主机名
     * @param unknown $agent_name 别名
     * @return name(ip)
     */
    public function getAgentName($hostname, $agentname, $ip)
    {
        $nameip = '(' . $ip . ')';
        if (empty($hostname) && !empty($agentname)) {
            return $agentname . $nameip;
        } elseif (!empty($hostname) && empty($agentname)) {
            return $hostname . $nameip;
        } else {
            if ($agentname == $ip) {
                return $hostname . $nameip;
            } else {
                return $agentname . $nameip;
            }
        }
    }


    public function getNetworkofAgent($params){
         //返回数据
         $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_MACHINE_OS_GET_PROXY_HOST_NETWORK_INFO'),
            'data' => $this->getnetworkByAgent($params),
         );
         return $resultInfo;

    }
















}
