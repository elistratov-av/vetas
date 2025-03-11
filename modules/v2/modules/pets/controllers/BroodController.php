<?php

namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pets\models\BroodModel;
use yii\web\BadRequestHttpException;

/**
 * Class BroodController
 * @package app\modules\v2\modules\pets\controllers
 */
class BroodController extends BaseController
{
    /**
     * Создание выводка
     *
     * @param int      $id_species
     * @param int|null $id_breed
     * @param string   $birthday
     * @param int      $id_owner
     * @param int      $id_owner_type
     * @param int      $pet_count
     * @return array
     */
    public function actionCreate($id_species, $id_breed = null, $birthday, $id_owner, $id_owner_type, $pet_count)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->createBrood($id_species, $id_breed, $birthday, $id_owner, $id_owner_type, $pet_count);

        return [
            'result' => $result,
        ];
    }

    /**
     * Редактирование выводка
     *
     * @param int      $id
     * @param int      $id_species
     * @param int|null $id_breed
     * @param string   $birthday
     * @param int      $id_owner
     * @param int      $id_owner_type
     * @return array
     */
    public function actionEdit($id, $id_species, $id_breed = null, $birthday, $id_owner, $id_owner_type)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->editBrood($id, $id_species, $id_breed, $birthday, $id_owner, $id_owner_type);

        return [
            'result' => $result,
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->findBrood($id);

        if ($result === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Список животных выводка
     *
     * @param int $id_brood
     * @param int $id_owner Если указано показывает только доступные для записи на прием животные
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionListPets($id_brood, $id_owner = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->listPets($id_brood, $id_owner);

        if ($result === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Список выводков владельца
     *
     * @param int $id_owner
     * @param boolean $has_active_brood Если TRUE показывает только доступные для записи на прием выводки
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionListBroods($id_owner, $has_active_brood = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->listBroods($id_owner, $has_active_brood);

        return [
            'result' => $result,
        ];
    }

    /**
     * Добавление животного выводка
     *
     * @param int $id_brood
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionAddPet($id_brood)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->addPet($id_brood);

        if ($result === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Удаление животного выводка
     *
     * @param int $id_brood
     * @param int $id_pet
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionRemovePet($id_brood, $id_pet)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new BroodModel())->removePet($id_brood, $id_pet);

        if ($result === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        return [
            'result' => $result,
        ];
    }

    /**
     * Удаление выводка
     *
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=129349313#id-%D0%94%D0%BE%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D0%BA%D0%B0%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D1%8B%22%D0%92%D0%BB%D0%B0%D0%B4%D0%B5%D0%BB%D0%B5%D1%86%D0%B6%D0%B8%D0%B2%D0%BE%D1%82%D0%BD%D0%BE%D0%B3%D0%BE%22%D0%B4%D0%BB%D1%8F%D0%BE%D1%82%D0%BE%D0%B1%D1%80%D0%B0%D0%B6%D0%B5%D0%BD%D0%B8%D1%8F%D0%B2%D1%8B%D0%B2%D0%BE%D0%B4%D0%BA%D0%BE%D0%B2-%D0%A4%D0%A2-3.2%D0%A3%D0%B4%D0%B0%D0%BB%D0%B5%D0%BD%D0%B8%D0%B5%D0%B2%D1%8B%D0%B2%D0%BE%D0%B4%D0%BA%D0%B0
     */
    public function actionDelete(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new BroodModel())->remove($id),
        ];
    }
}
