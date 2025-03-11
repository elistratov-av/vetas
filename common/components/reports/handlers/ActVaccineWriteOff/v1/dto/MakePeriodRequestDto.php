<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto;

use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для определения входящих параметров при создании отчета за период
 * Class MakePeriodRequestDto
 *
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto
 * @author Aleksandr Roik
 */
class MakePeriodRequestDto extends AbstractDto implements RequestDtoInterface
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
     * Дата
     *
     * @var \DateTime;
     */
    public $date;

    /**
     * @param string $date
     * @return MakePeriodRequestDto
     */
    public function setDate(string $date): self
    {
        $this->date = new \DateTime($date);

        return $this;
    }
}
