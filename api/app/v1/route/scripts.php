<?php

/**
* 任务相关的路由定义
 */

return [
    
    //脚本管理
    'scripts_manager' => [
        'ScriptsManager' => [
            'scripts' => [
                //获取脚本列表
                //获取单个脚本信息
                'get' => 'getScript',
                //添加脚本
                'post' => 'addScript',
                //删除脚本
                'delete' => 'deleteScript',
            ],
        ],
        
        
    ],
    

];
