<?php

namespace app\v1\cloud\v0\validate;

use app\v1\common\validate\Base;

/**
 * Class CloudBackup
 * @package app\v1\cloud\v0\validate
 */
class CloudBackup extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $timeReg = '/^((20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/'; //验证时间格式

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'job_uuid' => ['require'], //修改任务使用
            'job_name' => ['require'],

            //备份源
            'src_info' => ['require'],
            'src_info.instance_info' => ['require', 'array'],
            'src_info.instance_info.*.instance_uuid' => ['require'],
            'src_info.instance_info.*.platform_uuid' => ['require'],
            'src_info.instance_info.*.path' => ['require'],
            'src_info.instance_info.*.config' => ['require'],
            'src_info.instance_info.*.config.disk_list' => ['array'],
            'src_info.instance_info.*.config.disk_list.*.disk_name' => ['require'],
            'src_info.instance_info.*.config.disk_list.*.disk_uuid' => ['require'],
            'src_info.exclude_disk_list' => ['require', 'array'],
            'src_info.exclude_disk_list.*.disk_name' => ['require'],
            'src_info.exclude_disk_list.*.disk_uuid' => ['require'],
            'src_info.exclude_disk_des' => ['require', 'array'],
            'src_info.hypervisor_type' => ['require', 'integer', 'min' => 0],

            //时间策略
            'time_strategy' => ['require'],
            'time_strategy.backup_type' => ['require', 'in' => [1, 2]],
            'time_strategy.once_start_time' => ['date'],

            'time_strategy.full_backup.strategy_type' =>
                ['require', 'integer', 'in' => [1, 2, 3, 4]], //备份时间方式：1每天，2每周，3每月，4一次
            'time_strategy.full_backup.start_time' => ['require', 'dateFormat' => $timeReg],
            'time_strategy.full_backup.roll_flag' => ['require', 'boolean'],
            'time_strategy.full_backup.roll_interval' => ['dateFormat' => $timeReg],
            'time_strategy.full_backup.roll_end_time' => ['dateFormat' => $timeReg],
            'time_strategy.full_backup.days' => ['require', 'array'], //执行备份的日期
            'time_strategy.full_backup.days.*' => ['integer', 'in' => [0, 1]], //是否执行标记
            'time_strategy.full_backup.frequency' => ['string'], //按周备份的备份间隔周数，s1-s20
            'time_strategy.full_backup.des' => ['require'],

            'time_strategy.incremental_backup.strategy_type' =>
                ['require', 'integer', 'in' => [1, 2, 3, 4]], //备份时间方式：1每天，2每周，3每月，4一次
            'time_strategy.incremental_backup.start_time' => ['require', 'dateFormat' => $timeReg],
            'time_strategy.incremental_backup.roll_flag' => ['require', 'boolean'],
            'time_strategy.incremental_backup.roll_interval' => ['dateFormat' => $timeReg],
            'time_strategy.incremental_backup.roll_end_time' => ['dateFormat' => $timeReg],
            'time_strategy.incremental_backup.days' => ['require', 'array'], //执行备份的日期
            'time_strategy.incremental_backup.days.*' => ['integer', 'in' => [0, 1]], //是否执行标记
            'time_strategy.incremental_backup.frequency' => ['string'], //按周备份的备份间隔周数，s1-s20
            'time_strategy.incremental_backup.des' => ['require'],

            'time_strategy.differential_backup.strategy_type' =>
                ['require', 'integer', 'in' => [1, 2, 3, 4]], //备份时间方式：1每天，2每周，3每月，4一次
            'time_strategy.differential_backup.start_time' => ['require', 'dateFormat' => $timeReg],
            'time_strategy.differential_backup.roll_flag' => ['require', 'boolean'],
            'time_strategy.differential_backup.roll_interval' => ['dateFormat' => $timeReg],
            'time_strategy.differential_backup.roll_end_time' => ['dateFormat' => $timeReg],
            'time_strategy.differential_backup.days' => ['require', 'array'], //执行备份的日期
            'time_strategy.differential_backup.days.*' => ['integer', 'in' => [0, 1]], //是否执行标记
            'time_strategy.differential_backup.frequency' => ['string'], //按周备份的备份间隔周数，s1-s20
            'time_strategy.differential_backup.des' => ['require'],

            'time_strategy.forever_incremental.strategy_type' =>
                ['require', 'integer', 'in' => [1, 2, 3, 4]], //备份时间方式：1每天，2每周，3每月，4一次
            'time_strategy.forever_incremental.start_time' => ['require', 'dateFormat' => $timeReg],
            'time_strategy.forever_incremental.roll_flag' => ['require', 'boolean'],
            'time_strategy.forever_incremental.roll_interval' => ['dateFormat' => $timeReg],
            'time_strategy.forever_incremental.roll_end_time' => ['dateFormat' => $timeReg],
            'time_strategy.forever_incremental.days' => ['require', 'array'], //执行备份的日期
            'time_strategy.forever_incremental.days.*' => ['integer', 'in' => [0, 1]], //是否执行标记
            'time_strategy.forever_incremental.frequency' => ['string'], //按周备份的备份间隔周数，s1-s20
            'time_strategy.forever_incremental.des' => ['require'],

            //存储策略
            'storage_strategy' => ['require'],
            'storage_strategy.deduplication_flag' => ['boolean'],
            'storage_strategy.compress_flag' => ['boolean'],
            'storage_strategy.encrypt_flag' => ['boolean'],
            'storage_strategy.block_size' =>
                ['integer', 'in' => [64, 128, 256, 512, 1024, 2048]], //数据块大小，枚举值：64,128,256,512,1024,2048；单位KB

            //保留策略
            'reserved_strategy.reserved_type' => ['require', 'integer', 'in' => [1, 2]], //保留方式：1个数，2天数
            'reserved_strategy.value' => ['require', 'integer'],
            'reserved_strategy.gfs_reserved_flag' => ['boolean'],
            'reserved_strategy.gfs_reserved_strategy' => ['array'],
            'reserved_strategy.gfs_reserved_strategy.*.gfs_reserved_type' =>
                ['require', 'integer', 'in' => [1, 2, 3]], //GFS保留策略类型：1周，2月，3年
            'reserved_strategy.gfs_reserved_strategy.*.gfs_reserved_start' =>
                ['integer', 'min' => 1, 'max' => 12], //每周从星期几开始 取值:1-7;每月从哪周开始 1:第一周 2:最后一周;每年从第几月开始 取值:1-12
            'reserved_strategy.gfs_reserved_strategy.*.gfs_reserved_value' => ['require', 'integer'], //GFS保留个数
            'reserved_strategy.gfs_modify_flag' => ['require', 'boolean'], //GFS保留策略是否修改，修改任务使用

            //高级策略
            'advanced_strategy' => ['require'],
            'advanced_strategy.node' => ['required'],
            'advanced_strategy.node.node_uuid' => ['required'],
            'advanced_strategy.node.storage_uuid' => ['required'],
            'advanced_strategy.mode' => ['required'],
            'advanced_strategy.mode.snapshot_reserved_num' => ['required', 'integer', 'min' => 0],
            'advanced_strategy.mode.cbt_flag' => ['required', 'boolean'],
            'advanced_strategy.mode.parse_fs_flag' => ['required', 'boolean'],
            'advanced_strategy.mode.swap_flag' => ['boolean'],
            'advanced_strategy.mode.gap_flag' => ['boolean'],
            'advanced_strategy.mode.snapshot_type' => ['require', 'integer', 'in' => [1, 2]], //快照模式：1串行，2并行
            'advanced_strategy.mode.thread_num' => ['require', 'integer', 'min' => 1, 'max' => 8], //传输线程数量，1-8
            'advanced_strategy.mode.pre_snapshot_flag' => ['require', 'boolean'],

            //传输策略
            'transport_strategy' => ['require'],
            'transport_strategy.mode' => ['require', 'in' => ['nbd', 'nbdssl']], //传输模式：nbd网络传输，nbdssl网络加密传输
            'transport_strategy.appliance_uuid' => ['require'], //传输代理实例类型UUID

            //限速策略
            'speed_strategy' => ['array'],
            'speed_strategy.uuid' => ['require'],
            'speed_strategy.mode' => ['require', 'integer', 'in' => [1, 2]], //限速方式：1按策略，2永久
            'speed_strategy.speed_type' => ['require', 'integer', 'in' => [1, 2, 3]], //时间策略：1每天，2每周，3每月
            'speed_strategy.value' => ['require', 'integer'],
            'speed_strategy.start_time' => ['dateFormat' => $timeReg],
            'speed_strategy.end_time' => ['dateFormat' => $timeReg],
            'speed_strategy.days' => ['array'],
            'speed_strategy.days.*' => ['integer', 'in' => [0, 1]], //是否执行标记
            'speed_strategy.des' => ['require'],

            'emptyCachePath' => ['require', 'boolean']
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'createJob' => array_diff(array_keys($this->rule), ['job_uuid', 'reserved_strategy.gfs_modify_flag', 'emptyCachePath']),
            'editJob' => array_keys($this->rule),
        ];
    }
}
