<?php
return [
    'org-types' => [
        'meta' => [
            'parent' => 'base',
            'access' => [
                'R' => ['data.classificators.org_types'],
                'W' => ['data.classificators.org_types.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'is_tech',
                'type' => 'boolean',
                'rules' => [
                    'default' => ['value' => false],
                ],
            ],
        ],
    ],
];
