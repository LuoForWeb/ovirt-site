<?php

namespace app\v1\log\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use app\v1\user\v0\logic\Role;
use app\v1\user\v0\logic\User;
use app\v1\job\v0\logic\JobInfo;

/**
 * note          日志管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/6/5 16:17
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends Base
{

    // ---------------任务日志----------------
    /**
     * 获取任务日志列表
     * @param array $params 查询参数
     * @return array
     */
    public function getJobLogList($params, $isExport = false)
    {
        $offset = $params['offset'];
        $limit = $params['limit'];
        $order = $params['order'];
        $sort = $params['sort'];
        $createUser = $params['user_name'];
        $hypervisor = intval($params['vm_type']);
        $dbType = intval($params['db_type']);
        $taskType = intval($params['job_type']);
        $moduleType = $params['module_type'];
        $subModuleType = intval($params['sub_module_type']);
        $taskName = $params['job_name'];
        $taskName = v1_escape_wildcard($taskName);
        $logStatus = intval($params['log_status']);
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $FLAG = xphp_get_config('app', 'FLAG');

        $sqlData = "SELECT bu.user_level, btl.id, btl.task_name, btl.task_type, btl.user_name, btl.agent_name, btl.module_type, btl.submodule_type, 
                    btl.error_code, unix_timestamp(btl.op_time) op_time, btl.description_key, btl.description_param, btl.log_level, btl.user_uuid
                from bd_task_log btl left join bd_user bu on btl.user_uuid = bu.user_uuid
                where btl.running_flag = {$FLAG['UNSET']} ";
        $sqlCount = "SELECT count(btl.id) as total from bd_task_log btl left join bd_user bu on btl.user_uuid = bu.user_uuid
                        where btl.running_flag = {$FLAG['UNSET']} ";
        $sql = '';

        //获取当前用户所拥有的用户
        $name = v1_escape_wildcard($params['search']);
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['log']);
            $sql .= " and btl.user_uuid in ({$userUuidSql}) ";
        }
        //按任务名搜索
        if ($this->checkEmpty($name)) {
            $sql .= " and btl.task_name like '%{$name}%' ";
        }
        //判断输入的用户名是否在当前用户的管理范围
        if ($this->checkEmpty($createUser)) {
            //按模块搜索
            $sql .= " and btl.user_name like '%{$createUser}%'";
        }
        if ($moduleType == xphp_get_config('module', 'MODULE_TYPE')['VM'] && !empty($hypervisor)) {
            //虚拟化类型
            $sql .= " and btl.submodule_type = '{$hypervisor}' ";
        } else if ($moduleType == xphp_get_config('module', 'MODULE_TYPE')['DB'] && !empty($dbType)) {
            //数据库类型
            $sql .= " and btl.submodule_type = '{$dbType}' ";
        }

        //按任务名搜索
        if ($this->checkEmpty($taskName)) {
            $sql .= " and btl.task_name like '%{$taskName}%' ";
        }
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        //模块类型
        if (!empty($moduleType)) {
            if ($subModuleType == 3 && $moduleType == $moduleTypeArr['VM']) { //公有云
                $publicCloud = "(" . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . ")";
                $sql .= " and btl.module_type = '{$moduleType}' and btl.submodule_type in  " . $publicCloud . " ";
            } else if ($subModuleType == 2 && $moduleType == $moduleTypeArr['VM']) { // 私有云
                $privateCloud = "(" . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']) . ")";
                $sql .= " and btl.module_type = '{$moduleType}' and btl.submodule_type in  " . $privateCloud . " ";
            } else if ($subModuleType == 1 && $moduleType == $moduleTypeArr['VM']) { // 虚拟机
                $publicCloud = "(" . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . ")";
                $vmHypervisor = array_diff(xphp_get_config('vm', 'VMHYPERVISORTYPE'), xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']);
                $vmHypervisor = "(" . implode(',', $vmHypervisor) . ")";
                $sql .= " and btl.module_type = '{$moduleType}' and btl.submodule_type in  " . $vmHypervisor . " and btl.submodule_type not in " . $publicCloud . " ";
            } else {
                $moduleTypeArr = explode(',', $moduleType);
                $moduleTypeStr = "(" . implode(',', $moduleTypeArr) . ")";
                $sql .= " and btl.module_type in  " . $moduleTypeStr . " ";
                if ($moduleType != $moduleTypeArr['DB'] && !empty($subModuleType)) {
                    $sql .= " and btl.submodule_type = '{$subModuleType}' ";
                }
            }
        }
        //任务类型
        if (!empty($taskType)) {
            $sql .= " and btl.task_type = '{$taskType}' ";
        }

        //日志等级
        if (!empty($logStatus)) {
            $sql .= " and btl.log_level = '{$logStatus}' ";
        }

        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            $sql .= " and btl.op_time between '{$startTime}' and '{$endTime}' ";
        }
        $sortArr = array('job_name' => 'btl.task_name', 'module_type' => 'btl.module_type,btl.submodule_type', 'job_type_value' => 'btl.task_type', 'user_name' => 'btl.user_name', 'op_time' => 'btl.op_time', 'log_level' => 'btl.log_level', 'log_description' => 'btl.description_key');
        $count = $this->dbSelect($sqlCount . $sql, []);

        if (!$isExport) {
            if (!empty($sort) && !empty($order)) {
                $sql .= " order by $sortArr[$sort] $order ";
            }
            $sql .= " limit {$offset} , {$limit} ";
        }

        // dump($sql, $sqlParams);
        $data = $this->dbSelect($sqlData . $sql, []);

        $records = [];
        $records['rows'] = [];
        foreach ($data as $d) {
            $sub_module_type = $d['submodule_type'];
            if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['VM']) {
                $sub_module_type = $this->getVmSubModuleType($d['submodule_type']);
            } else if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['DB']) {
                $sub_module_type = '';
            }
            $records["rows"][] = array(
                'num' => ++$offset,
                'id' => $d['id'],
                'job_name' => $d['task_name'],
                'module_type' => $d['module_type'] == '30' ? xphp_get_lang('WEB_PLATFORM_DES_VERIFICATION') : (new JobInfo())->getObjects($d['module_type'], $sub_module_type),
                'job_type' => xphp_get_desc('Pf', 'TASKTYPEDES')[$d['task_type']],
                'module_type_value' => $d['module_type'],
                'sub_module_type_value' => $d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['FS'] ? $d['submodule_type'] : $this->getVmSubModuleType($d['submodule_type']),
                'job_type_value' => $d['task_type'],
                'user_name' => $d['user_name'],
                'op_time' => $this->parseDate($d['op_time']),
                'log_level_des' => $this->getLogLevelDes($d['log_level']),
                'log_description' => $this->getLogDescription(
                    xphp_get_config('log', 'LOGTYPE')['TASK'],
                    $d['error_code'],
                    $d['description_key'],
                    $d['description_param']
                ),
                'log_level' => intval($d['log_level']),
                'user_uuid' => $d['user_uuid'],
            );
        }
        $records["total"] = $count[0]['total'];
        return $records;
    }
    /**
     * 删除任务日志
     * @param mixed $params
     * @return string
     */
    public function deleteJobLog($params)
    {
        $id = $params['id'];
        $idArray = [];
        foreach ($id as $v) {
            $idArray[] = intval($v);
        }

        // 关联管理用户判断 存储资源 - 操作 log_operate
        // 判断用户是否有操作权限
        $checkOperate = xphp_check_operate('log_operate', [xphp_get_user_info()['userUuid']]);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, xphp_get_lang('UI_ROLE_PERMISSION'), xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'warning'));
        }
        $ids = implode(',', $idArray);
        $data = $this->dbSelect("SELECT user_uuid,unix_timestamp(op_time) op_time from bd_task_log where id in ($ids)");
        // 操作权限判断
        $userArr = implode(',', array_unique(array_column((array) $data, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['log']);

        // 判断下是否符合保留策略
        $leastDays = xphp_get_system_data_safe(2);
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column((array) $data, 'op_time');
        // 检查保留策略是否可删除
        $this->deleteCheckStrategy($reserveType, 1, $idArray, $daysArr, $reserveNum);
        $msg = ['id_list' => $idArray];
        return $this->unifyMsg('BD_LOG_OP_TASK_DELETE', json_encode($msg));
    }
    /**
     * 导出任务日志列表
     * @param mixed $params
     * @return void
     */
    public function exportJobLog($params)
    {
        $params['isExport'] = true;
        $exportData = $this->getJobLogList($params, true)['rows'];
        $title = xphp_get_lang('UI_HOMEPAGE_TASK_LOG');
        $header = [
            'num' => xphp_get_lang('UI_PUBLIC_NUMBER'),
            'job_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'module_type' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'job_type' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'user_name' => xphp_get_lang('UI_PUBLIC_USER'),
            'time' => xphp_get_lang('UI_PUBLIC_TIME'),
            'status' => xphp_get_lang('UI_PUBLIC_STATUS'),
            'description' => xphp_get_lang('UI_PUBLIC_DESCRIPTION'),
        ];
        $exportData = array_map(function ($row) {
            $nullSpace = xphp_get_config('app', 'NULLSPACE');
            $timeSpace = xphp_get_config('app', 'TIMESPACE');
            $authModule = json_decode($row['$authorization_module'], true);
            $authModuleDes = [];
            return [
                'num' => $row['id'],
                'job_name' => $row['job_name'],
                'module_type' => $row['module_type'],
                'job_type' => $row['job_type'],
                'user_name' => $row['user_name'],
                'time' => $row['op_time'],
                'status' => $row['log_level_des'],
                'description' => $row['log_description'],
            ];
        }, $exportData);
        $relation = [
            'num' => ['col_name' => 'A', 'width' => 10],
            'job_name' => ['col_name' => 'B', 'width' => 25],
            'module_type' => ['col_name' => 'D', 'width' => 15],
            'job_type' => ['col_name' => 'E', 'width' => 15],
            'user_name' => ['col_name' => 'C', 'width' => 20],
            'time' => ['col_name' => 'F', 'width' => 10],
            'status' => ['col_name' => 'G', 'width' => 30],
            'description' => ['col_name' => 'H', 'width' => 30],
        ];
        v1_base_export($title, $header, $exportData, $relation);
    }
    /**
     * 检查删除的日志是否在保留策略内
     * @param mixed $reserveType
     * @param mixed $type
     * @param mixed $id
     * @param mixed $daysArr
     * @param mixed $reserveNum
     * @return bool|string
     */
    private function deleteCheckStrategy($reserveType, $type, $id, $daysArr, $reserveNum)
    {
        switch ($reserveType) {
            case 1: // 按数量保留
                // 按数量保留
                $sql = "SELECT * from bd_task_log where running_flag = 2 ORDER BY op_time desc limit 0,{$reserveNum}";
                if ($type == 2) {
                    $sql = "SELECT * from bd_system_log ORDER BY op_time desc limit 0,{$reserveNum}";
                }
                $reserveNumList = $this->dbSelect($sql) ?? []; //删除前数量
                // 保留个数内的所有记录的id
                $idArr = array_column($reserveNumList, 'id');
                foreach ($id as $v) {
                    if (in_array($v, $idArr)) {
                        // 那么不允许删除
                        $msg = xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS');
                        $msg = str_replace('%S%', $reserveNum, $msg);
                        return $this->muOpResult(false, xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'), $msg);
                    }
                }
                break;
            case 2: // 按时间保留
                if (time() - min($daysArr) < $reserveNum * 24 * 3600) {
                    // 那么不允许删除
                    $msg = xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS');
                    $msg = str_replace('%S%', $reserveNum, $msg);
                    return $this->muOpResult(false, xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'), $msg);
                }
                break;
            case 3: // 永久保留
                // 永久保留，那么不允许删除
                return $this->muOpResult(false, xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'), xphp_get_lang('WEB_LOG_DELETE_CAN_NOT_DELETE_TIPS'));
        }
        return true;
    }
    /**
     * 获取任务运行日志
     * @param array $params 参数
     * @return array
     */
    public function getRunningJobLog($params)
    {
        $jobUuid = $params['jobs_uuid'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $sql = "SELECT 
            id, task_name, task_type, user_name, agent_name, 
            module_type, submodule_type, error_code, 
            unix_timestamp(op_time) op_time,
            description_key, description_param, log_level, error_detail, script_detail
        from bd_task_log 
        where 
            task_uuid = ? 
            and running_flag = ? 
        ";
        if (!empty($startTime) && !empty($endTime)) {
            $sql .= " and op_time >= ? and op_time <= ? ";
            $sqlParams = array($jobUuid, xphp_get_config('app', 'FLAG')['SET'], $startTime, $endTime, $offset, $limit);
        } else {
            $sqlParams = array($jobUuid, xphp_get_config('app', 'FLAG')['SET'], $offset, $limit);
        }
        $sql .= " order by id desc limit ?, ?";
        $data = $this->dbSelect($sql, $sqlParams);
        // 归档标志
        $archive_flag = false;

        $info = [];
        $confDes = xphp_get_desc('Pf', 'LOG_LEVEL_DES');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        foreach ($data as $d) {
            if ($d['task_type'] == $taskType['ARCHIVE'] || $d['task_type'] == $taskType['ARCHIVE_FETCH']) {
                $archive_flag = true;
            } else {
                $archive_flag = false;
            }
            $info[] = array(
                'op_time' => $this->parseDate($d['op_time']),
                'level' => $confDes[$d['log_level']],
                'level_value' => intval($d['log_level']),
                'description' => $this->getLogDescription(
                    xphp_get_config('log', 'LOGTYPE')['TASK'],
                    $d['error_code'],
                    $d['description_key'],
                    $d['description_param'],
                    $archive_flag,
                    $d['log_level'],
                    $d['module_type'],
                    $d['submodule_type'],
                    $d['id'],
                    $d['error_detail'],
                    $d['script_detail']
                )
            );
        }
        return $info;
    }

    /**
     * 获取历史任务运行日志
     * @param string $historyJobUuid 历史任务uuid
     * @return array
     */
    public function getHistoryRunningJobLog($params)
    {
        $historyJobUuid = $params['jobs_uuid'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];

        $sql = "SELECT 
                    bhtl.id, bhtl.agent_name, bhtl.error_code, unix_timestamp(bhtl.op_time) op_time, bhtl.description_key, bhtl.description_param, bhtl.log_level,bht.task_type
                FROM 
                    bd_history_task_log bhtl, bd_history_task bht 
                WHERE 
                    bhtl.history_uuid = bht.history_uuid AND bhtl.history_uuid = ?";
        if (!empty($startTime) && !empty($endTime)) {
            $sql .= " and op_time >= ? and op_time <= ? ";
            $sqlParams = array($historyJobUuid, $startTime, $endTime, $offset, $limit);
        } else {
            $sqlParams = array($historyJobUuid,  $offset, $limit);
        }
        $sql .= " ORDER BY id DESC LIMIT ?,?";
        $data = $this->dbSelect($sql, $sqlParams);
        $info = [];
        $confDes = xphp_get_desc('Pf', 'LOG_LEVEL_DES');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        foreach ($data as $d) {
            if ($d['task_type'] == $taskType['ARCHIVE'] || $d['task_type'] == $taskType['ARCHIVE_FETCH']) {
                $archive_flag = true;
            } else {
                $archive_flag = false;
            }
            $info[] = array(
                'op_time' => $this->parseDate($d['op_time']),
                'level' => $confDes[$d['log_level']],
                'level_value' => intval($d['log_level']),
                'description' => $this->getLogDescription(
                    xphp_get_config('log', 'LOGTYPE')['TASK'],
                    $d['error_code'],
                    $d['description_key'],
                    $d['description_param'],
                    $archive_flag,
                    $d['log_level']
                )
            );
        }
        return $info;
    }
    // ---------------任务日志----------------
    // ---------------系统日志----------------
    /**
     * 获取系统日志列表
     * @param mixed $params
     * @param mixed $isExport
     * @return array<array|int>
     */
    public function getSystemLogList($params, $isExport = false)
    {
        $offset = intval($params['offset']);
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        // 由于可能存在同一时间插入两条数据，但是需要显示先后顺序，所以时间排序使用id
        $sortArr = array('user_name' => 'bsl.user_name', 'op_time' => 'bsl.id', 'log_level' => 'bsl.log_level', 'description' => 'bsl.description_key');

        $sqlData = "SELECT bsl.id, bsl.user_uuid, bsl.user_name, bsl.agent_name, bsl.error_code, unix_timestamp(bsl.op_time) op_time, bsl.description_key, 
                bsl.description_param, bsl.log_level from bd_system_log bsl WHERE 1=1 ";
        $sqlCount = "SELECT count(bsl.id) as total from bd_system_log bsl WHERE 1=1 ";
        $sql = '';

        //检查是否是租户管理员
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['log']);
            $sql .= " and bsl.user_uuid in ({$userUuidSql}) ";
        }

        //获取当前用户所拥有的用户
        $name = $params['search'];
        if ($this->checkEmpty($name)) {
            $sql .= " and bsl.user_name LIKE '%{$name}%' ";
        }
        $createUser = $params['create_user'];
        $logStatus = intval($params['log_status']);
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $opType = $params['op_type'];

        if ($this->checkEmpty($opType)) {
            if ($opType == 'SYSTEM_USER') {
                $sql .= " and (bsl.description_key like '%SYSTEM_USER_ADD%' OR bsl.description_key like '%SYSTEM_USER_MODIFY%' OR bsl.description_key like '%SYSTEM_USER_DELETE%')";
            } else {
                $sql .= " and bsl.description_key LIKE '%{$opType}%'";
            }
        }
        if ($this->checkEmpty($createUser)) {
            $sql .= " and bsl.user_name like '%{$createUser}%' ";
        }

        //日志等级
        if (!empty($logStatus)) {
            $sql .= " and bsl.log_level = '{$logStatus}' ";
        }

        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            $sql .= " and bsl.op_time between '{$startTime}' and '{$endTime}' ";
        }
        //排除副本任务执行保留策略和合并时间点的日志
        // $sql .= ' and bsl.description_key != "BACKUP_COPY_DESC_KEY_DO_RETENTION" and bsl.description_key != "BACKUP_COPY_DESC_KEY_MERGE_TIMEPOINT" ';
        $count = $this->dbSelect($sqlCount . $sql, []);
        $sqlData = $sqlData . $sql;
        if (!$isExport) {
            if (!empty($params['sort']) && !empty($params['order'])) {
                $sqlData .= " order by $sortArr[$sort] $order";
            }
            $sqlData .= " limit {$offset} , {$limit} ";
        }
        $data = $this->dbSelect($sqlData, []);
        $num = intval($count[0]['total']);
        $records = [];
        $records['rows'] = [];
        foreach ($data as $d) {
            $des = $this->getLogDescription(
                xphp_get_config('log', 'LOGTYPE')['SYSTEM'],
                $d['error_code'],
                $d['description_key'],
                $d['description_param']
            );
            $opTypeDes = $this->getOpType($d['description_key']);

            $records['rows'][] = array(
                'id' => ++$offset,
                'log_id' => $d['id'],
                'user_name' => $d['user_name'],
                'op_type' => $d['description_key'],
                'op_type_des' => $opTypeDes,
                'op_time' => $this->parseDate($d['op_time']),
                'log_level_des' => $this->getLogLevelDes($d['log_level']),
                'description' => $des,
                'log_level' => intval($d['log_level']),
                'user_uuid' => $d['user_uuid'],
            );
        }
        $records["total"] = $num;
        return $records;
    }
    /**
     * 删除系统日志
     * @param mixed $params
     * @return string
     */
    public function deleteSystemLog($params)
    {
        $id = $params['id'];
        $idArray = [];
        foreach ($id as $v) {
            $idArray[] = intval($v);
        }

        // 判断下是否在最低删除期限内
        $ids = "(" . implode(',', $idArray) . ")";
        $data = $this->dbSelect("SELECT unix_timestamp(op_time) op_time, user_uuid from bd_system_log where id in {$ids}");

        // 操作权限判断
        $userArr = implode(',', array_unique(array_column((array) $data, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['log']);

        // 判断下是否符合保留策略
        $leastDays = xphp_get_system_data_safe(3);
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column((array) $data, 'op_time');
        // 检查保留策略是否可删除
        $this->deleteCheckStrategy($reserveType, 2, $idArray, $daysArr, $reserveNum);
        $msg = ['id_list' => $idArray];
        return $this->unifyMsg('BD_LOG_OP_SYSTEM_DELETE', json_encode($msg));
    }

    /**
     * 导出系统日志列表
     * @param mixed $params
     * @return void
     */
    public function exportSystemLog($params)
    {
        $params['isExport'] = true;
        $exportData = $this->getSystemLogList($params, true)['rows'];
        $title = xphp_get_lang('UI_HOMEPAGE_SYSTEM_LOG');
        $header = [
            'num' => xphp_get_lang('UI_PUBLIC_NUMBER'),
            'user_name' => xphp_get_lang('UI_PUBLIC_USER'),
            'op_type_des' => xphp_get_lang('UI_LOG_OPERATE_TYPE'),
            'op_time' => xphp_get_lang('UI_PUBLIC_TIME'),
            'log_level_des' => xphp_get_lang('UI_PUBLIC_STATUS'),
            'description' => xphp_get_lang('UI_PUBLIC_DESCRIPTION'),
        ];
        $exportData = array_map(function ($row) {
            $nullSpace = xphp_get_config('app', 'NULLSPACE');
            $timeSpace = xphp_get_config('app', 'TIMESPACE');
            $authModule = json_decode($row['$authorization_module'], true);
            $authModuleDes = [];
            return [
                'num' => $row['alarm_id'],
                'user_name' => $row['user_name'],
                'op_type_des' => $row['op_type_des'],
                'op_time' => $row['op_time'],
                'log_level_des' => $row['log_level_des'],
                'description' => $row['description'],
            ];
        }, $exportData);
        $relation = [
            'num' => ['col_name' => 'A', 'width' => 10],
            'user_name' => ['col_name' => 'B', 'width' => 25],
            'op_type_des' => ['col_name' => 'C', 'width' => 20],
            'op_time' => ['col_name' => 'D', 'width' => 15],
            'log_level_des' => ['col_name' => 'E', 'width' => 15],
            'description' => ['col_name' => 'F', 'width' => 10],
        ];
        v1_base_export($title, $header, $exportData, $relation);
    }

    /**
     * 系统日志下载
     * @param array $params 参数
     * @return string
     */
    public function downloadSystemLog(array $params)
    {
        $opName = "NODE_SYS_OP_DOWNLOAD_SYSTEM_LOG";
        $packList = $params['pack_list'];
        $targetLogList = [];

        foreach ($packList as $key => $list) {
            $targetLogList[] = $list;
        }

        $msg = [
            "node_uuid" => $params['node_uuid'],
            "target_log_list" => $targetLogList,
        ];
        $return = $this->mbNodeMsg($opName, $params['node_uuid'], json_encode($msg), true);
        $mbResult = $return;
        $logPath = $mbResult['msg']['log_located_path'] . '/system_log.tar.gz';
        if ($mbResult['result']) {
            $systemHandler = new \app\v1\system\v0\logic\Index();
            $nodeUuid = (new Node())->getLocalNodeUUID();
            $url = $systemHandler->groupUnifyDownloadUrl($nodeUuid, $logPath);
            return $this->muOpResult($mbResult['result'], xphp_get_lang('WEB_LOG_PACKAGE_FILE'), "", "", 0, $url);
        } else {
            return $this->muOpResult($mbResult['result'], xphp_get_lang('WEB_LOG_PACKAGE_FILE'));
        }
    }

    /**
     * 获取节点系统日志列表
     * @param array $params 参数
     * @return array
     */
    public function getNodeSystemLogList($params)
    {
        $opName = "NODE_SYS_OP_GET_SYSTEM_LOG_LIST";
        $nodeUuid = $params['node_uuid'];
        $start = intval($params['offset']);
        $length = $params['limit'];
        $sort = $params['sort'];
        $sortType = $params['order'];
        $msg = ["node_uuid" => $nodeUuid];
        $return = $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true);
        $rows = $return['msg']['log_list'];
        $total = count($rows);

        $sortOrder = SORT_ASC;
        if ("desc" == $sortType) {
            $sortOrder = SORT_DESC;
        }
        $ids = array_column($rows, 'pack_name'); // 提取 id 字段作为排序依据
        if ($sort == 'last_modify_time') {
            $ids = array_column($rows, 'last_modify_time');
        }

        array_multisort($ids, $sortOrder, $rows); // 按 id 升序排列
        $lengthNum = $start + $length;
        $records = [];
        for ($i = $start; $i < $lengthNum; $i++) {
            if (empty($rows[$i]))
                continue;
            $records[] = array(
                'last_modify_time' => $rows[$i]['last_modify_time'],
                'pack_name' => $rows[$i]['pack_name'],
                'pack_size' => v1_calsize($rows[$i]['pack_size'], true),
            );
        }

        return [
            'total' => $total,
            'rows' => $records,
        ];
    }

    // ---------------系统日志----------------
    // ---------------高可用日志----------------
    /**
     * 获取高可用日志
     * @param array $params     参数
     * @param bool  $exportFlag 是否导出
     * @return array
     */
    public function getHaLogList(array $params, bool $exportFlag = false)
    {
        $sql = "SELECT bcha.id, bcha.task_uuid, bcha.description_key, bcha.description_param, bcha.ha_type, bcha.ha_rr_type, bcha.ha_time, bcha.error_code, bcha.error_detail, bcha.log_level, bcha.task_name, bht.user_uuid FROM bd_cluster_ha_log bcha LEFT JOIN bd_history_task bht ON bcha.task_uuid = bht.task_uuid  WHERE 1=1 GROUP BY bcha.id";
        $sqlCount = "SELECT COUNT(*) AS total FROM bd_cluster_ha_log WHERE 1=1 ";
        if (isset($params['task_name']) && $params['task_name']) {
            $taskName = '%' . v1_escape_wildcard($params['task_name']) . '%';
            $sql .= " AND task_name LIKE '{$taskName}' ";
            $sqlCount .= " AND task_name LIKE '{$taskName}' ";
        }

        $haLogCount = $this->dbSelect($sqlCount);
        if (!is_array($haLogCount)) {
            $haLogCount = [['total' => 0]];
        }

        $allowSortField = [
            'task_name' => 'task_name',
            'ha_type' => 'ha_type',
            'ha_rr_type' => 'ha_rr_type',
            'ha_time' => 'ha_time',
            'log_id' => 'id',
            'log_level' => 'log_level',
        ];
        $sort = $allowSortField[$params['sort'] ?? 'ha_time'] ?? 'ha_time';
        $order = strtoupper($params['order'] ?? 'DESC') === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sort $order ";
        if (!$exportFlag) {
            $offset = $params['offset'] ?? 0;
            $limit = $params['limit'] ?? 20;
            $sql .= " LIMIT $offset, $limit ";
        }

        $haLogData = $this->dbSelect($sql);
        if (!is_array($haLogData)) {
            $haLogData = [];
        }

        $rows = [];

        $errorConf = xphp_get_config('error');
        foreach ($haLogData as $haLog) {
            $error = '';
            if ($haLog['error_code']) {
                $error = $errorConf['errorCodeDes'][$errorConf['errorCode'][intval($haLog['error_code'])]];
            }
            $rows[] = [
                'log_id' => intval($haLog['id']),
                'task_uuid' => $haLog['task_uuid'],
                'task_name' => $haLog['task_name'] ?: '',
                'ha_type' => intval($haLog['ha_type']),
                'ha_rr_type' => intval($haLog['ha_rr_type']),
                'description' => $this->getHaLogDescription(
                    $haLog['description_key'],
                    $haLog['description_param'],
                    $haLog['error_code'] ?? '',
                    $haLog['log_level'] ?? ''
                ),
                'ha_time' => $haLog['ha_time'],
                'log_level' => intval($haLog['log_level']),
                'log_level_des' => $this->getLogLevelDes($haLog['log_level']),
                'error' => $error,
                'error_code' => intval($haLog['error_code']),
                'error_detail' => $haLog['error_detail'] ?: '',
                'user_uuid' => $haLog['user_uuid'] ?: '',
            ];
        }


        return $this->sendResult('', true, 200, [
            'total' => $haLogCount[0]['total'],
            'rows' => $rows,
        ]);
    }

    /**
     * 删除高可用日志
     * @param array $params 参数
     * @return array
     */
    public function deleteHaLog($params): array
    {
        $opcodeName = 'BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG';
        $operate = (new \app\v1\opcode\ClusterOpcode())->getOpcodeDes($opcodeName);
        // 验证是否能够删除
        $id_arr = "(" . implode(',', $params['ha_log_id_list']) . ")";
        $sql = "SELECT bcha.id, bcha.task_uuid, bcha.description_key, bcha.description_param, bcha.ha_type, bcha.ha_rr_type, bcha.ha_time, bcha.error_code, bcha.error_detail, bcha.log_level, bcha.task_name, bht.user_uuid FROM bd_cluster_ha_log bcha LEFT JOIN bd_history_task bht ON bcha.task_uuid = bht.task_uuid  WHERE 1=1 AND bcha.id IN ({$id_arr})  GROUP BY bcha.id";
        $data = $this->dbSelect($sql);
        // 操作权限判断
        $userArr = implode(',', array_unique(array_column((array) $data, 'user_uuid')));
        if (!empty($userArr)) {
            $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['log']);
        }

        $deleteRet = $this->service('\\app\\v1\\cluster\\v0\\service\\Service')->deleteClusterHaLogService($params['ha_log_id_list']);
        if (!$deleteRet['result']) {
            $this->muOpResult(false, $operate, $deleteRet['msg'], 0, $deleteRet['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG_SUCCESS'));
        }
        return $this->sendResult(xphp_get_lang('WEB_BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG_SUCCESS'));
    }
    /**
     * 获取高可用日志描述
     * @param mixed $descriptionKey 描述信息
     * @param mixed $descriptionParam 描述参数
     * @param mixed $errorCode 错误码
     * @param mixed $logLevel 日志级别
     */
    private function getHaLogDescription($descriptionKey, $descriptionParam, $errorCode, $logLevel)
    {
        $haLogDes = xphp_get_desc('Pf', 'HA_LOG');
        $description = $haLogDes[$descriptionKey] ?? $descriptionKey;
        if ($errorCode) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $errorClass = $this->getLogLevelClass($logLevel);
            $description .= ',[' . '<span  class="' . $errorClass . '">#' . $errorCode . '</span>' . ']' . $errorStr;
        }
        if ($descriptionParam) {
            $pos = strpos($description, '%s');
            if ($pos === false) {
                return $description;
            }
            $descriptionParam = json_decode($descriptionParam, true);
            $descriptionArr = explode('%s', $description);
            $description = '';
            foreach ($descriptionArr as $k => $v) {
                $description .= $v . $this->getEachParamsDes($descriptionParam[$k], $descriptionParam);
            }
        }
        return $description;
    }
    /**
     * 验证删除高可用日志
     * @param mixed $haLogIdList 高可用日志ID列表
     * @return {}
     */
    public function validateDeleteHaLog($haLogIdList)
    {
        $opcodeName = 'BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG';
        $operate = (new \app\v1\opcode\ClusterOpcode())->getOpcodeDes($opcodeName);
        $haReserveStrategy = xphp_get_system_data_safe(4);
        if (!is_array($haReserveStrategy)) {
            return $this->sendResult('');
        }

        $sql = "SELECT id, log_level, ha_time
                FROM bd_cluster_ha_log
                WHERE id IN (" . implode(',', $haLogIdList) . ") ORDER BY ha_time ASC ";
        $haLogList = $this->dbSelect($sql) ?? [];

        // 按天数、按个数保留
        if ($haReserveStrategy[0] == 1) {  // 个数
            $sql = "SELECT * FROM bd_cluster_ha_log ORDER BY ha_time DESC limit 0, {$haReserveStrategy[1]} ";
            $reserveNumList = $this->dbSelect($sql) ?? [];
            // 保留个数内的所有记录的id
            $idArr = array_column($reserveNumList, 'id');
            foreach ($haLogIdList as $v) {
                // 如果id在保留策略的记录内
                if (in_array($v, $idArr)) {
                    $msg = xphp_get_lang('WEB_DB_CLUSTER_HA_LOG_DELETE_ERROR_REMAIN_COUNT');
                    $msg = str_replace('%s', $haReserveStrategy[1], $msg);
                    return $this->muOpResult(false, $operate, $msg);
                }
            }
        } else if ($haReserveStrategy[0] == 2) {  // 天数
            $remainEarliest = strtotime('-' . $haReserveStrategy[1] . ' day');
            $deleteEarliest = strtotime($haLogList[0]['ha_time']);
            if ($deleteEarliest >= $remainEarliest) {  // 删除的最早时间大于保留的最早时间,不能删除
                $msg = xphp_get_lang('WEB_DB_CLUSTER_HA_LOG_DELETE_ERROR_REMAIN_DAY');
                $msg = str_replace('%s', $haReserveStrategy[1], $msg);
                return $this->muOpResult(false, $operate, $msg);
            }
        } else { // 永久保留
            $msg = xphp_get_lang('WEB_LOG_DELETE_CAN_NOT_DELETE_TIPS');
            return $this->muOpResult(false, $operate, $msg);
        }
        return $this->sendResult('');
    }
    /**
     * 导出所有高可用日志
     * @param mixed $params
     * @return void
     */
    public function exportAllHaLog($params)
    {
        $allHaLogList = $this->getHaLogList($params, true)['data']['rows'];
        $title = xphp_get_lang('UI_CLUSTER_HA_LOG');
        $header = [
            'num' => xphp_get_lang('UI_PUBLIC_NUMBER'),
            'task_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'ha_type' => xphp_get_lang('UI_CLUSTER_HA_TYPE'),
            'ha_rr_type' => xphp_get_lang('UI_CLUSTER_HA_RR_TYPE'),
            'status' => xphp_get_lang('UI_PUBLIC_STATUS'),
            'ha_time' => xphp_get_lang('UI_PUBLIC_TIME'),
            'description' => xphp_get_lang('UI_PUBLIC_DESCRIPTION'),
        ];
        $relation = [
            'num' => ['col_name' => 'A', 'width' => 10],
            'task_name' => ['col_name' => 'B', 'width' => 25],
            'ha_type' => ['col_name' => 'C', 'width' => 15],
            'ha_rr_type' => ['col_name' => 'D', 'width' => 15],
            'status' => ['col_name' => 'E', 'width' => 10],
            'ha_time' => ['col_name' => 'F', 'width' => 20],
            'description' => ['col_name' => 'G', 'width' => 60],
        ];
        $exportData = [];
        $haLogTypeDes = xphp_get_desc('Pf', 'HA_LOG_TYPE');
        $haLogRRTypeDes = xphp_get_desc('Pf', 'HA_LOG_RR_TYPE');
        foreach ($allHaLogList as $haLogInfo) {
            $status = $haLogInfo['log_level_des'];
            $exportData[] = [
                'num' => $haLogInfo['log_id'],
                'task_name' => $haLogInfo['task_name'] ?: xphp_get_config('app', 'NULLSPACE'),
                'ha_type' => $haLogTypeDes[$haLogInfo['ha_type']] ?? $haLogTypeDes[0],
                'ha_rr_type' => $haLogRRTypeDes[$haLogInfo['ha_rr_type']] ?? $haLogRRTypeDes[0],
                'status' => $status,
                'ha_time' => $haLogInfo['ha_time'],
                'description' => $haLogInfo['description'],
            ];
        }
        v1_base_export($title, $header, $exportData, $relation);
    }
    // ---------------高可用日志----------------

    /**
     * 获取单个运行日志错误详情
     * @auther chenchao@vinchin.com
     * @param int $runningLogsId 任务运行日志ID
     * @return array
     */
    public function getRunningLogErrorDetail(int $runningLogsId): array
    {
        $sql = "SELECT error_detail FROM bd_task_log WHERE id = ? ";
        $data = $this->dbSelect($sql, [$runningLogsId]);
        if (!is_array($data) || !$data) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA'), false, 0);
        }
        return $this->sendResult('', true, 200, [
            'error_detail' => $data[0]['error_detail'],
        ]);
    }

    /**
     * 获取单个运行日志脚本详情
     * @auther chenchao@vinchin.com
     * @param int $runningLogsId 任务运行日志ID
     * @return array
     */
    public function getRunningLogScriptDetail(int $runningLogsId): array
    {
        $sql = "SELECT script_detail FROM bd_task_log WHERE id =? ";
        $data = $this->dbSelect($sql, [$runningLogsId]);
        if (!is_array($data) || !$data) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA'), false, 0);
        }
        return $this->sendResult('', true, 200, [
            'script_detail' => $data[0]['script_detail'],
        ]);
    }

    /**
     * 获取虚拟机模块的子模块类型:1虚拟机，2私有云，3公有云
     * @param int $subModuleType
     * @return int
     */
    private function getVmSubModuleType(int $subModuleType): int
    {
        if (in_array($subModuleType, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])) {
            return 2;
        }
        if (in_array($subModuleType, xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])) {
            return 3;
        }
        return 1;
    }


    //获取系统操作日志类型
    private function getOpType($des)
    {
        $res = 'UI_PLATFORM_SYSTEM_SET';
        if (strpos($des, 'SYSTEM_USER_LOGIN') !== false) {
            $res = 'UI_PLATFORM_SYSTEM_LOGIN'; // 登录
        } else if (strpos($des, 'SYSTEM_USER_LOGINOUT') !== false) {
            $res = 'UI_USER_LOGIN_OUT'; // 退出登录
        } else if (strpos($des, 'SYSTEM_USER') !== false) {
            $res = 'UI_ORGAN_USER_MANAGE'; // 用户管理
        } else if (strpos($des, 'BD_SYSTEMLOG_DESC_KEY_NODE') !== false) {
            $res = 'UI_VCENTER_ALLOCATION_NODE'; // 备份节点
        } else if (strpos($des, 'BD_SYSTEMLOG_DESC_KEY_AGENT_') !== false) {
            $res = 'UI_CLIENT_MANAGER'; // 客户端
        } else if (strpos($des, 'BD_SYSTEMLOG_DESC_KEY_STORAGE') !== false) {
            $res = 'UI_DATACENTER_BACKUP_STORAGE'; // 存储
        } else if (strpos($des, 'BD_SYSTEMLOG_DESC_KEY_PRODUCTION_STORAGE') !== false) {
            $res = 'UI_PRODUCTION_STORAGE_NAME'; // 生产存储
        }
        return xphp_get_lang($res);
    }

    /**
     * 得到日志等级
     * @param int $logLevel
     * @return string
     */
    public function getLogLevelDes($logLevel)
    {
        $logLevel = intval($logLevel);
        $confDes = xphp_get_desc('Pf', 'LOG_LEVEL_DES');
        return $confDes[$logLevel];
    }

    /**
     * 得到日志描述信息
     * @param int    $logType          日志类型
     * @param int    $errorCode        错误码
     * @param string $description       描述
     * @param string $descriptionParam 描述参数
     * @param string $logLevel         日志等级
     * @param string $moduleType       模块类型
     * @param string $subModuleType    子模块类型
     * @param mixed $logId             日志ID
     * @param mixed $errorDetail       错误详情
     * @param mixed $scriptDetail      脚本详情
     * @return string
     */
    public function getLogDescription(
        $logType,
        $errorCode,
        $description,
        $descriptionParam,
        $archive_flag = false,
        $logLevel = null,
        $moduleType = 0,
        $subModuleType = 0,
        $logId = null,
        $errorDetail = null,
        $scriptDetail = null
    ) {
        $logTypeArr = xphp_get_config('log', 'LOGTYPE');
        if ($logType == $logTypeArr['SYSTEM']) {
            $confDes = xphp_get_config('log_system');
        } elseif ($logType == $logTypeArr['TASK']) {
            $confDes = xphp_get_config('log_task');
        } else {
            return '';
        }
        $desStr = $confDes[$description];
        if ($errorCode) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $errorClass = $this->getLogLevelClass($logLevel);
            $desStr .= ',[' . '<span  class="' . $errorClass . '">#' . $errorCode . '</span>' . ']' . $errorStr;
        }
        // 公有云替换描述中的"虚拟机"为"实例","磁盘"为"卷"
        if (xphp_get_config('module', 'MODULE_TYPE')['VM'] == $moduleType && in_array($subModuleType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            $desStr = str_replace(xphp_get_lang('WEB_PLATFORM_DES_VM'), xphp_get_lang('WEB_PLATFORM_DES_INSTANCE'), $desStr);
            $desStr = str_replace(xphp_get_lang('WEB_PLATFORM_DES_DISK'), xphp_get_lang('WEB_PLATFORM_DES_VOL'), $desStr);
        }
        // 私有云替换描述中的"虚拟机"为"实例"
        if (xphp_get_config('module', 'MODULE_TYPE')['VM'] == $moduleType && in_array($subModuleType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'])) {
            $desStr = str_replace(xphp_get_lang('WEB_PLATFORM_DES_VM'), xphp_get_lang('WEB_PLATFORM_DES_INSTANCE'), $desStr);
        }
        // 多个参数合并到一个%s的情况
        $multiInOne = ['VM_TASK_DESC_KEY_BACKUP_TASK_ADD_VM', 'VM_TASK_DESC_KEY_BACKUP_TASK_REMOVE_VM'];
        if ($descriptionParam) {
            $param = json_decode($descriptionParam, true);
            if (in_array($description, $multiInOne)) {
                $desArr = explode("'%s'", $desStr);
                $desStr = "";
                $desStr .= $desArr[0];
                $paramArr = [];
                foreach ($param as $p) {
                    $paramArr[] = "'" . $this->getEachParamsDes($p, $param) . "'";
                }
                $desStr .= implode(',', $paramArr);
            } else {
                $moduleStr = '';
                $foundIndex = null; // 初始化一个变量来存储找到的索引
                foreach ($param as $index => $item) {
                    if (strpos($item, 'module_type') === 0) {
                        //找到参数为module_type的进行单独记录
                        $foundIndex = $index;
                        $moduleStr .= $item;
                    }
                    if (strpos($item, 'submodule_type') === 0) {
                        //找到参数为submodule_type的进行拼接
                        $submodule = explode(':', $item);
                        $moduleStr .= '-' . $submodule[1];
                        //将submodule_type的值拼接到module_type后面，用‘-’隔开
                        $param[$foundIndex] = $moduleStr;
                        array_splice($param, $index, 1);
                    }
                }
                $desArr = explode('%s', $desStr);
                $desStr = '';
                foreach ($desArr as $k => $v) {
                    $keyParam = $param[$k];
                    if ($description == 'BD_SYSTEMLOG_DESC_KEY_SET_TIMEPOINT_IMPORTANCE_FLAG' && $k == 3) {
                        $keyParam = str_replace('1', xphp_get_lang('WEB_TIMEPOINT_WEB_OP_SET'), $keyParam);
                        $keyParam = str_replace('2', xphp_get_lang('UI_VM_MACHINE_SURE_CANCEL'), $keyParam);
                    }
                    $desStr .= $v . $this->getEachParamsDes($keyParam, $param);
                }
            }
        }
        // 如果是归档任务，将副本替换为归档
        if ($archive_flag) {
            $desStr = str_replace(xphp_get_lang('WEB_PLATFORM_DES_COPY'), xphp_get_lang('UI_PLATFORM_ARCHIVE'), $desStr);
        }

        /**
         * @auther chenchao@vinchin.com
         * @function Oracle的rman错误日志信息
         */
        if ($errorDetail) {
            try {
                $errorDetail = json_decode($errorDetail, true);
                if ($errorDetail) {
                    $desStr .= ', <u class="text-success error-detail-link" data-id="' . $logId . '" style="cursor: pointer">'
                        . xphp_get_lang('WEB_LOG_TASK_ERROR_DETAIL_LINK') . '</u>';
                }
            } catch (\Exception $e) {}
        }

        /**
         * @auther chenchao@vinchin.com
         * @function 脚本详情
         */
        if ($scriptDetail) {
            try {
                $scriptDetail = json_decode($scriptDetail, true);
                if ($scriptDetail) {
                    $desStr .= ', <u class="text-success script-detail-link" data-id="' . $logId . '" style="cursor: pointer">'
                        . xphp_get_lang('WEB_LOG_TASK_SCRIPT_DETAIL_LINK') . '</u>';
                }
            } catch (\Exception $e) {}
        }

        return $desStr;
    }

    /**
     * 得到日志错误对应的类型
     * @param $logLevel 日志级别
     * @return string
     */
    private function getLogLevelClass($logLevel): string
    {
        $logClass = 'font-green';   //一般日志
        $logLevelArr = xphp_get_config('log', 'LOGLEVEL');
        if ($logLevelArr['WARN'] == $logLevel) {
            $logClass = 'font-yellow-gold';
        } elseif ($logLevelArr['ERROR'] == $logLevel) {
            $logClass = 'font-red-thunderbird';
        }
        return $logClass;
    }

    /**
     * 得到每一项参数的描述
     * @param string  $eachParams       参数
     * @param  $descriptionParam 参数
     * @param boolean $classShowFlag    是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    public function getEachParamsDes($eachParams, $descriptionParam, $classShowFlag = true)
    {
        if (empty($eachParams)) {
            return '';
        }
        $des = '';
        $arr = explode(':', $eachParams, 2);
        switch ($arr[0]) {
            case 'S':
                if ($classShowFlag) {
                    $des = '<span  class="font-green">' . htmlspecialchars(html_entity_decode($arr[1])) . '</span>';
                } else {
                    $des = htmlspecialchars(html_entity_decode($arr[1]));
                }
                break;
            case 'module_type':
                //解析3-4这种模块类型和子模块类型，如果submodule没有，则直接取
                $module = explode('-', $arr[1])[0];
                $submodule = explode('-', $arr[1])[1];
                if (empty($submodule)) {
                    $des = xphp_get_desc('Pf', 'MODULE_TYPE_DES')[$module];
                } else {
                    $des = $this->getSubmoduleDes($module, $submodule);
                }
                break;
            case 'backup_mode':
                $des = $this->logBackupModeDes($arr[1], $descriptionParam);
                break;
            default:
                break;
        }
        return $des;
    }

    /**
     * 获取文件子模块描述
     * @param string $submodule
     * @return string
     */
    private function getSubmoduleDes($module, $submodule)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        switch ($module) {
            case '0':
                $des = '';
                break;
            case $MODULE['VM']:
                $des = xphp_get_desc('Pf', 'SUB_MODULE_DES_VM')[$submodule];
                break;
            case $MODULE['FS']:
                $des = xphp_get_desc('Pf', 'FS_SUBMODULE_TYPE_DES')[$submodule];
                break;
            case $MODULE['NAS']:
                $des = xphp_get_desc('Pf', 'MODULE_TYPE_DES')[$module];
                break;
            case $MODULE['OS']:
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP');
                if ($submodule == 1) {
                    $des = xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP');
                }
                break;
        }
        return $des;
    }

    /**
     * 得到日志备份模式描述
     * @param int $index            模块定义键的位置
     * @param $descriptionParam 描述
     * @return string
     */
    private function logBackupModeDes($index, $descriptionParam)
    {
        $desConf = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $moduleType = explode(':', $descriptionParam[1], 2);
        // 子模块id默认为0 不显示未知
        if (($moduleType[0] == 'module_type' && $moduleType[1] == xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP']) || ($moduleType[0] == 'backup_mode' && $moduleType[1] == xphp_get_config('module', 'MODULE_TYPE')['UNKNOWN'])) {
            return '';
        } else {
            return $desConf[intval($index)] ?? '';
        }
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName
     * @param json $msg
     * @return string
     */
    private function unifyMsg($opName, $jsonMsg)
    {
        $mbResult = $this->mbPFMsg($opName, $jsonMsg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        $this->writeManualOpLog($opName, json_decode($jsonMsg, true), $mbResult);
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 手动写入用户操作日志
     * @param mixed $opName
     * @param mixed $msg
     * @param mixed $mbResult
     * @return void
     */
    private function writeManualOpLog($opName, $msg, $mbResult)
    {
        $key = "";
        $params = array(count($msg['id_list']));
        if ($opName == "BD_LOG_OP_TASK_DELETE") {
            $key = "SYSTEM_LOG_DELETE_TASK_LOG";
        } else if ($opName == "BD_LOG_OP_SYSTEM_DELETE") {
            $key = "SYSTEM_LOG_DELETE_SYSTEM_LOG";
        }

        if ($mbResult['result']) {
            $this->systemLog($key, $params);
        } else {
            $this->systemLog($key, $params, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
        }
    }


    /**
     * 
     * 根据类型得到日志配置文件
     * @param $logType 日志类型   系统日志2/任务日志1
     * @param mixed $logType
     */
    public function getLogConf($logType)
    {
        if ($logType == xphp_get_config('log', 'LOGTYPE')['SYSTEM']) {
            return xphp_get_config('log_system');
        } elseif ($logType == xphp_get_config('log', 'LOGTYPE')['TASK']) {
            return xphp_get_config('log_task');
        }
    }
    public function getLogDescriptionNotice($logType, $errorCode, $description, $descriptionParam, $taskName = '', $classShowFlag = true)
    {
        $confDes = $this->getLogConf($logType);
        $desStr = $confDes[$description];
        if ($errorCode) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $desStr .= ",[#" . $errorCode . "]" . $errorStr;
        }
        if ($descriptionParam) {
            $param = json_decode($descriptionParam, true);
            $desArr = explode("%s", $desStr);
            $moduleStr = '';
            $foundIndex = null; // 初始化一个变量来存储找到的索引
            foreach ($param as $index => $item) {
                if (strpos($item, 'module_type') === 0) {
                    //找到参数为module_type的进行单独记录
                    $foundIndex = $index;
                    $moduleStr .= $item;
                }
                if (strpos($item, 'submodule_type') === 0) {
                    //找到参数为submodule_type的进行拼接
                    $submodule = explode(':', $item);
                    $moduleStr .= '-' . $submodule[1];
                    //将submodule_type的值拼接到module_type后面，用‘-’隔开
                    $param[$foundIndex] = $moduleStr;
                    array_splice($param, $index, 1);
                }
            }
            $desArr = explode('%s', $desStr);
            $desStr = '';
            foreach ($desArr as $k => $v) {
                $desStr .= $v . $this->getEachParamsDes($param[$k], false, $classShowFlag);
            }
        }
        //如果是有任务名
        if (!empty($taskName)) {
            $desStr = "[" . $taskName . "]" . $desStr;
        }
        return $desStr;
    }
}
