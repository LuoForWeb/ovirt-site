<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2026-03-16 16:01:29
 * @Description: 
 * @version: 1.0
 */

namespace app\v2\resources\v0\controller;

use app\v2\common\controller\AuthBase;

class StoragePool extends AuthBase
{
    /**
     * 获取存储资源池列表/详情
     * @return void
     */
    public function getStoragePool()
    {
        if (isset($this->param['storage_pools_uuid'])) {
            $this->checkParams('get_storage_pool_detail');
            $result = $this->logic()->getStoragePoolDetail($this->param['storage_pools_uuid']);
        } else {
            $this->checkParams('get_storage_pool_list');
            $result = $this->logic()->getStoragePoolList($this->param);
        }
        $this->outputHandle($result);
    }

    /**
     * 添加存储资源池
     * @return void
     */
    public function addStoragePool()
    {
        $this->checkParams('add_storage_pool');
        $result = $this->logic()->addStoragePool($this->param);
        $this->outputHandle($result);
    }

    /**
     * 修改存储资源池
     * @return void
     */
    public function editStoragePool()
    {
        $this->checkParams('edit_storage_pool');
        $result = $this->logic()->editStoragePool($this->param);
        $this->outputHandle($result);
    }

    /**
     * 删除存储资源池
     * @return void
     */
    public function deleteStoragePool()
    {
        $this->checkParams('delete_storage_pool');
        $result = $this->logic()->deleteStoragePool($this->param['storage_pools_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 批量删除存储资源池
     * @return void
     */
    public function batchDeleteStoragePool()
    {
        $this->checkParams('batch_delete_storage_pool');
        $result = $this->logic()->batchDeleteStoragePool($this->param['storage_pool_uuid_list']);
        $this->outputHandle($result);
    }
}
