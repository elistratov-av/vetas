<?php


namespace app\common\components\reports\handlers\RequirementInvoice\v1\dto;


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
     * Форма выпуска
     *
     * @var string
     */
    public $form_name;

    /**
     * Единица измерения
     *
     * @var string
     */
    public $measure;

    /**
     * Количество
     *
     * @var integer
     */
    public $count;

    /**
     * Цена
     *
     * @var float
     */
    public $price;

    /**
     * Сумма
     *
     * @var float
     */

    public $sum;
    /**
     * Сумма без НДС
     *
     * @var float
     */

    public $sum_outNDS;

    /**
     * НДС
     * @var float
     */
    public $NDS;

    /**
     * Комментарий инициатора
     *
     * @var string
     */
    public $initiator_comment;

    /**
     * Комментарий принимающего
     *
     * @var string
     */
    public $acceptor_comment;

}