<?php

namespace app\common\components\reports\handlers\DailySpendingReport\v1\builders;

use app\common\components\reports\handlers\DailySpendingReport\v1\DataProvider;
use app\common\components\reports\handlers\DailySpendingReport\v1\dto\SpentDto;
use app\common\components\reports\handlers\DailySpendingReport\v1\SingleDataProvider;
use app\common\components\reports\handlers\DailySpendingReport\v1\dto\ReportDto;
use app\common\components\reports\handlers\DailySpendingReport\v1\dto\TmcDto;
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
 * @package app\common\components\reports\handlers\DailySpendingReport\v1\builders
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
                    'specialist_name'   => $provider->getFromSpecialistName(),
                    'organization_name' => $provider->getFromOrganizationName(),
                    'month'             => $provider->getMonth(),
                    'year'              => $provider->getYear(),
                    'tmc'               => $this->buildTmc($provider->getBalanceFlowsInPeriod())
                ]
            );
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }


    /**
     * @param BalanceFlow[] $flows
     * @return TmcDto[]
     */
    private function buildTmc(array $flows)
    {
        $flows = array_filter($flows, function ($flow) {
            return $flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE
                && (!empty($flow->id_visit_service) || $flow->balanceAction->action === BalanceAction::ACTION_WRITE_OFF);
        });

        /** @var TmcDto[] $result */
        $result = [];
        foreach ($flows as $flow) {
            $tmc = $result[$flow->balance->id] ??
                new TmcDto([
                    'id_balance' => $flow->balance->id,
                    'name'       => $flow->balance->tmc->name . ' '
                                        . '(' . $flow->balance->inventory_number . ')'
                                        . ($flow->balance->expiration_date . ', годен до '
                                        . date('d.m.Y', strtotime($flow->balance->expiration_date))),

                    'measure'    => $flow->balance->tmc->measure->name ?? '',
                    'spent'      => [],
                ]);

            $day = (new \DateTime($flow->created_at))->format('d');
            $tmc->spent[$day] = $tmc->spent[$day] ?? new SpentDto();
            $tmc->spent[$day]->day = $day;
            $tmc->spent[$day]->total_spent += (float)$flow->count;
            $tmc->total_sum += (float)$flow->count;

            $result[$flow->balance->id] = $tmc;
        }

        return $result;
    }
}
