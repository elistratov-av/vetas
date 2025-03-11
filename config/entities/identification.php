<?php

return [
    'identification' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'identification_code',
                'type' => 'string',
                'unique' => true,
                'required' => true,
                'max' => 255,
            ],
        ]
    ],
];
