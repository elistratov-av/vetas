<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.06.19
 * Time: 13:45
 */

namespace app\modules\admin\models;


use app\models\db\Specialists;

class AmbulanceReport extends \app\models\db\statistic\AmbulanceReport
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'id_crew']);
    }

}