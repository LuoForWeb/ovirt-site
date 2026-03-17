<?php

namespace app\v1\file\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Client;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Tenant;

/**
 * note          文件 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileBackUp extends Backup
{
    /**
     * 获取备份任务文件备份树
     * @param array $params 参数数组
     * @return array
     */
    public function getAgentGroupBackupTree($params = []): array
    {
        $nodes = $group = $agent = [];
        $search = v1_escape_wildcard($params['content']);
        $sql = "select ba.id, ba.agent_uuid,ba.os_type, ba.agent_name, ba.hostname, ba.ip, ba.os_type,online_flag, ba.authorization_module,bag.group_uuid,ba.net_model,  
        bag.group_name, bag.detail from bd_agent ba left join bd_agent_group bag on ba.group_uuid = bag.group_uuid where ba.agent_type not in (3, 4, 5)";
        //获取当前用户拥有的agent
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " and ({$resourceUuidSql})";
        }
        if(!empty($search)) {
            $sql .= " and (ba.agent_name like '%". $search ."%' or ba.hostname like '%". $search ."%' or ba.ip like '%". $search ."%' or bag.group_name like '%". $search ."%')";
        }
        $data = $this->dbSelect($sql);
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
                        'open' => false,
                        'type' => 1,
                        'icon' => './img/platform/flag.png',
                        'uuid' => $d['group_uuid'],
                        'event_type' => "group",
                        'agent_list' => $dataagent[0],
                        'nocheck' => false,
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
                    $chk = in_array(
                        $authorizationonlineinfo,
                        [
                            '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')',
                        ]
                    );
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
                    );
                }
            }
        }
        return $nodes;
    }

    /**
     * 创建备份任务
     * @param array $params 参数
     * @return string
     */
    public function createBackupJob($params = [])
    {
        //public params
        $taskname = htmlspecialchars_decode($params['job_name']);
        //全局策略uuid  (没有为空值)
        $globalID = $params['global_id'] ?? '';
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        //租户内检查可用数量是否超过授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            (new Tenant())->checkTenantAuth($moduleType, $params['src_info']['agent_list'], '');
        }
        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backup_info'], $globalID);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['high_info']['reserve']);
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['high_info']['transfer']);
        //组合存储策略
        $storagestrategy = $this->groupStorageStrategy($params['high_info']['store']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['high_info']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduleType,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo,
            $params['backup_info']['type']
        );

        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupFS($params['src_info']['file_info']);
        $pfMsg['group_list'] = $this->getGroupList($params['src_info']['group_list'], $params['src_info']['file_info']);
        $pfMsg['snap_shot_flag'] = $params['high_info']['newstr']['silentsnapshotcheck'] ? 1 : 2;//快照
        $pfMsg['thread_num'] = $params['high_info']['newstr']['backup_thread_num'];//传输线程数量
        $pfMsg['scan_thread_num'] = $params['high_info']['newstr']['scan_thread_num'];//扫描线程数量
        $pfMsg['scan_file_num'] = $params['high_info']['newstr']['scan_file_num'];//扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['high_info']['newstr']['wildcard_list']);//通配符
        //限速策略
        $pfMsg['speed_limit_strategy_limit'] = $this->groupTaskSpeedList($params['speed_info'], $globalID);
        // 全局限速策略配置-新
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $globalID;
        $pfMsg['file_archive_flag'] = $params['high_info']['file_archive'];//归档
        $pfMsg['skip_file_alarm_flag'] = $params['high_info']['skip_file_alarm_flag'] ? 1 : 2; //跳过文件告警开关
        $pfMsg['permission_operate_flag'] = $params['high_info']['permission_operate_flag'] ? 1 : 2; //文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['high_info']['skip_file_alarm_min_num']; //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['high_info']['skip_file_alarm_min_ratio']; //跳过文件告警比例
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        //按代理分组批量创建文件备份任务
        $agentList = $params['src_info']['agent_list'];
        if (!empty($agentList)) {
            $msg = json_encode($pfMsg);
            $mbResult = $this->service()->mbFSMsgs($nodeInfo['node_uuid'], $opName, $msg);
            sleep(1);   //睡一秒
            //更新fs_task的group_uuid
            if (!empty($params['src_info']['group_uuid'])) {
                $this->updateTaskAgentGroup($taskname, $params['src_info']['group_uuid']);
            }
        } else {
            //单个代理创建任务
            $msg = json_encode($pfMsg);
            $mbResult = $this->service()->mbFSMsgs($nodeInfo['node_uuid'], $opName, $msg);
        }

        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改备份任务
     * @param array $params 参数
     * @return string
     */
    public function editBackupJob($params)
    {
        $task_name = htmlspecialchars_decode($params['job_name']);
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['global_id'];
        $module_type = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            Tenant::instance()->checkTenantAuth($module_type, $params['src_info']['agent_list'], $params['job_uuid']);
        }
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['backup_info'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['high_info']['reserve'], $strategygroupuuid);
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($params['high_info']['transfer'], $strategygroupuuid);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['high_info']['store'], $strategygroupuuid);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['high_info']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $params['backup_info']['type']
        );
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['task_uuid'] = $params['job_uuid'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupFS($params['src_info']['file_info']);
        $pfMsg['group_list'] = $this->getGroupList($params['src_info']['group_list'], $params['src_info']['file_info']);
        $pfMsg['snap_shot_flag'] = $params['high_info']['newstr']['silentsnapshotcheck'] ? 1 : 2; //快照
        $pfMsg['thread_num'] = $params['high_info']['newstr']['backup_thread_num']; //传输线程数量
        $pfMsg['scan_thread_num'] = $params['high_info']['newstr']['scan_thread_num']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['high_info']['newstr']['scan_file_num']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['high_info']['newstr']['wildcard_list']); //通配符
        //限速策略
        $pfMsg['speed_limit_strategy_list'] =$this->groupTaskSpeedList($params['speed_info'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);

        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['file_archive_flag'] = $params['backup_info']['file_archive']; //归档
        $pfMsg['skip_file_alarm_flag'] = $params['backup_info']['skip_file_alarm_flag'] ? 1 : 2; //跳过文件告警开关
        $pfMsg['permission_operate_flag'] = $params['backup_info']['permission_operate_flag'] ? 1 : 2; //文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['backup_info']['skip_file_alarm_min_num']; //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['backup_info']['skip_file_alarm_min_ratio']; //跳过文件告警比例
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        //按代理分组批量创建文件备份任务
        $agentList = $params['src_info']['agent_list'];
        if (!empty($agentList)) {
            // dump($nodeInfo['node_uuid'], $opName,$pfMsg);
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeInfo['node_uuid'], $opName, $msg);
            sleep(1);   //睡一秒
            //更新fs_task的group_uuid
            if (!empty($params['backup_info']['groupuuid'])) {
                $this->updateTaskAgentGroup($task_name, $params['backup_info']['groupuuid']);
            }
        } else {
            //单个代理创建任务
            $msg = json_encode($pfMsg);
            
            $mbResult = $this->mbFSMsg($nodeInfo['node_uuid'], $opName, $msg);
        }


        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
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
    *  代理端文件树
     * @param array $params 参数
     * @return array|string
     */
    public function getFileDirTree($params = [])
    {
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $allresult = $this->getFileDir($params);
        $editFlag = $params['edit_flag'];    //修改标记
        $agentuuid = $params['agent_uuid'];
        $taskuuid = $params['job_uuid'];
        $applyPathList = $params['apply_path_list'];//用于应用到其他客户端
        $filelist = array();//用于保存修改文件列表
        //获取当前代理端文件备份路径列表
        if ($editFlag) {
            if (!empty($applyPathList)) {//应用到其他客户端
                $data = $applyPathList;
            } else {//文件修改
                $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
                $data = $this->dbSelect($sql, array($agentuuid,$taskuuid));
            }
            foreach ($data as $d) {
                if ($d['path_type'] == 1 || $d['path_type'] == 2) {//文件或文件夹
                    //截取文件信息
                    $info = explode('/', $d['path_name']);
                    $path = '';
                    //获取已选择的文件信息列表
                    foreach ($info as $key => $i) {
                        if ($key == count($info) - 1) {//不是路径最后一层就拼接 /
                            if ($d['path_type'] == 1) {//是最后一层判断是否是文件，文件不用在最后拼 /
                                $path .= $i;
                            } else {
                                continue;
                            }
                        } else {
                            $path .= $i . '/';
                        }
                        if (!in_array($path, $filelist)) {
                            $filelist[] = $path;
                        }
                    }
                } else {//磁盘
                    if (!in_array($d['path_name'], $filelist)) {
                        $filelist[] =  $d['path_name'];
                    }
                }
            }
        }
//         $list = array('c','c/1','c/1/2', 'f','f/1','f/1/2')
        $result = $allresult['result'];
        $nodeOpcode = new NodeOpcode();
        $operate = $nodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            're' => true,                                   //成功标志,方面前端统一处理
            'finishFlag' => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            'searchIndex' => $result['current_next_index'],   //未完成时,下一个开始位置
            'searchFilename' => $result['search_file_name'],  //未完成时,下一个开始名字
        );
        $fileNodes = array();

        //$list0 = array(D,E,F); 长度
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            $checked = false;
            $open = false;
            //检查节点是否在修改列表里
            if (in_array($d['item_path'], $filelist)) {
                $checked = true;
                //从修改文件列表中 排除已经选中的节点
                foreach ($filelist as $key => $file) {
                    if ($file == $d['item_path']) {
                        unset($filelist[$key]);
                    }
                }
            }
            //检查节点是否需要展开
            $flag = false;
            foreach ($filelist as $l) {
                //strpos返回第二个字符串在第一个字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
                if (strpos($l, $d['item_path']) !== false) {
                    $flag = true;
                    $open = true;
                }
            }
            $fileNodes[] = array(
                "id" => $d['item_path'],
                "pId" => 0,
                "name" => $filename,      //文件名或者磁盘名
                "title" => $filename,
                "isParent" => $this->getBoolType($d['item_type']),
                "open" => $open,
                "uuid" => $agentuuid,
                "nocheck" => false,
                "type" => $d['item_type'], //1文件 2 文件夹 3 磁盘
                "icon" => "./img/fs/cipan.png",
                "filepath" => $d['item_path'],
                "groupuuid" => $result['groupUUID'],
                "checked" => $checked,
                "code_type" => $d['code_type'],
                "event_type" => "agent",
            );
            //获取要展开节点的子节点
            if ($checked && $d['item_type'] != 1) {
                if (!$flag)
                    continue;
                //获取需要展开的节点和加载更多节点
                $params = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $filename,
                    'dir' => $d['item_path'],
                    'agent_uuid' => $agentuuid,
                    'group_uuid' => $result['groupUUID'],
                    'pid' => $d['item_path']

                );
                //展开子节点
                $sonList = $this->getOnLoadTree($filelist, $params, $d['item_path']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }

        }
        //如果list0长度不为空 继续加载更多
        $list['file_nodes'] = $fileNodes;
        return $list;
    }

    /**
    * 代理端文件子树
     * @param array $params 参数
     * @return array|string
     */
    public function getFileDirSonTree($params = [])
    {
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $allresult = $this->getFileDir($params);
        $result = $allresult['result'];
        $nodeOpcode = new NodeOpcode();
        $operate = $nodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            're' => true,                                   //成功标志,方面前端统一处理
            'finish_flag' => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            'search_index' => $result['current_next_index'],   //未完成时,下一个开始位置
            'search_filename' => $result['search_file_name'],  //未完成时,下一个开始名字
        );
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            if (!empty($params['dir'])) {
                $fileNodes[] = array(
                    'id' => $d['item_path'],
                    'pid' => $result['pid'] == '' ? 0 : $result['pid'],
                    'pId' => $result['pid'] == '' ? 0 : $result['pid'],
                    'name' => $filename,      //文件名或者磁盘名
                    'title' => $filename,
                    'is_parent' => $this->getBoolType($d['item_type']),
                    'isParent' => $this->getBoolType($d['item_type']),
                    'open' => false,
                    'uuid' => $params['agent_uuid'],
                    'nocheck' => false,
                    'type' => $d['item_type'], //1文件 2 文件夹 3 磁盘
                    'icon' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'icon_open' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                    'icon_close' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'filepath' => $d['item_path'],
                    'more' => false,
                    "group_uuid" => $result['group_uuid'],
                    'code_type' => $d['code_type'],
                    "event_type" => "agent",
                );
            }
        }
        if (intval($result['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                // "id" => $pid==0?0:$pid.'/',
                'pid' => $result['pid'],
                'pId' => $result['pid'],
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                // "isParent" => false,
                // "open" => false,
                'uuid' => $params['agent_uuid'],
                'nocheck' => true,
                'more' => true,
                'next_index' => $result['current_next_index'],       //从哪个位置开始加载
                'search_file_name' => $result['search_file_name'],          //从哪个目录开始加载
                'dir_path' => $params['dir'],
                "group_uuid" => $result['group_uuid'],                                //当前目录名
                "event_type" => "agent",
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            $fileNodes[] = $more;
        }
        $list['file_nodes'] = $fileNodes ?? [];

        return $list;
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
     * 递归获取加载子节点
     * @param array  $list   list
     * @param array  $params 参数
     * @param string $path   路径
     * @return array
     */
    private function getOnLoadTree($list, $params, $path)
    {
        $fileNodes = array();
        //获取子节点
        $data = $this->getFileDirSonTree($params);
        $fileData = $data['file_nodes'];
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
            if ($str == $path) {
                $info[] = $l;
            }
        }
        //获取展开子节点
        foreach ($fileData as $key => $d) {
            $checked = false;
            if (in_array($d['filepath'], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$list
                foreach ($list as $key => $file) {
                    if ($file == $d['filepath']) {
                        unset($list[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($info[$key]);
                    }
                }
            }

            if (count($info) != 0 && $d['more']) {
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
        //获取加载更多
        if (count($info) != 0 && $fileData[count($fileData) - 1]['more']) {
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
     * 递归获取加载更多
     * @param array $list   list
     * @param array $params 参数
     * @return array
     */
    private function getMoreData(array $list, array $params): array
    {
        $fileNodes = array();
        $moreData = //获取加载更多的节点
        $data = $this->getFileDirSonTree($params);
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
     * 得到代理端文件列表
     * @param array $params 参数
     *                      start  开始位置
     *                      limit  查找个数
     *                      searchFileName 从哪个文件名开始查找
     *                      dir            进入目录的目录名
     *                      三种情况
     *                      1.初始展示[0, N, '', '']
     *                      2.更多信息[N, N+M, searchFileName, '']
     *                      3.进入目录[0, N, '', dir]
     * @return array
     */
    private function getFileDir(array $params): array
    {
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'] ?? '';
        $dir = $params['dir']  ?? '';
        $pid = $params['pid'] ?? 0;          //父节点ID
        $agentUUID = $params['agent_uuid'];
        $groupUUID = $params['group_uuid'];
        $this->paramsCheck($agentUUID);
        $codetype = !empty($params['code_type']) ? $params['code_type'] : 2; // 编码类型

        $msg = array(
            'agent_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $codetype,
        );
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';

        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);
        $mbResult['msg']['pid'] = $pid;
        $mbResult['msg']['group_uuid'] = $groupUUID;
        return $mbResult;
    }

    /**
     *
     * @param string $taskname  任务名
     * @param string $groupuuid 分组uuid
     * @return boolean
     */
    private function updateTaskAgentGroup(string $taskname, string $groupuuid)
    {
        $sql = "update fs_task ft, bd_task bt set ft.agent_group_uuid = ?
                        where bt.task_uuid = ft.task_uuid and bt.task_name = ? ";
        return $this->dbExec($sql, array($groupuuid, $taskname));
    }

    /**
     * 得到备份的通配符相关信息
     * @param array $wildcard 列表
     * @return array
     */
    private function getWildCardList(array $wildcard): array
    {
        $data = array();
        foreach ($wildcard as $w) {
            $data[] = array(
                'agent_uuid' => $w[0],
                'wildcard' => $w[1],
                'wildcard_mode' => $w[2],
            );
        }
        return $data;
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
     * 得到备份的客户端列表
     * @param array $grouplists 分组列表
     * @param array $fileinfo   文件列表
     * @return array
     */
    private function getGroupList($grouplists, $fileinfo): array
    {
        $data = array();
        foreach ($grouplists as $g) {
            $agentList = array();
            foreach ($fileinfo as $f) {
                if ($f[3] == $g && !in_array($f[2], $agentList)) { //groupuuid相同
                    array_push($agentList, $f[2]);
                }
            }
            $data[] = array(
                'group_uuid' => $g,
                'agent_uuid' => $agentList,
            );
        }
        return $data;
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
}