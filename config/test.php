<?php
$config = require __DIR__ . '/config.php';
$db = require __DIR__.'/test_db.php';
$params = require __DIR__.'/params.php';
$container = require __DIR__.'/container.php';

$test_conf = [
    'id'         => 'vetais-test',
    'basePath'   => dirname(__DIR__),
    'bootstrap'  => ['log'],
    'params'     => $params,
    'modules'    => $config['modules'],
    'components' => [
        'request'    => $config['components']['request'],
        'log'        => [
            'traceLevel'    => 3,
            'flushInterval' => 1,
            'targets'       => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@app/runtime/logs/test.log',
                ],
            ],
        ],
        'formatter'  => $config['components']['formatter'],
        'response'  => $config['components']['response'],
        'urlManager' => $config['components']['urlManager'],
        'user'       => $config['components']['user'],
        'db'         => $db,
        'jwt'        => $config['components']['jwt'],
        'fileService'   => $config['components']['fileService'],
    ],
    'container' => $container
];

return $test_conf;