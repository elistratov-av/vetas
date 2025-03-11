<?php

namespace app\modules\v2\modules\subscriptions\controllers;

use app\models\db\Contacts;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\subscriptions\models\Subscriptions;
use yii\web\BadRequestHttpException;

class ContactsController extends BaseController
{
    /**
     * Подтверждение контактов
     *
     * @param array $contacts
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function actionConfirm(array $contacts)
    {
        if (empty($contacts)) {
            throw new BadRequestHttpException("Неверный запрос");
        }
        foreach ($contacts as $contact) {
            if (!isset($contact['id'])) {
                throw new BadRequestHttpException("Неверный запрос");
            }

            $subscriptions = new Subscriptions([
                'id' => $contact['id'],
                'scenario' => Subscriptions::SCENARIO_CONFIRM
            ]);
            if (!$subscriptions->validate()) {
                throw new BadRequestHttpException($subscriptions->getFirstError('id'));
            }
            $subscriptions->apply();
        }

        return [
            'result' => true
        ];
    }

    /**
     * Проверка подписок контактов
     *
     * @param array $contacts
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function actionCheck(array $contacts)
    {
        if (empty($contacts)) {
            throw new BadRequestHttpException("Неверный запрос");
        }
        $ids = [];
        foreach ($contacts as $contact) {
            if (!isset($contact['id'])) {
                throw new BadRequestHttpException("Неверный запрос");
            }

            $ids[] = $contact['id'];
        }

        $ids = array_unique($ids);
        $contacts = Contacts::find()
            ->select([
                'contacts.id',
                'contacts.id_contact_type', // 1 - телефон, 6 - email
                'value' => 'contacts.name',
                'pet_owners.sso_id'
            ])
            ->innerJoin('pet_owners', 'entity_id = pet_owners.id')
            ->where(['contacts.id' => $ids])
            ->asArray()
            ->indexBy('id')
            ->all();

        if (count($contacts) != count($ids)) {
            throw new BadRequestHttpException("Неверный запрос");
        }

        $subscriptions = new Subscriptions();
        return [
            'result' => [
                'contacts' => $subscriptions->check($contacts)
            ]
        ];
    }

    /**
     * @param array $contacts
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function actionSubscribe(array $contacts)
    {
        if (empty($contacts)) {
            throw new BadRequestHttpException("Неверный запрос");
        }
        foreach ($contacts as $contact) {
            if (!isset($contact['id'])) {
                throw new BadRequestHttpException("Неверный запрос");
            }

            $subscriptions = new Subscriptions([
                'id' => $contact['id'],
                'scenario' => Subscriptions::SCENARIO_SUBSCRIBE
            ]);
            if (!$subscriptions->validate()) {
                throw new BadRequestHttpException($subscriptions->getFirstError('id'));
            }
            $subscriptions->subscribe();
        }

        return [
            'result' => true
        ];
    }

    /**
     * @param array $contacts
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function actionUnsubscribe(array $contacts)
    {
        if (empty($contacts)) {
            throw new BadRequestHttpException("Неверный запрос");
        }
        foreach ($contacts as $contact) {
            if (!isset($contact['id'])) {
                throw new BadRequestHttpException("Неверный запрос");
            }

            $subscriptions = new Subscriptions([
                'id' => $contact['id']
            ]);
            if (!$subscriptions->validate()) {
                throw new BadRequestHttpException($subscriptions->getFirstError('id'));
            }
            $subscriptions->unsubscribe();
        }

        return [
            'result' => true
        ];
    }
}
