<?php

namespace app\v1\file\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileRecover extends AuthBase
{
    /**
     * 文件恢复搜索 创建搜索消息
     * @return json
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
     * 文件恢复搜索 停止搜索
     * @return json
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

    /**
     * 文件恢复搜索 获取搜索结果
     * @return json
     */
    public function getRecoSearchInfo()
    {
        // 1 参数验证
        $this->checkParams('files_get_search');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoSearchInfo($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 得到文件备份时间点树(灾备中心/文件备份数据)
     * @return json
     */
    public function getFileDataTree()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->getFileDataTree($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
    * 校验文件数据加密密码正确性
     * @return string
     */
    public function checkFSEncryptPass()
    {
        // 1 参数验证
        $this->checkParams('files_check_encrypt');

        // 2 请求转发到logic去处理
        $return = $this->logic()->checkFSEncryptPass($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 得到备份文件列表
     * @return string
     */
    public function getBackupFileDir()
    {
        // 1 参数验证
        $this->checkParams('files_get_backup_dir');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getBackupFileDir($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 异步获取文件时间点
     * @return string
     */
    public function getSyncFileTimepoint()
    {
        // 1 参数验证
        $this->checkParams('files_async_timepoint');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getSyncFileTimepoint($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 获取恢复任务文件备份树
     * @return string
     */
    public function getRecoverHostTree()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoverHostTree($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 获取文件客户端下的目录树
     * @return void
     */
    public function getRecoverPathTree() {
        // 1 参数验证
        $this->checkParams('file_dir_tree');

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
        $this->checkParams('files_recover_job');

        // 2 请求转发到logic去处理
        $return = $this->logic()->createRecoverJob($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 得到文件恢复任务名
     * @return string
     */
    public function getFileRecoverTaskName()
    {
        $return = $this->logic()->getFileRecoverTaskName($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error('', $return);
    }


}
