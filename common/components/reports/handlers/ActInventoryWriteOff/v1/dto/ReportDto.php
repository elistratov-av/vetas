<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto
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
     * Номер
     * Формат: {акт списания материальных запасов}{номер акта}{дата}.
     * Номер акта генерировать по маске АС{Регистрационный номер структурного подразделения}/{месяц}-{год}-{порядковый номер}
     * Акт списания материальных запасов № АС77-01-01/03-2021-001 от 01.03.2021
     *
     * @var string
     */
    public $number;

    /**
     * Учреждение
     * Всегда ГБУ "Мосветобъединение"
     *
     * @var string
     */
    public $organizationShortName = 'ГБУ "Мосветобъединение"';

    /**
     * Структурное подразделение
     * Указывается СББЖ округа или наименование дочерней организации
     *
     * @var string
     */
    public $fromOrganizationName;

    /**
     * Материально-ответственное лицо
     *
     * @var string
     */
    public $initiatorSpecialistName;

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во
     *
     * @var ReportTmcDto[]
     */
    public $tmc = [];

    /**
     * Итого (сумма)
     *
     * @var integer
     */
    public $sumTotal;
}
