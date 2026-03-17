<?php

namespace app\v1\job\v0\logic;

use app\v1\common\logic\JobController as Jobs;
use app\v1\job\v0\service\Service;
use app\v1\opcode\OrchestrationOpcode;
use app\v1\resources\v0\logic\Node;
use xphp\helper\Str;

/**
 * note          任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobController extends Jobs
{
    /**
     * 封装内部调用
     * @params
     * @param array  $taskuuid 任务uuid
     * @param string $method   方法
     * @param array  $params   请求参数
     * @return array
     */
    public function getClass(array $taskuuid, $method = '', $params = []): array
    {

        // 根据传递的 start_uuid 在任务里面获取类型 进行分发
        $sqlParam = [];
        if (count($taskuuid) == 1) {
            $sql = 'select module_type,sub_module_type,task_type,task_uuid,task_name,task_status,user_uuid
                    from bd_task where task_uuid = ?';
            $sqlParam = [$taskuuid[0]];
        } else {
            $uuid = "('" . implode("','", $taskuuid) . "')";
            $sql = "select module_type,sub_module_type,task_type,task_uuid,task_name,task_status,user_uuid
                    from bd_task where task_uuid in {$uuid}";
        }

        $task = $this->dbSelect(
            $sql,
            $sqlParam
        );

        if (empty($task)) {
            return [];
        }

        // 非全局观察者也不是admin时，才判断
        $userArr = array_unique(array_column($task, 'user_uuid'));
        $this->checkAuthByUserUuid(implode(',', $userArr), xphp_get_config('user', 'USER_AUTH')['current_job']);

        $user = xphp_get_user_info();

        // 模块映射 这里有重复的 暂时是副本和归档都是9 所以如果是9的话，那么还需要去判断
        $moduleTypeName = xphp_get_config('module', 'MODULE_NAME'); // 模块名数组
        $fileSubmoduleType = xphp_get_config('module', 'SUBMODULE_TYPE'); // 文件子模块数组
        $osSubmoduleType = xphp_get_config('module', 'OS_SUBMODULE_TYPE'); // 操作系统子模块数组
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $moduleTYpe = xphp_get_config('module', 'MODULE_TYPE');
        $array = [$taskStatus['ARCHIVE'], $taskStatus['ARCHIVE_FETCH']];
        $str = new Str();
        foreach ($task as $item) {
            //文件对比和文件复制共用一个任务，需要单独处理下任务类型
            if (!empty($params) && $params['start_type'] == $taskType['FILE_COMPARE']) {
                $item['task_type'] = 63;//文件对比
            }
            if ($item['module_type'] == 9 && in_array($item['task_status'], $array)) {
                // 默认是副本的 copy, 这种就是归档
                $item['module_type'] = 999999999;
            }
            $module = $moduleTypeName[$item['module_type']];
            // 副本  归档  回传
            if (
                in_array(
                    $item['task_type'],
                    [
                    $taskType['BACKUP_COPY'],
                    $taskType['BACKUP_COPY_FETCH'],
                    $taskType['ARCHIVE'],
                    $taskType['ARCHIVE_FETCH']
                    ]
                )
            ) {
                $module = 'copy';
            }

            $realModule = '';

            $recoveryArr = [
                "{$moduleTYpe['VM']}-{$taskType['INSTANT_RECOVERY']}", // 无代理瞬时恢复
                "{$moduleTYpe['VM']}-{$taskType['INSTANT_RECOVERY_MOTION']}", // 无代理迁移
                "{$moduleTYpe['VM']}-{$taskType['PLATFORM_RECOVERY']}", // 无代理跨平台恢复
                "{$moduleTYpe['OS']}-{$taskType['OS_INSTANT_RECOVERY']}", // 有代理瞬时恢复
                "{$moduleTYpe['OS']}-{$taskType['OS_INSTANT_RECOVERY_MOTION']}", // 有代理迁移
                "{$moduleTYpe['VM']}-{$taskType['OS_INSTANT_RECOVERY_MOTION']}", // 有代理迁移
                "{$moduleTYpe['OS']}-{$taskType['PLATFORM_RECOVERY']}", // 有代理跨平台恢复
                "{$moduleTYpe['VOL_CDP']}-{$taskType['PLATFORM_RECOVERY']}", // 有代理实时跨平台恢复
            ];
            if ($module === 'file') { // 文件模块获取对应子模块
                switch ($item['sub_module_type']) {
                    case $fileSubmoduleType['FS']:
                        $realModule = 'file';
                        break;
                    case $fileSubmoduleType['NAS']:
                        $realModule = 'nas';
                        break;
                    case $fileSubmoduleType['HADOOP']:
                        $realModule = 'hadoop';
                        break;
                    case $fileSubmoduleType['OBS']:
                        $realModule = 's3';
                        break;
                    default:
                        break;
                }
            } elseif (
                in_array("{$item['module_type']}-{$item['task_type']}", $recoveryArr) ||
                $item['task_type'] == $taskType['GRAIN_RECOVERY']
            ) {
                // 瞬时恢复、细粒度恢复都走 recovery
                $realModule = 'recovery';
            } elseif ($module == 'os') {
                // 操作系统整机判断
                if ($item['sub_module_type'] == $osSubmoduleType['MACHINE_OS']) {
                    $realModule = 'complete_machine_os';
                } else {
                    $realModule = 'os';
                }
            } else {
                $realModule = $module;
            }

            //数据验证
            if ($item['task_type'] == xphp_get_config('task', 'TASKTYPE')['SURE_BACKUP']) {
                $realModule = 'verification';
            }

            // 下划线转驼峰(首字母大写)
            $modulectrl = $str->studly($realModule);

            if (
                $realModule != 'recovery' && $module == 'os' &&
                $item['sub_module_type'] == $osSubmoduleType['MACHINE_OS']
            ) {
                // 操作系统整机判断
                $modulectrl = 'MachineOs';
            }

            $class = '\app\\' . getXphpVersion() . '\\' . $realModule . '\\' .
                getXphpVersion(1) . '\controller\\' . $modulectrl . 'JobController';
            // 这里先判断下是否存在方法
            $cls = new $class();
            if (method_exists($cls, $method)) {
                // 转发到模块内部去处理相应的逻辑
                $return[] = $cls->$method($item['task_uuid']);
            };

            $logParams = [
                'task_uuid' => $item['task_uuid'],
                'task_name' => $item['task_name'],
                'task_type' => $item['task_type'],
                'module_type' => $item['module_type'],
                'submodule_type' => $item['sub_module_type'],
            ];
            //GMP要求写启动任务日志
            if ($method == 'startJob' && xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']) {
                $this->taskLog(
                    $logParams,
                    'WEB_TASKLOG_DESC_KEY_START_TASK_SUCCESS',
                    [$user['userName'], $item['task_name']]
                );
            }
        }

        return $return ?? [];
    }

    /**
     * 启动任务
     * 批量的为 job_uuids 字符串逗号隔开
     * 单个的就是 start_uuid
     * @param array $params 数据
     * @return array
     */
    public function startJob($params = []): array
    {
        $taskuuid = !empty($params['job_uuids']) ? $params['job_uuids'] : [$params['start_uuid']];
        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($taskuuid, 'startJob', $params);
    }

    /**
     * 停止任务
     * 批量的为 job_uuids 字符串逗号隔开
     * 单个的就是 start_uuid
     * @param array $params 数据
     * @return array
     */
    public function stopJob($params = []): array
    {

        $taskuuid = !empty($params['job_uuids']) ? $params['job_uuids'] : [$params['stop_uuid']];
        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($taskuuid, 'stopJob');
    }

    /**
     * 删除任务
     * 批量的为 job_uuids 字符串数组
     * 单个的就是 jobs_uuid
     * @param array $params 数据
     * @return array
     */
    public function delJob($params = []): array
    {

        $taskuuid = !empty($params['job_uuids']) ? $params['job_uuids'] : [$params['jobs_uuid']];
        if (count($taskuuid) == 1) {
            $taskArr = $this->dbSelect(
                'select task_orchestration_plan_flag from bd_task where task_uuid = ?',
                [$taskuuid[0]]
            );
        } else {
            $uuid = "('" . implode("','", $taskuuid) . "')";
            $taskArr = $this->dbSelect(
                "select task_orchestration_plan_flag from bd_task where task_uuid in {$uuid}",
                []
            );
        }
        foreach ($taskArr as $t) {
            if (intval($t['task_orchestration_plan_flag']) == xphp_get_config('app', 'FLAG')['SET']) {
                // 任务编排计划中的任务不能删除
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_PLATFORM_JOB_MONITOR'),
                    xphp_get_lang('UI_JOB_TASK_ORCHESTRATION_OCCUPATION_TIP')
                );
            }
        }
        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($taskuuid, 'delJob');
    }

    /**
     * 启动接管任务 demo
     * 批量的为 job_uuids 数组
     * @param array $params 数据
     * @return array
     */
    public function startTakeover($params = []): array
    {

        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($params['job_uuids'], 'startTakeover');
    }

    /**
     * 停止接管任务 demo
     * 批量的为 job_uuids 数组
     * @param array $params 数据
     * @return array
     */
    public function stopTakeover($params = []): array
    {

        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($params['job_uuids'], 'stopTakeover');
    }

    /**
     * 切换自动接管配置
     * @param array $params 请求参数
     * @return array
     */
    public function switchTakeoverConfig($params = []): array
    {
        return $this->getClass($params['job_uuid'], 'autoTakeoverOperation');
    }

    /**
     * 启动回切任务 demo
     * 批量的为 job_uuids 数组
     * @param array $params 数据
     * @return array
     */
    public function startFailback($params = []): array
    {

        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($params['job_uuids'], 'startFailback');
    }

    /**
     * 暂停任务 demo
     * 批量的为 job_uuids 数组
     * @param array $params 数据
     * @return array
     */
    public function pauseTask($params = []): array
    {

        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($params['job_uuids'], 'pauseTask');
    }

    /**
     * 继续任务
     * 批量的为 job_uuid 字符串逗号隔开
     * @param array $params 数据
     * @return array
     */
    public function continueTask($params = []): array
    {

        $taskuuid = explode(',', $params['job_uuid']);
        // 进行分发到具体的模块去处理启动任务
        return $this->getClass($taskuuid, 'continueTask');
    }

    /**
     * 删除历史任务
     * 批量的为 job_uuid 字符串逗号隔开
     * 单个的就是 history_uuid
     * @param array $params 数据
     * @return array|string
     */
    public function delHistory($params = [])
    {

        // 查询出这些任务的主键ID（id）、模块类型（module_type）和节点uuid(node_uuid,如果module_type等于3（文件）那么就需要查询出)
        if (!empty($params['job_uuids'])) {
            $uuid = '(' . implode(',', $params['job_uuids']) . ')';
            // 批量
            $data = $this->dbSelect(
                "select id,module_type,details,user_uuid,unix_timestamp(finish_time) finish_time from
                                                                         bd_history_task where id in " . $uuid,
                []
            );
        } else {
            $data = $this->dbSelect(
                "select id,module_type,details,user_uuid,unix_timestamp(finish_time) finish_time
                        from bd_history_task where id = ?",
                [$params['history_uuid']]
            );
        }

        if (empty($data)) {
            return false;
        }

        // 非全局观察者也不是admin时，才判断
        $userArr = array_unique(array_column($data, 'user_uuid'));
        $this->checkAuthByUserUuid(implode(',', $userArr), xphp_get_config('user', 'USER_AUTH')['history_job']);

        // 判断下是否符合保留策略
        $leastDays = xphp_get_system_data_safe();
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'finish_time');
        $typeArr = xphp_get_config('system', 'RESERVED_TYPE');
        if ($reserveType == $typeArr['SYSTEM_RESERVED_STRATEGY_TYPE_PERMANENT']) {
            // 永久保留，那么不允许删除
            $msg = xphp_get_lang('WEB_JOB_DELETE_CAN_NOT_DELETE_TIPS');
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_JOB_DELETE_LESS_THAN_HALFYEAR_ERROR'),
                $msg
            );
        } elseif ($reserveType == $typeArr['SYSTEM_RESERVED_STRATEGY_TYPE_DAY']) {
            // 按时间保留
            $timestamp = strtotime($reserveNum . ' days ago midnight') + 24 * 3600;
            if (max($daysArr) > $timestamp) {
                // 那么不允许删除.如果最小的时间是 多少天前的开始时间
                $msg = xphp_get_lang('WEB_JOB_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS');
                $msg = str_replace('%S%', $reserveNum, $msg);
                return $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_JOB_DELETE_LESS_THAN_HALFYEAR_ERROR'),
                    $msg
                );
            }
        } elseif ($reserveType == $typeArr['SYSTEM_RESERVED_STRATEGY_TYPE_NUM']) {
            // 按数量保留
            $arrs = implode(',', $params['job_uuids']);
            // 默认就按照ID来排序，取出当前删除的最大的ID，然后判断比它大的个数，如果小于保留值，则不能删除
            $countSql = "select  max(finish_time) as `time` from bd_history_task
                        where id in (" . $arrs . ')';
            $remainCount = $this->dbSelect($countSql); // 本次删除的最新的时间
            $lastTime = strtotime($remainCount[0]['time']);
            $countSql = "select count(*) as total from bd_history_task
                        where UNIX_TIMESTAMP(finish_time) >= '{$lastTime}' and id not in (" . $arrs . ')';
            $remainCount = $this->dbSelect($countSql); // 本次删除的最新的时间
            $remainCount = $remainCount[0]['total'];
            if ($remainCount < $reserveNum) {
                // 那么不允许删除
                $msg = xphp_get_lang('WEB_JOB_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS');
                $msg = str_replace('%S%', $reserveNum, $msg);
                return $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_JOB_DELETE_LESS_THAN_HALFYEAR_ERROR'),
                    $msg
                );
            }
        }

        $module = xphp_get_config('module', 'MODULE_TYPE');
        $taskIDArr = [];
        $nodelist = [];//保存不同的节点信息
        $nodeinfo = [];//保存节点和已经分过组的id
        $fsTaskIDArr = [];
        $dbcdpTaskIDArr = [];//数据库cdp的id数组集合

        foreach ($data as $item) {
            if ($item['module_type'] == $module['FS']) {
                // 文件
                // 处理下nodeuuid的值
                $details = json_decode($item['details'], true);
                $nodeuuid = $details['agent_info_list'][0]['node_uuid'];
                $fsTaskIDArr[] = array(
                    'id' => $item['id'],
                    'nodeuuid' => $nodeuuid
                );
                if (!in_array($nodeuuid, $nodelist)) {//文件需要筛选出不同的nodeuuid
                    $nodelist[] = $nodeuuid;
                }
            } elseif ($item['module_type'] == $module['DB_CDP']) {
                // DBCDP
                $dbcdpTaskIDArr[] = $item['id'];
            } else {
                $taskIDArr[] = $item['id'];
            }
        }

        //文件模块得到不同nodeuuid下的不同id
        if (!empty($fsTaskIDArr) && count($nodelist) > 0) {
            foreach ($nodelist as $n) {
                $temparr = array();
                foreach ($fsTaskIDArr as $f) {
                    if ($n == $f['nodeuuid']) {
                        $temparr[] = $f['id'];
                        $tempnodeuuid = $f['nodeuuid'];
                    }
                }
                if (!empty($tempnodeuuid)) {
                    $nodeinfo[] = array(
                        'msg' => $temparr,
                        'nodeuuid' => $tempnodeuuid
                    );
                }
            }
        }

        //把按nodeuuid区分过的id发到不同的后台模块
        $opName = 'BD_TASK_OP_HISTORY_TASK_DELETE';
        $service = new Service();
        if (!empty($nodeinfo)) {
            foreach ($nodeinfo as $node) {
                $fsmsg = array('id_list' => $node['msg']);
                $fsResult = $service->mbFSMsgs($node['nodeuuid'], $opName, $fsmsg);
            }
        }
        if (!empty($taskIDArr)) {
            $msg = array('id_list' => $taskIDArr);
            $mbResult = $service->mbPFMsgs($opName, $msg);
        }
        if (!empty($dbcdpTaskIDArr)) {
            $msg = array('id_list' => $dbcdpTaskIDArr);
            $mbResult = $service->mbDbCdpMsgs((new Node())->getLocalNodeUuid(), $opName, $msg);
        }

        $operate = $this->getUnifyOpcodeDes($opName);


        //返回结果到UI
        if (!empty($nodeinfo) && !empty($taskIDArr)) {
            // 删除文件和其它
            $result = ($mbResult['result'] ?? false) && ($fsResult['result'] ?? false);
            if ($result) {
                // 都成功
                return $this->muOpResult(true, $operate, $mbResult['msg']);
            } elseif (
                !empty($mbResult['result']) && $mbResult['result'] &&
                !empty($fsResult['result']) && !$fsResult['result']
            ) {
                //  删除其他错误，删文件正确
                return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
            } elseif (
                !empty($mbResult['result']) && !$mbResult['result'] &&
                !empty($fsResult['result']) && $fsResult['result']
            ) {
                //文件报错  其他正确
                return $this->muOpResult($fsResult['result'], $operate, $fsResult['msg'], '', $fsResult['errorCode']);
            } else {//都报错
                return $this->muOpResult(false, $operate, $mbResult['msg'] ?? '', '', $mbResult['errorCode']);
            }
        } elseif ((empty($nodeinfo) && !empty($taskIDArr))) {
            // 删除其它 没得文件
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        } else {
            // 只有文件
            return $this->muOpResult($fsResult['result'], $operate, $fsResult['msg'], '', $fsResult['errorCode']);
        }
    }

    /**
     * 下载历史任务日志检查
     * @param string $taskUuid 任务uuid
     * @return array|string
     */
    public function downloadHistoryCheck(string $taskUuid)
    {
        //卷cdp没有日志,告警表中未关联历史任务uuid
        $productSql = "select module_type from bd_history_task where id = ?";
        $productData = $this->dbSelect($productSql, array($taskUuid));

        $moduleTYpe = xphp_get_config('module', 'MODULE_TYPE');

        if ($productData[0]['module_type'] == $moduleTYpe['VOL_CDP']) {
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_LOG_NOT_DOWNLOAD_TASK_LOG_ERROR'),
                xphp_get_lang('WEB_LOG_VOL_CDP_TASK_LOG_NOT_EXIST_ERROR_TIPS'),
                'warning'
            );
        }

        $sql = "select bta.task_alarm_id, bta.alarm_level, bta.module_type,bht.user_uuid
                from bd_history_task bht, bd_task_alarm bta
                where bht.history_uuid = bta.history_uuid and bht.history_uuid != '' and bht.id = ? ";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (empty($data)) {
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_LOG_NOT_EXIST_ERROR'),
                xphp_get_lang('WEB_LOG_NOT_EXIST_ERROR'),
                'warning'
            );
        }

        // 非全局观察者也不是admin时，才判断
        $userArr = array_unique(array_column($data, 'user_uuid'));
        $this->checkAuthByUserUuid(implode(',', $userArr), xphp_get_config('user', 'USER_AUTH')['history_job']);

        if ($data[0]['alarm_level'] != 2 && $data[0]['alarm_level'] != 3) {
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_LOG_NOT_DOWNLOAD_TASK_LOG_ERROR'),
                xphp_get_lang('WEB_LOG_NOT_DOWNLOAD_TASK_LOG_ERROR_TIPS'),
                'warning'
            );
        }

        return [
            'info' => $data[0]['task_alarm_id']
        ];
    }

    /**
     * 下载任务告警日志
     * @param int $id 日志告警id
     * @return string|bool
     */
    public function downLoadTaskLog(int $id)
    {

        $sql = "select node_uuid, task_log_path, task_name, alarm_time, user_uuid
                    from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        if (empty($data)) {
            return false;
        }

        $path = $data[0]['task_log_path'];
        $nodeuuid = $data[0]['node_uuid'];
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $fileContent = '';
        if ($msg['result']) {
            $fileContent = str_replace("\n", "\r\n", $msg['msg']['file_content']);
        }
        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        Header('Accept-Length: ' . strlen($fileContent));
        // phpcs:ignore
        Header('Content-Disposition: attachment; filename=' . $data[0]['task_name'] . '_' . $data[0]['alarm_time'] . '.txt');
        return $fileContent;
    }

    /**
     * 更新挂起任务优先级
     * @param array $params 请求参数
     * @return array
     */
    public function updatePendingSort(array $params)
    {

        $ids = $params['ids']; // 这是所有改变的任务uuid集合
        // 根据这些id查询出对应的这些的优先级
        // 要单独处理每页的数据，根据页码获取之前的第一个优先级，然后把当前的重新赋值即可
        $final = [];
        foreach ($ids as $item) {
            $startSort = xphp_get_cache('pending_job_page' . $item['page']) ?? 0;
            foreach ($item['ids'] as $item2) {
                $final[] = [
                    'task_uuid' => $item2,
                    'priority' => $startSort,
                ];
                $startSort++;
            }
        }
        $msg = [
            'task_pending_priority_list' => $final
        ];

        $opName = 'SS_OP_UPDATE_TASK_PENDING_STRATEGY';
        $mbResult = $this->service()->mbStrategyMsgs(
            (new Node())->getMasterNodeUuid(),
            $opName,
            $msg
        );
        $operate = (new OrchestrationOpcode())->getOpcodeDes($opName);
        return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
    }
}
