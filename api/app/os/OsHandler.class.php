<?php
/*******************************************
 ** 主机处理
 **
 ** @author       liushuai@vinchin.com
 ** @date         2021-10-19 10:06:00
 ********************************************/
require_once XPHP_PATH.'utils/BLLHandler.class.php';
class OsHandler extends BLLHandler{

    /**
     * 创建主机备份任务
     * @param unknown $params
     */
    public function createOSBackupJob($params){
        //public params
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $jobHandler = Xphp::instance('JobHandler');
        //租户内检查可用数量是否超过授权个数
        if(!empty($_SESSION['tenantuuid'])){
            $os_list_num = array();
            $tenantHandler = Xphp::instance('TenantHandler');
            foreach($params['srcInfo']['backup_oss_info'] as $os){
                $os_list_num[] = $os['agent_uuid'];
            }
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['OS'], $os_list_num, "");
        }

        $module_type = Xphp::$_config['MODULE_TYPE']['OS'];
        $vmHandler = Xphp::instance('Vmhandler');
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategygroupuuid'];
        //组合备份方式完备差备等
        $time_strategy_list = $vmHandler->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $vmHandler->groupReserverStrategy($params['highInfo']['reserve'], $strategygroupuuid);
        //组合传输策略
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        //组合存储策略
        $storage_strategy = $vmHandler->groupStorageStrategy($params['highInfo']['store'], $strategygroupuuid);
        //组合节点信息
        $nodeInfo = $vmHandler->groupBackupNodeInfo($params['highInfo']['node']);
        //时间策略备份方式
        $backup_type = $params['backupInfo']['type'];
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage($task_name, $module_type,
            $time_strategy_list, $reserver_strategy, $transport_strategy, $storage_strategy, $nodeInfo, $backup_type);
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['OS_BACKUP'];
        $utils = Xphp::instance('Utils');
        //得到线程数
        $pfMsg['thread_num'] = $params['thread_num'];
        //得到限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);

        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //-------以下为主机私有------
        //得到有效数据
        $pfMsg['valid_data_flag'] = $utils->parseBoolToFlag($params['srcInfo']['valid_data_flag']);
        //得到静默快照
        $pfMsg['silent_snapshot_flag'] = $utils->parseBoolToFlag($params['srcInfo']['silent_snapshot_flag']);
        //得到CBT
        $pfMsg['cbt_flag'] = $utils->parseBoolToFlag($params['srcInfo']['cbt_flag']);
        //得到快照
        $pfMsg['snapshot_flag'] = $utils->parseBoolToFlag($params['srcInfo']['snapshot_flag']);
        //得到传输模式
        $pfMsg['transport_priority'] = $params['srcInfo']['transport_priority'];
        //得到备份选择的虚拟机以及排除等信息(独有结构体)
        $pfMsg['backup_oss_info'] = $params['srcInfo']['backup_oss_info'];
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        $msg = json_encode($pfMsg);
        //发送消息
        //得到备份创建操作码
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $mbResult = $this->mbOSMsg($nodeInfo['node_uuid'],$opName,$msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if(!$result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }


    /**
     * 修改备份任务
     * @param unknown $params
     */
    public function editOSBackupJob($params){
        //public params
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $jobHandler = Xphp::instance('JobHandler');
         //租户内检查可用数量是否超过授权个数
         if(!empty($_SESSION['tenantuuid'])){
            $os_list_num = array();
            $tenantHandler = Xphp::instance('TenantHandler');
            foreach($params['srcInfo']['backup_oss_info'] as $os){
                $os_list_num[] = $os['agent_uuid'];
            }
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['OS'], $os_list_num, $params['taskuuid']);
        }
        $module_type = Xphp::$_config['MODULE_TYPE']['OS'];
        $vmHandler = Xphp::instance('Vmhandler');
        $nodeHandler = Xphp::instance('NodeHandler');
        //全局策略uuid  (没有传空值)
        $strategygroupuuid = '';
        //组合节点信息
        $nodeInfo = $vmHandler->groupBackupNodeInfo($params['highInfo']['node']);
        //获取传输网络
        $tranferInfo  = $params['highInfo']['transfer'];
        $tranferInfo['network'] = $nodeHandler->pGetDiffNodeNetwork($params['taskuuid'], $nodeInfo['node_uuid'], $params['highInfo']['transfer']['network']);
        //组合备份方式完备差备等
        $time_strategy_list = $vmHandler->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $vmHandler->groupReserverStrategy($params['highInfo']['reserve'], $strategygroupuuid);
        //组合传输策略
        $transport_strategy = $vmHandler->groupTransportStrategy($tranferInfo, $strategygroupuuid);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'], $strategygroupuuid,$params['taskuuid']);
        //时间策略备份方式
        $backup_type = $params['backupInfo']['type'];
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage($task_name, $module_type,
            $time_strategy_list, $reserver_strategy, $transport_strategy, $storage_strategy, $nodeInfo, $backup_type);
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['OS_BACKUP'];
        $utils = Xphp::instance('Utils');
        //得到线程数
        $pfMsg['thread_num'] = $params['thread_num'];
        //得到限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //-------以下为主机私有------
        //得到有效数据
        $pfMsg['valid_data_flag'] = $utils->parseBoolToFlag($params['srcInfo']['valid_data_flag']);
        //得到静默快照
        $pfMsg['silent_snapshot_flag'] = $utils->parseBoolToFlag($params['srcInfo']['silent_snapshot_flag']);
        //得到CBT
        $pfMsg['cbt_flag'] = $utils->parseBoolToFlag($params['srcInfo']['cbt_flag']);
        //得到快照
        $pfMsg['snapshot_flag'] = $utils->parseBoolToFlag($params['srcInfo']['snapshot_flag']);
        //得到传输模式
        $pfMsg['transport_priority'] = $params['srcInfo']['transport_priority'];
        //得到备份选择的虚拟机以及排除等信息(独有结构体)
        $pfMsg['backup_oss_info'] = $params['srcInfo']['backup_oss_info'];
        //忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['task_uuid'] = $params['taskuuid'];
        $msg = json_encode($pfMsg);
        //发送消息
        //得到备份创建操作码
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $mbResult = $this->mbOSMsg($nodeInfo['node_uuid'],$opName,$msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            $logParams = array(
                'task_uuid' => $params['taskuuid'],
                'task_name' => $task_name,
                'task_type' => Xphp::$_config['TASKTYPE']['OS_BACKUP'],
                'module_type' => Xphp::$_config['MODULE_TYPE']['OS'],
                'submodule_type' => 0,
            );
            $this->taskLog($logParams, 'WEB_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS', array($task_name));
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }



    }


