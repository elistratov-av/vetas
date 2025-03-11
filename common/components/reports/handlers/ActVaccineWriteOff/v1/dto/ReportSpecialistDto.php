<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO о специалисте
 * Class ReportSpecialistDto
 *
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto
 * @author Aleksandr Roik
 */
class ReportSpecialistDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Название специалиста
     *
     * @var string
     */
    public $specialistName;

    /**
     * Название организации
     *
     * @var string
     */
    public $organizationName;
}
