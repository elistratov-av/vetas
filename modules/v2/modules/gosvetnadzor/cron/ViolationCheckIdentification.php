<?php


namespace app\modules\v2\modules\gosvetnadzor\cron;

use app\common\models\VisitStatus;
use app\models\db\Pets;
use app\models\db\ServiceTypes;
use app\models\db\Violation;
use app\models\db\Species;
use app\models\db\ViolationType;
use yii\base\InvalidConfigException;
use yii\console\Exception;
use yii\db\Expression;
use yii\db\Query;

/**
 * Автоматическая проверка нарушений по идентификации и заведение их в системе
 *
 * @package app\modules\v2\modules\gosvetnadzor\cron
 */
class ViolationCheckIdentification extends BaseViolationCheck
{
    /**
     * Город в котором ищем
     */
    const QUERY_CONSTANTS_CITY_SEARCH = 'Москва';

    /**
     * Размер пачки животных для обработки
     */
    const QUERY_PETS_BATCH_SIZE = 20;

    /**
     * Комментарий к нарушению по идентификации
     */
    const COMMENT_TO_IDENTIFICATION_VIOLATION = 'Автоматическая проверка на наличие идентификации';


    /**
     * Создает нарушения по идентификации
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
    public function checkIdentificationAndCreateViolations()
    {
        /*
         * Тип нарушения - нарушение идентификации
         */
        $violation_type_id = $this->getViolationTypeId(ViolationType::V01_IDENT_LACK);


        $query = $this->buildQueryIdentification(
            $violation_type_id
        );

        $ids = $query->asArray()->column();

        /*
         * Критерии животных которым нужно выставлять нарушения
         */
        $pets = Pets::find()
            ->select('pets.id id')
            ->with('owner')
            ->distinct()
            ->leftJoin('species AS s', 'pets.id_species = s.id')
            ->leftJoin('visits_gov_services', 'pets.id = visits_gov_services.id_pet')
            ->leftJoin('visits', 'visits.id = visits_gov_services.id_visit')
            ->leftJoin('pets_to_owner AS pto', 'pets.id = pto.id_pet')
            ->leftJoin('pet_owner_type AS pot', 'pto.id_owner_type = pot.id')
            // Проверяем по адресу хозяина is_owner = true
            ->leftJoin('pet_owners po', 'pto.id_owner = po.id AND pot.is_owner = true')
            ->leftJoin('fias_addresses AS fact_fias', 'po.id_fact_fias_address = fact_fias.id')
            ->leftJoin('fias_addresses AS fias', 'po.id_fias_address = fias.id')
            // Животное не снято с учёта
            ->where(['pets.id_reg_expire_reason' => null])
            // Запись животного не является дублем
            ->andWhere(['pets.id_main_pet' => null])
            // Проверяем только Котов и Собак
            ->andWhere(['IN', 's.tech_name', [Species::TECH_NAME_DOG, Species::TECH_NAME_CAT]])
            // Только тех животных у которых есть завершенные обследования
            ->andWhere(['visits.status' => VisitStatus::FINISHED])
            // Только животные проживающие в Москве
            ->andWhere(['OR',
                ['ILIKE', 'fact_fias.full_address', self::QUERY_CONSTANTS_CITY_SEARCH],
                ['ILIKE', 'fias.full_address', self::QUERY_CONSTANTS_CITY_SEARCH]])
            // Исключаем животных у которых запланирована идентификация
            ->andWhere(['OR',
                ['pets.date_plan_identification' => null],
                new Expression('pets.date_plan_identification <= now()')])
            ->indexBy('id');

        $violationRows = [];
        /** @var Pets[] $rows */
        foreach ($pets->batch(self::QUERY_PETS_BATCH_SIZE) as $rows) {
            foreach ($rows as $row) {
                if ($row->owner && !in_array($row->id, $ids)) array_push($violationRows, $row);
            }
        }

        if (count($violationRows) > 0) {
            $violationRowsChunks = array_chunk($violationRows, self::QUERY_PETS_BATCH_SIZE);
            foreach ($violationRowsChunks as $chunk) {
                Violation::getDb()->beginTransaction();

                foreach ($chunk as $row) {
                    $violation = new Violation([
                        'state' => Violation::STATE_ON_VERIFY,
                        'id_owner' => $row->owner->id,
                        'id_pet' => $row->id,
                        'id_type' => $violation_type_id,
                        'date_violation' => new Expression('now()'),
                        'comment' => self::COMMENT_TO_IDENTIFICATION_VIOLATION,
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
     * Возвращает собранный запрос на поиск списка собак и кошек, которым нужна идентификация
     *
     * @param $violation_type_id
     * @return Query
     */
    protected function buildQueryIdentification($violation_type_id)
    {
        return Pets::find()
            ->select('pets.id as id')
            ->from('pets')
            ->distinct()
            ->leftJoin('species AS s', 'pets.id_species = s.id')
            ->leftJoin('pets_to_owner AS pto', 'pets.id = pto.id_pet')
            ->leftJoin('pet_owner_type AS  pot', 'pto.id_owner_type = pot.id')
            ->leftJoin('pet_owners po', 'pto.id_owner = po.id')
            ->leftJoin('fias_addresses AS fact_fias', 'po.id_fact_fias_address = fact_fias.id')
            ->leftJoin('fias_addresses AS fias', 'po.id_fias_address = fias.id')
            ->leftJoin('visits_gov_services', 'pets.id = visits_gov_services.id_pet')
            ->leftJoin('gov_services', 'visits_gov_services.id_service = gov_services.id')
            ->leftJoin('visits', 'visits_gov_services.id_visit = visits.id')
            ->leftJoin('violation', 'violation.id_pet = pets.id')
            ->leftJoin('violation_ARV as varv', 'violation.id_violation = varv.id_violation')
            ->leftJoin('pet_identification as pi', 'pets.id = pi.id_pet')
            ->where([
                /*
                 * Список условий, по которым не следует открывать нарушение на животное
                 */
                'OR',

                // Есть аналогичные открытые нарушения
                ['AND',
                    ['IN', 'state', Violation::ACTIVE_STATES],
                    ['id_type' => $violation_type_id],
                ],

                // Если запланирована идентификация
                // Либо по дате визита, либо по запланированой дате в pets.date_plan_identification для внешних Организаций
                ['OR',
                    ['AND',
                        ['gov_services.id_service_type' => ServiceTypes::TYPE_IDENTIFICATION],
                        ['NOT', ['visits.status' => VisitStatus::CANCELED]],
                        new Expression('lower(visits.time_range)::date >= CURRENT_TIMESTAMP')
                    ],
                    new Expression('pets.date_plan_identification > now()'),
                ],

                // Если есть подобное закрытое нарушение с оформленным Административно Правовым Нарушением (АПН)
                ['AND',
                    ['IN', 'violation.state', [Violation::STATE_FINISHED, Violation::STATE_CANCELED]],
                    ['violation.id_type' => $violation_type_id],
                    ['NOT', ['varv' => null]],
                ],

                // Идентифицирован
                ['NOT', ['pi' => null]],

                // Плановая идентфикация наступила или не установнена
                ['NOT',
                    ['OR',
                        ['pets.date_plan_identification' => null],
                        new Expression('pets.date_plan_identification <= now()')
                ]]
            ])
            ->orderBy('pets.id ASC');
    }
}
