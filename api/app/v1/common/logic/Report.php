<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2025-08-14 09:48:44
 * @Description: 报表公共方法
 * @version: 1.0
 */

namespace app\v1\common\logic;

use app\v1\common\logic\Base;
use app\v1\filecopy\v0\logic\FileCopyJobInfo;
use app\v1\resources\v0\logic\Index as ResourceHandler;

class Report extends Base
{
    /**
     * 获取虚拟机备份历史数据
     * @return void
     */
    public function getAllVmHistoryData($keys = ''): array
    {

        $vmHistoryDataCache = xphp_get_cache($keys);

        if (empty($vmHistoryDataCache)) {
            // 执行查询并填充缓存
            $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
            $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP']; //备份

            $sql = "SELECT 
                                id, start_time,
                                JSON_UNQUOTE(JSON_EXTRACT(details, '$.vms_details[0].vm_uuid')) AS vm_uuid,
                                JSON_UNQUOTE(JSON_EXTRACT(details, '$.vms_details[0].vm_size')) AS vm_size,
                                JSON_UNQUOTE(JSON_EXTRACT(details, '$.vms_details[0].vm_valid_size')) AS vm_valid_size,
                                JSON_UNQUOTE(JSON_EXTRACT(details, '$.vms_details[0].transport_size')) AS transport_size,
                                JSON_UNQUOTE(JSON_EXTRACT(details, '$.vms_details[0].write_size')) AS write_size
                    FROM 
                        bd_history_task
                    WHERE 
                        module_type = ? AND task_type = ? AND error_code = 0";

            $vmHistoryDataCache = $this->dbSelect($sql, [$moduleType, $taskType]);

            xphp_set_cache($keys, $vmHistoryDataCache);
        }

        return $vmHistoryDataCache;
    }

    /**
     * 获取虚拟机保护中的任务
     * @param $vmUuid vm uuid
     * @return array
     */
    public function getVmProtectionState($vmUuid)
    {
        $sql = "select bt.task_name 
                from bd_task bt,vm_machine_list vml 
                where vml.task_uuid = bt.task_uuid and vml.vm_uuid = ?";
        $data = $this->dbSelect($sql, array($vmUuid));
        $taskName = '--';
        if (!empty($data)) {
            $taskName = $data[0]['task_name'];
        }
        return $taskName;
    }

    /**
     * 获取存储别名
     * @param mixed $vmUuid
     */
    public function getStorageNickName($vmUuid)
    {
        $sql = "select storage_nickname  from  vm_machine_list vml
                LEFT JOIN bd_task bt on bt.task_uuid = vml.task_uuid 
                inner join bd_storage_resource bsr on bsr.storage_uuid = bt.storage_uuid 
                where vml.vm_uuid = ?";
        $data = $this->dbSelect($sql, [$vmUuid]);
        return $data[0]['storage_nickname'];
    }

    /**
     * 获取虚拟机上次备份时间
     * @param $moduleType module type
     * @param  $taskType   task type
     * @param $vmUuid     vm uuid
     * @return false|string
     */
    public function getVmRunningInfo($vmHistoryData, $vmUuid): array
    {
        $moduleDataList = [];
        $totalObjectSize = 0;
        $totalVmValidSize = 0;
        $totalTransportSize = 0;
        $totalWriteSize = 0;
        $backupCount = 0;
        foreach ($vmHistoryData as $d) {
            if ($d['vm_uuid'] == $vmUuid) {
                $moduleDataList[] = $d['start_time'];
                $backupCount += 1;
                $totalObjectSize += $d['vm_size'];
                $totalVmValidSize += $d['vm_valid_size'];
                $totalTransportSize += $d['transport_size'];
                $totalWriteSize += $d['write_size'];
            }
        }
        $timestampList = array_map(function ($item) {
            return strtotime($item);
        }, $moduleDataList);
        $maxTimestamp = empty($timestampList) ? 0 : max($timestampList); // 获取最大的时间戳
        $lastRunningTime = '--';
        if (!empty($maxTimestamp)) {
            $lastRunningTime = date('Y-m-d H:i:s', $maxTimestamp);  // 将时间戳转换为日期格式,返回
        }
        return array(
            'last_backup_time' => $lastRunningTime,
            'backup_count' => $backupCount,
            'total_object_size' => $totalObjectSize,
            'backup_data' => $totalVmValidSize,
            'total_object_transport_size' => $totalTransportSize,
            'total_object_write_size' => $totalWriteSize
        );
    }

    /**
     * 获取资源组详细的资源
     * @param array $params 数组
     * @return array 数组
     */

    public function getTotalSize($masterUuid)
    {
        $sql = "SELECT sum( backup_file_size + log_file_total_size ) AS total 
                FROM cdp_vol_backup_agent cvba
	            INNER JOIN cdp_vol_backup_vol_set cvbvs ON cvba.id = cvbvs.backup_agent_id 
                WHERE cvba.master_agent_uuid = ?
	            GROUP BY master_agent_uuid";
        $data = $this->dbSelect($sql, [$masterUuid]);
        $sqlPoint = "select sum(total_size) as total  from bd_task_agent_list btal
                     left join bd_backup_timepoint bbt on bbt.task_uuid = btal.task_uuid
                     where btal.agent_uuid = ?";
        $dataPoint = $this->dbSelect($sqlPoint, [$masterUuid]);
        return $data[0]['total'] + $dataPoint[0]['total'];
    }

    /**
     * 获取客户端完全备份点个数
     * @param $agentUuid
     * @return string
     */
    public function getFullPoint($agentUuid, $moduleType)
    {
        $moduleTypeCfg = xphp_get_config('module', 'MODULE_TYPE');

        $sql = "SELECT 
                    count(*) AS total 
                FROM bd_agent ba 
                    INNER JOIN bd_task_agent_list  bl ON bl.agent_uuid = ba.agent_uuid
                    INNER JOIN bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid";


        switch ($moduleType) {
            case $moduleTypeCfg['DB']: // 数据库模块关联 db_backup_timepoint 子表
                $sql .= " INNER JOIN db_backup_timepoint dbt ON dbt.agent_uuid = ba.agent_uuid AND dbt.timepoint_uuid = bbt.timepoint_uuid";
                break;
            default:
                break;
        }

        $whereClause = " WHERE bbt.task_type IN ('1','24','28','32','35') AND backup_mode = 1 AND ba.agent_uuid = ?";
        $sql .= $whereClause;
        $data = $this->dbSelect($sql, [$agentUuid]);

        return $data[0]['total'];
    }

    /**
     * 获取客户端上运行过的备份任务所属的对象类型
     * @param mixed $agentUUID
     */
    public function getClientBackupTaskBelongModule($agentUUID): array
    {
        $taskTypes = xphp_get_config('report', 'CLIENT_RUNNING_BACKUP_COPY_TASK_TYPES');
        $taskTypesStr = implode(',', $taskTypes);

        $sql = 'SELECT
                    bht.module_type,
                    bht.submodule_type,
                    cvt.dev_type 
                FROM
                    bd_agent ba
                    LEFT JOIN cdp_vol_task cvt ON cvt.master_agent_uuid = ba.agent_uuid
                    LEFT JOIN bd_task_agent_list btal ON btal.agent_uuid = ba.agent_uuid
                    INNER JOIN bd_history_task bht ON bht.task_uuid = btal.task_uuid 
                WHERE
                    bht.task_type IN ( ' . $taskTypesStr . ' ) 
                    AND ba.agent_uuid = ?';

        $data = $this->dbSelect($sql, [$agentUUID]);

        if (empty($data)) {
            return [
                'module_type' => 0,
                'sub_module_type' => 0,
                'dev_type' => 0,
                'module_type_des' => '--'
            ];
        }

        $uniqueKeys = []; // 用于过滤客户端上同种类型的备份任务
        $result = [];

        foreach ($data as $d) {
            // 创建唯一键，将module_type 和 sub_module_type 拼接成字符串
            $key = $d['module_type'] . '_' . $d['sub_module_type'];

            // 如果该键尚未存在，则添加到结果中
            if (!in_array($key, $uniqueKeys)) {
                $uniqueKeys[] = $key;
                $devType = $d['dev_type'] ? $d['dev_type'] : 0;
                $result[] = [
                    'module_type' => $d['module_type'],
                    'sub_module_type' => $d['submodule_type'],
                    'dev_type' => $devType,
                    'module_type_des' => $this->getObjectType($d['module_type'], $d['submodule_type'], $devType)
                ];
            }
        }

        return $result;
    }

