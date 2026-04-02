<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

class StoragePool extends Base
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $allStoragePoolType = xphp_get_config('storage', 'STORAGE_POOL_TYPE');
        unset($allStoragePoolType['UNKNOWN']);
        $this->rule = [
            'storage_pools_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'storage_uuid_list' => ['require', 'array'],
            'storage_pool_uuid_list' => ['require', 'array'],
            'offset' => ['require', 'number', 'between' => '0,99999'],
            'limit' => ['require', 'number', 'between' => '1,500'],
            'storage_pool_type' => ['require', 'in:' . implode(',', $allStoragePoolType)],
            'order' => ['in:asc,desc,ASC,DESC'],
            'sort' => ['in:storage_pool_name,update_time,creator,remark,storage_pool_type'],
        ];
        $this->message = $this->make_message($this->rule);
        $this->scene = [
            'get_storage_pool_detail' => ['storage_pools_uuid'],
            'get_storage_pool_list' => ['offset', 'limit', 'sort', 'order'],
            'add_storage_pool' => ['storage_uuid_list', 'storage_pool_type'],
            'edit_storage_pool' => [
                'storage_pools_uuid', 'storage_pool_type', 'storage_uuid_list'
            ],
            'delete_storage_pool' => ['storage_pools_uuid'],
            'batch_delete_storage_pool' => ['storage_pool_uuid_list'],
        ];
    }
}
