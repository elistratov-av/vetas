<?php

namespace app\common\components\reports\handlers\OrganizationReport\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для данных ТМЦ отчета.
 * Class TmcDto
 *
 * @package app\common\components\reports\handlers\OrganizationReport\v1\dto
 * @author Aleksandr Roik
 */
class TmcDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Наименование материала
     *
     * @var string
     */
    public $name;
    /**
     * Срок годности
     * @var string
     */
    public $expiration_date;
    /**
     * @var string
     * Имя специалиста
     */
    public $specialist_name;

    /**
     * Единица измерения
     *
     * @var string
     */
    public $measure;

    /**
     * @var MovementDto
     * Выдача (передача)
     */
    public $transfer;
    /**
     * @var MovementDto
     * Приход
     */
    public $income;
    /**
     * @var MovementDto
     * Расход
     */
    public $decrease;
    /**
     * @var MovementDto
     * Остаток на начало месяца
     */
    public $remains_start_date;
    /**
     * @var MovementDto
     * Остаток на конец месяца
     */
    public $remains_end_date;


}
