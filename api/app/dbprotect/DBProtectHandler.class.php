<?php

/*******************************************
 ** 数据库保护
 **
 ** @author       luokai@vinchin.com
 ** @date         2020-03-25
 ** @version      1.0.0
 ** @copyright    Copyright 2019 vinchin.com
 ********************************************/
class DBProtectHandler extends BLLHandler
{
    /**
     * 创建数据库备份任务
     * @param unknown $params
     */
    public function createDBBackupJob($params){
        //public params
        $utils = Xphp::instance('Utils');
        $task_name = htmlspecialchars_decode($utils->removeEscape($params['taskName']));
        $subTaskName = htmlspecialchars_decode($utils->removeEscape($params['subTaskName']));
        $dbType = intval($params['srcInfo']['dbType']);
        $this->paramsCheck($task_name);
        //租户内检查可用数量是否超过授权个数
        if(!empty($_SESSION['tenantuuid'])){
            $db_num = array();
            $tenantHandler = Xphp::instance('TenantHandler');
            foreach($params['srcInfo']['dbInfo'] as $db){
                if(!in_array($db['agentuuid'],$db_num)) {
                    $db_num[] = $db['agentuuid'];
                }
            }
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['DB'], $db_num, '');
        }
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $this->checkDBTaskLegal($operate, $params['srcInfo']['dbInfo'], null, $dbType);
        $allDbType = Xphp::$_config['DB_TYPE'];


        $module_type = Xphp::$_config['MODULE_TYPE']['DB'];
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        $strategygroupuuid = $params['strategygroupuuid'];
        $time_strategy_list = $vmHandler->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        $reserver_strategy = $vmHandler->groupReserverStrategy($params['highInfo']['reserve'], $strategygroupuuid);
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        $storage_strategy = $vmHandler->groupStorageStrategy($params['highInfo']['store'], $strategygroupuuid);
        $nodeInfo = $vmHandler->groupBackupNodeInfo($params['highInfo']['node']);
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $params['backupInfo']['type']
        );
        $pfMsg['sub_task_name'] = $subTaskName;
        $pfMsg['depend_task_uuid'] = '';
        // 客户端并行数量
        $pfMsg['transport_strategy']['max_object_transport_parallel_nums'] = (int) $params['agentInfo']['max_object_transport_parallel_nums'];
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['DB_BACKUP'];
        $pfMsg['auto_log_backup_interval'] = $params['agentInfo']['auto_log_backup_interval'];

        $pfMsg['backup_dbs_info'] = $this->groupBackupDB($params['srcInfo']['dbInfo'], $dbType);
        $pfMsg['db_type'] = intval($params['srcInfo']['dbType']);
        $pfMsg['transport_priority'] = intval($params['highInfo']['transfer']['mode']);
        $pfMsg['backup_level'] = 0;

        //代理配置
        $pfMsg['check_db_flag'] = $utils->parseBoolToFlag($params['agentInfo']['checkdbflag']);
        $pfMsg['checksum_flag'] = $utils->parseBoolToFlag($params['agentInfo']['checksumflag']);

        //oracle高级配置
        $pfMsg['channel_count'] = $params['highInfo']['transfer']['threadnum'];       //通道数
        if (
            $dbType == $allDbType['SAPHANA'] ||
            $dbType == $allDbType['MYSQL'] ||
            $dbType == $allDbType['MARIA']
        ) {
            $pfMsg['channel_count'] = $params['agentInfo']['channel_count'];
        }
        $pfMsg['last_archive_days'] = $params['agentInfo']['archivenum'];
        $pfMsg['delete_archive_log_flag'] = $utils->parseBoolToFlag($params['agentInfo']['delarchivelog']);
        //删除归档日志
        if (
            $dbType == $allDbType['POSTGRE'] ||
            $dbType == $allDbType['ANTDB'] ||
            $dbType == $allDbType['KINGBASE'] ||
            $dbType == $allDbType['UXDB'] ||
            $dbType == $allDbType['HIGHGO'] ||
            $dbType == $allDbType['OPENGAUSS'] ||
            $dbType == $allDbType['VASTBASE']
        ) {
            $pfMsg['delete_archive_log_flag'] = intval($params['agentInfo']['delarchivelog']);
        } elseif ($dbType == $allDbType['ORACLE']) {
            $pfMsg['delete_archive_log_flag'] = intval($params['agentInfo']['delarchivelog']);
        }
        $pfMsg['compress_flag'] = $utils->parseBoolToFlag($params['agentInfo']['compressflag']);
        //Postgres数据库源端压缩标记获取
        if (
            $dbType == $allDbType['POSTGRE'] ||
            $dbType == $allDbType['ANTDB'] ||
            $dbType == $allDbType['KINGBASE'] ||
            $dbType == $allDbType['OPENGAUSS'] ||
            $dbType == $allDbType['VASTBASE']
        ) {
            $pfMsg['compress_flag'] = intval($params['agentInfo']['compressflag']);
        }
        // TiDB高级配置
        $pfMsg['compress_method'] = $params['agentInfo']['compress_method'];
        $pfMsg['compress_level'] = $params['agentInfo']['compress_level'];
        //全局策略uuid
        $pfMsg['strategy_group_uuid'] = $params['strategygroupuuid'];
        $pfMsg['thread_num'] = $params['highInfo']['transfer']['threadnum'];
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $params['strategygroupuuid']);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //归档存储告警
        $pfMsg['detail'] = json_encode($params['agentInfo']['warningInfo']);
        // oracle的设置filesperset
        $pfMsg['set_filesperset_flag'] = $utils->parseBoolToFlag($params['agentInfo']['set_filesperset_flag']);
        $pfMsg['datafile_filesperset_num'] = (int) $params['agentInfo']['datafile_filesperset_num'];
        $pfMsg['archivelog_filesperset_num'] = (int) $params['agentInfo']['archivelog_filesperset_num'];
        // Oracle归档日志
        $pfMsg['log_backup_days_flag'] = $utils->parseBoolToFlag($params['agentInfo']['log_backup_days_flag']);
        $pfMsg['log_backup_days'] = (int) $params['agentInfo']['log_backup_days'];
        $pfMsg['log_backup_times_flag'] = $utils->parseBoolToFlag($params['agentInfo']['log_backup_times_flag']);
        $pfMsg['log_backup_times'] = (int) $params['agentInfo']['log_backup_times'];
        $pfMsg['skip_inaccessible_file_flag'] = $utils->parseBoolToFlag($params['agentInfo']['skip_inaccessible_file_flag']);
        $pfMsg['skip_offline_file_flag'] = $utils->parseBoolToFlag($params['agentInfo']['skip_offline_file_flag']);
        $pfMsg['enable_bct_flag'] = $utils->parseBoolToFlag($params['agentInfo']['enable_bct_flag']);
        $pfMsg['set_section_size_flag'] = $utils->parseBoolToFlag($params['agentInfo']['set_section_size_flag']);
        $pfMsg['section_size'] = $params['agentInfo']['section_size'];
        $pfMsg['multi_task_flag'] = $utils->parseBoolToFlag($params['agentInfo']['multi_task_flag']);
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['agentInfo']['ignore_resource_limiting_flag']);
        $pfMsg['custom_rman_cmd'] = $params['agentInfo']['custom_rman_cmd'];
        //得到重试策略
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        // 安全策略
        $pfMsg['safe_config_strategy'] = $jobHandler->groupSafeConfigStrategy($params['safe_config_strategy']);
        $msg = json_encode($pfMsg);

        $mbResult = $this->mbDBMsg(Xphp::instance('NodeHandler')->getMasterNodeUuid(), $dbType, $opName, $msg, FALSE);
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
     * 修改数据库备份任务
     * @param unknown $paramsf
     */
    public function editDBBackupJob($params){
        $utils = Xphp::instance('Utils');
        $taskName = htmlspecialchars_decode($utils->removeEscape($params['taskName']));
        $taskuuid = $params['taskuuid'];
        $dbType = intval($params['srcInfo']['dbType']);
        $this->paramsCheck($taskName, $taskuuid);
        //租户内检查可用数量是否超过授权个数
        if(!empty($_SESSION['tenantuuid'])){
            $db_num = array();
            $tenantHandler = Xphp::instance('TenantHandler');
            foreach($params['srcInfo']['dbInfo'] as $db){
                if(!in_array($db['agentuuid'],$db_num)) {
                    $db_num[] = $db['agentuuid'];
                }
            }
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['DB'], $db_num, $taskuuid);
        }
        $strategygroupuuid = "";

        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $this->checkDBTaskLegal($operate, $params['srcInfo']['dbInfo'], $taskuuid, $dbType);
        $allDbType = Xphp::$_config['DB_TYPE'];
        $vmHandler = Xphp::instance('Vmhandler');
        $nodeHandler = Xphp::instance('NodeHandler');

        //优先检测一次性备份的时候其时间是否已过期
        $timeStrategy = $params['backupInfo'];
        if("oncetime" == $timeStrategy['type']){
            //获取一次性备份的时间
            $taskCreateTime =  strtotime($timeStrategy['datetime']);
            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            if($systemTime >= $taskCreateTime){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_BACKUP_TIME'],Xphp::$_lang['WEB_DB_BACKUP_TIME_TIPS'], 'warning'));
            }
        }
        //监测任务状态是否在停止中
        $sql = "select task_status, strategy_id from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskStatus = intval($data[0]['task_status']);
        $strategyID = $data[0]['strategy_id'];
        if($taskStatus != Xphp::$_config['TASKSTATUS']['STOPPED']){
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_JOB_EDIT_TIPS']);
        }
        $nodeInfo = $this->getDBModifyJobNodeAndStorageInfo($params['highInfo']['node']);
        $this->dbBeginTransaction();
        $dbInfo = $params['srcInfo']['dbInfo'];
        $threadNum = $params['highInfo']['transfer']['threadnum'];
        //更新任务表         bd_task
        if(empty($nodeInfo['storageuuid'])){
            //自动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, auto_find_sr_flag = ?, thread_num = ?
                 where task_uuid = ?";
            $result = $this->dbExec($sql, array($taskName, date("Y-m-d H:i:s"), $nodeInfo['nodeuuid'],
                $nodeInfo['auto_find_sr_flag'], $threadNum, $taskuuid));
        }else{
            //手动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, agent_uuid = ?, node_uuid = ?, auto_find_sr_flag = ?,
                storage_uuid = ?, thread_num = ? where task_uuid = ?";
            $result = $this->dbExec($sql, array($taskName, date("Y-m-d H:i:s"), $dbInfo[0]['agentuuid'], $nodeInfo['nodeuuid'],
                $nodeInfo['auto_find_sr_flag'], $nodeInfo['storageuuid'], $threadNum, $taskuuid));
        }
        $sql = "UPDATE db_task SET 
                   check_db_flag = ?, compress_flag = ?, checksum_flag = ?,
                   last_archive_days = ?, delete_archive_log_flag = ?, detail = ?,
                   set_filesperset_flag = ?, datafile_filesperset_num = ?, archivelog_filesperset_num = ?,
                   log_backup_times_flag = ?, log_backup_times = ?, log_backup_days_flag = ?, log_backup_days = ?,
                   skip_inaccessible_file_flag = ?, skip_offline_file_flag = ?,
                   auto_log_backup_interval = ?, channel_count = ?, enable_bct_flag = ?,
                   set_section_size_flag = ?, section_size = ?, compress_method = ?, compress_level = ?
               WHERE task_uuid = ?";
        //代理配置
        $check_db_flag = $utils->parseBoolToFlag($params['agentInfo']['checkdbflag']);
        $compress_flag = $utils->parseBoolToFlag($params['agentInfo']['compressflag']);
        $delArchiveLog = $utils->parseBoolToFlag($params['agentInfo']['delarchivelog']);
        if (
            $dbType == $allDbType['POSTGRE'] ||  //Postgre数据库源端压缩标记获取
            $dbType == $allDbType['ANTDB'] ||
            $dbType == $allDbType['KINGBASE'] ||
            $dbType == $allDbType['UXDB'] ||
            $dbType == $allDbType['HIGHGO'] ||
            $dbType == $allDbType['OPENGAUSS'] ||
            $dbType == $allDbType['VASTBASE']
        ) {
            $compress_flag = intval($params['agentInfo']['compressflag']);
            $delArchiveLog = intval($params['agentInfo']['delarchivelog']);
        } else if ($dbType == $allDbType['ORACLE']) {
            $delArchiveLog = intval($params['agentInfo']['delarchivelog']);
        }
        $checksum_flag = $utils->parseBoolToFlag($params['agentInfo']['checksumflag']);
        $archiveNum = $params['agentInfo']['archivenum'];
        $setFilesPersetFlag = $utils->parseBoolToFlag($params['agentInfo']['set_filesperset_flag']);
        $datafileFilesPersetNum = (int) $params['agentInfo']['datafile_filesperset_num'];
        $archivelogFilesPersetNum = (int) $params['agentInfo']['archivelog_filesperset_num'];
        $logBackupTimesFlag = $utils->parseBoolToFlag($params['agentInfo']['log_backup_times_flag']);
        $logBackupTimes = (int) $params['agentInfo']['log_backup_times'];
        $logBackupDaysFlag = $utils->parseBoolToFlag($params['agentInfo']['log_backup_days_flag']);
        $logBackupDays = (int) $params['agentInfo']['log_backup_days'];
        $skipInaccessibleFileFlag = $utils->parseBoolToFlag($params['agentInfo']['skip_inaccessible_file_flag']);
        $skipOfflineFileFlag = $utils->parseBoolToFlag($params['agentInfo']['skip_offline_file_flag']);
        $channelCount = $params['agentInfo']['channel_count'];
        if ($dbType == $allDbType['ORACLE']) {
            $channelCount = $threadNum;
        }
        $enableBctFlag = $utils->parseBoolToFlag($params['agentInfo']['enable_bct_flag']);
        $setSectionSizeFlag = $utils->parseBoolToFlag($params['agentInfo']['set_section_size_flag']);
        $sectionSize = $params['agentInfo']['section_size'];
        $compressMethod = $params['agentInfo']['compress_method'];
        $compressLevel = $params['agentInfo']['compress_level'];
        $sqlParams = array(
            $check_db_flag,
            $compress_flag,
            $checksum_flag,
            $archiveNum,
            $delArchiveLog,
            json_encode($params['agentInfo']['warningInfo']),
            $setFilesPersetFlag,
            $datafileFilesPersetNum,
            $archivelogFilesPersetNum,
            $logBackupTimesFlag,
            $logBackupTimes,
            $logBackupDaysFlag,
            $logBackupDays,
            $skipInaccessibleFileFlag,
            $skipOfflineFileFlag,
            $params['agentInfo']['auto_log_backup_interval'],
            $channelCount,
            $enableBctFlag,
            $setSectionSizeFlag,
            $sectionSize,
            $compressMethod,
            $compressLevel,
            $taskuuid
        );
        $result = $result && $this->dbExec($sql, $sqlParams);

        //更新数据库列表 db_list
        $sql = "delete from db_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "insert db_list (task_uuid, db_name, instance_name, dir_path, db_uuid, agent_uuid, group_uuid, before_task_script, after_task_script)
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        foreach ($dbInfo as $db){
            $uuid = $db['dbuuid'];
            if(empty($uuid)){
                //再次获取时间点表里的db_uuid
                $uuid = $this->getDBuuidByTask($db['dbname'], $db['agentuuid'], $taskuuid, $db['instancename']);
                //如果还是为空
                if(empty($uuid)){
                    $uuid = $utils->uuid();
                }
            }
            $sqlParams = array(
                $taskuuid,
                $db['dbname'],
                $db['instancename'],
                $db['dir_path'],
                $uuid,
                $db['agentuuid'],
                $db['groupuuid'],
                json_encode($db['before_task_script']),
                json_encode($db['after_task_script']),
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

        //更新数据库列表 bd_task_agent_list
        $sql = "delete from bd_task_agent_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $sql = "insert bd_task_agent_list (task_uuid, group_uuid, agent_uuid)
                    values (?, ?, ?)";
        $agentArr = array();
        foreach ($dbInfo as $db){
            if(!in_array($db['agentuuid'],$agentArr)){
                $sqlParams = array(
                    $taskuuid,
                    $db['groupuuid'],
                    $db['agentuuid'],
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
                $agentArr[] = $db['agentuuid'];
            }
        }


        //更新时间策略表 bd_time_strategy
        $sql = "delete from bd_time_strategy where strategy_id = ?";
        $result = $result && $this->dbExec($sql, array($strategyID));

        $timeStrategy = $params['backupInfo'];
        if("oncetime" == $timeStrategy['type']){
            //一次性策略
            $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time, days, roll_interval, roll_end_time)
                    values (?, ?, ?, ?, '', 0, '')";
            $sqlParams = array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL'],
                Xphp::$_config['STRATEGY_TYPE']['ONCE'], $timeStrategy['datetime']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }elseif("strategy" == $timeStrategy['type']){
            //时间策略
            if(!empty($timeStrategy['fullInfo'])){
                //完全策略
                $modeType = Xphp::$_config['BACKUP_MODE']['FULL'];

                //单独处理完全备份修改
                $sql = "select strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
                        from bd_time_strategy where strategy_id = ?";
                $data = $this->dbSelect($sql, array($strategyID));
                if(empty($data[0])){
                    //如果数据库没有,直接插入,
                    $result = $result && $vmHandler->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                }else{
                    //如果数据库有,比较内容,如果一样,不做操作,如果不一样,删除后插入
                    $strategyInfo = $timeStrategy['fullInfo'];
                    $days = $vmHandler->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
                    //不一样,删除后再重新插入
                    $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                    $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));

                    $result = $result && $vmHandler->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                }
            }else{
                $sql = "delete from bd_time_strategy where strategy_id = ? and mode = ?";
                $result = $result && $this->dbExec($sql, array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL']));
            }
            if(!empty($timeStrategy['incrInfo'])){
                //增量策略
                $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                $result = $result && $vmHandler->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['incrInfo']);
            }
            if(!empty($timeStrategy['diffInfo'])){
                //差异策略
                $modeType = Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'];
                $result = $result && $vmHandler->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['diffInfo']);
            }
            if(!empty($timeStrategy['logInfo'])){
                //日志策略或归档日志策略
                $modeType = Xphp::$_config['BACKUP_MODE']['LOG'];
                $result = $result && $vmHandler->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['logInfo']);
            }
            if(!empty($timeStrategy['pIncrInfo'])){
                //永久增量策略  --- NOTE: 同发送给后台的消息一致，mode==增量备份
                $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                $result = $result && $vmHandler->insertTimeStrategy($taskuuid, $strategygroupuuid, $strategyID, $modeType, $timeStrategy['pIncrInfo']);
            }
        } elseif ('manual' == $timeStrategy['type']) {  // 手动启动
            //手动启动不需要插入时间策略
        }
        // 更新bd_strategy表
        $sql = "UPDATE bd_strategy SET
                       next_mode = ?, next_strategy_type = ? , next_start_time = ?, time_strategy_backup_type = ?
                WHERE strategy_id = ? ";
        $timeStrategyBackupType = Xphp::$_config['TIME_STRATEGY_BACKUP_TYPE'][$timeStrategy['type']];
        $result = $result && $this->dbExec($sql, [0, 0, '0000-00-00 00:00:00', $timeStrategyBackupType, $strategyID]);
        $highInfo = $params['highInfo'];
        //获取传输网络切换节点默认选择一个传输网络
        $highInfo['transfer']['network'] =  $nodeHandler->pGetDiffNodeNetwork($taskuuid, $nodeInfo['nodeuuid'], $params['highInfo']['transfer']['network']);
        //更新保留策略表 bd_reserved_strategy
        $sql = "UPDATE bd_reserved_strategy SET strategy_type = ?, number = ? WHERE strategy_id = ?";
        $sqlParams = array($highInfo['reserve']['type'], $highInfo['reserve']['value'], $strategyID);
        $result = $result && $this->dbExec($sql, $sqlParams);

        //更新传输策略表 bd_transport_strategy
        $maxParallelNums = (int) $params['agentInfo']['max_object_transport_parallel_nums'];
        $sql = "UPDATE bd_transport_strategy SET encrypt_flag = ?, network_uuid = ?, encrypt_method = ?
                    , max_object_transport_parallel_nums = ?
                WHERE strategy_id = ?";
        $sqlParams = array(
            $utils->parseBoolToFlag($highInfo['transfer']['encrypt']),
            $highInfo['transfer']['network'],
            intval($highInfo['transfer']['encrypt_method']),
            $maxParallelNums,
            $strategyID,
        );
        $result = $result && $this->dbExec($sql, $sqlParams);

        //更新存储策略表 bd_storage_strategy
        $sql = "UPDATE bd_storage_strategy SET deduplication_flag = ?, block_size = ?, compressed_flag = ?,
                    encrypted_flag = ?, password_auto_flag = ?, password = ?, compress_method = ?,
                    encrypt_method = ?
                WHERE strategy_id = ?";
        //AES-CBC 256位加密密码
        $password = base64_decode($highInfo['store']['password']);
        if($highInfo['store']['password_auto_flag']){
            //固定密码
            $password = "/mnt/vm_vinfs";
        }
        //判断修改密码是否没有变化
        $sqlPass = "select password from bd_storage_strategy where strategy_id = ? ";
        $data = $this->dbSelect($sqlPass, array($strategyID));
        $oldPass = "";
        if(!empty($data)){
            $oldPass = $data[0]['password'];
        }
        $passwordAES = $utils->ptPassEncrypt($password);
        if($oldPass == $password){
            $passwordAES = $password;
        }

        if(!$highInfo['store']['encrypt']){
            $passwordAES = "";
        }

        $sqlParams = array($utils->parseBoolToFlag($highInfo['store']['deduplication']),
            intval($highInfo['store']['blocksize'])*1024,
            $utils->parseBoolToFlag($highInfo['store']['compress_flag']),
            $utils->parseBoolToFlag($highInfo['store']['encrypt']),
            $utils->parseBoolToFlag($highInfo['store']['password_auto_flag']),
            $passwordAES,
            $highInfo['store']['compress_method'],
            $highInfo['store']['encrypt_method'],
            $strategyID
        );
        $result = $result && $this->dbExec($sql, $sqlParams);

        //限速策略
        $sql = "SELECT * FROM bd_task_speed_limit_strategy WHERE task_uuid = ? ";
        $speedData = $this->dbSelect($sql, [$taskuuid]);
        if ($speedData) {  // 删除之前存在的策略
            $sql = "DELETE FROM bd_global_speed_limit_strategy WHERE strategy_uuid = ? AND is_global = 0 ";
            $result = $result && $this->dbExec($sql, [$speedData[0]['strategy_uuid']]);
            $sql = "DELETE FROM bd_task_speed_limit_strategy WHERE task_uuid = ? ";
            $result = $result && $this->dbExec($sql, [$taskuuid]);
        }
        $speedList = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        if ($speedList) {
            $customStrategyEmpty = false;  // 自定义限速策略是否为空，为空需要删除bd_task_speed_limit_strategy里面的记录
            // 如果是自定义的话，那么需要在 bd_global_speed_limit_strategy  表里先加记录
            if (!$speedList['is_global']) {
                $strategy_uuid = $utils->uuid();
                // 自定义
                $sql = "insert into bd_global_speed_limit_strategy (strategy_uuid,strategy_name,strategy_type,extra_info,is_global,user_uuid,create_time,days,start_time,end_time,speed_limited_value) values (?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?)";
                $sqlParams = [
                    $strategy_uuid,$taskName,intval($speedList['strategy_type']),$speedList['extra_info'],0,Xphp::$_user['useruuid'],date('Y-m-d H:i:s')
                ];
                if (!$speedList['speed_limit_info']) {
                    $customStrategyEmpty = true;
                }
                foreach($speedList['speed_limit_info'] as $speed){
                    $sqlParams2 = array_merge($sqlParams, [$speed['days']]);
                    foreach ($speed['time_list'] as $speeds) {
                        $sqlParams3 = array_merge($sqlParams2, [$speeds['start_time'] , $speeds['end_time'] , intval($speeds['speed_limited_value'])]);
                        $result = $result && $this->dbExec($sql, $sqlParams3);
                    }
                }
            } else {
                $strategy_uuid = $speedList['strategy_uuid'];
            }
            if ($speedList['is_global'] || !$customStrategyEmpty) {
                $sql = "INSERT INTO bd_task_speed_limit_strategy(strategy_uuid, strategy_name, task_uuid, task_priority) VALUES(?, ?, ?, ?) ";
                $result = $result && $this->dbExec($sql, [$strategy_uuid, $taskName, $taskuuid, $speedList['task_priority']]);
            }
        }

        $logParams = array(
            'task_uuid' => $taskuuid,
            'task_name' => $taskName,
            'task_type' => Xphp::$_config['TASKTYPE']['DB_BACKUP'],
            'module_type' => Xphp::$_config['MODULE_TYPE']['DB'],
            'submodule_type' => $dbType,
        );
        if($result){
            $this->dbCommit();
            $this->taskLog($logParams, 'BD_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS', [$taskName]);
            return $this->muOpResult(true, $operate);
        }else{
            $this->dbRollBack();
            $this->taskLog($logParams, 'BD_TASKLOG_DESC_KEY_MODIFY_TASK_FAILURE', [$taskName], 3);
            return $this->muOpResult(false, $operate, '', 'warning');
        }
    }

    /**
     * 创建数据库恢复任务
     * @param array $params
     * @return string
     */
    public function createDBRecoveryJob($params){
        //public params
        $utils = Xphp::instance('Utils');
        $allDbType = Xphp::$_config['DB_TYPE'];
        $task_name = $utils->removeEscape($params['taskName']);
        $strategygroupuuid = $params['strategygroupuuid'];
        $dbtype = intval($params['pointInfo']['type']);
        $this->paramsCheck($task_name, $dbtype);
        $module_type = Xphp::$_config['MODULE_TYPE']['DB'];
        $recovery_position = intval($params['recoverInfo']['recoverposition']);
        $recovery_time_type = intval($params['timeInfo']['type']);
        $modifyPfileFlag = (bool) $params['modify_pfile_flag'];
        if ($dbtype != $allDbType['ORACLE']) {
            $detail = json_encode($params['detail'], JSON_UNESCAPED_UNICODE);
        } else {
            // 原数据库覆盖恢复和指定文件夹恢复 & 按时间恢复
            if (
                $params['recovery_type'] == 1 &&  // 按时间恢复
                (
                    $recovery_position == Xphp::$_config['DB_RECOVERY_TYPE']['COVER'] ||
                    $recovery_position == Xphp::$_config['DB_RECOVERY_TYPE']['SPECIFY_FOLDER'] ||
                    $recovery_position == Xphp::$_config['DB_RECOVERY_TYPE']['FULL'] ||
                    $recovery_position == Xphp::$_config['DB_RECOVERY_TYPE']['INCOMPLETE']
                )
            ) {
                $detail = $params['detail'];
                $detail['pfile_info']['file_content'] = $utils->removeEscape($detail['pfile_info']['file_content']);
                $detail['listener_info']['file_content'] = $utils->removeEscape($detail['listener_info']['file_content']);
                $detail['tnsnames_info']['file_content'] = $utils->removeEscape($detail['tnsnames_info']['file_content']);
                $detail['sqlnet_info']['file_content'] = $utils->removeEscape($detail['sqlnet_info']['file_content']);
                $detail['pfile_info']['recovery_flag'] = $utils->parseBoolToFlag($detail['pfile_info']['recovery_flag']);
                $detail['listener_info']['recovery_flag'] = $utils->parseBoolToFlag($detail['listener_info']['recovery_flag']);
                $detail['tnsnames_info']['recovery_flag'] = $utils->parseBoolToFlag($detail['tnsnames_info']['recovery_flag']);
                $detail['sqlnet_info']['recovery_flag'] = $utils->parseBoolToFlag($detail['sqlnet_info']['recovery_flag']);
                $detail['password_info']['recovery_flag'] = $utils->parseBoolToFlag($detail['password_info']['recovery_flag']);
                $detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
            } else {
                $detail = '';
            }
        }

        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        $time_strategy_list = $vmHandler->groupRecoverTimeList($params['timeInfo'], $strategygroupuuid);
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer'], $strategygroupuuid);
        $pfMSg = $this->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        if ($params['recovery_type'] == 1) {
            $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['DB_RECOVERY'];
        } else {
            $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['DRILL'];
        }
        //private params
        //disk or file
        $pfMSg['recovery_level'] = $recovery_position;
        if ($params['recoverInfo']['cluster_flag']) {  // 集群
            if (
                $allDbType['MONGODB'] == $dbtype ||  // MongoDB集群
                $allDbType['TIDB'] == $dbtype  // TiDB集群
            ) {
                $pfMSg['destination_agent_uuid'] = explode(',', $params['recoverInfo']['desagentuuid'])[0];
            } else {
                $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['desagentuuid'];
            }
        } else {
            $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['desagentuuid'];
        }
        $pfMSg['recovery_dbs_info'] = $this->groupRebuildDBInfo(
            $recovery_position,
            $params['pointInfo']['points'],
            $params['recoverInfo'],
            $dbtype,
            $detail,
            $modifyPfileFlag
        );
        $pfMSg['strategy_group_uuid'] = $strategygroupuuid;
        //恢复通道数
        $pfMSg['channel_count'] = intval($params['highInfo']['threadnum']);
        $pfMSg['thread_num'] = intval($params['highInfo']['threadnum']);

        $pfMSg['db_type'] = $dbtype;
        $pfMSg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        $pfMSg['recovery_type'] = intval($params['recovery_type']);
        $pfMSg['recovery_time_flag'] = $params['recovery_time_flag'];  // sap hana的恢复时间
        // 客户端并行数量
        $pfMSg['transport_strategy']['max_object_transport_parallel_nums'] = (int) $params['recoverInfo']['max_object_transport_parallel_nums'];
        $pfMSg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['ignore_resource_limiting_flag']);
        //得到重试策略
        $pfMSg['retry_strategy'] =  Xphp::instance('JobHandler')->groupRetryStrategy($params['retry_strategy']);
        // 安全策略
        $pfMSg['safe_config_strategy'] = $jobHandler->groupSafeConfigStrategy($params['safe_config_strategy']);

        $nodeuuid = $params['node_uuid'];
        if ($params['recovery_type'] == 2) {  // 定时恢复最新点
            $nodeuuid = $this->getTimerRecoveryNewestNodeUuid($params['pointInfo']['points'], $dbtype, $params['pointInfo']['points']);
        }
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbDBMsg($nodeuuid, $dbtype, $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['timeInfo']['type'];
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
     * 获取定时恢复最新点的节点uuid
     * 1、判断恢复源选择的实例、数据库是否存在备份任务，如果存在备份任务，那么就传到备份任务所在的节点
     * 2、如果选择的多个数据库存在多个备份任务，且备份任务在不同节点上，那么传到主节点上
     * 3、如果选择的实例、数据库不存在备份任务，那么传到主节点上
     * @param $sourceList
     * @param $dbType
     * @return mixed
     */
    private function getTimerRecoveryNewestNodeUuid($sourceList, $dbType, $points)
    {
        $sourceAgentUuidList = array_map(function ($row) {
            return $row['source_agent_uuid'];
        }, $points);
        $sourceAgentUuids = "'" . implode("', '", $sourceAgentUuidList) . "'";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getMasterNodeUuid();  // 主节点uuid
        $allDbType = Xphp::$_config['DB_TYPE'];

        $instanceNameList = [];
        $dbNameList = [];
        foreach ($sourceList as $sourceInfo) {
            $instanceNameList[] = $sourceInfo['source_instance_name'];
            $dbNameList[] = $sourceInfo['source_db_name'];
        }
        $instanceNames = "'" . implode("', '", $instanceNameList) . "'";
        $dbNames = "'" . implode("', '", $dbNameList) . "'";
        $sql = "SELECT bt.node_uuid
                FROM db_list dl
                    INNER JOIN bd_task bt ON dl.task_uuid = bt.task_uuid
                    INNER JOIN db_task dt ON dl.task_uuid = dt.task_uuid
                WHERE dl.instance_name IN ($instanceNames) AND dl.agent_uuid IN ($sourceAgentUuids) AND dt.db_type = ?
                    AND bt.task_type = ? ";
        switch ($dbType) {
            case $allDbType['SQLSERVER']:
            case $allDbType['SAPHANA']:
                $sql .= " AND dl.db_name IN ($dbNames) ";
                break;
        }
        $data = $this->dbSelect($sql, [$dbType, Xphp::$_config['TASKTYPE']['DB_BACKUP']]);
        if (!is_array($data) || !$data) {
            return $nodeUuid;
        }
        $nodeUuidList = array_column($data, 'node_uuid');
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        if (count($nodeUuidList) === 1) {
            return $nodeUuidList[0];
        }
        return $nodeUuid;
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName){
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type, dt.db_type
                from bd_task bt, db_task dt where bt.task_uuid = dt.task_uuid and bt.task_name = ? and bt.task_type = ?
                order by bt.id desc";
        $data = $this->dbSelect($sql, array($taskName, Xphp::$_config['TASKTYPE']['DB_RECOVERY']));
        if(!$data) return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'uuid' => $data[0]['task_uuid'],
            'module' => $data[0]['module_type'],
            'subModule' => $data[0]['db_type'],
            'taskType' => $data[0]['task_type'],
            'startType' => Xphp::$_config['BACKUP_MODE']['FULL'],
        );
        //调用系统统一启动任务接口.不重新写
        $jobHandler = Xphp::instance('JobHandler');
        $result = $jobHandler->startJob($params);
        $result = json_decode($result, true);
        //这里直接返回成功或失败 bool
        return $result['re'];
    }

    /**
     * 获取客户端列表
     */
    public function getDbAgent($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'hostname', 'agent_name', 'ip', 'os_version', 'register_time', '', 'authorization_module', 'online_flag', '');
        $sql = "select ba.agent_uuid, ba.user_uuid, bu.user_name, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.os_version, unix_timestamp(ba.register_time) register_time, ba.port, ba.online_flag, ba.register_flag, ba.authorization_module 
        from bd_agent ba left join bd_user bu 
                on ba.user_uuid = bu.user_uuid ";
        $sqlCount = "select count(ba.agent_uuid) as total from bd_agent ba left join bd_user bu 
                on ba.user_uuid = bu.user_uuid ";

        $sql .= " where ba.agent_type != 4 ";
        $sqlCount .= " where ba.agent_type != 4 ";

        $clientHandler = Xphp::instance('ClientHandler');
        $agentUuidArr = $clientHandler->getClientUuids();
        if (!$agentUuidArr) {
            $sql .= " AND ba.agent_uuid = '' ";
        } else {
            if (is_array($agentUuidArr)) {
                $agentUuids = "'" . implode("', '", $agentUuidArr) . "'";
                $sql .= " WHERE ba.agent_uuid IN ($agentUuids) ";
                $sqlCount .= " WHERE ba.agent_uuid IN ($agentUuids) ";
            }
        }
        $sql .= " and ba.agent_type != 4";
        $sqlCount .= " and ba.agent_type != 4";
