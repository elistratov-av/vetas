<?php

namespace app\common\components\ticket;

interface TicketGeneratorInterface
{
    /**
     * Определение номера визита
     * @return int
     */
    public function getVisitNumber() : int;

    /**
     * Генерация номера талона
     * @return string
     */
    public function make() : string;
}
