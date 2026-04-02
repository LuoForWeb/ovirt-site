<?php

namespace app\v1\backupmanager\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Index;
use app\v1\backupmanager\v0\logic\ReportOverView;

class ReportRunningData extends Base
{
    /**
     * 获取备份中心报表近期运行数据
     * @param array $params params
     * @author jiangyongjie@vinchin.com
     * @date 2023/10/19
     * @return array
     */
    public function getRunDataInfo($params)
    {
        $ip = $params['ip'];  //获取IP地址
        $port = $params['port'];  //获取端口号
        $apikey = $params['api_key'];  //获取api-key(api-key的验证在request进行)
        $reportType = $params['type'];  //报表类型
        $dataType = $params['data_type'];  //数据类型
        $basicInfo = $this->getReportModuleRunData($reportType, $dataType);
        return $basicInfo;
    }

    /**
     * 根据报表类型获取模块运行数据
     * @param $type     type
     * @param $dataType datatype
     * @return void
     */
    private function getReportModuleRunData($type, $dataType)
    {
        if ($type != 0) {
            $data = $this->getModuleReportData($type, $dataType);
        } else {
            $data = $this->getAllModuleReportData($dataType);
        }
        return $data;
    }

    /**
     * 获取所有模块近期运行数据
     * @param $dataType dataType
     * @return void
     */
    private function getAllModuleReportData($dataType)
    {
        $reportsModule = xphp_get_config('report', 'MODULE_REPORT'); //报表模块

        $successTaskData = $this->getTaskReportRunData($reportsModule['TASK'], $dataType = 1);

        $storageData = $this->getStorageReportRunData($reportsModule['STORAGE']);
        $backupStorageData = $storageData['backup_data'];
        $copyStorageData = $storageData['copy_data'];
        $archiveStorageData = $storageData['archive_data'];

        $clientData = $this->getClientReportRunData($reportsModule['CLIENT']);
        $nasData = $this->getNasReportRunData($reportsModule['NAS']);
        $vmData = $this->getVmReportRunData($reportsModule['VM']);
        $fileData = $this->getFileReportRunData($reportsModule['FILE']);
        $dbData = $this->getDbReportRunData($reportsModule['DB']);
        $osData = $this->getOsReportRunData($reportsModule['OS']);
        $volCdpData = $this->getVolcdpReportRunData($reportsModule['VOL_CDP']);

        $reportDataArray = [$successTaskData,$storageData,$backupStorageData,$copyStorageData,$archiveStorageData,
            $clientData,$nasData,$vmData,$fileData,$dbData,$osData,$volCdpData];
        return array(
            'rows' => $reportDataArray
        );
    }

    /**
     * 按模块获取报表进行运行数据
     * @param $type     type
     * @param $dataType data type
     * @return void
     */
    private function getModuleReportData($type, $dataType)
    {
        $reportsModule = xphp_get_config('report', 'MODULE_REPORT'); //报表模块
        $data = array();
        switch ($type) {
            case $reportsModule['TASK']:
                $data = $this->getTaskReportRunData($reportsModule['TASK'], $dataType);
                break;
            case $reportsModule['STORAGE']:
                $data = $this->getStorageReportRunData($reportsModule['STORAGE']);
                break;
            case $reportsModule['CLIENT']:
                $data = $this->getClientReportRunData($reportsModule['CLIENT']);
                break;
            case $reportsModule['NAS']:
                $data = $this->getNasReportRunData($reportsModule['NAS']);
                break;
            case $reportsModule['VM']:
                $data = $this->getVmReportRunData($reportsModule['VM']);
                break;
            case $reportsModule['FILE']:
                $data = $this->getFileReportRunData($reportsModule['FILE']);
                break;
            case $reportsModule['DB']:
                $data = $this->getDbReportRunData($reportsModule['DB']);
                break;
            case $reportsModule['OS']:
                $data = $this->getOsReportRunData($reportsModule['OS']);
                break;
            case $reportsModule['VOL_CDP']:
                $data = $this->getVolcdpReportRunData($reportsModule['VOL_CDP']);
                break;
        }
        return array(
            'rows' => $data
        );
    }

