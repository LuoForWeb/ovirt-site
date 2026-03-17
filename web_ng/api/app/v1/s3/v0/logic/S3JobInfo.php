<?php

namespace app\v1\s3\v0\logic;

use app\v1\common\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\user\v0\logic\User;

/**
 * note          对象存储保护 - 任务详情logic
 * @auther       chengjiafu@vinchin.com
 * @date         2023/11/27 15:56
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class S3JobInfo extends JobInfo {
    /**
     * 任务详情: 得到Exchange模块任务基本信息
     * @param unknown $params 参数
     * @return array 任务基本信息
     */
    public function getBasicInfo($params = [])
    {
        $taskUUID = $params['jobs_uuid'];
        $info = (new \app\v1\job\v0\logic\JobInfo())->getJobInfo($taskUUID);
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num, bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment, bt.node_uuid, bt.storage_uuid, bt.task_orchestration_plan_flag,
        bu.user_name, unix_timestamp(bs.next_start_time) next_start_time, bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, mt.detail as mtdetail,mt.m365_retry_info, mo.agent_uuid_list  
        from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs, m365_task mt, m365_organization mo   
        where bt.user_uuid = bu.user_uuid and bt.task_uuid = bri.task_uuid and bt.strategy_id = bs.strategy_id and bt.task_uuid = mt.task_uuid and bt.task_uuid = ? and mt.organization_uuid = mo.organization_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        if (!$data) {
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d) {
            $m365RetryInfo = json_decode($d['m365_retry_info'], true);
            $mtdetail = json_decode($d['mtdetail'], true);
            $basicInfo = array(
                'job_name' => $info['job_name'],
                'job_type' => $info['job_type'],
                'submodle_type' => $info['submodle_type'],
                'job_status'    => $info['job_status'],
                'total_size' => $info['total_size'],
                'complate_size' => $info['complate_size'],
                'create_time' => $info['create_time'],
                'start_time' => $info['start_time'],
                'interval_time' => $info['interval_time'],
                'job_stage' => $info['job_stage'],
                'speed' => $info['speed'],
                'speed_time' => $info['speed_time'],
                'thread_num' => $info['thread_num'],
                'timestamp' => $info['timestamp'],
                'progress' => $info['progress'],
                'next_time' => $info['next_time'],
                'storage_info'  => $info['storage_info'], // 存储信息
                'speed_limit'  => $info['speed_limit'], // 限速策略
                'time_strategy'  => $info['time_strategy'], // 时间策略
                'reserve_strategy'  => $info['reserve_strategy'], // 保留策略
                'transport_strategy'  => $info['transport_strategy'], // 传输策略
                'module_type' => $info['module_type'],
                'module_type_des' => $info['module_type_des'],
                'job_type_des' => $info['job_type_des'],
                'retry_flag' => $m365RetryInfo['retry_flag'],
                'retry_count' => $m365RetryInfo['retry_count'],
                'retry_delay_time' => $m365RetryInfo['retry_delay_time'],
                'flag' => true,
                'network_flag' => $this->getNodeNetworkFlag($taskUUID, json_decode($d['agent_uuid_list'], true)),//检查显示传输网络标志
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
            );
        }
        return $basicInfo;
    }


}