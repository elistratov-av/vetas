<?php

namespace app\modules\v2\common\balance;

/**
 * Interface CalculationInterface
 *
 * @package app\modules\v2\common\balance
 * @author Aleksandr Roik
 */
interface CalculationInterface
{
    public function getCount(): float;

    public function getPrice(): float;

    public function getCountUtilize(): float;

    public function getCountProductionForm(): float;
}
