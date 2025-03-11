<?php


namespace app\modules\animalid\skeletons\exceptions;


class IntegrationException extends \Exception
{
    public  $object;

    public function __construct($object, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->object = $object;
        parent::__construct($message, $code, $previous);
    }
}
