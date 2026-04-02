<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          资源组列表
 * @author       xiezhuowei@vinchin.com
 * @date         2016-05-23 下午15:06:44
 * @version      1.0.0
 * @copyright    Copyright 2016 vinchin.com
 */
class Group extends AuthBase
{
    /**
     * 获取资源组列表
     * @return void
     */
    public function getResource()
    {

        $this->checkParams('get-recourse');
        $info = $this->logic()->getResourceData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 删除资源
     * @return json
     */
    public function deleteResource()
    {
        $this->checkParams('recourse_delete');
        return $this->logic()->deleteResource($this->param);
    }

    /**
     * 添加资源
     * @return json
     */
    public function addResource()
    {
        $this->checkParams('recourse_add');
        return $this->logic()->addResource($this->param);
    }

    /**
     * 修改资源组
     * @return json
     */
    public function editResource()
    {
        $this->checkParams('recourse_edit');
        return $this->logic()->editResource($this->param);
    }

    /**
     * 获取资源详情
     * @return json
     */
    public function getDetail()
    {
        $this->checkParams('recourse_detail');
        $info = $this->logic()->getDetail($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取全部资源
     * @return json
     */
    public function getAllResource()
    {
        $this->checkParams('recourse_all');
        $info = $this->logic()->getAllResource($this->param);

        $this->success('', $info);
    }
}
