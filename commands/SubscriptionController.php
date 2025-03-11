<?php

namespace app\commands;

use app\common\components\inform\events\AnimalFoundEvent;
use app\common\components\inform\events\CancelVisitEvent;
use app\common\components\inform\events\ConfirmEmailEvent;
use app\common\components\inform\events\InitialIdentificationAndVaccinationEvent;
use app\common\components\inform\events\InitialIdentificationEvent;
use app\common\components\inform\events\InitialVaccinationEvent;
use app\common\components\inform\events\QuarantineEvent;
use app\common\components\inform\events\ReadyResearchEvent;
use app\common\components\inform\events\RemindIdentificationEvent;
use app\common\components\inform\events\RemindVaccinationEvent;
use app\common\components\inform\events\RemindVaccinationLeptoEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\events\TransferVisitEvent;
use app\common\components\inform\events\ViolationEvent;
use app\common\components\inform\events\VisitToTransferEvent;
use app\common\components\inform\jobs\CreatePushSubscriptionJob;
use app\common\components\inform\jobs\CreateSubscriptionJob;
use app\common\components\inform\jobs\RefreshSubscriptionsJob;
use app\common\components\inform\jobs\UnsubscribeJob;
use app\common\components\inform\SpkService;
use app\common\components\inform\SubscriptionService;
use app\common\components\rbac\Role;
use app\models\db\Contacts;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Specialists;
use app\models\db\Violation;
use app\models\db\Visits;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\queue\db\Queue;
use yii\web\BadRequestHttpException;

/**
 * Команды для работы с подписками
 * Class SubscriptionController
 * @package app\commands
 */
class SubscriptionController extends Controller
{
    /**
     * @return SpkService
     */
    protected function getSpkService()
    {
        return \Yii::$app->spkService;
    }

    /**
     * @return Queue
     */
    protected function getQueue(): Queue
    {
        return \Yii::$app->subscription_queue;
    }

    /**
     * @param $id
     * @return bool|null|Contacts
     */
    protected function getContact($id)
    {
        if (!$contact = Contacts::findOne(['id' => $id])) {
            Console::output(Console::ansiFormat("Не найден контакт #{$id}", [Console::FG_RED, Console::BOLD]) . PHP_EOL);

            return false;
        }

        if ($contact->id_contact_type != 6) { //не почта
            Console::output(Console::ansiFormat("Необходимо указать ID контакта с почтой",
                    [Console::FG_RED, Console::BOLD]) . PHP_EOL);

            return false;
        }

        return $contact;
    }

    /**
     * Удаление устаревших токенов для подтверждения контакта
     */
    public function actionClearOldConfirms()
    {
        $this->getSpkService()->clearOldConfirms();
    }

