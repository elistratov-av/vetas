<?php

namespace app\modules\soap\models\etp\status;

interface StatusReasonInterface extends StatusInterface
{
    /**
     * @return int
     */
    public function getReasonCode() : int;
}
