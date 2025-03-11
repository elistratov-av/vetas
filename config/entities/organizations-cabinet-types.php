<?php
return [
  'organizations-cabinet-types' => [
    'meta' => [
      'parent' => false,
    ],
    'attributes' => [],
    'relations' => [
      [
        'link' => 'cabinet-types',
        'property' => 'id_cabinet_type',
        'propname' => 'cabinet_type',
      ],
      [
        'link' => 'organization-cabinets',
        'property' => 'id_organization',
        'propname' => 'organization',
      ],
    ],
  ],
];