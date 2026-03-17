<?php
// phpcs:ignoreFile -- 框架类
namespace xphp;

use app\v1\common\logic\Recover;
use app\v1\resources\v0\logic\Node;
use xphp\db\Op;

/*******************************************
 ** 业务逻辑层
 *  统一业务处理
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-5-16 10:18:40
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
class BLLHandler extends Op
{

    /**
     * 创建备份任务消息,有点烦,注释都晓不到纳闷写Σ( ° △ °|||)︴,将就看,看不懂就去看函数实现/ 创建(修改)副本任务
     * @param string $task_name //task name
     * @param int $module_type //product module type
     * @param array $time_strategy_list //array[array each_strategy, array each_strategy..多项] 二维数组!!!
     * @param array each_strategy
     * @param int   'strategy_type'
     * @param int   'mode'
     * @param string   'days'
     * @param string   'start_time'
     * @param int   'roll_flag'
     * @param int   'roll_interval'
     * @param string   'roll_end_time'
     * @param int   'global_id'
     * @param array $reserver_strategy //reserve strategy
     * @param int strategy_type
     * @param int number
     * @param int auto_archive_flag
     * @param array $transport_strategy //transport strategy
     * @param int encrypt_flag
     * @param int compress_flag
     * @param int speed_limit_flag
     * @param int max_speed
     * @return array $storage_strategy
     * @param int deduplication_flag
     * @param int blocksize
     * @param int encrypt_flag
     * @param int compress_flag
     *
     */
    public function pfCreateBackupTaskMessage(string $task_name, int $module_type, array $time_strategy_list, array $reserver_strategy, array $transport_strategy, array $storage_strategy, array $node_info)
    {

        $backupTaskMessage = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'auto_find_sr_flag' => $node_info['auto_find_sr_flag'] ?? xphp_get_config('app')['FLAG']['UNSET'],
            'node_pool_uuid' => $node_info['node_pool_uuid'],
            'node_uuid' => $node_info['node_uuid'],
            'storage_uuid' => $node_info['storage_uuid'],
            'storage_pool_uuid' => $node_info['storage_pool_uuid'],
            'agent_uuid' => $transport_strategy['agent_uuid'],
            'agent_pool_uuid' => $transport_strategy['agent_pool_uuid'],
            'ignore_resource_limiting_flag' => 1,
        );

        $time_strategy_list_array = [];

        foreach ($time_strategy_list as $list) {

            //组合时间策略
            $time_strategy_list_array[] = $this->pfTimeStrategyMessage(
                $list['strategy_type'],
                $list['mode'],
                $list['days'],
                $list['start_time'],
                $list['roll_flag'],
                $list['roll_interval'],
                $list['roll_end_time'],
                $list['global_id'],
                $list['strategy_group_uuid'],
                $list['full_backup_compensation_flag']
            );
        }
        $backupTaskMessage['time_strategy_list'] = $time_strategy_list_array;

        if (!empty($reserver_strategy)) {

            //组合保留策略
            $backupTaskMessage['reserved_strategy'] = $this->pfReservedStrategyMessage(
                $reserver_strategy['strategy_type'],
                $reserver_strategy['number'],
                $reserver_strategy['auto_archive_flag'],
                $reserver_strategy['strategy_group_uuid'],
                $reserver_strategy['strategy_mode']);

        }
        $network = !empty($transport_strategy['network_uuid']) ? $transport_strategy['network_uuid'] : "";
        $networkPoolUuid = !empty($transport_strategy['network_pool_uuid']) ? $transport_strategy['network_pool_uuid'] : "";
        $agentUuid = $transport_strategy['agent_uuid'] ?: '';
        $agentPoolUuid = $transport_strategy['agent_pool_uuid'] ?: '';

        //组合传输策略
        $backupTaskMessage['transport_strategy'] = $this->pfTransportStrategyMessage(
            $transport_strategy['encrypt_flag'],
            $transport_strategy['encrypt_method'],
            $transport_strategy['compress_flag'],
            $transport_strategy['speed_limit_flag'],
            $transport_strategy['max_speed'],
            $network,
            $transport_strategy['strategy_group_uuid'],
            $transport_strategy['compress_method'],
            $transport_strategy['reconnect_times'],
            $transport_strategy['reconnect_interval'],
            $networkPoolUuid,
            $agentUuid,
            $agentPoolUuid
        );

        //组合存储策略
        $backupTaskMessage['storage_strategy'] = $this->pfStorageStrategyMessage(
            $storage_strategy['encrypt_flag'],
            $storage_strategy['compress_flag'],
            $storage_strategy['deduplication_flag'],
            $storage_strategy['blocksize'],
            $storage_strategy['strategy_group_uuid'],
            $storage_strategy['password_auto_flag'],
            $storage_strategy['password'],
            $storage_strategy['compress_method'],
            $storage_strategy['encrypt_method']
        );

        return $backupTaskMessage;
    }

    /**
     * 启动备份任务消息
     * @param string $task_uuid
     * @param int $backup_mode
     * @return array $msg
     */
    public function pfStartBackupTaskMessage(string $task_uuid, int $backup_mode)
    {
        return array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
        );
    }

    /**
     * 创建恢复任务消息,有点烦,注释也都晓不到纳闷写Σ( ° △ °|||)︴,将就看,看不懂就去看函数实现
     * @param string $task_name //task name
     * @param int $module_type //product module type
     * @param int $recovery_position //original position， other position
     * @param int $recovery_time_type //time strategy type
     * @param array $time_strategy_list //array[array each_strategy, array each_strategy..多项] 二维数组!!!
     * @param array each_strategy
     * @param int   'strategy_type'
     * @param int   'mode'
     * @param string   'days'
     * @param string   'start_time'
     * @param int   'roll_flag'
     * @param int   'roll_interval'
     * @param string   'roll_end_time'
     * @param int   'global_id'
     * @param array $transport_strategy //transport strategy
     * @param int encrypt_flag
     * @param int compress_flag
     * @param int speed_limit_flag
     * @param int max_speed
     * @return array $backupTaskMessage
     */
    public function pfCreateRecoveryTaskMessage(string $task_name, int $module_type, int $recovery_position, int $recovery_time_type, array $time_strategy_list, array $transport_strategy)
    {

        $RecoverHandler = new Recover;
        return $RecoverHandler->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position, $recovery_time_type, $time_strategy_list, $transport_strategy);
    }

    /**
     * 启动恢复任务消息
     * @param string $task_uuid
     * @return array
     */
    public function pfStartRecoveryTaskMessage(string $task_uuid)
    {
        return array('task_uuid' => $task_uuid);
    }

    /**
     * 停止任务消息
     * @param string $task_uuid
     * @return array
     */
    public function pfStopTaskMessage(string $task_uuid)
    {
        return array('task_uuid' => $task_uuid);
    }

    /**
     * 删除任务消息
     * @param array $task_uuid_list
     * @return array
     */
    public function pfDeleteTaskMessage(array $task_uuid_list)
    {
        return array('task_uuid_list' => $task_uuid_list);
    }

    /**
     * 删除备份时间点
     * @param array $timepoint_uuids
     * @return array
     */
    public function pfDeleteBackupTimepointMessage(array $timepoint_uuids)
    {
        return array('timepoint_uuids' => $timepoint_uuids);
    }

    /**
     * 时间策略消息
     * @param int $strategy_type //strategy type
     * @param int $mode //bakup or recovery mode
     * @param string $days //the select days map
     * @param string $start_time //strategy start time
     * @param int $roll_flag //wheather start roll strategy
     * @param int $roll_interval //the roll interval time
     * @param string $roll_end_time //the end time of roll
     * @param int $global_id //the global strategy id, 0-indicate this is not a global strategy
     * @return array
     */

    public function pfTimeStrategyMessage(int $strategy_type, int $mode, string $days, string $start_time, int $roll_flag, int $roll_interval, string $roll_end_time, $global_id, string $strategy_group_uuid, $full_backup_compensation_flag = 2)
    {
        $global_id = empty($global_id) ? 0 : $global_id;

        return array(
            'strategy_type' => $strategy_type,
            'mode' => $mode,
            'days' => $days,
            'start_time' => v1_formart_time($start_time),
            'roll_flag' => $roll_flag,
            'roll_interval' => $roll_interval,
            'roll_end_time' => v1_formart_time($roll_end_time),
            'global_id' => $global_id,
            'strategy_group_uuid' => $strategy_group_uuid,
            'full_backup_compensation_flag'=> $full_backup_compensation_flag
        );

    }

    /**
     * 保留策略消息
     * @param int $strategy_type //days or number
     * @param int $number //value of days or number
     * @param int $auto_archive_flag //auto archive flag
     * @return array $reservedStrategyMessage
     */
    protected function pfReservedStrategyMessage(int $strategy_type, int $number, int $auto_archive_flag, $strategy_group_uuid, int $strategy_mode)
    {
        $auto_archive_flag = empty($auto_archive_flag) ? false : $auto_archive_flag;
        return array(
            'strategy_type' => $strategy_type,
            'number' => $number,
            'auto_archive_flag' => $auto_archive_flag,
            'strategy_group_uuid' => $strategy_group_uuid,
        	'strategy_mode' => $strategy_mode
        );

    }

    /**
     * 传输策略消息
     * @param int $encrypt_flag
     * @param int $compress_flag
     * @param int $speed_limit_flag
     * @param int $max_speed
     * @param int $network_uuid
     * @param int $compress_method
     * @return array $transportStrategyMessage
     */
    public function pfTransportStrategyMessage(
        int $encrypt_flag,
        int $encrypt_method,
        int $compress_flag,
        int $speed_limit_flag,
        int $max_speed,
        string $network_uuid,
        $strategy_group_uuid = '',
        int $compress_method = 0,
        int $reconnect_times = 0,
        int $reconnect_interval = 0,
        string $networkPoolUuid = '',
        string $agentUuid = '',
        string $agentPoolUuid = ''
    ): array {
        return array(
            'encrypt_flag' => $encrypt_flag,
            'encrypt_method' => $encrypt_method,
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
        );

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
     * @param int $compress_method
     * @return array $storageStrategyMessage
     */
    protected function pfStorageStrategyMessage(int $encrypt_flag, int $compress_flag, int $deduplication_flag, int $blocksize, $strategy_group_uuid, int $password_auto_flag, string $password, int $compress_method, int $encrypt_method)
    {
        return array(
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

    }

    /**
     * 组合备份节点信息
     * @param array $node 备份节点信息
     * @param bool nodecheck   自动选择节点
     * @param string nodeuuid  节点UUID
     * @param bool storagecheck 自动选择存储
     * @param string storageuuid   存储UUID
     * @return array("node_uuid","auto_find_sr_flag",'storage_uuid')
     */
    public function groupBackupNodeInfo(array $node)
    {
        return array(
            'node_uuid' => $this->getBackupNodeUUID($node),
            'node_pool_uuid' => $node['node_pool_uuid'] ?? '',
            'auto_find_sr_flag' => $node['storagecheck'] ? xphp_get_config('app')['FLAG']['SET'] : xphp_get_config('app')['FLAG']['UNSET'],
            'storage_uuid' => $node['storageuuid'],
            'storage_pool_uuid' => $node['storage_pool_uuid'] ?? '',
        );

    }

    /**
     * 得到备份的节点信息
     * @param array $node 备份节点信息
     * @param bool nodecheck   自动选择节点
     * @param string nodeuuid  节点UUID
     * @param bool storagecheck 自动选择存储
     * @param string storageuuid   存储UUID
     * @return string
     */
    protected function getBackupNodeUUID(array $node)
    {
        if (!$node['nodecheck']) {
            //自定义节点
            return $node['nodeuuid'];
        }
        //自动选择节点
        $nodeHandler = new Node();
        return $nodeHandler->getAutoFindNode();
    }


}
