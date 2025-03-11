<?php

namespace app\modules\soap\models;

/**
 * This is the model class for table "breeds".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $species_id
 *
 * @property Pets[] $pets
 */
class Breeds extends \app\models\db\Breeds
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id_breed' => 'id']);
    }
}
