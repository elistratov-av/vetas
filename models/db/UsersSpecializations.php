<?php

namespace app\models\db;

/**
 * This is the model class for table "users_specializations".
 *
 * @property int $id
 * @property int|null $id_specialization Ссылка на специализацию
 * @property int|null $id_user Ссылка на пользователя
 * @property int|null $created_by
 * @property string|null $created_at
 */
class UsersSpecializations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_specializations';
    }

    /**
     * {@inheritdoc}-
     */
    public function rules()
    {
        return [
            [['id_specialization', 'id_user', 'created_by'], 'default', 'value' => null],
            [['id_specialization', 'id_user', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_specialization' => 'Id Specialization',
            'id_user' => 'Id User',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
        ];
    }
}
