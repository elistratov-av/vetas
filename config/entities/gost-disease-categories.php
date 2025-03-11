<?php
return [
    'gost-disease-categories' => [
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
    ]
];
