<?php


namespace app\modules\v2\modules\visit\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\models\BillModel;

class BillController extends BaseController
{
    /**
     * Возвращает чек
     *
     * @param $id_visit
     * @param null $id_discount
     * @param null $applied_discounts_balance_tmc
     * @param null $applied_discounts_services
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id_visit, $id_discount = null, $applied_discounts_balance_tmc = null, $applied_discounts_services = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new BillModel(
                $id_visit,
                $id_discount,
                $applied_discounts_balance_tmc,
                $applied_discounts_services
            ))->getBill()
        ];
    }
}
