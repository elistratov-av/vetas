<?php

use yii\helpers\ArrayHelper;
use yii\web\JsonParser;

if (file_exists(__DIR__ . '/db-local.php')) {
    $db = require __DIR__ . '/db-local.php';
} else {
    $db = require __DIR__ . '/db.php';
}
if (file_exists(__DIR__ . '/mailer-local.php')) {
    $mailer = require __DIR__ . '/mailer-local.php';
} else {
    $mailer = require __DIR__ . '/mailer.php';
}
$keys = require __DIR__ . '/keys.php';
$params = require __DIR__ . '/params.php';
$container = require __DIR__ . '/container.php';
$fias = require __DIR__ . '/fias.php';

$debug = null;
if (YII_DEBUG) {
    if (file_exists(__DIR__ . '/debug-local.php')) {
        $debug = require __DIR__ . '/debug-local.php';
    } else {
        $debug = require __DIR__ . '/debug.php';
    }
}

$bootstrap = [
    'log',
];
if (YII_DEBUG && $debug !== null) {
    $bootstrap[] = 'debug';
}

return [
    'id'         => 'vetais-api',
    'basePath'   => dirname(__DIR__),
    'language' => 'ru-RU',
    'controllerMap' => [
        'swagger' => 'controllers/DocumentationController',
    ],
    'bootstrap'  => array_merge(
        $bootstrap,
        [
            'soap_queue',
            'soap_queue_v2',
            'subscription_queue',
            'audit',
            'elk',
            'asur',
            'asur_queue',
            'mdm',
            'foundPet',
            'found_pet_queue',
        ]
    ),
    'params'     => $params,
    'timeZone' => 'Europe/Moscow',
    'modules'    => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'app\modules\v2\Module',
        ],
        'v3' => [
            'class' => 'app\modules\v3\Module',
        ],
        'soap' => [
            'class' => 'app\modules\soap\Module',
        ],
        'elk' => [
            'class' => \app\modules\elk\Module::class,
        ],
        'mdm' => [
            'class' => 'app\modules\mdm\Module',
        ],
        'asur' => [
            'class' => \app\modules\asur\Module::class,
        ],
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'animalid' => [
            'class' => 'app\modules\animalid\Module',
        ],
        'debug' => $debug,
        'sign' => [
            'class' => 'app\modules\sign\Module',
        ],
        'adminv' => [
            'class' => 'app\modules\adminv\Module',
            'layoutPath' => '@modules/adminv/views/layouts',
            'viewPath' => '@modules/adminv/views',
        ],
        'subscription' => [
            'class' => \app\modules\subscription\Module::class,
            'layoutPath' => '@modules/subscription/views/layouts',
            'viewPath' => '@modules/subscription/views',
        ],
        'audit' => [
            'class' => '\app\modules\audit\Module',
        ],
        'foundPet' => [
            'class' => '\app\modules\foundPet\Module',
        ],
        'adminfstek' => [
            'class' => '\app\modules\adminfstek\Module',
        ],
    ],
    'components' => [
        'request' => [
            'enableCsrfValidation' => false,
            'enableCookieValidation' => false,
            'enableCsrfCookie' => false,
            'ipHeaders' => [
                'X-Real-Ip',
                'X-Forwarded-For',
            ],
            'parsers' => [
                'application/json' => JsonParser::class,
            ],
        ],
        'log' => ArrayHelper::merge([
            'traceLevel'    => 3,
            'flushInterval' => 1,
            'targets'       => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/main.log',
                    'maxFileSize' => 20480,
                    'maxLogFiles' => 10,
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
                        'external-service-log',
                    ],
                ],
                'sign_log' => [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    'levels' => ['info'],
                    'logFile' => '@app/runtime/logs/sign.log',
                    'categories' => ['sign']
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
                'soap' => [
                    'class' => 'yii\log\FileTarget',
                    'logVars' => [],
                    'levels' => [
                        'error',
                        'warning',
                        'info',
                    ],
                    'logFile' => '@runtime/logs/soap.log',
                    'maxLogFiles' => 10,
                    'maxFileSize' => 102400,
                    'categories' => [
                        'soap*',
                        'app\\modules\\soap\\*',
                    ],
                ],
                'mailer' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/mailer_error.log',
                    'logVars' => [],
                    'categories' => ['mail', 'yii\swiftmailer\Logger::add'],
                ],
                'external_service_log' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/external_service_log.log',
                    'categories' => ['external-service-log'],
                ],
            ],
        ], $params['logging']),
        'urlManager' => [
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'rules'               => [
                [
                    'class' => \yii\rest\UrlRule::class,
                    'pluralize' => false,
                    'controller' => [
                        'wtf/pet',
                        'wtf/aviary',
                        'wtf/documentTypes',
                    ],
                ],
                'admin/login' => 'admin/entries/login',
                'admin/error' => 'admin/entries/error',
                'admin/profile' => 'admin/entries/profile',

                'admin/statistics/<controller:[\w-]+>/<action:[\w-]+>' => 'admin/statistics/<controller>/<action>',
                'admin/statistics/<controller:[\w-]+>' => 'admin/statistics/<controller>',

                'admin/<controller:[\w-]+>/<action:[\w-]+>' => 'admin/<controller>/<action>',
                'admin/<controller:[\w-]+>' => 'admin/<controller>',
                'admin' => 'admin',
                '' => '',

                'vetadmin' => 'adminv/site/index',
                'vetadmin/login' => 'adminv/site/login',
                'vetadmin/logout' => 'adminv/site/logout',
                'vetadmin/error' => 'adminv/site/error',
                'vetadmin/statistics/<controller:[\w-]+>/<action:[\w-]+>' => 'adminv/statistics/<controller>/<action>',
                'vetadmin/statistics/<controller:[\w-]+>' => 'adminv/statistics/<controller>/index',
                'vetadmin/<controller:[\w-]+>' => 'adminv/<controller>/index',
                'vetadmin/<controller:[\w-]+>/<action:[\w-]+>' => 'adminv/<controller>/<action>',
                'vetadmin/audit/<controller:[\w-]+>/<action:[\w-]+>' => 'adminv/audit/<controller>/<action>',
                'vetadmin/audit/<controller:[\w-]+>' => 'adminv/audit/<controller>/index',

                'adminf' => 'adminfstek/site/index',
                'adminf/login' => 'adminfstek/site/login',
                'adminf/logout' => 'adminfstek/site/logout',
                'adminf/error' => 'adminfstek/site/error',
                'adminf/<controller:[\w-]+>' => 'adminfstek/<controller>/index',
                'adminf/<controller:[\w-]+>/<action:[\w-]+>' => 'adminfstek/<controller>/<action>',

                'subscription' => 'subscription/default/index',
                'subscription/error' => 'subscription/default/error',
                'subscription/confirm' => 'subscription/default/confirm',
                'subscription/confirm-success' => 'subscription/default/confirm-success',
                'subscription/confirm-error' => 'subscription/default/confirm-error',
                'subscription/unsubscribe' => 'subscription/default/unsubscribe',
                'subscription/unsubscribe-success' => 'subscription/default/unsubscribe-success',
                'subscription/unsubscribe-error' => 'subscription/default/unsubscribe-error',

                'test-scheme' => 'test/scheme',
                'test-rels' => 'test/relations',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/fias',
                    'extraPatterns' => [
                        'GET <action:\w+>' => '<action>',
                        'POST save' => 'save',
                    ]
                ],
                'soap/<controller:\w+>' => 'soap/<controller>',
                'soap/<controller:[\w-]+>/<action:[\w-]+>' => 'soap/<controller>/<action>',

                'animalid/<controller:\w+>' => 'animalid/<controller>',
                'animalid/<controller:[\w-]+>/<action:[\w-]+>' => 'animalid/<controller>/<action>',

                'OPTIONS v2/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => 'v2/<module>/<controller>/options_bumper',
                'v2/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => 'v2/<module>/<controller>/<action>',
                'v3/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => 'v3/<module>/<controller>/<action>',
                'debug/<controller:\w+>' => 'debug/<controller>',
                'debug/<controller:[\w-]+>/<action:[\w-]+>' => 'debug/<controller>/<action>',
                'documentation/<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                'documentation' => 'documentation/index',
                'v3/analytics/dictionary' => 'v3/analytics/base/get-dictionary',
                'v3/shelters/dictionary' => 'v3/shelters/base/get-dictionary',
                'v3/external-services/vks' => 'v3/external-services/vks/index',
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/balance-flow',
                    'patterns' => [],
                    'extraPatterns' => [
                        'POST'     => 'create',
                        'OPTIONS' => 'options',
                    ],
                ],
                [
                    'class'         => 'yii\rest\UrlRule',
                    'controller'    => 'v1/service-reports',
                    'extraPatterns' => [
                        'POST {id}'     => 'create',
                        'GET {id}/files' => 'files',
                        'GET available/{id}' => 'available',
                        'GET available-pdf/{id}' => 'available-pdf',
                        'GET get-all-by-visit/{id}' => 'get-all-by-visit',
                        'PUT update-batch' => 'update-batch',
                        'OPTIONS {id}'     => 'options',
                        'OPTIONS {id}/files' => 'options',
                        'OPTIONS available/{id}' => 'options',
                        'OPTIONS available-pdf/{id}' => 'options',
                        'OPTIONS get-all-by-visit/{id}' => 'options',
                        'OPTIONS update-batch' => 'options',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/visits',
                    'patterns' => [],
                    'extraPatterns' => [
                        'GET {id}/service-types-description' => 'service-types-description',
                    ]
                ],
                [
                    'class' => 'app\common\components\entity\EntityUrlRule',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET {id}/relationships/{entity}' => 'getrel',
                        'GET {id}/{entity}' => 'getrel',
                        'POST {id}/{entity}/{entity_id}' => 'add-rel-by-id',
                        'POST {id}/{entity}' => 'addrel',
                        'PUT {id}/{entity}/{entity_id}' => 'update-rel-by-id',
                        'DELETE {parent_id}/{entity}' => 'delrel',
                        'DELETE {parent_id}/{entity}/{id}' => 'delrel',
                        'OPTIONS relationships/{entity}' => 'options',
                        'OPTIONS {id}/relationships/{entity}' => 'options',
                        'OPTIONS {id}/{entity}' => 'options',
                        'OPTIONS {parent_id}/{entity}/{id}' => 'options',
                    ]
                ],
                'sign/<controller:\w+>' => 'sign/<controller>',
                'sign/<controller:[\w-]+>/<action:[\w-]+>' => 'sign/<controller>/<action>',
            ],
        ],
        'user'       => [
            'class' => 'app\common\components\rbac\User',
            'identityClass' => 'app\common\models\UserModel',
            'enableSession' => false,
        ],
        'db'         => $db,
        'jwt'        => [
            'class'          => 'app\common\components\Jwt',
            'privateKeyFile' => $keys['privateKeyFile'],
            'publicKeyFile'  => $keys['publicKeyFile'],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'php:Y-m-d',
            'timeFormat' => 'php:H:i'
        ],
        'response'   => [
            'format' => yii\web\Response::FORMAT_JSON,
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class'         => 'tuyakhov\jsonapi\JsonApiResponseFormatter',
                    'prettyPrint'   => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'errorHandler' => [
            'class' => 'app\common\components\ErrorHandler',
        ],
        'fileService' => [
            'class' => 'app\common\components\FileService',
            'availableTypes' => ['pdf', 'xls', 'xlsx', 'doc', 'docx', 'txt', 'jpg', 'png', 'zip', 'rar'],
            'repository' => [
                'class' => app\common\components\media\MediaRepositoryInterface::class,
                'path' => '/upload/file',
                'depth' => 3
            ]
        ],
        'pdfGenerator' => [
            'class' => 'app\common\components\pdfGenerator\PdfGenerator'
        ],
        'wordGenerator' => [
            'class' => 'app\common\components\wordGenerator\WordGenerator'
        ],
        'xmlGenerator' => [
            'class' => 'app\common\components\xmlGenerator\XmlGenerator'
        ],
        'authManager' => [
            'class' => 'app\common\components\rbac\DbManager',
        ],
        'addressService' => [
            'class' => 'app\common\components\AddressService',
            'adapter' => [
                'class' => 'app\common\components\address\FiasRemoteAdapter',
                'host' => $fias['host'],
                'port' => $fias['port'],
            ],
        ],
        'signService' => [
            'class' => 'app\common\components\signService\signService',
            'wsdl' => 'http://10.89.79.2:8080/tccs/SignatureValidationService?wsdl', //todo тестовый айпишник для мока - заменить на боевой
            'user_agent' => 'PHPSoapClient',
            'connection_timeout' => 180,
            'exceptions' => false,
            'skip' => true //мокает боевой сервис валидации ЭЦП
        ],
        'jcpSign' => require_once __DIR__ . '/jcp.php',
        'spkService' => require_once __DIR__ . '/spk.php',
        'soap_queue' => require __DIR__ . '/queue/soap_queue.php',
        'soap_queue_v2' => require __DIR__ . '/queue/soap_queue_v2.php',
        'subscription_queue' => require_once __DIR__ . '/queue/subscription_queue.php',
        'asur_queue' => require_once __DIR__ . '/queue/asur_queue.php',
        'asurService' => require_once __DIR__ . '/asur.php',
        'fnsStorage' => require_once __DIR__ . '/fnsStorage.php',
        'reportService' => require_once __DIR__ . '/report-service.php',
        'asurStorage' => [
            'class' => \app\common\components\asurService\LocalDocumentStorage::class,
            'path' => '/fns'
        ],
        'odopmService' => require_once __DIR__ . '/odopm.php',
        'found_pet_queue' => require_once __DIR__ . '/queue/found_pet_queue.php',
        'rabbitmq' => [
            'class' => \mikemadisonweb\rabbitmq\Configuration::class,
            'logger' => [
                'log' => true,
                'category' => 'application',
                'print_console' => true,
                'system_memory' => false,
            ],
            'connections' => [
                $params['spk_rabbit']
            ],
            'queues' => [
                [
                    'name' => $params['spk_rabbit_queues']['spk_event_status_queue'],
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
        'userSessionManager' => [
            'class' => \app\modules\adminfstek\components\UserSessionManager::class,
        ],
        'mailer' => $mailer,
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ]
    ],
    'container' => $container,
    'on file.attach' => function (\app\common\events\FileAttachEvent $event) {
        /** @var \app\common\components\FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $fileService->attach($event->file);
    },
    'on file.delete' => function (\app\common\events\FileDeleteEvent $event) {
        /** @var \app\common\components\FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $fileService->delete($event->type, $event->id, $event->path);
    },
    'on subscription.event' => function (\app\common\components\inform\events\SubscriptionEventInterface $event) {
        $handler = new \app\common\components\inform\events\SubscriptionEventHandler();
        $handler->handle($event);
    },
    'on ' . \yii\base\Application::EVENT_BEFORE_REQUEST => function ($event) {
        try {
            \app\models\db\admin\SecuritySettings::loadParams();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
        }
    },
    'aliases' => require_once __DIR__ . '/aliases.php',
];
