<?php

namespace app\v1\file\v0\logic;

use app\v1\backupData\v0\logic\DataManage;
use app\v1\common\logic\Backup;
use app\v1\common\logic\Recover;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;
use app\v1\common\logic\JobInfo as JobInfos;
use xphp\BLLHandler;
use app\v1\opcode\NodeOpcode;
use app\v1\resources\v0\logic\Index as Resources;
use app\v1\tenant\v0\logic\Index as TenantIndex;

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
        ];
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg), true, true);
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
     * 得到文件别分时间点树(灾备中心/文件备份数据)
     * @param array $params 参数
     * @return array
     */
    public function getFileDataTree(array $params): array
    {
        $dataflag = $params['data_flag'];
        $storageUuid = $params['storage_uuid'];
        $sql = "select bbt.real_node_uuid, bsr.node_uuid,bsr.storage_type, bbt.id, bbt.module_type, bbt.task_type, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.src_data_deleted_flag, 
		              bbt.user_uuid, bbt.user_name, fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.source_agent_type as os_type, bbt.task_name
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and 
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
                       and bbt.import_flag = ? and
	                   bbt.module_type = " . xphp_get_config('module', 'MODULE_TYPE')['FS'] . " and bbt.sub_module_type = " . xphp_get_config('module', 'SUBMODULE_TYPE')['FS'] . " and
	                   bbt.data_local_flag = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = new TenantIndex();
        $tenantMangerFlag = (new User())->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty(xphp_get_user_info()['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings(xphp_get_user_info()['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($dataflag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser(xphp_get_user_info()['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                // 权限重构
                $authUser = $_SESSION['authUser']['fileprotect_look'] ?? [];
                if ($authUser) {
                    $userUuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                    $userUuids = "('" . implode("','", $userUuidArr) . "')";
                    $sql .= " and bbt.user_uuid IN $userUuids ";
                } else {
                    $sql .= " and bbt.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(xphp_get_user_info()['userUuid']));
                }
            }
        }

        if (empty(xphp_get_user_info()['tenantuuid'])) {// 非租户用户不能查看租户的资源
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

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        foreach ($data as $d) {
            if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT'] && $dataflag)
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
                $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                // 副本数据
                if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] || $d['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY_FETCH']) {
                    $name = $taskName . "(" . xphp_get_lang('UI_COPY_DATA') . ")";
                }
                // 归档数据
                if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] || $d['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']) {
                    $name = $taskName . "(" . xphp_get_lang('UI_ARCHIVE_DATA') . ")";
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != xphp_get_user_info()['userUuid']) {
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
        return $node;
    }

    /**
     * 校验文件数据加密密码正确性
     * @param array $params 参数
     * @return array|string
     */
    public function checkFSEncryptPass(array $params)
    {
        $timepointuuid = $params['timepoint_uuid'];
        $inputpass = xphp_decrypt_js($params['passwd']);
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
        $start = $params['offset'];
        $number = $params['limit'] ?? 10;
        $path = $params['path'] ?? '';
        $md5Flag = $params['md5_flag'];
        $md5High = $params['md5_high'] ?? '';
        $md5Low = $params['md5_low'] ?? '';
        $pid = $params['pid'] ?? 0;
        $sclass = $params['sclass'];
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
            $fileType = xphp_get_config('file', 'FILETYPE');
            $fileList[] = array(
                'id' => $d['path'],
                'pid' => $pid,
                'name' => $name ?? '',
                'nocheck' => false,
                'title' => $name ?? '',
                'path' => $d['path'],
                'btype' => $d['type'],   //文件类型
                'is_parent' => !($d['type'] == $fileType['FILE']),
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
            // $list['filelist'] = $more;
        }

        return $list;
    }

    /**
     * 异步获取文件时间点
     * @param array $params 参数
     * @return string
     */
    public function getSyncFileTimepoint(array $params)
    {
        $recoverflag = $params['recover_flag']; //文件恢复加载时间点
        $taskuuid = $params['job_uuid'];
        $agentuuid = $params['agent_uuid'];
        $id = $params['id'];
        $storageUuid = $params['storage_uuid'];
        $dataflag = $params['data_flag']; //备份数据的树形结构
        $chkDisabled = false;
        $sql = "select bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.encrypted_flag,bbt.remarks,bbt.detail,bbt.src_data_deleted_flag,
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, bbt.task_name,fbt.detail as fbtdetail 
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
                        unset($unfullList[$key]);
                        $unfullList = array_values($unfullList);

                        $unfullCountTmp--;
                    }
                    continue;
                }
            }
            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;
        }
        $node = array();
        $jobs = new JobInfo();
        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $fbtdetail = json_decode($d['fbtdetail'], true);
            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) . " (" . $jobs->getTimepointTypeDes($d['backup_mode']) . ")";
            $title = $name;
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
            if ($dataflag) {
                //添加GFS标识
                $mark = $mark . $jobs->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
                $gfsforever = $mark;
                $chkDisabled = true;
            }
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
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
                    "agent_name" => $d['agent_name'],
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                    "icon" => $jobs->getTimepointIcon($d['backup_mode']),
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
                "agent_name" => $d['agent_name'],
                "agent_ip" => $d['agent_ip'],
                "agent_uuid" => $d['agent_uuid'],
                "task_name" => $d['task_name'],
                "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                "mode" => intval($d['backup_mode']),
                "timepointuuid" => $d['timepoint_uuid'],
                "taskuuid" => $taskuuid,
                "nodeuuid" => $nodeUuid,
                "chkDisabled" => $chkDisabled,
                "storagename" => $d['storage_nickname'],
                'timepoint' => $this->parseDate($d['timepoint']),
                "encrypted_flag" => $encrypted_flag,
                "ostype" => $fbtdetail['os_type'],
                "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                "storage_type" => $d['storage_type'],
            );
        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return $msg;
         
    }

    /**
     * 获取恢复任务文件备份树
     * @param array $params 参数
     * @return array
     */
    public function getRecoverHostTree($params = array()): array
    {
        $sql = "select ba.id, ba.agent_uuid,ba.os_type, ba.agent_name, ba.hostname,ba.net_model, ba.ip,
                ba.os_type,online_flag, ba.authorization_module,bag.group_uuid, bag.group_name, bag.detail
                from bd_agent ba 
                left join bd_agent_group bag on ba.group_uuid = bag.group_uuid  
                where ba.agent_type not in (3, 4, 5)";
        //获取当前用户拥有的agent
        if(v1_auth_need_operation()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " and ({$resourceUuidSql})";
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
                        'name' => htmlspecialchars_decode($name),
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
     * 获取恢复任务文件客户端下的目录树
     * @param $params
     * @return array|string
     */
    public function getRecoverPathTree($params)
    {
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'] ?? '';
        $dir = $params['dir'] ?? '';
        $pid = $params['pid'] ?? 0; //父节点ID
        $agentUUID = $params['agent_uuid'];

        $this->paramsCheck($agentUUID);

        $codetype = !empty($params['code_type']) ? $params['code_type'] : 2; // 编码类型
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
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
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);

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

        if (!empty($data['item_list'])) {
            foreach ($data['item_list'] as $d) {
                $node = array(
                    'id' => $pid . '_' . $i++,
                    'pid' => $pid == '' ? 0 : $pid,
                    'uuid' => $agentUUID,
                    "name" => $d['item_name'],
                    "title" => $d['item_name'],
                    'dir_path' => $d['item_path'],
                    "isParent" => true,
                    "nocheck" => false,
                    'icon' => './img/fs/wenjianjia.png',
                    'iconOpen' => './img/fs/wenjianjiaopen.png',
                    'iconClose' => './img/fs/wenjianjia.png',
                    "type" => $d['item_type'],
                    "more" => false,
                    'noRemoveBtn' => true,
                    'isnew' => false, //文件夹是否是最新的新建
                    "new_dir_create" => $flag['UNSET'], //文件夹是否是新建的
                    'noEditBtn' => true,
                    'code_type' => intval($d['code_type']),
                    'event_type' => 'agent'
                    //                 "icon" => "./img/vm/host.png",
                );
                $tree[] = $node;
            }
        }

        if (intval($data['is_search_finish']) == $flag['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => $pid . '_' . $i++,
                'pid' => $pid == '' ? 0 : $pid,
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
                'event_type' => 'agent'
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
        $taskname = htmlspecialchars_decode($params['job_name']);
        $moduletypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        $moduletype = $moduletypeArr['FS'];
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
        $pfMSg['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['FS'];
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverFileList($params['pointInfo'], $params['recoverInfo']);
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
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, $msg);
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
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect(
            $sql,
            array(
                $taskName,
                xphp_get_config('module', 'MODULE_TYPE')['FS'],
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
        return FileBackUp::instance()->getValidTaskName(xphp_get_lang('WEB_FILE_RECOVER_TASKNAME'));
    }

}