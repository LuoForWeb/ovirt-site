<?php

namespace app\v1\s3\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          对象存储 - 恢复管理 controller
 * @auther       chengjiafu@vinchin.com
 * @date         2023/10/11 18:25
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsRecover extends AuthBase
{

    /**
     * 获取对象存储备份时间点树
     * @return json
     */
    public function getObsDataTree()
    {

        // 2 请求转发到logic去处理
        $return = $this->logic()->getObsDataTree($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 获取文件备份时间点
     * @return string
     */
    public function getSyncBackupTimePoint()
    {
        // 1 参数验证
        $this->checkParams('files_async_timepoint');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getSyncBackupTimePoint($this->param);

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

    /**
     * 检验对象数据加密密码的正确性
     * @return null
     */
    public function checkObsEncryptPass()
    {
        // 1 参数验证
        $this->checkParams('files_check_encrypt');

        // 2 请求转发到logic去处理
        $return = $this->logic()->checkObsEncryptPass($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 获取恢复文件列表
     * @return null
     */
    public function getRecoverDir()
    {
        // 1 参数验证
        $this->checkParams('files_get_backup_dir');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoverDir($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 获取恢复路径树
     * @return null
     */
    public function getRecoverPathTree()
    {
        // 1 参数验证
        $this->checkParams('obs_recover_path_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoverPathTree($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 创建搜索消息
     * @return void
     */
    public function createSearchJob()
    {
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
     * 获取恢复对象存储树
     * @return void
     */
    public function getRecoverObsTree()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->getRecoverObsTree($this->param);

        // 3 返回结果到用户
        return $this->success('', $return);
    }

    /**
     * 创建恢复任务
     * @return json
     */
    public function createRecoverJob()
    {
        // 2 请求转发到logic去处理
        $return = $this->logic()->createRecoverJob($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 获取默认的恢复任务名
     *
     * @param array $params
     * @return void
     */
    public function getDefaultRecoverTaskName()
    {
        $result = $this->logic()->getDefaultRecoverTaskName($this->param);
        if ($result) {
            $this->success('', $result);
        }
        $this->error('', $result);
    }
}