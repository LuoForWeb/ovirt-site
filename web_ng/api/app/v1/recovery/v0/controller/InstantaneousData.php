<?php

namespace app\v1\recovery\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          瞬时恢复快照点管理
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:34
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class InstantaneousData extends AuthBase
{
    /**
     * 创建瞬时恢复快照点
     * @return json
     */
    public function createInstancePonit()
    {
        // 参数验证
        $this->checkParams('instance_points');

        // 逻辑层转发
        $return = $this->logic()->createInstancePonit($this->param);

        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 瞬时恢复快照点之左边的树结构
     * @return json
     */
    public function instantanceTree()
    {

        // 逻辑层转发
        $return = $this->logic()->instantanceTree($this->param);

        return $this->success('', $return);
    }

    /**
     * 瞬时恢复快照点之具体的时间点列表
     * @return json
     */
    public function instantancePoints()
    {
        // 参数验证
        //$this->checkParams('points');
        // 逻辑层转发
        $return = $this->logic()->instantancePoints($this->param);

        return $this->success('', $return);
    }

    /**
     * 瞬时恢复快照点之删除时间点
     * @return json
     */
    public function instantancePointsDel()
    {
        // 参数验证
       // $this->checkParams('points_del');
        // 逻辑层转发
        $return = $this->logic()->instantancePointsDel($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 瞬时恢复快照点之删除备份时间点数据
     * @return json
     */
    public function delInstantanceTree()
    {
        // 参数验证
       // $this->checkParams('points_del');
        // 逻辑层转发
        $return = $this->logic()->delInstantanceTree($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 获取瞬时恢复任务对应的时间点。迁移需要用
     * @return json
     */
    public function instantancePoint()
    {
        // 参数验证
        $this->checkParams('job_points');
        // 逻辑层转发
        $return = $this->logic()->instantancePoint($this->param);

        return $this->success('', $return);
    }
}
