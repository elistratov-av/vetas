<?php

namespace app\common\components\reports\handlers\OrganizationReport\v1;

use app\common\components\reports\handlers\OrganizationReport\v1\dto\MakeRequestDto;
use app\common\components\reports\handlers\OrganizationReport\v1\dto\MovementDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\common\helpers\DateHelper;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\Specialists;
use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use app\modules\admin\models\Organization;
use yii\db\Exception;
use DateTime;
use yii\db\Query;

/**
 * Провайдер данных отчета.
 * Class DataProvider
 *
 * @property MakeRequestDto $requestDto
 * @package app\common\components\reports\handlers\OrganizationReport\v1
 * @author Aleksandr Roik
 */
class DataProvider extends AbctractDataProvider
{
    /**
     * @var Query
     */
    private $main_query;

    /**
     * @var Organizations
     */
    private $organization;

    /**
     * DataProvider constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param MakeRequestDto $requestDto
     */
    public function __construct(ReportHandlerInterface $handler, RequestDtoInterface $requestDto)
    {
        parent::__construct($handler, $requestDto);
        $this->initBalanceFlows();
        $this->organization = Organizations::findOne($requestDto->idOrganization);
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = null;
    }

    private function initBalanceFlows()
    {
        $this->main_query = BalanceFlow::find()
            ->where([
                'between',
                'balance_flow.created_at',
                $this->requestDto->year . '-' . $this->requestDto->month . '-01 00:00:00',
                $this->requestDto->year . '-' . $this->requestDto->month . '-' . cal_days_in_month(CAL_GREGORIAN, $this->requestDto->month, $this->requestDto->year) . ' 23:59:59',
            ]);
    }

    /**
     * Движение ТМЦ в запрошенной организации
     *
     * @return BalanceFlow[]
     */
    public function getOrganizationFlows()
    {
        $query = clone $this->main_query;

        return $query
            ->leftJoin('tmc.balance b', 'b.id = balance_flow.id_tmc_balance')
            ->andWhere(['b.id_organization' => $this->organization->id])
            ->all();
    }

    /**
     * @return BalanceFlow[]
     * @deprecated
     * Движение ТМЦ c начала даты (для расчета остатков)
     */
    public function getTmcFlowsFromStartDate($id_tmc, $id_specialist)
    {
        $query = clone $this->main_query;

        return $query
            ->leftJoin('tmc.balance b', 'b.id = balance_flow.id_tmc_balance')
            ->andWhere([
                'b.id_specialist' => $id_specialist,
                'b.id_tmc' => $id_tmc
            ])
            ->andWhere(['>=', 'balance_flow.created_at', $this->requestDto->year . '-' . $this->requestDto->month . '-01 00:00:00'])
            ->all();
    }

    /**
     * Остаток на начало периода
     *
     * @param int $id_balance
     * @return MovementDto
     */
    public function getStartDateRemains(int $id_balance)
    {
        /** @var Balance $balance */
        $balance = Balance::findOne(['id' => $id_balance]);
        $remains = new MovementDto([
            'count' => $balance->count,
            'price' => $balance->price,
            'sum' => (float)$balance->price * $balance->count
        ]);
        $query = BalanceFlow::find()
            ->alias('bf')
            ->leftJoin('tmc.balance_action ba', 'bf.id_balance_action = ba.id')
            ->where(['bf.id_tmc_balance' => $id_balance])
            ->andWhere([
                '>=',
                'bf.created_at',
                $this->requestDto->year . '-' . $this->requestDto->month . '-01 00:00:00',
            ])
            ->andWhere(['bf.flow_type' => [BalanceFlow::FLOW_TYPE_INCREASE, BalanceFlow::FLOW_TYPE_DECREASE]]);
        if (isset($balance->id_specialist)) {
            $query->andWhere(['or', ['ba.status' => BalanceAction::STATUS_COMPLETED], ['not', ['bf.id_visit_service' => null]]]);
         }
        /** @var BalanceFlow[] $balance_flows */
        $balance_flows = $query->all();
        foreach ($balance_flows as $flow) {
            $sign = $flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE ? 1 : -1;
            $remains->count += $sign * $flow->count;
            $remains->sum += $sign * $flow->count * (float)$balance->price;
        }

        return $remains;
    }

