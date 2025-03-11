<?php


namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\dto;


use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

class IncomeDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Приход за указанный период
     * @var float
     */
    public $income_count = 0;

    /**
     * От кого получено
     * @var string
     */
    public $income_specialist;
}