<?php

namespace app\models\db\spk;

use Yii;

/**
 * This is the model class for table "spk.subscription".
 *
 * @property int $id
 * @property int $id_task ID задания на обновление
 * @property int $ext_id ID в системе ИС ПК
 * @property string $stream Тип коммуникационного канала
 * @property string $email Адрес электронной почты (контакта)
 * @property string $msisdn Номер телефона (контакта)
 * @property string $service Код сервиса подписки
 * @property array $options Опции подписки для сервиса (при наличии)
 * @property string $expiration Время окончания срока действия подписки
 * @property string $created Время создания подписки (по данным ИС ПК)
 * @property array $day_time Дни недели, по которым контакту можно рассылать уведомления
 */
class SpkSubscription extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'spk.subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_task', 'ext_id'], 'default', 'value' => null],
            [['id_task', 'ext_id'], 'integer'],
            [['options', 'expiration', 'created', 'day_time'], 'safe'],
            [['stream', 'msisdn'], 'string', 'max' => 16],
            [['service'], 'string', 'max' => 32],
            [['email'], 'string'],
            [['id_task'], 'exist', 'skipOnError' => true, 'targetClass' => SpkUpdateSubscriptionTask::class, 'targetAttribute' => ['id_task' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_task' => 'Id Task',
            'ext_id' => 'Ext ID',
            'stream' => 'Stream',
            'email' => 'Email',
            'msisdn' => 'Msisdn',
            'service' => 'Service',
            'options' => 'Options',
            'expiration' => 'Expiration',
            'created' => 'Created',
            'day_time' => 'Day Time',
        ];
    }
}
