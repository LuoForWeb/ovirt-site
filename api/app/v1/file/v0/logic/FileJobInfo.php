<?php

namespace app\v1\file\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\tape\v0\logic\TapeInfo;
use app\v1\common\logic\Backup;

/**
 * note          文件 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileJobInfo extends Base
{

    /**
     * 任务详情: 得到文件模块任务基本信息
     * @param array $params 参数
     * @return array
     */
    public function getFsBasicInfo(array $params)
    {
        $taskUUID = $params['job_uuid'];
        $this->paramsCheck($taskUUID);
        $info = (new \app\v1\job\v0\logic\JobInfo())->getJobInfo($taskUUID);
        $sql = "select distinct bt.task_uuid, bt.task_name, bt.module_type,bt.sub_module_type, bt.task_type, bt.task_status, bt.thread_num,
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
                	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time, 
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time,
                        ft.snap_shot_flag, ft.detail, ft.file_archive_flag,ft.skip_file_alarm_flag,ft.skip_file_alarm_min_num,ft.skip_file_alarm_min_ratio, 
                        ft.permission_operate_flag, ft.same_file_strategy, ft.link_file_pass_flag, ft.dir_tree_recovery_flag, fpl.new_root_path,bt.task_orchestration_plan_flag
                from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs ,fs_task ft, fs_path_list fpl   
                where bt.user_uuid = bu.user_uuid and 
                      bt.task_uuid = bri.task_uuid and 
                      bt.task_uuid = fpl.task_uuid and
                      bt.strategy_id = bs.strategy_id and 
                      bt.task_uuid = ft.task_uuid and
                	  bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        $tapeHandler = new TapeInfo();
        $Job = new JobInfo();
        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            $detail = json_decode($d['detail'], true);
            $basicInfo = array(
                'job_name' => $info['job_name'],
                'job_type' => $info['job_type'],
                'submodle_type' => $d['sub_module_type'],
                'job_status'    => $info['job_status'],
                'total_size' => $info['total_size'],
                'complate_size' => $info['complate_size'],
                'create_time' => $info['create_time'],
                'start_time' => $info['start_time'],
                'interval_time' => $info['interval_time'],
                'speed' => $info['speed'],
                'thread_num' => $info['thread_num'],
                'timestamp' => $info['timestamp'],
                'progress' => $info['progress'],
                'next_time' => $info['next_time'],
                'storage_info'  => $info['storage_info'], // 存储信息
                'speed_limit'  => $info['speed_limit'], // 限速策略
                'time_strategy'  => $info['time_strategy'], // 时间策略
                'time_strategy_backup_type' => Backup::instance()->getTimeStrategyBackupType($d['strategy_id']),
                'reserve_strategy'  => $info['reserve_strategy'], // 保留策略
                'transport_strategy'  => $info['transport_strategy'], // 传输策略
                'module_type' => $info['module_type'],
                'module_type_des' => $info['module_type_des'],
                'job_type_des' => $info['job_type_des'],
                'flag' => true,
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $Job->getSpeedlimitDes($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'snapShotFlag' => $d['snap_shot_flag'],
                'wildcardMode' => $this-> getWildCardInfo($d['task_uuid']),
                'networkFlag' => $this->getNodeNetworkFlag($taskUUID),//检查显示传输网络标志
                'scan_thread_num' => intval($detail['scan_thread_num']),
                'scan_file_num' => intval($detail['scan_file_num']),
                'archiveflag' => $d['file_archive_flag'],
                'skip_file_alarm_flag' => $d['skip_file_alarm_flag'] == 1 ? true : false,
                'skip_file_alarm_min_num' => $d['skip_file_alarm_min_num'],
                'skip_file_alarm_min_ratio' => $d['skip_file_alarm_min_ratio'] . '%',
                'permission_operate_flag' => $d['permission_operate_flag'] == 1 ? true : false,
                'same_file_strategy' => $d['same_file_strategy'],
                'link_file_pass_flag' => $d['link_file_pass_flag'] == 1 ? true : false,
                'dir_tree_recovery_flag' => $d['dir_tree_recovery_flag'] == 1 ? true : false,
                'new_root_path' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH')  : $d['new_root_path'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $this->getNasResorceInfo($taskUUID)['source_agent_type'],//agent_type
                'src_sub_module_type' => $this->getNasResorceInfo($taskUUID)['src_type'],//sub_module_type
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $tapeHandler->getTapeGroupStrategy(array("group_uuid" => $d['storage_uuid'])) ?? '',
                'storage_type' => $this->getStorageTypeByTaskId($taskUUID, $d['task_type']),
            );
        }
        return $basicInfo;
    }

    /**
     * 任务详情-根据任务uuid获取存储类型--只用于恢复任务
     * @param string $taskUUID 任务uuid
     * @param string $taskType 任务类型
     * @return string 存储类型
     */
    private function getStorageTypeByTaskId($taskUUID,$taskType){
        $storageType = '';
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP']) {
            return $storageType;
        }
        $sql = "select bsr.storage_type from bd_storage_resource bsr, bd_backup_timepoint bbt, fs_path_list fpl where 
        fpl.task_uuid = ? and fpl.recovery_timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }

    /**
     * 任务详情-概览: 文件是否配置通配符
     * @param unknown $params
     */
    private function getWildCardInfo($task_uuid) {
        $sql = "select detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $count = 0;
        foreach($data as $d) {
            $count += intval(json_decode($d['detail'],true)["wildcard_mode"]);
        }
        if( $count > 0) {//大于0则配置了通配符
            return true;
        }
        return false;
    }

    /**
     * 获取nas源设备的信息
     * @param unknown $params
     */
    private function getNasResorceInfo($taskuuid) {
        //先找到恢复时间点
        $sql = " select fbt.detail,fbt.source_agent_type, bbt.sub_module_type from fs_path_list fpl,fs_backup_timepoint fbt,bd_backup_timepoint bbt where
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and fpl.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
        $detail = json_decode($data[0]['detail'], true);
        $sql = " select ip, share_path,nas_nickname from nas_storage_resource where nas_uuid = ?";
    	$resdata = $this->dbSelect($sql, array($detail["nas_uuid"]));
        if ($resdata[0]['nas_nickname'] != $resdata[0]['ip']) {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['nas_nickname'] . ")";
        } else {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['share_path'] . ")";
        }
        $info = array(
            "nas_name" => $nas_name,
            "source_agent_type" => $data[0]['source_agent_type'],
            "src_type" => $data[0]['sub_module_type'],
        );
        return $info;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getNodeNetworkFlag($taskuuid){
        $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d){
            if(intval($d['net_model']) == 2 && !empty($d['network_uuid'])){
                $flag = true;
            }
        }
        return $flag;
    }
}
