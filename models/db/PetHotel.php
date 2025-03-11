<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use app\modules\admin\models\Organization;

/**
 * This is the model class for table "pet_hotel".
 *
 * @property int $id
 * @property string $name
 * @property int $id_organization
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class PetHotel extends ActiveRecord
{

    public $free_rooms;
    public $busy_rooms;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_hotel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'id_organization'], 'required'],
            [['name'], FullTrimValidator::class],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['id_organization', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'id_organization' => 'Организация',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id_organization' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRooms()
    {
        return $this->hasMany(PetHotelRoom::class, ['id_pet_hotel' => 'id']);
    }

    public function __toString()
    {
        return "PetHotel:id={$this->id},name='{$this->name},id_organization='{$this->id_organization}'";
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields[] = 'free_rooms';
        $fields[] = 'busy_rooms';
        return $fields;
    }

}
