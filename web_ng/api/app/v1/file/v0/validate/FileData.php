<?php

namespace app\v1\file\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          文件 -- 之数据管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileData extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['require', 'number', 'min' => 0],
            'limit' => ['require','number', 'min' => 1],
            'agent_uuid' => ['require'],
            'job_uuid' => ['require'],
            'point_list' => ['require', 'array'],
            'timepoint_uuid' => ['require'],
            'fs_uuids' => ['require'],
            'path' => ['require'],
            'fsnodeuuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取文件时间点列表信息
            'files_timepoint_grid' => ['offset', 'limit', 'agent_uuid', 'job_uuid'],
            // 得到文件模块任务基本信息
            'files_basic_info'  =>  ['job_uuid'],
            // 获取文件主机列表
            'files_detail_list'  =>  ['offset', 'limit', 'job_uuid'],
            // 主机列表删除操作
            'files_select_fs'   => ['job_uuid', 'fs_uuids'],
            // 下载跳过文件
            'files_download_pass'   => ['node_uuid','history_uuid','agent_uuid'],
        ];
    }
}
