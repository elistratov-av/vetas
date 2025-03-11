<?php
return [
    'gost-disease-sub-categories' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.gost-diseases'],
                'W' => ['data.classificators.gost-diseases.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'rules' => [
                    'string' => ['max' => 250]
                ],
            ]
        ],
        'relations' => [
            [
                'link' => 'gost-disease-categories',
                'property' => 'id_category',
                'propname' => 'category',
                'required' => true,
            ],
        ],
    ]
];
