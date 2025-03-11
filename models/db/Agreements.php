<?php

namespace app\models\db;

use app\modules\admin\models\Organization;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "public.agreements".
 *
 * @property int $id
 * @property bool $is_agree
 * @property int $id_type
 * @property int $id_pet_owner
 * @property int $id_visit
 * @property int $id_pet
 * @property int $id_organization
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Agreements extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.agreements';
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors[] = [
            'class' => BlameableBehavior::class,
        ];

        $behaviors[] = [
            'class' => TimestampBehavior::class,
            'value' => date("Y-m-d H:i:s"),
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_agree', 'id_type', 'id_pet_owner'], 'required'],
            [['is_agree'], 'boolean'],
            [['id_type', 'id_pet_owner', 'id_visit', 'id_pet', 'id_organization', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_type', 'id_pet_owner', 'id_visit', 'id_pet', 'id_organization', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => AgreementTypes::className(), 'targetAttribute' => ['id_type' => 'id']],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['id_organization' => 'id']],
            [['id_pet_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::className(), 'targetAttribute' => ['id_pet_owner' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::className(), 'targetAttribute' => ['id_pet' => 'id']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::className(), 'targetAttribute' => ['id_visit' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_agree' => 'Is Agree',
            'id_type' => 'Id Type',
            'id_pet_owner' => 'Id Pet Owner',
            'id_visit' => 'Id Visit',
            'id_pet' => 'Id Pet',
            'id_organization' => 'Id Organization',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(AgreementTypes::class, ['id' => 'id_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_pet_owner']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_visit']);
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
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }
}