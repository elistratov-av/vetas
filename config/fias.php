<?php

$config = [
    'host' => 'https://fias.pet.altarix.org',
    'port' => '443',
];

if (file_exists(__DIR__ . '/fias-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge(
        $config,
        require(__DIR__ . '/fias-local.php')
    );
}

return $config;
