<?php


namespace app\models\db\tmc;

use app\models\db\Diseases;
use app\models\db\Measures;
use app\models\db\PetDehelmintization;
use app\models\db\PetEctoparasites;
use app\models\db\Species;

/**
 * Class TmcDrug
 * @package app\models\db\tmc
 *
 * ------------
 * ЭТИ ПОЛЯ УНАСЛЕДОВАНЫ ОТ ОСНОВНОЙ ЗАПИСИ,
 * НО НЕ ИСПОЛЬЗУЮТСЯ:
 * @property-read  string $description Описание
 * @property-read boolean $is_uncountable Неисчислимый расходный материал. Не списывается в приеме
 * ------------
 *
 * @property PetDehelmintization[] $petDehelmintizations
 * @property PetEctoparasites[] $petEctoparasites
 * @property Diseases[] $diseases
 * @property Species[] $species
 */
class TmcDrug extends TmcBase
{
    public function fields()
    {
        return [
            'id',
            'type',
            'name',
            'basis',
            'dealer',
            //'description',
            'excipients',
            'form_description',
            'id_measure',
            'unit',
            'packaging',
            'produced',
            'registered',
            'is_deleted',
            //'is_uncountable',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
        ];
    }

    public static function find()
    {
        return parent::find()->andWhere([
            'type' => self::TYPE_DRUG,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetDehelmintizations()
    {
        return $this->hasMany(PetDehelmintization::class, ['id_drug' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetEctoparasites()
    {
        return $this->hasMany(PetEctoparasites::class, ['id_drug' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiseases()
    {
        return $this->hasMany(Diseases::class, ['id' => 'id_disease'])
            ->viaTable('tmc.tmc_to_diseases', ['id_tmc' => 'id', 'type_tmc' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasMany(Species::class, ['id' => 'id_species'])
            ->viaTable('tmc.tmc_to_species', ['id_tmc' => 'id', 'type_tmc' => 'type']);
    }

}

