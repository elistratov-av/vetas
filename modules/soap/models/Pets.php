<?php

namespace app\modules\soap\models;

/**
 * @property Breeds $breed
 * @property Species $species
 * @property Visits[] $visits
 */
class Pets extends \app\models\db\Pets
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreed()
    {
        return $this->hasOne(Breeds::class, ['id' => 'id_breed']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasOne(Species::class, ['id' => 'id_species']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this->hasMany(Visits::class, ['id_pet' => 'id']);
    }

}
