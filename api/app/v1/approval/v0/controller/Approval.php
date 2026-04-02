<?php

namespace app\v1\approval\v0\controller;

use app\v1\common\controller\Base;

/**
 * note          审批流 controller
 * @author       wuyihang@vinchin.com
 * @date         2024/7/30 14:22
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Approval extends Base
{
    /**
    * 获取审批流列表
     * @return json
     */
    public function getApprovalList()
    {
        // 逻辑转发
        $record = $this->logic()->getApprovalList($this->param);

        if ($record) {
            // 返回数据
            $this->success('', $record);
        }
        $this->error('', $record, $record['code']);
    }

    /**
    * 添加审批流
     * @return json
     */
    public function addApproval()
    {
        // 逻辑转发
        $result = $this->logic()->addApproval($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
    * 修改审批流
     * @return json
     */
    public function modifyApproval()
    {
        // 逻辑转发
        $result = $this->logic()->modifyApproval($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
    * 禁用/启用审批流
     * @return json
     */
    public function enableOrDisableApproval()
    {
        // 逻辑转发
        $result = $this->logic()->enableOrDisableApproval($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
     * 删除审批流
     * @param array $param 参数
     * @return boolean
     */
    public function delApproval()
    {
        // 逻辑转发
        $result = $this->logic()->delApproval($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
     * 获取分类列表
     * @param array $param 参数
     * @return array
     */
    public function getClassifyList()
    {
        // 逻辑转发
        $result = $this->logic()->getClassifyList($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
     * 添加分类
     * @param array $param 参数
     * @return boolean
     */
    public function addClassify()
    {
        // 逻辑转发
        $result = $this->logic()->addClassify($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
     * 修改分类
     * @param array $param 参数
     * @return boolean
     */
    public function modifyClassify()
    {
        // 逻辑转发
        $result = $this->logic()->modifyClassify($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
     * 删除分类
     * @param array $param 参数
     * @return boolean
     */
    public function delApprovalClassify()
    {
        // 逻辑转发
        $result = $this->logic()->delApprovalClassify($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }

    /**
     * 启用或禁用分类
     * @param array $param 参数
     * @return boolean
     */
    public function enableOrDisableClassify()
    {
        // 逻辑转发
        $result = $this->logic()->enableOrDisableClassify($this->param);

        if ($result) {
            // 返回数据
            $this->success('', $result);
        }
        $this->error('', $result, $result['code']);
    }
}