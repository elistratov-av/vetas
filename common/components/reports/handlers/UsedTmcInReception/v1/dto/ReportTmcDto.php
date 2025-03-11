<?php

namespace app\common\components\reports\handlers\UsedTmcInReception\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для данных ТМЦ отчета.
 * Class ReportTmcDto
 *
 * @package app\common\components\reports\handlers\UsedTmcInReception\v1\dto
 * @author Aleksandr Roik
 */
class ReportTmcDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Наименование ТМЦ
     *
     * @var string
     */
    public $name;

    /**
     * Серийный номер
     *
     * @var string
     */
    public $inventoryNumber;

    /**
     * Срок годности
     *
     * @var \DateTime
     */
    public $expirationDate;

    /**
     * Количество используемого ТМЦ
     *
     * @var string
     */
    public $count;

    /**
     * Количество ТМЦ к утилизации
     *
     * @var string
     */
    public $countUtilize;
}
