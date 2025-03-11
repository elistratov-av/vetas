<?php
return [
    'species-diseases' => [
        'meta' => [
            'parent' => 'base',
        ],
        'attributes' => [],
        'relations' => [
            [
                'link' => 'diseases',
                'property' => 'id_disease',
                'propname' => 'disease',
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
