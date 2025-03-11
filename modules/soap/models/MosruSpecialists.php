<?php

namespace app\modules\soap\models;

use app\common\models\UserModel;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "mosru.specialists".
 *
 * @property int $id_organization
 * @property int $id_specialist
 * @property int $id_user
 * @property string $name
 *
 * @property UserModel $user
 */
class MosruSpecialists extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mosru.specialists';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization', 'id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_organization' => 'Id Organization',
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserModel::class, ['id' => 'id_user']);
    }
}
