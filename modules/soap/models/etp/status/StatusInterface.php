<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

interface StatusInterface
{
    /**
     * @return int
     */
    public function getCode() : float;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     */
    public function getNote($message) : string;

    /**
     * @param string $note
     */
    public function setNote(string $note): void;
}
