<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

class AgentPool extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->rule = [
            'agent_pools_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'offset' => ['require', 'number', 'between' => '0,99999'],
            'limit' => ['require', 'number', 'between' => '1,500'],
            'agent_pool_name' => ['require'],
            'agent_uuid_list' => ['require', 'array'],
            'add_agent_uuid_list' => ['array'],
            'delete_agent_uuid_list' => ['array'],
            'agent_pool_uuid_list' => ['require', 'array'],
            'order' => ['in:asc,desc,ASC,DESC'],
            'sort' => ['in:agent_pool_name,update_time,creator,remark'],
        ];
        $this->message = $this->make_message($this->rule);
        $this->scene = [
            'get_agent_pool_list' => ['limit', 'offset', 'sort', 'order'],
            'get_agent_pool_detail' => ['agent_pools_uuid'],
            'add_agent_pool' => ['agent_pool_name', 'agent_uuid_list'],
            'edit_agent_pool' => [
                'agent_pools_uuid', 'agent_pool_name', 'add_agent_uuid_list', 'delete_agent_uuid_list'
            ],
            'delete_agent_pool' => ['agent_pools_uuid'],
            'batch_delete_agent_pool' => ['agent_pool_uuid_list'],
        ];
    }
}
