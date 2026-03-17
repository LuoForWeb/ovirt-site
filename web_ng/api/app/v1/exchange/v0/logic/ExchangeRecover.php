<?php

namespace app\v1\exchange\v0\logic;

use app\v1\backupData\v0\logic\DataManage;
use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\JobController;
use app\v1\common\logic\Recover;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Notice;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;
use app\v1\resources\v0\logic\Storage;
use BLLHandler;
use phpseclib3\File\ASN1\Element;

/**
 * note          office365（exchange） 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeRecover extends Recover
{
    /**
     * 获取组织和任务备份数据
     * @return string 组织和任务备份数据
     * @param $params 参数
     */
    public function getRestoreData($params)
    {
        $dataflag = v1_parse_flag_to_bool($params['data_flag']);
        $storageUuid = $params['storage_uuid'];
        $sql = "select bbt.id, bbt.module_type, bbt.task_type, bbt.timepoint_uuid, bbt.depend_point_uuid,
        unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.copy_flag,
        bbt.src_data_deleted_flag,bbt.user_uuid, bbt.user_name,bbt.task_name, mbt.organization_info 
        from bd_backup_timepoint bbt, m365_backup_timepoint mbt, bd_storage_resource bsr
        where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and
        bbt.deleted_flag = ? and bbt.available_flag = ? 
        and bbt.import_flag = ? and
	    bbt.module_type in (14) and
	    bbt.data_local_flag = ? and  bbt.task_type in (1,17,18)";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源数据
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源数据
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['m365']
            );
            $sql .= " and bbt.user_uuid in ({$userUuidSql}) ";
        }
        if ($dataflag) {
            $sql .= ' and bbt.copy_flag = ? and bbt.module_type = ? ';
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], 14));
        }
        if (!empty($storageUuid)) {
            $sql .= ' and bsr.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        $sql .= " order by bbt.task_uuid, bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        //定义organization task 数组
        $organization = array();
        $task = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID(14);
        foreach ($data as $d) {
            $organizationInfo = json_decode($d['organization_info'], true);
            $organizationUuid = $organizationInfo['organization_uuid'];
            $organizationName = $organizationInfo['organization_name'];
            $organizationRegion = $organizationInfo['region'];
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            $taskAvailable = in_array($taskuuid, $currentTaskUUID);
            $name = $taskAvailable ? $taskName : $taskName . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';
            if ($d['copy_flag'] == $flag['SET']) {
                $name .= '(' . xphp_get_lang('UI_COPY_DATA') . ')';
            }
            //            if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] && $dataflag) {
//                c ontinue;
//            }
            //检查并添加组织
            if (!in_array($organizationUuid, $organization)) {
                $organization[] = $organizationUuid;
                //                if ($d['module_type'] == xphp_get_config('MODULE_TYPE')['BACKUP_COPY_CLIENT']) {
//                    if (intval($d['task_type']) == xphp_get_config('task', 'TASKTYPE')['NAS_BACKUP_COPY'] || intval($d['task_type']) == xphp_get_config('task', 'TASKTYPE')['NAS_BACKUP_COPY_FETCH']) {
//                        $name = $taskName . '(' .xphp_get_lang('UI_COPY_DATA') . ')';
//                    } elseif (intval($d['task_type']) == xphp_get_lang('TASKTYPE')['ARCHIVE'] || intval($d['task_type']) == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']) {
//                        $name = $taskName . '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')';
//                    }
//                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != xphp_get_user_info()['userUuid']) {
                    $name .= '(' . $d['user_name'] . ')';
                }
                $node[] = array(
                    'id' => $organizationUuid,
                    'pId' => 0,
                    'name' => $organizationName,
                    'title' => $organizationName,
                    'open' => true,
                    'nocheck' => true,
                    'dir_type' => 1,
                    'iconSkin' => $this->getIconSkin(10000),
                    'node_uuid' => $organizationInfo['node_uuid'],
                    'job_uuid' => $taskuuid,
                    'isParent' => true,
                    'organization_uuid' => $organizationUuid,
                    'region' => $organizationRegion,
                );
            }
            //检查并添加task
            if (!in_array($organizationUuid . '_' . $taskuuid, $task)) {
                $task[] = $organizationUuid . '_' . $taskuuid;
                $node[] = array(
                    'id' => $organizationUuid . '_' . $taskuuid,
                    'pId' => $organizationUuid,
                    'name' => $name,
                    'title' => $name,
                    'nocheck' => !$dataflag,
                    'dir_type' => 2,
                    'clickshow' => true,
                    'iconSkin' => $this->getIconSkin(3),
                    'node_uuid' => $organizationInfo['node_uuid'],
                    'job_uuid' => $taskuuid,
                    'isParent' => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                );
            }
        }
        return $node;
    }

    /**
     * 获取某个任务下的恢复点
     * @param $params 参数
     * @return object 某个任务下的恢复点
     */
    public function getRestorePoints($params)
    {
        $dataflag = v1_parse_flag_to_bool($params['data_flag']);
        $taskuuid = $params['job_uuid'];
        $organizationuuid = $params['organization_uuid'];
        $storageUuid = $params['storage_uuid'];
        $chkDisabled = false;
        $search = $params['search'];
        if (!empty($params['table_flag'])) {
            $limit = $params['limit'];
            $offset = $params['offset'];
        }
        $sql = "select bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, bbt.task_name, bbt.real_node_uuid,bbt.storage_uuid,
        unix_timestamp(bbt.timepoint) timepoint,bbt.backup_mode,bbt.total_size, bbt.write_size, bbt.task_uuid, bbt.importance_flag,bbt.encrypted_flag, bbt.integrity_check_flag,
        bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status,
        bbt.remarks,bbt.detail,bbt.src_data_deleted_flag,mbt.organization_info as mbtdetail,bu.user_name,bsr.node_uuid,bsr.storage_type,bsr.storage_uuid, bsr.storage_nickname
        from m365_backup_timepoint mbt, bd_user bu,bd_storage_resource bsr,bd_backup_timepoint bbt 
        left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
        where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and
        bbt.deleted_flag = ? and bbt.available_flag = ? and bbt.import_flag = ? and bbt.module_type in (14) and
        bbt.user_uuid = bu.user_uuid and bbt.task_uuid = ? and bbt.data_local_flag = ? and JSON_EXTRACT(mbt.organization_info, '$.organization_uuid') = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $taskuuid, $flag['SET'], $organizationuuid);
        //        if (!empty($nodeuuid)) {