    /**
     * Отправка события на получение письма с подпиской
     * @param string $email
     */
    public function actionSendConfirmEmailEvent(string $email)
    {
        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new ConfirmEmailEvent([
            'ownerName' => "Ивано П.А.",
            'token' => time(),
            'email' => $email,
        ]));
    }

    /**
     * Отправка события "Найдено животное" (Необходимо указать ID контакта из БД)
     * @param int $id ID контакта в БД
     * @return int
     */
    public function actionSendAnimalFoundEvent($id)
    {
        if (!$pet = Pets::findOne(['id' => $id])) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new AnimalFoundEvent([
            'phone' => '111',
            'address' => 'asdasd',
            'petMicrochip' => 'чип',
            'pet' => $pet,
            'id_pet' => $pet->id,
            'contacts' => SubscriptionService::getOwnerSubscriptions($pet->owner),
        ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Отмена визита" (Необходимо указать ID контакта из БД)
     * @param $id
     * @return int
     * @throws \Exception
     */
    public function actionSendCancelVisitEvent($id)
    {
        if (!$visit = Visits::findOne(['id' => $id])) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($visit->owner);
        if (!empty($contacts)) {
            \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new CancelVisitEvent([
                'visit' => $visit,
                'contacts' => $contacts,
                'id_visit' => $visit->id
            ]));
        }

        return ExitCode::OK;
    }

    /**
     * Отправка события "Перенос визита" (Необходимо указать ID контакта из БД)
     * @param $id
     * @return int
     * @throws \Exception
     */
    public function actionSendTransferVisitEvent($id)
    {
        /*
        if (!$contact = $this->getContact($id)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        */

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new TransferVisitEvent([
            'ownerName' => "Ивано П.А.",
            'token' => SubscriptionService::encodeUnsubscribeToken(1, 1, 'e1e417f9-6e9a-4bba-b761-44296bb331c1'),
            'msisdn' => '+78800300906',
            'phone' => '2112',
            'sso_id' => 'e1e417f9-6e9a-4bba-b761-44296bb331c1',
            'address' => 'Москва',
            'petName' => 'Пушок',
            'specialist' => 'Доктор Врач',
            'date' => new \DateTime('2019-10-10'),
        ]));

        return ExitCode::OK;
    }


    /**
     * Отправка события "Карантин" (Необходимо указать ID контакта из БД)
     * @param $id
     * @return int
     */
    public function actionSendQuarantineEvent($id)
    {
        if (!$contact = $this->getContact($id)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new QuarantineEvent([
            'ownerName' => "Ивано П.А.",
            'token' => SubscriptionService::encodeUnsubscribeToken($contact->id, $contact->name),
            'email' => $contact->name,
            'area' => 'район карантина',
            'startDate' => 'В понедельник',
        ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Уведомления о готовности результатов исследований" (Необходимо указать ID контакта из БД)
     * @param $id
     * @return int
     */
    public function actionSendReadyResearchEvent($id)
    {
        if (!$visit = Visits::findOne(['id' => $id])) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new ReadyResearchEvent([
            'researchTypes' => 'Анализ крови',
            'visit' => $visit,
            'contacts' => SubscriptionService::getOwnerSubscriptions($visit->owner),
            'id_visit' => $visit->id
        ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Напоминание об идентификации" (Необходимо указать ID контакта из БД)
     * @param $id
     * @return int
     */
    public function actionSendRemindIdentificationEvent($id)
    {
        if (!$contact = $this->getContact($id)) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new RemindIdentificationEvent([
            'ownerName' => "Ивано П.А.",
            'token' => SubscriptionService::encodeUnsubscribeToken($contact->id, $contact->name),
            'email' => $contact->name,
            'petName' => 'Пушок',
        ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Нарушение" (Необходимо указать ID нарушения из БД)
     * @param $id
     * @return int
     */
    public function actionSendViolationEvent($id)
    {
        if (!$violation = Violation::find()->where(['id_violation' => $id])->one()) {
            Console::output(Console::ansiFormat("Нет найдено нарушение #$id", [Console::FG_RED, Console::BOLD]) . PHP_EOL);
            return false;
        }
        $contacts = SubscriptionService::getOwnerSubscriptions($violation->owner);
        if (empty($contacts)) {
            Console::output(Console::ansiFormat('У пользователя нет подписок на уведомление', [Console::FG_RED, Console::BOLD]) . PHP_EOL);
            return false;
        }

        \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new ViolationEvent([
            'violation' => $violation,
            'contacts' => $contacts,
            'id_pet' => $violation->id_pet,
            'id_visit' => $violation->id_visit,
            'text' => 'Очень страшное нарушение',
        ]));

        return ExitCode::OK;
    }

    /**
     * Создание подписки
     * @param string $email
     * @param int    $id_contact
     * @param string $sso_id
     */
    public function actionSubscribe($email, $id_contact, $sso_id = null)
    {
        $this->getQueue()->push(new CreateSubscriptionJob([
            'email' => $email,
            'id_contact' => $id_contact,
            'sso_id' => $sso_id,
        ]));
    }

    /**
     * Создание подписки
     * @param string $phone
     * @param int    $id_contact
     * @param string $sso_id
     */
    public function actionSubscribePhone($phone, $id_contact, $sso_id = null)
    {
        $this->getQueue()->push(new CreatePushSubscriptionJob([
            'phone' => $phone,
            'id_contact' => $id_contact,
            'sso_id' => $sso_id,
        ]));
    }

    /**
     * Удаление подписки (необходимо указать ID подписки на стороне ИС ПК)
     * @param integer $id ID подписки на стороне ИС ПК
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $this->getSpkService()->deleteSubscription($id);
    }

    /**
     * @param $id
     */
    public function actionUnsubscribe($id)
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;
        $queue->push(new UnsubscribeJob([
            'contact_id' => 1,
            'sso_id' => '',
        ]));
    }

    /**
     * Получение списка подписок
     * @param int $toFile
     * @param int $asArray
     * @return int
     * @throws \app\common\components\inform\InformException
     */
    public function actionGet($toFile = null, $asArray = null)
    {
        $result = $this->getSpkService()->getSubscriptions();
        if ($asArray == 1) {
            $result = Json::decode(Json::encode($result));
        }

        if ($toFile == 1) {
            file_put_contents(\Yii::getAlias('@runtime') . '/logs/subscriptions-' . time() . '.txt', var_export($result, true), FILE_TEXT);
        } else {
            print_r($this->getSpkService()->getSubscriptions());
        }

        return ExitCode::OK;
    }

    /**
     * @param string $email
     * @return int
     */
    public function actionCheckIsEmailSubscribed($email)
    {
        var_dump($this->getSpkService()->checkIsEmailSubscribed($email));

        return ExitCode::OK;
    }

    /**
     * @param string $phone
     * @return int
     */
    public function actionCheckIsPhoneSubscribed($phone)
    {
        var_dump($this->getSpkService()->checkIsPhoneSubscribed($phone));

        return ExitCode::OK;
    }

    public function actionRefresh($id, $sso_id)
    {
        $this->getQueue()->push(new RefreshSubscriptionsJob([
            'id_owner' => $id,
            'sso_id' => $sso_id,
        ]));
    }

    /**
     * Отправка события "Напоминание о вакцинации"
     * @param int $id_owner
     * @param int $id_pet
     * @return int
     */
    public function actionSendRemindVaccinationEvent($id_owner, $id_pet)
    {
        $owner = $this->findOwner($id_owner);
        if (empty($owner)) {
            $this->stderr('Пользователь не найден');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $pet = $this->findPet($id_pet);
        if (empty($pet)) {
            $this->stderr('Животное не найдено');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($owner);
        if (empty($contacts)) {
            $this->stderr('У пользователя нет подписок на уведомление');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(
            SubscriptionEventInterface::EVENT_NAME,
            new RemindVaccinationEvent([
                'owner' => $owner,
                'pet' => $pet,
                'contacts' => $contacts,
                'id_pet' => $id_pet,
            ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Напоминание о вакцинации от лептоспироза"
     * @param int $id_owner
     * @param int $id_pet
     * @return int
     */
    public function actionSendRemindVaccinationLeptoEvent($id_owner, $id_pet)
    {
        $owner = $this->findOwner($id_owner);
        if (empty($owner)) {
            $this->stderr('Пользователь не найден');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $pet = $this->findPet($id_pet);
        if (empty($pet)) {
            $this->stderr('Животное не найдено');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($owner);
        if (empty($contacts)) {
            $this->stderr('У пользователя нет подписок на уведомление');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(
            SubscriptionEventInterface::EVENT_NAME,
            new RemindVaccinationLeptoEvent([
                'owner' => $owner,
                'pet' => $pet,
                'contacts' => $contacts,
                'id_pet'=> $pet->id
            ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Напоминание о вакцинации от лептоспироза"
     * @param int $id_owner
     * @param int $id_pet
     * @return int
     */
    public function actionSendInitialIdentificationAndVaccinationEvent($id_owner, $id_pet)
    {
        $owner = $this->findOwner($id_owner);
        if (empty($owner)) {
            $this->stderr('Пользователь не найден');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $pet = $this->findPet($id_pet);
        if (empty($pet)) {
            $this->stderr('Животное не найдено');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($owner);
        if (empty($contacts)) {
            $this->stderr('У пользователя нет подписок на уведомление');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(
            SubscriptionEventInterface::EVENT_NAME,
            new InitialIdentificationAndVaccinationEvent([
                'owner' => $owner,
                'pet' => $pet,
                'contacts' => $contacts,
                'id_pet' => $pet->id
            ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Напоминание о вакцинации от лептоспироза"
     * @param int $id_owner
     * @param int $id_pet
     * @return int
     */
    public function actionSendInitialIdentificationEvent($id_owner, $id_pet)
    {
        $owner = $this->findOwner($id_owner);
        if (empty($owner)) {
            $this->stderr('Пользователь не найден');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $pet = $this->findPet($id_pet);
        if (empty($pet)) {
            $this->stderr('Животное не найдено');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($owner);
        if (empty($contacts)) {
            $this->stderr('У пользователя нет подписок на уведомление');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(
            SubscriptionEventInterface::EVENT_NAME,
            new InitialIdentificationEvent([
                'owner' => $owner,
                'pet' => $pet,
                'contacts' => $contacts,
                'id_pet' => $pet->id
            ]));

        return ExitCode::OK;
    }

    /**
     * Отправка события "Напоминание о вакцинации от лептоспироза"
     * @param int $id_owner
     * @param int $id_pet
     * @return int
     */
    public function actionSendInitialVaccinationEvent($id_owner, $id_pet)
    {
        $owner = $this->findOwner($id_owner);
        if (empty($owner)) {
            $this->stderr('Пользователь не найден');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $pet = $this->findPet($id_pet);
        if (empty($pet)) {
            $this->stderr('Животное не найдено');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $contacts = SubscriptionService::getOwnerSubscriptions($owner);
        if (empty($contacts)) {
            $this->stderr('У пользователя нет подписок на уведомление');

            return ExitCode::UNSPECIFIED_ERROR;
        }

        \Yii::$app->trigger(
            SubscriptionEventInterface::EVENT_NAME,
            new InitialVaccinationEvent([
                'owner' => $owner,
                'pet' => $pet,
                'contacts' => $contacts,
                'id_pet' => $pet->id
            ]));

        return ExitCode::OK;
    }

    public function actionSendTransferredEvent($id_visit)
    {
        /** @var Visits $visit */
        if (!$visit = Visits::find()->where(['id' => $id_visit])->one()) {
            $this->stderr("Не найден осмотр с id: $id_visit \n");

            return ExitCode::UNSPECIFIED_ERROR;
        }
        $role = Role::ROLE_SYSADMIN_GOS;

        $specialists = Specialists::find()
            ->select(['specialists.id id', 'u.email', 'aa.item_name role_name', 'u.id user_id'])
            ->innerJoin('auth_assignment aa', "aa.id_specialist = specialists.id AND aa.item_name = '$role'")
            ->innerJoin('users u', 'u.id = specialists.id_user AND u.email IS NOT NULL')
            ->where([
                'AND',
                ['=', 'specialists.id_organization', $visit->organization->id],
                [
                    'OR',
                    ['>=', 'specialists.expel_date', (new \DateTime())->format('Y-m-d')],
                    new Expression('specialists.expel_date is null')
                ]
            ])
            ->asArray()->all()
        ;

        $count = count($specialists);
        if ($count === 0) {
            $this->stderr("Не найден специалист для уведомления \n");

            return ExitCode::UNSPECIFIED_ERROR;
        } else {
            $this->stderr("Найдено $count специалистов для уведомления");
        }

        foreach($specialists as $specialist) {
            \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new VisitToTransferEvent([
                'count' => 1,
                'id_visit' => $id_visit,
                'email' => $specialist['email'],
            ]));
            $this->stderr("Уведомления отправлены \n");
        }

        return ExitCode::OK;
    }

    /**
     * @param int $id_owner
     * @param int $id_pet
     * @return int
     */
    public function actionBatchSend($id_owner, $id_pet)
    {
        $this->runAction('send-remind-vaccination-event', [$id_owner, $id_pet]);
        $this->runAction('send-remind-vaccination-lepto-event', [$id_owner, $id_pet]);
        $this->runAction('send-initial-identification-and-vaccination-event', [$id_owner, $id_pet]);
        $this->runAction('send-initial-identification-event', [$id_owner, $id_pet]);
        $this->runAction('send-initial-vaccination-event', [$id_owner, $id_pet]);

        return ExitCode::OK;
    }

    /**
     * @param int $id
     * @return \app\models\db\PetOwners|null
     */
    private function findOwner($id)
    {
        return PetOwners::findOne(['id' => $id]);
    }

    /**
     * @param int $id
     * @return \app\models\db\Pets|null
     */
    private function findPet($id)
    {
        return Pets::findOne(['id' => $id]);
    }
}
