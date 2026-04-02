<?php

namespace app\v1\recovery\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          细粒度恢复任务详情右边的文件等的验证
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:45
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class GraininessInfo extends Base
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
            'job_name' => ['require'],
            'job_uuid' => ['require'],
            'uuid' => ['require'],
            'password' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['offset', 'limit'],
            'get_name'      => ['job_name', 'job_uuid'], // 根据任务名或语言包获取新的任务名
            'look_pwd'      => ['job_uuid', 'uuid', 'password'], // 查看共享任务的访问密码
        ];
    }
}
