<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统配置上传
 * @author       wuyihang@vinchin.com
 * @date         2024/12/12 20:34
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Settings extends AuthBase
{
    /**
    * 上传系统配置
     * @return json
     */
    public function uploadSysSetting()
    {
        $return = $this->logic()->uploadSysSetting($this->param);
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }

    /**
    * 获取系统配置
     * @return json
     */
    public function getSysSetting()
    {
        $return = $this->logic()->getSysSetting($this->param);
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }

    /**
     * 设置可视化配置
     * @return json
     */
    public function getDefaultVisualInfo()
    {
        $return = $this->logic()->getDefaultVisualInfo($this->param);
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }

    /**
     * 修改可视化配置
     * @return json
     */
    public function setVisualInfo()
    {
        $return = $this->logic()->setVisualInfo($this->param);
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }
}
