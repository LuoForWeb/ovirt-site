<?php

namespace app\v1\job\v0\logic;

use app\v1\common\logic\JobInfo as JobInfos;
use app\v1\resources\v0\logic\Node;
use app\v1\vm\v0\logic\VmJobInfo;
use xphp\helper\Str;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\tape\v0\logic\TapeInfo;

/**
 * note          任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobInfo extends JobInfos
{
    /**
     * 封装内部调用
     * @param string $taskuuid 任务uuid
     * @return false|mixed
     */
    protected function getClass(string $taskuuid)
    {

        // 根据传递的 start_uuid 在任务里面获取类型 进行分发
        $task = $this->dbSelect('select module_type,task_status from bd_task where task_uuid = ? limit 1', [$taskuuid]);
        if (empty($task)) {
            return false;
        }

        // 模块映射
        $moduletype = xphp_get_config('module', 'MODULE_NAME');
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $array = [$taskStatus['ARCHIVE'], $taskStatus['ARCHIVE_FETCH']];
        $str = new Str();
        $moduleType = $task[0]['module_type'];
        if ($moduleType == 9 && in_array($task[0]['task_status'], $array)) {
            // 默认是副本的 copy, 这种就是归档
            $moduleType = 999999999;
        }
        $module = $moduletype[$moduleType];

        // 下划线转驼峰(首字母大写)
        $modulectrl = $str->studly($module);

        $class = '\app\\' . getXphpVersion() . '\\' . $module . '\\' .
            getXphpVersion(1) . '\controller\\' . $modulectrl . 'JobInfo';

        return (new $class());
    }

    /**
     * 获取所有模块列表
     * @param array $params 数据
     * @return array
     */
    public function getModule($params = [])
    {
        // 读取配置文件
        $moduleweb = xphp_get_config('module', 'MODULE_WEB');
        // 获取用户授权 剔除无权限的模块
        $permission = xphp_get_user_info()['permission'];

        // 模块集合
        $authArr = array_column($moduleweb, 'auth');
        $newAuth = array_intersect($permission, $authArr);

        $lists = [];
        $num = 0;
        foreach ($moduleweb as $item => $value) {
            // 查找有权限的模块
            if (in_array($value['auth'], $newAuth)) {
                $num++;
                if ($num > $params['offset'] && $num <= $params['limit'] + 1) {
                    // 组装数据
                    $lists[] = [
                        'value' => $item,
                        'text' => $value['name'],
                        'child' => $params['chiild'] ? $this->getChild($value['child'], $value['auth']) : []
                    ];
                }
            }
        }
        return [
            'rows' => $lists,
            'total' => count($newAuth)
        ];
    }

    /**
     * 根据任务名或语言包获取新的任务名
     * @param string $name 任务名称/语言包健名
     * @return array
     */
    public function getValidName(string $name): array
    {
        // 获取真正的任务名
        if (strpos($name, 'WEB_') !== false || strpos($name, 'UI_') !== false) {
            /// 表示传递的是键名
            $name = xphp_get_lang($name);
        }
        // 这里判断下任务表里面是否存在同名的任务，存在就在编号前加上1
        $sqlParams = [$name . '%'];
        $sql = "select task_name from bd_task where task_name like ?";
        $data = $this->dbSelect($sql, $sqlParams);
        $sql = "select task_name from bd_backup_timepoint where task_name like ?";
        $data1 = $this->dbSelect($sql, $sqlParams);

        if (empty($data) && empty($data1)) {
            return ['value' => $name . '1'];
        }

        // 取出任务名后面的编号并降序
        $array = str_replace($name, '', array_column($data, 'task_name'));

        // 取出任务名后面的编号并降序
        $array1 = str_replace($name, '', array_column($data1, 'task_name'));

        $key = array_map('intval', $array);
        $key = !empty($key) ? max($key) : 0;

        $key1 = array_map('intval', $array1);
        $key1 = !empty($key1) ? max($key1) : 0;

        $new = $name . (max($key, $key1) + 1);

        return ['value' => $new];
    }

    /**
     * 获取child类型 根据模块和授权获取 各个模块的任务类型和个别模块的独特类型列表
     * @param array  $child child
     *                      数组
     * @param string $auth  授权名称
     * @return array
     */
    private function getChild($child = [], $auth = ''): array
    {
        $lang = xphp_get_lang();
        $childArr = [
            'task_type' => [    // 任务类型
                'name' => $lang['UI_PUBLIC_TASK_TYPE'], // 语言包
                'vmprotect' => [     // 虚拟机保护
                    ['value' => 1, 'text' => $lang['WEB_PLATFORM_DES_BACKUP']],
                    ['value' => 2, 'text' => $lang['WEB_PLATFORM_DES_RECOVERY']],
                    ['value' => 7, 'text' => $lang['WEB_PLATFORM_DES_INSTANT_RECOVERY']],
                    ['value' => 8, 'text' => $lang['WEB_PLATFORM_DES_MOTION']],
                    ['value' => 6, 'text' => $lang['WEB_PLATFORM_DES_FILE_REC']],
                    ['value' => 37, 'text' => $lang['WEB_PLATFORM_DES_SURE_BACKUP']],
                ],
                'fileprotect' => [     // 文件
                    ['value' => 1, 'text' => $lang['WEB_PLATFORM_DES_BACKUP']],
                    ['value' => 2, 'text' => $lang['WEB_PLATFORM_DES_RECOVERY']],
                ],
                'db_protect' => [     // 数据库
                    ['value' => 28, 'text' => $lang['WEB_PLATFORM_DES_BACKUP']],
                    ['value' => 29, 'text' => $lang['WEB_PLATFORM_DES_RECOVERY']],
                ],
                'os_protect' => [     // 操作系统
                    ['value' => 35, 'text' => $lang['WEB_PLATFORM_DES_BACKUP']],
                    ['value' => 36, 'text' => $lang['WEB_PLATFORM_DES_RECOVERY']],
                ],
                'vol_cdp_protect' => [     // 实时容灾保护
                    ['value' => 32, 'text' => $lang['WEB_PLATFORM_DES_BACKUP']],
                    ['value' => 33, 'text' => $lang['WEB_PLATFORM_DES_RECOVERY']],
                    ['value' => 34, 'text' => $lang['UI_PLATFORM_VOL_CDP_TAKEOVER']],
                ],
                'nas_protect' => [     // nas
                    ['value' => 1, 'text' => $lang['WEB_PLATFORM_DES_BACKUP']],
                    ['value' => 2, 'text' => $lang['WEB_PLATFORM_DES_RECOVERY']],
                ]
            ],
            'vm_type' => [   // 虚拟化类型
                'name' => $lang['UI_VCENTER_TYPE'], // 语言包
                'vmprotect' => (new VmJobInfo())->getAllHypervisorType(['all_type_flag' => true]),
            ],
            'db_type' => [    // 数据库类型
                'name' => $lang['UI_DB_DATABASE_TYPE'],
                'db_protect' => [
                    ['value' => 1, 'text' => 'SQL Server'],
                    ['value' => 2, 'text' => 'Oracle'],
                    ['value' => 3, 'text' => 'MySQL'],
                    ['value' => 4, 'text' => 'DM'],
                    ['value' => 5, 'text' => 'PostgreSQL'],
                    ['value' => 6, 'text' => 'KingbaseES'],
                    ['value' => 7, 'text' => 'UXDB'],
                    ['value' => 8, 'text' => 'Highgo DB'],
                    ['value' => 9, 'text' => 'MariaDB'],
                    ['value' => 10, 'text' => 'openGauss'],
                    ['value' => 11, 'text' => 'Vastbase'],
                ]
            ]
        ];
        $list = [];
        $nullspace = xphp_get_config('app', 'NULLSPACE');
        $publicAll = ['value' => 0, 'text' => $lang['UI_PUBLIC_ALL']];
        foreach ($child as $item) {
            $list[$item] = [
                'name' => $nullspace,
                'value' => []
            ];
            // 这里取出child对应的数组列表
            if (!empty($childArr[$item])) {
                // 存在的情况
                $list[$item]['name'] = $childArr[$item]['name'];
                $value = $childArr[$item][$auth] ?? [];
                array_unshift($value, $publicAll);
                $list[$item]['value'] = $value;
            }
        }
        return $list;
    }

    /**
     * 获取当前任务详情
     *  jobs_uuid
     * @param string $jobUuid 数据
     * @return json
     */
    public function getJob(string $jobUuid)
    {
        // 展示详情
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type,
                        unix_timestamp(bt.create_time) create_time,
                        bt.task_status, bt.strategy_id, bt.sub_module_type, bu.user_name,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time,
                        bri.total_object_valid_size,bri.total_object_completed_valid_size
                from bd_running_info bri, bd_user bu, bd_task bt 
                     left join sr_surebackup ssb on bt.task_uuid = ssb.sr_task_uuid 
                where bt.task_uuid = bri.task_uuid and 
                	 bt.user_uuid = bu.user_uuid and 
                     bt.delete_flag = ? and bt.task_uuid = ?";

        $data = $this->dbSelect($sql, [xphp_get_config('app', 'FLAG')['UNSET'], $jobUuid]);

        if (empty($data)) {
            return false;
        }

        return $this->getJobOtherInfo($data[0]);
    }

    /**
     * 获取当前任务的某个任务的基本信息
     * @param string $jobUuid 任务uuid
     * @return array|bool
     */
    public function getJobInfo(string $jobUuid)
    {
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type,bt.sub_module_type, bt.task_orchestration_plan_flag,bt.inc_mode,bt.ignore_resource_limiting_flag,
                        unix_timestamp(bt.create_time) create_time,bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
                        bt.task_status, bt.strategy_id,bt.node_uuid,bt.storage_uuid,bt.current_stage,bt.stage_percent, bt.user_uuid,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time,
                        unix_timestamp(bri.start_time) start_time,bt.thread_num,
                        unix_timestamp(bs.next_start_time) next_start_time, bs.time_strategy_backup_type,
                        bri.total_object_valid_size,bri.total_object_completed_valid_size,
                        vol_task.current_task_running_stage,cddt.current_task_running_stage,
                        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,
                        btsc.backup_integrity_check_inc_error_policy, btsc.recovery_integrity_check_error_policy,
                        brs.network_retry_times,brs.network_retry_interval,brs.op_retry_times,brs.op_retry_interval,brs.task_retry_object,brs.task_retry_times, brs.task_retry_interval,
                        bsr.worm_flag AS worm_storage_flag 
                from bd_running_info bri, bd_user bu, bd_strategy bs, bd_task bt
                     left join sr_surebackup ssb on bt.task_uuid = ssb.sr_task_uuid 
                     left join cdp_vol_task vol_task on bt.task_uuid = vol_task.task_uuid 
                     left join cdp_db_dr_task cddt on bt.task_uuid = cddt.task_uuid
                     left join bd_task_safe_config btsc on bt.task_uuid = btsc.task_uuid 
                     left join bd_retry_strategy brs on bt.task_uuid = brs.task_uuid  
                     left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid       
                where bt.task_uuid = bri.task_uuid and 
                	 bt.user_uuid = bu.user_uuid and 
                      bt.strategy_id = bs.strategy_id and
                     bt.delete_flag = ? and bt.task_uuid = ?";

        $data = $this->dbSelect($sql, [xphp_get_config('app', 'FLAG')['UNSET'], $jobUuid]);
        if (empty($data)) {
            return false;
        }
        $return = $data[0];

        $taskStatusDes = xphp_get_config('task', 'TASKSTATUSDES');
        // 处理下阶段的显示
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        if ($return['module_type'] == $moduletype['VOL_CDP']) {
            // 卷实时
            $stageArr = xphp_get_config('task', 'REAL_PROTECT_STAGE');
        } elseif ($return['module_type'] == $moduletype['DB_CDP']) {
            // 数据库实时
            $stageArr = xphp_get_config('task', 'DB_CDP_PROTECT_STAGE');
        } else {
            $stageArr = xphp_get_config('task', 'COMMON_STAGE');
        }
        $currentstage = !empty($stageArr[$return['current_stage']]) ?
            xphp_get_lang($stageArr[$return['current_stage']]) : xphp_get_config('app', 'NULLSPACE');
        if ($return['stage_percent'] != -1) {
            $currentstage .= '(' . $return['stage_percent'] . '%)';
        }
        //引用磁带模块
        $tapeHandler = new TapeInfo;
        return [
            'flag' => true,
            'user_uuid' => $return['user_uuid'],//任务的用户
            'job_name' => $return['task_name'],
            'job_type' => $return['task_type'],
            'submodle_type' => $this->pGetTaskSubmodule($jobUuid, $return['module_type'], $return['task_type']),
            'job_status' => $return['task_status'],
            'job_status_des' => xphp_get_lang($taskStatusDes[$return['task_status']]),
            'total_size' => v1_calsize($return['total_object_size'], true),
            'complate_size' => v1_calsize($return['total_object_completed_size'], true),
            'create_time' => $this->parseDate($return['create_time']),
            'start_time' => $this->getStartTIme($return['start_time'], $return['task_status']),
            'interval_time' => $this->getTimeInterval($return['start_time'], $return['task_status']),
            'job_stage' => $return['current_task_running_stage'] ?? 0,
            'speed' => $return['task_status'] != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ?
                xphp_get_config('app', 'NULLSPACE') :
                v1_calspeed($return['speed']),
            'speed_time' => $return['speed_time'],
            'thread_num' => $return['thread_num'],
            'timestamp' => TIMESTAMP,
            'progress' => $this->getTaskTotalProgress(
                $return['task_status'],
                $return['total_object_size'],
                $return['total_object_completed_size'],
                false,
                $return['task_type']
            ),
            'percent_progress' => $this->getTaskTotalProgress(
                $return['task_status'],
                $return['total_object_size'],
                $return['total_object_completed_size'],
                true,
                $return['task_type']
            ),
            'next_time' => $this->getNextStartTime($return['next_start_time'], $return['task_status']),
            'storage_info' => $this->getStorageInfo(
                $return['task_type'],
                $return['strategy_id'],
                $return['storage_uuid'],
                $return['node_uuid'],
                $jobUuid
            ), // 存储信息 1
            'speed_limit' => $this->getSpeedlimitDes($return['task_uuid']), // 限速策略 1
            'time_strategy' => $this->getJobTimeStrategy($return['strategy_id']), // 时间策略 1
            'reserve_strategy' => $this->getReservedStrategy($return['strategy_id'], $return['task_uuid']), // 保留策略 1
            'transport_strategy' => $this->getTransportInfo($return['task_uuid'], $return['task_type']), // 传输策略 1
            'timeStrategyBackupType' => $return['time_strategy_backup_type'], // 时间策略备份类型
            'task_orchestration_plan_flag' => v1_parse_flag_to_bool($return['task_orchestration_plan_flag']),
            'module_type' => $return['module_type'],
            'sub_module_type' => $return['sub_module_type'],
            'module_type_des' => $this->getModuleName($return),
            'job_type_des' => $this->getTaskNameString(
                xphp_get_config('module', 'MODULE_TYPE'),
                xphp_get_config('task', 'TASKTYPE'),
                $return['module_type'],
                $return['task_type']
            ),
            'storage_uuid' => $return['storage_uuid'],
            'inc_mode' => $return['inc_mode'],
            'merge_mode' => '',
            'merge_mode_des' => '',
            'safe_strategy' => $this->groupSafeStrategy($return),
            //重试策略
            'retry_strategy' => $this->groupRetryStrategy($return),
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($return['ignore_resource_limiting_flag']),
            'ip' => $this->getNodeNetworkIp($return['task_uuid']),
            'current_stage' => $return['current_stage'], // 任务阶段
            'stage_percent' => $return['stage_percent'], // 任务百分比
            'current_stage_value' => $currentstage, // 任务阶段+任务百分比
            'tape_strategy' => $tapeHandler->getTapeGroupStrategy(['group_uuid' => $return['storage_uuid']]) ?? '',
            'user_uuid' => $return['user_uuid'],
        ];
    }

    /**
     * 获取合并模式描述
     * @param int $mergeMode
     * @return string
     */
    protected function getMergeModeInfo(int $mergeMode): string
    {
        if (1 == $mergeMode) {
            return xphp_get_lang('UI_BACKUP_HIGH_PERFORMANCE');
        } elseif (2 == $mergeMode) {
            return xphp_get_lang('UI_BACKUP_LOW_REDUNDANCY');
        } else {
            return '--';
        }
    }

    /**
     * 任务详情-组合安全策略相关信息
     * @param object 任务所有信息
     * @return object 安全策略相关信息
     */
    public function groupSafeStrategy($data)
    {
        $info = array();
        $safeDes = require_once(APP_PATH . 'v1/description/Safe.php');
        if (!empty($data)) {
            $virusscanconfiglist = json_decode($data['virus_scan_config_list'], true);
            if (!$virusscanconfiglist) {
                $virusscanconfiglist = [];
            }
            $info = array(
                //获取worm开关
                'worm_flag' => v1_parse_flag_to_bool($data['worm_flag']),
                //获取worm保护期限
                'worm_protection_time' => intval($data['worm_protection_time']),
                //获取病毒是否开关
                'virus_scan_flag' => v1_parse_flag_to_bool($data['virus_scan_flag']),
                //获取病毒检测配置
                'virus_scan_config_list' => array_map(function ($virusScanConfigInfo) use ($safeDes) {
                    return [
                        //策略类型
                        'strategy_type' => intval($virusScanConfigInfo['strategy_type']),
                        'strategy_type_des' => $safeDes['STRAREGY_TYPE'][intval($virusScanConfigInfo['strategy_type'])],
                        //恢复策略
                        'recover_policy' => intval($virusScanConfigInfo['recover_policy']),
                        'recover_policy_des' => $safeDes['RECOVER_POLICY'][intval($virusScanConfigInfo['recover_policy'])],
                        //扫描中断策略
                        'interrupt_policy' => intval($virusScanConfigInfo['interrupt_policy']),
                        'interrupt_policy_des' => $safeDes['INTERRUPT_POLICY'][intval($virusScanConfigInfo['interrupt_policy'])],
                        //扫描策略
                        'scan_strategy' => intval($virusScanConfigInfo['scan_strategy']),
                        'scan_strategy_des' => $safeDes['SCAN_STRATEGY'][intval($virusScanConfigInfo['scan_strategy'])],
                        //是否扫描任务关联的所有未扫描备份点
                        'all_timepoints_flag' => $virusScanConfigInfo['all_timepoints_flag'],
                        //是否跳过应用组
                        'skip_application_group_flag' => $virusScanConfigInfo['skip_application_group_flag'],
                        // 扫描线程
                        'virus_thread_num' => intval($virusScanConfigInfo['virus_thread_num']),
                        // 扫描对象【1全盘扫描 2指定路径扫描】
                        'scan_entire_system_flag' => $virusScanConfigInfo['scan_entire_system_flag'],
                        'scan_entire_system_flag_des' => $safeDes['SCAN_ENTIRE_SYSTEM_FLAG'][intval($virusScanConfigInfo['scan_entire_system_flag'])],
                        // 扫描对象的路径和后缀
                        'specific_scan_target_map' => $virusScanConfigInfo['specific_scan_target_map'],
                        // 每对象单次运行最大扫描备份点数量
                        'max_num_of_one_detection' => intval($virusScanConfigInfo['max_num_of_one_detection']),
                        // 病毒扫描引擎
                        'virus_lib_type' => intval($virusScanConfigInfo['virus_lib_type']),
                        'virus_lib_type_name' => $virusScanConfigInfo['virus_lib_type_name'],
                    ];
                }, $virusscanconfiglist),
                //获取完整性效验开关
                'integrity_check_flag' => v1_parse_flag_to_bool($data['integrity_check_flag']),
                //获取完整性校验数据
                'integrity_check_config' => array(
                    //获取效验周期
                    'check_strategy' => intval($data['integrity_check_strategy']),
                    'check_strategy_des' => $safeDes['CHECK_STRATEGY'][intval($data['integrity_check_strategy'])],
                    //获取完全备份点异常
                    'full_error_policy' => intval($data['backup_integrity_check_full_error_policy']),
                    'full_error_policy_des' => $safeDes['FULL_ERROR_STRATEGY'][intval($data['backup_integrity_check_full_error_policy'])],
                    //获取其他备份点异常
                    'inc_error_policy' => intval($data['backup_integrity_check_inc_error_policy']),
                    'inc_error_policy_des' => $safeDes['INC_ERROR_STRATEGY'][intval($data['backup_integrity_check_inc_error_policy'])],
                    //恢复完整性校验异常处理
                    'recovery_error_policy' => intval($data['recovery_integrity_check_error_policy']),
                    'recovery_error_policy_des' => $safeDes['RECOVERY_ERROR_STRATEGY'][intval($data['recovery_integrity_check_error_policy'])],
                ),
                //存储是否开启了worm
                'worm_storage_flag' => v1_parse_flag_to_bool($data['worm_storage_flag']),
            );
        }
        return $info;
    }

    /**
     * 任务详情-组合重试策略相关信息
     * @param object 任务所有信息
     * @return object 重试策略相关信息
     */
    public function groupRetryStrategy($data)
    {
        $info = array();
        if (!empty($data)) {
            //操作重试开关
            $opRetryFlag = true;
            if ($data['op_retry_times'] == 0) {
                $opRetryFlag = false;
            }
            //任务重试开关
            $taskRetryFlag = true;
            if ($data['task_retry_times'] == 0) {
                $taskRetryFlag = false;
            }
            $info = array(
                'network_retry_times' => $data['network_retry_times'],
                'network_retry_interval' => $data['network_retry_interval'],
                'op_retry_flag' => $opRetryFlag,
                'op_retry_times' => $data['op_retry_times'],
                'op_retry_interval' => $data['op_retry_interval'],
                'task_retry_flag' => $taskRetryFlag,
                'task_retry_object' => $data['task_retry_object'],
                'task_retry_times' => $data['task_retry_times'],
                'task_retry_interval' => $data['task_retry_interval'],
            );
        }
        return $info;
    }

    /**
     * 获取单个任务的流量
     * @param string $jobUuid 任务uuid
     * @return array|bool
     */
    public function getJobFlow(string $jobUuid)
    {
        $sql = "select bri.speed,bri.speed_time,bt.task_status
                    from bd_running_info bri,bd_task bt where bri.task_uuid = bt.task_uuid and bt.task_uuid = ?";

        $data = $this->dbSelect($sql, [$jobUuid]);
        if (empty($data)) {
            return false;
        }
        return [
            'speed' => $data[0]['task_status'] != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ?
                xphp_get_config('app', 'NULLSPACE') :
                v1_calspeed($data[0]['speed']),
            'speed_value' => $data[0]['task_status'] != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ?
                xphp_get_config('app', 'NULLSPACE') : $data[0]['speed'],
            'speed_time' => $data[0]['speed_time'],
            'timestamp' => TIMESTAMP,
            'nowTime' => date('H:i:s')
        ];
    }

    /**
     * 导出任务列表
     * @param array $params
     * @return unknown
     * @author wuyihang@vinchin.com
     * @date 2024/8/22
     */
    public function exportAllCurrentJobs($params)
    {
        $params['module_type'] = $params['module_type'] ? implode(',', $params['module_type']) : [];
        $params['sub_module_type'] = $params['sub_module_type'] ? implode(',', $params['sub_module_type']) : [];
        $params['dev_type'] = $params['dev_type'] ? implode(',', $params['dev_type']) : [];
        $params['job_type'] = $params['job_type'] ? implode(',', $params['job_type']) : [];
        $params['job_status'] = $params['job_status'] ? implode(',', $params['job_status']) : [];

        $exportData = $this->getJobList($params, true)['rows'];
        $title = xphp_get_lang('UI_JOB_CURRENT');

        $header = [
            'job_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'module_type' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'job_type' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'create_time' => xphp_get_lang('UI_JOB_CREATE_TIME'),
            'speed' => xphp_get_lang('UI_JOB_SPEED'),
            'progress' => xphp_get_lang('UI_JOB_PROGRESS'),
            'user_name' => xphp_get_lang('UI_PUBLIC_USER'),
            'status' => xphp_get_lang('UI_PUBLIC_STATUS'),
            'node' => xphp_get_lang('UI_JOB_BACKUP_NODE'),
            'storage' => xphp_get_lang('UI_TASK_REPORT_STORAGE_NAME'),
            'next_time' => xphp_get_lang('UI_PUBLIC_NEXT_RUN_TIME'),
            'running_time' => xphp_get_lang('UI_JOB_INTERVAL_TIME'),
            'vcenter_hypervisor' => xphp_get_lang('UI_BACKUP_DATA_EXPORT_HYPERVISOR'),
            'vcenter_name' => xphp_get_lang('WEB_VM_VCENTER'),
            'db_type' => xphp_get_lang('UI_DB_DATABASE_TYPE'),
            'orchestration_name' => xphp_get_lang('UI_JOB_TASK_ORCHESTRATION'),
        ];

        $exportData = array_map(function ($row) {
            $nullSpace = xphp_get_config('app', 'NULLSPACE');
            $timeSpace = xphp_get_config('app', 'TIMESPACE');
            $authModule = json_decode($row['$authorization_module'], true);
            $authModuleDes = [];
            return [
                'job_name' => $row['job_name'],
                'module_type' => $row['module_type'],
                'job_type' => $row['job_type'],
                'create_time' => $row['create_time'],
                'speed' => $row['speed'],
                'progress' => $row['progress'],

                'user_name' => $row['user_name'],
                'status' => $row['job_status'],
                'node' => $row['back_node'],
                'storage' => $row['back_storage'],
                'next_time' => $row['next_time'],
                'running_time' => $row['running_time'],
                'vcenter_hypervisor' => $row['vcenter_hypervisor'],
                'vcenter_name' => $row['vcenter_name'],
                'db_type' => $row['db_type'] == 0 ? $nullSpace : xphp_get_config('db', 'DB_TYPE_DES')[$row['db_type']],
                'orchestration_name' => $row['orchestration_name'],
            ];
        }, $exportData);

        $relation = [
            'job_name' => ['col_name' => 'A', 'width' => 25],
            'module_type' => ['col_name' => 'B', 'width' => 15],
            'job_type' => ['col_name' => 'C', 'width' => 15],
            'create_time' => ['col_name' => 'D', 'width' => 15],
            'speed' => ['col_name' => 'E', 'width' => 15],
            'progress' => ['col_name' => 'F', 'width' => 15],

            'user_name' => ['col_name' => 'G', 'width' => 20],
            'status' => ['col_name' => 'H', 'width' => 20],
            'node' => ['col_name' => 'I', 'width' => 15],
            'storage' => ['col_name' => 'J', 'width' => 15],
            'next_time' => ['col_name' => 'K', 'width' => 15],
            'running_time' => ['col_name' => 'L', 'width' => 15],
            'vcenter_hypervisor' => ['col_name' => 'M', 'width' => 15],
            'vcenter_name' => ['col_name' => 'N', 'width' => 15],
            'db_type' => ['col_name' => 'O', 'width' => 15],
            'orchestration_name' => ['col_name' => 'P', 'width' => 15],
        ];
        v1_base_export($title, $header, $exportData, $relation);
    }

    /**
     * 获取当前任务列表
     * @param array $params      数据
     * @param bool  $isExportAll 是否全部导出
     * @return array
     */
    public function getJobList($params = [], $isExportAll = false)
    {
        // 1. 参数提取
        $start = $params['offset'];
        $length = $params['limit'];
        $taskName = v1_escape_wildcard($params['job_name'] ?: $params['search']);
        $userName = v1_escape_wildcard($params['user_name']);
        $otherHostName = v1_escape_wildcard($params['other_host_name']);
        $otherVmName = v1_escape_wildcard($params['other_vm_name']);
        $taskType = $params['job_type'] ? explode(',', trim($params['job_type'])) : '';
        $taskStatus = $params['job_status'] ? explode(',', trim($params['job_status'])) : '';
        $moduleType = $params['module_type'] ? explode(',', trim($params['module_type'])) : '';
        $subModuleType = ($params['sub_module_type'] === '0' || !empty($params['sub_module_type'])) ? explode(',', trim($params['sub_module_type'])) : '';
        $dbType = intval($params['db_type']);
        $vmType = intval($params['vm_type']);
        $startTime = $params['start_time'] ?? '';
        $endTime = $params['end_time'] ?? '';
        $nodeuuid = $params['node_uuid'] ?? '';
        $storageuuid = $params['storage_uuid'] ?? '';
        $hiddenFields = $params['hiddenFields'] ? explode(',', trim($params['hiddenFields'])) : [];
        $noSureBackupFlag = $params['no_surebackup_flag'] ?? false;

        // 2. 配置项和常量
        $flag = xphp_get_config('app', 'FLAG');
        $tasktypeCfg = xphp_get_config('task', 'TASKTYPE');
        $moduletypeCfg = xphp_get_config('module', 'MODULE_TYPE');
        $dbTypeCfg = xphp_get_config('db', 'DB_TYPE');
        $dbTypeDescCfg = xphp_get_config('db', 'DB_TYPE_DES');
        $taskStatusCfg = xphp_get_config('task', 'TASKSTATUS');

        // 3. 使用数组构建SQL查询
        $fields = [];
        $from = 'bd_task bt';
        $joins = [];
        $wheres = [];
        $sqlParams = [];

        // 4. 构建查询的各个部分
        // ----- SELECT 字段 -----
        $fields = [
            'distinct bt.task_uuid',
            'bt.task_name',
            'bt.module_type',
            'bt.sub_module_type',
            'bt.task_type',
            'bt.task_orchestration_plan_flag',
            'unix_timestamp(bt.create_time) create_time',
            'unix_timestamp(bri.start_time) start_time',
            'bt.task_status',
            'bt.strategy_id',
            'bu.user_uuid',
            'bu.user_name',
            'bt.node_uuid',
            'bri.total_object_size',
            'bri.total_object_completed_size',
            'bri.speed',
            'bri.speed_time',
            'bri.total_object_valid_size',
            'bri.total_object_completed_valid_size',
            'cddtpi.transmission_speed',
            'cddtpi.total_dict_num',
            'cddtpi.completed_dict_num',
            'cddtpi.total_table_num',
            'cddtpi.completed_table_num',
            'cddt.current_task_running_stage',
            'cvt.auto_takeover_flag',
            'cvt.auto_takeover_enable_flag',
            'cvti.takeover_agent_role',
            'cvt.dev_type',
            'cvt.standby_agent_uuid',
            'bt.stage_percent',
            'ssb.task_progress',
            'bsr.storage_nickname',
            'bsr.storage_type',
            'ct.copy_mode',
            'btopst.plan_uuid',
            'dt1.db_type',
            'dt1.multi_task_flag',
            'dt1.depend_task_uuid',
            'birt.instant_target_info',
            'birt.migrate_phase',
            'birt.migrate_status'
        ];

        $jobTypeCase = "CASE bt.task_type 
            WHEN {$tasktypeCfg['CDP_DB_BACKUP']} THEN 1 WHEN {$tasktypeCfg['DB_BACKUP']} THEN 1
            WHEN {$tasktypeCfg['VOL_CDP_BACKUP']} THEN 1 WHEN {$tasktypeCfg['OS_BACKUP']} THEN 1
            WHEN {$tasktypeCfg['CDP_DB_RECOVERY']} THEN 2 WHEN {$tasktypeCfg['DB_RECOVERY']} THEN 2
            WHEN {$tasktypeCfg['VOL_CDP_RECOVERY']} THEN 2 WHEN {$tasktypeCfg['OS_RECOVERY']} THEN 2
            WHEN {$tasktypeCfg['OS_INSTANT_RECOVERY']} THEN 7 WHEN {$tasktypeCfg['OS_INSTANT_RECOVERY_MOTION']} THEN 8
            ELSE bt.task_type END";
        $fields[] = "($jobTypeCase) AS JOB_TYPE";

        $fields[] = "case bt.module_type 
            when {$moduletypeCfg['VM']} then vt.hypervisor_type
            when {$moduletypeCfg['DB_CDP']} then cddt.current_task_running_stage
            when {$moduletypeCfg['VOL_CDP']} then cvti.takeover_agent_role
            else bt.id end as module_type_alias";

        $fields[] = "case bt.module_type 
            when {$moduletypeCfg['DB_CDP']} then cddt.current_task_running_stage
            when {$moduletypeCfg['VOL_CDP']} then cvt1.current_task_running_stage
            else bt.current_stage end as current_stage";

        $fields[] = "case bt.task_type 
            when {$tasktypeCfg['SURE_BACKUP']} then ssb.hypervisor_type
            when {$tasktypeCfg['DB_CDP_BACKUP']} then cdt.config
            when {$tasktypeCfg['VOL_CDP_BACKUP']} then cvt.current_task_running_stage
            when {$tasktypeCfg['VOL_CDP_RECOVERY']} then cvt.current_task_running_stage
            when {$tasktypeCfg['VOL_CDP_TAKEOVER']} then cvt.current_task_running_stage
            when {$tasktypeCfg['VOL_CDP_REPLICATION']} then cvt.current_task_running_stage
            else bt.id end as task_type_alias";

        $agentStatusSql = '(select count(agent_uuid) from bd_task_agent_list btal2 where btal2.task_uuid = bt.task_uuid)';
        $fields[] = "case bt.module_type 
            when {$moduletypeCfg['FS']} then CONCAT(btal.task_status, '-', {$agentStatusSql})
            when {$moduletypeCfg['NAS']} then CONCAT(btal.task_status, '-', {$agentStatusSql})
            when {$moduletypeCfg['DB']} then dt1.db_type
            when {$moduletypeCfg['VM']} then vt1.hypervisor_type
            when {$moduletypeCfg['VOL_CDP']} then cvt1.current_task_running_stage
            else bt.id end as module_type_agent";

        $dbTypeCase = 'case dt1.db_type ';
        foreach ($dbTypeCfg as $value) {
            $dbTypeCase .= $value == 0 ? "when 0 then '{$dbTypeDescCfg[0]}' " : "when $value then '{$dbTypeDescCfg[$value]}' ";
        }
        $fields[] = $dbTypeCase . ' end as db_type_des';

        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == xphp_get_config('app', 'VENDOR_LIST')['gmp']) {
            $fields[] = 'al.`name` as approval_name';
            $fields[] = 'it.`name` as temp_name';
        }

        // ----- JOINs -----
        $joins = [
            'INNER JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid',
            'INNER JOIN bd_user bu ON bt.user_uuid = bu.user_uuid',
            'LEFT JOIN vm_task vt ON bt.task_uuid = vt.task_uuid',
            'LEFT JOIN cdp_db_dr_task ON bt.task_uuid = cdp_db_dr_task.task_uuid',
            'LEFT JOIN cdp_vol_task_takeover_info cvti ON bt.task_uuid = cvti.task_uuid',
            'LEFT JOIN cdp_db_dr_task_progress_info cddtpi ON cddtpi.task_uuid = bt.task_uuid',
            'LEFT JOIN cdp_db_dr_task cddt ON cddt.task_uuid = bt.task_uuid',
            'LEFT JOIN sr_surebackup ssb ON bt.task_uuid = ssb.sr_task_uuid',
            'LEFT JOIN cdp_db_task cdt ON bt.task_uuid = cdt.task_uuid',
            'LEFT JOIN cdp_vol_task cvt ON bt.task_uuid = cvt.task_uuid',
            'LEFT JOIN bd_backup_timepoint bbt_main ON cvt.timepoint_uuid = bbt_main.timepoint_uuid',
            'LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid',
            'LEFT JOIN copy_task ct ON bt.task_uuid = ct.task_uuid',
            'LEFT JOIN copy_list cl ON bt.task_uuid = cl.task_uuid',
            'LEFT JOIN bd_task_orchestration_plan_section_task btopst ON bt.task_uuid = btopst.task_uuid',
            'LEFT JOIN (select task_uuid, task_status from bd_task_agent_list group by task_uuid) btal ON bt.task_uuid = btal.task_uuid',
            'LEFT JOIN db_task dt1 ON bt.task_uuid = dt1.task_uuid',
            'LEFT JOIN vm_task vt1 ON bt.task_uuid = vt1.task_uuid',
            'LEFT JOIN cdp_vol_task cvt1 ON bt.task_uuid = cvt1.task_uuid',
            'LEFT JOIN bd_instant_recovery_task birt ON birt.task_uuid = bt.task_uuid'
        ];

        if (array_diff(['vcenter_hypervisor', 'vcenter_name'], $hiddenFields) || !empty($otherVmName)) {
            $fields[] = 'vc.hypervisor_type';
            $fields[] = 'vc.nickname';
            $joins[] = 'LEFT JOIN vm_machine_list vml ON bt.task_uuid = vml.task_uuid';
            $joins[] = 'LEFT JOIN vm_vcenter vc ON vml.vcenter_uuid = vc.vcenter_uuid';
        }

        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == xphp_get_config('app', 'VENDOR_LIST')['gmp']) {
            $joins[] = "LEFT JOIN industry_report ir ON ir.task_uuid = bt.task_uuid";
            $joins[] = "LEFT JOIN approval_list al ON al.approval_uuid = ir.approval_uuid";
            $joins[] = "LEFT JOIN industry_temp it ON it.temp_uuid = ir.temp_uuid";
        }

        // ----- WHERE -----
        $wheres[] = 'bt.delete_flag = ?';
        $sqlParams[] = $flag['UNSET'];

        if ($noSureBackupFlag) {
            $wheres[] = 'bt.task_type != ?';
            $sqlParams[] = $tasktypeCfg['SURE_BACKUP'];
        }

        $user = xphp_get_user_info();
        if (empty($user['tenantuuid'])) {
            $joins[] = "LEFT JOIN mt_user_tenant mut ON bu.user_uuid = mut.user_uuid";
            $wheres[] = 'mut.tenant_uuid IS NULL';
        }

        // 判断是否为全局观察者查看权限
        if (v1_auth_need_check_look()) {
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['current_job']);
            $wheres[] = "bt.user_uuid IN ({$userUuidSql})";
        }

        if (!empty($userName)) {
            $wheres[] = 'bu.user_name LIKE ?';
            $sqlParams[] = '%' . $userName . '%';
        }

        $objectSql = ' WHERE sbt.sr_task_uuid = bt.task_uuid ';
        if (!empty($moduleType)) {
            $joins[] = 'LEFT JOIN sr_surebackup_item sbt ON sbt.sr_task_uuid = bt.task_uuid';
            $moduleArrSql = implode(',', $moduleType);
            $msqlParts = ["sbt.module_type IN ({$moduleArrSql})"];

            if (!empty($subModuleType)) {
                $submoduleArrSql = implode(',', $subModuleType);
                $whereOrParts[] = "(bt.module_type IN ({$moduleArrSql}) AND bt.sub_module_type IN ({$submoduleArrSql}))";
                $whereOrParts[] = "(sbt.module_type IN ({$moduleArrSql}) AND sbt.submodule_type IN ({$submoduleArrSql}))";
                $msqlParts[] = "sbt.submodule_type IN ({$submoduleArrSql})";
            } else {
                $whereOrParts[] = "bt.module_type IN ({$moduleArrSql})";
                $whereOrParts[] = "sbt.module_type IN ({$moduleArrSql})";
            }
            $wheres[] = '(' . implode(' OR ', $whereOrParts) . ')';
            $msql = ' WHERE ' . implode(' AND ', $msqlParts);
            $objectSql .= " AND sbt.sr_task_uuid IN (SELECT sbt.sr_task_uuid FROM sr_surebackup_item sbt {$msql})";
        }

        $fields[] = "(SELECT GROUP_CONCAT(sbt.module_type SEPARATOR ',') FROM sr_surebackup_item sbt {$objectSql}) AS sure_module_types";
        $fields[] = "(SELECT GROUP_CONCAT(sbt.submodule_type SEPARATOR ',') FROM sr_surebackup_item sbt {$objectSql}) AS sure_sub_module_types";
        $fields[] = "(SELECT count(*) FROM bd_grain_recovery_share WHERE task_uuid = bt.task_uuid AND task_status = {$taskStatusCfg['RUNNING']}) AS grain_share_num";
        $fields[] = "(SELECT count(*) FROM bd_grain_recovery_transport WHERE task_uuid = bt.task_uuid AND task_status = {$taskStatusCfg['RUNNING']}) AS grain_transfer_num";

        $moduleTimepointCase = "CASE
            WHEN bt.task_type = {$tasktypeCfg['PLATFORM_RECOVERY']} AND bt.module_type = {$moduletypeCfg['VM']} THEN
                (SELECT CONCAT(bbt.module_type, '-', bbt.sub_module_type) FROM vm_machine_list vml, bd_backup_timepoint bbt WHERE vml.timepoint_uuid = bbt.timepoint_uuid AND vml.task_uuid = bt.task_uuid LIMIT 1)
            WHEN bt.task_type = {$tasktypeCfg['PLATFORM_RECOVERY']} AND bt.module_type = {$moduletypeCfg['OS']} THEN
                (SELECT CONCAT(bbt.module_type, '-', bbt.sub_module_type) FROM os_list ol, bd_backup_timepoint bbt WHERE ol.timepoint_uuid = bbt.timepoint_uuid AND ol.task_uuid = bt.task_uuid LIMIT 1)
            WHEN bt.task_type = {$tasktypeCfg['PLATFORM_RECOVERY']} AND bt.module_type = {$moduletypeCfg['VOL_CDP']} THEN
                (SELECT bbt.module_type FROM cdp_vol_task_restore_info cvtri, bd_backup_timepoint bbt WHERE cvtri.recovery_datetime_uuid = bbt.timepoint_uuid AND cvtri.task_uuid = bt.task_uuid LIMIT 1)
            ELSE bt.id END";
        $fields[] = "{$moduleTimepointCase} AS module_timepoint";

        if (!empty($taskType)) {
            if (empty($moduleType)) {
                if (in_array($tasktypeCfg['BACKUP'], $taskType))
                    $taskType = array_merge($taskType, [$tasktypeCfg['DB_BACKUP'], $tasktypeCfg['OS_BACKUP']]);
                if (in_array($tasktypeCfg['RECOVERY'], $taskType))
                    $taskType = array_merge($taskType, [$tasktypeCfg['DB_RECOVERY'], $tasktypeCfg['OS_RECOVERY']]);
            }
            $wheres[] = 'bt.task_type IN (' . implode(',', $taskType) . ')';
        }

        if (!empty($taskStatus))
            $wheres[] = 'bt.task_status IN (' . implode(',', $taskStatus) . ')';
        if (!empty($taskName)) {
            $wheres[] = 'bt.task_name LIKE ?';
            $sqlParams[] = '%' . $taskName . '%';
        }

        if (!empty($otherHostName)) {
            $agentName = '%' . $otherHostName . '%';
            $wheres[] = "EXISTS (
                SELECT 1 FROM bd_task_agent_list btal
                INNER JOIN bd_agent ba ON ba.agent_uuid = btal.agent_uuid
                WHERE btal.task_uuid = bt.task_uuid AND (ba.agent_name LIKE ? OR ba.hostname LIKE ? OR ba.ip LIKE ?)
            )";
            array_push($sqlParams, $agentName, $agentName, $agentName);
        }

        if (!empty($otherVmName)) {
            $wheres[] = '(vml.vm_name LIKE ? OR vml.vcenter_ip LIKE ? OR cl.item_name LIKE ?)';
            array_push($sqlParams, '%' . $otherVmName . '%', '%' . $otherVmName . '%', '%' . $otherVmName . '%');
        }

        if ($vmType > 0) {
            $joins[] = 'INNER JOIN vm_task vt_s ON bt.task_uuid = vt_s.task_uuid';
            $wheres[] = 'vt_s.hypervisor_type = ?';
            $sqlParams[] = $vmType;
        }

        if ($dbType > 0) {
            $joins[] = 'INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid';
            $wheres[] = 'dt.db_type = ?';
            $sqlParams[] = $dbType;
        }

        if (!empty($nodeuuid)) {
            $wheres[] = 'bt.node_uuid = ?';
            $sqlParams[] = $nodeuuid;
        }

        if (!empty($storageuuid)) {
            $wheres[] = 'bt.storage_uuid = ?';
            $sqlParams[] = $storageuuid;
        }

        // 筛选实时整机或卷
        if (!empty($params['dev_type'])) {
            $dev_types = explode(',', trim($params['dev_type']));
            $backup_dev_types = []; // 实时整机/卷类型
            $replication_dev_types = []; // 复制整机/卷类型

            foreach ($dev_types as $item) {
                $item = intval($item);
                if ($item <= 2) { // 1和2 为实时保护的整机和卷
                    $backup_dev_types[] = $item;
                } else { // 3 和 4 为实时复制的整机和卷
                    $replication_dev_types[] = $item - 2;
                }
            }

            $include_surebackup = !empty($taskType) && in_array($tasktypeCfg['SURE_BACKUP'], $taskType); // 判断是否为数据验证任务
            $include_takeover = !empty($taskType) && in_array($tasktypeCfg['VOL_CDP_TAKEOVER'], $taskType); // 判断是否为接管任务

            $devTypeWheres = [];
            // --- 实时保护 (dev_type 1, 2) ---
            if (!empty($backup_dev_types)) {
                $dev_in_clause = implode(',', array_unique($backup_dev_types));
                $conditions = [];

                if (!empty($taskType)) {
                    // 过滤出非特殊处理的普通任务类型
                    $other_task_types = array_filter($taskType, function ($t) use ($tasktypeCfg) {
                        return !in_array($t, [$tasktypeCfg['SURE_BACKUP'], $tasktypeCfg['VOL_CDP_TAKEOVER']]);
                    });

                    if (!empty($other_task_types)) {
                        $conditions[] = "(bt.task_type IN (" . implode(',', $other_task_types) . ") AND cvt.dev_type IN ($dev_in_clause))";
                    }

                    // 为数据验证任务构建条件
                    if ($include_surebackup) {
                        $conditions[] = "(bt.task_type = {$tasktypeCfg['SURE_BACKUP']} AND sbt.submodule_type IN ($dev_in_clause))";
                    }

                    // 为接管任务构建条件 (原始任务为实时保护)需要通过bd_backup_timepoint表获取其task_type
                    if ($include_takeover) {
                        $conditions[] = "(bt.task_type = {$tasktypeCfg['VOL_CDP_TAKEOVER']} AND bbt_main.task_type = {$tasktypeCfg['VOL_CDP_BACKUP']} AND cvt.dev_type IN ($dev_in_clause))";
                    }

                    if (!empty($conditions)) {
                        $devTypeWheres[] = '(' . implode(' OR ', $conditions) . ')';
                    }
                } else { // 未传任务类型，默认查询CDP下的备份
                    $default_types = implode(',', [$tasktypeCfg['VOL_CDP_BACKUP']]);
                    $devTypeWheres[] = "(bt.task_type IN ($default_types) AND cvt.dev_type IN($dev_in_clause))";
                }
            }

            // --- 复制容灾 (dev_type 3, 4) ---
            if (!empty($replication_dev_types)) {
                $dev_in_clause = implode(',', array_unique($replication_dev_types));
                $conditions = [];

                if (!empty($taskType)) {
                    // 过滤出非特殊处理的普通任务类型
                    $other_task_types = array_filter($taskType, function ($t) use ($tasktypeCfg) {
                        return !in_array($t, [$tasktypeCfg['SURE_BACKUP'], $tasktypeCfg['VOL_CDP_TAKEOVER']]);
                    });
                    if (!empty($other_task_types)) {
                        $conditions[] = "(bt.task_type IN (" . implode(',', $other_task_types) . ") AND cvt.dev_type IN ($dev_in_clause))";
                    }

                    // 为数据验证任务构建条件
                    if ($include_surebackup) {
                        $verifyReplicationDevTypes = array_map(function ($type) {
                            return $type + 2;
                        }, $replication_dev_types);
                        $verifyInClause = implode(',', array_unique($verifyReplicationDevTypes));
                        $conditions[] = "(bt.task_type = {$tasktypeCfg['SURE_BACKUP']} AND sbt.submodule_type IN ($verifyInClause))";
                    }

                    // 为接管任务构建条件 (原始任务为复制容灾)
                    if ($include_takeover) {
                        $conditions[] = "(bt.task_type = {$tasktypeCfg['VOL_CDP_TAKEOVER']} AND bbt_main.task_type = {$tasktypeCfg['VOL_CDP_REPLICATION']} AND cvt.dev_type IN ($dev_in_clause))";
                    }

                    if (!empty($conditions)) {
                        $devTypeWheres[] = '(' . implode(' OR ', $conditions) . ')';
                    }
                } else { // 未传任务类型，默认查询复制任务
                    $devTypeWheres[] = "(bt.task_type = {$tasktypeCfg['VOL_CDP_REPLICATION']} AND cvt.dev_type IN($dev_in_clause))";
                }
            }

            if (!empty($devTypeWheres)) {
                $wheres[] = '(' . implode(' OR ', $devTypeWheres) . ')';
            }
        }

        if (!empty($startTime) && !empty($endTime)) {
            $wheres[] = "bt.create_time BETWEEN ? AND ?";
            array_push($sqlParams, $startTime, $endTime);
        }

        // 5. 组装并执行SQL
        $finalFields = implode(",\n", $fields);
        $finalFrom = "FROM {$from}";
        $finalJoins = implode("\n", $joins);
        $finalWheres = 'WHERE ' . implode("\nAND ", $wheres);

        $countSql = "SELECT count(DISTINCT bt.task_uuid) as total {$finalFrom} {$finalJoins} {$finalWheres}";
        if (!empty($moduleType)) {
            // 针对 moduleType 筛选时，JOIN sbt 可能导致主任务重复，需要特殊处理 count
            $countQuery = "SELECT bt.task_uuid {$finalFrom} {$finalJoins} {$finalWheres} GROUP BY bt.task_uuid";
            $countSql = "SELECT count(*) as total FROM ({$countQuery}) as temp";
        }

        $total = $this->dbSelect($countSql, $sqlParams)[0]['total'] ?? 0;

        $data = [];
        if ($total > 0) {
            $sortMap = [
                'id' => 'bt.id',
                'job_name' => 'bt.task_name',
                'module_type' => 'bt.module_type',
                'job_type_value' => 'bt.task_type',
                'create_time' => 'bt.create_time',
                'user_name' => 'bt.user_name',
                'job_status_value' => 'bt.task_status'
            ];
            // 根据前端传递的 sort 参数获取实际的排序列
            $sortKey = $params['sort'] ?? 'id';
            // 优先从映射中查找，如果未找到，则使用默认的 bt.id 排序以防止SQL错误
            $sort = $sortMap[$sortKey] ?? 'bt.id';
            $order = $params['order'] ?? 'desc';
            $orderBy = "ORDER BY {$sort} {$order}";
            $limitSql = $isExportAll ? '' : "LIMIT {$start}, {$length}";

            $dataSql = "SELECT {$finalFields} {$finalFrom} {$finalJoins} {$finalWheres} GROUP BY bt.task_uuid {$orderBy} {$limitSql}";
            $data = $this->dbSelect($dataSql, $sqlParams);
        }

        // 6. 处理数据库返回结果
        $rows = $this->formatAndOptimizeJobList($data);

        return [
            'rows' => $rows,
            'total' => $total
        ];
    }

    /**
     * 格式化数据库查询到的任务列表
     *
     * @param [type] $data
     * @param array $hiddenFields
     * @return void
     */
    private function formatAndOptimizeJobList($data, $hiddenFields = [])
    {
        if (empty($data)) {
            return [];
        }

        // 预加载配置信息
        $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $commonStageType = xphp_get_config('task', 'COMMON_STAGE_LIST');
        $hypervisorDes = xphp_get_config('vm', 'VMHYPERVISORDES');
        $userInfo = xphp_get_user_info();
        $dbTypeConfig = xphp_get_config('db', 'DB_TYPE');
        $node = new Node();

        // --- 收集所有需要的UUID ---
        $node_uuids = []; // 节点UUID
        $strategy_ids = []; // 策略ID
        $task_uuids_for_db_assoc = []; // 数据库关联任务UUID
        $task_uuids_for_next_time = []; // 下一次执行时间任务UUID
        $task_status_map_for_next_time = []; // 下一次执行时间任务状态映射
        $task_uuids_for_orchestration = []; // 编排任务UUID
        $task_uuids_for_archive = []; // 归档任务UUID

        foreach ($data as $d) {
            $node_uuids[] = $d['node_uuid'];
            $strategy_ids[] = $d['strategy_id'];
            $task_uuids_for_next_time[] = $d['task_uuid'];
            $task_status_map_for_next_time[$d['task_uuid']] = $d['task_status'];
            $task_uuids_for_archive[] = array(
                'task_uuid' => $d['task_uuid'],
                'module_type' => $d['module_type'],
                'sub_module_type' => $d['sub_module_type']
            );
            if ($d['module_type'] == $moduleType['DB'] && $d['task_type'] == $taskType['DB_BACKUP']) {
                $task_uuids_for_db_assoc[] = $d['task_uuid'];
            }
            if (v1_parse_flag_to_bool($d['task_orchestration_plan_flag'])) {
                $task_uuids_for_orchestration[] = $d['task_uuid'];
            }
        }

        // --- 批量获取数据并创建映射表，解决循环中 N+1 查询性能问题 ---
        $node_names_map = $node->getNodesName(array_unique(array_filter($node_uuids)));
        $strategies_map = $this->getBackupStrategies(array_unique(array_filter($strategy_ids)));
        $db_associated_jobs_map = (new \app\v1\db\v0\logic\DbJobInfo())->getDbAssociatedJobInfoForTasks($task_uuids_for_db_assoc);
        $next_times_map = $this->getJobsNextstarttime($task_uuids_for_next_time, $task_status_map_for_next_time);
        $orchestration_names_map = $this->getOrchestrationNames($task_uuids_for_orchestration);
        $archive_flags_map = $this->getArchiveFlags($task_uuids_for_archive);

        // --- 循环格式化数据 ---
        $records = [];
        foreach ($data as $d) {
            $submoduletype = $d['sub_module_type'];
            $dbType = $d['module_type'] == $moduleType['DB'] ? $d['module_type_agent'] : 0;
            $dbAssociatedJobList = [];
            $dbJobType = 'master';

            if ($d['module_type'] == $moduleType['DB'] && $d['task_type'] == $taskType['DB_BACKUP']) {
                $associatedJobInfo = $db_associated_jobs_map[$d['task_uuid']] ?? [];
                if ($associatedJobInfo) {
                    if ($dbType == $dbTypeConfig['TIDB']) {
                        foreach ($associatedJobInfo as $associatedJobRow) {
                            $dbAssociatedJobList[] = [
                                'db_job_type' => $associatedJobRow['depend_task_uuid'] ? 'slave' : 'master',
                                'db_job_name' => $associatedJobRow['task_name'],
                                'db_job_uuid' => $associatedJobRow['task_uuid'],
                                'db_job_status' => $associatedJobRow['task_status'],
                            ];
                        }
                        if ($d['depend_task_uuid'])
                            $dbJobType = 'slave';
                    } elseif ($dbType == $dbTypeConfig['ORACLE']) {
                        foreach ($associatedJobInfo as $associatedJobRow) {
                            $dbAssociatedJobList[] = [
                                'db_job_type' => $associatedJobRow['multi_task_flag'] ? 'slave' : 'master',
                                'db_job_name' => $associatedJobRow['task_name'],
                                'db_job_uuid' => $associatedJobRow['task_uuid'],
                                'db_job_status' => $associatedJobRow['task_status'],
                            ];
                        }
                        if (v1_parse_flag_to_bool($d['multi_task_flag']))
                            $dbJobType = 'slave';
                    }
                }
            }

            $instant = !empty($d['instant_target_info']) ? json_decode($d['instant_target_info'], true) : [];

            // 处理阶段显示
            $currentstage = '--';
            if ($d['task_type'] == $taskType['INSTANT_RECOVERY_MOTION']) {
                $stageArr = xphp_get_config('task', 'TASK_STAGE_MOTION');
                $currentstage = $d['migrate_phase'] == 0 ? '--' : xphp_get_lang($stageArr[$d['migrate_phase']]);
            } else {
                $stageArr = xphp_get_config('task', 'COMMON_STAGE');
                if ($d['module_type'] == $moduleType['VOL_CDP'])
                    $stageArr = xphp_get_config('task', 'REAL_PROTECT_STAGE');
                if ($d['module_type'] == $moduleType['DB_CDP'])
                    $stageArr = xphp_get_config('task', 'DB_CDP_PROTECT_STAGE');

                $currentstage = !empty($stageArr[$d['current_stage']]) ? xphp_get_lang($stageArr[$d['current_stage']]) : '--';

                if ($d['current_stage'] == $commonStageType['TYPE_VIRUS_CHECK'] && $d['task_type'] == $taskType['SURE_BACKUP']) {
                    $currentstage = xphp_get_lang('UI_PLATFORM_RECOVERY_JOB_VIRUS_SCAN');
                }
            }
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }
            if (
                in_array($d['module_type'], [$moduleType['DB'], $moduleType['OS'], $moduleType['VOL_CDP'], $moduleType['KUBERNETES']]) &&
                in_array($d['task_status'], [xphp_get_config('task', 'TASKSTATUS')['WAITTING'], xphp_get_config('task', 'TASKSTATUS')['STOPPED']])
            ) {
                $currentstage = '--';
            }

            $moduletypeAlis = !empty($d['sure_module_types']) ? $d['sure_module_types'] : $d['module_type'];
            $submoduletypeAlis = !empty($d['sure_module_types']) ? $d['sure_sub_module_types'] : $d['sub_module_type'];
            if ($d['task_type'] == $taskType['PLATFORM_RECOVERY']) {
                $moduleCross = explode('-', $d['module_timepoint']);
                $moduletypeAlis = $moduleCross[0];
                $submoduletypeAlis = $moduleCross[1] ?? 0;
            }

            $record = [
                'job_uuid' => $d['task_uuid'],
                'job_name' => $d['task_name'],
                'module_type' => $this->getObjects($moduletypeAlis, $submoduletypeAlis, $d['dev_type'] ?? ''),
                'module_type_value' => $d['module_type'],
                'instant_hypervisor_type' => $instant['hypervisor_type'] ?? 0,
                'sub_module_type_value' => $submoduletype,
                'job_type' => $this->getTaskNameString($moduleType, $taskType, $d['module_type'], $d['task_type'], $d['standby_agent_uuid']),
                'job_type_value' => $d['task_type'],
                'create_time' => $this->parseDate($d['create_time']),
                'job_status_value' => $d['task_status'],
                'job_status' => $ptDes[$d['task_status']],
                'speed' => $this->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time'], $d['task_type']),
                'progress' => $this->getCurrentTaskProgress($d),
                'dbcdp_progress' => '',
                'user_name' => $d['user_name'],
                'user_uuid' => $d['user_uuid'],
                'db_type' => $dbType,
                'db_associated_job_list' => $dbAssociatedJobList,
                'db_job_type' => $dbJobType,
                'vm_type' => $d['module_type'] == $moduleType['VM'] ? intval($d['module_type_agent']) : 0,
                'vcenter_name' => $d['nickname'] ?? '--',
                'vcenter_hypervisor' => $hypervisorDes[$d['hypervisor_type']] ?? '--',
                'job_stage' => $d['module_type'] == $moduleType['VOL_CDP'] ? intval($d['module_type_agent']) : 0,
                'job_dbcdp_stage' => $d['module_type'] == $moduleType['DB_CDP'] ? intval($d['module_type_alias']) : 0,
                'op_list' => $this->getTaskOpCodeByType($d['task_type'], $d['task_status'], $d['task_type_alias'], $d['task_uuid'], $dbType, $d['dev_type']),
                'back_storage' => $d['storage_nickname'] ?? '--',
                'storage_type' => $d['storage_type'] ?? '--',
                'back_node' => $node_names_map[$d['node_uuid']] ?? '--', // 使用映射
                'tenant_uuid' => $userInfo['tenantuuid'],
                'dbcdp_info' => $this->getDBCDPOtherInfo($d['module_type'], $d['task_type'], $d['task_uuid']),
                'mode_list' => $strategies_map[$d['strategy_id']] ?? [], // 使用映射
                'copy_mode' => $d['task_type'] == $taskType['BACKUP_COPY'] ? intval($d['copy_mode']) : 1,
                'archive_flag' => $archive_flags_map[$d['task_uuid']] ?? 0, // 使用映射
                'auto_takeover_flag' => $d['auto_takeover_flag'],
                'auto_takeover_enable_flag' => $d['auto_takeover_enable_flag'],
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'takeover_agent_role' => $d['takeover_agent_role'] ?? '--',
                'takeover_config_flag' => !empty($d['takeover_agent_role']),
                'dev_type' => $d['dev_type'] ?? '',
                'standby_agent_uuid' => $d['standby_agent_uuid'] ?? '',
                'current_stage' => $d['current_stage'],
                'stage_percent' => $d['stage_percent'],
                'current_stage_value' => $currentstage,
                'business_type' => $this->getObject($d['module_type'], $d['task_type']),
                'grain_num_flag' => $d['grain_share_num'] || $d['grain_transfer_num'],
                'migrate_status' => intval($d['migrate_status']),
            ];

            if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == xphp_get_config('app', 'VENDOR_LIST')['gmp']) {
                $record['approval_name'] = $d['approval_name'] ?: '--';
                $record['temp_name'] = $d['temp_name'] ?: '--';
            }

            if (!in_array('next_time', $hiddenFields)) {
                $record['next_time'] = $next_times_map[$d['task_uuid']] ?? '--'; // 使用映射
            }
            if (!in_array('running_time', $hiddenFields)) {
                $record['running_time'] = $this->getTimeInterval($d['start_time'], $d['task_status']);
            }
            if (!in_array('orchestration_name', $hiddenFields)) {
                $record['orchestration_name'] = $orchestration_names_map[$d['task_uuid']] ?? '--'; // 使用映射
            }

            $records[] = $record;
        }

        return $records;
    }

    // 获取任务所在编排任务的名称
    private function getOrchestrationName($uuid, $flag)
    {
        if ($flag == 2) { // 如果不被编排占用 直接返回空
            return xphp_get_config('app', 'NULLSPACE');
        }
        $sql = "select btop.plan_nickname from bd_task_orchestration_plan btop, bd_task_orchestration_plan_section_task btopst where btop.plan_uuid = btopst.plan_uuid and btopst.task_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        return $data[0]['plan_nickname'];
    }

    /**
     * 批量获取任务所在编排任务的名称
     * @param array $uuids
     * @return array
     */
    private function getOrchestrationNames(array $uuids)
    {
        if (empty($uuids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($uuids), '?'));
        $sql = "select btopst.task_uuid, btop.plan_nickname 
                from bd_task_orchestration_plan btop, bd_task_orchestration_plan_section_task btopst 
                where btop.plan_uuid = btopst.plan_uuid and btopst.task_uuid IN ({$placeholders})";

        $data = $this->dbSelect($sql, $uuids);

        return array_column($data, 'plan_nickname', 'task_uuid');
    }

    /**
     * 虚拟机当前任务获取vcenter
     * @param unknown $vcenterUuid vcenter的uuid
     * @return string
     */
    private function getVcenterName($vcenterUuid)
    {
        $sql = 'select nickname from vm_vcenter where vcenter_uuid = ?';
        $sqlParams = array($vcenterUuid);
        $data = dbSelect($sql, $sqlParams);
        return $data[0]['nickname'];
    }

    /**
     * 是否开启归档
     * @param unknown $taskUuid   任务的uuid
     * @param unknown $moduleType 模块类别
     * @return boolean
     */
    private function getArchiveFlag($taskUuid, $moduleType, $subModuleType)
    {
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $subModuleTypeArr = xphp_get_config('module', 'SUBMODULE_TYPE');
        if ($moduleType == $moduleTypeArr['FS'] && $subModuleType == $subModuleTypeArr['FS']) {
            $sql = "SELECT file_archive_flag from fs_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return $data[0]['file_archive_flag'] != 2;
        } elseif ($moduleType == $moduleTypeArr['FS'] && $subModuleType == $subModuleTypeArr['NAS']) {
            $sql = "SELECT file_archive_flag from nas_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return $data[0]['file_archive_flag'] != 2;
        } elseif ($moduleType == $moduleTypeArr['NAS']) {
            $sql = "SELECT file_archive_flag from nas_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($taskUuid));
            return $data[0]['file_archive_flag'] != 2;
        } else {
            return false;
        }
    }

    /**
     * 批量获取归档标志
     * @param array $jobs 任务列表
     * @return array
     */
    private function getArchiveFlags(array $jobs): array
    {
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $subModuleTypeArr = xphp_get_config('module', 'SUBMODULE_TYPE');

        $fs_task_uuids = [];
        $nas_task_uuids = [];

        $all_task_uuids = array_column($jobs, 'task_uuid');
        $archive_flags_map = array_fill_keys($all_task_uuids, false);

        foreach ($jobs as $job) {
            $taskUuid = $job['task_uuid'];
            $moduleType = $job['module_type'];
            $subModuleType = $job['sub_module_type'];

            if ($moduleType == $moduleTypeArr['FS'] && $subModuleType == $subModuleTypeArr['FS']) {
                $fs_task_uuids[] = $taskUuid;
            } elseif ($moduleType == $moduleTypeArr['FS'] && $subModuleType == $subModuleTypeArr['NAS']) {
                $nas_task_uuids[] = $taskUuid;
            } elseif ($moduleType == $moduleTypeArr['NAS']) {
                $nas_task_uuids[] = $taskUuid;
            }
        }

        if (!empty($fs_task_uuids)) {
            $fs_task_uuids = array_unique($fs_task_uuids);
            $in_clause_fs = implode(',', array_fill(0, count($fs_task_uuids), '?'));
            $sql = "SELECT task_uuid, file_archive_flag from fs_task where task_uuid IN ($in_clause_fs)";
            $data = $this->dbSelect($sql, $fs_task_uuids);
            foreach ($data as $row) {
                $archive_flags_map[$row['task_uuid']] = ($row['file_archive_flag'] != 2);
            }
        }

        if (!empty($nas_task_uuids)) {
            $nas_task_uuids = array_unique($nas_task_uuids);
            $in_clause_nas = implode(',', array_fill(0, count($nas_task_uuids), '?'));
            $sql = "SELECT task_uuid, file_archive_flag from nas_task where task_uuid IN ($in_clause_nas)";
            $data = $this->dbSelect($sql, $nas_task_uuids);
            foreach ($data as $row) {
                $archive_flags_map[$row['task_uuid']] = ($row['file_archive_flag'] != 2);
            }
        }

        return $archive_flags_map;
    }

    /**
     * 数据库实时获取所需启动任务的字段
     * @param int $module
     * @param int $tasktype
     */
    private function getDBCDPOtherInfo($module, $tasktype, $taskUuid)
    {
        if ($module != 10000) { //不是数据库实时
            return xphp_get_config('app', 'NULLSPACE');
        }
        $sql = "select cdh1.host_name as productname, cdh1.ip as productip, cdh1.host_uuid as productuuid, 
                		cdh2.host_name as standbyname, cdh2.ip as standbyip, cdh2.host_uuid as standbyuuid, cdt.config 
                from cdp_db_task as cdt 
                inner join cdp_db_host as cdh1 
                on cdt.product_host_uuid = cdh1.host_uuid 
                inner join cdp_db_host as cdh2 
                on cdt.standby_host_uuid = cdh2.host_uuid 
                where cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));

        $backupType = '';
        if (xphp_get_config('task', 'TASKTYPE')['DB_CDP_BACKUP'] == $tasktype) {
            //如果是备份,添加备份类型,
            $config = json_decode($data[0]['config'], true);
            $backupType = intval($config['standbyhostInfo']['backuptype']);
        }
        $info = array(
            'productdes' => $data[0]['productname'] . '(' . $data[0]['productip'] . ')',
            'standbydes' => $data[0]['standbyname'] . '(' . $data[0]['standbyip'] . ')',
            'productip' => $data[0]['productip'],
            'standbyip' => $data[0]['standbyip'],
            'productuuid' => $data[0]['productuuid'],
            'standbyuuid' => $data[0]['standbyuuid'],
            'tasktype' => intval($tasktype),
            'backuptype' => $backupType
        );
        return $info;
    }

    /**
     * 获取历史任务详情
     *  history_uuid
     * @param array $params 数据
     * @return json
     */
    public function getHistory($params = [])
    {
        // 展示详情
        return $this->getHistoryDetail($params['history_uuid']);
    }

    /**
     * 导出全部历史任务
     * @return void
     */
    public function exportAllHistoryJobs($params)
    {
        $params['module_type'] = $params['module_type'] ? implode(',', $params['module_type']) : [];
        $params['sub_module_type'] = $params['sub_module_type'] ? implode(',', $params['sub_module_type']) : [];
        $params['dev_type'] = $params['dev_type'] ? implode(',', $params['dev_type']) : [];
        $params['job_type'] = $params['job_type'] ? implode(',', $params['job_type']) : [];
        $params['job_status'] = $params['job_status'] ? implode(',', $params['job_status']) : [];

        $exportData = $this->getHistoryList($params, true)['rows'];
        $title = xphp_get_lang('UI_PLATFORM_HOSTORY_JOB');

        $header = [
            'job_name' => xphp_get_lang('UI_PUBLIC_TASK_RNAME'),
            'module_type' => xphp_get_lang('UI_PUBLIC_MODULE_TYPE'),
            'job_type' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'user_name' => xphp_get_lang('UI_JOB_CREATOR'),
            'start_time' => xphp_get_lang('UI_STRATEGY_STARTTIME'),
            'finish_time' => xphp_get_lang('UI_VOL_CDP_END_TIME'),
            'all_size' => xphp_get_lang('UI_JOB_TOTAL_SIZE'),
            'validate_size' => xphp_get_lang('UI_JOB_VALID_DATA_SIZE'),
            'speed_size' => xphp_get_lang('UI_JOB_TRANSFER_SIZE'),
            'write_size' => xphp_get_lang('UI_JOB_REAL_SIZE'),
            'status' => xphp_get_lang('UI_PUBLIC_STATUS')
        ];

        $exportData = array_map(function ($row) {
            return [
                'job_name' => $row['job_name'],
                'module_type' => $row['module_type'],
                'job_type' => $row['job_type'],
                'user_name' => $row['user_name'],
                'start_time' => $row['start_time'],
                'finish_time' => $row['finish_time'],
                'all_size' => $row['all_size'],
                'validate_size' => $row['validate_size'],
                'speed_size' => $row['speed_size'],
                'write_size' => $row['write_size'],
                'status' => $row['job_status']
            ];
        }, $exportData);

        $relation = [
            'job_name' => ['col_name' => 'A', 'width' => 25],
            'module_type' => ['col_name' => 'B', 'width' => 15],
            'job_type' => ['col_name' => 'C', 'width' => 15],
            'user_name' => ['col_name' => 'D', 'width' => 20],
            'start_time' => ['col_name' => 'E', 'width' => 20],
            'finish_time' => ['col_name' => 'F', 'width' => 20],
            'all_size' => ['col_name' => 'G', 'width' => 15],
            'validate_size' => ['col_name' => 'H', 'width' => 15],
            'speed_size' => ['col_name' => 'I', 'width' => 15],
            'write_size' => ['col_name' => 'J', 'width' => 15],
            'status' => ['col_name' => 'K', 'width' => 15],
        ];

        v1_base_export($title, $header, $exportData, $relation);
    }

    /**
     * 获取历史任务列表
     * @param mixed $params
     * @param mixed $isExportAll 是否导出全部
     * @return array{rows: array, total: int|array{rows: array, total: mixed}}
     */
    public function getHistoryList($params = [], $isExportAll = false): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $taskName = v1_escape_wildcard($params['job_name'] ?: $params['search']); // 任务名称
        $vmname = v1_escape_wildcard($params['other_vm_name']); // 虚拟机名/IP搜索
        $dbType = intval($params['db_type']); // module_type 参数为数据库（4）时可能存在的数据库类型
        $hypervisor = intval($params['vm_type']); // module_type 参数为虚拟机（2）时可能存在的虚拟化类型
        $timeType = $params['type_time'] ?? ''; // 时间类型 1开始时间 2结束时间
        $startTime = $params['start_time'] ?? ''; // 开始时间
        $endTime = $params['end_time'] ?? '';     // 结束时间
        $nodeuuid = $params['node_uuid'] ?? ''; // 节点uuid
        $userName = v1_escape_wildcard($params['user_name']); // 创建者
        $otherHostName = v1_escape_wildcard($params['other_host_name']); // 主机名/IP/别名
        $storageuuid = $params['storage_uuid'] ?? ''; // 存储设备uuid
        $noSureBackupFlag = $params['no_surebackup_flag'] ?? false; // 是否不查询验证历史任务信息

        $taskType = $params['job_type'] ? explode(',', trim($params['job_type'])) : ''; // 任务类型 支持以英文逗号隔开的多个
        $errorCode = $params['job_status'] ? explode(',', trim($params['job_status'])) : ''; // 任务状态 支持以英文逗号隔开的多个

        $errorCodeSql = !empty($errorCode) ? $this->getJobStatusSql($errorCode) : '';

        $moduleType = $params['module_type'] ? explode(',', trim($params['module_type'])) : []; // 模块类型 支持以英文逗号隔开的多个
        if ($params['sub_module_type'] === '0') {
            $subModuleType = explode(',', trim($params['sub_module_type']));
        } else {
            $subModuleType = $params['sub_module_type'] ?
                explode(',', trim($params['sub_module_type'])) : '';
        }

        $sql = " from bd_history_task bht ";
        $source = 'module_source_arr';
        $sureSouceModule = 'bht.' . $source; // 验证任务源的时间点的模块和子模块集合(,2-1,2-2,5-0,)
        $field = '(CASE bht.error_code WHEN 0 THEN 0 WHEN 45 THEN 1 WHEN 47 THEN 2 ELSE 3 END)
                        AS ERROR_STATUS,
                (CASE bht.task_type 
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'] . ' THEN 1
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'] . ' THEN 1
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] . ' THEN 1
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] . ' THEN 1
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['CDP_DB_RECOVERY'] . ' THEN 2
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'] . ' THEN 2
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'] . ' THEN 2
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY'] . ' THEN 2
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY_MOTION'] . ' THEN 7
                WHEN ' . xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY'] . ' THEN 8
                ELSE bht.task_type
                END ) AS JOB_TYPE';
        $fields = 'bht.id, bht.task_name, bht.module_type, bht.submodule_type, bht.task_type,
                bht.current_mode, bht.error_code, 
                bht.details, bht.total_object_size, bht.total_object_transport_size,
                bht.total_object_completed_size, bht.total_object_write_size, bht.average_speed,bht.user_uuid,
                unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                bht.user_name, bht.task_uuid, bht.history_uuid,' . $field . ',' . $sureSouceModule;
        $left = '';
        $sqlCount = "select count(bht.id) as total 
                        from bd_history_task bht";

        //此处作用是为了添加where，代替下面判断不确定的地方加where
        $sqls = [];
        $sqlParams = [];

        if ($noSureBackupFlag) {
            array_push($sqls, 'bht.task_type != ? ');
            $sqlParams = array_merge($sqlParams, array(xphp_get_config('task', 'TASKTYPE')['SURE_BACKUP']));
        }

        $user = xphp_get_user_info();

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['history_job']
            );
            $sqlNew = " bht.user_uuid in ({$userUuidSql}) ";
            array_push($sqls, $sqlNew);
        }

        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');

        $moduletype = xphp_get_config('module', 'MODULE_TYPE');

        if (count($moduleType) == 1) {
            if ($moduleType[0] == $moduletype['VM'] && !empty($hypervisor)) {
                //虚拟化类型
                $subModuleType = [$hypervisor];
            } elseif ($moduleType[0] == $moduletype['DB']) {
                //数据库类型
                if (!empty($dbType)) {
                    $subModuleType = [$dbType];
                } else {
                    $subModuleType = [];
                }
            }
        }

        $vmHypervisorArr = array_diff(
            xphp_get_config('vm', 'VMHYPERVISORTYPE'),
            xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'],
            xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']
        );
        $vmHypervisor = '(' . implode(',', $vmHypervisorArr) . ')';
        $publicCloudArr = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloud = '(' . implode(',', $publicCloudArr) . ')';
        $privateCloudArr = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];
        $privateCloud = '(' . implode(',', $privateCloudArr) . ')';

        //模块类型
        if (!empty($moduleType)) {
            $moduleTypeArr = implode(',', $moduleType);
            $buildsql = ' bht.module_type in (' . $moduleTypeArr . ') '; // 通用的子查询
            $buildsql2 = []; // 验证任务的子查询
            //如果是虚拟机模块则有sub_module_type判断，历史任务，日志，告警的submodule_type字段在数据库表示虚拟化类型
            $moduleConfig = xphp_get_config('module', 'MODULE_TYPE');
            $vmmoduleConfig = xphp_get_config('module', 'VM_SUB_MODULE');
            $buildsqls = []; // 通用的子模块查询
            foreach ($moduleType as $ikey => $itemi) {
                if (!empty($subModuleType)) {
                    $buildsql2[] =
                        $sureSouceModule . " like '%,{$itemi}-" . ($subModuleType[$ikey] ?? 0) . ",%'";
                    if ($itemi == $moduleConfig['VM']) {
                        switch ($subModuleType[$ikey]) {
                            case $vmmoduleConfig['VM']: // 虚拟机
                                $buildsqls[] = ' bht.submodule_type in ' . $vmHypervisor;
                                break;
                            case $vmmoduleConfig['PRIVATE_CLOUD']: // 私有云
                                $buildsqls[] = ' bht.submodule_type in ' . $privateCloud;
                                break;
                            case $vmmoduleConfig['PUBLIC_CLOUD']: // 公有云
                                $buildsqls[] = ' bht.submodule_type in ' . $publicCloud;
                                break;
                            default: // 有时是选取了虚拟化类型进行筛选，虚拟化类型存在submodule_type上
                                $buildsqls[] = ' bht.submodule_type in (' . $subModuleType[$ikey] . ')';
                                break;
                        }
                    } elseif ($itemi == $moduleConfig['DB']) { // 数据库模块特殊处理
                        if ($subModuleType[$ikey] ?? 0) { // 如果传了数据库类型，则拼接上submodule_type（db_type是存在submodule_type字段上的）
                            $buildsqls[] = ' (bht.submodule_type = ' . ($subModuleType[$ikey] ?? 0) . ' AND bht.module_type = ' . $itemi . ')';
                        } else { // 如果没传数据库类型，则只拼接module_type
                            $buildsqls[] = ' bht.module_type = ' . $itemi;
                        }
                    } else {
                        $buildsqls[] = ' (bht.submodule_type = ' . ($subModuleType[$ikey] ?? 0) . ' AND bht.module_type = ' . $itemi . ')';
                    }
                } else {
                    $buildsql2[] = $sureSouceModule . " like '%,{$itemi}-%,'";
                }
            }

            if (!empty($buildsqls)) {
                $buildsql .= ' and (' . implode(' or ', $buildsqls) . ')';
            }

            $buildsqlFinal = ' ((' . $buildsql . ') or ' . implode(' or ', $buildsql2) . ' )';

            array_push($sqls, $buildsqlFinal);
        }

        //私有云副本合并入虚拟机
        if (
            empty($taskType) && in_array(2, $moduleType) &&
            in_array('2', $subModuleType) && !in_array('1', $subModuleType)
        ) {
            array_push($sqls, ' bht.task_type != 17');
        }

        //任务类型
        if (!empty($taskType)) {
            if (empty($moduleType)) {
                // 默认备份恢复包含vm/fs/db/os/nas
                if (in_array($tasktypeArr['BACKUP'], $taskType)) {
                    $taskType = array_merge($taskType, [$tasktypeArr['DB_BACKUP'], $tasktypeArr['OS_BACKUP']]);
                }

                if (in_array($tasktypeArr['RECOVERY'], $taskType)) {
                    $taskType = array_merge($taskType, [$tasktypeArr['DB_RECOVERY'], $tasktypeArr['OS_RECOVERY']]);
                }
            }

            // $sqls .= ' and bht.task_type in (' . implode(',', $taskType) . ') ';
            array_push($sqls, 'bht.task_type in (' . implode(',', $taskType) . ')');
        }

        // 实时保护（整机/卷）复制容灾（整机/卷）
        if (!empty($params['dev_type'])) {
            $dev_types = explode(',', trim($params['dev_type']));
            $backup_dev_types = []; // 实时整机/卷类型
            $replication_dev_types = []; // 复制整机/卷类型

            foreach ($dev_types as $item) {
                $item = intval($item);
                if ($item <= 2) { // 1和2 为实时保护的整机和卷
                    $backup_dev_types[] = $item;
                } else { // 3 和 4 为实时复制的整机和卷
                    $replication_dev_types[] = $item - 2;
                }
            }

            if (!empty($backup_dev_types)) {
                $backup_dev_types = implode(',', $backup_dev_types);

                if (empty($taskType)) { // 没传任务类型时，排除掉 复制任务类型-65
                    $sqls[] = " bht.task_type NOT IN (65)";
                }
                $sqls[] = " bht.submodule_type in ({$backup_dev_types})";
            }

            if (!empty($replication_dev_types)) {
                $replication_dev_types = implode(',', $replication_dev_types);

                if (empty($taskType)) { // 没传任务类型时，默认给上 复制任务类型-65
                    $sqls[] = " bht.task_type = 65";
                }
                $sqls[] = " bht.submodule_type in ({$replication_dev_types})";
            }
        }

        //任务名
        if (!empty($taskName)) {
            // $sqls .= ' and bht.task_name like ? ';
            array_push($sqls, "bht.id in (SELECT id from bd_history_task where task_name like '%{$taskName}%')");
        }

        // 用户名
        if (!empty($userName)) {
            array_push($sqls, "bht.user_name like '%{$userName}%'");
        }

        //虚拟机名
        if (!empty($vmname)) {
            // $sqls .= ' and bht.details like ? ';
            array_push($sqls, "instr(bht.details, '" . $vmname . "')>0 ");
        }

        // 主机名/IP/别名
        if (!empty($otherHostName)) {
            $left = " inner join bd_agent ba on bht.details LIKE CONCAT('%\"agent_uuid\":\"', ba.agent_uuid, '\",\"%')
             and (ba.agent_name like '%{$otherHostName}%' 
            or ba.hostname like '%{$otherHostName}%' or ba.ip like '%{$otherHostName}%' )";
            $sql .= $left;
            $sqlCount .= $left;
        }
        //联查租户表
        if (empty($user['tenantuuid'])) {
            array_push($sqls, 'NOT EXISTS (
                SELECT 1 FROM mt_user_tenant mut WHERE mut.user_uuid = bht.user_uuid
            )');
        }
        //如果选择了节点
        if (!empty($nodeuuid)) {
            $left = " left join bd_task_alarm bta on bht.history_uuid = bta.history_uuid ";
            $sql .= $left;
            $sqlCount .= $left;
            // $sqls .= " and bta.node_uuid = ? and bht.history_uuid != ''";
            array_push($sqls, "bta.node_uuid = ? and bht.history_uuid != ''");
            array_push($sqlParams, $nodeuuid);
        }

        if (!empty($params['dev_type'])) {
            $left = ' left join cdp_vol_task cvt on bht.task_uuid = cvt.task_uuid ';
            $sql .= $left;
            $sqlCount .= $left;

            $devType = explode(',', trim($params['dev_type']));
            foreach ($devType as $items2) {
                $items2 = intval($items2);
                if ($items2 <= 2) {
                    // 实时保护
                    $s = "(bht.task_type = {$tasktypeArr['VOL_CDP_BACKUP']} and cvt.dev_type = " . $items2 . ')';
                } else {
                    // 复制
                    $s = "(bht.task_type = {$tasktypeArr['VOL_CDP_REPLICATION']}
                     and cvt.dev_type = " . ($items2 - 2) . ')';
                }

                array_push($sqls, $s);
            }
        }

        //如果选了错误，没有填错误码
        if (!empty($errorCodeSql)) {
            // $sqls .= ' and bht.error_code ' . $errorCodeSql;
            array_push($sqls, 'bht.error_code ' . $errorCodeSql);
        }

        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            if ($timeType == '1') {
                $key = 'bht.start_time';
            } else {
                $key = 'bht.finish_time';
            }
            // $sqls .= ' and ' . $key . ' between ? and ? ';
            array_push($sqls, $key . ' between ? and ?');
            array_push($sqlParams, $startTime);
            array_push($sqlParams, $endTime);
        }
        $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));

        $countkey = md5($sqls . json_encode($sqlParams));
        // $count = cache($countkey);
        // if (empty($count)) {
        //     $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);
        //     cache($countkey, $count, 60);
        // }

        $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $order = '';
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'job_name' => 'bht.task_name',
                'job_type_value' => 'JOB_TYPE, bht.current_mode',
                'module_type' => 'bht.module_type',
                'user_name' => 'bht.user_name',
                'all_size' => 'bht.total_object_size',
                'write_size' => 'bht.total_object_write_size',
                'start_time' => 'unix_timestamp(bht.start_time)',
                'finish_time' => 'unix_timestamp(bht.finish_time)',
                'job_status_value' => 'ERROR_STATUS',
            ];

            if (empty($sortArr[$params['sort']])) {
                $sort = ' bht.id desc';
            } else {
                if ($params['sort'] === 'job_type_value') { // 任务类型排序特殊处理
                    $sort = 'JOB_TYPE  ' . $sortType . ', bht.current_mode ' . $sortType . ', bht.id desc';
                } else {
                    $sort = $sortArr[$params['sort']] . ' ' . $sortType . ', bht.id desc';
                }
            }
            $order = " order by " . $sort;
        }
        $sqls .= $order;
        if (!$isExportAll) {
            $sqls .= ' limit ?, ?';
            $sqlParams = array_merge($sqlParams, array($start, $length));
        }

        $datas = $this->dbSelect('select distinct bht.id,' . $field . $sql . $sqls, $sqlParams);
        if (!empty($datas)) {
            $idArr = array_column($datas, 'id');
            $data = $this->dbSelect(
                'select ' . $fields . ' from bd_history_task bht
                 where bht.id in (' . implode(',', $idArr) . ')' . $order
            );
        } else {
            $data = [];
        }

        $records = [];
        foreach ($data as $d) {
            $details = json_decode($d['details'], true);
            $details = $details ?: [];
            $transportSize = $this->getTransportSize(
                $d['total_object_transport_size'],
                $d['total_object_completed_size'],
                intval($d['module_type']),
                $details
            );
            $transportSize = v1_calsize($transportSize, true);

            $writeSize = $this->getTotalWriteSize($details, $d['module_type'], $d['total_object_write_size']);
            $allSize = v1_calsize($d['total_object_size'], true);
            //验证任务没有大小
            if (intval($d['task_type']) == $tasktypeArr['SURE_BACKUP']) {
                $allSize = $validSize = $transportSize = $writeSize = xphp_get_config('app', 'NULLSPACE');
            } else {
                $validSize = $this->getTotalValidSize(
                    is_array($details) ? $details : array(),
                    //                    $details ?? [],
                    intval($d['module_type']),
                    intval($d['task_type']),
                    intval($d['total_object_size'])
                );
            }

            if (
                in_array(
                    $d['task_type'],
                    [
                        $tasktypeArr['BACKUP_COPY'],
                        $tasktypeArr['BACKUP_COPY_FETCH'],
                        $tasktypeArr['ARCHIVE'],
                        $tasktypeArr['ARCHIVE_FETCH']
                    ]
                ) && $d['module_type'] == $moduletype['VM']
            ) {
                $details = json_decode($d['details'], true);
                $submoduletype = intval($details[0]['hypervisor_type']);
            }


            $moduletypeAlis = $d['module_type'];
            if ($moduletypeAlis == $moduletype['VM']) {
                if (in_array($d['submodule_type'], $privateCloudArr)) {
                    $d['submodule_type'] = 2;
                } elseif (in_array($d['submodule_type'], $publicCloudArr)) {
                    $d['submodule_type'] = 3;
                } elseif (in_array($d['submodule_type'], $vmHypervisorArr)) {
                    $d['submodule_type'] = 1;
                }
            }

            $submoduletypeAlis = $d['submodule_type'];
            if (!empty($d[$source])) {
                // 解析下数据验证的模块和子模块类型
                $sureModule = array_filter(explode(',', $d[$source]));
                $moduletypeAlis = [];
                $submoduletypeAlis = [];
                foreach ($sureModule as $items) {
                    $itemsArr = explode('-', $items);
                    $moduletypeAlis[] = $itemsArr[0] ?? 0;
                    $submoduletypeAlis[] = $itemsArr[1] ?? 0;
                }
                $moduletypeAlis = implode(',', $moduletypeAlis);
                $submoduletypeAlis = implode(',', $submoduletypeAlis);
            }

            $records[] = [
                'job_uuid' => $d['id'],
                'num' => ++$start,
                'job_name' => $d['task_name'],
                /*'module_type' => $this->getModuleNameHistory(
                    $d['module_type'],
                    $submoduletype,
                    $d['task_type']
                ), // 模块类型*/
                'module_type' => $this->getObjects(
                    $moduletypeAlis,
                    $submoduletypeAlis,
                    $d['dev_type'] ?? ''
                ), // 对象类型
                'module_type_value' => $moduletypeAlis,   // 模块类型 标识
                'sub_module_type_value' => $submoduletypeAlis,
                /*'job_type' => $this->getHistoryTaskType(
                    $d['task_type'],
                    $d['current_mode'],
                    $submoduletype
                ),  // 任务类型*/
                'job_type' => $this->getTaskNameString(
                    $moduletype,
                    $tasktypeArr,
                    $d['module_type'],
                    $d['task_type'],
                    ''
                ),  // 任务类型

                'job_type_value' => $d['task_type'],  // 任务类型 标识
                'user_uuid' => $d['user_uuid'],
                'user_name' => $d['user_name'],
                'all_size' => $allSize,
                'validate_size' => $validSize,
                'speed_size' => $transportSize,
                'write_size' => $writeSize,
                'start_time' => $this->parseDate($d['start_time']),
                'finish_time' => $this->parseDate($d['finish_time']),
                'job_status_value' => $d['error_code'],  // 任务状态
                'job_status' => $this->getHistoryJobResultDes($d['error_code']),  // 任务状态 标识
                'history_uuid' => $d['history_uuid'],
                'execution_duration' => $this->getExecutionDuration($d['start_time'], $d['finish_time'])
            ];
        }
        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 获取任务执行时长，格式化为 HH:MM:SS 形式
     * @param int $startTime
     * @param int $finishTime
     * @return void
     */
    protected function getExecutionDuration(int $startTime, int $finishTime): string
    {
        if ($startTime <= 0 || $finishTime <= 0 || $finishTime < $startTime) {
            return '00:00:00';
        }

        $duration = $finishTime - $startTime;

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        // 格式化为 HH:MM:SS
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * 获取单个任务的历史任务列表
     *  jobs_uuid
     * @param array $params jobs_uuid、offset、limit、sort、order
     * @return array
     */
    public function getJobHistory(array $params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $jobUuid = $params['jobs_uuid'];

        $sql = "select bht.id, bht.task_name, bht.module_type, bht.submodule_type, bht.task_type,
                bht.current_mode, bht.error_code, bht.details, bht.total_object_size, bht.total_object_transport_size,
                bht.total_object_completed_size, bht.total_object_write_size, bht.average_speed,
                unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time,
                bht.user_name, bht.task_uuid, bht.history_uuid
            from bd_history_task bht ";
        $sqlCount = "select count(bht.id) as total from bd_history_task bht  where bht.task_uuid = ?";

        //此处作用是为了添加where，代替下面判断不确定的地方加where
        $sqlParams = [$jobUuid];

        $count = $this->dbSelect($sqlCount, $sqlParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $historyId = $this->dbSelect('select id from bd_history_task bht where bht.task_uuid = ?', $sqlParams);
        if (empty($historyId)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $sql .= ' where bht.id in (' . implode(',', array_column($historyId, 'id')) . ')';
        $sqlParams = [];

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'job_name' => 'bht.task_name',
                'job_type' => 'bht.task_type',
                'user_name' => 'bht.user_name',
                'total_object_size' => 'bht.total_object_size',
                'total_object_write_size' => 'bht.total_object_write_size',
                'start_time' => 'bht.start_time',
                'finish_time' => 'bht.finish_time',
                'job_status' => 'bht.error_code',
                'all_size' => 'bht.total_object_size',
                'write_size' => 'bht.total_object_write_size',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' bht.id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', bht.id desc');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ?, ?';
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $tasktype = xphp_get_config('task', 'TASKTYPE');
        $records = [];
        foreach ($data as $d) {
            $details = json_decode($d['details'], true);
            $details = $details ?: [];
            $transportSize = $this->getTransportSize(
                $d['total_object_transport_size'],
                $d['total_object_completed_size'],
                intval($d['module_type']),
                $details
            );
            $transportSize = v1_calsize($transportSize, true);

            $writeSize = $this->getTotalWriteSize($details, $d['module_type'], $d['total_object_write_size']);

            //验证任务没有大小
            if (intval($d['task_type']) == $tasktype['SURE_BACKUP']) {
                $validSize = $transportSize = $writeSize = xphp_get_config('app', 'NULLSPACE');
            } else {
                $validSize = $this->getTotalValidSize(
                    is_array($details) ? $details : array(),
                    //                    $details,
                    intval($d['module_type']),
                    intval($d['task_type']),
                    intval($d['total_object_size'])
                );
            }

            $records[] = [
                'job_uuid' => $d['task_uuid'],
                'num' => ++$start,
                'job_name' => $d['task_name'],
                'job_type' => $this->getHistoryTaskType(
                    $d['task_type'],
                    $d['current_mode'],
                    $d['submodule_type']
                ),  // 任务类型
                'job_type_value' => $d['task_type'],  // 任务类型 标识
                'user_name' => $d['user_name'],
                'all_size' => v1_calsize($d['total_object_size'], true),
                'validate_size' => $validSize,
                'speed_size' => $transportSize,
                'write_size' => $writeSize,
                'start_time' => $this->parseDate($d['start_time']),
                'finish_time' => $this->parseDate($d['finish_time']),
                'job_status_value' => $d['error_code'],  // 任务状态
                'job_status' => $this->getHistoryJobResultDes($d['error_code']),  // 任务状态 标识
                'history_uuid' => $d['history_uuid'],
                'detail' => $this->getHistoryDetail($d['id']),
                'job_id' => $d['id'],
            ];
        }
        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 获取每个小时任务占用个数列表
     * @param array $params 请求参数
     * @return array
     */
    public function getTimeCrowdList($params = []): array
    {

        $sqlHour = "SELECT distinct timetable.hour Hour, ifnull(sumtable.count, 0) Count FROM 
                    (SELECT 0 hour UNION ALL SELECT 1 hour 
                    UNION ALL SELECT 2 hour 
                    UNION ALL SELECT 3 hour 
                    UNION ALL SELECT 4 hour 
                    UNION ALL SELECT 5 hour 
                    UNION ALL SELECT 6 hour 
                    UNION ALL SELECT 7 hour 
                    UNION ALL SELECT 8 hour 
                    UNION ALL SELECT 9 hour 
                    UNION ALL SELECT 10 hour 
                    UNION ALL SELECT 11 hour 
                    UNION ALL SELECT 12 hour 
                    UNION ALL SELECT 13 hour 
                    UNION ALL SELECT 14 hour 
                    UNION ALL SELECT 15 hour 
                    UNION ALL SELECT 16 hour 
                    UNION ALL SELECT 17 hour 
                    UNION ALL SELECT 18 hour 
                    UNION ALL SELECT 19 hour 
                    UNION ALL SELECT 20 hour 
                    UNION ALL SELECT 21 hour 
                    UNION ALL SELECT 22 hour 
                    UNION ALL SELECT 23 hour) timetable 
                    LEFT JOIN( 
                    SELECT hour(start_time) hour, count(id) count from 
                    bd_time_strategy group by hour) sumtable ON timetable.hour = sumtable.hour ORDER BY hour asc";
        $dataHour = $this->dbSelect($sqlHour);
        $hourInfo = [];
        foreach ($dataHour as $hour) {
            $hourInfo[] = array(
                'hour' => $hour['Hour'],
                'num' => intval($hour['Count']),
                'date' => $hour['Hour'] . ':00:00' . ' ~ ' . $hour['Hour'] . ':59:59'
            );
        }

        //租户内不显示任务个数描述
        $user = xphp_get_user_info();
        $showFlag = empty($user['tenantUuid']);

        return [
            'time_list' => $hourInfo,
            'suggest_time' => $this->getSuggestTime([]),
            'show_flag' => $showFlag
        ];
    }

    /**
     * 返回任务建议执行时间
     * @param array $params 参数
     * @return array
     */
    public function getSuggestTime($params = []): array
    {
        $time = '';
        //先检测小时
        $sqlHour = "SELECT timetable.hour Hour, ifnull(sumtable.count, 0) Count FROM 
(SELECT 0 hour UNION ALL SELECT 1 hour UNION ALL SELECT 2 hour 
UNION ALL SELECT 3 hour UNION ALL SELECT 4 hour UNION ALL SELECT 5 hour 
UNION ALL SELECT 6 hour UNION ALL SELECT 7 hour UNION ALL SELECT 8 hour 
UNION ALL SELECT 9 hour UNION ALL SELECT 10 hour UNION ALL SELECT 11 hour 
UNION ALL SELECT 12 hour UNION ALL SELECT 13 hour UNION ALL SELECT 14 hour 
UNION ALL SELECT 15 hour UNION ALL SELECT 16 hour UNION ALL SELECT 17 hour 
UNION ALL SELECT 18 hour UNION ALL SELECT 19 hour UNION ALL SELECT 20 hour 
UNION ALL SELECT 21 hour UNION ALL SELECT 22 hour UNION ALL SELECT 23 hour) timetable 
LEFT JOIN( SELECT hour(start_time)  hour, count(id) count from 
bd_time_strategy group by date_format(start_time, '%H'), hour) sumtable 
ON timetable.hour = sumtable.hour ORDER BY count asc, hour asc";
        $dataHour = $this->dbSelect($sqlHour);
        $hourInfo = array();
        foreach ($dataHour as $hour) {
            if ($hour['Count'] == $dataHour[0]['Count']) {
                $hourInfo[] = $hour['Hour'];
            }
        }
        //随机取最低消耗其中一个
        $hourIndex = array_rand($hourInfo, 1);  // bug#28793 array_rand返回随机数的 索引!!!
        $hour = $hourInfo[$hourIndex];

        //继续检测分钟
        $sqlMinute = "SELECT timetable.minute Minute, ifnull(sumtable.count, 0) Count FROM 
(SELECT 0 minute UNION ALL SELECT 1 minute UNION ALL SELECT 2 minute UNION ALL SELECT 3 minute 
UNION ALL SELECT 4 minute UNION ALL SELECT 5 minute UNION ALL SELECT 6 minute 
UNION ALL SELECT 7 minute UNION ALL SELECT 8 minute UNION ALL SELECT 9 minute 
UNION ALL SELECT 10 minute UNION ALL SELECT 11 minute UNION ALL SELECT 12 minute 
UNION ALL SELECT 13 minute UNION ALL SELECT 14 minute
                    UNION ALL SELECT 15 minute UNION ALL SELECT 16 minute UNION ALL SELECT 17 minute 
                    UNION ALL SELECT 18 minute UNION ALL SELECT 19 minute UNION ALL SELECT 20 minute 
                    UNION ALL SELECT 21 minute UNION ALL SELECT 22 minute UNION ALL SELECT 23 minute 
                    UNION ALL SELECT 24 minute UNION ALL SELECT 25 minute UNION ALL SELECT 26 minute 
                    UNION ALL SELECT 27 minute UNION ALL SELECT 28 minute UNION ALL SELECT 29 minute
                    UNION ALL SELECT 30 minute UNION ALL SELECT 31 minute UNION ALL SELECT 32 minute 
                    UNION ALL SELECT 33 minute UNION ALL SELECT 34 minute UNION ALL SELECT 35 minute 
                    UNION ALL SELECT 36 minute UNION ALL SELECT 37 minute UNION ALL SELECT 38 minute 
                    UNION ALL SELECT 39 minute UNION ALL SELECT 40 minute UNION ALL SELECT 41 minute 
                    UNION ALL SELECT 42 minute UNION ALL SELECT 43 minute UNION ALL SELECT 44 minute
                    UNION ALL SELECT 45 minute UNION ALL SELECT 46 minute UNION ALL SELECT 47 minute 
                    UNION ALL SELECT 48 minute UNION ALL SELECT 49 minute UNION ALL SELECT 50 minute 
                    UNION ALL SELECT 51 minute UNION ALL SELECT 52 minute UNION ALL SELECT 53 minute 
                    UNION ALL SELECT 54 minute UNION ALL SELECT 55 minute UNION ALL SELECT 56 minute 
                    UNION ALL SELECT 57 minute UNION ALL SELECT 58 minute UNION ALL SELECT 59 minute
                    ) timetable LEFT JOIN( 
                    SELECT minute(start_time)  minute, count(id) count from 
                    bd_time_strategy group by date_format(start_time, '%i'), minute) sumtable 
ON timetable.minute = sumtable.minute  ORDER BY count asc, minute asc";
        $dataMinute = $this->dbSelect($sqlMinute);
        $minuteInfo = array();
        foreach ($dataMinute as $minute) {
            if ($minute['Count'] == $dataMinute[0]['Count']) {
                $minuteInfo[] = $minute['Minute'];
            }
        }
        //随机取最低消耗其中一个
        $minuteIndex = array_rand($minuteInfo, 1);  // bug#28793 array_rand返回随机数的 索引!!!
        $minute = $minuteInfo[$minuteIndex];

        $timestamp = intval($hour) * 3600 + intval($minute) * 60;
        $endtimestamp = $timestamp + 30 * 60;
        $rollendstamp = $timestamp + 60 * 60;
        // 之前采用date格式化时间，但date针对的是时间戳，而v1_sec_to_time针对的是一天的秒数
        return [
            // 'start_time' => date('H:i:s', $timestamp),  // 保留之前的代码
            // 'end_time' => date('H:i:s', $endtimestamp),
            // 'roll_end_time' => date('H:i:s', $rollendstamp)
            'start_time' => v1_sec_to_time($timestamp),
            'end_time' => v1_sec_to_time($endtimestamp),
            'roll_end_time' => v1_sec_to_time($rollendstamp)
        ];
    }

    /**
     * 初始化策略选择下拉框
     * @param int $type 类型
     * @return array
     */
    public function getStrategySelect(int $type): array
    {

        $info = array();
        $sql = "select strategy_group_uuid, strategy_group_name, extra_info from 
                     bd_strategy_group where strategy_group_type = ? and user_uuid = ? order by create_time desc";
        $user = xphp_get_user_info();
        $sqlParams = array($type, $user['userUuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info[] = array(
            'uuid' => '',
            'text' => xphp_get_lang('UI_GLOBAL_STRATEGY_AUTO'),
            'strategy' => []
        );
        if (!empty($data)) {
            foreach ($data as $d) {
                $info[] = [
                    'uuid' => $d['strategy_group_uuid'],
                    'text' => $d['strategy_group_name'],
                    'strategy' => json_decode($d['extra_info'], true)
                ];
            }
        }

        return $info;
    }

    /**
     * 获取时间点对应任务名
     * @param string $taskUuid 任务uuid
     * @param string $taskName 任务名称
     * @return string
     */
    public function getTimepointTaskname(string $taskUuid, string $taskName): string
    {
        $sql = "select task_name from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (!empty($data)) {
            $name = $data[0]['task_name'];
        } else {
            $name = $taskName;
        }

        return $name;
    }

    /**
     * 处理下历史任务的任务状态搜索条件
     * 1成功 2中止 3异常 4 失败
     * @param array $jobStats 状态数组
     * @return string
     */
    private function getJobStatusSql(array $jobStats)
    {
        $arr = [
            1 => [0],
            2 => [45],
            3 => [47],
            4 => [0, 45, 47], // 不在这个里面
        ];
        $arr1 = $arr2 = [];
        foreach ($jobStats as $item) {
            if ($item == 4) {
                $arr2 = array_merge($arr2, $arr[$item]);
            } else {
                $arr1 = array_merge($arr1, $arr[$item]);
            }
        }
        // 最后中和下arr1和arr2的元素
        if (!empty($arr2)) {
            // 存在not in
            $arrs = array_diff($arr2, $arr1); // 去除in的
            return !empty($arrs) ? ' not in (' . implode(',', $arrs) . ')' : '';
        }
        return !empty($arr1) ? ' in (' . implode(',', $arr1) . ')' : '';
    }

    /**
     * 获取历史任务列表的展示详情
     * @param string $taskUuid 任务uuid
     * @return array|bool
     */
    private function getHistoryDetail(string $taskUuid)
    {
        $sql = "SELECT 
                    bht.id, bht.task_name, bht.module_type, bht.submodule_type, 
                    bht.task_type, bht.current_mode, bht.error_code, bht.details, bht.total_object_size, 
                    bht.total_object_transport_size, bht.total_object_completed_size, bht.total_object_write_size, bht.average_speed,
                    unix_timestamp(bht.start_time) AS start_time, unix_timestamp(bht.finish_time) AS finish_time,
                    bht.user_name, bht.task_uuid, bht.history_uuid, bht.error_detail
                FROM 
                    bd_history_task bht 
                WHERE
                    bht.id = ?";

        $data = $this->dbSelect($sql, [$taskUuid]);

        if (empty($data)) {
            return false;
        }

        $details = json_decode($data[0]['details'], true);
        if (empty($details)) {
            return false;
        }

        $dataH = $data[0];
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $subModuleType = intval($dataH['submodule_type']);
        $re = $this->historyTaskDetailsHandler($details, $dataH, $data[0]['error_detail']);
        if (
            (
                $dataH['task_type'] == $taskType['BACKUP_COPY'] ||
                $dataH['task_type'] == $taskType['BACKUP_COPY_FETCH'] ||
                $dataH['task_type'] == $taskType['ARCHIVE'] ||
                $dataH['task_type'] == $taskType['ARCHIVE_FETCH']
            ) &&
            $dataH['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['VM']
        ) {
            $details = json_decode($dataH['details'], true);
            $subModuleType = intval($details[0]['hypervisor_type']);
        }

        $re['info']['sub_module_type_value'] = $this->getVmSubModuleType($subModuleType);
        if ($dataH['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['FS']) {
            $re['info']['sub_module_type_value'] = $subModuleType;
        }

        //处理数据验证
        if ($dataH['task_type'] == $taskType['SURE_BACKUP']) {
            $re['info']['job_type_value'] = $dataH['task_type'];
            $sql = "select extension_info, report_uuid from sr_surebackup_report where history_uuid = ?";
            $data = $this->dbSelect($sql, array($dataH['history_uuid']));
            $verifyType = 1;    //默认手动验证
            foreach ($data as $d) {
                if (!empty($d['extension_info'])) {
                    $info = json_decode($d['extension_info'], true);
                    $verifyType = intval($info['automatic_verify_flag']);
                }
            }
            $re['info']['verify_type'] = $verifyType; //验证类型：自动验证|手动验证
            $re['info']['report_uuid'] = $data[0]['report_uuid'];
        }

        return $re;
    }

    /**
     * 获取模块名称 得到模块的详细描述信息,主要是虚拟机模块要得到子模块号信息
     * 限速策略分发会获取模块名称
     * @param array $params 任务列表数组元素
     * @return string
     */
    public function getModuleName($params = []): string
    {
        $ptDes = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $moduleTypeDes = $ptDes[$params['module_type']];
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        $submoduletype = xphp_get_config('module', 'SUBMODULE_TYPE');
        $ossubmoduletype = xphp_get_config('module', 'OS_SUBMODULE_TYPE');
        $tasktype = xphp_get_config('task', 'TASKTYPE');
        if ($params['module_type'] == $moduletype['VM']) {
            $hypervisor = $params['module_type_alias'];
            //数据验证任务
            if ($params['task_type'] == $tasktype['SURE_BACKUP']) {
                $hypervisor = $params['task_type_alias'];
            }
            $vmsubmoduletype = xphp_get_config('module', 'VM_SUB_MODULE');

            if ($params['sub_module_type'] == $vmsubmoduletype['PRIVATE_CLOUD']) {
                $moduleTypeDes = xphp_get_lang('WEB_PLATFORM_DES_PRIVATE_CLOUD');
            }
            if ($params['sub_module_type'] == $vmsubmoduletype['PUBLIC_CLOUD']) {
                $moduleTypeDes = xphp_get_lang('WEB_PLATFORM_DES_PUBLIC_CLOUD');
            }
        } elseif ($params['module_type'] == $moduletype['BACKUP_COPY_CLIENT']) {
            if (in_array($params['task_type'], [$tasktype['BACKUP_COPY'], $tasktype['BACKUP_COPY_FETCH']])) {
                //副本
                $moduleTypeDes = xphp_get_lang('WEB_PLATFORM_DES_COPY');
            } elseif (in_array($params['task_type'], [$tasktype['ARCHIVE'], $tasktype['ARCHIVE_FETCH']])) {
                //归档
                $moduleTypeDes = xphp_get_lang('UI_PLATFORM_ARCHIVE');
            }
        } elseif ($params['module_type'] == $moduletype['FS']) {
            if ($params['sub_module_type'] == $submoduletype['NAS']) {
                $moduleTypeDes = xphp_get_lang('WEB_PLATFORM_DES_NAS');
            }
            if ($params['sub_module_type'] == $submoduletype['HADOOP']) {
                $moduleTypeDes = xphp_get_lang('UI_PLATFORM_HADOOP');
            }
            if ($params['sub_module_type'] == $submoduletype['OBS']) {
                $moduleTypeDes = xphp_get_lang('WEB_PLATFORM_DES_OBS');
            }
        } elseif ($params['module_type'] == $moduletype['OS']) {
            if ($params['sub_module_type'] == $ossubmoduletype['OS']) {
                // $moduleTypeDes = $ptDes[$params['module_type']];
            } else {
                //定时整机
                $moduleTypeDes = xphp_get_lang('UI_COMPLETE_MACHINE');
            }
        } elseif ($params['module_type'] == $moduletype['KUBERNETES']) {
            //定时整机
            $moduleTypeDes = xphp_get_lang('UI_PLATFORM_K8S');
        } elseif ($params['module_type'] == $moduletype['FILE_COPY']) {
            //文件复制
            $moduleTypeDes = xphp_get_lang('UI_PLATFORM_FILES');
        }
        return $moduleTypeDes ?? '';
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

    /**
     * 当前任务中的任务类型修改部分描述,在任务中的当前任务虚拟机  文件  数据库中用
     * 限速策略分发任务会用到
     * @param array $moduleType 所有模块列表
     * @param array $taskType   所有任务类型
     * @param int   $moduletype 模块类型
     * @param int   $tasktype   任务类型
     * @return string
     */
    public function getTaskNameString(array $moduleType, array $taskType, int $moduletype, int $tasktype, $standbyAgentUuid = ''): string
    {

        $moduleTypeArr = [
            $moduleType['DB'] => [ // 如果为数据库
                $taskType['DB_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $taskType['DB_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OEM_DBCDP'] => [ // 如果为数据库实时
                $taskType['DB_CDP_BACKUP'] => 'CDP',
                $taskType['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OEM_FSCDP'] => [
                $taskType['FILE_CDP_BACKUP'] => ('WEB_PLATFORM_DES_REAL_TIME_SYN'),
                $taskType['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OS'] => [
                $taskType['OS_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $taskType['OS_INSTANT_RECOVERY'] => ('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
                $taskType['OS_INSTANT_RECOVERY_MOTION'] => ('WEB_PLATFORM_DES_MOTION'),
            ],
            $moduleType['DB_CDP'] => [
                $taskType['CDP_DB_BACKUP'] => ('WEB_PLATFORM_REPLICATION'),
                $taskType['CDP_DB_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['VOL_CDP'] => [
                $taskType['VOL_CDP_BACKUP'] => ('UI_CLIENT_CDP_BACKUP'),
                $taskType['VOL_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
                $taskType['VOL_CDP_TAKEOVER'] => ('WEB_PLATFORM_DES_TAKEOVER'),
                $taskType['VOL_CDP_REPLICATION'] => ('WEB_PLATFORM_REPLICATION'),
            ]
        ];

        $des = !empty($moduleTypeArr[$moduletype][$tasktype]) ? xphp_get_lang($moduleTypeArr[$moduletype][$tasktype]) : (xphp_get_desc('Pf', 'TASKTYPEDES')[$tasktype] ?? '');

        return $des;
    }

    /**
     * 得到各模块的显示描述
     * @param int $moduleType    模块类型
     * @param int $subModuleType 子模块
     * @param int $taskType      任务类型
     * @return string
     */
    private function getModuleNameHistory(int $moduleType, $subModuleType, int $taskType)
    {

        $pfdes = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $moduleTypeDes = $pfdes[$moduleType];
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        if ($moduleType == $moduleTypeArr['VM']) {
            $hypervisor = intval($subModuleType);
            $moduleTypeDes = xphp_get_config('vm', 'VMHYPERVISORDES')[$hypervisor];
        } elseif ($moduleType == $moduleTypeArr['BACKUP_COPY_CLIENT']) {
            $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
            if (
                inArray($taskType, [
                    $taskTypeArr['BACKUP_COPY'],
                    $taskTypeArr['BACKUP_COPY_FETCH'],
                    $taskTypeArr['FILE_BACKUP_COPY'],
                    $taskTypeArr['FILE_BACKUP_COPY_FETCH'],
                    $taskTypeArr['DB_BACKUP_COPY'],
                    $taskTypeArr['DB_BACKUP_COPY_FETCH'],
                    $taskTypeArr['NAS_BACKUP_COPY'],
                    $taskTypeArr['NAS_BACKUP_COPY_FETCH']
                ])
            ) {
                //副本
                $moduleTypeDes = xphp_get_lang('WEB_PLATFORM_DES_COPY');
            } elseif (inArray($taskType, [$taskTypeArr['ARCHIVE'], $taskTypeArr['ARCHIVE_FETCH']])) {
                //归档
                $moduleTypeDes = xphp_get_lang('UI_PLATFORM_ARCHIVE');
            }
        } elseif ($moduleType == $moduleTypeArr['FS']) {
            $submoduleType = intval($subModuleType);
            if ($submoduleType == xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']) {
                $moduleTypeDes = xphp_get_lang('UI_PLATFORM_HADOOP');
            } elseif ($submoduleType == xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']) {
                $moduleTypeDes = xphp_get_lang('UI_PLATFORM_OBS');
            }
        }
        return $moduleTypeDes;
    }

    /**
     * 得到历史任务结果描述
     * @param int $errorCode 错误码
     * @see \app\v1\backupmanager\v0\logic\ReportInfo::getTaskReportData
     * @return string
     */
    public function getHistoryJobResultDes(int $errorCode)
    {
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_NOT_INIT_ERROR',
            'BD_TASK_BE_CANCELLED_ERROR'
        );
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
     * 检测密码正确性
     * @param array $params 参数
     * @return array|string
     */
    public function checkEncryptPass(array $params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('UI_JOB_VERIFY_PASSWORD_SUCCESS'),
            'data' => array(),
        );
        $timepointuuid = $params['timepoint_uuid'];
        $inputpass = base64_decode($params['password']);

        $sqlencrypt = "select encrypted_flag,timepoint, detail from bd_backup_timepoint where timepoint_uuid  = ? ";
        $sqlParamsencrypt = array($timepointuuid);
        $dataencrypt = $this->dbSelect($sqlencrypt, $sqlParamsencrypt);//password_auto_flag, password
        $dataencrypt = $dataencrypt[0];
        $detail = json_decode($dataencrypt['detail'], true);

        if ($dataencrypt['encrypted_flag'] == 1 && intval($detail['password_auto_flag']) == 2) {//加密开 自动生成密码关
            $inputpass = v1_pt_pass_encrypt($inputpass);//htmlspecialchars_decode把特殊字符转成原来的字符
            if ($detail['password'] != $inputpass) {//数据库中的密码不等于页面输入密码
                $resultInfo['success'] = false;
                $resultInfo['message'] = xphp_get_lang('UI_JOB_VERIFY_PASSWORD_FAILURE_TIP1') . $dataencrypt['timepoint'] . xphp_get_lang('UI_JOB_VERIFY_PASSWORD_FAILURE_TIP2');
                return $resultInfo;
            }
        }
        return $resultInfo;
    }

    /**
     * 获取挂起任务列表
     * @param array $params 请求参数
     * @return array
     */
    public function getPending(array $params): array
    {

        $taskName = v1_escape_wildcard($params['job_name'] ?: $params['search']); // 任务名称
        $startTime = $params['start_time'] ?? ''; // 开始时间
        $endTime = $params['end_time'] ?? '';     // 结束时间
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];

        $where = '';
        $sqlParams = [];
        //任务名
        if (!empty($taskName)) {
            $where .= ' and bt.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $taskName . '%'));
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['current_job']
            );
            $sqlNew = " and bt.user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            $where .= ' and bpt.add_time between ? and ? ';
            $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        }

        $sqlCont = 'select count(bpt.id) as num FROM
                bd_task bt,bd_pending_task bpt 
            WHERE
                bt.task_uuid = bpt.task_uuid ' . $where;

        $total = $this->dbSelect($sqlCont, $sqlParams);
        if (empty($total) || empty($total[0]['num'])) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $where .= ' GROUP BY bpt.task_uuid order by bpt.priority asc,bpt.add_time asc ';
        $sql = "SELECT bpt.*,
                    bt.module_type,bt.sub_module_type,bt.task_name,bt.task_type,bt.task_status,
                    bt.stage_percent,bt.current_stage, birt.migrate_phase,cvt.dev_type,
                    GROUP_CONCAT(sbt.module_type SEPARATOR ',') AS sure_module_types,
                    GROUP_CONCAT(sbt.submodule_type SEPARATOR ',') AS sure_sub_module_types 
                FROM bd_task bt,bd_pending_task bpt
                left join bd_instant_recovery_task birt on birt.task_uuid = bpt.task_uuid
                LEFT JOIN sr_surebackup_item sbt ON sbt.sr_task_uuid = bpt.task_uuid
                left join cdp_vol_task cvt on bpt.task_uuid = cvt.task_uuid
                WHERE bt.task_uuid = bpt.task_uuid " . $where;

        if (!empty($limit)) {
            $sql .= ' limit ?, ?';
            $sqlParams = array_merge($sqlParams, array($offset, $limit));
        }

        $data = $this->dbSelect($sql, $sqlParams);

        $records = [];
        $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $pendingType = xphp_get_config('task', 'PENDING_TYPE');
        foreach ($data as $d) {
            $moduletype = !empty($d['sure_module_types']) ? $d['sure_module_types'] : $d['module_type'];
            $submoduletype = !empty($d['sure_module_types']) ? $d['sure_sub_module_types'] : $d['sub_module_type'];
            // 处理下阶段的显示
            if ($d['task_type'] == $taskType['INSTANT_RECOVERY_MOTION']) {
                // 如果是迁移任务，那么阶段是只有2个，并且没得进度的
                $stageArr = xphp_get_config('task', 'TASK_STAGE_MOTION');
                $currentstage = $d['migrate_phase'] == 0 ? xphp_get_config('app', 'NULLSPACE') :
                    xphp_get_lang($stageArr[$d['migrate_phase']]);
            } else {
                if ($d['module_type'] == $moduleType['VOL_CDP']) {
                    // 卷实时
                    $stageArr = xphp_get_config('task', 'REAL_PROTECT_STAGE');
                } elseif ($d['module_type'] == $moduleType['DB_CDP']) {
                    // 数据库实时
                    $stageArr = xphp_get_config('task', 'DB_CDP_PROTECT_STAGE');
                } else {
                    $stageArr = xphp_get_config('task', 'COMMON_STAGE');
                }
                $currentstage = !empty($stageArr[$d['current_stage']]) ?
                    xphp_get_lang($stageArr[$d['current_stage']]) : xphp_get_config('app', 'NULLSPACE');
            }
            if ($d['stage_percent'] != -1) {
                $currentstage .= '(' . $d['stage_percent'] . '%)';
            }

            $record = array(
                'job_uuid' => $d['task_uuid'], // 任务 uuid
                'job_name' => $d['task_name'],  // 任务名称
                //'module_type' => $this->getModuleName($d), // 模块类型
                'module_type' => $this->getObjects(
                    $moduletype,
                    $submoduletype,
                    $d['dev_type'] ?? ''
                ), // 模块类型
                'module_type_value' => $moduletype,   // 模块类型 标识
                'sub_module_type_value' => $submoduletype, // 子模块类型：1虚拟化，2私有云，3公有云
                'job_type' => $this->getTaskNameString(
                    $moduleType,
                    $taskType,
                    $d['module_type'],
                    $d['task_type'],
                    $d['standby_agent_uuid']
                ),  // 任务类型
                'job_type_value' => $d['task_type'],  // 任务类型 标识
                'create_time' => $d['add_time'], // 挂起时间
                'job_status_value' => $d['task_status'],  // 任务状态
                'job_status' => $ptDes[$d['task_status']],  // 任务状态 标识
                'duration_time' => v1_sec_to_day_time(TIMESTAMP - strtotime($d['add_time'])), // 持续时间
                'current_stage' => $d['current_stage'], // 任务阶段
                'stage_percent' => $d['stage_percent'], // 任务百分比
                'current_stage_value' => $currentstage, // 任务阶段+任务百分比
                'business_type' => $this->getObject($d['module_type'], $d['task_type']),
                'priority' => $d['priority'], // 优先级
                'pending_type' => $d['pending_type'], // 挂起原因标识
                // 挂起原因
                'pending_msg' =>
                    $pendingType[$d['pending_type']] ? xphp_get_lang($pendingType[$d['pending_type']]) : '--',
            );
            $records[] = $record;
        }
        $page = $params['offset'] / $params['limit'] + 1;
        // 缓存本次请求的第一个优先级
        xphp_set_cache('pending_job_page' . $page, $records[0]['priority']);
        return [
            'rows' => $records,
            'total' => $total[0]['num']
        ];
    }

    /**
     * 根据 moduleType 和 subModuleType 获取对应的中文对象名称
     *
     * @param string|int|array $moduleType    模块类型（多个用逗号分隔）
     * @param string|int|array $subModuleType 模块类型（多个用逗号分隔）
     * @param int              $devType       设备类型（用于 VOL_CDP 类型区分卷/整机）
     * @return string 匹配的对象名称，多个用逗号连接
     */
    public function getObjects($moduleType, $subModuleType = '', $devType = 0)
    {
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        if ($moduleType == $moduleArr['VOL_CDP']) {
            // 是实时，那么根据 devType 来判断
            return $devType == 1 ?
                xphp_get_lang('UI_PLATFORM_CDP_REEL_BACKUP') : xphp_get_lang('UI_PLATFORM_CDP_COMPLETE_BACKUP');
        } elseif ($moduleType == $moduleArr['DB']) {  // 数据库模块不需要判断sub_module_type
            return xphp_get_lang('UI_PLATFORM_DATABASE');
        }

        $moduleArrs = explode(',', trim($moduleType, ','));
        $subModuleArrs = explode(',', trim($subModuleType, ','));

        // 如果 moduleArrs 为空，直接返回空
        if (empty($moduleArrs)) {
            return '';
        }

        // 根据module_type和sub_module_type返回对应的对象信息
        $objectArr = [
            '2-0' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2-1' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2-2' => ('UI_PLATFORM_PRIVATE_CLOUD'), // 私有云
            '2-3' => ('UI_PLATFORM_PUBLIC_CLOUD'), // 公有云
            '5-0' => ('UI_PLATFORM_MACHINE_REEL_BACKUP'), // 定时卷（数据库中submodule_type存的0）
            '5-1' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 定时整机
            '5-2' => ('UI_PLATFORM_MACHINE_REEL_BACKUP'), // 定时卷（兼容前端过滤筛选）
            '11-0' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '11-2' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '3-1' => ('UI_PLATFORM_FILES'),// 文件系列 文件
            '3-3' => ('UI_PLATFORM_HADOOP_HDFS'),// 文件系列 HADOOP
            '3-4' => ('UI_PLATFORM_OBS_STORAGE'),// 文件系列 OBS
            '4-0' => ('UI_PLATFORM_DATABASE'), // 数据库
            '14-0' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '14-1' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '28-0' => ('UI_PLATFORM_K8S'), // 容器
            '26-0' => ('UI_PLATFORM_FILES'), // 文件复制 文件
            '12-0' => ('UI_PLATFORM_DATABASE'), // 数据库复制 数据库
            '10-0' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 实时 需要根据 dev_type 区分，备份类型 1卷 2整机
            '10-2' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 实时保护整机的数据验证任务
            '10-3' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 复制容灾整机的数据验证任务
            '10000-0' => ('UI_PLATFORM_DATABASE'), // 友商数据库
        ];

        $result = [];
        foreach ($moduleArrs as $key => $item) {
            $subKey = !isset($subModuleArrs[$key]) ? 0 : $subModuleArrs[$key];
            $items = $item . '-' . $subKey;
            if (isset($objectArr[$items])) {
                $result[] = xphp_get_lang($objectArr[$items]);
            }
        }

        // 去重 + 合并成字符串
        $result = array_unique($result);
        return implode(',', $result);
    }
}
