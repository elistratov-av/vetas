<?php


namespace app\models\db\tmc;

use app\models\db\Diseases;
use app\models\db\Measures;
use app\models\db\Pets;
use app\models\db\Species;

/**
 * Class TmcVaccine
 *
 * @package app\models\db\tmc
 *
 * ------------
 * ЭТИ ПОЛЯ УНАСЛЕДОВАНЫ ОТ ОСНОВНОЙ ЗАПИСИ,
 * НО НЕ ИСПОЛЬЗУЮТСЯ:
 * @property-read string  $basis          Основание препарата
 * @property-read  string $description    Описание
 * @property-read boolean $is_uncountable Неисчислимый расходный материал. Не списывается в приеме
 * ------------
 *
 * @property Measures     $measure
 * @property Dosages      $dosages
 * @property Diseases[]   $diseases
 * @property Species[]    $species
 */
class TmcVaccine extends TmcBase
{

    public function fields()
    {
        return [
            'id',
            'type',
            'name',
            //'basis',
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
            'type' => self::TYPE_VACCINE,
        ]);
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

    /**
     * Возвращает статус, что вакцина может быть от бешенства
     */
    public function isDiseasesRabies(): bool
    {
        return (bool)$this->getDiseases()
            ->where(['id' => 4])
            ->count();
    }

    public function searchDefaultDosage(int $speciesId, int $sizeId, $backHard = false): ?Dosages
    {
        if ($sizeId === Pets::SIZE_BIG) {
            $size = Dosages::PET_SIZE_LARGE;
        } else {
            $size = Dosages::PET_SIZE_SMALL;
        }

        /** @var Dosages $dosage */
        $dosage = $this->getDosages()
            ->where(['id_species' => $speciesId, 'pet_size' => $size])
            ->orderBy('is_default DESC')
            ->one();

        if (!$dosage && $backHard) {
            if ($speciesId === 25 && $sizeId === Pets::SIZE_BIG) {
                $dosage = $this->getDosages()->where(['dosage' => 2])->one();//, 'id_measure' => 11
            } else {
                $dosage = $this->getDosages()->where(['dosage' => 1])->one();//, 'id_measure' => 11
            }
        }

        return $dosage ?? null;
    }
}
