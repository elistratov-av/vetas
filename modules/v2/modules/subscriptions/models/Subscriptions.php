<?php

namespace app\modules\v2\modules\subscriptions\models;

use app\common\components\inform\events\ConfirmEmailEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\jobs\CreateSubscriptionJob;
use app\common\components\inform\jobs\UnsubscribeJob;
use app\common\components\inform\SpkService;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\models\db\subscription\SubscriptionConfirm;
use yii\base\Model;
use yii\queue\db\Queue;
use yii\web\BadRequestHttpException;

/**
 * Class Confirm
 * @package app\modules\v2\modules\subscriptions\models
 *
 * @property int $id ID контакта в БД
 */
class Subscriptions extends Model
{
    const
        SCENARIO_CONFIRM = 'confirm',
        SCENARIO_UNSUBSCRIBE = 'unsubscribe',
        SCENARIO_SUBSCRIBE = 'subscribe'
    ;

    /** @var integer */
    public $id;

    /** @var Contacts */
    protected $contact;

    /** @var PetOwners */
    protected $owner;

    public function init()
    {
        parent::init();

        $this->contact = Contacts::findOne(['id' => $this->id]);
        $this->owner = PetOwners::findOne(['id' => $this->contact->entity_id]);
    }

    public function rules()
    {
        return [
            ['id', function($attribute, $params, $validator){
                if (!$this->contact) {
                    $this->addError($attribute, 'Не найден контакт');
                }

                if (!$this->owner) {
                    $this->addError($attribute, 'Не найден пользователь');
                }
            }],
            ['id', function($attribute, $params, $validator){
                if (SubscriptionConfirm::findOne(['id_contact' => $this->id])) {
                    $this->addError($attribute, 'По данному контакту уже отправлено письмо с уведомлением');
                }
            }, 'on' => self::SCENARIO_CONFIRM],
            ['id', function($attribute, $params, $validator){
                if (!$this->contact->confirmed) {
                    $this->addError($attribute, 'Нельзя подписать неподтвержденный контакт');
                }
            }, 'on' => self::SCENARIO_SUBSCRIBE]
        ];
    }

    /**
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function apply()
    {
        $contact = Contacts::findOne(['id' => $this->id]);

        if ($contact->contact_type->type != ContactTypes::TYPE_EMAIL) {
            throw new BadRequestHttpException('Тип контакта не предусматривает подтверждение');
        }
        
        $confirm = new SubscriptionConfirm([
            'id_contact' => $this->id,
            'contact_value' => $contact->name,
            'token' => \Yii::$app->security->generateRandomString()
        ]);
        $confirm->save(false);

        $params = [
            'ownerName' => $this->owner->getNameForInformation(),
            'token' => $confirm->token,
            'email' => $contact->name
        ];

        if (!empty($this->owner->sso_id)) {
            $params['sso_id'] = $this->owner->sso_id;
        }
        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new ConfirmEmailEvent($params));
    }

    /**
     * Отписка контакта
     *
     * @throws \Exception
     */
    public function unsubscribe()
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;
        $queue->push(new UnsubscribeJob([
            'contact_id' => $this->contact->id,
            'contact' => $this->contact->name,
            'type' => $this->contact->getTypeForInformation(),
            'sso_id' => $this->owner->sso_id
        ]));
    }

    /**
     * Подписка контакта
     */
    public function subscribe()
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;
        $contact = Contacts::findOne(['id' => $this->id]);
        $queue->push(new CreateSubscriptionJob([
            'id_contact' => $contact->id,
            'email' => $contact->name,
            'sso_id' => (PetOwners::findOne(['id' => $contact->entity_id]))->sso_id
        ]));
    }

    /**
     * Проверка контактов на подписку
     *
     * @param array $contacts
     * @return array
     * @throws BadRequestHttpException
     */
    public function check(array $contacts)
    {
        $emails = [];
        $phones = [];
        $subscribed = [];
        foreach ($contacts as $contact) {
            switch ($contact['id_contact_type']) {
                case 1:
                    $phones[$contact['id']] = $contact['value'];
                    break;

                case 6:
                    $emails[$contact['id']] = $contact['value'];
                    break;

                default:
                    throw new BadRequestHttpException("Неверный запрос");
            }
        }
        /** @var SpkService $service */
        $spkService = \Yii::$app->spkService;

        if (!empty($emails)) {
            $subscribed = array_merge($subscribed, $spkService->getSubscribedEmails($emails));
        }

        if (!empty($phones)) {
            $subscribed = array_merge($subscribed, $spkService->getSubscribedPhones($emails));
        }

        $result = [];
        foreach ($contacts as $contact) {
            $result[] = [
                'id' => $contact['id'],
                'subscribed' => in_array($contact['value'], $subscribed)
            ];
        }

        return $result;
    }

}
