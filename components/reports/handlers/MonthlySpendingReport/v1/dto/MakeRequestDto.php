<?php

namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\dto;

use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для определения входящих параметров при создании отчета
 *
 * Class RequestDto
 *
 * @package app\common\components\reports\handlers\MonthlySpendingReport\v1\dto
 * @author Aleksandr Roik
 */
class MakeRequestDto extends AbstractDto implements RequestDtoInterface
{
    /**
     * id организации
     *
     * @var int
     */
    public $idOrganization;

    /**
     * id специалиста
     *
     * @var int
     */
    public $idSpecialist;

    /**
     * Начало периода
     *
     * @var string;
     */
    public $start_date;

    /**
     * Конец периода
     *
     * @var string;
     */
    public $end_date;
}
