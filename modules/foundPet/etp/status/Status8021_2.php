<?php

namespace app\modules\foundPet\etp\status;

use app\models\db\found_pet\Ad;

/**
 * Class Status8021_2
 * @package app\modules\foundPet\etp\status
 */
class Status8021_2 extends Status8021
{
    const CODE = 8021.2;

    /**
     * @var int
     */
    public $ReasonCode = 2;
    /**
     * @var string
     */
    public $Note = '
Уважаемый пользователь!
Вы подавали объявление о %s животном и подписались на уведомления.
В случае если надо:
•	изменить параметры, указанные в объявлении
•	удалить поданное объявление
•	продлить срок размещения объявления
•	отредактировать параметры подписки на уведомления
•	отказаться от подписки на уведомления 

Необходимо перейти на сервис <a href="%s">«Поиск животных»</a> -> выбрать цель обращения «Животное %s» -> вкладка «Мои объявления» -> внести необходимые изменения.';


    /**
     * @inheritDoc
     */
    public function prepareNote()
    {
        $this->Note = sprintf(
            $this->Note,
            $this->ad->isLost() ? 'пропавшем' : 'найденном',
            $this->createUserAdsUrl(),
            $this->ad->isLost() ? 'пропало' : 'найдено'
        );
    }
}
