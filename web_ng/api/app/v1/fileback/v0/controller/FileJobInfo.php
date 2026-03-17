<?php

namespace app\v1\fileback\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件任务信息 logic
 * @author       wuxian@vinchin.com
 * @date         2025/8/27 15:20
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
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
    public function getBasicInfo()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->getBasicInfo($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 下载跳过文件
     * @return null
     */
    public function downLoadPassFile()
    {
        $result = $this->logic()->downLoadPassFile($this->param);
        return $result;
    }

    /**
     * 获取对象列表-客户端列表、nas设备、hadoop集群、对象存储
     * @return json
     */
    public function getObjectList()
    {
        $return = $this->logic()->getObjectList($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }
}
