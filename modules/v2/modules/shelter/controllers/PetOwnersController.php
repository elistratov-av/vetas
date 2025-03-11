<?php

namespace app\modules\v2\modules\shelter\controllers;

use app\models\db\PetOwnerType;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\petOwners\models\PetOwnersModel;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use yii\web\BadRequestHttpException;

/**
 * Class PetOwnersController
 * @package app\modules\v2\modules\shelter\controllers
 */
class PetOwnersController extends BaseController
{
    /**
     * Поиск владельца животного
     *
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList($page = 1, $limit = 10, $filter = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetOwnersModel())->listPetOwners($page, $limit, $filter),
        ];
    }

    /**
     * Возвращает владельца по id
     *
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetOwnersModel())->getPetOwner($id),
        ];
    }

    /**
     * Создание владельца/представителя
     *
     * @param string      $f_fio
     * @param string      $i_fio
     * @param string|null $o_fio
     * @param string|null $jur_name
     * @param string|null $inn
     * @param string|null $ogrn
     * @param string|null $birthday
     * @param string|null $snils
     * @param bool        $is_legal
     * @param int|null    $id_area
     * @param int|null    $id_district
     * @param string|null $fias_address
     * @param string|null $fact_fias_address
     * @param bool|null   $entrepreneur
     * @param string|null   $description
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate($f_fio, $i_fio, $o_fio = null, $jur_name = null, $inn = null, $ogrn = null, $birthday = null,
                                 $snils = null, $is_legal = false, $id_area = null, $id_district = null,
                                 $fias_address = null, $fact_fias_address = null, $entrepreneur = false, $description = null, $addresses_is_equal = false)
    {

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pet_owner = (new PetOwnersModel())->create(
            $f_fio, $i_fio, $o_fio, $jur_name, $inn, $ogrn, $birthday, $snils, $is_legal,
            $id_area, $id_district, $fias_address, $fact_fias_address, $entrepreneur, $description, $addresses_is_equal
        );

        return [
            'result' => true,
            'id' => $pet_owner->id,
        ];
    }

    /**
     * Создает связь животное-владелец/представитель
     * Примечание: сотрудники приютов могут создавать только представителей
     *
     * @param int $id_pet
     * @param int $id_owner
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionLink($id_pet, $id_owner)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $id_owner_type = PetOwnerType::findRepresentativeTypeId();
        $pets_to_owner = (new PetToOwnerModel())->create($id_pet, $id_owner, $id_owner_type);

        return [
            'result' => true,
            'id' => $pets_to_owner->id,
        ];
    }
}
