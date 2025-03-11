<?php

namespace app\common\components\inform\events;

use app\models\db\Visits;

class RefundEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'refund';

    /** @var Visits */
    public $visit;
    /** @var string */
    public $bank;
    /** @var string */
    public $corresponded_account;
    /** @var string */
    public $org_name;
    /** @var string */
    public $bik;
    /** @var string */
    public $client_account;
    /** @var string */
    public $client_fio;

    /** @var bool */
    public $isAdminNotification = true;
    //protected $email = 'mosobvet@vet.mos.ru';
    protected $email = 'evgeny.starodubtsev@park-solutions.ru';

    public function getEventData($token = null): array
    {
        $io = $this->visit->owner->i_fio . ' ' . $this->visit->owner->o_fio;

        return [
            'io' => $io,
            'bank' => $this->bank,
            'corresponded_account' => $this->corresponded_account,
            'org_name' => $this->org_name,
            'bik' => $this->bik,
            'client_account' => $this->client_account,
            'client_fio' => $this->client_fio,
        ];
    }
}
