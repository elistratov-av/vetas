<?php

namespace app\modules\v2\modules\tmc\models\balanceActionList;

use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use app\modules\v2\common\balance\WriteOffCalculation;
use yii\web\BadRequestHttpException;

/**
 * Cписание с баланса
 * Class WriteOffActionListModel
 *
 * @property WriteOffCalculation $calculation
 * @author Aleksandr Roik
 */
class WriteOffSaveModel extends AbstractSaveModel
{
    /**
     * @return string
     */
    protected function getCalcucationClassName(): ?string
    {
        return WriteOffCalculation::class;
    }

    /**
     * Валидация
     *
     * @return $this
     */
    public function validate(): AbstractSaveModel
    {
        //Проверка баланса (выдаст екцепшин, если баланса нет)
        $balance = $this->getBalance();
        if ($balance->id_organization != $this->balanceAction->from_id_organization) {
            throw new BadRequestHttpException('Указанная ТМЦ не найдена на балансе организации у пользователя');
        }

        //Проверка наличия ТМЦ на балансе
        if (bccomp((string)$this->getBalance()->count, '0.00', 2) <= 0) {
            throw new BadRequestHttpException('На балансе нет ТМЦ для списания');
        }

        //Проверка количества
        if (!$this->itemDto->writeOffAll &&
            (
                !is_numeric($this->itemDto->countSelected) ||
                !(bool)preg_match("/^[0-9]{1,7}\.?[0-9]{0,2}$/", $this->itemDto->countSelected) ||
                bccomp((string)$this->itemDto->countSelected, '0.00', 2) <= 0
            )
        ) {
            throw new BadRequestHttpException('Значение "Количество" должно быть числовым (x.xx) и больше нуля');
        }

        $count = $this->calculation->getCount();
        if (bccomp((string)$count, (string)$this->getBalance()->count, 2) === 1) {
            throw new BadRequestHttpException('На балансе нет достаточного количества ТМЦ для списания');
        }

        return $this;
    }

    /**
     * @param BalanceActionTmcList $actionList
     * @throws BadRequestHttpException
     */
    public function afterSave(BalanceActionTmcList $actionList)
    {
        parent::afterSave($actionList);

        $this
            ->saveBalanceFlow($this->getBalanceFlowAttr());
    }

    /**
     * Возвращает аттрибуты сущности для модели BalanceFlow
     *
     * @param BalanceActionTmcList $actionList
     * @return array
     */
    protected function getBalanceFlowAttr(): array
    {
        return [
            'flow_type'         => BalanceFlow::FLOW_TYPE_DECREASE,
            'flow_action'       => BalanceFlow::FLOW_ACTION_EXPENSE,
            'id_tmc_balance'    => $this->itemDto->idBalanceTmc,
            'id_balance_action' => $this->balanceAction->getPrimaryKey(),
            'count'             => $this->getCount(),
        ];
    }

    /**
     * Количество форм
     *
     * @return float|null
     */
    protected function getCountProductionForm(): ?float
    {
        return $this->calculation ? $this->calculation->getCountProductionForm() : null;
    }
}
