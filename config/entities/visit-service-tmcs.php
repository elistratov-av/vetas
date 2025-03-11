<?php

return [
    'visit-service-tmcs' => [
        'meta' => ['parent' => false],
        'attributes' => [
            ['name' => 'count', 'type' => 'integer', 'required' => true],
            ['name' => 'id_tmc', 'type' => 'integer', 'required' => true, 'composite_unique' => true,],
        ],
        'relations' => [
            ['link' => 'gov-services', 'property' => 'id_service', 'propname' => 'service', 'composite_unique' => true,],
            ['link' => 'visits', 'property' => 'id_visit', 'propname' => 'visit'],
        ]
    ]
];
