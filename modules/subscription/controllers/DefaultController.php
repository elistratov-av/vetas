<?php

namespace app\modules\subscription\controllers;

use app\common\components\inform\jobs\CreateSubscriptionJob;
use app\common\components\inform\jobs\UnsubscribeJob;
use app\common\components\inform\SpkService;
use app\common\components\inform\SubscriptionService;
use app\models\db\Contacts;
use app\models\db\PetOwners;
use app\models\db\subscription\SubscriptionConfirm;
use app\models\db\subscription\Subscriptions;
use yii\queue\db\Queue;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 * @package app\modules\subscription\controllers
 */
class DefaultController extends Controller
{
    /**
     * @var string
     */
    public $layout = 'main';

    /**
     * @return SpkService
     */
    protected function getService() : SpkService
    {
        return \Yii::$app->spkService;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return '';
    }

    /**
     * @param $token
     * @throws \Throwable
     */
    public function actionConfirm($token)
    {
        try {
            if (!$token || !$confirm = SubscriptionConfirm::findOne(['token' => $token])) {
                throw new BadRequestHttpException('Неверный токен');
            }

            $service = $this->getService();
            if ((time() - strtotime($confirm->created_at)) > $service->tokenTtl) {
                throw new BadRequestHttpException('Токен устарел');
            }

            $contact = $confirm->contact;
            if ($contact === null) {
                throw new BadRequestHttpException('Контакт не найден');
            }
            if ($contact->name !== $confirm->contact_value) {
                throw new BadRequestHttpException('Контакт устарел');
            }

            \Yii::$app->db->transaction(function() use ($service, $confirm, $contact){
                if (!$contact->confirmed) {
                    $contact->confirmed = true;
                    $contact->save(false);
                }

                \Yii::$app->subscription_queue->push(new CreateSubscriptionJob([
                    'id_contact' => $contact->id,
                    'email' => $contact->name,
                    'sso_id' => (PetOwners::findOne(['id' => $contact->entity_id]))->sso_id
                ]));

                $confirm->delete();
            });

            $this->redirect('/subscription/confirm-success');
        } catch (\Exception $e) {
            $this->redirect('/subscription/confirm-error');
        }
    }

    /**
     * @return string
     */
    public function actionConfirmSuccess()
    {
        return $this->render('page', [
            'title' => 'Ваш email успешно подтвержден!',
            'text' => 'Теперь Вы будете получать рассылку от Комитета ветеринарии города Москвы.
        Чтобы отписаться от рассылки, обратитесь в любую государственную ветеринарную клинику.'
        ]);
    }

    /**
     * @return string
     */
    public function actionConfirmError()
    {
        return $this->render('page', [
            'title' => 'Ссылка устарела!',
            'text' => 'Срок действия ссылки (24 часа) истек.
        Чтобы подписаться на рассылку, обратитесь в любую государственную ветеринарную клинику'
        ]);
    }

    /**
     * @param string $token
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionUnsubscribe($token)
    {
        $data = SubscriptionService::decodeUnsubscribeToken($token);

        if (!isset($data['id_contact']) || !isset($data['value'])) {
            throw new BadRequestHttpException();
        }

        $id_contact = (int)$data['id_contact'];
        $subscription = Subscriptions::findOne(['id_contact' => $id_contact]);
        $contact = Contacts::findOne(['id' => $id_contact]);

        if ($subscription === null || $subscription->subscribed === false || $contact === null) {
            // не будем бросать ошибку, возможно отписались раньше
            // чтобы все были довольны просто редиректим на success
            return $this->redirect(['unsubscribe-success']);
        }

        if ($contact->name != $data['value']) {
            // например, не совпадает email
            throw new BadRequestHttpException();
        }

        try {
            /** @var Queue $queue */
            $queue = \Yii::$app->subscription_queue;
            $queue->push(new UnsubscribeJob([
                'contact_id' => $contact->id,
                'contact' => $contact->name,
                'type' => $contact->getTypeForInformation(),
                'sso_id' => isset($data['sso_id']) ? $data['sso_id'] : null
            ]));
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if ($message == 'entity not found') {
                // ошибку возвращает СПК - подписка не найдена на их стороне - просто отпишем у себя и вернем успех
                $subscription->subscribed = false;
                $subscription->save();
            } else {
                \Yii::error($message, 'subscription_queue');

                return $this->redirect(['unsubscribe-error']);
            }
        }

        return $this->redirect(['unsubscribe-success']);
    }

    /**
     * @return string
     */
    public function actionUnsubscribeSuccess()
    {
        return $this->render('page', [
            'title' => 'Вы успешно отписались от рассылки!',
            'text' => 'Теперь Вы не будете получать рассылку от Комитета ветеринарии города Москвы.
        Чтобы возобновить подписку, обратитесь в любую государственную ветеринарную клинику. '
        ]);
    }

    /**
     * @return string
     */
    public function actionUnsubscribeError()
    {
        return $this->render('page', [
            'title' => 'Ошибка',
            'text' => 'Произошла ошибка при изменении подписки на рассылку от Комитета ветеринарии города Москвы'
        ]);
    }

    /**
     * @return string
     */
    public function actionError()
    {
        if (($exception = \Yii::$app->getErrorHandler()->exception) === null) {
            $exception = new NotFoundHttpException('Страница не найдена');
        }

        return $this->render('page', [
            'title' => $exception->getName(),
            'text' => $exception->getMessage()
        ]);
    }
}
