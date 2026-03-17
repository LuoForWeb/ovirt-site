<?php

namespace app\v2\system\v0\controller;

use app\v2\common\controller\Base;

/**
 * note          网络管理 控制器
 * @author       wanggongxi@vinchin.com
 * @date         2026/3/12 17:16
 * @version      1.0.0
 * @copyright    Copyright 2026 vinchin.com
 */
class Network extends Base
{

    /**
    * 获取所有网卡/单个网卡信息
     * @return json
     */
    public function getNetwork()
    {
       if (!empty($this->param['ip_uuid'])) {
            // 获取单个网卡信息

       } else {
           // 获取所有网卡列表
       }

        $this->success('', $results);
    }
}
