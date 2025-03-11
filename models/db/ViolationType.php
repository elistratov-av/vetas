<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "violation_type".
 *
 * @property int $id_type
 * @property string $name
 * @property string $type Константа: тип
 * @property string $tech_name Текстовая константа типа нарушения - используется в логике приложения
 *
 * @property Violation[] $violations
 */
class ViolationType extends ActiveRecord
{
    const TYPE_IDENT_VIOLATION = 'ident_violation';
    const TYPE_VACCINATION_VIOLATION = 'vaccination_violation';
    const TYPE_OTHER_VIOLATION = 'other_violation';

    /*
     * Используются в бизнес логике!
     */
    const V01_IDENT_LACK = 'V01_IDENT_LACK';
    const V02_IDENT_REJECTION = 'V02_IDENT_REJECTION';
    const V03_VACCINATION_DEADLINE = 'V03_VACCINATION_DEADLINE';
    const V04_VACCINATION_REJECTION = 'V04_VACCINATION_REJECTION';
    const V05_OTHER_QUARANTINE_OR_VETERINARY_RULES = 'V05_OTHER_QUARANTINE_OR_VETERINARY_RULES';
    const V06_OTHER_CONCEALMENT_DEATH_OR_MASS_DISEASE = 'V06_OTHER_CONCEALMENT_DEATH_OR_MASS_DISEASE';
    const V07_OTHER_TRANSPORTATION_RULES = 'V07_OTHER_TRANSPORTATION_RULES';
    const V08_OTHER_RULES_OF_BIOLOGICAL_WASTE = 'V08_OTHER_RULES_OF_BIOLOGICAL_WASTE';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'violation_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type', 'tech_name'], 'required'],
            [['name'], 'string'],
            [['type'], 'string', 'max' => 32],
            [
                ['type'], 'in',
                'range' => [self::TYPE_OTHER_VIOLATION, self::TYPE_VACCINATION_VIOLATION, self::TYPE_IDENT_VIOLATION],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [
                ['tech_name'], 'in',
                'range' => [
                    self::V01_IDENT_LACK,
                    self::V02_IDENT_REJECTION,
                    self::V03_VACCINATION_DEADLINE,
                    self::V04_VACCINATION_REJECTION,
                    self::V05_OTHER_QUARANTINE_OR_VETERINARY_RULES,
                    self::V06_OTHER_CONCEALMENT_DEATH_OR_MASS_DISEASE,
                    self::V07_OTHER_TRANSPORTATION_RULES,
                    self::V08_OTHER_RULES_OF_BIOLOGICAL_WASTE,
                ],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['tech_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_type' => 'Id Type',
            'name' => 'Name',
            'type' => 'Константа: тип',
            'tech_name' => 'Текстовая константа типа нарушения - используется в логике приложения',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolations()
    {
        return $this->hasMany(Violation::class, ['id_type' => 'id_type']);
    }
}
