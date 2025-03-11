<?php

namespace app\modules\soap\log;

trait LoggerAwareTrait
{
    /** @var callable|null */
    protected $logger;

    public function setLogger(callable $logger): void
    {
        $this->logger = $logger;
    }

    protected function log($msg, string $level, $extraData = null): void
    {
        if (null === $this->logger) {
            return;
        }

        call_user_func($this->logger, $msg, $level, $extraData);
    }
}