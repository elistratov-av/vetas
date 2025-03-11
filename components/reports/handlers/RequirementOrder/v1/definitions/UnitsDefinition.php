<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1\definitions;

use app\common\definitions\AbstractDefinition;

/**
 * Справочник единиц измерения
 * Class UnitsDefinition
 *
 * @package app\common\components\reports\handlers\RequirementOrder\v1\definitions
 * @author Aleksandr Roik
 */
class UnitsDefinition extends AbstractDefinition
{
    const STANDART = 'standart'; //стандартные единицы
    const DOSAGE = 'dosage'; // дозы
}
