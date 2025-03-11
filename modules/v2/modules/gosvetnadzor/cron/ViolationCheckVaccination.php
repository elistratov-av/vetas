<?php


namespace app\modules\v2\modules\gosvetnadzor\cron;

use app\common\models\VisitStatus;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\ServiceTypes;
use app\models\db\tmc\TmcVaccine;
use app\models\db\Violation;
use app\models\db\ViolationType;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitServiceTmc;
use app\models\db\Species;
use yii\base\InvalidConfigException;
use yii\console\Exception;
use yii\db\Expression;
use yii\db\Query;

/**
 * Автоматическая проверка нарушений по вакцинациям и заведение их в системе
 *
 * @package app\modules\v2\modules\gosvetnadzor\cron
 */
class ViolationCheckVaccination extends BaseViolationCheck
{
    /**
     * Город в котором ищем
     */
    const QUERY_CONSTANTS_CITY_SEARCH = 'Москва';

    /**
     * Параметр, на который обращаем внимание при поиске вакцин в данных визита
     */
    const QUERY_CONSTANTS_PARAMS_TECH_NAME = 'P15_Vacexpirationdate';

    /**
     * Название заболевания БЕШЕНСТВО в справочнике
     */
    const QUERY_CONSTANTS_DISEASES_NAME_RABIES = 'Бешенство (Rabies)';

    /**
     * Название заболевания Лептоспироз в справочнике
     */
    const QUERY_CONSTANTS_DISEASES_NAME_LEPTOSPIROSIS = 'Лептоспироз';

    /**
     * Комментарий к нарушению по бешенству
     */
    const COMMENT_TO_RABIES_VIOLATION = 'Автоматическая проверка на наличие вакцинации от бешенства';

    /**
     * Комментарий к нарушению по лептоспирозу
     */
    const COMMENT_TO_LEPTOSPIROSIS_VIOLATION = 'Автоматическая проверка на наличие вакцинации от лептоспироза';

    /**
     * Размер пачки животных для обработки
     */
    const QUERY_PETS_BATCH_SIZE = 20;

    /**
     * Создает нарушения по вакцинации от бешенства
     * (!!! Этот запрос - большой костыль, в виду того что оригинальный запрос построенный на подзапросах улетал в стратосферу !!!)
     * Создаётся два списка животных по условиям:
     * 1) Список животных к которым нужно применять проверку
     * 2) Список животных у которых не обнаружено нарушений по критериям
     * Исключая животных из первого списка присутствующих во втором - получаем тех животных, которым нужно выставить нарушение
     * Критерии в обоих списках описаны в комментариях к обоим квери
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function checkRabiesVaccinationAndCreateViolations()
    {
        // Тип нарушения - нарушение сроков вакцинации
        $violation_type_id = $this->getViolationTypeId(ViolationType::V03_VACCINATION_DEADLINE);

        // БЕШЕНСТВО
        $rabies_disease_id = $this->getDiseaseId(self::QUERY_CONSTANTS_DISEASES_NAME_RABIES);

        $query = $this->buildQueryRabiesVaccination(
            $rabies_disease_id, $violation_type_id
        );

        $ids = $query->asArray()->column();

        $cat = Species::TECH_NAME_CAT;
        $dog = Species::TECH_NAME_DOG;

        /*
         * Критерии животных которым нужно выставлять нарушения
         */
        $pets = Pets::find()
            ->select('pets.id id')
            ->with('owner')
            ->distinct()
            ->leftJoin('visits_gov_services', 'pets.id = visits_gov_services.id_pet')
            ->leftJoin('visits', 'visits.id = visits_gov_services.id_visit')
            ->leftJoin('pets_to_owner AS pto', 'pets.id = pto.id_pet')
            ->leftJoin('pet_owner_type AS pot', 'pto.id_owner_type = pot.id')
            // Проверяем по адресу хозяина is_owner = true
            ->leftJoin('pet_owners po', 'pto.id_owner = po.id AND pot.is_owner = true')
            ->leftJoin('fias_addresses AS fact_fias', 'po.id_fact_fias_address = fact_fias.id')
            ->leftJoin('fias_addresses AS fias', 'po.id_fias_address = fias.id')
            // Только для Кошек и Собак
            ->innerJoin('species', "pets.id_species = species.id AND species.tech_name in ('$cat','$dog')")
            // Животное не снято с учёта
            ->where(['pets.id_reg_expire_reason' => null])
            // Запись животного не является дублем
            ->andWhere(['pets.id_main_pet' => null])
            // Только животным, которые прошли хотя бы одно обследование
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            // Проживающие в Москве
            ->andWhere(['OR',
                ['ILIKE', 'fact_fias.full_address', self::QUERY_CONSTANTS_CITY_SEARCH],
                ['ILIKE', 'fias.full_address', self::QUERY_CONSTANTS_CITY_SEARCH]])
            ->indexBy('id');