//            $sql .= ' and bsr.node_uuid = ? ';
//            $sqlParams = array_merge($sqlParams, array($nodeuuid));
//        }
        $userInfo = xphp_get_user_info();
        // 非租户用户不能查看租户的资源
        if (empty($userInfo['tenantuuid'])) {
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }
        if (!empty($params['timepoint_type'])) {
            $sql .= " and bbt.backup_mode like '%" . $params['timepoint_type'] . "%'";
        }
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $sql .= ' and bbt.timepoint between ? and ? ';
            $sqlParams = array_merge($sqlParams, array($params['start_time'], $params['end_time']));
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        if (!empty($search) || !empty($params['forever'])) {
            $info = $this->getExchangeSearch((string) $search, $nodeuuid);
            return $info;
        }
        //        $sql .= " order by bbt.timepoint";
        $total = $this->dbSelect($sql, $sqlParams);
        if (!empty($params['table_flag'])) {
            //排序
            if (!empty($params['sort']) && !empty($params['order'])) {
                $sortType = !in_array(strtolower($params['order']), ['desc', 'asc']) ? 'desc' : strtolower($params['order']);
                $sortArr = [
                    'time_point' => 'bbt.timepoint',
                    'type_des' => 'bbt.backup_mode',
                    'time_des' => 'bbt.backup_mode',
                    'total_size' => 'bbt.total_size',
                    'write_size' => 'bbt.write_size',
                ];
                $sort = empty($sortArr[$params['sort']]) ? ' bbt.timepoint' : ($sortArr[$params['sort']] . ' ' . $sortType . ', bbt.timepoint');
                $sql .= " order by " . $sort;
            }
            $sql .= ' limit ? , ?';
            $sqlParams = array_merge($sqlParams, array($offset, $limit));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }
        $i = 1;
        if (is_array($total)) {
            $count = count($total);
        } else {
            $count = 0; // 如果不是一个数组，则总数为0
        }
        $node = array(
            'rows' => array(),
            'total' => $count,
        );
        $storageHandler = new Storage();
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);
        $storageOfflineStatus = xphp_get_config('storage', 'STORAGE_STATUS')['OFFLINE'];
        foreach ($data as $d) {
            $pointMixedStatus = $this->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
            // 判断当前时间点所在存储是否离线，是则置灰节点
            $isOfflineStorage = 1;
            foreach ($storageStatusList as $key => $storageStatusInfo) {
                if ($d['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                    $isOfflineStorage = $storageStatusInfo['storage_status'];
                    break;
                }
            }
            $realNode = (new Node())->getNodeUUIDWithTimepointUUID($d['timepoint_uuid']);
            $mbtdetail = json_decode($d['mbtdetail'], true);
            $detail = json_decode($d['detail'], true);
            $timeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[$d['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
            $name = $this->parseDate($d['timepoint']) . ' (' . $timeDes . ')';
            $title = $name;
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')' . '</span>';
            }
            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock" style="font-family: FontAwesome;"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }
            $remark = '';
            $mark = '';
            $chkDisabled = $isOfflineStorage == $storageOfflineStatus;
            if ($dataflag) {
                if ($d['backup_mode'] == 2) { //增备禁用复选框
                    $chkDisabled = true;
                }
                //永久标记
                $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
                if (!empty($mark)) {
                    $remark .= $mark;
                }
                //备注
                if (!empty($d['remarks'])) {
                    $remark .= '<a class="popovers remarktips remarktips_' . $d['timepoint_uuid'] . '" data-container="body" data-trigger="hover" data-placement="right" style="vertical-align: bottom;"
                data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
                }
            }
            $timepointuuid = $d['timepoint_uuid'];
            $owner = (new ExchangeOrganization())->getOganizationUuidName($organizationuuid, $d['user_name']);
            //            if (intval($d['backup_mode']) == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
            $node['rows'][] = array(
                'num' => $i++,
                'id' => $d['timepoint_uuid'],
                'pId' => $organizationuuid . '_' . $taskuuid,
                'name' => $name . $mark,
                'title' => $title,
                'checked' => false,
                'dir_type' => 3,
                'nocheck' => !$dataflag,
                'old_name' => $name,
                'gfsforever' => '',
                'depend_uuid' => $d['depend_point_uuid'],
                'organization_name' => $mbtdetail['organization_name'],
                'organization_uuid' => $mbtdetail['organization_uuid'],
                'job_name' => $d['task_name'],
                'star' => intval($d['importance_flag']) == $flag['SET'],
                'iconSkin' => $this->getTimepointIcon($d['backup_mode']),
                'mode' => intval($d['backup_mode']),
                'timepoint_uuid' => $d['timepoint_uuid'],
                'job_uuid' => $taskuuid,
                'node_uuid' => $realNode,
                'storage_name' => $this->getStorageName($d['storage_uuid'], $realNode),
                'time_point' => $this->parseDate($d['timepoint']),
                'time_des' => $timeDes,
                'encrypted_flag' => $encryptedflag,
                'isParent' => !($isOfflineStorage == $storageOfflineStatus),
                'type' => '10000', //选时间点相当于选择整个组织
                'type_des' => $d['backup_mode'] == 1 ? xphp_get_lang('WEB_M365_FULL_BACKUP_POINT') : xphp_get_lang('WEB_M365_INCRE_BACKUP_POINT'),
                'password_auto_flag' => intval($detail['password_auto_flag']) == 1 ? true : false,
                'src_data_deleted_flag' => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                'total_size' => v1_calsize($d['total_size'], true) == '--' ? '0B' : v1_calsize($d['total_size'], true),
                'write_size' => v1_calsize($d['write_size'], true) == '--' ? '0B' : v1_calsize($d['write_size'], true),
                'remarks' => $d['remarks'],
                'remarks_span' => $remark,
                'chkDisabled' => $chkDisabled,
                'storage_type' => $d['storage_type'],
                'owner' => $owner,
                'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                "point_status" => $pointMixedStatus['status'],
                "available_flag" => $pointMixedStatus['available_flag'],
            );
        }
        return $node;
    }

    /**
     * 时间点搜索--左侧筛选
     * @param string $search   搜索内容
     * @param string $nodeuuid nodeuuid
     * @return array 搜索结果
     */
    public function getExchangeSearch(string $search, $nodeuuid)
    {
        $sql = "select bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, bbt.task_name, bbt.real_node_uuid, bsr.storage_uuid, 
        unix_timestamp(bbt.timepoint) timepoint,bbt.backup_mode,bbt.total_size, bbt.write_size, bbt.task_uuid, bbt.importance_flag,bbt.encrypted_flag,
        bbt.remarks,bbt.detail,bbt.src_data_deleted_flag,mbt.organization_info as mbtdetail,bsr.node_uuid,bsr.storage_type
        from bd_backup_timepoint bbt, m365_backup_timepoint mbt,bd_storage_resource bsr
        where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and
        bbt.deleted_flag = ? and bbt.available_flag = ? and bbt.import_flag = ? and bbt.module_type in (?) and
         bbt.data_local_flag = ? and bbt.backup_mode = 1 and bbt.task_type = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], xphp_get_config('module', 'MODULE_TYPE')['M365'], $flag['SET'], xphp_get_config('task', 'TASKTYPE')['BACKUP']);
        //        if (!empty($nodeuuid)) {
