<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 任务编排管理逻辑方法
 * @Date: 2023-12-19 15:44:21
 * @LastEditTime: 2026-01-14 10:50:07
 * @Version: 1.0
 * @copyright: Copyright 2024 vinchin.com
 */

namespace app\v1\orchestration\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\common\logic\JobInfo;
use app\v1\job\v0\logic\JobInfo as currentJob;
use app\v1\backupData\v0\logic\DataManage;

class taskOrchestration extends Base
{
    /**
     * @description: 创建编排计划
     * @param {*} $params
     * @return {*}
     */
    public function addOrchestrationList($params)
    {
        $backup_mode = xphp_get_config('task', 'BACKUP_MODE');
        $user = xphp_get_user_info();
        $pfMsg = array();
        $pfMsg['task_orchestration_plan'] = $params['task_orchestration_plan'];
        $time_strategy = $params['task_orchestration_plan']['time_strategy'] != [] ? $this->groupTimeStrategy($backup_mode['FULL'], $params['task_orchestration_plan']['time_strategy']) : array();
        $pfMsg['task_orchestration_plan']['time_strategy'] = $time_strategy;
        $pfMsg['task_orchestration_plan']['user_uuid'] = $user['userUuid'];
        // 操作
        $opName = 'SS_OP_ADD_TYPE_TASK_ORCHESTRATION_PLAN';
        if ($params['task_orchestration_plan']['plan_uuid']) { // 修改任务编排计划
            $opName = 'SS_OP_MOD_TYPE_TASK_ORCHESTRATION_PLAN';
        }
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $node_uuid = $this->getLocalNodeUUID();
        $msg = $pfMsg;
        $mbResult = $this->service()->operateOrchestrationPlan($node_uuid, $opName, $msg);
        // 选择是否接受服务通信结果 然后返回到控制器
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }
    /**
     * @description: 删除编排计划
     * @param {*} $params
     * @return {*}
     */
    public function deleteOrchestrationList($params)
    {
        $msg = [
            'plan_uuid_list' => $params['plan_uuid_list'],
        ];
        // 操作
        $opName = 'SS_OP_DEL_TYPE_TASK_ORCHESTRATION_PLAN';
        $node_uuid = $this->getLocalNodeUUID();
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        // dump($opName, $msg);
        $mbResult = $this->service()->operateOrchestrationPlan($node_uuid, $opName, $msg);
        // 选择是否接受服务通信结果 然后返回到控制器
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }
    /**
     * @description: 编排计划操作  1.启动策略 2.启动 3.停止
     * @param {*} $params
     * @return {*}
     */
    public function operateOrchestration($params)
    {
        $opMode = intval($params['op_mode']);
        $msg = [];
        $node_uuid = $this->getLocalNodeUUID();
        if ($opMode == 1) {
            // 启动策略
            $opName = "SS_OP_ENABLE_TASK_ORCHESTRATION_PLAN";
            $msg = [
                'plan_uuid_list' => $params['plan_uuid'],
            ];
            $operate = (new PfOpcode())->getOpcodeDes($opName);
            $mbResult = $this->service()->operateOrchestrationPlan($node_uuid, $opName, $msg);
        }
        if ($opMode == 2) {
            // 启动计划
            $opName = "SS_OP_START_TYPE_TASK_ORCHESTRATION_PLAN";
            $msg['task_orchestration_plan'] = [
                'plan_uuid' => $params['plan_uuid'],
            ];
            $operate = (new PfOpcode())->getOpcodeDes($opName);
            $nodeList = $this->getTaskUsedNode($params['plan_uuid'], $operate);
            if (!in_array($node_uuid, $nodeList)) {
                array_push($nodeList, $node_uuid);
            }
            foreach ($nodeList as $n) {
                $mbResult = $this->service()->operateOrchestrationPlan($n, $opName, $msg);
            }
        }
        if ($opMode == 3) {
            // 停止
            $opName = "SS_OP_STOP_TYPE_TASK_ORCHESTRATION_PLAN";
            $msg = [
                'plan_uuid_list' => $params['plan_uuid'],
            ];
            $operate = (new PfOpcode())->getOpcodeDes($opName);
            $nodeList = $this->getTaskUsedNode($params['plan_uuid'], $operate);
            if (!in_array($node_uuid, $nodeList)) {
                array_push($nodeList, $node_uuid);
            }
            foreach ($nodeList as $n) {
                $mbResult = $this->service()->operateOrchestrationPlan($n, $opName, $msg);
            }
        }
        // 选择是否接受服务通信结果 然后返回到控制器
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    private function getTaskUsedNode($plan_uuid, $operate)
    {
        $sql = "SELECT bn.node_uuid,bn.status,bms.online_flag FROM bd_task bt, bd_task_orchestration_plan_section_task bo, bd_module_server bms, bd_node bn WHERE bt.task_uuid = bo.task_uuid AND bn.node_uuid = bt.node_uuid AND bms.node_uuid = bt.node_uuid";
        if (is_array($plan_uuid)) {
            $uuidArr = "('" . implode("','", $plan_uuid) . "')";
            $sql .= " AND bo.plan_uuid IN " . $uuidArr . " group by bn.node_uuid";
        } else {
            $sql .= " AND bo.plan_uuid = '" . $plan_uuid . "' group by bn.node_uuid";
        }
        $nodeList = array();
        $data = $this->dbSelect($sql, array());
        foreach ($data as $d) {
            if (intval($d['status']) == 0 && !!$d['online_flag']) {
                $nodeList[] = $d['node_uuid'];
            } else {
                return $this->muOpResult(false, $operate, xphp_get_lang('UI_JOB_TASK_ORCHESTRATION_OPERATE_NODE_DISABLE'));
            }
        }
        return $nodeList;
    }

    /**
     * @description: 获取所有的编排计划列表
     * @return {*}
     */
    public function getOrchestrationList($params)
    {
        $offset = $params['offset'];
        $limit = $params['limit'];
        $search = $params['search'];
        $sqlParams = array();
        $sql = "SELECT btop.plan_uuid, btop.plan_nickname, btop.time_strategy_id, btop.plan_state, btop.tasks_number, btop.execute_count, btop.details, btop.create_time,btop.user_uuid,
                bu.user_name,
                bts.last_finish_time, bs.next_start_time
                FROM bd_user bu, bd_task_orchestration_plan btop
                LEFT JOIN bd_time_strategy bts ON bts.strategy_id = btop.time_strategy_id
                LEFT JOIN bd_strategy bs ON bs.strategy_id = btop.time_strategy_id
                WHERE bu.user_uuid = btop.user_uuid";
        $sqlCount = "SELECT COUNT(btop.plan_uuid) AS total
                from bd_user bu, bd_task_orchestration_plan btop
                LEFT JOIN bd_time_strategy bts ON bts.strategy_id = btop.time_strategy_id
                LEFT JOIN bd_strategy bs ON bs.strategy_id = btop.time_strategy_id
                where bu.user_uuid = btop.user_uuid";
        // 全局观察者权限
        if (v1_auth_need_check_look()) {
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['current_job']);
            return " AND btop.user_uuid IN ({$user_uuid_arr})";
        }
        if (!empty($search)) {
            $sql .= " AND btop.plan_nickname LIKE '%" . $search . "%'";
            $sqlCount .= " AND btop.plan_nickname LIKE '%" . $search . "%'";
        }
        $sql .= " GROUP BY btop.plan_uuid";
        // 排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = array(
                'plan_nickname' => 'btop.plan_nickname',
                'plan_state' => 'btop.plan_state',
                'tasks_number' => 'btop.tasks_number',
                'execute_count' => 'btop.execute_count',
                'user_name' => 'bu.user_name',
                'create_time' => 'btop.create_time',
            );
            if (!empty($sortArr[$params['sort']]) && in_array($params['order'], ['asc', 'desc'])) {
                $sql .= " ORDER BY {$sortArr[$params['sort']]} {$params['order']} ";
            }
        }
        $sql .= " limit ?, ?";
        // dump($sql);
        $data = $this->dbSelect($sql, array_merge($sqlParams, [$offset, $limit]));
        $dataCount = $this->dbSelect($sqlCount, array());

        $records = array();
        $records['rows'] = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $records['rows'][] = array(
                    'plan_uuid' => $d['plan_uuid'], //计划uuid
                    'plan_nickname' => $d['plan_nickname'], //名称
                    'plan_state' => $d['plan_state'], //状态
                    'create_time' => $d['create_time'], //更新时间
                    'tasks_number' => $d['tasks_number'], //任务数量
                    'execute_count' => $d['execute_count'], //执行次数
                    'user_name' => $d['user_name'], //作者
                    'time_strategy' => $this->getJobTimeStrategy($d['time_strategy_id']),
                    'current_running_section' => $this->getRunningSection($d['plan_uuid'], $d['plan_state']),
                    'plan_actions' => array(1, 2, 3),
                    'last_time' => $d['last_finish_time'],
                    'next_time' => $this->getNextStartTime($d['next_start_time'], $d['plan_state']),
                    'user_uuid' => $d['user_uuid'],
                );
            }
        }
        $records['total'] = $dataCount[0]['total'];
        return $records;
    }

    /**
     * 过滤下次启动时间
     * @param  $nextTime   下次开始时间
     * @param  $taskStatus 任务状态
     * @return string
     */
    protected function getNextStartTime($nextTime, $planState)
    {
        $nowTime = time();
        if ($nextTime <= 0 || $nextTime < $nowTime || $planState == xphp_get_config('orchestration', 'ORCHESTRATION_PLAN_STATUS')['STOPPED']) {
            return xphp_get_config('app', 'TIMESPACE');
        } else if ($planState == xphp_get_config('orchestration', 'ORCHESTRATION_PLAN_STATUS')['WAITING']) {
            return $nextTime;
        }
        return xphp_get_config('app', 'TIMESPACE');
    }


    /**
     * @description: 获取时间策略信息
     * @param {*} $strategyID
     * @return {*}
     */
    private function getJobTimeStrategy($strategyID)
    {
        $sql = "SELECT mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time,full_backup_compensation_flag
                fROM bd_time_strategy
                WHERE strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $allBackupMode = xphp_get_config('task', 'BACKUP_MODE');
        $strategy = array();
        foreach ($data as $d) {
            $startTime = $d['start_time'];
            if (
                intval($d['strategy_type']) == xphp_get_config('task', 'STRATEGY_TYPE')['ONCE'] &&
                strtotime($d['start_time']) < time()
            ) {
                $startTime = $d['start_time'] . '(' . xphp_get_lang('UI_PUBLIC_EXPIRED') . ')';
            }
            $backupMode = (int) $d['mode'];
            if (1 != count($data) || $allBackupMode['INCREMENTAL'] == $data[0]['mode']) {
                $backupMode = $allBackupMode['PINCREMENTAL'];
            }
            $strategy[] = array(
                //只有一条增量则是永久增量
                'mode' => $backupMode,
                'type' => $d['strategy_type'], // 1是天,2是周,3是月,4是一次性
                'days' => $this->getDaysArr($d['days']),
                'frequency' => $this->getFrequency($d['strategy_type'], $d['days']),
                'startTime' => $startTime,
                'rollFlag' => xphp_get_config('app', 'FLAG')['SET'] == intval($d['roll_flag']) ? true : false,
                'rollInterval' => v1_sec_to_time($d['roll_interval']),
                'endTime' => $d['roll_end_time'],
                'full_backup_compensation_flag'=> v1_parse_flag_to_bool($d['full_backup_compensation_flag']),
            );
        }
        return $strategy;
    }

    /**
     * @description: 获取频率
     * @param {*} $strategyType
     * @param {*} $days
     * @return {*}
     */
    private function getFrequency($strategyType, $days)
    {
        $frequency = '';
        if (xphp_get_config('task', 'STRATEGY_TYPE')['EVERY_WEEK'] != intval($strategyType)) {
            return $frequency;
        }
        $strIndex = strpos($days, 's');
        if ($strIndex) {
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }

    /**
     * @description: 获取日期数组
     * @param {*} $days
     * @return {*}
     */
    private function getDaysArr($days)
    {
        $count = strlen($days);
        $daysArr = array();
        for ($i = 0; $i < $count; $i++) {
            if ('s' == substr($days, $i, 1)) {
                break;
            }
            $daysArr[] = intval(substr($days, $i, 1));
        }
        return $daysArr;
    }

    /**
     * @description: 得到当前运行阶段
     * @param {*} $plan_uuid
     * @param {*} $plan_state
     * @return {*}
     */
    private function getRunningSection($plan_uuid, $plan_state)
    {
        $sql = "SELECT section_state, section_id, details FROM bd_task_orchestration_plan_section WHERE plan_uuid = ? ORDER BY section_id";
        $data = $this->dbSelect($sql, array($plan_uuid));
        $i = 0;
        $runningId = '';
        $j = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $details = json_decode($d['details'], true);
                $remark = $details['remarks'] ? '(' . $details['remarks'] . ')' : '';
                $i++;
                if ($d['section_state'] == 2) {
                    $j++;
                    if ($j > 1) {
                        $runningId .= '，' . xphp_get_lang('UI_JOB_TASK_ORCHESTRATION_SECTION') . $i . $remark;
                    } else {
                        $runningId .= xphp_get_lang('UI_JOB_TASK_ORCHESTRATION_SECTION') . $i . $remark;
                    }
                };
            }
        }
        if ($plan_state == 2) {
            return $runningId;
        } else {
            return xphp_get_config('app', 'TIMESPACE');
        }
    }

    /**
     * @description: 得到默认计划名
     * @return {*}
     */
    public function getPlanName()
    {
        $taskName = xphp_get_lang('UI_JOB_TASK_ORCHESTRATION');
        $name = $this->getValidName($taskName);
        $info = array(
            'name' => $name
        );
        return $info;
    }

    /**
     * @description: 获取不重复的名称
     * @param {*} $taskName
     * @return {*}
     */
    private function getValidName($taskName)
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "SELECT plan_id FROM bd_task_orchestration_plan WHERE plan_nickname = ?";
            $data = $this->dbSelect($sql, array($taskName));
            if (empty($data)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * @description: 获取任务列表
     * @param {*} $params
     * @return {*}
     */
    public function getTaskList($params)
    { // 参数验证和处理
        $statusArr = $params['status'];
        $taskType = $params['task_type'];
        $moduleTypeArr = $params['module_type'];
        $sub_module_type = $params['sub_module_type'];
        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $startTime = $params['start_time']; // 开始时间
        $endTime = $params['end_time']; // 结束时间
        $searchStr = $params['search'];
        // 配置信息
        $flag = xphp_get_config('app', 'FLAG');
        $sortArr = [
            "create_time" => "bt.create_time",
            "status" => "bt.task_status",
        ];
        // 查询参数
        $sqlParams = array();
        $sqlParamsCount = array();

        // 构造基础查询
        $sql = $this->buildQuery();
        $sqlCount = $this->buildQuery(true);

        // 过滤条件处理
        // 过滤状态
        $sql .= $this->buildStatusFilter($statusArr);
        $sqlCount .= $this->buildStatusFilter($statusArr);
        // 过滤模块类型
        $sql .= $this->buildModuleTypeFilter($moduleTypeArr, $sub_module_type);
        $sqlCount .= $this->buildModuleTypeFilter($moduleTypeArr, $sub_module_type);
        // 过滤任务类型
        $sql .= $this->buildTaskTypeFilter($taskType);
        $sqlCount .= $this->buildTaskTypeFilter($taskType);
        // 过滤时间段
        $sql .= $this->buildTimeFilter($startTime, $endTime);
        $sqlCount .= $this->buildTimeFilter($startTime, $endTime);
        if (!empty($searchStr)) {
            $sql .= " and bt.task_name like '%" . $searchStr . "%'";
            $sqlCount .= " and bt.task_name like '%" . $searchStr . "%'";
        }
        // 分页
        $sql .= " group by bt.task_uuid";
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sql .= " order by {$sortArr[$params['sort']]} {$params['order']}";
        }
        $sql .= " limit ?,?";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        // dump($sql, $sqlParams);
        // 查询未加入计划任务
        $data = $this->dbSelect($sql, $sqlParams);
        // 查询未加入计划任务数量
        $dataCount = $this->dbSelect($sqlCount, $sqlParamsCount);
        $records = array();
        $records['rows'] = array();
        $records['total'] = 0;
        // $data = array_merge($data, $dataEdit);
        if (!empty($data)) {
            $records = $this->processTaskData($data);
        }
        $records['total'] = $dataCount[0]['total'];
        return $records;
    }
    /**
     * @description: 创建基础查询语句
     * @param {*} $deleteFlagField
     * @param {*} $deleteFlagValue
     * @param {*} $planFlagField
     * @param {*} $planFlagValue
     * @param {*} $isCountQuery
     * @param {*} $isEditQuery
     * @param {*} $planUuid
     * @return {*}
     */
    private function buildQuery($isCountQuery = false)
    {
        $sql = "SELECT ";
        // 查询数量标志
        if ($isCountQuery) {
            $sql .= "COUNT(DISTINCT bt.task_uuid) as total FROM bd_strategy bs, bd_task bt ";
        } else {
            $sql .= "bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.create_time, bt.task_status, bt.task_orchestration_plan_flag, bt.sub_module_type, bts.strategy_id, bs.time_strategy_backup_type, dt.db_type FROM bd_strategy bs, bd_task bt ";
        }
        $sql .= "LEFT JOIN bd_time_strategy bts ON bt.strategy_id = bts.strategy_id
                LEFT JOIN db_task dt ON bt.task_uuid = dt.task_uuid";
        $sql .= " WHERE bs.strategy_id = bt.strategy_id AND bs.time_strategy_backup_type != 2 AND bt.delete_flag = 2";
        // 全局观察者权限
        if (v1_auth_need_check_look()) {
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['current_job']);
            $sql.= " AND bt.user_uuid IN ({$user_uuid_arr})";
        }
        return $sql;
    }
    /**
     * @description: 模块类型查询
     * @param {*} $moduleTypeArr
     * @return {*}
     */
    private function buildModuleTypeFilter($moduleTypeArr, $sub_module_type)
    {
        $sql = "";
        if (!empty($moduleTypeArr)) {
            $sql = " AND bt.module_type IN (" . implode(",", $moduleTypeArr) . ")";
            if(!empty($sub_module_type)){
                $sql .= " AND bt.sub_module_type IN (" . implode(",", $sub_module_type) . ") ";
            }
        }
        return $sql;
    }
    /**
     * @description: 任务状态查询
     * @param {*} $statusArr
     * @return {*}
     */
    private function buildStatusFilter($statusArr)
    {
        if (!empty($statusArr)) {
            return " AND bt.task_status in (" . implode(",", $statusArr) . ")";
        }
        return '';
    }
    /**
     * @description: 任务类型查询
     * @param {*} $taskType
     * @return {*}
     */
    private function buildTaskTypeFilter($taskType)
    {
        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        $backupArr = [$taskTypeArr['BACKUP'], $taskTypeArr['DB_BACKUP'], $taskTypeArr['OS_BACKUP'], $taskTypeArr['NAS_BACKUP'],$taskTypeArr['KUBE_BACKUP']];
        $recoveryArr = [$taskTypeArr['DB_RECOVERY'],$taskTypeArr['RECOVERY']];
        $copyArr = [$taskTypeArr['BACKUP_COPY']];
        $archiveArr = [$taskTypeArr['ARCHIVE']];
        $replicationArr = [$taskTypeArr['FILE_COPY'], $taskTypeArr['VOL_CDP_REPLICATION']];
        $sql = "";
        if (!empty($taskType)) {
            switch ($taskType) {
                case 1:
                    $typePart = implode(",", $backupArr);
                    $sql = " AND bt.task_type IN ($typePart)";
                    break;
                case 2:
                    $typePart = implode(",", $recoveryArr);
                    $sql = " AND bt.task_type IN ($typePart) AND bts.strategy_type != 4";
                    break;
                case 3:
                    $typePart = implode(",", $copyArr);
                    $sql = " AND bt.task_type IN ($typePart)";
                    break;
                case 4:
                    $typePart = implode(",", $archiveArr);
                    $sql = " AND bt.task_type IN ($typePart)";
                    break;
                case 5:
                    $sql = " AND bt.task_type = {$taskTypeArr['SURE_BACKUP']}";
                    break;
                case 6:
                    $typePart = implode(",", $replicationArr);
                    $sql = " AND bt.task_type IN ($typePart)";
                    break;
            }
        }
        return $sql;
    }
    /**
     * @description: 时间范围查询
     * @param {*} $startTime
     * @param {*} $endTime
     * @return {*}
     */
    private function buildTimeFilter($startTime, $endTime)
    {
        if (!empty($startTime) && !empty($endTime)) {
            return " AND bt.create_time BETWEEN '{$startTime}' AND '{$endTime}' ";
        }
        return '';
    }
    /**
     * @description: 返回数据
     * @param {*} $taskData
     * @return {*}
     */
    private function processTaskData($taskData)
    {
        $jobInfo = new currentJob();
        $records = ['rows' => []];
        $strategyIdCache = [];
        $i = 0;
        foreach ($taskData as $d) {
            $strategyId = $d['strategy_id'] ?? null;
            $timeStrategy = $strategyIdCache[$strategyId] ?? '';
            $timeStrategy = '';

            if ($strategyId !== null && !isset($strategyIdCache[$strategyId])) {
                $timeStrategy = $this->getJobTimeStrategy($strategyId);
            }
            $records['rows'][] = [
                'id' => $i++,
                'task_uuid' => $d['task_uuid'],
                'task_name' => $d['task_name'],
                'task_type' => $d['task_type'],
                'sub_type' => $d['db_type'],
                'task_type_des' => $jobInfo->getTaskNameString(
                    xphp_get_config('module', 'MODULE_TYPE'),
                    xphp_get_config('task', 'TASKTYPE'),
                    $d['module_type'],
                    $d['task_type']
                ),
                'module_type' => $d['module_type'],
                'module_type_des' => (new DataManage())->getModuleType($d['module_type'], $d['sub_module_type']),
                'create_time' => $d['create_time'],
                'status' => $d['task_status'],
                'strategy_id' => $d['strategy_id'],
                'sub_module_type' => $d['sub_module_type'],
                'time_strategy' => $timeStrategy,
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
            ];
        }

        return $records;
    }

    private function getTaskProgress($task_uuid)
    {
        $jobInfo = new JobInfo();
        $sql = "SELECT bt.task_type, bt.task_status, bt.module_type AS module_type_agent, bri.total_object_size, bri.total_object_completed_size, bri.total_object_valid_size,bri.total_object_completed_valid_size ,ssb.task_progress
        FROM bd_running_info bri, bd_task bt
        LEFT JOIN sr_sure_backup ssb ON bt.task_uuid = ssb.task_uuid 
        WHERE bt.task_uuid = bri.task_uuid AND bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $progress = $jobInfo->getCurrentTaskProgress($data[0]);
        return $progress;
    }

    /**
     * @description: 组装时间策略
     * @param {*} $mode
     * @param {*} $strategy
     * @return {*}
     */
    private function groupTimeStrategy($mode, $strategy)
    {
        $strategy_type = xphp_get_config('task', 'STRATEGY_TYPE');
        $strategy_roll_type = xphp_get_config('task', 'STRATEGY_ROLL_TYPE');
        $this->paramsCheck($mode, $strategy);
        $strArr = array("mode" => $mode);
        //时间策略
        if ($mode == 7) {
            $strategy = $strategy[0];
        }
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['days'] = empty($strategy['days']) ? "" : implode("", $strategy['days']) . $strategy['frequency'];
        $strArr['start_time'] = v1_formart_time($strategy['startTime']);
        $strArr['roll_flag'] = $strategy['rollFlag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['rollInterval']);
        $strArr['roll_end_time'] = $strategy['endTime'];

        if ($strategy_type['EVERY_DAY'] == intval($strArr['strategy_type'])) {
            //每天备份
            $strArr['days'] = "1111111";
        }

        if ($strategy_type['ONCE'] == intval($strArr['strategy_type'])) {
            //一次性备份
            $strArr['days'] = '';
        }

        if ($strArr['roll_flag']) {
            //滚动备份
            $strArr['roll_flag'] = $strategy_roll_type['ON'];
        } else {
            //不滚动
            $strArr['roll_flag'] = $strategy_roll_type['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }
    /**
     * @description: 获取滚动间隔
     * @param {*} $rollInterval
     * @return {*}
     */
    private function getRollInterval($rollInterval)
    {
        if (empty($rollInterval)) {
            return 0;
        }
        $intervalArr = explode(":", $rollInterval);
        $second = intval($intervalArr[0]) * 3600 + intval($intervalArr[1]) * 60 + intval($intervalArr[2]);
        return $second;
    }
    /**
     * @description: 获取当前主节点
     * @return {*}
     */
    public function getLocalNodeUUID()
    {
        $sql = "SELECT node_uuid FROM bd_node WHERE node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }
    /**
     * @description: 获取详细信息
     * @param {*} $params
     * @return {*}
     */
    public function getPlanDetails($params)
    {
        $plan_uuid = $params['plan_uuid'];
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        $sql = "SELECT btop.plan_uuid, btop.plan_nickname, btop.time_strategy_id, btop.plan_state, btop.tasks_number, btop.execute_count, btop.details, btop.create_time,
                bu.user_name,
                bts.last_finish_time, bs.next_start_time
                FROM bd_user bu, bd_task_orchestration_plan btop
                LEFT JOIN bd_time_strategy bts ON bts.strategy_id = btop.time_strategy_id
                LEFT JOIN bd_strategy bs ON bs.strategy_id = btop.time_strategy_id
                where bu.user_uuid = btop.user_uuid AND btop.plan_uuid = ?";
        $data = $this->dbSelect($sql, array($plan_uuid));
        // var_dump($data);
        $STATUS = xphp_get_config('orchestration', 'ORCHESTRATION_PLAN_STATUS');
        $info = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $state = $nullSpace;
                if ($d['plan_state'] == $STATUS['WAITING'] || $d['plan_state'] == $STATUS['RUNNING']) {
                    $state = $d['next_start_time'] ?? $nullSpace;
                }
                $info = array(
                    'plan_uuid' => $d['plan_uuid'], //计划uuid
                    'plan_nickname' => $d['plan_nickname'], //名称
                    'time_strategy_id' => $d['time_strategy_id'], //名称
                    'time_strategy' => $this->getJobTimeStrategy($d['time_strategy_id']), //时间策略
                    'section_list' => $this->getSectionList($d['plan_uuid']),
                    'tasks_number' => $d['tasks_number'],
                    'last_finish_time' => $d['last_finish_time'] ?? $nullSpace,
                    'next_start_time' => $state,
                    'execute_count' => $d['execute_count'],
                    'plan_state' => $d['plan_state'],
                );
            };
        }
        return $info;
    }
    /**
     * @description: 获取阶段详情
     * @param {*} $plan_uuid
     * @return {*}
     */
    private function getSectionList($plan_uuid)
    {
        $sql = "SELECT section_uuid,section_state, section_id, start_type, last_section_uuid, event_type,execution_interval,drive_task_uuid,details,start_time, end_time, trigger_flag FROM bd_task_orchestration_plan_section WHERE plan_uuid = ? ORDER BY section_id";
        $data = $this->dbSelect($sql, array($plan_uuid));
        $sections = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $sections[] = array(
                    'section_state' => $d['section_state'],
                    'start_type' => $d['start_type'],
                    'last_section_uuid' => $d['last_section_uuid'],
                    'event_type' => $d['event_type'],
                    'execution_interval' => $d['execution_interval'],
                    'drive_task_uuid' => $d['drive_task_uuid'],
                    'details' => $d['details'],
                    'task_list' => $this->getSectionTask($d['section_uuid']),
                    'driveDes' => $this->getDriveTaskName($d['drive_task_uuid']),
                    'start_time' => $d['start_time'],
                    'end_time' => $d['end_time'],
                    'trigger_flag' => v1_parse_flag_to_bool($d['trigger_flag']),
                );
            }
        }
        return $sections;
    }
    /**
     * @description: 获取驱动事件触发的任务名
     * @param {*} $list
     * @return {*}
     */
    private function getDriveTaskName($list)
    {
        $des = '';
        if ($list != '[]') {
            $taskIds = json_decode($list);
            $i = 0;
            foreach ($taskIds as $id) {
                $sql = "SELECT task_name FROM bd_task WHERE task_uuid = ?";
                $data = $this->dbSelect($sql, array($id));
                if ($i == 0) {
                    $des .= $data[0]['task_name'];
                } else {
                    $des .= '，' . $data[0]['task_name'];
                }
                $i++;
            }
        }
        return $des;
    }
    /**
     * @description: 获取阶段任务信息
     * @param {*} $section_uuid
     * @return {*}
     */
    private function getSectionTask($section_uuid)
    {
        $sql = "SELECT backup_mode,task_uuid,details FROM bd_task_orchestration_plan_section_task WHERE section_uuid = ?";
        $data = $this->dbSelect($sql, array($section_uuid));
        $taskList = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $taskList[] = array(
                    'backup_mode' => $d['backup_mode'],
                    'task_uuid' => $d['task_uuid'],
                    'details' => $d['details'],
                    'info' => $this->getTaskInfo($d['task_uuid']),
                );
            };
        }
        return $taskList;
    }
    /**
     * @description: 获取阶段任务的详细信息
     * @param {*} $task_uuid
     * @return {*}
     */
    private function getTaskInfo($task_uuid)
    {
        $sql = "SELECT bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.create_time, bt.task_status, bts.strategy_id, bt.sub_module_type 
                FROM bd_task bt
                LEFT JOIN bd_time_strategy bts ON bts.strategy_id = bt.strategy_id
                WHERE bt.delete_flag = ? AND bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['UNSET'], $task_uuid));
        $info = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $info = array(
                    'task_name' => $d['task_name'],
                    'task_uuid' => $d['task_uuid'],
                    'task_type' => $d['task_type'],
                    'module_type' => $d['module_type'],
                    'sub_module_type' => $d['sub_module_type'],
                    'strategy_id' => $d['strategy_id'],
                    'task_status' => $d['task_status'],
                    'time_strategy' => $d['strategy_id'] ? $this->getJobTimeStrategy($d['strategy_id']) : '',
                    'progress' => $this->getTaskProgress($d['task_uuid']),
                );
            };
        };
        return $info;
    }

    /**
     * @description: 获取编排计划历史信息
     * @param {*} $params
     * @return {*}
     */
    public function getHistoryList($params)
    {
        $errorCode = xphp_get_config('error', 'errorCode');
        $errorCodeDes = xphp_get_config('error', 'errorCodeDes');
        $sql = "SELECT history_uuid, plan_nickname, task_number, start_time, end_time, error_code FROM bd_history_task_orchestration_plan WHERE plan_uuid = ?  ORDER BY {$params['sort']} {$params['order']}  LIMIT ?,?";
        $sqlCount = 'SELECT COUNT(history_uuid) AS total FROM bd_history_task_orchestration_plan WHERE plan_uuid = ?';
        $data = $this->dbSelect($sql, array($params['plan_uuid'], $params['offset'], $params['limit']));
        $dataCount = $this->dbSelect($sqlCount, array($params['plan_uuid']));
        $records = array();
        $records['rows'] = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $records['rows'][] = array(
                    'history_uuid' => $d['history_uuid'],
                    'plan_nickname' => $d['plan_nickname'],
                    'task_number' => $d['task_number'],
                    'start_time' => $d['start_time'],
                    'end_time' => $d['end_time'],
                    'error_code' => $errorCodeDes[$errorCode[$d['error_code']]],
                );
            }
        };
        $records['total'] = $dataCount[0]['total'];
        return $records;
    }
}
