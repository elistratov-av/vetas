<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "violation_history".
 *
 * @property int $id_change
 * @property int $id_violation
 * @property int $id_inspector
 * @property string $date
 * @property string $description Заполняется автоматически при совершении действия над нарушением
 *
 * @property Users $inspector
 * @property Violation $violation
 */
class ViolationHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'violation_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_violation', 'id_inspector', 'date', 'description'], 'required'],
            [['id_violation', 'id_inspector'], 'default', 'value' => null],
            [['id_violation', 'id_inspector'], 'integer'],
            [['date'], 'safe'],
            [['description'], 'string'],
            [['description'], FullTrimValidator::class],
            [['id_inspector'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['id_inspector' => 'id']],
            [['id_violation'], 'exist', 'skipOnError' => true, 'targetClass' => Violation::class, 'targetAttribute' => ['id_violation' => 'id_violation']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_change' => 'Id Change',
            'id_violation' => 'Id Violation',
            'id_inspector' => 'Id Inspector',
            'date' => 'Date',
            'description' => 'Заполняется автоматически при совершении действия над нарушением',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspector()
    {
        return $this->hasOne(Users::class, ['id' => 'id_inspector']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolation()
    {
        return $this->hasOne(Violation::class, ['id_violation' => 'id_violation']);
    }
}
