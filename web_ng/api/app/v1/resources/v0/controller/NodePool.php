<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

class NodePool extends AuthBase
{
    /**
     * 获取计算资源池列表/获取计算资源池详情
     * @return void
     */
    public function getNodePool()
    {
        if (isset($this->param['node_pools_uuid'])) {
            $this->checkParams('get_node_pool_detail');
            $return = $this->logic()->getNodePoolDetail($this->param['node_pools_uuid']);
        } else {
            $this->checkParams('get_node_pool_list');
            $return = $this->logic()->getNodePoolList($this->param);
        }
        $this->outputHandle($return);
    }

    /**
     * 添加计算资源池
     * @return void
     */
    public function addNodePool()
    {
        $this->checkParams('add_node_pool');
        $return = $this->logic()->addNodePool($this->param);
        $this->outputHandle($return);
    }

    /**
     * 修改计算资源池
     * @return void
     */
    public function editNodePool()
    {
        $this->checkParams('edit_node_pool');
        $return = $this->logic()->editNodePool($this->param);
        $this->outputHandle($return);
    }

    /**
     * 删除计算资源池
     * @return void
     */
    public function deleteNodePool()
    {
        $this->checkParams('delete_node_pool');
        $return = $this->logic()->deleteNodePool($this->param['node_pools_uuid']);
        $this->outputHandle($return);
    }

    /**
     * 批量删除计算资源池
     * @return void
     */
    public function batchDeleteNodePool()
    {
        $this->checkParams('batch_delete_node_pool');
        $return = $this->logic()->batchDeleteNodePool($this->param['node_pool_uuid_list']);
        $this->outputHandle($return);
    }
}
