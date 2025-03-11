<?php

namespace app\models\db;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * @property int    $id_pet
 * @property int    $id_organization
 * @property string $description
 * @property string $created_at
 * @property int    $created_by
 */
class ShelterVaccineRejection extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shelter_vaccine_rejection';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_organization', 'id_pet', 'description'], 'required'],
            [['id_organization', 'id_pet'], 'integer'],
            [['description'], 'string'],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            [['created_at', 'created_by'], 'safe'],
        ];
    }
}
