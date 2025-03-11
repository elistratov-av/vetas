<?php

return [
    'reg-certificates' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            /*['name' => 'date', 'type' => 'date', 'required' => true,],*/
            /*['name' => 'number', 'type' => 'integer', 'required' => true,],*/

        ],
        'relations' => [
            [
                'link' => 'pets',
                'property' => 'id_pet',
                'required' => true,
                'rules' => ['unique' => ['on' => ['insert', 'update']]],
            ],
            [
                'link' => 'pet-owners',
                'property' => 'id_owner',
                'required' => true,
            ],
//            [
//                'link' => 'contacts',
//                'property' => 'phone',
//                'required' => true,
//            ],
//            [
//                'link' => 'contacts',
//                'property' => 'mail',
//                'required' => true,
//            ],
//            [
//                'link' => 'organizations',
//                'property' => 'id_organization',
//                'propname' => 'organization',
//                'required' => true,
//            ],
        ],
        'plural_relations' => [
            [
                'link' => 'files',
                'property' => 'entity_id',
                'additional_fields' => [
                    'entity_type' => 'reg_certificate',
                ],
            ],
        ],
    ],
];
