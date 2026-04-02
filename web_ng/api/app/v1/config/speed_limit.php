<?php

/**
 * 全局限速策略的一些配置
 */

return [
    'LIMIT_TYPE' => [ // 类型定义
        'BD_GLOBAL_SPEED_LIMIT_STRATEGY_TYPE_UNKNOWN'       => 0, // unlown
        'BD_GLOBAL_SPEED_LIMIT_STRATEGY_TYPE_EVERY_DAY'     => 1, // every day
        'BD_GLOBAL_SPEED_LIMIT_STRATEGY_TYPE_EVERY_WEEK'    => 2, // every week
        'BD_GLOBAL_SPEED_LIMIT_STRATEGY_TYPE_EVERY_MONTH'   => 3, // every month
        'BD_GLOBAL_SPEED_LIMIT_STRATEGY_TYPE_FOREVER'       => 4, // forever
        'BD_GLOBAL_SPEED_LIMIT_STRATEGY_TYPE_CUSTOM'        => 5, // custom
    ],
    'LIMIT_TYPE_LANG' => [ // 类型对应的语言包
        0   => 'WEB_PLATFORM_PUBLIC_UNKNOWN', // unlown
        1   => 'UI_STRATEGY_EVERY_DAY', // every day
        2   => 'UI_STRATEGY_EVERY_WEEK', // every week
        3   => 'UI_STRATEGY_EVERY_MONTH', // every month
        4   => 'UI_STRATEGY_FOREVER', // forever
        5   => 'UI_STRATEGY_CUSTOM', // custom
    ],
];
