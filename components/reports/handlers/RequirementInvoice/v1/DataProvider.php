<?php

namespace app\common\components\reports\handlers\RequirementInvoice\v1;

use app\common\components\reports\interfaces\AbctractDataProvider;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\NumberGenerator;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\Specialists;
use app\models\db\tmc\BalanceAction;
use app\models\db\tmc\BalanceActionTmcList;
use DateTime;
use NumberFormatter;
use yii\db\Exception;

/**
 * Провайдер данных отчета.
 * Здесь должны буть основные методы, что подготавилают и возвращаю данные для отчета
 * Class DataProvider
 *
 * @package app\common\components\reports\handlers\RequirementInvoice\v1
 * @author Aleksandr Roik
 */
class DataProvider extends AbctractDataProvider
{
    //ToDO перенести id в OrgTypes
    private static $SBBJ_ORG_TYPE_ID = 39;

    /**
     * @var  BalanceAction
     */
    private $balance_action;

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
        $this->hash = md5(
            $this->requestDto->id_balance_action
        );
    }

    /**
     * Дата акта / Дата совершения операции передачи
     *
     * @return  \DateTime
     */
    public function getAcceptorDate()
    {
        return $this->balance_action->acceptor_date ? new DateTime($this->balance_action->acceptor_date) : null;
    }

    /**
     * Дата отпуска ТМЦ
     *
     * @return  \DateTime
     */
    public function getInitiatorDate()
    {
        return $this->balance_action->initiator_date ? new DateTime($this->balance_action->initiator_date) : null;
    }

    /**
     * Номер
     * формат: {Требование накладная}{номер накладной}{дата}.
     * Номер накладной генерировать по маске Н{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Требование-накладная № ТН77-01-01/03-2021-001 от 01.03.2021
     *
     * @return  string
     */
    public function getNumber()
    {
        return
            'ТН' .
            $this->balance_action->fromOrganization->reg_number .
            '/' .
            $this->getAcceptorDate()->format('m') .
            '-' .
            $this->getAcceptorDate()->format('Y') .
            '-' .
            NumberGenerator::generateWithPeriodYear(
                $this->handler->getReportId(),
                $this->handler->getVerion(),
                $this->getHash(),
                new DateTime($this->getAcceptorDate()->format('Y-m'))
            );
    }

    /**
     * Отправитель
     *
     * @return  string
     */
    public function getSenderName()
    {
        /** @return  Organizations $sender_org */
        $sender_org = Organizations::findOne($this->balance_action->fromOrganization->id);

        return $sender_org->name;
    }

    /**
     * Получатель
     *
     * @return  string
     */
    public function getRecipientName()
    {
        /** @return  Organizations $recipient_org */
        $recipient_org = Organizations::findOne($this->balance_action->toOrganization->id);

        return $recipient_org->name;

    }

    /**
     * Название структурного подразделения
     *
     * @return  string
     */
    public function getFromOrganizationName()
    {
        if ($this->balance_action->fromOrganization->id_org_type === self::$SBBJ_ORG_TYPE_ID) {
            return '';
        } else {
            return $this->balance_action->fromOrganization->name;
        }
    }

    /**
     * Название структурного подразделения
     *
     * @return  string
     */
    public function getToOrganizationName()
    {
        if ($this->balance_action->toOrganization->id_org_type === self::$SBBJ_ORG_TYPE_ID) {
            return '';
        } else {
            return $this->balance_action->toOrganization->name;
        }
    }

    /**
     * Список ТМЦ действия
     *
     * @return  BalanceActionTmcList[]
     */
    public function getActionTmcList()
    {
        return $this->balance_action->actionTmcList;
    }

    /**
     * Итоговая сумма
     *
     * @return  string
     */
    public function getTotalSum()
    {
        $sum = 0;
        foreach ($this->getActionTmcList() as $tmcList) {
            $sum += ((float)$tmcList->count * (float)$tmcList->balance->price);
        }

        return $sum;
    }

    /**
     * Итоговая сумма прописью
     *
     * @return string
     */
    public function getTotalSumString()
    {
        $sum = $this->getTotalSum();
        $value = explode('.', number_format($sum, 2, '.', ''));

        $f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
        $str = $f->format($value[0]);

        // Первую букву в верхний регистр.
        $str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));

        // Склонение слова "рубль".
        $num = $value[0] % 100;
        if ($num > 19) {
            $num = $num % 10;
        }

        switch ($num) {
            case 1:
                $rub = 'рубль';
                break;
            case 2:
            case 3:
            case 4:
                $rub = 'рубля';
                break;
            default:
                $rub = 'рублей';
        }

        return $str . ' ' . $rub . ' ' . $value[1] . ' копеек.';
    }

    /**
     * Имя отпускающего специалиста
     *
     * @return Specialists
     */
    public function getInitiatorSpecialist()
    {
        return $this->balance_action->initiatorSpecialist;
    }

    /**
     * Имя принимающего специалиста
     *
     * @return Specialists
     */
    public function getAcceptorSpecialist()
    {
        return $this->balance_action->acceptorSpecialist;
    }

    private function initBalanceAction()
    {
        $this->balance_action =
            BalanceAction::find()
                ->where(['id' => $this->requestDto->id_balance_action,])
                ->one();

        if (!$this->balance_action) {
            throw new Exception('Данные для отчета с указанным id не найдены');
        }
    }
}
