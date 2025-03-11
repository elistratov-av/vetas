<?php

namespace app\modules\v2\modules\tmc\models\balanceActionList;

use app\common\components\rbac\Role;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use app\modules\v2\common\balance\WriteOffCalculation;
use app\modules\v2\modules\tmc\dto\BalanceActionListSaveDto;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Передача на баланс
 * Class TransferToBalanceActionListModel
 *
 * @author Aleksandr Roik
 */
class TransferToBalanceSaveModel extends AbstractSaveModel
{
    /**
     * TransferRequestSaveModel constructor.
     *
     * @param BalanceAction $balanceAction
     * @param BalanceActionListSaveDto $itemDto
     */
    public function __construct(BalanceAction $balanceAction, BalanceActionListSaveDto $itemDto)
    {
        $itemDto->writeOffPackForm = false;
        parent::__construct($balanceAction, $itemDto);
    }

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
            throw new BadRequestHttpException('На балансе нет ТМЦ');
        }

        // Балансы есть только у ветспецов (и выездных тоже)
        if (!empty($this->balanceAction->to_id_specialist)) {
            $balance_exist = (new Query())
                ->from('auth_assignment')
                ->where([
                    'AND',
                    ['id_specialist' => $this->balanceAction->to_id_specialist],
                    ['IN', 'item_name', [
                        Role::ROLE_VET_SPECIALIST_GOS_AMB,
                        Role::ROLE_VET_SPECIALIST_GOS
                    ]]
                ])->exists();

            if (!$balance_exist) {
                throw new BadRequestHttpException('Запрашивать могут пользователи с ролью Вет. специалист или Вет. специалист выездной бригады');
            }

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
            throw new BadRequestHttpException('На балансе нет достаточного количества ТМЦ');
        }


        return $this;
    }

    /**
     * Заморозка данных баланса
     *
     * @param BalanceActionTmcList $actionList
     */
    public function afterSave(BalanceActionTmcList $actionList)
    {
        parent::afterSave($actionList);

        $this->saveBalanceFlow([
                'flow_type'         => BalanceFlow::FLOW_TYPE_HOLD,
                'flow_action'       => BalanceFlow::FLOW_ACTION_EXPENSE,
                'id_tmc_balance'    => $actionList->id_balance_tmc,
                'id_balance_action' => $actionList->id_action,
                'count'             => $actionList->count,
            ]
        );
    }
}
