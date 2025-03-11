<?php
return [
    'document-types' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.R'],
                'W' => ['data.classificators.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
                'composite_unique' => true,
            ],
            [
                'name' => 'group',
                'type' => 'string',
                'required' => true,
                'composite_unique' => true,
            ],
            [
                'name' => 'type',
                'type' => 'string',
                'required' => true,
                'composite_unique' => true,
            ],
        ],
    ],
];