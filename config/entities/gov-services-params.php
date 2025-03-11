<?php

return [
    'gov-services-params' => [
        'meta' => [
            'parent' => false
        ],
        'attributes' => [
            [
                'name' => 'req_in',
                'type' => 'boolean',
            ],
            [
                'name' => 'req_out',
                'type' => 'boolean',
            ],
            [
                'name' => 'sort_by',
                'type' => 'integer',
            ],
        ],
        'relations' => [
            [
                'link' => 'params',
                'property' => 'id_param'
            ],
            [
                'link' => 'gov-services',
                'property' => 'id_service'
            ],
        ]
    ]
];
