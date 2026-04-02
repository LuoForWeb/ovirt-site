<?php

namespace app\v1\db\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库 -- 之数据管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbData extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        unset($allDbType['UNKNOWN']);

        $allBackupType = xphp_get_config('db', 'BACKUP_MODE', 'db');
        $allBackupType = array_values(array_unique($allBackupType));

        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $dbTaskType = [$allTaskType['DB_BACKUP'], $allTaskType['DB_BACKUP_COPY']];

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'with_copy_data' => ['number'],
            'node_uuid' => ['max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'jobs_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 任务uuid
            'job_type' => ['require', 'number', 'in:' . implode(',', $dbTaskType)],  // 任务类别
            'agent_cluster_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 客户端uuid/集群uuid
            'agent_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 客户端uuid
            'db_type' => ['require', 'number', 'in:' . implode(',', $allDbType)],  // 数据库类别
            'instance_name' => ['require'],  // 实例名称
            'db_uuid' => ['max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 数据库类别uuid
            'only_forever' => ['number'],  // 是否只返回永久备份点，默认全返回【0全返回 1只返回永久备份点】
            'backup_mode' => ['number', 'in:' . implode(',', $allBackupType)],  // 备份模式【1完备 2增量 3差异 4日志/归档日志】
            'log_type' => ['number'],  // 日志备份类型，【1日志 2归档日志】
            'start_time' => ['date'],  // 备份点开始时间
            'end_time' => ['date'],  // 备份点结束时间
            'encrypt_password' => ['require'],  // 加密的密码
            'time_point_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 备份点uuid
            'time_point_list' => ['array'],  // 备份时间点信息
            'backup_db_list' => ['array', 'sceneBackupDbList'],  // 备份数据库信息
            'agent_list' => ['require', 'array'],
            'timepoint_uuid' => ['require'],
            'new_instance_name' => ['require'],
            'oracle_home_path' => ['require'],
            'oracle_base_path' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'get_db_backup_data' => ['with_copy_data', 'node_uuid', 'recovery_flag'],
            'get_db_backup_time_point' => [
                'jobs_uuid', 'job_type', 'agent_cluster_uuid', 'db_type',
                'instance_name', 'db_uuid', 'only_forever', 'backup_mode',
                'log_type', 'start_time', 'end_time',
            ],
            'get_db_backup_time_point_list' => ['agent_list', 'db_type'],
            'verify_db_backup_password' => ['encrypt_password', 'time_point_uuid'],
            'delete_db_backup_time_point' => ['time_point_list', 'backup_db_list', 'db_type'],  // 删除数据库备份时间点
            'get_config_file_content' => ['time_point_uuid', 'db_type'],  // get_config_file_content
            'parse_config_file_content' => [
                'timepoint_uuid', 'new_instance_name', 'oracle_home_path', 'oracle_base_path', 'db_type',
            ],
        ];
    }

    /**
     * 验证备份数据库信息
     * @param array  $backupDbList 备份数据库信息
     * @param string $rules        验的规则
     * @param array  $data         请求的全部数据
     * @param string $field        验证字段
     * @return bool|string
     */
    protected function sceneBackupDbList(array $backupDbList, string $rules, array $data, string $field)
    {
        foreach ($backupDbList as $index => $item) {
            $ret = $this->checkBackupDbItem($item);
            if (true !== $ret) {
                return sprintf($ret, $field . "[$index]");
            }
        }
        return true;
    }

    /**
     * 验证需要删除数据库详情
     * @param array $item 数据库详情
     * @return bool|string
     */
    private function checkBackupDbItem(array $item)
    {
        $requireAttrs = [
            'job_uuid', 'instance_name', 'db_name', 'agent_uuid',
        ];

        foreach ($requireAttrs as $requireAttr) {
            if (!isset($item[$requireAttr])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', $requireAttr);
            }
        }
        return true;
    }
}
