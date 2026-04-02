<?php

namespace app\v1\nas\v0\logic;

use app\v1\common\logic\JobInfo;
use app\v1\common\logic\Backup;
use app\v1\tape\v0\logic\TapeInfo;
use app\v1\user\v0\logic\User;
use app\v1\resources\v0\logic\Storage;
use app\v1\resources\v0\logic\Node;

/**
 * note          NAS 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasJobInfo extends JobInfo
{
    /**
     * 任务详情: 得到NAS模块任务基本信息
     * @param array $params 参数
     * @return array
     */
    public function getBasicInfo(array $params)
    {
        $taskUUID = $params['job_uuid'];

        $sql = "SELECT 
                    bt.task_uuid, bt.task_name, bt.module_type, bt.sub_module_type, bt.task_type, 
                    bt.task_status, bt.thread_num, bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, 
                    bt.transport_ip_segment, bt.node_uuid, bt.storage_uuid, bt.task_orchestration_plan_flag,
                    bu.user_name, unix_timestamp(bs.next_start_time) next_start_time, bri.total_object_size, bri.total_object_completed_size, 
                    bri.speed, unix_timestamp(bri.start_time) start_time, bal.detail, nt.detail as ntdetail, nt.file_archive_flag,
                    nt.skip_file_alarm_flag, nt.skip_file_alarm_min_num, nt.skip_file_alarm_min_ratio, nt.permission_operate_flag, 
                    nt.same_file_strategy, nt.link_file_pass_flag, nt.dir_tree_recovery_flag, fpl.new_root_path 
                FROM 
                    bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs, bd_task_agent_list bal, nas_task nt, fs_path_list fpl     
                WHERE 
                    bt.user_uuid = bu.user_uuid AND 
                    bt.task_uuid = bri.task_uuid AND 
                    bt.strategy_id = bs.strategy_id AND 
                    bt.task_uuid = bal.task_uuid AND 
                    bt.task_uuid = nt.task_uuid AND 
                    bt.task_uuid = fpl.task_uuid AND 
                    bt.task_uuid = ? ";

        $data = $this->dbSelect($sql, array($taskUUID));

        $basicInfo = array();
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        $tapeHandler = new TapeInfo();

        foreach ($data as $d) {
            $ntdetail = json_decode($d['ntdetail'], true);
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => $ptDes['MODULE_TYPE_DES'][$d['module_type']],
                'taskType' => $this->getBasicInfoTaskTypeDes(
                    $ptDes['TASKTYPEDES'][$d['task_type']],
                    $d['module_type'],
                    $d['task_uuid']
                ),
                'user' => $d['user_name'],
                'status' => $ptDes['TASKSTATUSDES'][$d['task_status']],
                'statusValue' => intval($d['task_status']),
                'totalSize' => v1_calsize($d['total_object_size']),
                'currentSize' => v1_calsize($d['total_object_completed_size']),
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
                'time_strategy_backup_type' => Backup::instance()->getTimeStrategyBackupType($d['strategy_id']),
                'reservedStrategy' => $this->getReservedStrategy($d['strategy_id'], $d['task_uuid']),
                'transportStrategy' => $this->getFsTransportStrategy($taskUUID, $d['strategy_id']),
                'storageInfo' => $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $d['storage_uuid']),
                'flag' => true,
                'taskTypeFlag' => intval($d['task_type']),      //迁移用
                'modeStrategy' => "",
                'thread_num' => intval($d['thread_num']),
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),
                // 'hypervisor' => $this->getHypervisor($d['task_uuid']),
                'fulldiskRestore' => $this->getRecoveryFullDisk($d['task_uuid']),
                'nosnapshot' => 1,
                'transport_ip_segment' => $d['transport_ip_segment'],
                'snapShotFlag' => $d['snap_shot_flag'],
                'wildcardMode' => intval(json_decode($d['detail'], true)["wildcard_mode"]),
                'scan_thread_num' => intval($ntdetail['scan_thread_num']),
                'scan_file_num' => intval($ntdetail['scan_file_num']),
                'archiveflag' => $d['file_archive_flag'],
                'skip_file_alarm_flag' => $d['skip_file_alarm_flag'] == 1 ? true : false,
                'skip_file_alarm_min_num' => $d['skip_file_alarm_min_num'],
                'skip_file_alarm_min_ratio' => $d['skip_file_alarm_min_ratio'] . '%',
                'permission_operate_flag' => $d['permission_operate_flag'] == 1 ? true : false,
                'same_file_strategy' => $d['same_file_strategy'],
                'link_file_pass_flag' => $d['link_file_pass_flag'] == 1 ? true : false,
                'dir_tree_recovery_flag' => $d['dir_tree_recovery_flag'] == 1 ? true : false,
                'new_root_path' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $this->getNasResorceInfo($taskUUID)['source_agent_type'],//agent_type
                'src_sub_module_type' => $this->getNasResorceInfo($taskUUID)['src_type'],//sub_module_type
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $tapeHandler->getTapeGroupStrategy($reserveParams) ?? '',
                'storage_type' => $this->getStorageType($taskUUID)
            );
        }

        return $basicInfo;
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

    /**
     * 获取nas源设备的信息
     * @param unknown $params
     */
    private function getNasResorceInfo($taskuuid)
    {
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
        $needTime = ($totalSize - $currentSize) / $speed;
        return date('Y-m-d H:i:s', time() + intval($needTime));
    }

    /**
     * 得到文件任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    private function getFsTransportStrategy($taskuuid, $strategyid)
    {
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method from bd_transport_strategy bts left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v1_parse_flag_to_bool($data[0]['encrypt_flag']);
        $info['encrypt_method'] = $data[0]['transport_method'];
        $info['compress'] = v1_parse_flag_to_bool($data[0]['compress_flag']);

        //获取传输网络描述
        $name = "";
        if (!empty($data[0]['ip'])) {
            $name = $data[0]['ip'] . ":" . $data[0]['port'];
            if (!empty($data[0]['alias_name'])) {
                $name .= "(" . $data[0]['alias_name'] . ")";
            }
        }
        $info['network'] = $name;
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];

        return $info;
    }

    /**
     * 得到当前任务节点和存储信息
     * @param string $nodeuuid
     * @param string $storageuuid
     */
    public function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid)
    {
        $info = array('flag' => false);
        if (
            xphp_get_config('task')['TASKTYPE']['BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['OS_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VM_HUAWEI_CBR_SYNC'] != $tasktype
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
                'typenum' => $data[0]['storage_type']
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
     * 获取nas设备列表
     * @param array $params 参数
     * @return string
     */
    public function getTaskDetail($params)
    {
        $taskUUID = $params['job_uuid'];
        $sql = "select distinct ba.ip, ba.hostname,  ba.detail,ba.agent_uuid,
        bt.task_name, bt.task_type,bt.task_status as fs_task_status, bt.module_type,   
        fpl.recovery_timepoint_uuid as sour_timepointuuid,fpl.backup_mode,fpl.new_root_path,  
        fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail,fbt.agent_uuid as uuid,nt.file_archive_flag,
        btal.task_status,btal.task_uuid,btal.detail,
        nt.nas_uuid from bd_task bt left join nas_task nt on
        bt.task_uuid = nt.task_uuid left join fs_path_list fpl on
        nt.task_uuid = fpl.task_uuid left join bd_task_agent_list btal on
        fpl.task_uuid = btal.task_uuid left join bd_agent ba on 
        btal.agent_uuid = ba.agent_uuid left join fs_backup_timepoint fbt on
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid where nt.task_uuid = ?
        group by ba.agent_uuid order by ba.id  limit ?,?";
        $data = $this->dbSelect($sql, array($taskUUID, $params['offset'], $params['limit']));
        $i = 1;
        $taskType = xphp_get_desc('Pf', 'TASKTYPEDES');
        $backupModeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $agentTaskStatus = xphp_get_desc('Pf', 'AGENT_TASK_STATUS');
        $records = [];
        // nas设备数量
        $sqlCount = "select count(nas_uuid) as total from nas_task where task_uuid = ? ";
        $agentcount = $this->dbSelect($sqlCount, array($taskUUID));
        foreach ($data as $d) {
            if ($d['task_type'] == 2) {
                // 恢复
                $sourhost = $this->getNasCopyNameStr($d['uuid'], $d['agent_name'], $d['agent_ip']);
                // nas恢复到文件
                $fbtdetail = json_decode($d['fbtdetail'], true);
                if ($fbtdetail['nas_type'] == 0) {
                    // 源是文件  文件的nas_type为0 nas为6、7
                    $sourhost = $this->getCopyNameStr($d['uuid'], $d['agent_name'], $d['agent_ip']);
                }
            }
            //nas设备
            $nasdevice = $this->dbSelect(
                'select nsr.ip, nsr.nas_nickname, nsr.share_path
                    from nas_storage_resource nsr, nas_task nt where nt.nas_uuid = nsr.nas_uuid and nt.task_uuid = ?;',
                array($taskUUID)
            );
            if ($nasdevice[0]['nas_nickname'] == $nasdevice[0]['ip']) {
                $desnas = $nasdevice[0]['ip'] . '(' . $nasdevice[0]['share_path'] . ')';
            } else {
                $desnas = $nasdevice[0]['ip'] . '(' . $nasdevice[0]['nas_nickname'] . ')';
            }
            //文件列表
            $list = array();
            // 路径
            $sqlpath = "select path_name from fs_path_list where task_uuid = ?;";
            // 文件个数总个数等信息
            $sqlcount = "select total_fs_count,current_fs_count,current_dir_count
                            from fs_running_info fri where fri.task_uuid = ?;";
            $sqlParamspath = array($d['task_uuid']);
            $datapath = $this->dbSelect($sqlpath, $sqlParamspath);
            foreach ($datapath as $eachpath) {
                array_push($list, $eachpath['path_name']);
            }
            //文件数量
            $datacount = $this->dbSelect($sqlcount, $sqlParamspath);
            //通配符
            if ($d['fs_task_status'] != 2 || $d['task_status'] == 1) {//任务不是运行中,客户端不是等待状态都应显示--
                $ttype = $totalfscount = $currentfscount = $currentdircount = $agentstatus
                    = xphp_get_config('app', 'NULLSPACE');
            } else {
                if ($d['task_type'] == 2) {
                    // 恢复
                    $ttype = $taskType[$d['task_type']];
                } else {
                    $ttype = $backupModeDes[$d['backup_mode']] . xphp_get_lang('UI_PLATFORM_BACKUP');
                }
                $totalfscount = $datacount[0]['total_fs_count'];
                $currentfscount = $datacount[0]['current_fs_count'];
                $currentdircount = $datacount[0]['current_dir_count'];
                $agentstatus = $agentTaskStatus[$d['task_status']];
            }
            $wildcardInfo = json_decode($d['detail'], true);
            $records[] = array(
                'id' => $d['id'],
                'num' => $i++,
                'nickname' => $desnas,
                'job_type' => $taskType[$d['task_type']],
                'total_fs_count' => $totalfscount,
                'current_fs_count' => $currentfscount,
                'current_dir_count' => $currentdircount,
                'agent_status' => $agentstatus,
                'path_name' => $list,
                'job_status' => $d['task_status'],
                'popover' => $agentTaskStatus[$d['task_status']],
                'des_agent' => '',
                'wildcard_mode' => $wildcardInfo['wildcard_mode'],
                'wildcard' => $wildcardInfo['wildcard'],
                'nas_status' => $sourhost,
                'res_nas' => $sourhost,
                'path' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'file_archive_flag' => $d['file_archive_flag'],
                'ttype' => $ttype,
            );
        }

        return [
            'rows' => $records,
            'total' => $agentcount[0]['total']
        ];
    }

    /**
     * 获取nas历史任务表格信息
     * @param mixed $params
     * @return void
     */
    public function getNasHistory($params)
    {
        $taskUUID = $params['task_uuid'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sortName = $params['sort'];
        $sortOrder = $params['order'];

        $sql =
            "SELECT 
                history_uuid, task_type, module_type, submodule_type, current_mode, 
                error_code, details, total_object_size, total_object_transport_size, total_object_write_size,
                total_object_completed_size, average_speed, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time 
            FROM 
                bd_history_task 
            WHERE 
                task_uuid = ?
    	    ORDER BY 
                $sortName $sortOrder 
            limit ? , ? ";

        $data = $this->dbSelect($sql, array($taskUUID, $offset, $limit));

        $sqlCount = "select count(task_type) as total from bd_history_task where task_uuid = ? ";
        $countData = $this->dbSelect($sqlCount, array($taskUUID));

        $info = array(
            'rows' => array(),
            'total' => $countData[0]['total'],
        );

        if (!empty($data)) {
            $i = 1;
            foreach ($data as $d) {
                $info['rows'][] = array(
                    'num' => $i++,
                    'id' => $d['id'],
                    'history_uuid' => $d['history_uuid'],
                    'task_type' => $this->getHistoryTaskType($d['task_type'], $d['current_mode'], $d['submodule_type']),
                    'job_status' => $this->getHistoryJobResultDes($d['error_code']),
                    'total_object_size' => v1_calsize($d['total_object_size'], true),
                    'total_object_completed_size' => v1_calsize($d['total_object_completed_size'], true),
                    'total_object_write_size' => v1_calsize($d['total_object_write_size'], true),
                    'start_time' => $d['start_time'],
                    'finish_time' => $d['finish_time']
                );
            }
        }

        return $info;
    }

    /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param unknown $fbt_name 时间点中的主机名
     * @return name(ip)
     */
    public function getNasCopyNameStr($nasuuid, $name, $ip)
    {
        $sql = "select nas_nickname, share_path, ip from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nasuuid));
        if (!empty($result)) {
            if ($result[0]['nas_nickname'] == $ip) {
                return $ip . '(' . $result[0]['share_path'] . ')';
            } else {
                return $ip . '(' . $result[0]['nas_nickname'] . ')';
            }
        } else {
            return $ip . '(' . $name . ')';
        }
    }

    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param unknown $fbt_name 时间点中的主机名
     * @return name(ip)
     */
    public function getCopyNameStr($uuid, $name, $ip)
    {
        $sql = "select agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($uuid));
        if (!empty($result)) {
            if ($result[0]['agent_name'] == $result[0]['ip']) {
                return $result[0]['hostname'] . '(' . $result[0]['ip'] . ')';
            } else {
                return $result[0]['agent_name'] . '(' . $result[0]['ip'] . ')';
            }
        } else {
            return $name . '(' . $result[0]['ip'] . ')';
        }
    }

    /**
     * 获取任务appliance信息
     * @param string $taskuuid
     */
    private function getJobAppliance($taskuuid)
    {
        $sql = "select ba.ip, ba.agent_name from bd_agent ba, vm_task vt where vt.agent_uuid = ba.agent_uuid and vt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!empty($data)) {
            $flag = true;
            $des = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        } else {
            $flag = false;
            $des = xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        }

        return ['flag' => $flag, 'des' => $des];
    }

    /**
     * 获取恢复全磁盘恢复开启/关闭
     * @param unknown $taskuuid
     * @return number
     */
    private function getRecoveryFullDisk($taskuuid)
    {
        $sql = "select level from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;
        if (!empty($data)) {
            $level = intval($data[0]['level']);
            if ($level == xphp_get_config('VM_RECOVERY_ZERO')['INCLOUD_ZERO']) {
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getNodeNetworkFlag($taskuuid)
    {
        $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
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
     * 任务详情-概览: 文件是否配置通配符
     * @param unknown $params
     */
    private function getWildCardInfo($task_uuid)
    {
        $sql = "select detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $count = 0;
        foreach ($data as $d) {
            $count += intval(json_decode($d['detail'], true)["wildcard_mode"]);
        }
        if ($count > 0) {//大于0则配置了通配符
            return true;
        }
        return false;
    }
}