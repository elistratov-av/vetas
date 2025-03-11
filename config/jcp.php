<?php

$config = [
    'class' => 'app\common\components\jcpSign\JcpSignService',
    'uri' => 'http://172.29.27.2:8080/brogisign-1.0/signature/sign' //todo ПОМЕНЯТЬ на ip томката на контуре
];


if (file_exists(__DIR__ . '/jcp-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge(
        $config,
        require(__DIR__ . '/jcp-local.php')
    );
}

return $config;
