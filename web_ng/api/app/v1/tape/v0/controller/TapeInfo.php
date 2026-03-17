<?php

namespace app\v1\tape\v0\controller;

use app\v1\common\controller\AuthBase;
use app\v1\tape\v0\logic;

/**
 * note         磁带设备信息
 * @author      wuyihang@vinchin.com
 * @date        2023/9/1 10:26
 * @version     1.0.0
 * @copyright   Copyright 2023 vinchin.com
 */
class TapeInfo  extends AuthBase
{
    /**
     * 获取磁带库信息
     * @return json
     */
    public function getTapeLibInfo()
    {
        //参数验证
        $this->checkParams('get_tape_lib_info');
        //逻辑转发
        $return = $this->logic()->getTapeLibInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带信息
     * @return json
     */
    public function getTapeCarriageInfo()
    {
        //参数验证
        $this->checkParams('get_tape_lib_info');
        //逻辑转发
        $return = $this->logic()->getTapeCarriageInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 修改磁带
     * @return json
     */
    public function modifyTapeCarriageInfo()
    {
        //参数验证
        $this->checkParams('modify_info');
        //逻辑转发
        $return = $this->logic()->modifyTapeCarriageInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 修改磁带备份集
     * @return json
     */
    public function modifyTapeBackupSet()
    {
        //参数验证
        $this->checkParams('modify_info');
        //逻辑转发
        $return = $this->logic()->modifyTapeBackupSet($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取驱动器信息
     */
    public function getTapeDriverInfo()
    {
        //参数验证
        $this->checkParams('driver');
        //逻辑转发
        $return = $this->logic()->getTapeDriverInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带组信息
     */
    public function getTapeGroupInfo()
    {
        //参数验证
        $this->checkParams('group');
        //逻辑转发
        $return = $this->logic()->getTapeGroupInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带组策略信息
     */
    public function getTapeGroupStrategy()
    {
        //逻辑转发
        $return = $this->logic()->getTapeGroupStrategy($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取备份集信息
     */
    public function getBackupSetInfo()
    {

        //逻辑转发
        $return = $this->logic()->getBackupSetInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带备份集树
     */
    public function getTimePoint()
    {

        //逻辑转发
        $return = $this->logic()->getTimePoint($this->param['backup_set_id']);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带任务
     */
    public function getTapeJobInfo()
    {

        //逻辑转发
        $return = $this->logic()->getTapeJobInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带监控信息
     */
    public function getTapeMonitorInfo()
    {
        //逻辑转发
        $return = $this->logic()->getTapeMonitorInfo($this->param);
        //返回数据
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取磁带备份集模块
     */
    public function getBackupModule()
    {

        //参数验证
        $this->checkParams('tapes_modules');

        //逻辑转发
        $return = $this->logic()->getBackupModule($this->param);

        //返回数据
        $this->success('', $return);
    }

    /**
     * 获取磁带备份集模块树
     */
    public function getBackupTree()
    {

        //参数验证
        $this->checkParams('tapes_modules');

        //逻辑转发
        $return = $this->logic()->getBackupTree($this->param);

        //返回数据
        $this->success('', $return);
    }

    /**
     * 获取磁带备份对象下的时间点列表
     */
    public function getBackupTimepoint()
    {

        //参数验证
        $this->checkParams('tapes_modules');

        //逻辑转发
        if (!empty($this->param['export'])) {
            // 导出
            if ($this->param['export_type'] == 'csv') {
                return $this->logic()->exportBackupTimepointWithBatchCsv($this->param);
            }
            return $this->logic()->exportBackupTimepointWithBatch($this->param);
        }
        $return = $this->logic()->getBackupTimepoint($this->param);

        //返回数据
        $this->success('', $return);
    }
}
