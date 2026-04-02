<?php

/**
 * 一些授权信息相关配置信息
 */

return [

    //lisence info
    'LISENCE_INFO' => array(
        'thumbprintFileName' => 'thumbprint.txt',  //指纹文件名
        //下载文件方式
        'uploadfile' => array(
            'name' => 'files',
            'suffixes' => 'key',
            'size' => 204800,
            'type' => 'application/octet-stream'
        ),
        'authflag' => array(
            'authorized' => 1,      //已授权
            'unauthorized' => 2,    //未授权
            'expire' => 3,          //授权过期
            'invalid' => 4,         //授权异常
        ),
        //授权方式
        'type' => array(
            'host' => 1,   //按主机授权
            'cpu' => 2,    //按CPU
            'storage' => 3,//按容量
            'vm' => 4,     //按虚拟机个数
        ),
        //license文件类型
        'filetype' => array(
            'license' => 1, //系统授权文件后缀
            'service' => 2, //服务授权文件后缀
        ),
        //服务授权文件路径
        'serviceFilePath' => '/opt/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/vinsc.service',  //这里取名service,有混淆的意思.
        //服务类型
        'servertype' => array(
            '---',
            '软件标准服务',
            '软件白金服务',
        ),
        //rootType类型
        'roottype' => array(
            'storage' => 1, //容量
            'timereal' => 2, //定时实时
            'other' => 3, //其他
        )
    ),

    //lisence类型
    'TRIAL_TYPE' => array(
        'UNKNOWN' => 0,
        'TRIAL' => 1,           //试用授权
        'NORMAL' => 2,          //正式授权
    ),

    //6.0授权调整后的授权容量/数量类型
    'LICENSE_BIG_TYPE' => array(
        'STORAGE' => 1,     //容量授权
        'NUM' => 2,         //数量授权
        'MIX_STORAGE' => 3  //混合存储空间授权
    ),

    //卷CDP授权
    'VOLCDP_LISENCE_TYPE' => array(
        'NUM' => 1,         //数量授权
        'STORAGE' => 2,     //容量授权
    ),

];
