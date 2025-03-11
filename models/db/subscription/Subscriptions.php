<?php

namespace app\models\db\subscription;

use app\models\db\ActiveRecord;
use app\models\db\Contacts;

/**
 * This is the model class for table "subscription.subscriptions".
 *
 * @property int $id
 * @property int $id_contact
 * @property bool $subscribed
 * @property int $subscription_id ID подписки на стороне ИС ПК
 * @property string $created_at
 * @property string $updated_at
 */
class Subscriptions extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription.subscriptions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_contact'], 'required'],
            [['id_contact'], 'default', 'value' => null],
            [['id_contact', 'subscription_id'], 'integer'],
            [['subscribed'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_contact'], 'exist', 'skipOnError' => true, 'targetClass' => Contacts::class, 'targetAttribute' => ['id_contact' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_contact' => 'Id Contact',
            'subscribed' => 'Subscribed',
            'subscription_id' => 'Subscription ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
