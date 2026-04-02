<?php

namespace app\v1\hadoop\v0\logic;

use app\v1\common\logic\Base;
use xphp\helper\Utils;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;
use app\v1\common\logic\Recover;
use app\v1\hadoop\v0\logic\HadoopBackUp;
use app\v1\common\logic\Backup;
use app\v1\common\logic\JobInfo as JobInfos;
use app\v1\hadoop\v0\logic\HadoopJobController;
use xphp\BLLHandler;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\FsOpcode;
use app\v1\resources\v0\logic\Storage;
use app\v1\backupData\v0\logic\DataManage;
/**
 * note          Hadoop 集群管理
 * @author       lilingyu@vinchin.com
 * @date         2023/9/14 13:23
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopRecovery extends Recover
{
    /**
     * 获取所有集群列表
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function getClusterList($params)
    {
        $sql = "select hadoop_cluster_uuid, hadoop_cluster_name from hadoop_cluster";
        $data = $this->dbSelect($sql);
        $clusterlist = array();
        //添加上所有集群
        $clusterlist[] = array(
            'cluster_name' => xphp_get_lang('WEB_HADOOP_ALL_CLUSTER'),
            'cluster_uuid' => ""
        );
        foreach ($data as $d) {
            $clusterlist[] = array(
                'cluster_name' => $d['hadoop_cluster_name'],
                'cluster_uuid' => $d['hadoop_cluster_uuid']
            );
        }
        return $clusterlist;
    }

    /**
     * 获取集群列表树
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function getClusterZtree($params)
    {
        $dataflag = $params['data_flag'];
        // $clusteruuid = $params['cluster_id'];
        $storageUuid = $params['storage_uuid'];                    //存储UUID
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE');
        $sql = "select bsr.node_uuid, bbt.module_type, bbt.timepoint_uuid, bbt.task_uuid, bbt.task_name, unix_timestamp(bbt.timepoint) timepoint,  bbt.user_uuid, bbt.user_name, bbt.task_type, 
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip 
        from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
        where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        bbt.storage_uuid = bsr.storage_uuid and 
        bbt.deleted_flag = ? and bbt.available_flag = ? 
        and bbt.import_flag = ? and 
        bbt.module_type = ? and  
        bbt.sub_module_type = ? and
        bbt.data_local_flag = ? and bbt.user_uuid is not null and bbt.user_uuid != '' and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']}) ";
        $flag = xphp_get_config('app', 'FLAG');
        $user = xphp_get_user_info();
        $userUUID = $user['userUuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $moduleType['FS'], $submoduleType['HADOOP'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = new Index();
        $tenantMangerFlag = (new User())->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($user['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的资源数据
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源数据
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['hadoop']
            );
            $sql .= " and bbt.user_uuid in ({$userUuidSql}) ";
        }
        
        if ($dataflag) {
            $sql .= ' and bbt.copy_flag = ? and bbt.module_type = ? ';
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], $moduleType['FS']));
        }

        if (!empty($storageUuid)) {
            $sql .= ' and bbt.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        $sql .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);

        //定义agent task 数组
        $node = $agent = $task = [];
        if (empty($data)) {
            return $node;
        }
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $hadoopBackup = new HadoopBackUp();
        foreach ($data as $d) {
            if ($d['module_type'] == $moduleType['BACKUP_COPY_CLIENT'] && $dataflag) {
                continue;
            }
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];

            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                if (
                    in_array($d['task_type'], [$taskType['BACKUP_COPY'], $taskType['BACKUP_COPY_FETCH']])
                ) {
                    $name = $taskName . '(' . xphp_get_lang('UI_COPY_DATA') . ')';
                } elseif (in_array($d['task_type'], [$taskType['ARCHIVE'], $taskType['ARCHIVE_FETCH']])) {
                    $name = $taskName . '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')';
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != $userUUID) {
                    $name .= '(' . $d['user_name'] . ')';
                }
                $node[] = array(
                    'id' => $d['task_uuid'],
                    'pId' => -1,
                    'name' => $name,
                    'title' => $name,
                    'open' => true,
                    'nocheck' => !$dataflag,
                    'type' => 1,
                    'icon' => './img/platform/flag.png',
                    'nodeuuid' => $d['node_uuid'],
                    'taskuuid' => $d['task_uuid'],
                    'isParent' => true,
                    'clusteruuid' => $d['agent_uuid']
                );
            }
            //检查并添加cluster
            if (!in_array($d['agent_uuid'] . '_' . $d['task_uuid'], $agent)) {
                $agent[] = $d['agent_uuid'] . '_' . $d['task_uuid'];
                //这里还需要选择 一个在线的节点进行显示 如果节点都不在线 则随便选一个节点进行展示
                $node[] = array(
                    'id' => $d['agent_uuid'] . '_' . $d['task_uuid'],
                    'pId' => $d['task_uuid'],
                    'name' => $d['agent_name'] . "(" . $d['agent_ip'] . ")",
                    'title' => $d['agent_ip'],
                    'nocheck' => !$dataflag,
                    'type' => 2,
                    'clickshow' => true,
                    'icon' => './img/hadoop/hadoop.png',
                    'nodeuuid' => $d['node_uuid'],
                    'taskuuid' => $d['task_uuid'],
                    'clusteruuid' => $d['agent_uuid'],
                    'isParent' => true,
                );
            }
        }

        return $node;



    }

    /**
     * 获取时间点树
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function getTimepointZtree($params)
    {
        $recoverflag = $params['recoverflag'] ?? false; //hadoop恢复加载时间点
        $taskuuid = $params['taskuuid'];
        $clusteruuid = $params['clusteruuid'];
        $storageuuid = $params['storageuuid'];
        $id = $params['id'];
        $dataflag = $params['dataflag'] ?? false;//备份数据的树形结构
        $chkDisabled = false;
        $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE');
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $sql = "select bsr.storage_nickname, bsr.node_uuid, bsr.storage_type, bsr.storage_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid,
            unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,
            bbt.encrypted_flag,bbt.remarks,bbt.detail,bbt.src_data_deleted_flag,bbt.integrity_check_flag,bbt.operation_status,bbt.merge_status,
            fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, bbt.task_name,fbt.detail as fbtdetail ,
            bsi.virus_scan_status,bsi.integrity_check_status
        from bd_backup_timepoint bbt
        left join fs_backup_timepoint fbt on bbt.timepoint_uuid = fbt.fs_timepoint_uuid 
        left join bd_storage_resource bsr on  bbt.storage_uuid = bsr.storage_uuid
        left join bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid  = bsi.timepoint_uuid 
        where 
            bbt.deleted_flag = ? and bbt.available_flag = ? and 
            bbt.import_flag = ? and 
            bbt.module_type = ? and bbt.sub_module_type = ? and
            bbt.task_uuid = ? and fbt.agent_uuid = ? and bbt.data_local_flag = ? ";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $moduleTypeArr['FS'], $submoduleType['HADOOP'], $taskuuid, $clusteruuid, $flag['SET']);
        if(!empty($storageuuid)){
             //如果是选择了某个存储,显示这个存储下面的
             $sql .= " and bsr.storage_uuid = ? ";
             $sqlParams = array_merge($sqlParams, array($storageuuid));
        }
        $sql .= " order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint";
        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');
        foreach ($data as $point) {
            if ($point['backup_mode'] == $backupMode['FULL']) {
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
                        unset($unfullList[$key]);
                        $unfullList = array_values($unfullList);
                        $unfullCountTmp--;
                    }
                }
            }
            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) {
                break;
            }
        }
        $storageHandler = new Storage();
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);

        $node = array();
        $jobs = new JobInfos();
        $storageOfflineStatus = xphp_get_config('storage', 'STORAGE_STATUS')['OFFLINE'];
        foreach ($data as $d) {
            // 判断当前时间点所在存储是否离线，是则置灰节点
            $isOfflineStorage = 1;
            foreach ($storageStatusList as $key => $storageStatusInfo) {
                if ($d['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                    $isOfflineStorage = $storageStatusInfo['storage_status'];
                    break;
                }
            }
            $pointMixedStatus = $this->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
            $fbtdetail = json_decode($d['fbtdetail'], true);
            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) . ' (' . $jobs->getTimepointTypeDes($d['backup_mode']) . ')';
            $title = $name;
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')' . '</span>';
            }
            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }
            // $mark = '';
            // if ($dataflag) {
            //     //添加GFS标识
            //     $mark .= $jobs->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            //     $gfsforever = $mark;
            //     $chkDisabled = true;
            // }
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == $backupMode['FULL']) {
                $node[] = array(
                    'id' => $d['timepoint_uuid'],
                    'pId' => $id,
                    'name' => $name,
                    'title' => $title,
                    'checked' => false,
                    'type' => 3,
                    'nocheck' => false,
                    'oldname' => $name,
                    'gfsforever' => $gfsforever ?? '',
                    'point_uuid' => $d['timepoint_uuid'],
                    'depend_uuid' => $d['depend_point_uuid'],
                    'cluster_name' => $d['agent_name'],
                    'cluster_ip' => $d['agent_ip'],
                    'cluster_uuid' => $d['agent_uuid'],
                    'task_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'icon' => $jobs->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'timepointuuid' => $d['timepoint_uuid'],
                    'taskuuid' => $taskuuid,
                    'nodeuuid' => $d['node_uuid'],
                    'storagename' => $d['storage_nickname'],
                    'storagetype' => $d['storage_type'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'encrypted_flag' => $encryptedflag,
                    'os_type' => $fbtdetail['os_type'],
                    'password_auto_flag' => intval($detail['password_auto_flag']) == 1,
                    'src_data_deleted_flag' => intval($d['src_data_deleted_flag']) == 1,
                    'chkDisabled' => $isOfflineStorage == $storageOfflineStatus,
                    'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                    "point_status" => $pointMixedStatus['status'],
                    "available_flag" => $pointMixedStatus['available_flag'],
                );
                continue;
            }
            $node[] = array(
                'id' => $d['timepoint_uuid'],
                'pId' => $fulluuidList[$timepointuuid],
                'name' => $name,
                'title' => $title,
                'checked' => false,
                'type' => 4,
                'oldname' => $name,
                'nocheck' => !$recoverflag,
                'point_uuid' => $d['timepoint_uuid'],
                'depend_uuid' => $d['depend_point_uuid'],
                'cluster_name' => $d['agent_name'],
                'cluster_ip' => $d['agent_ip'],
                'cluster_uuid' => $d['agent_uuid'],
                'task_name' => $d['task_name'],
                'star' => intval($d['importance_flag']) == $flag['SET'],
                'icon' => $jobs->getTimepointIcon($d['backup_mode']),
                'mode' => intval($d['backup_mode']),
                'timepointuuid' => $d['timepoint_uuid'],
                'taskuuid' => $taskuuid,
                'nodeuuid' => $d['node_uuid'],
                'chkDisabled' =>  $isOfflineStorage == $storageOfflineStatus,
                'storagename' => $d['storage_nickname'],
                'storagetype' => $d['storage_type'],
                'timepoint' => $this->parseDate($d['timepoint']),
                'encrypted_flag' => $encryptedflag,
                'os_type' => $fbtdetail['os_type'],
                'password_auto_flag' => intval($detail['password_auto_flag']) == 1,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1,
                'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
            );
        }
        return $node;

    }

    /**
     * 获取备份的的文件列表
     * @param string $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function getFileZtree($params)
    {
        // $params =  $params['info'];
        $paramsobj = json_decode($params['info']);
        $params = $this->object_to_array($paramsobj);
        $info = array(
            "result" => true,
            "data" => array(),
            "error_message" => "",
            "errorCode" => 0,
        );
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = intval($params['root_flag']);
        $start = $params['start'];
        $number = intval($params['number']);
        $path = $params['path'];
        $md5Flag = intval($params['md5_flag']);
        $md5High = $params['md5_high'];
        $md5Low = $params['md5_low'];
        $pid = $params['pid'] ?? 0;
        $sclass = $params['sclass'];
        $isSearch = $params['isSearch'];
        $this->paramsCheck($timepointUUID);
        $pfMsg = array(
            'timepoint_uuid' => $timepointUUID,
            'root_flag' => $rootFlag,
            'start' => $start,
            'number' => $number,
            'path' => $path,
            'md5_flag' => $md5Flag,
            'md5_high' => $md5High,
            'md5_low' => $md5Low
        );
        //获取节点
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($timepointUUID);
        $mbResult = $this->service()->getFileZtree($nodeuuid, $pfMsg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if(!$result){
            $opName =  'BD_BACKUP_POINT_OP_SCAN';
            $operate = (new PfOpcode())->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg,'', $mbResult['errorCode']);
        }
        $info['result'] = $mbResult['result'];
        // $info['errorCode'] = $mbResult['errorCode'];
        $info['data']['finishFlag'] = $mbResult['msg']['finish_flag'];  //文件是否列完成   1完成,2未完成
        $info['data']['nextStart'] = $mbResult['msg']['next_start'];  //未完成时,下一个开始位置
        $info['data']['path'] = $mbResult['msg']['path'];       //当前路径
        $info['data']['timepointUUID'] = $mbResult['msg']['timepoint_uuid'];       //当前路径       
        $fileList = array();
        foreach ($mbResult['msg']['item_list'] as $d) {
            $filename = $d['filename'];
            // $filename = $this->getFileName($d['type'],$d['path']);
            $fileList[] = array(
                'id' => $d['path'],
                'pId' => $pid,
                'name' => $filename,
                'nocheck' => false,
                'title' => $filename,
                'path' => $d['path'],
                'btype' => $d['type'],   //文件类型
                'isParent' => $d['type'] == 2,
                'icon' => $d['type'] == 2 ? './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                'iconOpen' => $d['type'] == 2 ? './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                'iconClose' => $d['type'] == 2 ? './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                'sclass' => $this->getFileClassName($d['type'], $filename, $sclass), //显示类型
                'md5Flag' => $d['md5_flag'],
                'md5High' => $d['md5_high'],
                'md5Low' => $d['md5_low'],
                "createTime" => $d['create_time'],
                'modifyTime' => $d['modify_time'],
                'pointuuid' => $params['timepoint_uuid'],
                'more' => false,
                'searchNode' => $isSearch
            );
        }
        $info['data']['filelist'] = $fileList;
        if (intval($mbResult['msg']['finish_flag']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => $mbResult['msg']['path'] . '/more',
                'pId' => $pid,
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'nocheck' => true,
                'more' => true,
                'next_index' => $mbResult['msg']['next_start'],       //从哪个位置开始加载
                'pointuuid' => $params['timepoint_uuid'],
                'sclass' => $sclass,                //显示类型
                'md5Flag' => $md5Flag,
                'md5High' => $md5High,
                'md5Low' => $md5Low,                             //当前目录名

            );
            array_push($info['data']['filelist'], $more);
            // $list['filelist'] = $more;
        }
        return $info;



    }

    /**
     * 创建搜索消息
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function createSearchJob($params)
    {
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);
        $data = [
            'search_mode' => $params['search_mode'],
            'md5_list' => $params['md5_list'],
            'path_name' => $params['path_name'],
            'timepoint_uuid' => $params['timepoint_uuid'],
            'req_total_num' => $params['req_total_num'],
            'module_type' => $params['module_type'],
        ];
        $mbResult = $this->service()->createSearchJob($nodeuuid, $data);
        $operate = xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE');
        if ($mbResult['result']) {
            return $mbResult;
        } else {
            //失败
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 停止搜索消息
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function stopSearchJob($params)
    {
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['search_timepoint']);
        $mbResult = $this->service()->stopSearchJob($nodeuuid, $params['info']);
        return $mbResult;
    }


    /**
     * 得到恢复搜索的结果
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/11/13
     * @return unknown
     */
    public function getSearchInfo($params)
    {
        $operate = xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_RESULT_GET');
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['search_timepoint']);
        // $data = [
        //     'task_uuid'  => $params['thread_uuid'],
        //     'number'  => $params['limit'],
        //     'offset'  => $params['offset'],
        // ];
        $mbResult = $this->service()->getSearchInfo($nodeuuid, $params['info']);
        $result = $mbResult['result'];
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $result = $mbResult['msg'];
        $filenode = array();
        // var_dump("msg---",$mbResult);
        foreach ($result['item_list'] as $file) {
            if ($file['file_type'] == 1) {
                // 文件
                $titleDes = xphp_get_lang('WEB_FILE_FILE_SIZE') . v1_calsize($file['file_size'], true)
                    . PHP_EOL . xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($file['modify_time'] / 1000);
            } else {
                $titleDes = xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($file['modify_time'] / 1000);
            }
            $filenode[] = array(
                'pid' => 0,
                'id' => $file['path_name'],
                'name' => $file['path_name'],
                'title' => $titleDes,
                'file_type' => $file['file_type'],
                'md5High' => $file['md5_high'],
                'md5Low' => $file['md5_low'],
                'file_size' => $file['file_size'],
                'modify_time' => $file['modify_time'],
                'file_offset' => $file['offset'],
                'icon' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                'iconOpen' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                'iconClose' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                'isParent' => $file['file_type'] != 1, //1文件  2文件夹
                'path' => $file['path_name'],
                'pointuuid' => $params['search_timepoint'],
                'md5Flag' => '1',
                'searchNode' => true,
            );
        }
        $mbResult['data'] = array(
            "search_finish_flag" => $result['finish_flag'],
            "current_total_num" => $result['number'],
            "current_dir_num" => $result['dir_num'],
            "current_file_num" => $result['file_num'],
            "all_file_num" => $result['all_file_num'],
            "all_dir_num" => $result['all_dir_num'],
            "offset" => $result['offset'],
            "filenode" => $filenode,
        );
        return $mbResult;
    }


    /**
     * 获取恢复任务名
     */
    public function getRecoveryTaskName()
    {
        return $this->getValidTaskName(xphp_get_lang('WEB_HADOOP_RECOVERY_TASK_NAME'));
    }

    /**
     * 获取可用的任务名
     * @param string $taskName 任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName)
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            if (empty($data)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }


    /**
     * 创建恢复任务
     */
    public function createRecoverJob($params)
    {
        $taskname = htmlspecialchars_decode($params['job_name']);
        $moduletypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        if( $params['recoveryType'] == 3){
            $moduletype = $moduletypeArr['NAS'];
        }else{
            $moduletype = $moduletypeArr['FS'];
        }
        switch ($params['recoveryType']) {
            case 1:
                $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];
                break;
            case 2:
                $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE')['FS'];
                break;
            case 3:
                $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE')['NAS'];
                break;
            case 4:
                $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'];
                break;
        }
        $recoveryposition = intval($params['recoverInfo']['pathtype']);
        $recoverytimetype = intval($params['typeInfo']['type']);
        $timestrategylist = $this->groupRecoverTimeList($params['typeInfo']);
        $transportstrategy = (new Backup())->groupTransportStrategy($params['typeInfo']['high']['trasfer']);
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $taskname,
            $moduletype,
            $recoveryposition,
            $recoverytimetype,
            $timestrategylist,
            $transportstrategy
        );
        $pfMSg['task_type'] = $tasktypeArr['RECOVERY'];
        $pfMSg['recovery_level'] = 1;
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverFileList($params['pointInfo'], $params['recoverInfo']);
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedInfo']);
        $pfMSg['password'] = $params['recoverInfo']['password'];
        $pfMSg['thread_num'] = $params['thread_num'];
        $pfMSg['nas_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['new_dir_create'] = $params['recoverInfo']['new_dir_create'];
        //是否是跨平台传输
        $pfMSg['cross_platform_transform'] = $params['recoverInfo']['cross_platform_transform'];
        // $submodule_type = intval($params['pointInfo']['type']);
        $pfMSg['distinct_flag'] = intval($params['distinct_flag']);
        //目录树恢复
        $pfMSg['dir_tree_recovery_flag'] = intval($params['highInfo']['dir_tree_recovery_flag']);
        //同名文件处理
        $pfMSg['same_file_strategy'] = intval($params['highInfo']['same_file_strategy']);
        //无效快捷方式清理
        $pfMSg['link_file_pass_flag'] = intval($params['highInfo']['link_file_pass_flag']);
        //文件权限恢复
        $pfMSg['permission_operate_flag'] = intval($params['highInfo']['permission_operate_flag']);
        $pfMSg['submodule_type'] = $submoduleType;
        //传输代理
        //从文件/obs那边传过来的传输代理uuid写到high_trasfer里面在
        $pfMSg['appliance_uuid'] = empty($params['appliance_uuid']) ? $params['typeInfo']['high']['trasfer']['appliance_uuid'] : $params['appliance_uuid'];
        $pfMSg['agent_uuid'] = empty($params['appliance_uuid']) ? $params['typeInfo']['high']['trasfer']['appliance_uuid'] : $params['appliance_uuid'];
        $pfMSg['agent_pool_uuid'] = empty($params['appliance_pool_uuid']) ? $params['typeInfo']['high']['trasfer']['appliance_pool_uuid'] : $params['appliance_pool_uuid'];
        //重试策略
        $pfMSg['retry_strategy'] = (new Backup())->groupRetryStrategy($params['retry_strategy']);
        //忽略节点资源限制
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['highInfo']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMSg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy']);
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['pointInfo']['pointUUID']);
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$params['pointInfo']['pointUUID']]);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }
        $mbResult = $this->service()->createRecoverJob($nodeuuid, $pfMSg);
        $result = $mbResult['result'];
        // //由于是立即恢复的方式 创建完成后启动任务
        // if ($mbResult['result']) {
        //     $startResult = $this->startRecoverJob($taskname);
        // }
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($taskname);
            } else {
                $startResult = true;
            }
            //启动任务成功,直接返回创建任务成功
            return $this->muOpResult($result, $operate, $mbResult['msg']);
        } else {
            //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
            return $this->muOpResult($result, $operate, $mbResult['msg'],'', $mbResult['errorCode']);
        }
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
            $pathtype = intval($recoverInfo['pathType']);
            $newRootPath = '';
            if ($pathtype == xphp_get_config('app', 'FLAG')['UNSET']) {
                //异机恢复
                $newRootPath = $recoverInfo['path'];
                $agentUUID = $recoverInfo['agentUUID'];
            }
            $files[] = array(
                'agent_uuid' => $recoverInfo['agentUUID'],
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
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    private function object_to_array($obj)
    {
        $obj = (array) $obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array) $this->object_to_array($v);
            }
        }

        return $obj;
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName 任务名
     * @return boolean
     */
    private function startRecoverJob(string $taskName)
    {   
        $param  = "(". xphp_get_config('module', 'MODULE_TYPE')['FS'] .",".xphp_get_config('module', 'MODULE_TYPE')['NAS'].")";
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type in ".$param. " and bt.task_type = ?"; 
        $data = $this->dbSelect($sql,
            array(
                $taskName,
                xphp_get_config('task', 'TASKTYPE')['RECOVERY']
            )
        );
        if (!$data) {
            return false;
        }
        //调用系统统一启动任务接口.不重新写
        $result = (new HadoopJobController())->startJob(
            $data[0]['task_uuid'],
            xphp_get_config('task', 'BACKUP_MODE')['FULL']
        );
        //这里直接返回成功或失败 bool
        return $result[0];
    }

    /**
     * 根据路径得到文件名
     * @param unknown $path
     */
    private function getFileName($filetype, $filepath)
    {
        $pathArr = explode("/", $filepath);
        $arrLen = count($pathArr);
        if ($filetype == 2) {
            $filepath = $pathArr[$arrLen - 2];
        } else {
            $filepath = $pathArr[$arrLen - 1];
        }
        return $filepath;
    }
    /**
     * 获取恢复目录树
     * @param unknown $path
     */
    public function getRecoverPathTree($params)
    {
        $parent_dir = $params['fetch_root_dir'];
        $cluster_uuid = $params['hadoop_cluster_id'];
        $info = array(
            "result" => true,
            "data" => array(),
            "msg" => "",
            "errorCode" => 0,
        );
        $tree = array();
        //获取该集群下的目录
        //获取参数
        $data = array();
        $data['target_uuid'] = $cluster_uuid;
        $data['limit_count'] = $params['limit_count'];
        $data['search_file_name'] = '';
        $data['dir_path'] = htmlspecialchars_decode($parent_dir);
        $data['start_after'] = $params['start_after'];
        $data['code_type'] = 2;
        $data['search_index'] = intval($params['start']);
        $data['module_type'] = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $data['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];

        //获取节点
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->getRecoveryFileZtree($nodeuuid, $data);
        $info['result'] = $mbResult['result'];
        if (!$mbResult['result']) {
            $msg = $mbResult['msg'];
            $opName =  'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY';
            $operate = (new FsOpcode())->getOpcodeDes($opName);
            return $this->muOpResult($mbResult['result'], $operate, $msg,'', $mbResult['errorCode']);
        }
        $list = $mbResult['msg']['item_list'];
        //构建文件目录树
        foreach ($list as $d) {
            $name = $d['item_name'];
            $tree[] = array(
                "icon" => $d['item_type'] == 2 ? './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                "icon_open" => $d['item_type'] == 2 ? './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                "icon_close" => $d['item_type'] == 2 ? './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                "id" => $d['item_path'],
                "isParent" => $d['item_type'] == 2,
                "name" => $name,
                'dir_path' => $d['item_path'],
                "nocheck" => false,
                "pId" => $parent_dir,
                "title" => $d['item_path'],
                "type" => 3,
                "clusteruuid" => $cluster_uuid,
                "code_type" => $d['code_type'],
                "filepath" => $d['item_path'],
                "filetype" => $d['item_type'],
                'more' => false,
                'event_type' => 'hadoop_cluster',
                'uuid' => $cluster_uuid,
                "new_dir_create" => xphp_get_config('app', 'FLAG')['UNSET'], //文件夹是否是新建的
                'noEditBtn' => true,
                'noRemoveBtn' => true,
                'isnew' => false, //文件夹是否是最新的新建
            );

        }
        if (intval($mbResult['msg']['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            //如果没有显示完全 则显示加载更多
            $more = array(
                'id' => $parent_dir . '/more',
                "isParent" => false,
                "name" => xphp_get_lang('WEB_FILE_MORE'),
                'dir_path' => $parent_dir,
                "nocheck" => true,
                "pId" => $parent_dir,
                "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                "clusteruuid" => $cluster_uuid,
                "more" => true,
                "next_start" => $mbResult['msg']['start_after'], //未完成时 下一个开始的文件路径
                "next_index" => $mbResult['msg']['current_next_index'],       //从哪个位置开始加载
                'event_type' => 'hadoop_cluster',
                'uuid' => $cluster_uuid,
                'noRemoveBtn' => true,
                'isnew' => false, //文件夹是否是最新的新建
                "new_dir_create" => xphp_get_config('app', 'FLAG')['UNSET'], //文件夹是否是新建的
                'noEditBtn' => true,
            );
            $tree[] = $more;
        }
        $info['data']['file_nodes'] = $tree;
        $info['data']['finish_flag'] = $mbResult['msg']['is_search_finish'];
        return $info;
    }
}