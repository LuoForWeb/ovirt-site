<?php
/*
 * @note: 系统备份与恢复逻辑
 * @author: chenyunfeng@vinchin.com
 * @Description: 系统备份与恢复逻辑
 * @Date: 2025-04-23 15:48:39
 * @LastEditTime: 2026-03-20 16:32:25
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Storage;
use DateTime;
use app\v1\resources\v0\logic\Node;

class SystemBackup extends Base
{
    //自动备份文件相对URL
    private $systemAutoUrl = "";
    //自动备份自描述文件相对URL
    private $systemAutoSelfMarkUrl = '';
    //自动备份存储路径
    private $autoBackupStoragePath = "";
    //自动备份导出文件路径
    private $autoBackupExportFilePath = "";
    //自动备份文件名
    private $autoBackupFileName = "";
    //自动备份时间
    private $autoBackupTime = "";
    //恢复工作目录
    private $systemRecDir = "";
    //恢复的vcenter记录
    private $vcenterRefleshList = [];
    // 数据库名称->唯一标识字段
    private const TABLE_PRIMARY_KEY = [
        "bd_task" => 'task_uuid', // 任务总表
        "bd_running_info" => 'task_uuid', // 任务运行表
        "bd_strategy" => 'strategy_id', // 策略总表
        "bd_time_strategy" => 'strategy_id', // 时间策略
        "bd_reserved_strategy"  => 'strategy_id', // 保留策略
        "bd_storage_strategy" => 'strategy_id', // 存储策略
        "bd_task_speed_limit_strategy" => 'strategy_uuid', // 速度策略
        "bd_transport_strategy" => 'strategy_id', // 传输策略
        "bd_retry_strategy" => 'task_uuid', // 重试策略
        "bd_task_safe_config" => "task_uuid", // 安全策略
        "bd_task_gfs_retention_strategy" => 'task_uuid', // GFS策略
        "bd_gfs_entity_waiting_map" => 'task_uuid', // GFS等待map表
        "bd_node_network" => 'network_uuid', // 节点网络
        "bd_task_agent_list" => 'task_uuid', // 备份/恢复代理列表
        "bd_grain_recovery_task" => 'task_uuid', // 细粒度恢复
        "bd_instant_recovery_task" => 'task_uuid', // 瞬时恢复
        "bd_grain_recovery_transport" => 'task_uuid', // 细粒度文件传输信息
        "bd_grain_recovery_share" => 'task_uuid', // 细粒度共享文件信息
        "bd_task_agent_pool", // 任务代理池表
        // // 虚拟机任务相关表
        "vm_task" => 'task_uuid', // 虚拟机任务表
        "vm_machine_list" => 'machine_id', // 虚拟机列表
        "vm_object_list" => 'object_uuid', // 虚拟机对象
        // // 文件模块任务相关表
        "fs_task" => 'task_uuid', // 文件模块任务表
        "fs_running_info" => 'task_uuid', // 文件模块运行表
        "fs_path_list" => 'task_uuid', // 文件模块路径
        "nas_task" => 'task_uuid', // nas 任务表
        // // 数据库任务相关表
        "db_task" => 'task_uuid', // 数据库任务表
        // "db_instance", // 数据库实例(6.0已弃用)
        "db_list" => 'task_uuid', // 数据库列表
        // // 整机任务相关表
        "os_task" => 'task_uuid', // 整机任务表
        "os_list" => 'task_uuid', // 备份/恢复整机列表
        // Microsoft 365任务相关表
        "m365_task" => 'task_uuid', // Microsoft 365任务表
        "m365_object_list" => 'task_uuid', // Microsoft 365列表
        "m365_running_info" => 'task_uuid', // Microsoft 365运行表
        // kubernetes相关任务表
        "kube_task" => 'task_uuid', // kubernetes任务表
        "kube_running_info" => 'task_uuid', // kubernetes运行表
        "kube_object_list" => 'task_uuid', // kubernetes对象
        // // 整机实时相关任务表
        "cdp_vol_task" => 'task_uuid', // 整机实时任务表
        "cdp_vol_task_cache_info" => 'task_uuid', // 卷实时缓存信息表
        "cdp_vol_task_data_consistency_check_info" => 'task_uuid', // 卷任务数据一致性检查信息表
        "cdp_vol_task_io_mapping_vol" => 'task_uuid', // 卷实时I/O映射卷表 
        "cdp_vol_task_progress_info" => 'task_uuid', // 卷实时任务进度信息表
        "cdp_vol_task_running_info" => 'task_uuid', // 卷实时任务运行信息表
        "cdp_vol_task_takeover_app" => 'task_uuid', // 卷实时任务接管应用表
        "cdp_vol_task_takeover_failback_info" => 'task_uuid', // 卷实时接管回切信息表
        "cdp_vol_task_takeover_info" => 'task_uuid', // 卷实时接管信息表
        "cdp_vol_task_takeover_lun" => 'target_id', // 卷实时接管lun信息表
        "cdp_vol_task_takeover_script" => 'task_uuid', // 卷实时接管脚本表
        "cdp_vol_task_takeover_target" => 'task_uuid', // 卷实时接管target表
        "cdp_vol_task_vol" => 'task_uuid', // 卷实时任务卷表
        "cdp_vol_task_exclude_vol" => 'task_uuid', // 卷实时任务过滤卷
        "cdp_vol_task_restore_info" => 'task_uuid', // 卷实时恢复任务配置
        "cdp_vol_task_high_pressure_strategy" => 'task_uuid', // 卷实时高负载配置
        "cdp_vol_task_common_script" => 'task_uuid', // 卷实时任务通用脚本
        "cdp_vol_task_disk" => 'task_uuid', // 卷实时备份目标磁盘设备
        "cdp_vol_task_takeover_part" => 'target_id', // 卷实时接管part信息
        "cdp_vol_backup_agent_invalid_period" => 'backup_agent_id', // 卷实时无效备份时间段
        // 副本任务相关表
        "copy_task" => 'task_uuid', // 副本任务表
        "copy_list" => 'task_uuid', // 副本对象列表
        "copy_running_info" => 'task_uuid', // 副本任务运行表
        // 数据库、文件cdp模块任务相关表
        "cdp_db_host" => 'host_uuid', // 数据实时主机表
        "cdp_db_task" => 'task_uuid', // 数据实时任务表
        "cdp_fs_task" => 'task_uuid', // 文件实时任务表
        // 文件复制相关任务表
        "sync_task" => 'task_uuid', // 文件复制任务表
        "sync_running_info" => 'task_uuid', // 文件复制运行表
        "sync_task_path_list" => 'task_uuid', // 文件复制任务路径清单
        // 数据库实时任务相关表
        "cdp_db_dr_task" => 'task_uuid', // 数据实时任务表
        "cdp_db_dr_task_app_info" => 'task_uuid', // 数据实时任务应用信息表
        "cdp_db_dr_task_cache_info" => 'task_uuid', // 数据实时任务缓存信息表
        "cdp_db_dr_task_progress_info" => 'task_uuid', // 数据实时任务进度信息表
        "cdp_db_dr_task_takeover_info" => 'task_uuid', // 数据实时任务接管信息表
        "cdp_db_dr_task_takeover_failback_info" => 'task_uuid', // 数据实时任务回切信息表
        // 验证任务相关表
        "sr_surebackup" => 'sr_task_id', // 验证任务表
        "sr_surebackup_item" => 'item_id', // 验证任务对象表
        //历史任务
        "bd_history_task" => 'id',
        "bd_storage_monitor" => 'id',
        "sr_surebackup_report" => 'report_id',
        "cdp_vol_history_task" => 'id',
        "os_migration_history" => 'os_migration_id',
        // 系统告警
        "bd_system_alarm" => 'system_alarm_id',
        // 任务告警
        "bd_task_alarm" => 'task_alarm_id',
        // 任务日志
        "bd_task_log" => 'id',
        // 系统日志
        "bd_system_log" => 'id',
        // 备份系统集群日志
        "bd_cluster_ha_log" => 'id',
        // 用户管理
        "bd_user" => 'user_uuid',
        "bd_user_extension" => 'id',
        "bd_user_resource_transfer" => 'id',
        "bd_account_safe" => 'id',
        // 用户组
        "bd_user_group" => 'user_group_uuid',
        "mt_user_user_group" => 'user_uuid',
        // 角色
        "bd_role" => 'role_uuid',
        "mt_user_role" => 'id',
        "mt_user_group_role" => 'id',
        "bd_permission" => 'permission_uuid',
        // 域服务器
        "bd_domain_server" => 'domain_uuid',
        // 虚拟化中心
        "vm_vcenter" => 'vcenter_uuid',
        "mt_platform_node" => 'platform_uuid',
        "vm_tree" => "tree_id",
        "vm_host" => 'host_id',
        // 代理客户端
        "bd_agent" => 'agent_uuid',
        "bd_agent_app" => 'agent_uuid',
        "bd_agent_disk" => 'id',
        "bd_agent_group" => 'group_uuid',
        "bd_agent_vol" => 'id',
        "bd_agent_pool" => 'agent_pool_uuid',
        "bd_agent_pool_list" => 'agent_pool_uuid',
        // nas设备
        "nas_storage_resource" => 'nas_uuid',
        "nas_mount_list" => 'id',
        // 对象存储
        "obs_resource" => 'obs_uuid',
        // Kubernetes集群
        "kube_cluster" => 'cluster_uuid',
        "kube_node" => 'id',
        // Microsoft 365
        "m365_organization" => 'organization_uuid',
        "m365_azure_ad_app" => 'id',
        // hadoop 集群
        "hadoop_cluster" => 'hadoop_cluster_uuid',
        "hadoop_namenode" => 'id',
        // 存储资源
        "bd_storage_resource" => 'storage_uuid',
        // 虚拟演练室
        "sr_network_map_list" => 'virtual_lab_uuid',
        "sr_virtual_lab" => 'virtual_lab_id',
        // 应用组
        "sr_application_group" => 'appgroup_id',
        "sr_application_item" => 'id',
        // 节点管理
        "bd_node" => 'node_id',
        "bd_system_cache_config" => 'system_cache_id',
        "bd_node_network_pool" => 'network_pool_uuid',
        "bd_node_network_pool_list" => 'id',
        "bd_node_pool" => 'node_pool_uuid',
        "bd_node_pool_list" => 'id',
        "bd_system" => 'id',
        "bd_resource_limiting_strategy_node_config" => "id",
        "bd_module_server" => "module_uuid",
        // 集群管理
        "bd_cluster" => 'cluster_uuid', // 集群信息
        "bd_cluster_node" => 'id', // 集群节点信息
        "bd_cluster_node_network" => 'id', // 集群节点网络
        "bd_cluster_log" => "id",
        // 驱动库
        "bd_driver" => 'driver_uuid', // 驱动信息
        "bd_driver_detail" => 'driver_uuid', // 驱动详情
        // 脚本管理
        "bd_script" => 'script_uuid',
        // 备份策略
        "bd_strategy_group" => 'strategy_group_id',
        // 限速策略
        "bd_global_speed_limit_strategy" => 'strategy_uuid',
        // 资源组
        "bd_resource_group" => 'resource_group_uuid',
        "mt_resource_resource_group" => 'id',
        "mt_user_group_resource_group" => 'id',
        "mt_user_resource_group" => 'id',
        "mt_user_resource" => 'id',
        // 存储设备
        "bd_tape_library" => 'id',
        "bd_tape_carriage" => 'id',
        "bd_tape_driver" => 'id',
        "bd_tape_group" => 'group_uuid',
        "bd_shared_storage_node_layout" => "id",
        // 病毒库
        "bd_virus_library" => "uuid",
    ];
    // 需要多键值判断是否插入数据的表
    private $MORE_PRIMARY_KEY = ['vm_machine_list', 'vm_object_list', 'bd_shared_storage_node_layout', 'bd_node_network_pool_list', 'bd_node_pool_list', 'bd_agent_pool_list', 'nas_mount_list'];
    /**
     * 获取备份源树
     * @param mixed $params
     * @return array<array|array{checked: bool, id: mixed, name: mixed, nocheck: bool, open: bool, pid: string, title: mixed, type: int>}
     */
    public function getSystemBackupTreeInfo($params)
    {
        $select_nodes = $params['nodes'];
        $recover_flag = false;
        // 判断是否是恢复源
        if (!empty($select_nodes)) {
            $recover_flag = true;
        }
        $checked_parent_nodes = [];
        // 从配置文件读取备份配置
        $SYSTEM_BACKUP_CONFIG = xphp_get_config('system', 'SYSTEM_BACKUP_CONFIG');
        $return = [];
        // 配置项转化为树结构数组
        if (!empty($SYSTEM_BACKUP_CONFIG)) {
            foreach ($SYSTEM_BACKUP_CONFIG as $config) {
                $return[] = [
                    "id" => $config['id'],
                    "checked" => true,
                    "pid" => '',
                    "title" => $config['title'],
                    "name" => $config['title'],
                    "open" => false,
                    "nocheck" => false,
                    "type" => 1, // 根节点
                    "chkDisabled" => false,
                ];
                if (!empty($config['child'])) {
                    foreach ($config['child'] as $child) {
                        $return[] = [
                            "id" => $child['id'],
                            "checked" => $recover_flag ? (in_array($child['id'], $select_nodes) ? true : false) : true,
                            "pid" => $config['id'],
                            "title" => $child['title'],
                            "name" => $child['title'],
                            "open" => false,
                            "nocheck" => false,
                            "type" => 2, // 子节点
                            "chkDisabled" => $recover_flag ? (in_array($child['id'], $select_nodes) ? false : true) : false,
                        ];
                        if ($recover_flag && in_array($child['id'], $select_nodes) && !in_array($config['id'], $checked_parent_nodes)) {
                            $checked_parent_nodes[] = $config['id'];
                        }
                    }
                }
            }
        }
        // 遍历$return， 如果id在$checked_parent_nodes中，则将checked设置为true
        if ($recover_flag && !empty($checked_parent_nodes)) {
            foreach ($return as &$item) {
                if ($item['type'] === 1) {
                    if (in_array($item['id'], $checked_parent_nodes)) {
                        $item['checked'] = true;
                    } else {
                        $item['checked'] = false;
                        $item['chkDisabled'] = true;
                    }
                }
            }
        }
        return $return;
    }
    /**
     * 手动备份
     * @param mixed $params
     * @return string
     */
    public function systemBackupMaual($params)
    {
        $BACKUP_DIR = xphp_get_config('app', 'TMP_PATH') . "systembak";
        $nodes = $params['nodes'];
        $operate = xphp_get_lang('UI_SBH_PRODUCE_BACKUPFILE');
        // 从配置文件读取备份配置
        $SYSTEM_BACKUP_CONFIG = xphp_get_config('system', 'SYSTEM_BACKUP_CONFIG');
        //准备备份导出环境,清理目录
        $this->clearBackupDir();
        $result = true;
        foreach ($SYSTEM_BACKUP_CONFIG as $config) {
            // 循环子节点
            if (!empty($config['child'])) {
                foreach ($config['child'] as $child) {
                    // 子节点勾选，则调用备份方法
                    if (in_array($child['id'], $nodes)) {
                        $callResult = call_user_func_array([$this, 'backupTableInfo'], [$child]);
                        $result = $result && $callResult;
                    }
                }
            }
        }
        if (!$result) {
            //如果导出失败
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_EXPORT_BACKUPFILE_FAIL'), "warning");
        }
        //写入本次配置文件
        $config = ["nodes" => $nodes];
        $filename = $BACKUP_DIR . "/config";
        $result = file_put_contents($filename, json_encode($config));
        //打包数据文件
        $timeStamp = date("Ymd.His");
        $desZip = $BACKUP_DIR . "/systembak." . $timeStamp . ".bak";
        $cmd = "zip -q -j -r -m -P " . xphp_get_config('system', 'ZIP_PASS') . " " . $desZip . " " . $BACKUP_DIR;
        $result = "";
        exec($cmd, $info, $result);
        if (0 == $result) {
            $node_uuid = $this->getLocalnodeUuid();
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_USER_BACKUP_SYSTEM", [], xphp_get_config('log', 'LOGLEVEL')['NORMAL']);
            return $this->muOpResult(true, $operate, "", "", 0, ["file_path" => $desZip]);
        } else {
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_USER_BACKUP_SYSTEM", [], xphp_get_config('log', 'LOGLEVEL')['ERROR']);
            return $this->muOpResult(false, $operate);
        }
    }
    /**
     * 获取备份系统的主节点
     * @return mixed
     */
    private function getLocalnodeUuid()
    {
        $sql = "SELECT node_uuid FROM bd_node WHERE node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }
    /**
     * 清空到处目录
     * @return bool
     */
    private function clearBackupDir()
    {
        $bakDir = xphp_get_config('app', 'TMP_PATH') . "systembak";

        //删除目录,并新建一个目录
        if (file_exists($bakDir)) {
            //如果目录存在,给目录nginx:nginx权限(自动备份的时候是后台执行的,创建的目录是root:root权限)
            $cmd = "chown nginx:nginx " . $bakDir;
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = ['command' => $cmd];
            $mbResult = (array)$this->mbPFMsg($opName, json_encode($msg), true);
            if ($mbResult['result']) {
                // 删除目录
                $this->removedir($bakDir);
            }
        }
        $result = mkdir($bakDir, 0777);
        return $result;
    }
    /**
     * 删除目录
     * @param mixed $dir
     * @return bool
     */
    function removedir($dir)
    {
        if (is_dir($dir) && !is_link($dir)) {
            if ($dh = opendir($dir)) {
                while (($sf = readdir($dh)) !== false) {
                    if ('.' == $sf || '..' == $sf) {
                        continue;
                    }
                    $this->removedir($dir . '/' . $sf);
                }
                closedir($dh);
            }
            return rmdir($dir);
        }
        return @unlink($dir);
    }

    /**
     * 备份整张表
     * @param mixed $params
     * @return bool|int
     */
    public function backupTableInfo($params)
    {
        $BACKUP_DIR = xphp_get_config('app', 'TMP_PATH') . "systembak";
        $vm_keys = array_keys(xphp_get_config('vendor')['VMHYPERVISORTYPE']);
        $public_keys = array_keys(xphp_get_config('vendor')['CLOUDHYPERVISORTYPE']);
        $private_keys = array_keys(xphp_get_config('vendor')['PUBLICCLOUDHYPERVISORTYPE']);
        $BACKUP_PART_TABLE_CONFIG = [
            "vcenter_Info_vm_vcenter" => "SELECT * FROM vm_vcenter WHERE hypervisor_type IN " . "('" . implode("','", $vm_keys) . "')",
            "private_cloud_vm_vcenter" => "SELECT * FROM vm_vcenter WHERE hypervisor_type IN " . "('" . implode("','", $public_keys) . "')",
            "public_cloud_vm_vcenter" => "SELECT * FROM vm_vcenter WHERE hypervisor_type IN " . "('" . implode("','", $private_keys) . "')",
            "lan_free_bd_storage_resource" => "SELECT * FROM bd_storage_resource WHERE lan_free_flag = 1",
            "storage_resource_bd_storage_resource" => "SELECT * FROM bd_storage_resource WHERE lan_free_flag = 2",
        ];
        $database_table = $params['database_table'];
        $tables = []; // 组装最终的结构
        if (!empty($database_table)) {
            // 循环查询所有需要备份表格
            foreach ($database_table as $table) {
                // 一表存多个类型，备份需要拆分开
                if (!empty($BACKUP_PART_TABLE_CONFIG[$params['id'] . '_' . $table])) {
                    $sql = $BACKUP_PART_TABLE_CONFIG[$params['id'] . '_' . $table];
                } else {
                    // 备份整张表
                    $sql = "SELECT * FROM {$table}";
                }
                $tables[$table] = $sql;
            }
        } else {
            $this->writeSystemBackupLog($params['id'] . ' has no database table!');
            return false;
        }
        //写入信息到文件
        $filename = $BACKUP_DIR . "/" . $params['id'];

        return $this->backupMoreTables($filename, $tables);
    }



    /**
     * 数据备份 - 复用现有单表导出方法
     * // 表配置
    $tables = [
    'bd_history_task' => 'SELECT * FROM bd_history_task',
    'bd_storage_monitor' => 'SELECT * FROM bd_storage_monitor',
    'sr_sure_backup_report' => 'SELECT * FROM sr_sure_backup_report',
    'cdp_vol_history_task' => 'SELECT * FROM cdp_vol_history_task',
    'os_migration_history' => 'SELECT * FROM os_migration_history'
    ];
     */
    private function backupMoreTables($filename, $tables = []): bool
    {
        // 临时增加内存限制
        $originalMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1024M');
        set_time_limit(0);          // 取消脚本超时

        try {

            // 创建目录
            $dir = dirname($filename);
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new \RuntimeException("无法创建目录: {$dir}");
            }

            $fp = fopen($filename, 'w');
            if (!$fp) {
                throw new \RuntimeException("无法打开文件: {$filename}");
            }


            // 开始JSON
            fwrite($fp, "{");

            $firstTable = true;
            $pdo = ($this->_basedb);
            foreach ($tables as $tableName => $sql) {
                if (!$firstTable) {
                    fwrite($fp, ",");
                }

                // 写入表名和开始数组
                fwrite($fp, "\"{$tableName}\":[");

                // 直接调用单表导出的核心逻辑
                $rowsExported = $this->exportTableData($fp, $sql, $tableName, 5000, $pdo);

                // 结束数组
                fwrite($fp, "]");

                $firstTable = false;

                error_log("表 {$tableName} 导出: {$rowsExported} 行");

                fflush($fp);
                usleep(50000);
            }

            // 结束JSON
            fwrite($fp, "}");
            fclose($fp);

            // 立即检查文件
            clearstatcache(true, $filename);
            if (file_exists($filename)) {
                $size = filesize($filename);
                $this->writeSystemBackupLog("备份完成，文件: {$filename}, 大小: {$size} 字节");
                error_log("备份完成，文件: {$filename}, 大小: {$size} 字节");
            } else {
                $this->writeSystemBackupLog("错误：文件未生成");
                error_log("错误：文件未生成");
            }

            return true;
        } catch (\Exception $e) {
            $this->writeSystemBackupLog("备份失败: " . $e->getMessage());
            error_log("备份失败: " . $e->getMessage());

            return false;
        } finally {
            ini_set('memory_limit', $originalMemoryLimit);
        }
    }


    /**
     * 导出表数据 - 优化版，增加内存清理
     */
    private function exportTableData_bak($fp, string $sql, string $tableName, int $batchSize): int
    {
        $offset = 0;
        $totalRows = 0;
        $hasData = false;
        $hasWrittenDataInThisTable = false;
        $startMemory = memory_get_usage(true);

        error_log("开始导出表 {$tableName}，初始内存: " . round($startMemory / 1024 / 1024, 2) . "MB，批次大小: {$batchSize}");

        // 监控内存使用
        $memoryCheckInterval = 100; // 每100行检查一次内存

        try {
            do {
                // 构建分页查询
                $batchSql = $this->buildBatchSql($sql, $offset, $batchSize);

                // 监控查询前内存
                $beforeQueryMemory = memory_get_usage(true);

                try {
                    $batchData = $this->dbSelect($batchSql, [], \PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    error_log("查询表 {$tableName} 失败 [offset: {$offset}, limit: {$batchSize}]: " . $e->getMessage());
                    break;
                }

                // 监控查询后内存
                $afterQueryMemory = memory_get_usage(true);
                $queryMemoryUsed = $afterQueryMemory - $beforeQueryMemory;
                error_log("查询批次 {$offset}-" . ($offset + $batchSize) . " 内存使用: " . round($queryMemoryUsed / 1024 / 1024, 2) . "MB");

                if (empty($batchData)) {
                    error_log("表 {$tableName} 查询结束，无更多数据");
                    break;
                }

                $hasData = true; // 标记有数据

                // 写入当前批次
                $batchCount = count($batchData);
                for ($i = 0; $i < $batchCount; $i++) {
                    $row = $batchData[$i];

                    // 只有在表内已经写过数据时才需要加逗号
                    if ($hasWrittenDataInThisTable) {
                        fwrite($fp, ",");
                    }

                    // JSON编码
                    $jsonRow = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if ($jsonRow === false) {
                        $row = $this->cleanInvalidChars($row);
                        $jsonRow = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    // 写入数据
                    $writeResult = fwrite($fp, $jsonRow);
                    if ($writeResult === false) {
                        error_log("警告：写入表 {$tableName} 数据失败");
                    }

                    // 标记表内已经写过数据
                    $hasWrittenDataInThisTable = true;

                    // 及时释放当前行内存
                    unset($batchData[$i]);

                    // 定期监控内存
                    if (($totalRows + $i) % $memoryCheckInterval === 0) {
                        $currentMemory = memory_get_usage(true);
                        $usedMemory = round(($currentMemory - $startMemory) / 1024 / 1024, 2);
                        error_log("已处理 " . ($totalRows + $i) . " 行，内存增加: {$usedMemory}MB");

                        // 如果内存增长过快，强制垃圾回收
                        if ($usedMemory > 50) { // 超过50MB
                            if (gc_enabled()) {
                                gc_collect_cycles();
                                error_log("强制垃圾回收，当前内存: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB");
                            }
                        }
                    }
                }

                $totalRows += $batchCount;
                $offset += $batchSize;

                // 完全释放批次数据内存
                unset($batchData);

                // 强制垃圾回收（每批结束后）
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles(); // 主动回收循环引用
                }

                // 定期刷新文件缓冲区
                if ($offset % ($batchSize * 5) === 0) {
                    fflush($fp);
                }

                // 如果内存使用过高，自动减小批次大小
                $currentMemory = memory_get_usage(true);
                if ($currentMemory > 200 * 1024 * 1024) { // 超过200MB
                    $newBatchSize = max(100, intval($batchSize * 0.7)); // 减少30%
                    if ($newBatchSize < $batchSize) {
                        error_log("内存使用过高 (" . round($currentMemory / 1024 / 1024, 2) . "MB)，减小批次大小: {$batchSize} -> {$newBatchSize}");
                        $batchSize = $newBatchSize;
                    }
                }

            } while ($batchCount === $batchSize);

            // 如果是空表，记录日志
            if (!$hasData) {
                error_log("表 {$tableName} 无数据，输出空数组 []");
            }

            $endMemory = memory_get_usage(true);
            $totalMemoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);
            error_log("表 {$tableName} 导出完成: {$totalRows} 行，内存变化: {$totalMemoryUsed}MB");

            return $totalRows;

        } catch (Exception $e) {
            error_log("导出表 {$tableName} 数据异常: " . $e->getMessage());
            return $totalRows; // 返回已处理的行数
        }
    }


    private function exportTableData($fp, string $sql, string $tableName, int $batchSize, $pdo): int
    {
        $offset = 0;
        $totalRows = 0;
        $hasWrittenDataInThisTable = false;

        // 构建带 ORDER BY 的基础 SQL（不含 LIMIT）
        $baseSql = $sql;
        if (stripos($sql, 'ORDER BY') === false) {
            $baseSql .= " ORDER BY 1";
        }

        do {
            // 构建分页查询
            $batchSql = $this->buildBatchSql($sql, $offset, $batchSize);

            // 准备分页语句（非缓冲）
            $stmt = $pdo->prepare($batchSql, [
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
            ]);
            $stmt->execute();

            $rowCountInBatch = 0;
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($hasWrittenDataInThisTable) {
                    fwrite($fp, ",");
                }

                $jsonRow = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($jsonRow === false) {
                    $row = $this->cleanInvalidChars($row);
                    $jsonRow = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                fwrite($fp, $jsonRow);

                $hasWrittenDataInThisTable = true;
                $totalRows++;
                $rowCountInBatch++;

                // 可选：每1000行刷一次磁盘
                if ($totalRows % 1000 === 0) {
                    fflush($fp);
                }
            }

            $stmt->closeCursor(); // 释放 MySQL 结果集
            $offset += $batchSize;

        } while ($rowCountInBatch === $batchSize);

        return $totalRows;
    }

    /**
     * 构建分批查询SQL
     */
    private function buildBatchSql($sql, $offset, $limit): string
    {
        // 检查SQL是否已有ORDER BY
        $hasOrderBy = stripos($sql, 'ORDER BY') !== false;

        // 如果没有ORDER BY，添加默认排序以确保分页稳定
        if (!$hasOrderBy) {
            // 尝试找到主键或第一个字段
            $sql .= " ORDER BY 1";
        }

        // 添加LIMIT子句
        return $sql . " LIMIT {$offset}, {$limit}";
    }

    /**
     * 清理无效字符
     */
    private function cleanInvalidChars(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                // 清理无效UTF-8字符
                $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

                // 移除控制字符（除了制表符、换行符、回车符）
                $row[$key] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $row[$key]);
            }
        }
        return $row;
    }





    /**
     * 自动备份配置文件
     * @param mixed $params
     * @return string
     */
    public function systemBackupAutoConfig($params)
    {
        $auto_flag = $params['auto_flag'];
        $backup_time = $params['backup_time'];
        $reserved_num = $params['reserved_num'];
        $node_uuid = $params['node_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $nodes = $params['nodes'];
        //写入配置文件
        $BACKUP_DIR = xphp_get_config('app', 'TMP_PATH') . "autobak.config";
        $info = [
            "auto_flag" => $auto_flag,
            "backup_time"  => v1_formart_time($backup_time),
            "reserved_num"  => $reserved_num,
            "node_uuid"  => $node_uuid,
            "storage_uuid"  => $storage_uuid,
            "nodes"  => $nodes,
            "server_addr" => $_SERVER['SERVER_ADDR'],
        ];

        $operate = xphp_get_lang('UI_SBH_AUTO_SETTING');
        $result = file_put_contents($BACKUP_DIR, json_encode($info));
        if ($result === false) {
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_SETTINGS", [], xphp_get_config('log', 'LOGLEVEL')['ERROR']);
            return $this->muOpResult(false, $operate);
        }
        $this->systemLog("SYSTEM_LOG_SYSBAKREC_SETTINGS", [], xphp_get_config('log', 'LOGLEVEL')['NORMAL']);
        return $this->muOpResult(true, $operate);
    }
    /**
     * 获取自动备份点列表
     * @param mixed $params
     * @return array
     */
    public function getSystemBackupList($params)
    {
        $sql = "SELECT bsbd.backup_uuid, bsbd.file_name, bsbd.file_size, bsbd.file_path,  bsbd.backup_time, bsbd.config, bn.host_name, bn.ip, bn.node_nickname, bsr.storage_nickname
                    FROM bd_system_backup_data bsbd, bd_storage_resource bsr, bd_node bn
                    WHERE bsbd.node_uuid = bn.node_uuid AND bsbd.storage_uuid = bsr.storage_uuid ";
        $sqlCount = "SELECT count(bsbd.backup_uuid) AS total FROM bd_system_backup_data bsbd, bd_storage_resource bsr, bd_node bn
                    WHERE bsbd.node_uuid = bn.node_uuid AND bsbd.storage_uuid = bsr.storage_uuid";
        $sortArray = [
            'backup_time' => 'bsbd.id',
            'file_name' => 'bsbd.file_name',
            'file_size' => 'bsbd.file_size',
        ];
        if (!empty($params['search'])) {
            $sql .= " AND bsbd.file_name like '%{$params['search']}%' ";
            $sqlCount .= " AND bsbd.file_name like '%{$params['search']}%' ";
        }
        if (!empty($params['sort'] && $params['order'])) {
            $sql .= " order by {$sortArray[$params['sort']]} {$params['order']}";
        }
        $sql .= " limit {$params['offset']}, {$params['limit']}";
        $data = $this->dbSelect($sql, []);
        $dataCount = $this->dbSelect($sqlCount, []);
        $return = [];
        $return['rows'] = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $parts = explode(".", $d['file_name']);
                $timestamp = $parts[1] . "." . $parts[2]; // 20250614.161322
                $datetime = DateTime::createFromFormat('Ymd.His', $timestamp);
                $standardDate = $datetime->format('Y-m-d H:i:s');
                $return['rows'][] = [
                    "id" => $d['backup_uuid'],
                    "file_name" => $d['file_name'],
                    "file_size" => v1_calsize($d['file_size'], true),
                    "backup_time" => $standardDate,
                    "node_ip" => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                    "storage" => $d['storage_nickname'],
                    "file_path" => $d['file_path'],
                    "ip" => $d['ip'],
                ];
            }
        }
        $return['total'] = $dataCount[0]['total'];
        return $return;
    }
    /**
     * 组合节点显示名称
     * @param mixed $ip
     * @param mixed $nickname
     * @param mixed $hostname
     * @return string
     */
    private function getNodeShowName($ip, $nickname, $hostname)
    {
        if (empty($ip) && empty($nickname) && empty($hostname)) {
            return "--";
        }
        //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
        if ($ip == $nickname || empty($nickname)) {
            $name = $hostname . '(' . $ip . ')';
        } else {
            $name = $nickname . '(' . $ip . ')';
        }
        return $name;
    }
    /**
     * 上传恢复源文件
     *
     * 该函数用于处理上传的系统恢复文件（.bak），支持分片上传。
     * 文件上传后会进行基本校验（类型、大小等），并将其移动到指定的恢复目录中。
     * 同时支持大文件上传时的内存限制调整和分片合并逻辑。
     *
     * @return {} 无返回值，直接输出 JSON-RPC 格式的响应或终止脚本执行
     */
    public function uploadRecoverySrc()
    {
        //临时修改一下配置,适应直接上传大文件
        ini_set('memory_limit', '2000m');
        // 获取配置信息
        $IMPORT_INFO = xphp_get_config('system', 'IMPORT_INFO');
        $files = $_FILES['file'];
        $targetDir = xphp_get_config('app', 'TMP_PATH') . "systemrec";
        $uploadDir = xphp_get_config('app', 'TMP_PATH') . "systemrec";
        // 获取上传文件名
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        // 构造文件路径
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // 获取分片信息
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1; // 分片总数

        // 打开临时文件写入当前分片数据
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        // 判断文件是否正常上传
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            // 打开上传文件读取内容
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else { // 使用输入流方式读取数据
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        // 分块读取并写入临时文件
        while ($buff = fread($in, 5 * 1024 * 1024)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);
        // 重命名临时文件为正式分片文件
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
        // 判断是否所有分片都上传成功，只有全部成功才能进行合并
        $done = true;
        // 循环判断所有分片是否已经上传完成
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
            }
        }
        // 如果所有分片上传完成，则合并所有分片为完整文件
        if ($done) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }
            if (flock($out, LOCK_EX)) {
                // 遍历所有分片并依次写入最终文件
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        //赋予文件读写执行权限，重新获取一次
                        chmod("{$filePath}_{$index}.part", 0755);
                        $in = @fopen("{$filePath}_{$index}.part", "rb");
                    }
                    while ($buff = fread($in, 5 * 1024 * 1024)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part"); // 删除已合并的分片
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }
        // 处理上传后的文件验证与操作
        $operate = xphp_get_lang('UI_SBH_UPLOAD_BACKUPFILE');
        if ($done) {
            //检测上传文件状态
            if (0 != $files['error']) {
                return $this->muOpResult(false, $operate);
            }
            // 检测文件后缀名是否为 .bak
            $arr = explode(".", $files['name']);
            if ($arr[count($arr) - 1] != "bak") {
                return $this->muOpResult(false, $operate,  xphp_get_lang('UI_SBH_ERROR_FILE_TYPE'), 'error');
            }
            // 检测上传文件大小是否超出限制
            if ($files['size'] > $IMPORT_INFO['uploadfile']['size']) {
                return $this->muOpResult(false, $operate,  xphp_get_lang('UI_SBH_FILE_OVERSIZE'), 'error');
            }
            // 恢复源目录
            $data_back_DIR = xphp_get_config('app', 'TMP_PATH') . "systemrec/data.bak";
            // 重命名文件为 data.bak
            $cmd = "mv " . xphp_get_config('app', 'TMP_PATH') . "systemrec/" . $files['name'] . " " . $data_back_DIR;
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            // 执行命令
            $msg = ['command' => $cmd];
            $this->mbPFMsg($opName, json_encode($msg), true);
            //保存临时文件
            $this->muOpResult(true, $operate);
        } else {
            $this->muOpResult(false, $operate);
        }
        // 输出会话中的文件名并终止脚本
        die($_SESSION['fileName']);
    }

    /**
     * 清理恢复目录
     * @return bool
     */
    public function cleanRecoveryDir()
    {
        // 恢复源目录
        $BACKUP_DIR = xphp_get_config('app', 'TMP_PATH') . "systemrec/";
        // 删除旧文件
        $cmd = "rm -rf " . $BACKUP_DIR;
        // 新建文件
        $cmd .= ";mkdir " . $BACKUP_DIR;
        // 修改权限
        $cmd .= ";chmod -R 777 " . $BACKUP_DIR . ";chown nginx:nginx " . $BACKUP_DIR;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        // 执行命令
        $msg = ['command' => $cmd];
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        return $mbResult['result'];
    }
    /**
     * 检查并创建自动备份存储目录
     * @param mixed $node_uuid
     * @param mixed $storage_uuid
     * @return bool
     */
    public function systemBackupAuto($params)
    {
        $nodes = $params['nodes'];
        $node_uuid = $params['node_uuid'];
        $storage_uuid = $params['storage_uuid'];
        $server_addr = $params['server_addr'];
        $reserved_num = intval($params['reserved_num']);
        $backup_uuid = xphp_uuid();
        $LOG_LEVEL = xphp_get_config('log', 'LOGLEVEL');
        $this->writeSystemBackupLog('start to check and create backup storage file...');
        //创建并检查存储目录
        $result = $this->checkAndCreateAutoBakDir($node_uuid, $storage_uuid);
        if (!$result) {
            $this->writeSystemBackupLog('check auto backup storage dir failure!');
            //写日志
            $this->writeLog("check auto backup storage dir failure!");
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE1", [], $LOG_LEVEL['ERROR']);
            return false;
        }
        $this->writeSystemBackupLog('check auto backup storage dir success!');

        $this->writeSystemBackupLog('start to export data into file! ');
        //生成备份文件,并生成相对URL地址
        $result = $this->exportAutoBakFile($backup_uuid, $nodes);
        if (!$result) {
            //写日志
            $this->writeLog("export auto backup file failure!");
            $this->writeSystemBackupLog('export data into file failure! ');
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE2", [], $LOG_LEVEL['ERROR']);
            return false;
        }
        $this->writeSystemBackupLog('export data into file success! ');

        $this->writeSystemBackupLog('start to save auto backup file to storage! ');
        //保存备份数据到存储目录
        $result = $this->saveAutoBakFileToStorage($node_uuid, $server_addr);
        if (!$result) {
            //写日志
            $this->writeLog("save auto backup file to storage failure!");
            $this->writeSystemBackupLog('save auto backup file to storage failure! ');
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE3", [], $LOG_LEVEL['ERROR']);
            return false;
        }
        $this->writeSystemBackupLog('save auto backup file to storage success! ');

        $this->writeSystemBackupLog('start to write backup info into database! ');
        //写入备份数据到数据库
        $result = $this->writeAutoBakToDatabase($backup_uuid, $node_uuid, $storage_uuid, $nodes);
        if (!$result) {
            //写日志
            $this->writeLog("witer backup info into databases failure!");
            $this->writeSystemBackupLog('start to write backup info into database failure! ');
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE4", [], $LOG_LEVEL['ERROR']);
            return false;
        }
        $this->writeSystemBackupLog('start to write backup info into database success! ');
        
        $this->writeSystemBackupLog('start to check reserve strategy! ');
        //检查保留配置,删除过期保留备份点
        $result = $this->deleteOldAutoBakData($node_uuid, $reserved_num);
        if (!$result) {
            //写日志
            $this->writeLog("delete old backup point failure!");
            $this->writeSystemBackupLog('delete old backup point failure!');
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE5", [], $LOG_LEVEL['ERROR']);
            return false;
        }
        $this->writeSystemBackupLog('reserve strategy success!');
        $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_SUCCESS", [], $LOG_LEVEL['NORMAL']);
        return true;
    }
    /**
     * 准备自动备份目录
     * @param mixed $node_uuid
     * @param mixed $storage_uuid
     */
    private function checkAndCreateAutoBakDir($node_uuid, $storage_uuid)
    {
        // 检查存储挂载点
        $mountPoint = $this->getAutoBakStoragePath($storage_uuid);
        if (empty($mountPoint)) {
            $this->writeSystemBackupLog('check storage mount point failure!');
            return false;
        };
        $this->writeSystemBackupLog('check storage mount point success!');

        $this->autoBackupStoragePath = $mountPoint . "/" . "systembak";
        //检查目录是否存在,这里涉及到节点,只能通过命令去检测
        $cmd = "test -d " . $this->autoBackupStoragePath . "; echo $?;";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = ['command' => $cmd];
        $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);

        $this->writeLog("autoBackupStoragePath:" . $this->autoBackupStoragePath);
        
        if (!$mbResult['result']) {
            //获取失败,可能是节点不可用,返回失败
            $this->writeSystemBackupLog('check storage path failure!');
            return false;
        }
        $this->writeSystemBackupLog('check storage path success!');
        $flag = trim($mbResult['msg']['detail']);
        if ("1" == $flag) {
            $this->writeSystemBackupLog('check storage path is not exist!');
            //如果目录不存在,创建目录
            $cmd = "mkdir " . $this->autoBackupStoragePath;
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $msg = ['command' => $cmd];
            $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);
            if(!$mbResult['result']){
                $this->writeSystemBackupLog('create storage path failure!');
            }
            $this->writeSystemBackupLog('create storage path success!');
            return $mbResult['result'];
        }
        return true;
    }
    /**
     * 检查存储挂载点
     * @param mixed $storage_uuid
     */
    private function getAutoBakStoragePath($storage_uuid)
    {
        $sql = "select mount_point from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storage_uuid));
        $mountPoint = $data[0]['mount_point'];

        $this->writeLog("mountPoint:" . $mountPoint);
        return $mountPoint;
    }
    /**
     * 导出自动备份文件
     * @param mixed $backup_uuid
     * @param mixed $nodes
     * @return bool
     */
    private function exportAutoBakFile($backup_uuid,  $nodes)
    {
        $BACKUP_DIR = xphp_get_config('app', 'TMP_PATH') . "systembak";
        if (empty($nodes)) {
            $this->writeSystemBackupLog('backup nodes is empty!');
            return false;
        };
        //准备备份导出环境,清理目录,导出是先导出到本地
        $cleanResult = $this->clearBackupDir();
        if(!$cleanResult){
            $this->writeSystemBackupLog('clean backup dir failure!');
            return false;
        }
        $this->writeSystemBackupLog('clean backup dir success!');
        //导出数据库数据
        $SYSTEM_BACKUP_CONFIG = xphp_get_config('system', 'SYSTEM_BACKUP_CONFIG');
        $result = true;
        
        $this->writeSystemBackupLog('start to backup data table!');
        foreach ($SYSTEM_BACKUP_CONFIG as $config) {
            //按配置文件第一层遍历,如果选择了此项,则调用此项的备份方法,进入备份流程
            // 循环子节点
            if (!empty($config['child'])) {
                foreach ($config['child'] as $child) {
                    // 子节点勾选，则调用备份方法
                    if (in_array($child['id'], $nodes)) {
                        $this->writeSystemBackupLog('backup data table ' . $child['id']);
                        $callResult = call_user_func_array([$this, 'backupTableInfo'], [$child]);
                        if(!$callResult){
                            $this->writeSystemBackupLog('backup data table ' . $child['id'] . 'failure');
                        } else {
                            $this->writeSystemBackupLog('backup data table ' . $child['id'] . 'success');
                        }
                        $result = $result && $callResult;
                    }
                }
            }
        }
        if (!$result) {
            $this->writeSystemBackupLog('start to backup all data table failure');
            //如果导出失败
            return false;
        }
        $this->writeSystemBackupLog('start to backup all data table success');

        //写入本次配置文件
        $config = ["nodes" => $nodes,];
        $filename = $BACKUP_DIR . "/config";
        $result = file_put_contents($filename, json_encode($config));
        if(!$result){
            $this->writeSystemBackupLog('save backup config failure');
            return false;
        }
        $this->writeSystemBackupLog('save backup config success');

        //打包数据文件
        $nowTime = time();
        $timeStamp = date("Ymd.His", $nowTime);
        $this->autoBackupFileName = "systembak." . $timeStamp . ".bak";
        $this->autoBackupTime = $timeStamp;
        $desZip = $BACKUP_DIR . "/systembak." . $timeStamp . ".bak";
        $desSelfMarkPath = $BACKUP_DIR . "/" . "systembak." . $timeStamp . ".self";

        //生成备份文件
        $cmd = "zip -q -j -r -m -P " . xphp_get_config('system', 'ZIP_PASS') . " " . $desZip . " " . $BACKUP_DIR;
        $result = "";
        exec($cmd, $info, $result);
        if (0 == $result) {
            $this->writeSystemBackupLog('zip backup file success');
            $this->systemAutoUrl = xphp_get_config('app', 'TMP_PATH_RE') . "systembak/systembak." . $timeStamp . ".bak";
        } else {
            $this->writeSystemBackupLog('zip backup file failure');
            return false;
        }
        //生成自描述文件
        $nodes = ["nodes" => $nodes,];
        $data = [
            "backup_uuid" => $backup_uuid,
            "file_name" => $this->autoBackupFileName,
            "file_size" => filesize($desZip),
            "backup_time" => $this->autoBackupTime,
            "config" => json_encode($nodes),
        ];
        $this->writeLog("data:" . $data);
        $result = file_put_contents($desSelfMarkPath, json_encode($data));

        if (false !== $result) {
            $this->writeSystemBackupLog('save self file success');
            $this->systemAutoSelfMarkUrl = xphp_get_config('app', 'TMP_PATH_RE') . "systembak/systembak." . $timeStamp . ".self";
        } else {
            $this->writeSystemBackupLog('save self file failure');
            return false;
        }

        return true;
    }
    /**
     * 保存自动备份文件到存储
     * @param mixed $nodeuuid
     * @param mixed $serverAddr
     * @return bool
     */
    private function saveAutoBakFileToStorage($nodeuuid, $serverAddr)
    {
        $vendor = xphp_get_config('app', 'SYSTEM_INFO')['vendor'];
        $cmd = "/opt/$vendor/sysget \-\-no-check-certificate -P " . $this->autoBackupStoragePath . " https://" . $serverAddr . $this->systemAutoUrl;
        $cmd .= ";" . "/opt/$vendor/sysget \-\-no-check-certificate -P " . $this->autoBackupStoragePath . " https://" . $serverAddr . $this->systemAutoSelfMarkUrl;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = ['command' => $cmd];
        $this->writeLog("command:" . $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            //保存失败
            return false;
        }
        return true;
    }
    /**
     * 自动备份文件信息保存到数据库
     * @param mixed $backupuuid
     * @param mixed $nodeuuid
     * @param mixed $storageuuid
     * @param mixed $nodes
     * @return bool
     */
    private function writeAutoBakToDatabase($backupuuid, $nodeuuid, $storageuuid, $nodes)
    {
        $datetime = date('Y-m-d H:i:s');
        // 写入数据库
        $sql = "INSERT INTO bd_system_backup_data (backup_uuid, file_name, file_size, file_path, backup_time, node_uuid, storage_uuid, config) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";
        $fullPath = $this->autoBackupExportFilePath . "/" . $this->autoBackupFileName;
        $this->writeLog("fullPath:" . $fullPath);
        $storagePath = $this->autoBackupStoragePath . "/" . $this->autoBackupFileName;
        $file_size = 0;
        //先检查文件的大小
        $opName = "NODE_SYS_OP_GET_FILE_SIZE";
        $msg = ['file_path' => $storagePath];
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        if ($mbResult['result']) {
            $this->writeSystemBackupLog('get backup file size success! ');
            $file_size = $mbResult['msg']['file_size'];
        } else {
            $this->writeSystemBackupLog('get backup file size failure! ');
            return false;
        }
        $this->writeLog("file_size:" . $file_size);
        $config = ["nodes" => $nodes];
        $sqlParams = [$backupuuid, $this->autoBackupFileName, $file_size, $storagePath, $datetime, $nodeuuid, $storageuuid, json_encode($config)];
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }
    /**
     * 删除过期自动备份点，执行保留策略
     * @param mixed $nodeuuid
     * @param mixed $reservedNum
     * @return bool
     */
    private function deleteOldAutoBakData($nodeuuid, $reservedNum)
    {
        //从数据库查找过期备份点
        $sql = "select id, file_path, node_uuid from bd_system_backup_data order by id desc limit ?, 999";
        $data = $this->dbSelect($sql, array($reservedNum));
        if (empty($data)) {
            $this->writeSystemBackupLog('no expire backup info');
            return true;
        };

        $vendor = xphp_get_config('app', 'SYSTEM_INFO')['vendor'];
        //由于用户可能在备份中途更换备份节点和存储,所以,这里需要一个一个点的删除,不能全部一个命令删除 
        $cmd = "";
        //调用删除命令删除文件
        foreach ($data as $d) {
            $file_path = $d['file_path'];
            //检查一下全路径的格式,删除是一个危险的操作,方式误删
            //标准的格式如:/backup_storage/e406b71d-31a2-40bb-a80f-2a8d88b5c188/systembak/systembak.20210427.195215.bak
            $startPathStr = substr($file_path, 0, 16);
            $endPathStr = substr($file_path, -3, 3);
            if ("/backup_storage/" == $startPathStr && "bak" == $endPathStr) {
                $selfMarkFilePath = substr($file_path, 0, -3) . "self";     //删除self文件
                //检查标准:以"/backup_storage/"开始,以"bak"结束
                $cmd = "/opt/$vendor/sysdm -rf " . $file_path . ";" . "/opt/$vendor/sysdm -rf " . $selfMarkFilePath . ";";
                $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
                $msg = ['command' => $cmd];
                $mbResult = $this->mbNodeMsg($opName, $d['node_uuid'], json_encode($msg), true, false);
                if ($mbResult['result']) {
                    //删除数据库记录
                    $sql = "delete from bd_system_backup_data where id = ?";
                    $result = $this->dbExec($sql, [$d['id']]);
                    if (!$result) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * 获取自动备份配置
     * @param mixed $params
     */
    public function getAutoBakConfig($params)
    {
        $filePath = xphp_get_config('app', 'TMP_PATH') . "autobak.config";
        //如果配置文件不存在
        $fileInfo = ["auto_flag" => false,];
        if (!file_exists($filePath)) {
            //如果配置文件不存在
            return $fileInfo;
        }
        //如果存在
        $fileInfo = file_get_contents($filePath);
        return json_decode($fileInfo);
    }
    /**
     * 删除自动备份点
     * @param mixed $params
     * @return string
     */
    public function deleteSystemBackupAutoPoint($params)
    {
        $uuid = $params['uuid'];
        $LOG_LEVEL = xphp_get_config('log', 'LOGLEVEL');

        $vendor = xphp_get_config('app', 'SYSTEM_INFO')['vendor'];
        $operate = xphp_get_lang('UI_SBH_REMOVE_BACKUPFILE');
        $uuidStr = implode("','", $uuid);
        $sql = "select id, file_path, node_uuid from bd_system_backup_data where backup_uuid in ('" . $uuidStr . "')";
        $data = $this->dbSelect($sql, [], \PDO::FETCH_ASSOC);
        foreach ($data as $d) {
            $file_path = $d['file_path'];
            //检查一下全路径的格式,删除是一个危险的操作,方式误删
            //标准的格式如:/backup_storage/e406b71d-31a2-40bb-a80f-2a8d88b5c188/systembak/systembak.20210427.195215.bak
            $startPathStr = substr($file_path, 0, 16);
            $endPathStr = substr($file_path, -3, 3);
            if ("/backup_storage/" == $startPathStr && "bak" == $endPathStr) {
                $selfMarkFilePath = substr($file_path, 0, -3) . "self";     //删除self文件
                //检查标准:以"/backup_storage/"开始,以"bak"结束
                $cmd = "/opt/$vendor/sysdm -rf " . $file_path . ";" . "/opt/$vendor/sysdm -rf " . $selfMarkFilePath . ";";
                $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
                $msg = ['command' => $cmd];
                $mbResult = $this->mbNodeMsg($opName, $d['node_uuid'], json_encode($msg), true, false);
                if ($mbResult['result']) {
                    //删除数据库记录
                    $sql = "delete from bd_system_backup_data where id = ?";
                    $result = $this->dbExec($sql, [$d['id']]);
                    if (!$result) {
                        $this->systemLog("SYSTEM_LOG_SYSBAKREC_DELETE_TIMEPOINT", [], $LOG_LEVEL['ERROR']);
                        return $this->muOpResult(false, $operate);
                    }
                } else {
                    $this->systemLog("SYSTEM_LOG_SYSBAKREC_DELETE_TIMEPOINT", [], $LOG_LEVEL['ERROR']);
                    return $this->muOpResult(false, $operate);
                }
            }
        }

        $this->systemLog("SYSTEM_LOG_SYSBAKREC_DELETE_TIMEPOINT", [], $LOG_LEVEL['NORMAL']);
        return $this->muOpResult(true, $operate);
    }
    /**
     * 获取上传文件的恢复树
     * @param mixed $params
     * @return array|string
     */
    public function getUploadSrcTree($params)
    {
        //检查恢复文件是否存在
        $operate = xphp_get_lang('UI_SBH_GET_RECOVERY_ITEM');
        $RECOVERY_DIR = xphp_get_config('app', 'TMP_PATH') . "systemrec/";
        $zipFile = $RECOVERY_DIR . "data.bak";
        if (!file_exists($zipFile)) {
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_CONFIGFILE_NOT_EXIST'), 'warning');
        }
        //解压恢复文件
        $cmd = "unzip -P " . xphp_get_config('system', 'ZIP_PASS') . " " . $zipFile . " -d " . $RECOVERY_DIR;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = ['command' => $cmd];
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_FILE_DECOMPRESS_FAIL'), 'warning');
        }
        //获取配置文件
        $content = file_get_contents($RECOVERY_DIR . "config");
        $select_node = json_decode($content, true);
        $params = ['nodes' => $select_node['nodes']];

        return $this->getSystemBackupTreeInfo($params);
    }
    /**
     * 检查恢复源信息
     * @param mixed $params
     * @return string
     */
    public function checkRecData($params)
    {
        // 从配置文件读取备份配置
        $SYSTEM_RECOVERY_TYPE = xphp_get_config('system', 'SYSTEM_RECOVERY_TYPE');
        $operate = xphp_get_lang('UI_SBH_CHECK_DATA');
        $recType = intval($params['srctype']);
        $uuid = $params['uuid'];
        $RECOVERY_DIR = xphp_get_config('app', 'TMP_PATH') . "systemrec/";
        if (!in_array($recType, $SYSTEM_RECOVERY_TYPE)) {
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_PLEASE_CHECK'), "warning");
        }
        //检查恢复文件是否可用
        if ($SYSTEM_RECOVERY_TYPE['AUTO'] == $recType) {
            //如果是自动恢复源,拷贝备份文件到本地工作目录,并解压,然后可以进入下一步
            $sql = "select file_name, file_size, file_path, node_uuid, storage_uuid from bd_system_backup_data where backup_uuid = '{$uuid}'";
            $data = $this->dbSelect($sql, []) ?? [];

            $filesize = $data[0]['file_size'];
            $filepath = $data[0]['file_path'];
            $nodeuuid = $data[0]['node_uuid'];
            $filename = $data[0]['file_name'];

            //先检查文件的大小
            $opName = "NODE_SYS_OP_GET_FILE_SIZE";
            $operate = (new Storage())->getUnifyOpcodeDes($opName);
            $msg = ['file_path' => $filepath];
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
            if (!$mbResult['result']) {
                //获取文件大小失败
                return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
            } else {
                //获取成功,检查获取的大小是否和数据库存储的大小一致
                if ($mbResult['msg']['file_size'] != $filesize) {
                    return $this->muOpResult(false, $operate, '', 'warning');
                }
            }

            //清理并创建恢复目录
            $this->cleanRecoveryDir();
            $recFileName = $RECOVERY_DIR . $filename;

            //下载文件到本地
            $opName = "NODE_SYS_OP_PREAD_FILE";
            $operate = (new Storage())->getUnifyOpcodeDes($opName);
            $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
            //如果大于分块大小,分块下载
            for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
                if ($filesize - $i <= $blockSize) {
                    $readLen = $filesize - $i;
                } else {
                    $readLen = $blockSize;
                }
                $msg = array(
                    "file_path" => $filepath,
                    "offset" => $i,
                    "length" => $readLen,
                );
                $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);

                if (0 == $i) {
                    $result = file_put_contents($recFileName, $mbResult);
                } else {
                    $result = file_put_contents($recFileName, $mbResult, FILE_APPEND);
                }
                ob_flush(); //将数据从php的buffer中释放出来
            }
            //解压文件
            if (!file_exists($recFileName)) {
                return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_NOTFILE_DECOMPRESS_FAIL'), 'warning');
            }
            $cmd = "unzip -P " . xphp_get_config('system', 'ZIP_PASS') . " " . $recFileName . " -d " . $RECOVERY_DIR;
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = ['command' => $cmd];
            $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
            if (!$mbResult['result']) {
                return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_FILE_DECOMPRESS_FAIL'), 'warning');
            }
        } elseif ($SYSTEM_RECOVERY_TYPE['MANUAL'] == $recType) {
            //如果是手动恢复源,因为上传的时候已经解压了,这里再检查一下解压后的配置文件是否存在,存在表示可以进入下一步
            $configPath = $RECOVERY_DIR . "config";
            if (!file_exists($configPath)) {
                return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_NOTFILE_PLEASE_RETRY'), "warning");
            }
        }

        //检查是否有任务运行中
        $sql = "SELECT task_name FROM bd_task WHERE task_status = ?";
        $data = $this->dbSelect($sql, [xphp_get_config('task', 'TASKSTATUS')['RUNNING']]);
        $taskNames = [];
        if (count((array)$data) > 0) {
            foreach ($data as $d) {
                $taskNames[] = $d['task_name'];
            }
            return $this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_CHECK_TASK'), xphp_get_lang('WEB_SYSTEM_RUNNING_NOW'), "warning", 0, $taskNames);
        }

        return $this->muOpResult(true, $operate);
    }
    /**
     * 启动系统恢复
     * @param mixed $params
     * @return bool
     */
    public function doSystemRecovery($params)
    {
        $nodes = $params['nodes'];
        // 从配置文件读取备份配置
        $SYSTEM_BACKUP_CONFIG = xphp_get_config('system', 'SYSTEM_BACKUP_CONFIG');
        $LOG_LEVEL = xphp_get_config('log', 'LOGLEVEL');


        //定义恢复目录和恢复日志文件
        //定义恢复目录和恢复日志文件
        $this->systemRecDir = xphp_get_config('app', 'TMP_PATH') . "systemrec";
        $log_dir = $this->systemRecDir . "/log.txt";

        //清空日志文件
        file_put_contents($log_dir, "");

        $this->writeRecLog([], true, xphp_get_lang('UI_SBH_SYSRECOVERY_START'));

        //开始事务
        $this->dbBeginTransaction();

        $result = true;
        foreach ($SYSTEM_BACKUP_CONFIG as $config) {
            // 循环子节点
            if (!empty($config['child'])) {
                $recovery_node = '';
                foreach ($config['child'] as $child) {
                    // 子节点勾选，则调用备份方法
                    if (in_array($child['id'], $nodes)) {
                        $recovery_node = $config['title'];
                        $callResult = call_user_func_array([$this, 'recoveryDatabaseTable'], [$child]);
                        $result = $result && $callResult;
                    }
                }
                if ($result && !empty($recovery_node)) {
                    $this->writeRecLog([], true, str_replace('%s', $recovery_node, xphp_get_lang('UI_SBH_SYSRECOVERY_TABLE_SUCCESS')));
                }
                if (!$result && !empty($recovery_node)) {
                    $this->writeRecLog([], false, $recovery_node);
                }
            }
        }

        if ($result) {
            $this->dbCommit();
            $this->recoveryEnding();
            $this->writeRecLog([], true, xphp_get_lang('UI_SBH_SYSRECOVERY_SUCCESS'));
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_RECOVERY_SYSTEM", [], $LOG_LEVEL['NORMAL']);
        } else {
            $this->dbRollBack();
            $this->writeRecLog([], false, xphp_get_lang('UI_SBH_SYSRECOVERY_FAIL'));
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_RECOVERY_SYSTEM", [], $LOG_LEVEL['ERROR']);
        }

        return $result;
    }
    /**
     * 写恢复日志
     * @param mixed $value
     * @param mixed $result
     * @param mixed $des
     * @return void
     */
    private function writeRecLog($value, $result, $des = '')
    {
        $RECOVERY_DIR = xphp_get_config('app', 'TMP_PATH') . "systemrec/" . "/log.txt";
        $thisDate = date("Y-m-d H:i:s");
        if (empty($des)) {
            $thisDes = xphp_get_lang('UI_PLATFORM_RECOVER') . '[' . $value['title'] . ']';
        } else {
            $thisDes = $des;
        }
        if ($result) {
            $thisCode = 1;
        } else {
            $thisCode = 0;
        }

        $logInfo = $thisCode . "|" . $thisDate . '|' . $thisDes . PHP_EOL;
        file_put_contents($RECOVERY_DIR, $logInfo, FILE_APPEND);
    }
    /**
     * 恢复完成收尾
     * @return bool
     */
    private function recoveryEnding()
    {
        $result = true;
        //刷新虚拟化中心
        $result = $result && $this->refleshVcenter();

        return $result;
    }
    /**
     * 刷新虚拟化中心
     * @return bool
     */
    private function refleshVcenter()
    {
        $result = true;
        $nodeuuid = $this->getvCenterUUID();
        $opName = 'VM_VCENTER_OP_REFLASH';
        foreach ($this->vcenterRefleshList as $value) {
            $msg = json_encode(array('vcenter_uuid' => $value['vcenter_uuid'], 'display_mode' => xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER']));
            $mbResult = $this->mbVMMsg($nodeuuid, $value['hypervisor_type'], $opName, $msg, false, true);
            $result = $result && $mbResult['result'];
        }

        if (!empty($this->vcenterRefleshList)) {
            $this->writeRecLog(0, $result, xphp_get_lang('UI_SBH_UPDATE_VCENTER'));
        }

        return $result;
    }
    // 获取虚拟化中心UUID
    public function getvCenterUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'NODETYPE')['MASTER']));
        return $data[0]['node_uuid'];
    }
    /**
     * 恢复数据库表
     * @param mixed $params
     * @return bool
     */
    private function recoveryDatabaseTable($params)
    {
        // 获取备份模块对应的备份数据
        $content = $this->getRecoveryFileInfo($params['id']);
        // 暂存数据表唯一标识的数组
        $primary_ids = [];
        $result = true;
        $table_array = array_keys(self::TABLE_PRIMARY_KEY);
        // 遍历备份模块的数据
        foreach ($content as $table_id => $list) {
            if (!in_array($table_id, $table_array)) {
                continue;
            }
            // 查询数据表当前数据信息
            $sqlData = "SELECT * FROM {$table_id}";
            $data = $this->dbSelect($sqlData, [], \PDO::FETCH_ASSOC);
            // 将数据表唯一标识暂存
            if (!empty($data)) {
                $primary_ids = array_column((array)$data, self::TABLE_PRIMARY_KEY[$table_id]);
            }
            // 恢复以唯一标识存在与否来判断是否恢复本条数据
            // 遍历备份的数据，如果备份数据的唯一标识不在数据库中，则插入数据库
            foreach ($list as $li) {
                // 1.需要多个key来判断是否需要插入数据
                if (in_array($table_id, $this->MORE_PRIMARY_KEY)) {
                    // 如果唯一标识不是id，则不插入id
                    if (self::TABLE_PRIMARY_KEY[$table_id] !== 'id') {
                        unset($li['id']);
                    }
                    $result = $this->moreKeyInsertIntoTable($table_id, $li);
                    // 2.只需要一个key判断是否需要插入数据
                } else {
                    if (!in_array($li[self::TABLE_PRIMARY_KEY[$table_id]], $primary_ids)) {
                        // 如果唯一标识不是id，则不插入id
                        if (self::TABLE_PRIMARY_KEY[$table_id] !== 'id') {
                            unset($li['id']);
                        }
                        $result = $this->insertDataIntoTable($table_id, $li);
                    }
                }
            }
        };
        return $result;
    }
    /**
     * 插入数据到数据表
     * @param mixed $table_id
     * @param mixed $data
     * @return bool
     */
    private function insertDataIntoTable($table_id, $data)
    {
        // 需要去掉自增长的id
        if ($table_id == 'bd_storage_resource') {
            unset($data['storage_id']);
        }
        if ($table_id == 'vm_vcenter') {
            unset($data['vcenter_id']);
        }
        if ($table_id == 'bd_cluster') {
            unset($data['cluster_id']);
        }
        if ($table_id == 'bd_node_network_pool') {
            unset($data['network_pool_id']);
        }
        if ($table_id == 'bd_node_pool') {
            unset($data['node_pool_id']);
        }
        if ($table_id == 'bd_agent_pool') {
            unset($data['agent_pool_id']);
        }
        $columns = array_keys($data);
        $columnsSql = '`' . implode("`, `", $columns) . '`';
        $values = array_values($data);
        $valuesSql = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO {$table_id}({$columnsSql}) VALUES ({$valuesSql})";
        if ($table_id == 'vm_vcenter') {
            //将本次新增的是vcenter加入到刷新列表,所有数据库插入完成后再刷新,如果数据库没有记录刷新会失败,所以不在这里刷新
            $this->vcenterRefleshList[] = array(
                'vcenter_uuid' => $data['vcenter_uuid'],
                'hypervisor_type' => $data['hypervisor_type'],
            );
        }
        try {
            $result = $this->dbExec($sql, $values);
        } catch (\Exception $e) {
            // dump($sql, $values);
            $this->writeRecLog([], false, str_replace('%s', $table_id, xphp_get_lang('UI_SBH_SYSRECOVERY_TABLE_FAILED')));
        }
        // 虚拟化中心恢复，需要设置vm_host为未授权，不然会出现授权异常的情况
        if ($table_id == 'vm_host') {
            $updateVmHost = "UPDATE vm_host set authorization_flag = ? where vcenter_uuid =?";
            $result = $result && $this->dbExec($updateVmHost, [xphp_get_config('app', 'FLAG')['UNSET'], $data['vcenter_uuid']]);
        }
        return $result;
    }
    /**
     * 需要多个key来判断是否需要插入数据
     * @param mixed $table_id
     * @param mixed $data
     * @return bool
     */
    private function moreKeyInsertIntoTable($table_id, $data)
    {
        $result = true;
        switch ($table_id) {
            case 'vm_machine_list':
                $sqlAll = "SELECT count(*) as total from vm_machine_list where task_uuid = ? and vcenter_uuid = ? and vm_uuid = ?";
                $dataAll = $this->dbSelect($sqlAll, [$data['task_uuid'], $data['vcenter_uuid'], $data['vm_uuid']]) ?? [];
                break;
            case 'vm_object_list':
                $sql = "SELECT count(*) as total from vm_object_list where task_uuid = ? and vcenter_uuid = ? and object_uuid = ?";
                $dataAll = $this->dbSelect($sql, [$data['task_uuid'], $data['vcenter_uuid'], $data['object_uuid']]) ?? [];
                break;
            case 'bd_shared_storage_node_layout':
                $sql = "SELECT count(*) as total from bd_shared_storage_node_layout where storage_uuid = ? and node_uuid = ?";
                $dataAll = $this->dbSelect($sql, [$data['storage_uuid'], $data['node_uuid']]) ?? [];
                break;
            case 'bd_node_pool_list':
                $sql = "SELECT count(*) as total from bd_node_pool_list where node_pool_uuid = ? and node_uuid = ?";
                $dataAll = $this->dbSelect($sql, [$data['node_pool_uuid'], $data['node_uuid']]) ?? [];
                break;
            case 'bd_node_network_pool_list':
                $sql = "SELECT count(*) as total from bd_node_network_pool_list where network_pool_uuid = ? and network_uuid = ?";
                $dataAll = $this->dbSelect($sql, [$data['network_pool_uuid'], $data['network_uuid']]) ?? [];
                break;
            case 'bd_agent_pool_list':
                $sql = "SELECT count(*) as total from bd_agent_pool_list where agent_pool_uuid = ? and agent_uuid = ?";
                $dataAll = $this->dbSelect($sql, [$data['agent_pool_uuid'], $data['agent_uuid']]) ?? [];
                break;
            case 'nas_mount_list':
                $sql = "SELECT count(*) as total from nas_mount_list where nas_uuid = ? and node_uuid = ?";
                $dataAll = $this->dbSelect($sql, [$data['nas_uuid'], $data['node_uuid']]) ?? [];
                break;
        }
        if ($dataAll[0]['total'] > 0) {
            return $result;
        }
        $result = $this->insertDataIntoTable($table_id, $data);
        return $result;
    }
    /**
     * 获取恢复文件信息
     * @param mixed $filename
     */
    private function getRecoveryFileInfo($filename)
    {
        ini_set('memory_limit', '2000m');
        $path = $this->systemRecDir . "/" . $filename;
        if (!file_exists($path)) {
            return false;
        }
        $content = file_get_contents($path);
        return json_decode($content, true);
    }
    /**
     * 获取恢复进度,循环读取恢复日志
     * @param mixed $params
     * @return array<int|string>[]
     */
    public function getRecoveryProgress($params)
    {
        $logPath = xphp_get_config('app', 'TMP_PATH') . "systemrec/log.txt";
        $fileInfo = file_get_contents($logPath);
        $fileInfo = explode(PHP_EOL, $fileInfo);
        $info = [];
        foreach ($fileInfo as $file) {
            if (empty($file)) continue;
            $eachFile = explode("|", $file);
            $info[] = [
                intval($eachFile[0]),
                $eachFile[1],
                $eachFile[2],
            ];
        }
        return $info;
    }
    /**
     * 获取自动备份源文件树
     * @param $params
     * @return array
     */
    public function getAutoSourceTree($params)
    {
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);
        $operate = xphp_get_lang('UI_SBH_GET_RECOVERY_ITEM');
        $sql = "select config from bd_system_backup_data where backup_uuid = '{$uuid}'";
        $data = $this->dbSelect($sql, []);

        if (empty($data)) {
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_SBH_GETINFO_FAIL'), 'warning');
        }
        $config = json_decode($data[0]['config'], true);
        return $this->getSystemBackupTreeInfo(['nodes' => $config['nodes']]);
    }
    public function downloadBackupList($params)
    {
        $tmpPath = xphp_get_config('app', 'TMP_PATH') . 'systembak';
        // 修改权限
        $cmd = "chmod -R 777 " . $tmpPath . ";chown nginx:nginx " . $tmpPath;
        // 执行命令
        $msg = ['command' => $cmd];
        $mbResult = $this->mbPFMsg('PT_SYSTEM_BACKGROUND_OP_DO_COMMAND', json_encode($msg), true);

        // 单次下载文件的大小跟随配置文件，默认为8MB
        $fileBuffer = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        $opName = 'NODE_SYS_OP_PREAD_FILE';
        //如果是自动恢复源,拷贝备份文件到本地工作目录,并解压,然后可以进入下一步
        $sql = "select file_name, file_size, file_path, node_uuid, storage_uuid from bd_system_backup_data where backup_uuid = '{$params['uuid']}'";
        $data = $this->dbSelect($sql, []) ?? [];
        $file_size = $data[0]['file_size'];
        $file_path = $data[0]['file_path'];
        $node_uuid = $data[0]['node_uuid'];
        $file_name = $data[0]['file_name'];
        $tmpFilename = $tmpPath . '/' . $file_name;
        // dump($tmpFilename);
        // 这里采用分块写，一次写太多内存会超出
        $fp = fopen($tmpFilename, 'a+');
        for ($i = 0; $i < $file_size; $i += $fileBuffer) {
            if ($file_size - $i <= $fileBuffer) {
                $readLen = $file_size - $i;
            } else {
                $readLen = $fileBuffer;
            }
            $msg = array(
                'file_path' => $file_path,
                'offset' => $i,
                'length' => $readLen,
            );
            $readBuffer = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true);
            fwrite($fp, $readBuffer);
        }
        fclose($fp);

        $systemHandler = new \app\v1\system\v0\logic\Index();
        $nodeHandler = new Node();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $url = $systemHandler->groupUnifyDownloadUrl($nodeuuid, $tmpFilename);

        return $this->sendResult('', true, 200, ['url' =>$url]);
    }
    /**
     * 写日志,只用于WEB写文件,调试用
     * @param string $msg   日志消息,可以是自定义消息,|error日志可以是错误定义名字或错误号
     * @param int $type  消息类型           info/notice/warning/error       1/2/3/4
     */
    public function writeSystemBackupLog($msg, $type = 1){
        // 构建文件路径
        $filepath = xphp_get_config('log', 'LOG_INFO')['PATH'];
        $filename = $filepath . '_system_backup-' . date('Y-m-d') . '.log';
        if (!file_exists($filename)) {
            $addFile = 'touch ' . $filename;
            exec($addFile);
            // 删除日志，保证只有15个，按照时间顺序排序
            $cmd = 'ls -tr ' . $filepath . 'system_backup-*';
            exec($cmd, $output);
            $count = count($output);
            // 保留30天，一天一个文件，保留30个
            if ($count > 30) {
                $row = $count - 30;
                // 删除30个之前的日志文件
                for ($i = 0; $i < $row; $i++) {
                    if (file_exists($output[$i])) {
                        // 这里采用unlink，不采用rm -rf， 避免风险
                        unlink($output[$i]);
                    }
                }
            }
        }
        $count = file_put_contents($filename, date('Y-m-d H:i:s') . $msg . "." . PHP_EOL , FILE_APPEND | LOCK_EX);
        if ($count > 0) {
            return true;
        }
        return false;
    }
}
