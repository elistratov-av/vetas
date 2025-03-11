<?php
return [
  'districts' => [
    'meta' => [
      'parent' => false,
    ],
    'attributes' => [
      [
        'name' => 'name',
        'type' => 'string',
          'max' => 255
      ],
      [
        'name' => 'bti_code',
        'type' => 'integer',
          'max' => 10
      ],
    ],
    'relations' => [
      [
        'link' => 'areas',
        'property' => 'id_area',
        'propname' => 'area',
      ],
    ],
  ],
];