    /**
     * 组合存储策略
     * @param array $storage  存储策略信息
     *  @param  int blocksize    数据块大小
     *  @param  bool compress    压缩
     *  @param  bool deduplication  重删
     *  @param  bool  encrypt     加密
     * @return array
     */
    public function groupStorageStrategy($storage, $strategygroupuuid,$taskuuid){
        $strArr = array(
            'deduplication_flag' => $storage['deduplication'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'blocksize' => intval($storage['blocksize'])*1024,
            'encrypt_flag' => $storage['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $storage['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'strategy_group_uuid' => $strategygroupuuid,
            'password_auto_flag' => $storage['password_auto_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'password' => $this->verifyEditEncry($taskuuid, base64_decode($storage['password'])),
            'compress_method' => $storage['compress_method'] ? $storage['compress_method'] : 0,
            'encrypt_method' => intval($storage['encrypt_method']),
        );
        return $strArr;
    }


    /**
     * 创建主机恢复任务
     * @param unknown $params
     */
    public function createOSRecoverJob($params){
        $jobHandler = Xphp::instance('JobHandler');
        $utils = Xphp::instance('Utils');
        //public params
        //得到名称
        $task_name = $params['taskName'];
        //得到全局策略组uuid
        $strategygroupuuid = $params['strategygroupuuid'];
        //得到模块编号
        $module_type = Xphp::$_config['MODULE_TYPE']['OS'];
        //得到恢复方式
        $recovery_time_type = intval($params['typeInfo']['type']);
        //得到恢复位置  默认传1
        $recovery_position = 1;
        $vmHandler = Xphp::instance('Vmhandler');
        //组合传输策略
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        //组合时间策略
        $time_strategy_list = $vmHandler->groupRecoverTimeList($params['typeInfo'], $strategygroupuuid);
        //组合--返回task_name,module_type,recovery_position,recovery_time_type,time_strategy_list,transport_strategy
        $pfMsg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
            $recovery_time_type, $time_strategy_list, $transport_strategy);
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['OS_RECOVERY'];
        //得到限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);

        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //得到线程数
        $pfMsg['thread_num'] = $params['highInfo']['threadnum'];
         //得到重试策略
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);
        //根据时间点得到node_uuid  因为时间点只能选择同一节点下的  所以数据里所有时间点的节点是同一时间点  随便传一个时间点即可
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['recoverInfo'][0]['recovery_timepoint_uuid']);
        $pfMsg['node_uuid'] = $nodeuuid;
        //---------以下主机恢复私有-------------
        $pfMsg['recovery_oss_info'] = $params['recoverInfo'];
        //目前单个恢复 则把密码信息放在外面 后面有需求做成多个的时候再改结构
        $pfMsg['timepoint_password'] = base64_decode($params['timepoint_password']);
        //发送消息
        //得到恢复操作码
        $opName = "BD_TASK_OP_RECOVERY_CREATE";
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbOSMsg($nodeuuid,$opName,$msg);

        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if($result){
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if(Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == $recoveryType){
                $startResult = $this->startRecoverJob($task_name);
            }else{
                $startResult = true;
            }
            if($startResult){
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            }else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
                return $this->muOpResult($result, $operate, $msg);
            }
        }else{
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
            'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
        );
        //调用系统统一启动任务接口.不重新写
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($data[0]['task_uuid']);   //任务UUID对应的存储节点uuid
        $msg = json_encode($params);
        $sync = FALSE;
        $command = TRUE;
        $opName = 'BD_TASK_OP_RECOVERY_START';
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];

        //这里直接返回成功或失败 bool
        return $result;
    }



    /**
     * 得到备份端代理树
     */
    public function getOsBackupTree($params){
        $agent_uuids = $params['agent_uuids'];
        
        //从数据库获取所有已添加的代理以及分组信息
        $sql = "select ba.agent_uuid,ba.os_type,ba.agent_name,ba.hostname,ba.ip,ba.group_uuid,ba.online_flag,ba.authorization_module, ba.net_model,
        bag.group_name, bag.group_type from bd_agent_group bag, bd_agent ba";
        //admin不能看租户内部的资源
        $sqlcontext = ' where';
        if (empty($_SESSION['tenantuuid'])) { // 非租户用户不能查看租户的资源
            $sql .= " LEFT JOIN mt_user_tenant mut on ba.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL";
            $sqlcontext = ' and';
        }
        $sql .= $sqlcontext." ba.group_uuid = bag.group_uuid and ba.plugin_deploy_status = 2 and ba.agent_type not in (3, 4, 5)";
        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源列表
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到分配的资源
            $resourceUuidSql = $utils->v1_auth_get_source_by_type(10, 'ba.agent_uuid');
            $sqlNew = " and ({$resourceUuidSql}) ";
            $sql .= $sqlNew;
        }

        //得到所有数据
        $result = $this->dbSelect($sql);
		$info = array();
        //判断是否在任务中
        $task_agent_uuid_list = $this->getTaskAgentuuidList();

        $group_uuid_list = array();
        foreach ($result as $op){
            //先判断是否已经生成1级树
            if(!in_array($op['group_uuid'],$group_uuid_list)){
                $group_uuid_list[] = $op['group_uuid'];
                $open_flag = false;
                //第一层分组信息层
                $info[] = array(
                    "id" => $op['group_uuid'],
                    "pId" => 0,
                    "name" => $op['group_name'],
                    "title" => $op['group_name'],
                    "isParent" => true,
                    "uuid" => $op['group_uuid'],
                    "nocheck" => false,
                    "type" => 0,
                    "icon" => "./img/platform/flag.png",
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" => $open_flag,
                    "iconSkin" => 'Windowslogo',
                );
            }
            //判断是否有被勾选 主要是修改的时候需要用到
            $checkflag = false;
            if(!empty($agent_uuids) && in_array($op['agent_uuid'],$agent_uuids)){
                $checkflag = true;
                //这里需要把其父级也设置为勾选状态
                for($i = 0;$i<count($info);$i++){
                    if($op['group_uuid'] == $info[$i]['uuid']){
                        $info[$i]['checked'] = true;
                    }
                }

            }


            //是否授权
            $authorization_module = $op['authorization_module'];
            if(empty($authorization_module)){
                $auth_flag = false;
            }else{
                $authorization_module = json_decode($authorization_module,true);
                $auth_flag = $authorization_module['os'] ? true:false;
            }
            //是否在线
            $online_flag = $op['online_flag']== 1 ? true:false;

            //处理名字
            $titleDes = $this->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            //如果是未授权暂时不加前面提示
            // if($auth_flag == false){

            //     $titleDes = "(".Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'].")".$titleDes;
            // }
            if($online_flag == false){

                $titleDes = $titleDes.Xphp::$_lang['WEB_OS_OFFLINE'];
            }

            //是否在任务中
            if(in_array($op['agent_uuid'],$task_agent_uuid_list)){
                $agentuuidInTask = true;
            }else{
                $agentuuidInTask = false;
            }


            //这里判断有4个条件
            //1 是否在任务中 2是否是修改 3是否授权 4是否离线
            //是否是在任务中
            if(in_array($op['agent_uuid'],$task_agent_uuid_list)){
                //是否是修改
                if(!empty($agent_uuids) && in_array($op['agent_uuid'],$agent_uuids)){

                }

            }
            //只需要判断是否在线
            if(!$online_flag){
                //如果离线 统一禁用
                $chkDisabledflag = true;
            }else{
                //是否在任务中
                if(in_array($op['agent_uuid'],$task_agent_uuid_list)){
                    //查看是否是修改
                    if(!empty($agent_uuids) && in_array($op['agent_uuid'],$agent_uuids)){
                        //是修改而且在任务中
                        $chkDisabledflag = false;
                    }else{
                        //不是修改 但是在任务中
                        $chkDisabledflag = true;//禁用
                    }
                }else{
                    $chkDisabledflag = false;
                }

            }


            //开始组合第二层
            $info[] = array(
                "id" => $op['agent_uuid'],
                "pId" => $op['group_uuid'],
                "name" => $titleDes,
                "title" => $titleDes,
                "isParent" => false,
                "uuid" => $op['agent_uuid'],
                "nocheck" => false,
                "type" => 1,
                "icon" => "./img/os/".$op['os_type'].".png",
                "clickshow" => false,
                "checked" => $checkflag,
                "chkDisabled" => $chkDisabledflag,
                "iconSkin" => $op['os_type'].'logo',
                //是否是在任务中
                'agentuuidInTask' => $agentuuidInTask,
                //是否授权操作系统
                'auth_flag' =>$auth_flag, //false 未授权
                //是否离线
                'online_flag' =>$online_flag, //false 离线
                //传输网络
                "net_model" => $op['net_model'],
            );
        }
        return json_encode($info);

    }

    /*
     * 得到在任务中的主机备份的agentuuid集合
     */
    public function getTaskAgentuuidList(){
        $info = array();
        $sql = "select ol.agent_uuid from os_list ol, bd_task bt where ol.task_uuid = bt.task_uuid and bt.task_type = ?";
        $result = $this->dbSelect($sql,array(Xphp::$_config['TASKTYPE']['OS_BACKUP']));
        if(empty($result)){
            return $info;
        }
        foreach ($result as $each){
            $info[] = $each['agent_uuid'];
        }
        return $info;
    }




    /**
     * 获取代理端的磁盘信息
     * @param unknown $params
     * @$sourceflag 为true时返回不处理的数据  为false返回处理后的数据 默认false
     * @return void|unknown
     */
    public function getOSBackupAgentInfo($params,$sourceflag = false){
        //得到agent_uuid
        $agentuuid = $params['uuid'];
        //得到主机类型
        $osinfo = $this->getOSType($agentuuid);
        $ostype = $osinfo['os_type'];
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $agentuuid;
        $msg = json_encode($pfMSg);
        //同步数据
        $mbResult = $this->mbOSMsg($nodeuuid,$opName,$msg,true);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('OSOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $data = $mbResult['msg'];
        //返回结果到UI
        if(!$result){//如果返回失败
            exit($this->muOpResult($result, $operate, '', '', $mbResult['errorCode']));
        }
        $disk_list =$data['disk_list'];
        if($sourceflag){
            return $disk_list;
        }
        //得到所有的volume_set数据
        $infoVolume = array(); //所有磁盘分区信息集合
        $infoDisk = array(); //所有磁盘信息集合
        $utils = Xphp::instance("Utils");
        foreach ($disk_list as $op){
            //判断是否展示这个卷
            if($op['show_flag'] != 2){
                $infoDisk[] = array(
                    "display_name" => $op['display_name'],
                    "type" => $op['type'],
                    "vendor" => $op['vendor'],
                    "model" => $op['model'],
                    "part_table_type" => $op['part_table_type'],
                    "name" => $op['name'],
                    "path" => $op['path'],
                    "serial_number" => $op['serial_number'],
                    "disk_uuid" => $op['disk_uuid'],
                    "dev_id" => $op['dev_id'],
                    "total_size" => $op['total_size'],
                    "total_size_str" => $utils->calSize($op['total_size']),
                    "allocated_size" => $op['allocated_size'],
                    "logic_sector" => $op['logic_sector'],
                    "physical_sector" => $op['physical_sector'],
                    "mount_point" => $op['mount_point'],
                    "is_slave" => $op['is_slave'],
                    "system_flag" => $op['system_flag'],
                );
            }
            $volume_set = $op['volume_set'];//获取分区信息
            //如果没有分区的信息 则继续下个
            if(empty($volume_set)){
                continue;
            }
            foreach ($volume_set as $one){
                //判断是否展示这个卷
                if($one['show_flag'] != 1){
                    continue;
                }
                //判断类型后根据不同类型展示不同类型
                $infoVolume[] = array(
                    "display_name" => $one['display_name'],
                    "show_flag" => $one['show_flag'],
                    "rd_flag" => $one['rd_flag'],
                    "is_bitLocker" => $one['is_bitLocker'] ? $one['is_bitLocker'] : 2,
                    "system_flag" => $one['system_flag'],
                    "dev_id"=> $one['dev_id'],
                    "type"=> $one['type'],
                    "name"=> $one['name'],
                    "path"=> $one['path'],
                    "vol_uuid"=> $one['vol_uuid'],
                    "mount_point"=> $one['mount_point'],
                    "fs_type"=> $one['fs_type'],
                    "gpt_type"=> $one['gpt_type'],
                    "gpt_id"=> $one['gpt_id'],
                    "gpt_name"=> $one['gpt_name'],
                    "gpt_attribute"=> $one['gpt_attribute'],
                    "total_size"=> $one['total_size'],
                    "total_size_str" => $utils->calSize($one['total_size']),
                    "used_size"=> $one['used_size'],
                    "used_size_str" => $utils->calSize($one['used_size']),
                    "usable_size" =>$utils->calSize($one['total_size'] - $one['used_size']),
                    "start_offset"=> $one['start_offset'],
                    "sector_start"=> $one['sector_start'],
                    "sector_count"=> $one['sector_count'],
                    "hidden_sector"=> $one['hidden_sector'],
                    "is_boot"=> $one['is_boot'],
                    "is_holder"=> $one['is_holder'],
                    "is_slave"=> $one['is_slave'],
                    "iconSkin" => $this->getIconOfData($ostype, $one['system_flag']),
                    "removable_flag" =>  $utils->parseFlagToBool($one['removable_flag']),
                );
            }
        }
        $info = array(
            're' => true,
            'infoDisk' => $infoDisk,
            'infoVolume' => $infoVolume,
        );

        return json_encode($info);

    }


    /**
     * 得到本地节点UUID
     */
    public function getLocalNodeUUID(){
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }


    /**
     * 获取时间点树
     * @param unknown $params node 节点uuid
     * @return string
     */
    public function getTimepointTree($params){
        //-----------------------------------------------------------------
        $storageuuid = $params['storageuuid'];
        //来源
        $recoverflag = $params['recoverflag']; //恢复的树形结构
        $dataflag = $params['dataflag'];//备份数据的树形结构
        $instantflag =  $params['instantflag']? true : false; //是否来自瞬时恢复
        //如果来自恢复则为true  来自备份数据为false
        $nocheckflag = $recoverflag && !$dataflag ? true : false;

        //获取所有时间点信息
        $sql = "select bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,
    			bbt.task_uuid, obt.os_name, obt.dir_path, bbt.real_node_uuid, bsr.node_uuid, obt.os_type,obt.agent_ip,obt.agent_uuid   
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and bbt.import_flag = 2 and 
                      bbt.sub_module_type = 0 and
                      bbt.data_local_flag = ? and bbt.user_uuid is not null and bbt.user_uuid != '' ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET']);
        $flag = Xphp::$_config['FLAG'];
        //备份数据不展示副本相关内容
        if($dataflag){
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], Xphp::$_config['MODULE_TYPE']['OS']));
        }else{
            $sql .= " and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['MODULE_TYPE']['OS']));
        }


        if(!empty($storageuuid)){
            $sql .=" and bsr.storage_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($storageuuid));
        }

        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 备份数据恢复属于操作权限，不能归属于查看
            // 三权模式下的操作员只能查看自身的数据
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到自身的或者管理的用户的的数据
            $userUuidSql = $utils->v1_auth_get_users('osbackup');
            $sqlNew = " and bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
        }

        //admin不能看租户内部的资源
        if (empty($_SESSION['tenantuuid'])) { // 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }

        //如果是瞬时恢复 需要屏蔽云存储的时间点 还需要屏蔽磁带的时间点
        if($instantflag){
            $sql .= " and bsr.storage_type != ? and bsr.storage_type != ? ";
            array_push($sqlParams, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], Xphp::$_config['BD_STORAGE_TYPE']['TAPE']);
        }

        $sql .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql,$sqlParams);


        //所有任务的集合
        $task = array();
        $info = array();
        $os = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $agentList = $this->getAllAgentList();
        foreach ($data as $op){
            $task_uuid =  $op['task_uuid'];
            $taskName = $op['task_name'];
            if(!in_array($task_uuid,$task)){
                $task[] = $task_uuid;
                //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
                $taskAvailable = in_array($task_uuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                //如果是恢复的树  副本归档不显示删除 只显示副本数据和归档数据
                if($recoverflag){
                    // 副本数据
                    if($op['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $op['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                        $name = $taskName . "(" . Xphp::$_lang['UI_COPY_DATA'] . ")";
                    }
                    // 归档数据
                    if($op['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $op['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                        $name = $taskName . "(" . Xphp::$_lang['UI_ARCHIVE_DATA'] . ")";
                    }
                }


                //第一层 任务名称
                $info[] = array(
                    "id" => $op['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => $nocheckflag,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $op['task_create_time']),
                    "isParent" => true,
                );
            }

            if(!in_array($op['task_uuid'].$op['agent_uuid'],$os)){
                $os[] = $op['task_uuid'].$op['agent_uuid'];
                //获取系统类型
                $os_type = $op['os_type'];
                $osTypeName = $this->getOSTypeStr($os_type);
                //第二层 任务系统类型
                $info[] = array(
                    "id" => $op['task_uuid'].$op['agent_uuid'],
                    "pId" => $op['task_uuid'],
                    'name' => $this->getAgentNameByList($agentList,$op['agent_uuid'],$op['os_name'],$op['agent_ip']),
                    "open" => false,
                    "nocheck" => $nocheckflag,
                    "type" => 1,
                    "icon" => './img/os/'.$osTypeName.'.png',
                    "iconSkin" => $osTypeName.'logo',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $op['dir_path'],
                    "taskuuid" => $op['task_uuid'],
                    "agentuuid" => $op['agent_uuid'],
                    "nodeuuid" => $op['node_uuid'],
                    "real_node_uuid" => $op['real_node_uuid'],
                    "createtime" => $this->parseDate($op['task_create_time']),
                    "osType" => $op['os_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            }


        }
        return json_encode($info);
    }


    /**
     * 连接测试
     * @param $params IPList数组
     * return {re:true/false 成功或失败 msg:信息}
     *
     */
    public function linkTest($params,$osmotionflag =  false){
        //时间点uuid集合
        $timepoint_uuid_list = $params['timepointList'];
        //代理主机uuid集合
        $agent_uuid_list = $params['agentList'];
        //检测是否在任务中
        $this->getAgentUsed($agent_uuid_list,$osmotionflag);
        //获取到时间点的数据
        $old_list = $this->getOSBackupDiskInfo($timepoint_uuid_list);
        //获取代理主机数据的方式有两种 一种是调用接口实时获取 一种是数据库获取
        //先写一种实时获取数据的方式 后面有需要可以写实时没获取到就从数据库获取
        $newList = $this->getNewDiskInfo($agent_uuid_list);
        $result = array(
            're' => true,
            'oldList' => $old_list,
            'newList' => $newList,
            'encrypt_flag' => $this->getEncryFlag($timepoint_uuid_list),
        );
        return json_encode($result);
    }

    /**
     * 得到该代理主机是否在以下2种情况中
     * 1备份任务中 任务状态正在进行中
     * 2恢复任务中 任务状态任意
     * 2种情况都不让进入下一步
     */
    private function getAgentUsed($agent_uuid_list,$osmotionflag){
        if(empty($agent_uuid_list)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OS_GET_TIMEPOINT_INFO'], '', 'warning'));
        }
        foreach ($agent_uuid_list as $eachuuid){
            //查找瞬时恢复任务id
            $sql1 =  "select ol.task_uuid from os_list as ol, bd_task as bt where ol.task_uuid = bt.task_uuid and bt.task_type in (49,50) and ol.inst_recovery_agent_uuid = ?";
            $result1 = $this->dbSelect($sql1,array($eachuuid));
            //查找迁移任务id
            $sql2 = "select ol.task_uuid from os_list as ol, bd_task as bt where ol.task_uuid = bt.task_uuid and bt.task_type = 50 and ol.agent_uuid = ?";
            $result2 = $this->dbSelect($sql2,array($eachuuid));
            //查找其他任务id
            $sql3 = "select ol.task_uuid from os_list as ol, bd_task as bt where ol.task_uuid = bt.task_uuid and bt.task_type not in (49,50) and ol.agent_uuid = ?";
            $result3 = $this->dbSelect($sql3,array($eachuuid));
            

            //融合三个sql语句的结合
            $resultTaskSql  = array_merge($result1,$result2,$result3);
            //判断是为空 为空则直接退出
            if(empty($resultTaskSql)){
                return;
            }
            //得到每一个task信息
            foreach ($resultTaskSql as $eachTask){
                //查询每一个task信息
                $taskSql = "select task_name,task_type,task_status from bd_task where task_uuid = ? and module_type = 5";
                $resultSql = $this->dbSelect($taskSql,array($eachTask['task_uuid']));
                if(empty($resultSql)){
                    return;
                }
                foreach ($resultSql as $each){

                    //如果是操作系统迁移  不能在备份/迁移/恢复任务中  可以在瞬时恢复任务的主机中

                    //如果是在备份中并且任务正在进行中
                    if($each['task_type'] == 35 && $each['task_status'] == 2){
                        exit($this->muOpResult(false, Xphp::$_lang['WEB_OS_HOST_IP'], Xphp::$_lang['WEB_OS_IS_TASK'].$each['task_name'].Xphp::$_lang['WEB_OS_WAIT_BACKUP_RETRY'], 'warning'));
                    }

                    //如果是在恢复中
                    if($each['task_type'] == 36){
                        exit($this->muOpResult(false,Xphp::$_lang['WEB_OS_HOST_IP'], Xphp::$_lang['WEB_OS_IS_TASK'].$each['task_name'].Xphp::$_lang['WEB_OS_RECOVERY_TASK_RETRY'], 'warning'));
                    }

                    //如果是瞬时恢复任务中
                    if($each['task_type'] == 49){
                        if(!$osmotionflag){
                            exit($this->muOpResult(false, Xphp::$_lang['WEB_OS_HOST_IP'], Xphp::$_lang['WEB_OS_IS_TASK'].$each['task_name'].Xphp::$_lang['WEB_OS_INST_RECOVERY_TASK_RETRY'], 'warning'));
                        }
                    }

                    //如果是迁移任务中
                    if($each['task_type'] == 50){
                        exit($this->muOpResult(false, Xphp::$_lang['WEB_OS_HOST_IP'], Xphp::$_lang['WEB_OS_IS_TASK'].$each['task_name'].Xphp::$_lang['WEB_OS_MOTION_TASK_RETRY'], 'warning'));
                    }
                }
            }
        }

    }



    /**
     * 获取是否有加密的flag,用于前端显示,适用于1个 ,后面做成多个再改结构
     * @param unknown $timepoint_uuid_list
     */
    private function getEncryFlag($timepoint_uuid_list){
        $flag = 2;
        //由于目前只有一个时间点 就按照一个来处理
        $timepoint_uuid = $timepoint_uuid_list[0];
        if(empty($timepoint_uuid)){
            return $flag;
        }
        $sql = "select detail, encrypted_flag from bd_backup_timepoint where timepoint_uuid = ?";
        $result = $this->dbSelect($sql,array($timepoint_uuid));
        $detail = $result[0]['detail'];
        $detail = json_decode($detail,true);
        $encrypted_flag = $result[0]['encrypted_flag'];
        //开始判断是否有设置密码
        if($encrypted_flag == 1 && $detail['password_auto_flag'] == 2){
            $flag = 1;
        }
        return $flag;
    }

    /**
     * 实时获取物理主机上的数据
     * @param unknown $agentList 代理主机列表
     * diskinfo中[uuid,名称,容量,是否是系统盘]
     * return array
     */
    public function getNewDiskInfo($agentList){
        $getdata = array();
        foreach ($agentList as $oneAgentList){
            $info = array(
                'uuid' => $oneAgentList,
            );
            $getdata[$oneAgentList] = $this->getOSBackupAgentInfo($info,true);
        };
        $newList = array();
        $utils = Xphp::instance('Utils');
        foreach ($getdata as $agentuuid=>$data){
            //得到主机类型
            $osinfo = $this->getOSType($agentuuid);
            $ostype = $osinfo['os_type'];
            //得到分区信息
            $diskInfo = array();
            //得到磁盘信息
            $diskInfo2 = array();
            foreach ($data as $oneDisk){
                //如果为磁盘为空则跳过
                if(empty($oneDisk)){
                    continue;
                }
                $volume_set = $oneDisk['volume_set'];
                foreach ($volume_set as $oneVolume){
                    if($oneVolume['show_flag'] != 1){
                        continue;
                    }
                    //这里通过与值计算看是否是U盘分区,如果是可移动设备则不显示
                    //获取分区的type值
                    $volume_type = $oneVolume['type'];
                    //后端给的一个检测是否是移动设备的值(应该是一个移动设备的type)
                    $num_type = 0x8000;
                    //计算与运算
                    $result_type = $volume_type & $num_type;
                    // if($result_type != 0){
                    //     continue;
                    // }
                    $removabledes = "";
                    if($oneVolume['removable_flag'] == 1){
                        $removabledes = "(". Xphp::$_lang['WEB_OS_REMOVE_DEVICE'] .")";
                    }
                    $diskInfo[] = array(
                        $oneVolume['vol_uuid'],
                        $oneVolume['display_name'].$removabledes,
                        $oneVolume['total_size'],
                        $oneVolume['system_flag'],
                        $utils->calSize($oneVolume['total_size'], true)

                    );
                }
                //有可能分区显示 但是磁盘不显示的情况  这种 在最下面处理
                if($oneDisk['show_flag'] != 1){
                    continue;
                }
                //这里通过和后端对接 后端type枚举19为可移动设备,这里要排除掉可移动设备
                // if($oneDisk['type'] == 19){
                //     continue;
                // }
                $removabledes2 = "";
                if($oneDisk['type'] == 19){
                    $removabledes2 = "(". Xphp::$_lang['WEB_OS_REMOVE_DEVICE'] .")";
                }
                $diskInfo2[] = array(
                    $oneDisk['disk_uuid'],
                    $oneDisk['display_name'].$removabledes2,
                    $oneDisk['total_size'],
                    $oneDisk['system_flag'],
                    $utils->calSize($oneDisk['total_size'], true)

                );
            }
            //得到的数组看卷是否为空 如果为空 则开启重建分区
            $builtFlag = false;
            if(empty($diskInfo)){
                $builtFlag = true;
            }
            //组合数据
            $newList[] = array(
                'net_model' => $osinfo['net_model'],
                'agent_uuid' => $agentuuid,
                'group_uuid' => $this->agentuuidGetgroupuuid($agentuuid),
                'agent_name' => $this->getAgentName($osinfo['hostname'], $osinfo['agent_name'], $osinfo['ip']),
                'builtFlag' => $builtFlag,
                'diskInfo' => $diskInfo,
                'diskInfo2' => $diskInfo2,
            );
        }
        return $newList;
    }

    /**
     * 通过agentuuid获取到groupuuid
     * @param unknown $agentuuid
     */
    private function agentuuidGetgroupuuid($agentuuid){
        $sql = "select group_uuid from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql,array($agentuuid));
        $group_uuid = $result[0]['group_uuid'];
        return $group_uuid;
    }


    /**
     * 异步获取时间点数
     * @param unknown $params
     * taskuuid agentuuid nodeuuid  任务uuid 代理机uuid 节点uuid   3者确定一条数据
     * $recoverflag boolean
     * $dataflag  boolean
     */
    public function getSyncTimepoint($params){
        //----------------------------
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $storageuuid = $params['storageuuid'];
        $oschecked = $params['oschecked']; //父级是否被勾选上
        //来源
        $recoverflag = $params['recoverflag']; //恢复的树形结构
        $dataflag = $params['dataflag'];//备份数据的树形结构
        $instantflag =  $params['instantflag']? true : false; //是否来自瞬时恢复
        $agentList = $this->getAgentuuidList(); //得到在任务中的一些代理机uuid
        

        //如果来自恢复则为true  来自备份数据为false
        $nocheckflag = $recoverflag && !$dataflag ? true : false;

        $sql = "select bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,bbt.backup_mode,
    			     bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.detail,bbt.importance_flag, bbt.remarks,bbt.archive_flag,bbt.deleted_flag, 
                     bbt.task_uuid, obt.os_timepoint_id, obt.os_name, obt.dir_path, bbt.real_node_uuid, bsr.node_uuid, obt.os_type,obt.agent_ip ,obt.agent_uuid , bsr.status, bsr.storage_type,
                     bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status 
                from os_backup_timepoint obt, bd_storage_resource bsr, bd_backup_timepoint bbt
                left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and
                      bbt.import_flag = 2 and   
                      bbt.module_type in (".Xphp::$_config['MODULE_TYPE']['OS'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'].") and
                      bbt.data_local_flag = ? and bbt.task_uuid = ? and 
                      obt.agent_uuid = ?";
        $sqlparams = array(Xphp::$_config['FLAG']['SET'], $taskuuid,$agentuuid);
        if(!empty($storageuuid)){
            $sql .=" and bsr.storage_uuid = ? ";
            $sqlparams = array_merge($sqlparams, array($storageuuid));
        }
        //如果是瞬时恢复 需要屏蔽云存储的时间点 还需要屏蔽磁带上的时间点
        if($instantflag){
            $sql .= " and bsr.storage_type != ? and bsr.storage_type != ? ";
            $sqlparams = array_merge($sqlparams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], Xphp::$_config['BD_STORAGE_TYPE']['TAPE']));
        }
        $sql .=" order by timepoint asc";
        $pointData = $this->dbSelect($sql,$sqlparams);

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        foreach ($pointData as $point){
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            }else{
                $unfullList[] = $point;

            }
        }
        while (!empty($unfullList)){
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key1 => $unfull){
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $key => $value){
                    if($dependId == $key){
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                        $unfullList = array_splice($unfullList, $key1, 1);
                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) break;

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


        foreach ($pointData as $op){
            $pointMixedStatus = $jobHandler->getTimePointStatus($op['merge_status'], $op['operation_status'], $op['virus_scan_status'], $op['integrity_check_status']);
            //----判断是否时间点是否可用 true表示禁用
            $availableFlag = true;

            //如果时间点正在合并且不可用
            if($op['archive_flag'] == Xphp::$_config['FLAG']['SET'] || $op['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']){
                $availableFlag = false;
            }


            $osTypeName = $this->getOSTypeStr($op['os_type']);

            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
            $details = json_decode($op['detail'],true);
            $name = $this->parseDate($op['timepoint'])."(".$this->getTimepointTypeDes($op['backup_mode']).")";

            //如果在合并中
            if($op['archive_flag'] == Xphp::$_config['FLAG']['SET']){
                $name .= Xphp::$_lang['WEB_OS_MERGE'];
            }else if($op['archive_flag'] == Xphp::$_config['FLAG']['UNSET'] && $op['deleted_flag'] == Xphp::$_config['FLAG']['SET']){
                $name .= "(" . Xphp::$_lang['UI_PUBLIC_MERGE_ERROR'] . ")";
                //如果是在恢复页面不可用
                if($recoverflag){
                    $availableFlag = false;
                }
            }

            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
            if(!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])){
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            }else{
                $encrypted_flag = false;
            }

            //是否是数据备份
            $mark = "";
            if($dataflag){
                //添加GFS标识
                $mark .= $vmHandler->pGetTimepointMark(false,false,false,$utils->parseFlagToBool($op['importance_flag']));

            }

            //其父级是否被勾选
            if(!empty($oschecked)){
                $oschecked = true;
            }else{
                $oschecked = false;
            }





            if($op['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                if(!in_array($op['timepoint_uuid'], $timepoint)){
                    $timepoint[] =  $op['timepoint_uuid'];
                    $info[] = array(
                        "agentuuid"=> $agentuuid,
                        "checked"=> $oschecked,
                        "createtime" => $this->parseDate($op['task_create_time']),
                        "osname"=> $this->getNameByAgentList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                        "ostype"=> $op['os_type'],
                        "icon"=> "./img/platform/timepoint-f.png",
                        "id"=> $op['timepoint_uuid'],
                        "name"=> $name.$mark,
                        "oldname" => $name,
                        "nodeuuid"=> $op['node_uuid'],
                        "real_node_uuid" => $op['real_node_uuid'],
                        "pId"=> $op['task_uuid'].$op['agent_uuid'],
                        "path"=> $op['dir_path'],
                        "pointname"=> $this->parseDate($op['task_create_time']),
                        "taskuuid"=> $taskuuid,
                        "timepointuuid"=> $op['timepoint_uuid'],
                        "title"=> Xphp::$_lang['WEB_OS_SOURCE_PATH']."=> ".$op['dir_path'],
                        "type"=> 2,
                        "iconSkin" => $osTypeName.'logo',
                        "chkDisabled" => !$availableFlag,
                        //是否是在任务中
                        'agentuuidInTask' => $chkDisabledflag,
                        "password_auto_flag" => intval($details['password_auto_flag'])==1 ? true: false,
                        "encrypted_flag" => $encrypted_flag,
                        "storage_type"=> $op['storage_type'], 
                        "point_status" => $pointMixedStatus['status'],
                        "available_flag" => $pointMixedStatus['available_flag'],
                    );
                    continue;
                }
            }
            
            
            //第四层 增备差异点
            $info[] = array(
                "agentuuid"=> $agentuuid,
                "checked"=> $oschecked,
                "createtime" => $this->parseDate($op['task_create_time']),
                "osname"=> $this->getNameByAgentList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                "ostype"=> $op['os_type'],
                "icon"=> $this->getTimepointIcon($op['backup_mode']),
                "id"=> $op['timepoint_uuid'],
                "name"=> $name.$mark,
                "oldname" => $name,
                "nodeuuid"=> $op['node_uuid'],
                "real_node_uuid" => $op['real_node_uuid'],
                "pId"=> $fulluuidList[$op['timepoint_uuid']],
                "path"=> $op['dir_path'],
                "pointname"=> $this->parseDate($op['task_create_time']),
                "taskuuid"=> $taskuuid,
                "timepointuuid"=> $op['timepoint_uuid'],
                "title"=> Xphp::$_lang['WEB_OS_SOURCE_PATH']."=> ".$op['dir_path'],
                "type"=> 3,
//                 "nocheck" => !$nocheckflag,
                "nocheck" => false,
                "iconSkin" => $osTypeName.'logo',
                "chkDisabled" => $dataflag ? true : !$availableFlag,
                //是否是在任务中
                'agentuuidInTask' => $chkDisabledflag,
                "password_auto_flag" => intval($details['password_auto_flag'])==1 ? true: false,
                "encrypted_flag" => $encrypted_flag,
                "storage_type"=> $op['storage_type'],
                "point_status" => $pointMixedStatus['status'],
                "available_flag" => $pointMixedStatus['available_flag'],
            );

        }


        $msg = array(
            're' => true,
            'msg' => $info,
        );
        return json_encode($msg);


    }


    /**
     * 得到所有在任务中的agentuuid集合 ,此主机在任务中并且是为在运行状态的
     *
     */
    public function getAgentuuidList(){
        $info = array();
        $sql = "select ol.agent_uuid from os_list ol, bd_task bt where ol.task_uuid = bt.task_uuid and ((bt.task_type = ? and bt.task_status = ?) or bt.task_type = ?)";
        $result = $this->dbSelect($sql,array(Xphp::$_config['TASKTYPE']['OS_BACKUP'],Xphp::$_config['TASKSTATUS']['RUNNING'],Xphp::$_config['TASKTYPE']['OS_RECOVERY']));
        if(empty($result)){
            return $info;
        }
        foreach ($result as $each){
            $info[] = $each['agent_uuid'];
        };
        return $info;
    }




    /**
     * 得到当前所有任务的UUID
     * return array
     */
    public function getCurrentAllTaskUUID(){
        $taskuuid = array();
        $sql = "select task_uuid from bd_task where (module_type = ? or module_type = ?) and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['OS'],Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'],Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d){
            $taskuuid[] = $d['task_uuid'];
        }
        return $taskuuid;
    }



    /*
     * 得到主机保护-备份数据-任务列表
     * taskuuid agentuuid nodeuuid
     * 编号 时间点 类型 数据大小 写入大小 所在存储 分区信息 备注 操作 星标
     * return
     */
    public function getOsTimepointGrid($params){
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        // $nodeuuid = $params['nodeuuid'];
        $copyFlag = $params['copyFlag'];    //副本标记
        $accurateFlag = $params['accurateFlag'];  //操作系统备份数据页面标记
        $search = $params['search'];
        $storageuuid = $params['storageuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $localflag = intval($params['localflag']); //本地标志
        $sql = "select unix_timestamp(bbt.timepoint) timepoint, bbt.remarks, bbt.importance_flag, bbt.total_size, bbt.write_size, bbt.storage_uuid, bbt.task_uuid, bbt.timepoint_uuid,
                        bbt.backup_mode,bbt.user_uuid,
                        obt.os_type, obt.os_config, obt.agent_uuid, bsr.storage_type
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
        			  bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and
                      bbt.import_flag = 2 and   
                      bbt.task_uuid = ? and obt.agent_uuid = ? ";
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                     where bbt.timepoint_uuid = obt.timepoint_uuid and
            			   bbt.storage_uuid = bsr.storage_uuid and
                           bbt.available_flag = 1 and
                           bbt.import_flag = 2 and  
                           bbt.task_uuid = ? and obt.agent_uuid = ? ";

        $sqlSortParams = array('bbt.timepoint','obt.os_type','bbt.total_size','bbt.write_size','','','','');
        $sqlParams = array($taskuuid,$agentuuid);
        $sqlCountParams = array($taskuuid,$agentuuid);

        // if(!$copyFlag && !empty($nodeuuid)){
        //     $sql .=" and (bsr.node_uuid = ? or bbt.real_node_uuid = ?) ";
        //     $sqlCount .=" and (bsr.node_uuid = ? or bbt.real_node_uuid = ?) ";
        //     $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
        //     $sqlCountParams = array_merge($sqlCountParams,array($nodeuuid, $nodeuuid));
        // }
        //新增sql 为了得到最新的增量备份点
        $sqlincre  = $sql . " and bbt.backup_mode = ? order by bbt.timepoint desc";
        $sqlincreParams = array_merge($sqlParams, array(Xphp::$_config['BACKUP_MODE']['INCREMENTAL']));
        //如果带有搜索条件
        if(!empty($search)){
            //如果开始时间和结束时间都有 则添加时间查询
            if(!empty($search['startTime']) && !empty($search['endTime'])){
                $sql .= " and bbt.timepoint between ? and ? ";
                $sqlCount .= " and bbt.timepoint between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($search['startTime'],$search['endTime']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['startTime'],$search['endTime']));
            }
            //如果有类型 则添加类型
            if(!empty($search['timepointType'])){
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($search['timepointType']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['timepointType']));
            }
            //如果有永久标记点 则添加永久标记的搜索
            if(!empty($search['forever']) && $search['forever'] != 2){
                $sql .= " and bbt.importance_flag = ? ";
                $sqlCount .= " and bbt.importance_flag = ? ";
                $sqlParams = array_merge($sqlParams, array($search['forever']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['forever']));
            }

        }

        //区分异地还是本地
        if(!empty($localflag)){
            $sql .= " and bbt.data_local_flag = ? ";
            $sqlParams = array_merge($sqlParams, array($localflag));
        }
        // if($copyFlag){

        //     if($storageuuid && !empty($storageuuid)){
        //         $sql .=" and bsr.storage_uuid = ? ";
        //         $sqlCount .= " and bsr.storage_uuid = ? ";
        //         $sqlParams = array_merge($sqlParams, array($storageuuid));
        //         $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
        //     }
        // }
        if($storageuuid && !empty($storageuuid)){
            $sql .=" and bsr.storage_uuid = ? ";
            $sqlCount .= " and bsr.storage_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($storageuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
        }

        $sql .= " order by $sqlSortParams[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $dataincre  = $this->dbSelect($sqlincre,$sqlincreParams);
        //得到最新增量备份时间点id
        if(!empty($dataincre)){
            $latestincretpuuid  =  $dataincre[0]['timepoint_uuid'];
        }
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $fileHandler = Xphp::instance('FileHandler');
        $records = array("data" => array());
        $i = 1;
        
        $userUuidList = array_column($data, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";
        $sql = "select user_name, user_uuid from bd_user where user_uuid in ($userUuids)";
        $userData = $this->dbSelect($sql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }

        foreach ($data as $d){
            $mark =  $vmHandler->pGetTimepointMark(false, false, false,$utils->parseFlagToBool($d['importance_flag']));
            $remark = "";   //备注
            $op = array(1,2);  //1 备注 2 删除 3设置标记
            //时间点在合并中，不让删除
            //如果是最新增量备份时间点不能让他删除
            if($d['archive_flag'] == Xphp::$_config['FLAG']['SET'] || $copyFlag || $d['timepoint_uuid'] == $latestincretpuuid){
                $op = array(1);
            }
            //如果是存储在云存储上和磁带上的，不允许删除  
            if($d['storage_type'] ==  Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'] || $d['storage_type'] ==  Xphp::$_config['BD_STORAGE_TYPE']['TAPE']){
                $op = array(1);
            }

            //不是异地存储并且不是磁带可以标记永久增量
            if(intval($d['storage_type']) != Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'] && intval($d['storage_type']) != Xphp::$_config['BD_STORAGE_TYPE']['TAPE']){
                $op = array_merge($op, array(3));
            }

            //添加备注
            if(!empty($d['remarks'])){
                //根据标记是否显示固定备注显示高度
                $top ="";
                if(!empty($mark)){
                    $top ="top:-4px;";
                }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover"
                            data-placement="right" data-content="'.preg_replace('/\"/', "'", $d['remarks']).'"><i class="viconfont vicon-remark-info"></i></a>';
            }

            $records["data"][] = array(
                //编号
                // '<span tid="'.$d['timepoint_uuid'].'" sid="'.$d['storage_uuid'].'">'.$i++.'</span>',
                //时间点
                '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['timepoint']) . '</span><br>'.$mark.$remark,  //隐藏展示时间点uuid出来,方便运维
                //类型
                $this->getTimepointTypeDes($d['backup_mode']),
                //数据大小
                $utils->calSize($d['total_size'], true),
                //写入大小
                $utils->calSize($d['write_size'], true),
                //所在存储
                $fileHandler->getStorageName($d['storage_uuid'],$d['timepoint_uuid']),
                //分区信息
                $this->getVolStr(json_decode($d['os_config'],true)),
                //所有者
                $userMapping[$d['user_uuid']],
                //额外信息
                array(
                    'uuid'=>$d['timepoint_uuid'],
                    'timepointuuid'=>$d['timepoint_uuid'],
                    'ostype'=>$d['os_type'],
                    'agentuuid'=>$agentuuid,
                    'taskuuid'=>$taskuuid,
                    'backupmode'=> $d['backup_mode'],
                    'remark' => $d['remarks'],
                    'weekly_flag' => false,
                    'monthly_flag' => false,
                    'yearly_flag' => false,
                    'importance_flag' => $utils->parseFlagToBool($d['importance_flag']),
                    'operate' => $op
                ),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];

        return  json_encode($records);


    }

    /**
     * 得到分区描述
     * @param unknown $list
     */
    private function getVolStr($list){
        $list = $list['volumes'];
        $info = array();
        if(empty($list)){
            return $info;
        }
        foreach ($list as $each){
            $str = $each['display_name'];
//             if(!empty($each['mount_point'])){
//                 $str .= "(".$each['mount_point'].")";
//             }
            $info[] = $str;
        }
        return $info;

    }



    /**
     * 获取主机类型
     * @param unknown $os_type 主机类型
     * return 主机文字描述
     */
    public function getOSTypeStr($os_type){
        $TypeStr = '';
        switch ($os_type){
            case 1:
                $TypeStr = 'Windows';
                break;
            case 2:
                $TypeStr = 'Linux';
                break;
            default:
                $TypeStr = "unknown";
                break;
        };
        return $TypeStr;
    }

    /**
     * 得到备份时间点类型描述
     * @param unknown $backup_mode
     * return
     */
    public function getTimepointTypeDes($backup_mode){
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des ='';
        $des = $pfDes['BACKUP_MODE_DES'][$backup_mode];
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }

    /**
     * 根据备份模式得到时间点图标
     * @param int $backupMode
     */
    public function getTimepointIcon($backupMode){
        $backupMode = intval($backupMode);
        $icon = "./img/platform/timepoint.png";
        switch($backupMode){
            case Xphp::$_config['BACKUP_MODE']['FULL']:
                $icon = "./img/platform/timepoint-f.png";
                break;
            case Xphp::$_config['BACKUP_MODE']['INCREMENTAL']:
                $icon = "./img/platform/timepoint-i.png";
                break;
            case Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']:
                $icon = "./img/platform/timepoint-d.png";
                break;
        }
        return $icon;
    }


    /**
     * 备份点修改备注信息
     * @param unknown $params
     * taskuuid agentuuid remark
     */
    public function remarkTimepoint($params){
        $remark = $params['remark'];
        $timepointuuid = $params['timepointuuid'];
        $sql = "update bd_backup_timepoint set remarks = ? where timepoint_uuid = ?";
        $result = $this->dbExec($sql,array($remark,$timepointuuid));
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['WEB_OS_EDIT_REMARK_INFO'],"" , "success");
        }else{
            return $this->muOpResult(false, Xphp::$_lang['WEB_OS_EDIT_REMARK_INFO'],"" , "warning");
        }

    }


    /**
     * 根据时间点得到此时间点备份的分区或磁盘信息
     * $params 一个timepointuuid的数组 适应后面多对多
     * $params [timepointuuid,timepointuuid,timepointuuid]
     * return 返回一个数组 分区磁盘信息
     */
    public function getOSBackupDiskInfo($params){
//         $timepointuuid_list = $params['pointList'];
        $timepointuuid_list = $params;
        $this->paramsCheck($timepointuuid_list);
        $paramsList =  implode("','", $timepointuuid_list);
        $sql = "select obt.timepoint_uuid, obt.agent_uuid, obt.os_name, obt.agent_ip, obt.os_type, obt.os_config, 
                unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode   
                from os_backup_timepoint obt, bd_backup_timepoint bbt  
                where obt.timepoint_uuid = bbt.timepoint_uuid 
                and obt.timepoint_uuid in ('".$paramsList."')";
        $result = $this->dbSelect($sql,array());
        if(empty($result)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OS_GET_TIMEPOINT_INFO'], '', 'warning'));
        }
        $oldList = array();
        $utils = Xphp::instance('Utils');
        foreach ($result as $d){
            $agent_uuid = $d['agent_uuid'];
            $osinfo = $this->getOSType($agent_uuid);
            $ostype = $osinfo['os_type'];
            $os_config = json_decode($d['os_config'],true);
            //得到分区信息
            $diskInfo = array();
            foreach ($os_config['disk_list'] as $oneDisk){
                $volume_set = $oneDisk['volume_set'];
                foreach ($volume_set as $oneVolume){
                    if($oneVolume['show_flag'] != 1){
                        continue;
                    }
                    $diskInfo[] = array(
                        $oneVolume['vol_uuid'],
                        $oneVolume['display_name'],
                        $oneVolume['total_size'],
                        $oneVolume['system_flag'],
                        $utils->calSize($oneVolume['total_size'], true),
                        $ostype,
                        $oneVolume['mount_point'],
                    );
                }
            }
            //组合数据
            $oldList[] = array(
                'os_uuid' => $d['timepoint_uuid'],
                'os_name' => $d['agent_ip'],
                'os_str' => $this->parseDate($d['timepoint'])."(".$this->getTimepointTypeDes($d['backup_mode']).")",
                'diskInfo' => $diskInfo,
                'agent_uuid' => $d['agent_uuid'],
                'os_type' => $d['os_type'],
            );
        }
//         return json_encode($oldList);
        return $oldList;
    }
    
    
    
    /**
     * 获取主机任务信息(修改任务用)
     */
    public function getBackupTaskAllInfo($params){
        $vmHandler = Xphp::instance('Vmhandler');
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, bt.node_pool_uuid, bt.storage_pool_uuid, bt.ignore_resource_limiting_flag, 
                       ot.serial_snapshot_flag, ot.cbt_flag,ot.transport_priority, ot.valid_data_flag,ot.silent_snapshot_flag,
                       ol.agent_uuid, ol.dir_path, ol.exclude_devices_list,
                	   brs.strategy_type, brs.number, brs.strategy_mode,
                	   bts.encrypt_flag, bts.compress_flag,bts.network_uuid, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method, bts.network_pool_uuid,
                	   bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.compress_method, bss.encrypted_flag, bss.password_auto_flag, bss.password, bss.encrypt_method,
                       bsr.storage_type,
                       bres.network_retry_times,bres.network_retry_interval,bres.op_retry_times,bres.op_retry_interval,bres.task_retry_object,bres.task_retry_times, bres.task_retry_interval 
                from os_task ot, os_list ol, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss, bd_retry_strategy bres, bd_task bt 
                left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
                where bt.task_uuid = ot.task_uuid
                and ot.task_uuid = ol.task_uuid 
                and bt.task_uuid = bres.task_uuid 
                and bt.strategy_id = brs.strategy_id
                and bt.strategy_id = bts.strategy_id
                and bt.strategy_id = bss.strategy_id
                and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        
        $info = array();
        $utils = Xphp::instance('Utils');
        // 查询存储池类型
        $storagePoolType = 0;
        if ($data[0]['storage_pool_uuid']) {
            $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$data[0]['storage_pool_uuid']]);
            $storagePoolType = $storagePoolData[0]['storage_pool_type'];
        }
        if($data){
            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
//                 //虚拟化类型
//                 'hypervisor' => $data[0]['hypervisor_type'],
//                 //appliance
//                 'applianceuuid' => $data[0]['appliance_uuid'],
                //保留策略
                'brs' => array(
                    'type' => intval($data[0]['strategy_type']),
                    'strategyMode' => intval($data[0]['strategy_mode']),
                    'number' => intval($data[0]['number']),
                ),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,  // 需要返回存储池类别
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
                    'compress' => $utils->parseFlagToBool($data[0]['compress_flag']),
//                     'mode' => $this->getTransportModeToArr($data[0]['transport_priority'],$data[0]['hypervisor_type']),
                    'network' => $data[0]['network_uuid'],
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                    'network_pool_uuid' => $data[0]['network_pool_uuid'],
                ),
                //存储策略
                'bss' => array(
                    'deduplication' => $utils->parseFlagToBool($data[0]['deduplication_flag']),
                    'blocksize' => intval($data[0]['block_size'])/1024,
                    'compress' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'password_auto_flag' => $utils->parseFlagToBool($data[0]['password_auto_flag']),
                    'password' =>  base64_encode($utils->ptPassDecrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method']),
                ),
                //备份模式
                'mode' => array(
                    'threadnum' => intval($data[0]['thread_num']),      //线程数量
                    //传输模式
                    'transport_priority' => intval($data[0]['transport_priority']),
                    //快照
                    'serial_snapshot_flag' => $utils->parseFlagToBool($data[0]['serial_snapshot_flag']),
                    //cbt
                    'cbt_flag' => $utils->parseFlagToBool($data[0]['cbt_flag']),
                    //获取有效数据
                    'valid_data_flag' => $utils->parseFlagToBool($data[0]['valid_data_flag']),
                    //静默快照
                    'silent_snapshot_flag' => $utils->parseFlagToBool($data[0]['silent_snapshot_flag']),

                ),
//                 //虚拟机信息
//                 'vm_info' => $this->getVMEditInfo($taskUUID),
                //时间策略
                'timestrategy' =>  $vmHandler->getTimeStrategyInfo($data[0]['strategy_id']),
                //备份方式
                'timeStrategyBackupType' => $vmHandler->getTimeStrategyBackupType($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $vmHandler->getSpeedGlobalStrategyInfo($taskUUID),
//                 'nosnapshot' => $this->getXenSnapshotFlag($taskUUID),
//                 'backup_server_ip' => $data[0]['backup_server_ip'],
//                 'transport_ip_segment' => $data[0]['transport_ip_segment']
                'backup_oss_info' => $this->getBackupOssInfo($taskUUID),
                //重试策略
                'retry_strategy' => Xphp::instance('JobHandler')->groupRetryStrategyInfo($data[0]),
                'ignore_resource_limiting_flag' => $utils->parseFlagToBool($data[0]['ignore_resource_limiting_flag']),
            );
        }
        return json_encode($info);
    }
    
    
    /**
     * 得到主机排出分区信息
     * @param unknown $taskUUID
     */
    public function getBackupOssInfo($taskUUID){
        $info = array();
        $sql = "select agent_uuid, os_name, agent_ip, dir_path, exclude_devices_list from os_list where task_uuid = ?";
        $result = $this->dbSelect($sql,array($taskUUID));
        $agentList = $this->getAllAgentList();
        foreach ($result as $each){
            $disk_value = json_decode($each['exclude_devices_list'],true);
            $disk_list = array();
            $disk_des = array();
            foreach ($disk_value as $one){
                $disk_list[] = $one['volume_uuid'];
                $disk_des[] = $one['volume_des'];
            }
            $info[] = array(
                'agent_group_uuid' => $this->agentuuidGetgroupuuid($each['agent_uuid']),
                'agent_uuid' => $each['agent_uuid'],
                'agent_name' => $this->getAgentNameByList($agentList, $each['agent_uuid'], $each['os_name'], $each['agent_ip']),
                'os_name' => $each['os_name'],
                'agent_ip' => $each['agent_ip'],
                'os_config' => array(
                    'disk_list' => $disk_list,
                    'disk_des' => $disk_des,
                    'disk_value' => $disk_value,
                ),
                'dir_path' => $each['dir_path'],
                
            );
        }
        return $info;
    }
    
    
    
    
    
    
    /**
     * 根据策略id得到时间策略信息
     * @param int $strategyID
     */
    public function getTimeStrategyInfo($strategyID){
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $info = array();
        
        if(1 == count($data) && Xphp::$_config['STRATEGY_TYPE']['ONCE'] == $data[0]['strategy_type']){
            //如果是一次性策略(只有一条记录,并且策略类型为一次性策略)
            $info['type'] = 'oncetime';
            $info['data'] = $data[0]['start_time'];
        }else{
            //如果是按时间策略
            $info['type'] = 'strategy';
            $strategyData = array();
            $utils = Xphp::instance('Utils');
            //可能有多个策略类型(每天,每周,每月)
            foreach ($data as $d){
                if($d['roll_flag'] == Xphp::$_config['FLAG']['SET']){
                    $rollInterval = $d['roll_interval'];
                    $endTime = $d['roll_end_time'];
                }else{
                    $rollInterval = '3600';
                    $endTime = '23:59:59';
                }
                $strategyData[] = array(
                    'start_time' => $d['start_time'],
                    'roll_flag' => $utils->parseFlagToBool($d['roll_flag']),
                    'roll_interval' => $utils->secToTime($rollInterval),
                    'roll_end_time' => $endTime,
                    'mode' => $d['mode'],
                    'strategy_type' => intval($d['strategy_type']),
                    'days' => $this->parseTimeStrategyDay($d['days']),
                    'frequency' => $this->parseTimeStrategyFrequency($d['strategy_type'], $d['days'])
                );
            }
            $info['data'] = $strategyData;
        }
        return $info;
    }
    
    
    /**
     * 获取任务限速策略列表信息
     * @param string $taskuuid
     */
    public function getSpeedStrategyInfo($taskuuid){
        $sql = "select strategy_uuid, speed_limited_value, start_time, end_time, days, remark, strategy_type from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        $utils = Xphp::instance('Utils');
        foreach($data as $d){
            $value = intval($d['speed_limited_value']);
            $valueList = $utils->calSizeToValueAndUnit($value);
            $info[] = array(
                'uuid' => $d['strategy_uuid'],
                'type' => $d['strategy_type'],
                'startTime' => $d['start_time'],
                'endTime' => $d['end_time'],
                'days' => $this->parseSpeedStrategyDay($d['days']),
                'value' => $value,
                'des' => $d['remark'],
                'unit' => $valueList['unit'].'/s',
                'speednum' => intval($valueList['value'])
            );
        }
        return $info;
    }
    
    
   
    
    
    /**
     * 删除单个时间点
     * @param unknown $params
     * @return string
     */
    public function deleteTimepoint($params){
        //获取单个时间点
        $pointUUID = $params['uuid'];
        //得到agentuuid
        $agentuuid = $params['agentuuid'];
        //得到任务uuid
        $taskuuid = $params['taskuuid'];
        //检测参数是否存在
        $this->paramsCheck($pointUUID);
        //查询时间点相关信息 用于系统日志描述
        $sql = "select bbt.timepoint_uuid, bbt.timepoint, bbt.task_name, bbt.backup_mode, obt.os_name from bd_backup_timepoint bbt, os_backup_timepoint obt where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID));

        //检测存储是否是磁带相关的
        $timepoint_uuid_list_check_storage = array($data[0]['timepoint_uuid']);
        $this->checkTimepointStorage($timepoint_uuid_list_check_storage);

        //权限重构
        $userUuidData = $this->dbSelect("select user_uuid from bd_backup_timepoint where timepoint_uuid in ('$pointUUID')");
        $this->checkOsOperatePermission(array_column($userUuidData, 'user_uuid'));
        //得到操作码
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [Xphp::$_user['useruuid'], $pointUUID]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'],'error'));
            }
        }

        //组装消息
        $msg = array($pointUUID);
        $msg = json_encode(array('timepoint_uuids'=>$msg));
        //发送删除消息到后台
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbOSMsg($nodeuuid,$opName, $msg);
        //得到返回的消息
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        //得到系统日志中的参数
        $timepointDes = $data[0]['timepoint']."(".$pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])].Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'].")";
        $descriptionParam = array($timepointDes, $data[0]['task_name'], $data[0]['os_name']);
        //返回结果到UI
        if($result){
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_OS_TIMEPOINT', $descriptionParam);
            //组合额外的消息
            $sqlCount = "select count(id) as total from bd_backup_timepoint where timepoint_uuid = ?";
            $dataCount = $this->dbSelect($sqlCount, array($pointUUID));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count"=>intval($count), "id"=>$pointUUID));
        }else{
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_OS_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    
    /**
     * 删除批量备份时间点
     * @param array $params 二维数组
     * 如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     */
    public function deleteSelectTimepoint($params){
        //权限检查
        //         $roleHandler = Xphp::instance('RoleHandler');
        //         $roleHandler->pOperationPermissionCheckExit("p_vmdata_detele");
        //得到二维数组
        //遍历，并按节点uuid分组
        //发送删除消息到不同的节点。删除消息array(timepointuuid,timepointuuid,timepointuuid,timepointuuid)
        $timepointList = $params['timepointlist'];
        $osList = $params['oslist'];
        $selectTimepoints = array();
        if(!empty($osList)){
            foreach ($osList as $os){
                $selectTimepoints = $this->getTimepointByOS($os);
                $timepointList =  array_merge($timepointList, $selectTimepoints);
            }
        }
        $utils = Xphp::instance('Utils');
        $timepointList = $utils->arraySort($timepointList, 'nodeuuid', '', 0, -1);
        $info = array();
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach($timepointList as $d){
            $i++;
            if(!in_array($d['nodeuuid'], $nodeuuids)){
                $nodeuuids[] = $d['nodeuuid'];
                $info[] =  array(
                    "nodeuuid" => $d['nodeuuid'],
                    "ostype" => $d['ostype']);
                if(!empty($timepointuuid)){
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }
                
                $timepointuuid[] = $d['timepointuuid'];
            }else{
                $timepointuuid[] = $d['timepointuuid'];
            }
            // 时间点信息
            $details .= $this->getPointDetails($i,$d['timepointuuid']);
        }

        $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'],'error'));
            }
        }

        $timepointuuids[] = $timepointuuid;

        //检测时间点是否在磁带上
        $this->checkTimepointStorage($timepointuuids);

        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
