<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

class NetworkPool extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->rule = [
            'network_pools_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'network_uuid_list' => ['require', 'array'],
            'network_pool_uuid_list' => ['require', 'array'],
            'offset' => ['require', 'number', 'between' => '0,99999'],
            'limit' => ['require', 'number', 'between' => '1,500'],
            'order' => ['in:asc,desc,ASC,DESC'],
            'sort' => ['in:network_pool_name,update_time,creator,remark,node_info'],
            'node_uuid' => ['require'],
        ];
        $this->message = $this->make_message($this->rule);
        $this->scene = [
            'get_network_pool_detail' => ['network_pools_uuid', 'sort', 'order'],
            'get_network_pool_list' => ['offset', 'limit', 'sort', 'order'],
            'add_network_pool' => ['network_uuid_list', 'node_uuid'],
            'edit_network_pool' => ['network_pools_uuid', 'network_uuid_list', 'node_uuid'],
            'delete_network_pool' => ['network_pools_uuid'],
            'batch_delete_network_pool' => ['network_pool_uuid_list'],
        ];
    }
}