    /**
     * 获取卷实时模块报表数据-运行数据
     * @param $type 报表类型
     * @return array
     */
    private function getVolcdpReportRunData($type)
    {
        $volcdpSql = "select cvbcs.backup_file_size 
                    from bd_backup_timepoint bt,cdp_vol_backup_agent cvba,cdp_vol_backup_vol_set cvbcs
                    where bt.timepoint_uuid = cvba.timepoint_uuid and cvba.id = cvbcs.backup_agent_id ";
        $volcdpDataList = $this->dbSelect($volcdpSql);
        $volcdpData = 0;
        if (!empty($volcdpDataList)) {
            foreach ($volcdpDataList as $d) {
                $volcdpData += $d['backup_file_size'];
            }
        }
        $reportArray = array(
            'data' => $volcdpData,
            'time' => date('Y-m-d h:m:s'),
            'report_type' => $type,
            'data_type' => 0
        );
        return $reportArray;
    }

    /**
     * 获取操作系统模块报表运行数据
     * @param $type 报表类型 8
     * @return void
     */
    private function getOSReportRunData($type)
    {
        $data = $this->getModuleRunningData(xphp_get_config('module', 'MODULE_TYPE')['OS'], $type);
        return $data;
    }

    /**
     * 获取数据库模块报表运行数据
     * @param $type 报表类型 7
     * @return void
     */
    private function getDbReportRunData($type)
    {
        $data = $this->getModuleRunningData(xphp_get_config('module', 'MODULE_TYPE')['DB'], $type);
        return $data;
    }

    /**
     * 获取文件模块报表运行数据
     * @param $type 报表类型 6
     * @return void
     */
    private function getFileReportRunData($type)
    {
        $data = $this->getModuleRunningData(xphp_get_config('module', 'MODULE_TYPE')['FS'], $type);
        return $data;
    }

    /**
     * 获取虚拟机模块报表运行数据
     * @param $type 报表类型5
     * @return void
     */
    private function getVmReportRunData($type)
    {
        $data = $this->getModuleRunningData(xphp_get_config('module', 'MODULE_TYPE')['VM'], $type);
        return $data;
    }

    /**
     * 获取NAS 设备报表运行数据
     * @param $reportType 报表类型4
     * @return array|null
     */
    private function getNasReportRunData($reportType)
    {
        $data = $this->getModuleRunningData(xphp_get_config('module', 'MODULE_TYPE')['NAS'], $reportType);
        return $data;
    }

    /**
     * 获取模块运行数据
     * @param $moduleType moduleType
     * @param $reportType reportType
     * @return void
     */
    private function getModuleRunningData($moduleType, $reportType)
    {
        $sql = "SELECT total_size,timepoint from bd_backup_timepoint where module_type = ? ";
        $data = $this->dbSelect($sql, array($moduleType));
        $rowsInfo = array();
        if ($data) {
            foreach ($data as $d) {
                $nowTime = $d['timepoint'];
                $dataSize = $d['total_size'];
                $rowsInfo[] = array(
                    'time' => $nowTime,
                    'data_size' => $dataSize,
                    'report_type' => $reportType,
                );
            }
        }
        return array(
            'report_type' => $reportType,
            'data' =>  $rowsInfo,
        );

//        $runData = 0;
//        if (!empty($data)) {
//            foreach ($data as $d) {
//                $runData += $d['total_size'];
//            }
//        }
//        $reportsModule = xphp_get_config('report', 'MODULE_REPORT'); //报表模块
//        $moduleDetail = array();
//        $reportDetailStr = '';
//        if ($reportType == $reportsModule['NAS']) {
//            $reportDetailStr = 'nas_report_detail';
//            $detail = (new ReportOverView())->getNasOverviewData();
//            $moduleDetail = array(
//                'nas_devices' => $detail['nas_device_sum'],
//                'on_line_number' => $detail['online_number'],
//                'off_line_number' => $detail['offline_number'],
//                'backup_data' => $runData
//            );
//        }
//        if ($reportType == $reportsModule['VM']) {
//            $reportDetailStr = 'vm_report_detail';
//            $detail = (new ReportOverView())->getVmOverviewData();
//            $moduleDetail = array(
//                'vm_number' => $detail['vm_total_number'],
//                'vcenter_number' => $detail['vm_platform_number'],
//                'protected_vm' => $detail['protected_vm_total'],
//                'unprotected_vm' => $detail['unprotected_vm_total'],
//                'backup_number' => $detail['backup_number'],
//                'backup_data' => $detail['backup_data']
//            );
//        }
//        if ($reportType == $reportsModule['FILE']) {
//            $reportDetailStr = 'file_report_detail';
//            $detail = (new ReportOverView())->getFileOverviewData();
//            $moduleDetail = array(
//                'protected_agent' => $detail['protected_client_total'],
//                'protected_nas' => $detail['protected_nas_total'],
//                'backup_number' => $detail['backup_number'],
//                'backup_data' => $detail['backup_data'],
//            );
//        }
//        if ($reportType == $reportsModule['DB']) {
//            $reportDetailStr = 'db_report_detail';
//            $detail = (new ReportOverView())->getDbOverviewData();
//            $moduleDetail = array(
//                'protected_agent' => $detail['protected_client_total'],
//                'instance_count' => $detail['ins_total'],
//                'protected_instance' => $detail['protected_instance_total'],
//                'backup_number' => $detail['backup_number'],
//                'backup_data' => $detail['backup_data'],
//            );
//        }
//        if ($reportType == $reportsModule['OS']) {
//            $reportDetailStr = 'os_report_detail';
//            $detail = (new ReportOverView())->getOsOverviewData();
//            $moduleDetail = array(
//                'protected_client_total' => $detail['protected_client_total'],
//                'backup_number' => $detail['backup_number'],
//                'backup_data' => $detail['backup_data'],
//            );
//        }
    }

