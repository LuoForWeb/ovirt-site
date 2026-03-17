<?php

return [
    'archive' => [
        'ArchiveBackUp' => [
            'archive_storages' => [
                'get' => 'getArchiveStorage',
            ],
            'archive_back' => [
                'post' => 'createArchiveBackJob',
            ],
            'archive' => [
                'post' => 'createArchiveJob',
                'put' => 'editArchiveJob',
            ],
        ]
    ]
];