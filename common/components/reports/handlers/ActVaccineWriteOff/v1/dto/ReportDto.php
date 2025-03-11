<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Структурное подразделение
     *
     * @var string
     */
    public $organizationName;

    /**
     * Дата акта / Дата совершения операции передачи
     *
     * @var \DateTime
     */
    public $acceptorDate;

    /**
     * Номер
     * Формат: {акт списания материальных запасов}{номер акта}{дата}.
     * Номер акта генерировать по маске АС{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Акт списания материальных запасов № АВ77-01-01/03-2021-001 от 01.03.2021
     *
     * @var string
     */
    public $number;

    /**
     * Список ТМЦ (вакцин)
     *
     * @var ReportTmcDto[]
     */
    public $tmc = [];
}
