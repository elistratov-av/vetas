<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status1080
 * @package app\modules\foundPet\etp\status
 */
class Status1080 extends Status
{
    const CODE = 1080;

    /**
     * @var string
     */
    public $StatusTitle = 'Объявление удалено';
    /**
     * @var string
     */
    public $Note = 'Объявление о %s животном № %s от %s удалено по причине несоответствия правилам предоставления электронного сервиса.';

    /**
     * Status1080 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->StatusCode = self::CODE;
    }

    /**
     * @inheritDoc
     */
    public function prepareNote()
    {
        $this->Note = sprintf(
            $this->Note,
            $this->ad->isLost() ? 'пропавшем' : 'найденном',
            $this->ad->id,
            $this->ad->createdAt()->format('d.m.Y')
        );
    }
}
