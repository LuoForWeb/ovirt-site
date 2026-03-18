<?php

namespace app\v1\nas\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          NAS -- 之数据管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasData extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['min' => 0],
            'limit' => ['require', 'number', 'min' => 0],
            'job_uuid' => ['require'],
            'agent_uuid' => ['require'],
            'job_name' => ['require'],
            'point_list' => ['require', 'array'],
            'timepoint_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取nas时间点列表信息
            'nas_timepoint_grid' => ['offset', 'limit', 'agent_uuid', 'job_uuid'],
            // 删除批量备份时间点
            'nas_batch_timepoint' => ['point_list'],
            // 删除备份时间点
            'nas_timepoint' => ['timepoint_uuid', 'job_uuid', 'agent_uuid'],
            // 得到NAS模块任务基本信息
            'nas_basic_info' => ['job_uuid'],
            // 获取nas设备列表
            'nas_detail_list' => ['offset', 'limit', 'job_uuid'],
        ];
    }
}