//         $this->paramsCheck( $timepointuuids[0][0],$nodeuuids[0]);
        //检测是否在任务中在任务就直接返回
        $uuidList = array();
        foreach($timepointuuids as $d){

            $uuidList = array_merge($uuidList,$d);
        }
         //权限重构
         $timepointuuidstr = implode("','", $uuidList);
         $userUuidData = $this->dbSelect("select user_uuid from bd_backup_timepoint where timepoint_uuid in ('$timepointuuidstr')");
         $this->checkOsOperatePermission(array_column($userUuidData, 'user_uuid'));

        //检查时间点是否有任务存在
        $this->taskExist($uuidList, $operate);
        $countPoint = 0;
        $nodeHandler = Xphp::instance('NodeHandler');
        for($i=0;$i<$nodeuuidsCount;$i++){
            $msg = $timepointuuids[$i];
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            $msg = json_encode($msg);
            if(empty($nodeuuids[$i])){
                $nodeuuids[$i] = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointuuids[$i][0]);
            }
            $mbResult = $this->mbOSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, Xphp::$_lang['WEB_PLATFORM_DES_OS']);
        //返回结果到UI
        if($result){
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        }else{
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }


     /**
     * 检测时间点是否在磁带上,如果在磁带上则退出不让其删除并给出提示
     */
    public function checkTimepointStorage($timepoint_list){
        //对嵌套数组进行处理
        $timepoint_list = array_map(function ($item) {
            return is_array($item) ? current($item) : $item;
        }, $timepoint_list);
        $storage_type_to_check = 10;  //10为磁带相关的存储
         // 构建IN条件子句，用?作为占位符
         $timepoint_uuid_strings = implode("', '", $timepoint_list);
         $sql = "SELECT COUNT(*) AS count_num 
         FROM bd_backup_timepoint bbt 
         JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid 
         WHERE bbt.timepoint_uuid IN ('$timepoint_uuid_strings') AND bsr.storage_type = $storage_type_to_check";
         $result = $this->dbSelect($sql);
         if ($result[0]['count_num'] > 0) {
             exit($this->muOpResult(false,  Xphp::$_lang['WEB_M365_SERVER_DELETE_TIME_POINT'],  Xphp::$_lang['UI_TAPE_DELETE_BACKUP_POINT_TIPS'], 'warning'));
         }  
         return;
    }


    
    /**
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, obt.os_name, obt.agent_ip from bd_backup_timepoint bbt, os_backup_timepoint obt where bbt.timepoint_uuid = ? and obt.timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_OS'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_AGENT_HOST_NAME'] . "：" . $data[0]['os_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_OS'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_AGENT_HOST_NAME'] . "：" . $data[0]['os_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
    }
    /**
     * 获取删除的主机对应时间点信息
     * 根据类型和任务筛选
     * @param array $vminfo
     */
    public function getTimepointByOS($osinfo){
        $info = array();
        $sql =  "select bbt.timepoint_uuid, bbt.real_node_uuid from bd_backup_timepoint bbt, os_backup_timepoint obt,bd_storage_resource bsr where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and 
    			bbt.task_uuid = ? and obt.os_type = ?";
        $sql_param = array($osinfo['taskuuid'], $osinfo['ostype']);
        if(!empty($osinfo['nodeuuid'])){
            $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? )";
            $sql_param = array_merge($sql_param, array($osinfo['nodeuuid'],$osinfo['nodeuuid']));
        }
        $data  = $this->dbSelect($sql, $sql_param);
        foreach ($data as $d){
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $nodeUuid,
                'hypervisor' => $osinfo['ostype']
            );
        }
        return $info;
    }
    
    
    /**
     * 获取删除的主机对应时间点信息
     * 根据类型和任务筛选
     * @param array $vminfo
     */
    public function getTimepointByCopyOS($osinfo){
        $info = array();
        $sql =  "select bbt.timepoint_uuid, obt.os_type from bd_backup_timepoint bbt, os_backup_timepoint obt where bbt.timepoint_uuid = obt.timepoint_uuid and
    			bbt.task_uuid = ? and obt.agent_uuid = ?";
        $sql_params = array($osinfo['taskuuid'],$osinfo['agentuuid']);
        if(!empty($osinfo['storageuuid'])){
            $sql .= " and bbt.storage_uuid = ?";
            $sql_params = array_merge($sql_params, array($osinfo['storageuuid']));
        }
        $data  = $this->dbSelect($sql, $sql_params);
        foreach ($data as $d){
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $osinfo['nodeuuid'],
                'hypervisor' => $d['os_type'],
                'dbtype' => $d['os_type']
            );
        }
        return $info;
    }




    /**
     * 得到主机任务名(备份)
     * @param unknown $params
     */
    public function getOSBackupTaskName(){
        $taskName = Xphp::$_lang['WEB_OS_BACKUP_TASK_NAME'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到主机任务名(恢复)
     * @param unknown $params
     */
    public function getOSRecoverTaskName(){
        $taskName = Xphp::$_lang['WEB_OS_RECOVERY_TASK_NAME'];
        return $this->getValidTaskName($taskName);
    }


    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName){
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
    * 得到所有IP地址用于组装option
    * return
    */
    public function getOptionIP($params){
        $timepoint_uuid = $params['timepoint_uuid'];
        $os_type = intval($params['os_type']);//1表示Windows 2表示Linux
        $agent_uuid = $params['agent_uuid'];
        //先获取该用户分配了哪些主机
        //获取该用户拥有的所有agent_uuid集合
        $resourceHandler = Xphp::instance('ResourceHandler');
        //userLevel是1 2 3就全部显示
        $sql = "select ba.agent_name, ba.hostname, ba.agent_uuid, ba.os_type, ba.ip, ba.online_flag, ba.authorization_module from bd_agent as ba ";
        //admin不能看租户内部的资源
        $sqlcontext = ' where';
        if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $sql .= " LEFT JOIN mt_user_tenant mut on ba.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL ";
            $sqlcontext = ' and';
        }
        //目前只能 win->win/linux, linux->linux
        //排除掉nas的agent agent_type = 3为nas的
        if($os_type == 2){
            $sql .= $sqlcontext. "  ba.os_type = 'Linux' and  ba.agent_type != 3";
        }else{
            $sql .= $sqlcontext. "  ba.agent_type != 3";
        }

        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 备份数据恢复属于操作权限，不能归属于查看
            // 三权模式下的操作员只能查看自身的数据
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到自身的或者管理的用户的的数据
            $resourceUuidSql= v1_auth_get_source_by_type(
                10,
				'ba.agent_uuid'
            );
            $sql .= " and ({$resourceUuidSql})";
        }
        $result = $this->dbSelect($sql,array());
        $info = array();
        foreach ($result as $op){
            //处理名字
            $titleDes = $this->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            if($agent_uuid == $op['agent_uuid']){
                $titleDes .=Xphp::$_lang['WEB_OS_ORIGINAL_HOST'];
            }
            if($op['online_flag'] != 1){
                $titleDes .= Xphp::$_lang['WEB_OS_OFFLINE'];
            }
            //如果为空 则是未添加授权的新虚拟机
            if(empty($op['authorization_module'])){
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' =>0,//0为未授权主机的新机子
                );
                continue;
            }
            $authorization = json_decode($op['authorization_module'],true);
//             如果都是false 那么也是未授权的
            if($authorization['file'] == false && $authorization['vm'] == false && $authorization['mysql'] == false && $authorization['oracle'] == false &&
                $authorization['sqlserver'] == false && $authorization['dm'] == false && $authorization['os'] == false && $authorization['cdp']
                == false && $authorization['desktop'] == false && $authorization['database'] == false){
                    $info[] = array(
                        'os_type' => $op['os_type'],
                        'agent_name' => $titleDes,
                        'agent_uuid' => $op['agent_uuid'],
                        'ip' => $op['ip'],
                        'type' =>1,//1为一个未授权的机子
                    );
                    continue;
            }
            if($authorization['os'] == true){
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' =>2,//1为授权有主机的机子
                );
            }
        }
        return json_encode($info);
    }




    /**
     * 搜索操作系统时间点
     * @param unknown $params
     */
    public function searchTimepoint($params){
        $search = $params['search'];
        $week = $params['week'];
        $month = $params['month'];
        $year = $params['year'];
        $forever = $params['forever'];
        $storage = $params['storage'];
        $sql = "select bbt.timepoint_uuid,bbt.storage_uuid, bbt.task_name, bbt.task_type, 
                bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,bbt.task_uuid,
                bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                bbt.detail,bbt.importance_flag, bbt.remarks,bbt.archive_flag,
                obt.os_name, obt.dir_path, obt.os_type,obt.agent_ip,obt.agent_uuid,
                bbt.real_node_uuid,
                bsr.node_uuid          
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and 
                      bbt.import_flag = 2 and
                      bbt.module_type = ? and bbt.task_type = ? ";
        $sqldiff = $sql;
        if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . Xphp::$_user['useruuid'] . "' ";
        }
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        if($week) {
            $sql .= " and bbt.weekly_flag = 1";
        }
        if($month) {
            $sql .= " and bbt.monthly_flag = 1";
        }
        if($year) {
            $sql .= " and bbt.yearly_flag = 1";
        }
        if($forever) {
            $sql .= " and bbt.importance_flag = 1";
        }
        if(!empty($storage)) {
            // $sql .= " and (bsr.node_uuid = '".$node."' or bbt.real_node_uuid = '".$node."') ";
            $sql .= " and bsr.storage_uuid = '".$storage."'";
        }
        if(!empty($search)) {
            $search = '%'.$search.'%';
            $sql .= " and (bbt.timepoint like '".$search."' or bbt.task_name like '".$search."' or obt.os_name like '".$search."' or obt.agent_ip like '".$search."')";
        }
        $sql .= ' order by bbt.timepoint';

        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['OS'], Xphp::$_config['TASKTYPE']['OS_BACKUP']);
        $pointData = $this->dbSelect($sql,$sqlParams);
        //所有任务的集合
        $task = array();
        $info = array();
        $os = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        $jobHandler = Xphp::instance('JobHandler');

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        foreach ($pointData as $key=>$point){
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            }else{
                if($point['backup_mode']==Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']) {//差异
                    $sqlfull = $sqldiff." and bbt.timepoint_uuid = ? order by bbt.timepoint";
                    $data1 = $this->dbSelect($sqlfull,array_merge($sqlParams, array($point['depend_point_uuid'])));
                    if(!in_array($data1[0], $pointData)){
                        $pointData[] = $data1[0];
                    }
                }else {//增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                        $pointData[] = $fullpoint;
                        $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }

        //判断是否可被勾选,先判断该代理主机是否存在恢复任务中,如果在任务中就看是否在运行状态  如果在就不让选中

        //默认为可勾选
        $chkDisabledflag = false;
        $timepoint = array();
        $agentList = $this->getAllAgentList();

        foreach ($pointData as $op){
            $task_uuid =  $op['task_uuid'];
            if(!in_array($task_uuid,$task)){
                $task[] = $task_uuid;
                $taskName = $jobHandler->getTimepointTaskname($op['task_uuid'],$op['task_name']);
                //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
                $taskAvailable = in_array($task_uuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                //第一层 任务名称
                $info[] = array(
                    "id" => $op['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => false,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $op['task_create_time']),
                    "isParent" => true,
                );
            }

            if(!in_array($op['task_uuid'].$op['agent_uuid'],$os)){
                $os[] = $op['task_uuid'].$op['agent_uuid'];
                //获取系统类型
                $os_type = $op['os_type'];
                $osTypeName = $this->getOSTypeStr($os_type);
                //第二层 任务系统类型
                $info[] = array(
                    "id" => $op['task_uuid'].$op['agent_uuid'],
                    "pId" => $op['task_uuid'],
                    'name' => $this->getAgentNameByList($agentList,$op['agent_uuid'],$op['os_name'],$op['agent_ip']),
                    "open" => false,
                    "nocheck" => false,
                    "type" => 1,
                    "icon" => './img/os/'.$osTypeName.'.png',
                    "iconSkin" => $osTypeName.'logo',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $op['dir_path'],
                    "taskuuid" => $op['task_uuid'],
                    "agentuuid" => $op['agent_uuid'],
                    "nodeuuid" => $op['node_uuid'],
                    "real_node_uuid" => $op['real_node_uuid'],
                    "createtime" => $this->parseDate($op['task_create_time']),
                    "osType" => $op['os_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            }


            $osTypeName = $this->getOSTypeStr($op['os_type']);

            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
            $details = json_decode($op['detail'],true);
            $name = $this->parseDate($op['timepoint'])."(".$this->getTimepointTypeDes($op['backup_mode']).")";

            //如果在合并中
            if($op['archive_flag'] == Xphp::$_config['FLAG']['SET']){
                $name .= Xphp::$_lang['WEB_OS_MERGE'];
            }

            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
            if(!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])){
                $name .= '<i class="fa fa-lock"></i>';
            }

            //是否是数据备份
            $mark = "";
            //添加GFS标识
            $mark .= $vmHandler->pGetTimepointMark(false,false,false,$utils->parseFlagToBool($op['importance_flag']));
            //其父级是否被勾选
            if(!empty($oschecked)){
                $oschecked = true;
            }else{
                $oschecked = false;
            }

            if($op['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                if(!in_array($op['timepoint_uuid'], $timepoint)){
                    $fullTimepoint = $op['timepoint_uuid'];
                    $timepoint[] =  $op['timepoint_uuid'];
                    $info[] = array(
                        "agentuuid"=> $op['agent_uuid'],
                        "checked"=> $oschecked,
                        "createtime" => $this->parseDate($op['task_create_time']),
                        "osname"=> $op['os_name'],
                        "ostype"=> $op['os_type'],
                        "icon"=> "./img/platform/timepoint-f.png",
                        "id"=> $op['timepoint_uuid'],
                        "name"=> $name.$mark,
                        "oldname" => $name,
                        "nodeuuid"=> $op['node_uuid'],
                        "real_node_uuid" => $op['real_node_uuid'],
                        "pId"=> $op['task_uuid'].$op['agent_uuid'],
                        "path"=> $op['dir_path'],
                        "pointname"=> $this->parseDate($op['task_create_time']),
                        "taskuuid"=> $op['task_uuid'],
                        "timepointuuid"=> $op['timepoint_uuid'],
                        "title"=> Xphp::$_lang['WEB_OS_SOURCE_PATH']."=> ".$op['dir_path'],
                        "type"=> 2,
                        "iconSkin" => $osTypeName.'logo',
                        "chkDisabled" => $chkDisabledflag,
                        //是否是在任务中
                        'agentuuidInTask' => $chkDisabledflag,
                    );

                    $incList = $this->getSearchIncTimepoint($op['timepoint_uuid']);
                    if(!empty($incList)){
                        foreach($incList as $op){
                            if($op['timepoint_uuid'] == $fullTimepoint){
                                continue;
                            }

                            $osTypeName = $this->getOSTypeStr($op['os_type']);

                            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
                            $details = json_decode($op['detail'],true);
                            $name = $this->parseDate($op['timepoint'])."(".$this->getTimepointTypeDes($op['backup_mode']).")";
                
                            //如果在合并中
                            if($op['archive_flag'] == Xphp::$_config['FLAG']['SET']){
                                $name .= Xphp::$_lang['WEB_OS_MERGE'];
                            }
                
                            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
                            if(!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])){
                                $name .= '<i class="fa fa-lock"></i>';
                            }
                            //是否是数据备份
                            $mark = "";
                            //添加GFS标识
                            $mark .= $vmHandler->pGetTimepointMark(false,false,false,$utils->parseFlagToBool($op['importance_flag']));
                            //其父级是否被勾选
                            if(!empty($oschecked)){
                                $oschecked = true;
                            }else{
                                $oschecked = false;
                            }
                             //第四层 增备差异点
                                $info[] = array(
                                    "agentuuid"=> $op['agent_uuid'],
                                    "checked"=> $oschecked,
                                    "createtime" => $this->parseDate($op['task_create_time']),
                                    "osname"=> $op['os_name'],
                                    "ostype"=> $op['os_type'],
                                    "icon"=> $this->getTimepointIcon($op['backup_mode']),
                                    "id"=> $op['timepoint_uuid'],
                                    "name"=> $name.$mark,
                                    "oldname" => $name,
                                    "nodeuuid"=> $op['node_uuid'],
                                    "real_node_uuid" => $op['real_node_uuid'],
                                    "pId"=> $fullTimepoint,
                                    "path"=> $op['dir_path'],
                                    "pointname"=> $this->parseDate($op['task_create_time']),
                                    "taskuuid"=> $op['task_uuid'],
                                    "timepointuuid"=> $op['timepoint_uuid'],
                                    "title"=> Xphp::$_lang['WEB_OS_SOURCE_PATH']."=> ".$op['dir_path'],
                                    "type"=> 3,
                                    //                 "nocheck" => !$nocheckflag,
                                    "nocheck" => false,
                                    "iconSkin" => $osTypeName.'logo',
                                    "chkDisabled" => true,
                                    //是否是在任务中
                                    'agentuuidInTask' => $chkDisabledflag,
                                );

                        }

                    }
                    continue;
                }
            }
        }
        return json_encode($info);
    }
    
    /**
     * 搜索时获取增量点
     */
    public function getSearchIncTimepoint($depend_point_uuid){
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, 
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,
                   obt.os_name, obt.dir_path, obt.os_type, obt.agent_ip, obt.agent_uuid,
                   bbt.real_node_uuid,
                   bsr.node_uuid
            FROM bd_backup_timepoint bbt
            JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            WHERE bbt.timepoint_uuid = ? 
                  AND bbt.available_flag = 1 
                  AND bbt.import_flag = 2
        
            UNION ALL
        
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, 
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,
                   obt.os_name, obt.dir_path, obt.os_type, obt.agent_ip, obt.agent_uuid,
                   bbt.real_node_uuid,
                   bsr.node_uuid
            FROM bd_backup_timepoint bbt
            JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            JOIN cte ON bbt.depend_point_uuid = cte.timepoint_uuid
            WHERE bbt.available_flag = 1 
                  AND bbt.import_flag = 2
        )
        SELECT * FROM cte ORDER BY timepoint;
        ";
        $result = $this->dbSelect($sql,array($depend_point_uuid));
        if(empty($result)){
            return array();
        }
       return $result;
    }

    



    
    /**
     * 找增量点的完备点
     * @param unknown $params
     */
    private function getFulllPoint($timepoint_uuid) {
        $sqlfull = "select bbt.timepoint_uuid,bbt.storage_uuid, bsr.node_uuid, bbt.task_name, bbt.task_type, 
                bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,bbt.task_uuid,
                bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                bbt.detail,bbt.importance_flag, bbt.remarks,bbt.archive_flag,
                obt.os_name, obt.dir_path, obt.os_type,obt.agent_ip,obt.agent_uuid,
                bbt.real_node_uuid     
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and bbt.import_flag = 2 and bbt.timepoint_uuid = ? order by bbt.timepoint";
        $data = $this->dbSelect($sqlfull,array($timepoint_uuid));
        if(!empty($data[0]['depend_point_uuid'])) {//不是完备点继续找
            return $this->getFulllPoint($data[0]['depend_point_uuid']);
        }else {
            return $data[0];
        }
    }







    /**
     * 转化时间策略天数为一个数组,每一项为boll
     * @param unknown $days
     */
    private function parseTimeStrategyDay($days){
        if (empty($days)){
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        $trueDays = array();
        foreach ($daysArr as $key => $d){
            //如果遇到s,结束    现在每周存储格式为0000001s1   s1表示间隔一周,以此类推
            if("s" == $d){
                break;
            }
            if($d){
                $trueDays[$key] = true;
            }else{
                $trueDays[$key] = false;
            }
        }
        return $trueDays;
    }


    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int $StrategyType
     * @param array $days
     * @return string  空字符串  s1 - s4
     */
    private function parseTimeStrategyFrequency($strategyType, $days){
        $frequency = "";
        if(Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'] != intval($strategyType)){
            return $frequency;
        }
        $strIndex = strpos($days, "s");
        if($strIndex){
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }

    /**
     * 转换限速策略天数为一个数组,每一项为0,1
     * @param unknown $days
     */
    private function parseSpeedStrategyDay($days){
        if (empty($days)){
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        foreach ($daysArr as $key => $d){
            if($d){
                $daysArr[$key] = 1;
            }else{
                $daysArr[$key] = 0;
            }
        }
        return $daysArr;
    }


    /**
     * 根据agent_uuid得到系统类型 目前就window和linux
     * 数据库中存的字段为str类型 为Windows和Linux   注意大小写
     * @param unknown $agent_uuid
     * @return string
     */
    private function getOSType($agent_uuid){
        $sql = "select os_type,agent_name,hostname,ip,net_model from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql,array($agent_uuid));
        //得到系统类型
        $os_type = $result[0]['os_type'];
        return array(
            'os_type' => $result[0]['os_type'],
            'agent_name' => $result[0]['agent_name'],
            'hostname' => $result[0]['hostname'],
            'ip' => $result[0]['ip'],
            'net_model' => $result[0]['net_model'],
        );
    }



    /**
     * 得到是否是系统盘
     * @param unknown $ostype 操作系统类型
     * @param unknown $system_flag 1是系统盘 2是数据盘     是否是有系统的盘
     * return icon的名字,这个和css里面的class相对应
     */
    public function getIconOfData($ostype,$system_flag){
        $iconName = "datalogo";
        //先判断操作类型
        if($ostype == 1 || $ostype == "Windows" || $ostype == "windows"){
            if($system_flag == 1){
                $iconName = "oslogo";
            }else{
                $iconName = "datalogo";
            }
        }else{
            if($system_flag == 1){
                $iconName = "linuxlogo";
            }else{
                $iconName = "datalogo";
            }
        }
        return $iconName;
    }

    /**
     * 验证操作系统是时间点密码
     * @param unknown $params
     */
    public function osVerifyEncry($params){
        //时间点
        $timepointList = $params['timepoint_uuid'];
        $timepoint_uuid = $timepointList[0];
        //得到用户输入的值
        $encrypt_value = base64_decode($params['encryptVal']);
        $sql = "select detail from bd_backup_timepoint where timepoint_uuid = ?";
        $result = $this->dbSelect($sql,array($timepoint_uuid));
        //解析json
        $password = json_decode($result[0]['detail'],true);
        $password = $password['password'];
        if(empty($password)){
            $this_result = false;
            return json_encode($this_result);
        }
        //解密
        $utils = Xphp::instance('Utils');
        $password_old = $utils->ptPassDecrypt($password);
        //判断密码是否正确
        if($encrypt_value == $password_old){
            $this_result = true;
            return json_encode($this_result);
        }else{
            $this_result = false;
            return json_encode($this_result);
        }
    }


    /**
     * 修改的时候验证是否有修改过密码 如果修改过密码则传入新的密码 如果没有修改过则传入原来的解密后的密码
     * @param unknown $password
     */
    private function verifyEditEncry($timepoint_uuid, $password_new){
        //先获取数据库存入的密码
        $sql = "select password from bd_storage_strategy where task_uuid = ?";
        $result = $this->dbSelect($sql,array($timepoint_uuid));
        //解析json
        $password = $result[0]['password'];
        //解密
        $utils = Xphp::instance('Utils');
        $password_old = $utils->ptPassDecrypt($password);
        //先判断是否与原来的密码相同
        if($password == $password_new){
            //如果相同则说明传入的密码是未修改
            return base64_encode($password_old);
        }else{
            return base64_encode($password_new);
        }
    }


    /**
     * 如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * @param unknown $host_name 主机名
     * @param unknown $agent_name 别名
     * @return name(ip)
     */
    public function getAgentName($host_name,$agent_name,$ip){
        $name_ip = "(".$ip.")";
        if(empty($host_name) && !empty($agent_name)){
            return $agent_name.$name_ip;
        }else if(!empty($host_name) && empty($agent_name)){
            return $host_name.$name_ip;
        }else{
            if($agent_name == $ip){
                return $host_name.$name_ip;
            }else{
                return $agent_name.$name_ip;
            }
        }
    }


    /**
     * 获得所有agent的name和ip信息
     */
    public function getAllAgentList(){
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $each){
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
     * 根据agent_uuid 查询出agent的信息并拼接成名字
     * @param unknown $list
     * @param unknown $agent_uuid
     * @return name(ip)
     */
    public function getAgentNameByList($list,$agent_uuid,$os_name, $os_agent_ip){
        $name = "--";
        //如果没获取到主机则直接返回空
        if(empty($list)){
            if(empty($os_name)){
                return $name;
            }else{
                return $this->getAgentName($os_name, "", $os_agent_ip);
            }
        }
        //开始循环获取主机
        foreach ($list as $each){
            if($each['agent_uuid'] == $agent_uuid){
                return $this->getAgentName($each['hostname'], $each['agent_name'], $each['ip']);
            }
        }
        return $this->getAgentName($os_name, "", $os_agent_ip);
    }


    /**
     * 根据agent_uuid 查询出名称
     * @param unknown $list
     * @param unknown $agent_uuid
     * @return name
     */
    public function getNameByAgentList($list,$agent_uuid,$os_name, $os_agent_ip){
        $name = "--";
        //如果没获取到主机则直接返回空
        if(empty($list)){
            if(empty($os_name)){
                return $name;
            }else{
                return $os_name;
            }
        }
        //开始循环获取主机
        foreach ($list as $each){
            if($each['agent_uuid'] == $agent_uuid){
                if(empty($each['hostname']) && !empty($each['agent_name'])){
                    return $each['agent_name'];
                }else if(!empty($host_name) && empty($agent_name)){
                    return $each['hostname'];
                }else{
                    if($each['agent_name'] == $each['ip']){
                        return $each['hostname'];
                    }else{
                        return $each['agent_name'];
                    }
                }
            }
        }
        return $os_name;
    }

    //----------------------os瞬时恢复新增
    /**
     * 得到主机任务名(瞬时恢复)
     * @param unknown $params
     */
    public function getOSInstantRecoverTaskName(){
        $taskName = Xphp::$_lang['WEB_OS_INSTANT_RECOVERY_TASK'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 获取理解恢复信息
     * @param $params IPList数组
     * return {re:true/false 成功或失败 msg:信息}
     * 
     */
    public function getInstantRecoverInfo($params){
        //时间点uuid集合
        $timepoint_uuid_list = $params['timepointList'];
        //代理主机uuid集合
        $agent_uuid_list = $params['agentList'];
        //获取到时间点的数据
        $old_list = $this->getOSBackupDiskInfo($timepoint_uuid_list);
        //获取代理主机数据的方式有两种 一种是调用接口实时获取 一种是数据库获取
        //先写一种实时获取数据的方式 后面有需要可以写实时没获取到就从数据库获取
        $newList = $this->getNewDiskInfo($agent_uuid_list);
        $result = array(
            're' => true,
            'oldList' => $old_list,
            'newList' => $newList,
            'encrypt_flag' => $this->getEncryFlag($timepoint_uuid_list),
        );
        return json_encode($result);
    }
    /**
     * 创建主机瞬时恢复任务
     * @param unknown $params
     */
    public function createInstantOSRecoverJob($params){
        //得到名称
        $task_name = $params['task_name'];
        $agent_uuid= $params['agent_uuid'];
        $recovery_timepoint_uuid  = $params['recovery_timepoint_uuid'];
        $timepoint_pwd =  $params['timepoint_pwd'];
        $cache_target = $params['cache_target'];
        $nodeuuid  =$params['node_uuid'];
        $recovery_timepoint_info =  $params['recovery_timepoint_info'];
        $transport_ip  = $params['transport_ip'];
        //得到任务类型
        // $pfMsg['agent_uuid'] = $agent_uuid;
        // $pfMsg['recovery_timepoint_uuid'] = $recovery_timepoint_uuid;
        $pfMsg['task_name'] = $task_name;
        // $pfMsg['timepoint_pwd'] = $timepoint_pwd;
        $pfMsg['cache_target'] = $cache_target;
        $pfMsg['recovery_timepoint_info']  = $recovery_timepoint_info;
        $pfMsg['transport_ip'] = $transport_ip;
        // var_dump("pfmsg",$pfMsg);
        //发送消息
        //得到恢复操作码 操作码待修改
        $opName = "OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY";
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbOSMsg($nodeuuid,$opName,$msg);
       //写入假数据
        // $mbResult =  array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=> array()
        // );
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('OSOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }

   
     /**
     *  获取操作系统瞬时恢复主机可用IP
     * @param unknown $params
     */
    public function getRecoverHostIP($params){
        $task_uuid = $params['taskuuid'];
        $utils = Xphp::instance('Utils');
        $instantflag = $params['instantflag'];
        //如果是瞬时恢复 直接获取参数
        if($instantflag){
            $os_type =  $params['os_type'];
            $agent_uuid =  $params['agent_uuid'];
        }
        else{
            //先获取操作系统类型
            $sql  = "select obt.os_type, obt.agent_uuid from os_list ol
            left join os_backup_timepoint obt on ol.timepoint_uuid  = obt.timepoint_uuid
            where ol.task_uuid  = ?";
            $result =  $this->dbSelect($sql,array($task_uuid));
            $os_type =  $result[0]['os_type'];
            $agent_uuid =  $result[0]['agent_uuid'];
        }

        $sql1 = "select ba.agent_name, ba.hostname, ba.agent_uuid, ba.os_type, ba.ip,  ba.online_flag, ba.authorization_module, ba.net_model from bd_agent as ba";
        $sqlcontext = ' where';
        if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $sql1 .= " LEFT JOIN mt_user_tenant mut on ba.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL ";
            $sqlcontext = ' and';
        }
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $agent_list = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'],10);
            $agent_list =array_column($agent_list, 'resource_uuid');
            if(empty($agent_list)){
                return json_encode(array());
            }else{
                $agent_uuids = implode(',', array_map(function($item) {return "'{$item}'";}, $agent_list));
                $sql1 .= " where agent_uuid in ($agent_uuids) ";
                //如果是瞬时恢复 那么win->win linux->linux 限制不能为livecd
                if($instantflag){
                    if($os_type == 2){
                        $sql1 .= " and os_type = 'Linux' ";
                    }else{
                        $sql1 .= " and os_type = 'Windows' ";
                    }
                    //瞬时恢复还要屏蔽 livecd/winpe
                    $sql1 .= " and agent_type not in (2,3,4,5) ";
                }else{
                    //目前只能 win->win/linux, linux->linux
                    //排除掉nas的agent agent_type = 3为nas的
                    if($os_type == 2){
                        $sql1 .= " and os_type = 'Linux' and agent_type not in (3, 4, 5)";
                    }else{
                        $sql1 .= " and agent_type not in (3, 4, 5)";
                    }
                }
            }
        }else{
            //如果是瞬时恢复 那么win->win linux->linux 限制不能为livecd
            if($instantflag){
                if($os_type == 2){
                    $sql1 .= $sqlcontext." ba.os_type = 'Linux' ";
                }else{
                    $sql1 .= $sqlcontext." ba.os_type = 'Windows' ";
                }
                //瞬时恢复还要屏蔽 livecd/winpe
                $sql1 .= " and ba.agent_type not in (2,3,4) ";
            }else{
                //目前只能 win->win/linux, linux->linux
                //排除掉nas的agent agent_type = 3为nas的
                if($os_type == 2){
                    $sql1 .= $sqlcontext." ba.os_type = 'Linux' and ba.agent_type not in (3, 4, 5)";
                }else{
                    $sql1 .= $sqlcontext. " ba.agent_type not in (3, 4, 5)";
                }
            }
        }
        $result = $this->dbSelect($sql1);

        $info = array();
        foreach($result as $op){
            //处理名字
            $titleDes = $this->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            if($agent_uuid == $op['agent_uuid']){
                $titleDes .=Xphp::$_lang['WEB_OS_ORIGINAL_HOST'];
            }
            if($op['online_flag'] != 1){
                $titleDes .= Xphp::$_lang['WEB_OS_OFFLINE'];
            }
            //如果为空 则是未添加授权的新虚拟机
            if(empty($op['authorization_module'])){
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' =>0,//0为未授权主机的新机子,
                    'net_model' => $op['net_model'],
                );
                continue;
            }
            $authorization = json_decode($op['authorization_module'],true);
            //如果都是false 那么也是未授权的
            if($authorization['file'] == false && $authorization['vm'] == false && $authorization['mysql'] == false && $authorization['oracle'] == false &&
                $authorization['sqlserver'] == false && $authorization['dm'] == false && $authorization['os'] == false && $authorization['cdp']
                == false && $authorization['desktop'] == false && $authorization['database'] == false){
                    $info[] = array(
                        'os_type' => $op['os_type'],
                        'agent_name' => $titleDes,
                        'agent_uuid' => $op['agent_uuid'],
                        'ip' => $op['ip'],
                        'type' =>1,//1为一个未授权的机子
                        'net_model' => $op['net_model'],
                    );
                    continue;
            }
            if($authorization['os'] == true){
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' =>2,//1为授权有主机的机子
                    'net_model' => $op['net_model'],
                );
            }

        }
        return json_encode($info);
    }

    /**
     * 操作系统瞬时恢复链接测试
     * @param unknown $params
     */
    public function osInstantlinkTest($params){
        //首先需要通过任务id获得时间点id 再调用linkTest方法
        $task_uuid =  $params['taskuuid'];
        $agentList = $params['agentList'];
        //通过任务id获取备份时间点id
        $sql = "select timepoint_uuid from os_list where task_uuid = ?";
        $result =  $this->dbSelect($sql,array($task_uuid));
        $timepointist = array();
        foreach ($result as $each){
            $timepointist[] = $each['timepoint_uuid'];
        }
        $getInfo =  array(
            "agentList"=>$agentList,
            "timepointList"=>$timepointist
        );
        return $this->linkTest($getInfo,true);
    }

     /**
     * 操作系统瞬时恢复初始化节点传输网络
     * @param unknown $params
     */
    public function osInstantgetNetList($params){
        // var_dump("111",$params);
        //直接通过任务id在bd_task中去找到node_uuid 然后直接使用调用你node的getNodeNetworkList方法
        $task_uuid = $params['taskuuid'];

        $sql = "select node_uuid from bd_task where task_uuid  = ?";
        $result =  $this->dbSelect($sql,array($task_uuid));
        $getparams = array(
            "nodeuuid" => $result[0]['node_uuid']
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        return $nodeHandler->getNodeNetworkList($getparams);
    }
    /**
     * 创建操作系统迁移任务
     * @param unknown $params
     */
    public function createMotionJob($params){
        //得到任务uuid
        $task_uuid = $params['taskuuid'];
        //得到传输线程
        $thread_num = $params['highInfo']['threadnum'];
        //得到全局策略组uuid
        $strategygroupuuid = $params['strategygroupuuid'];
        //组合传输策略
        $vmHandler = Xphp::instance('Vmhandler');
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        //得到限速策略
        $speed_limit_strategy_list = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $speed_limit_strategy = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到操作系统恢复信息
        $recovery_oss_info = $params['recoverInfo'];
        //组装数据
        $pfMsg['task_uuid'] = $task_uuid;
        $pfMsg['thread_num'] = $thread_num;
        $pfMsg['transport_strategy']=  $transport_strategy;
        $pfMsg['speed_limit_strategy_list'] = $speed_limit_strategy_list;
        $pfMsg['speed_limit_strategy'] = $speed_limit_strategy;
        $pfMsg['recovery_oss_info'] = $recovery_oss_info;
        //因为发送消息需要到节点nodeuuid  所以需要再获取一次节点uuid
        $sql = "select node_uuid from bd_task where task_uuid  = ?";
        $result =  $this->dbSelect($sql,array($task_uuid));
        $nodeuuid =   $result[0]['node_uuid'];
        //发送消息
        $opName = "OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION";
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbOSMsg($nodeuuid,$opName,$msg);
        //写入假数据 后续需要删除
        // $resultdata  = '{
        //     "error_code":0,
        //     "result":true,
        //     "msg":{

        //     }
        // }';
        //$mbResult =  json_decode($resultdata,true);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('OSOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 检查是否拥有操作系统保护的操作权限
     * @param array $userIdList 操作资源的拥有者uuid列表
     * @return void
     */
    private function checkOsOperatePermission(array $userIdList)
    {
        // 关联管理用户判断 操作系统保护 - 操作 os_protect_operate
        $utils = Xphp::instance("Utils");
        
        $authUser = $_SESSION['authUser']['os_protect_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], $userIdList, $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }
    }

    /**
     * 检查需要删除的完备点是否在恢复、瞬时恢复和迁移任务中
     * @param array $timepointuuids
     *
     */
    public function taskExist($timepointuuids, $operate){
        // 将 timepoint_uuid 数组转换为字符串，用于 SQL 查询
        $timepointuuidsStr = "'" . implode("','", $timepointuuids) . "'";

        // 使用子查询来检查这些 timepoint_uuid 对应的 task_uuid 是否在 bd_task 表中存在
        $sql = "
            SELECT task_uuid 
            FROM bd_task 
            WHERE task_uuid IN (
                SELECT task_uuid 
                FROM bd_backup_timepoint 
                WHERE timepoint_uuid IN ($timepointuuidsStr)
            ) and task_status != ?
        ";
        $result = $this->dbSelect($sql,array( Xphp::$_config['TASKSTATUS']['STOPPED']));

        if (empty($result)) {
           //再检查是否在恢复以及迁移和瞬时恢复中
            $sql_other = "
            SELECT task_uuid
            FROM bd_task
            WHERE task_uuid IN (
                SELECT task_uuid 
            FROM os_list 
            WHERE timepoint_uuid IN ($timepointuuidsStr)
            ) AND task_status != ?
        ";
            $result_other = $this->dbSelect($sql_other,array( Xphp::$_config['TASKSTATUS']['STOPPED']));
            if(empty($result_other)){
                return true;
            }else{
                //可能在恢复,迁移,瞬时恢复中
                exit($this->muOpResult(false, $operate,Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IN_USE_BY_TASK_ERROR'],'warning'));
            }
        }else{
            exit($this->muOpResult(false, $operate,Xphp::$_lang['WEB_ERROR_BD_TIMEPOINT_IN_USE_BY_TASK_ERROR'],'warning'));
        }




    }







}
?>