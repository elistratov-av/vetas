<?php
return [
  'description-types' => [
    'meta' => [
      'parent' => false,
    ],
    'attributes' => [
      [
        'name' => 'name',
        'type' => 'string',
        'composite_unique' => true,
        'required' => true,
        'max' => 255,
      ],
        [
        'name' => 'entity_type',
        'type' => 'string',
        'composite_unique' => true,
        'required' => true,
        'max' => 255,
      ],
        [
            'name' => 'sort_by',
            'type' => 'integer',
        ],
    ],
  ],
];
