<?php

return [
    'reports-params' => [
        'meta' => [
            'parent' => false
        ],
        'attributes' => [],
        'relations' => [
            [
                'link' => 'params',
                'property' => 'id_param'
            ],
            [
                'link' => 'reports',
                'property' => 'id_report'
            ],
        ]
    ]
];
