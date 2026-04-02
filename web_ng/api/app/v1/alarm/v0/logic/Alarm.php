<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-05-26 15:37:36
 * @LastEditTime: 2026-03-06 17:54:55
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */

namespace app\v1\alarm\v0\logic;

use app\v1\common\logic\Base;
use app\v1\log\v0\logic\Index as LogInfo;
use app\v1\opcode\PfOpcode;
use app\v1\user\v0\logic\Role;
use app\v1\copy\v0\logic\CopyJobInfo;
use app\v1\system\v0\logic\Notice;
use app\v1\job\v0\logic\JobInfo;
use app\v1\common\logic\Report as ReportHandler;
use app\v1\filecopy\v0\logic\FileCopyJobInfo;

/**
 * note          系统告警
 * @author       liushuai@vinchin.com
 * @date         2023/10/24 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Alarm extends Base
{

    /**
     * 获取任务告警列表
     * @param array $params 数据
     * @return object  任务告警列表对象
     */
    public function getJobAlarmList($params = [], $isExport = false): array
    {
        $taskName = $params['search'];
        $sqlData = "SELECT
                        bta.task_alarm_id,
                        bta.task_name,
                        bta.user_name,
                        bta.task_type,
                        bta.module_type,
                        bta.alarm_level,
                        bta.description_key,
                        bta.history_uuid,
                        bta.description_param,
                        unix_timestamp( bta.alarm_time ) alarm_time,
                        bta.solved_flag,
                        bta.error_code,
                        bta.submodule_type,
                        bta.user_uuid 
                    FROM
                        bd_task_alarm bta
                    WHERE
                        bta.alarm_level != 1";
        $sqlCount = "SELECT count(bta.task_alarm_id) as total from bd_task_alarm bta where bta.alarm_level != 1 ";
        $sqlParams = [];
        $sortArr = [
            'job_name' => 'bta.task_name',
            'alarm_id' => 'bta.task_alarm_id',
            'user_name' => 'bta.user_name',
            'job_type_value' => 'bta.task_type',
            'module_type_value' => 'bta.module_type',
            'status' => 'bta.alarm_level',
            'alarm_time' => 'bta.alarm_time',
            'solved_flag' => 'bta.solved_flag'
        ];
        $sql = '';
        $level = $params['alarm_level'];
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $vmGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        $TASK_TYPE_DES = xphp_get_desc('Pf', 'TASKTYPEDES');
        $MODULE_TYPE_DES = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $ALARM_LEVEL_DES = xphp_get_desc('Pf', 'ALARM_LEVEL_DES');
        $ALARM_RESPOND_DES = xphp_get_desc('Pf', 'ALARM_RESPOND_DES');
        $confDes = xphp_get_config('log_task');
        $errorConf = xphp_get_config('error');
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['alarm']);
            $sql .= " and bta.user_uuid in ({$userUuidSql}) ";
        }

        if (!empty($level)) {
            $sql .= " and bta.alarm_level = '{$level}' ";
        }

        $search = $params['search'];
        $hypervisor = intval($params['vm_type']);
        $dbType = intval($params['db_type']);
        $taskType = intval($params['job_type']);
        $moduleType = $params['module_type'];
        $subModuleType = intval($params['sub_module_type']);
        $taskName = $params['job_name'];
        $alarmLevel = intval($params['alarm_level']);
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $node_uuid = $params['node_uuid'];
        $alarmId = $params['task_alarm_id'];
        $isCurrentJob = $params['current_job_flag']; // 是否当前任务的告警，1是，2不是
        //虚拟化类型
        if ($moduleType == $moduleTypeArr['VM'] && !empty($hypervisor)) {
            $sql .= " and bta.submodule_type = '{$hypervisor}' ";
        }
        //数据库类型
        if ($moduleType == $moduleTypeArr['DB'] && !empty($dbType)) {
            $sql .= " and bta.submodule_type = '{$dbType}' ";
        }

        //模块类型
        if (!empty($moduleType)) {
            if ($subModuleType == 3 && $moduleType == $moduleTypeArr['VM']) { //公有云
                $publicCloud = "(" . implode(',', $vmGroup['publiccloud']) . ")";
                $sql .= " and bta.module_type = '{$moduleType}' and bta.submodule_type in  " . $publicCloud . " ";
            } else if ($subModuleType == 2 && $moduleType == $moduleTypeArr['VM']) { // 私有云
                $privateCloud =  "(" . implode(',', $vmGroup['openstack']) . ")";
                $sql .= " and bta.module_type = '{$moduleType}' and bta.submodule_type in  " . $privateCloud . " ";
            } else if ($subModuleType == 1 && $moduleType == $moduleTypeArr['VM']) { // 虚拟机
                $publicCloud = "(" . implode(',', $vmGroup['publiccloud']) . ")";
                $vmHypervisor = array_diff(xphp_get_config('vm', 'VMHYPERVISORTYPE'), $vmGroup['publiccloud'], $vmGroup['openstack']);
                $vmHypervisor = "(" . implode(',', $vmHypervisor) . ")";
                $sql .= " and bta.module_type = '{$moduleType}' and bta.submodule_type in  " . $vmHypervisor . " and bta.submodule_type not in " . $publicCloud . " ";
            } else {
                $moduleTypeArr = explode(',', $moduleType);
                $moduleTypeStr = "(" . implode(',', $moduleTypeArr) . ")";
                $sql .= " and bta.module_type in  " . $moduleTypeStr . " ";
                if ($moduleType != $moduleTypeArr['DB'] && !empty($subModuleType)) {
                    $sql .= " and bta.submodule_type = '{$subModuleType}' ";
                }
            }
        }
        //任务类型
        if (!empty($taskType)) {
            $sql .= " and bta.task_type = '{$taskType}'";
        }

        //告警等级
        if (!empty($alarmLevel)) {
            $sql .= " and bta.alarm_level = '{$alarmLevel}' ";
        }

        //节点唯一标识
        if (!empty($node_uuid)) {
            $sql .= " and bta.node_uuid = '{$node_uuid}' ";
        }

        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            $sql .= " and bta.alarm_time between '{$startTime}' and '{$endTime}' ";
        }

        //告警ID查询
        if (!empty($alarmId)) {
            $sql .= " and bta.task_alarm_id = '{$alarmId}' ";
        }

        //是否当前未删除任务的告警
        if (!empty($isCurrentJob)) {
            $setFlag = xphp_get_config('app', 'FLAG')['SET'];
            $unsetFlag = xphp_get_config('app', 'FLAG')['UNSET'];
            $sql2 = "SELECT task_uuid from bd_task where delete_flag = {$unsetFlag}";
            $taskList = $this->dbSelect($sql2);
            if (is_array($taskList) && $taskList) {
                $taskUuidList = array_values(array_unique(array_column($taskList, 'task_uuid')));
            }
            $taskUuids = "'" . implode("', '", $taskUuidList) . "'";
            if ($isCurrentJob == $setFlag) {
                $sql .= " and bta.task_uuid in ($taskUuids) ";
            } else if ($isCurrentJob == $unsetFlag) {
                $sql .= " and bta.task_uuid not in ($taskUuids) ";
            }
        }

        if ($this->checkEmpty($taskName)) {
            $sql .= " and bta.task_name like '%{$taskName}%' ";
        }
        if ($this->checkEmpty($search)) {
            $sql .= " and bta.task_name like '%{$search}%' ";
        }
        $count = $this->dbSelect($sqlCount . $sql, $sqlParams) ?? [];
        $sql .= ' GROUP BY bta.task_alarm_id';
        if ((!empty($params['sort']) && in_array(strtolower($params['order']), ['asc', 'desc']))) {
            // 排序
            $sql .= " order by " . $sortArr[$params['sort']] . ' ' . $params['order'];
        }
        if (!$isExport) {
            $sql .= " limit {$params['offset']} , {$params['limit']} ";
        }
        $data = $this->dbSelect($sqlData . $sql, $sqlParams) ?? [];
        $history_uuid = array_column((array)$data, 'history_uuid');
        $history_list = $this->getHistoryList($history_uuid);
        $records = $rows = [];
        $num = intval($count[0]['total']);
        foreach ($data as $d) {
            $des = $this->getLogDescription(
                $d['error_code'],
                $d['description_key'],
                $d['description_param'],
                $confDes,
                $errorConf,
            );
            // 公有云替换描述中的"虚拟机"为"实例"
            if (in_array($d['submodule_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
                $des = str_replace(xphp_get_lang('WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER'), xphp_get_lang('WEB_PLATFORM_DES_INSTANCE'), $des);
            }
            $moduleTypeAli = $d['module_type'];
            $subModuleTypeAli = $d['submodule_type'];
            if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['VM']) {
                $subModuleTypeAli = $this->getVmSubModuleType($d['submodule_type'], $vmGroup);
            } else if ($d['module_type'] == '30') {
                if (!empty($history_list[$d['history_uuid']])) {
                    // 解析下数据验证的模块和子模块类型
                    $sureModule = array_filter(explode(',', $history_list[$d['history_uuid']]));
                    $moduleTypeAli = [];
                    $subModuleTypeAli = [];
                    foreach ($sureModule as $items) {
                        $itemsArr = explode('-', $items);
                        $moduleTypeAli[] = $itemsArr[0] ?? 0;
                        $subModuleTypeAli[] = $itemsArr[1] ?? 0;
                    }
                    $moduleTypeAli = implode(',', $moduleTypeAli);
                    $subModuleTypeAli = implode(',', $subModuleTypeAli);
                }
            } else if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['DB']) {
                $subModuleTypeAli = '';
            }
            $rows[] = [
                'id' => intval(++$params['offset']),
                'alarm_id' => intval($d['task_alarm_id']),
                'job_name' => $d['task_name'],
                'module_type_value' => (new JobInfo())->getObjects($moduleTypeAli, $subModuleTypeAli),
                'module_type' => $MODULE_TYPE_DES[intval($d['module_type'])],
                'sub_module_type_value' => in_array($d['module_type'], [$moduleTypeArr['FS'], $moduleTypeArr['OS']]) ? $d['submodule_type'] : $this->getVmSubModuleType($d['submodule_type'], $vmGroup),
                'job_type_value' => intval($d['task_type']),
                'job_type' => $TASK_TYPE_DES[intval($d['task_type'])],
                'user_name' => $d['user_name'],
                'alarm_time' => date('Y-m-d H:i:s', intval($d['alarm_time'])),
                'status' => intval($d['alarm_level']),
                'status_des' => $ALARM_LEVEL_DES[intval($d['alarm_level'])],
                'description' => $des,
                'solved_flag' => intval($d['solved_flag']),
                'solved_flag_des' => $ALARM_RESPOND_DES[intval($d['solved_flag'])],
                "history_uuid" => $d['history_uuid'],
                "user_uuid" => $d['user_uuid'],
            ];
        }

        $records['rows'] = $rows;
        $records['total'] = $num;

        return $records;
    }
    /**
     * 获取历史任务列表信息
     * @param mixed $history_uuid
     * @return {}
     */
    private function getHistoryList($history_uuid){
        $sqlHistory = "SELECT
                        bht.history_uuid,
                        bht.module_source_arr
                    FROM
                        bd_history_task bht
                    WHERE
                        bht.history_uuid IN ('" . implode("','", $history_uuid) . "')";
        $data = $this->dbSelect($sqlHistory) ?? [];
        $result = [];
        if(!empty($data)){
            foreach ($data as $d) {
                $result[$d['history_uuid']] = $d['module_source_arr'];
            }
        }
        return $result;
    }
    public function getLogDescription($errorCode, $description, $descriptionParam, $confDes, $errorConf): string
    {
        $desStr = $confDes[$description];
        if ($errorCode) {
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $desStr .= ',[' . '<span  class="font-green">#' . $errorCode . '</span>' . ']' . $errorStr;
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

        return $desStr;
    }

    /**
     * 得到每一项参数的描述
     * @param string  $eachParams       参数
     * @param  $descriptionParam 参数
     * @param boolean $classShowFlag    是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    private function getEachParamsDes($eachParams, $descriptionParam, $classShowFlag = true): string
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
     * 得到日志备份模式描述
     * @param int $index            模块定义键的位置
     * @param $descriptionParam 描述
     * @return string
     */
    private function logBackupModeDes($index, $descriptionParam)
    {
        $desConf = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $moduleType = explode(':', $descriptionParam[1], 2);
        if ($moduleType[0] == 'module_type' && $moduleType[1] == xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP']) {
            return '';
        } else {
            return $desConf[intval($index)] ?? '';
        }
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
     * 获取虚拟机模块的子模块类型:1虚拟机，2私有云，3公有云
     * @param int $subModuleType
     * @return int
     */
    private function getVmSubModuleType(int $subModuleType, $vmGroup): int
    {
        if (in_array($subModuleType, $vmGroup['openstack'])) {
            return 2;
        }
        if (in_array($subModuleType, $vmGroup['publiccloud'])) {
            return 3;
        }
        return 1;
    }

    /**
     * 获取系统告警列表
     * @param array $params
     * @author wuyihang@vinchin.com
     * @date 2024/4/12
     * @return array
     */
    public function getSystemAlarmList($params, $isExport = false)
    {
        $start = intval($params['offset']);
        $length = $params['limit'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $level = $params['level'];

        $sqlData = "SELECT system_alarm_id, alarm_level, description_key, description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code 
                from bd_system_alarm where system_alarm_id is not null";
        $sqlCount = "SELECT count(system_alarm_id) as total from bd_system_alarm where system_alarm_id is not null";
        $sql = '';
        $sqlParams = [];
        if (!empty($level)) {
            $sql .= " and alarm_level = '{$level}' ";
        }
        $alarmLevel = intval($params['alarmLevel']);
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $alarmId = $params['alarm_id'];
        //告警等级
        if (!empty($alarmLevel)) {
            $sql .= " and alarm_level = '{$alarmLevel}' ";
        }

        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            $sql .= " and alarm_time between '{$startTime}' and '{$endTime}' ";
        }

        //告警ID查询
        if (!empty($alarmId)) {
            $sql .= " and system_alarm_id = '{$alarmId}' ";
        }

        $count = $this->dbSelect($sqlCount . $sql, $sqlParams) ?? [];

        if (!empty($sortColumn) && !empty($sortType)) {
            if ($sortColumn == 'alarm_id') {
                $sortColumn = 'system_alarm_id';
            }
            $sql .= "  order by $sortColumn $sortType";
        }
        if (!$isExport) {
            $sql .= " limit ? , ? ";
            $sqlParams = array_merge($sqlParams, [$start, $length]);
        }
        $data = $this->dbSelect($sqlData . $sql, $sqlParams);
        $records = [
            'rows' => [],
            'total' => $count[0]['total'],
        ];
        $confDes = xphp_get_config('log_system');
        $errorConf = xphp_get_config('error');
        foreach ($data as $d) {
            $records["rows"][] = [
                'id' => ++$start,
                'alarm_id' => $d['system_alarm_id'],
                'alarm_level' => intval($d['alarm_level']),
                'alarm_time' => date('Y-m-d H:i:s', $d['alarm_time']),
                'description' => $this->getLogDescription(
                    $d['error_code'],
                    $d['description_key'],
                    $d['description_param'],
                    $confDes,
                    $errorConf,
                ),
                'solved_flag' => v1_parse_flag_to_bool($d['solved_flag']),
                'system_alarm_id' => intval($d['system_alarm_id']),
                'level_des' => xphp_get_desc('Pf', 'ALARM_LEVEL_DES')[intval($d['alarm_level'])],
                'solved_des' => xphp_get_desc('Pf', 'ALARM_RESPOND_DES')[intval($d['solved_flag'])],
            ];
        }
        $records['total'] = $count[0]['total'];
        return $records;
    }

    /**
     * 得到任务日志模块类型信息
     * @param int $module       模块号
     * @param int $subModule    子模块号
     * @param int $taskType    任务类型
     * 告警/存储要调用
     */
    public function getModuleTypeDes($module, $subModule = null, $taskType = null)
    {
        $MODULE_TYPE_DES = xphp_get_config('Pf', 'MODULE_TYPE_DES');
        $des = $MODULE_TYPE_DES[intval($module)];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskTypeArr = xphp_get_config('task', 'TASK_TYPE');
        if ($module == $moduleType['BACKUP_COPY_CLIENT']) {
            if ($taskType) {
                if ($taskType == $taskTypeArr['ARCHIVE'] || $taskType == $taskTypeArr['ARCHIVE_FETCH']) {
                    $des = xphp_get_lang('UI_PLATFORM_ARCHIVE');
                }
            }
        }
        if ($subModule) {
            //添加子模块,暂时添加虚拟机子模块,后期TODO涉及到数据库子模块
            if ($module == $moduleType['VM']) {
                $vmGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');

                if (in_array($subModule, $vmGroup['openstack'])) {
                    // 副本没有私有云
                    if ($taskType == $taskTypeArr['ARCHIVE'] || $taskType == $taskTypeArr['ARCHIVE_FETCH'] || $taskType == $taskTypeArr['BACKUP_COPY'] || $taskType == $taskTypeArr['BACKUP_COPY_FETCH']) {
                        $des = xphp_get_lang('WEB_PLATFORM_DES_VM');
                    } else {
                        $des = xphp_get_lang('WEB_PLATFORM_DES_PRIVATE_CLOUD');
                    }
                } elseif (in_array($subModule, $vmGroup['publiccloud'])) {
                    $des = xphp_get_lang('WEB_PLATFORM_DES_PUBLIC_CLOUD');
                } else {
                    $des = xphp_get_lang('WEB_PLATFORM_DES_VM');
                }
            }
        }
        return $des;
    }

    /**
     * 获取虚拟机子模块
     * @param $module
     * @param $subModule
     * @return int
     */
    public function getSubModuleTypeValue($module, $subModule = null): int
    {
        if ($module == xphp_get_config('module', 'MODULE_TYPE')['VM']) {
            $vmGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
            if (in_array($subModule, $vmGroup['openstack'])) {
                return 2;
            } elseif (in_array($subModule, $vmGroup['publiccloud'])) {
                return 3;
            } else {
                return 1;
            }
        }
        return 0;
    }

    /**
     * 获取任务告警详情信息
     * @param {} $id 任务告警uuid
     * @return array  任务告警详情
     */
    public function getJobAlarmDetail($id)
    {
        //获取任务告警是否已响应标记
        $solvedFlag = $this->getTaskAlarmSolvedFlag($id);
        $flag = xphp_get_config('app', 'FLAG');
        $vmGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        if ($solvedFlag == $flag['UNSET']) {
            //如果是未标记的才标记成已标记，打开详情直接标记为已响应
            $nowDate = date('Y-m-d H:i:s');
            $sql = "UPDATE bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where task_alarm_id = ? ";
            $sqlParams = array($flag['SET'], $nowDate, xphp_get_user_info()['userName'], $id);
            $result = $this->dbExec($sql, $sqlParams);
        }
        $sql = "SELECT
                    bta.task_alarm_id,
                    bta.alarm_level,
                    bta.description_key,
                    bta.description_param,
                    bta.alarm_time,
                    bta.task_uuid,
                    bta.task_name,
                    bta.task_type,
                    bta.module_type,
                    bta.submodule_type,
                    bta.node_uuid,
                    bta.node_name,
                    bta.storage_name,
                    bta.solved_flag,
                    bta.solved_time,
                    bta.solved_username,
                    bta.email_send_flag,
                    bta.sms_send_flag,
                    bta.wechat_send_flag,
                    bta.enterprise_wechat_send_flag AS wechat2_send_flag,
                    bta.error_code,
                    bta.task_log_path,
                    bht.module_source_arr,
                    bta.history_uuid 
                FROM
                    bd_task_alarm bta
                    LEFT JOIN bd_history_task bht ON bta.history_uuid = bht.history_uuid 
                WHERE
                    task_alarm_id = ?";

        $data = $this->dbSelect($sql, array($id)) ?? [];
        $d = $data[0];
        // 是否有任务告警推送  前提:策略自动推送关闭
        $pushSql = "SELECT task_uuid, alarm_type, push_response_type from bd_alarm_push_strategy where auto_push_flag != ? and alarm_type = ?";   //再循环查找，二维数组
        $pushSqlData = $this->dbSelect($pushSql, array($flag['SET'], $flag['SET']));

        // 手动推送消息-任务
        $pushParams = [];
        $pushParams['logType'] = xphp_get_config('log', 'LOGTYPE')['TASK'];
        $pushParams['errorCode'] = $d['error_code'];
        $pushParams['description'] = $d['description_key'];
        $pushParams['descriptionParam'] = $d['description_param'];
        $pushParams['logLevel'] = null;
        $pushParams['logId'] = null;
        $pushParams['errorDetail'] = null;
        $pushParams['moduleType'] = $d['module_type'];
        $pushParams['taskName'] = $d['task_name'];
        $confDes = xphp_get_config('log_task');
        $errorConf = xphp_get_config('error');

        $des =  $this->getLogDescription(
            $d['error_code'],
            $d['description_key'],
            $d['description_param'],
            $confDes,
            $errorConf,
        );
        // 公有云替换描述中的"虚拟机"为"实例"
        if (in_array($d['submodule_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            $des = str_replace(xphp_get_lang('WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER'), xphp_get_lang('WEB_PLATFORM_DES_INSTANCE'), $des);
        }
        $moduleTypeAli = $d['module_type'];
        $subModuleTypeAli = $d['submodule_type'];
        if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['VM']) {
            $subModuleTypeAli = $this->getVmSubModuleType($d['submodule_type'], $vmGroup);
        } else if ($d['module_type'] == '30') {
            if (!empty($d['module_source_arr'])) {
                // 解析下数据验证的模块和子模块类型
                $sureModule = array_filter(explode(',', $d['module_source_arr']));
                $moduleTypeAli = [];
                $subModuleTypeAli = [];
                foreach ($sureModule as $items) {
                    $itemsArr = explode('-', $items);
                    $moduleTypeAli[] = $itemsArr[0] ?? 0;
                    $subModuleTypeAli[] = $itemsArr[1] ?? 0;
                }
                $moduleTypeAli = implode(',', $moduleTypeAli);
                $subModuleTypeAli = implode(',', $subModuleTypeAli);
            }
        } else if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['DB']) {
            $subModuleTypeAli = '';
        }
        $info = array(
            'alarm_id' => intval($id),
            'task_name' => $d['task_name'],
            'module_type' => intval($d['module_type']),
            'module_type_des' => (new JobInfo())->getObjects($moduleTypeAli, $subModuleTypeAli),
            'task_type' => intval($d['task_type']),
            'task_type_des' => $this->getTaskNameString($d['module_type'], $d['task_type']),
            'node' => $d['node_name'],
            'storage' => empty($d['storage_name']) ? xphp_get_config('app', 'NULLSPACE') : $d['storage_name'],
            'sub_module_type_value' => in_array($d['module_type'], [xphp_get_config('module', 'MODULE_TYPE')['FS'], xphp_get_config('module', 'MODULE_TYPE')['OS']]) ? $d['submodule_type'] : $this->getSubModuleTypeValue($d['module_type'], $d['submodule_type']),
            'task_uuid' => $d['task_uuid'],
            'alarm_level_push' => $d['alarm_level'],
            'task_log' => $this->getTaskAlarmDetailsLogs($id),
            'alarm_level' => xphp_get_desc('Pf', 'ALARM_LEVEL_DES')[intval($d['alarm_level'])],
            'alarm_level_flag' => intval($d['alarm_level']),
            'alarm_time' => $d['alarm_time'],
            'description' => $des,
            'taskAlarmContent' => $this->getTaskAlarmContent($pushParams),
            'solved_flag' => v1_parse_flag_to_bool(intval($d['solved_flag'])),
            'solved_flag_des' => xphp_get_desc('Pf', 'ALARM_RESPOND_DES')[intval($d['solved_flag'])],
            'solved_user_name' => $d['solved_username'],
            'solved_time' => $d['solved_time'],
            'email_flag' => v1_parse_flag_to_bool(intval($d['email_send_flag'])),
            'email_flag_des' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['email_send_flag'])],
            'sms_flag' => v1_parse_flag_to_bool(intval($d['sms_send_flag'])),
            'sms_flag_des' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['sms_send_flag'])],
            'wx_flag' => v1_parse_flag_to_bool(intval($d['wechat_send_flag'])),
            'wx2_flag' => v1_parse_flag_to_bool(intval($d['wechat2_send_flag'])),
            'wx_flag_des' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['wechat_send_flag'])],
            'wx2_flag_des' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['wechat2_send_flag'])],
            'history_uuid' => $d['history_uuid'],
        );

        $arr = [];
        // 追加处理只返回哪些已经发送了的通知方式
        if ($info['email_flag']) {
            $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_EMAIL');
        }
        if ($info['sms_flag']) {
            $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_SMS');
        }
        if ($info['wx_flag']) {
            $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT');
        }
        if ($info['wx2_flag']) {
            $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_INTERNET2');
        }

        $info['send_types'] = implode(',', $arr);

        return $info;
    }

    /**
     * 获取任务告警内容
     */
    public function getTaskAlarmContent($params)
    {
        $msg = array(
            'success' => true,
            'code' => $params['errorCode'],
            'message' => array(
                'alarm_content' => ''
            ),
            'date' => '',
            'alarm_type' => xphp_get_config('log', 'LOGTYPE')['TASK'],
        );
        $confDes = (new LogInfo())->getLogConf($params['logType']);
        $desStr = '';
        if (!empty($params['taskName'])) {
            $desStr .= xphp_get_lang('UI_PUBLIC_TASK_RNAME') . ":" . $params['taskName'] . " ";
        }
        if (!empty($params['moduleType'])) {
            $desStr .= xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . ":" . $this->getModuleTypeDes($params['moduleType']) . " ";
            $desStr .= xphp_get_lang('UI_ALARM_CONTENT') . ":";
        }
        $desStr .= $confDes[$params['description']];
        if ($params['errorCode']) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$params['errorCode']]];
            $desStr .= "," . $errorStr;
        }
        $msg['message']['alarm_content'] = $desStr;
        $desStr ? $msg['success'] = true : $msg['success'] = false;
        $msg['data'] = date('Y-m-d H:i:s');
        $msg = json_encode($msg);
        return $msg;
    }

    /**
     * 得到系统告警详情
     * @param {} $id 系统告警id
     * @return array
     */
    public function getSystemAlarmDetail($id)
    {
        //获取系统告警响应标记
        $solvedFlag = $this->getSystemAlarmSolvedFlag($id);
        if ($solvedFlag == xphp_get_config('app', 'FLAG')['UNSET']) {
            //打开直接标记为已经响应
            $nowDate = date('Y-m-d H:i:s');
            $sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
            where system_alarm_id = ?";
            $sqlParams = array(xphp_get_config('app', 'FLAG')['SET'], $nowDate, xphp_get_user_info()['userName'], $id);
            $result = $this->dbExec($sql, $sqlParams);
        }
        //获取数据
        $sql = "SELECT system_alarm_id, alarm_level, description_key, description_param, alarm_time, 
                solved_flag, solved_time, solved_username, email_send_flag, sms_send_flag, wechat_send_flag,enterprise_wechat_send_flag as wechat2_send_flag, error_code 
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id)) ?? [];
        $d = $data[0];
        $info = [];
        $confDes = xphp_get_config('log_system');
        $errorConf = xphp_get_config('error');
        if (!empty($data)) {
            $info = array(
                'alarm_id' => $id,
                'alarm_level' => xphp_get_desc('Pf', 'ALARM_LEVEL_DES')[intval($d['alarm_level'])],
                'alarm_level_flag' => intval($d['alarm_level']),
                'alarm_time' => $d['alarm_time'],
                'alarm_content' => $this->getLogDescription(
                    $d['error_code'],
                    $d['description_key'],
                    $d['description_param'],
                    $confDes,
                    $errorConf,
                ),
                'alarm_solved_flag' => v1_parse_flag_to_bool($d['solved_flag']),
                'alarm_solved_des' => xphp_get_desc('Pf', 'ALARM_RESPOND_DES')[intval($d['solved_flag'])],
                'alarm_solved_user' => $d['solved_username'],
                'alarm_solved_time' => $d['solved_time'],
                'alarm_email_flag' => v1_parse_flag_to_bool($d['email_send_flag']),
                'alarm_sms_flag' => v1_parse_flag_to_bool($d['sms_send_flag']),
                'alarm_weChat_flag' => v1_parse_flag_to_bool($d['wechat_send_flag']),
                'alarm_weChat2_flag' => v1_parse_flag_to_bool($d['wechat2_send_flag']),
                'alarm_email' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['email_send_flag'])],
                'alarm_sms' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['sms_send_flag'])],
                'alarm_weChat' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['wechat_send_flag'])],
                'alarm_weChat2' => xphp_get_desc('Pf', 'ALARM_NOTICE_DES')[intval($d['wechat2_send_flag'])],
            );

            $arr = [];
            // 追加处理只返回哪些已经发送了的通知方式
            if ($info['alarm_email_flag']) {
                $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_EMAIL');
            }
            if ($info['alarm_sms_flag']) {
                $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_SMS');
            }
            if ($info['alarm_wechat_flag']) {
                $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT');
            }
            if ($info['alarm_wechat2_flag']) {
                $arr[] = xphp_get_lang('UI_SETTINGS_NOTICE_WECHAT_INTERNET2');
            }

            $info['send_types'] = implode(',', $arr);
        }
        return $info;
    }

    /**
     * 获取任务告警解决标记
     * @param string $id
     */
    public function getTaskAlarmSolvedFlag($id)
    {
        $sql = "SELECT solved_flag from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id)) ?? [];
        $solvedFlag = xphp_get_config('app', 'FLAG')['UNSET'];
        if (!empty($data)) {
            $solvedFlag = $data[0]['solved_flag'];
        }

        return $solvedFlag;
    }

    /**
     * 获取系统告警解决标记
     * @param string $id
     */
    public function getSystemAlarmSolvedFlag($id)
    {
        $sql = "SELECT solved_flag from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id)) ?? [];
        $solvedFlag = xphp_get_config('app', 'FLAG')['UNSET'];
        if (!empty($data)) {
            $solvedFlag = $data[0]['solved_flag'];
        }

        return $solvedFlag;
    }

    /**
     * 得到任务告警的任务类型  现在任务类型数据库和文件用的一样的task_type  这里通过模块类型区分虚拟机和文件
     * @param {} $moduleType
     * @param {} $taskType
     * @return string 任务类型
     */
    public function getTaskNameString($module_type, int $task_type)
    {
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $TASK_TYPE = xphp_get_config('task', 'TASKTYPE');

        $moduleTypeArr = [
            $MODULE_TYPE['DB'] => [ // 如果为数据库
                $TASK_TYPE['DB_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $TASK_TYPE['DB_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $MODULE_TYPE['OEM_DBCDP'] => [ // 如果为数据库实时
                $TASK_TYPE['DB_CDP_BACKUP'] => 'CDP',
                $TASK_TYPE['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $MODULE_TYPE['OEM_FSCDP'] => [
                $TASK_TYPE['FILE_CDP_BACKUP'] => ('WEB_PLATFORM_DES_REAL_TIME_SYN'),
                $TASK_TYPE['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $MODULE_TYPE['OS'] => [
                $TASK_TYPE['OS_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $TASK_TYPE['OS_INSTANT_RECOVERY'] => ('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
                $TASK_TYPE['OS_INSTANT_RECOVERY_MOTION'] => ('WEB_PLATFORM_DES_MOTION'),
            ],
            $MODULE_TYPE['DB_CDP'] => [
                $TASK_TYPE['CDP_DB_BACKUP'] => ('WEB_PLATFORM_REPLICATION'),
                $TASK_TYPE['CDP_DB_RECOVERY'] => ('UI_JOB_TYPE_CDP_DB_RECOVERY'),
            ],
            $MODULE_TYPE['VOL_CDP'] => [
                $TASK_TYPE['VOL_CDP_BACKUP'] => ('UI_CLIENT_CDP_BACKUP'),
                $TASK_TYPE['VOL_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
                $TASK_TYPE['VOL_CDP_TAKEOVER'] => ('WEB_PLATFORM_DES_TAKEOVER'),
                $TASK_TYPE['VOL_CDP_REPLICATION'] => ('WEB_PLATFORM_REPLICATION'),
            ]
        ];

        $des = !empty($moduleTypeArr[$module_type][$task_type]) ? xphp_get_lang($moduleTypeArr[$module_type][$task_type]) : (xphp_get_desc('Pf', 'TASKTYPEDES')[$task_type] ?? '');

        return $des;
    }

    /**
     * 得到任务日志信息
     * @param {} $params
     */
    public function getTaskAlarmDetailsLogs($id)
    {
        $sql = "SELECT node_uuid, task_log_path, alarm_level from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id)) ?? [];
        $info = '';
        if ($data) {
            $info = $this->getTaskLogInfo($data[0]['node_uuid'], $data[0]['task_log_path'], $data[0]['alarm_level'], $params['type'] ?? 0);
        }
        return $info;
    }

    /**
     * 获取任务告警同步信息
     * @param {} $params 数据
     * @return {}  获取任务告警同步信息
     */
    public function getJobAlarmSync(): array
    {
        $syncTime = date('Y-m-d H:i:s');
        //获取全部信息
        $sql = "SELECT * from bd_task_alarm where alarm_level != ? ";
        $sqlParams = array(xphp_get_config('log')['LOGLEVEL']['NORMAL']);
        if (!empty($syncTime)) {
            //同步时间
            $sql .= " and alarm_time > ? ";
            $sqlParams = array_merge($sqlParams, array($syncTime));
        }
        $data = $this->dbSelect($sql, $sqlParams) ?? [];
        return $data;
    }

    /**
     * 获取任务告警对应后台服务日志
     * @param array $params 数据
     * @author luokai@vinchin.com
     * @date 2023/11/1
     * @return string
     */
    public function getJobAlarmServerLog($id): array
    {
        $sql = "SELECT node_uuid, task_log_path, alarm_level from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id)) ?? [];
        $info = [];
        if ($data) {
            $info = array(
                'tasklog' => $this->getTaskLogInfo($data[0]['node_uuid'], $data[0]['task_log_path'], $data[0]['alarm_level'], $data['type'] ?? 0),
            );
        }
        return $info;
    }

    /**
     * 下载后台服务日志
     * @param array $params 数据
     * @author luokai@vinchin.com
     * @date 2023/11/1
     * @return void
     */
    public function downloadServerLog($id): array
    {
        if (empty($id))
            return [];
        $sql = "SELECT node_uuid, task_log_path, task_name, alarm_time from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id)) ?? [];
        if (empty($data))
            return [];
        $path = $data[0]['task_log_path'];
        $nodeuuid = $data[0]['node_uuid'];
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->service()->mbNodeMsgs($nodeuuid, $opName, json_encode($msg), true);
        $fileContent = "";
        if ($msg['result']) {
            $fileContent = $msg['msg']['file_content'];
        }
        $info = array(
            'content' => $fileContent
        );
        return $info;
    }

    /**
     * 下载任务告警日志
     * @param {} $params
     * @author wuyihang@vinchin.com
     * @date 2024/4/7
     * @return mixed
     */
    public function downLoadTaskLog($params)
    {
        $id = $params['id'];
        if (empty($id))
            return false;
        $sql = "SELECT node_uuid, task_log_path, task_name, alarm_time from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, [$id]) ?? [];
        if (empty($data))
            return false;
        $path = $data[0]['task_log_path'];
        $node_uuid = $data[0]['node_uuid'];
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = ['file_path' => $path];
        $msg = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true);
        $fileContent = '';
        if ($msg['result']) {
            $fileContent = str_replace("\n", "\r\n", $msg['msg']['file_content']);
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($fileContent));
            Header("Content-Disposition: attachment; filename=" . $data[0]['task_name'] . "_" . $data[0]['alarm_time'] . ".txt");
            return $fileContent;
        } else {
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($fileContent));
            Header("Content-Disposition: attachment; filename=" . $data[0]['task_name'] . "_" . $data[0]['alarm_time'] . ".txt");
            return $fileContent;
        }
    }

    /**
     * 得到告警日志的信息
     * @param string $nodeuuid
     * @param string $path
     * @param int    $logLeve
     * @param int    $type
     * @return string
     * @author wuyihang@vinchin.com
     * @date 2023/10/9
     * @return {}
     */
    private function getTaskLogInfo($nodeuuid, $path, $logLeve, $type = 0)
    {
        if (empty($path) || empty($nodeuuid) || empty($logLeve))
            return '';
        if (intval($logLeve) == xphp_get_config('app')['LOGLEVEL']['NORMAL']) {
            //如果是一般告警,没有日志
            return '';
        }
        // 判断节点是否在线，不在线不请求日志信息
        $sql = "select node_uuid from bd_node where status = 0";
        $data = $this->dbSelect($sql) ?? [];
        $node_arr = array_column($data, 'node_uuid');
        if (!in_array($nodeuuid, $node_arr)) {
            return '';
        }
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->service()->mbNodeMsgs($nodeuuid, $opName, json_encode($msg), true);
        $fileContent = '';
        if ($msg['result']) {
            $fileContent = str_replace("\n", "\n", $msg['msg']['file_content']);
        } else {
            if (!$type) {
                // $errorMsg = $this->muOpResult(false, xphp_get_lang('WEB_ALARM_READ_LOG_INFO'), '', 'error', $msg['errorCode']);
                // exit($errorMsg);
                return $fileContent;
            } else {
                // 内部调用，不能使用exit
                $errorCode = $msg['errorCode'];
                $msgs = xphp_get_lang('WEB_ALARM_READ_LOG_INFO') . xphp_get_lang('WEB_PUBLIC_FAILURE');
                if ($errorCode > 0) {
                    $error = xphp_get_config('error');
                    $errorKey = $error['errorCode'][$errorCode];
                    $msgs .= "," . xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ": #" . $errorCode . ",";
                    $msgs .= xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": " . $error['errorCodeDes'][$errorKey];
                }
                return $msgs;
            }
        }
        return $fileContent;
    }

    /**
     * 导出任务告警列表
     * @param array $params
     * @return {}
     * @author wuyihang@vinchin.com
     * @date 2024/4/22
     */
    public function exportJobAlarm($params)
    {
        $params['isExport'] = true;
        $exportData = $this->getJobAlarmList($params, true)['rows'];
        $title = xphp_get_lang('UI_HOMEPAGE_SYSTEM_LOG');
        $header = [
            'num' => xphp_get_lang('UI_PUBLIC_NUMBER'),
            'job_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'user_name' => xphp_get_lang('UI_PUBLIC_USER'),
            'module_type' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'job_type' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'alarm_level' => xphp_get_lang('UI_ALARM_LEVLE'),
            'alarm_time' => xphp_get_lang('UI_ALARM_TIME'),
            'description' => xphp_get_lang('UI_ALARM_CONTENT'),
            'solved_flag_des' => xphp_get_lang('UI_ALARM_RESPONSE_FLAG'),
        ];
        $exportData = array_map(function ($row) {
            return [
                'num' => $row['id'],
                'job_name' => $row['job_name'],
                'user_name' => $row['user_name'],
                'module_type' => $row['module_type'],
                'job_type' => $row['job_type'],
                'alarm_level' => $row['status_des'],
                'alarm_time' => $row['alarm_time'],
                'description' => $row['description'],
                'solved_flag_des' => $row['solved_flag_des'],
            ];
        }, $exportData);
        $relation = [
            'num' => ['col_name' => 'A', 'width' => 10],
            'job_name' => ['col_name' => 'B', 'width' => 25],
            'user_name' => ['col_name' => 'C', 'width' => 20],
            'module_type' => ['col_name' => 'D', 'width' => 15],
            'job_type' => ['col_name' => 'E', 'width' => 15],
            'alarm_level' => ['col_name' => 'F', 'width' => 10],
            'alarm_time' => ['col_name' => 'G', 'width' => 30],
            'description' => ['col_name' => 'H', 'width' => 30],
            'solved_flag_des' => ['col_name' => 'I', 'width' => 10],
        ];
        v1_base_export($title, $header, $exportData, $relation);
    }

    /**
     * 导出系统告警列表
     * @param array $params
     * @return {}
     * @author wuyihang@vinchin.com
     * @date 2024/4/23
     */
    public function exportSystemAlarm($params)
    {

        $params['isExport'] = true;
        $exportData = $this->getSystemAlarmList($params, true)['rows'];
        $title = xphp_get_lang('UI_PLATFORM_ALARM_SYSTEM');
        $header = [
            'num' => xphp_get_lang('UI_PUBLIC_NUMBER'),
            'level_des' => xphp_get_lang('UI_ALARM_LEVLE'),
            'alarm_time' => xphp_get_lang('UI_ALARM_TIME'),
            'description' => xphp_get_lang('UI_ALARM_CONTENT'),
            'solved_des' => xphp_get_lang('UI_ALARM_RESPONSE_FLAG'),
        ];
        $exportData = array_map(function ($row) {
            return [
                'num' => $row['id'],
                'level_des' => $row['level_des'],
                'alarm_time' => $row['alarm_time'],
                'description' => $row['UI_ALARM_CONTENT'],
                'solved_des' => $row['solved_des'],
            ];
        }, $exportData);
        $relation = [
            'num' => ['col_name' => 'A', 'width' => 10],
            'level_des' => ['col_name' => 'B', 'width' => 25],
            'alarm_time' => ['col_name' => 'C', 'width' => 20],
            'description' => ['col_name' => 'D', 'width' => 15],
            'solved_des' => ['col_name' => 'E', 'width' => 15],
        ];
        v1_base_export($title, $header, $exportData, $relation);
    }

    /**
     * 删除任务告警
     */
    public function deleteTaskAlarm($params)
    {
        $id = $params['id'];
        $idArray = [];
        foreach ($id as $v) {
            $idArray[] = intval($v);
        }

        // 关联管理用户判断 存储资源 - 操作 alarm_operate
        $alamId = "(" . implode(',', $idArray) . ")";
        $data = $this->dbSelect("SELECT user_uuid,unix_timestamp(alarm_time) alarm_time from bd_task_alarm where task_alarm_id in {$alamId}");
        if (empty($data)) {
            $data = [];
        }

        // 操作权限判断
        $userArr = implode(',', array_unique(array_column((array)$data, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['alarm']);
        // 判断下是否在最低删除期限内
        $daysArr = array_column($data, 'alarm_time');
        // 判断下是否符合保留策略
        $leastDays = xphp_get_system_data_safe(2);
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'alarm_time');
        // 检查保留策略，是否可以删除
        $this->deleteCheckStrategy($reserveType, 1, $id, $daysArr, $reserveNum);
        $opName = 'BD_ALARM_OP_TASK_DELETE';
        $msg = ['id_list' => $idArray];
        return $this->unifyMsg($opName, json_encode($msg));
    }
    /**
     * 删除前检查策略是否允许删除
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
                $sql = "select *  from bd_task_alarm ORDER BY alarm_time desc limit 0, {$reserveNum}";
                if ($type == 2) {
                    $sql = "select *  from bd_system_alarm ORDER BY alarm_time desc limit 0, {$reserveNum}";
                }
                // 保留个数内的所有记录
                $reserveNumList = $this->dbSelect($sql) ?? [];
                // 保留个数内的所有记录的id
                $idArr = array_column($reserveNumList, 'task_alarm_id');
                if ($type == 2) {
                    $idArr = array_column($reserveNumList, 'system_alarm_id');
                }
                foreach ($id as $v) {
                    // 如果id在保留策略的记录内
                    if (in_array($v, $idArr)) {
                        // 那么不允许删除
                        $msg = xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS');
                        $msg = str_replace('%S%', $reserveNum, $msg);
                        return $this->muOpResult(false, xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'), $msg);
                    }
                }
                break;
            case 2: // 按时间保留
                if (time() - min($daysArr) < $reserveNum * 24 * 3600) {
                    // 那么不允许删除
                    $msg = xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS');
                    $msg = str_replace('%S%', $reserveNum, $msg);
                    return $this->muOpResult(false, xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'), $msg);
                }
                break;
            case 3: // 永久保留
                // 永久保留，那么不允许删除
                return $this->muOpResult(false, xphp_get_lang('WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'), xphp_get_lang('WEB_ALARM_DELETE_CAN_NOT_DELETE_TIPS'));
        }
        return true;
    }

    /**
     * 删除系统告警
     * @param array $params
     * @return string
     */
    public function deleteSystemAlarm($params)
    {
        //权限检查
        $roleHandler = new Role();
        $roleHandler->pOperationPermissionCheckExit("p_system_alarm_delete");
        $id = $params['id'];
        $this->paramsCheck($id);
        $idArray = [];
        foreach ($id as $v) {
            $idArray[] = intval($v);
        }
        // 判断下是否在最低删除期限内
        $alamId = "(" . implode(',', $idArray) . ")";
        $data = $this->dbSelect("SELECT unix_timestamp(alarm_time) alarm_time from bd_system_alarm where system_alarm_id in {$alamId}");
        if (empty($data)) {
            $data = [];
        }
        // 判断用户是否有操作权限
        $checkOperate = xphp_check_operate('alarm_operate', [xphp_get_user_info()['userUuid']]);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, xphp_get_lang('UI_ROLE_PERMISSION'), xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'warning'));
        }
        $leastDays = xphp_get_system_data_safe(6);
        $daysArr = array_column($data, 'alarm_time');
        // 判断下是否符合保留策略
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'alarm_time');
        // 检查保留策略，是否可以删除
        $this->deleteCheckStrategy($reserveType, 1, $id, $daysArr, $reserveNum);
        $opName = 'BD_ALARM_OP_SYSTEM_DELETE';
        $msg = ['id_list' => $idArray];
        return $this->unifyMsg($opName, json_encode($msg));
    }


    private function writeAlarmLog($opName, $msg, $mbResult)
    {
        $key = "";
        $params = array(count($msg['id_list']));
        if ($opName == "BD_ALARM_OP_TASK_DELETE") {
            $key = "SYSTEM_LOG_DELETE_TASK_ALARM";
        } else if ($opName == "BD_ALARM_OP_SYSTEM_DELETE") {
            $key = "SYSTEM_LOG_DELETE_SYSTEM_ALARM";
        }

        if ($mbResult['result']) {
            $this->systemLog($key, $params);
        } else {
            $this->systemLog($key, $params, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
        }
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName
     * @param {} $msg
     * @return string
     */
    private function unifyMsg($opName, $jsonMsg)
    {
        $mbResult = $this->mbPFMsg($opName, $jsonMsg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        $this->writeAlarmLog($opName, json_decode($jsonMsg, true), $mbResult);
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 获取各模块的对象信息
     * @param mixed $params
     * @return array<array|int>
     */
    public function getJobAlarmModuleItem($params)
    {
        if (empty($params['alarm_id'])) {
            return [];
        }
        $errorConf = xphp_get_config('error');
        // 查询告警关联的历史任务记录
        $sql = "SELECT bht.details, bht.module_type, bht.task_type,bht.submodule_type from bd_history_task bht, bd_task_alarm  bta where bht.task_uuid = bta.task_uuid and bht.history_uuid = bta.history_uuid and bta.task_alarm_id = ? ";
        $data = $this->dbSelect($sql, [$params['alarm_id']]) ?? [];
        $node = [];
        $node['rows'] = [];
        $VM_STATUS_DES = xphp_get_desc('Vm', 'VmTaskStatus');
        $STATUS_DES = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $TASK_TYPE = xphp_get_config('task', 'TASKTYPE');
        $copy_task_type = [$TASK_TYPE['BACKUP_COPY'], $TASK_TYPE['BACKUP_COPY_FETCH'], $TASK_TYPE['ARCHIVE'], $TASK_TYPE['ARCHIVE_FETCH']];
        if (!empty($data)) {
            $module_type = $data[0]['module_type'];
            $sub_module_type = $data[0]['submodule_type'];
            $details = json_decode($data[0]['details'], true);
            // 副本统一查询，返回统一结构
            if (in_array($data[0]['task_type'], $copy_task_type)) {
                foreach ($details as $i => $d) {
                    $class = $this->getClassByItemStatus($d['error_code']);
                    $node['rows'][] = [
                        'index' => $i + 1,
                        'item_name' => $d['item_name'],
                        'module_type' => $module_type,
                        'status_des' => "<span class='" . $class . "' title='" . $errorConf['errorCodeDes'][$errorConf['errorCode'][$d['error_code']]] . "'>" . (new CopyJobInfo())->getHistoryJobResultDes($d['error_code']) . "</span>",
                        'status' => (new CopyJobInfo())->getHistoryJobResultDes($d['error_code']),
                    ];
                }
                // 非副本任务，details存值不相同 单独处理
            } else {
                switch ($module_type) {
                    case $MODULE_TYPE['VM']:
                        $vms_details = $details['vms_details'];
                        foreach ($vms_details as $i => $d) {
                            $class = $this->getClassByTaskStatus($d['task_status']);
                            $node['rows'][] = [
                                "index" => ++$i,
                                "item_name" => $d['vm_name'],
                                "status_des" => "<span class='" . $class . "' title='" . $errorConf['errorCodeDes'][$errorConf['errorCode'][$d['error_code']]] . "'>" . $VM_STATUS_DES[$d['task_status']] . "</span>",
                                "status" => $VM_STATUS_DES[$d['task_status']],
                                'module_type' => $module_type,
                                'sub_module_type' => $sub_module_type,
                            ];
                        }
                        break;
                    case $MODULE_TYPE['FS']:
                    case $MODULE_TYPE['NAS']:
                        $agent_info_list = $details['agent_info_list'];
                        foreach ($agent_info_list as $i => $d) {
                            $class = $this->getClassByTaskStatus($d['task_status']);
                            $node['rows'][] = [
                                "index" => ++$i,
                                "item_name" => $d['src_agent_name'] . "(" . $d['src_agent_ip'] . ")",
                                "status_des" => "<span class='" . $class . "' title='" . $errorConf['errorCodeDes'][$errorConf['errorCode'][$d['error_code']]] . "'>" . $VM_STATUS_DES[$d['task_status']] . "</span>",
                                "status" => $VM_STATUS_DES[$d['task_status']],
                                'source_list' => implode(';', $d['file_list']),
                                'module_type' => $module_type,
                                'sub_module_type' => $sub_module_type,
                            ];
                        }
                        break;
                    case $MODULE_TYPE['DB']:
                        foreach ($details as $i => $d) {
                            $class = $this->getClassByTaskStatus($d['task_status']);
                            $node['rows'][] = [
                                "index" => ++$i,
                                "item_name" => $d['dir_path'],
                                "status_des" => "<span class='" . $class . "' title='" . $errorConf['errorCodeDes'][$errorConf['errorCode'][$d['error_code']]] . "'>" . $VM_STATUS_DES[$d['task_status']] . "</span>",
                                "status" => $VM_STATUS_DES[$d['task_status']],
                                'module_type' => $module_type,
                                'sub_module_type' => $sub_module_type,
                            ];
                        }
                        break;
                    case  $MODULE_TYPE['OS']:
                        foreach ($details as $i => $d) {
                            switch ($d['task_status']) {
                                case 13:
                                    $class = "label label-sm label-success";
                                    break;
                                case 8:
                                    $class = "label label-sm label-danger";
                                    break;
                                default:
                                    $class = "label label-sm label-info";
                                    break;
                            }
                            $node['rows'][] = [
                                "index" => ++$i,
                                "item_name" => $d['os_name'] . "(" . $d['agent_ip'] . ")",
                                "status_des" => "<span class='" . $class . "' title='" . $errorConf['errorCodeDes'][$errorConf['errorCode'][$d['error_code']]] . "'>" . $STATUS_DES[$d['task_status']] . "</span>",
                                "status" => $STATUS_DES[$d['task_status']],
                                'module_type' => $module_type,
                                'sub_module_type' => $sub_module_type,
                            ];
                        }
                        break;
                    case $MODULE_TYPE['M365']:
                        $class = $this->getClassByTaskStatus($details['task_status']);
                        $source_list = '';
                        if ($data[0]['task_type'] == xphp_get_config('task', 'TASKTYPE')['RECOVERY']) {
                            $source_list = implode(';', $details['recovery_m365_object_info_list']);
                        } else {
                            $source_list = implode(';', $details['backup_m365_object_info_list']);
                        }
                        $node['rows'][] = [
                            "index" => 1,
                            'item_name' => $details['organization_name'],
                            'source_list' => $source_list,
                            "status_des" => "<span class='" . $class . "' title='" . $errorConf['errorCodeDes'][$errorConf['errorCode'][$details['error_code']]] . "'>" . $VM_STATUS_DES[$details['task_status']] . "</span>",
                            "status" => $VM_STATUS_DES[$details['task_status']],
                            'module_type' => $module_type,
                            'sub_module_type' => $sub_module_type,
                        ];
                        break;
                    default:
                        break;
                }
            }
        }
        $node['total'] = count($node['rows']);
        return $node;
    }
    /**
     * 根据副本任务状态获取样式
     * @param mixed $errorCode 错误码
     * @return string
     */
    private function getClassByItemStatus($errorCode)
    {
        //异常的错误
        $abnormal = ['BD_TASK_ANBNORMAL_ERROR'];
        //中止的错误
        $discontinue = ['BD_TASK_BE_CANCELLED_ERROR'];
        $errorCodeArr = xphp_get_config('error', 'errorCode');
        // 错误的class
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return 'label label-sm label-danger';
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return 'label label-sm label-default label-default_en';
        }
        // 其他为0就是成功，不为0就是错误
        return $errorCode == 0 ? 'label label-sm label-success' : 'label label-sm label-danger';
    }
    /**
     * 根据任务状态获取样式
     * @param mixed $taskStatus 任务状态
     * @return string
     */
    private function getClassByTaskStatus($taskStatus): string
    {
        $class = '';
        switch ($taskStatus) {
            case 3: //成功
                $class = "label label-sm label-success";
                break;
            case 4: //失败
                $class = "label label-sm label-danger";
                break;
            default:
                $class = "label label-sm label-info";
                break;
        }
        return $class;
    }
    /**
     * 设置任务告警响应状态
     * @param mixed $params
     * @return string
     */
    public function setTaskAlarmStatus($params)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $solved_flag = $params['resolved_flag'];
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        // 根据传入状态设置响应状态
        $des_key = $solved_flag ? $FLAG['SET'] : $FLAG['UNSET'];
        $sql = "UPDATE bd_task_alarm SET solved_flag = ?, solved_time = ?, solved_username = ? WHERE task_alarm_id IN ($ids)";
        $sqlParams = [$des_key, $nowDate, xphp_get_user_info()['userName']];
        $result = $this->dbExec($sql, $sqlParams);
        if ($solved_flag) {
            return $this->muOpResult($result, xphp_get_lang('WEB_ALARM_MARK_RESPONSE'), xphp_get_lang('WEB_ALARM_MARK_RESPONSE_SUCCESS'));
        } else {
            return $this->muOpResult($result, xphp_get_lang('WEB_ALARM_MARK_NOT_RESPONSE'), xphp_get_lang('WEB_ALARM_MARK_NOT_RESPONSE_SUCCESS'));
        }
    }
    /**
     * 设置系统告警响应状态
     * @param mixed $params
     * @return string
     */
    public function setSystemAlarmStatus($params)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $solved_flag = $params['resolved_flag'];
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        // 根据传入状态设置响应状态
        $des_key = $solved_flag ? $FLAG['SET'] : $FLAG['UNSET'];
        $sql = "UPDATE bd_system_alarm SET solved_flag = ?, solved_time = ?, solved_username = ? WHERE system_alarm_id IN ($ids)";
        $sqlParams = [$des_key, $nowDate, xphp_get_user_info()['userName']];
        $result = $this->dbExec($sql, $sqlParams);
        if ($solved_flag) {
            return $this->muOpResult($result, xphp_get_lang('WEB_ALARM_MARK_RESPONSE'), xphp_get_lang('WEB_ALARM_MARK_RESPONSE_SUCCESS'));
        } else {
            return $this->muOpResult($result, xphp_get_lang('WEB_ALARM_MARK_NOT_RESPONSE'), xphp_get_lang('WEB_ALARM_MARK_NOT_RESPONSE_SUCCESS'));
        }
    }


    // -------------------发送告警邮件----------------
    /**
     * 发送告警邮件--后端调用
     * url:/api/v1/alarm/dispatch?x-api-version=1.0-rev0&k=6e24cc40bfdb6963c04a4f1983c8af71&p={"email":1,"sms":2,"type":1,"id":2947}
     * @param mixed $params
     *  mode:1.邮件,2.短信
     *  type:1.任务,2.系统
     *  id:告警id
     * @return {}
     */
    public function sendAlarmEmail($params)
    {
        $return = ['code' => 1, 'msg' => xphp_get_lang('WEB_LINCESE_VERIFY_FAIL'), 'data' => 2];
        if (empty($params['k']) || $params['k'] != xphp_get_config('app', 'API_MAGIC')) {
            // 参数校验
            return $return;
        }
        $jsonParams = json_decode($params['p'], true);
        $emailFlag = v1_parse_flag_to_bool($jsonParams['email']);
        $smsFlag = v1_parse_flag_to_bool($jsonParams['sms']);
        $type = intval($jsonParams['type']);
        $id = intval($jsonParams['id']);
        $NOTICE_TYPE = xphp_get_config('app', 'NOTICE_TYPE');
        switch ($type) {
            case $NOTICE_TYPE['SYSTEM']:
                //发送系统通知
                return $this->sendSystemNotice($emailFlag, $smsFlag, $id);
            case $NOTICE_TYPE['TASK']:
                //发送任务通知
                return $this->sendTaskNotice($emailFlag, $smsFlag, $id);
        }
        return $return;
    }
    /**
     * 发送系统通知
     * @param mixed $emailFlag 邮件标志
     * @param mixed $smsFlag 短信标志
     * @param mixed $systemAlarmID 告警id
     * @return {}
     */
    private function sendSystemNotice($emailFlag, $smsFlag, $systemAlarmID)
    {
        $NOTICE_TYPE = xphp_get_config('app', 'NOTICE_TYPE');
        // 检查邮件发送配置信息
        $checkResult = $this->checkNoticeSetting($emailFlag, $smsFlag, $NOTICE_TYPE['SYSTEM'], $systemAlarmID);
        $emailFlag = $checkResult['email'];
        $smsFlag = $checkResult['sms'];
        $wechatFlag = $checkResult['wechat'];
        $wechat2Flag = $checkResult['wechat2'];
        $type = $NOTICE_TYPE['SYSTEM'];
        $NOTICE_MODE = xphp_get_config('app', 'NOTICE_MODE');

        if ($wechatFlag) {
            // 微信通知发送
            // 获取微信通知信息内容
            $wechatInfo = $this->getSystemWechatParams($systemAlarmID);
            // 发送微信通知
            $wechatResult = $this->sendTemplate($wechatInfo, $systemAlarmID, 1);
            $this->updateNoticeResult($systemAlarmID, $type, $NOTICE_MODE['WE_CHAT'], $wechatResult);
        }

        if ($wechat2Flag) {
            // 企业微信通知发送
            $wechatInfo = $this->getSystemWechatParams($systemAlarmID);
            $wechatResult = $this->sendTemplate2($wechatInfo, $systemAlarmID, 1);
            $this->updateNoticeResult($systemAlarmID, $type, $NOTICE_MODE['WE_CHAT2'], $wechatResult);
        }

        if ($emailFlag && $smsFlag) {
            //邮件短信一起发
            $EmailInfo = $this->getSystemEmailParams($systemAlarmID);
            $SmsInfo = $this->getSystemSmsParams($systemAlarmID);
            $emailResult = (new Notice())->sendEmail($EmailInfo);
            $smsResult = (new Notice())->sendSms($SmsInfo);

            $this->updateNoticeResult($systemAlarmID, $type, $NOTICE_MODE['EMAIL'], $emailResult);
            $this->updateNoticeResult($systemAlarmID, $type, $NOTICE_MODE['SMS'], $smsResult);
            $result = false;
            if ($emailResult['code'] && $smsResult['code']) {
                $msg = xphp_get_lang('WEB_ALARM_SEND_NOTICE_SUCCESS');
                $result = true;
            } elseif ($emailResult['code']) {
                $msg = xphp_get_lang('WEB_ALARM_SEND_EMAIL_SUCCESS');
            } elseif ($smsResult['code']) {
                $msg = xphp_get_lang('WEB_ALARM_SEND_SMS_SUCCESS');
            } else {
                $msg = xphp_get_lang('WEB_ALARM_SEND_NOTICE_FAILURE');
            }
            return $this->muOpResult($result, xphp_get_lang('WEB_ALARM_SEND_NOTICE'), $msg, 'error');
        }
        if ($emailFlag) {
            //邮件通知
            $EmailInfo = $this->getSystemEmailParams($systemAlarmID);
            $emailResult = (new Notice())->sendEmail($EmailInfo);
            $this->updateNoticeResult($systemAlarmID, $type, $NOTICE_MODE['EMAIL'], $emailResult);
            return $emailResult;
        }
        if ($smsFlag) {
            //短信通知
            $SmsInfo = $this->getSystemSmsParams($systemAlarmID);
            $smsResult = (new Notice())->sendSms($SmsInfo);
            $this->updateNoticeResult($systemAlarmID, $type, $NOTICE_MODE['SMS'], $smsResult);
            return $smsResult;
        }
    }
    /**
     * 根据配置得到本次发送邮件和短信的标志
     * @param mixed $emailFlag 调用者发送邮件的标志
     * @param mixed $smsFlag 调用者发送短信的标志
     * @param mixed $type 系统通知还是任务通知
     * @param mixed $id 通知ID号
     * @return array{email: bool, sms: bool, wechat: bool, wechat2: bool}
     */
    private function checkNoticeSetting($emailFlag, $smsFlag, $type, $id)
    {
        $emailFlag = $this->checkEmailNoticeSetting($emailFlag, $type, $id);
        $smsFlag = $this->checkSmsmNoticeSetting($smsFlag, $type, $id);
        $wechatFlag = $this->checkWechatNoticeSetting($type, $id);
        $wechat2Flag = $this->checkWechat2NoticeSetting($type, $id);
        $flag = array(
            'email' => $emailFlag,
            'sms' => $smsFlag,
            'wechat' => $wechatFlag,
            'wechat2' => $wechat2Flag,
        );
        return $flag;
    }
    /**
     * 根据配置得到本次发送邮件的标志
     * @param mixed $emailFlag 调用者发送邮件的标志
     * @param mixed $type 系统通知还是任务通知
     * @param mixed $id 通知ID号
     * @return bool
     */
    public function checkEmailNoticeSetting($emailFlag, $type, $id)
    {
        if (!$emailFlag) return false;
        //得到邮件配置
        $sql = "SELECT email_notice_flag, system_notice_flag, system_notice_level, task_notice_flag, task_notice_level, verify_report_flag, verify_report_level from bd_email_notice";
        $data = $this->dbSelect($sql) ?? [];
        //如果未设置总开关,直接返回false
        if ($data[0]['email_notice_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) return false;

        return $this->unifyCheckSetting($data[0], $type, $id);
    }
    /**
     * 根据配置得到本次发送短信的标志 
     * @param mixed $smsFlag 调用者发送短信的标志
     * @param mixed $type 系统通知还是任务通知
     * @param mixed $id 通知ID号
     * @return bool
     */
    private function checkSmsmNoticeSetting($smsFlag, $type, $id)
    {
        if (!$smsFlag) return false;
        //得到邮件配置
        $sql = "SELECT sms_notice_flag, system_notice_flag, system_notice_level,task_notice_flag, task_notice_level from bd_sms_notice";
        $data = $this->dbSelect($sql) ?? [];
        //如果未设置总开关,直接返回false
        if ($data[0]['sms_notice_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) return false;

        return $this->unifyCheckSetting($data[0], $type, $id);
    }
    /**
     * 根据配置得到本次发送微信模板消息的标志
     * @param mixed $type 系统通知还是任务通知
     * @param mixed $id 通知ID号
     * @return bool
     */
    private function checkWechatNoticeSetting($type, $id)
    {
        //得到配置
        $sql = "SELECT settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sql) ?? [];
        //如果未设置总开关,直接返回false
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            if (!empty($wechatContent['openid']) && $wechatContent['wechatFlag'] != xphp_get_config('app', 'FLAG')['UNSET']) {
                $datas = [
                    'system_notice_flag' => $wechatContent['systemFlag'],
                    'task_notice_flag' => $wechatContent['taskFlag'],
                    'system_notice_level' => $wechatContent['systemLevel'],
                    'task_notice_level' => $wechatContent['taskLevel'],
                ];
                return $this->unifyCheckSetting($datas, $type, $id);
            }
        }
        return false;
    }
    /**
     * 根据配置得到本次发送企业微信消息的标志
     * @param mixed $type 系统通知还是任务通知
     * @param mixed $id 通知ID号
     * @return bool
     */
    private function checkWechat2NoticeSetting($type, $id)
    {
        //得到配置
        $sql = "SELECT settings_content from bd_system_settings where settings_type = 11";
        $wechats = $this->dbSelect($sql) ?? [];
        //如果未设置总开关,直接返回false
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            if ($wechatContent['wechatFlag'] != xphp_get_config('app', 'FLAG')['UNSET']) {
                $datas = [
                    'system_notice_flag' => $wechatContent['systemFlag'],
                    'task_notice_flag' => $wechatContent['taskFlag'],
                    'system_notice_level' => $wechatContent['systemLevel'],
                    'task_notice_level' => $wechatContent['taskLevel'],
                ];
                return $this->unifyCheckSetting($datas, $type, $id);
            }
        }
        return false;
    }
    /**
     * 检查配置信息
     * @param mixed $setting 配置信息
     * @param mixed $type 系统通知还是任务通知
     * @param mixed $id 通知ID号
     * @return bool
     */
    private function unifyCheckSetting($setting, $type, $id)
    {
        $FLAG = xphp_get_config('app', 'FLAG');
        $NOTICE_TYPE = xphp_get_config('app', 'NOTICE_TYPE');
        $sql = "SELECT task_type from bd_task_alarm where task_alarm_id = ?";
        switch ($type) {
            case $NOTICE_TYPE['SYSTEM']:
                //系统通知
                if ($setting['system_notice_flag'] == $FLAG['UNSET'])   return false;

                $alarmID = "system_alarm_id";
                $tableName = "bd_system_alarm";
                $tableLevel = "system_notice_level";
                break;
            case $NOTICE_TYPE['TASK']:
                //任务通知
                if ($setting['task_notice_flag'] == $FLAG['UNSET'])   return false;
                $alarmID = "task_alarm_id";
                $tableName = "bd_task_alarm";
                $tableLevel = "task_notice_level";
                break;
        }
        //得到本次告警等级
        $sql = "SELECT alarm_level from $tableName where $alarmID = ?";
        $alarmInfo = $this->dbSelect($sql, array($id)) ?? [];
        $level = $alarmInfo[0]['alarm_level'];

        $settingLeve = explode(',', $setting[$tableLevel]);

        if (in_array($level, $settingLeve)) {
            return true;
        }
        return false;
    }
    /**
     * 得到系统微信通知参数
     * @param mixed $systemAlarmID
     * @return array{content: mixed, desc: mixed, task_name: array|string, title: array|string}
     */
    private function getSystemWechatParams($systemAlarmID)
    {
        $LOG_TYPE = xphp_get_config('log', 'LOGTYPE');
        $sql = "SELECT alarm_level, description_key, description_param, alarm_time, error_code, system_log_path from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, [$systemAlarmID]);
        if (empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ALARM_GET_NOTICE_INFO')));
        }
        $dataInfo = $data[0] ?? [];
        $LOG_SYSTEM = xphp_get_config('LOG_SYSTEM');
        $desStr = $LOG_SYSTEM[$dataInfo['description_key']];
        $description = (new LogInfo())->getLogDescriptionNotice(
            $LOG_TYPE['SYSTEM'],
            $dataInfo['error_code'],
            $dataInfo['description_key'],
            $dataInfo['description_param'],
            "",
            false,
        );

        if ($dataInfo['error_code']) {
            $errorConf = include CONF_PATH . 'error.php';
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$dataInfo['error_code']]];
            $desStrs = "[#" . $dataInfo['error_code'] . "]" . $errorStr;
        }

        if ($dataInfo['description_param']) {
            $desStrs = str_replace(["%s", "'", "[", "]"], ['', '', '', ''], $desStr);
            $desStr = $desStrs;
        }

        return [
            'title' => xphp_get_lang('UI_PLATFORM_SYSTEM_NOTICE'),
            'desc' => $desStr,
            'task_name' => $desStrs ?? '',
            'content' => $description,
        ];
    }
    /**
     * 发送企业微信通知
     * @param mixed $params 
     * @param mixed $alarmID 告警id
     * @param mixed $type 类型 2任务1系统
     * @return bool
     */
    public function sendTemplate($params, $alarmID, $type = 1)
    {
        $sqlupdate = "SELECT settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate) ?? [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            // 公众号模板消息
            $title = $params['title'];
            if (strlen($params['content']) > 32) {
                // 那么取 desc
                if (strlen($params['desc']) > 32) {
                    // 那么取 任务名称
                    $content = $params['task_name'] ?: $params['title'];
                    $content = strlen($content) > 32 ? $params['title'] : $content;
                } else {
                    $content = $params['desc'];
                }
            } else {
                $content = $params['content'];
            }

            if ($wechatContent['openid'] && $wechatContent['appid'] && $wechatContent['appsecret']) {
                $out_parma =  xphp_encrpt(json_encode(
                    [
                        'userUuid' => xphp_get_user_info()['useruuid'],
                        'alarmID' => $alarmID,
                        'type' => $type
                    ]
                ));
                // 发送模板
                $openid = array_column($wechatContent['openid'], 'openid'); // 消息接收人的openid
                $url = $wechatContent['template_url'] ?? 'https://' . $_SERVER['HTTP_HOST']; // 'https://www.vinchin.com'; // 跳转链接
                $url .=  '/alarm.php?param=' . $out_parma;

                return $this->sendWechat($openid, $wechatContent, $title, $content, $url);
            }
        }
        return false;
    }
    /**
     * 发送微信通知封装
     * @param mixed $openid 消息接收人的openid
     * @param mixed $wechatContent 消息内容
     * @param mixed $title 标题
     * @param mixed $content
     * @param mixed $url
     * @return bool
     */
    public function sendWechat($openid, $wechatContent, $title, $content, $url = '')
    {
        $data = [
            $wechatContent['template_param1'] => [
                'value' => $title,
                'color' => '#000000'
            ],
            $wechatContent['template_param2'] => [
                'value' => $content,
                'color' => '#666666'
            ],
        ];
        $config = [
            'appid' => $wechatContent['appid'],
            'appsecret' => $wechatContent['appsecret'],
        ];

        return xphp_send_wechat_template($openid, $wechatContent['template_id'], $data, $url, $config, $wechatContent['wechat_mode']);
    }
    /**
     * 更新通知结果
     * @param mixed $alarmID 告警ID号
     * @param mixed $type 系统告警/任务告警
     * @param mixed $mode 邮件/短信
     * @param mixed $result
     * @return bool
     */
    private function updateNoticeResult($alarmID, $type, $mode, $result)
    {
        $NOTICE_TYPE = xphp_get_config('app', 'NOTICE_TYPE');
        $NOTICE_MODE = xphp_get_config('app', 'NOTICE_MODE');

        if (in_array($mode, [$NOTICE_MODE['WE_CHAT'], $NOTICE_MODE['WE_CHAT2']])) {
            if (empty($result)) {
                return true;     //发送失败,不更新
            }
        } else {
            if (($result['code'] == 1)) {
                return true;     //发送失败,不更新
            }
        }

        if ($type == $NOTICE_TYPE['SYSTEM']) {
            $tableName = "bd_system_alarm";     //系统告警
            $tableID = "system_alarm_id";
        }
        if ($type == $NOTICE_TYPE['TASK']) {
            $tableName = "bd_task_alarm";       //任务告警
            $tableID = "task_alarm_id";
        }
        switch ($mode) {
            case $NOTICE_MODE['EMAIL']:
                $sendFlagName = "email_send_flag";      //邮件通知
                break;
            case $NOTICE_MODE['SMS']:
                $sendFlagName = "sms_send_flag";      //短信通知
                break;
            case  $NOTICE_MODE['WE_CHAT']:
                $sendFlagName = "wechat_send_flag";      //微信通知
                break;
            case $NOTICE_MODE['WE_CHAT2']:
                $sendFlagName = "enterprise_wechat_send_flag";      //企业微信通知
                break;
        }

        if (empty($tableName) || empty($sendFlagName)) return true;
        $sql = "UPDATE $tableName set $sendFlagName = ? where $tableID = ?";
        return $this->dbExec($sql, [xphp_get_config('app', 'FLAG')['SET'], $alarmID]);
    }
    /**
     * 得到系统邮件通知参数
     * @param mixed $systemAlarmID
     * @return array{attachment: array, email: array, info: array|string, title: mixed}
     */
    private function getSystemEmailParams($systemAlarmID)
    {
        $LOG_TYPE = xphp_get_config('log', 'LOGTYPE');
        $sql = "SELECT
                    alarm_level,
                    description_key,
                    description_param,
                    alarm_time,
                    error_code,
                    system_log_path 
                FROM
                    bd_system_alarm 
                WHERE
                    system_alarm_id = ?";
        $data = $this->dbSelect($sql, [$systemAlarmID]) ?? [];
        if (empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ALARM_GET_NOTICE_INFO')));
        }
        $description = (new LogInfo())->getLogDescriptionNotice(
            $LOG_TYPE['SYSTEM'],
            $data[0]['error_code'],
            $data[0]['description_key'],
            $data[0]['description_param'],
            "",
            false
        );
        $managerInfo = $this->getManagerEmailAndTelephone();
        $params = [
            'email' => $this->getAllEmail($managerInfo['email']),
            'title' => $description,
            'info' => $this->getSystemEmailBody($data[0]),
            'attachment' => [$data[0]['system_log_path']],
        ];
        return $params;
    }
    /**
     * 得到所有管理员的邮件和电话
     * @return array{email: array, telephone: array}
     */
    public function getManagerEmailAndTelephone()
    {
        $sql = "SELECT email, telephone from bd_user where user_type = ? or user_level = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('user', 'USERTYPE')['manager'], xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin']));
        $email = [];
        $telephone = [];
        foreach ($data as $d) {
            $email[] = $d['email'];
            $telephone[] = $d['telephone'];
        }
        $info = [
            'email' => $email,
            'telephone' => $telephone
        ];
        return $info;
    }
    /**
     * 获取所有邮箱
     * @param mixed $email
     * @return array
     */
    public function getAllEmail($email)
    {
        $sql = "SELECT receive_email from bd_email_notice";
        $data = $this->dbSelect($sql, []) ?? [];
        $setEmail = json_decode($data[0]['receive_email'], TRUE);
        $resultEmail = array_unique(array_merge($email, $setEmail));
        return $resultEmail;
    }
    /**
     * 获取系统邮件主体内容
     * @param mixed $data
     * @return array|string
     */
    private function getSystemEmailBody($data)
    {
        $LOG_SYSTEM = xphp_get_config('LOG_SYSTEM');
        $title = $LOG_SYSTEM[$data['description_key']];
        $subTitle = '';
        // if ($data['error_code']) {
        //     $errorConf = include CONF_PATH . 'error.php';
        //     $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data['error_code']]];
        //     $subTitle = "[#" . $data['error_code'] . "]" . $errorStr;
        // }
        if ($data['description_param']) {
            $title = (new LogInfo())->getLogDescription(
                xphp_get_config('log', 'LOGTYPE')['SYSTEM'],
                $data['error_code'],
                $data['description_key'],
                $data['description_param']
            );
        }

        //获取邮件模板内容并替换
        if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
            $info = file_get_contents(DATA_PATH . 'email/email-system-alarm-oem.html');
        } else if (xphp_get_config('app', 'lang') == "en-us") {
            $info = file_get_contents(DATA_PATH . 'email/email-system-alarm-en.html');
        } else {
            $info = file_get_contents(DATA_PATH . 'email/email-system-alarm.html');
        }
        $reportTime = date('Y-m-d H:i:s');
        $alarmLevelDes = xphp_get_desc('Pf', 'ALARM_LEVEL_DES')[2]; //告警等级为警告
        $info = str_replace('title', $title, $info);
        $info = str_replace('reportTime', $reportTime, $info);
        // $info = str_replace('subTitle', '', $info);
        $info = str_replace('alarmTime', $data['alarm_time'], $info);
        $info = str_replace('alarmLevel', $alarmLevelDes, $info);

        $info = str_replace('backupServerHost', (new ReportHandler())->getMasterNodeIpLink(), $info);
        $info = str_replace('supportEmailHref', 'mailto: ' . xphp_get_config('app', 'SYSTEM_INFO')['company_email'], $info);
        $info = str_replace('supportEmail', xphp_get_config('app', 'SYSTEM_INFO')['company_email'], $info);
        return $info;
    }
    /**
     * 发送企业微信通知
     * @param mixed $params
     * @param mixed $alarmID
     * @param mixed $type
     * @return bool
     */
    private function sendTemplate2($params, $alarmID, $type = 1)
    {
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 11";
        $wechats = $this->dbSelect($sqlupdate) ?? [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            // 企业微信通知方式
            $config = [
                "CORP_ID"               => $wechatContent['wechat_core_id'],
                "APP_ID"                => $wechatContent['wechat_app_id'],
                "APP_SECRET"            => $wechatContent['wechat_app_secret'],
            ];
            $url = $wechatContent['wechat_url'] ?? 'https://' . $_SERVER['HTTP_HOST']; // 'https://www.vinchin.com'; // 跳转链接
            $out_parma =  xphp_encrpt(json_encode(
                [
                    'userUuid' => xphp_get_user_info()['useruuid'],
                    'alarmID' => $alarmID,
                    'type' => $type
                ]
            ));

            $params['url'] = $url . '/alarm.php?param=' . $out_parma;
            return xphp_send_wework_api($config, $params);
        }
        return false;
    }
    /**
     * 获取系统告警sms参数
     * @param mixed $systemAlarmID
     * @return array{msg: string, tels: string}
     */
    private function getSystemSmsParams($systemAlarmID)
    {
        $LOG_TYPE = xphp_get_config('log', 'LOGTYPE');
        $sql = "SELECT alarm_level, description_key, description_param, alarm_time, error_code, system_log_path
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, [$systemAlarmID]) ?? [];
        if (empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ALARM_GET_NOTICE_INFO')));
        }
        $description = (new LogInfo())->getLogDescription(
            $LOG_TYPE['SYSTEM'],
            $data[0]['error_code'],
            $data[0]['description_key'],
            $data[0]['description_param'],
            ""
        );
        $managerInfo = $this->getManagerEmailAndTelephone();
        $telephone = implode(',', $managerInfo['telephone']);
        $params = [
            'tels' => $telephone,
            'msg' => $description . ". " . date("m-d H:i:s", strtotime($data[0]['alarm_time'])),
        ];
        return $params;
    }
    /**
     * 发送任务告警消息
     * @param mixed $emailFlag
     * @param mixed $smsFlag
     * @param mixed $taskAlarmID
     * @return {}
     */
    private function sendTaskNotice($emailFlag, $smsFlag, $taskAlarmID)
    {
        $NOTICE_TYPE = xphp_get_config('app', 'NOTICE_TYPE');
        $NOTICE_MODE = xphp_get_config('app', 'NOTICE_MODE');
        $checkResult = $this->checkNoticeSetting($emailFlag, $smsFlag, $NOTICE_TYPE['TASK'], $taskAlarmID);
        $emailFlag = $checkResult['email'];
        $smsFlag = $checkResult['sms'];
        $wechatFlag = $checkResult['wechat'];
        $wechat2Flag = $checkResult['wechat2'];
        $type = $NOTICE_TYPE['TASK'];

        if ($wechatFlag) {
            // 微信公众号通知发送
            $wechatInfo = $this->getTaskWechatParams($taskAlarmID);
            $wechatResult = $this->sendTemplate($wechatInfo, $taskAlarmID, 2);
            $this->updateNoticeResult($taskAlarmID, $type, $NOTICE_MODE['WECHAT'], $wechatResult);
        }

        if ($wechat2Flag) {
            // 企业微信通知发送
            $wechatInfo = $this->getTaskWechatParams($taskAlarmID);
            $wechatResult = $this->sendTemplate2($wechatInfo, $taskAlarmID, 2);
            $this->updateNoticeResult($taskAlarmID, $type, $NOTICE_MODE['WE_CHAT2'], $wechatResult);
        }

        if ($emailFlag && $smsFlag) {
            //邮件短信一起发
            $EmailInfo = $this->getTaskEmailParams($taskAlarmID);
            $SmsInfo = $this->getTaskSmsParams($taskAlarmID);

            $emailResult = (new Notice())->sendEmail($EmailInfo);
            $smsResult = (new Notice())->sendSms($SmsInfo);

            $this->updateNoticeResult($taskAlarmID, $type, $NOTICE_MODE['EMAIL'], $emailResult);
            $this->updateNoticeResult($taskAlarmID, $type, $NOTICE_MODE['SMS'], $smsResult);

            $result = false;
            if ($emailResult['code'] && $smsResult['code']) {
                $msg = xphp_get_lang('WEB_ALARM_SEND_NOTICE_SUCCESS');
                $result = true;
            } elseif ($emailResult['code']) {
                $msg = xphp_get_lang('WEB_ALARM_SEND_EMAIL_SUCCESS');
            } elseif ($smsResult['code']) {
                $msg = xphp_get_lang('WEB_ALARM_SEND_SMS_SUCCESS');
            } else {
                $msg = xphp_get_lang('WEB_ALARM_SEND_NOTICE_FAILURE');
            }
            return $this->muOpResult($result, xphp_get_lang('WEB_ALARM_SEND_NOTICE'), $msg, 'error');
        }
        if ($emailFlag) {
            //邮件通知
            $EmailInfo = $this->getTaskEmailParams($taskAlarmID);
            $emailResult = (new Notice())->sendEmail($EmailInfo);
            $this->updateNoticeResult($taskAlarmID, $type, $NOTICE_MODE['EMAIL'], $emailResult);
            return $emailResult;
        }
        if ($smsFlag) {
            //短信通知
            $SmsInfo = $this->getTaskSmsParams($taskAlarmID);
            $smsResult = (new Notice())->sendSms($SmsInfo);
            $this->updateNoticeResult($taskAlarmID, $type, $NOTICE_MODE['SMS'], $smsResult);
            return $smsResult;
        }
    }
    /**
     * 获取微信通知参数
     * @param mixed $taskAlarmID
     * @return array{content: mixed, desc: mixed, task_name: mixed, title: array|string}
     */
    private function getTaskWechatParams($taskAlarmID)
    {
        $sql = "SELECT
                    bta.alarm_level,
                    bta.description_key,
                    bta.description_param,
                    bta.alarm_time,
                    bta.task_name,
                    bta.task_type,
                    bta.module_type,
                    bta.submodule_type,
                    bta.node_name,
                    bta.storage_name,
                    bta.user_name,
                    bta.error_code,
                    bta.task_log_path,
                    bu.email,
                    bu.telephone 
                FROM
                    bd_task_alarm bta,
                    bd_user bu 
                WHERE
                    bta.user_uuid = bu.user_uuid 
                    AND bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, [$taskAlarmID]) ?? [];
        if (empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ALARM_GET_NOTICE_INFO')));
        }

        $confDes = (new LogInfo())->getLogConf(xphp_get_config('log', 'LOGTYPE')['TASK']);
        $desStr = $confDes[$data[0]['description_key']];

        if ($data[0]['error_code']) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data[0]['error_code']]];
            $desStrs = "[#" . $data[0]['error_code'] . "]" . $errorStr;
        }

        $description = (new LogInfo())->getLogDescriptionNotice(
            xphp_get_config('log', 'LOGTYPE')['TASK'],
            $data[0]['error_code'],
            $data[0]['description_key'],
            $data[0]['description_param'],
            $data[0]['task_name'],
            false
        );

        return [
            'title' =>  xphp_get_lang('UI_SETTINGS_NOTICE_TASK'),
            'task_name' => $data[0]['task_name'],
            'desc' => $desStrs ?? $desStr,
            'content' => $description,
        ];
    }
    /**
     * 获取任务邮件参数
     * @param mixed $taskAlarmID
     * @return array{attachment: array, email: array, info: array|string, title: mixed}
     */
    private function getTaskEmailParams($taskAlarmID)
    {
        // 查询告警详情信息
        $sql = "SELECT
                    bht.history_uuid,
                    bht.id AS history_id,
                    bta.alarm_level,
                    bta.description_key,
                    bta.description_param,
                    bta.alarm_time,
                    bta.task_uuid,
                    bta.task_name,
                    bta.task_type,
                    bta.module_type,
                    bta.submodule_type,
                    bta.node_name,
                    bta.storage_name,
                    bta.node_uuid,
                    bta.user_name,
                    bta.error_code,
                    bta.task_log_path,
                    bu.email,
                    bu.telephone
                FROM
                    bd_user bu,
                    bd_task_alarm bta
                    LEFT JOIN bd_history_task bht ON bht.history_uuid = bta.history_uuid 
                WHERE
                    bta.user_uuid = bu.user_uuid 
                    AND bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, [$taskAlarmID]) ?? [];
        if (empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ALARM_GET_NOTICE_INFO')));
        }
        $description = (new LogInfo())->getLogDescriptionNotice(
            xphp_get_config('log', 'LOGTYPE')['TASK'],
            $data[0]['error_code'],
            $data[0]['description_key'],
            $data[0]['description_param'],
            '',
            false
        );
        //开启防篡改，php没权限访问存储下的文件，调用接口获取文件内容重写到指定文件夹
        try {
            $attachment = $this->getRedirectAttach($data[0]['task_log_path'], $data[0]['node_uuid']);
        } catch (\Exception $e) {
            $attachment = [];
        }
        $params = [
            'email' => $this->getAllEmail([$data[0]['email'], $data[2]['email']]),
            'title' => $description,
            'info' => $this->getTaskEmailBody($data[0], $taskAlarmID),
            'attachment' => [$attachment],
        ];
        return $params;
    }
    /**
     * 获取文件内容重写到指定文件夹
     * @param mixed $path
     * @param mixed $nodeuuid
     */
    private function getRedirectAttach($path, $nodeuuid)
    {
        if (empty($path)) return $path;
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = json_encode(['file_path' => $path]);
        $msg = $this->mbNodeMsg($opName, $nodeuuid, $msg, true);
        if ($msg['result']) {
            //检查一个月定期清理日志文件;
            $cmd = "find " . xphp_get_config('app', 'TMP_PATH') . " logdir/ " . " -type f -mtime +30 -exec rm {} \;";
            exec($cmd);
            //日志文件另存为php有权限访问的路径
            $path = xphp_get_config('app', 'TMP_PATH') . "logdir/" . "log_attachment_" . date("Y_m_d_H_i_s");
            file_put_contents($path, $msg['msg']['file_content']);
        } else {
            //出错设置路径为空
            $path = "";
        }

        return $path;
    }
    /**
     * 获取任务告警邮件主体内容
     * @param mixed $data
     * @return array|string
     */
    private function getTaskEmailBody($data, $alarm_id)
    {
        $info = '';
        $FS_SUB = xphp_get_config('module', 'SUBMODULE_TYPE');
        $OS_SUB = xphp_get_config('module', 'OS_SUBMODULE_TYPE');
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $VM_GROUP = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        //模块类型
        $moduleDes = xphp_get_desc('Pf', 'MODULE_TYPE_DES')[$data['module_type']];
        switch ($data['module_type']) {
            case $MODULE_TYPE['VM']:
                if (in_array($data['submodule_type'], $VM_GROUP['publiccloud'])) {
                    //公有云
                    $moduleDes = xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD');
                }
                if (in_array($data['submodule_type'], $VM_GROUP['privatecloud'])) {
                    //私有云
                    $moduleDes = xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD');
                }
                $moduleDes .= "[" . xphp_get_config('vm', 'VMHYPERVISORDES')[$data['submodule_type']] . "]";
                break;
            case $MODULE_TYPE['FS']:
                if ($data['submodule_type'] ==  xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']) {
                    $moduleDes = xphp_get_lang('UI_PLATFORM_HADOOP_HDFS');
                }
                if ($data['submodule_type'] == $FS_SUB['OBS']) {
                    $moduleDes = xphp_get_lang('UI_PLATFORM_OBS_STORAGE');
                }
                if ($data['submodule_type'] == $FS_SUB['NAS']) {
                    $moduleDes = xphp_get_lang('UI_PLATFORM_NAS');
                }
                break;
            case $MODULE_TYPE['DB']:
                $moduleDes .= "[" . xphp_get_config('db', 'DB_TYPE_DES')[$data['submodule_type']] . "]";
                break;
            case $MODULE_TYPE['OS']:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_VOL');
                if ($data['submodule_type'] == $OS_SUB['MACHINE_OS']) {
                    $moduleDes = xphp_get_lang('WEB_MACHINE_OS_MACHINE_TIME');
                }
                break;
        }
        //备份任务才有存储名称字段
        $storage_name = empty($data['storage_name']) ? xphp_get_config('app', 'NULLSPACE') : $data['storage_name'];

        //获取邮件模板内容并替换
        if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
            $info = file_get_contents(DATA_PATH . 'email/email-task-alarm-oem.html');
        } else if (xphp_get_config('app', 'lang') == "en-us") {
            $info = file_get_contents(DATA_PATH . 'email/email-task-alarm-en.html');
        } else {
            $info = file_get_contents(DATA_PATH . 'email/email-task-alarm.html');
        }
        $ALARM_LEVEL_DES = xphp_get_desc('Pf', 'ALARM_LEVEL_DES');
        $reportTime = date('Y-m-d H:i:s');
        if (xphp_get_config('alarm', 'ALARM')['notice'] == $data['alarm_level']) {
            if ('BD_TASKLOG_DESC_KEY_TASK_STOPPED' == $data['description_key']) {
                // 任务停止时告警等级为警告
                $taskStatusDes = xphp_get_lang('WEB_LOG_TASK_TASK_STOPPED');
                $alarmLevelDes = $ALARM_LEVEL_DES[2]; //告警等级为警告
                $info = str_replace('font-style', 'font-warning', $info);
                $info = str_replace('bg-style', 'bg-warning', $info);
                $info = str_replace('alarm-level-style', 'font-warning', $info);
            } else {
                $taskStatusDes = xphp_get_lang('WEB_LOG_TASK_TASK_SUCCESS'); // 副标题
                $alarmLevelDes = $ALARM_LEVEL_DES[1]; //告警等级为一般
                //成功状态的字体和背景颜色
                $info = str_replace('font-style', 'font-success', $info);
                $info = str_replace('bg-style', 'bg-success', $info);
                $info = str_replace('alarm-level-style', 'font-normal', $info);
            }
        } elseif (xphp_get_config('alarm', 'ALARM')['general'] == $data['alarm_level']) {
            $taskStatusDes = (new LogInfo())->getLogDescriptionNotice(
                xphp_get_config('log', 'LOGTYPE')['TASK'],
                $data['error_code'],
                $data['description_key'],
                $data['description_param'],
                ''
            );
            $alarmLevelDes = $ALARM_LEVEL_DES[2]; //告警等级为警告

            $info = str_replace('font-style', 'font-warning', $info);
            $info = str_replace('bg-style', 'bg-warning', $info);
            $info = str_replace('alarm-level-style', 'font-warning', $info);
        } else {
            $errorConf = include CONF_PATH . 'error.php';
            $taskStatusDes = 'BD_TASKLOG_DESC_KEY_TASK_ABNORMAL' == $data['description_key'] ?
                xphp_get_lang('WEB_LOG_TASK_TASK_ABNORMAL') :
                xphp_get_lang('WEB_LOG_TASK_TASK_FAILURE');
            if ($data['error_code']) {
                $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data['error_code']]];
                $taskStatusDes .= ' | [#' . $data['error_code'] . ']' . $errorStr;
            }
            $alarmLevelDes = $ALARM_LEVEL_DES[intval($data['alarm_level'])];

            $info = str_replace('font-style', 'font-error', $info);
            $info = str_replace('bg-style', 'bg-error', $info);
            $info = str_replace('alarm-level-style', 'font-error', $info);
        }
        $info = str_replace('title', '[' . $data['task_name'] . ']', $info);
        $info = str_replace('reportTime', $reportTime, $info);

        if (in_array($data['description_key'], [
            'VM_TASK_DESC_KEY_PREPARE_BACKUP_TASK_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_CREATE_SNANPSHOT_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_CAL_VM_SIZE_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_TRANSFER_BACKUP_DATA_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_CREATE_TIMEPOINT_AND_CLEANUP_ERROR',
        ])) {
            // 单个虚拟机备份的告警
            $subTitle = (new LogInfo())->getLogDescriptionNotice(
                xphp_get_config('log', 'LOGTYPE')['TASK'],
                $data['error_code'],
                $data['description_key'],
                $data['description_param'],
                ''
            );
            $info = str_replace('subTitle', $subTitle, $info);
            $singleVmAlarm = true;
        } else {
            $info = str_replace('subTitle', $taskStatusDes, $info);
            $singleVmAlarm = false;
        }

        $info = str_replace('taskName', $data['task_name'], $info);
        $info = str_replace('taskType', xphp_get_desc('Pf', 'TASKTYPEDES')[$data['task_type']], $info);
        $info = str_replace('moduleType', $moduleDes, $info);
        $info = str_replace('nodeName', $data['node_name'] ?: xphp_get_config('app', 'NULLSPACE'), $info);
        $info = str_replace('storageName', $storage_name, $info);
        $info = str_replace('errorCode', $data['error_code'], $info);
        $info = str_replace('alarmTime', $data['alarm_time'], $info);
        $info = str_replace('alarmLevel', $alarmLevelDes, $info);
        $info = str_replace('backupServerHost', (new ReportHandler())->getMasterNodeIpLink(), $info);
        $info = str_replace('supportEmailHref', 'mailto: ' . xphp_get_config('app', 'SYSTEM_INFO')['company_email'], $info);
        $info = str_replace('supportEmail', xphp_get_config('app', 'SYSTEM_INFO')['company_email'], $info);
        //获取列表数据
        if (!empty($data['history_uuid'])) {
            $list = $this->getTaskEmailBodyList(
                $data['task_type'],
                $data['submodule_type'],
                $data['module_type'],
                $data['task_uuid'],
                $data['history_uuid'],
                $alarm_id
            );
            if ($list['td'] && !$singleVmAlarm) {
                //有列表数据则显示表格
                $info = str_replace('display-hide', 'display-show', $info);
                $info = str_replace('<tr><th>tableTh</th></tr>', $list['th'], $info);
                $info = str_replace('<tr><td>tableTd</td></tr>', $list['td'], $info);
            }
        }
        return $info;
    }
    /**
     * 获取告警邮件主体内容列表
     * @param mixed $taskType
     * @param mixed $sub_module_type
     * @param mixed $moduleType
     * @param mixed $taskuuid
     * @param mixed $historyUuid
     * @return array{td: string, th: string}
     */
    private function getTaskEmailBodyList($taskType, $sub_module_type, $moduleType, $task_uuid, $historyUuid, $alarm_id)
    {
        // 每个模块对应的对象名称中文
        $item_name = [
            "2-1" => xphp_get_lang('UI_BACKUP_REPORT_NAME'),
            "2-2" => xphp_get_lang('UI_BACKUP_REPORT_NAME'),
            "2-3" => xphp_get_lang('UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME'),
            "3-1" => xphp_get_lang('UI_AGENT_HOST_NAME'),
            "3-2" => xphp_get_lang('UI_PLATFORM_NAS_MANAGER'),
            "3-3" => xphp_get_lang('WEB_HADOOP_CLUSTER_NAME_DES'),
            "3-4" => xphp_get_lang('UI_PLATFORM_OBS_NAME'),
            "4" => xphp_get_lang('UI_DB_DATABASE_NAME') . '/' . xphp_get_lang('UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME'),
            "5-0" => xphp_get_lang('UI_AGENT_HOST_NAME'),
            "5-1" => xphp_get_lang('UI_AGENT_HOST_NAME'),
            "10-0" => xphp_get_lang('UI_AGENT_HOST_NAME'),
            "10-1" => xphp_get_lang('UI_AGENT_HOST_NAME'),
            "10-2" => xphp_get_lang('UI_AGENT_HOST_NAME'),
            "11-2" => xphp_get_lang('UI_PLATFORM_NAS_MANAGER'),
            "14-1" => xphp_get_lang('UI_ORGAN_ORGANIZATION_NAME'),
            "18-0" => xphp_get_lang('WEB_HADOOP_CLUSTER_NAME_DES'),
        ];
        $TASK_TYPE = xphp_get_config('task', 'TASKTYPE');
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $VM_STATUS_DES = xphp_get_desc('Vm', 'VmTaskStatus');
        $STATUS_DES = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $copy_module = [$TASK_TYPE['BACKUP_COPY'], $TASK_TYPE['BACKUP_COPY_FETCH'], $TASK_TYPE['ARCHIVE'], $TASK_TYPE['ARCHIVE_FETCH']];
        $info = [
            'th' => '',
            'td' => '',
        ];
        $item_key = $moduleType . '-' . $sub_module_type;
        // 副本对象列表显示
        if (in_array($taskType, $copy_module)) {
            //虚拟机
            $info['th'] = '<tr><th width="10%">' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th width="30%">' . $item_name[$item_key] . '</th><th width="10%">' . xphp_get_lang('UI_PUBLIC_STATUS') . '</th></tr>';
            $list = $this->getCopyItemList($historyUuid);
            foreach ($list as $l) {
                $class = $this->getClassByItemStatus($l['error_code']);
                $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . (new CopyJobInfo())->getHistoryJobResultDes($l['error_code']) . "</span></td></tr>";
            }
            return $info;
        } else {
            switch ($moduleType) {
                //虚拟机
                case $MODULE_TYPE['VM']:
                case $MODULE_TYPE['DB']:
                    if ($moduleType == $MODULE_TYPE['DB']) {
                        $item_key = $MODULE_TYPE['DB'];
                    }
                    $info['th'] = '<tr><th>' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th>' . $item_name[$item_key] . '</th><th>' . xphp_get_lang('UI_PUBLIC_STATUS') . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $class = $this->getClassByTaskStatus($l['task_status']);
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $VM_STATUS_DES[$l['task_status']] . "</span></td></tr>";
                    }
                    break;
                case $MODULE_TYPE['FS']:
                case $MODULE_TYPE['NAS']:
                    $info['th'] = '<tr><th>' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th>' . $item_name[$item_key] . '</th><th>' . xphp_get_lang('UI_BACKUP_FILE_LIST') . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td>{$l['source_list']}</td></tr>";
                    }
                    break;
                case $MODULE_TYPE['OS']:
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    if ($sub_module_type == 0) {
                        $info['th'] = '<tr><th>' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th>' . $item_name[$item_key] . '</th><th>' . xphp_get_lang('UI_PUBLIC_STATUS') . '</th></tr>';
                        foreach ($list as $l) {
                            $class = $this->getStatusClass($l['task_status']);
                            $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $STATUS_DES[$l['task_status']] . "</span></td></tr>";
                        }
                    } else {
                        $info['th'] = '<tr><th>' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th>' . $item_name[$item_key] . '</th><th>' . xphp_get_lang('UI_PUBLIC_STATUS') . '</th></tr>';
                        foreach ($list as $l) {
                            $class = $this->getStatusClass($l['task_status']);
                            $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $STATUS_DES[$l['task_status']] . "</span></td></tr>";
                        }
                    }
                    break;
                case $MODULE_TYPE['VOL_CDP']:
                    $info['th'] = '<tr><th>' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th>' . $item_name[$item_key] . '</th><th>' . xphp_get_lang('UI_VOL_CDP_STANDBY') . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td>{$l['target_name']}</td></tr>";
                    }
                    break;
                case $MODULE_TYPE['M365']:
                    $info['th'] = '<tr><th>' . xphp_get_lang('UI_PUBLIC_NUMBER') . '</th><th>' . $item_name[$item_key] . '</th><th>' . xphp_get_lang('WEB_KUBE_BACKUP_RESOURCE_LIST') . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType, $taskType);
                    foreach ($list as $l) {
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td>{$l['source_list']}</td></tr>";
                    }
                    break;
                case $MODULE_TYPE['FILE_COPY']:
                    $info['th'] = '<tr><th>' . xphp_get_lang('UI_FILE_COPY_SRC') . '</th><th>' . xphp_get_lang('UI_FILE_COPY_DES') . '</th><th>' . xphp_get_lang('WEB_KUBE_BACKUP_RESOURCE_LIST') . '</th></tr>';
                    $list = (new FileCopyJobInfo())->getFileCopyAlarm(['uuid' => $task_uuid, 'alarm_id' => $alarm_id]) ?? [];
                    $copy_list = $list['copy_list'] ?? [];
                    $source_list = '';
                    foreach ($copy_list as $l) {
                        $source_list .= $l['source'] . '->' . $l['target'] . '<br>';
                    }
                    $info['td'] .= "<tr><td>{$list['source_name']}</td><td>{$list['target_name']}</td><td>{$source_list}</td></tr>";
                    break;
            }
        }
        return $info;
    }
    /**
     * 根据任务状态获取样式
     * @param mixed $status
     * @return string
     */
    private function getStatusClass($status)
    {
        $class = "label label-sm label-info";
        switch ($status) {
            case 13:
            case 17:
                $class = "vm-status-success";
                break;
            case 6:
            case 7:
            case 8:
                $class = "vm-status-error";
                break;
            default:
                $class = "vm-status-info";
                break;
        }
        return $class;
    }
    /**
     * 返回副本对象列表
     * @param mixed $history_uuid
     * @return {}
     */
    private function getCopyItemList($history_uuid)
    {
        $sql = "SELECT details,error_code from bd_history_task where history_uuid = '{$history_uuid}' and details != '' order by id desc limit 1";
        $data = $this->dbSelect($sql, []) ?? [];
        if (!$data) {
            return [];
        }
        $details = $data[0]['details'];
        $data = json_decode($details, true);
        $list = [];
        foreach ($data as $k => $d) {
            $list[] = [
                'index' => $k + 1,
                'item_name' => $d['item_name'],
                'item_status' => $d['item_status'],
                'error_code' => $d['error_code'],
            ];
        }
        return $list;
    }
    private function geCopyResultDes($errorCode)
    {
        //异常的错误
        $abnormal = ['BD_TASK_ANBNORMAL_ERROR'];
        //中止的错误
        $discontinue = ['BD_TASK_BE_CANCELLED_ERROR'];
        $errorCodeArr = xphp_get_config('error', 'errorCode');
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return xphp_get_lang('WEB_PLATFORM_DES_DISCONTINUE');
        }
        return $errorCode == 0 ? xphp_get_lang('WEB_PLATFORM_DES_SUCCESSED') : xphp_get_lang('WEB_PUBLIC_FAILURE_HOMEPAGE');
    }
    /**
     * 获取任务对象列表
     * @param mixed $history_uuid
     * @param mixed $module_type
     * @return {mixed[]}
     */
    private function getTaskItemList($history_uuid, $module_type, $task_type = '')
    {
        $MODULE_TYPE = xphp_get_config('module', 'MODULE_TYPE');
        $list = [];
        $sql = "SELECT
                    details,
                    module_type,
                    submodule_type,
                    error_code 
                FROM
                    bd_history_task 
                WHERE
                    history_uuid = ? 
                    AND details != '' 
                ORDER BY
                    id DESC 
                    LIMIT 1";
        $data = $this->dbSelect($sql, [$history_uuid]) ?? [];
        if (!$data) {
            return $list;
        }
        $sub_module_type = $data[0]['submodule_type'];
        $details = $data[0]['details'];
        $data = json_decode($details, true);
        // 按模块返回不同的信息
        switch ($module_type) {
            case $MODULE_TYPE['VM']:
                $vms_details = $data['vms_details'] ?? $data;
                foreach ($vms_details as $k => $d) {
                    $list[] = [
                        'index' => $k + 1,
                        'item_name' => $d['dir_path'] ?: $d['vm_name'],
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case $MODULE_TYPE['FS']:
            case $MODULE_TYPE['NAS']:
                foreach ($data['agent_info_list'] as $k => $d) {
                    $list[] = [
                        'index' => ++$k,
                        'item_name' => $d['src_agent_name'] . "(" . $d['src_agent_ip'] . ")",
                        'source_list' => implode(';', $d['file_list']),
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case $MODULE_TYPE['DB']:
                foreach ($data as $k => $d) {
                    $list[] = [
                        'index' => ++$k,
                        'item_name' => $d['dir_path'],
                        'dir_path' => $d['dir_path'],
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case $MODULE_TYPE['OS']:
                foreach ($data as $k => $d) {
                    if ($sub_module_type == 0) {
                        $list[] = [
                            'index' => ++$k,
                            'item_name' => $d['os_name'] . "(" . $d['agent_ip'] . ")",
                            'task_status' => $d['task_status'],
                            'error_code' => $d['error_code'],
                        ];
                    } else {
                        $list[] = [
                            'index' => ++$k,
                            'item_name' => $d['os_name'] . "(" . $d['agent_ip'] . ")",
                            'task_status' => $d['task_status'],
                            'error_code' => $d['error_code'],
                        ];
                    }
                }
                break;
            case $MODULE_TYPE['VOL_CDP']:
                foreach ($data as $k => $d) {
                    $list[] = [
                        'index' => ++$k,
                        'item_name' => $d['hostname'] . "(" . $d['agent_ip'] . ")",
                        'target_name' => !empty($d['standby_agent_name']) ? $d['standby_agent_name'] . "(" . $d['standby_agent_ip'] . ")" : '---',
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case $MODULE_TYPE['M365']:
                $source_list = '';
                if ($task_type == xphp_get_config('task', 'TASKTYPE')['RECOVERY']) {
                    $source_list = implode(';', $data['recovery_m365_object_info_list']);
                } else {
                    $source_list = implode(';', $data['backup_m365_object_info_list']);
                }
                $list[] = [
                    'index' => 1,
                    'item_name' => $data['organization_name'],
                    'source_list' => $source_list,
                    'task_status' => $data['task_status'],
                    'error_code' => $data['error_code'],
                ];
                break;
            case $MODULE_TYPE['KUBERNETS']:
                // foreach($data['resources'] as $k => $d){
                //     $list[] = [
                //         'index' => ++$k,
                //         'item_name' => $d['name'],
                //         'source_list' => implode('<br>', $d['file_list']),
                //         'task_status' => $d['task_status'],
                //     ];
                // }
                break;
            case $MODULE_TYPE['FILE_COPY']:
                $copy_list = $data['copy_list'];
                $source_list = [];
                foreach ($copy_list as $k => $d) {
                    $source_list[] =  $d['source'] . "->" . $d['target'];
                }
                $source_name = $data['source_agent_nickname'] . "(" . $data['source_agent_ip'] . ")";
                // Nas端显示ip(路径)
                if ($data['source_type'] == 2) {
                    $source_name = $data['source_agent_ip'] . "(" . $data['source_agent_name'] . ")";
                }
                $target_name = $data['des_agent_nickname'] . "(" . $data['des_agent_ip'] . ")";
                // Nas端显示ip(路径)
                if ($data['target_type'] == 2) {
                    $target_name = $data['des_agent_ip'] . "(" . $data['des_agent_name'] . ")";
                }
                $list[] = [
                    'index' => 1,
                    'source_agent_name' => $source_name,
                    'des_agent_name' => $target_name,
                    'source_list' => implode('<br>', $source_list),
                ];
                break;
            default:
                break;
        }
        return $list;
    }
    /**
     * 得到任务短信通知参数
     * @param mixed $taskAlarmID
     * @return array{msg: string, tels: mixed}
     */
    private function getTaskSmsParams($taskAlarmID)
    {
        $sql = "SELECT
                    bta.alarm_level,
                    bta.description_key,
                    bta.description_param,
                    bta.alarm_time,
                    bta.task_name,
                    bta.task_type,
                    bta.module_type,
                    bta.submodule_type,
                    bta.node_name,
                    bta.storage_name,
                    bta.user_name,
                    bta.error_code,
                    bta.task_log_path,
                    bu.email,
                    bu.telephone 
                FROM
                    bd_task_alarm bta,
                    bd_user bu 
                WHERE
                    bta.user_uuid = bu.user_uuid 
                    AND bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, [$taskAlarmID]) ??  [];
        if (empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ALARM_GET_NOTICE_INFO')));
        }
        $description = (new LogInfo())->getLogDescriptionNotice(
            xphp_get_config('log', 'LOGTYPE')['TASK'],
            $data[0]['error_code'],
            $data[0]['description_key'],
            $data[0]['description_param'],
            $data[0]['task_name'],
            false
        );
        $params = [
            'tels' =>  $data[0]['telephone'],
            'msg' => $description . ". " . date("m-d H:i:s", strtotime($data[0]['alarm_time'])),
        ];
        return $params;
    }
    // -------------------发送告警邮件----------------
}
