<?php

namespace app\common\components\reports\handlers\PetHotelStayReport\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * В свойствах могут быть и другие DTO. Все зависит от струкртуры сложности отчета...
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\PetHotelStayReport\v1\dto
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * типы животных
     * @var array
     */
    public $animalType;

    /**
     * Заявки
     * @var array
     */
    public $animals;

    /**
     * Дата от
     * @var string
     */
    public $dateFrom;

    /**
     * Дата до
     * @var string
     */
    public $dateTo;

    /**
     * Цена за день
     * @var float
     */
    public $priceForDay;
}
