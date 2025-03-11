<?php

return [
    'specialists-update-reasons' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'description',
                'type' => 'string',
                'required' => true,
            ],
        ],
        'relations' => [
            [
                'link' => 'specialists',
                'property' => 'id_specialist',
                'propname' => 'specialist',
                'required' => true
            ],
        ],
        'plural_relations' => [
            [
                'link' => 'files',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'specialists_update_reason'
                ]
            ],
        ]
    ]
];
