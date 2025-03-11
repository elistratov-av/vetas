<?php

return [
    'components' => \yii\helpers\ArrayHelper::merge(
        require_once('components.php'),
        [
            'response'   => [
                'class' => \yii\web\Response::class,
                'format' => \yii\web\Response::FORMAT_XML,
            ],
            'authLogger' => [
                'class' => \app\modules\adminfstek\components\ExternalLogManager::class,
                'serviceName' => 'mosru',
                'protocol' => 'https',
            ],
        ]
    ),
    'params' => require_once('params.php')
];
