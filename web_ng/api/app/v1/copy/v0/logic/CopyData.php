<?php

namespace app\v1\copy\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\PfOpcode;

/**
 * note          副本 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class CopyData extends Base
{
    public function remarkTimePoint($params)
    {
        $remark = $params['remark'];
        $time_point_uuid = $params['time_point_uuid'];
        // 权限检查
        $userUuidData = $this->dbSelect("select user_uuid from bd_backup_timepoint where timepoint_uuid = ?", [$time_point_uuid]);
        $userArr = array_unique(array_column($userUuidData, 'user_uuid'));
        if (!xphp_check_operate('data_manager_operate', $userArr)) {
            // 没有操作权限
            return [];
        }
        $sql = "update bd_backup_timepoint set remarks = ? where timepoint_uuid = ?";
        $result = $this->dbExec($sql, array($remark, $time_point_uuid));
        return $result;
    }
    public function addStar($params)
    {
        $time_point_uuid = $params['time_point_uuid'];
        // 权限检查
        $userUuidData = $this->dbSelect("select user_uuid from bd_backup_timepoint where timepoint_uuid = ?", [$time_point_uuid]);
        $userArr = array_unique(array_column($userUuidData, 'user_uuid'));
        if (!xphp_check_operate('data_manager_operate', $userArr)) {
            // 没有操作权限
            return [];
        }
        $mbResult = $this->service()->addStar($params);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            $this->muOpResult(true, xphp_get_lang('UI_COPY_DATA_SET_FOREVER_MASK'), $msg);
        } else {
            $this->muOpResult(false,  xphp_get_lang('UI_COPY_DATA_SET_FOREVER_MASK'), $msg, '', $mbResult['errorCode']);
        }
    }
    public function deleteStar($params)
    {
        $time_point_uuid = $params['time_point_uuid'];
        // 权限检查
        $userUuidData = $this->dbSelect("select user_uuid from bd_backup_timepoint where timepoint_uuid = ?", [$time_point_uuid]);
        $userArr = array_unique(array_column($userUuidData, 'user_uuid'));
        if (!xphp_check_operate('data_manager_operate', $userArr)) {
            // 没有操作权限
            return [];
        }

        $mbResult = $this->service()->deleteStar($params);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            $this->muOpResult(true, xphp_get_lang('UI_COPY_DATA_DELETE_FOREVER_MASK'), $msg);
        } else {
            $this->muOpResult(false, xphp_get_lang('UI_COPY_DATA_DELETE_FOREVER_MASK'), $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 根据模块类型获取子模块类型
     * @param mixed $moduleType
     * @return mixed
     */
    private function getSubModuleType($moduleType)
    {
        // 文件子模块
        $FILE = xphp_get_config('module', 'SUBMODULE_TYPE');
        // 虚拟机子模块
        $VM = xphp_get_config('module', 'VM_SUB_MODULE');
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        if (in_array($moduleType, [$MODULE['VM'], $MODULE['PUBLIC_CLOUD'], $MODULE['PRIVATE_CLOUD']])) {
            if ($moduleType == $MODULE['PRIVATE_CLOUD']) {
                return $VM['PRIVATE_CLOUD'];
            } else if ($moduleType == $MODULE['PUBLIC_CLOUD']) {
                return $VM['PUBLIC_CLOUD'];
            }
            return $VM['VM'];
        }
        if (in_array($moduleType, [$MODULE['FS'], $MODULE['OBS'], $MODULE['HADOOP']])) {
            if ($moduleType == $MODULE['FS']) {
                return $FILE['FS'];
            } else if ($moduleType == $MODULE['OBS']) {
                return $FILE['OBS'];
            } else if ($moduleType == $MODULE['HADOOP']) {
                return $FILE['HADOOP'];
            }
        }
        return 0;
    }
    /**
     * 子模块转到模块类型
     * @param mixed $moduleType
     * @return mixed
     */
    private function getModuleType($moduleType)
    {
        // 模块类型
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        if (in_array($moduleType, [$MODULE['VM'], $MODULE['PUBLIC_CLOUD'], $MODULE['PRIVATE_CLOUD']])) {
            return $MODULE['VM'];
        }
        if (in_array($moduleType, [$MODULE['FS'], $MODULE['OBS'], $MODULE['HADOOP']])) {
            return $MODULE['FS'];
        }
        return $moduleType;
    }
    private function getPointSql($module, $search = null){
        $module_type = xphp_get_config('module', 'MODULE_TYPE');
        $sql = "SELECT DISTINCT bbt.detail, bbt.deleted_flag, bbt.timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, bbt.task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag, bbt.weekly_flag, bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag,bbt.data_local_flag,
        bsr.node_uuid,bsr.status,bsr.storage_uuid";
        switch ($module) {
            case $module_type['VM']:
            case $module_type['PUBLIC_CLOUD']:
            case $module_type['PRIVATE_CLOUD']:
                $sql .= ", vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type
                FROM bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr 
                WHERE bbt.timepoint_uuid = vbt.timepoint_uuid 
                AND bbt.storage_uuid = bsr.storage_uuid
                AND bbt.sub_module_type = {$this->getSubModuleType($module)}";

                if (!empty($search)) { // 查询搜索关键字
                    $sql .= " AND (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" . $search . "%' or vbt.vm_name like '%" . $search . "%')";
                }
                break;
            case $module_type['FS']:
            case $module_type['HADOOP']:
            case $module_type['OBS']:
                $sql .= ",fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, bbt.data_local_flag, bbt.task_create_time,bbt.storage_uuid
                FROM bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid 
                AND bbt.storage_uuid = bsr.storage_uuid
                AND bbt.sub_module_type = {$this->getSubModuleType($module)}";
                if (!empty($search)) { // 查询搜索关键字
                    $sql .= " AND (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
                }
                break;
            case $module_type['DB']:
                $sql .= ", dbt.db_uuid, dbt.db_name,dbt.instance_name, dbt.dir_path,dbt.db_type as sub_type, dbt.agent_uuid, bbt.data_local_flag,bbt.storage_uuid
                FROM bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr 
                where bbt.timepoint_uuid = dbt.timepoint_uuid 
                AND bbt.storage_uuid = bsr.storage_uuid ";
                if (!empty($search)) { // 查询搜索关键字
                    $sql .= " AND (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" . $search . "%' or dbt.db_name like '%" . $search . "%' or dbt.agent_ip like '%" . $search . "%')";
                }
                break;
            case $module_type['OS']:
                $sql .= ", obt.os_name, obt.dir_path, obt.os_type,obt.agent_ip,obt.agent_uuid, bbt.data_local_flag 
                FROM bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid 
                and bbt.storage_uuid = bsr.storage_uuid";
                if (!empty($search)) { // 查询搜索关键字
                    $sql .= " and (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" . $search . "%' or obt.os_name like '%" . $search . "%' or obt.agent_ip like '%" . $search . "%')";
                }
                break;
            case $module_type['NAS']:
                $sql .= ",fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, bbt.task_name,bbt.data_local_flag, bbt.task_create_time,bbt.storage_uuid
                FROM bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid 
                and bbt.storage_uuid = bsr.storage_uuid";
                if (!empty($search)) { // 查询搜索关键字
                    $sql .= " and (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
                }
                break;
            case $module_type['M365']:
                $sql .= "s,mbt.organization_info, mbt.m365_type, mbt.m365_timepoint_uuid as timepoint_uuid, cl.dir_path,mbt.organization_uuid as item_uuid,mbt.organization_name as item_name, bbt.backup_mode, bbt.timepoint, bbt.depend_point_uuid, bbt.task_create_time, bbt.importance_flag
                FROM m365_backup_timepoint mbt, bd_storage_resource bsr, bd_backup_timepoint bbt
                left join copy_list cl on cl.task_uuid = bbt.task_uuid
                where  bbt.timepoint_uuid = mbt.m365_timepoint_uuid 
                AND bbt.storage_uuid = bsr.storage_uuid";
                    if (!empty($search)) { // 查询搜索关键字
                        $sql .= " AND (bbt.timepoint like '%" . $search . "%' or bbt.task_name like '%" . $search . "%' or mbt.organization_name like '%" . $search . "%')";
                    }
                break;
        }
        $sql .= "  AND bbt.available_flag = ? AND bbt.import_flag = ? AND bbt.deleted_flag = ? AND bbt.module_type = ? AND bbt.copy_flag = ?";
        return $sql;
    }

    public function searchCopyTimePoint($params)
    {
        $forever = $params['forever'];
        $search = $params['search'];
        $storage_uuid = $params['storage_uuid'];
        $module = intval($params['module']);
        $archive_flag = $params['archive_flag'];
        $module_type = xphp_get_config('module', 'MODULE_TYPE');
        // 任务类型
        $task_type = xphp_get_config('task', 'TASKTYPE');
        // 时间点类型
        $backup_mode = xphp_get_config('task', 'BACKUP_MODE');
        $flag = xphp_get_config('app', 'FLAG');
        $instanceDb = [xphp_get_config('db', 'DB_TYPE')['SQLSERVER'], xphp_get_config('db', 'DB_TYPE')['SAPHANA']];
        // 虚拟机子模块类型
        $jobInfo = new JobInfo();
        $sqlParams = array();
        $sqlParams = array();
        $sql = $this->getPointSql($module, $search);
        $sqlParams =  [$flag['SET'], $flag['UNSET'],$flag['UNSET'],$this->getModuleType($module), $flag['SET']];
        //筛选出每个增备点和差异点对应PID
        if (!empty($storage_uuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storage_uuid));
        }
        if ($archive_flag) {
            $sql .= " and bbt.task_type in (" . $task_type['ARCHIVE'] . ", " . $task_type['ARCHIVE_FETCH'] . ") ";
        } else {
            $sql .= " and bbt.task_type in (" . $task_type['BACKUP_COPY'] . ", " . $task_type['BACKUP_COPY_FETCH'] . ") ";
        }
        if ($forever) { // 查询永久标记点
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }
        $sql .= ' order by bbt.timepoint';
        // dump($sql);
        $data = $this->dbSelect($sql, $sqlParams);
        $taskArr = array();
        $timepoint = array();
        // dump($pointData);
        foreach ($data as $point) {
            switch ($module) {
                    //虚拟机 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['VM']:
                case $module_type['PUBLIC_CLOUD']:
                case $module_type['PRIVATE_CLOUD']:
                    $item_uuid = $point['vm_uuid'];
                    break;
                    //文件 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['FS']:
                case $module_type['NAS']:
                case $module_type['HADOOP']:
                case $module_type['OBS']:
                    $item_uuid = $point['agent_uuid'];
                    break;
                    //数据库 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['DB']:
                    $item_uuid = $point['db_uuid'];
                    break;
                    //操作系统 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['OS']:
                    $item_uuid = $point['agent_uuid'];
                    break;
                case $module_type['M365']:
                    $item_uuid = $point['item_uuid'];
                    break;
            }
            if(!in_array($point['task_uuid'], $taskArr)){
                $taskArr[] = $point['task_uuid'];
            }
            $task_uuid = $point['task_uuid'];
            $subType = intval($point['sub_type']);
            $time_point_uuid = $point['timepoint_uuid'];

            $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($point['importance_flag']));
            $pId = $item_uuid . $task_uuid;
            //sql server多显示一层
            if (in_array($subType,$instanceDb)) {
                $pId = $subType . $point['instance_name'] . $task_uuid;
            }
            $name = $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")" . " " . $mark;
            if ($point['backup_mode'] == $backup_mode['FULL']) {
                if (!in_array($time_point_uuid, $timepoint)) {
                    $timepoint[] =  $time_point_uuid;
                    $node[] = array(
                        "id" =>  $time_point_uuid,
                        "pId" =>  $pId,
                        "name" => $name,
                        "oldname" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                        "title" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                        "checked" => false,
                        "type" => 3,
                        "point_name" => $point['timepoint'],
                        "vcenter_uuid" => $point['vcenter_uuid'],
                        "time_point_uuid" => $time_point_uuid,
                        "create_time" => $point['task_create_time'],
                        "node_uuid" => $point['node_uuid'],
                        "task_uuid" => $task_uuid,
                        "version" => $point['version'],
                        "icon" => $jobInfo->getTimepointIcon($point['backup_mode']),
                        "chkDisabled" =>  $point['backup_mode'] != $backup_mode['FULL'] ? true : false,
                        "event_type" => "point",
                        "item_uuid" => $item_uuid,
                        "path" => $point['dir_path'],
                        'module' => $module,
                        "source_storage_uuid" => $point["storage_uuid"],
                        "storage_uuid" => $point["storage_uuid"],
                        "depend_point_uuid" => $point["depend_point_uuid"],
                    );
                    // //添加了完全备份时间点继续下一次\
                    continue;
                }
            }
        }
        $unFull = $this->getUnFullPoint($taskArr, $module);
        if(!empty($unFull)){
            $node = array_merge($node, $unFull);
        }
        return $node;
    }
    private function getUnFullPoint($arr, $module){
        $module_type = xphp_get_config('module', 'MODULE_TYPE');
        // 时间点类型
        $backup_mode = xphp_get_config('task', 'BACKUP_MODE');
        $flag = xphp_get_config('app', 'FLAG');
        // 文件子模块
        $FSType = xphp_get_config('module', 'SUBMODULE_TYPE');
        $jobInfo = new JobInfo();
        $timepoint = [];
        $full_uuid_List = [];
        $un_full_list = [];
        $sql = $this->getPointSql($module,);
        $tasks = implode("','", $arr);
        $sql .= " and bbt.task_uuid in ('$tasks')";
        $sqlParams =  [$flag['SET'], $flag['UNSET'],$flag['UNSET'],$this->getModuleType($module), $flag['SET']];
        // dump($sql, $sqlParams);
        $data = $this->dbSelect($sql, $sqlParams);
        // dump($data);
        // 完备点和飞完备点数组
        foreach ($data as $point) {
            if ($point['backup_mode'] == $backup_mode['FULL']) {
                $full_uuid_List[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $un_full_list[] = $point;
            }
        }
        // 建立完备点和非完备点的依赖
        while (!empty($un_full_list)) {
            $un_full_count = count($un_full_list);
            $un_full_countTmp = count($un_full_list);
            foreach ($un_full_list as $key => $un_full) {
                $dependId = $un_full['depend_point_uuid'];
                foreach ($full_uuid_List as $key => $value) {
                    if ($dependId == $key) {
                        $full_uuid_List[$un_full['timepoint_uuid']] = $full_uuid_List[$key];
                        $un_full_list = array_splice($un_full_list, intval($key), 1);
                        $un_full_countTmp--;
                    }
                    continue;
                }
            }
            if ($un_full_count == $un_full_countTmp || $un_full_countTmp == 0) break;
        }
        foreach ($data as $point) {
            switch ($module) {
                    //虚拟机 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['VM']:
                case $module_type['PUBLIC_CLOUD']:
                case $module_type['PRIVATE_CLOUD']:
                    $item_uuid = $point['vm_uuid'];
                    break;
                    //文件 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['FS']:
                case $module_type['NAS']:
                case $module_type['HADOOP']:
                case $module_type['OBS']:
                    $item_uuid = $point['agent_uuid'];
                    break;
                    //数据库 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['DB']:
                    $item_uuid = $point['db_uuid'];
                    break;
                    //操作系统 获取$name/$item_uuid/$icon/$parent_uuid
                case $module_type['OS']:
                    $item_uuid = $point['agent_uuid'];
                    break;
                case $module_type['M365']:
                    $item_uuid = $point['item_uuid'];
                    break;
            }
            $task_uuid = $point['task_uuid'];
            $time_point_uuid = $point['timepoint_uuid'];

            $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($point['importance_flag']));
            $pId = $full_uuid_List[$time_point_uuid];
            if($module == $module_type['M365']){
                $pId = $item_uuid . $task_uuid;
            }
            $name = $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")" . " " . $mark;
            if (!in_array($time_point_uuid, $timepoint) && $point['backup_mode'] != $backup_mode['FULL']) {
                $timepoint[] =  $time_point_uuid;
                $node[] = array(
                    "id" => $time_point_uuid,
                    "pId" => $pId,
                    "name" => $name,
                    "oldname" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                    "title" => $point['timepoint'] . "(" . $jobInfo->getTimepointTypeDes($point['backup_mode']) . ")",
                    "checked" => false,
                    "type" => 4,
                    "point_name" => $point['timepoint'],
                    "vcenter_uuid" => $point['vcenter_uuid'],
                    "time_point_uuid" => $time_point_uuid,
                    "create_time" => $point['task_create_time'],
                    "node_uuid" => $point['node_uuid'],
                    "task_uuid" => $task_uuid,
                    "version" => $point['version'],
                    "icon" => $jobInfo->getTimepointIcon($point['backup_mode']),
                    "chkDisabled" =>  $point['backup_mode'] != $backup_mode['FULL'] ? true : false,
                    "event_type" => "point",
                    "item_uuid" => $item_uuid,
                    "path" => $point['dir_path'],
                    'module' => $module,
                    "source_storage_uuid" => $point["storage_uuid"],
                    "storage_uuid" => $point["storage_uuid"],
                    "depend_point_uuid" => $point["depend_point_uuid"],
                );
            }
        }
        // dump($node);
        return $node;
    }
    public function getFullPoint($timepoint_uuid, $module)
    {
        $flag = xphp_get_config('app', 'FLAG');
        $sql = $this->getPointSql($module) . " AND bbt.timepoint_uuid = ?";
        $sqlParams =  [$flag['SET'], $flag['UNSET'],$flag['UNSET'],$this->getModuleType($module), $flag['SET'],$timepoint_uuid];
        $data = $this->dbSelect($sql, $sqlParams);
        if (!empty($data[0]['depend_point_uuid'])) { //不是完备点继续找
            return $this->getFullPoint($data[0]['depend_point_uuid'], $module);
        } else {
            return $data[0]['timepoint_uuid'];
        }
    }

    public function deleteBatchCopyPoint($params)
    {
        //得到二维数组
        //遍历，并按节点uuid分组
        //发送删除消息到不同的节点
        $time_point_list = $params['time_point_list'];
        $delete_list = $params['deleteList'];
        $archive_data_flag = $params['archive_data'];
        $select_time_points = array();;
        $module = $params['module'];
        $node_uuids = array();
        $time_point_uuids = array();
        $time_point_uuid = array();
        $storage_uuids = array();
        $taskIds = array();
        $details = '';
        $countPoint = 0;
        // 按模块组装时间点信息
        // 删除任务主机 获取任务主机下所有时间点信息
        foreach ($delete_list as $d) {
            $select_time_points = $this->getTimepointByCopy($module, $d);
            $time_point_list =  array_merge($time_point_list, $select_time_points);
            if(!in_array($d['task_uuid'], $taskIds) && !empty($d['task_uuid'])){
                array_push($taskIds, $d['task_uuid']);
            }
        }
        $time_point_list = $this->arraySort($time_point_list, 'node_uuid', '', 0, -1);
        // 获取删除时间点信息
        foreach ($time_point_list as $d) {
            $countPoint++;
            if(!in_array($d['task_uuid'], $taskIds) && !empty($d['task_uuid'])){
                array_push($taskIds, $d['task_uuid']);
            }
            if (!in_array($d['node_uuid'], $node_uuids)) {
                $node_uuids[] = $d['node_uuid'];
                if (!empty($time_point_uuid)) {
                    $time_point_uuids[] = $time_point_uuid;
                    $time_point_uuid = array();
                }
                $time_point_uuid[] = $d['time_point_uuid'];
                $storage_uuids[] = $d['storage_uuid'];
            } else {
                $time_point_uuid[] = $d['time_point_uuid'];
            }
            if($countPoint == 1){
                $details .= "\n" . $d['deleteDetails'] . "\n" ;
            } else {
                $details .= $d['deleteDetails'] . "\n" ;
            }
        }
        $time_point_uuids[] = $time_point_uuid;
        $node_uuids_count = count($node_uuids);   //计算node uuids数组的长度用于分组发送消息
        $opName = 'BD_BACKUP_COPY_POINT_OP_BATCH_DELETE';
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        // 处于磁带上的备份点不能删除(tape = 10)
        $timePointIds = implode("','", $time_point_uuid);
        $sql = "SELECT *
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.timepoint_uuid IN ('$timePointIds') AND bsr.storage_type = ? ";
        $tapePointData = $this->dbSelect($sql, [xphp_get_config('resource','BD_STORAGE_TYPE')['TAPE']]);
        if (is_array($tapePointData) && $tapePointData) {
            return $this->muOpResult(false, $operate, xphp_get_lang('UI_COPY_DATA_TAPE_STORAGE_TIP'),'warning');
        }
        for ($i = 0; $i < $node_uuids_count; $i++) {
            $msg = $time_point_uuids[$i];
            $msg = array(
                'timepoint_uuids' => $time_point_uuids[$i]
            );
            $msg = json_encode($msg);
            $deleteSubType = 0;
            // 按模块获取删除的任务类型
        }
        $uuidList = array();
        foreach ($time_point_uuids as $d) {

            $uuidList = array_merge($uuidList, $d);
        }
        // 检测副本任务是否被副本占用
        $this->taskExistInCopy($taskIds, $operate);

        // 检测时间点是否被占用
        $this->pointExistInCopy($uuidList, $operate);

        // 检测时间点是否被恢复占用
        $this->pointExistInRecovery($uuidList, $operate, $module);
        // 权限检查
        $uuid = "('" . implode("','", $uuidList) . "')";
        $userUuidData = $this->dbSelect("select user_uuid from bd_backup_timepoint where timepoint_uuid in {$uuid}");
        $userArr = array_unique(array_column($userUuidData, 'user_uuid'));
        if (!xphp_check_operate('data_manager_operate', $userArr)) {
            // 没有操作权限
            return [];
        }
        for ($i = 0; $i < $node_uuids_count; $i++) {
            $msg = array(
                'timepoint_uuids' => array_values(array_unique($time_point_uuids[$i])),
                'storage_uuid' => $storage_uuids[$i],
            );
            $msg = json_encode($msg);
            $mbResult = $this->mbCopyMsg($node_uuids[$i], $deleteSubType, $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint);
        //返回结果到UI
        if ($result) {
            if ($archive_data_flag) {
                $this->systemLog('SYSTEM_LOG_DELETE_ARCHIVE_BATCH_TIMEPOINT', $descriptionParam);
                $opName = 'BD_ARCHIVE_POINT_OP_BATCH_DELETE';
                $operate = (new PfOpcode())->getOpcodeDes($opName);
                return $this->muOpResult($result, $operate, $msg, '', 0, '');
            } else {
                $this->systemLog('SYSTEM_LOG_DELETE_COPY_BATCH_TIMEPOINT', $descriptionParam);
                return $this->muOpResult($result, $operate, $msg, '', 0, '');
            }
        } else {
            if ($archive_data_flag) {
                $this->systemLog('SYSTEM_LOG_DELETE_ARCHIVE_BATCH_TIMEPOINT', $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
                $opName = 'BD_ARCHIVE_POINT_OP_BATCH_DELETE';
                $operate = (new PfOpcode())->getOpcodeDes($opName);
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            } else {
                $this->systemLog('SYSTEM_LOG_DELETE_COPY_BATCH_TIMEPOINT', $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        }
    }

    /**
     * @description: 获取任务主机下的所有时间点信息
     * @param {*} $module
     * @param {*} $copy_info
     * @return {*}
     */    
    public function getTimepointByCopy($module, $copy_info)
    {
        $storage_uuid = $copy_info['storage_uuid'];
        $info = array();
        $module_type = xphp_get_config('module', 'MODULE_TYPE');
        $taskNameDes = xphp_get_lang('UI_PUBLIC_TASK_RNAME');
        $moduleDes = xphp_get_lang('UI_PUBLIC_MODULE_TYPE');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        // 分模块查询
        switch ($module) {
            case $module_type['VM']:
            case $module_type['PRIVATE_CLOUD']:
            case $module_type['PUBLIC_CLOUD']:
                $sql =  "select bbt.timepoint_uuid, bsr.node_uuid,bbt.task_name,bbt.timepoint,vbt.vm_name from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ? and vbt.vm_uuid = ? and bbt.available_flag = 1 ";
                $sqlParams = array($copy_info['task_uuid'], $copy_info['item_uuid']);
                break;
            case $module_type['FS']:
            case $module_type['NAS']:
            case $module_type['OBS']:
            case $module_type['HADOOP']:
                $sql =  "select bbt.timepoint_uuid,bbt.task_name,bbt.timepoint,fbt.agent_name from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
                $sqlParams = array($copy_info['task_uuid'], $copy_info['item_uuid']);
                break;
            case $module_type['DB']:
                $sql =  "select bbt.timepoint_uuid,bbt.task_name,bbt.timepoint,dbt.db_name from bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.task_uuid = ? and dbt.db_uuid = ? and bbt.available_flag = 1 ";
                $sqlParams = array($copy_info['task_uuid'], $copy_info['item_uuid']);
                break;
            case $module_type['OS']:
                $sql =  "select bbt.timepoint_uuid, obt.os_type,bbt.task_name,bbt.timepoint,obt.os_name from bd_backup_timepoint bbt, os_backup_timepoint obt where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.task_uuid = ? and obt.agent_uuid = ? and bbt.available_flag = 1 ";
                $sqlParams = array($copy_info['task_uuid'], $copy_info['item_uuid']);
                break;
            case $module_type['M365']:
                $sql =  "select mbt.m365_timepoint_uuid as timepoint_uuid,bbt.task_name,bbt.timepoint,mbt.organization_name from bd_backup_timepoint bbt, m365_backup_timepoint mbt where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.task_uuid = ? and bbt.available_flag = 1";
                $sqlParams = array($copy_info['task_uuid']);
                break;
        }
        // 按节点筛选
        if (!empty($nodeuuid)) {
            $sql .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($copy_info['node_uuid']));
        }
        // 按存储筛选
        if (!empty($storage_uuid)) {
            $sql .=  " and bbt.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storage_uuid));
        }
        $data  = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d) {
            switch ($module) {
                case $moduleType['VM']:
                case $moduleType['PRIVATE_CLOUD']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_VM');
                    $hostDes = xphp_get_lang('UI_VCENTER_MACHINE_NAME');
                    $item_name = $d['vm_name'];
                    break;
                case $moduleType['FS']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_FS');
                    $hostDes = xphp_get_lang('UI_AGENT_HOST_NAME');
                    $item_name = $d['agent_name'];
                    break;
                case $moduleType['DB']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_DB');
                    $hostDes = xphp_get_lang('UI_DB_DATABASE_NAME');
                    $item_name = $d['db_name'];
                    break;
                case $moduleType['OS']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_OS');
                    $hostDes = xphp_get_lang('UI_AGENT_HOST_NAME');
                    $item_name = $d['os_name'];
                    break;
                case $moduleType['NAS']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_NAS');
                    $hostDes = xphp_get_lang('UI_STORAGE_SHARED_FOLDERS');
                    $item_name = $d['agent_name'];
                    break;
                case $moduleType['PUBLIC_CLOUD']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_PUBLIC_CLOUD');
                    $hostDes = xphp_get_lang('UI_DB_INSTANCE_NAME');
                    $item_name = $d['vm_name'];
                    break;
                case $moduleType['OBS']:
                    $m = xphp_get_lang('UI_PLATFORM_OBS');
                    $hostDes = xphp_get_lang('UI_PLATFORM_OBS_NAME');
                    $item_name = $d['agent_name'];
                    break;
                case $moduleType['HADOOP']:
                    $m = xphp_get_lang('UI_PLATFORM_HADOOP');
                    $hostDes = xphp_get_lang('WEB_HADOOP_CLUSTER_NAME');
                    $item_name = $d['agent_name'];
                    break;
                case $moduleType['M365']:
                    $m = xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE');
                    $hostDes = xphp_get_lang('UI_ORGAN_ORGANIZATION_NAME');
                    $item_name = $d['organization_name'];
                    break;
            }
            $info[] = array(
                'time_point_uuid' => $d['timepoint_uuid'],
                'node_uuid' => $copy_info['node_uuid'],
                'storage_uuid' => $storage_uuid,
                'deleteDetails' => $taskNameDes . "：" . $d['task_name'] . "，" . $moduleDes . "：" . $m . "，" . xphp_get_lang('UI_DATA_TIMEPOINT') . ":" . $d['timepoint'] . "，" . $hostDes . "：" . $item_name,
            );
        }
        return $info;
    }

    /**
     * @description: 自动以排序
     * @param {*} $sortArray
     * @param {*} $sortColumn
     * @param {*} $sortType
     * @param {*} $start
     * @param {*} $length
     * @return {*}
     */    
    private function arraySort($sortArray, $sortColumn, $sortType, $start, $length)
    {
        // 排序方式-升、降
        if ('desc' == $sortType) {
            $sortType = SORT_DESC;
        } else {
            $sortType = SORT_ASC;
        }
        if (-1 == $length) {
            $length = null;     //显示全部
        }
        $column = array();
        foreach ($sortArray as $arr) {
            $column[] = $arr[$sortColumn];
        }
        $column = array_map('strtolower', $column); //不区分大小写
        array_multisort($column, $sortType, $sortArray);
        $sortArray = array_slice($sortArray, $start, $length);
        return $sortArray;
    }

    public function taskExistInCopy($ids, $operate){
        $idArray = implode("','", $ids);
        $sql = "SELECT cl.task_uuid FROM copy_list cl, bd_task bt WHERE (cl.source_task_uuid IN ('$idArray') OR cl.task_uuid IN ('$idArray')) AND bt.task_uuid = cl.task_uuid AND bt.task_status != ?";
        $data = $this -> dbSelect($sql, [xphp_get_config('task', 'TASKSTATUS')['STOPPED']]);
        if (!empty($data)) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_COPY_DELETE_TIMEPOINT_INSTANT_WARNING'), 'warning'));
        }
    }

    /**
     * 检查需要删除的完备点是否被使用
     * @param array $ids
     *
     */
    public function pointExistInCopy($ids, $operate)
    {
        //判断完备点是否在回传任务中
        $idsArray = implode("','", $ids);
        $sqlA = "select task_uuid from copy_list where archive_timepoint_uuid in ('$idsArray')";
        $dataA  = $this->dbSelect($sqlA, array());
        $sqlB = "select task_uuid, specified_timepoint_list from copy_list where specified_timepoint_list IS NOT NULL";
        $dataB = $this->dbSelect($sqlB, array());
        $uuids = array();
        foreach($dataB as $d){
            if(!empty($d['specified_timepoint_list'])){
                $specifiedList = json_decode($d['specified_timepoint_list'],true);
                foreach($ids as $s){
                    if(in_array($s,$specifiedList)){
                        array_push($uuids,$s);
                    }
                }
            }
        }
        if (!empty($dataA) || !empty($uuids)) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_COPY_DELETE_TIMEPOINT_INSTANT_WARNING'), 'warning'));
        }
    }

    /**
     * 
     * 检查需要删除的完备点是否被恢复任务使用
     * @param array $uuids
     *
     */
    public function pointExistInRecovery($uuids, $operate, $module)
    {
        //判断完备点是否在恢复任务中
        $module_type = xphp_get_config('module', 'MODULE_TYPE');
        $uuids = implode("','", $uuids);
        switch ($module) {
            case $module_type['VM']:
            case $module_type['PUBLIC_CLOUD']:
            case $module_type['PRIVATE_CLOUD']:
                $sql = "select task_uuid from vm_machine_list where timepoint_uuid in ('$uuids')";
                break;
            case $module_type['FS']:
            case $module_type['NAS']:
            case $module_type['M365']:
            case $module_type['OBS']:
            case $module_type['HADOOP']:
                $sql = "select task_uuid from fs_path_list where recovery_timepoint_uuid in ('$uuids')";
                break;
            case $module_type['DB']:
                $sql = "select task_uuid from db_list where timepoint_uuid in ('$uuids')";
                break;
            case $module_type['OS']:
                $sql = "select task_uuid from os_list where timepoint_uuid in ('$uuids')";
                break;
        }
        $data  = $this->dbSelect($sql, array());
        if (!empty($data)) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_COPY_DELETE_TIMEPOINT_INSTANT_WARNING'), 'warning'));
        }
    }
}
