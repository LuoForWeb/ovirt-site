<?php
/******************************************* 
** 文件备份处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-8-07 下午03:20:22 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
require_once XPHP_PATH.'utils/BLLHandler.class.php';
class FileHandler extends BLLHandler{
    
    /**
     * 创建备份任务
     * @param unknown $params
     */
    public function createBackupJob($params){
        //public params
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $module_type = Xphp::$_config['MODULE_TYPE']['FS'];
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo']);
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve']);
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer']);
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store']);
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        $pfMsg = $this->pfCreateBackupTaskMessage($task_name, $module_type,
            $time_strategy_list, $reserver_strategy, $transport_strategy, $storage_strategy, $nodeInfo);
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['BACKUP'];
        //private params
        //bakcup level: disk or file
        $pfMsg['backup_level'] = 1;
        $pfMsg['agent_uuid'] = $params['srcInfo']['agentUUID'];
        $pfMsg['fs_path_list'] = $this->groupBackupFS($params['srcInfo']['fileInfo']);
        
        $msg = json_encode($pfMsg);
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $mbResult = $this->mbFSMsg($nodeInfo['node_uuid'], $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
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
     * 插入时间策略
     * @param int $strategyID 策略ID
     * @param int $modeType
     * @param array $strategyInfo
     */
    private function insertTimeStrategy($strategyID, $modeType, $strategyInfo){
        $utils = Xphp::instance('Utils');
        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']);
        $rollFlag = $utils->parseBoolToFlag($strategyInfo['rollFlag']);
        $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array($strategyID, $modeType, $strategyInfo['type'], $days,
            $utils->formartTime($strategyInfo['startTime']), $rollFlag, $utils->timeToSec($strategyInfo['rollInterval']),
            $utils->formartTime($strategyInfo['endTime'])
        );
    
        return $this->dbQuery($sql, $sqlParams);
    }
    
    /**
     * 获取时间策略天数的字符串表示
     * @param array $days
     */
    private function getTimeStrategyDaysStr($days){
        $str = '';
        if(empty($days)){
            return $str;
        }
        $str = implode('', $days);
        return $str;
    }
    
    /**
     * 修改备份任务
     * @param unknown $params
     * @return string
     */
    public function editBackupJob($params){
        $taskuuid = $params['taskuuid'];
        $taskName = $params['taskName'];
        $this->paramsCheck($taskuuid, $taskName);
        $operate = Xphp::$_lang['UI_BACKUP_FILE_EDIT_TASK'];
        //监测任务状态是否在停止中
        $sql = "select task_status, strategy_id from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskStatus = intval($data[0]['task_status']);
        $strategyID = $data[0]['strategy_id'];
        if($taskStatus != Xphp::$_config['TASKSTATUS']['STOPPED']){
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_JOB_EDIT_TIPS']);
        }
        $nodeInfo = $this->getModifyJobNodeAndStorageInfo($params['highInfo']['node']);
        $this->dbBeginTransaction();
        //更新任务表         bd_task
        if(empty($nodeInfo['storageuuid'])){
            //自动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, auto_find_sr_flag = ?
                 where task_uuid = ?";
            $result = $this->dbExec($sql, array($taskName, date("Y-m-d H:i:s"), $nodeInfo['nodeuuid'],
                $nodeInfo['auto_find_sr_flag'], $taskuuid));
        }else{
            //手动选择存储
            $sql = "update bd_task set task_name = ?, create_time = ?, node_uuid = ?, auto_find_sr_flag = ?,
                storage_uuid = ? where task_uuid = ?";
            $result = $this->dbExec($sql, array($taskName, date("Y-m-d H:i:s"), $nodeInfo['nodeuuid'],
                $nodeInfo['auto_find_sr_flag'], $nodeInfo['storageuuid'], $taskuuid));
        }
        //更新文件列表 fs_path_list
        $sql = "delete from fs_path_list where task_uuid = ?";
        $result = $result && $this->dbExec($sql, array($taskuuid));
        $utils = Xphp::instance('Utils');
        $sql = "insert fs_path_list (task_uuid, path_uuid, path_name, path_type) values (?, ?, ?, ?)";
        $fileInfo = $params['srcInfo']['fileInfo'];
        foreach ($fileInfo as $fs){
            $sqlParams = array($taskuuid, $utils->uuid(), $fs[1], $fs[0]);
            $result = $result && $this->dbQuery($sql, $sqlParams);
        }
        //更新时间策略表 bd_time_strategy
        $sql = "delete from bd_time_strategy where strategy_id = ?";
        $result = $result && $this->dbExec($sql, array($strategyID));
        $timeStrategy = $params['backupInfo'];
        if("oncetime" == $timeStrategy['type']){
            //一次性策略
            $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, start_time)
                    values (?, ?, ?, ?)";
            $sqlParams = array($strategyID, Xphp::$_config['BACKUP_MODE']['FULL'],
                Xphp::$_config['STRATEGY_TYPE']['ONCE'], $timeStrategy['datetime']);
            $result = $result && $this->dbQuery($sql, $sqlParams);
        }elseif("strategy" == $timeStrategy['type']){
            //时间策略
            if(!empty($timeStrategy['fullInfo'])){
                //完全策略
                $modeType = Xphp::$_config['BACKUP_MODE']['FULL'];
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['fullInfo']);
            }
            if(!empty($timeStrategy['incrInfo'])){
                //增量策略
                $modeType = Xphp::$_config['BACKUP_MODE']['INCREMENTAL'];
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['incrInfo']);
            }
            if(!empty($timeStrategy['diffInfo'])){
                //差异策略
                $modeType = Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'];
                $result = $result && $this->insertTimeStrategy($strategyID, $modeType, $timeStrategy['diffInfo']);
            }
        
        }
        
        $highInfo = $params['highInfo'];
        //更新保留策略表 bd_reserved_strategy
        $sql = "update bd_reserved_strategy set strategy_type = ?, number = ? where strategy_id = ?";
        $sqlParams = array($highInfo['reserve']['type'], $highInfo['reserve']['value'], $strategyID);
        $result = $result && $this->dbExec($sql, $sqlParams);
        
        //更新传输策略表 bd_transport_strategy
        $sql = "update bd_transport_strategy set encrypt_flag = ? where strategy_id = ?";
        $sqlParams = array($utils->parseBoolToFlag($highInfo['transfer']['encrypt']), $strategyID);
        $result = $result && $this->dbExec($sql, $sqlParams);
        
        //更新存储策略表 bd_storage_strategy
        $sql = "update bd_storage_strategy set compressed_flag = ?,
                encrypted_flag = ? where strategy_id = ?";
        $sqlParams = array(
            $utils->parseBoolToFlag($highInfo['store']['compress']),
            $utils->parseBoolToFlag($highInfo['store']['encrypt']),
            $strategyID
        );
        $result = $result && $this->dbExec($sql, $sqlParams);
        
        if($result){
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }
        
        return $this->muOpResult($result, $operate);
    }
    
    /**
     * 得到修改任务的节点和存储UUID信息
     * @return array('nodeuuid','auto_find_sr_flag','storageuuid')
     */
    private function getModifyJobNodeAndStorageInfo($nodeInfo){
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
     * 得到备份的文件列表
     * @param unknown $filelists
     */
    private function groupBackupFS($filelists){
        $fileList = array();
        foreach ($filelists as $file){
            $fileList[] = array(
                'path_type' => intval($file[0]),    //文件类型
                'path_name' => $file[1]             //文件路径
            );
        }
        return $fileList;
    }
    
    /**
     * 得到文件备份任务名
     * @param unknown $params
     */
    public function getFileBackupTaskName($params){
        return $this->getValidTaskName(Xphp::$_lang['WEB_FILE_BACKUP_TASKNAME']);
    }
    
    /**
     * 得到文件恢复任务名
     * @param unknown $params
     */
    public function getFileRecoverTaskName($params){
        return $this->getValidTaskName(Xphp::$_lang['WEB_FILE_RECOVER_TASKNAME']);
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
     * 创建恢复任务
     * @param unknown $params
     */
    public function createRecoverJob($params){
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $module_type = Xphp::$_config['MODULE_TYPE']['FS'];
        $recovery_position = intval($params['recoverInfo']['pathtype']);
        $recovery_time_type = intval($params['typeInfo']['type']);
        $time_strategy_list = $this->groupRecoverTimeList($params['typeInfo']);
        $transport_strategy = $this->groupTransportStrategy($params['typeInfo']['high']['trasfer']);
        
        $pfMSg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
            $recovery_time_type, $time_strategy_list, $transport_strategy);
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['RECOVERY'];
        //private params
        //disk or file
        $pfMSg['recovery_level'] = 1;
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverFileList($params['pointInfo'], $params['recoverInfo']);
        
        
        $submodule_type = intval($params['pointInfo']['type']);
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['pointUUID']);
        $msg = json_encode($pfMSg);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
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
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where task_name = ?";
        $data = $this->dbSelect($sql, array($taskName));
        if(!$data) return false;
    
        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'uuid' => $data[0]['task_uuid'],
            'module' => $data[0]['module_type'],
            'subModule' => 0,
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
    private function getRecoverFileList($pointInfo, $recoverInfo){
        $fileInfo = $pointInfo['fileInfo'];
        $files = array();
        foreach($fileInfo as $f){
            $pathtype = intval($recoverInfo['pathtype']);
            $newRootPath = '';
            if($pathtype == Xphp::$_config['FLAG']['UNSET']){
                //异机恢复
                $newRootPath = $recoverInfo['path'];
            }
            $files[] = array(
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
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    private function groupBackupTimeList($params){
        $msg = array();
        if('strategy' == $params['type']){
            //按时间策略备份
            if(!empty($params['fullInfo'])){
                //完全策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['fullInfo']);
            }
            if(!empty($params['incrInfo'])){
                //增量策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['incrInfo']);
            }
            if(!empty($params['diffInfo'])){
                //差异策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'], $params['diffInfo']);
            }
        }else{
            //一次性备份
            $strategy = array('startTime' => $params['datetime']);
            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
            $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $strategy);
        }
    
        return $msg;
    }
    
    /**
     * 组合恢复时间策略
     * @param array $params
     * @return array
     */
    private function groupRecoverTimeList($params){
        $timeList = array();
        if(Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == intval($params['type'])){
            //立即恢复
            return $timeList;
        }elseif(Xphp::$_config['RECOVERY_TIME_TYPE']['STRATEGY'] == intval($params['type'])){
            //按时间策略恢复
            $timeList[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['strategy']);
            return $timeList;
        }
    }
    
    /**
     * 组合每一个时间策略
     * @param int $mode         完全1/增量2/差异3/日志4/标签5
     * @param array $strategy
     * @return array
     */
    private function groupEachTimestrategy($mode, $strategy){
        $this->paramsCheck($mode, $strategy);
        $strArr = array("mode" => $mode);
        if($strategy['globalID']){
            //使用全局策略
            $strArr['global_id'] = $strategy['globalID'];
        }
        //时间策略
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['days'] = implode("", $strategy['days']);
        $strArr['start_time'] = $strategy['startTime'];
        $strArr['roll_flag'] = $strategy['rollFlag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['rollInterval']);
        $strArr['roll_end_time'] = $strategy['endTime'];
        if(Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY'] == intval($strArr['strategy_type'])){
            //每天备份
            $strArr['days'] = "1111111";
        }
    
        if(Xphp::$_config['STRATEGY_TYPE']['ONCE'] == intval($strArr['strategy_type'])){
            //一次性备份
            $strArr['days'] = '';
        }
    
        if($strArr['roll_flag']){
            //滚动备份
            $strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['ON'];
        }else{
            //不滚动
            $strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }
    
    /**
     * 转化滚动间隔为秒
     * @param string $rollInterval
     * @return number
     */
    private function getRollInterval($rollInterval){
        if(empty($rollInterval)){
            return 0;
        }
        $intervalArr = explode(":", $rollInterval);
        $second = intval($intervalArr[0]) * 3600 + intval($intervalArr[1]) * 60 + intval($intervalArr[2]);
        return $second;
    }
    
    /**
     * 组合保留策略
     * @param array $reserve    保留策略信息
     *  @param  int type        类型
     *  @param  int value       值
     *  @param  bool archive    归档标记
     * @return array
     */
    private function groupReserverStrategy($reserve){
        $this->paramsCheck($reserve);
        $strArr = array(
            'strategy_type' => intval($reserve['type']),
            'number' => intval($reserve['value']),
            'auto_archive_flag' => $reserve['archive'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
        );
        return $strArr;
    }
    
    /**
     * 组合传输策略
     * @param array $transport  传输策略信息
     *  @param  bool encrypt    加密
     *  @param  bool compress   压缩
     *  @param  bool speedFlag  限速
     *  @param  int  speed
     * @return array
     */
    private function groupTransportStrategy($transport){
        $strArr = array(
            'encrypt_flag' => $transport['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $transport['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'speed_limit_flag' => $transport['speedFlag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'max_speed' => intval($transport['speed']),
            'compress_method' => $transport['compress_method'] ? $transport['compress_method'] : 0,
            'reconnect_times' => intval($transport['reconnect_times']),
            'reconnect_interval' => intval($transport['reconnect_interval']),
        );
        return $strArr;
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
    private function groupStorageStrategy($storage){
        $strArr = array(
            'deduplication_flag' => $storage['deduplication'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'blocksize' => intval($storage['blocksize']),
            'encrypt_flag' => $storage['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $storage['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_method' => $storage['compress_method'] ? $storage['compress_method'] : 0,
        );
        return $strArr;
    }
    
    /**
     * 得到文件备份代理端树
     * @param unknown $params
     * @return string
     */
    public function getBackupAgentTree($params){
        $tree = array();
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module 
                from bd_agent 
                where online_flag = ? 
                and user_uuid = ?  ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        
        $systemHandler = Xphp::instance('SystemHandler');
        foreach ($data as $d){
            if(!($systemHandler->checkModuleValid($d['authorization_module'], 'file'))){
                continue;
            }
            $node = array(
                "id" => $d['id'],
                "pId" => 0,
                "name" => $d['agent_name'] == $d['hostname'] ? $d['ip']."(". $d['hostname'] .")" : $d['ip']."(". $d['agent_name'] .")",
                "title" => $d['ip'],
                "isParent" => false,
                "uuid" => $d['agent_uuid'],
                "nocheck" => true,
                "type" => 1,
                "icon" => "./img/vm/host.png",
            );
            $tree[] = $node;
        }
        
        return json_encode($tree);
    }
    
    /**
     * 得到代理端文件目录
     * @param array $params
     *      start  开始位置 
     *      limit  查找个数
     *      searchFileName 从哪个文件名开始查找
     *      dir            进入目录的目录名
     *      三种情况
     *          1.初始展示[0, N, '', '']
     *          2.更多信息[N, N+M, searchFileName, '']
     *          3.进入目录[0, N, '', dir]
     * @return string
     */
    public function getAgentFileDir($params){
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        if(count($params) != 5){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
        }
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agentuuid'];
        $this->paramsCheck($agentUUID);
        $msg = array(
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
        );
        $opName = 'PT_QUERY_AGENT_INFO_OP_QUERY_DIR_LIST';
        $mbResult = $this->mbAgentMsg($opName, $agentUUID, json_encode($msg));
        
//         var_dump($mbResult);
        
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcodePrivate');
        $operate = $pfOpcode->getOpcodeDes($opName);
//         var_dump($operate);
//         return;
        //返回结果到UI
        if(!$result){
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $list = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finishFlag" => $data['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "searchIndex" => $data['current_next_index'],   //未完成时,下一个开始位置
            "searchFilename" => $data['search_file_name'],  //未完成时,下一个开始名字
        );
        $fileList = array();
        $utils = Xphp::instance('Utils');
        foreach ($data['_item_list'] as $d){
            $filename = $d['item_name'];
            $filesize = $utils->calSize($d['file_size']);
            $fileList[] = array(
                $filename,      //文件名
                $filesize,      //文件大小
                array(
                    "path" => $d['item_path'],                                                          //路径
                    "type" => $this->getFileType($d['item_type'], $filename),                           //文件类型
                    "btype" => $d['item_type'],                                                         //从后台获取的文件类型
                    "isfile" => $d['item_type'] == Xphp::$_config['FILETYPE']['FILE'] ? true : false,   //是否是文件 
                    "sclass" => $this->getFileClassName($d['item_type'], $filename, 's'),                //显示类型
                ),
            );
        }
        $list['filelist'] = $fileList;
        
        return json_encode($list);    
    }
    
    /**
     * 得到代理端文件类型
     * @param int $filetype
     * @param string $filename
     */
    public function getFileType($filetype, $filename){
        $filetypeConf = Xphp::$_config['FILETYPE'];
        $fileClass = "unknown";
        switch ($filetype){
            case $filetypeConf['FILE']:
                //TODO加入文件类型
                $fileClass = "file";
                break;
            case $filetypeConf['DIRECTORY']:
                $fileClass = "directory";
                break;
            case $filetypeConf['FIX_DRIVER']:
                $fileClass = "fixdriver";
                break;
            case $filetypeConf['REMOVABLE']:
                $fileClass = "removable";
                break;
            case $filetypeConf['REMOTE']:
                $fileClass = "remote";
                break;
            default:
                $fileClass = "unknown";
                break;
        }
        return $fileClass;
    }
    
    /**
     * 得到文件别分时间点树(灾备中心/文件备份数据)
     * @param unknown $params
     */
    public function getFileDataTree($params){
    	$nodeuuid = $params['nodeuuid'];
        $sql = "select bsr.node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag, 
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and 
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
                       and bbt.import_flag = ? and
	                   bbt.module_type = ? and
	                   bbt.user_uuid = ? ";
        $flag = Xphp::$_config['FLAG'];
        $module = Xphp::$_config['MODULE_TYPE']['FS'];
        $userUUID = Xphp::$_user['useruuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $module, $userUUID);
        if(!empty($nodeuuid)){
        	$sql .=" and bsr.node_uuid = ? ";
        	$sqlParams = array_merge($sqlParams, array($nodeuuid));
         }
        $sql .= " order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        //定义host task 数组
        $host = array();
        $task = array();
        
        $pfDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($data as $d){
            //检查并添加host
            if(!in_array($d['agent_uuid'], $host)){
                $host[] = $d['agent_uuid'];
                $key = array_search($d['agent_uuid'], $host);
                $node[] = array(
                    "id" => '0_' . $key,
                    "pId" => 0,
                    "name" => $d['agent_ip'] . "(" . $d['agent_name'] . ")",
                    "open" => true,
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => './img/vm/host.png',
                	"nodeuuid" => $d['node_uuid']
                );
            }
            $key = array_search($d['agent_uuid'], $host);
            $pid = '0_' . $key;
        
            //检查并添加task
            if(!in_array($d['task_uuid'], $task)){
                $task[] = $d['task_uuid'];
                $key = array_search($d['task_uuid'], $task);
                $node[] = array(
                    "id" => $pid . "_" . $key,
                    "pId" => $pid,
                    "name" => $d['task_name'],
                    "open" => false,
                    "nocheck" => true,
                    "type" => 2,
                    "icon" => './img/platform/flag.png',
                	"nodeuuid" => $d['node_uuid'],
                	"taskuuid" => $d['task_uuid'],
                	"clickshow" => true,
                	"isParent" => true,
                );
            }
        
        }
        return json_encode($node);
    }
    
    /**
     * 异步获取文件时间点
     * @param unknown $params
     * @return string
     */
    public function getSyncFileTimepoint($params){
		$recoverflag = $params['recoverflag']; //文件恢复加载时间点
    	$taskuuid = $params['taskuuid'];
    	$id = $params['id'];
    	$nodeuuid = $params['nodeuuid'];
    	$sql = "select bsr.storage_nickname, bsr.node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type = ? and
	                   bbt.user_uuid = ? and bbt.task_uuid = ? ";
    	
    	$flag = Xphp::$_config['FLAG'];
    	$module = Xphp::$_config['MODULE_TYPE']['FS'];
    	$userUUID = Xphp::$_user['useruuid'];
    	$sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $module, $userUUID, $taskuuid);
		if(!empty($nodeuuid)){
			$sql .=" and bsr.node_uuid = ? ";
			$sqlParams = array_merge($sqlParams, array($nodeuuid));
		}
		
    	$sql .= " order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint";
    	$data = $this->dbSelect($sql, $sqlParams);
    	$node = array();
    	$vmHandler = Xphp::instance('Vmhandler');
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$pid = null;
    	foreach($data as $d){
    		$timepointuuid = $d['timepoint_uuid'];
			if(intval($d['backup_mode']) == Xphp::$_config['BACKUP_MODE']['FULL']){
				$node[] = array(
						"id" => $d['timepoint_uuid'],
						"pId" => $id,
						"name" => $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")",
						"checked" => false,
						"type" => 3,
						"nocheck" => $recoverflag,
						"point_uuid" => $d['timepoint_uuid'],
						"depend_uuid" => $d['depend_point_uuid'],
						"agent_name" => $d['agent_name'],
						"agent_ip" => $d['agent_ip'],
						"agent_uuid" => $d['agent_uuid'],
						"task_name" => $d['task_name'],
						"star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
						"icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
						"mode" => intval($d['backup_mode']),
						"timepointuuid" => $d['timepoint_uuid'],
						"taskuuid" => $taskuuid,
						"nodeuuid" => $d['node_uuid'],
						'storagename' => $d['storage_nickname'],
						'timepoint'=> $this->parseDate($d['timepoint']),
						"nodename" => $nodeHandler->getNodeName($d['node_uuid']),
				);
				$pid = $timepointuuid;
				continue;
			}    
			$node[] = array(
					"id" => $d['timepoint_uuid'],
					"pId" => $pid,
					"name" => $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")",
					"checked" => false,
					"type" => 4,
					"nocheck" => $recoverflag,
					"point_uuid" => $d['timepoint_uuid'],
					"depend_uuid" => $d['depend_point_uuid'],
					"agent_name" => $d['agent_name'],
					"agent_ip" => $d['agent_ip'],
					"agent_uuid" => $d['agent_uuid'],
					"task_name" => $d['task_name'],
					"star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
					"icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
					"mode" => intval($d['backup_mode']),
					"timepointuuid" => $d['timepoint_uuid'],
					"taskuuid" => $taskuuid,
					"nodeuuid" => $d['node_uuid'],
					"chkDisabled" => !$recoverflag,
					"storagename" => $d['storage_nickname'],
					'timepoint'=> $this->parseDate($d['timepoint']),
					"nodename" => $nodeHandler->getNodeName($d['node_uuid'])
			);
    	}
    	
    	$msg = array(
    		're' => true,
    		'msg' => $node,
    	);
    	return json_encode($msg);
    }
    
    /**
     * 得到文件备份时间点树(文件恢复STEP1)
     * @param unknown $params
     */
    public function getFileTimepointTree($params){
        $sql = "select bbt.id, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid,  
		              fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name  
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? and
	                   bbt.import_flag = ? and bbt.module_type = ? and 
	                   bbt.user_uuid = ? 
                order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint desc  ";
        
        $flag = Xphp::$_config['FLAG'];
        $module = Xphp::$_config['MODULE_TYPE']['FS'];
        $userUUID = Xphp::$_user['useruuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $module, $userUUID);
        
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        //定义host task 数组
        $host = array();
        $task = array();
        $pfDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($data as $d){
            //检查并添加host
            if(!in_array($d['agent_uuid'], $host)){
                $host[] = $d['agent_uuid'];
                $key = array_search($d['agent_uuid'], $host);
                $node[] = array(
                    "id" => '0_' . $key,
                    "pId" => 0,
                    "name" => $d['agent_ip'] . "(" . $d['agent_name'] . ")",
                    "open" => false,
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => './img/vm/host.png',
                );
            }
            $key = array_search($d['agent_uuid'], $host);
            $pid = '0_' . $key;
        
            //检查并添加task
            if(!in_array($d['task_uuid'], $task)){
                $task[] = $d['task_uuid'];
                $key = array_search($d['task_uuid'], $task);
                $node[] = array(
                    "id" => $pid . "_" . $key,
                    "pId" => $pid,
                    "name" => $d['task_name'],
                    "open" => false,
                    "nocheck" => true,
                    "type" => 2,
                    "icon" => './img/platform/flag.png'
                );
            }
            
            $key = array_search($d['task_uuid'], $task);
            $pid .= "_" . $key;
            
            $mode = intval($d['backup_mode']);
            $pointMode = "(" . $pfDes['BACKUP_MODE_DES'][$mode] . Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . ")";
            
            $node[] = array(
                "id" => $pid . "_" . $d['id'],
                "pId" => $pid,
                "name" => $this->parseDate($d['timepoint']) . $pointMode,
                "checked" => false,
                "type" => 3,
                "nocheck" => true,
                "point_uuid" => $d['timepoint_uuid'],
                "agent_name" => $d['agent_name'],
                "agent_ip" => $d['agent_ip'],
                "agent_uuid" => $d['agent_uuid'],
                "task_name" => $d['task_name'],
                "icon" => './img/platform/timepoint.png'
            );
        }
        return json_encode($node);
    }
    
    /**
     * 得到备份文件列表
     * @param array $params
     *      sclass                  文件样式类型  s/m/l 24 32 48
     *      
     *      timepoint_uuid(string)   时间点UUID
     *      root_flag(int)           是否是根节点
     *      start(int)               开始位置
     *      number(int)              获取条数
     *      path(string)             当前路径
     *      md5_flag(int)            是否有MD5信息标志
     *      md5_high(int)            MD5的高八位
     *      md5_low(int)             MD5的第八位
     *      三种情况
     *          1.初始展示[timepoint_uuid, 1, 0, N, '', 0, '', '']
     *          2.更多信息[timepoint_uuid, 0, N, N+M, path, md5_flag, md5_high, md5_low]
     *          3.进入目录[timepoint_uuid, 0, 0, N, path, md5_flag, md5_high, md5_low]
     *      
     */
    public function getBackupFileDir($params){
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        if(count($params) != 9){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
        }
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = intval($params['root_flag']);
        $start = intval($params['start']);
        $number = intval($params['number']);
        $path = $params['path'];
        $md5Flag = intval($params['md5_flag']);
        $md5High = $params['md5_high'];
        $md5Low = $params['md5_low'];
        
        $sclass = $params['sclass'];
        
        $this->paramsCheck($timepointUUID);
        
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
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true, true);
        
        $result = $mbResult['result'];
        //返回结果到UI
        if(!$result){
            //失败
            $pfOpcode = Xphp::instance('PFOpcode');
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $list = array(
            "re" => true,                               //成功标志,方面前端统一处理
            "finishFlag" => $data['finish_flag'],       //文件是否列完成   1完成,2未完成
            "nextStart" => $data['next_start'],       //未完成时,下一个开始位置
            "path" => $data['path'],                    //当前路径
            "timepointUUID" => $data['timepoint_uuid'],
        );
        $fileList = array();
        $utils = Xphp::instance('Utils');
        foreach ($data['item_list'] as $d){
            $filename = $d['filename'];
            $filesize = $utils->calSize($d['file_size']);
            $fileList[] = array(
                $filename,      //文件名
                $filesize,      //文件大小
                array(
                    "path" => $d['path'],                                                          //路径
                    "type" => $this->getFileType($d['type'], $filename),                           //文件类型
                    "btype" => $d['type'],                                                         //从后台获取的文件类型
                    "isfile" => $d['type'] == Xphp::$_config['FILETYPE']['FILE'] ? true : false,   //是否是文件 
                    "sclass" => $this->getFileClassName($d['type'], $filename, $sclass),                //显示类型
                    "md5Flag" => $d['md5_flag'],
                    "md5High" => $d['md5_high'],
                    "md5Low" => $d['md5_low'],
                    "createTime" => $d['create_time'],
                    "modifyTime" => $d['modify_time'],
                ),
            );
        }
        $list['filelist'] = $fileList;
        
        return json_encode($list);
    }
    
    /**
     * 根据文件类型得到显示的Class
     * @param int $filetype 文件类型
     * @param string $filename  文件名
     * @param string $size  图标大小   s/m/l 24/32/48px
     */
    public function getFileClassName($filetype, $filename, $size){
        $class = "filetype-unknown-" . $size;
        if(intval($filetype) != Xphp::$_config['FILETYPE']['FILE']){
            $class = "filetype-dir-" . $size;
            return $class;
        }
        $allFileType = array(
            'aac','ai','aiff','asp','avi','bmp','c','cpp','css','dat',
            'dmg','doc','docx','dot','dotx','dwg','dxf','eps','exe',
            'flv','gif','h','html','ics','iso','java','jpg','key','m4v',
            'mid','mov','mp3','mp4','mpg','odp','ods','odt','otp','ots',
            'ott','pdf','php','png','pps','ppt','psd','py','qt','rar',
            'rb','rtf','sql','tga','tgz','tiff','txt','wav','xls','xlsx',
            'xml','yml','zip'
        );
        $fileArr = explode('.', $filename);
        $index = (count($fileArr) - 1) <= 0 ? 0 :  (count($fileArr) - 1);
        if(in_array(strtolower($fileArr[$index]), $allFileType)){
            $class = "filetype-" . strtolower($fileArr[$index]) . "-" . $size;
        }
        return $class;
    }
    
    /**
     * 得到恢复代理(恢复到其他宿主机)
     * @param unknown $params
     */
    public function getRecoverAgentTree($params){
        $agentUUID = $params['agentuuid'];
        $this->paramsCheck($agentUUID);
        $tree = array();
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module
                from bd_agent
                where online_flag = ?
                and user_uuid = ?  and agent_uuid != ?";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid'], $agentUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        
        $systemHandler = Xphp::instance('SystemHandler');
        foreach ($data as $d){
            if(!($systemHandler->checkModuleValid($d['authorization_module'], 'file'))){
                continue;
            }
            $node = array(
                "id" => $d['id'],
                "pId" => 0,
                "name" => $d['agent_name'] == $d['hostname'] ? $d['hostname'] : $d['agent_name'],
                "title" => $d['ip'],
                "isParent" => false,
                "uuid" => $d['agent_uuid'],
                "nocheck" => false,
                "type" => 1,
                "icon" => "./img/vm/host.png",
            );
            $tree[] = $node;
        }
        
        return json_encode($tree);
    }
    
    /**
     * 得到代理端恢复文件目录树
     * @param array $params
     *      start  开始位置
     *      limit  查找个数
     *      searchFileName 从哪个文件名开始查找
     *      dir            进入目录的目录名
     *      三种情况
     *          1.初始展示[0, N, '', '']
     *          2.更多信息[N, N+M, searchFileName, '']
     *          3.进入目录[0, N, '', dir]
     * @return string
     */
    public function getRecoverPathTree($params){
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agentuuid'];
        $pid = $params['pid'];          //父节点ID
        $this->paramsCheck($agentUUID);
        
        $msg = array(
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
        );
        $opName = 'PT_QUERY_AGENT_INFO_OP_QUERY_RECOVERY_DIR_LIST';
        $mbResult = $this->mbAgentMsg($opName, $agentUUID, json_encode($msg));
        
        $result = $mbResult['result'];
        
        //返回结果到UI
        if(!$result){
            //失败
            $pfOpcode = Xphp::instance('PFOpcodePrivate');
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        
        $i = 0;
        foreach ($data['_item_list'] as $d){
            $node = array(
                "id" => $pid . "_" . $i++,
                "pId" => $pid,
                "name" => $d['item_name'],
                "title" => $d['item_path'],
                "isParent" => true,
                "nocheck" => false,
                "type" => $d['item_type'],
                "more" => false,
//                 "icon" => "./img/vm/host.png",
            );
            $tree[] = $node;
        }
        if(intval($data['is_search_finish']) == Xphp::$_config['FLAG']['UNSET']){
            //如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $pid . "_" . $i++,
                "pId" => $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "isParent" => false,
                "nocheck" => true,
                "more" => true,
                "next_index" => $data['current_next_index'],       //从哪个位置开始加载
                "search_file_name" => $data['search_file_name'],          //从哪个目录开始加载
                "dir_path" => $dir,                                  //当前目录名
            );
            $tree[] = $more;
        }
        
        $info = array(
            're' => true,
            'tree' => $tree
        );
        
        return json_encode($info);
        
    }
    
    /**
     * 得到文件备份任务运行详情
     * 当前文件总数/完成总数/当前文件(只显示运行状态)
     * @param unknown $params
     */
    public function getTaskRunningInfo($params){
        
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_status, bt.task_type, 
                fri.current_fs_count, fri.total_fs_count, bri.current_mode, bri.current_file_name  
                from bd_task bt, fs_running_info fri, bd_running_info bri 
                where bt.task_uuid = bri.task_uuid 
                and bri.task_uuid = fri.task_uuid 
                and fri.task_uuid = ? ";
        $sqlParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array(
            'currentCount' => '--',
            'totalCount' => '--',
            'currentFile' => '----',
            'taskMode' => '--',
        );
        if($data){
            if(intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']){
                //任务运行中
                $info = array(
                    'taskStatus' => intval($data[0]['task_status']),
                    'currentCount' => $data[0]['current_fs_count'],
                    'totalCount' => $data[0]['total_fs_count'],
                    'currentFile' => $data[0]['current_file_name'],
                    'taskMode' => $this->getTaskBackupTypeDes($data[0]['task_type'], $data[0]['current_mode']),
                );
            }
        }
        return json_encode($info);
    }
    
    /**
     * 得到文件备份任务的类型  完全备份/增量备份/差异备份
     * @param $taskType     任务类型
     * @param $currentMode  当前任务模式
     */
    private function getTaskBackupTypeDes($taskType, $currentMode){
        $des = '--';
        if(intval($taskType) == Xphp::$_config['TASKTYPE']['BACKUP']){
            //如果是备份任务才处理
            $pfDes = include APP_PATH . 'platform/PFDescription.php';
            $des = $pfDes['BACKUP_MODE_DES'][intval($currentMode)] . 
                   $pfDes['TASKTYPEDES'][intval($taskType)];
        }
        return $des;
    }
    
    /**
     * 得到文件备份任务详情
     * @param unknown $params
     */
    public function getBackupTaskInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select ba.agent_name, ba.ip 
                from bd_task bt, bd_agent ba 
                where bt.agent_uuid = ba.agent_uuid 
                and bt.task_uuid = ? ";
        $sqlParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if($data){
            $info = array(
                'agentName' => $data[0]['agent_name'],
                'agentIP' => $data[0]['ip'] 
            );
        }
        //得到文件列表
        $sql = "select path_name from fs_path_list where task_uuid = ? ";
        $sqlParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $list = array();
        foreach ($data as $d){
            $list[] = $d['path_name'];
        }
        $info['fileList'] = $list;
        return json_encode($info);
    }
    
    /**
     * 得到文件恢复任务详情
     * @param unknown $params
     */
    public function getRecoverTaskInfo($params){
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select ba.agent_name, ba.ip
                from bd_task bt, bd_agent ba
                where bt.agent_uuid = ba.agent_uuid
                and bt.task_uuid = ? ";
        $sqlParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if($data){
            //得到目的代理信息
            $info = array(
                'desAgentName' => $data[0]['agent_name'],
                'desAgentIP' => $data[0]['ip']
            );
        }
        //得到文件列表
        $sql = "select path_name, recovery_timepoint_uuid, new_root_path from fs_path_list where task_uuid = ? ";
        $sqlParams = array($taskUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        $timepointUUID = '';
        $newPath = '';
        $list = array();
        foreach ($data as $d){
            $list[] = $d['path_name'];
            $timepointUUID = $d['recovery_timepoint_uuid'];
            $newPath = $d['new_root_path'];
        }
        
        //得到恢复路径
        if(empty($newPath)){
            //恢复到原路径
            $info['newPath'] = Xphp::$_lang['UI_RECOVERY_FILE_OLD_PATH'];
        }else{
            //恢复到自定义路径
            $info['newPath'] = $newPath;
        }
        
        //得到源代理信息
        $sql = "select agent_name, agent_ip from fs_backup_timepoint where fs_timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($timepointUUID));
        if($data){
            $info['srcAgentName'] = $data[0]['agent_name'];
            $info['srcAgentIP'] = $data[0]['agent_ip'];
        }
        
        $info['fileList'] = $list;
        return json_encode($info);
    }
    
    /**
     * 删除备份时间点
     * @param unknown $params
     * storageHandler deleteFSImportData调用
     */
    public function deleteTimepoint($params){
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
    
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $msg = array($pointUUID);
        $msg = json_encode(array('timepoint_uuids'=>$msg));
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
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
     * 转化时间策略天数为一个数组,每一项为boll
     * @param unknown $days
     */
    private function parseTimeStrategyDay($days){
        $daysArr = str_split($days);
        foreach ($daysArr as $key => $d){
            if($d){
                $daysArr[$key] = true;
            }else{
                $daysArr[$key] = false;
            }
        }
        return $daysArr;
    }
    
    /**
     * 根据策略id得到时间策略信息
     * @param int $strategyID
     */
    private function getTimeStrategyInfo($strategyID){
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
                $strategyData[] = array(
                    'start_time' => $d['start_time'],
                    'roll_flag' => $utils->parseFlagToBool($d['roll_flag']),
                    'roll_interval' => $utils->secToTime($d['roll_interval']),
                    'roll_end_time' => $d['roll_end_time'],
                    'mode' => $d['mode'],
                    'strategy_type' => $d['strategy_type'],
                    'days' => $this->parseTimeStrategyDay($d['days']),
                );
            }
            $info['data'] = $strategyData;
        }
        return $info;
    }
    
    /**
     * 根据路径得到文件名
     * @param unknown $path
     */
    private function getFileName($pathType, $pathName){
        $pathArr = explode("/", $pathName);
        $arrLen = count($pathArr);
        if(Xphp::$_config['FILETYPE']['FILE'] == $pathType){
            //文件
            $filename = $pathArr[$arrLen - 1];
        }else{
            //目录
            $filename = $pathArr[$arrLen - 2];
        }
        return $filename;
    }
    
    /**
     * 根据任务ID得到文件备份备份列表信息
     * @param unknown $taskuuid
     */
    private function getFileBackupFiles($taskuuid){
        $sql = "select path_name, path_type from fs_path_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d){
            $filename = $this->getFileName(intval($d['path_type']), $d['path_name']);
            $info[] = array(
                'type' => $d['path_type'],
                'name' => $filename,
                'path' => $d['path_name'],
                'sclass' => $this->getFileClassName($d['path_type'], $filename, 's'),
            );
        }
        return $info;
    }
    
    /**
     * 修改任务/得到备份任务的所有信息
     * @param unknown $params
     */
    public function getBackupTaskAllInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.agent_uuid, ft.level,
                	   brs.strategy_type, brs.number,
                	   bts.encrypt_flag, bts.compress_flag,
                	   bss.compressed_flag, bss.encrypted_flag, bss.compress_method
                from bd_task bt, fs_task ft, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss
                where bt.task_uuid = ft.task_uuid
                and bt.strategy_id = brs.strategy_id
                and bt.strategy_id = bts.strategy_id
                and bt.strategy_id = bss.strategy_id
                and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $info = array();
        $utils = Xphp::instance('Utils');
        if($data){
            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //任务名
                'taskname' => $data[0]['task_name'],
                //代理端UUID
                'agentuuid' => $data[0]['agent_uuid'],
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
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypt_flag']),
                    'compress' => $utils->parseFlagToBool($data[0]['compress_flag']),
                ),
                //存储策略
                'bss' => array(
                    'compress' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'compress_method' => $data[0]['compress_method'],
                ),
                //时间策略
                'timestrategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
            );
        }
        return json_encode($info);
    }
}
?>