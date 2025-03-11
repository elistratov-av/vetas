<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.06.19
 * Time: 16:17
 */

namespace app\modules\admin\models;


class BalanceDrugs extends \app\models\db\BalanceDrugs
{
    public function getDrug()
    {
        return $this->hasOne(Drugs::class, ['id' => 'id_drug']);
    }
}
