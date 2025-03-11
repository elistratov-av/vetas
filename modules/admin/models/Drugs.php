<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.04.19
 * Time: 14:32
 */

namespace app\modules\admin\models;


use app\models\db\BalanceFlow;

class Drugs extends \app\models\db\Drugs
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalanceFlow()
    {
        return $this->hasOne(BalanceFlow::class, [
            'id_balance_tmc_type' => 'id',
        ])
            ->andWhere(['=', 'balance_tmc_type', 'drug']);
    }



}