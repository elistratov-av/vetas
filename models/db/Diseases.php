<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use app\models\db\gost_diseases\GostDiseases;
use Yii;

/**
 * This is the model class for table "diseases".
 *
 * @property int $id
 * @property string $name
 * @property bool $flag_danger
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 * @property int $id_gost_disease Идентификатор болезни из справочника болезней ГОСТ
 *
 * @property BreedsDiseases[] $breedsDiseases
 * @property SpeciesDiseases[] $speciesDiseases
 * @property VaccinesToDiseases[] $vaccinesToDiseases
 * @property Vaccines[] $vaccines
 * @property Violation[] $violations
 * @property GostDiseases $gostDisease
 */
class Diseases extends ActiveRecord
{
    /**
     * Название заболевания БЕШЕНСТВО в справочнике
     */
    const NAME_RABIES = 'Бешенство (Rabies)';

    /**
     * Название заболевания ЛЕПТОСПИРОЗ в справочнике
     */
    const NAME_LEPTOSPIROZ = 'Лептоспироз';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'diseases';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['code'], 'string'],
            [['flag_danger'], 'boolean'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by', 'id_gost_disease'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name', 'code'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['name'], FullTrimValidator::class],
            [
                ['id_gost_disease'],
                'exist',
                'skipOnError' => true,
                'targetClass' => GostDiseases::class,
                'targetAttribute' => ['id_gost_disease' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'flag_danger' => 'Flag Danger',
            'gost_code' => 'Gost code',
            'created_by' => 'Автор добавления (id пользователя)',
            'updated_by' => 'Автор последнего изменения (id пользователя)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreedsDiseases()
    {
        return $this->hasMany(BreedsDiseases::class, ['id_disease' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpeciesDiseases()
    {
        return $this->hasMany(SpeciesDiseases::class, ['id_disease' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVaccinesToDiseases()
    {
        return $this->hasMany(VaccinesToDiseases::class, ['id_disease' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVaccines()
    {
        return $this->hasMany(Vaccines::class, ['id' => 'id_vaccine'])->viaTable('vaccines_to_diseases', ['id_disease' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolations()
    {
        return $this->hasMany(Violation::class, ['id_disease' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGostDisease()
    {
        return $this->hasOne(GostDiseases::class, ['id' => 'id_gost_disease']);
    }
}
