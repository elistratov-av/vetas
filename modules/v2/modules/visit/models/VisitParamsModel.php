<?php

namespace app\modules\v2\modules\visit\models;

use app\common\models\UserModel;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\FiasAddresses;
use app\models\db\IdentificationTypes;
use app\models\db\Organizations;
use app\models\db\Params;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetIdentification;
use app\models\db\PetsToOwner;
use app\models\db\RegCertificates;
use app\models\db\ServiceTypes;
use app\models\db\Specialists;
use app\models\db\tmc\Category;
use app\models\db\tmc\TmcBase;
use app\models\db\VisitParamValues;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use app\models\db\VisitsSpecialists;
use app\modules\v1\models\FileResource;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Сохранение параметров отчета
 * Class VisitParamsModel
 *
 * @package app\modules\v2\modules\visit\models
 */
class VisitParamsModel extends Model
{
    use ParamsTrait;

    /**
     * Сохраняет данные для отчета
     *
     * @param int $visitGovServiceId - id услуги
     */
    public function saveVisitParamsByVisitGovService(int $visitGovServiceId)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        $this->deleteByServiceId($visitGovServiceId);

        try {
            $visitGovService = VisitsGovServices::findOne($visitGovServiceId);
            if (!$visitGovService) {
                return;
            }

            switch ($visitGovService->service->serviceType->getTypeNamed()) {
                case ServiceTypes::TYPE_VACCINATION:
                    $this->saveVisitParamsByVaccinaftion($visitGovService);
                    break;
                    /*
                case ServiceTypes::TYPE_VSD:
                    $this->saveVisitParamsByVsd($visitGovService);
                    break;
                    */
                default:
                    $this->saveVisitParamsByGeneral($visitGovService);
            }

            $this->removeFiles($visitGovServiceId);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();
    }

    /**
     * Сохранение параметров отчета о вакцинации
     *
     * @param VisitsGovServices $visitGovService
     * @throws \yii\db\Exception
     */
    private function saveVisitParamsByVaccinaftion(VisitsGovServices $visitGovService)
    {
        foreach ($visitGovService->visitServiceTmcPet as $visitServiceTmcPet) {
            if ($visitServiceTmcPet->type_tmc != TmcBase::TYPE_VACCINE) {
                continue;
            }

            $this
                ->saveVisitParamsOne(
                    $visitServiceTmcPet->visit,
                    $visitServiceTmcPet->pet,
                    $visitServiceTmcPet->visitsGovService,
                    $visitServiceTmcPet->visitServiceTmc
                );
        }
    }

    /**
     * Сохранение параметров отчета об Оформление ветеринарных сопроводительных документов (ВСД)
     *
     * @param VisitsGovServices $visitGovService
     * @throws \yii\db\Exception
     */
    private function saveVisitParamsByVsd(VisitsGovServices $visitGovService)
    {
        foreach ($visitGovService->visitServiceTmcPet as $visitServiceTmcPet) {
            if ($visitServiceTmcPet->type_tmc != TmcBase::TYPE_EXP_MATERIAL) {
                continue;
            }
            if (!$visitServiceTmcPet->visitServiceTmc->tmc->hasCategorySlug(Category::SLUG_VETERINARY_SERTIFICATE_FORMS)) {
                continue;
            }

            $this
                ->saveVisitParamsOne(
                    $visitServiceTmcPet->visit,
                    $visitServiceTmcPet->pet,
                    $visitServiceTmcPet->visitsGovService,
                    $visitServiceTmcPet->visitServiceTmc
                );
        }
    }

    /**
     * Сохранение параметров всех остальных отчетов
     *
     * @param VisitsGovServices $visitGovService
     * @throws \yii\db\Exception
     */
    public function saveVisitParamsByGeneral(VisitsGovServices $visitGovService)
    {
        $pets = $visitGovService->pets ?? $visitGovService->pet;
        if (!$pets) {
            return;
        }

        if (!is_array($pets)) {
            $pets = [$pets];
        }

        foreach ($pets as $pet) {
            $this
                ->saveVisitParamsOne(
                    $visitGovService->visit,
                    $pet,
                    $visitGovService
                );
        }
    }

