<?php

namespace app\common\components\reports\handlers\DailySpendingReport\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\DailySpendingReport\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Сотрудник
     * @var string
     */
    public $specialist_name;

    /** Месяц
     * @var integer
     */
    public $month;


    /** Год
     * @var integer
     */
    public $year;

    /**
     * Наименование организации
     * @var string
     */
    public $organization_name;

    /**
     * @var TmcDto[]
     */
    public $tmc;
}
