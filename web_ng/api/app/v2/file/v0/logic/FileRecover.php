<?php

namespace app\v2\file\v0\logic;

use app\v2\backupData\v0\logic\DataManage;
use app\v2\common\logic\Backup;
use app\v2\common\logic\Recover;
use app\v2\job\v0\logic\JobInfo;
use app\v2\opcode\PfOpcode;
use app\v2\resources\v0\logic\Node;
use app\v2\tenant\v0\logic\Index;
use app\v2\user\v0\logic\User;
use app\v2\common\logic\JobInfo as JobInfos;
use xphp\BLLHandler;
use app\v2\opcode\NodeOpcode;
use app\v2\resources\v0\logic\Index as Resources;
use app\v2\tenant\v0\logic\Index as TenantIndex;
use app\v2\nas\v0\logic\NasRecover;
use app\v2\resources\v0\logic\Storage;
use app\v2\fileback\v0\logic\FileBackUp;
/**
 * note          文件 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileRecover extends Recover
{
    /**
     * 文件恢复搜索---创建搜索消息
     * @param array $params 参数
     * @return array
     */
    public function createSearchJob(array $params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_THREAD_CREATE';
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);
        //这里需要重写请求参数
        $msg = [
            'search_mode' => $params['search_mode'],
            'md5_list' => $params['md5_list'],
            'path_name' => $params['path_name'],
            'timepoint_uuid' => $params['timepoint_uuid'],
            'req_total_num' => $params['req_total_num'],
            'module_type' => $params['module_type'],
            'submodule_type' => $params['sub_module_type']
        ];
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg), true, true);
        if ($mbResult['result']) {
            $info = [
                'result' => true,
                'msg' => $mbResult['msg']['task_uuid'],
            ];
        } else {
            $result = $mbResult['result'];
            $operate = xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE');
            return $this->muOpResult($result, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
        return $info;
    }

    /**
     * 文件恢复搜索---停止搜索
     * @param array $params 参数
     * @return array
     */
    public function stopSearchJob(array $params): array
    {
        $opName = 'FS_OP_TYPE_SEARCH_STOP';

        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);
        $msg = [
            'task_uuid' => $params['thread_uuid'],
        ];
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg));
        if ($mbResult['result']) {
            $info = [
                'result' => true
            ];
        } else {
            //失败
            $info = [
                'result' => false,
                'msg' => xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE'),
                'code' => $mbResult['errorCode']
            ];
        }

        return $info;
    }

    /**
     * 文件恢复搜索---获取搜索结果
     * @param array $params 参数
     * @return array|string
     */
    public function getRecoSearchInfo(array $params)
    {   
        $opName = 'FS_OP_TYPE_SEARCH_RESULT_GET';
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);
        $msg = [
            'task_uuid' => $params['thread_uuid'],
            'number' => $params['limit'],
            'offset' => $params['offset'],
        ];
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg), true, false);
        
        $operate = xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_RESULT_GET');
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $result = $mbResult['msg'];
        $filenode = array();
        $hadoop_submodule_type = xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];
        foreach ($result['item_list'] as $file) {
            $modifyTime = $file['modify_time'];
            if($params['sub_module_type'] == $hadoop_submodule_type ){
                $modifyTime = $modifyTime / 1000; //因为hadoop模块的时间戳默认存的是毫秒，所以需要除以1000
            }
            if ($file['file_type'] == 1) {
                // 文件
                $titleDes = xphp_get_lang('WEB_FILE_FILE_SIZE') . v1_calsize($file['file_size'], true)
                    . PHP_EOL . xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($modifyTime);
            } else {
                $titleDes = xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($modifyTime);
            }
            $filenode[] = array(
                'pid' => 0,
                'id' => $file['path_name'],
                'name' => $file['path_name'],
                'title' => $titleDes,
                'file_type' => $file['file_type'],
                'md5_high' => $file['md5_high'],
                'md5_low' => $file['md5_low'],
                'file_size' => $file['file_size'],
                'modify_time' => $file['modify_time'],
                'file_offset' => $file['offset'],
                'icon' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                'iconOpen' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                'iconClose' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                'isParent' => $this->getBoolType($file['file_type']),
                'path' => $file['path_name'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'md5_flag' => '1',
                'search_node' => true,
            );
        }
        return [
            'search_finish_flag' => $result['finish_flag'],
            'current_total_num' => $result['number'],
            'current_dir_num' => $result['dir_num'],
            'current_file_num' => $result['file_num'],
            'all_file_num' => $result['all_file_num'],
            'all_dir_num' => $result['all_dir_num'],
            'offset' => $result['offset'],
            'filenode' => $filenode,
        ];
    }

    /**
     * 得到文件恢复源树(灾备中心/文件备份数据)
     * @param array $params 参数
     * @return array
     */
    public function getFileDataTree(array $params): array
    {
        
        $dataflag = $params['data_flag'];
        $storageUuid = $params['storage_uuid'];
        $subModuleType = $params['sub_module_type'];
        $filesubModuleTypes =  xphp_get_config('module', 'SUBMODULE_TYPE');
        $moduleType = $subModuleType == 2 ? xphp_get_config('module', 'MODULE_TYPE')['NAS'] : xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $sql = "select bbt.real_node_uuid, bbt.id, bbt.module_type, bbt.task_type, bbt.task_uuid, bbt.task_name, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.importance_flag,bbt.src_data_deleted_flag, 
                      bbt.user_uuid, bbt.user_name, 
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.source_agent_type as os_type, fbt.detail,
                      bsr.node_uuid, bsr.storage_type 
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and 
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
                       and bbt.import_flag = ? and
	                   bbt.module_type = {$moduleType} and bbt.sub_module_type = {$subModuleType} and
	                   bbt.data_local_flag = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = new TenantIndex();
        $tenantMangerFlag = (new User())->pCheckTenantManager();
        $userInfo = xphp_get_user_info();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($userInfo['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($userInfo['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }



        // // 如果是有全局观察者权限，那么显示所有的数据
        // if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
        //     // 没有全局观察者权限
        //     //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
        //     if ($dataflag && $tenantMangerFlag && $allManageFlag) {
        //         $userList = $tenantHandler->getTenantAllUser($userInfo['tenantuuid']);
        //         $userListDes = implode("','", $userList);
        //         $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
        //     } else {
        //         // 权限重构
        //         //找到不同模块的权限
        //         switch ($subModuleType) {
        //             case $filesubModuleTypes['FS']:
        //                 $session =  $_SESSION['authUser']['fileprotect_look'];
        //                 break;
        //             case $filesubModuleTypes['NAS']:
        //                 $session =  $_SESSION['authUser']['nas_protect_look'];
        //                 break;
        //             case $filesubModuleTypes['HADOOP']:
        //                 $session =  $_SESSION['authUser']['hadoop_protect_look'];
        //                 break;
        //             case $filesubModuleTypes['OBS']:
        //                 $session =  $_SESSION['authUser']['obs_protect_look'];
        //                 break;
        //             default:
        //                 $session =  $_SESSION['authUser']['fileprotect_look'];
        //                 break;
        //         }
        //         $authUser = $session ?? [];
        //         if ($authUser) {
        //             $userUuidArr = array_merge([$userInfo['userUuid']], $authUser);
        //             $userUuids = "('" . implode("','", $userUuidArr) . "')";
        //             $sql .= " and bbt.user_uuid in $userUuids ";
        //         } else {
        //             $sql .= " and bbt.user_uuid = ? ";
        //             $sqlParams = array_merge($sqlParams, array($userInfo['userUuid']));
        //         }
        //     }
        // }
   


        if (v1_auth_need_operation()) {
             // 备份数据恢复属于操作权限，不能归属于查看 文件这里用的v1_auth_need_operation？
            // 三权模式下的操作员只能查看分配的资源数据
            // 不是超级管理员也不是全局观察者，也只能看到自身的或者管理的用户的的数据
            $useauth_config = xphp_get_config('user', 'USER_AUTH');
            switch ($subModuleType) {
                case $filesubModuleTypes['FS']:
                    $useauth =  $useauth_config['fileprotect'];
                    break;
                case $filesubModuleTypes['NAS']:
                    $useauth =  $useauth_config['nas_protect'];
                    break;
                case $filesubModuleTypes['HADOOP']:
                    $useauth =  $useauth_config['hadoop_protect'];
                    break;
                case $filesubModuleTypes['OBS']:
                    $useauth =  $useauth_config['obs'];
                    break;
                default:
                    $useauth =  $useauth_config['fileprotect'];
                    break;
            }
            $userUuidSql = v1_auth_get_users($useauth);
            $sql .= " and bbt.user_uuid in ({$userUuidSql}) ";
        }

        //admin不能看租户内部的资源
        if (empty($userInfo['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }

        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ?";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET']));
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
        if (empty($data)) {
            return $node;
        }
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $taskType = xphp_get_config('task', 'TASKTYPE');
        foreach ($data as $d) {
            if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT'] && $dataflag)
                continue;
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            // $origin_vendor = '';
            // if($subModuleType == $filesubModuleTypes['OBS']){
            //     $detail = json_decode($d['detail'], true);
            //     $origin_vendor =  $detail['origin_vendor'];
            // }
            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                // 副本数据
                if ( in_array($d['task_type'], [$taskType['BACKUP_COPY'], $taskType['BACKUP_COPY_FETCH']])) {
                    $name = $taskName . "(" . xphp_get_lang('UI_COPY_DATA') . ")";
                }
                // 归档数据
                if (in_array($d['task_type'], [$taskType['ARCHIVE'], $taskType['ARCHIVE_FETCH']])) {
                    $name = $taskName . "(" . xphp_get_lang('UI_ARCHIVE_DATA') . ")";
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != $userInfo['userUuid']) {
                    $name .= "(" . $d['user_name'] . ")";
                }
              
                $node[] = array(
                    "id" => $d['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "task_name" => $taskName,
                    "open" => true,
                    "nocheck" => !$dataflag,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "node_uuid" => $nodeUuid,
                    "task_uuid" => $d['task_uuid'],
                    "isParent" => true,
                    "agent_uuid" => $d['agent_uuid'],
                    "storage_uuid" => $storageUuid,
                    "storage_type" => $d['storage_type'],
                    // 'origin_vendor' => $origin_vendor,
                );
            }
            //设置显示的图标
            $icon = "";
            $agentUuid = $d['agent_uuid'];
            switch ($subModuleType){
                case $filesubModuleTypes['FS']:
                    $icon = $d['os_type'] == "Windows" ? "./img/os/Windows.png" : "./img/os/Linux.png";
                    break;
                case $filesubModuleTypes['NAS']:
                    $detail = json_decode($d['detail'], true);
                    $nasuuid = $detail['nas_uuid'];
                    $nastype = $detail['nas_type'];
                        if ($nastype == 6) {
                        $icon = "./img/nas/nfs.png";
                    } else if ($nastype == 7) {
                        $icon = "./img/nas/cifs.png";
                    }
                    $agentUuid = $nasuuid;
                    break;
                case $filesubModuleTypes['HADOOP']: 
                    $icon = "./img/hadoop/hadoop.png";
                    break;
                case $filesubModuleTypes['OBS']: 
                    $detail = json_decode($d['detail'], true);
                    $icon = $d['os_type'] == 'Windows' ? './img/os/Windows.png' : './img/s3/cloud-platform.svg';
                    break;    
            }
            //检查并添加agent
            if (!in_array($agentUuid . "_" . $d['task_uuid'], $agent)) {
                $agent[] = $agentUuid . "_" . $d['task_uuid'];
                $agentName = $this->getFileAgentNameStr($agentUuid , $d['agent_name'], $d['agent_ip'], $subModuleType);
                if($subModuleType == $filesubModuleTypes['OBS']){
                    $agentName =  $d['agent_name'];
                }
                $agentTitle = $agentName; //title和name一样
                $node[] = array(
                    "id" => $agentUuid . "_" . $d['task_uuid'],
                    "pId" => $d['task_uuid'],
                    'name' => $agentName,
                    "title" => $agentTitle,
                    "nocheck" => !$dataflag,
                    "type" => 2,
                    "clickshow" => true,
                    "icon" => $icon,
                    "node_uuid" => $nodeUuid,
                    "task_uuid" => $d['task_uuid'],
                    "agent_uuid" => $d['agent_uuid'],
                    "isParent" => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    "storage_uuid" => $storageUuid,
                    "storage_type" => $d['storage_type'],
                    // 'origin_vendor' => $origin_vendor,
                );
            }

        }
        return $node;
    }

    /**
     * 获取恢复源设备名称
     * @param string $agentuuid 时间点中的uuid
     * @param string $agentname 时间点中的主机名
     * @param string $ip      时间点中的ip
     * @return string name(ip)
     */
    public function getFileAgentNameStr(string $agentuuid, string $agentname, string $ip, string $subModuleType){
        if($subModuleType == xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']){
            $nasRecover = new NasRecover();
            $agentName = $nasRecover -> getNasNameStr($agentuuid, $agentname, $ip);
        }else{
            $agentName = $this->getAgentNameStr($agentuuid, $agentname, $ip);
        }
        return $agentName;
    }

    /**
     * 校验文件数据加密密码正确性
     * @param array $params 参数
     * @return array|string
     */
    public function checkFSEncryptPass(array $params)
    {
        $timepointuuid = $params['timepoint_uuid'];
        $inputpass = $params['passwd'];
        $sqlencrypt = "select encrypted_flag, detail from bd_backup_timepoint where timepoint_uuid  = ? ";
        $sqlParamsencrypt = array($timepointuuid);
        $dataencrypt = $this->dbSelect($sqlencrypt, $sqlParamsencrypt);//password_auto_flag, password
        $dataencrypt = $dataencrypt[0];
        $detail = json_decode($dataencrypt['detail'], true);

        if ($dataencrypt['encrypted_flag'] == 1 && intval($detail['password_auto_flag']) == 2) {//加密开 自动生成密码关
            $inputpass = v1_pt_pass_encrypt(htmlspecialchars_decode($inputpass));//htmlspecialchars_decode把特殊字符转成原来的字符
            if ($detail['password'] != $inputpass) {//数据库中的密码不等于页面输入密码
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_BACKUP_DATA_ENCRYPT'),
                    xphp_get_lang('UI_FILE_INCORRECT_PASSWORD'),
                    'warning'
                );
            }
        }

        return [
            'encrypted_flag' => $dataencrypt['encrypted_flag'],
            'password_auto_flag' => intval($detail['password_auto_flag']),
            'flag' => true
        ];
    }

    /**
     * 得到备份文件列表
     * @param array $params 参数
     *                      sclass                  文件样式类型  s/m/l 24 32 48
     *                      timepoint_uuid(string)   时间点UUID
     *                      root_flag(int)           是否是根节点
     *                      offset(int)               开始位置
     *                      limit(int)              获取条数
     *                      path(string)             当前路径
     *                      md5_flag(int)            是否有MD5信息标志
     *                      md5_high(int)            MD5的高八位
     *                      md5_low(int)             MD5的第八位
     *                      三种情况
     *                      1.初始展示[timepoint_uuid, 1, 0, N, '', 0, '', '']
     *                      2.更多信息[timepoint_uuid, 0, N, N+M, path, md5_flag, md5_high, md5_low]
     *                      3.进入目录[timepoint_uuid, 0, 0, N, path, md5_flag, md5_high, md5_low]
     * @return array|string
     */
    public function getBackupFileDir(array $params)
    {
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = $params['root_flag'];
        $start = $params['start'];
        $number = $params['limit'] ?? 10;
        $path = $params['path'] ?? '';
        $md5Flag = $params['md5_flag'];
        $md5High = $params['md5_high'] ?? '';
        $md5Low = $params['md5_low'] ?? '';
        $pid = $params['pid'] ?? 0;
        $sclass = $params['sclass'];
        $subModuleType = $params['sub_module_type'];
        $isSearch = $params['isSearch'];
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

        $nodeHandler = new Node();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true, true);
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $pfOpcode = new PfOpcode();
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $list = array(
            'finish_flag' => $data['finish_flag'],       //文件是否列完成   1完成,2未完成
            'next_start' => $data['next_start'],       //未完成时,下一个开始位置
            'path' => $data['path'],                    //当前路径
            'timepoint_uuid' => $data['timepoint_uuid'],
        );
        $obsSubModule =  xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'];
        $fileList = array();
        foreach ($data['item_list'] as $d) {
            switch ($d['type']) {
                case 1:
                    $icon = './img/fs/wenjian.png';
                    if ($params['sub_flag']) {
                        // 截取字符串最后一个/后面所有的内容
                        $name = substr($d['path'], strrpos($d['path'], '/') + 1);
                    } else {
                        $name = $d['path'];
                    }
                    break;
                case 2:
                    $icon = './img/fs/wenjianjia.png';
                    if ($params['sub_flag']) {
                        // 截取字符串倒数第2个/后面所有的内容
                        $name = substr($d['path'], 0, strrpos($d['path'], '/'));//最后一个斜杠前面的所有内容
                        $name = substr($name, strrpos($name, '/') + 1);
                    } elseif ($d['path'] != '/') {//不是linux根目录
                        $name = substr($d['path'], 0, strrpos($d['path'], '/'));
                    } else {//是linux根目录不剪切斜杠
                        $name = $d['path'];
                    }
                    break;
                case 3:
                    $icon = './img/fs/cipan.png';
                    //如果是对象存储这里显示的是存储桶
                    if($subModuleType == $obsSubModule){
                        $icon = './img/s3/cunchutong.svg';
                    }
                    $name = substr($d['path'], 0, strrpos($d['path'], '/'));
                    break;
                default:
                    break;
            }
            $filename = $d['filename'];
            $fileType = xphp_get_config('file', 'FILETYPE');
            if($subModuleType == $obsSubModule){
                if(!$name){
                    $name = '/';
                }
            }else{
                //如果是文件 nas hadoop 直接用 $d['filename']
                $name =  $filename;
            }
            $fileList[] = array(
                'id' => $d['path'],
                'pid' => $pid,
                'name' => $name,
                'nocheck' => false,
                'title' => $name,
                'path' => $d['path'],
                'btype' => $d['type'],   //文件类型
                'isParent' =>  $this->getBoolType($d['type']),
                'icon' => $icon ?? '',
                'iconOpen' => $d['type'] == 2 ? './img/fs/wenjianjiaopen.png' : $icon,
                'iconClose' => $d['type'] == 2 ? './img/fs/wenjianjia.png' : $icon, // 从后台获取的文件类型
                'isfile' => $d['type'] == $fileType['FILE'],   //是否是文件
                'sclass' => $this->getFileClassName($d['type'], $filename, $sclass), //显示类型
                'md5_flag' => $d['md5_flag'],
                'md5_high' => $d['md5_high'],
                'md5_low' => $d['md5_low'],
                "create_time" => $d['create_time'],
                'modify_time' => $d['modify_time'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'more' => false,
                'searchNode' => $isSearch
            );
        }
        $list['filelist'] = $fileList;
        if (intval($data['finish_flag']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => $data['path'] . '/more',
                'pid' => $pid,
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'nocheck' => true,
                'more' => true,
                'next_index' => $data['next_start'],       //从哪个位置开始加载
                // "search_file_name" => $data['path'],          //从哪个目录开始加载
                // "dir_path" => $params['dir'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'sclass' => $sclass,                //显示类型
                'md5_flag' => $md5Flag,
                'md5_high' => $md5High,
                'md5_low' => $md5Low,                             //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            array_push($list['filelist'], $more);
        }

        return $list;
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
     * 异步获取文件时间点
     * @param array $params 参数
     * @return string
     */
    public function getSyncFileTimepoint(array $params)
    {
        // $recoverflag = $params['recover_flag']; //文件恢复加载时间点
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $id = $params['id'];
        $storageUuid = $params['storage_uuid'];
        $chkDisabled = false;
        $subModuleType =  $params['sub_module_type'];
        $filesubModuleTypes =  xphp_get_config('module', 'SUBMODULE_TYPE');
        $sql = "select bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, 
                bbt.real_node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag, bbt.encrypted_flag, bbt.remarks, bbt.detail, bbt.src_data_deleted_flag, bbt.task_name,
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail 
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        			  bbt.storage_uuid = bsr.storage_uuid and
                      bbt.deleted_flag = ? and bbt.available_flag = ?
                      and bbt.import_flag = ? and
	                  bbt.task_uuid = ? and fbt.agent_uuid = ? and bbt.data_local_flag = ? ";

        $flag = xphp_get_config('app', 'FLAG');
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
        // foreach ($data as $point) {
        //     if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
        //         $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
        //     } else {
        //         $unfullList[] = $point;
        //     }
        // }
        // while (!empty($unfullList)) {
        //     $unfullCount = count($unfullList);
        //     $unfullCountTmp = count($unfullList);
        //     foreach ($unfullList as $key => $unfull) {
        //         $dependId = $unfull['depend_point_uuid'];
        //         foreach ($fulluuidList as $key => $value) {
        //             if ($dependId == $key) {
        //                 $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
        //                 unset($unfullList[$key]);
        //                 $unfullList = array_values($unfullList);

        //                 $unfullCountTmp--;
        //             }
        //             continue;
        //         }
        //     }
        //     if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
        //         break;
        // }
        foreach ($data as $point) {
            if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $unfullList[$point['timepoint_uuid']] = $point;
            }
        }
        $changed =  true;
        while($changed && !empty($unfullList)) {
            $changed = false;
            foreach($unfullList as $uuid => $unfull) {
               $dependId = $unfull['depend_point_uuid'];
                if(isset($fulluuidList[$dependId])) {
                    $fulluuidList[$uuid] = $fulluuidList[$dependId]; //这里值存的是它的依赖点
                    unset($unfullList[$uuid]);
                    $changed = true;
                }
            }
        }

        $node = array();
        $jobs = new JobInfo();
        $storageHandler = new Storage();
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);
        $storageOfflineStatus = xphp_get_config('storage', 'STORAGE_STATUS')['OFFLINE'];
        foreach ($data as $d) {
            //获取时间点状态--是否正常/异常
            $pointMixedStatus = $jobs->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
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
            $name = $this->parseDate($d['timepoint']) . " (" . $jobs->getTimepointTypeDes($d['backup_mode']) . ")";
            $title = $name;
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')' . '</span>';
            }
            $chkDisabled = $isOfflineStorage == $storageOfflineStatus;

            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }

            $mark = "";
            if ($d["src_data_deleted_flag"] == 1) { //归档开启
                $mark = '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')' . $mark;
            }
            $timepointuuid = $d['timepoint_uuid'];
            //获取ostype
            if($subModuleType ==  $filesubModuleTypes['NAS']){
                $ostype = $fbtdetail['nas_type'];
            }else if($subModuleType ==  $filesubModuleTypes['OBS']){
                 $ostype = 'linux';
            }else{
                $ostype = $fbtdetail['os_type'];
            }
            if (intval($d['backup_mode']) == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $id,
                    "name" => $name . $mark,
                    "title" => $title,
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "chkDisabled" => $chkDisabled,
                    "point_uuid" => $d['timepoint_uuid'],
                    // "timepoint_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => htmlspecialchars_decode($d['agent_name']),
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                    "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "task_uuid" => $taskuuid,
                    "node_uuid" => $nodeUuid,
                    "timepoint" => $this->parseDate($d['timepoint']),
                    "encrypted_flag" => $encrypted_flag,
                    "ostype" => $ostype,
                    "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    'storage_name' => $d['storage_nickname'],
                    "storage_type" => $d['storage_type'],
                    "integrity_check_flag" => intval($d['integrity_check_flag']) == 1,
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
                "nocheck" => false,
                "chkDisabled" => $chkDisabled,
                "point_uuid" => $d['timepoint_uuid'],
                // "timepoint_uuid" => $d['timepoint_uuid'],
                "depend_uuid" => $d['depend_point_uuid'],
                "agent_name" => $d['agent_name'],
                "agent_ip" => $d['agent_ip'],
                "agent_uuid" => $d['agent_uuid'],
                "task_name" => $d['task_name'],
                "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                "mode" => intval($d['backup_mode']),
                "task_uuid" => $taskuuid,
                "node_uuid" => $nodeUuid,
                "timepoint" => $this->parseDate($d['timepoint']),
                "encrypted_flag" => $encrypted_flag,
                "ostype" => $fbtdetail['os_type'],
                "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                "storage_name" => $d['storage_nickname'],
                "storage_type" => $d['storage_type'],
                "integrity_check_flag" => intval($d['integrity_check_flag']) == 1,
                "point_status" => $pointMixedStatus['status'],
                "available_flag" => $pointMixedStatus['available_flag'],
                         
            );
        }
        return $node;
         
    }

    /**
     * 搜索恢复时间点
     * @param array $params 参数
     * @return string
     */
    public function searchTimepoint($params)
    {
        // $search = $params['search'];
        $storageUuid = $params['storage_uuid'];
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $subModuleType = $params['sub_module_type'];
        $filesubModuleTypes =  xphp_get_config('module', 'SUBMODULE_TYPE');
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');
        $userInfo = xphp_get_user_info();
        // $filesubModuleTypes =  xphp_get_config('module', 'SUBMODULE_TYPE');
        $moduleType = $subModuleType == 2 ? xphp_get_config('module', 'MODULE_TYPE')['NAS'] : xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $sql = "select bbt.deleted_flag, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag, bbt.operation_status, bbt.merge_status, 
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail,
        bsi.virus_scan_status, bsi.integrity_check_status 
        from fs_backup_timepoint fbt, bd_storage_resource bsr, bd_backup_timepoint bbt 
        left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
        where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        bbt.storage_uuid = bsr.storage_uuid and 
        bbt.available_flag = 1 and 
        bbt.deleted_flag = 2 and 
        bbt.module_type = {$moduleType} and bbt.sub_module_type = {$subModuleType} and bbt.data_local_flag = 1 and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
         if (empty($userInfo['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . $userInfo['userUuid'] . "'";
        }
         //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        // if (!empty($search)) {
        //     $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        // }
        $sqlParams = array();
        if (!empty($params['recovery_range'])) {
            $sql .= ' and bbt.timepoint between ? and ?';
            $sqlParams = array_merge($sqlParams, array($params['startTime'], $params['endTime']));
        }
        //权限
        //获取权限
        switch ($subModuleType) {
            case $filesubModuleTypes['FS']:
                $session =  $_SESSION['authUser']['fileprotect_look'];
                break;
            case $filesubModuleTypes['NAS']:
                $session =  $_SESSION['authUser']['nas_protect_look'];
                break;
            case $filesubModuleTypes['HADOOP']:
                $session =  $_SESSION['authUser']['hadoop_protect_look'];
                break;
            case $filesubModuleTypes['OBS']:
                $session =  $_SESSION['authUser']['obs_protect_look'];
                break;
            default:
                $session =  $_SESSION['authUser']['fileprotect_look'];
                break;
        }
        $authUser = $session ?? [];
        if ($authUser) {
            $userUuidArr = array_merge([$userInfo['userUuid']], $authUser);
            $userUuids = "('" . implode("','", $userUuidArr) . "')";
            $sql .= " and bbt.user_uuid IN $userUuids ";
        } else {
            $sql .= " and bbt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userInfo['userUuid']));
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
            $pointData = $this->dbSelect($sql, array_merge($sqlParams,array($storageUuid)));
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql, $sqlParams);
        }
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == $backupMode['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                //如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
                //这里比较浪费时间，后续看能不能优化
                if ($point['backup_mode'] == $backupMode['DIFFERENTIAL']) { //差异
                    $sqlfull = "select bbt.deleted_flag, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag, bbt.integrity_check_flag, bbt.operation_status, bbt.merge_status,
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail,
                    bsr.storage_nickname, bsr.node_uuid,bsr.storage_type, 
                    bsi.virus_scan_status, bsi.integrity_check_status  
                    from fs_backup_timepoint fbt, bd_storage_resource bsr, bd_backup_timepoint bbt
                    left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                    bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
                    bbt.module_type = {$moduleType} and bbt.data_local_flag = 1 and bbt.timepoint_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
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
        $node = array();
        $jobs = new JobInfo();
        $nodeHandler = new Node();
        foreach ($pointData as $d) {
            $pointMixedStatus = $this->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
            $detail = json_decode($d['detail'], true);
            $ftDetail = json_decode($d['ft_detail'], true);
            $name = $this->parseDate($d['timepoint']) . " (" . $jobs->getTimepointTypeDes($point['backup_mode']) . ")";
            $title = $name;
            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }

            $mark = "";
            if ($d["src_data_deleted_flag"] == 1 && empty($params['recovery_range'])) { //归档开启 不是恢复页面的搜索才显示归档
                $mark = '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')' . $mark;
            }
            //添加GFS标识
            $mark = $mark . $jobs->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            // $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == $backupMode['FULL']) {
                $fullTimepoint = $d['timepoint_uuid'];
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $d['agent_uuid'] . "_" . $d['task_uuid'],
                    "name" => $name,
                    "title" => $name,
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "oldname" => $name,
                    "point_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => $d['agent_name'],
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                    "icon" => $jobs->getTimepointIcon($d['backup_mode']),
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
                        $ftDetail = json_decode($d['ft_detail'], true);
                        $name = $this->parseDate($d['timepoint']) . " (" . $jobs->getTimepointTypeDes($d['backup_mode']) . ")";
                        if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                            $name .= '<i class="fa fa-lock"></i>';
                            $encrypted_flag = true;
                        } else {
                            $encrypted_flag = false;
                        }
                        //添加GFS标识
                        $node[] = array(
                            "id" => $d['timepoint_uuid'],
                            "pId" => $fullTimepoint,
                            "name" => $name,
                            "title" => $name,
                            "checked" => false,
                            "type" => 4,
                            "oldname" => $name,
                            "nocheck" => false,
                            "point_uuid" => $d['timepoint_uuid'],
                            "depend_uuid" => $d['depend_point_uuid'],
                            "agent_name" => $d['agent_name'],
                            "agent_ip" => $d['agent_ip'],
                            "agent_uuid" => $d['agent_uuid'],
                            "task_name" => $d['task_name'],
                            "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                            "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                            "mode" => intval($d['backup_mode']),
                            "timepointuuid" => $d['timepoint_uuid'],
                            "taskuuid" => $d['task_uuid'],
                            "nodeuuid" => $nodeUuid,
                            "chkDisabled" => false,
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
        return $node;

    }
    /**
     * 获取恢复目标-客户端、nas设备、Hadoop集群、对象存储
     * @return object
     */
    public function getRecoveryTarget($params){
        $fileBackUp = new FileBackUp();
        return $fileBackUp->getBackupSource($params);
    }

    /**
     * 获取恢复任务文件备份树   --这个方法暂时没用到
     * @param array $params 参数
     * @return array
     */
    public function getRecoverHostTree($params = array()): array
    {
        $sql = "select ba.id, ba.agent_uuid,ba.os_type, ba.agent_name, ba.hostname,ba.net_model, ba.ip,
                        ba.os_type,online_flag, ba.authorization_module,bag.group_uuid, bag.group_name, bag.detail
                from bd_agent ba 
                left join bd_agent_group bag on ba.group_uuid = bag.group_uuid";
        $user = xphp_get_user_info();
        // 非租户用户不能查看租户的资源
        if (empty($user['tenantuuid'])) {
            $sql .= " LEFT JOIN mt_user_tenant mut on ba.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL and ba.agent_type not in (3, 4, 5)";
        } else {
            $sql .= ' where ba.agent_type not in (3, 4, 5)';
        }
        //userLevel是1 2 3就全部显示
        if (!in_array($user['userLevel'],[1,2,3])) {
            $uuid = (new Resources())->pGetUserAllResource($user['userUuid'], 10);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return [];
            } else {
                // $uuidArr 是一个一维数组  organization_uuid in
                $agentUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and ba.agent_uuid in ($agentUuidsIn)";
            }
        }
        if (!empty($params['keyword'])) {
            $search = $params['keyword'];
            $sql .= " and (ba.agent_name like '%" . $search .
                "%' or ba.hostname like '%" . $search .
                "%' or ba.ip like '%" . $search .
                "%' or bag.group_name like '%" . $search . "%')";
        }
        $data = $this->dbSelect($sql, array());

        $nodes = $group = $agent = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                //分组
                if (!in_array($d['group_uuid'], $group)) {
                    //查分组下的agent
                    $sqlagent = "select agent_uuid from bd_agent where agent_type != 4 and group_uuid = ?";
                    $dataagent = $this->dbSelect($sqlagent, array($d['group_uuid']));
                    array_push($group, $d['group_uuid']);
                    $nodes[] = array(
                        'id' => $d['group_uuid'],
                        'pId' => 0,
                        'name' => $d['group_name'],
                        'title' => $d['group_name'],
                        'isParent' => true,
                        'nocheck' => true,
                        'open' => true,
                        'type' => 1,
                        'icon' => './img/platform/flag.png',
                        'uuid' => $d['group_uuid'],
                        'event_type' => "group",
                        'agentlist' => $dataagent[0],

                    );
                }
                //客户端
                if ($d['online_flag'] != 1) {
                    $authorization_online_info = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')';
                } else {
                    $authorization_online_info = '';
                }
                //客户端名  别名不为空并且不等于IP 显示别名+ip
                if (!empty($d['agent_name']) && $d['agent_name'] != $d['ip']) {
                    $name = $authorization_online_info . $d['agent_name'] . "(" . $d['ip'] . ")";
                } else {
                    $name = $authorization_online_info . $d['hostname'] . "(" . $d['ip'] . ")";
                }
                if (!in_array($d['agent_uuid'], $agent)) {
                    array_push($agent, $d['agent_uuid']);
                    $nodes[] = array(
                        'id' => $d['group_uuid'] . '_' . $d['agent_uuid'],
                        'pId' => $d['group_uuid'],
                        'name' => $name,
                        'title' => $d['ip'],
                        'isParent' => false,
                        'uuid' => $d['agent_uuid'],
                        'nocheck' => false,
                        'type' => 2,
                        'icon' => $d['os_type'] == 'Windows' ? './img/os/Windows.png' : './img/os/Linux.png',
                        'event_type' => 'agent',
                        'os_type' => $d['os_type'],
                        'chkDisabled' => $authorization_online_info == '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')' ? true : false,
                        'net_model' => intval($d['net_model']),
                        //是否可以被选中
                        "isCheckFlag" => $authorization_online_info == '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')' ? true : false,
                        "authorization_module" => true,
                    );
                }
            }
        }

        return $nodes;
    }

    /**
     * 获取恢复任务恢复目标的目录树 
     * @param $params
     * @return array|string
     */
    public function getRecoverPathTree($params)
    {
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $pid = $params['pid']; //父节点ID
        $agentUUID = $params['agent_uuid'];
        $subModuleType = $params['sub_module_type'];
        $this->paramsCheck($agentUUID);
        $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        $codetype = !empty($params['code_type']) ? $params['code_type'] : 2; // 编码类型
        $subModuleTypes = xphp_get_config('module', 'SUBMODULE_TYPE');
        switch ($subModuleType){
            case $subModuleTypes['FS']:
                $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
                break;
            case $subModuleTypes['NAS']:
                $moduleType = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
                break;
            case $subModuleTypes['HADOOP']:
                $moduleType = xphp_get_config('module', 'MODULE_TYPE')['HADOOP'];
                $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY';
                break;
            case $subModuleTypes['OBS']:
                $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OBS'];
                $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY';
                break;
        }
        // $moduleType = $subModuleType == 2 ? xphp_get_config('module', 'MODULE_TYPE')['NAS'] : xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $startAfter = $params['startAfter'];
        $msg = array(
            'agent_uuid' => $agentUUID, //文件和nas要使用这个key 不用target_uuid
            'target_uuid' => $agentUUID, //对象存储和hadoop要使用这个key 不用agent_uuid
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            // 'dir_path' => htmlspecialchars_decode($dir),
            'dir_path' =>$dir,
            'code_type' => $codetype,
            'module_type' => $moduleType,
            'start_after' => $startAfter,
            'submodule_type' => $subModuleType
        );
        $nodeuuid = (new Node())->getLocalNodeUUID();
        if($subModuleType == $subModuleTypes['FS']){
            $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);
        }else if($subModuleType ==  $subModuleTypes['NAS']){
            // 获取nasuuid
            $nasUuid = explode('/', $dir)[3];
            $nodeInfo = (new NasRecover())->getMountNode($nasUuid);
            //消息发往各个节点，成功就返回  失败就继续循环
            $sendFlag = false; //发送消息到后台标志
            foreach ($nodeInfo as $info) {
                $nodeStatus = (new Node)->getNodeAllStatus($info['node_uuid']);
                if ($nodeStatus['flag'] && !$sendFlag) {
                    //如果节点状态正常
                    $msg['agent_uuid'] = $info['agent_uuid'];
                    $mbResult = $this->service()->mbNodeMsgs($opName, $info['node_uuid'], json_encode($msg), true);
                    if ($mbResult['result']) {
                        $sendFlag = true;
                    }
                }
            }

        }
        else if($subModuleType == $subModuleTypes['HADOOP']){
            $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, json_encode($msg), true, true, $subModuleType);
        }else if($subModuleType == $subModuleTypes['OBS']){
            $mbResult = $this->mbOBSMsg($nodeuuid, $opName, json_encode($msg), true, true, $subModuleType);
        }
       
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $nodeOpcode = new NodeOpcode();
            $operate = $nodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        $data = $mbResult['msg'];

        $i = 0;
        $flag = xphp_get_config('app', 'FLAG');
        //设置icon
        $icon  = './img/fs/wenjianjia.png';
        $iconOpen = './img/fs/wenjianjiaopen.png';
        $iconClose = './img/fs/wenjianjia.png';
        if  ($subModuleType ==  $subModuleTypes['OBS'] && empty($dir)){
            $icon = $iconOpen = $iconClose = './img/s3/cunchutong.svg';
        }
        if (!empty($data['item_list'])) {
            foreach ($data['item_list'] as $d) {
                $node = array(
                    'id' => $pid . '_' . $i++,
                    'pId' => $pid == '' ? 0 : $pid,
                    'uuid' => $agentUUID,
                    "name" => htmlspecialchars_decode($d['item_name']),
                    "title" => htmlspecialchars_decode($d['item_name']),
                    'dir_path' => $d['item_path'],
                    "isParent" => true,
                    "nocheck" => false,
                    'icon' => $icon,
                    'iconOpen' => $iconOpen,
                    'iconClose' => $iconClose,
                    "type" => $d['item_type'],
                    "more" => false,
                    'noRemoveBtn' => true,
                    'isnew' => false, //文件夹是否是最新的新建
                    "new_dir_create" => $flag['UNSET'], //文件夹是否是新建的
                    'noEditBtn' => true,
                    'code_type' => intval($d['code_type']),
                    // 'event_type' => 'agent'
                    //                 "icon" => "./img/vm/host.png",
                );
                $tree[] = $node;
            }
        }

        if (intval($data['is_search_finish']) == $flag['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => $pid . '_' . $i++,
                'pId' => $pid == '' ? 0 : $pid,
                'uuid' => $agentUUID,
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'icon' => './img/fs/wenjianjia.png',
                'iconOpen' => './img/fs/wenjianjiaopen.png',
                'iconClose' => './img/fs/wenjianjia.png',
                'isParent' => false,
                'nocheck' => true,
                'more' => true,
                'next_index' => $data['current_next_index'],       //从哪个位置开始加载
                'search_file_name' => $data['search_file_name'],          //从哪个目录开始加载
                'dir_path' => $dir,                                  //当前目录名
                'noRemoveBtn' => true,
                'isnew' => false, //文件夹是否是最新的新建
                "new_dir_create" => $flag['UNSET'], //文件夹是否是新建的
                'noEditBtn' => true,
                // 'event_type' => 'agent'
            );
            $tree[] = $more;
        }

        return $tree ?? [];
    }

    /**
     * 创建恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createRecoverJob(array $params)
    {
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $taskname = htmlspecialchars_decode($params['job_name']);
        $moduletypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $subModuleTypeArr = xphp_get_config('module', 'SUBMODULE_TYPE');
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        if( $params['recoveryType'] == $subModuleTypeArr['NAS'] ){
            $moduletype = $moduletypeArr['NAS'];
        }else{
            $moduletype = $moduletypeArr['FS'];
        }
        $recoveryposition = intval($params['recoverInfo']['pathType']);
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
        //private params
        //disk or file
        $pfMSg['recovery_level'] = 1;
        $pfMSg['submodule_type'] = $params['recoveryType'];
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverFileList($params['pointInfo'], $params['recoverInfo']);
        $pfMSg['nas_uuid'] = $params['recoverInfo']['agentUUID'];
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedInfo']);
        $pfMSg['password'] = $params['recoverInfo']['password'];
        $pfMSg['thread_num'] = $params['thread_num'];
        $pfMSg['new_dir_create'] = $params['recoverInfo']['new_dir_create'];
        //是否是跨平台传输
        $pfMSg['cross_platform_transform'] = $params['recoverInfo']['cross_platform_transform'];
        $pfMSg['same_file_strategy'] = intval($params['highInfo']['same_file_strategy']);
        $pfMSg['dir_tree_recovery_flag'] = v1_parse_bool_to_flag($params['highInfo']['dir_tree_recovery_flag']);
        $pfMSg['link_file_pass_flag'] = v1_parse_bool_to_flag($params['highInfo']['link_file_pass_flag']);
        $pfMSg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']);
        $pfMSg['distinct_flag'] = intval($params['distinct_flag']);
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
        //前后置脚本
        $pfMSg['object_task_config'] = [$params['object_task_config']];
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['pointInfo']['pointUUID']);
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$params['pointInfo']['pointUUID']]);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }
        $msg = json_encode($pfMSg);
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($taskname);
            } else {
                $startResult = true;
            }
            //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加

            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到恢复的文件列表信息
     * @param array $pointInfo   参数
     *                           agentUUID
     *
     *                           恢复源代理UUID
     *                           pointUUID
     *
     *                           恢复时间点
     *                           fileInfo
     *
     *                           array
     *                           [类型,路径,名字,MD5high,
     *                           MD5low]
     * @param array $recoverInfo 参数
     *                           type        恢复类型    原机1/异机2
     *                           agentUUID   恢复目标UUID    原机就是原机的UUID,异机就是异机的UUID
     *                           pathtype    恢复路径类型      原路径1/新路径2
     *                           path        恢复新路径
     * @return array
     */
    private function getRecoverFileList($pointInfo, $recoverInfo): array
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
            );
        }
        return $files;
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
        $data = $this->dbSelect(
            $sql,
            array(
                $taskName,
                xphp_get_config('task', 'TASKTYPE')['RECOVERY']
            )
        );
        if (!$data) {
            return false;
        }

        //调用系统统一启动任务接口.不重新写
        $result = (new FileJobController())->startJob(
            $data[0]['task_uuid'],
            xphp_get_config('task', 'BACKUP_MODE')['FULL']
        );

        //这里直接返回成功或失败 bool
        return $result[0];
    }

    /**
     * 得到文件恢复任务名
     * @param unknown $params
     */
    public function getFileRecoverTaskName($params)
    {
        $taskName = '';
        switch ($params['sub_module_type']) {
            case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']:
                $taskName = xphp_get_lang('WEB_FILE_RECOVER_TASKNAME');
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']:
                $taskName = xphp_get_lang('UI_NAS_RESTORE_TASK');
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']:
                $taskName = xphp_get_lang('WEB_HADOOP_RECOVERY_TASK_NAME');
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']:
                $taskName = xphp_get_lang('WEB_OBS_RECOVER_TASKNAME');
                break;
        }
        return FileBackUp::instance()->getValidTaskName($taskName);
    }

     /**
     * 递归查询增量备份时间点
     *
     * @param [type] $depend_point_uuid
     * @return void
     */
    private function getSearchIncTimepoint($depend_point_uuid)
    {
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,bbt.integrity_check_flag, bbt.operation_status, bbt.merge_status,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid, bsr.storage_type,
                   bsi.virus_scan_status, bsi.integrity_check_status
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
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,bbt.integrity_check_flag, bbt.operation_status, bbt.merge_status,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid, bsr.storage_type,
                   bsi.virus_scan_status, bsi.integrity_check_status 
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

}