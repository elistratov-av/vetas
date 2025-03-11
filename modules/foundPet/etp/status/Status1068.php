<?php

namespace app\modules\foundPet\etp\status;

/**
 * Class Status1068
 * @package app\modules\foundPet\etp\status
 */
class Status1068 extends Status
{
    const CODE = 1068;

    /**
     * @var string
     */
    public $StatusTitle = '';
    /**
     * @var string
     */
    public $Note = '';

    /**
     * Status1068 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }
}
