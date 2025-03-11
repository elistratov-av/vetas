<?php
return [
    'aviary' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.R'],
                'W' => ['data.classificators.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'title',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 255],
                ]
            ],
            [
                'name' => 'description',
                'type' => 'string',
                'rules' => [
                    'string' => ['max' => 255],
                ]
            ],
            [
                'name' => 'organization_id',
                'type' => 'integer',
                'required' => true,
            ],
        ],
        'relations' => [
            [
                'link' => 'organizations',
                'property' => 'organization_id',
                'propname' => 'organization',
            ],
        ],
    ],
];
