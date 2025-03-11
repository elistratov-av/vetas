<?php

namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\builders;

use app\common\components\reports\handlers\MonthlySpendingReport\v1\DataProvider;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\IncomeDto;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\ReportDto;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\TmcDto;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\TransferDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use app\models\db\tmc\TmcBase;
use app\modules\v2\modules\visit\skeletons\visit\Lists;
use phpDocumentor\Reflection\Types\This;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 * * @property ReportDto $reportDto
 *
 * @property DataProvider $provider
 * @package app\common\components\reports\handlers\MonthlySpendingReport\v1\builders
 * @author Aleksandr Roik
 */
class DataBuilder extends AbctractDataBuilder
{

    /**
     * Запуск построения
     *
     * @return $this|AbctractDataBuilder
     */
    public function build(string $reportDtoClass)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $provider = $this->provider;
            $this->reportDto = new $reportDtoClass([
                    'organization_name' => $provider->getFromOrganizationName(),
                    'start_date'        => $provider->getStartDate(),
                    'end_date'          => $provider->getEndDate(),
                    'specialist_name'   => $provider->getFromSpecialistName(),
                ]
            );
            $this->buildTmc($provider->getBalanceFlows());
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }

    /**
     * @param BalanceFlow[] $balance_flows
     */
    private function buildTmc(array $balance_flows)
    {
        @ini_set('memory_limit', '512M');
        /** @var TmcDto[] $proceeded_tmc */
        $proceeded_tmc = [];
        foreach ($balance_flows as $flow) {
            $tmc = $proceeded_tmc[$flow->balance->id] ?? new TmcDto([
                    'name'               => $flow->balance->tmc->name . ' (' . $flow->balance->inventory_number . ') ' . $flow->balance->expiration_date,
                    'type'               => $this->getType($flow->balance->tmc),
                    'measure'            => $flow->balance->tmc->measure->name ?? '',
                    'expiration_date'    => $flow->balance->expiration_date,
                    'inventory_number'   => $flow->balance->inventory_number,
                    'price'              => $flow->balance->price,
                    'remains_start_date' => $this->provider->getStartDateRemains($flow->balance->id),
                    'remains_end_date'   => $this->provider->getEndDateRemains($flow->balance->id),
                ]);
            //Приход
            if ($flow->flow_type == BalanceFlow::FLOW_TYPE_INCREASE) {
                if ($this->notToHimself($flow)) {
                    $tmc->income[] = new IncomeDto([
                        'income_count' => $flow->count,
                        'income_specialist' => $flow->balanceAction->fromSpecialist->fullname
                            ?? $flow->balanceAction->initiatorSpecialist->fullname
                            ?? ''
                    ]);
                    $tmc->income_sum += $flow->count * $flow->balance->price;
                }
            } //Расход
            else {
                if (
                    $flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE
                    && (!empty($flow->id_visit_service) || $flow->balanceAction->action == BalanceAction::ACTION_WRITE_OFF)
                ) {
                    $tmc->decrease += $flow->count;
                    $tmc->decrease_sum += $flow->count * $flow->balance->price;
                } //Передача
                else {
                    if (
                        $flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE
                        && $flow->balanceAction->action == BalanceAction::ACTION_TRANSFER_TO_BALANCE
                    ) {
                        if ($this->notToHimself($flow)) {
                            $tmc->transfer[] = new TransferDto([
                                'transfer_count' => $flow->count,
                                'transfer_specialist' => $flow->balanceAction->toSpecialist->fullname ?? ''
                            ]);
                        }
                    }
                }
            }
            $proceeded_tmc[$flow->balance->id] = $tmc;
        }
        //Если не было движения, но есть остатки
        $balances = $this->provider->getBalances();
        foreach ($balances as $balance) {
            if (!array_key_exists($balance->id, $proceeded_tmc)) {
                $remains_start_date = $this->provider->getStartDateRemains($balance->id);
                $remains_end_date   = $this->provider->getEndDateRemains($balance->id);
                if ($remains_start_date == 0 && $remains_end_date == 0){
                    continue;
                }
                $proceeded_tmc[$balance->id] = new TmcDto([
                    'name'               => $balance->tmc->name . ' (' . $balance->inventory_number . ') ' . $balance->expiration_date,
                    'type'               => $this->getType($balance->tmc),
                    'measure'            => $balance->tmc->measure->name ?? '',
                    'inventory_number'   => $balance->inventory_number,
                    'expiration_date'    => $balance->expiration_date,
                    'price'              => $balance->price,
                    'remains_start_date' => $remains_start_date,
                    'remains_end_date'   => $remains_end_date
                ]);
            }
        }

        //Разобьем по категориям
        $alcohol_tmc = [];
        $vaccines_tmc = [];
        $medications_tmc = [];
        foreach ($proceeded_tmc as $tmc) {
            if ($tmc->type == 'Спирт') {
                $alcohol_tmc[] = $tmc;
            }
            if ($tmc->type == 'Вакцина') {
                $vaccines_tmc[] = $tmc;
            }
            if ($tmc->type == 'Медпрепарат') {
                $medications_tmc[] = $tmc;
            }
        }
        $this->reportDto->alcohol_tmc = $alcohol_tmc;
        $this->reportDto->vaccines_tmc = $vaccines_tmc;
        $this->reportDto->medications_tmc = $medications_tmc;
    }

    /**
     * Содержится ли в названии "спирт"
     *
     * @param string $str
     * @return bool
     */

    private function isAlcohol(string $str)
    {
        if ((strpos($str, 'спирт')) !== false || (strpos($str, 'Спирт') !== false)) {
            return true;
        }

        return false;
    }

    private function getType(TmcBase $tmc)
    {
        if ($this->isAlcohol($tmc->name)) {
            return 'Спирт';
        } else {
            if ($tmc->type == TmcBase::TYPE_VACCINE) {
                return 'Вакцина';
            } else {
                return 'Медпрепарат';
            }
        }
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
        //Передача специалиста самому себе
        if (!empty($flow->balance->id_specialist)){
            if ($flow->balanceAction->from_id_specialist == $flow->balanceAction->to_id_specialist){
                return false;
            }
        }
        return true;
    }

}
