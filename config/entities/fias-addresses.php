<?php
return [
    'fias-addresses' => [
        'meta' => [
            'parent' => false,
        ],
        'attributes' => [
            [ 'name' => 'full_address', 'type' => 'string', ],
            [ 'name' => 'aoguid', 'type' => 'string', ],
            [ 'name' => 'region', 'type' => 'string', ],
            [ 'name' => 'city', 'type' => 'string', 'required' => true,],
            [ 'name' => 'street', 'type' => 'string',],
            [ 'name' => 'house', 'type' => 'string',],
            [ 'name' => 'room', 'type' => 'string', ],
            [ 'name' => 'regionguid', 'type' => 'integer', ],
            [ 'name' => 'cityguid', 'type' => 'string', ],
            [ 'name' => 'streetguid', 'type' => 'string', ],
            [ 'name' => 'houseguid', 'type' => 'string', ],
            [ 'name' => 'roomguid', 'type' => 'string', ],
            [ 'name' => 'oktmo', 'type' => 'string', ],
            [ 'name' => 'lat', 'type' => 'string', ],
            [ 'name' => 'lon', 'type' => 'string', ],
        ],
    ],
];
