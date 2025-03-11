<?php


namespace app\modules\v2\modules\tmc\controllers;

use app\models\db\tmc\TmcBase;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\BalanceReplenishModel;
use app\modules\v2\modules\tmc\models\BalanceViewModel;

class BalanceEquipmentsController extends BaseController
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
            'result' => $balance->getList(TmcBase::TYPE_EQUIPMENT, $page, $limit, $filter)
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
        $balance->appendEquipment($items);

        return [
            'result' => true
        ];
    }
}