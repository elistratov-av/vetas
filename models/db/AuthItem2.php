<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */
class AuthItem2 extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type', 'description'], 'required'],
            [['name', 'description'], FullTrimValidator::class],
            [['name'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'ID',
            'type' => 'Тип',
            'description' => 'Описание',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}