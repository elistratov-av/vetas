<?php

namespace app\modules\v2\modules\tmc\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\BalanceActionSaveModel;
use app\modules\v2\modules\tmc\models\BalanceActionViewModel;

/**
 * Балансовые операции
 * Class BalanceActionsController
 *
 * @package app\modules\v2\modules\tmc\controllers
 * @author Aleksandr Roik
 */
class BalanceActionsController extends BaseController
{

    /**
     * Возвращает список действий с балансом ТМЦ (для журнала операций)
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $balance = new BalanceActionViewModel();

        return [
            'result' => $balance->getList($page, $limit, $filter)
        ];
    }

    /**
     * Списание ТМЦ з баланса
     *
     * @param array $action
     * @param array $items
     * @return bool[]
     */
    public function actionWriteOff(array $items, array $action = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new BalanceActionSaveModel())
            ->writeOf($action, $items);

        return [
            'result' => true
        ];
    }

    /**
     * Заявка на ТМЦ
     *
     * @param array $items
     * @return bool[]
     */
    public function actionCreateTransferRequest(array $items)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new BalanceActionSaveModel())
            ->transferRequest($items);

        return [
            'result' => true
        ];
    }

    /**
     * Передача ТМЦ другому сотруднику/организации
     *
     * @param array $action
     * @param array $items
     * @return bool[]
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreateTransferToBalance(array $action, array $items, $transfer_date = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (empty($transfer_date)){
            $transfer_date = date('Y-m-d');
        }

        (new BalanceActionSaveModel())
            ->transferToBalance($action, $items, null, $transfer_date);

        return [
            'result' => true
        ];
    }

    /**
     * Подтверждение передачи ТМЦ передающим
     *
     * @param int $action_id
     * @param array $items
     * @return bool[]
     * @throws \yii\web\BadRequestHttpException
     */

    public function actionTransferToRequester(int $action_id, array $items = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new BalanceActionSaveModel())
            ->transferToRequester($action_id, $items);

        return [
            'result' => true
        ];
    }

    /**
     * Подтверждение передачи ТМЦ запросившим сотрудником
     *
     * @param int $action_id
     * @return bool[]
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionConfirmTransfer(int $action_id, string $receiving_date = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (empty($receiving_date)){
            $receiving_date = date('Y-m-d');
        }

        (new BalanceActionSaveModel())
            ->confirmTransfer($action_id, $receiving_date);

        return [
            'result' => true
        ];
    }

    /**
     * Отмена передачи ТМЦ запросившим сотрудником
     *
     * @param int $action_id
     * @return bool[]
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCancelTransfer(int $action_id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new BalanceActionSaveModel())
            ->cancelTransfer($action_id);

        return [
            'result' => true
        ];
    }
}
