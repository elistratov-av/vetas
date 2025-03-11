<?php

namespace app\common\components\reports\handlers\DailySpendingReport\v1;

use app\common\components\reports\handlers\DailySpendingReport\v1\dto\MakeRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\BalanceFlow;
use yii\db\Exception;
use DateTime;

/**
 * Провайдер данных отчета.
 * Class DataProvider
 *
 * @property MakeRequestDto $requestDto
 * @package app\common\components\reports\handlers\DailySpendingReport\v1
 * @author Aleksandr Roik
 */
class DataProvider extends AbctractDataProvider
{
    /**
     * @var BalanceFlow[]
     */
    private $balanceFlows;

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
        $this->balanceFlows = BalanceFlow::find()
            ->alias('bf')
            ->leftJoin('tmc.balance b', 'bf.id_tmc_balance = b.id')
            ->leftJoin('tmc.balance_action ba', 'bf.id_balance_action = ba.id')
            ->andWhere(['b.id_specialist' => $this->requestDto->idSpecialist])
            ->andWhere(['b.id_organization' => $this->requestDto->idOrganization])
            ->andWhere(['bf.flow_type' => [
                BalanceFlow::FLOW_TYPE_DECREASE
            ]])
            ->andWhere([
                'between',
                'bf.created_at',
                $this->requestDto->year . '-' . $this->requestDto->month . '-01 00:00:00',
                $this->requestDto->year . '-' . $this->requestDto->month . '-' . cal_days_in_month(CAL_GREGORIAN, $this->requestDto->month, $this->requestDto->year) . ' 23:59:59',
            ])
            ->andWhere([
                'or',
                ['ba.status' => BalanceAction::STATUS_COMPLETED, 'ba.action' => BalanceAction::ACTION_WRITE_OFF],
                ['not', ['bf.id_visit_service' => null]]
            ])
            ->all();

        if (!$this->balanceFlows) {
            throw new Exception('Данные для отчета не найдены');
        }
    }

    /**
     * Возвращает список balance_flow за запрошенный период
     * @param int $id_balance
     * @return BalanceFlow[]
     */
    public function getBalanceFlowsInPeriod()
    {
        return $this->balanceFlows;
    }

    /**
     * Опеределяем и возвращает дату акта
     *
     * @return DateTime
     * @throws Exception
     */
    public function getAcceptorDate(): DateTime
    {
        return new \DateTime();
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
     * Возвращает список ТМЦ действия
     *
     * @return BalanceActionTmcList[]
     */
    public function getActionTmcList(): array
    {
        $result = [];

        foreach ($this->balanceActions as $balanceAction) {
            foreach ($balanceAction->actionTmcList as $tmcList) {
                $result[] = $tmcList;
            }
        }

        return $result;
    }
    public function getMonth(){
        return $this->requestDto->month;
    }
    public function getYear(){
        return $this->requestDto->year;
    }

}
