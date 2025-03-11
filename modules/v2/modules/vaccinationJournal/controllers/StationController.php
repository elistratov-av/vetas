<?php

namespace app\modules\v2\modules\vaccinationJournal\controllers;

use app\models\db\Contacts;
use app\models\db\DescriptionTypes;
use app\models\db\Diseases;
use app\models\db\FiasAddresses;
use app\models\db\GovServices;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\ShiftType;
use app\models\db\Species;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcVaccine;
use app\models\db\VaccinationStation;
use app\models\db\Visits;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\petOwners\models\ContactsModel;
use app\modules\v2\modules\pets\models\IdentModel;
use app\modules\v2\modules\vaccinationJournal\models\StationModel;
use app\modules\v2\modules\visit\models\ServiceTmcsModelSave;
use app\modules\v2\modules\visit\models\VisitDescriptionsModel;
use app\modules\v2\modules\visit\models\VisitSaveModel;
use app\modules\v2\modules\visit\models\VisitViolationReport;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class StationController
 *
 * @package app\modules\v2\modules\vaccinationJournal\controllers
 */
class StationController extends BaseController
{
    public function actionListForAct(array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => StationModel::getAllForAct($filter),
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
     * @param int   $page
     * @param int   $limit
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
            'result' => StationModel::getAll($page, $limit, $filter),
        ];
    }

    /**
     * @param int          $vaccination_station_id
     * @param int          $organization_id
     * @param int          $user_specialist_id
     * @param int          $owner_id
     * @param array|string $fact_fias_address
     * @param array|string $contacts
     * @param int          $pet_id
     * @param int          $species_id
     * @param int|null     $size_id
     * @param string|null  $sex
     * @param int|null     $breed_id
     * @param string       $birthday
     * @param array|string $pet_identification
     * @param string       $description
     * @param bool         $is_rejected
     * @param int|null     $vaccine_id
     * @param bool         $vaccine_balance
     * @param string|null  $vaccine_series
     * @param string|null  $vaccine_best_before
     * @param int|null     $specialist_id
     * @param string|null  $reg_number
     * @param string|null  $rejected_info
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function actionCreate(
        int $vaccination_station_id,
        int $organization_id,
        int $user_specialist_id,
        int $owner_id,
        $fact_fias_address,
        $contacts,
        int $pet_id,
        int $species_id,
        ?int $breed_id = null,
        string $birthday,
        $pet_identification,
        string $description,
        bool $is_rejected,
        ?int $vaccine_id = null,
        bool $vaccine_balance,
        ?string $vaccine_series = null,
        ?string $vaccine_best_before = null,
        ?int $specialist_id = null,
        ?int $size_id = null,
        ?string $sex = null,
        ?int $balance_id = null,
        ?string $reg_number = null,
        ?string $rejected_info = null,
        ?string $new_valid_until = null,
        ?bool $is_out_org = false,
        ?bool $health = true,
        ?string $anamnez = null
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!$breed_id) {
            /** @var Species $species */
            $species = Species::find()->where(['id' => $species_id])->one();
            if (in_array($species->tech_name, [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG])) {
                throw new BadRequestHttpException('Параметр breed_id обязателен для кошек и собак');
            }
        }

        // Validation
        $balance = null;
        $tmcVaccine = null;
        $dosage = null;
        if (!$is_rejected) {
            if ($balance_id) {
                $balance = Balance::findOne([
                    'id' => $balance_id,
                    'id_specialist' => $specialist_id,
                ]);
                if (!$balance) {
                    throw new NotFoundHttpException('Не хватает вакцины на балансе.');
                }

                $tmcVaccine = TmcVaccine::findOne($balance->id_tmc);
            } else {
                $tmcVaccine = TmcVaccine::findOne($vaccine_id);
            }

            $dosage = $tmcVaccine->searchDefaultDosage($species_id, $size_id, true);
            if ($balance_id && !$dosage) {
                throw new NotFoundHttpException('Не найдена дозировка вакцины на балансе.');
            }
        }

        // Visit
        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_CREATE_SIMPLIFIED_VISIT,
            'type' => Visits::TYPE_VISIT_VC,
            'variety' => Visits::VISIT_SINGLE,
        ]);
        $vaccinationStation = VaccinationStation::findOne($vaccination_station_id);
        $model->vaccination_station_id = $vaccination_station_id;
        $model->id_owner = $owner_id;
        $model->id_pet = $pet_id;
        $model->id_organization = $vaccinationStation->parent_id;
        $shiftType = ShiftType::findOne(['type' => 'VACCINATION_STATION']);
        $model->channel = $shiftType->id;
        $model->id_specialist = $specialist_id ?? $user_specialist_id;
        $model->author = $user_specialist_id;
        $model->start_dttm = $vaccinationStation->date . ' ' . date('H:i:s');
        $model->fact_start_dttm = $vaccinationStation->date . ' ' . date('H:i:s');

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

        // Pet
        $pet = Pets::findOne($pet_id);
        $pet->reg_number = $reg_number;
        $pet->size_id = $size_id;
        $pet->id_species = $species_id;
        $pet->sex = $sex;
        $pet->id_breed = $breed_id;
        $pet->birthday = $birthday;
        $pet->description = $description;
        $pet->save();

        // Pet Owner - Fact FIAS Address
        $petOwner = $pet->owner;
        if (!$petOwner) {
            $petOwner = PetOwners::findOne($owner_id);
        }
        if ($petOwner) {
            if (!empty($fact_fias_address)) {
                $petOwner->id_fact_fias_address = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);
                if (!$petOwner->id_area) {
                    $petOwner->id_area = FiasAddresses::findOne($petOwner->id_fact_fias_address)->id_area;
                }
                if (!$petOwner->id_district) {
                    $petOwner->id_district = FiasAddresses::findOne($petOwner->id_fact_fias_address)->id_district;
                }
                $petOwner->save();
            } else {
                $petOwner->id_fact_fias_address = null;
            }
        }

        // Pet - Pet Identification
        (new IdentModel)->save($pet_id, $pet_identification);

        // Pet Owner - Contact
        foreach ($contacts as $contact) {
            $oldContact = Contacts::findOne([
                'id_contact_type' => $contact['id_contact_type'],
                'entity_type' => $contact['entity_type'],
                'entity_id' => $contact['entity_id'],
                'name' => $contact['name'],
            ]);
            if (!$oldContact) {
                (new ContactsModel())->create(
                    $contact['id_contact_type'],
                    $contact['entity_type'],
                    $contact['entity_id'],
                    $contact['name'],
                    $contact['main_flag']
                );
            }
        }

        // Vaccine
        if (!$is_rejected) {
            $balanceTmcs = [];
            $otherTmcs = [];
            if ($balance_id) {
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
            } else {
                $otherTmcs = [
                    [
                        'row_id' => 1,
                        'id_tmc' => $tmcVaccine->id,
                        'type_tmc' => TmcBase::TYPE_VACCINE,
                        'count_selected' => 1,
                        'id_dosage' => $dosage->id ?? null,
                        'batch' => $vaccine_series,
                        'production_date' => null,
                        'expiry_date' => $vaccine_best_before,
                        'pets' => [
                            $pet_id,
                        ],
                        'valid_until' => $new_valid_until ?? date('Y-m-d', strtotime($model->visit->fact_start_dttm . ' +1 year')),
                    ],
                ];
            }

            (new ServiceTmcsModelSave())->save(
                $model->visit->id,
                $model->visit->visitsGovServices[0]->id,
                $balanceTmcs,
                $otherTmcs,
                $new_valid_until
            );
        } else {
            $rabies = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one();
            $pet = Pets::find()->where(['id' => $pet_id])->one();
            if ($pet->hasActiveVaccination($rabies->id)) {
                throw new Exception('Животное имеет активную вакцинацию. Невозможно создать нарушение');
            }
            (new VisitViolationReport())->report(
                $model->visit,
                4,
                [
                    $pet_id => $rejected_info,
                ],
                [],
                true
            );
        }

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
        }
        else {
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
    }

    /**
     * @param int         $id
     * @param int         $vaccination_station_id
     * @param int         $organization_id
     * @param int         $user_specialist_id
     * @param int         $owner_id
     * @param             $fact_fias_address
     * @param             $contacts
     * @param int         $pet_id
     * @param int         $species_id
     * @param int|null    $size_id
     * @param string|null $sex
     * @param int|null    $breed_id
     * @param string      $birthday
     * @param             $pet_identification
     * @param string      $description
     * @param bool        $is_rejected
     * @param int|null    $vaccine_id
     * @param bool        $vaccine_balance
     * @param string|null $vaccine_series
     * @param string|null $vaccine_best_before
     * @param int|null    $specialist_id
     * @param bool        $health
     * @param string|null $anamnez
     *
     * @param string|null $reg_number
     * @param string|null $rejected_info
     *
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionEdit(
        int $id,
        int $vaccination_station_id,
        int $organization_id,
        int $user_specialist_id,
        int $owner_id,
        $fact_fias_address,
        $contacts,
        int $pet_id,
        int $species_id,
        ?int $breed_id = null,
        string $birthday,
        $pet_identification,
        string $description,
        bool $is_rejected,
        ?int $vaccine_id = null,
        bool $vaccine_balance,
        ?string $vaccine_series = null,
        ?string $vaccine_best_before = null,
        ?int $specialist_id = null,
        ?int $size_id = null,
        ?string $sex = null,
        ?int $balance_id = null,
        ?string $new_valid_until = null,
        ?string $reg_number = null,
        ?string $rejected_info = null,
        ?bool $is_out_org = false,
        ?bool $health = true,
        ?string $anamnez = null
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (!$breed_id) {
            /** @var Species $species */
            $species = Species::find()->where(['id' => $species_id])->one();
            if (in_array($species->tech_name, [Species::TECH_NAME_CAT, Species::TECH_NAME_DOG])) {
                throw new BadRequestHttpException('Параметр breed_id обязателен для кошек и собак');
            }
        }

        // Validation
        $balance = null;
        $tmcVaccine = null;
        $dosage = null;
        if (!$is_rejected) {
            if ($balance_id) {
                $balance = Balance::findOne([
                    'id' => $balance_id,
                    'id_specialist' => $specialist_id,
                ]);
                if (!$balance) {
                    throw new NotFoundHttpException('Не хватает вакцины на балансе.');
                }

                $tmcVaccine = TmcVaccine::findOne($balance->id_tmc);
            } else {
                $tmcVaccine = TmcVaccine::findOne($vaccine_id);
            }

            $dosage = $tmcVaccine->searchDefaultDosage($species_id, $size_id, true);
            if (!$dosage) {
                throw new NotFoundHttpException('Не найдена дозировка вакцины на балансе.');
            }
        }

        // Visit
        $visit = Visits::findOne($id);
        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_UPDATE_SIMPLIFIED_VISIT,
            'type' => Visits::TYPE_VISIT_VC,
            'visit'    => $visit,
        ]);

        $vaccinationStation = VaccinationStation::findOne($vaccination_station_id);
        $model->vaccination_station_id = $vaccination_station_id;
        $model->id_owner = $owner_id;
        $model->id_pet = $pet_id;
        $model->id_organization = $vaccinationStation->parent_id;
        $model->id_specialist = $specialist_id ?? $user_specialist_id;
        $model->author = $user_specialist_id;
        $model->start_dttm = $vaccinationStation->date . ' ' . date('H:i:s');
        $model->fact_start_dttm = $vaccinationStation->date . ' ' . date('H:i:s');

        $govService = GovServices::findOne(['cod' => '0206']);
        $model->services = [
            [
                'id_service' => $govService->id,
                'count' => 1,
            ],
        ];
        if (!$model->updateVisit()) {
            $this->errorResponse($model);
        }

        // Pet
        $pet = Pets::findOne($pet_id);
        $pet->reg_number = $reg_number;
        $pet->size_id = $size_id;
        $pet->id_species = $species_id;
        $pet->sex = $sex;
        $pet->id_breed = $breed_id;
        $pet->birthday = $birthday;
        $pet->description = $description;
        $pet->save();

        // Pet Owner - Fact FIAS Address
        $petOwner = $pet->owner;
        if (!$petOwner) {
            $petOwner = PetOwners::findOne($owner_id);
        }
        if ($petOwner) {
            if (!empty($fact_fias_address)) {
                $petOwner->id_fact_fias_address = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);
                if (!$petOwner->id_area) {
                    $petOwner->id_area = FiasAddresses::findOne($petOwner->id_fact_fias_address)->id_area;
                }
                if (!$petOwner->id_district) {
                    $petOwner->id_district = FiasAddresses::findOne($petOwner->id_fact_fias_address)->id_district;
                }
            } else {
                $petOwner->id_fact_fias_address = null;
            }
        }

        // Pet - Pet Identification
        (new IdentModel)->save($pet_id, $pet_identification);

        // Pet Owner - Contact
        foreach ($contacts as $contact) {
            $oldContact = Contacts::findOne([
                'id_contact_type' => $contact['id_contact_type'],
                'entity_type' => $contact['entity_type'],
                'entity_id' => $contact['entity_id'],
                'name' => $contact['name'],
            ]);
            if (!$oldContact) {
                (new ContactsModel())->create(
                    $contact['id_contact_type'],
                    $contact['entity_type'],
                    $contact['entity_id'],
                    $contact['name'],
                    $contact['main_flag']
                );
            }
        }

        // Vaccine
        if (!$is_rejected) {
            $balanceTmcs = [];
            $otherTmcs = [];
            if ($balance_id) {
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
            } else {
                $otherTmcs = [
                    [
                        'row_id' => 1,
                        'id_tmc' => $tmcVaccine->id,
                        'type_tmc' => TmcBase::TYPE_VACCINE,
                        'count_selected' => 1,
                        'id_dosage' => $dosage->id,
                        'batch' => $vaccine_series,
                        'production_date' => null,
                        'expiry_date' => $vaccine_best_before,
                        'pets' => [
                            $pet_id,
                        ],
                        'valid_until' => $new_valid_until ?? date('Y-m-d', strtotime($model->fact_start_dttm . ' +1 year')),
                    ],
                ];
            }

            (new ServiceTmcsModelSave())->save(
                $model->visit->id,
                $model->visit->visitsGovServices[0]->id,
                $balanceTmcs,
                $otherTmcs,
                $new_valid_until
            );
        } else {
            (new VisitViolationReport())->report(
                $model->visit,
                4,
                [
                    $pet_id => $rejected_info,
                ],
                [],
                true
            );
        }

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
        }
        else {
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
    }

    /**
     * @param int $id
     *
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
     *
     * @return array
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): array
    {
        if (($model = StationModel::get($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Journal doesn\'t exists.');
    }

}
