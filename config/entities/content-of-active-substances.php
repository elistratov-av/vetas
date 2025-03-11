<?php
return [
    'content-of-active-substances' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.active_substances'],
                'W' => ['data.classificators.active_substances.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'unit',
                'type' => 'double',
            ],
        ],
        'relations' => [
            [
                'link' => 'drugs',
                'property' => 'id_tmc',
                'propname' => 'tmc',
                'required' => true,
            ],
            [
                'link' => 'active-substances',
                'property' => 'id_active_substance',
                'propname' => 'activeSubstance',
                'required' => true,
            ],
            [
                'link' => 'measures',
                'property' => 'id_measure',
                'propname' => 'measure',
            ],
        ],
    ],
];
