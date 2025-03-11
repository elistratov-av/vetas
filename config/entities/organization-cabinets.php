<?php
return [
    'organization-cabinets' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.organizations.manage'],
                'W' => ['data.organizations.manage.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'cabinet_count',
                'type' => 'integer',
                'required' => true,
            ],
        ],
        'relations' => [
            [
                'link' => 'organizations',
                'property' => 'id_organization',
                'propname' => 'organization',
                'composite_unique' => true,
            ],
            [
                'link' => 'cabinet-types',
                'property' => 'id_cabinet_type',
                'propname' => 'cabinet_type',
                'composite_unique' => true,
            ],
        ],
    ],
];
