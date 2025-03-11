<?php
return [
    'species-services' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [],
        'relations' => [
            [
                'link' => 'services',
                'property' => 'id_service',
                'propname' => 'services',
                'composite_unique' => true,
            ],
            [
                'link' => 'species',
                'property' => 'id_species',
                'propname' => 'species',
                'composite_unique' => true,
            ],
        ],
    ],
];
