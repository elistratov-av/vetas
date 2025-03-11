<?php

return [
    'gov-services-reports' => [
        'meta' => [
            'parent' => false
        ],
        'attributes' => [],
        'relations' => [
            [
                'link' => 'reports',
                'property' => 'id_report'
            ],
            [
                'link' => 'gov-services',
                'property' => 'id_service'
            ],
        ]
    ]
];