//            $sql .= ' and bsr.node_uuid = ? ';
//            $sqlParams = array_merge($sqlParams, array($nodeuuid));
//        }
        $userInfo = xphp_get_user_info();
        // 非租户用户不能查看租户的资源
        if (empty($userInfo['tenantuuid'])) {
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . xphp_get_user_info()['userUuid'] . "'";
        }
        $sql .= ' order by bbt.timepoint';
        $data = $this->dbSelect($sql, $sqlParams);
        $searchNode = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID(14);
        foreach ($data as $d) {
            $realNode = (new Node())->getNodeUUIDWithTimepointUUID($d['timepoint_uuid']);
            $mbtdetail = json_decode($d['mbtdetail'], true);
            if (!empty($nodeuuid) && $realNode != $nodeuuid) {
                continue;
            }
            $organizationUuid = $mbtdetail['organization_uuid'];
            $organizationName = $mbtdetail['organization_name'];
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            if (strpos($d['task_name'], $search) !== false) {
                if (!in_array($d, $searchNode)) {
                    $searchNode[] = $d;
                }
            }
            if (strpos($mbtdetail['organization_name'], $search) !== false) {
                if (!in_array($d, $searchNode)) {
                    $searchNode[] = $d;
                }
            }
            $timeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[$d['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
            $name = $this->parseDate($d['timepoint']) . ' (' . $timeDes . ')';
            if (strpos($name, $search) !== false) {
                if (!in_array($d, $searchNode)) {
                    $searchNode[] = $d;
                }
            }
        }
        $node = array();
        //定义organization task 数组
        $organization = array();
        $task = array();
        foreach ($searchNode as $d) {
            $mbtdetail = json_decode($d['mbtdetail'], true);
            $organizationUuid = $mbtdetail['organization_uuid'];
            $organizationName = $mbtdetail['organization_name'];
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            $taskAvailable = in_array($taskuuid, $currentTaskUUID);
            $name = $taskAvailable ? $taskName : $taskName . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';
            //            if ($d['module_type'] == xphp_get_config('module')['MODULE_TYPE']['BACKUP_COPY_CLIENT']) {
//                continue;
//            }
            //检查并添加组织
            if (!in_array($organizationUuid, $organization)) {
                $organization[] = $organizationUuid;
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($tenantMangerFlag && $allManageFlag && $d['user_uuid'] != xphp_get_user_info()['userUuid']) {
                    $name .= '(' . $d['user_name'] . ')';
                }
                $node[] = array(
                    'id' => $organizationUuid,
                    'pId' => 0,
                    'name' => $organizationName,
                    'title' => $organizationName,
                    'open' => true,
                    'nocheck' => true,
                    'dir_type' => 1,
                    'iconSkin' => $this->getIconSkin(10000),
                    'node_uuid' => $realNode,
                    'job_uuid' => $taskuuid,
                    'isParent' => true,
                    'organization_uuid' => $organizationUuid,
                );
            }
            //检查并添加task
            if (!in_array($organizationUuid . '_' . $taskuuid, $task)) {
                $task[] = $organizationUuid . '_' . $taskuuid;
                $node[] = array(
                    'id' => $organizationUuid . '_' . $taskuuid,
                    'pId' => $organizationUuid,
                    'name' => $name,
                    'title' => $name,
                    'nocheck' => false,
                    'dir_type' => 2,
                    'clickshow' => true,
                    'iconSkin' => $this->getIconSkin(3),
                    'node_uuid' => $realNode,
                    'job_uuid' => $taskuuid,
                    'isParent' => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                );
            }
            $detail = json_decode($d['detail'], true);
            $timeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[$d['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
            $name = $this->parseDate($d['timepoint']) . ' (' . $timeDes . ')';
            $title = $name;
            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock" style="font-family: FontAwesome;"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }
            $mark = '';
            //永久标记
            $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            if (intval($d['backup_mode']) == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fullTimepoint = $d['timepoint_uuid'];
                $node[] = array(
                    'num' => $i++,
                    'id' => $d['timepoint_uuid'],
                    'pId' => $mbtdetail['organization_uuid'] . '_' . $taskuuid,
                    'name' => $name . $mark,
                    'title' => $title,
                    'checked' => false,
                    'dir_type' => 3,
                    'nocheck' => false,
                    'old_name' => $name,
                    'depend_uuid' => $d['depend_point_uuid'],
                    'organization_name' => $mbtdetail['organization_name'],
                    'organization_uuid' => $mbtdetail['organization_uuid'],
                    'job_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'iconSkin' => $this->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'job_uuid' => $taskuuid,
                    'node_uuid' => $realNode,
                    'storage_name' => $this->getStorageName($d['storage_uuid'], $realNode),
                    'time_point' => $this->parseDate($d['timepoint']),
                    'time_des' => $timeDes,
                    'encrypted_flag' => $encryptedflag,
                    'isParent' => false,
                    'type' => '10000', //选时间点相当于选择整个组织
                    'password_auto_flag' => intval($detail['password_auto_flag']) == 1 ? true : false,
                    'src_data_deleted_flag' => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    'total_size' => v1_calsize($d['total_size'], true),
                    'write_size' => v1_calsize($d['write_size'], true),
                    'remarks' => $d['remarks'],
                    'storage_type' => $d['storage_type'],
                );
            }
            //增量差异
            $incList = $this->getSearchIncTimepoint($d['timepoint_uuid']);
            if (!empty($incList)) {
                foreach ($incList as $d) {
                    if ($d['timepoint_uuid'] == $fullTimepoint) {
                        continue;
                    }
                    $detail = json_decode($d['detail'], true);
                    $timeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[$d['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
                    $name = $this->parseDate($d['timepoint']) . ' (' . $timeDes . ')';
                    $title = $name;
                    if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                        $name .= '<i class="fa fa-lock" style="font-family: FontAwesome;"></i>';
                        $encryptedflag = true;
                    } else {
                        $encryptedflag = false;
                    }
                    $node[] = array(
                        'num' => $i++,
                        'id' => $d['timepoint_uuid'],
                        'pId' => $mbtdetail['organization_uuid'] . '_' . $taskuuid,
                        'name' => $name . $mark,
                        'title' => $title,
                        'checked' => false,
                        'dir_type' => 3,
                        'nocheck' => false,
                        'old_name' => $name,
                        'depend_uuid' => $d['depend_point_uuid'],
                        'organization_name' => $mbtdetail['organization_name'],
                        'organization_uuid' => $mbtdetail['organization_uuid'],
                        'job_name' => $d['task_name'],
                        'star' => intval($d['importance_flag']) == $flag['SET'],
                        'iconSkin' => $this->getTimepointIcon($d['backup_mode']),
                        'mode' => intval($d['backup_mode']),
                        'timepoint_uuid' => $d['timepoint_uuid'],
                        'job_uuid' => $taskuuid,
                        'node_uuid' => $realNode,
                        'storage_name' => $this->getStorageName($d['storage_uuid'], $realNode),
                        'time_point' => $this->parseDate($d['timepoint']),
                        'time_des' => $timeDes,
                        'encrypted_flag' => $encryptedflag,
                        'isParent' => false,
                        'type' => '10000', //选时间点相当于选择整个组织
                        'password_auto_flag' => intval($detail['password_auto_flag']) == 1 ? true : false,
                        'src_data_deleted_flag' => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                        'total_size' => v1_calsize($d['total_size'], true),
                        'write_size' => v1_calsize($d['write_size'], true),
                        'remarks' => $d['remarks'],
                        'storage_type' => $d['storage_type'],
                        'chkDisabled' => true,
                    );
                }
            }
        }
        return $node;
    }

    /**
     * 搜索时获取增量点
     */
    public function getSearchIncTimepoint($depend_point_uuid)
    {
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   bbt.total_size, bbt.write_size, mbt.organization_info as mbtdetail,
                   bsr.node_uuid,bsr.storage_type
            FROM bd_backup_timepoint bbt
            JOIN m365_backup_timepoint mbt ON bbt.timepoint_uuid = mbt.m365_timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            WHERE bbt.timepoint_uuid = ? 
                  AND bbt.available_flag = 1 
                  AND bbt.import_flag = 2
                  AND bbt.module_type in (14)
            UNION ALL
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   bbt.total_size, bbt.write_size, mbt.organization_info as mbtdetail,
                   bsr.node_uuid,bsr.storage_type
            FROM bd_backup_timepoint bbt
            JOIN m365_backup_timepoint mbt ON bbt.timepoint_uuid = mbt.m365_timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            JOIN cte ON bbt.depend_point_uuid = cte.timepoint_uuid
            WHERE bbt.available_flag = 1 
                  AND bbt.import_flag = 2
                  AND bbt.module_type in (14)
        )
        SELECT * FROM cte ORDER BY timepoint;
        ";
        $result = $this->dbSelect($sql, array($depend_point_uuid));
        if (empty($result)) {
            return array();
        }
        return $result;
    }

    /**
     * 根据备份模式得到时间点图标
     * @param int $backupMode mode
     * @return string
     */
    public function getTimepointIcon($backupMode): string
    {
        $backupMode = intval($backupMode);
        $backupModeArr = xphp_get_config('task', 'BACKUP_MODE');
        $icon = 'iconSkin-exch-full';
        switch ($backupMode) {
            case $backupModeArr['FULL']:
                $icon = 'iconSkin-exch-full';
                break;
            case $backupModeArr['INCREMENTAL']:
                $icon = 'iconSkin-exch-inc';
                break;
            case $backupModeArr['DIFFERENTIAL']:
                $icon = 'iconSkin-exch-diff';
                break;
        }
        return $icon;
    }

    /**
     * 得到顶级目录
     * @param $params 参数
     * @return object 顶级目录
     */
    public function getRootDir($params)
    {
        $data = $this->service()->getRootDir($params);
        if ($data['result']) {
            if (is_array($data['msg']['root_folder_list'])) {
                $count = count($data['msg']['root_folder_list']);
            } else {
                $count = 0; // 如果不是一个数组，则总数为0
            }
            $info = array(
                'rows' => array(),
                'total' => $count,
            );
            foreach ($data['msg']['root_folder_list'] as $each) {
                $info['rows'][] = array(
                    'name' => $each['display_name'],
                    'uuid' => $each['folder_id'],
                    'id' => $each['folder_id'],
                    'item_id' => $each['folder_id'],
                    'pId' => $data['msg']['user_uuid'],
                    'title' => $each['display_name'],
                    'organization_uuid' => $data['msg']['organization_uuid'],
                    'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                    'isParent' => true,
                    'user_uuid' => $data['msg']['user_uuid'],
                    'table_id' => $data['msg']['table_id'],
                    'index_container_id' => $data['msg']['index_container_id'],
                    'dir_type' => 5,
                    'type' => 100,
                    'meta_type' => $each['type'],
                    'size' => $each['size'],
                    'iconSkin' => $this->getIconSkin($each['type']),
                    'user' => '--',
                    'level' => '--',
                    'send' => '--',
                    'receive' => '--',
                    'subject' => '--',
                    'recv_date' => '--',
                    'nocheck' => true,
                    'node_uuid' => $params['node_uuid'],
                    'storage_type' => $params['storage_type'],
                );
            }
        }
        return $info;
    }

    /**
     * 得到子目录
     * @param $params 参数
     * @return object 子目录
     */
    public function getChildDir($params)
    {
        $info = array(
            'rows' => array(),
            'dir' => array(),
        );
        $data = $this->service()->getChildDir($params);
        if ($data['result']) {
            if (empty($data['msg']['child_folder_list']) && empty($params['table_flag'])) {
                //请求的是目录树类型数据  目录为空
                $info['dir'] = [];
                //获取元数据  把元数据数组拼接到子目录数组中
                $info['rows'] = array_merge($info['rows'], $this->getMetadata($params));
                return $info;
            }
            foreach ($data['msg']['child_folder_list'] as $each) {
                //表格的数据
                $info['rows'][] = array(
                    'id' => $each['folder_id'],
                    'name' => $each['display_name'],
                    'organization_uuid' => $data['msg']['organization_uuid'],
                    'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                    'dir_type' => 6,
                    'uuid' => $each['folder_id'],
                    'item_id' => $each['folder_id'],
                    'size' => $data['msg']['size'],
                    'table_id' => $data['msg']['table_id'],
                    'user_uuid' => $data['msg']['user_uuid'],
                    'index_container_id' => $data['msg']['index_container_id'],
                    'type' => 100, //目录类型：子目录
                    'meta_type' => $each['type'],
                    'isParent' => true,
                    'nocheck' => true,
                    'send' => '--',
                    'receive' => '--',
                    'cc' => '--',
                    'subject' => '--',
                    'recv_date' => '--',
                    'operate' => '--',
                    'node_uuid' => $params['node_uuid'],
                    'storage_type' => $params['storage_type'],
                );
                //树的数据
                $info['dir'][] = array(
                    'id' => $each['folder_id'],
                    'name' => $each['display_name'],
                    'title' => $each['display_name'],
                    'organization_uuid' => $data['msg']['organization_uuid'],
                    'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                    'dir_type' => 6,
                    'uuid' => $each['folder_id'],
                    'size' => $data['msg']['size'],
                    'table_id' => $data['msg']['table_id'],
                    'user_uuid' => $data['msg']['user_uuid'],
                    'index_container_id' => $data['msg']['index_container_id'],
                    'type' => $each['type'], //目录类型：子目录
                    'meta_type' => $each['type'],
                    'isParent' => true,
                    'nocheck' => true,
                    'send' => '--',
                    'receive' => '--',
                    'cc' => '--',
                    'subject' => '--',
                    'recv_date' => '--',
                    'operate' => '--',
                    'node_uuid' => $params['node_uuid'],
                    'storage_type' => $params['storage_type'],
                );
            }
        }
        //获取元数据  把元数据数组拼接到子目录数组中
        $info['rows'] = array_merge($info['rows'], $this->getMetadata($params));
        return $info;
    }

    /**
     * 得到目录下的元数据
     * @param $params 参数
     * @return object 目录下的元数据
     */
    public function getMetadata($params)
    {
        $data = $this->service()->getMetadata($params);
        if ($data['result'] && !empty($data['msg']['item_list'])) {
            foreach ($data['msg']['item_list'] as $each) {
                //不同类型返回不同字段
                switch (intval($params['type'])) {
                    case 0: //邮件类型目录
                        $result[] = array(
                            'name' => xphp_get_lang('WEB_M365_EMAIL'),
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'organization_uuid' => $data['msg']['organization_uuid'],
                            'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                            'user_uuid' => $data['msg']['user_uuid'],
                            'table_id' => $data['msg']['table_id'],
                            'parent_folder_id' => $data['msg']['parent_folder_id'],
                            'next_start' => $data['msg']['next_start'],
                            'index_container_id' => $data['msg']['index_container_id'],
                            'dir_type' => 7,
                            'type' => $each['type'], //目录类型：0:邮件类型目录 1：日历类型目录 2：联系人类型目录 3：任务类型目录
                            'send' => $each['mail_sender'],
                            'receive' => json_decode($each['mail_recipents']),
                            'cc' => json_decode($each['mail_cc']),
                            'subject' => $each['subject'],
                            'recv_date' => $each['recv_date'],
                            'size' => $each['size'],
                            'total_count' => $data['msg']['total_count'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'more' => false,
                            'node_uuid' => $params['node_uuid'],
                            'storage_type' => $params['storage_type'],
                        );
                        break;
                    case 1: //日历类型目录
                        $result[] = array(
                            'name' => xphp_get_lang('WEB_M365_CALENDAR'),
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'organization_uuid' => $data['msg']['organization_uuid'],
                            'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                            'user_uuid' => $data['msg']['user_uuid'],
                            'table_id' => $data['msg']['table_id'],
                            'parent_folder_id' => $data['msg']['parent_folder_id'],
                            'next_start' => $data['msg']['next_start'],
                            'index_container_id' => $data['msg']['index_container_id'],
                            'dir_type' => 7,
                            'type' => $each['type'], //目录类型：0:邮件类型目录 1：日历类型目录 2：联系人类型目录 3：任务类型目录
                            'is_attachment' => $each['is_attachment'],
                            'location_display' => $each['location_display'],
                            'organizer_name' => $each['organizer_name'],
                            'size' => $each['size'],
                            'start_time' => $each['start_time'],
                            'state' => $each['state'],
                            'stop_time' => $each['stop_time'],
                            'subject' => $each['subject'],
                            'total_count' => $data['msg']['total_count'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'more' => false,
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'node_uuid' => $params['node_uuid'],
                            'storage_type' => $params['storage_type'],
                        );
                        break;
                    case 2: //联系人类型目录
                        $result[] = array(
                            'name' => xphp_get_lang('WEB_M365_CONTACTS'),
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'organization_uuid' => $data['msg']['organization_uuid'],
                            'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                            'user_uuid' => $data['msg']['user_uuid'],
                            'table_id' => $data['msg']['table_id'],
                            'parent_folder_id' => $data['msg']['parent_folder_id'],
                            'next_start' => $data['msg']['next_start'],
                            'index_container_id' => $data['msg']['index_container_id'],
                            'dir_type' => 7,
                            'type' => $each['type'], //目录类型：0:邮件类型目录 1：日历类型目录 2：联系人类型目录 3：任务类型目录
                            'business_phone' => $each['business_phone'],
                            'company' => $each['company'],
                            'department' => $each['department'],
                            'display_name' => $each['display_name'],
                            'email_addresses' => json_decode($each['email_addresses'], true),
                            'is_attachment' => $each['is_attachment'],
                            'size' => $each['size'],
                            'total_count' => $data['msg']['total_count'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'more' => false,
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'subject' => '--',
                            'node_uuid' => $params['node_uuid'],
                            'storage_type' => $params['storage_type'],
                        );
                        break;
                    case 3: //任务类型目录
                        $result[] = array(
                            'name' => xphp_get_lang('WEB_M365_TASK'),
                            'item_id' => $each['item_id'],
                            'id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'organization_uuid' => $data['msg']['organization_uuid'],
                            'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                            'user_uuid' => $data['msg']['user_uuid'],
                            'table_id' => $data['msg']['table_id'],
                            'parent_folder_id' => $data['msg']['parent_folder_id'],
                            'next_start' => $data['msg']['next_start'],
                            'index_container_id' => $data['msg']['index_container_id'],
                            'dir_type' => 7,
                            'type' => $each['type'], //目录类型：0:邮件类型目录 1：日历类型目录 2：联系人类型目录 3：任务类型目录
                            'due_date' => $each['due_date'],
                            'importance' => $each['importance'],
                            'is_attachment' => $each['is_attachment'],
                            'owner_name' => $each['owner_name'],
                            'size' => $each['size'],
                            'start_date' => $each['start_date'],
                            'status' => $each['status'],
                            'subject' => $each['subject'],
                            'total_count' => $data['msg']['total_count'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'more' => false,
                            'contact_name' => '--',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'node_uuid' => $params['node_uuid'],
                            'storage_type' => $params['storage_type'],
                        );
                        break;
                }
            }
            if ($data['msg']['finish_flag'] == 2) { //2:元数据未加载完成   1:元数据加载完成
                $result[] = array(
                    'name' => xphp_get_lang('WEB_M365_LOAD_MORE'),
                    'organization_uuid' => $data['msg']['organization_uuid'],
                    'timepoint_uuid' => $data['msg']['timepoint_uuid'],
                    'user_uuid' => $data['msg']['user_uuid'],
                    'table_id' => $data['msg']['table_id'],
                    'parent_folder_id' => $data['msg']['parent_folder_id'],
                    'next_start' => $data['msg']['next_start'],
                    'index_container_id' => $data['msg']['index_container_id'],
                    'dir_type' => 7,
                    'total_count' => $data['msg']['total_count'],
                    'type' => $params['type'], //目录类型：0:邮件类型目录 1：日历类型目录 2：联系人类型目录 3：任务类型目录
                    'more' => true,
                    'storage_type' => $params['storage_type'],
                );
            }
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * 得到目录下的元数据详细信息
     * @param $params 参数
     * @return array $info  目录下的元数据详细信息
     */
    public function getMetadataDetail($params)
    {
        $info = array();
        $result = $this->service()->getMetadataDetail($params);
        if ($result['result']) {
            $detail = $result['msg']['item_detail'];
            switch (intval($detail['item_type'])) {
                case 0:
                    //邮件正文的html转码
                    if ($detail['encode'] == 'base64') {
                        $tmpdecodestring = base64_decode($detail['mail_body_html']);
                        if (false == $tmpdecodestring) {
                            $string = $detail['mail_body_html'];
                        } else {
                            $string = $tmpdecodestring;
                        }
                    } else {
                        //处理其他编码
                        try {
                            $tmpdecodestring = quoted_printable_decode($detail['mail_body_html']);
                            $tmpiconvstring = iconv($detail['charset'], 'utf8', $tmpdecodestring);
                            $string = $tmpiconvstring;
                        } catch (\Exception $exception) {
                            $string = "";
                        }
                    }
                    $info[] = array(
                        'is_attachment' => $detail['is_attachment'],
                        'attachments' => json_decode($detail['attachments']),
                        'item_id' => $detail['item_id'],
                        'item_uuid' => $detail['item_uuid'],
                        'item_type' => $detail['item_type'],
                        'content' => $string,
                        'mail_cc' => json_decode($detail['mail_cc']),
                        'mail_recipents' => json_decode($detail['mail_recipents'], true),
                        'mail_sender' => $detail['mail_sender'],
                        'recv_date' => $detail['recv_date'],
                        'size' => $detail['size'],
                        'subject' => $detail['subject'],
                        'folder_id' => $detail['folder_id'],
                        'index_container_id' => $detail['index_container_id'],
                        'table_id' => $detail['table_id'],
                        'node_uuid' => $params['node_uuid'],
                        'storage_type' => $params['storage_type'],
                    );
                    break;
                case 1: //日历
                    $info[] = array(
                        'attachments' => json_decode($detail['attachments']),
                        'event_body' => $detail['event_body'],
                        'folder_id' => $detail['folder_id'],
                        'index_container_id' => $detail['index_container_id'],
                        'is_attachment' => $detail['is_attachment'],
                        'item_id' => $detail['item_id'],
                        'item_uuid' => $detail['item_uuid'],
                        'item_type' => $detail['item_type'],
                        'location_dispaly' => $detail['location_dispaly'],
                        'size' => $detail['size'],
                        'start_time' => $detail['start_time'],
                        'stop_time' => $detail['stop_time'],
                        'subject' => $detail['subject'],
                        'table_id' => $detail['table_id'],
                        'node_uuid' => $params['node_uuid'],
                        'storage_type' => $params['storage_type'],
                    );
                    break;
                case 2: //联系人
                    $info[] = array(
                        'business_fax' => $detail['business_fax'],
                        'business_phone' => $detail['business_phone'],
                        'company' => $detail['company'],
                        'country' => $detail['country'],
                        'county' => $detail['county'],
                        'department' => $detail['department'],
                        'display_as' => $detail['display_as'],
                        'job_title' => $detail['job_title'],
                        'display_name' => $detail['display_name'],
                        'email_addresses' => json_decode($detail['email_addresses'], true),
                        'file_as' => $detail['file_as'],
                        'folder_id' => $detail['folder_id'],
                        'home_phone' => $detail['home_phone'],
                        'index_container_id' => $detail['index_container_id'],
                        'item_id' => $detail['item_id'],
                        'item_uuid' => $detail['item_uuid'],
                        'item_type' => $detail['item_type'],
                        'mobile' => $detail['mobile'],
                        'postal_code' => $detail['postal_code'],
                        'province_and_city' => $detail['province_and_city'],
                        'remarks' => $detail['remarks'],
                        'size' => $detail['size'],
                        'street' => $detail['street'],
                        'table_id' => $detail['table_id'],
                        'web_page_and_im_addess' => $detail['web_page_and_im_addess'],
                        'node_uuid' => $params['node_uuid'],
                        'storage_type' => $params['storage_type'],
                    );
                    break;
                case 3: //任务
                    $info[] = array(
                        'attachments' => json_decode($detail['attachments']),
                        'display_name' => $detail['display_name'],
                        'due_date' => $detail['due_date'],
                        'folder_id' => $detail['folder_id'],
                        'importance' => $detail['importance'],
                        'index_container_id' => $detail['index_container_id'],
                        'is_attachment' => $detail['is_attachment'],
                        'item_id' => $detail['item_id'],
                        'item_uuid' => $detail['item_uuid'],
                        'item_type' => $detail['item_type'],
                        'owner' => $detail['owner'],
                        'remarks' => $detail['remarks'],
                        'size' => $detail['size'],
                        'start_date' => $detail['start_date'],
                        'status' => $detail['status'],
                        'table_id' => $detail['table_id'],
                        'node_uuid' => $params['node_uuid'],
                        'storage_type' => $params['storage_type'],
                    );
                    break;
            }
        } else {
            return $this->muOpResult(false,'EXCH_BACKUP_POINT_OP_CODE_GET_ITEM_DETAIL',  xphp_get_lang('WEB_M365_SERVER_GET_INFO_ERROR'), '', $result['errorCode']);
        }
        //获取系统通知默认配置--获取发件人信息
        $emailInfo = (new Notice())->getEmailConf();
        $info['system_send'] = $emailInfo['user'];
        return $info;
    }

    /**
     * 获取时间点下的用户列表/获取用户下的详情列表
     * @param $params 参数
     * @return object 用户列表/用户下的详情列表
     */
    public function getRestoreUsers($params)
    {
        $result = $this->service()->getRestoreUsers($params);
        if ($result['result']) {
            $msg = $result['msg']['user_list']; //所有用户用户组
            if (is_array($msg)) {
                $count = count($msg);
            } else {
                $count = 0; // 如果不是一个数组，则总数为0
            }
            $info = array(
                'rows' => array(),
                'total' => $count
            );
            foreach ($msg as $each) {
                $info['rows'][] = array(
                    'name' => $each['display_name'] . '(' . $each['mail'] . ')',
                    'uuid' => $each['user_uuid'],
                    'id' => $each['user_uuid'],
                    'pId' => $result['msg']['organization_uuid'],
                    'title' => $each['display_name'] . '(' . $each['mail'] . ')',
                    'organization_uuid' => $result['msg']['organization_uuid'],
                    'iconSkin' => $this->getIconSkin($each['type']),
                    'timepoint_uuid' => $result['msg']['timepoint_uuid'],
                    'dir_type' => 4,
                    'isParent' => true,
                    'display_name' => $each['display_name'],
                    'email' => $each['mail'],
                    'type' => $each['type'],
                    'index_container_id' => $each['index_container_id'],
                    'table_id' => $each['table_id'],
                    'nocheck' => true,
                    'user_uuid' => $each['user_uuid'],
                    'node_uuid' => $params['node_uuid'],
                    'storage_type' => $params['storage_type'],
                );
            }
            //恢复用户加载更多，表格不显示
            if ($result['msg']['finish_flag'] == 2 && !$params['table_flag']) {
                $info['rows'][] = array(
                    'name' => xphp_get_lang('WEB_FILE_MORE'),
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'id' => $each['user_uuid'] . '_more',
                    'pId' => $result['msg']['timepoint_uuid'],
                    'organization_uuid' => $params['organization_uuid'],
                    'timepoint_uuid' => $result['msg']['timepoint_uuid'],
                    'more' => true,
                    'next_start' => $result['msg']['next_start'], //下一次开始位置
                    'current_num' => $result['msg']['current_num'],
                    'nocheck' => true,
                    'node_uuid' => $params['node_uuid'],
                    'dir_type' => 'more',
                    'storage_type' => $params['storage_type'],
                );
            }
        } else {
            return [];
        }
        return $info;
    }

    //    /**
//     * 得到当前所有任务的UUID
//     * @return array 所有taskuuid
//     */
//    public function getCurrentAllTaskUUID(): array
//    {
//        $taskuuid = array();
//        $sql = "select task_uuid from bd_task where module_type in(2, 3, 4, 9, 11, 14) and delete_flag = ?";
//        $sqlParams = array(xphp_get_config('FLAG')['UNSET']);
//        $data = $this->dbSelect($sql, $sqlParams);
//        foreach ($data as $d) {
//            $taskuuid[] = $d['task_uuid'];
//        }
//        return $taskuuid;
//    }

    /**
     * 得到标记图标,即WMYF的标记  表示GFS的W周,M月,Y年,F星标
     * 传入参数都为布尔值
     * @param boolean $wflag 保留周标记
     * @param boolean $mflag 保留月标记
     * @param boolean $yflag 保留年标记
     * @param boolean $fflag 永久保留标记
     * @return string $markStr 标记图标
     */
    public function pGetTimepointMark($wflag, $mflag, $yflag, $fflag)
    {
        $markStr = '';
        if ($wflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_WEEK_POINT') . '" class="button ico_docu data_week_mark"></i>';
        }
        if ($mflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_MONTH_POINT') . '" class="button ico_docu data_month_mark"></i>';
        }
        if ($yflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_YEAR_POINT') . '" class="button ico_docu data_year_mark"></i>';
        }
        if ($fflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_FOREVER_POINT') . '" class="viconfont vicon-remark-forever"></i>';
        }
        return $markStr;
    }

    //    /**
//     * 根据备份模式得到时间点图标
//     * @param int $backupMode 1完备 2增量  3差备
//     * @return string 时间点图标
//     */
//    public function getTimepointIcon($backupMode)
//    {
//        $backupMode = intval($backupMode);
//        $icon = './img/exchange/point.png';
//        switch ($backupMode) {
//            case xphp_get_config('task', 'BACKUP_MODE')['FULL']:
//                $icon = './img/exchange/point.png';
//                break;
//            case xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL']:
//                $icon = './img/exchange/increment.png';
//                break;
//            case xphp_get_config('task', 'BACKUP_MODE')['DIFFERENTIAL']:
//                $icon = './img/exchange/different.png';
//                break;
//        }
//        return $icon;
//    }

    /**
     * 获取存储名字
     * @param string $storageuuid 存储uuid
     * @param string $nodeuuid    nodeuuid
     * @return string 存储名称
     */
    public function getStorageName($storageuuid, $nodeuuid)
    {
        $sql = "select storage_nickname, node_uuid, storage_config, storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = $data[0]['storage_nickname'];
        $type = intval($data[0]['storage_type']);
        if ($type == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
            $storageConfig = json_decode($data[0]['storage_config'], true);
            $nodename = $storageConfig['remote_ip'];
        } else {
            $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ? ";
            $dataNode = $this->dbSelect($sql, array($nodeuuid));
            if (empty($dataNode[0]['ip']) && empty($dataNode[0]['node_nickname']) && empty($dataNode[0]['host_name'])) {
                return '--';
            }
            //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
            if ($dataNode[0]['ip'] == $dataNode[0]['node_nickname'] || empty($dataNode[0]['node_nickname'])) {
                $nodename = $dataNode[0]['host_name'] . '(' . $dataNode[0]['ip'] . ')';
            } else {
                $nodename = $dataNode[0]['node_nickname'] . '(' . $dataNode[0]['ip'] . ')';
            }
        }
        $name .= "\n" . '(' . $nodename . ')';
        return $name;
    }

    /**
     * 创建恢复任务
     * @param unknown $params 创建任务参数
     * @return string 恢复结果
     */
    public function createRecoverJob($params)
    {
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $taskname = htmlspecialchars_decode($params['job_name']);
        $moduletypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        $moduletype = $moduletypeArr['M365'];
        $recoveryposition = intval($params['recovery_position']);
        $recoverytimetype = 0;
        $timestrategylist = $this->groupRecoverTimeList($params['type_info']);
        $transportstrategy = (new Backup())->groupTransportStrategy($params['type_info']['high']['transfer']);
        $pfMSg = (new \xphp\BLLHandler())->pfCreateRecoveryTaskMessage(
            $taskname,
            $moduletype,
            $recoveryposition,
            $recoverytimetype,
            $timestrategylist,
            $transportstrategy
        );

        $pfMSg['task_type'] = $tasktypeArr['RECOVERY'];
        //private params
        //disk or file
        $pfMSg['recovery_level'] = 1;
        $pfMSg['destination_agent_uuid'] = $params['recover_info']['agent_uuid'];
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedLimit']);
        $pfMSg['password'] = $params['recover_info']['password'];
        $pfMSg['thread_num'] = $params['thread_num'];
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['recovery_timepoint_uuid']);
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$params['recovery_timepoint_uuid']]);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }
 
        $pfMSg['destination_user_uuid'] = $params['destination_user_uuid'];
        $pfMSg['destination_mail'] = $params['destination_mail'];
        $pfMSg['timepoint_password'] = '';
        $pfMSg['overwrite'] = $params['overwrite'];
        $pfMSg['recovery_timepoint_uuid'] = $params['recovery_timepoint_uuid'];
        $pfMSg['organization_uuid'] = $params['organization_uuid'];
        $pfMSg['destination_organization_uuid'] = $params['destination_organization_uuid'];
        $pfMSg['destination_user_uuid'] = $params['destination_user_uuid'];
        $pfMSg['destination_user_type'] = $params['destination_user_type'];
        $pfMSg['exch_recovery_object_info_list'] = $params['exch_recovery_object_info_list'];
        $pfMSg['retry_strategy'] = (new Backup())->groupRetryStrategy($params['retry_strategy']);
        $pfMSg['agent_uuid'] = $params['high_conf']['agent_uuid'];
        // 忽略节点资源限制
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_conf']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMSg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy']);
        $mbResult = $this->service()->createRecoverJob($nodeuuid, $pfMSg, $opName);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            //如果是立即恢复,需要创建完成后启动任务
            if($params['type_info']['type'] == xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY']) {
                $this->startRecoverJob($taskname);
            }
            //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName 任务名
     * @return boolean
     */
    private function startRecoverJob(string $taskName)
    {
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql, array($taskName, xphp_get_config('module', 'MODULE_TYPE')['M365'], xphp_get_config('task', 'TASKTYPE')['RECOVERY']));
        if (!$data) {
            return false;
        }
        //调用系统统一启动任务接口.不重新写
        (new \app\v1\exchange\v0\logic\ExchangeJobController())->startJob($data[0]['task_uuid'], xphp_get_config('task', 'BACKUP_MODE')['FULL']);
    }

    /**
     * 获取恢复任务名
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getRestoreTaskName($params = [])
    {
        return (new ExchangeBackUp())->getValidTaskName(xphp_get_lang('WEB_M365_RECOVERY_NAME'));
    }

    /**
     * 高级搜索
     * @param $params 参数
     * @return object 目录下的元数据详细信息
     */
    public function getSearchInfo($params)
    {
        $info = array(
            'rows' => array(),
            'total' => 0,
        );
        $result = $this->service()->getSearchInfo($params);
        if ($result['result']) {
            if (is_array($result['msg']['item_list'])) {
                $count = count($result['msg']['item_list']);
            } else {
                $info['total'] = 0; // 如果不是一个数组，则总数为0
                $info['rows'][] = array(
                    'more' => false,
                    'finish_flag' => 3,//自己定义一个3用于解决恰好20条搜索结果时，返回空数组的情况
                );
                return $info;
            }
            $info['total'] = $count;
            $msg = $result['msg']['item_list']; //所有用户用户组
            foreach ($msg as $each) {
                switch (intval($each['item_type'])) {
                    case 0:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_EMAIL'),
                            'contact_name' => '--',
                            'type' => intval($each['item_type']),
                            'index_container_id' => $each['index_container_id'],
                            'is_attachment' => $each['is_attachment'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'mail_cc' => json_decode($each['mail_cc']),
                            'receive' => json_decode($each['mail_recipents']),
                            'send' => $each['mail_sender'],
                            'recv_date' => $each['recv_date'],
                            'size' => $each['size'],
                            'subject' => $each['subject'],
                            'table_id' => $each['table_id'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'dir_type' => 7,
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                    case 1:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_CALENDAR'),
                            'type' => intval($each['item_type']),
                            'index_container_id' => $each['index_container_id'],
                            'is_attachment' => $each['is_attachment'],
                            'item_id' => $each['item_id'],
                            'contact_name' => '--',
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'location_dispaly' => $each['location_dispaly'], //地区
                            'organizer_name' => $each['organizer_name'],
                            'size' => $each['size'],
                            'start_time' => $each['start_time'],
                            'stop_time' => $each['stop_time'],
                            'subject' => $each['subject'],
                            'table_id' => $each['table_id'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'dir_type' => 7,
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                    case 2:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_CONTACTS'),
                            'type' => intval($each['item_type']),
                            'business' => $each['business'],
                            'company' => $each['company'],
                            'department' => $each['department'],
                            'display_name' => $each['display_name'],
                            'contact_name' => $each['display_name'],
                            'email_addresses' => $each['email_addresses'],
                            'index_container_id' => $each['index_container_id'],
                            'is_attachment' => $each['is_attachment'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'size' => $each['size'],
                            'table_id' => $each['table_id'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'dir_type' => 7,
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'subject' => '--',
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                    case 3:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_TASK'),
                            'type' => intval($each['item_type']),
                            'display_name' => $each['display_name'],
                            'contact_name' => '--',
                            'due_date' => $each['due_date'],
                            'importance' => $each['importance'],
                            'index_container_id' => $each['index_container_id'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'owner' => $each['owner'],
                            'size' => $each['size'],
                            'start_date' => $each['start_date'],
                            'status' => $each['status'],
                            'table_id' => $each['table_id'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'dir_type' => 7,
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'subject' => $each['display_name'],
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                }
            }
            if ($result['msg']['finish_flag'] == 2) { //还没加载完
                $info['rows'][] = array(
                    'name' => xphp_get_lang('WEB_M365_LOAD_MORE'),
                    'index_container_id' => $each['index_container_id'],
                    'is_attachment' => $each['is_attachment'],
                    'item_id' => $each['item_id'],
                    'item_uuid' => $each['item_uuid'],
                    'id' => $each['item_id'],
                    'item_type' => $each['item_type'],
                    'type' => intval($each['item_type']),
                    'table_id' => $each['table_id'],
                    'parent_folder_id' => $each['parent_folder_id'],
                    'dir_type' => 7,
                    'user_uuid' => $each['user_uuid'],
                    'last_search_info' => $result['msg']['last_search_info'],
                    'next_start' => $result['msg']['next_start'],
                    'organization_uuid' => $each['organization_uuid'],
                    'timepoint_uuid' => $each['timepoint_uuid'],
                    'more' => true,
                    'finish_flag' => $result['msg']['finish_flag'],
                );
            }
        } else {
            return [];
        }
        return $info;
    }

    /**
     * 普通全文搜索
     * @param $params 参数
     * @return object 普通全文搜索信息
     */
    public function getNormalSearch($params)
    {
        $info = array(
            'rows' => array(),
            'total' => 0,
        );
        $result = $this->service()->getNormalSearch($params);
        if ($result['result']) {
            if (is_array($result['msg']['item_list'])) {
                $info['total'] = count($result['msg']['item_list']);
                $msg = $result['msg']['item_list']; //所有用户用户组
            } else {
                $info['total'] = 0; // 如果不是一个数组，则总数为0
                $info['rows'][] = array(
                    'more' => false,
                    'finish_flag' => 3,//自己定义一个3用于解决恰好20条搜索结果时，返回空数组的情况
                );
                return $info;
            }
            foreach ($msg as $each) {
                switch (intval($each['item_type'])) {
                    case 0:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_EMAIL'),
                            'contact_name' => '--',
                            'dir_type' => 7,
                            'type' => intval($each['item_type']),
                            'index_container_id' => $each['index_container_id'],
                            'is_attachment' => $each['is_attachment'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'mail_cc' => json_decode($each['mail_cc']),
                            'receive' => json_decode($each['mail_recipents']),
                            'send' => $each['mail_sender'],
                            'recv_date' => $each['recv_date'],
                            'size' => $each['size'],
                            'subject' => $each['subject'],
                            'table_id' => $each['table_id'],
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'finish_flag' => $result['msg']['finish_flag'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                    case 1:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_CALENDAR'),
                            'contact_name' => '--',
                            'dir_type' => 7,
                            'type' => intval($each['item_type']),
                            'index_container_id' => $each['index_container_id'],
                            'is_attachment' => $each['is_attachment'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'location_dispaly' => $each['location_dispaly'], //地区
                            'organizer_name' => $each['organizer_name'],
                            'size' => $each['size'],
                            'start_time' => $each['start_time'],
                            'stop_time' => $each['stop_time'],
                            'subject' => $each['subject'],
                            'table_id' => $each['table_id'],
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                    case 2:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_CONTACTS'),
                            'contact_name' => $each['display_name'],
                            'dir_type' => 7,
                            'type' => intval($each['item_type']),
                            'business' => $each['business'],
                            'company' => $each['company'],
                            'department' => $each['department'],
                            'display_name' => $each['display_name'],
                            'email_addresses' => $each['email_addresses'],
                            'index_container_id' => $each['index_container_id'],
                            'is_attachment' => $each['is_attachment'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'size' => $each['size'],
                            'table_id' => $each['table_id'],
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'subject' => '--',
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                    case 3:
                        $info['rows'][] = array(
                            'name' => xphp_get_lang('WEB_M365_TASK'),
                            'contact_name' => '--',
                            'dir_type' => 7,
                            'type' => intval($each['item_type']),
                            'display_name' => $each['display_name'],
                            'due_date' => $each['due_date'],
                            'importance' => $each['importance'],
                            'index_container_id' => $each['index_container_id'],
                            'item_id' => $each['item_id'],
                            'item_uuid' => $each['item_uuid'],
                            'id' => $each['item_id'],
                            'item_type' => $each['item_type'],
                            'owner' => $each['owner'],
                            'size' => $each['size'],
                            'start_date' => $each['start_date'],
                            'status' => $each['status'],
                            'table_id' => $each['table_id'],
                            'user_uuid' => $each['user_uuid'],
                            'last_search_info' => $result['msg']['last_search_info'],
                            'next_start' => $result['msg']['next_start'],
                            'organization_uuid' => $params['organization_uuid'],
                            'timepoint_uuid' => $params['timepoint_uuid'],
                            'parent_folder_id' => $each['parent_folder_id'],
                            'operate' => '<span class="label label-sm label-success status-icon detail">' . xphp_get_lang('UI_PUBLIC_DETAIL') . '</span>',
                            'receive' => '--',
                            'send' => '--',
                            'recv_date' => '--',
                            'subject' => $each['display_name'],
                            'finish_flag' => $result['msg']['finish_flag'],
                            'node_uuid' => $params['node_uuid'],
                        );
                        break;
                }
            }
            if ($result['msg']['finish_flag'] == 2) { //还没加载完
                $info['rows'][] = array(
                    'name' => xphp_get_lang('WEB_M365_LOAD_MORE'),
                    'index_container_id' => $each['index_container_id'],
                    'is_attachment' => $each['is_attachment'],
                    'item_id' => $each['item_id'],
                    'item_uuid' => $each['item_uuid'],
                    'id' => $each['item_id'],
                    'item_type' => $each['item_type'],
                    'dir_type' => 7,
                    'more' => true,
                    'table_id' => $each['table_id'],
                    'user_uuid' => $each['user_uuid'],
                    'last_search_info' => $result['msg']['last_search_info'],
                    'next_start' => $result['msg']['next_start'],
                    'organization_uuid' => $each['organization_uuid'],
                    'timepoint_uuid' => $each['timepoint_uuid'],
                    'finish_flag' => $result['msg']['finish_flag'],
                );
            }
        } else {
            return [];
        }
        return $info;
    }

    /**
     * 根据类型获取图标
     * @param $type 类型
     * @return string 类型中文描述
     */
    public function getName($type)
    {
        $typedes = '';
        switch (intval($type)) {
            case 0:
                $typedes = '<i class="viconfont vicon-youjian1 c3E767F mr8"></i>';
                break;
            case 1:
                $typedes = '<i class="viconfont vicon-rili1 c3E767F mr8"></i>';
                break;
            case 2:
                $typedes = '<i class="viconfont vicon-lianxiren1 c3E767F mr8"></i>';
                break;
            case 3:
                $typedes = '<i class="viconfont vicon-renwu c3E767F mr8"></i>';
                break;
        }
        return $typedes;
    }

    /**
     * 根据类型获取iconskin
     * @param $type 类型
     * @return string 类型中文描述
     */
    public function getIconSkin($type)
    {
        $classDes = '';
        switch (intval($type)) {
            case 0:
                $classDes = 'iconSkin-exch-email';
                break;
            case 1:
                $classDes = 'iconSkin-exch-calendar';
                break;
            case 2:
                $classDes = 'iconSkin-exch-contact';
                break;
            case 3:
                $classDes = 'iconSkin-exch-task';
                break;
            case 1000: //用户
                $classDes = 'iconSkin-exch-user';
                break;
            case 1001: //用户组
                $classDes = 'iconSkin-exch-group';
                break;
            case 6: //目录
                $classDes = 'iconSkin-exch-dir';
                break;
            case 10000: //组织
                $classDes = 'iconSkin-exch-organization';
                break;
        }
        return $classDes;
    }

    /**
     * 发送邮件（单个/批量发送）
     * @return object 发送邮件的结果
     */
    public function sendRestoreEmail($params)
    {
        $data = $this->getMetadataDetail($params['detail']);
        //处理html
        $content = $this->formatHtml($data[0]['content'],$data[0]['node_uuid']);
        $msg = array(
            'title' => $data[0]['subject'],
            'email' => $params['info']['email'],
            'attachment' => $data[0]['attachments'],
            'cc' => $params['info']['cc'],
            'info' => $content,
        );
        $result = Notice::instance()->sendEmail($msg);
        return $result;
    }

    /**
     * 导出压缩包
     * @return object 发送邮件的结果
     */
    public function getRestoreZip($params)
    {
        $result = $this->service()->getRestoreZip($params);
        return $result;
    }

    /**
     * 导出压缩包
     * @return object 获取压缩包数据
     */
    public function getRestoreZipData($params)
    {
        $filename = $params['export_file_name'];
        $filesize = $params['export_file_size'];
        $blockSize = '8388608'; //分块大小，8M
        //如果大于分块大小,分块下载
        $mbResult = '';
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                'process_uuid' => '',
                'node_uuid' => $params['node_uuid'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'export_file_name' => $filename,
                'export_file_size' => $filesize,
                'offset' => $i,
                'length' => $readLen,
            );
            Header('Content-type: application/octet-stream');
            Header('Accept-Ranges: bytes');
            Header('Accept-Length: ' . $filesize);
            Header('Content-Disposition: attachment; filename=' . basename($filename));
            $mbResult = $this->service()->getRestoreZipData($msg);
            echo $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }

        //return  $mbResult;
    }

    /**
     * 恢复时间点身份验证
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getAuthResult($params = [])
    {
        $info = array(
            'state' => true,
        );
        $sql = "select organization_info from m365_backup_timepoint where m365_timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($params['timepoint_uuid']));
        if (!empty($data)) {
            $organizationInfo = json_decode($data[0]['organization_info'], true);
            if ($organizationInfo['region'] == 100) {//server
                //加密的密码和不加密的都判断下，兼容旧版本
                $password = base64_decode(xphp_decrypt_js($params['app_password']));
                if (v1_pt_pass_decrypt($organizationInfo['app_password']) != $password && $organizationInfo['app_password'] != $password) {
                    $info = array(
                        'state' => false,
                        'des' => xphp_get_lang('WEB_M365_INPUT_CORRENT_PASSWORD_TIPS'),
                    );
                }
                if ($organizationInfo['app_username'] != $params['app_username']) {
                    $info = array(
                        'state' => false,
                        'des' => xphp_get_lang('WEB_M365_INPUT_CORRENT_ACCOUNT_TIPS'),
                    );
                }
            } else {//online
                if ($organizationInfo['tenant_uuid'] != $params['tenant_uuid']) {
                    $info = array(
                        'state' => false,
                        'des' => xphp_get_lang('WEB_M365_TENANT_INFO_NOT_MATCH'),
                    );
                }
                if ($organizationInfo['username'] != $params['username']) {
                    $info = array(
                        'state' => false,
                        'des' => xphp_get_lang('WEB_M365_USER_INFO_NOT_MATCH'),
                    );
                }
            }
        }
        return $info;
    }
    /**
     * 格式化html
     * @param unkown $content 参数
     * @param unkown $nodeUuid nodeuuid
     * @return string 返回结果
     */
    private function formatHtml($content, $nodeUuid) {
        //邮件内增加由备份系统发送的提示 下面一根横线
        $email_body_feature = ExchangeJobInfo::instance()->getNodeNameAndIp($nodeUuid);
        $ip = explode(" ", trim($email_body_feature['ip']))[0];
        $des = xphp_get_lang('WEB_M365_SEND_BY_BACKUP_SYSTEM');
        //正则匹配body
        $pattern = '/<body\b[^>]*>/i';
        $replacement = '${0}<div>'. $des . $ip .'</div><hr>'; // ${0} 表示匹配到的整个<body>标签开头
        // 替换第一个匹配项，所以将preg_replace的第四个参数设置为1
        $modifiedHtml = preg_replace($pattern, $replacement, $content, 1);
        return $modifiedHtml;
    }
}