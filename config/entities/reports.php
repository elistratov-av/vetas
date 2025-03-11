<?php

return [
    'reports' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'unique' => true,
                'required' => true,
                'max' => 255,
            ],
            [
                'name' => 'report_type',
                'type' => 'string',
                'required' => true,
                'max' => 1,
            ]
        ]
    ],
];
