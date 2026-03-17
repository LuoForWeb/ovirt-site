<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

class NodePool extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->rule = [
            'node_pools_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'offset' => ['require', 'number', 'between' => '0,99999'],
            'limit' => ['require', 'number', 'between' => '1,500'],
            'node_pool_name' => ['require'],
            'node_uuid_list' => ['require', 'array'],
            'node_pool_uuid_list' => ['require', 'array'],
            'order' => ['in:asc,desc,ASC,DESC'],
            'sort' => ['in:node_pool_name,update_time,creator,remark'],
        ];
        $this->message = $this->make_message($this->rule);
        $this->scene = [
            'get_node_pool_list' => ['limit', 'offset', 'sort', 'order'],
            'get_node_pool_detail' => ['node_pools_uuid'],
            'add_node_pool' => ['node_pool_name', 'node_uuid_list'],
            'edit_node_pool' => [
                'node_pools_uuid', 'node_pool_name', 'node_uuid_list'
            ],
            'delete_node_pool' => ['node_pools_uuid'],
            'batch_delete_node_pool' => ['node_pool_uuid_list'],
        ];
    }
}
