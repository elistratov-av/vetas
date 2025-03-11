<?php

namespace app\common\components\reports\handlers\MonthlySpendingReport\v1;

use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\MakeRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\Specialists;
use app\models\db\tmc\Balance;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use DateTime;
use NumberFormatter;
use phpDocumentor\Reflection\Types\This;
use yii\db\Exception;

/**
 * Провайдер данных отчета.
 * Здесь должны буть основные методы, что подготавилают и возвращаю данные для отчета
 * Class DataProvider
 *
 * @package app\common\components\reports\handlers\MonthlySpendingReport\v1
 * @author Aleksandr Roik
 */
class DataProvider extends AbctractDataProvider
{
    /**
     * @var BalanceFlow[]
     */
    private $balance_flows;

    /**
     * @var Specialists
     */
    private $specialist;

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
        $this->specialist = Specialists::findOne($requestDto->idSpecialist);
        $this->organization = Organizations::findOne($requestDto->idOrganization);
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = null;
    }

    /**
     * @throws Exception
     */
    private function initBalanceFlows()
    {
        $this->balance_flows = BalanceFlow::find()
            ->alias('bf')
            ->leftJoin('tmc.balance b', 'bf.id_tmc_balance = b.id')
            ->leftJoin('tmc.balance_action ba', 'bf.id_balance_action = ba.id')
            ->andWhere([
                'between',
                'bf.created_at',
                $this->requestDto->start_date . ' 00:00:00',
                $this->requestDto->end_date . ' 23:59:59',
            ])
            ->andWhere(
                [
                    'b.id_organization' => $this->requestDto->idOrganization,
                    'b.id_specialist' => $this->requestDto->idSpecialist
                ]
            )
            ->andWhere(['or', ['ba.status' => BalanceAction::STATUS_COMPLETED], ['not', ['bf.id_visit_service' => null]]])
            ->all();

    }

    /**
     * Возвращает список ТМЦ действия
     *
     * @return BalanceFlow[]
     */
    public function getBalanceFlows(): array
    {
        return $this->balance_flows;
    }


    /**
     * @return string|null
     */
    public function getFromOrganizationName(): ?string
    {
        return $this->organization->short_name;
    }

    /**
     * @return string|null
     */
    public function getFromSpecialistName(): ?string
    {
        return $this->specialist->getFullname();
    }

    /**
     * Начало периода
     * @return string
     */
    public function getStartDate()
    {
        return $this->requestDto->start_date;
    }

    /**
     * Конец периода
     * @return string
     */
    public function getEndDate()
    {
        return $this->requestDto->end_date;
    }

    /**
     * Возвращает остаток на начало периода
     * @param int $id_balance
     * @return float
     */
    public function getStartDateRemains(int $id_balance)
    {
        $start_date = $this->requestDto->start_date;
        /** @var BalanceFlow[] $flows */
        $flows = BalanceFlow::find()
            ->alias('bf')
            ->leftJoin('tmc.balance_action ba', 'bf.id_balance_action = ba.id')
            ->where(['id_tmc_balance' => $id_balance])
            ->andWhere(['>=', 'bf.created_at', "$start_date" . ' 00:00:00'])
            ->andWhere([
                'or',
                ['ba.status' => BalanceAction::STATUS_COMPLETED],
                ['not', ['bf.id_visit_service' => null]],
                ['and', ['ba.status' => BalanceAction::STATUS_WAITING_CONFIRMATION, 'bf.flow_type' => BalanceFlow::FLOW_TYPE_HOLD]]
            ])
            ->all();
        /** @var Balance $balance */
        $balance = Balance::find()->where(['id'=>$id_balance])->one();
        $sum = $balance->count;
        foreach ($flows as $flow) {
            if ($flow->flow_type == BalanceFlow::FLOW_TYPE_INCREASE) {
                $sum -= $flow->count;
            } else if ($flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE || $flow->flow_type == BalanceFlow::FLOW_TYPE_HOLD) {
                $sum += $flow->count;
            }
        }
        return $sum;
    }

    /**
     * Возвращает остаток на конец периода
     * @param int $id_balance
     * @return float
     */
    public function getEndDateRemains(int $id_balance)
    {
        $end_date = $this->requestDto->end_date;
        /** @var BalanceFlow[] $flows */
        $flows = BalanceFlow::find()
            ->alias('bf')
            ->leftJoin('tmc.balance_action ba', 'bf.id_balance_action = ba.id')
            ->where(['id_tmc_balance' => $id_balance])
            ->andWhere(['>=', 'bf.created_at', "$end_date" . ' 23:59:59'])
            ->andWhere([
                'or',
                ['ba.status' => BalanceAction::STATUS_COMPLETED],
                ['not', ['bf.id_visit_service' => null]],
                ['and', ['ba.status' => BalanceAction::STATUS_WAITING_CONFIRMATION, 'bf.flow_type' => BalanceFlow::FLOW_TYPE_HOLD]]
            ])
            ->all();

        $balance = Balance::findOne($id_balance);
        $sum = $balance->count;
        foreach ($flows as $flow) {
            if ($flow->flow_type == BalanceFlow::FLOW_TYPE_INCREASE) {
                $sum -= $flow->count;
            } else if ($flow->flow_type == BalanceFlow::FLOW_TYPE_DECREASE || $flow->flow_type == BalanceFlow::FLOW_TYPE_HOLD) {
                $sum += $flow->count;
            }
        }
        return $sum;
    }

    /**
     * Возвращает список balance_flow за запрошенный период
     * @param int $id_balance
     * @return BalanceFlow[]
     */
    public function getBalanceFlowsInPeriod(int $id_balance)
    {
        $flows = BalanceFlow::find()
            ->where(['id_tmc_balance' => $id_balance])
            ->andWhere(['flow_type' => [
                BalanceFlow::FLOW_TYPE_INCREASE,
                BalanceFlow::FLOW_TYPE_DECREASE,
            ]])
            ->andWhere([
                'between',
                'created_at',
                $this->requestDto->start_date . ' 00:00:00',
                $this->requestDto->end_date . ' 23:59:59',
            ])
            ->all();
        return $flows;
    }

    /**
     * Возвращает балансы спеца в организации
     * @return Balance[]
     */
    public function getBalances(){
        /** @var Balance[] $balances */
        $balances = Balance::find()
            ->where([
                'id_specialist' => $this->requestDto->idSpecialist,
                'id_organization' => $this->requestDto->idOrganization
            ])
            ->all();
        return $balances;
    }

    /**
     * @return BalanceFlow[]
     *  Возвращает список всех BalanceFlow прихода, которых не было в $proceed_tmc
     */
    public function getIncomeBalanceFlows()
    {
        /** @var BalanceAction[] $actions */
        $actions = BalanceAction::find()
            ->andWhere([
                'between',
                'acceptor_date',
                $this->requestDto->start_date . ' 00:00:00',
                $this->requestDto->end_date . ' 23:59:59',
            ])
            ->andWhere(['status' => BalanceAction::STATUS_COMPLETED])
            ->andWhere(
                [
                    'to_id_organization' => $this->requestDto->idOrganization,
                    'to_id_specialist' => $this->requestDto->idSpecialist
                ]
            )
            ->andWhere(['action'=>BalanceAction::ACTION_TRANSFER_TO_BALANCE])
            ->all();

        /** @var BalanceFlow[] $result */
        $result = [];
        foreach ($actions as $action) {
            $flows = BalanceFlow::find()
                ->andWhere(['id_balance_action'=>$action->id])
                ->andWhere(['flow_type'=> BalanceFlow::FLOW_TYPE_INCREASE])
                ->all();
            $result = array_merge($result, $flows);
        }
        return $result;
    }

}
