<?php

namespace app\v1\nas\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          NAS -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasData extends AuthBase
{
    /**
     * 获取nas时间点列表信息
     * @return json
     */
    public function getNasTimepointGrid()
    {
        // 1 参数验证
        $this->checkParams('nas_timepoint_grid');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getNasTimepointGrid($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 搜索nas时间点
     * @return json
     */
    public function searchNasTimepoint()
    {

        //  请求转发到logic去处理
        $return = $this->logic()->searchNasTimepoint($this->param);

        //  返回结果到用户
        $this->success('', $return);
    }

    /**
     * 删除备份时间点
     * @return json
     */
    public function deleteTimepoint()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteTimepoint($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 删除批量备份时间点
     * @return json
     */
    public function deleteSelectTimepoint()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteSelectTimepoint($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到NAS模块任务基本信息
     * @return json
     */
    public function getNasBasicInfo()
    {
        // 1 参数验证
        $this->checkParams('nas_basic_info');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getNasBasicInfo($this->param);

        if (empty($return)) {
            $this->error();
        }

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到NAS模块任务基本信息
     * @return json
     */
    public function getDetailsNas()
    {
        // 1 参数验证
        $this->checkParams('nas_detail_list');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getDetailsNas($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }
}