    /**
     * @param Visits $visit
     * @param Pets $pet
     * @param VisitsGovServices|null $visitGovService
     * @param VisitServiceTmc|null $visitServiceTmc
     * @throws \yii\db\Exception
     */
    private function saveVisitParamsOne(Visits $visit, Pets $pet, ?VisitsGovServices $visitGovService = null, ?VisitServiceTmc $visitServiceTmc = null)
    {
        $visitParams = $this->findVisitParams('tech_name');
        $owner = self::findVisitOwner($visit->id_owner);
        $specialist = self::findSpecialist($visit->id);

        $organization = self::findOrganization($visit->id_organization);
        if ($organization['parent_id'] == $organization['id']) {
            // защита от дурака
            $parentOrg = $organization;
        } else {
            $parentOrg = empty($organization['parent_id']) ? $organization : self::findOrganization($organization['parent_id']);
            if ($parentOrg !== null) {
                while (!empty($parentOrg['parent_id'])) {
                    $parentOrg = self::findOrganization($parentOrg['parent_id']);
                    if ($parentOrg === null) {
                        break;
                    }
                }
            }
        }

        //1
        /* @var PetRabiesVaccination|PetOtherVaccinations $petVaccination */
        $petVaccination = null;
        if ($visitServiceTmc && $visitServiceTmc->getPetVaccination()) {
            $petVaccination = $visitServiceTmc
                ->getPetVaccination()
                ->andWhere(['id_pet' => $pet->id])
                ->one();
        }

        foreach ($visitParams as $field) {
            $param = [
                'id_visit'             => $visit->id,
                'id_param'             => $field['id'],
                'id_visitservice'      => $visitGovService ? $visitGovService->id : null,
                'id_visit_service_tmc' => $visitServiceTmc ? $visitServiceTmc->id : null,
                'id_pet'               => $pet->id,
            ];
            switch ($field['tech_name']) {
                case 'P1_Parentorgshortname':
                    $param['char_value'] = empty($parentOrg) ? '' : $parentOrg['name'];
                    break;
                case 'P13_Orgshortname':
                    $param['char_value'] = $organization['short_name'];
                    break;
                case 'P3_Visitstartdate':
                    // формат visits.start_dttm был изменен!
                    $value = null;
                    if ($visit->fact_start_dttm) {
                        $start_dttm = date_create_from_format('Y-m-d H:i:s', $visit->fact_start_dttm);
                        if ($start_dttm !== false) {
                            $value = $start_dttm->getTimestamp();
                        }
                    }
                    $param['date_value'] = $value;
                    break;
                case 'P4_Ownername':
                    $param['char_value'] = empty($owner['jur_name']) ? $owner['fullname'] : ($owner['jur_name'] . ' (' . $owner['fullname'] . ')');
                    break;
                case 'P5_Ownercontact':
                    $param['char_value'] = $owner['phone'];
                    break;
                case 'P5_Owneraddres':
                    // уточнить - возможно устаревшее описание ('Адрес владельца строкой (Owner.id_jur_address)...')
                    // уточнить - на данный момент есть id_address и id_fact_address
                    // уточнить - нужен ли район и округ в строковом представлении адреса
                    $param['char_value'] = $owner['address'];
                    break;
                case 'P6_Speciesname':
                    $param['char_value'] = $pet->species->name;
                    break;
                case 'P7_Breedname':
                    $param['char_value'] = $pet->breeds ? $pet->breeds->name : null;
                    break;
                case 'P8_Petsex':
                    $param['char_value'] = $pet->sex;
                    break;
                case 'P9_Petname':
                    $param['char_value'] = $pet->name;
                    break;
                case 'P10_Petbirthday':
                    // в связи с разногласиями с аналитиками поставим тупо дату рождения животного
                    $value = null;
                    if (!empty($pet->birthday)) {
                        $birth_date = date_create_from_format('Y-m-d', $pet->birthday);
                        if ($birth_date !== false) {
                            $value = $birth_date->getTimestamp();
                        }
                    }
                    $param['date_value'] = $value;
                    break;
                case 'P19_Petregexpiredate':
                    $value = null;
                    if (!empty($pet->reg_expire_date)) {
                        $reg_expire_date = date_create_from_format('Y-m-d', $pet->reg_expire_date);
                        if ($reg_expire_date !== false) {
                            $value = $reg_expire_date->getTimestamp();
                        }
                    }
                    $param['date_value'] = $value;
                    break;
                case 'P33_SpecialistFIO':
                    $param['char_value'] = trim($specialist['f_fio'] . ' ' . $specialist['i_fio'] . ' ' . $specialist['o_fio']);
                    break;
                case 'P0_Petregnum':
                    $param['char_value'] = self::findRegNumber($pet->id);
                    break;
                case 'P0_Petregdate':
                    $value = self::findRegDate($pet->id);;
                    if ($value !== null) {
                        $reg_date = date_create_from_format('Y-m-d', $value);
                        if ($reg_date !== false) {
                            $value = $reg_date->getTimestamp();
                        }
                    }
                    $param['date_value'] = $value;
                    break;

                case 'P0_Petchpidentificationcode':
                case 'P0_Petlabelidentificationcode':
                case 'P0_Petstampidentificationcode':
                    // проставляем значения ниже
                    break;
                case 'P0_PetRabiesVaccinationDate':
                    // Дата вакцинации животного
                    $value = null;
                    $lastVaccination = self::findLastPetVaccination($pet->id);
                    if (!empty($lastVaccination)) {
                        $vaccination_date = date_create_from_format('Y-m-d H:i:s', $lastVaccination['date'] . ' 00:00:00');
                        if ($vaccination_date !== false) {
                            $value = $vaccination_date->getTimestamp();
                        }
                    }
                    $param['date_value'] = $value;
                    break;
                //
                case 'P13_Servicetext':
                    if (!$visitServiceTmc) {
                        continue 2;
                    }
                    $param['char_value'] = $visitServiceTmc->tmc->name;
                    break;
                case 'P0_Inventorynumber':
                    if (!$petVaccination) {
                        continue 2;
                    }
                    $param['char_value'] = $petVaccination->batch;
                    break;
                case 'P15_Vacexpirationdate':
                    if (!$petVaccination) {
                        continue 2;
                    }
                    $expiryDate = false;
                    if ($petVaccination->expiry_date) {
                        $expiryDate = date_create_from_format('Y-m-d H:i:s', $petVaccination->expiry_date . ' 00:00:00');
                    }

                    $param['date_value'] = $expiryDate !== false ? $expiryDate->getTimestamp() : null;
                    break;
                case 'P13_ListOfDiseases':
                    if (!$petVaccination) {
                        continue 2;
                    }
                    $param['char_value'] = implode(', ', array_map(function ($diseas) {
                            return $diseas->name;
                        }, $petVaccination->vaccine->diseases)
                    );
                    break;

                default:
                    break;
            }

            if (count($param) == 2) {
                continue;
            }

            Yii::$app->db->createCommand()
                ->insert(VisitParamValues::tableName(), $param)
                ->execute();
        }

        //2
        $identificationData = self::findPetIdentification($pet->id);
        if (empty($identificationData)) {
            return;
        }

        $identificationTypes = self::identificationTypes();
        $typesMap = [
            'чип'    => 'P0_Petchpidentificationcode',
            'бирка'  => 'P0_Petlabelidentificationcode',
            'клеймо' => 'P0_Petstampidentificationcode',
        ];

        foreach ($identificationData as $id_ident_type => $identifications) {
            if (empty($identifications) || !is_array($identifications)) {
                continue;
            }
            $typeName = ArrayHelper::getValue($identificationTypes, $id_ident_type);
            if (empty($typeName) || !array_key_exists($typeName, $typesMap)) {
                continue;
            }
            $typeTechName = $typesMap[$typeName];
            if (!array_key_exists($typeTechName, $visitParams)) {
                continue;
            }
            // если несколько - берем первый по порядку,
            // он либо будет иметь main_flag true, либо самым новым по дате создания.
            $param = [
                'id_visit'             => $visit->id,
                'id_param'             => $visitParams[$typeTechName]['id'],
                'id_visitservice'      => $visitGovService ? $visitGovService->id : null,
                'id_visit_service_tmc' => $visitServiceTmc ? $visitServiceTmc->id : null,
                'id_pet'               => $pet->id,
                'char_value'           => $identifications[0]['identification_code'],
            ];
            Yii::$app->db->createCommand()
                ->insert(VisitParamValues::tableName(), $param)
                ->execute();
        }
    }

