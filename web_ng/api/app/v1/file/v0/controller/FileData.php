<?php

namespace app\v1\file\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileData extends AuthBase
{
    /**
     * 获取文件时间点列表信息
     * @return json
     */
    public function getFsTimepointGrid()
    {
        // 1 参数验证
        $this->checkParams('files_timepoint_grid');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getFsTimepointGrid($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 搜索文件时间点
     * @return json
     */
    public function searchFsTimepoint()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->searchFsTimepoint($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 删除备份时间点
     * @return json
     */
    public function deleteTimepoint()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteTimepoint($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 删除批量备份时间点
     * @return json
     */
    public function deleteSelectTimepoint()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteSelectTimepoint($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 获取文件主机列表
     * @return json
     */
    public function getDetailsFs()
    {
        // 1 参数验证
        $this->checkParams('files_detail_list');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getDetailsFs($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 文件主机列表-删除单个主机
     * @return json
     */
    public function deleteSelectFs()
    {
        // 1 参数验证
        $this->checkParams('files_select_fs');

        // 2 请求转发到logic去处理
        $return = $this->logic()->deleteSelectFs($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 文件主机列表-启动单个主机备份
     * @return json
     */
    public function startSelectFs()
    {
        // 1 参数验证
        $this->checkParams('files_select_fs');

        // 2 请求转发到logic去处理
        $return = $this->logic()->startSelectFs($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }

    /**
     * 下载任务告警日志
     * @return json
     */
    public function downLoadPassFile()
    {
        // 参数验证
        $this->checkParams('files_download_pass');
        // 逻辑层转发
        $return = $this->logic()->downLoadPassFile($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }
}
