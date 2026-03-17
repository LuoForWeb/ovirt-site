<?php

namespace app\v1\dbcdp\v0\logic;

use app\v1\common\logic\Backup as Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\Unification;
use app\v1\opcode\PfOpcode;

use function Couchbase\defaultDecoder;

/**
 * note          数据库实时 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpRecover extends Base
{
    /**
     * @param array $params 创建恢复任务参数
     * @return array
     */
    public function createRecoverJob(array $params = [])
    {
        $jobName = htmlspecialchars_decode($params['job_name']);  //任务名
        $timepoint_uuid = $params['timepoint_uuid'];
        $apptableSpaceDir = $params['app_tablespace_dir']; // 数据表空间存储目录
        $exp_threadNum = $params['exp_max_process_num'];  //传输导出线程个数
        $imp_threadNum = $params['imp_max_process_num'];  //传输导入线程个数
        $sourceAppUuid = $params['recovery_object']['source_app_uuid'];
        $netModel = $params['net_model'];   //网络模式
        $speedStrategy = $params['speed_limit_strategy_list'];     //限速策略
        $recoveryDatetime = $params['recovery_target_datetime'];  //恢复时间点
        $recoveryObject = $params['recovery_object'];   //恢复对象配置
        $cachConfig = $params['cache_config'];   //缓存配置
        $transportStrategy = $params['transport_strategy']; //传输策略
        $recovery_timepoint_type = $params['recovery_timepoint_type'];
        $recovery_target_scn = $params['recovery_target_scn'];
        $nodeuuid = $params['node_uuid'];  //管理节点
        $extend_advanced_config = $params['extend_advanced_config'];
        $select_copy_users = $params['select_copy_users']; // 非系统用户

        $moduleTypeInfo = xphp_get_config('module', 'MODULE_TYPE');
        $moduleType = $moduleTypeInfo['DB_CDP'];  //模块类型

        $taskTypeInfo = xphp_get_config('task', 'TASKTYPE');
        $taskType = $taskTypeInfo['CDP_DB_RECOVERY'];  //任务类型
        $timeStrategyList = array();  //时间策略 本模块不涉及
        $reserverStrategy = array();  //保留策略，本模块未涉及
        $storageStrategy = array();  //存储策略
        $strategyGroupuuid = '';  //向后台发消息时占位
        $uniFication = new Unification();

        $recoveryObject = array(
            'source_app_uuid' => $recoveryObject['source_app_uuid'],
            'target_app_uuid' => $recoveryObject['target_app_uuid'],
            'recovery_level' => $recoveryObject['recovery_level'],
            'recovery_type' => $recoveryObject['recovery_type'],
            'source_cluster_flag' => v1_parse_bool_to_flag($recoveryObject['source_cluster_flag']),
            'target_cluster_flag' => v1_parse_bool_to_flag($recoveryObject['target_cluster_flag']),
            'source_agent_uuid' => $recoveryObject['source_agent_uuid'],
            'target_agent_uuid' => $recoveryObject['target_agent_uuid'],
        );
        $recoverTaskMessage = array(
            'task_name' => $jobName,
            'timepoint_uuid' => $timepoint_uuid,
            'module_type' => $moduleType,
            'task_type' => $taskType,
            'speed_limit_strategy_list' => Backup::instance()->groupTaskSpeedGlobalList($speedStrategy),
            'recovery_target_datetime' => $recoveryDatetime,
            'recovery_object' => $recoveryObject,
            'exp_max_process_num' => $exp_threadNum,
            'imp_max_process_num' => $imp_threadNum,
            'cache_config' => $cachConfig,
            'app_tablespace_dir' => $apptableSpaceDir,
            'recovery_timepoint_type' => $recovery_timepoint_type,
            'recovery_target_scn' => $recovery_target_scn,
            'node_uuid' => $nodeuuid,
            'extend_advanced_config' => $extend_advanced_config,
            'select_copy_users' => $select_copy_users
        );
        $netWork = !(empty($transportStrategy['network_uuid'])) ? $transportStrategy['network_uuid'] : '';
        $recoverTaskMessage['transport_strategy'] = $this->transportStrategyMessage(
            $transportStrategy['transport_encrypt_flag'],
            $transportStrategy['transport_compress_flag'],
            $transportStrategy['transport_block_size'],
            $netWork,
            $strategyGroupuuid,
            $transportStrategy['transport_compress_method'],
            $transportStrategy['transport_encrypt_method']
        );
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';  //控制码
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $mbResult = $this->service()->createRecoverJob($nodeuuid, $opName, json_encode($recoverTaskMessage));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        $message = xphp_get_lang('WEB_DB_CDP_CREATE_RECOVER_TASK');
        if ($result) {
            $resultMsg = $this->muOpResult($result, $message, $msg);
        } else {
            $resultMsg = $this->muOpResult($result, $message, $msg, '', $mbResult['errorCode']);
        }

        return $resultMsg;
    }

    /**
     * 获取事件信息
     * @param array $params 容灾数据集id
     * @return array
     */
    public function getTransactionInfo(array $params = [])
    {
        $restoreDataUuid = $params['restore_data_uuid'];
        $syncUuid = $params['sync_uuid'];
        $eventTime = $params['event_time'];
        $sql = 'select task_uuid from cdp_db_dr_task where source_agent_uuid = ? and target_agent_uuid = ?';
        $sqlParams = array($syncUuid,$restoreDataUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $taskUuid = $data[0]['task_uuid'];
        $start = intval($params['offset']);
        $transaction_id = $params['transaction_id'];

        $sql = 'select id, task_uuid, transaction_id, transaction_time, transaction_level,        
                transaction_type, description_key, transaction_detail ,transaction_sql 
                from cdp_db_dr_backup_transaction
                where transaction_id = ? and transaction_type = ? and task_uuid = ?';
        $sqlParams = array($transaction_id,xphp_get_config('app')['FLAG']['UNSET'],$taskUuid);
        $sqlCount = "select count(id) as total from cdp_db_dr_backup_transaction 
                     where transaction_id = ? and transaction_type = ? and task_uuid = ?";
        $sqlCountParams = array($transaction_id,xphp_get_config('app')['FLAG']['UNSET'],$taskUuid);
        if (!empty($eventTime)) {
            $sql .= ' and transaction_time = ?';
            $sqlCount .= ' and transaction_time = ?';
            $sqlParams = array_merge($sqlParams, array($eventTime));
            $sqlCountParams = array_merge($sqlCountParams, array($eventTime));
        }
        $sql .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($params['offset'], $params['limit']));
        $data = $this->dbSelect($sql, $sqlParams);
        $sqlCountData = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = ['rows' => [],'total' => $sqlCountData[0]['total']];
        if (empty($data[0])) {
            return $records;
        }
        //组装返回信息
        foreach ($data as $d) {
            $descriptionKey = $d['description_key'];
            $eventLevel = $d['transaction_level'];
            $records['rows'][] = array(
                'id' => ++$start,
                'transaction_id' => $d['transaction_id'],
                'task_uuid' => $d['task_uuid'],
                'transaction_time' => $d['transaction_time'],
                'transaction_level' => $this->getEventLevelDesc($eventLevel),  //事件等级
                'transaction_detail' => $d['transaction_detail'],
                'transaction_detail_scn' => json_decode($d['transaction_detail'])[0]->scn,
                'transaction_sql' => json_decode($d['transaction_sql']),
            );
        }
        return $records;
    }

    /**
     * 获取事件详情
     * @param array $params restore_data_uuid：容灾数据集ID events_uuid：事件详情
     * @return array
     */
    public function getEventDetail($params = [])
    {
        $taskUuid = $params['job_uuid'];
        $eventTime = $params['event_time'];
        $restoreDataUuid = $params['restore_data_uuid'];
        $eventsUuid = $params['events_uuid'];
        $sql = 'select id, transaction_id, transaction_time, transaction_type, transaction_level, transaction_detail, transaction_sql 
                from cdp_db_dr_backup_transaction 
                where transaction_id = ? ';
        $sqlParams = array($eventsUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        if (empty($data[0])) {
            return false;
        }
        $records = array();
        foreach ($data as $d) {
            $records = array(
                'transaction_detail' => $d['transaction_detail'],
                'transaction_sql' => $d['transaction_sql']
            );
        }
        return $records;
    }

    /**
     * 获取可恢复时间范围
     * @param array $params 容灾数据集id
     * @return array
     */
    public function getRestoreTimeRange(array $params = [])
    {
        $restoreDataUuid = $params['restore_data_uuid'];
        $sql = 'select bbt.id, bbt.dst_start_timepoint, cddbi.delay_time, cddbi.last_redo_replay_time,
        cddbi.latest_recv_transaction_time,cddbi.last_redo_replay_scn,cddbi.latest_recv_transaction_scn
        from bd_backup_timepoint bbt 
        inner join cdp_db_dr_backup_info cddbi
        on bbt.timepoint_uuid = cddbi.timepoint_uuid 
        where cddbi.target_agent_uuid = ? order by bbt.id desc limit 1';
        $sqlParams = array($restoreDataUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $records = array();
        if (!empty($data)) {
            $records = array(
                'restore_start_timepoint' => $data[0]['dst_start_timepoint'],
                'delay_time' => $data[0]['delay_time'],
                'last_replay_time' => $data[0]['last_redo_replay_time'],
                'latest_recv_transaction_time' => $data[0]['latest_recv_transaction_time'],
                'last_redo_replay_scn' => $data[0]['last_redo_replay_scn'],
                'latest_recv_transaction_scn' => $data[0]['latest_recv_transaction_scn']
            );
        }
        return $records;
    }

    /**
     * 获取指定时间区间的数据流量信息
     * @param string $restoreDataUuid 容灾数据集id 时间范围标志
     * @param int    $timeUnit        时间范围
     * @return array
     */
    public function getAgentBkTimelineData(string $restoreDataUuid, int $timeUnit)
    {
        $sql = 'select bbt.id, cddbi.latest_recv_transaction_time from cdp_db_dr_backup_second_level_data_flow flow
                    inner join bd_backup_timepoint bbt on flow.task_uuid = bbt.task_uuid
                    inner join cdp_db_dr_backup_info cddbi
                    on cddbi.timepoint_uuid = bbt.timepoint_uuid 
                    where cddbi.target_agent_uuid = ? order by bbt.id desc limit 1';
        $data = $this->dbSelect($sql, array($restoreDataUuid));
        if ($timeUnit == 1 || $timeUnit == 2) {  //1:最近十分钟 2:一小时
            if (!empty($data)) {

                $latestTime = $data[0]['latest_recv_transaction_time'];  //最新时间点
                $records['time_data']  = $this->getSecondLevelData($latestTime, $timeUnit);
            } else {
                $records['time_data'] = [];
            }
        } elseif ($timeUnit == 3) {   //3:最近一天
            if (!empty($data)) {
                $latestTime = $data[0]['latest_recv_transaction_time'];  //最新时间点
                $records['time_data']  = $this->getMinuteLevelData($latestTime, $timeUnit);
            }
        } elseif ($timeUnit == 4) {  //4:最近一周
            if (!empty($data)) {
                $latestTime = $data[0]['latest_recv_transaction_time'];  //最新时间点
                $records['time_data']  = $this->getMinuteLevelData($latestTime, $timeUnit);
            }
        }
        return $records;
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
    private function transportStrategyMessage($encryptFlag, $compressFlag, $blockSize, $networkUuid, $strategyGroupuuid,$transportCompressMethod,$transportEncryptMethod)
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
     * 获取秒级表对应时间轴需要的数据
     * @param unknown $latestTime       当前客户端最新可用时间点
     * @param unknown $agentUuid        客户端uuid
     * @param unknown $timeIntervalType 查询时间区间类型
     * @return unknown[]
     */
    private function getSecondLevelData($latestTime, $timeIntervalType)
    {
        if ($timeIntervalType == 1) {
            $timeIntervalValue = 600;
            $timeDif = strtotime($latestTime) - $timeIntervalValue;
        } elseif ($timeIntervalType == 2) {
            $timeIntervalValue = 3600;
            $timeDif = strtotime($latestTime) - $timeIntervalValue;
        }
        $difTimeStr = date('Y-m-d H:i:s', $timeDif); //10分钟前或1小时前的时间字符串
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType, $latestTime, $timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select se.backup_timestamp,se.data_flow,se.transaction_point_count,cddbt.transaction_id,cddbt.transaction_type
                from cdp_db_dr_backup_second_level_data_flow se left join cdp_db_dr_backup_transaction cddbt on se.backup_timestamp  = cddbt.transaction_time 
                where se.backup_timestamp between '" . $difTimeStr . "' and '" . $latestTime . "'";
        $data = $this->dbSelect($sql);  //十分钟/一小时前到最新时间内的所有时间点
        if (!empty($data)) {
            //获取当前时间，减去最新时间，获取二维数组下标位置
            foreach ($data as $d) {
                $timeStr = $d['backup_timestamp'];  //十分钟前或一小时前到最新时间点内的时间点
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime) - $timestamp;  //时间点到最新时间点相差的时间戳
                $listIndex = $timeIntervalValue - $indexDifNumber;  //时间点到十分钟/一小时前的时间戳
                $dataFlow = round($d['data_flow'] / 1024, 2);  //数据流量
                $bakcupFlowData[$listIndex][0] = $timeStr;  //获得listIndex时的时间点
                $bakcupFlowData[$listIndex][1] = $dataFlow;  //获得listIndex时的数据流量
                $bakcupFlowData[$listIndex][2] = $d['transaction_point_count'];  //获得listIndex时的事件点的个数
                //1 事件点 2 事务点
                $bakcupFlowData[$listIndex][3] = ($d['transaction_id'] == 70 && $d['transaction_type'] == 2) ? 2 : 1;
            }
        }
        return $bakcupFlowData;
    }

    /**
     * 获取分钟级表对应时间轴需要的数据
     * @param unknown $latestTime       当前客户端最新可用时间点
     * @param unknown $agentUuid        客户端uuid
     * @param unknown $timeIntervalType 查询时间区间类型
     * @return unknown[]
     */
    private function getMinuteLevelData($latestTime, $timeIntervalType)
    {
        if ($timeIntervalType == 3) {
            $timeIntervalValue = 1440;
            $timeDif = strtotime($latestTime) - ($timeIntervalValue * 60);
        } elseif ($timeIntervalType == 4) {
            $timeIntervalValue = 10080;  //单位分钟
            $timeDif = strtotime($latestTime) - ($timeIntervalValue * 60);
        }
        $difTimeStr =  date('Y-m-d H:i:s', $timeDif);   //1天前/1周前的时间字符串
        // 获取时间间隔周期默认的dataList
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType, $latestTime, $timeIntervalValue);
        $sql = "select se.backup_timestamp,se.data_flow,se.transaction_point_count,cddbt.transaction_id,cddbt.transaction_type
                from cdp_db_dr_backup_second_level_data_flow se left join cdp_db_dr_backup_transaction cddbt 
                on se.backup_timestamp = cddbt.transaction_time 
                where se.backup_timestamp between '" . $difTimeStr . "' and '" . $latestTime . "'";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            //获取当前时间，减去最新时间，获取二维数组下标位置
            foreach ($data as $d) {
                $timeStr = $d['backup_timestamp'];  //xx前到最新时间点内的时间点
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime) - $timestamp;  //时间点到最新时间点相差的时间戳
                $listIndex = $timeIntervalValue - $indexDifNumber / 60;  //时间点1天前/1周前的时间戳
                $dataFlow = round($d['data_flow'] / 1024, 2);  //数据流量
                $bakcupFlowData[$listIndex][0] = $timeStr;  //获得listIndex时的时间点
                $bakcupFlowData[$listIndex][1] = $dataFlow;  //获得listIndex时的数据流量
                $bakcupFlowData[$listIndex][2] = $d['transaction_point_count'];  //获得listIndex时的事件点的个数
                //1 事件点 2 事务点
                $bakcupFlowData[$listIndex][3] = ($d['transaction_id'] == 70 && $d['transaction_type'] == 2) ? 2 : 1;
            }
        }
        return $bakcupFlowData;
    }

    /**
     * 通过当前选择的时间区间类型及当前客户端可供配置的最新时间，计算默认data list
     * @param unknown $timeIntervalType  查询时间区间类型
     * @param unknown $latestTime        最新时间
     * @param unknown $timeIntervalValue 时间区间值 秒数
     * @return unknown  返回十分钟内或一小时内或一天内的每个时间点
     */
    private function createDefaultTimeList($timeIntervalType, $latestTime, $timeIntervalValue)
    {
        if ($timeIntervalType == 1 || $timeIntervalType == 2) {
            for ($i = $timeIntervalValue; $i >= 0; $i--) {
                $defaultTime = strtotime($latestTime) - $i; //秒
                $defaultTimeStr =  date('Y-m-d H:i:s', $defaultTime);
                $defaultFlow = 0;  //数据流量
                $defaultEventCount = 0;
                $defaultEventType = 1;  //事件类型
                $defaultData[] = array(
                    $defaultTimeStr,$defaultFlow,$defaultEventCount,$defaultEventType
                );
            }
        } elseif ($timeIntervalType == 3 || $timeIntervalType == 4) {
            for ($i = $timeIntervalValue; $i >= 0; $i--) {
                $defaultTime = intval(strtotime($latestTime) / 60) - $i; //分钟
                $defaultTimeStr =  date('Y-m-d H:i:s', $defaultTime * 60);
                $defaultFlow = 0;  //数据流量
                $defaultEventCount = 0;
                $defaultEventType = 1;  //事件类型
                $defaultData[] = array(
                    $defaultTimeStr,$defaultFlow,$defaultEventCount,$defaultEventType
                );
            }
            $defaultTimeStr =  date('Y-m-d H:i:s', $defaultTime);
            $defaultFlow = 0;  //数据流量
            $defaultEventCount = 0;
            $defaultData[] = array(
                $defaultTimeStr,$defaultFlow,$defaultEventCount
            );
            $defaultTimeStr = date('Y-m-d H:i:s', $defaultTime);
            $defaultFlow = 0;  //数据流量
            $defaultEventCount = 0;
            $defaultData[] = array(
                $defaultTimeStr,
                $defaultFlow,
                $defaultEventCount
            );
        }
        return $defaultData;
    }

    /**
     * 解析系统事件及应用事件
     * @param string $desription       描述
     * @param string $descriptionParam 描述参数
     * @return string
     */
    public function getEventDesriptionNotice($desription, $descriptionParam)
    {
        $dbCdpDes = xphp_get_desc('Dbcdp', 'DbCdpEventDes');
        $desStr = $dbCdpDes[$desription];
        if ($descriptionParam) {
            $param = json_decode($descriptionParam, true);
            $desArr = explode('%s', $desStr);
            $desStr = '';
            foreach ($desArr as $k => $v) {
                $desStr .= $v . $this->getEachParamsDes($param[$k], $param);
            }
        }
        return $desStr;
    }

    /**
     * 得到每一项参数的描述
     * @param string  $eachParams    每一项
     * @param boolean $classShowFlag 是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    private function getEachParamsDes($eachParams, $classShowFlag = true)
    {
        if (empty($eachParams)) {
            return '';
        }
        $des = '';
        $arr = explode(':', $eachParams, 2);
        switch ($arr[0]) {
            case 'S':
                if ($classShowFlag) {
                    $des = '<span  class="font-blue">' . $arr[1] . '</span>';
                } else {
                    $des = $arr[1];
                }
                break;
            case 'module_type':
                $des = $this->logModuleTypeDes($arr[1]);
                break;
            case 'backup_mode':
                $des = $this->logBackupModeDes($arr[1]);
                break;
            default:
                break;
        }
        return $des;
    }

    /**
     * 得到日志模块描述
     * @param int $index 模块定义键的位置
     * @return string
     */
    private function logModuleTypeDes($index)
    {
        $desConf = xphp_get_desc('Pf');
        return $desConf['MODULE_TYPE_DES'][intval($index)];
    }

    /**
     * 得到日志备份模式描述
     * @param int $index 模块定义键的位置
     * @return string
     */
    private function logBackupModeDes($index)
    {
        $desConf = xphp_get_desc('Pf');
        return $desConf['BACKUP_MODE_DES'][intval($index)];
    }

    /**
     * 得到事件类型
     * @param int $eventType 1,系统事件；2,任务事件
     * @return mixed
     */
    private function getEventTypeDesc($eventType)
    {
        if ($eventType == 1) {
            $eventTypeDesc = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_SYSTEM_EVENT');
        } elseif ($eventType == 2) {
            $eventTypeDesc = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_TASK_EVENT');
        }
        return $eventTypeDesc;
    }

    /**
     * 得到事件对应的级别
     * @param  int $eventLevel 等级
     * @return string
     */
    private function getEventLevelDesc($eventLevel)
    {
        $eventClass = 'label label-sm label-info';   //一般日志
        $levelStr = xphp_get_lang('WEB_PLATFORM_DES_GENERAL');
        if (xphp_get_config('log', 'LOGLEVEL')['WARN']  == $eventLevel) {
            $eventClass = 'label label-sm label-warning';
            $levelStr = xphp_get_lang('WEB_PLATFORM_DES_WARNING');
        } elseif (xphp_get_config('log', 'ERROR') == $eventLevel) {
            $eventClass = 'label label-sm label-danger';
            $levelStr = xphp_get_lang('WEB_PLATFORM_DES_ERROR');
        }
        $eventLevelStr = '<span class="' . $eventClass . '">' . $levelStr . '</span>';
        return $eventLevelStr;
    }
}
