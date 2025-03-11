<?php


namespace app\models\db\found_pet;

use app\models\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class MessageSent - отправленные статусы
 * @package app\models\db\found_pet
 *
 * @property int          $id
 * @property string       $type
 * @property string       $service_number
 * @property array|string $request
 * @property array|string $response
 * @property array|string $response_headers
 * @property array|string $response_code
 * @property string       $user_error
 * @property string       $curl_error
 * @property string       $created_at
 */
class MessageSent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => new Expression('NOW()::timestamp without time zone'),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'found_pet.messages_sent';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [$this->attributes(), 'safe']
        ];
    }
}