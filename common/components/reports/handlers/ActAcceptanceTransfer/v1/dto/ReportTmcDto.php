<?php

namespace app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для данных ТМЦ отчета.
 * Class ReportTmcDto
 *
 * @package app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto
 * @author Aleksandr Roik
 */
class ReportTmcDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Наименование материала
     *
     * @var string
     */
    public $name;

    /**
     * Количество
     *
     * @var integer
     */
    public $count;
}
