<?php

namespace app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\dto;

use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для определения входящих параметров при создании отчета
 *
 * Class RequestDto
 *
 * @package app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\dto
 * @author Aleksandr Roik
 */
class MakeRequestDto extends AbstractDto implements RequestDtoInterface
{
    /**
     * id действия с балансом
     * @var integer
     */
    public $id_balance_action;

}
