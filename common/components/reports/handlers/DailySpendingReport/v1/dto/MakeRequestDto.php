<?php

namespace app\common\components\reports\handlers\DailySpendingReport\v1\dto;

use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для определения входящих параметров при создании отчета за период
 * Class MakeRequestDto
 *
 * @package app\common\components\reports\handlers\DailySpendingReport\v1\dto
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
     * код месяца
     *
     * @var string;
     */
    public $month;

    /**
     * год
     *
     * @var string;
     */
    public $year;
}
