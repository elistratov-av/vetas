<?php
return [
    'contacts' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 255],
                    \app\common\validators\FilterOrganizationPhoneValidator::class => ['on' => ['insert', 'update']],
                ]
            ],
            [
                'name' => 'entity_type',
                'type' => 'string',
                'max' => 50,
                'required' => true,
                'rules' => [
                    'exist' => [
                        'targetClass' => \app\models\db\ContactTypes::class,
                        'targetAttribute' => [
                            'id_contact_type' => 'id',
                            'entity_type' => 'entity_type'
                        ]
                    ]
                ]
            ],
            [
                'name' => 'entity_id',
                'type' => 'integer',
                'required' => true,
            ],
        ],
        'relations' => [
            [
                'link' => 'contact-types',
                'property' => 'id_contact_type',
                'propname' => 'contactType',
                'required' => true,
            ],
        ],
    ],
];
