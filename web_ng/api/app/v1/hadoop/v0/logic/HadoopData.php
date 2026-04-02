<?php

namespace app\v1\hadoop\v0\logic;

use app\v1\common\logic\Data;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;
/**
 * note          hadoop 备份数据 logic
 * @author       lilingyu@vinchin.com
 * @date         2023/11/20 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopData extends Data
{
    /**
     * 获取恢复的时间点表格
     */
    public function getRestoreTimepointTable($params)
    {

        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $clusteruuid = $params['cluster'];
        $taskuuid = $params['task'];
        $storageuuid = $params['storage_uuid'];
        //         编号   时间点 类型  数据大小    用户  备注  操作  星标
        $sql = 'select bbt.id, bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint,
        bbt.backup_mode, bbt.total_size, bbt.user_uuid,
        bbt.write_size, bbt.data_local_flag, bbt.importance_flag, bbt.remarks,bbt.src_data_deleted_flag, bsr.storage_type
        from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr
        where bbt.timepoint_uuid = fst.fs_timepoint_uuid
        and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ?
        and bbt.import_flag = ? and bbt.available_flag = ? and fst.agent_uuid = ? and bbt.task_uuid = ?';
        $sqlCount = "select count(bbt.id) as total 
            from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr
            where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid
                and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ?
                and fst.agent_uuid = ? and bbt.task_uuid = ?";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $clusteruuid, $taskuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $clusteruuid, $taskuuid);
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
        if (!empty($params['forever']) && $params['forever'] != 2) {
            $sql .= ' and bbt.importance_flag = ? ';
            $sqlCount .= ' and bbt.importance_flag = ? ';
            $sqlParams = array_merge($sqlParams, array($params['forever']));
            $sqlCountParams = array_merge($sqlCountParams, array($params['forever']));
        }
        //如果有存储 则添加存储查询
        if(!empty($storageuuid)){
            $sql .= ' and bsr.storage_uuid = ? ';
            $sqlCount .= ' and bsr.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
        }
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = array(
                'timepoint' => 'bbt.timepoint',
                'timpoint_type_desc' => 'bbt.backup_mode',
                'total_size' => 'bbt.total_size',
                'write_size' => 'bbt.write_size',
                'remarks' => 'bbt.remarks',
                'importance_flag' => 'bbt.importance_flag',
                'user_uuid' => 'bbt.user_uuid'
            );
            if (!empty($sortArr[$params['sort']]) && in_array($params['order'], ['sort', 'desc'])) {
                $sql .= " order by {$sortArr[$params['sort']]} {$params['order']} ";
            }
        }
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        //如果高级搜索不为空的话 且搜索出来的总条数小于等于当前请求的offset 从第一页显示 否则显示当前页
        //搜索条件为空的话  直接limit
        if (empty($params['start_time']) && empty($params['end_time']) && empty($params['timepoint_type']) && empty($params['forever'])) {
            if (is_numeric($start) && !empty($length)) {
                $sql .= " limit ?, ?";
                $sqlParams = array_merge($sqlParams, array($start, $length));
            }
        } else {
            //有高级搜索
            if ($dataCount[0]['total'] <= $length) {
                //从第一页开始显示
                if (is_numeric($start) && !empty($length)) {
                    $sql .= " limit ?, ?";
                    $sqlParams = array_merge($sqlParams, array(0, $length));
                }

            } else {
                //显示当前页
                if (is_numeric($start) && !empty($length)) {
                    $sql .= " limit ?, ?";
                    $sqlParams = array_merge($sqlParams, array($start, $length));
                }
            }
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array(
            'rows' => array(),
            'total' => $dataCount[0]['total'],

        );
        if (empty($data)) {
            return $info;
        }
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

        $storage = new Storage();
        $jobs = new JobInfo();
        $i =  1;
        foreach ($data as $d) {
            $mark = $this->pGetTimepointMark(
                false,
                false,
                false,
                v1_parse_flag_to_bool($d['importance_flag'])
            );
            $remark = ''; //备注
            //添加备注
            if (!empty($d['remarks'])) {
                if(!empty($mark)){
                    //根据标记是否显示固定备注显示高度
                    $remark .= '<a class="popovers remarktips" style="padding-top:0px;" data-container="body" data-trigger="hover" 
                    data-placement="right" data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '">' . '<i class="viconfont vicon-remark-info"></i></a>';
                }else{
                    //根据标记是否显示固定备注显示高度
                    $remark .= '<a class="popovers remarktips" style="padding-top:0px;" data-container="body" data-trigger="hover" 
                    data-placement="right" data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '">' . '<i class="viconfont vicon-remark-info" style="margin:0px;"></i></a>';
                }

            }
            $timepointDiv = '<span>' . $this->parseDate($d['timepoint']) . ' '. '</span>'. '<span>' . $mark . $remark . '</span>';

            $remotFlag = !v1_parse_flag_to_bool(intval($d['data_local_flag']));
            $op = array(1, 2, 3); //1 备注 2 删除 3设置标记
            if ($remotFlag) {//如果是异地副本，不能设置星标
                $op = array(1, 2);
            }
            $backupMode = xphp_get_config('task', 'BACKUP_MODE');
            //1、增备和差备只能备注、2、时间点在合并中只能备注 3、磁带存储类型只能备注
            if ($d['backup_mode'] == $backupMode['INCREMENTAL'] || $d['backup_mode'] == $backupMode['DIFFERENTIAL']
            || $d['archive_flag'] == $flag['SET']
            || $d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']) {
                $op = array(1);
            }

            //添加备注
            $info['rows'][] = array(
                //编号
                'no_id' => $i++,
                //获取id
                'id' => $d['id'],
                //获取时间点
                'timepoint' => $timepointDiv,
                //获取类型
                'timpoint_type_desc' => $jobs->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                'total_size' => v1_calsize($d['total_size'], true),
                'write_size' => v1_calsize($d['write_size'], true),
                'storage' => $this->getStorageName($d['storage_uuid'], $d['timepoint_uuid']),
                'remark' => $d['remarks'],
                'uuid' => $d['timepoint_uuid'],
                'importance_flag' => v1_parse_flag_to_bool($d['importance_flag']),
                'backup_mode' => $d['backup_mode'],
                'cluster_uuid' => $clusteruuid,
                'job_uuid' => $taskuuid,
                'remote_flag' => $remotFlag,
                'weekly_flag' => v1_parse_flag_to_bool($d['weekly_flag']),
                'monthly_flag' => v1_parse_flag_to_bool($d['monthly_flag']),
                'yearly_flag' => v1_parse_flag_to_bool($d['yearly_flag']),
                'cluster_uuid' => $clusteruuid,
                'src_data_deleted_flag' => $d['src_data_deleted_flag'],
                'timepoint_uuid' => $d['timepoint_uuid'],
                'storage_uuid' => $d['storage_uuid'],
                'storage_type' => $d['storage_type'],
                'description' => $mark,
                'op' => $op,
                'owner' => $userMapping[$d['user_uuid']]
            );
        }
        return $info;
    }

    /**
     * 给时间点添加备注
     */
    public function remarkTimepoint($params)
    {
        $remark = $params['remark'];
        $timepointuuid = $params['timepoint_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_COMMENT';
        $operate = (new PFOpcode())->getOpcodeDes($opName);
        $msg = json_encode(array('timepoint_uuid' => $timepointuuid, 'remarks' => $remark));
        return $this->service()->opUnifyPfMsg($opName, $msg, $operate);
    }

    /**
     * 给时间点添加星标
     */
    public function addStar($params)
    {
        $mbResult = $this->service()->addStar($params);
        return $mbResult;
    }

    /**
     * 给时间点删除星标
     */
    public function deleteStar($params)
    {
        $mbResult = $this->service()->deleteStar($params);
        return $mbResult;
    }

    /**
     * 删除时间点
     */
    public function deleteTimepoint($params)
    {

        $pointUUID = $params['timepoint_uuid'];
        $taskuuid = $params['job_uuid'];
        $clusteruuid = $params['cluster_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $user = xphp_get_user_info();
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $user['permissionArr']) && in_array('global_read', $user['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chklist = $this->dbSelect(
                "select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?",
                [$user['userUuid'], $pointUUID]
            );
            if (!empty($chklist)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chklist, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR')));
            }
        }
        $msg = json_encode(array('timepoint_uuids' => [$pointUUID]));
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->deleteTimepoint($nodeuuid, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.deleted_flag = ? and
                bbt.available_flag = ? and fbt.agent_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                ";
            $flag = xphp_get_config('app', 'FLAG');
            $dataCount = $this->dbSelect(
                $sqlCount,
                array(
                    $flag['UNSET'],
                    $flag['SET'],
                    $clusteruuid,
                    $taskuuid,
                    $pointUUID
                )
            );
            $count = $dataCount[0]['total'];
            return $this->muOpResult(
                $result,
                $operate,
                $msg,
                '',
                0,
                array("count" => intval($count), 'id' => $pointUUID)
            );
            // $mbResult['info']  = array("count" => intval($count), 'id' => $pointUUID);
            // return $mbResult;
        } else {
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
        $pointList = $params['pointList'];
        $agentList = $params['agentList'];

        if (!empty($agentList)) {
            foreach ($agentList as $agent) {
                $selectTimepoints = $this->getTimepointByFS($agent, '');
                $pointList = array_merge($pointList, $selectTimepoints);
            }
        }
        // 日志信息
        $details = '';
        $i = 0;
        $pointList = v1_array_sort($pointList, 'nodeuuid', '', 0, -1);
        $info = $nodeuuids = $timepointuuids = $timepointuuid = array();
        foreach ($pointList as $d) {
            $i++;
            if (!in_array($d['nodeuuid'], $nodeuuids)) {
                $nodeuuids[] = $d['nodeuuid'];
                $info[] = array(
                    'nodeuuid' => $d['nodeuuid'],
                    'type' => $d['type']
                );
                if (!empty($timepointuuid)) {
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }
                $timepointuuid[] = $d['timepointuuid'];
            } else {
                $timepointuuid[] = $d['timepointuuid'];
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
            $this->checkRecoveryPoint($nodeuuids[$i], $timepointuuids[$i]);
            $msg = json_encode($msg);
            $mbResult = $this->mbFSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details,$countPoint, xphp_get_lang('WEB_PLATFORM_DES_HADOOP'));
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
     * 获取删除的主机对应时间点信息--文件备份数据
     * 根据类型和任务筛选
     * @param array  $fsinfo      info
     * @param string $storageuuid uuid
     * @return array
     */
    private function getTimepointByFS($fsinfo, $storageuuid = '')
    {
        $info = array();
        $sql = "select distinct bbt.timepoint_uuid
            from bd_backup_timepoint bbt,bd_storage_resource bsr, fs_backup_timepoint fbt
            where bbt.timepoint_uuid = fbt.fs_timepoint_uuid
              and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['taskuuid'], $fsinfo['agentuuid']);
        if (!empty($storageuuid)) {
            $sql .= ' and bbt.storage_uuid = bsr.storage_uuid and bsr.node_uuid = ?';
            $params = array_merge($params, array($storageuuid));
        }
        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $fsinfo['nodeuuid'],
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
     * 搜索文件时间点
     */
    // public function searchTimepoint($params)
    // {
    //     $search = $params['search'];
    //     $forever = $params['forever'];
    //     $storage_uuid = $params['storage_uuid'];
    //     $dataflag = $params['data_flag'];
    //     $taskType = xphp_get_config('task', 'TASKTYPE');
    //     $sql = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bsr.storage_type, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
    //     fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name,
    //     bsi.virus_scan_status,bsi.integrity_check_status,
    //     bbt.merge_status,bbt.operation_status 
    //     from bd_backup_timepoint bbt 
    //     left join fs_backup_timepoint fbt on bbt.timepoint_uuid = fbt.fs_timepoint_uuid
    //     left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid
    //     left join bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid  = bsi.timepoint_uuid 
    //     where  bbt.available_flag = 1 and bbt.deleted_flag = 2 and bbt.import_flag = 2 and 
    //     bbt.module_type = 3 and bbt.sub_module_type = 3 and bbt.data_local_flag = 1 ";

    //      $userInfo = xphp_get_user_info();
    //      // 非租户用户不能查看租户的资源
    //      if (empty($userInfo['tenantuuid'])) {
    //          $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
    //          $tenantUuids = array_column($tenantUuids,'user_uuid');
    //          $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
    //          $sql .= " and bbt.user_uuid not in $tenantUuids ";
    //      } else {
    //          $sql .= " and bbt.user_uuid = '" . xphp_get_user_info()['userUuid'] . "'";
    //      }
         
    //     //筛选出每个增备点和差异点对应PID
    //     $fulluuidList = array();
    //     if ($forever) {
    //         $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
    //     }
    //     if (!empty($search)) {
    //         $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
    //     }
    //     if (!$dataflag) { //恢复页面
    //         $sql .= " and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
    //     } else { // 恢复页面
    //         $sql .= " and bbt.task_type = {$taskType['BACKUP']}";
    //     }
    //     if (!empty($params['recovery_range'])) {
    //         $sql .= " and bbt.timepoint between '". $params['startTime'] . "' and '" . $params['endTime'] . "' ";
    //     }
    //     //租户管理员特殊处理数据显示
    //     $tenantHandler = new Index();
    //     $tenantMangerFlag = (new User())->pCheckTenantManager();
    //     $user = xphp_get_user_info();
    //     $userUUID = $user['userUuid'];
    //     //获取租户管理员是否可以控制所有备份数据标志
    //     if (!empty($user['tenantuuid'])) {
    //         $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
    //         $allManageFlag = $settings['common']['datamanage'];
    //     }
    //     // 如果是有全局观察者权限，那么显示所有的数据
    //     if (!(in_array('global_read', $user['permissionArr']) || in_array('global_write', $user['permissionArr']))) {
    //         // 没有全局观察者权限
    //         //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
    //         if ($tenantMangerFlag && $allManageFlag) {
    //             $userList = $tenantHandler->getTenantAllUser($user['tenantuuid']);
    //             $userListDes = implode("','", $userList);
    //             $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
    //         } else {
    //             $sql .= ' and bbt.user_uuid = "'. $userUUID .'" ';
    //         }
    //     }
    //     if($dataflag){
    //         $sql .= ' and bbt.copy_flag = 2 ';
    //     }
    //     $startMicrotime = microtime(true);
    //     if (!empty($storage_uuid)) {
    //         $sql .= " and bbt.storage_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
    //         $pointData = $this->dbSelect($sql, array($storage_uuid));
    //     } else {
    //         $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
    //         $pointData = $this->dbSelect($sql);
    //     }
    //     $backupMode = xphp_get_config('task', 'BACKUP_MODE');
    //     foreach ($pointData as $key => $point) {
    //         if ($point['backup_mode'] == $backupMode['FULL']) {
    //             $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
    //         } else {//如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
    //             if ($point['backup_mode'] == $backupMode['DIFFERENTIAL']) {//差异
    //                 $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
    //                 fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name
    //                 from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
    //                 where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
    //                 bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
    //                 bbt.module_type = 3 and bbt.sub_module_type = 3 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=? order by fbt.agent_uuid, bbt.timepoint";
    //                 $data = $this->dbSelect($sqlfull, array($point['depend_point_uuid']));
    //                 if (!in_array($data[0]['timepoint_uuid'], $fulluuidList)) {
    //                     $pointData[] = $data[0];//完备点
    //                 }
    //             } else {//增量
    //                 $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
    //                 if (!in_array($fullpoint['timepoint_uuid'], $fulluuidList)) {
    //                     $fulluuidList[] = $fullpoint['timepoint_uuid'];//避免搜出重复完备点
    //                     $pointData[] = $fullpoint;
    //                 }
    //                 $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
    //             }
    //         }
    //     }
    //     $node = array();
    //     $flag = xphp_get_config('app', 'FLAG');
    //     $nodeHandler = new Node();
    //     $pfdes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
    //     //在每一个描述后面加上时间点这几个字
    //     foreach($pfdes as $key => $value){
    //         $pfdes[$key] = $value. xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
    //     }
    //     foreach ($pointData as $d) {
    //         $detail = json_decode($d['detail'], true);   
    //         $name = $this->parseDate($d['timepoint']) . ' (' .$pfdes[$d['backup_mode']] . ')';
    //         $title = $name;
    //         if ($d['encrypted_flag'] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
    //             $name .= '<i class="fa fa-lock"></i>';
    //             $encryptedflag = true;
    //         } else {
    //             $encryptedflag = false;
    //         }

    //         //添加GFS标识
    //         $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
    //         $gfsforever = $mark;
    //         if (!$dataflag) {
    //             $mark = '';
    //         }
    //         // $chkDisabled = true;
    //         $pointMixedStatus = $this->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
    //         if (intval($d['backup_mode']) == $backupMode['FULL']) {
    //             $node[] = array(
    //                 'id' => $d['timepoint_uuid'],
    //                 'pId' => $d['agent_uuid'] . '_' . $d['task_uuid'],
    //                 'name' => $name . $mark,
    //                 'title' => $title,
    //                 'checked' => false,
    //                 'type' => 3,
    //                 'nocheck' => false,
    //                 'oldname' => $name,
    //                 'gfsforever' => $gfsforever,
    //                 'point_uuid' => $d['timepoint_uuid'],
    //                 'depend_uuid' => $d['depend_point_uuid'],
    //                 'agent_name' => $d['agent_name'],
    //                 'agent_ip' => $d['agent_ip'],
    //                 'agent_uuid' => $d['agent_uuid'],
    //                 'cluster_name' => $d['agent_name'],
    //                 'cluster_ip' => $d['agent_ip'],
    //                 'cluster_uuid' => $d['agent_uuid'],
    //                 'task_name' => $d['task_name'],
    //                 'star' => intval($d['importance_flag']) == $flag['SET'],
    //                 'icon' => $this->getTimepointIcon($d['backup_mode']),
    //                 'mode' => intval($d['backup_mode']),
    //                 'timepointuuid' => $d['timepoint_uuid'],
    //                 'taskuuid' => $d['taskuuid'],
    //                 'nodeuuid' => $d['node_uuid'],
    //                 'storagename' => $d['storage_nickname'],
    //                 'storagetype' => $d['storage_type'],
    //                 'timepoint' => $this->parseDate($d['timepoint']),
    //                 // 'node_name' => $nodeHandler->getNodeName($d['node_uuid']),
    //                 'encrypted_flag' => $encryptedflag,
    //                 'ostype' => $detail['os_type'],
    //                 'password_auto_flag' => intval($detail['password_auto_flag']) == 1,
    //                 "point_status" => $pointMixedStatus['status'],
    //                 "available_flag" => $pointMixedStatus['available_flag'],
    //             );
    //             continue;
    //         }
    //         $node[] = array(
    //             'id' => $d['timepoint_uuid'],
    //             'pId' => $d['depend_point_uuid'],
    //             'name' => $name . $mark,
    //             'title' => $title,
    //             'checked' => false,
    //             'type' => 4,
    //             'oldname' => $name,
    //             'gfsforever' => $gfsforever,
    //             'nocheck' => $dataflag,
    //             'point_uuid' => $d['timepoint_uuid'],
    //             'depend_uuid' => $d['depend_point_uuid'],
    //             'agent_name' => $d['agent_name'],
    //             'agent_ip' => $d['agent_ip'],
    //             'agent_uuid' => $d['agent_uuid'],
    //             'cluster_name' => $d['agent_name'],
    //             'cluster_ip' => $d['agent_ip'],
    //             'cluster_uuid' => $d['agent_uuid'],
    //             'task_name' => $d['task_name'],
    //             'star' => intval($d['importance_flag']) == $flag['SET'],
    //             'icon' => $this->getTimepointIcon($d['backup_mode']),
    //             'mode' => intval($d['backup_mode']),
    //             "timepointuuid" => $d['timepoint_uuid'],
    //             'taskuuid' => $d['taskuuid'],
    //             'nodeuuid' => $d['node_uuid'],
    //             'chkDisabled' => false,
    //             'storagename' => $d['storage_nickname'],
    //             'storagetype' => $d['storage_type'],
    //             'timepoint' => $this->parseDate($d['timepoint']),
    //             // 'nodename' => $nodeHandler->getNodeName($d['node_uuid']),
    //             'encrypted_flag' => $encryptedflag,
    //             'ostype' => $detail['os_type'],
    //             'password_auto_flag' => intval($detail['password_auto_flag']) == 1
    //         );
    //     }
    //     return $node;
    // }

    public function searchTimepoint($params)
    {
        $search = $params['search'];
        $forever = $params['forever'];
        $storage_uuid = $params['storage_uuid'];
        $dataflag = $params['data_flag'];
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $sql = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bsr.storage_type, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name,
        bsi.virus_scan_status,bsi.integrity_check_status,
        bbt.merge_status,bbt.operation_status 
        from bd_backup_timepoint bbt 
        left join fs_backup_timepoint fbt on bbt.timepoint_uuid = fbt.fs_timepoint_uuid
        left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid
        left join bd_backup_timepoint_safe_info bsi on bbt.timepoint_uuid  = bsi.timepoint_uuid 
        where  bbt.available_flag = 1 and bbt.deleted_flag = 2 and bbt.import_flag = 2 and 
        bbt.module_type = 3 and bbt.sub_module_type = 3 and bbt.data_local_flag = 1 ";

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
         
        //筛选出每个增备点和差异点对应PID
        if ($forever) {
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }
        if (!empty($search)) {
            $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        }
        if (!$dataflag) { //恢复页面
            $sql .= " and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
        } else { // 恢复页面
            $sql .= " and bbt.task_type = {$taskType['BACKUP']}";
        }
        if (!empty($params['recovery_range'])) {
            $sql .= " and bbt.timepoint between '". $params['startTime'] . "' and '" . $params['endTime'] . "' ";
        }
        //租户管理员特殊处理数据显示
        $tenantHandler = new Index();
        $tenantMangerFlag = (new User())->pCheckTenantManager();
        $user = xphp_get_user_info();
        $userUUID = $user['userUuid'];
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($user['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $user['permissionArr']) || in_array('global_write', $user['permissionArr']))) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($user['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                $sql .= ' and bbt.user_uuid = "'. $userUUID .'" ';
            }
        }
        if($dataflag){
            $sql .= ' and bbt.copy_flag = 2 ';
        }
        if (!empty($storage_uuid)) {
            $sql .= " and bbt.storage_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
            $pointData = $this->dbSelect($sql, array($storage_uuid));
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql);
        }
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');

        // === 批量预加载依赖的完备点（解决 N+1 问题 + 防止重复）===
        $existingUuids = [];
        foreach ($pointData as $point) {
            $existingUuids[$point['timepoint_uuid']] = true;
        }

        $needFullUuids = [];
        foreach ($pointData as $point) {
            if ($point['backup_mode'] != $backupMode['FULL'] && !empty($point['depend_point_uuid'])) {
                $depUuid = $point['depend_point_uuid'];
                if (!isset($existingUuids[$depUuid])) {
                    $needFullUuids[] = $depUuid;
                }
            }
        }
        $needFullUuids = array_unique(array_filter($needFullUuids));

        if (!empty($needFullUuids)) {
            $placeholders = str_repeat('?,', count($needFullUuids) - 1) . '?';
            $sqlFull = "
                SELECT 
                    bbt.deleted_flag,
                    bsr.storage_nickname,
                    bsr.node_uuid,
                    bsr.storage_type,
                    bbt.task_uuid,
                    bbt.id,
                    bbt.timepoint_uuid,
                    bbt.depend_point_uuid,
                    UNIX_TIMESTAMP(bbt.timepoint) AS timepoint,
                    bbt.backup_mode,
                    bbt.importance_flag,
                    bbt.detail,
                    bbt.encrypted_flag,
                    bbt.remarks,
                    fbt.agent_uuid,
                    fbt.agent_name,
                    fbt.agent_ip,
                    fbt.task_name,
                    bsi.virus_scan_status,
                    bsi.integrity_check_status,
                    bbt.merge_status,
                    bbt.operation_status
                FROM bd_backup_timepoint bbt
                LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
                LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                LEFT JOIN bd_backup_timepoint_safe_info bsi ON bbt.timepoint_uuid = bsi.timepoint_uuid
                WHERE 
                    bbt.available_flag = 1 
                    AND bbt.deleted_flag = 2 
                    AND bbt.import_flag = 2 
                    AND bbt.module_type = 3 
                    AND bbt.sub_module_type = 3 
                    AND bbt.data_local_flag = 1
                    AND bbt.timepoint_uuid IN ($placeholders)
                ORDER BY fbt.agent_uuid, bbt.timepoint
            ";
            $extraPoints = $this->dbSelect($sqlFull, $needFullUuids);
            foreach ($extraPoints as $ep) {
                if (!isset($existingUuids[$ep['timepoint_uuid']])) {
                    $pointData[] = $ep;
                    $existingUuids[$ep['timepoint_uuid']] = true;
                }
            }
        }

        $pointMap = [];
        foreach ($pointData as $d) {
            $pointMap[$d['timepoint_uuid']] = $d;
        }

        $finalDependMap = [];
        foreach ($pointData as $d) {
            $uuid = $d['timepoint_uuid'];
            if ($d['backup_mode'] == $backupMode['FULL']) {
                $finalDependMap[$uuid] = $uuid; // 完备点自身
            } else {
                $current = $d['depend_point_uuid'];
                $visited = [];
                // 沿依赖链向上查找，直到找到完备点
                while (!empty($current) && isset($pointMap[$current]) && $pointMap[$current]['backup_mode'] != $backupMode['FULL']) {
                    if (isset($visited[$current])) break; // 防循环
                    $visited[$current] = true;
                    $current = $pointMap[$current]['depend_point_uuid'];
                }
                // 如果找到完备点
                if (!empty($current) && isset($pointMap[$current]) && $pointMap[$current]['backup_mode'] == $backupMode['FULL']) {
                    $finalDependMap[$uuid] = $current;
                } else {
                    // 降级：使用原始依赖（或自身）
                    $finalDependMap[$uuid] = $d['depend_point_uuid'] ?: $uuid;
                }
            }
        }

        // === 构建返回节点树 ===
        $node = [];
        $flag = xphp_get_config('app', 'FLAG');
        $pfdes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        //在每一个描述后面加上时间点这几个字
        foreach($pfdes as $key => $value){
            $pfdes[$key] = $value. xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
        }
        foreach ($pointData as $d) {
            $detail = !empty($d['detail']) ? json_decode($d['detail'], true) : [];
            $name = $this->parseDate($d['timepoint']) . ' (' .$pfdes[$d['backup_mode']] . ')';
            $title = $name;

            $encryptedflag = false;
            if ($d['encrypted_flag'] == 1 && isset($detail['password_auto_flag']) && $detail['password_auto_flag'] == 2) {
                $name .= '<i class="fa fa-lock"></i>';
                $encryptedflag = true;
            }

            // GFS 标识
            $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            $gfsforever = $mark;
            if (!$dataflag) {
                $mark = '';
            }

            // 状态计算
            $pointMixedStatus = $this->getTimePointStatus(
                $d['merge_status'],
                $d['operation_status'],
                $d['virus_scan_status'],
                $d['integrity_check_status']
            );

            if (intval($d['backup_mode']) == $backupMode['FULL']) {
                $node[] = [
                    'id' => $d['timepoint_uuid'],
                    'pId' => $d['agent_uuid'] . '_' . $d['task_uuid'],
                    'name' => $name . $mark,
                    'title' => $title,
                    'checked' => false,
                    'type' => 3,
                    'nocheck' => false,
                    'oldname' => $name,
                    'gfsforever' => $gfsforever,
                    'point_uuid' => $d['timepoint_uuid'],
                    'depend_uuid' => $d['depend_point_uuid'],
                    'agent_name' => $d['agent_name'],
                    'agent_ip' => $d['agent_ip'],
                    'agent_uuid' => $d['agent_uuid'],
                    'cluster_name' => $d['agent_name'],
                    'cluster_ip' => $d['agent_ip'],
                    'cluster_uuid' => $d['agent_uuid'],
                    'task_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'icon' => $this->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'timepointuuid' => $d['timepoint_uuid'],
                    'taskuuid' => $d['task_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    'storagename' => $d['storage_nickname'],
                    'storagetype' => $d['storage_type'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'encrypted_flag' => $encryptedflag,
                    'ostype' => $detail['os_type'] ?? '',
                    'password_auto_flag' => isset($detail['password_auto_flag']) && intval($detail['password_auto_flag']) == 1,
                    'point_status' => $pointMixedStatus['status'],
                    'available_flag' => $pointMixedStatus['available_flag'],
      
                ];
            } else {
                $node[] = [
                    'id' => $d['timepoint_uuid'],
                    'pId' => $finalDependMap[$d['timepoint_uuid']] ?? $d['depend_point_uuid'],
                    'name' => $name . $mark,
                    'title' => $title,
                    'checked' => false,
                    'type' => 4,
                    'oldname' => $name,
                    'gfsforever' => $gfsforever,
                    'nocheck' => $dataflag,
                    'point_uuid' => $d['timepoint_uuid'],
                    'depend_uuid' => $d['depend_point_uuid'],
                    'agent_name' => $d['agent_name'],
                    'agent_ip' => $d['agent_ip'],
                    'agent_uuid' => $d['agent_uuid'],
                    'cluster_name' => $d['agent_name'],
                    'cluster_ip' => $d['agent_ip'],
                    'cluster_uuid' => $d['agent_uuid'],
                    'task_name' => $d['task_name'],
                    'star' => intval($d['importance_flag']) == $flag['SET'],
                    'icon' => $this->getTimepointIcon($d['backup_mode']),
                    'mode' => intval($d['backup_mode']),
                    'timepointuuid' => $d['timepoint_uuid'],
                    'taskuuid' => $d['task_uuid'], // 修正字段名
                    'nodeuuid' => $d['node_uuid'],
                    'chkDisabled' => false,
                    'storagename' => $d['storage_nickname'],
                    'storagetype' => $d['storage_type'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    'encrypted_flag' => $encryptedflag,
                    'ostype' => $detail['os_type'] ?? '',
                    'password_auto_flag' => isset($detail['password_auto_flag']) && intval($detail['password_auto_flag']) == 1,
                ];
            }
        }

        return $node;

    }

    /**
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $taskNameDes = xphp_get_lang('UI_PUBLIC_TASK_RNAME');
        $moduleDes = xphp_get_lang('UI_PUBLIC_MODULE_TYPE');
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, fbt.agent_name, fbt.agent_ip from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = ? and fbt.fs_timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[intval($data[0]['backup_mode'])] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') . '：' .  $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . $taskNameDes . "：" . $data[0]['task_name'] . "，" .  $moduleDes . "：" . xphp_get_lang('WEB_PLATFORM_DES_HADOOP') . "，" . $timepointDes . "，" . xphp_get_lang('WEB_HADOOP_CLUSTER_NAME_DES') . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = $taskNameDes . "：" . $data[0]['task_name'] . "，" .  $moduleDes . "：" . xphp_get_lang('WEB_PLATFORM_DES_HADOOP') . "，" . $timepointDes . "，" . xphp_get_lang('WEB_HADOOP_CLUSTER_NAME_DES') . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
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

}