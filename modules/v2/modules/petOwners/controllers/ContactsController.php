<?php


namespace app\modules\v2\modules\petOwners\controllers;

use app\modules\v2\modules\petOwners\models\ContactsModel;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;

/**
 * Class ContactsController
 * @package app\modules\v2\modules\petOwners\controllers
 */
class ContactsController extends BaseController
{
    /**
     * Типы контактов
     *
     * @return array
     */
    public function actionTypes()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ContactsModel())->getContactTypes()
        ];
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
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionCreate($id_contact_type, $entity_type, $entity_id, $name, $main_flag = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $haveContacts = (new ContactsModel())->getPetOwnerContactsList($entity_id);

        if ($haveContacts) {
                $contact = (new ContactsModel())->create(
                    $id_contact_type, $entity_type, $entity_id, $name, $main_flag
                );
        }
        else {

            // id_contact_type 1,2,3 - телефоны

            if (in_array($id_contact_type, [1, 2, 3])) {
                $contact = (new ContactsModel())->create(
                    $id_contact_type, $entity_type, $entity_id, $name, true
                );
            }
            else{
                $contact = (new ContactsModel())->create(
                    $id_contact_type, $entity_type, $entity_id, $name, $main_flag
                );
            }
        }

        return [
            'result' => true,
            'id' => $contact->id,
            'message' => $contact->message . ' (' . $contact->name . ')' ?? null,
        ];
    }

    /**
     * Удаляет указанный контакт
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     *
     */
    public
    function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ContactsModel())->delete($id);
        return [
            'result' => true,
        ];
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
     * @return array
     * @throws BadRequestHttpException
     *
     */
    public
    function actionEdit($id, $id_contact_type, $entity_type, $entity_id, $name, $main_flag = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ContactsModel())->edit(
            $id, $id_contact_type, $entity_type, $entity_id, $name, $main_flag
        );
        return [
            'result' => true,
        ];
    }

    /**
     * Возвращает контакт пользователя
     *
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769311
     * @param $id
     * @return array
     */
    public
    function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ContactsModel())->getContact($id)
        ];
    }

    /**
     * Список всех контактов пользователя
     *
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769423
     * @param int $id_pet_owner
     * @return array
     */
    public
    function actionList(int $id_pet_owner)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ContactsModel())->getPetOwnerContactsList($id_pet_owner)
        ];
    }
}
