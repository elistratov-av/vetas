<?php

namespace app\modules\v2\modules\gosvetnadzor\cron;

use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use app\models\db\ViolationType;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use yii\db\Query;

/**
 * Автоматическая проверка на устранение нарушений и закрытие их в системе
 *
 * @package app\modules\v2\modules\gosvetnadzor\cron
 */
class ViolationCheckClosed extends BaseViolationCheck
{
    /**
     * Размер пачки животных для обработки
     */
    const QUERY_VIOLATIONS_BATCH_SIZE = 20;

    /**
     * Название заболевания БЕШЕНСТВО в справочнике
     */
    const QUERY_CONSTANTS_DISEASES_NAME_RABIES = 'Бешенство (Rabies)';

    /**
     * Находит и закрывает нарушения по факту устранения нарушений
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function checkAndCloseViolations()
    {
        $this->checkAndCloseVaccinationViolations();
        $this->checkAndCloseIdentificationViolations();

        $this->checkAndCloseExpiredViolations();
    }

    /**
     * Находит и закрывает нарушения вакцинации по факту устранения нарушения
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function checkAndCloseVaccinationViolations()
    {
        $vaccineViolationQueries = $this->getRemovedVaccineViolationQueries();
        foreach ($vaccineViolationQueries as $query) {
            $this->closeViolations($query, ViolationCancellation::CANCEL_BY_VACCINATION);
        }
    }

    /**
     * Находит и закрывает нарушения по идентификации по факту устранения нарушения
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function checkAndCloseIdentificationViolations()
    {
        $this->closeViolations(
            $this->buildQueryIdentifiedViolations(),
            ViolationCancellation::CANCEL_BY_IDENTIFICATION
        );
    }

    /**
     * Находит и закрывает нарушения по идентификации по истечению срока обработки нарушения
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function checkAndCloseExpiredViolations()
    {
        $this->closeViolations(
            $this->buildQueryExpiredViolations(),
            ViolationCancellation::CANCEL_BY_VIOLATION_EXPIRED
        );
    }

    /**
     * Метод для закрытия нарушений и логирования этих изменений в истории нарушений
     *
     * @param Query $query
     * @param string $closeReason
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    private function closeViolations(Query $query, string $closeReason)
    {
        /** @var ViolationCancellation $violationCancellation */
        $violationCancellation = ViolationCancellation::find()
            ->where(['tech_name' => $closeReason])
            ->one();

        foreach ($query->select('violation.id_violation id_violation')->batch(self::QUERY_VIOLATIONS_BATCH_SIZE) as $rows) {
            foreach($rows as $row) {
                try {
                    (new ViolationChangeStateModel(['id_violation' => $row['id_violation']]))->cancel($violationCancellation->id_cancellation);
                } catch(\Throwable $ex) {

                }
            }
        }
    }

    /**
     * Возвращает список нарушений по вакцинированию по которым внесеный данные об устранении нарушения
     *
     * @return array
     */
    protected function getRemovedVaccineViolationQueries()
    {
        return [
            $this->buildQueryRabiesVaccinedViolations(),
            $this->buildQueryVaccinedViolations()
        ];
    }

    /**
     * Возвращает query списка нарушений по вакцинированию по которым внесеный данные об устранении нарушения (только Бешенство)
     *
     * @return \yii\db\ActiveQuery
     */
    protected function buildQueryRabiesVaccinedViolations()
    {
        return Violation::find()
            ->distinct()
            ->leftJoin('pet_rabies_vaccination AS prv', 'violation.id_pet = prv.id_pet')
            ->leftJoin('violation_type AS vt', 'violation.id_type = vt.id_type')
            ->leftJoin('tmc.tmc as tmc', 'prv.id_vaccine = tmc.id')
            ->leftJoin('tmc.tmc_to_diseases as ttd', 'tmc.id = ttd.id_tmc')
            ->leftJoin('violation_ARV as arv', 'violation.id_violation = arv.id_violation')
            ->where(['in', 'violation.state', Violation::ACTIVE_STATES])
            ->andWhere('prv.valid_until > now() AND ttd.id_disease = violation.id_disease')
            ->andWhere(['vt.type' => ViolationType::TYPE_VACCINATION_VIOLATION])
            ->andWhere(['arv' => null])
            ->orderBy('violation.id_violation ASC')
        ;
    }

    /**
     * Возвращает query списка нарушений по вакцинированию по которым внесеный данные об устранении нарушения (кроме Бешенства)
     *
     * @return \yii\db\ActiveQuery
     */
    protected function buildQueryVaccinedViolations()
    {
        /*
         * БЕШЕНСТВО
         */
        $rabies_disease_id = $this->getDiseaseId(self::QUERY_CONSTANTS_DISEASES_NAME_RABIES);

        return Violation::find()
            ->distinct()
            ->leftJoin('pet_other_vaccinations AS pov', 'violation.id_pet = pov.id_pet')
            ->leftJoin('violation_type AS vt', 'violation.id_type = vt.id_type')
            ->leftJoin('tmc.tmc as tmc', 'pov.id_vaccine = tmc.id')
            ->leftJoin('tmc.tmc_to_diseases as ttd', 'tmc.id = ttd.id_tmc')
            ->leftJoin('violation_ARV as arv', 'violation.id_violation = arv.id_violation')
            ->where(['in', 'violation.state', Violation::ACTIVE_STATES])
            ->andWhere("violation.id_disease != $rabies_disease_id")
            ->andWhere('pov.valid_until > now() AND ttd.id_disease = violation.id_disease')
            ->andWhere(['vt.type' => ViolationType::TYPE_VACCINATION_VIOLATION])
            ->andWhere(['arv' => null])
            ->orderBy('violation.id_violation ASC')
        ;
    }

    /**
     * Возвращает query списка нарушений по идентификации для которых найдена идентификация
     *
     * @return \yii\db\ActiveQuery
     */
    protected function buildQueryIdentifiedViolations()
    {
        return Violation::find()
            ->distinct()
            ->innerJoin('pet_identification AS pi', 'violation.id_pet = pi.id_pet')
            ->leftJoin('violation_type AS vt', 'violation.id_type = vt.id_type')
            ->leftJoin('violation_ARV as arv', 'violation.id_violation = arv.id_violation')
            ->where(['in', 'state', Violation::ACTIVE_STATES])
            ->andWhere(['vt.type' => ViolationType::TYPE_IDENT_VIOLATION])
            ->andWhere(['arv' => null])
            ->orderBy('violation.id_violation ASC')
        ;
    }

    /**
     * Возвращает query списка нарушений в статусе "На проверке" или "Подтверждено", которым более 30 дней
     *
     * @return \yii\db\ActiveQuery
     */
    protected function buildQueryExpiredViolations()
    {
        return Violation::find()
            ->distinct()
            ->where(['IN', 'violation.state', [Violation::STATE_NEW, Violation::STATE_ON_VERIFY, Violation::STATE_ACCEPTED]])
            ->andWhere("violation.date_violation < NOW() - INTERVAL '30 DAYS'")
            ->orderBy('violation.id_violation ASC')
        ;
    }
}