//         //精确搜索
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

        $sqlParams = array($start, $length);
        $sqlCountParams = array();

        $data =$this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $countNum = 0;
        if(!empty($count)){
            $countNum = $count[0]['total'];
        }
        $utils = Xphp::instance("Utils");

        $records = array();
        $records["data"] = array();
        $agentHandler = Xphp::instance('AgentHandler');
        $pfDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($data as $d){
            $agentname = $d['agent_name'];
            $authModule = $agentHandler->getAgentModule($d['authorization_module']);
            if(empty($agentname)){
                $agentname = Xphp::$_config['NULLSPACE'];
            }
            $typeList = $this->pGetDbTypeList($d['agent_uuid']);
            $authDes = Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED'];
            if(!empty($typeList)){
                $authDes = "";
                foreach ($typeList as $key=>$value){
                    $authDes .= Xphp::$_config['DB_TYPE_DES'][intval($value['db_type'])];
                    if($key != (count($typeList)-1)) {
                        $authDes .= ", ";
                    }
                }
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['agent_uuid'] .'">',
                $d['hostname'],
                $agentname,
                $d['ip'],
                $d['os_type']."(".$d['os_version'].")",
                $this->parseDate($d['register_time']),
                $authDes,
                $authModule,
                $pfDes['ONLINEDES'][$d['online_flag']],
                array(1, 2, 3),
                intval($d['online_flag']),
                $d['agent_uuid']
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $countNum;
        $records["recordsFiltered"] = $countNum;

        return  json_encode($records);
    }

    /**
     * 添加数据库客户端代理
     * @param unknown $params
     */
    public function addDBAgent($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_db_agent_manager_add");
        $ip = $params['ip'];
        $agentName = $params['agentname'];
        $this->checkAgentExist($ip);
        $port = $params['port'];
        $transportPort = $params['transport_port'];
        $clientPort = $params['client_port'];
        $opcodeName = 'DB_CLIENT_OP_ADD';
        //组合消息
        $msg = array(
            "client_ip" => $ip,
            "agent_name" => $agentName,
            "port" => $port,
            "transport_port" => $transportPort,
            "client_transport_port" => $clientPort,
            "user_uuid" => Xphp::$_user['useruuid']

        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, Xphp::$_config['DB_TYPE']['UNKNOWN'], json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $DBProtectHandler = Xphp::instance('DBProtectOpcode');
        $operate = $DBProtectHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            //查询uuid
            $sql = "select agent_uuid from bd_agent where ip = ? ";
            $clientHandler = Xphp::instance('ClientHandler');
            $agentUuidArr = $clientHandler->getClientUuids();
            if (!$agentUuidArr) {
                $sql .= " AND agent_uuid = '' ";
            } else {
                if (is_array($agentUuidArr)) {
                    $agentUuids = "'" . implode("', '", $agentUuidArr) . "'";
                    $sql .= " AND agent_uuid IN ($agentUuids) ";
                }
            }
            $data = $this->dbSelect($sql, array($ip));

            if(!empty($data)){
                //添加资源和用户关联
                $userHandler = Xphp::instance('UsersHandler');
                $resourceInfo = array();
                $resourceInfo[] = array(
                    'resourceuuid' => $data[0]['agent_uuid'],
                    'vmuuid' => "",
                    'vcenteruuid' => "",
                    'resourceType' => Xphp::$_config['RESOURCE_TYPE']['DB_HOST']
                );
                $userHandler->pAddUserResource(Xphp::$_user['useruuid'], $resourceInfo);
            }
            //添加代理系统日志
            $this->systemLog('SYSTEM_LOG_ADD_DB_AGENT', array($ip));
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 修改数据库客户端代理
     * @param unknown $params
     */
    public function editDBAgent($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_db_agent_manager_edit");
        $agentuuid = $params['agentuuid'];
        $ip = $params['ip'];
        $port = $params['port'];
        $agentName = $params['agentname'];
        $transportPort = $params['transport_port'];
        $clientPort = $params['client_port'];
        $opcodeName = 'DB_CLIENT_OP_MODIFY';
        //组合消息
        $msg = array(
            "agent_uuid" => $agentuuid,
            "client_ip" => $ip,
            "agent_name" => $agentName,
            "port" => $port,
            "transport_port" => $transportPort,
            "client_transport_port" => $clientPort

        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, Xphp::$_config['DB_TYPE']['UNKNOWN'], json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $DBProtectHandler = Xphp::instance('DBProtectOpcode');
        $operate = $DBProtectHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            //修改代理系统日志
            $this->systemLog('SYSTEM_LOG_MODIFY_DB_AGENT', array($ip));
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 获取客户端下数据库实例消息
     * @param int $dbType
     * @param string $agentuuid
     * @return array
     */
    private function getInstanceMsg($dbType, $agentuuid){
        $opcodeName = 'DB_CLIENT_OP_GET_INSTANCE_LIST';
        $msg = array(
            'agent_uuid' => $agentuuid,
            'db_type' => $dbType,
            'agent_uuid_list' => [],
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbDBMsg($nodeuuid, $dbType, $opcodeName, json_encode($msg), TRUE);
        if ($mbResult['result']) {
            return $mbResult['msg'];
        }
        return [];
    }

    /**
     * 获取数据库实例对象
     * @param array $params
     * @return string
     */
    public function getDBTree(array $params): string
    {
        $dbType = $params['type'];
        $agentuuid= $params['agentuuid'];
        $groupuuid = $params['groupuuid'];
        $agentname = $params['agentname'];
        $clusterFlag = $params['cluster_flag'];
        $clusterUuid = $params['cluster_uuid'];
        $clusterName = $params['cluster_name'];

        $editFlag = $params['editFlag']; //修改任务标志
        $taskuuid = $params['taskuuid']; //任务唯一标识
        $allDbType = Xphp::$_config['DB_TYPE'];
        $setFlag = Xphp::$_config['FLAG'];
        $editList = array();
        if ($editFlag) {
            //获取该任务所备份的数据库
            $sql = "SELECT dt.db_type, dl.instance_name, dl.db_uuid, dl.db_name
                    FROM db_task dt
                        INNER JOIN db_list dl ON dt.task_uuid=dl.task_uuid
                    WHERE dt.task_uuid = ? ";
            $sqlParams = [$taskuuid];
            if (
                $clusterFlag &&
                (
                    $dbType == $allDbType['MONGODB'] ||
                    $dbType == $allDbType['TIDB']
                )
            ) {
                $subSql = "SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid = ? ";
                $clusterAgentList = $this->dbSelect($subSql, [$clusterUuid]);
                $clusterAgentUuidList = array_column($clusterAgentList, 'agent_uuid');
                $clusterAgentUuids = "'" . implode("', '", $clusterAgentUuidList) . "'";
                $sql .= " AND dl.agent_uuid IN ($clusterAgentUuids) ";
            } else {
                $sql .= " AND dl.agent_uuid = ? ";
                $sqlParams[] = $agentuuid;
            }
            $taskData = $this->dbSelect($sql, $sqlParams);
            if ($taskData) {
                foreach ($taskData as $d) {
                    $editList[$d['instance_name']] = $d['db_uuid'];
                }
            }
        }
        // 查询数据库信息
        $sql = "SELECT baa.app_name, baa.dir_path, baa.agent_uuid, baa.cluster_uuid, baa.cluster_name, baa.cluster_type,
                    ba.group_uuid, baa.cluster_flag, baa.app_detail, ba.online_flag, baa.cluster_service_ip,
                    baa.app_auth_type, baa.app_uuid
                FROM bd_agent_app baa
                    INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                WHERE baa.app_type = ? ";
        if ($clusterFlag) {
            $sql .= " AND baa.cluster_uuid = ? ";
            $sqlParams = [$dbType, $clusterUuid];
        } else {
            $sql .= " AND baa.agent_uuid = ? ";
            $sqlParams = [$dbType, $agentuuid];
            if ($dbType == $allDbType['TIDB']) {
                $sql .= " AND baa.app_name = ? ";
                $sqlParams[] = $params['instance_name'];
            }
        }
        $dbInstanceList = $this->dbSelect($sql, $sqlParams);
        if (
            $clusterFlag &&
            (
                $dbType == $allDbType['TIDB'] ||
                $dbType == $allDbType['MONGODB']
            )
        ) {
            foreach ($dbInstanceList as $item) {
                $appDetail = json_decode($item['app_detail'], true);
                if ($dbType == $allDbType['TIDB']) {
                    if (strpos($appDetail['node_role'], 'deploy') !== false) {
                        $agentuuid = $item['agent_uuid'];
                        break;
                    }
                } elseif ($item['online_flag'] == Xphp::$_config['FLAG']['SET']) {
                    $agentuuid = $item['agent_uuid'];
                    break;
                }
            }
        }
        $instanceMsg = $this->getInstanceMsg($dbType, $agentuuid);  //获取数据库实例列表
        $instanceList = $instanceMsg ? $instanceMsg['instance_list'] : [];
        //获取在数据库备份任务中的信息
        $dbBackupInfo = $this->getDbBackupInfo();

        $utils = Xphp::instance('Utils');
        $taskInfoParams = [
            'agent_uuid' => $dbInstanceList[0]['agent_uuid'],
            'task_uuid' => $taskuuid,
            'db_type' => $dbType,
            'dbname' => $dbInstanceList[0]['app_name'],
            'instance_uuid' => $dbInstanceList[0]['app_name'],
            'name' => $dbInstanceList[0]['app_name'],
            'cluster_flag' => $utils->parseFlagtoBool($dbInstanceList[0]['cluster_flag']) && $dbInstanceList[0]['cluster_uuid'],
            'cluster_uuid' => $dbInstanceList[0]['cluster_uuid'],
        ];
        // 获取当前客户端集群备份的数据库
        $editClusterList = [];
        if ($editFlag) {
            $editClusterList = $this->getBackupClusterInstance($taskInfoParams, $dbBackupInfo);
        }
        // NOTE #9574: 添加排序
        $utils->secondaryArraySort($instanceList, 'instance');
        $node = array();
        $dbDes = include APP_PATH . 'dbprotect/DbDescription.php';
        $showAsClusterFlag = $clusterFlag && ($dbType == $allDbType['MONGODB'] || $dbType == $allDbType['TIDB']);
        $rootNodeInfo = [
            "id" => $showAsClusterFlag ? $clusterUuid : $agentuuid,
            "pid" => 0,
            "name" => $showAsClusterFlag ? $clusterName : $agentname,
            "title" => $showAsClusterFlag ? $clusterName : $agentname,
            "isParent" => true,
            "nocheck" => true,
            "type" => -1,
            "flush" => true,
            "icon" => $showAsClusterFlag ? './img/vm/hostcluster.png' : "./img/vm/host.png",
            "eventtype" => $showAsClusterFlag ? "cluster" : 'agent',
            "agentuuid" => $agentuuid,
            "groupuuid" => $groupuuid,
            'cluster_uuid' => $clusterUuid,
            "open" => true,
            'cluster_flag' => $showAsClusterFlag,
            'dbtype' => $dbType,
            'instanceuuid' => '',
            'app_uuid' => $dbInstanceList[0]['app_uuid'],
        ];

        $appDetail = json_decode($dbInstanceList[0]['app_detail'], true);
        if (
            ($showAsClusterFlag && $dbType == $allDbType['MONGODB'] && 2 == $dbInstanceList[0]['cluster_type']) ||  // MongoDB分片集群
            $dbType == $allDbType['TIDB']  // TiDB
        ) {  // 结构集群名->数据库, 加载数据库
            $rootNodeInfo['instanceuuid'] = $dbInstanceList[0]['app_name'];
            $rootNodeInfo['nocheck'] = false;
            $rootNodeInfo['clickshow'] = false;  // 已经加载数据库了，不能点击了
            $rootNodeInfo['cluster_name'] = $clusterName;
            $isInBackup = $this->judgeInClusterBackup($taskInfoParams, $dbBackupInfo);
            $rootNodeInfo['inbackup'] = $isInBackup;
            $rootNodeInfo['chkDisabled'] = $isInBackup;
            if ($editFlag) {  // 编辑备份任务
                $rootNodeInfo['chkDisabled'] = false;
                $rootNodeInfo['checked'] = false;
                $rootNodeInfo['dbuuid'] = $editList[$dbInstanceList[0]['app_name']];
            }
            $taskInfoParams['name'] = $rootNodeInfo['title'];
            $rootNodeInfo['title'] = $this->getClusterBackupTreeNodeTitle($taskInfoParams, $dbBackupInfo, $isInBackup);
            $node[] = $rootNodeInfo;
            // 加载数据库信息
            $instanceInfo = $this->getInstanceInfo($dbInstanceList[0]['app_name'], $agentuuid);
            $dbList = $this->scanAgentDbList($instanceInfo);
            if (is_string($dbList)) {
                return $dbList;
            }
            foreach ($dbList as $db) {
                $dbName = $db['db'];
                $node[] = [
                    'id' => $dbName,
                    'pid' => $rootNodeInfo['id'],
                    'name' => $dbName,
                    'title' => $dbName,
                    'nocheck' => true,
                    'eventtype' => 'db',
                    'cluster_uuid' => $clusterUuid,
                    'cluster_flag' => true,
                    'icon' => './img/db/database.png',
                    'type' => 2,
                    'dbtype' => $dbType,
                    'dbuuid' => '',
                    'groupuuid' => $groupuuid,
                    'agentuuid' => $agentuuid,
                    'isParent' => false,
                    'verifyflag' => true,
                    'clickshow' => false,  // 具体数据库将不能点击
                    'auth_type' => 0,
                    'app_uuid' => $dbInstanceList[0]['app_uuid'],
                ];
            }
        } else {
            if ($instanceList) {
                $node[] = $rootNodeInfo;
            }
            foreach ($instanceList as $instance) {
                $instancename = trim($instance['instance']);
                $taskInfoParams['name'] = $taskInfoParams['dbname'] = $taskInfoParams['instance_uuid'] = $instancename;
                $authFlag = false;
                $info = array(
                    "id" => $instancename,
                    "pid" => $showAsClusterFlag ? $clusterUuid : $agentuuid,
                    "name" => $instancename . '(' . Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED'] . ')',
                    "title" => $instancename . '(' . Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED'] . ')',
                    "nocheck" => false,
                    "type" => 1,
                    "flush" => true,
                    "icon" => "./img/vm/host.png",
                    "eventtype" => "instance",
                    "agentuuid" => $agentuuid,
                    "groupuuid" => $groupuuid,
                    "agentname" => $agentname,
                    "instanceuuid" => $instancename,
                    "verifyflag" => false,
                    "clickshow" => false,
                    "open" => false,
                    "dbtype" => $dbType,
                    "isParent" => false,
                    "chkDisabled" => true,
                    "dbuuid" => "",
                    'cluster_uuid' => '',
                    'cluster_flag' => false,
                    'auth_type' => 0,
                );
                foreach ($dbInstanceList as $dbInstance) {
                    if ($instancename != $dbInstance['app_name']) {
                        continue;
                    }
                    $info['verifyflag'] = true;
                    $info['name'] = $dbInstance['app_name'];
                    $info['title'] = $dbInstance['app_name'];
                    $info['clickshow'] = true;
                    $info['isParent'] = true;
                    $info['chkDisabled'] = false;
                    $info['app_uuid'] = $dbInstance['app_uuid'];
                    $info['auth_type'] = intval($dbInstance['app_auth_type']);
                    $info['cluster_uuid'] = $dbInstance['cluster_uuid'];
                    $info['cluster_flag'] = $utils->parseFlagToBool($dbInstance['cluster_flag']) && $dbInstance['cluster_uuid'];
                    $authFlag = true;
                }
                //都可勾选实例
                if ($dbType != $allDbType['SQLSERVER'] && $dbType != $allDbType['SAPHANA']) {  // SQL server和SAP HANA针对数据库，实例可以勾选
                    $inBackupFlag = $this->judgeInClusterBackup($taskInfoParams, $dbBackupInfo);
                    $info['chkDisabled'] = !$authFlag || $inBackupFlag;  // 如果这个实例有备份任务，就禁止勾选
                    $info['inbackup'] = $inBackupFlag;
                    $info['title'] = $this->getClusterBackupTreeNodeTitle($taskInfoParams, $dbBackupInfo, $inBackupFlag);
                }
                if ($editFlag) {
                    if ($dbType != $allDbType['SQLSERVER'] && $dbType != $allDbType['SAPHANA']) {
                        if (isset($editList[$instancename])) {  // 如果这里实例的备份任务就是当前任务，那允许勾选，并且勾选上
                            $info['chkDisabled'] = false;
                            $info['checked'] = false;  // NOTE #10643: 这里不勾选上，勾选操作由接口getBackupTreeOldInfo实现
                            $info['dbuuid'] = $editList[$instancename];
                        } elseif (isset($editClusterList[$instancename])) {
                            /**
                             * 如果当前实例的是一个集群实例，并且当前任务有关联这个集群的其他客户端的相同实例
                             * 1. 允许这个客户端的这个实例勾选
                             * 2. 默认不勾选上
                             */
                            $info['chkDisabled'] = false;
                            $info['checked'] = false;
                        }
                    }
                    if (
                        $dbType == $allDbType['DM'] ||
                        $dbType == $allDbType['KINGBASE'] ||
                        $dbType == $allDbType['HIGHGO'] ||
                        $dbType == $allDbType['OPENGAUSS'] ||
                        $dbType == $allDbType['VASTBASE']
                    ) {  // DM集群实例不一致实例
                        /**
                         * 查询拥有备份任务的DM集群
                         */
                        $sql = "SELECT baa.cluster_uuid
                                FROM db_list dl
                                    INNER JOIN bd_task bt ON bt.task_uuid = dl.task_uuid
                                    INNER JOIN bd_task_agent_list btal ON btal.task_uuid = dl.task_uuid
                                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = btal.agent_uuid
                                        AND baa.app_name = dl.instance_name
                                WHERE baa.app_type = ? AND baa.cluster_flag = ? AND bt.task_type = ?
                                    AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != '' ";
                        $sqlParams = [$dbType, Xphp::$_config['FLAG']['SET'], Xphp::$_config['TASKTYPE']['DB_BACKUP']];
                        $taskClusterData = $this->dbSelect($sql, $sqlParams);
                        if (is_array($taskClusterData) && $taskClusterData) {  // 存在DM集群有备份任务
                            /**
                             * 查询这些集群关联的客户端端和实例名
                             */
                            $clusterUuidList = array_values(array_unique(array_column($taskClusterData, 'cluster_uuid')));
                            $clusterUuids = "'" . implode("', '", $clusterUuidList) . "'";
                            $sql = "SELECT agent_uuid, app_name FROM bd_agent_app WHERE cluster_uuid IN ($clusterUuids) ";
                            $clusterAgentData = $this->dbSelect($sql);
                            foreach ($clusterAgentData as $clusterMap) {
                                if ($agentuuid == $clusterMap['agent_uuid'] && $instancename == $clusterMap['app_name']) {
                                    // 表示这个客户端有备份任务
                                    $info['chkDisabled'] = false;
                                    $info['checked'] = false;
                                }
                            }
                        }
                    }
                }

                // Oracle以操作系统身份认证不能用于备份任务
                if ($dbType == Xphp::$_config['DB_TYPE']['ORACLE'] && $dbDes['ORACLE_AUTH_TYPE']['OS_AUTH'] == $info['auth_type']) {
                    $info['name'] = $info['name'] . '(' . Xphp::$_lang['UI_DB_INSTANCE_ORACLE_OS_AUTH_NOT_ALLOWED_BACKUP'] . ')';
                    $info['title'] = Xphp::$_lang['UI_DB_INSTANCE_ORACLE_OS_AUTH_TIPS'];
                    $info['chkDisabled'] = true;
                    $info['clickshow'] = false;
                }

                $node[] = $info;
            }
        }

        return json_encode($node);
    }

    /**
     * 扫描客户端实例的数据库信息
     * @param array $instanceInfo
     * @return array|string
     */
    private function scanAgentDbList(array $instanceInfo)
    {
        $opName = "DB_INSTANCE_OP_SCAN_DB";
        if($instanceInfo['db_type'] == Xphp::$_config['DB_TYPE']['ORACLE']){
            $opName = "DB_INSTANCE_OP_SCAN_TABLE_SPACE";
        }
        $dbProtectOpcode = Xphp::instance('DBProtectOpcode');
        $operate = $dbProtectOpcode->getOpcodeDes($opName);
        $msg = array(
            'agent_uuid' => $instanceInfo['agent_uuid'],
            'db_type' => $instanceInfo['db_type'],
            'instance_name' => $instanceInfo['instance_name'],
            'auth_type' => $instanceInfo['auth_type'],
            'username' => $instanceInfo['username'],
            'password' => $instanceInfo['password']
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbDBMsg($nodeUuid, $instanceInfo['db_type'], $opName, json_encode($msg), true);
        $dbList = [];
        if ($mbResult['result']) {
            if ($instanceInfo['db_type'] == Xphp::$_config['DB_TYPE']['ORACLE']) {
                foreach ($mbResult['msg']['table_space_list'] as $item) {
                    $dbList[] = [
                        'db' => $item['table_space'],
                    ];
                }
            } else {
                $dbList = $mbResult['msg']['db_list'];
            }
        } else {
            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
        return $dbList;
    }

    /**
     *
     * 异步获取实例下数据库列表
     * @param array $params
     * @return string
     */
    public function getBackupSyncAgent(array $params): string
    {
        $instanceuuid = $params['id'];
        $agentuuid = $params['agentuuid'];
        $groupuuid = $params['groupuuid'];
        $agentName = $params['agentip'];
        $taskuuid = $params['taskuuid'];    //修改任务uuid
        $editFlag = $params['editFlag'];    //修改任务标志
        $dbType = $params['dbtype'];
        $clusterFlag = $params['cluster_flag'];
        $clusterUuid = $params['cluster_uuid'];
        $allDbType = Xphp::$_config['DB_TYPE'];

        // 查询数据库信息
        $sql = "SELECT baa.app_name, baa.dir_path, baa.agent_uuid, baa.cluster_uuid, baa.cluster_name,
                    ba.group_uuid, baa.cluster_flag, baa.app_detail, ba.online_flag, baa.cluster_service_ip,
                    baa.app_uuid
                FROM bd_agent_app baa
                    INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                WHERE baa.app_type = ? AND baa.app_name = ? ";
        $sqlParams = [$dbType, $instanceuuid];
        if ($clusterFlag) {
            $sql .= " AND baa.cluster_uuid = ? ";
            $sqlParams[] = $clusterUuid;
        } else {
            $sql .= " AND baa.agent_uuid = ? ";
            $sqlParams[] = $agentuuid;
        }
        $dbInstanceList = $this->dbSelect($sql, $sqlParams);
        if (
            $clusterFlag &&
            (
                $dbType == $allDbType['TIDB'] ||
                $dbType == $allDbType['MONGODB']
            )
        ) {
            foreach ($dbInstanceList as $item) {
                $appDetail = json_decode($item['app_detail'], true);
                if ($dbType == $allDbType['TIDB']) {
                    if (strpos($appDetail['node_role'], 'deploy') !== false) {
                        $agentuuid = $item['agent_uuid'];
                        break;
                    }
                } elseif ($item['online_flag'] == Xphp::$_config['FLAG']['SET']) {
                    $agentuuid = $item['agent_uuid'];
                    break;
                }
            }
        }
        $instanceInfo = $this->getInstanceInfo($instanceuuid, $agentuuid);
        $dbList = $this->scanAgentDbList($instanceInfo);
        if (is_string($dbList)) {
            return $dbList;
        }
        $node = array();
        $nocheckFlag = ($dbType != $allDbType['SQLSERVER'] && $dbType != $allDbType['SAPHANA']);
        //获取在当前任务中的数据库列表
        $dbTaskList = $this->getDBInBackupList($instanceuuid, $agentuuid, $taskuuid);
        //获取在数据库备份任务中的信息
        $dbBackupInfo = $this->getDbBackupInfo();
        $taskInfoParams = [
            'instance_uuid' => $instanceuuid,
            'agent_uuid' => $agentuuid,
            'task_uuid' => $taskuuid,
            'db_type' => $dbType,
        ];

        $utils = Xphp::instance('Utils');
        // 获取当前客户端在当前任务的集群中的备份数据库列表
        $clusterDbTaskList = $this->getClusterDBBackupList($taskInfoParams, $dbBackupInfo);
        foreach ($dbList as $db){
            $dbName = $db['db'];
            $taskInfoParams['dbname'] = $taskInfoParams['name'] = $dbName;
            $inBackupFlag = $this->judgeInClusterBackup($taskInfoParams, $dbBackupInfo);
            $title = $this->getClusterBackupTreeNodeTitle($taskInfoParams, $dbBackupInfo, $inBackupFlag);
            $inTaskFlag = false;
            if($editFlag){
                $inTaskFlag = in_array($dbName, $dbTaskList) || in_array($dbName, $clusterDbTaskList);
            }
            $node[] = array(
                "id" => $dbName,
                "pid" => $instanceuuid,
                "name" => $dbName,
                "title" => $title,
                "nocheck" => $nocheckFlag,
                "type" => 2,
                "flush" => true,
                "icon" => "./img/platform/storage.png",
                "eventtype" => "db",
                "agentuuid" => $agentuuid,
                "groupuuid" => $groupuuid,
                "agentname" => $agentName,
                "instanceuuid" => $instanceuuid,
                "dbuuid" => "",
                "dbtype" => $dbType,
                "inbackup" => $inBackupFlag,
                "chkDisabled" => $inBackupFlag && !$inTaskFlag,
                'app_uuid' => $dbInstanceList[0]['app_uuid'],
                'cluster_uuid' => $dbInstanceList[0]['cluster_uuid'],
                'cluster_flag' => $utils->parseFlagtoBool($dbInstanceList[0]['cluster_flag']) && $dbInstanceList[0]['cluster_uuid'],
            );
        }
        $utils->secondaryArraySort($node, 'name');
        $msg = array(
            're' => true,
            'msg' => $node
        );

        return json_encode($msg);
    }

    /**
     * 获取实例认证列表
     * @param mixed $params
     * @return string
     */
    public function getDBInstance($params): string
    {
        $type = intval($params['type']);
        $name = $params['app_name'];
        $agentUuid= $params['agentuuid'];
        $agentInfo = $this->getAgentInfo($agentUuid);
        if ($agentInfo['agentname'] == $agentInfo['ip']) {
            $agentName = $agentInfo['ip']."(". $agentInfo['hostname'] .")";
        } else {
            $agentName = $agentInfo['ip']."(". $agentInfo['agentname'] .")";
        }
        $start = $params['start'];
        $length = $params['length'];
        $sql ="SELECT app_listen_ip, app_uuid, app_name, app_type,
                app_version, app_auth_type, app_username, app_password, UNIX_TIMESTAMP(update_time) AS update_time,
                install_app_username, app_detail, cluster_flag, cluster_uuid, cluster_name, cluster_service_ip
              FROM bd_agent_app
              WHERE agent_uuid = ? AND app_type = ? ORDER BY update_time DESC LIMIT ?, ? ";

        $data = $this->dbSelect($sql, array($agentUuid, $type, $start, $length));

        if ($type != Xphp::$_config['DB_TYPE']['MYSQL'] && $type != Xphp::$_config['DB_TYPE']['MARIA']) {
            $instanceMsg = $this->getInstanceMsg($type, $agentUuid);  //获取数据库实例列表
            $instanceList = $instanceMsg ? $instanceMsg['instance_list'] : [];
        } else {
            $instanceList = [];
        }
        $count = count($instanceList);
        $utils = Xphp::instance("Utils");
        $records = array();
        $list = array();
        $dbList = array();
        if (!empty($instanceList)) {
            foreach ($instanceList as $index => $instance) {
                $dbInfo = array(
                    'instance_name' => trim($instance['instance']),
                    'user_name' => Xphp::$_config['NULLSPACE'],
                    'password' => '',
                    'auth_type' => 0,
                    'save_time' => '',
                    'db_type' => $type,
                    'detail' => "",
                    'authFlag' => false,
                    'clusterFlag' => false,
                    'cluster_uuid' => '',
                    'cluster_name' => '',
                    'cluster_service_ip' => '',
                    'version' => Xphp::$_config['NULLSPACE'],
                    'listen_ip' => $agentInfo['ip'],
                    'port' => Xphp::$_config['NULLSPACE'],
                );
                if (!empty($data)) {
                    foreach ($data as $d) {
                        if (trim($instance['instance']) != $d['app_name']) {
                            continue;
                        }
                        $appDetail = json_decode($d['app_detail'], true);
                        $dbInfo['user_name'] = $d['app_username'];
                        $dbInfo['password'] = base64_encode($utils->ptPassDecrypt($d['app_password']));
                        $dbInfo['auth_type'] = $d['app_auth_type'];
                        $dbInfo['save_time'] = $d['update_time'];
                        $dbInfo['install_db_username'] = $d['install_app_username'];
                        $dbInfo['detail'] = $d['app_detail'];
                        $dbInfo['authFlag'] = true;
                        $dbInfo['clusterFlag'] = $utils->parseFlagToBool(intval($d['cluster_flag'])) && $d['cluster_uuid'];
                        $dbInfo['cluster_uuid'] = $d['cluster_uuid'];
                        $dbInfo['cluster_name'] = $d['cluster_name'];
                        $dbInfo['cluster_service_ip'] = $d['cluster_service_ip'];
                        $dbInfo['version'] = $d['app_version'];
                        $dbInfo['listen_ip'] = $d['app_listen_ip'];
                        $dbInfo['port'] = $appDetail['port'] ?? Xphp::$_config['NULLSPACE'];
                    }
                }
                $list[] = $dbInfo;
            }

        } else {
            if (!empty($data)) {
                foreach ($data as $d) {
                    $appDetail = json_decode($d['app_detail'], true);
                    $list[] = array(
                        'instance_name' => trim($d['app_name']),
                        'user_name' => $d['app_username'],
                        'password' => base64_encode($utils->ptPassDecrypt($d['app_password'])),
                        'auth_type' => $d['app_auth_type'],
                        'save_time' => $d['update_time'],
                        'db_type' => $type,
                        'detail' => $d['app_detail'],
                        'authFlag' => true,
                        'clusterFlag' => $utils->parseFlagToBool(intval($d['cluster_flag'])) && $d['cluster_uuid'],
                        'cluster_uuid' => $d['cluster_uuid'],
                        'cluster_name' => $d['cluster_name'],
                        'cluster_service_ip' => $d['cluster_service_ip'],
                        'version' => $d['app_version'],
                        'listen_ip' => $d['app_listen_ip'],
                        'port' => $appDetail['port'] ?? Xphp::$_config['NULLSPACE'],
                    );
                }
            }
        }
        $dbDes = include APP_PATH . 'dbprotect/DbDescription.php';
        if (!empty($list)) {
            // 查询有无任务<数据库备份任务/数据库恢复任务>
            $clientHandler = Xphp::instance('ClientHandler');
            $agentUuidList = $clientHandler->getAllAgentUuidFromAgentUuid($agentUuid);
            $agentUuidDes = "'" . implode("', '", $agentUuidList) . "'";
            $allTaskType = Xphp::$_config['TASKTYPE'];
            $allowTaskType = [$allTaskType['DB_BACKUP'], $allTaskType['DB_RECOVERY']];
            $allowTaskTypeDes = "'" . implode("', '", $allowTaskType) . "'";
            $taskSql = "SELECT bt.task_uuid, bt.task_name, bt.task_status, bt.task_type,
                        dl.instance_name, dl.db_name
                    FROM bd_task bt
                        INNER JOIN bd_task_agent_list btal ON bt.task_uuid = btal.task_uuid
                        LEFT JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                        INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                    WHERE btal.agent_uuid IN ($agentUuidDes) AND bt.task_type IN ($allowTaskTypeDes)
                        AND dt.db_type = ? ";
            $taskData = $this->dbSelect($taskSql, [$type]);
            foreach ($list as $l){
                $authTypeDes = $dbDes['AUTH_TYPE_DES'][intval($l['auth_type'])];
                if(intval($l['db_type']) != Xphp::$_config['DB_TYPE']['SQLSERVER']){
                    $authTypeDes = Xphp::$_config['NULLSPACE'];
                }
                $version = $l['version'];
                if(empty($version)){
                    $version = Xphp::$_config['NULLSPACE'];
                }
                $detail = json_decode($l['detail'],true);
                $checkBox = '<input type="checkbox" name="id[]" value="' . $l['instance_name']. '">';
                $taskInfo = [];
                foreach ($taskData as $task) {
                    if ($l['instance_name'] == $task['instance_name']) {
                        $taskInfo[] = [
                            'db_name' => $task['db_name'],
                            'instance_name' => $task['instance_name'],
                            'task_uuid' => $task['task_uuid'],
                            'task_name' => $task['task_name'],
                            'task_type' => $task['task_type'],
                            'task_status' => $task['task_status'],
                        ];
                    }
                }
                $dbList[] = array(
                    $checkBox,
                    $l['instance_name'],
                    $version,
                    $l['port'],  // 端口号
                    $l['user_name'],
                    $authTypeDes,
                    $this->parseDate($l['save_time']),
                    !empty($detail['cluster_path'])?$detail['cluster_path']: "",
                    $this->getDbInstanceDetails($l, $type),
                    $l['listen_ip'],
                    $agentName,
                    $taskInfo ?: false,
                );
            }
        }
        $records["data"] = $dbList;
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;
        return  json_encode($records);
    }

    /**
     * 删除客户端
     * @param unknown $params
     */
    public function deleteAgent($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_db_agent_manager_delete");
        $uuids = $params['uuids'];
        $deleteTenantFlag = $params['flag'];
        $ipDes = $this->getAgentnamebyUuids($uuids);
        //删除数据库代理检测
        if(!$deleteTenantFlag){
            $this->deleteAgentCheck($uuids);
        }
        $opcodeName = 'DB_CLIENT_OP_DELETE';
        //组合消息
        $msg = array(
            "agent_uuid_list" => $uuids

        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbDBMsg($nodeuuid, Xphp::$_config['DB_TYPE']['SQLSERVER'], $opcodeName, json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $DBProtectHandler = Xphp::instance('DBProtectOpcode');
        $operate = $DBProtectHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            //添加资源和用户关联
            $userHandler = Xphp::instance('UsersHandler');
            $userHandler->pDeleteUserResource(Xphp::$_user['useruuid'], $uuids);
            //删除代理系统日志
            $this->systemLog('SYSTEM_LOG_DELETE_DB_AGENT', array($ipDes));
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 得到数据库备份任务名
     * @param unknown $params
     */
    public function getDBBackupTaskName($params){
        return $this->getValidTaskName(Xphp::$_lang['WEB_DB_BACKUP_TASKNAME']);
    }

    /**
     * 得到数据库恢复任务名
     * @param unknown $params
     */
    public function getDBRecoveryTaskName($params){
        return $this->getValidTaskName(Xphp::$_lang['WEB_DB_RECOVERY_TASKNAME']);
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
            if(empty($data)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * 得到备份数据库数组
     * @param array $backupDbInfo 备份源信息
     * @param int   $dbType       数据库类别
     * @return array
     */
    private function groupBackupDB(array $backupDbInfo, int $dbType): array
    {
        $dbInfoMsg = array();
        foreach ($backupDbInfo as $db){
            $detail = '';
            if ($dbType == Xphp::$_config['DB_TYPE']['ORACLE']) {
                $detail = json_encode($db['detail'], JSON_UNESCAPED_SLASHES);
            }
            $tmp = [
                'group_uuid' => $db['groupuuid'],
                'agent_uuid' => $db['agentuuid'],
                'instance_name' => $db['instancename'],
                'db_name' => $db['dbname'],
                'dir_path' => "",
                'data_file_path' => "",
                'log_file_path' => "",
                'before_task_script' => $db['before_task_script'] ? json_encode($db['before_task_script']) : '',
                'after_task_script' =>$db['after_task_script'] ? json_encode($db['after_task_script']) : '',
                'db_uuid' => '',
                'error_code' => 0,
                'detail' => $detail,
            ];
            $dbInfoMsg[] = $tmp;
        }
        return $dbInfoMsg;
    }

    /**
     * 认证实例信息
     * @param unknown $params
     */
    public function AuthDBInstance($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_db_agent_manager_certification");
        $agentuuid = $params['agentuuid'];
        $dbtype = intval($params['dbtype']);
        $authtype = intval($params['authtype']);
        $instancename = $params['instancename'];
        $username = $params['username'];
        $password = $params['password'];
        $listenIp = $params['listenip'];    //监听IP
        $clusterName = $params['cluster_name'];
        $clusterServiceIp = $params['cluster_service_ip'];
        $oldInstanceInfo = $this->getInstanceInfo($instancename, $agentuuid);
        $utils = Xphp::instance('Utils');
        $allDbType = Xphp::$_config['DB_TYPE'];
        $setFlag = Xphp::$_config['FLAG'];

        if(!empty($oldInstanceInfo)){
            $oldPassword = $oldInstanceInfo['password'];
            if(!empty($oldPassword)){
                $oldDecryptPassword = $utils->ptPassDecrypt($oldPassword);
                if($oldDecryptPassword == $utils->ptPassDecrypt(base64_decode($password))){
                    $password = base64_encode($oldDecryptPassword);
                }
            }
        }
        $installname = $params['installname'];
        $verifydetail = $params['verifydetail'];
        $opcodeName = 'DB_INSTANCE_OP_VERIFY_AUTH';
        //判断是否有集群，有1 无2
        if ($params['dbtype'] == $allDbType['OPENGAUSS'] || $params['dbtype'] == $allDbType['VASTBASE']) {
            $clusterFlag = $setFlag['SET'];
        } else {
            $clusterFlag = $utils->parseBoolToFlag($params['clusterflag']);
        }
        $clusterInfo = array();
        $allowClusterDbType = [
            $allDbType['SQLSERVER'],
            $allDbType['ORACLE'],
            $allDbType['HIGHGO'],
            $allDbType['OPENGAUSS'],
            $allDbType['VASTBASE'],
            $allDbType['MONGODB'],
            $allDbType['TIDB'],
            $allDbType['SAPHANA'],
        ];
        if (in_array($dbtype, $allowClusterDbType)) {
            $clusterInfo = $params['clusterInfo'];
        }

        //组合消息
        $msg = array(
            'agent_uuid' => $agentuuid,
            'db_type' => $dbtype,
            'instance_name' => $instancename,
            'auth_type' => $authtype,
            'username' => $username,
            'password' => $password,
            'verify_detail' => $verifydetail,
            'install_db_username'=> $installname,
            'cluster_flag' => $clusterFlag,
            'cluster_name' => $clusterName,
            'cluster_service_ip' => $clusterServiceIp,
            'cluster_info' => $clusterInfo,
            'listen_ip' => $listenIp,
            'listen_port' => 0
        );

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbDBMsg($nodeuuid, $dbtype, $opcodeName, json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $DBProtectHandler = Xphp::instance('DBProtectOpcode');
        $operate = $DBProtectHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            //认证实例系统日志
            //查询主机名 ip 数据库类型DB_TYPE
            $sql= "select bda.agent_name,bda.hostname,bda.ip from bd_agent bda where bda.agent_uuid= ?" ;
            $data = $this->dbSelect($sql, array($agentuuid));
            $name = $data[0]['hostname']?: $data[0]['agent_name'];
            $paramList = array(
                $name."[".$data[0]['ip']."]",
                Xphp::$_config['DB_TYPE_DES'][$dbtype],
                $instancename
            );
            $this->systemLog('SYSTEM_LOG_DB_AUTH_INSTANCE',$paramList);
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取时间点树
     * @param array $params
     * @return string
     */
    public function getTimepointTree($params){
        $showtype = intval($params['showtype']);
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $manageflag  = $params['manageflag'];       //备份数据管理标志,备份数据管理的树带有checkbox
        $dataflag = $params['dataflag'];			//备份数据标志
        $recoveryFlag = $params['recoveryFlag'] ?? false;  //用于恢复任务
        $dbType = intval($params['db_type'] ?? 0);
        if($showtype == Xphp::$_config['POINTSHOWTYPE']['TIMEGROUP']){
            //如果是按时间点方式显示
            return $this->getTimepointTimeGroup($nodeuuid);
        }

        $node = array();
        $this->paramsCheck($showtype);
        $sql = "SELECT bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, UNIX_TIMESTAMP(bbt.task_create_time) AS task_create_time,
    			    bbt.task_uuid, bbt.user_uuid, bbt.user_name, bbt.timepoint,
                    dbt.instance_name, dbt.db_name,
    			    dbt.db_uuid, dbt.dir_path, dbt.db_type, dbt.agent_uuid, dbt.agent_ip, dbt.db_config, dbt.cluster_uuid,
                    dbt.cluster_name,
                    bsr.node_uuid
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.deleted_flag = ? AND bbt.available_flag = ? AND bbt.import_flag = ?
                AND bbt.module_type = ".Xphp::$_config['MODULE_TYPE']['DB']."
	                AND bbt.data_local_flag = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if(!empty($_SESSION['tenantuuid'])){
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if($dataflag && $tenantMangerFlag && $allManageFlag){
                $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('".$userListDes."')";
            }else{
                // 权限重构
                $authUser = $_SESSION['authUser']['db_protect_look'] ?? [];
                if ($authUser) {
                    $userUuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    if ($recoveryFlag) {  // 用于恢复任务需要拥有操作权限
                        $operateAuthUser = $_SESSION['authUser']['db_protect_operate'] ?? [];
                        $userUuidArr = array_intersect($userUuidArr, $operateAuthUser);  // 取交集
                    }
                    $userUuids = "('" . implode("','", $userUuidArr) . "')";
                    $sql .= " and bbt.user_uuid IN $userUuids ";
                } else {
                    $sql .= " and bbt.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                }
            }
        }
        if (!$_SESSION['tenantuuid']) {  // 非租户用户不能查看租户的资源
            $userUuidData = $this->dbSelect("SELECT user_uuid FROM mt_user_tenant");
            if (is_array($userUuidData) && $userUuidData) {
                $userUuidList = array_column($userUuidData, 'user_uuid');
                $userUuidList = array_values(array_unique($userUuidList));
                $userUuids = "'" . implode("', '", $userUuidList) . "'";
                $sql .= " AND bbt.user_uuid NOT IN ($userUuids) ";
            }
        }

        if($dataflag){
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], Xphp::$_config['MODULE_TYPE']['DB']));
        }
        if ($nodeuuid){
            //如果是选择了某个节点,显示这个节点下面的
            // 云存储，使用bbt.real_node_uuid；其他存储使用bsr.node_uuid
            $sql .= " and ((bsr.storage_type != ? AND bsr.node_uuid = ?) OR (bsr.storage_type = ? AND bbt.real_node_uuid = ?) )";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid));
        }
        if ($dbType) {
            $sql .= " AND dbt.db_type = ? ";
            $sqlParams[] = $dbType;
        }
        $sql .= " order by bbt.task_name, dbt.db_name, dbt.instance_name,  dbt.db_timepoint_id, bbt.timepoint desc  ";


        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();



        //定义task db timepoint
        $dbtype = array();
        $task = array();
        $agentMap = [];  // 客户端
        $db = array();
        $instance = array();
        $pid = null;
        $jobHandler = Xphp::instance('JobHandler');
        $utils = Xphp::instance('Utils');
        $dbDes = include APP_PATH . 'dbprotect/DbDescription.php';
        $allDbType = Xphp::$_config['DB_TYPE'];
        foreach ($data as $d) {
            $taskuuid = $d['task_uuid'];
            $agentuuid = $d['agent_uuid'];
            $dbConfig = json_decode($d['db_config'], true);
            $cluserName = $d['cluster_name'];
            $clusterUuid = $d['cluster_uuid'];
            $clusterFlag = !!$clusterUuid;
            //检查并添加数据库类型
            if(!in_array($d['db_type'], $dbtype)){
                $type = intval($d['db_type']);
                $name = $dbDes['DB_TYPE_DES'][$type];
                $node[$d['db_type']] = array(
                    "id" => $type,
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => !$manageflag,
                    "type" => -1,
                    "icon" => './img/vm/host.png',
                    "agentuuid" => $d['agent_uuid'],
                    "dbtype" => $d['db_type'],
                    'eventtype' => 'db_type',
                );
                $dbtype[] = $type;
            }
            $dbType = intval($d['db_type']);

            $taskName = $jobHandler->getTimepointTaskname($d['task_uuid'],$d['task_name']);
            //检查并添加task
            if (
                !in_array($dbType.$taskuuid, $task) &&
                ($dbType != $allDbType['ORACLE'] || !$recoveryFlag) && // Oracle恢复任务不显示任务
                ($dbType != $allDbType['TIDB'] || !$recoveryFlag) // TiDB恢复任务不显示任务
            ) {
                $task[] = $dbType.$taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                // 副本数据
                if($d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                    $name = $taskName . "(" . Xphp::$_lang['UI_COPY_DATA'] . ")";
                }
                // 归档数据
                if($d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                    $name = $taskName . "(" . Xphp::$_lang['UI_ARCHIVE_DATA'] . ")";
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != Xphp::$_user['useruuid']){
                    $name .= "(" .$d['user_name'].")";
                }
                $node[$dbType . $taskuuid] = array(
                    "id" => $dbType.$taskuuid,
                    "pId" => $dbType,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => !$manageflag,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
                    "agentuuid" => $d['agent_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "dbtype" => $d['db_type'],
                    "isParent" => true,
                    'eventtype' => 'task',
                );
            }
            if (
                ($dbType == $allDbType['ORACLE'] && $recoveryFlag) ||  // Oracle恢复显示客户端
                ($dbType == $allDbType['TIDB'] && $recoveryFlag)  // TIDB恢复显示客户端
            ) {
                if ($d['cluster_uuid']) {  // 集群
                    if (!isset($agentMap[$dbType . $d['cluster_uuid']])) {
                        $agentMap[$dbType . $d['cluster_uuid']] = 1;
                        $clusterName = $d['db_name'] . '(' . $d['cluster_name'] . ')';
                        if (!$d['cluster_name']) {
                            $clusterName = $d['db_name'];
                        }
                        if ($dbType == $allDbType['TIDB']) {
                            $clusterName = $d['cluster_name'];
                        }
                        $node[$dbType . $d['cluster_uuid']] = array(
                            "id" => $dbType . $d['cluster_uuid'],
                            "pId" => $dbType,
                            "name" => $clusterName,
                            "nocheck" => false,
                            "type" => 0,
                            "icon" => './img/platform/db-cluster.png',
                            "title" => $clusterName,
                            "agentuuid" => $d['agent_uuid'],
                            "instanceuuid" => $d['instance_name'],
                            "dbtype" => $d['db_type'],
                            "isParent" => true,
                            'open' => true,
                            'eventtype' => 'cluster',
                            'cluster_uuid' => $d['cluster_uuid'],
                            'cluster_flag' => true,
                            'dir_path' => $clusterName,
                            'backup_instance_list' => [
                                $agentuuid => [
                                    'instance_name' => $d['instance_name'],
                                    'agent_uuid' => $d['agent_uuid'],
                                ]
                            ],
                            'app_service_name' => $d['db_name'],
                            'newest_timepoint' => $d['timepoint'],
                        );
                        // 构建RAC的各个节点信息
                        $start = strpos($d['dir_path'], '(');
                        $end = strrpos($d['dir_path'], ')');
                        $agentStr = substr($d['dir_path'], $start + 1, $end - $start - 1);
                        $agentList = explode(',', $agentStr);
                        if ($dbType == $allDbType['TIDB']) {
                            $agentList = explode(', ', $agentStr);
                        }
                        foreach ($agentList as $agentInfoStr) {
                            $instanceInfo = explode('/', $agentInfoStr);
                            $agentIp = trim($instanceInfo[0]);
                            $agentInstanceName = trim($instanceInfo[1]);
                            $agentName = $agentInstanceName . '(' . $agentIp . ')';
                            if ($dbType == $allDbType['TIDB']) {
                                $agentInstanceName = $d['cluster_name'];
                            }
                            $node[$dbType . $d['cluster_uuid'] . $agentInfoStr] = array(
                                "id" => $dbType . $d['cluster_uuid'] . $agentInfoStr,
                                "pId" => $dbType . $d['cluster_uuid'],
                                "name" => $agentName,
                                "title" => $agentName,
                                "nocheck" => true,
                                "type" => 0,
                                "icon" => './img/platform/storage.png',
                                "instanceuuid" => $agentInstanceName,
                                'agent_ip' => $agentIp,
                                "dbtype" => $d['db_type'],
                                "isParent" => false,
                                'eventtype' => 'cluster_instance',
                            );
                        }
                    } else {
                        if (strtotime($d['timepoint']) > strtotime($node[$dbType. $d['cluster_uuid']]['newest_timepoint'])) {
                            $clusterName = $d['db_name'] . '(' . $d['cluster_name'] . ')';
                            if (!$d['cluster_name']) {
                                $clusterName = $d['db_name'];
                            }
                            $node[$dbType. $d['cluster_uuid']]['newest_timepoint'] = $d['timepoint'];
                            $node[$dbType. $d['cluster_uuid']]['name'] = $clusterName;
                            $node[$dbType. $d['cluster_uuid']]['title'] = $clusterName;
                        }
                        $node[$dbType . $d['cluster_uuid']]['backup_instance_list'][$agentuuid] = [
                            'instance_name' => $d['instance_name'],
                            'agent_uuid' => $d['agent_uuid'],
                        ];
                    }
                } else {  // 单机
                    if (!isset($agentMap[$dbType . $agentuuid])) {
                        $agentMap[$dbType . $agentuuid] = 1;
                        $node[$dbType . $agentuuid] = array(
                            "id" => $dbType . $agentuuid,
                            "pId" => $dbType,
                            "name" => $d['instance_name'] . '(' . $d['agent_ip'] . ')',
                            'title' => $d['instance_name'] . '(' . $d['agent_ip'] . ')',
                            "open" => false,
                            "nocheck" => false,
                            "type" => 0,
                            "icon" => './img/platform/storage.png',
                            "agentuuid" => $d['agent_uuid'],
                            "dbtype" => $d['db_type'],
                            "isParent" => false,
                            'eventtype' => 'instance',
                            'cluster_flag' => $clusterFlag,
                            'cluster_uuid' => $clusterUuid,
                            'db_config' => $dbConfig,
                            "dir_path" => $d['dir_path'],
                            "instanceuuid" => $d['instance_name'],
                            "dbuuid" => $d['db_uuid'],
                            "taskuuid" => $taskuuid,
                            "nodeuuid" => $d['node_uuid'],
                        );
                    }
                }
                continue;
            }
            $dbuuid = $d['db_uuid'];
            $pId = $dbType.$taskuuid;
            $dbname = $d['db_name']."(".$d['agent_ip'].")";
            $id = $dbType. $agentuuid. $dbuuid . $taskuuid;
            if ($dbType == $allDbType['ORACLE'] && $recoveryFlag) {  // Oracle恢复显示客户端
                $pId = $dbType . $agentuuid;
                $id = $dbType . $agentuuid . $d['instance_name'];
                $dbname = $d['db_name'];
            }
            //sqlserver和SAP Hana多显示一层
            if($dbType == $allDbType['SQLSERVER'] || $dbType == $allDbType['SAPHANA']){
                $instancename = $d['instance_name'];
                //检查并添加INSTANCE
                if(!in_array($dbType.$agentuuid.$instancename. $taskuuid, $instance)){
                    $instance[] = $dbType.$agentuuid.$instancename. $taskuuid;
                    $node[$dbType . $agentuuid . $instancename . $taskuuid] = array(
                        "id" => $dbType.$agentuuid.$instancename. $taskuuid,
                        "pId" => $dbType.$taskuuid,
                        "name" => $d['instance_name']."(".$d['agent_ip'].")",
                        "open" => false,
                        "nocheck" => !$manageflag,
                        "type" => 5,
                        "icon" => './img/vm/host.png',
                        "title" => $d['instance_name']."(".$d['agent_ip'].")",
                        "agentuuid" => $d['agent_uuid'],
                        "instanceuuid" => $d['instance_name'],
                        "taskuuid" => $taskuuid,
                        "nodeuuid" => $d['node_uuid'],
                        "createtime" => $this->parseDate($d['task_create_time']),
                        "dbtype" => $d['db_type'],
                        "isParent" => true,
                        'eventtype' => 'instance',
                    );
                }

                $dbname = $d['db_name'];
                $pId = $dbType.$agentuuid.$instancename. $taskuuid;
                $id = $dbType.$agentuuid. $instancename. $dbuuid .$taskuuid;
            }

            if (in_array($id, $db)) {
                continue;
            }
            // 检查并添加db
            $db[] = $id;
            $dbIcon = './img/platform/storage.png';
            if ($allDbType['MONGODB'] == $dbType && $clusterFlag) {
                $dbname = $cluserName;
                $dbIcon = './img/vm/hostcluster.png';
            }
            if ($allDbType['TIDB'] == $dbType) {
                $dbname = $d['instance_name'];
                $dbIcon = './img/vm/hostcluster.png';
            }
            $isParent = true;
            $nocheck = !$manageflag;
            $clickShow = true;
            if (
                ($allDbType['SAPHANA'] == $dbType && $recoveryFlag) ||
                ($allDbType['ORACLE'] == $dbType && $recoveryFlag)
            ) {
                $isParent = false;
                $nocheck = false;
                $clickShow = false;
            }
            $node[$id] = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $dbname,
                "open" => false,
                "nocheck" => $nocheck,
                "type" => 1,
                "icon" => $dbIcon,
                "title" => Xphp::$_lang['WEB_DB_DATABASE_SRC_PATH'] . ': ' . $d['dir_path'],
                "agentuuid" => $d['agent_uuid'],
                "instanceuuid" => $d['instance_name'],
                "dbuuid" => $d['db_uuid'],
                "taskuuid" => $taskuuid,
                "nodeuuid" => $d['node_uuid'],
                "createtime" => $this->parseDate($d['task_create_time']),
                "dbtype" => $d['db_type'],
                "dir_path" => $d['dir_path'],
                "isParent" => $isParent,
                "clickshow" => $clickShow,
                'eventtype' => 'db',
                'cluster_flag' => $clusterFlag,
                'cluster_uuid' => $clusterUuid,
                'db_config' => $dbConfig,
            );
        }
        $utils = Xphp::instance('Utils');
        $node = array_values($node);
        $utils->secondaryArraySort($node, 'name');
        return json_encode($node);
    }

    /**
     * 得到恢复按时间点分组显示方式树
     * @param string $nodeuuid  节点UUID
     * @param boolean  $instantflag 瞬时恢复标志
     */
    private function getTimepointTimeGroup($nodeuuid){
        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_type, bbt.module_type,
                        bbt.task_name, bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
		               dbt.db_name, dbt.instance_name, dbt.agent_uuid, dbt.db_type, bsr.node_uuid
                from bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = dbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type in (4) and data_local_flag = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        if ($nodeuuid){
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?";
            array_push($sqlParams, $nodeuuid);
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            $sql .= " and bbt.user_uuid = ?";
            array_push($sqlParams, Xphp::$_user['useruuid']);
        }

        $sql .= " order by bbt.timepoint desc  ";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        //初始化数据库模块描述
        $dbDes = include APP_PATH . 'dbprotect/DbDescription.php';

        //定义task db timepoint
        $dbtype = array();
        $timepoint = array();
        $task = array();
        foreach ($data as $d){
            $taskuuid = $d['task_uuid'];
            $dbuuid = $d['instance_name'];
            $timepointuuid = $d['timepoint_uuid'];
            $taskCreateTime = $d['task_create_time'];
            //检查并添加数据库类型
            if(!in_array($d['db_type'], $dbtype)){
                $type = intval($d['db_type']);
                $name = $dbDes['DB_TYPE_DES'][$type];
                $node[] = array(
                    "id" => $d['db_type'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => true,
                    "type" => -1,
                    "icon" => './img/vm/host.png',
                );
                $dbtype[] = $type;
            }
            //检查并添加task
            if(!in_array($d['db_type'].$taskuuid, $task)){
                $task[] = $d['db_type'].$taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $d['task_name'] : $d['task_name'] . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                $node[] = array(
                    "id" => $d['db_type'].$taskuuid,
                    "pId" => $d['db_type'],
                    "name" => $name,
                    "open" => false,
                    "nocheck" => true,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
                    "agentuuid" => $d['agent_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "dbtype" => $d['db_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            }

        }
        return json_encode($node);
    }

    /**
     * 异步获取按时间点显示数据库
     * @param unknown $params
     */
    public function getSyncTimepointTimeGroup($params){
        $node = array();
        $taskuuid = $params['taskuuid'];
        $nodeuuid = $params['nodeuuid'];
        $type = $params['dbtype'];
        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode,
                       bbt.depend_point_uuid, unix_timestamp(bbt.task_create_time) task_create_time,
		               dbt.db_type,dbt.instance_name, dbt.db_name, dbt.dir_path, dbt.agent_uuid, bsr.node_uuid
                from bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = dbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type in (4) and
                       bbt.user_uuid = ? and bbt.task_uuid= ? and dbt.db_type = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'],Xphp::$_user['useruuid'], $taskuuid, $type);
        if($nodeuuid){
            $sql .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $sql .= " order by  dbt.instance_name,  dbt.db_timepoint_id desc";
        $data = $this->dbSelect($sql, $sqlParams);
        //添加时间点
        $timepoint = array();
        $node = array();
        foreach ($data as $d){
            if(!in_array($d['db_type'].$d['timepoint_uuid'].$taskuuid, $timepoint)){
                $timepoint[] =  $d['db_type'].$d['timepoint_uuid'].$taskuuid;
                $node[] = array(
                    "id" =>  $d['db_type'].$d['timepoint_uuid'].$taskuuid,
                    "pId" =>  $d['db_type'].$taskuuid,
                    "name" => $this->parseDate($d['timepoint']) . "(" . $this->getTimepointTypeDes($d['backup_mode'], $d['db_type']) . ")",
                    "checked" => false,
                    "nocheck" => false,
                    "type" => 1,
                    "dbuuid" => $d['db_name'],
                    "instanceuuid" => $d['instance_name'],
                    "dbname" => $d['db_name'],
                    "agentuuid" => $d['agent_uuid'],
                    "timepointuuid" => $d['timepoint_uuid'],
                    "dbtype" => $type,
                    "nodeuuid" => $d['node_uuid'],
                    "dir_path" => $d['dir_path'],
                    "icon" => $this->getTimepointIcon($d['backup_mode']),
                    "title" => Xphp::$_lang['WEB_DB_DATABASE_SRC_PATH'] . ': ' . $d['dir_path'],
                );
            }

            //添加数据库
            $node[] = array(
                "id" => $d['agent_uuid'] . $d['instance_name'],
                "pId" => $d['db_type'].$d['timepoint_uuid'].$taskuuid,
                "name" => $d['db_name'],
                "open" => false,
                "nocheck" => false,
                "type" => 2,
                "icon" => './img/platform/storage.png',
                "title" => Xphp::$_lang['WEB_DB_DATABASE_SRC_PATH'] . ': ' . $d['dir_path'],
                "dbname" => $d['db_name'],
                "dbuuid" => $d['db_name'],
                "instanceuuid" => $d['instance_name'],
                "agentuuid" => $d['agent_uuid'],
                "timepointuuid" => $d['timepoint_uuid'],
                "dbtype" => $type,
                "nodeuuid" => $d['node_uuid'],
                "pointname" => $this->parseDate($d['timepoint']),
                "dir_path" => $d['dir_path'],
            );
        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }

    /**
     * 异步获取每个任务对应的数据库时间点
     * @param array $params
     */
    public function getSyncTimepoint($params){
        $manageflag  = $params['manageflag'];       //备份数据管理标志,备份数据管理的树带有checkbox
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];  //客户端唯一标识
        $type = $params['dbtype'];
        $dbuuid = $params['dbuuid'];
        $dbcheck = $params['dbcheck'];
        $nodeuuid =$params['nodeuuid'];
        $instanceuuid = $params['instanceuuid'];    //实例唯一标识

        $allModuleType = Xphp::$_config['MODULE_TYPE'];
        $allDbType = Xphp::$_config['DB_TYPE'];
        $node = array();
        $sqlPoint = "SELECT bbt.detail, bbt.timepoint_uuid, UNIX_TIMESTAMP(bbt.timepoint) AS timepoint, bbt.backup_mode,
                        bbt.task_name, bbt.depend_point_uuid, UNIX_TIMESTAMP(bbt.task_create_time) AS task_create_time,
                        bbt.task_uuid, bbt.remarks, bbt.encrypted_flag, bbt.importance_flag, bbt.real_node_uuid,
                        bbt.src_start_timepoint, bbt.src_end_timepoint,
		                dbt.instance_name, dbt.db_name, dbt.dir_path, dbt.agent_uuid, dbt.db_type, dbt.db_uuid,
		                dbt.db_config, dbt.cluster_uuid, dbt.cluster_name,
		                bsr.node_uuid, bsr.storage_type
                    FROM bd_backup_timepoint bbt
                        INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                        INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                    WHERE bbt.deleted_flag = ? AND bbt.available_flag = ? AND bbt.import_flag = ?
                        AND bbt.module_type IN ({$allModuleType['DB']}, {$allModuleType['BACKUP_COPY_CLIENT']})
                        AND bbt.task_uuid = ? AND dbt.db_uuid = ? AND dbt.db_type = ? AND bbt.data_local_flag = ? AND dbt.agent_uuid =? ";

        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $taskuuid, $dbuuid, $type, $flag['SET'], $agentuuid);
        if(!empty($nodeuuid)){
            // 云存储，使用bbt.real_node_uuid；其他存储使用bsr.node_uuid
            $sqlPoint .= " and ((bsr.storage_type != ? AND bsr.node_uuid = ?) OR (bsr.storage_type = ? AND bbt.real_node_uuid = ?) )";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid));
        }
        $sqlPoint .= " order by dbt.instance_name,  bbt.timepoint";
        $pointData = $this->dbSelect($sqlPoint, $sqlParams);
        $pid =  null;
        $timepoint = array();
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');

        foreach ($pointData as $point){
            $taskuuid = $point['task_uuid'];
            $dbuuid = $point['db_name'];
            $dbType = $point['db_type'];
            $instancename = $point['instance_name'];
            $timepointuuid = $point['timepoint_uuid'];
            $detail = json_decode($point['detail'],true);
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
            $pId = $dbType.$dbuuid . $taskuuid;
            $dbConfig = json_decode($point['db_config'], true);
            $clusterName = $point['cluster_name']?? '';
            $clusterUuid = $point['cluster_uuid']?? '';
            $clusterFlag = !!$clusterUuid;

            if($manageflag){
                $mark = $vmHandler->pGetTimepointMark(false, false, false,$utils->parseFlagToBool($point['importance_flag']));
            }else{
                $mark = "";
            }
            //sqlserver新增一层实例
            if($dbType == $allDbType['SQLSERVER'] || $dbType == $allDbType['SAPHANA']){
                $pId = $dbType.$dbuuid . $instancename. $taskuuid;
            }
            $name = $this->parseDate($point['timepoint']) . " (" . $this->getTimepointTypeDes($point['backup_mode'], $type) . ")";
            if(intval($point['encrypted_flag']) == Xphp::$_config['FLAG']['SET']){
                $name .= '<i class="fa fa-lock"></i>';
            }

            if (in_array($timepointuuid, $timepoint)) {
                continue;
            }
            $timepoint[] =  $timepointuuid;
            //检查并添加完备点
            if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                $node[] = array(
                    "id" =>  $timepointuuid,
                    "mark" => $utils->parseFlagToBool($point['importance_flag']),
                    "pId" =>  $pId,
                    "name" => $name.$mark,
                    "oldname" => $name,
                    "checked" => $dbcheck,
                    "type" => 3,
                    "dbuuid" => $point['db_uuid'],
                    "instanceuuid" => $point['instance_name'],
                    "dbname" => $point['db_name'],
                    "pointname" => $this->parseDate($point['timepoint']),
                    "agentuuid" => $point['agent_uuid'],
                    "timepointuuid" => $timepointuuid,
                    "createtime" => $taskCreateTimeIn,
                    "nodeuuid" => $point['real_node_uuid'] ?: $point['node_uuid'],
                    "taskuuid" => $taskuuid,
                    "dir_path" => $point['dir_path'],
                    "icon" => $this->getTimepointIcon($point['backup_mode']),
                    "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                    "dbtype" => $type,
                    "pointtype" => intval($point['backup_mode']),
                    "encryptedflag" => $utils->parseFlagToBool(intval($point['encrypted_flag'])) && ($detail['password_auto_flag'] == 2),
                    "config" => $detail,
                    'log_time_point' => $this->parseDate($point['timepoint']),  // 日志回滚时间
                    'log_start_time_point' => $this->parseDate($point['src_start_timepoint']),  // 日志开始时间
                    'log_end_time_point' => $this->parseDate($point['src_end_timepoint']),  // 日志结束时间
                    'db_config' => $point['db_config'],
                    'eventtype' => $this->getTimepointEventType($point['backup_mode']),
                    'storage_type' => $point['storage_type'],
                    'cluster_flag' => $clusterFlag,
                    'cluster_uuid' => $clusterUuid,
                    'cluster_name' => $clusterName,
                );
                //添加了完全备份时间点继续下一次
                $pid = $timepointuuid;
                continue;
            }

            $subDisabledFlag = $params['disabledflag'];  // 备份数据禁用勾选增量差异标志
            if ($allDbType['MONGODB'] == $dbType) {  // 允许删除差异、增量、日志
                $subDisabledFlag = false;
            }
            $nocheck = false;
            $node[] = array(
                "id" => $timepointuuid,
                "pId" => $pid,
                "name" => $name,
                "oldname" => $name,
                "checked" => $dbcheck,
                "type" => 4,
                "dbuuid" => $point['db_uuid'],
                "instanceuuid" => $point['instance_name'],
                "dbname" => $point['db_name'],
                "pointname" => $this->parseDate($point['timepoint']),
                "agentuuid" => $point['agent_uuid'],
                "nodeuuid" => $point['real_node_uuid'] ?: $point['node_uuid'],
                "taskuuid" => $taskuuid,
                "timepointuuid" => $timepointuuid,
                "dbtype" => $point['db_type'],
                "dir_path" => $point['dir_path'],
                "icon" => $this->getTimepointIcon($point['backup_mode']),
                "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                "chkDisabled" => $subDisabledFlag,
                'nocheck' => $nocheck,
                "pointtype" => intval($point['backup_mode']),
                "encryptedflag" => $utils->parseFlagToBool(intval($point['encrypted_flag'])) && ($detail['password_auto_flag'] == 2),
                "config" => $detail,
                'log_time_point' => $this->parseDate($point['timepoint']),  // 日志回滚时间
                'log_start_time_point' => $this->parseDate($point['src_start_timepoint']),  // 日志开始时间
                'log_end_time_point' => $this->parseDate($point['src_end_timepoint']),  // 日志结束时间
                'db_config' => $point['db_config'],
                'eventtype' => $this->getTimepointEventType($point['backup_mode']),
                'storage_type' => $point['storage_type'],
                'cluster_flag' => $clusterFlag,
                'cluster_uuid' => $clusterUuid,
                'cluster_name' => $clusterName,
            );
        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }

    /**
     * 得到当前所有任务的UUID
     * return array
     */
    public function getCurrentAllTaskUUID(){
        $taskuuid = array();
        $sql = "select task_uuid from bd_task where module_type in(4) and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d){
            $taskuuid[] = $d['task_uuid'];
        }
        return $taskuuid;
    }

    /**
     * 得到数据库备份时间点的备注信息,
     * 如果有备注添加到数据库源路径后面就是
     * @param string $dirPath
     * @param string $remarks
     */
    public function getBackupTimepointTreeTitle($dirPath, $remarks){
        $title = Xphp::$_lang['WEB_DB_DATABASE_SRC_PATH'] . ': ' . $dirPath;
        if(!empty($remarks)){
            $title .=  "  " . Xphp::$_lang['UI_PUBLIC_REMARK'] . ": " . $remarks;
        }
        return $title;
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

    public function getTimepointEventType($backupMode){
        $backupMode = intval($backupMode);
        switch($backupMode){
            case Xphp::$_config['BACKUP_MODE']['FULL']:
                return 'full';
            case Xphp::$_config['BACKUP_MODE']['INCREMENTAL']:
                return 'incr';
            case Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']:
                return 'diff';
            case Xphp::$_config['BACKUP_MODE']['LOG']:
                return 'log';
        }
        return 'pIncr';
    }

    /**
     * 得到数据库恢复的宿主机
     * @param array $params ['type'] 数据库类型
     */
    public function getRecoverAgentTree(array $params)
    {
        $dbType = intval($params['type']);
        $reqClusterFlag = $params['cluster_flag'];
        $reqClusterUuid = $params['cluster_uuid'];
        $tree = array();
        $sql = "SELECT ba.id, ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.authorization_module,
                    ba.net_model, ba.online_flag, baa.cluster_flag, baa.cluster_uuid, baa.cluster_name,
                    baa.cluster_service_ip, baa.app_detail, baa.app_name, baa.app_auth_type
                FROM bd_agent ba
                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = ba.agent_uuid
                WHERE baa.app_type = ? ";
        $clientHandler = Xphp::instance('ClientHandler');
        $agentUuidArr = $clientHandler->getClientUuids();
        $clusterAgentUuidList = [];
        if (
            isset($params['recovery_source_type']) &&
            $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
        ) {
            foreach ($params['source_agent_uuid_list'] as $sourceAgentUuid) {
                $clusterAgentUuidList = array_merge($clusterAgentUuidList, $clientHandler->getAllAgentUuidFromAgentUuid($sourceAgentUuid));
            }
        }
        if (!$agentUuidArr) {
            $sql .= " AND ba.agent_uuid = '' ";
        } else {
            if (is_array($agentUuidArr)) {
                $agentUuids = "'" . implode("', '", $agentUuidArr) . "'";
                $sql .= " AND ba.agent_uuid IN ($agentUuids) ";
            }
        }
        $data = $this->dbSelect($sql, [$dbType]);
        $allDbType = Xphp::$_config['DB_TYPE'];
        $systemHandler = Xphp::instance('SystemHandler');
        $utils = Xphp::instance('Utils');

        $agentMap = [];
        foreach ($data as $d) {
            $clusterFlag = $utils->parseFlagToBool($d['cluster_flag']) && $d['cluster_uuid'];
            $appAuthType = $d['app_auth_type'];
            $agentMapKey = $d['agent_uuid'];
            if (
                $clusterFlag && (
                    $dbType === $allDbType['TIDB'] ||
                    $dbType === $allDbType['MONGODB']
                )
            ) {
                $agentMapKey .= '_' . $d['cluster_uuid'];
            } else {
                if ($dbType === $allDbType['TIDB']) {
                    $agentMapKey .= '_' . $d['app_uuid'];
                }
            }
            if (!isset($agentMap[$agentMapKey])) {
                $agentMap[$agentMapKey] = 1;
            } else {
                continue;
            }
            //是否授权
            $chkDisabledflag =  false;

            //是否在线
            $onlineFlag = $utils->parseFlagToBool($d['online_flag']);
            $authorization_online_info = '';
            if (!$onlineFlag) {
                $authorization_online_info .= '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')';  //离线
            }
            //客户端名  别名不为空并且不等于IP 显示别名+ip
            if (!empty($d['agent_name']) && $d['agent_name'] != $d['ip']) {
                $name = $authorization_online_info . $d['agent_name'] . "(" . $d['ip'] . ")";
            } else {
                $name = $authorization_online_info . $d['hostname'] . "(" . $d['ip'] . ")";
            }
            $noCheckFlag = true;  // 点击客户端，加载实例
            if (
                $dbType != $allDbType['SQLSERVER'] &&
                $dbType != $allDbType['SAPHANA'] &&
                $dbType != $allDbType['MYSQL'] &&
                $dbType != $allDbType['MARIA'] &&
                $dbType != $allDbType['DM'] &&
                $dbType != $allDbType['KINGBASE'] &&
                $dbType != $allDbType['HIGHGO'] &&
                $dbType != $allDbType['OPENGAUSS'] &&
                $dbType != $allDbType['VASTBASE']
            ) {
                $noCheckFlag = false;
            }
            $pid = 0;
            $appDetail = json_decode($d['app_detail'], true);
            if ($clusterFlag && (
                    $allDbType['MONGODB'] == $dbType ||
                    $allDbType['TIDB'] == $dbType)
            ) {
                $clusterName = $d['cluster_name'];
                $id = $d['cluster_uuid'];
                if ($allDbType['TIDB'] == $dbType) {
                    $clusterName = $d['app_name'];
                }
                if (!$onlineFlag) {
                    $clusterName = $authorization_online_info . $clusterName;
                }
                $clusterKey = 'cluster_uuid' . $d['cluster_uuid'];
                if (!isset($tree[$clusterKey])) {
                    $tree[$clusterKey] = [
                        'id' => $id,
                        'pId' => $pid,
                        'name' => $clusterName,
                        'title' => $clusterName,
                        'isParent' => true,
                        'open' => true,
                        'uuid' => $d['cluster_uuid'],
                        'nocheck' => false,
                        'type' => 0,
                        'icon' => './img/vm/hostcluster.png',
                        'agentuuid' => $d['agent_uuid'],
                        'clickshow' => false,
                        'os_type' => $d['os_type'],
                        'net_model' => (int) $d['net_model'],
                        'chkDisabled' => !$onlineFlag,
                        'online_flag' => $onlineFlag,
                        'eventtype' => 'cluster',
                        'cluster_flag' => $clusterFlag,
                        'cluster_uuid' => $d['cluster_uuid'],
                        'instanceuuid' => $d['app_name'],
                    ];
                } else {
                    $tree[$clusterKey]['agentuuid'] .= ',' . $d['agent_uuid'];
                    $tree[$clusterKey]['online_flag'] &= $onlineFlag;
                    /**
                     * 处理集群能否勾选的逻辑
                     * MongoDB: 副本集群有一台客户端在线即可; 分片集群需要所有客户端在线; 需要所有客户端都已认证
                     * TiDB、Cache、IRIS: 集群需要所有客户端在线; 需要所有客户端都已认证
                     */
                    if (!$tree[$clusterKey]['online_flag']) {
                        $tree[$clusterKey]['chkDisabled'] = true;
                        $tree[$clusterKey]['name'] = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')' . $clusterName;
                        $tree[$clusterKey]['title'] = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')' . $clusterName;
                    } else {  // 这里需要加else, 因为MongoDB的副本集群只需要一台服务器在线就可以了
                        $tree[$clusterKey]['chkDisabled'] = false;
                        $tree[$clusterKey]['name'] = $clusterName;
                        $tree[$clusterKey]['title'] = $clusterName;
                    }
                    if ($d['net_model'] == 2) {  // 集群的通信模式，如果客户端有2，那就是2
                        $tree[$clusterKey]['net_model'] = 2;
                    }
                }
                $pid = $id;
                if ($allDbType['TIDB'] == $dbType) {
                    $name = $authorization_online_info . $appDetail['node_role'] . '(' . $d['ip'] . ')';
                    // 恢复最新点不支持原机恢复
                    if (
                        isset($params['recovery_source_type']) &&
                        $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
                    ) {
                        if ($d['cluster_uuid'] == $reqClusterUuid && $reqClusterFlag) {
                            $tree[$clusterKey]['chkDisabled'] = true;
                        }
                    }
                }
                $noCheckFlag = true;
            }
            if (!$clusterFlag && $allDbType['TIDB'] == $dbType) {
                $id = 'tidb_agent' . $d['agent_uuid'] . $d['app_uuid'];
                $tidbName = $authorization_online_info . $d['app_name'];

                $tidbChkDisabled = !$onlineFlag;
                // 恢复最新点不支持原机恢复
                if (
                    isset($params['recovery_source_type']) &&
                    $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
                ) {
                    if (in_array($d['agent_uuid'], $params['source_agent_uuid_list']) && !$reqClusterFlag) {
                        $tidbChkDisabled = true;
                    }
                }
                $tree[$id] = [
                    'id' => $id,
                    'pId' => $pid,
                    'name' => $tidbName,
                    'title' => $tidbName,
                    'isParent' => true,
                    'open' => true,
                    'uuid' => $d['agent_uuid'],
                    'nocheck' => false,
                    'type' => 0,
                    'icon' => './img/vm/hostcluster.png',
                    'agentuuid' => $d['agent_uuid'],
                    'clickshow' => false,
                    'os_type' => $d['os_type'],
                    'net_model' => (int) $d['net_model'],
                    'chkDisabled' => $tidbChkDisabled,
                    'online_flag' => $onlineFlag,
                    'eventtype' => 'cluster',
                    'cluster_flag' => false,
                    'cluster_uuid' => '',
                    'instanceuuid' => $d['app_name'],
                ];
                $pid = $id;
                $noCheckFlag = true;
            }
            $agentKey = 'agent_uuid' . $d['agent_uuid'];
            if ($clusterFlag) {
                $agentKey = 'cluster_uuid' . $d['cluster_uuid'] . $agentKey;
            } else {
                if ($allDbType['TIDB'] == $dbType) {
                    $agentKey .= $d['app_uuid'];
                }
            }
            $isParent = false;
            if (
                $dbType == $allDbType['SQLSERVER'] ||
                $dbType == $allDbType['SAPHANA'] ||
                $dbType == $allDbType['MYSQL'] ||
                $dbType == $allDbType['MARIA'] ||
                $dbType == $allDbType['DM'] ||
                $dbType == $allDbType['KINGBASE'] ||
                $dbType == $allDbType['HIGHGO'] ||
                $dbType == $allDbType['OPENGAUSS'] ||
                $dbType == $allDbType['VASTBASE']
            ) {
                $isParent = true;
            }

            $chkDisabled = !$onlineFlag;
            $clickShow = $onlineFlag && $isParent;
            // 恢复最新点不支持原机恢复
            $oracleOsAuthFlag = false;
            $title = $d['ip'];
            if (
                isset($params['recovery_source_type']) &&
                $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
            ) {
                if (in_array($d['agent_uuid'], $clusterAgentUuidList)) {
                    $chkDisabled = true;
                    $clickShow = false;
                }
                if (1 == $appAuthType && $dbType == $allDbType['ORACLE']) {  // 操作系统认证（空实例）
                    $chkDisabled = true;
                    $clickShow = false;
                    $oracleOsAuthFlag = true;
                    $title = Xphp::$_lang['WEB_DB_RECOVERY_ORACLE_OS_AUTH'];
                }
            }
            $tree[$agentKey] = array(
                "id" => $d['agent_uuid'],
                "pId" => $pid,
                "name"=> $name,
                "title" => $title,
                "isParent" => $isParent,
                "open" => false,
                "uuid" => $d['agent_uuid'],
                "nocheck" => $noCheckFlag,
                "type" => 1,
                "icon" => "./img/vm/cluster.png",
                "agentuuid" => $d['agent_uuid'],
                "clickshow" => $clickShow,
                "os_type" => $d['os_type'],
                "net_model" => intval($d['net_model']),
                "chkDisabled" => $chkDisabled,
                //是否离线
                'online_flag' => $onlineFlag, //false 离线
                'eventtype' => 'agent',
                'cluster_flag' => $utils->parseFlagToBool($d['cluster_flag']) && $d['cluster_uuid'],
                'cluster_uuid' => $d['cluster_uuid'],
                'instanceuuid' => $d['app_name'],
                'oracle_os_auth_flag' => $oracleOsAuthFlag,
            );
        }

        return json_encode(array_values($tree));
    }

    /**
     * 异步获取恢复目的实例
     * @param unknown $params
     * @return string
     */
    public function getSyncRecoveryInstance($params){
        $agentuuid = $params['agentuuid'];
        $type = $params['dbtype'];
        $allDbType = Xphp::$_config['DB_TYPE'];

        if (
            $type != $allDbType['MYSQL'] &&
            $type != $allDbType['MARIA'] &&
            $type != $allDbType['DM'] &&  // DM恢复不需要扫描实例（因为需要把实例关闭）
            $type != $allDbType['KINGBASE'] &&  // pg系恢复不需要扫描实例（因为需要把实例关闭）
            $type != $allDbType['HIGHGO'] &&
            $type != $allDbType['OPENGAUSS'] &&
            $type != $allDbType['VASTBASE']
        ) {
            $instanceMsg = $this->getInstanceMsg($type, $agentuuid);
            $instanceList = $instanceMsg ? $instanceMsg['instance_list'] : [];
        } else {
            $instanceList = [];
        }
        $sql = "SELECT baa.app_name, baa.agent_uuid, baa.cluster_uuid, baa.cluster_flag, baa.app_detail,
                    ba.agent_name, ba.net_model
                FROM bd_agent_app baa
                    INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                WHERE ba.agent_uuid = ? AND baa.app_type = ? ";
        $data = $this->dbSelect($sql, array($agentuuid, $type));
        $clusterAgentUuidList = [];
        if (
            isset($params['recovery_source_type']) &&
            $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
        ) {
            $clientHandler = Xphp::instance('ClientHandler');
            foreach ($params['source_agent_uuid_list'] as $sourceAgentUuid) {
                $clusterAgentUuidList = array_merge($clusterAgentUuidList, $clientHandler->getAllAgentUuidFromAgentUuid($sourceAgentUuid));
            }
        }

        // 获取恢复任务数据
        $recoveryTaskData = [];
        if ($type == $allDbType['SAPHANA']) {
            $dbNames = "'" . implode("', '", $params['source_db_list']) . "'";
            $sql = "SELECT dl.task_uuid, dl.db_name, dl.instance_name, bt.task_name, dl.agent_uuid
                    FROM db_list dl
                        INNER JOIN db_task dt ON dl.task_uuid = dt.task_uuid
                        INNER JOIN bd_task bt ON bt.task_uuid = dl.task_uuid
                    WHERE dt.db_type = ? AND dl.recovery_mode = 1 AND dl.db_name IN ($dbNames) ";
            $recoveryTaskData = $this->dbSelect($sql, [$type]);
        }

        $node = array();
        if(!empty($instanceList)){
            foreach ($instanceList as $instance){
                $instanceName = trim($instance['instance']);
                $info = array(
                    "id" => $instanceName,
                    "pid" => $agentuuid,
                    "name" => $instanceName . '('.Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED'] . ')',
                    "title" => $instanceName . '('.Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED'] . ')',
                    "isParent" => false,
                    "nocheck" => false,
                    "type" => 2,
                    "icon" => "./img/vm/host.png",
                    "agentuuid" => $agentuuid,
                    "instanceuuid" => $instanceName,
                    "dbtype" => $type,
                    "chkDisabled" => true,
                    "verifyflag" => false,
                    'eventtype' => 'instance',
                    'cluster_uuid' => '',
                    'cluster_flag' => false,
                    'recovery_task_flag' => false,
                    'app_detail' => [],
                );
                if ($data) {
                    $taskInfo = null;
                    foreach ($recoveryTaskData as $task) {
                        if ($task['agent_uuid'] == $agentuuid && $instanceName == $task['instance_name']) {  // 表明源勾选的数据库存在恢复任务
                            $taskInfo = $task;
                            break;
                        }
                    }
                    foreach ($data as $d) {
                        if ($instanceName != $d['app_name']) {
                            continue;
                        }
                        $info['name'] = $d['app_name'];
                        $info['title'] = $d['app_name'];
                        $info['chkDisabled'] = false;
                        $info['net_model'] = intval($d['net_model']);
                        $info['cluster_uuid'] = $d['cluster_flag'] == Xphp::$_config['FLAG']['SET'] && $d['cluster_uuid'] ? $d['cluster_uuid'] : '';
                        $info['cluster_flag'] = $d['cluster_flag'] == Xphp::$_config['FLAG']['SET'] && $d['cluster_uuid'];
                        if ($type == $allDbType['SAPHANA'] && $taskInfo != null) {
                            $info['title'] = sprintf(Xphp::$_lang['WEB_DB_RECOVERY_SAP_HANA_TASK_EXISTS'], $taskInfo['task_name']);
                            $info['chkDisabled'] = true;
                            $info['recovery_task_flag'] = true;
                        }
                        try {
                            $info['app_detail'] = json_decode($d['app_detail'], true);
                        } catch (Exception $e) {
                            $info['app_detail'] = [];
                        }
                    }
                }
                if (
                    isset($params['recovery_source_type']) &&
                    $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
                ) {
                    // 恢复最新点不支持原机恢复
                    if (in_array($agentuuid, $clusterAgentUuidList)) {
                        $info['chkDisabled'] = true;
                    }
                    // 恢复最新点仅支持异机同实例恢复
                    if ($instanceName != $params['source_instance_name']) {
                        // SQL Server、SAP HANA可以跨实例恢复
                        if ($allDbType['SQLSERVER'] != $type && $allDbType['SAPHANA'] != $type) {
                            $info['chkDisabled'] = true;
                        }
                    }
                }

                $node[] = $info;
            }
        } else if (
            $type == $allDbType['MYSQL'] ||
            $type == $allDbType['MARIA'] ||
            $type == $allDbType['DM'] ||
            $type == $allDbType['KINGBASE'] ||
            $type == $allDbType['HIGHGO'] ||
            $type == $allDbType['OPENGAUSS'] ||
            $type == $allDbType['VASTBASE']
        ) {
            foreach ($data as $row) {
                try {
                    $appDetail = json_decode($row['app_detail'], true);
                } catch (Exception $e) {
                    $appDetail = [];
                }
                $info = array(
                    "id" => $row['app_name'],
                    "pid" => $agentuuid,
                    "name" => $row['app_name'],
                    "title" => $row['app_name'],
                    "isParent" => false,
                    "nocheck" => false,
                    "type" => 2,
                    "icon" => "./img/vm/host.png",
                    "agentuuid" => $agentuuid,
                    "instanceuuid" => $row['app_name'],
                    "dbtype" => $type,
                    "chkDisabled" => false,
                    "verifyflag" => false,
                    'eventtype' => 'instance',
                    'net_model' => intval($row['net_model']),
                    'cluster_uuid' => $row['cluster_uuid'],
                    'cluster_flag' => $row['cluster_flag'] == Xphp::$_config['FLAG']['SET'] && $row['cluster_uuid'],
                    'app_detail' => $appDetail,
                );
                // 恢复最新点不支持原机恢复
                if (
                    isset($params['recovery_source_type']) &&
                    $params['recovery_source_type'] == Xphp::$_config['BD_RECOVERY_TYPE']['LATEST_TIMEPOINT']
                ) {
                    if (in_array($agentuuid, $clusterAgentUuidList)) {
                        $info['chkDisabled'] = true;
                    }
                }

                $node[] = $info;
            }
        }

        $msg = array(
            're' => true,
            'msg' => $node
        );

        return json_encode($msg);
    }

    /**
     * 获取数据库源信息
     * @param unknown $params
     * @return string
     */
    public function getDBOldConifg($params){
        $timepointuuid = $params['timepointuuid'];
        $dbtype = $params['type'];

        $info = array(
            'filepath' => '',
            'logpath' => '',
            'starttimepoint' => '',
            'endtimepoint' => '',
            'timepoint' => date('Y-m-d H:i:s'),
        );
        $sql = "SELECT bbt.depend_point_uuid, bbt.backup_mode, UNIX_TIMESTAMP(bbt.timepoint) AS timepoint,
                    bbt.src_start_timepoint, bbt.src_end_timepoint, dbt.db_config
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($timepointuuid));
        if(!empty($data)){
            $dbconfig = json_decode($data[0]['db_config'], true);
            $startTimepoint = date('Y-m-d H:i:s', intval($data[0]['src_start_timepoint']));
            $endTimepoint = date('Y-m-d H:i:s', intval($data[0]['src_end_timepoint']));
            if($data[0]['backup_mode'] == 4 && $dbtype == Xphp::$_config['DB_TYPE']['ORACLE']){//oracle归档日志
                $startTimepoint = $this->getArchiveLogStartTime($data[0]['depend_point_uuid'], $startTimepoint);
            }
            $info = array(
                'filepath' => $dbconfig['db_file_path'],
                'logpath' => $dbconfig['db_log_path'],
                'starttimepoint' => $startTimepoint,
                'endtimepoint' => $endTimepoint,
                'timepoint' => date('Y-m-d H:i:s', intval($data[0]['timepoint']))
            );

        }

        //返回结果到UI
        return json_encode($info);
    }

    /**
     * 组合恢复虚拟机和时间点,新名字信息
     * @param int $recoveryPos  原机/异机
     * @param array $points     时间点信息
     * @param array $recoverInfo      名字信息
     * @param int $dbType     数据库类型
     * @param mixed $detail 特殊配置
     * @param bool  $modifyPfileFlag 是否配置pfile参数文件
     * @return array
     */
    private function groupRebuildDBInfo($recoveryPos, $points, $recoverInfo, $dbType, $detail, $modifyPfileFlag){
        $setFlag = Xphp::$_config['FLAG'];
        $utils = Xphp::instance('Utils');
        $allDbType = Xphp::$_config['DB_TYPE'];
        $pointInfo = array();
        if (
            $dbType == $allDbType['SQLSERVER'] ||
            $dbType == $allDbType['SAPHANA']
        ) {  // 多时间点恢复/多表空间恢复
            foreach ($points as $dbInfo) {
                $pointInfo[] = [
                    'instance_name' => $recoverInfo['instancename'],  // 恢复目标机的实例名
                    'src_db_name' => $dbInfo['oldDbname'],
                    'create_db_flag' => $dbInfo['is_create_db'],
                    'new_db_name' => $dbInfo['is_create_db'] ? $dbInfo['new_db_name'] : '',
                    'new_data_file_path' => $dbInfo['is_create_db'] ? $dbInfo['datafile_path'] : '',
                    'new_log_file_path' => $dbInfo['is_create_db'] ? $dbInfo['logfile_path'] : '',
                    'log_rollback_time' => $dbInfo['is_rollback'] ? $dbInfo['rollback_time'] : '',
                    'recovery_timepoint_uuid' => $dbInfo['timepointuuid'],
                    'new_db_password' => '',
                    'detail' => '',
                    'recovery_mode' => $recoveryPos,
                    'modify_pfile_flag' => $setFlag['UNSET'],
                    'timepoint_password' => base64_decode($dbInfo['encrypt_password']),
                    'log_restore_start_time' => $recoverInfo['log_restore_start_time'],
                    'log_restore_end_time' => $recoverInfo['log_restore_end_time'],
                    'recovery_scene' => $recoverInfo['recovery_scene'],
                    'open_db_flag' => $utils->parseBoolToFlag($recoverInfo['open_db_flag']),
                    'source_agent_uuid' => $dbInfo['source_agent_uuid'],
                    'source_instance_name' => $dbInfo['source_instance_name'],
                    'source_db_name' => $dbInfo['source_db_name'],
                    'recovery_time' => $dbInfo['recovery_time'] ?? '',
                    'initialize_log_area' => $utils->parseBoolToFlag($dbInfo['initialize_log_area'] ?? false),
                    'latest_timepoint_uuid' => $dbInfo['latest_timepoint_uuid'] ?? '',
                    'before_task_script' => $dbInfo['before_task_script'] ? json_encode($dbInfo['before_task_script']) : '',
                    'after_task_script' => $dbInfo['after_task_script'] ? json_encode($dbInfo['after_task_script']) : '',
                    'verification_script' => $dbInfo['verification_script'] ? json_encode($dbInfo['verification_script']) : '',
                    'db_uuid' => '',
                    'error_code' => 0,
                ];
            }
        } else {
            $datafilepath = $recoverInfo['newfilepath'];
            $logfilepath = $recoverInfo['newlogpath'];
            if(Xphp::$_config['DB_RECOVERY_TYPE']['COVER'] == $recoveryPos){
                if($dbType != $allDbType['MYSQL'] && $dbType != $allDbType['MARIA']){
                    $datafilepath = "";
                    $logfilepath = "";
                }
            }
            $pointInfo[0] = array(
                'instance_name' => $recoverInfo['instancename'],
                'src_db_name' => $recoverInfo['olddbname'] ?: '',
                'create_db_flag' => $recoverInfo['createflag'],
                'new_db_name' => $recoverInfo['newdbname'],
                'new_data_file_path' => $datafilepath,
                'new_log_file_path' => $logfilepath,
                'log_rollback_time' => $recoverInfo['logrolltime'],
                'recovery_timepoint_uuid' => $points[0]['timepointuuid'],
                'new_db_password' => $recoverInfo['password'],
                'detail' => $detail,
                'recovery_mode' => $recoveryPos,
                'modify_pfile_flag' => $utils->parseBoolToFlag($modifyPfileFlag),
                'timepoint_password' => base64_decode($recoverInfo['encrypt_password']),
                'log_restore_start_time' => $recoverInfo['log_restore_start_time'],
                'log_restore_end_time' => $recoverInfo['log_restore_end_time'],
                'recovery_scene' => $recoverInfo['recovery_scene'],
                'open_db_flag' => $utils->parseBoolToFlag($recoverInfo['open_db_flag']),
                'source_agent_uuid' => $points[0]['source_agent_uuid'],
                'source_instance_name' => $points[0]['source_instance_name'],
                'source_db_name' => $points[0]['source_db_name'],
                'recovery_time' => $points[0]['recovery_time'],
                'initialize_log_area' => $utils->parseBoolToFlag($points[0]['initialize_log_area'] ?? false),
                'latest_timepoint_uuid' => $points[0]['latest_timepoint_uuid'] ?? '',
                'before_task_script' => $points[0]['before_task_script'] ? json_encode($points[0]['before_task_script']) : '',
                'after_task_script' => $points[0]['after_task_script'] ? json_encode($points[0]['after_task_script']) : '',
                'verification_script' => $points[0]['verification_script'] ? json_encode($points[0]['verification_script']) : '',
                'db_uuid' => '',
                'error_code' => 0,
            );
        }

        return $pointInfo;
    }

    /**
     * 获取代理IP和端口号
     * @param string $agentuuid
     */
    private function getAgentInfo($agentuuid){
        $sql = "select ip, port, agent_name, hostname from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $info = array(
            'ip' => $data[0]['ip'],
            'port' => $data[0]['port'],
            'agentname' => $data[0]['agent_name'],
            'hostname' => $data[0]['hostname']
        );

        return $info;
    }

    /**
     * 获取实例信息
     * @param string $instancename
     */
    private function getInstanceInfo($instancename, $agentuuid){
        $sql = "select app_auth_type, app_username, app_password, app_name, agent_uuid, app_type
                from bd_agent_app where app_name = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($instancename, $agentuuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                'auth_type' => intval($data[0]['app_auth_type']),
                'username' => $data[0]['app_username'],
                'password' => $data[0]['app_password'],
                'instance_name' => $data[0]['app_name'],
                'agent_uuid' => $data[0]['agent_uuid'],
                'db_type' => $data[0]['app_type'],
            );
        }

        return $info;
    }

    /**
     * 得到数据库备份代理端树
     * @param array $params
     * @return string
     */
    public function getDBBackupAgentTree(array $params): string
    {
        /**
         * MongoDB、Cache、IRIS:
         * 无集群: 客户端分组/客户端
         * 有集群: 客户端分组/集群别名/客户端
         *
         * TiDB:
         * 无集群: 客户端分组/集群名(端口)/客户端
         * 有集群: 客户端分组/集群名(端口)/客户端
         */
        $utils = Xphp::instance('Utils');
        $dbType = $params['dbType'];
        $allDbType = Xphp::$_config['DB_TYPE'];
        $sql = "SELECT bag.group_name, ba.group_uuid, ba.id, ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip,
                    ba.os_type, ba.authorization_module, ba.online_flag, ba.net_model,
                    baa.cluster_uuid, baa.cluster_flag, baa.cluster_name, baa.cluster_service_ip, baa.cluster_type,
                    baa.app_detail, baa.app_name, baa.app_uuid
                FROM bd_agent ba
                    LEFT JOIN bd_agent_app baa ON ba.agent_uuid = baa.agent_uuid
                    INNER JOIN bd_agent_group bag ON ba.group_uuid = bag.group_uuid
                WHERE baa.app_type = ? and ba.agent_type != 4 ";
        $clientHandler = Xphp::instance('ClientHandler');
        $agentUuidArr = $clientHandler->getClientUuids();
        if (!$agentUuidArr) {
            $sql .= " AND ba.agent_uuid = '' ";
        } else {
            if (is_array($agentUuidArr)) {
                $agentUuids = "'" . implode("', '", $agentUuidArr) . "'";
                $sql .= " AND ba.agent_uuid IN ($agentUuids) ";
            }
        }
        $sqlParams = array($dbType);
        $data = $this->dbSelect($sql,$sqlParams);
        $systemHandler = Xphp::instance('SystemHandler');
        $agentHandler = Xphp::instance('AgentHandler');
        $node = array();
        if(!empty($data)) {
            foreach ($data as $d) {
                $clusterFlag = $utils->parseFlagToBool($d['cluster_flag']) && $d['cluster_uuid'];
                $appDetail = json_decode($d['app_detail'], true);
                //添加代理分组
                if (!isset($node['group_uuid' . $d['group_uuid']])) {
                    $node['group_uuid' . $d['group_uuid']] = array(
                        "id" => $d['group_uuid'],
                        "pId" => 0,
                        "name" => $d['group_name'],
                        "title" => $d['group_name'],
                        "open" => true,
                        "nocheck" => false,
                        "type" => 0,
                        "icon" => "./img/platform/flag.png",
                        "groupuuid" => $d['group_uuid'],
                        "eventtype" => "group",
                    );
                }
                $onlineFlag = $utils->parseFlagToBool($d['online_flag']);
                if (!$onlineFlag) {
                    $authorization_online_info = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')';  //离线
                } else {
                    $authorization_online_info = '';
                }
                //客户端名  别名不为空并且不等于IP 显示别名+ip
                if (!empty($d['agent_name']) && $d['agent_name'] != $d['ip']) {
                    $agentName = $authorization_online_info . $d['agent_name'] . "(" . $d['ip'] . ")";
                } else {
                    $agentName = $authorization_online_info . $d['hostname'] . "(" . $d['ip'] . ")";
                }
                $pid = $d['group_uuid'];
                $agentNoCheck = false;
                if ($clusterFlag && (
                        $dbType == $allDbType['MONGODB'] ||
                        $dbType == $allDbType['TIDB'])
                ) {
                    $id = $d['cluster_uuid'];
                    $clusterName = $d['cluster_name'];
                    if ($dbType == $allDbType['TIDB']) {
                        $clusterName = $d['app_name'];
                    }
                    $clusterKey = 'cluster_uuid' . $clusterName . $d['cluster_uuid'];
                    if (!$onlineFlag) {
                        $clusterName = $authorization_online_info . $clusterName;
                    }
                    if (!isset($node[$clusterKey])) {  // 集群层
                        $node[$clusterKey] = [
                            'id' => $id,
                            'pId' => $pid,
                            'name' => $clusterName,
                            'title' => $clusterName,
                            'open' => true,
                            'nocheck' => false,
                            'chkDisabled' => !$onlineFlag,
                            'type' => 2,  // 集群类别设置为2，不影响其他逻辑
                            'icon' => './img/vm/hostcluster.png',
                            "agentuuid" => $d['agent_uuid'],
                            "groupuuid" => $d['group_uuid'],
                            'cluster_uuid' => $d['cluster_uuid'],
                            'eventtype' => 'cluster',
                            'net_model' => (int) $d['net_model'],
                            'cluster_flag' => $clusterFlag,
                            'online_flag' => $onlineFlag,
                            'instance_name' => $d['app_name'],
                        ];
                    } else {
                        if (
                            (2 == $d['cluster_type'] && $allDbType['MONGODB'] == $dbType) ||
                            $allDbType['TIDB'] == $dbType
                        ) {  // 判断所有客户端都在线
                            $node[$clusterKey]['online_flag'] &= $onlineFlag;
                        } else {
                            $node[$clusterKey]['online_flag'] |= $onlineFlag;
                        }
                        /**
                         * 处理集群能否勾选的逻辑
                         * MongoDB: 副本集群有一台客户端在线即可; 分片集群需要所有客户端在线; 需要所有客户端都已认证
                         * TiDB、Cache、IRIS: 集群需要所有客户端在线; 需要所有客户端都已认证
                         */
                        if (!$node[$clusterKey]['online_flag']) {
                            $node[$clusterKey]['chkDisabled'] = true;
                            $node[$clusterKey]['name'] = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')' . $clusterName;
                            $node[$clusterKey]['title'] = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')' . $clusterName;
                        } else {  // 这里需要加else, 因为MongoDB的副本集群只需要一台服务器在线就可以了
                            $node[$clusterKey]['chkDisabled'] = false;
                            $node[$clusterKey]['name'] = $clusterName;
                            $node[$clusterKey]['title'] = $clusterName;
                        }
                        if ($d['net_model'] == 2) {  // 集群的通信模式，如果客户端有2，那就是2
                            $node[$clusterKey]['net_model'] = 2;
                        }
                    }
                    $agentNoCheck = true;
                    $pid = $id;
                    if ($dbType == $allDbType['TIDB']) {
                        // TIDB集群下的客户端显示角色名
                        $agentName = $authorization_online_info . $appDetail['node_role'] . '(' . $d['ip'] . ')';
                    }
                }
                $agentKey = 'agent_uuid' . $d['agent_uuid'];
                if ($clusterFlag && ($allDbType['MONGODB'] == $dbType || $allDbType['TIDB'] == $dbType)) {
                    $agentKey = 'cluster_uuid' . $d['cluster_uuid'] . $agentKey;
                }
                if (!$clusterFlag && $allDbType['TIDB'] == $dbType) {  // TiDB单机有特殊需求，分组 => 实例 => 客户端，多实例情况下存在问题
                    $id = 'tidb_agent' . $d['app_name'] . $d['agent_uuid'];  // 加上实例名，避免重复
                    if (!isset($node[$id])) {
                        // TiDB单机显示集群, 其他数据和客户端的一致
                        // TiDB单机虽然以集群方式展示，但不能放在集群那里，因为TiDB单机的cluster_uuid为空，不能作为集群的唯一区分，多个TiDB单机会有问题
                        $tidbName = $authorization_online_info . $d['app_name'];
                        $node[$id] = [
                            'id' => $id,
                            'pId' => $pid,
                            'name' => $tidbName,
                            'title' => $tidbName,
                            'isParent' => true,
                            'open' => true,
                            "agentuuid" => $d['agent_uuid'],
                            "groupuuid" => $d['group_uuid'],
                            "nocheck" => $agentNoCheck,
                            "type" => 2,  // 集群类别设置为2，不影响其他逻辑
                            "icon" => "./img/vm/hostcluster.png",
                            "module" => $agentHandler->getAgentModule($d['authorization_module']),
                            "chkDisabled" => !$onlineFlag,
                            "eventtype" => "cluster",
                            "net_model" => intval($d['net_model']),
                            "cluster_uuid" => '',
                            "cluster_flag" => false,
                            'online_flag' => $onlineFlag,
                            'instance_name' => $d['app_name'],
                            'app_uuid' => $d['app_uuid'],
                        ];
                        $pid = $id;
                        $agentNoCheck = true;  // 不让客户端勾选
                        $agentKey = 'agent_uuid' . $d['app_name'] . $d['agent_uuid'];
                    }
                }
                //添加客户端
                if (!isset($node[$agentKey])) {
                    $node[$agentKey] = array(
                        "id" => $d['agent_uuid'],
                        "pId" => $pid,
                        "name" => $agentName,
                        "title" => $d['ip'],
                        "isParent" => false,
                        "agentuuid" => $d['agent_uuid'],
                        "groupuuid" => $d['group_uuid'],
                        "nocheck" => $agentNoCheck,
                        "type" => 1,
                        "icon" => "./img/vm/host.png",
                        "module" => $agentHandler->getAgentModule($d['authorization_module']),
                        "chkDisabled" => !$onlineFlag,
                        "eventtype" => "agent",
                        "net_model" => intval($d['net_model']),
                        "cluster_uuid" => $d['cluster_uuid'],
                        "cluster_flag" => $clusterFlag,
                        'online_flag' => $onlineFlag,
                    );
                }
            }
        }
        return json_encode(array_values($node));
    }

    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode
     * @return string
     */
    public function getTimepointTypeDes($bakcupMode,$db_type){
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $allDbType = Xphp::$_config['DB_TYPE'];
        if($bakcupMode == 4){
            if (
                $db_type == $allDbType['DM'] ||
                $db_type == $allDbType['ORACLE'] ||
                $db_type == $allDbType['POSTGRE'] ||
                $db_type == $allDbType['ANTDB'] ||
                $db_type == $allDbType['KINGBASE'] ||
                $db_type == $allDbType['UXDB'] ||
                $db_type == $allDbType['HIGHGO'] ||
                $db_type == $allDbType['OPENGAUSS'] ||
                $db_type == $allDbType['VASTBASE']
            ) {
                //归档日志备份
                $des = Xphp::$_pfdes['BACKUP_MODE_DES'][5];
            } else {
                //日志备份
                $des = Xphp::$_pfdes['BACKUP_MODE_DES'][$bakcupMode];
            }
        }else{
            $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
        }
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }

    /**
     * 获取数据库时间点列表信息
     * @param unknown $params
     */
    public function getDbTimepointGrid($params){
        $nodeuuid = $params['nodeuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $dbuuid = $params['dbuuid'];
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];
        $createtime = $params['createtime'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $dbType = (int)$params['dbtype'];
        $allDbType = Xphp::$_config['DB_TYPE'];  // 所有的数据库类别

        $copyFlag = $params['copyFlag'];        //副本数据标志
        $archiveFlag = $params['archiveFlag'];  //归档数据标志
        $storageuuid = $params['storageuuid'];
        $localflag = intval($params['localflag']);//本地标志

        $sortArr = array('', 'bbt.timepoint', 'bbt.backup_mode', 'bbt.total_size', 'bbt.write_size', '',
            'bbt.remarks', '', 'bbt.importance_flag'
        );

        //         编号	时间点	类型	数据大小	用户	备注	操作	星标
        $sql = "SELECT bbt.storage_uuid, bbt.timepoint_uuid, UNIX_TIMESTAMP(bbt.timepoint) AS timepoint, bbt.backup_mode,
                    bbt.total_size, bbt.write_size, bbt.data_local_flag,
		            bbt.importance_flag, bbt.remarks, bbt.user_uuid, bbt.real_node_uuid,
		            dbt.db_type ,bsr.node_uuid,bsr.storage_type
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.deleted_flag = ? AND bbt.import_flag = ? AND bbt.available_flag = ? AND dbt.db_uuid = ?
                    AND dbt.agent_uuid = ? AND bbt.task_uuid = ? ";
        $sqlCount = "SELECT count(bbt.id) AS total
                    FROM bd_backup_timepoint bbt
                        INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                        INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                    WHERE bbt.deleted_flag = ? AND bbt.import_flag = ? AND bbt.available_flag = ? AND dbt.db_uuid = ?
                        AND dbt.agent_uuid = ? AND bbt.task_uuid = ? ";
        $flag = Xphp::$_config['FLAG'];
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $dbuuid, $agentuuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $dbuuid, $agentuuid, $taskuuid);
        //如果切换了节点
        if($nodeuuid){
            // 云存储，使用bbt.real_node_uuid；其他存储使用bsr.node_uuid
            $sql .= " and ((bsr.storage_type != ? AND bsr.node_uuid = ?) OR (bsr.storage_type = ? AND bbt.real_node_uuid = ?) )";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid));
            $sqlCount .= " and ((bsr.storage_type != ? AND bsr.node_uuid = ?) OR (bsr.storage_type = ? AND bbt.real_node_uuid = ?) )";
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], $nodeuuid));
        }

        //区分异地本地
        if(!empty($loaclflag)){
            $sql .= " and bbt.data_local_flag = ? ";
            $sqlParams = array_merge($sqlParams,array($loaclflag));
        }


        if($copyFlag || $archiveFlag){
            if($storageuuid && !empty($storageuuid)){
                $sql .=" and bsr.storage_uuid = ? ";
                $sqlCount .= " and bsr.storage_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($storageuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
            }
        }

        if($accurateFlag){
            $search = $params['search'];
            $timepointType = intval($search['timepointType']);
            $backupType = $search['backupType'];
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            if(!empty($timepointType)){
                $archiveLogDbType = [  // 拥有归档日志备份的数据库类别
                    $allDbType['ORACLE'],
                    $allDbType['DM'],
                    $allDbType['POSTGRE'],
                    $allDbType['ANTDB'],
                    $allDbType['KINGBASE'],
                    $allDbType['UXDB'],
                    $allDbType['HIGHGO'],
                    $allDbType['OPENGAUSS'],
                    $allDbType['VASTBASE'],
                ];
                if ($timepointType == Xphp::$_config['BACKUP_MODE']['LOG']) {
                    // 日志备份和归档日志备份的值是一样的, 需要做区分
                    if ($backupType == 'log' && in_array($dbType, $archiveLogDbType)) {
                        // 如果筛选日志备份, 但数据库类型是归档日志备份的数据库类别, 查不出结果
                        $sql .= " and 1<>1 ";  // 这里用 1<>1表示查询条件恒不成立, 即不能查出任何数据
                        $sqlCount .= " and 1<>1 ";
                    }
                    if ($backupType == 'archiveLog' && !in_array($dbType, $archiveLogDbType)) {
                        // 如果筛选备份日志备份, 但数据库不是归档日志备份你的数据库类别, 查不出结果
                        $sql .= " and 1<>1 ";
                        $sqlCount .= " and 1<>1 ";
                    }
                }
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($timepointType));
                $sqlCountParams = array_merge($sqlCountParams, array($timepointType));
            }

            //如果填了开始时间范围查询
            if($startTime && $endTime){
                $sql .= " and bbt.timepoint between '". $startTime ."' and '". $endTime ."' ";
                $sqlCount .= " and bbt.timepoint between '". $startTime ."' and '". $endTime ."' ";
            }

        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);

        $userUuidList = array_column($data, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";
        $sql = "SELECT user_name, user_uuid FROM bd_user WHERE user_uuid IN ($userUuids)";
        $userData = $this->dbSelect($sql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }

        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $records = array("data" => array());
        $i = 1;

        foreach ($data as $d){
            $mark =  $vmHandler->pGetTimepointMark(false, false, false,$utils->parseFlagToBool($d['importance_flag']));
            $type = intval($d['storage_type']);
            $remark = "";   //备注
            $op = array(1,2,3);  // 1备注 2删除 3永久标记
            //如果是异地存储，不能被永久标记 可以进行备注或者删除
            if($type == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                $op = array_diff($op, [3]);
            }
            //如果是磁带存储，不能被永久标记和删除 可以进行备注
            if($type == Xphp::$_config['BD_STORAGE_TYPE']['TAPE']){
                $op = array_diff($op, [2, 3]);
            }
            //时间点在合并中，不让删除和永久标记
            if($d['archive_flag'] == Xphp::$_config['FLAG']['SET']){
                $op = array_diff($op, [2, 3]);
            }
            // 非完备点不能删除和永久标记
            if(intval($d['backup_mode']) != Xphp::$_config['BACKUP_MODE']['FULL']){
                if ($allDbType['MONGODB'] != $dbType) {
                    $op = array_diff($op, [2]);
                }
                $op = array_diff($op, [3]);
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
                '<span tid="'.$d['timepoint_uuid'].'" sid="'.$d['storage_uuid'].'">'.$i++.'</span>',
                '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['timepoint']) . '</span><br>'.$mark.$remark,  //隐藏展示时间点uuid出来,方便运维
                $this->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                $utils->calSize($d['total_size'], true),
                $utils->calSize($d['write_size'], true),
                $vmHandler->getStorageName($d['storage_uuid'], $d['real_node_uuid']),
                $userMapping[$d['user_uuid']],
                $op,
                array(
                    'uuid'=>$d['timepoint_uuid'],
                    'dbtype'=>$d['db_type'],
                    'mode'=>$d['backup_mode'],
                    'dbuuid'=>$dbuuid,
                    'remark' => $d['remarks'],
                    'importance_flag' => $utils->parseFlagToBool($d['importance_flag']),
                    'agentuuid'=>$agentuuid,
                    'taskuuid'=>$taskuuid,
                    'remote_flag' => !$utils->parseFlagToBool(intval($d['data_local_flag'])),
                    'nodeuuid' => $d['node_uuid'],
                ),
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];

        return  json_encode($records);
    }

    /**
     * 获取添加的数据库客户端信息
     * @param unknown $params
     */
    public function getDBAgentInfo($params){
        $agentuuid = $params['agentuuid'];
        $sql = "select agent_name, ip, port, transport_port, client_transport_port from bd_agent where agent_uuid =?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                'ip' => $data[0]['ip'],
                'agentName' => $data[0]['agent_name'],
                'managePort' => $data[0]['port'],
                'transferPort' => $data[0]['transport_port'],
                'clientPort' => $data[0]['client_transport_port'],
            );
        }
        return json_encode($info);
    }

    /**
     * 删除一个备份时间点
     * @param unknown $params
     * storageHandler deleteVMImportData调用
     */
    public function deleteDBTimepoint($params){
        $pointUUID = $params['uuid'];
        $dbType = $params['dbtype'];
        $sql = "SELECT bbt.timepoint, bbt.task_name, bbt.backup_mode, dbt.instance_name
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID));

        $this->paramsCheck($pointUUID, $dbType);
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        //检测是否在任务中在任务就直接返回
        if ($dbType == Xphp::$_config['DB_TYPE']['MONGODB']) {  // 支持删除单个备份点,因此不需要验证备份链
            $this->checkTaskExist(array($pointUUID), $operate, $dbType);
        } else {  // 删除的是整个备份链，需要判断这个备份链是否有备份点处于恢复中
            $backupChain = $this->getBackupChainByFullTimePoint($pointUUID);
            if (!$backupChain) {
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_ERROR_DATABASE_FAILED_OBTAIN_BACKUP_CHAIN'],'error'));
            }
            $this->checkTaskExist($backupChain, $operate, $dbType);
        }
        $dbuuid = $params['dbuuid'];
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];

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

        $submodule_type = intval($dbType);
        $msg = array($pointUUID);
        $msg = json_encode(array('timepoint_uuids'=>$msg));
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbDBMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $timepointDes = $data[0]['timepoint']."(".$pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])].Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'].")";
        $descriptionParam = array($timepointDes, $data[0]['task_name'], $data[0]['instance_name']);
        //返回结果到UI
        if($result){
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_DB_TIMEPOINT', $descriptionParam);
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, db_backup_timepoint dbt
                    where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and dbt.db_name = ? and dbt.agent_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                    ";
            $flag = Xphp::$_config['FLAG'];
            $dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $dbuuid, $agentuuid, $taskuuid, $pointUUID));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count"=>intval($count), "id"=>$pointUUID));
        }else{
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_DB_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取完全备份点的备份链
     * @param string $fullTimePointUuid  完备点uuid
     * @return array
     */
    private function getBackupChainByFullTimePoint(string $fullTimePointUuid): array
    {
        /**
         * 1. 初始条件: 查询完备点
         * 2. 递归条件: depend_point_uuid等于完备点的uuid
         * 3. 查询timepoint_uuid, task_uuid, task_name
         */
        $fullBackMode = Xphp::$_config['BACKUP_MODE']['FULL'];
        $tableName = 'temp_table_' . time();  // 避免表名重复
        $sql = "WITH RECURSIVE $tableName AS (
	                (SELECT * FROM bd_backup_timepoint
	                    WHERE timepoint_uuid = '{$fullTimePointUuid}' AND backup_mode = $fullBackMode ORDER BY timepoint DESC LIMIT 1)
		            UNION ALL
	                SELECT bbt.* FROM bd_backup_timepoint bbt, $tableName WHERE bbt.depend_point_uuid = $tableName.timepoint_uuid 
	            )
	            SELECT timepoint_uuid, task_uuid, task_name FROM $tableName;";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            return [];
        }
        $retTimePointUuid = array_column($data, 'timepoint_uuid');
        return array_values(array_unique($retTimePointUuid));  // 去重
    }

    /**
     * 删除批量备份时间点
     * @param array $params 二维数组
     * 如 array(array('nodeuuid'=>'', 'dbtype'=>'', 'timepointuuid'=>''),array())
     */
    public function deleteDBSelectTimepoint($params){
        //得到二维数组
        //遍历，并按节点uuid分组
        //发送删除消息到不同的节点。删除消息array(timepointuuid,timepointuuid,timepointuuid,timepointuuid)
        $timepointList = $params['timepointlist'];
        $dbList = $params['dblist'];
        $nodeuuid = $params['nodeuuid'];
        $selectTimepoints = array();
        foreach ($dbList as $db){
            $selectTimepoints = $this->getTimepointByDB($db, $nodeuuid);
            $timepointList = array_merge($timepointList, $selectTimepoints);
        }

        $utils = Xphp::instance('Utils');
        $timepointList = $utils->arraySort($timepointList, 'nodeuuid', '', 0, -1);

        $info = array();
        $dbType = 0;
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach($timepointList as $d){
            $i ++;
            $dbType = $d['dbtype'];
            if(!in_array($d['nodeuuid'], $nodeuuids)){
                $nodeuuids[] = $d['nodeuuid'];
                $info[] =  array(
                    "nodeuuid" => $d['nodeuuid'],
                    "dbtype" => $d['dbtype']);
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
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        $this->paramsCheck( $timepointuuids[0][0],$nodeuuids[0]);
        //检测是否在任务中在任务就直接返回
        $uuidList = array();
        foreach($timepointuuids as $index => $d){
            $timepointuuids[$index] = array_values(array_unique($d));  // 时间点去重，不然后台回报时间点不存在的问题
            $uuidList = array_merge($uuidList,$d);
        }
        //检测是否在任务中在任务就直接返回
        $this->checkTaskExist($uuidList, $operate, $dbType);
        $countPoint = 0;
        for($i=0;$i<$nodeuuidsCount;$i++){
            $msg = $timepointuuids[$i];
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            $msg = json_encode($msg);
            $mbResult = $this->mbDBMsg($nodeuuids[$i], intval($info[$i]['dbtype']), $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, Xphp::$_lang['WEB_PLATFORM_DES_DB']);
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
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, dbt.instance_name, dbt.agent_ip from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = ? and dbt.timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_DB'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_DB_INSTANCE_NAME'] . "：" . $data[0]['instance_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_DB'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_DB_INSTANCE_NAME'] . "：" . $data[0]['instance_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
    }

    /**
     * 获取删除的数据库对应时间点信息
     * @param array $dbinfo
     * @param string $nodeuuid
     */
    public function getTimepointByDB($dbinfo, $nodeuuid){
        $info = array();
        $sql =  "SELECT bbt.timepoint_uuid, dbt.db_type, bsr.node_uuid
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid=dbt.timepoint_uuid
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.task_uuid = ? AND dbt.db_uuid = ? ";
        $sqlParams = array($dbinfo['taskuuid'], $dbinfo['dbuuid']);
        if(!empty($nodeuuid)){
            $sql .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $data  = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d){
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $d['node_uuid'],
                'dbtype' => $d['db_type']
            );
        }
        return $info;
    }

    /**
     * 获取数据库备份任务信息(修改任务用)
     */
    public function getBackupTaskAllInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT bt.thread_num, bt.task_name, bt.strategy_id, bt.node_uuid, bt.strategy_group_uuid, bt.storage_uuid,
                    bt.agent_uuid, bt.node_pool_uuid, bt.storage_pool_uuid, bt.ignore_resource_limiting_flag,
                    dt.db_type, dt.db_type, dt.channel_count, dt.last_archive_days,
                    dt.set_filesperset_flag, dt.datafile_filesperset_num, dt.archivelog_filesperset_num, dt.delete_archive_log_flag,
                    dt.log_backup_times_flag, dt.log_backup_times, dt.log_backup_days_flag, dt.log_backup_days,
                    dt.check_db_flag, dt.compress_flag AS db_compress_flag, dt.checksum_flag, dt.detail,
                    dt.skip_inaccessible_file_flag, dt.skip_offline_file_flag,
                    dt.auto_log_backup_interval, dt.enable_bct_flag, dt.set_section_size_flag, dt.section_size,
                    dt.compress_method AS db_compress_method, dt.compress_level AS db_compress_level,
                    dt.multi_task_flag, dt.custom_rman_cmd, dt.depend_task_uuid,
                    brs.strategy_type, brs.number, brs.strategy_mode,
                    bts.encrypt_flag, bts.network_uuid, bts.encrypt_method as transport_method,
                    bts.max_object_transport_parallel_nums, bts.network_pool_uuid,
                    bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.compress_method, bss.encrypted_flag,
                    bss.password_auto_flag, bss.password, bss.encrypt_method,
                    bres.network_retry_times, bres.network_retry_interval, bres.op_retry_times, bres.op_retry_interval, 
                    bres.task_retry_object, bres.task_retry_times, bres.task_retry_interval 
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                    INNER JOIN bd_reserved_strategy brs ON bt.strategy_id = brs.strategy_id
                    INNER JOIN bd_transport_strategy bts ON bt.strategy_id = bts.strategy_id
                    INNER JOIN bd_storage_strategy bss ON bt.strategy_id = bss.strategy_id
                    INNER JOIN bd_retry_strategy bres ON bt.task_uuid = bres.task_uuid
                WHERE bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $info = array();
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        $allDbType = Xphp::$_config['DB_TYPE'];
        $subTaskName = '';
        $subTaskFlag = false;
        $dependTaskUuid = '';
        if($data){
            $dbType = intval($data[0]['db_type']);
            switch ($dbType){
                case $allDbType['SQLSERVER']:
                    $agentInfo = array(
                        'checkdbflag' => $utils->parseFlagToBool($data[0]['check_db_flag']),
                        'compressflag' => $utils->parseFlagToBool($data[0]['db_compress_flag']),
                        'checksumflag' => $utils->parseFlagToBool($data[0]['checksum_flag']),
                        'dbtype' => $dbType
                    );
                    break;
                case $allDbType['ORACLE']:
                    $dependTaskUuid = $data[0]['depend_task_uuid'];
                    $agentInfo = array(
                        'compressflag' => $utils->parseFlagToBool($data[0]['db_compress_flag']),
                        'checkdbflag' => $utils->parseFlagToBool($data[0]['check_db_flag']),
                        'channel_count' => intval($data[0]['channel_count']),
                        'last_archive_days' => intval($data[0]['last_archive_days']),
                        'delete_archive_log_flag' => intval($data[0]['delete_archive_log_flag']),
                        'dbtype' => $dbType,
                        'set_filesperset_flag' => $utils->parseFlagToBool($data[0]['set_filesperset_flag']),
                        'datafile_filesperset_num' => (int) $data[0]['datafile_filesperset_num'],
                        'archivelog_filesperset_num' => (int) $data[0]['archivelog_filesperset_num'],
                        'log_backup_times_flag' => $utils->parseFlagToBool($data[0]['log_backup_times_flag']),
                        'log_backup_times' => (int) $data[0]['log_backup_times'],
                        'log_backup_days_flag' => $utils->parseFlagToBool($data[0]['log_backup_days_flag']),
                        'log_backup_days' => (int) $data[0]['log_backup_days'],
                        'skip_inaccessible_file_flag' => $utils->parseFlagToBool($data[0]['skip_inaccessible_file_flag']),
                        'skip_offline_file_flag' => $utils->parseFlagToBool($data[0]['skip_offline_file_flag']),
                        'enable_bct_flag' => $utils->parseFlagToBool($data[0]['enable_bct_flag']),
                        'set_section_size_flag' => $utils->parseFlagToBool($data[0]['set_section_size_flag']),
                        'section_size' => (int) $data[0]['section_size'],
                        'multi_task_flag' => $utils->parseFlagToBool($data[0]['multi_task_flag']),
                        'custom_rman_cmd' => $data[0]['custom_rman_cmd'],
                    );
                    break;
                case $allDbType['MARIA']:
                case $allDbType['MYSQL']:
                    $agentInfo = array(
                        'compressflag' => $utils->parseFlagToBool($data[0]['db_compress_flag']),
                        'channel_count' => $data[0]['channel_count'],
                    );
                    break;
                case $allDbType['DM']:
                    $agentInfo = array(
                        'compressflag' => $utils->parseFlagToBool($data[0]['db_compress_flag']),
                        'compress_level' => intval($data[0]['db_compress_level']),
                        'delete_archive_log_flag' => $utils->parseFlagToBool($data[0]['delete_archive_log_flag']),
                        'dbtype' => $dbType
                    );
                    break;
                case $allDbType['POSTGRE']:
                case $allDbType['ANTDB']:
                case $allDbType['KINGBASE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['VASTBASE']:
                    $detail = json_decode($data[0]['detail'], true);
                    $info = [
                        'warn_check' => $utils->parseFlagToBool($detail['warn_check']),
                        'warn_type' => intval($detail['warn_type']),
                        'warn_value' => intval($detail['warn_value']),
                    ];
                    if($info['warn_type'] == 2){
                        $info['warn_value'] = intval($detail['warn_value']) /1024/1024/1024;
                    }
                    $agentInfo = array(
                        'compressflag' => intval($data[0]['db_compress_flag']),
                        'delete_archive_log_flag' => intval($data[0]['delete_archive_log_flag']),
                        'dbtype' => $dbType,
                        'warningInfo' => $info
                    );
                    break;
                case $allDbType['MONGODB']:
                    $agentInfo = [
                        'max_object_transport_parallel_nums' => intval($data[0]['max_object_transport_parallel_nums']),
                    ];
                    break;
                case $allDbType['TIDB']:
                    $sql = "SELECT bt.task_name, dt.depend_task_uuid
                            FROM bd_task bt
                                INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                            WHERE dt.depend_task_uuid = ? ";
                    $subTaskData = $this->dbSelect($sql, [$taskUUID]);
                    if (is_array($subTaskData) && $subTaskData) {
                        $subTaskName = $subTaskData[0]['task_name'];
                        $subTaskFlag = true;
                        $dependTaskUuid = $taskUUID;
                    }
                    $agentInfo = [
                        'compress_method' => $data[0]['db_compress_method'],
                        'compress_level' => $data[0]['db_compress_level'],
                        'max_object_transport_parallel_nums' => intval($data[0]['max_object_transport_parallel_nums']),
                    ];
                    break;
                case $allDbType['SAPHANA']:
                    $agentInfo = [
                        'compressflag' => $utils->parseFlagToBool($data[0]['db_compress_flag']),
                        'auto_log_backup_interval' => (int) $data[0]['auto_log_backup_interval'],
                        'channel_count' => (int) $data[0]['channel_count'],
                    ];
                    break;
            }
            $agentInfo['thread_num'] = intval($data[0]['thread_num']);
            $agentInfo['ignore_resource_limiting_flag'] = $utils->parseFlagToBool($data[0]['ignore_resource_limiting_flag']);
            $agentInfo['multi_task_flag'] = $agentInfo['multi_task_flag'] ?? false;
            // 查询存储池类型
            $storagePoolType = 0;
            if ($data[0]['storage_pool_uuid']) {
                $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$data[0]['storage_pool_uuid']]);
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }
            // 查询存储类型
            $storageType = 0;
            if ($data[0]['storage_uuid']) {
                $storageData = $this->dbSelect("SELECT storage_type FROM bd_storage_resource WHERE storage_uuid = ? ", [$data[0]['storage_uuid']]);
                $storageType = $storageData[0]['storage_type'];
            }

            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //任务名
                'taskname' => $data[0]['task_name'],
                // 日志任务名
                'sub_task_name' => $subTaskName,
                'sub_task_flag' => $subTaskFlag,
                'depend_task_uuid' => $dependTaskUuid,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //数据库类型
                'dbtype' => intval($data[0]['db_type']),
                //保留策略
                'brs' => array(
                    'type' => intval($data[0]['strategy_type']),
                    'number' => intval($data[0]['number']),
                    'strategyMode' => intval($data[0]['strategy_mode']),
                ),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,
                    'storage_type' => $storageType,
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
                    'mode' => intval($data[0]['transport_priority']),
                    'network' => $data[0]['network_uuid'],
                    'network_pool_uuid' => $data[0]['network_pool_uuid'],
                    'thread_num' => intval($data[0]['thread_num']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                ),
                //存储策略
                'bss' => array(
                    'deduplication' => $utils->parseFlagToBool($data[0]['deduplication_flag']),
                    'blocksize' => intval($data[0]['block_size'])/1024,
                    'compress_flag' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'dataencrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'password_auto_flag' => $utils->parseFlagToBool($data[0]['password_auto_flag']),
                    'password' => base64_encode($utils->ptPassDecrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method']),
                ),
                //虚拟机信息
                'db_info' => $this->getDBEditInfo($taskUUID, $data[0]['db_type']),
                //时间策略
                'timestrategy' => $vmHandler->getTimeStrategyInfo($data[0]['strategy_id']),

                //代理配置
                'agentInfo' => $agentInfo,
                //限速策略
                // 'speedInfo' => $vmHandler->getSpeedStrategyInfo($taskUUID),
                'speedInfo' => $vmHandler->getSpeedGlobalStrategyInfo($taskUUID),
                'retry_strategy' => $jobHandler->groupRetryStrategyInfo($data[0]),  // 重试策略
                'safe_config_strategy' => $jobHandler->getSafeConfigStrategy($taskUUID),  // 安全配置策略
            );
        }
        return json_encode($info);
    }

    /**
     * 获取任务修改的数据库信息
     * @param string $taskuuid
     * @param int $dbType
     */
    private function getDBEditInfo($taskuuid, $dbType){
        $info = array();
        $utils = Xphp::instance('Utils');
        $sql = "SELECT db_name, instance_name, dir_path, agent_uuid, group_uuid, db_uuid, before_task_script, after_task_script, detail FROM db_list WHERE task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $sql = "SELECT cluster_uuid, cluster_flag, agent_uuid, app_name, app_type FROM bd_agent_app ";
        $appData = $this->dbSelect($sql);
        foreach ($data as $d) {
            $clusterFlag = false;
            $clusterUuid = '';
            $agentUuidList = [$d['agent_uuid']];
            if (is_array($appData) && $appData) {
                foreach ($appData as $appInfo) {
                    if (
                        $appInfo['app_name'] === $d['instance_name'] &&
                        $appInfo['agent_uuid'] === $d['agent_uuid'] &&
                        $appInfo['app_type'] === $dbType
                    ) {
                        $clusterUuid = $appInfo['cluster_uuid'];
                        $clusterFlag = $utils->parseFlagToBool($appInfo['cluster_flag'] && $appInfo['cluster_uuid']);
                        break;
                    }
                }
                if ($clusterFlag) {
                    $agentUuidList = [];
                    foreach ($appData as $appInfo) {
                        if ($appInfo['cluster_uuid'] === $clusterUuid) {
                            $agentUuidList[] = $appInfo['agent_uuid'];
                        }
                    }
                }
            }
            $detail = [];
            if ($dbType == Xphp::$_config['DB_TYPE']['ORACLE']) {
                $detail = json_decode($d['detail'], true);
                $detail = [
                    'skip_datafiles_bad_block_info' => $detail['skip_datafiles_bad_block_info'] ?? [],
                ];
            }
            $info[] = array(
                "dbname" => $d['db_name'],
                "instancename" => $d['instance_name'],
                "agentuuid" => $d['agent_uuid'],
                "agent_uuid_list" => $agentUuidList,
                "groupuuid" => $d['group_uuid'],
                "dir_path" => $d['dir_path'],
                "config" => array(),
                "dbuuid" => $d['db_uuid'],
                'cluster_uuid' => $clusterUuid,
                'cluster_flag' => $clusterFlag,
                'before_task_script' => json_decode($d['before_task_script'], true),
                'after_task_script' => json_decode($d['after_task_script'], true),
                'detail' => $detail,
            );
        }

        return $info;
    }

    /**
     * 得到修改备份任务树
     * @param array $params
     */
    public function getBackupTreeOldInfo($params)
    {
        $taskuuid = $params['taskuuid'];
        $dbType = $params['type'];
        $agentuuid = $params['agentuuid'];
        $groupuuid = $params['groupuuid'];
        $agentname = $params['agentname'];
        $clusterUuid = $params['cluster_uuid'];
        $clusterFlag = $params['cluster_flag'];
        $params['editFlag'] = true; //修改标志

        // 获取集群、实例、数据库信息
        $topNode = $this->getDBTree($params);
        $utils = Xphp::instance('Utils');
        $topNode = $utils->object_array(json_decode($topNode));
        $allDbType = Xphp::$_config['DB_TYPE'];
        //获取该任务所备份的数据库
        $sql = "select dl.db_name, dl.instance_name, dl.db_uuid from db_list dl  where dl.task_uuid = ?  ";
        $sqlParams = [$taskuuid];
        if (
            $clusterFlag &&
            (
                $dbType == $allDbType['MONGODB'] ||
                $dbType == $allDbType['TIDB']
            )
        ) {
            $subSql = "SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid = ? ";
            $clusterAgentList = $this->dbSelect($subSql, [$clusterUuid]);
            $clusterAgentUuidList = array_column($clusterAgentList, 'agent_uuid');
            $clusterAgentUuids = "'" . implode("', '", $clusterAgentUuidList) . "'";
            $sql .= " AND dl.agent_uuid IN ($clusterAgentUuids) ";
        } else {
            $sql .= " AND dl.agent_uuid = ? ";
            $sqlParams[] = $agentuuid;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $clusterType = 0;
        if ($clusterFlag) {
            if ($allDbType['MONGODB'] == $dbType) {
                $sql = " SELECT cluster_type FROM bd_agent_app WHERE cluster_uuid = ? ";
                $appInfo = $this->dbSelect($sql, [$clusterUuid]);
                $clusterType = intval($appInfo[0]['cluster_type']);
            }
        }
        $instanceUUIDs = array();
        $childArr = array();
        if ($data) {
            //得到所有备份数据库uuid数组
            $dbuuidArr = array();
            foreach ($data as $d){
                $dbuuidArr[] = array(
                    'db_name' => $d['db_name'],
                    'instance_name' => $d['instance_name'],
                    'db_uuid' => $d['db_uuid']
                );
                if(!in_array($d['instance_name'], $instanceUUIDs)){
                    $instanceUUIDs[] = $d['instance_name'];
                }
            }
            foreach ($instanceUUIDs as $instanceUuid){
                if (
                    (2 == $clusterType && $allDbType['MONGODB'] == $dbType) ||  // MongoDB的分片集群已经加载数据库了，因此不需要再加载数据库
                    $allDbType['TIDB'] == $dbType  // TIDB也不需要加载数据库
                ) {
                    continue;
                }
                //获取对应Instance的子树
                $syncParams = array(
                    'id' => $instanceUuid,
                    'agentuuid' => $agentuuid,
                    'groupuuid' => $groupuuid,
                    'agentip' => $agentname,
                    'dbtype' => $dbType,
                    'cluster_flag' => $clusterFlag,
                    'cluster_uuid' => $clusterUuid,
                );
                $syncInstance = $this->getBackupSyncAgent($syncParams);
                $syncInstance = $utils->object_array(json_decode($syncInstance));
                if($syncInstance['re']){
                    //获取成功
                    $msg = $syncInstance['msg'];
                    foreach ($msg as $key => $each){
                        $msg[$key]['dbuuid'] = "";
                        if ($dbType == $allDbType['MONGODB']) {
                            continue;
                        }
                        //如果找到对应数据库,直接赋值选中
                        foreach ($dbuuidArr as $db){
                            if($each['name'] == $db['db_name'] && $each['instanceuuid'] == $db['instance_name']){
                                $msg[$key]['checked'] = true;
                                $msg[$key]['chkDisabled'] = false;
                                if(!empty($db['db_uuid'])){
                                    $msg[$key]['dbuuid'] = $db['db_uuid'];
                                }
                            }
                        }
                    }
                    $childArr = array_merge($childArr, $msg);
                }
            }
        }
        //展开
        foreach ($topNode as $key => $each){
            if ($each['eventtype'] == 'cluster') {
                if (2 == $clusterType && $allDbType['MONGODB'] == $dbType) {
                    $topNode[$key]['open'] = true;
                    $topNode[$key]['checked'] = true;
                    continue;
                }
            }
            foreach ($instanceUUIDs as $instanceUuid){
                if($each['instanceuuid'] == $instanceUuid){
                    //展开
                    $topNode[$key]['open'] = true;
                    $topNode[$key]['checked'] = true;
                    break;
                }
            }
        }

        return json_encode(array_merge($topNode, $childArr));
    }

    /**
     * 得到修改任务的节点和存储UUID信息
     * @return array('nodeuuid','auto_find_sr_flag','storageuuid')
     */
    private function getDBModifyJobNodeAndStorageInfo($nodeInfo){
        $info = array(
            'nodeuuid' => $nodeInfo['nodeuuid'],
            'auto_find_sr_flag' => Xphp::$_config['FLAG']['UNSET'],
            'storageuuid' => $nodeInfo['storageuuid']
        );
        if(empty($nodeInfo['nodeuuid'])){
            $nodeHandler = Xphp::instance('NodeHandler');
            $info['nodeuuid'] = $nodeHandler->getAutoFindNode();
        }
        if($nodeInfo['storagecheck']){
            //自动选择存储
            $info['auto_find_sr_flag'] = Xphp::$_config['FLAG']['SET'];
            $info['storageuuid'] = '';
        }
        return $info;
    }

    /**
     * 创建和修改数据库备份任务做检测(vcenter.class.php有调用)
     * @param string $operate   操作描述
     * @param array $dbInfo     本次备份的数据库
     * @param string $taskuuid  任务UUID(修改的时候用)
     * @param int $dbType 数据库类别
     */
    private function checkDBTaskLegal($operate, $dbInfo, $taskuuid, int $dbType)
    {
        // $dbCount = $this->getAllTaskCount($dbInfo, $taskuuid);
        //这里暂时只检测在按虚拟机授权的时候虚拟机个数是否足够,不足够直接退出并提示
        $systemHandler = Xphp::instance('SystemHandler');
        $systemLisenceInfo = $systemHandler->getSystemLisenceInfo();
        $systemLisenceInfo = json_decode($systemLisenceInfo, true);

        if($systemLisenceInfo['status'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            //未授权
            exit($this->muOpResult(false, $operate, $systemLisenceInfo['statusDes'], 'error'));
        }
        //已授权
        // 如果是按照虚拟机授权,检测虚拟机个数是否超出限制
        // if($systemLisenceInfo['vminfo']['type'] == Xphp::$_config['LISENCE_INFO']['type']['vm']){
        //     $validCount = $systemLisenceInfo['vminfo']['valid'];
        //     if($dbCount > $validCount){
        //         exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_LISENCE_USED_ALL'], 'error'));
        //     }
        // }
        if (!$taskuuid) {
            if ($dbType == Xphp::$_config['DB_TYPE']['ORACLE']) {  // Oracle可以建立多个备份任务
                return true;
            }
            foreach ($dbInfo as $db) {
                $inBackupFlag = $this->getDBInBackupFlag($db['instancename'], $db['dbname'], $db['agentuuid'], $dbType);
                if($inBackupFlag){
                    exit($this->muOpResult(false, $operate,Xphp::$_lang['WEB_LOG_SYSTEM_DB_IN_BACKUP_TIPS'], 'warning'));
                }
            }
        }
        return true;
    }

    /**
     * 获取任务新增数据库个数
     * @param array $dbInfo
     * @param string $taskuuid
     * @return number
     */
    private function getAllTaskCount($dbInfo, $taskuuid){
        $dbNum = count($dbInfo);
        if(empty($taskuuid)){
            //创建任务
            return $dbNum;
        }
        //修改任务
        $sql = "select count(database_id) as db_num from db_list where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $oldNum = intval($data[0]['db_num']);
        $newNum = $dbNum - $oldNum;
        return $newNum;
    }

    /**
     * 获取数据库备份标记
     * @param string $instancename
     * @param string $dbname
     * @param string $agentuuid
     * @param int $dbType
     * @return boolean
     */
    private function getDBInBackupFlag($instancename, $dbname, $agentuuid, int $dbType){
        $sql = "SELECT dl.task_uuid
                FROM bd_task bt
                    INNER JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                    INNER JOIN db_task dt on bt.task_uuid = dt.task_uuid
                WHERE dl.agent_uuid = ? AND dl.instance_name = ? AND dl.db_name = ?
                    AND bt.task_type = ? AND dt.db_type = ? ";
        $data = $this->dbSelect($sql, array(
            $agentuuid,
            $instancename,
            $dbname,
            Xphp::$_config['TASKTYPE']['DB_BACKUP'],
            $dbType
        ));
        $flag = false;
        if(!empty($data)){
            $flag = true;
        }

        return $flag;
    }

    /**
     * 获取在当前数据库任务的数据库列表
     * @param string $instancename
     * @param string $agentuuid
     * @param string $taskuuid
     * @return fetchAll()[]
     */
    private function getDBInBackupList($instancename, $agentuuid, $taskuuid){
        $sql = "select dl.db_name from bd_task bt, db_list dl where bt.task_uuid = dl.task_uuid and dl.agent_uuid = ? and dl.instance_name = ? and dl.task_uuid = ? and bt.task_type = ? ";
        $data = $this->dbSelect($sql, array($agentuuid, $instancename, $taskuuid, Xphp::$_config['TASKTYPE']['DB_BACKUP']));
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[] = $d['db_name'];
            }
        }

        return $info;
    }

    /**
     * 获取主机是否授权了该数据库类型
     * @param string $agentuuid
     * @param int $type
     */
    public function getDBAuthStatus($params){
        $agentuuid = $params['agentuuid'];
        $type = $params['dbtype'];
        $sql = "select authorization_module from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $flag = false;
        if(!empty($data)){
            $list = json_decode($data[0]['authorization_module'], true);
            switch ($type){
                case 1:
                    $flag = $list['sqlserver'];
                    break;
                case 2:
                    $flag = $list['oracle'];
                    break;
                case 3:
                    $flag = $list['mysql'];
                    break;
            }
        }

        $info = array(
            "flag" => $flag
        );

        return json_encode($info);

    }

    /**
     * 检查代理是否存在
     * @param string $ip
     */
    public function checkAgentExist($ip){
        $sql = "select agent_uuid from bd_agent where ip = ?";
        $data = $this->dbSelect($sql, array($ip));
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_ADD_CLIENT'], Xphp::$_lang['WEB_DB_AGENT_ADD_EXIST_ERROR'], "warning"));
        }
    }

    /**
     * 删除数据库代理检测
     * @param array $agentuuids
     * @return boolean
     */
    public function deleteAgentCheck($agentuuids){
        $agentuuidDes = implode("','", $agentuuids);
        $hite = Xphp::$_lang['WEB_DB_DELETE_AGENT_HAVE_TASK_TIPS'];
        $Str = $this->DBAgentCheck($agentuuids);
        if(!empty($Str)){
            $hite .= ',  '.Xphp::$_lang['WEB_NODE_NAME_OF_TASK_USING_THIS_AGENT'].': <br>';
            $hite .= $Str;
        }
        //检测主机里是否有数据库存在于备份或恢复任务中
        $sqlCheck = "select task_uuid from bd_task where agent_uuid in ('".$agentuuidDes."')";
        $data = $this->dbSelect($sqlCheck);
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_DELETE_CLIENT'], $hite, "warning"));
        }
        return true;
    }

    /**
     * 删除传输代理检测 看次传输代理是否有任务在使用 如果有就给出提示(删除数据库备份代理)
     * @author liushuai@vinchin.com
     * @return
     */
    public function DBAgentCheck($agentuuids){
        $str = "";
        $agentuuidDes = implode("','", $agentuuids);
        $sqlCheck ="select task_name from bd_task where agent_uuid in ('".$agentuuidDes."')";
        $sql_db_task = $this->dbSelect($sqlCheck);
        if(empty($sql_db_task)){  //如果没有任务在使用此传输策略
            return $str;
        }
        $i = 0;//控制一行显示几个
        $j = 0;//控制显示几行
        foreach ($sql_db_task as $p){
            $str .= $p['task_name']."  ;  ";
            $i++;
            $j++;
            if($i == 2){
                $str .="<br>";
                $i = 1;
            }
            if($j == 5){
                return $str."<br> ...";
            }
        }
        return $str;
    }

    /**
     * 获取Oracle关联实例树
     * @param unknown $params
     * @return string
     */
    public function getReletiveInstanceTree($params){
        $agentuuid = $params['agentuuid'];
        $dbType = $params['dbtype'];
        $selectInstance = $params['instancename'];

        $instanceMsg = $this->getInstanceMsg($dbType, $agentuuid);  //获取数据库实例列表
        $instanceList = $instanceMsg ? $instanceMsg['instance_list'] : [];
        $sql = "select baa.app_name, baa.dir_path, baa.agent_uuid, ba.agent_name, baa.cluster_uuid from bd_agent_app baa, bd_agent ba where baa.agent_uuid = ba.agent_uuid and ba.agent_uuid = ? and baa.app_type = ?";
        $data = $this->dbSelect($sql, array($agentuuid, $dbType));
        $node = array();

        if(!empty($instanceList)){
            foreach ($instanceList as $instance){
                $instancename = trim($instance['instance']);
                $chkDisabled = true;
                if($selectInstance == $instancename){
                    $chkDisabled = false;
                }
                $info = array(
                    "id" => $instancename,
                    "pid" => $agentuuid,
                    "name" => $instancename . '('.Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED']. ')',
                    "title" => $instancename . '('.Xphp::$_lang['WEB_DB_INSTANCE_UNVERIFIED']. ')',
                    "nocheck" => true,
                    "type" => 2,
                    "flush" => true,
                    "icon" => "./img/vm/host.png",
                    "eventtype" => "instance",
                    "agentuuid" => $agentuuid,
                    "instanceuuid" => $instancename,
                    "verifyflag" => false,
                    "open" => false,
                    "dbtype" => $dbType,
                    "isParent" => false,
                    "chkDisabled" => $chkDisabled
                );
                foreach ($data as $d){
                    if(trim($instance['instance']) == $d['app_name']){
                        $info['verifyflag'] = true;
                        $info['name'] = $d['app_name'];
                        $info['title'] = $d['app_name'];
                        if(!empty($d['cluster_uuid'])){
                            $info['chkDisabled'] = true;
                        }
                    }
                    continue;
                }


                $node[] = $info;
            }
        }

        $msg = array(
            're' => true,
            'msg' => $node
        );

        return json_encode($msg);
    }


    /**
     * 获取每种数据库认证实例配置信息
     * @param array $d
     * @param int $type
     * @return array
     */
    public function getDbInstanceDetails($d, $type): array
    {
        $allDbType = Xphp::$_config['DB_TYPE'];
        $info = array();
        $appDetail = json_decode($d['detail'], true);
        switch ($type){
            case $allDbType['SQLSERVER']:
                $info = array(
                    'auth_type' => intval($d['auth_type']),
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    "cluster_uuid" => $d['cluster_uuid']
                );
                break;
            case $allDbType['ORACLE']:
                $info = array(
                    'auth_type' => intval($d['auth_type']),
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'install_db_username' => $d['install_db_username'],
                    'cluster_uuid' => $d['cluster_uuid'],
                    'cluster_name' => $d['cluster_name'],
                    'cluster_service_ip' => $d['cluster_service_ip'],
                    'other_info' => $appDetail,
                );
                break;
            case $allDbType['MYSQL']:
            case $allDbType['MARIA']:
                $info = array(
                    'auth_type' => intval($d['auth_type']),
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'other_info' => $appDetail,
                );
                break;
            case $allDbType['DM']:
                $info = array(
                    'auth_type' => intval($d['auth_type']),
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'install_db_username' => $d['install_db_username'],
                    'other_info' => $appDetail,
                );
                break;
            case $allDbType['POSTGRE']:
            case $allDbType['ANTDB']:
            case $allDbType['KINGBASE']:
            case $allDbType['UXDB']:
            case $allDbType['HIGHGO']:
            case $allDbType['OPENGAUSS']:
            case $allDbType['VASTBASE']:
                $info = array(
                    'auth_type' => intval($d['auth_type']),
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'install_db_username' => $d['install_db_username'],
                    'other_info' => $appDetail,
                    'cluster_uuid' => $d['cluster_uuid'],
                );
                break;
            case $allDbType['MONGODB']:  // MongoDB
                $info = [
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'cluster_uuid' => $d['cluster_uuid'],
                    'cluster_name' => $d['cluster_name'],
                ];
                break;
            case $allDbType['TIDB']:  // TiDB
                $info = [
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'cluster_uuid' => $d['cluster_uuid'],
                    'db_name' => $appDetail['database_name'],
                    'install_db_username' => $d['install_db_username'],
                ];
                break;
            case $allDbType['SAPHANA']:
                $info = [
                    'username' => $d['user_name'],
                    'password' => $d['password'],
                    'cluster_service_ip' => $d['cluster_service_ip'],
                    'cluster_uuid' => $d['cluster_uuid'],
                ];
        }

        $info['authFlag'] = $d['authFlag'];
        $info['clusterFlag'] = $d['clusterFlag'];

        return $info;
    }


    /**
     * 通过数据库类型筛选恢复目的主机
     * @param int $dbType
     */
    public function getRecoveryListByType($dbType){
        $sql = "select distinct agent_uuid from bd_agent_app where app_type = ?";
        $data = $this->dbSelect($sql, array($dbType));
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[] = $d['agent_uuid'];
            }

        }

        return $info;
    }

    /**
     * 根据任务uuid获取代理
     * @param string $taskuuid
     * @return string|fetchAll()
     */
    public function getAgentuuidByTask($taskuuid){
        $sql = "select agent_uuid from bd_task where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $agentuuid ="";
        if(!empty($data)){
            $agentuuid = $data[0]['agent_uuid'];
        }

        return $agentuuid;
    }

    /**
     * 修改任务检查代理端状态
     * @param unknown $params
     * @return string
     */
    public function checkAgentOnline($params){
        $taskuuid = $params['taskuuid'];
//         $sql = "select ba.online_flag from bd_task bt, bd_agent ba where bt.agent_uuid = ba.agent_uuid and bt.task_uuid = ? ";
//         $data = $this->dbSelect($sql, array($taskuuid));
//         if(!empty($data)){
//             $onlineFlag = intval($data[0]['online_flag']);
//             if($onlineFlag == Xphp::$_config['FLAG']['UNSET']){
//                 return $this->muOpResult(false, Xphp::$_lang['UI_BACKUP_EDIT_TASK'], Xphp::$_lang['WEB_DB_AGENT_OFFLINE_TIPS'], "warning");
//             }
//         }
        $info = array(
            'flag' => true
        );
        return json_encode($info);
    }


    /**
     * 获取已授权数据库类型
     * @param unknown $agentuuid
     * @return array|fetchAll()
     */
    public function pGetDbTypeList($agentuuid){
        $sql = "select distinct app_type as db_type from bd_agent_app where agent_uuid = ? order by app_type asc";
        $data = $this->dbSelect($sql, array($agentuuid));
        $info = array();
        if(!empty($data)){
            $info = $data;
        }

        return $info;
    }

    /**
     * 获取当前可以备份的数据库类型列表
     * @param unknown $params
     * @return string
     */
    public function getDbBackupTypeList($params){
        $agentuuid = $params['agentuuid'];
        $data = $this->pGetDbTypeList($agentuuid);
        $list = array();
        if(!empty($data)){
            foreach ($data as $d){
                $type = intval($d['db_type']);
                if ($type == 1000) {  // 排除exchange
                    continue;
                }
                $list[] = array(
                    'text' => Xphp::$_config['DB_TYPE_DES'][$type],
                    'value' => $type
                );
            }
        }

        return json_encode($list);
    }

    /**
     * 获取当前可以备份的数据库类型列表
     * @param unknown $params
     * @return string
     */
    public function getDbTypeList($params){
        $agentuuid = $params['agentuuid'];
        $data = $this->pGetDbTypeList($agentuuid);
        $list = array();
        $dbList = Xphp::$_config['DB_TYPE_INDEX'];
        $select = array();
        foreach ($dbList as $d){
            $list[] = array(
                'text' => Xphp::$_config['DB_TYPE_DES'][$d],
                'value' => $d
            );
        }
        if(!empty($data)){
            foreach ($data as $d){
                $type = intval($d['db_type']);
                $select[] = $type;
            }
        }
        $info = array(
            'list' => $list,
            'select' => $select
        );

        return json_encode($info);
    }

    /**
     * 获取数据库实例集群关联主机
     * @param string $clusteruuid
     * @return fetchAll()[]
     */
    private function getClusterList($clusteruuid){
        $list = array();
        $otherList = array();
        $sql = "select distinct agent_uuid from bd_agent_app where cluster_uuid = ? and cluster_uuid != ''";
        $data = $this->dbSelect($sql, array($clusteruuid));
        if(!empty($data)){
            foreach ($data as $d){
                $list[] = $d['agent_uuid'];
            }
        }
        $sqlOther = "select distinct agent_uuid from bd_agent_app where cluster_uuid != ? and cluster_uuid != ''";
        $dataOther = $this->dbSelect($sqlOther, array($clusteruuid));
        if(!empty($data)){
            foreach ($data as $d){
                $otherList[] = $d['agent_uuid'];
            }
        }
        $info = array(
            'cluster_list' => $list,
            'other' => $otherList
        );
        return $info;
    }


    /**
     * 通过归档日志时间点获取上一个依赖的完备或归档日志点
     * @param string $timepointuuid
     * @param int $startTimepoint
     * @return string|unknown|string
     */
    public function getArchiveLogStartTime($timepointuuid, $startTimepoint){
        $sql = "select backup_mode, depend_point_uuid, src_end_timepoint from bd_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($timepointuuid));
        if(!empty($data)){
            $startTimepoint = date('Y-m-d H:i:s', intval($data[0]['src_end_timepoint']));
            //先找最近的上一个归档点，然后找完备点
            if($data[0]['backup_mode'] == 4 || $data[0]['backup_mode'] == 1){
                return $startTimepoint;
            }else{
                return $this->getArchiveLogStartTime($data[0]['depend_point_uuid'], $startTimepoint);
            }
        }else{
            return $startTimepoint;
        }
    }

    /**
     * 获取代理名
     * @param unknown $uuid
     */
    public function getAgentNameByUUID($params){
        $uuid = $params['agentuuid'];
        $sql = "select agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $agentName = Xphp::$_config['NULLSPACE'];
        if(!empty($data)){
            $agentName = $data[0]['agent_name'] == $data[0]['ip'] ? $data[0]['ip']."(". $data[0]['hostname'] .")" : $data[0]['ip']."(". $data[0]['agent_name'] .")";
        }

        $info = array(
            'agentName' => $agentName
        );
        return json_encode($info);
    }


    /**
     * 根据uuid获取对应的代理IP
     * @param array $uuids
     * @return mixed
     */
    private function getAgentnamebyUuids($uuids){
        $nameDes = Xphp::$_config['NULLSPACE'];
        if(empty($uuids)) return $nameDes;
        $uuidDes = implode("','", $uuids);
        $sql = "select ip from bd_agent where agent_uuid in ('".$uuidDes."')";
        $data = $this->dbSelect($sql);
        if(!empty($data)){
            $nameDes = '';
            foreach ($data as $key => $d){
                if($key == (count($data) - 1)){
                    $nameDes .= $d['ip'];
                }else{
                    $nameDes .= $d['ip'] . ',';
                }
            }
        }

        return $nameDes;
    }

    /**
     * 获取oracle pdb恢复数据库
     * @param unknown $params
     */
    public function getOracleDbList($params){
        $timepointuuid = $params['timepointuuid'];
        $sql = "select db_config from db_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($timepointuuid));
        $list = array();
        $dbConfig = json_decode($data[0]['db_config'], true);
        $dbList = $dbConfig['pdb_list'];
        if(!empty($dbList)){
            foreach ($dbList as $d){
                $list[] = $d['pdb_name'];
            }
        }

        return json_encode($list);
    }

    /**
     * 通过时间点获取原来的db_uuid
     * @param unknown $dbname
     * @param unknown $taskuuid
     * @return string|fetchAll()
     */
    private function getDBuuidByTask($dbname, $agentuuid, $taskuuid, $instanceName){
        $sql = "select distinct dbt.db_uuid from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.task_uuid = ? and dbt.db_name = ? and dbt.agent_uuid = ? and dbt.instance_name =?";
        $data = $this->dbSelect($sql, array($taskuuid, $dbname, $agentuuid, $instanceName));
        $uuid = "";
        if(!empty($data)){
            $uuid = $data[0]['db_uuid'];
        }

        return $uuid;

    }

    /**
     * 校验数据加密密码正确性
     * @param unknown $params
     * @return string
     */
    public function checkDBEncryptPass($params){
        $password = $params['password'];
        $passFlag = $params['passFlag'];
        $timepointuuid = $params['timepointuuid'];
        $utils = Xphp::instance('Utils');
        $flag = true;
        //校验输入密码
        if($passFlag){
            $sql = "select bbt.detail, dbt.db_name from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
            $data = $this->dbSelect($sql, array($timepointuuid));
            if(!empty($data)){
                $detail = json_decode($data[0]['detail'], true);
                $oldPassword = $detail['password'];
                $password = $utils->ptPassEncrypt(base64_decode($password));
                if($oldPassword != $password){
                    $flag = false;
                }
            }
        }

        $info = array(
            'flag' => $flag
        );
        return json_encode($info);
    }

    /**
     * 搜索数据库时间点
     * @param unknown $params
     */
    public function searchTimepoint($params){
        // NOTE: 时间点的筛选放到js里面处理
        // $search = $params['search'];
        // NOTE: 永久标记点的筛选放在循环里面筛选
        $forever = $params['forever'];
        $nodeuuid = $params['node'];
        $sql = "SELECT bbt.detail, bbt.deleted_flag, bbt.timepoint_uuid, UNIX_TIMESTAMP(bbt.timepoint) AS timepoint,
                    bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, bbt.encrypted_flag,
                    UNIX_TIMESTAMP(bbt.task_create_time) AS task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,
                    bbt.importance_flag, dbt.db_uuid, dbt.db_name, dbt.instance_name, dbt.dir_path, dbt.db_type,
                    dbt.agent_uuid, bsr.node_uuid, bsr.status
                FROM bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr
                WHERE bbt.timepoint_uuid = dbt.timepoint_uuid AND bbt.storage_uuid = bsr.storage_uuid
                    AND bbt.available_flag = ? AND bbt.import_flag = ? AND bbt.module_type = ? AND data_local_flag = ?
                    AND bbt.task_type = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['SET'], $flag['UNSET'], Xphp::$_config['MODULE_TYPE']['DB'], $flag['SET'], Xphp::$_config['TASKTYPE']['DB_BACKUP']);
        if(!empty($nodeuuid)) {
            $sql .= " and bsr.node_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        //租户管理员特殊处理数据显示
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if(!empty($_SESSION['tenantuuid'])){
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if($tenantMangerFlag && $allManageFlag){
                $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('".$userListDes."')";
            }else{
                // 权限重构
                $authUser = $_SESSION['authUser']['db_protect_look'] ?? [];
                if ($authUser) {
                    $userUuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $userUuids = "('" . implode("','", $userUuidArr) . "')";
                    $sql .= " and bbt.user_uuid IN $userUuids ";
                } else {
                    $sql .= " and bbt.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                }
            }
        }
        if (!$_SESSION['tenantuuid']) {  // 非租户用户不能查看租户的资源
            $userUuidData = $this->dbSelect("SELECT user_uuid FROM mt_user_tenant");
            if (is_array($userUuidData) && $userUuidData) {
                $userUuidList = array_column($userUuidData, 'user_uuid');
                $userUuidList = array_values(array_unique($userUuidList));
                $userUuids = "'" . implode("', '", $userUuidList) . "'";
                $sql .= " AND bbt.user_uuid NOT IN ($userUuids) ";
            }
        }
        $sql .= " order by dbt.db_uuid, bbt.timepoint ";
        $pointData = $this->dbSelect($sql, $sqlParams);
        // NOTE: 接口只有<永久标记点>的筛选, 永久标记点只有全量备份有, 因此不用通过增量、差异、日志、归档日志备份去反查询全量备份
        $timepoint = array();
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $allDbType = Xphp::$_config['DB_TYPE'];
        $fullTimepointUuids = [];
        foreach ($pointData as $point){
            $taskuuid = $point['task_uuid'];
            $dbType = $point['db_type'];
            $dbuuid = $point['db_uuid'];
            $agentuuid = $point['agent_uuid'];
            $timepointuuid = $point['timepoint_uuid'];
            $instancename = $point['instance_name'];
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
            $config = json_decode($point['detail'], true);

            $mark = $vmHandler->pGetTimepointMark(false,false,false,$utils->parseFlagToBool($point['importance_flag']));
            $pId = $dbType. $agentuuid. $dbuuid . $taskuuid;
            //sqlserver多显示一层
            if($dbType == $allDbType['SQLSERVER'] || $dbType == $allDbType['SAPHANA']){
                $pId = $dbType.$agentuuid. $instancename. $dbuuid .$taskuuid;
            }
            //检查并添加完备点

            //备份数据显示备注
            // if(!empty($point['remarks'])){
            //     $remark = '<a id="remark_'.$timepointuuid.'" style="display:inline-block;color: #5b9bd1;position: relative;top:5px;left:-4px;" class="popovers remarktips" data-container="body" data-trigger="hover"
            //                 data-placement="right" data-content="'.$point['remarks'].'"><i class="fa fa-info-circle fa-lg" style="font-size: 21px !important; position:relative;top:-4px;left:-4px;"></i></a>';
            // }else{
            //     $remark = "";
            // }
            $name = $this->parseDate($point['timepoint']) . " (" . $this->getTimepointTypeDes($point['backup_mode'], $point['db_type']) . ")";
            if(intval($point['encrypted_flag']) == Xphp::$_config['FLAG']['SET']){
                $name .= '<i class="fa fa-lock"></i>';
            }
            if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                if ($forever && $point['importance_flag'] != Xphp::$_config['FLAG']['SET']) { // 判断是否为永久标记点
                    continue;
                }
                $fullTimepointUuids[] = $timepointuuid;
                if(!in_array($timepointuuid, $timepoint)){
                    $timepoint[] =  $timepointuuid;
                    $node[] = array(
                        "id" =>  $timepointuuid,
                        "pId" =>  $pId,
                        "name" => $name." ".$mark,
                        "oldname" => $name,
                        "type" => 3,
                        "dbuuid" => $point['db_uuid'],
                        "dbname" => $point['db_name'],
                        "dbtype" => $point['db_type'],
                        "pointname" => $this->parseDate($point['timepoint']),
                        "agentuuid" => $point['agent_uuid'],
                        "timepointuuid" => $timepointuuid,
                        "createtime" => $taskCreateTimeIn,
                        "nodeuuid" => $point['node_uuid'],
                        "taskuuid" => $taskuuid,
                        "dir_path" => $point['dir_path'],
                        "pointtype" => intval($point['backup_mode']),
                        "icon" => $this->getTimepointIcon($point['backup_mode']),
                        "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                        "encryptedflag" => $utils->parseFlagToBool(intval($point['encrypted_flag'])) && ($config['password_auto_flag'] == 2),
                        'config' => $config,
                        'eventtype' => $this->getTimepointEventType($point['backup_mode']),
                    );
                    continue;
                }
            }
            $pid = $this->getFullTimePointByTimePoint($point['timepoint_uuid'], $pointData);
            if (!in_array($pid, $fullTimepointUuids)) {
                // 表示这个备份类别的时间没有完备点, 直接跳过
                continue;
            }

            $chkDisabled = true;
            if ($allDbType['MONGODB'] == $dbType) {
                $chkDisabled = false;
            }
            $node[] = array(
                "id" => $timepointuuid,
                "pId" => $pid,
                "name" => $name." ".$mark,
                "oldname" => $name,
                "type" => 4,
                "dbuuid" => $point['db_uuid'],
                "instanceuuid" => $point['instance_name'],
                "dbname" => $point['db_name'],
                "pointname" => $this->parseDate($point['timepoint']),
                "agentuuid" => $point['agent_uuid'],
                "nodeuuid" => $point['node_uuid'],
                "timepointuuid" => $timepointuuid,
                "dbtype" => $point['db_type'],
                "dir_path" => $point['dir_path'],
                "icon" => $this->getTimepointIcon($point['backup_mode']),
                "title" => $this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                "pointtype" => intval($point['backup_mode']),
                "encryptedflag" => $utils->parseFlagToBool(intval($point['encrypted_flag'])) && ($config['password_auto_flag'] == 2),
                "config" => $config,
                'chkDisabled' => $chkDisabled,
                'eventtype' => $this->getTimepointEventType($point['backup_mode']),
            );

        }
        return json_encode($node);
    }

    /**
     * 获取备份点的完备点
     * @param string $timePointUuid 备份点uuid
     * @param array  $allData       所有数据
     * @return string
     */
    private function getFullTimePointByTimePoint($timePointUuid, $allData)
    {
        $fullBackupMode = Xphp::$_config['BACKUP_MODE']['FULL'];

        // 用索引构建比较快
        $dependentUuidList = [
            $timePointUuid => 1,
        ];
        $fullTimePointUuid = null;
        $loopFlag = true;
        while ($loopFlag) {
            $loopFlag = false;
            foreach ($allData as $point) {
                if (
                    isset($dependentUuidList[$point['timepoint_uuid']]) &&  // 这个备份点被需要查询的点依赖
                    !isset($dependentUuidList[$point['depend_point_uuid']]) // 这个备份点的以来点还没有加入缓存中
                ) {
                    if ($point['backup_mode'] == $fullBackupMode) {
                        $fullTimePointUuid = $point['timepoint_uuid'];
                        break;
                    }
                    $loopFlag = true;
                    $dependentUuidList[$point['depend_point_uuid']] = 1;
                }
            }
            if (!is_null($fullTimePointUuid)) {
                break;
            }
        }
        if (is_null($fullTimePointUuid)) {
            return $timePointUuid;
        }
        return $fullTimePointUuid;
    }

    /**
     * 得到数据库备份代理端树
     * @param unknown $params
     * @return string
     */
    public function getDBClusterAgentTree($params){
        $dbType = $params['dbType'];
        $clusteruuid = $params['clusteruuid'];
        $authFlag = $params['authFlag'];  //实例认证入口
        $editFlag = $params['editFlag'];  //修改入口
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];  //实例认证主机UUID
        if($editFlag){
            $editAgentuuid = $this->getAgentuuidByTask($taskuuid);
        }
        //如果是授权认证页面，获取数据库集群关联列表
        if($authFlag){
            $clusterList = $this->getClusterList($clusteruuid);
        }


        $tree = array();
        $sql = "select  baa.app_listen_ip, bag.group_name, ba.group_uuid, ba.id, ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.authorization_module, ba.online_flag
                from bd_agent_group bag, bd_agent ba left join bd_agent_app baa on ba.agent_uuid = baa.agent_uuid
                where ba.group_uuid = bag.group_uuid  and ba.agent_type not in (3, 4) and ba.online_flag = ? ";
        $clientHandler = Xphp::instance('ClientHandler');
        $agentUuidArr = $clientHandler->getClientUuids();
        if (!$agentUuidArr) {
            $sql .= " AND ba.agent_uuid = '' ";
        } else {
            if (is_array($agentUuidArr)) {
                $agentUuids = "'" . implode("', '", $agentUuidArr) . "'";
                $sql .= " AND ba.agent_uuid IN ($agentUuids) ";
            }
        }
        $sqlParams = array(Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $systemHandler = Xphp::instance('SystemHandler');
        $agentHandler = Xphp::instance('AgentHandler');
        $group = array();
        $agent = array();
        $node = array();

        foreach ($data as $d){
            if($authFlag && $agentuuid == $d['agent_uuid']) continue;
            //添加代理分组
            if(!in_array($d['group_uuid'], $group)){
                $group[] = $d['group_uuid'];
                $node[] = array(
                    "id" => $d['group_uuid'],
                    "pId" => 0,
                    "name" => $d['group_name'],
                    "title" => $d['group_name'],
                    "open" => true,
                    "nocheck" => false,
                    "type" => 0,
                    "icon" => "./img/platform/flag.png",
                    "groupuuid" => $d['group_uuid'],
                    "eventtype" => "group",
                );

            }

            //添加客户端
            if(!in_array($d['agent_uuid'], $agent)){

                $clusterFlag = false;
                $ip = $d['ip'];



                if($authFlag && in_array($d['agent_uuid'], $clusterList['cluster_list'])){
                    $clusterFlag = true;
                    $ip = $d['app_listen_ip'];

                }
                $agent[] = $d['agent_uuid'];
                $node[] = array(
                    "id" => $d['agent_uuid'],
                    "pId" => $d['group_uuid'],
                    "name" => $d['agent_name'] == $d['ip'] ? $d['ip']."(". $d['hostname'] .")" : $d['ip']."(". $d['agent_name'] .")",
                    "title" => $d['ip'],
                    "isParent" => false,
                    "agentuuid" => $d['agent_uuid'],
                    "groupuuid" => $d['group_uuid'],
                    "nocheck" => false,
                    "type" => 1,
                    "icon" => "./img/vm/host.png",
                    "module" => $agentHandler->getAgentModule($d['authorization_module']),
                    "checked" => $clusterFlag,
                    "chkDisabled" => $clusterFlag,
                    "eventtype" => "agent",
                    "ip" => $ip
                );

            }
        }

        return json_encode($node);
    }

    /**
     * 获取已授权数据库类型
     * @param unknown $agentuuid
     * @return array|fetchAll()
     */
    public function getBackupTypeList($params){
        $sql = "select distinct baa.app_type as db_type
                from bd_agent_app baa, bd_agent ba
                where ba.agent_type != 4 and ba.agent_uuid = baa.agent_uuid
                    and ba.online_flag = ? ";
        $clientHandler = Xphp::instance('ClientHandler');
        $allDbTypeDes = Xphp::$_config['DB_TYPE_DES'];
        unset($allDbTypeDes[0]);  // 去掉UNKNOWN
        unset($allDbTypeDes[1000]);  // 去掉Exchange Server

        $agentUuidArr = $clientHandler->getClientUuids();
        if (!$agentUuidArr) {
            $sql .= " AND ba.agent_uuid = '' ";
        } else {
            if (is_array($agentUuidArr)) {
                $agentUuids = "'" . implode("', '", $agentUuidArr) . "'";
                $sql .= " AND ba.agent_uuid IN ($agentUuids) ";
            }
        }
        $sql .= " ORDER BY app_type ASC ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
        $allowDbTypeList = [];
        if (is_array($data)) {
            $allowDbTypeList = array_column($data, 'db_type');
        }
        $list = array();
        foreach ($allDbTypeDes as $dbType => $dbTypeDes) {
            if (!in_array($dbType, $allowDbTypeList)) {
                continue;
            }
            $list[] = [
                'text' => $dbTypeDes,
                'value' => $dbType,
            ];
        }

        return json_encode($list);
    }

    /**
     * 得到数据库备份树节点标题
     * @param string $name      节点名字
     * @param string $dbname      数据库名
     * @param string $agentuuid      客户端唯一标识
     * @param string $backupDBInfo     数据库备份信息
     * @param string $backupFlag      备份标记
     * @return string
     */
    public function getBackupTreeNodeTitle($name, $dbname, $agentuuid, $backupDBInfo, $backupFlag){
        if(!$backupFlag){
            return $name;
        }
        //在任务中的虚拟机
        foreach ($backupDBInfo as $key => $db){

            if($dbname == $db['dbname'] && $agentuuid == $db['agentuuid']){
                return $name . "('" . $db['taskname'] . "'". Xphp::$_lang['WEB_BACKUP_TASK_PROTECTED'] . ")";
            }
        }
        return $name;
    }

    /**
     * 得到数据库备份树节点标题(集群)
     * @param $taskInfo
     * @param $dbBackupInfo
     * @param $backupFlag
     * @return string
     */
    private function getClusterBackupTreeNodeTitle($taskInfo, $dbBackupInfo, $backupFlag)
    {
        if (!$backupFlag || !$dbBackupInfo) {
            return $taskInfo['name'];
        }
        $clusterAgentUuidList = $this->getClusterAgentUuid($taskInfo['agent_uuid'], $taskInfo['db_type'], $dbBackupInfo);
        foreach ($dbBackupInfo as $db) {
            if ($db['dbtype'] != $taskInfo['db_type']) {
                continue;
            }
            foreach ($clusterAgentUuidList as $clusterAgentUuid) {
                if ($taskInfo['cluster_flag'] && $db['cluster_uuid'] && $db['cluster_uuid'] == $taskInfo['cluster_uuid']) {
                    return $taskInfo['name'] . "('" . $db['taskname'] . "'". Xphp::$_lang['WEB_BACKUP_TASK_PROTECTED'] . ")";
                } elseif (
                    $taskInfo['instance_uuid'] == $db['instancename'] &&
                    $taskInfo['dbname'] == $db['dbname'] &&
                    $db['agentuuid'] == $clusterAgentUuid
                ) {
                    return $taskInfo['name'] . "('" . $db['taskname'] . "'". Xphp::$_lang['WEB_BACKUP_TASK_PROTECTED'] . ")";
                }
            }
        }
        return $taskInfo['name'];
    }

    /**
     * 获取到客户端所在集群的所有客户端uuid
     * @param $agentUuid
     * @param $dbType
     * @param $dbBackupInfo
     * @return array
     */
    private function getClusterAgentUuid($agentUuid, $dbType, $dbBackupInfo): array
    {
        foreach ($dbBackupInfo as $db) {
            if ($db['dbtype'] != $dbType) {
                continue;
            }
            if ($db['cluster_uuid']) { // 集群
                if (in_array($agentUuid, $db['cluster_agent_uuid'])) {  // 有集群
                    return $db['cluster_agent_uuid'];
                }
            } elseif ($agentUuid == $db['agentuuid']) { // 单机
                return [$agentUuid];
            }
        }
        return [];
    }

    /**
     * 获取所有数据库备份信息(集群环境)
     * @return array
     */
    public function getDbBackupInfo()
    {
        $setFlag = Xphp::$_config['FLAG'];
        $sql = "SELECT bt.task_name, bt.task_uuid, dt.db_type, dl.instance_name,
                    dl.db_uuid, dl.db_name, dl.agent_uuid
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                    INNER JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = dl.agent_uuid AND baa.app_name = dl.instance_name
                WHERE bt.task_type = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['DB_BACKUP']));
        $list = array();
        foreach ($data as $d){
            // 查询集群信息
            $sql = "SELECT baa1.agent_uuid, baa1.cluster_uuid 
                FROM bd_agent_app baa
                    LEFT JOIN bd_agent_app baa1 ON baa.cluster_uuid = baa1.cluster_uuid 
                WHERE baa.agent_uuid = ? AND baa.app_name = ? AND baa.cluster_flag = ? AND baa1.cluster_flag = ?
                    AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != ''
                    AND baa1.cluster_uuid IS NOT NULL AND baa1.cluster_uuid != '' ";
            $sqlParams = [$d['agent_uuid'], $d['instance_name'], $setFlag['SET'], $setFlag['SET']];
            $cluster = $this->dbSelect($sql, $sqlParams);
            $clusterAgentUuid = [];
            $clusterUuid = '';
            if (is_array($cluster) && $cluster) {
                foreach ($cluster as $agent) {
                    $clusterAgentUuid[] = $agent['agent_uuid'];
                    if ($d['cluster_uuid']) {
                        $clusterUuid = $agent['cluster_uuid'];
                    } elseif ($agent['agent_uuid'] == $d['agent_uuid']) {
                        $clusterUuid = $agent['cluster_uuid'];
                    }
                }
            }
            $list[] = array(
                'dbuuid' => $d['db_uuid'],
                'dbname' => $d['db_name'],
                'instancename' => $d['instance_name'],
                'agentuuid' => $clusterUuid ? $clusterAgentUuid[0] : $d['agent_uuid'],
                'taskuuid' => $d['task_uuid'],
                'taskname' => $d['task_name'],
                'dbtype' => intval($d['db_type']),
                'cluster_uuid' => $clusterUuid,
                'cluster_agent_uuid' => $clusterAgentUuid,
            );
        }

        return $list;
    }

    /**
     * 判断客户端集群是否在备份任务中
     * @param $taskInfo
     * @param $dbBackupInfo
     * @return bool
     */
    private function judgeInClusterBackup($taskInfo, $dbBackupInfo): bool
    {
        if (!$dbBackupInfo) {
            return false;
        }
        $clusterAgentUuidList = $this->getClusterAgentUuid($taskInfo['agent_uuid'], $taskInfo['db_type'], $dbBackupInfo);
        foreach ($dbBackupInfo as $db) {
            if ($db['dbtype'] != $taskInfo['db_type']) {
                continue;
            }
            foreach ($clusterAgentUuidList as $clusterAgentUuid) {
                // DM 集群的实例名不一致
                if ($taskInfo['cluster_flag'] && $db['cluster_uuid'] && $db['cluster_uuid'] == $taskInfo['cluster_uuid']) {
                    return true;
                } elseif (
                    $taskInfo['instance_uuid'] == $db['instancename'] &&
                    $taskInfo['dbname'] == $db['dbname'] &&
                    $db['agentuuid'] == $clusterAgentUuid
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 根据任务获取客户端的集群备份的实例信息
     * @param array $taskInfo 任务信息
     * @param array $dbBackupInfo 当前数据库的所有备份任务信息
     * @return array
     */
    private function getBackupClusterInstance(array $taskInfo, array $dbBackupInfo): array
    {
        $clusterInstanceList = [];
        if (!$dbBackupInfo) {
            return $clusterInstanceList;
        }
        $clusterAgentUuidList = $this->getClusterAgentUuid($taskInfo['agent_uuid'], $taskInfo['db_type'], $dbBackupInfo);
        foreach ($dbBackupInfo as $db) {
            if ($db['dbtype'] != $taskInfo['db_type']) {
                continue;
            }
            foreach ($clusterAgentUuidList as $clusterAgentUuid) {
                if ($db['agentuuid'] == $clusterAgentUuid && $db['taskuuid'] == $taskInfo['task_uuid']) {
                    $clusterInstanceList[$db['instancename']] = $db['dbuuid'];
                }
            }
        }
        return $clusterInstanceList;
    }

    /**
     * 获取客户端在备份任务的集群中的数据库列表
     * @param $taskInfo
     * @param $dbBackupInfo
     * @return array
     */
    private function getClusterDBBackupList($taskInfo, $dbBackupInfo): array
    {
        $clusterDbTaskList = [];
        if (!$dbBackupInfo) {
            return $clusterDbTaskList;
        }
        $clusterAgentUuidList = $this->getClusterAgentUuid($taskInfo['agent_uuid'], $taskInfo['db_type'], $dbBackupInfo);
        foreach ($dbBackupInfo as $db) {
            if ($db['dbtype'] != $taskInfo['db_type']) {
                continue;
            }
            foreach ($clusterAgentUuidList as $clusterAgentUuid) {
                if (
                    $db['agentuuid'] == $clusterAgentUuid &&
                    $db['taskuuid'] == $taskInfo['task_uuid'] &&
                    $db['instancename'] == $taskInfo['instance_uuid']
                ) {
                    $clusterDbTaskList[] = $db['dbname'];
                }
            }
        }
        return $clusterDbTaskList;
    }

    /**
     * 检查时间点是否正在被使用
     * @param array $pointList
     * @param string $operate
     * @param int $dbType
     */
    private function checkTaskExist($pointList, $operate, $dbType)
    {
        /**
         * 1、恢复任务无论是什么状态，使用的备份链都不能删除
         * 2、备份任务处于运行、异常状态，该备份任务的全部链都不能删除
         * 3、备份任务处于等待状态，最近一条备份链不能删除
         * 4、当这个备份任务有副本任务的时候，触发保留策略时会检查这个副本任务是不是处于停止状态
         *      1) 如果副本任务在停止状态那就不管
         *      2) 如果不是停止状态，那进一步检查，副本任务拷贝过现在触发保留策略的这条链没有
         *          1> 如果没有拷贝过，那就不能删除这条链
         *          2> 如果拷贝过，那就可以删除
         * 5、Oracle数据库不能删除最后一个备份链
         * 6、处于磁带上的备份点不能删除(tape = 10)
         *
         * 名词解释
         * 1、备份链: 完备点+所有依赖他的其他点所组成的一条链子
         */
        // 1.判断时间点点是否在恢复任务中
        $allTaskType = Xphp::$_config['TASKTYPE'];
        $allTaskStatus = Xphp::$_config['TASKSTATUS'];
        $allStorageType = Xphp::$_config['BD_STORAGE_TYPE'];
        $timepointuuids = implode("','", $pointList);
        $sql1 = "select task_uuid from db_list where timepoint_uuid in ('$timepointuuids')";
        $data1  =$this->dbSelect($sql1);

        if(!empty($data1)){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_DB_DELETE_TIMEPOINT_IN_RECOVERY_WARNING'],'warning'));
        }

        // 6. 处于磁带上的备份点不能删除(tape = 10)
        $sql = "SELECT *
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.timepoint_uuid IN ('$timepointuuids') AND bsr.storage_type = ? ";
        $tapePointData = $this->dbSelect($sql, [$allStorageType['TAPE']]);
        if (is_array($tapePointData) && $tapePointData) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_TAPE_DELETE_BACKUP_POINT_TIPS'],'warning'));
        }
        /**
         * 验证权限
         */
        $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
        $this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'));

        // 获取到这些备份点任务的所有运行状态
        $sql = "SELECT bt.task_name, bt.task_status, bt.task_uuid, bbt.timepoint_uuid
                FROM bd_task bt
                  INNER JOIN bd_backup_timepoint bbt ON bbt.task_uuid = bt.task_uuid
                WHERE bbt.timepoint_uuid IN ('$timepointuuids')
                  AND bt.task_type = {$allTaskType['DB_BACKUP']}";
        $taskList = $this->dbSelect($sql);
        $backupTaskList = [];  // 备份任务的uuid列表
        $backupTimepointMap = [];
        if (is_array($taskList) && $taskList) {  // 备份任务判断
            $backupChainList = [];
            $backupTaskList = array_unique(array_column($taskList, 'task_uuid'));
            foreach ($taskList as $taskInfo) {
                if (!isset($backupTimepointMap[$taskInfo['task_uuid']])) {
                    $backupTimepointMap[$taskInfo['task_uuid']] = [];
                }
                $backupTimepointMap[$taskInfo['task_uuid']][] = $taskInfo['timepoint_uuid'];
                // 备份任务处于运行、异常状态，该备份任务的全部链都不能删除
                if (
                    $taskInfo['task_status'] == $allTaskStatus['RUNNING'] ||
                    $taskInfo['task_status'] == $allTaskStatus['NETWORK_FAULT'] ||
                    $taskInfo['task_status'] == $allTaskStatus['ABNORMAL'] ||
                    $taskInfo['task_status'] == $allTaskStatus['ERROR'] ||
                    $taskInfo['task_status'] == $allTaskStatus['STOPPING'] ||
                    $taskInfo['task_status'] == $allTaskStatus['STARTING']
                ) {
                    exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_DB_DELETE_TIMEPOINT_IN_BACKUP_TASK_WARNING'],'warning'));
                }
                // 备份任务处于等待，最近一条备份链不能删除
                if ($taskInfo['task_status'] == $allTaskStatus['WAITTING']) {
                    if (!isset($backupChainList[$taskInfo['task_uuid']])) {
                        $backupChainList[$taskInfo['task_uuid']] = $this->getLastBackupChain($taskInfo['task_uuid']);
                    }
                    $backupChain = $backupChainList[$taskInfo['task_uuid']];
                    foreach ($pointList as $timePointUuid) {
                        if (isset($backupChain[$timePointUuid])) {  // 标识备份点在最后的备份连上，不能删除
                            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_DB_DELETE_TIMEPOINT_IN_BACKUP_TASK_WARNING'],'warning'));
                        }
                    }
                }
                // Oracle的最后一条备份链不论什么情况都不能删除 bug#10607
                if ($dbType == Xphp::$_config['DB_TYPE']['ORACLE']) {
                    if (!isset($backupChainList[$taskInfo['task_uuid']])) {
                        $backupChainList[$taskInfo['task_uuid']] = $this->getLastBackupChain($taskInfo['task_uuid']);
                    }
                    $backupChain = $backupChainList[$taskInfo['task_uuid']];
                    foreach ($pointList as $timePointUuid) {
                        if (isset($backupChain[$timePointUuid])) {  // 标识备份点在最后的备份连上，不能删除
                            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_DB_DELETE_TIMEPOINT_ORACLE_NEWEST_BACKUP_CHAIN'],'warning'));
                        }
                    }
                }
            }
        }

        // 数据库副本任务查询
        if ($backupTaskList) {
            $backupTaskDes = implode("','", $backupTaskList);
            // 查询副本任务
            $sql = "SELECT bt.task_uuid, bt.task_status, cl.source_task_uuid
                    FROM copy_list cl INNER JOIN bd_task bt ON cl.task_uuid=bt.task_uuid
                    WHERE source_task_uuid IN ('$backupTaskDes')";
            $copyTaskList = $this->dbSelect($sql);
            if (is_array($copyTaskList) && $copyTaskList) {
                // 查询副本备份点
                $copyTaskUuids = "'" . implode("','", array_map(function ($item) {
                        return $item['task_uuid'];
                    }, $copyTaskList)) . "'";
                $sql = "SELECT task_uuid, src_timepoint_uuid FROM bd_backup_timepoint WHERE copy_flag = ? AND task_uuid IN ($copyTaskUuids)";
                $copyTimepointList = $this->dbSelect($sql, [Xphp::$_config['FLAG']['SET']]);
                $copyTimepointMap = [];
                if ($copyTimepointList && is_array($copyTimepointList)) {
                    foreach ($copyTimepointList as $copyTimepoint) {
                        if (!isset($copyTimepointMap[$copyTimepoint['task_uuid']])) {
                            $copyTimepointMap[$copyTimepoint['task_uuid']] = [];
                        }
                        $copyTimepointMap[$copyTimepoint['task_uuid']][] = $copyTimepoint['src_timepoint_uuid'];
                    }
                }
                foreach ($copyTaskList as $copyTaskInfo) {
                    if ($copyTaskInfo['task_status'] == $allTaskStatus['STOPPED']) {
                        continue;
                    }
                    $diff = array_diff($backupTimepointMap[$copyTaskInfo['source_task_uuid']], $copyTimepointMap[$copyTaskInfo['task_uuid']] ?? []);
                    if (count($diff)) {
                        exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_DB_DELETE_TIMEPOINT_IN_COPY_TASK_WARNING'],'warning'));
                    }
                }
            }
        }
    }

    /**
     * 获取备份任务最后一条备份链
     * @param string $taskUuid
     * @return array
     */
    private function getLastBackupChain(string $taskUuid): array
    {
        $backupChain = [];
        $allBackupMode = Xphp::$_config['BACKUP_MODE'];
        // 这里采用公用表达式进行查询，方便递归构建备份链
        /**
         * 1. 初始条件: 查询这个任务的最后一个完备点
         * 2. 递归条件: depend_point_uuid等于完备点的uuid
         * 3. 查询timepoint_uuid, task_uuid, task_name
         */
        $tableName = 'temp_table_' . time();  // 避免表名重复
        $sql = "WITH RECURSIVE $tableName AS (
	                (SELECT * FROM bd_backup_timepoint
	                    WHERE task_uuid = '$taskUuid' AND backup_mode = {$allBackupMode['FULL']} ORDER BY timepoint DESC LIMIT 1)
		            UNION ALL
	                SELECT bbt.* FROM bd_backup_timepoint bbt, $tableName WHERE bbt.depend_point_uuid = $tableName.timepoint_uuid 
	            )
	            SELECT timepoint_uuid, task_uuid, task_name FROM $tableName;";
        $data = $this->dbSelect($sql);
        if (!is_array($data) || !$data) {
            return $backupChain;
        }
        foreach ($data as $row) {
            $backupChain[$row['timepoint_uuid']] = [
                'task_uuid' => $row['task_uuid'],
                'task_name' => $row['task_name'],
                'timepoint_uuid' => $row['timepoint_uuid'],
            ];
        }
        return $backupChain;
    }

    /**
     * 检查是否拥有主机保护的操作权限
     * @param array $userIdList 操作资源的拥有者uuid列表
     * @return void
     */
    private function checkHostOperatePermission(array $userIdList)
    {
        // 关联管理用户判断 数据库保护 - 操作 db_protect_operate
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['db_protect_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], $userIdList, $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }
    }

    /**公共函数
     * 获取是否为集群标志
     * @param string $instanceuuid
     * @param string $agentuuid
     */
    private function pGetClientClusterFlag($instanceuuid, $agentuuid){
        $sql = "select cluster_flag, cluster_uuid from bd_agent_app where app_name = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($instanceuuid, $agentuuid));

        return intval($data[0]['cluster_flag']) == Xphp::$_config['FLAG']['SET'] && $data[0]['cluster_uuid'] ? true : false;
    }

    private function getFileContentByFilePath($timepoint, $fileFormat)
    {
        $pfilePath = sprintf($fileFormat, $timepoint['storage_uuid'], $timepoint['timepoint_uuid']);
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = [
            'file_path' => $pfilePath,
        ];
        $mbResult = $this->mbNodeMsg($opName, $timepoint['node_uuid'], json_encode($msg), true);
        if ($mbResult['result']) {
            return str_replace("\n", "\n", $mbResult['msg']['file_content']);
        }
        return '';
    }

    /**
     * 获取PFile文件内容
     * @param $params
     * @return string
     */
    public function getCustomConfigContent($params): string
    {
        $sql = "SELECT bbt.storage_uuid, bbt.timepoint_uuid, bsr.node_uuid, dbt.db_config
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid=bsr.storage_uuid
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid=dbt.timepoint_uuid
                WHERE bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['timepoint_uuid']]);
        $dbConfig = json_decode($data[0]['db_config'], true);
        return $this->muOpResult(true, '', '', '', '',[
            'pfile' => $this->getFileContentByFilePath($data[0], Xphp::$_config['ORACLE_SPFILE_PATH']),
            'listener' => $this->getFileContentByFilePath($data[0], Xphp::$_config['ORACLE_LISTENER_PATH']),
            'listener_path' => $dbConfig['config_file_path']['listener_file_path'] ?? '',
            'tnsnames' => $this->getFileContentByFilePath($data[0], Xphp::$_config['ORACLE_TNSNAMES_PATH']),
            'tnsnames_path' => $dbConfig['config_file_path']['tnsnames_file_path'] ?? '',
            'sqlnet' => $this->getFileContentByFilePath($data[0], Xphp::$_config['ORACLE_SQLNET_PATH']),
            'sqlnet_path' => $dbConfig['config_file_path']['sqlnet_file_path'] ?? '',
            'password_path' => $dbConfig['config_file_path']['password_file_path'] ?? '',
        ]);
    }

    /**
     * 获取数据库恢复任务的数据库配置
     * @param $params
     * @return void
     */
    public function getRecoveryDbConfig($params)
    {
        $taskUuid = $params['task_uuid'];
        $sql = "SELECT dl.instance_name, dl.db_name, dl.timepoint_uuid,
                    dl.recovery_mode, dl.new_db_name, dl.data_file_path, dl.log_file_path,
                    dl.log_rollback_time, dl.detail, dt.db_type,
                    dl.recovery_time as dl_recovery_time, dl.initialize_log_area,
                    dt.recovery_time_flag
                FROM db_list dl
                    INNER JOIN db_task dt ON dt.task_uuid = dl.task_uuid
                WHERE dl.task_uuid = ? ";
        $data = $this->dbSelect($sql, [$taskUuid]);
        $nullSpace = Xphp::$_config['NULLSPACE'];
        $records = ['data' => [], 'draw' => $params['draw']];
        $utils = Xphp::instance('Utils');
        foreach ($data as $row) {
            $isRollback = false;
            if ($row['log_rollback_time'] != '0000-00-00 00:00:00') {
                $isRollback = true;
            }
            $isCreate = false;
            if (Xphp::$_config['DB_RECOVERY_TYPE']['CREATE'] == $row['recovery_mode']) {
                $isCreate = true;
            } elseif (Xphp::$_config['DB_RECOVERY_TYPE']['SPECIFY_DATABASE'] == $row['recovery_mode']) {
                $isCreate = true;
            }
            $recoveryTime = $nullSpace;
            if (
                $row['db_type'] == Xphp::$_config['DB_TYPE']['SAPHANA'] &&
                $row['recovery_time_flag'] != Xphp::$_dbdes['SAPHANA_RECOVERY_TIME']['timepoint']
            ) {
                $recoveryTime = $row['dl_recovery_time'];
                if ($row['recovery_time_flag'] == Xphp::$_dbdes['SAPHANA_RECOVERY_TIME']['newest']) {
                    if ($recoveryTime != '0000-00-00 00:00:00') {
                        $datetime = new Datetime($recoveryTime);
                        $datetime->sub(new DateInterval('P1Y'));
                        $recoveryTime = $datetime->format('Y-m-d H:i:s');
                    } else {
                        $recoveryTime = $nullSpace;
                    }
                }
            }
            $records['data'][] = [
                $row['db_name'],
                $isCreate ? $row['new_db_name'] : $nullSpace,
                $isCreate ? $row['data_file_path'] : $nullSpace,
                $isCreate ? $row['log_file_path'] : $nullSpace,
                $isRollback ? $row['log_rollback_time'] : $nullSpace,
                $recoveryTime,
                $utils->parseFlagToBool($row['initialize_log_area']) ? Xphp::$_lang['UI_PUBLIC_ON_TWO'] : Xphp::$_lang['UI_PUBLIC_OFF_TWO'],
            ];
        }
        return json_encode($records);
    }

    /**
     * 获取备份数据的表空间信息 - InterSystems Caché/IRIS使用
     * @param array $params
     * @return string
     */
    public function getBackupTableSpace(array $params): string
    {
        $timePointUuid = $params['timepoint_uuid'];
        $dbType = (int) $params['db_type'];
        $utils = Xphp::instance('Utils');
        // FIXME: 获取Cache/IRIS的备份点表空间
        $sql = "SELECT bbt.encrypted_flag, bbt.timepoint, bbt.timepoint_uuid, bbt.detail
                FROM bd_backup_timepoint bbt
                WHERE bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$timePointUuid]);
        $encryptFlag = $utils->parseFlagToBool($data[0]['encrypted_flag']);
        $encryptContent = '';
        if ($encryptFlag) {
            $encryptContent = '<i class="fa fa-lock"></i>';
        }
        $detail = json_decode($data[0]['detail'], true);
        $tableSpaceList = $detail['table_space_list'] ?? [];
        $ret = [];
        foreach ($tableSpaceList as $index => $tableSpace) {
            $ret[] = [
                'id' => $tableSpace['table_uuid'],
                'name' => $tableSpace['table_name'],
                'show_name' => $tableSpace['table_name'] . $encryptContent,
                'path' => $tableSpace['table_path'],
                'icon' => './img/platform/storage.png',
                'db_type' => $dbType,
            ];
        }
        return json_encode($ret);
    }

    /**
     * 获取客户端的数据库列表
     * @param array $params
     * @return string
     */
    public function getAgentDbList(array $params): string
    {
        $instanceInfo = $this->getInstanceInfo($params['instance_name'], $params['agent_uuid']);
        $dbList = $this->scanAgentDbList($instanceInfo);
        return json_encode($dbList);
    }

    /**
     * 获取还原归日志信息
     * @param array $params
     * @return string
     */
    public function getRestoreArchivelogInfo(array $params): string
    {
        $timePointUuid = $params['timepoint_uuid'];
        $sql = "SELECT db_config FROM db_backup_timepoint WHERE timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$timePointUuid]);
        if (!$data || !is_array($data)) {
            return '{}';
        }
        $dbConfig = json_decode($data[0]['db_config'], true);
        if (!isset($dbConfig['archive_log_restore_range'])) {
            return '{}';
        }
        return json_encode($dbConfig['archive_log_restore_range']);
    }

    public function judgeAgentContainEmptyInstance($params)
    {
        $sql = "SELECT app_name FROM bd_agent_app WHERE agent_uuid = ? AND app_type = ? AND app_auth_type = ? ";
        $data = $this->dbSelect($sql, [$params['agent_uuid'], $params['db_type'], $params['auth_type']]);
        if (!$data || !is_array($data)) {
            return json_encode([]);
        }
        return json_encode(array_column($data, 'app_name'));
    }
}