<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          节点管理验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:24
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */

class Node extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $allNodeType = xphp_get_config('app', 'NODETYPE');
        unset($allModuleType['UNKNOWN']);
        unset($allNodeType['UNKNOWN']);
        $allProhibitTimeType = xphp_get_config('node', 'PROHIBIT_TIME_TYPE', 'resources');
        unset($allProhibitTimeType['UNKNOWN']);
        $allNodeFunction = xphp_get_config('node', 'NODE_FUNCTION', 'resources');
        unset($allNodeFunction['UNKNOWN']);
        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'name' => ['require', 'max' => 25, 'regex' => '/^[\w|\d]\w+/'],
            'id' => ['require', 'number', 'between' => '1,100'],
            'nodeuuid' => ['require'],
            'nodename' => ['require'],
            'nodes_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'offset' => ['require', 'number', 'between' => '0,99999'],
            'limit' => ['require', 'number', 'between' => '1,500'],
            'module_type' => ['require', 'number'],
            'time_point_module_type' => ['number', 'in:' . implode(',', $allModuleType)],
            'remote_ip' => ['validateIp'],
            'remote_port' => ['number'],
            'common_server_info' => ['checkCommonServerInfo'],
            'network_ip' => ['validateIp'],
            'operation_type' => ['require', 'number'],
            'node_config_flag' => ['require', 'boolean'],
            'max_task_running_num' => ['require', 'number'],
            'prohibit_time_type' => ['require', 'in:' . implode(',', $allProhibitTimeType)],
            'prohibit_time_info' => ['array', 'checkTaskProhibitTimeInfo'],
            'node_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'network_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'network_port' => ['require', 'number', 'between' => '0,65535'],
            'network_uuid_list' => ['require', 'array'],
            'platform_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'node_uuids' => ['require', 'array'],
            'node_type' => ['require', 'number',  'in:' . implode(',', $allNodeType)],
            'node_nickname' => ['require'],
            'node_uuid_list' => ['require', 'array'],
            // 节点缓存
            'cache_type' => ['require', 'number'],
            'switch_strategy' => ['require', 'number'],
            'node_function' => ['require', 'in:' . implode(',', $allNodeFunction)],
            // 资源池
            'pool_type' => ['require', 'in:node,storage,network,agent'],
            'pool_prefix_name' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取节点列表
            'getNodes' => ['offset', 'limit', 'time_point_module_type'],
            // 获取单个节点详情
            'getNode' => ['nodes_uuid'],
            // 获取节点的网路列表
            'getNodesNetworkCard' => ['nodes_uuid'],
            // 得到某个节点下的可用存储
            'storages_backup' => ['node_uuid'],
            // 得到恢复的时候所有有虚拟机|数据库备份数据的节点列表
            'nodes_get_timepoint' => ['module_type'],
            // 添加节点
            'add_node' => [
                'remote_ip', 'remote_port', 'common_server_info', 'network_ip', 'network_name', 'force_flag', 'node_function',
            ],
            // 修改节点
            'edit_node' => [
                'node_nickname', 'remote_ip', 'remote_port', 'node_type', 'common_server_info', 'nodes_uuid', 'node_function',
            ],
            // 删除节点
            'delete_node' => ['nodes_uuid'],
            'delete_node_list' => ['node_uuid_list'],
            // 节点缓存
            'get_node_cache' => ['nodes_uuid'],
            'set_node_cache' => [
                'nodes_uuid', 'cache_type', 'cache_dir_path', 'cache_storage_uuid',
                'switch_strategy', 'warning_flag', 'warning_value',
            ],

            // 资源限制
            // 设置节点的资源限制
            'set_node_resources_limit' => [
                'node_config_flag', 'max_task_running_num', 'node_uuid_list',
                'prohibit_time_type', 'prohibit_time_info',
            ],
            // 获取节点的资源限制
            'get_node_resources_limit' => ['node_uuid'],
            'get_node_resources_limit_batch' => ['node_uuid_list'],

            // 节点网络
            'get_node_network_detail' => ['nodes_uuid', 'network_uuid'],
            'get_node_network_list' => ['nodes_uuid', 'offset', 'limit'],
            'add_node_network' => ['nodes_uuid', 'network_ip', 'network_port', 'network_alias'],
            'edit_node_network' => ['nodes_uuid', 'network_uuid', 'network_ip', 'network_port', 'network_alias'],
            'delete_node_network' => ['nodes_uuid', 'network_uuid'],
            'batch_delete_node_network' => ['nodes_uuid', 'network_uuid_list'],
            'sort_node_network' => ['nodes_uuid', 'network_uuid_list'],
            // 获取节点分配列表
            'allocationList' => ['platform_uuid'],
            // 分配备份节点到虚拟化平台
            'allocation' => ['platform_uuid', 'node_uuids'],
            // 获取资源池名称
            'get_resource_pool_name' => ['pool_type', 'pool_prefix_name'],
        ];
    }

    /**
     * 验证配置节点资源限制的task_prohibit_time_info参数
     * @param ?array $taskProhibitTimeInfo 任务禁止运行时间段
     * @param string $rules                定义的验证规则的参数，当前规则没有参数。如in:1,2的参数是1,2
     * @param array  $data                 请求的全部数据
     * @param string $field                验证字段
     * @return bool|string
     */
    protected function checkTaskProhibitTimeInfo(
        ?array $taskProhibitTimeInfo,
        string $rules,
        array $data,
        string $field
    ) {
        if (!is_array($taskProhibitTimeInfo)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        foreach ($taskProhibitTimeInfo as $index => $timeItem) {
            if (!is_array($timeItem)) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field . "[$index]");
            }
            $checkResult = $this->checkProhibitTimeItem($timeItem);
            if (true !== $checkResult) {
                return sprintf($checkResult, $field . "[$index]");
            }
        }
        return true;
    }

    /**
     * 验证禁止运行时间段
     * @param array $timeItem 单个时间项
     * @return boolean|string
     */
    private function checkProhibitTimeItem(array $timeItem)
    {
        if (!isset($timeItem['days'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', '.days');
        }
        if (!is_array($timeItem['days'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), '%s.days');
        }
        if (!isset($timeItem['time_list'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), '%s', '.time_list');
        }
        if (!is_array($timeItem['time_list'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), '%s.time_list');
        }
        if (!$timeItem['time_list']) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), '%s.time_list');
        }
        foreach ($timeItem['time_list'] as $index => $time) {
            if (!isset($time['start_time'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), "%s.time_list[$index]", '.start_time');
            }
            if (!isset($time['end_time'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), "%s.time_list[$index]", '.end_time');
            }
        }
        return true;
    }

    /**
     * 验证添加客户端的common_server_info参数
     * @param ?array $commonServerInfo 本地节点配置
     * @param string $rules            定义的验证规则的参数，当前规则没有参数。如in:1,2的参数是1,2
     * @param array  $data             请求的全部数据
     * @param string $field            验证字段
     * @return bool|string
     */
    protected function checkCommonServerInfo(?array $commonServerInfo, string $rules, array $data, string $field)
    {
        if (isset($data['node_type'])) {  // 传了node_type
            if (xphp_get_config('app', 'NODETYPE')['MASTER'] == $data['node_type']) {
                return true;
            }
        }

        // 验证remote_ip
        if (!isset($data['remote_ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), 'remote_ip');
        }
        // 验证remote_port
        if (!isset($data['remote_port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_REQUIRE'), 'remote_port');
        }
        if (!is_array($commonServerInfo)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }
        if (!isset($commonServerInfo['server_ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'server_ip');
        }
        $ret = $this->validateIp($commonServerInfo['server_ip'], '', [], $field . '.server_ip');
        if (true !== $ret) {
            return $ret;
        }
        if (!isset($commonServerInfo['server_port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'server_port');
        }
        if (!isset($commonServerInfo['database_ip'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'database_ip');
        }
        if (!isset($commonServerInfo['database_name'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'database_name');
        }
        if (!isset($commonServerInfo['database_passwd'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'database_passwd');
        }
        if (!isset($commonServerInfo['database_port'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'database_port');
        }
        if (!isset($commonServerInfo['database_user'])) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field, 'database_user');
        }
        return true;
    }
}
