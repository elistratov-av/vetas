<?php

namespace app\modules\soap\models;

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
 *
 * @property Pets[] $pets
 * @property SpeciesServices[] $speciesServices
 */
class Species extends \app\models\db\Species
{
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
    public function getSpeciesServices()
    {
        return $this->hasMany(SpeciesServices::class, ['id_species' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreeds_list()
    {
        return $this->hasMany(Breeds::class, ['species_id' => 'id']);
    }
}
