<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          系统安全配置 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/12/19 16:17
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Safe extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'history_days' => ['require', 'number'],
            'job_log_days' => ['require', 'number'],
            'system_log_days' => ['require', 'number'],
            'job_alarm_dayas' => ['require', 'number'],
            'system_alarm_days' => ['require', 'number'],
            'out_time' => ['require', 'number'],
            'faild_count' => ['require', 'number'],
            'faild_lock_time' => ['require', 'number'],
            'password_time' => ['require', 'number'],
            'passlength' => ['require', 'number'],
            'passcomplexity' => ['require', 'number'],
            'protect_flag' => ['boolean'],
            'firewall_flag' => ['boolean'],
            'nfs_flag' => ['boolean'],
            'port3306_flag' => ['boolean'],
            'port8080_flag' => ['boolean'],
            'rpcbind_flag' => ['boolean'],
            'ssh_flag' => ['boolean'],
            'node_uuid' => ['require'],
            'power_type' => ['require', 'number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 保存数据安全配置
            'set_datas' => ['history_days', 'job_log_days', 'system_log_days', 'job_alarm_dayas', 'system_alarm_days'],
            // 保存账号安全配置
            'set_accounts' => [
                'out_time', 'faild_count', 'faild_lock_time', 'password_time', 'passlength', 'passcomplexity'
            ],
            // 保存存储安全配置
            'set_storages' => ['protect_flag'],
            // 保存系统安全配置
            'set_os' => ['firewall_flag', 'nfs_flag', 'port3306_flag', 'port8080_flag', 'rpcbind_flag', 'ssh_flag'],
            // 关机/重启
            'power' => ['node_uuid', 'power_type'],
        ];
    }
}
