<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1\dto;

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

}
