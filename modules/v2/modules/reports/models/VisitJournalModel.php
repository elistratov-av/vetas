<?php

namespace app\modules\v2\modules\reports\models;

use app\common\helpers\DateHelper;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\models\db\Breeds;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\DescriptionTypes;
use app\models\db\FiasAddresses;
use app\models\db\GovServices;
use app\models\db\PetIdentification;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\RegCertificates;
use app\models\db\Reports;
use app\models\db\Specialists;
use app\models\db\Species;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\Users;
use app\models\db\VisitDescriptions;
use app\models\db\VisitPets;
use app\models\db\VisitPrice;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitServiceTmcPet;
use app\models\db\VisitsGovServices;
use app\models\db\VisitsSpecialists;
use app\modules\admin\models\BalanceFlow;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class VisitJournalModel
 *
 * @package app\modules\v2\modules\reports\models
 * @link https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=134644332
 */
class VisitJournalModel extends Model
{
    private const DESCRIPTION_TYPES_FOR_VIEW = [
        'Дата заболевания' => 'date_of_illness',
        'Анамнез' => 'anamnesis',
        'Предварительный диагноз' => 'preliminary_diagnosis',
        'Рекомендации' => 'recommendations',
        'Заключительный диагноз' => 'final_diagnosis',
        'Данные клинического осмотра' => 'clinical_signs',
        'Схема лечения' => 'treatment_regimen',
        'Заключение' => 'final',
    ];

    /** @var array */
    public $idOrganizations = [];

    /** @var array */
    public $filter = [];

    /** @var array */
    public $order = [];

