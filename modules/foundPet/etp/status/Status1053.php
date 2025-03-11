<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status1053
 * @package app\modules\foundPet\etp\status
 */
class Status1053 extends Status
{
    const CODE = 1053;

    /**
     * @var string
     */
    public $StatusTitle = 'Объявление изменено';
    /**
     * @var string
     */
    public $Note = 'Объявление о %s животном № %s от %s успешно отредактировано.';

    /**
     * Status1053 constructor.
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
