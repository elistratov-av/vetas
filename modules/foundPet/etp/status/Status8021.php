<?php

namespace app\modules\foundPet\etp\status;

/**
 * Class Status8021
 * @package app\modules\foundPet\etp\status
 */
abstract class Status8021 extends Status
{
    const CODE = 8021;

    /**
     * @var string
     */
    public $StatusTitle = 'Отправка уведомления';
    /**
     * @var string
     */
    public $Note = '';

    /**
     * Status8021 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }
}