    /**
     * Удаляем сохраненные файлы
     *
     * @param int $id
     */
    private function removeFiles($visitGovServiceId)
    {
        /* @var $files \app\modules\v1\models\FileResource[] */
        $files = FileResource::find()
            ->where([
                'entity_id'   => $visitGovServiceId,
                'entity_type' => 'visits_gov_service'
            ])
            ->all();

        if (!empty($files)) {
            foreach ($files as $file) {
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                    Yii::error('Failed to delete pdf file for visitservice_id ' . $visitGovServiceId . "\n" . $e->getMessage());
                }
            }
        }
    }

    /**
     * Удаление данных
     *
     * @param $visitGovServiceId
     */
    private function deleteByServiceId($visitGovServiceId)
    {
        VisitParamValues::deleteAll(['id_visitservice' => $visitGovServiceId]);

        return $this;
    }

    /**
     * @param string $indexBy
     * @return array
     */
    private function findVisitParams($indexBy = 'id')
    {
        $q = new Query();
        $q->select('*')
            ->from(Params::tableName())
            ->where(['[[visit_flag]]' => true]);

        $q->orderBy(['[[id]]' => SORT_ASC])
            ->indexBy($indexBy);

        return $q->all();
    }

    /**
     * @param int $id_owner
     * @return array
     */
    private static function findVisitOwner($id_owner)
    {
        $q = (new Query())
            ->select('p.*')
            ->addSelect('[[cct]].[[phone]], [[cct]].[[owner_id]]')
            ->addSelect('[[addr]].[[full_address]] as address')
            ->from(PetOwners::tableName() . ' p')
            ->innerJoin(PetsToOwner::tableName() . ' pto', '[[pto]].[[id_owner]] = [[p]].[[id]]')
            ->leftJoin(
                [
                    'cct' => (new Query())
                        ->select('[[c]].[[name]] phone, [[c]].[[entity_id]] owner_id')
                        ->from(Contacts::tableName() . ' c')
                        ->leftJoin(ContactTypes::tableName() . ' ct', '[[ct]].[[id]] = [[c]].[[id_contact_type]]')
                        ->where([
                            '[[c]].[[entity_type]]' => ContactTypes::ENTITY_TYPE_PET_OWNER,
                            '[[c]].[[main_flag]]'   => true,
                        ])
                        ->andWhere(['[[ct]].[[type]]' => ContactTypes::TYPE_PHONE]),
                    '[[cct]].[[entity_id]] = [[p]].[[id]]',
                ],
                '[[p]].[[id]] = [[cct]].[[owner_id]]'
            )
            ->leftJoin(FiasAddresses::tableName() . ' addr', '[[addr]].[[id]] = [[p]].[[id_fias_address]]')
            ->where([
                '[[p]].[[id]]' => $id_owner,
            ]);

        return $q->one();
    }

    /**
     * @param int $id_visit
     * @return array
     */
    private static function findSpecialist($id_visit)
    {
        $q = new Query();
        $q->select('sp.*')
            ->addSelect(Specialists::personalAttributes())
            ->addSelect('[[vs]].[[id_visit]]')
            ->from(Specialists::tableName() . ' sp')
            ->leftJoin(VisitsSpecialists::tableName() . ' vs', '[[vs]].[[id_specialist]] = [[sp]].[[id]]')
            ->leftJoin(UserModel::tableName() . ' u', '[[sp]].[[id_user]] = [[u]].[[id]]')
            ->where(['[[vs]].[[id_visit]]' => $id_visit]);

        return $q->one();
    }

    /**
     * @param int $id_organization
     * @return array
     */
    private static function findOrganization($id_organization)
    {
        $q = new Query();
        $q->select('*')
            ->from(Organizations::tableName())
            ->where(['id' => $id_organization]);

        return $q->one();
    }

    /**
     * @return array
     */
    private static function identificationTypes()
    {
        $rows = (new Query())
            ->from(IdentificationTypes::tableName())
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return ArrayHelper::map($rows, 'id', 'name');
    }

    /**
     * VETAIS-2038
     * № и от – необходимо брать данные из регистрационного документа животного
     *
     * @param int $id_pet
     * @return string
     */
    private static function findRegNumber($id_pet)
    {
        $row = (new Query())
            ->from(RegCertificates::tableName())
            ->where(['id_pet' => $id_pet])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        return empty($row) ? null : $row['number'];
    }

    /**
     * VETAIS-2038
     * № и от – необходимо брать данные из регистрационного документа животного
     *
     * @param int $id_pet
     * @return string
     */
    private static function findRegDate($id_pet)
    {
        $row = (new Query())
            ->from(RegCertificates::tableName())
            ->where(['id_pet' => $id_pet])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        return empty($row) ? null : $row['date'];
    }

    /**
     * @param int $id_pet
     * @return array
     */
    private static function findPetIdentification($id_pet)
    {
        $q = new Query();
        $q->select('*')
            ->from(PetIdentification::tableName())
            ->where(['id_pet' => $id_pet])
            ->orderBy([
                'main_flag'  => SORT_DESC,
                'created_at' => SORT_DESC,
            ]);

        $rows = $q->all();

        return ArrayHelper::index($rows, null, 'id_ident_type');
    }

    /**
     * @param int $id_pet
     * @return array
     */
    private static function findLastPetVaccination($id_pet)
    {
        $q = new Query();
        $q->select('*')
            ->from(PetRabiesVaccination::tableName())
            ->where(['id_pet' => $id_pet])
            ->orderBy([
                'date' => SORT_DESC,
            ])
            ->limit(1);

        return $q->one();
    }
}
