<?php


namespace app\common\components\reports\handlers\OrganizationReport\v1\dto;


use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Class MovementDto
 * @package app\common\components\reports\handlers\OrganizationReport\v1\dto
 * Дто движения ТМЦ (Приход, расход и т.д.)
 */
class MovementDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * @var float
     * Количество
     */
    public $count = 0;
    /**
     * Цена
     * @var string
     */
    public $price;
    /**
     * Стоимость
     * @var float
     */
    public $sum = 0;
}