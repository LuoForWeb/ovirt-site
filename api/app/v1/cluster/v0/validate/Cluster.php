<?php

namespace app\v1\cluster\v0\validate;

use app\v1\common\validate\Base;

class Cluster extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'cluster_name' => ['require'],
            'config_service_ip' => ['require', 'validateIp'],
            'priority_flag' => ['boolean'],
            'node_config' => ['require', 'checkNodeConfig'],
            'source_node_uuid'   => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'target_node_uuid'   => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'config_mode' => ['require', 'number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'set_cluster_config' => ['config_service_ip', 'priority_flag', 'node_config', 'cluster_name', 'config_mode'],
            'set_cluster_master_node' => ['source_node_uuid', 'target_node_uuid'],
        ];
    }

    /**
     * @param ?array $nodeConfig 节点配置
     * @param string $rules      定义的验证规则的参数，当前规则没有参数。如in:1,2的参数是1,2
     * @param array  $data       请求的全部数据
     * @param string $field      验证字段
     * @return boolean|string
     */
    protected function checkNodeConfig(?array $nodeConfig, string $rules, array $data, string $field)
    {
        if (!is_array($nodeConfig)) {
            return sprintf(xphp_get_lang('WEB_VALIDATE_ARRAY'), $field);
        }

        $setFlag = array_values(xphp_get_config('app', 'FLAG'));
        foreach ($nodeConfig as $index => $item) {
            if (!isset($item['node_uuid'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . "[$index]", 'node_uuid');
            }
            if (!$item['node_uuid']) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field . "[$index].node_uuid");
            }
            if (!isset($item['node_role'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . "[$index]", 'node_role');
            }
            if (!is_numeric($item['node_role'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_NUMBER'), $field . "[$index].node_role");
            }
            if (!in_array($item['node_role'], $setFlag)) {
                return sprintf(
                    xphp_get_lang('WEB_VALIDATE_IN'),
                    $field . "[$index].node_role",
                    implode(', ', $setFlag)
                );
            }
            if (!isset($item['priority'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . "[$index]", 'priority');
            }
            if (!is_numeric($item['priority'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_NUMBER'), $field . "[$index].priority");
            }
            if (!isset($item['network_name'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . "[$index]", 'network_name');
            }
            if (!$item['network_name']) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_EMPTY'), $field . "[$index].network_name");
            }
            if (!isset($item['network_ip'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_OBJECT_NEED_ATTR'), $field . "[$index]", 'network_ip');
            }
            if (!$this->ip($item['network_ip'])) {
                return sprintf(xphp_get_lang('WEB_VALIDATE_IP'), $field . "[$index].network_ip");
            }
        }
        return true;
    }
}
