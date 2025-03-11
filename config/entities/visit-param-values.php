<?php

return [
    'visit-param-values' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [
                'name' => 'num_value',
                'type' => 'double',
            ],
            [
                'name' => 'char_value',
                'type' => 'string',
            ],
            [
                'name' => 'date_value',
                'type' => 'integer',
            ],
            [
                'name' => 'dict_value',
                'type' => 'integer',
            ],
        ],
        'relations' => [
            [
                'link' => 'visits',
                'property' => 'id_visit',
                'propname' => 'visit',
                'required' => true
            ],
            [
                'link' => 'params',
                'property' => 'id_param',
                'propname' => 'param',
                'required' => true
            ],
        ],
    ],
];
