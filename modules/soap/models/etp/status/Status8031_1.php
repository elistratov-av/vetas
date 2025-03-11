<?php

namespace app\modules\soap\models\etp\status;

use app\models\db\Visits;
use app\modules\soap\models\etp\CoordinateMessageInterface;

class Status8031_1 extends Status
{
    const CODE = 8031.1;

    protected $name = 'Оплата произведена';
    // Отмена онлайн консультации доступна вплоть до начала приема заявителя.
    // Пользователю доступна кнопка [Отменить запись]
    // Пользователю недоступна кнопка [Оплата]
    protected $note = 'Произведена оплата онлайн-консультации. Дата и время консультации: Ссылка на онлайн-трансляцию %s';

    public function __construct()
    {
        $this->code = self::CODE;
    }

    public function getNote($message): string
    {
        return sprintf($this->note, Visits::TELEVET_CLIENT_LINK . $message->getVisit()->guid_video);
    }
}