    /**Остаток на конец периода
     *
     * @param int $id_balance
     * @return MovementDto
     */
    public function getEndDateRemains(int $id_balance)
    {
        /** @var Balance $balance */
        $balance = Balance::findOne(['id' => $id_balance]);
        $remains = $this->getStartDateRemains($id_balance);
        $query = BalanceFlow::find()
            ->alias('bf')
            ->leftJoin('tmc.balance_action ba', 'bf.id_balance_action = ba.id')
            ->where(['bf.id_tmc_balance' => $id_balance])
            ->andWhere([
                '>=',
                'bf.created_at',
                $this->requestDto->year . '-' . $this->requestDto->month . '-01 00:00:00',
            ])
            ->andWhere([
                '<=',
                'bf.created_at',
                $this->requestDto->year . '-' . $this->requestDto->month . '-'
                . cal_days_in_month(CAL_GREGORIAN, $this->requestDto->month, $this->requestDto->year) . ' 23:59:59',
            ])
            ->andWhere(['bf.flow_type' => [BalanceFlow::FLOW_TYPE_INCREASE, BalanceFlow::FLOW_TYPE_DECREASE]]);
        if (isset($balance->id_specialist)) {
            $query->andWhere(['or', ['ba.status' => BalanceAction::STATUS_COMPLETED], ['not', ['bf.id_visit_service' => null]]]);
        }
        /** @var BalanceFlow[] $balance_flows */
        $balance_flows = $query->all();
        foreach ($balance_flows as $flow) {
            $sign = $flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE ? -1 : 1;
            $remains->count += $sign * $flow->count;
            $remains->sum += $sign * $flow->count * (float)$balance->price;
        }

        return $remains;
    }

    /**
     * Все балансы в организации
     *
     * @return Balance[]
     */
    public function getOrganizationBalances()
    {
        return Balance::find()
            ->where([
                'id_organization' => $this->requestDto->idOrganization,
            ])
            ->all();
    }

    /**
     * @param string $name_tmc
     * @return MovementDto[]
     * @deprecated
     * Считает остатки на складе.
     */
    public function getTechnicRemains(string $name_tmc)
    {
        /** @var Balance[] $balances */
        $balances = Balance::find()
            ->where([
                'id_specialist' => null,
                'id_organization' => $this->organization->id,
            ])
            ->all();
        /** @var MovementDto[] $remains */
        $remains = [];
        foreach ($balances as $balance) {
            if (($balance->tmc->name . ' (' . $balance->inventory_number . ') ' . $balance->expiration_date) == $name_tmc) {
                $remains[$balance->price] = $remains[$balance->price] ?? new MovementDto();
                $remains[$balance->price]->count += (float)$balance->count;
                $remains[$balance->price]->price = $balance->price;
                $remains[$balance->price]->sum += (float)$balance->price * $balance->count;
                /** @var BalanceFlow[] $balance_flows */
                $balance_flows = BalanceFlow::find()
                    ->where(['id_tmc_balance' => $balance->id])
                    ->andWhere([
                        '>=',
                        'created_at',
                        $this->requestDto->year . '-' . $this->requestDto->month . '-'
                        . cal_days_in_month(CAL_GREGORIAN, $this->requestDto->month, $this->requestDto->year) . ' 23:59:59',
                    ])
                    ->andWhere(['flow_type' => [BalanceFlow::FLOW_TYPE_INCREASE, BalanceFlow::FLOW_TYPE_DECREASE]])
                    ->all();
                foreach ($balance_flows as $balance_flow) {
                    $sign = $balance_flow->flow_type == BalanceFlow::FLOW_TYPE_INCREASE ? -1 : +1;
                    $remains[$balance->price]->count += $sign * (float)$balance_flow->count;
                    $remains[$balance->price]->sum += $sign * (float)$balance_flow->count * $balance->price;
                }
            }
        }

        return $remains;
    }

    /**
     * @return string
     */
    public function getMonth()
    {
        return $this->requestDto->month;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->requestDto->year;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organization->short_name;
    }

}
