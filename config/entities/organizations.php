<?php
return [
    'organizations' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.organizations.manage'],
                'W' => ['data.organizations.manage.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'unique' => true,
            ],
            [
                'name' => 'managing_organization_id',
                'type' => 'integer',
            ],
            [
                'name' => 'short_name',
                'type' => 'string',
                'required' => true,
                'unique' => true,
                'rules' => [
                    'string' => ['max' => 120],
                ]
            ],
            [
                'name' => 'inn',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 12],
                    \app\common\validators\InnValidator::class
                ]
            ],
            [
                'name' => 'kpp',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 9],
                    \app\common\validators\KppValidator::class
                ]
            ],
            [
                'name' => 'ogrn',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 13],
                    \app\common\validators\OgrnValidator::class
                ]
            ],
            [
                'name' => 'schedule',
                'type' => 'json',
            ],
            [
                'name' => 'mark_up_from_time',
                'type' => 'string',
                'rules' => [
                    \app\common\validators\PGTimeValidator::class
                ]
            ],
            [
                'name' => 'mark_up_to_time',
                'type' => 'string',
                'rules' => [
                    \app\common\validators\PGTimeValidator::class
                ]
            ],
            [
                'name' => 'mark_up_flag',
                'type' => 'boolean',
            ],
            [
                'name' => 'mark_up_ratio',
                'type' => 'double',
                'rules' => [
                    'double' => ['min' => 1, 'max' => 999999.99]
                ]
            ],
            [
                'name' => 'latitude',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 255],
                ]
            ],
            [
                'name' => 'longitude',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 255],
                ]
            ],
            [
                'name' => 'reseption_corpses',
                'type' => 'boolean',
            ],
            [
                'name' => 'free_vaccination',
                'type' => 'boolean',
            ],
            [
                'name' => 'pet_registration',
                'type' => 'boolean',
            ],
            [
                'name' => 'capital_structure',
                'type' => 'boolean',
            ],
            [
                'name' => 'public_services_available',
                'type' => 'boolean'
            ],
            [
                'name' => 'comment',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 3000]
                ]
            ],
            [
                'name' => 'clarification_schedule',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 3000]
                ]
            ],
            [
                'name' => 'chief_name',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 100]
                ]
            ],
            [
                'name' => 'chief_position',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 250]
                ]
            ],
            [
                'name' => 'bti_adm_area_code',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 255]
                ]
            ],
            [
                'name' => 'bti_adm_district_codes',
                'type' => 'array',
            ],
        ],
        'relations' => [
            [
                'link' => 'org-types',
                'property' => 'id_org_type',
                'propname' => 'orgType',
            ],
            [
                'link' => 'addresses',
                'property' => 'id_address',
                'propname' => 'address',
                'required' => true,
            ],
            [
                'link' => 'fias-addresses',
                'property' => 'id_fias_address'
            ],
            [
                'link' => 'areas',
                'property' => 'id_area',
                'propname' => 'area',
            ],
            [
                'link' => 'districts',
                'property' => 'id_district',
                'propname' => 'district',
            ],
            [
                'link' => 'organizations',
                'property' => 'parent_id',
                'propname' => 'parent',
                'rules' => [
                    \app\common\validators\OrganizationsLoopValidator::class
                ]
            ],
        ],
        'plural_relations' => [
            [
                'link' => 'organization-cabinets',
                'property' => 'id_organization',
            ],
            [
                'link' => 'pricelists',
                'property' => 'id_organization',
            ],
            [
                'link' => 'cabinet-types',
                'property' => 'id_organization',
            ],
            [
                'link' => 'contacts',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'organization',
                ],
            ],
            [
                'link' => 'specialists',
                'property' => 'id_organization',
            ],
            [
                'link' => 'shifts',
                'property' => 'id_organization',
            ],
        ],
    ],
];
