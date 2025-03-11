<?php

namespace app\common\exceptions;

use yii\web\ForbiddenHttpException;

class UpdateForbiddenHttpException extends ForbiddenHttpException
{
    /**
     * UpdateForbiddenHttpException constructor.
     * @param null|string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        $message = (is_null($message)) ? 'Нет прав доступа к редактируемому ресурсу' : $message;
        parent::__construct($message, $code, $previous);
    }
}
