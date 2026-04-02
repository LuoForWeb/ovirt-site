<?php

namespace app\v1\db\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库 -- 之任务操作 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbJobController extends Base
{
    function __construct()
    {
        parent::__construct();
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        unset($allDbType['UNKNOWN']);
        $allBackupMode = xphp_get_config('db', 'BACKUP_MODE', 'db');
        $allBackupMode = array_values(array_unique($allBackupMode));

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start_uuid' => ['require'],
            'jobs_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 任务uuid
            'db_type' => ['require', 'number', 'in:' . implode(',', $allDbType)],
            'backup_mode' => ['require', 'number', 'in:' . implode(',', $allBackupMode)],
            'db_list' => ['require', 'array', 'checkStartBackupJob'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 启动任务
            'start' => ['start_uuid'],
            // 启动备份任务
            'start_backup_job' => [
                'crosscheck', 'jobs_uuid', 'db_type', 'backup_mode',
                'db_list',
            ],
        ];
    }

    /**
     * 验证启动数据库备份任务
     * @param array  $dbList db_list数据
     * @param string $rules  规则
     * @param array  $data   其他数据
     * @param string $field  验证字段名
     * @return boolean|string
     */
    protected function checkStartBackupJob(array $dbList, string $rules, array $data, string $field)
    {
        foreach ($dbList as $index => $dbInfo) {
            if (!isset($dbInfo['agent_uuid'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, "[$index].agent_uuid");
            }
            if (!isset($dbInfo['db_uuid'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, "[$index].db_uuid");
            }
        }
        $dbType = intval($data['db_type']);
        if ($dbType == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {  // Oracle有特殊逻辑
            $allBackupMode = xphp_get_config('db', 'BACKUP_MODE', 'db');
            if ($data['backup_mode'] == $allBackupMode['archivelog_backup']) {
                if ($data['crosscheck']) {  // 归档日志备份不支持crosscheck
                    return xphp_get_lang('WEB_DB_ORACLE_ARCHIVELOG_NONSUPPORT_CROSSCHECK');
                }
            }
        }
        return true;
    }
}
