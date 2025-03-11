<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;
use app\models\db\PetOwners;

/**
 * DTO тмц (вакцинации)
 * Class ReportTmcDto
 *
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto
 * @author Aleksandr Roik
 */
class ReportTmcDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Наименование TMC
     *
     * @var string
     */
    public $tmcName;

    /**
     * Производитель (Биофабрика)
     *
     * @var string
     */
    public $tmcProduced;

    /**
     * Строк годности
     *
     * @var \DateTime
     */
    public $tmcExpirationDate;

    /**
     * Серия/номер
     *
     * @var string
     */
    public $tmcInventoryNumber;

    /**
     * Список специалистов
     *
     * @var ReportSpecialistDto[]
     */
    public $specialists = [];

    /**
     * Список владельцев животных
     *
     * @var PetOwners[]
     */
    public $petOwners = [];

    /**
     * Количество "других" животных
     *
     * @var int
     */
    public $otherAnimalCount;

    /**
     * Количество собак
     *
     * @var int
     */
    public $dogCount;

    /**
     * Количество мелких собак
     *
     * @var int
     */
    public $dogIsSmallCount;

    /**
     * Количетво кошек
     *
     * @var int
     */
    public $catCount;

    /**
     * Общее кол.
     *
     * @var int
     */
    public $totalCount;

    /**
     * @param $value
     * @throws \Exception
     */
    public function setTmcExpirationDate($value)
    {
        $this->tmcExpirationDate = is_string($value) ? new \DateTime($value) : $value;
    }

}
