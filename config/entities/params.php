<?php

return [
    'params' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'max' => 255,
            ],
            [
                'name' => 'tech_name',
                'type' => 'string',
                'required' => true,
                'max' => 100,
            ],
            [
                'name' => 'datatype',
                'type' => 'string',
                'required' => true,
                'max' => 255,
            ],
            [
                'name' => 'datatype_details',
                'type' => 'string',
                'max' => 255,
            ],
            [
                'name' => 'config',
                'type' => 'json',
            ],
            [
                'name' => 'visit_flag',
                'type' => 'boolean',
                'required' => true,
                'rules' => [
                    'default' => ['value' => false, 'on' => 'insert'],
                ],
            ],
        ]
    ],
];
