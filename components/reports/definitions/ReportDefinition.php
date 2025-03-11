<?php

namespace app\common\components\reports\definitions;

use app\common\definitions\AbstractDefinition;
use function Complex\sec;

/**
 * Справочник отчетов
 * Class ReportDefinition
 *
 * @package app\common\components\reports\definitions
 * @author Aleksandr Roik
 */
class ReportDefinition extends AbstractDefinition
{
    /**
     * Отчеты
     */
    const ACT_INVENTORY_WRITE_OFF = 1; //Акт о списании материальных запасов
    const ACT_ACCEPTANCE_TRANSFER = 2; //Акт приема-передачи ТМЦ
    const ACT_VACCINE_WRITE_OFF = 3; //Акт списания вакцины ежедневный от врача
    const INVOICE_MATERIALS_LEAVE_TO_SIDE = 4; // Накладная на отпуск материалов на сторону
    const REQUIREMENT_INVOICE = 5; // Требование-накладная
    const MONTHLY_SPENDING_REPORT = 6; // Ведомость ежемесячных расходов
    const DAILY_SPENDING_REPORT = 7; // Ведомость ежедневных расходов
    const REQUIREMENT_ORDER = 8; // Требование-заказ
    const ORGANIZATION_REPORT = 9; // Отчет по организации
    const USED_TMC_IN_RECEPTION = 10; //Отчет по использованных ТМЦ (балансовых) в приеме/услуге



    /**
     * @var int[]
     */
    protected static $collection = [
        self::ACT_INVENTORY_WRITE_OFF,
        self::INVOICE_MATERIALS_LEAVE_TO_SIDE,
        self::REQUIREMENT_INVOICE,
        self::MONTHLY_SPENDING_REPORT,
        self:: DAILY_SPENDING_REPORT,
        self::ACT_ACCEPTANCE_TRANSFER,
        self::ACT_VACCINE_WRITE_OFF,
        self::REQUIREMENT_ORDER,
        self::ORGANIZATION_REPORT,
        self::USED_TMC_IN_RECEPTION,
    ];

    /**
     * @var string[]
     */
    protected static $titleCollection = [
        self::ACT_INVENTORY_WRITE_OFF => 'Акт о списании материальных запасов',
        self::INVOICE_MATERIALS_LEAVE_TO_SIDE => 'Накладная на отпуск материалов на сторону',
        self::REQUIREMENT_INVOICE => 'Требование-накладная',
        self::ACT_ACCEPTANCE_TRANSFER => 'Акт приема-передачи ТМЦ',
        self::ACT_VACCINE_WRITE_OFF   => 'Акт списания вакцины ежедневный от врача',
        self::DAILY_SPENDING_REPORT => 'Ведомость ежедневного расходования',
        self::MONTHLY_SPENDING_REPORT => 'Ведомость ежемесячного расходования',
        self::REQUIREMENT_ORDER => 'Требование-заказ',
        self::ORGANIZATION_REPORT => 'Отчет о расходовании ТМЦ в организации',
        self::USED_TMC_IN_RECEPTION => 'Отчет по использованных ТМЦ (балансовых) в приеме/услуге',
    ];

}
