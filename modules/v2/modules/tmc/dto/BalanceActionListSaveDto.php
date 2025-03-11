<?php

namespace app\modules\v2\modules\tmc\dto;

use app\common\dto\AbstractDto;

/**
 * DTO для параметров (items) входящего запросса методов-действий над балансовыми операциями
 * Class BalanceActionListDto
 *
 * @package app\modules\v2\modules\tmc\dto
 * @author Aleksandr Roik
 */
class BalanceActionListSaveDto extends AbstractDto
{
    /**
     * id сущности. Используется, если запись обсновляется
     *
     * @var
     */
    public $id;

    /**
     * id баланса
     *
     * @var int
     */
    public $idBalanceTmc;

    /**
     * id организации балансового ТМЦ
     *
     * @var int
     */
    public $idOrganization;

    /**
     * id специалиста балансового ТМЦ (нужен для ключа)
     *
     * @var int
     */
    public $idSpecialist;

    /**
     * id дозировки
     *
     * @var int|null
     */
    public $idDosage;

    /**
     * Количество, введенное пользователем
     *
     * @var int|null
     */
    public $countSelected;

    /**
     * Флаг: списать целиком весь баланс
     *
     * @var boolean|null
     */
    public $writeOffAll;

    /**
     * Флаг: списать целиком форму производства
     *
     * @var boolean|null
     */
    public $writeOffPackForm;

    /**
     * Комментарий
     *
     * @var string|null
     */
    public $comment;

}
