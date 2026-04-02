<?php

namespace app\v1\user\v0\service;

use app\v1\common\service\Base;

/**
 * note          服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * 用户资源转移
     * @param array $opname 操作码
     * @param array $params 参数
     * @return array
     */
    public function doTransfer($opname, $params = [])
    {

        return $this->mbNodeMsg($opname, $this->getLocalNodeUuid(), json_encode($params));
    }
}
