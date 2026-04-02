<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          全局限速策略管理控制器
 * @author       wanggongxi@vinchin.com
 * @date         2023/9/11 14:33
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class SpeedLimit extends AuthBase
{
    /**
     * 列表管理
     * @return json
    */
    public function getList()
    {
        $this->checkParams('lists');

        $return = $this->logic()->getList($this->param);

        $this->success('', $return);
    }

    /**
     * 添加
     * @return json
     */
    public function addSpeed()
    {

        $this->checkParams('add');

        $return = $this->logic()->saveSpeed($this->param);

        $this->success('', $return);
    }

    /**
     * 修改
     * @return json
     */
    public function editSpeed()
    {
        $this->param['strategy_uuid'] = $this->param['global_speed_edit_uuid'];
        $this->checkParams('edit', '', $this->param);

        $return = $this->logic()->saveSpeed($this->param);

        $this->success('', $return);
    }

    /**
     * 获取详情
     * @return json
     */
    public function viewSpeed()
    {
        $this->param['strategy_uuid'] = $this->param['global_speed_detail_uuid'];
        $this->checkParams('view', '', $this->param);

        $return = $this->logic()->viewSpeed($this->param);

        if (empty($return)) {
            $this->error('');
        }

        $this->success('', $return);
    }

    /**
     * 删除
     * @return json
     */
    public function delSpeed()
    {

        $this->checkParams('del');

        $return = $this->logic()->delSpeed($this->param);

        $this->success('', $return);
    }

    /**
     * 分发
     * @return json
     */
    public function sendSpeed()
    {
        $this->checkParams('send');

        $return = $this->logic()->sendSpeed($this->param);

        if (empty($return)) {
            $this->error();
        }
        $this->success();
    }

    /**
     * 获取可以分发的任务列表
     * @return json
     */
    public function getSpeedJob()
    {
        $this->checkParams('jobs');

        $return = $this->logic()->getSpeedJob($this->param);

        $this->success('', $return);
    }
    public function getGlobalSpeedName()
    {
        $return = $this->logic()->getGlobalSpeedName($this->param);

        $this->success('', $return);
    }
}
