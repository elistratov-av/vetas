<?php


namespace app\common\validators;


class PGIdValidator extends PGIntegerValidator
{
    /**
     * @var int min value
     */
    public $min = 1;
}
