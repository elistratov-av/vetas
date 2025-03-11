<?php
return [
    'specialists' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'f_fio',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 150],
                    \app\common\validators\FilterUcwordsValidator::class
                ]
            ],
            [
                'name' => 'i_fio',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 50],
                    \app\common\validators\FilterUcwordsValidator::class
                ]
            ],
            [
                'name' => 'o_fio',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 50],
                    \app\common\validators\FilterUcwordsValidator::class
                ]
            ],
            [
                'name' => 'reg_date',
                'type' => 'date',
                'required' => true,
            ],
            [
                'name' => 'birthday',
                'type' => 'date',
                'required' => true,
            ],
            [
                'name' => 'expel_date',
                'type' => 'date',
            ],
            [
                'name' => 'fullname',
                'type' => 'string',
            ],
            [
                'name' => 'id_user',
                'type' => 'integer',
            ],
            [
                'name' => 'sex',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 1],
                    'in' => ['range' => ['f', 'm'], 'message' => 'Неверный пол.']
                ]
            ],
        ],
        'relations' => [
            [
                'link' => 'organizations',
                'property' => 'id_organization',
                'propname' => 'organization',
                'required' => true,
            ],
            [
                'link' => 'files',
                'property' => 'photo',
                'propname' => 'file',
            ],

        ],
        'plural_relations' => [
            ['link' => 'timesheets', 'property' => 'id_specialist'],
            [
                'link' => 'files',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'specialist'
                ]
            ],
        ]
    ],
];
