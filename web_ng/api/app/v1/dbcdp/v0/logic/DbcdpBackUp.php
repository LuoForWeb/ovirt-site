<?php

namespace app\v1\dbcdp\v0\logic;

use app\v1\common\logic\Backup as Backup;
use app\v1\opcode\PfOpcode;
use app\v1\common\logic\Base;
use app\v1\common\logic\Unification;

/**
 * note          数据库实时 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpBackUp extends Base
{
    /**
     * 创建备份任务
     * @param array $params 创建备份任务入参
     * @return array
     */
    public function createBackupJob(array $params = [])
    {
        // 检查授权是否到期
        $this->licenseCheck();
        $jobName = $params['job_name'];  //任务名
        $select_copy_users = $params['select_copy_users']; // 非系统用户
        $apptableSpaceDir = $params['app_tablespace_dir']; // 数据表空间存储目录
        $exp_threadNum = $params['exp_max_process_num'];  //传输导出线程个数
        $imp_threadNum = $params['imp_max_process_num'];  //传输导入线程个数
        $transportStrategy = $params['transport_strategy'];  //传输策略
        $speedStrategy = $params['speed_limit_strategy_list'];     //限速策略
        $autoTakeoverFlag = v1_parse_bool_to_flag($params['auto_takeover_flag']);  //是否启用自动接管
        //同步对象配置
        $syncObject = array(
            'source_app_uuid' => $params['sync_object']['source_app_uuid'],
            'target_app_uuid' => $params['sync_object']['target_app_uuid'],
            'source_agent_uuid' => $params['sync_object']['source_agent_uuid'],
            'target_agent_uuid' => $params['sync_object']['target_agent_uuid'],
            'sync_direction' => $params['sync_object']['sync_direction'],
            'sync_level' => $params['sync_object']['sync_level'], //?
            'source_cluster_flag' => v1_parse_bool_to_flag($params['sync_object']['source_cluster_flag']),
            'target_cluster_flag' => v1_parse_bool_to_flag($params['sync_object']['target_cluster_flag']),
        );
        //接管对象配置
        $takeoverObject = array(
            'switch_ip_flag' => v1_parse_bool_to_flag($params['takeover_object']['switch_ip_flag']),
            'failback_target_ip' => $params['takeover_object']['failback_target_ip'],
            'app_consecutive_failure_num' => $params['takeover_object']['app_consecutive_failure_num'],
            'agent_heartbeat_failure_time' => $params['takeover_object']['agent_heartbeat_failure_time'],
            'app_fault_detection_interval' => $params['takeover_object']['app_fault_detection_interval'],
            'takeover_business_ip_map' => $params['takeover_object']['takeover_business_ip_map'],
        );
        $cacheConfig = $params['cache_config'];  //缓存配置
        //$moduleType（模块类型）和$taskType（任务类型）的值根据当前模块从配置文件中获取
        $moduleTypeInfo = xphp_get_config('module', 'MODULE_TYPE');
        $moduleType = $moduleTypeInfo['DB_CDP'];  //模块类型

        $taskTypeInfo = xphp_get_config('task', 'TASKTYPE');
        $taskType = $taskTypeInfo['CDP_DB_BACKUP'];  //任务类型

        $opName = 'BD_TASK_OP_BACKUP_CREATE';  //控制码
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $nodeuuid = $params['node_uuid'];  //管理节点
        $node_pool_uuid = $params['node_pool_uuid'];  //资源池
        $ignore_resource_limiting_flag = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
        $extend_advanced_config = $params['extend_advanced_config'];
        $timeStrategyList = array();  //时间策略，本模块未涉及
        $reserverStrategy = array();  //保留策略，本模块未涉及
        $storageStrategy = array();  //存储策略
        $strategyGroupuuid = ''   ;
        $uniFication = new Unification();
        $syncTaskMessage = array(
            'task_name' => $jobName,
            'module_type' => $moduleType,
            'task_type' => $taskType,
            'time_strategy_list' => $timeStrategyList,
            'reserved_strategy' => $reserverStrategy,
            'storage_strategy' => $storageStrategy,
            'auto_takeover_flag' => $autoTakeoverFlag,
            'exp_max_process_num' => $exp_threadNum,
            'imp_max_process_num' => $imp_threadNum,
            'sync_object' => $syncObject,
            'takeover_object' => $takeoverObject,
            'cache_config' => $cacheConfig,
            'speed_limit_strategy_list' => Backup::instance()->groupTaskSpeedGlobalList($speedStrategy),
            'app_tablespace_dir' => $apptableSpaceDir,
            'node_uuid' => $nodeuuid,
            'node_pool_uuid' => $node_pool_uuid,
            'extend_advanced_config' => $extend_advanced_config,
            'select_copy_users' => $select_copy_users,
            'ignore_resource_limiting_flag' => $ignore_resource_limiting_flag
        );

        $netWork = !empty($transportStrategy['network_uuid']) ? $transportStrategy['network_uuid'] : '';
        //组合传输策略
        $syncTaskMessage['transport_strategy'] = $this->transportStrategyMessage(
            $transportStrategy['encrypt_flag'],
            $transportStrategy['compress_flag'],
            $transportStrategy['block_size'],
            $netWork,
            $strategyGroupuuid,
            $transportStrategy['transport_compress_method'],
            $transportStrategy['transport_encrypt_method']
        );
        // 需要和后台通信的 需要再次转发到 service 服务层去处理
        $mbResult = $this->service()->createBackupJob($nodeuuid, $opName, json_encode($syncTaskMessage));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        // 选择是否接受服务通信结果 然后返回到控制器
        //返回结果到UI
        $message = xphp_get_lang('WEB_DB_CDP_CREATE_SYNC_TASK');
        if ($result) {
            $resultMsg = $this->muOpResult($result,$message, xphp_get_lang('UI_DB_CDP_BACKUP_CREATE_REPLICATION_TASK_SUCCESS'));
        } else {
            $resultMsg = $this->muOpResult($result, $message, xphp_get_lang('UI_DB_CDP_BACKUP_CREATE_REPLICATION_TASK_FAIL'), '', $mbResult['errorCode']);
        }
        return $resultMsg;
    }

    /**
     * 获取客户端网卡信息(回切)
     * @param array $params
     * @return array
     */
    public function getHostNetworkInfo(array $params = [])
    {
        $agentuuid = $params['agent_uuid'];
        $isClustersql = 'select cluster_flag from bd_agent_app where agent_uuid = ? ';
        $isClustersqlData = $this->dbSelect($isClustersql, array($agentuuid));
        if ($isClustersqlData[0]['cluster_flag'] == xphp_get_config('app')['FLAG']['SET']) {
            $sql = 'select cdda.takeover_ip_detail from bd_agent_app baa inner join 
                    cdp_db_dr_app cdda on baa.app_uuid = cdda.app_uuid 
                    where baa.agent_uuid = ?';
            $data = $this->dbSelect($sql, array($agentuuid));
            $takeover_ip_detail = json_decode($data[0]['takeover_ip_detail'],true);
            $nic_list = $takeover_ip_detail['nic_list'];
            foreach ($nic_list as &$nic) {
                $nic['is_cluster'] = true;
            }
            unset($nic); // 解除引用
        } else {
            $sql = "select detail from bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($agentuuid));
            $detail = json_decode($data[0]['detail'],true);
            $nic_list = $detail['nic_list'];
            foreach ($nic_list as &$nic) {
                $nic['is_cluster'] = false;
            }
            unset($nic); // 解除引用
        }
        return $nic_list;
    }

    /**
     * 扫描ip(源端为集群时)
     * @return $return
     */
    public function scanIp(array $params = [])
    {
        $nodeuuid = $params['refresh_agent_set'][0]['node_uuid'];
        $opName = 'DB_CDP_TASK_SCANNING_SERVICE_INFO';  //控制码
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $scanIpMessage = $params;

        // 需要和后台通信的 需要再次转发到 service 服务层去处理
        $mbResult = $this->service()->scanIp($nodeuuid, $opName, json_encode($scanIpMessage));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        // 选择是否接受服务通信结果 然后返回到控制器
        //返回结果到UI
        if ($result) {
            $resultMsg = $this->muOpResult($result, $operate, $msg);
        } else {
            $resultMsg = $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        return $resultMsg;
    }

    /**
     * 获取网卡信息（源端为集群时,同步或手动接管）
     * @return $return
     */
    public function getClusterNetworkInfo(array $params = [])
    {
        $sql = 'select app_uuid from bd_agent_app where agent_uuid = ? ';
        $data = $this->dbSelect($sql, array($params['agent_uuid']));
        if (!empty($data)) {
            $appSql = 'select ip_detail from cdp_db_dr_app where app_uuid = ?';
            $appData = $this->dbSelect($appSql, array($data[0]['app_uuid']));
        }
        $ip_detail = json_decode($appData[0]['ip_detail'],true);
        $nic_list = $ip_detail['nic_list'];
        foreach ($nic_list as &$nic) {
            $nic['is_cluster'] = true;
        }
        unset($nic); // 解除引用
        return $nic_list;
    }

    /**
     * 修改同步任务
     * @param array $params 修改任务对应参数
     * @return array
     */
    public function editBackupJob($params = [])
    {
        // 检查授权是否到期
        $this->licenseCheck();
        $jobName = htmlspecialchars_decode($params['job_name']);  //任务名
        $apptableSpaceDir = $params['app_tablespace_dir']; // 数据表空间存储目录
        $jobUuid = $params['job_uuid'];  //修改任务uuid
        $exp_threadNum = $params['exp_max_process_num'];  //传输导出线程个数
        $imp_threadNum = $params['imp_max_process_num'];  //传输导入线程个数
        $transportStrategy = $params['transport_strategy'];  //传输策略
        $speedStrategy = $params['speed_limit_strategy_list'];     //限速策略
        $autoTakeoverFlag = $params['auto_takeover_flag'];  //是否启用自动接管
        $syncObject = $params['sync_object'];  //同步对象配置
        $nodeuuid = $params['node_uuid'];
        $extend_advanced_config = $params['extend_advanced_config'];
        $node_pool_uuid = $params['node_pool_uuid'];  //资源池
        $ignore_resource_limiting_flag = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
        //接管对象配置
        $takeoverObject = array(
            'switch_ip_flag' => v1_parse_bool_to_flag($params['takeover_object']['switch_ip_flag']),
            'failback_target_ip' => $params['takeover_object']['failback_target_ip'],
            'app_consecutive_failure_num' => $params['takeover_object']['app_consecutive_failure_num'],
            'agent_heartbeat_failure_time' => intval($params['takeover_object']['agent_heartbeat_failure_time']),
            'app_fault_detection_interval' => $params['takeover_object']['app_fault_detection_interval'],
            'takeover_business_ip_map' => $params['takeover_object']['takeover_business_ip_map'],
        );
        $cacheConfig = $params['cache_config'];  //缓存配置

        //$moduleType（模块类型）和$taskType（任务类型）的值根据当前模块从配置文件中获取
        $moduleTypeInfo = xphp_get_config('module', 'MODULE_TYPE');
        $moduleType = $moduleTypeInfo['DB_CDP'];  //模块类型

        $taskTypeInfo = xphp_get_config('task', 'TASKTYPE');
        $taskType = $taskTypeInfo['CDP_DB_BACKUP'];  //任务类型

        $opName = xphp_get_lang('WEB_DB_CDP_MODIFY_SYNC_TASK_CODE');  //控制码
        $nodeUuid = $params['node_uuid'];  //管理节点uuid

        $timeStrategyList = array();  //时间策略，本模块未涉及
        $reserverStrategy = array();  //保留策略，本模块未涉及
        $storageStrategy = array();  //存储策略

        $opName = 'BD_TASK_OP_BACKUP_MODIFY';  //控制码
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);

        $strategyGroupuuid = ''   ;
        $uniFication = new Unification();
        //同步对象配置
        $syncObject = array(
            'source_app_uuid' => $params['sync_object']['source_app_uuid'],
            'target_app_uuid' => $params['sync_object']['target_app_uuid'],
            'source_agent_uuid' => $params['sync_object']['source_agent_uuid'],
            'target_agent_uuid' => $params['sync_object']['target_agent_uuid'],
            'sync_direction' => $params['sync_object']['sync_direction'],
            'sync_level' => $params['sync_object']['sync_level'],
            'source_cluster_flag' => v1_parse_bool_to_flag($params['sync_object']['source_cluster_flag']),
            'target_cluster_flag' => v1_parse_bool_to_flag($params['sync_object']['target_cluster_flag']),
        );
        $syncTaskMessage = array(
            'task_uuid' => $jobUuid,
            'task_name' => $jobName,
            'task_type' => $taskType,
            'module_type' => $moduleType,
            'time_strategy_list' => $timeStrategyList,
            'reserved_strategy' => $reserverStrategy,
            'storage_strategy' => $storageStrategy,
            'auto_takeover_flag' => v1_parse_bool_to_flag($autoTakeoverFlag),
            'exp_max_process_num' => $exp_threadNum,
            'imp_max_process_num' => $imp_threadNum,
            'sync_object' => $syncObject,
            'takeover_object' => $takeoverObject,
            'cache_config' => $cacheConfig,
            'speed_limit_strategy_list' => Backup::instance()->groupTaskSpeedGlobalList($speedStrategy),
            'app_tablespace_dir' => $apptableSpaceDir,
            'node_uuid' => $nodeuuid,
            'extend_advanced_config' => $extend_advanced_config,
            'node_pool_uuid' => $node_pool_uuid,  //资源池
            'ignore_resource_limiting_flag' => $ignore_resource_limiting_flag
        );

        $netWork = !empty($transportStrategy['network_uuid']) ? $transportStrategy['network_uuid'] : '';
        //组合传输策略
        $syncTaskMessage['transport_strategy'] = $this->transportStrategyMessage(
            $transportStrategy['encrypt_flag'],
            $transportStrategy['compress_flag'],
            $transportStrategy['block_size'],
            $netWork,
            $strategyGroupuuid,
            $transportStrategy['transport_compress_method'],
            $transportStrategy['transport_encrypt_method']
        );
        // 需要和后台通信的 需要再次转发到 service 服务层去处理
        $mbResult = $this->service()->createBackupJob($nodeuuid, $opName, json_encode($syncTaskMessage));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        // 选择是否接受服务通信结果 然后返回到控制器
        //返回结果到UI
        $message = xphp_get_lang('WEB_DB_CDP_MODIFY_SYNC_TASK');
        if ($result) {
            $resultMsg = $this->muOpResult($result, $message, $msg);
        } else {
            $resultMsg = $this->muOpResult($result, $message, $msg, '', $mbResult['errorCode']);
        }
        return $resultMsg;
    }

    /**
     * 修改同步任务
     * @param array $params 修改任务对应参数
     * @return array
     */
    public function editBackupJobConf($params = [])
    {
        $jobUuid = $params['jobs_uuid'];  //修改任务uuid
        // 同步源agent 同步应用  cdp_db_dr_task.source_agent_uuid  cdp_db_dr_task.source_app_uuid cdp_db_dr_task.source_cluster_flag
        // 备机 cdp_db_dr_task.target_agent_uuid  cdp_db_dr_task.target_app_uuid cdp_db_dr_task.target_cluster_flag
        // 是否自动接管 服务IP漂移 配置接管网络 回切通讯IP 心跳失效时间 连续故障次数 故障监测间隔 加密传输 压缩传输 传输线程 传输限速
        // 数据库映射详情 数据同步节点

        $sql = 'select cddt.source_agent_uuid, cddt.target_agent_uuid,
                cddt.source_app_uuid,cddt.target_app_uuid,
                cddt.source_cluster_flag, cddt.target_cluster_flag,cddt.auto_takeover_flag, 
                cddt.app_tablespace_dir, cddt.extend_advanced_config, cddtti.switch_ip_flag,
                cddt.imp_max_process_num, cddt.exp_max_process_num,
                baa.cluster_uuid as source_cluster_uuid, baa_target.cluster_uuid as target_cluster_uuid
                from cdp_db_dr_task cddt 
                inner join bd_agent_app baa ON cddt.source_agent_uuid = baa.agent_uuid 
                inner join bd_agent_app baa_target ON cddt.target_agent_uuid = baa_target.agent_uuid
                inner join cdp_db_dr_task_takeover_info cddtti ON cddt.task_uuid = cddtti.task_uuid
                where cddt.task_uuid = ?';
        $data = $this->dbSelect($sql, array($jobUuid));

        $sqlTakeover = 'select agent_heartbeat_failure_time, app_consecutive_failure_num, 
                        app_fault_detection_interval,failback_target_ip
                        from cdp_db_dr_task_takeover_info where task_uuid = ?';
        $dataTakeoverData = $this->dbSelect($sqlTakeover, array($jobUuid));

        $sqlIpDetail = 'select takeover_business_ip_map from cdp_db_dr_task_takeover_info
                        where task_uuid = ?';
        $sqlIpDetailData = $this->dbSelect($sqlIpDetail, array($jobUuid));

        $sqlFile = 'select source_file_cache_alloc_space,source_file_cache_storage_path,file_cache_alloc_space,file_cache_storage_path 
                    from cdp_db_dr_task_cache_info where task_uuid = ?';
        $sqlFileData = $this->dbSelect($sqlFile, array($jobUuid));

        $sqlDelayTime = 'select delay_load_time from cdp_db_dr_task where task_uuid = ?';
        $sqlDelayTimeData = $this->dbSelect($sqlDelayTime, array($jobUuid));

        $sqlStrategy = 'select bt.thread_num,bt.task_name,bt.task_status,bts.encrypt_flag,bts.compress_flag,
                        bts.encrypt_method,bts.compress_method,bt.node_pool_uuid,bt.ignore_resource_limiting_flag,bt.node_uuid,bts.network_uuid
                        from bd_task bt inner join bd_transport_strategy bts 
                        on bt.task_uuid = bts.task_uuid where bt.task_uuid = ?';
        $sqlStrategyData = $this->dbSelect($sqlStrategy, array($jobUuid));

        $sqlLimitTimeStrategy = 'select bgsls.strategy_name,bgsls.strategy_group_uuid,
                                 bgsls.start_time,bgsls.end_time,bgsls.days,bgsls.days 
                                 from bd_task_speed_limit_strategy btsls 
                                 inner join bd_global_speed_limit_strategy bgsls 
                                 on btsls.strategy_uuid = bgsls.strategy_uuid where btsls.task_uuid = ?';
        $sqlLimitTimeStrategyData = $this->dbSelect($sqlLimitTimeStrategy, array($jobUuid));

        $syncEditTaskMessage = array(
            'sync_object' => array(
                'source_agent_uuid' => $data[0]['source_agent_uuid'],
                'source_app_uuid' => $data[0]['source_app_uuid'],
                'target_agent_uuid' => $data[0]['target_agent_uuid'],
                'target_app_uuid' => $data[0]['target_app_uuid'],
                'source_cluster_flag' => v1_parse_flag_to_bool($data[0]['source_cluster_flag']),
                'target_cluster_flag' => v1_parse_flag_to_bool($data[0]['target_cluster_flag']),
                'source_cluster_uuid' => $data[0]['source_cluster_uuid'],
                'target_cluster_uuid' => $data[0]['target_cluster_uuid'],
                'auto_takeover_flag' => v1_parse_flag_to_bool($data[0]['auto_takeover_flag']),  //自动接管
                'switch_ip_flag' => v1_parse_flag_to_bool($data[0]['switch_ip_flag']),  //服务IP漂移
                'failback_target_ip' => $dataTakeoverData[0]['failback_target_ip'],
                'agent_heartbeat_failure_time' => $dataTakeoverData[0]['agent_heartbeat_failure_time'],  //心跳失效时间
                'app_consecutive_failure_num' => $dataTakeoverData[0]['app_consecutive_failure_num'],   //连续故障次数
                'app_fault_detection_interval' => $dataTakeoverData[0]['app_fault_detection_interval'], //故障监测间隔
                'takeover_business_ip_map' => json_decode($sqlIpDetailData[0]['takeover_business_ip_map']),  // 网卡信息
                'source_file_cache_alloc_space' => $sqlFileData[0]['source_file_cache_alloc_space'],      // 源机文件缓存大小
                'source_file_cache_storage_path' => $sqlFileData[0]['source_file_cache_storage_path'],    // 源机文件缓存路径
                'file_cache_alloc_space' => $sqlFileData[0]['file_cache_alloc_space'],      // 备机文件缓存大小
                'file_cache_storage_path' => $sqlFileData[0]['file_cache_storage_path'],    // 备机文件缓存路径
                'delay_load_time' => $sqlDelayTimeData[0]['delay_load_time'],  //延迟时间大小
                'exp_max_process_num' => $data[0]['exp_max_process_num'],  //导出线程个数
                'imp_max_process_num' => $data[0]['imp_max_process_num'],  //导入线程个数
                'encrypt_flag' => v1_parse_flag_to_bool($sqlStrategyData[0]['encrypt_flag']),  //加密
                'compress_flag' => v1_parse_flag_to_bool($sqlStrategyData[0]['compress_flag']),  //压缩
                'transport_compress_method' => $sqlStrategyData[0]['compress_method'],
                'transport_encrypt_method' => $sqlStrategyData[0]['encrypt_method'],
                'task_name' => $sqlStrategyData[0]['task_name'],  //任务名
                'task_status' => $sqlStrategyData[0]['task_status'],  //任务状态
                'speedInfo' => $this->getSpeedStrategy($jobUuid),
                'app_tablespace_dir' => $data[0]['app_tablespace_dir'],
                'extend_advanced_config' => json_decode($data[0]['extend_advanced_config']),
                'node_pool_uuid' => $sqlStrategyData[0]['node_pool_uuid'],
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($sqlStrategyData[0]['ignore_resource_limiting_flag']),
                'node_uuid' => $sqlStrategyData[0]['node_uuid'],
                'network_uuid' => $sqlStrategyData[0]['network_uuid']
            )
        );
        return $syncEditTaskMessage;
    }

    /**
     * 获取任务全局限速策略列表信息
     * @param string $taskuuid 任务uuid
     * @return array $info 全局限速策略列表信息
     */
    public function getSpeedGlobalStrategyInfo($taskuuid)
    {
        $sql = "select strategy_uuid,task_priority from bd_task_speed_limit_strategy where task_uuid = ? limit 1";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = [
            'level' => 1,
            'type' => 2,
            'speedInfo' => [],
        ];
        if (!empty($data)) {
            // 查询出全局限速策略
            $sql = "select * from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
            $strategy = $this->dbSelect($sql, array($data[0]['strategy_uuid']));
            if (!empty($strategy)) {
                $info['level'] = $data[0]['task_priority']; // 任务级别
                $info['strategy_type'] = $strategy[0]['strategy_type']; // 限速类型
                $info['type'] = $strategy[0]['is_global'] ? 1 : 2; // 任务类型 选择策略还是自定义策略
                if ($info['type'] == 1) {
                    // 全局策略
                    $info['global_speed_limit'] = $strategy[0]['strategy_uuid'];
                } else {
                    $info['speedInfo'] = json_decode($strategy[0]['extra_info'], true);
                }
            }
        }
        return $info;
    }

    /**
     * 组合传输策略参数
     * @param unknown $encryptFlag       加密标志位
     * @param unknown $compressFlag      压缩标志
     * @param unknown $blockSize         传输字节大小，单位字节
     * @param unknown $networkUuid       传输网络
     * @param unknown $strategyGroupuuid 策略组uuid
     * @return unknown[]
     */
    private function transportStrategyMessage($encryptFlag, $compressFlag, $blockSize, $networkUuid, $strategyGroupuuid, $transportCompressMethod, $transportEncryptMethod)
    {
        $message = array(
            'transport_encrypt_flag' => v1_parse_bool_to_flag($encryptFlag),
            'transport_compress_flag' => v1_parse_bool_to_flag($compressFlag),
            'transport_block_size' => $blockSize,
            'transport_network_uuid' => $networkUuid,
            'strategy_group_uuid' => $strategyGroupuuid,
            'transport_compress_method' => $transportCompressMethod,
            'transport_encrypt_method' => $transportEncryptMethod,
        );
        return $message;
    }

    /**
     * 客户端是否在任务中
     * @param $params
     * @return void
     */
    public function taskExist($params = [])
    {
        // 如果选择的是集群下的机器
        $sql = "select agent_uuid
                from bd_agent_app
                where cluster_uuid = (select cluster_uuid from bd_agent_app where agent_uuid = ?)
                and cluster_uuid not in ('')";
        $clutserData = $this->dbSelect($sql, array($params['agent_uuid']));
        if (count($clutserData) != 0) {
            // 获取同一集群下的所有机器uuid
            foreach ($clutserData as $d) {
                if ($params['task_type'] == xphp_get_config('task')['TASKTYPE']['CDP_DB_BACKUP']) {
                    // 同步
                    $sql = 'SELECT IF(
                            EXISTS(
                            SELECT 1
                            FROM cdp_db_dr_task CDDT
                            JOIN bd_task BT ON BT.task_uuid = CDDT.task_uuid
                            WHERE (CDDT.source_agent_uuid = ? AND BT.task_type = 46)
                            OR (CDDT.target_agent_uuid = ? AND BT.task_type = 46)
                            OR (CDDT.target_agent_uuid = ? AND BT.task_type = 47)
                            )
                            OR EXISTS(
                            SELECT 1
                            FROM cdp_db_dr_task_takeover_failback_info CDDTTFI
                            WHERE CDDTTFI.failback_target_agent_uuid = ?
                            ),
                            0, 1
                            ) AS result';
                    $sqlParams = array($d['agent_uuid'],$d['agent_uuid'],
                        $d['agent_uuid'],$d['agent_uuid']);
                } elseif ($params['task_type'] == xphp_get_config('task')['TASKTYPE']['CDP_DB_RECOVERY']) {
                    // 恢复
                    $sql = 'SELECT IF(
                            EXISTS( SELECT 1 FROM cdp_db_dr_task CDDT
                            JOIN bd_task BT ON BT.task_uuid = CDDT.task_uuid
                            WHERE (CDDT.source_agent_uuid = ? AND BT.task_type = 46)
                            OR (CDDT.target_agent_uuid = ? AND BT.task_type = 46)
                            OR (CDDT.target_agent_uuid = ? AND BT.task_type = 47)
                            ),
                            0, 1
                            ) AS result';
                    $sqlParams = array($d['agent_uuid'],$d['agent_uuid'],$d['agent_uuid']);
                }

                $data = $this->dbSelect($sql, $sqlParams);
                if ($data[0]['result'] == 0) {
                    $taskList[] = array(
                        'in_task' => $data[0]['result'] == 0 ? v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['SET'])
                            : v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['UNSET']),
                    );
                    return $taskList;
                }
            };
        }
        if ($params['task_type'] == xphp_get_config('task')['TASKTYPE']['CDP_DB_BACKUP']) {
            // 同步
            $sql = '
	            SELECT IF(
                EXISTS(
                SELECT 1
                FROM cdp_db_dr_task CDDT
                JOIN bd_task BT ON BT.task_uuid = CDDT.task_uuid
                WHERE (CDDT.source_agent_uuid = ? AND BT.task_type = 46)
                OR (CDDT.target_agent_uuid = ? AND BT.task_type = 46)
                OR (CDDT.target_agent_uuid = ? AND BT.task_type = 47)
                )
                OR EXISTS(
                SELECT 1
                FROM cdp_db_dr_task_takeover_failback_info CDDTTFI
                WHERE CDDTTFI.failback_target_agent_uuid = ?
                ),
                0, 1
                ) AS result';
            $sqlParams = array($params['agent_uuid'],$params['agent_uuid'],
                $params['agent_uuid'],$params['agent_uuid']);
        } elseif ($params['task_type'] == xphp_get_config('task')['TASKTYPE']['CDP_DB_RECOVERY']) {
            // 恢复
            $sql = '
	            SELECT IF(
                EXISTS( SELECT 1 FROM cdp_db_dr_task CDDT
                JOIN bd_task BT ON BT.task_uuid = CDDT.task_uuid
                WHERE (CDDT.source_agent_uuid = ? AND BT.task_type = 46)
                OR (CDDT.target_agent_uuid = ? AND BT.task_type = 46)
                OR (CDDT.target_agent_uuid = ? AND BT.task_type = 47)
                ),
                0, 1
                ) AS result';
            $sqlParams = array($params['agent_uuid'],$params['agent_uuid'],$params['agent_uuid']);
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $taskList = array();
        $taskList[] = array(
            'in_task' => $data[0]['result'] == 0 ? v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['SET'])
                : v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['UNSET']),
        );
        return $taskList;
    }

    /**
     * 加载数据库应用/实例的数据库信息
     * @param string $appUuid 客户端uuid
     * @return array
     */
    public function loadInstanceDatabase($params = []): array
    {
        $task_uuid = $params['task_uuid'];
        $appUuid = $params['refresh_agent_set'][0]['app_uuid_set'][0]['app_uuid'];
        $select_copy_users_flag = $params['select_copy_users_flag'];
        $opName = 'DB_CDP_TASK_SCANNING_APP_INFO';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        $sql = "SELECT app_name AS instance_name, app_auth_type AS auth_type, app_username AS username,
                    app_password AS password, app_type AS db_type, agent_uuid, cluster_flag, cluster_uuid, cluster_name,
                    app_detail
                FROM bd_agent_app WHERE app_uuid = ?";
        $appData = $this->dbSelect($sql, array($appUuid));
        if (!is_array($appData) || !$appData) {
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_INSTANCE_NOT_EXISTS'), false, 400);
        }
        $appData = $appData[0];
        $serviceRet = $this->service()->loadInstanceDatabaseService($params);
        if (!$serviceRet['result']) {
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_DB_CDP_FAILED_SCAN_APP_INFO'), 'warning', $serviceRet['errorCode']);
        }

        if ($appData['db_type'] == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
            $appDetail = json_decode($serviceRet['msg']['app_detail'], true);
            $dbList = $appDetail['database_list'];
        }

        $userSql = "select select_copy_users from cdp_db_dr_task where task_uuid = ?";
        $userData = $this->dbSelect($userSql,array($task_uuid));
        $select_copy_users = array();
        if(!empty($userData) && $select_copy_users_flag){
            $select_copy_users = json_decode($userData[0]['select_copy_users']);
        }else{
            $select_copy_users = $dbList;
        }

        $sql = "select detail from cdp_db_dr_app where app_uuid = ?";
        $data = $this->dbSelect($sql,array($appUuid));
        $agent_install_dir = json_decode($data[0]['detail'],true)['agent_install_dir'];
        $ip_scope_info = json_decode($data[0]['detail'],true)['ip_scope_info'];
        $rows = [];
        foreach ($dbList as $db) {
            $userListValues = [];
            foreach ($db['user_list'] as $user) {
                $userListValues[] = [
                    'user_name' => $user['name'],
                    'instance_name' => $appData['instance_name'],
                    'is_cluster' => v1_parse_flag_to_bool($appData['cluster_flag']),
                    'cluster_uuid' => $appData['cluster_uuid'],
                    'cluster_name' => $appData['cluster_name'],
                    'agent_uuid' => $appData['agent_uuid'],
                    'app_uuid' => $appUuid,
                    'db_type' => (int) $appData['db_type'],
                ];
            }

            $rows[] = [
                'con_id' => $db['con_id'],
                'pdb_name' => $db['pdb_name'],
                'is_cdb' => v1_parse_flag_to_bool($appDetail['is_cdb']),
                'user_list' => $userListValues, // 包含所有user_list值的数组
                'open_mode' => $db['open_mode']
            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
            'select_copy_users' => $select_copy_users,
            'agent_install_dir' => $agent_install_dir,
            'ip_scope_info' => $ip_scope_info
        ]);
    }

    /**
     * 获取剩余授权是否足够
     * @param string module_type 模块类型
     * @param string task_type 任务类型
     * @return boolean
     */
    public function getAuthRemainEnough($params = [])
    {
        $moduleType = $params['module_type'];
        $taskType = $params['task_type'];

        // 查询数据库复制的最大授权数量
        $maxDbReplicationSql = "SELECT db_replication_max_num FROM bd_license";
        $maxDbReplicationData = $this->dbSelect($maxDbReplicationSql);

        // 查询当前已使用的数据库复制数量（通过子查询计算）
        $usedDbReplicationSql = "SELECT COALESCE(SUM(count), 0) AS count FROM (
                                    SELECT BT.agent_uuid, 
                                    CASE 
                                    WHEN BAA.cluster_uuid = '' THEN 1 
                                    WHEN BAA.cluster_flag = 1 THEN (SELECT COUNT(*) FROM bd_agent_app WHERE cluster_uuid = BAA.cluster_uuid) 
                                    ELSE 0 
                                    END AS count 
                                    FROM bd_task BT 
                                    INNER JOIN bd_agent_app BAA ON BT.agent_uuid = BAA.agent_uuid 
                                    WHERE BT.module_type = ? AND BT.task_type = ?
                                    ) AS subquery;";
        $usedDbReplicationCount = $this->dbSelect($usedDbReplicationSql, array($moduleType, $taskType));
        $result = $maxDbReplicationData[0]['db_replication_max_num'] > $usedDbReplicationCount[0]['count'];
        return $result;
    }
}
