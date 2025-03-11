<?php

return [
    'pet-owners-contact-types' => [
        'meta' => ['parent' => false],
        'attributes' => [],
        'relations' => [
            ['link' => 'contact-types', 'property' => 'id_contact_type'],
            ['link' => 'pet-owners', 'property' => 'entity_id']
        ]
    ]
];
