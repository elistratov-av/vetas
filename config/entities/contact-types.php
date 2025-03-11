<?php

return [
    'contact-types' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'unique',
                    'string' => ['max' => 255],
                ]
            ],
            [
                'name' => 'type',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 50],
                    'in' => ['range' => [
                        \app\models\db\ContactTypes::TYPE_PHONE,
                        \app\models\db\ContactTypes::TYPE_EMAIL
                    ]]
                ]
            ],
            [
                'name' => 'entity_type',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 50],
                    'in' => ['range' => ['pet_owner', 'organization']]
                ]
            ],
        ],
    ],
];
