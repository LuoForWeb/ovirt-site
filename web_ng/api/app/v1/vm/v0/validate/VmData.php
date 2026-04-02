<?php

namespace app\v1\vm\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          虚拟机管理 -- 之数据管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmData extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $timeReg = '/^ ((20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/'; //验证时间格式

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'show_type' => ['require', 'integer', 'in' => [1, 2]],
            'instant_flag' => ['boolean'],
            'manage_flag' => ['require', 'boolean'],
            'export_flag' => ['boolean'],
            'data_flag' => ['require', 'boolean'],
            'aws_flag' => ['boolean'],

            'offset' => ['integer', 'min' => 0],
            'limit' => ['integer', 'min' => 1],
            'order' => ['in' => ['asc', 'desc']],
            'copy_flag' => ['boolean'],
            'archive_flag' => ['boolean'],
            'backup_mode' => ['integer', 'in' => [0, 1, 2, 3, 9]],
            'start_time' => ['date'],
            'end_time' => ['date'],
            'week_flag' => ['integer', 'in' => [1, 2]], //1是2否
            'month_flag' => ['integer', 'in' => [1, 2]],
            'year_flag' => ['integer', 'in' => [1, 2]],
            'forever_flag' => ['integer', 'in' => [1, 2]],

            'timepoint_list' => ['require', 'array'],
            'timepoint_list.*.timepoint_uuid' => ['require'],
            'timepoint_list.*.node_uuid' => ['require'],
            'timepoint_list.*.hypervisor_type' => ['require', 'integer',
                'in' => xphp_get_config('vm', 'VMHYPERVISORTYPE')],

            'timepoint_uuid' => ['require'],

            'item_list' => ['array'],
            'item_list.*.level1_type' => ['require', 'integer', 'in' => [1, 2, 3, 4]], //保留策略：1周，2月，3年，4永久
            'item_list.*.flag' => ['require', 'integer', 'in' => [1, 2]], //是否设置：1是2否
            'node_uuid' => ['require'],
            'hypervisor_type' => ['require', 'integer', 'in' => xphp_get_config('vm', 'VMHYPERVISORTYPE')],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'getRestoreData' => ['show_type', 'instant_flag', 'manage_flag', 'export_flag', 'data_flag'],
            'getRestorePoints' => ['offset', 'limit', 'order', 'copy_flag', 'archive_flag',
                'backup_mode', 'start_time', 'end_time', 'week_flag', 'month_flag', 'year_flag', 'forever_flag'],
            'deleteRestoreData' => ['timepoint_list'],
            'deleteRestoreDataOne' => ['timepoint_uuid'],
            'addRemark' => ['timepoint_uuid'],
            'setGfsMark' => ['timepoint_uuid', 'item_list', 'node_uuid', 'hypervisor_type'],
        ];
    }
}
