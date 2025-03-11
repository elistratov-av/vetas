<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status1051
 * @package app\modules\foundPet\etp\status
 */
class Status1051 extends Status
{
    const CODE = 1051;

    /**
     * @var string
     */
    public $StatusTitle = 'Размещение объявления продлено';
    /**
     * @var string
     */
    public $Note = 'Размещение объявления о %s животном № %s от %s успешно продлено на %d дней.';

    /**
     * Status1051 constructor.
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
            $this->ad->createdAt()->format('d.m.Y'),
            Ad::DAYS_ACTIVE
        );
    }
}
