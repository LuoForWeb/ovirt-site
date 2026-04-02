<?php

namespace app\v1\backupmanager\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Index;
use app\v1\job\v0\logic\JobInfo;

/**
 * note          容器 脚本管理
 * @author       jiangyongjie@vinchin.com
 * @date         2023/10/19 10:32:01
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ReportInfo extends Base
{
    /**
     * 获取虚拟机模块报表数据
     * @param $params params
     * @return void
     */
    public function getTaskReportData($params)
    {
        $pfDes = require APP_PATH . 'v1/description/Pf.php';
        $allTaskTypeDes = $pfDes['TASKTYPEDES'];
        $allModuleTypeDes = $pfDes['MODULE_TYPE_DES'];
        $jobInfo = new JobInfo();
        $sortFields = [
            'task_name' => 'bht.task_name',
            'module_type' => 'bht.module_type',
            'task_type' => 'bht.task_type',
            'start_time' => 'bht.start_time',
            'finish_time' => 'bht.finish_time',
            'total' => 'bht.total_object_size',
            'transfer_size' => 'bht.total_object_transport_size',
            'write_size' => 'bht.total_object_write_size',
            'error_code' => 'bht.error_code',
        ];
        $search = $params['search'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $taskStatus = $params['task_status'];
        $taskType = $params['task_type'];
        $moduleType = $params['module_type'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'bht.start_time';
        $order = $params['order'] ?: 'desc';

        $sql = "select  bht.task_name, bht.module_type,  bht.task_type,bht.start_time,bht.finish_time,
                bht.total_object_size,bht.total_object_transport_size,bht.total_object_write_size,bht.error_code
                from bd_history_task bht WHERE 1=1 ";
        $sqlCount = "select count(*) as total from bd_history_task bht WHERE 1=1 ";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索

        if ($this->checkEmpty($search)) {
            $sql .= ' and bht.task_name like ? ';
            $sqlCount .= 'and bht.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }
        if ($this->checkEmpty($startTime)) {
            $sql .= " and bht.start_time >= '$startTime' and bht.finish_time <=  '$endTime'";
            $sqlCount .= "and bht.start_time >= '$startTime' and bht.finish_time <=  '$endTime'";
        }
        if (!empty($moduleType)) {
            $moduleType = implode(', ', $moduleType);
            $sql .= " and bht.module_type in ($moduleType) ";
            $sqlCount .= " and bht.module_type in ($moduleType)";
        }

        if (!empty($taskType)) {
            $taskType = implode(', ', $taskType);
            $sql .= " and bht.task_type in ($taskType) ";
            $sqlCount .= " and bht.task_type in ($taskType) ";
        }
        if (!empty($taskStatus)) {
            foreach ($taskStatus as &$d) {
                if ($d == 17) {
                    $d = 0;
                } elseif ($d == 7) {
                    $d = 47;
                } elseif ($d == 4) {
                    $d = 49;
                }
            }
            unset($d);
            if (in_array(8, $taskStatus)) {  // 查询包含错误
                $otherStatus = [0, 47, 49];
                $selectStatus = array_diff($otherStatus, $taskStatus);
                $selectStatus = implode(', ', $selectStatus);
                if ($selectStatus) {
                    $sql .= " and bht.error_code not in ($selectStatus) ";
                    $sqlCount .= " and bht.error_code not in ($selectStatus) ";
                }
            } elseif (in_array(1, $taskStatus)) {
                $sql .= ' and 1<>1 ';
                $sqlCount .= ' and 1<>1 ';
            } else {
                $taskStatus = implode(',', $taskStatus);
                $sql .= " and bht.error_code in ($taskStatus) ";
                $sqlCount .= " and bht.error_code in ($taskStatus) ";
            }
        }
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $rows = array_map(function ($row) use ($jobInfo, $allTaskTypeDes, $allModuleTypeDes) {
            return [
                'task_name' => $row['task_name'],
                'task_type_des' => xphp_get_lang($allTaskTypeDes[$row['task_type']]),
                'module_type' => xphp_get_lang($allModuleTypeDes[$row['module_type']]),
                'task_type' => $row['task_type'],
                'start_time' => $row['start_time'],
                'end_time' => $row['finish_time'],
                'total' => v1_calsize($row['total_object_size'], true),
                'transfer_size' => v1_calsize($row['total_object_transport_size'], true),
                'write_size' => v1_calsize($row['total_object_write_size'], true),
                'task_status' => $jobInfo->getHistoryJobResultDes($row['error_code']),
            ];
        }, $data);
        return ['total' => $count[0]['total'], 'rows' => $rows];
    }

    /**
     * 获取nas报表数据
     * @param $params params
     * @return array
     */
    public function getNasReportData($params)
    {
        $sortFields = [
            'ip' => 'ip',
            'share_path' => 'share_path',
            'nas_type' => 'nas_type',
            'backup_status' => 'backup_status',
            'task_name' => 'task_name',
            'total_object_completed_size' => 'total_object_completed_size',
            'nas_state' => 'nas_state',
        ];
        $search = $params['search'];
        $type = $params['nas_type'];
        $backupStatus = $params['backup_status'];
        $hostStatus = $params['host_status'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'ip';
        $order = $params['order'] ?: 'desc';

        $sql = "SELECT a.*,MAX( a.start_time ) 
                FROM
	               (
	               SELECT nsr.ip,nsr.share_path,nsr.nas_type,nsr.nas_uuid,bht.total_object_completed_size,
	                      nsr.authorization_status,
	                      IF( nt.task_uuid IS NOT NULL, 1, 0 ) AS backup_status,bht.start_time,bht.task_name
	               FROM nas_storage_resource nsr
		                 INNER JOIN nas_mount_list nml ON nml.nas_uuid = nsr.nas_uuid
		                 INNER JOIN nas_task nt on nt.nas_uuid = nsr.nas_uuid
		                 LEFT JOIN bd_history_task bht ON bht.task_uuid = nt.task_uuid 
	               ) a GROUP BY a.nas_uuid HAVING 1 = 1";

        $sqlCount = "select count(*) as total
                     FROM nas_storage_resource nsr
                     INNER JOIN nas_mount_list nml  ON nml.nas_uuid =nsr.nas_uuid   
                     LEFT JOIN 
                         (SELECT nt.task_uuid,nt.nas_uuid,IF( nt.task_uuid IS NOT NULL, 1, 0 ) AS backup_status 
                          FROM nas_task nt INNER JOIN bd_history_task bht 
                          ON bht.task_uuid = nt.task_uuid GROUP BY nt.nas_uuid) 
                          nt ON nt.nas_uuid =nsr.nas_uuid WHERE 1=1";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' and  a.ip like ? ';
            $sqlCount .= ' and  nsr.ip like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }
        // 类型
        if (!empty($type)) {
            $type = implode(', ', $type);
            $sql .= " and a.nas_type in ($type) ";
            $sqlCount .= " and nsr.nas_type in ($type)";
        }

        // TODO:上次运行时间 过滤搜索，在bd_history_task 中搜时间
        if ($this->checkEmpty($startTime)) {

        }

        // 备份状态
        if (!empty($backupStatus)) {
            $backupStatus = implode(', ', $backupStatus);
            $sql .= " and a.backup_status in ($backupStatus) ";
            $sqlCount .= " and nt.backup_status in ($backupStatus) ";
        }
        // 主机状态
        if (!empty($hostStatus)) {
            $hostStatus = implode(', ', $hostStatus);
            $sql .= " and a.authorization_status in ($hostStatus) ";
            $sqlCount .= " and nsr.authorization_status in ($hostStatus) ";
        }
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $data = $this->dbSelect($sql, $sqlParams);
        $rows = array_map(function ($row) {
            return [
                'ip' => $row['ip'],
                'share_path' => $row['share_path'],
                'type' => $row['nas_type'] == 6 ? 'NFS' : 'CIFS',
                'backup_status' => $row['backup_status'],
                'backup_task' => $row['task_name'],
                'backup_data' => v1_calsize($row['total_object_completed_size'], true),
                'last_backup_time' => $row['MAX( a.start_time )'],
                'host_status' => $row['authorization_status']

            ];
        }, $data);
        return array(
            'total' => $count[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * 获取存储报表数据
     * @param $params params
     * @return array
     */
    public function getStorageReportData($params)
    {
        $pfDes = require APP_PATH . 'v1/description/Pf.php';
        $allstorageStatusDes = $pfDes['STORAGESTATUS'];
        $storageTypeDes = $pfDes['STORAGETYPE'];
        $sortFields = [
            'storage_name' => 'bs.storage_nickname',
            'storage_type' => 'bs.storage_type',
            'total' => 'bs.total_size',
            'used' => 'used_size',
            'rest' => 'bs.free_size',
            'storage_status' => 'bs.status',
            'node_name' => 'bn.node_nickname',
            'node_status' => 'bm.online_flag',
        ];
        $search = $params['search'];
        $storageType = $params['storage_type'];
        $storageStatus = $params['storage_status'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'bs.total_size';
        $order = $params['order'] ?: 'desc';

        $sql = "SELECT 
                    bs.storage_nickname, bs.storage_type, bs.total_size, bs.free_size,
                    (bs.total_size-bs.free_size) as used_size, bs.status, 
                    bn.node_nickname, bn.host_name, bn.node_type, bm.online_flag 
                FROM 
                    bd_storage_resource bs
                LEFT JOIN bd_node bn ON bn.node_uuid =bs.node_uuid
                LEFT JOIN 
                    (select online_flag, node_uuid from bd_module_server GROUP BY node_uuid) AS bm 
                ON bm.node_uuid = bs.node_uuid  WHERE 1 = 1";
        $sqlCount = "SELECT count(*) AS total
                     FROM bd_storage_resource bs
                     LEFT JOIN bd_node bn ON bn.node_uuid =bs.node_uuid
                     LEFT JOIN 
                        (select online_flag, node_uuid from bd_module_server GROUP BY node_uuid) AS bm 
                     ON bm.node_uuid = bs.node_uuid  WHERE 1 = 1";
        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' and  bs.storage_nickname like ? ';
            $sqlCount .= ' and  bs.storage_nickname like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }

        // 模块类型筛选
        if (!empty($storageType)) {
            $storageType = implode(', ', $storageType);
            $sql .= " and bs.storage_type in ($storageType) ";
            $sqlCount .= " and bs.storage_type in ($storageType)";
        }

        // 存储状态
        if (!empty($storageStatus)) {
            $storageStatus = implode(', ', $storageStatus);
            $sql .= " and bs.status in ($storageStatus) ";
            $sqlCount .= " and bs.status in ($storageStatus) ";
        }
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $rows = array_map(function ($row) use ($allstorageStatusDes, $storageTypeDes) {
            $flag = true;
            if ($row['online_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                $flag = false;
            }
            if (empty($row['online_flag'])) {
                $flag = false;
            }

            $primaryNodeDes = $row['node_type'] === 1 ?
                '(' . xphp_get_lang('UI_STORAGE_REMOTE_NODE_MASTER') . ')' :
                '(' . xphp_get_lang('UI_STORAGE_REMOTE_NODE_SUB') . ')';
            return [
                'storage_nickname' => $row['storage_nickname'],
                'storage_type' => $row['storage_type'],
                'total' => v1_calsize($row['total_size'], true),
                'rest' => v1_calsize($row['free_size'], true),
                'used' => v1_calsize($row['used_size'], true),
                'storage_status' => $row['status'],
                'node_name' => $row['node_nickname'] ? $row['node_nickname'] . $primaryNodeDes : $row['host_name'] . $primaryNodeDes,
                'node_status' => $flag,
            ];
        }, $data);
        return array(
            'total' => $count[0]['total'],
            'rows' => $rows
        );
    }

    /**
     * 获取客户端报表数据
     * @param $params params
     * @return array
     */
    public function getClientReportData($params)
    {
        $sortFields = [
            'ip' => 'ip',
            'agent_name' => 'agent_name',
            'hostname' => 'hostname',
            'os_type' => 'os_type',
            'register_time' => 'register_time',
            'backup_status' => 'backup_status',
            'online' => 'online_flag',
        ];

        $search = $params['search'];
        $osType = $params['os_type'];
        $protectStatus = $params['backup_status'];
        $online = $params['host_status'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'ip';
        $order = $params['order'] ?: 'desc';
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];

        $sql = "SELECT DISTINCT
                    ba.hostname,
                    ba.agent_name,
                    ba.ip,
                    ba.os_type,
                    bu.user_name,
                    ba.register_time,
                    ba.authorization_module,
                    ba.online_flag,
                IF ( bl.task_names IS NOT NULL, 1, 0 ) AS backup_status,
                    ba.plugin_deploy_status,
                    ba.agent_uuid,
                    task_names
                FROM
                    bd_agent ba
                    LEFT JOIN bd_user bu ON ba.user_uuid = bu.user_uuid
                    LEFT JOIN (
                    SELECT
                        group_concat( bt.task_name SEPARATOR ', ' ) AS task_names,
                        bl.agent_uuid,
                        bl.task_uuid
                    FROM
                        bd_task_agent_list bl
                        INNER JOIN bd_task bt ON bl.task_uuid = bt.task_uuid
                    GROUP BY
                        bl.agent_uuid 
                    ) bl ON bl.agent_uuid = ba.agent_uuid 
                    WHERE
                        ba.agent_type NOT IN (3,4) ";
        $sqlParams = array();
        $sqlCountParams = array();

        // 按名字搜索
        if ($this->checkEmpty($search)) {
            $sql .= ' and  ba.ip like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $search . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $search . '%'));
        }
        //操作系统
        if (!empty($osType)) {
            $result = array();
            foreach ($osType as $item) {
                $result[] = "'" . $item . "'";
            }
            $string = implode(', ', $result);
            $sql .= " and  ba.os_type  in ($string)";
        }

        // 时间搜索
        if ($this->checkEmpty($startTime)) {
            $sql .= " and ba.register_time >= '$startTime' and ba.register_time <=  '$endTime'";
        }

        // 备份状态
        if (!empty($protectStatus)) {
            if (count($protectStatus) == 1) {
                if ($protectStatus[0] == 0) {
                    $sql .= " and task_names IS NULL ";
                } else {
                    $sql .= " and task_names IS NOT NULL ";
                }
            }

        }
        // 主机状态
        if (!empty($online)) {
            $hostStatus = implode(', ', $online);
            $sql .= " and ba.online_flag in ($hostStatus) ";
        }
        $sqlCount = $sql;
        $sql .= " GROUP BY ba.agent_uuid";
        $isExport = $params['isExport'];
        if (!$isExport) {
            $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $countData = $this->dbSelect($sqlCount, $sqlCountParams);
        $count = count($countData);
        $rows = array_map(function ($row) {
            $totalSize = $this->getTotalSize($row['agent_uuid']);
            return [
                'agent_name' => $row['agent_name'],
                'hostname' => $row['hostname'] ? $row['hostname'] : '--',
                'ip' => $row['ip'],
                'os_type' => $row['os_type'],
                'add_time' => $row['register_time'],
                'backup_status' => $row['backup_status'],
                'register_time' => $row['register_time'] ? $row['register_time'] : '--',
                'backup_task' => $row['task_names'] ? $row['task_names'] : '--',
                'backup_data' => $totalSize ? v1_calsize($totalSize, true) : '--',
                'host_status' => $row['online_flag'],
            ];
        }, $data);
        return ['total' => $count, 'rows' => $rows];
    }
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
     * 获取虚拟机报表数据详情
     * @param $params params
     * @return void
     */
    public function getVmReportData($params)
    {
        $sortFields = [
            'ip' => 'vv.vcenter_ip',
            'vm_name' => 'vv.vm_name',
            'hostname' => 'vv.vm_type',
            'backup_status' => 'backup_status',
        ];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'vv.vcenter_ip';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];    //模块匹配IP或主机名
        $vmType = $params['vm_type'];  //虚拟机类型
        $backupStatus = $params['backup_status'];  //备份状态 1.保护中，未保护
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP']; //备份
        $sql = "select vm.vm_uuid,vm.vm_name,vm.vcenter_uuid,vv.vcenter_ip,vv.hypervisor_type,
                IF (vml.task_uuid IS NOT NULL, 1, 0) AS backup_status
                from vm_machine vm
                LEFT JOIN vm_vcenter vv ON vv.vcenter_uuid = vm.vcenter_uuid 
                LEFT JOIN vm_machine_list vml ON vml.vm_uuid = vm.vm_uuid 
                where  1=1  ";
        if (!empty($vmType)) {  // 主机状态
            $vmType = implode(', ', $vmType);
            $sql .= ' and vv.hypervisor_type in  (' . $vmType . ') ';
        }
        // 备份状态
        if (!empty($backupStatus) && count($backupStatus) == 1) {
            $backupStatus = implode(', ', $backupStatus);
            if ($backupStatus) {
                $sql .= ' AND vml.task_uuid IS NOT NULL ';
            } else {
                $sql .= ' AND vml.task_uuid IS NULL ';
            }
        }
        if ($this->checkEmpty($searchName)) {
            $sql .= ' and vm.vm_name like "%' . $searchName . '%" ';
        }

        $countSql = $sql;
        $countData = $this->dbSelect($countSql);

        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);
        $vmReportArray = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $vmUuid = $d['vm_uuid'];
                $vmName = $d['vm_name'];
                $vcenterIp = $d['vcenter_ip'];
                $vmType = $d['hypervisor_type'];
                $backupStatus = $d['backup_status'];
                $vmRunningInfo = $this->getVmRunningInfo($moduleType, $taskType, $vmUuid);
                $backupStatusInfo = $this->getVmProtectionState($vmUuid);
                $vmReportArray[] = array(
                    'vm_name' => $vmName,
                    'vm_type' => $vmType,
                    'vcenter_ip' => $vcenterIp,
                    'last_backup_time' => $vmRunningInfo['last_backup_time'],
                    'backup_task' => $backupStatusInfo['task_name'],
                    'backup_status' => $backupStatus,
                    'backup_count' => $vmRunningInfo['backup_count'],
                    'backup_data' => v1_calsize($vmRunningInfo['backup_data'], true),
                );
            }
        }
        return ['total' => count($countData), 'rows' => $vmReportArray];
    }

    /**
     * 获取虚拟机保护状态
     * @param $vmUuid vm uuid
     * @return array
     */
    private function getVmProtectionState($vmUuid)
    {
        $sql = "select bt.task_name 
                from bd_task bt,vm_machine_list vml 
                where vml.task_uuid = bt.task_uuid and vml.vm_uuid = ?";
        $data = $this->dbSelect($sql, array($vmUuid));
        $backupSet = xphp_get_config('app', 'FLAG')['UNSET'];
        $taskName = '--';
        if (!empty($data)) {
            $backupSet = xphp_get_config('app', 'FLAG')['SET'];
            $taskName = $data[0]['task_name'];
        }
        return array(
            'backup_status' => $backupSet,
            'task_name' => $taskName
        );
    }

    /**
     * 获取虚拟机上次备份时间
     * @param $moduleType module type
     * @param  $taskType   task type
     * @param $vmUuid     vm uuid
     * @return false|string
     */
    private function getVmRunningInfo($moduleType, $taskType, $vmUuid)
    {
        $sql = "select start_time,details,total_object_completed_size 
                from bd_history_task 
                where module_type = ? and task_type = ?";
        $data = $this->dbSelect($sql, array($moduleType, $taskType));
        $moduleDataList = [];
        $totalObjectCompletedSize = 0;
        $backupCount = 0;
        foreach ($data as $d) {
            $startTime = $d['start_time'];
            $details = json_decode($d['details'], true);
            $vmsDetails = $details['vms_details'];
            $taskVmUuid = $vmsDetails[0]['vm_uuid'];
            if ($taskVmUuid == $vmUuid) {
                $moduleDataList[] = $startTime;
                $backupCount += 1;
                $totalObjectCompletedSize += $d['total_object_completed_size'];
            }
        }
        $timestampList = array_map(function ($item) {
            return strtotime($item);
        }, $moduleDataList);
        $maxTimestamp = empty($timestampList) ? 0 : max($timestampList); // 获取最大的时间戳
        $lastRunningTime = '--';
        if (!empty($maxTimestamp)) {
            $lastRunningTime = date('Y - m - d H : i : s', $maxTimestamp);  // 将时间戳转换为日期格式,返回
        }
        return array(
            'last_backup_time' => $lastRunningTime,
            'backup_count' => $backupCount,
            'backup_data' => $totalObjectCompletedSize,
        );
    }

    /**
     * 获取文件报表数据详情
     * @param $params params
     * @return void
     */
    public function getFileReportData($params)
    {
        $sortFields = [
            'ip' => 'ba.ip',
            'host_name' => 'ba.hostname',
            'os_type' => 'ba.vm_type',
            'backup_status' => 'ba.os_type',
            'backup_status' => 'ba.os_type',
            'host_status' => 'ba.online_flag',
        ];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'ba.ip';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];    //模块匹配IP或主机名
        $hostStatus = $params['host_status'];  //主机状态
        $backupStatus = $params['backup_status'];  //备份状态1.保护中，未保护
        $osType = $params['os_type'];
        $reportType = '';
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $flagSet = xphp_get_config('app', 'FLAG')['SET'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP']; //备份
        $sql = "select DISTINCT ba.ip,ba.agent_name,ba.hostname,ba.agent_uuid,ba.online_flag,ba.os_type,
                IF (bt.task_uuid IS NOT NULL, 1, 0) AS backup_status
                from bd_agent ba
                LEFT JOIN bd_task_agent_list btal ON ba.agent_uuid = btal.agent_uuid
                LEFT JOIN bd_task bt ON btal.task_uuid = bt.task_uuid
                where  1=1 ";
        if (!empty($hostStatus)) {  // 主机状态
            $hostStatus = implode(', ', $hostStatus);
            $sql .= " and ba.online_flag in ($hostStatus)";
        }
        if (!empty($backupStatus) && count($backupStatus) == 1) {
            $backupStatus = implode(', ', $backupStatus);
            if ($backupStatus) {
                $sql .= ' AND bt.task_uuid IS NOT NULL ';
            } else {
                $sql .= ' AND bt.task_uuid IS NULL ';
            }
        }
        if (!empty($osType)) {
            $result = array();
            foreach ($osType as $item) {
                $result[] = "'" . $item . "'";
            }
            $string = implode(', ', $result);
            $sql .= " and  ba.os_type  in ($string)";
        }
        if ($this->checkEmpty($searchName)) {
            $sql .= ' and (ba.agent_name like "%' . $searchName . '%" or ba.ip like "%' . $searchName . '%") ';
        }

        $countSql = $sql;
        $countData = $this->dbSelect($countSql);

        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);

        $fileReportArray = array();
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        if (!empty($data)) {
            foreach ($data as $d) {
                $agentName = !empty($d['agent_name']) ? $d['agent_name'] : $nullSpace;
                $hostName = !empty($d['hostname']) ? $d['hostname'] : $nullSpace;
                $agentUuid = $d['agent_uuid'];

                $backupStatusInfo = $this->getAgentProtectionState($agentUuid, $moduleType);
                $agentRunningInfo = $this->getAgentRunningInfo($moduleType, $agentUuid, $taskType);
                $backupStatus = $d['backup_status'];
                $backupTaskName = $backupStatusInfo['task_name'];
                //$backupStatus = $backupStatusInfo['backup_status'];
//                $moduleBackupInfo =  $this->getAgentModuleBackupCount($moduleType, $taskType, $agentUuid);
                $fileReportArray[] = array(
                    'ip' => $d['ip'],
                    'host_name' => $hostName,
                    'agent_name' => $agentName,
                    'os_type' => $d['os_type'],
                    'host_status' => $d['online_flag'],
                    'backup_status' => $backupStatus,
                    'last_backup_time' => $agentRunningInfo['last_backup_time'],
                    'backup_task' => $backupTaskName,
                    'backup_count' => $agentRunningInfo['backup_data'],
                    'backup_data' => $agentRunningInfo['backup_count']
                );
            }
        }
        return array(
            'total' => count($countData),
            'rows' => $fileReportArray
        );
    }

    /**
     * 获取数据库模块报表详情
     * @param $params params
     * @return void
     */
    public function getDbReportData($params)
    {
        $sortFields = [
            'database_name' => 'baa.app_name',
            'database_type' => 'baa.app_type',
        ];

        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'app_type';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];
        $dbType = $params['database_type'];  //数据类型
        $backupStatus = $params['backup_status'];  //备份状态1.保护中，未保护

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['DB'];
        $flagSet = xphp_get_config('app', 'FLAG')['SET'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']; //数据库备份
        $taskTypeStr = xphp_get_config('db', 'DB_TYPE_DES'); //数据库备份
        $sql = 'SELECT DISTINCT baa.app_name,baa.app_type,baa.agent_uuid, 
                IF (dl.task_uuid IS NOT NULL, 1, 0) AS backup_status
                from bd_agent_app baa
                LEFT JOIN db_list dl ON dl.agent_uuid = baa.agent_uuid
                where 1=1 ';
        if ($this->checkEmpty($searchName)) {
            $sql .= ' and baa.app_name like "%' . $searchName . '%" ';
        }
        if (!empty($backupStatus) && count($backupStatus) == 1) {
            $backupStatus = implode(', ', $backupStatus);
            if ($backupStatus) {
                $sql .= ' AND dl.task_uuid IS NOT NULL ';
            } else {
                $sql .= ' AND dl.task_uuid IS NULL ';
            }
        }
        if (!empty($dbType)) {
            $dbType = implode(', ', $dbType);
            $sql .= "and baa.app_type in ($dbType)";
        }
        $countSql = $sql;
        $dataCount = $this->dbSelect($countSql);
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);
        $dbReportList = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $appName = $d['app_name'];
                $appType = $taskTypeStr[$d['app_type']];
                $agentUuid = $d['agent_uuid'];
                $backupStatusInfo = $this->getDbInsBackupStatus($appName);
                $backupTaskName = $backupStatusInfo['task_name'];
                $backupStatus = $d['backup_status'];
                $agentRunningInfo = $this->getAgentRunningInfo($moduleType, $agentUuid, $taskType);
                //                $moduleBackupInfo =  $this->getAgentModuleBackupCount($moduleType, $taskType, $agentUuid);
                $dbReportList[] = array(
                    'database_name' => $appName,
                    'database_type' => $appType,
                    'backup_status' => $backupStatus,
                    'last_backup_time' => $agentRunningInfo['last_backup_time'],
                    'backup_task' => $backupTaskName,
                    'backup_data' => v1_calsize($agentRunningInfo['backup_data'], true),
                    'backup_count' => $agentRunningInfo['backup_count']
                );
            }
        }
        return array(
            'total' => count($dataCount),
            'rows' => $dbReportList
        );
    }

    /**
     * 获取数据库实例保护状态
     * @param $insName ins name
     * @return void
     */
    private function getDbInsBackupStatus($insName)
    {
        $sql = "select DISTINCT bt.task_name 
                from db_list dl,bd_task bt where dl.instance_name = ? and bt.task_uuid = dl.task_uuid";
        $data = $this->dbSelect($sql, array($insName));
        $backupSet = xphp_get_config('app', 'FLAG')['UNSET'];
        $taskName = '--';
        if (!empty($data)) {
            $backupSet = xphp_get_config('app', 'FLAG')['SET'];
            $taskName = $data[0]['task_name'];
        }
        return array(
            'backup_status' => $backupSet,
            'task_name' => $taskName
        );
    }

    /**
     * 获取操作系统报表详情
     * @param $params params
     * @return void
     */
    public function getOsReportData($params)
    {
        $sortFields = [
            'ip' => 'ba.ip',
            'host_name' => 'hostname',
            'os_type' => 'os_type',
            'host_status' => 'online_flag',
        ];

        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'ip';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];
        $osType = $params['os_type'];
        $backupStatus = $params['backup_status'];
        $hostStatus = $params['host_status'];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        $backupSet = xphp_get_config('app', 'FLAG')['SET'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP']; //操作系统备份
        $sql = 'select DISTINCT ba.ip,ba.hostname,ba.agent_name,ba.online_flag,ba.agent_uuid,ba.os_type, 
                 IF (bt.task_uuid IS NOT NULL, 1, 0) AS backup_status
                 from bd_agent ba
                 left join bd_task_agent_list btal on btal.agent_uuid = ba.agent_uuid
                 left join bd_task bt on bt.task_uuid = btal.task_uuid
                 where 1 =1 ';
        if ($this->checkEmpty($searchName)) {
            $sql .= ' and ba.ip like "%' . $searchName . '%" ';
        }

        if (!empty($backupStatus) && count($backupStatus) == 1) {
            $backupStatus = implode(', ', $backupStatus);
            if ($backupStatus) {
                $sql .= ' AND bt.task_uuid IS NOT NULL ';
            } else {
                $sql .= ' AND bt.task_uuid IS NULL ';
            }
        }
        if (!empty($osType)) {
            $result = array();
            foreach ($osType as $item) {
                $result[] = "'" . $item . "'";
            }
            $string = implode(', ', $result);
            $sql .= " and  ba.os_type  in ($string)";
        }
        if (!empty($hostStatus)) {  // 主机状态
            $hostStatus = implode(', ', $hostStatus);
            $sql .= " and ba.online_flag  in ($hostStatus)";
        }

        $countSql = $sql;
        $dataCount = $this->dbSelect($countSql);
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $data = $this->dbSelect($sql);
        $rowsInfo = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $onlineFlag = $d['online_flag'];
                $agentUuid = $d['agent_uuid'];
                $backupStatusInfo = $this->getAgentProtectionState($agentUuid, $moduleType);
                $backupTaskName = $backupStatusInfo['task_name'];
                $backupStatus = $d['backup_status'];
                $agentRunningInfo = $this->getAgentRunningInfo($moduleType, $agentUuid, $taskType);
                //                $moduleBackupInfo =  $this->getAgentModuleBackupCount($moduleType, $taskType, $agentUuid);
                $rowsInfo[] = array(
                    'ip' => $d['ip'],
                    'host_name' => $d['hostname'],
                    'os_type' => $d['os_type'],
                    'backup_status' => $backupStatus,
                    'last_backup_time' => $agentRunningInfo['last_backup_time'],
                    'backup_task' => $backupTaskName,
                    'backup_data' => v1_calsize($agentRunningInfo['backup_data'], true),
                    'backup_count' => $agentRunningInfo['backup_count'],
                    'host_status' => $onlineFlag
                );
            }
        }

        return array(
            'total' => count($dataCount),
            'rows' => $rowsInfo
        );
    }

    /**
     * 获取客户端备份次数
     * @param $moduleType module type
     * @param $taskType   任务类型
     * @param $agentUuid  agent uuid
     * @return void
     */
    private function getAgentModuleBackupCount($moduleType, $taskType, $agentUuid)
    {
        $sql = "select details,total_object_completed_size 
                from bd_history_task 
                where module_type = ? and task_type = ?";
        $data = $this->dbSelect($sql, array($moduleType, $taskType));
        $backupCount = 0;
        $totalObjectCompletedSize = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $totalObjectCompletedSize += $d['total_object_completed_size'];
                $details = json_decode($d['details'], true);
                if ($moduleType == xphp_get_config('module', 'MODULE_TYPE')['OS']) {
                    foreach ($details as $de) {
                        $masterAgentUuid = $de['agent_uuid'];
                        if ($agentUuid == $masterAgentUuid) {
                            $backupCount = $backupCount + 1;
                        }
                    }
                }
            }
        }
        return $historyData = array(
            'backup_data' => $totalObjectCompletedSize,
            'backup_count' => $backupCount
        );
    }

    /**
     * 获取客户端模块保护状态
     * @param $uuid       uuid
     * @param $moduleType moduleType
     * @return void
     */
    private function getAgentProtectionState($uuid, $moduleType)
    {
        $sql = "select bt.task_name from bd_task_agent_list btal,bd_task bt  
            where bt.task_uuid = btal.task_uuid and bt.module_type = ? and btal.agent_uuid = ?";
        $data = $this->dbSelect($sql, array($moduleType, $uuid));
        $taskArray = array();
        if (!empty($data)) {
            return array(
                'task_name' => $data[0]['task_name'],
                'backup_status' => xphp_get_config('app', 'FLAG')['SET']
            );
        } else {
            return array(
                'task_name' => '--',
                'backup_status' => xphp_get_config('app', 'FLAG')['UNSET']
            );
        }
    }

    /**
     * @param $params 卷CDP模块报表数据
     * @return array|void
     */
    public function getVolCdpReportData($params)
    {
        $sortFields = [
            'ip' => 'ba.ip',
            'host_name' => 'hostname',
            'os_type' => 'os_type',
            'host_status' => 'online_flag',
        ];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'start_time';
        $order = $params['order'] ?: 'desc';
        $searchName = $params['search'];
        $backupStatus = $params['task_type'];  //任务类型
        $hostStatus = $params['result']; //执行结果
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $sqlCount = "select id,task_name,module_type,task_type,total_object_size,start_time,details,error_code 
                        from bd_history_task 
                        where module_type = ?";
        $sql = $sqlCount;

        $sqlParams = array($moduleType, );
        $sqlCountParams = array($moduleType);
        if ($this->checkEmpty($searchName)) {
            $sql = $sql . ' and task_name like ? ';
            $sqlCount .= ' and task_name like ? ';
            $sqlParams = array_merge($sqlParams, array(' % ' . $searchName . ' % '));
            $sqlCountParams = array_merge($sqlCountParams, array(' % ' . $searchName . ' % '));
        }
        // 备份状态
        if (!empty($backupstatus)) {
            $sql .= " and error_code in ($hostStatus) ";
            $sqlCount .= " and ba.error_code in ($hostStatus)";
        }
        $sql .= " ORDER BY $sort $order LIMIT $offset, $limit ";
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $data = $this->dbSelect($sql, $sqlParams);

        $count = count($dataCount);
        $rowsInfo = array();

        if ($count > 0) {
            foreach ($data as $d) {
                $details = json_decode($d['details'], true);
                $agentIp = $details[0]['agent_ip'];
                $agentName = $details[0]['agent_name'];
                $hostName = $details[0]['hostname'];
                $masterAgentUuid = $details[0]['master_agent_uuid'];
                $historyId = $d['id'];
                $taskName = $d['task_name'];
                //                $taskType = $d['task_type'];
                $backupData = $d['total_object_size'];
                $errorCode = $d['error_code'];
                $agentRunningInfo = $this->getAgentRunningInfo($moduleType, $masterAgentUuid, $taskType);
                $rowsInfo[] = array(
                    'ip' => $agentIp,
                    'host_name' => $hostName,
                    'backup_status' => $this->getHistoryJobResultDes($errorCode),
                    'last_backup_time' => $agentRunningInfo['last_backup_time'],
                    'backup_task' => $taskName,
                    'backup_data' => v1_calsize($backupData, true),
                    'host_status' => $this->getHostInfo($masterAgentUuid),
                    'backup_set_count' => $this->getHostBackupSetInfo($masterAgentUuid)
                );
            }
        }
        return array(
            'total' => $count,
            'rows' => $rowsInfo
        );
    }

    /**
     * 得到历史任务结果描述
     * @param int $errorCode 错误码
     * @return string
     */
    private function getHistoryJobResultDes(int $errorCode)
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
     * 获取客户端模块任务上次运行时间
     * @param $moduleType 模块类型
     * @param $agentUuid  主机uuid
     * @param $taskType   task type
     * @return void
     */
    private function getAgentRunningInfo($moduleType, $agentUuid, $taskType)
    {
        $sql = "select start_time,details,total_object_completed_size 
                from bd_history_task 
                where module_type = ? and task_type = ?";
        $data = $this->dbSelect($sql, array($moduleType, $taskType));
        $moduleDataList = [];
        $backupCount = 0;
        $totalObjectCompletedSize = 0;
        foreach ($data as $d) {
            $startTime = $d['start_time'];
            $totalObjectCompletedSize += $d['total_object_completed_size'];
            $details = json_decode($d['details'], true);
            $masterAgentUuid = $details['master_agent_uuid'];
            if ($agentUuid == $masterAgentUuid) {
                $moduleDataList[] = $startTime;
                $backupCount += 1;
            }
        }
        $timestampList = array_map(function ($item) {
            return strtotime($item);
        }, $moduleDataList);
        $maxTimestamp = max($timestampList); // 获取最大的时间戳
        $lastRunningTime = '--';
        if (!empty($maxTimestamp)) {
            $lastRunningTime = date('Y - m - d H : i : s', $maxTimestamp);  // 将时间戳转换为日期格式,返回
        }
        return array(
            'last_backup_time' => $lastRunningTime,
            'backup_count' => $backupCount,
            'backup_data' => $totalObjectCompletedSize,
        );
    }

    /**
     * 获取客户端生成备份集状态
     * @param $uuid uuid
     * @return void
     */
    private function getHostBackupSetInfo($uuid)
    {
        $sql = "select * from cdp_vol_backup_agent where master_agent_uuid =?";
        $data = $this->dbSelect($sql, array($uuid));
        return count($data);
    }

    /**
     * 获取客户端信息
     * @param $uuid 主机uuid
     * @return void
     */
    private function getHostInfo($uuid)
    {
        $sql = "select online_flag from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $agentOnline = 0;
        if (!empty($data)) {
            $agentOnline = $data[0]['online_flag'];
        }
        return $agentOnline;
    }
}
