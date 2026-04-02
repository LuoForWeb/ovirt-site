<?php

/**
 * alarm的一些公共必须加载的配置信息
 */

return [
    //告警类型
    'ALARM' => array(
        'notice' => 1,  //提示错误
        'general' => 2, //一般错误
        'serious' => 3, //严重错误
        'fatal' => 4,   //致命错误
    ),
];
