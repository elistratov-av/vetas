<?php

return [
    'visit-descriptions' => [
        'meta' => ['parent' => false],
        'attributes' => [
            ['name' => 'description', 'type' => 'string', 'required' => true],
        ],
        'relations' => [
            [
                'link' => 'visits',
                'property' => 'id_visit',
                'propname' => 'visit',
                'composite_unique' => true,
            ],
            [
                'link' => 'description-types',
                'property' => 'id_description_type',
                'propname' => 'descType',
                'composite_unique' => true,
                'rules' => [
                    \app\common\validators\VisitDescriptionTypeValidator::class
                ]
            ],
        ]
    ]
];