        $violationRows = [];
        foreach ($pets->batch(self::QUERY_PETS_BATCH_SIZE) as $rows) {
            foreach ($rows as $row) {
                if ($row->owner && !in_array($row->id, $ids)) array_push($violationRows, $row);
            }
        }

        if (count($violationRows) > 0) {
            $violationRowsChunks = array_chunk($violationRows, self::QUERY_PETS_BATCH_SIZE);
            foreach($violationRowsChunks as $chunk) {
                Violation::getDb()->beginTransaction();

                foreach ($chunk as $row) {
                    $violation = new Violation([
                        'state' => Violation::STATE_ON_VERIFY,
                        'id_owner' => $row->owner->id,
                        'id_pet' => $row->id,
                        'id_type' => $violation_type_id,
                        'id_disease' => $rabies_disease_id,
                        'date_violation' => new Expression('now()'),
                        'comment' => self::COMMENT_TO_RABIES_VIOLATION,
                    ]);

                    if (!$violation->save()) {
                        $errors = $violation->getErrorSummary(true);
                        throw new Exception(empty($errors) ? 'Ошибка при сохранении нарушения' : implode("\n", array_values($errors)));
                    }
                }

                Violation::getDb()->transaction->commit();
            }
        }
    }

    /**
     * @param $disease_id
     * @param $violation_type_id
     * @return \app\models\db\PetsQuery|\yii\db\ActiveQuery
     */
    protected function buildQueryRabiesVaccination($disease_id, $violation_type_id)
    {
        return Pets::find()
            ->select('pets.id as id')
            ->from('pets')
            ->distinct()
            ->leftJoin('pets_to_owner AS pto', 'pets.id = pto.id_pet')
            ->leftJoin('pet_owner_type AS pot', 'pto.id_owner_type = pot.id')
            ->leftJoin('pet_owners po', 'pto.id_owner = po.id AND pot.is_owner = true')
            ->leftJoin('fias_addresses AS fact_fias', 'po.id_fact_fias_address = fact_fias.id')
            ->leftJoin('fias_addresses AS fias', 'po.id_fias_address = fias.id')

            ->leftJoin('pet_rabies_vaccination', 'pet_rabies_vaccination.id_pet = pets.id')
            ->leftJoin('tmc.tmc', 'pet_rabies_vaccination.id_vaccine = tmc.tmc.id')
            ->leftJoin('tmc.tmc_to_diseases', 'tmc.id = tmc.tmc_to_diseases.id_tmc')

            ->leftJoin('visits_gov_services', 'pets.id = visits_gov_services.id_pet')
            ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
            ->leftJoin('visits', 'visits_gov_services.id_visit = visits.id')

            ->leftJoin('violation', 'violation.id_pet = pets.id')

            ->leftJoin('violation_ARV as varv', 'violation.id_violation = varv.id_violation')

            ->where([
                /*
                 * Список условий, по которым не следует открывать нарушение на животное
                 */
                'OR',

                // Есть вакцина в карточке животного
                ['AND',
                    ['tmc.tmc.type' => 'vaccine'],
                    ['tmc.tmc_to_diseases.id_disease' => $disease_id],
                    new Expression('pet_rabies_vaccination.valid_until > now()'),
                ],

                // Если запланирована вакцинация
                // Либо по дате визита, либо по запланированой дате в pets.date_plan_rabies_vaccination для внешних Организаций
                ['OR', [
                    'AND',
                    ['gov_services.id_service_type' => ServiceTypes::TYPE_VACCINATION],
                    ['NOT', ['visits.status' => VisitStatus::CANCELED]],
                    new Expression('lower(visits.time_range)::date >= CURRENT_TIMESTAMP')
                ],
                    new Expression('pets.date_plan_rabies_vaccination > now()')
                ],

                // Если есть подобное открытое нарушение
                ['AND',
                    ['IN', 'violation.state', Violation::ACTIVE_STATES],
                    ['violation.id_disease' => $disease_id],
                    ['violation.id_type' => $violation_type_id],
                ],

                // Если есть подобное закрытое нарушение с оформленным Административно Правовым Нарушением (АПН)
                ['AND',
                    ['IN', 'violation.state', [Violation::STATE_FINISHED, Violation::STATE_CANCELED]],
                    ['violation.id_disease' => $disease_id],
                    ['violation.id_type' => $violation_type_id],
                    ['NOT', ['varv' => null]],
                ],
            ])
        ;
    }
}
