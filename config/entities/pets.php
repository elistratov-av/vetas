<?php
return [
    'pets' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'identification_code',
                'type' => 'string',
                'max' => 50,
            ],
            [
                'name' => 'reg_expire_date',
                'type' => 'date',
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'max' => 50,
            ],
            [
                'name' => 'sex',
                'type' => 'string',
                'rules' => [
                    'default' => ['value' => null],
                    'in' => ['range' => ['m', 'f']]
                ]
            ],
            [
                'name' => 'birthday',
                'type' => 'date',
            ],
            [
                'name' => 'reg_date',
                'type' => 'date',
            ],
        ],
        'relations' => [
            [
                'link' => 'species',
                'property' => 'id_species',
                'propname' => 'species',
                'required' => true
            ],
            [
                'link' => 'pet-ref-color',
                'property' => 'color_id',
                'propname' => 'color',
                'required' => true
            ],
            [
                'link' => 'pet-ref-skill',
                'property' => 'skill_id',
                'propname' => 'skill',
                'required' => true
            ],
            [
                'link' => 'pet-ref-size',
                'property' => 'size_id',
                'propname' => 'size',
                'required' => true
            ],
            [
                'link' => 'pet-ref-ear-type',
                'property' => 'ear_type_id',
                'propname' => 'ear_type',
                'required' => true
            ],
            [
                'link' => 'pet-ref-tail-type',
                'property' => 'tail_type_id',
                'propname' => 'tail_type',
                'required' => true
            ],
            [
                'link' => 'pet-ref-wool-type',
                'property' => 'tail_wool_id',
                'propname' => 'wool_type',
                'required' => true
            ],
            [
                'link' => 'breeds',
                'property' => 'id_breed',
                'propname' => 'breed',
            ],
            [
                'link' => 'organizations',
                'property' => 'id_reg_organization',
                'propname' => 'organization',
            ],
            [
                'link' => 'identification-types',
                'property' => 'id_ident_type',
                'propname' => 'identification-type',
            ],
            [
                'link' => 'reg-expire-reasons',
                'property' => 'id_reg_expire_reason',
                'propname' => 'reg-expire-reason',
            ],
            [
                'link' => 'pet-owners',
                'property' => 'id_owner',
                'propname' => 'owner',
                'required' => true
            ],
            [
                'link' => 'pet-rabies-vaccination',
                'property' => 'id_pet',
                'propname' => 'pet_rabies_vaccinations',
                'required' => true
            ],
        ],
        'plural_relations' => [
            [
                'link' => 'files',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'pet'
                ]
            ],
            [
                'link' => 'reg-certificates',
                'property' => 'id_pet',
            ],
            [
                'link' => 'pet-rabies-vaccination',
                'property' => 'id_pet',
                'required' => true
            ],
        ]
    ],
];
