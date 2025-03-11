<?php


namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\dto;


use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

class TransferDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Количество переданного
     * @var float
     */
    public $transfer_count = 0;

    /**
     * Кому передано
     * @var string
     */
    public $transfer_specialist;
}