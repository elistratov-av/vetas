<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "violation_cancellation".
 *
 * @property int $id_cancellation
 * @property string $description
 * @property bool $is_need_cancellation_details Флаг: при указании данного типа, необходимо заполнить поле violation.cancellation_details
 * @property string $tech_name
 * @property bool $is_deleted
 *
 * @property Violation[] $violations
 */
class ViolationCancellation extends ActiveRecord
{
    const CANCEL_BY_VIOLATION_EXPIRED = 'expired';
    const CANCEL_BY_VACCINATION = 'vaccinated';
    const CANCEL_BY_IDENTIFICATION = 'identified';
    const CANCEL_BY_DEATH = 'death';

    const PUBLIC_TECH_NAMES = [
        self::CANCEL_BY_DEATH,
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'violation_cancellation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'tech_name'], 'string'],
            [['is_need_cancellation_details', 'is_deleted'], 'boolean'],
            [['description'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_cancellation' => 'Id Cancellation',
            'description' => 'Description',
            'tech_name' => 'Tech Name',
            'is_need_cancellation_details' => 'Флаг: при указании данного типа, необходимо заполнить поле violation.cancellation_details',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolations()
    {
        return $this->hasMany(Violation::class, ['id_cancellation' => 'id_cancellation']);
    }
}
