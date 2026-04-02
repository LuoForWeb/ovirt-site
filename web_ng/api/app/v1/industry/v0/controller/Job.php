<?php

namespace app\v1\industry\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          行业合规任务详情相关控制器
 * @author       wanggongxi@vinchin.com
 * @date         2025/3/13 18:12
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */

class Job extends AuthBase
{
    /**
     * 获取数据验证获取任务详情
     * @return json
     */
    public function getVerifyJobDetail()
    {

        $return = $this->logic()->getVerifyJobDetail($this->param['job_uuid']);

        return $this->success('', $return);
    }

    /**
     * 获取数据验证对象列表
     * @return json
     */
    public function getVerifyObjectList()
    {

        $return = $this->logic()->getVerifyObjectList($this->param);

        return $this->success('', $return);
    }

    /**
     * 切换设备获取右边的详细信息
     * @return json
     */
    public function getClientInfos()
    {

        $return = $this->logic()->getClientInfos($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 切换设备保存报告的某些信息
     * @return json
     */
    public function updateClientInfos()
    {

        $return = $this->logic()->updateClientInfos($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }
}
