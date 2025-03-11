<?php

namespace app\modules\foundPet\etp\status;

/**
 * Class Status801199
 * @package app\modules\foundPet\etp\status
 */
class Status801199 extends Status
{
    const CODE = 801199;

    /**
     * @var string
     */
    public $StatusTitle = 'Технический сбой';
    /**
     * @var string
     */
    public $Note = 'Произошел технический сбой. Попробуйте повторить позже.';

    /**
     * Status801199 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }
}
