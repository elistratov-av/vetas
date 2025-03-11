<?php
return [
    'pet-rabies-vaccination' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'id',
                'type' => 'integer',
            ],
            [
                'name' => 'id_pet',
                'type' => 'integer',
            ],
            [
                'name' => 'id_vaccine',
                'type' => 'integer',
            ],
            [
                'name' => 'drug_name',
                'type' => 'string',
                'max' => 255,
            ],
            [
                'name' => 'producer_name',
                'type' => 'batch',
                'max' => 255,
            ],
            [
                'name' => 'drug_name',
                'type' => 'string',
                'max' => 255,
            ],
            [
                'name' => 'production_date',
                'type' => 'date',
            ],
            [
                'name' => 'expiry_date',
                'type' => 'date',
            ],
            [
                'name' => 'date',
                'type' => 'date',
            ],
            [
                'name' => 'valid_until',
                'type' => 'date',
            ],
        ],
        'relations' => [
        ],
        'plural_relations' => [
        ]
    ],
];
