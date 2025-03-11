<?php

namespace app\modules\v2\modules\emergency\models;

use app\models\db\OrganizationsEmergency;
use app\models\db\Specialists;
use app\models\db\Visits;
use app\models\db\VisitsEmergency;
use app\modules\v2\modules\emergency\events\EmergencyEvent;
use app\modules\v2\modules\emergency\skeletons\emergency\EmergencyLists;
use yii\web\BadRequestHttpException;
use yii\db\Expression;

class EmergencyModel
{
    /**
     * @param int $idOrganization
     * @param string $dateFrom
     * @param string $dateTo
     * @return OrganizationsEmergency
     * @throws BadRequestHttpException
     */
    public function save(int $idOrganization, string $dateFrom, string $dateTo): OrganizationsEmergency
    {
        $emergency = new OrganizationsEmergency();
        $emergency->id_organization = $idOrganization;
        $emergency->date_from = $dateFrom;
        $emergency->date_to = $dateTo;
        $emergency->reason = $this->makeReason($dateFrom, $dateTo);

        if (!$emergency->validate()) {
            throw new BadRequestHttpException(implode("\r\n", $emergency->getErrorSummary(true)));
        }

        $emergency->save(false);

        $event = new EmergencyEvent();
        $event->emergency = $emergency;
        \Yii::$app->trigger(EmergencyEvent::EMERGENCY_CREATE_EVENT, $event);

        return $emergency;
    }

    /**
     * Метод для получения одной экстренной ситуации, которая имеют пересечение с переданным диапозоном
     *
     * @param int $idOrganization
     * @param string|null $dateTimeFrom
     * @param string|null $dateTimeTo
     * @return OrganizationsEmergency|array|\yii\db\ActiveRecord|null
     */
    public function getOneOrganizationsEmergencyForPeriod(int $idOrganization, string $dateTimeFrom = null, string $dateTimeTo = null)
    {
        $expressionFrom = $dateTimeFrom === null ?
            new Expression('now()::timestamp without time zone') :
            new Expression("to_timestamp('{$dateTimeFrom}', 'YYYY-MM-DD hh24:mi:ss')::timestamp without time zone")
        ;

        $expressionTo = $dateTimeTo === null ?
            new Expression('now()::timestamp without time zone') :
            new Expression("to_timestamp('{$dateTimeTo}', 'YYYY-MM-DD hh24:mi:ss')::timestamp without time zone")
        ;

        return OrganizationsEmergency::find()
            ->select(['id', 'date_from', 'date_to', 'reason'])
            ->where(['id_organization' => $idOrganization])
            ->andWhere([
                'AND',
                ['<=', 'date_from', $expressionFrom],
                ['>', 'date_to', $expressionTo]
            ])
            ->one();
    }

    /**
     * Метод для получения всех экстренных ситуаций, которые имеют пересечения с переданным диапозоном
     *
     * @param int $idOrganization
     * @param string $dateTimeFrom
     * @param string $dateTimeTo
     * @return array
     */
    public function getAllOrganizationsEmergencyForPeriod(int $idOrganization, string $dateTimeFrom, string $dateTimeTo): array
    {
        $expressionFrom = new Expression("to_timestamp('{$dateTimeFrom}', 'YYYY-MM-DD hh24:mi:ss')::timestamp without time zone");
        $expressionTo = new Expression("to_timestamp('{$dateTimeTo}', 'YYYY-MM-DD hh24:mi:ss')::timestamp without time zone");

        return OrganizationsEmergency::find()
            ->select(['date_from', 'date_to'])
            ->where(['id_organization' => $idOrganization])
            ->andWhere([
                'OR',
                [
                    'AND',
                    ['<=', 'date_from', $expressionFrom],
                    ['>=', 'date_to', $expressionTo]
                ],
                [
                    'AND',
                    ['>=', 'date_from', $expressionFrom],
                    ['<=', 'date_to', $expressionTo]
                ],
                [
                    'AND',
                    ['>=', 'date_to', $expressionFrom],
                    ['<=', 'date_to', $expressionTo]
                ],
                [
                    'AND',
                    ['>=', 'date_from', $expressionFrom],
                    ['<=', 'date_from', $expressionTo]
                ],
            ])
            ->all();
    }

    /**
     * @param int $id
     * @param int $page
     * @param int $limit
     * @return EmergencyLists
     */
    public function visitsList(int $id, int $page = 1, int $limit = 10): EmergencyLists
    {
        $visits = Visits::find()
            ->select([
                'channel AS id_shift_type', 'status', 'change_reason', 'is_paid', 'start_dttm', 'ticket_number',
                'fact_start_dttm', 'fact_end_dttm', 'duration', 'cooldown',
                'id_pet', 'visits.id', 'visits.id_owner', 'visits.created_at', 'vs.id_specialist',
                've.notify_status'
            ])
            ->innerJoin('visits_emergency as ve', 've.id_emergency = :id_emergency AND ve.id_visit = visits.id', [
                'id_emergency' => $id
            ])
            ->joinWith(['pet' => function ($petsQuery) {
                $petsQuery->select(['id', 'name']);
            }])
            ->joinWith('visitsSpecialists AS vs', false)
            ->with([
                'specialists' => function ($specialistQuery) {
                    /* @var $specialistQuery \yii\db\ActiveQuery */
                    $specialistQuery->joinWith('user', false)
                        ->select(array_merge(['specialists.*'], Specialists::personalAttributes()));
                },
                'owner',
                'species' => function ($speciesQuery) {
                    $speciesQuery->select(['id', 'name']);
                },
            ])
            ->asArray()
            ->limit($limit)
            ->offset($limit * ($page - 1));

        $countsNotifiedVisits = OrganizationsEmergency::find()
            ->select(['count_live_queue_visits', 'count_not_live_queue_visits'])
            ->where(['id' => $id])
            ->one()
        ;
        $result = new EmergencyLists(
            $visits->all(),
            $visits->count(),
            $countsNotifiedVisits->count_live_queue_visits ?? 0,
            $countsNotifiedVisits->count_not_live_queue_visits ?? 0
        );
        $result->customPagination($page, $limit);

        return $result;
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return string
     */
    protected function makeReason($dateFrom, $dateTo): string
    {
        $template = 'В организации в <b>%s</b> выявлено животное с признаками бешенства. Проводится дезинфекция до <b>%s</b>';
        return sprintf(
            $template,
            (new \DateTime($dateFrom))->format('H:i, d.m.Y'),
            (new \DateTime($dateTo))->format('H:i, d.m.Y')
        );
    }

    /**
     * @param int $idVisit
     * @return bool
     * @throws BadRequestHttpException
     */
    public function notify(int $idVisit): bool
    {
        $visitModel = Visits::find()
            ->where(['id' => $idVisit])
            ->one();

        if ($visitModel === null) {
            throw new BadRequestHttpException('Визит с указанным id отсутствует');
        }

        $visitsEmergencyModel = VisitsEmergency::find()
            ->where(['id_visit' => $visitModel->id])
            ->one()
        ;

        if ($visitsEmergencyModel === null) {
            throw new BadRequestHttpException('Для данного визита не создана запись об экстренной ситуации');
        }

        $visitsEmergencyModel->notify_status = !$visitsEmergencyModel->notify_status;
        return $visitsEmergencyModel->save(false);
    }
}
