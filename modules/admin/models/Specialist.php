<?php

namespace app\modules\admin\models;

use app\models\db\Specialists;

/**
 * Class Specialist
 * @package app\modules\admin\models
 */
class Specialist extends Specialists
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitsSpecialist()
    {
        return $this
            ->hasMany(VisitsSpecialist::class, ['id_specialist' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this
            ->hasMany(Visits::class, ['id' => 'id_visit'])
            ->viaTable('visits_specialists', ['id_specialist' => 'id']);
    }

    public function getOrganization(): \yii\db\ActiveQuery
    {
        return $this
            ->hasOne(Organization::class, ['id' => 'id_organization']);
    }
}
