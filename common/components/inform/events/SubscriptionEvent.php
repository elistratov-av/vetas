<?php

namespace app\common\components\inform\events;

use app\common\components\inform\jobs\SendEventJob;
use app\common\components\inform\SpkService;
use app\common\components\inform\SubscriptionService;
use app\models\db\Contacts;
use Ramsey\Uuid\Uuid;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\queue\db\Queue;
use yii\queue\JobInterface;

/**
 * Class SubscriptionEvent
 * @package app\common\components\inform\events
 *
 * @property string $sso_id
 * @property Contacts[] $contacts
 */
abstract class SubscriptionEvent extends Event implements SubscriptionEventInterface
{
    /** @var Contacts[] */
    public $contacts;

    /** @var null|string  */
    public $sso_id = null;

    /** @var integer[] */
    public $attached_files_ids = null;

    /** @var null|string */
    public $files_token = null;

    /** @var  null|integer */
    public $id_pet = null;

    /** @var  null|integer[] */
    public $pets = [];

    /** @var  null|integer */
    public $id_visit = null;

    /** @var null|int[] */
    public $notification_history = null;

    /** @var bool */
    public $isAdminNotification = false;

    /** @var string|null */
    protected $email = null;

    /**
     * @param $token
     * @return array
     */
    abstract public function getEventData($token) : array;

    /**
     * @param $channel
     * @param $contact
     * @return array
     */
    public function getRecipient($channel, $contact) : array
    {
        if ($channel == SpkService::CHANNEL_MSISDN) {
            $contact = trim($contact, '+');
        }

        $result[$channel] = $contact;

        return $result;
    }

    /**
     * Инициатор отправки.
     * @return int|null
     * @throws \Throwable
     */
    public function getInitiatorId() {
        try {
            /** @var $user \app\common\models\UserModel */
            $user = \Yii::$app->user->getIdentity();
            if (empty($user)){
                return null;
            }
            return $user->id;
        }
        catch (\Exception $exception){
            return null;
        }
    }

    /**
     * @return SpkService
     */
    protected function getService() : SpkService
    {
        return \Yii::$app->spkService;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getEventId() : string
    {
        return Uuid::uuid4();
    }

    /**
     * @return int
     */
    public function getDateTime() : int
    {
        return time();
    }

    /**
     * @param $token
     * @return string
     */
    public function getSubscribeLink($token) : string
    {
        return sprintf($this->getService()->linkToSubscribe, $token);
    }

    /**
     * @param $token
     * @return string
     */
    public function getUnsubscribeLink($token) : string
    {
        return sprintf($this->getService()->linkToUnsubscribe, $token);
    }

    /**
     * @param Contacts $contact
     * @return string
     */
    public function generateToken($contact)
    {
        return SubscriptionService::encodeUnsubscribeToken($contact->id, $contact->name, $this->sso_id);
    }

    /**
     * @param $channel
     * @param Contacts $contact
     * @param string $token
     * @return JobInterface
     * @throws \Exception
     */
    public function getJob($channel, $contact, $token = '') : JobInterface
    {
        return new SendEventJob([
            'event_id' => $this->getEventId(),
            'event_code' => static::EVENT_CODE,
            'date_time' => $this->getDateTime(),
            'to' => $this->getRecipient($channel, $contact->name),
            'data' => $this->getEventData($token),
            // Исключительно для ивентов нарушений, передаём для последующего логирования id нарушения и автора нарушения
            'id_violation' => $this->violation->id_violation ?? null,
            'id_author' => $this->id_author ?? null,
            // Привязка оповещения к истории нарушения
            'id_violation_history' => $this->getViolationHistoryByContact($contact),
            // Для последущей привязки загруженных файлов к логу события
            'attached_files_ids' => $this->attached_files_ids,
            'files_token' => $this->files_token,
            //VETAIS-3399
            'id_owner' => $contact->entity_id,
            'id_initiator'=> $this->getInitiatorId(),
            'id_pet' => $this->id_pet ?? null,
            'id_visit' => $this->id_visit ?? null,
            //Животные, по которым уведомляли
            'pets' => $this->pets ?? [],
            'is_admin_notification' => $this->isAdminNotification,
        ]);
    }

    /**
     * @return SendEventJob
     * @throws \Exception
     */
    public function getAdminJob()
    {
        return new SendEventJob([
            'event_id' => $this->getEventId(),
            'event_code' => static::EVENT_CODE,
            'date_time' => $this->getDateTime(),
            'to' => $this->getRecipient(SpkService::CHANNEL_EMAIL, $this->email),
            'data' => $this->getEventData(),
            'is_admin_notification' => $this->isAdminNotification,
        ]);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function makeQueueJobs() : void
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;

        if ($this->isAdminNotification) {
            if (!$this->email) {
                throw new InvalidArgumentException('Оповещения типа isAdminNotification требуют email');
            }
            $queue->push($this->getAdminJob());
        } else {
            foreach ($this->contacts as $contact) {
                $queue->push($this->getJob($contact->getTypeForInformation(), $contact, $this->generateToken($contact)));
            }
        }
    }

    /**
     * @param Contacts $contact
     * @return int|null
     */
    public function getViolationHistoryByContact(Contacts $contact) {
        if (isset($this->notification_history)) {
            return $this->notification_history[$contact->id];
        }
        return null;
    }
}
