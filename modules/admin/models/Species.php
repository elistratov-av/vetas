<?php

namespace app\modules\admin\models;

/**
 * Class Species
 * @package app\modules\admin\models
 */
class Species extends \app\models\db\Species
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'species';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreeds()
    {
        return $this->hasMany(Breeds::class, ['species_id' => 'id']);
    }
}
