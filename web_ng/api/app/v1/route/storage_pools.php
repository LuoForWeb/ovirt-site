<?php

return [
    'resources' => [
        'StoragePool' => [
            'storage_pools' => [
                'get' => 'getStoragePool',
                'post' => 'addStoragePool',
                'put' => 'editStoragePool',
                'delete' => 'deleteStoragePool',
            ],
            'storage_pools_batch' => [
                'delete' => 'batchDeleteStoragePool',
            ],
        ],
    ],
];
