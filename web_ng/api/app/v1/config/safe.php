<?php

/**
 * 安全策略的一些配置
 */

return [
    // 完整性校验
    'INTEGRITY_CHECK_STRATEGY' => [  // 校验周期
        'DAY' => 1,//每天一次
		'WEEK' => 0, //每周一次
		'EVERY' => 2,//每次备份
    ],
    'INTEGRITY_FULL_ERROR_POLICY' => [  // 完备点异常
        'REFULL' => 1,//将整链标记为损坏，并重做完备
		'STOPBACKUP' => 0,//中止备份
    ],
    'INTEGRITY_INC_ERROR_POLICY' => [  // 增量点点异常
        'REFULL' => 1,//重做完备（将对应差异点/增量点标记为损坏）
		'REINC' => 2,//重做增量（删除无效差异点/增量点，并基于最新有效点做目标端增量）
		'STOPBACKUP' => 0,//中止备份
    ],
    'INTEGRITY_RECOVERY_ERROR_POLICY' => [  // 完整性恢复策略
        'TERMINAL_RECOVERY' => 0,  // 中断恢复
        'CONTINUE_RECOVERY' => 1,  // 继续恢复
        'RECOVERY_TO_NO_NETWORK' => 2,  // 恢复到无网络环境
    ]
];
