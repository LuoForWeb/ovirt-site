<?php

namespace app\v1\file\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件 -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileJobInfo extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 得到文件模块任务基本信息
     * @return json
     */
    public function getFsBasicInfo()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->getFsBasicInfo($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }
}
