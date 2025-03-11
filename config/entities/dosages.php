<?php
return [
    'dosages' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.dosages.manage'],
                'W' => ['data.dosages.manage.W'],
            ],
        ],
        'attributes' => [
            ['name' => 'dosage', 'type' => 'number',],
        ],
        'relations' => [
            [
                'link' => 'drugs',
                'property' => 'id_drug',
                'propname' => 'drug',
            ],
        ],
    ],
];
