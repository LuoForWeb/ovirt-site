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
     * @param string $timStrategyBackupType 时间策略备份方式[strategy按策略备份 oncetime一次性备份 manual手动启动]
     */
    protected function pfCreateBackupTaskMessage(
        $task_name,
        $module_type,
        $time_strategy_list,
        $reserver_strategy,
        $transport_strategy,
        $storage_strategy,
        $node_info,
        $timStrategyBackupType = 'strategy'
    ){
        $backupTaskMessage = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'auto_find_sr_flag' => $node_info['auto_find_sr_flag'],
            'node_uuid' => $node_info['node_uuid'],
            'node_pool_uuid' => $node_info['node_pool_uuid'],
            'storage_uuid' => $node_info['storage_uuid'],
            'storage_pool_uuid' => $node_info['storage_pool_uuid'],
            'agent_uuid' => $transport_strategy['agent_uuid'],
            'agent_pool_uuid' => $transport_strategy['agent_pool_uuid'],
            'ignore_resource_limiting_flag' => 1,
            'time_strategy_backup_type' => Xphp::$_config['TIME_STRATEGY_BACKUP_TYPE'][$timStrategyBackupType],
        );
        $time_strategy_list_array = array();
        foreach ($time_strategy_list as $list){
            //组合时间策略
            $time_strategy_list_array[] = $this->pfTimeStrategyMessage(
                $list['strategy_type'], $list['mode'], $list['days'], $list['start_time'], 
                $list['roll_flag'], $list['roll_interval'], $list['roll_end_time'], $list['global_id'],$list['strategy_group_uuid'], $list['full_backup_compensation_flag']);
        }
        $backupTaskMessage['time_strategy_list'] = $time_strategy_list_array;
		
		if(!empty($reserver_strategy)){
			//组合保留策略
			$backupTaskMessage['reserved_strategy'] = $this->pfReservedStrategyMessage(
							$reserver_strategy['strategy_type'], 
							$reserver_strategy['number'], 
							$reserver_strategy['auto_archive_flag'],
							$reserver_strategy['strategy_group_uuid'],
                            $reserver_strategy['strategy_mode'],
							$reserver_strategy['enable_flag']);
		

		}
		$network = !empty($transport_strategy['network_uuid'])?$transport_strategy['network_uuid']: "";
		$network_pool_uuid = !empty($transport_strategy['network_pool_uuid'])?$transport_strategy['network_pool_uuid']: "";
        $agentUuid = $transport_strategy['agent_uuid'] ?: '';
        $agentPoolUuid = $transport_strategy['agent_pool_uuid'] ?: '';
        //组合传输策略
        $backupTaskMessage['transport_strategy'] = $this->pfTransportStrategyMessage(
                                    $transport_strategy['encrypt_flag'],
                                    $transport_strategy['compress_flag'],
                                    $transport_strategy['speed_limit_flag'],
                                    $transport_strategy['max_speed'],
                                    $network,
        							$transport_strategy['strategy_group_uuid'],
                                    $transport_strategy['compress_method'],
                                    $transport_strategy['reconnect_times'],
                                    $transport_strategy['reconnect_interval'],
                                    $transport_strategy['encrypt_method'],
                                    $network_pool_uuid,
                                    $agentUuid,
                                    $agentPoolUuid
                                );
        //组合存储策略
        $backupTaskMessage['storage_strategy'] = $this->pfStorageStrategyMessage($storage_strategy['encrypt_flag'],
                                    $storage_strategy['compress_flag'],
                                    $storage_strategy['deduplication_flag'],
                                    $storage_strategy['blocksize'],
        							$storage_strategy['strategy_group_uuid'],
                                    $storage_strategy['password_auto_flag'],
                                    $storage_strategy['password'],
                                    $storage_strategy['compress_method'],
                                    $storage_strategy['encrypt_method']);
        
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
                $list['roll_flag'], $list['roll_interval'], $list['roll_end_time'], $list['global_id'], $list['strategy_group_uuid'], $list['full_backup_compensation_flag']);
        }
        $msg['time_strategy_list'] = $time_strategy_list_array;
        //组合传输策略
        $network = !empty($transport_strategy['network_uuid'])?$transport_strategy['network_uuid']: "";
        if($module_type==Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            $msg['transport_strategy'] = array(
                'encrypt_flag' => $transport_strategy['encrypt_flag'],
                'compress_flag' => $transport_strategy['compress_flag'],
                'speed_limit_flag' => $transport_strategy['speed_limit_flag'],
                'max_speed' => $transport_strategy['max_speed'],
                'network_uuid' => $network,
                'strategy_group_uuid' => $transport_strategy['strategy_group_uuid'],
                'block_size' => intval($transport_strategy['block_size']),
                'reconnect_times' => $transport_strategy['reconnect_times'],
                'reconnect_interval' => $transport_strategy['reconnect_interval'],
                'compress_method' => $transport_strategy['compress_method'],
                'encrypt_method' => $transport_strategy['encrypt_method']
            );
        }else{
            $msg['transport_strategy'] = $this->pfTransportStrategyMessage(
                $transport_strategy['encrypt_flag'],
                $transport_strategy['compress_flag'],
                $transport_strategy['speed_limit_flag'],
                $transport_strategy['max_speed'],
                $network,
                $transport_strategy['strategy_group_uuid'],
                $transport_strategy['compress_method'],
                $transport_strategy['reconnect_times'],
                $transport_strategy['reconnect_interval'],
                $transport_strategy['encrypt_method']
            );
        }
        
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
                                            $roll_flag, $roll_interval, $roll_end_time, $global_id, $strategy_group_uuid,$full_backup_compensation_flag = 2){
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
        	'strategy_group_uuid' => $strategy_group_uuid,
            'full_backup_compensation_flag' => $full_backup_compensation_flag,
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
    protected function pfReservedStrategyMessage($strategy_type, $number, $auto_archive_flag, $strategy_group_uuid, $strategy_mode, $enable_flag = 1){
        $auto_archive_flag = empty($auto_archive_flag) ? false : $auto_archive_flag;
        $reservedStrategyMessage = array(
            'strategy_type' => $strategy_type,
            'number' => $number,
            'auto_archive_flag' => $auto_archive_flag,
        	'strategy_group_uuid' => $strategy_group_uuid,
        	'strategy_mode' => $strategy_mode,
        	'enable_flag' => $enable_flag
        );
        return $reservedStrategyMessage; 
    }
    
    /**
     * 传输策略消息
     * @param int $encrypt_flag
     * @param int $compress_flag
     * @param int $speed_limit_flag
     * @param int $max_speed
     * @param int $network_uuid
     * @return array $transportStrategyMessage
     */
    protected function pfTransportStrategyMessage(
        $encrypt_flag,
        $compress_flag,
        $speed_limit_flag,
        $max_speed,
        $network_uuid,
        $strategy_group_uuid,
        $compress_method,
        $reconnect_times,
        $reconnect_interval,
        $encrypt_method,
        $networkPoolUuid = '',
        $agentUuid = '',
        $agentPoolUuid = ''
    ){
        $transportStrategyMessage = array(
            'encrypt_flag' => $encrypt_flag,
            'compress_flag' => $compress_flag,
            'speed_limit_flag' => $speed_limit_flag,
            'max_speed' => $max_speed,
            'network_uuid' => $network_uuid,
            'network_pool_uuid' => $networkPoolUuid,
            'agent_uuid' => $agentUuid,
            'agent_pool_uuid' => $agentPoolUuid,
            'strategy_group_uuid' => $strategy_group_uuid,
            'compress_method' => $compress_method,
            'reconnect_times' => $reconnect_times,
            'reconnect_interval' => $reconnect_interval,
            'encrypt_method' => $encrypt_method,
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
    protected function pfStorageStrategyMessage($encrypt_flag, $compress_flag, $deduplication_flag, $blocksize, $strategy_group_uuid, $password_auto_flag, $password, $compress_method, $encrypt_method){
        $storageStrategyMessage = array(
            'encrypt_flag' => $encrypt_flag,
            'compress_flag' => $compress_flag,
            'deduplication_flag' => $deduplication_flag,
            'block_size' => $blocksize,
        	'strategy_group_uuid' => $strategy_group_uuid,
            'password_auto_flag' => $password_auto_flag,
            'password' => $password,
            'compress_method' => $compress_method,
            'encrypt_method' => $encrypt_method,
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
    public function groupBackupNodeInfo($node,$moduleType=""){
        $strArr = array(
            'node_uuid' => $this->getBackupNodeUUID($node,$moduleType),
            'node_pool_uuid' => $node['node_pool_uuid'] ?? '',
            'auto_find_sr_flag' => $node['storagecheck'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'storage_uuid' => $node['storageuuid'],
            'storage_pool_uuid' => $node['storage_pool_uuid'] ?? '',
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
    protected function getBackupNodeUUID($node,$moduleType = ""){
        if(!$node['nodecheck']){
            //自定义节点
            return $node['nodeuuid'];
        }
        if($moduleType ==  Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            //自动选择节点
            $nodeHandler = Xphp::instance('NodeHandler');
            return $nodeHandler->getAutoFindNode();
        }else{
            return '';
        }
    }
    /**
     * 构建网络uuid
     * @param string $nodeUuid    节点uuid
     * @param string $networkUuid 网络uuid
     * @return string
     */
    public function buildNetworkUuid(string $nodeUuid, string $networkUuid): string
    {
        if (!$nodeUuid) {
            return '';
        }
        if (!$networkUuid) {
            return '';
        }
        // 继续获取新加节点的网络
        $sql = "SELECT network_uuid FROM bd_node_network WHERE node_uuid = ? ORDER BY network_order ";
        $networkData = $this->dbSelect($sql, [$nodeUuid]);
        foreach ($networkData as $networkInfo) {
            if ($networkUuid == $networkInfo['network_uuid']) {
                // 如果选择了当前节点网络，直接返回
                return $networkUuid;
            }
        }
        // 返回选择节点默认第一顺序网络
        return $networkData[0]['network_uuid'];
    }
}
?>