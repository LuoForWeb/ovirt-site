<?php

namespace app\v2\s3\v0\logic;

use app\v2\common\logic\Backup;
use app\v2\common\logic\Recover;
use app\v2\job\v0\logic\JobInfo;
use app\v2\opcode\FsOpcode;
use app\v2\opcode\PfOpcode;
use app\v2\resources\v0\logic\Node;
use app\v2\tenant\v0\logic\Tenant;
use app\v2\user\v0\logic\User;
use app\v2\common\logic\JobInfo as JobInfos;
use xphp\BLLHandler;
use app\v2\s3\v0\logic\S3JobController;
use app\v2\resources\v0\logic\Index;
use app\v2\resources\v0\logic\Storage;
use app\v2\backupData\v0\logic\DataManage;

/**
 * note          对象存储 - 恢复管理 logic
 * @auther       chengjiafu@vinchin.com
 * @date         2023/10/11 18:02
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsRecover extends Recover
{

    /**
     * 获取备份时间点树 
     * @param array $params
     * @return array
     */
    public function getObsDataTree(array $params)
    {

        $dataflag = $params['data_flag'];
        $storage_uuid = $params['storage_uuid'];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');

        $sql = "select bsr.node_uuid, bbt.id, bbt.module_type, bbt.task_type, bbt.timepoint_uuid,
                        bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid,
                        bbt.importance_flag,bbt.src_data_deleted_flag, 
		                bbt.user_uuid, bbt.user_name, fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                        fbt.source_agent_type as os_type, fbt.detail, bbt.task_name
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and 
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
                       and bbt.import_flag = ? and
	                   bbt.module_type = {$moduleType['FS']} and
	                   bbt.sub_module_type = 4 and
	                   bbt.data_local_flag = ? and  bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']}) ";

        $flag = xphp_get_config('app', 'FLAG');
        $user = xphp_get_user_info();
        $userUUID = $user['userUuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);

        // 非租户用户不能查看租户的资源
        if (empty($_SESSION['tenantuuid'])) {
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids, 'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }

        //租户管理员特殊处理数据显示
        $tenantHandler = new Tenant();
        $tenantMangerFlag = (new User())->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($user['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        if (v1_auth_need_check_look()) {
            $obsUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['obs'], 2);
            $sql .= " and bbt.user_uuid in ($obsUuidSql) ";
        }

        // 备份数据
        if ($dataflag) {
            $sql .= ' and bbt.copy_flag = ? and bbt.module_type = ? ';
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], $moduleType['FS']));
        }

        if (!empty($storage_uuid)) { // 存储uuid不为空
            $sql .= " and bbt.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storage_uuid));
        }

        // if (!empty($nodeuuid)) {
        //     $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? )";
        //     $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
        // }

        $sql .= " order by bbt.timepoint desc";

        $data = $this->dbSelect($sql, $sqlParams);

        //定义agent task 数组
        $node = $agent = $task = [];

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getAllTaskUUIDS(3, 4);

        foreach ($data as $d) {
            if ($d['module_type'] == $moduleType['BACKUP_COPY_CLIENT'] && $dataflag) {
                continue;
            }

            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];

            $detail = json_decode($d['detail'], true);

            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';

                if (
                    in_array($d['task_type'], [$taskType['BACKUP_COPY'], $taskType['BACKUP_COPY_FETCH']])
                ) {
                    $name = $taskName . '(' . xphp_get_lang('UI_COPY_DATA') . ')';
                } elseif (in_array($d['task_type'], [$taskType['ARCHIVE'], $taskType['ARCHIVE_FETCH']])) {
                    $name = $taskName . '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')';
                }

                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != $userUUID) {
                    $name .= '(' . $d['user_name'] . ')';
                }

                $node[] = array(
                    'id' => $d['task_uuid'],
                    'pId' => 0,
                    'name' => $name,
                    'title' => $name,
                    'open' => true,
                    'nocheck' => !$dataflag,
                    'type' => 1,
                    'icon' => './img/platform/flag.png',
                    'node_uuid' => $d['node_uuid'],
                    'job_uuid' => $d['task_uuid'],
                    'isParent' => true,
                    'agent_uuid' => $d['agent_uuid'],
                    'origin_vendor' => $detail['origin_vendor']
                );
            }

            //检查并添加agent
            if (!in_array($d['agent_uuid'] . '_' . $d['task_uuid'], $agent)) {
                $agent[] = $d['agent_uuid'] . '_' . $d['task_uuid'];
                $node[] = array(
                    'id' => $d['agent_uuid'] . '_' . $d['task_uuid'],
                    'pId' => $d['task_uuid'],
                    'name' => $d['agent_name'],
                    'title' => $d['agent_name'],
                    'nocheck' => !$dataflag,
                    'type' => 2,
                    'icon' => $d['os_type'] == 'Windows' ? './img/os/Windows.png' : './img/s3/cloud-platform.svg',
                    'node_uuid' => $d['node_uuid'],
                    'job_uuid' => $d['task_uuid'],
                    'agent_uuid' => $d['agent_uuid'],
                    'isParent' => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1,
                    'origin_vendor' => $detail['origin_vendor']
                );
            }
        }

        return $node;
    }

    /**
     * 获取对象存储下的备份时间点 
     * @param array $params
     * @return void
     */
    public function getSyncBackupTimePoint(array $params)
    {
        // 1.组装SQL查询参数
        $recoverflag = $params['recover_flag'] ?? false; //文件恢复加载时间点
        $taskuuid = $params['job_uuid'];
        $agentuuid = $params['agent_uuid'];
        $id = $params['id'];
        $storage_uuid = $params['storage_uuid'];
        $dataflag = $params['data_flag'] ?? false; // 备份数据的树形结构 
        $chkDisabled = false;
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');

        $sql = "SELECT 
                    bsr.storage_uuid, bsr.storage_nickname, bsr.storage_type, bsr.node_uuid, 
                    bbt.real_node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid,
                    unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.task_name, 
                    bbt.importance_flag, bbt.encrypted_flag, bbt.remarks, bbt.detail, bbt.src_data_deleted_flag, 
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail, bbt.integrity_check_flag  
                FROM 
                    bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                WHERE 
                    bbt.timepoint_uuid = fbt.fs_timepoint_uuid AND
        			bbt.storage_uuid = bsr.storage_uuid AND
                    bbt.deleted_flag = ? AND 
                    bbt.available_flag = ? AND
                    bbt.import_flag = ? AND
	                bbt.module_type in ({$moduleTypeArr['FS']},{$moduleTypeArr['BACKUP_COPY_CLIENT']}) AND
	                bbt.sub_module_type = 4 AND
	                bbt.task_uuid = ? AND 
                    fbt.agent_uuid = ? AND 
                    bbt.data_local_flag = ? ";

        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $taskuuid, $agentuuid, $flag['SET']);

        if (!empty($storage_uuid)) { // 存储uuid不为空
            $sql .= " and bbt.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storage_uuid));
        }

        if (!empty($nodeuuid)) {
            $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? )";
            $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
        }

        $sql .= " order by fbt.agent_uuid, bbt.task_uuid, bbt.timepoint";
        // 2.查询SQL
        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }

        // 3.筛选出每个完备和非完备对应PID
        $fulluuidList = array();
        $unfullList = array();
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');
        // 3-1.遍历data组装增备pid和差备pid
        foreach ($data as $point) {
            if ($point['backup_mode'] == $backupMode['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $unfullList[] = $point;
            }
        }

        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $key => $value) {
                    if ($dependId == $key) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                        //                        $unfullList = array_splice($unfullList, $key, 1);
                        unset($unfullList[$key]);
                        $unfullList = array_values($unfullList);

                        $unfullCountTmp--;
                    }
                }
            }
            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) {
                break;
            }
        }

        $storageHandler = new Storage();
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);

        // 4.处理时间点数据
        $node = array();
        $jobs = new JobInfos();
        $storageOfflineStatus = xphp_get_config('storage', 'STORAGE_STATUS')['OFFLINE'];

        foreach ($data as $d) {
            // 判断当前时间点所在存储是否离线，是则置灰节点
            $isOfflineStorage = 1;
            foreach ($storageStatusList as $key => $storageStatusInfo) {
                if ($d['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                    $isOfflineStorage = $storageStatusInfo['storage_status'];
                    break;
                }
            }

            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }

            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) . ' (' . $jobs->getTimepointTypeDes($d['backup_mode']) . ')';
            $title = $name;

            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')' . '</span>';
            }

            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }

            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == $backupMode['FULL']) {
                $node[] = array(
                    'id' => $d['timepoint_uuid'],
                    'pId' => $id,
                    'name' => $name,
                    'title' => $title,
                    'checked' => false,
                    'type' => 3,
                    'nocheck' => false,
                    'oldname' => $name,
                    'gfsforever' => $gfsforever ?? '',
                    'point_uuid' => $d['timepoint_uuid'],
                    'depend_uuid' => $d['depend_point_uuid'],
                    'agent_name' => $d['agent_name'],
                    'agent_ip' => $d['agent_ip'],
                    'agent_uuid' => $d['agent_uuid'],
                    'task_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'icon' => $jobs->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'taskuuid' => $taskuuid,
                    'nodeuuid' => $nodeUuid,
                    'storage_nickname' => $d['storage_nickname'],
                    'storage_type' => $d['storage_type'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'encrypted_flag' => $encryptedflag,
                    'os_type' => 'linux',
                    'password_auto_flag' => intval($detail['password_auto_flag']) == 1,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1,
                    'chkDisabled' => $isOfflineStorage == $storageOfflineStatus,
                    'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                );
                continue;
            }

            $node[] = array(
                'id' => $d['timepoint_uuid'],
                'pId' => $fulluuidList[$timepointuuid],
                'name' => $name,
                'title' => $title,
                'checked' => false,
                'type' => 4,
                'oldname' => $name,
                'nocheck' => $recoverflag,
                'point_uuid' => $d['timepoint_uuid'],
                'depend_uuid' => $d['depend_point_uuid'],
                'agent_name' => $d['agent_name'],
                'agent_ip' => $d['agent_ip'],
                'agent_uuid' => $d['agent_uuid'],
                'task_name' => $d['task_name'],
                'star' => intval($d['importance_flag']) == $flag['SET'],
                'icon' => $jobs->getTimepointIcon($d['backup_mode']),
                'mode' => intval($d['backup_mode']),
                'timepoint_uuid' => $d['timepoint_uuid'],
                'job_uuid' => $taskuuid,
                'nodeuuid' => $d['node_uuid'],
                'chkDisabled' => $isOfflineStorage == $storageOfflineStatus,
                'storage_nickname' => $d['storage_nickname'],
                'storage_type' => $d['storage_type'],
                'timepoint' => $this->parseDate($d['timepoint']),
                'encrypted_flag' => $encryptedflag,
                'os_type' => 'linux',
                'password_auto_flag' => intval($detail['password_auto_flag']) == 1,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1,
                'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
            );
        }

        return $node;
    }

    /**
     * 获取恢复存储树
     * @param $params
     * @return array
     */
    public function getRecoverObsTree($params = array()): array
    {
        // 1.获取对象存储表中的所有对象存储
        $sql = 'SELECT 
                    obs.id, obs.obs_uuid, obs.obs_nickname, obs.obs_create_time, obs.vendor, 
                    obs.access_key_id, obs.access_key_secret, obs.endpoint_override, obs.ssl_verify_flag,
                    obs.status, obs.authorization 
                    FROM 
                    obs_resource obs WHERE obs.id IS NOT NULL ';
        // 2.获取用户
        $user = xphp_get_user_info();
        $sqlParams = array();

        if (v1_auth_need_check_look()) {
            $obsUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs.obs_uuid');

            $sql .= ' AND ' . $obsUuidSql;
        }

        if (!empty($params['keyword'])) {
            $search = $params['keyword'];

            $sql .= " AND (obs_nickname LIKE '%" . $search . "%' OR endpoint_override like '%" . $search . "%')";
        }

        // 3.过滤出当前用户下的所有对象存储
        $data = $this->dbSelect($sql, $sqlParams);

        // 4.获取所有云服务商
//        $storageVendors = xphp_get_config('resource', 'OBJECT_STORAGE_VENDOR');
        $hasPushedFlag = [false, false, false, false, false, false, false, false, false, false];

        $firstLevelArr = [];
        foreach ($data as $d) {
            switch ($d['vendor']) {
                case 0:
                    $item = array('pId' => 0, 'id' => 1, 'name' => xphp_get_lang('UI_OBS_VENDOR_AWS'), 'title' => xphp_get_lang('UI_OBS_VENDOR_AWS'), 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/aws.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[0]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[0] = true;
                    }
                    break;
                case 1:
                    $item = array('pId' => 0, 'id' => 2, 'name' => xphp_get_lang('UI_OBS_VENDOR_OSS'), 'title' => xphp_get_lang('UI_OBS_VENDOR_OSS'), 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/ali.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[1]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[1] = true;
                    }
                    break;
                case 2:
                    $item = array('pId' => 0, 'id' => 3, 'name' => xphp_get_lang('UI_OBS_VENDOR_COS'), 'title' => xphp_get_lang('UI_OBS_VENDOR_COS'), 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/tengxun.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[2]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[2] = true;
                    }
                    break;
                case 3:
                    $item = array('pId' => 0, 'id' => 4, 'name' => xphp_get_lang('UI_OBS_VENDOR'), 'title' => xphp_get_lang('UI_OBS_VENDOR'), 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/huawei.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[3]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[3] = true;
                    }
                    break;
                case 4:
                    $item = array('pId' => 0, 'id' => 5, 'name' => 'Ceph S3', 'title' => 'Ceph S3', 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/ceph.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[4]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[4] = true;
                    }
                    break;
                case 5:
                    $item = array('pId' => 0, 'id' => 6, 'name' => 'Wasabi', 'title' => 'Wasabi', 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/wasabi.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[5]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[5] = true;
                    }
                    break;
                case 6:
                    $item = array('pId' => 0, 'id' => 7, 'name' => 'MinIO', 'title' => 'MinIO', 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/minio.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[6]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[6] = true;
                    }
                    break;
                case 7:
                    $item = array('pId' => 0, 'id' => 8, 'name' => xphp_get_lang('UI_OBS_VENDOR_AZURE'), 'title' => xphp_get_lang('UI_OBS_VENDOR_AZURE'), 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/azure.svg', 'type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[7]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[7] = true;
                    }
                    break;
                case 8:
                    $item = array('pId' => 0, 'id' => 9, 'name' => 'Huawei OceanStor Pacific', 'title' => 'Huawei OceanStor Pacific', 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/huawei.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[8]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[8] = true;
                    }
                    break;
                case 9:
                    $item = array('pId' => 0, 'id' => 10, 'name' => xphp_get_lang('UI_OBS_VENDOR_OTHER'), 'title' => xphp_get_lang('UI_OBS_VENDOR_OTHER'), 'open' => false, 'nocheck' => true, 'isParent' => true, 'icon' => './img/s3/other-cloud.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[9]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[9] = true;
                    }
                    break;
                default:
                    break;
            }
        }

        $nodes = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['status'] === 0) { // 优先显示离线
                    $name = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')' . $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')';
                } else {
                    $name = $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')';
                }

                // 4.根据不同服务商进行分组
                $nodes[] = array(
                    'id' => $d['obs_uuid'],
                    'uuid' => $d['obs_uuid'],
                    'pId' => $d['vendor'] + 1,
                    'vendor' => $d['vendor'],
                    'name' => $name,
                    'title' => $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')',
                    'isParent' => false,
                    'nocheck' => false,
                    'open' => false,
                    'type' => 'obsItem',
                    'event_type' => 'obs_agent',
                    'os_type' => 'linux',
                    'chkDisabled' => $d['status'] === 0 ? true : false,
                    'isOfflineNode' => $d['status'] === 0 ? true : false, // 是否为禁用且已勾选节点
                    'icon' => './img/s3/cloud-platform.svg',
                );
            }
        }

        return array_merge($firstLevelArr, $nodes);
    }

    /**
     * 对象存储恢复 - 停止搜索
     * @param array $params
     * @return array|true[]
     */
    public function stopSearchJob(array $params): array
    {
        $opName = 'FS_OP_TYPE_SEARCH_STOP';

        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);
        $msg = [
            'task_uuid' => $params['thread_uuid'],
        ];
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg));

        if ($mbResult['result']) {
            $info = [
                'result' => true
            ];
        } else {
            //失败
            $info = [
                'result' => false,
                'msg' => xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE'),
                'code' => $mbResult['errorCode']
            ];
        }

        return $info;
    }

    /**
     * 检验对象数据加密密码的正确性
     * @param array $params
     * @return array|string
     */
    public function checkObsEncryptPass(array $params)
    {
        $timepointuuid = $params['timepoint_uuid'];
        $inputpass = base64_decode($params['passwd']);
        $sqlencrypt = "select encrypted_flag, detail from bd_backup_timepoint where timepoint_uuid  = ? ";
        $sqlParamsencrypt = array($timepointuuid);
        $dataencrypt = $this->dbSelect($sqlencrypt, $sqlParamsencrypt);//password_auto_flag, password
        $dataencrypt = $dataencrypt[0];
        $detail = json_decode($dataencrypt['detail'], true);

        if ($dataencrypt['encrypted_flag'] == 1 && intval($detail['password_auto_flag']) == 2) {//加密开 自动生成密码关
            $inputpass = v1_pt_pass_encrypt(htmlspecialchars_decode($inputpass));//htmlspecialchars_decode把特殊字符转成原来的字符

            if ($detail['password'] != $inputpass) {//数据库中的密码不等于页面输入密码
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_BACKUP_DATA_ENCRYPT'),
                    xphp_get_lang('UI_FILE_INCORRECT_PASSWORD'),
                    'warning'
                );
            }
        }

        return [
            'encrypted_flag' => $dataencrypt['encrypted_flag'],
            'password_auto_flag' => intval($detail['password_auto_flag']),
            'flag' => true
        ];
    }

    /**
     * 获取恢复文件目录树
     * @param array $params
     * @return array|string
     */
    public function getRecoverDir(array $params)
    {
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = $params['root_flag'];
        $start = $params['start'];
        $number = $params['limit'] ?? 10;
        $path = $params['path'] ?? '';
        $md5Flag = $params['md5_flag'];
        $md5High = $params['md5_high'] ?? '';
        $md5Low = $params['md5_low'] ?? '';
        $pid = $params['pid'] ?? 0;
        $sclass = $params['sclass'];

        $msg = array(
            'timepoint_uuid' => $timepointUUID,
            'root_flag' => $rootFlag,
            'start' => $start,
            'number' => $number,
            'path' => $path,
            'md5_flag' => $md5Flag,
            'md5_high' => $md5High,
            'md5_low' => $md5Low
        );

        $nodeHandler = new Node();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        $mbResult = $this->mbOBSMsg($nodeuuid, $opName, json_encode($msg), true, true, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);

        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $pfOpcode = new PfOpcode();
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        $data = $mbResult['msg'];
        $list = array(
            'finish_flag' => $data['finish_flag'],       //文件是否列完成   1完成,2未完成
            'next_start' => $data['next_start'],       //未完成时,下一个开始位置
            'path' => $data['path'],                    //当前路径
            'timepoint_uuid' => $data['timepoint_uuid'],
        );

        $fileList = array();

        foreach ($data['item_list'] as $d) {
            switch ($d['type']) {
                case 1:
                    $icon = './img/fs/wenjian.png';
                    if ($params['sub_flag']) {
                        // 截取字符串最后一个/后面所有的内容
                        $name = substr($d['path'], strrpos($d['path'], '/') + 1);
                    } else {
                        $name = $d['path'];
                    }
                    break;
                case 2:
                    $icon = './img/fs/wenjianjia.png';
                    if ($params['sub_flag']) {
                        // 截取字符串倒数第2个/后面所有的内容
                        $name = substr($d['path'], 0, strrpos($d['path'], '/'));//最后一个斜杠前面的所有内容
                        $name = substr($name, strrpos($name, '/') + 1);
                    } elseif ($d['path'] != '/') {//不是linux根目录
                        $name = substr($d['path'], 0, strrpos($d['path'], '/'));
                    } else {//是linux根目录不剪切斜杠
                        $name = $d['path'];
                    }
                    break;
                case 3:
                    $icon = './img/s3/cunchutong.svg';
                    $name = substr($d['path'], 0, strrpos($d['path'], '/'));
                    break;
                default:
                    break;
            }
            $filename = $d['filename'];
            $fileType = xphp_get_config('file', 'FILETYPE');
            $fileList[] = array(
                'id' => $d['path'],
                'pid' => $pid,
                'name' => $name ? $name : '/',
                'nocheck' => false,
                'title' => $name ? $name : '/',
                'path' => $d['path'],
                'btype' => $d['type'],   //文件类型
                'isParent' => !($d['type'] == $fileType['FILE']),
                'icon' => $icon ?? '',
                'iconOpen' => $d['type'] == 2 ? './img/fs/wenjianjiaopen.png' : $icon,
                'iconClose' => $d['type'] == 2 ? './img/fs/wenjianjia.png' : $icon, // 从后台获取的文件类型
                'isfile' => $d['type'] == $fileType['FILE'],   //是否是文件
                'sclass' => $this->getFileClassName($d['type'], $filename, $sclass), //显示类型
                'md5_flag' => $d['md5_flag'],
                'md5_high' => $d['md5_high'],
                'md5_low' => $d['md5_low'],
                "create_time" => $d['create_time'],
                'modify_time' => $d['modify_time'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'more' => false,
            );
        }

        $list['filelist'] = $fileList;

        if (intval($data['finish_flag']) === 0) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => $data['path'] . '/more',
                'pid' => $pid,
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'nocheck' => true,
                'more' => true,
                'next_index' => $data['next_start'],       //从哪个位置开始加载
                // "search_file_name" => $data['path'],          //从哪个目录开始加载
                // "dir_path" => $params['dir'],
                'timepoint_uuid' => $params['timepoint_uuid'],
                'sclass' => $sclass,                //显示类型
                'md5_flag' => (string) $md5Flag,
                'md5_high' => (string) $md5High,
                'md5_low' => (string) $md5Low,                             //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            array_push($list['filelist'], $more);
            // $list['filelist'] = $more;
        }

        return $list;
    }

    /**
     * 获取恢复对象存储下的文件目录树
     * @param array $params
     * @return void
     */
    public function getRecoverPathTree(array $params)
    {
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agent_uuid'];
        $pid = $params['pid'];

        $this->paramsCheck($agentUUID);

        $codetype = $params['code_type']; //编码类型
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OBS'];
        $startAfter = $params['start_after'];

        $msg = array(
            'target_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'module_type' => $moduleType,
            'submodule_type' => xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'],
            'code_type' => $codetype,
            'start_after' => $startAfter
        );

        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY';
        $nodeuuid = (new Node())->getLocalNodeUUID();

        $mbResult = $this->mbOBSMsg($nodeuuid, $opName, json_encode($msg), true, true, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);

        $result = $mbResult['result'];

        // 日志记录
        $logger = new \xphp\log\Log();

        $logger->write(print_r($msg, true));
        $logger->write(print_r($mbResult, true));

        if (empty($result)) {
            //失败
            $fsOpcode = new FsOpcode();
            $operate = $fsOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        $data = $mbResult['msg'];
        $i = 0;
        $flag = xphp_get_config('app', 'FLAG');

        if (!empty($data['item_list'])) {
            if (empty($dir)) {
                foreach ($data['item_list'] as $d) {
                    $node = array(
                        'id' => $pid . '_' . $i++,
                        'pid' => $pid == '' ? 0 : $pid,
                        'uuid' => $agentUUID,
                        'name' => $d['item_name'],
                        'title' => $d['item_name'],
                        'dir_path' => $d['item_path'],
                        'isParent' => true,
                        'nocheck' => false,
                        'icon' => './img/s3/cunchutong.svg',
                        'type' => $d['item_type'],
                        'more' => false,
                        'noRemoveBtn' => true,
                        'isnew' => false, //文件夹是否是最新的新建
                        "new_dir_create" => $flag['UNSET'], //文件夹是否是新建的
                        'noEditBtn' => true,
                        //                 "icon" => "./img/vm/host.png",
                        'code_type' => intval($d['code_type']),
                        'event_type' => 'obs_agent'
                    );
                    $tree[] = $node;
                }
            } else {
                foreach ($data['item_list'] as $d) {
                    $node = array(
                        'id' => $pid . '_' . $i++,
                        'pid' => $pid == '' ? 0 : $pid,
                        'uuid' => $agentUUID,
                        'name' => $d['item_name'],
                        'title' => $d['item_path'],
                        'dir_path' => $d['item_path'],
                        'isParent' => true,
                        'nocheck' => false,
                        'icon' => './img/fs/wenjianjia.png',
                        'iconOpen' => './img/fs/wenjianjiaopen.png',
                        'iconClose' => './img/fs/wenjianjia.png',
                        'type' => $d['item_type'],
                        'more' => false,
                        'noRemoveBtn' => true,
                        'isnew' => false, //文件夹是否是最新的新建
                        "new_dir_create" => $flag['UNSET'], //文件夹是否是新建的
                        'noEditBtn' => true,
                        //                 "icon" => "./img/vm/host.png",
                        'code_type' => intval($d['code_type']),
                        'event_type' => 'obs_agent'
                    );
                    $tree[] = $node;
                }
            }
        }

        if (intval($data['is_search_finish']) == 0) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => $pid . '_' . $i++,
                'pid' => $pid == '' ? 0 : $pid,
                'uuid' => $agentUUID,
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'icon' => './img/fs/wenjianjia.png',
                'iconOpen' => './img/fs/wenjianjiaopen.png',
                'iconClose' => './img/fs/wenjianjia.png',
                'isParent' => false,
                'nocheck' => true,
                'more' => true,
                'next_index' => $data['current_next_index'],       //从哪个位置开始加载
                'search_file_name' => $data['search_file_name'],          //从哪个目录开始加载
                'dir_path' => $dir,                                  //当前目录名
                'no_remove_btn' => true,
                'isnew' => false, //文件夹是否是最新的新建
                "new_dir_create" => $flag['UNSET'], //文件夹是否是新建的
                'no_edit_btn' => true,
                'event_type' => 'obs_agent'
            );
            $tree[] = $more;
        }

        return $tree ?? [];
    }

    /**
     * 创建搜索消息
     * @param array $params
     * @return array
     */
    public function createSearchJob(array $params): array
    {
        $opName = 'FS_OP_TYPE_SEARCH_THREAD_CREATE';

        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);

        $msg = [
            'search_mode' => $params['search_mode'],
            'md5_list' => $params['md5_list'],
            'path_name' => $params['path_name'],
            'timepoint_uuid' => $params['timepoint_uuid'],
            'req_total_num' => $params['req_total_num'],
            'module_type' => $params['module_type'],
            'submodule_type' => $params['sub_module_type']
        ];

        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg), true, true);

        if ($mbResult['result']) {
            $info = [
                'result' => true,
                'msg' => $mbResult['msg']['task_uuid'],
            ];
        } else {
            $fsOpcode = new FsOpcode();
            $operate = $fsOpcode->getOpcodeDes($opName);
            $result = $mbResult['result'];

            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }

        return $info;
    }

    /**
     * 获取搜索结果
     * @param array $params
     * @return array|string
     */
    public function getSearchInfo(array $params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_RESULT_GET';
        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['timepoint_uuid']);
        $msg = [
            'task_uuid' => $params['thread_uuid'],
            'number' => $params['limit'],
            'offset' => $params['offset'],
        ];

        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, json_encode($msg), true, false);

        $operate = xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_RESULT_GET');
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $result = $mbResult['msg'];
        $filenode = array();
        foreach ($result['item_list'] as $file) {
            if ($file['file_type'] == 1) {
                // 文件
                $titleDes = xphp_get_lang('WEB_FILE_FILE_SIZE') . v1_calsize($file['file_size'], true)
                    . PHP_EOL . xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($file['modify_time']);
            } else {
                $titleDes = xphp_get_lang('WEB_FILE_MODIFY_TIME') . $this->parseDate($file['modify_time']);
            }
            $filenode[] = array(
                'pid' => 0,
                'id' => $file['path_name'],
                'name' => $file['path_name'],
                'title' => $titleDes,
                'file_type' => $file['file_type'],
                'md5_high' => $file['md5_high'],
                'md5_low' => $file['md5_low'],
                'file_size' => $file['file_size'],
                'modify_time' => $file['modify_time'],
                'file_offset' => $file['offset'],
                'icon' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                'iconOpen' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                'iconClose' => $file['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                'is_arent' => $file['file_type'] != 1, //1文件  2文件夹
                'path' => $file['path_name'],
                'point_uuid' => $params['timepoint_uuid'],
                'md5_flag' => '1',
                'search_node' => true,
            );
        }
        return [
            'search_finish_flag' => $result['finish_flag'],
            'current_total_num' => $result['number'],
            'current_dir_num' => $result['dir_num'],
            'current_file_num' => $result['file_num'],
            'all_file_num' => $result['all_file_num'],
            'all_dir_num' => $result['all_dir_num'],
            'offset' => $result['offset'],
            'filenode' => $filenode,
        ];
    }

    /**
     * 创建恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createRecoverJob(array $params)
    {
        $taskname = htmlspecialchars_decode($params['job_name']);
        $moduletypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        $moduletype = $moduletypeArr['FS'];
        $recoveryposition = intval($params['recoverInfo']['pathType']);
        $recoverytimetype = intval($params['typeInfo']['type']);
        $timestrategylist = $this->groupRecoverTimeList($params['typeInfo']);
        $transportstrategy = (new Backup())->groupTransportStrategy($params['typeInfo']['high']['trasfer']);

        $pfMsg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $taskname,
            $moduletype,
            $recoveryposition,
            $recoverytimetype,
            $timestrategylist,
            $transportstrategy
        );

        $pfMsg['task_type'] = $tasktypeArr['RECOVERY'];
        $pfMsg['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'];
        $pfMsg['recovery_level'] = 1;
        $pfMsg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMsg['fs_path_list'] = $this->getRecoverFileList($params['pointInfo'], $params['recoverInfo']);
        $pfMsg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedInfo']);
        $pfMsg['password'] = $params['recoverInfo']['password'];
        $pfMsg['thread_num'] = $params['thread_num'];
        $pfMsg['new_dir_create'] = $params['recoverInfo']['new_dir_create'];
        //是否是跨平台传输
        $pfMsg['cross_platform_transform'] = $params['recoverInfo']['cross_platform_transform'];
        $pfMsg['same_file_strategy'] = intval($params['highInfo']['same_file_strategy']);
        $pfMsg['dir_tree_recovery_flag'] = v1_parse_bool_to_flag($params['highInfo']['dir_tree_recovery_flag']);
        $pfMsg['link_file_pass_flag'] = v1_parse_bool_to_flag($params['highInfo']['link_file_pass_flag']);
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']);
        $pfMsg['distinct_flag'] = intval($params['distinct_flag']);
        // 传输代理
        $pfMsg['appliance_uuid'] = $params['typeInfo']['high']['trasfer']['appliance_uuid'];
        $pfMsg['agent_pool_uuid'] = $params['typeInfo']['high']['appliance_pool_uuid'];
        $pfMsg['retry_strategy'] = (new Backup())->groupRetryStrategy($params['retry_strategy']); // 重试策略
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['highInfo']['ignore_resource_limiting_flag']); // 忽略节点资源限制
        // 安全策略
        $pfMsg['safe_config_strategy'] = $params['safe_strategy'];

        $opName = 'BD_TASK_OP_RECOVERY_CREATE';

        $nodeuuid = (new Node())->getNodeUUIDWithTimepointUUID($params['pointInfo']['pointUUID']);

        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$params['pointInfo']['pointUUID']]);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }

        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($taskname);
            } else {
                $startResult = true;
            }
            //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取默认的恢复任务名
     * @param array $params
     * @return void
     */
    public function getDefaultRecoverTaskName(array $params)
    {
        $taskName = $params['job_name'];
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            if (empty($data)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }

        return $oldTaskName;
    }

    /**
     * 获取对象存储对应的终端节点end point
     * @param string $obsUUID 对象存储id
     * @return string
     */
    private function getObsEndPointName(string $obsUUID)
    {
        $sql = "select obs_nickname, endpoint_override from obs_resource where obs_uuid = ?";
        $result = $this->dbSelect($sql, array($obsUUID));

        return $result[0]['obs_nickname'] . '(' . $result[0]['endpoint_override'] . ')';
    }

    /**
     * 得到恢复的文件列表信息
     * @param array $pointInfo   参数
     *                           agentUUID
     *
     *                           恢复源代理UUID
     *                           pointUUID
     *
     *                           恢复时间点
     *                           fileInfo
     *
     *                           array
     *                           [类型,路径,名字,MD5high,
     *                           MD5low]
     * @param array $recoverInfo 参数
     *                           type        恢复类型    原机1/异机2
     *                           agentUUID   恢复目标UUID    原机就是原机的UUID,异机就是异机的UUID
     *                           pathtype    恢复路径类型      原路径1/新路径2
     *                           path        恢复新路径
     * @return array
     */
    private function getRecoverFileList($pointInfo, $recoverInfo): array
    {
        $fileInfo = $pointInfo['fileInfo'];
        $files = array();
        foreach ($fileInfo as $f) {
            $pathtype = intval($recoverInfo['pathType']);
            $newRootPath = '';
            if ($pathtype == xphp_get_config('app', 'FLAG')['UNSET']) { // 手动选择路径
                $newRootPath = $recoverInfo['path'];
                $agentUUID = $recoverInfo['agentUUID'];
            }

            $files[] = array(
                'agent_uuid' => $pointInfo['agentUUID'],
                'path_type' => intval($f[0]),
                'path_name' => $f[1],
                'new_root_path' => $newRootPath,
                'recovery_timepoint_uuid' => $pointInfo['pointUUID'],
                'md5_high' => $f[3],
                'md5_low' => $f[4],
            );
        }
        return $files;
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
        $data = $this->dbSelect(
            $sql,
            array(
                $taskName,
                xphp_get_config('module', 'MODULE_TYPE')['FS'],
                xphp_get_config('task', 'TASKTYPE')['RECOVERY']
            )
        );
        if (!$data) {
            return false;
        }

        //调用系统统一启动任务接口.不重新写
        $result = (new S3JobController())->startJob(
            $data[0]['task_uuid'],
            xphp_get_config('task', 'BACKUP_MODE')['FULL']
        );
        //        $result = (new \app\v2\s3\v0\logic\S3JobController())->startJob(
//            $data[0]['task_uuid'],
//            xphp_get_config('task', 'BACKUP_MODE')['FULL']
//        );


        //这里直接返回成功或失败 bool
        return $result;
    }

    /**
     * 获取文件模块下的子模块的所有任务UUID
     * @param $moduleType
     * @param $subModuleType
     * @return array
     */
    private function getAllTaskUUIDS($moduleType, $subModuleType)
    {
        $sql = 'select bt.task_uuid from bd_task bt, fs_task ft WHERE bt.task_uuid = ft.task_uuid AND bt.module_type = ? AND bt.delete_flag = ? AND ft.submodule_type = ?';

        $sqlParams = array($moduleType, xphp_get_config('app', 'FLAG')['UNSET'], $subModuleType);
        $data = $this->dbSelect($sql, $sqlParams);

        return !empty($data) ? array_column($data, 'task_uuid') : [];
    }
}