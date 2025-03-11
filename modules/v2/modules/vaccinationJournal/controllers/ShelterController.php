<?php

namespace app\modules\v2\modules\vaccinationJournal\controllers;

use app\models\db\Diseases;
use app\models\db\FiasAddresses;
use app\models\db\GovServices;
use app\models\db\Organizations;
use app\models\db\PetOwnerType;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\ShelterVaccineRejection;
use app\models\db\ShiftType;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcVaccine;
use app\models\db\Visits;
use app\modules\admin\models\PetsToOwner;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use app\modules\v2\modules\vaccinationJournal\models\ShelterModel;
use app\modules\v2\modules\visit\models\ServiceTmcsModelSave;
use app\modules\v2\modules\visit\models\VisitChangeStatusModel;
use app\modules\v2\modules\visit\models\VisitSaveModel;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class ShelterController
 *
 * @package app\modules\v2\modules\vaccinationJournal\controllers
 */
class ShelterController extends BaseController
{
    public function actionListForAct(array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => ShelterModel::getAllForAct($filter),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     *
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionListShelters(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => ShelterModel::getAllShelters($page, $limit, $filter),
        ];
    }

    public function actionListVaccines(): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => ShelterModel::getAllVaccines(),
        ];
    }

    /**
     * @param int $id
     *
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
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => ShelterModel::getAll($page, $limit, $filter),
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionStatistics(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => ShelterModel::getAll($page, $limit, $filter, true),
        ];
    }

    /**
     * @param int $user_specialist_id
     * @param int $organization_id
     * @param int $pet_id
     * @param int $shelter_id
     * @param int $size_id
     * @param int $balance_id
     * @param bool $is_rejected
     * @param string|null $rejected_info
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionCreate(
        int     $user_specialist_id,
        int     $organization_id,
        int     $pet_id,
        int     $balance_id = null,
        int     $shelter_id,
        bool    $is_rejected,
        ?int    $size_id = null,
        ?string $rejected_info = null,
        ?string $new_valid_until = null,
        ?string $vaccine_date = null,
        ?string $vaccination_date = null,
        ?string $vaccine_series = null,
        ?string $drug_name = null,
        ?string $id_tmc = null,
        ?string $produced_name = null,
        ?string $production_date = null
    ): array{
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pet = Pets::findOne($pet_id);

        // Validation
        $balance = null;
        $tmcVaccine = null;
        $dosage = null;
        if ($balance_id) {
            $balance = Balance::findOne([
                'id' => $balance_id,
                'id_specialist' => $user_specialist_id,
            ]);
            if (!$balance) {
                throw new NotFoundHttpException('Не хватает вакцины на балансе.');
            }

            $tmcVaccine = TmcVaccine::findOne($balance->id_tmc);

            $dosage = $tmcVaccine->searchDefaultDosage($pet->id_species, $size_id, true);
            if (!$dosage) {
                throw new NotFoundHttpException('Не найдена дозировка вакцины на балансе.');
            }
        }

        if ($size_id !== null) {
            $pet->size_id = $size_id;
            $pet->save();
        }

        // Vaccine
        if ($balance && $tmcVaccine && $dosage) {
            if ($size_id === null) {
                throw new BadRequestException('Для добавления вакцины необходимо выбрать размер животного');
            }
            // Visit
            $model = new VisitSaveModel([
                'scenario' => VisitSaveModel::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                'type' => Visits::TYPE_VISIT_VC_SHELTER,
                'variety' => Visits::VISIT_SINGLE,
            ]);
            if (isset($pet->owner->id)) {
                $model->id_owner = $pet->owner->id;
            }
            else {
                /** @var Organizations $organization */
                $organization = Organizations::find()->where(['id' => $shelter_id])->one();
                if ($organization->representative) {
                    /** @var PetsToOwner $link */
                    $link = PetsToOwner::find()->where(['id_owner' => $organization->representative->id, 'id_pet' => $pet_id])->one();
                    if (!$link) {
                        /** @var PetOwnerType $representativeOwnerType */
                        $representativeOwnerType = PetOwnerType::find()->where(['is_owner' => false])->one();
                        (new PetToOwnerModel())->create($pet_id, $organization->representative->id, $representativeOwnerType->id);
                    }
                    $model->id_owner = $organization->representative->id;
                }
            }

            if (!$model->id_owner){
                $owner = PetsToOwner::find()->where(['id_pet' => $pet_id])->one();
                if ($owner && $owner->hasProperty('id_owner')){
                    $owner_id =  $owner->id_owner;
                    $model->id_owner = $owner_id;
                }
            }

            $model->id_pet = $pet_id;
            $model->id_organization = $organization_id;
            $shiftType = ShiftType::findOne(['type' => 'SHELTER']);
            $model->channel = $shiftType->id;
            $model->id_specialist = $user_specialist_id;
            $model->author = $user_specialist_id;
            $model->start_dttm = date('Y-m-d H:i:s');
            $model->fact_start_dttm = $vaccine_date ? date('Y-m-d H:i:s', strtotime($vaccine_date)) : date('Y-m-d H:i:s');

            $govService = GovServices::findOne(['cod' => '0206']);
            $model->services = [
                [
                    'id_service' => $govService->id,
                    'count' => 1,
                ],
            ];

            if (!$model->createVisit()) {
                $this->errorResponse($model);
            }

            $balanceTmcs = [
                [
                    'row_id' => 1,
                    'id_balance_tmc' => $balance->id,
                    'id_tmc' => $tmcVaccine->id,
                    'type_tmc' => TmcBase::TYPE_VACCINE,
                    'count_selected' => 1,
                    'count_production_form' => floatval($dosage->dosage / $balance->production_form->volume),
                    'id_dosage' => $dosage->id,
                    'write_off_pack_form' => $balance->production_form->is_utilize,
                    'pets' => [
                        $pet_id,
                    ],
                    'valid_until' => $new_valid_until ?? date('Y-m-d', strtotime($model->visit->fact_start_dttm . ' +1 year')),
                ],
            ];

            (new ServiceTmcsModelSave())->save(
                $model->visit->id,
                $model->visit->visitsGovServices[0]->id,
                $balanceTmcs,
                []
            );
        }

        if ($is_rejected) {
            $existedShelterRejection = ShelterVaccineRejection::find()->where(['description' => $rejected_info, 'id_pet' => $pet_id, 'id_organization' => $organization_id])->one();

            if (!$existedShelterRejection) {
                $shelterRejection = new ShelterVaccineRejection([
                    'id_organization' => $shelter_id,
                    'id_pet' => $pet_id,
                    'description' => $rejected_info,
                    'created_by' => \Yii::$app->user->getIdentity()->getId(),
                    'updated_by' => \Yii::$app->user->getIdentity()->getId(),
                ]);

                if (!$shelterRejection->save()) {
                    $errors = $shelterRejection->getErrorSummary(true);
                    throw new Exception(empty($errors) ? 'Ошибка при сохранении данных обхода' : implode("\n", array_values($errors)));
                }
            }

            return [
                'result' => true,
                'id' => null,
            ];
        }

        $model = new VisitChangeStatusModel(['visit' => $model->visit]);
        $model->finishVisit();

        return [
            'result' => true,
            'id' => $model->visit->id,
        ];
    }

    /**
     * @param int $user_specialist_id
     * @param int $organization_id
     * @param int $balance_id
     * @param string $vaccination_date
     * @param        $pets
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionMassCreate(
        int     $user_specialist_id,
        int     $organization_id,
        int     $balance_id,
        string  $vaccination_date,
        int     $shelter_id,
                $pet_ids,
        ?string $new_valid_until
    ): array
    {

//        ini_set('max_execution_time', 900);
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $balance = Balance::findOne([
            'id' => $balance_id,
            'id_specialist' => $user_specialist_id,
        ]);
        if (!$balance) {
            throw new NotFoundHttpException('Не хватает вакцины на балансе.');
        }
        $tmcVaccine = TmcVaccine::findOne($balance->id_tmc);

        /** @var Organizations $shelter */
        $shelter = Organizations::find()->where(['id' => $shelter_id])->one();
        /** @var Pets[] $pets */
        $pets = Pets::find()->where(['IN', 'id', $pet_ids])->all();
        foreach ($pets as $pet) {
            // Проверяем наличие дозировки для животного
            $dosage = $tmcVaccine->searchDefaultDosage($pet->id_species, intval($pet->size_id), true);
            if (!$dosage) {
                throw new NotFoundHttpException('Не найдена дозировка вакцины на балансе.');
            }

            // Visit
            $model = new VisitSaveModel([
                'scenario' => VisitSaveModel::SCENARIO_CREATE_SIMPLIFIED_VISIT,
                'type' => Visits::TYPE_VISIT_VC_SHELTER,
                'variety' => Visits::VISIT_SINGLE,
            ]);
            if (isset($pet->owner->id)) {
                $model->id_owner = $pet->owner->id;
            } elseif ($shelter->representative) {
                $model->id_owner = $shelter->representative->id;
            } else {
                throw new NotFoundHttpException('Не настроен представитель приюта для создания осмотра с животными без владельца');
            }
            $model->id_pet = $pet->id;
            $model->id_organization = $organization_id;
            $shiftType = ShiftType::findOne(['type' => 'SHELTER']);
            $model->channel = $shiftType->id;
            $model->id_specialist = $user_specialist_id;
            $model->author = $user_specialist_id;
            $model->start_dttm = $vaccination_date . ' ' . date('H:i:s');
            $model->fact_start_dttm = $vaccination_date . ' ' . date('H:i:s');

            $govService = GovServices::findOne(['cod' => '0206']);
            $model->services = [
                [
                    'id_service' => $govService->id,
                    'count' => 1,
                ],
            ];

            if (!$model->createVisit()) {
                $this->errorResponse($model);
            }

            // Vaccine
            $balanceTmcs = [
                [
                    'row_id' => 1,
                    'id_balance_tmc' => $balance->id,
                    'id_tmc' => $tmcVaccine->id,
                    'type_tmc' => TmcBase::TYPE_VACCINE,
                    'count_selected' => 1,
                    'count_production_form' => floatval($dosage->dosage / $balance->production_form->volume),
                    'id_dosage' => $dosage->id,
                    'write_off_pack_form' => $balance->production_form->is_utilize,
                    'pets' => [
                        $pet->id,
                    ],
                    'valid_until' => $new_valid_until ?? date('Y-m-d', strtotime($model->visit->fact_start_dttm . ' +1 year')),
                ],
            ];

            (new ServiceTmcsModelSave())->save(
                $model->visit->id,
                $model->visit->visitsGovServices[0]->id,
                $balanceTmcs,
                []
            );

            $model = new VisitChangeStatusModel(['visit' => $model->visit]);
            $model->finishVisit();
        }

        return [
            'result' => true,
        ];
    }

    /**
     * @param int $id
     * @param int $user_specialist_id
     * @param int $organization_id
     * @param int $shelter_id
     * @param int $pet_id
     * @param int $size_id
     * @param int $balance_id
     * @param bool $is_rejected
     * @param string|null $rejected_info
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionEdit(
        int     $id,
        int     $organization_id,
        int     $user_specialist_id,
        int     $balance_id = null,
        int     $shelter_id,
        int     $pet_id,
        int     $size_id,
        bool    $is_rejected,
        ?string $rejected_info = null,
        ?string $new_valid_until = null
    ): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = Visits::findOne($id);
        $pet = Pets::findOne($pet_id);

        // Validation
        $balance = null;
        $tmcVaccine = null;
        $dosage = null;
        if ($balance_id) {
            $balance = Balance::findOne([
                'id' => $balance_id,
                'id_specialist' => $user_specialist_id,
            ]);
            if (!$balance) {
                throw new NotFoundHttpException('Не хватает вакцины на балансе.');
            }

            $tmcVaccine = TmcVaccine::findOne($balance->id_tmc);

            $dosage = $tmcVaccine->searchDefaultDosage($pet->id_species, $size_id, true);
            if (!$dosage) {
                throw new NotFoundHttpException('Не найдена дозировка вакцины на балансе.');
            }
        }

        if ($size_id !== null) {
            $pet->size_id = $size_id;
            $pet->save();
        }

        // Vaccine
        if ($balance && $tmcVaccine && $dosage) {
            if ($size_id === null) {
                throw new BadRequestException('Для добавления вакцины необходимо выбрать размер животного');
            }

            $balanceTmcs = [
                [
                    'row_id' => 1,
                    'id_balance_tmc' => $balance->id,
                    'id_tmc' => $tmcVaccine->id,
                    'type_tmc' => TmcBase::TYPE_VACCINE,
                    'count_selected' => 1,
                    'count_production_form' => floatval($dosage->dosage / $balance->production_form->volume),
                    'id_dosage' => $dosage->id,
                    'write_off_pack_form' => $balance->production_form->is_utilize,
                    'pets' => [
                        $pet_id,
                    ],
                    'valid_until' => $new_valid_until ?? date('Y-m-d', strtotime($model->fact_start_dttm . ' +1 year')),
                ],
            ];

            (new ServiceTmcsModelSave())->save(
                $model->id,
                $model->visitsGovServices[0]->id,
                $balanceTmcs,
                []
            );
        }
        if ($is_rejected) {
            $existedShelterRejection = ShelterVaccineRejection::find()->where(['description' => $rejected_info, 'id_pet' => $pet_id, 'id_organization' => $organization_id])->one();

            if (!$existedShelterRejection) {
                $shelterRejection = new ShelterVaccineRejection([
                    'id_organization' => $shelter_id,
                    'id_pet' => $pet_id,
                    'description' => $rejected_info,
                    'created_by' => \Yii::$app->user->getIdentity()->getId(),
                    'updated_by' => \Yii::$app->user->getIdentity()->getId(),
                ]);

                if (!$shelterRejection->save()) {
                    $errors = $shelterRejection->getErrorSummary(true);
                    throw new Exception(empty($errors) ? 'Ошибка при сохранении данных обхода' : implode("\n", array_values($errors)));
                }
            }

            return [
                'result' => true,
                'id' => null,
            ];
        }

        return [
            'result' => true,
            'id' => $model->id,
        ];
    }

    /**
     * @param int $id
     * @param string $id_pet
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     */
    public function actionDelete(int $id = null, int $id_pet = null): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if ($id) {
            Visits::findOne($id)->delete();
            return [
                'result' => true,
            ];
        }
        if ($id_pet) {
            $petRabiesVaccinationId = PetRabiesVaccination::find()->where(['id_pet' => $id_pet])->orderBy('valid_until desc')->one()->delete();

            if ($petRabiesVaccinationId) {
                return [
                    'result' => true,
                ];
            }
        }
        return [
            'result' => false,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): array
    {
        if (($model = ShelterModel::get($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Journal doesn\'t exists.');
    }
}
