<?php

return [
    'visits' => [
        'meta' => ['parent' => false],
        'attributes' => [
            ['name' => 'status', 'type' => 'string', 'rules' => [
                'default' => ['value' => \app\common\models\VisitStatus::NEW, 'on' => 'insert'],
                'filter' => ['filter' => 'strtoupper'],
                \app\common\validators\VisitStatusValidator::class => ['on' => ['insert', 'update']],
                \app\common\validators\VisitDescriptionsValidator::class => ['on' => ['update']]
            ]],
            ['name' => 'is_paid', 'type' => 'boolean'],
            [   
                'name' => 'start_dttm', 
                'type' => 'integer',
                'rules' => [
                    \app\common\validators\VisitStartDttmValidator::class
                ]
            ],
            ['name' => 'fact_start_dttm', 'type' => 'integer'],
            ['name' => 'fact_end_dttm', 'type' => 'integer'],
            [
                'name' => 'cooldown', 
                'type' => 'integer', 
                'rules' => [
                    'integer' => ['min' => 0]
                ]
            ],
            ['name' => 'change_reason', 'type' => 'text'],
//            ['name' => 'channel', 'type' => 'integer', 'required' => true],
//            ['name' => 'number', 'type' => 'integer', 'required' => true],
        ],
        'relations' => [
            [   
                'link' => 'pet-owners', 
                'property' => 'id_owner', 
                'propname' => 'owner', 
                'required' => true,
            ],
            [   
                'link' => 'pets', 
                'property' => 'id_pet', 
                'propname' => 'pet', 
                'required' => true,
                'rules' => [
                    \app\common\validators\VisitPetValidator::class
                ]
            ],
            ['link' => 'organizations', 'property' => 'id_organization', 'propname' => 'organization', 'required' => true],
//            ['link' => 'specialists', 'property' => 'author', 'propname' => 'specialist'],
            ['link' => 'visits', 'property' => 'id_visit', 'propname' => 'source', 'required' => false],
        ],
        'plural_relations' => [
            ['link' => 'visit-service-tmcs', 'property' => 'id_visit'],
            ['link' => 'visit-descriptions', 'property' => 'id_visit'],
        ]
    ]
];
