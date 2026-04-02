<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

class AgentPool extends AuthBase
{
    /**
     * 获取传输代理资源池列表/获取传输代理资源池详情
     * @return void
     */
    public function getAgentPool()
    {
        if (isset($this->param['agent_pools_uuid'])) {
            $this->checkParams('get_agent_pool_detail');
            $return = $this->logic()->getAgentPoolDetail($this->param['agent_pools_uuid']);
        } else {
            $this->checkParams('get_agent_pool_list');
            $return = $this->logic()->getAgentPoolList($this->param);
        }
        $this->outputHandle($return);
    }

    /**
     * 添加传输代理资源池
     * @return void
     */
    public function addAgentPool()
    {
        $this->checkParams('add_agent_pool');
        $return = $this->logic()->addAgentPool($this->param);
        $this->outputHandle($return);
    }

    /**
     * 修改传输代理资源池
     * @return void
     */
    public function editAgentPool()
    {
        $this->checkParams('edit_agent_pool');
        $return = $this->logic()->editAgentPool($this->param);
        $this->outputHandle($return);
    }

    /**
     * 删除传输代理资源池
     * @return void
     */
    public function deleteAgentPool()
    {
        $this->checkParams('delete_agent_pool');
        $return = $this->logic()->deleteAgentPool($this->param['agent_pools_uuid']);
        $this->outputHandle($return);
    }

    /**
     * 批量删除传输代理资源池
     * @return void
     */
    public function batchDeleteAgentPool()
    {
        $this->checkParams('batch_delete_agent_pool');
        $return = $this->logic()->batchDeleteAgentPool($this->param['agent_pool_uuid_list']);
        $this->outputHandle($return);
    }
}
