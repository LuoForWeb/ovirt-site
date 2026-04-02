<?php

return [
    'resources' => [
        'AgentPool' => [
            'agent_pools' => [
                'get' => 'getAgentPool',  // 获取传输代理资源池列表 / 详情
                'post' => 'addAgentPool',  // 添加传输代理资源池
                'put' => 'editAgentPool',  // 修改传输代理资源池
                'delete' => 'deleteAgentPool',  // 删除传输代理资源池
            ],
            'agent_pools_batch' => [
                'delete' => 'batchDeleteAgentPool',  // 批量删除传输代理资源池
            ],
        ],
    ],
];
