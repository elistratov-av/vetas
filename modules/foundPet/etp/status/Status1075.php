<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status1075
 * @package app\modules\foundPet\etp\status
 */
abstract class Status1075 extends Status
{
    const CODE = 1075;

    /**
     * @var string
     */
    public $StatusTitle = 'Объявление удалено';
    /**
     * @var string
     */
    public $Note = 'Объявление о %s животном № %s от %s и параметры подписки успешно удалены.';

    /**
     * Status1075 constructor.
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
