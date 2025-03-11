<?php

namespace app\common\components\reports\handlers\RequirementInvoice\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;
use app\models\db\Specialists;

/**
 * Корневая DTO для данных отчета.
 * В ствойствах могут быть и другие DTO. Все зависит от струкртуры сложности отчета...
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\RequirementInvoice\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Дата акта / Дата совершения операции передачи
     *
     * @var \DateTime
     */
    public $acceptor_date;

    /**
     * Дата отпуска ТМЦ
     *
     * @var \DateTime
     */
    public $initiator_date;

    /**
     * Номер
     * Формат: {требование-накладная}{номер накладной}{дата}.
     * Номер накладной генерировать по маске ТН{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер};
     * Требование-накладная № ТН77-01-01/03-2021-001 от 01.03.2021
     * @var string
     */
    public $number;

    /** Всегда ГБУ Мосветобъединение
     * @var string
     */
    public $organization = 'ГБУ "Мосветобъединение"';

    /** Структурное подразделение отправителя
     *
     * @var string
     */
    public $sender_org;

    /** Структурное подразделение получателя
     * (Если прием выполняется из дочерней организации)
     * @var string
     */
    public $recipient_org;
    /** СББЖ отправителя
     * @var string
     */
    public $sender;

    /** СББЖ получателя
     * @var string
     */
    public $recipient;


    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во
     * @var TmcDto[]
     */
    public $tmc = [];

    /**
     * Итого (сумма)
     * @var float
     */
    public $total_sum_outNDS;

    /**
     * Затребовавший сотрудник
     * @var Specialists
     */
    public $acceptor_specialist;

    /**
     * Отпустивший сотрудник
     * @var Specialists
     */
    public $initiator_specialist;
}
