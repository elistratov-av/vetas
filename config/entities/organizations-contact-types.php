<?php

return [
    'organizations-contact-types' => [
        'meta' => ['parent' => false],
        'attributes' => [],
        'relations' => [
            ['link' => 'contact-types', 'property' => 'id_contact_type'],
            ['link' => 'organizations', 'property' => 'entity_id']
        ]
    ]
];
