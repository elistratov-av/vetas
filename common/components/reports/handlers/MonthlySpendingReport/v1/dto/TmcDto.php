<?php


namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\dto;


use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\dto\AbstractDto;

class TmcDto extends AbstractDto implements ReportDtoInterface
{
    /**
     * Наименование материала
     *
     * @var string
     */
    public $name;
    /**
     * Тип ТМЦ (Вакцина, спирт или медпрепарат)
     * @var string
     */
    public $type;
    /**
     * Единица измерения
     *
     * @var string
     */
    public $measure;

    /**
     * Серия
     * @var string
     */
    public $inventory_number;

    /**
     * Окончание срока годности
     * @var string
     */
    public $expiration_date;

    /**
     * Цена
     *
     * @var float
     */
    public $price;

    /** Остаток на начало  месяца
     * @var float
     */
    public $remains_start_date;


    /** Остаток на конец месяца
     * @var float
     */
    public $remains_end_date;

    /**
     * Приход ТМЦ
     * @var IncomeDto[]
     */
    public $income = [];

    /**
     * Сумма прихода
     * @var float
     */
    public $income_sum = 0;


    /**
     * Передача ТМЦ
     * @var TransferDto[]
     */
    public $transfer = [];

    /**
     * Расход за указанный период
     * @var float
     */
    public $decrease = 0;

    /**
     * Сумма расхода за указанный период
     * @var float
     */
    public $decrease_sum = 0;

}