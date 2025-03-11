<?php

namespace app\modules\soap\log;

interface LoggerAwareInterface
{
    public function setLogger(callable $logger): void;
}