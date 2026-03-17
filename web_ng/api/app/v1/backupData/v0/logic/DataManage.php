<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-07-15 15:47:45
 * @LastEditTime: 2026-01-27 15:52:16
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

namespace app\v1\backupData\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use DateTime; // 显式导入 DateTime 类
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\complete_machine_os\v0\logic\MachineOsBackup;
use app\v1\common\logic\JobInfo;

class DataManage extends Base
{
    // 无数据占位符
    private const NULL_RESULT_DES = "--";
    // 时间点副表联查sql
    private const POINT_LEFT_JOIN_SQL = " LEFT JOIN vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
                LEFT JOIN vm_vcenter vc ON vbt.vcenter_uuid = vc.vcenter_uuid
                LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
                LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                LEFT JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
                LEFT JOIN m365_backup_timepoint mbt ON bbt.timepoint_uuid = mbt.m365_timepoint_uuid
                LEFT JOIN kube_backup_timepoint kbt ON bbt.timepoint_uuid = kbt.timepoint_uuid
                LEFT JOIN cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
                LEFT JOIN cdp_vol_backup_vol_set vol ON agent.id = vol.backup_agent_id
                LEFT JOIN cdp_db_dr_backup_info cddbi ON bbt.timepoint_uuid = cddbi.timepoint_uuid
                LEFT JOIN bd_agent_app baa ON baa.app_uuid = cddbi.source_app_uuid
                LEFT JOIN bd_agent ba ON ba.agent_uuid = cddbi.source_agent_uuid";
    // 备份数据基础字段查询sql
    private const POINT_BASE_SQL = "SELECT
                                        bbt.timepoint,
                                        bbt.timepoint_uuid,
                                        bbt.backup_mode,
                                        bbt.total_size,
                                        bbt.write_size,
                                        bbt.storage_uuid,
                                        bbt.task_uuid,
                                        bbt.module_type,
                                        bbt.sub_module_type,
                                        bbt.task_type,
                                        bbt.task_name,
                                        bbt.remarks,
                                        bbt.encrypted_flag,
                                        bbt.weekly_flag,
                                        bbt.monthly_flag,
                                        bbt.yearly_flag,
                                        bbt.importance_flag,
                                        bbt.operation_status,
                                        bbt.depend_point_uuid,
                                        bbt.merge_status,
                                        bbt.worm_flag,
                                        bbt.integrity_check_flag,
                                        bbt.verify_flag,
                                        bbt.real_node_uuid,
                                        bbt.detail,
                                        bbt.src_data_deleted_flag,
                                        bbt.chain_uuid,
                                        bbt.detail,
                                        bbt.user_uuid,
                                        bbt.chain_status,
                                        bt.task_uuid AS task_delete,
                                        bsr.node_uuid,
                                        bsr.storage_type,
                                        vbt.vm_name,
                                        vbt.hypervisor_type,
                                        vbt.vcenter_uuid,
                                        vbt.vm_uuid,
                                        vbt.dir_path AS vm_path,
                                        fbt.agent_ip AS fs_ip,
                                        fbt.agent_name,
                                        fbt.agent_uuid,
                                        fbt.agent_uuid AS fs_uuid,
                                        fbt.backup_path_list AS fs_path,
                                        dbt.agent_ip AS db_ip,
                                        dbt.db_name,
                                        dbt.depend_task_uuid,
                                        dbt.cluster_name AS db_cluster_name,
                                        dbt.db_type,
                                        dbt.dir_path AS db_path,
                                        dbt.db_uuid,
                                        dbt.instance_name,
                                        dbt.agent_uuid AS db_agent_uuid,
                                        dbt.cluster_uuid AS db_cluster_uuid,
                                        obt.agent_ip AS os_ip,
                                        obt.agent_uuid AS os_uuid,
                                        obt.os_name,
                                        obt.dir_path AS os_path,
                                        obt.os_config,
                                        mbt.m365_timepoint_uuid,
                                        mbt.organization_uuid,
                                        mbt.organization_name,
                                        mbt.organization_info,
                                        vol.start_timestamp,
                                        vol.end_timestamp,
                                        vol.backup_file_size,
                                        vol.log_file_total_size,
                                        vol.storage_status,
                                        vol.vol_uuid,
                                        vol.vol_display_name,
                                        vol.is_boot,
                                        vol.capacity,
                                        vol.id AS vol_id,
                                        agent.master_agent_uuid,
                                        agent.master_agent_detail,
                                        agent.storage_location,
                                        agent.dev_type,
                                        agent.id AS backup_set_id,
                                        bsi.virus_scan_status,
                                        bsi.virus_list,
                                        bsi.last_virus_scan_time,
                                        bsi.last_integrity_check_time,
                                        bsi.integrity_check_status,
                                        bsi.worm_expire_date,
                                        baa.app_name,
                                        baa.app_type,
                                        baa.app_uuid,
                                        baa.app_listen_ip,
                                        cddbi.target_agent_uuid,
                                        cddbi.source_agent_uuid,
                                        ba.ip AS target_app_ip,
                                        cddbi.last_redo_replay_time,
                                        cddbi.latest_recv_transaction_time,
                                        cddbi.backup_info_status,
                                        kbt.cluster_uuid,
                                        kbt.cluster_name,
                                        kbt.by_type 
                                    FROM
                                        bd_backup_timepoint bbt
                                        LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                                        LEFT JOIN bd_task bt ON bbt.task_uuid = bt.task_uuid";
    // 用于暂存时间点，去重
    private $timepoint_arr = [];
    // 获取任务表格
    public function getTaskGrid($params)
    {
        // 参数
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        //配置
        $FLAG = xphp_get_config('app', 'FLAG');
        $CHAIN_STATUS = xphp_get_config('point', 'CHAIN_STATUS');
        $TASK_TYPE = xphp_get_config('task', 'TASKTYPE');
        $VMHYPERVISORDES = xphp_get_config('vm', 'VMHYPERVISORDES');
        $DB_TYPE_DES = xphp_get_config('db', 'DB_TYPE_DES');
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        // 排序字段匹配
        $sortArr = [
            "task_name" => "bbt.task_name",
            "module_type_des" => "bbt.module_type, bbt.sub_module_type",
            "task_type_des" => "bbt.task_type",
            "total_size" => "SUM(bbt.total_size)",
            "write_size" => "SUM(bbt.write_size)",
            "point_num" => "COUNT(bbt.task_uuid)",
            "task_create_time" => "bbt.task_create_time",
            "sub_type_des" => "vbt.hypervisor_type,dbt.db_type",
            "vcenter_name" => "vcenter_name",
            "deleted_flag" => "deleted_flag",
        ];
        // 查询语句
        $sqlData = "SELECT
                        bbt.task_uuid,
                        bbt.task_name,
                        bbt.module_type,
                        bbt.task_type,
                        bbt.sub_module_type,
                        bbt.task_create_time,
                        bbt.storage_uuid,
                        bbt.backup_mode,
                        bbt.timepoint_uuid,
                        bbt.real_node_uuid,
                        bbt.src_data_deleted_flag,
                        vbt.hypervisor_type,
                        vbt.vcenter_uuid,
                        dbt.db_type,
                        COUNT( bbt.task_uuid ) AS point_num,
                        SUM( bbt.total_size ) AS total_size,
                        SUM( bbt.write_size ) AS write_size,
                        agent.dev_type,
                        agent.id AS backup_set_id,
                        bbt.chain_status,
                        bbt.user_uuid";
        // 查询数量
        $sqlCount = "SELECT COUNT(bbt.timepoint_uuid) as total ";
        $sql = " FROM bd_backup_timepoint bbt
                LEFT JOIN vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
                LEFT JOIN cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
                LEFT JOIN db_backup_timepoint dbt ON dbt.timepoint_uuid = bbt.timepoint_uuid
                WHERE bbt.deleted_flag = {$FLAG['UNSET']}  AND bbt.import_flag = {$FLAG['UNSET']} AND (dbt.depend_task_uuid = '' OR dbt.depend_task_uuid IS NULL)";
        $sql =  $this->addFilterParams($sql, $params);
        // 查询条件-关键字匹配
        if (!empty($params['search'])) {
            $sql .= " AND bbt.task_name LIKE '%{$params['search']}%'";
        }
        // 去除重复任务
        $sql .= " GROUP BY bbt.task_uuid";
        if (!empty($sort) && !empty($order)) {
            $sql .= " ORDER BY {$sortArr[$sort]} {$order}";
        }
        $sqlTask = $sqlData . $sql;
        if (!empty($limit)) {
            $sqlTask .= " LIMIT {$offset},{$limit}";
        }
        // 查询结果
        $data = $this->dbSelect($sqlTask, []);
        // 获取数据的task_uuid列表
        $task_uuid_arr = array_column((array)$data, 'task_uuid');
        // 查询数量
        $dataCount = $this->dbSelect($sqlCount . $sql, []);
        // 获取所有任务列表
        $all_task_arr = $this->getAllTaskIdArr($task_uuid_arr);
        // 获取所有子任务列表
        $sub_task_arr = $this->getSubTaskList();
        // 获取所有vm_vcenter信息
        $vm_vcenter_arr = $this->getVmVcenter();
        // 获取列表的最新任务名称
        $newest_data = $this->getTimePointNewName($task_uuid_arr);
        // 处理数据，组装返回数据
        $record = [];
        $record['rows'] = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $children = [];
                foreach ($sub_task_arr as $st) {
                    if ($st['pid'] == $d['task_uuid']) {
                        $st['deleted_flag'] = !in_array($st['task_uuid'], $all_task_arr);
                        $children[] = $st;
                    }
                }
                $sub_type_des = $VMHYPERVISORDES[$d['hypervisor_type']] ?: $DB_TYPE_DES[$d['db_type']] ?: $nullSpace;
                $record['rows'][] = [
                    "id" => $d['task_uuid'],
                    "task_uuid" => $d['task_uuid'],
                    "task_name" => $newest_data[$d['task_uuid']] ?? $d['task_name'],
                    "module_type" => $d['module_type'],
                    "sub_module_type" => $d['sub_module_type'],
                    'module_type_des' => $this->getModuleType($d['module_type'], $d['sub_module_type'], $d['dev_type']),
                    "task_type" => $d['task_type'],
                    "task_type_des" => $this->getTaskTypeDes($d['task_type']),
                    "point_num" => $d['point_num'],
                    "task_create_time" => $d['task_create_time'],
                    "total_size" => $d['total_size'] ? v1_calsize($d['total_size'], true) : self::NULL_RESULT_DES,
                    "write_size" => $d['write_size'] ? v1_calsize($d['write_size'], true) : self::NULL_RESULT_DES,
                    "deleted_flag" => !in_array($d['task_uuid'], $all_task_arr),
                    "node_uuid" => $d['real_node_uuid'] ?: $d['node_uuid'],
                    "storage_uuid" => $d['storage_uuid'],
                    "src_data_deleted_flag" => v1_parse_flag_to_bool($d['src_data_deleted_flag']),
                    "vcenter_name" => $vm_vcenter_arr[$d['vcenter_uuid']] ?? $nullSpace,
                    "sub_type" => $d['hypervisor_type'] ?: $d['db_type'] ?: 0,
                    "sub_type_des" => in_array($d['task_type'], [$TASK_TYPE['BACKUP_COPY'], $TASK_TYPE['BACKUP_COPY_FETCH'], $TASK_TYPE['ARCHIVE'], $TASK_TYPE['ARCHIVE_FETCH']]) ? $nullSpace : $sub_type_des,
                    "hasChildren" => !empty($children) ? true : false,
                    "subTaskFlag" => !empty($children) ? true : false,
                    "backup_set_id" => $d['backup_set_id'],
                    "children" => json_encode($children),
                    "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                    "user_uuid" => $d['user_uuid'],
                ];
            }
        }
        $record['total'] = count((array)$dataCount);
        return $record;
    }
    /**
     * 获取当前获取任务的最新名称
     * @param mixed $task_uuid_arr
     * @return {}
     */
    private function getTimePointNewName($task_uuid_arr)
    {
        $sql = "SELECT
                    task_uuid,
                    timepoint,
                    SUBSTRING_INDEX(GROUP_CONCAT(task_name ORDER BY timepoint DESC SEPARATOR ','), ',', 1) as latest_value
                FROM bd_backup_timepoint 
                WHERE task_uuid IN ('" . implode("','", $task_uuid_arr) . "')
                GROUP BY
	                task_uuid";
        $data = $this->dbSelect($sql, []) ?? [];
        $result = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $result[$d['task_uuid']] = $d['latest_value'];
            }
        }
        return $result;
    }
    /**
     * 获取所有vcenter信息
     * @return array
     */
    private function getVmVcenter()
    {
        $result = [];
        $sql = "SELECT nickname,vcenter_uuid FROM vm_vcenter";
        $data = $this->dbSelect($sql, []) ?? [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $result[$d['vcenter_uuid']] = $d['nickname'];
            }
        }
        return $result;
    }
    /**
     * 获取当前任务所有ID
     * @return array
     */
    private function getAllTaskIdArr($task_uuid_arr)
    {
        $sql = "SELECT task_uuid FROM bd_task WHERE task_uuid IN ('" . implode("','", $task_uuid_arr) . "')";
        $data = $this->dbSelect($sql, []) ?? [];
        $result = [];
        if (!empty($data)) {
            $result = array_column((array)$data, 'task_uuid');
        }
        return $result;
    }
    // 获取存储状态和节点信息
    public function getStorageStatus($uuids)
    {
        $allStorageData = $this->getStorageInfoByTimepoints($uuids);
        $result = [];
        foreach ($allStorageData as $storageData) {
            $result[$storageData['timepoint_uuid']] = [
                "storage_online_flag" => $storageData['storage_online_flag'],
                "node_uuid" => $storageData['node_uuid'],
            ];
        }
        return $result;
    }
    /**
     * 获取所有子任务列表
     * @return {}
     */
    private function getSubTaskList()
    {
        $sql = "SELECT bbt.task_uuid,bbt.task_name,bbt.module_type,bbt.task_type,bbt.sub_module_type,bbt.task_create_time,bbt.storage_uuid,bbt.real_node_uuid,dbt.db_type,dbt.depend_task_uuid,bbt.timepoint_uuid, COUNT(bbt.task_uuid) as point_num,SUM(bbt.total_size) AS total_size, SUM(bbt.write_size) AS write_size
                FROM bd_backup_timepoint bbt
                LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.deleted_flag = 2 AND bbt.available_flag = 1 AND bbt.backup_mode= 4 AND dbt.depend_task_uuid IS NOT NULL AND dbt.depend_task_uuid !='' GROUP BY bbt.task_uuid ORDER BY bbt.task_create_time desc";
        $data = $this->dbSelect($sql, []);
        $node = [];
        if (!is_array($data)) {
            $data = [];
        }
        // 调用公共方法获取存储在线状态
        foreach ($data as $d) {
            $node[] = [
                "id" => $d['task_uuid'],
                "checked" => false,
                "task_uuid" => $d['task_uuid'],
                "task_name" => $this->getNewTaskName($d['task_uuid']),
                "module_type" => $d['module_type'],
                "sub_module_type" => $d['sub_module_type'],
                'module_type_des' => $this->getModuleType($d['module_type'], $d['sub_module_type']),
                "task_type" => $d['task_type'],
                "task_type_des" => $this->getTaskTypeDes($d['task_type']),
                "point_num" => $d['point_num'],
                "task_create_time" => $d['task_create_time'],
                "total_size" => $d['total_size'] ? v1_calsize($d['total_size'], true) : self::NULL_RESULT_DES,
                "write_size" => $d['write_size'] ? v1_calsize($d['write_size'], true) : self::NULL_RESULT_DES,
                "deleted_flag" => false,
                "node_uuid" => $d['real_node_uuid'] ?: $d['node_uuid'],
                "storage_uuid" => $d['storage_uuid'],
                "vcenter_name" => '',
                "sub_type" => $d['db_type'] ?: 0,
                "sub_type_des" => xphp_get_config('db', 'DB_TYPE_DES')[$d['db_type']] ?: '',
                "hasChildren" => false,
                "pid" => $d['depend_task_uuid'],
                'detail' => true,
            ];
        }
        return $node;
    }
    /**
     * 修改任务名之后的时间，在使用group by 和order 不用组合时，不能查出最新点的task_name，所以需要单独查询
     * 因为group by只能卸载order by前面，导致排序是在分组之后进行，所以查不出最新任务名
     * @param mixed $task_uuid
     * @return mixed
     */
    private function getNewTaskName($task_uuid)
    {
        $sql = "SELECT task_name FROM bd_backup_timepoint WHERE task_uuid = '{$task_uuid}' ORDER BY timepoint DESC limit 1";
        $data = $this->dbSelect($sql, []);
        return $data[0]['task_name'];
    }
    /**
     * 增加过滤条件 任务和对象表格使用
     * @param mixed $sql
     * @param mixed $params
     * @return string
     */
    private function addFilterParams($sql, $params)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        // 添加模块筛选条件
        if (!empty($params['module_type'])) {
            $moduleArr = implode("','", $params['module_type']);
            $sql .= " AND bbt.module_type IN ('{$moduleArr}')";
            if (!empty($params['sub_module_type'])) {
                $subModuleArr = implode("','", $params['sub_module_type']);
                $sql .= " AND bbt.sub_module_type IN ('{$subModuleArr}')";
            }
            // 只有单独数据库复制才显示数据库复制，多模块不显示数据库复制
            $module_arr = array_map('intval', $params['module_type']);
            $module_config = array_map('intval', [$MODULE['DB_CDP']]);
            if ($module_arr != $module_config) {
                $sql .= " AND bbt.available_flag = {$FLAG['SET']}";
            }
        } else {
            $sql .= " AND bbt.available_flag = {$FLAG['SET']}";
        }
        // 查询条件-任务类型
        if (!empty($params['task_type'])) {
            $taskTypeArr = "('" . implode("','", $params['task_type']) . "')";
            $sql .= " AND bbt.task_type in" . $taskTypeArr;
        }
        // 查询条件-时间范围
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $sql .= " AND bbt.task_create_time BETWEEN '{$params['start_time']}' AND '{$params['end_time']}'";
        }
        // 查询条件-存储
        if (!empty($params['storage_uuid'])) {
            $storageArr = "('" . implode("','", $params['storage_uuid']) . "')";
            $sql .= " AND bbt.storage_uuid IN " . $storageArr;
        }
        // 区分磁盘和卷 dev_type, 1是卷 2是磁盘
        if (!empty($params['dev_type']) && in_array($MODULE['VOL_CDP'], $params['module_type'])) {
            $sql .= " AND agent.dev_type = {$params['dev_type']}";
        }
        // 获取异常链数据
        if ($params['abnormal_chain_flag']) {
            $sql .= " AND bbt.chain_status in (2,3)";
        } else {
            $sql .= " AND bbt.chain_status in (0,1)";
        }
        $sql .= $this->userLookPermission();
        return $sql;
    }
    /**
     * 权限判断
     * @return string
     */
    private function userLookPermission()
    {
        if (v1_auth_need_check_look()) {
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['data']);
            return " AND bbt.user_uuid IN ({$user_uuid_arr})";
        }
        return '';
    }
    private function getTaskTypeDes($task_type)
    {
        $TASK = xphp_get_config('task', 'TASKTYPE');
        // 副本
        if ($task_type == $TASK['BACKUP_COPY'] || $task_type == $TASK['BACKUP_COPY_FETCH']) {
            return xphp_get_lang('WEB_PLATFORM_DES_COPY');
        }
        // 归档
        if ($task_type == $TASK['ARCHIVE'] || $task_type == $TASK['ARCHIVE_FETCH']) {
            return xphp_get_lang('UI_PLATFORM_ARCHIVE');
        }
        // 复制
        if (in_array($task_type, [$TASK['VOL_CDP_REPLICATION'], $TASK['FILE_COPY'], $TASK['CDP_DB_BACKUP']])) {
            return xphp_get_lang('UI_PLATFORM_COPY');
        }
        // 定时备份
        if ($task_type == $TASK['VOL_CDP_BACKUP']) {
            return xphp_get_lang('UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC');
        }
        return xphp_get_lang('WEB_PLATFORM_DES_BACKUP');
    }
    /**
     * 获取模块类型
     * 文件和虚拟机的子模块最终需要使用文件和虚拟机的模块类型来查询
     * @param mixed $module
     * @param mixed $sub
     * @return mixed
     */
    public function getModuleType($module, $sub, $dev_type = '')
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $FS_DES = xphp_get_desc('Pf', 'SUB_MODULE_DES_FS');
        $VM_DES = xphp_get_desc('Pf', 'SUB_MODULE_DES_VM');
        $MODULE_DES = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $des = $MODULE_DES[$module];
        // 虚拟机子模块
        if ($module == $MODULE['VM']) {
            $des = $VM_DES[$sub];
        }
        // 文件子模块
        if ($module == $MODULE['FS']) {
            $des = $FS_DES[$sub];
        }
        // 区分定时模块的整机和卷
        if ($module == $MODULE['OS']) {
            if ($sub == 1) {
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP');
            } else {
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP');
            }
        }
        // 区分实时模块的整机和卷
        if ($module == $MODULE['VOL_CDP']) {
            if ($dev_type == 1) {
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP');
            } else {
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP');
            }
        }
        return $des;
    }
    /**
     * 获取以备份对象的视图表格数据
     * @param mixed $params
     * @return array
     */
    public function getItemGrid($params)
    {
        // 参数
        $offset = $params['offset'];
        $limit = $params['limit'];
        $search = $params['search'];
        $sort = $params['sort'];
        $order = $params['order'];
        // 配置信息
        $FLAG = xphp_get_config('app', 'FLAG');
        $CHAIN_STATUS = xphp_get_config('point', 'CHAIN_STATUS');
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        $DB_TYPE_DES = xphp_get_config('db', 'DB_TYPE_DES');
        $VMHYPERVISORDES = xphp_get_config('vm', 'VMHYPERVISORDES');
        $sortArr = array(
            "module_type_des" => "bbt.module_type,bbt.sub_module_type",
            "task_type_des" => "bbt.task_type",
            "data_size" => "SUM(bbt.total_size)",
            "write_size" => "SUM(bbt.write_size)",
            "point_num" => "COUNT(bbt.timepoint)",
            "last_time" => "bbt.timepoint",
            "sub_type_des" => "vbt.hypervisor_type,dbt.db_type",
            "vcenter_name" => "vcenter_name",
        );
        // 查询语句
        $sqlData = "SELECT
                        max(unix_timestamp(bbt.timepoint)) as latest_time,
                        bbt.module_type,
                        bbt.task_type,
                        bbt.task_name,
                        bbt.timepoint,
                        bbt.sub_module_type,
                        bbt.task_uuid,
                        bbt.src_data_deleted_flag,
                        bbt.timepoint_uuid,
                        SUM( bbt.total_size ) AS total_size,
                        SUM( bbt.write_size ) AS write_size,
                        COUNT( bbt.timepoint ) AS point_num,
                        bbt.user_uuid,
                        bbt.chain_status,
                        vbt.vm_name,
                        vbt.vm_uuid,
                        vbt.vcenter_uuid,
                        vbt.hypervisor_type,
                        fbt.agent_name,
                        fbt.agent_ip AS fs_ip,
                        fbt.agent_uuid,
                        fbt.agent_uuid AS fs_uuid,
                        dbt.agent_ip AS db_ip,
                        dbt.db_uuid,
                        dbt.db_name,
                        dbt.db_type,
                        dbt.agent_uuid AS db_agent_uuid,
                        dbt.cluster_name AS db_cluster_name,
                        dbt.cluster_uuid AS db_cluster_uuid,
                        dbt.dir_path AS db_path,
                        dbt.instance_name,
                        obt.agent_ip AS os_ip,
                        obt.agent_uuid AS os_uuid,
                        obt.os_name,
                        mbt.m365_timepoint_uuid AS m365_uuid,
                        mbt.organization_name,
                        mbt.organization_uuid,
                        mbt.organization_info,
                        agent.master_agent_uuid,
                        agent.master_agent_detail,
                        agent.dev_type,
                        agent.id AS backup_set_id,
                        vol.vol_uuid,
                        baa.app_name,
                        baa.app_listen_ip,
                        baa.app_type,
                        cddbi.target_agent_uuid,
                        cddbi.source_agent_uuid,
                        ba.ip AS target_app_ip,
                        cddbi.backup_info_status,
                        kbt.cluster_uuid,
                        kbt.cluster_name";
        $sql = " FROM bd_backup_timepoint bbt  LEFT JOIN vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
                LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
                LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                LEFT JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
                LEFT JOIN m365_backup_timepoint mbt ON bbt.timepoint_uuid = mbt.m365_timepoint_uuid
                LEFT JOIN kube_backup_timepoint kbt ON bbt.timepoint_uuid = kbt.timepoint_uuid
                LEFT JOIN cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
                LEFT JOIN cdp_vol_backup_vol_set vol ON agent.id = vol.backup_agent_id
                LEFT JOIN cdp_db_dr_backup_info cddbi ON bbt.timepoint_uuid = cddbi.timepoint_uuid
                LEFT JOIN bd_agent_app baa ON baa.agent_uuid = cddbi.source_agent_uuid
                LEFT JOIN bd_agent ba ON ba.agent_uuid = cddbi.source_agent_uuid
                WHERE bbt.deleted_flag = {$FLAG['UNSET']} AND bbt.import_flag = {$FLAG['UNSET']} ";
        // 数量查询
        $sqlCount = "SELECT COUNT(bbt.timepoint_uuid) AS total ";
        // 增加过滤条件
        $sql = $this->addFilterParams($sql, $params);
        // 模糊搜索备份对象名称
        if (!empty($search)) {
            $sql .= " AND (vbt.vm_name LIKE '%{$search}%' OR fbt.agent_name LIKE '%{$search}%' OR dbt.cluster_name LIKE '%{$search}%' OR dbt.agent_ip LIKE '%{$search}%'  OR dbt.dir_path LIKE '%{$search}%' OR obt.os_name LIKE '%{$search}%' OR mbt.organization_name LIKE '%{$search}%' OR kbt.cluster_name LIKE '%{$search}%' OR fbt.agent_ip  LIKE '%{$search}%' OR obt.agent_ip LIKE '%{$search}%' OR JSON_EXTRACT(agent.master_agent_detail, '$.ip') LIKE '%{$search}%')";
        }
        // 去除重复的对象 同一个对象 属于多个模块显示多次
        $sql .= "  GROUP BY bbt.module_type,bbt.task_type,fbt.agent_uuid,vbt.vm_uuid, dbt.agent_uuid, dbt.instance_name, dbt.db_name, dbt.cluster_uuid,obt.agent_uuid, JSON_EXTRACT(mbt.organization_info, '$.organization_uuid'), kbt.cluster_uuid,agent.master_agent_uuid,cddbi.source_agent_uuid ";
        if (!empty($sort) && !empty($order)) {
            if ($sort == 'item_name') {
                $sql .= " ORDER BY vbt.vm_name {$order}, fbt.agent_name {$order}, dbt.db_name {$order}, obt.os_name {$order}, mbt.organization_name {$order}";
            } else {
                $sql .= " ORDER BY {$sortArr[$sort]} {$order}";
            }
        }
        $sqlItem = $sqlData . $sql;
        if (!empty($limit)) {
            $sqlItem .= " LIMIT {$offset},{$limit}";
        }
        // 查询数据
        $data = $this->dbSelect($sqlItem);
        // 查询数量
        $dataCount = $this->dbSelect($sqlCount . $sql);
        // 获取所有vm_vcenter信息
        $vm_vcenter_arr = $this->getVmVcenter();
        // 获取返回字段
        $record = [];
        $record['rows'] = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $unifiedField = $this->getUnifiedField($d, true);
                // 组装返回数据
                $record['rows'][] = array(
                    "item_name" => $unifiedField['show_name'],
                    "title" => $unifiedField['item_name'],
                    "item_uuid" => $unifiedField['item_uuid'],
                    "db_agent_uuid" => $unifiedField['db_agent_uuid'],
                    "timepoint_uuid" => $d['timepoint_uuid'],
                    "module_type" => $d['module_type'],
                    "sub_module_type" => $d['sub_module_type'],
                    'module_type_des' => $this->getModuleType($d['module_type'], $d['sub_module_type'], $d['dev_type']),
                    "task_type" => $d['task_type'],
                    "task_type_des" => $this->getTaskTypeDes($d['task_type']),
                    "ip" => $unifiedField['ip'],
                    "data_size" => v1_calsize($d['total_size'], true),
                    "write_size" => v1_calsize($d['write_size'], true),
                    "point_num" => $d['point_num'],
                    "task_name" => $d['task_name'],
                    "src_data_deleted_flag" => v1_parse_flag_to_bool($d['src_data_deleted_flag']),
                    "last_time" => $d['timepoint'] ? $this->parseDate($d['latest_time']) : '--',
                    "vcenter_name" => $vm_vcenter_arr[$d['vcenter_uuid']] ?? $nullSpace,
                    "db_cluster_uuid" => $d['db_cluster_uuid'] ?? '',
                    "db_name" => $d['db_name'] ?? '',
                    "instance_name" => $d['instance_name'] ?? '',
                    "sub_type" => $d['hypervisor_type'] ?: $d['db_type'] ?: 0,
                    "hypervisor_type" => $d['hypervisor_type'] ?? 0,
                    "db_type" => $d['db_type'] ?? 0,
                    "sub_type_des" => $VMHYPERVISORDES[$d['hypervisor_type']] ?: $DB_TYPE_DES[$d['db_type']] ?: $nullSpace,
                    "backup_set_id" => $d['backup_set_id'] ?? '',
                    "user_uuid" => $d['user_uuid'],
                    "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                    "db_path" => $d['db_path'],
                );
            }
        }
        $record['total'] = count((array)$dataCount);
        return $record;
    }
    /**
     * 删除任务，备份对象
     * 删除需要传递所有需要删除的时间点，所以先查询出删除的所有时间带你
     * @param mixed $params
     * @return array
     */
    public function getAllPoints($params)
    {
        if ($params['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
            return $this->getRemotePoint($params);
        }
        if ($params['taskGridFlag']) {
            return $this->getTaskAllPoints($params);
        } else {
            return $this->getItemAllPoints($params);
        }
    }
    /**
     * 根据查询参数得到查询的sql语句
     * @param mixed $params
     * @param mixed $flag 是否查询所有时间点 true or false 查询完备点
     * @return string
     * 
     * 数据库查询：
     * 集群：查询集群cluster_uuid + sqlserver和saphana需要加上查询数据库名 db_name
     * 单机：查询agent_uuid + instance_name + sqlserver和saphana需要加上查询数据库名 db_name
     */
    private function getSqlWhere($params, $flag = false)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $FLAG = xphp_get_config('app', 'FLAG');
        $sql = '';
        // 参数
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $taskId = $params['task_uuid'];
        $taskType = $params['task_type'];
        $itemId = $params['item_uuid'];
        $start = $params['start_time'];
        $end = $params['end_time'];
        $search = $params['search'];
        $operationStatus = $params['operation_status'];
        $status = $params['status'];
        $storage_uuid = $params['storage_uuid'];
        $OP_STATUS = xphp_get_config('point', 'OPERATION_STATUS');
        if (!empty($taskId)) {
            if ($params['subTaskFlag'] && $module_type == $MODULE['DB']) {
                $sql .= " AND (bbt.task_uuid = '{$taskId}' OR dbt.depend_task_uuid = '{$taskId}')";
            } else {
                $sql .= " AND bbt.task_uuid = '{$taskId}' ";
            }
        }

        if (!empty($module_type)) {
            $sql .= " AND bbt.module_type = {$module_type} ";
            if (!empty($sub_module_type)) {
                $sql .= " AND bbt.sub_module_type = {$sub_module_type}";
            };
            // 只有单独数据库复制才显示数据库复制，多模块不显示数据库复制
            if ($module_type != $MODULE['DB_CDP']) {
                $sql .= " AND bbt.available_flag = {$FLAG['SET']}";
            }
        } else {
            $sql .= " AND bbt.available_flag = {$FLAG['SET']}";
        }
        // 数据库集群只查询集群uuid
        if ($module_type == $MODULE['DB']) {
            if (!empty($params['db_cluster_uuid'])) {
                $sql .= " AND dbt.cluster_uuid = '{$params['db_cluster_uuid']}'";
            } else {
                if(!$params['taskGridFlag']){
                    $sql .= " AND dbt.cluster_uuid = ''";
                }
            }
            // 数据库需要查实例名
            if (!empty($params['instance_name'])) {
                $sql .= " AND dbt.instance_name = '{$params['instance_name']}' ";
            }
            if (!empty($params['db_agent_uuid'])) {
                $sql .= " AND dbt.agent_uuid = '{$params['db_agent_uuid']}' ";
            }
            // sqlserver和hana需要查询数据库名
            if (($params['db_type'] == 1 || $params['db_type'] == 13) && !empty($params['db_name'])) {
                $sql .= " AND dbt.db_name = '{$params['db_name']}'";
            }
        } else {
            // 查询条件-对象uuid
            if (!empty($itemId)) {
                $itemIdArr = implode("','", $itemId);
                $sql .= " AND (vbt.vm_uuid IN ('{$itemIdArr}') OR fbt.agent_uuid IN ('{$itemIdArr}') OR dbt.agent_uuid IN ('{$itemIdArr}') OR obt.agent_uuid IN ('{$itemIdArr}') OR JSON_EXTRACT(mbt.organization_info, '$.organization_uuid') IN ('{$itemIdArr}') OR agent.master_agent_uuid IN ('{$itemIdArr}') OR kbt.cluster_uuid IN ('{$itemIdArr}') OR cddbi.source_agent_uuid IN ('{$itemIdArr}'))";
            }
        }
        if (!empty($params['vol_uuid'])) {
            $volIdArr = implode("','", $params['vol_uuid']);
            $sql .= " AND vol.vol_uuid IN ('{$volIdArr}')";
        }
        // 查询条件-任务类型
        if (!empty($taskType)) {
            $sql .= " AND bbt.task_type IN ('" . implode("','", $taskType) . "')";
        }
        if ($module_type == $MODULE['DB_CDP']) {
            $flag = true;
        }
        if (!$flag && !$params['abnormal_chain_flag']) {
            $sql .= " AND bbt.backup_mode in (0,1)";
        }
        // 查询条件-存储
        if (!empty($storage_uuid)) {
            $sql .= " AND bbt.storage_uuid IN ('" . implode("','", $storage_uuid) . "')";
        }
        // 查询条件-时间点范围
        if (!empty($start) && !empty($end)) {
            if ($module_type == $MODULE['VOL_CDP']) {
                $sql .= " AND vol.start_timestamp BETWEEN '{$start}' AND '{$end}' AND vol.end_timestamp BETWEEN '{$start}' AND '{$end}'";
            } else {
                $sql .= " AND bbt.timepoint BETWEEN '{$start}' AND '{$end}'";
            }
        }
        // 查询条件-搜索关键字
        if (!empty($search)) {
            $sql .= " AND (vbt.vm_name LIKE '%{$search}%' OR fbt.agent_name LIKE '%{$search}%' OR dbt.dir_path LIKE '%{$search}%' OR obt.os_name LIKE '%{$search}%' OR mbt.organization_name LIKE '%{$search}%' OR kbt.cluster_name LIKE '%{$search}%' OR fbt.agent_ip LIKE '%{$search}%' OR dbt.agent_ip LIKE '%{$search}%' OR obt.agent_ip LIKE '%{$search}%')";
        }
        // 筛选时间点正常异常状态
        if (!empty($status)) {
            // 正常状态
            if ($status == [1]) {
                $sql .= " AND bbt.operation_status = {$OP_STATUS['NO_OPERATION']} AND bsi.virus_scan_status in (0,2) AND bsi.integrity_check_status in (0,2)";
            }
            // 异常状态
            if ($status == [2]) {
                $sql .= " AND bbt.operation_status = {$OP_STATUS['NO_OPERATION']} AND ( bsi.virus_scan_status in (3,4) OR bsi.integrity_check_status = 1)";
            }
            if ($status == [1, 2]) {
                $sql .= " AND bbt.merge_status = {$OP_STATUS['NO_OPERATION']}";
            }
        }
        // 筛选时间点操作状态
        if (!empty($operationStatus)) {
            $sql .= " AND bbt.operation_status IN ('" . implode("','", $operationStatus) . "')";
        }
        // 获取异常链数据
        if ($params['abnormal_chain_flag']) {
            $sql .= " AND bbt.chain_status in (2,3)";
        } else {
            $sql .= " AND bbt.chain_status in (0,1)";
        }
        // 查询验证点
        if($params['verify_flag'] == 1){
            $sql .= " AND bbt.verify_flag = {$FLAG['SET']}";
        }
        $sql .= "  AND bbt.import_flag = {$FLAG['UNSET']}";
        $sql .= $this->userLookPermission();
        return $sql;
    }
    /**
     * 获取任务树
     * @param array $params
     * @return {}
     * 
     */
    public function getTaskTree($params)
    {
        $DB_TYPE = xphp_get_config('db', 'DB_TYPE');
        $FLAG = xphp_get_config('app', 'FLAG');
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $CHAIN_STATUS = xphp_get_config('point', 'CHAIN_STATUS');
        $TASK_TYPE = xphp_get_config('task', 'TASKTYPE');
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        $sortArr = [
            "total_size" => "SUM(bbt.total_size)",
        ];
        $copy_task_type_arr = [$TASK_TYPE['BACKUP_COPY'], $TASK_TYPE['BACKUP_COPY_FETCH'], $TASK_TYPE['ARCHIVE'], $TASK_TYPE['ARCHIVE_FETCH']];
        $sqlData = "SELECT
                        bbt.module_type,
                        bbt.task_type,
                        bbt.task_name,
                        bbt.timepoint,
                        bbt.sub_module_type,
                        bbt.task_uuid,
                        bbt.src_data_deleted_flag,
                        bbt.timepoint_uuid,
                        SUM( bbt.total_size ) AS total_size,
                        SUM( bbt.write_size ) AS write_size,
                        COUNT( bbt.timepoint ) AS point_num,
                        bbt.chain_status,
                        vbt.vm_name,
                        vbt.vm_uuid,
                        vbt.vcenter_uuid,
                        vbt.hypervisor_type,
                        vc.nickname AS vcenter_name,
                        fbt.agent_name,
                        fbt.agent_ip AS fs_ip,
                        fbt.agent_uuid AS fs_uuid,
                        dbt.agent_ip AS db_ip,
                        dbt.db_uuid,
                        dbt.db_name,
                        dbt.db_type,
                        dbt.agent_uuid AS db_agent_uuid,
                        dbt.cluster_name AS db_cluster_name,
                        dbt.cluster_uuid AS db_cluster_uuid,
                        dbt.dir_path AS db_path,
                        dbt.instance_name,
                        obt.agent_ip AS os_ip,
                        obt.agent_uuid AS os_uuid,
                        obt.os_name,
                        COUNT( obt.agent_uuid ) AS os_point_count,
                        mbt.m365_timepoint_uuid AS m365_uuid,
                        mbt.organization_name,
                        mbt.organization_uuid,
                        mbt.organization_info,
                        vol.start_timestamp,
                        vol.end_timestamp,
                        vol.backup_file_size,
                        vol.log_file_total_size,
                        vol.storage_status,
                        vol.vol_uuid,
                        vol.vol_display_name,
                        vol.is_boot,
                        vol.capacity,
                        vol.id AS vol_id,
                        agent.master_agent_uuid,
                        agent.master_agent_detail,
                        agent.storage_location,
                        agent.dev_type,
                        agent.id AS backup_set_id,
                        baa.app_name,
                        baa.app_type,
                        baa.app_uuid,
                        baa.app_listen_ip,
                        cddbi.target_agent_uuid,
                        cddbi.source_agent_uuid,
                        ba.ip AS target_app_ip,
                        cddbi.last_redo_replay_time,
                        cddbi.latest_recv_transaction_time,
                        cddbi.backup_info_status,
                        kbt.cluster_uuid,
                        kbt.cluster_name";
        $sqlCount = "SELECT COUNT(*) AS total ";
        $sql = " FROM bd_backup_timepoint bbt
            LEFT JOIN vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
            LEFT JOIN vm_vcenter vc ON vbt.vcenter_uuid = vc.vcenter_uuid
            LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
            LEFT JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
            LEFT JOIN m365_backup_timepoint mbt ON bbt.timepoint_uuid = mbt.m365_timepoint_uuid
            LEFT JOIN kube_backup_timepoint kbt ON bbt.timepoint_uuid = kbt.timepoint_uuid
            LEFT JOIN cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
            LEFT JOIN cdp_vol_backup_vol_set vol ON agent.id = vol.backup_agent_id
            LEFT JOIN cdp_db_dr_backup_info cddbi ON bbt.timepoint_uuid = cddbi.timepoint_uuid
            LEFT JOIN bd_agent_app baa ON baa.agent_uuid = cddbi.source_agent_uuid
            LEFT JOIN bd_agent ba ON ba.agent_uuid = cddbi.source_agent_uuid
            WHERE bbt.deleted_flag = {$FLAG['UNSET']} AND bbt.import_flag = {$FLAG['UNSET']} AND (dbt.depend_task_uuid IS NULL OR dbt.depend_task_uuid = '')";

        $sql .= $this->getSqlWhere($params, true);
        $sql .= " GROUP BY bbt.module_type,bbt.task_type,fbt.agent_uuid,vbt.vm_uuid,dbt.agent_uuid,dbt.instance_name,dbt.db_name,dbt.cluster_uuid,obt.agent_uuid, JSON_EXTRACT(mbt.organization_info, '$.organization_uuid'), kbt.cluster_uuid,vol.id";
        $dataCount = $this->dbSelect($sqlCount . $sql) ?? [];
        if (!empty($sort) && !empty($order)) {
            if ($sort == 'item_name') {
                $sql .= " ORDER BY vbt.vm_name {$order}, fbt.agent_name {$order}, dbt.db_name {$order}, obt.os_name {$order}, mbt.organization_name {$order}, kbt.cluster_name {$order} ";
            } else {
                $sql .= " ORDER BY {$sortArr[$sort]} {$order}";
            }
        }
        $sql .= " LIMIT {$offset},{$limit}";
        $data = $this->dbSelect($sqlData . $sql) ?? [];
        $task_arr = [];
        $cluster_instance = [];
        $cluster_node = [];
        $item_arr = [];
        $vol_arr = [];
        $instance = [];
        $node = [];
        $node['tree'] = [];
        $newest_data = $this->getTimePointNewName([$params['task_uuid']]);
        // 以数据库为备份对象的数据库类型
        $databaseObjType = [$DB_TYPE['SQLSERVER'], $DB_TYPE['SAPHANA']];
        $task_name = $params['task_name'];
        // 返回树结构
        if (!empty($data)) {
            // 处理子任务需要额外树节点显示，仅展示
            if ($params['subTaskFlag']) {
                $subTaskArr = $this->getSubTaskInfo($params['task_uuid']);
                $node['tree'] = array_merge($node['tree'], $subTaskArr);
            }
            foreach ($data as $d) {
                // 不同模块获取对象名  对象id，子类型
                $unifiedField = $this->getUnifiedField($d);
                $item_uuid = $unifiedField['item_uuid'];
                if (empty($item_uuid)) {
                    continue;
                }
                $item_name = $unifiedField['item_name'];
                $node['item_name'] = $item_name;
                $node['task_uuid'] = $d['task_uuid'];
                $node['task_name'] = $task_name;
                $node['task_type'] = $d['task_type'];
                $node['task_type_des'] = $this->getTaskTypeDes($d['task_type']);
                $node['module_type'] = $d['module_type'];
                $node['module_type_des'] = $this->getModuleType($d['module_type'], $d['sub_module_type'], $d['dev_type']);
                $node['sub_module_type'] = $d['sub_module_type'];
                $node['sub_type_des'] = $unifiedField['sub_type_des'];
                $node['sub_type'] = $unifiedField['sub_type'];
                $node['abnormal_chain_flag'] = !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false;
                $task_node_id = $d['task_uuid'];
                // 增加任务显示
                if (!in_array($task_node_id, $task_arr) && !$params['more_flag']) {
                    $task_node_pid = '';
                    array_push($task_arr, $task_node_id);
                    $node['tree'][] = [
                        "id" => $task_node_id,
                        "pId" => $task_node_pid,
                        "task_uuid" => $d['task_uuid'],
                        "name" => $newest_data[$d['task_uuid']],
                        "title" => $newest_data[$d['task_uuid']],
                        "is_parent" => true,
                        "open" => true,
                        "icon" => "./img/backup_data/backupData-task.png",
                        "type" => "task",
                        "sub_type" => $unifiedField['sub_type'],
                        "module_type" => $d['module_type'],
                        "sub_module_type" => $d['sub_module_type'],
                        "task_type" => $d['task_type'],
                        "storage_uuid" => $d['storage_uuid'],
                        "nocheck" => true,
                        "instance_name" => $d['instance_name'] ?? '',
                    ];
                }
                if ($d['module_type'] == $MODULE_TYPE['DB']) {
                    // 集群备份
                    if (!empty($d['db_cluster_uuid']) && empty($d['depend_task_uuid'])) {
                        $cluster_id = $d['task_uuid'] . '_' . $d['db_cluster_uuid'];
                        // 集群名称节点
                        // 以实例为备份对象，节点显示在实例同一级
                        if (in_array($d['db_type'], $databaseObjType)) {
                            if (!in_array($cluster_id, $cluster_instance)) {
                                $cluster_instance[] = $cluster_id;
                                $node['tree'][] = [
                                    "id" => $cluster_id,
                                    "pId" => $task_node_id,
                                    "name" => $d['db_cluster_name'],
                                    "title" => $d['db_cluster_name'],
                                    "nocheck" => true,
                                    "type" => 'display_node',
                                    "icon" => './img/platform/storage.png',
                                ];
                            }
                            $start = strpos($d['db_path'], '(');
                            $end = strrpos($d['db_path'], ')');
                            $agentStr = substr($d['db_path'], $start + 1, $end - $start - 1);
                            $agentList = explode(',', $agentStr);
                            if ($d['db_type'] == $DB_TYPE['TIDB']) {
                                $agentList = explode(', ', $agentStr);
                            }
                            // 集群所有节点展示的树节点
                            foreach ($agentList as $agentInfoStr) {
                                $instanceInfo = explode('/', $agentInfoStr);
                                $agentIp = trim($instanceInfo[0]);
                                $agentInstanceName = trim($instanceInfo[1]);
                                $agentName = $agentInstanceName . '(' . $agentIp . ')';
                                $cluster_node_id = $d['task_uuid'] . '_' . $d['db_cluster_uuid'] . '_' . $agentName;
                                if (!in_array($cluster_node_id, $cluster_node)) {
                                    $cluster_node[] = $cluster_node_id;
                                    array_unshift($node['tree'], [
                                        "id" => $cluster_node_id,
                                        "pId" => $cluster_id,
                                        "name" => $agentName,
                                        "title" => $agentName,
                                        "nocheck" => true,
                                        "type" => 'display_node',
                                        "icon" => './img/platform/storage.png',
                                    ]);
                                }
                            }
                        }
                        $cluster_item_id = $d['task_uuid'] . $d['db_cluster_uuid'] . $d['instance_name'];
                        if (in_array($d['db_type'], [$DB_TYPE['SQLSERVER'], $DB_TYPE['SAPHANA']])) {
                            $cluster_item_id = $d['task_uuid'] . $d['db_cluster_uuid'] . $d['instance_name'] . $d['db_name'];
                        };
                        // 集群备份对象 --- 实例||数据库
                        if (!in_array($cluster_item_id, $item_arr)) {
                            array_push($item_arr, $cluster_item_id);
                            $node_name = $d['total_size'] ? $unifiedField['item_name'] . "(" . v1_calsize($d['total_size'], true) . ")" : $unifiedField['item_name'];
                            if (in_array($d['task_type'], $copy_task_type_arr)) {
                                $node_name .= "(" . $unifiedField['sub_type_des'] . ")";
                            }
                            $node['tree'][] = [
                                "id" => $cluster_item_id,
                                "pId" => in_array($d['db_type'], $databaseObjType) ? $cluster_id : $task_node_id,
                                "item_uuid" => $item_uuid,
                                "name" => $node_name,
                                "title" => $node_name,
                                "is_parent" => true,
                                "open" => true,
                                "icon" => './img/platform/storage.png',
                                "type" => "item",
                                "module_type" => $d['module_type'],
                                "sub_module_type" => $d['sub_module_type'],
                                "task_type" => $d['task_type'],
                                "sub_type" => $unifiedField['sub_type'],
                                "hypervisor_type" => $d['hypervisor_type'] ?? 0,
                                "db_type" => $d['db_type'] ?? 0,
                                'task_uuid' => $d['task_uuid'],
                                "timepoint_uuid" => $d['timepoint_uuid'],
                                "storage_uuid" => $d['storage_uuid'],
                                "db_uuid" => $unifiedField['db_uuid'],
                                "db_name" => $unifiedField['db_name'],
                                "subTaskFlag" => $params['subTaskFlag'],
                                "db_cluster_uuid" => $d['db_cluster_uuid'],
                                "chkDisabled" => in_array($d['module_type'], [$MODULE_TYPE['VOL_CDP']]),
                                "instance_name" => $d['instance_name'] ?? '',
                                "virus_list" => $d['virus_list'],
                                "source_agent_uuid" => $d['source_agent_uuid'],
                                "sub_type_des" => $unifiedField['sub_type_des'],
                                "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                            ];
                            // 以数据库为备份对象，节点显示在数据库下一级
                            if (!in_array($d['db_type'], $databaseObjType)) {
                                $start = strpos($d['db_path'], '(');
                                $end = strrpos($d['db_path'], ')');
                                $agentStr = substr($d['db_path'], $start + 1, $end - $start - 1);
                                $agentList = explode(',', $agentStr);
                                if ($d['db_type'] == $DB_TYPE['TIDB']) {
                                    $agentList = explode(', ', $agentStr);
                                }
                                // 集群所有节点展示的树节点
                                foreach ($agentList as $agentInfoStr) {
                                    $instanceInfo = explode('/', $agentInfoStr);
                                    $agentIp = trim($instanceInfo[0]);
                                    $agentInstanceName = trim($instanceInfo[1]);
                                    $agentName = $agentInstanceName . '(' . $agentIp . ')';
                                    $cluster_node_id = $d['task_uuid'] . '_' . $d['db_cluster_uuid'] . '_' . $agentName;
                                    if (!in_array($cluster_node_id, $cluster_node)) {
                                        $cluster_node[] = $cluster_node_id;
                                        $node['tree'][] = [
                                            "id" => $cluster_node_id,
                                            "pId" => $cluster_item_id,
                                            "name" => $agentName,
                                            "title" => $agentName,
                                            "nocheck" => true,
                                            "type" => 0,
                                            "icon" => './img/platform/storage.png',
                                        ];
                                    }
                                }
                            }
                        }
                    } else { // 单例备份
                        $pId = $task_node_id;
                        $db_item_key = $d['db_agent_uuid'] . $d['instance_name'];
                        $id = $d['task_uuid'] . $db_item_key;
                        // 以数据库为备份对象增加实例显示
                        if ($d['db_type'] == $DB_TYPE['SQLSERVER'] && empty($d['db_cluster_uuid']) && empty($d['depend_task_uuid'])) {
                            $id = $unifiedField['sub_type'] . $d['instance_name'] . $d['task_uuid'];
                            //检查并添加INSTANCE
                            if (!in_array($id, $instance)) {
                                $instance[] = $id;
                                $node['tree'][] = array(
                                    "id" => $id,
                                    "pId" => $pId,
                                    "name" => $d['instance_name'],
                                    "open" => false,
                                    "icon" => './img/vm/host.png',
                                    "title" => $d['instance_name'],
                                    "agent_uuid" => $d['agent_uuid'],
                                    "instance_name" => $d['instance_name'] ?? '',
                                    "task_uuid" => $d['task_uuid'],
                                    "node_uuid" => $d['real_node_uuid'] ?: $d['node_uuid'],
                                    "sub_type" => $d['sub_type'],
                                    "isParent" => true,
                                    'click_show' => false,
                                    "module_type" => $d['module_type'],
                                    "sub_module_type" => $d['sub_module_type'],
                                    "copy_flag" => $d['copy_flag'],
                                    "item_uuid" => $item_uuid,
                                    "type" => 'instance',
                                    "storage_uuid" => $d["storage_uuid"],
                                    "item_name" => $item_name,
                                    "task_type" => $d['task_type'],
                                    "nocheck" => true,
                                    "sub_type_des" => $unifiedField['sub_type_des'],
                                );
                            }
                            $pId = $id;
                            $db_item_key = $d['db_agent_uuid'] . $d['instance_name'];
                            $id = $d['task_uuid'] . $db_item_key . $unifiedField['db_name'];
                        }
                        // 增加对象显示
                        if (!in_array($id, $item_arr) && empty($d['depend_task_uuid'])) {
                            array_push($item_arr, $id);
                            $node_name = $d['total_size'] ? $unifiedField['item_name'] . "(" . v1_calsize($d['total_size'], true) . ")" : $unifiedField['item_name'];
                            if (in_array($d['task_type'], $copy_task_type_arr)) {
                                $node_name .= "(" . $unifiedField['sub_type_des'] . ")";
                            }
                            $node['tree'][] = [
                                "id" => $id,
                                "pId" => $pId,
                                "item_uuid" => $item_uuid,
                                "name" => $node_name,
                                "title" => $node_name,
                                "is_parent" => true,
                                "open" => true,
                                "icon" => './img/platform/storage.png',
                                "type" => "item",
                                "module_type" => $d['module_type'],
                                "sub_module_type" => $d['sub_module_type'],
                                "task_type" => $d['task_type'],
                                "sub_type" => $unifiedField['sub_type'],
                                "hypervisor_type" => $d['hypervisor_type'] ?? 0,
                                "db_type" => $d['db_type'] ?? 0,
                                'task_uuid' => $d['task_uuid'],
                                "timepoint_uuid" => $d['timepoint_uuid'],
                                "storage_uuid" => $d['storage_uuid'],
                                "db_uuid" => $unifiedField['db_uuid'],
                                "db_name" => $unifiedField['db_name'],
                                "subTaskFlag" => $params['subTaskFlag'],
                                "db_cluster_uuid" => $d['db_cluster_uuid'],
                                "chkDisabled" => in_array($d['module_type'], [$MODULE_TYPE['VOL_CDP']]),
                                "instance_name" => $d['instance_name'] ?? '',
                                "virus_list" => $d['virus_list'],
                                "source_agent_uuid" => $d['source_agent_uuid'],
                                "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                                "sub_type_des" => $unifiedField['sub_type_des'],
                                "db_agent_uuid" => $d['db_agent_uuid'],
                            ];
                        }
                    }
                } else {
                    $itemNodeId = $d['task_uuid'] . $item_uuid;
                    // 增加对象显示
                    if ($itemNodeId && !in_array($itemNodeId, $item_arr) && empty($d['depend_task_uuid'])) {
                        array_push($item_arr, $itemNodeId);
                        $node_name = $d['total_size'] ? $unifiedField['item_name'] . "(" . v1_calsize($d['total_size'], true) . ")" : $unifiedField['item_name'];
                        if ($d['module_type'] == $MODULE_TYPE['VM']  && in_array($d['task_type'], $copy_task_type_arr)) {
                            $node_name .= "(" . $unifiedField['sub_type_des'] . ")";
                        }
                        $node['tree'][] = [
                            "id" => $itemNodeId,
                            "pId" => $task_node_id,
                            "item_uuid" => $item_uuid,
                            "name" => $node_name,
                            "title" => $node_name,
                            "is_parent" => true,
                            "open" => true,
                            'iconSkin' => $this->getIconByModule($d['module_type'], $d['sub_module_type']),
                            "type" => "item",
                            "module_type" => $d['module_type'],
                            "sub_module_type" => $d['sub_module_type'],
                            "task_type" => $d['task_type'],
                            "sub_type" => $unifiedField['sub_type'],
                            "hypervisor_type" => $d['hypervisor_type'] ?? 0,
                            "db_type" => $d['db_type'] ?? 0,
                            'task_uuid' => $d['task_uuid'],
                            "timepoint_uuid" => $d['timepoint_uuid'],
                            "storage_uuid" => $d['storage_uuid'],
                            "db_uuid" => $unifiedField['db_uuid'],
                            "db_name" => $unifiedField['db_name'],
                            "subTaskFlag" => $params['subTaskFlag'],
                            "db_cluster_uuid" => $d['db_cluster_uuid'],
                            "chkDisabled" => in_array($d['module_type'], [$MODULE_TYPE['VOL_CDP']]),
                            "instance_name" => $d['instance_name'] ?? '',
                            "virus_list" => $d['virus_list'],
                            "source_agent_uuid" => $d['source_agent_uuid'],
                            "backup_set_id" => $d['backup_set_id'],
                            "sub_type_des" => $unifiedField['sub_type_des'],
                            "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                        ];
                    }
                    // 整机实时任务和对象都需要显示树
                    if ($d['module_type'] == $MODULE_TYPE['VOL_CDP'] && !in_Array($d['vol_uuid'] . $item_uuid . $d['task_uuid'], $vol_arr)) {
                        array_push($vol_arr, $d['vol_uuid'] . $item_uuid . $d['task_uuid']);
                        $isBootStr = $d['isBoot'] == $FLAG['SET'] ? xphp_get_lang('UI_VOL_CDP_BACKUP_SET_SYSTEM_VOL') : xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DATA_VOL');
                        $node['tree'][] = [
                            "id" => $d['vol_uuid'] . $item_uuid . $d['task_uuid'],
                            "pId" => $d['task_uuid'] . $item_uuid,
                            "item_uuid" => $item_uuid,
                            "name" => $d['vol_display_name'] . "(" . $isBootStr . "，" . xphp_get_lang('UI_PUBLIC_CAPACITY') . ":" . v1_calsize($d['capacity'], true) . ")",
                            "title" => $d['vol_display_name'] . "(" . $isBootStr . "，" . xphp_get_lang('UI_PUBLIC_CAPACITY') . ":" . v1_calsize($d['capacity'], true) . ")",
                            "is_parent" => false,
                            "open" => true,
                            "icon" => "./img/vm/host.png",
                            "type" => "vol",
                            "module_type" => $d['module_type'],
                            "sub_module_type" => $d['sub_module_type'],
                            "task_type" => $d['task_type'],
                            "sub_type" => $unifiedField['sub_type'],
                            'task_uuid' => $d['task_uuid'],
                            "timepoint_uuid" => $d['timepoint_uuid'],
                            "chkDisabled" => false,
                            "storage_uuid" => $d['storage_uuid'],
                            'vol_uuid' => $d['vol_uuid'],
                            "virus_list" => $d['virus_list'],
                            "backup_set_id" => $d['backup_set_id'],
                            "sub_type_des" => $unifiedField['sub_type_des'],
                            "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                        ];
                    }
                }
            }
            if (count((array)$dataCount) > count((array)$data) + $offset) {
                $node['tree'][] = [
                    "pId" => $task_node_id,
                    "name" => xphp_get_lang('WEB_FILE_MORE'),
                    "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    "task_uuid" => $task_node_id,
                    "nocheck" => true,
                    "more" => true,
                    "offset" => $offset + $limit,
                    "limit" => $limit,       //从哪个位置开始加载
                ];
            }
        }
        return $node;
    }
    /**
     * 获取子任务信息
     * @param mixed $task_uuid
     * @return {}
     */
    private function getSubTaskInfo($task_uuid)
    {
        $node = [];
        $sql = "SELECT bbt.task_name,bbt.task_uuid FROM bd_backup_timepoint bbt,db_backup_timepoint dbt WHERE bbt.timepoint_uuid = dbt.timepoint_uuid AND dbt.depend_task_uuid = '{$task_uuid}' GROUP BY dbt.depend_task_uuid ORDER BY timepoint DESC";
        $data = $this->dbSelect($sql) ?? [];
        if (!empty($data)) {
            foreach ($data as $subTask) {
                $node[] = [
                    "id" => $subTask['task_uuid'],
                    "pId" => $task_uuid,
                    "task_uuid" => $subTask['task_uuid'],
                    "name" => $subTask['task_name'],
                    "title" => $subTask['task_name'],
                    "open" => true,
                    "icon" => "./img/backup_data/backupData-task.png",
                    "type" => "task",
                    "nocheck" => true,
                    "chkDisabled" => true,
                ];
            }
        }
        return $node;
    }
    /**
     * 获取任务下的所有时间点，以及任务对象的树形结构
     * @param mixed $params
     * @return array
     */
    private function getTaskAllPoints($params)
    {
        if ($params['storage_type'] == 8) {
            return $this->getRemotePoint($params);
        }
        $FLAG = xphp_get_config('app', 'FLAG');
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $backMode = $params['backup_mode'] ?? 1;
        $module_type = $params['module_type'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $offset = $params['offset'];
        $getAllFlag = false;
        $sortArr = [
            "vm_name" => "vbt.vm_name",
            "agent_name" => "fbt.agent_name",
            "db_name" => "dbt.db_name",
            "os_name" => "obt.os_name",
            "vol_name" => "vol.vol_name",
            "organization_name" => "mbt.organization_name",
            "timepoint" => "bbt.timepoint",
            "total_size" => "bbt.total_size",
            "write_size" => "bbt.write_size",
            "storage" => "bbt.storage_uuid",
            "end_timestamp" => "vol.end_timestamp",
            "start_timestamp" => "vol.start_timestamp",
            "backup_file_size" => "vol.backup_file_size",
            "log_file_total_size" => "vol.log_file_total_size",
            "task_name" => "bbt.task_name"
        ];
        $order = $params['order'];
        // 查询数据
        $records = [];
        $records['rows'] = [];
        $this->timepoint_arr = [];
        // 配置文件
        $backMode = $module_type == $MODULE_TYPE['VOL_CDP'] ? 0 : $backMode;
        // 查询语句
        $countChar = 'bbt.timepoint_uuid';
        if ($module_type == $MODULE_TYPE['VOL_CDP']) {
            $countChar = 'vol.vol_uuid';
        }
        $sqlCount = "SELECT COUNT({$countChar}) as total
                    FROM bd_backup_timepoint bbt " . self::POINT_LEFT_JOIN_SQL . " LEFT JOIN bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid = bsi.timepoint_uuid
                    LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                    WHERE bbt.deleted_flag = {$FLAG['UNSET']} ";
        // 查询数量
        $sqlData = self::POINT_BASE_SQL . self::POINT_LEFT_JOIN_SQL . " LEFT JOIN bd_backup_timepoint_safe_info bsi ON bbt.timepoint_uuid = bsi.timepoint_uuid
                    WHERE bbt.deleted_flag = {$FLAG['UNSET']} ";
        $sqlUnFull = $sqlData . $this->getSqlWhere($params, true) . " ORDER BY bbt.timepoint ASC";
        $sqlData .= $this->getSqlWhere($params, $getAllFlag);
        $sqlCount .= $this->getSqlWhere($params);
        if (!empty($order) && !empty($sort)) {
            $sqlData .= " ORDER BY {$sortArr[$sort]} {$order}";
        }
        if (!empty($limit)) {
            $sqlData .= " LIMIT {$offset}, {$limit}";
        }
        $dataCount = $this->dbSelect($sqlCount, []) ?? [];
        $records['total'] = $dataCount[0]['total'];
        // 查询所有任务对象相关时间点
        $dataUnFull = $this->dbSelect($sqlUnFull, []) ?? [];
        // 获取所有存储名称
        $storageName = $this->getStorageName();
        // 获取完备点
        $data = $this->dbSelect($sqlData, []) ?? [];
        // 获取时间点链关系
        $full_uuid_List = [];
        if (!empty($dataUnFull)) {
            $full_uuid_List = $this->getPointChainArray((array)$dataUnFull);
        }
        $node_name_array = $this->getRealNodeName();
        // 返回表格数据
        if (!empty($data)) {
            foreach ($data as $d) {
                $records['rows'][] = $this->getPointResult($d, $full_uuid_List, $storageName, $node_name_array, $dataUnFull);
            }
        }
        return $records;
    }
    /**
     * 获取对象的最新名称
     * @param mixed $module_type
     * @param mixed $item_uuid
     * @param mixed $agent_uuid
     * @param mixed $instance_name
     * @param mixed $db_name
     */
    private function getItemNewName($module_type, $item_uuid, $agent_uuid = '', $instance_name ='', $db_name = ''){
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        switch ($module_type) {
            case $MODULE['VM']:
            case $MODULE['PUBLIC_CLOUD']:
            case $MODULE['PRIVATE_CLOUD']:
                $sql = "SELECT
                            SUBSTRING_INDEX( GROUP_CONCAT( vbt.vm_name ORDER BY vbt.vm_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_value 
                        FROM
                            bd_backup_timepoint bbt,
                            vm_backup_timepoint vbt 
                        WHERE
                            bbt.timepoint_uuid = vbt.timepoint_uuid 
                            AND vbt.vm_uuid = '{$item_uuid}'";
                break;
            case $MODULE['FS']:
            case $MODULE['NAS']:
            case $MODULE['OBS']:
            case $MODULE['HADOOP']:
                $sql = "SELECT
                            SUBSTRING_INDEX( GROUP_CONCAT( fbt.agent_name ORDER BY fbt.fs_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_value 
                        FROM
                            bd_backup_timepoint bbt,
                            fs_backup_timepoint fbt 
                        WHERE
                            bbt.timepoint_uuid = fbt.fs_timepoint_uuid
                            AND fbt.agent_uuid = '{$item_uuid}' ";
                break;
            case $MODULE['DB']:
                $sql = "SELECT
                            SUBSTRING_INDEX( GROUP_CONCAT( dbt.instance_name ORDER BY dbt.db_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_instance_value ,
                            SUBSTRING_INDEX( GROUP_CONCAT( dbt.db_name ORDER BY dbt.db_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_db_value ,
                            SUBSTRING_INDEX( GROUP_CONCAT( dbt.cluster_name ORDER BY dbt.db_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_cluster_value
                        FROM
                            bd_backup_timepoint bbt,
                            db_backup_timepoint dbt 
                        WHERE
                            bbt.timepoint_uuid = dbt.timepoint_uuid
                            AND dbt.agent_uuid = '{$agent_uuid}' ";
                if (!empty($instance_name)) {
                    $sql .= " AND dbt.instance_name = '{$instance_name}' ";
                }
                if (!empty($db_name)) {
                    $sql .= " AND dbt.db_name = '{$db_name}' ";
                }
                break;
            case $MODULE['OS']:
                $sql = "SELECT
                            SUBSTRING_INDEX( GROUP_CONCAT( obt.os_name ORDER BY obt.os_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_value
                        FROM
                            bd_backup_timepoint bbt,
                            os_backup_timepoint obt 
                        WHERE
                            bbt.timepoint_uuid = obt.timepoint_uuid
                        AND obt.agent_uuid = '{$item_uuid}' ";
                break;
            case $MODULE['KUBERNETES']:
                $sql = "SELECT
                            SUBSTRING_INDEX( GROUP_CONCAT( kbt.cluster_name ORDER BY kbt.kube_timepoint_id DESC SEPARATOR ',' ), ',', 1 ) AS latest_instance_value
                        FROM
                            bd_backup_timepoint bbt,
                            kube_backup_timepoint kbt 
                        WHERE
                            bbt.timepoint_uuid = kbt.timepoint_uuid
                            AND kbt.cluster_uuid = '{$item_uuid}' ";
                break;
        }
        $data = $this->dbSelect($sql) ?? [];
        return $data[0];
    }
    /**
     * 不同模块的字段整理为统一字段使用
     * @param array $d
     * @param array $MODULE
     * @return array
     */
    private function getUnifiedField($d, $item_flag = false): array
    {
        $MODE_DES = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $DB_TYPE = xphp_get_config('db', 'DB_TYPE');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $item_name = '';
        $item_uuid = '';
        $sub_type = '';
        $sub_type_des = '';
        $ip = self::NULL_RESULT_DES;
        $backup_mode_des = $d['backup_mode'] ? $MODE_DES[$d['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') : '';
        $archiveLogType = [$DB_TYPE['DM'], $DB_TYPE['ORACLE'], $DB_TYPE['POSTGRE'], $DB_TYPE['KINGBASE'], $DB_TYPE['UXDB'], $DB_TYPE['HIGHGO'], $DB_TYPE['ANTDB'], $DB_TYPE['OPENGAUSS'], $DB_TYPE['VASTBASE']];
        // 以数据库为备份对象的数据库类型
        $databaseObjType = [$DB_TYPE['SQLSERVER'], $DB_TYPE['SAPHANA']];
        switch ($d['module_type']) {
            case $MODULE['VM']:
            case $MODULE['PUBLIC_CLOUD']:
            case $MODULE['PRIVATE_CLOUD']:
                $newest_info = $this->getItemNewName($d['module_type'],$d['vm_uuid']);
                $item_name = $newest_info['latest_value'] ?: $d['vm_name'];
                $item_uuid = $d['vm_uuid'];
                $sub_type = $d['hypervisor_type'];
                $sub_type_des = xphp_get_config('vm', 'VMHYPERVISORDES')[$sub_type];
                $show_name = $item_name;
                break;
            case $MODULE['FS']:
            case $MODULE['OBS']:
            case $MODULE['HADOOP']:
            case $MODULE['NAS']:
                $newest_info = $this->getItemNewName($d['module_type'],$d['fs_uuid']);
                $agent_name = $newest_info['latest_value'] ?: $d['agent_name'];
                $item_name = $agent_name . '(' . $d['fs_ip'] . ')';
                $item_uuid = $d['fs_uuid'];
                $ip = $d['fs_ip'];
                $show_name = $item_name;
                break;
            case $MODULE['DB']:
                $newest_info = $this->getItemNewName(
                    $d['module_type'],
                    $d['vm_uuid'], 
                    $d['db_agent_uuid'], 
                    $d['instance_name'], 
                    $d['db_name']);
                $db_name = $newest_info['latest_db_value'] ?: $d['db_name'];
                $instance_name = $newest_info['latest_instance_value'] ?: $d['instance_name'];
                $item_name = $db_name . '(' . $d['db_ip'] . ')';
                $db_item_key = $d['db_agent_uuid'] . $instance_name . $db_name;
                $item_uuid = $item_flag ? $db_item_key : $d['db_agent_uuid'];
                $db_agent_uuid = $d['db_agent_uuid'];
                $db_uuid = $d['db_uuid'];
                $sub_type = $d['db_type'];
                $sub_type_des = xphp_get_config('db', 'DB_TYPE_DES')[$sub_type];
                if ($d['backup_mode'] == 4 && in_array($d['db_type'], $archiveLogType)) {
                    $backup_mode_des = $MODE_DES[5] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
                }
                $ip = empty($d['db_cluster_uuid']) ? $d['db_ip'] : self::NULL_RESULT_DES;
                $show_name = $db_name . '(' . $d['db_ip'] . ')';
                if (!empty($d['db_cluster_uuid'])) {
                    $cluster_name = $newest_info['latest_cluster_value'] ?: $d['db_cluster_name'];
                    if (in_array($d['db_type'], $databaseObjType)) {
                        $show_name = $cluster_name . '/' . $d['db_name'];
                        $item_name = $db_name;
                    } else {
                        $show_name = $cluster_name;
                        $item_name = $cluster_name;
                    }
                }
                break;
            case $MODULE['OS']:
                $newest_info = $this->getItemNewName($d['module_type'],$d['os_uuid']);
                $os_name = $newest_info['latest_value'] ?: $d['os_name'];
                $item_name = $os_name . '(' . $d['os_ip'] . ')';
                $item_uuid = $d['os_uuid'];
                $ip = $d['os_ip'];
                $show_name = $item_name;
                break;
            case $MODULE['M365']:
                $organizationInfo = json_decode($d['organization_info'], true);
                $item_name = $organizationInfo['organization_name'];
                $item_uuid = $organizationInfo['organization_uuid'];
                $show_name = $item_name;
                break;
            case $MODULE['VOL_CDP']:
                $agentInfo = json_decode($d['master_agent_detail'], true);
                $item_name = $agentInfo['agent_name'];
                $item_uuid = $d['master_agent_uuid'];
                $ip = $agentInfo['ip'];
                $show_name = $item_name;
                break;
            case $MODULE['DB_CDP']:
                $item_name = $d['app_name'] . '(' . $d['target_app_ip'] . ')';
                $item_uuid = $d['source_agent_uuid'];
                $sub_type = $d['app_type'];
                $show_name = $item_name;
                break;
            case $MODULE['KUBERNETES']:
                $newest_info = $this->getItemNewName($d['module_type'],$d['cluster_uuid']);
                $item_name = $newest_info['latest_value'] ?: $d['cluster_name'];
                $item_uuid = $d['cluster_uuid'];
                $sub_type = $d['by_type'];
                $show_name = $item_name;
                break;
        }
        return [
            'item_name' => $item_name,
            'item_uuid' => $item_uuid,
            'sub_type' => $sub_type ?? 0,
            'sub_type_des' => $sub_type_des ?? '',
            'ip' => $ip ?? self::NULL_RESULT_DES,
            'backup_mode_des' => $backup_mode_des,
            'show_name' => $show_name,
            'db_uuid' => $db_uuid ?? '',
            'db_name' => $db_name ?? '',
            'db_agent_uuid' => $db_agent_uuid ?? '',
        ];
    }
    /**
     * 根据给定的数据获取完整的备份点链数组
     * 
     * 本函数的目的是区分出哪些时间点是完整备份，哪些是增量备份，并且要找出所有依赖于完整备份的增量备份时间点
     * 完整备份（full backup）是备份系统中的一种备份方式，它备份的是所有指定的数据，不遗漏任何内容
     * 增量备份（incremental backup）则只备份与上次备份相比新增或修改的数据
     * 
     * @param array $data 包含备份点信息的数组，每个备份点包括备份模式（完整或增量）、时间点UUID和依赖的时间点UUID等信息
     * @return array 返回一个数组，键和值都是时间点UUID，表示完整的备份链
     */
    private function getPointChainArray($data)
    {
        // 如果输入数据为空，则直接返回空数组
        if (empty($data)) {
            return [];
        }
        // 初始化两个数组，分别用于存储完整备份和非完整备份的时间点信息
        $full_uuid_list = [];
        $un_uuid_list = [];
        $backupModes = xphp_get_config('task', 'BACKUP_MODE');
        foreach ($data as $key => $point) {
            if ($point['backup_mode'] == $backupModes['FULL']) {
                $full_uuid_list[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $un_uuid_list[] = $point;
            }
        }
        while (!empty($un_uuid_list)) {
            $un_full_count = count($un_uuid_list);
            $un_full_count_tmp = count($un_uuid_list);
            foreach ($un_uuid_list as $key => $un_full) {
                $dependId = $un_full['depend_point_uuid'];
                foreach ($full_uuid_list as $k => $value) {
                    if ($dependId == $k) {
                        $full_uuid_list[$un_full['timepoint_uuid']] = $full_uuid_list[$k];
                        $un_uuid_list = array_splice($un_uuid_list, $key, 1);
                        $un_full_count_tmp--;
                    }
                    continue;
                }
            }

            if ($un_full_count == $un_full_count_tmp || $un_full_count_tmp == 0)
                break;
        }
        // 返回完整的备份链数组
        return $full_uuid_list;
    }
    /**
     * 获取备份点结果
     * @param array $d 备份点信息
     * @param array $full_uuid_List 完整的备份链数组
     * @param string $storageName 存储名称
     * @param array $dataUnFull 非完整备份点信息
     * @return array 备份点结果

     */
    private function getPointResult($d, $full_uuid_List, $storageName, $node_name_array, $dataUnFull = [])
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $children = [];
        // oracle数据用chain_uuid来判断同链
        if ($d['module_type'] == $MODULE['DB'] && !empty($d['chain_uuid']) && $d['db_type'] == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
            // children中保存所有子节点
            foreach ($dataUnFull as $item) {
                if ($item['chain_uuid'] == $d['chain_uuid'] && $item['backup_mode'] !== 1) {
                    if (!in_array($item['timepoint_uuid'], $this->timepoint_arr)) {
                        $this->timepoint_arr[] = $item['timepoint_uuid'];
                        $children[] = $this->buildPointNode($item, $storageName, $node_name_array, $d['timepoint_uuid']);
                    }
                }
            }
        } else {
            // children中保存所有子节点
            foreach ($dataUnFull as $item) {
                if ($full_uuid_List[$item['timepoint_uuid']] == $d['timepoint_uuid'] && $item['backup_mode'] !== 1) {
                    if (!in_array($item['timepoint_uuid'], $this->timepoint_arr)) {
                        $this->timepoint_arr[] = $item['timepoint_uuid'];
                        $children[] = $this->buildPointNode($item, $storageName, $node_name_array,  $d['timepoint_uuid']);
                    }
                }
            }
        }
        // 差备链判断
        $diff_chain_flag = false;
        if (!in_array($d['module_type'], [$MODULE['VOL_CDP'], $MODULE['DB_CDP']])) {
            foreach ($dataUnFull as $k) {
                if ($k['depend_point_uuid'] == $d['timepoint_uuid'] && ($d['module_type'] == $MODULE['FS'] || $d['module_type'] == $MODULE['NAS']) && $k['backup_mode'] == 3) {
                    $diff_chain_flag = true;
                }
            }
        }
        return $this->buildPointNode($d, $storageName, $node_name_array, '', $children, $diff_chain_flag);
    }
    /**
     * 构造子节点信息
     */
    private function buildPointNode($d, $storageName, $node_name_array, $pId = '', $children = [], $diff_chain_flag = false)
    {
        // 状态描述信息
        $DES = require APP_PATH . 'v1/description/Point.php';
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $BD_STORAGE_TYPE = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $CHAIN_STATUS = xphp_get_config('point', 'CHAIN_STATUS');
        $CHAIN_STATUS_DES = xphp_get_config('point', 'CHAIN_STATUS_DES');
        $unifiedField = $this->getUnifiedField($d);
        $point_detail = !empty($d['detail']) ? json_decode($d['detail'], true) : [];
        // 时间点处于操作状态，优先显示对应操作
        // 如果不在操作状态，判断病毒扫描，完整性，合并结果
        $status = $this->getPointStatus($d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status'], $d['merge_status']);
        $worm_info = $this->getWormExpireDays($d['worm_flag'], $d['worm_expire_date']);

        // 整机实时显示特殊字段
        if ($d['module_type'] == $MODULE['VOL_CDP']) {
            $storageStatus = $this->getBackupSetStorageStatus($d['storage_status'], $d['storage_location']);
            $encryptedStr = xphp_get_lang('UI_PUBLIC_OFF_TWO');
            if ($d['encrypted_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                $encryptedStr = xphp_get_lang('UI_PUBLIC_ON_TWO');
            }
        }
        return [
            "id" => $d['timepoint_uuid'],
            "timepoint" => $d['timepoint'],
            "backup_mode" => $d['backup_mode'],
            "backup_mode_des" => $unifiedField['backup_mode_des'],
            "module_type" => $d['module_type'],
            "sub_module_type" => $d['sub_module_type'],
            "task_type" => $d['task_type'],
            'module_type_des' => $this->getModuleType($d['module_type'], $d['sub_module_type'], $d['dev_type']),
            "task_type_des" => $this->getTaskTypeDes($d['task_type']),
            "point_num" => count($children) == 0 ? '--' : count($children),
            "total_size" => v1_calsize($d['total_size'], true),
            "write_size" => v1_calsize($d['write_size'], true),
            "storage_uuid" => $d['storage_uuid'],
            'storage' => in_array($d['storage_type'], [$BD_STORAGE_TYPE['NFS'], $BD_STORAGE_TYPE['CIFS'], $BD_STORAGE_TYPE['CLOUD']]) ? $storageName[$d['storage_uuid']]['storage_name'] . '(' . $node_name_array[$d['real_node_uuid']] . ')' : $storageName[$d['storage_uuid']]['storage_name'],
            'storage_status' => $storageName[$d['storage_uuid']]['status'],
            "remarks" => $d['remarks'] ?? '',
            "status_des" => $status['des'],
            "status_value" => $status['value'],
            "pid" => $pId,
            "hasChildren" => count($children),
            "item_uuid" => $unifiedField['item_uuid'],
            "item_name" => $unifiedField['item_name'],
            "task_name" => $d['task_name'],
            'task_uuid' => $d['task_uuid'],
            'db_uuid' => $d['db_uuid'], // 用于恢复跳转时使用
            'db_name' => $d['db_name'],
            "timepoint_uuid" => $d['timepoint_uuid'],
            "start_timestamp" => $d['module_type'] == $MODULE['DB_CDP'] ? $d['last_redo_replay_time'] : $d['start_timestamp'],
            "end_timestamp" => $d['module_type'] == $MODULE['DB_CDP'] ? $d['latest_recv_transaction_time'] : $d['end_timestamp'],
            "source_agent" => $this->getAgentInfo($d['source_agent_uuid']),
            "target_agent" => $this->getAgentInfo($d['target_agent_uuid']),
            "app_name" => $d['app_name'],
            "db_cdp_status" => $this->getDbCdpStatus($d['backup_info_status']),
            "backup_file_size" => $d['backup_file_size'] ? v1_calsize($d['backup_file_size'], true) : '0B',
            "log_file_total_size" => $d['log_file_total_size'] ? v1_calsize($d['log_file_total_size'], true) : '0B',
            "node_uuid" => $d['real_node_uuid'] ?: $d['node_uuid'],
            "sub_type" => $unifiedField['sub_type'],
            "encrypted_des" => $encryptedStr,
            "vol_storage_status" => $storageStatus,
            "importance_flag" => v1_parse_flag_to_bool($d['importance_flag']),
            "mark_info" => [
                "weekly_flag" => v1_parse_flag_to_bool($d['weekly_flag']),
                "monthly_flag" => v1_parse_flag_to_bool($d['monthly_flag']),
                "yearly_flag" => v1_parse_flag_to_bool($d['yearly_flag']),
            ],
            "vol_id" => $d['vol_id'],
            "vol_uuid" => $d['vol_uuid'],
            "storage_type" => $d['storage_type'],
            "instance_name" => $d['instance_name'] ?? '',
            'db_cluster_uuid' => $d['db_cluster_uuid'] ?? '',
            "dev_type" => $d['dev_type'] ?? '',
            "task_delete" => $d['task_delete'] ? false : true,
            "details" => [
                "timepoint_uuid" => $d['timepoint_uuid'],
                "timepoint" => $d['timepoint'],
                "operation_status" => $d['operation_status'],
                "virus_info" => [
                    "virus_scan_status" => $d['virus_scan_status'] ?? 0,
                    "virus_scan_status_des" => $d['virus_scan_status'] ? $DES['VIRUS_STATUS_DES'][$d['virus_scan_status']] : $DES['VIRUS_STATUS_DES'][0],
                    "virus_list" => $d['virus_list'],
                    "last_virus_scan_time" => $d['last_virus_scan_time'],
                ],
                "integrity_info" => [
                    "integrity_check_flag" => $d['integrity_check_flag'],
                    "integrity_check_status" => $d['integrity_check_status'],
                    "integrity_check_status_des" => $DES['INTEGRITY_STATUS_DES'][$d['integrity_check_status']],
                    "last_integrity_check_time" => $d['last_integrity_check_time'],
                ],
                "worm_info" => [
                    "worm_flag" => $d['worm_flag'],
                    "worm_expire_date" => $d['worm_expire_date'],
                    "worm_expire_days" => $worm_info['days'], // 剩余期限天数
                    "worm_expire_left" => $worm_info['left'], // 剩余期限具体时间
                    "show_worm_flag" => $d['backup_mode'] == 1 ? true : $this->showWormSetting(v1_parse_flag_to_bool($d['worm_flag']), $d['worm_expire_date']), // 完备点显示配置worm按钮，非完备点仅在未配置worm时显示，已配置不显示
                ],
                "merge_status" => $d['merge_status'],
                "merge_status_des" => $DES['MERGE_STATUS_DES'][$d['merge_status']],
            ],
            "diff_chain_flag" => $diff_chain_flag,
            "verify_flag" => v1_parse_flag_to_bool($d['verify_flag']),
            "verify_flag_des" => $d['verify_flag'] == xphp_get_config('app', 'FLAG')['SET'] ? xphp_get_lang('UI_BACKUP_DATA_LABEL_VERIFY_SET') : xphp_get_lang('UI_BACKUP_DATA_LABEL_VERIFY_UNSET'),
            "point_config" => $d['detail'],
            "encrypted_flag" => $d['encrypted_flag'] && $point_detail['password_auto_flag'] == 2 && !empty($point_detail['password']), // #25033 手动加密且有密码才显示加密图标
            "src_data_deleted_flag" => v1_parse_flag_to_bool($d['src_data_deleted_flag']),
            "chain_latest_point_flag" => false,
            "chain_uuid" => $d['chain_uuid'],
            "tape_storage_flag" => $d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'],
            "children" => json_encode($children),
            "user_uuid" => $d['user_uuid'],
            "chain_status" => $d['chain_status'],
            "abnormal_chain_flag" => !empty($d['chain_status']) ? in_array($d['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
            "abnormal_chain_des" => $CHAIN_STATUS_DES[$d['chain_status']],
            "backup_set_id" => $d['backup_set_id'],
        ];
    }
    private function getDbCdpStatus($status)
    {
        if (empty($status)) return '';
        switch ($status) {
            case 1:
                return xphp_get_lang('UI_VOL_CDP_TAKEOVER_RECOVERY');
            case 2:
                return xphp_get_lang('UI_BACKUP_DATA_LABEL_TAKEOVERED');
            case 3:
                return xphp_get_lang('UI_BACKUP_DATA_LABEL_RECOVERED');
            case 4:
                return xphp_get_lang('UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER');
        }
    }
    /**
     * 计算剩余的worm保护天数
     * @param mixed $expireDate
     * @return mixed
     */
    private function getWormExpireDays($worm_flag, $expireDate)
    {
        $result = [];
        // 未开启worm、没有过期时间返回未配置
        if (in_array($worm_flag, [0, 2]) || !$expireDate) {
            $result['days'] = xphp_get_lang('UI_BACKUP_DATA_LABEL_WORM_UNSET');
            $result['left'] = xphp_get_lang('UI_BACKUP_DATA_LABEL_WORM_UNSET');
            return $result;
        }
        $now = new DateTime(); // 当前日期
        $targetDate = new DateTime($expireDate); // 目标日期
        // 过期时间早于当前时间返回空
        if ($targetDate < $now) {
            $result['days'] = xphp_get_lang('UI_PUBLIC_EXPIRED');
            $result['left'] = xphp_get_lang('UI_PUBLIC_EXPIRED');
            return $result;
        }
        $interval = $now->diff($targetDate);
        $result['days'] = $interval->days;
        $expireLeft = '';
        if ($interval->days > 0) {
            $expireLeft .= $interval->days . xphp_get_lang('WEB_UTILS_DAY');
        }
        if ($interval->h > 0) {
            $expireLeft .= $interval->h . xphp_get_lang('WEB_UTILS_HOUR');
        }
        if ($interval->i > 0) {
            $expireLeft .= $interval->i . xphp_get_lang('WEB_UTILS_MINUTE');
        }
        $result['left'] = $expireLeft;
        return $result;
    }
    /**
     * 整机实时--获取存储的状态
     * @param mixed $status
     * @param mixed $storageLocation
     * @return array|string
     */
    private function getBackupSetStorageStatus($status, $storageLocation)
    {
        $STATUS = xphp_get_desc('Volcdp', 'VOL_STORAGE_STATUS');
        $verifyResult = "";
        switch ($status) {
            case $STATUS['VOL_CDP_VOL_STORAGE_IN_NEW']:  //新建状态
                $verifyResult = xphp_get_lang('UI_PUBLIC_ADD');
                break;
            case $STATUS['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']:  //初始同步
                $verifyResult = xphp_get_lang('UI_VOL_CDP_INIT_SYNC');
                break;
            case $STATUS['VOL_CDP_VOL_STORAGE_IN_REALTIME_SYNC']:  //实时同步
                $verifyResult = xphp_get_lang('UI_VOL_CDP_TAKEOVER_RECOVERY');
                if ($storageLocation == xphp_get_desc('Volcdp', 'RECOVERY_DATA_SOURCE')['STANDBY']) {
                    $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER');
                }
                break;
            case $STATUS['VOL_CDP_VOL_STORAGE_IN_IMAGE_MERGE']:  //镜像合并中
                $verifyResult = xphp_get_lang('UI_VOL_CDP_TAKEOVER_RECOVERY');
                if ($storageLocation == xphp_get_desc('Volcdp', 'RECOVERY_DATA_SOURCE')['STANDBY']) {
                    $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUPSET_BE_AVAILABLE_FOR_TAKEOVER');
                }
                break;
        }
        return $verifyResult;
    }
    /**
     * 获取备份对象有关的所有时间点、以及对象磁盘等的树形结构
     * @param mixed $params
     * @return array
     */
    private function getItemAllPoints($params)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        // 参数
        $module_type = $params['module_type'];
        $backMode = $params['backup_mode'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        $records = [];
        $records['rows'] = [];
        $getAllFlag = false;
        // 配置信息
        $backMode = $module_type == $MODULE_TYPE['VOL_CDP'] ? 0 : $backMode;
        $sortArr = array(
            "total_size" => "bbt.total_size",
            "write_size" => "bbt.write_size",
            "task_create_time" => "bbt.timepoint",
            "module_type" => "bbt.module_type",
            "task_type" => "bbt.task_type",
            "task_name" => "bbt.task_name",
            "timepoint" => "bbt.timepoint",
            "end_timestamp" => "vol.end_timestamp",
            "start_timestamp" => "vol.start_timestamp",
            "backup_file_size" => "vol.backup_file_size",
            "log_file_total_size" => "vol.log_file_total_size",
            "vol_name" => "vol.vol_name",
        );
        // 查询语句
        $sql = self::POINT_BASE_SQL . self::POINT_LEFT_JOIN_SQL . " LEFT JOIN bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid = bsi.timepoint_uuid
                    WHERE bbt.deleted_flag = {$FLAG['UNSET']} ";
        // 数量查询
        $countChar = 'bbt.timepoint_uuid';
        if ($module_type == $MODULE_TYPE['VOL_CDP']) {
            $countChar = 'vol.vol_uuid';
        }
        $sqlCount = "SELECT
                    COUNT({$countChar}) AS total
                    FROM bd_backup_timepoint bbt " . self::POINT_LEFT_JOIN_SQL . "  LEFT JOIN bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid = bsi.timepoint_uuid
                    LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                    WHERE bbt.deleted_flag = {$FLAG['UNSET']} ";
        $sqlUnFull = $sql . $this->getSqlWhere($params, true);
        $sql .= $this->getSqlWhere($params, $getAllFlag);
        $sqlCount .= $this->getSqlWhere($params);
        if (!empty($sort) && !empty($order)) {
            if ($sort == 'item_name') {
                $sql .= " ORDER BY vbt.vm_name {$order}, fbt.agent_name {$order}, dbt.db_name {$order}, obt.os_name {$order}, mbt.organization_name {$order}";
            } else {
                $sql .= " ORDER BY {$sortArr[$sort]} {$order}";
            }
        }
        if (!empty($limit)) {
            $sql .= " LIMIT {$offset},{$limit}";
        }
        $data = $this->dbSelect($sql, []);
        $dataUnFull = [];
        $dataCount = $this->dbSelect($sqlCount, []) ?? [];
        $records['total'] = $dataCount[0]['total'];
        $dataUnFull = $this->dbSelect($sqlUnFull, []) ?? [];
        $storageName = $this->getStorageName();
        $full_uuid_List = !empty($dataUnFull) ? $this->getPointChainArray($dataUnFull) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $node_name_array = $this->getRealNodeName();
        // 返回表格数据
        if (!empty($data)) {
            foreach ($data as $d) {
                $records['rows'][] = $this->getPointResult($d, $full_uuid_List, $storageName, $node_name_array, $dataUnFull);
            }
        }
        return $records;
    }
    /**
     * 返回时间点状态
     * 1.时间点操作状态优先显示（扫描，校验，合并等）
     * 2.时间点可用状态，如果时间点处于无操作状态，根据病毒查杀结果、完整性校验、合并成功与否来决定
     * 如果三者都处于正常状态，则时间点为正常状态，如果有一个为异常状态则时间点为异常状态
     * @param mixed $opStatus
     * @param mixed $virusStatus
     * @param mixed $integrityStatus
     * @param mixed $mergeStatus
     * @return {string} value 状态描述
     * @return {number} 1 操作中 2 异常 3 正常 用于前端生成标签颜色
     */
    private function getPointStatus($opStatus, $virusStatus, $integrityStatus, $mergeStatus)
    {
        // 无操作状态
        $noOperation = xphp_get_config('point', 'OPERATION_STATUS')['NO_OPERATION'];
        // 病毒感染状态
        $infected = xphp_get_config('point', 'VIRUS_STATUS')['INFECTED'];
        $INFECTED_PARTLY_SCAN = xphp_get_config('point', 'VIRUS_STATUS')['INFECTED_PARTLY_SCAN']; // 感染但未扫描完成
        // 数据不完整状态
        $broken = xphp_get_config('point', 'INTEGRITY_STATUS')['BROKEN'];
        // 合并失败状态
        $failed = [xphp_get_config('point', 'MERGE_STATUS')['FAILED'], xphp_get_config('point', 'MERGE_STATUS')['DEPEND_MERGE_FAILED']];
        // 异常状态
        $abnormal = xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
        // 正常状态
        $normal = xphp_get_lang('WEB_PLATFORM_DES_NORMAL');
        // 如果处于无操作状态，显示正常异常
        if ($opStatus == $noOperation) {
            // 病毒查杀、数据完整性、时间点合并状态 有一个异常就是异常
            if ($virusStatus == $infected || $virusStatus == $INFECTED_PARTLY_SCAN || $integrityStatus == $broken || in_array($mergeStatus, $failed)) {
                return [
                    'des' => $abnormal,
                    'value' => 2,
                ];
            }
            // 其他是正常状态
            return [
                'des' => $normal,
                'value' => 3,
            ];
        }
        // 如果有操作就显示操作的具体类型 操作状态优先显示
        return [
            'des' => $this->getOpStatusDes($opStatus),
            'value' => 1,
        ];
    }
    private function showWormSetting($worm_flag, $worm_expire_date)
    {
        // 未配置worm可以配置
        if (!$worm_flag) {
            return true;
        }
        // 已经配置过worm但是过期了  也可以配置
        $now = new DateTime(); // 当前日期
        $targetDate = new DateTime($worm_expire_date); // 目标日期
        // 过期时间早于当前时间返回空
        if ($targetDate < $now) {
            return true;
        }
        return false;
    }
    private function getPointTypeDes(int $backupMode, $type = 0): string
    {
        $modeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        if ($backupMode == 4) {
            $typeArr = xphp_get_config('db', 'DB_TYPE');
            if (
                in_array(
                    $type,
                    [
                        $typeArr['DM'],
                        $typeArr['ORACLE'],
                        $typeArr['POSTGRE'],
                        $typeArr['KINGBASE'],
                        $typeArr['UXDB'],
                        $typeArr['HIGHGO'],
                        $typeArr['ANTDB'],
                        $typeArr['OPENGAUSS'],
                        $typeArr['VASTBASE'],
                    ]
                )
            ) {
                //归档日志备份
                $des = $modeDes[5];
            } else {
                //日志备份
                $des = $modeDes[$backupMode];
            }
        } else {
            $des = $modeDes[$backupMode];
        }

        return $des . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
    }
    private function getStorageName()
    {
        $BD_STORAGE_TYPE = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $sql = "SELECT bsr.storage_uuid,bsr.storage_nickname, bsr.node_uuid, bsr.storage_config, bsr.storage_type, bn.ip, bn.host_name, bn.node_nickname, bsr.status, bsr.mount_flag FROM bd_storage_resource bsr, bd_node bn WHERE bn.node_uuid = bsr.node_uuid";
        $storage = [];
        $data = $this->dbSelect($sql, []);
        foreach ($data as $d) {
            $nodeAllStatus = (new Node())->getNodeAllStatus($d['node_uuid']);
            if ($d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
                $storageConfig = json_decode($d['storage_config'], true);
                $nodeName = $storageConfig['remote_ip'];
            } else {
                $nodeName = $d['host_name'] . '(' . $d['ip'] . ')';
            }
            if (in_array($d['storage_type'], [$BD_STORAGE_TYPE['NFS'], $BD_STORAGE_TYPE['CIFS'], $BD_STORAGE_TYPE['CLOUD']])) {
                $storage[$d['storage_uuid']]['storage_name'] = $d['storage_nickname'];
            } else {
                $storage[$d['storage_uuid']]['storage_name'] = $d['storage_nickname'] . "\n" . '(' . $nodeName . ')';
            }
            $flag = (new Storage())->getStorageStatus(
                $nodeAllStatus,
                intval($d['status']),
                intval($d['mount_flag']),
                $d['storage_type']
            );
            $storage[$d['storage_uuid']]['status'] = $flag;
        }
        return $storage;
    }
    /**
     * 获取删除对象或者任务下的所有时间点
     * @param mixed $params
     * @param boolean $flag 用于获取完备点关联的增备点
     * @return {}
     */
    private function getDeletePoints($params, $flag = false, $abnormal_flag = false)
    {
        $task_uuid = $params['task_uuid'];
        $item_uuid = $params['item_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $sql = "SELECT bbt.timepoint_uuid, bbt.module_type, bbt.task_type, bbt.storage_uuid, bbt.task_uuid, bbt.user_uuid, bsr.node_uuid, bsr.storage_type,bbt.real_node_uuid, vbt.hypervisor_type, bbt.user_uuid AS sub_type, dbt.db_type FROM bd_storage_resource bsr, bd_backup_timepoint bbt " . self::POINT_LEFT_JOIN_SQL . " WHERE bbt.storage_uuid = bsr.storage_uuid";

        if ($flag) {
            $sql .= " AND bbt.backup_mode in (0,1)";
        }
        if (!empty($task_uuid)) {
            $task_arr = "('" . implode("','", $task_uuid) . "')";
            $sql .= " AND bbt.task_uuid in" . $task_arr;
        }
        if (!empty($params['task_type'])) {
            $sql .= " AND bbt.task_type = {$params['task_type']}";
        }
        if (!empty($params['module_type'])) {
            $sql .= " AND bbt.module_type = {$params['module_type']}";
            if (!empty($params['sub_module_type'])) {
                $sql .= " AND bbt.sub_module_type = {$params['sub_module_type']}";
            }
        }
        // 数据库需要区分集群和单例
        if ($params['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['DB']) {
            // 集群和单例都需要查agent_uuid
            if (!empty($params['db_agent_uuid'])) {
                $sql .= " AND dbt.agent_uuid = '{$params['db_agent_uuid']}'";
            } else {
                if (!empty($item_uuid)) {
                    $sql .= " AND dbt.cluster_uuid = ''";
                }
            }
            // 集群需要查询集群uuid
            if (!empty($params['db_cluster_uuid'])) {
                $sql .= " AND dbt.cluster_uuid = '{$params['db_cluster_uuid']}'";
            }
            // 单例需要查询instance_name和db_name
            if (!empty($params['instance_name'])) {
                $sql .= " AND dbt.instance_name = '{$params['instance_name']}'";
            }
            // sqlserver和hana需要加上数据库名称
            if (($params['db_type'] == 1 || $params['db_type'] == 13) && !empty($d['db_name'])) {
                $sql .= " AND dbt.db_name = '{$params['db_name']}'";
            }
        } else {
            if (!empty($item_uuid)) {
                $sql .= " AND (vbt.vm_uuid = '{$item_uuid}' OR fbt.agent_uuid = '{$item_uuid}' OR obt.agent_uuid = '{$item_uuid}' OR JSON_EXTRACT(mbt.organization_info, '$.organization_uuid') = '{$item_uuid}' OR agent.master_agent_uuid = '{$item_uuid}' OR kbt.cluster_uuid = '{$item_uuid}')";
            }
        }
        if (!empty($storage_uuid)) {
            $sql .= " AND bbt.storage_uuid = '{$storage_uuid}'";
        }
        // 获取异常点
        if ($abnormal_flag) {
            $sql .= " AND bbt.chain_status in (2,3)";
        }
        $sql .= " GROUP BY bbt.timepoint_uuid";
        return $this->dbSelect($sql, []);
    }
    /**
     * 删除时间点，批量删除和单个时间点删除
     * 异地数据都是批量删除
     * @param mixed $params
     * @return {}
     */
    public function deletePoint($params)
    {
        // 删除单个时间点
        if ($params['batchFlag'] || !empty($params['remote_ip'])) {
            // 批量删除时间点
            return $this->deleteBatchPoint($params);
        } else {
            return $this->deleteSinglePoint($params);
        }
    }
    /**
     * 删除，批量删除和删除单个点
     * 批量删除都是按整条链删除-》直接传完备点数组
     * 删除单个点只需要传一个 timepoint_uuid
     * 
     * 虚拟机模块：
     * 页面删除一个完备点，页面需要选择删除方式：1.删除整条链 2.删除合并时间点
     * 如果删除整条链直接使用批量删除，如果删除合并直接使用删除单个点
     * 如果是删除非完备点则使用删除单个点
     * 
     * 文件模块：
     * 可以删除单个非完备点（发单个删除消息）
     * 可以批量删除多个非完备点或者完备点（发批量消息）
     * 
     * 删除单个时间点
     * 只有虚拟机和操作系统、文件模块能删除单个点非完备点（但是目前操作系统好像有问题 不能删除单个非完备点）
     * 虚拟机的合并时间点删除使用单个删除方法，删除整条链使用批量删除方法
     * @param mixed $params
     * @return string
     */
    private function deleteSinglePoint($params)
    {
        // 操作权限判断
        $sql = "select user_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['timepoint_uuid']]) ?? [];
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['data']);
        // 获取可用的节点
        $pointStatusMap = $this->getStorageStatus([$params['timepoint_uuid']]);
        $node_uuid = $pointStatusMap[$params['timepoint_uuid']]['node_uuid'];

        $opName = 'TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT';
        $operate = xphp_get_lang('WEB_TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT');
        // 删除异常点
        if ($params['abnormal_chain_flag']) {
            $opName = 'TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT';
            $operate = xphp_get_lang("WEB_TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT");
            // 删除消息
            $msg = [
                "module_type" => $params['module_type'],
                "timepoint_uuid_list" => [$params['timepoint_uuid']],
            ];
            // 删除正常点
        } else {
            // 删除消息
            $msg = [
                "task_type" => $params['task_type'],
                "module_type" => $params['module_type'],
                "timepoint_uuid" => $params['timepoint_uuid'],
                "op_user_uuid" => xphp_get_user_info()['userUuid'],
                "remote_ip" => $params['remote_ip'] ?? '',
            ];
        }
        $mbResult = $this->service()->deletePoints($node_uuid, json_encode($msg), $opName);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 删除任务的所有时间点
     * 默认批量删除，只需要传所有完备点
     * @param mixed $params
     * @param mixed $operate
     * @param mixed $opName
     * @return string
     */
    private function deletPointInTask($params, $operate, $opName)
    {
        // 参数task_uuid检测
        if (empty($params['task_uuid'])) {
            return $this->muOpResult(false, $operate, xphp_get_lang(('WEB_TIMEPOINT_WEB_DELETE_POINT_GET_TASK_FAIL')));
        }

        // 获取任务的所有完备点
        $fullPoints = $this->getDeletePoints($params, true) ?? [];
        // 未查询到时间点报错
        if (empty($fullPoints)) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TIMEPOINT_WEB_DELETE_POINT_INFO_GET_FAIL'));
        }
        $result = true;
        $timepointUuidList = array_column((array)$fullPoints, 'timepoint_uuid');
        $pointStatusMap = $this->getStorageStatus($timepointUuidList);
        // 操作权限判断
        $userArr = implode(',', array_unique(array_column((array)$fullPoints, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['data']);

        // 时间点根据节点，模块类型，任务类型进行分组
        $pfMsg = $this->groupDeletePointsParams($fullPoints, $operate, $pointStatusMap, 1);
        $user_uuid = xphp_get_user_info()['userUuid'];
        foreach ($pfMsg as $id => $node) {
            $node_uuid = $id;
            $deletMsg = [];
            $deletMsg['op_user_uuid'] = $user_uuid;
            $deletMsg['remote_ip'] = '';
            $deletMsg['backup_chain_list'] = [];
            foreach ($node as $module) {
                foreach ($module as $task) {
                    $deletMsg['backup_chain_list'][] = $task;
                }
            }
            $mbResult = $this->service()->deletePoints($node_uuid, json_encode($deletMsg), $opName);
            $result = $result && $mbResult['result'];
            $msg = $mbResult['msg'];
            //删除失败则停止
            if (!$result) {
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        }
        //返回结果到UI
        return $this->muOpResult($result, $operate, $msg);
    }
    /**
     * 删除对象的所有时间点
     * 批量删除，只需要传所有的完备点
     * @param mixed $params
     * @param mixed $operate
     * @param mixed $opName
     * @return string
     */
    private function deletPointInItem($params, $operate, $opName)
    {
        if (empty($params['item'])) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TIMEPOINT_WEB_DELETE_POINT_GET_ITEM_FAIL'));
        }
        $fullPoints = [];
        $result = true;
        foreach ($params['item'] as $item) {
            $point = $this->getDeletePoints($item, true);
            $fullPoints = [...$fullPoints, ...(array)$point];
        }
        // 未查询到时间点报错
        if (empty($fullPoints)) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TIMEPOINT_WEB_DELETE_POINT_INFO_GET_FAIL'));
        }
        $timepointUuidList = array_column((array)$fullPoints, 'timepoint_uuid');
        $pointStatusMap = $this->getStorageStatus($timepointUuidList);
        // 操作权限判断
        $userArr = implode(',', array_unique(array_column($fullPoints, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['data']);
        // 时间点根据节点，模块类型，任务类型进行分组
        $pfMsg = $this->groupDeletePointsParams($fullPoints, $operate, $pointStatusMap, 1);
        $user_uuid = xphp_get_user_info()['userUuid'];
        foreach ($pfMsg as $id => $node) {
            $node_uuid = $id;
            $deletMsg = [];
            $deletMsg['op_user_uuid'] = $user_uuid;
            $deletMsg['remote_ip'] = '';
            $deletMsg['backup_chain_list'] = [];
            foreach ($node as $module) {
                foreach ($module as $task) {
                    $deletMsg['backup_chain_list'][] = $task;
                }
            }
            $mbResult = $this->service()->deletePoints($node_uuid, json_encode($deletMsg), $opName);
            $result = $result && $mbResult['result'];
            $msg = $mbResult['msg'];
            //返回结果到UI
            if (!$result) {
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        }
        //返回结果到UI
        return $this->muOpResult($result, $operate, $msg);
    }
    /**
     * 删除勾选的时间点
     * 1.如果勾选整条则在full_timepoint_uuid_list中传完备点uuid
     * 2.如果勾选链的部分点，无论是完备点、还是增量点，将勾选的点uuid传到full_timepoint_uuid_list中
     * @param mixed $params
     * @param mixed $operate
     * @param mixed $opName
     * @return string
     */
    private function deletPointInList($params, $operate, $opName)
    {
        $fullList = $params['pointList']['fullList'];
        $unFullList = $params['pointList']['unFullList'];
        if (empty($fullList) && empty($unFullList)) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TIMEPOINT_WEB_DELETE_POINT_GET_POINT_FAIL'));
        }
        // 时间点根据节点，模块类型，任务类型进行分组
        $pfMsg = [];
        $result = true;
        if (!empty($fullList)) {
            // 操作权限判断
            $userArr = implode(',', array_unique(array_column($fullList, 'user_uuid')));
            $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['data']);

            $pointStatusMapFull = $this->getStorageStatus(array_column($fullList, 'timepoint_uuid'));
            $pfMsgFull = $this->groupDeletePointsParams($fullList, $operate, $pointStatusMapFull, 1);
            $pfMsg = array_merge($pfMsg, $pfMsgFull);
        }
        if (!empty($unFullList)) {
            // 操作权限判断
            $userArr = implode(',', array_unique(array_column($unFullList, 'user_uuid')));
            $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['data']);

            $pointStatusMapUnFull = $this->getStorageStatus(array_column($unFullList, 'timepoint_uuid'));
            $pfMsgUnFull = $this->groupDeletePointsParams($unFullList, $operate, $pointStatusMapUnFull, 2);
            $pfMsg = array_merge($pfMsg, $pfMsgUnFull);
        }
        $user_uuid = xphp_get_user_info()['userUuid'];
        $msg['remote_ip'] = $fullList[0]['remote_ip'] ?? '';
        foreach ($pfMsg as $id => $node) {
            $node_uuid = $id;
            $deletMsg = [];
            $deletMsg['op_user_uuid'] = $user_uuid;
            $deletMsg['remote_ip'] = $fullList[0]['remote_ip'] ?? '';
            $deletMsg['backup_chain_list'] = [];
            foreach ($node as $module) {
                foreach ($module as $task) {
                    $deletMsg['backup_chain_list'][] = $task;
                }
            }
            $mbResult = $this->service()->deletePoints($node_uuid, json_encode($deletMsg), $opName);
            $result = $result && $mbResult['result'];
            $msg = $mbResult['msg'];
            //返回结果到UI
            if (!$result) {
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        }
        //返回结果到UI
        return $this->muOpResult($result, $operate, $msg);
    }

    /**
     * 批量删除时间点
     * 删除整个任务或者整个备份对象的所有时间点，只需要传所有的完备点
     * 虚拟机的合并时间点删除使用单个删除方法，删除整条链使用批量删除方法
     * 
     * 文件删除
     * 1.删除任务或者对象，发批量删除消息，只传完备点
     * 2.时间点列表删除，批量删除按钮，选多少个就发多少个
     * 3.时间点操作删除
     *  a.触发删除选项：删除整条链->批量删除,发批量删除消息、只发完备点；合并时间点删除-> 发单个删除消息
     *  b.不触发删除选项：删除非完备点->发单个删除消息
     * 4.文件数据只能在停止的任务或者任务不存在时删除
     * 其他模块删除：
     * 1.删除任务或者对象：发批量删除消息，传完备点
     * 2.列表不允许单独勾选非完备点，批量删除也是发完备点
     * 3.操作删除触发：
     *  a.触发删除选项：删除整条链->批量删除,发批量删除消息、只发完备点；合并时间点删除-> 发单个删除消息
     *  b.不触发删除选项：删除非完备点->发单个删除消息
     * 请求消息
     * {
     *    "backup_chain_list": [{
     *        timepoint_uuid: 'xxxx',
     *        module_type: 'xxxx',
     *        task_type: 'xxxx',
     *        task_uuid: 'xxxx',
     *    }],
     *   "op_user_uuid": "xxxx",
     *   "remote_ip": "xxxx"
     * }
     * @param mixed $params
     * @return {}
     */
    private function deleteBatchPoint($params)
    {
        $opName = 'TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT_IN_BATCH';
        $operate = xphp_get_lang('WEB_TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT_IN_BATCH');
        $type = $params['type'];
        // 删除异常点
        if ($params['abnormal_chain_flag']) {
            return $this->deleteAbnormalPoint($params);
            // 删除正常点
        } else {
            // 根据不同类型获取所有需要删除时间点
            switch ($type) {
                case 'task':
                    // 任务直接查询任务下所有时间点
                    $this->deletPointInTask($params, $operate, $opName);
                    break;
                case 'item':
                    // 对象需要查询删除对象的模块类型和任务类型
                    $this->deletPointInItem($params, $operate, $opName);
                    break;
                case 'point':
                case 'vol_point':
                    $this->deletPointInList($params, $operate, $opName);
                    break;
            }
        }
    }
    /**
     * 删除异常时间点
     * @param mixed $params
     * @return string
     */
    private function deleteAbnormalPoint($params)
    {
        // 获取任务的所有完备点
        $abnormalData = $this->getDeletePoints($params, false, true) ?? [];
        $opName = 'TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT';
        $operate = xphp_get_lang('WEB_TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT');
        $pointStatusMap = $this->getStorageStatus(array_column($abnormalData, 'timepoint_uuid'));
        // 组装删除消息
        $pfMsg = $this->groupDeletePointsParams($abnormalData, $operate, $pointStatusMap, 2, true);
        // 操作权限判断
        $userArr = implode(',', array_unique(array_column($abnormalData, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['data']);
        $result = true;
        $mbResult = [];
        $msg = [];
        // 删除消息
        foreach ($pfMsg as $id => $node) {
            $node_uuid = $id;
            $msg['timepoint_uuid_list'] = [];
            foreach ($node as $key => $module) {
                $msg['module_type'] = $key;
                foreach ($module as $task_type) {
                    $msg['timepoint_uuid_list'] = array_merge($msg['timepoint_uuid_list'], $task_type['timepoint_uuid_list']);
                }
                $mbResult = $this->service()->deletePoints($node_uuid, json_encode($msg), $opName);
                $result = $result && $mbResult['result'];
                $msg = $mbResult['msg'];
                //返回结果到UI
                if (!$result) {
                    return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
                }
            }
        }
        return $this->muOpResult($result, $operate, $msg);
    }
    /**
     * 时间点根据节点，模块类型，任务类型进行分组
     * @param mixed $point_array
     * @param mixed $operate
     * @param mixed $pointStatusMap
     * @return array<array>|string
     * {
     *    "backup_chain_list": {}
     *        "full_timepoint_uuid_list":[ {
     *            timepoint_uuid: 'xxxx',
     *            module_type: 'xxxx',
     *            task_type: 'xxxx',
     *            task_uuid: 'xxxx',
     *        }],
     *        "timepoint_uuid_list":[ {
     *            timepoint_uuid: 'xxxx',
     *            module_type: 'xxxx',
     *            task_type: 'xxxx',
     *            task_uuid: 'xxxx',
     *        }],
     *    },
     *   "op_user_uuid": "xxxx",
     *   "remote_ip": "xxxx"
     * }
     * 
     * 
     */
    private function groupDeletePointsParams($point_array, $operate, $pointStatusMap, $type, $abnormal_flag = false)
    {
        // 时间点根据节点，模块类型，任务类型进行分组
        $pfMsg = [];
        foreach ($point_array as $d) {
            // 有磁带下的时间点无法删除
            if (xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'] == $d['storage_type'] && !$abnormal_flag) {
                return $this->muOpResult(false, $operate, xphp_get_lang('UI_TAPE_DELETE_BACKUP_POINT_TIPS'));
            }
            $node_uuid = $pointStatusMap[$d['timepoint_uuid']]['node_uuid'];
            if (!isset($pfMsg[$node_uuid])) {
                $pfMsg[$node_uuid] = [];
            }
            // 区分模块
            if (!isset($pfMsg[$node_uuid][$d['module_type']])) {
                $pfMsg[$node_uuid][$d['module_type']] = [];
            }
            // 区分任务类型
            if (!isset($pfMsg[$node_uuid][$d['module_type']][$d['task_type']])) {
                $pfMsg[$node_uuid][$d['module_type']][$d['task_type']] = [];
            }
            // 按类型分组时间点 1 完备点，2 非完备点
            if ($type == 1) {
                // 同模块且同任务类型时间点一起删除
                if (!isset($pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['full_timepoint_uuid_list'])) {
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['full_timepoint_uuid_list'] = [];
                }
                // 只传完备点
                if (!in_array($d['timepoint_uuid'], $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['full_timepoint_uuid_list'])) {
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['full_timepoint_uuid_list'][] = $d['timepoint_uuid'];
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['timepoint_uuid_list'] = [];
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['task_type'] = $d['task_type'];
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['module_type'] = $d['module_type'];
                }
            } else {
                // 同模块且同任务类型时间点一起删除
                if (!isset($pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['timepoint_uuid_list'])) {
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['timepoint_uuid_list'] = [];
                }
                // 只传非完备点
                if (!in_array($d['timepoint_uuid'], $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['timepoint_uuid_list'])) {
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['full_timepoint_uuid_list'] = [];
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['timepoint_uuid_list'][] = $d['timepoint_uuid'];
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['task_type'] = $d['task_type'];
                    $pfMsg[$node_uuid][$d['module_type']][$d['task_type']]['module_type'] = $d['module_type'];
                }
            }
        };
        return $pfMsg;
    }
    /**
     * 获取备份系统的主节点
     * @return mixed
     */
    private function getLocalNodeUUID()
    {
        $sql = "SELECT node_uuid FROM bd_node WHERE node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }
    /**
     * 获取时间点详情显示信息-路径
     * @param mixed $params
     * @return array
     */
    public function getPointDetailsInfo($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $VERIFY_FUNC_STATUS = xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS');
        $CHAIN_STATUS = xphp_get_config('point', 'CHAIN_STATUS');
        $CHAIN_STATUS_DES = xphp_get_config('point', 'CHAIN_STATUS_DES');
        // 状态描述信息
        $DES = require APP_PATH . 'v1/description/Point.php';
        $return = [];
        $dirPath = [];
        $verify_info = [];

        $sql = "SELECT
                    ssr.sr_task_uuid,
                        bht.task_uuid,
                        bbt.timepoint,
                        bbt.timepoint_uuid,
                        bbt.module_type,
                        bbt.sub_module_type,
                        bbt.backup_mode,
                        bsr.storage_uuid,
                        bsr.storage_type,
                        bsr.storage_nickname,
                        bsr.node_uuid,
                        bbt.real_node_uuid,
                        bsr.storage_config,
                        bbt.operation_status,
                        bbt.merge_status,
                        bbt.worm_flag,
                        bbt.integrity_check_flag,
                        bbt.verify_flag,
                        bbt.verify_report_uuid,
                        bbtsi.worm_expire_date,
                        bbtsi.virus_scan_status,
                        bbtsi.last_virus_scan_time,
                        bbtsi.integrity_check_status,
                        bbtsi.last_integrity_check_time,
                        ssr.extension_info,
                        bht.finish_time,
                        bbtsi.virus_list,
                        bbt.chain_status 
                    FROM
                        bd_backup_timepoint bbt
                        LEFT JOIN bd_backup_timepoint_safe_info bbtsi ON bbt.timepoint_uuid = bbtsi.timepoint_uuid
                        LEFT JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                        LEFT JOIN sr_surebackup_report ssr ON ssr.timepoint_uuid = bbt.timepoint_uuid
                        LEFT JOIN bd_history_task bht ON bht.task_uuid = ssr.sr_task_uuid 
                    WHERE
                        bbt.timepoint_uuid = '{$params['timepoint_uuid']}' ORDER BY ssr.end_time DESC";
        $data = $this->dbSelect($sql, []) ?? [];
        if (!empty($data)) {
            $data = $data[0];
            $worm_info = $this->getWormExpireDays($data['worm_flag'], $data['worm_expire_date']);
            if (!empty($data['extension_info'])) {
                $extension_info = json_decode($data['extension_info'], true);
                $verify_info = [
                    'ping_status' => intval($extension_info['ping_test_status']),
                    'ping_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['ping_test_status'])],
                    'heartbeat_status' => intval($extension_info['heartbeat_status']),
                    'heartbeat_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['heartbeat_status'])],
                    'screen_status' => intval($extension_info['print_screen_status']),
                    'screen_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['print_screen_status'])],
                    'vir_det_kill_status' => intval($extension_info['vir_det_kill_status']),
                    'vir_det_kill_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['vir_det_kill_status'])],
                    'integrity_check_status' => intval($extension_info['integrity_check_status']),
                    'integrity_check_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['integrity_check_status'])],
                ];
            }
            $return['detail'] = [
                "timepoint" => $data['timepoint'] . " (" . (new JobInfo())->getTimepointTypeDes($data['backup_mode']) . ")",
                "timepoint_uuid" => $data['timepoint_uuid'],
                "operation_status" => $data['operation_status'],
                "verify_flag" => v1_parse_flag_to_bool($data['verify_flag']),
                "last_verify_time" => $data['finish_time'] ?? '--',
                "storage_uuid" => $data['storage_uuid'],
                'storage_nickname' => $data['storage_nickname'],  // 存储别名
                'storage_type' => $data['storage_type'],  // 存储类型
                'node_uuid' => $data['real_node_uuid'] ?: $data['node_uuid'],  // 节点uuid
                'storage_mount_point_list' => (new Storage())->getNasMountPointList($data['storage_uuid'], $data['storage_type']),  // 挂载点列表
                "module_type" => $data['module_type'],
                "sub_module_type" => $data['sub_module_type'],
                "virus_info" => [
                    "virus_scan_status" => $data['virus_scan_status'] ?? 0,
                    "virus_scan_status_des" => !empty($data['virus_scan_status']) ? $DES['VIRUS_STATUS_DES'][$data['virus_scan_status']] : $DES['VIRUS_STATUS_DES'][0],
                    "virus_list" => $data['virus_list'],
                    "last_virus_scan_time" => $data['last_virus_scan_time'],
                ],
                "integrity_info" => [
                    "integrity_check_flag" => $data['integrity_check_flag'],
                    "integrity_check_status" => $data['integrity_check_status'],
                    "integrity_check_status_des" => $DES['INTEGRITY_STATUS_DES'][$data['integrity_check_status']],
                    "last_integrity_check_time" => $data['last_integrity_check_time'],
                ],
                "worm_info" => [
                    "worm_flag" => $data['worm_flag'],
                    "worm_expire_date" => $data['worm_expire_date'],
                    "worm_expire_days" => $worm_info['days'],
                    "worm_expire_left" => $worm_info['left'],
                    "show_worm_flag" => $this->showWormSetting(v1_parse_flag_to_bool($data['worm_flag']), $data['worm_expire_date']), // 完备点显示配置worm按钮，非完备点仅在未配置worm时显示，已配置不显示
                ],
                "merge_status" => $data['merge_status'],
                "merge_status_des" => $DES['MERGE_STATUS_DES'][$data['merge_status']],
                "tape_storage_flag" => $data['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'],
                "verify_info" => $verify_info,
                "abnormal_chain_flag" => !empty($data['chain_status']) ? in_array($data['chain_status'], [$CHAIN_STATUS['MISS_DEPEND'], $CHAIN_STATUS['DISORDER']]) : false,
                "abnormal_chain_des" => $CHAIN_STATUS_DES[$data['chain_status']],
            ];
        }
        switch ($data['module_type']) {
            case $MODULE['VM']:
            case $MODULE['PUBLIC_CLOUD']:
            case $MODULE['PRIVATE_CLOUD']:
                $sql = "SELECT vbt.dir_path FROM bd_backup_timepoint bbt, vm_backup_timepoint vbt WHERE vbt.timepoint_uuid = bbt.timepoint_uuid AND bbt.timepoint_uuid = '{$params['timepoint_uuid']}'";
                $data = $this->dbSelect($sql, []) ?? [];
                $dirPath = $data[0]['dir_path'];
                break;
            case $MODULE['FS']:
            case $MODULE['NAS']:
            case $MODULE['OBS']:
            case $MODULE['HADOOP']:
                $sql = "SELECT fbt.backup_path_list FROM bd_backup_timepoint bbt, fs_backup_timepoint fbt WHERE bbt.timepoint_uuid = fbt.fs_timepoint_uuid AND bbt.timepoint_uuid = '{$params['timepoint_uuid']}'";
                $data = $this->dbSelect($sql, []) ?? [];
                $fsList = json_decode($data[0]['backup_path_list'], true);
                foreach ($fsList as $each_path) {
                    if (!in_array($each_path['backup_path'], $dirPath)) {
                        array_push($dirPath, $each_path['backup_path']);
                    }
                }
                break;
            case $MODULE['DB']:
                $sql = "SELECT dbt.dir_path FROM bd_backup_timepoint bbt, db_backup_timepoint dbt WHERE dbt.timepoint_uuid = bbt.timepoint_uuid AND bbt.timepoint_uuid = '{$params['timepoint_uuid']}'";
                $data = $this->dbSelect($sql, []) ?? [];
                $dirPath = $data[0]['dir_path'];
                break;
            case $MODULE['M365']:
                $sql = "SELECT mbt.user_config,mbt.organization_name FROM m365_backup_timepoint mbt, bd_backup_timepoint bbt WHERE bbt.timepoint_uuid = mbt.m365_timepoint_uuid AND bbt.timepoint_uuid = '{$params['timepoint_uuid']}'";
                $data = $this->dbSelect($sql, []) ?? [];
                $m365Object = json_decode($data[0]['user_config'], true);
                $infoList = $m365Object['backup_m365_object_info_list'];
                // 备份整个组织只显示组织名
                if ($infoList[0]['backup_object_type'] == '10000') {
                    array_push($dirPath, $data[0]['organization_name']);
                } else {
                    foreach ($infoList as $m365) {
                        array_push($dirPath, explode('@', $m365['backup_object_mail'])[0] . "(" . $m365['backup_object_mail'] . ')');
                    }
                }
                break;
            case $MODULE['OS']:
                $sql = "SELECT obt.os_config, obt.os_type FROM bd_backup_timepoint bbt, os_backup_timepoint obt WHERE bbt.timepoint_uuid = '{$params['timepoint_uuid']}' AND obt.timepoint_uuid = bbt.timepoint_uuid";
                $data = $this->dbSelect($sql, []) ?? [];
                if (!empty($data)) {
                    $dirPath = $this->getPartitionInfo($data[0]['os_config'], $data[0]['os_type']);
                }
                break;
            case $MODULE['KUBERNETES']:
                $sql = "SELECT kbt.meta, kbt.by_type, kbt.cluster_name, bbt.task_uuid FROM bd_backup_timepoint bbt, kube_backup_timepoint kbt WHERE bbt.timepoint_uuid = '{$params['timepoint_uuid']}' AND kbt.timepoint_uuid = bbt.timepoint_uuid";
                $data = $this->dbSelect($sql, []) ?? [];
                if (!empty($data)) {
                    $dirPath = $this->getK8sAppionInfo($data[0]['meta'], $data[0]['by_type'], $data[0]['cluster_name'], $data[0]['task_uuid']);
                }
                break;
        }
        $return['dir_path'] = is_array($dirPath) ? json_encode($dirPath) : $dirPath;
        return $return;
    }
    /**
     * 获取k8s集群应用信息
     * @param mixed $config
     * @param mixed $by_type
     * @param mixed $cluster_name
     * @return array
     */
    private function getK8sAppionInfo($config, $by_type, $cluster_name, $task_uuid)
    {
        $info = [];
        if (empty($config)) {
            return $info;
        }
        $data = json_decode($config, true);
        $app_arr = [];
        $name_arr = [];
        // 集群根节点
        $info[] = [
            "id" => $cluster_name,
            "name" => $cluster_name,
            "pId" => '',
            "title" => $cluster_name,
            "nocheck" => true,
            "isParent" => true,
            "chkDisabled" => false,
            "dev_name" => $cluster_name,
            'iconSkin' => "ztree_namespace",
        ];
        foreach ($data['resources'] as $each_config) {
            if (empty($each_config['name'])) continue;
            // 命名空间
            if (in_array($each_config['type'], [1, 5])) {
                $app_icon = 'ztree_namespace';
                if ($by_type == 1) {
                    $app_icon = 'ztree_app';
                }
                $id = $task_uuid . $each_config['name'];
                if (!in_array($id, $name_arr)) {
                    $name_arr[] = $id;
                    $info[] = [
                        "id" => $id,
                        "name" => $each_config['name'],
                        "pId" => $cluster_name,
                        "title" => $each_config['namespace'],
                        "nocheck" => true,
                        "dev_name" => $each_config['namespace'],
                        'iconSkin' => $app_icon,
                    ];
                }
            } else if ($each_config['type'] == 3) {
                // 应用
                if (!in_array($each_config['name'], $app_arr)) {
                    $app_arr[] = $each_config['name'];
                    $info[] = [
                        "id" => $each_config['name'],
                        "name" => $each_config['name'],
                        "pId" => $task_uuid . $each_config['namespace'],
                        "title" => $each_config['name'],
                        "nocheck" => true,
                        "dev_name" => $each_config['name'],
                        'iconSkin' => "ztree_group",
                    ];
                }
            }
        }
        return $info;
    }


    /**
     * 获取整机模块分区信息显示
     * @param mixed $config
     * @return array
     */
    private function getPartitionInfo($config, $os_type)
    {
        $info = [];
        if (empty($config)) {
            return $info;
        }
        $os_config = json_decode($config, true);
        // 1.按卷备份
        if (!empty($os_config['volumes'])) {
            $info = array_column($os_config['volumes'], 'display_name');
            return $info;
        }
        // 2.按磁盘备份
        //获取备份磁盘信息
        $backup_info_list_params1 = [
            'all_disk_list' => $os_config['all_disk_list'],
            'exclude_device_list' => $os_config['exclude_device_list'],
        ];
        $backup_info_list_params2 = [
            'nocheck' => true,
            'recover_type' => true,
            'origin_os_type' =>  $os_type == 1 ? 'Windows' :  'Linux',
        ];
        $all_device_node = (new MachineOsBackup())->getBackupDevices($backup_info_list_params1, $backup_info_list_params2);
        return $all_device_node;
    }
    // 添加备注信息
    public function updateRemarks($params)
    {
        // 操作权限判断
        $sql = "select user_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['time_point_uuid']]) ?? [];
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['data']);

        $mbResult = $this->service()->updateRemarks($params) ?? [];
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            $this->muOpResult($result, xphp_get_lang('UI_BACKUP_DATA_POINT_OPERATION_REMARK'), $msg);
        } else {
            $this->muOpResult($result, xphp_get_lang('UI_BACKUP_DATA_POINT_OPERATION_REMARK'), $msg, $mbResult['errorCode']);
        }
    }
    // 添加永久标记
    public function addStar($params)
    {
        // 操作权限判断
        $sql = "select user_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['timepoint_uuid']]) ?? [];
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['data']);

        $mbResult = $this->service()->addStar($params) ?? [];
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            $this->muOpResult($result, xphp_get_lang('UI_COPY_DATA_SET_FOREVER_MASK'), $msg);
        } else {
            $this->muOpResult($result,  xphp_get_lang('UI_COPY_DATA_SET_FOREVER_MASK'), $msg, '', $mbResult['errorCode']);
        }
    }
    // 删除永久标记
    public function deleteStar($params)
    {
        // 操作权限判断
        $sql = "select user_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['timepoint_uuid']]) ?? [];
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['data']);

        $mbResult = $this->service()->deleteStar($params) ?? [];
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            $this->muOpResult($result, xphp_get_lang('UI_COPY_DATA_DELETE_FOREVER_MASK'), $msg);
        } else {
            $this->muOpResult($result, xphp_get_lang('UI_COPY_DATA_DELETE_FOREVER_MASK'), $msg, '', $mbResult['errorCode']);
        }
    }
    // 设置gfs标记
    public function setGfsMark($params)
    {
        // 操作权限判断
        $sql = "select user_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['timepoint_uuid']]) ?? [];
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['data']);

        $mbResult = $this->service()->setGfsMark($params) ?? [];
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            $this->muOpResult($result, xphp_get_lang('WEB_TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_GFS_FLAG'), $msg);
        } else {
            $this->muOpResult($result, xphp_get_lang('WEB_TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_GFS_FLAG'), $msg, '', $mbResult['errorCode']);
        }
    }
    // 获取异地副本数据任务表格-异地只有副本数据
    public function getRemoteGrid($params)
    {
        $storage_uuid = $params['storage_uuid'];
        $remote_ip = $params['remote_ip'];
        $module_type = $params['module_type'] ?? -1;
        $sub_module_type = $params['sub_module_type'] ?? -1;
        $opName = 'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT';
        $node_uuid = $this->getLocalNodeUUID();
        $records = [];
        $records['rows'] = [];
        $records['total'] = 0;
        if (in_array($module_type, [xphp_get_config('module', 'MODULE_TYPE')['DB_CDP'], xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP']])) {
            return $records;
        };
        //得到所有的任务
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $msg = [
            'storage_uuid' => $storage_uuid,
            'module_type' => $module_type,
            'sub_module_type' => $sub_module_type,
            'tree_name_type' => 1,
            'parent_tree_uuid' => '',
            'offset' => $params['offset'],
            'limit' => $params['limit'] ?? 9999,
        ];
        $countMsg = [
            'storage_uuid' => $storage_uuid,
            'module_type' => $module_type,
            'sub_module_type' => $sub_module_type,
            'tree_name_type' => 1,
            'parent_tree_uuid' => '',
            'offset' => 0,
            'limit' => 99999,
        ];
        $data = $this->service()->getRemoteTree($node_uuid, 0, $opName, $msg);
        // 缓存异地存储的任务数量
        if (empty(getCache($storage_uuid . $module_type))) {
            $count = $this->service()->getRemoteTree($node_uuid, 0, $opName, $countMsg);
            // 设置session变量
            setCache($storage_uuid . $module_type, $count['msg'] ? count($count['msg']) : 0);
        }
        $taskList = $data['msg'];
        if (!empty($taskList)) {
            foreach ($taskList as $d) {
                // 任务是否已删除
                $taskAvailable = in_array($d['tree_uuid'], $currentTaskUUID);
                $records['rows'][] = array(
                    "id" => $d['tree_uuid'],
                    "task_uuid" => $d['tree_uuid'],
                    "task_name" => $d['tree_name'],
                    "module_type" => $d['module_type'],
                    "sub_module_type" => $d['sub_module_type'],
                    'module_type_des' => $this->getModuleType($d['module_type'], $d['sub_module_type']),
                    "task_type" => 17,
                    "task_type_des" => xphp_get_lang('WEB_PLATFORM_DES_COPY'),
                    "point_num" => $d['timepoint_count'],
                    "task_create_time" => $d['task_create_time'],
                    "total_size" => $d['sum_total_size'] ? v1_calsize($d['sum_total_size'], true) : 0,
                    "write_size" => $d['sum_write_size'] ? v1_calsize($d['sum_write_size'], true) : 0,
                    "deleted_flag" => $taskAvailable,
                    "node_uuid" => $node_uuid,
                    "storage_uuid" => $storage_uuid,
                    "vcenter_name" => '',
                    "sub_type" => '',
                    "sub_type_des" => '',
                    "storage_type" => 8,
                    "remote_flag" => true,
                    "remote_ip" => $remote_ip,
                );
            }
        }
        $records['total'] = empty($taskList) ? 0 : getCache($storage_uuid . $module_type);
        return $records;
    }
    // 获取异地副本数据任务详情主机树
    public function getRemoteItem($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $parent_tree_uuid = $params['parent_tree_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $remote_ip = $params['remote_ip'];
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $task_name = $params['task_name'];
        $opName = 'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT';
        $node_uuid = $this->getLocalNodeUUID();
        $item_arr = array();
        $instanceArray = array();
        $msg = array(
            'storage_uuid' => $storage_uuid,
            'tree_name_type' => 2,
            'parent_tree_uuid' => $parent_tree_uuid,
            'module_type' => $params['module_type'],
            'sub_module_type' => $params['sub_module_type'],
        );
        $data = $this->service()->getRemoteData($node_uuid, 0, $opName, $msg);
        $taskList = $data['msg'];
        if (!empty($taskList)) {
            $node['tree'][] = array(
                "id" => $parent_tree_uuid,
                "task_uuid" => $parent_tree_uuid,
                "name" => $task_name,
                "title" => $task_name,
                "is_parent" => true,
                "open" => true,
                "icon" => "./img/backup_data/backupData-task.png",
                "pId" => 0,
                "type" => "task",
                "module_type" => $module_type,
                "task_type" => 17,
                "nocheck" => true,
            );
            $node['task_uuid'] = $parent_tree_uuid;
            $node['task_name'] = $task_name;
            $node['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'];
            $node['task_type_des'] = xphp_get_lang('WEB_PLATFORM_DES_COPY');
            $node['module_type'] = $module_type;
            $node['sub_module_type'] = $sub_module_type;
            $node['module_type_des'] = $this->getModuleType($module_type, $sub_module_type);
            foreach ($taskList as $d) {
                if ($module_type == $MODULE['DB']) {
                    $node['sub_type_des'] = xphp_get_config('db', 'DB_TYPE_DES')[$d['sub_type']];
                }
                if ($module_type == $MODULE['VM']) {
                    $node['sub_type_des'] = xphp_get_config('vm', 'VMHYPERVISORDES')[$d['sub_type']];
                }
                $node['sub_type'] = $d['sub_type'];
                $item_uuid = $d['tree_uuid'];
                $item_name = $d['tree_name'];
                $pId = $parent_tree_uuid;
                if ($module_type == xphp_get_config('module', 'MODULE_TYPE')['DB'] && $d['sub_type'] == xphp_get_config('db', 'DB_TYPE')['SQLSERVER']) {
                    $parts = explode(':', $d['tree_name'], 2); // 只分割两次
                    if (!in_array($parts[0] . $parent_tree_uuid, $instanceArray)) {
                        array_push($instanceArray,  $parts[0] . $parent_tree_uuid);
                        $node['tree'][] = array(
                            'id' => $parts[0] . $parent_tree_uuid,
                            'pId' => $parent_tree_uuid,
                            'name' => $parts[0],
                            'item_name' => $item_name,
                            'item_uuid' => $d['tree_uuid'],
                            'click_show' => true,
                            'nocheck' => true,
                            'title' => $item_name,
                            'isParent' => true,
                            // "icon" => $icon,
                            'type' => 2,
                            'task_uuid' => $parent_tree_uuid,
                            'module_type' => $module_type,
                            'remote_flag' => true,
                            'storage_uuid' => $storage_uuid,
                            'open' => true,
                            'event_type' => 'instance',
                            'data_type' => 4,
                            'node_uuid' => $node_uuid,
                            'parent_uuid' => $parent_tree_uuid,
                            'source_storage_uuid' => $storage_uuid,
                            'task_name' => $task_name,
                            'sub_module_type' => $sub_module_type,
                            "remote_ip" => $remote_ip,
                        );
                    }
                    $parts = explode(':', $d['tree_name'], 2); // 只分割两次
                    $pId = $parts[0] . $parent_tree_uuid;
                    $item_name = $parts[1];
                }
                if (!in_array($item_uuid, $item_arr)) {
                    array_push($item_arr, $item_uuid);
                    $node['tree'][] = [
                        'id' => $d['tree_uuid'] . $parent_tree_uuid,
                        "pId" => $pId,
                        "item_uuid" => $d['tree_uuid'],
                        "name" => $item_name,
                        // "name" => $item_name . "(" . v1_calsize($d['write_size'], true) . ")",
                        "title" => $item_name,
                        "is_parent" => true,
                        "open" => true,
                        "icon" => "./img/vm/host.png",
                        "type" => "item",
                        "module_type" => $module_type,
                        'sub_module_type' => $sub_module_type,
                        "task_type" => 17,
                        "sub_type" => $d['sub_type'],
                        'task_uuid' => $parent_tree_uuid,
                        "storage_type" => 8,
                        "storage_uuid" => $storage_uuid,
                        "remote_flag" => true,
                        "remote_ip" => $remote_ip,
                    ];
                }
            }
            return $node;
        }
    }
    // 获取异地副本数据时间点
    private function getRemotePoint($params)
    {
        // 缓存配置和描述
        static $configCache = [];

        if (empty($configCache)) {
            $configCache = [
                'MODULE_TYPE' => xphp_get_config('module', 'MODULE_TYPE'),
                'OPERATION_STATUS' => xphp_get_config('point', 'OPERATION_STATUS'),
                'VIRUS_STATUS' => xphp_get_config('point', 'VIRUS_STATUS'),
                'INTEGRITY_STATUS' => xphp_get_config('point', 'INTEGRITY_STATUS'),
                'MERGE_STATUS' => xphp_get_config('point', 'MERGE_STATUS'),
                'FLAG' => xphp_get_config('app', 'FLAG'),
                'VIRUS_STATUS_DES' => xphp_get_config('point', 'VIRUS_STATUS_DES'),
                'INTEGRITY_STATUS_DES' => xphp_get_config('point', 'INTEGRITY_STATUS_DES'),
                'BACKUP_MODE_DES' => xphp_get_desc('Pf', 'BACKUP_MODE_DES'),
                'MODULE_DES' => xphp_get_desc('Pf', 'MODULE_TYPE_DES'),
            ];
        }
        $item_name = $params['item_name'];
        $item_uuid = $params['item_uuid'];
        $task_uuid = $params['task_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $remote_ip = $params['remote_ip'];
        $module_type = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $opName = 'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TIMEPOINT';
        $node_uuid = $this->getLocalNodeUUID();
        $timepoint = [];
        $records = ['rows' => []];
        $MODE = xphp_get_config('task', 'BACKUP_MODE');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $countMsg = [
            'storage_uuid' => $storage_uuid[0],
            'parent_uuid' => '',
            'module_type' => $module_type,
            'sub_module_type' => $sub_module_type,
            'item_uuid' => $item_uuid[0],
            'task_uuid' => $task_uuid,
            'offset' => 0,
            'limit' => 99999,
        ];
        $data = $this->service()->getRemoteData($node_uuid, 1, $opName, $countMsg);
        $allPointList = $data['msg']['timepoint_info_list'];
        // 获取所有完备点
        $full_List = array_filter($allPointList, function ($d) {
            return $d['backup_mode'] == 1;
        });
        // 获取所有非完备点
        $unFull_List = array_filter($allPointList, function ($d) {
            return $d['backup_mode'] != 1;
        });
        // 按时间升序排列所有时间点，以便查找链关系
        $allPointList = v1_array_sort($allPointList, 'timepoint', 'DESC', 0, 9999);  //排序
        // 按时间升序排列非完备点，正确显示链关系
        $unFull_List = v1_array_sort($unFull_List, 'timepoint', 'ASC', 0, 9999);  //排序
        // 按表格排序完备点
        $full_List = v1_array_sort($full_List, $params['sort'], $params['order'], $params['offset'], $params['limit']);  //排序
        // 找到非完备点的父节点
        $full_uuid_List = $this->getPointChainArray($allPointList);
        $storageName = $this->getStorageName();
        $dir_path = [];
        if (!empty($full_List)) {
            foreach ($full_List as $d) {
                // 查找完备点的所有非完备点
                $children = array_filter($unFull_List, function ($p) use ($d, $full_uuid_List) {
                    return $full_uuid_List[$p['timepoint_uuid']] == $d['timepoint_uuid'];
                });
                if (!empty($children)) {
                    // 组装非完备点数据
                    $child_list = $this->getChildrenList(
                        $children, 
                        $storageName, 
                        $configCache, 
                        $d['timepoint_uuid'], 
                        $item_name[0], 
                        $item_name, 
                        $remote_ip);
                }
                $point_status = $this->getPointStatus(
                    $d['operation_status'], 
                    0, 
                    0,
                    0);
                if (in_array($d['timepoint_uuid'], $timepoint)) {
                    continue;
                }
                $info = json_decode($d['module_timepoint_info'], true);
                switch ($d['module_type']) {
                    case $MODULE['VM']:
                        $dir_path = $info['dir_path'];
                        $item_name = $info['vm_name'];
                        break;
                    case $MODULE['FS']:
                    case $MODULE['NAS']:
                        $path_list = json_decode($info['backup_path_list'], true);
                        $dir_path = [];
                        foreach ($path_list as $p) {
                            array_push($dir_path, $p['backup_path']);
                        };
                        $item_name = $info['agent_name'];
                        break;
                    case $MODULE['DB']:
                        $db_type = intval($info['db_type']);
                        $dir_path = $info['dir_path'];
                        $item_name = $info['db_name'];
                        break;
                    case $MODULE['OS']:
                        $item_name = $info['os_name'];
                        break;
                    case $MODULE['M365']:
                        $config = json_decode($info['user_config'], true);
                        $infoList = $config['backup_m365_object_info_list'];
                        $dir_path = [];
                        foreach ($infoList as $l) {
                            array_push($dir_path, $l['backup_object_mail']);
                        }
                        break;
                }
                $timepoint[] = $d['timepoint_uuid'];
                $records['rows'][] = array(
                    "id" => $d['timepoint_uuid'],
                    "timepoint" => $this->parseDate($d['timepoint']),
                    "backup_mode" => $d['backup_mode'],
                    "backup_mode_des" => $this->getPointTypeDes($d['backup_mode'], $db_type),
                    "module_type" => $d['module_type'],
                    "task_type" => $d['task_type'],
                    "point_num" => count($children),
                    "children" => json_encode($child_list),
                    "total_size" => v1_calsize($d['total_size'], true),
                    "write_size" => v1_calsize($d['write_size'], true),
                    "storage_uuid" => $d['storage_uuid'],
                    'storage' => $storageName[$d['storage_uuid']]['storage_name'],
                    "remarks" => $d['remarks'] ?? '',
                    "status" => $point_status,
                    "status_des" => $point_status['des'],
                    "status_value" => $point_status['value'],
                    "pid" => $d['backup_mode'] == $MODE['FULL'] ? '' : $full_uuid_List[$d['timepoint_uuid']],
                    "hasChildren" => count($children),
                    "item_uuid" => $item_uuid[0],
                    "item_name" => $item_name,
                    "dir_path" => is_array($dir_path) ? json_encode($dir_path) : $dir_path,
                    "task_name" => $d['task_name'],
                    'task_uuid' => $d['task_uuid'],
                    "timepoint_uuid" => $d['timepoint_uuid'],
                    "node_uuid" => $d['node_uuid'],
                    "open" => false,
                    // "sub_type" => $sub_type,
                    "importance_flag" => v1_parse_flag_to_bool($d['importance_flag']),
                    "mark_info" => [
                        "weekly_flag" => v1_parse_flag_to_bool($d['weekly_flag']),
                        "monthly_flag" => v1_parse_flag_to_bool($d['monthly_flag']),
                        "yearly_flag" => v1_parse_flag_to_bool($d['yearly_flag']),
                    ],
                    "details" => [
                        "timepoint_uuid" => $d['timepoint_uuid'],
                        "timepoint" => $d['timepoint'],
                        "operation_status" => 0,
                    ],
                    "task_delete" => true,
                    "remote_flag" => true,
                    "remote_ip" => $remote_ip,
                );
            }
            $records['total'] = count($full_List);
            return $records;
        }
    }
    /**
     * 组装非完备点数据
     * @param mixed $list
     * @param mixed $storageName
     * @param mixed $configCache
     * @param mixed $timepoint_uuid
     * @param mixed $item_uuid
     * @param mixed $item_name
     * @param mixed $remote_ip
     * @return array{}
     */
    private function getChildrenList($list, $storageName, $configCache, $timepoint_uuid, $item_uuid, $item_name, $remote_ip)
    {
        $records = [];
        foreach ($list as $d) {
            $point_status = $this->getPointStatus(
                $d['operation_status'], 
                0, 
                0,
                0);
            $info = json_decode($d['module_timepoint_info'], true);
            switch ($d['module_type']) {
                case $configCache['MODULE_TYPE']['VM']:
                    $dir_path = $info['dir_path'];
                    $item_name = $info['vm_name'];
                    break;
                case $configCache['MODULE_TYPE']['FS']:
                case $configCache['MODULE_TYPE']['NAS']:
                    $path_list = json_decode($info['backup_path_list'], true);
                    $dir_path = [];
                    foreach ($path_list as $p) {
                        array_push($dir_path, $p['backup_path']);
                    };
                    $item_name = $info['agent_name'];
                    break;
                case $configCache['MODULE_TYPE']['DB']:
                    $db_type = intval($info['db_type']);
                    $dir_path = $info['dir_path'];
                    $item_name = $info['db_name'];
                    break;
                case $configCache['MODULE_TYPE']['OS']:
                    $item_name = $info['os_name'];
                    break;
                case $configCache['MODULE_TYPE']['M365']:
                    $config = json_decode($info['user_config'], true);
                    $infoList = $config['backup_m365_object_info_list'];
                    $dir_path = [];
                    foreach ($infoList as $l) {
                        array_push($dir_path, $l['backup_object_mail']);
                    }
                    break;
            }
            $records[] = array(
                "id" => $d['timepoint_uuid'],
                "timepoint" => $this->parseDate($d['timepoint']),
                "backup_mode" => $d['backup_mode'],
                "backup_mode_des" => $this->getPointTypeDes($d['backup_mode'], $db_type),
                "module_type" => $d['module_type'],
                "task_type" => $d['task_type'],
                // "point_num" => $children['total'],
                "total_size" => v1_calsize($d['total_size'], true),
                "write_size" => v1_calsize($d['write_size'], true),
                "storage_uuid" => $d['storage_uuid'],
                'storage' => $storageName[$d['storage_uuid']]['storage_name'],
                "remarks" => $d['remarks'] ?? '',
                "status" => $point_status,
                "status_des" => $point_status['des'],
                "status_value" => $point_status['value'],
                "pid" => $timepoint_uuid,
                "hasChildren" => 0,
                "item_uuid" => $item_uuid,
                "item_name" => $item_name,
                "dir_path" => is_array($dir_path) ? json_encode($dir_path) : $dir_path,
                "task_name" => $d['task_name'],
                'task_uuid' => $d['task_uuid'],
                "timepoint_uuid" => $d['timepoint_uuid'],
                "node_uuid" => $d['node_uuid'],
                "point_num" => self::NULL_RESULT_DES,
                "open" => false,
                // "sub_type" => $sub_type,
                "importance_flag" => v1_parse_flag_to_bool($d['importance_flag']),
                "mark_info" => [
                    "weekly_flag" => v1_parse_flag_to_bool($d['weekly_flag']),
                    "monthly_flag" => v1_parse_flag_to_bool($d['monthly_flag']),
                    "yearly_flag" => v1_parse_flag_to_bool($d['yearly_flag']),
                ],
                "details" => [
                    "timepoint_uuid" => $d['timepoint_uuid'],
                    "timepoint" => $d['timepoint'],
                    "operation_status" => 0,
                ],
                "task_delete" => true,
                "remote_flag" => true,
                "remote_ip" => $remote_ip,
            );
        }
        return $records;
    }
    /**
     * 获取所有节点的名称
     * @return string[]
     */
    private function getRealNodeName()
    {
        $result = [];
        $sql = "SELECT host_name,ip,node_uuid FROM bd_node";
        $data = $this->dbSelect($sql, []) ?? [];
        foreach ($data as $d) {
            $result[$d['node_uuid']] = '' . $data[0]['host_name'] . '(' . $data[0]['ip'] . ')';
        }
        return $result;
    }
    // 获取标签点列表
    public function getVolTagPointGrid($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $item_uuid = $params['item_uuid'];
        $sort = $params['sort'];
        $order = $params['order'];
        $sortArr = [
            'label_timestamp' => 'label.label_timestamp',
        ];
        $sql = "SELECT bbt.storage_uuid,bbt.timepoint_uuid,bbt.data_local_flag,bbt.importance_flag,bbt.remarks,bbt.archive_flag,bbt.task_name,bbt.task_uuid,bbt.backup_mode,bbt.operation_status,bbt.merge_status,bbt.worm_flag,
        label.label_timestamp,label.id,label.remarks,label.backup_agent_id,
        bsi.virus_scan_status,bsi.virus_list,bsi.last_virus_scan_time,bsi.last_integrity_check_time,bsi.integrity_check_status,
        bsi.worm_expire_date,bsr.node_uuid, bbt.real_node_uuid
        FROM cdp_vol_backup_agent agent,cdp_vol_agent_label_set label,bd_storage_resource bsr,bd_backup_timepoint bbt
        LEFT JOIN bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid = bsi.timepoint_uuid
        where bbt.timepoint_uuid = agent.timepoint_uuid
        AND bbt.storage_uuid = bsr.storage_uuid
        AND agent.master_agent_uuid = ?  
        AND agent.id = label.backup_agent_id 
        AND bbt.deleted_flag = " . xphp_get_config('app', 'FLAG')['UNSET'];
        $sqlCount = "SELECT COUNT(label.id) AS total FROM bd_backup_timepoint bbt,cdp_vol_backup_agent agent,cdp_vol_agent_label_set AS label,bd_storage_resource bsr
        where bbt.timepoint_uuid = agent.timepoint_uuid
        AND bbt.storage_uuid = bsr.storage_uuid
        AND agent.master_agent_uuid = ?  
        AND agent.id = label.backup_agent_id 
        AND bbt.deleted_flag = " . xphp_get_config('app', 'FLAG')['UNSET'];
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        if (!empty($authUser)) {  // 表示有管理的用户
            $userArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
            $sql .= " AND bbt.user_uuid in ('" . implode("','", $userArr) . "')";
        }
        if (!empty($sort) && !empty($order)) {
            $sql .= " ORDER BY {$sortArr[$sort]} {$order}";
        }
        if (!empty($start) && !empty($length)) {
            $sql .= " LIMIT {$start} , {$length} ";
        }

        $data = $this->dbSelect($sql, [$item_uuid]);
        $countData = $this->dbSelect($sqlCount, [$item_uuid]);
        $storageName = $this->getStorageName();
        $records = [];
        $node_name_array = $this->getRealNodeName();
        if (!empty($data)) {
            foreach ($data as $d) {
                $worm_info = $this->getWormExpireDays($d['worm_flag'], $d['worm_expire_date']);
                $records['rows'][] = [
                    "label_timestamp" => $d['label_timestamp'],
                    'storage' => !empty($d['real_node_uuid']) ? $storageName[$d['storage_uuid']]['storage_name'] . '(' . $node_name_array[$d['real_node_uuid']] . ')'  : $storageName[$d['storage_uuid']]['storage_name'],
                    'storage_status' => $storageName[$d['storage_uuid']]['status'],
                    "remarks" => $d['remarks'],
                    "backup_mode" => $d['backup_mode'],
                    "node_uuid" => $d['real_node_uuid'] ?: $d['node_uuid'],
                    "backup_agent_id" => $d['backup_agent_id'],
                    "label_id" => $d['id'],
                    "tagFlag" => true,
                    "details" => [
                        "timepoint_uuid" => $d['timepoint_uuid'],
                        "timepoint" => $d['timepoint'],
                        "operation_status" => $d['operation_status'],
                        "virus_info" => [
                            "virus_scan_status" => $d['virus_scan_status'] ?? 0,
                            "virus_scan_status_des" => xphp_get_config('point', 'VIRUS_STATUS_DES')[$d['virus_scan_status']],
                            "virus_list" => $d['virus_list'],
                            "last_virus_scan_time" => $d['last_virus_scan_time'],
                        ],
                        "integrity_info" => [
                            "integrity_check_flag" => $d['integrity_check_flag'],
                            "integrity_check_status" => $d['integrity_check_status'],
                            "integrity_check_status_des" => xphp_get_config('point', 'INTEGRITY_STATUS_DES')[$d['integrity_check_status']],
                            "last_integrity_check_time" => $d['last_integrity_check_time'],
                        ],
                        "worm_info" => [
                            "worm_flag" => $d['worm_flag'],
                            "worm_expire_date" => $d['worm_expire_date'],
                            "worm_expire_days" => $worm_info['days'],
                            "worm_expire_left" => $worm_info['left'],
                        ],
                        "merge_status" => $d['merge_status'],
                        "merge_status_des" => xphp_get_config('point', 'MERGE_STATUS_DES')[$d['merge_status']],
                    ],
                ];
            }
        }
        $records["total"] = $countData[0]['total'];
        return  $records;
    }
    // 设置标签点备注
    public function remarkTagPoint($params)
    {
        $label_id = $params['label_id'];
        $backupAgentId = $params['backup_agent_id'];
        $remark = $params['remark'];
        $this->paramsCheck($label_id);
        $sql = "update cdp_vol_agent_label_set set remarks = ? where backup_agent_id = ? and id = ?";
        $result = $this->dbExec($sql, array($remark, $backupAgentId, $label_id));
        if ($result) {
            return $this->muOpResult(true, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'), "", "success");
        } else {
            return $this->muOpResult(false, xphp_get_lang('UI_VOL_CDP_BACKUP_SET_MODIFY_LABEL_INFO'), "", "warning");
        }
    }
    // 删除标签点
    public function deleteTagPoint($params)
    {
        $pfOpcode = new PfOpcode();
        $msg = [
            "backup_agent_id" => $params['backup_agent_id'],
            "label_timestamp" => $params['label_timestamp'],
        ];
        $opName = 'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL';
        $operate = $pfOpcode->getOpcodeDes($opName);
        $mbResult = $this->mbVolCdpMsg($params['node_uuid'], $opName, json_encode($msg));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * worm保护
     * 1.配置worm只有oracle需要发完备 其他发点击的点
     * 2.延长worm都发发完备点
     * @param mixed $params
     * @return void
     */
    public function setWormTime($params)
    {
        $worm_set_time = $params['worm_set_time'];
        $extend_time = $params['extend_time'];
        $sql = "SELECT
                    bsr.node_uuid,
                    bsr.storage_type,
                    bbt.real_node_uuid,
                    bbt.backup_mode,
                    bbt.module_type,
                    bbt.task_uuid,
                    dbt.db_type
                FROM
                    bd_storage_resource bsr,
                    bd_backup_timepoint bbt
                    LEFT JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE
                    bbt.storage_uuid = bsr.storage_uuid 
                    AND bbt.timepoint_uuid = ?";
        $data = $this->dbSelect($sql, [$params['full_uuid']]) ?? [];
        $node_uuid = $data[0]['real_node_uuid'] ?: $data[0]['node_uuid'];
        $msg = [];
        // 延长发完备点
        if (!empty($extend_time)) {
            $opName = 'TIMEPOINT_WEB_OP_EXTEND_WORM_FOR_BACKUP_CHAIN';
            $operate = xphp_get_lang('WEB_TIMEPOINT_WEB_OP_EXTEND_WORM_FOR_BACKUP_CHAIN');
            $msg = [
                "timepoint_uuid" => $params['full_uuid'],
                "extend_time" => $extend_time,
            ];
        } else { // 配置woram发本身，oracle发完备点
            $opName = 'TIMEPOINT_WEB_OP_SET_WORM_OF_BACKUP_POINT';
            $operate = xphp_get_lang('WEB_TIMEPOINT_WEB_OP_SET_WORM_OF_BACKUP_POINT');
            // oracle发完备点
            if ($data[0]['db_type'] == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
                $msg = [
                    "timepoint_uuid" => $params['full_uuid'],
                    "extend_time" => $extend_time,
                ];
            } else {
                $msg = [
                    "timepoint_uuid" => $params['latest_uuid'],
                    "worm_set_time" => $worm_set_time,
                ];
            }
        }
        $mbResult = $this->service()->configWormTime($node_uuid, $msg, $opName) ?? [];
        $result = $mbResult['result'];
        $msg = $mbResult['errorMsg'];
        if ($result) {
            $this->muOpResult(true, $operate, $msg);
        } else {
            $this->muOpResult(false, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取病毒感染列表
     * @param mixed $params
     * @return array{file_index: mixed|string, file_md5: mixed|string, file_path: mixed|string, file_size: string, file_state: mixed|string, file_virus_name: mixed|string[][]}
     */
    public function getVirusList($params)
    {
        $msg = [
            "max_num" => 100,
            "timepoint_uuid" => $params['timepoint_uuid'],
            "result_file_path" => $params['result_file_path'],
            "detect_uuid" => $params['detect_uuid'],
            "start_file_index" => 0,
        ];
        $mbResult = $this->service()->getVirusInfo($msg);
        $data = $mbResult['msg']['result_list'];
        $result = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $result['result_list'][] = [
                    "file_index" => $d['file_index'],
                    "file_md5" => $d['file_md5'],
                    "file_path" => $d['file_path'],
                    "file_size" => v1_calsize($d['file_size'], true),
                    "file_state" => $d['file_state'],
                    "file_virus_name" => $d['file_virus_name'],
                ];
            }
        }
        return $result;
    }

    /**
     * 根据备份获取存储信息
     * @param array $timepointUuidList 备份点uuid列表
     * @return array
     */
    public function getStorageInfoByTimepoints(array $timepointUuidList): array
    {
        $timepointUuidList = array_values(array_unique($timepointUuidList));
        $timepointUuids = "'" . implode("','", $timepointUuidList) . "'";
        $sql = "SELECT timepoint_uuid, storage_uuid, real_node_uuid, backup_mode
                FROM bd_backup_timepoint
                WHERE timepoint_uuid IN ($timepointUuids) ";
        $timepointData = $this->dbSelect($sql);
        if (!is_array($timepointData)) {
            $timepointData = [];
        }
        $storageUuidList = array_column($timepointData, 'storage_uuid');
        $storageList = (new Storage())->batchGetStorageStatus($storageUuidList);
        $storageMap = [];
        foreach ($storageList as $storageInfo) {
            $storageMap[$storageInfo['storage_uuid']] = $storageInfo;
        }

        $allStorageType = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        $shareStorageTypeList = [  // 共享存储
            $allStorageType['NFS'],
            $allStorageType['CIFS'],
            $allStorageType['CLOUD'],
        ];
        $retTimepointData = [];
        foreach ($timepointData as $timepoint) {
            $tmpTimepointInfo = [
                'timepoint_uuid' => $timepoint['timepoint_uuid'],
                'real_node_uuid' => $timepoint['real_node_uuid'],
                'backup_mode' => $timepoint['backup_mode'],
                'storage_uuid' => $timepoint['storage_uuid'],
                'storage_type' => 0,
                'storage_status' => 0,
                'storage_nickname' => '',
                'storage_online_flag' => false,
                'storage_mount_node_uuid_list' => [],
                'node_online_flag' => false,
                'node_uuid' => '',
                'node_nickname' => '',
                'node_ip' => '',
                'node_hostname' => '',
                'node_type' => 0,
            ];
            if (isset($storageMap[$timepoint['storage_uuid']])) {
                $tmpTimepointInfo['storage_type'] = $storageMap[$timepoint['storage_uuid']]['storage_type'];
                $tmpTimepointInfo['storage_status'] = $storageMap[$timepoint['storage_uuid']]['storage_status'];
                $tmpTimepointInfo['storage_nickname'] = $storageMap[$timepoint['storage_uuid']]['storage_nickname'];
                $tmpTimepointInfo['storage_online_flag'] = $storageMap[$timepoint['storage_uuid']]['storage_online_flag'];
                $tmpTimepointInfo['node_online_flag'] = $storageMap[$timepoint['storage_uuid']]['node_online_flag'];
                $tmpTimepointInfo['node_uuid'] = $storageMap[$timepoint['storage_uuid']]['node_uuid'];
                $tmpTimepointInfo['node_nickname'] = $storageMap[$timepoint['storage_uuid']]['node_nickname'];
                $tmpTimepointInfo['node_ip'] = $storageMap[$timepoint['storage_uuid']]['node_ip'];
                $tmpTimepointInfo['node_hostname'] = $storageMap[$timepoint['storage_uuid']]['node_hostname'];
                $tmpTimepointInfo['node_type'] = $storageMap[$timepoint['storage_uuid']]['node_type'];
                $tmpTimepointInfo['storage_mount_node_uuid_list'] = $storageMap[$timepoint['storage_uuid']]['mount_node_uuid_list'];
                // 共享存储采用real_node_uuid
                if (in_array($tmpTimepointInfo['storage_type'], $shareStorageTypeList)) {
                    $tmpTimepointInfo['node_uuid'] = $timepoint['real_node_uuid'];
                }
            }
            $retTimepointData[] = $tmpTimepointInfo;
        }
        $allRealNodeUuidList = array_column($timepointData, 'real_node_uuid');
        $allRealNodeUuidList = array_filter($allRealNodeUuidList);
        $allRealNodeList = (new Node())->batchGetNodeStatus($allRealNodeUuidList);
        $allRealNodeMap = [];
        foreach ($allRealNodeList as $realNodeInfo) {
            $allRealNodeMap[$realNodeInfo['node_uuid']] = $realNodeInfo;
        }
        $requestNodeUuidList = [];
        foreach ($retTimepointData as $key => $tmpTimepointInfo) {
            // 共享存储
            if (!in_array($tmpTimepointInfo['storage_type'], $shareStorageTypeList)) {  // 不是共享存储
                continue;
            }
            // real_node在线
            if (
                $allRealNodeMap[$tmpTimepointInfo['real_node_uuid']]['online_flag'] &&  // real_node在线
                in_array($tmpTimepointInfo['real_node_uuid'], $tmpTimepointInfo['storage_mount_node_uuid_list'])  // real_node还被该存储挂载
            ) {
                $retTimepointData[$key]['node_online_flag'] = true;
                continue;
            }

            if (!$tmpTimepointInfo['storage_online_flag']) {  // 所有存储都离线了
                continue;
            }

            // real_node离线了，因此需要向后台发送消息，获取可用的节点uuid
            if (!isset($requestNodeUuidList[$tmpTimepointInfo['storage_uuid']])) {
                $requestNodeUuidList[$tmpTimepointInfo['storage_uuid']] = $this->service('\\app\\v1\\backupData\\v0\\service\\Service')->getClusterAccessableNode(["storage_uuid" => $tmpTimepointInfo['storage_uuid']]);
            }
            $retTimepointData[$key]['node_online_flag'] = true;
            $retTimepointData[$key]['node_uuid'] = $requestNodeUuidList[$tmpTimepointInfo['storage_uuid']]['node_uuid'];
        }
        return $retTimepointData;
    }
    /**
     * 获取病毒扫描历史记录
     * @param mixed $params
     * @return array[]|array{rows: array, total: mixed}
     */
    public function getVirusHistoryList($params)
    {
        if (empty($params['timepoint_uuid'])) return [];
        $sortArr = [
            "start_time" => 'bbtdh.start_time',
            "end_time" => 'bbtdh.end_time',
            "status" => 'bbtdh.status',
            "skip_file_num" => 'bbtdh.skip_file_num',
            "virus_file_num" => 'bbtdh.virus_file_num',
        ];
        // 状态描述信息
        $DES = require APP_PATH . 'v1/description/Point.php';
        $sql = "SELECT bbtdh.timepoint_datetime,bbtdh.timepoint_uuid, bbtdh.start_time, bbtdh.end_time, bbtdh.status, bbtdh.total_file_num, bbtdh.detect_file_num, bbtdh.virus_file_num, bbtdh.detect_uuid, bbtdh.skip_file_num, bbtdh.detect_config FROM bd_backup_timepoint_detect_history bbtdh, bd_backup_timepoint bbt WHERE bbtdh.timepoint_uuid = bbt.timepoint_uuid AND bbt.timepoint_uuid = ?";
        $sqlCount = "SELECT COUNT(bbtdh.timepoint_uuid) as total FROM bd_backup_timepoint_detect_history bbtdh, bd_backup_timepoint bbt WHERE bbtdh.timepoint_uuid = bbt.timepoint_uuid AND bbt.timepoint_uuid = ?";
        // 排序方式
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sql .= " ORDER BY {$sortArr[$params['sort']]} {$params['order']}";
            $sqlCount .= " ORDER BY {$sortArr[$params['sort']]} {$params['order']}";
        }
        $data = $this->dbSelect($sql, [$params['timepoint_uuid']]) ?? [];
        $dataCount = $this->dbSelect($sqlCount, [$params['timepoint_uuid']]) ?? [];
        $node = ['rows' => [], 'total' => $dataCount[0]['total']];
        if (!empty($data)) {
            foreach ($data as $key => $d) {
                $node['rows'][] = [
                    'timepoint' => $d['timepoint_datetime'],
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'start_time' => $d['start_time'],
                    'end_time' => $d['end_time'],
                    'status' => $d['status'],
                    'status_des' => !empty($d['status']) ? $DES['VIRUS_STATUS_DES'][$d['status']] : $DES['VIRUS_STATUS_DES'][0],
                    'total_detect_file_num' => $d['detect_file_num'] . '/' . $d['total_file_num'],
                    'detect_file_num' => $d['detect_file_num'],
                    'detect_uuid' => $d['detect_uuid'],
                    'detect_config' => $d['detect_config'],
                    'skip_file_num' => $d['skip_file_num'],
                    'virus_file_num' => $d['virus_file_num'],
                    'virus_list' => $params['virus_list'],
                ];
            }
        }
        return $node;
    }
    /**
     * 获取验证历史记录
     * @param mixed $params
     * @return array|array{rows: array, total: int}
     */
    public function getDataVerifyHistory($params)
    {
        $VERIFY_FUNC_STATUS = xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS');
        if (empty($params['timepoint_uuid'])) return [];
        $sql = "SELECT * from sr_surebackup_report where timepoint_uuid = '{$params['timepoint_uuid']}'";
        $data = $this->dbSelect($sql) ?? [];
        $sqlCount = "SELECT COUNT(timepoint_uuid) as total from sr_surebackup_report where timepoint_uuid = '{$params['timepoint_uuid']}'";
        $dataCount = $this->dbSelect($sqlCount) ?? [];
        $node = ['rows' => [], 'total' => 0];
        if (!empty($data)) {
            foreach ($data as $key => $d) {
                if (!empty($d['extension_info'])) {
                    $extension_info = json_decode($d['extension_info'], true);
                    $node['rows'][] = [
                        'timepoint' => $d['timestamp'],
                        'timepoint_uuid' => $d['timepoint_uuid'],
                        'start_time' => $d['start_time'],
                        'end_time' => $d['end_time'],
                        'ping_status' => intval($extension_info['ping_test_status']),
                        'ping_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['ping_test_status'])],
                        'heartbeat_status' => intval($extension_info['heartbeat_status']),
                        'heartbeat_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['heartbeat_status'])],
                        'screen_status' => intval($extension_info['print_screen_status']),
                        'screen_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['print_screen_status'])],
                        'vir_det_kill_status' => intval($extension_info['vir_det_kill_status']),
                        'vir_det_kill_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['vir_det_kill_status'])],
                        'integrity_check_status' => intval($extension_info['integrity_check_status']),
                        'integrity_check_status_des' => $VERIFY_FUNC_STATUS[intval($extension_info['integrity_check_status'])],
                    ];
                }
            }
            $node['total'] = $dataCount[0]['total'];
        }
        return $node;
    }
    /**
     * 获取主机信息
     * @param mixed $agent_uuid
     */
    private function getAgentInfo($agent_uuid)
    {
        $sql = "SELECT * FROM bd_agent WHERE agent_uuid = '{$agent_uuid}'";
        $data = $this->dbSelect($sql) ?? [];
        return $data[0]['agent_name'];
    }
    /**
     * 获取模块对应的图标
     * @param mixed $module_type
     * @param mixed $sub_module_type
     * @return string
     */
    public function getIconByModule($module_type, $sub_module_type)
    {
        $icon = 'item-icon_host';
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        switch ($module_type) {
            case $MODULE_TYPE['VM']:
                $icon = 'item-icon_vm';
                break;
            case $MODULE_TYPE['FS']:
                if ($sub_module_type == 2) {
                    $icon = 'item-icon_nas';
                }
                if ($sub_module_type == 3) {
                    $icon = 'item-icon_hadoop';
                }
                if ($sub_module_type == 4) {
                    $icon = 'item-icon_obs';
                }
                break;
            case $MODULE_TYPE['DB']:
                $icon = 'item-icon_db';
                break;
            case $MODULE_TYPE['NAS']:
                $icon = 'item-icon_nas';
                break;
            case $MODULE_TYPE['M365']:
                $icon = 'iconSkin-exch-organization';
                break;
            case $MODULE_TYPE['KUBERNETES']:
                $icon = 'ztree_CLUSTER';
                break;
        }
        return $icon;
    }
    /**
     * 导出任务列表
     * @param mixed $params
     * @return {}
     */
    public function exportAllTaskList($params)
    {
        $exportData = $this->getTaskGrid($params)['rows'];
        $title = xphp_get_lang('UI_BACKUP_DATA_LABEL_TASK_LIST');
        $header = [
            'task_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'module_type_des' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'task_type_des' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'total_size' => xphp_get_lang('UI_DATA_SIZE'),
            'write_size' => xphp_get_lang('UI_JOB_REAL_SIZE'),
            'point_num' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_POINT_NUM'),
            'task_create_time' => xphp_get_lang('UI_PUBLIC_CREATE_TIME'),
            'sub_type_des' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_SUB_MODULE_TYPE'),
            'vcenter_name' => xphp_get_lang('WEB_VM_VCENTER'),
            'deleted_flag' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_DELETED'),
        ];
        $data = [];
        foreach ($exportData as $row) {
            $data[] = [
                'task_name' => $row['task_name'],
                'module_type_des' => $row['module_type_des'],
                'task_type_des' => $row['task_type_des'],
                'total_size' => $row['total_size'],
                'write_size' => $row['write_size'],
                'point_num' => $row['point_num'],
                'task_create_time' => $row['task_create_time'],
                'sub_type_des' => $row['sub_type_des'],
                'vcenter_name' => $row['vcenter_name'],
                'deleted_flag' => $row['deleted_flag'] ? xphp_get_lang('WEB_PLATFORM_PUBLIC_YES') : xphp_get_lang('WEB_PLATFORM_PUBLIC_NO'),
            ];
        }

        $relation = [
            'task_name' => ['col_name' => 'A', 'width' => 25],
            'module_type_des' => ['col_name' => 'B', 'width' => 15],
            'task_type_des' => ['col_name' => 'C', 'width' => 15],
            'total_size' => ['col_name' => 'D', 'width' => 15],
            'write_size' => ['col_name' => 'E', 'width' => 15],
            'point_num' => ['col_name' => 'F', 'width' => 15],
            'task_create_time' => ['col_name' => 'G', 'width' => 20],
            'sub_type_des' => ['col_name' => 'H', 'width' => 20],
            'vcenter_name' => ['col_name' => 'I', 'width' => 15],
            'deleted_flag' => ['col_name' => 'J', 'width' => 15],
        ];
        v1_base_export($title, $header, $data, $relation);
    }
    /**
     * 导出异地数据列表
     * @param mixed $params
     * @return {}
     */
    public function exportAllRemoteTaskList($params)
    {
        $exportData = $this->getRemoteGrid($params)['rows'];
        $title = xphp_get_lang('UI_BACKUP_DATA_LABEL_TASK_LIST');
        $header = [
            'task_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'module_type_des' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'task_type_des' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'total_size' => xphp_get_lang('UI_DATA_SIZE'),
            'write_size' => xphp_get_lang('UI_JOB_REAL_SIZE'),
            'point_num' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_POINT_NUM'),
            'task_create_time' => xphp_get_lang('UI_PUBLIC_CREATE_TIME'),
            'deleted_flag' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_DELETED'),
        ];
        $data = [];
        foreach ($exportData as $row) {
            $data[] = [
                'task_name' => $row['task_name'],
                'module_type_des' => $row['module_type_des'],
                'task_type_des' => $row['task_type_des'],
                'total_size' => $row['total_size'],
                'write_size' => $row['write_size'],
                'point_num' => $row['point_num'],
                'task_create_time' => $row['task_create_time'],
                'deleted_flag' => $row['deleted_flag'] ? xphp_get_lang('WEB_PLATFORM_PUBLIC_YES') : xphp_get_lang('WEB_PLATFORM_PUBLIC_NO'),
            ];
        }

        $relation = [
            'task_name' => ['col_name' => 'A', 'width' => 25],
            'module_type_des' => ['col_name' => 'B', 'width' => 15],
            'task_type_des' => ['col_name' => 'C', 'width' => 15],
            'total_size' => ['col_name' => 'D', 'width' => 15],
            'write_size' => ['col_name' => 'E', 'width' => 15],
            'point_num' => ['col_name' => 'F', 'width' => 15],
            'task_create_time' => ['col_name' => 'G', 'width' => 20],
            'deleted_flag' => ['col_name' => 'J', 'width' => 15],
        ];
        v1_base_export($title, $header, $data, $relation);
    }
    /**
     * 导出对象列表
     * @param mixed $params
     * @return {}
     */
    public function exportAllItemList($params)
    {
        $exportData = $this->getItemGrid($params)['rows'];
        $title = xphp_get_lang('UI_BACKUP_DATA_LABEL_TASK_LIST');
        $header = [
            'item_name' => xphp_get_lang('UI_COPY_SOURCE_ITEM_NAME'),
            'module_type_des' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'task_type_des' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'ip' => xphp_get_lang('UI_VOL_CDP_RECOVER_IP'),
            'data_size' => xphp_get_lang('UI_DATA_SIZE'),
            'write_size' => xphp_get_lang('UI_JOB_REAL_SIZE'),
            'point_num' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_POINT_NUM'),
            'last_time' => xphp_get_lang('UI_PUBLIC_CREATE_TIME'),
            'sub_type_des' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_SUB_MODULE_TYPE'),
            'vcenter_name' => xphp_get_lang('WEB_VM_VCENTER'),
        ];
        $data = [];
        foreach ($exportData as $row) {
            $data[] = [
                'item_name' => $row['item_name'],
                'module_type_des' => $row['module_type_des'],
                'task_type_des' => $row['task_type_des'],
                'ip' => $row['ip'],
                'data_size' => $row['data_size'],
                'write_size' => $row['write_size'],
                'point_num' => $row['point_num'],
                'last_time' => $row['last_time'],
                'sub_type_des' => $row['sub_type_des'],
                'vcenter_name' => $row['vcenter_name'],
            ];
        }

        $relation = [
            'task_name' => ['col_name' => 'A', 'width' => 25],
            'module_type_des' => ['col_name' => 'B', 'width' => 15],
            'task_type_des' => ['col_name' => 'C', 'width' => 15],
            'ip' => ['col_name' => 'C', 'width' => 15],
            'data_size' => ['col_name' => 'D', 'width' => 15],
            'write_size' => ['col_name' => 'E', 'width' => 15],
            'point_num' => ['col_name' => 'F', 'width' => 15],
            'last_time' => ['col_name' => 'G', 'width' => 20],
            'sub_type_des' => ['col_name' => 'H', 'width' => 20],
            'vcenter_name' => ['col_name' => 'I', 'width' => 15],
        ];
        v1_base_export($title, $header, $data, $relation);
    }
    /**
     * 导出备份点列表
     * @param mixed $params
     * @return {}
     */
    public function exportAllPointList($params)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $exportData = $this->getAllPoints($params)['rows'];
        switch ($params['module_type']) {
            case $MODULE['VOL_CDP']:
                $this->exportAllVolPointList($exportData);
                break;
            case $MODULE['DB_CDP']:
                $this->exportAllDBCdpPointList($exportData);
                break;
            default:
                $this->exportAllPoint($exportData);
        }
    }
    /**
     * 导出定时模块备份点列表
     * @param mixed $params
     * @return {}
     */
    private function exportAllPoint($exportData)
    {
        $title = xphp_get_lang('UI_BACKUP_DATA_LABEL_TASK_LIST');
        $header = [
            'timepoint' => xphp_get_lang('UI_BACKUP_DATA_REPORT_TIME_POINT'),
            'backup_mode_des' => xphp_get_lang('UI_VISUAL_TYPE'),
            'total_size' => xphp_get_lang('UI_DATA_SIZE'),
            'write_size' => xphp_get_lang('UI_JOB_REAL_SIZE'),
            'point_num' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_UN_FULL_NUM'),
            'storage' => xphp_get_lang('UI_DATA_OF_STORAGE'),
            'status' => xphp_get_lang('UI_BACKUP_DATA_EXPORT_STATE'),
            'task_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'remarks' => xphp_get_lang('UI_PUBLIC_REMARK'),
            'abnormal_chain_des' => xphp_get_lang('UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN_REASON'),
            'importance_flag' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_FOREVER'),
            'GFS_flag' => xphp_get_lang('UI_BACKUP_DATA_TABLE_LABEL_GFS'),
        ];
        $data = [];
        foreach ($exportData as $row) {
            $data[] = [
                'timepoint' => $row['timepoint'],
                'backup_mode_des' => $row['backup_mode_des'],
                'total_size' => $row['total_size'],
                'write_size' => $row['write_size'],
                'point_num' => $row['point_num'],
                'storage' => $row['storage'],
                'status' => $row['status_des'],
                'task_name' => $row['task_name'],
                'remarks' => $row['remarks'],
                'abnormal_chain_des' => $row['abnormal_chain_des'],
                'importance_flag' => $row['importance_flag'],
                'GFS_flag' => $row['GFS_flag'],
            ];
        }

        $relation = [
            'timepoint' => ['col_name' => 'A', 'width' => 25],
            'backup_mode_des' => ['col_name' => 'B', 'width' => 15],
            'total_size' => ['col_name' => 'C', 'width' => 15],
            'write_size' => ['col_name' => 'C', 'width' => 15],
            'point_num' => ['col_name' => 'D', 'width' => 15],
            'storage' => ['col_name' => 'E', 'width' => 35],
            'status' => ['col_name' => 'F', 'width' => 15],
            'task_name' => ['col_name' => 'G', 'width' => 20],
            'remarks' => ['col_name' => 'H', 'width' => 20],
            'abnormal_chain_des' => ['col_name' => 'I', 'width' => 25],
            'importance_flag' => ['col_name' => 'I', 'width' => 15],
            'GFS_flag' => ['col_name' => 'I', 'width' => 15],
        ];
        v1_base_export($title, $header, $data, $relation);
    }
    /**
     * 导出整机实时备份点列表
     * @param mixed $params
     * @return {}
     */
    private function exportAllVolPointList($exportData)
    {
        $title = xphp_get_lang('UI_BACKUP_DATA_LABEL_TASK_LIST');
        $header = [
            'start_timestamp' => xphp_get_lang('UI_VOL_CDP_START_TIME'),
            'end_timestamp' => xphp_get_lang('UI_VOL_CDP_END_TIME'),
            'encrypted_des' => xphp_get_lang('UI_VOL_CDP_DATA_ENCRYPT'),
            'backup_file_size' => xphp_get_lang('UI_VOL_CDP_DATA_MIRROR_DATA'),
            'log_file_total_size' => xphp_get_lang('UI_VOL_CDP_DATA_BACKOUT_DATA'),
            'storage' => xphp_get_lang('UI_DATA_OF_STORAGE'),
            'vol_storage_status' => xphp_get_lang('UI_PUBLIC_STATUS'),
            'remarks' => xphp_get_lang('UI_PUBLIC_REMARK'),
        ];
        $data = [];
        foreach ($exportData as $row) {
            $data[] = [
                'start_timestamp' => $row['start_timestamp'],
                'end_timestamp' => $row['end_timestamp'],
                'encrypted_des' => $row['encrypted_des'],
                'backup_file_size' => $row['backup_file_size'],
                'log_file_total_size' => $row['log_file_total_size'],
                'storage' => $row['storage'],
                'vol_storage_status' => $row['vol_storage_status'],
                'remarks' => $row['remarks'],
            ];
        }
        $relation = [
            'start_timestamp' => ['col_name' => 'A', 'width' => 25],
            'end_timestamp' => ['col_name' => 'B', 'width' => 25],
            'encrypted_des' => ['col_name' => 'C', 'width' => 15],
            'backup_file_size' => ['col_name' => 'C', 'width' => 15],
            'log_file_total_size' => ['col_name' => 'D', 'width' => 15],
            'storage' => ['col_name' => 'E', 'width' => 35],
            'vol_storage_status' => ['col_name' => 'F', 'width' => 15],
            'remarks' => ['col_name' => 'H', 'width' => 20],
        ];
        v1_base_export($title, $header, $data, $relation);
    }
    /**
     * 导出数据库实时
     * @param mixed $exportData
     * @return {}
     */
    public function exportAllDBCdpPointList($exportData)
    {
        $title = xphp_get_lang('UI_BACKUP_DATA_LABEL_TASK_LIST');
        $header = [
            'start_timestamp' => xphp_get_lang('UI_BACKUP_DATA_REPORT_TIME_POINT'),
            'end_timestamp' => xphp_get_lang('UI_VISUAL_TYPE'),
            'source_agent' => xphp_get_lang('UI_BACKUP_FILE_SOURCE_HOST'),
            'target_agent' => xphp_get_lang('UI_VOL_CDP_STANDBY'),
            'db_cdp_status' => xphp_get_lang('UI_PUBLIC_STATUS'),
        ];
        $data = [];
        foreach ($exportData as $row) {
            $data[] = [
                'start_timestamp' => $row['timepoint'],
                'end_timestamp' => $row['backup_mode_des'],
                'source_agent' => $row['total_size'],
                'target_agent' => $row['write_size'],
                'db_cdp_status' => $row['point_num'],
            ];
        }
        $relation = [
            'start_timestamp' => ['col_name' => 'A', 'width' => 25],
            'end_timestamp' => ['col_name' => 'B', 'width' => 15],
            'source_agent' => ['col_name' => 'C', 'width' => 15],
            'target_agent' => ['col_name' => 'C', 'width' => 15],
            'db_cdp_status' => ['col_name' => 'D', 'width' => 15],
        ];
        v1_base_export($title, $header, $data, $relation);
    }
}
