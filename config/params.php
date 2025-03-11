<?php

use \yii\helpers\ArrayHelper;


$params = [
    'adminEmail' => 'admin@vetas.mos.ru',
    'url_api' => 'https://api-vetas.mos.ru',
    'pagination_offset' => 0,
    'pagination_limit'  => 10,
    'static_routes' => ['v1/files'],
    'resources_media_dir' => '/upload',
    'callToHomeSpecializationId' => 17,
    'televeterinary_type' => 18,
    'logging' => [],
    'mosru' => [
        'species_ids' => [
            'cats' => 9,
            'dogs' => 25,
            'other' => 36
        ]
    ],
    'identification' => [
        'batch_size' => 1000,
    ],
    'unsubscribeValidationKey' => 'y1QcHxiKgWhCzwYa1Uw1NQIzbFhPSZlq',
    'passwordEncryptionKey' => 'KEUDfBcb+SiOarQeWwT5G9Zf4R5JZ8zu7x1ClgR+th4=',
    'deleteOldVisitsForUnauthMosruClients' => [
        // Возраст приёма в формате \DateInterval, https://www.php.net/manual/en/dateinterval.createfromdatestring.php
        'visitAge' => '30 days',
    ],
];

if (file_exists(__DIR__ . '/rabbit-local.php')) {
    $params = ArrayHelper::merge(
        $params,
        require(__DIR__ . '/rabbit-local.php')
    );
} else {
    $params = ArrayHelper::merge(
        $params,
        require(__DIR__ . '/rabbit.php'));
}

if (is_readable(__DIR__ . '/payment-gateway-local.php')) {
    $payment_gateway_config = ArrayHelper::merge(
        $params,
        require(__DIR__ . '/payment-gateway-local.php')
    );
} else {
    $payment_gateway_config = ArrayHelper::merge(
        $params,
        require(__DIR__ . '/payment-gateway.php')
    );
}
$params['payment_gateway_config'] = $payment_gateway_config;

$asur_websocket_config = require(__DIR__ . '/asur-websocket.php');
$params['asur_websocket'] = $asur_websocket_config;

// ЕФСП
$efsp_config = require(__DIR__ . '/efsp.php');
if (is_readable(__DIR__ . '/efsp-local.php')) {
    $efsp_config = ArrayHelper::merge(
        $efsp_config,
        require(__DIR__ . '/efsp-local.php')
    );
}
$params['efsp'] = $efsp_config;

if (file_exists(__DIR__ . '/params-local.php')) {
    $params = ArrayHelper::merge(
        $params,
        require(__DIR__ . '/params-local.php')
    );
}

return $params;
