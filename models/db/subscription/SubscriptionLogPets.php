<?php


namespace app\models\db\subscription;


use app\models\db\ActiveRecord;
/**
 * This is the model class for table "subscription.subscriptions".
 *
 * @property int $id
 * @property int $id_log
 * @property int $id_pet
 */
class SubscriptionLogPets extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription.log_pets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_log', 'id_pet'], 'integer']
        ];
    }
}