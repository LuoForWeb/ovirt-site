<?php

namespace app\v1\job\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          任务信息 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobInfo extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'jobs_uuid' => ['require'],
            'history_uuid' => ['require'],
            'offset' => ['require', 'number'],
            'limit' => ['require', 'number'],
            'type' => ['require', 'number'],
            'job_name' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'get_job'       => ['jobs_uuid'],       // 获取当前任务详情
            'get_job_list'  => ['offset', 'limit'], // 获取当前任务列表
            'history'       => ['history_uuid'],    // 获取历史任务
            'job_history'   => ['jobs_uuid', 'offset', 'limit'],       // 获取单个任务历史列表
            'get_history_list'  => ['offset', 'limit'], // 获取历史任务列表
            'get_module'    => ['offset', 'limit'], // 获取模块列表
            'get_name'      => ['job_name'], // 根据任务名或语言包获取新的任务名
            'jobs_strategy_list'      => ['type'], // 初始化策略选择下拉框
        ];
    }
}
