<?php

/**
 * 病毒引擎
 */

return [

    ### 备份节点
    'virus' => [ // 模块名
        ### 备份节点
        'Virus' => [ // 类名
            'virus' => [
                'get'   => 'getVirusList', //获取病毒库
                'post'       => 'addVirous',//添加病毒库
                'delete'   => 'deleteVirusList', //删除病毒库
            ],
            'virus_operate' => [
                'post'   => 'applyVirusList', //病毒库操作
            ],
            'virus_update' => [
                'post'   => 'updateVirus', //病毒库操作
            ],
            'virus_detail' => [
                'get'   => 'getVirus', //获取病毒库
            ],
            'virus_file' => [
                'post'   => 'getVirusFile', //获取病毒库
            ],
            'virus_authorization_file' => [
                'post'   => 'uploadAuthorizationFile', // 上传授权文件
            ],
        ],
    ]
];
