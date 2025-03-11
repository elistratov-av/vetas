<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto;

use app\common\components\reports\handlers\ActInventoryWriteOff\v1\definitions\UnitsDefinition;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для определения входящих параметров при создании отчета по id действия
 * Class MakeSingleRequestDto
 *
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto
 * @author Aleksandr Roik
 */
class MakeSingleRequestDto extends AbstractDto implements RequestDtoInterface
{
    /**
     * id действия баланса
     *
     * @var int
     */
    public $idBalanceAction;

    /**
     * Единицы измерения
     *
     * @var string
     */
    public $units = UnitsDefinition::STANDART;
}
