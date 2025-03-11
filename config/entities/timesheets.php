<?php
return [
  'timesheets' => [
    'meta' => [
      'parent' => false,
    ],
    'attributes' => [
      [
        'name' => 'date',
        'type' => 'date',
        'required' => true,
      ],
    ],
    'relations' => [
      [
        'link' => 'specialists',
        'property' => 'id_specialist',
        'propname' => 'specialist',
      ],
      [
        'link' => 'shifts',
        'property' => 'id_shift',
        'propname' => 'shift',
      ],
    ],
  ],
];