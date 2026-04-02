<?php
/******************************************* 
** 大屏2.0，获取所有数据类
** 
** @date         2022-8-05 下午13:40:40 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class VisualScreenHandler extends OPHandler{
    /**
     * 数据统计，存储统计，存储详情
     * @param unknown $params
     */
    public function getStatisticData($params){
        $dataDetails = $this->getStatisticDataDetails();
        $storageList = $this->getStorageList();
        $storageData = $this->getStorageData();
        
        $info = array(
            'data_statistics' => $dataDetails,
            'storage_details' => array(
                'list' => $storageList
            ),
            'storing_stastical' => $storageData,
        );
        return json_encode($info);
    }
    
    /**
     * 获取数据统计信息
     */
    private function getStatisticDataDetails(){
        $dataDetials = array();
        $utils = Xphp::instance('Utils');
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        
        //备份数据:文件(1)，数据库，操作系统，虚拟机(1)，NAS(1)，实时容灾
        $taskTypeArr = array($taskTypeConf['BACKUP'], $taskTypeConf['DB_BACKUP'], $taskTypeConf['OS_BACKUP'], $taskTypeConf['VOL_CDP_BACKUP']);
        
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        $dataDetials['total_backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $dataDetials['total_backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位
        
        //副本数据:虚拟机，文件，操作系统，数据库
        $taskTypeArr = array($taskTypeConf['BACKUP_COPY'], $taskTypeConf['FILE_BACKUP_COPY'], $taskTypeConf['DB_BACKUP_COPY'], $taskTypeConf['OS_BACKUP_COPY']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        $dataDetials['total_copy_data'] = $valueAndUnit['value'];     //副本数据总量
        $dataDetials['total_copy_data_unit'] = $valueAndUnit['unit']; //副本数据总量的单位
        
        //归档数据:虚拟机
        $taskTypeArr = array($taskTypeConf['ARCHIVE']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        $dataDetials['total_archived_data'] = $valueAndUnit['value'];     //归档数据总量
        $dataDetials['total_archived_data_unit'] = $valueAndUnit['unit']; //归档数据总量的单位
        
        
        //每日数据统计列表
        $dataDetials['list'] = $this->getStatisticDataDetailsList();
        
        return $dataDetials;
    }
    
    /**
     * 获取指定类型任务的写入数据总大小
     * @param array $taskTypeArr    任务类型
     * @return array $valueAndUnit  大小(value)和单位(unit)和数量(num)          
     */
    private function getSomeTaskTypeTimepointWriteSize($taskTypeArr){
        $utils = Xphp::instance('Utils');
        $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type in (?)";
        // 实时容灾
        $sqlVolCdp = "select SUM(cvbvs.backup_file_size + cvbvs.log_file_total_size) as size ,count(bbt.id) as num FROM cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt where
        cvbvs.backup_agent_id = cvba.id and cvba.timepoint_uuid = bbt.timepoint_uuid  ";
        // 副本任务个数
        $taskNumSql = "select count(task_uuid) as task_num from bd_task where task_type = ?";
        $sqlParams = array_map(function ($value) {
            $value = sprintf("%d", $value);
            return $value;
        }, $taskTypeArr);
        $data_Size = 0;
        $data_Num = 0;
        foreach ($sqlParams as $d){
            if($d == sprintf("%d", Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'])){ //volcdp的数据量
                $data = $this->dbSelect($sqlVolCdp);
            } else{
                $data = $this->dbSelect($sql,array($d));
            }
            $taskNumSqlData = $this->dbSelect($taskNumSql,array($d));
            $data_Size += $data[0]['size'];
            $data_Num += $data[0]['num'];
            $task_Num += $taskNumSqlData[0]['task_num'];
        }
        $valueAndUnit = $utils->calSizeToValueAndUnit($data_Size, true);
        $valueAndUnit['num'] = $data_Num;     //时间点个数
        $valueAndUnit['task_num'] = $task_Num;  //副本任务个数
        return $valueAndUnit;
    }
    
    /**
     * 获取指定类型模块的写入数据总大小
     * @param array $moduleTypeArr      模块类型
     * @return array $valueAndUnit      大小(value)和单位(unit)和数量(num)  
     */
    private function getSomeModuleTypeTimepointWriteSize($moduleTypeArr){
        $utils = Xphp::instance('Utils');
        $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where module_type in (?);";
        $sqlParams = array(implode(",", $moduleTypeArr));
        $data = $this->dbSelect($sql, $sqlParams);
        $valueAndUnit = $utils->calSizeToValueAndUnit($data[0]['size'], true);
        $valueAndUnit['num'] = $data[0]['num'];     //时间点个数
        
        return $valueAndUnit;
    }


    /**
     * 获取大小转换成界面显示信息
     * @param unknown $size
     */
    private function pGetIntToSizeInfo($size, $utils){
        $writeSize = $utils->calSize($size, true);
        $writeInfo = $utils->calSizeToValueAndUnit($size, true);
        $info = array(
            'size' => $size,
            'value' => $writeInfo['value'],
            'unit' => $writeInfo['unit'],
            'des' => $writeSize
        );
        return $info;
    }

    /**
     * 获取指定类型任务的当天写入数据总大小
     * @param array $taskTypeArr    任务类型
     * @return array $valueAndUnit  大小和单位和真实值
     */
    private function getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr){
        $today = date('Y-m-d');
        $sql ="select sum(total_object_write_size) as write_size from bd_history_task where finish_time like '%". $today . "%'  and task_type in (?) ";
        $sqlParams = array_map(function ($value) {
            $value = sprintf("%d", $value);
            return $value;
        }, $taskTypeArr);
        $dataWrite_Size = 0;
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        foreach ($sqlParams as $d){
            if( intval($d) == $taskTypeConf['VOL_CDP_BACKUP']){
                $todayStart = date('Y-m-d') . " 00:00:00";
                $todayEnd = date('Y-m-d') . " 23:59:59";
                $sqlCdp = "select sum(data_flow) as size from cdp_vol_backup_agent_minute_level_data_flow 
                        where backup_start_timestamp >= ? and backup_end_timestamp < ?";
                $sqlParams = array($todayStart, $todayEnd);
                $data = $this->dbSelect($sqlCdp, $sqlParams);
                if(empty($data[0]['size'])){
                    $data[0]['size'] = 0;
                }
                $dataWrite_Size += $data[0]['size'];
            }else{
                $data = $this->dbSelect($sql,array($d));
                $dataWrite_Size += $data[0]['write_size'];
            }
        }
        $utils = Xphp::instance('Utils');
        $sizeInfo = $this->pGetIntToSizeInfo(intval($dataWrite_Size), $utils);
        $info = array(
            'date' => date("m/d"),
            'size' => $sizeInfo['size'],
            'value' => $sizeInfo['value'],
            'unit' => $sizeInfo['unit'],
            'des' => $sizeInfo['des']
        );
        return $info;
    }
    
    /**
     * 获取指定类型模块的当天写入数据总大小
     * @param array $moduleTypeArr      模块类型
     * @return array $valueAndUnit      大小和单位和真实值
     */
    private function getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr,$taskType1,$taskType2,$taskType3){
        $utils = Xphp::instance('Utils');
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = "select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and task_type not in (?,?,?) and  start_time >= ? and finish_time < ?;";
        $sqlParams = array(implode(",", $moduleTypeArr), $taskType1, $taskType2, $taskType3, $todayStart, $todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $size = $data[0]['size'];
        $valueAndUnit = $utils->calSizeToValueAndUnit($size, true);
        if(empty($size)){
            $size = 0;
        }
        $valueAndUnit['size'] = $size;
        
        return $valueAndUnit;
    }
    
    
    /**
     * 获取数据统计的每日备份，副本，归档情况列表
     * @return array    $info
     */
    private function getStatisticDataDetailsList(){
        $info = array();
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        //当天的数据从时间点中获取
        $todayInfo = array(
            'date' => date("m/d"),
        );
        
        //备份数据:文件(1)，数据库，操作系统，虚拟机(1)，NAS(1)，实时容灾
        $taskTypeArr = array($taskTypeConf['BACKUP'], $taskTypeConf['DB_BACKUP'], $taskTypeConf['OS_BACKUP'], $taskTypeConf['VOL_CDP_BACKUP'], $taskTypeConf['VOL_CDP_TAKEOVER']);
        
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量
        $todayInfo['backup_data_des'] = $valueAndUnit['value'];     //备份数据总量
        $todayInfo['backup_data_des_unit'] = $valueAndUnit['unit']; //备份数据总量的单位
        
        //副本数据:虚拟机，文件，操作系统，数据库
        $taskTypeArr = array($taskTypeConf['BACKUP_COPY'], $taskTypeConf['FILE_BACKUP_COPY'], $taskTypeConf['DB_BACKUP_COPY'], $taskTypeConf['OS_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量
        $todayInfo['copy_data_des'] = $valueAndUnit['value'];     //副本数据总量
        $todayInfo['copy_data_des_unit'] = $valueAndUnit['unit'];   //副本数据总量的单位
        
        //归档数据:虚拟机
        $taskTypeArr = array($taskTypeConf['ARCHIVE']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['archived_data'] = $valueAndUnit['size'];          //归档数据真实量
        $todayInfo['archived_data_des'] = $valueAndUnit['value'];     //归档数据总量
        $todayInfo['archived_data_des_unit'] = $valueAndUnit['unit']; //归档数据总量的单位
        
        $info[] = $todayInfo;
        
        //从bd_storage_monitor表取最新6条，每一条都是存储的上一天的数据。
        $sql = "select unix_timestamp(date) date,  backup_write_size, copy_write_size, 	archive_write_size from bd_storage_monitor 
                order by id desc limit 0, 6";
        $data = $this->dbSelect($sql);
        
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
            $dateTime = intval($d['date']) - 3600*24;
            $backupValueAndUnit = $utils->calSizeToValueAndUnit($d['backup_write_size'], true);
            $copyValueAndUnit = $utils->calSizeToValueAndUnit($d['copy_write_size'], true);
            $archiveValueAndUnit = $utils->calSizeToValueAndUnit($d['archive_write_size'], true);
            
            $info[] = array(
                'date' => date("m/d", $dateTime),
                
                'backup_data' => $d['backup_write_size'],
                'backup_data_des' => $backupValueAndUnit['value'],
                'backup_data_des_unit' => $backupValueAndUnit['unit'],
                
                'copy_data' => $d['copy_write_size'],
                'copy_data_des' => $copyValueAndUnit['value'],
                'copy_data_des_unit' => $copyValueAndUnit['unit'],
                
                'archived_data' => $d['archive_write_size'],
                'archived_data_des' => $archiveValueAndUnit['value'],
                'archived_data_des_unit' => $archiveValueAndUnit['unit'],
            );
        }
        
        //按时间顺序排列
        $info = array_reverse($info);
        return $info;
    }
    
    /**
     * 获取存储信息：包括存储统计和存储详情
     */
    private function getStorageList(){
        $sql = "select storage_nickname, storage_type, total_size, free_size, status, mount_flag, node_uuid from bd_storage_resource where lan_free_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql,$sqlParams);
        $list = array();
        $utils = Xphp::instance('Utils');
        $storageHandler = Xphp::instance('StorageHandler');
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d){
            $usedSize = $d['total_size'] - $d['free_size'];
            //存储状态
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            $status = $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
            //转换存储状态0在线 1离线
            $status = $status == Xphp::$_config['STORAGE_STATUS']['ONLINE']? 0 : 1;
            $list[] = array(
                'store_name' => $d['storage_nickname'],
                'store_type' => $d['storage_type'],
                'use_space' => $utils->calPercentValue($d['total_size'], $usedSize),
                'use_space_des' => $utils->calSize($usedSize, true) . "/" . $utils->calSize($d['total_size'], true),
                'store_status' => $status,
            );
        }
        return $list;
    }
    
    /**
     *  获取存储统计
     */
    private function getStorageData(){
        $sql = "select count(bsr.storage_uuid) as storage_count, sum(bsr.total_size) as total_size, sum(bsr.free_size) as free_size from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql,$sqlParams);
        $utils = Xphp::instance('Utils');
        if(!$data){
            $totalSize = 0;
            $freeSize = 0;
        }else{
            $totalSize = intval($data[0]['total_size']);
            $freeSize = intval($data[0]['free_size']);
        }
        
        if($totalSize > 0){
            $percent = round(($freeSize/$totalSize)*100, 1);
        }else{
            $percent = 0;
        }
        $useSize = $totalSize - $freeSize;
        
        $useSizeValueAndUnit = $utils->calSizeToValueAndUnit($useSize);
        $freeSizeValueAndUnit = $utils->calSizeToValueAndUnit($freeSize);
        
        $info = array(
            "storage_num" => intval($data[0]['storage_count']),
            "total_storage_data" => $utils->calSize($totalSize),
            
            "use_size" => $useSize,
            "free_size" => $freeSize,
            
            "use_size_des" => $useSizeValueAndUnit['value'],
            "use_size_unit" => $useSizeValueAndUnit['unit'],
            
            "free_size_des" => $freeSizeValueAndUnit['value'],
            "free_size_unit" => $freeSizeValueAndUnit['unit'],
        );
        
        return $info;
    }
    
    /**
     * 告警与任务
     * @param unknown $params
     */
    public function getCurrentTaskAndWarning($params){
        $info = array();
        $sql = "select module_type, task_type, task_status from bd_task where delete_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        
        $taskNum = 0;       //任务总数
        $waittingNum = 0;   //等待任务数：任务在等待运行状态
        $finishNum = $this->getTodayHistoryJobSuccessNum();     //完成任务数：在历史任务去取，规则为今日所有成功的历史任务
        $moduleAndTasktypeArr = array();     //模块和任务类型数组，按模块统计使用,这里需要通过module_type和task_type区分模块
        foreach ($data as $d){
            $taskNum++;
            $moduleAndTasktypeArr[] = array(
                "module_type" => intval($d['module_type']),
                "task_type" => intval($d['task_type'])
            );
            if(Xphp::$_config['TASKSTATUS']['WAITTING'] == $d['task_status']){
                //处理等待任务数
                $waittingNum++;
            }
        }
        
        $taskModuleNum = $this->getTaskModultNum($moduleAndTasktypeArr);
        
        $currentTask = array(
            'total_task_num' => $taskNum,
            'wait_num' => $waittingNum,
            'complete_num' => $finishNum,
        );
        
        $currentTask = array_merge($currentTask, $taskModuleNum);
        $alarmNum = $this->getAlarmInfo();
        
        $info = array(
            'current_task' => $currentTask,
            'warning' => $alarmNum
        );
        return json_encode($info);
    }
    
    /**
     * 得到告警信息
     */
    private function getAlarmInfo(){
        //任务告警
        $sql = "select count(task_alarm_id) as num from bd_task_alarm where alarm_level != ? and solved_flag = ?";
        $sqlParams = array(Xphp::$_config['ALARM']['notice'], Xphp::$_config['FLAG']['UNSET']);
        
        $data = $this->dbSelect($sql, $sqlParams);
        $taskAlarmNum = $data[0]['num'];
        
        //系统告警
        $sql = "select count(system_alarm_id) as num from bd_system_alarm where alarm_level != ? and solved_flag = ?";
        $sqlParams = array(Xphp::$_config['ALARM']['notice'], Xphp::$_config['FLAG']['UNSET']);
        
        $data = $this->dbSelect($sql, $sqlParams);
        $systemAlarmNum = $data[0]['num'];
        
        $alarm = array(
            'task_num' => $taskAlarmNum,
            'system_num' => $systemAlarmNum,
        );
        
        return $alarm;
    }
    
    /**
     * 获取当日任务，成功的
     * @param unknown $currentTaskArr
     */
    private function getTodayHistoryJobSuccessNum(){
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = "select count(id) as num from bd_history_task where error_code = 0 and  finish_time >= ? and finish_time < ?;";
        
        $data = $this->dbSelect($sql, array($todayStart, $todayEnd));
        $num = $data[0]['num'];
        
        return $num;
    }
    
    /**
     * 根据模块和任务类型统计各个模块任务数量
     * @param unknown $moduleAndTasktypeArr  模块和任务类型数组
     * @return array    
     */
    private function getTaskModultNum($moduleAndTasktypeArr){
        $taskNum = array(
            'vm_num' => 0,
            'file_num' => 0,
            'database_num' => 0,
            'os_num' => 0,
            'cdp_num' => 0,
            'nas_num' => 0,
            'copy_num' => 0,
            'dbcdp_num' => 0,
            'm365_num' => 0,
            'publicCloud_num' => 0,
        );
        $moduleTypeConf = Xphp::$_config['MODULE_TYPE'];
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        foreach ($moduleAndTasktypeArr as $type){
            $module = $type['module_type'];
            $tasktype = $type['task_type'];
            switch ($module){
                case $moduleTypeConf['VM']:
                    // 公有云
                    $sql = "select distinct bt.task_uuid, bt.module_type, bt.task_type, bt.task_status,bbt.sub_module_type from bd_task bt 
                            inner join bd_backup_timepoint bbt on bt.task_uuid = bbt.task_uuid   where bt.delete_flag = ? and bbt.sub_module_type = ?";
                    $data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
                    $taskNum['publicCloud_num'] = count($data);
                    //虚拟机
                    $sql = "SELECT bt.task_uuid, bt.module_type, bt.task_type, bt.task_status, bbt.sub_module_type
                            FROM bd_task bt
                            LEFT JOIN bd_backup_timepoint bbt ON bt.task_uuid = bbt.task_uuid AND bbt.sub_module_type = ?
                            WHERE bt.delete_flag = ?
                            AND bbt.task_uuid IS NULL";
                    $data = $this->dbSelect($sql,array(Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['FLAG']['UNSET']));
                    $taskNum['vm_num'] = count($data);
                    break;
                case $moduleTypeConf['FS']:
                    //文件
                    $taskNum['file_num'] += 1;
                    break;
                case $moduleTypeConf['DB']:
                    //数据库
                    $taskNum['database_num'] += 1;
                    break;
                case $moduleTypeConf['OS']:
                    //操作系统
                    $taskNum['os_num'] += 1;
                    break;
                case $moduleTypeConf['VOL_CDP']:
                    //实时容灾
                    $taskNum['cdp_num'] += 1;
                    break;
                case $moduleTypeConf['NAS']:
                    //NAS
                    $taskNum['nas_num'] += 1;
                    break;
                case $moduleTypeConf['BACKUP_COPY_CLIENT']:
                    //副本，这里特殊处理，因为副本和归档都是这个模块类型 ，需要再判断任务类型
                    $acrhiveTaskTypeArr = array($taskTypeConf['ARCHIVE'], $taskTypeConf['ARCHIVE_FETCH']);
                    if(!in_array($tasktype, $acrhiveTaskTypeArr)){
                        $taskNum['copy_num'] += 1;
                    }
                    break;
                case $moduleTypeConf['OEM_DBCDP'];
                    //实时同步
                    $taskNum['dbcdp_num'] += 1;
                    break;
                case $moduleTypeConf['M365'];
                    //m365
                    $taskNum['m365_num'] += 1;
                    break;
            }
        }
        return $taskNum;
    }
    
    /**
     * 任务列表
     * @param unknown $params
     */
    public function getCurrentTaskList($params){
        $info = array();
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, bu.user_name,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time,
                        bri.total_object_valid_size,bri.total_object_completed_valid_size
                from bd_running_info bri, bd_user bu, bd_task bt
                     left join sr_sure_backup ssb on bt.task_uuid = ssb.task_uuid
                where bt.task_uuid = bri.task_uuid and
                	 bt.user_uuid = bu.user_uuid and
                     bt.delete_flag = ? and bt.task_type != ? and bt.task_type != ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'],Xphp::$_config['TASKTYPE']['ORCH_TASK'], Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        $data = $this->dbSelect($sql, $sqlParams);
        
        $jobHandler = Xphp::instance('JobHandler');
        foreach ($data as $d){
            $info[] = array(
                'task_name' => $d['task_name'],
                'task_speed' => $jobHandler->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                'task_status' => $d['task_status'],
                'task_progress' => $jobHandler->getCurrentTaskProgress($d),
                'task_module' => $d['module_type'],
                'task_type' => $d['task_type'],
            );
        }
        
        $list = array(
            'list' => $info
        );
        return json_encode($list);
    }
    
    /**
     * 系统配置
     * @param unknown $params
     */
    public function getConfigure($params){
        $settingsHandler = Xphp::instance('SettingsHandler');
        $visualConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['VISUAL']);
        $visualConf = json_decode($visualConf[0]['settings_content'], true);
            
        $config = $visualConf['config'];
        //专门判断是否是空是为了处理兼容性，如果是老版本升级上来的可能只有title,没有其他配置
        $info = array(
            'system_name' => empty($config['title']) ? "" : $config['title'],
            'cloud_name' => empty($config['cloudName']) ? "" : $config['cloudName'],
            'local_name' => empty($config['localName']) ? "" : $config['localName'],
            'offsite_name' => empty($config['offsiteName']) ? "" : $config['offsiteName'],
            'warning' => array(
                'task_show' => empty($config['taskAlertCheck']) ? false : $config['taskAlertCheck'],
                'system_show' => empty($config['systemAlertCheck']) ? false : $config['systemAlertCheck'],
            )
        );
        return json_encode($info); 
    }
    
    /**
     * 概览
     * @param unknown $params
     */
    public function getOverView($params){
        $info = array();
        //累计运行时长和单位
        $runningInfo = $this->getRunningTimeInfo();
        $info['running_time'] = $runningInfo['running_time'];
        $info['running_time_unit'] = $runningInfo['running_time_unit'];
        
        //累计保护数据和单位
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['UNKNOWN']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        //今日累计保护数据
        //备份数据:文件(1)，数据库，操作系统，虚拟机(1)，NAS(1)，实时容灾
        //副本数据:虚拟机，文件，操作系统，数据库
        //归档数据:虚拟机
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $taskTypeArr = array(
            $taskTypeConf['BACKUP'], $taskTypeConf['DB_BACKUP'], $taskTypeConf['OS_BACKUP'], $taskTypeConf['VOL_CDP_BACKUP'],
            $taskTypeConf['BACKUP_COPY'], $taskTypeConf['FILE_BACKUP_COPY'], $taskTypeConf['DB_BACKUP_COPY'], $taskTypeConf['OS_BACKUP_COPY'],
            $taskTypeConf['VOL_CDP_TAKEOVER'], $taskTypeConf['ARCHIVE']
        );
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $info['protect_data_today'] = $valueAndUnit['value'];          
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];     
        
        //节点状态
        $nodeAndStorageInfo = $this->getNodeAndStorageInfo();
        $info = array_merge($info, $nodeAndStorageInfo);
        
        
        //获取显示哪些模块图标
        $moduels = $this->getLicenseModuleInfo();
        $returnMoudles = array(
            "vm" => $moduels['vm'],
            "file" => $moduels['fs'],
            "database" => $moduels['db'],
            "nas" => $moduels['nas'],
            "os" => $moduels['os'],
            "cdp" => $moduels['volcdp'],
            "dbcdp" => $moduels['dbcdp'],
            "m365" => $moduels['office365'],
        );
        $info['module_show'] = $returnMoudles;
        
        
        
        //模块状态,通过当前任务来判断
        $moduleTaskStatus = $this->getModuleTaskStatus($moduels);
        $info = array_merge($info, $moduleTaskStatus);
        return json_encode($info);
    }
    
    /**
     * 获取每个模块任务的状态
     * @param unknown $modules  系统授权的模块
     */
    private function getModuleTaskStatus($modules){
        $moduleTypeConf = Xphp::$_config['MODULE_TYPE'];
        $info = array();
        $info['vm_status'] = $this->getSomeModuleTaskStatus($modules['vm'], $moduleTypeConf['VM']);
        $info['file_status'] = $this->getSomeModuleTaskStatus($modules['fs'], $moduleTypeConf['FS']);
        $info['database_status'] = $this->getSomeModuleTaskStatus($modules['db'], $moduleTypeConf['DB']);
        $info['os_status'] = $this->getSomeModuleTaskStatus($modules['os'], $moduleTypeConf['OS']);
        $info['cdp_status'] = $this->getSomeModuleTaskStatus($modules['volcdp'], $moduleTypeConf['VOL_CDP']);
        $info['nas_status'] = $this->getSomeModuleTaskStatus($modules['nas'], $moduleTypeConf['NAS']);
        
        return $info;
    }
        
    /**
     * 获取指定模块的任务状态
     * @param boolean $moduleFlag   是否有这个模块
     * @param int $moduleType       模块类型
     */
    private function getSomeModuleTaskStatus($moduleFlag, $moduleType){
        $threeStatus = Xphp::$_config['THREE_STATUS'];
        if(!$moduleFlag){
            //如果没有这个模块，直接返回正常
            return $threeStatus['normal'];
        }
        $sql = "select task_status from bd_task where module_type = ?";
        $data = $this->dbSelect($sql, array($moduleType));
        
        $total = 0;
        $normal = 0;
        $abnormal = 0;
        $taskStatusConf = Xphp::$_config['TASKSTATUS'];
        $abnormalTaskStatus = array(
            $taskStatusConf['NETWORK_FAULT'],   //network fault
            $taskStatusConf['ABNORMAL'],        //task compeleted but abnormal
            $taskStatusConf['ERROR'],           //task is error
        );
        foreach ($data as $d){
            $total++;
            //如果任务状态是异常的：在异常任务状态列表中
            if(in_array($d['task_status'], $abnormalTaskStatus)){
                $abnormal++;
            }else{
                $normal++;
            }
        }
        
        $status = $this->getThreeStatus($total, $normal, $abnormal);
        
        return $status;
    }
    
    /**
     * 获取指定任务类型的任务状态
     * @param boolean $moduleFlag   是否有这个模块
     * @param array $taskTypeArr       任务类型
     */
    private function getSomeTaskTypeTaskStatus($moduleFlag, $taskTypeArr){
        $threeStatus = Xphp::$_config['THREE_STATUS'];
        if(!$moduleFlag){
            //如果没有这个模块，直接返回正常
            return $threeStatus['normal'];
        }
        $sql = "select task_status from bd_task where task_type in (?)";
        $data = $this->dbSelect($sql, array(implode(",", $taskTypeArr)));
        
        $total = 0;
        $normal = 0;
        $abnormal = 0;
        $taskStatusConf = Xphp::$_config['TASKSTATUS'];
        $abnormalTaskStatus = array(
            $taskStatusConf['NETWORK_FAULT'],   //network fault
            $taskStatusConf['ABNORMAL'],        //task compeleted but abnormal
            $taskStatusConf['ERROR'],           //task is error
        );
        foreach ($data as $d){
            $total++;
            //如果任务状态是异常的：在异常任务状态列表中
            if(in_array($d['task_status'], $abnormalTaskStatus)){
                $abnormal++;
            }else{
                $normal++;
            }
        }
        
        $status = $this->getThreeStatus($total, $normal, $abnormal);
        
        return $status;
    }
    
    /**
     * 获取节点和存储状态
     */
    private function getNodeAndStorageInfo(){
        $info = array();
        //获取节点状态：1为部分有问题为黄色，2为全部有问题为红色，0为正常
        //因为这里只判断是否是部分或全部有问题，所以使用分组来看
        // 节点的个数
        $sql = "select node_uuid from bd_node";
        $sqlData = $this->dbSelect($sql);
        $sqlCount = count($sqlData);

        $sql = "select bms.online_flag from bd_module_server bms, bd_node bn where bn.node_uuid = bms.node_uuid and bms.online_flag = ? group by bms.node_uuid;";
        $data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET']));
        $abnormalStatus = count($data);

        if($sqlCount == $abnormalStatus){
            // 全部异常
            $nodeStatus = Xphp::$_config['THREE_STATUS']['all_abnormail'];
        }else if($sqlCount > $abnormalStatus && $abnormalStatus !== 0){
            // 部分异常
            $nodeStatus = Xphp::$_config['THREE_STATUS']['some_abnormal'];
        }else if($abnormalStatus == 0){
            // 无异常
            $nodeStatus = Xphp::$_config['THREE_STATUS']['normal'];
        }
        
        //获取存储状态，同时获取类型
        $sql = "select storage_type, status, mount_flag from bd_storage_resource where lan_free_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $cloudStorageNum = 0;      //云存储个数
        $remoteStorageNum = 0;     //异地备份系统个数
        $allStatus = count($data);
        $online = 0;
        $offline = 0;
        foreach ($data as $d){
            if(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'] == $d['storage_type']){
                $cloudStorageNum++;
            }
            if(Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'] == $d['storage_type']){
                $remoteStorageNum++;
            }
            if(Xphp::$_config['FLAG']['SET'] == $d['status']){
                $online++;
            }else{
                $offline++;
            }
        }
        $storageStatus = $this->getThreeStatus($allStatus, $online, $offline);
        
        /**
         * 根据节点状态生成存储状态
         * 节点全部有问题2，存储和节点状态一致2
         * 节点部分有问题或全部正常，1或0， 存储状态取存储的状态
         */
        $threeConf = Xphp::$_config['THREE_STATUS'];
        if($nodeStatus == $threeConf['all_abnormail']){
            $storageStatus = $threeConf['all_abnormail'];
        }
        
        $info['node_status'] = $nodeStatus;
        $info['save_status'] = $storageStatus;
        $info['cloud_show'] = $cloudStorageNum > 0 ? true : false;
        $info['offsite_show'] = $remoteStorageNum > 0 ? true : false;
        
        return $info;
    }
    
    /**
     * 得到三态状态
     * @param int $totalNum     总计个数
     * @param int $normalNum    正常个数
     * @param int $abnormailNum 异常个数
     */
    private function getThreeStatus($totalNum, $normalNum, $abnormailNum){
        $threeConf = Xphp::$_config['THREE_STATUS'];
        if(0 == $totalNum){
            //总数为0，直接返回全部正常
            return $threeConf['normal'];
        }   
        if($totalNum == $normalNum){
            //总数和正常个数一样，返回全部正常
            return $threeConf['normal'];
        }
        if($totalNum == $abnormailNum){
            //总数和异常个数一样，返回全部异常
            return $threeConf['all_abnormail'];
        }
        
        //其他情况，返回部分异常
        return $threeConf['some_abnormal'];
    }
    
    /**
     * 获取累计运行时间，单位为天
     */
    private function getRunningTimeInfo(){
        $info = array();
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $runTime = intval($value/24);
        //累计运行时长和单位
        $info['running_time'] = $runTime;
        $info['running_time_unit'] = Xphp::$_lang['WEB_UTILS_DAY'];
        
        return $info;
    }
    
    /**
     * 获取累计保护数据，后期可能会增加类型
     */
    /**
     * 
     * @param int $moduleType   0的时候获取所有数据，其他获取指定模块的数据
     * @return array('protect_data'=>"",'protect_data_unit'=>"")
     */
    private function getProtectDataInfo($moduleType)
    {
        $info = array();
        $sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data, db_data, os_data, copy_data, archive_data ,nas_data ,vol_cdp_data, m365_data from bd_user_extension ";
        $data = $this->dbSelect($sql);
        $total = 0;
        $moduleTypeConf = Xphp::$_config['MODULE_TYPE'];
        foreach ($data as $d) {
            if ($moduleType == 0) {
                $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] + $d['kvm_data'] + $d['hyperv_data'] +
                    $d['fs_data'] + $d['db_data'] + $d['os_data'] + $d['copy_data'] + $d['archive_data'] + $d['nas_data'] + $d['vol_cdp_data'] + $d['m365_data'];
            } else if ($moduleTypeConf['VM'] == $moduleType) {
                //虚拟机
                $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] + $d['kvm_data'] + $d['hyperv_data'];
            } else if ($moduleTypeConf['FS'] == $moduleType) {
                //文件
                $total += $d['fs_data'];
            } else if ($moduleTypeConf['DB'] == $moduleType) {
                //数据库
                $total += $d['db_data'];
            } else if ($moduleTypeConf['OS'] == $moduleType) {
                //操作系统
                $total += $d['os_data'];
            } else if ($moduleTypeConf['VOL_CDP'] == $moduleType) {
                //卷实时
                $total += $d['vol_cdp_data'];
            } else if ($moduleTypeConf['NAS'] == $moduleType) {
                //NAS
                $total += $d['nas_data'];
            } else if ($moduleTypeConf['BACKUP_COPY_CLIENT'] == $moduleType) {
                //副本归档客户端
                $total += $d['copy_data'];
            } else if ($moduleTypeConf['M365'] == $moduleType) {
                //m365
                $total += $d['m365_data'];

            }
        }
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($total, true);
        //累计保护数据和单位
        $info['protect_data'] = $valueAndUnit['value'];
        $info['protect_data_unit'] = $valueAndUnit['unit'];

        return $info;
    }
    
    /**
     * 除了概览以外的数据
     * @param unknown $params
     */
    public function getOtherView($params){
        $info = array();
        //先获取系统授权的模块
        $modules = $this->getLicenseModuleInfo();
        $info['vm_data'] = $this->getVmViewInfo($modules);
        $info['file_data'] = $this->getFsViewInfo($modules);
        $info['database_data'] = $this->getDbViewInfo($modules);
        $info['nas_data'] = $this->getNasViewInfo($modules);
        $info['os_data'] = $this->getOsViewInfo($modules);
        $info['cdp_data'] = $this->getVolcdpViewInfo($modules);
        $info['copy_data'] = $this->getCopyViewInfo($modules);
        $info['dbcdp_data'] = $this->getDBCDPViewInfo($modules);
        $info['office365_data'] = $this->getOffice365ViewInfo($modules);
        $info['publicCloud_data'] = $this->getpublicCloudViewInfo($modules);
        return json_encode($info);
    }
    
    /**
     * 获取虚拟机模块统计信息
     * @param array $modules
     */
    private function getVmViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['vm']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        
        //有哪些虚拟机类型
        $sql = "select hypervisor_type from vm_vcenter group by hypervisor_type";
        $data = $this->dbSelect($sql);
        $hypervisorArr = array();
        foreach ($data as $d){
            $hypervisorArr[] = $d['hypervisor_type'];
        }
        $info['vm_type'] = $hypervisorArr;
        
        //虚拟机总数
        $sql = 'select count(vt.tree_id) as total from vm_tree vt, vm_vcenter vv where vv.hypervisor_type not in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')'.'
        and vt.display_mode = ? and vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid and vv.user_uuid  =?';
        $sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM'],Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info['total_vm_num'] = $data[0]['total'];
        
        //受保护虚拟机个数
        $sql = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv, bd_task bt, vm_machine vm
                        where vml.vcenter_uuid = vm.vcenter_uuid and vml.vm_uuid = vm.vm_uuid and bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? and vv.user_uuid  =? and bt.sub_module_type = ?";
        $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP'],Xphp::$_user['useruuid'], Xphp::$_config['VM_SUB_MODULE']['VM']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info['protected_vm_num'] = $data[0]['protect_vms'];

        //虚拟机备份数据
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $this->pGetProtectData(Xphp::$_config['MODULE_TYPE']['VM'], $utils, Xphp::$_config['VM_SUB_MODULE']['VM']);
        $info['backup_data'] = $utils->calSizeToValueAndUnit($valueAndUnit['size'],true)['value'];     //备份数据总量

        $info['backup_data_unit'] = $utils->calSizeToValueAndUnit($valueAndUnit['size'],true)['unit'];  //备份数据总量的单位
        
        //虚拟机累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['VM']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $todayInfo = array();
        //当日备份数据:虚拟机,按模块获取
        $utils = Xphp::instance('Utils');
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = 'select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and submodule_type not in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) .')'.' and task_type in (?) and  start_time >= ? and finish_time < ?';
        $data = $this->dbSelect($sql,array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['TASKTYPE']['BACKUP'],$todayStart,$todayEnd));
        $backupSize = $data[0]['size'];  ////备份数据真实量
       
        
        //当日副本数据:虚拟机
        $sql = 'select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and submodule_type in (?) and task_type in (?) and  start_time >= ? and finish_time < ?';
        $data = $this->dbSelect($sql,array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['VM_SUB_MODULE']['VM'],Xphp::$_config['TASKTYPE']['BACKUP_COPY'],$todayStart,$todayEnd));
        $copySize = $data[0]['size'];  //副本数据真实量         
        
        //当日归档数据:虚拟机
        $sql = 'select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and submodule_type in (?) and task_type in (?) and  start_time >= ? and finish_time < ?';
        $data = $this->dbSelect($sql,array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['VM_SUB_MODULE']['VM'],Xphp::$_config['TASKTYPE']['ARCHIVE'],$todayStart,$todayEnd));
        $archiveSize = $data[0]['size']; //归档数据真实量         
        
        $todayProtectData = $backupSize + $copySize + $archiveSize;
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($todayProtectData, true);
        
        //虚拟机今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        
        //虚拟机副本数据
        $sqlVm = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type in (?) and sub_module_type != ? and module_type = ?";
        $data = $this->dbSelect($sqlVm,array(Xphp::$_config['TASKTYPE']['BACKUP_COPY'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['MODULE_TYPE']['VM']));
        $valueAndUnit = $utils->calSizeToValueAndUnit($data[0]['size'], true);
        //如果没有副本copy就没有
        if($data[0]['num'] > 0){
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['value'],      //虚拟机副本数据
                'copy_data_unit' => $valueAndUnit['unit'],  //虚拟机副本数据单位
                'copy_point' => $data[0]['num'],       //虚拟机副本点
            );
        }
        
        //虚拟机归档数据
        $sqlVm = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type in (?) and sub_module_type != ? and module_type = ?";
        $data = $this->dbSelect($sqlVm,array(Xphp::$_config['TASKTYPE']['ARCHIVE'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['MODULE_TYPE']['VM']));
        $valueAndUnit = $utils->calSizeToValueAndUnit($data[0]['size'], true);
        //如果没有归档,archive就没有
        if($data[0]['num']){
            //有时间点，给copy赋值
            $info['archive'] = array(
                'archive_data' => $valueAndUnit['value'],      //归档数据
                'archive_data_unit' => $valueAndUnit['unit'],  //归档数据单位
                'archive_point' => $data[0]['num'],       //归档点
            );
        }
        return $info;
    }
    
    /**
     * 获取文件模块统计信息
     * @param array $modules
     */
    private function getFsViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['fs']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4);";
        $data = $this->dbSelect($sql);
        $info['total_file_num'] = $data[0]['num'];
        
        //受保护主机个数
        $info['protected_file_num'] = $this->pGetProtectClient(Xphp::$_config['MODULE_TYPE']['FS'], Xphp::$_config['TASKTYPE']['BACKUP']);
        
        //文件备份数据
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['FS']);
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleTypeArr);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位
        
        //文件累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['FS']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $todayInfo = array();
        //当日备份数据:文件,按模块获取
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['FS']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr,2,0,0);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量
        
        //当日副本数据:文件
        $taskTypeArr = array($taskTypeConf['FILE_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量
        
        //当日归档数据:文件暂无归档
        
        $todayProtectData = $todayInfo['backup_data'];
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($todayProtectData, true);
        
        //文件今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        
        //文件副本数据
        $taskTypeArr = array($taskTypeConf['FILE_BACKUP_COPY']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        //如果没有副本copy就没有
        if($valueAndUnit['num'] > 0){
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['value'],      //副本数据
                'copy_data_unit' => $valueAndUnit['unit'],  //副本数据单位
                'copy_point' => $valueAndUnit['num'],       //副本点
            );
        }
        
        //文件暂无归档
        
        return $info;
    }
    
    /**
     * 获取数据库模块统计信息
     * @param unknown $modules
     * agent_type == 3 是nas和其它主机去区分，查找主机要排除agent_type == 3
     */
    private function getDbViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['db']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        //有哪些数据库类型 
        $sql = "select app_type from bd_agent_app group by app_type";
        $data = $this->dbSelect($sql);
        $databaseTypeArr = array();
        foreach ($data as $d){
            $databaseTypeArr[] = $d['app_type'];
        }
        $info['database_type'] = $databaseTypeArr;
        
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4);";
        $data = $this->dbSelect($sql);
        $info['total_database_num'] = $data[0]['num'];
        
        //受保护主机个数
        $info['protected_database_num'] = $this->pGetProtectClient(Xphp::$_config['MODULE_TYPE']['DB'], Xphp::$_config['TASKTYPE']['DB_BACKUP']);
        
        //数据库备份数据
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['DB']);
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleTypeArr);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位
        
        //数据库累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['DB']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $todayInfo = array();
        //当日备份数据:数据库,按模块获取
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['DB']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr,0,0,0);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量
        
        //当日副本数据:数据库
        $taskTypeArr = array($taskTypeConf['DB_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量
        
        //当日归档数据:数据库暂无归档
        
        $todayProtectData = $todayInfo['backup_data'] + $todayInfo['copy_data'];
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($todayProtectData, true);
        
        //数据库今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        
        //数据库副本数据
        $taskTypeArr = array($taskTypeConf['DB_BACKUP_COPY']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        //如果没有副本copy就没有
        if($valueAndUnit['num'] > 0){
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['value'],      //副本数据
                'copy_data_unit' => $valueAndUnit['unit'],  //副本数据单位
                'copy_point' => $valueAndUnit['num'],       //副本点
            );
        }
        
        //数据库暂无归档
        
        return $info;
    }
    
    /**
     * 获取操作系统模块统计信息
     * @param unknown $modules
     */
    private function getOsViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['os']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4);";
        $data = $this->dbSelect($sql);
        $info['total_os_num'] = $data[0]['num'];
        
        //受保护主机个数
        $info['protected_os_num'] = $this->pGetProtectClient(Xphp::$_config['MODULE_TYPE']['OS'], Xphp::$_config['TASKTYPE']['OS_BACKUP']);
        
        //操作系统备份数据
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['OS']);
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleTypeArr);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位
        
        //操作系统累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['OS']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $todayInfo = array();
        //当日备份数据:操作系统,按模块获取
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['OS']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr,36,49,50);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量
        
        //当日副本数据:操作系统
        $taskTypeArr = array($taskTypeConf['OS_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量
        
        //当日归档数据:操作系统暂无归档
        
        $todayProtectData = $todayInfo['backup_data'];
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($todayProtectData, true);
        
        //操作系统今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        
        //操作系统副本数据
        $taskTypeArr = array($taskTypeConf['OS_BACKUP_COPY']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        //如果没有操作系统copy就没有
        if($valueAndUnit['num'] > 0){
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['value'],      //副本数据
                'copy_data_unit' => $valueAndUnit['unit'],  //副本数据单位
                'copy_point' => $valueAndUnit['num'],       //副本点
            );
        }
        
        //操作系统暂无归档
        return $info;
    }
    
    /**
     * 获取卷cdp模块统计信息
     * @param unknown $modules
     */
    private function getVolcdpViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['volcdp']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4);";
        $data = $this->dbSelect($sql);
        $info['total_cdp_num'] = $data[0]['num'];
        
        //受保护主机个数
        $sql = "SELECT count(task_uuid) as total from bd_task where  delete_flag = ? and task_type = ? and module_type = ? ";
        $sqlParams = array(
            Xphp::$_config['FLAG']['UNSET'],
            Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'],
            Xphp::$_config['MODULE_TYPE']['VOL_CDP'],
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $info['protected_cdp_num'] = $data[0]['total'];
        
        //累计实时容灾数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['VOL_CDP']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        //今日实时容灾累计保护数据
        $utils = Xphp::instance('Utils');
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = "select sum(data_flow) as size from cdp_vol_backup_agent_minute_level_data_flow 
                where backup_start_timestamp >= ? and backup_end_timestamp < ?";
        $sqlParams = array($todayStart, $todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $size = $data[0]['size'];
        $valueAndUnit = $utils->calSizeToValueAndUnit($size, true);
        if(empty($size)){
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        
        return $info;
    }
    
    /**
     * 获取数据库cdp模块统计信息
     * @param unknown $modules
     * @return boolean[]|boolean[]|number[]
     */
    private function getDBCDPViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['dbcdp']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        //获取生产主机和备份主机个数
        $sql = "select host_type from cdp_db_host";
        $data = $this->dbSelect($sql);
        $product = 0;   //生产主机
        $stantby = 0;   //备份主机
        foreach ($data as $d){
            if(intval($d['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['product']){
                $product ++;
            }else{
                $stantby ++;
            }
        }
        //获取任务总数和运行个数
        $sql = "select task_status from bd_task where module_type = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['OEM_DBCDP']));
        $runNum = 0;
        $taskNum = count($data);
        foreach ($data as $d){
            if(intval($d['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']){
                $runNum ++;
            }
        }
        //生产主机个数
        $info['product_host'] = $product;
        //备份主机个数
        $info['standby_host'] = $stantby;
        //任务个数
        $info['task_num'] = $taskNum;
        //任务运行个数
        $info['task_run_num'] = $runNum;
        
        return $info;
    }
    
    /**
     * 获取nas模块统计信息
     * @param unknown $modules
     */
    private function getNasViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['nas']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        //NAS设备总数
        $sql = "select count(id) as num from nas_storage_resource;";
        $data = $this->dbSelect($sql);
        $info['total_nas_num'] = $data[0]['num'];
        
        //受保护主机个数
        $sql = "select count(distinct nt.nas_uuid) as total from bd_task bt, nas_task nt where bt.task_uuid = nt.task_uuid  and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $sqlParams = array(
            Xphp::$_config['FLAG']['UNSET'],
            Xphp::$_config['MODULE_TYPE']['NAS'],
            Xphp::$_config['TASKTYPE']['BACKUP'],
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $info['protected_nas_num'] = $data[0]['total'];
        
        //NAS备份数据
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['NAS']);
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleTypeArr);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位
        
        //NAS累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['NAS']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $todayInfo = array();
        //当日备份数据:NAS,按模块获取
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['NAS']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr,2,0,0);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量
        
        //当日副本数据:NAS 这里区分不了，暂无
        
        //当日归档数据:NAS暂无归档
        
        $todayProtectData = $todayInfo['backup_data'];
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($todayProtectData, true);
        
        //NAS今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        
        //NAS副本数据
        $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type in (?) and module_type = ?";
        $data = $this->dbSelect($sql,array($taskTypeConf['BACKUP_COPY'],Xphp::$_config['MODULE_TYPE']['NAS']));
        $valueAndUnit = $utils->calSizeToValueAndUnit($data[0]['size'], true);
        $valueAndUnit['num'] = $data[0]['num'];
        if($valueAndUnit['num'] > 0){
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['value'],      //副本数据
                'copy_data_unit' => $valueAndUnit['unit'],  //副本数据单位
                'copy_point' => $valueAndUnit['num'],       //副本点
            );
        }
        //NAS暂无归档
        return $info;
    }
    
    /**
     * 获取副本模块统计信息
     * @param unknown $modules
     */
    private function getCopyViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['copy']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        //副本数据
        $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type = ?";
        $data = $this->dbSelect($sql,array(Xphp::$_config['TASKTYPE']['BACKUP_COPY']));
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($data[0]['size'],true);
        $info['copy_data'] = $valueAndUnit['value'];     //数据总量
        $info['copy_data_unit'] = $valueAndUnit['unit']; //数据总量的单位
        $info['copy_point_num'] = $data[0]['num'];  //点个数
        
        //累计副本数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        
        //今日副本累计保护数据
        $taskType1 = Xphp::$_config['TASKTYPE']['BACKUP_COPY'];
        $taskType2 = Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'];
        $taskType3 = Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'];
        $taskType4 = Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY'];
        $taskType5 = Xphp::$_config['TASKTYPE']['NAS_BACKUP_COPY'];
        $utils = Xphp::instance('Utils');
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = "select sum(total_object_write_size) as size from bd_history_task where task_type in (?,?,?,?,?) and start_time >= ? and finish_time < ?;";
        $data = $this->dbSelect($sql,array($taskType1,$taskType2,$taskType3,$taskType4,$taskType5,$todayStart,$todayEnd));
        $size = $data[0]['size'];
        $valueAndUnit = $utils->calSizeToValueAndUnit($size, true);
        $info['protect_data_today'] = $valueAndUnit['value'];          //数据真实量
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];          //数据真实量
        
        //各模块副本详情，虚拟机，文件，操作系统，数据库
        $info['copy_details'] = $this->getAllModuleCopyData();
        
        return $info;
    }

    /**
     * 获取office365模块统计信息
     * @param unknow $modules
     */
    private function getOffice365ViewInfo($modules) {
        $info = array('show' => true);
        if(false === $modules['office365']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        // m365的类型
        $sql = "select m365_type from m365_backup_timepoint group by m365_type";
        $data = $this->dbSelect($sql,array());
        $m365TypeArr = array();
        foreach ($data as $d){
            $m365TypeArr[] = $d['m365_type'];
        }
        // m365的类型个数
        $info['office365_type'] = $m365TypeArr;

        // 受保护用户
        $userNum = 0;
        //获取m365所有备份任务
        $sql = "select task_uuid from m365_task where m365_type = 1";
        $taskList =  $this->dbSelect($sql);
        if (!empty($taskList)) {
            foreach ($taskList as $task) {
                //获取每个任务最新的时间点,解析出每个任务的最新时间点的授权用户数，然后相加
                $userNum += $this->getNewestTimepoit($task['task_uuid']);
            }
        }
        //受保护用户
        $info['protected_user_num'] = $userNum;

        //m365备份数据
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['M365']);
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleTypeArr);
        $info['backup_data'] = $valueAndUnit['value'];  //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //m365累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(Xphp::$_config['MODULE_TYPE']['M365']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        //当日备份数据:M365,按模块获取
        $moduleTypeArr = array(Xphp::$_config['MODULE_TYPE']['M365']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr,0,0,0);
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        return $info;
    }

    /**
     * 获取公有云模块统计信息
     * @params unknow $modules
     */
    private function getpublicCloudViewInfo($modules){
        $info = array('show' => true);
        if(false === $modules['publicCloud']){
            //没有这个模块的授权，直接返回
            $info['show'] = false;
            return $info;
        }
        // 公有云的类型
        $sql = "SELECT hypervisor_type FROM vm_vcenter WHERE hypervisor_type in (?) GROUP BY hypervisor_type";
        $data = $this->dbSelect($sql,array(implode(",", Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])));
        $publicCloudTypeArr = array();
        foreach ($data as $d){
            $publicCloudTypeArr[] = $d['hypervisor_type'];
        }
        // publicCloud的类型
        $info['publicCloud_type'] = $publicCloudTypeArr;
        // 实例总个数
        $sql = 'select count(vt.tree_id) as total from vm_tree vt, vm_vcenter vv where vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')'.'  and vv.user_uuid  =?';
        $dataTotal = $this->dbSelect($sql, array(Xphp::$_config['VM_TREE_TYPE']['VM'],Xphp::$_user['useruuid']));
        // 受保护实例
        $protectsql = 'select count(distinct vml.machine_id) as protectNum from bd_task bt,vm_machine_list vml, vm_vcenter vv, vm_tree vt where bt.task_uuid = vml.task_uuid and  vml.vcenter_uuid = vv.vcenter_uuid and vml.vm_uuid = vt.uuid and vml.vcenter_uuid = vt.vcenter_uuid and bt.task_type =? and bt.module_type = ? and bt.sub_module_type = ? and vv.user_uuid  = ?;';
        $protectCount = $this->dbSelect($protectsql, array(Xphp::$_config['TASKTYPE']['BACKUP'],Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_user['useruuid']));
        $utils = Xphp::instance('Utils');
        // 副本数据
        $copyArchiveSql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type in (?) and sub_module_type = ? and module_type = ?";
        $copyData = $this->dbSelect($copyArchiveSql,array(Xphp::$_config['TASKTYPE']['BACKUP_COPY'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['MODULE_TYPE']['VM']));
        $copy_data_Size = $copyData[0]['size'];
        $copy_data_Num = $copyData[0]['num'];
        $info['copy_data'] = $utils->calSizeToValueAndUnit($copy_data_Size, true);
        $info['copy_num'] = $copy_data_Num;     //副本时间点个数
        // 归档
        $archiveData = $this->dbSelect($copyArchiveSql,array(Xphp::$_config['TASKTYPE']['ARCHIVE'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['MODULE_TYPE']['VM']));
        $archive_data_Size = $archiveData[0]['size'];
        $archive_data_Num = $archiveData[0]['num'];
        $info['archive_data'] = $utils->calSizeToValueAndUnit($archive_data_Size, true);
        $info['archive_num'] = $archive_data_Num;     //归档时间点个数
        // 今日保护实例数据
        //当日备份数据:公有云,按模块获取
        $utils = Xphp::instance('Utils');
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = 'select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and submodule_type in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) .')'.' and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['TASKTYPE']['BACKUP'],$todayStart,$todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $backupSize = $data[0]['size'];   //备份数据真实量       

        //当日副本数据:公有云
        $sql = 'select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and submodule_type in (?) and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['TASKTYPE']['BACKUP_COPY'],$todayStart,$todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $copySize = $data[0]['size'];  //副本数据真实量         

        //当日归档数据:公有云
        $sql = 'select sum(total_object_write_size) as size from bd_history_task where module_type in (?) and submodule_type in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) .')'.' and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['TASKTYPE']['ARCHIVE'],$todayStart,$todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $archiveSize = $data[0]['size'];  //归档数据真实量         
        $todayProtectData = $backupSize + $copySize + $archiveSize;
        $utils = Xphp::instance('Utils');
        $valueAndUnit = $utils->calSizeToValueAndUnit($todayProtectData, true);

        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        $info['total_publicCloud_num'] = $dataTotal[0]['total'];
        $info['protected_publicCloud_num'] = $protectCount[0]['protectNum'];
        $info['backupDataSize'] = $this->pGetProtectData(Xphp::$_config['MODULE_TYPE']['VM'], $utils, Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']); // 实例备份数据
        return $info;
    }

    /**
     * 根据taskuuid获取授权用户数
     * @param string $taskUuid 任务uuid
     * @return int 用户数
     */
    private function getNewestTimepoit($taskUuid)
    {
        $sql = "select mbt.organization_info from m365_backup_timepoint mbt, bd_backup_timepoint bbt where bbt.available_flag = 1
        and bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.task_uuid = ? order by timepoint desc limit 0 , 1";
        $data =  $this->dbSelect($sql, array($taskUuid));
        if (!empty($data)) {
            foreach ($data as $d) {
                $organizationInfo = json_decode($d['organization_info'], true);
                return intval($organizationInfo['current_reserverd_user_num']);
            }
        }
    }
    
    /**
     * 获取副本分模块详情信息
     * 虚拟机，文件，操作系统，数据库
     */
    private function getAllModuleCopyData(){
        $utils = Xphp::instance('Utils');
        $info = array();
        
        //按照任务类型来统计副本数据，按照模块类型返回数据
        $copyDataTaskTypes = array(
            Xphp::$_config['MODULE_TYPE']['VM'] => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],          //虚拟机副本 公有云副本 通过sub_module_type区分
            Xphp::$_config['MODULE_TYPE']['FS'] => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],     //文件副本
            Xphp::$_config['MODULE_TYPE']['OS'] => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],       //操作系统副本
            Xphp::$_config['MODULE_TYPE']['DB'] => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],       //数据库副本
            Xphp::$_config['MODULE_TYPE']['NAS'] => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],       //NAS副本
            Xphp::$_config['MODULE_TYPE']['PUBLIC_CLOUD'] => Xphp::$_config['TASKTYPE']['BACKUP_COPY'],       //公有云副本
        );

        foreach ($copyDataTaskTypes as $key => $value){
            // 数据总量
            if($key == Xphp::$_config['MODULE_TYPE']['PUBLIC_CLOUD']){
                // 公有云
                $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect($sql,array(Xphp::$_config['MODULE_TYPE']['VM'],$value,Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
                $taskNumSql = "select count(task_uuid) as task_num from bd_task where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect($taskNumSql,array(Xphp::$_config['MODULE_TYPE']['VM'],$value,Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
            }else{
                $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type != ?";
                $data = $this->dbSelect($sql,array($key,$value,Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
                $taskNumSql = "select count(task_uuid) as task_num from bd_task where module_type = ? and task_type = ? and sub_module_type != ?";
                $taskNumSqlData = $this->dbSelect($taskNumSql,array($key,$value,Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
            }
            $data_Size = $data[0]['size'];
            $data_Num = $data[0]['num'];
            $task_Num = $taskNumSqlData[0]['task_num'];
            $valueAndUnit = $utils->calSizeToValueAndUnit($data_Size, true);
            $valueAndUnit['num'] = $data_Num;     //时间点个数
            $valueAndUnit['task_num'] = $task_Num;  //副本任务个数
            $info[] = array(
                'type' => $key,                             //对应模块类型
                'copy_data' => $valueAndUnit['value'],      //数据总量
                'copy_data_unit' => $valueAndUnit['unit'],  //数据总量的单位
                'copy_point' => $valueAndUnit['num'],       //点个数
                'copy_task_num' => $valueAndUnit['task_num'],    //副本任务个数
            );
        }
        return $info;
    }
    
    /**
     * 获取系统授权的模块
     * @return module['vm','fs','db','os','volcdp','nas','copy',]
     */
    private function getLicenseModuleInfo(){
        $sql = "select extension from bd_license ";
        $data = $this->dbSelect($sql);
        $extension = array();
        $utils = Xphp::instance('Utils');
        if(!empty($data)){
            $extension = json_decode($utils->decrypt($data[0]['extension']), true);
        }
        $pagesArr = $extension['p'];
        $moduleFlagArr = array(
            'vm' => false,
            'fs' => false,
            'db' => false,
            'os' => false,
            'volcdp' => false,
            'nas' => false,
            'copy' => false,
            'dbcdp' => false,
            'office365' => false,
            'publicCloud' => false,
        );
        if(in_array('vmprotect', $pagesArr)){
            $moduleFlagArr['vm'] = true;
        }
        if(in_array('fileprotect', $pagesArr)){
            $moduleFlagArr['fs'] = true;
        }
        if(in_array('db_protect', $pagesArr)){
            $moduleFlagArr['db'] = true;
        }
        if(in_array('os_protect', $pagesArr)){
            $moduleFlagArr['os'] = true;
        }
        if(in_array('vol_cdp_protect', $pagesArr)){
            $moduleFlagArr['volcdp'] = true;
        }
        if(in_array('nas_protect', $pagesArr)){
            $moduleFlagArr['nas'] = true;
        }
        if(in_array('dbprotect', $pagesArr)){
            $moduleFlagArr['dbcdp'] = true;
        }
        if(in_array('copy', $extension['f'])){
            $moduleFlagArr['copy'] = true;
        }
        if(in_array('office365_protect', $pagesArr)){
            $moduleFlagArr['office365'] = true;
        }
        if(in_array('awsprotect', $pagesArr)){
            $moduleFlagArr['publicCloud'] = true;
        }
        return $moduleFlagArr;
    }
    
    /**
     * 节点统计
     * @author liushuai@vinchin.com
     * @param unknown $params
     */
    public function getNodeMonitor($params){
        //得到所有节点
        $nodeList = $this->getNodeUUid();
        $info = array();
        foreach ($nodeList as $eachNode){
            $each_node_uuid = $eachNode['node_uuid'];
            $data = $this->getNodeData($params,$each_node_uuid);
            $info['list'][] = $data;
        }

        return json_encode($info);
    }
    
    
    /**
     * 根据节点获取数据
     * @param unknown $params
     * @param unknown $node_uuid
     * @return array|number[]|unknown[]|unknown[][][]|unknown[][][][]|fetchAll()[]|NULL[]|fetchAll()[][][]
     */
    public function getNodeData($params,$node_uuid){
        $count = $params['count'];      //本次取的条数
        $this->paramsCheck($count);
        $sql = "select node_uuid, monitor_time, cpu_percentage, ram_percentage, system_load_1, system_load_5,
                system_load_15, iops_read, iops_write, bps_read, bps_write, network_in, network_out, details
                from bd_system_monitor where node_uuid = ?
                order by monitor_time desc
                limit 0,?";
        
        $result = $this->dbSelect($sql,array($node_uuid,$count));
        $info = array();
        if(empty($result)){
            return $info;
        }
        $utils = Xphp::instance('Utils');
        
        
        
        $netData = array();
        $bpsData = array();
        $iopsData = array();
        $SystemLoadData = array();
        foreach ($result as $each){
            $network_receive = $utils->calSizeToValueAndUnit($each['network_in']*1024,true);
            $network_transmit = $utils->calSizeToValueAndUnit($each['network_out']*1024,true);
            $bps_read = $utils->calSizeToValueAndUnit($each['bps_read']*1024,true);
            $bps_write = $utils->calSizeToValueAndUnit($each['bps_write']*1024,true);
            $netData[] = array(
                'network' => array(
                    'receive' => $each['network_in'],
                    'transmit' => $each['network_out'],
                    'receive_des' => $network_receive['value'],
                    'receive_des_unit' => $network_receive['unit']."/s",
                    'transmit_des' => $network_transmit['value'],
                    'transmit_des_unit' => $network_transmit['unit']."/s",
                    
                ),
                'time'=> $each['monitor_time'],
            );
            
            $bpsData[] = array(
                'bpswork' => array(
                    'read' => $each['bps_read'],
                    'write' => $each['bps_write'],
                    'read_des' => $bps_read['value'],
                    'read_des_unit' => $bps_read['unit']."/s",
                    'write_des' => $bps_write['value'],
                    'write_des_unit' => $bps_write['unit']."/s",
                ),
                'time'=> $each['monitor_time'],
            );
            
            $iopsData[] = array(
                'iopswork' => array(
                    'read' => $each['iops_read'],
                    'write' => $each['iops_write'],
                    'read_des' => $each['iops_read'],
                    'read_des_unit' => Xphp::$_lang['WEB_PALTFORM_DC_TIME']."/s",
                    'read_des_unit_en'=>"/s",
                    'write_des' => $each['iops_write'],
                    'write_des_unit' => Xphp::$_lang['WEB_PALTFORM_DC_TIME']."/s",
                    'write_des_unit_en'=>"/s",
                ),
                'time'=> $each['monitor_time'],
            );
            $SystemLoadData[] = array(
                'systemloadwork' => array(
                    'load_1' => $each['system_load_1'],
                    'load_5' => $each['system_load_5'],
                    'load_15' => $each['system_load_15'],
                    
                ),
                'time'=> $each['monitor_time'],
            );
            
        };
        
        $nodeInfo = $this->getNodeMsg($result[0]['node_uuid']);
        $info = array(
            'node_name' => $nodeInfo['node_name'],
            'node_status' => $nodeInfo['status']['flag'] ? 0:1,
            'ip' => $nodeInfo['ip'],
            'cpu_use' => intval($result[0]['cpu_percentage']),
            'memory_use'=> intval($result[0]['ram_percentage']),
            'netData' => $netData,
            'bpsData' => $bpsData,
            'iopsData' => $iopsData,
            'SystemLoadData' => $SystemLoadData,
        );
        
        return $info;
    }
    
    
    
    
    
    /**
     * 得到节点名以及状态
     */
    private function getNodeMsg($node_uuid){
        $sql = "select host_name,node_nickname,ip from bd_node where node_uuid = ?";
        $result = $this->dbSelect($sql,array($node_uuid));
        $nodeHandler = Xphp::instance('NodeHandler');
        $info = array(
            'node_name' => $result[0]['node_nickname'] ? $result[0]['node_nickname'] : $result[0]['host_name'],
            'ip' => $result[0]['ip'],
            'status' => $nodeHandler->getNodeAllStatus($node_uuid),
        );
        return $info;
    }
    
    
    /**
     * 得到所有uuid
     */
    public function getNodeUUid(){
        $sql = "select ip, node_uuid, node_type from bd_node order by node_type asc";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $each){
            $name = "";
            if($each['node_type'] == 1){
                $name = Xphp::$_lang['UI_PALTFORM_MASTER_NODE']."(".$each['ip'].")";
            }else{
                $name = $each['ip'];
            }
            $info[] = array(
                'ip'=>  $each['ip'],
                'name' => $name,
                'node_uuid' => $each['node_uuid'],
            );
        }
        return $info;
    }
    
    public function  getSystemLisenceInfo(){
        $utils = Xphp::instance('Utils');
        $sql = "select extension from bd_license ";
        $data = $this->dbSelect($sql);
        $info['extension'] = json_decode($utils->decrypt($data[0]['extension']), true);
        //授权功能
        in_array('vmprotect', $info['extension']['p'])? $info['vminfo']['showFlag'] = true: $info['vminfo']['showFlag'] =false;
        in_array('fileprotect', $info['extension']['p'])? $info['fileinfo']['showFlag'] = true: $info['fileinfo']['showFlag'] =false;
        in_array('db_protect', $info['extension']['p'])? $info['dbinfo']['showFlag'] = true: $info['dbinfo']['showFlag'] =false;
        in_array('os_protect', $info['extension']['p'])? $info['osinfo']['showFlag'] = true: $info['osinfo']['showFlag'] =false;
        in_array('vol_cdp_protect', $info['extension']['p'])? $info['volinfo']['showFlag'] = true: $info['volinfo']['showFlag'] =false; //实时容灾
        in_array('nas_protect', $info['extension']['p'])? $info['nasinfo']['showFlag'] = true: $info['nasinfo']['showFlag'] =false;
        if($info['extension']['f']['copy']?$info['copyinfo']['showFlag'] = true:$info['copyinfo']['showFlag'] = false);            //副本通过f中的copy进行判断
        if($info['extension']['f']['archive']?$info['archiveinfo']['showFlag'] = true:$info['archiveinfo']['showFlag'] = false);   //归档通过f中的archive进行判断
        in_array('office365_protect',$info['extension']['p'])? $info['office365info']['showFlag'] = true: $info['office365info']['showFlag'] = false; // office365
        in_array('awsprotect',$info['extension']['p']) ? $info['publicCloudinfo']['showFlag'] = true : $info['publicCloudinfo']['showFlag'] = false;  //公有云
        in_array('dbprotect', $info['extension']['p'])? $info['cdpinfo']['showFlag'] = true: $info['cdpinfo']['showFlag'] =false; //实时同步
        in_array('cloud_storage', $info['extension']['p'])? $info['cloudStorageinfo']['showFlag'] = true: $info['cloudStorageinfo']['showFlag'] =false; //云存储
        in_array('remote_system', $info['extension']['p'])? $info['remoteSysteminfo']['showFlag'] = true: $info['remoteSysteminfo']['showFlag'] =false; //异地备份系统
        return json_encode($info);
    }
    
    /**
     * 获取受保护的客户端主机个数
     * @param unknown $moduleType
     * @param unknown $taskType
     * @return number
     */
    public function pGetProtectClient($moduleType, $taskType){
        //受保护主机个数
        $sql = "select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $sqlParams = array(
            Xphp::$_config['FLAG']['UNSET'],
            $moduleType,
            $taskType,
        );
        $data = $this->dbSelect($sql, $sqlParams);

        
        return !empty($data[0]['total'])? intval($data[0]['total']) : 0;
    }

    /**
     * 公共函数
     * 获取保护数据总量
     * @param int $moduleType  模块类型
     */
    private function pGetProtectData($moduleType, $utils, $subModule = null){
        $sql = "select sum(write_size) as write_size from  bd_backup_timepoint where module_type = ? and user_uuid = ? ";
        $sqlParams = array($moduleType,Xphp::$_user['useruuid']);
        if($subModule == Xphp::$_config['VM_SUB_MODULE']['VM']){
            // 虚拟机
            $sql .= " and sub_module_type != ? and task_type = ?";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_config['TASKTYPE']['BACKUP']));
        }else {
            $sql .= " and sub_module_type =? and task_type = ?";
            $sqlParams = array_merge($sqlParams, array($subModule,Xphp::$_config['TASKTYPE']['BACKUP']));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']), $utils);
    }
   	
}	