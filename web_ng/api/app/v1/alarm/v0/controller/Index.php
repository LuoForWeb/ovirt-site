<?php

namespace app\v1\alarm\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          告警消息
 * @author       wanggongxi@vinchin.com
 * @date         2025/7/24 11:28
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Index extends AuthBase
{
    /**
     * 获取系统告警 得到首页通知信息、左侧菜单提示、顶部消息通知
     * @return void
     */
    public function getSurveyNoticeInfo()
    {

        $return = $this->logic()->getSurveyNoticeInfo($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
}
