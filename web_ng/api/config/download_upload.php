<?php

/**
* 这是文件上传配置
 */

return [
    'mimes' => '', // 允许上传的文件MiMe类型
    'maxSize' => 20 * 1024 * 1024, // 5 * 1024 * 1024, // 上传的文件大小限制 (0-不做限制)
    'exts' => 'svg,jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,flv,mp4,csv,mp3,xls', // 允许上传的文件后缀
    'watermark' => './static/system/images/water.png',
    'autoSub' => true, // 自动子目录保存文件
    'subName' => array (
        'date',
        'Y-m-d'
    ), // 子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    'rootPath' => DATA_PATH . 'uploadfile/file/', // 保存根路径
    'savePath' => '', // 保存路径
    'saveName' => array (
        'uniqid',
        ''
    ), // 上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
    'saveExt' => '', // 文件保存后缀，空则使用原后缀
    'replace' => false, // 存在同名是否覆盖
    'hash' => true, // 是否生成hash编码
    'driver' => 'Local',
    'callback' => false
];
