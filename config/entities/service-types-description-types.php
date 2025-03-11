<?php

return [
    'service-types-description-types' => [
        'meta' => ['parent' => false],
        'attributes' => [],
        'relations' => [
            ['link' => 'description-types', 'property' => 'id_description_type'],
            ['link' => 'service-types', 'property' => 'id_service_type']
        ]
    ]
];
