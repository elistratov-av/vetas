<?php

$config = [
    'class' => \app\common\components\odopm\ODOPMService::class,
    'wsdl' => 'https://op.mos.ru/EHDWS/soap?wsdl',
    'username' => 'vetas',
    'password' => 'g~6W6DIw',
    'environment' => 'prod',
    'signRequests' => true,
];

if (file_exists(__DIR__ . '/odopm-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge(
        $config,
        require(__DIR__ . '/odopm-local.php')
    );
}

return $config;
