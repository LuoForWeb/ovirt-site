<?php

/**
* 这是行业合规的一些配置信息
 */

return [
    // 报告状态标识
    // 状态，默认0待审批，1已归档，2审批中，3已驳回，4已撤回，9待生成，此时是创建任务的时候配置的
    'REPORT_STATUS' => [
        'PENDING' => 0,
        'ARCHIVED' => 1,
        'APPROVALING' => 2,
        'REJECTED' => 3,
        'REVOKE' => 4,
        'GENERATED' => 9
    ],
    // 方案状态，默认0待审批，1已审批，2审批中，3已驳回，4已撤回
    'PLAN_STATUS' => [
        'PENDING' => 0,
        'ARCHIVED' => 1,
        'APPROVALING' => 2,
        'REJECTED' => 3,
        'REVOKE' => 4,
    ],
    // 方案类型
    'PLAN_TYPE' => [
        'PENDING' => 0,
        'DATA_VALIDATE' => 1,
        'APPROVALING' => 2,
    ],
    // 验证的状态枚举
    'VERIFY_STATUS' => [
        'UNKNOW' => 0,
        'WAITING' => 1,
        'DOING' => 2,
        'SKIP' => 3,
        'ERROR' => 4,
        'SUCCESS' => 5,
        'DONE' => 6,
        'WARNING' => 7,
    ],
    // tcpdf的一些配置信息
    'tcpdf' => [
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'default_font_size' => 10,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_header' => 5,
        'margin_footer' => 5,
        'default_font' => 'cid0cs',
        'rgba_line' => [
            'r' => '210',
            'g' => '212',
            'b' => '222',
        ],
        'rgba_text' => [
            'r' => '140',
            'g' => '135',
            'b' => '135',
        ],
        'diy_font' => DATA_PATH . 'industry/fonts/msyh.ttf',
    ]
];
