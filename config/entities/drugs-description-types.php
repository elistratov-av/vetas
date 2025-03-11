<?php
return [
    'drugs-description-types' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.drugs'],
                'W' => ['data.classificators.drugs.W'],
            ],
        ],
        'attributes' => [],
        'relations' => [
            [
                'link' => 'descriptions',
                'property' => 'id_description_type',
                'propname' => 'descType',
            ],
            [
                'link' => 'drugs',
                'property' => 'entity_id',
                'propname' => 'drug',
            ],
        ],
    ],
];
