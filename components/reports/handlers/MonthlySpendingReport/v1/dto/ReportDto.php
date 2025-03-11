<?php

namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * В ствойствах могут быть и другие DTO. Все зависит от струкртуры сложности отчета...
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\MonthlySpendingReport\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Сотрудник
     * @var string
     */
    public $specialist_name;

    /** Начало периода
     * @var integer
     */
    public $start_date;


    /** Конец периода
     * @var integer
     */
    public $end_date;

    /**
     * Наименование организации
     * @var string
     */
    public $organization_name;

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во (спирты)
     * @var TmcDto[]
     */
    public $alcohol_tmc = [];

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во (медпрепараты)
     * @var TmcDto[]
     */
    public $medications_tmc = [];

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во (вакцины)
     * @var TmcDto[]
     */
    public $vaccines_tmc = [];

}
