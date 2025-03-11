<?php

namespace app\modules\soap\models\etp\status;

class Status1168 extends Status
{
    const CODE = 1168;

    protected $code;

    protected $name = 'В переносе записи отказано';
    protected $note = 'Перенос записи на указанную дату и время невозможен. Попробуйте повторить запись на другое время.';

    /**
     * Status1050 constructor.
     */
    public function __construct()
    {
        $this->code = self::CODE;
    }

    /**
     * @return string
     */
    public static function visitDateTimeError()
    {
        return 'Перенос записи на указанную дату и время невозможен. Данный слот времени был только что забронирован. Попробуйте повторить запись на другое время';
    }

    /**
     * @return string
     */
    public static function wrongOrgError()
    {
        return 'Перенос записи в выбранную клинику невозможен. Вы можете перенести текущую запись только в рамках ранее выбранной клиники/сети клиник. Если вы хотите записаться в выбранную при переносе записи клинику, то необходимо создать новую запись к ветеринарному врачу и отменить текущую запись в личном кабинете.';
    }
}
