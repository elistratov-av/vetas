<?php


namespace app\modules\v2\modules\petOwners\models;

use app\common\components\inform\jobs\UnsubscribeJob;
use app\common\components\inform\SpkService;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use yii\db\Expression;
use yii\queue\db\Queue;
use yii\web\BadRequestHttpException;

class ContactsModel
{

    /**
     * Возвращает список типов контактов
     * (!!! только для владельца/представителя !!!)
     *
     * * @return ContactTypes[]
     */
    public function getContactTypes()
    {
        return ContactTypes::find()
            ->where(['entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER])
            ->all();
    }

    /**
     * Возвращает указанный контакт
     *
     * @param $id
     * @return array|null
     */
    public function getContact($id)
    {
        return $this->getContactQueryById($id)
            ->with('contact_type')
            ->asArray()
            ->one();
    }

    /**
     * Создать контакт
     *
     * @param $id_contact_type
     * @param $entity_type
     * @param $entity_id
     * @param $name
     * @param $main_flag
     *
     * @return Contacts
     * @throws BadRequestHttpException
     */
    public function create($id_contact_type, $entity_type, $entity_id, $name, $main_flag)
    {
        $contact = new Contacts([
            'id_contact_type' => $id_contact_type,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'name' => $name,
            'main_flag' => $main_flag,
        ]);

        $contact->message = $this->validateContact($contact) ?? null;

        if (!$contact->save()) {
            $errors = $contact->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании контакта' : implode("\n", array_values($errors)));
        }

        return $contact;
    }

    /**
     * Редактирование контакта
     *
     * @param $id
     * @param $id_contact_type
     * @param $entity_type
     * @param $entity_id
     * @param $name
     * @param $main_flag
     *
     * @return Contacts
     * @throws BadRequestHttpException
     */
    public function edit($id, $id_contact_type, $entity_type, $entity_id, $name, $main_flag)
    {
        /** @var Contacts $contact */
        $contact = $this->getContactQueryById($id)->one();

        if (empty($contact)) {
            throw new BadRequestHttpException('Указанный контакт не найден');
        }

        # VETAIS-2231 У подтвержденного контакта можно изменить только признак "Основной"
        if (!$contact->confirmed) {
            $contact->id_contact_type = $id_contact_type;
            $contact->entity_type = $entity_type;
            $contact->entity_id = $entity_id;
            $contact->name = $name;
        }
        $contact->main_flag = $main_flag;

        $this->validateContact($contact);

        if (!$contact->save()) {
            $errors = $contact->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении контакта' : implode("\n", array_values($errors)));
        }

        return $contact;
    }

    /**
     * Удаление контакта
     *
     * @param $id
     * @throws BadRequestHttpException|\Exception|\Throwable
     */
    public function delete($id)
    {
        /** @var Contacts $contact */
        $contact = $this->getContactQueryById($id)->one();

        if (empty($contact)) {
            throw new BadRequestHttpException('Указанный контакт не найден');
        }

        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;
        $queue->push(new UnsubscribeJob([
            'contact_id' => $contact->id,
            'contact' => $contact->name,
            'type' => $contact->getTypeForInformation(),
            'delete' => true,
            'sso_id' => (PetOwners::findOne(['id' => $contact->entity_id]))->sso_id
        ]));

        if ($contact->delete() === FALSE) {
            throw new BadRequestHttpException('Неизвестная ошибка при удалении контакта');
        }
    }

    /**
     * Возвращает список контактов владельца/представителя
     *
     * @param $id_pet_owner
     * @return array
     * @throws \app\common\components\inform\InformException
     */
    public function getPetOwnerContactsList($id_pet_owner)
    {
        $result = Contacts::find()
            ->select('*')
            ->addSelect([
                'subscribed' => new Expression('(SELECT EXISTS (SELECT 1 FROM subscription.subscriptions WHERE id_contact = contacts.id AND subscribed = true))')
            ])
            ->with('contact_type')
            ->where([
                'AND',
                ['contacts.entity_type' => Contacts::ENTITY_TYPE_PET_OWNER],
                ['entity_id' => $id_pet_owner]
            ])
            ->asArray()
            ->all();

        /**
         * TODO: подумать как изменить
         */
        $sso_id = (PetOwners::findOne(['id' => $id_pet_owner]))->sso_id;
        try {
            /** @var SpkService $spkService */
            $spkService = \Yii::$app->spkService;
            foreach ($result as &$contact) {
                switch ($contact['contact_type']['type']) {
                    case ContactTypes::TYPE_EMAIL:
                        $contact['subscribed'] = $spkService->checkIsEmailSubscribed($contact['name'], $sso_id);
                        break;

                    case ContactTypes::TYPE_PHONE:
                        $contact['subscribed'] = $spkService->checkIsPhoneSubscribed($contact['name'], $sso_id);
                        // т.к. телефоны мы не подтверждаем и не знаем в какой момент подпишется/отпишется
                        // считаем что если подписан - то подтвержден
                        // VETAIS-2477 - контакты, которые пришли как подтвержденные, так и покажем
                        // если неподтвержденный контакт подписан - считаем подтвержденным и обновим контакт
                        // если подтвежденный не подписан - пока ничего не будем делать
                        if ($contact['subscribed'] === true && $contact['confirmed'] === false) {
                            $this->updateContactIsConfirmed($contact['id'], true);
                            $contact['confirmed'] = true;
                        }
                        break;
                }
            }
        } catch (\Exception $e) {

        }

        return $result;
    }

    /**
     * Возвращает запрос на получение контакта пользователя
     *
     * @param $id int ID КОНТАКТА
     * @return \yii\db\ActiveQuery
     */
    protected function getContactQueryById($id)
    {
        return Contacts::find()
            ->where([
                'AND',
                ['contacts.entity_type' => Contacts::ENTITY_TYPE_PET_OWNER],
                ['id' => $id]
            ]);
    }

    /**
     * @param $contact Contacts
     * @throws BadRequestHttpException
     * @todo Может переделать на валидацию по сценарию?
     */
    protected function validateContact($contact)
    {
        if ($contact->entity_type !== Contacts::ENTITY_TYPE_PET_OWNER) {
            throw new BadRequestHttpException('Wrong entity_type');
        }

        $contact_type = ContactTypes::findOne([
            'id' => $contact->id_contact_type,
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER
        ]);

        if (empty($contact_type)) {
            throw new BadRequestHttpException('Wrong id_contact_type');
        }

        $owner = PetOwners::findOne([
            'id' => $contact->entity_id
        ]);

        if (empty($owner)) {
            throw new BadRequestHttpException('Wrong entity_id');
        }

        if ($contact_type->type == ContactTypes::TYPE_EMAIL || $contact_type->type == ContactTypes::TYPE_PHONE) {
            // VETAIS-2240 проверка на уникальность для владельцев животных
            $query = Contacts::find()
                ->where([
                    'entity_type' => Contacts::ENTITY_TYPE_PET_OWNER,
                ]);

            if ($contact_type->type == ContactTypes::TYPE_PHONE) {
                // Для телефонов проверка уникальности вне зависимости от типа контакта
                // – уникальность по name без contact_type.
                $query->andWhere(['name' => $contact->name]);
            } elseif ($contact_type->type == ContactTypes::TYPE_EMAIL) {
                // Для email’ов проверка должна быть регистронезависимая.
                $query->andWhere(['id_contact_type' => $contact->id_contact_type])
                    ->andWhere(['ilike', 'name', $contact->name, false]);
            }

            if ($contact->id !== null) {
                $query->andWhere(['!=', 'id', $contact->id]);
            }

            if ($query->exists()) {
                return $message = ('Указанный ' . ($contact_type->type == ContactTypes::TYPE_EMAIL ? 'email' : 'телефон') . ' уже существует в Системе');
            }
        }
    }

    /**
     * @param int $id
     * @param bool $value
     * @throws \yii\db\Exception
     */
    private function updateContactIsConfirmed($id, $value)
    {
        \Yii::$app->db
            ->createCommand()
            ->update(
                Contacts::tableName(),
                [
                    'confirmed' => $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => \Yii::$app->user->getId(),
                ],
                ['id' => $id]
            )->execute();
    }
}
