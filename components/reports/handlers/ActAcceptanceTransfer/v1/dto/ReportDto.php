<?php

namespace app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Дата акта / Дата совершения операции передачи
     *
     * @var \DateTime
     */
    public $acceptorDate;

    /**
     * Номер акта
     * формат: {акт приема-передачи}{номер акта}{дата}.
     * Формат: {Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Акт приема-передачи № АП77-01-01/03-2021-001 от 01.03.2021
     *
     * @var string
     */
    public $number;

    /**
     * Наименование организации (для шапки)
     *
     * @var string
     */
    public $fromOrganizationNameHeader;

    /**
     * Наименование организации
     *
     * @var string
     */
    public $fromOrganizationName;

    /**
     * Наименование организации
     *
     * @var string
     */
    public $fromSpecialistName;

    /**
     * Материально-ответственное лицо
     *
     * @var string
     */
    public $toOrganizationName;

    /**
     * Материально-ответственное лицо
     *
     * @var string
     */
    public $toSpecialistName;

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во
     *
     * @var ReportTmcDto[]
     */
    public $tmc = [];
}
