<?php

return [
  'visits-specialists' => [
      'meta' => ['parent' => false],
      'attributes' => [],
      'relations' => [
          ['link' => 'specialists', 'property' => 'id_specialist', 'composite_unique' => true,],
          ['link' => 'visits', 'property' => 'id_visit', 'composite_unique' => true,]
      ]
  ]
];
