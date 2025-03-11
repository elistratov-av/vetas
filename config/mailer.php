<?php

return [
    'class' => 'yii\swiftmailer\Mailer',
    'enableSwiftMailerLogging' => true,
    'viewPath' => '@app/common/mail',
    'useFileTransport' => true,
    // 'transport' => [
    //     'class' => 'Swift_SmtpTransport',
    //     'host' => 'smtp.example.com',
    //     'username' => 'user@example.com',
    //     'password' => 'password',
    //     'port' => '465',
    //     'encryption' => 'ssl',
    // ],
];
