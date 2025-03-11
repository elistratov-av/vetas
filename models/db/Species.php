<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "species".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 * @property bool $flag_mos_ru Флаг: отображать для записи на mos.ru
 * @property string $tech_name
 *
 * @property Pets[] $pets
 * @property SpeciesDiseases[] $speciesDiseases
 * @property SpeciesServices[] $speciesServices
 * @property VaccinesToSpecies[] $vaccinesToSpecies
 * @property Vaccines[] $vaccines
 */
class Species extends ActiveRecord
{
    const TECH_NAME_CAT = 'CAT';
    const TECH_NAME_DOG = 'DOG';
    const TECH_NAME_HORSE = 'HORSE';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'species';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['flag_mos_ru'], 'boolean'],
            [['name', 'description'], 'string', 'max' => 255],
            [['tech_name'], 'string', 'max' => 50],
            [['tech_name'], 'unique'],
            [['name'], 'unique'],
            [['name', 'description'], FullTrimValidator::class],
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
            'description' => 'Description',
            'created_by' => 'Автор добавления (id пользователя)',
            'updated_by' => 'Автор последнего изменения (id пользователя)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'flag_mos_ru' => 'Флаг: отображать для записи на mos.ru',
            'tech_name' => 'Tech Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id_species' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpeciesDiseases()
    {
        return $this->hasMany(SpeciesDiseases::class, ['id_species' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpeciesServices()
    {
        return $this->hasMany(SpeciesServices::class, ['id_species' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVaccinesToSpecies()
    {
        return $this->hasMany(VaccinesToSpecies::class, ['id_species' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVaccines()
    {
        return $this->hasMany(Vaccines::class, ['id' => 'id_vaccine'])->viaTable('vaccines_to_species', ['id_species' => 'id']);
    }
}
