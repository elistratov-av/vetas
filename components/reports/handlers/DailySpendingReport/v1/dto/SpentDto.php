<?php


namespace app\common\components\reports\handlers\DailySpendingReport\v1\dto;


use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

class SpentDto extends AbstractDto implements ReportDtoInterface
{
    public $day;

    public $total_spent = 0;
}