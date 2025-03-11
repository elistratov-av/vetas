<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.04.19
 * Time: 16:11
 */

namespace app\modules\admin\models;

class BalanceFlow extends \app\models\db\BalanceFlow
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalanceDrug()
    {
        return $this->hasOne(BalanceDrugs::class, ['id' => 'id_balance_tmc_type'])
            ->andWhere(['=', 'balance_tmc_type', 'drug']);
    }


}