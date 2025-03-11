<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1;

use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\MakeSingleRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use yii\db\Exception;
use DateTime;

/**
 * Провайдер данных отчета по одному списанию.
 * Class DataProvider
 *
 * @property MakeSingleRequestDto $requestDto
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1
 * @author Aleksandr Roik
 */
class SingleDataProvider extends AbctractDataProvider
{
    /**
     * @var BalanceAction
     */
    private $balanceAction;

    /**
     * DataProvider constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param RequestDtoInterface $requestDto
     */
    public function __construct(ReportHandlerInterface $handler, RequestDtoInterface $requestDto)
    {
        parent::__construct($handler, $requestDto);
        $this->initBalanceAction();
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = md5($this->requestDto->idBalanceAction);
    }

    /**
     * @throws Exception
     */
    private function initBalanceAction()
    {
        $this->balanceAction =
            BalanceAction::find()
                ->where([
                    'id'     => $this->requestDto->idBalanceAction,
                    'status' => BalanceAction::STATUS_COMPLETED,
                    'action' => BalanceAction::ACTION_WRITE_OFF,
                ])->one();

        if (!$this->balanceAction) {
            throw new Exception('Данные для отчета не найдены');
        }
    }

    /**
     * Опеределяем и возвращает дату акта
     *
     * @return DateTime|null
     * @throws Exception
     */
    public function getAcceptorDate(): ?DateTime
    {
        return $this->balanceAction->acceptor_date ? new DateTime($this->balanceAction->acceptor_date) : null;
    }

    /**
     * @return string|null
     */
    public function getFromOrganizationName(): ?string
    {
        return $this->balanceAction->fromOrganization->short_name;
    }

    /**
     * @return string|null
     */
    public function getInitiatorSpecialistName(): ?string
    {
        return $this->balanceAction->initiatorSpecialist->getFullname();
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
            $this->balanceAction->fromOrganization->reg_number .
            '/' .
            $this->getAcceptorDate()->format('m') .
            '-' .
            $this->getAcceptorDate()->format('Y') .
            '-' .
            NumberGenerator::generateWithPeriodYear(
                $this->handler->getReportId(),
                $this->handler->getVerion(),
                $this->getHash(),
                new DateTime($this->balanceAction->created_at)
            );
    }

    /**
     * Возвращает список ТМЦ действия
     *
     * @return BalanceActionTmcList[]
     */
    public function getActionTmcList(): array
    {
        return $this->balanceAction->actionTmcList;
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
