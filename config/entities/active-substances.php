<?php
return [
  'active-substances' => [
    'meta' => [
      'parent' => false,
        'access' => [
            'R' => ['data.classificators.active_substances'],
            'W' => ['data.classificators.active_substances.W'],
        ],
    ],
    'attributes' => [
      [
        'name' => 'name_en',
        'type' => 'string',
        'max' => 255,
          'rules' => [
              'unique' => ['on' => ['insert', 'update']],
              'string' => ['max' => 255]
          ]
      ],
      [
        'name' => 'name',
        'type' => 'string',
        'required' => true,
        'max' => 255,
          'rules' => [
              'unique' => ['on' => ['insert', 'update']],
              'string' => ['max' => 255]
          ]
      ],
      [
          'name' => 'description',
          'type' => 'string'
      ],
    ],
  ],
];
