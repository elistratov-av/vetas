<?php
return [
  'drug-orgs' => [
    'meta' => [
      'parent' => false,
        'access' => [
            'R' => [
                'data.classificators.drugs',
                'data.classificators.vaccines',
            ],
            'W' => [
                'data.classificators.drugs.W',
                'data.classificators.vaccines.W',
            ],
        ],
    ],
    'attributes' => [
      [
        'name' => 'name',
        'type' => 'string',
        'required' => true,
        'unique' => true,
        'max' => 255,
      ],
    ],
  ],
];
