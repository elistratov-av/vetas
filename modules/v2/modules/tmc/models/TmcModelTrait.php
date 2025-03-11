<?php

namespace app\modules\v2\modules\tmc\models;

use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use yii\web\BadRequestHttpException;

/**
 * Трейт для испольщования в моделях ТМЦ
 * Trait TmcModelTrait
 *
 * @package app\modules\v2\modules\tmc\models
 * @author Aleksandr Roik
 */
trait TmcModelTrait
{

    /**
     * Проверка наличия остатка на балансе. Если есть - выброс екцепшина
     *
     * @param Balance[] $balances
     */
    protected function validateWhereIsBalance(TmcBase $tmc)
    {
        $balances = $tmc
            ->getBalances()
            ->andWhere(['>', 'count', 0])
            ->orderBy('id_organization, id_specialist')
            ->all();

        if (!$balances) {
            return;
        }

        $messages = [
            'text' => 'Удаление невозможно, имеется остаток на балансе:',
            'data' => array_map(function (Balance $balance) {
                return [
                    'organization' => $balance->organization->short_name,
                    'specialist'   => $balance->specialist ? $balance->specialist->getFullnameInitials() : null,
                    'count'        => $balance->count,
                    'measure'      => $balance->tmc->measure->name
                ];
            }, $balances)
        ];

        throw new BadRequestHttpException(json_encode($messages, JSON_UNESCAPED_UNICODE), 1);
    }

}
