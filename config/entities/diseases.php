<?php
return [
    'diseases' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.diseases'],
                'W' => ['data.classificators.diseases.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'unique' => true,
                'max' => 200,
            ],
            [
                'name' => 'code',
                'type' => 'string',
            ],
            [
                'name' => 'flag_danger',
                'type' => 'boolean',
                'rules' => [
                    'default' => ['value' => false],
                ],
            ],
        ],
        'relations' => [
            [
                'link' => 'gost-diseases',
                'property' => 'id_gost_disease',
                'propname' => 'gostDisease',
                'required' => false,
            ],
        ],
        'plural_relations' => [
            [
                'link' => 'descriptions',
                'property' => 'entity_id',
            ],
        ],
    ],
];
