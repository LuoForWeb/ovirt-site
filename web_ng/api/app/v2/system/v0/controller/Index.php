<?php

namespace app\v2\system\v0\controller;

use app\v2\common\controller\Base;

/**
 * note          system
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:02
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends Base
{

    /**
     * 获取系统的基本信息
     * @return void
     */
    public function getConfig()
    {
        $data = $this->logic()->getConfig();
        $this->success('', $data);
    }
}
