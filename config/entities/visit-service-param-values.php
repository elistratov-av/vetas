<?php

return [
    'visit-service-param-values' => [
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
            [
                'name' => 'complex_value',
                'type' => 'json',
            ],
        ],
        'relations' => [
            [
                'link' => 'visits-gov-services',
                'property' => 'id_visitservice',
                'propname' => 'visit-service',
                'required' => true
            ],
            [
                'link' => 'params',
                'property' => 'id_param',
                'propname' => 'param',
                'required' => true
            ],
        ],
        'plural_relations' => [
            [
                'link' => 'files',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'visit_service_param_value',
                ],
            ],
        ],
    ],
];