    /**
     * 获取客户端上特定模块类型备份数据大小（写入数据、可用数据和总数据）
     * @param mixed $agentUUID 客户端agent_uuid
     * @return array 包含module_type, submodule_type和存储统计信息的数组
     */
    public function getClientBackupSizeByModule($agentUUID, $moduleType, $submoduleType, $devType): array
    {
        $sql = "SELECT 
            bht.module_type,
            bht.submodule_type,
            bht.task_type,
            cvt.dev_type,
            SUM(bht.total_object_write_size) AS total_object_write_size,
            SUM(bht.total_object_valid_size) AS total_object_valid_size,
            SUM(bht.total_object_size) AS total_object_size
        FROM bd_agent ba 
        LEFT JOIN bd_task_agent_list btal ON ba.agent_uuid = btal.agent_uuid
        INNER JOIN bd_history_task bht ON btal.task_uuid = bht.task_uuid
        LEFT JOIN cdp_vol_task cvt ON bht.task_uuid = cvt.task_uuid
        WHERE ba.agent_uuid = ?";

        $where = '';

        $volCdpBackup = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $volCdpReplication = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'];
        if (!empty($moduleType)) {
            $buildsqls = []; // 通用的子模块查询
            foreach ($moduleType as $key => $item) {
                if ($item == xphp_get_config('module', 'MODULE_TYPE')['DB']) { // 数据库模块特殊处理，不需要拼接 submodule_type，因为该字段上存的是数据库类型db_type
                    $buildsqls[] = ' (bht.module_type = ' . $item . ')';
                } else {
                    if ($item == 10 && !empty($devType)) { // 实时或复制模块，需要拼接 dev_type 和 task_type
                        foreach ($devType as $v) { // dev_type 有四类：1,2,3,4
                            $v = intval($v);

                            if ($v <= 2) { // 1：卷实时 2：整机实时
                                $buildsqls[] = ' (bht.module_type = ' . $item . ' AND bht.submodule_type = ' . ($submoduleType[$key] ?? 0) . ' AND cvt.dev_type = ' . $v . ' AND bht.task_type = ' . $volCdpBackup . ')';
                            } else { // ($v - 2): 1 卷复制 2 整机复制
                                $buildsqls[] = ' (bht.module_type = ' . $item . ' AND bht.submodule_type = ' . ($submoduleType[$key] ?? 0) . ' AND cvt.dev_type = ' . ($v - 2) . ' AND bht.task_type = ' . $volCdpReplication . ')';
                            }
                        }
                    } else { // 非实时 非复制模块
                        $buildsqls[] = ' (bht.module_type = ' . $item . ' AND bht.submodule_type = ' . ($submoduleType[$key] ?? 0) . ')';
                    }
                }
            }

            if (!empty($buildsqls)) {
                $where .= ' AND (' . implode(' OR', $buildsqls) . ')';
            }

        }

        $sql .= $where . " GROUP BY bht.module_type, bht.submodule_type, cvt.dev_type";

        $data = $this->dbSelect($sql, [$agentUUID]);

        if (empty($data)) {
            return array(
                [
                    'module_type' => 0,
                    'sub_module_type' => 0,
                    'dev_type' => 0,
                    'module_type_des' => '--',
                    'total_object_write_size' => 0,
                    'total_object_valid_size' => 0,
                    'total_object_size' => 0
                ]
            );
        }

        // 格式化返回数据
        $result = [];
        foreach ($data as $row) {
            // 整机复制任务 dev_type + 2，方便getObjectType()区分对象类型
            if ($row['task_type'] == $volCdpReplication) {
                $devType = $row['dev_type'] + 2;
            } else {
                $devType = $row['dev_type'];
            }

            $result[] = [
                'module_type' => $row['module_type'],
                'sub_module_type' => $row['submodule_type'],
                'dev_type' => $row['dev_type'],
                'module_type_des' => $this->getObjectType($row['module_type'], $row['submodule_type'], $devType),
                'total_object_write_size' => $row['total_object_write_size'],
                'total_object_valid_size' => $row['total_object_valid_size'],
                'total_object_size' => $row['total_object_size']
            ];
        }

        return $result;
    }

    /**
     * 获取对象类型
     * @param mixed $moduleType
     * @param mixed $subModuleType
     * @param mixed $devType
     * @return array|string
     */
    private function getObjectType($moduleType, $subModuleType = '', $devType = 0)
    {
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');

        if ($moduleType == $moduleArr['VOL_CDP']) {
            switch ($devType) {
                case 1: // 卷实时
                    return xphp_get_lang('UI_REPORT_REAL_TIME_MACHINE_REEL_BACKUP');
                case 2: // 整机实时
                    return xphp_get_lang('UI_REPORT_REAL_TIME_COMPLETE_BACKUP');
                case 3: // 卷复制
                    return xphp_get_lang('UI_REPORT_COPY_MACHINE_REEL_BACKUP');
                case 4: // 整机复制
                    return xphp_get_lang('UI_REPORT_COPY_COMPLETE_BACKUP');
                default:
                    return '';
            }
        } elseif ($moduleType == $moduleArr['DB']) {  // 数据库模块不需要判断sub_module_type
            return xphp_get_lang('UI_PLATFORM_DATABASE');
        }

        $moduleArrs = explode(',', trim($moduleType, ','));
        $subModuleArrs = explode(',', trim($subModuleType, ','));

        // 如果 moduleArrs 为空，直接返回空
        if (empty($moduleArrs)) {
            return '';
        }

        // 根据module_type和sub_module_type返回对应的对象信息
        $objectArr = [
            '2-0' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2-1' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2-2' => ('UI_PLATFORM_PRIVATE_CLOUD'), // 私有云
            '2-3' => ('UI_PLATFORM_PUBLIC_CLOUD'), // 公有云
            '5-0' => ('UI_REPORT_TIMING_MACHINE_REEL_BACKUP'), // 定时卷（数据库中submodule_type存的0）
            '5-1' => ('UI_REPORT_TIMING_COMPLETE_BACKUP'), // 定时整机
            '5-2' => ('UI_REPORT_TIMING_MACHINE_REEL_BACKUP'), // 定时卷（兼容前端过滤筛选）
            '11-0' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '11-2' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '3-1' => ('UI_PLATFORM_FILEBACKUP'),// 文件系列 文件 
            '3-3' => ('UI_PLATFORM_HADOOP_HDFS'),// 文件系列 HADOOP
            '3-4' => ('UI_PLATFORM_OBS_STORAGE'),// 文件系列 OBS
            '4-0' => ('UI_PLATFORM_DATABASE'), // 数据库
            '14-0' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '14-1' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '28-0' => ('UI_PLATFORM_K8S'), // 容器
            '26-0' => ('UI_FILE_COPY'), // 文件复制 文件
            '12-0' => ('UI_PLATFORM_DATABASE'), // 数据库复制 数据库
            '10-0' => ('UI_REPORT_REAL_TIME_COMPLETE_BACKUP'), // 实时 需要根据 dev_type 区分，备份类型 1卷 2整机
            '10000-0' => ('UI_PLATFORM_DATABASE'), // 友商数据库
        ];

        $result = [];
        foreach ($moduleArrs as $key => $item) {
            $subKey = !isset($subModuleArrs[$key]) ? 0 : $subModuleArrs[$key];
            $items = $item . '-' . $subKey;
            if (isset($objectArr[$items])) {
                $result[] = xphp_get_lang($objectArr[$items]);
            }
        }

        // 去重 + 合并成字符串
        $result = array_unique($result);
        return implode(',', $result);
    }

