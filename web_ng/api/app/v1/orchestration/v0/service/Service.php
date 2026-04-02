<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 任务编排通信service
 * @Date: 2024-03-20 14:31:56
 * @LastEditTime: 2024-04-10 14:33:25
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

namespace app\v1\orchestration\v0\service;

use app\v1\common\service\Base;

class Service extends Base
{
    /**
     * @description: 发送编排计划消息
     * @param {*} $node_uuid
     * @param {*} $opName
     * @param {*} $list
     * @return {*}
     */
    public function operateOrchestrationPlan($node_uuid, $opName, $list)
    {
        return $this->mbStrategyMsg($node_uuid, $opName, json_encode($list));
    }
}
