<?php

namespace app\v1\verification\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据验证CDM -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VirtualLab extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加虚拟演练室
     * @return json
     */
    public function addVirtualLab()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->addVirtualLab($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success($return);
    }

    /**
     * 修改虚拟演练室
     * @return json
     */
    public function editVirtualLab()
    {


        // 2 请求转发到logic去处理
        $return = $this->logic()->editVirtualLab($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success($return);
    }

    /**
     * 删除虚拟演练室
     * @return json
     */
    public function deleteVirtualLab()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteVirtualLab($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success($return);
    }

    /**
     * 获取虚拟演练室信息
     * @return json
     */
    public function getVirtualLab()
    {
        // 参数验证
        if (empty($this->param['lab_uuid'])) {
            // 获取列表

            $records = $this->logic()->getVirtualLabList($this->param);
        } else {
            // 获取单个详情
            $records = $this->logic()->getVirtualLabDetail($this->param['lab_uuid']);
        }

        $this->success('', $records);
    }

    /**
     * 自动生成隔离网络
     * @return json
     */
    public function createIsolationNetwork()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->createIsolationNetwork($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success($return['msg'], $return['data']);
    }

    /**
     * 刷新虚拟演练室
     * @return json
     */
    public function refreshVirtualLab()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->refreshVirtualLab($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success($return);
    }

    /**
     * 获取虚拟演练室可以使用名称
     * @return json
     */
    public function getCreateLabName()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->getCreateLabName($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 验证演练室名称是否可用
     * @return json
     */
    public function labnameAvailable()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->labnameAvailable($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }

    /**
     * 获取创建虚拟演练室所选择宿主机
     * @return json
     */
    public function getVirtualLabHost()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->getVirtualLabHost($this->param);

        // 3 返回结果到用户
        if (!$return){
            $this->error();
        }
        $this->success('', $return);
    }



}
