<?php

namespace app\modules\v2\modules\vaccinationJournal\controllers;

use app\models\db\DescriptionTypes;
use app\models\db\GovServices;
use app\models\db\Pets;
use app\models\db\Quarantine;
use app\models\db\ShiftType;
use app\models\db\VisitDescriptions;
use app\models\db\Visits;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\vaccinationJournal\models\FlatModel;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use app\modules\v2\modules\visit\models\VisitSaveModel;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Class FlatController
 *
 * @package app\modules\v2\modules\vaccinationJournal\controllers
 */
class FlatController extends BaseController
{
    public function actionListForAct(array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => FlatModel::getAllForAct($filter),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionListOutOrganizations(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => FlatModel::getAllOutOrganizations($page, $limit, $filter),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $this->findModel($id),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => FlatModel::getAll($page, $limit, $filter),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     */
    public function actionNonExistedPetList(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => FlatModel::getNonExistedPets($page, $limit, $filter),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionStatistics(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => FlatModel::getAll($page, $limit, $filter, true),
        ];
    }

    /**
     * @param int|null $visit_id
     * @param int $organization_id
     * @param int $user_specialist_id
     * @param string $date
     * @param array|string $fact_fias_address
     * @param int|null $owner_id
     * @param array|string $contacts
     * @param int $why_not_available
     * @param int|null $species_id
     * @param int|null $size_id
     * @param int|null $pet_id
     * @param string|null $sex
     * @param int|null $breed_id
     * @param string|null $birthday
     * @param array|string $pet_identification
     * @param string|null $description
     * @param string|null $new_valid_until
     * @param int|null $vaccine_organization_id
     * @param bool $is_out_org
     * @param int|null $vaccine_id
     * @param int|null $balance_id
     * @param string|null $vaccine_series
     * @param string|null $vaccine_best_before
     * @param int|null $specialist_id
     * @param bool $is_rejected
     * @param string|null $rejected_info
     * @param int|null $quarantine_zone_id
     * @param bool|null $is_owner_moved
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function actionCreate(
        int     $organization_id,
        int     $user_specialist_id,
        string  $date,
        int     $why_not_available,
        bool    $is_rejected,
        ?int    $visit_id = null,
        ?string $description = null,
        ?int    $owner_id = null,
                $contacts = [],
                $fact_fias_address = [],
                $pet_identification = [],
        ?string $vaccine_series = null,
        ?int    $species_id = null,
        ?int    $size_id = null,
        ?int    $pet_id = null,
        ?int    $breed_id = null,
        ?string $birthday = null,
        ?string $sex = null,
        ?string $new_valid_until = null,
        ?int    $vaccine_organization_id = null,
        ?bool   $is_out_org = null,
        ?int    $vaccine_id = null,
        ?int    $balance_id = null,
        ?string $vaccine_best_before = null,
        ?int    $specialist_id = null,
        ?string $rejected_info = null,
        ?int    $quarantine_zone_id = null,
        ?bool   $is_owner_moved = null,
        ?string $anamnez = null,
        ?bool $health = true
    ) {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if ($is_owner_moved) {
            FlatModel::manageOwnerIsMoved($owner_id, $pet_id);
        }

        if ($why_not_available !== FlatModel::NONE) {
            FlatModel::manageWhyNotAvailable($quarantine_zone_id, $why_not_available, $date, $fact_fias_address, $pet_id);
        }

        FlatModel::updatePetAndOwnerData($pet_id, $owner_id, $contacts, $fact_fias_address, $pet_identification, $description, $birthday, $species_id, $size_id, $breed_id, $sex);

//        if (!$is_rejected && !$vaccine_id && !$balance_id) {
//            return [
//                'result' => true,
//                'id' => null,
//            ];
//        }

        // Проверим на наличие активных вакцин перед созданием отказа
        if ($is_rejected) {
            $quarantine = Quarantine::find()->where(['id' => $quarantine_zone_id])->one();
            $pet = Pets::find()->where(['id' => $pet_id])->one();
            if ($pet->hasActiveVaccination($quarantine->id_disease)) {
                throw new Exception('Животное имеет активную вакцинацию. Невозможно создать нарушение');
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($visit_id) {
                //0. При редактировании ищем существующий приём (обход)
                $visit = Visits::find()->where(['id' => $visit_id])->one();
                if (!$visit) {
                    throw new Exception("Данные о обходе с id '$visit_id' не найдены");
                }

                /** @var Visits $visit */
                $model = new VisitSaveModel([
                    'scenario' => VisitSaveModel::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
                    'type' => Visits::TYPE_VISIT_VC_DETOUR,
                    'visit' => $visit,
                ]);
            } else {
                //1. Создаем прием (обход)
                $model = new VisitSaveModel([
                    'scenario' => VisitSaveModel::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                    'type' => Visits::TYPE_VISIT_VC_DETOUR,
                    'variety' => Visits::VISIT_SINGLE,
                ]);
            }
            $model->id_owner = $owner_id;
            $model->id_pet = $pet_id;
            $model->id_organization = $is_out_org ? $organization_id : $vaccine_organization_id;
            $shiftType = ShiftType::findOne(['type' => 'DETOUR']);
            $model->channel = $shiftType->id;
            $model->id_specialist = $specialist_id ?? $user_specialist_id;
            $model->author = $user_specialist_id;
            $model->start_dttm = $date . ' ' . date('H:i:s');
            $model->fact_start_dttm = $date . ' ' . date('H:i:s');
            $model->id_quarantine = $quarantine_zone_id;

            $govService = GovServices::findOne(['cod' => '0206']);
            $model->services = [
                [
                    'id_service' => $govService->id,
                    'count' => 1,
                ],
            ];

            if ($visit_id) {
                if (!$model->updateVisit()) {
                    $this->errorResponse($model);
                }
            } else {
                if (!$model->createVisit()) {
                    $this->errorResponse($model);
                }
            }

            //2. Если отказ от вакцинации
            if ($is_rejected) {
                FlatModel::manageVaccineRejection($quarantine_zone_id, $owner_id, $pet_id, $model->visit, $rejected_info, $fact_fias_address, $date);
            }

            if (!$is_rejected && $vaccine_id) {
                $balance = FlatModel::getBalance($balance_id, $specialist_id);
                $tmcVaccine = FlatModel::getTmc($vaccine_id, $balance);

                $dosage = $tmcVaccine->searchDefaultDosage($species_id, $size_id, true);
                if ($balance_id && !$dosage) {
                    throw new NotFoundHttpException('Не найдена дозировка вакцины на балансе.');
                }

                // Вакцины проставленные организациями из системы идут в услуги Осмотра
                // Вакцины внешних организаций пишем в карту животного напрямую
                if (!$is_out_org) {
                    FlatModel::addTmcVaccine($tmcVaccine, $pet_id, $vaccine_series, $vaccine_best_before, $new_valid_until, $model->visit, $balance, $dosage);
                } else {
                    FlatModel::addOutsideVaccine($quarantine_zone_id, $pet_id, $vaccine_id, $tmcVaccine, $date, $vaccine_organization_id, $user_specialist_id, $model->visit, $vaccine_best_before, $vaccine_series, $fact_fias_address, $new_valid_until);
                }
            }

            $transaction->commit();

            if ($anamnez){
                $where = [
                    'tech_name' => "VISIT_ANAMNEZ_1",
                ];
                $descriptionTypesModel = DescriptionTypes::findOne($where);
                if ($model->visit->id) {
                    $descriptions = [[
                        'id_description_type' => $descriptionTypesModel->id,
                        'description' => [$anamnez],
                        'id_pet' => $pet_id
                    ],];
                    $visit = $model->visit;
                    $model = new VisitDescriptionsModel(compact('visit', 'descriptions'));
                    if (!$model->save()) {
                        $this->errorResponse($model, 'Ошибка при сохранении анамнеза при вакцинации');
                    }
                }
            }
            if ($health){
                $where = [
                    'tech_name' => "VISIT_CLINICAL_DATA",
                ];
                $descriptionTypesModel = DescriptionTypes::findOne($where);
                if ($model->visit->id) {
                    $descriptions = [[
                        'id_description_type' => $descriptionTypesModel->id,
                        'description' => ['Клинически здоров'],
                        'id_pet' => $pet_id
                    ],];
                    $visit = $model->visit;
                    $model = new VisitDescriptionsModel(compact('visit', 'descriptions'));
                    if (!$model->save()) {
                        $this->errorResponse($model, 'Ошибка при сохранении клинического здоровья при вакцинации');
                    }
                }
            } else {
                $where = [
                    'tech_name' => "VISIT_CLINICAL_DATA",
                ];
                $descriptionTypesModel = DescriptionTypes::findOne($where);
                if ($model->visit->id) {
                    $descriptions = [[
                        'id_description_type' => $descriptionTypesModel->id,
                        'description' => '-',
                        'id_pet' => $pet_id
                    ],];
                    $visit = $model->visit;
                    $model = new VisitDescriptionsModel(compact('visit', 'descriptions'));
                    if (!$model->save()) {
                        $this->errorResponse($model, 'Ошибка при сохранении клинического здоровья при вакцинации');
                    }
                }
            }



            return [
                'result' => true,
                'id' => $model->visit->id,
            ];

        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     */
    public function actionDelete(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        Visits::findOne($id)->delete();

        return [
            'result' => true,
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): array
    {
        if (($model = FlatModel::get($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Journal doesn\'t exists.');
    }
}
