<?php
return [
    'gov-services-species' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [],
        'relations' => [
            [
                'link' => 'species',
                'property' => 'id_species',
                'propname' => 'species',
                'composite_unique' => true,
            ],
            [
                'link' => 'services',
                'property' => 'id_service',
                'propname' => 'services',
                'composite_unique' => true,
            ],
        ],
    ],
];
