<?php
return [
  'addresses' => [
    'meta' => [
      'parent' => false,
    ],
    'attributes' => [
      [
        'name' => 'name',
        'type' => 'string',
      ],
      [
        'name' => 'latitude',
        'type' => 'string',
      ],
      [
        'name' => 'longitude',
        'type' => 'string',
      ],
    ],
    'relations' => [
      [
        'link' => 'areas',
        'property' => 'id_area',
        'propname' => 'area',
      ],
      [
        'link' => 'districts',
        'property' => 'id_district',
        'propname' => 'district',
      ],
    ],
  ],
];
