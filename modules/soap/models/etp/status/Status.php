<?php

namespace app\modules\soap\models\etp\status;

use app\modules\soap\models\etp\CoordinateMessageInterface;

abstract class Status implements StatusInterface
{
    /** @var float */
    protected $code;

    /** @var string */
    protected $name;

    /** @var string */
    protected $note;

    /**
     * @return int
     */
    public function getCode() : float
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param \app\modules\soap\models\etp\CoordinateMessageInterface|\app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return string
     */
    public function getNote($message) : string
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote(string $note): void
    {
        $this->note = $note;
    }
}
