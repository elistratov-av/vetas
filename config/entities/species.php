<?php
return [
    'species' => [
        'meta' => [
            'parent' => 'base',
            'access' => [
                'R' => ['data.classificators.species'],
                'W' => ['data.classificators.species.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'tech_name',
                'type' => 'string',
                'composite_unique' => true,
            ]
        ],
        'plural_relations' => [
            ['link' => 'breeds', 'property' => 'species_id']
        ]
    ],
];
