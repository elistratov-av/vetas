<?php
return [
    'gov-services' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'composite_unique' => true,
                'rules' => [
                    'string' => ['max' => 255]
                ]
            ],
            [
                'name' => 'price',
                'type' => 'double',
                'required' => true,
                'rules' => [
                    'double' => ['min' => 0]
                ]
            ],
            [
                'name' => 'sort_by',
                'type' => 'integer',
            ],
            [
                'name' => 'duration',
                'type' => 'integer',
                'required' => true,
                'rules' => [
                    'integer' => ['min' => 0]
                ]
            ],
            [
                'name' => 'cooldown',
                'type' => 'integer',
                'rules' => [
                    'integer' => ['min' => 0]
                ]
            ],
            [
                'name' => 'cod',
                'type' => 'string',
                'rules' => [
                    'match' => ['pattern' => '#^\d{4}$#', 'on' => ['insert', 'update']]
                ]
            ],
        ],
        'relations' => [
            [
                'link' => 'service-types',
                'property' => 'id_service_type',
                'propname' => 'service_type',
                'required' => true,
            ],
            [
                'link' => 'service-measures',
                'property' => 'id_service_measure',
                'propname' => 'measure',
            ],
            [
                'link' => 'pricelists',
                'property' => 'id_pricelist',
                'propname' => 'pricelist',
                'composite_unique' => true,
            ],
        ]
    ],
];
