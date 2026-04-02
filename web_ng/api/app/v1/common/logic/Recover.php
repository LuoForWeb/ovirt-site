<?php

namespace app\v1\common\logic;

/**
 * note          恢复任务的一些公共方法
 * @author       wanggongxi@vinchin.com
 * @date         2023/6/21 11:44
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Recover extends Base
{
    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param string $agentuuid 客户端uuid
     * @param string $fbtname   时间点中的主机名
     * @param string $ip        agent_ip
     * @return string name(ip)
     */
    protected function getAgentNameStr(string $agentuuid, string $fbtname, string $ip)
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agentuuid));
        if (!empty($result)) {
            if ($result[0]['agent_name'] == $ip) {
                return $result[0]['hostname'] . '(' . $ip . ')';
            } else {
                return $result[0]['agent_name'] . '(' . $ip . ')';
            }
        }
        return $fbtname . '(' . $ip . ')';
    }

    /**
     * 组合恢复时间策略
     * @param array $params 参数
     * @return array
     */
    public function groupRecoverTimeList(array $params): array
    {
        $timeList = [];
        $recoverType = xphp_get_config('task', 'RECOVERY_TIME_TYPE');
        if ($recoverType['IMMEDIATELY'] == intval($params['type'])) {
            //立即恢复
            return $timeList;
        } elseif ($recoverType['STRATEGY'] == intval($params['type'])) {
            //按时间策略恢复
            $timeList[] = (new Backup())->groupEachTimestrategy(
                xphp_get_config('task', 'BACKUP_MODE')['FULL'],
                $params['strategy']
            );
            return $timeList;
        } else if ($recoverType['ONCETIME'] == intval($params['type'])) {//一次性恢复
             //获取一次性恢复的时间
             $taskCreateTime = strtotime($params['strategy']['start_time']);
             //获取系统时间
             $systemTime = strtotime(date('Y-m-d H:i:s'));
             if ($systemTime >= $taskCreateTime) {
                 exit($this->muOpResult(
                     false,
                     ('UI_RECOVERY_TYPE_TIMING_TIME'),
                     xphp_get_lang('WEB_RECOVERY_TIME_TIPS'),
                     'warning'
                 )
                 );
             }
            $timeList[] = (new Backup())->groupEachTimestrategy(
                xphp_get_config('task', 'BACKUP_MODE')['FULL'],
                $params['strategy']
            );
            return $timeList;
        }
    }



    /**
     * 获取代理主机的配置网络信息
     */
    public function getnetworkByAgent(array $params)
    {
        $resultInfo = array();
        //得到主机uuid
        $agent_uuid = $params['agent_uuid'];
        $sql = "select agent_uuid, agent_name, hostname,os_type, agent_type, ip, detail  from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agent_uuid));
        if (empty($result)) {
            return $resultInfo;
        }
        $resultInfo = array(
            'agent_uuid' => $result[0]['agent_uuid'],
            'agent_name' => $result[0]['agent_name'],
            'hostname' => $result[0]['hostname'],
            'os_type' => $result[0]['os_type'],
            'agent_type' => $result[0]['agent_type'],
            'ip' => $result[0]['ip'],
            'host_name_limit' => xphp_get_config('machine_os_config', 'HostNameCheck')[$result[0]['os_type']],
        );
        $nic_list = json_decode($result[0]['detail'], true);
        $nic_list = $nic_list['nic_list'];
        $resultInfo['nic_list'] = $nic_list;
        return $resultInfo;
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

        $msg = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'recovery_position' => $recovery_position,
            'recovery_time_type' => $recovery_time_type,
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
                $list['end_time'],
                $list['global_id'],
                $list['strategy_group_uuid']
            );
        }
        $msg['time_strategy_list'] = $time_strategy_list_array;

        //组合传输策略
        $network = $transport_strategy['network_uuid'] ?? '';

        if ($module_type == xphp_get_config('module')['MODULE_TYPE']['VOL_CDP']) {
            $msg['transport_strategy'] = array(
                'encrypt_flag' => $transport_strategy['encrypt_flag'],
                'compress_flag' => $transport_strategy['compress_flag'],
                'speed_limit_flag' => $transport_strategy['speed_limit_flag'],
                'max_speed' => $transport_strategy['max_speed'],
                'network_uuid' => $network,
                'network_pool_uuid' => '',
                'strategy_group_uuid' => $transport_strategy['strategy_group_uuid'],
                'block_size' => intval($transport_strategy['block_size']),
                'compress_method' => $transport_strategy['compress_method'],
            );

        } else {

            $msg['transport_strategy'] = $this->pfTransportStrategyMessage(
                $transport_strategy['encrypt_flag'],
                $transport_strategy['encrypt_method'],
                $transport_strategy['compress_flag'],
                $transport_strategy['speed_limit_flag'],
                $transport_strategy['max_speed'],
                $network,
                $transport_strategy['strategy_group_uuid'],
                $transport_strategy['compress_method'],
                $transport_strategy['reconnect_times'],
                $transport_strategy['reconnect_interval']
            );
        }

        return $msg;
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

     public function pfTimeStrategyMessage(int $strategy_type, int $mode, string $days, string $start_time, int $roll_flag, int $roll_interval, string $roll_end_time, int $global_id, string $strategy_group_uuid)
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
             'strategy_group_uuid' => $strategy_group_uuid
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
        string $networkPoolUuid = ''
    ): array {
        return array(
            'encrypt_flag' => $encrypt_flag,
            'encrypt_method' => $encrypt_method,
            'compress_flag' => $compress_flag,
            'speed_limit_flag' => $speed_limit_flag,
            'max_speed' => $max_speed,
            'network_uuid' => $network_uuid,
            'network_pool_uuid' => $networkPoolUuid,
            'strategy_group_uuid' => $strategy_group_uuid,
            'compress_method' => $compress_method,
            'reconnect_times' => $reconnect_times,
            'reconnect_interval' => $reconnect_interval,
        );

    }

    /**
     * 组合安全策略
     * @param mixed $safeConfigStrategy 安全策略
     * @return mixed
     */
    public function groupSafeConfigStrategy($safeConfigStrategy)
    {
        $safeConfigStrategy['worm_flag'] =  v1_parse_bool_to_flag($safeConfigStrategy['worm_flag']);
        $safeConfigStrategy['virus_scan_flag'] =  v1_parse_bool_to_flag($safeConfigStrategy['virus_scan_flag']);
        $safeConfigStrategy['integrity_check_flag'] =  v1_parse_bool_to_flag($safeConfigStrategy['integrity_check_flag']);
        return $safeConfigStrategy;
    }


}
