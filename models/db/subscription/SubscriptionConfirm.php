<?php

namespace app\models\db\subscription;

use app\models\db\ActiveRecord;
use app\models\db\Contacts;

/**
 * This is the model class for table "subscription.confirm".
 *
 * @property int $id
 * @property string $token
 * @property int $id_contact
 * @property string $created_at
 * @property string $updated_at
 * @property string $contact_value
 *
 * @property Contacts $contact
 */
class SubscriptionConfirm extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription.confirm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'id_contact', 'contact_value'], 'required'],
            [['id_contact'], 'integer'],
            [['id_contact'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['id_contact'], 'exist', 'skipOnError' => true, 'targetClass' => Contacts::class, 'targetAttribute' => ['id_contact' => 'id']],
            [['contact_value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'id_contact' => 'Id Contact',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contacts::class, ['id' => 'id_contact']);
    }
}
