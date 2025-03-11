<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1;

use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\MakePeriodRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use yii\db\Exception;
use DateTime;

/**
 * Провайдер данных отчета за период.
 * Class PerionDataProvider
 *
 * @property MakePeriodRequestDto $requestDto
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1
 * @author Aleksandr Roik
 */
class PerionDataProvider extends AbctractDataProvider
{
    /**
     * @var BalanceAction[]
     */
    private $balanceActions;

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
     * @param MakePeriodRequestDto $requestDto
     */
    public function __construct(ReportHandlerInterface $handler, RequestDtoInterface $requestDto)
    {
        parent::__construct($handler, $requestDto);
        $this->specialist = Specialists::findOne($requestDto->idSpecialist);
        $this->organization = Organizations::findOne($requestDto->idOrganization);
        $this->initBalanceAction();
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = md5(
            $this->requestDto->idOrganization .
            $this->requestDto->idSpecialist .
            $this->requestDto->year .
            $this->requestDto->month
        );
    }

    /**
     * @throws Exception
     */
    private function initBalanceAction()
    {
        $this->balanceActions = BalanceAction::find()
            ->andWhere([
                'between',
                'acceptor_date',
                $this->requestDto->year . '-' . $this->requestDto->month . '-01 00:00:00',
                $this->requestDto->year . '-' . $this->requestDto->month . '-' . cal_days_in_month(CAL_GREGORIAN, $this->requestDto->month, $this->requestDto->year) . ' 23:59:59',
            ])
            ->andWhere(['from_id_organization' => $this->requestDto->idOrganization])
            ->andWhere(['initiator_id_specialist' => $this->requestDto->idSpecialist])
            ->andWhere(['status' => BalanceAction::STATUS_COMPLETED])
            ->andWhere(['action' => BalanceAction::ACTION_WRITE_OFF])
            ->all();

        if (!$this->balanceActions) {
            throw new Exception('Данные для отчета не найдены');
        }
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
    public function getInitiatorSpecialistName(): ?string
    {
        return $this->specialist->getFullname();
    }

    /**
     * Номер.
     * Маска:
     * {акт списания материальных запасов}{номер акта}{дата}.
     * Номер акта генерировать по маске АС{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Акт списания материальных запасов № АС77-01-01/03-2021-001 от 01.03.2021
     *
     * @return string
     */
    public function getNumber(): string
    {
        return
            'АС' .
            $this->organization->reg_number .
            '/' .
            $this->requestDto->month .
            '-' .
            $this->requestDto->year .
            '-' .
            NumberGenerator::generateWithPeriodYear(
                $this->handler->getReportId(),
                $this->handler->getVerion(),
                $this->getHash(),
                new DateTime($this->requestDto->year . '-' . $this->requestDto->month . '-01')
            );
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

    /**
     * Общая сума по ТМЦ
     *
     * @return float
     */
    public function getSumTotal(): float
    {
        $sum = 0;
        foreach ($this->getActionTmcList() as $tmcList) {
            $sum += ((float)$tmcList->count * (float)$tmcList->balance->price);
        }

        return $sum;
    }
}
