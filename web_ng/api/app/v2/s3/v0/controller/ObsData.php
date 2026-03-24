<?php

namespace app\v2\s3\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          对象存储 -- 备份数据管理
 * @auther       chengjiafu@vinchin.com
 * @date         2023/11/3 9:36
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsData extends AuthBase
{
    /**
     * 获取对象存储备份时间点列表
     * @return void
     */
    public function getObsTimePointGrid()
    {
        // 1 参数验证
        $this->checkParams('obs_timepoint_grid');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getObsTimePointGrid($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 搜索对象存储备份时间点
     * @return void
     */
    public function searchObsTimePoint()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->searchObsTimePoint($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 批量删除备份时间点
     * @return void
     */
    public function deleteSelectedTimePoints()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteSelectedTimePoints($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 添加备注
     * @return null
     */
    public function remarkTimePoint()
    {
        $result = $this->logic()->remarkTimePoint($this->param);

        if ($result) {
            return $this->success(xphp_get_lang('UI_COPY_ADD_REMARK_SUCCESS'), $result);
        } else {
            return $this->error(xphp_get_lang('UI_COPY_ADD_REMARK_FAILED'), $result);
        }
    }

    public function addStar()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->addStar($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    public function deleteStar()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteStar($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }
}