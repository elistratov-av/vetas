<?php
return [
    'organization-type' => [
        'meta' => [
            'parent' => 'base',
            'access' => [
                'R' => ['data.classificators.organization_type'],
                'W' => ['data.classificators.organization_type.W'],
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
