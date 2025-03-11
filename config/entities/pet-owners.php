<?php

return [
    'pet-owners' => [
        'meta' => ['parent' => false],
        'attributes' => [
            ['name' => 'f_fio', 'type' => 'string', 'required' => true, 'rules' => [
                'string' => ['max' => 150], 
                \app\common\validators\FilterUcwordsValidator::class
            ]],
            ['name' => 'i_fio', 'type' => 'string', 'required' => true, 'rules' => [
                'string' => ['max' => 50],
                \app\common\validators\FilterUcwordsValidator::class
            ]],
            ['name' => 'o_fio', 'type' => 'string', 'rules' => [
                'string' => ['max' => 50],
                \app\common\validators\FilterUcwordsValidator::class
            ]],
            ['name' => 'jur_name', 'type' => 'string', 'rules' => [
                'string' => ['max' => 150]
            ]],
            ['name' => 'inn', 'type' => 'string', 'rules' => [
                \app\common\validators\InnValidator::class
            ]],
            ['name' => 'ogrn', 'type' => 'string', 'rules' => [
                \app\common\validators\OgrnValidator::class
            ]],
            ['name' => 'birthday', 'type' => 'date'],
            ['name' => 'snils', 'type' => 'string', 'rules' => [
                \app\common\validators\SnilsValidator::class
            ]],
            ['name' => 'fullname', 'type' => 'string', 'rules' => [
                'string' => ['max' => 255]
            ]],
            ['name' => 'email', 'type' => 'string', 'rules' => [
                'string' => ['max' => 255]
            ]],
            ['name' => 'phone', 'type' => 'string', 'rules' => [
                'string' => ['max' => 255]
            ]],
            ['name' => 'is_legal', 'type' => 'boolean', 'required' => true,],
        ],
        'relations' => [
            ['link' => 'addresses', 'property' => 'id_address', 'propname' => 'address'],
            ['link' => 'addresses', 'property' => 'id_fact_address', 'propname' => 'factAddress'],
            ['link' => 'areas', 'property' => 'id_area', 'propname' => 'area'],
            ['link' => 'districts', 'property' => 'id_district', 'propname' => 'district'],
            ['link' => 'fias-addresses','property' => 'id_fias_address']
        ],
        'plural_relations' => [
            [
                'link' => 'contacts',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'pet_owner',
                ],
            ],
            ['link' => 'pets', 'property' => 'id_owner'],
            [
                'link' => 'reg-certificates',
                'property' => 'id_owner',
            ],
        ]
    ]
];
