<?php

namespace app\v1\file\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          文件 -- 之任务操作 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileJobController extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start_type' => ['require', 'number', 'in' => [0,1,2,3]],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 启动任务
            'start' => ['start_type'],
        ];
    }
}
