<?php

namespace app\v1\fileback\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\JobInfo;
use app\v1\hadoop\v0\logic\HadoopBackUp;
use app\v1\nas\v0\logic\NasBackUp;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Client;
use app\v1\resources\v0\logic\Node;
use app\v1\s3\v0\logic\ObsBackup;
use app\v1\tenant\v0\logic\Tenant;

/**
 * note          文件相关模块备份管理 logic
 * @author       wuxian@vinchin.com
 * @date         2025/7/14 16:18
 * @copyright    Copyright 2025 vinchin.com
 */
class FileBackUp extends Backup
{
    
    /**
     * 创建备份任务
     * @param array $params 参数
     * @return string
     */
    public function createBackupJob($params = [])
    {
        $pfMsg = array();
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        if (!empty($params['job_uuid'])) {
            $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        }
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $nasUuid = '';
        if ($params['submodule_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']) {
            $moduleType = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
            $nasUuid = $params['nas_uuid'];
        }
        $strategyGroupUuid = $params['strategy_group_uuid'] ?? '';
        //租户内检查可用数量是否超过授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            (new Tenant())->checkTenantAuth($moduleType, $params['host_list'], '');
        }
        //时间策略
        $timeStrategy = $this->newGroupBackupTimeList($params['strategy_info']['time'], $strategyGroupUuid);
        //保留策略
        $reserverStrategy = $this->groupReserverStrategy($params['strategy_info']['reserve']['reserveInfo']);
        //传输策略
        $transportStrategy = $this->groupTransportStrategy($params['transfer_info']);
        //存储策略
        $storageStrategy = $this->groupStorageStrategy($params['strategy_info']['store']['storeInfo']);
        //节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['node_info']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            htmlspecialchars_decode($params['job_name']),
            $moduleType,
            $timeStrategy,
            $reserverStrategy,
            $transportStrategy,
            $storageStrategy,
            $nodeInfo,
            $params['strategy_info']['time']['type']
        );
        if (!empty($params['job_uuid'])) {
            $pfMsg['task_uuid'] = $params['job_uuid'];
        }
        //nasuuid
        $pfMsg['nas_uuid'] = $nasUuid;
        //任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $params['fs_path_list'];
        //快照
        $pfMsg['snap_shot_flag'] = v1_parse_bool_to_flag($params['high_info']['snapshot']);
        //传输线程
        $pfMsg['thread_num'] = $params['transfer_info']['thread_num'];
        //扫描线程
        $pfMsg['scan_thread_num'] = $params['transfer_info']['scan_thread_num'];
        //扫描文件速度
        $pfMsg['scan_file_num'] = $params['transfer_info']['scan_file_num'];
        //通配符
        $pfMsg['wildcard_list'] = $params['wildcard_list'];
        // 限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['strategy_info']['speedlimit']);
        //得到全局策略uuid,没有传空值
        $pfMsg['strategy_group_uuid'] = $strategyGroupUuid;
        //归档
        $pfMsg['file_archive_flag'] = v1_parse_bool_to_flag($params['high_info']['file_archive_flag']);
        //文件权限备份
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['high_info']['permission']); 
        //跳过文件告警开关
        $pfMsg['skip_file_alarm_flag'] = v1_parse_bool_to_flag($params['high_info']['abnormal']); 
        //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_num'] = $params['high_info']['skip_file_alarm_min_num']; 
        //跳过文件告警比例
        $pfMsg['skip_file_alarm_min_ratio'] = $params['high_info']['skip_file_alarm_min_ratio'];
        //忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_info']['ignore_resource_limiting_flag']); 
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy'], $params['high_info']['node']['storage_uuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $this->groupRetryStrategy($params['high_info']['retry_strategy']);
        //gfs策略
        $pfMsg['gfs_strategy_item_list'] = $params['strategy_info']['reserve']['reserveInfo']['gfs_strategy_item_list'];
        $pfMsg['submodule_type'] = $params['submodule_type'];
        $pfMsg['group_list'] = $this->getGroupList($pfMsg['fs_path_list']);
        //前后置脚本
        $pfMsg['object_task_config'] = $params['object_task_config'];
        if ($params['submodule_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']
        || $params['submodule_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']) {
            $pfMsg['appliance_uuid'] = $params['appliance_uuid'];
            $pfMsg['agent_uuid'] = $params['appliance_uuid'];
            $pfMsg['agent_pool_uuid'] = $params['appliance_pool_uuid'];
        }
        $nodeUuid = $pfMsg['node_uuid'];
        if (!$pfMsg['node_uuid']) {
            $nodeUuid = Node::instance()->getMasterNodeUuid();
        }
        $mbResult = $this->service()->mbFSMsgs($nodeUuid, $opName, json_encode($pfMsg));
        $result = $mbResult['result'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($result, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到备份的客户端分组列表
     * @param array $fileList 文件列表
     */
    private function getGroupList($fileList)
    {
        $info = array();
        $groupList = array();
        //组合分组和客户端为key、value格式
        foreach ($fileList as $file) {
            if (!isset($groupList[$file['group_uuid']])) {
                $groupList[$file['group_uuid']] = [];
            }
            if (!in_array($file['agent_uuid'], $groupList[$file['group_uuid']])) {
                $groupList[$file['group_uuid']][] = $file['agent_uuid'];
            }
        }
        //组合返回数据
        foreach ($groupList as $groupUuid => $agentList) {
            $info[] = array(
                'group_uuid' => $groupUuid,
                'agent_uuid' => $agentList,
            );
        }
        return $info;
    }

    /**
     * 修改任务/得到备份任务的所有信息
     * @param string $taskUUID 任务uuid
     * @return array
     */
    public function getBackupTaskAllInfo(string $taskUUID)
    {
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid,bt.strategy_group_uuid, bt.agent_uuid, bt.thread_num,
                       ft.level, ft.agent_group_uuid,ft.snap_shot_flag,ft.detail as ftdetail,ft.file_archive_flag,ft.skip_file_alarm_flag,ft.skip_file_alarm_min_num,ft.skip_file_alarm_min_ratio,
                	   ft.permission_operate_flag,brs.strategy_type, brs.number,
                	   bts.encrypt_flag, bts.compress_flag,bts.network_uuid, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
                	   bss.compressed_flag, bss.compress_method, bss.encrypted_flag,bss.password,bss.password_auto_flag, bss.encrypt_method
                from bd_task bt, fs_task ft, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss
                where bt.task_uuid = ft.task_uuid
                and bt.strategy_id = brs.strategy_id
                and bt.strategy_id = bts.strategy_id
                and bt.strategy_id = bss.strategy_id
                and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $sqlwild = 'select group_uuid,agent_uuid,detail from bd_task_agent_list where task_uuid = ?';
        $sqlwilddata = $this->dbSelect($sqlwild, array($taskUUID));
        $info = array();
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
            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
                //代理分组
                'groupList' => $groupuuid,
                'agentList' => $agentInfo[0]['agent_list'],
                'agentOnlineNum' => $agentInfo[0]['agent_online_num'],
                'agentOfflineNum' => $agentInfo[0]['agent_offline_num'],
                //level
                'level' => $data[0]['level'],
                //文件信息
                'fileinfo' => $this->getFileBackupFiles($taskUUID),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid']
                ),
                //保留策略
                'brs' => array(
                    'type' => $data[0]['strategy_type'],
                    'number' => $data[0]['number'],
                    'GFS' => $this->getGfsStrategyInfo($taskUUID),
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    'compress' => v1_parse_flag_to_bool($data[0]['compress_flag']),
                    'network' => $data[0]['network_uuid'],
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                ),
                //存储策略
                'bss' => array(
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' => base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method'])
                ),
                //时间策略
                'timestrategy' => (new JobInfo())->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $this->getSpeedGlobalStrategyInfo($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                    'snap_shot_flag' => v1_parse_flag_to_bool($data[0]['snap_shot_flag']),
                    'wildcardinfo' => $wildcardinfo,
                    'scan_thread_num' => $ftdetail['scan_thread_num'],
                    'scan_file_num' => $ftdetail['scan_file_num'],
                    'file_archive_flag' => $data[0]['file_archive_flag'],
                    'skip_file_alarm_flag' => $data[0]['skip_file_alarm_flag'] == 1 ? true : false,
                    'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
                    'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
                    'permission_operate_flag' => $data[0]['permission_operate_flag'] == 1 ? true : false,
                ),
            );

        }
        return $info;
    }

    

    /**
     * 得到修改文件备份任务客户端树
     * @param array $params 参数
     * @return array
     */
    public function getBackupTreeOldInfo(array $params)
    {
        $agentlist = $params['agent_list'];
        $grouplist = $params['group_list'];

        //增加文件代理分组节点
        $agentGroupList = $this->getAgentGroupBackupTree();
        if (!empty($agentGroupList)) {
            foreach ($agentGroupList as $key => $node) {
                if ($node['eventtype'] == "group") {//分组
                    foreach ($grouplist as $g) {
                        if ($g == $node['id']) {//如果分组被选中
                            $agentGroupList[$key]['checked'] = true;
                            $agentGroupList[$key]['open'] = true;
                        }
                    }
                } else {//客户端
                    foreach ($agentlist as $a) {
                        if ($a == $node['uuid']) {
                            $agentGroupList[$key]['checked'] = true;
                        }
                    }
                }
            }
        }
        return $agentGroupList;
    }

    /**
     * 根据任务ID得到文件备份备份列表信息
     * @param string $taskuuid 任务uuid
     * @return array
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
     * 获取代理分组代理列表信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    private function getAllGroup(string $taskuuid): array
    {
        $sql = "select distinct(group_uuid) from fs_path_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        return !empty($data) ? array_column($data, 'group_uuid') : [];
    }

    /**
     * 得到修改任务的节点和存储UUID信息
     * @param array $nodeInfo 参数
     * @return array('nodeuuid','auto_find_sr_flag','storageuuid')
     */
    private function getModifyJobNodeAndStorageInfo($nodeInfo = [])
    {
        $flag = xphp_get_config('app', 'FLAG');
        $info = array(
            'nodeuuid' => $nodeInfo['nodeuuid'],
            'auto_find_sr_flag' => $flag['UNSET'],
            'storageuuid' => $nodeInfo['storageuuid']
        );
        if (empty($nodeInfo['nodeuuid'])) {
            $info['nodeuuid'] = (new Node())->getAutoFindNode();
        }
        if ($nodeInfo['storagecheck']) {
            //自动选择存储
            $info['auto_find_sr_flag'] = $flag['SET'];
            $info['storageuuid'] = '';
        }
        return $info;
    }

    /**
     * 获取代理分组的所有任务
     * @param string $groupuuid 分组uuid
     * @return array
     */
    private function getFileEditTaskInfo($groupuuid): array
    {
        $sql = "select bt.strategy_id, bt.agent_uuid, ft.task_uuid
                    from bd_task bt, fs_task ft where bt.task_uuid = ft.task_uuid and ft.agent_group_uuid = ?";
        $data = $this->dbSelect($sql, array($groupuuid));
        $list = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $list[] = array(
                    'job_uuid' => $d['task_uuid'],
                    'strategy_id' => $d['strategy_id'],
                    'agent_uuid' => $d['agent_uuid']
                );
            }
        }

        return $list;
    }

   

    /**
     * 递归获取加载更多
     * @param array $list   list
     * @param array $params 参数
     * @return array
     */
    private function getMoreData(array $list, array $params): array
    {
        $fileNodes = array();
        $moreData = //获取加载更多的节点
        $data = $this->getFileDirTree($params);
        $moreData = $data['file_nodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, '/');
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, '/'));
                $str = substr($str, 0, strripos($str, '/'));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, '/'));
            }
            $str .= '/';
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
            if (count($list) != 0 && $d['more']) {
                continue;
            }
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
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'agent_uuid' => $params['agent_uuid'],
                    'group_uuid' => $params['group_uuid'],
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
                'agent_uuid' => $params['agent_uuid'],
                'group_uuid' => $params['group_uuid'],
                'pid' => $params['dir'],
            );
            $moreNodes = $this->getMoreData($list, $data);
            $fileNodes = array_merge($fileNodes, $moreNodes);
        }

        return $fileNodes;
    }

    /**
     * 得到备份的文件列表
     * @param array $filelists 参数
     * @return array
     */
    private function groupBackupFS(array $filelists): array
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
     * 得到文件备份任务名
     * @param unknown $params
     */
    public function getFileBackupTaskName($params)
    {
        return $this->getValidTaskName(xphp_get_lang('WEB_FILE_BACKUP_TASKNAME'));
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
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
    //-------------------------new------------------------------------

    /**
     * 获取备份源-客户端、nas设备、Hadoop集群、对象存储
     * @return object
     */
    public function getBackupSource($params)
    {
        $result = array();
        $sub_module_type = $params['sub_module_type'];
        switch (intval($sub_module_type)) {
            case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']: 
                $result = $this->getAgentSoure($params);
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']: 
                $NasBackUp = (new NasBackUp);
                $result = $NasBackUp->getNasBackupTree($params);
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']: 
                $HadoopBackUp = (new HadoopBackUp);
                $result = $HadoopBackUp->getBackupZtree($params);
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']: 
                $ObsBackUp = (new ObsBackup);
                $result = $ObsBackUp->getObsBackupTree($params);
                break;
        }
        return $result;
    }

    /**
     * 获取文件备份客户端
     * @param array $params 参数数组
     * @return array
     */
    public function getAgentSoure($params = []): array
    {
        $nodes = $group = $agent = [];
        $search = v1_escape_wildcard($params['content']);
 		$isRecoveryFlag = $params['isRecovery'] ? true : false;
        $editUuid = $params['edit_uuid'];
        $sql = "select ba.id, ba.agent_uuid,ba.os_type, ba.agent_name, ba.hostname, ba.ip, ba.os_type,online_flag, ba.authorization_module,bag.group_uuid,ba.net_model,  
        bag.group_name, bag.detail from bd_agent ba left join bd_agent_group bag on ba.group_uuid = bag.group_uuid where ba.agent_type not in (3, 4, 5)";
        $agentuuidArr = Client::instance()->getClientUuids();
        if (empty($agentuuidArr)) {
            // 表示没得数据
            return $nodes;
        } else {
            if (is_array($agentuuidArr)) {
                $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
                // 具体的业务逻辑
                $sql .= " and ba.agent_uuid in {$agentuuidArrStr} ";
            }
        }
        if(!empty($search)) {
            $sql .= " and (ba.agent_name like '%". $search ."%' or ba.hostname like '%". $search ."%' or ba.ip like '%". $search ."%' or bag.group_name like '%". $search ."%')";
        }
        $data = $this->dbSelect($sql);
        $editAgentUuid = [];
        if (!empty($editUuid)) {//修改任务
            $sqlAgent = "SELECT agent_uuid FROM bd_task_agent_list WHERE task_uuid = ?";
            $dataAgent = $this->dbSelect($sqlAgent, array($editUuid));
            $editAgentUuid = array_column($dataAgent, 'agent_uuid');
        }
        if (!empty($data)) {
            foreach ($data as $d) {
                //分组
                if (!in_array($d['group_uuid'], $group)) {
                    //查分组下的agent
                    $sqlagent = "select agent_uuid from bd_agent where agent_type != 4 and group_uuid = ?";
                    $dataagent = $this->dbSelect($sqlagent, array($d['group_uuid']));
                    array_push($group,$d['group_uuid']);
                    $nodes[] = array(
                        'id' => $d['group_uuid'],
                        'pId' => 0,
                        'pid' => 0,
                        'name' => htmlspecialchars_decode($d['group_name']),
                        'title' => htmlspecialchars_decode($d['group_name']),
                        "isParent" => true,
                        "is_parent" => true,
                        'open' => $isRecoveryFlag,
                        'type' => 1,
                        'icon' => './img/platform/flag.png',
                        'uuid' => $d['group_uuid'],
                        'event_type' => "group",
                        'agent_list' => $dataagent[0],
                        'nocheck' => $isRecoveryFlag,
                        'checked' => !empty($editUuid),
                    );
                }
                //客户端
                if ($d['online_flag'] != 1) {
                    $authorizationonlineinfo = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')';
                } else {
                    $authorizationonlineinfo = '';
                }
                //客户端名  别名不为空并且不等于IP 显示别名+ip
                if (!empty($d['agent_name']) && $d['agent_name'] != $d['ip']) {
                    $name = $authorizationonlineinfo . $d['agent_name'] . '(' . $d['ip'] . ')';
                } else {
                    $name = $authorizationonlineinfo . $d['hostname'] . '(' . $d['ip'] . ')';
                }
                if (!in_array($d['agent_uuid'], $agent)) {
                    array_push($agent, $d['agent_uuid']);
                    $chk = in_array($authorizationonlineinfo, [ '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')', ]);
                    $checked = false;
                    if (in_array($d['agent_uuid'], $editAgentUuid)) {//修改任务选中的agent
                        $checked = true;
                    }
                    $nodes[] = array(
                        'id' => $d['group_uuid'] . '_' . $d['agent_uuid'],
                        'pid' => $d['group_uuid'],
                        "pId" => $d['group_uuid'],
                        'name' => htmlspecialchars_decode($name),
                        'title' => $d['ip'],
                        'is_parent' => false,
                        "isParent" => false,
                        'uuid' => $d['agent_uuid'],
                        'nocheck' => false,
                        'type' => 2,
                        'icon' => $d['os_type'] == 'Windows' ? './img/os/Windows.png' : './img/os/Linux.png',
                        'event_type' =>  'agent',
                        'os_type' => $d['os_type'],
                        'chk_disabled' => $chk,
                        "chkDisabled" => $chk,
                        'net_model' => intval($d['net_model']),
                        'is_check_flag' => $chk,   //是否可以被选中
                        'agent_uuid' => $d['agent_uuid'],
                        'checked' => $checked,
                    );
                }
            }
        }
        //分组个数为1时，展开分组；多个分组时，不展开，避免客户端太多，看不到下面的分组
        if (count($group) == 1) {
            $nodes[0]['open'] = true;
        }
        return $nodes;
    }

    /**
     * 获取客户端下的文件目录
     * @return object
     */
    public function getFileDirTree($params)
    {
        $uuid = '';
        switch (intval($params['sub_module_type'])) {
            case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']: 
                $uuid = $params['agent_uuid'];
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']: 
                $uuid = $params['nas_uuid'];
                return (new NasBackUp)->getNasSonTree($params);
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']: 
                return $this->groupHadoopData((new HadoopBackUp)->getFileDirTree($params));
            case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']: 
                return $this->groupObsData((new ObsBackup)->getFileDirTree($params), $params);
        }
        $agentUuid = $params['agent_uuid'];
        $fileList = array(); //用于保存修改文件列表
        //获取当前代理端文件备份路径列表
        if ($params['edit_flag']) {
            if (!empty($params['apply_path_list'])) { //应用到其他客户端
                $data = $params['apply_path_list'];
            } else {//查询出修改任务需要选中的路径
                $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
                $data = $this->dbSelect($sql, array($uuid, $params['edit_uuid']));
            }
            $fileList = $this->getFileList($data);
        }
        //获取后台返回的文件列表，与需要选中的文件路径进行匹配
        $result = $this->getFileDir($params);
        $info = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finish_flag" => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "search_index" => $result['current_next_index'],   //未完成时,下一个开始位置
            "search_file_name" => $result['search_file_name'],  //未完成时,下一个开始名字
        );
        foreach ($result['item_list'] as $d) {
            $checked = false; //选中标志
            $open = false; //展开标志
            if($params['edit_flag']) {
                //检查返回结果是否在修改文件列表$fileList里
                if (in_array($d['item_path'], $fileList)) {
                    $checked = true;
                    //从修改文件列表中 排除已经选中的
                    foreach ($fileList as $key => $file) {
                        if ($file == $d['item_path']) {
                            unset($fileList[$key]);
                        }
                    }
                }
                //检查节点是否需要展开
                foreach ($fileList as $l) {
                    //strpos返回第二个字符串在第一个字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
                    if (strpos($l, $d['item_path']) !== false) {
                        $open = true;
                    }
                }
            }
            $info['file_nodes'][] = array(
                "id" => $d['item_path'],
                "pId" => 0,
                "name" => $d['item_name'],      //文件名或者磁盘名
                "title" => $d['item_name'],
                "isParent" => $this->getBoolType($d['item_type']),
                "open" => $open,
                "uuid" => $uuid,
                "nocheck" => false,
                "type" => $d['item_type'], //1文件 2 文件夹 3 磁盘
                "icon" => $this->getIconByType($d['item_type']),
                "file_path" => $d['item_path'],
                "group_uuid" => $result['group_uuid'],
                "agent_uuid" => $agentUuid,
                "checked" => $checked,
                "code_type" => $d['code_type'],
            );
            //修改或者应用到其他客户端获取要展开目录或磁盘下的文件
            if ($checked && $d['item_type'] != 1) {
                if (!$open)
                    continue;
                //获取需要展开的节点和加载更多节点
                $params = array(
                    'start' => 0,
                    'limit' => 40,
                    'file_name' => $d['item_name'],
                    'dir' => $d['item_path'],
                    'agent_uuid' => $agentUuid,
                    'group_uuid' => $result['group_uuid'],
                    'pid' => $d['item_path'],
                    'uuid' => $uuid,
                );
                //展开子节点
                $sonList = $this->getOnLoadTree($fileList, $params, $d['item_path']);
                $info['file_nodes'] = array_merge($info['file_nodes'], $sonList);
            }

        }
        return $info;
    }

    /**
     * 组装对象存储数据让数据格式与其他模块一致
     * @return object
     */
    public function groupObsData($data, $params)
    { 
        $info = array();
        $info['re'] = $data['re'];
        $info['finish_flag'] = $data['finishFlag'];
        $info['search_index'] = $data['searchIndex'];
        $info['search_file_name'] = $data['searchFilename'];
        $info['file_nodes'] = $data['fileNodes'];
        foreach ($info['file_nodes'] as $key => $each_path) {
            if ($params['no_son_dir']) {
                $info['file_nodes'][$key]['file_path'] = rtrim($each_path['id'], '/') . '/';
                $info['file_nodes'][$key]['id'] = rtrim($each_path['id'], '/') . '/';  
                // if (!$params['edit_uuid']) {
                //     $info['file_nodes'][$key]['icon'] = $this->getIconByType($each_path['item_type']);  
                // }
            } else {//如果是子目录请求回来的数据需要去掉一个斜杠
                $info['file_nodes'][$key]['file_path'] = rtrim($each_path['id'], '/') . '/'; 
                $info['file_nodes'][$key]['id'] = rtrim($each_path['id'], '/') . '/'; 
                if (!$params['edit_uuid']) {
                    $info['file_nodes'][$key]['icon'] = $this->getIconByType($each_path['type']);  
                }
            }
            
            $info['file_nodes'][$key]['group_uuid'] = $params['group_uuid']; 
        }
        return $info;
    }

    /**
     * 组装Hadoop数据让数据格式与其他模块一致
     * @return object
     */
    public function groupHadoopData($data)
    { 
        $info = array();
        $info['file_nodes'] = $data['file_nodes'];
        foreach ($info['file_nodes'] as $key => $each_path) {
            $info['file_nodes'][$key]['file_path'] = $each_path['filepath']; 
            $info['file_nodes'][$key]['uuid'] = $each_path['clusteruuid']; 
        }
        return $info;
    }

    /**
     * 获取图标
     * @return object
     */
    public function getIconByType($type)
    { 
        $icon = '';
        switch ($type) {
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
        return $icon;
    }

    /**
     * 构造修改任务或者应用到其他客户端需要选中的文件的结构
     * @return object
     */
    public function getFileList($data)
    {
        $fileList = array();
        foreach ($data as $d) {
            if ($d['path_type'] != 3) { //文件或文件夹
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
                    if (!in_array($path, $fileList)) {
                        $fileList[] = $path;
                    }
                }
            } else { //磁盘
                if (!in_array($d['path_name'], $fileList)) {
                    $fileList[] = $d['path_name'];
                }
            }
        } 
        return $fileList;
    }

     /**
     * 递归获取加载子节点
     * @param array  $list 修改需要选中的文件列表
     * @param array  $params 向后台请求文件目录的参数
     * @param string $path  要展开的路径
     * @return array
     */
    private function getOnLoadTree($list, $params, $path)
    {
        $fileNodes = array();
        //获取子节点
        $result = $this->getFileDir($params);
        $fileData = $result['item_list'];
        //获取展开子节点
        foreach ($fileData as $key => $d) {
            $checked = false;
            if (in_array($d['item_path'], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$list
                foreach ($list as $key => $file) {
                    if ($file == $d['item_path']) {
                        unset($list[$key]);
                    }
                }
            }
            if ($d['more']) {
                continue;
            }
            //检查节点是否需要展开
            $flag = false;
            foreach ($list as $l) {
                if (strpos($l, $d['item_path']) !== false) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }

            $fileNodes[] = array(
                "id" => $d['item_path'],
                "pId" => $params['pid'],
                "name" => $d['item_name'],      //文件名或者磁盘名
                "title" => $d['item_name'],
                "isParent" => $this->getBoolType($d['item_type']),
                "open" => $flag,
                "uuid" => $params['uuid'],
                "nocheck" => false,
                "type" => $d['item_type'], //1文件 2 文件夹 3 磁盘
                "icon" => $this->getIconByType($d['item_type']),
                "file_path" => $d['item_path'],
                "group_uuid" => $result['group_uuid'],
                "agent_uuid" => $params['agent_uuid'],
                "checked" => $checked,
                "code_type" => $d['code_type'],
            );
            if ($checked && $d['type'] != 1) {
                //判判断$list存在父节点是c/1/2
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['item_path'],
                    'agent_uuid' => $params['agent_uuid'],
                    'group_uuid' => $params['group_uuid'],
                    'pid' => $d['item_path']
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['item_path']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        //获取加载更多
        // if (count($info) != 0 && $fileData[count($fileData) - 1]['more']) {
            if ($fileData[count($fileData) - 1]['more']) {
            //递归加载更多
            $data = array(
                'start' => $fileData[count($fileData) - 1]['next_index'],
                'limit' => 40,
                'filename' => $fileData[count($fileData) - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agent_uuid' => $params['agent_uuid'],
                'group_uuid' => $params['group_uuid'],
                'pid' => $params['dir']
            );
            $moreNodes = $this->getMoreData($list, $data);
            $fileNodes = array_merge($fileNodes, $moreNodes);
        }

        return $fileNodes;
    }
    /**
     * 获取源端文件列表
     * @return array
     */
    private function getFileDir(array $params): array
    {
        // $this->paramsCheck($params['agent_uuid']);
        $msg = array(
            'agent_uuid' => $params['agent_uuid'],
            'search_index' => intval($params['offset']),
            'limit_count' => intval($params['limit']),
            'search_file_name' => $params['file_name'] ?? '',
            'dir_path' => $params['dir']  ?? '',
            'code_type' => !empty($params['code_type']) ? $params['code_type'] : 2, // 编码类型
        );
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);
        $result = $mbResult['result'];
        if (!$result) {//失败
            $nodeOpcode = new NodeOpcode();
            $operate = $nodeOpcode->getOpcodeDes($opName);
            exit($this->muOpResult($result, $operate, '', '', $mbResult['errorCode']));
        }
        $mbResult['msg']['pid'] = $params['pid'] ?? 0;
        $mbResult['msg']['group_uuid'] = $params['group_uuid'];
        return $mbResult['msg'];
    }
}