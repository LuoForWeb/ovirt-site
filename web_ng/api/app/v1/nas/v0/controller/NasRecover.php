<?php

namespace app\v1\nas\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          NAS -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasRecover extends AuthBase
{
    /**
     * 得到nas时间点树
     * @return json
     */
    public function getNasDataTree()
    {
        // 1 参数验证
        // $this->checkParams('nas_get_data_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getNasDataTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到nas恢复文件树
     * @return json
     */
    public function getRecoveryNasDir()
    {
        // 1 参数验证
        $this->checkParams('nas_get_recovery_dir');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoveryNasDir($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 异步获取nas时间点
     * @return json
     */
    public function getSyncNasTimepoint()
    {
        // 1 参数验证
        $this->checkParams('nas_async_timepoint');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getSyncNasTimepoint($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到nas设备下的恢复文件目录树
     * @return json
     */
    public function getRecoverPathTree()
    {
        // 1 参数验证
        $this->checkParams('nas_recover_path_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoverPathTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 创建恢复任务
     * @return json
     */
    public function createRecoverJob()
    {
        // 1 参数验证
        $this->checkParams('nas_recover_job');

        // 2 请求转发到logic去处理
        $return = $this->logic()->createRecoverJob($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 获取恢复任务NAS设备树
     * @return null
     */
    public function getNasBackupTree()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->getNasBackupTree($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 创建搜索消息
     * @return void
     */
    public function createSearchJob()
    {
        // 1 参数验证
        $this->checkParams('files_create_search');

        // 2 请求转发到logic去处理
        $return = $this->logic()->createSearchJob($this->param);

        // 3 返回结果到用户
        if ($return['result']) {
            return $this->success('', ['thread_uuid' => $return['msg']]);
        }

        return $this->error($return['msg'], [], $return['code']);
    }

    /**
     * 获取搜索结果
     * @return null
     */
    public function getSearchInfo()
    {
        // 1 参数验证
        $this->checkParams('files_get_search');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getSearchInfo($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 对象存储恢复 停止搜索
     * @return null
     */
    public function stopSearchJob()
    {
        // 1 参数验证
        $this->checkParams('files_stop_search');

        // 2 请求转发到logic去处理
        $return = $this->logic()->stopSearchJob($this->param);

        // 3 返回结果到用户
        if ($return['result']) {
            return $this->success();
        }

        return $this->error($return['msg'], [], $return['code']);
    }
}