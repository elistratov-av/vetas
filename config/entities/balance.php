<?php
return [
    'balance' => [
        'meta' => [
            'parent' => false,
            'virtual' => true,
        ],
        'attributes' => [
            [
                'name' => 'inventory_number',
                'type' => 'string',
                'required' => true,
                'composite_unique' => true,
                'rules' => [
                    'string' => ['max' => 150],
                ],
            ],
            [
                'name' => 'registration_date',
                'type' => 'date',
                'required' => true,
            ],
        ],
    ],
];
