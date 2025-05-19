<?php


namespace app\modules\v2\modules\tmc\controllers;

use app\models\db\tmc\TmcBase;
use app\models\db\tmc\Balance;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\BalanceReplenishModel;
use app\modules\v2\modules\tmc\models\BalanceViewModel;
use yii\web\BadRequestHttpException;

class BalanceExpMaterialsController extends BaseController
{
    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $balance = new BalanceViewModel();

        return [
            'result' => $balance->getList(TmcBase::TYPE_EXP_MATERIAL, $page, $limit, $filter)
        ];
    }

    /**
     * Пополнение баланса организации
     *
     * @param $items
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAdd($items)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $balance = new BalanceReplenishModel();
        $balance->append(TmcBase::TYPE_EXP_MATERIAL, $items);

        return [
            'result' => true
        ];
    }

    /**
     * Редактирование баланса единиц ТМЦ для расходных материалов
     *
     * @param int $id
     * @param float $count
     * @return bool
     * @throws BadRequestHttpException
     */
    public function actionEdit(
        int $id,
        float $count
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!$balance = Balance::findOne(['id' => $id, 'type_tmc' => TmcBase::TYPE_EXP_MATERIAL, 'id_specialist' => null])) {
            throw new BadRequestHttpException('Баланс не найден или не соответствует требованиям');
        }
        $count_old = $balance->count;
        $balance->count = $count;

        if ($balance->validate() === false) {
            $errors = $balance->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ?
                'Ошибка валидации' :
                implode("\n", array_values($errors)));
        }

        $balance->save(false);
		
        \Yii::$app->db->createCommand()->insert('tmc.balance_change_history', [
            'id_balance' => $balance->id,
            'id_tmc' => $balance->id_tmc,
            'count_old' => $count_old,
            'count_new' => $balance->count,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => \Yii::$app->user->identity->getId()
        ])->execute();
        
        return [
            'result' => true
        ];
    }
}