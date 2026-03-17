<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          虚拟机管理 验证器
 * @author       wanggongxi@vinchin.com
 * @date         2024/3/5 18:25
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Machine extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset'                    => ['number', 'between' => '0,9999999'],
            'limit'                     => ['require', 'number', 'between' => '1,500'],
            'temp_agent'                => ['require', 'array'],
            'temp_agent.uuid'           => ['require'],
            'temp_agent.node_uuid'      => ['require'],
            'id_list'                   => ['require', 'array'],
            'operate_uuid'              => ['require'],
            'type'                      => ['require'],
            'node_uuid'                 => ['require'],
            'cpus'                      => ['require'],
            'mems'                      => ['require'],
            'name'                      => ['require'],
            'forword_mode'              => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'lists' => ['offset', 'limit'], // 列表
            'editMachine' => ['temp_agent', 'temp_agent.uuid', 'temp_agent.node_uuid'], // 修改主机
            'delMachine' => ['id_list'], // 删除主机
            'refreshMachines' => ['offset'], // 刷新主机
            'operateMachines' => ['operate_uuid', 'type'], // 操作主机
            'getResources' => ['node_uuid'], // 获取资源隔离
            'editResources' => ['node_uuid', 'cpus', 'mems'], // 保存资源隔离
            'editNetworks' => ['name', 'forword_mode', 'node_uuid'], // 添加、修改网络
            'delNetworks' => ['id_list'], // 删除网络
            'delLogs' => ['id_list'], // 删除日志
            'nodeVt' => ['node_uuid', 'type'], // 更改资源列表的是否使用VT
        ];
    }
}