    /**
     * 获取客户端上备份数据大小（写入数据、可用数据和总数据）
     * @param mixed $agentUUID
     * @return void
     */
    public function getClientBackupSize($agentUUID): array
    {
        $sql = "SELECT 
            bht.total_object_write_size,
            bht.total_object_valid_size,
            bht.total_object_size
        FROM bd_agent ba
        INNER JOIN bd_task_agent_list btal ON ba.agent_uuid = btal.agent_uuid
        INNER JOIN bd_history_task bht ON btal.task_uuid = bht.task_uuid
        WHERE ba.agent_uuid = ?";

        $data = $this->dbSelect($sql, array($agentUUID));

        if (empty($data)) {
            return [
                'total_object_write_size' => 0,
                'total_object_valid_size' => 0,
                'total_object_size' => 0
            ];
        }

        $total_object_write_size = 0;
        $total_object_valid_size = 0;
        $total_object_size = 0;
        foreach ($data as $d) {
            $total_object_write_size += $d['total_object_write_size'];
            $total_object_valid_size += $d['total_object_valid_size'];
            $total_object_size += $d['total_object_size'];
        }

        return [
            'total_object_write_size' => $total_object_write_size,
            'total_object_valid_size' => $total_object_valid_size,
            'total_object_size' => $total_object_size
        ];
    }

    /**
     * 获取客户端增量备份点个数
     * @param $agentUuid
     * @return string
     */
    public function getIncrementPoint($agentUuid, $moduleType)
    {
        $moduleTypeCfg = xphp_get_config('module', 'MODULE_TYPE');

        $sql = "SELECT 
                    count(*) as total 
                FROM 
                    bd_agent ba 
                    INNER JOIN bd_task_agent_list bl ON bl.agent_uuid = ba.agent_uuid
                    INNER JOIN bd_backup_timepoint bbt on bbt.task_uuid = bl.task_uuid";

        switch ($moduleType) {
            case $moduleTypeCfg['DB']: // 数据库模块关联 db_backup_timepoint 子表
                $sql .= " INNER JOIN db_backup_timepoint dbt ON dbt.agent_uuid = ba.agent_uuid AND dbt.timepoint_uuid = bbt.timepoint_uuid";
                break;
            default:
                break;
        }

        $whereClause = " WHERE bbt.task_type IN ('1','24','28','32','35') AND backup_mode = 2 AND ba.agent_uuid = ?";
        $sql .= $whereClause;
        $data = $this->dbSelect($sql, [$agentUuid]);
        return $data[0]['total'];
    }

    /**
     * 获取客户端增量备份点个数
     * @param $agentUuid
     * @return string
     */
    public function getDiffrencePoint($agentUuid, $moduleType)
    {
        $moduleTypeCfg = xphp_get_config('module', 'MODULE_TYPE');

        $sql = "SELECT count(*) AS total 
            FROM 
                bd_agent ba 
                INNER JOIN bd_task_agent_list  bl ON bl.agent_uuid = ba.agent_uuid
                INNER JOIN bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid";

        switch ($moduleType) {
            case $moduleTypeCfg['DB']: // 数据库模块关联 db_backup_timepoint 子表
                $sql .= " INNER JOIN db_backup_timepoint dbt ON dbt.agent_uuid = ba.agent_uuid AND dbt.timepoint_uuid = bbt.timepoint_uuid";
                break;
            default:
                break;
        }

        $whereClause = " WHERE bbt.task_type in ('1','24','28','32','35') and backup_mode = 3 and ba.agent_uuid = ?";
        $sql .= $whereClause;
        $data = $this->dbSelect($sql, [$agentUuid]);
        return $data[0]['total'];
    }

    /**
     * 获取归档日志备份点个数
     * @param mixed $agentUUID
     */
    public function getArchivedLogPoint($agentUUID, $moduleType)
    {
        $moduleTypeCfg = xphp_get_config('module', 'MODULE_TYPE');

        $sql = "SELECT
                    count(*) AS total 
                FROM
                    bd_agent ba
                    INNER JOIN bd_task_agent_list bl ON bl.agent_uuid = ba.agent_uuid
                    INNER JOIN bd_backup_timepoint bbt ON bbt.task_uuid = bl.task_uuid";

        switch ($moduleType) {
            case $moduleTypeCfg['DB']: // 数据库模块关联 db_backup_timepoint 子表
                $sql .= " INNER JOIN db_backup_timepoint dbt ON dbt.agent_uuid = ba.agent_uuid AND dbt.timepoint_uuid = bbt.timepoint_uuid";
                break;
            default:
                break;
        }

        $whereClause = " WHERE bbt.task_type IN ( 17, 18, 28 ) AND backup_mode = 4 AND ba.agent_uuid = ?";
        $sql .= $whereClause;
        $data = $this->dbSelect($sql, [$agentUUID]);
        return $data[0]['total'];
    }

    public function getOganizationUuidName(string $organizationUuid, string $userName = ''): string
    {
        $sqlParams = [$organizationUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 56 and resource_uuid = ?";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 56";
            $array = $this->dbSelect($sql, $sqlParams);
        }
        if ($array) {
            $sql = "select user_name from bd_user where user_uuid in ('" . implode("','", array_column($array, 'user_uuid')) . "')";
            $data = $this->dbSelect($sql);
            $userName = implode(',', array_column($data, 'user_name'));
        }
        return $userName;
    }

    /**
     * 获取存储用途描述
     * @param int $usemode
     * @return string|mixed
     */
    public function getUsemodeDes($usemode)
    {
        $des = "";
        switch ($usemode) {
            //华为cbr默认选择--
            case 0:
                $des = "--";
                break;
            case 1:
                $des = xphp_get_lang('UI_PLATFORM_BACKUP');
                break;
            case 2:
                $des = xphp_get_lang('WEB_PLATFORM_DES_COPY') . '|' . xphp_get_lang('UI_PLATFORM_ARCHIVE');
                break;
            case 3:
                $des = xphp_get_lang('UI_PLATFORM_ARCHIVE');
                break;
            case 4:
                $des = "NAS";
                break;
            default:
                $des = xphp_get_lang('UI_PLATFORM_BACKUP');
                break;
        }
        return $des;

    }

    /**
     * 获取上次备份时间
     * @param array $params 数组
     * @return array 数组
     */
    public function getLastStart($params)
    {
        $sql = "select start_time  from bd_history_task bht 
                where  bht.task_uuid = ?
                ORDER BY start_time DESC LIMIT 1;";
        $data = $this->dbSelect($sql, [$params]);
        return $data[0]['start_time'];
    }

