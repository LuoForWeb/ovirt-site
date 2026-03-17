<?php

/**
* 虚拟机的一些配置信息
 */

return [
    //虚拟实验室状态
    "VIRTUAL_LAB_STATUS" => array(
        'UNKNOWN' => 0, //未知
        'DEPLOYMENT' => 1,     //部署中
        'ONLINE' => 2,  //在线
        'OFFLINE' => 3,     //离线
        'ABNORMAL' => 4,  //异常
        'ERROR' => 5,  //错误
        'MODIFY' => 6,  //修改中
        'DEPLOY' => 7  //已部署
    ),
];
