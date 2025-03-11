<?php

namespace app\modules\soap\models\etp\response;

use app\modules\soap\models\etp\CoordinateMessageInterface;
use app\modules\soap\models\etp\status\StatusInterface;

class Note
{
    /** @var CoordinateMessageInterface  */
    protected $message;

    /** @var StatusInterface  */
    protected $status;

    public function __construct(StatusInterface $status, CoordinateMessageInterface $message)
    {
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->status->getNote($this->message);
    }

}
