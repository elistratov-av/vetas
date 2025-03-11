<?php

namespace app\modules\v2\modules\emergency\events;

use app\common\models\VisitStatus;
use app\models\db\OrganizationsEmergency;
use app\models\db\ShiftType;
use app\models\db\Visits;

class EmergencyEventHandler
{
    private $countLiveQueueVisits;
    private $countChannelVisits;

    /**
     * Обработчик события
     *
     * @param EmergencyEvent $event
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(EmergencyEvent $event): void
    {
        \Yii::$app->db->transaction(function() use($event){
            $this->cancelLiveQueueVisits($event->emergency);
            $this->cancelChannelVisits($event->emergency);
            $event->emergency->count_live_queue_visits = $this->countLiveQueueVisits;
            $event->emergency->count_not_live_queue_visits = $this->countChannelVisits;
            $event->emergency->save(false);
        });
    }

    /**
     * Отмена не взятых в работу приемов из живой очереди
     *
     * @param OrganizationsEmergency $emergency
     * @throws \yii\db\Exception
     */
    protected function cancelLiveQueueVisits(OrganizationsEmergency $emergency): void
    {
        $sql = <<<SQL
WITH updated AS (
  UPDATE visits 
  SET status = :new_status 
  WHERE
    id_organization = :id_organization 
    AND status IN (:status_new, :status_changed) 
    AND channel = :live_channel
  RETURNING 1
)
SELECT count(*) FROM updated;
SQL;
        $result = \Yii::$app->db->createCommand($sql, [
            'new_status' => VisitStatus::TRANSFER,
            'status_new' => VisitStatus::NEW,
            'status_changed' => VisitStatus::TRANSFER,
            'live_channel' => $this->getLiveChannelId(),
            'id_organization' => $emergency->id_organization,
        ])->queryOne();
        $this->countLiveQueueVisits = $result['count'];
    }

    /**
     * Перевод не взятых в работу приемов не из живой очереди, которые попали на время действия эктренной ситуации
     * в статус "К переносу"
     *
     * @param OrganizationsEmergency $emergency
     * @throws \yii\db\Exception
     */
    protected function cancelChannelVisits(OrganizationsEmergency $emergency): void
    {
        $sql = <<<SQL
WITH updated AS (
  UPDATE visits 
  SET status = :new_status 
  WHERE
    id_organization = :id_organization 
    AND (time_range && tsrange(:from, :to, '[)')) 
    AND status IN (:status_new, :status_changed) 
    AND channel NOT IN (:live_channel, :call_to_home_channel)
    AND "type" = :type_visit
  RETURNING id
),
rows AS (
  INSERT INTO visits_emergency(id_visit, id_emergency) 
    SELECT id as id_visit, :emergency_id as id_emergency 
    FROM updated
  RETURNING 1
)
SELECT count(*) FROM rows;
SQL;
        $result = \Yii::$app->db->createCommand($sql, [
            'new_status' => VisitStatus::TRANSFER,
            'from' => $emergency->date_from,
            'to' => $emergency->date_to,
            'status_new' => VisitStatus::NEW,
            'status_changed' => VisitStatus::CHANGED,
            'live_channel' => $this->getLiveChannelId(),
            'call_to_home_channel' => ShiftType::getTypesIdWithCallToHome(),
            'id_organization' => $emergency->id_organization,
            'emergency_id' => $emergency->id,
            'type_visit' => Visits::TYPE_VISIT,
        ])->queryOne();
        $this->countChannelVisits = $result['count'];
    }

    /**
     * @return int
     */
    protected function getLiveChannelId(): int
    {
        return ShiftType::findOne(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE])->id;
    }
}
