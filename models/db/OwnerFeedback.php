<?php

namespace app\models\db;

use app\common\validators\OrgDictionaryValidator;
use app\models\db\tmc\TmcBase;
use yii\base\DynamicModel;

/**
 * This is the model class for table "user_feedback".
 *
 * @property int $id
 * @property int $id_violation
 * @property int $id_ident_type
 * @property int $id_tmc
 * @property int $id_organization
 * @property boolean $is_out_org
 * @property string $planned_close_date
 * @property string $identification_code
 * @property string $batch
 * @property string $production_date
 * @property string $vaccine_date
 * @property string $expiry_date
 * @property string $valid_until
 * @property boolean $is_processed
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property TmcBase $tmc
 */
class OwnerFeedback extends ActiveRecord
{
    const CHIP_REG_EXP = '~^\d{15}$~';

    public const VACCINE_RULES = [
        [['id_organization', 'id_tmc', 'is_out_org', 'vaccine_date', 'batch', 'valid_until'], 'required']
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'owner_feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_violation'], 'required'],
            [['id_violation', 'id_tmc', 'id_ident_type', 'id_organization'], 'integer'],
            [['batch', 'identification_code'], 'string'],
            [['is_processed', 'is_out_org'], 'boolean'],
            [['planned_close_date', 'expiry_date', 'vaccine_date', 'valid_until', 'production_date'], 'date', 'format' => 'php:Y-m-d'],
            [['id_violation'], 'exist', 'skipOnError' => true, 'targetClass' => Violation::class, 'targetAttribute' => ['id_violation' => 'id_violation']],
            [['id_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => TmcBase::class, 'targetAttribute' => ['id_tmc' => 'id']],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => OrgDictionary::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_organization'], OrgDictionaryValidator::class],
            [['id_tmc', 'batch', 'expiry_date', 'vaccine_date', 'valid_until', 'production_date', 'is_out_org'], function ($attribute, $params, $validator) {
                /** @var Violation $violation */
                $violation = Violation::find()->where(['id_violation' => $this->id_violation])->one();
                if ($violation->type->type !== ViolationType::TYPE_VACCINATION_VIOLATION && $this->$attribute !== null) {
                    $this->addError($attribute, "Свойство не доступно для Нарушений типа '".$violation->type->name."'");
                }
            }],
            [['id_organization', 'id_tmc', 'batch', 'vaccine_date', 'valid_until', 'is_out_org'], 'required', 'when' => function (OwnerFeedback $model) {
                // Возможно создание без данных полей, если фидбек на "запланированую дату отказа"
                /** @var Violation $violation */
                $violation = Violation::find()->where(['id_violation' => $this->id_violation])->one();
                return (!$model->planned_close_date && $violation->type->type === ViolationType::TYPE_VACCINATION_VIOLATION);
            }],
            [['id_ident_type', 'identification_code'], function ($attribute, $params, $validator) {
                /** @var Violation $violation */
                $violation = Violation::find()->where(['id_violation' => $this->id_violation])->one();
                if ($violation->type->type !== ViolationType::TYPE_IDENT_VIOLATION && $this->$attribute !== null) {
                    $this->addError($attribute, "Свойство не доступно для Нарушений типа '".$violation->type->name."'");
                }
            }],
            [['id_ident_type', 'identification_code'], 'required', 'when' => function (OwnerFeedback $model) {
                /** @var Violation $violation */
                $violation = Violation::find()->where(['id_violation' => $this->id_violation])->one();
                return $violation->type->type === ViolationType::TYPE_IDENT_VIOLATION;
            }],
            ['identification_code', 'match', 'pattern' => self::CHIP_REG_EXP, 'when' => function() {
                return $this->id_ident_type == IdentificationTypes::findIdentificationTypeChipId();
            }, 'message' => 'Номер чипа должен содержать 15 чисел.'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getViolation()
    {
        return $this->hasOne(Violation::class, ['id_violation' => 'id_violation']);
    }

    public function getTmc()
    {
        return $this->hasOne(TmcBase::class, ['id' => 'id_tmc']);
    }

    public function getOrganization()
    {
        return $this->hasOne(OrgDictionary::class, ['id' => 'id_organization', 'is_out_org' => 'is_out_org']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->where(['entity_type' => static::tableName()]);
    }

    /**
     * @return bool
     */
    public function validateVaccineFeedback(): bool
    {
        $model = DynamicModel::validateData($this->toArray(), self::VACCINE_RULES);
        if ($model->hasErrors()) return false;
        return true;
    }
}
