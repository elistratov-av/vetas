<?php

namespace app\models\db\elk;

use app\common\validators\SnilsValidator;
use app\models\db\ActiveRecord;
use app\models\db\PetOwners;

/**
 * This is the model class for table "elk.owners".
 *
 * @property int $id
 * @property string $sso_id
 * @property int $id_owner
 * @property string $first_name
 * @property string $last_name
 * @property string $middle_name
 * @property string $phone
 * @property string $email
 * @property string $snils
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PetOwners $owner
 * @property ElkPets[] $elkPets
 */
class ElkOwners extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'elk.owners';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_owner'], 'default', 'value' => null],
            [['id_owner'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['sso_id', 'first_name', 'last_name', 'middle_name', 'phone', 'email', 'snils'], 'string', 'max' => 255],
            ['snils', 'filter', 'filter' => function($value){
                return preg_replace("/[^0-9]/", "", $value);
            }],
            [['snils'], 'string', 'min' => 11, 'max' => 11],
            ['snils', SnilsValidator::class],
            [['id_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sso_id' => 'Sso ID',
            'id_owner' => 'Id Owner',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'middle_name' => 'Middle Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'snils' => 'Snils',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElkPets()
    {
        return $this->hasMany(ElkPets::class, ['id_elk_owner' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
    }
}
