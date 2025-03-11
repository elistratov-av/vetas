<?php

namespace app\modules\v2\modules\tmc\models;

use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceFlow;
use app\modules\v2\modules\tmc\dto\BalanceActionSaveDto;
use app\modules\v2\modules\tmc\dto\BalanceActionListSaveDto;
use app\modules\v2\modules\tmc\models\balanceActionList\AbstractSaveModel;
use app\modules\v2\modules\tmc\models\balanceActionList\TransferToBalanceConfirmSaveModel;
use app\modules\v2\modules\tmc\models\balanceActionList\TransferRequestSaveModel;
use app\modules\v2\modules\tmc\models\balanceActionList\TransferToBalanceSaveModel;
use app\modules\v2\modules\tmc\models\balanceActionList\WriteOffSaveModel;
use yii\base\Exception;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\User;

/**
 * Модель действий по работе с балансом (передача на баланс, утилизация, списание, запрос на выдачу)
 * Class BalanceActionSaveModel
 *
 * @package app\modules\v2\modules\tmc\models
 * @author Aleksandr Roik
 */
class BalanceActionSaveModel
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @var BalanceAction
     */
    private $balanceActionInstance = [];

    /**
     * BalanceActionSaveModel constructor.
     *
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function __construct()
    {
        $this->user = \Yii::$app->user->getIdentity();
        if ($this->user->specialist->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }
    }

    /**
     * Передать ТМЦ запросившему сотруднику.
     * ТМЦ только ставятся в резерв. Дальше запросившему сотруднику надо его принять или отклонить
     * Сценарий:
     * 1. Закрываем заявку: action = STATUS_COMPLETED
     * 2. Создаем новое действие: прием-передачу
     *
     * @param int $actionId
     * @param array $items
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function transferToRequester(int $actionId, array $items = [])
    {
        $balanceAction = BalanceAction::findOne($actionId);
        if (!$balanceAction) {
            throw new BadRequestHttpException("Значение «id действия баланса» неверно.");
        }

        BalanceActionAccessRules::checkTransferToRequester($balanceAction);

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            //1. Закрываем заявку, то есть создаем из нее "прием-передачу"
            $balanceAction
                ->setAttributes([
                    'action' => BalanceAction::ACTION_TRANSFER_TO_BALANCE,
                    'status' => BalanceAction::STATUS_WAITING_CONFIRMATION,
                ])
                ->save();

            //2. Проверяем занныя ТМЦ. Если $items пустой, значит берем те даные, что были в заявке. Если не пустой, значит
            //   есть вероятность, что ТМЦ, те что в запросе, возможно были изменены передающим. Потому - удаляем старые (что в заявке), и пишем новые
            if ($items) {
                foreach ($balanceAction->actionTmcList as $actionTmcList) {
                    $actionTmcList->delete();
                }
            } else {
                $rowId = 1;
                $items = array_map(function ($actionTmcList) use (&$rowId) {
                    return array_merge(['row_id' => $rowId++], $actionTmcList->getAttributes());
                }, $balanceAction->actionTmcList);
            }

            //3. И заполняем действие новыми ТМЦ завками (на случай, если они изменились)
            $this->transferToBalance(
                [
                    'to_id_organization' => $balanceAction->to_id_organization,
                    'to_id_specialist'   => $balanceAction->to_id_specialist,
                ],
                $items,
                $balanceAction
            );
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage());
        }
        $transaction->commit();
    }

    /**
     * Подтверждение перевода ТМЦ запросившим сотрудником
     * После подтверждения зарезервированные ТМЦ переводятся на баланс
     *
     * @param int $actionId
     * @param int|null $id_visit_service Используется при автопередаче в приеме.
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function confirmTransfer(int $actionId, string $receiving_date, int $id_visit_service = null)
    {
        $balanceAction = BalanceAction::findOne($actionId);
        if (!$balanceAction) {
            throw new BadRequestHttpException("Значение «id действия баланса» неверно.");
        }

        BalanceActionAccessRules::checkConfirmTransfer($balanceAction);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //1. Меняем на статус "Завершено"
            $balanceAction
                ->setAttributes([
                    'status'                 => BalanceAction::STATUS_COMPLETED,
                    'acceptor_date'          => new Expression('now()'),
                    'acceptor_id_specialist' => $this->user->specialist->id,
                    'receiving_date'         => $receiving_date ?? date('Y-m-d'),
                ])
                ->save();

            //2. Удаляем резерв
            BalanceFlow::deleteAll(['id_balance_action' => $balanceAction->getPrimaryKey()]);

            //3. Переводим ТМЦ на баланс запросившего
            try {
                foreach ($balanceAction->actionTmcList as $actionList) {
                    $rowId = $actionList->id;

                    (new TransferToBalanceConfirmSaveModel($balanceAction))
                        ->confirmTransfer($actionList, $receiving_date, $id_visit_service);
                }
            } catch (\Throwable $e) {
                throw new BadRequestHttpException('Action list ID - ' . $rowId . '. ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage());
        }
        $transaction->commit();
    }

    /**
     * Отмена перевода ТМЦ запросившим собтрудником
     * После омены зарезервированные ТМЦ отправляются обратно на баланс передающего собтрудника
     *
     * @param integer $actionId
     */
    public function cancelTransfer($actionId)
    {
        $balanceAction = BalanceAction::findOne($actionId);
        if (!$balanceAction) {
            throw new BadRequestHttpException("Значение «id действия баланса» неверно.");
        }

        BalanceActionAccessRules::checkCancelTransfer($balanceAction);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //1. Меняем на статус "Отклюнено"
            $balanceAction
                ->setAttributes([
                    'status'                 => BalanceAction::STATUS_REJECTED,
                    'acceptor_date'          => new Expression('now()'),
                    'acceptor_id_specialist' => $this->user->specialist->id,
                ])
                ->save();

            //2. Удаляем резерв
            BalanceFlow::deleteAll(['id_balance_action' => $balanceAction->getPrimaryKey()]);

        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException($e->getMessage());
        }
        $transaction->commit();
    }

    /**
     * Передача ТМЦ на баланс другому сотруднику (организации)
     *!!! Изменение этого метода могут повлиять на метод transferWithinVisit
     * @param array $action
     * @param array $items
     * @param BalanceAction|null $balanceAction
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function transferToBalance(array $action, array $items, BalanceAction $balanceAction = null, $transfer_date = null)
    {
        $this->saveItems(
            TransferToBalanceSaveModel::class,
            $items,
            function ($item) use ($action, $balanceAction, $transfer_date) {
                if ($balanceAction) {
                    return $balanceAction;
                }

                $balance = $this->getBalance($item['id_balance_tmc']);
                $actionDto = new BalanceActionSaveDto(
                    [
                        'action'             => BalanceAction::ACTION_TRANSFER_TO_BALANCE,
                        'status'             => BalanceAction::STATUS_WAITING_CONFIRMATION,
                        'fromIdOrganization' => $balance->id_organization,
                        'fromIdSpecialist'   => $balance->id_specialist,
                        'toIdOrganization'   => $action['to_id_organization'],
                        'toIdSpecialist'     => $action['to_id_specialist'],
                        'transferDate'       => $transfer_date ?? date('Y-m-d'),
                    ]
                );

                return $this->saveOnceBalanceAction($actionDto);
            }
        );
    }

    /**
     * Запрос на ТМЦ
     *
     * @param array $items
     * @return void
     */
    public function transferRequest(array $items)
    {
        $this->saveItems(
            TransferRequestSaveModel::class,
            $items,
            function ($item) {
                $balance = $this->getBalance($item['id_balance_tmc']);
                $actionDto = new BalanceActionSaveDto(
                    [
                        'action'             => BalanceAction::ACTION_TRANSFER_REQUEST,
                        'status'             => BalanceAction::STATUS_WAITING_EXECUTION,
                        'fromIdOrganization' => $balance->id_organization,
                        'fromIdSpecialist'   => $balance->id_specialist,
                        'toIdOrganization'   => $this->user->specialist->id_organization,
                        'toIdSpecialist'     => $this->user->specialist->id,
                    ]
                );

                return $this->saveOnceBalanceAction($actionDto);
            }
        );
    }

    /**
     * Cписание с баланса
     *
     * @param array $action
     * @return void
     */
    public function writeOf(array $action, array $items)
    {
        $this->saveItems(
            WriteOffSaveModel::class,
            $items,
            function ($item) use ($action) {
                $balance = $this->getBalance($item['id_balance_tmc']);
                $actionDto = new BalanceActionSaveDto(array_merge(
                        $action,
                        [
                            'action'               => BalanceAction::ACTION_WRITE_OFF,
                            'status'               => BalanceAction::STATUS_COMPLETED,
                            'fromIdOrganization'   => $balance->id_organization,
                            'fromIdSpecialist'     => $balance->id_specialist,
                            'acceptorDate'         => new Expression('now()'),
                            'acceptorIdSpecialist' => $this->user->specialist->id,
                        ]
                    )
                );

                return $this->saveOnceBalanceAction($actionDto);
            }
        );
    }

    /**
     * Делает передачу с баланса организации на баланс спеца и подтверждает ее.
     * Используется при использовании ТМЦ организиации в приеме.
     * @see https://jira.altarix.ru/browse/VETAIS-3418
     * @param array $action
     * @param array $items
     * @param int $id_visit_service
     * @return BalanceAction
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function transferWithinVisit(array $action, array $items, int $id_visit_service){
        $this->transferToBalance($action, $items);

        // Делаем передачу по одному ТМЦ
        if (count($this->balanceActionInstance) > 1){
            throw new Exception('Передача внутри приема возможна только по одному ТМЦ');
        }


        /** @var BalanceAction $balance_action */
        $balance_action = $this->balanceActionInstance[array_key_first($this->balanceActionInstance)];

        $this->confirmTransfer($balance_action->id, date('Y-m-d'), $id_visit_service);

        return $balance_action;
    }

    /**
     * Обработка действий запросса
     *
     * @param string $actionListSaveModel
     * @param array $items
     * @param callable $actionDtoCallback
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    private function saveItems(string $actionListSaveModel, array $items, callable $saveBalanceActionFunction)
    {
        $this->checkItems($items);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($items as $item) {
                //1. Сохраняем информацию о действии над ТМЦ
                $balanceAction = $saveBalanceActionFunction($item);

                //2. Обработка сущности определенного действия
                $balance = $this->getBalance($item['id_balance_tmc']);

                /* @var AbstractSaveModel $actionListSaveModel */
                (new $actionListSaveModel(
                    $balanceAction,
                    (new BalanceActionListSaveDto($item))
                        ->mergeData([
                            'idOrganization' => $balance->id_organization,
                            'idSpecialist'   => $balance->id_specialist,
                        ])
                ))->execute();
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new BadRequestHttpException('ID - ' . $item['row_id'] . '. ' . $e->getMessage());
        }
        $transaction->commit();
    }

    /**
     * Сохраняем информацию о действии над ТМЦ
     *
     * @param BalanceActionSaveDto $actionDto
     * @param array $items
     * @param string $actionModelHandler
     * @return BalanceAction
     * @throws BadRequestHttpException
     */
    protected function saveOnceBalanceAction(BalanceActionSaveDto $actionDto): BalanceAction
    {
        // Добавляем аттрибуты по умолчанию
        $actionDto
            ->mergeData(
                [
                    'num'                   => (string)(BalanceAction::find()->max('num') + 1),
                    'initiatorDate'         => new Expression('now()'),
                    'initiatorIdSpecialist' => $this->user->specialist->id,
                ]
            );

        // Создаем обьект действия (синглтон)
        $balanceAction = $this->getBalanceActionInstance(
            $actionDto->fromIdOrganization,
            $actionDto->fromIdSpecialist
        );

        // Пропускаем, есть он уже был сохранен
        if (!$balanceAction->getIsNewRecord()) {
            return $balanceAction;
        }
        $balanceAction->setAttributes($actionDto->toAttributes());

        // transfer_to_balance - /v2/tmc/balance-actions/create-transfer-to-balance
        // write_off             /v2/tmc/balance-actions/write-off
        // transfer_request      /v2/tmc/balance-actions/create-transfer-request

        switch ($actionDto->action){
            case BalanceAction::ACTION_WRITE_OFF:
                BalanceActionAccessRules::checkWriteOff($balanceAction);
                break;
            case BalanceAction::ACTION_TRANSFER_TO_BALANCE:
                BalanceActionAccessRules::checkCreateTransferToBalance($balanceAction);
                break;
            case BalanceAction::ACTION_TRANSFER_REQUEST:
                BalanceActionAccessRules::checkCreateTransferRequest($balanceAction);
                break;
            case BalanceAction::ACTION_RECYCLING:
        }

        if (!$balanceAction->save()) {
            $errors = $balanceAction->getErrorSummary(true);
            throw new BadRequestHttpException(
                empty($errors) ? 'Ошибка при сохранении информации о действии над ТМЦ ' : implode(";", array_unique(array_values($errors)))
            );
        }

        return $balanceAction;
    }

    /**
     * Создаем/возвращаем обьект действия (синглтон)
     *
     * @param int $fromIdOrganization
     * @param int $fromIdSpecialist
     * @return BalanceAction
     */
    protected function getBalanceActionInstance(?int $fromIdOrganization, ?int $fromIdSpecialist): BalanceAction
    {
        $key = $fromIdOrganization . '_' . $fromIdSpecialist;
        if (!array_key_exists($key, $this->balanceActionInstance)) {
            $this->balanceActionInstance[$key] = new BalanceAction();
        }

        return $this->balanceActionInstance[$key];
    }

    /**
     * Возвращает объект баланса (с кеширование)
     *
     * @return Balance
     */
    protected function getBalance(int $idBalanceTmc): Balance
    {
        if ($this->balance === null) {
            $this->balance = Balance::findOne($idBalanceTmc);

            if (!$this->balance) {
                throw new BadRequestHttpException("Значение «id баланса» неверно.");
            }
        }

        return $this->balance;
    }

    /**
     * Проверяем основной массив
     *
     * @param $array
     * @return void
     * @throws BadRequestHttpException
     */
    protected function checkItems($array)
    {
        if (!is_array($array)) {
            throw new BadRequestHttpException('Параметр items должен быть массивом');
        }

        $rowIds = [];
        foreach ($array as $row) {
            if (!is_array($row)) {
                throw new BadRequestHttpException('Параметр items должен быть массивом массивов');
            }

            if (!array_key_exists('row_id', $row)) {
                throw new BadRequestHttpException('Некоторые строки не содержат параметра row_id');
            }

            if (!is_numeric($row['row_id'])) {
                throw new BadRequestHttpException('Параметр row_id должен быть числом');
            }

            if (in_array($row['row_id'], $rowIds)) {
                throw new BadRequestHttpException('Массив items содержит элементы с повторяющимися row_id');
            }

            $rowIds[] = $row['row_id'];
        }
    }

}
