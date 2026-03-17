<?php

namespace app\v1\completemachinevolcdp\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note          卷实时 --数据管理 logic
 * @author       jiangyongjie@vinchin.com
 * @date         2024-7-31 17:40:28
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Service extends Base
{
    /**
     * 手动添加集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function addClusterManual($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_ADD_CLUSTER';
        //想后台发消息
        //---测试结果----
        $info = array(
            'result' => true,
            'message' => $params,
            'error_code' => 0,
        );
        return $info;
        //---测试结果----
        $mbResult = "";
        return $mbResult;
    }

}

