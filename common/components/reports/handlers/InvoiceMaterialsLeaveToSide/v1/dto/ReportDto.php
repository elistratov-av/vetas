<?php

namespace app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;
use app\models\db\Specialists;

/**
 * Корневая DTO для данных отчета.
 * В ствойствах могут быть и другие DTO. Все зависит от струкртуры сложности отчета...
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\dto
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
     * формат: {накладная на отпуск материалов}{номер накладной}{дата}.
     * Номер накладной генерировать по маске Н{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Накладная на отпуск материалов № Н77-01-01/03-2021-001 от 01.03.2021
     * @var string
     */
    public $number;

    /** Отправитель (СББЖ округа передающего ТМЦ)
     * @var string
     */
    public $sender;

    /** Структурное подразделение отправителя
     * (Если передача выполняется из дочерней организации)
     * @var string
     */
    public $sender_org;

    /** Получатель (СББЖ округа передающего ТМЦ)
     * @var string
     */
    public $recipient;

    /** Структурное подразделение получателя
     * (Если прием выполняется из дочерней организации)
     * @var string
     */
    public $recipient_org;

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во
     * @var TmcDto[]
     */
    public $tmc = [];

    /**
     * Итого (сумма)
     * @var float
     */
    public $total_sum;

    /**
     * Итоговая сумма прописью
     * @var string
     */
    public $str_sum;

    /**
     * Принимающий специалист
     * @var Specialists
     */
    public $acceptor_specialist;

    /**
     * Отпускающий специалист
     * @var Specialists
     */
    public $initiator_specialist;
}
