<?php

namespace app\v1\nas\v0\logic;

use app\v1\common\logic\Data;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\common\logic\JobInfo;

/**
 * note          NAS 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasData extends Data
{
    /**
     * 获取nas时间点列表信息
     * @param array $params 参数
     * @return array
     */
    public function getNasTimepointGrid(array $params): array
    {
        $nodeuuid = $params['node_uuid'];
        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $agentuuid = $params['agent_uuid'];
        $taskuuid = $params['job_uuid'];

        $copyFlag = $params['copy_flag'];        //副本数据标志
        $archiveFlag = $params['archive_flag'];  //归档数据标志
        $storageuuid = $params['storage_uuid'];
        $localflag = intval($params['local_flag']); //本地标志

        //         编号   时间点 类型  数据大小    用户  备注  操作  星标
        $sql = 'select bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint,
                        bbt.backup_mode, bbt.total_size, bbt.write_size, bbt.data_local_flag, bbt.importance_flag,
                        bbt.remarks,bbt.src_data_deleted_flag
                from bd_backup_timepoint bbt,fs_backup_timepoint fst, bd_storage_resource bsr
                where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid
                  and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ?
                  and bbt.task_uuid = ? and fst.agent_uuid = ?';
        $sqlCount = "select count(bbt.id) as total from
                                   bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr
                    where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid
                      and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ?
                      and bbt.task_uuid = ? and fst.agent_uuid = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $taskuuid, $agentuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $taskuuid, $agentuuid);
        //如果带有搜索条件
        //如果开始时间和结束时间都有 则添加时间查询
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $sql .= ' and bbt.timepoint between ? and ? ';
            $sqlCount .= ' and bbt.timepoint between ? and ? ';
            $sqlParams = array_merge($sqlParams, array($params['start_time'], $params['end_time']));
            $sqlCountParams = array_merge($sqlCountParams, array($params['start_time'], $params['end_time']));
        }
        //如果有类型 则添加类型
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

        //如果切换了节点
        if ($nodeuuid) {
            $sql .= ' and bsr.node_uuid = ? ';
            $sqlCount .= ' and bsr.node_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
        }
        if ($copyFlag || $archiveFlag) {
            if (!empty($storageuuid)) {
                $sql .= ' and bsr.storage_uuid = ? ';
                $sqlCount .= ' and bsr.storage_uuid = ? ';
                $sqlParams = array_merge($sqlParams, array($storageuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
            }
        }
        //区分异地还是本地
        if (!empty($localflag)) {
            $sql .= ' and bbt.data_local_flag = ? ';
            $sqlParams = array_merge($sqlParams, array($localflag));
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = array(
                'timepoint' => 'bbt.timepoint',
                'backup_mode' => 'bbt.backup_mode',
                'total_size' => 'bbt.total_size',
                'write_size' => 'bbt.write_size',
                'remarks' => 'bbt.remarks',
                'importance_flag' => 'bbt.importance_flag'
            );
            if (!empty($sortArr[$params['sort']]) && in_array($params['order'], ['sort', 'desc'])) {
                $sql .= " order by {$sortArr[$params['sort']]} {$params['order']} ";
            }
        }

        $sqlParams = array_merge($sqlParams, array($start, $length));
        $sql .= ' limit ? , ? ';

        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);

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
            //时间点在合并中，不让删除
            if ($d['archive_flag'] == $flag['SET']) {
                $op = array(1);
            }
            $backupMode = xphp_get_config('task', 'BACKUP_MODE');
            //增备和差备只能备注
            if (in_array($d['backup_mode'], [$backupMode['INCREMENTAL'], $backupMode['DIFFERENTIAL']])) {
                $op = array(1);
            }

            //添加备注
            $records[] = array(
                'uuid' => $d['timepoint_uuid'],
                'num' => $i++,
                'backup_mode' => $d['backup_mode'],
                'agent_uuid' => $agentuuid,
                'job_uuid' => $taskuuid,
                'remote_flag' => $remotFlag,
                'weekly_flag' => v1_parse_flag_to_bool($d['weekly_flag']),
                'monthly_flag' => v1_parse_flag_to_bool($d['monthly_flag']),
                'yearly_flag' => v1_parse_flag_to_bool($d['yearly_flag']),
                'importance_flag' => v1_parse_flag_to_bool($d['importance_flag']),
                'remark' => $d['remarks'],
                'node_uuid' => $nodeuuid,
                'src_data_deleted_flag' => $d['src_data_deleted_flag'],
                'timepoint_uuid' => $d['timepoint_uuid'],
                'storage_uuid' => $d['storage_uuid'],
                'description' => $mark,
                'timepoint' => $this->parseDate($d['timepoint']),
                'timpoint_type_desc' => $jobs->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                'total_size' => v1_calsize($d['total_size'], true),
                'write_size' => v1_calsize($d['write_size'], true),
                'storage_name' => $storage->getStorageName($d['storage_uuid']),
                'op' => $op,
            );
        }

        return [
            'total' => $dataCount[0]['total'],
            'rows' => $records
        ];
    }

    /**
     * 搜索nas时间点
     * @param array $params 参数
     * @return array
     */
    public function searchNasTimepoint(array $params): array
    {
        $search = $params['keyword'];
        $forever = $params['forever'];
        $nodeuuid = $params['node_uuid'];
        $sql = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bbt.task_uuid, bbt.id,
                       bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint,
                       bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
                        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name
                  from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                  where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
                         bbt.module_type = 11 and bbt.data_local_flag = 1";
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();

        if ($forever) {
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }
        if (!empty($search)) {
            $sql .= " and (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" .
                $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        }
        if (!empty($nodeuuid)) {
            $sql .= " and bsr.node_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
            $pointData = $this->dbSelect($sql, array($nodeuuid));
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql);
        }
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');
        $flag = xphp_get_config('app', 'FLAG');
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == $backupMode['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                // 如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
                if ($point['backup_mode'] == $backupMode['DIFFERENTIAL']) {
                    // 差异
                    $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bbt.task_uuid, bbt.id,
       bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode,
                                       bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
                                        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name
                    from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                     bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
                     bbt.module_type = 11 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=?
                    order by fbt.agent_uuid, bbt.timepoint";
                    $data = $this->dbSelect($sqlfull, array($point['depend_point_uuid']));
                    $pointData[] = $data[0];
                } else {
                    // 增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                    if (!in_array($fullpoint['timepoint_uuid'], $fulluuidList)) {
                        $fulluuidList[] = $fullpoint['timepoint_uuid'];//避免搜出重复完备点
                        $pointData[] = $fullpoint;
                    }
                    $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }

        $node = array();
        $nodeHandler = new Node();
        $jobInfo = new JobInfo();

        foreach ($pointData as $d) {
            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) .
                ' (' . $jobInfo->getTimepointTypeDes(intval($d['backup_mode'])) . ')';
            if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) {
                // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }

            //添加GFS标识
            $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            $gfsforever = $mark;
            $chkDisabled = true;
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == $backupMode['FULL']) {
                $node[] = array(
                    'id' => $d['timepoint_uuid'],
                    'pid' => $d['agent_uuid'] . '_' . $d['task_uuid'],
                    'name' => $name . $mark,
                    'checked' => false,
                    'type' => 3,
                    'nocheck' => false,
                    'oldname' => $name,
                    'gfsforever' => $gfsforever,
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'depend_point_uuid' => $d['depend_point_uuid'],
                    'agent_name' => $d['agent_name'],
                    'agent_ip' => $d['agent_ip'],
                    'agent_uuid' => $d['agent_uuid'],
                    'job_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'icon' => $this->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'job_uuid' => $d['taskuuid'],
                    'node_uuid' => $d['node_uuid'],
                    'storage_nickname' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'node_name' => $nodeHandler->getNodeName($d['node_uuid']),
                    'encrypted_flag' => $encryptedflag,
                    'os_type' => $detail['os_type'],
                    'password_auto_flag' => $detail['password_auto_flag'] == 1
                );
                $pid = $timepointuuid;
                continue;
            }
            $node[] = array(
                'id' => $d['timepoint_uuid'],
                'pid' => $d['depend_point_uuid'],
                'name' => $name . $mark,
                'checked' => false,
                'type' => 4,
                'oldname' => $name,
                'gfsforever' => $gfsforever,
                'nocheck' => false,
                'timepoint_uuid' => $d['timepoint_uuid'],
                'depend_point_uuid' => $d['depend_point_uuid'],
                'agent_name' => $d['agent_name'],
                'agent_ip' => $d['agent_ip'],
                'agent_uuid' => $d['agent_uuid'],
                'job_name' => $d['task_name'],
                'star' => intval($d['importance_flag']) == $flag['SET'],
                'icon' => $this->getTimepointIcon($d['backup_mode']),
                'mode' => intval($d['backup_mode']),
                'job_uuid' => $d['taskuuid'],
                'node_uuid' => $d['node_uuid'],
                'chk_disabled' => $chkDisabled,
                'storage_nickname' => $d['storage_nickname'],
                'timepoint' => $this->parseDate($d['timepoint']),
                'node_name' => $nodeHandler->getNodeName($d['node_uuid']),
                'encrypted_flag' => $encryptedflag,
                'os_type' => $detail['os_type'],
                'password_auto_flag' => $detail['password_auto_flag'] == 1
            );
        }

        return $node;
    }

    /**
     * 删除备份时间点 
     * @param array $params 参数
     *                      storageHandler deleteFSImportData调用
     * @return string
     */
    public function deleteTimepoint(array $params)
    {
        $pointUUID = $params['timepoint_uuid'];
        $taskuuid = $params['job_uuid'];
        $agentuuid = $params['agent_uuid'];

        $sql = "SELECT timepoint,timepoint_uuid FROM bd_backup_timepoint WHERE timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID[0]));

        //检测时间点是否在磁带上
        $this->checkTimepointStorage([$data[0]['timepoint_uuid']]);
        $this->paramsCheck($pointUUID);

        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [xphp_get_user_info()['userUuid'], $pointUUID]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'error'));
            }
        }

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            $authUser = $_SESSION['authUser']['nas_protect_operate'] ?? [];
            if (!empty($authUser)) {
                $authUserStr = "'" . implode("','", $authUser) . "'";
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid not in 
                   ({$authUserStr}) and timepoint_uuid = ?", [$pointUUID]);
            } else {
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [xphp_get_user_info()['userUuid'], $pointUUID]);
            }
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'error'));
            }
        }

        $msg = json_encode(array('timepoint_uuids' => $pointUUID));
        $nodeuuid = '';
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
     * 删除批量备份时间点
     * @param array $params 二维数组
     *                      如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     * @return string
     */
    public function deleteSelectTimepoint(array $params)
    {
        $pointList = $params['point_list'];
        $agentList = $params['agent_list'];

        if (!empty($agentList)) {
            foreach ($agentList as $agent) {
                $selectTimepoints = $this->getTimepointByNas($agent, $params['storage_uuid']);
                $pointList = array_merge($pointList, $selectTimepoints);
            }
        }

        $pointList = v1_array_sort($pointList, 'node_uuid', '', 0, -1);
        $info = $nodeuuids = $timepointuuids = $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach ($pointList as $d) {
            $i++;
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
            // 时间点信息
            $details .= $this->getPointDetails($i, $d['timepointuuid']);
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
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        //检测时间点是否在磁带上
        $this->checkTimepointStorage([$timepointuuids[0]]);
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
            // $this->checkRecoveryPoint($nodeuuids[$i], $timepointuuids[$i]);
            $msg = json_encode($msg);
            $mbResult = $this->mbFSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, xphp_get_lang('WEB_PLATFORM_DES_NAS'));
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
     * 获取删除的主机对应时间点信息--nas备份数据
     * @param array  $fsinfo      info
     * @param string $storageuuid 资源uuid
     * @return array
     */
    public function getTimepointByNas($fsinfo, $storageuuid)
    {
        $info = array();
        $sql = "select bbt.timepoint_uuid,bbt.storage_uuid
                from bd_backup_timepoint bbt,bd_storage_resource bsr, fs_backup_timepoint fbt
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ?
                  and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['job_uuid'], $fsinfo['agent_uuid']);
        if (!empty($storageuuid)) {
            $sql .= ' and bbt.real_node_uuid = ?';
            $params = array_merge($params, array($storageuuid));
        }
        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $info[] = array(
                'timepoint_uuid' => $d['timepoint_uuid'],
                'node_uuid' => $fsinfo['nodeuuid'],
                'type' => $fsinfo['type']
            );
        }
        return $info;
    }

    /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param string $nasuuid 时间点中的uuid
     * @param string $name    时间点中的主机名
     * @param string $ip      时间点中的ip
     * @return string name(ip)
     */
    private function getNasCopyNameStr(string $nasuuid, $name, $ip): string
    {
        $sql = "select nas_nickname, share_path, ip from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nasuuid));
        if (!empty($result)) {
            if ($result[0]['nas_nickname'] == $ip) {
                return $ip . '(' . $result[0]['share_path'] . ')';
            } else {
                return $ip . '(' . $result[0]['nas_nickname'] . ')';
            }
        } else {
            return $ip . '(' . $name . ')';
        }
    }

    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param string $uuid 时间点中的uuid
     * @param string $name 时间点中的主机名
     * @param string $ip   时间点中的ip
     * @return string name(ip)
     */
    private function getCopyNameStr($uuid, $name, $ip)
    {
        $sql = "select agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($uuid));
        if (!empty($result)) {
            if ($result[0]['agent_name'] == $result[0]['ip']) {
                return $result[0]['hostname'] . '(' . $result[0]['ip'] . ')';
            } else {
                return $result[0]['agent_name'] . '(' . $result[0]['ip'] . ')';
            }
        } else {
            return $name . '(' . $ip . ')';
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
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, fbt.agent_name, fbt.agent_ip from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = ? and fbt.fs_timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp_get_lang('UI_PUBLIC_TASK_RNAME') . "：" . $data[0]['task_name'] . "，" . xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . "：" . xphp_get_lang('WEB_PLATFORM_DES_NAS') . "，" . $timepointDes . "，" . xphp_get_lang('UI_STORAGE_SHARED_FOLDERS') . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = xphp_get_lang('UI_PUBLIC_TASK_RNAME') . "：" . $data[0]['task_name'] . "，" . xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . "：" . xphp_get_lang('WEB_PLATFORM_DES_NAS') . "，" . $timepointDes . "，" . xphp_get_lang('UI_STORAGE_SHARED_FOLDERS') . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
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