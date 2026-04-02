<?php

namespace app\v1\db\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库 -- 之备份管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbBackUp extends Base
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        unset($allDbType['UNKNOWN']);
        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'is_auth_db' => ['in:0,1'],  // 是否获取系统已认证的数据库类别
            'is_client_online' => ['in:0,1'],  // 是否获取系统在线的客户端
            'app_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 客户端应用/实例uuid
            'job_name' => ['require'],
            'db_type' => ['require', 'number', 'in:' . implode(',', $allDbType)],
            'backup_source' => ['require', 'array', 'checkBackupSource'],  // 备份源
            'backup_target' => ['require', 'array', 'checkBackupTarget'],  // 备份目的地
            'backupInfo' => ['require', 'array'],  // 时间策略
            'speedList' => ['require', 'array', 'checkSpeedStrategy'],  // 限速策略
            'storage_strategy' => ['require', 'array', 'checkStorageStrategy'],  // 存储策略
            'reserved_strategy' => ['require', 'array', 'checkReservedStrategy'],  // 保留策略
            'transport_strategy' => ['require', 'array', 'checkTransportStrategy'],  // 传输策略
            'advanced_strategy' => ['require', 'array', 'checkAdvancedStrategy'],  // 高级策略
            'jobs_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 任务uuid，通过query发送的
            'agent_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 客户端uuid
            'retry_strategy' => ['require', 'array'],
            'safe_config_strategy' => ['require', 'array'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'get_db_type' => ['is_auth_db', 'is_client_online'],  // 获取数据库类别
            'load_instance_database' => ['app_uuid'],  // 加载数据库应用/实例的数据库信息
            'create_db_backup_job' => [  // 创建数据库备份任务
                'task_name', 'sub_task_name', 'db_type', 'backup_source', 'backup_target',
                'speed_strategy', 'storage_strategy',
                'reserved_strategy', 'transport_strategy', 'advanced_strategy',
                'strategy_group_uuid',  'retry_strategy', 'safe_config_strategy',
            ],
            'edit_db_backup_job' => [  // 修改数据库备份任务
                'jobs_uuid', 'task_name', 'sub_task_name', 'db_type', 'backup_source', 'backup_target',
                'speed_strategy', 'storage_strategy',
                'reserved_strategy', 'transport_strategy', 'advanced_strategy',
                'strategy_group_uuid',  'retry_strategy', 'safe_config_strategy',
            ],
            'get_db_backup_job' => ['jobs_uuid'],  // 获取数据库备份任务
            'load_db_data_file' => ['agent_uuid', 'table_space_name', 'db_type', 'instance_name'],
        ];
    }

    /**
     * 验证backup_source字段
     * @param array  $backupSource 备份源信息
     * @param string $rules        验证规则
     * @param array  $data         其他数据
     * @param string $field        字段名称
     * @return bool|string
     */
    protected function checkBackupSource(array $backupSource, string $rules, array $data, string $field)
    {
        foreach ($backupSource as $index => $item) {
            if (!is_array($item)) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), $field . "[$index]");
            }
            $ret = $this->checkBackupSourceItem($item, $data);
            if (true !== $ret) {
                return sprintf($ret, $field . "[$index]");
            }
        }
        return true;
    }

    /**
     * 验证单个备份源
     * @param array $item 单个备份源(数据库、实例)
     * @param array $data 请求的全部数据
     * @return bool|string
     */
    private function checkBackupSourceItem(array $item, array $data)
    {
        $requireAttrs = [
            'db_name', 'instance_name', 'agent_group_uuid', 'agent_uuid', 'cluster_uuid'
        ];
        foreach ($requireAttrs as $requireAttr) {
            if (!isset($item[$requireAttr])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', $requireAttr);
            }
            if (isset($data['jobs_uuid'])) {  // 修改需要db_uuid
                if (!isset($item['db_uuid'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'db_uuid');
                }
            }
        }
        return true;
    }

    /**
     * 验证backup_target字段
     * @param array  $backupTarget 备份源信息
     * @param string $rules        验证规则
     * @param array  $data         其他数据
     * @param string $field        字段名称
     * @return bool|string
     */
    protected function checkBackupTarget(array $backupTarget, string $rules, array $data, string $field)
    {
        $requireAttrs = ['node_uuid', 'storage_uuid'];
        foreach ($requireAttrs as $requireAttr) {
            if (!isset($backupTarget[$requireAttr])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, $requireAttr);
            }
        }
        return true;
    }

    /**
     * 验证存储策略
     * @param array  $storageStrategy 存储策略
     * @param string $rule            验证规则
     * @param array  $data            全部请求数据
     * @param string $field           验证的字段名
     * @return bool|string
     */
    protected function checkStorageStrategy(array $storageStrategy, string $rule, array $data, string $field)
    {
        if (!isset($storageStrategy['deduplication_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'deduplication_flag');
        }
        if (!isset($storageStrategy['compress_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'compress_flag');
        }
        if (!isset($storageStrategy['compress_method'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'compress_method');
        }
        if (!isset($storageStrategy['encrypt_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'encrypt_flag');
        }
        if (!isset($storageStrategy['encrypt_method'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'encrypt_method');
        }
        if (!isset($storageStrategy['auto_password_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'auto_password_flag');
        }
        if (!isset($storageStrategy['password'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'password');
        }
        if ($storageStrategy['encrypt_flag'] && !$storageStrategy['auto_password_flag']) {
            if (!$storageStrategy['password']) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field . '.password');
            }
        }
        return true;
    }

    /**
     * 验证保留策略
     * @param array  $reservedStrategy 保留策略
     * @param string $rule             验证规则
     * @param array  $data             全部请求数据
     * @param string $field            验证字段名
     * @return true|string
     */
    protected function checkReservedStrategy(array $reservedStrategy, string $rule, array $data, string $field)
    {
        $allReversedType = xphp_get_config('db', 'REVERSED_TYPE', 'db');
        if (!isset($reservedStrategy['reserved_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'reserved_type');
        }
        if (!in_array($reservedStrategy['reserved_type'], $allReversedType)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), $field . '.reserved_type', implode(',', $allReversedType));
        }
        if (!isset($reservedStrategy['value'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'value');
        }
        return true;
    }

    /**
     * 验证传输策略
     * @param array  $transportStrategy 传输策略
     * @param string $rule              验证规则
     * @param array  $data              全部请求数据
     * @param string $field             验证字段
     * @return bool|string
     */
    protected function checkTransportStrategy(array $transportStrategy, string $rule, array $data, string $field)
    {
        $allTransportMode = xphp_get_config('db', 'TRANSPORT_MODE', 'db');
        if (!isset($transportStrategy['encrypt_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'encrypt_flag');
        }
        if (!isset($transportStrategy['encrypt_method'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'encrypt_method');
        }

        if (!isset($transportStrategy['transport_mode'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'transport_mode');
        }
        if (!in_array($transportStrategy['transport_mode'], $allTransportMode)) {
            return sprintf(
                xphp_get_lang('WEB_VALIDATE_IN'),
                $field . '.transport_mode',
                implode(',', $allTransportMode)
            );
        }

        if (!isset($transportStrategy['network_uuid'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'network_uuid');
        }
        return true;
    }

    /**
     * 验证高级配置
     * @param array  $advancedStrategy 高级配置
     * @param string $rule             验证规则
     * @param array  $data             全部请求数据
     * @param string $field            验证字段
     * @return bool|string
     */
    protected function checkAdvancedStrategy(array $advancedStrategy, string $rule, array $data, string $field)
    {
        if (!isset($advancedStrategy['check_db_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'check_db_flag');
        }

        if (!isset($advancedStrategy['compress_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'compress_flag');
        }

        if (!isset($advancedStrategy['checksum_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'checksum_flag');
        }

        if (!isset($advancedStrategy['archive_num'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'archive_num');
        }

        if (!isset($advancedStrategy['delete_archivelog_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'delete_archivelog_flag');
        }

        if (!isset($advancedStrategy['thread_num'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'thread_num');
        }

        if (!isset($advancedStrategy['warning_setting'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'warning_setting');
        }
        if (!is_array($advancedStrategy['warning_setting'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), $field . '.warning_setting');
        }
        $ret = $this->checkAdvancedStrategyWarningSetting((int) $data['db_type'], $advancedStrategy['warning_setting']);
        if (true !== $ret) {
            return sprintf($ret, $field . '.warning_setting');
        }

        if (!isset($advancedStrategy['set_filesperset_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'set_filesperset_flag');
        }

        if (!isset($advancedStrategy['datafile_filesperset_num'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'datafile_filesperset_num');
        }

        if (!isset($advancedStrategy['archivelog_filesperset_num'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'archivelog_filesperset_num');
        }

        if (!isset($advancedStrategy['auto_log_backup_interval'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'auto_log_backup_interval');
        }
        if (!isset($advancedStrategy['channel_count'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'channel_count');
        }
        if (!isset($advancedStrategy['log_backup_times_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'log_backup_times_flag');
        }
        if (!isset($advancedStrategy['log_backup_times'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'log_backup_times');
        }
        if (!isset($advancedStrategy['log_backup_days_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'log_backup_days_flag');
        }
        if (!isset($advancedStrategy['log_backup_days'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'log_backup_days');
        }
        if (!isset($advancedStrategy['skip_inaccessible_file_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'skip_inaccessible_file_flag');
        }
        if (!isset($advancedStrategy['skip_offline_file_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'skip_offline_file_flag');
        }
        return true;
    }

    /**
     * 验证高级配置-告警配置
     * @param int   $dbType         数据库类别
     * @param array $warningSetting 告警配置
     * @return string|bool
     */
    private function checkAdvancedStrategyWarningSetting(int $dbType, array $warningSetting)
    {
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        switch ($dbType) {
            case $allDbType['POSTGRE']:
            case $allDbType['KINGBASE']:
            case $allDbType['UXDB']:
            case $allDbType['HIGHGO']:
            case $allDbType['OPENGAUSS']:
            case $allDbType['VASTBASE']:
            case $allDbType['ANTDB']:
                if (!isset($warningSetting['warn_check'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'warn_check');
                }
                $allWarnType = xphp_get_config('db', 'WARN_TYPE', 'db');
                if (!isset($warningSetting['warn_type'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'warn_type');
                }
                if (!in_array($warningSetting['warn_type'], $allWarnType)) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), '%s.warn_type', implode(',', $allWarnType));
                }

                if (!isset($warningSetting['warn_value'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'warn_value');
                }
                break;
        }
        return true;
    }
}
