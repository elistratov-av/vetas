<?php

$params = [
    'soapServiceUrl' => null,
    'wsdlUrl' => null,
    'allowedIPs' => ['127.0.0.1'],
    'enabled' => true,
    'enableLog' => false,
];

if (file_exists(__DIR__ . '/params-local.php')) {
    $params = \yii\helpers\ArrayHelper::merge(
        $params,
        require(__DIR__ . '/params-local.php')
    );
}

return $params;
