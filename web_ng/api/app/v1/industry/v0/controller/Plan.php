<?php

namespace app\v1\industry\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          行业合规方案相关控制器
 * @author       wanggongxi@vinchin.com
 * @date         2025/9/29 10:33
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Plan extends AuthBase
{
    /**
     * 方案列表/详情
     * @return string
     */
    public function index()
    {

        if (!empty($this->param['plan_uuid'])) {
            // 参数验证
            $this->checkParams('view');
            $return = $this->logic()->viewPlan($this->param['plan_uuid']);
            if ($return['code'] == 0) {
                return $this->success('', $return['msg']);
            }
        } else {
            // 参数验证
            $this->checkParams('list');
            $return = $this->logic()->getList($this->param);

            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 新建、修改方案
     * @return string
     */
    public function submitPlan()
    {
        // 参数验证
        $this->checkParams('save');
        $return = $this->logic()->submitPlan($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 复制方案
     * @return string
     */
    public function copyPlan()
    {
        // 参数验证
        $this->checkParams('make');
        $return = $this->logic()->copyPlan($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }
}
