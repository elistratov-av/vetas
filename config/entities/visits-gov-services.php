<?php

return [
    'visits-gov-services' => [
        'meta' => ['parent' => false],
        'attributes' => [],
        'relations' => [
            ['link' => 'gov-services', 'property' => 'id_service'],
            ['link' => 'visits', 'property' => 'id_visit'],
            ['link' => 'pets', 'property' => 'id_pet', 'propname' => 'pet'],
        ],
        'plural_relations' => [
            [
                'link' => 'visit-service-param-values',
                'property' => 'id_visitservice',
            ],
            [
                'link' => 'files',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'visits_gov_service'
                ]
            ],
        ],
    ]
];
