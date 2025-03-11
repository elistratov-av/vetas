<?php

$config = [
    'class' => \app\common\components\asurService\RemoteDocumentStorage::class,
    'url' => 'http://212.11.151.48:80/custom-api-2.0/rest/api',
    //'url' => 'http://10.127.131.38:80/custom-api-2.0/rest/api',
    'login' => 'login',
    'password' => 'password'
];

if (file_exists(__DIR__ . '/fnsStorage-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge(
        $config,
        require(__DIR__ . '/fnsStorage-local.php')
    );
}

return $config;
