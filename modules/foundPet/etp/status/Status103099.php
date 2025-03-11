<?php

namespace app\modules\foundPet\etp\status;

/**
 * Class Status103099
 * @package app\modules\foundPet\etp\status
 */
class Status103099 extends Status
{
    const CODE = 103099;

    /**
     * @var string
     */
    public $StatusTitle = 'Технический сбой';
    /**
     * @var string
     */
    public $Note = 'Произошел технический сбой. Попробуйте повторить позже.';

    /**
     * Status103099 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }
}
