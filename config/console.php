<?php

use app\commands\cron\AutoDuplicatesController;
use app\commands\cron\LogCleanController;
use app\commands\cron\TelevetController;

$params = require __DIR__ . '/params.php';
if (file_exists(__DIR__ . '/db-local.php')) {
    $db = require __DIR__.'/db-local.php';
} else {
    $db = require __DIR__.'/db.php';
}

$keys = require __DIR__.'/keys.php';
$container = require __DIR__.'/container.php';
$fias = require __DIR__.'/fias.php';

if (file_exists(__DIR__ . '/mailer-local.php')) {
    $mailer = require __DIR__.'/mailer-local.php';
} else {
    $mailer = require __DIR__.'/mailer.php';
}
$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'soap',
        'soap_queue',
        'soap_queue_v2',
        'subscription_queue',
        'elk',
        'asur_queue',
        'foundPet',
        'found_pet_queue',
    ],
    'controllerNamespace' => 'app\commands',
    'timeZone' => 'Europe/Moscow',
    'modules'    => [
        'soap' => [
            'class' => 'app\modules\soap\Module',
        ],
        'elk' => [
            'class' => \app\modules\elk\Module::class,
        ],
        'foundPet' => [
            'class' => '\app\modules\foundPet\Module',
        ],
    ],
    'aliases' => require_once __DIR__ . '/aliases.php',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'xmlGenerator' => [
            'class' => 'app\common\components\xmlGenerator\XmlGenerator'
        ],
        'signService' => [
            'class' => 'app\common\components\signService\signService',
            'wsdl' => 'http://10.89.79.2:8080/tccs/SignatureValidationService?wsdl',
            'user_agent' => 'PHPSoapClient',
        ],
        'jcpSign' => require_once __DIR__ . '/jcp.php',
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except' => [
                        'validation',
                        'subscription_queue',
                        'app\common\components\inform\InformException',
                        'asur',
                        'odopm',
                        'animalid_input',
                        'animalid_output',
                        'found_pet',
                        'mail',
                        'yii\swiftmailer\Logger::add',
                    ],
                ],
                'etp_log' => [
                    'class' => \app\modules\soap\log\ETPStatusLogTarget::class,
                    'levels' => ['info'],
                    'logVars' => [],
                    'categories' => ['soap_queue', 'soap_queue_v2'],
                ],
                'spk_log' => [
                    'class' => \app\common\components\inform\SubscriptionLogTarget::class,
                    'levels' => ['info'],
                    'logVars' => [],
                    'categories' => ['subscription_queue']
                ],
                'spk_error_log' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/spk_error.log',
                    'categories' => ['subscription_queue', 'app\common\components\inform\InformException']
                ],
                'asur' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['info'],
                    'logFile' => '@app/runtime/logs/asur.log',
                    'logVars' => [],
                    'categories' => ['asur']
                ],
                'asur_error' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/asur_error.log',
                    'logVars' => [],
                    'categories' => ['asur']
                ],
                'odopm' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['info', 'error'],
                    'logFile' => '@app/runtime/logs/odopm.log',
                    'logVars' => [],
                    'categories' => ['odopm']
                ],
                'animalid_input' => [
                    'class' => 'app\modules\animalid\log\AidLogTarget',
                    'levels' => ['error', 'info'],
                    'logVars' => [],
                    'categories' => ['animalid_input'],
                ],
                'animalid_output' => [
                    'class' => 'app\modules\animalid\log\AidLogTarget',
                    'levels' => ['error', 'info'],
                    'logVars' => [],
                    'categories' => ['animalid_output'],
                ],
                'found_pet_module' => [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],
                    'levels' => ['error', 'warning', 'info', 'trace'],
                    'logFile' => '@runtime/logs/found_pet_module.log',
                    'maxFileSize' => 102400,
                    'fileMode' => 0777,
                    'categories' => ['found_pet_module']
                ],
                'spk_rabbit_queue' => [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],
                    'levels' => ['error', 'warning', 'info', 'trace'],
                    'logFile' => '@runtime/logs/spk_rabbit_queue.log',
                    'maxFileSize' => 102400,
                    'fileMode' => 0777,
                    'categories' => ['spk_rabbit_queue']
                ],
                'mailer' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/mailer_error.log',
                    'logVars' => [],
                    'categories' => ['mail', 'yii\swiftmailer\Logger::add'],
                ],
                'soap_module' => [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],
                    'levels' => [
                        'error',
                        'warning',
                        'info',
                        'trace'
                    ],
                    'logFile' => '@runtime/logs/soap_module.log',
                    'maxLogFiles' => 10,
                    'maxFileSize' => 102400,
                    'fileMode' => 0777,
                    'categories' => ['soap_module'],
                ],
            ],
        ],
        'user'       => [
            'class' => \app\common\toolkit\User::class,
            'identityClass' => 'app\common\models\UserModel',
            'enableSession' => false,
        ],
        'db' => $db,
        'jwt'        => [
            'class'          => 'app\common\components\Jwt',
            'privateKeyFile' => $keys['privateKeyFile'],
            'publicKeyFile'  => $keys['publicKeyFile'],
        ],
        'authManager' => [
            'class' => 'app\common\components\rbac\DbManager',
        ],
        'entityCache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@runtime/cache/entities',
        ],
        'spkService' => require_once __DIR__ . '/spk.php',
        'soap_queue' => require __DIR__ . '/queue/soap_queue.php',
        'soap_queue_v2' => require __DIR__ . '/queue/soap_queue_v2.php',
        'subscription_queue' => require __DIR__ . '/queue/subscription_queue.php',
        'asur_queue' => require_once __DIR__ . '/queue/asur_queue.php',
        'asurService' => require_once __DIR__ . '/asur.php',
        'fnsStorage' => require_once __DIR__ . '/fnsStorage.php',
        'asurStorage' => [
            'class' => \app\common\components\asurService\LocalDocumentStorage::class,
            'path' => '/fns'
        ],
        'odopmService' => require_once __DIR__ . '/odopm.php',
        'found_pet_queue' => require_once __DIR__ . '/queue/found_pet_queue.php',
        'mailer' => $mailer,
        'rabbitmq' => [
            'class' => \mikemadisonweb\rabbitmq\Configuration::class,
            'logger' => [
                'log' => true,
                'category' => 'spk_rabbit_queue',
                'print_console' => true,
                'system_memory' => false,
            ],
            'connections' => [
                $params['spk_rabbit']
            ],
            'exchanges' => [
                [
                    'name' => 'spk_exchange',
                    'type' => 'direct'
                    // Refer to Defaults section for all possible options
                ],
            ],
            'queues' => [
                [
                    'name' => $params['spk_rabbit_queues']['spk_event_status_queue'],
                ],
            ],
            'bindings' => [
                [
                    'queue' => $params['spk_rabbit_queues']['spk_event_status_queue'],
                    'exchange' => 'spk_exchange',
                    'routing_keys' => ['YOUR_ROUTING_KEY'],
                ],
            ],
            'producers' => [
                [
                    'name' => 'spk_producer',
                    'connection' => 'spk_rabbit_connection'
                ],
            ],
            'consumers' => [
                [
                    'name' => 'spk_event_status_consumer',
                    'connection' => 'spk_rabbit_connection',
                    // Every consumer should define one or more callbacks for corresponding queues
                    'callbacks' => [
                        // queue name => callback class name
                        $params['spk_rabbit_queues']['spk_event_status_queue'] => \app\common\rabbit\SpkEventStatusConsumer::class,
                    ],
                ],
            ],
        ],
        'authClients' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'efsp' => [
                    'class' => 'app\common\efsp\Client',
                    'clientId' => $params['efsp']['auth']['clientId'],
                    'clientSecret' => $params['efsp']['auth']['clientSecret'],
                    'apiBaseUrl' => $params['efsp']['baseUrl'],
                    'tokenUrl' => $params['efsp']['tokenUrl'] ?? ($params['efsp']['baseUrl'] . '/token'),
                ],
            ],
        ],
    ],
    'container' => $container,
    'params' => $params,
    'on subscription.event' => function(\app\common\components\inform\events\SubscriptionEventInterface $event) {
        $handler = new \app\common\components\inform\events\SubscriptionEventHandler();
        $handler->handle($event);
    },
    'controllerMap' => [
        'migrate' => [
            'class' => 'app\commands\MigrateController',
            'migrationNamespaces' => [
                'yii\queue\db\migrations',
            ],
        ],
        'etp_resender' => [
            'class' => \app\commands\EtpStatusResenderController::class,
        ],
        'televet' => [
            'class' => TelevetController::class
        ],
        'log-clean' => [
            'class' => LogCleanController::class
        ],
        'auto-duplicates' => [
            'class' => AutoDuplicatesController::class
        ]
    ],
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
