<?php

/**
* 这是 全局观察者对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 只查看
    [
        'name' => 'global_read',
        'class' => 'icon-doc',
        'title' => 'UI_PLATFORM_GLOBAL_READ',
        'level' => 1,
    ],
    // 查看并操作
    [
        'name' => 'global_write',
        'class' => 'icon-magic-wand',
        'title' => 'UI_PLATFORM_GLOBAL_WRITE',
        'level' => 1,
    ],
];
