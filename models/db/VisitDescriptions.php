<?php

namespace app\models\db;

/**
 * Class VisitDescriptions
 * @package app\models\db
 *
 * @property int    $id
 * @property string $description
 * @property int    $id_visit
 * @property int    $id_description_type
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class VisitDescriptions extends ActiveRecord
{
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_UPDATE_WHEN_VISIT_FINISHED = 'update_when_visit_finished';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.visit_descriptions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'description',
                'required',
                'on' => [self::SCENARIO_UPDATE_WHEN_VISIT_FINISHED],
                'except' => [self::SCENARIO_DEFAULT, self::SCENARIO_UPDATE],
            ],
            ['description', 'filter', 'filter' => 'trim'],
            ['description', 'filter', 'filter' => 'strip_tags'],
            ['description', 'string'],
            [['id_visit', 'id_description_type'], 'required'],
            [['id_visit', 'id_description_type'], 'integer'],
            [
                'id_visit',
                'exist',
                'targetClass' => Visits::class,
                'targetAttribute' => ['id_visit' => 'id'],
            ],
            [
                'id_pet',
                'exist',
                'targetClass' => Pets::class,
                'targetAttribute' => ['id_pet' => 'id'],
            ],
            [
                'id_description_type',
                'exist',
                'targetClass' => DescriptionTypes::class,
                'targetAttribute' => ['id_description_type' => 'id'],
                'filter' => ['entity_type' => 'visit'],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescription_type()
    {
        return $this->hasMany(DescriptionTypes::class, ['id' => 'id_description_type']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescription_types()
    {
        return $this->hasOne(DescriptionTypes::class, ['id' => 'id_description_type']);
    }
}
