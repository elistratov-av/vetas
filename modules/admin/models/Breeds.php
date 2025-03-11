<?php

namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * Class Breeds
 * @package app\modules\admin\models
 */
class Breeds extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'breeds';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasOne(Species::class, ['id' => 'species_id']);
    }
}
