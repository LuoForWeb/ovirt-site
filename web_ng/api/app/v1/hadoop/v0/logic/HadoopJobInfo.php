<?php

namespace app\v1\hadoop\v0\logic;

use app\v1\common\logic\JobInfo;
use app\v1\user\v0\logic\User;
use app\v1\resources\v0\logic\Storage;
use app\v1\resources\v0\logic\Node;
use app\v1\common\logic\Data;
use app\v1\common\logic\Backup;
use app\v1\opcode\PfOpcode;
use app\v1\hadoop\v0\logic\HadoopBackUp;
use app\v1\tape\v0\logic\TapeInfo;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
/**
 * note          hadoop 之任务信息 logic
 * @author       lilingyu@vinchin.com
 * @date         2023/11/29 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopJobInfo extends JobInfo
{

    /**
     * 任务详情: 得到Hadoop模块任务基本信息
     * @param unknown $params 参数
     * @return array 任务基本信息
     */
    public function getBasicInfo($params = [])
    {
        $taskUUID = $params['jobs_uuid'];
        $sql = "select distinct bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num, bt.task_orchestration_plan_flag, bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
                    bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,bt.ignore_resource_limiting_flag,bt.current_stage,bt.stage_percent, bt.user_uuid,
                    bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time, 
                    bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time,
                    ft.snap_shot_flag, ft.detail, ft.file_archive_flag,ft.skip_file_alarm_flag,ft.skip_file_alarm_min_num,ft.skip_file_alarm_min_ratio, 
                    ft.permission_operate_flag, ft.same_file_strategy, ft.link_file_pass_flag, ft.dir_tree_recovery_flag, ft.proxy_uuid, fpl.new_root_path,
                    btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy, btsc.backup_integrity_check_inc_error_policy, btsc.recovery_integrity_check_error_policy, bs.time_strategy_backup_type  
            from bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs ,fs_task ft, fs_path_list fpl, bd_task_safe_config btsc
            where bt.user_uuid = bu.user_uuid and 
                bt.task_uuid = bri.task_uuid and 
                bt.task_uuid = fpl.task_uuid and
                bt.strategy_id = bs.strategy_id and 
                bt.task_uuid = ft.task_uuid and
                bt.task_uuid = btsc.task_uuid and
                bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        if (!$data) {
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        $tapeHandler = new TapeInfo();
        foreach ($data as $d){
            $worm_storage_flag = false;
            //如果是备份需要获取worm_storage_flag
            if($d['task_type'] ==  1){
                $wormsql = "select bsr.worm_flag as worm_storage_flag from bd_task bt , bd_storage_resource bsr where bt.storage_uuid = bsr.storage_uuid and  bt.task_uuid = ? ";
                $wormdata = $this->dbSelect($wormsql, array($taskUUID));
                $worm_storage_flag = v1_parse_flag_to_bool($wormdata[0]['worm_storage_flag']);
            }
            //这里判断是备份还是恢复任务 如果是恢复任务获取source_type
            $resourcetype = '';
            if($d['task_type'] ==  2){
                $resourcesql = "select bbt.sub_module_type as source_submodule_type from fs_path_list fpl 
                left join bd_backup_timepoint bbt ON fpl.recovery_timepoint_uuid = bbt.timepoint_uuid where fpl.task_uuid = ?";
                $resourceparams = array($d['task_uuid']);
                $resourceinfo = $this->dbSelect($resourcesql,$resourceparams);
                $resourcetype = $resourceinfo[0]['source_submodule_type'];
            }
            $detail = json_decode($d['detail'], true);
            $applianceAgencyFlag = $d['proxy_uuid'] ? true : false;
            $applianceAgency = $applianceAgencyFlag ? $this->getApplianceAgency($d['proxy_uuid']) : '';
            $agentPoolInfo = $this->getJobAgentPoolInfo($d['task_uuid']);
            $reserveParams = array(
                "group_uuid" => $d['storage_uuid']
            );
            //获取任务阶段
            $stageArr = xphp_get_config('task', 'COMMON_STAGE');
            $currentstage = !empty($stageArr[$d['current_stage']]) ?
                xphp_get_lang($stageArr[$d['current_stage']]) : xphp_get_config('app', 'NULLSPACE');
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' =>xphp_get_lang($ptDes['MODULE_TYPE_DES'][$d['module_type']]),
                'taskType' => $this->getBasicInfoTaskTypeDes(xphp_get_lang($ptDes['TASKTYPEDES'][$d['task_type']]), 
                                $d['module_type'], $d['task_uuid']),
                'user' => $d['user_name'],
                'status' => xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                'statusValue' => intval($d['task_status']),
                //只有运行状态才显示数值
                'totalSize' => $d['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ? v1_calsize($d['total_object_size'], true) : xphp_get_config('app', 'NULLSPACE'),
                'currentSize' =>$d['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ? v1_calsize($d['total_object_completed_size'], true) : xphp_get_config('app', 'NULLSPACE'),
                'speed' => $return['task_status'] != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ?
                    xphp_get_config('app', 'NULLSPACE') :
                    v1_calspeed($return['speed']),
                'progress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                'totalprogress' => $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($d['start_time'], $d['task_status']),
                'intervalTime' => $this->getTimeInterval($d['start_time'], $d['task_status']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'nextTime' => $this->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => $this->getJobTimeStrategy($d['strategy_id']),
                'time_strategy_backup_type' => $d['time_strategy_backup_type'],
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transportStrategy' => $this->getFsTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid'], $taskUUID),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'fulldiskRestore' =>  $this->getRecoveryFullDisk($taskUUID),
                'nosnapshot' =>  $this->getXenSnapshotFlag($d['task_uuid']),
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
                'path' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'appliance_agency_flag' => $applianceAgencyFlag,
                'appliance_agency' => $applianceAgency,
                'agent_pool_info' => $agentPoolInfo,
                'source_submodule_type' => $resourcetype,
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $tapeHandler -> getTapeGroupStrategy($reserveParams) ?? '',
                'storageType' =>  $d['task_type'] == 2 ? $this->getStorageType($taskUUID) : '', // 恢复任务获取存储类型
                'retry_strategy' => (new ExchangeJobInfo())->getRetryStrategy($taskUUID),
                //新增安全策略
                'safeStrategy' => array(
                    //获取worm开关
                    'worm_flag' => v1_parse_flag_to_bool($data[0]['worm_flag']),
                    //获取worm保护期限
                    'worm_protection_time' => intval($data[0]['worm_protection_time']),
                    // //获取病毒是否开关
                    // 'virus_scan_flag' => v1_parse_flag_to_bool($data[0]['virus_scan_flag']),
                    // //获取病毒检测配置
                    // 'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true),  
                    //获取完整性效验开关
                    'integrity_check_flag' => v1_parse_flag_to_bool($data[0]['integrity_check_flag']),
                    //获取完整性校验数据
                    'integrity_check_config' => array(
                        //获取效验周期
                        'check_strategy' => intval($data[0]['integrity_check_strategy']),
                        //获取完全备份点异常
                        'full_error_policy' => intval($data[0]['backup_integrity_check_full_error_policy']),
                        //获取其他备份点异常
                        'inc_error_policy' => intval($data[0]['backup_integrity_check_inc_error_policy']),
                        //获取恢复完整性校验
                        'recovery_error_policy' => intval($data[0]['recovery_integrity_check_error_policy']),

                    ),
                     //存储是否开启了worm
                     'worm_storage_flag' => $worm_storage_flag,
                ),
                "ignore_resource_limiting_flag" => v1_parse_flag_to_bool($d['ignore_resource_limiting_flag']),
                'current_stage' => $d['current_stage'], // 任务阶段
                'stage_percent' => $d['stage_percent'], // 任务百分比
                'current_stage_value' => $currentstage, // 任务阶段+任务百分比
                 'task_user_uuid' => $d['user_uuid'],
            );
        }
        return $basicInfo;
        
    }


    /**
     * 得到任务监控界面的任务类型 ,主要是获取虚拟机的子模块
     * @param string $taskTypeDes
     * @param int    $moduleType
     * @param string $taskuuid
     */
    protected function getBasicInfoTaskTypeDes($taskTypeDes, $moduleType, $taskuuid)
    {
        if ($moduleType == xphp_get_config('module')['MODULE_TYPE']['VM']) {
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $hypervisor = intval($data[0]['hypervisor_type']);
            $taskTypeDes .= '[' . xphp_get_config('vm')['VMHYPERVISORDES'][$hypervisor] . ']';
        }
        return $taskTypeDes;
    }

     /**
     * 根据任务运行情况估算完成时间
     * @param int $totalSize   总大小
     * @param int $currentSize 当前大小
     * @param int $taskStatus  任务状态
     * @param int $speed       速度
     * @return 时间
     */
    public function getCalEndTime($totalSize, $currentSize, $taskStatus, $speed)
    {
        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //任务没有运行
            return xphp_get_config('app', 'TIMESPACE');
        }
        if (0 == intval($speed)) {
            return xphp_get_config('app', 'TIMESPACE');
        }
        $needTime = ($totalSize - $currentSize) / $speed ;
        return date('Y-m-d H:i:s', time() + intval($needTime));
    }

     /**
     * 得到文件任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getFsTransportStrategy($taskuuid, $strategyid){
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method from bd_transport_strategy bts left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v1_parse_flag_to_bool($data[0]['encrypt_flag']);
        $info['encrypt_method'] = $data[0]['transport_method'];
        $info['compress'] = v1_parse_flag_to_bool($data[0]['compress_flag']);

        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        
        return $info;
    }
    /**
    * 获取任务对应的虚拟化类型
    * @param string $taskuuid
    */
    private function getHypervisor($taskuuid){
        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return intval($data[0]['hypervisor_type']);
    }

    /**
     * 获取恢复全磁盘恢复开启/关闭
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    protected function getRecoveryFullDisk(string $taskuuid)
    {
        $sql = "select level from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        return !empty($data) && $data[0]['level'] == xphp_get_config('vm', 'VM_RECOVERY_ZERO')['INCLOUD_ZERO'];
    }

   /**
     * 任务详情-概览: 文件是否配置通配符
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    protected function getWildCardInfo(string $taskuuid)
    {
        $sql = "select detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        if (empty($data)) {
            return false;
        }
        $count = 0;
        foreach ($data as $d) {
            $count += intval(json_decode($d['detail'], true)['wildcard_mode']);
        }

        //大于0则配置了通配符
        return $count > 0;
    }
    
    /**
     * 获取xen版本判断有无静默快照flag
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    protected function getXenSnapshotFlag(string $taskuuid)
    {
        $sql = "select distinct vcenter_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (empty($data)) {
            return false;
        }

        $sql = "select hypervisor_type, version from vm_vcenter where vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($data[0]['vcenter_uuid']));

        if (!empty($data)) {
            $vmhypervisonrtype = xphp_get_config('vm', 'VMHYPERVISORTYPE');
            if (
                in_array(
                    intval($data[0]['hypervisor_type']),
                    [
                        $vmhypervisonrtype['VM_HYPERVISOR_TYPE_XENSERVER'],
                        $vmhypervisonrtype['VM_HYPERVISOR_TYPE_XCP_NG']
                    ]
                ) && intval(substr($data[0]['version'], 0, 1)) >= 8
            ) {
                $flag = true;
            }
        }
        return $flag ?? false;
    }


    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    protected function getNodeNetworkFlag(string $taskuuid)
    {
        $sql = "select ba.net_model, bts.network_uuid from
                                           bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal
                where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d) {
            if (intval($d['net_model']) == 2 && !empty($d['network_uuid'])) {
                $flag = true;
            }
        }

        return $flag;
    }


        /**
     * 得到当前任务节点和存储信息
     * @param string $nodeuuid
     * @param string $storageuuid
     */
    public function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid, $taskUuid = '')
    {
        $info = array('flag' => false);
        if (
            xphp_get_config('task')['TASKTYPE']['BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['OS_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VM_HUAWEI_CBR_SYNC']!= $tasktype
        ) {
            return $info;
        }
        //恢复任务显示节点信息
        if (
            xphp_get_config('task')['TASKTYPE']['RECOVERY'] == $tasktype
            || xphp_get_config('task')['TASKTYPE']['OS_RECOVERY'] == $tasktype
            || xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] == $tasktype
        ) {
            $info['flag'] = true;
        }
        $sql = "SELECT bt.task_uuid, bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid,
                    bnp.node_pool_nickname, bsrp.storage_pool_nickname
                FROM bd_task bt
                    LEFT JOIN bd_node_pool bnp ON bt.node_pool_uuid = bnp.node_pool_uuid
                    LEFT JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid
                WHERE bt.task_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        if (is_array($taskData) && $taskData) {
            $info['node_uuid'] = $taskData[0]['node_uuid'];
            $info['node_pool_uuid'] = $taskData[0]['node_pool_uuid'];
            $info['storage_uuid'] = $taskData[0]['storage_uuid'];
            $info['node_pool_uuid'] = $taskData[0]['node_pool_uuid'];
            $info['storage_pool_uuid'] = $taskData[0]['storage_pool_uuid'];
            $info['node_pool_nickname'] = $taskData[0]['node_pool_nickname'];
            $info['storage_pool_nickname'] = $taskData[0]['storage_pool_nickname'];
        }
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size from bd_storage_resource 
            where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        if ($data) {
            $info['flag'] = true;
            $totalSize = v1_calsize($data[0]['total_size'], true);
            $freeSize = v1_calsize($data[0]['free_size'], true);
            $quotaDes = '';
            $quotaInfo = User::instance()->getUserQuotaInfo();

            $quotaFlag = false;     //分配配额标记
            //设置了配额或者是在租户内部
            if ($quotaInfo['quota'] != -1 || !empty($_SESSION['tenantuuid'])) {
                $quotaDes = $quotaInfo['des'];
                $quotaFlag = true;
            }
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => Storage::instance()->getStorageTypeDes($data[0]['storage_type']),
                'size' => $totalSize,
                'freesize' => $freeSize,
                'quotades' => $quotaDes,
                'tenantuuid' => $_SESSION['tenantuuid'],
                'quotaFlag' => $quotaFlag,
                'typenum'=>$data[0]['storage_type']
            );
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag, encrypted_flag, password_auto_flag, compress_method, encrypt_method from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if ($data) {
            $info['high'] = array(
                'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                'blocksize' => intval($data[0]['block_size']) / 1024 . 'KB',
                'compressed' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                'compress_method' => intval($data[0]['compress_method']),
                'encrypt_flag' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                'encrypt_method' => intval($data[0]['encrypt_method']),
            );
        }
        return $info;
    }

     /**
     * 获取节点名称和ip
     * @param $nodeuuid
     * @return array
     */
    private function getNodeNameAndIp($nodeuuid)
    {
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $re = array(
            'name' => xphp_get_config('app')['NULLSPACE'],
            'ip' => '',
        );
        if ($data) {
            $re = array(
                'name' => Node::instance()
                    ->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
                'ip' => $data[0]['ip'],
            );
        }
        return $re;
    }


    /**
     * 得到任务流量
     * @param unknown $params 参数
     * @return 流量
     */
    public function getTaskSpeed($params)
    {
        $taskUUID = $params['jobs_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.module_type, bri.speed, bri.speed_time, bt.task_status from bd_running_info bri, bd_task bt 
                where bri.task_uuid = bt.task_uuid and bri.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $speed = 0;
        if ($data) {
            if (
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
            ) {
                $speedCount = intval($data[0]['speed']);
                if ($speedCount < 0) {
                    $speedCount = 0;
                }
                $speed = round($speedCount / 1024, 2);
            }
        }
        if (time() - $data[0]['speed_time'] > 12) {
            //如果长时间没有更新速度,处理速度为0
            $speed = 0;
        }
        $data = array(
            'speed' => $speed,
            'test' => $data[0]['speed_time'],
            't' => time(),
            'nowTime' => date('H:i:s')
        );
        return $data;
    }


     /**
     * 获取hadoop详情里的集群列表等信息
     * @param $params uknown 参数
     * @return 返回组织基本信息
     */

    public function getHadoopDetail($params)
    {

        $taskUUID = $params['jobs_uuid'];
        $limit = $params['limit'];
        $offset = $params['offset'];
        $search = $params['search'];
        $sql = "select distinct hc.hadoop_cluster_uuid,hc.hadoop_cluster_name as nickname, 
        bt.task_name, bt.task_type,bt.task_status as fs_task_status, bt.module_type,   
        fpl.recovery_timepoint_uuid as sour_timepointuuid,fpl.backup_mode,fpl.new_root_path,  
        fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail,fbt.agent_uuid as uuid,ft.file_archive_flag, ft.submodule_type,
        btal.task_status,btal.task_uuid,btal.detail from bd_task bt left join fs_task ft on
        bt.task_uuid = ft.task_uuid left join fs_path_list fpl on
        bt.task_uuid = fpl.task_uuid left join bd_task_agent_list btal on
        fpl.task_uuid = btal.task_uuid left join hadoop_cluster hc on 
        btal.agent_uuid = hc.hadoop_cluster_uuid left join fs_backup_timepoint fbt on
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid where bt.task_uuid = ?";
        $sqlparams = array($taskUUID);

        if(!empty($search)){
            //只有备份才有搜索
            $sql .= " and hc.hadoop_cluster_name like ?";
            $sqlparams = array_merge($sqlparams, array('%' .$search. '%'));
        }
        $sql .= " group by hc.hadoop_cluster_uuid order by btal.id";
        if(!empty($limit)){
            $sql .= " limit ?, ?";
            $sqlparams = array_merge($sqlparams, array($offset,$limit));
        }
        $data = $this->dbSelect($sql, $sqlparams);
        $i = 1;
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        // 客户端数量
        $sqlCount = "select count(agent_uuid) as total from bd_task_agent_list where task_uuid = ? ";
        $agentcount = $this->dbSelect($sqlCount, array($taskUUID));
         if(!empty($search)){
            $sqlCount = "select count(btal.agent_uuid) as total from bd_task_agent_list btal, hadoop_cluster hc where hc.hadoop_cluster_uuid = btal.agent_uuid and  btal.task_uuid = ? and hc.hadoop_cluster_name like ?";
            $agentcount = $this->dbSelect($sqlCount, array($taskUUID,'%' .$search. '%'));
        }
        $info = array(
            'rows' => array(),
            'total' => $agentcount[0]['total']
        );

    	foreach ($data as $d){
            //文件列表
            $resourcetype = '';
            $list = array();
            if($d['task_type']==2) {//恢复
                //恢复任务还要判断bbt中的sub_module_type
                $resourcesql = "select bbt.sub_module_type as source_submodule_type from fs_path_list fpl 
                left join bd_backup_timepoint bbt ON fpl.recovery_timepoint_uuid = bbt.timepoint_uuid where fpl.task_uuid = ?";
                $resourceparams = array($d['task_uuid']);
                $resourceinfo = $this->dbSelect($resourcesql,$resourceparams);
                $resourcetype = $resourceinfo[0]['source_submodule_type'];
                $sql_path = "select path_name, new_root_path from fs_path_list where task_uuid = ?";
                $sqlParams_path = array($d['task_uuid']);
                // 文件个数总个数等信息
                $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.task_uuid = ?;";
                //源客户端名字
                switch ($resourcetype){
                    case 1: // 源端为文件客户端
                        $sourhost = $d['agent_name'] . '(' . $d['agent_ip'] . ')';
                        break;
                    case 2: // 源端为NAS设备
                        $sourhost = $d['agent_name'] . '(' . $d['agent_ip'] . ')';
                        break;
                    case 3: // 源端为Hadoop集群
                        $noderesult =  HadoopBackUp::instance() -> getClusterNode($d['uuid']);
                        $nodelist =  array();
                        $nodestr = '';
                        foreach($noderesult as $node){
                            $nodelist[] =  $node['namenode_ip'];
                        }
                        $nodestr = implode(" ",$nodelist);
                        $sourhost = $d['agent_name']. '(' . $nodestr . ')';
                        break;
                    case 4: // 源端为对象存储
                        $sourhost = $d['agent_name'];
                        break;
                    default:
                        break;
                }
                //目的客户端名字
                $noderesult =  HadoopBackUp::instance() -> getClusterNode($d['hadoop_cluster_uuid']);
                $nodelist =  array();
                $nodestr = '';
                foreach($noderesult as $node){
                    $nodelist[] =  $node['namenode_ip'];
                }
                $nodestr = implode(" ",$nodelist);
                $desagent = $d['nickname']. '(' . $nodestr . ')';
            }else {
                //备份
                $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.agent_uuid = ? and fri.task_uuid = ?;";
                $sql_path = "select path_name from fs_path_list where agent_uuid = ? and task_uuid = ?;";
                $sqlParams_path = array($d['hadoop_cluster_uuid'],$d['task_uuid']);
                $noderesult =  HadoopBackUp::instance() -> getClusterNode($d['hadoop_cluster_uuid']);
                $nodelist =  array();
                $nodestr = '';
                foreach($noderesult as $node){
                    $nodelist[] =  $node['namenode_ip'];
                }
                $nodestr = implode(" ",$nodelist);
                $sourhost = $d['nickname']. '(' . $nodestr . ')';
            }
            
            $data_path = $this->dbSelect($sql_path, $sqlParams_path);
            foreach($data_path as $each_path) {
                array_push($list,$each_path['path_name']);
            }
            //文件数量
            $data_count = $this->dbSelect($sql_count, $sqlParams_path);
            //通配符
            if($d['fs_task_status']!=2 || $d['task_status']==1) {//任务不是运行中,客户端是等待状态都应显示--
                $ttype = xphp_get_config('app', 'NULLSPACE');
                $total_fs_count = xphp_get_config('app', 'NULLSPACE');
                $current_fs_count = xphp_get_config('app', 'NULLSPACE');
                $current_dir_count = xphp_get_config('app', 'NULLSPACE');
                $agent_status = xphp_get_config('app', 'NULLSPACE');
                $progress = xphp_get_config('app', 'NULLSPACE');
                
            }else {
                if($d['task_type']==2) {//恢复
                    $ttype = xphp_get_lang($ptDes['TASKTYPEDES'][$d['task_type']]);
                }else {
                    $ttype =   xphp_get_lang($ptDes['BACKUP_MODE_DES'][$d['backup_mode']]). xphp_get_lang('UI_PLATFORM_BACKUP');;
                }
                $total_fs_count = $data_count[0]['total_fs_count'];
                $current_fs_count = $data_count[0]['current_fs_count'];
                $current_dir_count = $data_count[0]['current_dir_count'];
                $agent_status = xphp_get_lang($ptDes['AGENT_TASK_STATUS'][$d['task_status']]);
                $progress = $this->getFsAgentProgress($taskUUID,$d['hadoop_cluster_uuid'],intval($d['fs_task_status']),intval($d['task_status']));
            }
            $wildcardInfo = json_decode($d['detail'],true);
            $info["rows"][] = array(
                "agent_uuid"=>$d['hadoop_cluster_uuid'],
                "num"=> $i++,
                "job_type" => $ttype,
                "host_name" => $sourhost,
                'ttype'    => $ttype,
				"total_num" => $total_fs_count,
                "completed_num" => $current_fs_count,
                "src_data_num" => $current_dir_count,
                "progress" => $progress,
                "status" => $agent_status,
                'des' =>'',
                'list' =>  $list,
                'wildcardinfo' =>   array(
                    'wildcardmode' => $wildcardInfo['wildcard_mode'],
                    'wildcard' => $wildcardInfo['wildcard']
                ),
                'des_agent' => $desagent,
                'tasktype' => $d['task_type'],
                'taskStatus' => $d['fs_task_status'],
                'path' => $data_path[0]['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') :  $data_path[0]['new_root_path'],
                'source_submodule_type' => $resourcetype,
                'cross_platform_flag' => $d['submodule_type'] !== $resourcetype ? true : false,
                'cross_platform_des' => $this->getCrossPlatformDes($resourcetype)
            );
        }
        return  $info;
    }

    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param unknown $fbt_name 时间点中的主机名
     * @return name(ip)
     */
    public function getAgentNameStr($agent_uuid,$fbt_name,$ip){
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql,array($agent_uuid));
        if(!empty($result)){
            if($result[0]['agent_name'] == $ip){
                return $result[0]['hostname'].'('.$ip.')';
            }else{
                return $result[0]['agent_name'].'('.$ip.')';
            }
        }else {
            return $fbt_name.'('.$ip.')';
        }
    }


        /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示share_path,如果别名和IP一样则显示share_path
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param string $nasuuid $nasuuid
     * @param string $fbtname 时间点中的主机名
     * @param string $ip      时间点中的主机名
     * @return name(ip)
     */
    protected function getNasNameStr(string $nasuuid, $fbtname, $ip)
    {
        $sql = "select nas_nickname, share_path from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nasuuid));
        if (!empty($result)) {
            return $ip . '(' .
                ($result[0]['nas_nickname'] == $ip ? $result[0]['share_path'] : $result[0]['nas_nickname']) .  ')';
        } else {
            return $ip . '(' . $fbtname . ')';
        }
    }

    /**
     * 根据任务状态计算进度
     * @param string $taskUUID    任务uuid
     * @param string $agentUUID   客户端uuid
     * @param int    $taskStatus  任务状态
     * @param int    $agentStatus 客户端状态
     * @return string
     */
    protected function getFsAgentProgress(string $taskUUID, string $agentUUID, $taskStatus, $agentStatus)
    {
        $sql = "select current_object_total_size, current_object_completed_size
                from fs_running_info where task_uuid = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID,$agentUUID));
        $taskStatusArr = xphp_get_config('task', 'TASKSTATUS');
        if (!in_array($taskStatus, [$taskStatusArr['RUNNING'], $taskStatusArr['PAUSED'], $taskStatusArr['ABNORMAL']])) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }

        //备份任务只有目录时，文件大小为0  导致进度计算出来一直是0
        if ($agentStatus == 4) {
            return '100.00%';
        }

        $speed = v1_calpercent($data[0]['current_object_total_size'], $data[0]['current_object_completed_size']);
        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        return sprintf('%.2f', substr($speed, 0, -1)) . '%';
    }
    /**
     * 获取传输代理 agent ip
     *
     * @param string $appliance_uuid
     * @return void
     */
    private function getApplianceAgency(string $appliance_uuid)
    {
        $sql = 'select agent_name, ip from bd_agent where agent_uuid = ?';
        $data = $this->dbSelect($sql, array($appliance_uuid));

        $result = '';
        if (!empty($data)) {
            $result = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        }

        return $result;
    }

    /**
     * 启动备份任务
     * @param $params
     * @return string
     */
    public function startHadoopBackupJob($params){
        // 获取备份模式
        $backup_mode = $params['backup_mode'];
        // 获取任务uuid
        $task_uuid = $params['task_uuid'];
        // 获取对象存储列表
        $cluster_uuid_list = $params['cluster_uuids'];

        $time_strategy_id = 0;
        
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => xphp_get_config('app')['FLAG']['UNSET'],
            "fs_uuid_list" => $cluster_uuid_list,
        );
        $opName = 'BD_TASK_OP_BACKUP_START';
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->opUnifyMsg($task_uuid, $opName, $msg, false, true);

        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }


    }

    /**
     * 下载跳过文件
     * @param $params
     * @return string
     */
    public function downLoadPassFile($params){
        $nodeuuid = $params['fsnodeuuid'];
        $historyUuid = $params['history_uuid'];
        $filename = 'passfilelist';
        $agentUuid = $params['agent_uuid'];
        $opName = "FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET";
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        $msg = array(
            "history_uuid" => $historyUuid,
            "read_offset" => 0,
            "read_size" => $blockSize,
            "agent_uuid" => $agentUuid,
        );
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($result) {
            ob_clean();
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Content-Disposition: attachment; filename=" . $filename . ".txt");
            //如果大于分块大小,分块下载
            $filesize = $mbResult['msg']['file_size'];
            for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
                if ($filesize - $i <= $blockSize) {
                    $readLen = $filesize - $i;
                } else {
                    $readLen = $blockSize;
                }
                $msg = array(
                    "history_uuid" => $historyUuid,
                    "read_offset" => $i,
                    "read_size" => $readLen,
                    "agent_uuid" => $agentUuid,
                );

                $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);

                echo $mbResult['msg']['data'];
                ob_flush(); //将数据从php的buffer中释放出来
                flush(); //将释放出来的数据发送给浏览器
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取恢复源信息
     *
     * @param [type] $sourceModuleType
     * @return void
     */
    private function getCrossPlatformDes($sourceModuleType)
    {
        $des = '';
        switch ($sourceModuleType) {
            case 1:
                $des = xphp_get_lang('UI_HADOOP_RECOVER_SOURCE_FILE');
                break;
            case 2:
                $des = xphp_get_lang('UI_HADOOP_RECOVER_SOURCE_NAS');
                break;
            case 3:
                $des = "";
                break;
            case 4:
                $des = xphp_get_lang('UI_HADOOP_RECOVER_SOURCE_OBS');
                break;
        }

        return $des;
    }

    /**
     * 根据TASKUUID获取存储类型
     * @param string $taskUUID
     * @return mixed
     */
    private function getStorageType(string $taskUUID)
    {
        $storageType = '';

        $sql = "SELECT bsr.storage_type 
                FROM 
                    bd_storage_resource bsr, bd_backup_timepoint bbt, fs_path_list fpl 
                WHERE 
                    fpl.task_uuid = ? AND fpl.recovery_timepoint_uuid = bbt.timepoint_uuid AND bbt.storage_uuid = bsr.storage_uuid";

        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }

}

