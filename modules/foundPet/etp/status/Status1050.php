<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status1050
 * @package app\modules\foundPet\etp\status
 */
class Status1050 extends Status
{
    const CODE = 1050;

    /**
     * @var string
     */
    public $StatusTitle = 'Объявление опубликовано';
    /**
     * @var string
     */
    public $Note = 'Создано объявление о %s животном № %s от %s. Срок размещения объявления %d дней. Ознакомиться с поданными объявлениями и параметрами подписки можно в разделе <a href="%s">«Мои объявления»</a> сервиса «Поиск пропавших и найденных животных».';

    /**
     * Status1050 constructor.
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
            Ad::DAYS_ACTIVE,
            $this->createUserAdsUrl()
        );
    }
}
