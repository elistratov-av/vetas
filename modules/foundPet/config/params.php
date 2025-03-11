<?php

$params = [
    'mosru_env' => 'dev',
    'mosru_urls' => [
        'dev' => [
            'user_ads' => 'http://ms.mpgu.srvdev.ru/ru/app/dvm/067901/?ads=my',
            'single_ad' => 'http://ms.mpgu.srvdev.ru/ru/app/dvm/067901/?type={type}&number={id}',
            'file_service_url' => 'https://doc-upload2.mos.ru/universal-form/uform3.0/service/getcontent?os=GU_DOCS&id={uid}',
        ],
        'prod' => [
            'user_ads' => 'https://www.mos.ru/pgu/ru/application/dvm/067901/?ads=my',
            'single_ad' => 'https://www.mos.ru/pgu/ru/application/dvm/067901/?type={type}&number={id}',
            'file_service_url' => 'https://doc-upload2.mos.ru/universal-form/uform3.0/service/getcontent?os=GU_DOCS&id={uid}',
        ],
    ],
];

if (file_exists(__DIR__ . '/params-local.php')) {
    $params = \yii\helpers\ArrayHelper::merge(
        $params,
        require(__DIR__ . '/params-local.php')
    );
}

return $params;
