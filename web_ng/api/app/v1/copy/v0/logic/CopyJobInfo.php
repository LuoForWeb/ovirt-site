<?php

/*
 * @note:
 * @author: chenyunfeng@vinchin.com
 * @Description: 副本容灾 --- 副本任务信息逻辑
 * @Date: 2023-08-22 10:30:59
 * @LastEditTime: 2026-02-26 10:57:13
 * @Version: 1.0
 * @copyright: Copyright 2023 vinchin.com
 */

namespace app\v1\copy\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\resources\v0\logic\Node;
use app\v1\tape\v0\logic\TapeInfo;

class CopyJobInfo extends Base
{
    /**
     * @param array $params 参数
     * @return array|false[]
     */
    public function getCopyBasicInfo($params)
    {
        $tapeHandler = new TapeInfo();
        $taskUUID = $params['task_uuid'];
        $info = (new \app\v1\job\v0\logic\JobInfo())->getJobInfo($taskUUID);
        $copyDes = require APP_PATH . 'v1/description/Copy.php';
        $sql = "SELECT
                    ct.copy_mode,
                    ct.chain_length,
                    ct.transport_ip,
                    ct.copy_week_flag,
                    ct.copy_month_flag,
                    ct.copy_year_flag,
                    cl.sub_type,
                    ct.copy_last_chain_flag,
                    cl.specified_timepoint_list,
                    cl.archive_timepoint_uuid,
                    ct.auto_add_flag,
                    cri.remainder_time,
                    bsr.storage_nickname,
                    bsr.storage_type,
                    bsr.total_size,
                    bsr.free_size,
                    bsr.node_uuid,
                    bn.node_nickname,
                    bn.host_name,
                    bn.ip 
                FROM
                    copy_task ct,
                    copy_list cl
                    LEFT JOIN copy_running_info cri ON cri.task_uuid = cl.task_uuid
                    LEFT JOIN bd_storage_resource bsr ON cl.source_storage_uuid = bsr.storage_uuid
                    LEFT JOIN bd_node bn ON bn.node_uuid = bsr.node_uuid 
                WHERE
                    cl.task_uuid = ? 
                    AND ct.task_uuid = ? 
                GROUP BY
                    cl.task_uuid";
        $data = $this->dbSelect($sql,[$taskUUID, $taskUUID]);
        $copy = $data[0];
        if (empty($data)) {
            return ['flag' => false];
        }
        $reserve_strategy = $info['reserve_strategy'];
        $reserve_strategy['copy_gfs_strategy'] = [
            "copy_week_flag"=> v1_parse_flag_to_bool($copy['copy_week_flag']),
            "copy_month_flag"=>v1_parse_flag_to_bool($copy['copy_month_flag']),
            "copy_year_flag"=>v1_parse_flag_to_bool($copy['copy_year_flag']),
        ];
        $basicInfo = array(
            'job_name' => $info['job_name'],
            'moduleType' => $info['module_type_des'],
            'moduleTypeValue' => $info['module_type'],
            'subModuleTypeValue' => $info['sub_module_type'],
            'taskType' => $info['job_type'],
            'job_type_des' => $info['job_type_des'],
            'status' => xphp_get_lang(xphp_get_config('task', 'TASKSTATUSDES')[$info['job_status']]),
            'statusValue' => $info['job_status'],
            'inc_mode' => $info['inc_mode'],
            'totalSize' => $info['total_size'],
            'currentSize' => $info['complate_size'],
            'speed' => $info['speed'],
            'progress' => $info['progress'],
            'totalprogress' => $info['percent_progress'],
            'createTime' => $info['create_time'],
            'startTime' => $info['start_time'],
            'intervalTime' => $info['interval_time'],
            'nextTime' => $info['next_time'],
            'timeStrategy' => $info['time_strategy'],
            'reservedStrategy' => $reserve_strategy,
            'dataEncrypt' => v1_parse_flag_to_bool($d['transport_data_encrypt']),
            'transportStrategy' =>$info['transport_strategy'],
            'storageInfo' => $info['storage_info'],
            'speed_limit' => $info['speed_limit'],
            'thread_num' => $info['thread_num'],
            'task_orchestration_plan_flag' => v1_parse_flag_to_bool($info['task_orchestration_plan_flag']),
            'flag' => true,
            'copy_mode' => $copy['copy_mode'],
            'copy_mode_des' => $copyDes['COPY_MODE'][$copy['copy_mode']],
            'chain_length' => $copy['chain_length'],
            'specified_timepoint_list' => json_decode($copy['specified_timepoint_list']),
            'op_list' => $this->getOperateCode($info['job_type'], $copy['copy_mode']),
            'remainder_time' => $copy['remainder_time'],
            'transport_ip' => $copy['transport_ip'],
            'sub_type' => $copy['sub_type'],
            'source_storage_info' => [
                'node'=>[
                    'name' => $copy['ip'] == $copy['node_nickname'] || empty($copy['node_nickname']) ? $copy['host_name'] : $copy['node_nickname'],
                    'ip' => $copy['ip']
                ],
                'storage' => [
                    'name' => $copy['storage_nickname'],
                    'type' => $this->getStorageTypeDes($copy['storage_type']),
                    'size' => v1_calSize($copy['total_size'], true),
                    'freesize' => v1_calSize($copy['free_size'], true),
                    'node' => $this->getNodeName($copy['node_uuid']),
                    'storage_type' => $copy['storage_type'],
                ]
            ],
            'tape_strategy' => $tapeHandler -> getTapeGroupStrategy(['group_uuid' => $info['storage_uuid']]) ?? '',
            'retry_strategy' => $info['retry_strategy'],
            'safe_strategy' => $info['safe_strategy'],
            'ignore_resource_limiting_flag' => $info['ignore_resource_limiting_flag'],
            // gfs策略
            "copy_gfs_strategy" => [
                "copy_week_flag"=> v1_parse_flag_to_bool($copy['copy_week_flag']),
                "copy_month_flag"=>v1_parse_flag_to_bool($copy['copy_month_flag']),
                "copy_year_flag"=>v1_parse_flag_to_bool($copy['copy_year_flag']),
            ],
            'user_uuid' => $info['user_uuid'],
            "copy_last_chain_flag" => v1_parse_flag_to_bool($copy['copy_last_chain_flag']),
            "task_source_flag" => empty($copy['archive_timepoint_uuid']) && empty($copy['specified_timepoint_list']),
            'auto_add_flag' => v1_parse_flag_to_bool($copy['auto_add_flag']),
        );
        return $basicInfo;
    }

