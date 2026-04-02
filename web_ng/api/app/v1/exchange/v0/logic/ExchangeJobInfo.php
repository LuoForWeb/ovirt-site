<?php

namespace app\v1\exchange\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\tape\v0\logic\TapeInfo;
use app\v1\user\v0\logic\User;

/**
 * note          office365（exchange） 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeJobInfo extends JobInfo
{
    /**
     * 任务详情: 得到Exchange模块任务基本信息
     * @param unknown $params 参数
     * @return array 任务基本信息
     */
    public function getBasicInfo($params = [])
    {
        $taskUUID = $params['jobs_uuid'];
        $info = (new \app\v1\job\v0\logic\JobInfo())->getJobInfo($taskUUID);
        $sql = " select bt.agent_uuid, bt.strategy_id,  bt.task_orchestration_plan_flag, bt.user_uuid,
            mt.detail as mtdetail,mt.m365_retry_info, mo.agent_uuid_list,mo.region 
            from bd_task bt,  bd_strategy bs, m365_task mt, m365_organization mo
            where mt.task_uuid = ? and bt.strategy_id = bs.strategy_id and bt.task_uuid = mt.task_uuid and mt.organization_uuid = mo.organization_uuid";
        if ($info['job_type'] == 2) {
            $sql = "select bt.agent_uuid, bt.strategy_id,  bt.task_orchestration_plan_flag, bt.user_uuid,
            mt.detail as mtdetail,mt.m365_retry_info, mo.agent_uuid_list,mo.region 
            from bd_task bt,  bd_strategy bs, m365_task mt, m365_organization mo,m365_object_list mol
            where mt.task_uuid = ? and bt.strategy_id = bs.strategy_id and 
            bt.task_uuid = mt.task_uuid and bt.task_uuid = mol.task_uuid and mol.destination_organization_uuid = mo.organization_uuid";
        }
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $agentDes = '';
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        if (!$data) {
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        $agentConfigFlag = false;
        foreach ($data as $d) {
            if ($d['agent_uuid'] != "") {
                $agentConfigFlag = true;
                $agentDes = $this->getAgentDes($d['agent_uuid']);
            }
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
                'time_strategy_backup_type' => Backup::instance()->getTimeStrategyBackupType($d['strategy_id']),
                'reserve_strategy'  => $info['reserve_strategy'], // 保留策略
                'transport_strategy'  => $info['transport_strategy'], // 传输策略
                'module_type' => $info['module_type'],
                'module_type_des' => $info['module_type_des'],
                'job_type_des' => $info['job_type_des'],
                'flag' => true,
                'region' => $this->getDesRegion($taskUUID, $info['job_type'], $d['region']),//目标组织的region
                'network_flag' => $this->getNetworkShowFlag($taskUUID,$info['module_type'], $info['job_type']),//检查显示传输网络标志
                'agent_config_flag' => $agentConfigFlag,//是否配置客户端
                'agent_des' => $agentDes,
                'agent_uuid' => $d['agent_uuid'],
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'tape_strategy' => TapeInfo::instance()->getTapeGroupStrategy(array("group_uuid" => $info['storage_uuid'])) ?? '',
                'storage_type' => $this->getStorageTypeByTaskId($taskUUID, $info['job_type']),
                //安全策略
                'safe_strategy' => $info['safe_strategy'],
                'retry_strategy' => $info['retry_strategy'],
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($info['ignore_resource_limiting_flag']),
                'current_stage' => $info['current_stage'], // 任务阶段
                'stage_percent' => $info['stage_percent'], // 任务百分比
                'current_stage_value' => $info['current_stage_value'], // 任务阶段+任务百分比
                'task_user_uuid' => $d['user_uuid'],
            );
        }
        return $basicInfo;
    }

    /**
     * 任务详情-根据任务uuid获取重试策略相关信息
     * @param string $taskUUID 任务uuid
     * @return object 重试策略相关信息
     */
    public function getRetryStrategy($taskUUID) {
        $info = array();
        $sql = "select network_retry_times,network_retry_interval,op_retry_times,op_retry_interval,task_retry_object,
            task_retry_times, task_retry_interval from bd_retry_strategy where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            //操作重试开关
            $opRetryFlag = true;
            if ($data[0]['op_retry_times'] == 0) {
                $opRetryFlag = false;
            }
            //任务重试开关
            $taskRetryFlag = true;
            if ($data[0]['task_retry_times'] == 0) {
                $taskRetryFlag = false;
            }
            $info = array(
                'network_retry_times' => $data[0]['network_retry_times'],
                'network_retry_interval' => $data[0]['network_retry_interval'],
                'op_retry_flag' => $opRetryFlag,
                'op_retry_times' => $data[0]['op_retry_times'],
                'op_retry_interval' => $data[0]['op_retry_interval'],
                'task_retry_flag' => $taskRetryFlag,
                'task_retry_object' => $data[0]['task_retry_object'],
                'task_retry_times' => $data[0]['task_retry_times'],
                'task_retry_interval' => $data[0]['task_retry_interval'],
            );
        }
        return $info;
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
        $sql = "select bsr.storage_type from bd_storage_resource bsr, bd_backup_timepoint bbt, m365_task mt where 
        mt.task_uuid = ? and mt.recovery_timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }

    /**
     * 获取根据uuid客户端名
     * @return string 客户端信息
     */
    public function getAgentDes($agentUuid)
    {
        $info = '';
        $sql = "select agent_uuid,agent_name,hostname,ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agentUuid));
        if (!empty($result)) {
            $name = $result[0]['agent_name'] . '(' . $result[0]['ip'] . ')';
            if ($result[0]['agent_name'] == $result[0]['ip']) {
                $name = $result[0]['hostname'] . '(' . $result[0]['ip'] . ')';
            }
            $info = $name;
        }
        return $info;
    }

    /**
     * 获取目标组织的region
     * @param string $taskUuid  任务uuid
     * @param int    $taskType  任务类型
     * @param int    $regionDes 地区
     * @return int $region
     */
    public function getDesRegion($taskUuid, $taskType, $regionDes)
    {
        $region = $regionDes;//备份
        if ($taskType == 2) {//恢复
            $sql = "select mo.region from m365_organization mo,m365_object_list mol where mol.task_uuid = ?
            and mol.destination_organization_uuid = mo.organization_uuid";
            $data = $this->dbSelect($sql, array($taskUuid));
            if (!empty($data)) {
                $region = $data[0]['region'];
            }
        }
        return $region;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid      任务uuid
     * @param array  $agentuuidlist 关联的agentuuid
     * @param string $agentUuid     指定客户端uuid
     * @return boolean
     */
    public function getNodeNetworkFlag(string $taskuuid, array $agentuuidlist, $agentUuid)
    {
        $flag = false;  //定义传输网络标志
        //指定了客户端
        if (!empty($agentUuid)) {
            $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba
                where bts.task_uuid = ? and ba.agent_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid, $agentUuid));
            if (intval($data[0]['net_model']) == 2 && !empty($data[0]['network_uuid'])) {
                return true;
            }else {
                return false;
            }
        }
        //没有指定客户端
        if (!empty($agentuuidlist)) {
            foreach ($agentuuidlist as $agent) {
                $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba
                where bts.task_uuid = ? and ba.agent_uuid = ?";
                $data = $this->dbSelect($sql, array($taskuuid, $agent));
                if (intval($data[0]['net_model']) == 2 && !empty($data[0]['network_uuid'])) {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }

    /**
     * 根据任务状态计算速度
     * @param unknown $taskStatus 任务状态
     * @param  unknown $speed      速度
     * @return   速度
     */
    public function getJobSpeed($taskStatus, $speed)
    {
        if ($taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
            ;
        }
        return v1_calspeed($speed);
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
     * 得到任务传输策略
     * @param string $taskuuid   任务UUID
     * @param int    $strategyid 策略ID
     * @return  传输策略
     */
    public function getTransportStrategy($taskuuid, $strategyid)
    {
        $info = array();
        //加密传输,XenServer使用
        $sql = "select encrypt_flag from bd_transport_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v1_parse_flag_to_bool($data[0]['encrypt_flag']);
        return $info;
    }

    /**
     * 得到当前任务节点和存储信息
     * @param string $tasktype    任务类型
     * @param string $strategyid  策略id
     * @param string $nodeuuid    节点uuid
     * @param string $storageuuid 存储uuid
     * @return 节点和存储信息
     */
    private function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid)
    {
        $info = array('flag' => false);
        if (
            xphp_get_config('task', 'TASKTYPE')['BACKUP'] != $tasktype
            && xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'] != $tasktype
            && xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'] != $tasktype
            && xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] != $tasktype
            && xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'] != $tasktype
            && xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] != $tasktype
        ) {
            return $info;
        }
        //恢复任务显示节点信息
        if (
            xphp_get_config('task', 'TASKTYPE')['RECOVERY'] == $tasktype
            || xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY'] == $tasktype
            || xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'] == $tasktype
        ) {
            $info['flag'] = true;
        }
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        if ($data) {
            $info['flag'] = true;
            $totalSize = v1_calsize($data[0]['total_size'], true);
            $freeSize = v1_calsize($data[0]['free_size'], true);
            $quotaDes = '';
            $quotaInfo = (new User())->getUserQuotaInfo();
            $quotaFlag = false;     //分配配额标记
            //设置了配额或者是在租户内部
            if ($quotaInfo['quota'] != -1 || !empty($_SESSION['tenantuuid'])) {
                $quotaDes = $quotaInfo['des'];
                $quotaFlag = true;
            }
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => empty($data[0]['storage_type']) ? '' : xphp_get_desc('Pf', 'STORAGETYPE')[$data[0]['storage_type']],
                'size' => $totalSize,
                'freesize' => $freeSize,
                'quotades' => $quotaDes,
                'tenantuuid' => $_SESSION['tenantuuid'],
                'quotaFlag' => $quotaFlag
            );
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag, encrypted_flag, password_auto_flag from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if ($data) {
            $info['high'] = array(
                'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                'blocksize' => intval($data[0]['block_size']) / 1024 . 'KB',
                'compressed' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                'encrypt_flag' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
            );
        }
        return $info;
    }

    /**
     * 获取节点名称和ip
     * @param $nodeuuid 节点uuid
     * @return array
     */
    public function getNodeNameAndIp($nodeuuid)
    {
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $re = array(
            'name' => xphp_get_config('app', 'NULLSPACE'),
            'ip' => '',
        );
        if ($data) {
            $re = array(
                'name' => $this->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
                'ip' => $data[0]['ip'],
            );
        }
        return $re;
    }

    /**
     * 得到展示的节点名
     * @param $ip       ip
     * @param $nickname 别名
     * @param $hostname 主机名
     * @return  展示的节点名
     */
    public function getNodeGridName($ip, $nickname, $hostname)
    {
        if ($ip == $nickname || empty($nickname)) {
            return $hostname;
        } else {
            return $nickname;
        }
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
     * 获取exchange详情里的组织列表等信息
     * @param $params uknown 参数
     * @return 返回组织基本信息
     */
    public function getExchangeDetail($params)
    {
        $jobsuuid = $params['jobs_uuid'];
        $sql = "select mo.organization_uuid,mo.agent_uuid_list,mo.organization_name,mo.nickname,mo.region,mo.auth_apps,bt.task_status,
       bt.task_type,mri.current_complete_item_count, mri.current_complete_user_count,mri.total_user_count,mri.total_item_count,
       mol.destination_organization_uuid, mol.recovery_object_info,mt.detail,mol.destination_user_uuid,bri.current_mode,
        mo.online_flag,mo.error_code,mo.create_time from m365_organization mo left join m365_task mt 
        on mo.organization_uuid  = mt.organization_uuid left join bd_task bt on bt.task_uuid = mt.task_uuid left join 
        m365_running_info mri on bt.task_uuid = mri.task_uuid left join m365_object_list mol on bt.task_uuid = mol.task_uuid 
        left join bd_running_info bri on bt.task_uuid = bri.task_uuid where bt.task_uuid = ?";
        $sqlcount = "select count(organization_uuid) as total from m365_organization";
        $sqlparams = array($jobsuuid);
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount);
        $info = array(
            'rows' => array(),
            'total' => 1,//详情页只会有一个组织
        );
        //获取所有备份对象和排除目录信息
        $srcDataInfo = $this->getSrcDataInfo($jobsuuid);
        $recoveryobjectinfo = array();
        if ((empty($result)) || (!empty($result) && $result[0]['task_type'] == 2)) {//恢复
            $sql = 'select bt.task_status, bt.task_type,mri.current_complete_item_count, mri.current_complete_user_count,mri.total_user_count,mri.total_item_count,
                mol.destination_organization_uuid, mol.recovery_object_info,mol.destination_user_uuid,mt.detail, mbt.organization_info from m365_task mt left join bd_task bt on bt.task_uuid = mt.task_uuid 
                    left join m365_running_info mri on bt.task_uuid = mri.task_uuid left join m365_object_list mol on bt.task_uuid = mol.task_uuid left join m365_backup_timepoint mbt 
                        on mbt.m365_timepoint_uuid = mt.recovery_timepoint_uuid where bt.task_uuid = ?';
            $sqlparams = array($jobsuuid);
            $result = $this->dbSelect($sql, $sqlparams);
        }
        if (!empty($result)) {
            foreach ($result as $each) {
                $recoveryobjectinfo[] = json_decode($each['recovery_object_info'], true);
            }
                //认证方式:app_secret有值就是密码认证
//                $verifyway = '证书方式';
//                $verifyvalue = 2;
//            if ($result[0]['region'] == 100) {//本地版
//                $sqlApp = 'select app_secret, tenant_uuid, app_uuid, app_cert_info, app_name, username from
//                                m365_azure_ad_app where organization_uuid = ?';
//                $appResult = $this->dbSelect($sqlApp, array($result[0]['organization_uuid']));
//                if (!empty($appResult[0]['app_secret'])) {
//                    $verifyway = '密码方式';
//                    $verifyvalue = 1;
//                }
//            }
            $detail = json_decode($result[0]['detail'], true);
            $jobStatus = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[$result[0]['current_mode']] . xphp_get_lang('UI_PLATFORM_BACKUP');
            $recoveryType = '';
            $organizationname = $result[0]['organization_name'];
            if (!empty($result[0]['nickname'])) {
                $organizationname = $result[0]['nickname'];
            }
            if ($result[0]['task_type'] == 2) {//恢复
                $recoveryType = $detail['overwrite'];
                $organizationinfo = json_decode($result[0]['organization_info'], true);
                $organizationname = $organizationinfo['organization_name'];
                $jobStatus = xphp_get_lang('WEB_PLATFORM_DES_RECOVERY');
            }
            if ($result[0]['task_status'] != xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                $info['rows'][] = array(
                    'organization_name' => $organizationname,
                    'jobs_status' => xphp_get_config('app', 'NULLSPACE'),
                    'user_num' => xphp_get_config('app', 'NULLSPACE'),//用户、用户组个数
                    'completed_num' => xphp_get_config('app', 'NULLSPACE'),//已完成个数
                    'src_data_num' => xphp_get_config('app', 'NULLSPACE'),//已备份数据条数
                    'status' => xphp_get_config('app', 'NULLSPACE'),// 状态
                    'src_data_list' => $srcDataInfo[0]['src_data_list'],
                    'exclude_dir' => $srcDataInfo[0]['exclude_dir'],
                    'des_organization_name' => $this->getOrganizationName($result[0]['destination_organization_uuid']),
                    'des_user_name' => $this->getUserName($result[0]['destination_user_uuid']),
                    'recovery_type' => $recoveryType == 1 ? xphp_get_lang('WEB_M365_OVERWRITE_RECOVERY') : xphp_get_lang('WEB_M365_NEW_RECOVERY'),
                    'recovery_object_info' => $recoveryobjectinfo,
                );
            } else {
                $info['rows'][] = array(
                    'organization_name' => $organizationname,
                    'jobs_status' => $jobStatus,
                    'user_num' => $result[0]['total_user_count'],//用户、用户组个数
                    'completed_num' => $result[0]['current_complete_user_count'],//已完成个数
                    'src_data_num' => $result[0]['current_complete_item_count'],//已备份数据条数
                    'status' => $result[0]['task_status'],// 状态
                    'src_data_list' => $srcDataInfo[0]['src_data_list'],
                    'exclude_dir' => $srcDataInfo[0]['exclude_dir'],
                    'des_organization_name' => $this->getOrganizationName($result[0]['destination_organization_uuid']),
                    'des_user_name' => $this->getUserName($result[0]['destination_user_uuid']),
                    'recovery_type' => $recoveryType == 1 ? xphp_get_lang('WEB_M365_OVERWRITE_RECOVERY') : xphp_get_lang('WEB_M365_NEW_RECOVERY'),
                    'recovery_object_info' => $recoveryobjectinfo,
                );
            }
        }
        return $info;
    }

    /**
     * 获取所有备份对象和排除目录信息
     * @param $jobsuuid 任务id
     * @return  备份对象和排除目录信息
     */
    private function getSrcDataInfo($jobsuuid)
    {
        $info = array();
        $srcDataList = array();
        $sql = "select exclude_folders, backup_object_info from m365_object_list where task_uuid = ?";
        $result = $this->dbSelect($sql, array($jobsuuid));
        if (!empty($result)) {
            foreach ($result as $each) {
                $detail = json_decode($each['backup_object_info'], true);
                if ($detail['backup_object_type'] == 10000) {//组织
                    $srcDataList[] = $detail['backup_object_name'];
                } else {
                    $srcDataList[] = $detail['backup_object_name'] . '(' . $detail['backup_object_mail'] . ')';
                }
                $excludeDir = json_decode($each['exclude_folders'], true);
            }
            if (empty($excludeDir)) {
                $excludeDir = xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
            }
            $info[] = array(
                'src_data_list' => $srcDataList,
                'exclude_dir' => $excludeDir,
            );
        }
        return $info;
    }

    /**
     * 获取当前任务的某个任务的任务流量
     * @return 返回任务基本信息
     */
    public function getExchangeHistory()
    {
        $sql = "select mo.organization_uuid,mo.agent_uuid_list,mo.organization_name,mo.nickname,mo.region,mo.auth_apps,
        mo.online_flag,mo.error_code,mo.create_time,maaa.app_secret,maaa.tenant_uuid,maaa.app_uuid,maaa.app_cert_info,maaa.app_name,
       maaa.username from m365_organization mo,m365_azure_ad_app maaa where mo.organization_uuid 
        = maaa.organization_uuid";
        $sqlcount = "select count(organization_uuid) as total from m365_organization";
        $sqlparams = array();
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount, $sqlparams);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['total'],
        );

        if (!empty($result)) {
            foreach ($result as $each) {
                $name = $each['organization_name'];
                if (!empty($each['nickname'])) {
                    $name = $each['nickname'];
                }
                //认证方式:app_secret有值就是密码认证
                $verifyway = xphp_get_lang('WEB_M365_CERTIFICATE_METHOD');
                $verifyvalue = 2;
                if (!empty($each['app_secret'])) {
                    $verifyway = xphp_get_lang('WEB_M365_PASSWORD_METHOD');
                    $verifyvalue = 1;
                }
                //证书信息
                $appcertinfo = json_decode($each['app_cert_info'], true);
                $info['rows'][] = array(
                    'organization_uuid' => $each['organization_uuid'],
                    'agent_list' => $this->getAgentIp(json_decode($each['agent_uuid_list'], true)),
                    'organization_name' => $name,
                    'region' => $each['region'],//组织区域
                    'online_flag' => $each['online_flag'],//在线标志
                    'error_code' => $each['error_code'],
                    'create_time' => $each['create_time'],
                    'app_auth' => $this->getAppIcon(json_decode($each['auth_apps'], true)),//已授权的应用
                    'verify_way' => $verifyway,//认证方式
                    'type' => $each['region'] == 100 ? 'Microsoft 365 on-premises' : 'Microsoft 365',//类型
                    'type_value' => $each['region'] == 100 ? 2 : 1,//类型的value:2 server  1 online
                    'verify_value' => $verifyvalue,//认证方式的value  2密码  1证书
                    'tenant_uuid' => $each['tenant_uuid'],
                    'app_name' => $each['app_name'],
                    'app_uuid' => $each['app_uuid'],
                    'username' => $each['username'],
                    'app_secret' => $each['app_secret'],
                    'agentConnect_value' => $each['agent_uuid_list'],
                    'cert_name' => $appcertinfo['cert_name'],
                    'cert_password' => $appcertinfo['cert_password'],
                );
            }
        }
        return $info;
    }

    /**
     * 根据用户uuid获取组织名
     * @param string $useruuid 用户uuid
     * @return 用户名
     */
    private function getUserName($useruuid)
    {
        $des = xphp_get_lang('WEB_M365_NOT_SPECIFY_USER');
        if (!empty($useruuid)) {
            $sql = "select display_name, mail from m365_user where user_uuid = ?";
            $result = $this->dbSelect($sql, array($useruuid));
            if (!empty($result)) {
                $des = $result[0]['display_name'] . '(' . $result[0]['mail'] . ')';
            }
        }
        return $des;
    }

    /**
     * 根据组织uuid获取组织名
     * @param string $organizationuuid 组织uuid
     * @return 组织名
     */
    private function getOrganizationName($organizationuuid)
    {
        $des = '';
        $sql = "select organization_name,nickname from m365_organization where organization_uuid = ?";
        $result = $this->dbSelect($sql, array($organizationuuid));
        if (!empty($result)) {
            $des = $result[0]['organization_name'];
            if (!empty($result[0]['nickname'])) {
                $des = $result[0]['nickname'];
            }
        }
        return $des;
    }

    /**
     * 下载跳过数据
     * @param array $params 参数
     * @return string
     */
    public function downLoadPassData(array $params)
    {
        $nodeUuid =  $params['node_uuid'];
        $readFileName =  $params['read_file_name'];
        $storageUuid =  $params['storage_uuid'];
        $filesize = $params['pass_item_file_size'];
        $filename = 'passItemList';
        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        Header('Content-Disposition: attachment; filename=' . $filename . '.txt');
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                'read_file_name' => $readFileName,
                'storage_uuid' => $storageUuid,
                'offset' => $i,
                'length' => $readLen,
            );
            echo $this->service()->downLoadPassData($nodeUuid, $msg);
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
    }
}
