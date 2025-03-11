<?php
return [
    'breeds-diseases' => [
        'meta' => [
            'parent' => false,
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
                'link' => 'breeds',
                'property' => 'id_breed',
                'propname' => 'breed',
                'composite_unique' => true,
            ],
        ],
    ],
];