    /**
     * 获取存储
     * @param array $params 数组
     * @return array 数组
     */
    public function getStorageName($params)
    {
        $sql = "select storage_nickname  from bd_task bt 
                left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
                where  bt.task_uuid = ?";
        $data = $this->dbSelect($sql, [$params]);
        return $data[0]['storage_nickname'] ? $data[0]['storage_nickname'] : '--';
    }

    public function getStatus($taskuuid)
    {
        $sql = "select task_status from bd_task bt where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return $data[0]['task_status'] ? $data[0]['task_status'] : 0;
    }

    /**
     * 获取任务下次开始时间
     * @param string $taskuuid   任务uuid
     * @param $taskStatus 任务状态
     * @return array|mixed|string
     */
    public function getJobNextstarttime(string $taskuuid, $taskStatus)
    {

        $sql = "select unix_timestamp(bs.next_start_time) next_start_time from bd_task bt, bd_strategy bs 
                        where bt.strategy_id = bs.strategy_id and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nextTime = $timesapce = xphp_get_config('app', 'TIMESPACE');
        if (!empty($data)) {
            $nowTime = time();
            if ($data[0]['next_start_time'] < $nowTime) {
                $nextTime = $timesapce;
            } else {
                $nextTime = $this->parseDate($data[0]['next_start_time']);
            }
        }

        if (intval($taskStatus) == xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
            $nextTime = $timesapce;
        }

        return $nextTime;
    }

    /**
     * 获取客户端生成备份集状态
     * @param $uuid uuid
     * @return void
     */
    public function getHostBackupSetInfo($uuid)
    {
        $sql = "select * from cdp_vol_backup_agent where master_agent_uuid =?";
        $data = $this->dbSelect($sql, array($uuid));
        return count($data);
    }

    public function getHostBackupData($masterUuid)
    {
        $sql = "select sum(backup_file_size + log_file_total_size) as total_size 
                from cdp_vol_backup_vol_set cvbvs
                inner join cdp_vol_backup_agent cvba on cvba.id = cvbvs.backup_agent_id
                where master_agent_uuid =?";
        $data = $this->dbSelect($sql, array($masterUuid));
        return $data[0]['total_size'];
    }

    /**
     * 得到应用接管
     * @param int $taskUuid 任务id
     * @return string
     */
    public function getAutoTakeover($taskUuid)
    {
        $sql = "select auto_takeover_flag  from bd_history_task bht
                left join cdp_vol_task cvt on cvt.task_uuid = bht.task_uuid
                where  bht.task_uuid = ?";
        $data = $this->dbSelect($sql, [$taskUuid]);
        return $data[0]['auto_takeover_flag'] ? $data[0]['auto_takeover_flag'] : 2;
    }

    /**
     * 获取传输代理 agent ip
     *
     * @param string $appliance_uuid
     * @return void
     */
    public function getApplianceAgency(string $appliance_uuid): string
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
     * 根据对象存储uuid获取当前的使用者是谁 只查询佩芬的资源/组
     *
     * @param string $obsUUID 对象存储uuid
     * @param string $userName 拥有者
     * @return string
     */
    public function getObsUUIDName(string $obsUUID, string $userName = ''): string
    {
        $sqlParams = [$obsUUID];
        // 先 再分配的资源和资源组去查询
        $sql = "select user_uuid from mt_user_resource where resource_type = 59 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql, $sqlParams);

        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 59";
            $array = $this->dbSelect($sql, $sqlParams);
        }

        if ($array) {
            $sql = "select user_name from bd_user where user_uuid in ('" . implode("','", array_column($array, 'user_uuid')) . "')";
            $data = $this->dbSelect($sql);
            $userName = implode(',', array_column($data, 'user_name'));
        }

