<?php
return [
    'shifts' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
                'required' => true,
            ],
            [
                'name' => 'from_time',
                'type' => 'time',
            ],
            [
                'name' => 'idle',
                'type' => 'boolean',
            ],
            [
                'name' => 'duration',
                'type' => 'integer',
            ],
            [
                'name' => 'colour',
                'type' => 'string',
                'max' => 16
            ],
        ],
        'relations' => [
            [
                'link' => 'organizations',
                'property' => 'id_organization',
                'propname' => 'organization',
            ],
        ],
    ],
];
