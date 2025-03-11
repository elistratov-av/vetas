<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "pet_identification".
 *
 * @property int $id
 * @property int $id_pet
 * @property int $id_ident_type
 * @property string $identification_code
 * @property bool $main_flag
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property int $identif_comp //только для негосударственных организаций!
 * @property int $identif_org
 *
 * @property IdentificationTypes $ident_type
 * @property Pets $pet
 */
class PetIdentification extends ActiveRecord
{
    /** @var string Значение ident_type соответствующее ид-ции по чипу */
    const IDENT_TYPE_CHIP = 1;

    const IDENT_TYPE_BRAND = 2;

    const CHIP_REG_EXP = '~^\d{15}$~';

    /**
     * ВНИМАНИЕ!
     * При изменении main_flag в TRUE срабатывает по триггеру ф-ция
     * set_uniq_main_flag_in_pet_identification, которая проставляет
     * всем остальным записям животного main_flag = FALSE
     */

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_identification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pet', 'id_ident_type', 'identification_code', 'main_flag'], 'required'],
            [['id_pet', 'id_ident_type'], 'default', 'value' => null],
            [['id_pet', 'id_ident_type'], 'integer'],
            [['main_flag'], 'boolean'],
            [['identification_code'], 'string', 'max' => 50],
            [['identification_code'], FullTrimValidator::class],
            // Проверка на стороне ДБ, тк индекс частичный
            // [['id_ident_type', 'identification_code'], 'unique', 'targetAttribute' => ['id_ident_type', 'identification_code']],
            [['id_ident_type'], 'exist', 'skipOnError' => true, 'targetClass' => IdentificationTypes::className(), 'targetAttribute' => ['id_ident_type' => 'id']],
            [['id_pet'], 'exist', 'skipOnError' => true, 'targetClass' => Pets::className(), 'targetAttribute' => ['id_pet' => 'id']],
            ['identification_code', 'match', 'pattern' => self::CHIP_REG_EXP, 'when' => function() {
                return $this->id_ident_type == IdentificationTypes::findIdentificationTypeId('чип');
            }, 'message' => 'Номер чипа должен содержать 15 цифр.'],
            [
                'identification_code',
                function ($attribute, $params, $validator) {
                    if ($this->id_pet !== null && $this->id_ident_type !== null ) {

                        $check = PetIdentification::find()
                            ->where([
                                'identification_code' => $this->identification_code,
                                'id_ident_type' => $this->id_ident_type,
                            ])
                            ->andWhere(['<>','id_pet', $this->id_pet])
                            ->exists();


                        if ($check === true && !($this->getOldAttribute('identification_code') == $this->identification_code && $this->getOldAttribute('id_ident_type') == $this->id_ident_type)) {
                            $this->addError($attribute, "Животное с таким идентификатором уже существует.");
                        }
                    }
                },
                'on' => self::SCENARIO_DEFAULT
            ],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_pet' => 'Id Pet',
            'id_ident_type' => 'Id Ident Type',
            'identification_code' => 'Identification Code',
            'main_flag' => 'Main Flag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdent_type()
    {
        return $this->hasOne(IdentificationTypes::className(), ['id' => 'id_ident_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::className(), ['id' => 'id_pet']);
    }

}