    /**
     * 获取客户端报表运行数据
     * @param $reportType 报表类型
     * @return array
     */
    private function getClientReportRunData($reportType)
    {
        $endTime = date('Y-m-d');
        $interval = 30;
        $startTime = date('Y-m-d', strtotime($endTime . ' - ' . $interval . 'days'));

        //数据库
        $sql = "SELECT sum( bsa.total_size) as total,alarm_day 
                FROM
	              ( SELECT DATE_FORMAT( timepoint, '%Y-%m-%d' ) AS alarm_day,bbt.total_size 
	                FROM bd_backup_timepoint bbt,db_backup_timepoint dt
	                where bbt.timepoint_uuid = dt.timepoint_uuid) AS bsa 
                GROUP BY alarm_day";
        //文件nas
        $sql1 = "SELECT sum( bsa.total_size) as total,alarm_day 
                FROM
	              ( SELECT DATE_FORMAT( timepoint, '%Y-%m-%d' ) AS alarm_day,bbt.total_size 
	                FROM bd_backup_timepoint bbt,fs_backup_timepoint ft 
	                where bbt.timepoint_uuid = ft.fs_timepoint_uuid) AS bsa 
                GROUP BY alarm_day";
        //操作系统
        $sql2 = "SELECT sum( bsa.total_size) as total,alarm_day 
                FROM
	              ( SELECT DATE_FORMAT( timepoint, '%Y-%m-%d' ) AS alarm_day,bbt.total_size 
	                FROM bd_backup_timepoint bbt,os_backup_timepoint ot  
	                where bbt.timepoint_uuid = ot.timepoint_uuid) AS bsa 
                GROUP BY alarm_day";
        //cdp
        $sql3 = "SELECT sum( bsa.backup_file_size + bsa.log_file_total_size) as total,alarm_day 
                FROM
	              ( SELECT DATE_FORMAT( timepoint, '%Y-%m-%d' ) AS alarm_day,cvbcs.backup_file_size,log_file_total_size
	                FROM bd_backup_timepoint bbt,cdp_vol_backup_agent cvba,cdp_vol_backup_vol_set cvbcs
	                where bbt.timepoint_uuid = cvba.timepoint_uuid and cvba.id = cvbcs.backup_agent_id) AS bsa 
                GROUP BY alarm_day";
        $data = $this->dbSelect($sql);
        $data1 = $this->dbSelect($sql1);
        $data2 = $this->dbSelect($sql2);
        $data3 = $this->dbSelect($sql3);
        $dbData = [];
        $filmData = [];
        $osData = [];
        $cdpData = [];
        foreach ($data as $item) {
            $dbData[$item['alarm_day']] = $item['total'];
        }
        foreach ($data1 as $item) {
            $filmData[$item['alarm_day']] = $item['total'];
        }
        foreach ($data2 as $item) {
            $osData[$item['alarm_day']] = $item['total'];
        }
        foreach ($data3 as $item) {
            $cdpData[$item['alarm_day']] = $item['total'];
        }
        $startTimestamp = strtotime(date('Y-m-d', strtotime($startTime))); // 2024-01-01
        $endTimestamp = strtotime(date('Y-m-d', strtotime($endTime))); // 2024-02-23
        $ret = [
            'db' => [],
            'film' => [],
            'os' => [],
            'cdp' =>[],
        ];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('Y-m-d', $i);
            if (isset($dbData[$day])) {
                $ret['db'][$day] = [
                    'value' => $dbData[$day],
                    'name' => $day,
                ];
            } else {
                $ret['db'][$day] = [
                    'value' => 0,
                    'name' => $day,
                ];
            }
            if (isset($filmData[$day])) {
                $ret['film'][$day] = [
                    'value' => $filmData[$day],
                    'name' => $day,
                ];
            } else {
                $ret['film'][$day] = [
                    'value' => 0,
                    'name' => $day,
                ];
            }
            if (isset($osData[$day])) {
                $ret['os'][$day] = [
                    'value' => $osData[$day],
                    'name' => $day,
                ];
            } else {
                $ret['os'][$day] = [
                    'value' => 0,
                    'name' => $day,
                ];
            }
            if (isset($cdpData[$day])) {
                $ret['cdp'][$day] = [
                    'value' => $cdpData[$day],
                    'name' => $day,
                ];
            } else {
                $ret['cdp'][$day] = [
                    'value' => 0,
                    'name' => $day,
                ];
            }
        }
        $sumData = [];
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += 86400) {
            $day = date('Y-m-d', $i);
            $sumData[] = [
                'time' =>$day. ' 00:00:00',
                'data_size' =>$ret['cdp'][$day]['value'] + $ret['db'][$day]['value'] + $ret['film'][$day]['value'] + $ret['os'][$day]['value'],
                'report_type' => $reportType,
            ];
        }
        return array(
            'report_type' => $reportType,
            'data' =>  $sumData,
        );
    }

    /**
     * 获取客户端在线状态
     * @return void
     */
    private function getAgentOnlineInfo()
    {
        $sql = "select online_flag from bd_agent";
        $data = $this->dbSelect($sql);
        $count = count($data);
        $onLineNumber = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['online_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    $onLineNumber += 1;
                }
            }
        }
        $offLineNumber = $count - $onLineNumber;
        return array(
            'agent_number' => $count,
            'on_line_number' => $onLineNumber,
            'off_line_number' => $offLineNumber
        );
    }

    /**
     * 获取任务报表相关运行数据
     * @param $reportType 报表类型 1
     * @param $dataType   数据类型
     * @return array
     */
    private function getTaskReportRunData($reportType, $dataType)
    {
        $sql = "select distinct total_object_completed_size,finish_time,error_code from bd_history_task";
        $sqlParams = array();
        $dataCenter = $this->dbSelect($sql, $sqlParams);
        $rowsInfo = array();
        if ($dataCenter) {
            foreach ($dataCenter as $d) {
                $nowTime = $d['finish_time'];
                $dataSize = $d['total_object_completed_size'];
                $errorCode = $d['error_code'];
                $rowsInfo[] = array(
                    'time' => $nowTime,
                    'data_size' => $dataSize,
                    'error_code' => $errorCode,
                    'report_type' => $reportType,
                );
            }
        }
        return array(
            'report_type' => $reportType,
            'data' =>  $rowsInfo,
        );
    }

    /**
     * 获取成功任务数
     * @return void
     */
    private function getTaskRunningNumber()
    {
        $sql = "select total_object_completed_size,start_time,error_code from bd_history_task ";
        $data = $this->dbSelect($sql);
        $successNumber = 0;
        $failedNumber = 0;
        $successCode = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $errorCode = $d['error_code'];
                if ($errorCode == $successCode) {
                    $successNumber += 1;
                }
            }
            $failedNumber = count($data) - $successNumber;
        }
        return array(
            'success_number' => $successNumber,
            'failed_number' => $failedNumber
        );
    }

    /**
     * 获取当前任务数
     * @return void
     */
    private function getCurrentTaskNumber()
    {
        $sql = "select * from bd_task";
        $data  = $this->dbSelect($sql);
        return count($data);
    }

    /**
     * 获取存储报表相关运行数据
     * @param $reportType 报表类型 2
     * @return void
     */
    private function getStorageReportRunData($reportType)
    {

        $sql = "select distinct total_object_completed_size,finish_time,task_type from bd_history_task";
        $sqlParams = array();
        $dataCenter = $this->dbSelect($sql, $sqlParams);
        $rowsInfo = array();
        if ($dataCenter) {
            foreach ($dataCenter as $d) {
                $nowTime = $d['finish_time'];
                $dataSize = $d['total_object_completed_size'];
                $taskType = $d['task_type'];
                $rowsInfo[] = array(
                    'time' => $nowTime,
                    'data_size' => $dataSize,
                    'report_type' => $reportType,
                    'task_type' => $taskType
                );
            }
        }
        return array(
            'report_type' => $reportType,
            'data' =>  $rowsInfo,
        );
//        $sql = "select module_type,total_object_completed_size,start_time,submodule_type,task_type
//                from bd_history_task";
//        $data = $this->dbSelect($sql);
//
//        $nowTime = date('Y-m-d h:m:s');
//        $backupDataValue = 0;
//        $copyDataValue = 0;
//        $archiveDataValue = 0;
//        if (!empty($data)) {
//            foreach ($data as $d) {
//                $moduleType = $d['module_type']; //模块类型
//                $submoduleType = $d['submodule_type'];
//                $taskType = $d['task_type'];
//                $taskDataType = $this->getReportDataType($moduleType, $taskType);
//                if ($taskDataType == 1) {  //备份数据
//                    $backupDataValue += $d['total_object_completed_size'];
//                } elseif ($taskDataType == 2) { //副本数据
//                    $copyDataValue += $d['total_object_completed_size'];
//                } elseif ($taskDataType == 3) {  //归档数据
//                    $archiveDataValue += $d['total_object_completed_size'];
//                }
//            }
//        }
//        $getStorageDetail = (new ReportOverView())->getStorageOverviewData();
//        $storageReportDetail = array(
//            'storage_device' => $getStorageDetail['total_storage_devices'],
//            'backup_data' => $getStorageDetail['backup_data'],
//            'on_line_number' => $getStorageDetail['online_number'],
//            'off_line_number' => $getStorageDetail['total_storage_devices'] - $getStorageDetail['online_number'],
//            'copy_data' => $getStorageDetail['copy_data'],
//            'archive_data' => $getStorageDetail['archived_data'],
//        );
//
//        $reportBkData = array(
//            'time' => $nowTime,
//            'data' => $backupDataValue,
//            'report_type' => $reportType,
//            'data_type' => 1,
//            'storage_report_detail' => $storageReportDetail
//        );
//        $reportCopyBkData = array(
//            'time' => $nowTime,
//            'data' => $copyDataValue,
//            'report_type' => $reportType,
//            'data_type' => 2,
//            'storage_report_detail' => $storageReportDetail
//        );
//        $reportArchiveBkData = array(
//            'time' => $nowTime,
//            'data' => $archiveDataValue,
//            'report_type' => $reportType,
//            'data_type' => 3,
//            'storage_report_detail' => $storageReportDetail
//        );
//
//        return $reportData = array(
//            'backup_data' => $reportBkData,
//            'copy_data' => $reportCopyBkData,
//            'archive_data' => $reportArchiveBkData
//        );
    }

    /**
     * 得到各模块的显示描述
     * @param int $moduleType module type
     * @param int $taskType   task type
     * @return string
     */
    private function getReportDataType(int $moduleType, int $taskType): string
    {
        $modeultType = 1; //备份数据
        if ($moduleType == xphp_get_config('module', 'MODULE_NAME')['copy']) {
            if (
                $taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY_FETCH'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['FILE_BACKUP_COPY'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['FILE_BACKUP_COPY_FETCH'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['DB_BACKUP_COPY'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['DB_BACKUP_COPY_FETCH'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['NAS_BACKUP_COPY'] ||
                $taskType == xphp_get_config('task', 'TASKTYPE')['NAS_BACKUP_COPY_FETCH']
            ) {
                $modeultType = 2; //副本
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
}
