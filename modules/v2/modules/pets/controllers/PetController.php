<?php

namespace app\modules\v2\modules\pets\controllers;

use app\common\components\pdfGenerator\PdfGenerator;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\models\db\Aviary;
use app\models\db\Files;
use app\models\db\Organizations;
use app\models\db\PetHistory;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\RegExpireReasons;
use app\models\db\ShelterGuests;
use app\models\db\VisitDescriptions;
use app\models\db\Visits;
use app\modules\v2\modules\pets\models\PetsModel;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
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
     * @param int $id_species
     * @param string $name
     * @param string $reg_expire_date
     * @param string $birthday
     * @param string $sex
     * @param int $id_breed
     * @param int|null $id_reg_expire_reason
     * @param bool $guide_dog
     * @param bool $castrated
     * @param int $photo
     * @param string $date_plan_rabies_vaccination
     * @param string $date_plan_identification
     * @param string $date_plan_lept_vaccination
     * @param bool $force
     * @param int $id_owner
     * @param int $id_owner_type
     * @param bool $is_address_pet_owners
     * @param string $description
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate(
        $id_species,
        $id_breed,
        $name = null,
        $reg_expire_date = null,
        $birthday = null,
        $sex = null,
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

            $optionalFiels = [
                "id_breed" => $id_breed ?? null,
                "description" => $description ?? null,
                "sex" => $sex ?? null,
                "guide_dog" => $guide_dog ?? null,
                "castrated" => $castrated ?? null,
                "birthday" => $birthday ?? null,
            ];

            $optionalFielsdHash = mb_strtoupper(md5(implode('|', $optionalFiels)));
            $suggestions = $model->suggestDuplicates(
                $id_owner,
                $id_species,
                $name,
                $optionalFielsdHash
//                $id_breed,
//                $id_owner_type
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
     * @param int $id
     * @param int $id_species
     * @param string $name
     * @param string $reg_expire_date
     * @param string $birthday
     * @param string $sex
     * @param int $id_breed
     * @param int|null $id_reg_expire_reason
     * @param bool $guide_dog
     * @param bool $castrated
     * @param int $photo
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
    public
    function actionEdit(
        $id,
        $id_species,
        $id_breed,
        $name = null,
        $reg_expire_date = null,
        $birthday = null,
        $sex = null,
        $id_reg_expire_reason = null,
        $guide_dog = false,
        $castrated = false,
        $photo = null,
        $date_plan_rabies_vaccination = null,
        $date_plan_identification = null,
        $date_plan_lept_vaccination = null,
        $description = null,
        $fias_address = null,
        $is_address_pet_owners = null,
        $characteristics = null
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
                $is_address_pet_owners,
                $characteristics
            );

        if ($reg_expire_date) {
            if (PetHistory::findOne(['id_pet' => $id, 'event' => 'DEREGISTERED'])) {
            } else {
                PetHistory::addRecord(['id_pet' => $id, 'event' => PetHistory::HISTORY_EVENT_DEREGISTERED, 'options' => $id_reg_expire_reason]);
            }
        }

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
    public
    function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetsModel())->getPet($id),
        ];
    }

    /**
     * Поиск животного для приемов
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public
    function actionListVisits($page = 1, $limit = 10, $filter = null)
    {
        return [
            'result' => (new PetsModel())->listPetsVisits($page, $limit, $filter),
        ];
    }

    /**
     * Поиск животного
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public
    function actionList2($page = 1, $limit = 10, $filter = null, $sortBy = null, $sortDesc = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetsModel())->listPets($page, $limit, $filter, $sortBy, $sortDesc),
        ];
    }

    public
    function actionList($page = 1, $limit = 10, $filter = null, $isAsc = "flag", $orderBy = "flag", $leftJoinType = "flag", $idBaseTable = "flag")
    {
        if ($limit > 100) {
            $limit = 100;
        }

        if ($isAsc == "flag") {
            $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
            return [
                'result' => (new PetsModel())->listPets($page, $limit, $filter, $isAsc),
            ];
        }

        if ($isAsc != "flag" && $orderBy == "name") {
            return [
                'result' => (new PetsModel())->listPetsNullif($page, $limit, $filter, $isAsc, $orderBy),
            ];
        }

        if ($isAsc != "flag" && $orderBy != "name" && $leftJoinType == "flag") {
            return [
                'result' => (new PetsModel())->listPetsUniversal($page, $limit, $filter, $isAsc, $orderBy),
            ];
        }

        if ($isAsc != "flag" && $orderBy != "name" && $leftJoinType != "flag") {
            return [
                'result' => (new PetsModel())->listPetsUniversal($page, $limit, $filter, $isAsc, $orderBy, $leftJoinType, $idBaseTable),
            ];
        }
    }

    /**
     *
     * @param integer|array $id_pet
     * @param int|int[] $visit_ids
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public
    function actionPrint($id_pet, $visit_ids)
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
    public
    function actionDuplicates($id)
    {
        // $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new PetsModel())->findDuplicates($id),
        ];
    }

    private function getHistory($pet_id, $is_animal)
    {
        $query = Visits::find()
            ->select([
                    'visits.id as id',
                    'visits.fact_start_dttm as date',
                    'organizations.short_name as organization',
                    'addresses.name as adress',
                    'users.fullname as specialist',
                ]
            )
            ->rightJoin('visit_descriptions', 'visit_descriptions.id_visit = visits.id')
            ->rightJoin('organizations', 'organizations.id = visits.id_organization')
            ->rightJoin('addresses', 'addresses.id = organizations.id_address')
            ->rightJoin('description_types', 'description_types.id = visit_descriptions.id_description_type')
            ->rightJoin('users', 'users.id = visit_descriptions.created_by')
            ->andWhere(['visit_descriptions.id_pet' => $pet_id])
            ->orderBy(['date' => SORT_ASC]);

        $records = $query
            ->asArray()
            ->all();

        for ($i = 0; $i < count($records); $i++) {
            $desc = VisitDescriptions::find()
                ->select([
                    'description',
                    'id_visit',
                    'id_description_type',
                    'id_pet',
                    'gd.name as disease_name',
                    'tech_name',
                ])
                ->from('visit_descriptions vd')
                ->leftJoin('description_types dt', 'dt.id = vd.id_description_type')
                ->leftJoin('gost_diseases gd', 'vd.description = gd.gost_code')
                ->where([
                    'AND',
                    ['vd.id_visit' => $records[$i]['id']],
                    ['vd.id_pet' => $pet_id],
                ])
                ->asArray()
                ->all();

            //Предварительный диагноз
            $mergedDiseaseNames = [];
            foreach ($desc as $item) {
                if ($item['tech_name'] == 'VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE') {
                    $mergedDiseaseNames[] = '('.$item['description'].') ' . $item['disease_name'];
                }
            }
            $mergedDiseaseString = implode(', ', $mergedDiseaseNames);
            //

            //Заключительный диагноз
            $mergedDiagnosisNames = [];
            foreach ($desc as $item) {
                if ($item['tech_name'] == 'VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE') {
                    $mergedDiagnosisNames[] = '('.$item['description'].') ' . $item['disease_name'];
                }
            }
            $mergedDiagnosisString = implode(', ', $mergedDiagnosisNames);
            //

            $records[$i]['anamnesis'] = null;
            $records[$i]['schema'] = null;
            $records[$i]['reccomends'] = null;
            $records[$i]['disease'] = null;
            $records[$i]['diagnosis'] = null;
            $records[$i]['file'] = null;
            $records[$i]['clinical_data'] = null;
            foreach ($desc as $des) {
                if ($des['tech_name'] === 'VISIT_ANAMNEZ_1') {
                    $records[$i]['anamnesis'] = $des['description'];
                }
                if ($des['tech_name'] === 'VISIT_SKHEMA_LECHENIYA_5') {
                    $records[$i]['schema'] = $des['description'];
                }
                if ($des['tech_name'] === 'VISIT_REKOMENDATSII') {
                    $records[$i]['reccomends'] = $des['description'];
                }
                if ($des['tech_name'] === 'LABORATORY_FILE') {
                    $records[$i]['file'] = $des['description'];
                }
                if ($des['tech_name'] === 'VISIT_CLINICAL_DATA') {
                    $records[$i]['clinical_data'] = $des['description'];
                }
                if ($des['tech_name'] == 'VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE') {
                    $records[$i]['disease'] =  $mergedDiseaseString;
                }
                if ($des['tech_name'] === 'VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE') {
                    $records[$i]['diagnosis'] = $mergedDiagnosisString;
                }
            }
        }

        return [
            'result' => $records,
        ];
    }


    public function actionMedicalHistory($pet_id)
    {
        return $this->getHistory($pet_id, true);
    }

}
