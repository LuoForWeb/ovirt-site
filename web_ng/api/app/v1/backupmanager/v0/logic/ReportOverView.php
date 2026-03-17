<?php

namespace app\v1\backupmanager\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Index;

/**
 * note          容器 脚本管理
 * @author       jiangyongjie@vinchin.com
 * @date         2023/10/19 10:32:01
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ReportOverView extends Base
{
    /**
     * 获取备份中心报表数据概览
     * @param $params $params
     * @return void
     */
    public function getOverviewDataInfo($params)
    {
        //        $ip = $params['ip'];  //获取IP地址
//        $port = $params['port'];  //获取端口号
//        $api_key = $params['api_key'];  //获取api-key(api-key的验证在request进行)
        $reportType = $params['type'];  //报表类型
        $dataType = $params['data_type'];  //数据类型
        $basicInfo = $this->getReportModuleOverviewData($reportType, $dataType);
        return $basicInfo;
    }

    /**
     * 获取模块概览数据
     * @param $reportType report type
     * @param $dataType   data type
     * @return array[]
     */
    private function getReportModuleOverviewData($reportType, $dataType): array
    {
        $taskOverviewData = $this->getTaskOverviewData();
        $taskInfo = array(
            'overview' => $taskOverviewData,
            'report_type' => 1
        );
        $storageInfo = array(
            'overview' => $this->getStorageOverviewData(),
            'report_type' => 2
        );
        $agentInfo = array(
            'overview' => $this->getAgentOverviewData(),
            'report_type' => 3
        );

        $nasInfo = array(
            'overview' => $this->getNasOverviewData(),
            'report_type' => 4
        );
        $vmInfo = array(
            'overview' => $this->getVmOverviewData(),
            'report_type' => 5
        );
        $fileInfo = array(
            'overview' => $this->getFileOverviewData(),
            'report_type' => 6
        );
        $dbInfo = array(
            'overview' => $this->getDbOverviewData(),
            'report_type' => 7
        );
        $osInfo = array(
            'overview' => $this->getOsOverviewData(),
            'report_type' => 8
        );
        $volCdpInfo = array(
            'overview' => $this->getVolCdpOverviewData(),
            'report_type' => 9
        );
        $reportData = [$taskInfo, $storageInfo, $agentInfo, $nasInfo, $vmInfo, $fileInfo, $dbInfo, $osInfo, $volCdpInfo];
        return array(
            'rows' => $reportData
        );
    }

    /**
     * 获取任务概览数据
     * @return void
     */
    private function getTaskOverviewData()
    {
        $userVal = xphp_get_user_info()['userUuid'];
        $sql = "select * from bd_task where delete_flag != ?";
        $taskData = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        $totalTasks = count($taskData);

        $hisSql = "select error_code from bd_history_task";
        $sqlAlarm = "select count(task_alarm_id) as total from bd_task_alarm where user_uuid = ? and alarm_level = ? and solved_flag = ?";
        //异常  level 2
        $countAbnormal = $this->dbSelect($sqlAlarm, array($userVal, 2, 2));
        //失败  level 3
        $countFail = $this->dbSelect($sqlAlarm, array($userVal, 3, 2));
        $hisData = $this->dbSelect($hisSql);
        $runTaskNumber = count($hisData);
        $successTaskNumber = 0;
        $abnormalTaskNumber = 0;
        $errorCodeArr = xphp_get_config('error', 'errorCode');
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        if (!empty($hisData)) {
            foreach ($hisData as $d) {
                $errorCode = $d['error_code'];
                if ($errorCode == 0) {
                    $successTaskNumber += 1;
                }
                if (in_array($errorCodeArr[$errorCode], $abnormal)) {  //异常任务
                    $abnormalTaskNumber += 1;
                }
            }

        }
        $taskOverviewData = array(
            'task_num' => $totalTasks, //当前任务个数
            'running_task' => $runTaskNumber,  //已运行任务个数
            'success_task' => $successTaskNumber,
            'abnormal_task' => $countAbnormal[0]['total'],
            'failed_task' => $countFail[0]['total']
        );
        return $taskOverviewData;
    }

    /**
     * 获取存储概览数据
     * @return void
     */
    public function getStorageOverviewData()
    {
        $useMode1 = xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['COPY'];
        $useMode2 = xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['ARCHIVE'];
        $useMode3 = xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['BACKUP'];

        $sqlCopy = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource where use_mode = ? or use_mode = ?  ";
        $dataCopy = $this->dbSelect($sqlCopy, array($useMode1, $useMode2));
        $sqlAll = "select sum(total_size) as total_size from  bd_storage_resource where use_mode = ? ";
        //备份
        $backupNumber = $this->dbSelect($sqlAll, array($useMode3));
        $backupCount = intval($backupNumber[0]['total_size']);
        //副本
        $copyNumber = $this->dbSelect($sqlAll, array($useMode1));
        $copyCount = intval($copyNumber[0]['total_size']);
        //归档
        $archiveNumber = $this->dbSelect($sqlAll, array($useMode2));
        $archiveCount = intval($archiveNumber[0]['total_size']);
        //返回已用容量
        $total = intval($dataCopy[0]['total_size']);
        $free = intval($dataCopy[0]['free_size']);
        $used = $total - $free;

        $sql = "select status,total_size,free_size from bd_storage_resource";
        $data = $this->dbSelect($sql);
        $totalStorageDevices = count($data);
        $onlineNumber = 0;
        $offlineNumber = 0;
        $usedStorage = 0;
        $unusedStorage = 0;
        $allSize = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $allSize += $d['total_size'];
                $usedStorage += $d['total_size'] - $d['free_size'];
                $unusedStorage += $d['free_size'];
                if ($d['status'] == xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE']) {
                    $onlineNumber += 1;
                } else {
                    $offlineNumber += 1;
                }
            }
        }

        $storageOverviewData = array(
            'total_storage_devices' => $totalStorageDevices,
            'online_device' => $onlineNumber,
            'backup_data' => $backupCount,
            'copy_data' => $copyCount,
            'archived_data' => $archiveCount,
            'used_storage' => $usedStorage,
            'unused_storage' => $unusedStorage,
        );
        return $storageOverviewData;
    }
    /**
     * 获取系统存储数据
     * @return void
     */
    private function getBackupDataInfo()
    {
        $sql = "select module_type,total_object_completed_size,start_time,submodule_type,task_type 
                from bd_history_task";
        $data = $this->dbSelect($sql);
        $backupDataValue = 0;
        $copyDataValue = 0;
        $archiveDataValue = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $taskDataType = $this->getReportDataType($d['module_type'], $d['task_type']);
                if ($taskDataType == 1) {  //备份数据
                    $backupDataValue += $d['total_object_completed_size'];
                } elseif ($taskDataType == 2) { //副本数据
                    $copyDataValue += $d['total_object_completed_size'];
                } elseif ($taskDataType == 3) {  //归档数据
                    $archiveDataValue += $d['total_object_completed_size'];
                }
            }
        }
        return array(
            'backup_data' => $backupDataValue,
            'copy_data' => $copyDataValue,
            'archive_data' => $archiveDataValue
        );
    }

    /**
     * 得到各模块的显示描述
     * @param int $moduleType type
     * @param int $taskType   taskType
     * @return int
     */
    private function getReportDataType(int $moduleType, int $taskType): int
    {
        $modeultType = 1;
        if ($moduleType == xphp_get_config('module', 'MODULE_NAME')['copy']) {
            $taskTypeArray = xphp_get_config('task', 'TASKTYPE');
            if (
                $taskType == $taskTypeArray['BACKUP_COPY'] ||
                $taskType == $taskTypeArray['BACKUP_COPY_FETCH'] ||
                $taskType == $taskTypeArray['FILE_BACKUP_COPY'] ||
                $taskType == $taskTypeArray['FILE_BACKUP_COPY_FETCH'] ||
                $taskType == $taskTypeArray['DB_BACKUP_COPY'] ||
                $taskType == $taskTypeArray['DB_BACKUP_COPY_FETCH'] ||
                $taskType == $taskTypeArray['NAS_BACKUP_COPY'] ||
                $taskType == $taskTypeArray['NAS_BACKUP_COPY_FETCH']
            ) {
                $modeultType = 2;
            } elseif (
                $taskType == xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']
            ) {
                $moduleTypeDes = Xphp::$lang['UI_PLATFORM_ARCHIVE'];  //归档
                $modeultType = 3; //归档
            } else {
                $modeultType = 1; //备份数据
            }
        }
        return $modeultType;
    }

    /**
     * 获取客户端报表概览数据
     * @return void
     */
    public function getAgentOverviewData()
    {
        $sql = "select online_flag from bd_agent ba where ba.agent_type not in (3, 4)";
        $data = $this->dbSelect($sql);
        $totalClientNumber = count($data);
        $onlineClientNumber = 0;
        $offlineClientNumber = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['online_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    $onlineClientNumber += 1;
                } else {
                    $offlineClientNumber += 1;
                }
            }
        }
        $sqlBackup = "select sum(total_size) as total from bd_backup_timepoint where module_type != 2";

        $dataBackup = $this->dbSelect($sqlBackup);
        $sqlCdpBackup = "select sum(backup_file_size + log_file_total_size) as total from cdp_vol_backup_vol_set";
        $dataCdpBackup = $this->dbSelect($sqlCdpBackup);

        $protectedClient = $this->getTaskAgentNumber();
        $agentOverviewData = array(
            'total_client_number' => $totalClientNumber,
            'online_client_number' => $onlineClientNumber,
            'offline_client_number' => $offlineClientNumber,
            'backup_data' => $dataBackup[0]['total'] + $dataCdpBackup[0]['total'],
            'protected_client' => $protectedClient,
            'unprotected_client' => $totalClientNumber - $protectedClient,
        );
        return $agentOverviewData;
    }
    /**
     * 获取已创建任务的客户端个数
     * @return void
     */
    private function getTaskAgentNumber()
    {
        $sql = "select distinct agent_uuid from bd_task_agent_list";
        $data = $this->dbSelect($sql);
        return count($data);
    }

    /**
     * 获取NAS设备报表概览数据
     * @return void
     */
    public function getNasOverviewData()
    {
        $sql = "select nas_status from nas_storage_resource";
        $data = $this->dbSelect($sql);
        $nasDeviceDum = count($data);
        $onlineNumber = 0;
        $offlineNumber = 0;
        $protectedNasDevice = 0;
        $unprotectedNasDevice = 0;

        if (!empty($data)) {
            foreach ($data as $d) {
                $nasStatus = $d['nas_status'];
                if ($nasStatus == 1) {
                    $onlineNumber += 1;
                }
            }
        }
        $offlineNumber = $nasDeviceDum - $onlineNumber;
        $nasTaskSql = "select task_uuid from nas_task";
        $nasTaskData = $this->dbSelect($nasTaskSql);
        $protectedNasDevice = count($nasTaskData); //以保护的NAS设备总数

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
        $backupData = $this->getModuleBackupData($moduleType);
        $unprotectedNasDevice = $nasDeviceDum - $protectedNasDevice;  //未保护NAS设备总数

        $nasOverviewData = array(
            'nas_device_sum' => $nasDeviceDum,
            'online_number' => $onlineNumber,
            'offline_number' => $offlineNumber,
            'backup_data' => $backupData,
            'protected_nas_device' => $protectedNasDevice,
            'unprotected_nas_device' => $unprotectedNasDevice
        );
        return $nasOverviewData;
    }

    /**
     * 获取虚拟机模块运行概要数据
     * @return void
     */
    public function getVmOverviewData()
    {
        $vmNumberSql = "select * from vm_machine";
        $vmNumberData = $this->dbSelect($vmNumberSql);
        $vmTotalNumber = count($vmNumberData); //系统虚拟机总个数

        $vmPlatformNumberSql = "select * from vm_vcenter";
        $vmPlatformNumberData = $this->dbSelect($vmPlatformNumberSql);
        $vmPlatformNumberCount = count($vmPlatformNumberData);  //虚拟化平台总数

        $protectedVmNumberSql = "select * from vm_machine_list";
        $protectedVmNumberData = $this->dbSelect($protectedVmNumberSql);
        $protectedVmTotal = count($protectedVmNumberData);  //受保护的虚拟机总数
        $unprotectedVmTotal = $vmTotalNumber - $protectedVmTotal;  //未保护虚拟机个数

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $backupData = $this->getModuleBackupData($moduleType);
        $backupNumber = $this->getModuleBackupNum($moduleType);

        $vmOverviewData = array(
            'vm_total_number' => $vmTotalNumber,
            'protected_vm_total' => $protectedVmTotal,
            'unprotected_vm_total' => $unprotectedVmTotal,
            'vm_platform_number' => $vmPlatformNumberCount,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData,
        );
        return $vmOverviewData;
    }

    /**
     * 获取备份数据，单位字节
     * @param $moduleType module type
     * @return int
     */
    private function getModuleBackupData($moduleType)
    {
        $sql = "select total_size from bd_backup_timepoint where module_type = ?";
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
     * 获取模块备份次数
     * @param $moduleType moduleType
     * @return void
     */
    private function getModuleBackupNum($moduleType)
    {
        $sql = "select * from bd_history_task where module_type = ?";
        $data = $this->dbSelect($sql, array($moduleType));
        return count($data);  //备份次数
    }

    /**
     * 文件报表数据概览
     * @return array
     */
    public function getFileOverviewData(): array
    {
        $sql = "select * from bd_task_agent_list btal,bd_task bt 
                where bt.task_uuid = btal.task_uuid and bt.module_type =?";
        $data = $this->dbSelect($sql, array(xphp_get_config('module', 'MODULE_TYPE')['FILE']));
        $totalClientNumber = count($data);

        $nasTaskSql = "select task_uuid from nas_task";
        $nasTaskData = $this->dbSelect($nasTaskSql);
        $protectedNasDevice = count($nasTaskData); //以保护的NAS设备总数

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['File'];
        $backupNumber = $this->getModuleBackupNum($moduleType);

        $backupData = $this->getModuleBackupData($backupNumber);
        return array(
            'protected_client_total' => $totalClientNumber,
            'protected_nas_total' => $protectedNasDevice,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData,
        );
    }

    /**
     * 获取数据库模块报表概览数据
     * @return array
     */
    public function getDbOverviewData(): array
    {
        $sql = "select DISTINCT agent_uuid from db_list ";
        $data = $this->dbSelect($sql);
        $protectedClientTotal = count($data);

        $insSql = "select DISTINCT instance_name from db_list ";
        $insData = $this->dbSelect($insSql);
        $protectedInsTotal = count($insData);

        $appSql = "select * from bd_agent_app";
        $appData = $this->dbSelect($appSql);
        $appCount = count($appData);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['DB'];
        $backupNumber = $this->getModuleBackupNum($moduleType);
        $backupData = $this->getModuleBackupData($moduleType);
        return array(
            'protected_client_total' => $protectedClientTotal,
            'ins_total' => $appCount,
            'protected_instance_total' => $protectedInsTotal,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData,
        );
    }

    /**
     * 获取操作系统模块概览数据
     * @return array
     */
    public function getOsOverviewData(): array
    {
        $sql = "select * from os_list";
        $data = $this->dbSelect($sql);
        $protectedClientTotal = count($data);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        $backupNumber = $this->getModuleBackupNum($moduleType);
        $backupData = $this->getModuleBackupData($moduleType);
        return array(
            'protected_client_total' => $protectedClientTotal,
            'backup_number' => $backupNumber,
            'backup_data' => $backupData
        );
    }

    /**
     * 卷CDP模块概览数据
     * @return array
     */
    private function getVolCdpOverviewData(): array
    {
        $sql = "select distinct master_agent_uuid from cdp_vol_task";
        $data = $this->dbSelect($sql);
        $protectedClientTotal = count($data);

        $allAgentSql = "select online_flag from bd_agent";
        $allAgentData = $this->dbSelect($allAgentSql);
        $unprotectedClientTotal = count($allAgentData) - $protectedClientTotal;

        $backupData = 0;
        $volSetSql = "select  backup_agent_id,backup_file_size from cdp_vol_backup_vol_set";
        $backupSetIdArray = array();
        $volData = $this->dbSelect($volSetSql);
        if (!empty($volData)) {
            foreach ($volData as $d) {
                $backupData += $d['backup_file_size'];
                $backupSetIdArray[] = $d['backup_agent_id'];
            }
        }
        $backupSetNumber = count($backupSetIdArray);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $taskNumber = $this->getModuleTaskNumber($moduleType);
        return array(
            'protected_client_total' => $protectedClientTotal,
            'unprotected_client_total' => $unprotectedClientTotal,
            'backup_data' => $backupData,
            'backup_set_number' => $backupSetNumber,
            'task_number' => $taskNumber
        );
    }

    /**
     * 获取模块任务总个数
     * @param $module module
     * @return int
     */
    private function getModuleTaskNumber($module)
    {
        $sql = "select * from bd_task where module_type = ?";
        $data = $this->dbSelect($sql, array($module));
        return count($data);
    }
}
