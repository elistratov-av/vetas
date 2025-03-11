<?php
return [
    'pricelists' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'id_organization',
                'type' => 'integer',
                'required' => true,
                'rules' => [
                    'unique'
                ],
            ],
        ],
        'relations' => [
            [
                'link' => 'organizations',
                'property' => 'id_organization',
                'propname' => 'organization',
                'required' => true,
            ],
        ],
        'plural_relations' => [
            [
                'link' => 'gov-services',
                'property' => 'id_pricelist',
            ],
        ],
    ],
];
