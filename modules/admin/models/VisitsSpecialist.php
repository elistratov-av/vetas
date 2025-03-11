<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.12.18
 * Time: 14:08
 */

namespace app\modules\admin\models;

use app\models\db\VisitsSpecialists;

/**
 * Class VisitsSpecialist
 * @package app\modules\admin\models
 */
class VisitsSpecialist extends VisitsSpecialists
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'visits_specialists';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialist::class, ['id' => 'id_specialist']);
    }
}