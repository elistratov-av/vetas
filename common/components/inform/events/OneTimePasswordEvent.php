<?php

namespace app\common\components\inform\events;

use app\common\components\inform\EncryptPasswordTrait;

class OneTimePasswordEvent extends SubscriptionEvent
{
    use EncryptPasswordTrait;

    const EVENT_CODE = 'sendPassword';

    /** @var int */
    public $id_author;

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token): array
    {
        return [
            'password' => $this->password,
            'link' => $this->getService()->linkToLoginPage,
        ];
    }
}
