<?php

namespace app\modules\foundPet\etp\status;

/**
 * Class Status106899
 * @package app\modules\foundPet\etp\status
 */
class Status106899 extends Status
{
    const CODE = 106899;

    /**
     * @var string
     */
    public $StatusTitle = 'Технический сбой';
    /**
     * @var string
     */
    public $Note = 'Произошел технический сбой. Попробуйте повторить позже.';

    /**
     * Status106899 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }
}
