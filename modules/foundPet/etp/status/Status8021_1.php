<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status8021_1
 * @package app\modules\foundPet\etp\status
 */
class Status8021_1 extends Status8021
{
    const CODE = 8021.1;

    /**
     * @var int
     */
    public $ReasonCode = 1;
    /**
     * @var string
     */
    public $Note = 'Уважаемый пользователь!
Найдено <a href="%s">новое объявление о %s животном</a>.

Чтобы отказаться от получения рассылки о новых объявлениях необходимо перейти на сервис <a href="%s">«Поиск пропавших и найденных животных»</a> -> выбрать цель обращения «Животное %s» -> вкладка «Мои объявления» -> внести необходимые изменения';

    /**
     * @var Ad
     */
    protected $ad_for_subscriber;

    /**
     * @param Ad $ad
     */
    public function setad_for_subscriber($ad)
    {
        $this->ad_for_subscriber = $ad;
    }

    /**
     * @inheritDoc
     */
    public function prepareNote()
    {
        $this->Note = sprintf(
            $this->Note,
            $this->createAdUrl($this->ad_for_subscriber->type, $this->ad_for_subscriber->id),
            $this->ad->isLost() ? 'найденном' : 'пропавшем',
            $this->createUserAdsUrl(),
            $this->ad->isLost() ? 'пропало' : 'найдено'
        );
    }
}
