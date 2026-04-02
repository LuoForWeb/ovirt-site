<?php

namespace app\v1\nas\v0\logic;

use app\v1\backupData\v0\logic\DataManage;
use app\v1\common\logic\Backup;
use app\v1\common\logic\Recover;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;
use xphp\BLLHandler;
use app\v1\tenant\v0\logic\Index as TenantHandler;

/**
 * note          NAS 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasRecover extends Recover
{
    /**
     * 得到nas时间点树
     * @param array $params 参数
     * @return array
     */
    public function getNasDataTree(array $params)
    {
        $dataflag = $params['data_flag'];
        $storageUuid = $params['storage_uuid'];
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $sql = "SELECT 
                    bbt.real_node_uuid, bsr.node_uuid, bsr.storage_type, bbt.id, bbt.module_type, 
                    bbt.task_type, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, 
                    bbt.task_uuid, bbt.importance_flag, bbt.src_data_deleted_flag, bbt.user_uuid, bbt.user_name, 
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, bbt.task_name, fbt.detail
                FROM 
                    bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                WHERE 
                    bbt.timepoint_uuid = fbt.fs_timepoint_uuid AND 
        			bbt.storage_uuid = bsr.storage_uuid AND
                    bbt.deleted_flag = ? AND 
                    bbt.available_flag = ? AND 
                    bbt.import_flag = ? AND
	                bbt.module_type = " . xphp_get_config('module', 'MODULE_TYPE')['NAS'] . " AND
	                bbt.data_local_flag = ?";

        $flag = xphp_get_config('app', 'FLAG');
        $userUUID = xphp_get_user_info()['userUuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);

        //租户管理员特殊处理数据显示
        $tenantHandler = new TenantHandler();
        $tenantMangerFlag = (new User())->pCheckTenantManager();

        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($_SESSION['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源数据
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源数据
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['nas_protect']
            );
            $sql .= " and bbt.user_uuid in ({$userUuidSql}) ";
        }

        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], xphp_get_config('module', 'MODULE_TYPE')['NAS']));
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

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID(11);

        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $detail = json_decode($d['detail'], true);
            $nasuuid = $detail['nas_uuid'];
            $nastype = $detail['nas_type'];
            //图标区分不同类型
            if ($nastype == 6) {
                $icon = "./img/nas/nfs.png";
            } else if ($nastype == 7) {
                $icon = "./img/nas/cifs.png";
            }
            if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT'] && $dataflag)
                continue;
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                // 副本数据
                if ($d['task_type'] == $taskType['BACKUP_COPY'] || $d['task_type'] == $taskType['BACKUP_COPY_FETCH']) {
                    $name = $taskName . "(" . xphp_get_lang('UI_COPY_DATA') . ")";
                }
                // 归档数据
                if ($d['task_type'] == $taskType['ARCHIVE'] || $d['task_type'] == $taskType['ARCHIVE_FETCH']) {
                    $name = $taskName . "(" . xphp_get_lang('UI_ARCHIVE_DATA') . ")";
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != Xphp::$_user['useruuid']) {
                    $name .= "(" . $d['user_name'] . ")";
                }
                $node[] = array(
                    "id" => $d['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "task_name" => $taskName,
                    "open" => true,
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "nodeuuid" => $nodeUuid,
                    "taskuuid" => $d['task_uuid'],
                    "isParent" => true,
                    "nasuuid" => $nasuuid,
                    "agentuuid" => $d['agent_uuid'],
                    "storage_uuid" => $storageUuid,
                );
            }
            //检查并添加agent
            if (!in_array($nasuuid . "_" . $d['task_uuid'], $agent)) {
                $agent[] = $nasuuid . "_" . $d['task_uuid'];
                $node[] = array(
                    "id" => $nasuuid . "_" . $d['task_uuid'],
                    "pId" => $d['task_uuid'],
                    'name' => $this->getNasNameStr($nasuuid, $d['agent_name'], $d['agent_ip']),
                    "title" => $this->getNasNameStr($nasuuid, $d['agent_name'], $d['agent_ip']),
                    // "open" => true,
                    "nocheck" => !$dataflag,
                    "type" => 2,
                    "clickshow" => true,
                    "icon" => $icon,
                    "nodeuuid" => $nodeUuid,
                    "taskuuid" => $d['task_uuid'],
                    "nasuuid" => $nasuuid,
                    "agentuuid" => $d['agent_uuid'],
                    "isParent" => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    "storage_uuid" => $storageUuid,
                );
            }

        }

        return $node;
    }

    /**
     * 得到nas恢复文件树
     * @param array $params 参数
     * @return string|array
     */
    public function getRecoveryNasDir(array $params)
    {

        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = intval($params['root_flag']);
        $start = intval($params['offset']) ?? 0;
        $number = intval($params['limit']) ?? 10;
        $path = $params['path'] ?? '';
        $md5Flag = intval($params['md5_flag']) ?? '';
        $md5High = $params['md5_high'] ?? '';
        $md5Low = $params['md5_low'] ?? '';
        $pid = $params['pid'] ?? '';
        $sclass = $params['sclass'] ?? '';

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

        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        $mbResult = $this->service()->mbFSMsgs($opName, $nodeuuid, json_encode($msg), true);
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
                    $name = substr($d['path'], 0, strrpos($d['path'], '/'));
                    break;
                default:
                    break;
            }
            $filename = $d['filename'];
            $fileList[] = array(
                // $filename,      //文件名
                // $filesize,      //文件大小
                'id' => $d['path'],
                'pid' => $pid,
                'name' => $name,
                'nocheck' => false,
                'title' => $name,
                'path' => $d['path'],
                'btype' => $d['type'],   //文件类型
                'is_parent' => !($d['type'] == xphp_get_config('file', 'FILETYPE')['FILE']),
                'icon' => $icon ?? '',
                'iconOpen' => $d['type'] == 2 ? './img/fs/wenjianjiaopen.png' : $icon,
                'iconClose' => $d['type'] == 2 ? './img/fs/wenjianjia.png' : $icon, // 从后台获取的文件类型
                'isfile' => $d['type'] == xphp_get_config('file', 'FILETYPE'),   //是否是文件
                'sclass' => $this->getFileClassName($d['type'], $filename, $sclass),                //显示类型
                'md5_flag' => $d['md5_flag'],
                'md5_high' => $d['md5_high'],
                'md5_low' => $d['md5_low'],
                "create_time" => $d['create_time'],
                'modify_time' => $d['modify_time'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'more' => false,
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
                'md5_low' => $md5Low,
                'filepath' => '',                            //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            array_push($list['file_list'], $more);
            // $list['filelist'] = $more;
        }

        return $list;
    }

    /**
     * 异步获取nas时间点
     * @param array $params 参数
     * @return array
     */
    public function getSyncNasTimepoint(array $params)
    {
        $recoverflag = $params['recover_flag']; //文件恢复加载时间点
        $taskuuid = $params['job_uuid'];
        $id = $params['id'];
        $nodeuuid = $params['node_uuid'];
        $dataflag = $params['data_flag'];//备份数据的树形结构
        $agentuuid = $params['agent_uuid'];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $chkDisabled = false;
        $sql = "select bsr.storage_nickname, bsr.node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid,
                       unix_timestamp(bbt.timepoint) timepoint,bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,
                       bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag,
		              fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as nasdetail  
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type in (" . $moduleType['NAS'] . ' , ' . $moduleType['BACKUP_COPY_CLIENT'] . ') and
	                    bbt.task_uuid = ? and bbt.data_local_flag = ? and fbt.agent_uuid = ?';

        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $taskuuid, $flag['SET'], $agentuuid);
        if (!empty($nodeuuid)) {
            $sql .= ' and bsr.node_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }

        $sql .= " order by bbt.task_uuid, bbt.timepoint";
        $data = $this->dbSelect($sql, $sqlParams);
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($data as $point) {
            if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
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

                        if (!empty($unfullList[$key])) {
                            unset($unfullList[$key]);
                            $unfullList = array_values($unfullList);
                            $unfullCountTmp--;
                        }
                    }
                    continue;
                }
            }
            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) {
                break;
            }
        }
        $node = array();

        foreach ($data as $d) {
            $detail = json_decode($d['detail'], true);
            $nasdetail = json_decode($d['nasdetail'], true);
            $nasuuid = $nasdetail['nas_uuid'];
            $nasname = $d['agent_ip'] . '(' . $d['agent_name'] . ')';
            $name = $this->parseDate($d['timepoint']) .
                ' (' . (new JobInfo())->getTimepointTypeDes($d['backup_mode']) . ')';
            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) {
                // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }

            $mark = '';
            if ($dataflag) {
                //添加GFS标识
                $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
                $gfsforever = $mark;
                if ($d["src_data_deleted_flag"] == 1) {//归档开启
                    $mark = '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')' . $mark;
                }
                $chkDisabled = true;
            }

            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $node[] = array(
                    'id' => $d['timepoint_uuid'],
                    'pid' => $id,
                    'name' => $name . $mark,
                    'checked' => false,
                    'type' => 3,
                    'nocheck' => false,
                    'oldname' => $name,
                    'nas_uuid' => $nasuuid,
                    'nas_name' => $nasname,
                    'gfsforever' => $gfsforever,
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'depend_point_uuid' => $d['depend_point_uuid'],
                    'agent_name' => $d['agent_name'],
                    'agent_ip' => $d['agent_ip'],
                    'job_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'icon' => $this->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'job_uuid' => $taskuuid,
                    'node_uuid' => $d['node_uuid'],
                    'storage_nickname' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'encrypted_flag' => $encryptedflag,
                    'os_type' => $nasdetail['nas_type'],
                    'password_auto_flag' => $detail['password_auto_flag'] == 1,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1,
                );
                continue;
            }
            $node[] = array(
                'id' => $d['timepoint_uuid'],
                'pid' => $fulluuidList[$timepointuuid],
                'name' => $name . $mark,
                'checked' => false,
                'type' => 4,
                'oldname' => $name,
                'nas_uuid' => $nasuuid,
                'nas_name' => $nasname,
                'gfsforever' => $gfsforever,
                'nocheck' => $recoverflag,
                'timepoint_uuid' => $d['timepoint_uuid'],
                'depend_point_uuid' => $d['depend_point_uuid'],
                'agent_name' => $d['agent_name'],
                'agent_ip' => $d['agent_ip'],
                'job_name' => $d['task_name'],
                'star' => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                'icon' => $this->getTimepointIcon($d['backup_mode']),
                'mode' => intval($d['backup_mode']),
                'job_uuid' => $taskuuid,
                'node_uuid' => $d['node_uuid'],
                'chk_disabled' => $chkDisabled,
                'storage_nickname' => $d['storage_nickname'],
                'timepoint' => $this->parseDate($d['timepoint']),
                'encrypted_flag' => $encryptedflag,
                'os_type' => $nasdetail['nas_type'],
                'password_auto_flag' => $detail['password_auto_flag'] == 1,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1,
            );
        }

        return $node;
    }

    /**
     * 得到nas设备下的恢复文件目录树 
     * @param array $params 参数
     * @return array|string
     */
    public function getRecoverPathTree(array $params)
    {
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agent_uuid'];
        $pid = $params['pid'];          //父节点ID

        $this->paramsCheck($agentUUID);

        $codetype = !empty($params['code_type']) ? $params['code_type'] : 2; // 编码类型
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
        $startAfter = $params['startAfter'];

        $msg = array(
            'agent_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $codetype,
            'module_type' => $moduleType,
            'start_after' => $startAfter
        );

        $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        // 获取nasuuid
        $nasUuid = explode('/', $dir)[3];
        $nodeInfo = $this->getMountNode($nasUuid);
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
        $result = $mbResult['result'];

        //返回结果到UI
        if (empty($result)) {
            //失败
            $nodeOpcode = new NodeOpcode();
            $operate = $nodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        $data = $mbResult['msg'];
        $i = 0;
        $flag = xphp_get_config('app', 'FLAG');

        if (!empty($data['item_list'])) {
            foreach ($data['item_list'] as $d) {
                $node = array(
                    'id' => $pid . '_' . $i++,
                    'pId' => $pid == '' ? 0 : $pid,
                    'uuid' => $agentUUID,
                    'name' => htmlspecialchars_decode($d['item_name']),
                    'title' => htmlspecialchars_decode($d['item_name']),
                    'dir_path' => $d['item_path'],
                    'isParent' => true,
                    'nocheck' => false,
                    'icon' => './img/fs/wenjianjia.png',
                    'iconOpen' => './img/fs/wenjianjiaopen.png',
                    'iconClose' => './img/fs/wenjianjia.png',
                    'type' => $d['item_type'],
                    'more' => false,
                    'noRemoveBtn' => true,
                    'isnew' => false,//文件夹是否是最新的新建
                    "new_dir_create" => $flag['UNSET'],//文件夹是否是新建的
                    'noEditBtn' => true,
                    //                 "icon" => "./img/vm/host.png",
                    'code_type' => intval($d['code_type']),
                    'event_type' => 'nas'
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
                'isnew' => false,//文件夹是否是最新的新建
                "new_dir_create" => $flag['UNSET'],//文件夹是否是新建的
                'noEditBtn' => true,
                'event_type' => 'nas'
            );
            $tree[] = $more;
        }

        return $tree ?? [];
    }

    /**
     * 得到所有挂载成功节点和节点的agent_uuid，用于备份第一步扫描目录 消息发往各个节点
     * @param unknown $params
     */
    public function getMountNode($nasuuid)
    {
        $sql = 'select node_uuid, agent_uuid from nas_mount_list where nas_uuid = ?';
        $data = $this->dbSelect($sql, array($nasuuid));
        $nodeList = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $nodeList[] = array(
                    'node_uuid' => $d['node_uuid'],
                    'agent_uuid' => $d['agent_uuid'],
                );
            }

        }
        return $nodeList;
    }

    /**
     * 创建恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createRecoverJob($params)
    {
        $taskname = htmlspecialchars_decode($params['job_name']);

        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
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
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['RECOVERY'];
        //private params
        //disk or file
        $pfMSg['recovery_level'] = 1;
        $pfMSg['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['NAS'];
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverNasList($params['pointInfo'], $params['recoverInfo']);
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedInfo']);
        $pfMSg['password'] = $params['recoverInfo']['password'];
        $pfMSg['thread_num'] = $params['thread_num'];
        $pfMSg['nas_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['new_dir_create'] = $params['recoverInfo']['new_dir_create'];
        //是否是跨平台传输
        $pfMSg['cross_platform_transform'] = $params['recoverInfo']['cross_platform_transform'];
        $pfMSg['same_file_strategy'] = intval($params['highInfo']['same_file_strategy']);
        $pfMSg['dir_tree_recovery_flag'] = v1_parse_bool_to_flag($params['highInfo']['dir_tree_recovery_flag']);
        $pfMSg['link_file_pass_flag'] = v1_parse_bool_to_flag($params['highInfo']['link_file_pass_flag']);
        $pfMSg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']);
        $pfMSg['distinct_flag'] = intval($params['distinct_flag']);
        //重试策略
        $pfMSg['retry_strategy'] = (new Backup())->groupRetryStrategy($params['retry_strategy']);
        //忽略节点资源限制
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['highInfo']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMSg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy']);
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';

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
        $mbResult = $this->service()->mbFSMsgs($opName, $nodeuuid, $msg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
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
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建搜索消息
     * @param array $params
     * @return array
     */
    public function createSearchJob(array $params): array
    {
        $opName = 'FS_OP_TYPE_SEARCH_THREAD_CREATE';

        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);

        $msg = [
            'search_mode' => $params['search_mode'],
            'md5_list' => $params['md5_list'],
            'path_name' => $params['path_name'],
            'timepoint_uuid' => $params['timepoint_uuid'],
            'req_total_num' => $params['req_total_num'],
            'module_type' => $params['module_type'],
            'submodule_type' => $params['sub_module_type']
        ];

        $mbResult = $this->service()->mbFSMsgs($opName, $nodeuuid, json_encode($msg), true, true);

        if ($mbResult['result']) {
            $info = [
                'result' => true,
                'msg' => $mbResult['msg']['task_uuid'],
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
     * 获取搜索结果
     * @param array $params
     * @return array|string
     */
    public function getSearchInfo(array $params)
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
        foreach ($result['item_list'] as $file) {
            if ($file['file_type'] == 1) {
                // 文件
                $titleDes = xphp_get_lang('WEB_FILE_FILE_SIZE') . v1_calsize($file['file_size'], true)
                    . PHP_EOL . xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($file['modify_time']);
            } else {
                $titleDes = xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($file['modify_time']);
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
                'is_arent' => $file['file_type'] != 1, //1文件  2文件夹
                'path' => $file['path_name'],
                'point_uuid' => $params['timepoint_uuid'],
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
     * 停止搜索
     * @param array $params
     * @return array|true[]
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
     * 得到恢复的nas列表信息
     * @param array $pointInfo   时间点信息
     *                           agentUUID 恢复源代理UUID
     *                           pointUUID 恢复时间点
     *                           fileInfo array
     *                           [类型,路径,名字,MD5high,MD5low]
     * @param array $recoverInfo 恢复信息
                                 type 恢复类型   原机1/异机2
                                 agentUUID   恢复目标UUID    原机就是原机的UUID,异机就是异机的UUID
                                 pathtype    恢复路径类型      原路径1/新路径2
                                 path       恢复新路径
     * @return array
     */
    private function getRecoverNasList(array $pointInfo, array $recoverInfo): array
    {
        $fileInfo = $pointInfo['fileInfo'];
        $files = [];
        $flag = xphp_get_config('app', 'FLAG');
        foreach ($fileInfo as $f) {
            $pathtype = intval($recoverInfo['pathType']);
            $newRootPath = '';
            if ($pathtype == $flag['UNSET']) {
                //异机恢复
                $newRootPath = $recoverInfo['path'];
                $agentUUID = $recoverInfo['agentUUID'];
            }
            $files[] = [
                'agentUUID' => $agentUUID ?? '',
                'path_type' => intval($f[0]),
                'path_name' => $f[1],
                'new_root_path' => $newRootPath,
                'recovery_timepoint_uuid' => $pointInfo['pointUUID'],
                'md5_high' => $f[3],
                'md5_low' => $f[4],
                'code_type' => $recoverInfo['code_type'],
            ];
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
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type = ? and bt.task_type = ?";

        $data = $this->dbSelect(
            $sql,
            array(
                $taskName,
                xphp_get_config('module', 'MODULE_TYPE')['NAS'],
                xphp_get_config('task', 'TASKTYPE')['RECOVERY']
            )
        );
        if (!$data) {
            return false;
        }

        //调用系统统一启动任务接口.不重新写
        $result = (new NasJobController())->startJob(
            $data[0]['task_uuid'],
            xphp_get_config('task', 'BACKUP_MODE')['FULL']
        );
        // $result = json_decode($result, true);
        //这里直接返回成功或失败 bool
        return $result;
    }

    /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示share_path,如果别名和IP一样则显示share_path
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param string $nasuuid 时间点中的uuid
     * @param string $fbtname 时间点中的主机名
     * @param string $ip      时间点中的ip
     * @return string name(ip)
     */
    public function getNasNameStr(string $nasuuid, $fbtname, $ip): string
    {
        $sql = "select nas_nickname, share_path from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nasuuid));
        if (!empty($result)) {
            if ($result[0]['nas_nickname'] == $ip) {
                return $ip . '(' . $result[0]['share_path'] . ')';
            } else {
                return $ip . '(' . $result[0]['nas_nickname'] . ')';
            }
        } else {
            return $ip . '(' . $fbtname . ')';
        }
    }
}