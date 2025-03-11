<?php


namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\pets\models\PetOwnersHistoryModel;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;

/**
 * СВЯЗЬ ВЛАДЕЛЕЦ/ПРЕДСТВИТЕЛЬ с ЖИВОТНЫМ
 *
 * @package app\modules\v2\modules\pets\controllers
 * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769658
 */
class OwnerController extends BaseController
{
    /**
     * Создает связь животное-владелец/представитель
     *
     * @param int $id_pet
     * @param int $id_owner
     * @param int $id_owner_type
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreate($id_pet, $id_owner, $id_owner_type)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pets_to_owner = (new PetToOwnerModel())->create($id_pet, $id_owner, $id_owner_type);

        return [
            'result' => true,
            'id' => $pets_to_owner->id,
        ];
    }

    /**
     * Создает связь животное-владелец/представитель и связывает с приемом 
     *
     * @param int $id_pet
     * @param int $id_owner
     * @param bool $is_owner
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionAddToVisit($id_pet, $id_owner, $is_owner, $id_visit, $id_tmp_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pets_to_owner = (new PetToOwnerModel())->addToVisit($id_pet, $id_owner, $is_owner, $id_visit, $id_tmp_pet);

        return [
            'result' => true,
            'id' => $pets_to_owner['id'],
        ];
    }

    /**
     * Создает животное и связывает его с владельцем и приемом
     *
     * @param int $id_species
     * @param string $sex
     * @param string $name
     * @param int $id_owner
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreatePetForVisit($id_species, $sex, $name, $id_owner, $id_visit, $id_tmp_pet){
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pets_to_owner = (new PetToOwnerModel())->createPetForVisit($id_species, $sex, $name, $id_owner, $id_visit, $id_tmp_pet);

        return [
            'result' => true,
            'id' => $pets_to_owner->id,
        ];
    }

    /**
     * Создает владельца и связывает его с животным и приемом
     *
     * @param int $id_pet
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $phone
     * @param string $email
     * @param string $fias_address
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreateOwnerForVisit($id_pet, $f_fio, $i_fio, $o_fio="", $phone, $fias_address, $id_visit, $id_tmp_pet) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pets_to_owner = (new PetToOwnerModel())->createOwnerForVisit($id_pet, $f_fio, $i_fio, $o_fio, $phone, "", $fias_address, $id_visit, $id_tmp_pet, false);

        return [
            'result' => true,
            'id' => $pets_to_owner->id,
        ];
    }

     /**
     * Создает владельца и животное, связывает их друг с другом и с приемом
     *
     * @param int $id_species
     * @param string $sex
     * @param string $name
     * @param string $chip_number
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $phone
     * @param string $email
     * @param string $fias_address
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreatePetAndOwnerForVisit($id_species, $sex, $name, $chip_number, $f_fio, $i_fio, $o_fio="", $phone, $email="", $fias_address, $id_visit, $id_tmp_pet) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pets_to_owner = (new PetToOwnerModel())->createPetAndOwnerForVisit($id_species, $sex, $name, $chip_number, $f_fio, $i_fio, $o_fio, $phone, $email, $fias_address, $id_visit, $id_tmp_pet);

        return [
            'result' => true,
            'id' => $pets_to_owner->id,
        ];
    }

    /**
     * Удаляет связь владелец/животное
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new PetToOwnerModel())->delete($id);

        return [
            'result' => true,
        ];
    }

    /**
     * Редактирование связи
     *
     * @param int $id
     * @param int $id_owner_type
     * @param int $id_owner
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionEdit($id, $id_owner_type, $id_owner = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new PetToOwnerModel())->edit($id, $id_owner_type, $id_owner);

        return [
            'result' => true,
        ];
    }

    /**
     * Возвращает указанный объект связи животное-владелец
     *
     * @param $id
     * @return array
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetToOwnerModel())->getPetsToOwner($id),
        ];
    }

    public function actionList()
    {
        throw new BadRequestHttpException('Not yet implemented');
    }

    /**
     * Типы владельцев
     *
     * @return array
     */
    public function actionType()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetToOwnerModel())->getOwnerTypes()
        ];
    }

    /**
     * История изменений владельцев
     *
     * @param int $id_pet
     * @return array
     */
    public function actionHistory(int $id_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetOwnersHistoryModel())->history($id_pet)
        ];
    }
}
