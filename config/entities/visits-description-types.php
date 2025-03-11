<?php

return [
    'visits-description-types' => [
        'meta' => ['parent' => false],
        'attributes' => [],
        'relations' => [
            ['link' => 'description-types', 'property' => 'id_description_type'],
            ['link' => 'visits', 'property' => 'id_visit']
        ]
    ]
];
