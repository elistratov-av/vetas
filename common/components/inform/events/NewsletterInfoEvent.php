<?php

namespace app\common\components\inform\events;

use app\common\models\NewsletterInfoStatus;
use app\models\db\NewsletterInfo;

class NewsletterInfoEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'newsletter_info';

    /** @var NewsletterInfo */
    public $newsletter;

    public function init()
    {
        $this->newsletter->status = NewsletterInfoStatus::IS_SENT;
        $this->newsletter->actual_mailing_date = date('Y-m-d H:i:s');
        $this->newsletter->save();
        parent::init();
    }

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token): array
    {
        return [
            'title' => $this->newsletter->name,
            'message' => $this->newsletter->text,
        ];
    }
}
