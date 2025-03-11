<?php

$components = [
    'etp' => [
        'class' => \app\modules\soap\models\etp\ETP::class,
        'schemePath' => '@app/modules/soap/config/scheme.xsd',
        'sendUrl' => 'localhost:8081/api/send'
    ],
];

if (file_exists(__DIR__ . '/components-local.php')) {
    $components = \yii\helpers\ArrayHelper::merge(
        $components,
        require(__DIR__ . '/components-local.php')
    );
}

return $components;
