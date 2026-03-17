<?php

namespace app\v1\verification\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据验证CDM -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VerificationJobInfo extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取数据验证任务详情信息
     * @return json
     */
    public function getVerifyJobDetails()
    {
        // 1 参数验证


        // 2 请求转发到logic去处理
        $return = $this->logic()->getVerifyJobDetails($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 获取数据验证对象列表
     * @return json
     */
    public function getVerifyObjectList()
    {
        // 1 参数验证

        // 2 请求转发到logic去处理
        $return = $this->logic()->getVerifyObjectList($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success("", $return);
    }

    /**
     * 获取数据验证任务单个对象详情信息
     * @return json
     */
    public function getVerifyObjectInfo()
    {


        // 2 请求转发到logic去处理
        $return = $this->logic()->getVerifyObjectInfo($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 获取历史任务验证报告
     * @return json
     */
    public function getVerifyJobReport()
    {


        // 2 请求转发到logic去处理
        $return = $this->logic()->getVerifyJobReport($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 发送验证报告邮件
     * @return json
     */
    public function sendVerifyEmail()
    {


        // 2 请求转发到logic去处理
        $return = $this->logic()->sendVerifyEmail($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }
}
