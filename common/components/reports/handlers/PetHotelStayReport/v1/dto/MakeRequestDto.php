<?php

namespace app\common\components\reports\handlers\PetHotelStayReport\v1\dto;

use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\dto\AbstractDto;

/**
 * DTO для определения входящих параметров при создании отчета за период
 * Class MakeRequestDto
 *
 * @package app\common\components\reports\handlers\PetHotelStayReport\v1\dto
 */
class MakeRequestDto extends AbstractDto implements RequestDtoInterface
{
    /**
     * Тип животного
     *
     * @var string
     */
    public $animalType;

    /**
     * код месяца
     *
     * @var string;
     */
    public $dateFrom;

    /**
     * год
     *
     * @var string;
     */
    public $dateTo;

    /**
     * статус
     *
     * @var idPetHotel;
     */
    public $idPetHotel;

    /**
     * код прайслиста
     *
     * @var string;
     */
    public $cod = '0328';
}
