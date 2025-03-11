<?php

$config = [
    'class' => \app\common\components\asurService\ASURService::class,
    'url' => 'http://event-listener-asur:8080/api/send',
    'signUrl' => 'http://sign-service:8080/brogisign-asur-1.1/signature/sign',
    'test' => false,
    'Responsible' => [
        'LastName' => 'Кузнецов',
        'FirstName' => 'Иван',
        'MiddleName' => 'Николаевич',
        'JobTitle' => 'Заместитель начальника Государственной ветеринарной инспекции Комитета ветеринарии города Москвы - главный инспектор',
        'Phone' => '8-495-633-78-30',
        'Email' => 'KuznetsovIN@mos.ru'
    ],
    'Department' => [
        'Name' => 'Комитет ветеринарии города Москвы',
        'Code' => '2071',
        'Inn' => '7725570674',
        'Ogrn' => '1067746617938',
        'RegDate' => '2019-01-01T00:00:00',
        'SystemCode' => '9000142'
    ],
    'FunctionTypeCode' => '010215',
    'TaskNumberMask' => '2071-9000142-010215-0000000/YY/Q', // NNNNNNN, YY и Q заменяемые параметры
];

if (file_exists(__DIR__ . '/asur-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge(
        $config,
        require(__DIR__ . '/asur-local.php')
    );
}

return $config;
