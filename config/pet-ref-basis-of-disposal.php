<?php
return [
    'pet-ref-basis-of-disposal' => [
        'meta' => [
            'parent' => false,
            'access' => [
                'R' => ['data.classificators.R'],
                'W' => ['data.classificators.W'],
            ],
        ],
        'attributes' => [
            [
                'name' => 'title',
                'type' => 'string',
                'required' => true,
                'composite_unique' => true,
            ],
        ],
    ],
];
