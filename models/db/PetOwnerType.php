<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pet_owner_type".
 *
 * @property int $id
 * @property string $name
 * @property bool $is_owner
 *
 * @property PetsToOwner[] $petsToOwners
 */
class PetOwnerType extends ActiveRecord
{
    /** @var int Владелец */
    const TYPE_OWNER = 1;

    /** @var int Представитель */
    const TYPE_AGENT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_owner_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_owner'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'is_owner' => 'Is Owner',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetsToOwners()
    {
        return $this->hasMany(PetsToOwner::className(), ['id_owner_type' => 'id']);
    }

    /**
     * @return false|string|null
     */
    public static function findOwnerTypeId()
    {
        return static::find()
            ->select('id')
            ->where(['is_owner' => TRUE])
            ->scalar();
    }

    /**
     * @return false|string|null
     */
    public static function findRepresentativeTypeId()
    {
        return static::find()
            ->select('id')
            ->where(['is_owner' => false])
            ->scalar();
    }
}
