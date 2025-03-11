<?php

return [
    // ~dev
    // 'baseUrl' => 'http://efp6.sm-soft.ru:8280/address/1.0',
    // 'tokenUrl' => 'http://efp6.sm-soft.ru:8280/token',
    // 'auth' => [
    //     'clientId' => 'abVe_zoGIiLfwMItcI044ZqqsPUa',
    //     'clientSecret' => 'QiZut1fQFC7J2iDojf4OabGaN2Qa',
    // ],
    // prod
    'baseUrl' => 'https://api.mos.ru/api/fias/13.5',
    'tokenUrl' => 'https://api.mos.ru/token',
    'auth' => [
        'clientId' => 'CsSRqAomVoZz2fScm7j7rWA02cEa',
        'clientSecret' => 'oGKOfMddtxvqU64kTf06rQncGIga',
    ],
    /** TODO:efsp-apis Отдельная настройка API */
    'apis' => [
        'address' => [
            /** @link https://efp6.sm-soft.ru:9443/store/apis/info?name=AddressApi&version=1.0&provider=admin */
            // ~dev
            'path' => '/address/1.0',
        ],
    ],
];
