<?php

namespace app\modules\admin\models;

use yii\db\ActiveRecord;

/**
 * Class Specializations
 * @package app\modules\admin\models
 */
class Specializations extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'specializations';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonalSpecializations()
    {
        return $this->hasMany(PersonalSpecializations::class, ['id_specialization' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this
            ->hasMany(Specialist::class, ['id' => 'id_specialization'])
            ->viaTable('personal_specializations', ['id_specialist' => 'id']);
    }

}