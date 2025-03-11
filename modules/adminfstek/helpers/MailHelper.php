<?php

namespace app\modules\adminfstek\helpers;

/**
 * Class MailHelper
 * @package app\modules\adminfstek\helpers
 */
class MailHelper
{
    /**
     * @param string $email
     * @param string $password
     * @return bool
     */
    public static function sendTemporaryPassword(string $email, string $password)
    {
        try {
            $mailer = self::getMailer();
            /* @var $message \yii\swiftmailer\Message */
            $message = $mailer->compose(['html' => 'temporary-password.php'], ['password' => $password])
                ->setFrom(\Yii::$app->params['emailFrom'])
                ->setTo($email)
                ->setSubject('Установка пароля на сайте ВетАС');

            $result = $message->send();
        } catch (\Throwable $e) {
            $result = false;
            \Yii::error($e->getMessage(), 'mail');
        }

        return $result;
    }

    /**
     * @return \yii\swiftmailer\Mailer
     */
    private static function getMailer()
    {
        return \Yii::$app->getMailer();
    }
}
