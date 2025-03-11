<?php

namespace app\modules\foundPet\etp\status;

/**
 * Class Status8011
 * @package app\modules\foundPet\etp\status
 */
abstract class Status8011 extends Status
{
    const CODE = 8011;

    /**
     * @var string
     */
    public $StatusTitle = '';
    /**
     * @var string
     */
    public $Note = '';

    /**
     * Status8011 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }
}
