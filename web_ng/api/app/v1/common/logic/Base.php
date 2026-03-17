<?php

namespace app\v1\common\logic;

use app\v1\opcode\PfOpcode;
use xphp\db\Op;
use xphp\OpcodeHandler;
use xphp\Request;
use xphp\Response;

/**
 * note          基础的 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:26
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Base extends Op
{
    // 声明请求
    protected $request;

    protected $pfOpcode;

    protected static $instance = null;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->request = new Request();
        $this->pfOpcode = new PfOpcode();
    }

    /**
     * 获取单例实例
     * @return static|null
     */
    public static function instance(): ?self
    {
        if (!static::$instance instanceof static) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 返回消息到UI统一接口
     * @param boolean $result    操作结果
     *                           true/false
     * @param string  $operate   操作描述
     * @param string  $msg       消息描述/具体消息
     * @param string  $level     提示等级
     * @param int     $errorCode 错误码
     * @param array   $extInfo   附加信息
     * @return string           json
     */
    public function muOpResult(bool $result, string $operate, $msg = '', $level = '', $errorCode = 0, $extInfo = '')
    {
        $data = parent::muOpResult($result, $operate, $msg, $level, $errorCode, $extInfo);

        return (new Response())->returns($data);
    }

    /**
     * 和service 通信封装
     * @param string $class  可以为空 表示同级别目录下Service的service
     *                       只是个名称 就是同级别下的某个service
     *                       也可以是绝对的路径
     * @param string $method 请求方式
     * @param array  $param  数组
     * @return Op
     */
    protected function service(string $class = '', $method = '', $param = [])
    {

        if (empty($class)) {
            // 默认为同级别的同名称的logic
            $class = $this->request->request(4) . '\\service\\Service';
        } elseif (!(strpos($class, '\app') !== false)) {
            // 表示写的相对路径 就是同级别下的某个logic
            $class = $this->request->request(4) . '\\service\\' . $class;
        }
        if (empty($method)) {
            return (new $class());
        }

        return (new $class())->$method($param);
    }

    /**
     * 得到模块类型信息
     * @param int $module    模块号
     * @param int $subModule 子模块号
     * @param int $tasktype  任务类型
     * @return string
     * 告警/存储要调用
     */
    protected function getModuleTypeDes($module, $subModule = null, $tasktype = null)
    {
        $desConf = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        $des = $desConf[$module];
        if ($module == $moduletype['BACKUP_COPY_CLIENT']) {
            if ($tasktype) {
                $tasktypes = xphp_get_config('task', 'TASKTYPE');
                if (in_array($tasktype, [$tasktypes['ARCHIVE'], $tasktypes['ARCHIVE_FETCH']])) {
                    $des = xphp_get_lang('UI_PLATFORM_ARCHIVE');
                }
            }
        }
        if ($subModule) {
            //添加子模块,暂时添加虚拟机子模块,后期TODO涉及到数据库子模块
            if ($module == $moduletype['VM']) {
                $des = xphp_get_config('vm', 'VMHYPERVISORDES')[$subModule];
            }
            //还需添加文件子模块
            elseif ($module == $moduletype['FS']) {
                $submoduletype = xphp_get_config('module', 'SUBMODULE_TYPE');
                if ($subModule == $submoduletype['HADOOP']) {
                    $des = xphp_get_lang('UI_PLATFORM_HADOOP');
                } elseif ($subModule == $submoduletype['OBS']) {
                    $des = xphp_get_lang('UI_PLATFORM_OBS');
                }
            }
        }
        return $des;
    }

    /**
     * 得到当前所有任务的UUID
     * @return array
     */
    protected function getCurrentAllTaskUUID(): array
    {

        $sql = "select task_uuid from bd_task where module_type in(2, 3, 4, 5, 9, 10, 11, 14, 17, 28) and delete_flag = ?";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);

        return !empty($data) ? array_column($data, 'task_uuid') : [];
    }

    /**
     * 根据文件类型得到显示的Class
     * @param int    $filetype 文件类型
     * @param string $filename 文件名
     * @param string $size     图标大小   s/m/l
     *                         24/32/48px
     * @return string
     */
    protected function getFileClassName($filetype, $filename, $size): string
    {
        $class = 'filetype-unknown-' . $size;
        if (intval($filetype) != xphp_get_config('file', 'FILETYPE')['FILE']) {
            return 'filetype-dir-' . $size;
        }
        $allFileType = array(
            'aac',
            'ai',
            'aiff',
            'asp',
            'avi',
            'bmp',
            'c',
            'cpp',
            'css',
            'dat',
            'dmg',
            'doc',
            'docx',
            'dot',
            'dotx',
            'dwg',
            'dxf',
            'eps',
            'exe',
            'flv',
            'gif',
            'h',
            'html',
            'ics',
            'iso',
            'java',
            'jpg',
            'key',
            'm4v',
            'mid',
            'mov',
            'mp3',
            'mp4',
            'mpg',
            'odp',
            'ods',
            'odt',
            'otp',
            'ots',
            'ott',
            'pdf',
            'php',
            'png',
            'pps',
            'ppt',
            'psd',
            'py',
            'qt',
            'rar',
            'rb',
            'rtf',
            'sql',
            'tga',
            'tgz',
            'tiff',
            'txt',
            'wav',
            'xls',
            'xlsx',
            'xml',
            'yml',
            'zip'
        );
        $fileArr = explode('.', $filename);
        $index = (count($fileArr) - 1) <= 0 ? 0 : (count($fileArr) - 1);
        if (in_array(strtolower($fileArr[$index]), $allFileType)) {
            $class = 'filetype-' . strtolower($fileArr[$index]) . '-' . $size;
        }
        return $class;
    }

    /**
     * 检测模块是否授权
     * @param string $authModule 代理模块授权字段authorization_module
     * @param string $module     模块名
     * @return boolean
     */
    protected function checkModuleValid($authModule, $module): bool
    {
        if (empty($authModule)) {
            return false;
        }
        $authModule = json_decode($authModule, true);
        if ($authModule[$module]) {
            return true;
        }
        return false;
    }

    /**
     * 公共方法
     * 得到标记图标,即WMYF的标记  表示GFS的W周,M月,Y年,F星标
     * 传入参数都为布尔值
     * @param boolean $wflag 保留周标记
     * @param boolean $mflag 保留月标记
     * @param boolean $yflag 保留年标记
     * @param boolean $fflag 永久保留标记
     * @return string
     */
    public function pGetTimepointMark($wflag, $mflag, $yflag, $fflag)
    {
        $markStr = '';
        if ($wflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_WEEK_POINT') .
                '" class="viconfont vicon-remark-week"></i>';
        }
        if ($mflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_MONTH_POINT') .
                '" class="viconfont vicon-remark-month"></i>';
        }
        if ($yflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_YEAR_POINT') .
                '" class="viconfont vicon-remark-year"></i>';
        }
        if ($fflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_FOREVER_POINT') .
                '" class="viconfont vicon-remark-forever"></i>';
        }
        return $markStr;
    }

    /**
     * 根据备份模式得到时间点图标
     * @param int $backupMode mode
     * @return string
     */
    public function getTimepointIcon($backupMode): string
    {
        $backupMode = intval($backupMode);
        $backupModeArr = xphp_get_config('task', 'BACKUP_MODE');
        $icon = './img/platform/timepoint.png';
        switch ($backupMode) {
            case $backupModeArr['FULL']:
                $icon = './img/platform/timepoint-f.png';
                break;
            case $backupModeArr['INCREMENTAL']:
                $icon = './img/platform/timepoint-i.png';
                break;
            case $backupModeArr['DIFFERENTIAL']:
                $icon = './img/platform/timepoint-d.png';
                break;
        }
        return $icon;
    }

    /**
     * 找增量点的完备点
     * @param string $timepointuuid uuid
     * @return array
     */
    protected function getFulllPoint($timepointuuid)
    {
        $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bbt.task_uuid, bbt.id,
       bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode,
       bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks, bbt.real_node_uuid,
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name
        from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
        where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
         bbt.module_type = 3 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=?
        order by fbt.agent_uuid, bbt.timepoint";
        $data = $this->dbSelect($sqlfull, array($timepointuuid));
        if (!empty($data[0]['depend_point_uuid'])) { //不是完备点继续找
            return $this->getFulllPoint($data[0]['depend_point_uuid']);
        } else {
            return $data[0];
        }
    }

    /**
     * 检查系统授权是否正常
     * @return bool
     */
    protected function checkSystemAuthStatus(): bool
    {
        $sql = "SELECT authorized_flag FROM bd_system ";
        $data = $this->dbSelect($sql);
        if ((int) $data[0]['authorized_flag'] != xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            // 未正常授权返回错误提示
            return false;
        }
        return true;
    }

    /**
     * 构建限速策略
     * @param array $speedStrategy 限速策略
     * @return array
     */
    protected function buildSpeedStrategy(array $speedStrategy): array
    {
        $info = [
            'task_priority' => $speedStrategy['level'], // 任务级别
            'remark'    => '',
            'is_global' => 0,
        ];
        if ($speedStrategy['type'] == 1) {
            // 全局策略
            if (!$speedStrategy['uuid']) {
                return [];
            }
            $info['strategy_uuid'] = $speedStrategy['uuid'];
            $info['strategy_name'] = $speedStrategy['name'];
            $info['strategy_type'] = $speedStrategy['strategy_type'];
            $info['is_global'] = 1;
            $info['speed_limit_info'] = [];
            $info['extra_info'] = '';
        } else {
            $speedStrategy['speed'] = $speedStrategy['speed'] ?: [];
            // 自定义
            $info['strategy_uuid'] = '';
            $info['strategy_name'] = '';
            $info['is_global'] = 0;
            $info['extra_info'] = json_encode($speedStrategy['speed']);
            // 查询出type和组装下策略
            if (isset($speedStrategy['speed']) && count($speedStrategy['speed'])) {
                $strategyType = $speedStrategy['speed'][0]['type'];
                $info['strategy_type'] = $strategyType;
                if (in_array($strategyType, [1, 4, 5])) {
                    // 这三个类型的没有days的值
                    $timeList = [];
                    foreach ($speedStrategy['speed'] as $item) {
                        $timeList[] = [
                            'start_time' => $strategyType != 5
                                ? v1_formart_time($item['startTime']) : $item['startTime'],
                            'end_time' => $strategyType != 5 ? v1_formart_time($item['endTime']) : $item['endTime'],
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedLimitInfo[] = [
                        'days' => '',
                        'time_list' => $timeList
                    ];
                } else {
                    $speedLimitInfos = [];
                    foreach ($speedStrategy['speed'] as $item) {
                        $days = implode('', $item['days']);
                        $speedLimitInfos[$days][] = [
                            'start_time' => v1_formart_time($item['startTime']),
                            'end_time' => v1_formart_time($item['endTime']),
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedLimitInfo = [];
                    foreach ($speedLimitInfos as $keys => $items) {
                        $speedLimitInfo[] = [
                            'days' => $keys,
                            'time_list' => $items,
                        ];
                    }
                }
                $info['speed_limit_info'] = $speedLimitInfo;
            } else {
                // 未定义，直接传空数组
                return [];
            }
        }

        return $info;
    }

    /**
     * 获取任务的限速策略
     * @param string $jobUuid 任务uuid
     * @return array
     */
    protected function getSpeedStrategy(string $jobUuid): array
    {
        $sql = "SELECT strategy_uuid,task_priority FROM bd_task_speed_limit_strategy WHERE task_uuid = ? LIMIT 1";
        $data = $this->dbSelect($sql, [$jobUuid]);
        $info = [
            'level' => 1,
            'type' => 1,
            'speedInfo' => [],
            'strategy_type' => 1,
        ];
        if (!empty($data)) {
            // 查询出全局限速策略
            $sql = "SELECT * FROM bd_global_speed_limit_strategy WHERE strategy_uuid = ? LIMIT 1";
            $strategy = $this->dbSelect($sql, [$data[0]['strategy_uuid']]);
            if (!empty($strategy)) {
                $info['level'] = $data[0]['task_priority']; // 任务级别
                $info['strategy_type'] = $strategy[0]['strategy_type']; // 限速类型
                $info['type'] = $strategy[0]['is_global'] ? 1 : 2; // 任务类型 选择策略还是自定义策略
                if ($info['type'] == 1) {
                    // 全局策略
                    $info['uuid'] = $strategy[0]['strategy_uuid'];
                } else {
                    $info['speedInfo'] = json_decode($strategy[0]['extra_info'], true);
                }
            }
        }
        return $info;
    }

    /**
     * 检查任务是否存在
     * @param string $jobsUuid    任务的uuid
     * @param array  $jobTypeList 任务类别
     * @return array|bool
     */
    protected function checkTaskExists(string $jobsUuid, array $jobTypeList)
    {
        $sql = "SELECT * FROM bd_task WHERE task_uuid = ? ";
        if (!$jobTypeList) {
            return false;
        } elseif (count($jobTypeList) == 1) {
            $sql .= " AND task_type = $jobTypeList[0] ";
        } else {
            $jobTypes = "'" . implode("', '", $jobTypeList) . "'";
            $sql .= " AND task_type IN ($jobTypes) ";
        }
        $data = $this->dbSelect($sql, [$jobsUuid]);
        if (!$data || !is_array($data)) {
            return false;
        }
        return $data[0];
    }

    /**
     * 构建返回信息
     * @param string $message 返回消息
     * @param bool   $success 返回结果
     * @param int    $code    返回状态码
     * @param ?array $data    data数据
     * @return array
     */
    protected function sendResult(string $message, bool $success = true, int $code = 200, array $data = []): array
    {
        return [
            'success' => $success,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * 判断是否显示所有客户端
     * @return boolean
     */
    private function judgeShowAllAgent(): bool
    {
        /**
         * 1. 三权模式并且是系统管理员、安全管理员显示所有客户端
         * 2. 拥有全局查看权限的用户显示所有客户端
         */
        $loginUser = xphp_get_user_info();
        $showAllAgent = false;
        // admin类别
        $showAllAgent = $showAllAgent || $loginUser['userType'] == xphp_get_config('user', 'USER_TYPES')['USER_ADMIN'];
        // 三权模式
        $threePosersUser = xphp_get_config('three_powers', 'THREE_POWERS_USER');
        if ($_SESSION['isThreePowers']) {
            $showAllAgent = $showAllAgent || $_SESSION['userLevel'] == $threePosersUser['sysadmin'];
            $showAllAgent = $showAllAgent || $_SESSION['userLevel'] == $threePosersUser['safeadmin'];
        } else {  // 非三权模式的普通用户只显示自己拥有的客户端
            // $showAllAgent = $showAllAgent || false;
        }
        // 2. 拥有全局查看权限的用户显示所有客户端
        if (
            in_array('global_read', $loginUser['permissionArr']) ||
            in_array('global_write', $loginUser['permissionArr'])
        ) {
            $showAllAgent = true;
        }
        return $showAllAgent;
    }

    /**
     * 获取所有租户的所有客户端 资源
     * @return array|boolean
     */
    private function getAllTenantAgentResource()
    {
        $sql = "SELECT ba.agent_uuid
                FROM bd_agent ba
                    INNER JOIN mt_user_tenant mut ON ba.user_uuid = mut.user_uuid ";

        $tenantAgentData = $this->dbSelect($sql);
        if (is_array($tenantAgentData) && !$tenantAgentData) {  // 表示租户没有添加客户端，admin可以查看所有客户端
            return true;
        }
        $sql = "SELECT ba.agent_uuid
                FROM bd_agent ba
                    LEFT JOIN mt_user_tenant mut ON ba.user_uuid = mut.user_uuid
                WHERE (mut.tenant_uuid = '' OR mut.tenant_uuid is null) ";

        $adminAgentData = $this->dbSelect($sql);
        if (is_array($adminAgentData) && count($adminAgentData)) {
            return array_column($adminAgentData, 'agent_uuid');
        }
        return [];
    }

    /**
     * 获取某个用户的所有的客户端资源
     * @param string $userUuid      用户uuid
     * @param int    $type          类型
     * @param array  $agentTypeList 代理类型[10客户端 2传输代理]
     * @param array  $userUuidList  用户uuid列表，用于查询租户的所有客户端资源
     * @return array|boolean
     */
    public function getClientUuids($userUuid = '', $type = 1, $agentTypeList = [10], $userUuidList = [])
    {
        $loginUser = xphp_get_user_info();
        if ($loginUser['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {  // 超级管理员不能查看租户添加的客户端
            return $this->getAllTenantAgentResource();
        }
        if ($this->judgeShowAllAgent()) {
            return true;
        }
        $agentTypes = implode(', ', $agentTypeList);
        $user = xphp_get_user_info();
        if (empty($userUuid)) {
            $userUuid = $user['userUuid'];
        }

        $userUuid = [$userUuid];
        $userUuid = array_merge($userUuid, $userUuidList);

        if ($type) {
            // 关联管理用户判断 存储资源 - 查看  resmanagement_look
            $authUser = $user['authUser']['resmanagement_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $userUuid = array_merge($userUuid, $authUser);
            }
        }

        $userUuidStr = "'" . implode("','", $userUuid) . "'";

        // resource_type 为 10
        $return = [];
        // 创建者的客户端uuid
        $sql = "select agent_uuid from bd_agent where user_uuid in ( {$userUuidStr} )";
        $data1 = $this->dbSelect($sql);
        if (!empty($data1)) {
            $array = array_column($data1, 'agent_uuid');
            $arrayStr = "'" . implode("','", $array) . "'";
            // 根据创建者的客户端uuid去查询是否有分配到资源/组给其它用户
            $sql2 = "select resource_uuid from mt_user_resource
                    where resource_type IN ($agentTypes) and resource_uuid in ( {$arrayStr} )";
            $array2 = $this->dbSelect($sql2);
            $array2 = !empty($array2) ? array_column($array2, 'resource_uuid') : [];

            // 资源组的创建不算，要分配了的才能不算
            $sql3 = "select mrrg.resource_uuid from mt_resource_resource_group mrrg
                    join mt_user_resource_group murg on murg.resource_group_uuid = mrrg.resource_group_uuid
                    where mrrg.resource_type IN ($agentTypes) and mrrg.resource_uuid in ( {$arrayStr} )";
            $array3 = $this->dbSelect($sql3);
            $array3 = !empty($array3) ? array_column($array3, 'resource_uuid') : [];

            // 计算差集
            $arrays = array_unique(array_merge($array2, $array3));
            $return = array_diff($array, $arrays);
        }

        // 分配的资源
        $sql = "select resource_uuid from mt_user_resource
                where resource_type IN ($agentTypes) and user_uuid in ( {$userUuidStr} )";
        $data1 = $this->dbSelect($sql);
        $array1s = !empty($data1) ? array_column($data1, 'resource_uuid') : [];
        $return = array_merge($return, $array1s);

        // 分配的资源组资源
        $sql = "select mrrg.resource_uuid from mt_resource_resource_group mrrg
                    join mt_user_resource_group murg on murg.resource_group_uuid = mrrg.resource_group_uuid
                    where mrrg.resource_type IN ($agentTypes) and murg.user_uuid in ( {$userUuidStr} )";
        $data2 = $this->dbSelect($sql);
        $array2s = !empty($data2) ? array_column($data2, 'resource_uuid') : [];
        return array_merge($return, $array2s);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        $result = false;
        $userInfo = xphp_get_user_info();
        if (
            xphp_three_powers() &&
            ($userInfo['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['admin'] ||
                $userInfo['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin'] ||
                $userInfo['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['safeadmin']
            )
        ) {
            $result = true;
        }
        if (!xphp_three_powers() && xphp_get_user_info()['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            $result = true;
        }
        return $result;
    }

    /**
     * 根据状态码获取状态描述字符串
     *
     * 此函数接受一个状态码作为输入，然后根据该状态码与预定义的操作状态码进行   按位与运算，
     * 来判断当前状态包括哪些操作状态，并将这些操作状态的描述组合成一个字符串返回。
     * 主要用于将状态码转换为人类可读的描述信息。
     *
     * @param int $status 当前的状态码，是与操作状态码进行位运算的依据。
     * @return string 当前状态的描述字符串，各描述信息之间用逗号和空格分隔。 例：“扫描中，校验中”
     */
    protected function getOpStatusDes($status)
    {
        $oPCODE = xphp_get_config('point', 'OPERATION_STATUS');
        // 获取操作描述配置
        $oPDES = xphp_get_desc('Point', 'OPERATE_DES');
        // 0是未操作
        if ($status == 0) {
            return $oPDES[0];
        }
        // 初始化结果数组，用于存储当前状态对应的操作描述
        $result = [];

        // 检查当前状态是否包含合并操作
        if ($status & $oPCODE['MERGING']) {
            $result[] = $oPDES[$oPCODE['MERGING']];
        }
        // 检查当前状态是否包含删除操作
        if ($status & $oPCODE['DELETING']) {
            $result[] = $oPDES[$oPCODE['DELETING']];
        }
        // 检查当前状态是否包含扫描操作
        if ($status & $oPCODE['SCANNING']) {
            $result[] = $oPDES[$oPCODE['SCANNING']];
        }
        // 检查当前状态是否包含验证操作
        if ($status & $oPCODE['VERIFY']) {
            $result[] = $oPDES[$oPCODE['VERIFY']];
        }
        // 将结果数组中的描述信息组合成一个字符串返回
        return implode(', ', $result);
    }

    /**
     * 获取状态提示信息
     * 根据操作状态、病毒状态和完整性状态生成相应的提示信息
     * 操作中的时间点
     *
     *
     * @param int $merge_status     合并状态
     * @param int $operation_status 操作状态码
     * @param int $virusStatus      病毒状态码
     * @param int $integrityStatus  完整性状态码
     *
     * @return {
     * avaliable_flag:时间点可用标志，操作中的点不可恢复，其他状态均可恢复
     * status:状态码，1操作中，2正常，3异常
     * status_des:状态描述，例：该备份点已经被感染
     * } 提示信息 例：该备份点已经被感染
     * 1 操作中
     * 2 正常
     * 3 异常
     */
    protected function getTimePointStatus($mergestatus, $operationstatus, $virusscanstatus, $integritycheckstatus)
    {
        // 操作状态码
        $oPCODE = xphp_get_config('point', 'OPERATION_STATUS');
        // 病毒状态
        $vIRUSSTATUS = xphp_get_config('point', 'VIRUS_STATUS');
        // 完整性状态
        $iNTEGRITYSTATUS = xphp_get_config('point', 'INTEGRITY_STATUS');
        // 合并状态
        $mERGESTATUS = xphp_get_config('point', 'MERGE_STATUS');
        $return = [
            'avaliable_flag' => true,
            'status' => 2,
            'status_des' => xphp_get_lang('UI_REPORT_NODE_NORMAL'),
        ];
        // 获取操作描述配置---操作状态优先显示
        if ($operationstatus != 0) {
            // 时间点处于操作中
            $statusDes = $this->getOpStatusDes($operationstatus);
            $avaliableflag = true;
            if (($operationstatus & $oPCODE['MERGING']) || ($operationstatus & $oPCODE['DELETING'])) {
                $avaliableflag = false;
            }
            $return = [
                'avaliable_flag' => $avaliableflag,
                'status' => 1,
                'status_des' => str_replace('%s', $statusDes, xphp_get_lang('UI_BACKUP_DATA_TIP_OPERATING')),
            ];
            return $return;
        }
        // 正常状态
        if ($virusscanstatus !== $vIRUSSTATUS['INFECTED'] && $integritycheckstatus !== $iNTEGRITYSTATUS['BROKEN'] && $mergestatus !== $mERGESTATUS['FAILED'] && $mergestatus !== $mERGESTATUS['FAILED']) {
            $return = [
                'avaliable_flag' => true,
                'status' => 2,
                'status_des' => xphp_get_lang('UI_REPORT_NODE_NORMAL'),
            ];
            return $return;
        // 异常状态
        } else {
            $des = '';
            // 感染、合并失败、损坏
            if (($virusscanstatus == $vIRUSSTATUS['INFECTED'] || $virusscanstatus == $vIRUSSTATUS['INFECTED_PARTLY_SCAN']) && $integritycheckstatus == $iNTEGRITYSTATUS['BROKEN'] && $mergestatus == $mERGESTATUS['FAILED']) {
                $des = xphp_get_lang('UI_BACKUP_DATA_TIP_INFECTED_AND_BROKEN_AND_FAILED');
            }
            // 合并失败、损坏
            if ($integritycheckstatus == $iNTEGRITYSTATUS['BROKEN'] && $mergestatus == $mERGESTATUS['FAILED']) {
                $des .= xphp_get_lang('UI_BACKUP_DATA_TIP_BROKEN_AND_FAILED');
            }
            // 感染、损坏
            if (($virusscanstatus == $vIRUSSTATUS['INFECTED'] || $virusscanstatus == $vIRUSSTATUS['INFECTED_PARTLY_SCAN']) && $integritycheckstatus == $iNTEGRITYSTATUS['BROKEN']) {
                $des = xphp_get_lang('UI_BACKUP_DATA_TIP_INFECTED_AND_BROKEN');
            }
            // 感染且合并失败
            if (($virusscanstatus == $vIRUSSTATUS['INFECTED'] || $virusscanstatus == $vIRUSSTATUS['INFECTED_PARTLY_SCAN']) && $mergestatus == $mERGESTATUS['FAILED']) {
                $des = xphp_get_lang('UI_BACKUP_DATA_TIP_INFECTED_AND_FAILED');
            }
            // 感染
            if (($virusscanstatus == $vIRUSSTATUS['INFECTED'] || $virusscanstatus == $vIRUSSTATUS['INFECTED_PARTLY_SCAN'])) {
                $des = xphp_get_lang('UI_BACKUP_DATA_TIP_INFECTED');
            }
            // 数据损坏
            if ($integritycheckstatus == $iNTEGRITYSTATUS['BROKEN']) {
                $des = xphp_get_lang('UI_BACKUP_DATA_TIP_BROKEN');
            }
            // 合并失败
            if ($mergestatus == $mERGESTATUS['FAILED'] || $mergestatus == $mERGESTATUS['DEPEND_MERGE_FAILED']) {
                $des = xphp_get_lang('UI_BACKUP_DATA_TIP_MERGE_FAILED');
            }
            $return = [
                'avaliable_flag' => true,
                'status' => 3,
                'status_des' => $des,
            ];
        }
        return $return;
    }

    /**
     * 构建重试策略信息
     * @param string $jobUuid 任务uuid
     * @return array
     */
    public function groupRetryStrategyInfo(string $jobUuid): array
    {
        $sql = "SELECT network_retry_times, network_retry_interval, op_retry_times,
                    op_retry_interval, task_retry_object, task_retry_times, task_retry_interval
                FROM bd_retry_strategy WHERE task_uuid = ? ";
        $retryStrategyData = $this->dbSelect($sql, [$jobUuid]);
        if (!is_array($retryStrategyData) || !$retryStrategyData) {
            return [];
        }
        $retryStrategy = $retryStrategyData[0];
        //操作重试开关
        $opRetryFlag = true;
        if ($retryStrategy['op_retry_times'] == 0) {
            $opRetryFlag = false;
        }
        //任务重试开关
        $taskRetryFlag = true;
        if ($retryStrategy['task_retry_times'] == 0) {
            $taskRetryFlag = false;
        }
        return [
            'network_retry_times' => $retryStrategy['network_retry_times'],
            'network_retry_interval' => $retryStrategy['network_retry_interval'],
            'op_retry_flag' => $opRetryFlag,
            'op_retry_times' => $retryStrategy['op_retry_times'],
            'op_retry_interval' => $retryStrategy['op_retry_interval'],
            'task_retry_flag' => $taskRetryFlag,
            'task_retry_object' => $retryStrategy['task_retry_object'],
            'task_retry_times' => $retryStrategy['task_retry_times'],
            'task_retry_interval' => $retryStrategy['task_retry_interval'],
        ];
    }

    /**
     * 任务详情-获取安全策略
     */
    public function getSafeConfigStrategy($taskUuid)
    {
        $sql = "SELECT bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
                    btsc.worm_protection_time, btsc.virus_scan_config_list,
                    btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,
                    btsc.backup_integrity_check_inc_error_policy, btsc.recovery_integrity_check_error_policy,
                    btsc.create_surebackup_after_backup, btsc.surebackup_timepoint_range, btsc.surebackup_thread_num
                FROM bd_task bt
                    INNER JOIN bd_task_safe_config btsc ON bt.task_uuid = btsc.task_uuid
                WHERE bt.task_uuid = ? ";
        $safeConfigData = $this->dbSelect($sql, [$taskUuid]);
        if (!is_array($safeConfigData) || !$safeConfigData) {
            return [];
        }
        return [
            'worm_flag' => v1_parse_flag_to_bool($safeConfigData[0]['worm_flag']),
            'worm_protection_time' => intval($safeConfigData[0]['worm_protection_time']),
            'virus_scan_flag' => v1_parse_flag_to_bool($safeConfigData[0]['virus_scan_flag']),
            'virus_scan_config_list' => json_decode($safeConfigData[0]['virus_scan_config_list'], true) ?: [],
            'integrity_check_flag' => v1_parse_flag_to_bool($safeConfigData[0]['integrity_check_flag']),
            'integrity_check_config' => [
                'check_strategy' => intval($safeConfigData[0]['integrity_check_strategy']),
                'full_error_policy' => intval($safeConfigData[0]['backup_integrity_check_full_error_policy']),
                'inc_error_policy' => intval($safeConfigData[0]['backup_integrity_check_inc_error_policy']),
                'recovery_error_policy' => intval($safeConfigData[0]['recovery_integrity_check_error_policy']),
            ],
            'create_surebackup_after_backup' => v1_parse_flag_to_bool($safeConfigData[0]['create_surebackup_after_backup']),
            'surebackup_timepoint_range' => intval($safeConfigData[0]['surebackup_timepoint_range']),
            'surebackup_thread_num' => intval($safeConfigData[0]['surebackup_thread_num']),
        ];
    }

    /**
     * 获取任务的代理池信息
     * @param mixed $taskUuid
     * @return array
     */
    public function getJobAgentPoolInfo($taskUuid): array
    {
        $sql = "SELECT btap.agent_pool_uuid, bap.agent_pool_nickname
                FROM bd_task_agent_pool btap
                    INNER JOIN bd_agent_pool bap ON btap.agent_pool_uuid = bap.agent_pool_uuid
                WHERE task_uuid = ? ";
        $agentPoolData = $this->dbSelect($sql, [$taskUuid]);
        if (!is_array($agentPoolData) || !$agentPoolData) {
            return [
                'agent_pool_uuid' => '',
                'agent_pool_nickname' => '',
            ];
        }
        return [
            'agent_pool_uuid' => $agentPoolData[0]['agent_pool_uuid'],
            'agent_pool_nickname' => $agentPoolData[0]['agent_pool_nickname'],
        ];
    }

    /**
     * 操作存储权限判断-分配的资源
     * @param string $sourceUuid 资源uuid,多个以逗号隔开
     * @param int    $souceType  分配的资源类型标识 详见resource的 RESOURCE_TYPE 枚举
     * @param string $auth       权限标识，详见 user的 USER_AUTH 枚举
     * @return json|bool
     */
    protected function checkAuthBySourceUuid(string $sourceUuid, int $souceType, string $auth = 'resmanagement')
    {

        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 非三权模式，并且不是超级管理员也不是全局观察者操作，也只能操作的资源
            $check = v1_auth_check_operate(
                $sourceUuid,
                $auth,
                $souceType
            );

            if (empty($check)) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_PUBLIC_TIPS'),
                    xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR')
                );
            }
        }
        return true;
    }

    /**
     * 操作存储权限判断-非分配的资源
     * @param string $userUuid 资源所对应的用户uuid,多个以逗号隔开
     * @param string $auth     权限标识，详见 user的 USER_AUTH 枚举
     * @return json|bool
     */
    protected function checkAuthByUserUuid(string $userUuid, string $auth)
    {

        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 非三权模式，并且不是超级管理员也不是全局观察者操作，也只能操作的资源
            if (empty($userUuid)) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_PUBLIC_TIPS'),
                    xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR')
                );
            }
            $userUuidArr = v1_auth_get_users($auth, 2);
            $idArr = explode(',', $userUuid);
            $idArr = array_filter($idArr);
            if (count($idArr) == 1) {
                $sql = "SELECT CASE WHEN '{$idArr[0]}' IN (
                            {$userUuidArr}
                        ) THEN 1 ELSE 0 END AS result";
            } else {
                // 多个
                $subSql = '';
                $i = 0;
                foreach ($idArr as $item) {
                    if ($i == 0) {
                        $subSql = "SELECT '{$item}' AS user_uuid ";
                    } else {
                        $subSql .= "UNION ALL SELECT '{$item}' ";
                    }
                    $i++;
                }
                $sql = "WITH target_users AS (
                            {$userUuidArr}
                        ),
                        check_list AS (
                            {$subSql}
                        )
                        SELECT 
                            CASE WHEN COUNT(t.user_uuid) = COUNT(*) THEN 1 ELSE 0 END AS result
                        FROM check_list cl
                        LEFT JOIN target_users t ON cl.user_uuid = t.user_uuid";
            }

            $check = dbSelect($sql);
            if (empty($check) || $check[0]['result'] != 1) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_PUBLIC_TIPS'),
                    xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR')
                );
            }
        }
        return true;
    }

    /**
     * 授权检查
     * @return void
     */
    protected function licenseCheck(): void
    {
        if (v1_license_get_expire_days()['expire_days'] < 0) {
            // 授权无效
            $msg = xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED');
            exit($this->muOpResult(false, xphp_get_lang('UI_LICENSE_AUTH_INFO_TITLE'), $msg, 'warning'));
        }
    }
}
