<?php

namespace app\v1\file\v0\logic;

use app\v1\common\logic\Data;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;

/**
 * note          文件 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileData extends Data
{
    /**
     * 获取文件时间点列表信息
     * @param array $params 参数
     * @return array
     */
    public function getFsTimepointGrid(array $params)
    {
        $storageUuid = $params['storage_uuid'];
        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $agentuuid = $params['agent_uuid'];
        $taskuuid = $params['job_uuid'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $search = $params['search'];
        $copyFlag = $params['copy_flag'];        //副本数据标志
        $archiveFlag = $params['archive_flag'];  //归档数据标志
        $localflag = intval($params['local_flag']); //本地标志
        $sql = 'select bsr.storage_type,bsr.node_uuid, bbt.real_node_uuid, bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.total_size, bbt.write_size, bbt.data_local_flag ,bbt.user_uuid, bbt.importance_flag, bbt.remarks,bbt.src_data_deleted_flag from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ? and fst.agent_uuid = ? and bbt.task_uuid = ?';
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ? and fst.agent_uuid = ? and bbt.task_uuid = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid);
        //区分异地本地
        if (!empty($localflag)) {
            $sql .= ' and bbt.data_local_flag = ?';
            $sqlParams = array_merge($sqlParams, array($localflag));
        }
        //如果带有搜索条件
        if (!empty($search)) {
            $search = json_decode($search,true);
            //如果开始时间和结束时间都有 则添加时间查询
            if (!empty($search['startTime']) && !empty($search['endTime'])) {
                $sql .= " and bbt.timepoint between ? and ? ";
                $sqlCount .= " and bbt.timepoint between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($search['startTime'], $search['endTime']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['startTime'], $search['endTime']));
            }
            //如果有类型 则添加类型
            if (!empty($search['timepointType'])) {
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($search['timepointType']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['timepointType']));
            }
            //如果有永久标记点 则添加永久标记的搜索
            if (!empty($search['forever'])) {
                $sql .= " and bbt.importance_flag = ? ";
                $sqlCount .= " and bbt.importance_flag = ? ";
                $sqlParams = array_merge($sqlParams, array($search['forever']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['forever']));
            }
        }
        //如果切换了节点
        if ($storageUuid) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlCount .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
        }
        if ($copyFlag || $archiveFlag) {
            if ($storageUuid && !empty($storageUuid)) {
                $sql .= " and bsr.storage_uuid = ? ";
                $sqlCount .= " and bsr.storage_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($storageUuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
            }
        }

        $sqlParams = array_merge($sqlParams, array($start, $length));
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
        $sql .= " limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $jobs = new JobInfo();
        $storage = new Storage();
        $records = array();
        $userUuidList = array_column($data, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";
        $sql = "select user_name, user_uuid from bd_user where user_uuid in ($userUuids)";
        $userData = $this->dbSelect($sql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }
        foreach ($data as $d) {
            $nodeuuid = $d['real_node_uuid'];
            if (empty($d['real_node_uuid'])) {
                $nodeuuid = $d['node_uuid'];
            }
            $mark = $jobs->pGetTimepointMark(v1_parse_flag_to_bool($d['weekly_flag']), v1_parse_flag_to_bool($d['monthly_flag']), v1_parse_flag_to_bool($d['yearly_flag']), v1_parse_flag_to_bool($d['importance_flag']));
            $remark = "";   //备注
            $remotFlag = !v1_parse_flag_to_bool(intval($d['data_local_flag']));
            $op = array(1, 2, 3);
            if ($remotFlag) { //如果是异地副本，不能设置星标
                $op = array(1, 2);
            }
            //1、增备和差备只能备注、2、时间点在合并中只能备注 3、磁带存储类型只能备注
            if ($d['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'] || $d['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['DIFFERENTIAL']
            || $d['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']
            || $d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']) {
                $op = array(1);
            }

            //添加备注
            if (!empty($d['remarks'])) {
                //根据标记是否显示固定备注显示高度
                $top = "";
                if (!empty($mark)) {
                    $top = "top:-4px;";
                }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover"
                            data-placement="right" data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
            }
            $records[] = array(
                '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['timepoint']) . '</span><br>' . $mark . $remark,  //隐藏展示时间点uuid出来,方便运维
                $jobs->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                v1_calsize($d['total_size'], true),
                v1_calsize($d['write_size'], true),
                $storage->getStorageName($d['storage_uuid']),
                //所有者
                $userMapping[$d['user_uuid']],
                $op,
                array(
                    'uuid' => $d['timepoint_uuid'],
                    'backupmode' => $d['backup_mode'],
                    'agentuuid' => $agentuuid,
                    'taskuuid' => $taskuuid,
                    'remote_flag' => $remotFlag,
                    'weekly_flag' => v1_parse_flag_to_bool($d['weekly_flag']),
                    'monthly_flag' => v1_parse_flag_to_bool($d['monthly_flag']),
                    'yearly_flag' => v1_parse_flag_to_bool($d['yearly_flag']),
                    'importance_flag' => v1_parse_flag_to_bool($d['importance_flag']),
                    'remark' => $d['remarks'],
                    'nodeuuid' => $nodeuuid,
                    'src_data_deleted_flag' => $d['src_data_deleted_flag'],
                ),
            );
        }
        return [
            'total' => $dataCount[0]['total'],
            'rows' => $records
        ];
    }

    /**
     * 搜索文件时间点
     * @param array $params 参数
     * @return array
     * 2024-12-04更新
     */
    public function searchFsTimepoint(array $params): array
    {
        $search = $params['search'];
        $forever = $params['forever'];
        $storageUuid = $params['storage'];
        $dataFlag = $params['dataFlag'];
        $chkDisabled = true;
        $sql = "select bbt.deleted_flag,bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag,
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail 
  from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
  where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and bbt.deleted_flag = 2 and 
         bbt.module_type = ? and bbt.sub_module_type = " . xphp_get_config('module', 'SUBMODULE_TYPE')['FS'] . " and bbt.data_local_flag = 1";
         if (empty(xphp_get_user_info()['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . xphp_get_user_info()['userUuid'] . "'";
        }
         //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        if ($forever) {
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }
        if (!empty($search)) {
            $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        }
        $sqlParams = array(xphp_get_config('module', 'MODULE_TYPE')['FS']);
        $taskType = xphp_get_config('task', 'TASKTYPE');
        if (!$dataFlag) { //恢复页面
            $chkDisabled = false;
            $sql .= " and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
        } else {
            $sql .= " and bbt.task_type = {$taskType['BACKUP']}";
        }
        if (!empty($params['recovery_range'])) {
            $sql .= ' and bbt.timepoint between ? and ?';
            $sqlParams = array_merge($sqlParams, array($params['startTime'], $params['endTime']));
        }
        //权限
        $authUser = $_SESSION['authUser']['fileprotect_look'] ?? [];
        if ($authUser) {
            $userUuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
            $userUuids = "('" . implode("','", $userUuidArr) . "')";
            $sql .= " and bbt.user_uuid IN $userUuids ";
        } else {
            $sql .= " and bbt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(xphp_get_user_info()['userUuid']));
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
            $pointData = $this->dbSelect($sql, array_merge($sqlParams,array($storageUuid)));
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql, $sqlParams);
        }
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else { //如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
                // $unfullList[] = $point;
                if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['DIFFERENTIAL']) { //差异
                    $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid,bsr.storage_type, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag,
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, fbt.detail as ft_detail 
                    from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                     bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
                     bbt.module_type = 3 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=? order by fbt.agent_uuid, bbt.timepoint";
                    $data = $this->dbSelect($sqlfull, array($point['depend_point_uuid']));
                    if (!in_array($data[0]['timepoint_uuid'], $fulluuidList)) {
                        $pointData[] = $data[0]; //完备点
                    }
                } else { //增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                    if (!in_array($fullpoint['timepoint_uuid'], $fulluuidList)) {
                        $fulluuidList[] = $fullpoint['timepoint_uuid']; //避免搜出重复完备点
                        $pointData[] = $fullpoint;
                    }
                    $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }
        $node = array();
        $jobs = new JobInfo();
        $nodeHandler = new Node();
        foreach ($pointData as $d) {
            $detail = json_decode($d['detail'], true);
            $ftDetail = json_decode($d['ft_detail'], true);
            $name = $this->parseDate($d['timepoint']) . " (" . $jobs->getTimepointTypeDes($d['backup_mode']) . ")";
            $title = $name;
            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }

            $mark = "";
            if ($d["src_data_deleted_flag"] == 1 && empty($params['recovery_range'])) { //归档开启 不是恢复页面的搜索才显示归档
                $mark = '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')' . $mark;
            }
            //添加GFS标识
            $mark = $mark . $jobs->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            $gfsforever = $mark;
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fullTimepoint = $d['timepoint_uuid'];
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $d['agent_uuid'] . "_" . $d['task_uuid'],
                    "name" => $name . $mark,
                    "title" => $name,
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
                    "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                    "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "timepointuuid" => $d['timepoint_uuid'],
                    "taskuuid" => $d['task_uuid'],
                    "nodeuuid" => $nodeUuid,
                    'storagename' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    "nodename" => $nodeHandler->getNodeName($d['real_node_uuid']),
                    "encrypted_flag" => $encrypted_flag,
                    "ostype" => $ftDetail['os_type'],
                    "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                    "storage_type" => $d['storage_type'],
                );
                $incList = $this->getSearchIncTimepoint($d['timepoint_uuid']);
                if (!empty($incList)) {
                    foreach ($incList as $d) {
                        if ($d['timepoint_uuid'] == $fullTimepoint) {
                            continue;
                        }
                        $detail = json_decode($d['detail'], true);
                        $ftDetail = json_decode($d['ft_detail'], true);
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
                            "name" => $name . $mark,
                            "title" => $name,
                            "checked" => false,
                            "type" => 4,
                            "oldname" => $name,
                            "gfsforever" => $gfsforever,
                            "nocheck" => $dataFlag,
                            "point_uuid" => $d['timepoint_uuid'],
                            "depend_uuid" => $d['depend_point_uuid'],
                            "agent_name" => $d['agent_name'],
                            "agent_ip" => $d['agent_ip'],
                            "agent_uuid" => $d['agent_uuid'],
                            "task_name" => $d['task_name'],
                            "star" => intval($d['importance_flag']) == xphp_get_config('app', 'FLAG')['SET'],
                            "icon" => $jobs->getTimepointIcon($d['backup_mode']),
                            "mode" => intval($d['backup_mode']),
                            "timepointuuid" => $d['timepoint_uuid'],
                            "taskuuid" => $d['task_uuid'],
                            "nodeuuid" => $nodeUuid,
                            "chkDisabled" => $chkDisabled,
                            "storagename" => $d['storage_nickname'],
                            'timepoint' => $this->parseDate($d['timepoint']),
                            "nodename" => $nodeHandler->getNodeName($nodeUuid),
                            "encrypted_flag" => $encrypted_flag,
                            "ostype" => $ftDetail['os_type'],
                            "password_auto_flag" => intval($detail['password_auto_flag']) == 1 ? true : false,
                            "storage_type" => $d['storage_type'],
                        );
                    }
                }
                continue;
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
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,fbt.detail as ft_detail,
                   bsr.node_uuid,bsr.storage_type,bsr.storage_nickname 
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
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,fbt.detail as ft_detail,
                   bsr.node_uuid,bsr.storage_type,bsr.storage_nickname 
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
     * 删除备份时间点单个
     * @param array $params 参数
     *                      storageHandler deleteFSImportData调用
     * @return string
     */
    public function deleteTimepoint(array $params)
    {
        $pointUUID = $params['timepoint_uuid'];
        $taskuuid = $params['job_uuid'];
        $agentuuid = $params['agent_uuid'];
        $sql = "select timepoint,timepoint_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID[0]));
        //检测时间点是否在磁带上
        $this->checkTimepointStorage([$data[0]['timepoint_uuid']]);
        $this->paramsCheck($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = new PFOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $authUser = $_SESSION['authUser']['fileprotect_operate'] ?? [];
            if (!empty($authUser)) {
                $authUserStr = "'" . implode("','", $authUser) . "'";
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid not in 
                   ({$authUserStr}) and timepoint_uuid = ?", [$pointUUID]);
            } else {
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [Xphp::$_user['useruuid'], $pointUUID]);
            }
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'error'));
            }
        }

        $msg = json_encode(array('timepoint_uuids' => $pointUUID));
        $nodeHandler = new Node();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($data[0]['timepoint']);
        //返回结果到UI
        if ($result) {
            //             $this->systemLog('SYSTEM_LOG_DELETE_FILE_ONE_TIMEPOINT', $descriptionParam);
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and fbt.agent_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                    ";
            $flag = xphp_get_config('app', 'FLAG');
            $dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid, $pointUUID));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count" => intval($count), "id" => $pointUUID));
        } else {
            //             $this->systemLog('SYSTEM_LOG_DELETE_FILE_ONE_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除批量备份时间点批量
     * @param array $params 二维数组
     *                      如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     * @return string
     */
    public function deleteSelectTimepoint(array $params)
    {
        $pointList = $params['point_list'];
        $agentList = $params['agent_list'];
        $selectTimepoints = array();
        if (!empty($agentList)) {
            foreach ($agentList as $agent) {
                $selectTimepoints = $this->getTimepointByFS($agent, $params['storage_uuid']);
                $pointList = array_merge($pointList, $selectTimepoints);
            }
        }
        $pointList = v1_array_sort($pointList, 'node_uuid', '', 0, -1);
        $info = array();
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach ($pointList as $d) {
            $i++;
            if (!in_array($d['node_uuid'], $nodeuuids)) {
                $nodeuuids[] = $d['node_uuid'];
                $info[] = array(
                    "node_uuid" => $d['node_uuid'],
                    "type" => $d['type']
                );
                if (!empty($timepointuuid)) {
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }

                $timepointuuid[] = $d['timepoint_uuid'];
            } else {
                $timepointuuid[] = $d['timepoint_uuid'];
            }
            // 时间点信息
            $details .= $this->getPointDetails($i, $d['timepoint_uuid']);
        }

        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'error'));
            }
        }

        $timepointuuids[] = $timepointuuid;
        //检测时间点是否在磁带上
        $this->checkTimepointStorage($timepointuuids[0]);
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        //检测是否在任务中  在任务就直接返回
        $uuidList = array();
        foreach ($timepointuuids as $d) {
            $uuidList = array_merge($uuidList, $d);
        }
        // $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
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
            $this->checkRecoveryPoint($nodeuuids[$i], $timepointuuids[$i], 'fileprotect_operate');
            $msg = json_encode($msg);
            $mbResult = $this->mbFSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, xphp_get_lang('WEB_PLATFORM_DES_FS'));
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        } else {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam, xphp_get_config('log')['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = xphp_get_desc('Pf', 'TASKTYPEDES');
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, fbt.agent_name, fbt.agent_ip from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = ? and fbt.fs_timepoint_uuid = bbt.timepoint_uuid";
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
     * 获取文件任务详情信息
     * @param array $params 参数
     * @return array
     */
    public function getDetailsFs(array $params)
    {

        $taskUUID = $params['job_uuid'];
        $sql = "select distinct ba.ip, ba.hostname,  ba.detail,ba.agent_uuid,ba.agent_name as nickname, 
        bt.task_name, bt.task_type,bt.task_status as fs_task_status, bt.module_type,   
        fpl.recovery_timepoint_uuid as sour_timepointuuid,fpl.backup_mode,fpl.new_root_path,  
        fbt.agent_name, fbt.agent_ip, fbt.detail as fbtdetail,fbt.agent_uuid as uuid,ft.file_archive_flag,
        btal.task_status,btal.task_uuid,btal.detail from bd_task bt left join fs_task ft on
        bt.task_uuid = ft.task_uuid left join fs_path_list fpl on
        bt.task_uuid = fpl.task_uuid left join bd_task_agent_list btal on
        fpl.task_uuid = btal.task_uuid left join bd_agent ba on 
        btal.agent_uuid = ba.agent_uuid left join fs_backup_timepoint fbt on
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid where bt.task_uuid = ?
        group by ba.agent_uuid order by btal.id limit ?,?";
        $data = $this->dbSelect($sql, array($taskUUID, $params['offset'], $params['limit']));
        $records = array();
        $i = 1;
        $taskType = xphp_get_desc('Pf', 'TASKTYPEDES');
        $backupModeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $agentTaskStatus = xphp_get_desc('Pf', 'AGENT_TASK_STATUS');

        // 客户端数量
        $sqlCount = "select count(agent_uuid) as total from bd_task_agent_list where task_uuid = ? ";
        $agentcount = $this->dbSelect($sqlCount, array($taskUUID));
        foreach ($data as $d) {
            //文件列表
            $list = array();
            if ($d['task_type'] == 2) {//恢复
                // 路径
                $sqlpath = "select path_name from fs_path_list where task_uuid = ?;";
                $sqlParamspath = array($d['task_uuid']);
                // 文件个数总个数等信息
                $sqlcount = "select total_fs_count,current_fs_count,current_dir_count
                    from fs_running_info fri where fri.task_uuid = ?;";

                $sourhost = $this->getAgentNameStr($d['uuid'], $d['agent_name'], $d['agent_ip']);
                //nas恢复到文件
                $fbtdetail = json_decode($d['fbtdetail'], true);
                if ($fbtdetail['nas_type'] != 0) {//源是nas  文件的nas_type为0 nas为6、7
                    $sourhost = $this->getNasNameStr($d['uuid'], $d['agent_name'], $d['agent_ip']);
                }
                // 若“别名”等于“IP”，则显示“主机名/IP”
                if ($d['ip'] == $d['nickname']) {
                    $desagent = $d['hostname'] . '(' . $d['ip'] . ')';
                } else {
                    $desagent = $d['nickname'] . '(' . $d['ip'] . ')';
                }
            } else {
                $sqlcount = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri
                                where fri.agent_uuid = ? and fri.task_uuid = ?;";
                $sqlpath = "select path_name from fs_path_list where agent_uuid = ? and task_uuid = ?;";
                $sqlParamspath = array($d['agent_uuid'],$d['task_uuid']);
                // 备份
                // 若“别名”等于“IP”，则显示“主机名/IP”
                if ($d['ip'] == $d['nickname']) {
                    $sourhost = $d['hostname'] . '(' . $d['ip'] . ')';
                } else {
                    $sourhost = $d['nickname'] . '(' . $d['ip'] . ')';
                }
            }

            $datapath = $this->dbSelect($sqlpath, $sqlParamspath);
            foreach ($datapath as $eachpath) {
                array_push($list, $eachpath['path_name']);
            }
            // 文件数量
            $datacount = $this->dbSelect($sqlcount, $sqlParamspath);
            // 通配符
            if ($d['fs_task_status'] != 2 || $d['task_status'] == 1) {
                // 任务不是运行中,客户端是等待状态都应显示--
                $ttype = $totalfscount = $currentfscount = $currentdircount = $agentstatus = $progress =
                    xphp_get_config('app', 'NULLSPACE');
            } else {
                if ($d['task_type'] == 2) {
                    // 恢复
                    $ttype = $taskType[$d['task_type']];
                } else {
                    $ttype =  $backupModeDes[$d['backup_mode']] . xphp_get_lang('UI_PLATFORM_BACKUP');
                }
                $totalfscount = $datacount[0]['total_fs_count'];
                $currentfscount = $datacount[0]['current_fs_count'];
                $currentdircount = $datacount[0]['current_dir_count'];
                $agentstatus = $agentTaskStatus[$d['task_status']];
                $progress = $this->getFsAgentProgress(
                    $taskUUID,
                    $d['agent_uuid'],
                    intval($d['fs_task_status']),
                    intval($d['task_status'])
                );
            }
            $wildcardInfo = json_decode($d['detail'], true);
            $records[] = array(
                'agent_uuid'    => $d['agent_uuid'],
                'num'   => $i++,
                'nickname' => $sourhost,
                'job_type'  => $taskType[$d['task_type']],
                'total_fs_count'    => $totalfscount,
                'current_fs_count'    => $currentfscount,
                'current_dir_count'    => $currentdircount,
                'progress'    => $progress,
                'agent_status'    => $agentstatus,
                'path_name'    => $list,
                'job_status'    => $d['task_status'],
                'popover'    => $agentTaskStatus[$d['task_status']],
                'des_agent' => $desagent ?? '',
                'wildcard_mode' => $wildcardInfo['wildcard_mode'],
                'wildcard' => $wildcardInfo['wildcard'],
                'fs_status' => $d['fs_task_status'],
                'path' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'file_archive_flag' => $d['file_archive_flag'],
                'ttype'    => $ttype,
            );
        }

        return  [
            'rows' => $records,
            'total' => $agentcount[0]['total']
        ];
    }

    /**
     * 文件任务详情-主机列表-对单个主机进行操作相关-删除
     * @param array $params 参数
     * @return string
     */
    public function deleteSelectFs($params)
    {
        //获取任务uuid
        $taskuuid = $params['job_uuid'];
        //获取主机列表
        $fsuuidlist = $params['fs_uuids'];

        //定义操作码
        $opName = 'OS_PRIVATE_TASK_OP_CODE_DELETE_OS_LIST';
        $msg = json_encode([ 'task_uuid' => $taskuuid, 'fs_uuid_list' => $fsuuidlist,]);

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskuuid);
        $mbResult = $this->service()->mbFSMsgs($nodeuuid, $opName, $msg, false, true);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 文件任务详情-主机列表-对单个主机进行操作相关-启动任务
     * @param array $params 参数
     * @return string
     */
    public function startSelectFs($params)
    {
        //获取备份模式
        $backup_mode = $params['backup_mode'];
        //获取任务uuid
        $task_uuid = $params['job_uuid'];
        //获取主机列表
        $fs_uuid_list = $params['fs_uuids'];
        //以下参数置位空
        $time_strategy_id = 0;
        $auto_start_flag = xphp_get_config('app', 'FLAG')['UNSET'];
        //定义操作码
        $opName = 'BD_TASK_OP_BACKUP_START';
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => $auto_start_flag,
            "fs_uuid_list" => $fs_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 下载跳过文件
     * @param array $params 参数
     * @return string
     */
    public function downLoadPassFile(array $params)
    {
        $nodeuuid =  $params['node_uuid'];
        $historyUuid = $params['history_uuid'];
        $agentUuid = $params['agent_uuid'];
        $filename = 'passfilelist';
        //获取文件的大小,如果获取失败,表示这个文件不存在或节点不可用,不能下载
        $opName = 'FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET';
        $operate = (new NodeOpcode())->getOpcodeDes($opName);
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        $msg = array(
            "history_uuid" => $historyUuid,
            "read_offset" => 0,
            "read_size" => $blockSize,
            "agent_uuid" => $agentUuid,
        );
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);
        if (!$mbResult['result']) {
            //获取文件大小失败
            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        } else {
            //获取成功
            $filesize = $mbResult['msg']['file_size'];
        }
        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        Header('Content-Disposition: attachment; filename=' . $filename . '.txt');
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                "history_uuid" => $historyUuid,
                "read_offset" => $i,
                "read_size" => $readLen,
                "agent_uuid" => $agentUuid,
            );
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);
            echo  $mbResult['msg']['data'];
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
    }

    /**
     * 获取删除的主机对应时间点信息--文件备份数据
     * 根据类型和任务筛选
     * @param array  $fsinfo      info
     * @param string $storageuuid uuid
     * @return array
     */
    private function getTimepointByFS($fsinfo, $storageuuid = '')
    {
        $info = array();
        $sql = "select distinct bbt.timepoint_uuid,  bsr.node_uuid,bbt.real_node_uuid from bd_backup_timepoint bbt,bd_storage_resource bsr,
        fs_backup_timepoint fbt where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['job_uuid'], $fsinfo['agent_uuid']);
        if (!empty($storageuuid)) {
            $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ? )";
            $params = array_merge($params, array($storageuuid, $storageuuid));
        }
        $data = $this->dbSelect($sql, $params);
        $sql .= " group by bbt.timepoint_uuid";
        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $info[] = array(
                'timepoint_uuid' => $d['timepoint_uuid'],
                'node_uuid' => $nodeUuid,
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
    public function checkRecoveryPoint($nodeuuid, $pointList, $userModule)
    {
        //        操作权限
        $timepointuuids = implode("','", $pointList);
        $userUuidData = $this->dbSelect("SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ('$timepointuuids')");
        $this->checkOperatePermission(array_column($userUuidData, 'user_uuid'), $userModule);
        $sql = "select bt.id from bd_task bt, fs_path_list fpl where fpl.recovery_timepoint_uuid = ? and fpl.task_uuid = bt.task_uuid and bt.task_status = 2 and bt.node_uuid = ?";
        foreach ($pointList as $pointuuid) {
            $params = array($pointuuid, $nodeuuid);
            $data = $this->dbSelect($sql, $params);
            if (!empty($data)) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_FILE_DELETE_TIME_POINT_ERROR'], Xphp::$_lang['WEB_FILE_DELETE_TIME_POINT_ERROR_TIPS'], 'warning'));
            }
        }
    }

    /**
     * 检查是否拥有文件数据的操作权限
     * @param array $userIdList 操作资源的拥有者uuid列表
     * @param string $userModule 模块对应的uuid属性值
     * @return void
     */
    private function checkOperatePermission(array $userIdList, string $userModule)
    {
        $authUser = $_SESSION['authUser'][$userModule] ?? [];
        $checkOperate = xphp_check_operate(xphp_get_user_info()['userUuid'], $userIdList, $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, xphp_get_lang('UI_ROLE_PERMISSION'), xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'warning'));
        }
    }

    /**
     * 检测时间点是否在磁带上,如果在磁带上则退出不让其删除并给出提示
     */
    public function checkTimepointStorage($timepointList)
    {
        $timeDes = "'" . implode("','", $timepointList) . "'";
        $storage_type = xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'];  //磁带
        $sql = "SELECT bbt.timepoint_uuid, bsr.storage_uuid, bsr.storage_type FROM bd_backup_timepoint bbt JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid  
        WHERE bbt.timepoint_uuid IN ($timeDes) AND bsr.storage_type = " . $storage_type;
        $result = $this->dbSelect($sql);
        if (count($result) > 0) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_M365_SERVER_DELETE_TIME_POINT'), xphp_get_lang('UI_TAPE_DELETE_BACKUP_POINT_TIPS'), 'warning'));
        }
        return;
    }
}
