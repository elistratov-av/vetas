<?php

return [
    'components' => [
        'response'   => [
            'class' => \yii\web\Response::class,
            'format' => \yii\web\Response::FORMAT_XML,
        ],
        'authLogger' => [
            'class' => \app\modules\adminfstek\components\ExternalLogManager::class,
            'serviceName' => 'mdm',
            'protocol' => 'https',
        ],
    ],
    'params' => require_once('params.php')
];
