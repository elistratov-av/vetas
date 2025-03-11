<?php

namespace app\common\components\reports\handlers\DailySpendingReport\v1\dto;

use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для данных ТМЦ отчета.
 * Class TmcDto
 *
 * @package app\common\components\reports\handlers\DailySpendingReport\v1\dto
 * @author Aleksandr Roik
 */
class TmcDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * @var integer
     * Ид баланса.
     */
    public $id_balance;
    /**
     * Наименование материала
     *
     * @var string
     */
    public $name;

    /**
     * Единица измерения
     *
     * @var string
     */
    public $measure;

    /**
     * Количество
     *
     * @var SpentDto[]
     */
    public $spent;

    public $total_sum = 0;


}
