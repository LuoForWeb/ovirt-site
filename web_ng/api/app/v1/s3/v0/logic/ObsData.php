<?php

namespace app\v1\s3\v0\logic;

use app\v1\common\logic\Data;
use app\v1\common\logic\JobInfo;
use app\v1\common\logic\JobInfo as JobInfos;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;

/**
 * note          对象存储 - 备份数据管理logic
 * @auther       chengjiafu@vinchin.com
 * @date         2023/11/3 9:40
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsData extends Data
{

    /**
     * 获取备份时间点列表
     * @param array $params
     * @return array
     */
    public function getObsTimePointGrid(array $params)
    {

        $storage_uuid = $params['storage_uuid'];
        $start = intval($params['offset']);
        $length = intval($params['limit']);

        $agentuuid = $params['agent_uuid'];
        $taskuuid = $params['job_uuid'];

        $copyFlag = $params['copy_flag'];        //副本数据标志
        $archiveFlag = $params['archive_flag'];  //归档数据标志
        $storageuuid = $params['storage_uuid'];

        //         编号   时间点 类型  数据大小    用户  备注  操作  星标
        $sql = 'SELECT 
                    bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint,
                    bbt.backup_mode, bbt.total_size, bbt.write_size, bbt.data_local_flag, 
                    bbt.importance_flag, bbt.remarks, bbt.src_data_deleted_flag, bbt.user_uuid, bsr.node_uuid, bsr.storage_type, bbt.real_node_uuid  
                FROM 
                    bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr
                WHERE 
                    bbt.timepoint_uuid = fst.fs_timepoint_uuid AND 
                    bbt.storage_uuid = bsr.storage_uuid AND 
                    bbt.deleted_flag = ? AND 
                    bbt.import_flag = ? AND 
                    bbt.available_flag = ? AND 
                    fst.agent_uuid = ? AND 
                    bbt.task_uuid = ?';

        $sqlCount = "SELECT 
                        count(bbt.id) AS total 
                    FROM 
                        bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr
                    WHERE 
                        bbt.timepoint_uuid = fst.fs_timepoint_uuid AND 
                        bbt.storage_uuid = bsr.storage_uuid AND
                        bbt.deleted_flag = ? AND 
                        bbt.import_flag = ? AND 
                        bbt.available_flag = ? AND 
                        fst.agent_uuid = ? AND 
                        bbt.task_uuid = ?";

        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid);

        // 如果开始时间和结束时间都有 则添加时间查询
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $sql .= ' and unix_timestamp(bbt.timepoint) between ? and ? ';
            $sqlCount .= ' and unix_timestamp(bbt.timepoint) between ? and ? ';
            $sqlParams = array_merge($sqlParams, array(strtotime($params['start_time']), strtotime($params['end_time'])));
            $sqlCountParams = array_merge($sqlCountParams, array(strtotime($params['start_time']), strtotime($params['end_time'])));
        }

        // 如果有类型 则添加类型
        if (!empty($params['timepoint_type'])) {
            $sql .= ' and bbt.backup_mode = ? ';
            $sqlCount .= ' and bbt.backup_mode = ? ';
            $sqlParams = array_merge($sqlParams, array($params['timepoint_type']));
            $sqlCountParams = array_merge($sqlCountParams, array($params['timepoint_type']));
        }

        //如果有永久标记点 则添加永久标记的搜索
        if (!empty($params['forever'])) {
            $sql .= ' and bbt.importance_flag = ? ';
            $sqlCount .= ' and bbt.importance_flag = ? ';
            $sqlParams = array_merge($sqlParams, array($params['forever']));
            $sqlCountParams = array_merge($sqlCountParams, array($params['forever']));
        }

        // 如果切换了存储节点
        if ($storage_uuid) {
            $sql .= " and bbt.storage_uuid = ?";
            $sqlCount .= " and bbt.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storage_uuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storage_uuid));
        }

        // if ($nodeuuid) {
        //     $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? )";
        //     $sqlCount .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? )";
        //     $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
        //     $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid, $nodeuuid));
        // }

        if ($copyFlag || $archiveFlag) {
            if (!empty($$storage_uuid) && $$storage_uuid) {
                $sql .= ' and bsr.storage_uuid = ? ';
                $sqlCount .= ' and bsr.storage_uuid = ? ';
                $sqlParams = array_merge($sqlParams, array($$storage_uuid));
                $sqlCountParams = array_merge($sqlCountParams, array($$storage_uuid));
            }
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = array(
                'timepoint' => 'bbt.timepoint',
                'backup_mode' => 'bbt.backup_mode',
                'total_size' => 'bbt.total_size',
                'write_size' => 'bbt.write_size',
                'remarks' => 'bbt.remarks',
                'importance_flag' => 'bbt.importance_flag',
                'user_uuid' => 'bbt.user_uuid'
            );
            if (!empty($sortArr[$params['sort']]) && in_array($params['order'], ['asc', 'desc'])) {
                $sql .= " order by {$sortArr[$params['sort']]} {$params['order']} ";
            }
        }

        $sqlParams = array_merge($sqlParams, array($start, $length));
        $sql .= ' limit ? , ? ';

        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);

        // 查询所有者
        $userUuidList = array_column($data, 'user_uuid');

        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";

        $userSql = "select user_name, user_uuid from bd_user where user_uuid in ($userUuids)";
        $userData = $this->dbSelect($userSql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }

        $records = [];
        $storage = new Storage();
        $jobs = new JobInfo();

        $i = 1;
        foreach ($data as $d) {
            $mark = $this->pGetTimepointMark(
                v1_parse_flag_to_bool($d['weekly_flag']),
                v1_parse_flag_to_bool($d['monthly_flag']),
                v1_parse_flag_to_bool($d['yearly_flag']),
                v1_parse_flag_to_bool($d['importance_flag'])
            );

            $remotFlag = !v1_parse_flag_to_bool(intval($d['data_local_flag']));
            $op = array(1, 2, 3);
            if ($remotFlag) {//如果是异地副本，不能设置星标
                $op = array(1, 2);
            }

            $backupMode = xphp_get_config('task', 'BACKUP_MODE');

            // 1.时间点在合并中 2.增备和差备 3.存储类型为磁带 只能备注
            if (
                $d['archive_flag'] == $flag['SET'] ||
                in_array($d['backup_mode'], [$backupMode['INCREMENTAL'], $backupMode['DIFFERENTIAL']]) ||
                $d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']
            ) {
                $op = array(1);
            }

            $remark = '';
            if (!empty($d['remarks'])) {
                $remark .= '<a class="popovers remarktips remarktips_' . $d['timepoint_uuid'] . '" data-container="body" data-trigger="hover" data-placement="right" style="line-height: 25px;"
                data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
            }

            $star = '';
            if (v1_parse_flag_to_bool($d['importance_flag'])) {
                $star .= '<i title="' . xphp_get_lang('WEB_VM_GFS_FOREVER_POINT') . '" class="viconfont vicon-remark-forever"></i>';
            }

            $records[] = array(
                'num' => $i++,
                'uuid' => $d['timepoint_uuid'],
                'backup_mode' => $d['backup_mode'],
                'agent_uuid' => $agentuuid,
                'job_uuid' => $taskuuid,
                'remote_flag' => $remotFlag,
                'weekly_flag' => v1_parse_flag_to_bool($d['weekly_flag']),
                'monthly_flag' => v1_parse_flag_to_bool($d['monthly_flag']),
                'yearly_flag' => v1_parse_flag_to_bool($d['yearly_flag']),
                'importance_flag' => v1_parse_flag_to_bool($d['importance_flag']),
                'remark' => $d['remarks'],
                'remarks_span' => $remark,
                'star_span' => $star,
                'node_uuid' => empty($d['real_node_uuid']) ? $d['node_uuid'] : $d['real_node_uuid'],
                'src_data_deleted_flag' => $d['src_data_deleted_flag'],
                'timepoint_uuid' => $d['timepoint_uuid'],
                'storage_uuid' => $d['storage_uuid'],
                'description' => $mark,
                'timepoint' => $this->parseDate($d['timepoint']),
                'timepoint_type_desc' => $jobs->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                'total_size' => v1_calsize($d['total_size'], true),
                'write_size' => v1_calsize($d['write_size'], true),
                'storage_name' => $this->getStorageName($d['storage_uuid'], $d['timepoint_uuid']),
                'op' => $op,
                'owner' => $userMapping[$d['user_uuid']]
            );
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $records
        ];
    }

    /**
     * 获取存储名
     *
     * @param [type] $storageuuid
     * @param [type] $timepointUuid
     * @return void
     */
    private function getStorageName($storageuuid, $timepointUuid)
    {
        $sql = "select storage_nickname, node_uuid, storage_config, storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = $data[0]['storage_nickname'];
        $type = intval($data[0]['storage_type']);

        if ($type == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
            $storageConfig = json_decode($data[0]['storage_config'], true);
            $nodename = $storageConfig['remote_ip'];
        } else {
            $sqlNode = "select real_node_uuid from bd_backup_timepoint where timepoint_uuid = ?";
            $dataNode = $this->dbSelect($sqlNode, array($timepointUuid));
            $nodeUuid = !empty($dataNode[0]['real_node_uuid']) ? $dataNode[0]['real_node_uuid'] : $data[0]['node_uuid'];
            $nodename = (new Node())->getNodeName($nodeUuid);
        }

        $name .= "\n" . "(" . $nodename . ")";
        return $name;
    }

    /**
     * 搜索对象备份时间点  
     *
     * @param array $params
     * @return array
     */
    public function searchObsTimePoint(array $params): array
    {
        $dataflag = $params['data_flag'];
        $storage_uuid = $params['storage_uuid'];
        $search = $params['search'];
        $forever = $params['forever'];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');
        $flag = xphp_get_config('app', 'FLAG');
        $user = xphp_get_user_info();
        $userUUID = $user['userUuid'];

        // 1.组装SQL查询参数
        $sql = "SELECT 
                    bsr.node_uuid, bsr.storage_nickname,bsr.storage_type,
                    bbt.deleted_flag, bbt.real_node_uuid, bbt.task_uuid, bbt.id,
                    bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint,
                    bbt.backup_mode, bbt.importance_flag, bbt.detail, bbt.encrypted_flag,
                    bbt.remarks, bbt.src_data_deleted_flag, bbt.module_type, bbt.task_type, bbt.user_uuid, bbt.user_name,    
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail, fbt.source_agent_type as os_type 
                FROM 
                    bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                WHERE 
                    bbt.timepoint_uuid = fbt.fs_timepoint_uuid AND 
        			bbt.storage_uuid = bsr.storage_uuid AND 
                    bbt.available_flag = ? AND 
                    bbt.deleted_flag = ? AND 
	                bbt.module_type = " . $moduleType['FS'] . " AND 
                    bbt.sub_module_type = " . $submoduleType['OBS'] . " AND 
                    bbt.data_local_flag = ?";

        // 非租户用户不能查看租户的资源
        if (empty($_SESSION['tenantuuid'])) {
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids, 'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " AND bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . xphp_get_user_info()['userUuid'] . "'";
        }

        if ($dataflag) { // 备份数据页面
            $sql .= " AND bbt.task_type = {$taskType['BACKUP']}";
        } else { // 恢复页面
            $sql .= " AND bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
        }

        $sqlParams = array($flag['SET'], $flag['UNSET'], $flag['SET']);
        if ($forever) { // 查询永久标记点
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }

        if (!empty($search)) { // 查询搜索关键字
            $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        }

        if (!empty($params['recovery_range'])) {
            $sql .= ' and bbt.timepoint between ? and ?';
            $sqlParams = array_merge($sqlParams, array($params['startTime'], $params['endTime']));
        }

        // 租户管理员特殊处理数据显示
        $tenantHandler = new Index();
        $tenantMangerFlag = (new User())->pCheckTenantManager();
        // 获取租户管理员是否可以控制所有备份数据标志
        if (!empty($user['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $user['permissionArr']) || in_array('global_write', $user['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($dataflag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($user['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                // 权限重构
                $authUser = $user['authUser']['obs_protect_look'] ?? [];
                if ($authUser) {
                    $userUuidArr = array_merge([$userUUID], $authUser);
                    $userUuids = "('" . implode("','", $userUuidArr) . "')";
                    $sql .= " and bbt.user_uuid IN $userUuids ";
                } else {
                    $sql .= " and bbt.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array($userUUID));
                }
            }
        }

        // 2.查询时间点数据
        if (!empty($storage_uuid)) {
            $sql .= " and bbt.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storage_uuid));
            $pointData = $this->dbSelect($sql, $sqlParams);

            // $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? ) order by fbt.agent_uuid, bbt.timepoint";
            // $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
            // $pointData = $this->dbSelect($sql, $sqlParams);
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql, $sqlParams);
        }

        // 3.筛选出每个完备和非完备对应PID
        $fulluuidList = array();
        $unfullList = array();

        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == $backupMode['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else { // 如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
                if ($point['backup_mode'] == $backupMode['DIFFERENTIAL']) { // 差异
                    $sqlfull = "SELECT 
                                    bbt.deleted_flag, bsr.storage_nickname, bsr.node_uuid, 
                                    bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, 
                                    bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, 
                                    bbt.task_uuid, bbt.importance_flag, bbt.detail,bbt.encrypted_flag, bbt.remarks,bbt.src_data_deleted_flag,
                                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail 
                                FROM 
                                    bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                                WHERE 
                                    bbt.timepoint_uuid = fbt.fs_timepoint_uuid AND
                                    bbt.storage_uuid = bsr.storage_uuid AND 
                                    bbt.available_flag = 1 AND
                                    bbt.module_type = 3 AND 
                                    bbt.data_local_flag = 1 AND 
                                    bbt.timepoint_uuid = ? 
                                ORDER BY 
                                    fbt.agent_uuid, bbt.timepoint";

                    $data = $this->dbSelect($sqlfull, array($point['depend_point_uuid']));
                    if (!in_array($data[0]['timepoint_uuid'], $fulluuidList)) {
                        $pointData[] = $data[0]; //完备点
                    }
                } else { // 增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                    if (!in_array($fullpoint['timepoint_uuid'], $fulluuidList)) {
                        $fulluuidList[] = $fullpoint['timepoint_uuid']; //避免搜出重复完备点
                        $pointData[] = $fullpoint;
                    }
                    $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }

        // 4.处理时间点数据
        $node = array();
        $jobs = new JobInfos();
        $nodeHandler = new Node();
        foreach ($pointData as $d) {
            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) . ' (' . $jobs->getTimepointTypeDes($d['backup_mode']) . ')';
            $title = $name;

            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }

            $mark = '';
            if ($d["src_data_deleted_flag"] != 2) {//归档开启
                $mark = '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')' . $mark;
            }

            //添加GFS标识
            $mark .= $jobs->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            $gfsforever = $mark;
            if (!$dataflag) {
                $mark = '';
            }
            $nodeUuid = !empty($d['real_node_uuid']) ? $d['real_node_uuid'] : $d['node_uuid'];

            if (intval($d['backup_mode']) == $backupMode['FULL']) {
                $fullTimepoint = $d['timepoint_uuid'];
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $d['agent_uuid'] . "_" . $d['task_uuid'],
                    "name" => $dataflag ? $name . $mark : $name,
                    "title" => $title,
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "oldname" => $name,
                    "gfsforever" => $gfsforever,
                    "point_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => $d['agent_name'],
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == $flag['UNSET'],
                    "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "timepoint_uuid" => $d['timepoint_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "nodeuuid" => $nodeUuid,
                    'storagename' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    "nodename" => $nodeHandler->getNodeName($d['real_node_uuid']),
                    "encrypted_flag" => $encrypted_flag,
                    'os_type' => 'linux',
                    "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1
                );

                $incList = $this->getSearchIncTimepoint($d['timepoint_uuid']); // 增量备份点

                if (!empty($incList)) {
                    foreach ($incList as $d) {
                        if ($d['timepoint_uuid'] == $fullTimepoint) {
                            continue;
                        }
                        $detail = json_decode($d['detail'], true);
                        $name = $this->parseDate($d['timepoint']) . " (" . $jobs->getTimepointTypeDes($d['backup_mode']) . ")";
                        if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                            $name .= '<i class="fa fa-lock"></i>';
                            $encrypted_flag = true;
                        } else {
                            $encrypted_flag = false;
                        }
                        //添加GFS标识
                        $mark = $jobs->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
                        $gfsforever = $mark;
                        $node[] = array(
                            "id" => $d['timepoint_uuid'],
                            "pId" => $fullTimepoint,
                            "name" => $dataflag ? $name . $mark : $name,
                            "title" => $title,
                            "checked" => false,
                            "type" => 4,
                            "oldname" => $name,
                            "gfsforever" => $gfsforever,
                            "nocheck" => $dataflag ? true : false,
                            "point_uuid" => $d['timepoint_uuid'],
                            "depend_uuid" => $d['depend_point_uuid'],
                            "agent_name" => $d['agent_name'],
                            "agent_ip" => $d['agent_ip'],
                            "agent_uuid" => $d['agent_uuid'],
                            "task_name" => $d['task_name'],
                            "star" => intval($d['importance_flag']) == $flag['FLAG']['SET'],
                            "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                            "mode" => intval($d['backup_mode']),
                            "timepoint_uuid" => $d['timepoint_uuid'],
                            "taskuuid" => $d['task_uuid'],
                            "nodeuuid" => $nodeUuid,
                            "chkDisabled" => $dataflag ? true : false,
                            "storagename" => $d['storage_nickname'],
                            'timepoint' => $this->parseDate($d['timepoint']),
                            "nodename" => $nodeHandler->getNodeName($nodeUuid),
                            "encrypted_flag" => $encrypted_flag,
                            "ostype" => "linux",
                            "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                            "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1
                        );
                    }
                }

                continue;
            }
        }

        return $node;
    }

    /**
     * 递归查询增量备份时间点
     *
     * @param [type] $depend_point_uuid
     * @return void
     */
    private function getSearchIncTimepoint($depend_point_uuid)
    {
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid
            FROM bd_backup_timepoint bbt
            JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            WHERE bbt.timepoint_uuid = ? 
                  AND bbt.available_flag = 1 
                  AND bbt.import_flag = 2
            UNION ALL
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid
            FROM bd_backup_timepoint bbt
            JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            JOIN cte ON bbt.depend_point_uuid = cte.timepoint_uuid
            WHERE bbt.available_flag = 1 
                  AND bbt.import_flag = 2
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
     * 添加备注
     * @param $params
     * @return bool
     */
    public function remarkTimePoint($params)
    {
        $remark = $params['remark'];
        $timepointuuid = $params['time_point_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_COMMENT';
        $operate = (new PFOpcode())->getOpcodeDes($opName);
        $msg = json_encode(array('timepoint_uuid' => $timepointuuid, 'remarks' => $remark));

        return $this->service()->opUnifyPfMsg($opName, $msg, $operate);
    }

    /**
     * 设置永久标记
     * @param $params
     * @return
     */
    public function addStar($params)
    {
        $pointUUID = $params['time_point_uuid'];
        $this->paramsCheck($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_MAKR';
        $msg = json_encode(array('timepoint_uuids' => $msg));
        $mbResult = $this->mbPFMsg($opName, $msg);

        return $mbResult;
    }

    /**
     * 取消永久标记
     * @param $params
     * @return
     */
    public function deleteStar($params)
    {
        $time_point_uuid = $params['time_point_uuid'];
        $this->paramsCheck($time_point_uuid);
        $msg = array($time_point_uuid);
        $opName = 'BD_BACKUP_POINT_OP_UNMARK';
        $msg = json_encode(array('timepoint_uuids' => $msg));
        $mbResult = $this->mbPFMsg($opName, $msg);

        return $mbResult;
    }

    /**
     * 批量删除备份时间点
     * @param array $params
     * @return void
     */
    public function deleteSelectedTimePoints(array $params)
    {
        $pointList = $params['point_list'];
        $agentList = $params['agent_list'];

        if (!empty($agentList)) {
            foreach ($agentList as $agent) {
                $selectTimePoints = $this->getTimePointByObs($agent, $params['storage_uuid']);
                $pointList = array_merge($pointList, $selectTimePoints);
            }
        }

        $pointList = v1_array_sort($pointList, 'node_uuid', '', 0, -1);
        $info = $nodeuuids = $timepointuuids = $timepointuuid = array();

        foreach ($pointList as $d) {
            if (!in_array($d['node_uuid'], $nodeuuids)) {
                $nodeuuids[] = $d['node_uuid'];
                $info[] = array(
                    'node_uuid' => $d['node_uuid'],
                    'type' => $d['type']
                );
                if (!empty($timepointuuid)) {
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }
                $timepointuuid[] = $d['timepoint_uuid'];
            } else {
                $timepointuuid[] = $d['timepoint_uuid'];
            }
        }

        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $user = xphp_get_user_info();

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $user['permissionArr']) && in_array('global_read', $user['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chklist = $this->dbSelect(
                "select task_name from bd_backup_timepoint where user_uuid != ?
                        and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')",
                [$user['userUuid']]
            );
            if (!empty($chklist)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chklist, 'task_name')));
                return $this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'));
            }
        }

        $timepointuuids[] = $timepointuuid;
        $this->checkTimepointStorage($timepointuuids[0]);
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息

        //检测是否在任务中  在任务就直接返回
        $uuidList = array();
        foreach ($timepointuuids as $d) {
            $uuidList = array_merge($uuidList, $d);
        }

        // 日志信息
        $details = '';
        $i = 0;
        foreach ($uuidList as $u) {
            // 时间点信息
            $i++;
            $details .= $this->getPointDetails($i, $u);
        }

        $countPoint = 0;
        $nodeHandler = new Node();
        for ($i = 0; $i < $nodeuuidsCount; $i++) {
            $msg = $timepointuuids[$i];
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            if (empty($nodeuuids[$i])) {
                $nodeuuids[$i] = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointuuids[$i][0]);
            }
            //检查时间点是否有恢复任务正在使用
            $this->checkRecoveryPoint($nodeuuids[$i], $timepointuuids[$i]);
            $msg = json_encode($msg);
            $mbResult = $this->mbFSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, xphp_get_lang('UI_PLATFORM_OBS'));
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        } else {
            $this->systemLog(
                'SYSTEM_LOG_DELETE_BATCH_TIMEPOINT',
                $descriptionParam,
                xphp_get_config('log', 'LOGLEVEL')['ERROR'],
                $mbResult['errorCode']
            );
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取时间点信息
     * @param mixed $index
     * @param mixed $pointUUID
     * @return string
     */
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $sql = "SELECT 
                    bbt.timepoint, bbt.task_name, bbt.backup_mode, fbt.agent_name, fbt.agent_ip 
                FROM 
                    bd_backup_timepoint bbt, fs_backup_timepoint fbt 
                WHERE 
                    bbt.timepoint_uuid = ? AND fbt.fs_timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp_get_lang('UI_PUBLIC_TASK_RNAME') . "：" . $data[0]['task_name'] . "，" . xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . "：" . xphp_get_lang('WEB_PLATFORM_DES_FS') . "，" . $timepointDes . "，" . xphp_get_lang('UI_AGENT_HOST_NAME') . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = xphp_get_lang('UI_PUBLIC_TASK_RNAME') . "：" . $data[0]['task_name'] . "，" . xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . "：" . xphp_get_lang('WEB_PLATFORM_DES_FS') . "，" . $timepointDes . "，" . xphp_get_lang('UI_AGENT_HOST_NAME') . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
    }

    /**
     * 获取删除的对象存储对应时间点信息--对象存储备份数据
     * 根据类型和任务筛选
     * @param array  $fsinfo      info
     * @param string $storage_uuid 存储uuid
     * @return array
     */
    private function getTimePointByObs($fsinfo, $storage_uuid = ''): array
    {
        $info = array();

        $sql = "select distinct bbt.timepoint_uuid
            from bd_backup_timepoint bbt, bd_storage_resource bsr, fs_backup_timepoint fbt
            where bbt.timepoint_uuid = fbt.fs_timepoint_uuid
              and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";

        $params = array($fsinfo['job_uuid'], $fsinfo['agent_uuid']);

        if (!empty($storage_uuid)) {
            $sql .= " and bbt.storage_uuid = bsr.storage_uuid and bbt.storage_uuid = ?";
            $params = array_merge($params, array($storage_uuid));
        }

        // if (!empty($nodeUUID)) {
        //     $sql .= ' and bbt.storage_uuid = bsr.storage_uuid and bsr.node_uuid = ?';
        //     $params = array_merge($params, array($nodeUUID));
        // }

        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);

        foreach ($data as $d) {
            $info[] = array(
                'timepoint_uuid' => $d['timepoint_uuid'],
                'node_uuid' => $fsinfo['node_uuid'],
                'type' => $fsinfo['type']
            );
        }

        return $info;
    }

    /**
     * 检测恢复任务正在使用的备份点不能被删除
     * @param $nodeuuid  Node
     * @param $pointList list
     * @return void
     */
    private function checkRecoveryPoint($nodeuuid, $pointList)
    {
        $sql = "select bt.id from bd_task bt, fs_path_list fpl
                where fpl.recovery_timepoint_uuid = ? and fpl.task_uuid = bt.task_uuid
                  and bt.task_status = 2 and bt.node_uuid = ?";
        foreach ($pointList as $pointuuid) {
            $params = array($pointuuid, $nodeuuid);
            $data = $this->dbSelect($sql, $params);
            if (!empty($data)) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_FILE_DELETE_TIME_POINT_ERROR'), xphp_get_lang('WEB_FILE_DELETE_TIME_POINT_ERROR_TIPS'), 'warning'));
            }
        }
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
     * 检测时间点是否在磁带上，如果再次带上则退出不允许删除
     * @param mixed $timepointList
     * @return void
     */
    private function checkTimepointStorage($timepointList)
    {
        $timeDes = "'" . implode("','", $timepointList) . "'";
        $storage_type = xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'];  //磁带
        $sql = "    SELECT 
                        bbt.timepoint_uuid, bsr.storage_uuid, bsr.storage_type 
                    FROM 
                        bd_backup_timepoint bbt 
                    JOIN 
                        bd_storage_resource bsr 
                    ON 
                        bbt.storage_uuid = bsr.storage_uuid  
                    WHERE 
                        bbt.timepoint_uuid IN ($timeDes) AND bsr.storage_type = " . $storage_type;


        $result = $this->dbSelect($sql);

        if (count($result) > 0) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_M365_SERVER_DELETE_TIME_POINT'), xphp_get_lang('UI_TAPE_DELETE_BACKUP_POINT_TIPS'), 'warning'));
        }

        return;
    }
}