    /**
     * @return Query
     * @throws NotSupportedException
     * @throws BadRequestHttpException
     */
    public function findRecords(): Query
    {
        $query = (new Query())
            ->select([
                'visit_id' => 'v.id', // для печати ПФ приема
                /* -- Общее ----------------------------------- */
                'visit_date' => 'v.fact_start_dttm',
                'visit_date_safe' => 'v.start_dttm', // используется для расчёта возраста животного
                'visit_type' => 'v.type',
                'service_id' => 'vgs.id',
                'service_count' => new Expression(
                    "CASE
                    WHEN v.variety = 'BROOD' AND gs.for_broods = 'HEAD' THEN 1
                    ELSE vgs.count
                    END"),
                'service_name' => 'gs.name',
                'service_price' => new Expression(
                    "CASE
                     WHEN v.variety = 'BROOD' AND gs.for_broods = 'HEAD' THEN vgs.price_with_discount::numeric(8, 2)
                     ELSE (vgs.count * vgs.price_with_discount)::numeric(8, 2)
                     END"),
                'visit_price' => 'vp.price_with_discount',
                'specialist_full_name' => 'u.fullname',
                /* -- Владелец -------------------------------- */
                'client_id' => 'c.id',
                'client_full_name' => 'c.fullname',
                'owner_id' => 'po.id',
                'owner_full_name' => 'po.fullname',
                'address' => 'fa.full_address',
                'phones' => 'phones.numbers',
                'emails' => 'emails.emails',
                /* -- Животное -------------------------------- */
                'pet_id' => 'p.id',
                'pet_species' => 'ps.name',
                'pet_sex' => 'p.sex',
                'pet_breed' => 'b.name',
                'pet_name' => 'p.name',
                'pet_age' => 'p.birthday',
                'pet_reg_num' => 'rc.number',
                'pet_identification_code' => 'pi.identification_code',
                'pet_rabies_vaccination_date' => 'prv.date',
                /* -- Вакцина ---------------------------------- */
                'tmc_name' => 'tmc.name',
                'tmc_inventory'=> 'tb.inventory_number',
            ])
            ->from(VisitsGovServices::tableName() . ' vgs')
            ->innerJoin(GovServices::tableName() . ' gs', 'gs.id = vgs.id_service')
            ->leftJoin('visit_service_pet sp', 'sp.id_visits_gov_service = vgs.id')
            ->innerJoin(Visits::tableName() . ' v', 'vgs.id_visit = v.id')
            ->leftJoin(VisitPrice::tableName() . ' vp', 'vp.id_visit = vgs.id_visit')
            ->innerJoin(VisitsSpecialists::tableName() . ' vs', 'vs.id_visit = v.id')
            ->innerJoin(Specialists::tableName() . ' s', 's.id = vs.id_specialist')
            ->innerJoin(Users::tableName() . ' u', 'u.id = s.id_user')
            ->innerJoin(PetOwners::tableName() . ' c', 'c.id = v.id_owner')
            ->leftJoin(VisitPets::tableName() . ' vtp', 'vtp.id_visit = v.id AND sp.id_pet isnull')
            ->innerJoin(Pets::tableName() . ' p', 'p.id = coalesce(sp.id_pet, vtp.id_pet)')
            ->innerJoin(Species::tableName() . ' ps', 'ps.id = p.id_species')
            ->leftJoin(Breeds::tableName() . ' b', 'b.id = p.id_breed')
            ->leftJoin(PetsToOwner::tableName() . ' pto', 'pto.id_pet = p.id AND pto.id_owner_type = 1')
            ->leftJoin(PetOwners::tableName() . ' po', 'pto.id_owner = po.id')
            ->leftJoin(RegCertificates::tableName() . ' rc', 'rc.id_pet = p.id')
            ->leftJoin(PetIdentification::tableName() . ' pi', 'pi.id_pet = p.id AND pi.main_flag = TRUE')
            ->leftJoin(FiasAddresses::tableName() . ' fa', 'fa.id = vtp.id_fias_address')
            ->leftJoin(['emails' => self::getSubQueryForEmails()], 'emails.entity_id = po.id')
            ->leftJoin(['phones' => self::getSubQueryForPhones()], 'phones.id_pet = p.id')
            ->leftJoin(['prv' => self::getSubQueryForDateRabiesVaccinations()], 'p.id = prv.id_pet')
            ->leftJoin(VisitServiceTmcPet::tableName() . ' vsp', 'vsp.id_pet = p.id AND vsp.id_visits_gov_service = vgs.id')
            ->leftJoin(VisitServiceTmc::tableName() . ' vt', 'vsp.id_visit_service_tmc = vt.id')
            ->leftJoin(TmcBase::tableName() . ' tmc', 'tmc.id = vt.id_tmc')
            ->leftJoin(Balance::tableName() . ' tb', 'tb.id_tmc = vt.id_tmc AND vt.id_balance_tmc = tb.id')
            ->where([
                'AND',
                ['IN', 'v.status', [VisitStatus::FINISHED, VisitStatus::FINISHED_UNPAYED]],
                ['IN', 'v.id_organization', $this->idOrganizations],
            ]);

        self::addFilters($query, $this->filter);
        self::addOrder($query, count($this->order) ? $this->order : [['by' => 'visit_date', 'isReverse' => true]]);

        return (new Query())
            ->select([
                'tbl.*',
                'npp' => new Expression('ROW_NUMBER() OVER()'),
            ])
            ->from(['tbl' => $query]);
    }

    /**
     * Расчитывает вычисляемые поля с итоговыми числовыми значениями
     *
     * @return array
     * @see findRecords()
     */
    public function calculateStat(): array
    {
        $q = $this->findRecords();

        if (!$q->count()) {
            return [
                'visit_count' => 0,
                'visit_sum' => null,
                'service_count' => 0,
                'service_sum' => sprintf("%.2f", 0),
            ];
        }

        ['total_count' => $visitsCount, 'total_sum' => $visitsSum] = (new Query())
            ->select(['total_count' => new Expression('COUNT(visit_id)'), 'total_sum' => new Expression('SUM(visit_prices)')])
            ->from(['tmp' => (clone $q)->select(['visit_id', 'visit_prices' => new Expression('MAX(visit_price)')])->groupBy(['visit_id'])])
            ->one();

//        ['total_count' => $servicesCount, 'total_sum' => $servicesSum] = (clone $q)
//            ->select(['total_count' => new Expression('SUM(service_count)'), 'total_sum' => new Expression('SUM(service_price)')])
//            ->one();

        return [
            'visit_count' => (int)$visitsCount,
            'visit_sum' =>$visitsSum,
            'service_count' => false, // корректно не считается с текущим вариантом привязки услуга-животное - отключили временно
            'service_sum' => false, // корректно не считается с текущим вариантом привязки услуга-животное - отключили временно
        ];
    }

    /**
     * Добавляет к данным вывода данные приема
     *
     * @param array $journalRows
     * @return array
     */
    public static function addVisitDescriptions(array $journalRows): array
    {
        if (!count($journalRows)) {
            return $journalRows;
        }
        $result = [];
        $visitIds = array_unique(ArrayHelper::getColumn($journalRows, 'visit_id'));
        $visitDescriptions = self::getVisitDescriptions($visitIds);
        $visitsAdditionalResearch = self::getAdditionalResearch($visitIds);

        foreach ($journalRows as $row) {
            ['visit_id' => $visitId, 'pet_id' => $petId, 'visit_date' => $visitDate, 'visit_date_safe' => $visitDateSave, 'pet_sex' => $petSex, 'pet_age' => $petAge] = $row;
            unset($row['visit_date_safe']);
            $result[] = array_merge($row, $visitDescriptions[$visitId][$petId] ?? [], [
                'pet_sex' => Pets::SEX_NAMES[$petSex] ?? '',
                'pet_age' => DateHelper::ageAtDate($petAge, $visitDate ?: $visitDateSave),
                'additional_research' => $visitsAdditionalResearch[$visitId][$petId] ?? null,
            ]);
        }

        return $result;
    }

    /**
     * Добавление фильтров к запросу
     *
     * @param Query $query
     * @param array $filter
     * @throws NotSupportedException
     * @throws BadRequestHttpException
     */
    private static function addFilters(Query $query, array $filter): void
    {
        $fullTrimValidator = new FullTrimValidator();
        // типы услуг -----------------------------------------------
        if (!empty($filter['service_type_ids'])) {
            $query
                ->andWhere(['IN', 'gs.id_service_type', $filter['service_type_ids']]);
        }

        // услуги ---------------------------------------------------
        if (!empty($filter['service_ids'])) {
            $query->andWhere(['IN', 'vgs.id_service', $filter['service_ids']]);
        }

        // специалист -----------------------------------------------
        if (!empty($filter['specialist_ids'])) {
            $query->andWhere(['s.id' => $filter['specialist_ids']]);
        }
        // вакцина -----------------------------------------------
        if (!empty($filter['vaccines_ids'])) {
            $query->andWhere(['tmc.id' => $filter['vaccines_ids']]);
        }
        // дата начала ----------------------------------------------
        if (!empty($filter['date_from'])) {
            $query->andWhere(['>=', new Expression('coalesce(v.fact_start_dttm::date, v.start_dttm::date, v.created_at::date)'), $filter['date_from']]);
        }

        // дата по -------------------------------------------
        if (!empty($filter['date_to'])) {
            $query->andWhere(['<=', new Expression('coalesce(v.fact_start_dttm::date, v.start_dttm::date, v.created_at::date)'), $filter['date_to']]);
        }

        // владелец/представитель -----------------------------------
        if (!empty($filter['owner_id'])) {
            $query->andWhere(['v.id_owner' => $filter['owner_id']]);
        } elseif (!empty($filter['owner_name'])) {
            $query->andWhere([
                'OR',
                ['ILIKE', 'c.fullname', $fullTrimValidator->validateValue($filter['owner_name'])],
                ['ILIKE', 'c.jur_name', $fullTrimValidator->validateValue($filter['owner_name'])],
            ]);
        }

        // виды животных --------------------------------------------
        if (!empty($filter['species_ids'])) {
            $query->andWhere(['IN', 'ps.id', $filter['species_ids']]);
        }

        // кличка животного -----------------------------------------
        if (!empty($filter['pet_name'])) {
            $query->andWhere(['ilike', 'p.name', $fullTrimValidator->validateValue($filter['pet_name'])]);
        } elseif (!empty($filter['pet_id'])) {
            $query->andWhere(['p.id' => $filter['pet_id']]);
        }

        // способ идентификации -------------------------------------
        if (!empty($filter['pet_identification_type_id'])) {
            $query->andWhere(['pi.id_ident_type' => $filter['pet_identification_type_id']]);
        }

        // идентификационный номер (чип) ----------------------------
        if (!empty($filter['pet_identification_code'])) {
            $query->andWhere(['pi.identification_code' => $filter['pet_identification_code']]);
        }

        // регистрационный номер ------------------------------------
        if (!empty($filter['pet_reg_certificates_number'])) {
            if (!is_int($filter['pet_reg_certificates_number'])) {
                throw new BadRequestHttpException('Параметр pet_reg_certificates_number должен быть числом');
            }
            $query->andWhere(['rc.number' => $filter['pet_reg_certificates_number']]);
        }

        // номер приема -----------------------------------------------
        if (ArrayHelper::getValue($filter, 'visit_id')) {
            $query->andWhere(['v.id' => $filter['visit_id']]);
        }

        // места проведения приемов ---------------------------------
        if (!empty($filter['visit_types'])) {
            $query->andWhere(['IN', 'v.type', $filter['visit_types']]);
        }

        // тип приёма -----------------------------------------------
        $variety = $filter['variety'] ?? null;
        if ($variety && in_array($variety, Visits::varieties(), true)) {
            $query->andWhere(['v.variety' => $filter['variety']]);
        }

        // источники записи -----------------------------------------
        if (!empty($filter['visit_channels'])) {
            $query->andWhere(['v.channel' => $filter['visit_channels']]);
        }

        // скидки ---------------------------------------------------
        if (!empty($filter['visit_discount_types'])) {
            $query->andWhere(['vp.id_discount' => $filter['visit_discount_types']]);
        }

        // кол-во животных в приеме ---------------------------------
        if (!empty($filter['pets_count'])) {
            $query
                ->innerJoin(['pc' => self::getSubQueryForPetsCount()], 'pc.id_visit = vgs.id_visit')
                ->andWhere(['pc.count' => $filter['pets_count']]);
        }
    }

    /**
     * Добавление сортировку к запросу
     *
     * @param Query $query
     * @param array $order
     */
    private static function addOrder(Query $query, array $order): void
    {
        $orderBy = [];
        foreach ($order as ['by' => $by, 'isReverse' => $desc]) {
            $orderBy[$by] = $desc ? SORT_DESC : SORT_ASC;
        }
        if (!count($orderBy)) {
            return;
        }
        $query->orderBy($orderBy);
    }

    /**
     * Подзапрос для номеров телефонов (владельца и представителей)
     *
     * @return Query
     */
    private static function getSubQueryForPhones(): Query
    {
        return (new Query())
            ->select([
                'id_pet' => 'pto.id_pet',
                'numbers' => new Expression("string_agg(c.name, ' ')"),
            ])
            ->from(Contacts::tableName() . ' c')
            ->innerJoin(PetsToOwner::tableName() . ' pto', 'pto.id_owner = c.entity_id')
            ->innerJoin(ContactTypes::tableName() . ' ct', 'ct.id = c.id_contact_type')
            ->where([
                'c.entity_type' => Contacts::ENTITY_TYPE_PET_OWNER,
                'ct.type' => ContactTypes::TYPE_PHONE,
            ])
            ->groupBy('pto.id_pet');
    }

    /**
     * Подзапрос для номеров телефонов
     *
     * @return Query
     */
    private static function getSubQueryForEmails(): Query
    {
        return (new Query())
            ->select([
                'entity_id' => 'c.entity_id',
                'emails' => new Expression("string_agg(c.name, ' ')"),
            ])
            ->from(Contacts::tableName() . ' c')
            ->innerJoin(ContactTypes::tableName() . ' ct', 'ct.id = c.id_contact_type')
            ->where([
                'c.entity_type' => Contacts::ENTITY_TYPE_PET_OWNER,
                'ct.type' => ContactTypes::TYPE_EMAIL,
            ])
            ->groupBy('entity_id');
    }

    /**
     * Подзапрос для даты вакцинации от бешенства
     *
     * @return Query
     */
    private static function getSubQueryForDateRabiesVaccinations(): Query
    {
        return (new Query())
            ->select([
                'id_pet',
                new Expression('MAX(date) AS date'),
            ])
            ->from(PetRabiesVaccination::tableName())
            ->groupBy('id_pet');
    }

    /**
     * Подзапрос для кол-ва животных в приеме
     *
     * @return Query
     */
    private static function getSubQueryForPetsCount(): Query
    {
        return (new Query())
            ->select([
                'id_visit',
                'COUNT(*)',
            ])
            ->from(VisitPets::tableName())
            ->groupBy('id_visit');
    }

    /**
     * Данные приема
     *
     * @param array $visitIds
     * @return array
     */
    private static function getVisitDescriptions(array $visitIds): array
    {
        $visitDescriptionTypes = self::getVisitDescriptionTypes();
        $visitDescriptions = (new Query())
            ->select(['id_visit', 'id_pet', 'id_description_type', 'description'])
            ->from(VisitDescriptions::tableName())
            ->where([
                'AND',
                ['IN', 'id_visit', $visitIds],
                ['IN', 'id_description_type', ArrayHelper::getColumn($visitDescriptionTypes, 'id')],
            ])
            ->all();

        return self::prepareVisitDescription($visitDescriptions, $visitDescriptionTypes);
    }

    /**
     * @return array
     */
    private static function getVisitDescriptionTypes(): array
    {
        return (new Query())
            ->select(['id', 'name'])
            ->from(DescriptionTypes::tableName())
            ->where([
                'AND',
                ['entity_type' => 'visit'],
                ['name' => array_keys(self::DESCRIPTION_TYPES_FOR_VIEW)],
            ])
            ->all();
    }

    /**
     * @param array $visitDescriptions
     * @param array $visitDescriptionTypes
     * @return array
     */
    private static function prepareVisitDescription(array $visitDescriptions, array $visitDescriptionTypes): array
    {
        $visitDescriptionTypes = ArrayHelper::map($visitDescriptionTypes, 'id', 'name');
        $result = [];
        foreach ($visitDescriptions as $visitDescription) {
            $visitDescriptionTypeName = self::DESCRIPTION_TYPES_FOR_VIEW[$visitDescriptionTypes[$visitDescription['id_description_type']]];
            $result[$visitDescription['id_visit']][$visitDescription['id_pet']][$visitDescriptionTypeName] = $visitDescription['description'];
        }

        return $result;
    }

    /**
     * Формирование списка доп. исследований по приемам
     *
     * @param array $visitIds
     * @return array
     */
    private static function getAdditionalResearch(array $visitIds): array
    {
        return ArrayHelper::map(
            (new Query())
                ->select([
                    'id_visit',
                    'id_pet',
                    new Expression('string_agg(name, \', \') AS additional_research'),
                ])
                ->from([
                    'ar' => (new Query())
                        ->select([
                            'vgs.id_visit',
                            'sp.id_pet',
                            new Expression('(CASE WHEN gs.briefname IS NULL THEN gs.name ELSE gs.briefname END) AS name'),
                        ])
                        ->from(VisitsGovServices::tableName() . ' vgs')
                        ->innerJoin(GovServices::tableName() . ' gs', 'gs.id = vgs.id_service AND gs.com_class_journal = \'additional\'')
                        ->innerJoin('visit_service_pet sp', 'sp.id_visits_gov_service = vgs.id')
                        ->where(['IN', 'vgs.id_visit', $visitIds]),
                ])
                ->groupBy('id_visit, id_pet')
                ->all(),
            'id_pet',
            'additional_research',
            'id_visit'
        );
    }

    /**
     * Шапка таблицы
     *
     * @return array
     */
    public static function getMeta(): array
    {
        return [
            [
                'label' => '№ п/п',
                'prop_path' => 'npp',
                'prop_type' => 'string',
            ],
            [
                'label' => 'номер приема',
                'prop_path' => 'visit_id',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Дата и время приёма',
                'prop_path' => 'visit_date',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Услуга',
                'prop_path' => 'service_name',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Стоимость услуги',
                'prop_path' => 'service_price',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Кол-во услуг',
                'prop_path' => 'service_count',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Общая стоимость приёма',
                'prop_path' => 'visit_price',
                'prop_type' => 'string',
            ],
            [
                'label' => 'ФИО ветеринарного специалиста',
                'prop_path' => 'specialist_full_name',
                'prop_type' => 'string',
            ],
            [
                'label' => 'ФИО обратившегося',
                'prop_path' => 'client_full_name',
                'prop_type' => 'string',
            ],
            [
                'label' => 'ФИО владельца',
                'prop_path' => 'owner_full_name',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Адрес содержания животного',
                'prop_path' => 'address',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Контактный телефон',
                'prop_path' => 'phones',
                'prop_type' => 'string',
            ],
            [
                'label' => 'E-mail',
                'prop_path' => 'emails',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Вид животного',
                'prop_path' => 'pet_species',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Пол животного',
                'prop_path' => 'pet_sex',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Порода животного',
                'prop_path' => 'pet_breed',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Кличка животного',
                'prop_path' => 'pet_name',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Возраст животного',
                'prop_path' => 'pet_age',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Номер регистрационного удостоверения',
                'prop_path' => 'pet_reg_num',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Идентификационный номер',
                'prop_path' => 'pet_identification_code',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Дата вакцинации против бешенства',
                'prop_path' => 'pet_rabies_vaccination_date',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Дата заболевания',
                'prop_path' => 'date_of_illness',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Анамнез',
                'prop_path' => 'anamnesis',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Предварительный диагноз',
                'prop_path' => 'preliminary_diagnosis',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Данные клинического осмотра',
                'prop_path' => 'clinical_signs',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Схема лечения',
                'prop_path' => 'treatment_regimen',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Дополнительные исследования',
                'prop_path' => 'additional_research',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Рекомендации',
                'prop_path' => 'recommendations',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Заключительный диагноз',
                'prop_path' => 'final_diagnosis',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Заключение',
                'prop_path' => 'final',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Наименование',
                'prop_path' => 'tmc_name',
                'prop_type' => 'string',
            ],
            [
                'label' => 'Серийный номер',
                'prop_path' => 'tmc_inventory',
                'prop_type' => 'string',
            ],
        ];
    }
}
