<?php
return [
    'descriptions' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'entity_id',
                'type' => 'integer',
                'required' => true,
                'composite_unique' => true,
            ],
            [
                'name' => 'description',
                'type' => 'string',
                'required' => true,
            ],
        ],
//        'relations' => [
//            [
//                'link' => 'description-types',
//                'property' => 'id_description_type',
//                'propname' => 'descType',
//                'required' => true,
//                'composite_unique' => true,
//                'rules' => [\app\common\validators\DescriptionTypeValidator::class]
//            ],
//        ],
    ],
];
