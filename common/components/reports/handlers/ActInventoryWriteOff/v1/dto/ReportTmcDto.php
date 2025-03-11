<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для данных ТМЦ отчета.
 * Class ReportTmcDto
 *
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto
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
     * Единица измерения
     *
     * @var string
     */
    public $measure;

    /**
     * Количество
     *
     * @var integer
     */
    public $count;

    /**
     * Цена
     *
     * @var float
     */
    public $price;

    /**
     * Сумма
     *
     * @var float
     */
    public $sum;

    /**
     * Направление расхода (комментарий)
     *
     * @var string
     */
    public $initiatorComment;

}
