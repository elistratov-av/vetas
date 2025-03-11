<?php

namespace app\models\db;

use Yii;
use yii\base\BaseObject;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\Query;
use yii\jui\Selectable;

/**
 * This is the model class for table "pet_owners_history".
 *
 * @property int $id
 * @property string $date
 * @property int $id_pet
 * @property string $created_by
 * @property string $organization_name
 * @property string $owner_type_old
 * @property string $owner_type_new
 * @property string $owner_name
 * @property int $id_owner
 *
 * @property Pets $pet
 */
class PetOwnersHistory extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()::timestamp without time zone'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_owners_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['id_pet', 'created_by'], 'required'],
            [['id_pet'], 'default', 'value' => null],
            [['id_pet'], 'integer'],
            [['created_by', 'organization_name', 'owner_type_old', 'owner_type_new', 'owner_name'], 'string', 'max' => 255],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::className(), 'targetAttribute' => ['id_pet' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'id_pet' => 'Id Pet',
            'created_by' => 'Created By',
            'organization_name' => 'Organization Name',
            'owner_type_old' => 'Owner Type Old',
            'owner_type_new' => 'Owner Type New',
            'owner_name' => 'Owner Name',
            'id_owner' => 'id Owner'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
    }
}
