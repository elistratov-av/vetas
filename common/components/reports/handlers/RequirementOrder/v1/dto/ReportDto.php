<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * Корневая DTO для данных отчета.
 * В ствойствах могут быть и другие DTO. Все зависит от струкртуры сложности отчета...
 * Class ReportDto
 *
 * @package app\common\components\reports\handlers\RequirementOrder\v1\dto
 * @author Aleksandr Roik
 */
class ReportDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Дата акта / Дата совершения операции передачи
     *
     * @var \DateTime
     */
    public $acceptor_date;

    /**
     * Дата формирования заказа
     *
     * @var \DateTime
     */
    public $initiator_date;


    /** Организация специалиста
     *
     * @var string
     */
    public $organization_to;

    /**
     * Список наименований ТМЦ с серией номера, сроком годности, кол-во
     * @var TmcDto[]
     */
    public $tmc = [];


    /**
     * Имя затребовавшего специалиста
     * @var string
     */
    public $initiator_name;
}
