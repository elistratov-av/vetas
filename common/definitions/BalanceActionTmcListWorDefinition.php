<?php

namespace app\common\definitions;

/**
 * Справочник типов причин списания ТМС
 * Class BalanceActionTmcListWorDefinition
 *
 * @package app\common\definitions
 * @author Aleksandr Roik
 */
class BalanceActionTmcListWorDefinition extends AbstractDefinition
{
    /**
     * Типы причины списания
     */
    const WOR_EXPIRATION_DATE = 'write-off_expiration_date';
    const WOR_SPOILED_TMC = 'write-off_spoiled_tmc';
    const WOR_UNPUNTABLE_TMC = 'write-off_uncountable_tmc';

    /**
     * @var array
     */
    protected static $collection = [
        self::WOR_EXPIRATION_DATE,
        self::WOR_SPOILED_TMC,
        self::WOR_UNPUNTABLE_TMC,
    ];

    /**
     * @var array
     */
    protected static $titleCollection = [
        self::WOR_EXPIRATION_DATE => 'Списание по истечению срока годности (утилизация)',
        self::WOR_SPOILED_TMC     => 'Списание испорченного ТМЦ',
        self::WOR_UNPUNTABLE_TMC  => 'Списание неисчисляемых ТМЦ, используемых в приемах',
    ];

}
