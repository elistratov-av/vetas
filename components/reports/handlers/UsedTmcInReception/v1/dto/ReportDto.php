<?php

namespace app\common\components\reports\handlers\UsedTmcInReception\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\UsedTmcInReception\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{

    /**
     * Ветеринарное лечебное учреждение (подразделение)
     *
     * @var string
     */
    public $organizationName;

    /**
     * Ф.И.О. владельца
     *
     * @var string
     */
    public $ownerName;

    /**
     * Адрес, телефон владельца
     *
     * @var string
     */
    public $ownerAddress;

    /**
     * Адрес, телефон владельца
     *
     * @var string
     */
    public $ownerPhone;

    /**
     * id приема
     *
     * @var int
     */
    public $visitId;

    /**
     * Дата отчета
     *
     * @var \DateTime
     */
    public $date;

    /**
     * Ветеринарный врач (ФИО)
     *
     * @var string
     */
    public $specialistName;

    /**
     * Список наименований ТМЦ
     *
     * @var ReportTmcDto[]
     */
    public $tmc = [];
}
