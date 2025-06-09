<?php

namespace app\modules\v2\modules\petOwners\controllers;

use app\modules\v2\modules\petOwners\models\PetOwnersModel;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\petOwners\models\PetsDuplicatesModel;
use yii\web\BadRequestHttpException;

/**
 * Class DuplicatesController
 * @package app\modules\v2\modules\petOwners\controllers
 */
class DuplicatesController extends BaseController
{
    /**
     * admin.pet_owners_duplicates_reestr
     */
    public function actionList(
        ?string $date_start = null,
        ?string $date_end = null,
        ?string $name = null,
        ?string $phone = null,
        ?string $address = null,
        ?string $automatic = null,
        int $page = 1, 
        int $limit = 10
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        return (new PetOwnersModel())->duplicatesList($date_start, $date_end, $name, $phone, $address, $automatic, $page, $limit);
    }

    /**
     * admin.pet_owners_duplicates ( mode => search_for_one_entity )
     */
    public function actionSearchForOneEntity(
        ?int $user_id = null,
        string $name,
        array $phone,
        string $address,
        string $address_fact
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!isset($user_id)) { $user_id = \Yii::$app->user->getId(); }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        return (new PetOwnersModel())->duplicatesSearchForOneEntity($user_id, $name, $phone, $address, $address_fact);
    }

    /**
     * admin.pet_owners_duplicates ( mode => search_and_link )
     */
    public function actionSearchAndLink(
        ?int $user_id = null,
        string $date_start,
        string $date_end
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
  
        if (!isset($user_id)) { $user_id = \Yii::$app->user->getId(); }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        
        return (new PetOwnersModel())->duplicatesSearchAndLink($user_id, $date_start, $date_end);
    }

    /**
     * admin.pet_owners_duplicates ( mode => search_for_delete )
     */
    public function actionSearchForDelete(
        ?int $user_id = null,
        string $date_start,
        string $date_end        
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!isset($user_id)) { $user_id = \Yii::$app->user->getId(); }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        
        return (new PetOwnersModel())->duplicatesSearchForDelete($user_id, $date_start, $date_end);
    }

    /**
     * admin.pet_owners_duplicates ( mode => soft_delete ( default ) or hard_delete )
     */
    public function actionDelete(
        ?int $user_id = null,
        array $pet_owners_ids,
        ?string $mode = 'soft_delete'
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!isset($user_id)) { $user_id = \Yii::$app->user->getId(); }
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        
        return (new PetOwnersModel())->duplicatesDelete($user_id, $pet_owners_ids, $mode);
    }

    /**
     * Метод подбора дублирующих записей владельцев (первый шаг объединения дублей)
     * (п.1.3 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916)
     *
     * @param int $id
     * @return array
     */
    public function actionCheck($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetOwnersModel();

        $result = [];
        $owner = $model->getPetOwner($id, true, true, true);

        if ($owner !== null) {
            $result[] = $owner;
            $suggestions = $model->suggestDuplicates(
                $owner['f_fio'],
                $owner['i_fio'],
                $owner['o_fio'],
                $owner['jur_name'],
                $owner['inn'],
                $owner['ogrn'],
                $owner['snils'],
                $owner['is_legal'],
                $owner['entrepreneur'],
                $owner['id'],
                true,
                null,
                false,
                null,
                null,
                $owner['contacts']
            );
            if (!empty($suggestions)) {
                $result = array_merge($result, $suggestions);
            }
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Метод объединения владельцев
     * (п.1.3 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Объединить")
     *
     * @param int   $id_main_owner
     * @param array $ids
     * @return array
     */
    public function actionLink($id_main_owner, $ids = [], array $merge_data = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetOwnersModel();

        if (!$model->linkOwners($id_main_owner, $ids, $merge_data)) {
            $this->errorResponse($model, 'Ошибка при объединении владельцев');
        }

        return [
            'result' => true,
            'id_main_owner' => $id_main_owner,
        ];
    }

    /**
     * Метод открепления владельца-дубля от основного владельца
     * (п.1.6.1 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Открепить")
     *
     * @param int $id ID владельца-дубля
     * @return array
     */
    public function actionUnlink($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetOwnersModel();

        if (!$model->unlinkOwner($id)) {
            $this->errorResponse($model, 'Ошибка при откреплении владельца');
        }

        return [
            'result' => true,
        ];
    }

    /**
     * Метод снятия признака "Основной" у владельца
     * (п.1.6.1.1 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Отменить признак "Основная запись"")
     *
     * @param int $id ID основного владельца
     * @return array
     */
    public function actionUndoMain($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetOwnersModel();

        if (!$model->undoMain($id)) {
            $this->errorResponse($model, 'Ошибка при снятии признака "Основной" у владельца');
        }

        return [
            'result' => true,
        ];
    }

    /**
     * Метод подбора дублирующих записей животных (второй шаг объединения дублей)
     * (п.1.4 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916)
     *
     * @param int $id_main_owner
     * @return array
     */
    public function actionCheckPets($id_main_owner)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetsDuplicatesModel();

        return [
            'result' => $model->check($id_main_owner),
        ];
    }

    /**
     * Метод выбора основного животного
     * (п.1.4.1 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Основная запись")
     *
     * @param int   $id_main_owner
     * @param int   $id_main_pet
     * @return array
     */
    public function actionMainPet($id_main_owner, $id_main_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetsDuplicatesModel();

        if (!$model->makeMain($id_main_owner, $id_main_pet)) {
            $this->errorResponse($model, 'Ошибка при выборе основного животного');
        }

        return [
            'result' => $model->check($id_main_owner),
        ];
    }

    /**
     * Метод объединения животных
     * (п.1.4.2 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Объединить выбранные записи")
     *
     * @param int   $id_main_owner
     * @param int   $id_main_pet
     * @param array $ids
     * @return array
     */
    public function actionLinkPets($id_main_owner, $id_main_pet, $ids = [], array $merge_data = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetsDuplicatesModel();

        if (!$model->linkPets($id_main_owner, $id_main_pet, $ids, $merge_data)) {
            if ($model->hasErrors('json')) {
                $this->errorResponse($model, '', 1);
            }
            $this->errorResponse($model, 'Ошибка при объединении животных');
        }

        return [
            'result' => $model->check($id_main_owner),
        ];
    }

    /**
     * Метод открепления дублирующих записей животных
     * (п.1.4.3 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     *
     * @param int $id_main_owner
     * @param int $id_pet
     * @return array
     */
    public function actionUnlinkPet($id_main_owner, $id_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetsDuplicatesModel();

        if (!$model->unlinkPet($id_main_owner, $id_pet)) {
            $this->errorResponse($model, 'Ошибка при откреплении животного');
        }

        return [
            'result' => $model->check($id_main_owner),
        ];
    }

    /**
     * Метод снятия признака "основная запись" у животного
     * (п.1.4.4 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     *
     * @param int   $id_main_owner
     * @param int   $id_main_pet
     * @return array
     */
    public function actionUndoMainPet($id_main_owner, $id_main_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetsDuplicatesModel();

        if (!$model->undoMakeMain($id_main_owner, $id_main_pet)) {
            $this->errorResponse($model, 'Ошибка при снятии признака основного животного');
        }

        return [
            'result' => $model->check($id_main_owner),
        ];
    }

    /**
     * @param \yii\base\Model $model
     * @param string|null $defaultMessage
     * @param int $code
     * @throws BadRequestHttpException
     */
    protected function errorResponse(\yii\base\Model $model, string $defaultMessage = null, int $code = 0): void
    {
        $defaultMessage = $defaultMessage ?? 'Ошибка';
        $errors = $model->getErrorSummary(true);
        throw new BadRequestHttpException(empty($errors) ? $defaultMessage : implode("\n", array_values($errors)), $code);
    }
}
