<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "reg_certificates".
 *
 * @property int $id
 * @property string $date
 * @property string $number
 * @property int $id_pet
 * @property int $id_owner
 * @property int $phone
 * @property int $mail
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property bool $to_update
 *
 * @property Contacts $contact_phone
 * @property Contacts $contact_mail
 * @property PetOwners $owner
 * @property Pets $pet
 * @property Files
 * @property RegCertificatesHistory[] $regUpdates
 */
class RegCertificates extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reg_certificates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'number', 'id_pet', 'id_owner', 'phone'], 'required'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['id_pet', 'id_owner', 'phone', 'mail', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_pet', 'id_owner', 'phone', 'mail', 'created_by', 'updated_by'], 'integer'],
            [['number'], 'string', 'max' => 255],
            [['number'], 'unique'],
            [['number'], FullTrimValidator::class],
            [['phone'], 'exist', 'skipOnError' => true, 'targetClass' => Contacts::class, 'targetAttribute' => ['phone' => 'id']],
            [['mail'], 'exist', 'skipOnError' => true, 'targetClass' => Contacts::class, 'targetAttribute' => ['mail' => 'id']],
            [['id_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            ['to_update', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            ['to_update', 'default', 'value' => false],
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
            'number' => 'Number',
            'id_pet' => 'Id Pet',
            'id_owner' => 'Id Owner',
            'phone' => 'Phone',
            'mail' => 'Mail',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact_phone()
    {
        return $this->hasOne(Contacts::class, ['id' => 'phone']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact_mail()
    {
        return $this->hasOne(Contacts::class, ['id' => 'mail']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
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
    public function getFile()
    {
        return $this->hasOne(Files::class, ['entity_id' => 'id'])
            ->andWhere(['entity_type' => 'reg_certificate']);
    }
}
