<?php

namespace app\v1\db\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库 -- 之恢复管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbRecover extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        unset($allDbType['UNKNOWN']);
        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'job_name' => ['require'],
            'db_type' => ['require', 'number', 'in:' . implode(',', $allDbType)],
            'recovery_target' => ['require', 'array', 'checkRecoveryTarget'],  // 恢复目的地
            'recovery_info' => ['require', 'array', 'checkRecoveryInfo'],  // 恢复方式
            'speed_strategy' => ['require', 'array', 'checkSpeedStrategy'],  // 限速策略
            'transfer_strategy' => ['require', 'array', 'checkTransferStrategy'],  // 传输策略
            'time_strategy' => ['require', 'array', 'checkTimeStrategy'],  // 时间策略
            'jobs_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 任务uuid，通过query发送的
            'retry_strategy' => ['require', 'array'],
            'safe_config_strategy' => ['require', 'array'],
            'source_instance_info_list' => ['require', 'array'],
            'path_type' => ['require', 'number'],
            'storage_uuid' => ['require'],
            'cluster_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'create_db_recovery_job' => [
                'task_name', 'db_type', 'recovery_target', 'recovery_type',
                'recovery_source_type', 'recovery_time_flag', 'time_strategy', 'speed_strategy',
                'transfer_strategy', 'recovery_source', 'retry_strategy', 'safe_config_strategy',
            ],
            'get_db_recovery_job' => ['jobs_uuid'],
            'edit_db_recovery_job' => [
                'jobs_uuid', 'task_name', 'db_type', 'recovery_target', 'recovery_type',
                'recovery_source_type', 'recovery_time_flag', 'time_strategy', 'speed_strategy',
                'transfer_strategy', 'recovery_source', 'retry_strategy', 'safe_config_strategy',
            ],
            'get_oracle_timepoint_chain_recovery_time' => ['source_instance_info_list', 'path_type', 'storage_uuid'],
            'get_sqlserver_cluster_active_node_agent_info' => ['cluster_uuid'],
        ];
    }

    /**
     * 验证recovery_target
     * @param array  $recoveryTarget 恢复源信息
     * @param string $rules          验证规则
     * @param array  $data           全部请求数据
     * @param string $field          验证字段
     * @return bool|string
     */
    protected function checkRecoveryTarget(array $recoveryTarget, string $rules, array $data, string $field)
    {
        if (!isset($recoveryTarget['agent_uuid'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'agent_uuid');
        }
        if (!isset($recoveryTarget['instance_name'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'instance_name');
        }
        return true;
    }

    /**
     * 验证recovery_method
     * @param array  $recoveryMethod 恢复源信息
     * @param string $rules          验证规则
     * @param array  $data           全部请求数据
     * @param string $field          验证字段
     * @return bool|string
     */
    protected function checkRecoveryInfo(array $recoveryMethod, string $rules, array $data, string $field)
    {
        $allRecoveryType = xphp_get_config('db', 'DB_RECOVERY_TYPE');
        unset($allRecoveryType['UNKNOWN']);
        if (!isset($recoveryMethod['recovery_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'recovery_type');
        }
        if (!in_array($recoveryMethod['recovery_type'], $allRecoveryType)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_IN'), $field . '.recovery_type', implode(',', $allRecoveryType));
        }

        if (!isset($recoveryMethod['db_config_list'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'db_config_list');
        }
        if (!is_array($recoveryMethod['db_config_list'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field . '.db_config_list');
        }
        if (!$recoveryMethod['db_config_list']) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field . '.db_config_list');
        }

        foreach ($recoveryMethod['db_config_list'] as $index => $dbConfig) {
            $ret = $this->checkRecoveryInfoConfigList(
                (int) $recoveryMethod['recovery_type'],
                $dbConfig,
                $allRecoveryType
            );
            if (true !== $ret) {
                return sprintf($ret, $field . ".db_config_list[$index]");
            }
        }
        if (!isset($recoveryMethod['recovery_source_type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'recovery_source_type');
        }
        if (!isset($recoveryMethod['recovery_time_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'recovery_time_flag');
        }
        if (!isset($recoveryMethod['max_parallel_nums'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'max_parallel_nums');
        }
        if (!isset($recoveryMethod['open_db_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'open_db_flag');
        }
        if (!isset($recoveryMethod['detail'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'detail');
        }

        return true;
    }

    /**
     * 验证恢复配置
     * @param int   $recoveryType    恢复类别
     * @param array $dbConfig        恢复配置
     * @param array $allRecoveryType 所有恢复类别
     * @return bool|string
     */
    private function checkRecoveryInfoConfigList(int $recoveryType, array $dbConfig, array $allRecoveryType)
    {
        // 数据库恢复源信息
        $requireAttrs = [
            'db_name', 'instance_name', 'agent_uuid', 'dir_path', 'time_point_uuid',
            'encrypt_password', 'is_rollback', 'rollback_time', 'recovery_time',
            'source_instance_name', 'source_db_name',
        ];
        foreach ($requireAttrs as $requireAttr) {
            if (!isset($dbConfig[$requireAttr])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', $requireAttr);
            }
        }
        $ret = true;
        switch ($recoveryType) {
            case $allRecoveryType['COVER']:  // 原数据库覆盖恢复
                break;
            case $allRecoveryType['CREATE']:  // 新建数据库恢复
                if (!isset($dbConfig['create'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'create');
                }
                if (!is_array($dbConfig['create'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.create');
                }
                $ret = $this->checkRecoveryInfoCreate($dbConfig['create']);
                break;
            case $allRecoveryType['SPECIFY_FOLDER']:  // 指定文件夹恢复
                if (!isset($dbConfig['specify_folder'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'specify_folder');
                }
                if (!is_array($dbConfig['specify_folder'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.specify_folder');
                }
                $ret = $this->checkRecoveryInfoSpecifyFolder($dbConfig['specify_folder']);
                break;
            case $allRecoveryType['REDIRECT_DIR']:  // 重定向目录恢复
                if (!isset($dbConfig['redirect_dir'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'redirect_dir');
                }
                if (!is_array($dbConfig['redirect_dir'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.redirect_dir');
                }
                $ret = $this->checkRecoveryInfoRedirectDir($dbConfig['redirect_dir']);
                break;
            case $allRecoveryType['EXPORT']:  // 导出目录恢复
                if (!isset($dbConfig['export'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'export');
                }
                if (!is_array($dbConfig['export'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.export');
                }
                $ret = $this->checkRecoveryInfoExport($dbConfig['export']);
                break;
            case $allRecoveryType['RESTORE_ARCHIVELOG']:
                if (!isset($dbConfig['restore_archivelog'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'restore_archivelog');
                }
                if (!is_array($dbConfig['restore_archivelog'])) {
                    return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT'), '%s.restore_archivelog');
                }
                $ret = $this->checkRecoveryInfoRestoreArchivelog($dbConfig['restore_archivelog']);
                break;
        }
        if (true !== $ret) {
            return $ret;
        }
        return true;
    }

    /**
     * 验证原数据库覆盖
     * @param array $cover 原数据库覆盖数据
     * @return bool|string
     */
    private function checkRecoveryInfoCover(array $cover)
    {
    }

    /**
     * 验证新建数据库恢复
     * @param array $create 新建数据库数据
     * @return bool|string
     */
    private function checkRecoveryInfoCreate(array $create)
    {
        if (!isset($create['db_name'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'create.db_name');
        }
        if (!isset($create['data_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'create.data_path');
        }
        if (!isset($create['log_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'create.log_path');
        }
        return true;
    }

    /**
     * 验证指定文件夹
     * @param array $specifyFolder 指定文件夹数据
     * @return bool|string
     */
    private function checkRecoveryInfoSpecifyFolder(array $specifyFolder)
    {
        if (!isset($specifyFolder['assign_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'specify_folder.assign_path');
        }
        if (!isset($specifyFolder['archive_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'specify_folder.archive_path');
        }
        return true;
    }

    /**
     * 验证重定向目录
     * @param array $redirectDir 重定向目录数据
     * @return bool|string
     */
    private function checkRecoveryInfoRedirectDir(array $redirectDir)
    {
        if (!isset($redirectDir['redirect_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'redirect_dir.redirect_path');
        }
        return true;
    }

    /**
     * 验证导出目录
     * @param array $export 导出目录数据
     * @return bool|string
     */
    private function checkRecoveryInfoExport(array $export)
    {
        if (!isset($export['export_path'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', 'export.export_path');
        }
        return true;
    }

    /**
     * 验证还原归档日志
     * @param array $restoreArchivelog 原归档日志数据
     * @return bool|string
     */
    private function checkRecoveryInfoRestoreArchivelog(array $restoreArchivelog)
    {
        if (!isset($restoreArchivelog['restore_archivelog_path'])) {
            return sprintf(
                xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'),
                '%s',
                'restore_archive_log.restore_archivelog_path'
            );
        }
        if (!isset($restoreArchivelog['restore_archivelog_type'])) {
            return sprintf(
                xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'),
                '%s',
                'restore_archive_log.restore_archivelog_type'
            );
        }
        if (!isset($restoreArchivelog['log_restore_start_time'])) {
            return sprintf(
                xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'),
                '%s',
                'restore_archive_log.log_restore_start_time'
            );
        }
        if (!isset($restoreArchivelog['log_restore_end_time'])) {
            return sprintf(
                xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'),
                '%s',
                'restore_archive_log.log_restore_end_time'
            );
        }
        return true;
    }

    /**
     * 验证传输策略
     * @param array  $transferStrategy 传输策略
     * @param string $rules            验证规则
     * @param array  $data             全部请求数据
     * @param string $field            验证字段
     * @return bool|string
     */
    protected function checkTransferStrategy(array $transferStrategy, string $rules, array $data, string $field)
    {
        if (!isset($transferStrategy['thread_num'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'thread_num');
        }
        if (!isset($transferStrategy['network_uuid'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'network_uuid');
        }
        if (!isset($transferStrategy['encrypt_flag'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'encrypt_flag');
        }
        if (!isset($transferStrategy['encrypt_method'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'encrypt_method');
        }
        return true;
    }

    /**
     * 验证时间策略
     * @param array  $timeStrategy 时间策略
     * @param string $rules        规则
     * @param array  $data         数据
     * @param string $field        字段名
     * @return bool|string
     */
    protected function checkTimeStrategy(array $timeStrategy, string $rules, array $data, string $field)
    {
        if (!isset($timeStrategy['type'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'type');
        }
        if ($timeStrategy['type'] == 2) {
            if (!isset($timeStrategy['strategy'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'strategy');
            }
        }
        return true;
    }
}
