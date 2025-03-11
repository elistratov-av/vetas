<?php
return [
    'local_rabbit' => [
        'host' => 'rabbitmq',
        'port' => 5672,
        'user' => 'user',
        'password' => 'password',
        'queue_name' => 'ainmalid_inbox',
    ],
    'remote_rabbit' => [
        'host' => '185.58.205.18',
        'port' => 5671,
        'user' => 'aid_producer',
        'password' => 'R2*)*@X',
        'queue_name' => '', //set to empty string
        'exchange' => 'animalid_exchange',
    ],

    // rabbit для обработки данных с ИС ПК
    // Используется пакет mikemadisonweb/yii2-rabbitmq
    // You can pass these parameters as a single `url` option: https://www.rabbitmq.com/uri-spec.html
    'spk_rabbit' => [
        'host' => '10.15.47.129',
        'port' => '5672',
        'user' => 'vetas',
        'password' => 'vetas',
        'vhost' => '/',
        'name' => 'spk_rabbit_connection',
        'heartbeat' => 60,
        'read_write_timeout' => 120,
        'channel_rpc_timeout' => 120,
        'keepalive' => false,
    ],
    'spk_rabbit_queues' => [
        'spk_event_status_queue' => 'external.pcs.stats.product.11',
    ],
    // When multiple connections is used you need to specify a `name` option for each one and define them in producer and consumer configuration blocks
];
