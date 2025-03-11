<?php
return [
  'base' => [
    'meta' => [
      'virtual' => true,
      'parent' => false,
    ],
    'attributes' => [
      [
        'name' => 'name',
        'type' => 'string',
        'unique' => true,
        'required' => true,
        'max' => 50,
      ],
      [
        'name' => 'description',
        'type' => 'string',
        'max' => 255,
      ],
    ],
  ],
];