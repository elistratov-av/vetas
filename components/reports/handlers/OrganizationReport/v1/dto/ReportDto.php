<?php

namespace app\common\components\reports\handlers\OrganizationReport\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\OrganizationReport\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
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
     * Движение ТМЦ у специалистов. Ключи: [name_tmc][id_specialist]
     * @var TmcDto[][]
     */
    public $specialist_tmc;

    /**
     * Движение ТМЦ у техников (организации). Ключи: [name_tmc].
     * @var TmcDto[]
     */
    public $technic_tmc;

}
