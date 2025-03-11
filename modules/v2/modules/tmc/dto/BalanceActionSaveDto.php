<?php

namespace app\modules\v2\modules\tmc\dto;

use app\common\dto\AbstractDto;

/**
 * DTO параметров входящего запросса модели BalanceAction
 * Class BalanceActionDto
 *
 * @package app\modules\v2\modules\tmc\dto
 * @author Aleksandr Roik
 */
class BalanceActionSaveDto extends AbstractDto
{
    /**
     * Номер (для документов)
     *
     * @var
     */
    public $num;

    /**
     * Статус
     *
     * @var string
     */
    public $status;

    /**
     * Действие (передача на баланс, утилизация, списание, запрос на выдачу)
     *
     * @var string
     */
    public $action;

    /**
     * Дата, когда создал
     *
     * @var string
     */
    public $initiatorDate;

    /**
     * Кто создал
     *
     * @var int
     */
    public $initiatorIdSpecialist;

    /**
     * Коммент того, кто создал
     *
     * @var string
     */
    public $initiatorComment;

    /**
     * Дата, когда согласился (отклонил)
     *
     * @var string
     */
    public $acceptorDate;

    /**
     * Кто согласился (отклонил)
     *
     * @var int
     */
    public $acceptorIdSpecialist;

    /**
     * Коммент того, кто согласился (отклонил)
     *
     * @var string
     */
    public $acceptorComment;

    /**
     * С баланса какой организации
     *
     * @var int
     */
    public $fromIdOrganization;

    /**
     * С баланса какого специалиста
     *
     * @var int
     */
    public $fromIdSpecialist;

    /**
     * На баланс какой организации
     *
     * @var int
     */
    public $toIdOrganization;

    /**
     * На баланс какого специалиста
     *
     * @var int
     */
    public $toIdSpecialist;

    /**
     * Тип причины списания
     *
     * @var string
     */
    public $writeOffReason;

    /**
     * Фактическая дата передачи
     *
     * @var string
     */
    public $transferDate;

    /**
     * Фактическая дата получения
     *
     * @var string
     */
    public $receivingDate;
}
