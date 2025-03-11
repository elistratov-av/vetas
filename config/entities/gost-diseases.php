<?php
return [
    'gost-diseases' => [
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
            ],
            [
                'name' => 'gost_code',
                'type' => 'string',
                'required' => true,
                'unique' => true,
                'rules' => [
                    'string' => ['max' => 20]
                ],
            ],
        ],
        'relations' => [
            [
                'link' => 'gost-disease-sub-categories',
                'property' => 'id_sub_category',
                'propname' => 'subCategory',
                'required' => true,
            ],
        ],
    ],
];
