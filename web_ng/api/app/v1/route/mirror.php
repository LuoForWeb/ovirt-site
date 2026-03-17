<?php

/**
 * 镜像管理
 */

return [

    ### 备份节点
    'mirror' => [ // 模块名
        ### 备份节点
        'Mirror' => [ // 类名
            'mirror' => [
                'get'   => 'getMirrorList', //获取镜像管理
                'post'       => 'addMirror',//添加镜像
                'delete'   => 'deleteMrriorList', //删除镜像
            ],
            'mirror_size' => [
                'get'   => 'sizeMirror', //清理镜像
            ],
        ],
    ]
];
