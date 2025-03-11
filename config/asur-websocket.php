<?php

$config = [
    'server_host' => '10.19.95.136', // Хост где хостится AsurWebsocketServer
    'server_port' => '8082', // Порт где хостится AsurWebsocketServer
];

if (file_exists(__DIR__ . '/asur-websocket-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge(
        $config,
        require(__DIR__ . '/asur-websocket-local.php')
    );
}

return $config;
