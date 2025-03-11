<?php

namespace app\modules\adminfstek\components;

use app\models\db\admin\AdminUser;
use yii\db\Query;

/**
 * Trait LogNotificationsTrait
 * @package app\modules\adminfstek\components
 */
trait LogNotificationsTrait
{
    /**
     * Отправка события в сервис мониторинга логирования для рассылки уведомлений о сбое логирования
     * @param string $type
     */
    protected static function notifyLogFailed($type)
    {
        try {
            $url = \Yii::$app->params['logFailedNotifyUrl'] . '?' . http_build_query(['type' => $type]);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (curl_exec($ch) === false) {
                \Yii::error('Failed to notify that log faled' . "\n" . curl_error($ch), 'mail');
            }
            curl_close($ch);
        } catch (\Throwable $e) {
            \Yii::error('Failed to notify that log faled' . "\n" . $e->getMessage(), 'mail');
        }
    }

    /**
     * Обновление списка email привилегированных пользователей в сервисе мониторинга логирования
     */
    protected static function updateRecipientsList()
    {
        $data = (new Query())
            ->select('email')
            ->from(AdminUser::tableName())
            ->where([
                'role' => AdminUser::ROLE_SECURITY,
                'is_blocked' => false,
            ])
            ->andWhere(['not', ['email' => null]])
            ->column();

        try {
            $url = \Yii::$app->params['logFailedRecipientsUrl'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["recipients" => $data]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (curl_exec($ch) === false) {
                \Yii::error('Failed to update recipients list' . "\n" . curl_error($ch), 'mail');
            }
            curl_close($ch);
        } catch (\Throwable $e) {
            \Yii::error('Failed to update recipients list' . "\n" . $e->getMessage(), 'mail');
        }
    }
}
