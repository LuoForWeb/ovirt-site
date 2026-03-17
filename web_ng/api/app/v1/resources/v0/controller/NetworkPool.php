<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

class NetworkPool extends AuthBase
{
    /**
     * 获取网络资源池列表/获取网络资源池详情
     * @return void
     */
    public function getNetworkPool()
    {
        if (isset($this->param['network_pools_uuid'])) {
            $this->checkParams('get_network_pool_detail');
            $result = $this->logic()->getNetworkPoolDetail($this->param['network_pools_uuid']);
        } else {
            $this->checkParams('get_network_pool_list');
            $result = $this->logic()->getNetworkPoolList($this->param);
        }
        $this->outputHandle($result);
    }

    /**
     * 添加网络资源池
     * @return void
     */
    public function addNetworkPool()
    {
        $this->checkParams('add_network_pool');
        $result = $this->logic()->addNetworkPool($this->param);
        $this->outputHandle($result);
    }

    /**
     * 修改网络资源池
     * @return void
     */
    public function editNetworkPool()
    {
        $this->checkParams('edit_network_pool');
        $result = $this->logic()->editNetworkPool($this->param);
        $this->outputHandle($result);
    }

    /**
     * 删除网络资源池
     * @return void
     */
    public function deleteNetworkPool()
    {
        $this->checkParams('delete_network_pool');
        $result = $this->logic()->deleteNetworkPool($this->param['network_pools_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 批量删除网络资源池
     * @return void
     */
    public function batchDeleteNetworkPool()
    {
        $this->checkParams('batch_delete_network_pool');
        $result = $this->logic()->batchDeleteNetworkPool($this->param['network_pool_uuid_list']);
        $this->outputHandle($result);
    }
}
