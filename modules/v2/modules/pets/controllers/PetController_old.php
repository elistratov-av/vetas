<?php

namespace app\modules\v2\modules\pets\controllers;

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\models\VisitStatus;
use app\modules\v2\modules\pets\models\PetsModel;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;
use yii\web\NotFoundHttpException;

/**
 * Class PetController
 * @package app\modules\v2\modules\pets\controllers
 * @see     https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769605
 */
class PetController extends BaseController
{
    /**
     * Создание животного
     *
     * @param int    $id_species
     * @param string $name
     * @param string $reg_expire_date
     * @param string $birthday
     * @param string $sex
     * @param int    $id_breed
     * @param int    $id_reg_expire_reason
     * @param bool   $guide_dog
     * @param bool   $castrated
     * @param int    $photo
     * @param string $date_plan_rabies_vaccination
     * @param string $date_plan_identification
     * @param string $date_plan_lept_vaccination
     * @param bool   $force
     * @param int    $id_owner
     * @param int    $id_owner_type
     * @param bool $is_address_pet_owners
     * @param string $description
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate(
        $id_species,
        $name = null,
        $reg_expire_date = null,
        $birthday = null,
        $sex = null,
        $id_breed = null,
        $id_reg_expire_reason = null,
        $guide_dog = false,
        $castrated = false,
        $photo = null,
        $date_plan_rabies_vaccination = null,
        $date_plan_identification = null,
        $date_plan_lept_vaccination = null,
        $force = true,
        $id_owner = null,
        $id_owner_type = null,
        $description = null,
        $fias_address = null,
        $is_address_pet_owners = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new PetsModel();

        if ($force !== true && !empty($id_owner)) {
            $suggestions = $model->suggestDuplicates(
                $id_owner, $id_species, $name, $id_breed, $id_owner_type
            );
            if (!empty($suggestions)) {
                return [
                    'result' => true,
                    'suggestions' => $suggestions,
                ];
            }
        }

        $pet = $model
            ->create(
                $reg_expire_date,
                $birthday,
                $name,
                $sex,
                $id_species,
                $id_breed,
                $id_reg_expire_reason,
                $guide_dog,
                $castrated,
                $photo,
                $date_plan_rabies_vaccination,
                $date_plan_identification,
                $date_plan_lept_vaccination,
                $description,
                $fias_address,
                $is_address_pet_owners,
                $id_owner,
                $id_owner_type
            );

        return [
            'result' => true,
            'id' => $pet->id,
        ];
    }

    /**
     * Редактирование животного
     *
     * @param int    $id
     * @param int    $id_species
     * @param string $name
     * @param string $reg_expire_date
     * @param string $birthday
     * @param string $sex
     * @param int    $id_breed
     * @param int    $id_reg_expire_reason
     * @param bool   $guide_dog
     * @param bool   $castrated
     * @param int    $photo
     * @param string $date_plan_rabies_vaccination
     * @param string $date_plan_identification
     * @param string $date_plan_lept_vaccination
     * @param string $description
     * @param array $fias_address
     * @param bool $is_address_pet_owners
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(
        $id,
        $id_species,
        $name = null,
        $reg_expire_date = null,
        $birthday = null,
        $sex = null,
        $id_breed = null,
        $id_reg_expire_reason = null,
        $guide_dog = false,
        $castrated = false,
        $photo = null,
        $date_plan_rabies_vaccination = null,
        $date_plan_identification = null,
        $date_plan_lept_vaccination = null,
        $description = null,
        $fias_address = null,
        $is_address_pet_owners = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new PetsModel())
            ->edit(
                $id,
                $reg_expire_date,
                $birthday,
                $name,
                $sex,
                $id_species,
                $id_breed,
                $id_reg_expire_reason,
                $guide_dog,
                $castrated,
                $photo,
                $date_plan_rabies_vaccination,
                $date_plan_identification,
                $date_plan_lept_vaccination,
                $description,
                $fias_address,
                $is_address_pet_owners
            );

        return [
            'result' => true,
        ];
    }

    /**
     * Получение информации о животном
     *
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetsModel())->getPet($id),
        ];
    }

    /**
     * Поиск животного
     *
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList($page = 1, $limit = 10, $filter = null, $isAsc = "flag",  $orderBy = "flag", $leftJoinType = "flag", $idBaseTable = "flag")
    {
        if($limit > 100){$limit=100;}
        
        if ($isAsc == "flag") {
            $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
            return [
                'result' => (new PetsModel())->listPets($page, $limit, $filter, $isAsc),
            ];
          }
          
          if($isAsc != "flag" && $orderBy == "name" ) {
            return [
                'result' => (new PetsModel())->listPetsNullif($page, $limit, $filter, $isAsc, $orderBy),
            ];
          }

          if($isAsc != "flag" && $orderBy != "name" && $leftJoinType == "flag") {
            return [
                'result' => (new PetsModel())->listPetsUniversal($page, $limit, $filter, $isAsc, $orderBy),
            ];
          }

          if($isAsc != "flag" && $orderBy != "name" && $leftJoinType != "flag") {
            return [
                'result' => (new PetsModel())->listPetsUniversal($page, $limit, $filter, $isAsc, $orderBy, $leftJoinType, $idBaseTable),
            ];
          }
    }


    /**
     *
     * @param int   $id_pet
     * @param int|int[]  $visit_ids
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionPrint($id_pet, $visit_ids)
    {
        $petModel = (new PetsModel())->getPet($id_pet);
        if (!$petModel) {
            throw new NotFoundHttpException('Животное не найдено');
        }
        $this->checkAccess($this->action->getUniqueId(), $petModel, $this->actionParams);

        $result = (new PetsModel())->createPdfCard($id_pet, $visit_ids);

        if ($result === false) {
            $this->errorResponse($petModel, 'Ошибка при генерации файла');
        }

        return [
            'result' => $result
        ];
    }

    /**
     * Поиск ПРИВЯЗАННЫХ дублей животного (вкладка “Дублирующие записи” в карточке животного)
     *
     * @param int $id
     * @return array
     */
    public function actionDuplicates($id)
    {
        // $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetsModel())->findDuplicates($id),
        ];
    }
}