        return $userName;
    }

    /**
     * 处理下历史任务的任务状态搜索条件
     * 1成功 2中止 3异常 4 失败
     * @param array $jobStats 状态数组
     * @return string
     */
    public function getJobStatusSql(array $jobStats)
    {
        $arr = [
            1 => [0],
            2 => [45],
            3 => [47],
            4 => [0, 45, 47], // 不在这个里面
        ];
        $arr1 = $arr2 = [];
        foreach ($jobStats as $item) {
            if ($item == 4) {
                $arr2 = array_merge($arr2, $arr[$item]);
            } else {
                $arr1 = array_merge($arr1, $arr[$item]);
            }
        }
        // 最后中和下arr1和arr2的元素
        if (!empty($arr2)) {
            // 存在not in
            $arrs = array_diff($arr2, $arr1); // 去除in的
            return !empty($arrs) ? ' not in (' . implode(',', $arrs) . ')' : '';
        }

        return !empty($arr1) ? ' in (' . implode(',', $arr1) . ')' : '';
    }

    /**
     * 获取文件复制任务源端和目标端
     * @param mixed $details
     * @return string[]
     */
    public function getFilecopyTaskDetails($details)
    {
        $result = array();
        $fileCopy = new FileCopyJobInfo($details);

        $result['source_name'] = $fileCopy->getSubAgentName($details['source_type'], $details['source_uuid']);
        $result['target_name'] = $fileCopy->getSubAgentName($details['target_type'], $details['target_uuid']);

        return $result;
    }

    /**
     * 添加自定义报表模版
     * @param mixed $params 自定义报表参数
     * @param mixed $overviewKeys 概览键数组
     * @param mixed $customFieldKeys 自定义字段键数组
     * @return string
     */
    public function addCustomReportTemplate($params, $overviewKeys, $customFieldKeys)
    {
        $templateName = $params['templateName'];
        $templateType = $params['templateType'];
        $recevieEmail = $params['recEmail'];

        $this->dbBeginTransaction();

        if (count($params['timeStrategy']) > 0) {
            $timeStrategy = $this->setNoticeConf($params['timeStrategy']);
            $reportFlag = true;
            $email_notice_flag = 1;
        } else {
            $reportFlag = false;
            $timeStrategy = [];
            $email_notice_flag = 2;
        }

        $emailType = 2;
        $reportConfig = array();
        $reportConfig['reportFlag'] = $reportFlag;
        $reportConfig['timeStrategy'] = $timeStrategy;
        $reportConfig['moudleType'] = $params['templateType'];
        $reportConfig = json_encode($reportConfig);
        $recevieEmail = json_encode($recevieEmail);
        $emailParams = array($email_notice_flag, $reportConfig, $recevieEmail, $emailType);
        $emailSql = "insert into bd_email_notice 
                (email_notice_flag, report_config, receive_email, email_notice_type) 
                values (?, ?, ?, ?)";
        $this->dbExec($emailSql, $emailParams);

        $id = $this->dbLastInsertId();

        // 获取概览字段
        $overview = $this->buildFieldArray($params['overview'], $overviewKeys);

        // 获取历史运行趋势字段
        $runningTendency = $this->buildFieldArray($params['runningTendency'], ['history']);

        // 获取自定义字段
        $customField = $this->buildFieldArray($params['customField'], $customFieldKeys);

        // 组装详情
        $detail = [
            'template_type' => $templateType,
            'overview' => $overview,
            'running_tendency' => $runningTendency,
            'custom_field' => $customField
        ];

        $detailJson = json_encode($detail);

        $description = $params['remark'] ?? '';
        $uuid = xphp_uuid();
        $user = xphp_get_user_info();
        $createTime = date('Y-m-d H:i:s');
        $userUuid = $user['userUuid'] ?? '';
        $username = $user['userName'] ?? '';

        $sqlParams = [
            $uuid,
            $templateName,
            $templateType,
            $createTime,
            $userUuid,
            $username,
            $id,
            $description,
            $detailJson
        ];

        $sql = "INSERT INTO bd_report_template 
                (template_uuid, template_name, template_type, create_time, user_uuid, user_name, email_notice_id, description, detail) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
            return $this->muOpResult(true, xphp_get_lang('UI_GLOBAL_STRATEGY_AUTO'));
        } else {
            $this->dbRollBack();
            return $this->muOpResult(false, xphp_get_lang('UI_GLOBAL_STRATEGY_AUTO'));
        }
    }

    /**
     * 修改自定义报表模版
     * @param mixed $params 自定义报表参数
     * @param mixed $overviewKeys 概览键数组
     * @param mixed $customFieldKeys 自定义字段键数组
     * @return string
     */
    public function modifyCustomReportTemplate($params, $overviewKeys, $customFieldKeys)
    {
        $templateName = $params['templateName'];
        $templateType = $params['templateType'];
        $templateUuid = $params['templateUuid'];
        $recevieEmail = $params['recEmail'];

        $emailSql = "select email_notice_id from bd_report_template where template_uuid = '$templateUuid'";
        $emailSqlParams = array();
        $dataEmailId = $this->dbSelect($emailSql, $emailSqlParams);
        $emailId = $dataEmailId[0]['email_notice_id'];

        $this->dbBeginTransaction();

        if (count($params['timeStrategy']) > 0) {
            $timeStrategy = $this->setNoticeConf($params['timeStrategy']);
            $reportFlag = true;
            $email_notice_flag = 1;
        } else {
            $timeStrategy = [];
            $reportFlag = false;
            $email_notice_flag = 2;
        }

        $emailType = 2;
        $reportConfig = array();
        $reportConfig['reportFlag'] = $reportFlag;
        $reportConfig['timeStrategy'] = $timeStrategy;
        $reportConfig['moudleType'] = $params['templateType'];
        $reportConfig = json_encode($reportConfig);
        $recevieEmail = json_encode($recevieEmail);
        $editEmailSqlParams = array($email_notice_flag, $reportConfig, $recevieEmail, $emailType, $emailId);
        $editEmailsql = "UPDATE 
                            bd_email_notice 
                        SET 
                            email_notice_flag = ?, report_config = ?, receive_email = ?, email_notice_type = ? 
                        WHERE 
                            id = ?";
        $this->dbExec($editEmailsql, $editEmailSqlParams);

        // 获取概览字段
        $overview = $this->buildFieldArray($params['overview'], $overviewKeys);

        // 获取历史运行趋势字段
        $runningTendency = $this->buildFieldArray($params['runningTendency'], ['history']);

        // 获取自定义字段
        $customField = $this->buildFieldArray($params['customField'], $customFieldKeys);

        // 组装详情
        $detail = [
            'template_type' => $templateType,
            'overview' => $overview,
            'running_tendency' => $runningTendency,
            'custom_field' => $customField
        ];

        $detailJson = json_encode($detail);

        $description = $params['remark'];
        $editTempleSqlParams = array($templateName, $templateType, $description, $detailJson, $templateUuid);
        $editTempleSql = "UPDATE 
                            bd_report_template 
                        SET 
                            template_name = ?, template_type = ?, description = ?, detail = ? 
                        WHERE 
                            template_uuid = ?";

        $result = $this->dbExec($editTempleSql, $editTempleSqlParams);

        if ($result) {
            $this->dbCommit();
            return $this->muOpResult(true, xphp_get_lang('UI_GLOBAL_STRATEGY_AUTO'));
        } else {
            $this->dbRollBack();
            return $this->muOpResult(false, xphp_get_lang('UI_GLOBAL_STRATEGY_AUTO'));
        }
    }

    /**
     * 修改邮箱类容格式
     * @param array $params 数组
     * @return array 数组
     */
    private function setNoticeConf(array $params)
    {
        $currentTime = strtotime(date('H:i:s'));
        $currentDate = strtotime(date('Y-m-d H:i:s'));
        foreach ($params as $s) {
            $sendDate = ''; //最后发送日期作为已发送标记，未发送设为空，当前时间超过设置时间就设为已发送
            $type = $s['type'];
            $time = strtotime($s['notice_time']);
            switch ($type) {
                case 1:
                    if ($currentTime >= $time) {
                        $sendDate = date('Y-m-d');
                    }
                    break;
                case 2:
                    $weekDay = date('w');
                    if ($weekDay == 0) {
                        $weekDay = 7;
                    }
                    $days = $s['days'];
                    $dayList = array();
                    for ($i = 0; $i < count($days); $i++) {
                        if ($days[$i] == 1) {
                            $dayList[] = $i + 1;
                        }
                    }
                    if ($weekDay == $dayList[0] && $currentTime >= $time || $weekDay > $dayList[0]) {
                        $sendDate = date('Y-m-d');
                    }
                    break;
                case 3:
                    $monthDay = date('j');
                    $days = $s['days'];
                    $dayList = array();
                    for ($i = 0; $i < count($days); $i++) {
                        if ($days[$i] == 1) {
                            $dayList[] = $i + 1;
                        }
                    }
                    if ($monthDay == $dayList[0] && $currentTime >= $time || $monthDay > $dayList[0]) {
                        $sendDate = date('Y-m-d');
                    }
                    break;
                case 4:
                    if ($currentDate >= $time) {
                        $sendDate = date('Y-m-d');
                    }
                    break;
            }
            $s['send_date'] = $sendDate;
        }
        return $params;
    }

    /**
     * 构建字段数组
     * @param mixed $sourceArray 源数组
     * @param mixed $keys 键列表
     * @return bool[] 构建后的字段数组
     */
    private function buildFieldArray($sourceArray, $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = in_array($key, $sourceArray);
        }

        return $result;
    }

    /**
     * 获取已创建任务的客户端个数
     * @return void
     */
    public function getTaskAgentNumber($userUUID = ''): int
    {
        $sql = "select distinct btal.agent_uuid from bd_task_agent_list btal
                inner join bd_agent ba on ba.agent_uuid = btal.agent_uuid where ba.agent_type not in (3, 4)";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'btal.agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }

        $data = $this->dbSelect($sql);
        return count($data);
    }

    /**
     * 获取模块任务总个数
     * @param $module module
     * @return int
     */
    public function getModuleTaskNumber($module, $userUUID = '')
    {
        $sql = "select * from bd_task where module_type = ?";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $data = $this->dbSelect($sql, array($module));
        return count($data);
    }

    /**
     * 获取备份数据，单位字节
     * @param $moduleType module type
     * @return int
     */
    public function getModuleBackupData($moduleType, $userUUID = '')
    {
        $sql = "select total_size,user_uuid from bd_backup_timepoint where module_type = ?";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " and user_uuid in ({$userUuidSql}) ";
        }

        $data = $this->dbSelect($sql, array($moduleType));
        $backupData = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $backupData += $d['total_size'];
            }
        }
        return $backupData;
    }

    /**
     * 获取文件客户端信息
     * @return []
     */
    public function getFileClientInfo($userUUID = '')
    {
        $flag = xphp_get_config('app', 'FLAG');
        $sql = "SELECT online_flag FROM bd_agent WHERE agent_type NOT IN (2, 3, 4)";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }

        $data = $this->dbSelect($sql);
        $online = 0;
        $offline = 0;
        $total = count($data);

        foreach ($data as $d) {
            if (intval($d['online_flag']) == $flag['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $total
        );
        return $info;
    }

    /**
     * 获取nas设备信息
     * @return void
     */
    public function getNasClientInfo($userUUID = ''): array
    {
        $user = xphp_get_user_info();
        $flag = xphp_get_config('app', 'FLAG');
        $sql = "SELECT nas_status FROM nas_storage_resource WHERE 1 = 1";

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['NAS'], 'nas_uuid');
            $sql .= " AND ({$resourceUuidSql}) ";
        }

        $data = $this->dbSelect($sql);
        $online = 0;
        $offline = 0;
        $total = count($data);
        foreach ($data as $d) {
            if (intval($d['nas_status']) == $flag['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $total
        );

        return $info;
    }

    /**
     * 获取复制任务运行数
     * @param mixed $module
     * @param mixed $subModule
     * @return int
     */
    public function pGetTaskNum($module, $subModule = null, $userUUID = '')
    {
        $sql = "SELECT COUNT(task_uuid) AS total FROM bd_task WHERE module_type = ? AND task_type = ? ";

        $sqlParams = array($module, xphp_get_config('task', 'TASKTYPE')['BACKUP']);

        if ($subModule == xphp_get_config('module', 'VM_SUB_MODULE')['VM']) {
            $sql .= " and sub_module_type != ? ";
            $sqlParams = array_merge($sqlParams, array(xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']));
        } else if (!empty($subModule)) {
            $sql .= " and sub_module_type =? ";
            $sqlParams = array_merge($sqlParams, array($subModule));
        }

        switch ($module) {
            case xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES']: // KUBERNETES
                $sqlParams = array($module, xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP']);
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY']://文件复制
                $sqlParams = array($module, xphp_get_config('task', 'TASKTYPE')['FILE_COPY']);
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['DB_CDP']://数据库复制    
                $sqlParams = array($module, xphp_get_config('task', 'TASKTYPE')['DB_CDP_SYN']);
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['DB']://数据库保护    
                $sqlParams = array($module, xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']);
                break;
            default:
                break;
        }

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND user_uuid IN ({$userUuidSql}) ";
        }

        $data = $this->dbSelect($sql, $sqlParams);

        return intval($data[0]['total']);
    }

    public function getDbCopyTotalData()
    {
        $sql = "SELECT sum(cdt.completed_size) AS completed_size FROM cdp_db_dr_task_progress_info cdt LEFT JOIN bd_task bt ON cdt.task_uuid = bt.task_uuid WHERE 1 = 1";
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND bt.user_uuid IN ({$userUuidSql}) ";
        }
        $data = $this->dbSelect($sql, []);
        $sizeInfo = $this->pGetIntToSizeInfo($data[0]['completed_size']);

        return $sizeInfo;
    }

    /**
     * 公共函数
     * 获取保护数据总量
     * @param int $moduleType  模块类型
     */
    public function pGetProtectData($moduleType, $subModule = null, $userUUID = '')
    {
        $user = xphp_get_user_info();
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');

        $sql = "select sum(write_size) as write_size from  bd_backup_timepoint where module_type = ? and user_uuid = ? and task_type = ? ";
        $taskType = $taskTypeArr['BACKUP'];
        switch ($moduleType) {
            case $moduleTypeArr['DB']:
                $taskType = $taskTypeArr['DB_BACKUP'];
                break;
            case $moduleTypeArr['OS']:
                $taskType = $taskTypeArr['OS_BACKUP'];
                break;
            case $moduleTypeArr['KUBERNETES']:
                $taskType = $taskTypeArr['KUBE_BACKUP'];
                break;
            default:
                break;
        }

        $sqlParams = array($moduleType, $user['userUuid'], $taskType);
        if ($moduleType == $moduleTypeArr['VM'] && $subModule == xphp_get_config('module', 'VM_SUB_MODULE')['VM']) {
            $sql .= " and sub_module_type != ? ";
            $sqlParams = array_merge($sqlParams, array(xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']));
        } else if (!empty($subModule)) {
            $sql .= " and sub_module_type =? ";
            $sqlParams = array_merge($sqlParams, array($subModule));
        }

        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND user_uuid IN ({$userUuidSql}) ";
        }

        $data = $this->dbSelect($sql, $sqlParams);

        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']));
    }

    /**
     * 获取大小转换成界面显示信息
     * @param unknown $size
     */
    private function pGetIntToSizeInfo($size)
    {
        $writeSize = v1_calsize($size, true);
        $writeInfo = v1_calsize_to_value_and_unit($size, true);
        $info = array(
            'size' => $size,
            'value' => $writeInfo['value'],
            'unit' => $writeInfo['unit'],
            'des' => $writeSize
        );
        return $info;
    }

    /**
     * 获取模块备份次数
     * @param $moduleType moduleType
     * @return void
     */
    public function getModuleBackupNum($moduleType, $userUUID = ''): int
    {
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $privateCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $cloudTypesStr = implode("','", $privateCloudTypes);

        $sql = "select * from bd_history_task where module_type = ? and submodule_type not in ('" . $publicCloudTypesStr . "') and submodule_type not in ('" . $cloudTypesStr . "') and task_type = 1";
        if (v1_auth_need_check_look($userUUID)) {
            // 三权模式下的操作员只能查看分配存储列表
            // 不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sql .= " AND user_uuid IN ({$userUuidSql}) ";
        }
        $data = $this->dbSelect($sql, array($moduleType));

        return count($data);  //备份次数
    }

    /**
     * 按来源和日期分组数据
     */
    public function groupBySourceAndDay($data)
    {
        $groupedData = [
            'db' => [],
            'file' => [],
            'os' => [],
            'cdp' => [],
        ];

        foreach ($data as $item) {
            if (isset($item['alarm_day']) && isset($item['total']) && isset($item['source'])) {
                $groupedData[$item['source']][$item['alarm_day']] = $item['total'];
            }
        }

        return $groupedData;
    }

    /**
     * 得到节点总状态
     * @param string $nodeuuid
     * @return array('flag'=> boolean, 'module'=>array(modules))
     */
    public function getNodeAllStatus($nodeuuid)
    {
        $sql = "select module_type, online_flag from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $flag = true;
        $module = array();
        if (empty($data)) {
            $flag = false; //如果没有记录
        }
        $info = array(
            'flag' => $flag,
            'module' => $module
        );
        return $info;
    }

    /**
     * 获取报表模版名称和创建时间
     * @param [type] $uuid email_notice_id
     * @param [type] $noticeTypeDes 通知类型 日报 周报 月报 年报
     * @return void
     */
    public function getReportDetail($uuid, $noticeTypeDes): array
    {
        $sql = "select template_name,create_time from bd_report_template 
                where email_notice_id = '$uuid'";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        return array(
            'templateType' => '【' . $noticeTypeDes . '】',
            'templateName' => $data[0]['template_name'],
            'createTime' => $data[0]['create_time'],
        );
    }

    private function getMasterNodeIp(): string
    {
        $ip = '';
        $sql = "select ip from bd_node where node_type = ? limit 1";
        $data = $this->dbSelect($sql, [xphp_get_config('app', 'NODETYPE')['MASTER']]);
        if (!empty($data)) {
            $ip = $data[0]['ip'];
        }
        return $ip;
    }

    public function getMasterNodeIpLink(): string
    {
        $ip = $this->getMasterNodeIp();
        $serverIpArr = explode(' ', $ip);
        $host = 'https://' . $serverIpArr[0];
        return '<a class="font-success" style="text-decoration: none" href="' . $host . '">' . $host . '</a>  ';
    }

    public function vmEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $vmNumber = 0;
        $protectVm = 0;
        $unProtectVm = 0;
        $vcenter = 0;
        $backupCount = 0;
        $backupData = 0;
        $content = '';
        $base64Img = "";
        foreach ($detailInfo as $d) {
            $vmNumber += $d['vm_total'];
            $protectVm += $d['vm_protected_total'];
            $unProtectVm += $d['vm_unprotected_total'];
            $vcenter += $d['vm_platform_total'];
            $backupCount += $d['backup_number'];
            $backupData += $d['backup_data'];

            $info = '<tr><td>' . $d['vm_total'] . '</td>' .
                '<td>' . $d['vm_platform_total'] . '</td>' .
                '<td>' . $d['vm_protected_total'] . '</td>' .
                '<td>' . $d['vm_unprotected_total'] . '</td>' .
                '<td>' . $d['backup_number'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('#tableContent#', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('vm_number', $vmNumber, $message);
        $message = str_replace('protected_number', $protectVm, $message);
        $message = str_replace('unprotectedNumber', $unProtectVm, $message);
        $message = str_replace('vm_platform', $vcenter, $message);
        $message = str_replace('backup_number', $backupCount, $message);
        $message = str_replace('backup_data', v1_calsize($backupData, true), $message);
        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    /**
     * 生成任务报表邮件内容
     * @param mixed $detailInfo
     * @param mixed $message
     * @param mixed $alarmInfo
     * @param mixed $nodeInfo
     * @param mixed $nameInfo
     * @return array|string
     */
    public function taskEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $totalCurrentTask = 0;
        $stopedTaskNumber = 0;
        $totalSuccessNumber = 0;
        $failedTaskNumber = 0;
        $abnoraml = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $totalCurrentTask += $d['task_num'];
            $stopedTaskNumber += $d['stop_task'];
            $totalSuccessNumber += $d['success_task'];
            $failedTaskNumber += $d['failed_task'];
            $abnoraml += $d['abnormal_task'];
            $info = '<tr><td>' . $d['task_num'] . '</td>' .
                '<td>' . $d['stop_task'] . '</td>' .
                '<td>' . $d['success_task'] . '</td>' .
                '<td>' . $d['failed_task'] . '</td>' .
                '<td>' . $d['abnormal_task'] . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('#tableContent#', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);

        $message = str_replace('task_total', $totalCurrentTask, $message);
        $message = str_replace('stop_task', $stopedTaskNumber, $message);
        $message = str_replace('success_task', $totalSuccessNumber, $message);
        $message = str_replace('abnormal_task', $abnoraml, $message);
        $message = str_replace('fail_task', $failedTaskNumber, $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function storageEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $deviceTotal = 0;
        $onLineNumber = 0;
        $offLineNumber = 0;
        $totalBackupData = 0;
        $totalCopyData = 0;
        $totalArchiveData = 0;
        $usedStorage = 0;
        $unUsedStorage = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $usedStorage += $d['used_storage'];
            $unUsedStorage += $d['unused_storage'];
            $onLineNumber += $d['online_device'];
            $offLineNumber += $d['offline_number'];
            $totalBackupData += $d['backup_data'];
            $totalCopyData += $d['copy_data'];
            $totalArchiveData = $d['archived_data'];
            $info = '<tr><td>' . $d['storage_device'] . '</td>' .
                '<td>' . $d['online_number'] . '</td>' .
                '<td>' . $d['offline_number'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td>' .
                '<td>' . v1_calsize($d['copy_data'], true) . '</td>' .
                '<td>' . v1_calsize($d['archived_data'], true) . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('tableContent', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);

        $message = str_replace('online_device', $onLineNumber, $message);
        $message = str_replace('offline_device', $offLineNumber, $message);
        $message = str_replace('used_storage', v1_calsize($usedStorage, true), $message);
        $message = str_replace('unusedStorage', v1_calsize($unUsedStorage, true), $message);
        $message = str_replace('backupData', v1_calsize($totalBackupData, true), $message);
        $message = str_replace('copyData', v1_calsize($totalCopyData, true), $message);
        $message = str_replace('archivedData', v1_calsize($totalArchiveData, true), $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function clientEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $clientNumber = 0;
        $onLineNumber = 0;
        $offLineNumber = 0;
        $protectNumber = 0;
        $unProtectNumber = 0;
        $totalBackupData = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $clientNumber += $d['host_number'];
            $onLineNumber += $d['online_host'];
            $offLineNumber += $d['offline_host'];
            $protectNumber += $d['protected_client'];
            $unProtectNumber += $d['unprotected_client'];
            $totalBackupData += $d['backup_data'];
            $info = '<tr><td>' . $d['host_number'] . '</td>' .
                '<td>' . $d['online_host'] . '</td>' .
                '<td>' . $d['offline_host'] . '</td>' .
                '<td>' . $d['protected_client'] . '</td>' .
                '<td>' . $d['unprotected_client'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('tableContent', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);

        $message = str_replace('host_number', $clientNumber, $message);
        $message = str_replace('protected_client', $protectNumber, $message);
        $message = str_replace('unprotectedClient', $unProtectNumber, $message);
        $message = str_replace('online_host', $onLineNumber, $message);
        $message = str_replace('offline_host', $offLineNumber, $message);
        $message = str_replace('backup_data', v1_calsize($totalBackupData, true), $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function nasEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $nasDeviceNumber = 0;
        $onLineNumber = 0;
        $offLineNumber = 0;
        $totalBackupData = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $nasDeviceNumber += $d['device_number'];
            $onLineNumber += $d['online_number'];
            $offLineNumber += $d['offline_number'];
            $totalBackupData += $d['backup_data'];
            $info = '<tr><td>' . $d['device_number'] . '</td>' .
                '<td>' . $d['online_number'] . '</td>' .
                '<td>' . $d['offline_number'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td>' .
                '<td>' . $d['nas_task'] . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('tableContent', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);

        $message = str_replace('device_number', $nasDeviceNumber, $message);
        $message = str_replace('protected_number', $d['protected_number'], $message);
        $message = str_replace('unprotectedNumber', $d['unprotected_number'], $message);
        $message = str_replace('online_number', $onLineNumber, $message);
        $message = str_replace('offline_number', $offLineNumber, $message);
        $message = str_replace('backup_data', v1_calsize($totalBackupData, true), $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function cdpEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $hostNumber = 0;
        $protectedTotal = 0;
        $unprotectedTotal = 0;
        $backupData = 0;
        $backupSetNumber = 0;
        $taskNumber = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $hostNumber += $d['host_number'];
            $protectedTotal += $d['protected_total'];
            $unprotectedTotal += $d['unprotected_total'];
            $backupData += $d['backup_data'];
            $backupSetNumber += $d['backup_set_number'];
            $taskNumber += $d['task_number'];
            $info = '<tr><td>' . $d['host_number'] . '</td>' .
                '<td>' . $d['protected_total'] . '</td>' .
                '<td>' . $d['unprotected_total'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td>' .
                '<td>' . $d['backup_set_number'] . '</td>' .
                '<td>' . $d['task_number'] . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('tableContent', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);

        $message = str_replace('host_number', $hostNumber, $message);
        $message = str_replace('protected_total', $protectedTotal, $message);
        $message = str_replace('unprotectedTotal', $unprotectedTotal, $message);
        $message = str_replace('task_total', $taskNumber, $message);
        $message = str_replace('backup_set_number', $backupSetNumber, $message);
        $message = str_replace('backup_data', v1_calsize($backupData, true), $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function appEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $appNumber = 0;
        $appOnline = 0;
        $appOffline = 0;
        $appProtected = 0;
        $appBackup = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $appNumber += $d['app_number'];
            $appOnline += $d['app_online'];
            $appOffline += $d['app_offline'];
            $appProtected += $d['app_protected'];
            $appBackup += $d['backup_data'];
            $info = '<tr><td>' . $d['app_number'] . '</td>' .
                '<td>' . $d['app_online'] . '</td>' .
                '<td>' . $d['app_offline'] . '</td>' .
                '<td>' . $d['app_protected'] . '</td>';
            '<td>' . v1_calsize($d['backup_data'], true) . '</td></tr>' .

                $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('tableContent', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);

        $message = str_replace('app_number', $appNumber, $message);
        $message = str_replace('app_online', $appOnline, $message);
        $message = str_replace('app_offline', $appOffline, $message);
        $message = str_replace('app_protected', $appProtected, $message);
        $message = str_replace('app_backup', v1_calsize($appBackup, true), $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function publicEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $vmNumber = 0;
        $protectVm = 0;
        $unProtectVm = 0;
        $vcenter = 0;
        $backupCount = 0;
        $backupData = 0;
        $content = '';
        $base64Img = "";
        foreach ($detailInfo as $d) {
            $vmNumber += $d['vm_number'];
            $protectVm += $d['protected_number'];
            $unProtectVm += $d['unprotected_number'];
            $vcenter += $d['vm_platform'];
            $backupCount += $d['backup_number'];
            $backupData += $d['backup_data'];

            $info = '<tr><td>' . $d['vm_number'] . '</td>' .
                '<td>' . $d['vm_platform'] . '</td>' .
                '<td>' . $d['protected_number'] . '</td>' .
                '<td>' . $d['unprotected_number'] . '</td>' .
                '<td>' . $d['backup_number'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('#tableContent#', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('vm_number', $vmNumber, $message);
        $message = str_replace('protected_number', $protectVm, $message);
        $message = str_replace('unprotectedNumber', $unProtectVm, $message);
        $message = str_replace('vm_platform', $vcenter, $message);
        $message = str_replace('backup_number', $backupCount, $message);
        $message = str_replace('backup_data', v1_calsize($backupData, true), $message);
        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function privateEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $vmNumber = 0;
        $protectVm = 0;
        $unProtectVm = 0;
        $vcenter = 0;
        $backupCount = 0;
        $backupData = 0;
        $content = '';
        $base64Img = "";
        foreach ($detailInfo as $d) {
            $vmNumber += $d['vm_number'];
            $protectVm += $d['protected_number'];
            $unProtectVm += $d['unprotected_number'];
            $vcenter += $d['vm_platform'];
            $backupCount += $d['backup_number'];
            $backupData += $d['backup_data'];

            $info = '<tr><td>' . $d['vm_number'] . '</td>' .
                '<td>' . $d['vm_platform'] . '</td>' .
                '<td>' . $d['protected_number'] . '</td>' .
                '<td>' . $d['unprotected_number'] . '</td>' .
                '<td>' . $d['backup_number'] . '</td>' .
                '<td>' . v1_calsize($d['backup_data'], true) . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('#tableContent#', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('vm_number', $vmNumber, $message);
        $message = str_replace('protected_number', $protectVm, $message);
        $message = str_replace('unprotectedNumber', $unProtectVm, $message);
        $message = str_replace('vm_platform', $vcenter, $message);
        $message = str_replace('backup_number', $backupCount, $message);
        $message = str_replace('backup_data', v1_calsize($backupData, true), $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function obEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $obTotal = 0;
        $obNormal = 0;
        $obOffline = 0;
        $content = '';
        $base64Img = "";
        foreach ($detailInfo as $d) {
            $obTotal += $d['obs_total'];
            $obNormal += $d['obs_normal'];
            $obOffline += $d['obs_offline'];

            $info = '<tr><td>' . $d['obs_total'] . '</td>' .
                '<td>' . $d['obs_normal'] . '</td>' .
                '<td>' . $d['obs_offline'] . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('#tableContent#', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('ob_total', $obTotal, $message);
        $message = str_replace('ob_normal', $obNormal, $message);
        $message = str_replace('ob_offline', $obOffline, $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);

        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function hadoopEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $hadoopTotal = 0;
        $hadoopAuth = 0;
        $hadoopOnline = 0;
        $hadoopOffline = 0;
        $content = '';
        foreach ($detailInfo as $d) {
            $hadoopTotal += $d['hadoop_total'];
            $hadoopAuth += $d['hadoop_auth'];
            $hadoopOnline += $d['hadoop_online'];
            $hadoopOffline += $d['hadoop_offline'];

            $info = '<tr><td>' . $d['hadoop_total'] . '</td>' .
                '<td>' . $d['hadoop_auth'] . '</td>' .
                '<td>' . $d['hadoop_online'] . '</td>' .
                '<td>' . $d['hadoop_offline'] . '</td>' .
                '<td>' . $d['backup_number'] . '</td></tr>';
            $content .= $info;
        }
        $message = str_replace('display-hide', 'display-show', $message);
        $message = str_replace('#tableContent#', $content, $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('hadoop_total', $hadoopTotal, $message);
        $message = str_replace('hadoop_auth', $hadoopAuth, $message);
        $message = str_replace('hadoop_online', $hadoopOnline, $message);
        $message = str_replace('hadoop_offline', $hadoopOffline, $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $message = str_replace('notice_type_des', $alarmInfo['notice_type_des'], $message);
        $message = str_replace('task_number', $alarmInfo['task_alarm_number'], $message);
        $message = str_replace('system_number', $alarmInfo['system_alarm_number'], $message);
        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function k8sEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $k8sTotal = $detailInfo['total'];
        $k8sProtected = $detailInfo['protectedNum'];
        $k8sOnline = $detailInfo['online'];
        $k8sOffline = $detailInfo['offline'];
        $backupNumber = $detailInfo['taskNum'];
        $backupData = v1_calsize($detailInfo['totalCopyData']['value'], true);

        $message = str_replace('display-hide', 'display-show', $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('total', $k8sTotal, $message);
        $message = str_replace('protectedNum', $k8sProtected, $message);
        $message = str_replace('online', $k8sOnline, $message);
        $message = str_replace('offline', $k8sOffline, $message);
        $message = str_replace('backup_number', $backupNumber, $message);
        $message = str_replace('backup_data', $backupData, $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }

    public function filecopyEmailContent($detailInfo, $message, $alarmInfo, $nodeInfo, $nameInfo)
    {
        $data = date("Y-m-d H:i:s");
        $copyObject = $detailInfo['totalClient'];
        $copyTarget = $detailInfo['totalClient'];
        $protectedNum = $detailInfo['protectedNum'];
        $taskNum = $detailInfo['taskNum'];
        $totalCopyData = v1_calsize($detailInfo['totalCopyData'], true);

        $message = str_replace('display-hide', 'display-show', $message);

        $message = str_replace('templateType', $nameInfo['templateType'], $message);
        $message = str_replace('reportCreateTime', $data, $message);
        $message = str_replace('reportName', $nameInfo['templateName'], $message);
        $message = str_replace('reportTime', $nameInfo['createTime'], $message);
        $message = str_replace('copyObject', $copyObject, $message);
        $message = str_replace('copyObject', $copyObject, $message);
        $message = str_replace('copyTarget', $copyTarget, $message);
        $message = str_replace('protectedNum', $protectedNum, $message);
        $message = str_replace('taskNum', $taskNum, $message);
        $message = str_replace('totalCopyData', $totalCopyData, $message);

        $message = str_replace('nodeName', $nodeInfo['master_ip'], $message);
        $company_email = xphp_get_config('app', 'SYSTEM_INFO');
        $company_email = $company_email['company_email'];
        $message = str_replace('supportEmail', $company_email, $message);
        $backupServerHost = $this->getMasterNodeIpLink();
        $message = str_replace('interServerHost', $backupServerHost, $message);

        return $message;
    }
}
