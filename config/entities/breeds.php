<?php
return [
  'breeds' => [
    'meta' => [
      'parent' => false,
        'access' => [
            'R' => ['data.classificators.breeds'],
            'W' => ['data.classificators.breeds.W'],
        ],
    ],
    'attributes' => [
        [
        'name' => 'name',
        'type' => 'string',
        'required' => true,
        'composite_unique' => true,
        ],
        [
        'name' => 'description',
        'type' => 'string',
        ],
    ],
    'relations' => [
      [
        'link' => 'species',
        'property' => 'species_id',
        'propname' => 'species',
        'composite_unique' => true,
        'required' => true,
      ],
    ],
  ],
];
