<?php

namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\OrderModel;

class OrderController extends BaseController
{
    /**
     * @param integer $id_violation
     * @param string $number
     * @param string $date_to
     * @param string $date_order
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate(int $id_violation, string $number, string $date_order, string $date_to)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OrderModel())->create($id_violation, $number, $date_order, $date_to)
        ];
    }

    /**
     * @param string $number
     * @param integer $id_ARV
     * @param string $date_ARV
     * @return array
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreateArv(string $number, int $id_ARV, string $date_ARV)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OrderModel())->makeARV($number, $id_ARV, $date_ARV)
        ];
    }

    /**
     * @param integer $id_order
     * @param integer[]|null $file_ids
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionNotifyOrder(int $id_order, array $file_ids = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OrderModel())->notifyOrder($id_order, $file_ids)
        ];
    }

    /**
     * @param integer $id_ARV
     * @param string $text
     * @param integer[]|null $file_ids
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionNotifyArv(int $id_ARV, string $text = null, array $file_ids = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new OrderModel())->notifyArv($id_ARV, $text, $file_ids)
        ];
    }
}