    private function getOperateCode($task_type, $copy_mode)
    {
        $task_conf = xphp_get_config('task')['TASKTYPE'];
        $taskControl = xphp_get_config('task')['TASK_CONTROL'];
        switch ($task_type) {
            case $task_conf['BACKUP_COPY']:
                if (intval($copy_mode) == 2) {
                    $opCode = array(
                        $taskControl['START_FULL'], // 完整副本
                        $taskControl['START_INCR'], // 增量副本
                        $taskControl['STOP'],
                    );
                } else {
                    $opCode = array(
                        $taskControl['START'], // 镜像副本
                        $taskControl['STOP'],
                    );
                }
                break;
            case $task_conf['ARCHIVE']:
            case $task_conf['ARCHIVE_FETCH']:
            case $task_conf['BACKUP_COPY_FETCH']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                );
                break;
        }
        return $opCode;
    }

    /**
     * @param string $nodeuuid 节点uuid
     * @return string
     */
    public function getNodeName($nodeuuid)
    {
        $sql = "SELECT ip, node_nickname, host_name FROM bd_node WHERE node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $name = (new Node())->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']);
        return $name;
    }

    /**
     * @param int $storageType 存储设备类别
     * @return mixed|string
     */
    public function getStorageTypeDes($storageType)
    {
        $copyDes = require APP_PATH . 'v1/description/Copy.php';
        $des = '';
        if (empty($storageType)) {
            return $des;
        }
        $des = $copyDes['STORAGE_TYPE'][$storageType];
        return $des;
    }

    /**
     * @description: 获取副本任务详细信息
     * @param array $params 参数
     * @return array
     */
    public function getCopyDetails($params)
    {
        $instanceDb = [xphp_get_config('db', 'DB_TYPE')['SQLSERVER'], xphp_get_config('db', 'DB_TYPE')['SAPHANA']];
        $task_uuid = $params['task_uuid'];
        $module_type = $params['module_type'];
        $module_type = xphp_get_config('module', 'MODULE_TYPE');
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $itemStatus = xphp_get_config('copy', 'COPY_TASK_STATUS');
        $copyDes = require APP_PATH . 'v1/description/Copy.php';
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        $sql = "SELECT cl.item_name,cl.timepoint_count, cl.write_size, cl.total_size, cl.transport_size,cl.write_size, cl.item_status, cl.item_uuid, cl.error_code, cl.parent_uuid, cl.dir_path,cl.sub_type,
                bri.speed, bri.current_object_transport_size, bri.speed_time,bri.current_object_valid_size,bri.total_object_transport_size, bri.current_object_write_size, bri.current_object_completed_size,bri.current_object_total_size,
                bt.task_status, bt.task_type, ba.ip, ba.agent_name, bsr.storage_type
                FROM copy_list cl 
                LEFT JOIN bd_running_info bri ON cl.task_uuid = bri.task_uuid
                LEFT JOIN bd_task bt ON bt.task_uuid = cl.task_uuid 
                LEFT JOIN bd_agent ba ON ba.agent_uuid = cl.item_uuid
                LEFT JOIN bd_storage_resource bsr ON bsr.storage_uuid = cl.source_storage_uuid
                WHERE cl.task_uuid = ? ";
        $sqlCount = "SELECT count(cl.item_name) AS total FROM copy_list cl 
                    LEFT JOIN bd_running_info bri ON cl.task_uuid = bri.task_uuid
                    LEFT JOIN bd_task bt ON bt.task_uuid = cl.task_uuid 
                    LEFT JOIN bd_agent ba ON ba.agent_uuid = cl.item_uuid
                    WHERE cl.task_uuid = ? ";
        if(!empty($params['search'])){
            $sql .= " AND (cl.item_name LIKE '%{$params['search']}%' OR cl.dir_path LIKE '%{$params['search']}%')";
            $sqlCount .= " AND (cl.item_name LIKE '%{$params['search']}%' OR cl.dir_path LIKE '%{$params['search']}%')";
        }
        $sql .= " ORDER BY item_id  LIMIT ?, ?";
        $data = $this->dbSelect($sql, array($task_uuid, $params['offset'], $params['limit']));
        $count = $this->dbSelect($sqlCount, array($task_uuid));
        $records = array();
        $i = 1;
        $records['rows'] = array();
        foreach ($data as $d) {
            $item_status = $d['item_status']; //主机状态
            $task_status = $d['task_status']; //任务状态
            $timePointCount = intval($d['timepoint_count']);
            $name = $d['item_name'];
            if ($module_type == $module_type['OS'] || $module_type == $module_type['FS']) {
                if ($d['ip']) {
                    if (stripos($d['item_name'], $d['ip'])) {
                        $name = $d['item_name'];
                    } else {
                        $name = $d['item_name'] . "( " . $d['ip'] . ")";
                    }
                } else {
                    $name = $d['item_name'];
                }
            }
            if ($module_type == $module_type['NAS']) {
                $sqlNas = "SELECT ip, nas_nickname,share_path FROM nas_storage_resource WHERE nas_uuid = ?";
                $dataNas = $this->dbSelect($sqlNas, array($d['item_uuid']));
                $name = $d['item_name'];
                if (!empty($dataNas)) {
                    $name =  $dataNas[0]['ip'] == $dataNas[0]['nas_nickname'] ? $dataNas[0]['ip'] . "(" . $dataNas[0]['share_path'] . ")" : $dataNas[0]['ip'] . "(" . $dataNas[0]['nas_nickname'] . ")";
                }
            }
            if ($module_type == $module_type['DB'] && in_array($d['sub_type'],$instanceDb)) {
                $parts = explode(':', $d['item_name'], 2); // 只分割两次
                $name = $parts[0] . "(" . $parts[1] . ")";
            }
            if ($task_status == $taskStatus['RUNNING'] || $task_status == $taskStatus['ABNORMAL']) {
                if ($item_status == $itemStatus['COPY_ITEM_STATUS_RUNNING']) {
                    $records['rows'][] = array(
                        // 编号
                        'id' => $i++,
                        // 主机名/ip
                        'host_name' => $name,
                        // 时间点个数
                        'time_point_num' => $this->getCopyDetailStr($task_status, $item_status, $timePointCount),
                        // 数据总大小
                        'data_size' => $this->getCopyDetailStr($task_status, $item_status, v1_calSize($d['current_object_total_size'], true)),
                        // 传输大小
                        'transfer_size' => $this->getCopyDetailStr($task_status, $item_status, v1_calSize($d['current_object_transport_size'], true)),
                        // 写入大小
                        'write_size' => $this->getCopyDetailStr($task_status, $item_status, v1_calSize($d['current_object_write_size'], true)),
                        // 传输速度
                        'speed' => $this->getCopyDetailStr($task_status, $item_status, $this->getSpeed($item_status, $d['speed'], $d['speed_time'])),
                        // 传输进度
                        'percent' => $this->getCopyDetailStr($task_status, $item_status, $this->getPercent($d['current_object_total_size'], $d['current_object_completed_size'], $item_status)),
                        // 状态
                        'status' => $this->getCopyDetailStr($task_status, $item_status, $copyDes['COPY_TASK_STATUS'][$item_status]),
                        // 错误描述
                        'description' => $this->getErrorCodeDes($d['item_status'], $d['error_code']),
                        'dir_path' => $d['dir_path'],
                    );
                } else if ($item_status == $itemStatus['COPY_ITEM_STATUS_UNKNOWN']) {
                    $records['rows'][] = array(
                        'id' => $i++,
                        'host_name' => $name,
                        'time_point_num' => $nullSpace,
                        'data_size' => $nullSpace,
                        'transfer_size' => $nullSpace,
                        'write_size' => $nullSpace,
                        'speed' => $nullSpace,
                        'percent' => $nullSpace,
                        'status' => $nullSpace,
                        'description' => $this->getErrorCodeDes($d['item_status'], $d['error_code']),
                        'dir_path' => $d['dir_path'],
                    );
                } else {
                    $records['rows'][] = array(
                        // 编号
                        'id' => $i++,
                        // 主机名/ip
                        'host_name' => $name,
                        // 时间点个数
                        'time_point_num' => $timePointCount,
                        // 数据总大小
                        'data_size' => v1_calSize($d['total_size'], true),
                        // 传输大小
                        'transfer_size' => v1_calSize($d['transport_size'], true),
                        // 写入大小
                        'write_size' => v1_calSize($d['write_size'], true),
                        // 传输速度
                        'speed' => $nullSpace,
                        // 传输进度
                        'percent' => $item_status == $itemStatus['COPY_ITEM_STATUS_SUCCESS'] ? '100%' : $this->getPercent($d['total_size'], $d['write_size'], $item_status),
                        // 状态
                        'status' => $copyDes['COPY_TASK_STATUS'][$item_status],
                        // 错误描述
                        'description' => $this->getErrorCodeDes($d['item_status'], $d['error_code']),
                        'dir_path' => $d['dir_path'],
                    );
                }
            } else {
                $records['rows'][] = array(
                    'id' => $i++,
                    'host_name' => $name,
                    'time_point_num' => $nullSpace,
                    'data_size' => $nullSpace,
                    'transfer_size' => $nullSpace,
                    'write_size' => $nullSpace,
                    'speed' => $nullSpace,
                    'percent' => $nullSpace,
                    'status' => $nullSpace,
                    'description' => '',
                    'dir_path' => $d['dir_path'],
                );
            }
        }
        $records['total'] = $count[0]['total'];
        return  $records;
    }

    /**
     * @description: 获取主机详情信息
     * @param {*} $task_status
     * @param {*} $item_status
     * @param {*} $size
     * @return {*}
     */
    private function getCopyDetailStr($task_status, $item_status, $size)
    {
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $itemStatus = xphp_get_config('copy', 'COPY_TASK_STATUS');
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        if ($task_status == $taskStatus['RUNNING'] || $task_status == $taskStatus['ABNORMAL']) {
            if ($item_status == $itemStatus['COPY_ITEM_STATUS_RUNNING']) {
                return $size;
            } else {
                return $nullSpace;
            }
        } else {
            return $nullSpace;
        }
    }

    /**
     * @param int $status    状态
     * @param int $speed     速度
     * @param int $speedTime 速度时间
     * @return string
     */
    private function getSpeed($status, $speed, $speedTime)
    {
        $itemStatus = xphp_get_config('copy', 'COPY_TASK_STATUS');
        if (time() - $speedTime > 12) {
            $speed = 0;
        }
        if ($itemStatus['COPY_ITEM_STATUS_RUNNING'] == intval($status) || $itemStatus['COPY_ITEM_STATUS_ABNORMAL'] == intval($status)) {
            $speed = v1_calSpeed($speed);
        } else {
            $speed = '--';
        }

        return $speed;
    }

    /**
     * @param int $vmSize           大小
     * @param int $briCompletedSize 大小
     * @param int $vmlCompletedSize 大小
     * @param int $bdTaskStatus     任务状态
     * @param int $itemStatus       任务状态
     * @return number|string
     */
    private function getPercent($valid_size, $transport_size, $item_status)
    {
        $valid_size = intval($valid_size);
        $transport_size = intval($transport_size);
        if ($valid_size == $transport_size) {
            //如果相等
            if (empty($valid_size)) {
                if ($item_status == xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_SUCCESS']) {
                    return '100%';
                }
                return '0%';
            } else {
                return '100%';
            }
        }
        if ($transport_size > $valid_size) {
            //如果分子比分母大
            return '--';
        }
        return v1_calPercent($valid_size, $transport_size);
    }

    /**
     * @param int $bdTaskStatus 任务状态
     * @param int $vmTaskStatus 任务状态
     * @return mixed|string
     */
    public function getStatus($bdTaskStatus, $vmTaskStatus)
    {
        $taskstatus = xphp_get_config('task', 'TASKSTATUS');
        if (
            $bdTaskStatus == $taskstatus['RUNNING'] ||
            $bdTaskStatus == $taskstatus['ABNORMAL']
        ) {
            //任务在运行状态时,显示虚拟机的状态
            $copyDes = require APP_PATH . 'v1/description/Copy.php';
            return $copyDes['COPY_TASK_STATUS'][$vmTaskStatus];
        } else {
            return '--';
        }
    }

    /**
     * @param int $status    状态
     * @param int $errorCode 错误码
     * @return mixed|string
     */
    private function getErrorCodeDes($status, $errorCode)
    {
        if (xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_FAILED'] == intval($status)) {
            $des = xphp_get_config('error', 'errorCodeDes')[xphp_get_config('error', 'errorCode')[$errorCode]];
        } else {
            $des = '';
        }

        return $des;
    }

    /**
     * 获取虚拟机模块的子模块类型
     * @param $hypervisor
     * @return mixed
     */
    private function getVmSubModuleType($hypervisor)
    {
        $vmGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        if (!in_array($hypervisor, $vmGroup['openstack']) && !in_array($hypervisor, $vmGroup['publiccloud'])) {
            return xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        }
        if (in_array($hypervisor, $vmGroup['openstack'])) {
            return xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        }
        if (in_array($hypervisor, $vmGroup['publiccloud'])) {
            return xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
        }
        return xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
    }

    /**
     * @param array $params 参数
     * @return array
     */
    public function getCopyHistory($params)
    {
        $jobInfo = new JobInfo();
        $task_uuid = $params['task_uuid'];
        $start = $params['offset'] ?? 0;
        // 每页数量
        $length = $params['limit'] ?? 10;
        $sortFields = [
            'module_type' => 'module_type',
            'status' => 'error_code',
            'data_size' => 'total_object_size',
            'transfer_size' => 'total_object_transport_size',
            'write_size' => 'total_object_write_size',
            'start_time' => 'start_time',
            'end_time' => 'finish_time',
        ];
        $sort = $sortFields[$params['sort'] ?? 'end_time'] ?? 'finish_time';
        $order = $params['order'] ?? 'DESC';
        $this->paramsCheck($task_uuid);
        $sql = "SELECT task_type, module_type, current_mode, error_code, details, total_object_size,total_object_transport_size, total_object_write_size, average_speed,total_object_completed_size, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, submodule_type AS sub_type
                FROM bd_history_task
                WHERE task_uuid = ? ORDER BY $sort $order LIMIT $start, $length ";
        $sqlCount = "SELECT COUNT(task_type) AS total FROM bd_history_task WHERE task_uuid = ? ";
        $sqlParams = array($task_uuid);
        $sqlCountParams = array($task_uuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $i = 1;
        $records['rows'] = array();
        $moduleTypeDes = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        foreach ($data as $d) {
            $details = json_decode($d['details'], true);
            $module_type = $d['module_type'];
            $moduleDes = $moduleTypeDes[$d['module_type']];
            // 公有云、对象存储，hadoop模块类型显示
            switch ($module_type) {
                case $moduleType['VM']:
                    if ($this->getVmSubModuleType($d['sub_type']) == xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']) {
                        $moduleDes = xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD');
                    }
                    if ($this->getVmSubModuleType($d['sub_type']) == xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD']) {
                        $moduleDes = xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD');
                    }
                    break;
                case $moduleType['FS']:
                    if ($d['sub_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']) {
                        $moduleDes = $moduleTypeDes[$moduleType['OBS']];
                    } else if ($d['sub_type'] == xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']) {
                        $moduleDes = $moduleTypeDes[$moduleType['HADOOP']];
                    }
                    break;
            }
            $records['rows'][] = array(
                'id' => $i++,
                'task_type' => $d['task_type'],
                'status' => $this->getHistoryJobResultDes($d['error_code']),
                'status_value' => $d['error_code'],
                'data_size' => v1_calSize($d['total_object_size'], true),
                'transfer_size' => v1_calsize($d['total_object_transport_size'], true),
                'write_size' => v1_calsize($d['total_object_write_size'], true),
                'start_time' => $this->parseDate($d['start_time']),
                'end_time' => $this->parseDate($d['finish_time']),
                'module_type' => $moduleDes,
                'module_type_value' => $module_type,
                'details' => array(
                    'info' => $this->historyTaskDetailsHandlerCopy($details, $module_type, $d['sub_type']),
                    'task_type' => intval($d['task_type'])
                ),
            );
        }
        $records['total'] = $count[0]['total'];
        return  $records;
    }

    /**
     * @param int $status    状态
     * @param int $errorCode 错误码
     * @return mixed|string
     */
    public function getVMErrorCodeDes($status, $errorCode)
    {
        if (xphp_get_config('vm', 'VmTaskStatus')['ERROR'] == intval($status)) {
            $des = xphp_get_config('error', 'errorCodeDes')[xphp_get_config('error', 'errorCode')[$errorCode]];
        } else {
            $des = '';
        }
        return $des;
    }

    /**
     * @param array $details 详情
     * @return array|false
     */
    private function historyTaskDetailsHandlerCopy($details, $module_type, $sub_type = null)
    {
        if (empty($details)) {
            return false;
        }
        // 开启节点资源限制时直接返回配置
        if (isset($details['resource_limiting_node_config'])) {
            return $details;
        }
        $STATUS = xphp_get_desc('Vm', 'CopyTaskStatus');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $i = 0;
        $records = array();
        foreach ($details as $d) {
            $item_name = $d['item_name'];
            if ($module_type == $MODULE['NAS']) {
                if (stripos($d['item_name'], $d['ip'])) {
                    $item_name = $d['item_name'];
                } else {
                    $item_name = $d['ip'] . "( " . $d['item_name'] . ")";
                }
            } else if ($module_type == $MODULE['FS']) {
                if (stripos($d['item_name'], $d['ip'])) {
                    $item_name = $d['item_name'];
                } else {
                    $item_name = $d['item_name'] . "( " . $d['ip'] . ")";
                }
                if ($sub_type != 1) {
                    $item_name = $d['item_name'];
                }
            } else if($module_type == $MODULE['OS']){
                if (stripos($d['item_name'], $d['ip']) || empty($d['ip'])) {
                    $item_name = $d['item_name'];
                } else {
                    $item_name = $d['item_name'] . "( " . $d['ip'] . ")";
                }
            }else {
                $item_name = $d['item_name'];
            }
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $records[$i]['item_name'] = $item_name;
            $records[$i]['timepoint_count'] = $d['timepoint_count'] ? intval($d['timepoint_count']) : 0;
            $records[$i]['start_transfer_time'] = $d['start_transfer_time'] ? $d['start_transfer_time'] : '';
            $records[$i]['end_transfer_time'] = $d['end_transfer_time'] ? $d['end_transfer_time'] : '';
            $records[$i]['transfer_speed'] = $d['transfer_speed'] ? v1_calspeed(intval($d['transfer_speed'])) : 0;
            $records[$i]['total_size'] = $d['total_size'] ? v1_calsize($d['total_size'], true) : 0;
            $records[$i]['transport_size'] = $d['transport_size'] ? v1_calsize($d['transport_size'], true) : 0;
            $records[$i]['write_size'] = $d['write_size'] ? v1_calsize($d['write_size'], true) : 0;
            $records[$i]['valid_size'] = $d['valid_size'] ? v1_calsize($d['valid_size'], true) : 0;
            $records[$i]['item_status'] = $d['item_status'] ? $STATUS[intval($d['item_status'])] : '';
            $records[$i]['error_code'] = $d['item_status'] ? $this->getCopyErrorCodeDes($d['item_status'], $d['error_code']) : '';
            $i++;
        }
        return $records;
    }

    /**
     * @param int $errorCode 状态码
     * @return array|string
     */
    public function getHistoryJobResultDes($errorCode)
    {
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
        $errorCodeArr = xphp_get_config('error', 'errorCode');
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return xphp_get_lang('WEB_PLATFORM_DES_DISCONTINUE');
        }
        return $errorCode == 0 ? xphp_get_lang('WEB_PUBLIC_SUCCESS') : xphp_get_lang('WEB_PUBLIC_FAILURE');
    }

    /**
     * @param int $status    状态
     * @param int $errorCode 错误码
     * @return mixed|string
     */
    public function getCopyErrorCodeDes($status, $errorCode)
    {

        if (xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_FAILED'] == intval($status)) {
            $des = xphp_get_config('error', 'errorCodeDes')[xphp_get_config('error', 'errorCode')[$errorCode]];
        } else {
            $des = '';
        }
        return $des;
    }
    /**
     * @param array $params 参数
     * @return array
     */
    public function getTaskSpeed($params)
    {
        $taskUUID = $params['task_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT bt.module_type, bri.speed, bri.speed_time, bt.task_status FROM bd_running_info bri, bd_task bt WHERE bri.task_uuid = bt.task_uuid AND bri.task_uuid = ?";
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
}
