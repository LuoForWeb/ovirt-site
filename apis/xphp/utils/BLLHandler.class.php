<?php 
/*******************************************
 ** 业务逻辑层
 *  统一业务处理
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-5-16 10:18:40
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class BLLHandler extends OPHandler{
    
    /**
     * 创建备份任务消息,有点烦,注释都晓不到纳闷写Σ( ° △ °|||)︴,将就看,看不懂就去看函数实现/ 创建(修改)副本任务
     * @param string $task_name             //task name
     * @param int $module_type              //product module type
     * @param array $time_strategy_list     //array[array each_strategy, array each_strategy..多项] 二维数组!!!
     *  @param array each_strategy 
     *      @param   int   'strategy_type'
     *      @param   int   'mode'
     *      @param   string   'days'
     *      @param   string   'start_time'
     *      @param   int   'roll_flag'
     *      @param   int   'roll_interval'
     *      @param   string   'roll_end_time'
     *      @param   int   'global_id'
     * @param array $reserver_strategy      //reserve strategy
     *  @param int strategy_type
     *  @param int number
     *  @param int auto_archive_flag
     * @param array $transport_strategy     //transport strategy
     *  @param int encrypt_flag
     *  @param int compress_flag
     *  @param int speed_limit_flag
     *  @param int max_speed
     * @return array $storage_strategy
     *  @param int deduplication_flag
     *  @param int blocksize
     *  @param int encrypt_flag
     *  @param int compress_flag
     *  
     */
    protected function pfCreateBackupTaskMessage($task_name, $module_type,  
                        $time_strategy_list, $reserver_strategy, $transport_strategy, $storage_strategy, $node_info){
        $backupTaskMessage = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'auto_find_sr_flag' => $node_info['auto_find_sr_flag'],
            'storage_uuid' => $node_info['storage_uuid']
        );
        $time_strategy_list_array = array();
        foreach ($time_strategy_list as $list){
            //组合时间策略
            $time_strategy_list_array[] = $this->pfTimeStrategyMessage(
                $list['strategy_type'], $list['mode'], $list['days'], $list['start_time'], 
                $list['roll_flag'], $list['roll_interval'], $list['roll_end_time'], $list['global_id'],$list['strategy_group_uuid']);
        }
        $backupTaskMessage['time_strategy_list'] = $time_strategy_list_array;
		
		if(!empty($reserver_strategy)){
			//组合保留策略
			$backupTaskMessage['reserved_strategy'] = $this->pfReservedStrategyMessage(
							$reserver_strategy['strategy_type'], 
							$reserver_strategy['number'], 
							$reserver_strategy['auto_archive_flag'],
							$reserver_strategy['strategy_group_uuid']);
		

		}
        //组合传输策略
        $backupTaskMessage['transport_strategy'] = $this->pfTransportStrategyMessage(
                                    $transport_strategy['encrypt_flag'],
                                    $transport_strategy['compress_flag'],
                                    $transport_strategy['speed_limit_flag'],
                                    $transport_strategy['max_speed'],
        							$transport_strategy['strategy_group_uuid']);
        //组合存储策略
        $backupTaskMessage['storage_strategy'] = $this->pfStorageStrategyMessage(
                                    $storage_strategy['encrypt_flag'],
                                    $storage_strategy['compress_flag'],
                                    $storage_strategy['deduplication_flag'],
                                    $storage_strategy['blocksize'],
        							$storage_strategy['strategy_group_uuid'],
                                    $storage_strategy['password_auto_flag'],
                                    $storage_strategy['password']);
        
        return $backupTaskMessage;
    }
    
    /**
     * 启动备份任务消息
     * @param string $task_uuid
     * @param int $backup_mode
     * @return array $msg
     */
    protected function pfStartBackupTaskMessage($task_uuid, $backup_mode){
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
        );
        return $msg;
    }
    
    /**
     * 创建恢复任务消息,有点烦,注释也都晓不到纳闷写Σ( ° △ °|||)︴,将就看,看不懂就去看函数实现
     * @param string $task_name             //task name
     * @param int $module_type              //product module type
     * @param int $recovery_position        //original position， other position
     * @param int $recovery_time_type       //time strategy type 
     * @param array $time_strategy_list     //array[array each_strategy, array each_strategy..多项] 二维数组!!!
     *  @param array each_strategy 
     *      @param   int   'strategy_type'
     *      @param   int   'mode'
     *      @param   string   'days'
     *      @param   string   'start_time'
     *      @param   int   'roll_flag'
     *      @param   int   'roll_interval'
     *      @param   string   'roll_end_time'
     *      @param   int   'global_id'
     * @param array $transport_strategy     //transport strategy
     *  @param int encrypt_flag
     *  @param int compress_flag
     *  @param int speed_limit_flag
     *  @param int max_speed
     * @return array $backupTaskMessage
     */
    protected function pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position, 
                        $recovery_time_type, $time_strategy_list, $transport_strategy){
        $msg = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'recovery_position' => $recovery_position,
            'recovery_time_type' => $recovery_time_type,
        );
        $time_strategy_list_array = array();
        foreach ($time_strategy_list as $list){
            //组合时间策略
            $time_strategy_list_array[] = $this->pfTimeStrategyMessage(
                $list['strategy_type'], $list['mode'], $list['days'], $list['start_time'],
                $list['roll_flag'], $list['roll_interval'], $list['roll_end_time'], $list['global_id'], $list['strategy_group_uuid']);
        }
        $msg['time_strategy_list'] = $time_strategy_list_array;
        //组合传输策略
        $msg['transport_strategy'] = $this->pfTransportStrategyMessage(
            $transport_strategy['encrypt_flag'],
            $transport_strategy['compress_flag'],
            $transport_strategy['speed_limit_flag'],
            $transport_strategy['max_speed'],
            $transport_strategy['strategy_group_uuid']);
        
        return $msg;
    }
    
    /**
     * 启动恢复任务消息
     * @param string $task_uuid
     * @return array 
     */
    protected function pfStartRecoveryTaskMessage($task_uuid){
        return array('task_uuid' => $task_uuid);
    }
    
    /**
     * 停止任务消息
     * @param string $task_uuid
     * @return array
     */
    protected function pfStopTaskMessage($task_uuid){
        return array('task_uuid' => $task_uuid);
    }
    
    /**
     * 删除任务消息
     * @param array $task_uuid_list
     * @return array
     */
    protected function pfDeleteTaskMessage($task_uuid_list){
        return array('task_uuid_list' => $task_uuid_list);
    }
    
    /**
     * 删除备份时间点
     * @param array $timepoint_uuids
     * @return array
     */
    protected function pfDeleteBackupTimepointMessage($timepoint_uuids){
        return array('timepoint_uuids' => $timepoint_uuids);
    }
    
    /**
     * 时间策略消息
     * @param int $strategy_type        //strategy type
     * @param int $mode                 //bakup or recovery mode
     * @param string $days              //the select days map
     * @param string $start_time        //strategy start time
     * @param int $roll_flag            //wheather start roll strategy
     * @param int $roll_interval        //the roll interval time
     * @param string $roll_end_time     //the end time of roll
     * @param int $global_id            //the global strategy id, 0-indicate this is not a global strategy
     * @return array
     */
    protected function pfTimeStrategyMessage($strategy_type, $mode, $days, $start_time, 
                                            $roll_flag, $roll_interval, $roll_end_time, $global_id, $strategy_group_uuid){
        $global_id = empty($global_id) ? 0 : $global_id;
        $utils = Xphp::instance('Utils');
        $timeStrategyMessage = array(
            'strategy_type' => $strategy_type,
            'mode' => $mode,
            'days' => $days,
            'start_time' => $utils->formartTime($start_time),
            'roll_flag' => $roll_flag,
            'roll_interval' => $roll_interval,
            'roll_end_time' => $utils->formartTime($roll_end_time),
            'global_id' => $global_id,
        	'strategy_group_uuid' => $strategy_group_uuid
        );
        return $timeStrategyMessage;
    }
    
    /**
     * 保留策略消息
     * @param int $strategy_type        //days or number
     * @param int $number               //value of days or number
     * @param int $auto_archive_flag    //auto archive flag
     * @return array $reservedStrategyMessage
     */
    protected function pfReservedStrategyMessage($strategy_type, $number, $auto_archive_flag, $strategy_group_uuid){
        $auto_archive_flag = empty($auto_archive_flag) ? false : $auto_archive_flag;
        $reservedStrategyMessage = array(
            'strategy_type' => $strategy_type,
            'number' => $number,
            'auto_archive_flag' => $auto_archive_flag,
        	'strategy_group_uuid' => $strategy_group_uuid
        );
        return $reservedStrategyMessage; 
    }
    
    /**
     * 传输策略消息
     * @param int $encrypt_flag
     * @param int $compress_flag
     * @param int $speed_limit_flag
     * @param int $max_speed
     * @return array $transportStrategyMessage
     */
    protected function pfTransportStrategyMessage($encrypt_flag, $compress_flag, $speed_limit_flag, $max_speed, $strategy_group_uuid){
        $transportStrategyMessage = array(
            'encrypt_flag' => $encrypt_flag,
            'compress_flag' => $compress_flag,
            'speed_limit_flag' => $speed_limit_flag,
            'max_speed' => $max_speed,
        	'strategy_group_uuid' => $strategy_group_uuid
        );
        return $transportStrategyMessage;
    }
    
    /**
     * 存储策略消息
     * @param int $encrypt_flag
     * @param int $compress_flag
     * @param int $deduplication_flag
     * @param int $blocksize
     * @param string $strategy_group_uuid
     * @param int $password_auto_flag
     * @param string $password
     * @return array $storageStrategyMessage
     */
    protected function pfStorageStrategyMessage($encrypt_flag, $compress_flag, $deduplication_flag, $blocksize, $strategy_group_uuid, $password_auto_flag, $password){
        $storageStrategyMessage = array(
            'encrypt_flag' => $encrypt_flag,
            'compress_flag' => $compress_flag,
            'deduplication_flag' => $deduplication_flag,
            'block_size' => $blocksize,
        	'strategy_group_uuid' => $strategy_group_uuid,
            'password_auto_flag' => $password_auto_flag,
            'password' => $password
        );
        return $storageStrategyMessage;
    }
    
    /**
     * 组合备份节点信息
     * @param array $node   备份节点信息
     *  @param bool nodecheck   自动选择节点
     *  @param string nodeuuid  节点UUID
     *  @param bool storagecheck 自动选择存储
     *  @param string storageuuid   存储UUID
     * @return array("node_uuid","auto_find_sr_flag",'storage_uuid')
     */
    public function groupBackupNodeInfo($node){
        $strArr = array(
            'node_uuid' => $this->getBackupNodeUUID($node),
            'auto_find_sr_flag' => $node['storagecheck'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'storage_uuid' => $node['storageuuid'],
        );
        return $strArr;
    }
    
    /**
     * 得到备份的节点信息
     * @param array $node   备份节点信息
     *  @param bool nodecheck   自动选择节点
     *  @param string nodeuuid  节点UUID
     *  @param bool storagecheck 自动选择存储
     *  @param string storageuuid   存储UUID
     * @return string
     */
    protected function getBackupNodeUUID($node){
        if(!$node['nodecheck']){
            //自定义节点
            return $node['nodeuuid'];
        }
        //自动选择节点
        $nodeHandler = Xphp::instance('NodeHandler');
        return $nodeHandler->getAutoFindNode();
    }
}
?>