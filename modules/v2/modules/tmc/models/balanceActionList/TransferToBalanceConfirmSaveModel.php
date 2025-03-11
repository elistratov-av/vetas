<?php

namespace app\modules\v2\modules\tmc\models\balanceActionList;

use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use yii\web\BadRequestHttpException;

/**
 * Подтверждение перевода ТМЦ запросившим сотрудником
 * Class TransferRequestConfirmModel
 *
 * @author Aleksandr Roik
 */
class TransferToBalanceConfirmSaveModel
{

    /**
     * @var BalanceAction
     */
    protected $balanceAction;

    /**
     * AbstractActionModel constructor.
     *
     * @param BalanceAction $balanceAction
     */
    public function __construct(BalanceAction $balanceAction)
    {
        $this->balanceAction = $balanceAction;
    }

    /**
     * Принимаем перевод
     * @param int|null $id_visit_service Используется при автопередаче в приеме.
     */
    public function confirmTransfer(BalanceActionTmcList $actionList, string $receiving_date = null, int $id_visit_service = null)
    {
        //0. Сравниваем дату получения с датой передачи VETAIS-3417
        if (empty($receiving_date)){
            $receiving_date = date('Y-m-d');
        }
        if (strtotime($receiving_date) < strtotime($actionList->balanceAction->transfer_date)){
            throw new BadRequestHttpException('Дата получения не может быть раньше даты передачи');
        }


        //1. Списываем с баланса передающего
        $this->saveBalanceFlow([
                'flow_type'         => BalanceFlow::FLOW_TYPE_DECREASE,
                'flow_action'       => BalanceFlow::FLOW_ACTION_EXPENSE,
                'id_tmc_balance'    => $actionList->balance->getPrimaryKey(),
                'id_balance_action' => $actionList->id_action,
                'id_visit_service'  => $id_visit_service,
                'count'             => $actionList->count,
            ]
        );

        //2. Ищет у запросившего сотрудника на балансе такую же ТМЦ, как у передающего
        //   Если нет - создаем
        $balance = $this->findBalanceInRequesting($actionList->balance);
        $balance->setAttribute('registration_date', $receiving_date);
        if (!$balance->save()) {
            $errors = $balance->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении баланса' : implode(";", array_values($errors)));
        }

        //3. Переводим ТМЦ (добавляем такое же количество что списали) на баланс запросившего сотрудника
        $this->saveBalanceFlow([
                'flow_type'         => BalanceFlow::FLOW_TYPE_INCREASE,
                'flow_action'       => BalanceFlow::FLOW_ACTION_INCOME_TO_ORG,
                'id_tmc_balance'    => $balance->getPrimaryKey(),
                'id_balance_action' => $actionList->id_action,
                'id_visit_service'  => $id_visit_service,
                'count'             => $actionList->count,
            ]
        );
    }

    /**
     * Ищет у запросившего сотрудника на балансе такую же ТМЦ, как у передающего
     * Если нет - создает и возвращает новую модель баланса
     *
     * @param Balance $balance_item
     * @return array
     */
    protected function findBalanceInRequesting(Balance $balanceTransferring): Balance
    {
        $balance = Balance::find()
            ->where([
                'AND',
                ['id_tmc' => $balanceTransferring->id_tmc],
                ['type_tmc' => $balanceTransferring->type_tmc],

                //организация
                ['id_organization' => $this->balanceAction->to_id_organization],

                //специалист
                ($this->balanceAction->to_id_specialist ? ['id_specialist' => $this->balanceAction->to_id_specialist] : ['IS', 'id_specialist', null]),

                //с этим инвентарным номером
                ['inventory_number' => $balanceTransferring->inventory_number],

                //договорились что на балансе может быть
                //тот же препарат но с разной ценой
                ['price' => $balanceTransferring->price]
            ])
            ->one();

        return $balance ?? new Balance(
                array_merge(
                    $balanceTransferring->getAttributes([
                        'id_tmc',
                        'type_tmc',
                        'id_production_form',
                        'price',
                        'expiration_date',
                        'registration_date',
                        'production_date',
                        'inventory_number',
                        'equipment_condition',
                        'manufactured_number',
                        'old_id',
                    ]), [
                        'id_organization'   => $this->balanceAction->to_id_organization,
                        'id_specialist'     => $this->balanceAction->to_id_specialist,
                       // 'registration_date' => date('Y-m-d'),
                    ]
                )
            );

    }

    /**
     * Списание/постановка на баланс
     *
     * @return $this
     * @throws BadRequestHttpException
     */
    protected function saveBalanceFlow($attributes): self
    {
        if (!$attributes) {
            return $this;
        }

        $flow = new BalanceFlow($attributes);

        if (!$flow->save()) {
            $errors = $flow->getErrorSummary(true);
            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка при сохранении информации о действии над ТМЦ ' : implode(";", array_unique(array_values($errors)))
            );
        }

        return $this;
    }

}
