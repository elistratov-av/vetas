<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status1075_3
 *
 * @package app\modules\foundPet\etp\status
 */
class Status1075_3 extends Status1075
{
    const CODE = 1075.3;

    /**
     * @var int
     */
    public $ReasonCode = 3;
    /**
     * @var string
     */
    public $Note = 'Объявление о %s животном № %s от %s удалено в связи с тем, что прошло более %d дней с момента публикации или внесения последних изменений.';

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
