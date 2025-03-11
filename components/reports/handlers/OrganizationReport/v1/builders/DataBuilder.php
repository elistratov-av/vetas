<?php

namespace app\common\components\reports\handlers\OrganizationReport\v1\builders;

use app\common\components\reports\handlers\OrganizationReport\v1\DataProvider;
use app\common\components\reports\handlers\OrganizationReport\v1\dto\MovementDto;
use app\common\components\reports\handlers\OrganizationReport\v1\dto\SpentDto;
use app\common\components\reports\handlers\OrganizationReport\v1\SingleDataProvider;
use app\common\components\reports\handlers\OrganizationReport\v1\dto\ReportDto;
use app\common\components\reports\handlers\OrganizationReport\v1\dto\TmcDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 *
 * @property ReportDto $reportDto
 * @property SingleDataProvider|DataProvider $provider
 * @package app\common\components\reports\handlers\OrganizationReport\v1\builders
 * @author Aleksandr Roik
 */
class DataBuilder extends AbctractDataBuilder
{
    /**
     * Запуск построения
     *
     * @return self
     */
    public function build(string $reportDtoClass)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $provider = $this->provider;
            $this->reportDto = new $reportDtoClass([
                    'organization_name' => $provider->getOrganizationName(),
                    'month'             => $provider->getMonth(),
                    'year'              => $provider->getYear(),
                ]
            );
            $this->buildTmc($provider->getOrganizationFlows());
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }

    /**
     * // Строит движение ТМЦ в отчете по организации
     * Решили группироваться по названию + номер + срок годности.
     *
     * @param BalanceFlow[] $balance_flows
     */
    private function buildTmc(array $balance_flows)
    {
        @ini_set('memory_limit', '512M');
        /** @var TmcDto[][] $specialist_dto */
        $specialist_dto = [];
        /** @var TmcDto[] $technic_dto */
        $technic_dto = [];
        foreach ($balance_flows as $flow) {
            $id_balance = $flow->balance->id_tmc;
            $name_tmc = $flow->balance->tmc->name . ' (' . $flow->balance->inventory_number . ') ' . $flow->balance->expiration_date;
            $id_specialist = $flow->balance->id_specialist;
            $tmc = [];
            if (isset($id_specialist) && !empty($specialist_dto[$name_tmc][$id_specialist])) {
                $tmc = $specialist_dto[$name_tmc][$id_specialist];
            } else {
                if (empty($id_specialist) && !empty($technic_dto[$name_tmc])) {
                    $tmc = $technic_dto[$name_tmc];
                } else {
                    $tmc = new TmcDto([
                        'name'               => $name_tmc,
                        'expiration_date'    => $flow->balance->expiration_date,
                        'specialist_name'    => $flow->balance->specialist->fullname ?? 'Склад',
                        'measure'            => $flow->balance->tmc->measure->name ?? '',
                        'transfer'           => new MovementDto(['price' => $flow->balance->price]),
                        'income'             => new MovementDto(['price' => $flow->balance->price]),
                        'decrease'           => new MovementDto(['price' => $flow->balance->price]),
                        'remains_start_date' => $this->provider->getStartDateRemains($flow->balance->id),
                        'remains_end_date'   => $this->provider->getEndDateRemains($flow->balance->id)
                    ]);
                }
            }
            //Приход
            if ($flow->flow_type == BalanceFlow::FLOW_TYPE_INCREASE
                //Если приход спецу - будет balance action. Иначе приход организации
                && (
                    ($flow->balanceAction->status ?? null) == BalanceAction::STATUS_COMPLETED || empty($id_specialist)
                )
            ) {
                if ($this->notToHimself($flow)){
                    $tmc->income->count += $flow->count;
                    $tmc->income->sum += (float)$flow->balance->price * $flow->count;
                }
            } //Расход
            else {
                if ($flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE
                    && ((isset($flow->id_visit_service) && empty($flow->id_balance_action)) // При автопередаче с баланса организации в приеме есть оба поля, в расход не включаем
                        || ($flow->balanceAction->action == BalanceAction::ACTION_WRITE_OFF && $flow->balanceAction->status == BalanceAction::STATUS_COMPLETED))) {
                    $tmc->decrease->count += $flow->count;
                    $tmc->decrease->sum += (float)$flow->balance->price * $flow->count;
                } //Выдача/Передача
                else {
                    if ($flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE
                        && $flow->balanceAction->action == BalanceAction::ACTION_TRANSFER_TO_BALANCE
                        && $flow->balanceAction->status == BalanceAction::STATUS_COMPLETED
                    ) {
                        if ($this->notToHimself($flow)){
                            $tmc->transfer->count += $flow->count;
                            $tmc->transfer->sum += (float)$flow->balance->price * $flow->count;
                        }
                    }
                }
            }

            if (empty($id_specialist)) {
                $technic_dto[$name_tmc] = $tmc;
            } else {
                $specialist_dto[$name_tmc][$id_specialist] = $tmc;
            }
        }
        //Если не было движения у склада/специалиста по балансу, но есть остатки
        $balances = $this->provider->getOrganizationBalances();
        foreach ($balances as $balance) {
            $name_tmc = $balance->tmc->name . ' (' . $balance->inventory_number . ') ' . $balance->expiration_date;
            if (
                (empty($balance->id_specialist) && !array_key_exists($name_tmc, $technic_dto) && $balance->count > 0)
                // Если на складе изначально ничего не было
                // <= 0 из-за старой реализации с отрицательными балансами.
                || (
                    empty($balance->id_specialist)
                    && empty($specialist_dto[$name_tmc][$balance->id_specialist])
                    && !array_key_exists($name_tmc, $technic_dto)
                )
            ) {
                $remains_start_date = $this->provider->getStartDateRemains($balance->id);
                $remains_end_date   = $this->provider->getEndDateRemains($balance->id);
                if ($remains_start_date->count == 0 && $remains_end_date->count == 0){
                    continue;
                }
                $technic_dto[$name_tmc] = new TmcDto([
                    'name'               => $name_tmc,
                    'expiration_date'    => $balance->expiration_date,
                    'specialist_name'    => 'Склад',
                    'measure'            => $balance->tmc->measure->name ?? '',
                    'transfer'           => new MovementDto(['price' => $balance->price]),
                    'income'             => new MovementDto(['price' => $balance->price]),
                    'decrease'           => new MovementDto(['price' => $balance->price]),
                    'remains_start_date' => $remains_start_date,
                    'remains_end_date'   => $remains_end_date
                ]);

            } else {
                if (isset($balance->id_specialist) && empty($specialist_dto[$name_tmc][$balance->id_specialist])) {
                    $remains_start_date = $this->provider->getStartDateRemains($balance->id);
                    $remains_end_date   = $this->provider->getEndDateRemains($balance->id);
                    if ($remains_start_date->count == 0 && $remains_end_date->count == 0){
                        continue;
                    }
                    $specialist_dto[$name_tmc][$balance->id_specialist] = new TmcDto([
                        'name'               => $name_tmc,
                        'expiration_date'    => $balance->expiration_date,
                        'specialist_name'    => $balance->specialist->fullname,
                        'measure'            => $balance->tmc->measure->name ?? '',
                        'transfer'           => new MovementDto(['price' => $balance->price]),
                        'income'             => new MovementDto(['price' => $balance->price]),
                        'decrease'           => new MovementDto(['price' => $balance->price]),
                        'remains_start_date' => $remains_start_date,
                        'remains_end_date'   => $remains_end_date
                    ]);
                }
            }
        }

        $this->reportDto->specialist_tmc = $specialist_dto;
        $this->reportDto->technic_tmc = $technic_dto;
    }

    /**
     * Проверка на передачу в свой же баланс.
     * @param BalanceFlow $flow
     * @return bool
     */
    private function notToHimself(BalanceFlow $flow){
        if (empty($flow->balanceAction)){
            return true;
        }
        //Передача организации самой себе
        if (empty($flow->balance->id_specialist)){
            if
            (
                ($flow->balanceAction->from_id_organization == $flow->balanceAction->to_id_organization)
                && (empty($flow->balanceAction->from_id_specialist) && empty($flow->balanceAction->to_id_specialist))
            )
            {
                return false;
            }
        }
        //Передача специалиста самому себе
        if (!empty($flow->balance->id_specialist)){
            if ($flow->balanceAction->from_id_specialist == $flow->balanceAction->to_id_specialist){
                return false;
            }
        }
        return true;
    }
}
