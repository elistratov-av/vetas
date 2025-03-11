<?php

$config = [
    'class' => \app\common\components\inform\SpkService::class,
    'eventUrl' => 'https://spk.mos.ru/app/events/send', // URL для отправки событий в ИС ПК
    'subscriptionsUrl' => 'https://subs.mos.ru/v2', // URL для работы с API подписок  ИС ПК
    'serviceName' => 'vetas', // название сервиса в ИС ПК
    'eventAccessToken' => '2955bc08-2d4f-4673-b780-b64fdb865ef6', // токен для событий, для отправки в ИС ПК
    'subscriptionsAccessToken' => '7c39601b-3cf8-4b79-8932-4e4344941aa1', // токен для подписок, для отправки в ИС ПК
    'tokenTtl' => 86400, // время жизни токена для подписки
    'linkToSubscribe' => 'https://api.vetas.mos.ru/subscription/confirm?token=%s',
    'linkToUnsubscribe' => 'https://api.vetas.mos.ru/subscription/unsubscribe?token=%s',
    'linkRegistration' => 'https://www.mos.ru/pgu/ru/services/link/2500/', // ссылка для записи на приём
    'fileRepositoryDomain' => 'https://api.vetas.mos.ru/', //домен для файлов
    'linkToVaccineFeedbackForm' => 'https://vetas.mos.ru/feedback/vaccination/public/%s', //ссылка на форму обратной связи на фронте
    'linkToFilePage' => 'vetas.mos.ru/attached-files/%s', // ссылка на страницу с файлами
    'linkToLoginPage' => 'vetas.mos.ru/', // ссылка на страницу аутентификации приложения для оповещений
    'enableELK' => false, // включить отправку уведомлений в ЕЛК по sso_id
    'linkToVisit' => 'vetas.mos.ru/visits/%s/main-info'
];




    // 'subscriptionsUrl' => 'https://subs.mos.ru/v2', // URL для работы с API подписок  ИС ПК
    // 'serviceName' => 'vetas', // название сервиса в ИС ПК
    // 'eventAccessToken' => '2955bc08-2d4f-4673-b780-b64fdb865ef6', // токен для событий, для отправки в ИС ПК
    // 'subscriptionsAccessToken' => '7c39601b-3cf8-4b79-8932-4e4344941aa1', // токен для подписок, для отправки в ИС ПК
    // 'tokenTtl' => 86400, // время жизни токена для подписки
    // 'linkToSubscribe' => 'https://pets.mos.ru/subscription/confirm?token=%s',
    // 'linkToUnsubscribe' => 'https://pets.mos.ru/subscription/unsubscribe?token=%s',
    // 'linkToVaccineFeedbackForm' => 'https://vetas.mos.ru/feedback/vaccination/public/%s', //ссылка на форму обратной связи на фронте
    // 'linkToFilePage' => 'https://vetas.mos.ru/attached-files/%s', // ссылка на страницу с файлами
    // 'fileRepositoryDomain' => 'https://pets.mos.ru/' //домен для файлов

#'eventAccessToken' => '61360acf-12f0-4f73-9807-0b8c90eed8fe', // токен для событий, для отправки в ИС ПК
#'subscriptionsAccessToken' => '7c39601b-3cf8-4b79-8932-4e4344941aa1', // токен для подписок, для отправки в ИС ПК

#'eventAccessToken' => '66dc1c1c-9a5d-40d1-b5ae-3828b9bbceb8', // токен для событий, для отправки в ИС ПК
#'subscriptionsAccessToken' => 'b5c75d60-c750-45ba-a715-17668ee8bf4e', // токен для подписок, для отправки в ИС ПК

// if (file_exists(__DIR__ . '/spk-local.php')) {
//     $config = \yii\helpers\ArrayHelper::merge(
//         $config,
//         require(__DIR__ . '/spk-local.php')
//     );
// }

return $config;
